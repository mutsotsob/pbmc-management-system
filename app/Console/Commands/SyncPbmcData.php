<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AcrnPbmc;
use App\Models\Pbmc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncPbmcData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pbmc:sync 
                            {--fresh : Delete all existing local records before sync}
                            {--limit= : Limit number of records to sync (for testing)}';

    /**
     * The console command description.
     */
    protected $description = 'Sync PBMC data from external ACRN PostgreSQL database to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║   Starting PBMC Data Sync Process     ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();
        
        try {
            // Test connection first
            $this->info('Testing connection to ACRN database...');
            $totalExternal = AcrnPbmc::count();
            $this->info("✓ Connection successful! Found {$totalExternal} records in external database");
            $this->newLine();
            
            // Handle fresh sync option
            if ($this->option('fresh')) {
                if ($this->confirm('Are you sure you want to delete all existing local PBMC records?', false)) {
                    $deleted = Pbmc::count();
                    Pbmc::truncate();
                    $this->warn("Deleted {$deleted} existing local records");
                    $this->newLine();
                }
            }
            
            // Get records to sync
            $query = AcrnPbmc::query();
            
            if ($limit = $this->option('limit')) {
                $query->limit($limit);
                $this->info("Limiting sync to {$limit} records (testing mode)");
            }
            
            $externalData = $query->get();
            $this->info("Processing {$externalData->count()} records...");
            $this->newLine();
            
            // Progress bar
            $bar = $this->output->createProgressBar($externalData->count());
            $bar->start();
            
            $imported = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];
            
            foreach ($externalData as $record) {
                try {
                    // Check if record exists locally using ptid and visit as unique identifier
                    // ACRN's sample_id is the PTID
                    $existing = Pbmc::where('ptid', $record->sample_id)
                        ->where('visit', $record->visit_number)
                        ->first();
                    
                    // Parse sample_date from "28-Jul-25" format to Y-m-d
                    $collectionDate = null;
                    if ($record->sample_date) {
                        try {
                            $collectionDate = \Carbon\Carbon::createFromFormat('d-M-y', $record->sample_date)->format('Y-m-d');
                        } catch (\Exception $e) {
                            Log::warning("Could not parse date: {$record->sample_date} for {$record->sample_id}");
                        }
                    }
                    
                    // Parse time fields safely
                    $collectionTime = null;
                    if ($record->blood_draw_time) {
                        try {
                            $collectionTime = \Carbon\Carbon::parse($record->blood_draw_time)->format('H:i:s');
                        } catch (\Exception $e) {
                            Log::warning("Could not parse blood_draw_time: {$record->blood_draw_time} for {$record->sample_id}");
                        }
                    }
                    
                    $processStartTime = null;
                    if ($record->lab_processing_start_time) {
                        try {
                            $processStartTime = \Carbon\Carbon::parse($record->lab_processing_start_time)->format('H:i:s');
                        } catch (\Exception $e) {
                            Log::warning("Could not parse lab_processing_start_time: {$record->lab_processing_start_time} for {$record->sample_id}");
                        }
                    }
                    
                    // Map ACRN fields to local Pbmc fields
                    $data = [
                        // Study Information
                        'study_choice' => 'ACRN Import',
                        'other_study_name' => null,
                        
                        // PT Details
                        'ptid' => $record->sample_id,  // ACRN sample_id = local ptid
                        'visit' => $record->visit_number,
                        'collection_date' => $collectionDate,
                        'collection_time' => $collectionTime,
                        'process_start_date' => $collectionDate, // Use collection date as default
                        'process_start_time' => $processStartTime,
                        
                        // Processing Data
                        'usable_blood_volume' => $record->total_blood_volume_ml,
                        'sample_status' => $record->sample_condition ? [$record->sample_condition] : null,
                        'counting_method' => 'Automated', // ACRN data is automated
                        
                        // Calculated Outcomes
                        'counting_resuspension' => $record->resuspension_volume_ml,
                        'cell_count_concentration' => $record->viable_cells_per_ml,
                        'total_cell_number' => $record->total_viable_cells,
                        
                        // Automated Cell Count (this is where ACRN data belongs)
                        'auto_viability_percent' => $record->viability_percent,
                        'auto_total_viable_cells_original' => $record->total_viable_cells,
                        'auto_total_cryovials_frozen' => $record->cryovials_frozen,
                        'auto_comment' => $record->comments,
                        
                        // Mark as imported from ACRN
                        'imported_from_acrn' => true,
                    ];
                    
                    if ($existing) {
                        // Update existing record
                        $existing->update($data);
                        $updated++;
                    } else {
                        // Create new record
                        Pbmc::create($data);
                        $imported++;
                    }
                    
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = [
                        'sample_id' => $record->sample_id ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                    
                    Log::error('PBMC Sync Error', [
                        'sample_id' => $record->sample_id ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);
            
            // Display results
            $this->info('╔════════════════════════════════════════╗');
            $this->info('║         Sync Completed!                ║');
            $this->info('╚════════════════════════════════════════╝');
            $this->newLine();
            
            $this->table(
                ['Status', 'Count'],
                [
                    ['New Records Imported', $imported],
                    ['Existing Records Updated', $updated],
                    ['Records Skipped (Errors)', $skipped],
                    ['Total Processed', $imported + $updated + $skipped],
                ]
            );
            
            // Show errors if any
            if (!empty($errors)) {
                $this->newLine();
                $this->error("Errors encountered during sync:");
                $this->table(
                    ['Sample ID', 'Error'],
                    array_map(fn($e) => [$e['sample_id'], substr($e['error'], 0, 80)], array_slice($errors, 0, 10))
                );
                
                if (count($errors) > 10) {
                    $this->warn("... and " . (count($errors) - 10) . " more errors. Check logs for details.");
                }
            }
            
            $this->newLine();
            $this->info('✓ Sync process completed successfully!');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('╔════════════════════════════════════════╗');
            $this->error('║      Sync Failed with Error!           ║');
            $this->error('╚════════════════════════════════════════╝');
            $this->newLine();
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            
            Log::error('PBMC Sync Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}
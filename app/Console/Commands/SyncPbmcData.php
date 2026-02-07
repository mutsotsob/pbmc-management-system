<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AcrnPbmc;
use App\Models\Pbmc; // Your local PBMC model
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
                    // Check if record exists locally using sample_id as unique identifier
                    $existing = Pbmc::where('sample_id', $record->sample_id)->first();
                    
                    // Prepare data for local database
                    // Convert sample_date from "28-Jul-25" format to Y-m-d
                    $sampleDate = null;
                    if ($record->sample_date) {
                        try {
                            $sampleDate = \Carbon\Carbon::createFromFormat('d-M-y', $record->sample_date)->format('Y-m-d');
                        } catch (\Exception $e) {
                            // If parsing fails, try to use as-is or set to null
                            $sampleDate = $record->sample_date;
                        }
                    }
                    
                    $data = [
                        'sample_id' => $record->sample_id,
                        'visit_number' => $record->visit_number,
                        'sample_date' => $sampleDate,
                        'total_blood_volume_ml' => $record->total_blood_volume_ml,
                        'blood_draw_time' => $record->blood_draw_time,
                        'sample_condition' => $record->sample_condition,
                        'viability_percent' => $record->viability_percent,
                        'viable_cells_per_ml' => $record->viable_cells_per_ml,
                        'resuspension_volume_ml' => $record->resuspension_volume_ml,
                        'total_viable_cells' => $record->total_viable_cells,
                        'cell_yield_per_ml' => $record->cell_yield_per_ml,
                        'cells_per_vial' => $record->cells_per_vial,
                        'cryovials_frozen' => $record->cryovials_frozen,
                        'lab_processing_start_time' => $record->lab_processing_start_time,
                        'freezing_time' => $record->freezing_time,
                        'processing_to_freezing_duration' => $record->processing_to_freezing_duration,
                        'blood_draw_to_freezing_duration' => $record->blood_draw_to_freezing_duration,
                        'operator_initials' => $record->operator_initials,
                        'comments' => $record->comments,
                        
                        // Mark as imported from ACRN
                        'imported_from_acrn' => true,
                        'acrn_pbmc_id' => $record->pbmc_id,
                        'acrn_synced_at' => now(),
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
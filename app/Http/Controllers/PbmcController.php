<?php

namespace App\Http\Controllers;

use App\Models\Pbmc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Throwable;

class PbmcController extends Controller
{
    /**
     * Display PBMC list / dashboard
     */
    public function index(Request $request)
    {
        try {
            $query = Pbmc::query()->latest();

            if ($request->filled('study')) {
                $query->byStudy($request->study);
            }

            if ($request->filled('ptid')) {
                $query->byPtid($request->ptid);
            }

            if ($request->filled('from') && $request->filled('to')) {
                $query->collectedBetween($request->from, $request->to);
            }

            if ($request->boolean('viable')) {
                $query->viable();
            }

            $pbmcs = $query->paginate(15);

            return view('pbmc.index', compact('pbmcs'));
        } catch (Throwable $e) {
            Log::error('PBMC index failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return back()->with('error', 'Failed to load PBMC records.');
        }
    }

    /**
     * Show create PBMC form
     */
    public function create()
    {
        return view('pbmc.create');
    }

    /**
     * Store new PBMC
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $this->validatedData($request);

            /** ------------------------------
             * Create PBMC
             * ------------------------------ */
            $pbmc = Pbmc::create($validated);

            /** ------------------------------
             * Save reagents
             * ------------------------------ */
            if ($request->filled('reagents')) {
                foreach ($request->reagents as $reagent) {
                    if (!empty($reagent['name'])) {
                        $pbmc->reagents()->create([
                            'name'   => $reagent['name'],
                            'lot'    => $reagent['lot'] ?? null,
                            'expiry' => $reagent['expiry'] ?? null,
                        ]);
                    }
                }
            }

            /** ------------------------------
             * Save washes
             * ------------------------------ */
            if ($request->filled('washes')) {
                foreach ($request->washes as $washNumber => $wash) {
                    $pbmc->washes()->create([
                        'wash_number'      => $washNumber,
                        'start_time'       => $wash['start_time'] ?? null,
                        'stop_time'        => $wash['stop_time'] ?? null,
                        'volume'           => $wash['volume'] ?? null,
                        'centrifuge_id'    => $wash['centrifuge_id'] ?? null,
                        'centrifuge_speed' => $wash['centrifuge_speed'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('pbmc.show', $pbmc)
                ->with('success', 'PBMC record created successfully.');

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('PBMC creation failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create PBMC record.');
        }
    }

    /**
     * Show single PBMC
     */
    public function show(Pbmc $pbmc)
    {
        try {
            $pbmc->load(['reagents', 'washes']);

            return view('pbmc.show', compact('pbmc'));
        } catch (Throwable $e) {
            Log::error('PBMC show failed', [
                'pbmc_id' => $pbmc->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('pbmc.index')
                ->with('error', 'Failed to load PBMC record.');
        }
    }

    /**
     * Show edit PBMC form
     */
    public function edit(Pbmc $pbmc)
    {
        try {
            $pbmc->load(['reagents', 'washes']);

            return view('pbmc.edit', compact('pbmc'));
        } catch (Throwable $e) {
            Log::error('PBMC edit failed', [
                'pbmc_id' => $pbmc->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('pbmc.index')
                ->with('error', 'Failed to load PBMC edit form.');
        }
    }

    /**
     * Update PBMC
     */
    public function update(Request $request, Pbmc $pbmc)
    {
        DB::beginTransaction();

        try {
            $validated = $this->validatedData($request);

            $pbmc->update($validated);

            /** Reset child records to avoid duplicates */
            $pbmc->reagents()->delete();
            $pbmc->washes()->delete();

            if ($request->filled('reagents')) {
                foreach ($request->reagents as $reagent) {
                    if (!empty($reagent['name'])) {
                        $pbmc->reagents()->create([
                            'name'   => $reagent['name'],
                            'lot'    => $reagent['lot'] ?? null,
                            'expiry' => $reagent['expiry'] ?? null,
                        ]);
                    }
                }
            }

            if ($request->filled('washes')) {
                foreach ($request->washes as $washNumber => $wash) {
                    $pbmc->washes()->create([
                        'wash_number'      => $washNumber,
                        'start_time'       => $wash['start_time'] ?? null,
                        'stop_time'        => $wash['stop_time'] ?? null,
                        'volume'           => $wash['volume'] ?? null,
                        'centrifuge_id'    => $wash['centrifuge_id'] ?? null,
                        'centrifuge_speed' => $wash['centrifuge_speed'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('pbmc.show', $pbmc)
                ->with('success', 'PBMC record updated successfully.');

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('PBMC update failed', [
                'pbmc_id' => $pbmc->id,
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update PBMC record.');
        }
    }

    /**
     * Delete PBMC
     */
    public function destroy(Pbmc $pbmc)
    {
        try {
            $pbmc->delete();

            return redirect()
                ->route('pbmc.index')
                ->with('success', 'PBMC record deleted successfully.');
        } catch (Throwable $e) {
            Log::error('PBMC delete failed', [
                'pbmc_id' => $pbmc->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete PBMC record.');
        }
    }

    /**
     * Centralised validation
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            // Study
            'study_choice' => 'required|string|max:255',
            'other_study_name' => 'nullable|string|max:255',

            // PT
            'ptid' => 'required|string|max:100',
            'visit' => 'required|string|max:50',
            'collection_date' => 'required|date',
            'collection_time' => 'nullable|date_format:H:i',
            'process_start_date' => 'nullable|date',
            'process_start_time' => 'nullable|date_format:H:i',

            // Processing
            'processing_data' => 'nullable|string',
            'plasma_harvesting' => 'nullable|boolean',
            'sample_status' => 'nullable|array',
            'counting_method' => 'required|in:Manual Count,Automated',
            'usable_blood_volume' => 'nullable|numeric',

            // Manual
            'manual_counts' => 'nullable|array',
            'manual_counts.*.nonviable' => 'nullable|numeric|min:0',
            'manual_counts.*.viable' => 'nullable|numeric|min:0',
            'manual_count_start_time' => 'nullable|date_format:H:i',
            'manual_count_stop_time' => 'nullable|date_format:H:i',
            'haemocytometer_factor' => 'nullable|numeric',
            'pbmc_dilution_factor' => 'nullable|numeric',

            // Calculated
            'counting_resuspension' => 'nullable|numeric',
            'cell_count_concentration' => 'nullable|numeric',
            'total_cell_number' => 'nullable|numeric',
            'final_cps_resuspension_volume' => 'nullable|numeric',
            'viability_percent' => 'nullable|numeric',

            // Automated
            'auto_system_clean_done' => 'nullable|boolean',
            'auto_qc_passed' => 'nullable|boolean',
            'auto_viability_percent' => 'nullable|numeric',
            'auto_total_viable_cells_original' => 'nullable|integer',
            'auto_total_cells_original' => 'nullable|integer',
            'auto_total_cryovials_frozen' => 'nullable|integer',

            // Frosty / LN2
            'frosty_storage_temp' => 'nullable|numeric',
            'frosty_date' => 'nullable|date',
            'frosty_time' => 'nullable|date_format:H:i',
            'frosty_transfer' => 'nullable|string',

            'ln2_transfer_first' => 'nullable|string|max:100',
            'ln2_transfer_last' => 'nullable|string|max:100',
            'ln2_transfer_datetime' => 'nullable|date',

            'auto_comment' => 'nullable|string',
        ]);
    }


public function syncFromAcrn()
{
    try {
        Artisan::call('pbmc:sync');
        $output = Artisan::output();
        
        // Check if the sync actually failed by looking for error indicators in output
        if (str_contains($output, 'Sync Failed') || 
            str_contains($output, 'Error') || 
            str_contains($output, 'SQLSTATE') ||
            str_contains($output, 'timeout expired')) {
            
            // Extract a cleaner error message
            if (str_contains($output, 'timeout expired')) {
                return redirect()->back()->with('error', 'Sync failed: Cannot connect to ACRN database. Please check network connectivity or contact your administrator.');
            }
            
            return redirect()->back()->with('error', 'Sync failed. Please check the error logs or contact your administrator.');
        }
        
        // Check for successful sync indicators
        if (str_contains($output, 'records synced') || str_contains($output, 'Processing')) {
            return redirect()->back()->with('success', 'Data synced successfully from ACRN database!');
        }
        
        // If we can't determine success or failure, show the output
        return redirect()->back()->with('success', 'Sync process completed. ' . strip_tags($output));
        
    } catch (\Exception $e) {
        \Log::error('PBMC Sync Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Sync failed: ' . $e->getMessage());
    }
}



public function exportAll()
    {
        $pbmcs = Pbmc::all();
        return $this->generateCsv($pbmcs, 'pbmc_records_all_' . date('Y-m-d_His') . '.csv');
    }

    /**
     * Export selected records to CSV
     */
    public function exportSelected(Request $request)
    {
        $selectedIds = $request->input('selected_ids', []);
        
        if (empty($selectedIds)) {
            return redirect()->back()->with('error', 'No records selected for export');
        }
        
        $pbmcs = Pbmc::whereIn('id', $selectedIds)->get();
        return $this->generateCsv($pbmcs, 'pbmc_records_selected_' . date('Y-m-d_His') . '.csv');
    }

    /**
     * Generate CSV file from PBMC records
     */
    private function generateCsv($pbmcs, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($pbmcs) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Study',
                'PTID',
                'Visit',
                'Collection Date',
                'Collection Time',
                'Process Start Date',
                'Process Start Time',
                'Usable Blood Volume (ml)',
                'Sample Status',
                'Counting Method',
                'Viability %',
                'Auto Viability %',
                'Cell Count Concentration',
                'Total Cell Number',
                'Auto Total Viable Cells',
                'Auto Total Cryovials Frozen',
                'Counting Resuspension',
                'Source',
                'Created At',
                'Updated At',
            ]);

            // CSV Data
            foreach ($pbmcs as $pbmc) {
                fputcsv($file, [
                    $pbmc->id,
                    $pbmc->study_choice === 'Other' ? $pbmc->other_study_name : $pbmc->study_choice,
                    $pbmc->ptid,
                    $pbmc->visit,
                    $pbmc->collection_date?->format('Y-m-d'),
                    $pbmc->collection_time,
                    $pbmc->process_start_date?->format('Y-m-d'),
                    $pbmc->process_start_time,
                    $pbmc->usable_blood_volume,
                    is_array($pbmc->sample_status) ? implode(', ', $pbmc->sample_status) : $pbmc->sample_status,
                    $pbmc->counting_method,
                    $pbmc->viability_percent,
                    $pbmc->auto_viability_percent,
                    $pbmc->cell_count_concentration,
                    $pbmc->total_cell_number,
                    $pbmc->auto_total_viable_cells_original,
                    $pbmc->auto_total_cryovials_frozen,
                    $pbmc->counting_resuspension,
                    $pbmc->imported_from_acrn ? 'ACRN' : 'Manual',
                    $pbmc->created_at?->format('Y-m-d H:i:s'),
                    $pbmc->updated_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

}

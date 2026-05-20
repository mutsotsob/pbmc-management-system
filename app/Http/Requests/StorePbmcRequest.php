<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePbmcRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'study_choice'       => 'required|string|max:255',
            'other_study_name'   => 'nullable|string|max:255',

            'ptid'               => 'required|string|max:100',
            'visit'              => 'required|string|max:50',
            'collection_date'    => 'required|date',
            'collection_time'    => 'nullable|date_format:H:i',
            'process_start_date' => 'nullable|date',
            'process_start_time' => 'nullable|date_format:H:i',

            'processing_data'    => 'nullable|string',
            'plasma_harvesting'  => 'nullable|boolean',
            'sample_status'      => 'nullable|array',
            'counting_method'    => 'required|in:Manual Count,Automated',
            'usable_blood_volume'=> 'nullable|numeric',

            'manual_counts'                => 'nullable|array',
            'manual_counts.*.nonviable'    => 'nullable|numeric|min:0',
            'manual_counts.*.viable'       => 'nullable|numeric|min:0',
            'manual_count_start_time'      => 'nullable|date_format:H:i',
            'manual_count_stop_time'       => 'nullable|date_format:H:i',
            'haemocytometer_factor'        => 'nullable|numeric',
            'pbmc_dilution_factor'         => 'nullable|numeric',

            'counting_resuspension'        => 'nullable|numeric',
            'cell_count_concentration'     => 'nullable|numeric',
            'total_cell_number'            => 'nullable|numeric',
            'final_cps_resuspension_volume'=> 'nullable|numeric',
            'viability_percent'            => 'nullable|numeric',

            'auto_system_clean_done'            => 'nullable|boolean',
            'auto_qc_passed'                    => 'nullable|boolean',
            'auto_viability_percent'            => 'nullable|numeric',
            'auto_total_viable_cells_original'  => 'nullable|integer',
            'auto_total_cells_original'         => 'nullable|integer',
            'auto_total_cryovials_frozen'       => 'nullable|integer',

            'frosty_storage_temp'  => 'nullable|numeric',
            'frosty_date'          => 'nullable|date',
            'frosty_time'          => 'nullable|date_format:H:i',
            'frosty_transfer'      => 'nullable|string',

            'ln2_transfer_first'    => 'nullable|string|max:100',
            'ln2_transfer_last'     => 'nullable|string|max:100',
            'ln2_transfer_datetime' => 'nullable|date',

            'auto_comment' => 'nullable|string',
        ];
    }
}

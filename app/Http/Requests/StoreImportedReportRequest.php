<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportedReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'study_code'                        => ['required', 'string', 'max:50'],
            'sample_id_visit_number'            => ['required', 'string', 'max:32'],
            'participant_id'                    => ['nullable', 'string', 'max:24'],
            'visit_code'                        => ['nullable', 'string', 'max:16'],
            'report_date'                       => ['nullable', 'date'],
            'total_blood_volume_ml'             => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'blood_draw_time'                   => ['nullable', 'date_format:H:i'],
            'sample_condition'                  => ['nullable', 'string', 'max:20'],
            'sample_tube_type'                  => ['nullable', 'string', 'max:50'],
            'plasma_harvesting'                 => ['nullable', 'string', 'max:50'],
            'counting_method'                   => ['nullable', 'string', 'max:50'],
            'dilution_factor'                   => ['nullable', 'numeric', 'min:1', 'max:99999'],
            'viability_percent'                 => ['nullable', 'numeric', 'min:0', 'max:100'],
            'viable_cells_per_ml_millions'      => ['nullable', 'numeric', 'min:0'],
            'resuspension_volume_ml'            => ['nullable', 'numeric', 'min:0'],
            'total_viable_cells_millions'       => ['nullable', 'numeric', 'min:0'],
            'cell_yield_per_ml_blood'           => ['nullable', 'numeric', 'min:0'],
            'final_cps_volume_ml'               => ['nullable', 'numeric', 'min:0'],
            'actual_cells_per_vial_millions'    => ['nullable', 'numeric', 'min:0'],
            'cryovials_frozen'                  => ['nullable', 'integer', 'min:0', 'max:65535'],
            'lab_processing_start_time'         => ['nullable', 'date_format:H:i'],
            'freezing_time'                     => ['nullable', 'date_format:H:i'],
            'processing_to_freezing_duration'   => ['nullable', 'date_format:H:i'],
            'blood_draw_to_freezing_duration'   => ['nullable', 'date_format:H:i'],
            'operator_initials'                 => ['nullable', 'string', 'max:16'],
            'comments'                          => ['nullable', 'string'],
        ];
    }
}

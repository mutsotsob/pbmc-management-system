<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Iavic114PbmcReport extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'study_code',
        'source_workbook',
        'source_sheet',
        'source_row_number',
        'sample_id_visit_number',
        'participant_id',
        'visit_code',
        'report_date',
        'total_blood_volume_ml',
        'blood_draw_time',
        'sample_condition',
        'sample_tube_type',
        'plasma_harvesting',
        'counting_method',
        'dilution_factor',
        'viability_percent',
        'viable_cells_per_ml_millions',
        'resuspension_volume_ml',
        'total_viable_cells_millions',
        'cell_yield_per_ml_blood',
        'final_cps_volume_ml',
        'actual_cells_per_vial_millions',
        'cryovials_frozen',
        'lab_processing_start_time',
        'freezing_time',
        'processing_to_freezing_minutes',
        'blood_draw_to_freezing_minutes',
        'operator_initials',
        'comments',
        'raw_payload',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'report_date' => 'date',
        'total_blood_volume_ml' => 'decimal:2',
        'dilution_factor' => 'decimal:1',
        'viability_percent' => 'decimal:2',
        'viable_cells_per_ml_millions' => 'decimal:3',
        'resuspension_volume_ml' => 'decimal:2',
        'total_viable_cells_millions' => 'decimal:2',
        'cell_yield_per_ml_blood' => 'decimal:3',
        'final_cps_volume_ml' => 'decimal:2',
        'actual_cells_per_vial_millions' => 'decimal:2',
        'cryovials_frozen' => 'integer',
        'processing_to_freezing_minutes' => 'integer',
        'blood_draw_to_freezing_minutes' => 'integer',
        'raw_payload' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $report): void {
            if (blank($report->sample_id_visit_number)) {
                return;
            }

            if (blank($report->study_code)) {
                $report->study_code = 'IAVIC114';
            }

            if (preg_match('/^(?<participant>[^_]+)_(?<visit>[^_]+)$/', $report->sample_id_visit_number, $matches) !== 1) {
                return;
            }

            $report->participant_id ??= $matches['participant'];
            $report->visit_code ??= $matches['visit'];
        });
    }

    public function scopeForParticipant(Builder $query, string $participantId): Builder
    {
        return $query->where('participant_id', $participantId);
    }

    public function scopeForVisit(Builder $query, string $visitCode): Builder
    {
        return $query->where('visit_code', $visitCode);
    }

    public function scopePassing(Builder $query): Builder
    {
        return $query->where('sample_condition', 'Pass');
    }
}

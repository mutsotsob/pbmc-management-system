<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pbmc extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // Study Information
        'study_choice',
        'other_study_name',

        // PT Details
        'ptid',
        'visit',
        'collection_date',
        'collection_time',
        'process_start_date',
        'process_start_time',

        // Processing Data
        'processing_data',
        'plasma_harvesting',
        'sample_status',
        'counting_method',
        'usable_blood_volume',

        // Manual Cell Counting
        'manual_counts',
        'manual_count_start_time',
        'manual_count_stop_time',
        'haemocytometer_factor',
        'pbmc_dilution_factor',

        // Calculated Outcomes
        'counting_resuspension',
        'cell_count_concentration',
        'total_cell_number',
        'final_cps_resuspension_volume',
        'viability_percent',

        // Automated Cell Count
        'auto_system_clean_done',
        'auto_qc_passed',
        'auto_viability_percent',
        'auto_total_viable_cells_original',
        'auto_total_cells_original',
        'auto_total_cryovials_frozen',

        // Stratacooler / Mr Frosty
        'frosty_storage_temp',
        'frosty_date',
        'frosty_time',
        'frosty_transfer',

        // LN2 Transfer
        'ln2_transfer_first',
        'ln2_transfer_last',
        'ln2_transfer_datetime',
        'auto_comment',
        
        // ACRN Import Tracking
        'imported_from_acrn',
    ];

    /**
     * Attribute casting (MATCHES DATABASE TYPES)
     */
    protected $casts = [
        // Dates & Times
        'collection_date' => 'date',
        'process_start_date' => 'date',
        'frosty_date' => 'date',
        'ln2_transfer_datetime' => 'datetime',

        // TIME columns (keep as strings)
        'collection_time' => 'string',
        'process_start_time' => 'string',
        'manual_count_start_time' => 'string',
        'manual_count_stop_time' => 'string',
        'frosty_time' => 'string',

        // JSON
        'sample_status' => 'array',
        'manual_counts' => 'array',

        // Booleans
        'plasma_harvesting' => 'boolean',
        'auto_system_clean_done' => 'boolean',
        'auto_qc_passed' => 'boolean',
        'imported_from_acrn' => 'boolean',

        // Decimals
        'usable_blood_volume' => 'decimal:2',
        'haemocytometer_factor' => 'decimal:2',
        'pbmc_dilution_factor' => 'decimal:2',
        'counting_resuspension' => 'decimal:2',
        'cell_count_concentration' => 'decimal:2',
        'total_cell_number' => 'decimal:2',
        'final_cps_resuspension_volume' => 'decimal:3',
        'viability_percent' => 'decimal:2',
        'auto_viability_percent' => 'decimal:2',
        'frosty_storage_temp' => 'decimal:2',

        // Integers
        'auto_total_viable_cells_original' => 'integer',
        'auto_total_cells_original' => 'integer',
        'auto_total_cryovials_frozen' => 'integer',
    ];

    /**
     * Relationships
     */
    public function reagents(): HasMany
    {
        return $this->hasMany(PbmcReagent::class);
    }

    public function washes(): HasMany
    {
        return $this->hasMany(PbmcWash::class)->orderBy('wash_number');
    }

    /**
     * Accessors
     */
    public function getStudyNameAttribute(): string
    {
        return $this->study_choice === 'Other'
            ? (string) $this->other_study_name
            : (string) $this->study_choice;
    }

    public function getLn2TransferNameAttribute(): ?string
    {
        if (!$this->ln2_transfer_first && !$this->ln2_transfer_last) {
            return null;
        }

        return trim("{$this->ln2_transfer_first} {$this->ln2_transfer_last}");
    }

    /**
     * Domain helpers
     */
    public function isManualCounting(): bool
    {
        return $this->counting_method === 'Manual Count';
    }

    public function isAutomatedCounting(): bool
    {
        return $this->counting_method === 'Automated';
    }

    /**
     * Manual count statistics (derived, not persisted)
     */
    public function getManualCountStats(): ?array
    {
        if (empty($this->manual_counts) || !is_array($this->manual_counts)) {
            return null;
        }

        $totalNonViable = 0;
        $totalViable = 0;
        $count = 0;

        foreach ($this->manual_counts as $square) {
            if (
                isset($square['nonviable'], $square['viable']) &&
                is_numeric($square['nonviable']) &&
                is_numeric($square['viable'])
            ) {
                $totalNonViable += (float) $square['nonviable'];
                $totalViable += (float) $square['viable'];
                $count++;
            }
        }

        if ($count === 0) {
            return null;
        }

        $avgNonViable = $totalNonViable / $count;
        $avgViable = $totalViable / $count;
        $avgTotal = $avgNonViable + $avgViable;

        return [
            'avg_nonviable' => round($avgNonViable, 2),
            'avg_viable' => round($avgViable, 2),
            'avg_total' => round($avgTotal, 2),
            'viability_percent' => $avgTotal > 0
                ? round(($avgViable / $avgTotal) * 100, 2)
                : 0.0,
        ];
    }

    /**
     * Query scopes
     */
    public function scopeByStudy($query, string $study)
    {
        return $query->where('study_choice', $study);
    }

    public function scopeByPtid($query, string $ptid)
    {
        return $query->where('ptid', $ptid);
    }

    public function scopeCollectedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('collection_date', [$startDate, $endDate]);
    }

    public function scopeViable($query)
    {
        return $query->where(function ($q) {
            $q->where('viability_percent', '>=', 80)
              ->orWhere('auto_viability_percent', '>=', 80);
        });
    }
}
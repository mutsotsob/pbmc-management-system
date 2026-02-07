<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcrnPbmc extends Model
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'acrn_postgres';
    
    /**
     * The table associated with the model.
     */
    protected $table = 'acrn_pbmc';
    
    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'pbmc_id';
    
    /**
     * The "type" of the primary key ID.
     */
    protected $keyType = 'string';
    
    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pbmc_id',
        'sample_id',
        'visit_number',
        'sample_date',
        'total_blood_volume_ml',
        'blood_draw_time',
        'sample_condition',
        'viability_percent',
        'viable_cells_per_ml',
        'resuspension_volume_ml',
        'total_viable_cells',
        'cell_yield_per_ml',
        'cells_per_vial',
        'cryovials_frozen',
        'lab_processing_start_time',
        'freezing_time',
        'processing_to_freezing_duration',
        'blood_draw_to_freezing_duration',
        'operator_initials',
        'comments',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        // sample_date is text in "28-Jul-25" format, not a proper date field
        'total_blood_volume_ml' => 'decimal:2',
        'blood_draw_time' => 'datetime:H:i:s',
        'viability_percent' => 'decimal:2',
        'viable_cells_per_ml' => 'decimal:2',
        'resuspension_volume_ml' => 'decimal:2',
        'total_viable_cells' => 'decimal:2',
        'cell_yield_per_ml' => 'decimal:2',
        'cells_per_vial' => 'decimal:2',
        'cryovials_frozen' => 'integer',
        'lab_processing_start_time' => 'datetime:H:i:s',
        'freezing_time' => 'datetime:H:i:s',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
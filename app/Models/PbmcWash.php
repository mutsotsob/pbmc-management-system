<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PbmcWash extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pbmc_id',
        'wash_number',
        'start_time',
        'stop_time',
        'volume',
        'centrifuge_id',
        'centrifuge_speed',
    ];

    /**
     * Attribute casting (MATCHES MIGRATION)
     */
    protected $casts = [
        // TIME columns → keep as strings
        'start_time' => 'string',
        'stop_time' => 'string',

        // Numerics
        'wash_number' => 'integer',
        'volume' => 'decimal:2',
        'centrifuge_speed' => 'integer',
    ];

    /**
     * Relationship: owning PBMC
     */
    public function pbmc(): BelongsTo
    {
        return $this->belongsTo(Pbmc::class);
    }

    /**
     * Derived attribute: wash duration in minutes
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->start_time || !$this->stop_time) {
            return null;
        }

        try {
            $start = \Carbon\Carbon::createFromFormat('H:i:s', $this->start_time);
            $stop  = \Carbon\Carbon::createFromFormat('H:i:s', $this->stop_time);

            return $start->diffInMinutes($stop);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Scope: filter by wash number
     */
    public function scopeWashNumber($query, int $number)
    {
        return $query->where('wash_number', $number);
    }
}

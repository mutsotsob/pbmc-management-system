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
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_time' => 'datetime',
        'stop_time' => 'datetime',
        'wash_number' => 'integer',
    ];

    /**
     * Get the PBMC that owns the wash.
     */
    public function pbmc(): BelongsTo
    {
        return $this->belongsTo(Pbmc::class);
    }

    /**
     * Calculate duration in minutes.
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->start_time || !$this->stop_time) {
            return null;
        }

        return $this->start_time->diffInMinutes($this->stop_time);
    }

    /**
     * Scope to filter by wash number.
     */
    public function scopeWashNumber($query, int $number)
    {
        return $query->where('wash_number', $number);
    }
}
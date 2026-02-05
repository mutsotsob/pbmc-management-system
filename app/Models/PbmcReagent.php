<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PbmcReagent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'pbmc_id',
        'name',
        'lot',
        'expiry',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'expiry' => 'date',
    ];

    /**
     * Get the PBMC that owns the reagent.
     */
    public function pbmc(): BelongsTo
    {
        return $this->belongsTo(Pbmc::class);
    }

    /**
     * Check if the reagent is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry && $this->expiry->isPast();
    }

    /**
     * Scope to filter expired reagents.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry')
                     ->where('expiry', '<', now());
    }

    /**
     * Scope to filter by reagent name.
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }
}
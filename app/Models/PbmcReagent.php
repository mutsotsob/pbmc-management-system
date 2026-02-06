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
     * Attribute casting
     */
    protected $casts = [
        'expiry' => 'date',
    ];

    /**
     * Relationship: owning PBMC
     */
    public function pbmc(): BelongsTo
    {
        return $this->belongsTo(Pbmc::class);
    }

    /**
     * Determine if the reagent is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry !== null && $this->expiry->isPast();
    }

    /**
     * Accessor: human-friendly expiry status
     */
    public function getExpiryStatusAttribute(): string
    {
        if ($this->expiry === null) {
            return 'Unknown';
        }

        return $this->isExpired() ? 'Expired' : 'Valid';
    }

    /**
     * Scope: expired reagents
     */
    public function scopeExpired($query)
    {
        return $query
            ->whereNotNull('expiry')
            ->where('expiry', '<', now());
    }

    /**
     * Scope: valid (non-expired) reagents
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry')
              ->orWhere('expiry', '>=', now());
        });
    }

    /**
     * Scope: filter by reagent name
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }
}

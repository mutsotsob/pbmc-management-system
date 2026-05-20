<?php

namespace App\Models;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SampleDispatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'dispatch_date',
        'dispatch_time',
        'sample_id',
        'study',
        'origin_location',
        'quantity',
        'destination',
        'driver_user_id',
        'driver_name',
        'driver_phone',
        'vehicle_registration',
        'dispatched_by_user_id',
        'status',
        'notes',
        'received_at',
        'received_by_user_id',
        'condition_on_arrival',
        'rejection_reason',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
        'received_at'   => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $dispatch) {
            if (!$dispatch->reference) {
                $today = now()->format('Ymd');
                $seq   = static::whereDate('created_at', today())->withTrashed()->count() + 1;
                $dispatch->reference = 'SD-' . $today . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function driverUser(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_user_id');
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by_user_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function scopeDispatched($query)
    {
        return $query->where('status', 'dispatched');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }
}

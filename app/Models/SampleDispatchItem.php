<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleDispatchItem extends Model
{
    protected $fillable = [
        'sample_dispatch_id',
        'participant_id',
    ];

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(SampleDispatch::class, 'sample_dispatch_id');
    }
}

<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\SampleDispatch;

class SampleDispatchObserver
{
    private const EXCLUDED = ['updated_at'];

    public function created(SampleDispatch $dispatch): void
    {
        AuditLog::record('created', $dispatch, [], $dispatch->getAttributes());
    }

    public function updated(SampleDispatch $dispatch): void
    {
        $dirty = collect($dispatch->getDirty())
            ->except(self::EXCLUDED)
            ->keys()
            ->toArray();

        if (empty($dirty)) {
            return;
        }

        $old = collect($dispatch->getOriginal())->only($dirty)->toArray();
        $new = collect($dispatch->getAttributes())->only($dirty)->toArray();

        // Label a status change as its own event for clearer audit trail
        $event = (count($dirty) === 1 && $dirty[0] === 'status') ? 'status_changed' : 'updated';

        AuditLog::record($event, $dispatch, $old, $new);
    }

    public function deleted(SampleDispatch $dispatch): void
    {
        AuditLog::record('deleted', $dispatch);
    }

    public function restored(SampleDispatch $dispatch): void
    {
        AuditLog::record('restored', $dispatch);
    }
}

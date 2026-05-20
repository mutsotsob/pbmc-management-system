<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Pbmc;

class PbmcObserver
{
    // Fields too large/noisy to diff on every update
    private const EXCLUDED_FROM_DIFF = ['manual_counts', 'sample_status', 'raw_payload', 'updated_at'];

    public function created(Pbmc $pbmc): void
    {
        AuditLog::record('created', $pbmc, [], $this->sanitize($pbmc->getAttributes()));
    }

    public function updated(Pbmc $pbmc): void
    {
        $dirty = collect($pbmc->getDirty())
            ->except(self::EXCLUDED_FROM_DIFF)
            ->keys();

        if ($dirty->isEmpty()) {
            return;
        }

        $old = collect($pbmc->getOriginal())->only($dirty)->all();
        $new = collect($pbmc->getAttributes())->only($dirty)->all();

        AuditLog::record('updated', $pbmc, $old, $new);
    }

    public function deleted(Pbmc $pbmc): void
    {
        AuditLog::record('deleted', $pbmc, ['ptid' => $pbmc->ptid, 'visit' => $pbmc->visit]);
    }

    public function restored(Pbmc $pbmc): void
    {
        AuditLog::record('restored', $pbmc, [], ['ptid' => $pbmc->ptid, 'visit' => $pbmc->visit]);
    }

    private function sanitize(array $attrs): array
    {
        return collect($attrs)->except(self::EXCLUDED_FROM_DIFF)->all();
    }
}

<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Iavic114PbmcReport;

class Iavic114PbmcReportObserver
{
    private const EXCLUDED = ['raw_payload', 'updated_at'];

    public function created(Iavic114PbmcReport $report): void
    {
        AuditLog::record('created', $report, [], $this->sanitize($report->getAttributes()));
    }

    public function updated(Iavic114PbmcReport $report): void
    {
        $dirty = collect($report->getDirty())
            ->except(self::EXCLUDED)
            ->keys();

        if ($dirty->isEmpty()) {
            return;
        }

        $old = collect($report->getOriginal())->only($dirty)->all();
        $new = collect($report->getAttributes())->only($dirty)->all();

        AuditLog::record('updated', $report, $old, $new);
    }

    public function deleted(Iavic114PbmcReport $report): void
    {
        AuditLog::record('deleted', $report, [
            'sample_id_visit_number' => $report->sample_id_visit_number,
            'source_workbook'        => $report->source_workbook,
        ]);
    }

    public function restored(Iavic114PbmcReport $report): void
    {
        AuditLog::record('restored', $report, [], [
            'sample_id_visit_number' => $report->sample_id_visit_number,
        ]);
    }

    private function sanitize(array $attrs): array
    {
        return collect($attrs)->except(self::EXCLUDED)->all();
    }
}

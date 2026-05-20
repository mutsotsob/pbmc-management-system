<?php

namespace App\Http\Controllers;

use App\Models\Iavic114PbmcReport;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class AnalyticsController extends Controller
{
    /**
     * Display analytics dashboard.
     */
    public function index()
    {
        $stats = $this->getAnalyticsData();

        return view('analytics.index', compact('stats'));
    }

    /**
     * Build dashboard metrics from imported IAVIC114 PBMC reports.
     */
    private function getAnalyticsData(?string $filter = null): array
    {
        $query = Iavic114PbmcReport::query();
        $this->applyFilter($query, $filter);

        $reports = $query
            ->orderBy('report_date')
            ->get();

        $totalRecords = $reports->count();
        $reportsWithViability = $reports->filter(fn (Iavic114PbmcReport $report) => $report->viability_percent !== null);
        $reportsWithComments = $reports->filter(fn (Iavic114PbmcReport $report) => filled($report->comments));
        $passingReports = $reports->filter(function (Iavic114PbmcReport $report): bool {
            return strcasecmp((string) $report->sample_condition, 'Pass') === 0;
        });

        $highViability = $reportsWithViability->filter(fn (Iavic114PbmcReport $report) => (float) $report->viability_percent >= 80)->count();
        $mediumViability = $reportsWithViability->filter(fn (Iavic114PbmcReport $report) => (float) $report->viability_percent >= 60 && (float) $report->viability_percent < 80)->count();
        $lowViability = $reportsWithViability->filter(fn (Iavic114PbmcReport $report) => (float) $report->viability_percent < 60)->count();

        $conditionGroups = $reports
            ->groupBy(fn (Iavic114PbmcReport $report) => $report->sample_condition ?: 'Unknown')
            ->map->count()
            ->sortDesc();

        $visitGroups = $reports
            ->groupBy(fn (Iavic114PbmcReport $report) => $report->visit_code ?: 'Unknown')
            ->map->count()
            ->sortDesc()
            ->take(8);

        $operatorPerformance = $reports
            ->filter(fn (Iavic114PbmcReport $report) => filled($report->operator_initials))
            ->groupBy('operator_initials')
            ->map(function (Collection $group, string $operator): array {
                $viabilityReports = $group->filter(fn (Iavic114PbmcReport $report) => $report->viability_percent !== null);

                return [
                    'operator' => $operator,
                    'count' => $group->count(),
                    'avg_viability' => round((float) ($viabilityReports->avg(fn (Iavic114PbmcReport $report) => (float) $report->viability_percent) ?? 0), 1),
                    'avg_processing_minutes' => round((float) ($group->avg(fn (Iavic114PbmcReport $report) => $report->processing_to_freezing_minutes !== null ? (int) $report->processing_to_freezing_minutes : null) ?? 0)),
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        $timelineGroups = $reports
            ->filter(fn (Iavic114PbmcReport $report) => $report->report_date !== null)
            ->groupBy(fn (Iavic114PbmcReport $report) => $report->report_date->format('Y-m'))
            ->sortKeys()
            ->take(-12);

        $timelineLabels = [];
        $timelineCounts = [];
        $timelineAvgViability = [];

        foreach ($timelineGroups as $month => $group) {
            $timelineLabels[] = Carbon::createFromFormat('Y-m', $month)->format('M Y');
            $timelineCounts[] = $group->count();
            $timelineAvgViability[] = round((float) ($group->avg(fn (Iavic114PbmcReport $report) => $report->viability_percent !== null ? (float) $report->viability_percent : null) ?? 0), 1);
        }

        $participantGroups = $reports
            ->filter(fn (Iavic114PbmcReport $report) => filled($report->participant_id))
            ->groupBy('participant_id')
            ->map(function (Collection $group, string $participant): array {
                return [
                    'participant' => $participant,
                    'count' => $group->count(),
                    'avg_viability' => round((float) ($group->avg(fn (Iavic114PbmcReport $report) => $report->viability_percent !== null ? (float) $report->viability_percent : null) ?? 0), 1),
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        $completeRecords = $reports->filter(function (Iavic114PbmcReport $report): bool {
            return filled($report->sample_id_visit_number)
                && $report->report_date !== null
                && $report->viability_percent !== null
                && $report->sample_condition !== null
                && $report->cryovials_frozen !== null
                && $report->processing_to_freezing_minutes !== null
                && $report->blood_draw_to_freezing_minutes !== null;
        })->count();

        $recent30Days = $reports->filter(function (Iavic114PbmcReport $report): bool {
            return $report->report_date !== null && $report->report_date->gte(now()->subDays(30)->startOfDay());
        })->count();

        $thisYear = $reports->filter(function (Iavic114PbmcReport $report): bool {
            return $report->report_date !== null && $report->report_date->year === now()->year;
        })->count();

        return [
            'total_records' => $totalRecords,
            'study_code' => 'IAVIC114',
            'unique_participants' => $reports->pluck('participant_id')->filter()->unique()->count(),
            'unique_visits' => $reports->pluck('visit_code')->filter()->unique()->count(),
            'avg_viability' => round((float) ($reportsWithViability->avg(fn (Iavic114PbmcReport $report) => (float) $report->viability_percent) ?? 0), 1),
            'avg_total_viable_cells' => round((float) ($reports->avg(fn (Iavic114PbmcReport $report) => $report->total_viable_cells_millions !== null ? (float) $report->total_viable_cells_millions : null) ?? 0), 2),
            'avg_cryovials' => round((float) ($reports->avg(fn (Iavic114PbmcReport $report) => $report->cryovials_frozen !== null ? (int) $report->cryovials_frozen : null) ?? 0), 1),
            'avg_processing_to_freezing' => round((float) ($reports->avg(fn (Iavic114PbmcReport $report) => $report->processing_to_freezing_minutes !== null ? (int) $report->processing_to_freezing_minutes : null) ?? 0)),
            'avg_blood_draw_to_freezing' => round((float) ($reports->avg(fn (Iavic114PbmcReport $report) => $report->blood_draw_to_freezing_minutes !== null ? (int) $report->blood_draw_to_freezing_minutes : null) ?? 0)),
            'pass_count' => $passingReports->count(),
            'pass_rate' => $totalRecords > 0 ? round(($passingReports->count() / $totalRecords) * 100, 1) : 0,
            'with_comments' => $reportsWithComments->count(),
            'complete_records' => $completeRecords,
            'last_30_days' => $recent30Days,
            'this_year' => $thisYear,
            'viability_high' => $highViability,
            'viability_medium' => $mediumViability,
            'viability_low' => $lowViability,
            'condition_labels' => $conditionGroups->keys()->values()->all(),
            'condition_counts' => $conditionGroups->values()->all(),
            'visit_labels' => $visitGroups->keys()->values()->all(),
            'visit_counts' => $visitGroups->values()->all(),
            'timeline_labels' => $timelineLabels,
            'timeline_counts' => $timelineCounts,
            'timeline_avg_viability' => $timelineAvgViability,
            'operator_performance' => $operatorPerformance->all(),
            'participant_leaders' => $participantGroups->all(),
        ];
    }

    /**
     * Get filtered analytics summary for lightweight refreshes.
     */
    public function getFilteredData(string $filter = 'all'): JsonResponse
    {
        $stats = $this->getAnalyticsData($filter);

        return response()->json([
            'total_records' => $stats['total_records'],
            'avg_viability' => $stats['avg_viability'],
            'pass_rate' => $stats['pass_rate'],
            'last_30_days' => $stats['last_30_days'],
        ]);
    }

    private function applyFilter($query, ?string $filter): void
    {
        match ($filter) {
            'week' => $query->whereDate('report_date', '>=', now()->subWeek()->toDateString()),
            'month' => $query->whereDate('report_date', '>=', now()->subMonth()->toDateString()),
            '6m' => $query->whereDate('report_date', '>=', now()->subMonths(6)->toDateString()),
            '1y' => $query->whereDate('report_date', '>=', now()->subYear()->toDateString()),
            default => null,
        };
    }
}

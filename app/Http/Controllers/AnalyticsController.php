<?php

namespace App\Http\Controllers;

use App\Models\Pbmc;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Display analytics dashboard
     */
    public function index()
    {
        $stats = $this->getAnalyticsData();
        
        return view('analytics.index', compact('stats'));
    }

    /**
     * Get comprehensive analytics data
     */
    private function getAnalyticsData(): array
    {
        // Basic counts
        $totalRecords = Pbmc::count();
        $acrnCount = Pbmc::where('imported_from_acrn', true)->count();
        $manualCount = $totalRecords - $acrnCount;

        // Viability statistics
        $viabilityData = Pbmc::select(
            DB::raw('COALESCE(viability_percent, auto_viability_percent) as viability')
        )
        ->whereNotNull(DB::raw('COALESCE(viability_percent, auto_viability_percent)'))
        ->get();

        $avgViability = $viabilityData->avg('viability') ?? 0;
        
        $viabilityHigh = $viabilityData->filter(fn($p) => $p->viability >= 80)->count();
        $viabilityMedium = $viabilityData->filter(fn($p) => $p->viability >= 60 && $p->viability < 80)->count();
        $viabilityLow = $viabilityData->filter(fn($p) => $p->viability < 60)->count();
        
        $viableCount = $viabilityHigh;
        $viablePercentage = $totalRecords > 0 ? ($viableCount / $totalRecords) * 100 : 0;

        // Cell count totals
        $totalCells = Pbmc::sum('total_cell_number') ?? 0;
        if ($totalCells == 0) {
            $totalCells = Pbmc::sum('auto_total_viable_cells_original') ?? 0;
        }

        // Counting method breakdown
        $automatedCount = Pbmc::where('counting_method', 'Automated')->count();
        $manualCountMethod = Pbmc::where('counting_method', 'Manual Count')->count();

        // Study distribution
        $studyData = Pbmc::select(
            DB::raw('CASE WHEN study_choice = "Other" THEN COALESCE(other_study_name, "Other") ELSE study_choice END as study_name'),
            DB::raw('COUNT(*) as count'),
            DB::raw('AVG(COALESCE(viability_percent, auto_viability_percent)) as avg_viability')
        )
        ->groupBy('study_name')
        ->orderByDesc('count')
        ->get();

        $studyLabels = $studyData->pluck('study_name')->toArray();
        $studyCounts = $studyData->pluck('count')->toArray();
        
        // Top 5 studies for leaderboard
        $topStudies = $studyData->take(5);

        // Timeline data (last 12 months)
        $timelineData = Pbmc::select(
            DB::raw('DATE_FORMAT(collection_date, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(CASE WHEN COALESCE(viability_percent, auto_viability_percent) >= 80 THEN 1 ELSE 0 END) as viable_count')
        )
        ->whereNotNull('collection_date')
        ->where('collection_date', '>=', Carbon::now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $timelineLabels = $timelineData->pluck('month')->map(function($month) {
            return Carbon::parse($month . '-01')->format('M Y');
        })->toArray();
        $timelineCounts = $timelineData->pluck('count')->toArray();
        $timelineViable = $timelineData->pluck('viable_count')->toArray();

        // Recent activity
        $last7Days = Pbmc::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $last30Days = Pbmc::where('created_at', '>=', Carbon::now()->subDays(30))->count();
        $thisYear = Pbmc::whereYear('created_at', Carbon::now()->year)->count();

        // Data quality metrics
        $completeRecords = Pbmc::whereNotNull('ptid')
            ->whereNotNull('collection_date')
            ->whereNotNull('visit')
            ->whereNotNull(DB::raw('COALESCE(viability_percent, auto_viability_percent)'))
            ->count();
        
        $withComments = Pbmc::whereNotNull('auto_comment')
            ->where('auto_comment', '!=', '')
            ->count();

        return [
            // Overview
            'total_records' => $totalRecords,
            'acrn_count' => $acrnCount,
            'manual_count' => $manualCount,
            
            // Viability
            'avg_viability' => $avgViability,
            'viability_high' => $viabilityHigh,
            'viability_medium' => $viabilityMedium,
            'viability_low' => $viabilityLow,
            'viable_count' => $viableCount,
            'viable_percentage' => $viablePercentage,
            
            // Cell counts
            'total_cells' => $totalCells,
            
            // Methods
            'automated_count' => $automatedCount,
            'manual_count' => $manualCountMethod,
            
            // Studies
            'study_labels' => $studyLabels,
            'study_counts' => $studyCounts,
            'top_studies' => $topStudies,
            
            // Timeline
            'timeline_labels' => $timelineLabels,
            'timeline_counts' => $timelineCounts,
            'timeline_viable' => $timelineViable,
            
            // Activity
            'last_7_days' => $last7Days,
            'last_30_days' => $last30Days,
            'this_year' => $thisYear,
            
            // Quality
            'complete_records' => $completeRecords,
            'with_comments' => $withComments,
        ];
    }

    /**
     * Get filtered analytics data (for AJAX requests)
     */
    public function getFilteredData($filter = 'all')
    {
        // Implementation for dynamic filtering
        $query = Pbmc::query();
        
        switch ($filter) {
            case 'week':
                $query->where('created_at', '>=', Carbon::now()->subWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', Carbon::now()->subMonth());
                break;
            case '6m':
                $query->where('created_at', '>=', Carbon::now()->subMonths(6));
                break;
            case '1y':
                $query->where('created_at', '>=', Carbon::now()->subYear());
                break;
        }
        
        return response()->json([
            'total' => $query->count(),
            'viable' => $query->where(function($q) {
                $q->where('viability_percent', '>=', 80)
                  ->orWhere('auto_viability_percent', '>=', 80);
            })->count()
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Iavic114PbmcReport;
use App\Models\Pbmc;
use App\Models\SampleDispatch;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        if ($this->isAdministrationUser($user)) {
            return $this->administrationDashboard($request);
        }

        if ($this->isLaboratoryUser($user)) {
            return $this->laboratoryDashboard($request);
        }

        $pbmcAllowedSorts = ['ptid', 'visit', 'collection_date', 'viability_percent', 'imported_from_acrn', 'created_at'];

        $sort = $request->query('sort', 'collection_date');
        $dir  = $request->query('dir', 'desc') === 'asc' ? 'asc' : 'desc';

        if (!in_array($sort, $pbmcAllowedSorts, true)) {
            $sort = 'collection_date';
        }

        $pbmcs = Pbmc::orderBy($sort, $dir)->paginate(15)->withQueryString();

        $reportAllowedSorts = ['sample_id_visit_number', 'report_date', 'viability_percent', 'cryovials_frozen', 'operator_initials', 'created_at'];

        $reportSort = $request->query('report_sort', 'report_date');
        $reportDir  = $request->query('report_dir', 'desc') === 'asc' ? 'asc' : 'desc';

        if (!in_array($reportSort, $reportAllowedSorts, true)) {
            $reportSort = 'report_date';
        }

        $reportSampleId = trim((string) $request->query('report_sample_id', ''));
        $reportOperator = trim((string) $request->query('report_operator', ''));

        $iavicReports = Iavic114PbmcReport::query()
            ->when($reportSampleId !== '', fn ($query) => $query->where('sample_id_visit_number', 'like', '%' . $reportSampleId . '%'))
            ->when($reportOperator !== '', fn ($query) => $query->where('operator_initials', 'like', '%' . $reportOperator . '%'))
            ->orderBy($reportSort, $reportDir)
            ->paginate(15, ['*'], 'reports_page')
            ->withQueryString();

        $isAdmin = $user?->isAdmin() ?? false;

        return view('dashboard', compact(
            'pbmcs',
            'sort',
            'dir',
            'iavicReports',
            'reportSort',
            'reportDir',
            'reportSampleId',
            'reportOperator',
            'user',
            'isAdmin',
        ));
    }

    private function administrationDashboard(Request $request)
    {
        $user = $request->user();
        $from = $request->date('from');
        $to = $request->date('to');
        $allowedSorts = [
            'reference',
            'dispatch_date',
            'study',
            'sample_id',
            'no_of_bags',
            'origin_location',
            'status',
            'received_at',
        ];

        $sort = $request->query('sort', 'dispatch_date');
        $dir = $request->query('dir', 'desc') === 'asc' ? 'asc' : 'desc';

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'dispatch_date';
        }

        $baseQuery = SampleDispatch::query()
            ->with(['items', 'dispatchedBy', 'receivedBy'])
            ->where('driver_user_id', $user->id)
            ->when($from, fn ($query) => $query->whereDate('dispatch_date', '>=', $from->toDateString()))
            ->when($to, fn ($query) => $query->whereDate('dispatch_date', '<=', $to->toDateString()));

        $dispatches = (clone $baseQuery)
            ->when(
                $sort === 'origin_location',
                fn ($query) => $query->orderBy('origin_location', $dir)->orderBy('destination', $dir),
                fn ($query) => $query->orderBy($sort, $dir)
            )
            ->when($sort !== 'dispatch_date', fn ($query) => $query->orderByDesc('dispatch_date'))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $allDispatches = (clone $baseQuery)->get();
        $receivedDispatches = $allDispatches->where('status', 'received');
        $pendingDispatches = $allDispatches->where('status', 'dispatched');
        $totalSamples = $allDispatches->sum(fn (SampleDispatch $dispatch) => max((int) $dispatch->quantity, $dispatch->items->count(), 1));
        $totalBags = $allDispatches->sum(fn (SampleDispatch $dispatch) => (int) ($dispatch->no_of_bags ?? 0));
        $thisMonthCount = $allDispatches
            ->filter(fn (SampleDispatch $dispatch) => $dispatch->dispatch_date?->isSameMonth(now()))
            ->count();

        $completionRate = $allDispatches->isNotEmpty()
            ? round(($receivedDispatches->count() / $allDispatches->count()) * 100)
            : 0;

        $monthlyTrend = $allDispatches
            ->filter(fn (SampleDispatch $dispatch) => $dispatch->dispatch_date !== null)
            ->groupBy(fn (SampleDispatch $dispatch) => $dispatch->dispatch_date->format('Y-m'))
            ->sortKeys()
            ->take(-6)
            ->map(fn ($group, string $month) => [
                'label' => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'count' => $group->count(),
            ])
            ->values();

        $maxMonthlyDispatches = max((int) $monthlyTrend->max('count'), 1);

        return view('dashboard-administration', [
            'dispatches' => $dispatches,
            'from' => $from?->toDateString(),
            'to' => $to?->toDateString(),
            'sort' => $sort,
            'dir' => $dir,
            'metrics' => [
                'total_dispatches' => $allDispatches->count(),
                'received_dispatches' => $receivedDispatches->count(),
                'pending_dispatches' => $pendingDispatches->count(),
                'total_samples' => $totalSamples,
                'total_bags' => $totalBags,
                'this_month_count' => $thisMonthCount,
                'completion_rate' => $completionRate,
            ],
            'monthlyTrend' => $monthlyTrend,
            'maxMonthlyDispatches' => $maxMonthlyDispatches,
        ]);
    }

    private function laboratoryDashboard(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $requestedStatus = (string) $request->query('sample_status', 'dispatched');
        $sampleStatus = in_array($requestedStatus, ['dispatched', 'received', 'processed'], true)
            ? $requestedStatus
            : 'dispatched';
        $allowedSorts = [
            'reference',
            'dispatch_date',
            'study',
            'visit',
            'sample_id',
            'no_of_bags',
            'origin_location',
            'destination',
            'driver_name',
            'received_at',
            'created_at',
        ];

        $sort = $request->query('sort', $sampleStatus === 'dispatched' ? 'dispatch_date' : 'received_at');
        $dir = $request->query('dir', 'desc') === 'asc' ? 'asc' : 'desc';

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'dispatch_date';
        }

        $baseQuery = SampleDispatch::query()
            ->with(['items', 'dispatchedBy', 'driverUser', 'receivedBy'])
            ->where('status', $sampleStatus)
            ->when($q !== '', fn ($query) => $query->where(function ($subQuery) use ($q) {
                $like = '%' . $q . '%';

                $subQuery->where('reference', 'like', $like)
                    ->orWhere('sample_id', 'like', $like)
                    ->orWhere('study', 'like', $like)
                    ->orWhere('visit', 'like', $like)
                    ->orWhere('origin_location', 'like', $like)
                    ->orWhere('destination', 'like', $like)
                    ->orWhere('driver_name', 'like', $like)
                    ->orWhereHas('items', fn ($itemQuery) => $itemQuery->where('participant_id', 'like', $like))
                    ->orWhereHas('dispatchedBy', fn ($userQuery) => $userQuery->where('name', 'like', $like));
            }));

        $dispatches = (clone $baseQuery)
            ->orderBy($sort, $dir)
            ->when($sort !== 'dispatch_date', fn ($query) => $query->orderByDesc('dispatch_date'))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $awaitingReceiptCount = SampleDispatch::dispatched()->count();
        $receivedTodayCount = SampleDispatch::received()
            ->whereDate('received_at', today()->toDateString())
            ->count();
        $totalBagsAwaitingReceipt = SampleDispatch::dispatched()
            ->sum('no_of_bags');

        return view('dashboard-laboratory', [
            'dispatches' => $dispatches,
            'q' => $q,
            'sort' => $sort,
            'dir' => $dir,
            'sampleStatus' => $sampleStatus,
            'metrics' => [
                'awaiting_receipt' => $awaitingReceiptCount,
                'received_today' => $receivedTodayCount,
                'bags_awaiting_receipt' => $totalBagsAwaitingReceipt,
            ],
        ]);
    }

    private function isAdministrationUser($user): bool
    {
        return $user && $user->isDepartment('Administration');
    }

    private function isLaboratoryUser($user): bool
    {
        return $user && $user->isDepartment('Laboratory') && !$user->hasFullAccessDepartment();
    }
}

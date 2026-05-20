<?php

namespace App\Http\Controllers;

use App\Models\Iavic114PbmcReport;
use App\Models\Pbmc;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
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

        return view('dashboard', compact(
            'pbmcs',
            'sort',
            'dir',
            'iavicReports',
            'reportSort',
            'reportDir',
            'reportSampleId',
            'reportOperator',
        ));
    }
}

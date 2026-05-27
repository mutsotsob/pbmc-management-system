<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImportedReportRequest;
use App\Models\Iavic114PbmcReport;
use App\Models\User;
use App\Notifications\ReportImportedNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class Iavic114ReportController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['sample_id_visit_number', 'report_date', 'viability_percent', 'cryovials_frozen', 'operator_initials', 'created_at'];

        $reportSort = $request->query('report_sort', 'report_date');
        $reportDir = $request->query('report_dir', 'desc') === 'asc' ? 'asc' : 'desc';

        if (!in_array($reportSort, $allowedSorts, true)) {
            $reportSort = 'report_date';
        }

        $reportSampleId = trim((string) $request->query('report_sample_id', ''));
        $reportOperator = trim((string) $request->query('report_operator', ''));

        $iavicReports = Iavic114PbmcReport::query()
            ->when($reportSampleId !== '', fn ($query) => $query->where('sample_id_visit_number', 'like', '%' . $reportSampleId . '%'))
            ->when($reportOperator !== '', fn ($query) => $query->where('operator_initials', 'like', '%' . $reportOperator . '%'))
            ->orderBy($reportSort, $reportDir)
            ->paginate(15)
            ->withQueryString();

        return view('iavic114-reports.index', compact(
            'iavicReports',
            'reportSort',
            'reportDir',
            'reportSampleId',
            'reportOperator',
        ));
    }

    public function create()
    {
        $labOperators = User::query()
            ->where('user_status', true)
            ->where('department', 'like', '%Lab%')
            ->orderBy('name')
            ->get(['name', 'email'])
            ->map(fn (User $user) => [
                'initials' => $this->initialsForName($user->name),
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->filter(fn (array $operator) => $operator['initials'] !== '')
            ->values();

        return view('iavic114-reports.create', compact('labOperators'));
    }

    public function store(StoreImportedReportRequest $request)
    {
        $validated = $request->validated();

        $payload = [
            'study_code'                     => $validated['study_code'],
            'sample_id_visit_number'         => trim($validated['sample_id_visit_number']),
            'participant_id'                 => $this->nullableTrim($validated['participant_id'] ?? null),
            'visit_code'                     => $this->nullableTrim($validated['visit_code'] ?? null),
            'report_date'                    => $validated['report_date'] ?? null,
            'total_blood_volume_ml'          => $validated['total_blood_volume_ml'] ?? null,
            'blood_draw_time'                => $this->normalizeTime($validated['blood_draw_time'] ?? null),
            'sample_condition'               => $this->nullableTrim($validated['sample_condition'] ?? null),
            'sample_tube_type'               => $this->nullableTrim($validated['sample_tube_type'] ?? null),
            'plasma_harvesting'              => $this->nullableTrim($validated['plasma_harvesting'] ?? null),
            'counting_method'                => $this->nullableTrim($validated['counting_method'] ?? null),
            'dilution_factor'                => $validated['dilution_factor'] ?? 10,
            'viability_percent'              => $validated['viability_percent'] ?? null,
            'viable_cells_per_ml_millions'   => $validated['viable_cells_per_ml_millions'] ?? null,
            'resuspension_volume_ml'         => $validated['resuspension_volume_ml'] ?? null,
            'total_viable_cells_millions'    => $validated['total_viable_cells_millions'] ?? null,
            'cell_yield_per_ml_blood'        => $validated['cell_yield_per_ml_blood'] ?? null,
            'final_cps_volume_ml'            => $validated['final_cps_volume_ml'] ?? null,
            'actual_cells_per_vial_millions' => $validated['actual_cells_per_vial_millions'] ?? null,
            'cryovials_frozen'               => $validated['cryovials_frozen'] ?? null,
            'lab_processing_start_time'      => $this->normalizeTime($validated['lab_processing_start_time'] ?? null),
            'freezing_time'                  => $this->normalizeTime($validated['freezing_time'] ?? null),
            'processing_to_freezing_minutes' => $this->timeToMinutes($validated['processing_to_freezing_duration'] ?? null),
            'blood_draw_to_freezing_minutes' => $this->timeToMinutes($validated['blood_draw_to_freezing_duration'] ?? null),
            'operator_initials'              => $this->nullableTrim($validated['operator_initials'] ?? null),
            'comments'                       => $this->nullableTrim($validated['comments'] ?? null),
            'source_workbook'                => 'Manual Entry',
            'source_sheet'                   => 'Dashboard Form',
        ];

        $payload['raw_payload'] = Arr::except(array_merge($validated, [
            'blood_draw_time'           => $validated['blood_draw_time'] ?? null,
            'lab_processing_start_time' => $validated['lab_processing_start_time'] ?? null,
            'freezing_time'             => $validated['freezing_time'] ?? null,
        ]), []);

        $report = Iavic114PbmcReport::create($payload);

        $importer = Auth::user();
        User::where('user_type', 'admin')->get()->each->notify(
            new ReportImportedNotification($report->sample_id_visit_number, $importer?->name ?? 'System')
        );

        return redirect()
            ->route('iavic114-reports.show', $report)
            ->with('success', 'IAVIC114 report created successfully.');
    }

    public function show(Iavic114PbmcReport $iavic114PbmcReport)
    {
        return view('iavic114-reports.show', ['report' => $iavic114PbmcReport]);
    }

    public function printReport(Iavic114PbmcReport $iavic114PbmcReport): View
    {
        return view('iavic114-reports.print', [
            'report' => $iavic114PbmcReport,
            'generatedAt' => now(),
            'generatedBy' => Auth::user(),
        ]);
    }

    public function exportExcel()
    {
        $reports = Iavic114PbmcReport::query()
            ->orderByDesc('report_date')
            ->orderBy('sample_id_visit_number')
            ->get();

        return $this->downloadExcel($reports, 'iavic114_pbmc_reports_all_' . now()->format('Y_m_d_His') . '.xls');
    }

    public function exportSelectedExcel(Request $request)
    {
        $reports = $this->resolveSelected($request);

        if ($reports->isEmpty()) {
            return back()->with('error', 'Please select at least one report to export.');
        }

        return $this->downloadExcel($reports, 'iavic114_pbmc_reports_selected_' . now()->format('Y_m_d_His') . '.xls');
    }

    public function exportCsv()
    {
        $reports = Iavic114PbmcReport::query()
            ->orderByDesc('report_date')
            ->orderBy('sample_id_visit_number')
            ->get();

        return $this->downloadCsv($reports, 'iavic114_pbmc_reports_all_' . now()->format('Y_m_d_His') . '.csv');
    }

    public function exportSelectedCsv(Request $request)
    {
        $reports = $this->resolveSelected($request);

        if ($reports->isEmpty()) {
            return back()->with('error', 'Please select at least one report to export.');
        }

        return $this->downloadCsv($reports, 'iavic114_pbmc_reports_selected_' . now()->format('Y_m_d_His') . '.csv');
    }

    public function exportPdf(): View
    {
        $reports = Iavic114PbmcReport::query()
            ->orderByDesc('report_date')
            ->orderBy('sample_id_visit_number')
            ->get();

        return $this->renderPdf($reports, 'IAVIC114 Imported Reports');
    }

    public function exportSelectedPdf(Request $request)
    {
        $reports = $this->resolveSelected($request);

        if ($reports->isEmpty()) {
            return back()->with('error', 'Please select at least one report to export.');
        }

        return $this->renderPdf($reports, 'Selected IAVIC114 Imported Reports');
    }

    private function resolveSelected(Request $request)
    {
        $data = $request->validate([
            'selected_report_ids'   => ['required', 'array', 'min:1'],
            'selected_report_ids.*' => ['integer', 'exists:iavic114_pbmc_reports,id'],
        ]);

        return Iavic114PbmcReport::query()
            ->whereIn('id', $data['selected_report_ids'])
            ->orderByDesc('report_date')
            ->orderBy('sample_id_visit_number')
            ->get();
    }

    private function downloadExcel($reports, string $filename)
    {
        $content = view('iavic114-reports.exports.excel', compact('reports'))->render();

        return Response::make($content, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function renderPdf($reports, string $title): View
    {
        return view('iavic114-reports.exports.pdf', [
            'reports'     => $reports,
            'title'       => $title,
            'generatedAt' => now(),
        ]);
    }

    private function downloadCsv($reports, string $filename)
    {
        return Response::streamDownload(function () use ($reports) {
            $handle = fopen('php://output', 'w');

            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Study Code',
                'Sample ID / Visit Number',
                'Participant ID',
                'Visit Code',
                'Report Date',
                'Sample Condition',
                'Sample Tube Type',
                'Plasma Harvesting',
                'Counting Method',
                'Dilution Factor',
                'Total Blood Volume (mL)',
                'Blood Draw Time',
                'Viability (%)',
                'Viable Cells / mL (x10^6)',
                'Resuspension Volume (mL)',
                'Total Viable Cells (x10^6)',
                'Cell Yield / mL Blood',
                'Final CPS Volume (mL)',
                'Actual Cells / Vial (x10^6)',
                'Cryovials Frozen',
                'Lab Processing Start Time',
                'Freezing Time',
                'Processing to Freezing (minutes)',
                'Blood Draw to Freezing (minutes)',
                'Operator Initials',
                'Comments',
                'Source Workbook',
                'Source Sheet',
                'Source Row Number',
                'Created At',
            ]);

            foreach ($reports as $report) {
                fputcsv($handle, [
                    $report->study_code,
                    $report->sample_id_visit_number,
                    $report->participant_id,
                    $report->visit_code,
                    $report->report_date?->format('Y-m-d'),
                    $report->sample_condition,
                    $report->sample_tube_type,
                    $report->plasma_harvesting,
                    $report->counting_method,
                    $report->dilution_factor,
                    $report->total_blood_volume_ml,
                    $report->blood_draw_time,
                    $report->viability_percent,
                    $report->viable_cells_per_ml_millions,
                    $report->resuspension_volume_ml,
                    $report->total_viable_cells_millions,
                    $report->cell_yield_per_ml_blood,
                    $report->final_cps_volume_ml,
                    $report->actual_cells_per_vial_millions,
                    $report->cryovials_frozen,
                    $report->lab_processing_start_time,
                    $report->freezing_time,
                    $report->processing_to_freezing_minutes,
                    $report->blood_draw_to_freezing_minutes,
                    $report->operator_initials,
                    $report->comments,
                    $report->source_workbook,
                    $report->source_sheet,
                    $report->source_row_number,
                    $report->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function normalizeTime(?string $value): ?string
    {
        return blank($value) ? null : $value . ':00';
    }

    private function timeToMinutes(?string $value): ?int
    {
        if (blank($value)) {
            return null;
        }

        [$hours, $minutes] = array_map('intval', explode(':', $value));

        return ($hours * 60) + $minutes;
    }

    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function initialsForName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        $initials = collect($parts)
            ->filter()
            ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
            ->implode('');

        return substr($initials, 0, 16);
    }
}

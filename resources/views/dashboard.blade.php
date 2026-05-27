@extends('layouts.app')

@section('title', 'PBMC | Dashboard')
@section('topnav-title', 'PBMC Dashboard')

@section('content')

    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <h2 class="text-lg font-bold text-orange-600 mb-1">
            Welcome, {{ auth()->user()?->name }} 👋
        </h2>

        <div class="mt-8 border rounded-xl overflow-hidden">
            <div class="px-5 py-4 bg-gray-50 border-b flex justify-between items-center">
                <div>
                    <h3 class="font-semibold text-gray-800">IAVIC114 Imported Reports</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Excel-imported PBMC report data from the production workbook.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                        {{ $iavicReports->total() }} imported rows
                    </span>
                    <a href="{{ route('iavic114-reports.create') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-green-700">
                        <i data-feather="plus" class="h-4 w-4"></i>
                        Add New Record
                    </a>
                </div>
            </div>

            <div class="p-6">
                @php
                    $hasReportSearch = filled($reportSampleId ?? null) || filled($reportOperator ?? null);
                @endphp

                <form method="GET" action="{{ route('dashboard') }}"
                    class="mb-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <input type="hidden" name="report_sort" value="{{ $reportSort }}">
                    <input type="hidden" name="report_dir" value="{{ $reportDir }}">

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] md:items-end">
                        <div>
                            <label for="reportSampleId" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">
                                Sample ID
                            </label>
                            <input id="reportSampleId" type="search" name="report_sample_id"
                                value="{{ $reportSampleId ?? '' }}"
                                placeholder="C114104003_V02"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:border-pbmc focus:outline-none focus:ring-1 focus:ring-pbmc">
                        </div>

                        <div>
                            <label for="reportOperator" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">
                                Operator
                            </label>
                            <input id="reportOperator" type="search" name="report_operator"
                                value="{{ $reportOperator ?? '' }}"
                                placeholder="BM"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:border-pbmc focus:outline-none focus:ring-1 focus:ring-pbmc">
                        </div>

                        <div class="flex items-center gap-2">
                            @if($hasReportSearch)
                                <a href="{{ route('dashboard', ['report_sort' => $reportSort, 'report_dir' => $reportDir]) }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    <i data-feather="x" class="h-4 w-4"></i>
                                    Clear
                                </a>
                            @endif
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-pbmc px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                                <i data-feather="search" class="h-4 w-4"></i>
                                Search
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <p class="text-sm text-gray-500">
                        Showing {{ $iavicReports->firstItem() ?? 0 }}-{{ $iavicReports->lastItem() ?? 0 }}
                        of {{ $iavicReports->total() }} imported records
                    </p>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('iavic114-reports.export.excel') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white transition-colors hover:bg-emerald-700">
                            <i data-feather="download" class="h-4 w-4"></i>
                            Export All to Excel
                        </a>
                        <a href="{{ route('iavic114-reports.export.csv') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm text-white transition-colors hover:bg-blue-700">
                            <i data-feather="file-text" class="h-4 w-4"></i>
                            Export All to CSV
                        </a>
                        <a href="{{ route('iavic114-reports.export.pdf') }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-2 rounded-lg bg-gray-700 px-4 py-2 text-sm text-white transition-colors hover:bg-gray-800">
                            <i data-feather="printer" class="h-4 w-4"></i>
                            Print All to PDF
                        </a>
                    </div>
                </div>

                <div id="importedBulkActionsBar" class="hidden mb-4 rounded-lg border border-indigo-200 bg-indigo-50 p-3">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <span class="text-sm font-medium text-indigo-900">
                            <span id="selectedImportedCount">0</span> imported report(s) selected
                        </span>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button"
                                onclick="submitImportedExport('{{ route('iavic114-reports.export.selected.excel') }}')"
                                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-1.5 text-sm text-white hover:bg-emerald-700">
                                <i data-feather="download" class="h-4 w-4"></i>
                                Export Selected to Excel
                            </button>
                            <button type="button"
                                onclick="submitImportedExport('{{ route('iavic114-reports.export.selected.csv') }}')"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-1.5 text-sm text-white hover:bg-blue-700">
                                <i data-feather="file-text" class="h-4 w-4"></i>
                                Export Selected to CSV
                            </button>
                            <button type="button"
                                onclick="submitImportedExport('{{ route('iavic114-reports.export.selected.pdf') }}', true)"
                                class="inline-flex items-center gap-2 rounded-lg bg-gray-700 px-3 py-1.5 text-sm text-white hover:bg-gray-800">
                                <i data-feather="printer" class="h-4 w-4"></i>
                                Print Selected to PDF
                            </button>
                            <button type="button" onclick="clearImportedSelection()"
                                class="text-sm font-medium text-indigo-700 hover:text-indigo-900">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <form id="importedExportForm" method="POST">
                        @csrf
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <th class="px-4 py-3 w-8">
                                        <input type="checkbox" id="selectAllImported"
                                            class="rounded border-gray-300 text-pbmc focus:ring-pbmc"
                                            onchange="toggleAllImported(this)">
                                    </th>
                                    <th class="px-4 py-3">
                                        <a href="{{ request()->fullUrlWithQuery([
                                            'report_sort' => 'sample_id_visit_number',
                                            'report_dir' => $reportSort === 'sample_id_visit_number' && $reportDir === 'asc' ? 'desc' : 'asc',
                                            'reports_page' => 1,
                                        ]) }}" class="inline-flex items-center gap-1 hover:text-gray-900 transition-colors">
                                            <span>Sample ID / Visit</span>
                                            <span aria-hidden="true" class="{{ $reportSort === 'sample_id_visit_number' ? 'text-gray-900' : 'text-gray-400' }}">
                                                {{ $reportSort === 'sample_id_visit_number' ? ($reportDir === 'asc' ? '↑' : '↓') : '↕' }}
                                            </span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3">
                                        <a href="{{ request()->fullUrlWithQuery([
                                            'report_sort' => 'report_date',
                                            'report_dir' => $reportSort === 'report_date' && $reportDir === 'asc' ? 'desc' : 'asc',
                                            'reports_page' => 1,
                                        ]) }}" class="inline-flex items-center gap-1 hover:text-gray-900 transition-colors">
                                            <span>Date</span>
                                            <span aria-hidden="true" class="{{ $reportSort === 'report_date' ? 'text-gray-900' : 'text-gray-400' }}">
                                                {{ $reportSort === 'report_date' ? ($reportDir === 'asc' ? '↑' : '↓') : '↕' }}
                                            </span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3">Condition</th>
                                    <th class="px-4 py-3">
                                        <a href="{{ request()->fullUrlWithQuery([
                                            'report_sort' => 'viability_percent',
                                            'report_dir' => $reportSort === 'viability_percent' && $reportDir === 'asc' ? 'desc' : 'asc',
                                            'reports_page' => 1,
                                        ]) }}" class="inline-flex items-center gap-1 hover:text-gray-900 transition-colors">
                                            <span>Viability</span>
                                            <span aria-hidden="true" class="{{ $reportSort === 'viability_percent' ? 'text-gray-900' : 'text-gray-400' }}">
                                                {{ $reportSort === 'viability_percent' ? ($reportDir === 'asc' ? '↑' : '↓') : '↕' }}
                                            </span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3">Total Viable Cells</th>
                                    <th class="px-4 py-3">
                                        <a href="{{ request()->fullUrlWithQuery([
                                            'report_sort' => 'cryovials_frozen',
                                            'report_dir' => $reportSort === 'cryovials_frozen' && $reportDir === 'asc' ? 'desc' : 'asc',
                                            'reports_page' => 1,
                                        ]) }}" class="inline-flex items-center gap-1 hover:text-gray-900 transition-colors">
                                            <span>Cryovials</span>
                                            <span aria-hidden="true" class="{{ $reportSort === 'cryovials_frozen' ? 'text-gray-900' : 'text-gray-400' }}">
                                                {{ $reportSort === 'cryovials_frozen' ? ($reportDir === 'asc' ? '↑' : '↓') : '↕' }}
                                            </span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3">
                                        <a href="{{ request()->fullUrlWithQuery([
                                            'report_sort' => 'operator_initials',
                                            'report_dir' => $reportSort === 'operator_initials' && $reportDir === 'asc' ? 'desc' : 'asc',
                                            'reports_page' => 1,
                                        ]) }}" class="inline-flex items-center gap-1 hover:text-gray-900 transition-colors">
                                            <span>Operator</span>
                                            <span aria-hidden="true" class="{{ $reportSort === 'operator_initials' ? 'text-gray-900' : 'text-gray-400' }}">
                                                {{ $reportSort === 'operator_initials' ? ($reportDir === 'asc' ? '↑' : '↓') : '↕' }}
                                            </span>
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($iavicReports as $report)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" name="selected_report_ids[]" value="{{ $report->id }}"
                                                class="imported-row-checkbox rounded border-gray-300 text-pbmc focus:ring-pbmc"
                                                onchange="updateImportedSelection()">
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-800">
                                            <div>{{ $report->sample_id_visit_number }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $report->participant_id ?? 'N/A' }} /
                                                {{ $report->visit_code ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $report->report_date?->format('d M Y') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <x-status-badge :value="$report->sample_condition ?? 'N/A'" />
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($report->viability_percent !== null)
                                                <x-status-badge
                                                    :value="number_format((float) $report->viability_percent, 2) . '%'"
                                                    :type="$report->viability_percent >= 80 ? 'success' : 'danger'" />
                                            @else
                                                <span class="text-gray-400 text-xs">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $report->total_viable_cells_millions !== null ? number_format((float) $report->total_viable_cells_millions, 2) . ' x10^6' : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $report->cryovials_frozen ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $report->operator_initials ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('iavic114-reports.show', $report) }}"
                                                    class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 hover:text-blue-800">
                                                    <i data-feather="eye" class="h-4 w-4"></i>
                                                    View
                                                </a>
                                                <a href="{{ route('iavic114-reports.print', $report) }}" target="_blank" rel="noopener"
                                                    class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 hover:text-gray-900">
                                                    <i data-feather="printer" class="h-4 w-4"></i>
                                                    Print
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9">
                                            <x-empty-state
                                                icon="file-text"
                                                title="No imported IAVIC114 records found."
                                                description="Import records from Excel or add one manually."
                                                :action-url="route('iavic114-reports.create')"
                                                action-label="Add Record" />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </form>
                </div>

                <div class="mt-6">
                    {{ $iavicReports->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function toggleAllImported(checkbox) {
        document.querySelectorAll('.imported-row-checkbox').forEach(cb => cb.checked = checkbox.checked);
        updateImportedSelection();
    }

    function updateImportedSelection() {
        const checked = document.querySelectorAll('.imported-row-checkbox:checked').length;
        const all = document.querySelectorAll('.imported-row-checkbox').length;
        const bar = document.getElementById('importedBulkActionsBar');
        const count = document.getElementById('selectedImportedCount');
        const selectAllImported = document.getElementById('selectAllImported');

        if (count) count.textContent = checked;
        if (bar) bar.classList.toggle('hidden', checked === 0);

        if (selectAllImported) {
            selectAllImported.checked = checked === all && checked > 0;
            selectAllImported.indeterminate = checked > 0 && checked < all;
        }
    }

    function clearImportedSelection() {
        document.querySelectorAll('.imported-row-checkbox').forEach(cb => cb.checked = false);
        const selectAllImported = document.getElementById('selectAllImported');
        if (selectAllImported) {
            selectAllImported.checked = false;
            selectAllImported.indeterminate = false;
        }
        updateImportedSelection();
    }

    function submitImportedExport(action, openInNewTab = false) {
        const selected = document.querySelectorAll('.imported-row-checkbox:checked').length;
        if (!selected) {
            alert('Please select at least one imported report.');
            return;
        }
        const form = document.getElementById('importedExportForm');
        form.action = action;
        form.target = openInNewTab ? '_blank' : '_self';
        form.submit();
        form.target = '_self';
    }
</script>
@endpush
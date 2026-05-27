@extends('layouts.app')

@section('title', 'IAVIC114 Imported Reports')
@section('topnav-title', 'IAVIC114 Imported Reports')

@section('content')
    @php
        $hasReportSearch = filled($reportSampleId ?? null) || filled($reportOperator ?? null);
        $sortUrl = function (string $column) use ($reportSort, $reportDir) {
            return request()->fullUrlWithQuery([
                'report_sort' => $column,
                'report_dir' => $reportSort === $column && $reportDir === 'asc' ? 'desc' : 'asc',
                'page' => 1,
            ]);
        };
        $sortIcon = fn (string $column) => $reportSort === $column ? ($reportDir === 'asc' ? 'chevron-up' : 'chevron-down') : null;
        $sortLinkClasses = 'inline-flex items-center gap-1 text-gray-500 hover:text-gray-900';
    @endphp

    <div class="space-y-5">
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Imported Processing Reports</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        PBMC processing records entered from the IAVIC114 workbook and manual report form.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i data-feather="arrow-left" class="h-4 w-4"></i>
                        Receipt Queue
                    </a>
                    <a href="{{ route('iavic114-reports.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700">
                        <i data-feather="plus" class="h-4 w-4"></i>
                        Add New Record
                    </a>
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <form method="GET" action="{{ route('iavic114-reports.index') }}"
                    class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end">
                    <input type="hidden" name="report_sort" value="{{ $reportSort }}">
                    <input type="hidden" name="report_dir" value="{{ $reportDir }}">

                    <div>
                        <label for="reportSampleId" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Sample ID</label>
                        <input id="reportSampleId" type="search" name="report_sample_id"
                            value="{{ $reportSampleId ?? '' }}"
                            placeholder="C114104003_V02"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-pbmc focus:outline-none focus:ring-1 focus:ring-pbmc">
                    </div>

                    <div>
                        <label for="reportOperator" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Operator</label>
                        <input id="reportOperator" type="search" name="report_operator"
                            value="{{ $reportOperator ?? '' }}"
                            placeholder="BM"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-pbmc focus:outline-none focus:ring-1 focus:ring-pbmc">
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($hasReportSearch)
                            <a href="{{ route('iavic114-reports.index', ['report_sort' => $reportSort, 'report_dir' => $reportDir]) }}"
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
                </form>

                <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <p class="text-sm text-gray-500">
                        Showing {{ $iavicReports->firstItem() ?? 0 }}-{{ $iavicReports->lastItem() ?? 0 }}
                        of {{ $iavicReports->total() }} imported records
                    </p>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('iavic114-reports.export.excel') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm text-white hover:bg-emerald-700">
                            <i data-feather="download" class="h-4 w-4"></i>
                            Excel
                        </a>
                        <a href="{{ route('iavic114-reports.export.csv') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700">
                            <i data-feather="file-text" class="h-4 w-4"></i>
                            CSV
                        </a>
                        <a href="{{ route('iavic114-reports.export.pdf') }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-2 rounded-lg bg-gray-700 px-3 py-2 text-sm text-white hover:bg-gray-800">
                            <i data-feather="printer" class="h-4 w-4"></i>
                            PDF
                        </a>
                    </div>
                </div>
            </div>

            @if ($iavicReports->isEmpty())
                <div class="p-10 text-center">
                    <i data-feather="file-text" class="mx-auto h-8 w-8 text-gray-400"></i>
                    <p class="mt-3 text-sm font-medium text-gray-800">No imported reports found.</p>
                    <p class="mt-1 text-sm text-gray-500">Add a processing record after receiving a sample.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ $sortUrl('sample_id_visit_number') }}" class="{{ $sortLinkClasses }}">
                                        Sample ID / Visit
                                        @if ($sortIcon('sample_id_visit_number'))
                                            <i data-feather="{{ $sortIcon('sample_id_visit_number') }}" class="h-3 w-3"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ $sortUrl('report_date') }}" class="{{ $sortLinkClasses }}">
                                        Date
                                        @if ($sortIcon('report_date'))
                                            <i data-feather="{{ $sortIcon('report_date') }}" class="h-3 w-3"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left">Condition</th>
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ $sortUrl('viability_percent') }}" class="{{ $sortLinkClasses }}">
                                        Viability
                                        @if ($sortIcon('viability_percent'))
                                            <i data-feather="{{ $sortIcon('viability_percent') }}" class="h-3 w-3"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ $sortUrl('cryovials_frozen') }}" class="{{ $sortLinkClasses }}">
                                        Cryovials
                                        @if ($sortIcon('cryovials_frozen'))
                                            <i data-feather="{{ $sortIcon('cryovials_frozen') }}" class="h-3 w-3"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ $sortUrl('operator_initials') }}" class="{{ $sortLinkClasses }}">
                                        Operator
                                        @if ($sortIcon('operator_initials'))
                                            <i data-feather="{{ $sortIcon('operator_initials') }}" class="h-3 w-3"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($iavicReports as $report)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 font-medium text-gray-900">
                                        <div>{{ $report->sample_id_visit_number }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $report->participant_id ?? 'N/A' }} / {{ $report->visit_code ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">{{ $report->report_date?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-4"><x-status-badge :value="$report->sample_condition ?? 'N/A'" /></td>
                                    <td class="px-4 py-4">
                                        @if ($report->viability_percent !== null)
                                            <x-status-badge
                                                :value="number_format((float) $report->viability_percent, 2) . '%'"
                                                :type="$report->viability_percent >= 80 ? 'success' : 'danger'" />
                                        @else
                                            <span class="text-xs text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-gray-700">{{ $report->cryovials_frozen ?? '-' }}</td>
                                    <td class="px-4 py-4 text-gray-700">{{ $report->operator_initials ?? '-' }}</td>
                                    <td class="px-4 py-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('iavic114-reports.show', $report) }}"
                                                class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100">
                                                <i data-feather="eye" class="h-4 w-4"></i>
                                                View
                                            </a>
                                            <a href="{{ route('iavic114-reports.print', $report) }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                                <i data-feather="printer" class="h-4 w-4"></i>
                                                Print
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-5 py-4">
                    {{ $iavicReports->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection

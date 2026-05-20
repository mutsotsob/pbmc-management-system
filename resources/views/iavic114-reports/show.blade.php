@extends('layouts.app')

@section('title', 'IAVIC114 Report')
@section('topnav-title', 'IAVIC114 Report Details')

@section('content')
@php
    $rawPayload = collect($report->raw_payload ?? [])->filter(fn ($value) => !is_null($value) && $value !== '');
    $viability = $report->viability_percent !== null ? (float) $report->viability_percent : null;
    $viabilityType = $viability === null ? 'neutral' : ($viability >= 80 ? 'success' : 'danger');

    $display = fn ($value, string $fallback = 'N/A') => blank($value) && $value !== 0 && $value !== '0' ? $fallback : $value;

    $decimal = function ($value, int $places, string $suffix = '') use ($display): string {
        if ($value === null || $value === '') {
            return $display(null);
        }

        return number_format((float) $value, $places) . $suffix;
    };

    $time = function ($value) use ($display): string {
        if (blank($value)) {
            return $display(null);
        }

        try {
            return \Carbon\Carbon::parse($value)->format('H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $duration = function (?int $minutes) use ($display): string {
        if ($minutes === null) {
            return $display(null);
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        if ($hours === 0) {
            return $remaining . ' min';
        }

        return $hours . 'h ' . str_pad((string) $remaining, 2, '0', STR_PAD_LEFT) . 'm';
    };

    $sectionTitleClass = 'text-xs font-semibold uppercase tracking-wide text-gray-500';
    $labelClass = 'text-xs font-medium uppercase tracking-wide text-gray-400';
    $valueClass = 'mt-1 text-sm font-semibold text-gray-900';
@endphp

<div class="mx-auto max-w-6xl space-y-4">
    <div class="flex items-center gap-2 text-sm text-gray-600">
        <a href="{{ route('dashboard') }}" class="hover:text-pbmc">Dashboard</a>
        <span>/</span>
        <span class="font-medium text-gray-900">{{ $report->sample_id_visit_number }}</span>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 bg-gray-50 px-5 py-4 sm:px-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="mb-2 flex flex-wrap items-center gap-3">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $report->sample_id_visit_number }}</h2>
                        <x-status-badge
                            :value="$viability !== null ? number_format($viability, 2) . '% viable' : 'Viability N/A'"
                            :type="$viabilityType" />
                        <x-status-badge :value="$report->sample_condition ?? 'Condition N/A'" />
                    </div>
                    <p class="text-sm text-gray-600">
                        {{ $report->study_code ?? 'IAVIC114' }}
                        <span class="mx-2 text-gray-300">|</span>
                        Participant <span class="font-semibold text-gray-900">{{ $report->participant_id ?? 'N/A' }}</span>
                        <span class="mx-2 text-gray-300">|</span>
                        Visit <span class="font-semibold text-gray-900">{{ $report->visit_code ?? 'N/A' }}</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i data-feather="arrow-left" class="h-4 w-4"></i>
                        Back to Dashboard
                    </a>
                    <a href="#source-data"
                       class="inline-flex items-center gap-2 rounded-lg bg-gray-800 px-3 py-2 text-sm font-medium text-white hover:bg-gray-900">
                        <i data-feather="file-text" class="h-4 w-4"></i>
                        Source Data
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 divide-y divide-gray-100 lg:grid-cols-12 lg:divide-x lg:divide-y-0">
            <section class="p-5 sm:p-6 lg:col-span-4">
                <div class="mb-4 flex items-center gap-2">
                    <i data-feather="clipboard" class="h-4 w-4 text-gray-500"></i>
                    <h3 class="{{ $sectionTitleClass }}">Report Identity</h3>
                </div>
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-1">
                    <div>
                        <dt class="{{ $labelClass }}">Report Date</dt>
                        <dd class="{{ $valueClass }}">{{ $report->report_date?->format('d M Y') ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelClass }}">Sample ID / Visit</dt>
                        <dd class="{{ $valueClass }}">{{ $report->sample_id_visit_number }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelClass }}">Participant ID</dt>
                        <dd class="{{ $valueClass }}">{{ $report->participant_id ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelClass }}">Visit Code</dt>
                        <dd class="{{ $valueClass }}">{{ $report->visit_code ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelClass }}">Operator</dt>
                        <dd class="{{ $valueClass }}">{{ $report->operator_initials ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="p-5 sm:p-6 lg:col-span-4">
                <div class="mb-4 flex items-center gap-2">
                    <i data-feather="droplet" class="h-4 w-4 text-gray-500"></i>
                    <h3 class="{{ $sectionTitleClass }}">Sample and Processing</h3>
                </div>
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-1">
                    <div>
                        <dt class="{{ $labelClass }}">Sample Condition</dt>
                        <dd class="{{ $valueClass }}">{{ $report->sample_condition ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelClass }}">Tube Type</dt>
                        <dd class="{{ $valueClass }}">{{ $report->sample_tube_type ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelClass }}">Plasma Harvesting</dt>
                        <dd class="{{ $valueClass }}">{{ $report->plasma_harvesting ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelClass }}">Counting Method</dt>
                        <dd class="{{ $valueClass }}">{{ $report->counting_method ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelClass }}">Dilution Factor</dt>
                        <dd class="{{ $valueClass }}">{{ $decimal($report->dilution_factor, 1) }}</dd>
                    </div>
                </dl>
            </section>

            <section class="p-5 sm:p-6 lg:col-span-4">
                <div class="mb-4 flex items-center gap-2">
                    <i data-feather="activity" class="h-4 w-4 text-gray-500"></i>
                    <h3 class="{{ $sectionTitleClass }}">Cell Yield Summary</h3>
                </div>
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-1">
                    <div>
                        <dt class="{{ $labelClass }}">Viability</dt>
                        <dd class="{{ $valueClass }}">{{ $viability !== null ? number_format($viability, 2) . '%' : 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelClass }}">Viable Cells / mL</dt>
                        <dd class="{{ $valueClass }}">{{ $decimal($report->viable_cells_per_ml_millions, 3, ' x10^6/mL') }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelClass }}">Total Viable Cells</dt>
                        <dd class="{{ $valueClass }}">{{ $decimal($report->total_viable_cells_millions, 2, ' x10^6') }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelClass }}">Cells / Vial</dt>
                        <dd class="{{ $valueClass }}">{{ $decimal($report->actual_cells_per_vial_millions, 2, ' x10^6') }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelClass }}">Cryovials Frozen</dt>
                        <dd class="{{ $valueClass }}">{{ $report->cryovials_frozen ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <div class="border-t border-gray-200 p-5 sm:p-6">
            <div class="mb-4 flex items-center gap-2">
                <i data-feather="clock" class="h-4 w-4 text-gray-500"></i>
                <h3 class="{{ $sectionTitleClass }}">Volumes and Timing</h3>
            </div>

            <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <dt class="{{ $labelClass }}">Blood Volume</dt>
                    <dd class="{{ $valueClass }}">{{ $decimal($report->total_blood_volume_ml, 2, ' mL') }}</dd>
                </div>
                <div>
                    <dt class="{{ $labelClass }}">Resuspension Volume</dt>
                    <dd class="{{ $valueClass }}">{{ $decimal($report->resuspension_volume_ml, 2, ' mL') }}</dd>
                </div>
                <div>
                    <dt class="{{ $labelClass }}">Final CPS Volume</dt>
                    <dd class="{{ $valueClass }}">{{ $decimal($report->final_cps_volume_ml, 2, ' mL') }}</dd>
                </div>
                <div>
                    <dt class="{{ $labelClass }}">Cell Yield / mL Blood</dt>
                    <dd class="{{ $valueClass }}">{{ $decimal($report->cell_yield_per_ml_blood, 3) }}</dd>
                </div>
                <div>
                    <dt class="{{ $labelClass }}">Blood Draw Time</dt>
                    <dd class="{{ $valueClass }}">{{ $time($report->blood_draw_time) }}</dd>
                </div>
                <div>
                    <dt class="{{ $labelClass }}">Lab Processing Start</dt>
                    <dd class="{{ $valueClass }}">{{ $time($report->lab_processing_start_time) }}</dd>
                </div>
                <div>
                    <dt class="{{ $labelClass }}">Freezing Time</dt>
                    <dd class="{{ $valueClass }}">{{ $time($report->freezing_time) }}</dd>
                </div>
                <div>
                    <dt class="{{ $labelClass }}">Imported At</dt>
                    <dd class="{{ $valueClass }}">{{ $report->created_at?->format('d M Y, H:i') ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="{{ $labelClass }}">Processing to Freezing</dt>
                    <dd class="{{ $valueClass }}">{{ $duration($report->processing_to_freezing_minutes) }}</dd>
                </div>
                <div>
                    <dt class="{{ $labelClass }}">Blood Draw to Freezing</dt>
                    <dd class="{{ $valueClass }}">{{ $duration($report->blood_draw_to_freezing_minutes) }}</dd>
                </div>
                <div>
                    <dt class="{{ $labelClass }}">Workbook</dt>
                    <dd class="mt-1 break-words text-sm font-semibold text-gray-900">{{ $report->source_workbook ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="{{ $labelClass }}">Sheet / Row</dt>
                    <dd class="{{ $valueClass }}">{{ $report->source_sheet ?? 'N/A' }} / {{ $report->source_row_number ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        <div class="grid grid-cols-1 border-t border-gray-200 lg:grid-cols-12 lg:divide-x lg:divide-gray-100">
            <section class="p-5 sm:p-6 lg:col-span-5">
                <div class="mb-3 flex items-center gap-2">
                    <i data-feather="message-square" class="h-4 w-4 text-gray-500"></i>
                    <h3 class="{{ $sectionTitleClass }}">Comments</h3>
                </div>
                <p class="whitespace-pre-line text-sm leading-6 text-gray-700">
                    {{ $report->comments ?: 'No comments recorded.' }}
                </p>
            </section>

            <section id="source-data" class="p-5 sm:p-6 lg:col-span-7">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <i data-feather="database" class="h-4 w-4 text-gray-500"></i>
                            <h3 class="{{ $sectionTitleClass }}">Source Data</h3>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Original workbook values preserved for audit and troubleshooting.</p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">
                        {{ $rawPayload->count() }} fields
                    </span>
                </div>

                <div class="max-h-96 overflow-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="sticky top-0 bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <th class="px-4 py-3">Field</th>
                                <th class="px-4 py-3">Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($rawPayload as $field => $value)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $field }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ is_array($value) ? json_encode($value) : $value }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-400">
                                        No source data is available for this report.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

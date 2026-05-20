@extends('layouts.app')

@section('title', 'PBMC Processing Worksheet')
@section('topnav-title', 'New PBMC Processing Worksheet')

@section('content')
<div class="mx-auto max-w-7xl pb-12">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-1 text-sm text-gray-500">
                <a href="{{ route('dashboard') }}" class="hover:text-pbmc">Dashboard</a>
                <span class="mx-1">/</span>
                <span>New IAVIC114 report</span>
            </p>
            <h2 class="text-2xl font-bold text-gray-900">PBMC Processing Worksheet</h2>
            <p class="mt-1 text-sm text-gray-500">Enter sample details, counts, timing, and cryovial information.</p>
        </div>

        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i data-feather="arrow-left" class="h-4 w-4"></i>
            Back to Dashboard
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-5 py-4">
            <p class="mb-1 text-sm font-semibold text-red-800">Please correct the following:</p>
            <ul class="list-inside list-disc space-y-0.5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('iavic114-reports.store') }}" id="worksheet" class="space-y-5">
        @csrf

        <section class="worksheet-panel">
            <div class="worksheet-panel-header">
                <div>
                    <p class="section-kicker">Section 1</p>
                    <h3 class="section-title">Sample Identification</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="field md:col-span-2">
                    <label for="sample_id_visit_number" class="form-label">Sample ID / Visit Number <span class="text-red-500">*</span></label>
                    <input type="text" id="sample_id_visit_number" name="sample_id_visit_number"
                           value="{{ old('sample_id_visit_number') }}" placeholder="Example: C114104003_V02"
                           required class="form-input">
                    @error('sample_id_visit_number')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="study_code" class="form-label">Study Code <span class="text-red-500">*</span></label>
                    <input type="text" id="study_code" name="study_code"
                           value="{{ old('study_code', 'IAVIC114') }}" required class="form-input">
                    @error('study_code')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="report_date" class="form-label">Processing Date</label>
                    <input type="date" id="report_date" name="report_date"
                           value="{{ old('report_date', date('Y-m-d')) }}" class="form-input">
                    @error('report_date')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="participant_id" class="form-label">Participant ID</label>
                    <input type="text" id="participant_id" name="participant_id"
                           value="{{ old('participant_id') }}" placeholder="Auto-filled from sample ID" class="form-input">
                    @error('participant_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="visit_code" class="form-label">Visit Code</label>
                    <input type="text" id="visit_code" name="visit_code"
                           value="{{ old('visit_code') }}" placeholder="Auto-filled from sample ID" class="form-input">
                    @error('visit_code')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="operator_initials" class="form-label">Operator Initials</label>
                    @php $selectedOperatorInitials = old('operator_initials'); @endphp
                    <select id="operator_initials" name="operator_initials" class="form-input">
                        <option value="">Select lab operator</option>
                        @foreach($labOperators as $operator)
                            <option value="{{ $operator['initials'] }}" {{ $selectedOperatorInitials === $operator['initials'] ? 'selected' : '' }}>
                                {{ $operator['initials'] }} - {{ $operator['name'] }}
                            </option>
                        @endforeach
                        @if(filled($selectedOperatorInitials) && !$labOperators->contains(fn ($operator) => $operator['initials'] === $selectedOperatorInitials))
                            <option value="{{ $selectedOperatorInitials }}" selected>
                                {{ $selectedOperatorInitials }} - previous value
                            </option>
                        @endif
                        @if($labOperators->isEmpty())
                            <option value="" disabled>No active Laboratory users found</option>
                        @endif
                    </select>
                    @error('operator_initials')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="sample_condition" class="form-label">Condition of Sample</label>
                    <select id="sample_condition" name="sample_condition" class="form-input">
                        <option value="">Select condition</option>
                        <option value="Pass" {{ old('sample_condition') === 'Pass' ? 'selected' : '' }}>Pass</option>
                        <option value="Fail" {{ old('sample_condition') === 'Fail' ? 'selected' : '' }}>Fail</option>
                    </select>
                    @error('sample_condition')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="worksheet-panel">
            <div class="worksheet-panel-header">
                <div>
                    <p class="section-kicker">Section 2</p>
                    <h3 class="section-title">Sample Details</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="field">
                    <label for="total_blood_volume_ml" class="form-label">Blood Volume (mL)</label>
                    <input type="number" step="0.01" min="0" max="999999.99"
                           id="total_blood_volume_ml" name="total_blood_volume_ml"
                           value="{{ old('total_blood_volume_ml') }}" placeholder="Example: 20.00"
                           class="form-input calc-trigger">
                    @error('total_blood_volume_ml')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="blood_draw_time" class="form-label">Time of Blood Draw</label>
                    <input type="time" id="blood_draw_time" name="blood_draw_time"
                           value="{{ old('blood_draw_time') }}" class="form-input calc-trigger">
                    @error('blood_draw_time')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="sample_tube_type" class="form-label">Sample Tube Type</label>
                    <select id="sample_tube_type" name="sample_tube_type" class="form-input">
                        <option value="">Select tube type</option>
                        @foreach(['CPT', 'EDTA', 'Lithium Heparin', 'Sodium Heparin', 'ACD-A', 'Other'] as $tubeType)
                            <option value="{{ $tubeType }}" {{ old('sample_tube_type') === $tubeType ? 'selected' : '' }}>
                                {{ $tubeType }}
                            </option>
                        @endforeach
                    </select>
                    @error('sample_tube_type')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="plasma_harvesting" class="form-label">Plasma Harvesting</label>
                    <select id="plasma_harvesting" name="plasma_harvesting" class="form-input">
                        <option value="">Select option</option>
                        <option value="Yes" {{ old('plasma_harvesting') === 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('plasma_harvesting') === 'No' ? 'selected' : '' }}>No</option>
                        <option value="N/A" {{ old('plasma_harvesting') === 'N/A' ? 'selected' : '' }}>N/A</option>
                    </select>
                    @error('plasma_harvesting')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field md:col-span-2">
                    <label for="counting_method" class="form-label">Counting Method</label>
                    <select id="counting_method" name="counting_method" class="form-input">
                        <option value="">Select method</option>
                        @foreach(['Haemocytometer', 'Automated (Vi-Cell)', 'Automated (NC-200)', 'Other'] as $method)
                            <option value="{{ $method }}" {{ old('counting_method') === $method ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                    @error('counting_method')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field md:col-span-2">
                    <label for="comments" class="form-label">Comments / Observations</label>
                    <textarea id="comments" name="comments" rows="4"
                              placeholder="Record observations, exceptions, or cryovial notes."
                              class="form-input resize-none">{{ old('comments') }}</textarea>
                    @error('comments')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_1.15fr]">
            <section class="worksheet-panel">
                <div class="worksheet-panel-header">
                    <div>
                        <p class="section-kicker">Section 3</p>
                        <h3 class="section-title">Manual Cell Count</h3>
                    </div>
                </div>

                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-sm">
                            <thead>
                                <tr>
                                    <th class="cell-head text-left">Square</th>
                                    <th class="cell-head text-center">Total</th>
                                    <th class="cell-head text-center">Viable</th>
                                    <th class="cell-head text-center">Non-Viable</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([1, 2, 3, 4] as $square)
                                    <tr>
                                        <td class="cell-body font-medium text-gray-700">Square {{ $square }}</td>
                                        <td class="cell-body text-center">
                                            <output id="sq{{ $square }}_total_display" class="output-pill">-</output>
                                        </td>
                                        <td class="cell-body">
                                            <input type="number" min="0" step="1" id="sq{{ $square }}_viable"
                                                   placeholder="0" class="count-input calc-trigger">
                                        </td>
                                        <td class="cell-body">
                                            <input type="number" min="0" step="1" id="sq{{ $square }}_non_viable"
                                                   placeholder="0" class="count-input calc-trigger">
                                        </td>
                                    </tr>
                                @endforeach

                                <tr class="bg-blue-50">
                                    <td class="cell-body font-bold text-gray-800">Average</td>
                                    <td class="cell-body text-center font-mono font-bold text-gray-900" id="avg_total_display">-</td>
                                    <td class="cell-body text-center font-mono font-bold text-gray-900" id="avg_viable_display">-</td>
                                    <td class="cell-body text-center font-mono font-bold text-gray-900" id="avg_non_viable_display">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="field">
                            <label for="dilution_factor" class="form-label">Dilution Factor</label>
                            <input type="number" min="1" step="0.5" id="dilution_factor" name="dilution_factor"
                                   value="{{ old('dilution_factor', 10) }}" class="form-input calc-trigger">
                            @error('dilution_factor')<p class="form-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="field">
                            <label for="final_cps_volume_ml" class="form-label">No. of CPS / Vials</label>
                            <input type="number" min="0" step="1" id="final_cps_volume_ml" name="final_cps_volume_ml"
                                   value="{{ old('final_cps_volume_ml') }}" placeholder="Enter count"
                                   class="form-input calc-trigger">
                            @error('final_cps_volume_ml')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="worksheet-panel">
                <div class="worksheet-panel-header">
                    <div>
                        <p class="section-kicker">Section 4</p>
                        <h3 class="section-title">Calculated Results</h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                    <div class="field">
                        <label for="viable_cells_per_ml_millions" class="form-label">Viable Cells (x10^6/mL)</label>
                        <input type="number" step="0.001" id="viable_cells_per_ml_millions" name="viable_cells_per_ml_millions"
                               value="{{ old('viable_cells_per_ml_millions') }}" readonly class="form-input readonly-input">
                        @error('viable_cells_per_ml_millions')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="viability_percent" class="form-label">Viability (%)</label>
                        <input type="number" step="0.01" id="viability_percent" name="viability_percent"
                               value="{{ old('viability_percent') }}" readonly class="form-input readonly-input">
                        @error('viability_percent')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="resuspension_volume_ml" class="form-label">Resuspension Volume (mL)</label>
                        <input type="number" step="0.01" id="resuspension_volume_ml" name="resuspension_volume_ml"
                               value="{{ old('resuspension_volume_ml') }}" readonly class="form-input readonly-input">
                        @error('resuspension_volume_ml')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="total_viable_cells_millions" class="form-label">Total Viable Cells (x10^6)</label>
                        <input type="number" step="0.01" id="total_viable_cells_millions" name="total_viable_cells_millions"
                               value="{{ old('total_viable_cells_millions') }}" readonly class="form-input readonly-input">
                        @error('total_viable_cells_millions')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="cell_yield_per_ml_blood" class="form-label">Cell Yield / mL Blood</label>
                        <input type="number" step="0.001" id="cell_yield_per_ml_blood" name="cell_yield_per_ml_blood"
                               value="{{ old('cell_yield_per_ml_blood') }}" readonly class="form-input readonly-input">
                        @error('cell_yield_per_ml_blood')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="actual_cells_per_vial_millions" class="form-label">Actual Cells / Vial (x10^6)</label>
                        <input type="number" step="0.01" id="actual_cells_per_vial_millions" name="actual_cells_per_vial_millions"
                               value="{{ old('actual_cells_per_vial_millions') }}" readonly class="form-input readonly-input">
                        @error('actual_cells_per_vial_millions')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field sm:col-span-2">
                        <label for="estimated_vials_display" class="form-label">Estimated No. of Vials</label>
                        <input type="text" id="estimated_vials_display" value="-" readonly class="form-input readonly-input font-mono">
                    </div>
                </div>
            </section>
        </div>

        <section class="worksheet-panel">
            <div class="worksheet-panel-header">
                <div>
                    <p class="section-kicker">Section 5</p>
                    <h3 class="section-title">Timing & Cryovials</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="field">
                    <label for="lab_processing_start_time" class="form-label">Lab Processing Start</label>
                    <input type="time" id="lab_processing_start_time" name="lab_processing_start_time"
                           value="{{ old('lab_processing_start_time') }}" class="form-input calc-trigger">
                    @error('lab_processing_start_time')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="freezing_time" class="form-label">Time at Freezing</label>
                    <input type="time" id="freezing_time" name="freezing_time"
                           value="{{ old('freezing_time') }}" class="form-input calc-trigger">
                    @error('freezing_time')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="cryovials_frozen" class="form-label">Cryovials Frozen</label>
                    <input type="number" min="0" max="65535" id="cryovials_frozen" name="cryovials_frozen"
                           value="{{ old('cryovials_frozen') }}" placeholder="Example: 8" class="form-input">
                    @error('cryovials_frozen')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="blood_draw_time_echo" class="form-label">Blood Draw Time</label>
                    <input type="text" id="blood_draw_time_echo" value="{{ old('blood_draw_time') ?: '-' }}" readonly
                           class="form-input readonly-input">
                </div>

                <div class="field md:col-span-2">
                    <label for="processing_to_freezing_duration_display" class="form-label">Processing to Freezing</label>
                    <input type="text" id="processing_to_freezing_duration_display" value="-" readonly class="form-input readonly-input">
                    <input type="hidden" id="processing_to_freezing_duration" name="processing_to_freezing_duration"
                           value="{{ old('processing_to_freezing_duration') }}">
                    @error('processing_to_freezing_duration')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="field md:col-span-2">
                    <label for="blood_draw_to_freezing_duration_display" class="form-label">Blood Draw to Freezing</label>
                    <input type="text" id="blood_draw_to_freezing_duration_display" value="-" readonly class="form-input readonly-input">
                    <input type="hidden" id="blood_draw_to_freezing_duration" name="blood_draw_to_freezing_duration"
                           value="{{ old('blood_draw_to_freezing_duration') }}">
                    @error('blood_draw_to_freezing_duration')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <div class="sticky bottom-0 z-10 -mx-6 border-t border-gray-200 bg-gray-100/95 px-6 py-4 backdrop-blur">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i data-feather="x" class="h-4 w-4"></i>
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                    <i data-feather="save" class="h-4 w-4"></i>
                    Save Worksheet
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .worksheet-panel {
        overflow: hidden;
        border: 1px solid #d1d5db;
        border-radius: 0.75rem;
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .worksheet-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        padding: 1rem 1.25rem;
    }

    .section-kicker {
        color: #f97316;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .section-title {
        margin-top: 0.1rem;
        color: #111827;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    .field {
        min-width: 0;
    }

    .form-label {
        display: block;
        margin-bottom: 0.35rem;
        color: #374151;
        font-size: 0.875rem;
        font-weight: 650;
        letter-spacing: 0;
    }

    .form-input,
    .count-input {
        display: block;
        width: 100%;
        min-height: 2.75rem;
        border: 1px solid #9ca3af;
        border-radius: 0.55rem;
        background: #ffffff;
        color: #111827;
        font-size: 0.95rem;
        line-height: 1.35;
        outline: none;
        transition: border-color 150ms ease, box-shadow 150ms ease, background-color 150ms ease;
    }

    .form-input {
        padding: 0.62rem 0.75rem;
    }

    .count-input {
        padding: 0.5rem;
        text-align: center;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
    }

    .form-input::placeholder,
    .count-input::placeholder {
        color: #9ca3af;
    }

    .form-input:focus,
    .count-input:focus {
        border-color: #f97316;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.18);
    }

    .readonly-input {
        border-color: #d1d5db;
        background: #f9fafb;
        color: #374151;
        font-weight: 650;
    }

    .form-error {
        margin-top: 0.35rem;
        color: #dc2626;
        font-size: 0.78rem;
        font-weight: 500;
    }

    .cell-head {
        border: 1px solid #d1d5db;
        background: #f9fafb;
        padding: 0.75rem;
        color: #4b5563;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .cell-body {
        border: 1px solid #d1d5db;
        padding: 0.65rem;
        vertical-align: middle;
    }

    .output-pill {
        display: block;
        min-width: 4rem;
        border-radius: 0.45rem;
        background: #fff7ed;
        padding: 0.45rem 0.5rem;
        color: #ea580c;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-weight: 800;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    function byId(id) {
        return document.getElementById(id);
    }

    function readNumber(id) {
        const value = parseFloat(byId(id)?.value);
        return Number.isFinite(value) ? value : 0;
    }

    function setValue(id, value, decimals) {
        const element = byId(id);
        if (!element) {
            return;
        }

        element.value = Number.isFinite(value) ? value.toFixed(decimals) : '';
    }

    function setTextValue(id, value) {
        const element = byId(id);
        if (element) {
            element.value = value;
        }
    }

    function setText(id, value) {
        const element = byId(id);
        if (element) {
            element.textContent = value;
        }
    }

    function toMinutes(timeValue) {
        if (!timeValue) {
            return null;
        }

        const parts = timeValue.split(':').map(Number);
        if (parts.length < 2 || parts.some(part => !Number.isFinite(part))) {
            return null;
        }

        return (parts[0] * 60) + parts[1];
    }

    function toHHMM(minutes) {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;

        return String(hours).padStart(2, '0') + ':' + String(mins).padStart(2, '0');
    }

    function formatDuration(minutes) {
        if (minutes === null) {
            return '-';
        }

        return Math.floor(minutes / 60) + 'h ' + String(minutes % 60).padStart(2, '0') + 'm';
    }

    function calculateAll() {
        let totalCells = 0;
        let viableCells = 0;
        let nonViableCells = 0;
        let hasCount = false;

        for (let square = 1; square <= 4; square++) {
            const viableInput = byId('sq' + square + '_viable');
            const nonViableInput = byId('sq' + square + '_non_viable');
            const viable = parseFloat(viableInput?.value) || 0;
            const nonViable = parseFloat(nonViableInput?.value) || 0;
            const rowHasCount = (viableInput?.value !== '' || nonViableInput?.value !== '');

            if (rowHasCount) {
                hasCount = true;
            }

            setText('sq' + square + '_total_display', rowHasCount ? String(viable + nonViable) : '-');

            totalCells += viable + nonViable;
            viableCells += viable;
            nonViableCells += nonViable;
        }

        const avgTotal = totalCells / 4;
        const avgViable = viableCells / 4;
        const avgNonViable = nonViableCells / 4;

        setText('avg_total_display', hasCount ? avgTotal.toFixed(2) : '-');
        setText('avg_viable_display', hasCount ? avgViable.toFixed(2) : '-');
        setText('avg_non_viable_display', hasCount ? avgNonViable.toFixed(2) : '-');

        const dilutionFactor = readNumber('dilution_factor') || 10;
        const bloodVolume = readNumber('total_blood_volume_ml');
        const viablePerMl = (avgViable * dilutionFactor * 10000) / 1000000;
        const viabilityPercent = avgTotal > 0 ? (avgViable / avgTotal) * 100 : 0;
        const resuspensionVolume = 0.2 * bloodVolume;
        const totalViable = resuspensionVolume * viablePerMl;
        const cellYield = bloodVolume > 0 ? totalViable / bloodVolume : 0;
        const estimatedVials = totalViable / 15;
        const cps = readNumber('final_cps_volume_ml');
        const actualPerVial = cps > 0 && totalViable > 0 ? totalViable / cps : 0;

        setValue('viable_cells_per_ml_millions', hasCount ? viablePerMl : Number.NaN, 3);
        setValue('viability_percent', hasCount ? viabilityPercent : Number.NaN, 2);
        setValue('resuspension_volume_ml', hasCount && bloodVolume > 0 ? resuspensionVolume : Number.NaN, 2);
        setValue('total_viable_cells_millions', hasCount && bloodVolume > 0 ? totalViable : Number.NaN, 2);
        setValue('cell_yield_per_ml_blood', hasCount && bloodVolume > 0 ? cellYield : Number.NaN, 3);
        setValue('actual_cells_per_vial_millions', hasCount ? actualPerVial : Number.NaN, 2);
        setTextValue('estimated_vials_display', hasCount && estimatedVials > 0 ? estimatedVials.toFixed(2) : '-');

        const drawTime = byId('blood_draw_time')?.value || '';
        const processingStartTime = byId('lab_processing_start_time')?.value || '';
        const freezingTime = byId('freezing_time')?.value || '';

        setTextValue('blood_draw_time_echo', drawTime || '-');

        if (processingStartTime && freezingTime) {
            let minutes = toMinutes(freezingTime) - toMinutes(processingStartTime);
            if (minutes < 0) {
                minutes += 1440;
            }

            setTextValue('processing_to_freezing_duration', toHHMM(minutes));
            setTextValue('processing_to_freezing_duration_display', formatDuration(minutes));
        } else {
            setTextValue('processing_to_freezing_duration', '');
            setTextValue('processing_to_freezing_duration_display', '-');
        }

        if (drawTime && freezingTime) {
            let minutes = toMinutes(freezingTime) - toMinutes(drawTime);
            if (minutes < 0) {
                minutes += 1440;
            }

            setTextValue('blood_draw_to_freezing_duration', toHHMM(minutes));
            setTextValue('blood_draw_to_freezing_duration_display', formatDuration(minutes));
        } else {
            setTextValue('blood_draw_to_freezing_duration', '');
            setTextValue('blood_draw_to_freezing_duration_display', '-');
        }
    }

    function wireSampleIdAutofill() {
        const sampleInput = byId('sample_id_visit_number');
        if (!sampleInput) {
            return;
        }

        sampleInput.addEventListener('input', () => {
            const parts = sampleInput.value.trim().split('_');
            if (parts.length !== 2) {
                return;
            }

            const participantInput = byId('participant_id');
            const visitInput = byId('visit_code');

            if (participantInput && !participantInput.value.trim()) {
                participantInput.value = parts[0];
            }

            if (visitInput && !visitInput.value.trim()) {
                visitInput.value = parts[1];
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        wireSampleIdAutofill();

        document.querySelectorAll('.calc-trigger').forEach(element => {
            element.addEventListener('input', calculateAll);
            element.addEventListener('change', calculateAll);
        });

        calculateAll();
    });
})();
</script>
@endpush

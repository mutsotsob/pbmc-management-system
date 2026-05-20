@php
    $viability = $report->viability_percent !== null ? (float) $report->viability_percent : null;
    $viabilityClass = $viability === null ? 'muted' : ($viability >= 80 ? 'pass' : 'review');
    $condition = $report->sample_condition ?? 'N/A';

    $decimal = function ($value, int $places, string $suffix = ''): string {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        return number_format((float) $value, $places) . $suffix;
    };

    $time = function ($value): string {
        if (blank($value)) {
            return 'N/A';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $duration = function (?int $minutes): string {
        if ($minutes === null) {
            return 'N/A';
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        if ($hours === 0) {
            return $remaining . ' min';
        }

        return $hours . 'h ' . str_pad((string) $remaining, 2, '0', STR_PAD_LEFT) . 'm';
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PBMC Laboratory Results - {{ $report->sample_id_visit_number }}</title>
    <style>
        :root {
            --ink: #111827;
            --muted: #4b5563;
            --line: #d1d5db;
            --soft: #f3f4f6;
            --accent: #0f766e;
            --danger: #b91c1c;
            --success: #047857;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #e5e7eb;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.45;
        }

        .toolbar {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 18px;
        }

        .button {
            border: 0;
            border-radius: 6px;
            background: #111827;
            color: #ffffff;
            cursor: pointer;
            display: inline-block;
            font-size: 13px;
            font-weight: 700;
            padding: 10px 14px;
            text-decoration: none;
        }

        .button.secondary {
            background: #ffffff;
            border: 1px solid var(--line);
            color: var(--ink);
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 24px;
            background: #ffffff;
            border: 1px solid var(--line);
            padding: 16mm;
        }

        .header {
            border-bottom: 3px solid var(--ink);
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 18px;
            padding-bottom: 14px;
        }

        .brand {
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        h1 {
            font-size: 22px;
            line-height: 1.15;
            margin: 6px 0 8px;
            text-transform: uppercase;
        }

        .subtitle {
            color: var(--muted);
            font-size: 12px;
            margin: 0;
        }

        .document-meta {
            border: 1px solid var(--line);
            border-radius: 4px;
        }

        .document-meta div {
            display: grid;
            grid-template-columns: 92px 1fr;
            border-bottom: 1px solid var(--line);
        }

        .document-meta div:last-child {
            border-bottom: 0;
        }

        .document-meta dt,
        .document-meta dd {
            margin: 0;
            padding: 7px 8px;
        }

        .document-meta dt {
            background: var(--soft);
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .document-meta dd {
            font-weight: 700;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 16px 0;
        }

        .summary-card {
            border: 1px solid var(--line);
            border-radius: 4px;
            padding: 10px;
        }

        .summary-card span {
            color: var(--muted);
            display: block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .summary-card strong {
            display: block;
            font-size: 15px;
            margin-top: 5px;
        }

        .pass {
            color: var(--success);
        }

        .review {
            color: var(--danger);
        }

        .muted {
            color: var(--muted);
        }

        .section {
            margin-top: 14px;
        }

        .section-title {
            background: var(--ink);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            margin: 0;
            padding: 7px 9px;
            text-transform: uppercase;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid var(--line);
            padding: 7px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: var(--soft);
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        td {
            font-weight: 600;
        }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .comments {
            border: 1px solid var(--line);
            min-height: 58px;
            padding: 9px;
            white-space: pre-line;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-top: 22px;
        }

        .signature-box {
            border-top: 1px solid var(--ink);
            padding-top: 7px;
        }

        .signature-box span {
            color: var(--muted);
            display: block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .footer {
            border-top: 1px solid var(--line);
            color: var(--muted);
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 20px;
            padding-top: 8px;
            font-size: 10px;
        }

        @page {
            size: A4;
            margin: 10mm;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .toolbar {
                display: none;
            }

            .page {
                border: 0;
                margin: 0;
                min-height: auto;
                padding: 0;
                width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="button" onclick="window.print()">Print / Save as PDF</button>
        <a href="{{ route('dashboard') }}" class="button secondary">Back to Dashboard</a>
        <a href="{{ route('iavic114-reports.show', $report) }}" class="button secondary">View Record</a>
    </div>

    <main class="page">
        <header class="header">
            <div>
                <div class="brand">PBMC Management System</div>
                <h1>PBMC Laboratory Results Report</h1>
                <p class="subtitle">IAVIC114 peripheral blood mononuclear cell processing and cryopreservation results.</p>
            </div>

            <dl class="document-meta">
                <div>
                    <dt>Report ID</dt>
                    <dd>{{ $report->sample_id_visit_number }}</dd>
                </div>
                <div>
                    <dt>Date</dt>
                    <dd>{{ $report->report_date?->format('d M Y') ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt>Generated</dt>
                    <dd>{{ $generatedAt->format('d M Y H:i') }}</dd>
                </div>
                <div>
                    <dt>Generated By</dt>
                    <dd>{{ $generatedBy?->name ?? 'System' }}</dd>
                </div>
            </dl>
        </header>

        <section class="summary">
            <div class="summary-card">
                <span>Sample Condition</span>
                <strong>{{ $condition }}</strong>
            </div>
            <div class="summary-card">
                <span>Viability</span>
                <strong class="{{ $viabilityClass }}">{{ $viability !== null ? number_format($viability, 2) . '%' : 'N/A' }}</strong>
            </div>
            <div class="summary-card">
                <span>Total Viable Cells</span>
                <strong>{{ $decimal($report->total_viable_cells_millions, 2, ' x10^6') }}</strong>
            </div>
            <div class="summary-card">
                <span>Cryovials Frozen</span>
                <strong>{{ $report->cryovials_frozen ?? 'N/A' }}</strong>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title">Specimen Identification</h2>
            <table>
                <tbody>
                    <tr>
                        <th>Study Code</th>
                        <td>{{ $report->study_code ?? 'IAVIC114' }}</td>
                        <th>Participant ID</th>
                        <td>{{ $report->participant_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Visit Code</th>
                        <td>{{ $report->visit_code ?? 'N/A' }}</td>
                        <th>Operator Initials</th>
                        <td>{{ $report->operator_initials ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Tube Type</th>
                        <td>{{ $report->sample_tube_type ?? 'N/A' }}</td>
                        <th>Plasma Harvesting</th>
                        <td>{{ $report->plasma_harvesting ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title">Processing and Cell Count Results</h2>
            <table>
                <tbody>
                    <tr>
                        <th>Total Blood Volume</th>
                        <td>{{ $decimal($report->total_blood_volume_ml, 2, ' mL') }}</td>
                        <th>Counting Method</th>
                        <td>{{ $report->counting_method ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Dilution Factor</th>
                        <td>{{ $decimal($report->dilution_factor, 1) }}</td>
                        <th>Viable Cells / mL</th>
                        <td>{{ $decimal($report->viable_cells_per_ml_millions, 3, ' x10^6/mL') }}</td>
                    </tr>
                    <tr>
                        <th>Resuspension Volume</th>
                        <td>{{ $decimal($report->resuspension_volume_ml, 2, ' mL') }}</td>
                        <th>Cell Yield / mL Blood</th>
                        <td>{{ $decimal($report->cell_yield_per_ml_blood, 3) }}</td>
                    </tr>
                    <tr>
                        <th>Final CPS Volume</th>
                        <td>{{ $decimal($report->final_cps_volume_ml, 2, ' mL') }}</td>
                        <th>Actual Cells / Vial</th>
                        <td>{{ $decimal($report->actual_cells_per_vial_millions, 2, ' x10^6') }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title">Timing and Traceability</h2>
            <table>
                <tbody>
                    <tr>
                        <th>Blood Draw Time</th>
                        <td>{{ $time($report->blood_draw_time) }}</td>
                        <th>Lab Processing Start</th>
                        <td>{{ $time($report->lab_processing_start_time) }}</td>
                    </tr>
                    <tr>
                        <th>Freezing Time</th>
                        <td>{{ $time($report->freezing_time) }}</td>
                        <th>Processing to Freezing</th>
                        <td>{{ $duration($report->processing_to_freezing_minutes) }}</td>
                    </tr>
                    <tr>
                        <th>Blood Draw to Freezing</th>
                        <td>{{ $duration($report->blood_draw_to_freezing_minutes) }}</td>
                        <th>Source Sheet / Row</th>
                        <td>{{ $report->source_sheet ?? 'N/A' }} / {{ $report->source_row_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Source Workbook</th>
                        <td colspan="3">{{ $report->source_workbook ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title">Comments</h2>
            <div class="comments">{{ $report->comments ?: 'No comments recorded.' }}</div>
        </section>

        <section class="signatures">
            <div class="signature-box">
                <span>Prepared / Reviewed By</span>
                {{ $report->operator_initials ?? 'N/A' }}
            </div>
            <div class="signature-box">
                <span>Generated By</span>
                {{ $generatedBy?->name ?? 'System' }} on {{ $generatedAt->format('d M Y H:i') }}
            </div>
        </section>

        <footer class="footer">
            <span>Electronically generated PBMC laboratory results report.</span>
            <span>Record ID: {{ $report->id }}</span>
        </footer>
    </main>
</body>
</html>

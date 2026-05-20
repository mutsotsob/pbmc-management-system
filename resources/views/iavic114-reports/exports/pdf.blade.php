<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1f2937;
            margin: 24px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 24px;
        }

        p {
            margin: 0 0 16px;
            color: #4b5563;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .actions {
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            background: #111827;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 8px;
            margin-right: 8px;
            font-size: 13px;
        }

        @media print {
            .actions {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="actions">
        <a href="javascript:window.print()" class="button">Print / Save as PDF</a>
        <a href="{{ route('dashboard') }}" class="button">Back to Dashboard</a>
    </div>

    <h1>{{ $title }}</h1>
    <p>Generated {{ $generatedAt->format('d M Y H:i') }} | {{ $reports->count() }} record(s)</p>

    <table>
        <thead>
            <tr>
                <th>Sample ID / Visit</th>
                <th>Date</th>
                <th>Condition</th>
                <th>Viability</th>
                <th>Total Viable Cells</th>
                <th>Cryovials</th>
                <th>Operator</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reports as $report)
                <tr>
                    <td>{{ $report->sample_id_visit_number }}</td>
                    <td>{{ $report->report_date?->format('d M Y') ?? 'N/A' }}</td>
                    <td>{{ $report->sample_condition ?? 'N/A' }}</td>
                    <td>{{ $report->viability_percent !== null ? number_format((float) $report->viability_percent, 2) . '%' : 'N/A' }}</td>
                    <td>{{ $report->total_viable_cells_millions !== null ? number_format((float) $report->total_viable_cells_millions, 2) . ' x10^6' : 'N/A' }}</td>
                    <td>{{ $report->cryovials_frozen ?? 'N/A' }}</td>
                    <td>{{ $report->operator_initials ?? 'N/A' }}</td>
                    <td>{{ $report->comments ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

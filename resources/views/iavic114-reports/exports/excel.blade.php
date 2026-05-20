<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IAVIC114 PBMC Reports Export</title>
</head>
<body>
    <table border="1">
        <thead>
            <tr>
                <th>Sample ID / Visit</th>
                <th>Participant ID</th>
                <th>Visit Code</th>
                <th>Report Date</th>
                <th>Blood Volume (mL)</th>
                <th>Blood Draw Time</th>
                <th>Condition</th>
                <th>Viability (%)</th>
                <th>Viable Cells / mL</th>
                <th>Resuspension Volume (mL)</th>
                <th>Total Viable Cells</th>
                <th>Cell Yield / mL Blood</th>
                <th>Actual Cells / Vial</th>
                <th>Cryovials Frozen</th>
                <th>Lab Processing Start</th>
                <th>Freezing Time</th>
                <th>Processing to Freezing (min)</th>
                <th>Blood Draw to Freezing (min)</th>
                <th>Operator</th>
                <th>Comments</th>
                <th>Workbook</th>
                <th>Sheet</th>
                <th>Source Row</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reports as $report)
                <tr>
                    <td>{{ $report->sample_id_visit_number }}</td>
                    <td>{{ $report->participant_id }}</td>
                    <td>{{ $report->visit_code }}</td>
                    <td>{{ $report->report_date?->format('Y-m-d') }}</td>
                    <td>{{ $report->total_blood_volume_ml }}</td>
                    <td>{{ $report->blood_draw_time }}</td>
                    <td>{{ $report->sample_condition }}</td>
                    <td>{{ $report->viability_percent }}</td>
                    <td>{{ $report->viable_cells_per_ml_millions }}</td>
                    <td>{{ $report->resuspension_volume_ml }}</td>
                    <td>{{ $report->total_viable_cells_millions }}</td>
                    <td>{{ $report->cell_yield_per_ml_blood }}</td>
                    <td>{{ $report->actual_cells_per_vial_millions }}</td>
                    <td>{{ $report->cryovials_frozen }}</td>
                    <td>{{ $report->lab_processing_start_time }}</td>
                    <td>{{ $report->freezing_time }}</td>
                    <td>{{ $report->processing_to_freezing_minutes }}</td>
                    <td>{{ $report->blood_draw_to_freezing_minutes }}</td>
                    <td>{{ $report->operator_initials }}</td>
                    <td>{{ $report->comments }}</td>
                    <td>{{ $report->source_workbook }}</td>
                    <td>{{ $report->source_sheet }}</td>
                    <td>{{ $report->source_row_number }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

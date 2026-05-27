<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        @page { size: landscape; margin: 14mm; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111827; margin: 0; }
        .header { display: flex; justify-content: space-between; gap: 24px; border-bottom: 2px solid #f97316; padding-bottom: 12px; margin-bottom: 18px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .meta { color: #6b7280; font-size: 11px; line-height: 1.5; text-align: right; }
        table { border-collapse: collapse; width: 100%; font-size: 11px; }
        th, td { border: 1px solid #d1d5db; padding: 7px 8px; vertical-align: top; }
        th { background: #f3f4f6; color: #374151; text-align: left; text-transform: uppercase; font-size: 10px; letter-spacing: .04em; }
        tr:nth-child(even) td { background: #fafafa; }
        .badge { display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 10px; font-weight: 700; }
        .active { background: #dcfce7; color: #166534; }
        .disabled { background: #fee2e2; color: #991b1b; }
        .actions { margin-top: 16px; text-align: right; }
        .button { border: 0; border-radius: 6px; background: #f97316; color: white; cursor: pointer; padding: 9px 14px; font-size: 12px; font-weight: 700; }
        @media print {
            .actions { display: none; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>{{ $title }}</h1>
            <div style="font-size: 12px; color: #6b7280;">{{ $users->count() }} user record(s)</div>
        </div>
        <div class="meta">
            Generated {{ $generatedAt->format('d M Y H:i') }}<br>
            By {{ $generatedBy?->name ?? 'System' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Job Title</th>
                <th>Phone</th>
                <th>Type</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->department ?: '-' }}</td>
                    <td>{{ $user->job_title ?: '-' }}</td>
                    <td>{{ $user->phone_number ?: '-' }}</td>
                    <td>{{ ucfirst((string) $user->user_type) }}</td>
                    <td>
                        <span class="badge {{ $user->user_status ? 'active' : 'disabled' }}">
                            {{ $user->user_status ? 'Active' : 'Disabled' }}
                        </span>
                    </td>
                    <td>{{ $user->created_at?->format('d M Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #6b7280; padding: 24px;">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="actions">
        <button type="button" class="button" onclick="window.print()">Print / Save PDF</button>
    </div>
</body>
</html>

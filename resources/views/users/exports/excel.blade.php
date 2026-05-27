<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Users Report</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; font-size: 12px; }
        th { background: #f3f4f6; font-weight: 700; text-align: left; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Job Title</th>
                <th>Phone Number</th>
                <th>User Type</th>
                <th>Status</th>
                <th>Email Verified At</th>
                <th>Created At</th>
                <th>Updated At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->department }}</td>
                    <td>{{ $user->job_title }}</td>
                    <td>{{ $user->phone_number }}</td>
                    <td>{{ ucfirst((string) $user->user_type) }}</td>
                    <td>{{ $user->user_status ? 'Active' : 'Disabled' }}</td>
                    <td>{{ $user->email_verified_at?->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $user->created_at?->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $user->updated_at?->format('Y-m-d H:i:s') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

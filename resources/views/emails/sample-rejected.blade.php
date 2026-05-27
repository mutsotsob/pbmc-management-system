<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Rejected - {{ $dispatch->reference }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f3f4f6; font-family: Arial, Helvetica, sans-serif; color: #1f2937; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .header { background: #dc2626; padding: 28px 32px; }
        .header h1 { margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; }
        .header p { margin: 4px 0 0; color: #fee2e2; font-size: 13px; }
        .body { padding: 28px 32px; }
        .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin: 0 0 12px; }
        .card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; }
        .alert { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px; }
        .grid { display: table; width: 100%; border-collapse: collapse; }
        .cell { display: table-cell; width: 50%; padding: 6px 0; vertical-align: top; }
        .label { font-size: 11px; color: #6b7280; margin-bottom: 2px; }
        .value { font-size: 14px; color: #111827; font-weight: 600; }
        .badge { display: inline-block; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 6px; padding: 3px 10px; font-size: 12px; font-weight: 700; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; text-align: center; font-size: 11px; color: #9ca3af; }
        .ref { font-family: monospace; font-size: 13px; background: #fef2f2; color: #b91c1c; padding: 2px 8px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Sample Rejected</h1>
        <p>The laboratory rejected this dispatched sample during receipt review.</p>
    </div>

    <div class="body">
        <p style="margin:0 0 20px;">
            Reference: <span class="ref">{{ $dispatch->reference }}</span>
            &nbsp; <span class="badge">Rejected</span>
        </p>

        <div class="alert">
            <strong>Rejection reason:</strong><br>
            {{ $dispatch->rejection_reason ?: 'No reason provided' }}
        </div>

        <p class="section-title">Sample Details</p>
        <div class="card">
            <div class="grid">
                <div class="cell">
                    <div class="label">Participant ID</div>
                    <div class="value">{{ $dispatch->sample_id }}</div>
                </div>
                <div class="cell">
                    <div class="label">Study / Visit</div>
                    <div class="value">{{ $dispatch->study ?: '-' }} / {{ $dispatch->visit ?: '-' }}</div>
                </div>
            </div>
            <div class="grid" style="margin-top:12px;">
                <div class="cell">
                    <div class="label">No. of bags</div>
                    <div class="value">{{ $dispatch->no_of_bags ?? '-' }}</div>
                </div>
                <div class="cell">
                    <div class="label">Condition</div>
                    <div class="value">{{ $dispatch->condition_on_arrival ?? 'Rejected' }}</div>
                </div>
            </div>
            <div class="grid" style="margin-top:12px;">
                <div class="cell">
                    <div class="label">Route</div>
                    <div class="value">{{ $dispatch->origin_location }} to {{ $dispatch->destination }}</div>
                </div>
            </div>
        </div>

        <p class="section-title">People</p>
        <div class="card">
            <div class="grid">
                <div class="cell">
                    <div class="label">Driver</div>
                    <div class="value">{{ $dispatch->driver_name ?: '-' }}</div>
                </div>
                <div class="cell">
                    <div class="label">Dispatched By</div>
                    <div class="value">{{ $dispatch->dispatchedBy?->name ?? '-' }}</div>
                </div>
            </div>
            <div class="grid" style="margin-top:12px;">
                <div class="cell">
                    <div class="label">Rejected By</div>
                    <div class="value">{{ $dispatch->receivedBy?->name ?? 'Laboratory' }}</div>
                </div>
                <div class="cell">
                    <div class="label">Rejected At</div>
                    <div class="value">{{ $dispatch->received_at?->format('d M Y H:i') ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        This is an automated notification from the Samples Management System.<br>
        Please do not reply to this email.
    </div>
</div>
</body>
</html>

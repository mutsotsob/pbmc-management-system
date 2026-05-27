<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Dispatched - {{ $dispatch->reference }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f3f4f6; font-family: Arial, Helvetica, sans-serif; color: #1f2937; }
        .wrapper { max-width: 680px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .header { background: #f97316; padding: 28px 32px; }
        .header h1 { margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; }
        .header p  { margin: 4px 0 0; color: #fff7ed; font-size: 13px; }
        .body { padding: 28px 32px; }
        .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin: 0 0 12px; }
        .card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; }
        .grid { display: table; width: 100%; border-collapse: collapse; }
        .cell { display: table-cell; width: 50%; padding: 6px 0; vertical-align: top; }
        .label { font-size: 11px; color: #6b7280; margin-bottom: 2px; }
        .value { font-size: 14px; color: #111827; font-weight: 600; }
        .badge { display: inline-block; background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; border-radius: 6px; padding: 3px 10px; font-size: 12px; font-weight: 700; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; text-align: center; font-size: 11px; color: #9ca3af; }
        .ref { font-family: monospace; font-size: 13px; background: #fff7ed; color: #ea580c; padding: 2px 8px; border-radius: 4px; }
        .actions { margin: 20px 0; }
        .btn { display: inline-block; text-decoration: none; color: #fff !important; font-size: 13px; font-weight: 700; padding: 10px 16px; border-radius: 8px; margin-right: 8px; }
        .btn-green { background: #16a34a; }
        .btn-red { background: #dc2626; }
        ul { margin: 0; padding-left: 18px; }
        li { margin: 0 0 6px; }
    </style>
</head>
<body>
@php
    $recipientType = $recipientType ?? 'lab';
    $isLab = $recipientType === 'lab';
    $participants = $dispatch->items->pluck('participant_id')->filter()->values();
    $receiveUrl = route('sample-dispatches.show', ['sampleDispatch' => $dispatch->id, 'email_action' => 'receive']) . '#confirm-receipt';
    $rejectUrl = route('sample-dispatches.show', ['sampleDispatch' => $dispatch->id, 'email_action' => 'reject']) . '#confirm-receipt';
@endphp
<div class="wrapper">
    <div class="header">
        <h1>{{ $isLab ? 'Sample Dispatched to Laboratory' : 'Dispatch Assignment Notification' }}</h1>
        <p>Reference {{ $dispatch->reference }} is now in transit.</p>
    </div>

    <div class="body">
        <p style="margin:0 0 20px;">
            Reference: <span class="ref">{{ $dispatch->reference }}</span>
            &nbsp; <span class="badge">Dispatched</span>
        </p>

        <p class="section-title">Dispatch Summary</p>
        <div class="card">
            <div class="grid">
                <div class="cell">
                    <div class="label">Study</div>
                    <div class="value">{{ $dispatch->study ?: '-' }}</div>
                </div>
                <div class="cell">
                    <div class="label">Visit</div>
                    <div class="value">{{ $dispatch->visit ?: '-' }}</div>
                </div>
            </div>
            <div class="grid" style="margin-top:12px;">
                <div class="cell">
                    <div class="label">Route</div>
                    <div class="value">{{ $dispatch->origin_location }} to {{ $dispatch->destination }}</div>
                </div>
                <div class="cell">
                    <div class="label">No. of Bags</div>
                    <div class="value">{{ $dispatch->no_of_bags ?? '-' }}</div>
                </div>
            </div>
            <div class="grid" style="margin-top:12px;">
                <div class="cell">
                    <div class="label">Dispatch Date</div>
                    <div class="value">{{ $dispatch->dispatch_date?->format('d M Y') ?? '-' }}</div>
                </div>
                <div class="cell">
                    <div class="label">Dispatch Time</div>
                    <div class="value">{{ $dispatch->dispatch_time ? substr($dispatch->dispatch_time, 0, 5) : '-' }}</div>
                </div>
            </div>
        </div>

        <p class="section-title">Driver Details</p>
        <div class="card">
            <div class="grid">
                <div class="cell">
                    <div class="label">Driver Name</div>
                    <div class="value">{{ $dispatch->driver_name ?: '-' }}</div>
                </div>
                <div class="cell">
                    <div class="label">Phone</div>
                    <div class="value">{{ $dispatch->driver_phone ?: '-' }}</div>
                </div>
            </div>
        </div>

        @if ($isLab)
            <p class="section-title">Full Sample Details</p>
            <div class="card">
                <div class="label" style="margin-bottom:8px;">Participant IDs</div>
                @if ($participants->isNotEmpty())
                    <ul>
                        @foreach ($participants as $participant)
                            <li><span class="value" style="font-size:13px;">{{ $participant }}</span></li>
                        @endforeach
                    </ul>
                @else
                    <div class="value" style="font-size:13px;">{{ $dispatch->sample_id ?: '-' }}</div>
                @endif

                @if ($dispatch->notes)
                    <div style="margin-top:12px; border-top:1px solid #e5e7eb; padding-top:10px;">
                        <div class="label">Notes</div>
                        <div style="font-size:13px;color:#374151;margin-top:2px;">{{ $dispatch->notes }}</div>
                    </div>
                @endif
            </div>

            <div class="actions">
                <a href="{{ $receiveUrl }}" class="btn btn-green">Receive Sample</a>
                <a href="{{ $rejectUrl }}" class="btn btn-red">Reject Sample (Reason Required)</a>
            </div>
            <p style="margin:0;font-size:12px;color:#6b7280;">
                The reject action opens the dispatch page with the rejection reason field enabled.
            </p>
        @else
            <p style="margin:0;font-size:13px;color:#6b7280;">
                You are assigned to transport this dispatch. Keep this reference for handover at the laboratory.
            </p>
        @endif

        <p style="margin:18px 0 0;font-size:13px;color:#6b7280;">
            Dispatched by <strong style="color:#111827;">{{ $dispatch->dispatchedBy?->name ?? 'System' }}</strong>.
        </p>
    </div>

    <div class="footer">
        This is an automated notification from the Samples Management System.<br>
        Please do not reply to this email.
    </div>
</div>
</body>
</html>

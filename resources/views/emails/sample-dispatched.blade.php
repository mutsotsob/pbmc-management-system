<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Dispatched – {{ $dispatch->reference }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f3f4f6; font-family: Arial, Helvetica, sans-serif; color: #1f2937; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
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
        .arrow { color: #9ca3af; margin: 0 6px; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; text-align: center; font-size: 11px; color: #9ca3af; }
        .ref { font-family: monospace; font-size: 13px; background: #fff7ed; color: #ea580c; padding: 2px 8px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <h1>Sample Dispatched</h1>
        <p>A sample has been dispatched to the lab and is on its way.</p>
    </div>

    <div class="body">

        {{-- Reference + status --}}
        <p style="margin:0 0 20px;">
            Reference: <span class="ref">{{ $dispatch->reference }}</span>
            &nbsp; <span class="badge">Dispatched</span>
        </p>

        {{-- Sample details --}}
        <p class="section-title">Sample Details</p>
        <div class="card">
            <div class="grid">
                <div class="cell">
                    <div class="label">Sample ID</div>
                    <div class="value">{{ $dispatch->sample_id }}</div>
                </div>
                <div class="cell">
                    <div class="label">Study</div>
                    <div class="value">{{ $dispatch->study }}</div>
                </div>
            </div>
            <div class="grid" style="margin-top:12px;">
                <div class="cell">
                    <div class="label">Quantity</div>
                    <div class="value">{{ $dispatch->quantity ?? 1 }}</div>
                </div>
                <div class="cell">
                    <div class="label">Route</div>
                    <div class="value">
                        {{ $dispatch->origin_location }}
                        <span class="arrow">→</span>
                        {{ $dispatch->destination }}
                    </div>
                </div>
            </div>
            <div class="grid" style="margin-top:12px;">
                <div class="cell">
                    <div class="label">Dispatch Date</div>
                    <div class="value">{{ $dispatch->dispatch_date->format('d M Y') }}</div>
                </div>
                <div class="cell">
                    <div class="label">Dispatch Time</div>
                    <div class="value">{{ $dispatch->dispatch_time ? substr($dispatch->dispatch_time, 0, 5) : '—' }}</div>
                </div>
            </div>
            @if($dispatch->notes)
            <div style="margin-top:12px; border-top:1px solid #e5e7eb; padding-top:10px;">
                <div class="label">Notes</div>
                <div style="font-size:13px;color:#374151;margin-top:2px;">{{ $dispatch->notes }}</div>
            </div>
            @endif
        </div>

        {{-- Driver details --}}
        <p class="section-title">Driver Details</p>
        <div class="card">
            <div class="grid">
                <div class="cell">
                    <div class="label">Driver Name</div>
                    <div class="value">{{ $dispatch->driver_name }}</div>
                </div>
                <div class="cell">
                    <div class="label">Phone</div>
                    <div class="value">{{ $dispatch->driver_phone ?: '—' }}</div>
                </div>
            </div>
            @if($dispatch->driverUser?->vehicle_registration)
            <div class="grid" style="margin-top:12px;">
                <div class="cell">
                    <div class="label">Vehicle Reg.</div>
                    <div class="value">{{ $dispatch->driverUser->vehicle_registration }}</div>
                </div>
            </div>
            @endif
        </div>

        {{-- Dispatched by --}}
        <p style="margin:0;font-size:13px;color:#6b7280;">
            Dispatched by <strong style="color:#111827;">{{ $dispatch->dispatchedBy?->name ?? 'System' }}</strong>
            on {{ $dispatch->dispatch_date->format('d M Y') }}
            @if($dispatch->dispatch_time) at {{ substr($dispatch->dispatch_time, 0, 5) }} @endif.
        </p>

    </div>

    <div class="footer">
        This is an automated notification from the PBMC Processing Portal.<br>
        Please do not reply to this email.
    </div>

</div>
</body>
</html>

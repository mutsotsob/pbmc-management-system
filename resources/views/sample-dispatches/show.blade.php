@extends('layouts.app')

@section('title', 'Dispatch - ' . $dispatch->reference)
@section('page-title', 'Sample Dispatch')

@section('content')
    @php
        $statusLabel = $dispatch->status === 'dispatched' ? 'Dispatched' : 'Received';
        $dispatchTime = $dispatch->dispatch_time ? substr($dispatch->dispatch_time, 0, 5) : null;
        $conditionClass = match ($dispatch->condition_on_arrival) {
            'Good' => 'text-green-700',
            'Compromised' => 'text-yellow-700',
            'Rejected' => 'text-red-700',
            default => 'text-gray-700',
        };
        $backToDashboard = auth()->user()?->isDepartment('Laboratory') && !auth()->user()?->hasFullAccessDepartment();
    @endphp

    <div class="mx-auto max-w-6xl space-y-4">

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-3">
                            <h2 class="font-mono text-xl font-bold text-gray-900">{{ $dispatch->reference }}</h2>
                            <x-status-badge :value="$statusLabel" />
                        </div>
                        <p class="text-sm text-gray-600">
                            Participant IDs were dispatched on {{ $dispatch->dispatch_date->format('d M Y') }}
                            @if ($dispatchTime)
                                at {{ $dispatchTime }}
                            @endif
                            by <span
                                class="font-semibold text-gray-900">{{ $dispatch->dispatchedBy?->name ?? 'Unknown' }}</span>.
                        </p>
                    </div>

                    <a href="{{ $backToDashboard ? route('dashboard') : route('sample-dispatches.index') }}"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i data-feather="arrow-left" class="h-4 w-4"></i>
                        {{ $backToDashboard ? 'Back to Receipt Queue' : 'Back to Dispatches' }}
                    </a>
                </div>
            </div>

            <div class="p-5 sm:p-6">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                    <div class="space-y-7 lg:col-span-7">
                        <section>
                            <div class="mb-4 flex items-center gap-2">
                                <i data-feather="package" class="h-4 w-4 text-gray-500"></i>
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Sample and Route
                                </h3>
                            </div>

                            <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Participant IDs
                                    </dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900">
                                        @forelse($dispatch->items as $item)
                                            <div>{{ $item->participant_id }}</div>
                                        @empty
                                            {{ $dispatch->sample_id }}
                                        @endforelse
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Study</dt>
                                    <dd class="mt-1 text-sm text-gray-700">{{ $dispatch->study ?: '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Visit</dt>
                                    <dd class="mt-1 text-sm text-gray-700">{{ $dispatch->visit ?: '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Quantity</dt>
                                    <dd class="mt-1 text-sm text-gray-700">{{ $dispatch->quantity }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">No. of bags</dt>
                                    <dd class="mt-1 text-sm text-gray-700">{{ $dispatch->no_of_bags ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Status</dt>
                                    <dd class="mt-1">
                                        <x-status-badge :value="$statusLabel" />
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Origin</dt>
                                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $dispatch->origin_location }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Destination</dt>
                                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $dispatch->destination }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Dispatch Date</dt>
                                    <dd class="mt-1 text-sm text-gray-700">{{ $dispatch->dispatch_date->format('d M Y') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Dispatch Time</dt>
                                    <dd class="mt-1 text-sm text-gray-700">{{ $dispatchTime ?? '-' }}</dd>
                                </div>
                            </dl>
                        </section>

                        <section class="border-t border-gray-100 pt-6">
                            <div class="mb-4 flex items-center gap-2">
                                <i data-feather="truck" class="h-4 w-4 text-gray-500"></i>
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Driver and Dispatch
                                    Team</h3>
                            </div>

                            <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Driver Name</dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $dispatch->driver_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Driver Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-700">{{ $dispatch->driver_phone ?: '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Dispatched By</dt>
                                    <dd class="mt-1 text-sm text-gray-700">
                                        {{ $dispatch->dispatchedBy?->name ?? 'Unknown' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Registered Driver
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-700">{{ $dispatch->driverUser?->name ?? '-' }}</dd>
                                </div>
                            </dl>
                        </section>

                        @if ($dispatch->notes)
                            <section class="border-t border-gray-100 pt-6">
                                <div class="mb-2 flex items-center gap-2">
                                    <i data-feather="file-text" class="h-4 w-4 text-gray-500"></i>
                                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Dispatch Notes
                                    </h3>
                                </div>
                                <p class="text-sm leading-6 text-gray-700">{{ $dispatch->notes }}</p>
                            </section>
                        @endif
                    </div>

                    <aside class="border-t border-gray-100 pt-7 lg:col-span-5 lg:border-l lg:border-t-0 lg:pl-8 lg:pt-0">
                        @if ($dispatch->isReceived())
                            <section>
                                <div class="mb-4 flex items-center gap-2">
                                    <i data-feather="check-circle" class="h-4 w-4 text-green-600"></i>
                                    <h3 class="text-sm font-semibold uppercase tracking-wide text-green-800">Receipt
                                        Confirmed</h3>
                                </div>

                                <dl class="grid grid-cols-1 gap-y-5 sm:grid-cols-2 sm:gap-x-8 lg:grid-cols-1">
                                    <div>
                                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Received By
                                        </dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900">
                                            {{ $dispatch->receivedBy?->name ?? '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Received At
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-700">
                                            {{ $dispatch->received_at?->format('d M Y, H:i') ?? '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Condition on
                                            Arrival</dt>
                                        <dd class="mt-1 text-sm font-semibold {{ $conditionClass }}">
                                            {{ $dispatch->condition_on_arrival ?? '-' }}</dd>
                                    </div>
                                    @if ($dispatch->rejection_reason)
                                        <div class="sm:col-span-2 lg:col-span-1">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Rejection
                                                Reason</dt>
                                            <dd class="mt-1 text-sm leading-6 text-red-700">
                                                {{ $dispatch->rejection_reason }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </section>
                        @elseif($canReceiveSamples)
                            <section x-data="{ condition: @js(old('condition_on_arrival')) }">
                                <div class="mb-4 flex items-center gap-2">
                                    <i data-feather="clipboard" class="h-4 w-4 text-green-600"></i>
                                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Confirm Lab Receipt</h3>
                                </div>

                                <form method="POST" action="{{ route('sample-dispatches.receive', $dispatch) }}"
                                    class="space-y-4">
                                    @csrf

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">
                                            Condition on Arrival <span class="text-red-600">*</span>
                                        </label>
                                        <select name="condition_on_arrival" x-model="condition"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 @error('condition_on_arrival') border-red-400 @enderror">
                                            <option value="">Select condition</option>
                                            @foreach ($conditions as $cond)
                                                <option value="{{ $cond }}"
                                                    {{ old('condition_on_arrival') === $cond ? 'selected' : '' }}>
                                                    {{ $cond }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('condition_on_arrival')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div x-show="condition === 'Rejected'" x-transition>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">
                                            Rejection Reason <span class="text-red-600">*</span>
                                        </label>
                                        <textarea name="rejection_reason" rows="3" placeholder="Describe why the sample was rejected"
                                            class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 @error('rejection_reason') border-red-400 @enderror">{{ old('rejection_reason') }}</textarea>
                                        @error('rejection_reason')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Receipt Notes</label>
                                        <textarea name="notes" rows="3" placeholder="Any observations at receipt"
                                            class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800">{{ old('notes') }}</textarea>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit"
                                            class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                                            <i data-feather="check" class="h-4 w-4"></i>
                                            Confirm Receipt
                                        </button>
                                    </div>
                                </form>
                            </section>
                        @else
                            <section>
                                <div class="mb-3 flex items-center gap-2">
                                    <i data-feather="clock" class="h-4 w-4 text-amber-600"></i>
                                    <h3 class="text-sm font-semibold uppercase tracking-wide text-amber-800">Awaiting
                                        Laboratory Receipt</h3>
                                </div>
                                <p class="text-sm leading-6 text-gray-600">
                                    This sample has not been received yet. Laboratory users will see the receive action
                                    here.
                                </p>
                            </section>
                        @endif
                    </aside>
                </div>
            </div>

            <div class="border-t border-gray-200">
                <div class="flex items-center gap-2 px-5 py-4 sm:px-6">
                    <i data-feather="clock" class="h-4 w-4 text-gray-500"></i>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Dispatch History</h3>
                </div>

                @if ($history->isEmpty())
                    <div class="border-t border-gray-100 px-5 py-8 text-center text-sm text-gray-400 sm:px-6">
                        No history recorded yet.
                    </div>
                @else
                    <div class="overflow-x-auto border-t border-gray-100">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Event</th>
                                    <th class="px-4 py-3 text-left">Date &amp; Time</th>
                                    <th class="px-4 py-3 text-left">Performed By</th>
                                    <th class="px-4 py-3 text-left">Driver</th>
                                    <th class="px-4 py-3 text-left">Details</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($history as $entry)
                                    @php
                                        $eventStyles = match ($entry->event) {
                                            'created' => ['bg-blue-50 text-blue-700', 'Dispatched'],
                                            'status_changed' => ['bg-green-50 text-green-700', 'Status Changed'],
                                            'updated' => ['bg-yellow-50 text-yellow-700', 'Updated'],
                                            'deleted' => ['bg-red-50 text-red-700', 'Deleted'],
                                            'restored' => ['bg-purple-50 text-purple-700', 'Restored'],
                                            default => [
                                                'bg-gray-100 text-gray-600',
                                                ucfirst(str_replace('_', ' ', $entry->event)),
                                            ],
                                        };

                                        $driverName =
                                            $entry->new_values['driver_name'] ??
                                            ($entry->old_values['driver_name'] ?? $dispatch->driver_name);

                                        $driverPhone =
                                            $entry->new_values['driver_phone'] ??
                                            ($entry->old_values['driver_phone'] ?? $dispatch->driver_phone);

                                        $details = '';
                                        if ($entry->event === 'created') {
                                            $details =
                                                ($entry->new_values['origin_location'] ?? $dispatch->origin_location) .
                                                ' to ' .
                                                ($entry->new_values['destination'] ?? $dispatch->destination);
                                        } elseif ($entry->event === 'status_changed') {
                                            $old = $entry->old_values['status'] ?? '-';
                                            $new = $entry->new_values['status'] ?? '-';
                                            $details = ucfirst($old) . ' to ' . ucfirst($new);
                                            if (!empty($entry->new_values['condition_on_arrival'])) {
                                                $details .=
                                                    ' - Condition: ' . $entry->new_values['condition_on_arrival'];
                                            }
                                        } elseif ($entry->event === 'updated' && !empty($entry->new_values)) {
                                            $details = collect($entry->new_values)
                                                ->map(fn($v, $k) => ucwords(str_replace('_', ' ', $k)) . ': ' . $v)
                                                ->implode(', ');
                                        }
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <span
                                                class="inline-flex items-center rounded px-2 py-0.5 text-xs font-semibold {{ $eventStyles[0] }}">
                                                {{ $eventStyles[1] }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                            {{ $entry->created_at->format('d M Y') }}
                                            <span
                                                class="block text-xs text-gray-400">{{ $entry->created_at->format('H:i:s') }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">{{ $entry->user_name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-700">
                                            {{ $driverName ?? '-' }}
                                            @if ($driverPhone)
                                                <span class="block text-xs text-gray-400">{{ $driverPhone }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500">{{ $details ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

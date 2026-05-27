@extends('layouts.app')

@section('title', 'Laboratory Receipt Queue')
@section('topnav-title', 'Laboratory Receipt Queue')

@section('content')
    @php
        $hasSearch = filled($q ?? null);
        $sortUrl = function (string $column) use ($sort, $dir) {
            return request()->fullUrlWithQuery([
                'sort' => $column,
                'dir' => $sort === $column && $dir === 'asc' ? 'desc' : 'asc',
                'page' => 1,
            ]);
        };
        $sortIcon = fn (string $column) => $sort === $column ? ($dir === 'asc' ? 'chevron-up' : 'chevron-down') : null;
        $sortLinkClasses = 'inline-flex items-center gap-1 text-gray-500 hover:text-gray-900';
        $isReceivedTab = $sampleStatus === 'received';
        $activeTabClasses = 'border-pbmc bg-orange-50 text-pbmc';
        $inactiveTabClasses = 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900';
    @endphp

    <div class="space-y-5"
        x-data="{ rejectOpen: false, rejectAction: '', rejectReference: '', rejectionReason: '' }">
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Awaiting Receipt</p>
                    <i data-feather="inbox" class="h-4 w-4 text-pbmc"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900">{{ number_format($metrics['awaiting_receipt']) }}</p>
                <p class="mt-1 text-xs text-gray-500">Dispatched samples pending lab receipt</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Bags Pending</p>
                    <i data-feather="briefcase" class="h-4 w-4 text-emerald-600"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900">{{ number_format($metrics['bags_awaiting_receipt']) }}</p>
                <p class="mt-1 text-xs text-gray-500">Recorded on pending dispatched samples</p>
            </div>

            <a href="{{ route('iavic114-reports.index') }}"
                class="group rounded-lg border border-orange-200 bg-orange-50 p-5 shadow-sm transition hover:border-orange-300 hover:bg-orange-100">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-orange-700">Processing Reports</p>
                    <i data-feather="arrow-right-circle" class="h-5 w-5 text-orange-600 transition group-hover:translate-x-0.5"></i>
                </div>
                <p class="mt-3 text-lg font-bold text-gray-900">Open imported reports</p>
                <p class="mt-1 text-xs text-orange-800">Use this after receipt to view and create processing records.</p>
            </a>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <a href="{{ route('dashboard', ['sample_status' => 'dispatched']) }}"
                        class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold {{ !$isReceivedTab ? $activeTabClasses : $inactiveTabClasses }}">
                        <i data-feather="inbox" class="h-4 w-4"></i>
                        Awaiting Receipt
                    </a>
                    <a href="{{ route('dashboard', ['sample_status' => 'received']) }}"
                        class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold {{ $isReceivedTab ? $activeTabClasses : $inactiveTabClasses }}">
                        <i data-feather="check-circle" class="h-4 w-4"></i>
                        Received Samples
                    </a>
                </div>

                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $isReceivedTab ? 'Received Samples' : 'Dispatched Samples' }}</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $isReceivedTab ? 'Samples already received by the laboratory.' : 'Confirm receipt here before the sample is processed.' }}
                        </p>
                    </div>

                    <form method="GET" action="{{ route('dashboard') }}"
                        class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,18rem)_auto] sm:items-end">
                        <input type="hidden" name="sort" value="{{ $sort }}">
                        <input type="hidden" name="dir" value="{{ $dir }}">
                        <input type="hidden" name="sample_status" value="{{ $sampleStatus }}">
                        <div>
                            <label for="receiptSearch" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Search</label>
                            <input id="receiptSearch" type="search" name="q" value="{{ $q }}"
                                placeholder="Reference, participant, driver..."
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-pbmc focus:outline-none focus:ring-1 focus:ring-pbmc">
                        </div>
                        <div class="flex gap-2">
                            @if ($hasSearch)
                                <a href="{{ route('dashboard', ['sample_status' => $sampleStatus, 'sort' => $sort, 'dir' => $dir]) }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    <i data-feather="x" class="h-4 w-4"></i>
                                    Clear
                                </a>
                            @endif
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-pbmc px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                                <i data-feather="search" class="h-4 w-4"></i>
                                Search
                            </button>
                        </div>
                    </form>
                </div>

                <p class="mt-3 text-xs text-gray-500">
                    Showing {{ $dispatches->firstItem() ?? 0 }}-{{ $dispatches->lastItem() ?? 0 }} of {{ $dispatches->total() }} {{ $isReceivedTab ? 'received' : 'pending' }} record{{ $dispatches->total() === 1 ? '' : 's' }}
                </p>
            </div>

            @if ($dispatches->isEmpty())
                <div class="p-10 text-center">
                    <i data-feather="check-circle" class="mx-auto h-8 w-8 text-green-600"></i>
                    <p class="mt-3 text-sm font-medium text-gray-800">
                        {{ $isReceivedTab ? 'No samples have been received yet.' : 'No dispatched samples are awaiting receipt.' }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $isReceivedTab ? 'Received samples will appear here after laboratory confirmation.' : 'New dispatches will appear here when Clinical Operations sends them.' }}
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ $sortUrl('reference') }}" class="{{ $sortLinkClasses }}">
                                        Reference
                                        @if ($sortIcon('reference'))
                                            <i data-feather="{{ $sortIcon('reference') }}" class="h-3 w-3"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ $sortUrl('dispatch_date') }}" class="{{ $sortLinkClasses }}">
                                        Date
                                        @if ($sortIcon('dispatch_date'))
                                            <i data-feather="{{ $sortIcon('dispatch_date') }}" class="h-3 w-3"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ $sortUrl('study') }}" class="{{ $sortLinkClasses }}">
                                        Study
                                        @if ($sortIcon('study'))
                                            <i data-feather="{{ $sortIcon('study') }}" class="h-3 w-3"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ $sortUrl('visit') }}" class="{{ $sortLinkClasses }}">
                                        Visit
                                        @if ($sortIcon('visit'))
                                            <i data-feather="{{ $sortIcon('visit') }}" class="h-3 w-3"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ $sortUrl('sample_id') }}" class="{{ $sortLinkClasses }}">
                                        Participants
                                        @if ($sortIcon('sample_id'))
                                            <i data-feather="{{ $sortIcon('sample_id') }}" class="h-3 w-3"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ $sortUrl('no_of_bags') }}" class="{{ $sortLinkClasses }}">
                                        Bags
                                        @if ($sortIcon('no_of_bags'))
                                            <i data-feather="{{ $sortIcon('no_of_bags') }}" class="h-3 w-3"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ $sortUrl('origin_location') }}" class="{{ $sortLinkClasses }}">
                                        Route
                                        @if ($sortIcon('origin_location'))
                                            <i data-feather="{{ $sortIcon('origin_location') }}" class="h-3 w-3"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <a href="{{ $sortUrl('driver_name') }}" class="{{ $sortLinkClasses }}">
                                        Driver
                                        @if ($sortIcon('driver_name'))
                                            <i data-feather="{{ $sortIcon('driver_name') }}" class="h-3 w-3"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-left">Dispatched By</th>
                                @if ($isReceivedTab)
                                    <th class="px-4 py-3 text-left">
                                        <a href="{{ $sortUrl('received_at') }}" class="{{ $sortLinkClasses }}">
                                            Received
                                            @if ($sortIcon('received_at'))
                                                <i data-feather="{{ $sortIcon('received_at') }}" class="h-3 w-3"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left">Condition</th>
                                @endif
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($dispatches as $dispatch)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 font-mono text-xs text-gray-700">{{ $dispatch->reference }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">
                                        {{ $dispatch->dispatch_date?->format('d M Y') ?? '-' }}
                                        @if ($dispatch->dispatch_time)
                                            <span class="block text-xs text-gray-400">{{ substr($dispatch->dispatch_time, 0, 5) }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">{{ $dispatch->study ?: '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">{{ $dispatch->visit ?: '-' }}</td>
                                    <td class="px-4 py-4 text-gray-800">
                                        @forelse ($dispatch->items as $item)
                                            <div>{{ $item->participant_id }}</div>
                                        @empty
                                            {{ $dispatch->sample_id ?: '-' }}
                                        @endforelse
                                    </td>
                                    <td class="px-4 py-4 text-gray-700">{{ $dispatch->no_of_bags ?? '-' }}</td>
                                    <td class="px-4 py-4 text-gray-700">
                                        <span class="font-medium text-gray-900">{{ $dispatch->origin_location }}</span>
                                        <span class="mx-1 text-gray-400">to</span>
                                        {{ $dispatch->destination }}
                                    </td>
                                    <td class="px-4 py-4 text-gray-700">
                                        {{ $dispatch->driver_name ?: '-' }}
                                        @if ($dispatch->driver_phone)
                                            <span class="block text-xs text-gray-400">{{ $dispatch->driver_phone }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-gray-700">{{ $dispatch->dispatchedBy?->name ?? '-' }}</td>
                                    @if ($isReceivedTab)
                                        <td class="whitespace-nowrap px-4 py-4 text-gray-700">
                                            {{ $dispatch->received_at?->format('d M Y H:i') ?? '-' }}
                                            @if ($dispatch->receivedBy)
                                                <span class="block text-xs text-gray-400">{{ $dispatch->receivedBy->name }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-gray-700">{{ $dispatch->condition_on_arrival ?? '-' }}</td>
                                    @endif
                                    <td class="px-4 py-4 text-right">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            <a href="{{ route('sample-dispatches.show', $dispatch) }}"
                                                class="inline-flex items-center justify-center gap-2 rounded-lg {{ $isReceivedTab ? 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50' : 'bg-green-600 text-white hover:bg-green-700' }} px-3 py-2 text-xs font-semibold">
                                                <i data-feather="{{ $isReceivedTab ? 'eye' : 'check' }}" class="h-3 w-3"></i>
                                                {{ $isReceivedTab ? 'View' : 'Receive' }}
                                            </a>

                                            @if (!$isReceivedTab)
                                                <button type="button"
                                                    @click="rejectOpen = true; rejectAction = @js(route('sample-dispatches.reject', $dispatch)); rejectReference = @js($dispatch->reference); rejectionReason = ''; $nextTick(() => $refs.rejectionReasonInput?.focus())"
                                                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                    <i data-feather="x-circle" class="h-3 w-3"></i>
                                                    Reject
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-3 border-t border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-gray-500">
                        Showing {{ $dispatches->firstItem() ?? 0 }}-{{ $dispatches->lastItem() ?? 0 }} of {{ $dispatches->total() }} {{ $isReceivedTab ? 'received' : 'pending' }} record{{ $dispatches->total() === 1 ? '' : 's' }}
                    </p>
                    <div>
                        {{ $dispatches->links() }}
                    </div>
                </div>
            @endif
        </section>

        <div x-show="rejectOpen" x-cloak x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6">
            <div class="w-full max-w-lg rounded-lg border border-gray-200 bg-white shadow-xl"
                @click.outside="rejectOpen = false">
                <div class="border-b border-gray-200 px-5 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Reject Sample</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Reference <span class="font-mono font-semibold text-gray-800" x-text="rejectReference"></span>
                            </p>
                        </div>
                        <button type="button" @click="rejectOpen = false"
                            class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                            <i data-feather="x" class="h-5 w-5"></i>
                        </button>
                    </div>
                </div>

                <form method="POST" :action="rejectAction" class="space-y-4 px-5 py-5">
                    @csrf

                    <div>
                        <label for="rejectionReason" class="mb-1 block text-sm font-medium text-gray-700">
                            Rejection reason <span class="text-red-600">*</span>
                        </label>
                        <textarea id="rejectionReason" name="rejection_reason" x-model="rejectionReason"
                            x-ref="rejectionReasonInput" rows="4" required maxlength="500"
                            placeholder="Describe why this sample is being rejected"
                            class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"></textarea>
                        <p class="mt-1 text-xs text-gray-500">This note will be sent to the assigned driver and Clinical Operations.</p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="rejectOpen = false"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" :disabled="rejectionReason.trim().length === 0"
                            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                            <i data-feather="x-circle" class="h-4 w-4"></i>
                            Reject Sample
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

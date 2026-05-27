@extends('layouts.app')

@section('title', 'Transport Metrics')
@section('topnav-title', 'My Transport Metrics')

@section('content')
    @php
        $hasDateFilter = filled($from) || filled($to);
        $sortUrl = function (string $column) use ($sort, $dir) {
            return request()->fullUrlWithQuery([
                'sort' => $column,
                'dir' => $sort === $column && $dir === 'asc' ? 'desc' : 'asc',
                'page' => 1,
            ]);
        };
        $sortIcon = fn (string $column) => $sort === $column ? ($dir === 'asc' ? 'chevron-up' : 'chevron-down') : null;
        $sortLinkClasses = 'inline-flex items-center gap-1 text-gray-500 hover:text-gray-900';
    @endphp

    <div class="space-y-5">
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Samples Transported</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Metrics shown here include only dispatches assigned to you as the registered driver.
                    </p>
                </div>

                <form method="GET" action="{{ route('dashboard') }}"
                    class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-end">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="dir" value="{{ $dir }}">
                    <div>
                        <label for="from" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">From</label>
                        <input id="from" type="date" name="from" value="{{ $from }}"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-pbmc focus:outline-none focus:ring-1 focus:ring-pbmc">
                    </div>
                    <div>
                        <label for="to" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">To</label>
                        <input id="to" type="date" name="to" value="{{ $to }}"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-pbmc focus:outline-none focus:ring-1 focus:ring-pbmc">
                    </div>
                    <div class="flex gap-2">
                        @if ($hasDateFilter)
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <i data-feather="x" class="h-4 w-4"></i>
                                Clear
                            </a>
                        @endif
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-pbmc px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                            <i data-feather="filter" class="h-4 w-4"></i>
                            Apply
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Dispatches</p>
                    <i data-feather="truck" class="h-4 w-4 text-pbmc"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900">{{ number_format($metrics['total_dispatches']) }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ number_format($metrics['this_month_count']) }} this month</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Samples Carried</p>
                    <i data-feather="package" class="h-4 w-4 text-blue-600"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900">{{ number_format($metrics['total_samples']) }}</p>
                <p class="mt-1 text-xs text-gray-500">Participant/sample quantity</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">No. of Bags</p>
                    <i data-feather="briefcase" class="h-4 w-4 text-emerald-600"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900">{{ number_format($metrics['total_bags']) }}</p>
                <p class="mt-1 text-xs text-gray-500">Bags recorded on assigned dispatches</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Receipt Rate</p>
                    <i data-feather="check-circle" class="h-4 w-4 text-green-600"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900">{{ $metrics['completion_rate'] }}%</p>
                <p class="mt-1 text-xs text-gray-500">
                    {{ number_format($metrics['received_dispatches']) }} received, {{ number_format($metrics['pending_dispatches']) }} pending
                </p>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm xl:col-span-4">
                <div class="flex items-center gap-2">
                    <i data-feather="bar-chart-2" class="h-4 w-4 text-gray-500"></i>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Six Month Trend</h3>
                </div>

                <div class="mt-5 space-y-4">
                    @forelse ($monthlyTrend as $month)
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <span class="font-medium text-gray-600">{{ $month['label'] }}</span>
                                <span class="text-gray-500">{{ $month['count'] }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-pbmc"
                                    style="width: {{ max(8, round(($month['count'] / $maxMonthlyDispatches) * 100)) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-200 p-6 text-center text-sm text-gray-500">
                            No transported samples found for this period.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm xl:col-span-8">
                <div class="border-b border-gray-200 px-5 py-4">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">Assigned Transport Log</h3>
                            <p class="mt-1 text-xs text-gray-500">Only dispatches assigned to your user account are listed.</p>
                        </div>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                            {{ $dispatches->total() }} record{{ $dispatches->total() === 1 ? '' : 's' }}
                        </span>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">
                        Showing {{ $dispatches->firstItem() ?? 0 }}-{{ $dispatches->lastItem() ?? 0 }} of {{ $dispatches->total() }} record{{ $dispatches->total() === 1 ? '' : 's' }}
                    </p>
                </div>

                @if ($dispatches->isEmpty())
                    <div class="p-8 text-center text-sm text-gray-500">
                        No transported samples have been assigned to you yet.
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
                                        <a href="{{ $sortUrl('status') }}" class="{{ $sortLinkClasses }}">
                                            Status
                                            @if ($sortIcon('status'))
                                                <i data-feather="{{ $sortIcon('status') }}" class="h-3 w-3"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-4 py-3 text-left">
                                        <a href="{{ $sortUrl('received_at') }}" class="{{ $sortLinkClasses }}">
                                            Received
                                            @if ($sortIcon('received_at'))
                                                <i data-feather="{{ $sortIcon('received_at') }}" class="h-3 w-3"></i>
                                            @endif
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($dispatches as $dispatch)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 font-mono text-xs text-gray-700">{{ $dispatch->reference }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-gray-700">
                                            {{ $dispatch->dispatch_date?->format('d M Y') ?? '-' }}
                                            @if ($dispatch->dispatch_time)
                                                <span class="block text-xs text-gray-400">{{ substr($dispatch->dispatch_time, 0, 5) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-gray-700">{{ $dispatch->study ?: '-' }}</td>
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
                                        <td class="px-4 py-4">
                                            <x-status-badge :value="$dispatch->status === 'received' ? 'Received' : 'Dispatched'" />
                                        </td>
                                        <td class="px-4 py-4 text-gray-600">
                                            {{ $dispatch->received_at?->format('d M Y H:i') ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-gray-500">
                            Showing {{ $dispatches->firstItem() ?? 0 }}-{{ $dispatches->lastItem() ?? 0 }} of {{ $dispatches->total() }} record{{ $dispatches->total() === 1 ? '' : 's' }}
                        </p>
                        <div>
                            {{ $dispatches->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

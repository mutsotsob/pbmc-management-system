@extends('layouts.app')

@section('title', 'Sample Dispatches')
@section('page-title', 'Sample Dispatches')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">
@endpush

@section('content')
    <div class="space-y-4">

        {{-- ── Header ──────────────────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Sample Dispatches</h2>
                <p class="text-sm text-gray-500">Track samples from clinic to lab.</p>
            </div>

            <div class="flex items-center gap-2">
                @if (auth()->user()->isAdmin() || auth()->user()->department === 'Clinical Operations')
                    <a href="{{ route('drivers.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <i data-feather="truck" class="w-4 h-4"></i>
                        Manage Drivers
                    </a>
                @endif
            </div>
        </div>

        @php
            $hasDispatchFilters = filled($q ?? null);
        @endphp

        <form method="GET" action="{{ route('sample-dispatches.index') }}"
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="dir" value="{{ $dir }}">

            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div class="w-full lg:max-w-xl">
                    <label for="dispatchSearch" class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                    <div class="relative">
                        <input id="dispatchSearch" type="search" name="q" value="{{ $q ?? '' }}"
                            placeholder="Reference, sample, driver, dispatcher, receiver..."
                            class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm text-gray-800 placeholder-gray-400">
                        <i data-feather="search" class="absolute left-3 top-2.5 h-4 w-4 text-gray-400"></i>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if ($hasDispatchFilters)
                        <a href="{{ route('sample-dispatches.index', ['sort' => $sort, 'dir' => $dir]) }}"
                            class="inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <i data-feather="x" class="w-4 h-4"></i>
                            Clear
                        </a>
                    @endif
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-pbmc px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                        <i data-feather="filter" class="w-4 h-4"></i>
                        Apply
                    </button>
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-500">
                {{ $dispatches->total() }} dispatch{{ $dispatches->total() === 1 ? '' : 'es' }} found
            </p>
        </form>

        <div class="space-y-4" x-data="dispatchPage()">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="flex flex-wrap items-center gap-2 px-4 py-3">
                    <button type="button" @click="activeTab = 'dispatch'"
                        :class="['rounded-t-lg px-4 py-2 text-sm font-semibold transition', activeTab === 'dispatch' ?
                            'border-b-2 border-pbmc text-slate-900' : 'text-slate-500 hover:text-slate-900'
                        ]">
                        <span class="inline-flex items-center gap-2">
                            <i data-feather="send" class="w-4 h-4"></i>
                            <span>Dispatch</span>
                        </span>
                    </button>
                    <button type="button" @click="activeTab = 'bulk'"
                        :class="['rounded-t-lg px-4 py-2 text-sm font-semibold transition', activeTab === 'bulk' ?
                            'border-b-2 border-pbmc text-slate-900' : 'text-slate-500 hover:text-slate-900'
                        ]">
                        <span class="inline-flex items-center gap-2">
                            <i data-feather="layers" class="w-4 h-4"></i>
                            <span>Bulk Dispatch</span>
                        </span>
                    </button>
                    <button type="button" @click="activeTab = 'table'"
                        :class="['rounded-t-lg px-4 py-2 text-sm font-semibold transition', activeTab === 'table' ?
                            'border-b-2 border-pbmc text-slate-900' : 'text-slate-500 hover:text-slate-900'
                        ]">
                        <span class="inline-flex items-center gap-2">
                            <i data-feather="list" class="w-4 h-4"></i>
                            <span>Dispatch History Table</span>
                        </span>
                    </button>
                </div>
            </div>

            <div x-show="activeTab === 'dispatch'" x-cloak class="space-y-4">
                @if (auth()->user()->isAdmin() || auth()->user()->department === 'Clinical Operations')
                    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-6">

                        <h3 class="text-sm font-semibold text-gray-700 mb-5 pb-2 border-b flex items-center gap-2">
                            <i data-feather="send" class="w-4 h-4 text-green-600"></i>
                            New Sample Dispatch
                        </h3>

                        <form method="POST" action="{{ route('sample-dispatches.store') }}" x-ref="dispatchForm">
                            @csrf

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                                {{-- Participant ID --}}
                                <div class="sm:col-span-2 lg:col-span-3">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Participant ID <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="participant_ids[0]" x-model="form.participant_id"
                                        placeholder="Participant ID"
                                        class="w-full rounded-lg border px-3 py-2 text-sm bg-white @error('participant_ids.*') border-red-400 @enderror">
                                    <p class="text-xs text-gray-500 mt-1">Enter the participant ID for this dispatch.</p>
                                    @error('participant_ids')
                                        <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
                                    @enderror
                                    @error('participant_ids.*')
                                        <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
                                    @enderror
                                    <input type="hidden" name="quantity" value="1">
                                </div>

                                {{-- Study --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Study <span
                                            class="text-red-500">*</span></label>
                                    <select name="study" x-model="form.study"
                                        class="w-full rounded-lg border px-3 py-2 text-sm bg-white @error('study') border-red-400 @enderror">
                                        @foreach (config('dispatch.studies') as $study)
                                            <option value="{{ $study }}"
                                                {{ old('study', 'C114') === $study ? 'selected' : '' }}>
                                                {{ $study }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('study')
                                        <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Visit --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Visit</label>
                                    <select name="visit" x-model="form.visit"
                                        class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                                        @foreach (range(1, 25) as $num)
                                            <option value="v{{ $num }}"
                                                {{ old('visit') === "v{$num}" ? 'selected' : '' }}>
                                                v{{ $num }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- No. of bags --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">No. of bags</label>
                                    <input type="number" name="no_of_bags" x-model="form.no_of_bags" min="1"
                                        max="9999" step="1" inputmode="numeric" placeholder="No. of bags"
                                        @keydown="if (['e', 'E', '+', '-', '.'].includes($event.key)) $event.preventDefault()"
                                        @input="form.no_of_bags = String($event.target.value).replace(/[^0-9]/g, '')"
                                        class="w-full rounded-lg border px-3 py-2 text-sm bg-white @error('no_of_bags') border-red-400 @enderror">
                                    @error('no_of_bags')
                                        <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Dispatch Date --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Dispatch Date <span
                                            class="text-red-500">*</span></label>
                                    <input type="date" name="dispatch_date" x-model="form.dispatch_date"
                                        class="w-full rounded-lg border px-3 py-2 text-sm bg-white @error('dispatch_date') border-red-400 @enderror">
                                    @error('dispatch_date')
                                        <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Dispatch Time --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Dispatch Time</label>
                                    <input type="time" name="dispatch_time" x-model="form.dispatch_time"
                                        class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                                </div>

                                {{-- From --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">From (Origin) <span
                                            class="text-red-500">*</span></label>
                                    <select name="origin_location" x-model="form.origin_location"
                                        class="w-full rounded-lg border px-3 py-2 text-sm bg-white @error('origin_location') border-red-400 @enderror">
                                        <option value="">Select origin</option>
                                        @foreach ($origins as $origin)
                                            <option value="{{ $origin }}"
                                                {{ old('origin_location') === $origin ? 'selected' : '' }}>
                                                {{ $origin }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('origin_location')
                                        <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- To --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">To (Destination) <span
                                            class="text-red-500">*</span></label>
                                    <select name="destination" x-model="form.destination"
                                        class="w-full rounded-lg border px-3 py-2 text-sm bg-white @error('destination') border-red-400 @enderror">
                                        <option value="">Select destination</option>
                                        @foreach ($destinations as $dest)
                                            <option value="{{ $dest }}"
                                                {{ old('destination') === $dest ? 'selected' : '' }}>{{ $dest }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('destination')
                                        <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Registered Driver --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Registered Driver</label>
                                    <select name="driver_user_id" x-model="form.driver_user_id" @change="selectDriver($event)"
                                        class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                                        <option value="">— Or select a driver —</option>
                                        @foreach ($drivers as $driver)
                                            <option value="{{ $driver->id }}" data-name="{{ $driver->name }}"
                                                data-phone="{{ $driver->phone_number ?? '' }}"
                                                {{ old('driver_user_id') == $driver->id ? 'selected' : '' }}>
                                                {{ $driver->name }}{{ $driver->phone_number ? ' · ' . $driver->phone_number : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="driver_name" x-model="form.driver_name">
                                </div>

                                {{-- Driver Phone --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Driver Phone</label>
                                    <input type="text" name="driver_phone" x-model="form.driver_phone"
                                        placeholder="07XXXXXXXX"
                                        class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                                </div>

                                {{-- Description --}}
                                <div class="sm:col-span-2 lg:col-span-3">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                                    <textarea name="description" x-model="form.description" rows="2" placeholder="Any additional information…"
                                        class="w-full rounded-lg border px-3 py-2 text-sm bg-white resize-none"></textarea>
                                </div>

                            </div>

                            <div class="mt-4 flex justify-end gap-3">
                                <button type="button" @click="resetForm()"
                                    class="px-4 py-2 rounded-lg border text-sm text-gray-600 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="button" @click="addToBulkRow()"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">
                                    <i data-feather="send" class="w-4 h-4"></i>
                                    Add to Bulk Dispatch
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        <p class="text-sm text-gray-600">You do not have permission to create a dispatch.</p>
                    </div>
                @endif
            </div>

            <div x-show="activeTab === 'bulk'" x-cloak class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700">Bulk Dispatch</h3>
                            <p class="text-xs text-gray-500">Capture individual samples on the Dispatch tab, then review them here before bulk dispatching.</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                            <span x-text="bulkRows.length"></span> queued item(s)
                        </span>
                    </div>

                    <div class="p-6 space-y-4">
                        <p class="text-sm text-gray-600">The Dispatch tab remains the primary entry form. Add sample details there and then switch to this Bulk Dispatch tab to review pending bulk sample entries.</p>

                        <template x-if="bulkRows.length > 0">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                        <tr>
                                            <th class="px-4 py-3 text-left">Participant ID</th>
                                            <th class="px-4 py-3 text-left">Study</th>
                                            <th class="px-4 py-3 text-left">Visit</th>
                                            <th class="px-4 py-3 text-left">Bags</th>
                                            <th class="px-4 py-3 text-left">Dispatch Date</th>
                                            <th class="px-4 py-3 text-left">Dispatch Time</th>
                                            <th class="px-4 py-3 text-left">Route</th>
                                            <th class="px-4 py-3 text-left">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        <template x-for="(row, index) in bulkRows" :key="index">
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-4 text-gray-700" x-text="row.participant_id"></td>
                                                <td class="px-4 py-4 text-gray-700" x-text="row.study"></td>
                                                <td class="px-4 py-4 text-gray-700" x-text="row.visit"></td>
                                                <td class="px-4 py-4 text-gray-700" x-text="row.no_of_bags || '-'"></td>
                                                <td class="px-4 py-4 text-gray-700" x-text="row.dispatch_date"></td>
                                                <td class="px-4 py-4 text-gray-700" x-text="row.dispatch_time"></td>
                                                <td class="px-4 py-4 text-gray-700"><span x-text="row.origin_location"></span> → <span x-text="row.destination"></span></td>
                                                <td class="px-4 py-4">
                                                    <button type="button" @click="removeBulkRow(index)"
                                                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                                        Remove
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                    <button type="button" @click="bulkRows = []"
                                        class="px-4 py-2 rounded-lg border text-sm text-gray-600 hover:bg-gray-50">
                                        Clear Bulk Queue
                                    </button>
                                    <button type="button" @click="submitBulkDispatch()"
                                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-pbmc text-white text-sm font-semibold hover:opacity-90">
                                        <i data-feather="send" class="w-4 h-4"></i>
                                        Dispatch Bulk Samples
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="bulkRows.length === 0">
                            <div class="p-10 text-center text-sm text-gray-500 border border-dashed border-gray-200 rounded-xl">
                                No bulk entries available yet. Use the Dispatch tab to add individual samples to the queue.
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <form x-ref="bulkForm" method="POST" action="{{ route('sample-dispatches.bulk') }}" class="hidden">
                @csrf
                <template x-for="(row, index) in bulkRows" :key="index">
                    <div class="hidden">
                        <input type="hidden" :name="`bulk_rows[${index}][participant_id]`" :value="row.participant_id">
                        <input type="hidden" :name="`bulk_rows[${index}][study]`" :value="row.study">
                        <input type="hidden" :name="`bulk_rows[${index}][visit]`" :value="row.visit">
                        <input type="hidden" :name="`bulk_rows[${index}][no_of_bags]`" :value="row.no_of_bags">
                        <input type="hidden" :name="`bulk_rows[${index}][dispatch_date]`" :value="row.dispatch_date">
                        <input type="hidden" :name="`bulk_rows[${index}][dispatch_time]`" :value="row.dispatch_time">
                        <input type="hidden" :name="`bulk_rows[${index}][origin_location]`" :value="row.origin_location">
                        <input type="hidden" :name="`bulk_rows[${index}][destination]`" :value="row.destination">
                        <input type="hidden" :name="`bulk_rows[${index}][driver_user_id]`" :value="row.driver_user_id">
                        <input type="hidden" :name="`bulk_rows[${index}][driver_name]`" :value="row.driver_name">
                        <input type="hidden" :name="`bulk_rows[${index}][driver_phone]`" :value="row.driver_phone">
                        <input type="hidden" :name="`bulk_rows[${index}][description]`" :value="row.description">
                    </div>
                </template>
            </form>

            <div x-show="activeTab === 'table'" x-cloak class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700">Dispatch Orders</h3>
                            <p class="text-xs text-gray-500">Recent dispatches captured in the system.</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                            {{ $dispatches->total() }} total
                        </span>
                    </div>

                    @if ($dispatches->isEmpty())
                        <div class="p-10 text-center text-sm text-gray-500">
                            No dispatch orders available yet. Submit a new dispatch to see it listed here.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table id="dispatch-history-table" class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    <tr>
                                        <th class="px-4 py-3 text-left">
                                            <label class="inline-flex items-center gap-2">
                                                <input id="select-all-rows" type="checkbox" class="form-checkbox h-4 w-4 text-pbmc rounded border-gray-300">
                                            </label>
                                        </th>
                                        <th class="px-4 py-3 text-left">Reference</th>
                                        <th class="px-4 py-3 text-left">Date</th>
                                        <th class="px-4 py-3 text-left">Study</th>
                                        <th class="px-4 py-3 text-left">Visit</th>
                                        <th class="px-4 py-3 text-left">Participant IDs</th>
                                        <th class="px-4 py-3 text-left">Route</th>
                                        <th class="px-4 py-3 text-left">Driver</th>
                                        <th class="px-4 py-3 text-left">Status</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($dispatches as $dispatch)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-4">
                                                <input type="checkbox" class="select-row form-checkbox h-4 w-4 text-pbmc rounded border-gray-300">
                                            </td>
                                            <td class="px-4 py-4 font-mono text-xs text-gray-700">{{ $dispatch->reference }}</td>
                                            <td class="px-4 py-4 text-gray-700 whitespace-nowrap">{{ $dispatch->dispatch_date->format('d M Y') }}</td>
                                            <td class="px-4 py-4 text-gray-700">{{ $dispatch->study }}</td>
                                            <td class="px-4 py-4 text-gray-700">{{ $dispatch->visit ?: '-' }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-800 space-y-1">
                                                @forelse($dispatch->items as $item)
                                                    <div>{{ $item->participant_id }}</div>
                                                @empty
                                                    <div>{{ $dispatch->sample_id }}</div>
                                                @endforelse
                                            </td>
                                            <td class="px-4 py-4 text-gray-700">
                                                <span class="font-medium">{{ $dispatch->origin_location }}</span>
                                                <span class="text-gray-400 mx-1">→</span>
                                                {{ $dispatch->destination }}
                                            </td>
                                            <td class="px-4 py-4 text-gray-700">{{ $dispatch->driver_name }}</td>
                                            <td class="px-4 py-4">
                                                <x-status-badge :value="$dispatch->status === 'dispatched' ? 'Dispatched' : 'Received'" />
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <a href="{{ route('sample-dispatches.show', $dispatch) }}"
                                                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                                    <i data-feather="eye" class="w-3 h-3"></i>
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('dispatchPage', () => ({
                    activeTab: 'dispatch',
                    bulkRows: [],
                    form: {
                        participant_id: @js(old('participant_ids.0', '')),
                        study: @js(old('study', 'C114')),
                        visit: @js(old('visit', 'v1')),
                        no_of_bags: @js(old('no_of_bags', '')),
                        dispatch_date: @js(old('dispatch_date', date('Y-m-d'))),
                        dispatch_time: @js(old('dispatch_time', '')),
                        origin_location: @js(old('origin_location', '')),
                        destination: @js(old('destination', '')),
                        driver_user_id: @js(old('driver_user_id', '')),
                        driver_name: @js(old('driver_name', '')),
                        driver_phone: @js(old('driver_phone', '')),
                        description: @js(old('description', '')),
                    },

                    init() {
                        this.$nextTick(() => window.feather?.replace());
                    },

                    selectDriver(event) {
                        const option = event.target.selectedOptions[0];

                        if (option && option.value) {
                            this.form.driver_name = option.dataset.name || '';
                            this.form.driver_phone = option.dataset.phone || '';
                            return;
                        }

                        this.form.driver_name = '';
                        this.form.driver_phone = '';
                    },

                    addToBulkRow() {
                        const requiredFields = [
                            'participant_id',
                            'study',
                            'dispatch_date',
                            'origin_location',
                            'destination',
                        ];

                        const hasMissingRequiredField = requiredFields.some((field) => {
                            return String(this.form[field] || '').trim() === '';
                        });

                        if (hasMissingRequiredField) {
                            alert('Please complete participant, study, date, origin, and destination before adding to bulk dispatch.');
                            return;
                        }

                        this.bulkRows.push({
                            ...this.form,
                            participant_id: String(this.form.participant_id).trim(),
                        });

                        this.form.participant_id = '';
                        this.form.description = '';
                        this.activeTab = 'bulk';
                    },

                    removeBulkRow(index) {
                        this.bulkRows.splice(index, 1);
                    },

                    resetForm() {
                        this.form.participant_id = '';
                        this.form.study = 'C114';
                        this.form.visit = 'v1';
                        this.form.no_of_bags = '';
                        this.form.dispatch_date = @js(date('Y-m-d'));
                        this.form.dispatch_time = '';
                        this.form.origin_location = '';
                        this.form.destination = '';
                        this.form.driver_user_id = '';
                        this.form.driver_name = '';
                        this.form.driver_phone = '';
                        this.form.description = '';
                    },

                    submitBulkDispatch() {
                        if (this.bulkRows.length === 0) {
                            return;
                        }

                        this.$nextTick(() => this.$refs.bulkForm.submit());
                    },
                }));
            });
        </script>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
        <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
        <script>
            $(document).ready(function() {
                var table = $('#dispatch-history-table').DataTable({
                    dom: '<"flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4"<"flex items-center gap-2"B><"flex items-center gap-2"f>>t<"flex items-center justify-between mt-4"ip>',
                    buttons: [
                        { extend: 'copy', text: '<span class="inline-flex items-center gap-2"><i data-feather="copy" class="w-4 h-4"></i>Copy</span>' },
                        { extend: 'csv', text: '<span class="inline-flex items-center gap-2"><i data-feather="file-text" class="w-4 h-4"></i>CSV</span>' },
                        { extend: 'excel', text: '<span class="inline-flex items-center gap-2"><i data-feather="file" class="w-4 h-4"></i>Excel</span>' },
                        { extend: 'print', text: '<span class="inline-flex items-center gap-2"><i data-feather="printer" class="w-4 h-4"></i>Print</span>' }
                    ],
                    select: {
                        style: 'multi',
                        selector: 'td:first-child input[type="checkbox"]'
                    },
                    order: [[2, 'desc']],
                    columnDefs: [
                        { orderable: false, targets: 0 },
                        { orderable: false, targets: -1 }
                    ],
                    language: {
                        search: 'Search dispatches:',
                        lengthMenu: 'Show _MENU_ rows'
                    }
                });

                if (window.feather) {
                    feather.replace();
                }

                $('#dispatch-history-table tbody').on('click', 'input.select-row', function(e) {
                    var $row = $(this).closest('tr');
                    if (this.checked) {
                        table.row($row).select();
                    } else {
                        table.row($row).deselect();
                    }
                });

                $('#select-all-rows').on('click', function() {
                    var checked = this.checked;
                    $('#dispatch-history-table tbody input.select-row').prop('checked', checked);
                    if (checked) {
                        table.rows({ page: 'current' }).select();
                    } else {
                        table.rows().deselect();
                    }
                });

                table.on('select deselect', function() {
                    var selectedCount = table.rows({ selected: true }).count();
                    $('#select-all-rows').prop('checked', selectedCount === table.rows({ page: 'current' }).count());
                });
            });
        </script>
    @endpush
    @endsection

@extends('layouts.app')

@section('title', 'Sample Dispatches')
@section('page-title', 'Sample Dispatches')

@section('content')
<div class="space-y-4" x-data="dispatchPage()">

    @if (session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Sample Dispatches</h2>
            <p class="text-sm text-gray-500">Track samples from clinic to lab.</p>
        </div>

        <div class="flex items-center gap-2">
            @if(auth()->user()->isAdmin() || auth()->user()->department === 'Clinical Operations')
                <a href="{{ route('drivers.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    <i data-feather="truck" class="w-4 h-4"></i>
                    Manage Drivers
                </a>
            @endif
            @if(auth()->user()->isAdmin() || auth()->user()->department === 'Clinical Operations')
                <button type="button" @click="showForm = !showForm"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">
                    <i data-feather="send" class="w-4 h-4"></i>
                    <span x-text="showForm ? 'Cancel' : 'Dispatch Sample'"></span>
                </button>
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
                @if($hasDispatchFilters)
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

    {{-- ── Dispatch Form ────────────────────────────────────────────────────── --}}
    @if(auth()->user()->isAdmin() || auth()->user()->department === 'Clinical Operations')
    <div x-show="showForm" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="bg-white rounded-xl border border-green-200 shadow-sm p-6">

        <h3 class="text-sm font-semibold text-gray-700 mb-5 pb-2 border-b flex items-center gap-2">
            <i data-feather="send" class="w-4 h-4 text-green-600"></i>
            New Sample Dispatch
        </h3>

        @if ($errors->any())
            <x-alert type="error" :dismissible="false" class="mb-4">
                <strong>Please fix the errors below:</strong>
                <ul class="list-disc ml-4 mt-1 space-y-0.5">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </x-alert>
        @endif

        <form method="POST" action="{{ route('sample-dispatches.store') }}" x-data="driverSelect()">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- Sample ID --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Sample ID <span class="text-red-500">*</span></label>
                    <input type="text" name="sample_id" value="{{ old('sample_id') }}"
                           placeholder="e.g. ACR-2026-0042"
                           class="w-full rounded-lg border px-3 py-2 text-sm bg-white @error('sample_id') border-red-400 @enderror">
                    @error('sample_id') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                </div>

                {{-- Study --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Study <span class="text-red-500">*</span></label>
                    <select name="study"
                            class="w-full rounded-lg border px-3 py-2 text-sm bg-white @error('study') border-red-400 @enderror">
                        @foreach(config('dispatch.studies') as $study)
                            <option value="{{ $study }}" {{ old('study', 'C114') === $study ? 'selected' : '' }}>{{ $study }}</option>
                        @endforeach
                    </select>
                    @error('study') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                </div>

                {{-- Quantity --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Quantity</label>
                    <input type="number" name="quantity" value="{{ old('quantity', 1) }}"
                           min="1" max="9999"
                           class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                </div>

                {{-- Dispatch Date --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Dispatch Date <span class="text-red-500">*</span></label>
                    <input type="date" name="dispatch_date" value="{{ old('dispatch_date', date('Y-m-d')) }}"
                           class="w-full rounded-lg border px-3 py-2 text-sm bg-white @error('dispatch_date') border-red-400 @enderror">
                    @error('dispatch_date') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                </div>

                {{-- Dispatch Time --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Dispatch Time</label>
                    <input type="time" name="dispatch_time" value="{{ old('dispatch_time') }}"
                           class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                </div>

                {{-- From --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">From (Origin) <span class="text-red-500">*</span></label>
                    <select name="origin_location"
                            class="w-full rounded-lg border px-3 py-2 text-sm bg-white @error('origin_location') border-red-400 @enderror">
                        <option value="">Select origin</option>
                        @foreach($origins as $origin)
                            <option value="{{ $origin }}" {{ old('origin_location') === $origin ? 'selected' : '' }}>{{ $origin }}</option>
                        @endforeach
                    </select>
                    @error('origin_location') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                </div>

                {{-- To --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">To (Destination) <span class="text-red-500">*</span></label>
                    <select name="destination"
                            class="w-full rounded-lg border px-3 py-2 text-sm bg-white @error('destination') border-red-400 @enderror">
                        <option value="">Select destination</option>
                        @foreach($destinations as $dest)
                            <option value="{{ $dest }}" {{ old('destination') === $dest ? 'selected' : '' }}>{{ $dest }}</option>
                        @endforeach
                    </select>
                    @error('destination') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                </div>

                {{-- Registered Driver --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Registered Driver</label>
                    <select name="driver_user_id" @change="selectDriver($event)"
                            class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                        <option value="">— Or enter manually below —</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}"
                                    data-name="{{ $driver->name }}"
                                    data-phone="{{ $driver->phone_number ?? '' }}"
                                    {{ old('driver_user_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }}{{ $driver->phone_number ? ' · '.$driver->phone_number : '' }}
                                {{ $driver->vehicle_registration ? ' · '.$driver->vehicle_registration : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Driver Name --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Driver Name <span class="text-red-500">*</span></label>
                    <input type="text" name="driver_name" x-model="driverName"
                           value="{{ old('driver_name') }}" placeholder="Full name"
                           class="w-full rounded-lg border px-3 py-2 text-sm bg-white @error('driver_name') border-red-400 @enderror">
                    @error('driver_name') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                </div>

                {{-- Driver Phone --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Driver Phone</label>
                    <input type="text" name="driver_phone" x-model="driverPhone"
                           value="{{ old('driver_phone') }}" placeholder="07XXXXXXXX"
                           class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                </div>

                {{-- Notes --}}
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Any additional information…"
                              class="w-full rounded-lg border px-3 py-2 text-sm bg-white resize-none">{{ old('notes') }}</textarea>
                </div>

            </div>

            <div class="mt-4 flex justify-end gap-3">
                <button type="button" @click="showForm = false"
                        class="px-4 py-2 rounded-lg border text-sm text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">
                    <i data-feather="send" class="w-4 h-4"></i>
                    Dispatch Sample
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- ── Dispatch Table ───────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($dispatches->isEmpty())
            <x-empty-state icon="send"
                           :title="$hasDispatchFilters ? 'No dispatches match these filters' : 'No dispatches yet'"
                           :description="$hasDispatchFilters ? 'Clear filters or adjust your search.' : 'Use the Dispatch Sample button above to record a new dispatch.'"
                           :action-url="$hasDispatchFilters ? route('sample-dispatches.index') : null"
                           action-label="Clear Filters" />
        @else
            <div x-show="selectedDispatches.length > 0" x-transition
                 class="border-b border-indigo-100 bg-indigo-50 px-4 py-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <span class="text-sm font-medium text-indigo-900">
                        <span x-text="selectedDispatches.length"></span> dispatch<span x-show="selectedDispatches.length !== 1">es</span> selected
                    </span>
                    <button type="button" @click="clearDispatchSelection()"
                            class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-white px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-50">
                        <i data-feather="x" class="w-4 h-4"></i>
                        Clear selection
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left w-10">
                                <input type="checkbox"
                                       class="rounded border-gray-300 text-pbmc focus:ring-pbmc"
                                       :checked="allVisibleDispatchesSelected()"
                                       :indeterminate="someVisibleDispatchesSelected() && !allVisibleDispatchesSelected()"
                                       @change="toggleVisibleDispatches($event)">
                            </th>
                            <th class="px-4 py-3 text-left">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'reference', 'dir' => $sort === 'reference' && $dir === 'asc' ? 'desc' : 'asc']) }}"
                                   class="inline-flex items-center gap-1 hover:text-gray-800">
                                    Reference
                                    @if($sort === 'reference') <i data-feather="{{ $dir === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3"></i> @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'dispatch_date', 'dir' => $sort === 'dispatch_date' && $dir === 'asc' ? 'desc' : 'asc']) }}"
                                   class="inline-flex items-center gap-1 hover:text-gray-800">
                                    Date
                                    @if($sort === 'dispatch_date') <i data-feather="{{ $dir === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3"></i> @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'sample_id', 'dir' => $sort === 'sample_id' && $dir === 'asc' ? 'desc' : 'asc']) }}"
                                   class="inline-flex items-center gap-1 hover:text-gray-800">
                                    Sample ID
                                    @if($sort === 'sample_id') <i data-feather="{{ $dir === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3"></i> @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left">Route</th>
                            <th class="px-4 py-3 text-left">Driver</th>
                            <th class="px-4 py-3 text-left">Dispatched By</th>
                            <th class="px-4 py-3 text-left">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'dir' => $sort === 'status' && $dir === 'asc' ? 'desc' : 'asc']) }}"
                                   class="inline-flex items-center gap-1 hover:text-gray-800">
                                    Status
                                    @if($sort === 'status') <i data-feather="{{ $dir === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3"></i> @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left">Received By</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($dispatches as $dispatch)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <input type="checkbox"
                                           value="{{ $dispatch->id }}"
                                           data-dispatch-checkbox
                                           x-model="selectedDispatches"
                                           class="rounded border-gray-300 text-pbmc focus:ring-pbmc">
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $dispatch->reference }}</td>
                                <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                                    {{ $dispatch->dispatch_date->format('d M Y') }}
                                    @if($dispatch->dispatch_time)
                                        <span class="block text-gray-400 text-xs">{{ substr($dispatch->dispatch_time, 0, 5) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-800">{{ $dispatch->sample_id }}</span>
                                    @if($dispatch->quantity > 1)
                                        <span class="ml-1 text-xs text-gray-400">×{{ $dispatch->quantity }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700">
                                    <span class="font-medium">{{ $dispatch->origin_location }}</span>
                                    <span class="text-gray-400 mx-1">→</span>
                                    {{ $dispatch->destination }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $dispatch->driver_name }}</td>
                                <td class="px-4 py-3 text-gray-600 text-xs">{{ $dispatch->dispatchedBy?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :value="$dispatch->status === 'dispatched' ? 'Dispatched' : 'Received'" />
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">
                                    @if($dispatch->isReceived())
                                        <span class="block font-medium text-gray-800">{{ $dispatch->receivedBy?->name ?? 'Unknown' }}</span>
                                        <span class="block text-gray-400">{{ $dispatch->received_at?->format('d M Y, H:i') }}</span>
                                    @else
                                        <span class="text-gray-400">Pending receipt</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($canReceiveSamples && !$dispatch->isReceived())
                                            <form method="POST" action="{{ route('sample-dispatches.receive', $dispatch) }}"
                                                  onsubmit="return confirm('Mark sample {{ $dispatch->sample_id }} as received in good condition?');">
                                                @csrf
                                                <input type="hidden" name="condition_on_arrival" value="Good">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-green-600 text-xs font-semibold text-white hover:bg-green-700 transition">
                                                    <i data-feather="check" class="w-3 h-3"></i>
                                                    Receive
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('sample-dispatches.show', $dispatch) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
                                            <i data-feather="eye" class="w-3 h-3"></i> View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($dispatches->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $dispatches->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>

</div>

<script>
function dispatchPage() {
    return {
        showForm: {{ $errors->any() ? 'true' : 'false' }},
        selectedDispatches: [],
        visibleDispatchIds() {
            return Array.from(document.querySelectorAll('[data-dispatch-checkbox]')).map(cb => cb.value);
        },
        toggleVisibleDispatches(event) {
            const visibleIds = this.visibleDispatchIds();

            if (event.target.checked) {
                this.selectedDispatches = Array.from(new Set([...this.selectedDispatches, ...visibleIds]));
                return;
            }

            this.selectedDispatches = this.selectedDispatches.filter(id => !visibleIds.includes(id));
        },
        allVisibleDispatchesSelected() {
            const visibleIds = this.visibleDispatchIds();

            return visibleIds.length > 0 && visibleIds.every(id => this.selectedDispatches.includes(id));
        },
        someVisibleDispatchesSelected() {
            const visibleIds = this.visibleDispatchIds();

            return visibleIds.some(id => this.selectedDispatches.includes(id));
        },
        clearDispatchSelection() {
            this.selectedDispatches = [];
        },
    };
}

function driverSelect() {
    return {
        driverName:  '{{ old('driver_name') }}',
        driverPhone: '{{ old('driver_phone') }}',
        selectDriver(event) {
            const opt = event.target.selectedOptions[0];
            if (opt && opt.value) {
                this.driverName  = opt.dataset.name  || '';
                this.driverPhone = opt.dataset.phone || '';
            } else {
                this.driverName  = '';
                this.driverPhone = '';
            }
        },
    };
}
</script>
@endsection

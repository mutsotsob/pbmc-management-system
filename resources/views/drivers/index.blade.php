@extends('layouts.app')

@section('title', 'Manage Drivers')
@section('topnav-title', 'Manage Drivers')

@section('content')
<div class="space-y-4" x-data="driversPage()">

    @if (session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Drivers</h2>
            <p class="text-sm text-gray-500">Manage drivers available for sample dispatch.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('sample-dispatches.index') }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-sm text-gray-600 hover:bg-gray-50 transition">
                <i data-feather="arrow-left" class="w-4 h-4"></i> Back to Dispatches
            </a>
            <button type="button" @click="showAdd = !showAdd"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">
                <i data-feather="user-plus" class="w-4 h-4"></i>
                <span x-text="showAdd ? 'Cancel' : 'Add Driver'"></span>
            </button>
        </div>
    </div>

    {{-- ── Search ──────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('drivers.index') }}" class="flex items-center gap-2">
        <div class="relative flex-1 max-w-sm">
            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                <i data-feather="search" class="w-4 h-4 text-gray-400"></i>
            </span>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Search by name, phone, vehicle…"
                   class="w-full pl-9 pr-3 py-2 rounded-lg border text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-300">
        </div>
        @if(request()->filled('q'))
            <a href="{{ route('drivers.index') }}"
               class="px-3 py-2 rounded-lg border text-sm text-gray-600 hover:bg-gray-50 transition">
                Clear
            </a>
        @endif
    </form>

    {{-- ── Add Driver Form ─────────────────────────────────────────────────── --}}
    <div x-show="showAdd"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="bg-white rounded-xl border border-green-200 shadow-sm p-6">

        <h3 class="text-sm font-semibold text-gray-700 mb-5 pb-2 border-b flex items-center gap-2">
            <i data-feather="user-plus" class="w-4 h-4 text-green-600"></i>
            New Driver
        </h3>

        @if ($errors->any() && old('_form') === 'add')
            <x-alert type="error" :dismissible="false" class="mb-4">
                <ul class="list-disc ml-4 space-y-0.5">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </x-alert>
        @endif

        <form method="POST" action="{{ route('drivers.store') }}">
            @csrf
            <input type="hidden" name="_form" value="add">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Tendai Moyo"
                           class="w-full rounded-lg border px-3 py-2 text-sm bg-white @error('name') border-red-400 @enderror">
                    @error('name') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="07XXXXXXXX"
                           class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Vehicle Reg.</label>
                    <input type="text" name="vehicle_registration" value="{{ old('vehicle_registration') }}" placeholder="e.g. ABC 1234"
                           class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-3">
                <button type="button" @click="showAdd = false"
                        class="px-4 py-2 rounded-lg border text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">
                    <i data-feather="save" class="w-4 h-4"></i> Save Driver
                </button>
            </div>
        </form>
    </div>

    {{-- ── Drivers Table ───────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($drivers->isEmpty())
            <x-empty-state icon="users"
                title="{{ request()->filled('q') ? 'No drivers found' : 'No drivers yet' }}"
                description="{{ request()->filled('q') ? 'No drivers match your search. Try a different name, phone, or vehicle.' : 'Add a driver above to make them available in the dispatch form.' }}" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Phone</th>
                            <th class="px-4 py-3 text-left">Vehicle Reg.</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($drivers as $driver)
                            <tr class="hover:bg-gray-50 transition" x-data="{ editing: false }">

                                {{-- ── View row ──────────────────────────────── --}}
                                <template x-if="!editing">
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $driver->name }}</td>
                                </template>
                                <template x-if="!editing">
                                    <td class="px-4 py-3 text-gray-600">{{ $driver->phone_number ?: '—' }}</td>
                                </template>
                                <template x-if="!editing">
                                    <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $driver->vehicle_registration ?: '—' }}</td>
                                </template>
                                <template x-if="!editing">
                                    <td class="px-4 py-3">
                                        @if($driver->active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-50 text-green-700">Active</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-500">Inactive</span>
                                        @endif
                                    </td>
                                </template>
                                <template x-if="!editing">
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <button type="button" @click="editing = true"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-xs font-medium text-gray-700 hover:bg-gray-50 transition">
                                                <i data-feather="edit-2" class="w-3 h-3"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('drivers.toggle-active', $driver) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-xs font-medium
                                                               {{ $driver->active ? 'text-red-600 hover:bg-red-50 border-red-200' : 'text-green-700 hover:bg-green-50 border-green-200' }} transition">
                                                    <i data-feather="{{ $driver->active ? 'user-x' : 'user-check' }}" class="w-3 h-3"></i>
                                                    {{ $driver->active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </template>

                                {{-- ── Inline edit row ───────────────────────── --}}
                                <template x-if="editing">
                                    <td colspan="5" class="px-4 py-4 bg-orange-50/40">
                                        <form method="POST" action="{{ route('drivers.update', $driver) }}"
                                              class="flex flex-col sm:flex-row items-start sm:items-end gap-3">
                                            @csrf @method('PUT')

                                            <div class="flex-1 min-w-0">
                                                <label class="block text-xs font-medium text-gray-500 mb-1">Full Name <span class="text-red-500">*</span></label>
                                                <input type="text" name="name" value="{{ $driver->name }}" required
                                                       class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                                            </div>
                                            <div class="w-full sm:w-44">
                                                <label class="block text-xs font-medium text-gray-500 mb-1">Phone</label>
                                                <input type="text" name="phone_number" value="{{ $driver->phone_number }}"
                                                       class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                                            </div>
                                            <div class="w-full sm:w-36">
                                                <label class="block text-xs font-medium text-gray-500 mb-1">Vehicle Reg.</label>
                                                <input type="text" name="vehicle_registration" value="{{ $driver->vehicle_registration }}"
                                                       class="w-full rounded-lg border px-3 py-2 text-sm bg-white">
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700 transition">
                                                    <i data-feather="save" class="w-3 h-3"></i> Save
                                                </button>
                                                <button type="button" @click="editing = false"
                                                        class="px-3 py-2 rounded-lg border text-xs text-gray-600 hover:bg-gray-50">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </template>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

<script>
function driversPage() {
    return {
        showAdd: {{ $errors->any() && old('_form') === 'add' ? 'true' : 'false' }},
    };
}
</script>
@endsection

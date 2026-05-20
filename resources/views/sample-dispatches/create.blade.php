@extends('layouts.app')

@section('title', 'Dispatch Sample')
@section('page-title', 'Dispatch Sample')

@section('content')
<div class="max-w-3xl mx-auto" x-data="dispatchForm()">

    @if ($errors->any())
        <x-alert type="error" :dismissible="false">
            <strong>Please fix the errors below:</strong>
            <ul class="list-disc ml-4 mt-1 space-y-0.5">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </x-alert>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">

        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Dispatch a Sample</h2>
                <p class="text-sm text-gray-500">Record the details of a sample being sent to the lab.</p>
            </div>
            <a href="{{ route('sample-dispatches.index') }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-sm hover:bg-gray-50">
                <i data-feather="arrow-left" class="w-4 h-4"></i> Back
            </a>
        </div>

        <form method="POST" action="{{ route('sample-dispatches.store') }}">
            @csrf

            {{-- ── Sample Details ────────────────────────────────────────────── --}}
            <fieldset class="mb-6">
                <legend class="text-sm font-semibold text-gray-700 mb-4 pb-1 border-b w-full">Sample Details</legend>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-sm font-medium mb-1">Sample ID <span class="text-red-600">*</span></label>
                        <input type="text" name="sample_id" value="{{ old('sample_id') }}"
                               placeholder="e.g. ACR-2026-0042"
                               class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white @error('sample_id') border-red-400 @enderror">
                        @error('sample_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Sample Type</label>
                        <input type="text" name="sample_type" value="{{ old('sample_type') }}"
                               placeholder="e.g. Whole Blood, Swab"
                               class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white">
                        @error('sample_type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Dispatch Date <span class="text-red-600">*</span></label>
                        <input type="date" name="dispatch_date" value="{{ old('dispatch_date', date('Y-m-d')) }}"
                               class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white @error('dispatch_date') border-red-400 @enderror">
                        @error('dispatch_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Dispatch Time</label>
                        <input type="time" name="dispatch_time" value="{{ old('dispatch_time') }}"
                               class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white">
                        @error('dispatch_time') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Quantity</label>
                        <input type="number" name="quantity" value="{{ old('quantity', 1) }}"
                               min="1" max="9999"
                               class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white">
                        @error('quantity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">From (Origin) <span class="text-red-600">*</span></label>
                        <select name="origin_location"
                                class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white @error('origin_location') border-red-400 @enderror">
                            <option value="">Select origin</option>
                            @foreach($origins as $origin)
                                <option value="{{ $origin }}" {{ old('origin_location') === $origin ? 'selected' : '' }}>
                                    {{ $origin }}
                                </option>
                            @endforeach
                        </select>
                        @error('origin_location') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">To (Destination) <span class="text-red-600">*</span></label>
                        <select name="destination"
                                class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white @error('destination') border-red-400 @enderror">
                            <option value="">Select destination</option>
                            @foreach($destinations as $dest)
                                <option value="{{ $dest }}" {{ old('destination') === $dest ? 'selected' : '' }}>
                                    {{ $dest }}
                                </option>
                            @endforeach
                        </select>
                        @error('destination') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                </div>
            </fieldset>

            {{-- ── Driver Details ────────────────────────────────────────────── --}}
            <fieldset class="mb-6">
                <legend class="text-sm font-semibold text-gray-700 mb-4 pb-1 border-b w-full">Driver Details</legend>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Select Registered Driver</label>
                    <select name="driver_user_id"
                            @change="selectDriver($event)"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white">
                        <option value="">— Choose from registered drivers or enter manually below —</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}"
                                    data-name="{{ $driver->name }}"
                                    data-phone="{{ $driver->phone_number ?? '' }}"
                                    {{ old('driver_user_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }}{{ $driver->phone_number ? ' · ' . $driver->phone_number : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-sm font-medium mb-1">Driver Name <span class="text-red-600">*</span></label>
                        <input type="text" name="driver_name" x-model="driverName"
                               value="{{ old('driver_name') }}"
                               placeholder="Full name"
                               class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white @error('driver_name') border-red-400 @enderror">
                        @error('driver_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Driver Phone</label>
                        <input type="text" name="driver_phone" x-model="driverPhone"
                               value="{{ old('driver_phone') }}"
                               placeholder="07XXXXXXXX"
                               class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white">
                        @error('driver_phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium mb-1">Vehicle Registration</label>
                        <input type="text" name="vehicle_registration" value="{{ old('vehicle_registration') }}"
                               placeholder="e.g. ABC 1234"
                               class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white">
                        @error('vehicle_registration') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                </div>
            </fieldset>

            {{-- ── Notes ────────────────────────────────────────────────────── --}}
            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Notes</label>
                <textarea name="notes" rows="3"
                          placeholder="Any additional information about this dispatch…"
                          class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white resize-none">{{ old('notes') }}</textarea>
                @error('notes') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- ── Actions ──────────────────────────────────────────────────── --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('sample-dispatches.index') }}"
                   class="inline-flex items-center px-4 py-2.5 rounded-lg border text-sm hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">
                    <i data-feather="send" class="w-4 h-4"></i>
                    Dispatch Sample
                </button>
            </div>

        </form>
    </div>
</div>

<script>
function dispatchForm() {
    return {
        driverName: '{{ old('driver_name') }}',
        driverPhone: '{{ old('driver_phone') }}',

        selectDriver(event) {
            const option = event.target.selectedOptions[0];
            if (option && option.value) {
                this.driverName  = option.dataset.name  || '';
                this.driverPhone = option.dataset.phone || '';
            } else {
                this.driverName  = '';
                this.driverPhone = '';
            }
        },
    };
}
</script>
@endsection

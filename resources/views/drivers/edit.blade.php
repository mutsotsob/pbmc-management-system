@extends('layouts.app')

@section('title', 'Edit Driver')
@section('topnav-title', 'Edit Driver')

@section('content')
    <div class="max-w-4xl mx-auto space-y-4">

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Edit Driver Details</h2>
                    <p class="text-sm text-gray-500">Update information for this Administration driver.</p>
                </div>
                <a href="{{ route('drivers.show', $driver) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm text-gray-700 hover:bg-gray-50 transition">
                    <i data-feather="eye" class="w-4 h-4"></i>
                    View Driver
                </a>
            </div>

            <form method="POST" action="{{ route('drivers.update', $driver) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-1">Full Name <span class="text-red-600">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $driver->name) }}"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white @error('name') border-red-400 @enderror"
                            placeholder="Driver full name">
                        @error('name')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Email <span class="text-red-600">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $driver->email) }}"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white @error('email') border-red-400 @enderror"
                            placeholder="driver@example.com">
                        @error('email')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Phone Number</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $driver->phone_number) }}"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm bg-white" placeholder="07XXXXXXXX">
                        @error('phone_number')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="mt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <a href="{{ route('drivers.show', $driver) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm text-gray-700 hover:bg-gray-50 transition">
                        <i data-feather="arrow-left" class="w-4 h-4"></i>
                        Back to Details
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-orange-600 text-white text-sm font-semibold hover:bg-orange-700 transition">
                        <i data-feather="save" class="w-4 h-4"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

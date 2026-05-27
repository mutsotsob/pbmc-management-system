@extends('layouts.app')

@section('title', 'Driver Details')
@section('topnav-title', 'Driver Details')

@section('content')
    <div class="max-w-4xl mx-auto space-y-4">

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">{{ $driver->name }}</h2>
                    <p class="text-sm text-gray-500">Administration driver details.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <a href="{{ route('drivers.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm text-gray-700 hover:bg-gray-50 transition">
                        <i data-feather="arrow-left" class="w-4 h-4"></i>
                        Back to Drivers
                    </a>
                    <a href="{{ route('drivers.edit', $driver) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-orange-600 text-white text-sm font-semibold hover:bg-orange-700 transition">
                        <i data-feather="edit-2" class="w-4 h-4"></i>
                        Edit Details
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Name</p>
                    <p class="mt-2 text-sm text-gray-900">{{ $driver->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Email</p>
                    <p class="mt-2 text-sm text-gray-900">{{ $driver->email }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Phone</p>
                    <p class="mt-2 text-sm text-gray-900">{{ $driver->phone_number ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</p>
                    <p class="mt-2 text-sm text-gray-900">{{ $driver->user_status ? 'Active' : 'Inactive' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Department</p>
                    <p class="mt-2 text-sm text-gray-900">{{ $driver->department ?? 'Administration' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

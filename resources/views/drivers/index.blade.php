@extends('layouts.app')

@section('title', 'Manage Drivers')
@section('topnav-title', 'Manage Drivers')

@section('content')
    <div class="space-y-4">

        {{-- ── Header ──────────────────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Drivers</h2>
                <p class="text-sm text-gray-500">Showing Administration department drivers with email addresses.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('sample-dispatches.index') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-sm text-gray-600 hover:bg-gray-50 transition">
                    <i data-feather="arrow-left" class="w-4 h-4"></i> Back to Dispatches
                </a>
            </div>
        </div>

        {{-- ── Search ──────────────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('drivers.index') }}" class="flex items-center gap-2">
            <div class="relative flex-1 max-w-sm">
                <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <i data-feather="search" class="w-4 h-4 text-gray-400"></i>
                </span>
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Search by name, email, or phone…"
                    class="w-full pl-9 pr-3 py-2 rounded-lg border text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>
            @if (request()->filled('q'))
                <a href="{{ route('drivers.index') }}"
                    class="px-3 py-2 rounded-lg border text-sm text-gray-600 hover:bg-gray-50 transition">
                    Clear
                </a>
            @endif
        </form>


        {{-- ── Drivers Table ───────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            @if ($drivers->isEmpty())
                <x-empty-state icon="users" title="{{ request()->filled('q') ? 'No drivers found' : 'No drivers yet' }}"
                    description="{{ request()->filled('q') ? 'No drivers match your search. Try a different name, email, or phone.' : 'No Administration drivers found yet.' }}" />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Phone</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($drivers as $driver)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $driver->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $driver->email }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $driver->phone_number ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $driver->user_status ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $driver->user_status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('drivers.show', $driver) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
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
@endsection

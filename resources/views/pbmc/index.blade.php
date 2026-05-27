@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">PBMC Records</h2>
            <p class="text-sm text-gray-500 mt-1">
                View, filter, and manage PBMC processing records
            </p>
        </div>

        <a href="{{ route('pbmc.create') }}"
           class="inline-flex items-center gap-2 bg-green-600 text-white px-5 py-2.5 rounded-lg
                  hover:bg-green-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New PBMC
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Study</label>
                <select name="study"
                        class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">All studies</option>
                    <option value="HIV-C114" {{ request('study') === 'HIV-C114' ? 'selected' : '' }}>
                        HIV-C114
                    </option>
                    <option value="Other" {{ request('study') === 'Other' ? 'selected' : '' }}>
                        Other
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">PTID</label>
                <input type="text" name="ptid" value="{{ request('ptid') }}"
                       placeholder="e.g. PT12345"
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">From date</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">To date</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="viable" value="1"
                           {{ request()->boolean('viable') ? 'checked' : '' }}>
                    Viable ≥ 80%
                </label>
            </div>

            <div class="md:col-span-5 flex gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600
                               text-white text-sm font-medium hover:bg-blue-700">
                    Apply Filters
                </button>

                <a href="{{ route('pbmc.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border
                          text-sm text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        @if($pbmcs->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <p class="font-medium">No PBMC records found</p>
                <p class="text-sm mt-1">Try adjusting filters or create a new record.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <th class="px-4 py-3">Study</th>
                            <th class="px-4 py-3">PTID</th>
                            <th class="px-4 py-3">Visit</th>
                            <th class="px-4 py-3">Collection Date</th>
                            <th class="px-4 py-3">Counting Method</th>
                            <th class="px-4 py-3">Viability</th>
                            <th class="px-4 py-3">Created</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach($pbmcs as $pbmc)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">
                                    {{ $pbmc->study_name }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $pbmc->ptid }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $pbmc->visit }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $pbmc->collection_date?->format('d M Y') }}
                                </td>

                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ $pbmc->counting_method === 'Manual Count'
                                            ? 'bg-blue-100 text-blue-700'
                                            : 'bg-purple-100 text-purple-700' }}">
                                        {{ $pbmc->counting_method }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    @php
                                        $v = $pbmc->viability_percent ?? $pbmc->auto_viability_percent;
                                    @endphp

                                    @if($v !== null)
                                        <span class="px-2 py-1 text-xs rounded-full
                                            {{ $v >= 80 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ number_format($v, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">N/A</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-gray-500">
                                    {{ $pbmc->created_at->format('d M Y') }}
                                </td>

                                <td class="px-4 py-3 text-right space-x-3">
                                    <a href="{{ route('pbmc.show', $pbmc) }}"
                                       class="text-blue-600 hover:underline font-medium">
                                        View
                                    </a>

                                    <a href="{{ route('pbmc.edit', $pbmc) }}"
                                       class="text-gray-600 hover:underline">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-4 py-3 border-t">
                {{ $pbmcs->withQueryString()->links() }}
            </div>
        @endif

    </div>

</div>
@endsection

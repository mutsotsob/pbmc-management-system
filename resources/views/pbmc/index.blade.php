@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800">PBMC Records</h2>

        @if($pbmcs->count() === 0)
            <a href="{{ route('pbmc.create') }}"
               class="inline-flex items-center gap-2 bg-pbmc text-white px-4 py-2 rounded-lg hover:opacity-90">
                <i data-feather="plus" class="w-4 h-4"></i>
                Create PBMC
            </a>
        @endif
    </div>

    @if($pbmcs->isEmpty())
        <div class="text-center py-10 text-gray-500">
            No PBMC records found.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr class="text-left text-sm text-gray-600">
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Created</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pbmcs as $pbmc)
                        <tr class="border-t">
                            <td class="px-4 py-3 font-medium">
                                {{ $pbmc->title }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100">
                                    {{ ucfirst($pbmc->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $pbmc->created_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('pbmc.show', $pbmc) }}"
                                   class="text-pbmc font-medium hover:underline">
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
@endsection

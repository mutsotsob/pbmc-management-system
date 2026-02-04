@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-6">

    <div class="flex items-start justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                {{ $pbmc->title }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Created on {{ $pbmc->created_at->format('d M Y') }}
            </p>
        </div>

        <span class="px-3 py-1 text-xs rounded-full bg-gray-100">
            {{ ucfirst($pbmc->status) }}
        </span>
    </div>

    <div class="prose max-w-none text-gray-700">
        @if($pbmc->description)
            <p>{{ $pbmc->description }}</p>
        @else
            <p class="italic text-gray-400">No description provided.</p>
        @endif
    </div>

    <div class="mt-8 flex justify-end gap-3">
        <a href="{{ route('pbmc.index') }}"
           class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
            Back
        </a>

        <a href="{{ route('pbmc.edit', $pbmc) }}"
           class="px-4 py-2 rounded-lg bg-pbmc text-white hover:opacity-90">
            Edit
        </a>
    </div>

</div>
@endsection

@extends('layouts.app')

@section('title', 'Feature Under Development')
@section('topnav-title', 'Feature Under Development')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                <i data-feather="tool" class="h-6 w-6"></i>
            </div>

            <h2 class="text-xl font-bold text-gray-900">Feature Under Development</h2>
            <p class="mt-2 text-sm text-gray-600">
                Processing for this study is not available yet. Please check back soon.
            </p>

            <div class="mt-6">
                <a href="{{ route('dashboard', ['sample_status' => 'received']) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-pbmc px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                    <i data-feather="arrow-left" class="h-4 w-4"></i>
                    Back to Received Samples
                </a>
            </div>
        </div>
    </div>
@endsection

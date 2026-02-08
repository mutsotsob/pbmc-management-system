@extends('layouts.app')

@section('title', 'PBMC Record')

@section('content')
@php
    $user = Auth::user();
    $isAdmin = $user && strtolower(trim($user->user_type ?? '')) === 'admin';
    $viability = $pbmc->viability_percent ?? $pbmc->auto_viability_percent;
@endphp

<div class="max-w-4xl mx-auto">

                <!-- Breadcrumb -->
                <div class="mb-4 flex items-center gap-2 text-sm text-gray-600">
                    <a href="{{ route('dashboard') }}" class="hover:text-pbmc">Dashboard</a>
                    <span>/</span>
                    <span class="text-gray-800 font-medium">PBMC #{{ $pbmc->ptid }}</span>
                </div>

                @if (session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i data-feather="check-circle" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm text-green-800 font-medium">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-xl border overflow-hidden">

                    <!-- Header Section -->
                    <div class="bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-8 border-b">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <h2 class="text-2xl font-bold text-gray-900">
                                        PTID: {{ $pbmc->ptid }}
                                    </h2>
                                    @if ($viability !== null)
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full
                                            {{ $viability >= 80 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ number_format($viability, 1) }}% Viable
                                        </span>
                                    @endif
                                </div>
                                <p class="text-gray-600">
                                    Collection Date: <strong>{{ $pbmc->collection_date?->format('d M Y') ?? 'N/A' }}</strong>
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    Source: <span class="font-medium">{{ ($pbmc->imported_from_acrn ?? false) ? 'ACRN' : 'Manual Entry' }}</span>
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('pbmc.edit', $pbmc) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-pbmc text-white rounded-lg hover:bg-orange-700 font-medium">
                                    <i data-feather="edit" class="w-4 h-4"></i>
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="p-6">
                        
                        <!-- Study Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <i data-feather="book-open" class="w-5 h-5 text-pbmc"></i>
                                Study Information
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="clipboard" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Study</p>
                                            <p class="text-base font-semibold text-gray-900">
                                                {{ $pbmc->study_choice === 'Other' ? ($pbmc->other_study_name ?? 'N/A') : $pbmc->study_choice }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Patient Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <i data-feather="user" class="w-5 h-5 text-pbmc"></i>
                                Patient Information
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- PTID -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="hash" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">PTID</p>
                                            <p class="text-base font-semibold text-gray-900">{{ $pbmc->ptid }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Visit -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="map-pin" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Visit</p>
                                            <p class="text-base font-semibold text-gray-900">{{ $pbmc->visit ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Collection Date -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="calendar" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Collection Date</p>
                                            <p class="text-base font-semibold text-gray-900">
                                                {{ $pbmc->collection_date?->format('d M Y') ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Collection Time -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="clock" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Collection Time</p>
                                            <p class="text-base font-semibold text-gray-900">{{ $pbmc->collection_time ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Processing Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <i data-feather="cpu" class="w-5 h-5 text-pbmc"></i>
                                Processing Information
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Process Start Date -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="calendar" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Process Start Date</p>
                                            <p class="text-base font-semibold text-gray-900">
                                                {{ $pbmc->process_start_date?->format('d M Y') ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Process Start Time -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="clock" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Process Start Time</p>
                                            <p class="text-base font-semibold text-gray-900">{{ $pbmc->process_start_time ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Counting Method -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="layers" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Counting Method</p>
                                            <p class="text-base font-semibold text-gray-900">{{ $pbmc->counting_method ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Usable Blood Volume -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="droplet" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Usable Blood Volume</p>
                                            <p class="text-base font-semibold text-gray-900">
                                                {{ $pbmc->usable_blood_volume !== null ? number_format($pbmc->usable_blood_volume, 2).' ml' : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Source -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="database" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Source</p>
                                            <p class="text-base font-semibold text-gray-900">
                                                {{ ($pbmc->imported_from_acrn ?? false) ? 'ACRN Import' : 'Manual Entry' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Viability & Cell Count -->
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <i data-feather="activity" class="w-5 h-5 text-pbmc"></i>
                                Viability & Cell Count
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Viability Percent -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="activity" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Viability (%)</p>
                                            <p class="text-base font-semibold text-gray-900">
                                                {{ $pbmc->viability_percent !== null ? number_format($pbmc->viability_percent, 1).'%' : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Auto Viability Percent -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="zap" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Auto Viability (%)</p>
                                            <p class="text-base font-semibold text-gray-900">
                                                {{ $pbmc->auto_viability_percent !== null ? number_format($pbmc->auto_viability_percent, 1).'%' : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cell Count Concentration -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="hexagon" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Cell Count Concentration</p>
                                            <p class="text-base font-semibold text-gray-900">
                                                {{ $pbmc->cell_count_concentration !== null ? number_format($pbmc->cell_count_concentration, 2) : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Cell Number -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="hexagon" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Total Cell Number</p>
                                            <p class="text-base font-semibold text-gray-900">
                                                {{ $pbmc->total_cell_number !== null ? number_format($pbmc->total_cell_number) : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Automated Processing -->
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <i data-feather="settings" class="w-5 h-5 text-pbmc"></i>
                                Automated Processing
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Auto Total Viable Cells -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="check-circle" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Auto Total Viable Cells</p>
                                            <p class="text-base font-semibold text-gray-900">
                                                {{ $pbmc->auto_total_viable_cells_original !== null ? number_format($pbmc->auto_total_viable_cells_original) : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Auto Total Cells -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="disc" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Auto Total Cells</p>
                                            <p class="text-base font-semibold text-gray-900">
                                                {{ $pbmc->auto_total_cells_original !== null ? number_format($pbmc->auto_total_cells_original) : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Auto Total Cryovials Frozen -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="archive" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Auto Total Cryovials Frozen</p>
                                            <p class="text-base font-semibold text-gray-900">
                                                {{ $pbmc->auto_total_cryovials_frozen !== null ? number_format($pbmc->auto_total_cryovials_frozen) : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reagents Section -->
                        @if($pbmc->reagents && $pbmc->reagents->count() > 0)
                            <div class="mb-8">
                                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <i data-feather="beaker" class="w-5 h-5 text-pbmc"></i>
                                    Reagents
                                </h3>
                                
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="space-y-3">
                                        @foreach($pbmc->reagents as $reagent)
                                            <div class="flex items-center justify-between border-b border-gray-200 pb-2 last:border-0">
                                                <div>
                                                    <p class="font-semibold text-gray-900">{{ $reagent->name }}</p>
                                                    <p class="text-sm text-gray-600">
                                                        Lot: {{ $reagent->lot ?? 'N/A' }} | 
                                                        Expiry: {{ $reagent->expiry ? \Carbon\Carbon::parse($reagent->expiry)->format('d M Y') : 'N/A' }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Washes Section -->
                        @if($pbmc->washes && $pbmc->washes->count() > 0)
                            <div class="mb-8">
                                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <i data-feather="refresh-cw" class="w-5 h-5 text-pbmc"></i>
                                    Washes
                                </h3>
                                
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="space-y-3">
                                        @foreach($pbmc->washes->sortBy('wash_number') as $wash)
                                            <div class="border-b border-gray-200 pb-3 last:border-0">
                                                <p class="font-semibold text-gray-900 mb-2">Wash #{{ $wash->wash_number }}</p>
                                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                                    <div><span class="text-gray-600">Start:</span> {{ $wash->start_time ?? 'N/A' }}</div>
                                                    <div><span class="text-gray-600">Stop:</span> {{ $wash->stop_time ?? 'N/A' }}</div>
                                                    <div><span class="text-gray-600">Volume:</span> {{ $wash->volume ?? 'N/A' }} ml</div>
                                                    <div><span class="text-gray-600">Centrifuge ID:</span> {{ $wash->centrifuge_id ?? 'N/A' }}</div>
                                                    <div><span class="text-gray-600">Speed:</span> {{ $wash->centrifuge_speed ?? 'N/A' }} rpm</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Auto Comment Section -->
                        @if ($pbmc->auto_comment)
                            <div class="mb-8">
                                <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                                    <i data-feather="message-square" class="w-5 h-5 text-pbmc"></i>
                                    Auto Comment
                                </h3>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <i data-feather="file-text" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $pbmc->auto_comment }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Metadata Section -->
                        <div class="mt-8 pt-6 border-t">
                            <h3 class="text-base font-bold text-gray-900 mb-3">Record Metadata</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Record ID:</span>
                                    <span class="ml-2 font-mono text-gray-900">{{ $pbmc->id }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Last Updated:</span>
                                    <span class="ml-2 text-gray-900">{{ $pbmc->updated_at?->format('d M Y, h:i A') ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-8 flex items-center justify-between pt-6 border-t">
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center gap-2 px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                                <i data-feather="arrow-left" class="w-4 h-4"></i>
                                Back to Dashboard
                            </a>

                            <div class="flex items-center gap-3">
                                @if ($isAdmin)
                                    <button type="button" onclick="confirmDelete()"
                                            class="inline-flex items-center gap-2 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                        Delete Record
                                    </button>
                                @endif

                                <a href="{{ route('pbmc.edit', $pbmc) }}"
                                   class="inline-flex items-center gap-2 px-6 py-2 bg-pbmc text-white rounded-lg hover:bg-orange-700 font-medium">
                                    <i data-feather="edit" class="w-4 h-4"></i>
                                    Edit Record
                                </a>
                            </div>
                        </div>

                        @if ($isAdmin)
                            <!-- Hidden Delete Form -->
                            <form id="deleteForm" method="POST" action="{{ route('pbmc.destroy', $pbmc) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this PBMC record?\n\nPTID: {{ $pbmc->ptid }}\n\nThis action cannot be undone.')) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>
@endpush
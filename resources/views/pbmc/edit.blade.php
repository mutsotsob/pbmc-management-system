@extends('layouts.app')

@section('title', 'Edit PBMC Record')

@section('content')
@php
    $user = Auth::user();
    $isAdmin = $user && strtolower(trim($user->user_type ?? '')) === 'admin';
@endphp

<div class="max-w-4xl mx-auto">

    <!-- Breadcrumb -->
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-600">
        <a href="{{ route('dashboard') }}" class="hover:text-pbmc">Dashboard</a>
        <span>/</span>
        <a href="{{ route('pbmc.show', $pbmc) }}" class="hover:text-pbmc">PBMC #{{ $pbmc->ptid }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Edit</span>
    </div>

    <div class="bg-white rounded-xl border p-6">

                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-orange-600 mb-1">
                            Edit PBMC Record
                        </h2>
                        <p class="text-gray-600">
                            Update the details for PTID: <strong>{{ $pbmc->ptid }}</strong>
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <i data-feather="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-red-900 mb-2">There were errors with your submission:</h3>
                                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('pbmc.update', $pbmc) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-8">

                            <!-- Study Information -->
                            <div class="border-b pb-6">
                                <h3 class="text-base font-bold text-gray-900 mb-4">Study Information</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Study Choice -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Study <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="study_choice" 
                                               value="{{ old('study_choice', $pbmc->study_choice) }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent"
                                               required>
                                        @error('study_choice')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Other Study Name -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Other Study Name
                                        </label>
                                        <input type="text" name="other_study_name" 
                                               value="{{ old('other_study_name', $pbmc->other_study_name) }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent">
                                        @error('other_study_name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Patient Information -->
                            <div class="border-b pb-6">
                                <h3 class="text-base font-bold text-gray-900 mb-4">Patient Information</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- PTID -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            PTID <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="ptid" 
                                               value="{{ old('ptid', $pbmc->ptid) }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent"
                                               required>
                                        @error('ptid')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Visit -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Visit <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="visit" 
                                               value="{{ old('visit', $pbmc->visit) }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent"
                                               required>
                                        @error('visit')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Collection Date -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Collection Date <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="collection_date" 
                                               value="{{ old('collection_date', $pbmc->collection_date?->format('Y-m-d')) }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent"
                                               required>
                                        @error('collection_date')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Collection Time -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Collection Time
                                        </label>
                                        <input type="time" name="collection_time" 
                                               value="{{ old('collection_time', $pbmc->collection_time) }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent">
                                        @error('collection_time')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Processing Information -->
                            <div class="border-b pb-6">
                                <h3 class="text-base font-bold text-gray-900 mb-4">Processing Information</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Process Start Date -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Process Start Date
                                        </label>
                                        <input type="date" name="process_start_date" 
                                               value="{{ old('process_start_date', $pbmc->process_start_date?->format('Y-m-d')) }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent">
                                        @error('process_start_date')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Process Start Time -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Process Start Time
                                        </label>
                                        <input type="time" name="process_start_time" 
                                               value="{{ old('process_start_time', $pbmc->process_start_time) }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent">
                                        @error('process_start_time')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Counting Method -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Counting Method <span class="text-red-500">*</span>
                                        </label>
                                        <select name="counting_method" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent"
                                                required>
                                            <option value="Manual Count" {{ old('counting_method', $pbmc->counting_method) == 'Manual Count' ? 'selected' : '' }}>Manual Count</option>
                                            <option value="Automated" {{ old('counting_method', $pbmc->counting_method) == 'Automated' ? 'selected' : '' }}>Automated</option>
                                        </select>
                                        @error('counting_method')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Usable Blood Volume -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Usable Blood Volume (ml)
                                        </label>
                                        <input type="number" name="usable_blood_volume" 
                                               value="{{ old('usable_blood_volume', $pbmc->usable_blood_volume) }}"
                                               step="0.01" min="0"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent">
                                        @error('usable_blood_volume')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Viability & Cell Count -->
                            <div class="border-b pb-6">
                                <h3 class="text-base font-bold text-gray-900 mb-4">Viability & Cell Count</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Viability Percent -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Viability (%)
                                        </label>
                                        <input type="number" name="viability_percent" 
                                               value="{{ old('viability_percent', $pbmc->viability_percent) }}"
                                               step="0.1" min="0" max="100"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent">
                                        @error('viability_percent')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Auto Viability Percent -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Auto Viability (%)
                                        </label>
                                        <input type="number" name="auto_viability_percent" 
                                               value="{{ old('auto_viability_percent', $pbmc->auto_viability_percent) }}"
                                               step="0.1" min="0" max="100"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent">
                                        @error('auto_viability_percent')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Cell Count Concentration -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Cell Count Concentration
                                        </label>
                                        <input type="number" name="cell_count_concentration" 
                                               value="{{ old('cell_count_concentration', $pbmc->cell_count_concentration) }}"
                                               step="0.01" min="0"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent">
                                        @error('cell_count_concentration')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Total Cell Number -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Total Cell Number
                                        </label>
                                        <input type="number" name="total_cell_number" 
                                               value="{{ old('total_cell_number', $pbmc->total_cell_number) }}"
                                               step="1" min="0"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent">
                                        @error('total_cell_number')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Automated Processing -->
                            <div class="border-b pb-6">
                                <h3 class="text-base font-bold text-gray-900 mb-4">Automated Processing</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Auto Total Viable Cells -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Auto Total Viable Cells
                                        </label>
                                        <input type="number" name="auto_total_viable_cells_original" 
                                               value="{{ old('auto_total_viable_cells_original', $pbmc->auto_total_viable_cells_original) }}"
                                               step="1" min="0"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent">
                                        @error('auto_total_viable_cells_original')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Auto Total Cells -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Auto Total Cells
                                        </label>
                                        <input type="number" name="auto_total_cells_original" 
                                               value="{{ old('auto_total_cells_original', $pbmc->auto_total_cells_original) }}"
                                               step="1" min="0"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent">
                                        @error('auto_total_cells_original')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Auto Total Cryovials Frozen -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Auto Total Cryovials Frozen
                                        </label>
                                        <input type="number" name="auto_total_cryovials_frozen" 
                                               value="{{ old('auto_total_cryovials_frozen', $pbmc->auto_total_cryovials_frozen) }}"
                                               step="1" min="0"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent">
                                        @error('auto_total_cryovials_frozen')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Auto Comment -->
                                <div class="mt-6">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Auto Comment
                                    </label>
                                    <textarea name="auto_comment" rows="3"
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pbmc focus:border-transparent"
                                              placeholder="Add automated processing comments...">{{ old('auto_comment', $pbmc->auto_comment) }}</textarea>
                                    @error('auto_comment')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-8 flex items-center justify-between pt-6 border-t">
                            <a href="{{ route('pbmc.show', $pbmc) }}"
                               class="inline-flex items-center gap-2 px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                                <i data-feather="x" class="w-4 h-4"></i>
                                Cancel
                            </a>

                            <div class="flex items-center gap-3">
                                @if ($isAdmin)
                                    <button type="button" onclick="confirmDelete()"
                                            class="inline-flex items-center gap-2 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                        Delete
                                    </button>
                                @endif

                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-6 py-2 bg-pbmc text-white rounded-lg hover:bg-orange-700 font-medium">
                                    <i data-feather="save" class="w-4 h-4"></i>
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>

                    @if ($isAdmin)
                        <!-- Hidden Delete Form -->
                        <form id="deleteForm" method="POST" action="{{ route('pbmc.destroy', $pbmc) }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this PBMC record? This action cannot be undone.')) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>
@endpush
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">

        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Record New PBMC</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Complete the form below to create a new PBMC record</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">

            <form method="POST" action="{{ route('pbmc.store') }}" class="p-8" id="pbmcForm">
                @csrf

                <!-- Study Selection -->
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4 pb-2 border-b-2 border-blue-600">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Study Information</h3>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Study Name <span class="text-red-500">*</span>
                        </label>

                        <select name="study_choice"
                                id="study_choice"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900
                                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                            <option value="">Select study</option>
                            <option value="HIV-C114" {{ old('study_choice') === 'HIV-C114' ? 'selected' : '' }}>HIV-C114</option>
                            <option value="Other" {{ old('study_choice') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>

                        @error('study_choice')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="otherStudyWrap" class="mb-4 {{ old('study_choice') === 'Other' ? '' : 'hidden' }}">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Specify Study Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="other_study_name" id="other_study_name"
                               value="{{ old('other_study_name') }}"
                               placeholder="Enter study name"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        @error('other_study_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- PT Details -->
                <div id="ptDetailsWrap" class="mb-8 {{ old('study_choice') ? '' : 'hidden' }}">
                    <div class="flex items-center gap-2 mb-4 pb-2 border-b-2 border-blue-600">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">PT Details</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                PTID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="ptid" id="ptid" value="{{ old('ptid') }}" required placeholder="e.g. PT12345"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                            @error('ptid')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Visit <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="visit" id="visit" value="{{ old('visit') }}" required placeholder="e.g. V1"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                            @error('visit')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Collection Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="collection_date" id="collection_date" value="{{ old('collection_date') }}"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                            @error('collection_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Collection Time <span class="text-red-500">*</span>
                            </label>
                            <input type="time" name="collection_time" id="collection_time" value="{{ old('collection_time') }}"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                            @error('collection_time')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Process Start Date</label>
                            <input type="date" name="process_start_date" id="process_start_date" value="{{ old('process_start_date') }}"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Process Start Time</label>
                            <input type="time" name="process_start_time" id="process_start_time" value="{{ old('process_start_time') }}"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                        </div>
                    </div>
                </div>

                <!-- Reagents + Processing + Washes -->
                <div id="reagentsWrap" class="mb-8 {{ old('study_choice') ? '' : 'hidden' }}">
                    <div class="flex items-center gap-2 mb-4 pb-2 border-b-2 border-blue-600">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Reagents</h3>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600">Reagents</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600">LOT Number</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600">Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php $reagents = ['Ficoll-Paque','FBS','DMSO','PBS']; @endphp
                                @foreach($reagents as $i => $r)
                                    <tr>
                                        <td class="px-4 py-2.5">
                                            <input type="text" readonly value="{{ $r }}"
                                                   class="w-full rounded-lg border px-3 py-2 text-sm bg-gray-50 cursor-not-allowed">
                                            <input type="hidden" name="reagents[{{ $i }}][name]" value="{{ $r }}">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="text" name="reagents[{{ $i }}][lot]" value="{{ old("reagents.$i.lot") }}"
                                                   class="w-full rounded-lg border px-3 py-2 text-sm ">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="date" name="reagents[{{ $i }}][expiry]" value="{{ old("reagents.$i.expiry") }}"
                                                   class="w-full rounded-lg border px-3 py-2 text-sm ">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Processing data -->
                    <div class="mt-8">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Processing Tube Type</label>
                        <select name="processing_data" id="processing_data"
                                class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                            <option value="">Select one</option>
                            <option value="EDTA" {{ old('processing_data')==='EDTA'?'selected':'' }}>EDTA</option>
                            <option value="ACD"  {{ old('processing_data')==='ACD' ? 'selected':'' }}>ACD</option>
                            <option value="HEP"  {{ old('processing_data')==='HEP' ? 'selected':'' }}>HEP</option>
                        </select>
                    </div>

                    <div class="mt-6">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Plasma Harvesting</h4>
                        <div class="flex gap-4 mb-4">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="radio" name="plasma_harvesting" value="1" {{ old('plasma_harvesting')==='1' ? 'checked' : '' }}> Yes
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="radio" name="plasma_harvesting" value="0" {{ old('plasma_harvesting')==='0' ? 'checked' : '' }}> No
                            </label>
                        </div>

                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Sample Status (circle one or more)</h4>
                        @php $oldStatuses = (array) old('sample_status', []); @endphp
                        <div class="flex flex-wrap gap-4 mb-6 text-sm text-gray-700">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="sample_status[]" value="NORM" {{ in_array('NORM',$oldStatuses) ? 'checked':'' }}> NORM
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="sample_status[]" value="HEM" {{ in_array('HEM',$oldStatuses) ? 'checked':'' }}> HEM
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="sample_status[]" value="CLOTTED" {{ in_array('CLOTTED',$oldStatuses) ? 'checked':'' }}> CLOTTED
                            </label>
                        </div>

                        <!-- Counting method -->
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-gray-800 mb-2">Counting Method</h4>
                            <div class="flex gap-4 text-sm text-gray-700">
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="counting_method" value="Manual Count" {{ old('counting_method')==='Manual Count' ? 'checked' : '' }}> Manual Count
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="counting_method" value="Automated" {{ old('counting_method')==='Automated' ? 'checked' : '' }}> Automated
                                </label>
                            </div>
                        </div>

                        <!-- Usable blood volume -->
                        <div class="mb-8">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Usable Blood Volume</label>
                            <input type="text" name="usable_blood_volume" value="{{ old('usable_blood_volume') }}"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                        </div>

                        <!-- Washes -->
                        @php $washDefaults = ['volume'=>'45','centrifuge_id'=>'CEN001','centrifuge_speed'=>'400XG']; @endphp
                        @foreach([1,2,3] as $w)
                            <div class="mb-6 border rounded-lg overflow-hidden">
                                <div class="px-5 py-3 bg-green-50 dark:bg-green-900/10 border-b">
                                    <h4 class="font-semibold text-green-700 dark:text-green-300">Wash {{ $w }}</h4>
                                </div>

                                <div class="p-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Wash Start Time</label>
                                            <input type="time" name="washes[{{ $w }}][start_time]" value="{{ old("washes.$w.start_time") }}"
                                                   class="w-full rounded-lg border px-3 py-2 ">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Wash Stop Time</label>
                                            <input type="time" name="washes[{{ $w }}][stop_time]" value="{{ old("washes.$w.stop_time") }}"
                                                   class="w-full rounded-lg border px-3 py-2 ">
                                        </div>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y">
                                            <thead class="bg-white">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Parameter</th>
                                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Value</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-sm">
                                                <tr>
                                                    <td class="px-4 py-2">Volume</td>
                                                    <td class="px-4 py-2">
                                                        <input type="text" name="washes[{{ $w }}][volume]"
                                                               value="{{ old("washes.$w.volume", $washDefaults['volume']) }}"
                                                               class="w-full rounded-lg border px-3 py-2 ">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2">Centrifuge ID</td>
                                                    <td class="px-4 py-2">
                                                        <input type="text" name="washes[{{ $w }}][centrifuge_id]"
                                                               value="{{ old("washes.$w.centrifuge_id", $washDefaults['centrifuge_id']) }}"
                                                               class="w-full rounded-lg border px-3 py-2 ">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2">Centrifuge Speed</td>
                                                    <td class="px-4 py-2">
                                                        <input type="text" name="washes[{{ $w }}][centrifuge_speed]"
                                                               value="{{ old("washes.$w.centrifuge_speed", $washDefaults['centrifuge_speed']) }}"
                                                               class="w-full rounded-lg border px-3 py-2 ">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>

                <!-- Manual Cell Counting -->
                <div id="manualCountingWrap" class="mb-8 {{ old('study_choice') ? '' : 'hidden' }}">
                    <div class="flex items-center gap-2 mb-4 pb-2 border-b-2 border-blue-600">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Manual Cell Counting</h3>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600">Manual Count</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600">NonViable Cells</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600">Viable Cells</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-gray-600">Total</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @php $squares = ['Square 1','Square 2','Square 3','Square 4']; @endphp
                                @foreach($squares as $i => $label)
                                    <tr>
                                        <td class="px-4 py-2.5">
                                            <input type="text" readonly value="{{ $label }}"
                                                   class="w-full rounded-lg border px-3 py-2 text-sm bg-gray-50 cursor-not-allowed">
                                            <input type="hidden" name="manual_counts[{{ $i }}][label]" value="{{ $label }}">
                                        </td>

                                        <td class="px-4 py-2.5">
                                            <input type="number" min="0" step="1"
                                                   name="manual_counts[{{ $i }}][nonviable]"
                                                   value="{{ old("manual_counts.$i.nonviable", '') }}"
                                                   class="manual-nonviable w-full rounded-lg border px-3 py-2 text-sm ">
                                        </td>

                                        <td class="px-4 py-2.5">
                                            <input type="number" min="0" step="1"
                                                   name="manual_counts[{{ $i }}][viable]"
                                                   value="{{ old("manual_counts.$i.viable", '') }}"
                                                   class="manual-viable w-full rounded-lg border px-3 py-2 text-sm ">
                                        </td>

                                        <td class="px-4 py-2.5">
                                            <input type="number" readonly
                                                   name="manual_counts[{{ $i }}][total]"
                                                   value="{{ old("manual_counts.$i.total", '') }}"
                                                   class="manual-total w-full rounded-lg border px-3 py-2 text-sm bg-gray-50">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <td class="px-4 py-2.5 font-semibold text-sm text-gray-700">Average</td>
                                    <td class="px-4 py-2.5">
                                        <input type="text" readonly id="avgNonViable"
                                               class="w-full rounded-lg border px-3 py-2 text-sm bg-gray-50">
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <input type="text" readonly id="avgViable"
                                               class="w-full rounded-lg border px-3 py-2 text-sm bg-gray-50">
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <input type="text" readonly id="sumTotals"
                                               class="w-full rounded-lg border px-3 py-2 text-sm bg-gray-50">
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Manual Count Start Time</label>
                            <input type="time" name="manual_count_start_time" value="{{ old('manual_count_start_time') }}"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Manual Count Stop Time</label>
                            <input type="time" name="manual_count_stop_time" value="{{ old('manual_count_stop_time') }}"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Haemocytometer Factor</label>
                            <input type="number" step="any" id="haemocytometer_factor" name="haemocytometer_factor"
                                   value="{{ old('haemocytometer_factor', '10000') }}"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">PBMC Dilution Factor</label>
                            <input type="number" step="any" id="pbmc_dilution_factor" name="pbmc_dilution_factor"
                                   value="{{ old('pbmc_dilution_factor', '2') }}"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                        </div>
                    </div>
                </div>

                <!-- ✅ Sample Calculated outcomes -->
                <div id="calculatedOutcomesWrap" class="mb-8 {{ old('study_choice') ? '' : 'hidden' }}">
                    <div class="flex items-center gap-2 mb-4 pb-2 border-b-2 border-blue-600">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Sample Calculated Outcomes</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Average cell count per square (cells/mm<sup>2</sup>)
                            </label>
                            <input type="text" readonly id="out_avg_cells_per_square"
                                   class="w-full rounded-lg border border-gray-300 bg-gray-50 dark:text-white px-4 py-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Total Count
                            </label>
                            <input type="text" readonly id="out_total_count"
                                   class="w-full rounded-lg border border-gray-300 bg-gray-50 dark:text-white px-4 py-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Average Viable Cells
                            </label>
                            <input type="text" readonly id="out_avg_viable_cells"
                                   class="w-full rounded-lg border border-gray-300 bg-gray-50 dark:text-white px-4 py-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Viability %
                            </label>
                            <div class="relative">
                                <input type="text" readonly id="out_viability_percent"
                                       class="w-full rounded-lg border border-gray-300 bg-gray-50 dark:text-white px-4 py-2.5 pr-10">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">%</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Counting Resuspension Volume (PBS)
                            </label>
                            <input type="number" step="any" min="0" id="counting_resuspension" name="counting_resuspension"
                                   value="{{ old('counting_resuspension') }}"
                                   placeholder="Enter resuspension volume"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                            <p class="mt-1 text-xs text-gray-500">Volume used for resuspension (used to calculate Total Cell Number).</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Cell Count Concentration (×10<sup>6</sup>/mL)
                            </label>
                            <input type="text" readonly id="out_cell_count_concentration"
                                   class="w-full rounded-lg border border-gray-300 bg-gray-50 dark:text-white px-4 py-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Total Cell Number (×10<sup>6</sup>)
                            </label>
                            <input type="text" readonly id="out_total_cell_number"
                                   class="w-full rounded-lg border border-gray-300 bg-gray-50 dark:text-white px-4 py-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Final CPS Resuspension Volume (Vf)
                            </label>
                            <input type="text" readonly id="final_cps_resuspension_volume" name="final_cps_resuspension_volume"
                                   value="{{ old('final_cps_resuspension_volume') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-gray-50 dark:text-white px-4 py-2.5">
                            <p class="mt-1 text-xs text-gray-500">Vf = Total Cell Number / 15,000,000</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Actual Number of Cells per Vial (N2) (×10<sup>6</sup>)
                            </label>
                            <input type="text" readonly id="out_cells_per_vial"
                                   class="w-full rounded-lg border border-gray-300 bg-gray-50 dark:text-white px-4 py-2.5">
                            <p class="mt-1 text-xs text-gray-500">N2 = Total Cell Number (T) / Final CPS Resuspension Volume (Vf)</p>
                        </div>
                    </div>

                    <p class="mt-4 text-xs text-gray-500">
                        <strong>Calculation Formulas:</strong><br>
                        • Average cell count per square = average of (NonViable + Viable) across 4 squares<br>
                        • Total Count = Avg per square × Haemocytometer factor × PBMC dilution factor<br>
                        • Viability % = (Avg viable cells) ÷ (Avg viable + Avg non-viable) × 100<br>
                        • <strong>Cell Count Concentration (×10⁶/mL) = (Avg viable cells × Haemocytometer factor × PBMC dilution factor) / 1,000,000</strong><br>
                        • <strong>Total Cell Number (×10⁶) = Cell Count Concentration × Counting Resuspension Volume</strong><br>
                        • Final CPS Resuspension Volume (Vf) = Total Cell Number ÷ 15,000,000<br>
                        • Cells per Vial (N2) = Total Cell Number ÷ Final CPS Resuspension Volume
                    </p>
                </div>

                <!-- ✅ Automated Cell Count -->
                <div id="autoCountingWrap" class="mb-8 {{ old('study_choice') ? '' : 'hidden' }}">
                    <div class="flex items-center gap-2 mb-4 pb-2 border-b-2 border-blue-600">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Automated Cell Count</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">System clean done</label>
                            <select name="auto_system_clean_done"
                                    class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                                <option value="">Select</option>
                                <option value="Yes" {{ old('auto_system_clean_done')==='Yes'?'selected':'' }}>Yes</option>
                                <option value="No"  {{ old('auto_system_clean_done')==='No'?'selected':'' }}>No</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">QC passed</label>
                            <select name="auto_qc_passed"
                                    class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                                <option value="">Select</option>
                                <option value="Yes" {{ old('auto_qc_passed')==='Yes'?'selected':'' }}>Yes</option>
                                <option value="No"  {{ old('auto_qc_passed')==='No'?'selected':'' }}>No</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Viability %</label>
                            <div class="relative">
                                <input type="number" min="0" max="100" step="0.01"
                                       name="auto_viability_percent"
                                       value="{{ old('auto_viability_percent') }}"
                                       class="w-full rounded-lg border border-gray-300  px-4 py-2.5 pr-10">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">%</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Total viable cells in original sample</label>
                            <input type="number" step="1" min="0"
                                   name="auto_total_viable_cells_original"
                                   value="{{ old('auto_total_viable_cells_original') }}"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Total cells in original sample</label>
                            <input type="number" step="1" min="0"
                                   name="auto_total_cells_original"
                                   value="{{ old('auto_total_cells_original') }}"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Total number of cryovials frozen</label>
                            <input type="number" step="1" min="0"
                                   name="auto_total_cryovials_frozen"
                                   value="{{ old('auto_total_cryovials_frozen') }}"
                                   class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                        </div>
                    </div>

                    <div class="mt-8">
                        <h4 class="text-base font-semibold text-gray-900 mb-3">Stratacooler / Mr Frosty</h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Storage temp (2-80 Degrees)</label>
                                <input type="number" step="0.1" min="2" max="80"
                                       name="frosty_storage_temp"
                                       value="{{ old('frosty_storage_temp') }}"
                                       class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                <input type="date" name="frosty_date" value="{{ old('frosty_date') }}"
                                       class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                                <input type="time" name="frosty_time" value="{{ old('frosty_time') }}"
                                       class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Transfer</label>
                                <input type="text" name="frosty_transfer" value="{{ old('frosty_transfer') }}"
                                       placeholder="e.g. moved to freezer / LN2 staging"
                                       class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h4 class="text-base font-semibold text-gray-900 mb-3">Person Transferring cryovials to LN2</h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">First</label>
                                <input type="text" name="ln2_transfer_first" value="{{ old('ln2_transfer_first') }}"
                                       class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Last</label>
                                <input type="text" name="ln2_transfer_last" value="{{ old('ln2_transfer_last') }}"
                                       class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Date / time cryovials were transferred to LN2
                                </label>
                                <input type="datetime-local" name="ln2_transfer_datetime" value="{{ old('ln2_transfer_datetime') }}"
                                       class="w-full rounded-lg border border-gray-300  px-4 py-2.5">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Comment</label>
                                <textarea name="auto_comment" rows="3"
                                          class="w-full rounded-lg border border-gray-300  px-4 py-2.5">{{ old('auto_comment') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200 my-8"></div>

                <!-- Action Buttons -->
                <div id="actionButtonsWrap" class="flex flex-wrap items-center justify-end gap-3 {{ old('study_choice') ? 'flex' : 'hidden' }}">
                    <button type="button" id="resetFormBtn"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </button>

                    <button type="button" id="saveProgressBtn"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-yellow-300 bg-yellow-50 text-yellow-700 text-sm font-medium hover:bg-yellow-100 hover:border-yellow-400 transition-all duration-200 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Save Progress
                    </button>

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition-all duration-200 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Submit Form
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Notification Container -->
<div id="notificationContainer" class="fixed top-4 right-4 z-50 space-y-3"></div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full transform transition-all">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div id="modalIcon" class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center">
                    <!-- Icon will be inserted here -->
                </div>
                <div>
                    <h3 id="modalTitle" class="text-lg font-bold text-gray-900"></h3>
                    <p id="modalMessage" class="text-sm text-gray-600 mt-1"></p>
                </div>
            </div>
            <div class="flex gap-3 justify-end">
                <button id="modalCancel" class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-all duration-200">
                    Cancel
                </button>
                <button id="modalConfirm" class="px-6 py-2.5 rounded-lg font-semibold text-white transition-all duration-200">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const studySelect = document.getElementById('study_choice');
    const otherWrap = document.getElementById('otherStudyWrap');
    const otherInput = document.getElementById('other_study_name');

    const ptWrap = document.getElementById('ptDetailsWrap');
    const reagentsWrap = document.getElementById('reagentsWrap');
    const manualWrap = document.getElementById('manualCountingWrap');
    const autoWrap = document.getElementById('autoCountingWrap');
    const outcomesWrap = document.getElementById('calculatedOutcomesWrap');
    const actionButtonsWrap = document.getElementById('actionButtonsWrap');

    const ptFields = ['ptid','visit','collection_date','collection_time','process_start_date','process_start_time']
        .map(id => document.getElementById(id)).filter(Boolean);

    function setPtRequired(required){
        ptFields.forEach(el => {
            if (!el) return;
            if (required) el.setAttribute('required','required'); else el.removeAttribute('required');
        });
    }

    function toggleStudyRelated(){
        if (!studySelect) return;
        const val = studySelect.value;

        if (val && val !== '') {
            ptWrap.classList.remove('hidden');
            if (reagentsWrap) reagentsWrap.classList.remove('hidden');
            if (manualWrap) manualWrap.classList.remove('hidden');
            if (autoWrap) autoWrap.classList.remove('hidden');
            if (outcomesWrap) outcomesWrap.classList.remove('hidden');
            if (actionButtonsWrap) actionButtonsWrap.classList.remove('hidden');
            setPtRequired(true);
        } else {
            ptWrap.classList.add('hidden');
            if (reagentsWrap) reagentsWrap.classList.add('hidden');
            if (manualWrap) manualWrap.classList.add('hidden');
            if (autoWrap) autoWrap.classList.add('hidden');
            if (outcomesWrap) outcomesWrap.classList.add('hidden');
            if (actionButtonsWrap) actionButtonsWrap.classList.add('hidden');
            setPtRequired(false);

            ptFields.forEach(el => { if (el && el.tagName !== 'SELECT') el.value = ''; });
        }

        if (val === 'Other') {
            otherWrap.classList.remove('hidden');
            if (otherInput) otherInput.setAttribute('required','required');
        } else {
            otherWrap.classList.add('hidden');
            if (otherInput) { otherInput.removeAttribute('required'); otherInput.value = ''; }
        }
    }

    if (studySelect) {
        studySelect.addEventListener('change', toggleStudyRelated);
        toggleStudyRelated();
    }

    // ---------- Manual counting + Calculated outcomes ----------
    function num(v) {
        const n = parseFloat(v);
        return Number.isFinite(n) ? n : 0;
    }
    function fixed(v, d = 2) {
        if (!Number.isFinite(v)) return '';
        return (Math.round(v * Math.pow(10, d)) / Math.pow(10, d)).toFixed(d);
    }

    function recomputeManualAndOutcomes() {
        const nonInputs = Array.from(document.querySelectorAll('.manual-nonviable'));
        const viableInputs = Array.from(document.querySelectorAll('.manual-viable'));
        const totalInputs = Array.from(document.querySelectorAll('.manual-total'));

        let nonSum = 0, viableSum = 0, totalSum = 0;

        for (let i = 0; i < nonInputs.length; i++) {
            const n = num(nonInputs[i].value);
            const v = num(viableInputs[i].value);
            const t = n + v;

            if (totalInputs[i]) totalInputs[i].value = t;
            nonSum += n;
            viableSum += v;
            totalSum += t;
        }

        const rows = nonInputs.length || 1;
        const avgNon = nonSum / rows;
        const avgViable = viableSum / rows;
        const avgTotal = totalSum / rows;

        // footer fields
        const avgNonEl = document.getElementById('avgNonViable');
        const avgViableEl = document.getElementById('avgViable');
        const sumTotalsEl = document.getElementById('sumTotals');
        if (avgNonEl) avgNonEl.value = fixed(avgNon, 2);
        if (avgViableEl) avgViableEl.value = fixed(avgViable, 2);
        if (sumTotalsEl) sumTotalsEl.value = fixed(totalSum, 0);

        // Get factors
        const haem = num(document.getElementById('haemocytometer_factor')?.value);
        const dil  = num(document.getElementById('pbmc_dilution_factor')?.value);

        // Average cell count per square (cells/mm^2) -> using avgTotal
        const outAvgPerSq = avgTotal;

        // Total Count -> avgTotal × haem × dil
        const outTotalCount = avgTotal * haem * dil;

        // Viability % = avgViable / (avgViable + avgNon) × 100
        const viabilityPct = (avgViable + avgNon) > 0 ? (avgViable / (avgViable + avgNon)) * 100 : 0;

        // Counting resuspension volume
        const countingResusp = num(document.getElementById('counting_resuspension')?.value);

        // ✅ CORRECTED FORMULA:
        // Cell Count Concentration (×10⁶/mL) = (avgViable × haem × dil) / 1,000,000
        const concentration = (avgViable * haem * dil) / 1000000;

        // ✅ Total Cell Number (×10⁶) = concentration × counting resuspension volume
        const totalCellNum = concentration * countingResusp;

        // Final CPS resuspension volume (Vf) = Total Cell Number / 15,000,000
        const finalCpsVol = totalCellNum > 0 ? (totalCellNum / 15) : 0;

        // Cells per vial = totalCellNum / finalCpsVol
        const cellsPerVial = finalCpsVol > 0 ? (totalCellNum / finalCpsVol) : 0;

        const el1 = document.getElementById('out_avg_cells_per_square');
        const el2 = document.getElementById('out_total_count');
        const el3 = document.getElementById('out_avg_viable_cells');
        const el4 = document.getElementById('out_viability_percent');
        const el5 = document.getElementById('out_cell_count_concentration');
        const el6 = document.getElementById('out_total_cell_number');
        const el7 = document.getElementById('final_cps_resuspension_volume');
        const el8 = document.getElementById('out_cells_per_vial');

        if (el1) el1.value = fixed(outAvgPerSq, 2);
        if (el2) el2.value = fixed(outTotalCount, 2);
        if (el3) el3.value = fixed(avgViable, 2);
        if (el4) el4.value = fixed(viabilityPct, 2);
        if (el5) el5.value = fixed(concentration, 2);
        if (el6) el6.value = countingResusp > 0 ? fixed(totalCellNum, 2) : '';
        if (el7) el7.value = countingResusp > 0 ? fixed(finalCpsVol, 3) : '';
        if (el8) el8.value = (countingResusp > 0 && finalCpsVol > 0) ? fixed(cellsPerVial, 2) : '';
    }

    // listeners
    document.querySelectorAll('.manual-nonviable, .manual-viable').forEach(el => {
        el.addEventListener('input', function () {
            if (this.value && parseFloat(this.value) < 0) this.value = 0;
            recomputeManualAndOutcomes();
        });
    });

    ['haemocytometer_factor','pbmc_dilution_factor','counting_resuspension']
        .forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', recomputeManualAndOutcomes);
        });

    // initial compute
    recomputeManualAndOutcomes();

    // ---------- Button handlers ----------

    // Notification System
    function showNotification(message, type = 'success') {
        const container = document.getElementById('notificationContainer');
        const notification = document.createElement('div');

        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };

        const icons = {
            success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
            error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
            warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
            info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        };

        notification.className = `${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 min-w-[300px] transform transition-all duration-300 translate-x-full`;
        notification.innerHTML = `
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${icons[type]}
            </svg>
            <span class="flex-1 font-medium">${message}</span>
            <button class="hover:bg-white/20 rounded-lg p-1 transition-colors" onclick="this.parentElement.remove()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;

        container.appendChild(notification);

        setTimeout(() => notification.classList.remove('translate-x-full'), 10);

        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }

    // Confirmation Modal
    function showConfirmModal(title, message, confirmText, confirmColor, onConfirm) {
        const modal = document.getElementById('confirmModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalIcon = document.getElementById('modalIcon');
        const modalConfirm = document.getElementById('modalConfirm');
        const modalCancel = document.getElementById('modalCancel');

        const configs = {
            danger: {
                bg: 'bg-red-50',
                btnBg: 'bg-red-600 hover:bg-red-700',
                icon: '<svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
            },
            warning: {
                bg: 'bg-yellow-50',
                btnBg: 'bg-yellow-600 hover:bg-yellow-700',
                icon: '<svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>'
            },
            success: {
                bg: 'bg-green-50',
                btnBg: 'bg-green-600 hover:bg-green-700',
                icon: '<svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
            }
        };

        const config = configs[confirmColor] || configs.warning;

        modalTitle.textContent = title;
        modalMessage.textContent = message;
        modalIcon.className = `flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center ${config.bg}`;
        modalIcon.innerHTML = config.icon;
        modalConfirm.textContent = confirmText;
        modalConfirm.className = `px-6 py-2.5 rounded-lg font-semibold text-white transition-all duration-200 ${config.btnBg}`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Cleanup previous listeners by cloning
        const newModalConfirm = modalConfirm.cloneNode(true);
        const newModalCancel = modalCancel.cloneNode(true);
        modalConfirm.parentNode.replaceChild(newModalConfirm, modalConfirm);
        modalCancel.parentNode.replaceChild(newModalCancel, modalCancel);

        newModalConfirm.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            onConfirm();
        });

        newModalCancel.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        // Close on backdrop click (attach once per open)
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }, { once: true });
    }

    // Reset Form button
    document.getElementById('resetFormBtn')?.addEventListener('click', function() {
        showConfirmModal(
            'Reset Form',
            'Are you sure you want to reset the form? All unsaved data will be lost.',
            'Reset Form',
            'danger',
            () => {
                document.getElementById('pbmcForm').reset();
                if (studySelect) {
                    studySelect.dispatchEvent(new Event('change'));
                }
                recomputeManualAndOutcomes();
                showNotification('Form has been reset successfully', 'success');
            }
        );
    });

    // Save Progress button
    document.getElementById('saveProgressBtn')?.addEventListener('click', function() {
        showConfirmModal(
            'Save Progress',
            'This will save your current form progress to your browser. You can restore it later.',
            'Save Progress',
            'warning',
            () => {
                const formData = new FormData(document.getElementById('pbmcForm'));
                const data = {};
                formData.forEach((value, key) => {
                    if (!data[key]) {
                        data[key] = value;
                    } else {
                        if (!Array.isArray(data[key])) {
                            data[key] = [data[key]];
                        }
                        data[key].push(value);
                    }
                });
                localStorage.setItem('pbmc_form_progress', JSON.stringify(data));
                localStorage.setItem('pbmc_form_progress_timestamp', new Date().toISOString());
                showNotification('Progress saved successfully!', 'success');
            }
        );
    });

    // Submit form with confirmation
    document.getElementById('pbmcForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        showConfirmModal(
            'Submit Form',
            'Are you sure you want to submit this PBMC record? Please review all information before submitting.',
            'Submit Form',
            'success',
            () => {
                localStorage.removeItem('pbmc_form_progress');
                localStorage.removeItem('pbmc_form_progress_timestamp');
                e.target.submit();
            }
        );
    });

    // Optional: Load saved progress on page load
    const savedProgress = localStorage.getItem('pbmc_form_progress');
    const savedTimestamp = localStorage.getItem('pbmc_form_progress_timestamp');

    if (savedProgress) {
        const timeAgo = savedTimestamp ? new Date(savedTimestamp).toLocaleString() : 'Unknown time';

        showConfirmModal(
            'Restore Saved Progress',
            `You have saved form progress from ${timeAgo}. Would you like to restore it?`,
            'Restore Progress',
            'warning',
            () => {
                try {
                    const data = JSON.parse(savedProgress);
                    Object.keys(data).forEach(key => {
                        const inputs = document.querySelectorAll(`[name="${key}"]`);
                        inputs.forEach(input => {
                            if (input.type === 'checkbox') {
                                const values = Array.isArray(data[key]) ? data[key] : [data[key]];
                                input.checked = values.includes(input.value);
                            } else if (input.type === 'radio') {
                                input.checked = input.value === data[key];
                            } else {
                                input.value = data[key];
                            }
                        });
                    });

                    if (studySelect) {
                        studySelect.dispatchEvent(new Event('change'));
                    }
                    recomputeManualAndOutcomes();
                    showNotification('Progress restored successfully!', 'success');
                } catch (e) {
                    console.error('Error loading saved progress:', e);
                    showNotification('Failed to restore progress', 'error');
                }
            }
        );
    }
});
</script>
@endsection

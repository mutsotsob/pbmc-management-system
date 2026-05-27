@props([
    'type' => 'success',   // success | error | warning | info
    'dismissible' => true,
])

@php
    $styles = [
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'error'   => 'bg-red-50   border-red-200   text-red-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'info'    => 'bg-blue-50  border-blue-200  text-blue-800',
    ];
    $icons = [
        'success' => 'check-circle',
        'error'   => 'alert-circle',
        'warning' => 'alert-triangle',
        'info'    => 'info',
    ];
    $cls  = $styles[$type] ?? $styles['info'];
    $icon = $icons[$type]  ?? 'info';
@endphp

<div
    @if($dismissible) x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 7000)" @endif
    {{ $attributes->merge(['class' => "mb-4 flex items-start gap-3 rounded-lg border p-4 text-sm $cls"]) }}
>
    <i data-feather="{{ $icon }}" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
    <div class="flex-1">{{ $slot }}</div>
    @if($dismissible)
        <button @click="show = false" class="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity">
            <i data-feather="x" class="w-4 h-4"></i>
        </button>
    @endif
</div>

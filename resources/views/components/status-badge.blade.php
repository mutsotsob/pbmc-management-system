@props([
    'value',
    'type' => null, // auto | success | danger | warning | info | neutral
])

@php
    // Auto-detect colour from common domain values when type not provided
    if (!$type) {
        $lower = strtolower((string) $value);
        $type = match(true) {
            in_array($lower, ['active', 'enabled', 'pass', 'passed', 'verified', 'success', 'received']) => 'success',
            in_array($lower, ['disabled', 'inactive', 'fail', 'failed', 'blocked', 'error'])             => 'danger',
            in_array($lower, ['pending', 'warning', 'review', 'dispatched'])                             => 'warning',
            in_array($lower, ['admin'])                                                                   => 'info',
            default                                                                            => 'neutral',
        };
    }
    $cls = match($type) {
        'success' => 'bg-green-100  text-green-700',
        'danger'  => 'bg-red-100    text-red-700',
        'warning' => 'bg-yellow-100 text-yellow-700',
        'info'    => 'bg-orange-100 text-orange-700',
        default   => 'bg-gray-100   text-gray-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold $cls"]) }}>
    {{ $value }}
</span>

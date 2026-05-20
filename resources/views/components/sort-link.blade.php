@props([
    'label',
    'column',
    'sort' => null,
    'dir' => 'asc',
])

@php
    $isActive = $sort === $column;
    $nextDir = $isActive && $dir === 'asc' ? 'desc' : 'asc';
    $query = array_merge(request()->query(), [
        'sort' => $column,
        'dir' => $nextDir,
    ]);
@endphp

<a href="{{ url()->current() . '?' . http_build_query($query) }}"
   {{ $attributes->class('inline-flex items-center gap-1 hover:text-gray-900 transition-colors') }}>
    <span>{{ $label }}</span>

    @if ($isActive)
        <span aria-hidden="true" class="text-gray-900">
            {{ $dir === 'asc' ? '↑' : '↓' }}
        </span>
    @else
        <span aria-hidden="true" class="text-gray-400">↕</span>
    @endif
</a>

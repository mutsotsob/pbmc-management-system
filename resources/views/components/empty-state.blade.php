@props([
    'icon'        => 'inbox',
    'title'       => 'No records found',
    'description' => null,
    'actionUrl'   => null,
    'actionLabel' => 'Create one',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-14 text-center']) }}>
    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-4">
        <i data-feather="{{ $icon }}" class="w-5 h-5 text-gray-400"></i>
    </div>
    <p class="text-sm font-medium text-gray-700">{{ $title }}</p>
    @if($description)
        <p class="text-xs text-gray-400 mt-1">{{ $description }}</p>
    @endif
    @if($actionUrl)
        <a href="{{ $actionUrl }}"
           class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-pbmc text-white text-sm font-medium hover:opacity-90 transition-opacity">
            <i data-feather="plus" class="w-4 h-4"></i>
            {{ $actionLabel }}
        </a>
    @endif
</div>

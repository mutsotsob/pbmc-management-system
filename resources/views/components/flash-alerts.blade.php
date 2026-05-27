@props([
    'showErrors' => true,
])

@php
    $flashMessages = [];

    foreach (['success', 'error', 'warning', 'info'] as $type) {
        $message = session()->pull($type);

        if (filled($message)) {
            $messages = is_array($message) ? $message : [$message];

            foreach ($messages as $item) {
                if (filled($item)) {
                    $flashMessages[] = [
                        'type' => $type,
                        'message' => $item,
                    ];
                }
            }
        }
    }

    $status = session()->pull('status');
    $statusMessages = [
        'password-updated' => 'Your password has been updated successfully.',
        'profile-updated' => 'Your profile information has been updated successfully.',
        'profile-photo-updated' => 'Your profile picture has been updated successfully.',
        'verification-link-sent' => 'A new verification link has been sent to your email address.',
    ];

    if (filled($status)) {
        $flashMessages[] = [
            'type' => 'success',
            'message' => $statusMessages[$status] ?? $status,
        ];
    }

    $validationMessages = collect();

    if ($showErrors && isset($errors)) {
        $validationMessages = collect($errors->getBags())
            ->flatMap(fn ($bag) => $bag->all())
            ->filter()
            ->unique()
            ->values();
    }
@endphp

@if (!empty($flashMessages) || $validationMessages->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'space-y-3']) }}>
        @foreach ($flashMessages as $flash)
            <x-alert :type="$flash['type']">
                {{ $flash['message'] }}
            </x-alert>
        @endforeach

        @if ($validationMessages->isNotEmpty())
            <x-alert type="error" :dismissible="false">
                <strong>Please fix the highlighted fields and try again.</strong>
                <ul class="mt-1 list-disc space-y-0.5 pl-4">
                    @foreach ($validationMessages as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif
    </div>
@endif

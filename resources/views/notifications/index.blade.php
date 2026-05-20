@extends('layouts.app')

@section('title', 'Notifications')
@section('topnav-title', 'Notifications')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Notifications</h2>
            <p class="text-sm text-gray-500 mt-0.5">System alerts and activity updates for your account.</p>
        </div>
        @if (auth()->user()->unreadNotifications->isNotEmpty())
            <form method="POST" action="{{ route('notifications.mark-read') }}">
                @csrf
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-orange-600 border border-orange-300 rounded-lg hover:bg-orange-50 transition-colors">
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    {{-- Notification list --}}
    <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);
                $iconMap = [
                    'user_created'     => ['icon' => 'user-plus',  'color' => 'text-green-500  bg-green-50'],
                    'password_changed' => ['icon' => 'lock',       'color' => 'text-yellow-500 bg-yellow-50'],
                    'sync_completed'   => ['icon' => 'refresh-cw', 'color' => 'text-teal-500   bg-teal-50'],
                    'report_imported'  => ['icon' => 'file-text',  'color' => 'text-indigo-500 bg-indigo-50'],
                ];
                $type   = $data['type'] ?? 'info';
                $icon   = $iconMap[$type]['icon']  ?? 'bell';
                $color  = $iconMap[$type]['color'] ?? 'text-gray-500 bg-gray-50';
            @endphp
            <div class="flex items-start gap-4 px-5 py-4 {{ $isUnread ? 'bg-orange-50/30' : '' }}">
                <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center {{ $color }}">
                    <i data-feather="{{ $icon }}" class="w-4 h-4"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-gray-800">{{ $data['title'] ?? 'Notification' }}</p>
                        @if ($isUnread)
                            <span class="w-2 h-2 rounded-full bg-orange-500 flex-shrink-0"></span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 mt-0.5">{{ $data['message'] ?? '' }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
                @if (!empty($data['url']))
                    <a href="{{ $data['url'] }}"
                        class="flex-shrink-0 text-xs text-orange-500 hover:underline">
                        View →
                    </a>
                @endif
            </div>
        @empty
            <x-empty-state icon="bell-off" title="No notifications yet." description="System alerts and activity updates will appear here." />
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div>{{ $notifications->links() }}</div>
    @endif

</div>
@endsection

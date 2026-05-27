@extends('layouts.app')

@section('title', 'Audit Log')
@section('topnav-title', 'Audit Log')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">System Audit Log</h2>
            <p class="text-sm text-gray-500 mt-0.5">All create, update, delete, and system events.</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.audit-logs') }}"
          class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-36">
            <label class="block text-xs font-medium text-gray-600 mb-1">Event</label>
            <select name="event"
                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
                <option value="">All events</option>
                @foreach ($events as $ev)
                    <option value="{{ $ev }}" @selected(request('event') === $ev)>{{ $ev }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-36">
            <label class="block text-xs font-medium text-gray-600 mb-1">User</label>
            <select name="user"
                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
                <option value="">All users</option>
                @foreach ($users as $userName)
                    <option value="{{ $userName }}" @selected(request('user') === $userName)>{{ $userName }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-36">
            <label class="block text-xs font-medium text-gray-600 mb-1">Model</label>
            <select name="model"
                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400">
                <option value="">All models</option>
                <option value="Pbmc" @selected(str_contains(request('model', ''), 'Pbmc') && !str_contains(request('model', ''), 'Report'))>Pbmc</option>
                <option value="User" @selected(str_contains(request('model', ''), 'User'))>User</option>
                <option value="Iavic114PbmcReport" @selected(str_contains(request('model', ''), 'Iavic114'))>IAVIC114 Report</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit"
                class="px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors">
                Filter
            </button>
            <a href="{{ route('admin.audit-logs') }}"
                class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Reset
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'dir' => ($sort === 'created_at' && $dir === 'asc') ? 'desc' : 'asc']) }}"
                               class="flex items-center gap-1 hover:text-orange-600">
                                Time
                                @if ($sort === 'created_at') <span class="text-orange-500">{{ $dir === 'asc' ? '↑' : '↓' }}</span> @endif
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'user_name', 'dir' => ($sort === 'user_name' && $dir === 'asc') ? 'desc' : 'asc']) }}"
                               class="flex items-center gap-1 hover:text-orange-600">
                                User
                                @if ($sort === 'user_name') <span class="text-orange-500">{{ $dir === 'asc' ? '↑' : '↓' }}</span> @endif
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'event', 'dir' => ($sort === 'event' && $dir === 'asc') ? 'desc' : 'asc']) }}"
                               class="flex items-center gap-1 hover:text-orange-600">
                                Event
                                @if ($sort === 'event') <span class="text-orange-500">{{ $dir === 'asc' ? '↑' : '↓' }}</span> @endif
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Subject</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Changes</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        @php
                            $eventColors = [
                                'created'          => 'bg-green-100 text-green-700',
                                'updated'          => 'bg-blue-100 text-blue-700',
                                'deleted'          => 'bg-red-100 text-red-700',
                                'restored'         => 'bg-purple-100 text-purple-700',
                                'login'            => 'bg-emerald-100 text-emerald-700',
                                'logout'           => 'bg-amber-100 text-amber-700',
                                'password_changed' => 'bg-yellow-100 text-yellow-700',
                                'sync_completed'   => 'bg-teal-100 text-teal-700',
                                'import_completed' => 'bg-indigo-100 text-indigo-700',
                                'user_created'     => 'bg-green-100 text-green-700',
                            ];
                            $color = $eventColors[$log->event] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $log->user_name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $color }}">
                                    {{ $log->event }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">
                                @if ($log->auditable_type)
                                    <span class="font-medium">{{ class_basename($log->auditable_type) }}</span>
                                    @if ($log->auditable_id)
                                        <span class="text-gray-400">#{{ $log->auditable_id }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">System</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 max-w-xs">
                                @if ($log->old_values || $log->new_values)
                                    <button
                                        onclick="toggleChanges({{ $log->id }})"
                                        class="text-orange-500 hover:underline text-xs">
                                        View diff
                                    </button>
                                    <div id="changes-{{ $log->id }}" class="hidden mt-2 space-y-1">
                                        @if ($log->old_values)
                                            <div class="text-red-600 bg-red-50 rounded p-1.5 font-mono text-xs">
                                                <strong>Before:</strong><br>
                                                @foreach ($log->old_values as $k => $v)
                                                    {{ $k }}: {{ is_array($v) ? json_encode($v) : $v }}<br>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if ($log->new_values)
                                            <div class="text-green-700 bg-green-50 rounded p-1.5 font-mono text-xs">
                                                <strong>After:</strong><br>
                                                @foreach ($log->new_values as $k => $v)
                                                    {{ $k }}: {{ is_array($v) ? json_encode($v) : $v }}<br>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state icon="clipboard" title="No audit log entries found." description="Actions on records will appear here automatically." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
function toggleChanges(id) {
    const el = document.getElementById('changes-' + id);
    el.classList.toggle('hidden');
}
</script>
@endpush

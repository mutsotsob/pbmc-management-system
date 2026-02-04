{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manage| Users')
@section('page-title', 'Manage Users')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

    {{-- Alerts --}}
    @if (session('success'))
        <div x-data="{ show:true }" x-init="setTimeout(()=>show=false,7000)" x-show="show" x-transition
             class="mb-5 rounded-lg bg-green-50 border border-green-200 p-4 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show:true }" x-init="setTimeout(()=>show=false,7000)" x-show="show" x-transition
             class="mb-5 rounded-lg bg-red-50 border border-red-200 p-4 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-800">System Users</h2>
            <p class="text-sm text-gray-500">
                Total: {{ $users->total() }}
            </p>
        </div>

        <div class="flex flex-col md:flex-row md:items-center gap-3">

            <!-- Search (server-side) -->
            <form method="GET" action="" class="flex items-center gap-2">
                <div class="relative">
                    <input
                        type="search"
                        name="q"
                        value="{{ $q ?? request('q') }}"
                        placeholder="Search name, email, dept..."
                        class="w-72 pl-10 pr-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-800 placeholder-gray-400 text-sm"
                    >
                    <i data-feather="search" class="w-4 h-4 absolute left-3 top-2.5 text-gray-400"></i>
                </div>

                {{-- Keep sort in search --}}
                <input type="hidden" name="sort" value="{{ $sort ?? request('sort','name') }}">
                <input type="hidden" name="dir" value="{{ $dir ?? request('dir','asc') }}">

                <button type="submit"
                        class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">
                    Search
                </button>

                @if(request('q'))
                    <a href="{{ route('admin.users.index', ['sort' => request('sort','name'), 'dir' => request('dir','asc')]) }}"
                       class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">
                        Clear
                    </a>
                @endif
            </form>

            <!-- Create User -->
            <a href="{{ url('/admin/users/create') }}"
               class="inline-flex items-center gap-2 bg-pbmc text-white px-4 py-2 rounded-lg shadow-sm hover:opacity-90 transition text-sm font-semibold">
                <i data-feather="user-plus" class="w-4 h-4"></i>
                Create User
            </a>
        </div>
    </div>

    {{-- Bulk actions (server-side) --}}
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="text-sm text-gray-600">
            Tip: Select users then use bulk actions.
        </div>

        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.users.bulk.enable') }}" id="bulkEnableForm">
                @csrf
                <button type="submit"
                        onclick="return confirmBulk('enable')"
                        class="px-3 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold">
                    Enable Selected
                </button>
            </form>

            <form method="POST" action="{{ route('admin.users.bulk.disable') }}" id="bulkDisableForm">
                @csrf
                @method('PATCH')
                <button type="submit"
                        onclick="return confirmBulk('disable')"
                        class="px-3 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold">
                    Disable Selected
                </button>
            </form>
        </div>
    </div>

    <form id="usersForm">
        <div class="overflow-x-auto border border-gray-100 rounded-lg">
            <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3">
                            <input type="checkbox" id="selectAll">
                        </th>

                        {{-- Sortable headers (server-side) --}}
                        @php
                            function sortLink($label, $field) {
                                $currentSort = request('sort','name');
                                $currentDir = request('dir','asc');
                                $dir = ($currentSort === $field && $currentDir === 'asc') ? 'desc' : 'asc';

                                $params = array_merge(request()->query(), ['sort' => $field, 'dir' => $dir]);
                                $url = url()->current() . '?' . http_build_query($params);

                                $arrow = '';
                                if ($currentSort === $field) {
                                    $arrow = $currentDir === 'asc' ? ' ↑' : ' ↓';
                                }

                                return '<a class="hover:underline" href="'.$url.'">'.$label.$arrow.'</a>';
                            }
                        @endphp

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">{!! sortLink('Name','name') !!}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">{!! sortLink('Email','email') !!}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">{!! sortLink('Department','department') !!}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">{!! sortLink('Job Title','job_title') !!}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">{!! sortLink('Type','user_type') !!}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">{!! sortLink('Status','user_status') !!}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y bg-white">
                    @forelse ($users as $u)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="rowCheck" value="{{ $u->id }}">
                            </td>

                            <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $u->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $u->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $u->department ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $u->job_title ?? '—' }}</td>

                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    {{ $u->user_type === 'admin' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($u->user_type) }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    {{ $u->user_status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $u->user_status ? 'Active' : 'Disabled' }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">

                                    <a href="{{ url('/admin/users/'.$u->id) }}"
                                       class="p-2 rounded hover:bg-gray-100 text-gray-600" title="View">
                                        <i data-feather="eye" class="w-4 h-4"></i>
                                    </a>

                                    <a href="{{ url('/admin/users/'.$u->id.'/edit') }}"
                                       class="p-2 rounded hover:bg-gray-100 text-blue-600" title="Edit">
                                        <i data-feather="edit" class="w-4 h-4"></i>
                                    </a>

                                    <form method="POST" action="{{ url('/admin/users/'.$u->id.'/toggle-status') }}"
                                          onsubmit="return confirm('Are you sure you want to {{ $u->user_status ? 'disable' : 'enable' }} this user?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="p-2 rounded hover:bg-gray-100 {{ $u->user_status ? 'text-red-600' : 'text-green-600' }}"
                                                title="{{ $u->user_status ? 'Disable' : 'Enable' }}">
                                            <i data-feather="{{ $u->user_status ? 'slash' : 'check-circle' }}" class="w-4 h-4"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.feather) feather.replace({ 'aria-hidden': 'true' });

    const selectAll = document.getElementById('selectAll');
    const checks = () => Array.from(document.querySelectorAll('.rowCheck'));

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checks().forEach(cb => cb.checked = selectAll.checked);
        });
    }

    function selectedIds() {
        return checks().filter(cb => cb.checked).map(cb => cb.value);
    }

    window.confirmBulk = function(action) {
        const ids = selectedIds();
        if (ids.length === 0) {
            alert('Please select at least one user.');
            return false;
        }

        const verb = action === 'enable' ? 'enable' : 'disable';
        if (!confirm(`Are you sure you want to ${verb} ${ids.length} selected user(s)?`)) return false;

        // inject ids[] into the correct form
        const form = document.getElementById(action === 'enable' ? 'bulkEnableForm' : 'bulkDisableForm');

        // remove old hidden inputs
        form.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());

        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'ids[]';
            inp.value = id;
            form.appendChild(inp);
        });

        return true;
    }
});
</script>
@endsection

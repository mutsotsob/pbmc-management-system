<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PBMC | Dashboard</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { pbmc: '#f97316' }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100 text-gray-800">

@php
    $user = Auth::user();
    $isAdmin = $user && strtolower(trim($user->user_type ?? '')) === 'admin';

    /* ---------- SORT HELPER ---------- */
    function sort_link($label, $column, $sort, $dir) {
        $newDir = ($sort === $column && $dir === 'asc') ? 'desc' : 'asc';
        $arrow  = $sort === $column ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
        return '<a href="'.request()->fullUrlWithQuery([
            'sort' => $column,
            'dir'  => $newDir,
        ]).'" class="hover:text-orange-600">'.$label.$arrow.'</a>';
    }
@endphp

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-56 bg-white border-r p-4 fixed left-0 top-0 h-screen z-30">
        <div class="mb-6 text-center">
            <img src="{{ asset('idrl.png') }}"
                 class="w-36 h-20 mx-auto rounded-lg border object-contain p-2 bg-white">
            <p class="mt-3 text-xs tracking-widest text-gray-600 uppercase">
                PBMC Processing Portal
            </p>
        </div>

        <ul class="space-y-1">
            <li>
                <a href="{{ route('dashboard') }}"
                   class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
                    <i data-feather="home" class="w-5 h-5 mr-2"></i>
                    Home
                </a>
            </li>

            @if ($isAdmin)
                <li class="pt-2 text-xs font-semibold text-gray-500 uppercase px-3">Admin</li>

                 <li>
    <a href="{{ route('analytics.index') }}"
       class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 transition-colors duration-200 text-gray-700">
        <i data-feather="bar-chart-2" class="w-5 h-5 mr-2 text-gray-500"></i>
        Analytics
    </a>
</li>

                <li>
                    <a href="{{ route('admin.users') }}"
                       class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
                        <i data-feather="users" class="w-5 h-5 mr-2"></i>
                        Manage Users
                    </a>
                </li>
            @endif

            <li class="pt-2">
                <a href="{{ url('/settings') }}"
                   class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
                    <i data-feather="settings" class="w-5 h-5 mr-2"></i>
                    My Profile
                </a>
            </li>

            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full flex items-center px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                        <i data-feather="log-out" class="w-5 h-5 mr-2"></i>
                        Logout
                    </button>
                </form>
            </li>
        </ul>
    </aside>

    <!-- Main -->
    <div class="flex-1 ml-56">

        <!-- Top Nav -->
        <nav class="bg-white border-b fixed top-0 left-56 right-0 z-20">
            <div class="px-6 py-4 flex justify-between items-center">
                <h1 class="text-xl font-bold">PBMC Dashboard</h1>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-pbmc rounded-full flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-semibold">{{ $user->name }}</span>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <main class="pt-24 px-6 pb-6">

            <div class="bg-white rounded-xl border p-6">

                <h2 class="text-lg font-bold text-orange-600 mb-1">
                    Welcome, {{ $user->name }} 👋
                </h2>

                <p class="text-gray-600 mb-6">
                    Track PBMC processes, all in one place.
                </p>

                <div class="border rounded-xl overflow-hidden">

                    <!-- Header -->
                    <div class="px-5 py-4 bg-gray-50 border-b flex justify-between items-center">
                        <h3 class="font-semibold">PBMC Records</h3>

                        <a href="{{ route('pbmc.create') }}"
                           class="inline-flex items-center gap-2 bg-green-600 text-white
                                  px-4 py-2 rounded-lg text-sm hover:bg-green-700">
                            <i data-feather="plus" class="w-4 h-4"></i>
                            Add Record
                        </a>
                    </div>

                    <div class="p-6">

                        <!-- Bulk Actions -->
                        <div id="bulkActionsBar"
                             class="hidden mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
                            <span class="text-sm font-medium text-blue-900">
                                <span id="selectedCount">0</span> record(s) selected
                            </span>
                            <div class="flex items-center gap-2">
                                <button onclick="exportSelected()"
                                        class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-700">
                                    <i data-feather="download" class="w-4 h-4"></i>
                                    Export Selected
                                </button>
                                <button onclick="clearSelection()"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <!-- Export All -->
                        <div class="mb-4 flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                Showing {{ $pbmcs->firstItem() }} to {{ $pbmcs->lastItem() }} of {{ $pbmcs->total() }} records
                            </div>
                            <a href="{{ route('pbmcs.export') }}"
                               class="inline-flex items-center gap-2 bg-gray-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700">
                                <i data-feather="download" class="w-4 h-4"></i>
                                Export All to CSV
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <form id="exportForm" method="POST" action="{{ route('pbmcs.export.selected') }}">
                                @csrf
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr class="text-left text-xs font-semibold text-gray-600 uppercase">
                                            <th class="px-4 py-3">
                                                <input type="checkbox" id="selectAll"
                                                       class="rounded border-gray-300 text-pbmc focus:ring-pbmc"
                                                       onchange="toggleAll(this)">
                                            </th>
                                            <th class="px-4 py-3">{!! sort_link('PTID', 'ptid', $sort, $dir) !!}</th>
                                            <th class="px-4 py-3">{!! sort_link('Collection Date', 'collection_date', $sort, $dir) !!}</th>
                                            <th class="px-4 py-3">{!! sort_link('Viability', 'viability_percent', $sort, $dir) !!}</th>
                                            <!-- <th class="px-4 py-3">{!! sort_link('Source', 'imported_from_acrn', $sort, $dir) !!}</th> -->
                                            <th class="px-4 py-3 text-right">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y bg-white">
                                        @foreach ($pbmcs as $pbmc)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3">
                                                    <input type="checkbox" name="selected_ids[]"
                                                           class="row-checkbox rounded border-gray-300 text-pbmc focus:ring-pbmc"
                                                           onchange="updateSelection()">
                                                </td>
                                                <td class="px-4 py-3">{{ $pbmc->ptid }}</td>
                                                <td class="px-4 py-3">{{ $pbmc->collection_date?->format('d M Y') }}</td>
                                                <td class="px-4 py-3">
                                                    @php $v = $pbmc->viability_percent ?? $pbmc->auto_viability_percent; @endphp
                                                    @if($v !== null)
                                                        <span class="px-2 py-1 text-xs rounded-full
                                                            {{ $v >= 80 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                            {{ number_format($v,1) }}%
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400 text-xs">N/A</span>
                                                    @endif
                                                </td>
                                                <!-- <td class="px-4 py-3">
                                                    {{ ($pbmc->imported_from_acrn ?? false) ? 'ACRN' : 'Manual' }}
                                                </td> -->

                                                <!-- ACTIONS -->
                                                <td class="px-4 py-3 text-right">
                                                    <div class="inline-flex items-center gap-3 justify-end">
                                                        <a href="{{ route('pbmc.show', $pbmc) }}"
                                                           class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium">
                                                            <i data-feather="eye" class="w-4 h-4"></i>
                                                            View
                                                        </a>

                                                        <a href="{{ route('pbmc.edit', $pbmc) }}"
                                                           class="inline-flex items-center gap-1 text-amber-600 hover:text-amber-800 font-medium">
                                                            <i data-feather="edit" class="w-4 h-4"></i>
                                                            Edit
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </form>
                        </div>

                        <div class="mt-6">
                            {{ $pbmcs->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://unpkg.com/feather-icons"></script>
<script>
    feather.replace();

    function toggleAll(checkbox) {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = checkbox.checked);
        updateSelection();
    }

    function updateSelection() {
        const checked = document.querySelectorAll('.row-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = checked;
        document.getElementById('bulkActionsBar').classList.toggle('hidden', checked === 0);

        const all = document.querySelectorAll('.row-checkbox').length;
        const selectAll = document.getElementById('selectAll');
        selectAll.checked = checked === all && checked > 0;
        selectAll.indeterminate = checked > 0 && checked < all;
    }

    function clearSelection() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        updateSelection();
    }

    function exportSelected() {
        if (!document.querySelectorAll('.row-checkbox:checked').length) {
            alert('Please select at least one record');
            return;
        }
        document.getElementById('exportForm').submit();
    }
</script>

</body>
</html>

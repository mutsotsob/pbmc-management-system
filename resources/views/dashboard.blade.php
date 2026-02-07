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
                    Dashboard
                </a>
            </li>

            @if ($isAdmin)
                <li class="pt-2 text-xs font-semibold text-gray-500 uppercase px-3">Admin</li>

                <li>
                    <a href="{{ route('admin.users') }}"
                       class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
                        <i data-feather="users" class="w-5 h-5 mr-2"></i>
                        Manage Users
                    </a>
                </li>

                <li>
                    <a href="#"
                       class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
                        <i data-feather="bar-chart-2" class="w-5 h-5 mr-2"></i>
                        Basic Analysis
                    </a>
                </li>
            @endif

            <li class="pt-2">
                <a href="{{ url('/settings') }}"
                   class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100">
                    <i data-feather="settings" class="w-5 h-5 mr-2"></i>
                    Settings
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

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-start justify-between">
                    <div class="flex items-start gap-2">
                        <i data-feather="check-circle" class="w-5 h-5 mt-0.5 text-green-600"></i>
                        <div>
                            <p class="font-semibold">Success!</p>
                            <p class="text-sm">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                        <i data-feather="x" class="w-5 h-5"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-start justify-between">
                    <div class="flex items-start gap-2">
                        <i data-feather="alert-circle" class="w-5 h-5 mt-0.5 text-red-600"></i>
                        <div>
                            <p class="font-semibold">Error!</p>
                            <p class="text-sm">{{ session('error') }}</p>
                        </div>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">
                        <i data-feather="x" class="w-5 h-5"></i>
                    </button>
                </div>
            @endif

            <div class="bg-white rounded-xl border p-6">

                <h2 class="text-lg font-bold text-orange-600 mb-1">
                    Welcome, {{ $user->name }} 👋
                </h2>

                <p class="text-gray-600 mb-6">
                    Track PBMC processes, all in one place.
                </p>

                <div class="border rounded-xl overflow-hidden">

                    <!-- HEADER WITH CREATE & SYNC BUTTONS -->
                    <div class="px-5 py-4 bg-gray-50 border-b flex justify-between items-center">
                        <h3 class="font-semibold">PBMC Records</h3>

                        <div class="flex items-center gap-3">
                            <!-- Sync Button (Admin Only) -->
                            @if ($isAdmin)
                                <form action="{{ route('pbmcs.sync') }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to sync data from ACRN database? This may take a few minutes.');"
                                      class="inline-block">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 bg-blue-600 text-white
                                                   px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
                                        <i data-feather="refresh-cw" class="w-4 h-4"></i>
                                        Sync from ACRN
                                    </button>
                                </form>
                            @endif

                            <!-- Add Record Button -->
                            <a href="{{ route('pbmc.create') }}"
                               class="inline-flex items-center gap-2 bg-green-600 text-white
                                      px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">
                                <i data-feather="plus" class="w-4 h-4"></i>
                                Add Record
                            </a>
                        </div>
                    </div>

                    <div class="p-6">

                        @if ($pbmcs->isEmpty())
                            <div class="text-center py-10 text-gray-600">
                                <i data-feather="inbox" class="w-12 h-12 mx-auto mb-3 text-gray-400"></i>
                                <p class="text-lg font-semibold mb-1">No PBMC records found</p>
                                <p class="text-sm">Get started by adding a new record or syncing from ACRN database.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr class="text-left text-xs font-semibold text-gray-600 uppercase">
                                            <th class="px-4 py-3">Study</th>
                                            <th class="px-4 py-3">PTID</th>
                                            <th class="px-4 py-3">Visit</th>
                                            <th class="px-4 py-3">Collection Date</th>
                                            <th class="px-4 py-3">Viability</th>
                                            <th class="px-4 py-3">Source</th>
                                            <th class="px-4 py-3 text-right">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y bg-white">
                                        @foreach ($pbmcs as $pbmc)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 font-medium">
                                                    {{ $pbmc->study_choice === 'Other'
                                                        ? $pbmc->other_study_name
                                                        : $pbmc->study_choice }}
                                                </td>
                                                <td class="px-4 py-3">{{ $pbmc->ptid }}</td>
                                                <td class="px-4 py-3">{{ $pbmc->visit }}</td>
                                                <td class="px-4 py-3">
                                                    {{ $pbmc->collection_date?->format('d M Y') }}
                                                </td>
                                                <td class="px-4 py-3">
                                                    @php
                                                        $v = $pbmc->viability_percent ?? $pbmc->auto_viability_percent;
                                                    @endphp
                                                    @if($v !== null)
                                                        <span class="px-2 py-1 text-xs rounded-full
                                                            {{ $v >= 80 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                            {{ number_format($v, 1) }}%
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400 text-xs">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($pbmc->imported_from_acrn ?? false)
                                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                                                            <i data-feather="database" class="w-3 h-3"></i>
                                                            ACRN
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">
                                                            <i data-feather="edit" class="w-3 h-3"></i>
                                                            Manual
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <a href="{{ route('pbmc.show', $pbmc) }}"
                                                       class="text-pbmc hover:underline font-medium">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination if needed -->
                            @if(method_exists($pbmcs, 'links'))
                                <div class="mt-4">
                                    {{ $pbmcs->links() }}
                                </div>
                            @endif
                        @endif

                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script src="https://unpkg.com/feather-icons"></script>
<script>
    feather.replace();
    
    // Auto-hide alerts after 10 seconds
    setTimeout(() => {
        document.querySelectorAll('.bg-green-50, .bg-red-50').forEach(el => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        });
    }, 10000);
</script>

</body>
</html>
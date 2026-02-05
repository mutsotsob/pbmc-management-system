<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <title>@yield('title', 'PBMC | Dashboard')</title>


    <!-- PBMC Branding -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.png') }}">

    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { pbmc: '#f97316' } }
            }
        }
    </script>
</head>

<body class="bg-gray-100 text-gray-800">

@php
    // ✅ Always compute once, and use everywhere
    $user = auth()->user();
    $isAdmin = $user && strtolower(trim((string) $user->user_type)) === 'admin';
@endphp

<div class="flex min-h-screen">

    <!-- Sidebar (WHITE THEME) -->
    <aside class="w-56 bg-white text-gray-800 border-r border-gray-200 p-4 flex flex-col items-center fixed left-0 top-0 h-screen overflow-y-auto z-30">

        <!-- Logo + Org -->
        <div class="mb-6 text-center w-full">
            <img src="{{ asset('idrl.png') }}"
                 alt="PBMC Logo"
                 class="w-36 h-20 mx-auto rounded-lg shadow-sm border border-gray-200 object-contain bg-white p-2">
            <p class="mt-3 text-xs tracking-widest text-gray-600 uppercase">PBMC Processing Portal</p>
        </div>

        <ul class="w-full space-y-1">

            <li>
                <a href="{{ route('dashboard') }}"
                   class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 transition-colors duration-200 text-gray-700">
                    <i data-feather="home" class="w-5 h-5 mr-2 text-gray-500"></i>
                    Dashboard
                </a>
            </li>

            <li>
                <a href="{{ route('pbmc.index') }}"
                   class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 transition-colors duration-200 text-gray-700">
                    <i data-feather="file-text" class="w-5 h-5 mr-2 text-gray-500"></i>
                    PBMC Processes
                </a>
            </li>

            {{-- ✅ ADMIN ONLY: always show if admin, never show if not --}}
            @if ($isAdmin)
                <li class="pt-2">
                    <p class="px-3 text-[11px] font-semibold text-gray-500 uppercase tracking-widest">
                        Admin
                    </p>
                </li>

                <li>
                    <a href="{{ url('/admin/users') }}"
                       class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 transition-colors duration-200 text-gray-700">
                        <i data-feather="users" class="w-5 h-5 mr-2 text-gray-500"></i>
                        Manage Users
                    </a>
                </li>
<!-- 
                <li>
                    <a href="{{ url('/admin/processed-samples') }}"
                       class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 transition-colors duration-200 text-gray-700">
                        <i data-feather="check-square" class="w-5 h-5 mr-2 text-gray-500"></i>
                        Processed Samples
                    </a>
                </li> -->

                <li>
                    <a href="{{ url('/admin/basic-analysis') }}"
                       class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 transition-colors duration-200 text-gray-700">
                        <i data-feather="bar-chart-2" class="w-5 h-5 mr-2 text-gray-500"></i>
                        Basic Analysis
                    </a>
                </li>
            @endif

            <!-- Settings -->
            <li class="pt-2">
                <a href="{{ url('/settings') }}"
                   class="w-full flex items-center py-2 px-3 rounded-lg hover:bg-gray-100 transition-colors duration-200 text-gray-700">
                    <i data-feather="settings" class="w-5 h-5 mr-2 text-gray-500"></i>
                    Settings
                </a>
            </li>

            <!-- Logout (Laravel safe) -->
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center py-2 px-3 rounded-lg hover:bg-red-50 transition-colors duration-200 text-left text-red-600">
                        <i data-feather="log-out" class="w-5 h-5 mr-2 text-red-500"></i>
                        Logout
                    </button>
                </form>
            </li>
        </ul>

    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 ml-56 flex flex-col">

        <!-- Top Navigation Bar (WHITE THEME) -->
        <nav class="bg-white shadow-sm border-b border-gray-200 fixed top-0 right-0 left-56 z-20">
            <div class="px-6 py-3">
                <div class="flex items-center justify-between">

                    <!-- Page Title -->
                    <div class="flex items-center gap-3">
                        <div class="w-2.5 h-2.5 rounded-full bg-pbmc"></div>
                        <h1 class="text-xl font-bold text-gray-800">
                            PBMC Dashboard
                        </h1>
                    </div>

                    <div class="flex items-center gap-4">

                        <!-- Search -->
                        <!-- <div class="hidden md:block">
                            <div class="relative">
                                <input type="text"
                                       placeholder="Search PBMC records..."
                                       class="w-72 pl-10 pr-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm">
                                <i data-feather="search" class="w-4 h-4 absolute left-3 top-2.5 text-gray-400"></i>
                            </div>
                        </div> -->

                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors relative">
                                <i data-feather="bell" class="w-5 h-5 text-gray-600"></i>
                                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>
                        </div>

                        <!-- User Menu -->
                        <div class="relative" id="userMenuWrap">
                            <button id="userMenuButton"
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">

                                <!-- Avatar Initial -->
                                <div class="w-8 h-8 rounded-full bg-pbmc flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                </div>

                                <!-- User Name + Department -->
                                <div class="hidden md:block text-left">
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ $user->name ?? 'User' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $user->department ?? 'PBMC Department' }}
                                    </p>
                                </div>

                                <i data-feather="chevron-down" class="w-4 h-4 text-gray-600"></i>
                            </button>

                            <!-- Dropdown -->
                            <div id="userDropdown"
                                 class="hidden absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-gray-200 py-2">

                                <!-- <a href="#"
                                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i data-feather="user" class="w-4 h-4 text-gray-500"></i>
                                    My Profile
                                </a> -->

                                <a href="{{ url('/settings') }}"
                                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i data-feather="settings" class="w-4 h-4 text-gray-500"></i>
                                    Settings
                                </a>

                                <hr class="my-2 border-gray-200">

                                <!-- Logout -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 text-left">
                                        <i data-feather="log-out" class="w-4 h-4 text-red-500"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 px-6 py-6 overflow-auto mt-20">
            @hasSection('content')
                @yield('content')
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-orange-600 mb-2">
                        Welcome, {{ $user->name ?? 'User' }} 👋
                    </h2>
                    <p class="text-gray-600">
                        Track PBMC processes, compliance status, risks, and audit logs — all in one place.
                    </p>
                </div>
            @endif
        </main>
    </div>

</div>

<!-- Feather Icons -->
<script src="https://unpkg.com/feather-icons"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        feather.replace({ 'aria-hidden': 'true' });

        const btn = document.getElementById('userMenuButton');
        const menu = document.getElementById('userDropdown');

        if (btn && menu) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                const wrap = document.getElementById('userMenuWrap');
                if (wrap && !wrap.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            });
        }
    });
</script>

</body>
</html>

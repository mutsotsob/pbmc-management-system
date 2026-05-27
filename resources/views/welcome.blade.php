<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'PBMC | Dashboard')</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pbmc: '#f97316'
                    }
                }
            }
        }
    </script>

    @stack('styles')
</head>

<body class="bg-gray-100 text-gray-800">

    <div class="flex min-h-screen">

        {{-- ─────────────────────────────────────────
             SIDEBAR
             All navigation lives here, never in views.
        ───────────────────────────────────────── --}}
        <aside
            class="w-56 bg-white text-gray-800 border-r border-gray-200 p-4 flex flex-col
                       fixed left-0 top-0 h-screen overflow-y-auto z-30">

            {{-- Logo --}}
            <div class="mb-6 text-center w-full">
                <img src="{{ asset('idrl.png') }}" alt="PBMC Logo"
                    class="w-36 h-20 mx-auto rounded-lg border border-gray-200 object-contain bg-white p-2">
                <p class="mt-3 text-xs tracking-widest text-gray-600 uppercase">
                    Samples Management System
                </p>
            </div>

            {{-- Nav links --}}
            <ul class="w-full space-y-1 flex-1">

                <li>
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100
                              transition-colors duration-200 text-gray-700
                              {{ request()->routeIs('dashboard') ? 'bg-orange-50 text-pbmc font-semibold' : '' }}">
                        <i data-feather="home" class="w-5 h-5 mr-2 text-gray-500"></i>
                        Home
                    </a>
                </li>

                <li>
                    <a href="{{ route('analytics.index') }}"
                        class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100
                              transition-colors duration-200 text-gray-700
                              {{ request()->routeIs('analytics.*') ? 'bg-orange-50 text-pbmc font-semibold' : '' }}">
                        <i data-feather="bar-chart-2" class="w-5 h-5 mr-2 text-gray-500"></i>
                        Analytics
                    </a>
                </li>

                {{-- Admin-only links --}}
                @if (auth()->user()?->isAdmin())
                    <li>
                        <a href="{{ route('admin.users') }}"
                            class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100
                                  transition-colors duration-200 text-gray-700
                                  {{ request()->routeIs('admin.users*') ? 'bg-orange-50 text-pbmc font-semibold' : '' }}">
                            <i data-feather="users" class="w-5 h-5 mr-2 text-gray-500"></i>
                            Manage Users
                        </a>
                    </li>
                @endif

                <li class="pt-2">
                    <a href="{{ url('/settings') }}"
                        class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100
                              transition-colors duration-200 text-gray-700">
                        <i data-feather="settings" class="w-5 h-5 mr-2 text-gray-500"></i>
                        My Profile
                    </a>
                </li>

            </ul>

            {{-- Logout pinned to bottom --}}
            <div class="mt-auto pt-4 border-t border-gray-100">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center py-2 px-3 rounded-lg hover:bg-red-50
                               transition-colors duration-200 text-red-600">
                        <i data-feather="log-out" class="w-5 h-5 mr-2 text-red-500"></i>
                        Logout
                    </button>
                </form>
            </div>

        </aside>

        {{-- ─────────────────────────────────────────
             MAIN COLUMN
        ───────────────────────────────────────── --}}
        <div class="flex-1 ml-56 flex flex-col">

            {{-- TOP NAV --}}
            <nav class="bg-white shadow-sm border-b border-gray-200 fixed top-0 right-0 left-56 z-20">
                <div class="px-6 py-3 flex items-center justify-between">

                    {{-- Page title slot — views can override via @section('topnav-title') --}}
                    <div class="flex items-center gap-3">
                        <div class="w-2.5 h-2.5 rounded-full bg-pbmc"></div>
                        <h1 class="text-xl font-bold text-gray-800">
                            @yield('topnav-title', 'PBMC Dashboard')
                        </h1>
                    </div>

                    <div class="flex items-center gap-4">

                        {{-- Notification bell --}}
                        <div class="relative">
                            <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors relative">
                                <i data-feather="bell" class="w-5 h-5 text-gray-600"></i>
                                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>
                        </div>

                        {{-- User dropdown --}}
                        <div class="relative" id="userMenuWrap">
                            <button id="userMenuButton"
                                class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div
                                    class="w-8 h-8 rounded-full bg-pbmc flex items-center justify-center
                                            text-white font-semibold text-sm">
                                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="hidden md:block text-left">
                                    <p class="text-sm font-semibold text-gray-800 leading-tight">
                                        {{ auth()->user()?->name ?? 'User' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ auth()->user()?->department ?? 'PBMC Department' }}
                                    </p>
                                </div>
                                <i data-feather="chevron-down" class="w-4 h-4 text-gray-600"></i>
                            </button>

                            <div id="userDropdown"
                                class="hidden absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg
                                        border border-gray-200 py-2 z-50">
                                <a href="{{ url('/settings') }}"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i data-feather="settings" class="w-4 h-4 text-gray-500"></i>
                                    Settings
                                </a>
                                <hr class="my-2 border-gray-200">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-2 px-4 py-2 text-sm
                                               text-red-600 hover:bg-red-50 text-left">
                                        <i data-feather="log-out" class="w-4 h-4 text-red-500"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </nav>

            {{-- PAGE CONTENT --}}
            <main class="flex-1 px-6 py-6 overflow-auto mt-16">
                @yield('content')
            </main>

        </div>
    </div>

    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace({
                'aria-hidden': 'true'
            });

            const btn = document.getElementById('userMenuButton');
            const menu = document.getElementById('userDropdown');
            const wrap = document.getElementById('userMenuWrap');

            if (btn && menu) {
                btn.addEventListener('click', e => {
                    e.stopPropagation();
                    menu.classList.toggle('hidden');
                });
                document.addEventListener('click', e => {
                    if (wrap && !wrap.contains(e.target)) {
                        menu.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    @stack('scripts')

</body>

</html>

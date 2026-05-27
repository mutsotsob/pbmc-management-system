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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>

<body class="bg-gray-100 text-gray-800">

    <div x-data="{ sidebarOpen: false }" class="flex min-h-screen">

        {{-- Mobile backdrop --}}
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/40 z-20 lg:hidden"></div>

        {{-- ─────────────────────────────────────────
             SIDEBAR
             All navigation lives here, never in views.
        ───────────────────────────────────────── --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="w-56 bg-white text-gray-800 border-r border-gray-200 p-4 flex flex-col
                       fixed left-0 top-0 h-screen overflow-y-auto z-30
                       transition-transform duration-200 ease-in-out">

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

                @php
                    $user = auth()->user();
                    $isClinicalOperations = strcasecmp(trim((string) $user?->department), 'Clinical Operations') === 0;
                    $isAdministration = strcasecmp(trim((string) $user?->department), 'Administration') === 0;
                    $isLaboratory = strcasecmp(trim((string) $user?->department), 'Laboratory') === 0;
                    $hasFullSystemAccess = $user?->hasFullSystemAccess() ?? false;
                    $hasFullAccessDepartment = $user?->hasFullAccessDepartment() ?? false;
                    $hasScopedNavigation = ($isClinicalOperations || $isAdministration || $isLaboratory) && !$hasFullAccessDepartment;
                @endphp

                @if ($isAdministration && !$hasFullAccessDepartment)
                    <li>
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100
                              transition-colors duration-200 text-gray-700
                              {{ request()->routeIs('dashboard') ? 'bg-orange-50 text-pbmc font-semibold' : '' }}">
                            <i data-feather="bar-chart-2" class="w-5 h-5 mr-2 text-gray-500"></i>
                            Transport Metrics
                        </a>
                    </li>
                @endif

                @if (!$hasScopedNavigation || $isLaboratory)
                    <li>
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100
                              transition-colors duration-200 text-gray-700
                              {{ request()->routeIs('dashboard') ? 'bg-orange-50 text-pbmc font-semibold' : '' }}">
                            <i data-feather="home" class="w-5 h-5 mr-2 text-gray-500"></i>
                            Home
                        </a>
                    </li>
                @endif

                @if ($hasFullAccessDepartment)
                    <li>
                        <a href="{{ route('analytics.index') }}"
                            class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100
                              transition-colors duration-200 text-gray-700
                              {{ request()->routeIs('analytics.*') ? 'bg-orange-50 text-pbmc font-semibold' : '' }}">
                            <i data-feather="bar-chart-2" class="w-5 h-5 mr-2 text-gray-500"></i>
                            Overview
                        </a>
                    </li>
                @endif

                @if ($isClinicalOperations || $hasFullAccessDepartment)
                    <li>
                        <a href="{{ route('sample-dispatches.index') }}"
                            class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100
                                  transition-colors duration-200 text-gray-700
                                  {{ request()->routeIs('sample-dispatches.*') ? 'bg-orange-50 text-pbmc font-semibold' : '' }}">
                            <i data-feather="send" class="w-5 h-5 mr-2 text-gray-500"></i>
                            Sample Dispatch
                        </a>
                    </li>
                @endif

                {{-- Admin-only links --}}
                @if ((!$hasScopedNavigation || $hasFullAccessDepartment) && $hasFullSystemAccess)
                    <li>
                        <a href="{{ route('admin.users') }}"
                            class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100
                                  transition-colors duration-200 text-gray-700
                                  {{ request()->routeIs('admin.users*') ? 'bg-orange-50 text-pbmc font-semibold' : '' }}">
                            <i data-feather="users" class="w-5 h-5 mr-2 text-gray-500"></i>
                            Users
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.audit-logs') }}"
                            class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100
                                  transition-colors duration-200 text-gray-700
                                  {{ request()->routeIs('admin.audit-logs') ? 'bg-orange-50 text-pbmc font-semibold' : '' }}">
                            <i data-feather="clipboard" class="w-5 h-5 mr-2 text-gray-500"></i>
                            Audit Log
                        </a>
                    </li>
                @endif

                <li class="pt-2">
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center py-2 px-3 rounded-lg hover:bg-gray-100
                              transition-colors duration-200 text-gray-700
                              {{ request()->routeIs('profile.edit') ? 'bg-orange-50 text-pbmc font-semibold' : '' }}">
                        <i data-feather="user" class="w-5 h-5 mr-2 text-gray-500"></i>
                        Profile
                    </a>
                </li>

                <li class="pt-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center py-2 px-3 rounded-lg hover:bg-red-50
                                  transition-colors duration-200 text-red-600">
                            <i data-feather="log-out" class="w-5 h-5 mr-2 text-red-500"></i>
                            Logout
                        </button>
                    </form>
                </li>

            </ul>

        </aside>

        {{-- ─────────────────────────────────────────
             MAIN COLUMN
        ───────────────────────────────────────── --}}
        <div class="flex-1 lg:ml-56 flex flex-col">

            {{-- TOP NAV --}}
            <nav class="bg-white shadow-sm border-b border-gray-200 fixed top-0 right-0 left-0 lg:left-56 z-20">
                <div class="px-4 lg:px-6 py-3 flex items-center justify-between">

                    {{-- Hamburger (mobile only) + page title --}}
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
                            aria-label="Toggle sidebar">
                            <i data-feather="menu" class="w-5 h-5 text-gray-600"></i>
                        </button>
                        <div class="w-2.5 h-2.5 rounded-full bg-pbmc hidden lg:block"></div>
                        <h1 class="text-xl font-bold text-gray-800">
                            @yield('topnav-title', 'PBMC Dashboard')
                        </h1>
                    </div>

                    <div class="flex items-center gap-4">

                        @if (!$isAdministration && !$isLaboratory)
                            {{-- Notification bell --}}
                            @php $unreadCount = auth()->user()?->unreadNotifications->count() ?? 0; @endphp
                            <div class="relative" id="notifWrap">
                            <button id="notifButton"
                                class="p-2 rounded-lg hover:bg-gray-100 transition-colors relative">
                                <i data-feather="bell" class="w-5 h-5 text-gray-600"></i>
                                @if ($unreadCount > 0)
                                    <span
                                        class="absolute top-1 right-1 min-w-[1rem] h-4 px-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                    </span>
                                @endif
                            </button>

                            <div id="notifDropdown"
                                class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-800">Notifications</span>
                                    @if ($unreadCount > 0)
                                        <form method="POST" action="{{ route('notifications.mark-read') }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-orange-500 hover:underline">
                                                Mark all read
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <div class="divide-y divide-gray-100 max-h-72 overflow-y-auto">
                                    @forelse (auth()->user()?->notifications->take(5) ?? [] as $notif)
                                        @php
                                            $d = $notif->data;
                                            $isUnread = is_null($notif->read_at);
                                        @endphp
                                        <div class="px-4 py-3 {{ $isUnread ? 'bg-orange-50/40' : '' }}">
                                            <div class="flex items-center gap-2">
                                                <p class="text-xs font-semibold text-gray-800 flex-1">
                                                    {{ $d['title'] ?? 'Notification' }}</p>
                                                @if ($isUnread)
                                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $d['message'] ?? '' }}
                                            </p>
                                            <p class="text-[10px] text-gray-400 mt-1">
                                                {{ $notif->created_at->diffForHumans() }}</p>
                                        </div>
                                    @empty
                                        <div class="px-4 py-6 text-center text-gray-400 text-xs">
                                            No notifications yet.
                                        </div>
                                    @endforelse
                                </div>
                                <div class="px-4 py-2 border-t border-gray-100 text-center">
                                    <a href="{{ route('notifications.index') }}"
                                        class="text-xs text-orange-500 hover:underline font-medium">
                                        View all notifications →
                                    </a>
                                </div>
                            </div>
                            </div>
                        @endif

                        {{-- User dropdown --}}
                        <div class="relative" id="userMenuWrap">
                            <button id="userMenuButton"
                                class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                @if (auth()->user()?->profile_photo_url)
                                    <img src="{{ auth()->user()->profile_photo_url }}"
                                        alt="{{ auth()->user()->name }} profile picture"
                                        class="h-8 w-8 rounded-full border border-gray-200 object-cover">
                                @else
                                    <div
                                        class="w-8 h-8 rounded-full bg-pbmc flex items-center justify-center
                                                text-white font-semibold text-sm">
                                        {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
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
                                <a href="{{ route('profile.edit') }}"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i data-feather="user" class="w-4 h-4 text-gray-500"></i>
                                    Profile
                                </a>
                                @if (!$isAdministration && !$isLaboratory)
                                    <a href="{{ url('/settings') }}"
                                        class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i data-feather="settings" class="w-4 h-4 text-gray-500"></i>
                                        Settings
                                    </a>
                                    <hr class="my-2 border-gray-200">
                                @endif
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
                <x-flash-alerts class="mb-4" />
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
                    document.getElementById('notifDropdown')?.classList.add('hidden');
                });
                document.addEventListener('click', e => {
                    if (wrap && !wrap.contains(e.target)) {
                        menu.classList.add('hidden');
                    }
                });
            }

            const notifBtn = document.getElementById('notifButton');
            const notifMenu = document.getElementById('notifDropdown');
            const notifWrap = document.getElementById('notifWrap');

            if (notifBtn && notifMenu) {
                notifBtn.addEventListener('click', e => {
                    e.stopPropagation();
                    notifMenu.classList.toggle('hidden');
                    document.getElementById('userDropdown')?.classList.add('hidden');
                });
                document.addEventListener('click', e => {
                    if (notifWrap && !notifWrap.contains(e.target)) {
                        notifMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    @stack('scripts')

</body>

</html>

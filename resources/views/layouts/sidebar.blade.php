{{-- Sidebar --}}
<aside data-sidebar class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col transition-transform duration-300 lg:translate-x-0 -translate-x-full fixed lg:relative h-screen z-30">
    
    {{-- Logo / Brand --}}
    <div class="h-16 border-b border-gray-200 dark:border-gray-700 flex items-center px-6">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6M10 3v6l-5 8a3 3 0 002.5 4.5h9a3 3 0 002.5-4.5l-5-8V3" />
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900 dark:text-white">IDRLPBMC</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">Lab Management</p>
            </div>
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-6 px-3">
        <div class="space-y-1">
            
            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>

            {{-- Samples Section --}}
            <div x-data="{ open: {{ request()->routeIs('samples.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('samples.*') ? 'bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6M10 3v6l-5 8a3 3 0 002.5 4.5h9a3 3 0 002.5-4.5l-5-8V3" />
                        </svg>
                        <span class="font-medium">Samples</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                
                <div x-show="open" x-collapse class="ml-8 mt-1 space-y-1">
                    <a href="" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('samples.index') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <span>All Samples</span>
                    </a>
                    <a href="" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('samples.create') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <span>Add New Sample</span>
                    </a>
                    <a href="" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('samples.pending') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <span>Pending Samples</span>
                        <span class="ml-auto bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400 text-xs px-2 py-0.5 rounded-full">12</span>
                    </a>
                </div>
            </div>

            {{-- Reports Section --}}
            <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('reports.*') ? 'bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="font-medium">Reports</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                
                <div x-show="open" x-collapse class="ml-8 mt-1 space-y-1">
                    <a href="" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('reports.index') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <span>All Reports</span>
                    </a>
                    <a href="" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('reports.pending') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <span>Pending Reports</span>
                    </a>
                    <a href="" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('reports.completed') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <span>Completed Reports</span>
                    </a>
                </div>
            </div>

            {{-- Separator --}}
            <div class="my-4 border-t border-gray-200 dark:border-gray-700"></div>

            {{-- Administration Section --}}
            <div class="px-3 mb-2">
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Administration</p>
            </div>

            {{-- Users --}}
            <a href="" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('users.*') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span class="font-medium">Users</span>
            </a>


        </div>
    </nav>

    {{-- User Info (Bottom) --}}
    <div class="border-t border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ Auth::user()->name ?? 'User Name' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->role ?? 'Lab Technician' }}</p>
            </div>
            <button class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Settings">
                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </div>
    </div>

</aside>

{{-- Mobile Sidebar Overlay --}}
<div data-sidebar-overlay class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-20 lg:hidden hidden" onclick="toggleSidebar()"></div>

<script>
    // Enhanced sidebar toggle with overlay
    function toggleSidebar() {
        const sidebar = document.querySelector('[data-sidebar]');
        const overlay = document.querySelector('[data-sidebar-overlay]');
        
        if (sidebar) {
            sidebar.classList.toggle('-translate-x-full');
            if (overlay) {
                overlay.classList.toggle('hidden');
            }
        }
    }

    // Close sidebar on mobile when clicking a link
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('[data-sidebar]');
        const links = sidebar?.querySelectorAll('a');
        
        links?.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    toggleSidebar();
                }
            });
        });
    });
</script>
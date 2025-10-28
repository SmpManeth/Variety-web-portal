<!-- Sidebar -->
<div x-data="{ open: false }" class="relative">
    <!-- Toggle (mobile only) -->
    <button @click="open = !open"
        class="md:hidden fixed top-4 left-4 z-50 rounded-lg bg-red-600 p-2 text-white focus:outline-none focus:ring-2 focus:ring-red-400">
        <i class="fa-solid fa-bars"></i>
    </button>

    <!-- Overlay -->
    <div x-show="open"
         @click="open = false"
         class="fixed inset-0 bg-black bg-opacity-40 z-40 md:hidden"
         x-transition.opacity></div>

    <!-- Sidebar -->
    <aside x-show="open || window.innerWidth >= 768"
           x-transition:enter="transition transform ease-out duration-300"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition transform ease-in duration-200"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed md:static top-0 left-0 h-screen w-64 flex flex-col justify-between border-r border-gray-200 bg-white p-4 z-50">

        <!-- Top Section -->
        <div>
            <!-- Logo -->
            <div class="flex items-center space-x-3 mb-6">
                <img src="{{ asset('images/variety-logo.png') }}" alt="Variety Logo" class="h-8 w-8 rounded">
                <div>
                    <h1 class="font-bold text-lg text-red-600">Variety</h1>
                    <p class="text-xs text-gray-500">Admin Portal</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="space-y-6 text-sm font-medium">
                <!-- Main -->
                <div>
                    <h3 class="text-xs uppercase tracking-wide text-gray-400 font-semibold mb-2">Main</h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('dashboard') }}"
                               class="flex items-center gap-3 rounded-lg px-3 py-2
                                   {{ request()->routeIs('dashboard') ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                <i class="fa-solid fa-chart-line w-5 text-center"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('events.index') }}"
                               class="flex items-center gap-3 rounded-lg px-3 py-2
                                   {{ request()->routeIs('events.*') ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                <i class="fa-solid fa-calendar-days w-5 text-center"></i>
                                <span>Events</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Management -->
                <div>
                    <h3 class="text-xs uppercase tracking-wide text-gray-400 font-semibold mb-2">Management</h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="#"
                               class="flex items-center gap-3 rounded-lg px-3 py-2
                                   {{ request()->routeIs('users.*') ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                <i class="fa-solid fa-users w-5 text-center"></i>
                                <span>User Management</span>
                            </a>
                        </li>
                        <li>
                            <a href="#"
                               class="flex items-center gap-3 rounded-lg px-3 py-2
                                   {{ request()->routeIs('settings.*') ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                                <i class="fa-solid fa-gear w-5 text-center"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <!-- Bottom Section -->
        <div class="flex items-center space-x-3 border-t border-gray-200 pt-4 mt-4">
            <div class="h-10 w-10 flex items-center justify-center rounded-full bg-red-600 text-white font-semibold">
                SJ
            </div>
            <div>
                <p class="font-medium text-gray-900 capitalize">{{ Auth::user()->name }}</p>
                <p class="text-sm text-gray-500">Administrator</p>
            </div>
        </div>
    </aside>
</div>

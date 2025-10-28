<!-- Sidebar -->
<aside class="flex w-64 flex-col justify-between border-r border-gray-200 bg-white p-4">
    <!-- Logo and Title -->
    <div>
        <div class="flex items-center space-x-3 mb-6">
            <img src="{{ asset('images/variety-logo.png') }}" alt="Variety Logo" class="h-8 w-8 rounded">
            <div>
                <h1 class="font-bold text-lg text-red-600">Variety</h1>
                <p class="text-xs text-gray-500">Admin Portal</p>
            </div>
        </div>

        <!-- Navigation Sections -->
        <nav class="space-y-6 text-sm font-medium">
            <!-- Main Section -->
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

            <!-- Management Section -->
            <div>
                <h3 class="text-xs uppercase tracking-wide text-gray-400 font-semibold mb-2">Management</h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('users.index') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 
                                  {{ request()->routeIs('users.*') ? 'bg-red-50 text-red-600 font-semibold' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fa-solid fa-users w-5 text-center"></i>
                            <span>User Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.index') }}"
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

    <!-- Bottom User Profile -->
    <div class="mt-6 flex items-center space-x-3 border-t border-gray-200 pt-4">
        <div class="h-10 w-10 flex items-center justify-center rounded-full bg-red-600 text-white font-semibold">
            SJ
        </div>
        <div>
            <p class="font-medium text-gray-900">Sarah Johnson</p>
            <p class="text-sm text-gray-500">Administrator</p>
        </div>
    </div>
</aside>

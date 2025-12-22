{{-- Customer Navbar --}}
<header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6">
    <!-- Page Title / Search -->
    <div class="flex-1">
        <h1 class="text-xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
    </div>

    <!-- Right Side Actions -->
    <div class="flex items-center space-x-4">
        <!-- Shortlist (PROMPT 50) -->
        <a href="{{ route('customer.shortlist') }}" class="relative p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100" title="My Shortlist">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
            <span id="shortlist-count" class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full" style="display: none;">0</span>
        </a>

        <!-- Notifications -->
        <button type="button" class="relative p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500"></span>
        </button>

        <!-- User Menu -->
        <div class="relative">
            <button type="button" class="flex items-center space-x-3 focus:outline-none">
                <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-medium">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>
        <button
    id="mobile-menu-btn"
    class="block md:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-md"
>
    ☰
</button>



    </div>
</header>

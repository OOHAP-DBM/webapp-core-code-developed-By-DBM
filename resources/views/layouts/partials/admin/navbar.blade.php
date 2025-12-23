{{-- Admin Navbar (Figma-aligned) --}}
<header class="admin-header sticky top-0 z-30 bg-white border-b border-gray-200 flex items-center justify-between px-6 h-20 shadow-sm">
    <div class="flex flex-col justify-center">
        <span class="text-xs text-gray-400 font-medium tracking-wide mb-1">@yield('page-title', 'Dashboard')</span>
        <nav class="breadcrumb flex items-center text-sm text-gray-500">
            {{-- Example: Home > Customers Management > Total Customers --}}
            @yield('breadcrumb', 'Home > Customers Management > Total Customers')
        </nav>
    </div>
    <div class="flex items-center gap-6">
        <!-- Notification bell (static badge for now) -->
        <button type="button" class="relative p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500"></span>
        </button>
        <!-- Admin profile section (Figma-aligned) -->
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold text-lg">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex flex-col items-start">
                <span class="font-semibold text-gray-900 leading-tight">Welcome Administrator</span>
                <span class="text-xs text-gray-500">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </div>
</header>

{{-- Admin Navbar (Figma-aligned) --}}
<header class="admin-header sticky top-0 z-30 bg-white border-b border-gray-200 flex items-center justify-between px-6 h-20 shadow-sm">
    <div class="flex flex-col justify-center">
        <span class="font-medium tracking-wide mb-1">@yield('page-title', 'Dashboard')</span>
    </div>
    <div class="flex items-center gap-6">
        <!-- Notification bell (static badge for now) -->
        <button type="button" class="relative p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 focus:outline-none hidden md:block">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500"></span>
        </button>
        <h2><div class="text-xs">Welcome</div>Administrator</h2>
        <button
            id="admin-mobile-menu-btn"
            class="block md:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-md"
        >
            ☰
        </button>
    </div>
</header>

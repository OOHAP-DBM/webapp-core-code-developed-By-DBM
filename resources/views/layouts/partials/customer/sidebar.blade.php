{{-- Customer Sidebar --}}
<aside class="w-64 bg-white border-r border-gray-200 overflow-y-auto text-center">
    <div class="p-6 text-center">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex items-center">
            <img src="{{ asset('assets/images/logo/logo_image.jpeg') }}" alt="OOHAPP" class="h-4 w-auto">
        </a>
    </div>

    <nav class="px-4 pb-4">
        <div class="space-y-1">
            <!-- Dashboard -->
            <a href="{{ route('customer.dashboard') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('customer.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Dashboard
            </a>

            <!-- Enquiries -->
            <a href="{{ route('customer.enquiries.index') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('customer.enquiries.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
                Enquiries
            </a>

            <!-- Quotations -->
            <a href="{{ route('customer.quotations.index') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('customer.quotations.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Quotations
            </a>

            <!-- Bookings -->
            <a href="{{ route('customer.bookings.index') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('customer.bookings.*') && !request()->routeIs('customer.campaigns.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Bookings
            </a>

            <!-- Campaigns -->
            <a href="{{ route('customer.campaigns.dashboard') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('customer.campaigns.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Campaigns
            </a>

            <!-- Payments -->
            <a href="{{ route('customer.payments.index') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('customer.payments.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                Payments
            </a>
            <a href="{{route('logout')}}">
                LogOut
            </a>
        </div>
    </nav>
</aside>

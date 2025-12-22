<aside class="admin-sidebar fixed left-0 top-0 h-screen flex flex-col shadow-lg transition-all duration-300">
    <!-- Logo -->
    <div class="flex items-center gap-2 px-6 py-5 border-b border-gray-800">
        <img src="{{ asset('images/logo-white.svg') }}" alt="OOHAPP" class="h-8 w-auto">
        <span class="text-xl font-bold tracking-wide text-black">OOHAPP</span>
    </div>
    <!-- Search -->
    <div class="px-6 py-3 border-b border-gray-100">
        <input type="text" placeholder="Search" class="w-full rounded-lg px-3 py-2 text-sm bg-gray-100 focus:outline-none" />
    </div>
    <!-- Profile -->
    <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="avatar" class="h-10 w-10 rounded-full object-cover">
        <div class="flex flex-col">
            <span class="font-semibold text-black leading-tight">{{ auth()->user()->name }}</span>
            <span class="text-xs text-green-600 font-semibold mt-0.5">Admin</span>
        </div>
    </div>
    <!-- Menu -->
    <nav class="flex-1 overflow-y-auto px-2 py-4">
        <ul class="space-y-1">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home mr-3"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <i class="fas fa-shopping-bag mr-3"></i> My Orders
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <i class="fas fa-store mr-3"></i> Multivendor Orders
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link flex items-center">
                    <i class="fas fa-comment-alt mr-3"></i> Messenger
                    <span class="ml-auto bg-blue-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full">27</span>
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <i class="fas fa-th-large mr-3"></i> All Hoardings
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <i class="fas fa-chart-line mr-3"></i> Earning Dashboard
                </a>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <i class="fas fa-undo-alt mr-3"></i> Refunds
                </a>
            </li>
        </ul>
        <div class="mt-6 mb-2 text-xs text-gray-400 font-semibold tracking-wider px-2">POS & BOOKINGS</div>
        <ul class="space-y-1">
            <li><a href="#" class="sidebar-link"><i class="fas fa-desktop mr-3"></i> POS Dashboard</a></li>
            <li><a href="#" class="sidebar-link"><i class="fas fa-calendar-plus mr-3"></i> Book Now</a></li>
            <li><a href="#" class="sidebar-link"><i class="fas fa-ticket-alt mr-3"></i> POS Booking</a></li>
            <li><a href="#" class="sidebar-link"><i class="fas fa-user-plus mr-3"></i> POS Customer</a></li>
            <li><a href="#" class="sidebar-link"><i class="fas fa-receipt mr-3"></i> POS Transactions</a></li>
            <li><a href="#" class="sidebar-link"><i class="fas fa-file-invoice-dollar mr-3"></i> Invoice Manager</a></li>
        </ul>
        <div class="mt-6 mb-2 text-xs text-gray-400 font-semibold tracking-wider px-2">PEOPLES</div>
        <ul class="space-y-1">
            <li>
                <div class="sidebar-submenu-title">Vendor Management</div>
                <ul class="ml-4 space-y-1">
                    <li><a href="{{ route('admin.vendors.requested') }}" class="sidebar-link flex items-center {{ request()->routeIs('admin.vendors.requested') ? 'active' : '' }}">Requested Vendors @if(isset($requestedVendorCount) && $requestedVendorCount > 0)<span class="ml-2 bg-green-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full">{{ $requestedVendorCount }}</span>@endif</a></li>
                    <li><a href="#" class="sidebar-link">Active Vendors</a></li>
                    <li><a href="#" class="sidebar-link">Deleted Vendors</a></li>
                </ul>
            </li>
            <li>
                <div class="sidebar-submenu-title">Customer Management</div>
                <ul class="ml-4 space-y-1">
                    <li><a href="{{ route('admin.customers.index') }}" class="sidebar-link flex items-center {{ request()->routeIs('admin.customers.index') ? 'active' : '' }}">Total Customers @if(isset($totalCustomerCount) && $totalCustomerCount > 0)<span class="ml-2 bg-blue-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full">{{ $totalCustomerCount }}</span>@endif</a></li>
                    <li><a href="#" class="sidebar-link">Joined this Week</a></li>
                    <li><a href="#" class="sidebar-link">Joined this Month</a></li>
                    <li><a href="#" class="sidebar-link">Account Deletion Request</a></li>
                    <li><a href="#" class="sidebar-link">Deleted Customer</a></li>
                </ul>
            </li>
            <li>
                <div class="sidebar-submenu-title">My Staff</div>
                <ul class="ml-4 space-y-1">
                    <li><a href="#" class="sidebar-link">Graphics Designer</a></li>
                    <li><a href="#" class="sidebar-link">Printer</a></li>
                    <li><a href="#" class="sidebar-link">Mounter</a></li>
                    <li><a href="#" class="sidebar-link">Supplier</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</aside>
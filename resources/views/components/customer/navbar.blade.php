<header class="bg-white border-b border-gray-100 sticky top-0 z-50">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="flex items-center justify-between h-16 gap-4 ">
            <!-- Logo -->
            <div class="flex items-center flex-shrink-0">
                <a href="{{ route('home') }}" class="flex items-center space-x-1.5">
                   <img src="{{asset('assets/images/logo/logo_image.jpeg')}}" alt="" width="150">
                </a>
            </div>

            <!-- Search Bar (Desktop & Tablet) -->
            @include('components.customer.home-search')

            <!-- Right Side Icons -->
            <div class="flex items-center space-x-3 lg:space-x-5">
                    <div class="relative inline-block" id="userDropdownWrapper">

                        <!-- USER ICON (UNCHANGED) -->
                <a href="javascript:void(0)"
                        id="userDropdownBtn"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                        title="Login">
                            <svg class="w-6 h-6" fill="white" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                </a>

                        <!-- DROPDOWN -->
                    <div id="userDropdown"
                        class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50">

                        {{-- TOP SECTION --}}
                        <div class="p-4 border-b">

                            @guest
                                <!-- GUEST VIEW -->
                                <p class="font-semibold text-gray-900">Welcome</p>
                                <p class="text-sm text-gray-500">
                                    To access account and manage bookings.
                                </p>

                                <button
                                    class="mt-3 w-full bg-black text-white rounded-md py-2 text-sm font-medium">
                                    <a href="{{ route('login') }}"
                                    class="mt-3  text-center rounded-md py-2 text-sm font-medium">
                                        Login /
                                    </a>
                                    <a href="{{ route('register.role-selection') }}"
                                    class="mt-2  text-center  rounded-md py-2 text-sm font-medium">
                                        Signup
                                    </a>
                                </button>
                            @endguest

                            @auth
                                @php
                                    $dashboardUrl = '#';

                                    if(auth()->user()->hasRole('admin')){
                                        $dashboardUrl = route('admin.dashboard');
                                    }elseif(auth()->user()->hasRole('vendor')){
                                        $dashboardUrl = route('vendor.dashboard');
                                    }else{
                                        $dashboardUrl = route('customer.dashboard');
                                    }
                                @endphp

                                <a href="{{ $dashboardUrl }}" class="block">
                                    <div class="bg-black text-white rounded-md px-3 py-2 hover:bg-gray-900 transition">
                                        <p class="text-sm font-semibold">
                                            {{ auth()->user()->name }}
                                        </p>
                                        @if(!empty(auth()->user()->phone))
                                            <p class="text-xs opacity-80">
                                                +91{{ auth()->user()->phone }}
                                            </p>
                                        @endif
                                    </div>
                                </a>
                            @endauth
                        </div>



                            @auth
                                <a href="{{ $dashboardUrl }}"
                                class="flex items-center font-semibold justify-between px-4 pt-2 text-gray-700 hover:bg-gray-100">
                                    Dashboard
                                </a>
                                <!-- <a href="javascript:void(0)"
                                class="flex items-center justify-between px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Messenger
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                </a>

                                <a href="javascript:void(0)"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Appointments
                                </a>

                                <a href="javascript:void(0)"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Bookmarked
                                </a>

                                <a href="javascript:void(0)"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Team Management
                                </a>

                                <a href="javascript:void(0)"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Payment & Invoices
                                </a>

                                <a href="javascript:void(0)"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Refer & Earn
                                </a>

                                <a href="javascript:void(0)"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Subscription
                                </a> -->
                            @endauth
                                                   {{-- LINKS --}}
                        <div class="py-2 text-sm">
                            <!-- 
                            <a href="javascript:void(0)"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                My Booking
                            </a> -->

                            <!-- <a href="javascript:void(0)"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                Enquiry
                            </a> -->

                            <!-- <a href="javascript:void(0)"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                Help Center
                            </a> -->

                            @auth
                                <button
                                    onclick="openLogoutModal()"
                                    class="w-full text-left px-4 py-2 text-red-500 hover:bg-gray-100">
                                    Logout
                                </button>
                            @endauth

                        </div>
                    </div>


                    </div>
                        <!-- Saved/Bookmarks -->
                    <a href="#" class="hidden md:block text-gray-400 hover:text-gray-600 transition-colors" title="Saved">
                        <svg class="w-6 h-6" fill="white" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                    </a>
                    <!-- Cart with Badge -->
                    <a href="{{ route('cart.index') }}" class="relative inline-block text-gray-400 hover:text-gray-600" title="Cart">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="white"
                                xmlns="http://www.w3.org/2000/svg"
                                class="">
                                <path d="M3 3H5L7.5 14H17.5L20 6H6"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"/>
                                <circle cx="9" cy="19" r="1.5" fill="currentColor"/>
                                <circle cx="17" cy="19" r="1.5" fill="currentColor"/>
                            </svg>
                            @php
                                $cartCount = 0;
                                if(auth()->check()) {
                                    $cartCount = \Illuminate\Support\Facades\DB::table('carts')
                                        ->where('user_id', auth()->id())
                                        ->count();
                                }
                            @endphp
                            @if($cartCount > 0)
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                    {{ $cartCount }}
                                </span>
                            @endif
                    </a>

                <!-- Mobile Menu Button -->
                <button type="button" class="md:hidden text-gray-700" onclick="toggleMobileMenu()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Search Bar -->
        <div class="md:hidden pb-3 pt-2">
            <form action="{{ route('search') }}" method="GET" class="flex items-center w-full bg-gray-50 rounded-lg border border-gray-300">
                <div class="flex items-center flex-1 px-3 py-2">
                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input 
                        type="text" 
                        name="location" 
                        placeholder="Search by city.." 
                        class="w-full bg-transparent border-none focus:outline-none focus:ring-0 text-sm"
                    >
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-r-lg">
                    Search
                </button>
            </form>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobile-menu" class="hidden md:hidden pb-4 border-t border-gray-100 mt-2">
            <div class="flex flex-col space-y-3 pt-3">
                <a href="{{ route('hoardings.index') }}" class="text-gray-700 hover:text-blue-600 font-medium px-2 py-1">Hoardings</a>
                <a href="{{ route('dooh.index') }}" class="text-gray-700 hover:text-blue-600 font-medium px-2 py-1">DOOH</a>
                @auth
                    <a href="" class="text-gray-700 hover:text-blue-600 font-medium px-2 py-1">Saved Items</a>
                    <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-blue-600 font-medium px-2 py-1">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 font-medium px-2 py-1">Login</a>
                    <a href="{{ route('register.role-selection') }}" class="text-blue-600 hover:text-blue-700 font-semibold px-2 py-1">Sign Up</a>
                @endauth
            </div>
        </div>
    </div>
</header>

@push('scripts')
<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    }

</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const btn  = document.getElementById('userDropdownBtn');
    const menu = document.getElementById('userDropdown');

    btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        menu.classList.toggle('hidden');
    });

    document.addEventListener('click', function () {
        menu.classList.add('hidden');
    });

});
</script>
@endpush

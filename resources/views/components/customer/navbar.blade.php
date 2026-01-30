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
                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0.5 14.5C0.5 13.4391 0.921427 12.4217 1.67157 11.6716C2.42172 10.9214 3.43913 10.5 4.5 10.5H12.5C13.5609 10.5 14.5783 10.9214 15.3284 11.6716C16.0786 12.4217 16.5 13.4391 16.5 14.5C16.5 15.0304 16.2893 15.5391 15.9142 15.9142C15.5391 16.2893 15.0304 16.5 14.5 16.5H2.5C1.96957 16.5 1.46086 16.2893 1.08579 15.9142C0.710714 15.5391 0.5 15.0304 0.5 14.5Z" stroke="#484848" stroke-linejoin="round"/>
                            <path d="M8.5 6.5C10.1569 6.5 11.5 5.15685 11.5 3.5C11.5 1.84315 10.1569 0.5 8.5 0.5C6.84315 0.5 5.5 1.84315 5.5 3.5C5.5 5.15685 6.84315 6.5 8.5 6.5Z" stroke="#484848"/>
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
                    <a href="{{route('customer.shortlist')}}" class="hidden md:block text-gray-400 hover:text-gray-600 transition-colors" title="Saved">
                        <svg width="19" height="17" viewBox="0 0 19 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.24 8.25002C1.84461 7.85725 1.53134 7.38971 1.31845 6.87466C1.10556 6.3596 0.997308 5.80733 1 5.25002C1 4.12285 1.44777 3.04184 2.2448 2.24481C3.04183 1.44778 4.12283 1.00002 5.25 1.00002C6.83 1.00002 8.21 1.86002 8.94 3.14002H10.06C10.4311 2.48908 10.9681 1.94811 11.6163 1.57219C12.2645 1.19628 13.0007 0.998856 13.75 1.00002C14.8772 1.00002 15.9582 1.44778 16.7552 2.24481C17.5522 3.04184 18 4.12285 18 5.25002C18 6.42002 17.5 7.50002 16.76 8.25002L9.5 15.5L2.24 8.25002ZM17.46 8.96002C18.41 8.00002 19 6.70002 19 5.25002C19 3.85763 18.4469 2.52227 17.4623 1.53771C16.4777 0.553141 15.1424 1.8052e-05 13.75 1.8052e-05C12 1.8052e-05 10.45 0.850018 9.5 2.17002C9.0151 1.49652 8.37661 0.948336 7.63748 0.570946C6.89835 0.193557 6.0799 -0.00216431 5.25 1.8052e-05C3.85761 1.8052e-05 2.52226 0.553141 1.53769 1.53771C0.553123 2.52227 0 3.85763 0 5.25002C0 6.70002 0.59 8.00002 1.54 8.96002L9.5 16.92L17.46 8.96002Z" fill="#484848"/>
                        </svg>
                    </a>
                    <!-- Cart with Badge -->
                    <a href="{{ route('cart.index') }}" class="relative inline-block text-gray-400 hover:text-gray-600" title="Cart">
                            <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 15C15.5304 15 16.0391 15.2107 16.4142 15.5858C16.7893 15.9609 17 16.4696 17 17C17 17.5304 16.7893 18.0391 16.4142 18.4142C16.0391 18.7893 15.5304 19 15 19C14.4696 19 13.9609 18.7893 13.5858 18.4142C13.2107 18.0391 13 17.5304 13 17C13 16.4696 13.2107 15.9609 13.5858 15.5858C13.9609 15.2107 14.4696 15 15 15ZM15 16C14.7348 16 14.4804 16.1054 14.2929 16.2929C14.1054 16.4804 14 16.7348 14 17C14 17.2652 14.1054 17.5196 14.2929 17.7071C14.4804 17.8946 14.7348 18 15 18C15.2652 18 15.5196 17.8946 15.7071 17.7071C15.8946 17.5196 16 17.2652 16 17C16 16.7348 15.8946 16.4804 15.7071 16.2929C15.5196 16.1054 15.2652 16 15 16ZM6 15C6.53043 15 7.03914 15.2107 7.41421 15.5858C7.78929 15.9609 8 16.4696 8 17C8 17.5304 7.78929 18.0391 7.41421 18.4142C7.03914 18.7893 6.53043 19 6 19C5.46957 19 4.96086 18.7893 4.58579 18.4142C4.21071 18.0391 4 17.5304 4 17C4 16.4696 4.21071 15.9609 4.58579 15.5858C4.96086 15.2107 5.46957 15 6 15ZM6 16C5.73478 16 5.48043 16.1054 5.29289 16.2929C5.10536 16.4804 5 16.7348 5 17C5 17.2652 5.10536 17.5196 5.29289 17.7071C5.48043 17.8946 5.73478 18 6 18C6.26522 18 6.51957 17.8946 6.70711 17.7071C6.89464 17.5196 7 17.2652 7 17C7 16.7348 6.89464 16.4804 6.70711 16.2929C6.51957 16.1054 6.26522 16 6 16ZM17 3H3.27L5.82 9H14C14.33 9 14.62 8.84 14.8 8.6L17.8 4.6C17.93 4.43 18 4.22 18 4C18 3.73478 17.8946 3.48043 17.7071 3.29289C17.5196 3.10536 17.2652 3 17 3ZM14 10H5.87L5.1 11.56L5 12C5 12.2652 5.10536 12.5196 5.29289 12.7071C5.48043 12.8946 5.73478 13 6 13H17V14H6C5.46957 14 4.96086 13.7893 4.58579 13.4142C4.21071 13.0391 4 12.5304 4 12C3.9997 11.6607 4.08573 11.3269 4.25 11.03L4.97 9.56L1.34 1H0V0H2L2.85 2H17C17.5304 2 18.0391 2.21071 18.4142 2.58579C18.7893 2.96086 19 3.46957 19 4C19 4.5 18.83 4.92 18.55 5.26L15.64 9.15C15.28 9.66 14.68 10 14 10Z" fill="#484848"/>
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
                                <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                    {{ $cartCount }}
                                </span>
                            @endif
                    </a>

                <!-- Mobile Menu Button -->
                <button type="button" class="md:hidden text-gray-700 -mt-1" onclick="toggleMobileMenu()">
                    <svg class="w-6 h-7" fill="none" stroke="currentColor" viewBox="0 0 19 19">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 6h16M4 12h16M4 18h16"/>
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
        <div id="mobile-menu"
            class="fixed top-0 left-0 h-full w-72 bg-white shadow-xl
                    transform -translate-x-full transition-transform duration-300 ease-in-out
                    z-50 md:hidden">

            <!-- HEADER -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ asset('assets/images/logo/logo_image.jpeg') }}" alt="OOHAPP" width="140">
                </a>

                <button onclick="toggleMobileMenu()"
                        class="text-gray-500 hover:text-gray-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- MENU LINKS -->
            <div class="flex flex-col px-5 py-4 space-y-2 text-sm">

                <!-- Hoardings -->
                <a href="{{ route('search') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md
                        text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 7h18M3 12h18M3 17h18"/>
                    </svg>
                    Hoardings
                </a>

                <!-- DOOH -->
                <a href="{{ route('search', ['type' => 'dooh']) }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md
                        text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 17V7l7 5-7 5z"/>
                    </svg>
                    DOOH
                </a>

                @auth
                    <!-- Saved -->
                    <a href="#"
                    class="flex items-center gap-3 px-3 py-2 rounded-md
                            text-gray-700 hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M5 5v14l7-4 7 4V5z"/>
                        </svg>
                        Saved Items
                    </a>

                    <!-- Dashboard -->
                    <a href="{{ $dashboardUrl }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md
                            text-gray-700 hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 12l2-2 4 4 8-8 4 4"/>
                        </svg>
                        Dashboard
                    </a>
                @else
                    <!-- Login -->
                    <a href="{{ route('login') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md
                            text-gray-700 hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M10 17l5-5-5-5"/>
                        </svg>
                        Login
                    </a>

                    <!-- Signup -->
                    <a href="{{ route('register.role-selection') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-md
                            text-blue-600 font-semibold hover:bg-blue-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 4v16m8-8H4"/>
                        </svg>
                        Sign Up
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>

@push('scripts')
<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');

    if (menu.classList.contains('-translate-x-full')) {
        menu.classList.remove('-translate-x-full');
        menu.classList.add('translate-x-0');
        document.body.style.overflow = 'hidden'; // background lock
    } else {
        menu.classList.add('-translate-x-full');
        menu.classList.remove('translate-x-0');
        document.body.style.overflow = '';
    }
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

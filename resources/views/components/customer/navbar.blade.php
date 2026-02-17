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
                            <svg width="20" height="19" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.5 4.25C3.5 3.69188 3.60993 3.13923 3.82351 2.6236C4.03709 2.10796 4.35015 1.63945 4.7448 1.2448C5.13944 0.850147 5.60796 0.537094 6.1236 0.323512C6.63923 0.109929 7.19188 0 7.75 0C8.30812 0 8.86077 0.109929 9.3764 0.323512C9.89204 0.537094 10.3606 0.850147 10.7552 1.2448C11.1499 1.63945 11.4629 2.10796 11.6765 2.6236C11.8901 3.13923 12 3.69188 12 4.25C12 5.37717 11.5522 6.45817 10.7552 7.2552C9.95817 8.05223 8.87717 8.5 7.75 8.5C6.62283 8.5 5.54183 8.05223 4.7448 7.2552C3.94777 6.45817 3.5 5.37717 3.5 4.25ZM7.75 1.5C7.02065 1.5 6.32118 1.78973 5.80546 2.30546C5.28973 2.82118 5 3.52065 5 4.25C5 4.97935 5.28973 5.67882 5.80546 6.19454C6.32118 6.71027 7.02065 7 7.75 7C8.47935 7 9.17882 6.71027 9.69454 6.19454C10.2103 5.67882 10.5 4.97935 10.5 4.25C10.5 3.52065 10.2103 2.82118 9.69454 2.30546C9.17882 1.78973 8.47935 1.5 7.75 1.5ZM3.75 11.5C3.15326 11.5 2.58097 11.7371 2.15901 12.159C1.73705 12.581 1.5 13.1533 1.5 13.75V14.938C1.5 14.956 1.513 14.972 1.531 14.975C5.65 15.647 9.851 15.647 13.969 14.975C13.9775 14.9731 13.9851 14.9684 13.9907 14.9617C13.9963 14.955 13.9996 14.9467 14 14.938V13.75C14 13.1533 13.7629 12.581 13.341 12.159C12.919 11.7371 12.3467 11.5 11.75 11.5H11.41C11.3832 11.5005 11.3567 11.5045 11.331 11.512L10.466 11.795C8.70118 12.3713 6.79882 12.3713 5.034 11.795L4.168 11.512C4.14296 11.5047 4.11708 11.5006 4.091 11.5H3.75ZM0 13.75C0 12.7554 0.395088 11.8016 1.09835 11.0983C1.80161 10.3951 2.75544 10 3.75 10H4.09C4.27667 10.0007 4.458 10.0293 4.634 10.086L5.5 10.369C6.96203 10.8463 8.53797 10.8463 10 10.369L10.866 10.086C11.041 10.029 11.225 10 11.409 10H11.75C12.7446 10 13.6984 10.3951 14.4017 11.0983C15.1049 11.8016 15.5 12.7554 15.5 13.75V14.938C15.5 15.692 14.954 16.334 14.21 16.455C9.93164 17.1534 5.56836 17.1534 1.29 16.455C0.930184 16.3958 0.603047 16.2108 0.366821 15.9331C0.130596 15.6553 0.000609175 15.3027 0 14.938V13.75Z" fill="#6E6E6E"/>
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
                                    $profileUrl   = '#';

                                    if(auth()->check()) {

                                        if(auth()->user()->hasRole('admin')) {
                                            $dashboardUrl = route('admin.dashboard');
                                            $profileUrl   = $dashboardUrl;

                                        } elseif(auth()->user()->hasRole('vendor')) {
                                            $dashboardUrl = route('vendor.dashboard');
                                            $profileUrl   = route('vendor.profile.edit');

                                        } else {
                                            $dashboardUrl = route('customer.dashboard');
                                            $profileUrl   = route('customer.profile.index');
                                        }
                                    }
                                @endphp


                                <a href="{{ $profileUrl }}" class="block">
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
                        <a href="javascript:void(0)"
                        onclick="openWishlist(event)"
                        class="relative inline-block text-gray-400 hover:text-gray-600"
                        data-auth="{{ auth()->check() ? '1' : '0' }}"
                        data-role="{{ auth()->check() ? auth()->user()->active_role : '' }}"
                        title="Wishlist"
                        >               
                         <svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.5 0.75C2.877 0.75 0.75 3.01 0.75 5.797C0.75 11.375 9.75 17.75 9.75 17.75C9.75 17.75 18.75 11.375 18.75 5.797C18.75 2.344 16.623 0.75 14 0.75C12.14 0.75 10.53 1.886 9.75 3.54C8.97 1.886 7.36 0.75 5.5 0.75Z" stroke="#6E6E6E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                            @php
                                $wishlistCount = auth()->check()
                                    ? auth()->user()->wishlist()->count()
                                    : 0;
                            @endphp
                      
                           @if($wishlistCount > 0)
                                <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                    {{$wishlistCount}}
                                </span>
                            @endif
                    </a>

                    <!-- Cart with Badge -->
                    <a href="{{ route('cart.index') }}" class="relative inline-block text-gray-400 hover:text-gray-600" title="Cart">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.46 3.15018H16.674C18.052 3.15018 19.047 4.42018 18.669 5.69818L17.015 11.2982C16.76 12.1582 15.946 12.7502 15.02 12.7502H5.862C4.935 12.7502 4.12 12.1572 3.866 11.2982L1.46 3.15018ZM1.46 3.15018L0.75 0.750183M14.25 18.7502C14.6478 18.7502 15.0294 18.5921 15.3107 18.3108C15.592 18.0295 15.75 17.648 15.75 17.2502C15.75 16.8524 15.592 16.4708 15.3107 16.1895C15.0294 15.9082 14.6478 15.7502 14.25 15.7502C13.8522 15.7502 13.4706 15.9082 13.1893 16.1895C12.908 16.4708 12.75 16.8524 12.75 17.2502C12.75 17.648 12.908 18.0295 13.1893 18.3108C13.4706 18.5921 13.8522 18.7502 14.25 18.7502ZM6.25 18.7502C6.64782 18.7502 7.02936 18.5921 7.31066 18.3108C7.59196 18.0295 7.75 17.648 7.75 17.2502C7.75 16.8524 7.59196 16.4708 7.31066 16.1895C7.02936 15.9082 6.64782 15.7502 6.25 15.7502C5.85218 15.7502 5.47064 15.9082 5.18934 16.1895C4.90804 16.4708 4.75 16.8524 4.75 17.2502C4.75 17.648 4.90804 18.0295 5.18934 18.3108C5.47064 18.5921 5.85218 18.7502 6.25 18.7502Z" stroke="#6E6E6E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
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
<script>
function openWishlist(event) {
    event.preventDefault();

    const link = event.currentTarget;
    const isAuth = link.dataset.auth === '1';
    const role = link.dataset.role;

    /* ❌ NOT LOGGED IN */
    if (!isAuth) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'warning',
            title: 'Please login to view your wishlist',
            showConfirmButton: false,
            timer: 2500
        });

        setTimeout(() => {
            window.location.href = "{{ route('login') }}";
        }, 2000);
        return;
    }
    window.location.href = "{{ route('shortlist') }}";
}
</script>

@endpush

<header class="bg-white border-b border-gray-100 sticky top-0 z-50">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="flex items-center justify-between h-16 gap-4">
            <!-- Logo -->
            <div class="flex items-center flex-shrink-0">
                <a href="{{ route('home') }}" class="flex items-center space-x-1.5">
                   <img src="{{asset('assets/images/logo/logo_image.jpeg')}}" alt="" width="150">
                </a>
            </div>

            <!-- Search Bar (Desktop & Tablet) -->
            <div class="hidden md:flex items-center flex-1 max-w-3xl mx-4">
                <form action="{{ route('search') }}" method="GET" class="flex items-center w-full bg-white rounded-md border border-gray-300 overflow-hidden">
                    <!-- Location Search Input -->
                    <div class="flex items-center flex-1 px-4 py-2.5">
                        <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input 
                            type="text" 
                            name="location" 
                            placeholder="Search by city, locality.." 
                            class="w-full bg-transparent border-none focus:outline-none focus:ring-0 text-sm text-gray-700 placeholder-gray-400"
                        >
                    </div>

                    <!-- Divider -->
                    <div class="h-8 w-px bg-gray-200"></div>

                    <!-- Near Me Button -->
                    <button 
                        type="button" 
                        class="flex items-center px-4 py-2.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors"
                        onclick="getCurrentLocation()"
                    >
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="whitespace-nowrap">Near me</span>
                    </button>

                    <!-- Divider -->
                    <div class="h-8 w-px bg-gray-200"></div>

                    <!-- Date Range Picker -->
                    <div class="flex items-center px-4 py-2.5 min-w-[180px]">
                        <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-400 leading-tight">From - To</span>
                            <input type="hidden" name="from_date" id="from_date">
                            <input type="hidden" name="to_date" id="to_date">
                            <input 
                                type="text" 
                                id="dateRange"
                                class="bg-transparent border-none focus:outline-none focus:ring-0 text-xs text-gray-700 cursor-pointer p-0 leading-tight"
                                value="Wed, 11 Dec 24 - Thu, 12 Dec 24"
                                readonly
                            >
                        </div>
                    </div>

                    <!-- Search Button -->
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors"
                    >
                        Search
                    </button>
                </form>
            </div>

            <!-- Right Side Icons -->
            <div class="flex items-center space-x-3 lg:space-x-5">
                    <div class="relative inline-block" id="userDropdownWrapper">

                        <!-- USER ICON (UNCHANGED) -->
                        <a href="javascript:void(0)"
                        id="userDropdownBtn"
                        class="text-gray-600 hover:text-gray-900 transition-colors"
                        title="Login">
                            <svg class="w-6 h-6" fill="black" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <p class="text-xs opacity-80">
                                            +91{{ auth()->user()->phone ?? '**********' }}
                                        </p>
                                    </div>
                                </a>
                            @endauth
                        </div>

                        {{-- LINKS --}}
                        <div class="py-2 text-sm">

                            <a href="javascript:void(0)"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                My Booking
                            </a>

                            <a href="javascript:void(0)"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                Enquiry
                            </a>

                            @auth
                                <a href="javascript:void(0)"
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
                                </a>
                            @endauth

                            <a href="javascript:void(0)"
                            class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                Help Center
                            </a>

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
                    <a href="#" class="hidden md:block text-gray-600 hover:text-gray-900 transition-colors" title="Saved">
                        <svg class="w-6 h-6" fill="black" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                    </a>
                    <!-- Cart with Badge -->
                    <a href="{{ route('cart.index') }}" class="relative inline-block" title="Cart">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="black"
                            xmlns="http://www.w3.org/2000/svg"
                            class="text-gray-700">
                            <path d="M3 3H5L7.5 14H17.5L20 6H6"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"/>
                            <circle cx="9" cy="19" r="1.5" fill="currentColor"/>
                            <circle cx="17" cy="19" r="1.5" fill="currentColor"/>
                        </svg>
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
<!-- LOGOUT MODAL -->
<div id="logoutModal"
     class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60">

    <div class="bg-white w-[90%] max-w-md rounded-2xl shadow-xl relative">

        <!-- CLOSE ICON -->
        <button
            onclick="closeLogoutModal()"
            class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
            ✕
        </button>

        <!-- CONTENT -->
        <div class="p-8 text-center">

            <!-- ICON -->
            <div class="flex justify-center mb-5">
                <div class="w-16 h-16 flex items-center justify-center rounded-full">
                    <svg width="56" height="51" viewBox="0 0 56 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M25.4167 50.8333C11.379 50.8333 0 39.4543 0 25.4167C0 11.3791 11.379 7.16314e-06 25.4167 7.16314e-06C29.363 -0.00295759 33.2557 0.914429 36.7854 2.67928C40.3151 4.44414 43.3846 7.00783 45.75 10.1667H38.8621C35.927 7.57865 32.3076 5.8925 28.438 5.31057C24.5684 4.72863 20.6131 5.27563 17.0467 6.88592C13.4803 8.49622 10.4543 11.1014 8.33176 14.3889C6.20927 17.6763 5.08046 21.5064 5.08079 25.4195C5.08111 29.3326 6.21056 33.1625 8.3336 36.4496C10.4566 39.7367 13.4831 42.3414 17.0498 43.9511C20.6164 45.5608 24.5718 46.1071 28.4413 45.5245C32.3108 44.9419 35.93 43.2552 38.8646 40.6667H45.7525C43.3869 43.8259 40.317 46.3898 36.7868 48.1546C33.2566 49.9195 29.3634 50.8367 25.4167 50.8333ZM43.2083 35.5833V27.9583H22.875V22.875H43.2083V15.25L55.9167 25.4167L43.2083 35.5833Z" fill="#E75858"/>
                    </svg>
                </div>
            </div>

            <!-- TEXT -->
            <h2 class="text-xl font-semibold mb-2">
                Comeback Soon!
            </h2>

            <p class="text-gray-500 mb-6">
                Are you sure you want<br>
                to logout from OOHAPP?
            </p>

            <div class="flex justify-between items-center gap-4">
                <button
                    type="button"
                    onclick="closeLogoutModal()"
                    class="text-gray-700 font-medium">
                    Cancel
                </button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="bg-red-500 hover:bg-red-600 text-white font-semibold px-6 py-3 rounded-lg">
                        Yes, Logout
                    </button>
                </form>

           </div>

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

    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    // Redirect to search with coordinates
                    window.location.href = `{{ route('search') }}?lat=${lat}&lng=${lng}&near_me=1`;
                },
                function(error) {
                    alert('Unable to get your location. Please enable location services.');
                }
            );
        } else {
            alert('Geolocation is not supported by your browser.');
        }
    }

    // Date Range Picker (Using flatpickr or similar library)
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('dateRange');
        if (dateInput && typeof flatpickr !== 'undefined') {
            flatpickr("#dateRange", {
                mode: "range",
                dateFormat: "D, d M y",
                onChange: function (selectedDates) {
                    if (selectedDates.length === 2) {
                        document.getElementById('from_date').value =
                            selectedDates[0].toISOString().split('T')[0];

                        document.getElementById('to_date').value =
                            selectedDates[1].toISOString().split('T')[0];
                    }
                }
            });

        } else {
            // Fallback: Set default text
            if (dateInput) {
                const today = new Date();
                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);
                
                const formatDate = (date) => {
                    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    return `${days[date.getDay()]}, ${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear().toString().slice(-2)}`;
                };
                
                dateInput.value = `${formatDate(today)} - ${formatDate(tomorrow)}`;
            }
        }
    });
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
    function openLogoutModal() {
        document.getElementById('logoutModal').classList.remove('hidden');
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').classList.add('hidden');
    }
</script>
@endpush

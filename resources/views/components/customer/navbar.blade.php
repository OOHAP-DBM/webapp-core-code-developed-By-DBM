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

                            <!-- TOP SECTION -->
                            <div class="p-4 border-b">
                                <p class="font-semibold text-gray-900">Welcome</p>
                                <p class="text-sm text-gray-500">
                                    To access account and manage bookings.
                                </p>

                                @guest
                                    <!-- GUEST : Login / Signup -->
                                    <a href="{{ route('login') }}"
                                    class="mt-3  text-center rounded-md py-2 text-sm font-medium">
                                        Login /
                                    </a>

                                    <a href="{{ route('register.role-selection') }}"
                                    class="mt-2  text-center  rounded-md py-2 text-sm font-medium">
                                        Signup
                                    </a>
                                @endguest

                                @auth
                                    <!-- AUTH : Dashboard -->
                                    @if(auth()->user()->hasRole('vendor'))
                                        <a href="{{ route('vendor.dashboard') }}"
                                        class="mt-3  text-center  rounded-md py-2 text-sm font-medium">
                                            Dashboard
                                        </a>
                                    @else
                                        <a href="{{ route('customer.dashboard') }}"
                                        class="mt-3  text-center  rounded-md py-2 text-sm font-medium">
                                            Dashboard
                                        </a>
                                    @endif
                                @endauth

                            </div>

                            <!-- LINKS -->
                            <div class="py-2">
                                <a href="#"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    My Booking
                                </a>
                                <a href="#"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Enquiry
                                </a>
                                <a href="#"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Help Center
                                </a>
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
                    <a href="#" class="relative inline-block" title="Cart">
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
            flatpickr(dateInput, {
                mode: 'range',
                dateFormat: 'D, d M y',
                defaultDate: [new Date(), new Date(Date.now() + 86400000)],
                onChange: function(selectedDates, dateStr) {
                    // Handle date selection
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
@endpush

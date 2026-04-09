<header id="mainHeader" class="bg-[#FBFBFB] border-b border-gray-100 fixed top-0 left-0 w-full z-50 md:h-43 transition-all duration-300">
    <!-- Desktop/Tablet Navbar -->
    <div >
        <div class="container mx-auto pr-4 hidden md:block">
           
            <div class="flex items-center justify-between  gap-4 ">

           
                <div class="flex items-center gap-3 ">
                    <!-- Logo -->
                    <div class="flex items-center flex-shrink-0">
                        <a href="{{ route('home') }}" class="flex items-center space-x-1.5">
                            <x-optimized-image
                                :src="route('brand.oohapp-logo')"
                                alt="OOHApp  logo"
                                width="200"
                                height="100"
                                style="max-height:80px;object-fit:contain;"
                            />
                        </a>
                    </div>
                </div>

                <!-- Search Bar (Desktop & Tablet only) -->
         
                <div class="hidden md:flex flex-1 justify-center px-6">
                    @include('components.customer.home-search')
                </div>
                

                <!-- Right Side Icons: User, Bookmark, Cart -->
                <div class="flex items-center space-x-4 lg:space-x-5">

                    <!-- USER ICON with Dropdown -->
                    <!-- <div class="relative inline-block" id="userDropdownWrapper">
                        <a href="javascript:void(0)"
                            id="userDropdownBtn"
                            class="text-gray-400 hover:text-gray-600 transition-colors"
                            title="Login">
                            <svg width="20" height="19" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.5 4.25C3.5 3.69188 3.60993 3.13923 3.82351 2.6236C4.03709 2.10796 4.35015 1.63945 4.7448 1.2448C5.13944 0.850147 5.60796 0.537094 6.1236 0.323512C6.63923 0.109929 7.19188 0 7.75 0C8.30812 0 8.86077 0.109929 9.3764 0.323512C9.89204 0.537094 10.3606 0.850147 10.7552 1.2448C11.1499 1.63945 11.4629 2.10796 11.6765 2.6236C11.8901 3.13923 12 3.69188 12 4.25C12 5.37717 11.5522 6.45817 10.7552 7.2552C9.95817 8.05223 8.87717 8.5 7.75 8.5C6.62283 8.5 5.54183 8.05223 4.7448 7.2552C3.94777 6.45817 3.5 5.37717 3.5 4.25ZM7.75 1.5C7.02065 1.5 6.32118 1.78973 5.80546 2.30546C5.28973 2.82118 5 3.52065 5 4.25C5 4.97935 5.28973 5.67882 5.80546 6.19454C6.32118 6.71027 7.02065 7 7.75 7C8.47935 7 9.17882 6.71027 9.69454 6.19454C10.2103 5.67882 10.5 4.97935 10.5 4.25C10.5 3.52065 10.2103 2.82118 9.69454 2.30546C9.17882 1.78973 8.47935 1.5 7.75 1.5ZM3.75 11.5C3.15326 11.5 2.58097 11.7371 2.15901 12.159C1.73705 12.581 1.5 13.1533 1.5 13.75V14.938C1.5 14.956 1.513 14.972 1.531 14.975C5.65 15.647 9.851 15.647 13.969 14.975C13.9775 14.9731 13.9851 14.9684 13.9907 14.9617C13.9963 14.955 13.9996 14.9467 14 14.938V13.75C14 13.1533 13.7629 12.581 13.341 12.159C12.919 11.7371 12.3467 11.5 11.75 11.5H11.41C11.3832 11.5005 11.3567 11.5045 11.331 11.512L10.466 11.795C8.70118 12.3713 6.79882 12.3713 5.034 11.795L4.168 11.512C4.14296 11.5047 4.11708 11.5006 4.091 11.5H3.75ZM0 13.75C0 12.7554 0.395088 11.8016 1.09835 11.0983C1.80161 10.3951 2.75544 10 3.75 10H4.09C4.27667 10.0007 4.458 10.0293 4.634 10.086L5.5 10.369C6.96203 10.8463 8.53797 10.8463 10 10.369L10.866 10.086C11.041 10.029 11.225 10 11.409 10H11.75C12.7446 10 13.6984 10.3951 14.4017 11.0983C15.1049 11.8016 15.5 12.7554 15.5 13.75V14.938C15.5 15.692 14.954 16.334 14.21 16.455C9.93164 17.1534 5.56836 17.1534 1.29 16.455C0.930184 16.3958 0.603047 16.2108 0.366821 15.9331C0.130596 15.6553 0.000609175 15.3027 0 14.938V13.75Z" fill="#6E6E6E"/>
                            </svg>
                        </a>

                        
                        <div id="userDropdown"
                            class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50">

                            <div class="p-4 border-b">
                                @guest
                                    <p class="font-semibold text-gray-900">Welcome</p>
                                    <p class="text-sm text-gray-500">
                                        To access account and manage bookings.
                                    </p>
                                    <button class="mt-3 w-full bg-black text-white rounded-md py-2 text-sm font-medium">
                                        <a href="{{ route('login') }}" class="mt-3 text-center rounded-md py-2 text-sm font-medium">
                                            Login /
                                        </a>
                                        <a href="{{ route('register.role-selection') }}" class="mt-2 text-center rounded-md py-2 text-sm font-medium">
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
                                            <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                                            @if(!empty(auth()->user()->phone))
                                                <p class="text-xs opacity-80">+91{{ auth()->user()->phone }}</p>
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
                            @endauth

                            <div class="py-2 text-sm">
                                @auth
                                    <button
                                        onclick="openLogoutModal()"
                                        class="w-full text-left px-4 py-2 text-red-500 hover:bg-gray-100">
                                        Logout
                                    </button>
                                @endauth
                            </div>
                        </div>
                    </div> -->

                    

                

                    <!-- Cart Icon -->
                    <a href="javascript:void(0)"
                        onclick="openCart(event)"
                        data-auth="{{ auth()->check() ? '1' : '0' }}"
                        class="relative inline-block text-gray-400 hover:text-gray-600" title="Cart">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.00001 2.0625C8.55245 2.0625 8.12323 2.24029 7.80676 2.55676C7.4903 2.87322 7.31251 3.30245 7.31251 3.75V3.945C7.73026 3.9375 8.18851 3.9375 8.69251 3.9375H9.30826C9.81076 3.9375 10.2698 3.9375 10.6883 3.945V3.75C10.6883 3.52833 10.6446 3.30883 10.5597 3.10405C10.4749 2.89926 10.3505 2.7132 10.1937 2.55649C10.037 2.39978 9.85084 2.2755 9.64602 2.19074C9.44119 2.10598 9.22168 2.0624 9.00001 2.0625ZM11.8125 3.996V3.75C11.8125 3.00408 11.5162 2.28871 10.9887 1.76126C10.4613 1.23382 9.74593 0.9375 9.00001 0.9375C8.25409 0.9375 7.53872 1.23382 7.01127 1.76126C6.48382 2.28871 6.18751 3.00408 6.18751 3.75V3.996C6.08051 4.005 5.97701 4.01575 5.87701 4.02825C5.11951 4.122 4.49551 4.3185 3.96451 4.75875C3.43351 5.199 3.12601 5.7765 2.89501 6.504C2.67001 7.209 2.50051 8.11425 2.28751 9.2535L2.27176 9.336C1.97026 10.9432 1.73326 12.21 1.68901 13.2083C1.64401 14.232 1.79701 15.0795 2.37451 15.7748C2.95201 16.4708 3.75676 16.7768 4.77076 16.9215C5.76076 17.0625 7.04851 17.0625 8.68426 17.0625H9.31801C10.953 17.0625 12.2415 17.0625 13.2308 16.9215C14.2448 16.7768 15.0503 16.4708 15.6278 15.7748C16.2053 15.0788 16.3568 14.232 16.3125 13.2083C16.269 12.21 16.0313 10.9432 15.7298 9.336L15.7148 9.2535C15.501 8.11425 15.3308 7.20825 15.1073 6.504C14.8748 5.7765 14.5673 5.199 14.0363 4.75875C13.506 4.3185 12.8813 4.12125 12.1238 4.02825C12.0205 4.01554 11.917 4.00479 11.8133 3.996M6.01501 5.145C5.37376 5.22375 4.98601 5.373 4.68301 5.625C4.38076 5.8755 4.16251 6.22875 3.96601 6.84525C3.76576 7.47525 3.60751 8.31375 3.38551 9.498C3.07351 11.1607 2.85226 12.348 2.81251 13.2577C2.77351 14.1503 2.91751 14.6678 3.23926 15.057C3.56176 15.4447 4.04401 15.681 4.92901 15.807C5.82901 15.936 7.03801 15.9375 8.73001 15.9375H9.27001C10.9628 15.9375 12.1703 15.936 13.071 15.8077C13.956 15.681 14.4383 15.4447 14.7608 15.057C15.0833 14.6685 15.2265 14.151 15.1883 13.257C15.1478 12.3488 14.9265 11.1607 14.6145 9.498C14.3925 8.313 14.235 7.476 14.034 6.84525C13.8375 6.22875 13.62 5.8755 13.317 5.62425C13.014 5.373 12.627 5.22375 11.985 5.14425C11.328 5.06325 10.4753 5.0625 9.27001 5.0625H8.73001C7.52476 5.0625 6.67201 5.06325 6.01501 5.145Z" fill="black"/>
                        </svg>


                        @php
                            // Logged in → DB se count
                            // Guest → 0 rakho, JS LocalStorage se update karega
                            $cartCount = auth()->check()
                                ? \Illuminate\Support\Facades\DB::table('carts')
                                    ->join('hoardings', 'hoardings.id', '=', 'carts.hoarding_id')
                                    ->where('carts.user_id', auth()->id())
                                    ->whereNull('hoardings.deleted_at')
                                    ->count()
                                : 0;
                        @endphp

                        {{-- Logged in ke liye DB count, guest ke liye JS update karega --}}
                        <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 items-center justify-center cart-count {{ $cartCount > 0 ? 'flex' : 'hidden' }}">
                            {{ $cartCount }}
                        </span>
                    </a>

                    <!-- Bookmark / Wishlist Icon -->
                    <a href="javascript:void(0)"
                        onclick="openWishlist(event)"
                        class="relative inline-block text-gray-400 hover:text-gray-600"
                        data-auth="{{ auth()->check() ? '1' : '0' }}"
                        title="Wishlist">
                        <svg width="22" height="22" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.00033 3.39579C8.12719 2.61284 6.98289 2.20169 5.81112 2.2499C4.63935 2.29811 3.53269 2.80186 2.72681 3.65388C1.92094 4.5059 1.47952 5.63887 1.49657 6.8115C1.51362 7.98414 1.98778 9.1038 2.81808 9.93204L7.93983 15.053C8.22112 15.3342 8.60258 15.4922 9.00033 15.4922C9.39807 15.4922 9.77953 15.3342 10.0608 15.053L15.1826 9.93204C16.0129 9.1038 16.487 7.98414 16.5041 6.8115C16.5211 5.63887 16.0797 4.5059 15.2738 3.65388C14.468 2.80186 13.3613 2.29811 12.1895 2.2499C11.0178 2.20169 9.87346 2.61284 9.00033 3.39579ZM8.12133 4.62879L8.47008 4.97679C8.61072 5.11739 8.80145 5.19637 9.00033 5.19637C9.1992 5.19637 9.38993 5.11739 9.53058 4.97679L9.87933 4.62879C10.1561 4.34225 10.4871 4.11371 10.8531 3.95648C11.2191 3.79925 11.6128 3.7165 12.0111 3.71303C12.4095 3.70957 12.8045 3.78548 13.1732 3.93632C13.5419 4.08716 13.8768 4.30992 14.1585 4.5916C14.4402 4.87328 14.6629 5.20823 14.8138 5.57692C14.9646 5.94561 15.0405 6.34065 15.0371 6.73899C15.0336 7.13733 14.9509 7.53099 14.7936 7.897C14.6364 8.26301 14.4079 8.59404 14.1213 8.87079L9.00033 13.9925L3.87933 8.87079C3.33285 8.30498 3.03047 7.54717 3.0373 6.76058C3.04414 5.97399 3.35965 5.22155 3.91587 4.66533C4.47209 4.1091 5.22453 3.7936 6.01112 3.78676C6.79771 3.77993 7.55552 4.08231 8.12133 4.62879Z" fill="black"/>
                        </svg>



                        @php
                            // Logged in → DB se count
                            // Guest → 0 rakho, JS LocalStorage se update karega
                            $wishlistCount = auth()->check()
                                ? auth()->user()->wishlist()->whereHas('hoarding', fn($q) => $q->whereNull('deleted_at'))->count()
                                : 0;
                        @endphp

                        {{-- Logged in ke liye DB count, guest ke liye JS update karega --}}
                        <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 items-center justify-center shortlist-count {{ $wishlistCount > 0 ? 'flex' : 'hidden' }}">
                            {{ $wishlistCount }}
                        </span>
                    </a>

                    <div class="relative">
                        <button type="button" class="text-gray-700" onclick="toggleMobileMenu()">
                            <svg class="w-6 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>
                   

                </div>
            </div>

            <!-- Mobile Search Bar -->
            @if(!isset($hideSearch) || !$hideSearch)
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
            @endif

            <!-- Mobile Navigation Menu (Slide-in) -->
            <div id="mobile-menu"
                     class="absolute right-15 mt-3 w-72 bg-white border border-gray-200 rounded-xl shadow-lg hidden z-50">
                <div class="p-4 space-y-2 text-sm">
                    <!-- HEADER -->
                    <div class="flex items-center justify-between px-5   border-gray-200">
                        <a href="{{ route('home') }}" class="flex items-center space-x-1.5">
                                <x-optimized-image
                                    :src="route('brand.oohapp-logo')"
                                    alt="OOHApp  logo"
                                
                                    width="150"
                                    height="48"
                                    style="max-height:48px;object-fit:contain;"
                                />
                            </a>
                        <!-- <button onclick="toggleMobileMenu()" class="text-gray-500 hover:text-gray-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button> -->
                    </div>

                    <!-- MENU LINKS -->
                    <div class="flex flex-col px-5 py-4 space-y-2 text-sm">

                        <a href="{{ url('/#best-hoardings-section') }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-green-600 transition">
                            <svg width="22" height="22" fill="none" viewBox="0 0 22 22"><circle cx="11" cy="11" r="10" stroke="#2CB67D" stroke-width="2"/><text x="50%" y="55%" text-anchor="middle" fill="#2CB67D" font-size="8" font-family="Arial" dy=".3em">★</text></svg>
                            Best Hoardings
                        </a>
                        <a href="{{ url('/#top-spots-section') }}" class="flex items-center gap-3 px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-green-600 transition">
                            <svg width="22" height="22" fill="none" viewBox="0 0 22 22"><circle cx="11" cy="11" r="10" stroke="#2CB67D" stroke-width="2"/><text x="50%" y="55%" text-anchor="middle" fill="#2CB67D" font-size="8" font-family="Arial" dy=".3em">★</text></svg>
                            Top Spots
                        </a>

                        <a href="{{ route('search') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition">
                            <svg width="32" height="30" viewBox="0 0 32 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M31.3647 23.0245C31.7754 22.796 31.7754 22.425 31.3647 22.1964L15.4082 13.3121C15.1776 13.1994 14.9227 13.1406 14.6642 13.1406C14.4057 13.1406 14.1508 13.1994 13.9203 13.3121L6.81738 17.2657L7.51496 17.6549L11.1798 15.621L27.2326 24.5549L23.8719 26.4232L24.6158 26.7843L31.3647 23.0245Z" fill="url(#paint0_linear_726_59043)"/>
                                <path d="M4.36041 18.0427L7.85 16.1016L23.2035 24.6463L19.7139 26.5891L4.36041 18.0427Z" fill="#D0D0D0"/>
                                <path d="M7.49976 0.940005V17.6435L6.80219 17.2543V0.550781L7.49976 0.940005Z" fill="#D0D0D0"/>
                                <path d="M7.5032 0.94544V17.6489L7.85371 17.4535V0.75L7.5032 0.94544Z" fill="#B2B2B2"/>
                                <path d="M2.79633 16.3954V16.0078L18.1498 24.5525V24.9417L2.79633 16.3954Z" fill="#B2B2B2"/>
                                <path d="M2.79636 16.0092L6.28594 14.0664L21.6394 22.6128L18.1498 24.5539L2.79636 16.0092Z" fill="#D0D0D0"/>
                                <path d="M21.6347 22.9986L21.6313 22.6094L18.1417 24.5505V24.9397L21.6347 22.9986Z" fill="#8DB3CD"/>
                                <path d="M6.27835 17.9506L9.76794 16.0078L10.1167 16.2016L6.62714 18.1444L6.27835 17.9506Z" fill="#B2B2B2"/>
                                <path d="M10.1224 20.0818L13.612 18.1406L13.9607 18.3344L10.4712 20.2772L10.1224 20.0818Z" fill="#B2B2B2"/>
                                <path d="M14.3054 22.4138L17.795 20.4727L18.1438 20.6664L14.6542 22.6092L14.3054 22.4138Z" fill="#B2B2B2"/>
                                <path d="M24.4305 9.39778L7.68705 0.079594C7.285 -0.15394 6.18023 0.132595 4.87614 0.858042L2.97586 1.9164C1.33158 2.83066 0 4.0944 0 4.73206V5.12791L0.343632 4.93413L16.7452 14.0618L24.4305 9.39778Z" fill="#949291"/>
                                <path d="M24.2549 10.2743V26.9778L23.5573 26.5902V9.88672L24.2549 10.2743Z" fill="#B2B2B2"/>
                                <path d="M23.5958 12.8188L24.1147 12.5273V12.9166L23.5958 13.2081V12.8188Z" fill="#BFD2D8"/>
                                <path d="M24.1146 12.52L23.7263 12.3047L23.5958 12.3792V12.8115L24.1146 12.52Z" fill="#B2B2B2"/>
                                <path d="M23.5958 23.8462L24.1146 23.5547V23.9439L23.5958 24.2354V23.8462Z" fill="#BFD2D8"/>
                                <path d="M24.1147 23.5591L23.7264 23.3438L23.5958 23.4183V23.8506L24.1147 23.5591Z" fill="#718090"/>
                                <path d="M24.2583 10.2719V26.9754L24.6088 26.78V10.0781L24.2583 10.2719Z" fill="#B2B2B2"/>
                                <path d="M24.6054 10.0806L23.9078 9.69141L23.5573 9.88685L24.2549 10.2744L24.6054 10.0806Z" fill="#718090"/>
                                <path d="M16.7517 28.8212L16.0541 28.4337V15.2266L16.7517 15.6141V28.8212Z" fill="#D0D0D0"/>
                                <path d="M16.0541 15.2294L23.0332 11.3438L23.7308 11.733L16.7517 15.6169L16.0541 15.2294Z" fill="#D0D0D0"/>
                                <path d="M23.7341 11.7344V24.9415L16.7549 28.8254V15.6183L23.7341 11.7344Z" fill="#5D5D5D"/>
                                <path d="M17.1017 28.2415L23.3833 24.7451V12.3164L17.1017 15.8128V28.2415Z" fill="white"/>
                                <path d="M23.3822 24.7451L23.0334 24.5513V12.5102L23.3822 12.3164V24.7451Z" fill="#5D5D5D"/>
                                <path d="M23.3833 24.7446L23.0346 24.5508L17.1017 27.8534V28.241L23.3833 24.7446Z" fill="#5D5D5D"/>
                                <path d="M24.6069 10.0852V9.69601C24.6069 9.05338 23.2753 9.27366 21.631 10.1929L19.7307 11.2513C18.0864 12.1655 16.7549 13.4292 16.7549 14.0669V14.4561L24.6069 10.0852Z" fill="#D0D0D0"/>
                                <path d="M16.7504 14.4568V14.0676L0 4.74609V5.13366L16.7504 14.4568Z" fill="#D0D0D0"/>
                                <path d="M19.5438 28.8186L23.5557 26.5859L24.2532 26.9735L20.2413 29.2078L19.5438 28.8186Z" fill="url(#paint1_linear_726_59043)"/>
                                <defs>
                                <linearGradient id="paint0_linear_726_59043" x1="19.2451" y1="13.1406" x2="19.2451" y2="26.7843" gradientUnits="userSpaceOnUse">
                                <stop/>
                                <stop offset="1" stop-opacity="0"/>
                                </linearGradient>
                                <linearGradient id="paint1_linear_726_59043" x1="21.8985" y1="26.5859" x2="21.8985" y2="29.2078" gradientUnits="userSpaceOnUse">
                                <stop/>
                                <stop offset="1" stop-opacity="0"/>
                                </linearGradient>
                                </defs>
                                </svg>
                            HOARDINGS
                        </a>
                        <a href="{{ route('search', ['type' => 'dooh']) }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition">
                                <svg width="32" height="30" viewBox="0 0 32 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M31.2168 23.0245C31.6255 22.796 31.6255 22.425 31.2168 22.1964L15.3355 13.3121C15.106 13.1994 14.8524 13.1406 14.595 13.1406C14.3377 13.1406 14.0841 13.1994 13.8546 13.3121L6.78522 17.2657L7.4795 17.6549L11.1271 15.621L27.1041 24.5549L23.7592 26.4232L24.4997 26.7843L31.2168 23.0245Z" fill="url(#paint0_linear_726_59074)"/>
                                <path d="M4.33984 18.0427L7.81297 16.1016L23.094 24.6463L19.6209 26.5891L4.33984 18.0427Z" fill="#D0D0D0"/>
                                <path d="M7.46436 0.940005V17.6435L6.77008 17.2543V0.550781L7.46436 0.940005Z" fill="#D0D0D0"/>
                                <path d="M7.46783 0.94544V17.6489L7.81668 17.4535V0.75L7.46783 0.94544Z" fill="#B2B2B2"/>
                                <path d="M2.78314 16.3954V16.0078L18.0642 24.5525V24.9417L2.78314 16.3954Z" fill="#B2B2B2"/>
                                <path d="M2.7832 16.0092L6.25633 14.0664L21.5374 22.6128L18.0643 24.5539L2.7832 16.0092Z" fill="#D0D0D0"/>
                                <path d="M21.5327 22.9986L21.5293 22.6094L18.0562 24.5505V24.9397L21.5327 22.9986Z" fill="#8DB3CD"/>
                                <path d="M6.24872 17.9506L9.72184 16.0078L10.069 16.2016L6.59586 18.1444L6.24872 17.9506Z" fill="#B2B2B2"/>
                                <path d="M10.0746 20.0818L13.5478 18.1406L13.8949 18.3344L10.4218 20.2772L10.0746 20.0818Z" fill="#B2B2B2"/>
                                <path d="M14.2379 22.4138L17.711 20.4727L18.0582 20.6664L14.5851 22.6092L14.2379 22.4138Z" fill="#B2B2B2"/>
                                <path d="M24.3153 9.39778L7.65079 0.079594C7.25064 -0.15394 6.15107 0.132595 4.85314 0.858042L2.96182 1.9164C1.3253 2.83066 0 4.0944 0 4.73206V5.12791L0.342011 4.93413L16.6662 14.0618L24.3153 9.39778Z" fill="#949291"/>
                                <path d="M24.1404 10.2743V26.9778L23.4462 26.5902V9.88672L24.1404 10.2743Z" fill="#B2B2B2"/>
                                <path d="M23.4845 12.8188L24.0009 12.5273V12.9166L23.4845 13.2081V12.8188Z" fill="#BFD2D8"/>
                                <path d="M24.0009 12.52L23.6145 12.3047L23.4845 12.3792V12.8115L24.0009 12.52Z" fill="#B2B2B2"/>
                                <path d="M23.4845 23.8462L24.0009 23.5547V23.9439L23.4845 24.2354V23.8462Z" fill="#BFD2D8"/>
                                <path d="M24.0009 23.5591L23.6145 23.3438L23.4845 23.4183V23.8506L24.0009 23.5591Z" fill="#718090"/>
                                <path d="M24.1439 10.2719V26.9754L24.4927 26.78V10.0781L24.1439 10.2719Z" fill="#B2B2B2"/>
                                <path d="M24.4893 10.0806L23.795 9.69141L23.4462 9.88685L24.1404 10.2744L24.4893 10.0806Z" fill="#718090"/>
                                <path d="M16.6726 28.8212L15.9783 28.4337V15.2266L16.6726 15.6141V28.8212Z" fill="#D0D0D0"/>
                                <path d="M15.9783 15.2294L22.9246 11.3438L23.6189 11.733L16.6726 15.6169L15.9783 15.2294Z" fill="#D0D0D0"/>
                                <path d="M23.6221 11.7344V24.9415L16.6758 28.8254V15.6183L23.6221 11.7344Z" fill="#5D5D5D"/>
                                <path d="M17.0211 28.2415L23.273 24.7451V12.3164L17.0211 15.8128V28.2415Z" fill="#CE2A96"/>
                                <path d="M23.2719 24.7451L22.9248 24.5513V12.5102L23.2719 12.3164V24.7451Z" fill="#5D5D5D"/>
                                <path d="M23.273 24.7446L22.9259 24.5508L17.0211 27.8534V28.241L23.273 24.7446Z" fill="#5D5D5D"/>
                                <path d="M24.4908 10.0852V9.69601C24.4908 9.05338 23.1655 9.27366 21.529 10.1929L19.6377 11.2513C18.0011 12.1655 16.6758 13.4292 16.6758 14.0669V14.4561L24.4908 10.0852Z" fill="#D0D0D0"/>
                                <path d="M16.6713 14.4568V14.0676L0 4.74609V5.13366L16.6713 14.4568Z" fill="#D0D0D0"/>
                                <path d="M19.4516 28.8186L23.4446 26.5859L24.1389 26.9735L20.1459 29.2078L19.4516 28.8186Z" fill="url(#paint1_linear_726_59074)"/>
                                <defs>
                                <linearGradient id="paint0_linear_726_59074" x1="19.1543" y1="13.1406" x2="19.1543" y2="26.7843" gradientUnits="userSpaceOnUse">
                                <stop/>
                                <stop offset="1" stop-opacity="0"/>
                                </linearGradient>
                                <linearGradient id="paint1_linear_726_59074" x1="21.7952" y1="26.5859" x2="21.7952" y2="29.2078" gradientUnits="userSpaceOnUse">
                                <stop/>
                                <stop offset="1" stop-opacity="0"/>
                                </linearGradient>
                                </defs>
                                </svg>
                            DOOH
                        </a>
                        <a href="{{ route('search', ['type' => 'ooh']) }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition">
                                <svg width="32" height="30" viewBox="0 0 32 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M31.3647 23.0245C31.7754 22.796 31.7754 22.425 31.3647 22.1964L15.4082 13.3121C15.1776 13.1994 14.9227 13.1406 14.6642 13.1406C14.4057 13.1406 14.1508 13.1994 13.9203 13.3121L6.81738 17.2657L7.51496 17.6549L11.1798 15.621L27.2326 24.5549L23.8719 26.4232L24.6158 26.7843L31.3647 23.0245Z" fill="url(#paint0_linear_726_59043)"/>
                                <path d="M4.36041 18.0427L7.85 16.1016L23.2035 24.6463L19.7139 26.5891L4.36041 18.0427Z" fill="#D0D0D0"/>
                                <path d="M7.49976 0.940005V17.6435L6.80219 17.2543V0.550781L7.49976 0.940005Z" fill="#D0D0D0"/>
                                <path d="M7.5032 0.94544V17.6489L7.85371 17.4535V0.75L7.5032 0.94544Z" fill="#B2B2B2"/>
                                <path d="M2.79633 16.3954V16.0078L18.1498 24.5525V24.9417L2.79633 16.3954Z" fill="#B2B2B2"/>
                                <path d="M2.79636 16.0092L6.28594 14.0664L21.6394 22.6128L18.1498 24.5539L2.79636 16.0092Z" fill="#D0D0D0"/>
                                <path d="M21.6347 22.9986L21.6313 22.6094L18.1417 24.5505V24.9397L21.6347 22.9986Z" fill="#8DB3CD"/>
                                <path d="M6.27835 17.9506L9.76794 16.0078L10.1167 16.2016L6.62714 18.1444L6.27835 17.9506Z" fill="#B2B2B2"/>
                                <path d="M10.1224 20.0818L13.612 18.1406L13.9607 18.3344L10.4712 20.2772L10.1224 20.0818Z" fill="#B2B2B2"/>
                                <path d="M14.3054 22.4138L17.795 20.4727L18.1438 20.6664L14.6542 22.6092L14.3054 22.4138Z" fill="#B2B2B2"/>
                                <path d="M24.4305 9.39778L7.68705 0.079594C7.285 -0.15394 6.18023 0.132595 4.87614 0.858042L2.97586 1.9164C1.33158 2.83066 0 4.0944 0 4.73206V5.12791L0.343632 4.93413L16.7452 14.0618L24.4305 9.39778Z" fill="#949291"/>
                                <path d="M24.2549 10.2743V26.9778L23.5573 26.5902V9.88672L24.2549 10.2743Z" fill="#B2B2B2"/>
                                <path d="M23.5958 12.8188L24.1147 12.5273V12.9166L23.5958 13.2081V12.8188Z" fill="#BFD2D8"/>
                                <path d="M24.1146 12.52L23.7263 12.3047L23.5958 12.3792V12.8115L24.1146 12.52Z" fill="#B2B2B2"/>
                                <path d="M23.5958 23.8462L24.1146 23.5547V23.9439L23.5958 24.2354V23.8462Z" fill="#BFD2D8"/>
                                <path d="M24.1147 23.5591L23.7264 23.3438L23.5958 23.4183V23.8506L24.1147 23.5591Z" fill="#718090"/>
                                <path d="M24.2583 10.2719V26.9754L24.6088 26.78V10.0781L24.2583 10.2719Z" fill="#B2B2B2"/>
                                <path d="M24.6054 10.0806L23.9078 9.69141L23.5573 9.88685L24.2549 10.2744L24.6054 10.0806Z" fill="#718090"/>
                                <path d="M16.7517 28.8212L16.0541 28.4337V15.2266L16.7517 15.6141V28.8212Z" fill="#D0D0D0"/>
                                <path d="M16.0541 15.2294L23.0332 11.3438L23.7308 11.733L16.7517 15.6169L16.0541 15.2294Z" fill="#D0D0D0"/>
                                <path d="M23.7341 11.7344V24.9415L16.7549 28.8254V15.6183L23.7341 11.7344Z" fill="#5D5D5D"/>
                                <path d="M17.1017 28.2415L23.3833 24.7451V12.3164L17.1017 15.8128V28.2415Z" fill="white"/>
                                <path d="M23.3822 24.7451L23.0334 24.5513V12.5102L23.3822 12.3164V24.7451Z" fill="#5D5D5D"/>
                                <path d="M23.3833 24.7446L23.0346 24.5508L17.1017 27.8534V28.241L23.3833 24.7446Z" fill="#5D5D5D"/>
                                <path d="M24.6069 10.0852V9.69601C24.6069 9.05338 23.2753 9.27366 21.631 10.1929L19.7307 11.2513C18.0864 12.1655 16.7549 13.4292 16.7549 14.0669V14.4561L24.6069 10.0852Z" fill="#D0D0D0"/>
                                <path d="M16.7504 14.4568V14.0676L0 4.74609V5.13366L16.7504 14.4568Z" fill="#D0D0D0"/>
                                <path d="M19.5438 28.8186L23.5557 26.5859L24.2532 26.9735L20.2413 29.2078L19.5438 28.8186Z" fill="url(#paint1_linear_726_59043)"/>
                                <defs>
                                <linearGradient id="paint0_linear_726_59043" x1="19.2451" y1="13.1406" x2="19.2451" y2="26.7843" gradientUnits="userSpaceOnUse">
                                <stop/>
                                <stop offset="1" stop-opacity="0"/>
                                </linearGradient>
                                <linearGradient id="paint1_linear_726_59043" x1="21.8985" y1="26.5859" x2="21.8985" y2="29.2078" gradientUnits="userSpaceOnUse">
                                <stop/>
                                <stop offset="1" stop-opacity="0"/>
                                </linearGradient>
                                </defs>
                                </svg>

                            OOH
                        </a>

                        @auth
                            <a href="{{ $dashboardUrl }}"
                                class="flex items-center gap-5 px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 transition">
                                <svg width="20" height="20" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.5 4.25C3.5 3.69188 3.60993 3.13923 3.82351 2.6236C4.03709 2.10796 4.35015 1.63945 4.7448 1.2448C5.13944 0.850147 5.60796 0.537094 6.1236 0.323512C6.63923 0.109929 7.19188 0 7.75 0C8.30812 0 8.86077 0.109929 9.3764 0.323512C9.89204 0.537094 10.3606 0.850147 10.7552 1.2448C11.1499 1.63945 11.4629 2.10796 11.6765 2.6236C11.8901 3.13923 12 3.69188 12 4.25C12 5.37717 11.5522 6.45817 10.7552 7.2552C9.95817 8.05223 8.87717 8.5 7.75 8.5C6.62283 8.5 5.54183 8.05223 4.7448 7.2552C3.94777 6.45817 3.5 5.37717 3.5 4.25ZM7.75 1.5C7.02065 1.5 6.32118 1.78973 5.80546 2.30546C5.28973 2.82118 5 3.52065 5 4.25C5 4.97935 5.28973 5.67882 5.80546 6.19454C6.32118 6.71027 7.02065 7 7.75 7C8.47935 7 9.17882 6.71027 9.69454 6.19454C10.2103 5.67882 10.5 4.97935 10.5 4.25C10.5 3.52065 10.2103 2.82118 9.69454 2.30546C9.17882 1.78973 8.47935 1.5 7.75 1.5ZM3.75 11.5C3.15326 11.5 2.58097 11.7371 2.15901 12.159C1.73705 12.581 1.5 13.1533 1.5 13.75V14.938C1.5 14.956 1.513 14.972 1.531 14.975C5.65 15.647 9.851 15.647 13.969 14.975C13.9775 14.9731 13.9851 14.9684 13.9907 14.9617C13.9963 14.955 13.9996 14.9467 14 14.938V13.75C14 13.1533 13.7629 12.581 13.341 12.159C12.919 11.7371 12.3467 11.5 11.75 11.5H11.41C11.3832 11.5005 11.3567 11.5045 11.331 11.512L10.466 11.795C8.70118 12.3713 6.79882 12.3713 5.034 11.795L4.168 11.512C4.14296 11.5047 4.11708 11.5006 4.091 11.5H3.75ZM0 13.75C0 12.7554 0.395088 11.8016 1.09835 11.0983C1.80161 10.3951 2.75544 10 3.75 10H4.09C4.27667 10.0007 4.458 10.0293 4.634 10.086L5.5 10.369C6.96203 10.8463 8.53797 10.8463 10 10.369L10.866 10.086C11.041 10.029 11.225 10 11.409 10H11.75C12.7446 10 13.6984 10.3951 14.4017 11.0983C15.1049 11.8016 15.5 12.7554 15.5 13.75V14.938C15.5 15.692 14.954 16.334 14.21 16.455C9.93164 17.1534 5.56836 17.1534 1.29 16.455C0.930184 16.3958 0.603047 16.2108 0.366821 15.9331C0.130596 15.6553 0.000609175 15.3027 0 14.938V13.75Z" fill="#6E6E6E"/>
                                </svg>
                                DASHBOARD
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="flex items-center gap-5 px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 transition">
                                <svg width="20" height="20" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.5 4.25C3.5 3.69188 3.60993 3.13923 3.82351 2.6236C4.03709 2.10796 4.35015 1.63945 4.7448 1.2448C5.13944 0.850147 5.60796 0.537094 6.1236 0.323512C6.63923 0.109929 7.19188 0 7.75 0C8.30812 0 8.86077 0.109929 9.3764 0.323512C9.89204 0.537094 10.3606 0.850147 10.7552 1.2448C11.1499 1.63945 11.4629 2.10796 11.6765 2.6236C11.8901 3.13923 12 3.69188 12 4.25C12 5.37717 11.5522 6.45817 10.7552 7.2552C9.95817 8.05223 8.87717 8.5 7.75 8.5C6.62283 8.5 5.54183 8.05223 4.7448 7.2552C3.94777 6.45817 3.5 5.37717 3.5 4.25ZM7.75 1.5C7.02065 1.5 6.32118 1.78973 5.80546 2.30546C5.28973 2.82118 5 3.52065 5 4.25C5 4.97935 5.28973 5.67882 5.80546 6.19454C6.32118 6.71027 7.02065 7 7.75 7C8.47935 7 9.17882 6.71027 9.69454 6.19454C10.2103 5.67882 10.5 4.97935 10.5 4.25C10.5 3.52065 10.2103 2.82118 9.69454 2.30546C9.17882 1.78973 8.47935 1.5 7.75 1.5ZM3.75 11.5C3.15326 11.5 2.58097 11.7371 2.15901 12.159C1.73705 12.581 1.5 13.1533 1.5 13.75V14.938C1.5 14.956 1.513 14.972 1.531 14.975C5.65 15.647 9.851 15.647 13.969 14.975C13.9775 14.9731 13.9851 14.9684 13.9907 14.9617C13.9963 14.955 13.9996 14.9467 14 14.938V13.75C14 13.1533 13.7629 12.581 13.341 12.159C12.919 11.7371 12.3467 11.5 11.75 11.5H11.41C11.3832 11.5005 11.3567 11.5045 11.331 11.512L10.466 11.795C8.70118 12.3713 6.79882 12.3713 5.034 11.795L4.168 11.512C4.14296 11.5047 4.11708 11.5006 4.091 11.5H3.75ZM0 13.75C0 12.7554 0.395088 11.8016 1.09835 11.0983C1.80161 10.3951 2.75544 10 3.75 10H4.09C4.27667 10.0007 4.458 10.0293 4.634 10.086L5.5 10.369C6.96203 10.8463 8.53797 10.8463 10 10.369L10.866 10.086C11.041 10.029 11.225 10 11.409 10H11.75C12.7446 10 13.6984 10.3951 14.4017 11.0983C15.1049 11.8016 15.5 12.7554 15.5 13.75V14.938C15.5 15.692 14.954 16.334 14.21 16.455C9.93164 17.1534 5.56836 17.1534 1.29 16.455C0.930184 16.3958 0.603047 16.2108 0.366821 15.9331C0.130596 15.6553 0.000609175 15.3027 0 14.938V13.75Z" fill="#6E6E6E"/>
                                </svg>
                                LOGIN
                            </a>
                            <a href="{{ route('register.role-selection') }}"
                            class="flex items-center gap-5 px-3 py-2 rounded-md text-gray-700  transition">
                                <svg width="20" height="20" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.5 4.25C3.5 3.69188 3.60993 3.13923 3.82351 2.6236C4.03709 2.10796 4.35015 1.63945 4.7448 1.2448C5.13944 0.850147 5.60796 0.537094 6.1236 0.323512C6.63923 0.109929 7.19188 0 7.75 0C8.30812 0 8.86077 0.109929 9.3764 0.323512C9.89204 0.537094 10.3606 0.850147 10.7552 1.2448C11.1499 1.63945 11.4629 2.10796 11.6765 2.6236C11.8901 3.13923 12 3.69188 12 4.25C12 5.37717 11.5522 6.45817 10.7552 7.2552C9.95817 8.05223 8.87717 8.5 7.75 8.5C6.62283 8.5 5.54183 8.05223 4.7448 7.2552C3.94777 6.45817 3.5 5.37717 3.5 4.25ZM7.75 1.5C7.02065 1.5 6.32118 1.78973 5.80546 2.30546C5.28973 2.82118 5 3.52065 5 4.25C5 4.97935 5.28973 5.67882 5.80546 6.19454C6.32118 6.71027 7.02065 7 7.75 7C8.47935 7 9.17882 6.71027 9.69454 6.19454C10.2103 5.67882 10.5 4.97935 10.5 4.25C10.5 3.52065 10.2103 2.82118 9.69454 2.30546C9.17882 1.78973 8.47935 1.5 7.75 1.5ZM3.75 11.5C3.15326 11.5 2.58097 11.7371 2.15901 12.159C1.73705 12.581 1.5 13.1533 1.5 13.75V14.938C1.5 14.956 1.513 14.972 1.531 14.975C5.65 15.647 9.851 15.647 13.969 14.975C13.9775 14.9731 13.9851 14.9684 13.9907 14.9617C13.9963 14.955 13.9996 14.9467 14 14.938V13.75C14 13.1533 13.7629 12.581 13.341 12.159C12.919 11.7371 12.3467 11.5 11.75 11.5H11.41C11.3832 11.5005 11.3567 11.5045 11.331 11.512L10.466 11.795C8.70118 12.3713 6.79882 12.3713 5.034 11.795L4.168 11.512C4.14296 11.5047 4.11708 11.5006 4.091 11.5H3.75ZM0 13.75C0 12.7554 0.395088 11.8016 1.09835 11.0983C1.80161 10.3951 2.75544 10 3.75 10H4.09C4.27667 10.0007 4.458 10.0293 4.634 10.086L5.5 10.369C6.96203 10.8463 8.53797 10.8463 10 10.369L10.866 10.086C11.041 10.029 11.225 10 11.409 10H11.75C12.7446 10 13.6984 10.3951 14.4017 11.0983C15.1049 11.8016 15.5 12.7554 15.5 13.75V14.938C15.5 15.692 14.954 16.334 14.21 16.455C9.93164 17.1534 5.56836 17.1534 1.29 16.455C0.930184 16.3958 0.603047 16.2108 0.366821 15.9331C0.130596 15.6553 0.000609175 15.3027 0 14.938V13.75Z" fill="#6E6E6E"/>
                                </svg>
                                SIGN UP
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @include('components.customer.mobile_navbar')
           
    </div>
</header>

@push('scripts')
<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');

    if (menu.classList.contains('hidden')) {
        menu.classList.remove('hidden');
        setTimeout(() => {
            menu.classList.remove('opacity-0', 'scale-95');
            menu.classList.add('opacity-100', 'scale-100');
        }, 10);
    } else {
        menu.classList.add('opacity-0', 'scale-95');
        setTimeout(() => menu.classList.add('hidden'), 200);
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
    const isAuth = document.querySelector('[data-auth]')?.dataset?.auth === '1';

    if (!isAuth) {
        // Guest — LocalStorage IDs URL mein bhejo
        const saved = JSON.parse(localStorage.getItem('guest_wishlist') || '[]');
        if (saved.length === 0) {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'info',
                title: 'Wishlist is empty', showConfirmButton: false, timer: 1800
            });
            return;
        }
        window.location.href = "{{ route('shortlist') }}?ids=" + saved.join(',');
        return;
    }

    window.location.href = "{{ route('shortlist') }}";
}
</script>
<script>
function openCart(event) {
    event.preventDefault();
    const isAuth = event.currentTarget.dataset.auth === '1';

    if (!isAuth) {
        const saved = JSON.parse(localStorage.getItem('guest_cart') || '[]');
        if (saved.length === 0) {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'info',
                title: 'Shortlist is empty', showConfirmButton: false, timer: 1800
            });
            return;
        }
        window.location.href = "{{ route('cart.index') }}?ids=" + saved.join(',');
        return;
    }

    window.location.href = "{{ route('cart.index') }}";
}
</script>
@endpush

@push('scripts')
<script>
// Smooth scroll to section if hash is present on page load
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash) {
        const el = document.querySelector(window.location.hash);
        if (el) {
            setTimeout(() => {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 200);
        }
    }
});
</script>
@endpush
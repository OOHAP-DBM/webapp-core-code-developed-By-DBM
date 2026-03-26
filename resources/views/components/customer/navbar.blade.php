<header class="bg-white border-b border-gray-100 fixed top-0 left-0 w-full z-50">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="flex items-center justify-between h-16 gap-4 ">

            <!-- Mobile Menu Button (LEFT) + Logo -->
            <div class="flex items-center gap-3">
                <button type="button" class="md:hidden text-gray-700" onclick="toggleMobileMenu()">
                    <svg class="w-6 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center space-x-1.5">
                        <x-optimized-image
                            src="assets/images/logo/logo_image.jpeg"
                            :webp-srcset="asset('assets/images/logo/logo_image-300.webp') . ' 300w, ' . asset('assets/images/logo/logo_image-600.webp') . ' 600w'"
                            :srcset="asset('assets/images/logo/logo_image-300.jpeg') . ' 300w, ' . asset('assets/images/logo/logo_image.jpeg') . ' 600w'"
                            sizes="(max-width: 768px) 96px, 150px"
                            alt="OOHApp company logo"
                            width="600"
                            height="120"
                            class="w-24 md:w-[150px]"
                            loading="eager"
                            fetchpriority="high"
                        />
                    </a>
                </div>
            </div>

            <!-- Search Bar (Desktop & Tablet only) -->
           @if(!isset($hideSearch) || !$hideSearch)
<div class="hidden md:flex flex-1 justify-center px-6">
    @include('components.customer.home-search')
</div>
@endif

            <!-- Right Side Icons: User, Bookmark, Cart -->
            <div class="flex items-center space-x-4 lg:space-x-5">

                <!-- USER ICON with Dropdown -->
                <div class="relative inline-block" id="userDropdownWrapper">
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
                </div>

                <!-- Bookmark / Wishlist Icon -->
                <a href="javascript:void(0)"
                    onclick="openWishlist(event)"
                    class="relative inline-block text-gray-400 hover:text-gray-600"
                    data-auth="{{ auth()->check() ? '1' : '0' }}"
                    title="Wishlist">
                    <svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.5 0.75C2.877 0.75 0.75 3.01 0.75 5.797C0.75 11.375 9.75 17.75 9.75 17.75C9.75 17.75 18.75 11.375 18.75 5.797C18.75 2.344 16.623 0.75 14 0.75C12.14 0.75 10.53 1.886 9.75 3.54C8.97 1.886 7.36 0.75 5.5 0.75Z" stroke="#6E6E6E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
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

                <!-- Cart Icon -->
                <a href="javascript:void(0)"
                    onclick="openCart(event)"
                    data-auth="{{ auth()->check() ? '1' : '0' }}"
                    class="relative inline-block text-gray-400 hover:text-gray-600" title="Cart">
                       <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2.75C11.4033 2.75 10.831 2.98705 10.409 3.40901C9.98706 3.83097 9.75001 4.40326 9.75001 5V5.26C10.307 5.25 10.918 5.25 11.59 5.25H12.411C13.081 5.25 13.693 5.25 14.251 5.26V5C14.251 4.70444 14.1928 4.41178 14.0796 4.13873C13.9665 3.86568 13.8007 3.6176 13.5916 3.40866C13.3826 3.19971 13.1345 3.034 12.8614 2.92098C12.5883 2.80797 12.2956 2.74987 12 2.75ZM15.75 5.328V5C15.75 4.00544 15.3549 3.05161 14.6517 2.34835C13.9484 1.64509 12.9946 1.25 12 1.25C11.0054 1.25 10.0516 1.64509 9.34836 2.34835C8.6451 3.05161 8.25001 4.00544 8.25001 5V5.328C8.10734 5.34 7.96934 5.35433 7.83601 5.371C6.82601 5.496 5.99401 5.758 5.28601 6.345C4.57801 6.932 4.16801 7.702 3.86001 8.672C3.56001 9.612 3.33401 10.819 3.05001 12.338L3.02901 12.448C2.62701 14.591 2.31101 16.28 2.25201 17.611C2.19201 18.976 2.39601 20.106 3.16601 21.033C3.93601 21.961 5.00901 22.369 6.36101 22.562C7.68101 22.75 9.39801 22.75 11.579 22.75H12.424C14.604 22.75 16.322 22.75 17.641 22.562C18.993 22.369 20.067 21.961 20.837 21.033C21.607 20.105 21.809 18.976 21.75 17.611C21.692 16.28 21.375 14.591 20.973 12.448L20.953 12.338C20.668 10.819 20.441 9.611 20.143 8.672C19.833 7.702 19.423 6.932 18.715 6.345C18.008 5.758 17.175 5.495 16.165 5.371C16.0273 5.35406 15.8893 5.33972 15.751 5.328M8.02001 6.86C7.16501 6.965 6.64801 7.164 6.24401 7.5C5.84101 7.834 5.55001 8.305 5.28801 9.127C5.02101 9.967 4.81001 11.085 4.51401 12.664C4.09801 14.881 3.80301 16.464 3.75001 17.677C3.69801 18.867 3.89001 19.557 4.31901 20.076C4.74901 20.593 5.39201 20.908 6.57201 21.076C7.77201 21.248 9.38401 21.25 11.64 21.25H12.36C14.617 21.25 16.227 21.248 17.428 21.077C18.608 20.908 19.251 20.593 19.681 20.076C20.111 19.558 20.302 18.868 20.251 17.676C20.197 16.465 19.902 14.881 19.486 12.664C19.19 11.084 18.98 9.968 18.712 9.127C18.45 8.305 18.16 7.834 17.756 7.499C17.352 7.164 16.836 6.965 15.98 6.859C15.104 6.751 13.967 6.75 12.36 6.75H11.64C10.033 6.75 8.89601 6.751 8.02001 6.86Z" fill="#6E6E6E"/>
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
                    <span class="absolute -top-1.5 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 items-center justify-center cart-count {{ $cartCount > 0 ? 'flex' : 'hidden' }}">
                        {{ $cartCount }}
                    </span>
                </a>

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
            class="fixed top-0 left-0 h-full w-72 bg-white shadow-xl
                    transform -translate-x-full transition-transform duration-300 ease-in-out
                    z-50 md:hidden">

            <!-- HEADER -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                <a href="{{ route('home') }}" class="flex items-center">
                    <x-optimized-image
                        src="assets/images/logo/logo_image.jpeg"
                        :webp-srcset="asset('assets/images/logo/logo_image-300.webp') . ' 300w, ' . asset('assets/images/logo/logo_image-600.webp') . ' 600w'"
                        :srcset="asset('assets/images/logo/logo_image-300.jpeg') . ' 300w, ' . asset('assets/images/logo/logo_image.jpeg') . ' 600w'"
                        sizes="140px"
                        alt="OOHApp company logo"
                        width="600"
                        height="120"
                        class="w-[140px]"
                        loading="eager"
                    />
                </a>
                <button onclick="toggleMobileMenu()" class="text-gray-500 hover:text-gray-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- MENU LINKS -->
            <div class="flex flex-col px-5 py-4 space-y-2 text-sm">

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
</header>

@push('scripts')
<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    if (menu.classList.contains('-translate-x-full')) {
        menu.classList.remove('-translate-x-full');
        menu.classList.add('translate-x-0');
        document.body.style.overflow = 'hidden';
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
                title: 'Cart is empty', showConfirmButton: false, timer: 1800
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
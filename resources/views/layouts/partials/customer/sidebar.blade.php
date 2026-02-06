{{-- Customer Sidebar --}}
<aside class="hidden lg:block w-64 bg-white border-r border-gray-200 overflow-y-auto" id="sidebar">
<button
    id="mobile-btn-close"
    class="block md:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-md text-end"
>
    ✕
</button>
    <div class="p-6 text-center">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex items-center">
            <img
                src="{{ asset('assets/images/logo/logo_image.jpeg') }}"
                alt="OOHAPP"
                class="h-7 w-auto"
            >
        </a>
    </div>

    <div class="bg-white px-6 py-5">
        <h2 class="text-base font-semibold text-gray-900 mb-4 text-left">
            My Account
        </h2>

        <div class="flex items-center space-x-3">
            <div
                style="width:56px; height:56px; min-width:56px; min-height:56px; max-width:56px; max-height:56px; border-radius:50%;
                overflow:hidden;border:1px solid #d1d5db; flex-shrink:0;display:inline-block;"
            >
                 @if(auth()->user()->avatar)
                        <img
                            src="{{ str_starts_with(auth()->user()->avatar, 'http')
                                ? auth()->user()->avatar
                                : asset('storage/' . ltrim(auth()->user()->avatar, '/')) }}"
                            alt="Profile Image"
                            class="w-full h-full object-cover block"
                        >
                    @else
                        {{-- Default User Icon --}}
                        <svg
                            class="w-14 h-14 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804
                                M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                    @endif
            </div>

            <div>
                <p class="flex items-center gap-3 text-sm font-medium text-gray-900">
                    {{ Auth::user()->name }}
                    <!-- <span class="cursor-pointer">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.07692 7H5.38462C5.01739 7 4.66521 7.14588 4.40554 7.40554
                                    C4.14588 7.66521 4 8.01739 4 8.38462V14.6154
                                    C4 14.9826 4.14588 15.3348 4.40554 15.5945
                                    C4.66521 15.8541 5.01739 16 5.38462 16H11.6154
                                    C11.9826 16 12.3348 15.8541 12.5945 15.5945
                                    C12.8541 15.3348 13 14.9826 13 14.6154V13.9231"
                                stroke="#1E1B18" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12.6666 5.33339L14.6666 7.33337M15.5899 6.39005
                                    C15.8525 6.12749 16 5.77138 16 5.40006
                                    C16 5.02874 15.8525 4.67263 15.5899 4.41007
                                    C15.3274 4.14751 14.9713 4 14.5999 4
                                    C14.2286 4 13.8725 4.14751 13.61 4.41007
                                    L8 10V12H9.99998L15.5899 6.39005Z"
                                stroke="#1E1B18" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span> -->
                </p>
                <p class="text-xs text-yellow-600">
                    Member Since {{ Auth::user()->created_at->format('M Y') }}
                </p>
            </div>
        </div>

        <hr class="mt-5 border-gray-200">
    </div>

    <nav class="px-4 pb-4">
        <div class="space-y-1">
            <!-- Dashboard -->
            <a
                href="{{ route('customer.dashboard') }}"
                class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg
                {{ request()->routeIs('customer.dashboard') ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
            >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="4" y="4" width="7" height="7" rx="1.5" fill="currentColor"/>
                <path opacity="0.3" d="M9.5 13C10.3284 13 11 13.6716 11 14.5V18.5C11 19.3284 10.3284 20 9.5 20H5.5C4.67157 20 4 19.3284 4 18.5V14.5C4 13.6716 4.67157 13 5.5 13H9.5ZM18.5 13C19.3284 13 20 13.6716 20 14.5V18.5C20 19.3284 19.3284 20 18.5 20H14.5C13.6716 20 13 19.3284 13 18.5V14.5C13 13.6716 13.6716 13 14.5 13H18.5ZM18.5 4C19.3284 4 20 4.67157 20 5.5V9.5C20 10.3284 19.3284 11 18.5 11H14.5C13.6716 11 13 10.3284 13 9.5V5.5C13 4.67157 13.6716 4 14.5 4H18.5Z" fill="currentColor"/>
                </svg>
                Dashboard
            </a>

            {{-- My Profile Dropdown --}}
            <div
                x-data="{ open: {{ request()->routeIs('customer.profile*') ? 'true' : 'false' }} }"
                class="space-y-1"
                 >
                {{-- Parent --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="cursor-pointer w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium rounded-lg
                    {{ request()->routeIs('customer.profile*') ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    <div class="flex items-center gap-3">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12ZM12 14C9.33 14 4 15.34 4 18V20H20V18C20 15.34 14.67 14 12 14Z" fill="currentColor"/>
                        </svg>
                        My Profile
                    </div>

                    <svg
                        class="w-4 h-4 transition-transform flex-shrink-0"
                        :class="{ 'rotate-180': open }"
                        fill="none"
                        stroke="currentColor"   
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </button>

                {{-- Children --}}
                <div x-show="open" x-collapse class="space-y-1 mt-1">
                    <a
                        href="{{ route('customer.profile.index') }}"
                        class="flex items-center gap-3 px-6 py-1 text-sm font-medium rounded-md 
                        {{ request()->routeIs('customer.profile.index') ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                    >
                        <span class="text-xs">•</span>
                        Personal Info
                    </a>
                    <a
                        href="{{ route('customer.profile.billing') }}"
                        class="flex items-center gap-3 px-6 py-1 text-sm font-medium rounded-md
                        {{ request()->routeIs('customer.profile.billing') ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                        >
                        <span class="text-xs">•</span>
                        Billing Address
                    </a>

                </div>
            </div>

            <!-- Enquiries -->
            <a
                href="{{ route('customer.enquiries.index') }}"
                class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg
                {{ request()->routeIs('customer.enquiries.*') ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-50' }}"
            >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.275 20.25L20.75 16.8L19.7 15.75L17.275 18.125L16.3 17.15L15.25 18.225L17.275 20.25ZM6 9H18V7H6V9ZM18 23C16.6167 23 15.4377 22.5123 14.463 21.537C13.4883 20.5617 13.0007 19.3827 13 18C13 16.6167 13.4877 15.4377 14.463 14.463C15.4383 13.4883 16.6173 13.0007 18 13C19.3833 13 20.5627 13.4877 21.538 14.463C22.5133 15.4383 23.0007 16.6173 23 18C23 19.3833 22.5123 20.5627 21.537 21.538C20.5617 22.5133 19.3827 23.0007 18 23ZM3 22V5C3 4.45 3.196 3.97933 3.588 3.588C3.98 3.19667 4.45067 3.00067 5 3H19C19.55 3 20.021 3.196 20.413 3.588C20.805 3.98 21.0007 4.45067 21 5V11.675C20.6833 11.525 20.3583 11.4 20.025 11.3C19.6917 11.2 19.35 11.125 19 11.075V5H5V19.05H11.075C11.1583 19.5667 11.2877 20.0583 11.463 20.525C11.6383 20.9917 11.8673 21.4333 12.15 21.85L12 22L10.5 20.5L9 22L7.5 20.5L6 22L4.5 20.5L3 22ZM6 17H11.075C11.125 16.65 11.2 16.3083 11.3 15.975C11.4 15.6417 11.525 15.3167 11.675 15H6V17ZM6 13H13.1C13.7333 12.3833 14.471 11.8957 15.313 11.537C16.155 11.1783 17.0507 10.9993 18 11H6V13Z" fill="currentColor"/>
                </svg>
                Enquiries
            </a>

            <!-- LogOut -->
            <button
                type="button"
                onclick="openLogoutModal()"
                class="cursor-pointer block w-full flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg
                    text-gray-700 hover:text-red-700 hover:bg-red-100"
                >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 22C6.477 22 2 17.523 2 12C2 6.477 6.477 2 12 2C13.5527 1.99884 15.0842 2.35978 16.4729 3.05414C17.8617 3.74851 19.0693 4.75718 20 6H17.29C16.1352 4.98176 14.7112 4.31836 13.1887 4.0894C11.6663 3.86044 10.1101 4.07566 8.70689 4.70922C7.30371 5.34277 6.11315 6.36776 5.27807 7.66119C4.44299 8.95462 3.99887 10.4615 3.999 12.0011C3.99913 13.5407 4.4435 15.0475 5.27879 16.3408C6.11409 17.6341 7.30482 18.6589 8.7081 19.2922C10.1114 19.9255 11.6676 20.1405 13.19 19.9113C14.7125 19.6821 16.1364 19.0184 17.291 18H20.001C19.0702 19.243 17.8624 20.2517 16.4735 20.9461C15.0846 21.6405 13.5528 22.0013 12 22ZM19 16V13H11V11H19V8L24 12L19 16Z" fill="currentColor"/>
                </svg>
                LogOut
            </button>       
        </div>
    </nav>
</aside>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

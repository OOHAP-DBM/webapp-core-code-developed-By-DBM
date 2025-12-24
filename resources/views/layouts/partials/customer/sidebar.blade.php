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
                <p class="text-sm font-medium text-gray-900 text-left">
                    {{ Auth::user()->name }}
                </p>
                <p class="text-xs text-gray-500 italic">
                    Member Since {{ Auth::user()->created_at->format('M Y') }}
                </p>
            </div>
        </div>

        <hr class="mt-5 border-gray-200">
    </div>

    <nav class="px-4 pb-4">
        <div class="space-y-1 text-center">
            <!-- Dashboard -->
            <a
                href="{{ route('customer.dashboard') }}"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
                {{ request()->routeIs('customer.dashboard') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}"
            >
                Dashboard
            </a>

            <!-- Bookings -->
            <a
                href="{{ route('customer.bookings.index') }}"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
                {{ request()->routeIs('customer.bookings.*') && !request()->routeIs('customer.campaigns.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}"
            >
                Bookings
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
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                    {{ request()->routeIs('customer.profile*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    <div class="flex items-center">
                        My Profile
                    </div>

                    <svg
                        class="w-4 h-4 transition-transform"
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
                    <div x-show="open" x-collapse class="space-y-1">
                    <a
                        href="{{ route('customer.profile.index') }}"
                        class="block px-3 py-2 text-sm rounded-md
                        {{ request()->routeIs('customer.profile.index') ? 'bg-green-100 text-green-700' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        - Personal Info..
                    </a>

                    <a
                        href=""
                        class="block px-3 py-2 text-sm rounded-md
                        {{ request()->routeIs('customer.profile.billing') ? 'bg-green-100 text-green-700' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                       - Billing Address
                    </a>

                    <a
                        href=""
                        class="block px-3 py-2 text-sm rounded-md"
                    >
                       - Delete Account
                    </a>
                </div>
            </div>

            <!-- Enquiries -->
            <a
                href="{{ route('customer.enquiries.index') }}"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
                {{ request()->routeIs('customer.enquiries.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}"
            >
                Enquiries
            </a>

            <!-- Quotations -->
            <a
                href="{{ route('customer.quotations.index') }}"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
                {{ request()->routeIs('customer.quotations.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}"
            >
                Quotations
            </a>

            <!-- Legal -->
            <a
                href="{{ route('customer.campaigns.dashboard') }}"
                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
                {{ request()->routeIs('customer.campaigns.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}"
            >
                Legal
            </a>

            <form action="{{ route('logout') }}" method="post" class="text-left">
                @csrf
                <button
                    type="submit"
                    onclick="return confirm('are you sure want to logout...')"
                    style="cursor:pointer; color:Red;"
                    class="block w-full text-left px-3 py-2 text-sm font-medium rounded-lg
                    text-gray-700 hover:text-red-700 hover:bg-red-100"
                >
                    LogOut
                </button>
            </form>
        </div>
    </nav>
</aside>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

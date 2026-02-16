{{-- Vendor Navbar --}}
<header class="bg-white border-b border-gray-200 h-16 flex items-center px-6 gap-4">
    
    <!-- LEFT : TITLE -->
    <div class="flex-1 min-w-0">
        <div class="px-4 md:px-6 py-1 bg-white  ">
            <div class="flex flex-col">
                <h1 class="text-xl font-semibold text-gray-800">
                    @yield('page_title', 'Dashboard')
                </h1>
                @hasSection('breadcrumb')
                    <div class="mt-[-3px]">
                        @yield('breadcrumb')
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- RIGHT : ACTIONS (NEVER SHRINK) -->
    <div class="flex items-center space-x-4 flex-shrink-0">
        <button class="cursor-pointer">Help</button>
        
        {{-- Notification Dropdown --}}
        <div x-data="{ open: false, unreadCount: {{ auth()->user()->unreadNotifications->count() ?? 0 }} }" class="relative">
            <button @click="open = !open" type="button" class="relative p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <template x-if="unreadCount > 0">
                    <span class="absolute -top-0.5 -right-0 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center min-w-[1.25rem] min-h-[1.25rem]">
                        <span x-text="unreadCount"></span>
                    </span>
                </template>
            </button>

            {{-- Dropdown Panel --}}
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition
                 class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
                 style="display: none;">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    @forelse(auth()->user()->notifications->take(5) as $notification)
                        <a href="{{ route('notifications.open', $notification->id) }}"
                            class="group block px-4 py-3 border-b border-gray-100 transition-all duration-200
                                {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/70' }}
                                hover:bg-gray-50">

                            <div class="flex gap-3 items-start">

                                {{-- Notification Icon --}}
                                <div class="mt-1">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center
                                                {{ $notification->read_at ? 'bg-gray-100 text-gray-500' : 'bg-blue-100 text-blue-600' }}">
                                        <!-- Bell SVG -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .53-.21 1.04-.6 1.42L4 17h5m6 0a3 3 0 11-6 0"/>
                                        </svg>
                                    </div>
                                </div>

                                {{-- Text --}}
                                <div class="flex-1 min-w-0">

                                    {{-- Title --}}
                                    <p class="text-sm font-semibold
                                        {{ $notification->read_at ? 'text-gray-800' : 'text-blue-900' }}">
                                        {{ $notification->data['title'] ?? 'New Notification' }}
                                    </p>

                                    {{-- Message --}}
                                    <p class="text-xs text-gray-600 mt-1 leading-relaxed line-clamp-2">
                                        {{ $notification->data['message'] ?? '' }}
                                    </p>

                                    {{-- Time + SVG --}}
                                    <div class="flex items-center gap-1.5 mt-2 text-[11px] text-blue-400">

                                        <!-- Clock SVG -->
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-3.5 h-3.5 opacity-70"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2">
                                            <circle cx="12" cy="12" r="9"></circle>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 7v5l3 2"></path>
                                        </svg>

                                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>

                                </div>

                                {{-- Unread Dot --}}
                                @if(!$notification->read_at)
                                    <div class="mt-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 block animate-pulse"></span>
                                    </div>
                                @endif

                            </div>
                        </a>

                    @empty
                        <div class="px-4 py-8 text-center text-gray-500">
                            <p class="text-sm">No notifications yet</p>
                        </div>
                    @endforelse
                </div>
                <div class="px-4 py-3 border-t border-gray-200 text-center">
                    <a href="{{ route('vendor.notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        View all notifications
                    </a>
                </div>
            </div>
        </div>

        <div class="relative">
            <button type="button" class="hidden md:flex flex items-center space-x-3 focus:outline-none">
                <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                <!-- <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg> -->
                <svg class="w-5 h-5 text-gray-600 cursor-pointer" fill="currentColor" viewBox="0 0 20 20" id="vendorUserDropdownBtn">
                    <circle cx="10" cy="4" r="1.5"></circle>
                    <circle cx="10" cy="10" r="1.5"></circle>
                    <circle cx="10" cy="16" r="1.5"></circle>
                </svg>
            </button>
      <div
            id="vendorUserDropdown"
            class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50"
        >
            <a
                href="javascript:void(0)"
                class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50"
            >
                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                </svg>
                <span>Download Vendor App</span>
            </a>
            <a
                href="javascript:void(0)"
                class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50"
            >
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_9_21915)">
                <path d="M3 7.00063V4.37063C3.00781 3.85366 3.11755 3.34332 3.32294 2.86883C3.52832 2.39435 3.82532 1.96506 4.1969 1.60555C4.56848 1.24605 5.00735 0.963395 5.48836 0.773792C5.96936 0.58419 6.48306 0.491363 7 0.500632C7.51694 0.491363 8.03064 0.58419 8.51164 0.773792C8.99265 0.963395 9.43152 1.24605 9.8031 1.60555C10.1747 1.96506 10.4717 2.39435 10.6771 2.86883C10.8824 3.34332 10.9922 3.85366 11 4.37063V7.00063M9 12.2506C9.53043 12.2506 10.0391 12.0399 10.4142 11.6648C10.7893 11.2898 11 10.7811 11 10.2506V8.00063M9 12.2506C9 12.5822 8.8683 12.9001 8.63388 13.1345C8.39946 13.3689 8.08152 13.5006 7.75 13.5006H6.25C5.91848 13.5006 5.60054 13.3689 5.36612 13.1345C5.1317 12.9001 5 12.5822 5 12.2506C5 11.9191 5.1317 11.6012 5.36612 11.3667C5.60054 11.1323 5.91848 11.0006 6.25 11.0006H7.75C8.08152 11.0006 8.39946 11.1323 8.63388 11.3667C8.8683 11.6012 9 11.9191 9 12.2506ZM1.5 5.50063H2.5C2.63261 5.50063 2.75979 5.55331 2.85355 5.64708C2.94732 5.74085 3 5.86802 3 6.00063V9.00063C3 9.13324 2.94732 9.26042 2.85355 9.35419C2.75979 9.44795 2.63261 9.50063 2.5 9.50063H1.5C1.23478 9.50063 0.98043 9.39527 0.792893 9.20774C0.605357 9.0202 0.5 8.76585 0.5 8.50063V6.50063C0.5 6.23541 0.605357 5.98106 0.792893 5.79352C0.98043 5.60599 1.23478 5.50063 1.5 5.50063ZM12.5 9.50063H11.5C11.3674 9.50063 11.2402 9.44795 11.1464 9.35419C11.0527 9.26042 11 9.13324 11 9.00063V6.00063C11 5.86802 11.0527 5.74085 11.1464 5.64708C11.2402 5.55331 11.3674 5.50063 11.5 5.50063H12.5C12.7652 5.50063 13.0196 5.60599 13.2071 5.79352C13.3946 5.98106 13.5 6.23541 13.5 6.50063V8.50063C13.5 8.76585 13.3946 9.0202 13.2071 9.20774C13.0196 9.39527 12.7652 9.50063 12.5 9.50063Z" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
                </g>
                <defs>
                <clipPath id="clip0_9_21915">
                <rect width="14" height="14" fill="white"/>
                </clipPath>
                </defs>
                </svg>
                <span>Contact Customer Support</span>
            </a>
        </div>

        <button
            id="vendor-mobile-menu-btn"
            class="block md:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-md"
        >
            ☰
        </button>

    </div>
</header>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn  = document.getElementById('vendorUserDropdownBtn');
    const menu = document.getElementById('vendorUserDropdown');
    if (!btn || !menu) return;
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

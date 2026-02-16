{{-- Admin Navbar (Figma-aligned) --}}
<header class="admin-header sticky top-0 z-30 bg-white border-b border-gray-200 flex items-center justify-between px-6 h-20 shadow-sm">
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

    <div class="flex items-center gap-6">
        {{-- Notification Dropdown --}}
        <div x-data="{ open: false, unreadCount: {{ auth()->user()->unreadNotifications->count() ?? 0 }} }" class="relative hidden md:block">
            <button @click="open = !open" type="button" class="relative p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <template x-if="unreadCount > 0">
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center min-w-[1.25rem] min-h-[1.25rem]">
                        <span x-text="unreadCount"></span>
                    </span>
                </template>
            </button>

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
                    <a href="{{ route('admin.notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        View all notifications
                    </a>
                </div>
            </div>
        </div>
        
        <h2><div class="text-xs">Welcome</div>Administrator</h2>
        <button
            id="admin-mobile-menu-btn"
            class="block md:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-md"
        >
            ☰
        </button>
    </div>
</header>

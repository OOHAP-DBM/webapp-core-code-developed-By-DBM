{{-- Admin Navbar (Figma-aligned) --}}
<header class="admin-header sticky top-0 z-30 bg-white border-b border-gray-200 flex items-center justify-between px-6 h-20 shadow-sm">
    <div class="flex flex-col justify-center">
        <span class="font-medium tracking-wide mb-1">@yield('page-title', 'Dashboard')</span>
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
                        <a href="{{ $notification->data['action_url'] ?? '#' }}" 
                           class="block px-4 py-3 border-b border-gray-100 hover:bg-gray-50 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}">
                            <p class="text-sm text-gray-900">{{ $notification->data['title'] ?? 'New Notification' }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
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

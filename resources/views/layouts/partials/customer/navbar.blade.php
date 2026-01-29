{{-- Customer Navbar --}}
<header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6">

    {{-- SEARCH BAR (Desktop & Tablet only) --}}
    <p></p>    
    @include('components.customer.home-search')


    {{-- RIGHT SIDE ACTIONS --}}
    <div class="flex items-center space-x-2 -mr-2 md:mr-0">

        {{-- Notification Dropdown --}}
        <div x-data="{ open: false, unreadCount: {{ auth()->user()->unreadNotifications->count() ?? 0 }} }" class="relative hidden md:block">
            <button @click="open = !open" type="button" class="relative p-2 text-gray-400 hover:text-gray-600 rounded-full bg-gray-100 hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <template x-if="unreadCount > 0">
                    <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center min-w-[1.25rem] min-h-[1.25rem]">
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
                    <a href="{{ route('customer.notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        View all notifications
                    </a>
                </div>
            </div>
        </div>

      {{-- Mobile Search --}}
        <form action="{{ route('search') }}"
            method="GET"
            class="flex md:hidden items-center max-w-[60%] px-2 gap-1">

            <input
                type="text"
                name="location"
                placeholder="Search by city..."
                class="flex-1 px-2 py-2 text-sm border border-gray-300 rounded-md focus:outline-none"
            >

            <button type="submit"
                    class=" rounded-md text-gray-600">
                <!-- <svg width="40" height="40" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="42" height="42" rx="21" fill="#F0F0F0"/>
                <path d="M20 28C24.4183 28 28 24.4183 28 20C28 15.5817 24.4183 12 20 12C15.5817 12 12 15.5817 12 20C12 24.4183 15.5817 28 20 28Z" stroke="#939393" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M29.9984 29.9984L25.6484 25.6484" stroke="#939393" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg> -->
            </button>
        </form>

        {{-- User Icon --}}
        <div class="relative text-gray-400 ml-4 md:ml-0" id="user">
            <button type="button" class="flex items-center focus:outline-none">
                <svg class="w-6 h-6" fill="white" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </button>
        </div>
        {{-- Cart --}}
        <a href="{{ route('cart.index') }}" class="relative inline-block text-gray-400 hover:text-gray-600" title="Cart">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="white"
                xmlns="http://www.w3.org/2000/svg"
                class="">
                <path d="M3 3H5L7.5 14H17.5L20 6H6"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"/>
                <circle cx="9" cy="19" r="1.5" fill="currentColor"/>
                <circle cx="17" cy="19" r="1.5" fill="currentColor"/>
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
                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                    {{ $cartCount }}
                </span>
            @endif
        </a>

        {{-- Mobile Menu --}}
        <button id="mobile-menu-btn"
                class="block md:hidden p-2 -mt-2 text-gray-600 hover:bg-gray-100 rounded-md">
              <svg class="w-6 h-7" fill="none" stroke="currentColor" viewBox="0 0 19 19">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

    </div>
</header>

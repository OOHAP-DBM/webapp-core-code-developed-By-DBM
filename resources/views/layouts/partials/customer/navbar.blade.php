{{-- Customer Navbar --}}
<header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6">
<div class="flex-1 min-w-0">
        <h1
            class="
                text-sm sm:text-base lg:text-xl
                font-semibold text-gray-900
                truncate
            "
            title="@yield('title', 'Dashboard')"
        >
            @yield('title', 'Dashboard')
        </h1>
    </div>
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
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 18C4 16.9391 4.42143 15.9217 5.17157 15.1716C5.92172 14.4214 6.93913 14 8 14H16C17.0609 14 18.0783 14.4214 18.8284 15.1716C19.5786 15.9217 20 16.9391 20 18C20 18.5304 19.7893 19.0391 19.4142 19.4142C19.0391 19.7893 18.5304 20 18 20H6C5.46957 20 4.96086 19.7893 4.58579 19.4142C4.21071 19.0391 4 18.5304 4 18Z" stroke="#484848" stroke-linejoin="round"/>
                <path d="M12 10C13.6569 10 15 8.65685 15 7C15 5.34315 13.6569 4 12 4C10.3431 4 9 5.34315 9 7C9 8.65685 10.3431 10 12 10Z" stroke="#484848"/>
                </svg>
            </button>
        </div>
        {{-- Cart --}}
        <a href="{{ route('cart.index') }}" class="relative inline-block text-gray-400 hover:text-gray-600" title="Cart">
             <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15 15C15.5304 15 16.0391 15.2107 16.4142 15.5858C16.7893 15.9609 17 16.4696 17 17C17 17.5304 16.7893 18.0391 16.4142 18.4142C16.0391 18.7893 15.5304 19 15 19C14.4696 19 13.9609 18.7893 13.5858 18.4142C13.2107 18.0391 13 17.5304 13 17C13 16.4696 13.2107 15.9609 13.5858 15.5858C13.9609 15.2107 14.4696 15 15 15ZM15 16C14.7348 16 14.4804 16.1054 14.2929 16.2929C14.1054 16.4804 14 16.7348 14 17C14 17.2652 14.1054 17.5196 14.2929 17.7071C14.4804 17.8946 14.7348 18 15 18C15.2652 18 15.5196 17.8946 15.7071 17.7071C15.8946 17.5196 16 17.2652 16 17C16 16.7348 15.8946 16.4804 15.7071 16.2929C15.5196 16.1054 15.2652 16 15 16ZM6 15C6.53043 15 7.03914 15.2107 7.41421 15.5858C7.78929 15.9609 8 16.4696 8 17C8 17.5304 7.78929 18.0391 7.41421 18.4142C7.03914 18.7893 6.53043 19 6 19C5.46957 19 4.96086 18.7893 4.58579 18.4142C4.21071 18.0391 4 17.5304 4 17C4 16.4696 4.21071 15.9609 4.58579 15.5858C4.96086 15.2107 5.46957 15 6 15ZM6 16C5.73478 16 5.48043 16.1054 5.29289 16.2929C5.10536 16.4804 5 16.7348 5 17C5 17.2652 5.10536 17.5196 5.29289 17.7071C5.48043 17.8946 5.73478 18 6 18C6.26522 18 6.51957 17.8946 6.70711 17.7071C6.89464 17.5196 7 17.2652 7 17C7 16.7348 6.89464 16.4804 6.70711 16.2929C6.51957 16.1054 6.26522 16 6 16ZM17 3H3.27L5.82 9H14C14.33 9 14.62 8.84 14.8 8.6L17.8 4.6C17.93 4.43 18 4.22 18 4C18 3.73478 17.8946 3.48043 17.7071 3.29289C17.5196 3.10536 17.2652 3 17 3ZM14 10H5.87L5.1 11.56L5 12C5 12.2652 5.10536 12.5196 5.29289 12.7071C5.48043 12.8946 5.73478 13 6 13H17V14H6C5.46957 14 4.96086 13.7893 4.58579 13.4142C4.21071 13.0391 4 12.5304 4 12C3.9997 11.6607 4.08573 11.3269 4.25 11.03L4.97 9.56L1.34 1H0V0H2L2.85 2H17C17.5304 2 18.0391 2.21071 18.4142 2.58579C18.7893 2.96086 19 3.46957 19 4C19 4.5 18.83 4.92 18.55 5.26L15.64 9.15C15.28 9.66 14.68 10 14 10Z" fill="#484848"/>
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
                <span class="absolute -top-3 -right-3 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
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

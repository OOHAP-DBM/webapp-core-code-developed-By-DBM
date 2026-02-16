{{-- Customer Navbar --}}
<header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6">
    {{-- SEARCH BAR (Desktop & Tablet only) --}}
    <p></p>    
    @include('components.customer.home-search')


    {{-- RIGHT SIDE ACTIONS --}}
    <div class="flex items-center space-x-5 mr-4 md:mr-8 lg:mr-10 xl:mr-12">

        {{-- Notification Dropdown --}}
        <div x-data="{ open: false, unreadCount: {{ auth()->user()->unreadNotifications->count() ?? 0 }} }" class="relative hidden md:block">
            <button @click="open = !open" type="button" class="relative p-2 text-gray-400 hover:text-gray-600 rounded-full bg-gray-100 hover:bg-gray-100 cursor-pointer">
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
            <button type="button" class="flex items-center focus:outline-none cursor-pointer">
                <svg width="20" height="19" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.5 4.25C3.5 3.69188 3.60993 3.13923 3.82351 2.6236C4.03709 2.10796 4.35015 1.63945 4.7448 1.2448C5.13944 0.850147 5.60796 0.537094 6.1236 0.323512C6.63923 0.109929 7.19188 0 7.75 0C8.30812 0 8.86077 0.109929 9.3764 0.323512C9.89204 0.537094 10.3606 0.850147 10.7552 1.2448C11.1499 1.63945 11.4629 2.10796 11.6765 2.6236C11.8901 3.13923 12 3.69188 12 4.25C12 5.37717 11.5522 6.45817 10.7552 7.2552C9.95817 8.05223 8.87717 8.5 7.75 8.5C6.62283 8.5 5.54183 8.05223 4.7448 7.2552C3.94777 6.45817 3.5 5.37717 3.5 4.25ZM7.75 1.5C7.02065 1.5 6.32118 1.78973 5.80546 2.30546C5.28973 2.82118 5 3.52065 5 4.25C5 4.97935 5.28973 5.67882 5.80546 6.19454C6.32118 6.71027 7.02065 7 7.75 7C8.47935 7 9.17882 6.71027 9.69454 6.19454C10.2103 5.67882 10.5 4.97935 10.5 4.25C10.5 3.52065 10.2103 2.82118 9.69454 2.30546C9.17882 1.78973 8.47935 1.5 7.75 1.5ZM3.75 11.5C3.15326 11.5 2.58097 11.7371 2.15901 12.159C1.73705 12.581 1.5 13.1533 1.5 13.75V14.938C1.5 14.956 1.513 14.972 1.531 14.975C5.65 15.647 9.851 15.647 13.969 14.975C13.9775 14.9731 13.9851 14.9684 13.9907 14.9617C13.9963 14.955 13.9996 14.9467 14 14.938V13.75C14 13.1533 13.7629 12.581 13.341 12.159C12.919 11.7371 12.3467 11.5 11.75 11.5H11.41C11.3832 11.5005 11.3567 11.5045 11.331 11.512L10.466 11.795C8.70118 12.3713 6.79882 12.3713 5.034 11.795L4.168 11.512C4.14296 11.5047 4.11708 11.5006 4.091 11.5H3.75ZM0 13.75C0 12.7554 0.395088 11.8016 1.09835 11.0983C1.80161 10.3951 2.75544 10 3.75 10H4.09C4.27667 10.0007 4.458 10.0293 4.634 10.086L5.5 10.369C6.96203 10.8463 8.53797 10.8463 10 10.369L10.866 10.086C11.041 10.029 11.225 10 11.409 10H11.75C12.7446 10 13.6984 10.3951 14.4017 11.0983C15.1049 11.8016 15.5 12.7554 15.5 13.75V14.938C15.5 15.692 14.954 16.334 14.21 16.455C9.93164 17.1534 5.56836 17.1534 1.29 16.455C0.930184 16.3958 0.603047 16.2108 0.366821 15.9331C0.130596 15.6553 0.000609175 15.3027 0 14.938V13.75Z" fill="#6E6E6E"/>
                </svg>
            </button>
        </div>
        {{-- Cart --}}
        <a href="{{ route('cart.index') }}" class="relative inline-block text-gray-400 hover:text-gray-600" title="Cart">
             <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1.46 3.15018H16.674C18.052 3.15018 19.047 4.42018 18.669 5.69818L17.015 11.2982C16.76 12.1582 15.946 12.7502 15.02 12.7502H5.862C4.935 12.7502 4.12 12.1572 3.866 11.2982L1.46 3.15018ZM1.46 3.15018L0.75 0.750183M14.25 18.7502C14.6478 18.7502 15.0294 18.5921 15.3107 18.3108C15.592 18.0295 15.75 17.648 15.75 17.2502C15.75 16.8524 15.592 16.4708 15.3107 16.1895C15.0294 15.9082 14.6478 15.7502 14.25 15.7502C13.8522 15.7502 13.4706 15.9082 13.1893 16.1895C12.908 16.4708 12.75 16.8524 12.75 17.2502C12.75 17.648 12.908 18.0295 13.1893 18.3108C13.4706 18.5921 13.8522 18.7502 14.25 18.7502ZM6.25 18.7502C6.64782 18.7502 7.02936 18.5921 7.31066 18.3108C7.59196 18.0295 7.75 17.648 7.75 17.2502C7.75 16.8524 7.59196 16.4708 7.31066 16.1895C7.02936 15.9082 6.64782 15.7502 6.25 15.7502C5.85218 15.7502 5.47064 15.9082 5.18934 16.1895C4.90804 16.4708 4.75 16.8524 4.75 17.2502C4.75 17.648 4.90804 18.0295 5.18934 18.3108C5.47064 18.5921 5.85218 18.7502 6.25 18.7502Z" stroke="#6E6E6E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
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

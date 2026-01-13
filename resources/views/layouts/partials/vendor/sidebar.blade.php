{{-- Vendor Sidebar --}}
<aside
    id="vendor-sidebar"
    class="hidden md:block w-64 bg-white border-r border-gray-200 overflow-y-auto"
>
    <button
    id="vendor-mobile-btn-close"
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
         <div class="px-3 py-2 rounded-lg {{ request()->routeIs('vendor.profile.*') ? 'bg-[#00995c] text-white' : '' }}">
            <a href="{{ route('vendor.profile.edit') }}" class="flex items-center space-x-3">
                <div class="w-14 h-14 rounded-full border {{ request()->routeIs('vendor.profile.*') ? 'border-white' : 'border-gray-300' }} overflow-hidden flex items-center justify-center {{ request()->routeIs('vendor.profile.*') ? 'bg-opacity-20 bg-white' : 'bg-gray-100' }}">
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
                            class="w-14 h-14 {{ request()->routeIs('vendor.profile.*') ? 'stroke-gray-400' : 'stroke-gray-400' }}"
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
                    <p class="flex items-center gap-3 text-sm font-medium {{ request()->routeIs('vendor.profile.*') ? 'text-white' : 'text-gray-900' }}">
                        {{ Auth::user()->name }}
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                            class="{{ request()->routeIs('vendor.profile.*') ? 'stroke-white' : '' }}"
                        >
                            <path d="M6.07692 7H5.38462C5.01739 7 4.66521 7.14588 4.40554 7.40554
                                    C4.14588 7.66521 4 8.01739 4 8.38462V14.6154
                                    C4 14.9826 4.14588 15.3348 4.40554 15.5945
                                    C4.66521 15.8541 5.01739 16 5.38462 16H11.6154
                                    C11.9826 16 12.3348 15.8541 12.5945 15.5945
                                    C12.8541 15.3348 13 14.9826 13 14.6154V13.9231"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12.6666 5.33339L14.6666 7.33337M15.5899 6.39005
                                    C15.8525 6.12749 16 5.77138 16 5.40006
                                    C16 5.02874 15.8525 4.67263 15.5899 4.41007
                                    C15.3274 4.14751 14.9713 4 14.5999 4
                                    C14.2286 4 13.8725 4.14751 13.61 4.41007
                                    L8 10V12H9.99998L15.5899 6.39005Z"
                                stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </p>
                    <p class="text-xs {{ request()->routeIs('vendor.profile.*') ? 'text-white text-opacity-80' : 'text-yellow-600' }}">
                        Vendor
                    </p>
                </div>
            </a>
        </div>

        <hr class="mt-5 border-gray-200">
    </div>

    <nav class="px-4 pb-4">
        <div class="space-y-1">
            <a href="{{ route('vendor.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('vendor.dashboard') ? 'bg-[#00995c] text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="4" y="4" width="7" height="7" rx="1.5" fill="currentColor"/>
                <path opacity="0.3" d="M9.5 13C10.3284 13 11 13.6716 11 14.5V18.5C11 19.3284 10.3284 20 9.5 20H5.5C4.67157 20 4 19.3284 4 18.5V14.5C4 13.6716 4.67157 13 5.5 13H9.5ZM18.5 13C19.3284 13 20 13.6716 20 14.5V18.5C20 19.3284 19.3284 20 18.5 20H14.5C13.6716 20 13 19.3284 13 18.5V14.5C13 13.6716 13.6716 13 14.5 13H18.5ZM18.5 4C19.3284 4 20 4.67157 20 5.5V9.5C20 10.3284 19.3284 11 18.5 11H14.5C13.6716 11 13 10.3284 13 9.5V5.5C13 4.67157 13.6716 4 14.5 4H18.5Z" fill="#1E1B18"/>
                </svg>
                Dashboard
            </a>
            {{-- My Orders Dropdown --}}
            <!-- <div x-data="{ open: false }" class="space-y-1">

                {{-- Parent --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                        text-gray-700 hover:bg-gray-50"
                 >
                    <div class="flex items-center gap-3">
                        {{-- SVG Icon --}}
                        <svg width="21" height="24" viewBox="0 0 21 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.33333 24H18.6667C19.9535 24 21 22.9236 21 21.6V4.8C21 3.4764 19.9535 2.4 18.6667 2.4H16.3333V0H14V2.4H7V0H4.66667V2.4H2.33333C1.0465 2.4 0 3.4764 0 4.8V21.6C0 22.9236 1.0465 24 2.33333 24ZM18.6667 7.2L18.6678 21.6H2.33333V7.2H18.6667Z"
                                fill="#949291"/>
                        </svg>

                        <span>My Orders</span>
                    </div>

                    {{-- Arrow --}}
                    <svg
                        class="w-4 h-4 transition-transform duration-200"
                        :class="{ 'rotate-180': open }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Children --}}
                <div
                    x-show="open"
                    x-collapse
                    x-cloak
                    class="space-y-1"
                >
                    <a href=""
                    class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
                        - All Orders
                    </a>

                    <a href=""
                    class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
                        - New Orders
                    </a>

                    <a href=""
                    class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
                        - Completed Orders
                    </a>

                    <a href=""
                    class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
                        - Cancelled Orders
                    </a>
                </div>
            </div> -->


            {{-- My Hoardings Dropdown --}}
            <div
                x-data="{ open: @if(request()->routeIs('vendor.hoardings.*')) true @else false @endif }"
                class="space-y-1"
            >
                {{-- Parent --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                           {{ request()->routeIs('vendor.hoardings.*') ? 'bg-[#00995c] text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    <div class="flex items-center gap-3 ">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g style="mix-blend-mode:multiply">
                            <path d="M23.7667 18.4194C24.0779 18.2365 24.0779 17.9397 23.7667 17.7569L11.6741 10.6493C11.4993 10.5591 11.3062 10.5121 11.1103 10.5121C10.9143 10.5121 10.7212 10.5591 10.5465 10.6493L5.16357 13.8122L5.69223 14.1236L8.4696 12.4964L20.6351 19.6437L18.0882 21.1384L18.652 21.4272L23.7667 18.4194Z" fill="url(#paint0_linear_1077_4794)"/>
                            </g>
                            <g style="mix-blend-mode:multiply">
                            <path d="M3.30835 14.4359L5.95291 12.8829L17.5885 19.7189L14.9439 21.2731L3.30835 14.4359Z" fill="#CECECE"/>
                            </g>
                            <path d="M5.68466 0.753037V14.1161L5.15601 13.8048V0.44165L5.68466 0.753037Z" fill="#CECECE"/>
                            <path d="M5.68408 0.755843V14.1189L5.94971 13.9626V0.599487L5.68408 0.755843Z" fill="white"/>
                            <path d="M2.11914 13.1189V12.8088L13.7547 19.6448V19.9561L2.11914 13.1189Z" fill="white"/>
                            <path d="M2.11914 12.8089L4.7637 11.2546L16.3993 18.0919L13.7547 19.6448L2.11914 12.8089Z" fill="#CECECE"/>
                            <path d="M16.4008 18.3998L16.3982 18.0884L13.7537 19.6413V19.9527L16.4008 18.3998Z" fill="#8DB3CD"/>
                            <path d="M4.75903 14.3631L7.4036 12.8088L7.66793 12.9639L5.02336 14.5181L4.75903 14.3631Z" fill="white"/>
                            <path d="M7.66992 16.0665L10.3145 14.5135L10.5788 14.6686L7.93424 16.2229L7.66992 16.0665Z" fill="white"/>
                            <path d="M10.8425 17.9316L13.4871 16.3787L13.7514 16.5337L11.1069 18.088L10.8425 17.9316Z" fill="white"/>
                            <path d="M18.517 7.51839L5.82803 0.0636767C5.52334 -0.123155 4.68609 0.106078 3.6978 0.686449L2.25768 1.53315C1.01157 2.26458 0.00244141 3.27559 0.00244141 3.78573V4.10242L0.262861 3.94739L12.6927 11.2497L18.517 7.51839Z" fill="white"/>
                            <path d="M18.3802 8.21936V21.5825L17.8516 21.2724V7.9093L18.3802 8.21936Z" fill="white"/>
                            <g style="mix-blend-mode:multiply">
                            <path d="M17.8835 10.2543L18.2768 10.0211V10.3325L17.8835 10.5657V10.2543Z" fill="#BFD2D8"/>
                            </g>
                            <path d="M18.2768 10.0182L17.9825 9.84595L17.8835 9.90557V10.2514L18.2768 10.0182Z" fill="white"/>
                            <g style="mix-blend-mode:multiply">
                            <path d="M17.8835 19.0789L18.2768 18.8457V19.1571L17.8835 19.3903V19.0789Z" fill="#BFD2D8"/>
                            </g>
                            <path d="M18.2768 18.8478L17.9825 18.6755L17.8835 18.7352V19.081L18.2768 18.8478Z" fill="#718090"/>
                            <path d="M18.3794 8.21484V21.5779L18.645 21.4216V8.05981L18.3794 8.21484Z" fill="white"/>
                            <path d="M18.6458 8.06529L18.1172 7.75391L17.8516 7.91026L18.3802 8.22032L18.6458 8.06529Z" fill="#718090"/>
                            <path d="M12.6937 23.0558L12.165 22.7457V12.1798L12.6937 12.4899V23.0558Z" fill="#CECECE"/>
                            <path d="M12.165 12.1849L17.4542 9.07629L17.9828 9.38768L12.6937 12.4949L12.165 12.1849Z" fill="#CECECE"/>
                            <path d="M17.9844 9.38953V19.9555L12.6953 23.0627V12.4968L17.9844 9.38953Z" fill="#5D5D5D"/>
                            <path d="M12.9595 22.5937L17.7199 19.7965V9.85339L12.9595 12.6506V22.5937Z" fill="white"/>
                            <path d="M17.7162 19.7965L17.4519 19.6415V10.0084L17.7162 9.85339V19.7965Z" fill="#5D5D5D"/>
                            <path d="M17.7199 19.7952L17.4556 19.6401L12.9595 22.2823V22.5923L17.7199 19.7952Z" fill="#5D5D5D"/>
                            <path d="M18.6459 8.06603V7.75464C18.6459 7.24052 17.6368 7.41676 16.3907 8.15216L14.9505 8.99886C13.7044 9.73029 12.6953 10.7413 12.6953 11.2514V11.5628L18.6459 8.06603Z" fill="#CECECE"/>
                            <path d="M12.6942 11.5655V11.2541L0 3.79675V4.10681L12.6942 11.5655Z" fill="#CECECE"/>
                            <g style="mix-blend-mode:multiply">
                            <path d="M14.812 23.057L17.8524 21.2709L18.3811 21.5809L15.3407 23.3684L14.812 23.057Z" fill="url(#paint1_linear_1077_4794)"/>
                            </g>
                            <defs>
                            <linearGradient id="paint0_linear_1077_4794" x1="14.5818" y1="10.5121" x2="14.5818" y2="21.4272" gradientUnits="userSpaceOnUse">
                            <stop/>
                            <stop offset="1" stop-opacity="0"/>
                            </linearGradient>
                            <linearGradient id="paint1_linear_1077_4794" x1="16.5965" y1="21.2709" x2="16.5965" y2="23.3684" gradientUnits="userSpaceOnUse">
                            <stop/>
                            <stop offset="1" stop-opacity="0"/>
                            </linearGradient>
                            </defs>
                            </svg>
                        My Hoardings
                    </div>

                    <svg
                        class="w-4 h-4 transition-transform duration-200"
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
                <div
                    x-show="open"
                    x-collapse
                    x-cloak
                    class="space-y-1"
                >
                    <a
                        href="{{ route('vendor.hoardings.myHoardings') }}"
                        class="block px-6 py-2 text-sm rounded-md {{ request()->routeIs('vendor.hoardings.myHoardings') ? 'bg-[#00995c] text-white font-semibold' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        - My Hoardings
                    </a>

                    @if(auth()->user() && auth()->user()->hasRole('vendor'))
                    <a
                        href="{{ route('vendor.hoardings.add') }}"
                        class="block px-6 py-2 text-sm rounded-md {{ request()->routeIs('vendor.hoardings.add') || request()->is('vendor/hoardings/add') || request()->is('vendor/hoardings*') ? 'bg-[#00995c] text-white font-semibold' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        <span class="inline-flex items-center gap-2">
                            - Add Hoardings
                        </span>
                    </a>
                    @endif

                    <!-- <a
                        href=""
                        class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
                    >
                        - Category
                    </a> -->
                </div>
            </div>

            <!-- <a href="" class=" gap-3 flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('vendor.message.*') ? 'bg-[#00995c]/10 text-[#00995c]' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_1077_4841)">
                <path d="M9.23077 0C4.19723 0 0 3.46154 0 7.84615C0 10.0892 1.22769 12.0443 2.97138 13.4714C2.85879 14.2297 2.55051 14.9455 2.07692 15.5483C1.88365 15.7957 1.68168 16.0361 1.47138 16.2692C1.36252 16.384 1.26571 16.5095 1.18246 16.644C1.12985 16.7298 1.04769 16.8258 1.00985 17.0197C0.971077 17.2126 1.02369 17.5302 1.18246 17.7692L1.29785 17.9714L1.52862 18.0868C2.33631 18.4902 3.20862 18.4191 4.00985 18.2022C4.81015 17.9843 5.58 17.6114 6.31754 17.2209C7.05415 16.8314 7.75477 16.4234 8.30769 16.1252C8.38523 16.0837 8.43508 16.0735 8.50985 16.0385C9.96554 18.0397 12.6314 19.3846 15.6055 19.3846C15.6342 19.3883 15.6609 19.3846 15.6923 19.3846C16.8923 19.3846 20.7692 23.3483 23.0769 21.7791C23.1692 21.4108 21.048 20.4868 20.9418 17.7406C22.7483 16.464 23.9142 14.5652 23.9142 12.4615C23.9142 9.34892 21.444 6.77723 18.1449 5.856C17.1009 2.45908 13.4714 0 9.23077 0ZM9.23077 1.84615C13.428 1.84615 16.6154 4.66154 16.6154 7.84615C16.6154 11.0308 13.428 13.8462 9.23077 13.8462C8.48123 13.8462 8.05108 14.1526 7.44185 14.4812C6.83262 14.8089 6.13385 15.216 5.45169 15.5769C4.86092 15.8889 4.29785 16.1289 3.77908 16.2978C4.284 15.5686 4.81108 14.6095 4.90338 13.2692L4.93292 12.7495L4.5 12.4329C2.85508 11.28 1.84615 9.62123 1.84615 7.84615C1.84615 4.66154 5.03354 1.84615 9.23077 1.84615Z" fill="#949291"/>
                </g>
                <defs>
                <clipPath id="clip0_1077_4841">
                <rect width="24" height="24" fill="white"/>
                </clipPath>
                </defs>
                </svg>
                Messenger
            </a> -->

            {{-- My Portal Toggle --}}
{{--       
         <div
                x-data="{ enabled: false }"
                class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 gap-3"
                >
                <div class="flex items-center gap-3 text-sm font-medium text-gray-700">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g style="mix-blend-mode:multiply">
                    <path d="M23.7667 18.4194C24.0779 18.2365 24.0779 17.9397 23.7667 17.7569L11.6741 10.6493C11.4993 10.5591 11.3062 10.5121 11.1103 10.5121C10.9143 10.5121 10.7212 10.5591 10.5465 10.6493L5.16357 13.8122L5.69223 14.1236L8.4696 12.4964L20.6351 19.6437L18.0882 21.1384L18.652 21.4272L23.7667 18.4194Z" fill="url(#paint0_linear_1077_4850)"/>
                    </g>
                    <g style="mix-blend-mode:multiply">
                    <path d="M3.30835 14.4359L5.95291 12.8829L17.5885 19.7189L14.9439 21.2731L3.30835 14.4359Z" fill="#D0D0D0"/>
                    </g>
                    <path d="M5.68466 0.753037V14.1161L5.15601 13.8048V0.44165L5.68466 0.753037Z" fill="#D0D0D0"/>
                    <path d="M5.68408 0.755843V14.1189L5.94971 13.9626V0.599487L5.68408 0.755843Z" fill="#B2B2B2"/>
                    <path d="M2.11914 13.1189V12.8088L13.7547 19.6448V19.9561L2.11914 13.1189Z" fill="#B2B2B2"/>
                    <path d="M2.11914 12.8089L4.7637 11.2546L16.3993 18.0919L13.7547 19.6448L2.11914 12.8089Z" fill="#D0D0D0"/>
                    <path d="M16.4008 18.3998L16.3982 18.0884L13.7537 19.6413V19.9527L16.4008 18.3998Z" fill="#8DB3CD"/>
                    <path d="M4.75903 14.3631L7.4036 12.8088L7.66793 12.9639L5.02336 14.5181L4.75903 14.3631Z" fill="#B2B2B2"/>
                    <path d="M7.66992 16.0665L10.3145 14.5135L10.5788 14.6686L7.93424 16.2229L7.66992 16.0665Z" fill="#B2B2B2"/>
                    <path d="M10.8425 17.9316L13.4871 16.3787L13.7514 16.5337L11.1069 18.088L10.8425 17.9316Z" fill="#B2B2B2"/>
                    <path d="M18.517 7.51839L5.82803 0.0636767C5.52334 -0.123155 4.68609 0.106078 3.6978 0.686449L2.25768 1.53315C1.01157 2.26458 0.00244141 3.27559 0.00244141 3.78573V4.10242L0.262861 3.94739L12.6927 11.2497L18.517 7.51839Z" fill="#949291"/>
                    <path d="M18.3802 8.21936V21.5825L17.8516 21.2724V7.9093L18.3802 8.21936Z" fill="#B2B2B2"/>
                    <g style="mix-blend-mode:multiply">
                    <path d="M17.8835 10.2543L18.2768 10.0211V10.3325L17.8835 10.5657V10.2543Z" fill="#BFD2D8"/>
                    </g>
                    <path d="M18.2768 10.0182L17.9825 9.84595L17.8835 9.90557V10.2514L18.2768 10.0182Z" fill="#B2B2B2"/>
                    <g style="mix-blend-mode:multiply">
                    <path d="M17.8835 19.0789L18.2768 18.8457V19.1571L17.8835 19.3903V19.0789Z" fill="#BFD2D8"/>
                    </g>
                    <path d="M18.2768 18.8478L17.9825 18.6755L17.8835 18.7352V19.081L18.2768 18.8478Z" fill="#718090"/>
                    <path d="M18.3794 8.21484V21.5779L18.645 21.4216V8.05981L18.3794 8.21484Z" fill="#B2B2B2"/>
                    <path d="M18.6458 8.06529L18.1172 7.75391L17.8516 7.91026L18.3802 8.22032L18.6458 8.06529Z" fill="#718090"/>
                    <path d="M12.6937 23.0558L12.165 22.7457V12.1798L12.6937 12.4899V23.0558Z" fill="#D0D0D0"/>
                    <path d="M12.165 12.1849L17.4542 9.07629L17.9828 9.38768L12.6937 12.4949L12.165 12.1849Z" fill="#D0D0D0"/>
                    <path d="M17.9844 9.38953V19.9555L12.6953 23.0627V12.4968L17.9844 9.38953Z" fill="#5D5D5D"/>
                    <path d="M12.9595 22.5937L17.7199 19.7965V9.85339L12.9595 12.6506V22.5937Z" fill="white"/>
                    <path d="M17.7162 19.7965L17.4519 19.6415V10.0084L17.7162 9.85339V19.7965Z" fill="#5D5D5D"/>
                    <path d="M17.7199 19.7952L17.4556 19.6401L12.9595 22.2823V22.5923L17.7199 19.7952Z" fill="#5D5D5D"/>
                    <path d="M18.6459 8.06603V7.75464C18.6459 7.24052 17.6368 7.41676 16.3907 8.15216L14.9505 8.99886C13.7044 9.73029 12.6953 10.7413 12.6953 11.2514V11.5628L18.6459 8.06603Z" fill="#D0D0D0"/>
                    <path d="M12.6942 11.5655V11.2541L0 3.79675V4.10681L12.6942 11.5655Z" fill="#D0D0D0"/>
                    <g style="mix-blend-mode:multiply">
                    <path d="M14.812 23.057L17.8524 21.2709L18.3811 21.5809L15.3407 23.3684L14.812 23.057Z" fill="url(#paint1_linear_1077_4850)"/>
                    </g>
                    <defs>
                    <linearGradient id="paint0_linear_1077_4850" x1="14.5818" y1="10.5121" x2="14.5818" y2="21.4272" gradientUnits="userSpaceOnUse">
                    <stop/>
                    <stop offset="1" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="paint1_linear_1077_4850" x1="16.5965" y1="21.2709" x2="16.5965" y2="23.3684" gradientUnits="userSpaceOnUse">
                    <stop/>
                    <stop offset="1" stop-opacity="0"/>
                    </linearGradient>
                    </defs>
                    </svg>
                    My Portal
                </div>

                <!-- Toggle -->
                <button
                    type="button"
                    @click="enabled = !enabled"
                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200"
                    :class="enabled ? 'bg-green-600' : 'bg-gray-300'"
                >
                    <span
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200"
                        :class="enabled ? 'translate-x-4' : 'translate-x-1'"
                    ></span>
                </button>
        </div> --}}

            {{-- Earning Dashboard Dropdown --}}
            <!-- <div
                x-data="{ open: @if(request()->routeIs('vendor.earning.*') || request()->routeIs('vendor.dashboard.*')) true @else false @endif }"
                class="space-y-1"
              >
                {{-- Parent --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                           {{ request()->routeIs('vendor.earning.*') || request()->routeIs('vendor.dashboard.*') ? 'bg-[#00995c] text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    <div class="flex items-center gap-3">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14.0109 22.8605C13.3125 22.8464 12.8109 22.7105 12.4781 22.4667L7.91715 20.0245L5.38121 16.575C4.84683 15.75 5.22652 14.1563 6.89527 15.1781L8.7234 17.5875C11.0437 19.5886 15.7781 18.6703 13.5328 14.6531C12.4312 12.3281 12.9468 11.1938 14.3671 10.7156L15.014 12.8906C16.1343 15.4125 18.225 15.8672 18.1406 17.9016L23.1656 17.5406L23.1187 22.7948L14.0109 22.8605ZM11.475 18.0141C10.6593 18.0281 9.85777 17.6906 9.27652 17.2031L7.44371 14.7938C7.94527 14.3953 8.45152 14.6109 8.95308 15.0938C9.54371 14.7938 10.0031 15.2578 10.3781 16.1859C10.5328 16.8188 10.7625 17.2641 11.475 18.0141ZM7.74371 13.5141C7.72496 13.5141 7.70152 13.5141 7.68277 13.5094C7.52808 13.4766 7.34527 13.3125 7.2234 12.9375C7.09683 12.5625 7.06871 12.0281 7.18121 11.4563C7.29371 10.8891 7.52808 10.4063 7.79058 10.1063C8.04371 9.81094 8.27808 9.72657 8.43277 9.75938C8.59214 9.78751 8.77027 9.95625 8.89215 10.3266C9.01871 10.7016 9.05152 11.2406 8.93902 11.8078C8.82183 12.3797 8.58746 12.8625 8.32965 13.1578C8.10465 13.4203 7.8984 13.5141 7.74371 13.5141ZM11.6156 8.50782C11.2453 8.50313 10.7812 8.38594 10.3265 8.16563C9.80621 7.90782 9.3984 7.55157 9.17808 7.22344C8.95777 6.90001 8.93902 6.65626 9.00933 6.51094C9.07965 6.37032 9.2859 6.23438 9.67965 6.21563C10.0734 6.19219 10.6031 6.3 11.1234 6.55782C11.6437 6.81563 12.0515 7.16719 12.2718 7.49532C12.4921 7.81876 12.5109 8.06719 12.4406 8.20782C12.3703 8.35313 12.164 8.48438 11.7703 8.50313C11.7187 8.50782 11.6718 8.50782 11.6156 8.50782ZM7.04058 4.02657C6.82027 4.02188 6.61871 3.98907 6.44058 3.93751C6.04215 3.82032 5.81246 3.60938 5.73746 3.36094C5.65777 3.10782 5.73746 2.80782 6.00465 2.48438C6.27652 2.16563 6.73121 1.86094 7.29371 1.69219C7.85152 1.52345 8.39527 1.52813 8.7984 1.65001C9.20152 1.76719 9.43121 1.97813 9.50621 2.22657C9.58121 2.47969 9.50621 2.77969 9.23433 3.10313C8.96715 3.42188 8.51246 3.72657 7.94996 3.89532C7.63121 3.98907 7.32183 4.03126 7.04058 4.02657Z" fill="#949291"/>
                        </svg>
                        Earning Dashboard
                    </div>

                    <svg
                        class="w-4 h-4 transition-transform duration-200"
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
            <div
                    x-show="open"
                    x-collapse
                    x-cloak
                    class="space-y-1"
                    >
                    <a
                        href=""
                        class="block px-6 py-2 text-sm rounded-md {{ request()->routeIs('vendor.earning.overview') ? 'bg-[#00995c] text-white font-semibold' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        - Overview
                    </a>

                    <a
                        href=""
                        class="block px-6 py-2 text-sm rounded-md {{ request()->routeIs('vendor.earning.monthly') ? 'bg-[#00995c] text-white font-semibold' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        - Monthly Earnings
                    </a>

                    <a
                        href=""
                        class="block px-6 py-2 text-sm rounded-md {{ request()->routeIs('vendor.earning.payout') ? 'bg-[#00995c] text-white font-semibold' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        - Payout History
                    </a>
                </div>
            </div> -->

            <!-- <hr class="mt-5 border-gray-200">
            <p class="text-center font-semibold text-gray-500 text-xs uppercase tracking-wide my-3">
                POS & bookings
            </p>

            <a href="" class=" gap-3 flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('vendor.pos.*') ? 'bg-[#00995c]/10 text-[#00995c]' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M14.275 17.25L17.75 13.8L16.7 12.75L14.275 15.125L13.3 14.15L12.25 15.225L14.275 17.25ZM3 6H15V4H3V6ZM15 20C13.6167 20 12.4377 19.5123 11.463 18.537C10.4883 17.5617 10.0007 16.3827 10 15C10 13.6167 10.4877 12.4377 11.463 11.463C12.4383 10.4883 13.6173 10.0007 15 10C16.3833 10 17.5627 10.4877 18.538 11.463C19.5133 12.4383 20.0007 13.6173 20 15C20 16.3833 19.5123 17.5627 18.537 18.538C17.5617 19.5133 16.3827 20.0007 15 20ZM0 19V2C0 1.45 0.196 0.979333 0.588 0.588C0.98 0.196667 1.45067 0.000666667 2 0H16C16.55 0 17.021 0.196 17.413 0.588C17.805 0.98 18.0007 1.45067 18 2V8.675C17.6833 8.525 17.3583 8.4 17.025 8.3C16.6917 8.2 16.35 8.125 16 8.075V2H2V16.05H8.075C8.15833 16.5667 8.28767 17.0583 8.463 17.525C8.63833 17.9917 8.86733 18.4333 9.15 18.85L9 19L7.5 17.5L6 19L4.5 17.5L3 19L1.5 17.5L0 19ZM3 14H8.075C8.125 13.65 8.2 13.3083 8.3 12.975C8.4 12.6417 8.525 12.3167 8.675 12H3V14ZM3 10H10.1C10.7333 9.38333 11.471 8.89567 12.313 8.537C13.155 8.17833 14.0507 7.99933 15 8H3V10Z" fill="#949291"/>
                </svg>
                POS Dashobard
            </a>
            <a href="" class=" gap-3 flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('vendor.booknow.*') && !request()->routeIs('vendor.pipeline.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M14.275 17.25L17.75 13.8L16.7 12.75L14.275 15.125L13.3 14.15L12.25 15.225L14.275 17.25ZM3 6H15V4H3V6ZM15 20C13.6167 20 12.4377 19.5123 11.463 18.537C10.4883 17.5617 10.0007 16.3827 10 15C10 13.6167 10.4877 12.4377 11.463 11.463C12.4383 10.4883 13.6173 10.0007 15 10C16.3833 10 17.5627 10.4877 18.538 11.463C19.5133 12.4383 20.0007 13.6173 20 15C20 16.3833 19.5123 17.5627 18.537 18.538C17.5617 19.5133 16.3827 20.0007 15 20ZM0 19V2C0 1.45 0.196 0.979333 0.588 0.588C0.98 0.196667 1.45067 0.000666667 2 0H16C16.55 0 17.021 0.196 17.413 0.588C17.805 0.98 18.0007 1.45067 18 2V8.675C17.6833 8.525 17.3583 8.4 17.025 8.3C16.6917 8.2 16.35 8.125 16 8.075V2H2V16.05H8.075C8.15833 16.5667 8.28767 17.0583 8.463 17.525C8.63833 17.9917 8.86733 18.4333 9.15 18.85L9 19L7.5 17.5L6 19L4.5 17.5L3 19L1.5 17.5L0 19ZM3 14H8.075C8.125 13.65 8.2 13.3083 8.3 12.975C8.4 12.6417 8.525 12.3167 8.675 12H3V14ZM3 10H10.1C10.7333 9.38333 11.471 8.89567 12.313 8.537C13.155 8.17833 14.0507 7.99933 15 8H3V10Z" fill="#949291"/>
                </svg>
                Book Now
            </a> -->

            {{-- POS Booking Dropdown --}}
            <!-- <div
                x-data="{ open: false }"
                class="space-y-1"
                 >
                {{-- Parent --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                           text-gray-700 hover:bg-gray-50"
                >
                    <div class="flex items-center gap-3">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14.275 17.25L17.75 13.8L16.7 12.75L14.275 15.125L13.3 14.15L12.25 15.225L14.275 17.25ZM3 6H15V4H3V6ZM15 20C13.6167 20 12.4377 19.5123 11.463 18.537C10.4883 17.5617 10.0007 16.3827 10 15C10 13.6167 10.4877 12.4377 11.463 11.463C12.4383 10.4883 13.6173 10.0007 15 10C16.3833 10 17.5627 10.4877 18.538 11.463C19.5133 12.4383 20.0007 13.6173 20 15C20 16.3833 19.5123 17.5627 18.537 18.538C17.5617 19.5133 16.3827 20.0007 15 20ZM0 19V2C0 1.45 0.196 0.979333 0.588 0.588C0.98 0.196667 1.45067 0.000666667 2 0H16C16.55 0 17.021 0.196 17.413 0.588C17.805 0.98 18.0007 1.45067 18 2V8.675C17.6833 8.525 17.3583 8.4 17.025 8.3C16.6917 8.2 16.35 8.125 16 8.075V2H2V16.05H8.075C8.15833 16.5667 8.28767 17.0583 8.463 17.525C8.63833 17.9917 8.86733 18.4333 9.15 18.85L9 19L7.5 17.5L6 19L4.5 17.5L3 19L1.5 17.5L0 19ZM3 14H8.075C8.125 13.65 8.2 13.3083 8.3 12.975C8.4 12.6417 8.525 12.3167 8.675 12H3V14ZM3 10H10.1C10.7333 9.38333 11.471 8.89567 12.313 8.537C13.155 8.17833 14.0507 7.99933 15 8H3V10Z" fill="#949291"/>
                        </svg>
                        POS Booking
                    </div>

                    <svg
                        class="w-4 h-4 transition-transform duration-200"
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
                <div
                    x-show="open"
                    x-collapse
                    x-cloak
                    class="space-y-1"
                >
                    <a
                        href=""
                        class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
                    >
                        - All Bookings
                    </a>

                    <a
                        href=""
                        class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
                    >
                        - Create Booking
                    </a>

                    <a
                        href=""
                        class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
                    >
                        - Booking History
                    </a>
                </div>
            </div> -->

            {{-- POS Customers Dropdown --}}
            <!-- <div
                x-data="{ open: false }"
                class="space-y-1"
                >
                {{-- Parent --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                           text-gray-700 hover:bg-gray-50"
                >
                    <div class=" gap-3 flex items-center">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.07 10.4101C13.6774 9.56132 14.0041 8.54377 14.0041 7.50005C14.0041 6.45634 13.6774 5.43879 13.07 4.59005C13.6385 4.20201 14.3117 3.99622 15 4.00005C15.9283 4.00005 16.8185 4.3688 17.4749 5.02518C18.1313 5.68156 18.5 6.57179 18.5 7.50005C18.5 8.42831 18.1313 9.31855 17.4749 9.97493C16.8185 10.6313 15.9283 11.0001 15 11.0001C14.3117 11.0039 13.6385 10.7981 13.07 10.4101ZM5.5 7.50005C5.5 6.80782 5.70527 6.13113 6.08986 5.55556C6.47444 4.97998 7.02107 4.53138 7.66061 4.26647C8.30015 4.00157 9.00388 3.93226 9.68282 4.0673C10.3617 4.20235 10.9854 4.5357 11.4749 5.02518C11.9644 5.51466 12.2977 6.1383 12.4327 6.81724C12.5678 7.49617 12.4985 8.1999 12.2336 8.83944C11.9687 9.47899 11.5201 10.0256 10.9445 10.4102C10.3689 10.7948 9.69223 11.0001 9 11.0001C8.07174 11.0001 7.1815 10.6313 6.52513 9.97493C5.86875 9.31855 5.5 8.42831 5.5 7.50005ZM7.5 7.50005C7.5 7.79672 7.58797 8.08673 7.7528 8.33341C7.91762 8.58008 8.15189 8.77234 8.42597 8.88587C8.70006 8.9994 9.00166 9.02911 9.29264 8.97123C9.58361 8.91335 9.85088 8.77049 10.0607 8.56071C10.2704 8.35093 10.4133 8.08366 10.4712 7.79269C10.5291 7.50172 10.4994 7.20012 10.3858 6.92603C10.2723 6.65194 10.08 6.41767 9.83335 6.25285C9.58668 6.08803 9.29667 6.00005 9 6.00005C8.60218 6.00005 8.22064 6.15809 7.93934 6.43939C7.65804 6.7207 7.5 7.10223 7.5 7.50005ZM16 17.0001V19.0001H2V17.0001C2 17.0001 2 13.0001 9 13.0001C16 13.0001 16 17.0001 16 17.0001ZM14 17.0001C13.86 16.2201 12.67 15.0001 9 15.0001C5.33 15.0001 4.07 16.3101 4 17.0001M15.95 13.0001C16.5629 13.4768 17.064 14.0819 17.4182 14.7729C17.7723 15.4639 17.9709 16.2241 18 17.0001V19.0001H22V17.0001C22 17.0001 22 13.3701 15.94 13.0001H15.95Z" fill="#949291"/>
                        </svg>
                        POS Customers
                    </div>

                    <svg
                        class="w-4 h-4 transition-transform duration-200"
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
                <div
                    x-show="open"
                    x-collapse
                    x-cloak
                    class="space-y-1"
                >
                    <a
                        href=""
                        class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
                    >
                        - All Customers
                    </a>

                    <a
                        href=""
                        class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
                    >
                        - Add Customer
                    </a>

                    <a
                        href=""
                        class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
                    >
                        - Customer History
                    </a>
                </div>
            </div> -->

            <!-- <a href="" class=" gap-3 flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('vendor.transactions.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.275 17.25L17.75 13.8L16.7 12.75L14.275 15.125L13.3 14.15L12.25 15.225L14.275 17.25ZM3 6H15V4H3V6ZM15 20C13.6167 20 12.4377 19.5123 11.463 18.537C10.4883 17.5617 10.0007 16.3827 10 15C10 13.6167 10.4877 12.4377 11.463 11.463C12.4383 10.4883 13.6173 10.0007 15 10C16.3833 10 17.5627 10.4877 18.538 11.463C19.5133 12.4383 20.0007 13.6173 20 15C20 16.3833 19.5123 17.5627 18.537 18.538C17.5617 19.5133 16.3827 20.0007 15 20ZM0 19V2C0 1.45 0.196 0.979333 0.588 0.588C0.98 0.196667 1.45067 0.000666667 2 0H16C16.55 0 17.021 0.196 17.413 0.588C17.805 0.98 18.0007 1.45067 18 2V8.675C17.6833 8.525 17.3583 8.4 17.025 8.3C16.6917 8.2 16.35 8.125 16 8.075V2H2V16.05H8.075C8.15833 16.5667 8.28767 17.0583 8.463 17.525C8.63833 17.9917 8.86733 18.4333 9.15 18.85L9 19L7.5 17.5L6 19L4.5 17.5L3 19L1.5 17.5L0 19ZM3 14H8.075C8.125 13.65 8.2 13.3083 8.3 12.975C8.4 12.6417 8.525 12.3167 8.675 12H3V14ZM3 10H10.1C10.7333 9.38333 11.471 8.89567 12.313 8.537C13.155 8.17833 14.0507 7.99933 15 8H3V10Z" fill="#949291"/>
            </svg>
                POS Transactions
            </a>
                    <hr class="mt-5 border-gray-200">
                    <p class="text-center font-semibold text-gray-500 text-xs uppercase tracking-wide my-3">
                        PEOPLES
                    </p> -->

{{-- My Staff Dropdown --}}
<!-- <div
    x-data="{ open: false }"
    class="space-y-1"
    >
    {{-- Parent --}}
    <button
        type="button"
        @click="open = !open"
        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
               text-gray-700 hover:bg-gray-50"
    >
        <div class="flex items-center gap-3">
            <svg width="20" height="15" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M11.07 6.41005C11.6774 5.56132 12.0041 4.54377 12.0041 3.50005C12.0041 2.45634 11.6774 1.43879 11.07 0.590053C11.6385 0.202008 12.3117 -0.00378014 13 5.2579e-05C13.9283 5.2579e-05 14.8185 0.368802 15.4749 1.02518C16.1313 1.68156 16.5 2.57179 16.5 3.50005C16.5 4.42831 16.1313 5.31855 15.4749 5.97493C14.8185 6.6313 13.9283 7.00005 13 7.00005C12.3117 7.00389 11.6385 6.7981 11.07 6.41005ZM3.5 3.50005C3.5 2.80782 3.70527 2.13113 4.08986 1.55556C4.47444 0.979985 5.02107 0.531381 5.66061 0.266474C6.30015 0.00156766 7.00388 -0.067744 7.68282 0.0673043C8.36175 0.202353 8.98539 0.535695 9.47487 1.02518C9.96436 1.51466 10.2977 2.1383 10.4327 2.81724C10.5678 3.49617 10.4985 4.1999 10.2336 4.83944C9.96867 5.47899 9.52007 6.02561 8.9445 6.4102C8.36892 6.79478 7.69223 7.00005 7 7.00005C6.07174 7.00005 5.1815 6.6313 4.52513 5.97493C3.86875 5.31855 3.5 4.42831 3.5 3.50005ZM5.5 3.50005C5.5 3.79672 5.58797 4.08673 5.7528 4.33341C5.91762 4.58008 6.15189 4.77234 6.42597 4.88587C6.70006 4.9994 7.00166 5.02911 7.29264 4.97123C7.58361 4.91335 7.85088 4.77049 8.06066 4.56071C8.27044 4.35093 8.4133 4.08366 8.47118 3.79269C8.52906 3.50172 8.49935 3.20012 8.38582 2.92603C8.27229 2.65194 8.08003 2.41767 7.83335 2.25285C7.58668 2.08803 7.29667 2.00005 7 2.00005C6.60218 2.00005 6.22064 2.15809 5.93934 2.43939C5.65804 2.7207 5.5 3.10223 5.5 3.50005ZM14 13.0001V15.0001H0V13.0001C0 13.0001 0 9.00005 7 9.00005C14 9.00005 14 13.0001 14 13.0001ZM12 13.0001C11.86 12.2201 10.67 11.0001 7 11.0001C3.33 11.0001 2.07 12.3101 2 13.0001M13.95 9.00005C14.5629 9.47678 15.064 10.0819 15.4182 10.7729C15.7723 11.4639 15.9709 12.2241 16 13.0001V15.0001H20V13.0001C20 13.0001 20 9.37005 13.94 9.00005H13.95Z" fill="#949291"/>
            </svg>
            My Staff
        </div>

        <svg
            class="w-4 h-4 transition-transform duration-200"
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
    <div
        x-show="open"
        x-collapse
        x-cloak
        class="space-y-1"
    >
        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - All Staff
        </a>

        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - Add Staff
        </a>

        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - Staff Roles
        </a>
    </div>
</div> -->

{{-- Graphics Designer Dropdown --}}
<!-- <div
    x-data="{ open: false }"
    class="space-y-1"
    >
    {{-- Parent --}}
    <button
        type="button"
        @click="open = !open"
        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
               text-gray-700 hover:bg-gray-50"
    >
        <div class="flex items-center gap-3">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.625 4.76927H21V21H4V4L14.625 14.8205V4.76927ZM5.0625 19.918H18.1196L5.0625 6.62059V19.918ZM18.5679 18.8359L19.6304 19.918H19.9375V5.85132H15.6875V6.93337H17.8125V8.01542H15.6875V9.09746H17.8125V10.1795H15.6875V11.2616H18.875V12.3436H15.6875V13.4257H17.8125V14.5077H15.6875V15.5898H17.8125V16.6718H16.4429L17.5054 17.7539H18.875V18.8359H18.5679ZM7.1875 12.6564L12.1929 17.7539H7.1875V12.6564ZM8.25 15.277V16.6718H9.61963L8.25 15.277Z" fill="#949291"/>
            </svg>
            Graphics Designer
        </div>

        <svg
            class="w-4 h-4 transition-transform duration-200"
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
    <div
        x-show="open"
        x-collapse
        x-cloak
        class="space-y-1"
    >
        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - All Designers
        </a>

        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - Add Designer
        </a>

        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - Assigned Designs
        </a>
    </div>
</div> -->
{{-- Printer Dropdown --}}
<!-- <div
    x-data="{ open: false }"
    class="space-y-1"
    >
    {{-- Parent --}}
    <button
        type="button"
        @click="open = !open"
        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
               text-gray-700 hover:bg-gray-50"
    >
        <div class="flex items-center gap-3">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20.1253 6.75H18.75V3.75C18.75 3.55109 18.671 3.36032 18.5303 3.21967C18.3897 3.07902 18.1989 3 18 3H6C5.80109 3 5.61032 3.07902 5.46967 3.21967C5.32902 3.36032 5.25 3.55109 5.25 3.75V6.75H3.87469C2.565 6.75 1.5 7.75969 1.5 9V16.5C1.5 16.6989 1.57902 16.8897 1.71967 17.0303C1.86032 17.171 2.05109 17.25 2.25 17.25H5.25V20.25C5.25 20.4489 5.32902 20.6397 5.46967 20.7803C5.61032 20.921 5.80109 21 6 21H18C18.1989 21 18.3897 20.921 18.5303 20.7803C18.671 20.6397 18.75 20.4489 18.75 20.25V17.25H21.75C21.9489 17.25 22.1397 17.171 22.2803 17.0303C22.421 16.8897 22.5 16.6989 22.5 16.5V9C22.5 7.75969 21.435 6.75 20.1253 6.75ZM6.75 4.5H17.25V6.75H6.75V4.5ZM17.25 19.5H6.75V15H17.25V19.5ZM21 15.75H18.75V14.25C18.75 14.0511 18.671 13.8603 18.5303 13.7197C18.3897 13.579 18.1989 13.5 18 13.5H6C5.80109 13.5 5.61032 13.579 5.46967 13.7197C5.32902 13.8603 5.25 14.0511 5.25 14.25V15.75H3V9C3 8.58656 3.39281 8.25 3.87469 8.25H20.1253C20.6072 8.25 21 8.58656 21 9V15.75ZM18.75 10.875C18.75 11.0975 18.684 11.315 18.5604 11.5C18.4368 11.685 18.2611 11.8292 18.0555 11.9144C17.85 11.9995 17.6238 12.0218 17.4055 11.9784C17.1873 11.935 16.9868 11.8278 16.8295 11.6705C16.6722 11.5132 16.565 11.3127 16.5216 11.0945C16.4782 10.8762 16.5005 10.65 16.5856 10.4445C16.6708 10.2389 16.815 10.0632 17 9.9396C17.185 9.81598 17.4025 9.75 17.625 9.75C17.9234 9.75 18.2095 9.86853 18.4205 10.0795C18.6315 10.2905 18.75 10.5766 18.75 10.875Z" fill="#949291"/>
            </svg>
            Printer
        </div>

        <svg
            class="w-4 h-4 transition-transform duration-200"
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
    <div
        x-show="open"
        x-collapse
        x-cloak
        class="space-y-1"
    >
        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - All Printers
        </a>

        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - Add Printer
        </a>

        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - Print Jobs
        </a>
    </div>
</div> -->
{{-- Munter Dropdown --}}
<!-- <div
    x-data="{ open: false }"
    class="space-y-1"
    >
    {{-- Parent --}}
    <button
        type="button"
        @click="open = !open"
        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
               text-gray-700 hover:bg-gray-50"
    >
        <div class="flex items-center gap-3">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1.12493 0.00100437L0 1.12587L2.47484 4.59158C2.57903 4.73752 2.7166 4.85644 2.87608 4.93843C3.03556 5.02042 3.21233 5.06309 3.39166 5.06289H3.4704C3.61831 5.06278 3.76478 5.09183 3.90145 5.14838C4.03811 5.20494 4.16228 5.28789 4.26685 5.39248L7.27603 8.40149L4.33209 11.3869C3.82857 11.2387 3.29743 11.2098 2.78082 11.3026C2.2642 11.3954 1.77632 11.6073 1.35587 11.9215C0.935427 12.2357 0.593985 12.6436 0.358631 13.1127C0.123277 13.5819 0.000486195 14.0994 0 14.6242C0.000674353 15.1079 0.105297 15.5857 0.306781 16.0254C0.508264 16.465 0.8019 16.8562 1.16779 17.1725C1.53369 17.4888 1.96329 17.7227 2.4275 17.8585C2.8917 17.9942 3.37967 18.0286 3.85833 17.9593C4.33699 17.89 4.79516 17.7187 5.20181 17.4568C5.60845 17.195 5.95406 16.8488 6.21523 16.4418C6.4764 16.0347 6.64702 15.5763 6.71553 15.0976C6.78404 14.6188 6.74884 14.1309 6.61232 13.667L9.59788 10.7232L10.6868 11.8121L10.3437 12.8402C10.2778 13.0384 10.2683 13.251 10.3164 13.4542C10.3645 13.6575 10.4682 13.8433 10.6159 13.9909L14.2944 17.6693C14.3987 17.7741 14.5226 17.8573 14.6591 17.914C14.7956 17.9708 14.9419 18 15.0898 18C15.2376 18 15.384 17.9708 15.5205 17.914C15.6569 17.8573 15.7809 17.7741 15.8851 17.6693L17.6692 15.8852C17.7741 15.781 17.8572 15.6571 17.914 15.5206C17.9708 15.3841 18 15.2378 18 15.0899C18 14.9421 17.9708 14.7958 17.914 14.6593C17.8572 14.5228 17.7741 14.3989 17.6692 14.2947L13.9907 10.6164C13.8431 10.4686 13.6572 10.3649 13.454 10.3168C13.2507 10.2688 13.0381 10.2782 12.8399 10.3441L11.8117 10.6872L10.7318 9.60735L13.7466 6.63433C14.2467 6.76782 14.7708 6.78469 15.2785 6.68361C15.7862 6.58254 16.2639 6.36623 16.6747 6.05137C17.0856 5.73651 17.4186 5.3315 17.6482 4.86758C17.8778 4.40365 17.9977 3.89321 17.9988 3.3756C17.9988 3.07188 17.9606 2.78054 17.8841 2.50158L15.4767 4.90991L13.4991 4.50046L13.0897 2.52408L15.4981 0.115741C14.9252 -0.038297 14.3218 -0.0385845 13.7487 0.114907C13.1756 0.268398 12.6531 0.570244 12.2339 0.990018C11.8147 1.40979 11.5135 1.93266 11.3608 2.50591C11.2081 3.07917 11.2092 3.68255 11.364 4.25524L8.39421 7.26763L5.39178 4.26762C5.1808 4.05671 5.06224 3.77064 5.06217 3.47234V3.39247C5.06219 3.21335 5.01943 3.03681 4.93744 2.87755C4.85546 2.71828 4.73663 2.5809 4.59083 2.47683L1.12493 0.00100437ZM11.976 11.9763C12.0282 11.9239 12.0903 11.8824 12.1586 11.854C12.227 11.8257 12.3002 11.8111 12.3742 11.8111C12.4482 11.8111 12.5214 11.8257 12.5898 11.854C12.6581 11.8824 12.7202 11.9239 12.7724 11.9763L16.0505 15.2553C16.1529 15.3614 16.2096 15.5034 16.2083 15.6509C16.207 15.7984 16.1479 15.9394 16.0436 16.0437C15.9393 16.148 15.7983 16.2071 15.6508 16.2084C15.5033 16.2097 15.3612 16.153 15.2551 16.0506L11.976 12.7727C11.9236 12.7205 11.882 12.6584 11.8537 12.5901C11.8253 12.5217 11.8107 12.4485 11.8107 12.3745C11.8107 12.3005 11.8253 12.2273 11.8537 12.159C11.882 12.0906 11.9236 12.0286 11.976 11.9763ZM3.37478 12.3745L3.90462 12.6467L4.49971 12.676L4.82256 13.1765L5.32316 13.4994L5.3524 14.0944L5.62464 14.6242L5.3524 15.1541L5.32316 15.7491L4.82256 16.0719L4.49971 16.5725L3.90462 16.6018L3.37478 16.874L2.84494 16.6018L2.24985 16.5725L1.927 16.0719L1.42641 15.7491L1.39716 15.1541L1.12493 14.6242L1.39716 14.0944L1.42641 13.4994L1.927 13.1765L2.24985 12.676L2.84494 12.6467L3.37478 12.3745Z" fill="#949291"/>
            </svg>
            Munter
        </div>

        <svg
            class="w-4 h-4 transition-transform duration-200"
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
    <div
        x-show="open"
        x-collapse
        x-cloak
        class="space-y-1"
    >
        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - All Munters
        </a>

        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - Assign Work
        </a>

        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - Installation History
        </a>
    </div>
</div> -->
{{-- Surveyor Dropdown --}}
<!-- <div
    x-data="{ open: false }"
    class="space-y-1"
    >
    {{-- Parent --}}
    <button
        type="button"
        @click="open = !open"
        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
               text-gray-700 hover:bg-gray-50"
    >
        <div class="flex items-center gap-3">
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12.2417 7.12508L15.8936 3.46667L15.3021 2.87517L12.2417 5.93558L10.6979 4.41242L10.1064 5.01042L12.2417 7.12508ZM0 17.3333V16.25H11.9167V17.3333H0ZM13.0033 9.75C11.6556 9.75 10.5054 9.27514 9.55283 8.32542C8.60094 7.37497 8.125 6.22592 8.125 4.87825C8.125 3.53058 8.59986 2.38044 9.54958 1.42783C10.5 0.475944 11.6491 0 12.9967 0C14.3444 0 15.4946 0.474861 16.4472 1.42458C17.3991 2.37503 17.875 3.52408 17.875 4.87175C17.875 6.21942 17.4001 7.36956 16.4504 8.32217C15.5 9.27406 14.3509 9.75 13.0033 9.75ZM0 8.66667V7.58333H5.82508C5.89586 7.78556 5.97422 7.97369 6.06017 8.14775C6.14683 8.32108 6.24469 8.49406 6.35375 8.66667H0ZM0 13V11.9167H10.0371C10.3274 12.0445 10.6307 12.1561 10.9471 12.2514C11.2649 12.346 11.5881 12.4157 11.9167 12.4605V13H0Z" fill="#949291"/>
        </svg>
            Surveyor
        </div>

        <svg
            class="w-4 h-4 transition-transform duration-200"
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
    <div
        x-show="open"
        x-collapse
        x-cloak
        class="space-y-1"
    >
        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - All Surveyors
        </a>

        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - Assign Survey
        </a>

        <a
            href=""
            class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50"
        >
            - Survey Reports
        </a>
    </div>
</div> -->


<!-- <hr class="mt-5 border-gray-200"> -->

<!-- 
            <a href="" class="gap-3 flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('vendor.appointments.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7 11H9V13H7V11ZM7 15H9V17H7V15ZM11 11H13V13H11V11ZM11 15H13V17H11V15ZM15 11H17V13H15V11ZM15 15H17V17H15V15Z" fill="#1E1B18"/>
                <path d="M5 22H19C20.103 22 21 21.103 21 20V6C21 4.897 20.103 4 19 4H17V2H15V4H9V2H7V4H5C3.897 4 3 4.897 3 6V20C3 21.103 3.897 22 5 22ZM19 8L19.001 20H5V8H19Z" fill="#949291"/>
                </svg>
                    Appointments
            </a>
            <a href="" class=" gap-3 flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('vendor.subscriptions.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11 4H0V6H11V4ZM11 0H0V2H11V0ZM0 10H7V8H0V10ZM18.5 5.5L20 7L13 14L8.5 9.5L10 8L13 11L18.5 5.5Z" fill="#949291"/>
                </svg>
                    Subscriptions
            </a> -->



{{-- Settings Dropdown --}}
<!-- <div
    x-data="{ open: false }"
    class="space-y-1"
    >
    {{-- Parent --}}
    <button
        type="button"
        @click="open = !open"
        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
               text-gray-700 hover:bg-gray-50"
    >
        <div class="flex items-center gap-3">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M13.0784 2C13.3724 2 13.6354 2.183 13.7344 2.457L14.4404 4.414C14.6934 4.477 14.9104 4.54 15.0944 4.606C15.2954 4.678 15.5544 4.787 15.8744 4.936L17.5184 4.066C17.6526 3.99491 17.8062 3.96925 17.9562 3.99287C18.1062 4.01649 18.2445 4.08811 18.3504 4.197L19.7964 5.692C19.9884 5.891 20.0424 6.182 19.9344 6.436L19.1634 8.243C19.2914 8.478 19.3934 8.679 19.4714 8.847C19.5554 9.03 19.6594 9.282 19.7834 9.607L21.5804 10.377C21.8504 10.492 22.0174 10.762 21.9994 11.051L21.8674 13.126C21.8583 13.2608 21.8099 13.39 21.7282 13.4975C21.6464 13.6051 21.5349 13.6863 21.4074 13.731L19.7054 14.336C19.6564 14.571 19.6054 14.772 19.5514 14.942C19.4643 15.2045 19.3648 15.4628 19.2534 15.716L20.1084 17.606C20.1687 17.7388 20.185 17.8874 20.1547 18.0301C20.1245 18.1728 20.0494 18.3021 19.9404 18.399L18.3144 19.851C18.2073 19.9462 18.0737 20.0064 17.9314 20.0236C17.7891 20.0408 17.645 20.014 17.5184 19.947L15.8424 19.059C15.5802 19.1978 15.3096 19.3204 15.0324 19.426L14.3004 19.7L13.6504 21.5C13.6022 21.6318 13.5153 21.746 13.4011 21.8276C13.2869 21.9091 13.1507 21.9542 13.0104 21.957L11.1104 22C10.9663 22.0038 10.8247 21.9628 10.7049 21.8828C10.5851 21.8027 10.493 21.6875 10.4414 21.553L9.67537 19.526C9.41401 19.4367 9.15524 19.34 8.89937 19.236C8.69008 19.1454 8.48396 19.0477 8.28137 18.943L6.38137 19.755C6.25618 19.8084 6.11816 19.8243 5.98411 19.8007C5.85006 19.7771 5.72577 19.715 5.62637 19.622L4.22037 18.303C4.11569 18.2052 4.0444 18.077 4.01658 17.9365C3.98877 17.796 4.00583 17.6503 4.06537 17.52L4.88237 15.74C4.77371 15.5292 4.67297 15.3144 4.58037 15.096C4.47227 14.8287 4.37222 14.5583 4.28037 14.285L2.49037 13.74C2.34487 13.696 2.21796 13.6052 2.12936 13.4817C2.04075 13.3582 1.99541 13.2089 2.00037 13.057L2.07037 11.136C2.07535 11.0107 2.1145 10.8891 2.1836 10.7844C2.25269 10.6797 2.34909 10.5959 2.46237 10.542L4.34037 9.64C4.42737 9.321 4.50337 9.073 4.57037 8.892C4.66471 8.65025 4.76947 8.41269 4.88437 8.18L4.07037 6.46C4.00859 6.32938 3.98983 6.18254 4.01679 6.04059C4.04374 5.89864 4.11502 5.76889 4.22037 5.67L5.62437 4.344C5.72279 4.25117 5.84594 4.18876 5.979 4.16428C6.11205 4.1398 6.24935 4.15429 6.37437 4.206L8.27237 4.99C8.48237 4.85 8.67237 4.737 8.84437 4.646C9.04937 4.537 9.32337 4.423 9.66837 4.3L10.3284 2.459C10.3772 2.32427 10.4664 2.20788 10.5838 2.12573C10.7012 2.04358 10.8411 1.99967 10.9844 2H13.0784ZM12.5884 3.377H11.4754L10.8704 5.071C10.8348 5.16978 10.7772 5.25918 10.7021 5.33246C10.6269 5.40574 10.536 5.46097 10.4364 5.494C10.0004 5.639 9.68537 5.764 9.50137 5.861C9.30637 5.964 9.05737 6.121 8.76137 6.331C8.66449 6.39899 8.55201 6.44142 8.43436 6.45436C8.31672 6.46729 8.19771 6.45032 8.08837 6.405L6.25837 5.65L5.54537 6.324L6.28837 7.894C6.33285 7.98741 6.35543 8.08974 6.35439 8.1932C6.35335 8.29665 6.32872 8.39851 6.28237 8.491C6.08237 8.892 5.94737 9.188 5.87937 9.37C5.77509 9.67297 5.68499 9.98064 5.60937 10.292C5.58424 10.3895 5.53811 10.4802 5.47419 10.558C5.41027 10.6357 5.33013 10.6985 5.23937 10.742L3.44937 11.601L3.41337 12.581L5.03337 13.073C5.24837 13.138 5.41837 13.303 5.48937 13.515C5.64937 13.995 5.77737 14.349 5.86937 14.571C5.99127 14.8527 6.12608 15.1287 6.27337 15.398C6.3232 15.4907 6.35085 15.5936 6.35415 15.6988C6.35744 15.8039 6.33629 15.9084 6.29237 16.004L5.54137 17.642L6.25237 18.31L8.03437 17.548C8.13035 17.507 8.23427 17.4879 8.33857 17.492C8.44288 17.4962 8.54494 17.5235 8.63737 17.572C9.00237 17.764 9.27437 17.897 9.44637 17.97C9.62137 18.043 9.95637 18.165 10.4424 18.331C10.5388 18.3639 10.6269 18.4178 10.7002 18.4886C10.7735 18.5595 10.8302 18.6457 10.8664 18.741L11.5744 20.612L12.5004 20.592L13.0974 18.938C13.1315 18.8436 13.1858 18.7578 13.2564 18.6865C13.327 18.6152 13.4123 18.5601 13.5064 18.525L14.5434 18.137C14.8054 18.04 15.1234 17.887 15.4944 17.677C15.597 17.6193 15.7126 17.5884 15.8303 17.587C15.9481 17.5856 16.0643 17.6138 16.1684 17.669L17.7454 18.504L18.6324 17.713L17.8564 16C17.8165 15.9121 17.7958 15.8167 17.7956 15.7201C17.7955 15.6236 17.8158 15.5281 17.8554 15.44C18.0374 15.033 18.1604 14.726 18.2224 14.53C18.2834 14.338 18.3464 14.061 18.4074 13.705C18.4283 13.5841 18.4812 13.4709 18.5605 13.3772C18.6398 13.2836 18.7426 13.2127 18.8584 13.172L20.5064 12.587L20.5784 11.447L18.9584 10.753C18.8721 10.7163 18.7942 10.6624 18.7294 10.5947C18.6646 10.527 18.6142 10.4468 18.5814 10.359C18.4658 10.0402 18.3398 9.7254 18.2034 9.415C18.0742 9.14473 17.9341 8.87985 17.7834 8.621C17.7311 8.52955 17.7008 8.42725 17.6947 8.32211C17.6887 8.21697 17.707 8.11186 17.7484 8.015L18.4734 6.315L17.7094 5.525L16.2214 6.313C16.1242 6.36452 16.0163 6.39254 15.9064 6.3948C15.7965 6.39705 15.6875 6.37349 15.5884 6.326C15.2725 6.16897 14.9495 6.02681 14.6204 5.9C14.3397 5.80575 14.0535 5.72894 13.7634 5.67C13.6482 5.64559 13.5412 5.59231 13.4524 5.51517C13.3635 5.43802 13.2957 5.33955 13.2554 5.229L12.5884 3.377ZM12.0244 7.641C14.4594 7.641 16.4344 9.594 16.4344 12.002C16.4344 14.41 14.4594 16.362 12.0244 16.362C9.58837 16.362 7.61437 14.41 7.61437 12.002C7.61437 9.594 9.58837 7.642 12.0244 7.642M12.0244 9.02C10.3574 9.02 9.00637 10.355 9.00637 12.003C9.00637 13.651 10.3574 14.987 12.0244 14.987C13.6904 14.987 15.0414 13.651 15.0414 12.003C15.0414 10.355 13.6914 9.02 12.0244 9.02Z" fill="#949291"/>
            </svg>
            Settings
        </div>

        <svg
            class="w-4 h-4 transition-transform duration-200"
            :class="{ 'rotate-180': open }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    {{-- Children --}}
    <div
        x-show="open"
        x-collapse
        x-cloak
        class="space-y-1"
    >
        <a href="" class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
            - Profile Settings
        </a>
        <a href="" class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
            - Account Settings
        </a>
        <a href="" class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
            - Password & Security
        </a>
        <a href="" class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
            - Notification Settings
        </a>
        <a href="" class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
            - Email Preferences
        </a>
        <a href="" class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
            - Billing Settings
        </a>
        <a href="" class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
            - Tax Information
        </a>
        <a href="" class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
            - Payment Methods
        </a>
        <a href="" class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
            - Language & Region
        </a>
        <a href="" class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
            - Privacy Controls
        </a>
        <a href="" class="block px-6 py-2 text-sm rounded-md text-gray-600 hover:bg-gray-50">
            - System Preferences
        </a>
    </div>
</div> -->

{{-- Logout --}}
<form method="POST" action="{{ route('logout') }}">
    @csrf

    <button
        type="submit"
        onclick="return confirm('Are you sure you want to logout?')"
        class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg
                hover:bg-red-50 transition"
    >
       <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
             <path d="M12 22C6.477 22 2 17.523 2 12C2 6.477 6.477 2 12 2C13.5527 1.99884 15.0842 2.35978 16.4729 3.05414C17.8617 3.74851 19.0693 4.75718 20 6H17.29C16.1352 4.98176 14.7112 4.31836 13.1887 4.0894C11.6663 3.86044 10.1101 4.07566 8.70689 4.70922C7.30371 5.34277 6.11315 6.36776 5.27807 7.66119C4.44299 8.95462 3.99887 10.4615 3.999 12.0011C3.99913 13.5407 4.4435 15.0475 5.27879 16.3408C6.11409 17.6341 7.30482 18.6589 8.7081 19.2922C10.1114 19.9255 11.6676 20.1405 13.19 19.9113C14.7125 19.6821 16.1364 19.0184 17.291 18H20.001C19.0702 19.243 17.8624 20.2517 16.4735 20.9461C15.0846 21.6405 13.5528 22.0013 12 22ZM19 16V13H11V11H19V8L24 12L19 16Z" fill="#949291"/>
    </svg>

        Logout
    </button>
</form>


          
        </div>
    </nav>
</aside>
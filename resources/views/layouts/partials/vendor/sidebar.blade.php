{{-- Vendor Sidebar --}}
<aside
    id="vendor-sidebar"
    class="hidden md:flex flex-col w-64 bg-white border-r border-gray-200 h-screen"
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
                            src="{{ route('vendor.view-avatar', auth()->user()->id) }}?t={{ time() }}"
                            alt="Profile Image"
                            class="w-full h-full object-cover block"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                        >
                        <svg
                            class="w-14 h-14 stroke-gray-400 hidden"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
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

          <!-- Search -->
        <div class=" pb-2 mt-2 border-b border-gray-100">
            {{--<div class="relative">
                <!-- Figma Search Icon -->
                <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.3" d="M14.2929 16.7071C13.9024 16.3166 13.9024 15.6834 14.2929 15.2929C14.6834 14.9024 15.3166 14.9024 15.7071 15.2929L19.7071 19.2929C20.0976 19.6834 20.0976 20.3166 19.7071 20.7071C19.3166 21.0976 18.6834 21.0976 18.2929 20.7071L14.2929 16.7071Z" fill="#949291"/>
                    <path d="M11 4C14.866 4 18 7.13401 18 11C18 14.866 14.866 18 11 18C7.13401 18 4 14.866 4 11C4 7.13401 7.13401 4 11 4ZM11 6C8.23858 6 6 8.23858 6 11C6 13.7614 8.23858 16 11 16C13.7614 16 16 13.7614 16 11C16 8.23858 13.7614 6 11 6Z" fill="#949291"/>
                    </svg>
                </span>
                <!-- Input -->
                <input
                    type="text"
                    placeholder="Search"
                    class="w-full rounded-lg pl-11 pr-3 py-2 text-sm
                        bg-gray-100 focus:outline-none"
                />
            </div>--}}
        </div>
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
            {{-- Display Enquiries Dropdown --}}
            <div
                x-data="{ open: {{ request()->routeIs('customer.enquiries.*') ? 'true' : 'false' }} }"
                class="space-y-1"
                >
                {{-- Parent --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                           {{ request()->routeIs('customer.enquiries.*') ? 'bg-[#00995c] text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    <div class="flex items-center gap-3
                         {{ request()->routeIs('customer.enquiries.*') ? 'text-white' : 'text-gray-700' }}">

                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z" fill="currentColor"/>
                        </svg>
                        My Order
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
                        class="space-y-1 pl-3 mt-1"
                    >

                    <a
                        href="{{ route('customer.enquiries.index') }}"
                        class="block px-6 py-1 text-sm rounded-md transition
                        {{ request()->routeIs('customer.enquiries.*')
                            ? 'bg-emerald-50 text-gray-900 border-[#00995c] pl-5 font-semibold'
                            : 'text-gray-600 hover:bg-gray-50 hover:pl-5 border-transparent' }}"
                    >
                        - Enquiries & Offers
                    </a>
                </div>
            </div>
            <div
                x-data="{ open: @if(request()->routeIs('vendor.enquiries.*')) true @else false @endif }"
                class="space-y-1"
                >
                {{-- Parent --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                           {{ request()->routeIs('vendor.enquiries.*') ? 'bg-[#00995c] text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    <div class="flex items-center gap-3
                         {{ request()->routeIs('vendor.enquiries.*') ? 'text-white' : 'text-gray-700' }}">

                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z" fill="currentColor"/>
                        </svg>
                        Display Enquiries
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
                        class="space-y-1 pl-3 mt-1"
                    >

                    <a
                        href="{{ route('vendor.enquiries.index') }}"
                        class="block px-6 py-1 text-sm rounded-md transition
                        {{ request()->routeIs('vendor.enquiries.index')
                            ? 'bg-emerald-50 text-gray-900 border-[#00995c] pl-5 font-semibold'
                            : 'text-gray-600 hover:bg-gray-50 hover:pl-5 border-transparent' }}"
                    >
                        - All Enquiries
                    </a>
                </div>
            </div>

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
                        class="space-y-1 pl-3 mt-1"
                    >

                    <a
                        href="{{ route('vendor.hoardings.myHoardings') }}"
                        class="block px-6 py-1 text-sm rounded-md transition
                        {{ request()->routeIs('vendor.hoardings.myHoardings')
                            ? 'bg-emerald-50 text-gray-900 border-[#00995c] pl-5 font-semibold'
                            : 'text-gray-600 hover:bg-gray-50 hover:pl-5 border-transparent' }}"
                    >
                        - My Hoardings
                    </a>

                    @if(auth()->user() && auth()->user()->hasRole('vendor'))
                    <a
                        href="{{ route('vendor.hoardings.add') }}"
                        class="block px-6 py-1 text-sm rounded-md transition
                        {{ request()->routeIs('vendor.hoardings.add') || request()->is('vendor/hoardings/add')
                            ? 'bg-emerald-50 text-gray-900 border-[#00995c] pl-5 font-semibold'
                            : 'text-gray-600 hover:bg-gray-50 hover:pl-5 border-transparent' }}"
                    >
                        <span class="inline-flex items-center gap-2">
                            - Add Hoardings
                        </span>
                    </a>
                    @endif
                </div>
            </div>
            <button
                type="button"
                onclick="openLogoutModal()"
                class="block w-full flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg
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
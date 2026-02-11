<aside class="admin-sidebar fixed left-0 top-0 h-screen flex flex-col shadow-lg transition-all duration-300 hidden md:flex">
    <button
    id="admin-mobile-btn-close"
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
    <div class="bg-white px-6 pt-5 pb-2">
        <div class="flex items-center space-x-3">
            <div
                    class="w-14 h-14 rounded-full border border-gray-300 overflow-hidden flex items-center justify-center bg-gray-100"
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

                    <span class="cursor-pointer">
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
                    </span>
                </p>
                <p class="text-xs text-yellow-600">
                    Admin
                </p>
            </div>
        </div>
        <hr class="mt-5 border-gray-200">
    </div>
    <nav class="flex-1 overflow-y-auto px-2 py-4">
        <div class="space-y-1">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-[#00995c] text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="4" y="4" width="7" height="7" rx="1.5" fill="currentColor"/>
                    <path opacity="0.3" d="M9.5 13C10.3284 13 11 13.6716 11 14.5V18.5C11 19.3284 10.3284 20 9.5 20H5.5C4.67157 20 4 19.3284 4 18.5V14.5C4 13.6716 4.67157 13 5.5 13H9.5ZM18.5 13C19.3284 13 20 13.6716 20 14.5V18.5C20 19.3284 19.3284 20 18.5 20H14.5C13.6716 20 13 19.3284 13 18.5V14.5C13 13.6716 13.6716 13 14.5 13H18.5ZM18.5 4C19.3284 4 20 4.67157 20 5.5V9.5C20 10.3284 19.3284 11 18.5 11H14.5C13.6716 11 13 10.3284 13 9.5V5.5C13 4.67157 13.6716 4 14.5 4H18.5Z" fill="#1E1B18"/>
                    </svg>
                    Dashboard
                </a>
            </div>
             <div
                x-data="{
                    open: false,
                    init() {
                        this.open = !!this.$el.querySelector('.child-active')
                    }
                }"

                class="space-y-1"
                  >
                {{-- Parent --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200
                    {{ request()->routeIs('admin.my-hoardings') ||
                    request()->routeIs('admin.vendor-hoardings.*') ||
                    request()->routeIs('admin.hoardings.drafts') ||
                    request()->routeIs('admin.hoarding-attributes.index')
                    ? 'bg-[#00995c] text-white'
                    : 'text-gray-700 hover:bg-gray-50' }}"
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
                        All Hoardings
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
                        href="{{ route('admin.my-hoardings') }}"
                        class="{{ request()->routeIs('admin.my-hoardings') ? 'child-active bg-emerald-50 text-gray-900 font-semibold pl-5' : 'text-gray-600 hover:bg-gray-50 hover:pl-5' }} block px-6 py-1 text-sm rounded-md transition"
                    >
                        - My Hoardings
                    </a>
                    <a
                        href="{{ route('admin.vendor-hoardings.index') }}"
                        class="{{ request()->routeIs('admin.vendor-hoardings.*') ? 'child-active bg-emerald-50 text-gray-900 font-semibold pl-5' : 'text-gray-600 hover:bg-gray-50 hover:pl-5' }} block px-6 py-1 text-sm rounded-md transition"
                    >
                        - Vendor's Hoardings
                    </a>
                    <a
                        href="{{route('admin.hoardings.drafts')}}"
                        class="{{ request()->routeIs('admin.hoardings.drafts') ? 'child-active bg-emerald-50 text-gray-900 font-semibold pl-5' : 'text-gray-600 hover:bg-gray-50 hover:pl-5' }} block px-6 py-1 text-sm rounded-md transition"
                    >
                        - Hoardings in draft
                    </a>
                    <a
                        href="{{route('admin.hoarding-attributes.index')}}"
                        class="{{ request()->routeIs('admin.hoarding-attributes.index') ? 'child-active bg-emerald-50 text-gray-900 font-semibold pl-5' : 'text-gray-600 hover:bg-gray-50 hover:pl-5' }} block px-6 py-1 text-sm rounded-md transition"
                    >
                        - Category
                    </a>

                </div>
            </div>
           
            <div
                x-data="{
                    open: false,
                    init() {
                        this.open = !!this.$el.querySelector('.submenu-active')
                    }
                }"
                class="space-y-1"
                >

                {{-- Parent --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200
                    {{ request()->routeIs('admin.enquiries.*') ||
                    request()->routeIs('admin.direct-enquiries.*')
                    ? 'bg-[#00995c] text-white'
                    : 'text-gray-700 hover:bg-gray-50' }}"
                                    >
                    <div class="flex items-center gap-3
                        ">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z" fill="currentColor"/>
                        </svg>
                        Display Enquiries
                    </div>
                    <svg
                        class="w-4 h-4 shrink-0 origin-center transition-transform duration-200"
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
                        href="{{ route('admin.enquiries.index') }}"
                        class="{{ request()->routeIs('admin.enquiries.*')
                            ? 'submenu-active bg-emerald-50 text-gray-900 border-[#00995c] pl-5 font-semibold'
                            : 'text-gray-600 hover:bg-gray-50 hover:pl-5 border-transparent' }} block px-6 py-1 text-sm rounded-md transition"
                    >


                        - Enquiries & Offers
                    </a>
                    <a
                        href="{{ route('admin.direct-enquiries.index') }}"
                        class="{{ request()->routeIs('admin.direct-enquiries.*')
                            ? 'submenu-active bg-emerald-50 text-gray-900 border-[#00995c] pl-5 font-semibold'
                            : 'text-gray-600 hover:bg-gray-50 hover:pl-5 border-transparent' }} block px-6 py-1 text-sm rounded-md transition"
                    >


                        - Direct Enquiry
                    </a>
                </div>
            </div>        
        </div>
        <div class="mt-6 mb-2 text-xs font-bold tracking-wider px-2">PEOPLES</div>
        <div class="space-y-1"> 
            <div
                x-data="{ open: @if(request()->routeIs('admin.vendors.*')) true @else false @endif }"
                class="space-y-1"
                >
                {{-- Parent --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                           {{ request()->routeIs('admin.vendors.*') ? 'bg-[#00995c] text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    <div class="flex items-center gap-3
                         {{ request()->routeIs('admin.vendors.*') ? 'text-white' : 'text-gray-700' }}">

                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.07 10.4101C13.6774 9.56132 14.0041 8.54377 14.0041 7.50005C14.0041 6.45634 13.6774 5.43879 13.07 4.59005C13.6385 4.20201 14.3117 3.99622 15 4.00005C15.9283 4.00005 16.8185 4.3688 17.4749 5.02518C18.1313 5.68156 18.5 6.57179 18.5 7.50005C18.5 8.42831 18.1313 9.31855 17.4749 9.97493C16.8185 10.6313 15.9283 11.0001 15 11.0001C14.3117 11.0039 13.6385 10.7981 13.07 10.4101ZM5.5 7.50005C5.5 6.80782 5.70527 6.13113 6.08986 5.55556C6.47444 4.97998 7.02107 4.53138 7.66061 4.26647C8.30015 4.00157 9.00388 3.93226 9.68282 4.0673C10.3617 4.20235 10.9854 4.5357 11.4749 5.02518C11.9644 5.51466 12.2977 6.1383 12.4327 6.81724C12.5678 7.49617 12.4985 8.1999 12.2336 8.83944C11.9687 9.47899 11.5201 10.0256 10.9445 10.4102C10.3689 10.7948 9.69223 11.0001 9 11.0001C8.07174 11.0001 7.1815 10.6313 6.52513 9.97493C5.86875 9.31855 5.5 8.42831 5.5 7.50005ZM7.5 7.50005C7.5 7.79672 7.58797 8.08673 7.7528 8.33341C7.91762 8.58008 8.15189 8.77234 8.42597 8.88587C8.70006 8.9994 9.00166 9.02911 9.29264 8.97123C9.58361 8.91335 9.85088 8.77049 10.0607 8.56071C10.2704 8.35093 10.4133 8.08366 10.4712 7.79269C10.5291 7.50172 10.4994 7.20012 10.3858 6.92603C10.2723 6.65194 10.08 6.41767 9.83335 6.25285C9.58668 6.08803 9.29667 6.00005 9 6.00005C8.60218 6.00005 8.22064 6.15809 7.93934 6.43939C7.65804 6.7207 7.5 7.10223 7.5 7.50005ZM16 17.0001V19.0001H2V17.0001C2 17.0001 2 13.0001 9 13.0001C16 13.0001 16 17.0001 16 17.0001ZM14 17.0001C13.86 16.2201 12.67 15.0001 9 15.0001C5.33 15.0001 4.07 16.3101 4 17.0001M15.95 13.0001C16.5629 13.4768 17.064 14.0819 17.4182 14.7729C17.7723 15.4639 17.9709 16.2241 18 17.0001V19.0001H22V17.0001C22 17.0001 22 13.3701 15.94 13.0001H15.95Z" fill="currentColor"/>
                            </svg>
                        Vendor Management
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
                        href="{{ route('admin.vendors.index') }}"
                        class="block px-6 py-1 text-sm rounded-md transition
                        {{ request()->routeIs('admin.vendors.index')
                            ? 'bg-emerald-50 text-gray-900 border-[#00995c] pl-5 font-semibold'
                            : 'text-gray-600 hover:bg-gray-50 hover:pl-5 border-transparent' }}"
                    >
                        - Total Vendor's
                    </a>
                </div>
            </div>
            <div
                x-data="{ open: @if(request()->routeIs('admin.customers.*')) true @else false @endif }"
                class="space-y-1"
                >
                {{-- Parent --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                           {{ request()->routeIs('admin.customers.*') ? 'bg-[#00995c] text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    <div class="flex items-center gap-3
                         {{ request()->routeIs('admin.customers.*') ? 'text-white' : 'text-gray-700' }}">

                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.07 10.4101C13.6774 9.56132 14.0041 8.54377 14.0041 7.50005C14.0041 6.45634 13.6774 5.43879 13.07 4.59005C13.6385 4.20201 14.3117 3.99622 15 4.00005C15.9283 4.00005 16.8185 4.3688 17.4749 5.02518C18.1313 5.68156 18.5 6.57179 18.5 7.50005C18.5 8.42831 18.1313 9.31855 17.4749 9.97493C16.8185 10.6313 15.9283 11.0001 15 11.0001C14.3117 11.0039 13.6385 10.7981 13.07 10.4101ZM5.5 7.50005C5.5 6.80782 5.70527 6.13113 6.08986 5.55556C6.47444 4.97998 7.02107 4.53138 7.66061 4.26647C8.30015 4.00157 9.00388 3.93226 9.68282 4.0673C10.3617 4.20235 10.9854 4.5357 11.4749 5.02518C11.9644 5.51466 12.2977 6.1383 12.4327 6.81724C12.5678 7.49617 12.4985 8.1999 12.2336 8.83944C11.9687 9.47899 11.5201 10.0256 10.9445 10.4102C10.3689 10.7948 9.69223 11.0001 9 11.0001C8.07174 11.0001 7.1815 10.6313 6.52513 9.97493C5.86875 9.31855 5.5 8.42831 5.5 7.50005ZM7.5 7.50005C7.5 7.79672 7.58797 8.08673 7.7528 8.33341C7.91762 8.58008 8.15189 8.77234 8.42597 8.88587C8.70006 8.9994 9.00166 9.02911 9.29264 8.97123C9.58361 8.91335 9.85088 8.77049 10.0607 8.56071C10.2704 8.35093 10.4133 8.08366 10.4712 7.79269C10.5291 7.50172 10.4994 7.20012 10.3858 6.92603C10.2723 6.65194 10.08 6.41767 9.83335 6.25285C9.58668 6.08803 9.29667 6.00005 9 6.00005C8.60218 6.00005 8.22064 6.15809 7.93934 6.43939C7.65804 6.7207 7.5 7.10223 7.5 7.50005ZM16 17.0001V19.0001H2V17.0001C2 17.0001 2 13.0001 9 13.0001C16 13.0001 16 17.0001 16 17.0001ZM14 17.0001C13.86 16.2201 12.67 15.0001 9 15.0001C5.33 15.0001 4.07 16.3101 4 17.0001M15.95 13.0001C16.5629 13.4768 17.064 14.0819 17.4182 14.7729C17.7723 15.4639 17.9709 16.2241 18 17.0001V19.0001H22V17.0001C22 17.0001 22 13.3701 15.94 13.0001H15.95Z" fill="currentColor"/>
                            </svg>
                        Customer Management
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
                        href="{{ route('admin.customers.index') }}"
                        class="block px-6 py-1 text-sm rounded-md transition
                        {{ request()->routeIs('admin.customers.index')
                            ? 'bg-emerald-50 text-gray-900 border-[#00995c] pl-5 font-semibold'
                            : 'text-gray-600 hover:bg-gray-50 hover:pl-5 border-transparent' }}"
                    >
                        - Total Customer's
                    </a>
                </div>
            </div> 
            <div>
                <button
                    type="button"
                    onclick="openLogoutModal()"
                    class="block sidebar-logout w-full flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg
                        text-gray-700 hover:text-red-700 hover:bg-red-100"
                      >
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22C6.477 22 2 17.523 2 12C2 6.477 6.477 2 12 2C13.5527 1.99884 15.0842 2.35978 16.4729 3.05414C17.8617 3.74851 19.0693 4.75718 20 6H17.29C16.1352 4.98176 14.7112 4.31836 13.1887 4.0894C11.6663 3.86044 10.1101 4.07566 8.70689 4.70922C7.30371 5.34277 6.11315 6.36776 5.27807 7.66119C4.44299 8.95462 3.99887 10.4615 3.999 12.0011C3.99913 13.5407 4.4435 15.0475 5.27879 16.3408C6.11409 17.6341 7.30482 18.6589 8.7081 19.2922C10.1114 19.9255 11.6676 20.1405 13.19 19.9113C14.7125 19.6821 16.1364 19.0184 17.291 18H20.001C19.0702 19.243 17.8624 20.2517 16.4735 20.9461C15.0846 21.6405 13.5528 22.0013 12 22ZM19 16V13H11V11H19V8L24 12L19 16Z" fill="currentColor"/>
                    </svg>
                    LogOut
                </button>
            </div> 
        </div>
    </nav>
</aside>
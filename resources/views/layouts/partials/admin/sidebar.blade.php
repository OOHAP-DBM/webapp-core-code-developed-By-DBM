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
        <ul class="space-y-1">
            <li>
                <a href="{{ route('admin.dashboard') }}" class=" gap-3 sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="4" y="4" width="7" height="7" rx="1.5" fill="currentColor"/>
                    <path opacity="0.3" fill-rule="evenodd" clip-rule="evenodd" d="M13 5.5C13 4.67157 13.6716 4 14.5 4H18.5C19.3284 4 20 4.67157 20 5.5V9.5C20 10.3284 19.3284 11 18.5 11H14.5C13.6716 11 13 10.3284 13 9.5V5.5ZM4 14.5C4 13.6716 4.67157 13 5.5 13H9.5C10.3284 13 11 13.6716 11 14.5V18.5C11 19.3284 10.3284 20 9.5 20H5.5C4.67157 20 4 19.3284 4 18.5V14.5ZM14.5 13C13.6716 13 13 13.6716 13 14.5V18.5C13 19.3284 13.6716 20 14.5 20H18.5C19.3284 20 20 19.3284 20 18.5V14.5C20 13.6716 19.3284 13 18.5 13H14.5Z" fill="currentColor"/>
                    </svg>
                    Dashboard
                </a>
            </li>
          
            <li x-data="{ open: false }">

                <!-- Parent -->
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm sidebar-link
                        "
                >
                    <div class="flex items-center gap-4">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g style="mix-blend-mode:multiply">
                            <path d="M23.7662 18.4194C24.0774 18.2365 24.0774 17.9397 23.7662 17.7569L11.6736 10.6493C11.4989 10.5591 11.3057 10.5121 11.1098 10.5121C10.9138 10.5121 10.7207 10.5591 10.546 10.6493L5.16309 13.8122L5.69174 14.1236L8.46912 12.4964L20.6346 19.6437L18.0877 21.1384L18.6515 21.4272L23.7662 18.4194Z" fill="url(#paint0_linear_500_6604)"/>
                            </g>
                            <g style="mix-blend-mode:multiply">
                            <path d="M3.30859 14.4359L5.95316 12.8829L17.5887 19.7189L14.9441 21.2731L3.30859 14.4359Z" fill="currentColor"/>
                            </g>
                            <path d="M5.6849 0.753037V14.1161L5.15625 13.8048V0.44165L5.6849 0.753037Z" fill="currentColor"/>
                            <path d="M5.68359 0.755843V14.1189L5.94922 13.9626V0.599487L5.68359 0.755843Z" fill="currentColor"/>
                            <path d="M2.11914 13.1189V12.8088L13.7547 19.6448V19.9561L2.11914 13.1189Z" fill="currentColor"/>
                            <path d="M2.11914 12.8089L4.7637 11.2546L16.3993 18.0919L13.7547 19.6448L2.11914 12.8089Z" fill="currentColor"/>
                            <path d="M16.4011 18.3998L16.3985 18.0884L13.7539 19.6413V19.9527L16.4011 18.3998Z" fill="currentColor"/>
                            <path d="M4.75879 14.3631L7.40335 12.8088L7.66768 12.9639L5.02312 14.5181L4.75879 14.3631Z" fill="currentColor"/>
                            <path d="M7.66992 16.0665L10.3145 14.5135L10.5788 14.6686L7.93424 16.2229L7.66992 16.0665Z" fill="currentColor"/>
                            <path d="M10.8428 17.9316L13.4873 16.3787L13.7517 16.5337L11.1071 18.088L10.8428 17.9316Z" fill="currentColor"/>
                            <path d="M18.5175 7.51839L5.82852 0.0636767C5.52383 -0.123155 4.68658 0.106078 3.69829 0.686449L2.25817 1.53315C1.01206 2.26458 0.00292969 3.27559 0.00292969 3.78573V4.10242L0.263349 3.94739L12.6932 11.2497L18.5175 7.51839Z" fill="currentColor"/>
                            <path d="M18.3802 8.21936V21.5825L17.8516 21.2724V7.9093L18.3802 8.21936Z" fill="currentColor"/>
                            <g style="mix-blend-mode:multiply">
                            <path d="M17.8838 10.2543L18.277 10.0211V10.3325L17.8838 10.5657V10.2543Z" fill="currentColor"/>
                            </g>
                            <path d="M18.277 10.0182L17.9827 9.84595L17.8838 9.90557V10.2514L18.277 10.0182Z" fill="currentColor"/>
                            <g style="mix-blend-mode:multiply">
                            <path d="M17.8838 19.0789L18.277 18.8457V19.1571L17.8838 19.3903V19.0789Z" fill="currentColor"/>
                            </g>
                            <path d="M18.277 18.8478L17.9827 18.6755L17.8838 18.7352V19.081L18.277 18.8478Z" fill="currentColor"/>
                            <path d="M18.3799 8.21484V21.5779L18.6455 21.4216V8.05981L18.3799 8.21484Z" fill="currentColor"/>
                            <path d="M18.6458 8.06529L18.1172 7.75391L17.8516 7.91026L18.3802 8.22032L18.6458 8.06529Z" fill="currentColor"/>
                            <path d="M12.6937 23.0558L12.165 22.7457V12.1798L12.6937 12.4899V23.0558Z" fill="currentColor"/>
                            <path d="M12.165 12.1849L17.4542 9.07629L17.9828 9.38768L12.6937 12.4949L12.165 12.1849Z" fill="currentColor"/>
                            <path d="M17.9844 9.38953V19.9555L12.6953 23.0627V12.4968L17.9844 9.38953Z" fill="currentColor"/>
                            <path d="M12.959 22.5937L17.7195 19.7965V9.85339L12.959 12.6506V22.5937Z" fill="white"/>
                            <path d="M17.7165 19.7965L17.4521 19.6415V10.0084L17.7165 9.85339V19.7965Z" fill="currentColor"/>
                            <path d="M17.7195 19.7952L17.4551 19.6401L12.959 22.2823V22.5923L17.7195 19.7952Z" fill="currentColor"/>
                            <path d="M18.6459 8.06603V7.75464C18.6459 7.24052 17.6368 7.41676 16.3907 8.15216L14.9505 8.99886C13.7044 9.73029 12.6953 10.7413 12.6953 11.2514V11.5628L18.6459 8.06603Z" fill="currentColor"/>
                            <path d="M12.6942 11.5655V11.2541L0 3.79675V4.10681L12.6942 11.5655Z" fill="currentColor"/>
                            <g style="mix-blend-mode:multiply">
                            <path d="M14.8125 23.057L17.8529 21.2709L18.3815 21.5809L15.3412 23.3684L14.8125 23.057Z" fill="url(#paint1_linear_500_6604)"/>
                            </g>
                            <defs>
                            <linearGradient id="paint0_linear_500_6604" x1="14.5813" y1="10.5121" x2="14.5813" y2="21.4272" gradientUnits="userSpaceOnUse">
                            <stop/>
                            <stop offset="1" stop-opacity="0"/>
                            </linearGradient>
                            <linearGradient id="paint1_linear_500_6604" x1="16.597" y1="21.2709" x2="16.597" y2="23.3684" gradientUnits="userSpaceOnUse">
                            <stop/>
                            <stop offset="1" stop-opacity="0"/>
                            </linearGradient>
                            </defs>
                            </svg>
                        <span>All Hoardings</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    {{-- <a  class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.my') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- My Hoardings</span>
                    </a> --}}
                    <a href="{{ route('admin.my-hoardings') }}"
                        class="block py-2 text-sm rounded-md sidebar-submenu-item
                        {{ request()->routeIs('admin.my-hoardings') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}">
                            <span class="submenu-item-indent">- My Hoardings</span>
                    </a>

                   <a href="{{ route('admin.vendor-hoardings.index') }}"
                    class="block py-2 text-sm rounded-md sidebar-submenu-item
                            {{ request()->routeIs('admin.vendor-hoardings.*') ? ' active' : '' }}">
                        <span class="submenu-item-indent">- Vendor's Hoardings</span>
                   </a>

                    {{-- <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Hoardings in draft</span>
                    </a> --}}
                    <a href="{{route('admin.hoardings.drafts')}}" class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Hoardings in draft</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding-attributes.index') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="{{ route('admin.hoarding-attributes.index') }}">
                        <span class="submenu-item-indent">- Category</span>
                    </a>
                </div>
            </li>
             <li x-data="{ open: false }">

                <!-- Parent -->
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium sidebar-link
                        "
                >
                    <div class="flex items-center gap-4">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.275 20.25L20.75 16.8L19.7 15.75L17.275 18.125L16.3 17.15L15.25 18.225L17.275 20.25ZM6 9H18V7H6V9ZM18 23C16.6167 23 15.4377 22.5123 14.463 21.537C13.4883 20.5617 13.0007 19.3827 13 18C13 16.6167 13.4877 15.4377 14.463 14.463C15.4383 13.4883 16.6173 13.0007 18 13C19.3833 13 20.5627 13.4877 21.538 14.463C22.5133 15.4383 23.0007 16.6173 23 18C23 19.3833 22.5123 20.5627 21.537 21.538C20.5617 22.5133 19.3827 23.0007 18 23ZM3 22V5C3 4.45 3.196 3.97933 3.588 3.588C3.98 3.19667 4.45067 3.00067 5 3H19C19.55 3 20.021 3.196 20.413 3.588C20.805 3.98 21.0007 4.45067 21 5V11.675C20.6833 11.525 20.3583 11.4 20.025 11.3C19.6917 11.2 19.35 11.125 19 11.075V5H5V19.05H11.075C11.1583 19.5667 11.2877 20.0583 11.463 20.525C11.6383 20.9917 11.8673 21.4333 12.15 21.85L12 22L10.5 20.5L9 22L7.5 20.5L6 22L4.5 20.5L3 22ZM6 17H11.075C11.125 16.65 11.2 16.3083 11.3 15.975C11.4 15.6417 11.525 15.3167 11.675 15H6V17ZM6 13H13.1C13.7333 12.3833 14.471 11.8957 15.313 11.537C16.155 11.1783 17.0507 10.9993 18 11H6V13Z" fill="currentColor"/>
                            </svg>
                        <span>My Hoarding Enquiry</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a
                        href="{{ route('admin.enquiries.index') }}"
                        class="block py-2 text-sm rounded-md sidebar-submenu-item
                        {{ request()->routeIs('admin.enquiries.*') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}"
                       >
                        <span class="submenu-item-indent">- Enquiries & Offers</span>
                    </a>
                    <a
                        href="{{ route('admin.direct-enquiries.index') }}"
                        class="block py-2 text-sm rounded-md sidebar-submenu-item
                        {{ request()->routeIs('admin.direct-enquiries.*') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}"
                     >
                        <span class="submenu-item-indent">- Direct Enquiry</span>
                    </a>

                </div>
            </li>
            
        </ul>
        <div class="mt-6 mb-2 text-xs font-bold tracking-wider px-2">PEOPLES</div>
        <ul class="space-y-1"> 
            <li x-data="{ open: false }">

                <!-- Parent -->
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm sidebar-link
                        "
                >
                    <div class="flex items-center gap-4">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.07 10.4101C13.6774 9.56132 14.0041 8.54377 14.0041 7.50005C14.0041 6.45634 13.6774 5.43879 13.07 4.59005C13.6385 4.20201 14.3117 3.99622 15 4.00005C15.9283 4.00005 16.8185 4.3688 17.4749 5.02518C18.1313 5.68156 18.5 6.57179 18.5 7.50005C18.5 8.42831 18.1313 9.31855 17.4749 9.97493C16.8185 10.6313 15.9283 11.0001 15 11.0001C14.3117 11.0039 13.6385 10.7981 13.07 10.4101ZM5.5 7.50005C5.5 6.80782 5.70527 6.13113 6.08986 5.55556C6.47444 4.97998 7.02107 4.53138 7.66061 4.26647C8.30015 4.00157 9.00388 3.93226 9.68282 4.0673C10.3617 4.20235 10.9854 4.5357 11.4749 5.02518C11.9644 5.51466 12.2977 6.1383 12.4327 6.81724C12.5678 7.49617 12.4985 8.1999 12.2336 8.83944C11.9687 9.47899 11.5201 10.0256 10.9445 10.4102C10.3689 10.7948 9.69223 11.0001 9 11.0001C8.07174 11.0001 7.1815 10.6313 6.52513 9.97493C5.86875 9.31855 5.5 8.42831 5.5 7.50005ZM7.5 7.50005C7.5 7.79672 7.58797 8.08673 7.7528 8.33341C7.91762 8.58008 8.15189 8.77234 8.42597 8.88587C8.70006 8.9994 9.00166 9.02911 9.29264 8.97123C9.58361 8.91335 9.85088 8.77049 10.0607 8.56071C10.2704 8.35093 10.4133 8.08366 10.4712 7.79269C10.5291 7.50172 10.4994 7.20012 10.3858 6.92603C10.2723 6.65194 10.08 6.41767 9.83335 6.25285C9.58668 6.08803 9.29667 6.00005 9 6.00005C8.60218 6.00005 8.22064 6.15809 7.93934 6.43939C7.65804 6.7207 7.5 7.10223 7.5 7.50005ZM16 17.0001V19.0001H2V17.0001C2 17.0001 2 13.0001 9 13.0001C16 13.0001 16 17.0001 16 17.0001ZM14 17.0001C13.86 16.2201 12.67 15.0001 9 15.0001C5.33 15.0001 4.07 16.3101 4 17.0001M15.95 13.0001C16.5629 13.4768 17.064 14.0819 17.4182 14.7729C17.7723 15.4639 17.9709 16.2241 18 17.0001V19.0001H22V17.0001C22 17.0001 22 13.3701 15.94 13.0001H15.95Z" fill="#949291"/>
                            </svg>
                        <span>Vendor Management</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                   <a href="{{ route('admin.vendors.index') }}"
                        class="block py-2 text-sm rounded-md sidebar-submenu-item
                            {{ request()->routeIs('admin.vendors.*') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        <span class="submenu-item-indent">- Total Vendor's</span>
                    </a>
                </div>
            </li>
            <li x-data="{ open: false }">

                <!-- Parent -->
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between py-2 text-sm sidebar-link
                        "
                 >
                    <div class="flex items-center gap-2">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.07 10.4101C13.6774 9.56132 14.0041 8.54377 14.0041 7.50005C14.0041 6.45634 13.6774 5.43879 13.07 4.59005C13.6385 4.20201 14.3117 3.99622 15 4.00005C15.9283 4.00005 16.8185 4.3688 17.4749 5.02518C18.1313 5.68156 18.5 6.57179 18.5 7.50005C18.5 8.42831 18.1313 9.31855 17.4749 9.97493C16.8185 10.6313 15.9283 11.0001 15 11.0001C14.3117 11.0039 13.6385 10.7981 13.07 10.4101ZM5.5 7.50005C5.5 6.80782 5.70527 6.13113 6.08986 5.55556C6.47444 4.97998 7.02107 4.53138 7.66061 4.26647C8.30015 4.00157 9.00388 3.93226 9.68282 4.0673C10.3617 4.20235 10.9854 4.5357 11.4749 5.02518C11.9644 5.51466 12.2977 6.1383 12.4327 6.81724C12.5678 7.49617 12.4985 8.1999 12.2336 8.83944C11.9687 9.47899 11.5201 10.0256 10.9445 10.4102C10.3689 10.7948 9.69223 11.0001 9 11.0001C8.07174 11.0001 7.1815 10.6313 6.52513 9.97493C5.86875 9.31855 5.5 8.42831 5.5 7.50005ZM7.5 7.50005C7.5 7.79672 7.58797 8.08673 7.7528 8.33341C7.91762 8.58008 8.15189 8.77234 8.42597 8.88587C8.70006 8.9994 9.00166 9.02911 9.29264 8.97123C9.58361 8.91335 9.85088 8.77049 10.0607 8.56071C10.2704 8.35093 10.4133 8.08366 10.4712 7.79269C10.5291 7.50172 10.4994 7.20012 10.3858 6.92603C10.2723 6.65194 10.08 6.41767 9.83335 6.25285C9.58668 6.08803 9.29667 6.00005 9 6.00005C8.60218 6.00005 8.22064 6.15809 7.93934 6.43939C7.65804 6.7207 7.5 7.10223 7.5 7.50005ZM16 17.0001V19.0001H2V17.0001C2 17.0001 2 13.0001 9 13.0001C16 13.0001 16 17.0001 16 17.0001ZM14 17.0001C13.86 16.2201 12.67 15.0001 9 15.0001C5.33 15.0001 4.07 16.3101 4 17.0001M15.95 13.0001C16.5629 13.4768 17.064 14.0819 17.4182 14.7729C17.7723 15.4639 17.9709 16.2241 18 17.0001V19.0001H22V17.0001C22 17.0001 22 13.3701 15.94 13.0001H15.95Z" fill="#949291"/>
                            </svg>
                        <span>Customer Management</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 ">
                    <a href="{{ route('admin.customers.index') }}"
                        class="block py-2 text-sm rounded-md sidebar-submenu-item
                            {{ request()->routeIs('admin.customers.index') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}"
                    >
                        <span class="submenu-item-indent">- Total Customer</span>
                    </a>

                </div>
            </li>
            
            <li>
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
            </li> 
        </ul>
    </nav>
</aside>
@push('scripts')
    <script>
        (function () {

            function getScrollContainer() {
                return document.querySelector('nav.overflow-y-auto');
            }

            function openParentMenu(item) {
                const parentLi = item.closest('li[x-data]');
                if (!parentLi) return;

                const button = parentLi.querySelector('button[type="button"]');
                const submenu = parentLi.querySelector('[x-show]');

                if (submenu && submenu.hasAttribute('style') && submenu.style.display === 'none') {
                    button?.dispatchEvent(new Event('click', { bubbles: true }));
                }
            }

            function scrollToActive(item) {
                const container = getScrollContainer();
                if (!container) return;

                const itemRect = item.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();

                const offset =
                    itemRect.top -
                    containerRect.top +
                    container.scrollTop -
                    120;

                container.scrollTo({
                    top: offset < 0 ? 0 : offset,
                    behavior: 'smooth'
                });
            }

            function initSidebarAutoScroll() {
                const activeItem = document.querySelector('.sidebar-submenu-item.active');
                if (!activeItem) return;

                // Step 1: open parent menu
                openParentMenu(activeItem);

                // Step 2: wait for Alpine DOM render
                setTimeout(() => {
                    scrollToActive(activeItem);
                }, 200); // ⬅️ Alpine animation safe delay
            }

            window.addEventListener('load', initSidebarAutoScroll);

        })();
    </script>
@endpush
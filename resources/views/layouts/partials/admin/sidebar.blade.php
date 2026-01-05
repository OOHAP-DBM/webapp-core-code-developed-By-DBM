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
    <!-- Search -->
    <div class="px-6 pb-2 border-b border-gray-100">
        <div class="relative">
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
        </div>
    </div>
    <!-- Menu -->
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
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.my') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- My Hoardings</span>
                    </a>
                   <a href="{{ route('admin.vendor-hoardings.index') }}"
                    class="block py-2 text-sm rounded-md sidebar-submenu-item
                            {{ request()->routeIs('admin.vendor-hoardings.*') ? ' active' : '' }}">
                        <span class="submenu-item-indent">- Vendor's Hoardings</span>
                   </a>

                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
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
                            <path d="M14.011 22.8605C13.3125 22.8464 12.811 22.7105 12.4781 22.4667L7.91721 20.0245L5.38127 16.575C4.84689 15.75 5.22658 14.1563 6.89533 15.1781L8.72346 17.5875C11.0438 19.5886 15.7781 18.6703 13.5328 14.6531C12.4313 12.3281 12.9469 11.1938 14.3672 10.7156L15.0141 12.8906C16.1344 15.4125 18.225 15.8672 18.1406 17.9016L23.1656 17.5406L23.1188 22.7948L14.011 22.8605ZM11.475 18.0141C10.6594 18.0281 9.85783 17.6906 9.27658 17.2031L7.44377 14.7938C7.94533 14.3953 8.45158 14.6109 8.95314 15.0938C9.54377 14.7938 10.0031 15.2578 10.3781 16.1859C10.5328 16.8188 10.7625 17.2641 11.475 18.0141ZM7.74377 13.5141C7.72502 13.5141 7.70158 13.5141 7.68283 13.5094C7.52814 13.4766 7.34533 13.3125 7.22346 12.9375C7.09689 12.5625 7.06877 12.0281 7.18127 11.4563C7.29377 10.8891 7.52814 10.4063 7.79064 10.1063C8.04377 9.81094 8.27814 9.72657 8.43283 9.75938C8.59221 9.78751 8.77033 9.95625 8.89221 10.3266C9.01877 10.7016 9.05158 11.2406 8.93908 11.8078C8.82189 12.3797 8.58752 12.8625 8.32971 13.1578C8.10471 13.4203 7.89846 13.5141 7.74377 13.5141ZM11.6156 8.50782C11.2453 8.50313 10.7813 8.38594 10.3266 8.16563C9.80627 7.90782 9.39846 7.55157 9.17814 7.22344C8.95783 6.90001 8.93908 6.65626 9.00939 6.51094C9.07971 6.37032 9.28596 6.23438 9.67971 6.21563C10.0735 6.19219 10.6031 6.3 11.1235 6.55782C11.6438 6.81563 12.0516 7.16719 12.2719 7.49532C12.4922 7.81876 12.511 8.06719 12.4406 8.20782C12.3703 8.35313 12.1641 8.48438 11.7703 8.50313C11.7188 8.50782 11.6719 8.50782 11.6156 8.50782ZM7.04064 4.02657C6.82033 4.02188 6.61877 3.98907 6.44064 3.93751C6.04221 3.82032 5.81252 3.60938 5.73752 3.36094C5.65783 3.10782 5.73752 2.80782 6.00471 2.48438C6.27658 2.16563 6.73127 1.86094 7.29377 1.69219C7.85158 1.52345 8.39533 1.52813 8.79846 1.65001C9.20158 1.76719 9.43127 1.97813 9.50627 2.22657C9.58127 2.47969 9.50627 2.77969 9.23439 3.10313C8.96721 3.42188 8.51252 3.72657 7.95002 3.89532C7.63127 3.98907 7.32189 4.03126 7.04064 4.02657Z" fill="currentColor"/>
                            </svg>
                        <span>Earning Dashboard</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.earnings.payments') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Received Payments</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.earnings.withdrawal.request') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Withdrawal Request</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.earnings.withdrawal.approved') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Approved Withdrawals</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.earnings.withdrawal.rejected') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Rejected Withdrawals </span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.earnings.failed') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Failed </span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.earnings.completed') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Completed </span>
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
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy1') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy2') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a href="#" class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy3') ? 'active' : 'text-gray-600 hover:bg-gray-50' }} ">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy4') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy </span>
                    </a>
                </div>
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
                            <g clip-path="url(#clip0_500_24343)">
                            <path d="M7 11H9V13H7V11ZM7 15H9V17H7V15ZM11 11H13V13H11V11ZM11 15H13V17H11V15ZM15 11H17V13H15V11ZM15 15H17V17H15V15Z" fill="currentColor"/>
                            <path d="M4.33333 24H20.6667C21.9535 24 23 22.9236 23 21.6V4.8C23 3.4764 21.9535 2.4 20.6667 2.4H18.3333V0H16V2.4H9V0H6.66667V2.4H4.33333C3.0465 2.4 2 3.4764 2 4.8V21.6C2 22.9236 3.0465 24 4.33333 24ZM20.6667 7.2L20.6678 21.6H4.33333V7.2H20.6667Z" fill="currentColor"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_500_24343">
                            <rect width="24" height="24" fill="white"/>
                            </clipPath>
                            </defs>
                            </svg>

                        <span>My Hoarding Booking</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy1') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy2') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy3') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy4') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy </span>
                    </a>
                </div>
            </li>
            <li>
                <a href="#" class="text-sm sidebar-link {{ request()->routeIs('admin.refunds*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_294_63403)">
                    <path d="M7 11H9V13H7V11ZM7 15H9V17H7V15ZM11 11H13V13H11V11ZM11 15H13V17H11V15ZM15 11H17V13H15V11ZM15 15H17V17H15V15Z" fill="#949291"/>
                    <path d="M4.33333 24H20.6667C21.9535 24 23 22.9236 23 21.6V4.8C23 3.4764 21.9535 2.4 20.6667 2.4H18.3333V0H16V2.4H9V0H6.66667V2.4H4.33333C3.0465 2.4 2 3.4764 2 4.8V21.6C2 22.9236 3.0465 24 4.33333 24ZM20.6667 7.2L20.6678 21.6H4.33333V7.2H20.6667Z" fill="#949291"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_294_63403">
                    <rect width="24" height="24" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg>
                    Multi-Vendor-Bookings
                </a>
            </li>
            <li>
                <a href="#" class="text-sm sidebar-link {{ request()->routeIs('admin.refunds*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.23077 0C4.19723 0 0 3.46154 0 7.84615C0 10.0892 1.22769 12.0443 2.97138 13.4714C2.85879 14.2297 2.55051 14.9455 2.07692 15.5483C1.88365 15.7957 1.68168 16.0361 1.47138 16.2692C1.36252 16.384 1.26571 16.5095 1.18246 16.644C1.12985 16.7298 1.04769 16.8258 1.00985 17.0197C0.971077 17.2126 1.02369 17.5302 1.18246 17.7692L1.29785 17.9714L1.52862 18.0868C2.33631 18.4902 3.20862 18.4191 4.00985 18.2022C4.81015 17.9843 5.58 17.6114 6.31754 17.2209C7.05415 16.8314 7.75477 16.4234 8.30769 16.1252C8.38523 16.0837 8.43508 16.0735 8.50985 16.0385C9.96554 18.0397 12.6314 19.3846 15.6055 19.3846C15.6342 19.3883 15.6609 19.3846 15.6923 19.3846C16.8923 19.3846 20.7692 23.3483 23.0769 21.7791C23.1692 21.4108 21.048 20.4868 20.9418 17.7406C22.7483 16.464 23.9142 14.5652 23.9142 12.4615C23.9142 9.34892 21.444 6.77723 18.1449 5.856C17.1009 2.45908 13.4714 0 9.23077 0ZM9.23077 1.84615C13.428 1.84615 16.6154 4.66154 16.6154 7.84615C16.6154 11.0308 13.428 13.8462 9.23077 13.8462C8.48123 13.8462 8.05108 14.1526 7.44185 14.4812C6.83262 14.8089 6.13385 15.216 5.45169 15.5769C4.86092 15.8889 4.29785 16.1289 3.77908 16.2978C4.284 15.5686 4.81108 14.6095 4.90338 13.2692L4.93292 12.7495L4.5 12.4329C2.85508 11.28 1.84615 9.62123 1.84615 7.84615C1.84615 4.66154 5.03354 1.84615 9.23077 1.84615Z" fill="#949291"/>
                    </svg>
                    Messenger
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
                            <path d="M13.91 2.91L11.83 5H14C16.1217 5 18.1566 5.84285 19.6569 7.34315C21.1571 8.84344 22 10.8783 22 13H20C20 11.4087 19.3679 9.88258 18.2426 8.75736C17.1174 7.63214 15.5913 7 14 7H11.83L13.92 9.09L12.5 10.5L8 6L9.41 4.59L12.5 1.5L13.91 2.91ZM2 12V22H18V12H2ZM4 18.56V15.45C4.60112 15.1009 5.10087 14.6011 5.45 14H14.55C14.8991 14.6011 15.3989 15.1009 16 15.45V18.56C15.4074 18.9089 14.9148 19.4049 14.57 20H5.45C5.09965 19.4024 4.59997 18.9062 4 18.56ZM10 19C10.828 19 11.5 18.105 11.5 17C11.5 15.895 10.828 15 10 15C9.172 15 8.5 15.895 8.5 17C8.5 18.105 9.172 19 10 19Z" fill="#949291"/>
                            </svg>

                        <span>Refunds</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy1') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy2') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy3') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy4') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy </span>
                    </a>
                </div>
            </li>
            <hr class="mt-5 border-gray-200">
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
                            <path d="M17.275 20.25L20.75 16.8L19.7 15.75L17.275 18.125L16.3 17.15L15.25 18.225L17.275 20.25ZM6 9H18V7H6V9ZM18 23C16.6167 23 15.4377 22.5123 14.463 21.537C13.4883 20.5617 13.0007 19.3827 13 18C13 16.6167 13.4877 15.4377 14.463 14.463C15.4383 13.4883 16.6173 13.0007 18 13C19.3833 13 20.5627 13.4877 21.538 14.463C22.5133 15.4383 23.0007 16.6173 23 18C23 19.3833 22.5123 20.5627 21.537 21.538C20.5617 22.5133 19.3827 23.0007 18 23ZM3 22V5C3 4.45 3.196 3.97933 3.588 3.588C3.98 3.19667 4.45067 3.00067 5 3H19C19.55 3 20.021 3.196 20.413 3.588C20.805 3.98 21.0007 4.45067 21 5V11.675C20.6833 11.525 20.3583 11.4 20.025 11.3C19.6917 11.2 19.35 11.125 19 11.075V5H5V19.05H11.075C11.1583 19.5667 11.2877 20.0583 11.463 20.525C11.6383 20.9917 11.8673 21.4333 12.15 21.85L12 22L10.5 20.5L9 22L7.5 20.5L6 22L4.5 20.5L3 22ZM6 17H11.075C11.125 16.65 11.2 16.3083 11.3 15.975C11.4 15.6417 11.525 15.3167 11.675 15H6V17ZM6 13H13.1C13.7333 12.3833 14.471 11.8957 15.313 11.537C16.155 11.1783 17.0507 10.9993 18 11H6V13Z" fill="#949291"/>
                            </svg>
                        <span>POS Booking</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy1') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy2') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy3') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoarding.booking.dummy4') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy </span>
                    </a>
                </div>
            </li>
            <hr class="mt-5 border-gray-200">
            
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
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.vendor') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.category') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
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

                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.vendor') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.category') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                </div>
            </li>
            <li>
                <a href="#" class="text-sm sidebar-link {{ request()->routeIs('admin.refunds*') ? 'active' : '' }}">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.07 10.4101C13.6774 9.56132 14.0041 8.54377 14.0041 7.50005C14.0041 6.45634 13.6774 5.43879 13.07 4.59005C13.6385 4.20201 14.3117 3.99622 15 4.00005C15.9283 4.00005 16.8185 4.3688 17.4749 5.02518C18.1313 5.68156 18.5 6.57179 18.5 7.50005C18.5 8.42831 18.1313 9.31855 17.4749 9.97493C16.8185 10.6313 15.9283 11.0001 15 11.0001C14.3117 11.0039 13.6385 10.7981 13.07 10.4101ZM5.5 7.50005C5.5 6.80782 5.70527 6.13113 6.08986 5.55556C6.47444 4.97998 7.02107 4.53138 7.66061 4.26647C8.30015 4.00157 9.00388 3.93226 9.68282 4.0673C10.3617 4.20235 10.9854 4.5357 11.4749 5.02518C11.9644 5.51466 12.2977 6.1383 12.4327 6.81724C12.5678 7.49617 12.4985 8.1999 12.2336 8.83944C11.9687 9.47899 11.5201 10.0256 10.9445 10.4102C10.3689 10.7948 9.69223 11.0001 9 11.0001C8.07174 11.0001 7.1815 10.6313 6.52513 9.97493C5.86875 9.31855 5.5 8.42831 5.5 7.50005ZM7.5 7.50005C7.5 7.79672 7.58797 8.08673 7.7528 8.33341C7.91762 8.58008 8.15189 8.77234 8.42597 8.88587C8.70006 8.9994 9.00166 9.02911 9.29264 8.97123C9.58361 8.91335 9.85088 8.77049 10.0607 8.56071C10.2704 8.35093 10.4133 8.08366 10.4712 7.79269C10.5291 7.50172 10.4994 7.20012 10.3858 6.92603C10.2723 6.65194 10.08 6.41767 9.83335 6.25285C9.58668 6.08803 9.29667 6.00005 9 6.00005C8.60218 6.00005 8.22064 6.15809 7.93934 6.43939C7.65804 6.7207 7.5 7.10223 7.5 7.50005ZM16 17.0001V19.0001H2V17.0001C2 17.0001 2 13.0001 9 13.0001C16 13.0001 16 17.0001 16 17.0001ZM14 17.0001C13.86 16.2201 12.67 15.0001 9 15.0001C5.33 15.0001 4.07 16.3101 4 17.0001M15.95 13.0001C16.5629 13.4768 17.064 14.0819 17.4182 14.7729C17.7723 15.4639 17.9709 16.2241 18 17.0001V19.0001H22V17.0001C22 17.0001 22 13.3701 15.94 13.0001H15.95Z" fill="#949291"/>
                        </svg>
                    My Brand Manager's
                </a>
            </li>
            <li x-data="{ open: false }">

                <!-- Parent -->
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between py-2 text-sm sidebar-link
                        "
                >
                    <div class="flex items-center gap-4">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.07 10.4101C13.6774 9.56132 14.0041 8.54377 14.0041 7.50005C14.0041 6.45634 13.6774 5.43879 13.07 4.59005C13.6385 4.20201 14.3117 3.99622 15 4.00005C15.9283 4.00005 16.8185 4.3688 17.4749 5.02518C18.1313 5.68156 18.5 6.57179 18.5 7.50005C18.5 8.42831 18.1313 9.31855 17.4749 9.97493C16.8185 10.6313 15.9283 11.0001 15 11.0001C14.3117 11.0039 13.6385 10.7981 13.07 10.4101ZM5.5 7.50005C5.5 6.80782 5.70527 6.13113 6.08986 5.55556C6.47444 4.97998 7.02107 4.53138 7.66061 4.26647C8.30015 4.00157 9.00388 3.93226 9.68282 4.0673C10.3617 4.20235 10.9854 4.5357 11.4749 5.02518C11.9644 5.51466 12.2977 6.1383 12.4327 6.81724C12.5678 7.49617 12.4985 8.1999 12.2336 8.83944C11.9687 9.47899 11.5201 10.0256 10.9445 10.4102C10.3689 10.7948 9.69223 11.0001 9 11.0001C8.07174 11.0001 7.1815 10.6313 6.52513 9.97493C5.86875 9.31855 5.5 8.42831 5.5 7.50005ZM7.5 7.50005C7.5 7.79672 7.58797 8.08673 7.7528 8.33341C7.91762 8.58008 8.15189 8.77234 8.42597 8.88587C8.70006 8.9994 9.00166 9.02911 9.29264 8.97123C9.58361 8.91335 9.85088 8.77049 10.0607 8.56071C10.2704 8.35093 10.4133 8.08366 10.4712 7.79269C10.5291 7.50172 10.4994 7.20012 10.3858 6.92603C10.2723 6.65194 10.08 6.41767 9.83335 6.25285C9.58668 6.08803 9.29667 6.00005 9 6.00005C8.60218 6.00005 8.22064 6.15809 7.93934 6.43939C7.65804 6.7207 7.5 7.10223 7.5 7.50005ZM16 17.0001V19.0001H2V17.0001C2 17.0001 2 13.0001 9 13.0001C16 13.0001 16 17.0001 16 17.0001ZM14 17.0001C13.86 16.2201 12.67 15.0001 9 15.0001C5.33 15.0001 4.07 16.3101 4 17.0001M15.95 13.0001C16.5629 13.4768 17.064 14.0819 17.4182 14.7729C17.7723 15.4639 17.9709 16.2241 18 17.0001V19.0001H22V17.0001C22 17.0001 22 13.3701 15.94 13.0001H15.95Z" fill="#949291"/>
                            </svg>
                        <span>My Staff</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.my') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.vendor') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.category') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
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
                    <div class="flex items-center gap-4">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14.625 4.76927H21V21H4V4L14.625 14.8205V4.76927ZM5.0625 19.918H18.1196L5.0625 6.62059V19.918ZM18.5679 18.8359L19.6304 19.918H19.9375V5.85132H15.6875V6.93337H17.8125V8.01542H15.6875V9.09746H17.8125V10.1795H15.6875V11.2616H18.875V12.3436H15.6875V13.4257H17.8125V14.5077H15.6875V15.5898H17.8125V16.6718H16.4429L17.5054 17.7539H18.875V18.8359H18.5679ZM7.1875 12.6564L12.1929 17.7539H7.1875V12.6564ZM8.25 15.277V16.6718H9.61963L8.25 15.277Z" fill="#949291"/>
                            </svg>
                        <span>Graphics Designer</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.my') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.vendor') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.category') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
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
                    <div class="flex items-center gap-4">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20.1253 6.75H18.75V3.75C18.75 3.55109 18.671 3.36032 18.5303 3.21967C18.3897 3.07902 18.1989 3 18 3H6C5.80109 3 5.61032 3.07902 5.46967 3.21967C5.32902 3.36032 5.25 3.55109 5.25 3.75V6.75H3.87469C2.565 6.75 1.5 7.75969 1.5 9V16.5C1.5 16.6989 1.57902 16.8897 1.71967 17.0303C1.86032 17.171 2.05109 17.25 2.25 17.25H5.25V20.25C5.25 20.4489 5.32902 20.6397 5.46967 20.7803C5.61032 20.921 5.80109 21 6 21H18C18.1989 21 18.3897 20.921 18.5303 20.7803C18.671 20.6397 18.75 20.4489 18.75 20.25V17.25H21.75C21.9489 17.25 22.1397 17.171 22.2803 17.0303C22.421 16.8897 22.5 16.6989 22.5 16.5V9C22.5 7.75969 21.435 6.75 20.1253 6.75ZM6.75 4.5H17.25V6.75H6.75V4.5ZM17.25 19.5H6.75V15H17.25V19.5ZM21 15.75H18.75V14.25C18.75 14.0511 18.671 13.8603 18.5303 13.7197C18.3897 13.579 18.1989 13.5 18 13.5H6C5.80109 13.5 5.61032 13.579 5.46967 13.7197C5.32902 13.8603 5.25 14.0511 5.25 14.25V15.75H3V9C3 8.58656 3.39281 8.25 3.87469 8.25H20.1253C20.6072 8.25 21 8.58656 21 9V15.75ZM18.75 10.875C18.75 11.0975 18.684 11.315 18.5604 11.5C18.4368 11.685 18.2611 11.8292 18.0555 11.9144C17.85 11.9995 17.6238 12.0218 17.4055 11.9784C17.1873 11.935 16.9868 11.8278 16.8295 11.6705C16.6722 11.5132 16.565 11.3127 16.5216 11.0945C16.4782 10.8762 16.5005 10.65 16.5856 10.4445C16.6708 10.2389 16.815 10.0632 17 9.9396C17.185 9.81598 17.4025 9.75 17.625 9.75C17.9234 9.75 18.2095 9.86853 18.4205 10.0795C18.6315 10.2905 18.75 10.5766 18.75 10.875Z" fill="#949291"/>
                            </svg>
                        <span>Printer</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.my') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.vendor') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.category') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
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
                    <div class="flex items-center gap-4">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4.12493 3.001L3 4.12587L5.47484 7.59158C5.57903 7.73752 5.7166 7.85644 5.87608 7.93843C6.03556 8.02042 6.21233 8.06309 6.39166 8.06289H6.4704C6.61831 8.06278 6.76478 8.09183 6.90145 8.14838C7.03811 8.20494 7.16228 8.28789 7.26685 8.39248L10.276 11.4015L7.33209 14.3869C6.82857 14.2387 6.29743 14.2098 5.78082 14.3026C5.2642 14.3954 4.77632 14.6073 4.35587 14.9215C3.93543 15.2357 3.59398 15.6436 3.35863 16.1127C3.12328 16.5819 3.00049 17.0994 3 17.6242C3.00067 18.1079 3.1053 18.5857 3.30678 19.0254C3.50826 19.465 3.8019 19.8562 4.16779 20.1725C4.53369 20.4888 4.96329 20.7227 5.4275 20.8585C5.8917 20.9942 6.37967 21.0286 6.85833 20.9593C7.33699 20.89 7.79516 20.7187 8.20181 20.4568C8.60845 20.195 8.95406 19.8488 9.21523 19.4418C9.4764 19.0347 9.64702 18.5763 9.71553 18.0976C9.78404 17.6188 9.74884 17.1309 9.61232 16.667L12.5979 13.7232L13.6868 14.8121L13.3437 15.8402C13.2778 16.0384 13.2683 16.251 13.3164 16.4542C13.3645 16.6575 13.4682 16.8433 13.6159 16.9909L17.2944 20.6693C17.3987 20.7741 17.5226 20.8573 17.6591 20.914C17.7956 20.9708 17.9419 21 18.0898 21C18.2376 21 18.384 20.9708 18.5205 20.914C18.6569 20.8573 18.7809 20.7741 18.8851 20.6693L20.6692 18.8852C20.7741 18.781 20.8572 18.6571 20.914 18.5206C20.9708 18.3841 21 18.2378 21 18.0899C21 17.9421 20.9708 17.7958 20.914 17.6593C20.8572 17.5228 20.7741 17.3989 20.6692 17.2947L16.9907 13.6164C16.8431 13.4686 16.6572 13.3649 16.454 13.3168C16.2507 13.2688 16.0381 13.2782 15.8399 13.3441L14.8117 13.6872L13.7318 12.6073L16.7466 9.63433C17.2467 9.76782 17.7708 9.78469 18.2785 9.68361C18.7862 9.58254 19.2639 9.36623 19.6747 9.05137C20.0856 8.73651 20.4186 8.3315 20.6482 7.86758C20.8778 7.40365 20.9977 6.89321 20.9988 6.3756C20.9988 6.07188 20.9606 5.78054 20.8841 5.50158L18.4767 7.90991L16.4991 7.50046L16.0897 5.52408L18.4981 3.11574C17.9252 2.9617 17.3218 2.96142 16.7487 3.11491C16.1756 3.2684 15.6531 3.57024 15.2339 3.99002C14.8147 4.40979 14.5135 4.93266 14.3608 5.50591C14.2081 6.07917 14.2092 6.68255 14.364 7.25524L11.3942 10.2676L8.39178 7.26762C8.1808 7.05671 8.06224 6.77064 8.06217 6.47234V6.39247C8.06219 6.21335 8.01943 6.03681 7.93744 5.87755C7.85546 5.71828 7.73663 5.5809 7.59083 5.47683L4.12493 3.001ZM14.976 14.9763C15.0282 14.9239 15.0903 14.8824 15.1586 14.854C15.227 14.8257 15.3002 14.8111 15.3742 14.8111C15.4482 14.8111 15.5214 14.8257 15.5898 14.854C15.6581 14.8824 15.7202 14.9239 15.7724 14.9763L19.0505 18.2553C19.1529 18.3614 19.2096 18.5034 19.2083 18.6509C19.207 18.7984 19.1479 18.9394 19.0436 19.0437C18.9393 19.148 18.7983 19.2071 18.6508 19.2084C18.5033 19.2097 18.3612 19.153 18.2551 19.0506L14.976 15.7727C14.9236 15.7205 14.882 15.6584 14.8537 15.5901C14.8253 15.5217 14.8107 15.4485 14.8107 15.3745C14.8107 15.3005 14.8253 15.2273 14.8537 15.159C14.882 15.0906 14.9236 15.0286 14.976 14.9763ZM6.37478 15.3745L6.90462 15.6467L7.49971 15.676L7.82256 16.1765L8.32316 16.4994L8.3524 17.0944L8.62464 17.6242L8.3524 18.1541L8.32316 18.7491L7.82256 19.0719L7.49971 19.5725L6.90462 19.6018L6.37478 19.874L5.84494 19.6018L5.24985 19.5725L4.927 19.0719L4.42641 18.7491L4.39716 18.1541L4.12493 17.6242L4.39716 17.0944L4.42641 16.4994L4.927 16.1765L5.24985 15.676L5.84494 15.6467L6.37478 15.3745Z" fill="#949291"/>
                            </svg>
                        <span>Mounter</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.my') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.vendor') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.category') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
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
                    <div class="flex items-center gap-4">
                           <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.5747 10.9171L20.2266 7.25866L19.6351 6.66716L16.5747 9.72758L15.0309 8.20441L14.4394 8.80241L16.5747 10.9171ZM4.33301 21.1253V20.042H16.2497V21.1253H4.33301ZM17.3363 13.542C15.9886 13.542 14.8385 13.0671 13.8858 12.1174C12.934 11.167 12.458 10.0179 12.458 8.67024C12.458 7.32258 12.9329 6.17244 13.8826 5.21983C14.833 4.26794 15.9821 3.79199 17.3298 3.79199C18.6774 3.79199 19.8276 4.26685 20.7802 5.21658C21.7321 6.16702 22.208 7.31608 22.208 8.66374C22.208 10.0114 21.7331 11.1615 20.7834 12.1142C19.833 13.066 18.6839 13.542 17.3363 13.542ZM4.33301 12.4587V11.3753H10.1581C10.2289 11.5775 10.3072 11.7657 10.3932 11.9397C10.4798 12.1131 10.5777 12.286 10.6868 12.4587H4.33301ZM4.33301 16.792V15.7087H14.3701C14.6604 15.8365 14.9638 15.9481 15.2801 16.0434C15.5979 16.138 15.9211 16.2077 16.2497 16.2525V16.792H4.33301Z" fill="#949291"/>
                            </svg>
                        <span>Servayer</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.my') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.vendor') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.category') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                </div>
            </li>
            <hr class="mt-5 border-gray-200">
            <li x-data="{ open: false }">

                <!-- Parent -->
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between py-2 text-sm sidebar-link
                        "
                >
                    <div class="flex items-center gap-4">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 4C2 3.73478 2.10536 3.48043 2.29289 3.29289C2.48043 3.10536 2.73478 3 3 3H21C21.2652 3 21.5196 3.10536 21.7071 3.29289C21.8946 3.48043 22 3.73478 22 4V20C22 20.2652 21.8946 20.5196 21.7071 20.7071C21.5196 20.8946 21.2652 21 21 21H3C2.73478 21 2.48043 20.8946 2.29289 20.7071C2.10536 20.5196 2 20.2652 2 20V4ZM4 5V19H20V5H4ZM6 7H12V13H6V7ZM8 9V11H10V9H8ZM14 9H18V7H14V9ZM18 13H14V11H18V13ZM6 15V17H18V15H6Z" fill="#949291"/>
                            </svg>
                        <span>News</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.my') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.vendor') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.category') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
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
                    <div class="flex items-center gap-4">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 11H9V13H7V11ZM7 15H9V17H7V15ZM11 11H13V13H11V11ZM11 15H13V17H11V15ZM15 11H17V13H15V11ZM15 15H17V17H15V15Z" fill="#949291"/>
                            <path d="M5 22H19C20.103 22 21 21.103 21 20V6C21 4.897 20.103 4 19 4H17V2H15V4H9V2H7V4H5C3.897 4 3 4.897 3 6V20C3 21.103 3.897 22 5 22ZM19 8L19.001 20H5V8H19Z" fill="#949291"/>
                            </svg>
                        <span>Appointments</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.my') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.vendor') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.category') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
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
                    <div class="flex items-center gap-4">
                           <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8.4498 7.79961C8.4498 8.31678 8.24436 8.81277 7.87866 9.17847C7.51297 9.54416 7.01698 9.74961 6.4998 9.74961C5.98263 9.74961 5.48664 9.54416 5.12095 9.17847C4.75525 8.81277 4.5498 8.31678 4.5498 7.79961C4.5498 7.28244 4.75525 6.78645 5.12095 6.42075C5.48664 6.05505 5.98263 5.84961 6.4998 5.84961C7.01698 5.84961 7.51297 6.05505 7.87866 6.42075C8.24436 6.78645 8.4498 7.28244 8.4498 7.79961ZM8.4498 12.9996C8.4498 13.5168 8.24436 14.0128 7.87866 14.3785C7.51297 14.7442 7.01698 14.9496 6.4998 14.9496C5.98263 14.9496 5.48664 14.7442 5.12095 14.3785C4.75525 14.0128 4.5498 13.5168 4.5498 12.9996C4.5498 12.4824 4.75525 11.9864 5.12095 11.6208C5.48664 11.2551 5.98263 11.0496 6.4998 11.0496C7.01698 11.0496 7.51297 11.2551 7.87866 11.6208C8.24436 11.9864 8.4498 12.4824 8.4498 12.9996ZM8.4498 18.1996C8.4498 18.7168 8.24436 19.2128 7.87866 19.5785C7.51297 19.9442 7.01698 20.1496 6.4998 20.1496C5.98263 20.1496 5.48664 19.9442 5.12095 19.5785C4.75525 19.2128 4.5498 18.7168 4.5498 18.1996C4.5498 17.6824 4.75525 17.1864 5.12095 16.8208C5.48664 16.4551 5.98263 16.2496 6.4998 16.2496C7.01698 16.2496 7.51297 16.4551 7.87866 16.8208C8.24436 17.1864 8.4498 17.6824 8.4498 18.1996Z" fill="#949291"/>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.75 8.44883C9.75 8.27644 9.81848 8.11111 9.94038 7.98921C10.0623 7.86731 10.2276 7.79883 10.4 7.79883H20.8C20.9724 7.79883 21.1377 7.86731 21.2596 7.98921C21.3815 8.11111 21.45 8.27644 21.45 8.44883C21.45 8.62122 21.3815 8.78655 21.2596 8.90845C21.1377 9.03035 20.9724 9.09883 20.8 9.09883H10.4C10.2276 9.09883 10.0623 9.03035 9.94038 8.90845C9.81848 8.78655 9.75 8.62122 9.75 8.44883ZM9.75 13.6488C9.75 13.4764 9.81848 13.3111 9.94038 13.1892C10.0623 13.0673 10.2276 12.9988 10.4 12.9988H20.8C20.9724 12.9988 21.1377 13.0673 21.2596 13.1892C21.3815 13.3111 21.45 13.4764 21.45 13.6488C21.45 13.8212 21.3815 13.9865 21.2596 14.1084C21.1377 14.2303 20.9724 14.2988 20.8 14.2988H10.4C10.2276 14.2988 10.0623 14.2303 9.94038 14.1084C9.81848 13.9865 9.75 13.8212 9.75 13.6488ZM9.75 18.8488C9.75 18.6764 9.81848 18.5111 9.94038 18.3892C10.0623 18.2673 10.2276 18.1988 10.4 18.1988H20.8C20.9724 18.1988 21.1377 18.2673 21.2596 18.3892C21.3815 18.5111 21.45 18.6764 21.45 18.8488C21.45 19.0212 21.3815 19.1865 21.2596 19.3084C21.1377 19.4303 20.9724 19.4988 20.8 19.4988H10.4C10.2276 19.4988 10.0623 19.4303 9.94038 19.3084C9.81848 19.1865 9.75 19.0212 9.75 18.8488Z" fill="#949291"/>
                            </svg>
                        <span>Reports</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.my') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.vendor') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.category') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
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
                    <div class="flex items-center gap-4">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14 10H3V12H14V10ZM14 6H3V8H14V6ZM3 16H10V14H3V16ZM21.5 11.5L23 13L16 20L11.5 15.5L13 14L16 17L21.5 11.5Z" fill="#949291"/>
                            </svg>
                        <span>Manage Subscriptions</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.my') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.vendor') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.category') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                </div>
            </li>
            <li>
                <a href="#" class="text-sm sidebar-link {{ request()->routeIs('admin.refunds*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.23077 0C4.19723 0 0 3.46154 0 7.84615C0 10.0892 1.22769 12.0443 2.97138 13.4714C2.85879 14.2297 2.55051 14.9455 2.07692 15.5483C1.88365 15.7957 1.68168 16.0361 1.47138 16.2692C1.36252 16.384 1.26571 16.5095 1.18246 16.644C1.12985 16.7298 1.04769 16.8258 1.00985 17.0197C0.971077 17.2126 1.02369 17.5302 1.18246 17.7692L1.29785 17.9714L1.52862 18.0868C2.33631 18.4902 3.20862 18.4191 4.00985 18.2022C4.81015 17.9843 5.58 17.6114 6.31754 17.2209C7.05415 16.8314 7.75477 16.4234 8.30769 16.1252C8.38523 16.0837 8.43508 16.0735 8.50985 16.0385C9.96554 18.0397 12.6314 19.3846 15.6055 19.3846C15.6342 19.3883 15.6609 19.3846 15.6923 19.3846C16.8923 19.3846 20.7692 23.3483 23.0769 21.7791C23.1692 21.4108 21.048 20.4868 20.9418 17.7406C22.7483 16.464 23.9142 14.5652 23.9142 12.4615C23.9142 9.34892 21.444 6.77723 18.1449 5.856C17.1009 2.45908 13.4714 0 9.23077 0ZM9.23077 1.84615C13.428 1.84615 16.6154 4.66154 16.6154 7.84615C16.6154 11.0308 13.428 13.8462 9.23077 13.8462C8.48123 13.8462 8.05108 14.1526 7.44185 14.4812C6.83262 14.8089 6.13385 15.216 5.45169 15.5769C4.86092 15.8889 4.29785 16.1289 3.77908 16.2978C4.284 15.5686 4.81108 14.6095 4.90338 13.2692L4.93292 12.7495L4.5 12.4329C2.85508 11.28 1.84615 9.62123 1.84615 7.84615C1.84615 4.66154 5.03354 1.84615 9.23077 1.84615Z" fill="#949291"/>
                    </svg>
                    Messenger
                </a>
            </li>
            <li x-data="{ open: false }">

                <!-- Parent -->
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between py-2 text-sm sidebar-link
                        "
                >
                    <div class="flex items-center gap-4">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.0784 2C13.3724 2 13.6354 2.183 13.7344 2.457L14.4404 4.414C14.6934 4.477 14.9104 4.54 15.0944 4.606C15.2954 4.678 15.5544 4.787 15.8744 4.936L17.5184 4.066C17.6526 3.99491 17.8062 3.96925 17.9562 3.99287C18.1062 4.01649 18.2445 4.08811 18.3504 4.197L19.7964 5.692C19.9884 5.891 20.0424 6.182 19.9344 6.436L19.1634 8.243C19.2914 8.478 19.3934 8.679 19.4714 8.847C19.5554 9.03 19.6594 9.282 19.7834 9.607L21.5804 10.377C21.8504 10.492 22.0174 10.762 21.9994 11.051L21.8674 13.126C21.8583 13.2608 21.8099 13.39 21.7282 13.4975C21.6464 13.6051 21.5349 13.6863 21.4074 13.731L19.7054 14.336C19.6564 14.571 19.6054 14.772 19.5514 14.942C19.4643 15.2045 19.3648 15.4628 19.2534 15.716L20.1084 17.606C20.1687 17.7388 20.185 17.8874 20.1547 18.0301C20.1245 18.1728 20.0494 18.3021 19.9404 18.399L18.3144 19.851C18.2073 19.9462 18.0737 20.0064 17.9314 20.0236C17.7891 20.0408 17.645 20.014 17.5184 19.947L15.8424 19.059C15.5802 19.1978 15.3096 19.3204 15.0324 19.426L14.3004 19.7L13.6504 21.5C13.6022 21.6318 13.5153 21.746 13.4011 21.8276C13.2869 21.9091 13.1507 21.9542 13.0104 21.957L11.1104 22C10.9663 22.0038 10.8247 21.9628 10.7049 21.8828C10.5851 21.8027 10.493 21.6875 10.4414 21.553L9.67537 19.526C9.41401 19.4367 9.15524 19.34 8.89937 19.236C8.69008 19.1454 8.48396 19.0477 8.28137 18.943L6.38137 19.755C6.25618 19.8084 6.11816 19.8243 5.98411 19.8007C5.85006 19.7771 5.72577 19.715 5.62637 19.622L4.22037 18.303C4.11569 18.2052 4.0444 18.077 4.01658 17.9365C3.98877 17.796 4.00583 17.6503 4.06537 17.52L4.88237 15.74C4.77371 15.5292 4.67297 15.3144 4.58037 15.096C4.47227 14.8287 4.37222 14.5583 4.28037 14.285L2.49037 13.74C2.34487 13.696 2.21796 13.6052 2.12936 13.4817C2.04075 13.3582 1.99541 13.2089 2.00037 13.057L2.07037 11.136C2.07535 11.0107 2.1145 10.8891 2.1836 10.7844C2.25269 10.6797 2.34909 10.5959 2.46237 10.542L4.34037 9.64C4.42737 9.321 4.50337 9.073 4.57037 8.892C4.66471 8.65025 4.76947 8.41269 4.88437 8.18L4.07037 6.46C4.00859 6.32938 3.98983 6.18254 4.01679 6.04059C4.04374 5.89864 4.11502 5.76889 4.22037 5.67L5.62437 4.344C5.72279 4.25117 5.84594 4.18876 5.979 4.16428C6.11205 4.1398 6.24935 4.15429 6.37437 4.206L8.27237 4.99C8.48237 4.85 8.67237 4.737 8.84437 4.646C9.04937 4.537 9.32337 4.423 9.66837 4.3L10.3284 2.459C10.3772 2.32427 10.4664 2.20788 10.5838 2.12573C10.7012 2.04358 10.8411 1.99967 10.9844 2H13.0784ZM12.5884 3.377H11.4754L10.8704 5.071C10.8348 5.16978 10.7772 5.25918 10.7021 5.33246C10.6269 5.40574 10.536 5.46097 10.4364 5.494C10.0004 5.639 9.68537 5.764 9.50137 5.861C9.30637 5.964 9.05737 6.121 8.76137 6.331C8.66449 6.39899 8.55201 6.44142 8.43436 6.45436C8.31672 6.46729 8.19771 6.45032 8.08837 6.405L6.25837 5.65L5.54537 6.324L6.28837 7.894C6.33285 7.98741 6.35543 8.08974 6.35439 8.1932C6.35335 8.29665 6.32872 8.39851 6.28237 8.491C6.08237 8.892 5.94737 9.188 5.87937 9.37C5.77509 9.67297 5.68499 9.98064 5.60937 10.292C5.58424 10.3895 5.53811 10.4802 5.47419 10.558C5.41027 10.6357 5.33013 10.6985 5.23937 10.742L3.44937 11.601L3.41337 12.581L5.03337 13.073C5.24837 13.138 5.41837 13.303 5.48937 13.515C5.64937 13.995 5.77737 14.349 5.86937 14.571C5.99127 14.8527 6.12608 15.1287 6.27337 15.398C6.3232 15.4907 6.35085 15.5936 6.35415 15.6988C6.35744 15.8039 6.33629 15.9084 6.29237 16.004L5.54137 17.642L6.25237 18.31L8.03437 17.548C8.13035 17.507 8.23427 17.4879 8.33857 17.492C8.44288 17.4962 8.54494 17.5235 8.63737 17.572C9.00237 17.764 9.27437 17.897 9.44637 17.97C9.62137 18.043 9.95637 18.165 10.4424 18.331C10.5388 18.3639 10.6269 18.4178 10.7002 18.4886C10.7735 18.5595 10.8302 18.6457 10.8664 18.741L11.5744 20.612L12.5004 20.592L13.0974 18.938C13.1315 18.8436 13.1858 18.7578 13.2564 18.6865C13.327 18.6152 13.4123 18.5601 13.5064 18.525L14.5434 18.137C14.8054 18.04 15.1234 17.887 15.4944 17.677C15.597 17.6193 15.7126 17.5884 15.8303 17.587C15.9481 17.5856 16.0643 17.6138 16.1684 17.669L17.7454 18.504L18.6324 17.713L17.8564 16C17.8165 15.9121 17.7958 15.8167 17.7956 15.7201C17.7955 15.6236 17.8158 15.5281 17.8554 15.44C18.0374 15.033 18.1604 14.726 18.2224 14.53C18.2834 14.338 18.3464 14.061 18.4074 13.705C18.4283 13.5841 18.4812 13.4709 18.5605 13.3772C18.6398 13.2836 18.7426 13.2127 18.8584 13.172L20.5064 12.587L20.5784 11.447L18.9584 10.753C18.8721 10.7163 18.7942 10.6624 18.7294 10.5947C18.6646 10.527 18.6142 10.4468 18.5814 10.359C18.4658 10.0402 18.3398 9.7254 18.2034 9.415C18.0742 9.14473 17.9341 8.87985 17.7834 8.621C17.7311 8.52955 17.7008 8.42725 17.6947 8.32211C17.6887 8.21697 17.707 8.11186 17.7484 8.015L18.4734 6.315L17.7094 5.525L16.2214 6.313C16.1242 6.36452 16.0163 6.39254 15.9064 6.3948C15.7965 6.39705 15.6875 6.37349 15.5884 6.326C15.2725 6.16897 14.9495 6.02681 14.6204 5.9C14.3397 5.80575 14.0535 5.72894 13.7634 5.67C13.6482 5.64559 13.5412 5.59231 13.4524 5.51517C13.3635 5.43802 13.2957 5.33955 13.2554 5.229L12.5884 3.377ZM12.0244 7.641C14.4594 7.641 16.4344 9.594 16.4344 12.002C16.4344 14.41 14.4594 16.362 12.0244 16.362C9.58837 16.362 7.61437 14.41 7.61437 12.002C7.61437 9.594 9.58837 7.642 12.0244 7.642M12.0244 9.02C10.3574 9.02 9.00637 10.355 9.00637 12.003C9.00637 13.651 10.3574 14.987 12.0244 14.987C13.6904 14.987 15.0414 13.651 15.0414 12.003C15.0414 10.355 13.6914 9.02 12.0244 9.02Z" fill="#949291"/>
                            </svg>
                        <span>Settings</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.my') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.vendor') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.draft') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm rounded-md sidebar-submenu-item {{ request()->routeIs('admin.hoardings.category') ? 'active' : 'text-gray-600 hover:bg-gray-50' }}" href="">
                        <span class="submenu-item-indent">- Dummy</span>
                    </a>
                </div>
            </li>
            <li>
                <a href="#" class="text-sm sidebar-link {{ request()->routeIs('admin.refunds*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_504_27409)">
                    <path d="M5.14314 11.9994V7.49084C5.15653 6.60461 5.34465 5.72973 5.69674 4.91633C6.04883 4.10293 6.55796 3.367 7.19496 2.7507C7.83196 2.13441 8.5843 1.64986 9.40889 1.32483C10.2335 0.999794 11.1141 0.840663 12.0003 0.856551C12.8865 0.840663 13.7671 0.999794 14.5917 1.32483C15.4163 1.64986 16.1686 2.13441 16.8056 2.7507C17.4426 3.367 17.9517 4.10293 18.3038 4.91633C18.6559 5.72973 18.844 6.60461 18.8574 7.49084V11.9994M15.4289 20.9994C16.3382 20.9994 17.2102 20.6382 17.8532 19.9952C18.4962 19.3522 18.8574 18.4802 18.8574 17.5708V13.7137M15.4289 20.9994C15.4289 21.5677 15.2031 22.1128 14.8012 22.5146C14.3994 22.9165 13.8543 23.1423 13.286 23.1423H10.7146C10.1462 23.1423 9.6012 22.9165 9.19934 22.5146C8.79747 22.1128 8.57171 21.5677 8.57171 20.9994C8.57171 20.4311 8.79747 19.886 9.19934 19.4842C9.6012 19.0823 10.1462 18.8566 10.7146 18.8566H13.286C13.8543 18.8566 14.3994 19.0823 14.8012 19.4842C15.2031 19.886 15.4289 20.4311 15.4289 20.9994ZM2.57171 9.42798H4.28599C4.51332 9.42798 4.73134 9.51829 4.89208 9.67903C5.05283 9.83978 5.14314 10.0578 5.14314 10.2851V15.428C5.14314 15.6553 5.05283 15.8733 4.89208 16.0341C4.73134 16.1948 4.51332 16.2851 4.28599 16.2851H2.57171C2.11705 16.2851 1.68102 16.1045 1.35952 15.783C1.03803 15.4615 0.857422 15.0255 0.857422 14.5708V11.1423C0.857422 10.6876 1.03803 10.2516 1.35952 9.93008C1.68102 9.60859 2.11705 9.42798 2.57171 9.42798ZM21.4289 16.2851H19.7146C19.4872 16.2851 19.2692 16.1948 19.1085 16.0341C18.9477 15.8733 18.8574 15.6553 18.8574 15.428V10.2851C18.8574 10.0578 18.9477 9.83978 19.1085 9.67903C19.2692 9.51829 19.4872 9.42798 19.7146 9.42798H21.4289C21.8835 9.42798 22.3195 9.60859 22.641 9.93008C22.9625 10.2516 23.1431 10.6876 23.1431 11.1423V14.5708C23.1431 15.0255 22.9625 15.4615 22.641 15.783C22.3195 16.1045 21.8835 16.2851 21.4289 16.2851Z" stroke="#949291" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_504_27409">
                    <rect width="24" height="24" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg>
                    Support
                </a>
            </li>
            <li>
                <a href="#" class="text-sm sidebar-link {{ request()->routeIs('admin.refunds*') ? 'active' : '' }}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="4" y="4" width="7" height="7" rx="1.5" fill="#949291"/>
                    <path opacity="0.3" d="M9.5 13C10.3284 13 11 13.6716 11 14.5V18.5C11 19.3284 10.3284 20 9.5 20H5.5C4.67157 20 4 19.3284 4 18.5V14.5C4 13.6716 4.67157 13 5.5 13H9.5ZM18.5 13C19.3284 13 20 13.6716 20 14.5V18.5C20 19.3284 19.3284 20 18.5 20H14.5C13.6716 20 13 19.3284 13 18.5V14.5C13 13.6716 13.6716 13 14.5 13H18.5ZM18.5 4C19.3284 4 20 4.67157 20 5.5V9.5C20 10.3284 19.3284 11 18.5 11H14.5C13.6716 11 13 10.3284 13 9.5V5.5C13 4.67157 13.6716 4 14.5 4H18.5Z" fill="#949291"/>
                    </svg>
                    CMS
                </a>
            </li>
            <li>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" id="logout-btn" class="w-full text-sm sidebar-link logout-btn-red" onclick="document.getElementById('logout-form').submit();">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22C6.477 22 2 17.523 2 12C2 6.477 6.477 2 12 2C13.5527 1.99884 15.0842 2.35978 16.4729 3.05414C17.8617 3.74851 19.0693 4.75718 20 6H17.29C16.1352 4.98176 14.7112 4.31836 13.1887 4.0894C11.6663 3.86044 10.1101 4.07566 8.70689 4.70922C7.30371 5.34277 6.11315 6.36776 5.27807 7.66119C4.44299 8.95462 3.99887 10.4615 3.999 12.0011C3.99913 13.5407 4.4435 15.0475 5.27879 16.3408C6.11409 17.6341 7.30482 18.6589 8.7081 19.2922C10.1114 19.9255 11.6676 20.1405 13.19 19.9113C14.7125 19.6821 16.1364 19.0184 17.291 18H20.001C19.0702 19.243 17.8624 20.2517 16.4735 20.9461C15.0846 21.6405 13.5528 22.0013 12 22ZM19 16V13H11V11H19V8L24 12L19 16Z" fill="currentColor"/>
                    </svg>
                    Logout
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
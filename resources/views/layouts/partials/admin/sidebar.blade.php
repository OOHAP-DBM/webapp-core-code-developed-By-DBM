<aside class="admin-sidebar fixed left-0 top-0 h-screen flex flex-col shadow-lg transition-all duration-300">
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
                <p class="text-sm font-medium text-gray-900 text-left">
                    {{ Auth::user()->name }}  <Span>✏</Span>
                </p>
                <p class="text-xs  italic">
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
                    <rect x="4" y="4" width="7" height="7" rx="1.5" fill="#949291"/>
                    <path opacity="0.3" fill-rule="evenodd" clip-rule="evenodd" d="M13 5.5C13 4.67157 13.6716 4 14.5 4H18.5C19.3284 4 20 4.67157 20 5.5V9.5C20 10.3284 19.3284 11 18.5 11H14.5C13.6716 11 13 10.3284 13 9.5V5.5ZM4 14.5C4 13.6716 4.67157 13 5.5 13H9.5C10.3284 13 11 13.6716 11 14.5V18.5C11 19.3284 10.3284 20 9.5 20H5.5C4.67157 20 4 19.3284 4 18.5V14.5ZM14.5 13C13.6716 13 13 13.6716 13 14.5V18.5C13 19.3284 13.6716 20 14.5 20H18.5C19.3284 20 20 19.3284 20 18.5V14.5C20 13.6716 19.3284 13 18.5 13H14.5Z" fill="#949291"/>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li x-data="{ open: false }">

                <!-- Parent -->
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                        text-gray-700 hover:bg-gray-50"
                >
                    <div class="flex items-center gap-4">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_500_25079)">
                            <path d="M7 11H9V13H7V11ZM7 15H9V17H7V15ZM11 11H13V13H11V11ZM11 15H13V17H11V15ZM15 11H17V13H15V11ZM15 15H17V17H15V15Z" fill="#949291"/>
                            <path d="M4.33333 24H20.6667C21.9535 24 23 22.9236 23 21.6V4.8C23 3.4764 21.9535 2.4 20.6667 2.4H18.3333V0H16V2.4H9V0H6.66667V2.4H4.33333C3.0465 2.4 2 3.4764 2 4.8V21.6C2 22.9236 3.0465 24 4.33333 24ZM20.6667 7.2L20.6678 21.6H4.33333V7.2H20.6667Z" fill="#949291"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_500_25079">
                            <rect width="24" height="24" fill="white"/>
                            </clipPath>
                            </defs>
                            </svg>
                        <span>My Orders</span>
                    </div>

                    <svg class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Submenu -->
                <div x-show="open" x-cloak class="mt-1 space-y-1">
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- New Orders</span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- In Progress Orders</span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Running Campaign</span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Completed  </span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Cancelled  </span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Remounting Orders  </span>
                    </a>
                </div>
            </li>
            <li x-data="{ open: false }">

                <!-- Parent -->
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                        text-gray-700 hover:bg-gray-50"
                >
                    <div class="flex items-center gap-4">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g style="mix-blend-mode:multiply">
                            <path d="M23.7662 18.4194C24.0774 18.2365 24.0774 17.9397 23.7662 17.7569L11.6736 10.6493C11.4989 10.5591 11.3057 10.5121 11.1098 10.5121C10.9138 10.5121 10.7207 10.5591 10.546 10.6493L5.16309 13.8122L5.69174 14.1236L8.46912 12.4964L20.6346 19.6437L18.0877 21.1384L18.6515 21.4272L23.7662 18.4194Z" fill="url(#paint0_linear_500_6604)"/>
                            </g>
                            <g style="mix-blend-mode:multiply">
                            <path d="M3.30859 14.4359L5.95316 12.8829L17.5887 19.7189L14.9441 21.2731L3.30859 14.4359Z" fill="#D0D0D0"/>
                            </g>
                            <path d="M5.6849 0.753037V14.1161L5.15625 13.8048V0.44165L5.6849 0.753037Z" fill="#D0D0D0"/>
                            <path d="M5.68359 0.755843V14.1189L5.94922 13.9626V0.599487L5.68359 0.755843Z" fill="#B2B2B2"/>
                            <path d="M2.11914 13.1189V12.8088L13.7547 19.6448V19.9561L2.11914 13.1189Z" fill="#B2B2B2"/>
                            <path d="M2.11914 12.8089L4.7637 11.2546L16.3993 18.0919L13.7547 19.6448L2.11914 12.8089Z" fill="#D0D0D0"/>
                            <path d="M16.4011 18.3998L16.3985 18.0884L13.7539 19.6413V19.9527L16.4011 18.3998Z" fill="#8DB3CD"/>
                            <path d="M4.75879 14.3631L7.40335 12.8088L7.66768 12.9639L5.02312 14.5181L4.75879 14.3631Z" fill="#B2B2B2"/>
                            <path d="M7.66992 16.0665L10.3145 14.5135L10.5788 14.6686L7.93424 16.2229L7.66992 16.0665Z" fill="#B2B2B2"/>
                            <path d="M10.8428 17.9316L13.4873 16.3787L13.7517 16.5337L11.1071 18.088L10.8428 17.9316Z" fill="#B2B2B2"/>
                            <path d="M18.5175 7.51839L5.82852 0.0636767C5.52383 -0.123155 4.68658 0.106078 3.69829 0.686449L2.25817 1.53315C1.01206 2.26458 0.00292969 3.27559 0.00292969 3.78573V4.10242L0.263349 3.94739L12.6932 11.2497L18.5175 7.51839Z" fill="#949291"/>
                            <path d="M18.3802 8.21936V21.5825L17.8516 21.2724V7.9093L18.3802 8.21936Z" fill="#B2B2B2"/>
                            <g style="mix-blend-mode:multiply">
                            <path d="M17.8838 10.2543L18.277 10.0211V10.3325L17.8838 10.5657V10.2543Z" fill="#BFD2D8"/>
                            </g>
                            <path d="M18.277 10.0182L17.9827 9.84595L17.8838 9.90557V10.2514L18.277 10.0182Z" fill="#B2B2B2"/>
                            <g style="mix-blend-mode:multiply">
                            <path d="M17.8838 19.0789L18.277 18.8457V19.1571L17.8838 19.3903V19.0789Z" fill="#BFD2D8"/>
                            </g>
                            <path d="M18.277 18.8478L17.9827 18.6755L17.8838 18.7352V19.081L18.277 18.8478Z" fill="#718090"/>
                            <path d="M18.3799 8.21484V21.5779L18.6455 21.4216V8.05981L18.3799 8.21484Z" fill="#B2B2B2"/>
                            <path d="M18.6458 8.06529L18.1172 7.75391L17.8516 7.91026L18.3802 8.22032L18.6458 8.06529Z" fill="#718090"/>
                            <path d="M12.6937 23.0558L12.165 22.7457V12.1798L12.6937 12.4899V23.0558Z" fill="#D0D0D0"/>
                            <path d="M12.165 12.1849L17.4542 9.07629L17.9828 9.38768L12.6937 12.4949L12.165 12.1849Z" fill="#D0D0D0"/>
                            <path d="M17.9844 9.38953V19.9555L12.6953 23.0627V12.4968L17.9844 9.38953Z" fill="#5D5D5D"/>
                            <path d="M12.959 22.5937L17.7195 19.7965V9.85339L12.959 12.6506V22.5937Z" fill="white"/>
                            <path d="M17.7165 19.7965L17.4521 19.6415V10.0084L17.7165 9.85339V19.7965Z" fill="#5D5D5D"/>
                            <path d="M17.7195 19.7952L17.4551 19.6401L12.959 22.2823V22.5923L17.7195 19.7952Z" fill="#5D5D5D"/>
                            <path d="M18.6459 8.06603V7.75464C18.6459 7.24052 17.6368 7.41676 16.3907 8.15216L14.9505 8.99886C13.7044 9.73029 12.6953 10.7413 12.6953 11.2514V11.5628L18.6459 8.06603Z" fill="#D0D0D0"/>
                            <path d="M12.6942 11.5655V11.2541L0 3.79675V4.10681L12.6942 11.5655Z" fill="#D0D0D0"/>
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
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- My Hoardings</span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Vendor's Hoardings</span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Hoardings in draft</span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Category</span>
                    </a>
                </div>
            </li>
            <li x-data="{ open: false }">

                <!-- Parent -->
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                        text-gray-700 hover:bg-gray-50"
                >
                    <div class="flex items-center gap-4">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14.011 22.8605C13.3125 22.8464 12.811 22.7105 12.4781 22.4667L7.91721 20.0245L5.38127 16.575C4.84689 15.75 5.22658 14.1563 6.89533 15.1781L8.72346 17.5875C11.0438 19.5886 15.7781 18.6703 13.5328 14.6531C12.4313 12.3281 12.9469 11.1938 14.3672 10.7156L15.0141 12.8906C16.1344 15.4125 18.225 15.8672 18.1406 17.9016L23.1656 17.5406L23.1188 22.7948L14.011 22.8605ZM11.475 18.0141C10.6594 18.0281 9.85783 17.6906 9.27658 17.2031L7.44377 14.7938C7.94533 14.3953 8.45158 14.6109 8.95314 15.0938C9.54377 14.7938 10.0031 15.2578 10.3781 16.1859C10.5328 16.8188 10.7625 17.2641 11.475 18.0141ZM7.74377 13.5141C7.72502 13.5141 7.70158 13.5141 7.68283 13.5094C7.52814 13.4766 7.34533 13.3125 7.22346 12.9375C7.09689 12.5625 7.06877 12.0281 7.18127 11.4563C7.29377 10.8891 7.52814 10.4063 7.79064 10.1063C8.04377 9.81094 8.27814 9.72657 8.43283 9.75938C8.59221 9.78751 8.77033 9.95625 8.89221 10.3266C9.01877 10.7016 9.05158 11.2406 8.93908 11.8078C8.82189 12.3797 8.58752 12.8625 8.32971 13.1578C8.10471 13.4203 7.89846 13.5141 7.74377 13.5141ZM11.6156 8.50782C11.2453 8.50313 10.7813 8.38594 10.3266 8.16563C9.80627 7.90782 9.39846 7.55157 9.17814 7.22344C8.95783 6.90001 8.93908 6.65626 9.00939 6.51094C9.07971 6.37032 9.28596 6.23438 9.67971 6.21563C10.0735 6.19219 10.6031 6.3 11.1235 6.55782C11.6438 6.81563 12.0516 7.16719 12.2719 7.49532C12.4922 7.81876 12.511 8.06719 12.4406 8.20782C12.3703 8.35313 12.1641 8.48438 11.7703 8.50313C11.7188 8.50782 11.6719 8.50782 11.6156 8.50782ZM7.04064 4.02657C6.82033 4.02188 6.61877 3.98907 6.44064 3.93751C6.04221 3.82032 5.81252 3.60938 5.73752 3.36094C5.65783 3.10782 5.73752 2.80782 6.00471 2.48438C6.27658 2.16563 6.73127 1.86094 7.29377 1.69219C7.85158 1.52345 8.39533 1.52813 8.79846 1.65001C9.20158 1.76719 9.43127 1.97813 9.50627 2.22657C9.58127 2.47969 9.50627 2.77969 9.23439 3.10313C8.96721 3.42188 8.51252 3.72657 7.95002 3.89532C7.63127 3.98907 7.32189 4.03126 7.04064 4.02657Z" fill="#949291"/>
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
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Received Payments</span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Withdrawal Request</span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Approved Withdrawals</span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Rejected Withdrawals </span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Failed </span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Completed </span>
                    </a>
                </div>
            </li>
            <li>
                <a href="#" class="sidebar-link gap-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.275 20.25L20.75 16.8L19.7 15.75L17.275 18.125L16.3 17.15L15.25 18.225L17.275 20.25ZM6 9H18V7H6V9ZM18 23C16.6167 23 15.4377 22.5123 14.463 21.537C13.4883 20.5617 13.0007 19.3827 13 18C13 16.6167 13.4877 15.4377 14.463 14.463C15.4383 13.4883 16.6173 13.0007 18 13C19.3833 13 20.5627 13.4877 21.538 14.463C22.5133 15.4383 23.0007 16.6173 23 18C23 19.3833 22.5123 20.5627 21.537 21.538C20.5617 22.5133 19.3827 23.0007 18 23ZM3 22V5C3 4.45 3.196 3.97933 3.588 3.588C3.98 3.19667 4.45067 3.00067 5 3H19C19.55 3 20.021 3.196 20.413 3.588C20.805 3.98 21.0007 4.45067 21 5V11.675C20.6833 11.525 20.3583 11.4 20.025 11.3C19.6917 11.2 19.35 11.125 19 11.075V5H5V19.05H11.075C11.1583 19.5667 11.2877 20.0583 11.463 20.525C11.6383 20.9917 11.8673 21.4333 12.15 21.85L12 22L10.5 20.5L9 22L7.5 20.5L6 22L4.5 20.5L3 22ZM6 17H11.075C11.125 16.65 11.2 16.3083 11.3 15.975C11.4 15.6417 11.525 15.3167 11.675 15H6V17ZM6 13H13.1C13.7333 12.3833 14.471 11.8957 15.313 11.537C16.155 11.1783 17.0507 10.9993 18 11H6V13Z" fill="#949291"/>
                    </svg>
                    <span class="text-sm">My Hoarding Enquiry</span>
                </a>
            </li>
             <li x-data="{ open: false }">

                <!-- Parent -->
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg
                        text-gray-700 hover:bg-gray-50"
                >
                    <div class="flex items-center gap-4">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_500_24343)">
                            <path d="M7 11H9V13H7V11ZM7 15H9V17H7V15ZM11 11H13V13H11V11ZM11 15H13V17H11V15ZM15 11H17V13H15V11ZM15 15H17V17H15V15Z" fill="#949291"/>
                            <path d="M4.33333 24H20.6667C21.9535 24 23 22.9236 23 21.6V4.8C23 3.4764 21.9535 2.4 20.6667 2.4H18.3333V0H16V2.4H9V0H6.66667V2.4H4.33333C3.0465 2.4 2 3.4764 2 4.8V21.6C2 22.9236 3.0465 24 4.33333 24ZM20.6667 7.2L20.6678 21.6H4.33333V7.2H20.6667Z" fill="#949291"/>
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
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Dummy</span>
                    </a>
                    <a class="block py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md" href="">
                        <span style="margin-left:25px">- Dummy </span>
                    </a>
                </div>
            </li>
            <li>
                <a href="#" class="sidebar-link">
                    <i class="fas fa-undo-alt mr-3"></i> Refunds
                </a>
            </li>
        </ul>
        <div class="mt-6 mb-2 text-xs text-gray-400 font-semibold tracking-wider px-2">POS & BOOKINGS</div>
        <ul class="space-y-1">
            <li><a href="#" class="sidebar-link"><i class="fas fa-desktop mr-3"></i> POS Dashboard</a></li>
            <li><a href="#" class="sidebar-link"><i class="fas fa-calendar-plus mr-3"></i> Book Now</a></li>
            <li><a href="#" class="sidebar-link"><i class="fas fa-ticket-alt mr-3"></i> POS Booking</a></li>
            <li><a href="#" class="sidebar-link"><i class="fas fa-user-plus mr-3"></i> POS Customer</a></li>
            <li><a href="#" class="sidebar-link"><i class="fas fa-receipt mr-3"></i> POS Transactions</a></li>
            <li><a href="#" class="sidebar-link"><i class="fas fa-file-invoice-dollar mr-3"></i> Invoice Manager</a></li>
        </ul>
        <div class="mt-6 mb-2 text-xs text-gray-400 font-semibold tracking-wider px-2">PEOPLES</div>
        <ul class="space-y-1">
            <li>
                <div class="sidebar-submenu-title">Vendor Management</div>
                <ul class="ml-4 space-y-1">
                    <li><a href="{{ route('admin.vendors.requested') }}" class="sidebar-link flex items-center {{ request()->routeIs('admin.vendors.requested') ? 'active' : '' }}">Requested Vendors @if(isset($requestedVendorCount) && $requestedVendorCount > 0)<span class="ml-2 bg-green-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full">{{ $requestedVendorCount }}</span>@endif</a></li>
                    <li><a href="#" class="sidebar-link">Active Vendors</a></li>
                    <li><a href="#" class="sidebar-link">Deleted Vendors</a></li>
                </ul>
            </li>
            <li>
                <div class="sidebar-submenu-title">Customer Management</div>
                <ul class="ml-4 space-y-1">
                    <li><a href="{{ route('admin.customers.index') }}" class="sidebar-link flex items-center {{ request()->routeIs('admin.customers.index') ? 'active' : '' }}">Total Customers @if(isset($totalCustomerCount) && $totalCustomerCount > 0)<span class="ml-2 bg-blue-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full">{{ $totalCustomerCount }}</span>@endif</a></li>
                    <li><a href="#" class="sidebar-link">Joined this Week</a></li>
                    <li><a href="#" class="sidebar-link">Joined this Month</a></li>
                    <li><a href="#" class="sidebar-link">Account Deletion Request</a></li>
                    <li><a href="#" class="sidebar-link">Deleted Customer</a></li>
                </ul>
            </li>
            <li>
                <div class="sidebar-submenu-title">My Staff</div>
                <ul class="ml-4 space-y-1">
                    <li><a href="#" class="sidebar-link">Graphics Designer</a></li>
                    <li><a href="#" class="sidebar-link">Printer</a></li>
                    <li><a href="#" class="sidebar-link">Mounter</a></li>
                    <li><a href="#" class="sidebar-link">Supplier</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</aside>
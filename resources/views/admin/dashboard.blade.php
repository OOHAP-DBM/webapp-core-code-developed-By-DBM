@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')

{{-- ================= HEADER ================= --}}
<!-- <div class="flex justify-between items-center mb-6">
    <h2 class="text-lg text-gray-700">
        Good Morning,
        <span class="text-blue-600 font-semibold">
            {{ auth()->user()->name ?? 'Admin' }}
        </span> 👋
    </h2>
</div> -->
<div class=" flex justify-between items-center  mb-6 px-2"> 
    <h2 class="font-['Poppins'] font-medium text-[18px] leading-[27px] text-[#464E5F]">
        {{ \App\Helpers\GreetingHelper::getGreeting() }}, 
        <span class="text-blue-600 font-semibold">
            {{ auth()->user()->name ?? 'Admin' }}!
        </span> 
    </h2>
    <!-- <a href="{{ route('vendor.hoardings.add') }}" class="bg-black text-white px-4 py-2 rounded-lg text-sm">+ Add Hoarding</a> -->
</div>

{{-- ================= STATS ================= --}}
<div class="bg-white rounded-xl shadow p-6 mb-6">
    <h3 class="text-lg font-semibold mb-4">Statistics</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        @php
            $statIcons = [
                'users' => '
                    <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.0049 13.2589C17.8608 12.063 18.3211 10.6291 18.3211 9.15846C18.3211 7.68777 17.8608 6.25395 17.0049 5.058C17.806 4.51121 18.7545 4.22124 19.7244 4.22664C21.0324 4.22664 22.2869 4.74624 23.2118 5.67113C24.1367 6.59603 24.6562 7.85046 24.6562 9.15846C24.6562 10.4665 24.1367 11.7209 23.2118 12.6458C22.2869 13.5707 21.0324 14.0903 19.7244 14.0903C18.7545 14.0957 17.806 13.8057 17.0049 13.2589ZM6.33807 9.15846C6.33807 8.18303 6.62731 7.22952 7.16923 6.41848C7.71114 5.60745 8.48139 4.97533 9.38256 4.60205C10.2837 4.22877 11.2754 4.13111 12.232 4.3214C13.1887 4.5117 14.0675 4.98141 14.7572 5.67113C15.4469 6.36086 15.9166 7.23963 16.1069 8.19631C16.2972 9.15298 16.1996 10.1446 15.8263 11.0458C15.453 11.947 14.8209 12.7172 14.0099 13.2591C13.1988 13.801 12.2453 14.0903 11.2699 14.0903C9.96189 14.0903 8.70746 13.5707 7.78256 12.6458C6.85767 11.7209 6.33807 10.4665 6.33807 9.15846ZM9.15625 9.15846C9.15625 9.57649 9.28021 9.98514 9.51246 10.3327C9.74471 10.6803 10.0748 10.9512 10.461 11.1112C10.8472 11.2712 11.2722 11.313 11.6822 11.2315C12.0922 11.1499 12.4689 10.9486 12.7645 10.653C13.0601 10.3574 13.2614 9.98081 13.3429 9.57081C13.4245 9.1608 13.3826 8.73582 13.2226 8.3496C13.0627 7.96339 12.7917 7.63328 12.4442 7.40103C12.0966 7.16878 11.6879 7.04482 11.2699 7.04482C10.7093 7.04482 10.1717 7.2675 9.77532 7.66389C9.37894 8.06027 9.15625 8.59788 9.15625 9.15846ZM21.1335 22.5448V25.363H1.40625V22.5448C1.40625 22.5448 1.40625 16.9085 11.2699 16.9085C21.1335 16.9085 21.1335 22.5448 21.1335 22.5448ZM18.3153 22.5448C18.1181 21.4457 16.4412 19.7266 11.2699 19.7266C6.09852 19.7266 4.32307 21.5725 4.22443 22.5448M21.0631 16.9085C21.9267 17.5802 22.6328 18.4329 23.1319 19.4066C23.6309 20.3803 23.9107 21.4514 23.9517 22.5448V25.363H29.5881V22.5448C29.5881 22.5448 29.5881 17.4298 21.049 16.9085H21.0631Z" fill="#2CB67D"/>
                    </svg>
                ',
                'vendors' => '
                    <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.0049 13.2589C17.8608 12.063 18.3211 10.6291 18.3211 9.15846C18.3211 7.68777 17.8608 6.25395 17.0049 5.058C17.806 4.51121 18.7545 4.22124 19.7244 4.22664C21.0324 4.22664 22.2869 4.74624 23.2118 5.67113C24.1367 6.59603 24.6562 7.85046 24.6562 9.15846C24.6562 10.4665 24.1367 11.7209 23.2118 12.6458C22.2869 13.5707 21.0324 14.0903 19.7244 14.0903C18.7545 14.0957 17.806 13.8057 17.0049 13.2589ZM6.33807 9.15846C6.33807 8.18303 6.62731 7.22952 7.16923 6.41848C7.71114 5.60745 8.48139 4.97533 9.38256 4.60205C10.2837 4.22877 11.2754 4.13111 12.232 4.3214C13.1887 4.5117 14.0675 4.98141 14.7572 5.67113C15.4469 6.36086 15.9166 7.23963 16.1069 8.19631C16.2972 9.15298 16.1996 10.1446 15.8263 11.0458C15.453 11.947 14.8209 12.7172 14.0099 13.2591C13.1988 13.801 12.2453 14.0903 11.2699 14.0903C9.96189 14.0903 8.70746 13.5707 7.78256 12.6458C6.85767 11.7209 6.33807 10.4665 6.33807 9.15846ZM9.15625 9.15846C9.15625 9.57649 9.28021 9.98514 9.51246 10.3327C9.74471 10.6803 10.0748 10.9512 10.461 11.1112C10.8472 11.2712 11.2722 11.313 11.6822 11.2315C12.0922 11.1499 12.4689 10.9486 12.7645 10.653C13.0601 10.3574 13.2614 9.98081 13.3429 9.57081C13.4245 9.1608 13.3826 8.73582 13.2226 8.3496C13.0627 7.96339 12.7917 7.63328 12.4442 7.40103C12.0966 7.16878 11.6879 7.04482 11.2699 7.04482C10.7093 7.04482 10.1717 7.2675 9.77532 7.66389C9.37894 8.06027 9.15625 8.59788 9.15625 9.15846ZM21.1335 22.5448V25.363H1.40625V22.5448C1.40625 22.5448 1.40625 16.9085 11.2699 16.9085C21.1335 16.9085 21.1335 22.5448 21.1335 22.5448ZM18.3153 22.5448C18.1181 21.4457 16.4412 19.7266 11.2699 19.7266C6.09852 19.7266 4.32307 21.5725 4.22443 22.5448M21.0631 16.9085C21.9267 17.5802 22.6328 18.4329 23.1319 19.4066C23.6309 20.3803 23.9107 21.4514 23.9517 22.5448V25.363H29.5881V22.5448C29.5881 22.5448 29.5881 17.4298 21.049 16.9085H21.0631Z" fill="#2CB67D"/>
                    </svg>
                ',
                'customers' => '
                    <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.0049 13.2589C17.8608 12.063 18.3211 10.6291 18.3211 9.15846C18.3211 7.68777 17.8608 6.25395 17.0049 5.058C17.806 4.51121 18.7545 4.22124 19.7244 4.22664C21.0324 4.22664 22.2869 4.74624 23.2118 5.67113C24.1367 6.59603 24.6562 7.85046 24.6562 9.15846C24.6562 10.4665 24.1367 11.7209 23.2118 12.6458C22.2869 13.5707 21.0324 14.0903 19.7244 14.0903C18.7545 14.0957 17.806 13.8057 17.0049 13.2589ZM6.33807 9.15846C6.33807 8.18303 6.62731 7.22952 7.16923 6.41848C7.71114 5.60745 8.48139 4.97533 9.38256 4.60205C10.2837 4.22877 11.2754 4.13111 12.232 4.3214C13.1887 4.5117 14.0675 4.98141 14.7572 5.67113C15.4469 6.36086 15.9166 7.23963 16.1069 8.19631C16.2972 9.15298 16.1996 10.1446 15.8263 11.0458C15.453 11.947 14.8209 12.7172 14.0099 13.2591C13.1988 13.801 12.2453 14.0903 11.2699 14.0903C9.96189 14.0903 8.70746 13.5707 7.78256 12.6458C6.85767 11.7209 6.33807 10.4665 6.33807 9.15846ZM9.15625 9.15846C9.15625 9.57649 9.28021 9.98514 9.51246 10.3327C9.74471 10.6803 10.0748 10.9512 10.461 11.1112C10.8472 11.2712 11.2722 11.313 11.6822 11.2315C12.0922 11.1499 12.4689 10.9486 12.7645 10.653C13.0601 10.3574 13.2614 9.98081 13.3429 9.57081C13.4245 9.1608 13.3826 8.73582 13.2226 8.3496C13.0627 7.96339 12.7917 7.63328 12.4442 7.40103C12.0966 7.16878 11.6879 7.04482 11.2699 7.04482C10.7093 7.04482 10.1717 7.2675 9.77532 7.66389C9.37894 8.06027 9.15625 8.59788 9.15625 9.15846ZM21.1335 22.5448V25.363H1.40625V22.5448C1.40625 22.5448 1.40625 16.9085 11.2699 16.9085C21.1335 16.9085 21.1335 22.5448 21.1335 22.5448ZM18.3153 22.5448C18.1181 21.4457 16.4412 19.7266 11.2699 19.7266C6.09852 19.7266 4.32307 21.5725 4.22443 22.5448M21.0631 16.9085C21.9267 17.5802 22.6328 18.4329 23.1319 19.4066C23.6309 20.3803 23.9107 21.4514 23.9517 22.5448V25.363H29.5881V22.5448C29.5881 22.5448 29.5881 17.4298 21.049 16.9085H21.0631Z" fill="#2CB67D"/>
                    </svg>
                ',
                'bookings' => '
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_265_28132)">
                    <path d="M6.41406 10.082H8.2474V11.9154H6.41406V10.082ZM6.41406 13.7487H8.2474V15.582H6.41406V13.7487ZM10.0807 10.082H11.9141V11.9154H10.0807V10.082ZM10.0807 13.7487H11.9141V15.582H10.0807V13.7487ZM13.7474 10.082H15.5807V11.9154H13.7474V10.082ZM13.7474 13.7487H15.5807V15.582H13.7474V13.7487Z" fill="#0089E1"/>
                    <path d="M3.33333 22H19.6667C20.9535 22 22 21.0133 22 19.8V4.4C22 3.1867 20.9535 2.2 19.6667 2.2H17.3333V0H15V2.2H8V0H5.66667V2.2H3.33333C2.0465 2.2 1 3.1867 1 4.4V19.8C1 21.0133 2.0465 22 3.33333 22ZM19.6667 6.6L19.6678 19.8H3.33333V6.6H19.6667Z" fill="#0089E1"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_265_28132">
                    <rect width="22" height="22" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg>
                ',
                'hoardings' => '
                    <svg width="33" height="30" viewBox="0 0 33 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g style="mix-blend-mode:multiply">
                    <path d="M32.397 23.0245C32.8212 22.796 32.8212 22.425 32.397 22.1964L15.9135 13.3121C15.6754 13.1994 15.4121 13.1406 15.145 13.1406C14.8779 13.1406 14.6146 13.1994 14.3765 13.3121L7.03906 17.2657L7.75967 17.6549L11.5455 15.621L28.1283 24.5549L24.6567 26.4232L25.4252 26.7843L32.397 23.0245Z" fill="url(#paint0_linear_265_27962)"/>
                    </g>
                    <g style="mix-blend-mode:multiply">
                    <path d="M4.50781 18.0427L8.11262 16.1016L23.9731 24.6463L20.3683 26.5891L4.50781 18.0427Z" fill="#D0D0D0"/>
                    </g>
                    <path d="M7.74404 0.940005V17.6435L7.02344 17.2543V0.550781L7.74404 0.940005Z" fill="#D0D0D0"/>
                    <path d="M7.75 0.94544V17.6489L8.11207 17.4535V0.75L7.75 0.94544Z" fill="#B2B2B2"/>
                    <path d="M2.89062 16.3954V16.0078L18.7511 24.5525V24.9417L2.89062 16.3954Z" fill="#B2B2B2"/>
                    <path d="M2.89062 16.0092L6.49543 14.0664L22.3559 22.6128L18.7511 24.5539L2.89062 16.0092Z" fill="#D0D0D0"/>
                    <path d="M22.3505 22.9986L22.347 22.6094L18.7422 24.5505V24.9397L22.3505 22.9986Z" fill="#8DB3CD"/>
                    <path d="M6.48438 17.9506L10.0892 16.0078L10.4495 16.2016L6.84468 18.1444L6.48438 17.9506Z" fill="#B2B2B2"/>
                    <path d="M10.4531 20.0818L14.0579 18.1406L14.4182 18.3344L10.8134 20.2772L10.4531 20.0818Z" fill="#B2B2B2"/>
                    <path d="M14.7812 22.4138L18.3861 20.4727L18.7464 20.6664L15.1415 22.6092L14.7812 22.4138Z" fill="#B2B2B2"/>
                    <path d="M25.2372 9.39778L7.94087 0.079594C7.52554 -0.15394 6.38429 0.132595 5.03715 0.858042L3.07412 1.9164C1.37554 2.83066 0 4.0944 0 4.73206V5.12791L0.354979 4.93413L17.2981 14.0618L25.2372 9.39778Z" fill="#949291"/>
                    <path d="M25.0565 10.2743V26.9778L24.3359 26.5902V9.88672L25.0565 10.2743Z" fill="#B2B2B2"/>
                    <g style="mix-blend-mode:multiply">
                    <path d="M24.375 12.8188L24.911 12.5273V12.9166L24.375 13.2081V12.8188Z" fill="#BFD2D8"/>
                    </g>
                    <path d="M24.911 12.52L24.5099 12.3047L24.375 12.3792V12.8115L24.911 12.52Z" fill="#B2B2B2"/>
                    <g style="mix-blend-mode:multiply">
                    <path d="M24.375 23.8462L24.911 23.5547V23.9439L24.375 24.2354V23.8462Z" fill="#BFD2D8"/>
                    </g>
                    <path d="M24.911 23.5591L24.5099 23.3438L24.375 23.4183V23.8506L24.911 23.5591Z" fill="#718090"/>
                    <path d="M25.0625 10.2719V26.9754L25.4246 26.78V10.0781L25.0625 10.2719Z" fill="#B2B2B2"/>
                    <path d="M25.4186 10.0806L24.698 9.69141L24.3359 9.88685L25.0565 10.2744L25.4186 10.0806Z" fill="#718090"/>
                    <path d="M17.3065 28.8212L16.5859 28.4337V15.2266L17.3065 15.6141V28.8212Z" fill="#D0D0D0"/>
                    <path d="M16.5859 15.2294L23.7956 11.3438L24.5162 11.733L17.3065 15.6169L16.5859 15.2294Z" fill="#D0D0D0"/>
                    <path d="M24.5143 11.7344V24.9415L17.3047 28.8254V15.6183L24.5143 11.7344Z" fill="#5D5D5D"/>
                    <path d="M17.6641 28.2415L24.1531 24.7451V12.3164L17.6641 15.8128V28.2415Z" fill="#F4F4F4"/>
                    <path d="M24.1572 24.7451L23.7969 24.5513V12.5102L24.1572 12.3164V24.7451Z" fill="#5D5D5D"/>
                    <path d="M24.1531 24.7446L23.7928 24.5508L17.6641 27.8534V28.241L24.1531 24.7446Z" fill="#5D5D5D"/>
                    <path d="M25.4159 10.0852V9.69601C25.4159 9.05338 24.0404 9.27366 22.3418 10.1929L20.3788 11.2513C18.6802 12.1655 17.3047 13.4292 17.3047 14.0669V14.4561L25.4159 10.0852Z" fill="#D0D0D0"/>
                    <path d="M17.3034 14.4568V14.0676L0 4.74609V5.13366L17.3034 14.4568Z" fill="#D0D0D0"/>
                    <g style="mix-blend-mode:multiply">
                    <path d="M20.1875 28.8186L24.3319 26.5859L25.0525 26.9735L20.9081 29.2078L20.1875 28.8186Z" fill="url(#paint1_linear_265_27962)"/>
                    </g>
                    <defs>
                    <linearGradient id="paint0_linear_265_27962" x1="19.8771" y1="13.1406" x2="19.8771" y2="26.7843" gradientUnits="userSpaceOnUse">
                    <stop/>
                    <stop offset="1" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="paint1_linear_265_27962" x1="22.62" y1="26.5859" x2="22.62" y2="29.2078" gradientUnits="userSpaceOnUse">
                    <stop/>
                    <stop offset="1" stop-opacity="0"/>
                    </linearGradient>
                    </defs>
                    </svg>

                ',
                'ooh' => '
                    <svg width="33" height="30" viewBox="0 0 33 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g style="mix-blend-mode:multiply">
                    <path d="M32.397 23.0245C32.8212 22.796 32.8212 22.425 32.397 22.1964L15.9135 13.3121C15.6754 13.1994 15.4121 13.1406 15.145 13.1406C14.8779 13.1406 14.6146 13.1994 14.3765 13.3121L7.03906 17.2657L7.75967 17.6549L11.5455 15.621L28.1283 24.5549L24.6567 26.4232L25.4252 26.7843L32.397 23.0245Z" fill="url(#paint0_linear_265_27962)"/>
                    </g>
                    <g style="mix-blend-mode:multiply">
                    <path d="M4.50781 18.0427L8.11262 16.1016L23.9731 24.6463L20.3683 26.5891L4.50781 18.0427Z" fill="#D0D0D0"/>
                    </g>
                    <path d="M7.74404 0.940005V17.6435L7.02344 17.2543V0.550781L7.74404 0.940005Z" fill="#D0D0D0"/>
                    <path d="M7.75 0.94544V17.6489L8.11207 17.4535V0.75L7.75 0.94544Z" fill="#B2B2B2"/>
                    <path d="M2.89062 16.3954V16.0078L18.7511 24.5525V24.9417L2.89062 16.3954Z" fill="#B2B2B2"/>
                    <path d="M2.89062 16.0092L6.49543 14.0664L22.3559 22.6128L18.7511 24.5539L2.89062 16.0092Z" fill="#D0D0D0"/>
                    <path d="M22.3505 22.9986L22.347 22.6094L18.7422 24.5505V24.9397L22.3505 22.9986Z" fill="#8DB3CD"/>
                    <path d="M6.48438 17.9506L10.0892 16.0078L10.4495 16.2016L6.84468 18.1444L6.48438 17.9506Z" fill="#B2B2B2"/>
                    <path d="M10.4531 20.0818L14.0579 18.1406L14.4182 18.3344L10.8134 20.2772L10.4531 20.0818Z" fill="#B2B2B2"/>
                    <path d="M14.7812 22.4138L18.3861 20.4727L18.7464 20.6664L15.1415 22.6092L14.7812 22.4138Z" fill="#B2B2B2"/>
                    <path d="M25.2372 9.39778L7.94087 0.079594C7.52554 -0.15394 6.38429 0.132595 5.03715 0.858042L3.07412 1.9164C1.37554 2.83066 0 4.0944 0 4.73206V5.12791L0.354979 4.93413L17.2981 14.0618L25.2372 9.39778Z" fill="#949291"/>
                    <path d="M25.0565 10.2743V26.9778L24.3359 26.5902V9.88672L25.0565 10.2743Z" fill="#B2B2B2"/>
                    <g style="mix-blend-mode:multiply">
                    <path d="M24.375 12.8188L24.911 12.5273V12.9166L24.375 13.2081V12.8188Z" fill="#BFD2D8"/>
                    </g>
                    <path d="M24.911 12.52L24.5099 12.3047L24.375 12.3792V12.8115L24.911 12.52Z" fill="#B2B2B2"/>
                    <g style="mix-blend-mode:multiply">
                    <path d="M24.375 23.8462L24.911 23.5547V23.9439L24.375 24.2354V23.8462Z" fill="#BFD2D8"/>
                    </g>
                    <path d="M24.911 23.5591L24.5099 23.3438L24.375 23.4183V23.8506L24.911 23.5591Z" fill="#718090"/>
                    <path d="M25.0625 10.2719V26.9754L25.4246 26.78V10.0781L25.0625 10.2719Z" fill="#B2B2B2"/>
                    <path d="M25.4186 10.0806L24.698 9.69141L24.3359 9.88685L25.0565 10.2744L25.4186 10.0806Z" fill="#718090"/>
                    <path d="M17.3065 28.8212L16.5859 28.4337V15.2266L17.3065 15.6141V28.8212Z" fill="#D0D0D0"/>
                    <path d="M16.5859 15.2294L23.7956 11.3438L24.5162 11.733L17.3065 15.6169L16.5859 15.2294Z" fill="#D0D0D0"/>
                    <path d="M24.5143 11.7344V24.9415L17.3047 28.8254V15.6183L24.5143 11.7344Z" fill="#5D5D5D"/>
                    <path d="M17.6641 28.2415L24.1531 24.7451V12.3164L17.6641 15.8128V28.2415Z" fill="#F4F4F4"/>
                    <path d="M24.1572 24.7451L23.7969 24.5513V12.5102L24.1572 12.3164V24.7451Z" fill="#5D5D5D"/>
                    <path d="M24.1531 24.7446L23.7928 24.5508L17.6641 27.8534V28.241L24.1531 24.7446Z" fill="#5D5D5D"/>
                    <path d="M25.4159 10.0852V9.69601C25.4159 9.05338 24.0404 9.27366 22.3418 10.1929L20.3788 11.2513C18.6802 12.1655 17.3047 13.4292 17.3047 14.0669V14.4561L25.4159 10.0852Z" fill="#D0D0D0"/>
                    <path d="M17.3034 14.4568V14.0676L0 4.74609V5.13366L17.3034 14.4568Z" fill="#D0D0D0"/>
                    <g style="mix-blend-mode:multiply">
                    <path d="M20.1875 28.8186L24.3319 26.5859L25.0525 26.9735L20.9081 29.2078L20.1875 28.8186Z" fill="url(#paint1_linear_265_27962)"/>
                    </g>
                    <defs>
                    <linearGradient id="paint0_linear_265_27962" x1="19.8771" y1="13.1406" x2="19.8771" y2="26.7843" gradientUnits="userSpaceOnUse">
                    <stop/>
                    <stop offset="1" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="paint1_linear_265_27962" x1="22.62" y1="26.5859" x2="22.62" y2="29.2078" gradientUnits="userSpaceOnUse">
                    <stop/>
                    <stop offset="1" stop-opacity="0"/>
                    </linearGradient>
                    </defs>
                    </svg>

                ',
                'dooh' => '
                    <svg width="33" height="30" viewBox="0 0 33 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g style="mix-blend-mode:multiply">
                    <path d="M32.397 23.0245C32.8212 22.796 32.8212 22.425 32.397 22.1964L15.9135 13.3121C15.6754 13.1994 15.4121 13.1406 15.145 13.1406C14.8779 13.1406 14.6146 13.1994 14.3765 13.3121L7.03906 17.2657L7.75967 17.6549L11.5455 15.621L28.1283 24.5549L24.6567 26.4232L25.4252 26.7843L32.397 23.0245Z" fill="url(#paint0_linear_265_27962)"/>
                    </g>
                    <g style="mix-blend-mode:multiply">
                    <path d="M4.50781 18.0427L8.11262 16.1016L23.9731 24.6463L20.3683 26.5891L4.50781 18.0427Z" fill="#D0D0D0"/>
                    </g>
                    <path d="M7.74404 0.940005V17.6435L7.02344 17.2543V0.550781L7.74404 0.940005Z" fill="#D0D0D0"/>
                    <path d="M7.75 0.94544V17.6489L8.11207 17.4535V0.75L7.75 0.94544Z" fill="#B2B2B2"/>
                    <path d="M2.89062 16.3954V16.0078L18.7511 24.5525V24.9417L2.89062 16.3954Z" fill="#B2B2B2"/>
                    <path d="M2.89062 16.0092L6.49543 14.0664L22.3559 22.6128L18.7511 24.5539L2.89062 16.0092Z" fill="#D0D0D0"/>
                    <path d="M22.3505 22.9986L22.347 22.6094L18.7422 24.5505V24.9397L22.3505 22.9986Z" fill="#8DB3CD"/>
                    <path d="M6.48438 17.9506L10.0892 16.0078L10.4495 16.2016L6.84468 18.1444L6.48438 17.9506Z" fill="#B2B2B2"/>
                    <path d="M10.4531 20.0818L14.0579 18.1406L14.4182 18.3344L10.8134 20.2772L10.4531 20.0818Z" fill="#B2B2B2"/>
                    <path d="M14.7812 22.4138L18.3861 20.4727L18.7464 20.6664L15.1415 22.6092L14.7812 22.4138Z" fill="#B2B2B2"/>
                    <path d="M25.2372 9.39778L7.94087 0.079594C7.52554 -0.15394 6.38429 0.132595 5.03715 0.858042L3.07412 1.9164C1.37554 2.83066 0 4.0944 0 4.73206V5.12791L0.354979 4.93413L17.2981 14.0618L25.2372 9.39778Z" fill="#949291"/>
                    <path d="M25.0565 10.2743V26.9778L24.3359 26.5902V9.88672L25.0565 10.2743Z" fill="#B2B2B2"/>
                    <g style="mix-blend-mode:multiply">
                    <path d="M24.375 12.8188L24.911 12.5273V12.9166L24.375 13.2081V12.8188Z" fill="#BFD2D8"/>
                    </g>
                    <path d="M24.911 12.52L24.5099 12.3047L24.375 12.3792V12.8115L24.911 12.52Z" fill="#B2B2B2"/>
                    <g style="mix-blend-mode:multiply">
                    <path d="M24.375 23.8462L24.911 23.5547V23.9439L24.375 24.2354V23.8462Z" fill="#BFD2D8"/>
                    </g>
                    <path d="M24.911 23.5591L24.5099 23.3438L24.375 23.4183V23.8506L24.911 23.5591Z" fill="#718090"/>
                    <path d="M25.0625 10.2719V26.9754L25.4246 26.78V10.0781L25.0625 10.2719Z" fill="#B2B2B2"/>
                    <path d="M25.4186 10.0806L24.698 9.69141L24.3359 9.88685L25.0565 10.2744L25.4186 10.0806Z" fill="#718090"/>
                    <path d="M17.3065 28.8212L16.5859 28.4337V15.2266L17.3065 15.6141V28.8212Z" fill="#D0D0D0"/>
                    <path d="M16.5859 15.2294L23.7956 11.3438L24.5162 11.733L17.3065 15.6169L16.5859 15.2294Z" fill="#D0D0D0"/>
                    <path d="M24.5143 11.7344V24.9415L17.3047 28.8254V15.6183L24.5143 11.7344Z" fill="#5D5D5D"/>
                    <path d="M17.6641 28.2415L24.1531 24.7451V12.3164L17.6641 15.8128V28.2415Z" fill="#F4F4F4"/>
                    <path d="M24.1572 24.7451L23.7969 24.5513V12.5102L24.1572 12.3164V24.7451Z" fill="#5D5D5D"/>
                    <path d="M24.1531 24.7446L23.7928 24.5508L17.6641 27.8534V28.241L24.1531 24.7446Z" fill="#5D5D5D"/>
                    <path d="M25.4159 10.0852V9.69601C25.4159 9.05338 24.0404 9.27366 22.3418 10.1929L20.3788 11.2513C18.6802 12.1655 17.3047 13.4292 17.3047 14.0669V14.4561L25.4159 10.0852Z" fill="#D0D0D0"/>
                    <path d="M17.3034 14.4568V14.0676L0 4.74609V5.13366L17.3034 14.4568Z" fill="#D0D0D0"/>
                    <g style="mix-blend-mode:multiply">
                    <path d="M20.1875 28.8186L24.3319 26.5859L25.0525 26.9735L20.9081 29.2078L20.1875 28.8186Z" fill="url(#paint1_linear_265_27962)"/>
                    </g>
                    <defs>
                    <linearGradient id="paint0_linear_265_27962" x1="19.8771" y1="13.1406" x2="19.8771" y2="26.7843" gradientUnits="userSpaceOnUse">
                    <stop/>
                    <stop offset="1" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="paint1_linear_265_27962" x1="22.62" y1="26.5859" x2="22.62" y2="29.2078" gradientUnits="userSpaceOnUse">
                    <stop/>
                    <stop offset="1" stop-opacity="0"/>
                    </linearGradient>
                    </defs>
                    </svg>

                ',
            ];
        @endphp
        @php
            $stats = [
                ['Total Users', $userCount, 'bg-blue-600', 'users'],
                ['Vendors', $vendorCount, 'bg-indigo-600', 'vendors'],
                ['Customers', $customerCount, 'bg-purple-600', 'customers'],
                ['Bookings', $bookingCount, 'bg-teal-600', 'bookings'],
                ['Total Hoardings', $hoardingCount, 'bg-green-600', 'hoardings'],
                ['OOH Hoardings', $oohCount, 'bg-gray-600', 'ooh'],
                ['DOOH Hoardings', $doohCount, 'bg-pink-600', 'dooh'],
            ];
        @endphp


        @foreach($stats as [$label, $value, $color,$key])
        <div class="relative">
            <!-- CARD -->
            <div class="relative z-10 bg-gray-100 shadow rounded-xl p-4">
                <div class="mb-2">
                    {!! $statIcons[$key] ?? '' !!}
                </div>

                <p class="text-2xl font-semibold mt-1">
                    {{ number_format($value) }}
                </p>

                <p class="text-black ">
                    {{ $label }}
                </p>
            </div>

            <!-- BOTTOM COLOR STRIP -->
            <div
                class="relative z-0 h-2 mx-4  rounded-b-full {{ $color }}">
            </div>
        </div>

        @endforeach
    </div>
</div>

{{-- ================= CHARTS ================= --}}
<div class="bg-white rounded-xl p-6 mb-6 grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div>
        <h4 class="text-sm font-semibold mb-2">Monthly User Growth</h4>
        <canvas id="userChart" height="120"></canvas>
    </div>

    <div>
        <h4 class="text-sm font-semibold mb-2">Monthly Bookings</h4>
        <canvas id="bookingChart" height="120"></canvas>
    </div>
</div>

{{-- ================= TOP 5 BEST SELLING HOARDINGS ================= --}}
<!-- <div class="bg-white rounded-xl shadow-sm mb-6">
    <div class="px-6 pt-5 pb-1 flex justify-between items-center">
        <div>
            <h4 class="text-sm font-semibold mb-4 text-gray-800">
                Top 5 Best Selling Hoardings
            </h4>
        </div>
        <div class="text-xs text-gray-500 flex items-center gap-1">
            SORT BY:
            <select name="" id="">
                <option value="">1 Month</option>
                <option value="">6 Month</option>
                <option value="">1 Year</option>
            </select>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">SN</th>
                    <th class="px-6 py-3 text-left">Hoarding Title</th>
                    <th class="px-6 py-3 text-left">Type</th>
                    <th class="px-6 py-3 text-left">Categories</th>
                    <th class="px-6 py-3 text-left">Hoarding Location</th>
                    <th class="px-6 py-3 text-left">Size</th>
                    <th class="px-6 py-3 text-left"># Of Bookings</th>
                    <th class="px-6 py-3 text-left">Published By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topHoardings as $i => $h)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">{{ sprintf('%02d', $i+1) }}</td>
                    <td class="px-6 py-4 font-medium text-gray-800 truncate max-w-[180px]">
                        {{ $h['title'] }}
                    </td>
                    <td class="px-6 py-4">{{ $h['type'] }}</td>
                    <td class="px-6 py-4">{{ $h['cat'] }}</td>
                    <td class="px-6 py-4 truncate max-w-[160px]">{{ $h['loc'] }}</td>
                    <td class="px-6 py-4">{{ $h['size'] }}</td>
                    <td class="px-6 py-4 text-green-600 font-semibold">{{ $h['bookings'] }}</td>
                    <td class="px-6 py-4">{{ $h['published_by'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-6 text-center text-gray-500">No hoardings available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div> -->

{{-- ================= RECENTLY BOOKED HOARDING - POS ================= --}}
<div class="bg-white rounded-xl shadow-sm mb-6">
    <div class="px-6 pt-5 pb-1 flex justify-between items-center">
        <div>
            <h4 class="text-sm font-semibold text-gray-800 mb-4">
                Recently Booked Hoarding - POS
            </h4>
        </div>
        <div class="text-xs text-gray-500 flex items-center gap-1">
            SORT BY:
            <select name="" id="">
                <option value="">1 Month</option>
                <option value="">6 Month</option>
                <option value="">1 Year</option>
            </select>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">SN</th>
                    <th class="px-6 py-3 text-left">Customer</th>
                    <th class="px-6 py-3 text-left"># Of Bookings</th>
                    <th class="px-6 py-3 text-left">Grand Total</th>
                    <th class="px-6 py-3 text-left">Amount Received</th>
                    <th class="px-6 py-3 text-left">Due Amount</th>
                    <th class="px-6 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBookings as $i => $b)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">{{ sprintf('%02d', $i+1) }}</td>
                    <td class="px-6 py-4">{{ $b['customer'] }}</td>
                    <td class="px-6 py-4 text-green-600 font-semibold">{{ $b['bookings'] }}</td>
                    <td class="px-6 py-4 text-blue-600 font-semibold">₹{{ number_format($b['grand_total']) }}</td>
                    <td class="px-6 py-4 text-blue-600 font-semibold">₹{{ number_format($b['amount_received']) }}</td>
                    <td class="px-6 py-4 text-red-600 font-semibold">₹{{ number_format($b['due_amount']) }}</td>
                    <td class="px-6 py-4">
                        {{ $b['action'] }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-6 text-center text-gray-500">No bookings available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ================= RECENT TRANSACTIONS ================= --}}
<div class="bg-white rounded-xl shadow-sm mb-6">
    <div class="px-6 pt-5 pb-1 flex justify-between items-center">
        <div>
            <h4 class="text-sm font-semibold text-gray-800">
                Recent Transactions
            </h4>
            <p class="text-xs text-gray-500">
                Total {{ count($transactions) }} Transactions shown
            </p>
        </div>
        <div class="text-xs text-gray-500 flex items-center gap-1">
            SORT BY:
            <select name="" id="">
                <option value="">1 Month</option>
                <option value="">6 Month</option>
                <option value="">1 Year</option>
            </select>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm mt-4">
            <thead class="border-b border-gray-200 bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">SN</th>
                    <th class="px-6 py-3 text-left">Transaction ID</th>
                    <th class="px-6 py-3 text-left">Customer</th>
                    <th class="px-6 py-3 text-left"># Of Bookings</th>
                    <th class="px-6 py-3 text-left">Payment Status</th>
                    <th class="px-6 py-3 text-left">Booking Type</th>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Amount Received</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $i => $t)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">{{ sprintf('%02d', $i+1) }}</td>
                    <td class="px-6 py-4 font-medium">{{ $t['id'] }}</td>
                    <td class="px-6 py-4">{{ $t['customer'] }}</td>
                    <td class="px-6 py-4 text-green-600 font-semibold">{{ $t['bookings'] }}</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                            {{ $t['status'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-blue-600">{{ $t['type'] }}</td>
                    <td class="px-6 py-4">{{ $t['date'] }}</td>
                    <td class="px-6 py-4 text-blue-600 font-semibold">₹{{ number_format($t['amount']) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-6 text-center text-gray-500">No transactions available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ================= ADMIN NOTES ================= --}}
<div class="bg-white rounded-xl shadow-sm p-6">
    <h4 class="text-sm font-semibold text-gray-800 mb-3">
        Admin Notes
    </h4>

    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
        <li>All data is fetched live from database</li>
        <li>User roles managed via Spatie permissions</li>
        <li>This dashboard is strictly admin-only</li>
        <li>No vendor components are reused</li>
    </ul>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// ===== USER CHART (Dynamic) =====
new Chart(document.getElementById('userChart'), {
    type: 'line',
    data: {
        labels: [
            ...Array.from({length: 12}, (_, i) => {
                const d = new Date();
                d.setMonth(d.getMonth() - (11 - i));
                return d.toLocaleString('default', { month: 'short' });
            })
        ],
        datasets: [{
            data: {!! json_encode($userGrowth) !!},
            borderColor: '#2563eb',
            tension: .4,
            fill: false
        }]
    },
    options: { plugins: { legend: { display: false } } }
});

// ===== BOOKING CHART (Dynamic, Booking + POSBooking) =====
new Chart(document.getElementById('bookingChart'), {
    type: 'line',
    data: {
        labels: [
            ...Array.from({length: 12}, (_, i) => {
                const d = new Date();
                d.setMonth(d.getMonth() - (11 - i));
                return d.toLocaleString('default', { month: 'short' });
            })
        ],
        datasets: [{
            data: {!! json_encode($bookingGrowth) !!},
            borderColor: '#14b8a6',
            tension: .4,
            fill: false
        }]
    },
    options: { plugins: { legend: { display: false } } }
});
</script>
@endpush

@extends('layouts.vendor')

@section('title', 'POS Dashboard')
@include('vendor.pos.components.pos-timer-notification')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Poppins', sans-serif; }
</style>

@section('content')
<div class="px-6 py-6 space-y-8 bg-gray-50 min-h-screen">

    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-[#1D1D1D]">Welcome, {{ Auth::user()->name }}</h2>
            <p class="text-sm text-gray-500 font-medium">POS Dashboard</p>
        </div>
        <button class="bg-[#1D1D1D] text-white px-6 py-2 rounded shadow-sm text-sm font-medium">POS</button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <div class="bg-[#4891EF] text-white p-5 flex gap-4">
            <div>
                <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="44" height="44" rx="2" fill="white"/>
                <path d="M17.4266 31.5247C15.8735 31.5247 14.5528 30.9806 13.4645 29.8923C12.3762 28.804 11.832 27.4829 11.832 25.9289C11.832 25.2724 11.9436 24.636 12.1667 24.0198C12.3899 23.4035 12.7121 22.8417 13.1334 22.3342L17.3771 17.2386L14.8637 12.1914H28.1337L25.6203 17.2386L29.864 22.3342C30.2853 22.8409 30.6075 23.4023 30.8307 24.0186C31.0538 24.6356 31.1654 25.2724 31.1654 25.9289C31.1654 27.4829 30.62 28.804 29.5293 29.8923C28.4386 30.9806 27.1191 31.5247 25.5708 31.5247H17.4266ZM21.4987 26.0631C20.9582 26.0631 20.4978 25.8734 20.1176 25.4939C19.7374 25.1137 19.5472 24.6534 19.5472 24.1128C19.5472 23.5715 19.7374 23.1107 20.1176 22.7305C20.4978 22.3503 20.9582 22.1602 21.4987 22.1602C22.0392 22.1602 22.4996 22.3503 22.8798 22.7305C23.26 23.1107 23.4502 23.5715 23.4502 24.1128C23.4502 24.6542 23.26 25.1145 22.8798 25.4939C22.4996 25.8734 22.0392 26.0631 21.4987 26.0631ZM18.4899 16.7227H24.5316L26.181 13.3997H16.8164L18.4899 16.7227ZM17.4266 30.3164H25.5708C26.7952 30.3164 27.8324 29.8879 28.6822 29.0307C29.5321 28.1736 29.957 27.1397 29.957 25.9289C29.957 25.415 29.8712 24.9156 29.6997 24.4306C29.5281 23.9457 29.2763 23.5074 28.9444 23.1159L24.6186 17.931H18.4078L14.0771 23.1087C13.7436 23.5018 13.4874 23.9416 13.3086 24.4282C13.1298 24.9148 13.0404 25.415 13.0404 25.9289C13.0404 27.1389 13.4693 28.1728 14.3272 29.0307C15.1844 29.8879 16.2175 30.3164 17.4266 30.3164Z" fill="#0089E1"/>
                </svg>
            </div>
           <div>
                <h6 class="text-sm opacity-80">Total Bookings</h6>
                <h2 id="total-bookings" class="text-3xl font-semibold">-</h2>
           </div>
        </div>

        <div class="bg-[#8153FF] text-white p-5 flex gap-4">
            <div>
                <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="44" height="44" rx="2" fill="white"/>
                <path d="M17 12.3594H18C18.2652 12.3594 18.5196 12.4647 18.7071 12.6523C18.8946 12.8398 19 13.0942 19 13.3594V14.3594H24V13.3594C24 13.0942 24.1054 12.8398 24.2929 12.6523C24.4804 12.4647 24.7348 12.3594 25 12.3594H26C26.2652 12.3594 26.5196 12.4647 26.7071 12.6523C26.8946 12.8398 27 13.0942 27 13.3594V14.3594C27.7956 14.3594 28.5587 14.6754 29.1213 15.2381C29.6839 15.8007 30 16.5637 30 17.3594V28.3594C30 29.155 29.6839 29.9181 29.1213 30.4807C28.5587 31.0433 27.7956 31.3594 27 31.3594H16C15.2044 31.3594 14.4413 31.0433 13.8787 30.4807C13.3161 29.9181 13 29.155 13 28.3594V17.3594C13 16.5637 13.3161 15.8007 13.8787 15.2381C14.4413 14.6754 15.2044 14.3594 16 14.3594V13.3594C16 13.0942 16.1054 12.8398 16.2929 12.6523C16.4804 12.4647 16.7348 12.3594 17 12.3594ZM25 14.3594H26V13.3594H25V14.3594ZM18 14.3594V13.3594H17V14.3594H18ZM16 15.3594C15.4696 15.3594 14.9609 15.5701 14.5858 15.9452C14.2107 16.3202 14 16.8289 14 17.3594V18.3594H29V17.3594C29 16.8289 28.7893 16.3202 28.4142 15.9452C28.0391 15.5701 27.5304 15.3594 27 15.3594H16ZM14 28.3594C14 28.8898 14.2107 29.3985 14.5858 29.7736C14.9609 30.1487 15.4696 30.3594 16 30.3594H27C27.5304 30.3594 28.0391 30.1487 28.4142 29.7736C28.7893 29.3985 29 28.8898 29 28.3594V19.3594H14V28.3594ZM22 23.3594H27V28.3594H22V23.3594ZM23 24.3594V27.3594H26V24.3594H23Z" fill="#8153FF"/>
                </svg>
            </div>
           <div>
                <h6 class="text-sm opacity-80">Total Revenue</h6>
                <h2 id="total-revenue" class="text-3xl font-semibold">-</h2>
           </div>
        </div>

        <div class="bg-[#2CB67D] text-white p-5 flex gap-4">
            <div>
                <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="44" height="44" rx="2" fill="white"/>
                <path d="M19 17.3594H15.145C16.0734 16.082 17.3393 15.0884 18.8007 14.4902C20.2622 13.8919 21.8614 13.7126 23.419 13.9724C23.5472 13.9883 23.6766 13.9539 23.78 13.8765C23.8835 13.7991 23.953 13.6847 23.9739 13.5572C23.9948 13.4298 23.9656 13.2991 23.8924 13.1927C23.8191 13.0863 23.7076 13.0123 23.581 12.9864C23.0582 12.9022 22.5295 12.8598 22 12.8594C20.5526 12.8618 19.1248 13.1942 17.8252 13.8314C16.5256 14.4686 15.3884 15.3937 14.5 16.5364V12.8594C14.5 12.7268 14.4473 12.5996 14.3536 12.5058C14.2598 12.4121 14.1326 12.3594 14 12.3594C13.8674 12.3594 13.7402 12.4121 13.6464 12.5058C13.5527 12.5996 13.5 12.7268 13.5 12.8594V17.8594C13.5 17.992 13.5527 18.1192 13.6464 18.2129C13.7402 18.3067 13.8674 18.3594 14 18.3594H19C19.1326 18.3594 19.2598 18.3067 19.3536 18.2129C19.4473 18.1192 19.5 17.992 19.5 17.8594C19.5 17.7268 19.4473 17.5996 19.3536 17.5058C19.2598 17.4121 19.1326 17.3594 19 17.3594ZM17.5 24.8594C17.3674 24.8594 17.2402 24.9121 17.1464 25.0058C17.0527 25.0996 17 25.2268 17 25.3594V29.2144C15.7226 28.286 14.7291 27.0201 14.1308 25.5587C13.5326 24.0972 13.3533 22.498 13.613 20.9404C13.6289 20.8122 13.5945 20.6828 13.5172 20.5793C13.4398 20.4759 13.3253 20.4064 13.1979 20.3855C13.0704 20.3645 12.9397 20.3937 12.8333 20.467C12.7269 20.5402 12.653 20.6518 12.627 20.7784C12.3451 22.4715 12.5263 24.2093 13.1512 25.8078C13.7761 27.4064 14.8216 28.8063 16.177 29.8594H12.5C12.3674 29.8594 12.2402 29.9121 12.1464 30.0058C12.0527 30.0996 12 30.2268 12 30.3594C12 30.492 12.0527 30.6192 12.1464 30.7129C12.2402 30.8067 12.3674 30.8594 12.5 30.8594H17.5C17.6326 30.8594 17.7598 30.8067 17.8536 30.7129C17.9473 30.6192 18 30.492 18 30.3594V25.3594C18 25.2268 17.9473 25.0996 17.8536 25.0058C17.7598 24.9121 17.6326 24.8594 17.5 24.8594ZM30 26.3594H25C24.8674 26.3594 24.7402 26.4121 24.6464 26.5058C24.5527 26.5996 24.5 26.7268 24.5 26.8594C24.5 26.992 24.5527 27.1192 24.6464 27.2129C24.7402 27.3067 24.8674 27.3594 25 27.3594H28.855C27.9266 28.6368 26.6607 29.6303 25.1993 30.2286C23.7378 30.8268 22.1386 31.0061 20.581 30.7464C20.4528 30.7305 20.3234 30.7648 20.22 30.8422C20.1165 30.9196 20.047 31.034 20.0261 31.1615C20.0052 31.289 20.0344 31.4196 20.1076 31.5261C20.1809 31.6325 20.2924 31.7064 20.419 31.7324C20.9418 31.8165 21.4705 31.859 22 31.8594C23.4474 31.8569 24.8752 31.5245 26.1748 30.8874C27.4744 30.2502 28.6116 29.3251 29.5 28.1824V31.8594C29.5 31.992 29.5527 32.1192 29.6464 32.2129C29.7402 32.3067 29.8674 32.3594 30 32.3594C30.1326 32.3594 30.2598 32.3067 30.3536 32.2129C30.4473 32.1192 30.5 31.992 30.5 31.8594V26.8594C30.5 26.7268 30.4473 26.5996 30.3536 26.5058C30.2598 26.4121 30.1326 26.3594 30 26.3594ZM31.5 13.8594H26.5C26.3674 13.8594 26.2402 13.9121 26.1464 14.0058C26.0527 14.0996 26 14.2268 26 14.3594V19.3594C26 19.492 26.0527 19.6192 26.1464 19.7129C26.2402 19.8067 26.3674 19.8594 26.5 19.8594C26.6326 19.8594 26.7598 19.8067 26.8536 19.7129C26.9473 19.6192 27 19.492 27 19.3594V15.4994C27.9637 16.1915 28.7677 17.0822 29.358 18.1114C30.1075 19.4015 30.5015 20.8673 30.5 22.3594C30.5 22.8354 30.4623 23.3084 30.387 23.7784C30.3764 23.8431 30.3786 23.9093 30.3935 23.9732C30.4085 24.0371 30.4359 24.0974 30.4741 24.1507C30.5124 24.204 30.5608 24.2492 30.6165 24.2839C30.6722 24.3185 30.7343 24.3417 30.799 24.3524C30.8637 24.363 30.9299 24.3608 30.9938 24.3458C31.0577 24.3309 31.118 24.3035 31.1713 24.2653C31.2246 24.227 31.2699 24.1786 31.3045 24.1229C31.3391 24.0671 31.3624 24.0051 31.373 23.9404C31.4571 23.4176 31.4996 22.8889 31.5 22.3594C31.5016 20.6922 31.0617 19.0544 30.225 17.6124C29.6157 16.5454 28.801 15.6097 27.828 14.8594H31.5C31.6326 14.8594 31.7598 14.8067 31.8536 14.7129C31.9473 14.6192 32 14.492 32 14.3594C32 14.2268 31.9473 14.0996 31.8536 14.0058C31.7598 13.9121 31.6326 13.8594 31.5 13.8594Z" fill="#2CB67D"/>
                </svg>
            </div>
           <div>
                <h6 class="text-sm opacity-80">Pending Payments</h6>
                <h2 id="pending-payments" class="text-3xl font-semibold">-</h2>
           </div>
        </div>

        <div class="bg-[#092C4C] text-white p-5 flex gap-4">
            <div>
                <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="44" height="44" rx="2" fill="white"/>
                <path d="M20.1149 24.7443C21.1159 24.1769 21.901 23.2941 22.3477 22.2337C22.7943 21.1733 22.8775 19.9949 22.5841 18.8823C22.2907 17.7696 21.6373 16.7854 20.7258 16.0831C19.8143 15.3808 18.696 15 17.5454 15C16.3947 15 15.2764 15.3808 14.3649 16.0831C13.4535 16.7854 12.8001 17.7696 12.5067 18.8823C12.2133 19.9949 12.2964 21.1733 12.7431 22.2337C13.1897 23.2941 13.9748 24.1769 14.9758 24.7443C13.1205 25.3358 11.5272 26.5513 10.4666 28.1844C10.3884 28.3042 10.361 28.45 10.3904 28.59C10.4198 28.7299 10.5036 28.8524 10.6234 28.9306C10.7431 29.0087 10.889 29.0361 11.0289 29.0067C11.1688 28.9773 11.2913 28.8935 11.3695 28.7738C12.0385 27.7452 12.9538 26.9 14.0323 26.3149C15.1108 25.7298 16.3184 25.4233 17.5454 25.4233C18.7724 25.4233 19.9799 25.7298 21.0584 26.3149C22.1369 26.9 23.0522 27.7452 23.7212 28.7738C23.8045 28.8805 23.9248 28.952 24.0583 28.974C24.1918 28.9961 24.3288 28.9671 24.4419 28.8929C24.555 28.8187 24.6361 28.7046 24.6691 28.5734C24.702 28.4421 24.6844 28.3033 24.6197 28.1844C23.5601 26.5521 21.9685 25.3367 20.1149 24.7443ZM13.4125 20.2135C13.4125 19.3961 13.6549 18.5971 14.1091 17.9174C14.5632 17.2378 15.2086 16.7081 15.9638 16.3953C16.719 16.0825 17.5499 16.0006 18.3516 16.1601C19.1533 16.3195 19.8897 16.7132 20.4677 17.2911C21.0457 17.8691 21.4393 18.6055 21.5988 19.4072C21.7582 20.2089 21.6764 21.0399 21.3636 21.795C21.0508 22.5502 20.5211 23.1957 19.8414 23.6498C19.1618 24.1039 18.3628 24.3463 17.5454 24.3463C16.4497 24.3449 15.3993 23.909 14.6246 23.1342C13.8499 22.3595 13.414 21.3091 13.4125 20.2135ZM32.3696 28.9283C32.2499 29.0064 32.1042 29.0338 31.9644 29.0045C31.8246 28.9752 31.7021 28.8915 31.6239 28.772C30.9561 27.7433 30.0415 26.8981 28.9634 26.3134C27.8853 25.7287 26.678 25.4231 25.4516 25.4244C25.3086 25.4244 25.1715 25.3676 25.0704 25.2665C24.9693 25.1654 24.9125 25.0283 24.9125 24.8854C24.9125 24.7424 24.9693 24.6053 25.0704 24.5042C25.1715 24.4031 25.3086 24.3463 25.4516 24.3463C26.0602 24.3457 26.6611 24.2107 27.2115 23.9509C27.7619 23.6911 28.2481 23.313 28.6354 22.8436C29.0227 22.3741 29.3015 21.8249 29.452 21.2352C29.6024 20.6455 29.6208 20.0299 29.5058 19.4323C29.3907 18.8347 29.1451 18.2698 28.7865 17.7781C28.4279 17.2864 27.9651 16.88 27.4312 16.5879C26.8973 16.2958 26.3055 16.1252 25.698 16.0883C25.0905 16.0514 24.4824 16.1491 23.9171 16.3745C23.8512 16.4014 23.7807 16.4151 23.7096 16.4146C23.6384 16.4142 23.5681 16.3997 23.5026 16.372C23.4371 16.3442 23.3777 16.3038 23.3279 16.253C23.2781 16.2022 23.2388 16.142 23.2124 16.076C23.1859 16.0099 23.1728 15.9393 23.1738 15.8681C23.1748 15.797 23.1898 15.7268 23.2181 15.6615C23.2464 15.5962 23.2873 15.5371 23.3385 15.4877C23.3897 15.4383 23.4501 15.3995 23.5164 15.3736C24.7426 14.8847 26.1086 14.8795 27.3386 15.3589C28.5685 15.8383 29.5707 16.7666 30.1426 17.9563C30.7145 19.146 30.8136 20.5085 30.4199 21.7685C30.0261 23.0284 29.1688 24.092 28.0211 24.7443C29.8764 25.3358 31.4698 26.5513 32.5304 28.1844C32.6075 28.3045 32.6338 28.4502 32.6037 28.5896C32.5736 28.729 32.4894 28.8508 32.3696 28.9283Z" fill="#138808"/>
                </svg>
            </div>
           <div>
                <h6 class="text-sm opacity-80">Active Credit Notes</h6>
                <h2 id="active-credit-notes" class="text-3xl font-semibold">-</h2>
           </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-4">
        <a
            href="{{ route('vendor.pos.create') }}"
            class="inline-flex items-center gap-2 px-6 py-3 rounded-lg btn-color text-white text-sm font-medium transition"
        >
            + Create New POS Booking
        </a>

        <a href="{{ route('vendor.pos.list') }}"
           class="px-5 py-2.5 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-100 transition">
            View All Bookings
        </a>
    </div>

    <!-- Recent Bookings -->
    <div class="bg-white  shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h5 class="text-lg font-semibold">Recent POS Bookings</h5>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Invoice</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Total Hoardings</th>
                        <th class="px-4 py-3 text-left">Booking Date</th>
                        <th class="px-4 py-3 text-left">Amount</th>
                        <th class="px-4 py-3 text-left">Payment</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Hold</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody id="recent-bookings-body" class="">
                    <tr>
                        <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pending Payments Widget -->
    <div id="pending-payments-widget" class="bg-white  shadow-sm border border-gray-200 hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-yellow-50">
            <h5 class="text-lg font-semibold text-yellow-800">Pending Payment</h5>
            <p class="text-sm text-yellow-700">Bookings with pending payment that need attention</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Invoice</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Amount</th>
                        <th class="px-4 py-3 text-left">Pending Since</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody id="pending-payments-body" class="">
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
/**
 * POS Dashboard - Web Session Auth
 * Uses new /vendor/pos/api/* endpoints with session auth
 * No tokens, credentials: 'same-origin'
 */

document.addEventListener('DOMContentLoaded', function () {

    // Helper: Fetch with session auth
    const fetchJSON = async (url) => {
        const res = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        if (!res.ok) {
            throw { status: res.status, message: 'Failed to fetch' };
        }

        return res.json();
    };

    // Helper: Format date to DD/MM/YYYY
    function formatDateDDMMYYYY(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;
        
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        
        return `${day}/${month}/${year}`;
    }

    // Load dashboard statistics
    fetchJSON('/vendor/pos/api/dashboard')
        .then(data => {
            if (data.success) {
                document.getElementById('total-bookings').textContent = data.data.total_bookings;
                document.getElementById('total-revenue').textContent = '₹' + data.data.total_revenue.toLocaleString();
                document.getElementById('pending-payments').textContent = '₹' + data.data.pending_payments.toLocaleString();
                document.getElementById('total-customers').textContent = data.data.total_customers;
            }
        })
        .catch(err => console.warn('Could not load dashboard stats:', err));

    // Load recent bookings
    fetchJSON('/vendor/pos/api/bookings?per_page=10')
        .then(data => {
            const tbody = document.getElementById('recent-bookings-body');

            if (data.success && data.data.data.length) {
                tbody.innerHTML = '';
                data.data.data.forEach(b => {
                    // Calculate hold expiry display
                    let holdExpiryDisplay = '-';
                    let holdExpiryClass = '';
                    
                    if (b.hold_expiry_at) {
                        const holdExpiry = new Date(b.hold_expiry_at);
                        const now = new Date();
                        const diff = holdExpiry - now;

                        if (diff > 0) {
                            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            holdExpiryDisplay = `In ${days}d ${hours}h`;
                            holdExpiryClass = diff < (12 * 60 * 60 * 1000) ? 'text-red-600 font-semibold' : 'text-yellow-600';
                        } else {
                            holdExpiryDisplay = 'EXPIRED!';
                            holdExpiryClass = 'text-red-600 font-semibold bg-red-50 px-2 py-1 rounded';
                        }
                    }

                    tbody.innerHTML += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">${b.invoice_number || 'N/A'}</td>
                        <td class="px-4 py-3">${b.customer_name}</td>
                    <td class="px-4 py-3 font-bold text-gray-800">
                        ${Array.isArray(b.hoardings) ? b.hoardings.length : (b.hoardings_count ?? 1)}
                    </td>
                    <td class="px-4 py-3">
                        ${formatDateDDMMYYYY(b.created_at)} 
                    </td>
                    <td class="px-4 py-3 font-medium">₹${parseFloat(b.total_amount).toLocaleString()}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full ${paymentBadge(b.payment_status)}">
                            ${b.payment_status}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full ${statusBadge(b.status)}">
                            ${b.status}
                        </span>
                    </td>
                    <td class="px-4 py-3 ${holdExpiryClass}">
                        ${holdExpiryDisplay}
                    </td>
                    <td class="px-4 py-3">
                        <a href="/vendor/pos/bookings/${b.id}"
                           class="text-blue-600 hover:underline text-sm">
                            View
                        </a>
                    </td>
                </tr>`;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="9" class="px-4 py-6 text-center text-gray-500">No bookings found</td></tr>`;
        }
    })
        .catch(err => console.warn('Could not load bookings:', err));

    // Load pending payments (bookings with active holds)
    fetchJSON('/vendor/pos/api/pending-payments')
        .then(data => {
            if (data.success && data.data.length > 0) {
                const widget = document.getElementById('pending-payments-widget');
                const tbody = document.getElementById('pending-payments-body');
                widget.classList.remove('hidden');

                tbody.innerHTML = '';
                data.data.forEach(b => {
                    // Calculate days pending
                    const createdDate = new Date(b.created_at);
                    const now = new Date();
                    const daysPending = Math.floor((now - createdDate) / (1000 * 60 * 60 * 24));
                    
                    let daysText = `${daysPending} day${daysPending !== 1 ? 's' : ''} pending`;
                    let rowClass = daysPending > 7 ? 'bg-red-100 border-l-4 border-red-600' : (daysPending > 3 ? 'bg-red-50' : 'bg-yellow-50');

                    tbody.innerHTML += `
                    <tr class="${rowClass}">
                        <td class="px-4 py-3 font-medium">${b.invoice_number || 'N/A'}</td>
                        <td class="px-4 py-3">${b.customer_name}</td>
                        <td class="px-4 py-3 font-semibold">₹${parseFloat(b.total_amount).toLocaleString()}</td>
                        <td class="px-4 py-3 font-semibold text-red-600">${daysText}</td>
                        <td class="px-4 py-3">
                            <a href="/vendor/pos/bookings/${b.id}"
                               class="text-blue-600 hover:underline text-sm font-medium">
                                View & Mark Paid
                            </a>
                        </td>
                    </tr>`;
                });
            }
        })
        .catch(err => console.warn('Could not load pending payments:', err));
});

function statusBadge(status) {
    return {
        draft: 'bg-gray-200 text-gray-700',
        confirmed: 'bg-green-100 text-green-700',
        active: 'bg-blue-100 text-blue-700',
        completed: 'bg-cyan-100 text-cyan-700',
        cancelled: 'bg-red-100 text-red-700'
    }[status] || 'bg-gray-200 text-gray-700';
}

function paymentBadge(status) {
    return {
        paid: 'bg-green-100 text-green-700',
        unpaid: 'bg-red-100 text-red-700',
        partial: 'bg-yellow-100 text-yellow-700',
        credit: 'bg-cyan-100 text-cyan-700'
    }[status] || 'bg-gray-200 text-gray-700';
}
</script>
@endsection
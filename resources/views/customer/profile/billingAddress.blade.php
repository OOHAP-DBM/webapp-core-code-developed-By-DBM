@extends('layouts.customer')

@section('title', 'Billing Address')

@section('content')
<div class="w-full px-4 py-6">
    <div class="w-full max-w-none">
        <div class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="text-decoration-none">Home</a>
        >
        <a href="{{ route('customer.dashboard') }}" class="text-decoration-none">My Account</a>
        >
        Profile
        >
        <span class="text-gray-800">Billing Address</span>
    </div>
    {{-- BIG CARD (FULL WIDTH) --}}
    <div class="bg-white  rounded-xl shadow-md p-6">

        {{-- TITLE --}}
        <h2 class="text-lg font-semibold text-gray-900 mb-6">
            Billing Address
        </h2>

        {{-- INNER LAYOUT --}}
        <div class="flex gap-6">

            {{-- SMALL LEFT CARD --}}
            <div class="w-72 bg-white rounded-lg shadow-sm border border-gray-200 p-4 relative">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">
                    {{ auth()->user()->name ?? '-' }}
                </h3>

                <div class="space-y-2 text-sm text-gray-700">
                    <div>
                        <span class="">Mobile:</span>
                        <span>{{ auth()->user()->phone ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="font-medium">{{ auth()->user()->address ?? '-' }}</span>
                    </div>
                </div>

                {{-- EDIT BUTTON --}}
                <button
                    onclick="openEditModal()"
                    style="color:#108fe3;font-weight:600;"
                    class="absolute bottom-3 right-3 px-3 py-1.5 text-xs hover:underline transition cursor-pointer"
                   >
                    Edit
                </button>



            </div>
        </div>
    </div>
</div>

    {{-- EDIT BILLING ADDRESS MODAL --}}
    <div
        id="editModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
        >
        <div class="bg-white w-full max-w-3xl rounded-xl shadow-xl p-6 relative">

            {{-- HEADER --}}
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">
                    Edit Billing Address
                </h2>
                <button
                    onclick="closeEditModal()"
                    class="text-xl text-gray-900 hover:text-gray-800 cursor-pointer"
                >
                    ×
                </button>
            </div>

            <form action="{{ route('customer.billing.update') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- FULL NAME --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">
                            Full Name<span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            value="{{ auth()->user()->name ?? '' }}"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-md
                                text-sm focus:outline-none focus:ring-1 focus:ring-green-600"
                        >
                    </div>

                    {{-- MOBILE --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">
                            Mobile Number
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                name="phone"
                                value="{{ auth()->user()->phone ?? '' }}"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-md
                                    text-sm focus:outline-none focus:ring-1 focus:ring-green-600"
                            >

                            @if(auth()->user()->phone_verified_at)
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-green-600 text-sm">
                                    <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.82113 3.25867C9.5243 1.89383 11.4757 1.89383 12.1789 3.25867C12.6014 4.07878 13.5546 4.47361 14.4333 4.19247C15.8956 3.7246 17.2754 5.10445 16.8075 6.56674C16.5264 7.44542 16.9212 8.39861 17.7413 8.82113C19.1062 9.5243 19.1062 11.4757 17.7413 12.1789C16.9212 12.6014 16.5264 13.5546 16.8075 14.4333C17.2754 15.8956 15.8956 17.2754 14.4333 16.8075C13.5546 16.5264 12.6014 16.9212 12.1789 17.7413C11.4757 19.1062 9.5243 19.1062 8.82113 17.7413C8.39861 16.9212 7.44542 16.5264 6.56674 16.8075C5.10445 17.2754 3.7246 15.8956 4.19247 14.4333C4.47361 13.5546 4.07878 12.6014 3.25867 12.1789C1.89383 11.4757 1.89383 9.5243 3.25867 8.82113C4.07878 8.39861 4.47361 7.44542 4.19247 6.56674C3.7246 5.10445 5.10445 3.7246 6.56674 4.19247C7.44542 4.47361 8.39861 4.07878 8.82113 3.25867Z" fill="#009A5C"/>
                                    <path d="M12.9997 8.04148C13.1786 7.83108 13.4554 7.82116 13.6658 8C13.8762 8.17884 13.8455 8.45626 13.6667 8.66667L10.3806 12.8238C10.199 13.0375 9.87711 13.06 9.66748 12.8737L7.41748 10.8737C7.21109 10.6902 7.1925 10.3742 7.37596 10.1678C7.55941 9.96142 7.87545 9.94283 8.08184 10.1263L9.94977 11.7867L12.9997 8.04148Z" fill="white"/>
                                    </svg>
                                </span>
                            @endif
                        </div>

                    </div>

                    {{-- EMAIL --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">
                            Email<span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="email"
                                name="email"
                                value="{{ auth()->user()->email ?? '' }}"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-md
                                    text-sm focus:outline-none focus:ring-1 focus:ring-green-600"
                            >

                            @if(auth()->user()->email_verified_at)
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-green-600 text-sm">
                                    <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.82113 3.25867C9.5243 1.89383 11.4757 1.89383 12.1789 3.25867C12.6014 4.07878 13.5546 4.47361 14.4333 4.19247C15.8956 3.7246 17.2754 5.10445 16.8075 6.56674C16.5264 7.44542 16.9212 8.39861 17.7413 8.82113C19.1062 9.5243 19.1062 11.4757 17.7413 12.1789C16.9212 12.6014 16.5264 13.5546 16.8075 14.4333C17.2754 15.8956 15.8956 17.2754 14.4333 16.8075C13.5546 16.5264 12.6014 16.9212 12.1789 17.7413C11.4757 19.1062 9.5243 19.1062 8.82113 17.7413C8.39861 16.9212 7.44542 16.5264 6.56674 16.8075C5.10445 17.2754 3.7246 15.8956 4.19247 14.4333C4.47361 13.5546 4.07878 12.6014 3.25867 12.1789C1.89383 11.4757 1.89383 9.5243 3.25867 8.82113C4.07878 8.39861 4.47361 7.44542 4.19247 6.56674C3.7246 5.10445 5.10445 3.7246 6.56674 4.19247C7.44542 4.47361 8.39861 4.07878 8.82113 3.25867Z" fill="#009A5C"/>
                                    <path d="M12.9997 8.04148C13.1786 7.83108 13.4554 7.82116 13.6658 8C13.8762 8.17884 13.8455 8.45626 13.6667 8.66667L10.3806 12.8238C10.199 13.0375 9.87711 13.06 9.66748 12.8737L7.41748 10.8737C7.21109 10.6902 7.1925 10.3742 7.37596 10.1678C7.55941 9.96142 7.87545 9.94283 8.08184 10.1263L9.94977 11.7867L12.9997 8.04148Z" fill="white"/>
                                    </svg>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- BILLING ADDRESS --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">
                            Billing Address<span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="billing_address"
                            value="{{ auth()->user()->billing_address ?? '' }}"
                            placeholder="Enter billing address"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-md
                                text-sm focus:outline-none focus:ring-1 focus:ring-green-600"
                        >
                    </div>

                    {{-- PINCODE --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">
                            Pincode<span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            name="billing_pincode"
                            value="{{ auth()->user()->billing_pincode?? '' }}"
                            placeholder="Enter pincode"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-md
                                text-sm focus:outline-none focus:ring-1 focus:ring-green-600"
                        >
                    </div>

                    {{-- CITY --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">
                            City
                        </label>
                        <input
                            type="text"
                            name="billing_city"
                            value="{{ auth()->user()->billing_city ?? '' }}"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-md
                                text-sm focus:outline-none focus:ring-1 focus:ring-green-600"
                        >
                    </div>

                    {{-- STATE (FULL WIDTH) --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-800 mb-1">
                            State
                        </label>
                        <input
                            type="text"
                            name="billing_state"
                            value="{{ auth()->user()->billing_state ?? '' }}"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-md
                                text-sm focus:outline-none focus:ring-1 focus:ring-green-600"
                        >
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="flex justify-end mt-8">
                    <button
                        type="submit"
                        class="px-24 py-2.5 rounded-md text-sm font-semibold
                            bg-gray-200 text-gray-800 cursor-pointer hover:bg-gray-300"
                    >
                        Save
                    </button>
                </div>

            </form>
        </div>
    </div>


{{-- JS --}}
<script>
    function openEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection

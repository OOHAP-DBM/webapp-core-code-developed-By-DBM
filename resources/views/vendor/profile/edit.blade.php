@extends('layouts.vendor')

@section('title', 'My Profile')

@section('content')
<div 
    class="max-w-full mx-auto px-2 py-8 space-y-6"
    x-data="{ showModal: false, modalType: null }"
>

    {{-- PERSONAL INFO --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Personal Info</h2>
            <button
                @click="showModal = true; modalType = 'personal'"
                class="text-blue-600 text-sm"
            >Edit</button>
        </div>

        <div class="grid md:grid-cols-3 gap-4 text-sm">
            <div>
                <label class="text-gray-500">Full Name</label>
                <p class="font-medium">{{ auth()->user()->name }}</p>
            </div>
            <div>
                <label class="text-gray-500">Email Address</label>
                <p class="font-medium flex items-center gap-1">
                    {{ auth()->user()->email }}
                    @if(auth()->user()->email)
                        <span class="text-green-600">✔</span>
                    @endif
                </p>
            </div>
            <div>
                <label class="text-gray-500">Mobile Number</label>
                <p class="font-medium flex items-center gap-1">
                        {{ auth()->user()->phone }}
                    @if(auth()->user()->phone)
                        <span class="text-green-600">✔</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- BUSINESS DETAILS --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Business Details</h2>
            <button
                @click="showModal = true; modalType = 'business'"
                class="text-blue-600 text-sm"
            >Edit</button>
        </div>

        <div class="grid md:grid-cols-4 gap-4 text-sm">
            <div>
                <label class="text-gray-500">GSTIN</label>
                <p class="font-medium">{{ $vendor->gstin }}</p>
            </div>
            <div>
                <label class="text-gray-500">Business Name</label>
                <p class="font-medium">{{ $vendor->company_name }}</p>
            </div>
            <div>
                <label class="text-gray-500">Business Type</label>
                <p class="font-medium">{{ $vendor->company_type }}</p>
            </div>
            <div>
                <label class="text-gray-500">PAN</label>
                <p class="font-medium flex items-center gap-2">
                    {{ $vendor->pan }}
                    <button
                        @click="showModal = true; modalType = 'pan'"
                        class="text-blue-600 text-xs"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21.544 11.045C21.848 11.471 22 11.685 22 12C22 12.316 21.848 12.529 21.544 12.955C20.178 14.871 16.689 19 12 19C7.31 19 3.822 14.87 2.456 12.955C2.152 12.529 2 12.315 2 12C2 11.684 2.152 11.471 2.456 11.045C3.822 9.129 7.311 5 12 5C16.69 5 20.178 9.13 21.544 11.045Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15 12C15 11.2044 14.6839 10.4413 14.1213 9.87868C13.5587 9.31607 12.7956 9 12 9C11.2044 9 10.4413 9.31607 9.87868 9.87868C9.31607 10.4413 9 11.2044 9 12C9 12.7956 9.31607 13.5587 9.87868 14.1213C10.4413 14.6839 11.2044 15 12 15C12.7956 15 13.5587 14.6839 14.1213 14.1213C14.6839 13.5587 15 12.7956 15 12Z" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </p>
            </div>
        </div>
    </div>

    {{-- BANK DETAILS --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Bank Details</h2>
            <button
                @click="showModal = true; modalType = 'bank'"
                class="text-blue-600 text-sm"
            >Edit</button>
        </div>

        <div class="grid md:grid-cols-4 gap-4 text-sm">
            <div>
                <label class="text-gray-500">Bank Name</label>
                <p class="font-medium">{{ $vendor->bank_name }}</p>
            </div>
            <div>
                <label class="text-gray-500">Account Holder</label>
                <p class="font-medium">{{ $vendor->account_holder_name }}</p>
            </div>
            <div>
                <label class="text-gray-500">Account Number</label>
                <p class="font-medium">{{ $vendor->account_number }}</p>
            </div>
            <div>
                <label class="text-gray-500">IFSC Code</label>
                <p class="font-medium">{{ $vendor->ifsc_code }}</p>
            </div>
        </div>
    </div>

    {{-- ADDRESS --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Registered Business Address</h2>
            <button
                @click="showModal = true; modalType = 'address'"
                class="text-blue-600 text-sm"
            >Edit</button>
        </div>

        <div class="grid md:grid-cols-4 gap-4 text-sm">
            <div class="md:col-span-4">
                <label class="text-gray-500">Business Address</label>
                <p class="font-medium">{{ $vendor->registered_address }}</p>
            </div>
            <div>
                <label class="text-gray-500">Pincode</label>
                <p class="font-medium">{{ $vendor->pincode }}</p>
            </div>
            <div>
                <label class="text-gray-500">City</label>
                <p class="font-medium">{{ $vendor->city }}</p>
            </div>
            <div>
                <label class="text-gray-500">State</label>
                <p class="font-medium">{{ $vendor->state }}</p>
            </div>
            <div>
                <label class="text-gray-500">Country</label>
                <p class="font-medium">{{ $user->country }}</p>
            </div>
        </div>
    </div>

    {{-- DELETE ACCOUNT --}}
    <div class="bg-white rounded-xl shadow p-6 border border-red-200">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-lg font-semibold text-red-600">Delete Account</h2>
                <ul class="text-sm text-gray-500 list-disc ml-5 mt-1">
                    <li>No active orders</li>
                    <li>No pending settlements</li>
                </ul>
            </div>
            <button
                @click="showModal = true; modalType = 'delete'"
                class="text-red-600 text-sm font-medium"
            >
                Delete
            </button>
        </div>
    </div>

    {{-- GLOBAL MODAL --}}
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    >
        <div class="bg-white rounded-xl w-full max-w-lg p-4 sm:p-6 relative max-h-[90vh] overflow-y-auto mx-auto">

            <button
                @click="showModal=false"
                class="absolute top-3 right-3 text-gray-400 hover:text-black"
            >✕</button>

            <template x-if="modalType === 'personal'">
                @include('vendor.profile.modals.personal')
            </template>

            <template x-if="modalType === 'business'">
                @include('vendor.profile.modals.business')
            </template>

            <template x-if="modalType === 'bank'">
                @include('vendor.profile.modals.bank')
            </template>

            <template x-if="modalType === 'address'">
                @include('vendor.profile.modals.address')
            </template>

            <template x-if="modalType === 'pan'">
                @include('vendor.profile.modals.pan')
            </template>

            <template x-if="modalType === 'delete'">
                @include('vendor.profile.modals.delete-account')
            </template>

        </div>
    </div>

</div>
@endsection

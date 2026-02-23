@extends('layouts.admin')

@section('title', 'Vendor Details')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home', 'route' => route('admin.dashboard')],
    ['label' => 'Vendors Management', 'route' => route('admin.vendors.index')],
    ['label' => $user->name]
]" />
@endsection

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="bg-white rounded-xl p-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800">
                {{ $user->name }}
            </h2>
            <p class="text-sm text-gray-500">
                Requested On: {{ $user->created_at->format('M d, Y') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">Profile Status</span>
            <div class="w-32 bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full w-full"></div>
            </div>

            @if($vendorProfile?->onboarding_status === 'pending_approval')
                <form method="POST" action="{{ route('admin.vendors.approve', $vendorProfile->id) }}">
                    @csrf
                    <button class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg text-sm">
                        Approve
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-4 gap-4">
        @php
            $stats = [
                ['Total Earnings', '₹0'],
                ['Total Hoardings', $totalHoardings ?? 0],
                ['Ongoing Orders', 0],
                ['Commission', ($commission ?? 0) . '%'],
            ];
        @endphp
        @foreach($stats as [$label, $value])
            <div class="bg-white p-4 rounded-xl">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="text-xl font-semibold">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    {{-- PERSONAL INFO --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Personal Info</h2>
            <!-- <button
                @click="showModal = true; modalType = 'personal'"
                class="text-blue-600 text-sm"
            >Edit</button> -->
        </div>
        <div class="grid md:grid-cols-4 gap-4 text-sm">
            <div>
                <div class="flex items-center gap-3 mt-2">
                    @if($user->avatar)
                        <img
                            src="{{ route('view-avatar', $user->id) }}"
                            alt="Avatar"
                            class="avatar-img"
                        >
                    @else
                        <span class="text-gray-400 text-xs">No avatar</span>
                    @endif
                </script>
                <style>
                .avatar-img {
                    width: 80px;
                    height: 80px;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 2px solid #e5e7eb;
                    display: block;
                }
                </style>
                </div>
            </div>
            <div>
                <label class="text-gray-500">Full Name</label>
                <p class="font-medium">{{ $user->name }}</p>
            </div>
            <div>
                <label class="text-gray-500">Email Address</label>
                <p class="font-medium flex items-center gap-1">
                    {{ $user->email }}
                    @if($user->email)
                        <span class="text-green-600">✔</span>
                    @endif
                </p>
            </div>
            <div>
                <label class="text-gray-500">Mobile Number</label>
                <p class="font-medium flex items-center gap-1">
                    {{ $user->phone }}
                    @if($user->phone)
                        <span class="text-green-600">✔</span>
                    @endif
                </p>
                <div class="mt-4 text-right">
            <!-- <button
                @click="showModal = true; modalType = 'change-password'"
                class="text-blue-600 text-sm"
            >Change Password</button> -->
        </div>
            </div>
            
        </div>
    </div>

    {{-- BUSINESS DETAILS --}}
    <div class="bg-white rounded-xl shadow p-6 mt-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Business Details</h2>
            <!-- <button
                @click="showModal = true; modalType = 'business'"
                class="text-blue-600 text-sm"
            >Edit</button> -->
        </div>
        <div class="grid md:grid-cols-4 gap-4 text-sm">
            <div>
                <label class="text-gray-500">GSTIN</label>
                <p class="font-medium">{{ $vendorProfile->gstin }}</p>
            </div>
            <div>
                <label class="text-gray-500">Business Name</label>
                <p class="font-medium">{{ $vendorProfile->company_name }}</p>
            </div>
            <div>
                <label class="text-gray-500">Business Type</label>
                <p class="font-medium">{{ $vendorProfile->company_type }}</p>
            </div>
            <div>
                <label class="text-gray-500">PAN</label>
                <p class="font-medium flex items-center gap-2">
                    {{ $vendorProfile->pan }}
                </p>
            </div>
        </div>
    </div>

    {{-- BANK DETAILS --}}
    <div class="bg-white rounded-xl shadow p-6 mt-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Bank Details</h2>
            <!-- <button
                @click="showModal = true; modalType = 'bank'"
                class="text-blue-600 text-sm"
            >Edit</button> -->
        </div>
        <div class="grid md:grid-cols-4 gap-4 text-sm">
            <div>
                <label class="text-gray-500">Bank Name</label>
                <p class="font-medium">{{ $vendorProfile->bank_name }}</p>
            </div>
            <div>
                <label class="text-gray-500">Account Holder</label>
                <p class="font-medium">{{ $vendorProfile->account_holder_name }}</p>
            </div>
            <div>
                <label class="text-gray-500">Account Number</label>
                <p class="font-medium">{{ $vendorProfile->account_number }}</p>
            </div>
            <div>
                <label class="text-gray-500">IFSC Code</label>
                <p class="font-medium">{{ $vendorProfile->ifsc_code }}</p>
            </div>
        </div>
    </div>

    {{-- ADDRESS --}}
    <div class="bg-white rounded-xl shadow p-6 mt-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Registered Business Address</h2>
            <!-- <button
                @click="showModal = true; modalType = 'address'"
                class="text-blue-600 text-sm"
            >Edit</button> -->
        </div>
        <div class="grid md:grid-cols-4 gap-4 text-sm">
            <div class="md:col-span-4">
                <label class="text-gray-500">Business Address</label>
                <p class="font-medium">{{ $vendorProfile->registered_address }}</p>
            </div>
            <div>
                <label class="text-gray-500">Pincode</label>
                <p class="font-medium">{{ $vendorProfile->pincode }}</p>
            </div>
            <div>
                <label class="text-gray-500">City</label>
                <p class="font-medium">{{ $vendorProfile->city }}</p>
            </div>
            <div>
                <label class="text-gray-500">State</label>
                <p class="font-medium">{{ $vendorProfile->state }}</p>
            </div>
            <div>
                <label class="text-gray-500">Country</label>
                <p class="font-medium">{{ $user->country }}</p>
            </div>
        </div>
    </div>

</div>
@endsection

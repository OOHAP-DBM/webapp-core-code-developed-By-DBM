@extends('layouts.admin')

@section('title', 'Vendor Details')

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
        <div class="bg-white p-4 rounded-xl">
            <p class="text-sm text-gray-500">Total Earnings</p>
            <p class="text-xl font-semibold">₹0</p>
        </div>
        <div class="bg-white p-4 rounded-xl">
            <p class="text-sm text-gray-500">Total Hoardings</p>
            <p class="text-xl font-semibold">{{ $vendorProfile->total_hoardings ?? 0 }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl">
            <p class="text-sm text-gray-500">Ongoing Orders</p>
            <p class="text-xl font-semibold">0</p>
        </div>
        <div class="bg-white p-4 rounded-xl">
            <p class="text-sm text-gray-500">Commission</p>
            <p class="text-xl font-semibold">{{ $vendorProfile->commission_percentage ?? 0 }}%</p>
        </div>
    </div>

    {{-- PERSONAL INFO --}}
    <div class="bg-white rounded-xl p-6">
        <h3 class="text-lg font-semibold mb-4">Personal Info</h3>
        <div class="grid grid-cols-3 gap-6">
            <div>
                <label class="text-sm text-gray-500">Full Name</label>
                <p class="font-medium">{{ $user->name }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">Email Address</label>
                <p class="font-medium flex items-center gap-2">
                    {{ $user->email }}
                    @if($user->email_verified_at)
                        <span class="text-green-500">✔</span>
                    @endif
                </p>
            </div>
            <div>
                <label class="text-sm text-gray-500">Mobile Number</label>
                <p class="font-medium flex items-center gap-2">
                    {{ $user->phone ?? '-' }}
                    @if($user->phone_verified_at)
                        <span class="text-green-500">✔</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- REGISTERED BUSINESS --}}
    <div class="bg-white rounded-xl p-6">
        <h3 class="text-lg font-semibold mb-4">Registered Business Address</h3>
        <div class="grid grid-cols-4 gap-6">
            <div>
                <label class="text-sm text-gray-500">GSTIN</label>
                <p class="font-medium">{{ $vendorProfile->gstin ?? '-' }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">Business Name</label>
                <p class="font-medium">{{ $vendorProfile->company_name ?? '-' }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">Business Type</label>
                <p class="font-medium">{{ $vendorProfile->company_type ?? '-' }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">PAN Number</label>
                <p class="font-medium">{{ $vendorProfile->pan ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- BANK DETAILS --}}
    <div class="bg-white rounded-xl p-6">
        <h3 class="text-lg font-semibold mb-4">Bank Details</h3>
        <div class="grid grid-cols-4 gap-6">
            <div>
                <label class="text-sm text-gray-500">Bank Name</label>
                <p class="font-medium">{{ $vendorProfile->bank_name ?? '-' }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">Account Holder</label>
                <p class="font-medium">{{ $vendorProfile->account_holder_name ?? '-' }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">Account Number</label>
                <p class="font-medium">{{ $vendorProfile->account_number ?? '-' }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">IFSC Code</label>
                <p class="font-medium">{{ $vendorProfile->ifsc_code ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- ADDRESS --}}
    <div class="bg-white rounded-xl p-6">
        <h3 class="text-lg font-semibold mb-4">Address</h3>
        <div class="grid grid-cols-5 gap-6">
            <div class="col-span-2">
                <label class="text-sm text-gray-500">Business Address</label>
                <p class="font-medium">{{ $vendorProfile->registered_address ?? '-' }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">City</label>
                <p class="font-medium">{{ $vendorProfile->city ?? '-' }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">State</label>
                <p class="font-medium">{{ $vendorProfile->state ?? '-' }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500">Country</label>
                <p class="font-medium">India</p>
            </div>
        </div>
    </div>

</div>
@endsection

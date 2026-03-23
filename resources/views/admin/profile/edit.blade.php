@extends('layouts.admin')

@section('content')
<div class="w-full bg-white mx-auto p-6 rounded-lg shadow-sm">

    {{-- ── TOP HEADER: Name + Profile Status ── --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
        <div class="flex items-center">
            <div>
                <div class="text-xl font-bold text-gray-900">{{ $user->name }}</div>
                {{-- <div class="text-gray-500 text-sm">{{ $user->email }}</div> --}}
            </div>
        </div>

        {{-- Profile Status --}}
        <div class="mt-4 md:mt-0 min-w-[220px]">
            <div class="flex items-center justify-between mb-1">
                <span class="text-sm text-gray-600">Profile Status</span>
                <span class="text-sm font-semibold text-gray-800">{{ $profileCompletion ?? 15 }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                    class="bg-green-500 h-2 rounded-full transition-all duration-500"
                    style="width: {{ $profileCompletion ?? 15 }}%"
                ></div>
            </div>
        </div>
    </div>

    {{-- ── STATS TABS: Total Earnings | Total Hoardings | Ongoing Orders ── --}}
    <div class="flex gap-3 border-gray-200 mb-6">
        <button class="px-6 py-3 rounded text-sm font-medium text-gray-700 border border-gray-200">
            Total Earnings
        </button>
        <button class="px-6 py-3 rounded text-sm font-medium text-gray-700 border border-gray-200">
            Total Hoardings
        </button>
        <button class="px-6 py-3 rounded text-sm font-medium text-gray-700 border border-gray-200">
            Ongoing Orders
        </button>
    </div>

  {{-- ── UPLOAD PROFILE IMAGE ── --}}
    <div class="bg-white shadow-sm rounded-lg mb-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Upload Profile Image</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            @csrf
            <label for="avatarInput" class="flex items-center border border-gray-300 rounded overflow-hidden w-full cursor-pointer hover:bg-gray-50 transition-colors">
                <span class="flex-shrink-0 bg-gray-100 border-r border-gray-300 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-200 transition-colors">
                    Browse
                </span>
                <span id="fileNameDisplay" class="px-4 py-2.5 text-sm text-gray-400 flex-1 truncate">Choose file</span>
                <input type="file" name="avatar" accept="image/*" class="hidden" id="avatarInput" onchange="updateFileName(this)">
            </label>
            @error('avatar')
                <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </form>
    </div>

    {{-- ── PERSONAL INFO ── --}}
    <div class="bg-white shadow-sm rounded-lg mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-900">Personal Info</h2>
            <button
                onclick="toggleEdit('personalForm', this)"
                class="text-blue-600 text-sm font-medium hover:underline focus:outline-none"
            >Edit</button>
        </div>
        <form id="personalForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Full Name</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ $user->name }}"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50 focus:outline-none focus:ring-1 focus:ring-green-500"
                        disabled
                    >
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ $user->email }}"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50 focus:outline-none focus:ring-1 focus:ring-green-500"
                        disabled
                    >
                </div>
            </div>
            <div class="flex items-center justify-between mt-4">
                <div id="personalSaveBtn" class="hidden">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 text-sm rounded hover:bg-green-700 transition-colors">
                        Save Changes
                    </button>
                </div>
                <div class="ml-auto">
                    <a href="" class="text-blue-600 text-xs font-medium hover:underline uppercase tracking-wide">
                        Change Password
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── BUSINESS DETAILS ── --}}
    <div class="bg-white shadow-sm rounded-lg mb-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Business Details</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">GSTIN Number</label>
                    <input
                        type="text"
                        name="gstin"
                        value="{{ $user->vendorProfile->gstin ?? '' }}"
                        placeholder="Enter GSTIN Number"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500"
                    >
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Business Name</label>
                    <input
                        type="text"
                        name="company_name"
                        value="{{ $user->vendorProfile->company_name ?? '' }}"
                        placeholder="Enter Business Name"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500"
                    >
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Business Type</label>
                    <select
                        name="business_type"
                        class="form-select block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500 bg-white"
                    >
                        <option value="">Business Type</option>
                        <option value="proprietorship" {{ ($user->vendorProfile->business_type ?? '') === 'proprietorship' ? 'selected' : '' }}>Proprietorship</option>
                        <option value="partnership" {{ ($user->vendorProfile->business_type ?? '') === 'partnership' ? 'selected' : '' }}>Partnership</option>
                        <option value="pvt_ltd" {{ ($user->vendorProfile->business_type ?? '') === 'pvt_ltd' ? 'selected' : '' }}>Pvt. Ltd.</option>
                        <option value="llp" {{ ($user->vendorProfile->business_type ?? '') === 'llp' ? 'selected' : '' }}>LLP</option>
                        <option value="public_ltd" {{ ($user->vendorProfile->business_type ?? '') === 'public_ltd' ? 'selected' : '' }}>Public Ltd.</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Upload PAN</label>
                    <label class="flex items-center justify-center w-full bg-black text-white px-4 py-2 rounded cursor-pointer hover:bg-gray-800 transition-colors text-sm font-medium">
                        Upload
                        <input type="file" name="pan_document" accept="image/*,.pdf" class="hidden">
                    </label>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit" class="bg-green-600 text-white px-5 py-2 text-sm rounded hover:bg-green-700 transition-colors">
                    Save Business Details
                </button>
            </div>
        </form>
    </div>

    {{-- ── BANK DETAILS ── --}}
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Bank Details</h2>
        <form method="POST" action="">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Bank Name</label>
                    <select
                        name="bank_name"
                        class="form-select block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500 bg-white"
                    >
                        <option value="">Select Bank</option>
                        @foreach($banks ?? [] as $bank)
                            <option value="{{ $bank }}" {{ ($user->vendorProfile->bank_name ?? '') === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                        @endforeach
                        {{-- Fallback if $banks not passed --}}
                        @if(empty($banks))
                            @foreach(['State Bank of India','HDFC Bank','ICICI Bank','Axis Bank','Kotak Mahindra Bank','Punjab National Bank','Bank of Baroda','Canara Bank','Union Bank of India','IndusInd Bank','Yes Bank','IDFC First Bank'] as $bank)
                                <option value="{{ $bank }}" {{ ($user->vendorProfile->bank_name ?? '') === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Account Holder Name</label>
                    <input
                        type="text"
                        name="account_holder_name"
                        value="{{ $user->vendorProfile->account_holder_name ?? '' }}"
                        placeholder="Enter Name"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500"
                    >
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Account Number</label>
                    <input
                        type="text"
                        name="account_number"
                        value="{{ $user->vendorProfile->account_number ?? '' }}"
                        placeholder="Enter Account Number"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500"
                    >
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">IFSC Code</label>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            name="ifsc_code"
                            value="{{ $user->vendorProfile->ifsc_code ?? '' }}"
                            placeholder="Enter IFSC"
                            class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500"
                        >
                        <a
                            href="https://www.bankbazaar.com/ifsc-code.html"
                            target="_blank"
                            class="text-blue-600 text-xs font-medium hover:underline whitespace-nowrap"
                        >Find IFSC</a>
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit" class="bg-green-600 text-white px-5 py-2 text-sm rounded hover:bg-green-700 transition-colors">
                    Save Bank Details
                </button>
            </div>
        </form>
    </div>

    {{-- ── REGISTERED BUSINESS ADDRESS ── --}}
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Registered Business Address</h2>
        <form method="POST" action="">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Country</label>
                    <select
                        name="country"
                        class="form-select block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500 bg-white"
                    >
                        <option value="">Select Country</option>
                        <option value="India" {{ ($user->vendorProfile->country ?? '') === 'India' ? 'selected' : '' }}>India</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Pincode</label>
                    <input
                        type="text"
                        name="pincode"
                        value="{{ $user->vendorProfile->pincode ?? '' }}"
                        placeholder="Enter Pincode"
                        maxlength="6"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500"
                        oninput="this.value=this.value.replace(/\D/g,'')"
                    >
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">State</label>
                    <input
                        type="text"
                        name="state"
                        value="{{ $user->vendorProfile->state ?? '' }}"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500"
                    >
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">City</label>
                    <input
                        type="text"
                        name="city"
                        value="{{ $user->vendorProfile->city ?? '' }}"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500"
                    >
                </div>
                <div class="md:col-span-4">
                    <label class="block text-sm text-gray-600 mb-1">Business Address</label>
                    <input
                        type="text"
                        name="business_address"
                        value="{{ $user->vendorProfile->business_address ?? '' }}"
                        placeholder="Enter Business Address"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500"
                    >
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit" class="bg-green-600 text-white px-5 py-2 text-sm rounded hover:bg-green-700 transition-colors">
                    Save Address
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
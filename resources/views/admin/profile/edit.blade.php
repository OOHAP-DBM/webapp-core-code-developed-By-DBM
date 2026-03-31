@extends('layouts.admin')

@section('content')
<div class="w-full mx-auto space-y-6">

    {{-- ── BOX 1: Name + Profile Status + Stats + Upload Profile Image ── --}}
    <div class="bg-white shadow-sm rounded-lg p-6">

        {{-- Name + Profile Status --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div class="text-xl font-bold text-gray-900">{{ $user->name ?? '' }}</div>
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

        {{-- Stats Tabs --}}
        <div class="flex flex-wrap gap-3 mb-6">
            <button class="w-full sm:w-auto px-6 py-3 rounded text-sm font-medium text-gray-700 border border-gray-200">
                <p class="font-bold text-lg">{{ $totalEarnings ?? 0 }}</p>
                Total Earnings
            </button>
            <button class="w-full sm:w-auto px-6 py-3 rounded text-sm font-medium text-gray-700 border border-gray-200">
                <p class="font-bold text-lg">{{ $totalHoardings ?? 0 }}</p>
                Total Hoardings
            </button>
            <button class="w-full sm:w-auto px-6 py-3 rounded text-sm font-medium text-gray-700 border border-gray-200">
                <p class="font-bold text-lg">{{ $totalActiveOrders ?? 0 }}</p>
                Ongoing Orders
            </button>
        </div>

        <h2 class="text-base font-semibold text-gray-900 mb-4">Upload Profile Image</h2>
            <form id="avatarUploadForm" method="POST" action="{{ route('admin.profile.avatar.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <label for="avatarInput" class="flex items-center border border-gray-300 rounded overflow-hidden w-full cursor-pointer hover:bg-gray-50 transition-colors">
                    <span class="flex-shrink-0 bg-gray-100 border-r border-gray-300 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-200 transition-colors">
                        Browse
                    </span>
                    <span id="fileNameDisplay" class="px-4 py-2.5 text-sm text-gray-400 flex-1 truncate">
                        {{ !empty($user->avatar ?? null) ? basename($user->avatar) : 'Choose file' }}
                    </span>
                    <input
                        type="file"
                        name="avatar"
                        accept="image/*"
                        class="hidden"
                        id="avatarInput"
                        onchange="uploadAvatar(this)">
                </label>

             @if(!empty($user->avatar ?? null))
                    <div class="flex items-center gap-3 my-3">
                        <div class="w-20 h-20 rounded-full overflow-hidden border border-gray-200">
                            <img
                                src="{{ route('admin.profile.avatar.view') }}?t={{ time() }}"
                                alt="Profile Image"
                                class="w-full h-full object-cover">
                        </div>

                        <button
                            type="button"
                            onclick="removeAvatar()"
                            class="px-4 py-2 text-sm font-medium text-red-600 rounded hover:bg-red-50 cursor-pointer">
                            Remove
                        </button>
                    </div>
                @endif
            </form>
        </div>

    {{-- ── BOX 2: Personal Info ── --}}
    <div class="bg-white shadow-sm rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-900">Personal Info</h2>
            <button
                type="button"
                onclick="openModal('personalModal')"
                class="text-blue-600 text-sm font-medium hover:underline focus:outline-none"
            >Edit</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Full Name</label>
                <input type="text" value="{{ $user->name ?? '' }}" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Email Address</label>
                <input type="email" value="{{ $user->email ?? '' }}" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
        </div>
        <div class="flex justify-end mt-4">
            <button type="button" onclick="openModal('changePasswordModal')" class="text-blue-600 text-xs font-medium hover:underline uppercase tracking-wide">
                Change Password
            </button>
        </div>
    </div>

    {{-- ── BOX 3: Business Details ── --}}
    <div class="bg-white shadow-sm rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-900">Business Details</h2>
            <button
                type="button"
                onclick="openModal('businessModal')"
                class="text-blue-600 text-sm font-medium hover:underline focus:outline-none"
            >Edit</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-sm text-gray-600 mb-1">GSTIN Number</label>
                <input type="text" value="{{ $profile->gstin ?? '' }}" placeholder="Enter GSTIN Number" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Business Name</label>
                <input type="text" value="{{ $profile->company_name ?? '' }}" placeholder="Enter Business Name" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Business Type</label>
                <input type="text" value="{{ ucfirst($profile->business_type ?? '') }}" placeholder="Business Type" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Upload PAN</label>
                <div class="relative">
                    <input type="text" 
                        value="{{ !empty($profile->pan_document ?? null) ? basename($profile->pan_document) : '' }}" 
                        placeholder="No file uploaded" 
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50 pr-9" 
                        disabled>
                        <button
                        type="button"
                        onclick="openModal('panModal')"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── BOX 4: Bank Details ── --}}
    <div class="bg-white shadow-sm rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-900">Bank Details</h2>
            <button
                type="button"
                onclick="openModal('bankModal')"
                class="text-blue-600 text-sm font-medium hover:underline focus:outline-none"
            >Edit</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Bank Name</label>
                <input type="text" value="{{ $profile->bank_name ?? '' }}" placeholder="Select Bank" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Account Holder Name</label>
                <input type="text" value="{{ $profile->account_holder_name ?? '' }}" placeholder="Enter Name" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Account Number</label>
                <input type="text" value="{{ $profile->account_number ?? '' }}" placeholder="Enter Account Number" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">IFSC Code</label>
                <input type="text" value="{{ $profile->ifsc_code ?? '' }}" placeholder="Enter IFSC" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
        </div>
    </div>

    {{-- ── BOX 5: Registered Business Address ── --}}
    <div class="bg-white shadow-sm rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-900">Registered Business Address</h2>
            <button
                type="button"
                onclick="openModal('addressModal')"
                class="text-blue-600 text-sm font-medium hover:underline focus:outline-none"
            >Edit</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Country</label>
                <input type="text" value="{{ $profile->country ?? '' }}" placeholder="Select Country" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">State</label>
                <input type="text" value="{{ $profile->state ?? '' }}" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">City</label>
                <input type="text" value="{{ $profile->city ?? '' }}" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Pincode</label>
                <input type="text" value="{{ $profile->pincode ?? '' }}" placeholder="Enter Pincode" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
            <div class="md:col-span-4">
                <label class="block text-sm text-gray-600 mb-1">Business Address</label>
                <input type="text" value="{{ $profile->address ?? '' }}" placeholder="Enter Business Address" class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50" disabled>
            </div>
        </div>
    </div>

</div>

{{-- ── MODALS ── --}}
@include('admin.profile.modals.personal')
@include('admin.profile.modals.change-password')
@include('admin.profile.modals.business')
@include('admin.profile.modals.pan')
@include('admin.profile.modals.bank')
@include('admin.profile.modals.address')

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('hidden');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('hidden');
    }

    // Overlay click se close
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.closest('[id$="Modal"]')?.classList.add('hidden');
        }
    });

    function updateFileName(input) {
        const display = document.getElementById('fileNameDisplay');
        if (display) display.textContent = input.files[0]?.name || 'Choose file';
    }
</script>
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('hidden');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('hidden');
    }

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.closest('[id$="Modal"]')?.classList.add('hidden');
        }
    });

    function uploadAvatar(input) {
        const file = input.files[0];
        const display = document.getElementById('fileNameDisplay');

        if (display) {
            display.textContent = file ? file.name : 'Choose file';
        }

        if (file) {
            document.getElementById('avatarUploadForm').submit();
        }
    }
</script>
<script>
    function uploadAvatar(input) {
        const file = input.files[0];
        const display = document.getElementById('fileNameDisplay');

        if (display) {
            display.textContent = file ? file.name : 'Choose file';
        }

        if (file) {
            document.getElementById('avatarUploadForm').submit();
        }
    }

    function removeAvatar() {
        Swal.fire({
            title: 'Remove avatar?',
            text: 'This will remove your profile image.',
            icon: 'warning',
            width: 360,
            padding: '1rem',
            showCancelButton: true,
            confirmButtonText: 'remove',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            customClass: {
                confirmButton: 'swal-confirm-btn',
                cancelButton: 'swal-cancel-btn'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.profile.avatar.remove') }}";
            }
        });
    }
</script>

<style>
    .swal-confirm-btn,
    .swal-cancel-btn {
        padding: 8px 14px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        width: 120px;
        min-width: 120px;
        text-align: center;
        white-space: nowrap;
    }

    .swal-confirm-btn {
        background: #ef4444;
        color: #fff;
    }

    .swal-confirm-btn:hover {
        background: #dc2626;
    }

    .swal-cancel-btn {
        background: #e5e7eb;
        color: #374151;
        margin-right: 8px;
    }

    .swal-cancel-btn:hover {
        background: #d1d5db;
    }

    @media (max-width: 640px) {
        .swal2-popup {
            width: calc(100% - 32px) !important;
            margin-left: 16px !important;
            margin-right: 16px !important;
        }

        .swal-confirm-btn,
        .swal-cancel-btn {
            width: 110px;
            min-width: 110px;
            font-size: 13px;
            padding: 8px 10px;
        }
    }
</style>
@endsection
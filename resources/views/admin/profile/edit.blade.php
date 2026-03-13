@extends('layouts.admin')

@section('content')
<div class="w-full mx-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
        <div class="flex items-center gap-4">
            @if($user->avatar)
                <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . ltrim($user->avatar, '/')) }}" alt="Profile Image" class="w-20 h-20 rounded-full object-cover border">
            @else
                <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            @endif
            <div>
                <div class="text-xl font-bold">{{ $user->name }}</div>
                <div class="text-gray-500">{{ $user->email }}</div>
                <div class="mt-2">
                    <span class="inline-block bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">Profile Status: 100%</span>
                </div>
            </div>
        </div>
        <div class="mt-4 md:mt-0 flex flex-col gap-2">
            <form method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                <input type="file" class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" name="avatar">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Upload</button>
            </form>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Personal Info</h2>
            <a href="#" class="text-blue-600 text-sm font-medium hover:underline">Edit</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700">Full Name</label>
                <input type="text" class="form-input mt-1 block w-full" value="{{ $user->name }}" disabled>
            </div>
            <div>
                <label class="block text-gray-700">Email Address</label>
                <input type="email" class="form-input mt-1 block w-full" value="{{ $user->email }}" disabled>
            </div>
        </div>
        <div class="flex justify-end mt-2">
            <a href="#" class="text-blue-600 text-xs font-medium hover:underline">Change Password</a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Business Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-gray-700">GSTIN Number</label>
                <input type="text" class="form-input mt-1 block w-full" value="{{ $user->gstin ?? '' }}" disabled>
            </div>
            <div>
                <label class="block text-gray-700">Business Name</label>
                <input type="text" class="form-input mt-1 block w-full" value="{{ $user->business_name ?? '' }}" disabled>
            </div>
            <div>
                <label class="block text-gray-700">Business Type</label>
                <input type="text" class="form-input mt-1 block w-full" value="{{ $user->business_type ?? '' }}" disabled>
            </div>
            <div class="flex items-end">
                <button class="bg-black text-white px-4 py-2 rounded w-full">Upload MSME</button>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Bank Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-gray-700">Bank Name</label>
                <input type="text" class="form-input mt-1 block w-full" value="{{ $user->bank_name ?? '' }}" disabled>
            </div>
            <div>
                <label class="block text-gray-700">Account Holder Name</label>
                <input type="text" class="form-input mt-1 block w-full" value="{{ $user->account_holder_name ?? '' }}" disabled>
            </div>
            <div>
                <label class="block text-gray-700">Account Number</label>
                <input type="text" class="form-input mt-1 block w-full" value="{{ $user->account_number ?? '' }}" disabled>
            </div>
            <div>
                <label class="block text-gray-700">IFSC Code</label>
                <div class="flex gap-2">
                    <input type="text" class="form-input mt-1 block w-full" value="{{ $user->ifsc_code ?? '' }}" disabled>
                    <a href="https://www.bankbazaar.com/ifsc-code.html" target="_blank" class="text-blue-600 text-xs font-medium hover:underline whitespace-nowrap self-center">Find IFSC</a>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Registered Business Address</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-gray-700">Country</label>
                <input type="text" class="form-input mt-1 block w-full" value="{{ $user->country ?? '' }}" disabled>
            </div>
            <div>
                <label class="block text-gray-700">Pincode</label>
                <input type="text" class="form-input mt-1 block w-full" value="{{ $user->pincode ?? '' }}" disabled>
            </div>
            <div>
                <label class="block text-gray-700">State</label>
                <input type="text" class="form-input mt-1 block w-full" value="{{ $user->state ?? '' }}" disabled>
            </div>
            <div>
                <label class="block text-gray-700">City</label>
                <input type="text" class="form-input mt-1 block w-full" value="{{ $user->city ?? '' }}" disabled>
            </div>
            <div class="md:col-span-4">
                <label class="block text-gray-700">Business Address</label>
                <input type="text" class="form-input mt-1 block w-full" value="{{ $user->business_address ?? '' }}" disabled>
            </div>
        </div>
    </div>
</div>
@endsection

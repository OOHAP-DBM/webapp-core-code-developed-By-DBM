@extends('layouts.admin')

@section('title', 'Customer Profile')

@section('content')
<div class="space-y-8">

    {{-- ================= GENERAL DETAILS ================= --}}
    <div class="bg-white rounded-xl p-6 shadow">

        <div class="flex justify-between items-start">
            <h2 class="text-lg font-semibold">General Details</h2>

            <button class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm">
                Delete Account
            </button>
        </div>

        <div class="flex gap-6 mt-6">

            {{-- Avatar --}}
            <img
                src="{{ $user->avatar ?? asset('images/avatar.png') }}"
                class="w-24 h-24 rounded-full object-cover"
                alt="Avatar"
            >

            {{-- Stats --}}
            <div class="flex gap-10 items-center">
                <div>
                    <p class="text-xl font-bold">{{ $stats['total'] }}</p>
                    <p class="text-sm text-gray-500">Total Bookings</p>
                </div>
                <div>
                    <p class="text-xl font-bold">{{ $stats['active'] }}</p>
                    <p class="text-sm text-gray-500">Active Bookings</p>
                </div>
                <div>
                    <p class="text-xl font-bold">{{ $stats['cancelled'] }}</p>
                    <p class="text-sm text-gray-500">Cancelled Bookings</p>
                </div>
            </div>
        </div>

        {{-- FORM --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">

            <div>
                <label class="text-sm">Full Name</label>
                <input type="text" value="{{ $user->name }}" class="w-full border rounded px-3 py-2" disabled>
            </div>

            <div>
                <label class="text-sm">Mobile Number</label>
                <input type="text" value="{{ $user->phone }}" class="w-full border rounded px-3 py-2" disabled>
            </div>

            <div>
                <label class="text-sm">Email</label>
                <input type="email" value="{{ $user->email }}" class="w-full border rounded px-3 py-2" disabled>
            </div>

            <div>
                <label class="text-sm">Street Address</label>
                <input type="text" value="{{ $user->address }}" class="w-full border rounded px-3 py-2" disabled>
            </div>

            <div>
                <label class="text-sm">Pincode</label>
                <input type="text" value="{{ $user->pincode }}" class="w-full border rounded px-3 py-2" disabled>
            </div>

            <div>
                <label class="text-sm">City</label>
                <input type="text" value="{{ $user->city }}" class="w-full border rounded px-3 py-2" disabled>
            </div>

            <div>
                <label class="text-sm">State</label>
                <input type="text" value="{{ $user->state }}" class="w-full border rounded px-3 py-2" disabled>
            </div>

            <div>
                <label class="text-sm">Country</label>
                <input type="text" value="{{ $user->country ?? 'India' }}" class="w-full border rounded px-3 py-2" disabled>
            </div>

            <div>
                <label class="text-sm">Password</label>
                <input type="password" value="********" class="w-full border rounded px-3 py-2" disabled>
            </div>

        </div>
    </div>

    {{-- ================= BOOKINGS SECTION ================= --}}
    <div class="bg-white rounded-xl p-6 shadow">

        <h3 class="font-semibold mb-4">Customer Bookings Details</h3>

        {{-- Tabs --}}
        <div class="flex gap-6 text-sm border-b pb-3">
            <span class="font-semibold">
                Total Bookings ({{ $stats['total'] }})
            </span>
            <span>Active ({{ $stats['active'] }})</span>
            <span>Cancelled ({{ $stats['cancelled'] }})</span>
        </div>

        {{-- Booking Cards --}}
        <div class="mt-6 space-y-4">
            @forelse($bookings as $booking)
                <div class="border rounded-xl p-4 flex justify-between items-center">

                    <div>
                        <p class="font-semibold">
                            {{ $booking->hoarding->title ?? 'Hoarding' }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ $booking->start_date }} – {{ $booking->end_date }}
                        </p>
                    </div>

                    <div class="text-right">
                        <span class="text-sm font-semibold
                            {{ $booking->status === 'cancelled' ? 'text-red-500' : 'text-green-600' }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                        <p class="text-sm text-gray-500">
                            Paid: ₹{{ number_format($booking->paid_amount ?? 0) }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-sm">No bookings found.</p>
            @endforelse
        </div>

    </div>

</div>
@endsection

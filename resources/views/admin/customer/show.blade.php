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
            <div style="width:56px; height:56px; min-width:56px; min-height:56px; max-width:56px; max-height:56px; border-radius:50%; overflow:hidden; border:1px solid #d1d5db; flex-shrink:0; display:inline-block;">
                @if($user->avatar)
                    <img
                        src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . ltrim($user->avatar, '/')) }}"
                        alt="Profile Image"
                        class="w-full h-full object-cover block"
                    >
                @else
                    {{-- Default User Icon --}}
                    <svg
                        class="w-14 h-14 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804
                            M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>
                @endif
            </div>

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
            @php
                $fields = [
                    'Full Name' => $user->name,
                    'Mobile Number' => $user->phone,
                    'Email' => $user->email,
                    'Street Address' => $user->address,
                    'Pincode' => $user->pincode,
                    'City' => $user->city,
                    'State' => $user->state,
                    'Country' => $user->country ?? 'India',
                    'Password' => '********',
                ];
            @endphp
            @foreach($fields as $label => $value)
                <div>
                    <label class="text-sm">{{ $label }}</label>
                    <input type="text" value="{{ $value }}" class="w-full border rounded px-3 py-2" disabled>
                </div>
            @endforeach
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

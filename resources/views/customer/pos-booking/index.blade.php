@extends('layouts.customer')

@section('title', 'POS Booking')

@section('content')
<div
    class="bg-white shadow w-full px-4 sm:px-6 py-6"
    x-data="{
        openFilter: false,
        dateFilter: '{{ request('date_filter', 'all') }}'
    }"
>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold">My Bookings ({{ $stats['total_bookings'] ?? 0 }})</h1>
            <p class="text-gray-500 text-xs sm:text-sm">View and manage booked hoardings</p>
        </div>

        <div class="flex flex-row gap-2 items-center w-full sm:w-auto">
            {{-- Search --}}
            <form method="GET" class="flex gap-2 w-full sm:w-auto">
                @foreach(request()->except(['search']) as $key => $val)
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endforeach
                <div class="relative w-full sm:w-64">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by Booking ID (Invoice No.)"
                        class="w-full border border-gray-300 pl-10 pr-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500"
                        inputmode="search"
                    />
                    <span class="absolute left-3 top-2.5 text-gray-400">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24">
                            <path d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
                <!-- Search button removed: search will trigger on Enter key in input -->
            </form>

            {{-- Filter Button --}}
            <button
                @click="openFilter = true"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold border border-gray-300 hover:bg-gray-50 whitespace-nowrap cursor-pointer"
            >
                Filter
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow border border-gray-200">
        <table class="min-w-full text-xs sm:text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Sn #</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Booking id</th>
                    <!-- <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Booking Type</th> -->
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Duration</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Start Date</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">End Date</th>
                    <th class="px-3 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">Total Amount</th>
                    <th class="px-3 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">Paid Amount</th>
                    <th class="px-3 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">Balance</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Payment Status</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Booking Status</th>
                    <th class="px-3 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $i => $booking)
                @php
                    $balance = $booking->total_amount - $booking->paid_amount;
                @endphp
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-3 py-2 text-gray-500">{{ $bookings->firstItem() + $i }}</td>

                    <td class="px-3 py-2">
                        {{$booking->id}}
                    </td>
<!-- 
                    <td class="px-3 py-2">
                        <span class="uppercase text-xs font-semibold px-2 py-0.5 rounded
                            {{ $booking->booking_type === 'ooh' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ strtoupper($booking->booking_type ?? '-') }}
                        </span>
                    </td> -->

                    <td class="px-3 py-2">
                        {{ $booking->duration_days ?? '-' }} days
                    </td>

                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('d M, Y') : '-' }}
                    </td>

                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ $booking->end_date ? \Carbon\Carbon::parse($booking->end_date)->format('d M, Y') : '-' }}
                    </td>

                    <td class="px-3 py-2 text-right font-semibold text-gray-800">
                        ₹{{ number_format($booking->total_amount, 2) }}
                    </td>

                    <td class="px-3 py-2 text-right font-semibold text-emerald-600">
                        ₹{{ number_format($booking->paid_amount, 2) }}
                    </td>

                    <td class="px-3 py-2 text-right font-semibold {{ $balance > 0 ? 'text-red-500' : 'text-gray-400' }}">
                        {{ $balance > 0 ? '₹' . number_format($balance, 2) : '-' }}
                    </td>

                    <td class="px-3 py-2">
                        @php
                            $payColors = [
                                'paid'    => 'bg-emerald-100 text-emerald-700',
                                'unpaid'  => 'bg-red-100 text-red-600',
                                'partial' => 'bg-yellow-100 text-yellow-700',
                            ];
                            $payClass = $payColors[$booking->payment_status] ?? 'bg-gray-100 text-gray-600';
                        @endphp
                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $payClass }}">
                            {{ ucfirst($booking->payment_status ?? '-') }}
                        </span>
                    </td>

                    <td class="px-3 py-2">
                        @php
                            $statusColors = [
                                'confirmed'       => 'bg-emerald-100 text-emerald-700',
                                'pending_payment' => 'bg-yellow-100 text-yellow-700',
                                'cancelled'       => 'bg-red-100 text-red-600',
                                'hold'            => 'bg-orange-100 text-orange-600',
                            ];
                            $statusClass = $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-600';
                        @endphp
                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $statusClass }}">
                            {{ ucwords(str_replace('_', ' ', $booking->status ?? '-')) }}
                        </span>
                    </td>

                    <td class="px-3 py-2 text-center">
                        <a href="{{ route('customer.pos.booking.show', $booking->id) }}"
                            class="bg-emerald-600 text-white px-3 py-1 rounded hover:bg-emerald-700 text-xs font-semibold">
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center py-10 text-gray-400">
                        <svg class="mx-auto mb-2 w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24">
                            <path d="M9 17H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M14 21l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        No bookings found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="flex flex-col sm:flex-row items-center justify-between px-4 py-3 bg-gray-50 border-t border-gray-200">
            <div class="text-xs text-gray-500 mb-2 sm:mb-0">
                Showing
                {{ $bookings->firstItem() ?? 0 }} - {{ $bookings->lastItem() ?? 0 }}
                of {{ $bookings->total() }} bookings
            </div>
            <div>
                {{ $bookings->links('pagination.vendor-compact') }}
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════
         FILTER MODAL  (same design as your enquiry modal)
         ════════════════════════════════════════════ --}}
    <div
        x-show="openFilter"
        x-cloak
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 md:px-0"
    >
        <div
            @click.away="openFilter = false"
            class="bg-white w-full max-w-2xl rounded shadow-lg relative"
        >
            {{-- Modal Header --}}
            <div class="flex items-center justify-between h-10 bg-green-100 px-4 rounded-t">
                <span></span>
                <button
                    @click="openFilter = false"
                    class="text-gray-800 hover:text-black text-xl cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <form method="GET" class="p-6 space-y-6">

                {{-- Preserve search if present --}}
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif

                <h2 class="inline-block text-lg font-semibold text-gray-900 border-b border-gray-700 pb-1">
                    Filter
                </h2>

                {{-- Date Filter --}}
                <!-- <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Booking by date</h3>
                    <div class="flex flex-wrap items-center gap-6 text-sm text-gray-700">
                        @foreach(['all' => 'All', 'last_week' => 'Last week', 'last_month' => 'Last month', 'last_year' => 'Last year', 'custom' => 'Custom Date'] as $val => $label)
                        <label class="flex items-center gap-2">
                            <input type="radio" name="date_filter" value="{{ $val }}" x-model="dateFilter">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>

                    {{-- Custom Date Range --}}
                    <div x-show="dateFilter === 'custom'" x-transition class="mt-4 flex gap-4">
                        <input
                            type="date"
                            name="from_date"
                            value="{{ request('from_date') }}"
                            class="px-3 py-2 border border-gray-300 text-sm w-full"
                        >
                        <input
                            type="date"
                            name="to_date"
                            value="{{ request('to_date') }}"
                            class="px-3 py-2 border border-gray-300 text-sm w-full"
                        >
                    </div>
                </div> -->

                {{-- Status Filter --}}
                <!-- <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Booking Status</h3>
                    <div class="flex flex-wrap items-center gap-6 text-sm text-gray-700">
                        @foreach($statusOptions as $val => $label)
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="status"
                                value="{{ $val }}"
                                {{ request('status') == $val ? 'checked' : '' }}
                            >
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div> -->

                {{-- Payment Status Filter --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Payment Status</h3>
                    <div class="flex flex-wrap items-center gap-6 text-sm text-gray-700">
                        @foreach($paymentStatusOptions as $val => $label)
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="payment_status"
                                value="{{ $val }}"
                                {{ request('payment_status') == $val ? 'checked' : '' }}
                            >
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-6 pt-4">
                    <a href="{{ route('customer.pos.booking') }}"
                        class="text-sm text-black font-semibold hover:underline">
                        Reset
                    </a>
                    <button
                        type="submit"
                        class="px-6 py-2 bg-green-800 text-white text-sm font-semibold hover:bg-green-900 cursor-pointer"
                    >
                        Apply Filter
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
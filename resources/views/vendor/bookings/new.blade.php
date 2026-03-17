@extends('layouts.vendor')

@section('page-title', 'New Bookings')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    <div class="bg-white rounded-md shadow">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 px-4 sm:px-6 py-4 bg-primary rounded-t-xl">
            <h4 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 flex items-center gap-2">
                New Bookings
            </h4>
            <a href="{{ route('vendor.bookings.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center border border-gray-300 bg-white/80 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-white transition">
                ← All Bookings
            </a>
        </div>

        {{-- Tab Navigation --}}
        <div class="flex border-b border-gray-200 overflow-x-auto">
            <a href="{{ route('vendor.bookings.new') }}" class="px-4 sm:px-6 py-3 text-sm font-semibold border-b-2 border-[#00A86B] text-[#00A86B] whitespace-nowrap flex items-center gap-1">
                <i class="bi bi-inbox"></i> New
                <span class="ml-1 bg-[#00A86B] text-white text-xs px-1.5 py-0.5 rounded-full">{{ $stats['total'] }}</span>
            </a>
            <a href="{{ route('vendor.bookings.ongoing') }}" class="px-4 sm:px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap flex items-center gap-1">
                <i class="bi bi-play-circle"></i> Ongoing
            </a>
            <a href="{{ route('vendor.bookings.completed') }}" class="px-4 sm:px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap flex items-center gap-1">
                <i class="bi bi-check-circle"></i> Completed
            </a>
            <a href="{{ route('vendor.bookings.cancelled') }}" class="px-4 sm:px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap flex items-center gap-1">
                <i class="bi bi-x-circle"></i> Cancelled
            </a>
        </div>

        <div class="p-4 sm:p-6 space-y-6">

{{-- ===== CONTENT BELOW (stats, filters, table) ===== --}}

            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#dbeafe;color:#3b82f6;"><i class="bi bi-inbox"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">Total New</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['total'] }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#fef3c7;color:#f59e0b;"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">Pending Payment</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['pending_payment'] }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#e0e7ff;color:#6366f1;"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">Payment Hold</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['payment_hold'] }}</div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('vendor.bookings.new') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
                    <div class="lg:col-span-3">
                        <input type="text" name="search" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                    <div class="lg:col-span-2">
                        <input type="date" name="date_from" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" value="{{ request('date_from') }}">
                    </div>
                    <div class="lg:col-span-2">
                        <input type="date" name="date_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" value="{{ request('date_to') }}">
                    </div>
                    <div class="lg:col-span-2">
                        <input type="number" name="amount_min" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" placeholder="Min Amount" value="{{ request('amount_min') }}">
                    </div>
                    <div class="lg:col-span-2">
                        <input type="number" name="amount_max" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" placeholder="Max Amount" value="{{ request('amount_max') }}">
                    </div>
                    <div class="lg:col-span-1">
                        <button type="submit" class="w-full bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary/90 transition"><i class="bi bi-funnel"></i></button>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            @if($bookings->count() > 0)
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="min-w-full text-xs sm:text-sm">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Booking ID</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Customer</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Hoarding</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Duration</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Amount</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Status</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Payment</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Created</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($bookings as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="font-semibold text-blue-600 hover:underline">#{{ $booking->id }}</a>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <span class="font-medium text-gray-800">{{ $booking->customer->name }}</span><br>
                                <span class="text-xs text-gray-500">{{ $booking->customer->phone }}</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <span class="font-medium text-gray-800">{{ $booking->hoarding->name }}</span><br>
                                <span class="text-xs text-gray-500"><i class="bi bi-geo-alt"></i> {{ $booking->hoarding->location }}</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}<br>
                                <span class="text-xs text-gray-500">to {{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}</span><br>
                                <span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{{ $booking->duration_days }} days</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap font-semibold text-gray-800">
                                ₹{{ number_format($booking->total_amount, 2) }}
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                @php
                                    $statusColor = match($booking->status) {
                                        'pending_payment_hold' => 'bg-yellow-100 text-yellow-700',
                                        'payment_hold' => 'bg-blue-100 text-blue-700',
                                        'confirmed' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700'
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                @if($booking->payment_status)
                                    <span class="px-2 py-1 rounded text-xs font-semibold {{ $booking->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ ucfirst($booking->payment_status) }}</span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap text-xs text-gray-500">
                                {{ $booking->created_at->diffForHumans() }}
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div>{{ $bookings->links('pagination.vendor-compact') }}</div>
            @else
            <div class="text-center py-10 text-gray-400">
                <i class="bi bi-inbox" style="font-size:2.5rem;"></i>
                <h4 class="mt-3 text-base font-semibold text-gray-500">No New Bookings</h4>
                <p class="text-sm">No pending bookings at the moment.</p>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .avatar-sm {
        width: 32px;
        height: 32px;
    }
    .avatar-title {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
</style>
@endpush

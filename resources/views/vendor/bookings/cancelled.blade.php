@extends('layouts.vendor')

@section('page-title', 'Cancelled Bookings')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    <div class="bg-white rounded-md shadow">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 px-4 sm:px-6 py-4 bg-primary rounded-t-xl">
            <h4 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="bi bi-x-circle"></i> Cancelled Bookings
            </h4>
            <a href="{{ route('vendor.bookings.index') }}" class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="bi bi-arrow-left"></i> All Bookings
            </a>
        </div>

        <div class="p-4 sm:p-6 space-y-6">
            <!-- Tab Navigation -->
            <div class="flex border-b border-gray-200 overflow-x-auto">
                <a href="{{ route('vendor.bookings.new') }}" class="flex items-center gap-1 px-4 py-2.5 text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium whitespace-nowrap transition">
                    <i class="bi bi-inbox"></i> New
                </a>
                <a href="{{ route('vendor.bookings.ongoing') }}" class="flex items-center gap-1 px-4 py-2.5 text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium whitespace-nowrap transition">
                    <i class="bi bi-play-circle"></i> Ongoing
                </a>
                <a href="{{ route('vendor.bookings.completed') }}" class="flex items-center gap-1 px-4 py-2.5 text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium whitespace-nowrap transition">
                    <i class="bi bi-check-circle"></i> Completed
                </a>
                <a href="{{ route('vendor.bookings.cancelled') }}" class="flex items-center gap-1 px-4 py-2.5 text-sm border-b-2 border-[#00A86B] text-[#00A86B] font-semibold whitespace-nowrap transition">
                    <i class="bi bi-x-circle"></i> Cancelled
                    <span class="ml-1 bg-[#00A86B] text-white text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $stats['total'] }}</span>
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center bg-red-100 text-red-600">
                        <i class="bi bi-x-circle text-lg"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 font-medium">Total Cancelled</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['total'] }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center bg-yellow-100 text-yellow-600">
                        <i class="bi bi-ban text-lg"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 font-medium">Cancelled Only</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['cancelled_only'] }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center bg-blue-100 text-blue-600">
                        <i class="bi bi-arrow-counterclockwise text-lg"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 font-medium">Refunded</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['refunded'] }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center bg-red-100 text-red-600">
                        <i class="bi bi-currency-rupee text-lg"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 font-medium">Lost Revenue</div>
                        <div class="text-xl font-bold text-gray-800">₹{{ number_format($stats['lost_revenue'] / 100000, 2) }}L</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('vendor.bookings.cancelled') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div class="lg:col-span-2">
                    <input type="text" name="search" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" placeholder="Search bookings..." value="{{ request('search') }}">
                </div>
                <div>
                    <select name="cancellation_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none">
                        <option value="">All Types</option>
                        <option value="cancelled" {{ request('cancellation_type') === 'cancelled' ? 'selected' : '' }}>Cancelled Only</option>
                        <option value="refunded" {{ request('cancellation_type') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                <div>
                    <input type="date" name="date_from" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" value="{{ request('date_from') }}">
                </div>
                <div>
                    <input type="date" name="date_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" value="{{ request('date_to') }}">
                </div>
                <div class="flex gap-2">
                    <select name="sort_by" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none">
                        <option value="cancelled_at_desc" {{ request('sort_by') === 'cancelled_at_desc' ? 'selected' : '' }}>Recently Cancelled</option>
                        <option value="cancelled_at_asc" {{ request('sort_by') === 'cancelled_at_asc' ? 'selected' : '' }}>Oldest First</option>
                        <option value="amount_desc" {{ request('sort_by') === 'amount_desc' ? 'selected' : '' }}>Highest Amount</option>
                    </select>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary/90 transition flex items-center gap-1 whitespace-nowrap">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </form>

            <!-- Bookings Table -->
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="min-w-full text-xs sm:text-sm">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Booking ID</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Customer</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Hoarding</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Booking Period</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Cancelled Date</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Status</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Amount</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Reason</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($bookings as $booking)
                        @php
                            $isRefunded = $booking->status === 'refunded';
                            $cancelledAt = $booking->cancelled_at ? \Carbon\Carbon::parse($booking->cancelled_at) : null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="font-bold text-blue-600 hover:underline">
                                    #{{ $booking->id }}
                                </a>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-800">{{ $booking->customer->name }}</div>
                                <div class="text-xs text-gray-500">{{ $booking->customer->phone }}</div>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3">
                                <div class="font-medium text-gray-800">{{ $booking->hoarding->name }}</div>
                                <div class="text-xs text-gray-500"><i class="bi bi-geo-alt"></i> {{ $booking->hoarding->location }}</div>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <div class="text-gray-700">{{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">to {{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}</div>
                                <span class="inline-block mt-1 bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded">{{ $booking->duration_days }} days</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                @if($cancelledAt)
                                    <div class="text-gray-700"><i class="bi bi-calendar-x text-red-500"></i> {{ $cancelledAt->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $cancelledAt->diffForHumans() }}</div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                @if($isRefunded)
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-700">
                                        <i class="bi bi-arrow-counterclockwise"></i> Refunded
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-700">
                                        <i class="bi bi-x-circle"></i> Cancelled
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <span class="font-bold text-red-600">₹{{ number_format($booking->total_amount, 2) }}</span>
                                @if($isRefunded)
                                    <div class="text-xs text-gray-500">Refunded</div>
                                @endif
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3" style="max-width: 180px;">
                                @if($booking->cancellation_reason)
                                    <div class="text-xs text-gray-600 truncate" title="{{ $booking->cancellation_reason }}">
                                        <i class="bi bi-chat-left-text text-gray-400 mr-1"></i>{{ Str::limit($booking->cancellation_reason, 50) }}
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">No reason</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-gray-500">
                                <i class="bi bi-x-circle text-4xl block mb-2 text-gray-300"></i>
                                No Cancelled Bookings
                                <p class="text-xs mt-1">No cancelled bookings found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bookings->hasPages())
            <div class="pt-2">
                {{ $bookings->links('pagination.vendor-compact') }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

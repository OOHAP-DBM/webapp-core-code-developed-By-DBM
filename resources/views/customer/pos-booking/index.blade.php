
@extends('layouts.customer')

@section('title', 'POS Booking')

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold">My Bookings ({{ $stats['total_bookings'] ?? 0 }})</h1>
            <p class="text-gray-500 text-xs sm:text-sm">View and manage booked hoardings</p>
        </div>
        <form method="GET" class="flex flex-col sm:flex-row gap-2 items-center w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search booking by booking ID..." class="w-full border border-gray-300 pl-10 pr-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-semibold border border-gray-300">Filter</button>
        </form>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow border border-gray-200">
        <table class="min-w-full text-xs sm:text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Sn #</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Booking ID</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Quotation ID</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Vendors</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">#of Hoardings</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">#of Locations</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">#of Milestone</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Survey Opt</th>
                    <th class="px-3 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">Total Amount</th>
                    <th class="px-3 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">Paid Amount</th>
                    <th class="px-3 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">Balance</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Booking Status</th>
                    <th class="px-3 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $i => $booking)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-3 py-2">{{ $bookings->firstItem() + $i }}</td>
                    <td class="px-3 py-2 text-emerald-700 font-semibold">
                        <a href="#" class="hover:underline">#{{ $booking->invoice_number }}</a>
                        <div class="text-xs text-gray-400">{{ $booking->created_at->format('d M, y') }}</div>
                    </td>
                    <td class="px-3 py-2 text-emerald-700 font-semibold">
                        <a href="#" class="hover:underline">{{ $booking->quotation_id ?? '-' }}</a>
                        <div class="text-xs text-gray-400">{{ $booking->created_at->format('d M, y') }}</div>
                    </td>
                    <td class="px-3 py-2">{{ $booking->vendor_name ?? '-' }}</td>
                    <td class="px-3 py-2 text-center">{{ optional($booking->hoardings)->count() ?? 0 }}</td>
                    <td class="px-3 py-2 text-center">{{ $booking->locations_count ?? 2 }}</td>
                    <td class="px-3 py-2 text-center">{{ $booking->milestones_count ?? 2 }}</td>
                    <td class="px-3 py-2 text-center">
                        @if($booking->survey_opted ?? false)
                            <span class="text-emerald-600 font-semibold">Yes</span>
                        @else
                            <span class="text-gray-500">No</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right text-emerald-700 font-semibold">₹{{ number_format($booking->total_amount, 2) }}</td>
                    <td class="px-3 py-2 text-right text-emerald-700 font-semibold">₹{{ number_format($booking->paid_amount, 2) }}</td>
                    <td class="px-3 py-2 text-right font-semibold {{ $booking->balance > 0 ? 'text-red-500' : 'text-gray-500' }}">
                        @if($booking->balance > 0)
                            ₹{{ number_format($booking->balance, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        <span class="text-xs font-medium {{ $booking->status_color ?? 'text-gray-700' }}">{{ $booking->booking_status ?? '-' }}</span>
                        <div>
                            <a href="#" class="text-xs text-emerald-600 hover:underline">{{ $booking->status_note ?? '' }}</a>
                        </div>
                    </td>
                    <td class="px-3 py-2 text-center">
                        @if($booking->can_upload_sample ?? false)
                            <button class="bg-gray-200 text-gray-700 px-3 py-1 rounded hover:bg-gray-300 text-xs font-semibold">Upload Sample</button>
                        @else
                            <a href="#" class="bg-emerald-600 text-white px-3 py-1 rounded hover:bg-emerald-700 text-xs font-semibold">View Booking</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="text-center py-8 text-gray-400">No bookings found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="flex flex-col sm:flex-row items-center justify-between px-4 py-3 bg-gray-50 border-t border-gray-200">
            <div class="text-xs text-gray-500 mb-2 sm:mb-0">
                Rows per page
                <select class="border border-gray-300 rounded px-1 py-0.5 text-xs">
                    <option>05</option>
                    <option>10</option>
                    <option>25</option>
                </select>
                Showing {{ $bookings->firstItem() }} - {{ $bookings->lastItem() }} of {{ $bookings->total() }}
            </div>
            <div>
                {{ $bookings->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>
</div>
@endsection

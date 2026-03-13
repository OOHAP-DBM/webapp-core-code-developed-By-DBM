@extends('layouts.vendor')

@section('page-title', 'Booking Management')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    <div class="bg-white rounded-md shadow">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 px-4 sm:px-6 py-4 bg-primary rounded-t-xl">
            <h4 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 flex items-center gap-2">
                Booking Management
            </h4>
        </div>

        <div class="p-4 sm:p-6 space-y-6">

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#fef3c7;color:#f59e0b;"><i class="bi bi-clock"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">Pending</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['pending'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#d1fae5;color:#10b981;"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">Confirmed</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['confirmed'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#dbeafe;color:#2563eb;"><i class="bi bi-play-circle"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">Active</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['active'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#e0e7ff;color:#6366f1;"><i class="bi bi-check2-all"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">Completed</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['completed'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <form action="{{ route('vendor.bookings.index') }}" method="GET">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
                    <div class="lg:col-span-3">
                        <input type="text" name="search" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" placeholder="Booking ID, Customer..." value="{{ request('search') }}">
                    </div>
                    <div class="lg:col-span-2 relative">
                        <select name="status" class="appearance-none w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none pr-8">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg></span>
                    </div>
                    <div class="lg:col-span-2">
                        <input type="date" name="start_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" value="{{ request('start_date') }}">
                    </div>
                    <div class="lg:col-span-2">
                        <input type="date" name="end_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" value="{{ request('end_date') }}">
                    </div>
                    <div class="lg:col-span-3 flex gap-2">
                        <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary/90 transition">Filter</button>
                        <a href="{{ route('vendor.bookings.index') }}" class="flex-1 text-center border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">Clear</a>
                        <button type="button" onclick="window.print()" class="flex-shrink-0 border border-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm hover:bg-gray-50 transition"><i class="bi bi-printer"></i></button>
                    </div>
                </div>
            </form>

            {{-- Table --}}
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
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($bookings ?? [] as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <span class="font-semibold text-gray-800">#{{ $booking->id }}</span><br>
                                <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y') }}</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <span class="font-medium text-gray-800">{{ $booking->customer->name ?? 'N/A' }}</span><br>
                                <span class="text-xs text-gray-500">{{ $booking->customer->phone ?? '' }}</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <span class="text-gray-800">{{ $booking->hoarding->title ?? 'N/A' }}</span><br>
                                <span class="text-xs text-gray-500">{{ $booking->hoarding->city ?? '' }}</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($booking->start_date)->format('d M') }} –
                                {{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}<br>
                                <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($booking->start_date)->diffInDays(\Carbon\Carbon::parse($booking->end_date)) }} days</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap font-semibold text-gray-800">
                                ₹{{ number_format($booking->total_amount ?? 0, 0) }}
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    @if($booking->status === 'pending') bg-yellow-100 text-yellow-700
                                    @elseif($booking->status === 'confirmed') bg-green-100 text-green-700
                                    @elseif($booking->status === 'active') bg-blue-100 text-blue-700
                                    @elseif($booking->status === 'completed') bg-cyan-100 text-cyan-700
                                    @elseif($booking->status === 'cancelled') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ ($booking->payment_status ?? '') === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ ucfirst($booking->payment_status ?? 'pending') }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition">View</a>
                                    @if($booking->status === 'pending')
                                        <button type="button" class="text-xs bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 transition" onclick="confirmBooking({{ $booking->id }})">Confirm</button>
                                        <button type="button" class="text-xs bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition" onclick="cancelBooking({{ $booking->id }})">Cancel</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-400">
                                <i class="bi bi-calendar-x" style="font-size:2rem;"></i>
                                <p class="mt-2 text-sm">No bookings found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(isset($bookings) && $bookings->hasPages())
            <div>{{ $bookings->links() }}</div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmBooking(id) {
    if (confirm('Confirm this booking?')) {
        fetch(`/vendor/bookings/${id}/confirm`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to confirm booking');
            }
        });
    }
}

function cancelBooking(id) {
    const reason = prompt('Enter cancellation reason:');
    if (reason) {
        fetch(`/vendor/bookings/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to cancel booking');
            }
        });
    }
}
</script>
@endpush

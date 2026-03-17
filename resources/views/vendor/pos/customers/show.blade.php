@extends($posLayout ?? 'layouts.vendor')

@section('title', 'Customer Details')

@section('content')
<div class="px-6 py-6 space-y-6">
    @include('vendor.pos.components.admin-vendor-switcher')

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow  p-6 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold">
                {{ $customer['name'] ?? 'Walk-in Customer' }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Customer ID: {{ $customer['id'] }}
            </p>
        </div>

        <a href="{{ route(($posRoutePrefix ?? 'vendor.pos') . '.create', ['customer_id' => $customer['id']]) }}"
           class="px-4 py-2 btn-color text-black rounded-lg text-sm hover:opacity-90">
            + New Booking
        </a>
    </div>

    {{-- Customer Info --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="bg-white rounded-xl shadow  p-5">
            <h4 class="font-semibold mb-3">📞 Contact Details</h4>
            <div class="text-sm space-y-2">
                <div>
                    <strong>Phone:</strong>
                    @if($customer['phone'])
                        <a href="tel:{{ $customer['phone'] }}" class="text-blue-600 hover:underline">{{ $customer['phone'] }}</a>
                    @else
                        —
                    @endif
                </div>
                <div>
                    <strong>Email:</strong>
                    @if($customer['email'])
                        <a href="mailto:{{ trim($customer['email']) }}" class="text-blue-600 underline hover:text-blue-800" style="word-break:break-all;">{{ $customer['email'] }}</a>
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow  p-5">
            <h4 class="font-semibold mb-3">📊 Stats</h4>
            <div class="text-sm space-y-2">
                <div><strong>Total Bookings:</strong> {{ $customer['total_bookings'] }}</div>
                <div>
                    <strong>Total Spent:</strong>
                    ₹{{ number_format($customer['total_spent'], 2) }}
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow  p-5">
            <h4 class="font-semibold mb-3">⏱ Activity</h4>
            <div class="text-sm space-y-2">
                <div>
                    <strong>Last Booking:</strong>
                    {{ $customer['last_booking_at'] ? \Carbon\Carbon::parse($customer['last_booking_at'])->format('d M Y') : '—' }}
                </div>
                <div>
                    <strong>Status:</strong>
                    @if($customer['is_active'])
                        <span class="text-green-600 font-semibold">Active</span>
                    @else
                        <span class="text-gray-500">Inactive</span>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- Booking History --}}
    <div class="bg-white rounded-xl shadow ">
        <div class="px-6 py-4">
            <h3 class="font-semibold">🧾 Booking History</h3>
        </div>

        <div class="p-6 overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Booking ID</th>
                        <th class="px-4 py-2  text-left">Date</th>
                        <th class="px-4 py-2 text-right">Amount</th>
                        <th class="px-4 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customer['bookings'] as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">#{{ $booking->id }}</td>
                            <td class="px-4 py-2">
                                {{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y') }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                ₹{{ number_format($booking->total_amount, 2) }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-1 rounded text-xs bg-gray-100">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-500">
                                No bookings yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

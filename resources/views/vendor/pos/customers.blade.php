@extends('layouts.vendor')

@section('title', 'POS Customers')

@section('content')
<div class="px-2">
         <div class="pb-4 px-2 bg-primary  rounded-t-xl">
            <h4 class="text-lg font-semibold flex items-center gap-2">
               POS Customers
            </h4>
        </div>
    <div class="bg-white rounded-xl shadow">
            <div class="px-6 bg-primary  rounded-t-xl">
                <div class="pt-5 text-sm font-medium">
                    Total Customers: <span class="font-bold">{{ $totalCustomers }}</span>
                </div>
            </div>
        {{-- Body --}}
        <div class="p-6 m-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-600 uppercase text-xs tracking-wider">
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3 text-center">Bookings</th>
                        <th class="px-4 py-3 text-right">Total Spent</th>
                        <th class="px-4 py-3">Last Booking</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-gray-50">

                            {{-- Customer --}}
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">
                                    {{ $customer['name'] }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    ID: {{ $customer['id'] }}
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td class="px-4 py-3">
                                <div>{{ $customer['phone'] }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $customer['email'] ?? '—' }}
                                </div>
                            </td>

                            {{-- Bookings --}}
                            <td class="px-4 py-3 text-center font-semibold">
                                {{ $customer['total_bookings'] }}
                            </td>

                            {{-- Total Spent --}}
                            <td class="px-4 py-3 text-right font-semibold text-green-700">
                                ₹{{ number_format($customer['total_spent'], 2) }}
                            </td>

                            {{-- Last Booking --}}
                            <td class="px-4 py-3">
                                {{ $customer['last_booking_at']
                                    ? \Carbon\Carbon::parse($customer['last_booking_at'])->format('d M Y')
                                    : '—'
                                }}
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3 text-center">
                                @if($customer['is_active'])
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('vendor.pos.customers.show', $customer['id']) }}"
                                       class="px-3 py-1 rounded-lg border border-gray-200 text-xs hover:bg-gray-100">
                                        View
                                    </a>

                                  <a href="{{ route('vendor.pos.create', ['customer_id' => $customer['id']]) }}"
                                       class="px-3 py-1 rounded-lg bg-primary  text-xs hover:opacity-90">
                                        New Booking
                                    </a>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-gray-500">
                                No customers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection

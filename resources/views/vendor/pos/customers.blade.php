@include('vendor.pos.components.pos-timer-notification')
@extends($posLayout ?? 'layouts.vendor')

@section('title', 'POS Customers')
@section('content')
<script>window.POS_BASE_PATH = @json($posBasePath ?? '/vendor/pos');</script>
<div class="px-3 sm:px-4 md:px-6 py-4 sm:py-6 bg-gray-50">
    @include('vendor.pos.components.admin-vendor-switcher')
    <div class="pb-4 px-2 bg-primary rounded-t-xl">
            <h4 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center gap-2">
               POS Customers
            </h4>
        </div>
    <div class="bg-white rounded-xl shadow">
            <div class="px-3 sm:px-4 md:px-6 bg-primary rounded-t-xl">
                <div class="pt-5 text-sm font-medium">
                    Total Customers: <span class="font-bold">{{ $totalCustomers }}</span>
                </div>
            </div>
        {{-- Body --}}
        <div class="p-3 sm:p-4 md:p-6 m-2 sm:m-3 md:m-5">
            <div class="overflow-x-auto max-w-full">
            <table class="min-w-[700px] lg:min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr class="text-left text-gray-600 uppercase text-xs tracking-wider">
                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3">Customer</th>
                        <th class="hidden sm:table-cell px-2 sm:px-3 md:px-4 py-2 sm:py-3">Contact</th>
                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-center">Bookings</th>
                        <th class="hidden sm:table-cell px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-right">Total Spent</th>
                        <th class="hidden lg:table-cell px-2 sm:px-3 md:px-4 py-2 sm:py-3">Last Booking</th>
                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-center">Status</th>
                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-gray-50">

                            {{-- Customer --}}
                            <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3">
                                <div class="font-medium text-gray-900">
                                    {{ $customer['name'] }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    ID: {{ $customer['id'] }}
                                </div>
                                <div class="sm:hidden text-xs text-gray-600 mt-1">
                                    {{ $customer['phone'] }}
                                </div>
                                <div class="sm:hidden text-[11px] text-gray-400 truncate">
                                    {{ $customer['email'] ?? '—' }}
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td class="hidden sm:table-cell px-2 sm:px-3 md:px-4 py-2 sm:py-3">
                                <div>{{ $customer['phone'] }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $customer['email'] ?? '—' }}
                                </div>
                            </td>

                            {{-- Bookings --}}
                            <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-center font-semibold">
                                {{ $customer['total_bookings'] }}
                            </td>

                            {{-- Total Spent --}}
                            <td class="hidden sm:table-cell px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-right font-semibold text-green-700">
                                ₹{{ number_format($customer['total_spent'], 2) }}
                            </td>

                            {{-- Last Booking --}}
                            <td class="hidden lg:table-cell px-2 sm:px-3 md:px-4 py-2 sm:py-3">
                                {{ $customer['last_booking_at']
                                    ? \Carbon\Carbon::parse($customer['last_booking_at'])->format('d M Y')
                                    : '—'
                                }}
                            </td>

                            {{-- Status --}}
                            <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-center">
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
                            <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-center align-middle">
                                <div class="mx-auto flex flex-col sm:inline-flex sm:flex-row gap-2 w-full max-w-[180px] sm:max-w-none sm:w-auto">
                                    <a href="{{ route(($posRoutePrefix ?? 'vendor.pos') . '.customers.show', $customer['id']) }}"
                                       class="w-full sm:w-auto inline-flex items-center justify-center text-xs sm:text-sm font-medium py-2 sm:py-1 px-3 rounded-md border border-gray-200 bg-white hover:bg-gray-100 whitespace-nowrap">
                                        View
                                    </a>

                                    <a href="{{ route(($posRoutePrefix ?? 'vendor.pos') . '.create', ['customer_id' => $customer['id']]) }}"
                                       class="w-full sm:w-auto inline-flex items-center justify-center text-xs sm:text-sm font-semibold py-2 sm:py-1 px-3 rounded-md bg-green-600 text-white hover:opacity-90 whitespace-nowrap">
                                        + New Booking
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
</div>
@endsection

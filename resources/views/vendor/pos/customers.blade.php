@include('vendor.pos.components.pos-timer-notification')
@extends($posLayout ?? 'layouts.vendor')

@section('title', 'Customer Management')
@section('content')
<script>window.POS_BASE_PATH = @json($posBasePath ?? '/vendor/pos');</script>
<div class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 bg-gray-50">
    @include('vendor.pos.components.admin-vendor-switcher')
    <div class="pb-2 px-2 bg-primary rounded-t-xl">
        <h4 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center gap-2">
            <!-- Mobile-only back arrow button -->
            <button onclick="handleBackWithSidebarClose()" class="inline-flex sm:hidden items-center justify-center ml-[-0.5rem] rounded-full hover:bg-gray-200 focus:outline-none" aria-label="Back">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            All Customers
        </h4>
    </div>
    <div class="bg-white rounded-xl shadow">
        <div class="px-3 sm:px-4 md:px-6 bg-primary rounded-t-xl">
            <div class="px-2 bg-primary rounded-t-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="pt-4 text-sm font-medium">
                    Active Customers: <span class="font-bold">
                        {{ $customers->where('profile_status', 'active')->count() }}
                    </span>
                </div>
                <div class="pt-4 w-full sm:w-auto flex items-center gap-2">
                    <div class="flex flex-col sm:flex-row w-full gap-2">
                        <form id="customer-search-form" method="GET" action="" class="w-full">
                            <input type="text" id="customer-search" name="search" value="{{ request('search') }}" placeholder="Search by name, phone, or email" class="w-full lg:w-[300px] px-3 py-2 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-200" autocomplete="off" />
                        </form>
                        <button type="button" id="filterBtn" class="w-full sm:w-auto px-3 py-2 text-sm border border-gray-200">Filter</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- Body --}}
        <div class="px-2 sm:p-2 md:p-3 m-1 sm:m-2 md:m-3">
            <div class="overflow-x-auto max-w-full">
            <table class="w-full min-w-[920px] divide-y divide-gray-200 text-xs sm:text-sm">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr class="text-left text-gray-600 uppercase text-xs tracking-wider">
                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3">Customer Name</th>
                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3">Contact Details</th>
                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-center">Total Bookings</th>
                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-right">Lifetime Spend</th>
                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3">Last Booking Date</th>
                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-center">Account Status</th>
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
                               
                            </td>

                            {{-- Contact --}}
                            <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3">
                                <div>
                                    @if($customer['phone'])
                                        <a href="tel:{{ $customer['phone'] }}" class="text-blue-600 hover:underline">{{ $customer['phone'] }}</a>
                                    @else
                                        —
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if($customer['email'])
                                        <a href="mailto:{{ trim($customer['email']) }}" class="text-blue-600 underline hover:text-blue-800" style="word-break:break-all;">{{ $customer['email'] }}</a>
                                    @else
                                        —
                                    @endif
                                </div>
                            </td>

                            {{-- Bookings --}}
                            <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-center font-semibold">
                                {{ $customer['total_bookings'] }}
                            </td>

                            {{-- Total Spent --}}
                            <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-right font-semibold text-green-700">
                                ₹{{ number_format($customer['total_spent'], 2) }}
                            </td>

                            {{-- Last Booking --}}
                            <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3">
                                {{ $customer['last_booking_at']
                                    ? \Carbon\Carbon::parse($customer['last_booking_at'])->format('d M Y')
                                    : '—'
                                }}
                            </td>

                          {{-- Status --}}
                            <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-center">
                                @if($customer['profile_status'] === 'active')
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
                                <div class="mx-auto inline-flex gap-2 whitespace-nowrap">
                                    <a href="{{ route(($posRoutePrefix ?? 'vendor.pos') . '.customers.show', $customer['id']) }}"
                                       class="inline-flex items-center justify-center text-xs sm:text-sm font-medium py-2 sm:py-1 px-3 rounded-md border border-gray-200 bg-white hover:bg-gray-100 whitespace-nowrap">
                                        View Profile
                                    </a>

                                    <a href="{{ route(($posRoutePrefix ?? 'vendor.pos') . '.create', ['customer_id' => $customer['id']]) }}"
                                       class="inline-flex items-center justify-center text-xs sm:text-sm font-semibold py-2 sm:py-1 px-3 rounded-md bg-green-600 text-white hover:opacity-90 whitespace-nowrap">
                                        + Create Booking
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

<!-- FILTER MODAL -->
<div id="filterModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden px-5">

    <div class="bg-white shadow-lg w-full max-w-md overflow-hidden">

        <!-- HEADER -->
        <div class="flex items-center justify-between px-5 py-3 bg-green-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Filter</h3>

            <button id="closeFilterModal" class="text-gray-600 hover:text-black text-xl cursor-pointer">
                &times;
            </button>
        </div>

        <!-- BODY -->
        <div class="p-5">

            <form id="filterForm" method="GET" action="">

                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Account Status
                </label>

                <div class="flex gap-4 mb-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="status[]" value="all" style="border-radius:50%;" {{ empty(request('status')) ? 'checked' : '' }}>
                        <span class="ml-2">All</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="status[]" value="active" style="border-radius:50%;" {{ in_array('active', (array)request('status')) ? 'checked' : '' }}>
                        <span class="ml-2">Active</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="status[]" value="inactive" style="border-radius:50%;" {{ in_array('inactive', (array)request('status')) ? 'checked' : '' }}>
                        <span class="ml-2">Inactive</span>
                    </label>
                </div>

                <!-- FOOTER BUTTONS -->
                <div class="flex items-center justify-end gap-3 mt-6">

                    <button type="button" id="filterResetBtn"
                        class="text-gray-600 text-sm hover:text-black cursor-pointer">
                        Reset
                    </button>

                    <button type="submit"
                        class="bg-green-700 text-white px-4 py-2 rounded text-sm hover:bg-green-800 cursor-pointer">
                        Apply Filter
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('customer-search');
    const searchForm = document.getElementById('customer-search-form');
    if (searchInput && searchForm) {
        let debounceTimer = null;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                searchForm.submit();
            }, 400);
        });
    }

    // Filter modal logic
    const filterBtn = document.getElementById('filterBtn');
    const filterModal = document.getElementById('filterModal');
    const closeFilterModal = document.getElementById('closeFilterModal');
    filterBtn.addEventListener('click', function () {
        filterModal.classList.remove('hidden');
    });
    closeFilterModal.addEventListener('click', function () {
        filterModal.classList.add('hidden');
    });
    // Close modal on outside click
    filterModal.addEventListener('click', function (e) {
        if (e.target === filterModal) {
            filterModal.classList.add('hidden');
        }
    });
    // Filter modal reset logic
    const filterResetBtn = document.getElementById('filterResetBtn');
    if (filterResetBtn) {
        filterResetBtn.addEventListener('click', function () {
            window.location.href = window.POS_BASE_PATH + '/customers';
        });
    }
    // Status filter checkboxes logic
    const statusAll = document.getElementById('statusAll');
    const statusCheckboxes = document.querySelectorAll('input[name="status[]"]');
    if (statusAll && statusCheckboxes.length) {
        statusAll.addEventListener('change', function () {
            if (statusAll.checked) {
                statusCheckboxes.forEach(cb => cb.checked = true);
            } else {
                statusCheckboxes.forEach(cb => cb.checked = false);
            }
        });
        statusCheckboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                if ([...statusCheckboxes].every(cb => cb.checked)) {
                    statusAll.checked = true;
                } else {
                    statusAll.checked = false;
                }
            });
        });
    }
});
</script>

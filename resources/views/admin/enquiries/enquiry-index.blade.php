@extends('layouts.admin')

@section('title', 'All Enquiries')

@section('content')

<div x-data="{ openFilter: false, dateFilter: '{{ request('date_filter', 'all') }}' }" class="px-6 py-6 bg-white">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900">My Hoarding Enquiry</h2>
            <p class="text-xs text-gray-500">
                View and manage all customer enquiries involving hoardings from multiple vendors.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="relative flex-1 md:w-72" x-data="{ search: '{{ request('search') }}' }">
                <input
                    type="text"
                    name="search"
                    x-model="search"
                    @input.debounce.400ms="if(search.length >= 2) $el.form.submit()"
                    placeholder="Search enquiry by enquiry ID..."
                    class="w-full px-4 py-2 pr-10 border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                >
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
            </form>
            <button
                type="button"
                @click="openFilter = true"
                class="px-4 py-2 border border-gray-300 bg-white text-gray-900 text-sm hover:bg-gray-100 font-medium"
            >
                Filter
            </button>
        </div>
    </div>

    {{-- FILTER MODAL --}}
    <div
        x-show="openFilter"
        x-cloak
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >
        <div
            @click.away="openFilter = false"
            class="bg-white w-full max-w-2xl rounded shadow-lg relative"
        >
            <div class="flex items-center justify-between h-10 bg-green-100 px-4 rounded-t">
                <span></span>
                <button
                    @click="openFilter = false"
                    class="text-gray-800 hover:text-black text-xl"
                >
                    ✕
                </button>
            </div>
            <form method="GET" class="p-6 space-y-6">
                <h2 class="inline-block text-lg font-semibold text-gray-900 border-b border-gray-700 pb-1">
                    Filter
                </h2>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">
                        Created Enquiry by date
                    </h3>
                    <div class="flex flex-wrap items-center gap-6 text-sm text-gray-700">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="date_filter" value="all" x-model="dateFilter">
                            All
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="date_filter" value="last_week" x-model="dateFilter">
                            Last week
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="date_filter" value="last_month" x-model="dateFilter">
                            Last month
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="date_filter" value="last_year" x-model="dateFilter">
                            Last year
                        </label>
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="date_filter"
                                value="custom"
                                x-model="dateFilter"
                            >
                            Custom Date
                        </label>
                    </div>
                    <div
                        x-show="dateFilter === 'custom'"
                        x-transition
                        class="mt-4 flex gap-4"
                    >
                        <input
                            type="date"
                            name="from_date"
                            class="px-3 py-2 border border-gray-300 text-sm w-full"
                            placeholder="From"
                        >
                        <input
                            type="date"
                            name="to_date"
                            class="px-3 py-2 border border-gray-300 text-sm w-full"
                            placeholder="To"
                        >
                    </div>
                </div>
                <div class="flex items-center justify-end gap-6 pt-4">
                    <a href="{{ route('admin.enquiries.index') }}" class="text-sm text-black font-semibold hover:underline">
                        Reset
                    </a>
                    <button
                        type="submit"
                        class="px-6 py-2 bg-green-800 text-white text-sm font-semibold hover:bg-green-900"
                    >
                        Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE CARD --}}
<div class="bg-white shadow border ">
    <div class="w-full overflow-x-auto">
        <table class="min-w-full text-xs">
            <thead class="bg-gray-50 text-gray-700 uppercase text-[11px]">
                <tr>
                    <th class="px-3 py-3 text-left">Sn</th>
                    <th class="px-3 py-3 text-left">Enquiry ID</th>
                    <th class="px-3 py-3 text-left">Offer ID</th>
                    <th class="px-3 py-3 text-left">Customer Name</th>
                    <th class="px-3 py-3 text-left">Brand Manager</th>
                    <th class="px-3 py-3 text-center">No. of Hoardings</th>
                    <th class="px-3 py-3 text-center">No. of Locations</th>
                    <th class="px-3 py-3 text-center">Offer Valid Till</th>
                    <th class="px-3 py-3 text-left">Status</th>
                    <th class="px-3 py-3 text-center">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y">

            @forelse($enquiries as $i => $enquiry)
                <tr class="hover:bg-gray-50">

                    {{-- SN --}}
                    <td class="px-3 py-3">
                        {{ $enquiries->firstItem() + $i }}
                    </td>

                    {{-- ENQUIRY ID --}}
                    <td class="px-3 py-3">
                        <a href="{{ route('admin.enquiries.show', $enquiry->id) }}"
                           class="text-green-600 font-semibold hover:underline">
                            ({{ $enquiry->formatted_id }})
                        </a>
                        <div class="text-[10px] text-gray-400">
                            {{ \Carbon\Carbon::parse($enquiry->created_at)->format('d M, y') }}
                        </div>
                    </td>

                    {{-- OFFER ID --}}
                    <td class="px-3 py-3 text-gray-600">
                        -
                    </td>

                    {{-- CUSTOMER --}}
                    <td class="px-3 py-3">
                        <div class="font-medium text-gray-900">
                            {{ $enquiry->customer_name }}
                        </div>
                        <div class="text-[11px] text-gray-400">
                            {{ $enquiry->customer_email }}
                        </div>
                    </td>

                    {{-- BRAND MANAGER --}}
                    <td class="px-3 py-3">
                        <span class="text-blue-600 font-medium text-xs">
                            Assigned Now
                        </span>
                        <div class="text-[10px] text-gray-400">
                            Customer Manager
                        </div>
                    </td>

                    {{-- HOARDINGS COUNT --}}
                    <td class="px-3 py-3 text-center font-medium">
                        {{ $enquiry->hoardings_count }}
                    </td>

                    {{-- LOCATIONS --}}
                    <td class="px-3 py-3 text-center font-medium">
                        {{ $enquiry->locations_count }}
                    </td>

                    {{-- VALID TILL --}}
                    <td class="px-3 py-3 text-center">
                        @if($enquiry->offer_valid_till)
                            {{ \Carbon\Carbon::parse($enquiry->offer_valid_till)->format('d M, y') }}
                        @else
                            -
                        @endif
                    </td>

                    {{-- STATUS --}}
                    <td class="px-3 py-3">
                        @php $status = strtolower($enquiry->status); @endphp

                        @if($status === 'submitted')
                            <span class="text-blue-600 font-semibold text-xs">
                                <i>Enquiry Received :</i>
                            </span>
                            <span class="text-[12px] text-[var(--waiting)]">
                                Waiting for Your Response
                            </span>

                        @elseif($status === 'responded')
                            <div class="text-blue-600 font-semibold text-xs">
                                Offer Sent
                            </div>
                            <div class="text-[10px] text-orange-500">
                                Waiting for Customer Response
                            </div>

                        @elseif($status === 'cancelled')
                            <div class="text-red-600 font-semibold text-xs">
                                Cancelled
                            </div>

                        @else
                            <div class="text-gray-600 font-semibold text-xs">
                                {{ ucfirst($enquiry->status) }}
                            </div>
                        @endif

                        <div class="text-[10px] text-gray-400">
                            {{ \Carbon\Carbon::parse($enquiry->updated_at)->format('d M, y') }}
                        </div>
                    </td>

                    {{-- ACTION --}}
                    <td class="px-3 py-3 text-center">
                        <a href="{{ route('admin.enquiries.show', $enquiry->id) }}"
                           class="px-4 py-1.5 bg-gray-800 text-white text-xs font-medium hover:bg-black">
                            View Enquiry
                        </a>
                    </td>

                </tr>

            @empty
                <tr>
                    <td colspan="10" class="text-center py-10 text-gray-500">
                        No Enquiries Found
                    </td>
                </tr>
            @endforelse

            </tbody>
        </table>
    </div>

        {{-- PAGINATION --}}
        <div class="flex items-center justify-between px-4 py-4 border-t bg-gray-50">
            <div class="text-xs text-gray-500">
                Showing {{ $enquiries->firstItem() }} - {{ $enquiries->lastItem() }} of {{ $enquiries->total() }}
            </div>

            <div>
                {{ $enquiries->links() }}
            </div>
        </div>

    </div>
</div>

@endsection

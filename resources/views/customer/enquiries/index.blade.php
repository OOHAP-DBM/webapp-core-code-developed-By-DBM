@extends('layouts.customer')

@section('title', 'Enquiry & Offers')

@section('content')
<div x-data="{ 
            openFilter: false,
            dateFilter: '{{ request('date_filter', 'all') }}'
        }"
        class="px-6 py-6 bg-white">

    {{-- FILTER BAR --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div class="mb-6">
            <h1 class="text-lg font-bold text-gray-900">
                Enquiry & Offers
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Track all your enquiries and responses from vendors
            </p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="relative flex-1 md:w-72">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search enquiry by enquiry ID..."
                    class="w-full px-4 py-2 pr-10 border border-gray-300 text-sm
                        focus:outline-none focus:ring-2 focus:ring-green-500"
                >

                {{-- Search Icon --}}
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z" />
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

    {{-- TABLE --}}
    <div class="bg-white border border-gray-200 overflow-x-auto shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Sn</th>
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Enquiry ID</th>
                    <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs">Vendors</th>
                    <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs">Requirement</th>
                    <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs">No. of Locations</th>
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Status</th>
                    <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($enquiries as $index => $enquiry)
                    <tr class="hover:bg-gray-50 transition-colors">

                        {{-- SN --}}
                        <td class="px-4 py-4 text-gray-700">
                            {{ ($enquiries->currentPage() - 1) * $enquiries->perPage() + $index + 1 }}
                        </td>

                        {{-- ENQUIRY ID --}}
                        <td class="px-4 py-4">
                            <a href="{{ route('customer.enquiries.show', $enquiry->id) }}" class="text-green-600 font-semibold hover:text-green-700 hover:underline">
                               {{ $enquiry->formatted_id }}
                            </a>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $enquiry->created_at->format('d M, y') }}
                            </div>
                        </td>

                        {{-- # OF VENDORS --}}
                        <td class="px-4 py-4 text-center">
                            @php
                                $vendorCount = $enquiry->items->map(function($item) {
                                    return optional($item->hoarding)->vendor_id;
                                })->filter()->unique()->count();
                            @endphp
                            <span class="text-gray-900 font-semibold">
                                {{ $vendorCount }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            {{$enquiry->customer_note}}
                        </td>

                        {{-- # OF OFFERS --}}
                        <!-- <td class="px-4 py-4 text-center">
                            <span class="text-gray-900 font-semibold">
                                {{ $enquiry->offers()->count() }}
                            </span>
                        </td> -->

                        {{-- # OF LOCATIONS --}}
                        <td class="px-4 py-4 text-center">
                            @php
                                $locationCount = $enquiry->items->flatMap(function($item) {
                                    $hoarding = optional($item->hoarding);
                                    $locatedAt = $hoarding->located_at ?? [];
                                    return is_array($locatedAt) ? $locatedAt : [];
                                })->unique()->count();
                            @endphp
                            <span class="text-gray-900 font-semibold">
                                {{ $locationCount }}
                            </span>
                        </td>

                        {{-- STATUS --}}
                        <td class="px-4 py-4">
                            <div class="space-y-1">

                                {{-- STATUS TEXT --}}
                                @if($enquiry->status === 'submitted')

                                    <div class="flex">
                                        <i class="text-xs font-semibold text-gray-900">
                                            Enquiry Sent: &nbsp;
                                        </i>
                                        <div class="text-xs font-semibold text-[var(--waiting)]">
                                            Waiting for Vendor Response
                                        </div>
                                    </div>

                                @else

                                    {{-- OTHER STATUSES --}}
                                    <div class="text-xs font-semibold
                                        @if($enquiry->status === 'responded')
                                            text-orange-600
                                        @elseif($enquiry->status === 'accepted')
                                            text-green-600
                                        @elseif($enquiry->status === 'rejected')
                                            text-red-600
                                        @else
                                            text-gray-600
                                        @endif
                                    ">
                                        @if($enquiry->status === 'responded')
                                            Offers Received
                                        @elseif($enquiry->status === 'accepted')
                                            Accepted
                                        @elseif($enquiry->status === 'rejected')
                                            Rejected
                                        @else
                                            {{ ucwords(str_replace('_', ' ', $enquiry->status)) }}
                                        @endif
                                    </div>

                                @endif

                                {{-- DATE --}}
                                <div class="text-xs text-gray-500">
                                    {{ $enquiry->updated_at->format('d M, y | H:i') }}
                                </div>

                            </div>
                        </td>


                        {{-- ACTION --}}
                        <td class="px-4 py-4 text-center">
                            <div class="flex gap-2 justify-center flex-wrap">
                                <a href="{{ route('customer.enquiries.show', $enquiry->id) }}"
                                   class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-xs  font-semibold inline-block whitespace-nowrap transition-colors">
                                    View Details
                                </a>
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                            <div class="space-y-2">
                                <p class="font-medium">No enquiries found</p>
                                <p class="text-xs">You haven't made any enquiries yet</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- FILTER MODAL --}}
<div
    x-show="openFilter"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >


    {{-- Modal Box --}}
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
                            <input type="radio" name="date_filter" value="last_month"x-model="dateFilter">
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

                    {{-- Custom Date --}}
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

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-6 pt-4">

                    <a href="{{ route('customer.enquiries.index') }}"
                    class="text-sm text-black font-semibold hover:underline">
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

    {{-- PAGINATION --}}
    <div class="mt-6 flex items-center justify-between text-sm text-gray-600">
        <div class="font-medium">
            Showing {{ $enquiries->firstItem() ?? 0 }} - {{ $enquiries->lastItem() ?? 0 }} of {{ $enquiries->total() }}
        </div>
        <div>
            {{ $enquiries->links() }}
        </div>
    </div>

</div>

@endsection

@extends('layouts.customer')

@section('title', 'Enquiry & Offers')

@section('content')
<div x-data="enquiryManager()" class="px-6 py-6 bg-white">

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
            <form method="GET" class="flex items-center gap-2 flex-1 md:flex-none">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search vendor by name, email, mobile..."
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-green-500 flex-1 md:w-72"
                >
                <button
                    type="submit"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-400 font-medium"
                >
                    Filter
                </button>
            </form>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white border border-gray-200 overflow-x-auto shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Sn #</th>
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Enquiry ID</th>
                    <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs"># of Vendors</th>
                    <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs"># of Locations</th>
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
                                {{ 'ENQ' . str_pad($enquiry->id, 6, '0', STR_PAD_LEFT) }}
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
                                <div class="text-xs font-semibold
                                    @if($enquiry->status === 'submitted')
                                        text-blue-600
                                    @elseif($enquiry->status === 'responded')
                                        text-orange-600
                                    @elseif($enquiry->status === 'accepted')
                                        text-green-600
                                    @elseif($enquiry->status === 'rejected')
                                        text-red-600
                                    @else
                                        text-gray-600
                                    @endif
                                ">
                                    @if($enquiry->status === 'submitted')
                                        Waiting for Vendor Response
                                    @elseif($enquiry->status === 'responded')
                                        Offers Received
                                    @elseif($enquiry->status === 'accepted')
                                        Accepted
                                    @elseif($enquiry->status === 'rejected')
                                        Rejected
                                    @else
                                        {{ ucwords(str_replace('_', ' ', $enquiry->status)) }}
                                    @endif
                                </div>
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

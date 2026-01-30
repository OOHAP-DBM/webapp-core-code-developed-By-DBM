@extends('layouts.customer')

@section('title', 'Enquiry Details')

@section('content')
<div class="px-6 py-6 bg-white">

    {{-- ===== HEADER SECTION ===== --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white mb-6">
        <div>
            <h2 class="text-base font-semibold text-gray-900">
                Enquiry ID
                <span class="text-green-600">{{ 'ENQ' . str_pad($enquiry->id, 6, '0', STR_PAD_LEFT) }}</span>
            </h2>
            <p class="text-xs text-gray-500">Details of enquiry and vendor responses</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ url()->previous() }}" class="px-5 py-2 bg-gray-200 text-sm rounded hover:bg-gray-300 transition-colors">
                Back to List
            </a>
        </div>
    </div>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="space-y-6">

        {{-- ===== TOP INFO SECTION: 3 Columns ===== --}}
        <div class="grid grid-cols-12 gap-8">

            {{-- Column 1: Vendor Details (Multiple vendors if applicable) --}}
            <div class="col-span-4">
                <h3 class="text-sm font-semibold mb-4">Vendor Details</h3>
                @php
                    $vendors = $enquiry->items->map(function($item) {
                        return optional($item->hoarding)->vendor;
                    })->filter()->unique('id')->values();
                @endphp
                <div class="space-y-3 text-xs">
                    @forelse($vendors as $vendor)
                        <div class=" pb-3 mb-3">
                            <div>Name : <span class="font-medium">{{ $vendor->name ?? 'N/A' }}</span></div>
                            <div>Business Name : <span>{{ $vendor->company_name ?? 'N/A' }}</span></div>
                            <div>GSTIN : <span>{{ $vendor->gstin ?? 'N/A' }}</span></div>
                            <div>Mobile : <span>{{ $vendor->phone ?? 'N/A' }}</span></div>
                            <div>Email : <span>{{ $vendor->email ?? 'N/A' }}</span></div>
                            <div>Address : <span>{{ $vendor->address ?? 'N/A' }}</span></div>
                        </div>
                    @empty
                        <span class="text-gray-400">No vendors found</span>
                    @endforelse
                </div>
            </div>

            {{-- Column 2: Enquiry Details --}}
            <div class="col-span-4">
                <h3 class="text-sm font-semibold mb-4">Enquiry Details</h3>
                <div class="space-y-3 text-xs">
                    <div>Enquiry ID : <span class="font-medium">{{ 'ENQ' . str_pad($enquiry->id, 6, '0', STR_PAD_LEFT) }}</span></div>
                    <div>Status : <span class="font-medium">
                        @if($enquiry->status === 'submitted')
                            <span class="text-blue-600">Waiting for Vendor Response</span>
                        @elseif($enquiry->status === 'responded')
                            <span class="text-orange-600">Offers Received</span>
                        @elseif($enquiry->status === 'accepted')
                            <span class="text-green-600">Accepted</span>
                        @elseif($enquiry->status === 'rejected')
                            <span class="text-red-600">Rejected</span>
                        @else
                            {{ ucwords(str_replace('_', ' ', $enquiry->status)) }}
                        @endif
                    </span></div>
                    <div>Total Hoardings : <span class="font-medium">{{ $enquiry->items->count() }}</span></div>
                    <div>Total Vendors : <span class="font-medium">{{ $vendors->count() }}</span></div>
                    @if($enquiry->customer_note)
                        <div>Requirement : <span class="italic text-gray-700">{{ $enquiry->customer_note }}</span></div>
                    @endif
                </div>
            </div>

            {{-- Column 3: Submitted Date --}}
            <div class="col-span-4">
                <h3 class="text-sm font-semibold mb-4">Submitted On</h3>
                <div>
                    <span class="text-lg font-semibold leading-none">{{ $enquiry->created_at->format('d') }}</span>
                    <span class="text-sm text-gray-500 block">{{ $enquiry->created_at->format('M, y') }}</span>
                </div>
                <div class="mt-4 text-xs text-gray-500">
                    <div>Last Updated: {{ $enquiry->updated_at->format('d M, y H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- ===== HOARDINGS/ITEMS SECTION ===== --}}
        <div>
            <h3 class="text-sm font-semibold mb-4">
                Hoardings in Enquiry ({{ $enquiry->items->count() }})
            </h3>

            {{-- Group items by type --}}
            @php
                $groupedItems = $enquiry->items->groupBy('hoarding_type');
            @endphp

            @forelse($groupedItems as $type => $items)
                <div class="mb-8">
                    
                    {{-- Group Header --}}
                    <div class="flex items-center justify-between bg-gray-100 px-4 py-2 rounded text-sm font-semibold border border-gray-300 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-900">{{ strtoupper($type) }}</span>
                            <span class="text-gray-500 font-normal text-xs">({{ $items->count() }} items)</span>
                        </div>
                        <span class="text-xs text-gray-500 font-normal">
                            @if(strtoupper($type) === 'OOH')
                                Selected hoardings for your enquiry
                            @else
                                Selected Digital Screens for your enquiry
                            @endif
                        </span>
                    </div>

                    {{-- TABLE LAYOUT --}}
                    <div class="overflow-x-auto border border-gray-200 rounded">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="hidden md:table-cell px-4 py-3 text-left font-semibold text-gray-700">Sn.</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        @if(strtoupper($type) === 'OOH')
                                            Hoarding
                                        @else
                                            Screen
                                        @endif
                                    </th>
                                    <th class="hidden lg:table-cell px-4 py-3 text-left font-semibold text-gray-700">Campain Duration</th>
                                    <th class="hidden lg:table-cell px-4 py-3 text-left font-semibold text-gray-700">Campain Start</th>
                                    <th class="hidden lg:table-cell px-4 py-3 text-left font-semibold text-gray-700">Selected Package</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $index => $item)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        {{-- Serial --}}
                                        <td class="hidden md:table-cell px-4 py-3 text-gray-600 font-medium">
                                            {{ $index + 1 }}
                                        </td>
                                        
                                        {{-- Image & Details --}}
                                        <td class="px-4 py-3 flex gap-3">
                                            <div class="w-14 h-14 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                                @if($item->image_url)
                                                    <img
                                                        src="{{ $item->image_url }}"
                                                        class="w-full h-full object-cover"
                                                        alt="Hoarding"
                                                    >
                                                @else
                                                    <div class="w-full h-full bg-gray-300 flex items-center justify-center text-[9px] text-gray-500">
                                                        No Image
                                                    </div>
                                                @endif
                                            </div>                                              
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $item->hoarding->title ?? 'N/A' }}</p>
                                                <p class="text-gray-500">{{ $item->hoarding->locality ?? 'N/A' }}</p>
                                                <p class="text-gray-500">{{ $item->hoarding->size ?? '' }}</p>
                                            </div>
                                        </td>
                                        
                                        {{-- Package --}}
                                        <td class="hidden lg:table-cell px-4 py-3">
                                            <div class="space-y-1">
                                                <p class="font-medium text-gray-900">
                                                    <span>{{ $item->expected_duration ?? '-' }}</span>
                                                </p>
                                            </div>
                                        </td>
                                        {{-- Package --}}
                                        <td class="hidden lg:table-cell px-4 py-3">
                                            <div class="space-y-1">
                                                <p class="font-medium text-gray-900">
                                                     <span>{{ \Carbon\Carbon::parse($item->preferred_start_date)->format('d M Y') }}</span>                                                
                                                </p>
                                            </div>
                                        </td>
                                        {{-- Package --}}
                                       <td class="hidden lg:table-cell px-4 py-3">
                                            <div class="space-y-1">
                                                <p class="font-medium text-gray-900">
                                                    @if($item->package_name !== '-' && $item->discount_percent !== '-')
                                                        {{ $item->package_name }} – Offer {{ $item->discount_percent }}% Off
                                                    @else
                                                        -
                                                    @endif
                                                </p>
                                            </div>
                                        </td>

                                        
                                        {{-- Vendor --}}
                                        <td class="px-4 py-3 text-right text-gray-900 font-medium">
                                            <span>₹ {{ $item->final_price}}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                            No items found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            @empty
                <div class="border border-gray-200 rounded p-6 text-center text-gray-500">
                    No hoardings in this enquiry
                </div>
            @endforelse
        </div>

        {{-- ===== VENDOR OFFERS SECTION ===== --}}
        <div class="mt-8">
            <h3 class="text-sm font-semibold mb-4">
                Offers Received ({{ $enquiry->offers->count() }})
            </h3>

            @if($enquiry->offers->count() > 0)
                <div class="overflow-x-auto border border-gray-200 rounded">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Sn.</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Vendor</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Offer Items</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($enquiry->offers as $index => $offer)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-600 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        {{ $offer->vendor->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-gray-900 font-semibold">{{ $offer->items->count() ?? 0 }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold
                                            @if($offer->status === 'pending')
                                                bg-blue-100 text-blue-700
                                            @elseif($offer->status === 'accepted')
                                                bg-green-100 text-green-700
                                            @elseif($offer->status === 'rejected')
                                                bg-red-100 text-red-700
                                            @else
                                                bg-gray-100 text-gray-700
                                            @endif
                                        ">
                                            {{ ucfirst(str_replace('_', ' ', $offer->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">
                                        {{ $offer->created_at->format('d M, y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                        No offers received yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="border border-gray-200 rounded p-6 text-center text-gray-500">
                    <p>No offers received for this enquiry yet</p>
                </div>
            @endif
        </div>

    </div>

</div>

@endsection

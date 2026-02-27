
@extends('layouts.admin')

@section('title', 'Enquiry Details')

@section('content')
<div class="px-6 py-6 bg-white">
    {{-- ===== HEADER SECTION ===== --}}
    <div class="flex items-center justify-between py-4 bg-white mb-6">
        <div class="flex">
            <a href="{{ route('admin.enquiries.index') }}" class="mx-2">
                <svg width="16" class="mt-2" height="10" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.414 7.91412L3.828 7.91412L8.328 12.4141L6.914 13.8281L-2.93326e-07 6.91412L6.914 0.000125592L8.328 1.41413L3.828 5.91412L15.414 5.91412L15.414 7.91412Z" fill="#3C3C3C"/>
                </svg>
            </a>
            <h2 class="text-base font-semibold text-gray-900">
                Enquiry ID
                <span class="text-green-600">({{ $enquiry->formatted_id }})</span><br>
                <p class="text-xs text-gray-500">Details of enquiry and vendor responses</p>
            </h2>
        </div>
        <div></div>
    </div>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="space-y-6" x-data="{ openTop: true, openBottom: true }">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 bg-[#f7f7f7]">
            <div class="flex flex-wrap gap-4 text-xs text-gray-700">
                <span>
                    <strong>Overview:</strong>
                    Enquiry Raised On:
                    {{ $enquiry->created_at->format('d M, y') }}
                </span>
                <span>
                    # of Hoardings:
                    <strong>{{ count($items) }}</strong>
                </span>
                <span>
                    # of Hoardings Locations:
                    <strong>
                        {{
                            collect($items)->flatMap(fn($item) =>
                                optional($item->hoarding)->located_at ?? []
                            )->unique()->count()
                        }}
                    </strong>
                </span>
            </div>
            <button
                @click="openTop = !openTop"
                class="flex items-center cursor-pointer gap-1 text-xs bg-gray-200 px-3 py-1 rounded hover:bg-gray-300"
            >
                <span x-text="openTop ? 'Collapse' : 'Expand'"></span>
                <svg
                    class="w-4 h-4 transition-transform"
                    :class="open ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
        <div x-show="openTop" x-transition class="px-6 py-1 bg-[#f7f7f7]">
            <div class="grid grid-cols-12 gap-8 bg-[#f7f7f7]">
                <!-- <div class="col-span-4">
                    <h3 class="text-sm font-semibold mb-4">Vendor Details</h3>
                    @php
                        $vendors = collect($items)->map(function($item) {
                            return optional($item->hoarding)->vendor ?? null;
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
                </div> -->
                <div class="col-span-4 py-2">
                    <h3 class="text-sm font-semibold mb-4">Enquiry Details</h3>
                    <div class="space-y-3 text-xs">
                        <div>Enquiry ID : <span class="font-medium">({{ $enquiry->formatted_id }})</span></div>
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
                        <div>Total Hoardings : <span class="font-medium">{{ count($items) }}</span></div>
                        <div>Total Vendors : <span class="font-medium">{{ $vendors->count() }}</span></div>
                        @if($enquiry->customer_note)
                            <div>Requirement : <span class="italic text-gray-700">{{ $enquiry->customer_note }}</span></div>
                        @endif
                    </div>
                </div>
                <div class="col-span-4 hidden md:block">
                    <h3 class="text-sm font-semibold mb-4">Submitted On</h3>
                    <div>
                        <span class="text-sm text-gray-500 block">{{ $enquiry->created_at->format('d M y') }}</span>
                    </div>
                    <div class="mt-4 text-xs text-gray-500">
                        <div>Last Updated: {{ $enquiry->updated_at->format('d M, y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-between px-6 py-1">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">
                    Enquire Hoardings ({{ count($items) }})
                </h3>
                <p class="text-xs text-gray-500">
                    Hoardings for Enquiry
                </p>
            </div>
            <div></div>
        </div>
        <div class="bg-[#f7f7f7] px-5 py-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold mb-4">
                Hoardings in Enquiry ({{ count($items) }})
            </h3>
            <button
                @click="openBottom = !openBottom"
                class="flex items-center cursor-pointer gap-1 text-xs bg-gray-200 px-3 py-1 rounded hover:bg-gray-300"
            >
                <span x-text="openBottom ? 'Collapse' : 'Expand'"></span>
                <svg
                    class="w-4 h-4 transition-transform"
                    :class="openBottom ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            </div>
            <div x-show="openBottom" x-transition>
                @php
                    $groupedItems = collect($items)->groupBy('hoarding_type');
                @endphp
                @forelse($groupedItems as $type => $items)
                    <div class="mb-8">
                        <div class="flex items-center justify-between bg-gray-100 px-4 py-2 rounded text-sm font-semibold border border-gray-300 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-900">{{ strtoupper($type) }}</span>
                                <span class="text-gray-500 font-normal text-xs">({{ $items->count() }} items)</span>
                            </div>
                            <span class="text-xs text-gray-500 font-normal">
                                @if(strtoupper($type) === 'OOH')
                                    Selected hoardings for this enquiry
                                @else
                                    Selected Digital Screens for this enquiry
                                @endif
                            </span>
                        </div>
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
                                            <td class="hidden md:table-cell px-4 py-3 text-gray-600 font-medium">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 flex gap-3">
                                                <div class="w-14 h-14 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                                    @if(isset($item->image_url))
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
                                            <td class="hidden lg:table-cell px-4 py-3">
                                                <div class="space-y-1">
                                                    <p class="font-medium text-gray-900">
                                                        <span>{{ $item->expected_duration ?? '-' }}</span>
                                                    </p>
                                                </div>
                                            </td>
                                            <td class="hidden lg:table-cell px-4 py-3">
                                                <div class="space-y-1">
                                                    <p class="font-medium text-gray-900">
                                                        <span>{{ \Carbon\Carbon::parse($item->preferred_start_date)->format('d M Y') }}</span>
                                                    </p>
                                                </div>
                                            </td>
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
        </div>
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

@extends('layouts.customer')

@section('title', 'Booking Details')

@section('content')

<div class="px-2 sm:px-4 md:px-6 py-4 md:py-6 bg-white rounded border border-gray-200 min-h-screen" x-data="{ openTop: true, openBottom: true }">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between py-1 bg-white mb-6 gap-2 sm:gap-0">
        <div class="flex items-center">
            <a href="{{ route('customer.pos.booking') }}" class="mr-2">
                <svg width="16"  height="10" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.414 7.91412L3.828 7.91412L8.328 12.4141L6.914 13.8281L-2.93326e-07 6.91412L6.914 0.000125592L8.328 1.41413L3.828 5.91412L15.414 5.91412L15.414 7.91412Z" fill="#3C3C3C"/>
                </svg>
            </a>
            <h2 class="text-base md:text-lg font-semibold text-gray-900">
                Booking ID
                <span class="text-green-600">({{ $booking->id }})</span><br>
                <p class="text-xs text-gray-500">View complete vendor, hoarding, and payment information for this order.</p>
            </h2>
        </div>
        <div class="mt-2 sm:mt-0">
            {{-- Uncomment to enable chat
            <button class="inline-flex items-center gap-1 bg-green-600 text-white px-4 py-1.5 text-xs rounded">Chat</button>
            --}}
        </div>
    </div>

    <div class="space-y-6">

        {{-- ===== OVERVIEW BAR ===== --}}
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-2 md:px-6 py-3 border-b border-gray-200 bg-[#f7f7f7] gap-2 md:gap-0">
            <div class="flex flex-wrap gap-4 text-xs text-gray-700 ">
                <span><strong>Overview:</strong></span>
                {{-- <span>Offer ID: <strong>#{{ $booking->offer_code ?? '-' }}</strong></span> --}}
                <span>No. of Hoardings: <strong>{{ $booking->hoardings->count() }}</strong></span>
                <span>No. of Hoardings Locations: <strong>{{ $booking->hoardings->pluck('city')->unique()->count() }}</strong></span>
            </div>
            <button
                @click="openTop = !openTop"
                class="flex items-center cursor-pointer gap-1 text-xs bg-gray-200 px-3 py-1 rounded hover:bg-gray-300"
            >
                <span x-text="openTop ? 'Collapse' : 'Expand'"></span>
                <svg class="w-4 h-4 transition-transform" :class="openTop ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        {{-- ===== COLLAPSIBLE TOP SECTION ===== --}}
        <div x-show="openTop" x-transition class="px-2 md:px-6 py-1 bg-[#f7f7f7]">

            @php
                $allBh = $booking->bookingHoardings;

                $oohBh = $allBh->filter(function($bh) {
                    if (!$bh->hoarding) return false;
                    if (isset($bh->hoarding->hoarding_type)) {
                        return strtolower($bh->hoarding->hoarding_type) === 'ooh';
                    }
                    return !$bh->hoarding->is_digital;
                });

                $doohBh = $allBh->filter(function($bh) {
                    if (!$bh->hoarding) return false;
                    if (isset($bh->hoarding->hoarding_type)) {
                        return strtolower($bh->hoarding->hoarding_type) === 'dooh';
                    }
                    return (bool) $bh->hoarding->is_digital;
                });

                $oohCount   = $oohBh->count();
                $doohCount  = $doohBh->count();
                $cities     = $booking->hoardings->pluck('city')->unique()->filter();
                $milestones = $booking->milestones ?? collect([]);
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8 bg-[#f7f7f7] my-3">

                {{-- Column 1: Vendor Details --}}
                <div class="">
                    <h3 class="text-sm font-semibold mb-4">Vendor Details</h3>
                    <div class="space-y-2 text-xs text-gray-700">
                        <div><strong>Name:</strong> {{ filled($booking->vendor->vendorProfile?->contact_person_name) ? $booking->vendor->vendorProfile->contact_person_name : (filled($booking->vendor?->name) ? $booking->vendor->name : '-') }}</div>
                        <div><strong>Business Name:</strong> {{ filled($booking->vendor->vendorProfile?->company_name) ? $booking->vendor->vendorProfile->company_name : (filled($booking->vendor?->company_name) ? $booking->vendor->company_name : '-') }}</div>
                        <div><strong>GSTIN:</strong> {{ filled($booking->vendor->vendorProfile?->gstin) ? $booking->vendor->vendorProfile->gstin : (filled($booking->vendor?->gstin) ? $booking->vendor->gstin : '-') }}</div>
                        <div><strong>Email:</strong> {{ filled($booking->vendor?->email) ? $booking->vendor->email : '-' }}</div>
                        <div><strong>Mobile:</strong> {{ filled($booking->vendor->vendorProfile?->contact_person_phone) ? $booking->vendor->vendorProfile->contact_person_phone : (filled($booking->vendor?->phone) ? $booking->vendor->phone : '-') }}</div>
                        <div>
                            <strong>Address:</strong>
                            {{ filled($booking->vendor->vendorProfile?->registered_address) ? $booking->vendor->vendorProfile->registered_address : (filled($booking->vendor?->address) ? $booking->vendor->address : '-') }},
                            {{ filled($booking->vendor->vendorProfile?->city) ? $booking->vendor->vendorProfile->city : (filled($booking->vendor?->city) ? $booking->vendor->city : '') }}
                            {{ filled($booking->vendor->vendorProfile?->state) ? $booking->vendor->vendorProfile->state : (filled($booking->vendor?->state) ? $booking->vendor->state : '') }}
                        </div>
                        {{-- <div class="flex items-center gap-2 pt-1">
                            <span>Brand Manager:</span>
                            <button class="text-xs bg-green-100 text-green-700 border border-green-300 rounded px-2 py-0.5">Assign</button>
                            <button class="text-xs bg-red-100 text-red-600 border border-red-300 rounded px-2 py-0.5">Reject</button>
                        </div> --}}
                    </div>
                </div>

                {{-- Column 2: Hoarding + Payment Details --}}
                <div class="">
                    <h3 class="text-sm font-semibold mb-4">Hoarding Details</h3>
                    <div class="space-y-2 text-xs text-gray-700">
                        <div>
                            Total Hoardings: <strong>{{ $allBh->count() }}</strong>
                            | OOH: <strong>{{ $oohCount }}</strong>
                            | DOOH: <strong>{{ $doohCount }}</strong>
                        </div>
                        <div class="flex flex-wrap gap-1 items-center">
                            <span>Including Cities:</span>
                            @foreach($cities as $city)
                                <a href="#" class="text-blue-600 underline">{{ $city }}</a>
                                @if(!$loop->last)<span class="text-gray-400">|</span>@endif
                            @endforeach
                        </div>
                    </div>

                    <h3 class="text-sm font-semibold mt-5 mb-4">Payment Details</h3>
                    <div class="space-y-2 text-xs text-gray-700">
                        <div>Total Amount: <strong>₹{{ number_format($booking->total_amount, 2) }}</strong></div>
                        <div>Paid Amount: <strong>₹{{ number_format($booking->paid_amount, 2) }}</strong></div>
                        <div>Balance: <strong class="text-red-600">₹{{ number_format($booking->total_amount - $booking->paid_amount, 2) }}</strong></div>
                        <div>Payment Status: <strong>{{ ucfirst($booking->payment_status) }}</strong></div>
                        <div>Payment Method: <strong>{{ ucfirst(str_replace('_', ' ', $booking->payment_mode)) }}</strong></div>

                    <a href="{{ url("invoices/{$booking->id}/download") }}" target="_blank" rel="noopener noreferrer">
                            <div class="flex items-center gap-2 flex-wrap mt-1">
                                <span>Invoice Number:</span>
                                <strong class="flex items-center gap-1 cursor-pointer">
                                    <span style="color:#0089E1">{{ $booking->invoice_number ?? '-' }}</span>
                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 9L2.25 5.25L3.3 4.1625L5.25 6.1125V0H6.75V6.1125L8.7 4.1625L9.75 5.25L6 9ZM1.5 12C1.0875 12 0.7345 11.8533 0.441 11.5597C0.1475 11.2662 0.0005 10.913 0 10.5V8.25H1.5V10.5H10.5V8.25H12V10.5C12 10.9125 11.8533 11.2657 11.5597 11.5597C11.2662 11.8538 10.913 12.0005 10.5 12H1.5Z" fill="#0089E1"/>
                                    </svg>
                                </strong>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="">
                    <h3 class="text-sm font-semibold mb-1">Booking  Status</h3>
                    <div class="space-y-0">
                    
                            <div class="flex items-center justify-between text-xs py-1.5 border-b border-gray-200 last:border-0">
                                <div>
                                    <div class="font-bold text-gray-700 uppercase mb-1">{{ $booking->status}}</div>
                                    <div class="text-gray-500">
                                        Updated on {{ $booking->updated_at ? $booking->updated_at->format('d M, y') : '-' }}
                                </div>
                            </div>
                     
                    </div>
                </div>

                {{-- Column 3: Milestone Timeline --}}
                <div class="lg:col-span-4 mt-6">
                    <h3 class="text-sm font-semibold mb-1">Milestone Timeline</h3>
                    <div class="space-y-0">
                        @forelse($milestones as $ms)
                            <div class="flex items-center justify-between text-xs py-1.5 border-b border-gray-200 last:border-0">
                                <div>
                                    <div class="font-semibold text-gray-700">{{ $ms->title ?? ('Milestone ' . ($ms->sequence_no ?? $loop->iteration)) }}</div>
                                    <div class="text-gray-500">
                                        ₹{{ number_format((float) ($ms->calculated_amount ?? $ms->amount ?? 0), 2) }}
                                        &nbsp;<span class="text-red-500">Due {{ $ms->due_date ? \Carbon\Carbon::parse($ms->due_date)->format('d M, y') : '-' }}</span>
                                        &nbsp;<span class="text-gray-400">| {{ ucfirst($ms->status ?? 'pending') }}</span>
                                    </div>
                                </div>
                                {{-- @if(in_array($ms->status, ['due', 'overdue']))
                                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-0.5 rounded text-xs font-semibold">Pay Now</button>
                                @else
                                    <span class="px-2.5 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-600">{{ ucfirst($ms->status ?? 'pending') }}</span>
                                @endif --}}
                            </div>
                        @empty
                            <p class="text-xs text-gray-400">No milestones added yet.</p>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- ===== BOOKING STATUS PROGRESS ===== --}}
            @php
                $duration = '';
                if ($booking->start_date && $booking->end_date) {
                    $diff   = $booking->start_date->diffInDays($booking->end_date);
                    $months = round($diff / 30, 1);
                    $duration = $booking->start_date->format('d M, y')
                                . ' – ' . $booking->end_date->format('d M, y')
                                . ' | ' . ($months >= 1 ? intval($months) . ' Month' : $diff . ' Days');
                }
                $steps = [
                    ['label' => 'Booked on',          'sub' => $booking->created_at ? $booking->created_at->format('d M, y') : '-', 'status' => 'done'],
                    ['label' => 'Graphics Completed', 'sub' => 'Due On 2 Mar, 25',  'status' => 'due'],
                    ['label' => 'Printing Completed', 'sub' => 'Due On 10 Mar, 25', 'status' => 'due'],
                    ['label' => 'Mounting Completed', 'sub' => 'Due On 16 Mar, 25', 'status' => 'due'],
                    ['label' => 'Campaign Live',       'sub' => 'Due On 22 Mar, 25', 'status' => 'warning'],
                    ['label' => 'Campaign Expired',   'sub' => '25 Apr, 25',         'status' => 'expired'],
                ];
            @endphp

            <!-- <div class="mt-4 pb-4">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-sm font-semibold text-gray-900">Booking Current Status</h3>
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        {{-- <span>Graphics Design Preferences <button class="text-blue-600 ml-1">Change</button></span>
                        <span>Design Reference: <button class="text-blue-600 ml-1">View</button></span> --}}
                    </div>
                </div>
                <p class="text-xs text-gray-500 mb-1">Track the live progress of your campaign from design to installation.</p>
                <p class="text-xs text-gray-500 mb-4">Duration: {{ $duration ?: '-' }}</p>

                <div class="relative flex items-start justify-between px-2">
                    <div class="absolute top-4 left-8 right-8 h-0.5 bg-gray-200 z-0"></div>
                    @foreach($steps as $step)
                        <div class="flex flex-col items-center z-10 flex-1 min-w-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 text-xs font-bold
                                @if($step['status']==='done')        bg-green-500  border-green-500  text-white
                                @elseif($step['status']==='warning') bg-orange-400 border-orange-400 text-white
                                @elseif($step['status']==='expired') bg-red-500    border-red-500    text-white
                                @else                                 bg-white      border-gray-300   text-gray-400
                                @endif">
                                @if($step['status']==='done')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @elseif($step['status']==='warning')
                                    <span>!</span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-gray-300 block"></span>
                                @endif
                            </div>
                            <div class="text-center mt-1.5 px-1">
                                <div class="text-xs font-semibold text-gray-700 leading-tight">{{ $step['label'] }}</div>
                                <div class="text-xs leading-tight mt-0.5 @if($step['status']==='due') text-red-500 @else text-gray-400 @endif">
                                    {{ $step['sub'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div> -->

        </div>{{-- /openTop --}}


        {{-- ===== HOARDINGS SECTION ===== --}}
        <div class="bg-[#f7f7f7] pr-2  py-3">

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-3 gap-2 sm:gap-0">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">
                        Hoarding Details ({{ $booking->bookingHoardings->count() }})
                    </h3>
                    <p class="text-xs text-gray-500">Hoardings selected for this booking</p>
                </div>
                <button
                    @click="openBottom = !openBottom"
                    class="flex items-center cursor-pointer gap-1 text-xs bg-gray-200 px-3 py-1 rounded hover:bg-gray-300"
                >
                    <span x-text="openBottom ? 'Collapse' : 'Expand'"></span>
                    <svg class="w-4 h-4 transition-transform" :class="openBottom ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            <div x-show="openBottom" x-transition>

                @php
                    $grouped = [];
                    if ($oohBh->count() || $doohBh->count()) {
                        if ($oohBh->count())  $grouped['OOH']          = $oohBh;
                        if ($doohBh->count()) $grouped['DIGITAL-DOOH'] = $doohBh;
                    } else {
                        $grouped['Hoardings'] = $allBh;
                    }
                @endphp

                @foreach($grouped as $groupLabel => $groupItems)
                <div class="mb-6">

                    {{-- Group Header --}}
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between bg-gray-100 px-2 md:px-4 py-2 rounded text-sm font-semibold border border-gray-300 mb-3 gap-2 sm:gap-0">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-900">{{ $groupLabel }}</span>
                            <span class="text-gray-500 font-normal text-xs">({{ $groupItems->count() }} items)</span>
                        </div>
                        <span class="text-xs text-gray-500 font-normal">
                            @if($groupLabel === 'OOH')
                                Selected Static Hoardings
                            @elseif($groupLabel === 'DIGITAL-DOOH')
                                Selected Digital Screens for the offer
                            @else
                                Selected hoardings for this booking
                            @endif
                        </span>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto border border-gray-200 rounded">
                        <table class="w-full text-xs min-w-[700px]">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-2 md:px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Sn.</th>
                                    <th class="px-2 md:px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Hoarding</th>
                                    <th class="px-2 md:px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Type</th>
                                    <th class="px-2 md:px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Address</th>
                                    <th class="px-2 md:px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Booking Start</th>
                                    <th class="px-2 md:px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Booking End</th>
                                    <th class="px-2 md:px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Booking Created</th>
                                    <th class="px-2 md:px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Graphics Designer</th>
                                    <th class="px-2 md:px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Price</th>
                                    <th class="px-2 md:px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groupItems as $bh)
                                    @php
                                        // Fetch media exactly like enquiry code does
                                        $mediaItem = null;

                                        if (($bh->hoarding->hoarding_type ?? '') === 'ooh') {
                                            $mediaItem = \Modules\Hoardings\Models\HoardingMedia::where('hoarding_id', $bh->hoarding->id)
                                                ->orderByDesc('is_primary')
                                                ->orderBy('sort_order')
                                                ->first();

                                        } elseif (($bh->hoarding->hoarding_type ?? '') === 'dooh') {
                                            $screen = \Modules\DOOH\Models\DOOHScreen::where('hoarding_id', $bh->hoarding->id)->first();
                                            if ($screen) {
                                                $mediaItem = \Modules\DOOH\Models\DOOHScreenMedia::where('dooh_screen_id', $screen->id)
                                                    ->orderBy('sort_order')
                                                    ->first();
                                            }
                                        }
                                    @endphp
                                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                        <td class="px-2 md:px-4 py-3 text-gray-600 font-medium">{{ $loop->iteration }}</td>
                                        <td class="px-2 md:px-4 py-3">
                                            <div class="flex items-center gap-2 md:gap-3">
                                                <div class="w-12 h-12 md:w-14 md:h-14 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                                    @if($mediaItem)
                                                        <x-media-preview :media="$mediaItem" :alt="$bh->hoarding->title ?? 'Hoarding'" />
                                                    @else
                                                        <div class="w-full h-full bg-gray-300 flex items-center justify-center text-[9px] text-gray-500">
                                                            No Image
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 truncate max-w-[120px] md:max-w-[160px]">
                                                        @php
                                                            $hoardingParam = $bh->hoarding->slug ?? $bh->hoarding->id;
                                                        @endphp
                                                        <a href="{{ route('hoardings.show', $hoardingParam) }}" target="_blank" class="hover:underline">
                                                            {{ $bh->hoarding->title ?? '-' }}
                                                        </a>
                                                    </p>
                                                    <p class="text-gray-500 flex items-center gap-0.5">
                                                        <svg class="w-2.5 h-2.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                                        </svg>
                                                        {{ $bh->hoarding->city ?? '' }}
                                                        @if($bh->hoarding->state ?? false)
                                                            {{ $bh->hoarding->state }}
                                                        @endif
                                                    </p>
                                                    @if($groupLabel === 'DIGITAL-DOOH')
                                                        <p class="text-blue-500 font-medium">LED</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2 md:px-4 py-3">{{ strtoupper($bh->hoarding->hoarding_type ?? '-') }}</td>
                                        <td class="px-2 md:px-4 py-3">{{ $bh->hoarding->display_location  ?? '-' }}</td>
                                        <td class="px-2 md:px-4 py-3">{{ $bh->start_date ? $bh->start_date->format('d M, Y') : '-' }}</td>
                                        <td class="px-2 md:px-4 py-3">{{ $bh->end_date ? $bh->end_date->format('d M, Y') : '-' }}</td>
                                        <td class="px-2 md:px-4 py-3">{{ $bh->created_at ? $bh->created_at->format('d M, Y') : '-' }}</td>
                                        <td class="px-2 md:px-4 py-3 text-gray-500">Not Assigned</td>
                                        <td class="px-2 md:px-4 py-3">₹{{ number_format($bh->hoarding_total, 2) }}</td>
                                        <td class="px-2 md:px-4 py-3 text-gray-400">
                                            {{ $bh->status ? ucwords(str_replace('_', ' ', $bh->status)) : '–' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-4 py-6 text-center text-gray-400">No hoardings found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
                @endforeach

            </div>{{-- /openBottom --}}
        </div>

    </div>{{-- /space-y-6 --}}
</div>

@endsection
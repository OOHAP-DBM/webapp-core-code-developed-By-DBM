@extends('layouts.customer')

@section('title', 'Booking Details')

@section('content')

<div class="px-6 py-6 bg-white" x-data="{ openTop: true, openBottom: true }">

    {{-- ===== HEADER ===== --}}
    <div class="flex items-center justify-between py-4 bg-white mb-6">
        <div class="flex">
            <a href="{{ route('customer.pos.booking') }}" class="mx-2">
                <svg width="16" class="mt-2" height="10" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.414 7.91412L3.828 7.91412L8.328 12.4141L6.914 13.8281L-2.93326e-07 6.91412L6.914 0.000125592L8.328 1.41413L3.828 5.91412L15.414 5.91412L15.414 7.91412Z" fill="#3C3C3C"/>
                </svg>
            </a>
            <h2 class="text-base font-semibold text-gray-900">
                Booking ID
                <span class="text-green-600">(#{{ $booking->id }})</span><br>
                <p class="text-xs text-gray-500">View complete vendor, hoarding, and payment information for this order.</p>
            </h2>
        </div>
        <div>
            {{-- Uncomment to enable chat
            <button class="inline-flex items-center gap-1 bg-green-600 text-white px-4 py-1.5 text-xs rounded">Chat</button>
            --}}
        </div>
    </div>

    <div class="space-y-6">

        {{-- ===== OVERVIEW BAR ===== --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 bg-[#f7f7f7]">
            <div class="flex flex-wrap gap-4 text-xs text-gray-700">
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
        <div x-show="openTop" x-transition class="px-6 py-1 bg-[#f7f7f7]">

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

            <div class="grid grid-cols-12 gap-8 bg-[#f7f7f7] my-3">

                {{-- Column 1: Vendor Details --}}
                <div class="col-span-4">
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
                <div class="col-span-4">
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
                        <div>Paid: <strong>₹{{ number_format($booking->paid_amount, 2) }}</strong></div>
                        <div>Balance: <strong class="text-red-600">₹{{ number_format($booking->total_amount - $booking->paid_amount, 2) }}</strong></div>
                        <div>Payment Status: <strong>{{ ucfirst($booking->payment_status) }}</strong></div>
                        <div>Payment Mode: <strong>{{ ucfirst(str_replace('_', ' ', $booking->payment_mode)) }}</strong></div>
                        <div>Invoice Number: <strong>{{ $booking->invoice_number ?? '-' }}</strong></div>
                    </div>
                </div>

                {{-- Column 3: Milestone Timeline --}}
                <div class="col-span-4">
                    <h3 class="text-sm font-semibold mb-4">Milestone Timeline</h3>
                    <div class="space-y-0">
                        @forelse($milestones as $ms)
                            <div class="flex items-center justify-between text-xs py-1.5 border-b border-gray-200 last:border-0">
                                <div>
                                    <div class="font-semibold text-gray-700">{{ $ms->name }}</div>
                                    <div class="text-gray-500">
                                        ₹{{ number_format($ms->amount, 0) }}
                                        &nbsp;<span class="text-red-500">Due {{ $ms->due_date ? \Carbon\Carbon::parse($ms->due_date)->format('d M, y') : '-' }}</span>
                                    </div>
                                </div>
                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-0.5 rounded text-xs font-semibold">Pay Now</button>
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

            <div class="mt-4 pb-4">
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
            </div>

        </div>{{-- /openTop --}}


        {{-- ===== HOARDINGS SECTION ===== --}}
        <div class="bg-[#f7f7f7] px-5 py-3">

            <div class="flex items-center justify-between mb-3">
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
                    <div class="flex items-center justify-between bg-gray-100 px-4 py-2 rounded text-sm font-semibold border border-gray-300 mb-3">
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
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Sn.</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Hoarding</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Address</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Booking Start</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Booking End</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Booking Created</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Graphics Designer</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Price</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
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
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-600 font-medium">{{ $loop->iteration }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-14 h-14 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                                    @if($mediaItem)
                                                        <x-media-preview :media="$mediaItem" :alt="$bh->hoarding->title ?? 'Hoarding'" />
                                                    @else
                                                        <div class="w-full h-full bg-gray-300 flex items-center justify-center text-[9px] text-gray-500">
                                                            No Image
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 truncate max-w-[160px]">
                                                        {{ $bh->hoarding->title ?? '-' }}
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
                                        <td class="px-4 py-3">{{ strtoupper($bh->hoarding->hoarding_type ?? '-') }}</td>
                                        <td class="px-4 py-3">{{ $bh->hoarding->address ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $bh->start_date ? $bh->start_date->format('d M, Y') : '-' }}</td>
                                        <td class="px-4 py-3">{{ $bh->end_date ? $bh->end_date->format('d M, Y') : '-' }}</td>
                                        <td class="px-4 py-3">{{ $bh->created_at ? $bh->created_at->format('d M, Y') : '-' }}</td>
                                        <td class="px-4 py-3 text-gray-500">Not Assigned</td>
                                        <td class="px-4 py-3">₹{{ number_format($bh->hoarding_total, 2) }}</td>
                                        <td class="px-4 py-3 text-gray-400">
                                            {{ $bh->status ? ucwords(str_replace('_', ' ', $bh->status)) : '–' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-400">No hoardings found</td>
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
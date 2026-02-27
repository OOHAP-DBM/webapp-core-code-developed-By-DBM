@php
    $user = auth()->user();
    $layout = ($user && $user->hasRole('admin')) ? 'layouts.admin' : 'layouts.vendor';
@endphp
@extends($layout)
@section('title', 'Enquiries')
@section('content')
<div class="px-6 py-6 bg-white">
    {{-- ===== HEADER SECTION ===== --}}
    <div class="flex items-center justify-between py-1 bg-white mb-6">
        <div class="flex">
            <a href="{{ route('vendor.enquiries.index') }}" class="mx-2">
                <svg width="16" class="mt-2" height="10" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.414 7.91412L3.828 7.91412L8.328 12.4141L6.914 13.8281L-2.93326e-07 6.91412L6.914 0.000125592L8.328 1.41413L3.828 5.91412L15.414 5.91412L15.414 7.91412Z" fill="#3C3C3C"/>
                </svg>
            </a>
            <div>
                <h2 class="text-base font-semibold text-gray-900">
                Enquiry ID
                    <span class="text-green-600">({{ $enquiry->formatted_id }})</span>
                </h2>
                <p class="text-xs text-gray-500">Details of enquiry</p>
            </div>
        </div>
        <div></div>
    </div>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="space-y-6" x-data="{ openTop: true, openHoardings: true }">
        {{-- ===== TOP INFO SECTION: Accordion ===== --}}
        <div class="rounded mb-6">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 bg-[#f7f7f7]">
                <span class="font-semibold text-sm">Enquiry & Customer Details</span>
                <button @click="openTop = !openTop"
                    class="flex items-center cursor-pointer gap-1 text-xs bg-gray-200 px-3 py-1 rounded hover:bg-gray-300">
                    <span x-text="openTop ? 'Collapse' : 'Expand'"></span>
                    <svg class="w-4 h-4 transition-transform" :class="openTop ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
            <div x-show="openTop" x-transition class="px-6 py-6 bg-[#f7f7f7]">
                <div class="grid grid-cols-12 gap-8 bg-[#f7f7f7]">
                    {{-- Column 1: Customer Details --}}
                    <div class="col-span-4">
                        <h3 class="text-sm font-semibold mb-4">Customer Details</h3>
                        <div class="space-y-3 text-xs">
                            <div>Name : <span class="font-medium">{{ $enquiry->customer->name ?? '' }}</span></div>
                            <div>Business Name : <span>{{ $enquiry->customer->company_name ?? 'N/A' }}</span></div>
                            <div>GSTIN : <span>{{ $enquiry->customer->gstin ?? 'N/A' }}</span></div>
                            <div>Mobile : <span>{{ $enquiry->customer->phone ?? '' }}</span></div>
                            <div>Address : <span>{{ $enquiry->customer->address ?? '' }}</span></div>
                        </div>
                    </div>
                    {{-- Column 2: Enquiry Details --}}
                    <div class="col-span-4">
                        <h3 class="text-sm font-semibold mb-4">Enquiry Details</h3>
                        <div class="space-y-3 text-xs">
                            <div>Enquiry ID : <span class="font-medium">({{ $enquiry->formatted_id }})</span></div>
                            <div>
                                Campaign Starting From :
                                <span>{{ optional($enquiry->items->first())->preferred_start_date ? \Carbon\Carbon::parse($enquiry->items->first()->preferred_start_date)->format('d M, Y') : 'N/A' }}</span>
                            </div>
                            <div>Requested Hoardings : <span>{{ $enquiry->items->count() }}</span></div>
                            @if($enquiry->customer_note)
                                <div>Requirement : <span class="italic text-gray-700">{{ $enquiry->customer_note }}</span></div>
                            @endif
                        </div>
                    </div>
                    {{-- Column 3: Received Date --}}
                    <div class="col-span-4">
                        <h3 class="text-sm font-semibold mb-4">Received On</h3>
                        <span class="text-lg font-semibold leading-none">{{ $enquiry->created_at->format('d') }}</span>
                        <span class="text-sm text-gray-500">{{ $enquiry->created_at->format('M y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== HOARDINGS SECTION: Accordion ===== --}}
        <div class="rounded mb-6">
            <div class="flex items-center justify-between px-6 py-3">
                <span class="font-semibold text-sm">Hoardings / Screens ({{ $enquiry->items->count() }})</span>
                <button @click="openHoardings = !openHoardings"
                    class="flex items-center cursor-pointer gap-1 text-xs bg-gray-200 px-3 py-1 rounded hover:bg-gray-300">
                    <span x-text="openHoardings ? 'Collapse' : 'Expand'"></span>
                    <svg class="w-4 h-4 transition-transform" :class="openHoardings ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
            <div x-show="openHoardings" x-transition class="bg-[#f7f7f7] px-5 py-3">
                {{-- Group and display hoardings as in the modal --}}
                @php
                    $groups = collect($enquiry->items)->groupBy(fn($item) => strtoupper($item->hoarding_type ?? 'OOH'));
                    $typeDescriptions = [
                        'OOH' => 'Selected basic hoardings for the offer',
                        'DOOH' => 'Selected Digital Screens for the offer',
                        'DIGITAL-DOOH' => 'Selected Digital Screens for the offer',
                        'HOARDINGS' => 'Selected hoardings for the offer',
                    ]; 
                @endphp
                @foreach($groups as $type => $items)
                    <div class="mb-8">
                        <div class="flex items-center justify-between bg-gray-100 px-4 py-2 rounded text-sm font-semibold border border-gray-300 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-900">{{ $type }}</span>
                                <span class="text-gray-500 font-normal text-xs">({{ $items->count() }} items)</span>
                            </div>
                            <span class="text-xs text-gray-500 font-normal">{{ $typeDescriptions[$type] ?? 'Selected hoardings for the offer' }}</span>
                        </div>
                        <div class="overflow-x-auto border border-gray-200 rounded">
                            <table class="w-full text-xs">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="hidden md:table-cell px-4 py-3 text-left font-semibold text-gray-700">Sn.</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ $type === 'OOH' ? 'Hoarding' : 'Screen' }}</th>
                                        <th class="hidden lg:table-cell px-4 py-3 text-left font-semibold text-gray-700">Campaign Duration</th>
                                        <th class="hidden lg:table-cell px-4 py-3 text-left font-semibold text-gray-700">Campaign Start</th>
                                        <th class="hidden lg:table-cell px-4 py-3 text-left font-semibold text-gray-700">Selected Package</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $i => $item)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="hidden md:table-cell px-4 py-3 text-gray-600 font-medium">
                                            <span>{{ $i + 1 }}</span>
                                        </td>
                                        <td class="px-4 py-3 flex gap-3">
                                            <div class="w-14 h-14 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                                @php
                                                    $mediaItem = null;
                                                    if (($item->hoarding->hoarding_type ?? '') === 'ooh') {
                                                        $mediaItem = \Modules\Hoardings\Models\HoardingMedia::where('hoarding_id', $item->hoarding->id)
                                                            ->orderByDesc('is_primary')
                                                            ->orderBy('sort_order')
                                                            ->first();
                                                    } elseif (($item->hoarding->hoarding_type ?? '') === 'dooh') {
                                                        $screen = \Modules\DOOH\Models\DOOHScreen::where('hoarding_id', $item->hoarding->id)->first();
                                                        if ($screen) {
                                                            $mediaItem = \Modules\DOOH\Models\DOOHScreenMedia::where('dooh_screen_id', $screen->id)
                                                                ->orderBy('sort_order')
                                                                ->first();
                                                        }
                                                    }
                                                @endphp
                                                @if($mediaItem)
                                                    <x-media-preview :media="$mediaItem" :alt="$item->hoarding->title ?? 'Hoarding'" />
                                                @else
                                                    <div class="w-full h-full bg-gray-300 flex items-center justify-center text-[9px] text-gray-500">No Image</div>
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
                                                    <span>{{ $item->expected_duration ?? '0' }}</span>
                                                </p>
                                            </div>
                                        </td>
                                        <td class="hidden lg:table-cell px-4 py-3">
                                            <div class="space-y-1">
                                                <p class="font-medium text-gray-900">
                                                    <span>{{ $item->preferred_start_date ? \Carbon\Carbon::parse($item->preferred_start_date)->format('d M, Y') : 'N/A' }}</span>
                                                </p>
                                            </div>
                                        </td>
                                        <td class="hidden lg:table-cell px-4 py-3">
                                            <div class="space-y-1">
                                                <p class="font-medium text-gray-900">
                                                    @if($item->package_name && $item->package_name !== '-')
                                                        {{ $item->package_name }} – Offer {{ $item->discount_percent }}% Off
                                                    @else
                                                        -
                                                    @endif
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-900 font-medium">
                                            <span>₹{{ $item->final_price ?? '0' }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

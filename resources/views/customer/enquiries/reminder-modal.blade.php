@php
    $vendors = $enquiry->items->map(fn($item) => optional($item->hoarding)->vendor)
        ->filter()->unique('id')->values();

    $firstVendor = $vendors->first();

    $oohCount  = $enquiry->items->filter(fn($i) => strtolower($i->hoarding_type ?? '') === 'ooh')->count();
    $doohCount = $enquiry->items->filter(fn($i) => strtolower($i->hoarding_type ?? '') === 'dooh')->count();

    // Cities with count
    $citiesGrouped = $enquiry->items->groupBy(fn($item) => optional($item->hoarding)->city)
        ->filter(fn($g, $city) => $city)
        ->map(fn($g, $city) => $city . ' (' . $g->count() . ')');
    $citiesLabel = $citiesGrouped->values()->implode(' | ');
@endphp

{{-- REMINDER MODAL --}}
<div id="reminderModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
    <div class="bg-white shadow-xl w-full max-w-2xl relative overflow-hidden max-h-[95vh] flex flex-col mx-4 sm:mx-0">

        {{-- TOP GREEN BAR + CLOSE --}}
        <div class="bg-[#C4E7D9] h-9 flex items-center justify-end px-4 flex-shrink-0">
            <button onclick="closeReminderModal()"
                class="text-gray-700 hover:text-black text-lg font-bold leading-none cursor-pointer">
                ✕
            </button>
        </div>

        {{-- SCROLLABLE BODY --}}
        <div class="overflow-y-auto flex-1 px-3 sm:px-8 pt-6 pb-4">

            {{-- TITLE --}}
            <div class="text-center mb-1">
                <h2 class="text-xl font-semibold text-gray-800">
                    🔔 Send Reminder to Vendor
                </h2>
            </div>

            {{-- ENQUIRY ID + HOARDING COUNT --}}
            <div class="text-center text-sm text-gray-700 mb-5">
                Enquiry ID:
                <span class="text-[#00995c] font-semibold">{{ $enquiry->formatted_id }}</span>
                &nbsp;|&nbsp; #of Hoardings:
                <span class="text-[#00995c] font-semibold">{{ $enquiry->items->count() }}</span>
            </div>

            <hr class="border-gray-200 mb-5">

            {{-- 3-COLUMN INFO CARD --}}
            <div class="border border-gray-200 rounded-sm grid grid-cols-2 divide-x divide-gray-200 mb-5 text-xs text-gray-700">
                {{-- Enquiry Details --}}
                <div class="p-4 space-y-1.5">
                    <p class="font-semibold text-gray-900 mb-2">Enquiry Details</p>
                    <p><span class="font-semibold">Received On:</span>
                        {{ $enquiry->created_at->format('d M, y') }}
                    </p>
                    <p><span class="font-semibold">Enquiry ID:</span> {{ $enquiry->formatted_id }}</p>
                    <p><span class="font-semibold">Requested for Month:</span>
                        {{ $enquiry->items->first()?->preferred_start_date
                            ? \Carbon\Carbon::parse($enquiry->items->first()->preferred_start_date)->format('F')
                            : 'N/A' }}
                    </p>
                    <p><span class="font-semibold">Months Duration:</span>
                        {{ $enquiry->items->first()?->expected_duration ?? 'N/A' }}
                    </p>
                    <p><span class="font-semibold">Requested Hoardings:</span>
                        {{ $enquiry->items->count() }}
                    </p>
                    @if($enquiry->customer_note)
                        <p><span class="font-semibold">Requirement:</span>
                            <span class="font-semibold text-gray-800">{{ $enquiry->customer_note }}</span>
                        </p>
                    @endif
                </div>

                {{-- Hoarding Details --}}
                <div class="p-4 space-y-1.5">
                    <p class="font-semibold text-gray-900 mb-2">Hoarding Details</p>
                    <p>
                        Total Hoardings: <span class="font-semibold">{{ $enquiry->items->count() }}</span>
                        &nbsp;|&nbsp; OOH: <span class="text-[#00995c] font-semibold">{{ $oohCount }}</span>
                        &nbsp;|&nbsp; DOOH: <span class="text-[#2563eb] font-semibold">{{ $doohCount }}</span>
                    </p>
                    @if($citiesLabel)
                        <p>Including Cities : {{ $citiesLabel }}</p>
                    @endif
                </div>

            </div>

            {{-- SELECTED HOARDINGS TABLE --}}
            <div class="border border-gray-200 rounded-sm mb-5">
                <div class="px-4 py-3 border-b border-gray-200">
                    <p class="font-semibold text-gray-800 text-sm">Selected Hoardings (OOH & Digital)</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-white border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left text-gray-600 font-medium w-10">Sn.</th>
                                <th class="px-4 py-2 text-left text-gray-600 font-medium">Hoardings</th>
                                <th class="px-4 py-2 text-left text-gray-600 font-medium">Location</th>
                                <th class="px-4 py-2 text-left text-gray-600 font-medium">Size(Sq.Ft)</th>
                                <th class="px-4 py-2 text-left text-gray-600 font-medium">Rental</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($enquiry->items as $i => $item)
                                @php
                                    // Image
                                    $mediaItem = null;
                                    if (strtolower($item->hoarding_type ?? '') === 'ooh') {
                                        $mediaItem = \Modules\Hoardings\Models\HoardingMedia::where('hoarding_id', $item->hoarding_id)
                                            ->orderByDesc('is_primary')->orderBy('sort_order')->first();
                                    } elseif (strtolower($item->hoarding_type ?? '') === 'dooh') {
                                        $screen = \Modules\DOOH\Models\DOOHScreen::where('hoarding_id', $item->hoarding_id)->first();
                                        if ($screen) {
                                            $mediaItem = \Modules\DOOH\Models\DOOHScreenMedia::where('dooh_screen_id', $screen->id)
                                                ->orderBy('sort_order')->first();
                                        }
                                    }
                                    $hoarding = optional($item->hoarding);
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-600">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-10 bg-gray-200 rounded-sm overflow-hidden flex-shrink-0 border border-gray-200">
                                                @if($mediaItem)
                                                    <x-media-preview :media="$mediaItem" :alt="$hoarding->title ?? 'Hoarding'" class="w-full h-full object-cover" />
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-[9px] text-gray-400">No Img</div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800">
                                                    {{ \Illuminate\Support\Str::limit($hoarding->title ?? 'N/A', 25) }}
                                                </p>
                                                <p class="text-gray-400 text-[11px]">
                                                    {{ strtoupper($item->hoarding_type ?? '') }}-{{ $hoarding->sub_type ?? $hoarding->display_type ?? '' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        <span class="text-red-500 mr-1">📍</span>
                                        {{ $hoarding->locality ?? '' }} {{ $hoarding->city ?? '' }} {{ $hoarding->state ?? '' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $hoarding->size ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900">
                                        ₹{{ number_format($item->final_price ?? $hoarding->monthly_rate ?? 0) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">No hoardings found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- FOOTER --}}
        <div class="flex-shrink-0 px-3 sm:px-8 pb-6">
            <div class="flex items-center gap-2 mb-3">
                <input type="checkbox" id="confirmReminderCheck"
                    class="w-4 h-4 accent-gray-500 cursor-pointer">
                <label for="confirmReminderCheck" class="text-xs text-gray-600 cursor-pointer">
                    Would you like to Send this Reminder to the Vendor Now?
                </label>
            </div>

            <form action="" method="POST">
                @csrf
                <button type="submit"
                    class="w-full py-3 bg-[#4b5563] hover:bg-[#374151] text-white text-sm font-semibold transition-colors cursor-pointer">
                    Send Reminder
                </button>
            </form>
        </div>

    </div>
</div>
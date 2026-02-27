@php
    $isOOH  = $hoarding->hoarding_type === 'ooh';
    $isDOOH = $hoarding->hoarding_type === 'dooh';

    $ooh  = $hoarding->ooh;
    $dooh = $hoarding->doohScreen;
@endphp

<div class="space-y-5">

    <h3 class="text-sm font-semibold text-gray-900 flex justify-between">
        Hoarding Details
    </h3>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-y-4 text-sm">

        {{-- TYPE --}}
        <div>
            <p class="text-gray-400">Type</p>
            <p class="font-medium">{{ strtoupper($hoarding->hoarding_type) }}</p>
        </div>

        {{-- CATEGORY --}}
        <div>
            <p class="text-gray-400">Category</p>
            <p class="font-medium">{{ $hoarding->category ?? '—' }}</p>
        </div>

        {{-- SIZE --}}
        <div>
            <p class="text-gray-400">Size</p>
            <p class="font-medium">
                @if($isOOH && $ooh)
                    {{ $ooh->width ?? '—' }} × {{ $ooh->height ?? '—' }}
                    {{ strtoupper($ooh->measurement_unit ?? 'sqft') }}
                @elseif($isDOOH && $dooh)
                    {{ $dooh->width ?? '—' }} × {{ $dooh->height ?? '—' }}
                    {{ strtoupper($dooh->measurement_unit ?? 'sqm') }}
                @else
                    —
                @endif
            </p>
        </div>

        {{-- VALIDITY --}}
        <div>
            <p class="text-gray-400">Valid Till</p>
            <p class="font-medium">
              {{ $hoarding->permit_valid_till ? \Carbon\Carbon::parse($hoarding->permit_valid_till)->format('d-m-Y') : '—' }}
            </p>
        </div>

        {{-- LIGHTING / SCREEN TYPE --}}
        <div>
            <p class="text-gray-400">
                {{ $isOOH ? 'Lighting' : 'Screen Type' }}
            </p>
            <p class="font-medium">
                @if($isOOH)
                    {{ ucfirst($ooh->lighting_type ?? '—') }}
                @else
                    {{ strtoupper($dooh->screen_type ?? '—') }}
                @endif
            </p>
        </div>

    </div>

    {{-- EXTRA ROW FOR DOOH --}}
    @if($isDOOH && $dooh)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-y-4 text-sm pt-2">

            <!-- <div>
                <p class="text-gray-400">Resolution</p>
                <p class="font-medium">
                    {{ $dooh->resolution_width ?? '—' }} × {{ $dooh->resolution_height ?? '—' }}
                    {{ $dooh->resolution_unit ?? 'px' }}
                </p>
            </div> -->

            <div>
                <p class="text-gray-400">Spot Duration</p>
                <p class="font-medium">
                    {{ $dooh->slot_duration_seconds ?? '—' }} sec
                </p>
            </div>

            <div>
                <p class="text-gray-400">Slots Per Day</p>
                <p class="font-medium">
                    {{ $dooh->available_slots_per_day ?? '—' }}
                </p>
            </div>
<!-- 
            <div>
                <p class="text-gray-400">Price / Slot</p>
                <p class="font-medium">
                    ₹{{ number_format($dooh->price_per_slot ?? 0) }}
                </p>
            </div> -->

        </div>
    @endif

    {{-- APPROVAL --}}
    <p class="text-sm text-gray-600">
        Approved From Nagar Nigam
        <span class="font-medium ml-1">
            {{ $hoarding->nagar_nigam_approved ? 'Yes' : 'No' }}
        </span>
    </p>

</div>

<hr class="my-6 border-gray-300">

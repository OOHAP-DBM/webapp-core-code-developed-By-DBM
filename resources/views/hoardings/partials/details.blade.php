@php
    $isOOH  = $hoarding->hoarding_type === 'ooh';
    $isDOOH = $hoarding->hoarding_type === 'dooh';

    $ooh  = $hoarding->ooh;
    $dooh = $hoarding->doohScreen;
    
    // Check if we have valid data to display
    $hasSize = ($isOOH && $ooh) || ($isDOOH && $dooh);
    $hasValidity = !empty($hoarding->permit_valid_till);
    $hasLighting = ($isOOH && !empty($ooh->lighting_type)) || ($isDOOH && !empty($dooh->screen_type));
    $hasValidData = $hasSize || $hasValidity || $hasLighting;
@endphp

@if($hasValidData)
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
        @if($hasSize)
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
        @endif

        {{-- VALIDITY --}}
        @if($hasValidity)
        <div>
            <p class="text-gray-400">Valid Till</p>
            <p class="font-medium">
              {{ $hoarding->permit_valid_till ? \Carbon\Carbon::parse($hoarding->permit_valid_till)->format('d-m-Y') : '—' }}
            </p>
        </div>
        @endif

        {{-- LIGHTING / SCREEN TYPE --}}
        @if($hasLighting)
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
        @endif

    </div>

    {{-- EXTRA ROW FOR DOOH --}}
    @if($isDOOH && $dooh)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-y-4 text-sm pt-2">

            <div>
                <p class="text-gray-400">Spot Duration</p>
                <p class="font-medium">
                    {{ $dooh->slot_duration_seconds ?? '—' }} sec
                </p>
            </div>

            <div>
                <p class="text-gray-400">Slots Per Day</p>
                <p class="font-medium">
                    {{ $dooh->total_slots_per_day ?? '—' }}
                </p>
            </div>

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

<hr class="my-6 border-gray-200">
@endif

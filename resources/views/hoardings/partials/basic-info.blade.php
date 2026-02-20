@php
    $isOOH  = $hoarding->hoarding_type === 'ooh';
    $isDOOH = $hoarding->hoarding_type === 'dooh';

    $ooh  = $hoarding->ooh;
    $dooh = $hoarding->doohScreen;
@endphp

<div class="space-y-4">

    {{-- OFFER TIMER (optional, static for now) --}}
    {{-- <div class="inline-flex items-center gap-2 bg-red-500 text-white text-xs font-semibold px-3 py-1 rounded-md">
        🔥 Hurry, offers ends in
        <span class="bg-red-600 px-2 py-0.5 rounded text-white">
            07 : 44 : 33
        </span>
    </div> --}}

    {{-- TITLE --}}
    <h2 class="text-lg font-semibold text-gray-900">
        {{ $hoarding->title ?? '—' }}
    </h2>

    {{-- LOCATION --}}
    <p class="text-sm text-gray-500 flex items-center gap-1">
        📍 {{ $hoarding->address ?? '—' }}
        @if($hoarding->city)
            , {{ $hoarding->city }}
        @endif
    </p>

    {{-- META LINE (TYPE | SIZE | CATEGORY) --}}
    <p class="text-sm text-gray-600">

        {{-- TYPE --}}
        {{ strtoupper($hoarding->hoarding_type) }}

        |

        {{-- SIZE --}}
        @if($isOOH && $ooh)
            {{ $ooh->width ?? '—' }}×{{ $ooh->height ?? '—' }}
            {{ strtoupper($ooh->measurement_unit ?? 'sqft') }}
        @elseif($isDOOH && $dooh)
            {{ $dooh->width ?? '—' }}×{{ $dooh->height ?? '—' }}
            {{ strtoupper($dooh->measurement_unit ?? 'sqm') }}
        @else
            —
        @endif

        |

        {{-- CATEGORY --}}
        {{ ucfirst($hoarding->category ?? '—') }}

    </p>

    <!-- {{-- EXTRA META FOR DOOH --}}
    @if($isDOOH && $dooh)
        <p class="text-xs text-gray-500">
            Resolution:
            {{ $dooh->resolution_width ?? '—' }}×{{ $dooh->resolution_height ?? '—' }}
            {{ $dooh->resolution_unit ?? 'px' }}
            • Slot {{ $dooh->slot_duration_seconds ?? '—' }}s
        </p>
    @endif -->

    {{-- RATING (static for now) --}}
    <div class="flex items-center gap-2 text-sm">
        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded font-medium">
            ⭐ {{ $hoarding->rating ?? '0' }}
        </span>
        <span class="text-gray-500">
            / {{ $hoarding->reviews_count ?? '0' }} Reviews
        </span>
    </div>

    {{-- CHAT CTA --}}
    {{-- <button
        class="border border-gray-300 text-sm px-4 py-2 rounded-md text-gray-600 hover:bg-gray-50">
        Chat with Hoarding Owner
    </button> --}}

</div>

<hr class="my-6 border-gray-300">

<div class="space-y-4">

    {{-- OFFER TIMER --}}
    <div class="inline-flex items-center gap-2 bg-red-500 text-white text-xs font-semibold px-3 py-1 rounded-md">
        🔥 Hurry, offers ends in
        <span class="bg-red-600 px-2 py-0.5 rounded text-white">
            07 : 44 : 33
        </span>
    </div>

    <h2 class="text-lg font-semibold text-gray-900">
        {{ $hoarding->title }}
    </h2>

    <p class="text-sm text-gray-500 flex items-center gap-1">
        📍 {{ $hoarding->address }}
    </p>

    <p class="text-sm text-gray-600">
        {{ strtoupper($hoarding->hoarding_type) }}
        |
        {{ $hoarding->width ?? '—' }}×{{ $hoarding->height ?? '—' }} {{ $hoarding->measurement_unit ?? 'Sq.ft' }}
        |
        {{ ucfirst($hoarding->category ?? '—') }}
    </p>

    {{-- Rating --}}
    <div class="flex items-center gap-2 text-sm">
        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded font-medium">
            ⭐ 4.5
        </span>
        <span class="text-gray-500">/ 335 Reviews</span>
    </div>

    {{-- Chat --}}
    <button class="border border-gray-300 text-sm px-4 py-2 rounded-md text-gray-600 hover:bg-gray-50">
        Chat with Hoarding Owner
    </button>

</div>

<hr class="my-6 border-gray-300">

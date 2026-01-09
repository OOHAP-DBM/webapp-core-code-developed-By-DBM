<div class="space-y-5">

    <h3 class="text-sm font-semibold text-gray-900 flex justify-between">
        Hoarding Details
        <span class="text-gray-400">—</span>
    </h3>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-y-4 text-sm">

        <div>
            <p class="text-gray-400">Type</p>
            <p class="font-medium">{{ strtoupper($hoarding->hoarding_type) }}</p>
        </div>

        <div>
            <p class="text-gray-400">Category</p>
            <p class="font-medium">{{ $hoarding->category ?? '—' }}</p>
        </div>

        <div>
            <p class="text-gray-400">Size</p>
            <p class="font-medium">
                {{ $hoarding->width ?? '—' }}×{{ $hoarding->height ?? '—' }} {{ $hoarding->measurement_unit ?? 'Sq.ft' }}
            </p>
        </div>

        <div>
            <p class="text-gray-400">Validity</p>
            <p class="font-medium">
                {{ optional($hoarding->available_from)->format('M d, Y') }}
                to
                {{ optional($hoarding->available_to)->format('M d, Y') }}
            </p>
        </div>

        <div>
            <p class="text-gray-400">Lightening</p>
            <p class="font-medium">{{ $hoarding->lighting_type ?? '—' }}</p>
        </div>

    </div>

    <p class="text-sm text-gray-600">
        Approved From Nagar Nigam
        <span class="font-medium ml-1">Yes</span>
    </p>

</div>

<hr class="my-6 border-gray-300">

<div class="p-4 bg-white">
    <!-- Section Title -->
    <h3 class="text-base font-semibold mb-4 text-[var(--accent-color)]">
        Hoarding Location
    </h3>

    <!-- Location Details -->
    <div class="space-y-3 text-sm">
        <div class="grid grid-cols-3 gap-4">
            <span class="text-gray-500">Address</span>
            <span class="col-span-2 font-medium text-gray-900">
                {{ $hoarding->address ?? '-' }}
            </span>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <span class="text-gray-500">Locality</span>
            <span class="col-span-2 font-medium text-gray-900">
                {{ $hoarding->locality ?? '-' }}
            </span>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <span class="text-gray-500">Landmark</span>
            <span class="col-span-2 font-medium text-gray-900">
                {{ $hoarding->landmark ?? '-' }}
            </span>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <span class="text-gray-500">Pincode</span>
            <span class="col-span-2 font-medium text-gray-900">
                {{ $hoarding->pincode ?? '-' }}
            </span>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <span class="text-gray-500">City</span>
            <span class="col-span-2 font-medium text-gray-900">
                {{ $hoarding->city ?? '-' }}
            </span>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <span class="text-gray-500">State</span>
            <span class="col-span-2 font-medium text-gray-900">
                {{ $hoarding->state ?? '-' }}
            </span>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <span class="text-gray-500">Country</span>
            <span class="col-span-2 font-medium text-gray-900">
                {{ $hoarding->country ?? '-' }}
            </span>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <span class="text-gray-500">Geotag Link</span>
            <span class="col-span-2 font-medium">
                @if($hoarding->geotag_link)
                    <a href="{{ $hoarding->geotag_link }}" target="_blank" class="text-emerald-500 hover:underline">{{ $hoarding->geotag_link }}</a>
                @else
                    -
                @endif
            </span>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <span class="text-gray-500">Google Map Address</span>
            <span class="col-span-2 font-medium text-gray-900">
                {{ $hoarding->google_map_address ?? '-' }}
            </span>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <span class="text-gray-500">Coordinates</span>
            <span class="col-span-2 text-xs text-emerald-500 font-medium">
                L: {{ $hoarding->latitude ?? '-' }}
                <span class="mx-2 text-gray-300">|</span>
                L: {{ $hoarding->longitude ?? '-' }}
            </span>
        </div>
    </div>
</div>

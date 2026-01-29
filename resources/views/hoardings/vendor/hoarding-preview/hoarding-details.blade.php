<div class="p-4">
<h3 class="text-base font-semibold mb-4 text-[var(--accent-color)]">
    Hoarding Details
</h3>
<div class="space-y-3 text-sm">
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Hoarding Type</span>
        <span class="col-span-2 font-medium text-gray-900">{{ strtoupper($hoarding->hoarding_type ?? '-') }}</span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Category</span>
        <span class="col-span-2 font-medium text-gray-900">{{ ucfirst($hoarding->category ?? '-') }}</span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Title</span>
        <span class="col-span-2 font-medium text-gray-900">{{ $hoarding->title ?? '-' }}</span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Status</span>
        <span class="col-span-2 font-semibold {{ $hoarding->status === 'active' ? 'text-green-600' : 'text-orange-500' }}">{{ ucfirst($hoarding->status ?? '-') }}</span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Size</span>
        <span class="col-span-2 font-medium text-gray-900">
            @if($hoarding->hoarding_type === 'ooh' && $hoarding->ooh)
                {{ $hoarding->ooh->width ?? '-' }} × {{ $hoarding->ooh->height ?? '-' }} {{ strtoupper($hoarding->ooh->measurement_unit ?? 'sqft') }}
            @elseif($hoarding->hoarding_type === 'dooh' && $hoarding->doohScreen)
                {{ $hoarding->doohScreen->width ?? '-' }} × {{ $hoarding->doohScreen->height ?? '-' }} {{ strtoupper($hoarding->doohScreen->measurement_unit ?? 'sqm') }}
            @else
                -
            @endif
        </span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Available From</span>
        <span class="col-span-2 font-medium text-gray-900">{{ optional($hoarding->available_from)->format('M d, Y') ?? '-' }}</span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Available To</span>
        <span class="col-span-2 font-medium text-gray-900">{{ optional($hoarding->available_to)->format('M d, Y') ?? '-' }}</span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Lighting / Screen Type</span>
        <span class="col-span-2 font-medium text-gray-900">
            @if($hoarding->hoarding_type === 'ooh' && $hoarding->ooh)
                {{ ucfirst($hoarding->ooh->lighting_type ?? '-') }}
            @elseif($hoarding->hoarding_type === 'dooh' && $hoarding->doohScreen)
                {{ strtoupper($hoarding->doohScreen->screen_type ?? '-') }}
            @else
                -
            @endif
        </span>
    </div>
    @if($hoarding->hoarding_type === 'dooh' && $hoarding->doohScreen)
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Resolution</span>
        <span class="col-span-2 font-medium text-gray-900">
            {{ $hoarding->doohScreen->resolution_width ?? '-' }} × {{ $hoarding->doohScreen->resolution_height ?? '-' }} {{ $hoarding->doohScreen->resolution_unit ?? 'px' }}
        </span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Slot Duration</span>
        <span class="col-span-2 font-medium text-gray-900">{{ $hoarding->doohScreen->slot_duration_seconds ?? '-' }} sec</span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Slots / Day</span>
        <span class="col-span-2 font-medium text-gray-900">{{ $hoarding->doohScreen->available_slots_per_day ?? '-' }}</span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Price / Slot</span>
        <span class="col-span-2 font-medium text-gray-900">₹{{ number_format($hoarding->doohScreen->price_per_slot ?? 0) }}</span>
    </div>
    @endif
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Expected Footfall</span>
        <span class="col-span-2 font-medium text-gray-900">{{ number_format($hoarding->expected_footfall ?? 0) }}</span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Expected Eyeball</span>
        <span class="col-span-2 font-medium text-gray-900">{{ number_format($hoarding->expected_eyeball ?? 0) }}</span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Audience Types</span>
        <span class="col-span-2 font-medium text-gray-900">
            @if(is_array($hoarding->audience_types) && count($hoarding->audience_types))
                {{ implode(', ', $hoarding->audience_types) }}
            @else
                -
            @endif
        </span>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <span class="text-gray-500">Approved From Nagar Nigam</span>
        <span class="col-span-2 font-semibold {{ $hoarding->nagar_nigam_approved ? 'text-green-600' : 'text-red-500' }}">
            {{ $hoarding->nagar_nigam_approved ? 'Yes' : 'No' }}
        </span>
    </div>
</div>
</div>
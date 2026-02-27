<div class="p-4 bg-white">
    <!-- Section Title -->
    <h3 class="text-base font-semibold mb-5 text-[var(--accent-color)]">
        Hoarding Attributes
    </h3>

    <!-- Visible From -->
    <div class="mb-6">
        <h4 class="text-sm font-semibold text-emerald-600 mb-3">
            Visible From
        </h4>
        <div class="flex flex-wrap gap-3 text-sm">
            @php
                $visibleFrom = is_string($hoarding->visible_from ?? null)
                    ? json_decode($hoarding->visible_from, true)
                    : ($hoarding->visible_from ?? []);
            @endphp
            @if(is_array($visibleFrom) && count($visibleFrom))
                @foreach($visibleFrom as $item)
                    <div class="flex items-center gap-2 px-3 py-2 rounded bg-gray-50">
                        <span class="text-gray-700">{{ $item }}</span>
                        <span class="font-semibold text-blue-600">Yes</span>
                    </div>
                @endforeach
            @else
                <div class="flex items-center gap-2 px-3 py-2 rounded bg-gray-50">
                    <span class="text-gray-700">Not Provided</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Located At -->
    <div class="mb-6">
        <h4 class="text-sm font-semibold text-emerald-600 mb-3">
            Located at
        </h4>
        <div class="space-y-2 text-sm">
            @php
                $locatedAt = is_string($hoarding->located_at ?? null)
                    ? json_decode($hoarding->located_at, true)
                    : ($hoarding->located_at ?? []);
            @endphp
            @if(is_array($locatedAt) && count($locatedAt))
                @foreach($locatedAt as $key => $val)
                    <div class="grid grid-cols-3 gap-4">
                        <span class="text-gray-500">{{ ucwords(str_replace('_',' ',$key+1)) }}</span>
                        <span class="col-span-2 font-medium text-gray-900">{{ $val ?: '-' }}</span>
                    </div>
                @endforeach
            @else
                <div class="grid grid-cols-3 gap-4">
                    <span class="text-gray-500">Not Provided</span>
                    <span class="col-span-2 font-medium text-gray-900">-</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Hoarding Visibility -->
    <div>
        <h4 class="text-sm font-semibold text-emerald-600 mb-3">
            Hoarding Visibility
        </h4>
        <div class="space-y-3 text-sm">
            <div class="grid grid-cols-3 gap-4">
                <span class="text-gray-500">Visibility</span>
                <span class="col-span-2 font-medium text-gray-900">
                    {{ ucfirst(str_replace('_', ' ', $hoarding->hoarding_visibility ?? '-')) }}
                </span>
            </div>
            @if(is_array($hoarding->visibility_details) && count($hoarding->visibility_details))
                <div class="grid grid-cols-3 gap-4">
                    <span class="text-gray-500">Details</span>
                    <span class="col-span-2 font-medium text-gray-900">
                        {{ implode(', ', $hoarding->visibility_details) }}
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>

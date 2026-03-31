@php
    $locatedAt = $hoarding->located_at;
    if (is_string($locatedAt)) {
        $locatedAt = json_decode($locatedAt, true);
    }
    $hasLocatedAt = !empty($locatedAt) || !empty($hoarding->road_name);
    $hasVisibility = !empty($hoarding->hoarding_visibility) && $hoarding->hoarding_visibility !== '—';
    $hasVisibilityDetails = is_array($hoarding->visibility_details) && count($hoarding->visibility_details) > 0;
@endphp

@if($hasLocatedAt || $hasVisibility || $hasVisibilityDetails)
<div class="max-w-7xl mx-auto px-4 pt-6  border-t border-gray-200">

    <h3 class="text-base font-semibold mb-4">Hoarding Attributes</h3>

    {{-- Located At --}}
    @if($hasLocatedAt)
    <div class="mb-5">
        <p class="text-sm font-medium mb-1">Located At</p>
        <p class="text-sm text-gray-700">
            {{ !empty($locatedAt) ? implode(' , ', $locatedAt) : '—' }}
        </p>
        <p class="text-sm text-gray-500">{{ $hoarding->road_name ?? '' }}</p>
    </div>
    @endif

    {{-- Hoarding Visibility --}}
    @if($hasVisibility || $hasVisibilityDetails)
    <div class="">
        <p class="text-sm font-medium mb-2">Hoarding Visibility</p>
        <p class="text-sm text-gray-700 mb-2">
            {{ ucfirst(str_replace('_', ' ', $hoarding->hoarding_visibility ?? '—')) }}
        </p>
        @if($hasVisibilityDetails)
            <div class="flex flex-wrap gap-2">
                @foreach($hoarding->visibility_details as $detail)
                    <span class="px-3 py-1 text-xs border border-gray-200 rounded  text-gray-700">
                        {{ $detail }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
    @endif

</div>
@endif
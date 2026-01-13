<div class="max-w-7xl mx-auto px-4 py-6 border-t border-gray-300">

    <h3 class="text-base font-semibold mb-4">Hoarding Attributes</h3>

    {{-- Visible From --}}
    <div class="mb-5">
        <p class="text-sm font-medium mb-2">Visible from</p>
        <div class="flex flex-wrap gap-2">
            @foreach(($hoarding->visible_from ?? []) as $item)
                <span class="px-3 py-1 text-sm border border-gray-300 rounded text-gray-700 bg-white">
                    {{ $item }}
                </span>
            @endforeach
        </div>
    </div>

    {{-- Located At --}}
    <div class="mb-5">
        <p class="text-sm font-medium mb-1">Located At</p>
        <p class="text-sm text-gray-700">
            {{ is_string($hoarding->located_at) ? $hoarding->located_at : '—' }}
        </p>
        <p class="text-sm text-gray-500">{{ $hoarding->road_name ?? '' }}</p>
    </div>

  {{-- Hoarding Visibility --}}
<div class="mb-5">
    <p class="text-sm font-medium mb-2">Hoarding Visibility</p>

    <p class="text-sm text-gray-700 mb-2">
        {{ ucfirst(str_replace('_', ' ', $hoarding->hoarding_visibility ?? '—')) }}
    </p>

    @if(is_array($hoarding->visibility_details) && count($hoarding->visibility_details))
        <div class="flex flex-wrap gap-2">
            @foreach($hoarding->visibility_details as $detail)
                <span class="px-3 py-1 text-xs border rounded bg-gray-100 text-gray-700">
                    {{ $detail }}
                </span>
            @endforeach
        </div>
    @else
        <span class="text-sm text-gray-500">—</span>
    @endif
</div>


</div>

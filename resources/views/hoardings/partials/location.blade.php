@if($hoarding->latitude && $hoarding->longitude)
<div class="space-y-3 pb-5">
    <h3 class="text-sm font-semibold">Location</h3>

    {{-- <div class="flex gap-6 text-sm text-gray-600">
        <p>Latitude: {{ $hoarding->latitude ?? '-' }}</p>
        <p>Longitude: {{ $hoarding->longitude ?? '-' }}</p>
    </div> --}}
</div>

<iframe
    src="https://www.google.com/maps?q={{ $hoarding->latitude }},{{ $hoarding->longitude }}&z=15&output=embed"
    width="100%"
    height="280"
    style="border-radius:5px;border:0;"
    loading="lazy"
    referrerpolicy="no-referrer-when-downgrade">
</iframe>

{{-- <hr class="my-6 border-gray-300"> --}}
@endif

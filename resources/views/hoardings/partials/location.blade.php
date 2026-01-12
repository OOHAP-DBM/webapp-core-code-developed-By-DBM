<div class="space-y-3 pb-5">
    <h3 class="text-sm font-semibold">Location</h3>

    <div class="flex gap-6 text-sm text-gray-600">
        <p>Latitude: {{ $hoarding->lat }}</p>
        <p>Longitude: {{ $hoarding->lng }}</p>
    </div>
</div>

<iframe
    src="https://www.google.com/maps?q={{ $hoarding->lat }},{{ $hoarding->lng }}&z=15&output=embed"
    width="100%" height="280"
    style="border-radius:12px;border:0;">
</iframe>

<div>
    <h3 class="text-sm font-semibold mb-3">Audience Type</h3>

    <div class="flex flex-wrap gap-3 text-sm text-gray-700">
        @foreach(($hoarding->audience_types ?? []) as $audience)
            <span class="flex items-center gap-1">
                ✔ {{ $audience }}
            </span>
        @endforeach
    </div>
</div>

<hr class="my-6 border-gray-300">

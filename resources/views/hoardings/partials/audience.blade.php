<div>
    <h3 class="text-sm font-semibold mb-3">Audience Type</h3>
    <div class="flex flex-wrap gap-3 text-sm text-gray-700">
        @forelse($hoarding->audience_types as $audience)
            <span class="flex items-center gap-1">
                ✔ {{ $audience }}
            </span>
        @empty
            <span class="text-gray-400 text-sm">No audience data available</span>
        @endforelse
    </div>
</div>

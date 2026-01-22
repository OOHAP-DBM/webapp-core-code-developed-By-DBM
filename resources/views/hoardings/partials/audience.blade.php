<div>
    <h3 class="text-sm font-semibold mb-3">Audience Type</h3>
    <div class="flex flex-wrap gap-3 text-sm text-gray-700">
        @forelse($hoarding->audience_types as $audience)
            <span class="flex items-center gap-1">
                <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 7L5.5 11.5L14 3" stroke="#1E1B18" stroke-linecap="square"/>
                </svg>
                {{ $audience }}
            </span>
        @empty
            <span class="text-gray-400 text-sm">No audience data available</span>
        @endforelse
    </div>
</div>

<div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group cursor-pointer" onclick="window.location.href='{{ route('dooh.show', $dooh->id) }}'">
    <!-- Image -->
    <div class="relative h-48 overflow-hidden bg-gradient-to-br from-pink-100 to-orange-100">
        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-pink-500 to-orange-600">
            <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
        </div>
        
        <!-- Type Badge -->
        <div class="absolute top-3 right-3">
            <span class="px-3 py-1 bg-white/90 backdrop-blur-sm text-xs font-semibold text-gray-800 rounded-full">
                {{ ucfirst($dooh->screen_type) }}
            </span>
        </div>

        <!-- Digital Badge -->
        <div class="absolute top-3 left-3">
            <span class="px-3 py-1 bg-gradient-to-r from-pink-500 to-orange-500 text-white text-xs font-semibold rounded-full flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"></path>
                </svg>
                DIGITAL
            </span>
        </div>
    </div>

    <!-- Content -->
    <div class="p-5">
        <!-- Title -->
        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-pink-600 transition-colors">
            {{ $dooh->name }}
        </h3>

        <!-- Location -->
        <div class="flex items-start text-sm text-gray-600 mb-3">
            <svg class="w-4 h-4 mr-1 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span class="line-clamp-2">{{ $dooh->city }}, {{ $dooh->state }}</span>
        </div>

        <!-- Specs -->
        <div class="grid grid-cols-2 gap-2 mb-4">
            <div class="text-xs text-gray-600">
                <span class="font-semibold">Resolution:</span> {{ $dooh->resolution }}
            </div>
            <div class="text-xs text-gray-600">
                <span class="font-semibold">Slots/Day:</span> {{ $dooh->total_slots_per_day }}
            </div>
        </div>

        <!-- Price -->
        <div class="flex items-baseline justify-between mb-4">
            <div>
                <span class="text-2xl font-bold text-pink-600">₹{{ number_format($dooh->price_per_slot, 0) }}</span>
                <span class="text-sm text-gray-500">/slot</span>
            </div>
        </div>

        <!-- Action Button -->
        <button class="w-full py-2.5 bg-gradient-to-r from-pink-600 to-orange-600 text-white rounded-lg font-semibold hover:from-pink-700 hover:to-orange-700 transition-all flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            View Details
        </button>
    </div>
</div>

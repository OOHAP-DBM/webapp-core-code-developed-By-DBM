<div class="relative rounded-xl overflow-hidden group cursor-pointer h-48 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-2xl">
    <!-- Background Image -->
    <img 
        src="{{ $city['image'] }}" 
        alt="{{ $city['name'] }}"
        class="w-full h-full object-cover"
    >
    
    <!-- Overlay Gradient -->
    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent group-hover:from-black/90 transition-all duration-300"></div>
    
    <!-- Content -->
    <div class="absolute inset-0 flex flex-col justify-end p-6">
        <h3 class="text-2xl md:text-3xl font-bold text-white mb-2 transform group-hover:translate-y-[-4px] transition-transform duration-300">
            {{ $city['name'] }}
        </h3>
        <div class="flex items-center text-white/90 text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <span class="font-medium">{{ $city['count'] }} Hoardings</span>
        </div>

        <!-- Explore Button (shows on hover) -->
        <div class="mt-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <button class="px-4 py-2 bg-white text-gray-900 rounded-lg font-semibold text-sm hover:bg-gray-100 transition-colors flex items-center">
                <span>Explore</span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Corner Accent -->
    <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-bl from-white/20 to-transparent"></div>
</div>

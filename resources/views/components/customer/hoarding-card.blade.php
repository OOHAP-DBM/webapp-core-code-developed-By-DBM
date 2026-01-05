<div class="bg-white rounded-lg border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden group cursor-pointer" onclick="window.location.href='{{ route('hoardings.show', $hoarding->id) }}';">
    <!-- Image -->
    <div class="relative h-48 overflow-hidden bg-gray-100">
        @php
            $hasImage = false;
            $imageUrl = '';
            
            if (is_callable([$hoarding, 'hasMedia']) && $hoarding->hasMedia('images')) {
                $hasImage = true;
                $imageUrl = $hoarding->getFirstMediaUrl('images');
            } elseif (isset($hoarding->image)) {
                $hasImage = true;
                $imageUrl = $hoarding->image;
            }
        @endphp
        
        @if($hasImage)
            <img 
                src="{{ $imageUrl }}" 
                alt="{{ $hoarding->title }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            >
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif
        
        <!-- Recommended Badge -->
        <div class="absolute top-3 left-3">
            <span class="px-2.5 py-1 bg-gradient-to-r from-pink-500 to-red-500 text-white text-xs font-semibold rounded">
                Recommended
            </span>
        </div>

        <!-- Top Right Icons -->
        <div class="absolute top-3 right-3 flex items-center space-x-2">
            <!-- Bookmark Icon -->
            <button class="w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white transition-colors" onclick="event.stopPropagation();">
                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </button>
            <!-- Info Icon -->
            <button class="w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white transition-colors" onclick="event.stopPropagation();">
                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
        </div>

        <!-- View Icon (bottom-left) -->
        <div class="absolute bottom-3 left-3">
            <div class="w-8 h-8 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="p-4">
        <!-- Location with Rating -->
        <div class="flex items-start justify-between mb-2">
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-gray-900 mb-0.5 line-clamp-1">
                    {{ $hoarding->title ?? 'Udaipur | Hiramagri Chouraha' }}
                </h3>
                <p class="text-xs text-gray-500">OOH - IMT-014eeft</p>
            </div>
            <div class="flex items-center ml-2">
                <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                </svg>
                <span class="text-xs font-semibold text-gray-700 ml-1">4.5</span>
            </div>
        </div>

        <!-- Price -->
        <div class="mb-3">
            <div class="flex items-baseline">
                <span class="text-xl font-bold text-gray-900">₹{{ number_format($hoarding->monthly_price ?? 10999, 0) }}</span>
                <span class="text-sm text-gray-500 ml-1">/Month</span>
            </div>
            <div class="flex items-center space-x-2 mt-1">
                <span class="text-xs text-gray-400 line-through">₹16,999</span>
                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">₹3,000 (61%)</span>
            </div>
        </div>

        <!-- Availability -->
        <p class="text-xs text-gray-600 mb-1">
            Hoarding Available from December 25
        </p>
        <p class="text-xs text-teal-600 font-medium mb-3">3 Packages Available</p>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-2 mb-2">
            @php
                $isInCart = in_array($hoarding->id, $cartIds ?? []);
            @endphp

            <button
                type="button"
                id="cart-btn-{{ $hoarding->id }}"
                data-state="{{ $isInCart ? 'remove' : 'add' }}"
                onclick="event.stopPropagation(); toggleCart({{ $hoarding->id }})"
                class="flex-1 py-2 px-3 text-sm font-semibold rounded
                    {{ $isInCart
                        ? 'bg-red-400 text-white hover:bg-red-500'
                        : 'border border-gray-300 text-gray-700 hover:bg-gray-50'
                    }}">
                {{ $isInCart ? 'Remove' : 'Add to Cart' }}
            </button>



            <button class="flex-1 py-2 px-3 bg-teal-500 text-white text-sm font-semibold rounded hover:bg-teal-600 transition-colors"
                onclick="window.location.href='{{ route('hoardings.show', $hoarding->id) }}'">
                Book Now
            </button>
        </div>


        <!-- Enquire Link -->
        <a href="#" class="block text-center text-xs text-teal-600 hover:text-teal-700 font-medium" onclick="event.stopPropagation();">
            Enquire Now
        </a>
    </div>
</div>

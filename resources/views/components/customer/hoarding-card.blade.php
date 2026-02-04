<div class="bg-white rounded-lg border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden group cursor-pointer flex flex-col h-full" onclick="if(event.target.closest('button') === null) window.location.href='{{ route('hoardings.show', $hoarding->id) }}';">
    <!-- Image -->
    <div class="relative h-48 overflow-hidden bg-gray-100">
    @php
        $imageUrl = null;

        if ($hoarding->hoarding_type === 'ooh'
            && $hoarding->hoardingMedia->isNotEmpty()) {

            $imageUrl = asset(
                'storage/' . $hoarding->hoardingMedia->first()->file_path
            );
        }

        if (
                $hoarding->hoarding_type === 'dooh'
                && $hoarding->doohScreen
                && $hoarding->doohScreen->media->isNotEmpty()
            ) {
                $imageUrl = asset(
                    'storage/' . $hoarding->doohScreen->media
                        ->sortBy('sort_order')
                        ->first()
                        ->file_path
                );
            }
        @endphp

    @if($imageUrl)
        <img src="{{ $imageUrl }}"
            class="w-full h-full object-cover">
    @else
        <div class="w-full h-full flex items-center justify-center bg-gray-200">
            No Image {{ $hoarding->id }} | {{ $hoarding->hoarding_type }}
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
             @php
                $isWishlisted = auth()->check()
                    ? auth()->user()->wishlist()->where('hoarding_id', $hoarding->id)->exists()
                    : false;
            @endphp
            @php
                $isOwnerVendor = false;
                if (
                    auth()->check()
                    && optional(auth()->user())->active_role === 'vendor'
                    && isset($hoarding->vendor_id)
                    && auth()->id() === (int) $hoarding->vendor_id
                ) {
                    $isOwnerVendor = true;
                }
            @endphp
            @if(!$isOwnerVendor)
            <button
                class="w-8 h-8 rounded-full flex items-center justify-center shortlist-btn
                    {{ $isWishlisted ? 'bg-[#daf2e7] is-wishlisted' : 'bg-[#9e9e9b]' }} {{ $isOwnerVendor ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}"
                data-id="{{ $hoarding->id }}"
                data-auth="{{ auth()->check() ? '1' : '0' }}"
                data-role="{{ auth()->check() ? auth()->user()->role : '' }}"
                @if($isOwnerVendor)
                    disabled
                @else
                    onclick="event.stopPropagation(); toggleShortlist(this);"
                @endif
            >
            

                    <svg
                        class="wishlist-icon"
                        width="20"
                        height="19"
                        viewBox="0 0 20 19"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M5.5 0.75C2.877 0.75 0.75 3.01 0.75 5.797C0.75 11.375 9.75 17.75 9.75 17.75C9.75 17.75 18.75 11.375 18.75 5.797C18.75 2.344 16.623 0.75 14 0.75C12.14 0.75 10.53 1.886 9.75 3.54C8.97 1.886 7.36 0.75 5.5 0.75Z"
                            stroke="white"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
                @endif

<!-- 
            <button class="w-8 h-8 bg-[#9e9e9b] backdrop-blur-sm rounded-full flex items-center justify-center" onclick="event.stopPropagation();">
                <svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5.5 0.75C2.877 0.75 0.75 3.01 0.75 5.797C0.75 11.375 9.75 17.75 9.75 17.75C9.75 17.75 18.75 11.375 18.75 5.797C18.75 2.344 16.623 0.75 14 0.75C12.14 0.75 10.53 1.886 9.75 3.54C8.97 1.886 7.36 0.75 5.5 0.75Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button> -->
            <!-- Info Icon -->
            <!-- <button class="w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white transition-colors" onclick="event.stopPropagation();">
                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button> -->
        </div>

        <!-- View Icon (bottom-left) -->
        <!-- <div class="absolute bottom-3 left-3">
            <div class="w-8 h-8 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </div>
        </div> -->
    </div>

    <!-- Content -->
    <div class="p-4 flex flex-col flex-grow">
        <!-- Location with Rating -->
        <div class="flex items-center space-x-2 mb-2">
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-gray-900 mb-0.5 line-clamp-1">
                    {{ $hoarding->title ?? 'Udaipur | Hiramagri Chouraha' }}
                </h3>
                <p class="text-xs text-gray-500">
                    @if(($hoarding->hoarding_type) === 'dooh')
                        DOOH -
                        {{ $hoarding->doohScreen->external_screen_id
                            ?? $hoarding->doohScreen->id
                            ?? $hoarding->id }}
                    @else
                        OOH -
                        {{ $hoarding->code
                            ?? $hoarding->slug
                            ?? 'IMT-' . str_pad($hoarding->id, 6, '0', STR_PAD_LEFT) }}
                    @endif
                </p>
            </div>
            <div class="flex items-center ml-2">
                <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                </svg>
                <span class="text-xs font-semibold text-gray-700 ml-1">0</span>
            </div>
        </div>

        <!-- Price -->
        <div class="mb-3">

            @if($hoarding->hoarding_type === 'ooh')

            @php
                $base  = $hoarding->base_monthly_price_display ?? $hoarding->base_monthly_price;
                $sale  = $hoarding->monthly_price_display ?? $hoarding->monthly_price;
                $hasSale = !empty($sale) && $sale > 0;
            @endphp

            {{-- MAIN PRICE --}}
            <div class="flex items-baseline">
                <span class="text-xl font-semibold text-gray-900">
                    ₹{{ number_format($hasSale ? $sale : $base, 0) }}
                </span>
                <span class="text-lg text-black font-bold ml-1">/Month</span>
            </div>

            {{-- CUT PRICE + DISCOUNT (ONLY IF REAL SALE) --}}
            @if($hasSale && $base && $base > $sale)
                <div class="flex items-center space-x-2 mt-1">
                    <span class="text-xs text-red-500 line-through">
                        ₹{{ number_format($base, 0) }}
                    </span>

                    @php
                        $basePrice = (float) ($base ?? 0);
                        $salePrice = (float) ($sale ?? 0);
                        $discountAmount = 0;
                        if ($basePrice > 0 && $salePrice > 0 && $salePrice < $basePrice) {
                            $discountAmount = $basePrice - $salePrice;
                        }
                    @endphp


                    @if($discountAmount > 0)
                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">
                            ₹{{ number_format($discountAmount) }} OFF!
                        </span>
                    @endif
                </div>
            @endif

            {{-- ================= DOOH ================= --}}
            @else
                <div class="flex items-baseline">
                    <span class="text-xl font-semibold text-gray-900">
                        ₹{{ number_format(optional($hoarding->doohScreen)->price_per_slot) }}
                    </span>
                    <span class="text-lg text-black font-bold ml-1">/Second</span>
                </div>
            @endif


        </div>


        @php
            use Carbon\Carbon;
        @endphp

        <p class="text-xs text-blue-500 mb-1">
            @if($hoarding->available_from && Carbon::parse($hoarding->available_from)->isFuture())
                Hoarding Available from
                {{ Carbon::parse($hoarding->available_from)->format('F d, Y') }}
            @else
                Available
            @endif
        </p>

        @php
            $packageCount = 0;
            if(($hoarding->price_type ?? $hoarding->hoarding_type) === 'ooh') {
                $packageCount = $hoarding->oohPackages()->count();
            } elseif(($hoarding->price_type ?? $hoarding->hoarding_type) === 'dooh') {
                // For DOOH, count packages from the doohScreen
                if($hoarding->doohScreen) {
                    $packageCount = $hoarding->doohScreen->packages()->count();
                }
            }
        @endphp

        @if($packageCount > 0)
            <p class="text-xs text-teal-600 font-medium mb-3">{{ $packageCount }} {{ Str::plural('Package', $packageCount) }} Available</p>
        @else
            <p class="text-xs text-teal-600 font-medium mb-3">No packages</p>
        @endif

        <!-- Action Buttons -->
        <div class="flex items-center space-x-2 mb-2 mt-auto cursor-pointer">
            @php
                $isInCart = in_array($hoarding->id, $cartIds ?? []);
            @endphp

            <button
                id="cart-btn-{{ $hoarding->id }}"
                data-in-cart="{{ $isInCart ? '1' : '0' }}"
                onclick="event.stopPropagation(); event.preventDefault(); toggleCart(this, {{ $hoarding->id }})"
                class="cart-btn flex-1 py-2 px-3 text-sm font-semibold rounded cursor-pointer"
            >
            </button>





            @auth
                <button
                    type="button"
                    class="flex-1 py-2 px-3 btn-color text-white text-sm font-semibold rounded enquiry-btn cursor-pointer"
                    data-hoarding-id="{{ $hoarding->id }}"
                    data-grace-days="{{ (int) $hoarding->grace_period_days }}"
                    data-base-price="{{ ($hoarding->hoarding_type === 'dooh')
                        ? ($hoarding->doohScreen->price_per_slot ?? 0)
                        : ((!empty($hoarding->monthly_price) && $hoarding->monthly_price > 0)
                            ? $hoarding->monthly_price
                            : ($hoarding->base_monthly_price ?? 0))
                    }}"
                    data-base-monthly-price="{{ $hoarding->base_monthly_price ?? 0 }}"
                    data-hoarding-type="{{ $hoarding->hoarding_type}}"
                >
                    Enquiry Now
                </button>
            @else
                <button
                    type="button"
                    class="flex-1 py-2 px-3 btn-color text-white text-sm font-semibold rounded cursor-pointer"
                    onclick="event.stopPropagation(); event.preventDefault(); window.location.href='/login?message=' + encodeURIComponent('Please login to raise an enquiry.');"
                >
                    Enquiry Now
                </button> 
            @endauth
        </div>
        <!-- Enquire Link -->
        <!-- <a href="#" class="block text-center text-xs text-teal-600 hover:text-teal-700 font-medium" onclick="event.stopPropagation();">
            Enquire Now
        </a> -->
</div>

</div>


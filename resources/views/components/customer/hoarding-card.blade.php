<a href="{{ route('hoardings.show', $hoarding->slug ?? $hoarding->id) }}"
   class="bg-white rounded-lg border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden group flex flex-col h-full"
   style="text-decoration:none; color:inherit;">

    <!-- Image / Video -->
    <div class="relative h-48 overflow-hidden bg-gray-100">
        @php $mediaItem = $hoarding->primaryMediaItem(); @endphp

        @if($mediaItem)
            <x-media-preview :media="$mediaItem" :alt="$hoarding->title ?? 'Hoarding'" />
        @endif

        {{-- Recommended Badge --}}
        @php
            if (($hoarding->is_recommended ?? 0) == 1) {
                $isRecommended = true;
            } else {
                $isRecommended = ($hoarding->view_count ?? 0) >= 50 ||
                                 ($hoarding->expected_eyeball ?? 0) >= 5000;
            }
        @endphp

        @if($isRecommended)
        <div class="absolute top-3 left-3">
            <span class="px-2.5 py-1 bg-gradient-to-r from-pink-500 to-red-500 text-white text-xs font-semibold rounded">
                Recommended
            </span>
        </div>
        @endif

        <!-- Top Right Icons -->
        <div class="absolute top-3 right-3 flex items-center space-x-2">
            @php
                // Logged in → DB se check
                // Guest → hamesha false, JS LocalStorage page load pe restore karega
                $isWishlisted = auth()->check()
                    ? auth()->user()->wishlist()->where('hoarding_id', $hoarding->id)->exists()
                    : false;

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
                    {{ $isWishlisted ? 'bg-[#daf2e7] is-wishlisted' : 'bg-[#9e9e9b]' }} cursor-pointer"
                data-id="{{ $hoarding->id }}"
                data-auth="{{ auth()->check() ? '1' : '0' }}"
                data-role="{{ auth()->check() ? auth()->user()->role : '' }}"
                onclick="event.preventDefault(); event.stopPropagation(); toggleShortlist(this);"
            >
                <svg class="wishlist-icon" width="20" height="19" viewBox="0 0 20 19" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.5 0.75C2.877 0.75 0.75 3.01 0.75 5.797C0.75 11.375 9.75 17.75 9.75 17.75C9.75 17.75 18.75 11.375 18.75 5.797C18.75 2.344 16.623 0.75 14 0.75C12.14 0.75 10.53 1.886 9.75 3.54C8.97 1.886 7.36 0.75 5.5 0.75Z"
                        stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            @endif
        </div>
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
                <span class="text-xs font-semibold text-gray-700 ml-1">{{ $hoarding->averageRating() ?? 0 }}</span>
            </div>
        </div>

        <!-- Price -->
        <div class="mb-3">
            @php
                $base    = $hoarding->base_monthly_price_display ?? $hoarding->base_monthly_price;
                $sale    = $hoarding->monthly_price_display ?? $hoarding->monthly_price;
                $hasSale = !empty($sale) && $sale > 0;
            @endphp

            {{-- MAIN PRICE --}}
            <div class="flex items-baseline">
                @php $displayPrice = $hasSale ? $sale : $base; @endphp
                <span class="text-xl font-semibold text-gray-900 price-display"
                      data-base-price="{{ $displayPrice }}">
                    ₹{{ number_format($displayPrice, 0) }}
                </span>
                <span class="text-lg text-black font-bold ml-1">/Month</span>
            </div>

            {{-- CUT PRICE + DISCOUNT --}}
            @if($hasSale && $base && $base > $sale)
                <div class="flex items-center space-x-2 mt-1">
                    <span class="text-xs text-red-500 line-through">
                        ₹{{ number_format($base, 0) }}
                    </span>
                    @php
                        $basePrice      = (float) ($base ?? 0);
                        $salePrice      = (float) ($sale ?? 0);
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
        </div>

        @php use Carbon\Carbon; @endphp

        @if($hoarding->today_availability_status === 'available')
            <p class="text-xs text-gray-500 font-semibold mb-1">
                Available from {{ \Carbon\Carbon::now()->format('F d, Y') }}
            </p>
        @elseif(!empty($hoarding->next_available_date))
            <p class="text-xs text-gray-500 font-semibold mb-1">
                Available from {{ \Carbon\Carbon::parse($hoarding->next_available_date)->format('F d, Y') }}
            </p>
        @else
            <p class="text-xs text-gray-500 font-semibold mb-1">Not Available</p>
        @endif

        @php
            $packageCount = 0;
            if(($hoarding->price_type ?? $hoarding->hoarding_type) === 'ooh') {
                $packageCount = $hoarding->oohPackages()->count();
            } elseif(($hoarding->price_type ?? $hoarding->hoarding_type) === 'dooh') {
                if($hoarding->doohScreen) {
                    $packageCount = $hoarding->doohScreen->packages()->count();
                }
            }
        @endphp

        @if($packageCount > 0)
            <p class="text-xs text-teal-600 font-medium mb-3">{{ $packageCount }} {{ Str::plural('Package', $packageCount) }} Available</p>
        @endif

        <!-- Action Buttons -->
        <div class="flex items-center space-x-2 mb-2 mt-auto">
            @php
                // Logged in → DB se check
                // Guest → hamesha false, JS LocalStorage page load pe restore karega
                $isInCart = auth()->check()
                    ? in_array($hoarding->id, $cartIds ?? [])
                    : false;

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
                {{-- Cart — guest ke liye JS LocalStorage handle karega --}}
                <button
                    id="cart-btn-{{ $hoarding->id }}"
                    data-id="{{ $hoarding->id }}"
                    data-in-cart="{{ $isInCart ? '1' : '0' }}"
                    data-auth="{{ auth()->check() ? '1' : '0' }}"
                    onclick="event.preventDefault(); event.stopPropagation(); toggleCart(this, {{ $hoarding->id }})"
                    class="cart-btn flex-1 py-2 text-sm font-semibold rounded cursor-pointer"
                ></button>

                @auth
                    <button
                        type="button"
                        class="flex-1 py-2  btn-color text-white text-sm font-semibold rounded enquiry-btn cursor-pointer"
                        data-hoarding-id="{{ $hoarding->id }}"
                        data-grace-days="{{ (int) $hoarding->grace_period_days }}"
                        data-base-price="{{ (!empty($hoarding->monthly_price) && $hoarding->monthly_price > 0) ? $hoarding->monthly_price : ($hoarding->base_monthly_price ?? 0) }}"
                        data-slot-duration="{{ $hoarding->doohScreen->slot_duration_seconds ?? '' }}"
                        data-total-slots="{{ $hoarding->doohScreen->total_slots_per_day ?? '' }}"
                        data-base-monthly-price="{{ $hoarding->base_monthly_price ?? 0 }}"
                        data-hoarding-type="{{ $hoarding->hoarding_type }}"
                        onclick="event.preventDefault(); event.stopPropagation(); openEnquiryModal({ id: this.dataset.hoardingId, basePrice: Number(this.dataset.basePrice), baseMonthlyPrice: Number(this.dataset.baseMonthlyPrice || 0), graceDays: Number(this.dataset.graceDays || 0), type: this.dataset.hoardingType, count: 1 });"
                    >
                        Enquiry Now
                    </button>
                @else
                    {{-- Enquiry sirf login ke baad --}}
                    <button
                        type="button"
                        class="flex-1 py-2 px-3 btn-color text-white text-sm font-semibold rounded cursor-pointer"
                        onclick="event.preventDefault(); event.stopPropagation(); window.location.href='/login?message=' + encodeURIComponent('Please login to raise an enquiry.');"
                    >
                        Enquiry Now
                    </button>
                @endauth
            @else
                <div class="w-full flex justify-center">
                    <button
                        class="flex-1 py-2 px-3 btn-color text-white text-sm font-semibold rounded cursor-pointer"
                        onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('hoardings.show', $hoarding->id) }}';"
                    >
                        View Details
                    </button>
                </div>
            @endif
        </div>
    </div>

</a>
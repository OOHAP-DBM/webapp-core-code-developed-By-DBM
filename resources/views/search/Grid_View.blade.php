<div id="gridView" class="bg-gray-100">
    <div class="max-w-[1460px] mx-auto py-6 px-4">

        @if($results->total() > 0)
            <h2 class="text-lg text-black font-semibold mb-4">
                {{ $results->total() }} Hoardings in {{ request('location') ?? 'India' }}
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($results as $item)

                    @php
                         $mediaItem = null;

                        if (($item->hoarding_type ?? '') === 'ooh') {
                            $mediaItem = \Modules\Hoardings\Models\HoardingMedia::where('hoarding_id', $item->id)
                                ->orderByDesc('is_primary')
                                ->orderBy('sort_order')
                                ->first();
                        } elseif (($item->hoarding_type ?? '') === 'dooh') {
                            $screen = \Modules\DOOH\Models\DOOHScreen::where('hoarding_id', $item->id)->first();
                            if ($screen) {
                                $mediaItem = \Modules\DOOH\Models\DOOHScreenMedia::where('dooh_screen_id', $screen->id)
                                    ->orderBy('sort_order')
                                    ->first();
                            }
                        }
                        $isOwnerVendor = false;
                        if (
                            auth()->check()
                            && auth()->user()->active_role === 'vendor'
                            && isset($item->vendor_id)
                            && auth()->id() === (int) $item->vendor_id
                        ) {
                            $isOwnerVendor = true;
                        }
                        $isWishlisted = auth()->check()
                            ? auth()->user()->wishlist()->where('hoarding_id', $item->id)->exists()
                            : false;
                    @endphp

                    <div
                        class="bg-white rounded-lg border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden group cursor-pointer flex flex-col h-full"
                        @if(!empty($item->slug ?? $item->title))
                            onclick="if(event.target.closest('button, a') === null) window.location.href='{{ route('hoardings.show', $item->slug ?? $item->id) }}';"
                        @else
                            style="cursor:not-allowed; opacity:0.6;"
                        @endif
                    >

                        {{-- IMAGE / VIDEO --}}
                        <div class="relative h-48 bg-gray-100 overflow-hidden">
                            @if($mediaItem)
                                <x-media-preview :media="$mediaItem" :alt="$item->title ?? 'Hoarding'" />
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-200 text-sm">
                                    No Image
                                </div>
                            @endif
                            @php
                                if (($item->is_recommended ?? 0) == 1) {
                                    $isRecommended = true;
                                } else {
                                    $isRecommended = ($item->view_count ?? 0) >= 50 || 
                                                    ($item->expected_eyeball ?? 0) >= 5000;
                                }
                            @endphp
                            @if($isRecommended)
                            <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] px-2 py-0.5 rounded">
                                RECOMMENDED
                            </span>
                            @endif

                            <div class="absolute top-3 right-3 flex items-center space-x-2">
                                @if(!$isOwnerVendor)
                                    <button
                                        class="w-8 h-8 rounded-full flex items-center justify-center shortlist-btn
                                            {{ $isWishlisted ? 'bg-[#daf2e7] is-wishlisted' : 'bg-[#9e9e9b]' }} cursor-pointer"
                                        data-id="{{ $item->id }}"
                                        data-auth="{{ auth()->check() ? '1' : '0' }}"
                                        data-role="{{ auth()->check() ? auth()->user()->active_role : '' }}"
                                        onclick="event.stopPropagation(); toggleShortlist(this);"
                                    >
                                        <svg class="wishlist-icon" width="20" height="19" viewBox="0 0 20 19" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5.5 0.75C2.877 0.75 0.75 3.01 0.75 5.797C0.75 11.375 9.75 17.75 9.75 17.75C9.75 17.75 18.75 11.375 18.75 5.797C18.75 2.344 16.623 0.75 14 0.75C12.14 0.75 10.53 1.886 9.75 3.54C8.97 1.886 7.36 0.75 5.5 0.75Z"
                                                stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- CONTENT --}}
                        <div class="p-4 flex flex-col flex-grow">

                            <h3 class="text-sm font-semibold text-gray-900 line-clamp-1">
                                {{ $item->title }}
                            </h3>

                            <p class="text-xs text-gray-500 line-clamp-1">
                                {{ $item->address }}, {{ $item->city }}
                            </p>
                            <div class="flex items-center justify-between mt-1">
                                <div class="text-xs text-gray-600">
                                    <span class="uppercase font-medium">{{ $item->hoarding_type }}</span>
                                    @if($item->display_width && $item->display_height)
                                        | {{ $item->display_width }} × {{ $item->display_height }}
                                        {{ $item->display_unit === 'px' ? 'px' : 'Sq.ft' }}
                                    @endif
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                    </svg>
                                    <span class="text-xs font-semibold text-gray-700 ml-1">{{ $item->avg_rating }}</span>
                                </div>
                            </div>

                            <div class="mt-2">
                                @php
                                    $displayPrice = $item->price ?? $item->monthly_price ?? $item->base_monthly_price ?? 0;
                                @endphp
                                <span class="text-lg font-bold price-display" data-base-price="{{ $displayPrice }}">₹{{ number_format($displayPrice) }}</span>
                                <span class="text-sm text-black">/Month</span>
                            </div>

                            @if(
                                request('duration') !== 'weekly'
                                && !empty($item->monthly_price)
                                && $item->monthly_price > 0
                                && !empty($item->base_monthly_price)
                                && $item->base_monthly_price > $item->monthly_price
                            )
                                <div class="text-xs mt-1">
                                    <span class="line-through text-red-500">
                                        ₹{{ number_format($item->base_monthly_price) }}
                                    </span>
                                    <span class="ml-1 bg-green-200 text-green-700 px-2 py-0.5 rounded">
                                        ₹{{ number_format($item->base_monthly_price - $item->monthly_price) }} OFF
                                    </span>
                                </div>
                            @endif

                            <!-- <p class="text-xs text-gray-500 mt-2">Taxes excluded</p> -->

                            <p class="text-xs text-blue-500 mb-1">
                                @if($item->available_from && \Carbon\Carbon::parse($item->available_from)->isFuture())
                                    Hoarding Available from {{ \Carbon\Carbon::parse($item->available_from)->format('F d, Y') }}
                                @else
                                    Available
                                @endif
                            </p>

                            {{-- ACTIONS --}}
                            <div class="mt-auto pt-4 flex gap-2">
                                @if(!$isOwnerVendor)
                                    <button
                                        class="cart-btn flex-1 border border-[#c7c7c7] py-2 text-sm rounded cursor-pointer"
                                        data-in-cart="{{ in_array($item->id, $cartHoardingIds) ? '1' : '0' }}"
                                        onclick="event.stopPropagation(); toggleCart(this, {{ $item->id }})"
                                    ></button>
                                    @auth
                                        <button
                                            type="button"
                                            class="flex-1 py-2 btn-color text-white text-sm font-semibold rounded enquiry-btn cursor-pointer"
                                            data-hoarding-id="{{ $item->id }}"
                                            data-grace-days="{{ isset($item->grace_period_days) ? (int) $item->grace_period_days : 0 }}"
                                            data-base-price="{{ (!empty($item->monthly_price) && $item->monthly_price > 0) ? $item->monthly_price : ($item->base_monthly_price ?? 0) }}"
                                            data-slot-duration="{{ $item->doohScreen->slot_duration_seconds ?? '' }}"
                                            data-total-slots="{{ $item->doohScreen->total_slots_per_day ?? '' }}"
                                            data-base-monthly-price="{{ $item->base_monthly_price ?? 0 }}"
                                            data-hoarding-type="{{ $item->hoarding_type }}"
                                        >
                                            Enquiry Now
                                        </button>
                                    @else
                                        <button
                                            class="flex-1 py-2 btn-color text-white text-sm font-semibold rounded cursor-pointer"
                                            onclick="event.stopPropagation(); window.location.href='/login';"
                                        >
                                            Enquiry Now
                                        </button>
                                    @endauth
                                @else
                                    <div class="w-full flex justify-center">
                                        <button class="flex-1 py-2 btn-color text-white text-sm font-semibold rounded cursor-pointer"
                                            onclick="event.stopPropagation(); window.location.href='{{ route('hoardings.show', $item->id) }}';">
                                            View Details
                                        </button>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>

                @endforeach
            </div>

            @if($results->total() > $results->perPage())
                <div class="mt-6 flex w-full justify-end">
                    {{ $results->links() }}
                </div>
            @endif

        {{-- @else
            <div class="bg-white p-8 text-center text-gray-500 rounded border">
                No hoardings found.
            </div>
        @endif --}}
        @else
    <div class="flex flex-col items-center justify-center py-16 px-6 text-center min-h-[40vh]">
        <div class="w-20 h-20 mb-5 rounded-full bg-gray-200 flex items-center justify-center">
            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Hoardings Found</h3>
        <p class="text-sm text-gray-500 max-w-xs mb-6">
            We couldn't find any hoardings matching your search. Try a different city or adjust your filters.
        </p>
        <a href="{{ route('search') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Reset Search
        </a>
    </div>
@endif

    </div>
</div>
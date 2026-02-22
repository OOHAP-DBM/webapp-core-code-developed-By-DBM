<div id="listView" class="bg-gray-100 ">
    <div class="max-w-[1460px] mx-auto px-6 py-6">

        @if($results->total() > 0)
            <h2 class="text-lg text-black font-semibold mb-4">
                {{ $results->total() }} Hoardings in {{ request('location') ?? 'India' }}
            </h2>

            @foreach($results as $item)
                 @php
                    $mainImage = null;
                    $thumbs = collect();
                    if(($item->hoarding_type ?? '') === 'ooh'){

                        $allMedia = \Modules\Hoardings\Models\HoardingMedia::where('hoarding_id', $item->id)
                            ->orderByDesc('is_primary')
                            ->orderBy('sort_order')
                            ->get();
                        $primary = $allMedia->first();
                        $mainImage = $primary ? asset('storage/'.$primary->file_path) : null;

                        $thumbs = $allMedia->skip(1)->take(4);
                    }
                    elseif(($item->hoarding_type ?? '') === 'dooh'){
                        $screen = \Modules\DOOH\Models\DOOHScreen::where('hoarding_id', $item->id)->first();
                        if($screen){
                            $allMedia = \Modules\DOOH\Models\DOOHScreenMedia::where('dooh_screen_id', $screen->id)
                                ->orderByDesc('is_primary')
                                ->orderBy('sort_order')
                                ->get();

                            $primary = $allMedia->first();
                            $mainImage = $primary ? asset('storage/'.$primary->file_path) : null;
                            $thumbs = $allMedia->skip(1)->take(4);
                        }
                    }
                @endphp
            <div class="bg-[#f0f0f0] rounded-xl p-5 mb-5 flex flex-col cursor-pointer"
                onclick="if(event.target.closest('button, a') === null)
                        @php
                            $hoardingParam = $item->slug ?? $item->title;
                        @endphp
                        @if(!empty($hoardingParam))
                            window.location.href='{{ route('hoardings.show', $hoardingParam) }}';
                        @else
                            // No valid slug or title, do nothing
                        @endif">
                    <div class="flex gap-6 items-stretch flex-1">

                        {{-- IMAGE --}}
                        <div class="w-[305px] flex-shrink-0">
                            <div class="relative group">
                                <div class="w-full h-[190px] overflow-hidden rounded-lg bg-gray-100">
                                    @if(isset($primary) && $primary)
                                        <x-media-preview :media="$primary" :alt="$item->title ?? 'Hoarding'" />
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-sm bg-gray-200">
                                            No Image
                                        </div>
                                    @endif
                                </div>

                                <!-- RECOMMENDED TAG -->
                                <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] px-2 py-0.5 rounded z-10">
                                    RECOMMENDED
                                </span>

                                <!-- SAVE (BOOKMARK) ICON -->
                                 @php
                                    $isWishlisted = auth()->check()
                                        ? auth()->user()->wishlist()->where('hoarding_id', $item->id)->exists()
                                        : false;
                                @endphp
                                @php
                                    $isOwnerVendor = false;

                                    if (
                                        auth()->check()
                                        && auth()->user()->active_role === 'vendor'
                                        && isset($item->vendor_id)
                                        && auth()->id() === (int) $item->vendor_id
                                    ) {
                                        $isOwnerVendor = true;
                                    }
                                @endphp
                                @if(!$isOwnerVendor)
                                <button
                                    class="absolute top-2 right-2 z-20 w-8 h-8 rounded-full flex items-center justify-center shortlist-btn
                                        {{ $isWishlisted ? 'bg-[#daf2e7] is-wishlisted' : 'bg-[#9e9e9b]' }}
                                        {{ $isOwnerVendor ? 'opacity-50' : '' }}"

                                    data-id="{{ $item->id }}"
                                    data-auth="{{ auth()->check() ? '1' : '0' }}"
                                    data-role="{{ auth()->check() ? auth()->user()->role : '' }}"

                                    {{-- 🔥 THIS IS THE KEY --}}
                                    onmouseenter="this.style.cursor='{{ $isOwnerVendor ? 'not-allowed' : 'pointer' }}'"
                                    onmouseleave="this.style.cursor='default'"

                                    @if($isOwnerVendor)
                                        disabled
                                        onclick="event.stopPropagation(); return false;"
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
                            </div>

                            <div class="flex gap-2 mt-2">
                                @if($thumbs->isNotEmpty())
                                    <div class="flex gap-2 mt-2 overflow-x-auto">
                                        @foreach($thumbs as $thumb)
                                            <div class="w-[70px] h-[48px] rounded overflow-hidden border border-gray-300 cursor-pointer list-thumb"
                                                onclick="switchListMedia(this)">

                                                <div class="hidden media-path">{{ asset('storage/'.$thumb->file_path) }}</div>
                                                <div class="hidden media-type">{{ str_contains($thumb->mime_type ?? '', 'video') ? 'video' : 'image' }}</div>

                                                <x-media-preview :media="$thumb" alt="" />

                                            </div>
                                        @endforeach
                                    </div>
                                    @endif
                            </div>

                        </div>

                        {{-- DETAILS --}}
                        <div class="flex-1">
                            {{-- TITLE --}}
                            <h2 class="text-xl font-semibold mt-1">
                                {{ $item->title }}
                            </h2>

                            {{-- ADDRESS --}}
                            <p class="text-sm text-gray-500">
                                {{ $item->address }}, {{ $item->city }}
                            </p>

                            {{-- TYPE + SIZE --}}
                            <div class="flex items-center gap-3 text-sm mt-1 text-gray-600">
                                <span class="uppercase font-medium">
                                    {{ $item->hoarding_type }}
                                </span>

                                @if($item->display_width && $item->display_height)
                                    <span>
                                        | {{ $item->display_width }} × {{ $item->display_height }}
                                        {{ $item->display_unit === 'px' ? 'px' : 'Sq.ft' }}
                                    </span>
                                @endif

                            </div>

                            {{-- FEATURED TAG --}}
                            @if($item->is_featured)
                                <div class="my-3">
                                    <span class="bg-[#fc6286] text-white text-xs px-2 py-0.5 rounded">
                                        Recommended
                                    </span>
                                </div>
                            @else
                            <div class="my-3">
                                <span class="bg-[#ffb854]  text-xs px-2 py-0.5 rounded">
                                            Limited Time Offer
                                </span>
                            </div>
                            @endif

                            {{-- PRICE --}}
                            <div class="mt-3">
                                <span class="text-xl font-bold">
                                    ₹{{ number_format($item->price) }}
                                </span>

                                <span class="text-lg text-black font-bold">
                                        /Month
                                </span>
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


                            {{-- TAX NOTE --}}
                            <p class="text-xs text-gray-500 my-2">
                                Taxes excluded
                            </p>

                            <p class="text-xs text-blue-500 mb-1">
                                @if($item->available_from && \Carbon\Carbon::parse($item->available_from)->isFuture())
                                    Hoarding Available from
                                    {{ \Carbon\Carbon::parse($item->available_from)->format('F d, Y') }}
                                @else
                                    Available
                                @endif
                            </p>

                            {{-- GAZEFLOW --}}
                            @if($item->expected_eyeball)
                                <p class="text-xs text-gray-500">
                                    Approx {{ number_format($item->expected_eyeball) }} daily eyeballs
                                </p>
                            @endif
                        </div>

                        {{-- ACTIONS --}}
                        <div class="min-w-[200px] relative">
                            <!-- FORCE BOTTOM -->
                            <div class="absolute bottom-0 right-0 flex gap-6 items-start">
                            @if(!$isOwnerVendor)
                                <!-- Short List + Enquire -->
                                <div class="flex flex-col">
                                    <button
                                        class="cart-btn border border-[#c7c7c7] px-4 py-1.5 rounded text-sm whitespace-nowrap min-w-[96px] cursor-pointer"
                                        data-in-cart="{{ in_array($item->id, $cartHoardingIds) ? '1' : '0' }}"
                                        onclick="toggleCart(this, {{ $item->id }})"
                                    >
                                    </button>

                                </div>
                                <!-- Book Now -->
                                <div>
                                 @auth
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center text-center
                                                sm:justify-start
                                                whitespace-nowrap py-2 px-3 btn-color text-white text-sm font-semibold rounded enquiry-btn cursor-pointer"
                                        data-hoarding-id="{{ $item->id }}"
                                        data-grace-days="{{ isset($item->grace_period_days) ? (int) $item->grace_period_days : 0 }}"
                                        data-base-price="{{ (!empty($item->monthly_price) && $item->monthly_price > 0)
                                            ? $item->monthly_price
                                            : ($item->base_monthly_price ?? 0)
                                        }}"
                                        data-slot-duration="{{ $item->doohScreen->slot_duration_seconds ?? '' }}"
                                        data-total-slots="{{ $item->doohScreen->total_slots_per_day ?? '' }}"
                                        data-base-monthly-price="{{ $item->base_monthly_price ?? 0 }}"
                                        data-hoarding-type="{{ $item->hoarding_type }}"
                                    >
                                        Enquiry Now
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        class="cursor-pointer text-center items-center justify-center inline-flex whitespace-nowrap py-2 px-3 btn-color text-white text-sm font-semibold rounded"
                                        onclick="event.stopPropagation(); event.preventDefault();
                                                window.location.href='/login?message=' + encodeURIComponent('Please login to raise an enquiry.');"
                                    >
                                        Enquiry Now
                                    </button>
                                @endauth
                                </div>
                            @else
                                <button class="cursor-pointer text-center items-center justify-center inline-flex whitespace-nowrap py-2 px-20 btn-color text-white text-sm font-semibold rounded">
                                    View Details
                                </button>
                            @endif

                            </div>
                        </div>
                    </div>
                </div>
             @endforeach

            @if($results->total() > $results->perPage())
                <div class="mt-6 flex justify-center">
                    {{ $results->links() }}
                </div>
            @endif
        @else
            <div class="bg-white p-8 text-center text-gray-500 rounded border">
                No hoardings found.
            </div>
        @endif
        @guest
            <!-- Personalized Recommendations CTA -->
            <div class="container mx-auto px-4">
                <hr class="border-gray-200">
            </div>
            <section class="py-12 bg-gray-100">
                    <div class="container mx-auto px-4 text-center">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">
                            See Personalized Recommendations
                        </h3>

                            <div class="flex flex-col items-center justify-center space-y-4">
                                <a href="{{ route('login') }}"
                                class="px-24 py-3 bg-gray-900 text-white rounded font-semibold hover:bg-gray-800">
                                    Login
                                </a>
                                <div class="flex items-center space-x-2">
                                    <span class="text-gray-500">New on OOHAPP?</span>

                                    <a href="{{ route('register.role-selection') }}"
                                        class="text-[#008ae0] font-semibold border-b-1 border-[#008ae0] hover:border-[#006bb3] transition">
                                            Signup
                                    </a>
                                </div>
                            </div>
                    </div>
            </section>
         
        @endguest

    </div>
</div>

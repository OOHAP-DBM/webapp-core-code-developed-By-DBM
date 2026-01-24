<div id="listView" class="bg-gray-100 ">
    <div class="max-w-[1460px] mx-auto px-6 py-6">

        @if($results->total() > 0)
            <h2 class="text-lg text-black font-semibold mb-4">
                {{ $results->total() }} Hoardings in {{ request('location') ?? 'India' }}
            </h2>

            @foreach($results as $item)
                 @php
                    $images = collect($item->images ?? []);

                    $primary = $images->firstWhere('is_primary', 1)
                        ?? $images->first();

                    $hasImage = (bool) $primary;

                    $mainImage = $hasImage
                        ? asset('storage/' . ltrim($primary->file_path, '/'))
                        : null;

                    $thumbs = $hasImage
                        ? $images
                            ->where('file_path', '!=', $primary->file_path)
                            ->take(4)
                        : collect();
                @endphp
            <div class="bg-[#f0f0f0] rounded-xl p-5 mb-5 flex flex-col cursor-pointer"
                onclick="if(event.target.closest('button, a') === null)
                        window.location.href='{{ route('hoardings.show', $item->id) }}';">
                    <div class="flex gap-6 items-stretch flex-1">

                        {{-- IMAGE --}}
                        <div class="w-[305px] flex-shrink-0">
                            <div class="relative group">
                                <img src="{{ $mainImage }}"
                                    class="w-full h-[190px] object-cover rounded-lg">
                                <!-- RECOMMENDED TAG -->
                                <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] px-2 py-0.5 rounded z-10">
                                    RECOMMENDED
                                </span>
                                <!-- SAVE (BOOKMARK) ICON -->
                                <!-- <button
                                    class="absolute top-2 right-2 z-10
                                        bg-white/90 hover:bg-white
                                        border border-gray-200
                                        rounded-full p-1.5 shadow"> -->
                                    <!-- bookmark svg -->
                                    <!-- <svg xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="w-4 h-4 text-gray-700">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M5 5v14l7-5 7 5V5a2 2 0 00-2-2H7a2 2 0 00-2 2z"/>
                                    </svg>
                                </button> -->
                                <!-- VIEW (EYE) ICON -->
                                <!-- <button
                                    class="absolute bottom-2 left-2 z-10
                                        bg-white/90 hover:bg-white
                                        border border-gray-200
                                        rounded-full p-1.5 shadow"> -->
                                    <!-- eye svg -->
                                    <!-- <svg xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="w-4 h-4 text-gray-700">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5
                                                c4.477 0 8.268 2.943 9.542 7
                                                -1.274 4.057-5.065 7-9.542 7
                                                -4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button> -->
                            </div>
                            <div class="flex gap-2 mt-2">
                                @if($thumbs->isNotEmpty())
                                    <div class="flex gap-2 mt-2">
                                        @foreach($thumbs as $thumb)
                                            <img src="{{ asset('storage/' . ltrim($thumb->file_path, '/')) }}"
                                                class="w-[70px] h-[48px] object-cover rounded"
                                                alt="Thumbnail">
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
                                    @if(
                                        $item->hoarding_type === 'ooh'
                                        && (empty($item->monthly_price) || $item->monthly_price == 0)
                                        && !empty($item->base_monthly_price)
                                    )
                                        ₹{{ number_format($item->base_monthly_price) }}
                                    @else
                                        ₹{{ number_format($item->price) }}
                                    @endif
                                </span>

                                <span class="text-lg text-black text-bold">
                                    @if($item->hoarding_type === 'dooh')
                                        /Second
                                    @elseif(request('duration') === 'weekly')
                                        /Week
                                    @else
                                        /Month
                                    @endif
                                </span>
                            </div>


                            {{-- BASE PRICE (CUT / RED) --}}
                            @if(!empty($item->base_monthly_price) && $item->base_monthly_price > $item->price)
                                <div class="mt-1">
                                    @if(
                                        $item->hoarding_type === 'ooh'
                                        && !empty($item->price)
                                        && $item->price > 0
                                        && !empty($item->base_monthly_price)
                                        && $item->base_monthly_price > $item->price
                                    )
                                        <span class="text-xs text-red-500 line-through">
                                            ₹{{ number_format($item->base_monthly_price) }}
                                        </span>
                                    @endif

                                    &nbsp;
                                    @if($item->discount_percent)
                                        <span class="bg-green-200 text-xs text-green-700 px-2 py-0.5 rounded">
                                             {{ $item->discount_percent }}% OFF
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- TAX NOTE --}}
                            <p class="text-xs text-gray-500 my-2">
                                Taxes excluded
                            </p>

                            {{-- AVAILABLE FROM --}}
                            @if($item->available_from)
                                <p class="text-xs text-gray-500">
                                    Available from:
                                    {{ \Carbon\Carbon::parse($item->available_from)->format('d M Y') }}
                                </p>
                            @endif

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
                                <!-- Short List + Enquire -->
                                <div class="flex flex-col">
                                    <button
                                        class="cart-btn border border-[#c7c7c7] px-4 py-1.5 rounded text-sm whitespace-nowrap min-w-[96px]"
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
                                        class="inline-flex whitespace-nowrap py-2 px-3 btn-color text-white text-sm font-semibold rounded enquiry-btn"
                                        data-hoarding-id="{{ $item->id }}"
                                        data-grace-days="{{ isset($item->grace_period_days) ? (int) $item->grace_period_days : 0 }}"

                                        {{-- BASE PRICE (used for dropdown base option) --}}
                                        data-base-price="{{ ($item->hoarding_type === 'dooh')
                                            ? ($item->price ?? 0)
                                            : ((!empty($item->monthly_price) && $item->monthly_price > 0)
                                                ? $item->monthly_price
                                                : ($item->base_monthly_price ?? 0))
                                        }}"

                                        {{-- ALWAYS BASE MONTHLY PRICE (for OOH package discount calc) --}}
                                        data-base-monthly-price="{{ $item->base_monthly_price ?? 0 }}"

                                        data-hoarding-type="{{ $item->hoarding_type }}"
                                    >
                                        Enquiry Now
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        class="inline-flex whitespace-nowrap py-2 px-3 btn-color text-white text-sm font-semibold rounded"
                                        onclick="event.stopPropagation(); event.preventDefault();
                                                window.location.href='/login?message=' + encodeURIComponent('Please login to raise an enquiry.');"
                                    >
                                        Enquiry Now
                                    </button>
                                @endauth
                                </div>

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

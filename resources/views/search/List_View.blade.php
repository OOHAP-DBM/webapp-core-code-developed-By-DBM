 <div id="listView" class="bg-gray-100 min-h-screen">
        <div class="max-w-[1460px] mx-auto px-6 py-6">

            <h2 class="text-sm text-gray-700 mb-4">
                {{ $results->total() }} Hoardings in {{ request('location') ?? 'India' }}
            </h2>

            @forelse($results as $item)
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
                <div class="bg-[#f0f0f0] rounded-xl p-5 mb-5 flex flex-col">
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
                                    ₹{{ number_format($item->price) }}
                                </span>
                                <span class="text-sm text-gray-500">
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
                                    <span class="text-xs text-red-500 line-through">
                                        ₹{{ number_format($item->base_monthly_price) }} 
                                    </span>
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

                                    <a class="block text-xs text-yellow-600 mt-1 italic underline whitespace-nowrap text-left">Enquire Now</a>
                                </div>
                                <!-- Book Now -->
                                <div>
                                    <button class="bg-green-600 text-white px-5 py-2 rounded-md text-sm whitespace-nowrap min-w-[110px]">
                                        Book Now
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
             @empty
                <div class="bg-white p-8 text-center text-gray-500 rounded border">
                    No hoardings found.
                </div>
            @endforelse

            {{ $results->links() }}
        </div>
</div>
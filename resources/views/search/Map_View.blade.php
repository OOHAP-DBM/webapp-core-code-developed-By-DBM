<div id="mapView" class="bg-white  min-h-screen">
        <div class="max-w-[1460px] mx-auto px-6 py-6">
            <h2 class="text-lg text-black font-semibold mb-4">
                {{ $results->total() }} Hoardings in {{ request('location') ?? 'India' }}
            </h2>
            <div class="map-view-container flex gap-6">
                 {{-- RIGHT: LISTINGS --}}
                <div class="w-1/2 overflow-y-auto" style="max-height: 700px;">
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
                        <div class="rounded-lg p-4 mb-4 shadow bg-[#F8F8F8]"
                        onclick="if(event.target.closest('button, a') === null)
                        window.location.href='{{ route('hoardings.show', $item->id) }}';">
                            <div class="flex gap-3">
                                {{-- THUMBNAIL --}}
                                <div class="w-50 flex-shrink-0">
                            <div class="relative group">
                                <img src="{{ $mainImage }}"
                                    class="w-full h-30 object-cover rounded-lg">
                                <!-- RECOMMENDED TAG -->
                                <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] px-2 py-0.5 rounded z-10">
                                    RECOMMENDED
                                </span>
                                <!-- SAVE (BOOKMARK) ICON -->
                                <button
                                    class="absolute top-2 right-2 z-10
                                        bg-[#9e9e9b]
                                        rounded-full p-1.5 shadow">
                                    <!-- wishlist svg -->
                                    <svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.5 0.75C2.877 0.75 0.75 3.01 0.75 5.797C0.75 11.375 9.75 17.75 9.75 17.75C9.75 17.75 18.75 11.375 18.75 5.797C18.75 2.344 16.623 0.75 14 0.75C12.14 0.75 10.53 1.886 9.75 3.54C8.97 1.886 7.36 0.75 5.5 0.75Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
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
                                                class="w-[44px] h-[48px] object-cover rounded"
                                                alt="Thumbnail">
                                        @endforeach
                                    </div>
                                @endif

                            </div>
                        </div>
                                
                                
                                {{-- DETAILS --}}
                                <div class="flex-1">
                                    <h4 class="font-semibold text-sm">{{ $item->title ?? 'Unipole Hazaratganj Lucknow' }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $item->address ?? 'Vipul khand gomti nagar' }}</p>
                                    
                                    <div class="flex items-center gap-2 mt-1">
                                        <span>{{ $item->hoarding_type }}</span>
                                        @if($item->display_width && $item->display_height)
                                            <span>
                                                | {{ $item->display_width }} × {{ $item->display_height }}
                                                {{ $item->display_unit === 'px' ? 'px' : 'Sq.ft' }}
                                            </span>
                                        @endif
                                        <span class="flex items-center gap-1 text-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ffc83d" class="w-3 h-3">
                                                <polygon points="12 2 15 8.5 22 9.3 17 14.1 18.2 21 12 17.8 5.8 21 7 14.1 2 9.3 9 8.5 12 2" />
                                            </svg>
                                            <span>{{ $item->rating ?? 0 }}</span>
                                        </span>
                                    </div>
                                    <div class="my-2">
                                        <span class="bg-[#ffb854]  text-xs px-2 py-0.5 rounded">
                                                    Limited Time Offer
                                        </span>
                                    </div>
                                    
                                     {{-- PRICE --}}
                                    <div class="mt-1">
                                        <span class="text-xl font-bold">
                                            ₹{{ number_format($item->price) }}
                                        </span>

                                        <span class="text-sm text-gray-500">
                                            @if($item->hoarding_type === 'dooh')
                                                /Slot
                                            @elseif(request('duration') === 'weekly')
                                                /Week
                                            @else
                                                /Month
                                            @endif
                                        </span>
                                    </div>


                                    @if(
                                        request('duration') !== 'weekly'
                                        && $item->hoarding_type === 'ooh'
                                        && !empty($item->monthly_price)
                                        && $item->monthly_price > 0
                                        && !empty($item->base_monthly_price)
                                        && $item->base_monthly_price > $item->monthly_price
                                    )
                                        <div class="mt-1">
                                            <span class="text-xs text-red-500 line-through">
                                                ₹{{ number_format($item->base_monthly_price) }}
                                            </span>

                                            @if($item->discount_percent)
                                                <span class="bg-green-200 text-xs text-green-700 px-2 py-0.5 rounded">
                                                    {{ $item->discount_percent }}% OFF
                                                </span>
                                            @endif

                                            <span class="text-xs text-gray-500 ml-1">Taxes excluded</span>
                                        </div>
                                    @endif

                                    {{-- GAZEFLOW --}}
                                    @if($item->expected_eyeball)
                                        <p class="text-xs text-gray-500 my-1">
                                            Approx {{ number_format($item->expected_eyeball) }} daily eyeballs
                                        </p>
                                    @endif
                                        <p class="text-xs text-blue-500">3 Packages Available</p>
                                    
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white p-4 text-center text-gray-500 rounded border">
                            No hoardings found.
                        </div>
                    @endforelse
                </div>
                {{-- LEFT: MAP --}}
                <div class="w-2/3">
                    <div class="bg-gray-300 rounded-xl h-[700px] flex items-center justify-center">
                        <div class="rounded-xl overflow-hidden h-[700px] w-full">
                            <div id="priceMap" class="h-full w-full"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
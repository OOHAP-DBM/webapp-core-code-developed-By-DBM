<div id="mapView" class="bg-white  min-h-screen">
        <div class="max-w-[1460px] mx-auto px-6 py-6">
            <h2 class="text-sm text-gray-700 mb-4">
                {{ $results->total() }} Hoardings in {{ request('location') ?? 'India' }}
            </h2>
            
            <div class="map-view-container flex gap-6">
                 {{-- RIGHT: LISTINGS --}}
                <div class="w-1/2 overflow-y-auto" style="max-height: 700px;">
                    @forelse($results as $item)
                        <div class="rounded-lg p-4 mb-4 shadow bg-[#F8F8F8]">
                            <div class="flex gap-3">
                                {{-- THUMBNAIL --}}
                                <div class="w-50 flex-shrink-0">
                                    <img src="{{$dummyImage }}" 
                                         class="w-full h-20 object-cover rounded">
                                         <div class="flex gap-2 mt-2">
                                            @foreach($dummyThumbs as $thumb)
                                                <img src="{{ $thumb }}"
                                                    class="w-[44px] h-[48px] object-cover rounded ">
                                            @endforeach
                                        </div>
                                </div>
                                
                                
                                {{-- DETAILS --}}
                                <div class="flex-1">
                                    <h4 class="font-semibold text-sm">{{ $item->title ?? 'Unipole Hazaratganj Lucknow' }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $item->address ?? 'Vipul khand gomti nagar' }}</p>
                                    
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-xs">{{$item->hoarding_type}} | 300*250Sq.ft</span>
                                        <span class="flex items-center gap-1 text-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ffc83d" class="w-3 h-3">
                                                <polygon points="12 2 15 8.5 22 9.3 17 14.1 18.2 21 12 17.8 5.8 21 7 14.1 2 9.3 9 8.5 12 2" />
                                            </svg>
                                            <span>{{ $item->rating ?? 4.5 }}</span>
                                        </span>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <span class="font-bold">₹{{ number_format($item->price ?? 10999) }}</span>
                                        <span class="text-xs text-gray-500">/Month</span>
                                                        {{-- OLD PRICE + DISCOUNT --}}
                                        <div class="flex items-center gap-2 mt-1">
                                            <!-- Old price -->
                                            <span class="text-xs text-red-400 line-through">
                                                ₹{{ number_format(($item->price ?? 10999) + 4200) }}
                                            </span>

                                            <!-- Discount badge -->
                                            <span class="text-[11px] text-green-600 font-medium">
                                                ₹3000 OFF!
                                            </span>
                                            <span class="text-xs text-gray-500 mt-1">Taxes excluded</span>

                                        </div>
                                        <p class="text-xs text-gray-500">Hoarding Available From: {{ $item->available_from ?? 'December 25' }}</p>
                                        <p class="text-xs text-blue-600">{{ $item->packages_count ?? 3 }} Packages Available</p>
                                    </div>
                                    
                                    <button class="mt-2 bg-green-600 text-white text-xs px-3 py-1.5 rounded w-full">
                                        View Details
                                    </button>
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
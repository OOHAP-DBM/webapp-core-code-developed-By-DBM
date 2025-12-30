 <div id="listView" class="bg-gray-100 min-h-screen">
        <div class="max-w-[1460px] mx-auto px-6 py-6">

            <h2 class="text-sm text-gray-700 mb-4">
                {{ $results->total() }} Hoardings in {{ request('location') ?? 'India' }}
            </h2>

            @forelse($results as $item)
                <div class="bg-[#f0f0f0] rounded-xl p-5 mb-5 flex flex-col">
                    <div class="flex gap-6 items-stretch flex-1">

                        {{-- IMAGE --}}
                        <div class="w-[305px] flex-shrink-0">
                            <div class="relative group">
                                <img src="{{ $dummyImage }}"
                                    class="w-full h-[190px] object-cover rounded-lg">
                                <!-- RECOMMENDED TAG -->
                                <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] px-2 py-0.5 rounded z-10">
                                    RECOMMENDED
                                </span>
                                <!-- SAVE (BOOKMARK) ICON -->
                                <button
                                    class="absolute top-2 right-2 z-10
                                        bg-white/90 hover:bg-white
                                        border border-gray-200
                                        rounded-full p-1.5 shadow">
                                    <!-- bookmark svg -->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="w-4 h-4 text-gray-700">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M5 5v14l7-5 7 5V5a2 2 0 00-2-2H7a2 2 0 00-2 2z"/>
                                    </svg>
                                </button>
                                <!-- VIEW (EYE) ICON -->
                                <button
                                    class="absolute bottom-2 left-2 z-10
                                        bg-white/90 hover:bg-white
                                        border border-gray-200
                                        rounded-full p-1.5 shadow">
                                    <!-- eye svg -->
                                    <svg xmlns="http://www.w3.org/2000/svg"
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
                                </button>
                            </div>
                            <div class="flex gap-2 mt-2">
                                @foreach($dummyThumbs as $thumb)
                                    <img src="{{ $thumb }}"
                                        class="w-[70px] h-[48px] object-cover rounded ">
                                @endforeach
                            </div>
                        </div>

                        {{-- DETAILS --}}
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold">{{ $item->title ?? 'Unipole Hazaratganj Lucknow' }}</h3>
                            <p class="text-sm text-gray-500">{{ $item->address ?? 'Vipul khand gomti nagar' }}</p>

                            <div class="flex items-center gap-3 text-sm mt-1">
                                <span>{{$item->hoarding_type}} | 300*250Sq.ft</span>
                                <span class="flex items-center gap-1 text-sm text-gray-600">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="#ffc83d"
                                        class="w-4 h-4">
                                        <polygon points="12 2 15 8.5 22 9.3 17 14.1 18.2 21 12 17.8 5.8 21 7 14.1 2 9.3 9 8.5 12 2" />
                                    </svg>

                                    <span>{{ $item->rating ?? 4.5 }}</span>
                                    <span class="text-xs text-gray-400">
                                        ({{ $item->reviews_count ?? 16 }} Ratings)
                                    </span>
                                </span>
                            </div>

                            <div class="flex gap-2 mt-2">
                                <span class="bg-[#ffb854] text-xs px-2 py-0.5 rounded">Limited time offer</span>
                                <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded">OOH</span>
                            </div>

                            <div class="mt-3">
                                <span class="text-xl font-bold">₹{{ number_format($item->price ?? 10999) }}</span>
                                <span class="text-sm text-gray-500">/Month</span>
                            </div>
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
                            </div>


                            <p class="text-xs text-gray-500 mt-1">Taxes excluded</p>
                            <p class="text-xs text-gray-500">Hoarding Available From: {{ $item->available_from ?? 'December 25' }}</p>
                            <p class="text-xs text-blue-600">{{ $item->packages_count ?? 3 }} Packages Available</p>
                        </div>

                        {{-- ACTIONS --}}
                        <div class="min-w-[200px] relative">
                            <!-- FORCE BOTTOM -->
                            <div class="absolute bottom-0 right-0 flex gap-6 items-start">
                                <!-- Short List + Enquire -->
                                <div class="flex flex-col">
                                    <button class="border border-[#c7c7c7] px-4 py-1.5 rounded text-sm whitespace-nowrap min-w-[96px]">Short List</button>
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
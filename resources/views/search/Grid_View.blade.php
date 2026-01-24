<div id="gridView" class="bg-gray-100 ">
    <div class="max-w-[1460px] mx-auto px-6 py-6">

        @if($results->total() > 0)
            <h2 class="text-lg text-black  mb-4">
                {{ $results->total() }} Hoardings in {{ request('location') ?? 'India' }}
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($results as $item)

                    @php
                        $images = collect($item->images ?? []);
                        $primary = $images->firstWhere('is_primary', 1) ?? $images->first();
                        $hasImage = (bool) $primary;
                        $mainImage = $hasImage
                            ? asset('storage/' . ltrim($primary->file_path, '/'))
                            : null;
                    @endphp

                    <div
                        class="bg-white rounded-lg border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden group cursor-pointer flex flex-col h-full"
                        onclick="if(event.target.closest('button, a') === null)
                            window.location.href='{{ route('hoardings.show', $item->id) }}';"
                    >

                        {{-- IMAGE --}}
                        <div class="relative h-48 bg-gray-100 overflow-hidden ">
                            @if($mainImage)
                                <img src="{{ $mainImage }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-200 text-sm">
                                    No Image
                                </div>
                            @endif

                            <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] px-2 py-0.5 rounded">
                                RECOMMENDED
                            </span>
                        </div>

                        {{-- CONTENT --}}
                        <div class="p-4 flex flex-col flex-grow">

                            {{-- TITLE --}}
                            <h3 class="text-sm font-semibold text-gray-900 line-clamp-1">
                                {{ $item->title }}
                            </h3>

                            {{-- ADDRESS --}}
                            <p class="text-xs text-gray-500 line-clamp-1">
                                {{ $item->address }}, {{ $item->city }}
                            </p>

                            {{-- TYPE + SIZE --}}
                            <div class="text-xs text-gray-600 mt-1">
                                <span class="uppercase font-medium">
                                    {{ $item->hoarding_type }}
                                </span>

                                @if($item->display_width && $item->display_height)
                                    | {{ $item->display_width }} × {{ $item->display_height }}
                                    {{ $item->display_unit === 'px' ? 'px' : 'Sq.ft' }}
                                @endif
                            </div>

                            

                            {{-- PRICE --}}
                            <div class="mt-2">
                                <span class="text-lg font-bold">
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

                                <span class="text-sm text-black">
                                    @if($item->hoarding_type === 'dooh')
                                        /Second
                                    @elseif(request('duration') === 'weekly')
                                        /Week
                                    @else
                                        /Month
                                    @endif
                                </span>
                            </div>

                            {{-- CUT PRICE + DISCOUNT --}}
                            @if(!empty($item->base_monthly_price) && $item->base_monthly_price > $item->price)
                                <div class="text-xs mt-1">
                                    @if(
                                        $item->hoarding_type === 'ooh'
                                        && !empty($item->price)
                                        && $item->price > 0
                                    )
                                        <span class="line-through text-red-500">
                                            ₹{{ number_format($item->base_monthly_price) }}
                                        </span>
                                    @endif

                                    @if($item->discount_percent)
                                        <span class="ml-1 bg-green-200 text-green-700 px-2 py-0.5 rounded">
                                            {{ $item->discount_percent }}% OFF
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- TAX NOTE --}}
                            <p class="text-xs text-gray-500 mt-2">
                                Taxes excluded
                            </p>
                            

                            {{-- ACTIONS --}}
                            <div class="mt-auto pt-4 flex gap-2">

                                {{-- CART --}}
                                <button
                                    class="cart-btn flex-1 border border-[#c7c7c7] py-2 text-sm rounded"
                                    data-in-cart="{{ in_array($item->id, $cartHoardingIds) ? '1' : '0' }}"
                                    onclick="event.stopPropagation(); toggleCart(this, {{ $item->id }})"
                                ></button>

                                {{-- ENQUIRY --}}
                                @auth
                                    <button
                                        type="button"
                                        class="flex-1 py-2 btn-color text-white text-sm font-semibold rounded enquiry-btn"
                                        data-hoarding-id="{{ $item->id }}"
                                        data-grace-days="{{ isset($item->grace_period_days) ? (int) $item->grace_period_days : 0 }}"

                                        data-base-price="{{ ($item->hoarding_type === 'dooh')
                                            ? ($item->price ?? 0)
                                            : ((!empty($item->monthly_price) && $item->monthly_price > 0)
                                                ? $item->monthly_price
                                                : ($item->base_monthly_price ?? 0))
                                        }}"

                                        data-base-monthly-price="{{ $item->base_monthly_price ?? 0 }}"
                                        data-hoarding-type="{{ $item->hoarding_type }}"
                                    >
                                        Enquiry Now
                                    </button>
                                @else
                                    <button
                                        class="flex-1 py-2 btn-color text-white text-sm font-semibold rounded"
                                        onclick="event.stopPropagation(); window.location.href='/login';"
                                    >
                                        Enquiry Now
                                    </button>
                                @endauth
                            </div>

                        </div>
                    </div>

                @endforeach
            </div>

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

    </div>
</div>
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
       <div class="container mx-auto px-4">
            <hr class="border-gray-200">
       </div>
@endguest
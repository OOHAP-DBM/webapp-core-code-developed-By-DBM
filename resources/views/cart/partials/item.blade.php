<style>
    .flatpickr-calendar {
        margin-top: 6px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        border-radius: 10px;
    }

    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background: #145efc !important;
        border-color: #145efc !important;
    }
</style>
<div class="bg-white border border-gray-200 rounded-xl p-4">

    <div class="flex gap-4">

        {{-- IMAGE --}}
        <div class="w-28 h-20 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
            <img
                src="{{ $item->image_url ?? 'https://via.placeholder.com/300x200' }}"
                alt="{{ $item->title }}"
                class="w-full h-full object-cover"
            >
        </div>

        {{-- CONTENT --}}
        <div class="flex-1">

            {{-- HEADER --}}
        <div class="flex justify-between items-start gap-4">

            {{-- LEFT CONTENT --}}
            <div class="flex-1">

                {{-- TITLE --}}
                <h2 class="text-lg font-semibold text-gray-900 leading-snug">
                    {{ $item->title }}
                </h2>

                {{-- LOCATION --}}
                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                    <svg width="10" height="14" viewBox="0 0 10 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.569 4.7845C9.569 4.15619 9.44525 3.53403 9.2048 2.95355C8.96436 2.37307 8.61194 1.84563 8.16765 1.40135C7.72337 0.957066 7.19593 0.604642 6.61545 0.364198C6.03497 0.123755 5.41281 0 4.7845 0C4.15619 0 3.53403 0.123755 2.95355 0.364198C2.37307 0.604642 1.84563 0.957066 1.40135 1.40135C0.957066 1.84563 0.604642 2.37307 0.364198 2.95355C0.123755 3.53403 -9.36254e-09 4.15619 0 4.7845C0 5.73251 0.279552 6.61423 0.755268 7.35788H0.7498L4.7845 13.67L8.8192 7.35788H8.81442C9.30713 6.59002 9.56904 5.69685 9.569 4.7845ZM4.7845 6.835C4.24067 6.835 3.71912 6.61897 3.33458 6.23442C2.95003 5.84988 2.734 5.32833 2.734 4.7845C2.734 4.24067 2.95003 3.71912 3.33458 3.33458C3.71912 2.95003 4.24067 2.734 4.7845 2.734C5.32833 2.734 5.84988 2.95003 6.23442 3.33458C6.61897 3.71912 6.835 4.24067 6.835 4.7845C6.835 5.32833 6.61897 5.84988 6.23442 6.23442C5.84988 6.61897 5.32833 6.835 4.7845 6.835Z" fill="#E75858"/>
                    </svg>
                    {{ $item->locality ?? '' }}
                    {{ $item->city }}, {{ $item->state }}
                </p>

                {{-- META LINE --}}
                <p class="text-xs text-gray-600 mt-1">
                    Type:
                    <span class="font-medium uppercase">{{ $item->hoarding_type }}</span>
                    &nbsp;&nbsp;•&nbsp;&nbsp;
                    Category:
                    <span class="font-medium">{{ ucfirst($item->category) }}</span>
                    &nbsp;&nbsp;•&nbsp;&nbsp;
                    Size:
                    <span class="font-medium">{{ $item->size }}</span>
                </p>
                {{-- CAMPAIGN DATE ROW --}}
                <div class="mt-3 flex items-center gap-3 text-xs">

                    {{-- CAMPAIGN DATE (FIGMA EXACT) --}}
                    <div class="relative mt-3">

                        <div
                            class="inline-flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 cursor-pointer"
                            data-campaign-trigger="{{ $item->hoarding_id }}"data-grace-days="{{ (int) ($item->grace_period_days ?? 0) }}"
                            data-block-dates='@json($item->block_dates ?? [])'
                        >

                            {{-- ICON --}}
                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-orange-50">
                                <svg class="w-4 h-4 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v9a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1z"/>
                                </svg>
                            </div>

                            {{-- TEXT --}}
                            <div class="leading-tight">
                                <p class="text-[11px] text-gray-500">Campaign Date</p>
                                <p class="text-xs font-medium text-gray-900"
                                id="campaign-text-{{ $item->hoarding_id }}">
                                    Select Date
                                </p>
                            </div>
                        </div>

                        {{-- HIDDEN INPUT (flatpickr attaches here) --}}
                        <input
                            type="text"
                            id="campaign-input-{{ $item->hoarding_id }}"
                            class="absolute opacity-0 pointer-events-none"
                            readonly
                        >
                    </div>


                    {{-- ACTION LINKS --}}
                    <button
                        type="button"
                        class="text-red-500 border border-none hover:underline cart-btn remove"
                        data-in-cart="1"
                        onclick="toggleCart(this, {{ $item->hoarding_id }})"
                         >
                    </button>


                    {{-- <a href="#" class="text-blue-500 hover:underline">
                        Bookmark
                    </a>

                    <a href="#" class="text-blue-500 hover:underline">
                        Share
                    </a> --}}

                </div>
            </div>
        </div>


           {{-- OFFERS --}}
           {{-- OFFERS --}}
            @if(!empty($item->packages) && count($item->packages))
                <div class="mt-4">
                    <p class="text-xs font-medium text-gray-600 mb-2">
                        Available Offers
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($item->packages as $pkg)
                            @include('cart.partials.offer-card', [
                                'pkg' => $pkg,
                                'item' => $item,
                                'selected' => isset($item->selected_package) && $item->selected_package && $item->selected_package->id == $pkg->id
                            ])
                        @endforeach
                    </div>
                </div>
            @endif

           {{-- PRICE --}}
            <div class="flex justify-end mt-4 text-right">

                {{-- OOH --}}
                @if($item->hoarding_type === 'ooh')
                    <div>
                        @if($item->base_monthly_price > $item->monthly_price)
                            <p
                                id="base-price-{{ $item->hoarding_id }}"
                                data-base-price="{{ $item->base_monthly_price }}"
                                class="text-sm text-gray-400 line-through {{ $item->base_monthly_price > $item->monthly_price ? '' : 'hidden' }}"
                              >
                                ₹{{ number_format($item->base_monthly_price) }}
                            </p>

                        @endif
                        <p
                            id="final-price-{{ $item->hoarding_id }}"
                            data-default-price="{{ $item->monthly_price }}"
                            class="text-lg font-semibold text-gray-900"
                          >                            
                            ₹{{ number_format($item->monthly_price) }}
                            <span class="text-sm text-gray-400">/ Month</span>
                        </p>
                    </div>
                @endif

                {{-- DOOH --}}
                @if($item->hoarding_type === 'dooh')
                    <div>
                        <p class="text-lg font-semibold text-gray-900">
                            ₹{{ number_format($item->slot_price) }}
                            <span class="text-sm text-gray-400">/ Slot</span>
                        </p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
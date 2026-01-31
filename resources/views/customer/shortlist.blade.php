@extends('layouts.app')

@section('title', 'My Shortlist')

@include('components.customer.navbar')

@section('content')
<div class="bg-white">
    <div class="max-w-[1460px] mx-auto px-6 py-6">

        {{-- HEADER --}}
<div class="bg-white p-6 mb-6
            flex flex-col sm:flex-row
            sm:items-center sm:justify-between
            gap-4 ">

    {{-- LEFT --}}
    <div class="flex items-start gap-4">
        {{-- ICON --}}
        <div class="w-11 h-11 rounded-full bg-[#daf2e7]
                    flex items-center justify-center">
            <svg width="22" height="20" viewBox="0 0 20 19" fill="#14c871">
                <path
                    d="M5.5 0.75C2.877 0.75 0.75 3.01 0.75 5.797
                       C0.75 11.375 9.75 17.75 9.75 17.75
                       C9.75 17.75 18.75 11.375 18.75 5.797
                       C18.75 2.344 16.623 0.75 14 0.75
                       C12.14 0.75 10.53 1.886 9.75 3.54
                       C8.97 1.886 7.36 0.75 5.5 0.75Z"
                />
            </svg>
        </div>

        {{-- TITLE --}}
        <div>
            <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                My Shortlist

                @if($wishlist->total() > 0)
                    <span class="text-xs font-semibold px-2 py-0.5
                                 rounded-full bg-[#daf2e7] text-[#14c871]">
                        {{ $wishlist->total() }}
                    </span>
                @endif
            </h2>

            <p class="text-sm text-gray-500">
                Hoardings you saved for later
            </p>
        </div>
    </div>


</div>


        {{-- LIST --}}
        @if($wishlist->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                @foreach($wishlist as $item)
                    @php
                        $hoarding = $item->hoarding;

                        $imageUrl = null;

                        if ($hoarding->hoarding_type === 'ooh'
                            && $hoarding->hoardingMedia->isNotEmpty()) {
                            $imageUrl = asset('storage/' . $hoarding->hoardingMedia->first()->file_path);
                        }

                        if ($hoarding->hoarding_type === 'dooh'
                            && $hoarding->doohScreen
                            && $hoarding->doohScreen->media->isNotEmpty()) {
                            $imageUrl = asset(
                                'storage/' .
                                $hoarding->doohScreen->media->sortBy('sort_order')->first()->file_path
                            );
                        }
                    @endphp

                    <div
                        id="wishlist-item-{{ $hoarding->id }}"
                        class="bg-white rounded-lg border border-gray-200 hover:shadow-lg transition-all duration-300
                               overflow-hidden group cursor-pointer flex flex-col h-full"
                        onclick="if(event.target.closest('button') === null)
                                 window.location.href='{{ route('hoardings.show', $hoarding->id) }}';"
                    >

                        {{-- IMAGE --}}
                        <div class="relative h-48 overflow-hidden bg-gray-100">

                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-500">
                                    No Image
                                </div>
                            @endif

                            {{-- BADGE --}}
                            <span class="absolute top-3 left-3 bg-red-500 text-white text-xs px-2 py-1 rounded">
                                Recommended
                            </span>

                            {{-- WISHLIST ICON (ACTIVE BY DEFAULT) --}}
                            <button
                                class="absolute top-3 right-3 w-8 h-8 rounded-full flex items-center justify-center
                                       shortlist-btn bg-[#daf2e7] is-wishlisted"
                                data-id="{{ $hoarding->id }}"
                                data-auth="{{ auth()->check() ? '1' : '0' }}"
                                data-role="{{ auth()->check() ? auth()->user()->role : '' }}"
                                data-context="wishlist"
                                onclick="event.stopPropagation(); toggleShortlist(this);"
                            >
                                <svg class="wishlist-icon" width="20" height="19" viewBox="0 0 20 19">
                                    <path
                                        d="M5.5 0.75C2.877 0.75 0.75 3.01 0.75 5.797
                                           C0.75 11.375 9.75 17.75 9.75 17.75
                                           C9.75 17.75 18.75 11.375 18.75 5.797
                                           C18.75 2.344 16.623 0.75 14 0.75
                                           C12.14 0.75 10.53 1.886 9.75 3.54
                                           C8.97 1.886 7.36 0.75 5.5 0.75Z"
                                        stroke="#14c871"
                                        fill="#14c871"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </button>
                        </div>

                        {{-- CONTENT --}}
                        <div class="p-4 flex flex-col flex-grow">

                            <h3 class="text-sm font-semibold line-clamp-1">
                                {{ $hoarding->title }}
                            </h3>

                            <p class="text-xs text-gray-500 mb-2">
                                {{ $hoarding->address }}
                            </p>

                            <div class="mb-3">

                                @if($hoarding->hoarding_type === 'ooh')

                                    @php
                                        $base  = $hoarding->base_monthly_price_display ?? $hoarding->base_monthly_price;
                                        $sale  = $hoarding->monthly_price_display ?? $hoarding->monthly_price;
                                        $hasSale = !empty($sale) && $sale > 0;
                                    @endphp

                                    {{-- MAIN PRICE --}}
                                    <div class="flex items-baseline">
                                        <span class="text-xl font-semibold text-gray-900">
                                            ₹{{ number_format($hasSale ? $sale : $base, 0) }}
                                        </span>
                                        <span class="text-lg text-black font-bold ml-1">/Month</span>
                                    </div>

                                    {{-- CUT PRICE + DISCOUNT (ONLY IF REAL SALE) --}}
                                    @if($hasSale && $base && $base > $sale)
                                        <div class="flex items-center space-x-2 mt-1">
                                            <span class="text-xs text-red-500 line-through">
                                                ₹{{ number_format($base, 0) }}
                                            </span>

                                            @php
                                                $basePrice = (float) ($base ?? 0);
                                                $salePrice = (float) ($sale ?? 0);
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

                                {{-- ================= DOOH ================= --}}
                                @else
                                    <div class="flex items-baseline">
                                        <span class="text-xl font-semibold text-gray-900">
                                            ₹{{ number_format(optional($hoarding->doohScreen)->price_per_slot) }}
                                        </span>
                                        <span class="text-lg text-black font-bold ml-1">/Second</span>
                                    </div>
                                @endif


                            </div>


       

                        <p class="text-xs text-gray-600 mb-1">
                            @if(
                                $hoarding->available_from &&
                                \Carbon\Carbon::parse($hoarding->available_from)->isFuture()
                            )
                                Hoarding Available from
                                {{ \Carbon\Carbon::parse($hoarding->available_from)->format('F d, Y') }}
                            @else
                                Hoarding Available Now
                            @endif
                        </p>
                            @php
                                $packageCount = 0;
                                if(($hoarding->price_type ?? $hoarding->hoarding_type) === 'ooh') {
                                    $packageCount = $hoarding->oohPackages()->count();
                                } elseif(($hoarding->price_type ?? $hoarding->hoarding_type) === 'dooh') {
                                    // For DOOH, count packages from the doohScreen
                                    if($hoarding->doohScreen) {
                                        $packageCount = $hoarding->doohScreen->packages()->count();
                                    }
                                }
                            @endphp

                            @if($packageCount > 0)
                                <p class="text-xs text-teal-600 font-medium mb-3">{{ $packageCount }} {{ Str::plural('Package', $packageCount) }} Available</p>
                            @else
                                <p class="text-xs text-teal-600 font-medium mb-3">No packages are in this hoarding</p>
                            @endif

                            {{-- ACTIONS --}}
                            <div class="mt-auto flex gap-2">
                                @php
                                    $isInCart = in_array($hoarding->id, $cartIds ?? []);
                                @endphp
                                 <button
                                    id="cart-btn-{{ $hoarding->id }}"
                                    data-in-cart="{{ $isInCart ? '1' : '0' }}"
                                    onclick="event.stopPropagation(); event.preventDefault(); toggleCart(this, {{ $hoarding->id }})"
                                    class="cart-btn flex-1 py-2 px-3 text-sm font-semibold rounded"
                                >
                                </button>

                                <!-- <button
                                    class="flex-1 py-2 btn-color text-white text-sm rounded enquiry-btn"
                                    data-hoarding-id="{{ $hoarding->id }}"
                                >
                                    Enquiry Now
                                </button> -->
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- PAGINATION --}}
            <div class="mt-8 flex justify-center">
                {{ $wishlist->links() }}
            </div>
        @else
            {{-- EMPTY --}}
            <div class="bg-white rounded-xl p-16 text-center text-gray-500">
                <h3 class="text-lg font-semibold mb-2">Your shortlist is empty</h3>
                <p class="mb-4">Start adding hoardings to your wishlist</p>
                <a href="{{ route('home') }}" class="btn-color text-white px-6 py-2 rounded">
                    Browse Hoardings
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

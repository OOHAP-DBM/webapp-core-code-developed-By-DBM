<div class="w-full max-w-sm">

@if($hoarding->hoarding_type === 'ooh')
    {{-- OOH BASE PRICE --}}
    @php
        $monthly = $hoarding->monthly_price;
        $base    = $hoarding->base_monthly_price;
    @endphp

    <div>
        {{-- MAIN PRICE --}}
        <div class="text-xl font-bold">
            @if(empty($monthly) || $monthly == 0)
                ₹{{ number_format($base) }}/Month
            @else
                ₹{{ number_format($monthly) }}/Month
            @endif
        </div>

        {{-- CUT PRICE --}}
        @if(
            !empty($monthly)
            && $monthly > 0
            && !empty($base)
            && $base > $monthly
        )
            <div class="text-sm text-gray-400 line-through">
                ₹{{ number_format($base) }}
            </div>
        @endif
    {{-- OOH PACKAGES (OPTIONAL) --}}
    @if($hoarding->packages->count())
        <p class="mt-4 font-semibold text-sm">Available Packages</p>

        @foreach($hoarding->packages as $pkg)

                @php
                    $basePrice = $hoarding->base_monthly_price * $pkg->min_booking_duration;
                    $discount  = ($basePrice * $pkg->discount_percent) / 100;
                    $finalPrice = $basePrice - $discount;
                @endphp

                <div class="package-card"
                    onclick="selectPackage({
                        id: {{ $pkg->id }},
                        months: {{ $pkg->min_booking_duration }},
                        discount: {{ $pkg->discount_percent }},
                        price: {{ round($finalPrice) }},
                        type: 'ooh'
                    }, this)">

                    <p class="font-medium">{{ $pkg->package_name }}</p>

                    <p class="text-xs text-gray-500">
                        {{ $pkg->min_booking_duration }} Month Package
                        @if($pkg->discount_percent)
                            • {{ $pkg->discount_percent }}% OFF
                        @endif
                    </p>

                    <p class="font-semibold">
                        ₹{{ number_format($finalPrice) }}
                    </p>

                    <p class="text-xs text-gray-400 line-through">
                        ₹{{ number_format($basePrice) }}
                    </p>
                </div>
            @endforeach

    @endif
@else
    {{-- DOOH BASE PRICE --}}
<div class="text-xl font-bold">
    ₹{{ number_format($hoarding->doohScreen->price_per_slot) }}
    <span class="text-sm text-gray-500">/Second </span>
</div>
    {{-- DOOH PACKAGES (OPTIONAL) --}}
@if($hoarding->packages->count())
    <p class="mt-4 font-semibold text-sm">Available Plans</p>

    @foreach($hoarding->packages as $pkg)

            @php
                $basePrice  = $hoarding->price_per_slot * $pkg->min_booking_duration;
                $discount   = ($basePrice * $pkg->discount_percent) / 100;
                $finalPrice = $basePrice - $discount;
            @endphp

            <div class="package-card"
                onclick="selectPackage({
                    id: {{ $pkg->id }},
                    months: {{ $pkg->min_booking_duration }},
                    discount: {{ $pkg->discount_percent }},
                    price: {{ round($finalPrice) }},
                    type: 'dooh'
                }, this)">

                <p class="font-medium">{{ $pkg->package_name }}</p>

                <p class="text-xs text-gray-500">
                    {{ $pkg->min_booking_duration }} Month Plan
                    @if($pkg->discount_percent)
                        • {{ $pkg->discount_percent }}% OFF
                    @endif
                </p>

                <p class="font-semibold">
                    ₹{{ number_format($finalPrice) }}/Month
                </p>

                <p class="text-xs text-gray-400 line-through">
                    ₹{{ number_format($basePrice) }}
                </p>
            </div>
        @endforeach

@endif
@endif
@php
    $isOwnerVendor = false;
    if(
        auth()->check()
        && auth()->user()->active_role === 'vendor'
        && isset($hoarding->vendor_id)
        && auth()->id() === (int)$hoarding->vendor_id
    ){
        $isOwnerVendor = true;
    }
@endphp
<div class="bg-gray-50 rounded-xl p-4 mt-4">

    @if($isOwnerVendor)
        <p class="text-white py-2 text-center rounded bg-[#22c55e] hover:bg-[#16a34a] mt-2">
          your own hoarding.
        </p>
    @else
        <button
            id="cart-btn-{{ $hoarding->id }}"
            data-in-cart="{{ $isInCart ? '1' : '0' }}"
            onclick="event.preventDefault(); toggleCart(this, {{ $hoarding->id }})"
            class="cart-btn cart-btn--white flex-1 py-2 px-3 text-sm font-semibold rounded w-full
                {{ $isInCart ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }}">
            
            {{ $isInCart ? 'Remove from Sortlist' : 'Add to Sortlist' }}
        </button>
        @auth
            <a href="javascript:void(0)"
                class="mt-3 block text-center text-xs text-teal-600 hover:text-teal-700 font-medium"
                data-hoarding-id="{{ $hoarding->id }}"
                data-hoarding-type="{{ $hoarding->hoarding_type ?? 'ooh' }}"
                data-base-price="{{ (!empty($hoarding->monthly_price) && $hoarding->monthly_price > 0)
                            ? $hoarding->monthly_price
                            : ($hoarding->base_monthly_price ?? 0)
                        }}"
                data-slot-duration="{{ $hoarding->doohScreen->slot_duration_seconds ?? '' }}"
                data-total-slots="{{ $hoarding->doohScreen->total_slots_per_day ?? '' }}"
                data-grace-days="{{ (int) $hoarding->grace_period_days }}"
                data-count="1"
                onclick="openEnquiryModal({
                    id: this.getAttribute('data-hoarding-id'),
                    basePrice: this.getAttribute('data-base-price'),
                    hoardingType: this.getAttribute('data-hoarding-type'),
                    graceDays: this.getAttribute('data-grace-days'),
                    count: this.getAttribute('data-count')
                })"
            >
                Enquire Now
            </a>
        @else
            <a href="/login?message={{ urlencode('Please login to raise an enquiry.') }}"
               class="mt-3 block text-center text-xs text-teal-600 hover:text-teal-700 font-medium">
                Enquire Now
            </a>
        @endauth
    @endif

</div>

</div>
<style>
.cart-btn--white {
    color: #fff !important;
    background-color: #22c55e;
}
.cart-btn--white:hover {
    background-color: #16a34a !important;
    color: #fff !important;
}


</style>
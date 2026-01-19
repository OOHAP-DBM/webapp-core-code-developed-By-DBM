<div class="w-full max-w-sm">

@if($hoarding->hoarding_type === 'ooh')
    {{-- OOH BASE PRICE --}}
<div>
    <div class="text-xl font-bold">
        ₹{{ number_format($hoarding->monthly_price) }}/Month
    </div>

    @if($hoarding->base_monthly_price)
        <div class="text-sm text-gray-400 line-through">
            ₹{{ number_format($hoarding->base_monthly_price) }}
        </div>
    @endif
</div>
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
    ₹{{ number_format($hoarding->price_per_slot) }}
    <span class="text-sm text-gray-500">/10 Second Slot</span>
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

{{-- FINAL PRICE --}}
<div class="bg-gray-50 rounded-xl p-4 mt-4">
    @auth
        @if(auth()->user()->hasRole('customer'))
            <button class="w-full bg-green-500 text-white py-3 rounded-md text-sm font-semibold">
                Sort list
            </button>
        @endif
    @endauth
{{-- 
    <a href="javascript:void(0)"
       class="mt-3 block text-center text-xs text-teal-600 hover:text-teal-700 font-medium"
       data-hoarding-id="{{ $hoarding->id }}"
    data-hoarding-type="{{ $hoarding->hoarding_type ?? 'ooh' }}"
       data-base-price="{{ $hoarding->hoarding_type === 'dooh' ? ($hoarding->doohScreen->price_per_slot ?? 0) : ($hoarding->monthly_price ?? 0) }}"
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
    </a> --}}
    @auth
    @if(auth()->user()->hasRole('customer'))
    <a href="javascript:void(0)"
       class="mt-3 block text-center text-xs text-teal-600 hover:text-teal-700 font-medium enquiry-btn"
       data-hoarding-id="{{ $hoarding->id }}"
       data-hoarding-type="{{ $hoarding->hoarding_type ?? 'ooh' }}"
       data-base-price="{{ $hoarding->hoarding_type === 'dooh' ? ($hoarding->doohScreen->price_per_slot ?? 0) : ($hoarding->monthly_price ?? 0) }}"
       data-grace-days="{{ (int) $hoarding->grace_period_days }}"
       data-count="1"
    >
        Enquire Now
    </a>
    @else
    <span class="mt-3 block text-center text-xs text-gray-400 font-medium cursor-not-allowed" title="Only customers can raise enquiries">
        Enquire Now (Customers Only)
    </span>
    @endif
@else
<a href="{{ route('login') }}?intended={{ urlencode(url()->current()) }}" 
   class="mt-3 block text-center text-xs text-teal-600 hover:text-teal-700 font-medium">
    Login to Enquire
</a>
@endauth




</div>

</div>

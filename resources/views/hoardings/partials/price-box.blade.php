<div class="w-full max-w-sm">

@php
    // Always use OOH price columns for both types
    $monthly = $hoarding->monthly_price;
    $base    = $hoarding->base_monthly_price;
    $isDooh = $hoarding->hoarding_type === 'dooh';
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

    {{-- PACKAGES (OPTIONAL) --}}
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
                    type: '{{ $isDooh ? 'dooh' : 'ooh' }}'
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
    <div class="bg-white border border-gray-200 p-4 mt-4" style="border-radius:5px;">
        @if($isOwnerVendor)
            <div class="flex justify-center mt-2">
                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-3 py-2 rounded-full">
                    ✔ Your Own Hoarding
                </span>
            </div>
            @else
                <button
                    id="cart-btn-{{ $hoarding->id }}"
                    data-in-cart="{{ $isInCart ? '1' : '0' }}"
                    data-auth="{{ auth()->check() ? '1' : '0' }}"
                    onclick="event.preventDefault(); toggleCart(this, {{ $hoarding->id }})"
                    class="cart-btn cart-btn--white flex-1 py-2 px-3 text-sm font-semibold rounded w-full
                        {{ $isInCart ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }}">
                    
                    {{ $isInCart ? 'Remove from Shortlist' : 'Add to Shortlist' }}
                </button>
                @auth
                    <div class="text-center">
                        <button
                            type="button"
                            class="py-2 px-3 text-teal-600 hover:text-teal-700 font-medium text-sm font-semibold rounded enquiry-btn cursor-pointer"
                            data-hoarding-id="{{ $hoarding->id }}"
                            data-grace-days="{{ (int) $hoarding->grace_period_days }}"
                            data-base-price="{{ (!empty($hoarding->monthly_price) && $hoarding->monthly_price > 0)
                                ? $hoarding->monthly_price
                                : ($hoarding->base_monthly_price ?? 0)
                            }}"
                            data-slot-duration="{{ $hoarding->doohScreen->slot_duration_seconds ?? '' }}"
                            data-total-slots="{{ $hoarding->doohScreen->total_slots_per_day ?? '' }}"
                            data-base-monthly-price="{{ $hoarding->base_monthly_price ?? 0 }}"
                            data-hoarding-type="{{ $hoarding->hoarding_type}}"
                        >
                            Enquiry Now
                        </button>
                    </div>
                @else
                    <a href="/login?message={{ urlencode('Please login to raise an enquiry.') }}"
                    class="mt-3 block text-center text-xs text-teal-600 hover:text-teal-700 font-medium">
                        Enquire Now
                    </a>
                @endauth
        @endif
    </div>

</div>
<div class="vendor-card border border-gray-200 p-6 bg-white shadow-sm mt-4 mb-4">

    <div class="flex items-start gap-6">

        {{-- Vendor Image --}}
        <div class="w-20 h-20 flex-shrink-0 rounded-full overflow-hidden border border-gray-200">
            <img
                src="{{ route('view-avatar', $hoarding->vendor->id) }}?v={{ optional($hoarding->vendor->updated_at)->timestamp ?? time() }}"
                alt="Vendor Image"
                class="w-full h-full object-cover"
                onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($hoarding->vendor->name ?? 'N/A') }}&background=22c55e&color=fff&size=128'"
            >
        </div>

        {{-- Vendor Details --}}
        <div class="flex-1">

            <div class="flex items-center justify-between">

                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $hoarding->vendor->name ?? 'N/A' }}
                    </h2>

                    <p class="text-gray-500 text-sm">
                        Member since {{ optional($hoarding->vendor->created_at)->format('Y') }}
                    </p>
                </div>

                {{-- Verified Badge --}}
                <span class="flex items-center gap-2 bg-green-100 text-green-700 px-3 py-1 rounded-lg text-sm font-semibold" style="margin-top:0;">
                    Verified
                    <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 0L6 3H2L3 7L0 9L3 11L2 15H6L7 18L10 16L13 18L14 15H18L17 11L20 9L17 7L18 3H14L13 0L10 2L7 0ZM14 5L15 6L8 13L5 10L6 9L8 11L14 5Z" fill="#009A5C"/>
                    </svg>
                </span>

            </div>

            {{-- Contact Buttons --}}
            <div class="flex gap-4 mt-4">

                <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ $hoarding->vendor->email }}"
                    target="_blank"
                    class="flex items-center gap-2 px-5 py-1 rounded bg-orange-200 text-orange-800 font-medium hover:bg-orange-300 transition w-full max-w-xs justify-center"
                        <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.75 0.75V0.125C0.58424 0.125 0.425268 0.190848 0.308058 0.308058C0.190848 0.425268 0.125 0.58424 0.125 0.75H0.75ZM15.75 0.75H16.375C16.375 0.58424 16.3092 0.425268 16.1919 0.308058C16.0747 0.190848 15.9158 0.125 15.75 0.125V0.75ZM0.75 1.375H15.75V0.125H0.75V1.375ZM15.125 0.75V10.75H16.375V0.75H15.125ZM14.0833 11.7917H2.41667V13.0417H14.0833V11.7917ZM1.375 10.75V0.75H0.125V10.75H1.375ZM2.41667 11.7917C1.84167 11.7917 1.375 11.325 1.375 10.75H0.125C0.125 11.3578 0.366443 11.9407 0.796214 12.3705C1.22598 12.8002 1.80888 13.0417 2.41667 13.0417V11.7917ZM15.125 10.75C15.125 11.325 14.6583 11.7917 14.0833 11.7917V13.0417C14.6911 13.0417 15.274 12.8002 15.7038 12.3705C16.1336 11.9407 16.375 11.3578 16.375 10.75H15.125Z" fill="#AD4800"/>
                        <path d="M0.75 0.75L8.25 8.25L15.75 0.75" stroke="#AD4800" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Email
                    </a>

                     <a href="tel:{{ $hoarding->vendor->vendorProfile->phone ?? $hoarding->vendor->phone }}"
                           class="flex items-center gap-2 px-5 py-1 rounded bg-blue-300 text-blue-900 font-medium w-full max-w-xs justify-center"

                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14.9987 10.3833L10.607 9.875L8.50703 11.975C6.14153 10.7716 4.21875 8.84884 3.01536 6.48333L5.1237 4.375L4.61536 0H0.0236981C-0.459635 8.48333 6.51536 15.4583 14.9987 14.975V10.3833Z" fill="#0089E1"/>
                    </svg>
                    Call
                </a>

            </div>

            {{-- Bottom Link --}}
            <div class="mt-6">
                <a href="{{ route('vendors.show', $hoarding->vendor->id) }}"
                   class="flex items-center text-green-600 font-semibold hover:underline">

                    View all hoardings

                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>

                </a>
            </div>

        </div>

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
.vendor-card{
    border-radius: 5px;
}
</style>
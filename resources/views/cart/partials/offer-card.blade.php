@php
    $duration = $pkg->duration ?? ($pkg->min_booking_duration ?? 1);
    $discountPercent = $pkg->discount_percent ?? 0;

    if (($item->hoarding_type ?? null) === 'ooh') {
        $basePrice = $item->base_monthly_price ?? 0;
        $totalBase = $basePrice * $duration;
        $discountAmount = $totalBase * $discountPercent / 100;
        $finalPrice = $totalBase - $discountAmount;
        $saveAmount = $discountAmount;
    } elseif (($item->hoarding_type ?? null) === 'dooh') {
        $basePrice = $item->slot_price ?? 0;
        if ($basePrice == 0) {
            $basePrice = $item->base_monthly_price ?? 0;
        }
        $totalBase = $basePrice;
        $discountAmount = $totalBase * $discountPercent / 100;
        $finalPrice = $totalBase - $discountAmount;
        $saveAmount = $discountAmount;
    } else {
        $basePrice = 0;
        $totalBase = 0;
        $finalPrice = 0;
        $saveAmount = 0;
    }
@endphp

<!-- Debug: DOOH package info -->
@if($item->hoarding_type === 'dooh')
    <script>
        console.log(`🔍 DOOH Package Debug:`, {
            hoardingId: {{ $item->hoarding_id }},
            packageId: {{ $pkg->id }},
            slotPrice: {{ $item->slot_price ?? 'null' }},
            basePrice: {{ $basePrice }},
            finalPrice: {{ $finalPrice }}
        });
    </script>
@endif

<div 
    class="relative bg-[#ededed] rounded-lg p-4 cursor-pointer transition hover:ring-2 hover:ring-green-400 package-card-{{ $item->hoarding_id }}"
    data-hoarding-id="{{ $item->hoarding_id }}"
    data-package-id="{{ $pkg->id }}"
    data-package-name="{{ $pkg->package_name }}"
    data-final-price="{{ $finalPrice }}"
    data-base-price="{{ $basePrice }}"
    data-hoarding-type="{{ $item->hoarding_type }}"
    onclick="handlePackageClick(this)"
>
    @if(isset($selected) && $selected)
        <div class="selected-strip absolute top-0 left-0 right-0 py-1 rounded-t-lg bg-green-600 text-white text-xs font-semibold shadow-md block w-full text-center">
            Selected
        </div>
    @else
        <div class="selected-strip absolute top-0 left-0 right-0 py-1 rounded-t-lg bg-green-600 text-white text-xs font-semibold shadow-md hidden w-full text-center">
            Selected
        </div>
    @endif

    <p class="text-sm font-semibold mt-1 text-gray-900">
        {{ $pkg->package_name }}
    </p>
    @php
        $services = $pkg->services_included;
        if (is_string($services)) {
            $services = json_decode($services, true);
            if (is_string($services)) {
                $services = json_decode($services, true);
            }
        }
    @endphp
    @if(is_array($services) && count($services))
        <p class="text-[11px] text-gray-500 mt-0.5">
            {{ implode(' + ', $services) }} included
        </p>
    @endif

    <div class="mt-3 flex items-center justify-between">
        <div>
            <p class="text-xs text-red-500 line-through">
                ₹{{ number_format($basePrice) }}
            </p>
            <p class="text-lg font-semibold text-gray-900">
                ₹{{ number_format($finalPrice) }}
            </p>
        </div>
        @if($discountPercent > 0)
            <span class="text-[11px] font-semibold text-green-700 bg-green-100 px-2 py-1 rounded-full">
                SAVE ₹{{ number_format($saveAmount) }}
            </span>
        @endif
    </div>
    <p class="text-gray-500 mt-1">
         Duration: {{ $pkg->min_booking_duration ?? 1 }} {{ $pkg->duration_unit ?? 'month(s)' }}
    </p>
</div>
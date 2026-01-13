<div class="relative bg-[#ededed] rounded-lg p-4 cursor-pointer transition hover:ring-2 hover:ring-green-400 package-card-{{ $item->hoarding_id }}">
    @if(isset($selected) && $selected)
        <div class="selected-strip absolute -top-2 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-green-600 text-white text-xs font-semibold shadow-md block">
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

    @php
        // Use correct base price for each hoarding type
        if (($item->hoarding_type ?? null) === 'ooh') {
            $basePrice = $item->base_monthly_price ?? 0;
        } elseif (($item->hoarding_type ?? null) === 'dooh') {
            $basePrice = $item->slot_price ?? 0;
        } else {
            $basePrice = 0;
        }
        $discountPercent = $pkg->discount_percent ?? 0;
        $finalPrice = \Modules\Cart\Services\CartService::calculateDiscountedPrice($basePrice, $discountPercent);
        $saveAmount = $basePrice - $finalPrice;
    @endphp
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
        Min Duration: {{ $pkg->min_booking_duration ?? 1 }} {{ $pkg->duration_unit ?? 'month(s)' }}
    </p>
</div>
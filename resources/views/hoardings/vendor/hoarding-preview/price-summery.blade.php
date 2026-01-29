<div class="p-4">
    <!-- Title -->
    <h3 class="text-base font-semibold mb-5 text-[var(--accent-color)]">
        Price Summary
    </h3>

    <div class="space-y-2 text-sm">
        <!-- Taxes -->
        <div class="grid grid-cols-3 gap-4 items-center">
            <div class="flex items-center gap-1 text-gray-600">
                <span>Taxes</span>
                <span class="inline-flex items-center justify-center w-3.5 h-3.5 text-[10px] rounded-full bg-gray-200 text-gray-600">?</span>
            </div>
            <span class="col-span-2 text-gray-900">
                ₹ {{ number_format($hoarding->tax_amount ?? ($hoarding->doohScreen->tax_amount ?? 18)) }}
            </span>
        </div>

        <!-- Base Price -->
        <div class="grid grid-cols-3 gap-4 items-center">
            <span class="text-gray-600">Base price</span>
            <span class="col-span-2 text-gray-900">
                ₹ {{ number_format(
                    $hoarding->base_price ??
                    $hoarding->base_monthly_price ??
                    ($hoarding->doohScreen->base_price ?? 0)
                ) }}
            </span>
        </div>

        <!-- Discount -->
        <!-- <div class="grid grid-cols-3 gap-4 items-center">
            <span class="text-gray-600">Discount</span>
            <span class="col-span-2 text-gray-900">
                {{ $hoarding->discount_percent ?? ($hoarding->doohScreen->discount_percent ?? 0) }}%
            </span>
        </div> -->

        <!-- Total -->
        <div class="grid grid-cols-3 gap-4 items-center">
            <span class="font-semibold text-gray-900">Total</span>
            <span class="col-span-2 font-semibold text-gray-900">
                ₹ {{ number_format($hoarding->total_price ?? ($hoarding->doohScreen->total_price ?? 0)) }}
                <span class="text-xs font-medium text-gray-500">/PM</span>
            </span>
        </div>
    </div>
</div>

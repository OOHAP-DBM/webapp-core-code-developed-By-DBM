<div class="p-4">
    <!-- Title -->
    <h3 class="text-base font-semibold mb-5 text-[var(--accent-color)]">
        Pricing
    </h3>

    <!-- ================= MONTHLY ================= -->
    <div class="mb-6">
        <h4 class="text-sm font-semibold text-emerald-600 mb-3">
            Rental Offering
        </h4>

        <div class="grid grid-cols-3 gap-4 text-sm mb-4">
            <span class="text-gray-500">Base Monthly Price</span>
            <span class="col-span-2 font-medium text-gray-900">
                ₹ {{ number_format($hoarding->base_monthly_price ?? ($hoarding->doohScreen->base_monthly_price ?? 0)) }}
            </span>

            <span class="text-gray-500">Offering Discount on Base Monthly Price</span>
            <span class="col-span-2 font-medium text-gray-900">
                Offered Monthly Price ₹ {{ number_format($hoarding->monthly_price ?? ($hoarding->doohScreen->monthly_price ?? 0)) }}/PM
            </span>
        </div>

        <!-- Long Term Offers -->
        <h5 class="text-sm font-semibold text-emerald-600 mb-2">
            Long term offers
        </h5>

        <div class="grid grid-cols-3 gap-4 text-sm mb-3">
            <span class="text-gray-500">Offering long term booking discount?</span>
            <span class="col-span-2 font-medium">
                {{ ($hoarding->monthly_long_term_discount ?? ($hoarding->doohScreen->monthly_long_term_discount ?? false)) ? 'Yes' : 'No' }}
            </span>

            <span class="text-gray-500">On which price you will offer long term discount?</span>
            <span class="col-span-2 font-medium">
                {{ ucfirst(str_replace('_', ' ', $hoarding->monthly_discount_on ?? ($hoarding->doohScreen->monthly_discount_on ?? 'base monthly price'))) }}
            </span>
        </div>

        <!-- Monthly Offers Boxes -->
        @php
            $monthlyOffers = is_string($hoarding->monthly_offers ?? null)
                ? json_decode($hoarding->monthly_offers, true)
                : ($hoarding->monthly_offers ?? ($hoarding->doohScreen->monthly_offers ?? []));
        @endphp

        <div class="space-y-2">
            @forelse($monthlyOffers as $offer)
                <div class="grid grid-cols-4 gap-4 text-xs border border-dashed border-emerald-300 rounded px-3 py-2">
                    <span>{{ $offer['duration'] ?? '-' }}</span>
                    <span>{{ $offer['discount'] ?? '-' }}</span>
                    <span>₹ {{ number_format($offer['price'] ?? 0) }}</span>
                    <span class="text-emerald-600 font-semibold">
                        ₹ {{ number_format($offer['final_price'] ?? 0) }}
                    </span>
                </div>
            @empty
                <p class="text-xs text-gray-400">No monthly offers provided</p>
            @endforelse
        </div>
    </div>

    <!-- ================= WEEKLY ================= -->
    <div class="mb-6 pt-4 border-t border-gray-300">
        <h4 class="text-sm font-semibold text-emerald-600 mb-3">
            Rental Offering
        </h4>

        <div class="grid grid-cols-3 gap-4 text-sm mb-4">
            <span class="text-gray-500">Base Weekly Price</span>
            <span class="col-span-2 font-medium text-gray-900">
                ₹ {{ number_format($hoarding->base_weekly_price ?? ($hoarding->doohScreen->base_weekly_price ?? 0)) }}
            </span>

            <span class="text-gray-500">Offering Discount on Base Weekly Price</span>
            <span class="col-span-2 font-medium text-gray-900">
                Offered Weekly Price ₹ {{ number_format($hoarding->weekly_price ?? ($hoarding->doohScreen->weekly_price ?? 0)) }}
            </span>
        </div>

        <h5 class="text-sm font-semibold text-emerald-600 mb-2">
            Long term offers
        </h5>

        <div class="grid grid-cols-3 gap-4 text-sm mb-3">
            <span class="text-gray-500">Offering long term booking discount?</span>
            <span class="col-span-2 font-medium">
                {{ ($hoarding->weekly_long_term_discount ?? ($hoarding->doohScreen->weekly_long_term_discount ?? false)) ? 'Yes' : 'No' }}
            </span>

            <span class="text-gray-500">On which price you will offer long term discount?</span>
            <span class="col-span-2 font-medium">
                {{ ucfirst(str_replace('_', ' ', $hoarding->weekly_discount_on ?? ($hoarding->doohScreen->weekly_discount_on ?? 'base weekly price'))) }}
            </span>
        </div>

        @php
            $weeklyOffers = is_string($hoarding->weekly_offers ?? null)
                ? json_decode($hoarding->weekly_offers, true)
                : ($hoarding->weekly_offers ?? ($hoarding->doohScreen->weekly_offers ?? []));
        @endphp

        <div class="space-y-2">
            @forelse($weeklyOffers as $offer)
                <div class="grid grid-cols-4 gap-4 text-xs border border-dashed border-emerald-300 rounded px-3 py-2">
                    <span>{{ $offer['duration'] ?? '-' }}</span>
                    <span>{{ $offer['discount'] ?? '-' }}</span>
                    <span>₹ {{ number_format($offer['price'] ?? 0) }}</span>
                    <span class="text-emerald-600 font-semibold">
                        ₹ {{ number_format($offer['final_price'] ?? 0) }}
                    </span>
                </div>
            @empty
                <p class="text-xs text-gray-400">No weekly offers provided</p>
            @endforelse
        </div>
    </div>

    <!-- ================= SERVICES ================= -->
    <div class="pt-4 border-t border-gray-300">
        <h4 class="text-sm font-semibold text-emerald-600 mb-3">
            Service Includes
        </h4>

        <div class="space-y-2 text-sm">
            <div class="grid grid-cols-3 gap-4">
                <span class="text-gray-500">Graphics Included</span>
                <span class="col-span-2 font-medium">
                    {{ $hoarding->graphics_included ? 'Yes' : 'No' }}
                    @if(!$hoarding->graphics_included && $hoarding->graphics_price)
                        | Price - ₹{{ number_format($hoarding->graphics_price) }}
                    @endif
                </span>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <span class="text-gray-500">Printing Included</span>
                <span class="col-span-2 font-medium">
                    {{ $hoarding->printing_included ? 'Yes' : 'No' }}
                </span>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <span class="text-gray-500">Mounting Included</span>
                <span class="col-span-2 font-medium">
                    {{ $hoarding->mounting_included ? 'Yes' : 'No' }}
                </span>
            </div>
        </div>

        <h4 class="text-sm font-semibold text-emerald-600 mt-5 mb-3">
            Extra Services
        </h4>

        <div class="space-y-2 text-sm">
            <div class="grid grid-cols-3 gap-4">
                <span class="text-gray-500">Remounting Service Charge</span>
                <span class="col-span-2 text-orange-500 font-medium">
                    {{ $hoarding->remounting_charge ?? ($hoarding->doohScreen->remounting_charge ?? 'Not Provided') }}
                </span>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <span class="text-gray-500">Survey Charge</span>
                <span class="col-span-2 text-orange-500 font-medium">
                    {{ $hoarding->survey_charge ?? ($hoarding->doohScreen->survey_charge ?? 'Not Provided') }}
                </span>
            </div>
        </div>
    </div>
</div>

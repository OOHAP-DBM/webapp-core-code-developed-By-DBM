<div class="p-4">
    <!-- Title -->
    <h3 class="text-base font-semibold mb-4 text-[var(--accent-color)]">
        Your Long Term Offer
    </h3>

    @php
        $longTermOffers = is_string($hoarding->long_term_offers ?? null)
            ? json_decode($hoarding->long_term_offers, true)
            : ($hoarding->long_term_offers ?? ($hoarding->doohScreen->long_term_offers ?? []));
    @endphp

    <div class="space-y-2 text-sm">
        @forelse($longTermOffers as $index => $offer)
            <div class="grid grid-cols-3 gap-4 items-center">
                <!-- Left (Label) -->
                <div class="text-gray-700">
                    <span class="font-medium">
                        Offer {{ $index + 1 }}
                    </span>
                    <span class="ml-2 text-gray-600">
                        {{ $offer['duration'] ?? '-' }} Month
                    </span>
                </div>

                <!-- Right (Value) -->
                <div class="col-span-2 font-semibold text-gray-900">
                    ₹ {{ number_format($offer['price'] ?? 0) }}
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">
                No long term offers provided
            </p>
        @endforelse
    </div>
</div>

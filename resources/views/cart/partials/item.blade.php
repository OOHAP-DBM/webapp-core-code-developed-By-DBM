<div class="bg-white border rounded-xl p-4">

    <div class="flex gap-4">

        {{-- IMAGE --}}
        <div class="w-28 h-20 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
            <img src="https://via.placeholder.com/300x200"
                 class="w-full h-full object-cover">
        </div>

        {{-- CONTENT --}}
        <div class="flex-1">

            <div class="flex justify-between items-start">
                <div>
                    <h2 class="font-semibold text-gray-900">
                        {{ $item->title }}
                    </h2>
                    <p class="text-xs text-gray-500">
                        {{ $item->city }}, {{ $item->state }}
                    </p>
                    <p class="text-xs text-indigo-400 mt-1 uppercase">
                        {{ strtoupper($item->hoarding_type) }} HOARDING
                    </p>
                </div>

                <button
                    onclick="toggleCart({{ $item->hoarding_id }}, true)"
                    class="text-xs text-red-500 hover:underline">
                    Remove
                </button>
            </div>

            {{-- CAMPAIGN --}}
            <div class="flex items-center gap-2 text-xs text-gray-500 mt-2">
                <span class="px-2 py-0.5 bg-orange-50 text-orange-600 rounded">
                    Campaign Date
                </span>
                <span>Jan 12, 25 – Feb 12, 25</span>
            </div>

            {{-- OFFERS --}}
            @if(count($item->packages))
            <div class="mt-4">
                <p class="text-xs font-medium text-gray-600 mb-2">
                    Available Offers
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($item->packages as $pkg)
                        @include('cart.partials.offer-card', [
                            'pkg' => $pkg
                        ])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- PRICE --}}
            <div class="flex justify-end mt-4">
                <p class="text-lg font-semibold text-gray-900">
                    ₹60,000<span class="text-sm text-gray-400">/ Month</span>
                </p>
            </div>

        </div>
    </div>
</div>

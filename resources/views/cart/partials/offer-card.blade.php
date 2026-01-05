<div class="border rounded-lg p-3 text-xs hover:border-gray-400 transition">

    <p class="font-semibold text-gray-900 mb-1">
        {{ $pkg->package_name }}
    </p>

    <p class="text-gray-500">
        Min Duration: {{ $pkg->min_booking_duration ?? 1 }} month(s)
    </p>

    @if($pkg->discount_percent > 0)
        <p class="text-green-600 mt-1">
            {{ number_format($pkg->discount_percent,2) }}% discount
        </p>
    @endif

    <div class="mt-2">
        <p class="line-through text-gray-400">
            ₹75,000
        </p>
        <p class="font-semibold text-gray-900">
            ₹60,000
        </p>
    </div>
</div>

@extends('layouts.app')

@section('title', 'My Cart')

@section('content')
@include('components.customer.navbar')

<div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ================= LEFT : CART ITEMS ================= --}}
    <div class="lg:col-span-2 space-y-6">

        <h2 class="text-lg font-semibold text-gray-800">
            Shortlisted ({{ count($items) }} Hoardings)
        </h2>

        @forelse($items as $item)
            <div class="bg-white border rounded-lg p-4 flex gap-4">

                {{-- IMAGE PLACEHOLDER --}}
                <div class="w-40 h-28 bg-gray-100 rounded flex items-center justify-center">
                    <span class="text-xs text-gray-400">Image</span>
                </div>

                {{-- DETAILS --}}
                <div class="flex-1">

                    <h3 class="text-sm font-semibold text-gray-900">
                        {{ $item->title }}
                    </h3>

                    <p class="text-xs text-gray-500 mt-1">
                        {{ ucfirst($item->hoarding_type) }} • {{ $item->city }}, {{ $item->state }}
                    </p>

                    {{-- PRICE INFO --}}
                    <div class="mt-3">

                        @if($item->hoarding_type === 'ooh')
                            <p class="text-sm text-gray-700 font-semibold">
                                ₹{{ number_format($item->price, 0) }} / Month
                            </p>
                            <p class="text-xs text-gray-500">
                                Monthly OOH Hoarding Price
                            </p>
                        @else
                            <p class="text-sm text-gray-700 font-semibold">
                                ₹{{ number_format($item->price, 2) }} per {{ $item->slot_duration_seconds ?? 10 }} sec slot
                            </p>
                            <p class="text-xs text-gray-500">
                                DOOH Slot Based Pricing
                            </p>
                        @endif

                    </div>

                    {{-- ACTIONS --}}
                    <div class="mt-4 flex items-center gap-4 text-xs">

                        <button
                            onclick="removeFromCart({{ $item->hoarding_id }})"
                            class="text-red-600 hover:underline">
                            Remove
                        </button>

                        <a href="{{ route('hoardings.show', $item->hoarding_id) }}"
                           class="text-teal-600 hover:underline">
                            View Details
                        </a>

                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border rounded-lg p-6 text-center text-gray-500">
                Your cart is empty
            </div>
        @endforelse
    </div>

    {{-- ================= RIGHT : SUMMARY ================= --}}
    <div class="bg-white border rounded-lg p-5 h-fit sticky top-24">

        <h3 class="text-sm font-semibold text-gray-800 mb-4">
            Cart Summary
        </h3>

        <div class="flex justify-between text-sm mb-2">
            <span>Subtotal</span>
            <span class="font-semibold">
                ₹{{ number_format($summary->subtotal ?? 0, 2) }}
            </span>
        </div>

        <div class="flex justify-between text-sm mb-4 text-green-600">
            <span>Discount</span>
            <span>₹0.00</span>
        </div>

        <hr class="mb-4">

        <div class="flex justify-between text-base font-semibold mb-4">
            <span>Total</span>
            <span>
                ₹{{ number_format($summary->subtotal ?? 0, 2) }}
            </span>
        </div>

        <button
            class="w-full bg-yellow-400 hover:bg-yellow-500 text-black py-2 rounded font-semibold">
            Book Now
        </button>

        <p class="text-xs text-gray-400 mt-3 text-center">
            Taxes calculated at checkout
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function removeFromCart(hoardingId) {
    fetch("{{ route('cart.remove') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            hoarding_id: hoardingId
        })
    })
    .then(res => res.json())
    .then(() => location.reload())
    .catch(() => alert('Failed to remove item'));
}
</script>
@endpush

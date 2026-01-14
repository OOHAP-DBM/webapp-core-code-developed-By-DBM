<div class="flex flex-col items-center justify-center space-y-4">

    {{-- PAN Card Preview --}}
    <div class="bg-white rounded-lg shadow border p-3">
        @if(!empty($vendor->pan_card_document))
            <img
                src="{{ route('vendor.pan.view', $vendor->id) }}"
                alt="PAN Card"
                class="max-w-full max-h-48 rounded-md object-contain"
            />

        @else
            <img
                src="https://upload.wikimedia.org/wikipedia/commons/5/5e/PAN_Card.jpg"
                alt="PAN Card"
                class="max-w-full max-h-48 rounded-md object-contain"
            >
        @endif
    </div>

    {{-- PAN Number --}}
    <div class="text-sm text-gray-700">
        <span class="font-medium">PAN Number:</span>
        {{ auth()->user()->pan ?? 'XXXXXXXXXX' }}
    </div>

    <div class="pt-2">
        <button
            type="button"
            @click="showModal = false"
            class="px-6 py-2 border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50"
        >
            Close
        </button>
    </div>

</div>
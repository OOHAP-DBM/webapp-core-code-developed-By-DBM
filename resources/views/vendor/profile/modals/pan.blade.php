<div class="flex flex-col items-center justify-center space-y-4">

    {{-- PAN Card Preview --}}
    <div class="bg-white rounded-none  shadow border p-3">
        @if(!empty($vendor->pan_card_document))
            <img
                src="{{ route('vendor.pan.view', $vendor->id) }}"
                alt="PAN Card"
                class="max-w-full p-4 max-h-48 rounded-md object-contain"
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
</div>
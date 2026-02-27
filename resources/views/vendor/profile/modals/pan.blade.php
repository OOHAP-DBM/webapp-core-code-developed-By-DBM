<div class="flex flex-col items-center justify-center space-y-4 p-2">

    {{-- PAN Card Preview --}}
    <div class="bg-white rounded-none  shadow  p-3">
        @if(!empty($vendor->pan_card_document))
            <img
                src="{{ route('vendor.pan.view', $vendor->id) }}"
                alt="PAN Card"
                class="max-w-full p-4 max-h-48 rounded-md object-contain"
            />

        @else
            <p class="text-sm text-gray-500 italic">
                PAN card has not been uploaded yet.
            </p>
        @endif
    </div>

    {{-- PAN Number --}}
    <div class="text-sm text-gray-700">
        <span class="font-medium">PAN Number:</span>
        {{ $vendor->pan ?: ($vendor->user->pan ?? '') }}
    </div>
</div>
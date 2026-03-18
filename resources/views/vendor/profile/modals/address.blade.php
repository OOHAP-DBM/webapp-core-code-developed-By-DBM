<form
    method="POST"
    action="{{ route('vendor.profile.update') }}"
    class="space-y-5"
>
    @csrf
    @method('PUT')

    <input type="hidden" name="section" value="address">

    <h2 class="text-lg font-semibold text-gray-900">
        Edit Address
    </h2>

    <div class="space-y-4 text-sm">

        {{-- Street Address --}}
        <div>
            <label class="block text-gray-600 mb-1">
                Street Address <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                name="registered_address"
                value="{{ $vendor->registered_address }}"
                required
                placeholder="Enter your street address"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500"
            >
        </div>

        {{-- Pincode --}}
        <div>
            <label class="block text-gray-600 mb-1">Pincode</label>
            <input
                type="text"
                id="vendor-pincode"
                name="pincode"
                value="{{ $vendor->pincode }}"
                maxlength="6"
                placeholder="Enter 6-digit pincode"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500"
            >
            <p id="vendor-pincode-error" class="text-red-500 text-xs mt-1 hidden"></p>
        </div>

        {{-- City --}}
        <div>
            <label class="block text-gray-600 mb-1">City</label>
            <input
                type="text"
                id="vendor-city"
                name="city"
                value="{{ $vendor->city }}"
                placeholder="Auto-filled from pincode"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500"
            >
        </div>

        {{-- State --}}
        <div>
            <label class="block text-gray-600 mb-1">State</label>
            <input
                type="text"
                id="vendor-state"
                name="state"
                value="{{ $vendor->state }}"
                placeholder="Auto-filled from pincode"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500"
            >
        </div>

        {{-- Country --}}
        <div>
            <label class="block text-gray-600 mb-1">Country</label>
            <input
                type="text"
                id="vendor-country"
                name="country"
                value="{{ $vendor->country ?? 'India' }}"
                placeholder="Auto-filled from pincode"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500"
            >
        </div>

    </div>

    {{-- Footer --}}
    <div class="flex justify-end gap-3 pt-4">
        <button
            type="button"
            @click="showModal = false"
            class="px-5 py-2 border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50"
        >
            Cancel
        </button>

        <button
            type="submit"
            class="px-6 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700"
        >
            Save
        </button>
    </div>
</form>

<form
    method="POST"
    action="{{ route('vendor.profile.update') }}"
    class="space-y-5"
>
    @csrf
    @method('PUT')

    {{-- IMPORTANT --}}
    <input type="hidden" name="section" value="address">

    {{-- Title --}}
    <h2 class="text-lg font-semibold text-gray-900">
        Edit Address
    </h2>

    {{-- Fields --}}
    <div class="space-y-4 text-sm">

        {{-- Street Address --}}
        <div>
            <label class="block text-gray-600 mb-1">Street Address</label>
            <input
                type="text"
                name="registered_address"
                value="{{ $vendor->registered_address }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500"
            >
        </div>

        {{-- Pincode --}}
        <div>
            <label class="block text-gray-600 mb-1">Pincode</label>
            <input
                type="text"
                name="pincode"
                value="{{ $vendor->pincode }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500"
            >
        </div>

        {{-- City --}}
        <div>
            <label class="block text-gray-600 mb-1">City</label>
            <select
                name="city"
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white focus:ring-green-500"
            >
                <option selected>{{ $vendor->city }}</option>
                <option>Lucknow</option>
                <option>Delhi</option>
                <option>Mumbai</option>
                <option>Bangalore</option>
            </select>
        </div>

        {{-- State --}}
        <div>
            <label class="block text-gray-600 mb-1">State</label>
            <select
                name="state"
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white focus:ring-green-500"
            >
                <option selected>{{ $vendor->state }}</option>
                <option>Uttar Pradesh</option>
                <option>Delhi</option>
                <option>Maharashtra</option>
                <option>Karnataka</option>
            </select>
        </div>

        {{-- Country --}}
        <div>
            <label class="block text-gray-600 mb-1">Country</label>
            <select
                name="country"
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white focus:ring-green-500"
            >
                <option selected>{{ $vendor->country }}</option>
                <option>India</option>
                <option>USA</option>
                <option>UK</option>
            </select>
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

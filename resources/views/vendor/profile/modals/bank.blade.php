<form
    method="POST"
    action="{{ route('vendor.profile.update') }}"
    class="space-y-5"
>
    @csrf
    @method('PUT')

    {{-- IMPORTANT --}}
    <input type="hidden" name="section" value="bank">

    {{-- Title --}}
    <h2 class="text-lg font-semibold text-gray-900">
        Edit Bank Details
    </h2>

    {{-- Fields --}}
    <div class="space-y-4 text-sm">

        {{-- Bank Name --}}
        <div>
            <label class="block text-gray-600 mb-1">Bank Name</label>
            <select
                name="bank_name"
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white focus:ring-green-500"
            >
                <option value="HDFC BANK" {{ $vendor->bank_name === 'HDFC BANK' ? 'selected' : '' }}>HDFC BANK</option>
                <option value="ICICI BANK" {{ $vendor->bank_name === 'ICICI BANK' ? 'selected' : '' }}>ICICI BANK</option>
                <option value="SBI" {{ $vendor->bank_name === 'SBI' ? 'selected' : '' }}>SBI</option>
                <option value="AXIS BANK" {{ $vendor->bank_name === 'AXIS BANK' ? 'selected' : '' }}>AXIS BANK</option>
                <option value="PNB" {{ $vendor->bank_name === 'PNB' ? 'selected' : '' }}>PNB</option>
            </select>
        </div>

        {{-- Account Number --}}
        <div>
            <label class="block text-gray-600 mb-1">Account Number</label>
            <input
                type="text"
                name="account_number"
                value="{{ $vendor->account_number }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500"
            >
        </div>

        {{-- Account Holder Name --}}
        <div>
            <label class="block text-gray-600 mb-1">Account Holder Name</label>
            <input
                type="text"
                name="account_holder_name"
                value="{{ $vendor->account_holder_name }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500"
            >
        </div>

        {{-- IFSC Code --}}
        <div>
            <label class="block text-gray-600 mb-1">IFSC Code</label>
            <div class="flex items-center gap-2">
                <input
                    type="text"
                    name="ifsc_code"
                    value="{{ $vendor->ifsc_code }}"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500"
                >
                <button
                    type="button"
                    class="text-blue-600 text-xs hover:underline"
                >
                    Find IFSC code
                </button>
            </div>
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
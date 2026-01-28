<form
    method="POST"
    action="{{ route('vendor.profile.update') }}"
    enctype="multipart/form-data"
    class="space-y-5"
>
    @csrf
    @method('PUT')

    {{-- IMPORTANT --}}
    <input type="hidden" name="section" value="business">

    <h2 class="text-lg font-semibold text-gray-900">
        Edit Business Details
    </h2>

    <div class="space-y-4 text-sm">

        {{-- GSTIN --}}
        <div>
            <label class="block text-gray-600 mb-1">GSTIN Number<span class="text-red-500">*</span></label>
            <input
                type="text"
                name="gstin"
                value="{{ $vendor->gstin }}"
                class="w-full px-3 py-2 border border-gray-200 rounded-md"
            >
        </div>

        {{-- Business Name --}}
        <div>
            <label class="block text-gray-600 mb-1">Business Name<span class="text-red-500">*</span></label>
            <input
                type="text"
                name="company_name"
                value="{{ $vendor->company_name }}"
                class="w-full px-3 py-2 border border-gray-200 rounded-md"
            >
        </div>

        {{-- Business Type --}}
        <div>
            <label class="block text-gray-600 mb-1">Business Type<span class="text-red-500">*</span></label>
            <select
                name="company_type"
                class="w-full px-3 py-2 border border-gray-200 rounded-md bg-white"
            >
                @foreach($businessTypes as $value => $label)
                    <option value="{{ $value }}"
                        {{ $vendor->company_type === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- PAN NUMBER --}}
        <div>
            <label class="block text-gray-600 mb-1">PAN Number<span class="text-red-500">*</span></label>
            <input
                type="text"
                name="pan"
                value="{{ $user->pan }}"
                class="w-full px-3 py-2 border border-gray-200 rounded-md"
            >
        </div>

        {{-- PAN FILE UPLOAD --}}
        <div>
            <label class="block text-gray-600 mb-1">
                Upload PAN Card <span class="text-red-500">*</span>
            </label>

            <input
                type="file"
                name="pan_file"
                accept=".jpg,.jpeg,.png,.pdf"
                class="w-full px-3 py-2 border border-gray-200 rounded-md"
            >

            @if($vendor->pan_card_document)
                <p class="text-xs text-green-600 mt-1">
                    ✔ PAN already uploaded
                </p>
            @endif
        </div>

    </div>

    <div class="flex justify-end gap-3 pt-4">
        <button
            type="button"
            @click="showModal = false"
            class="px-5 py-2 border rounded-md text-sm"
        >
            Cancel
        </button>

        <button
            type="submit"
            class="px-6 py-2 bg-green-600 text-white rounded-md text-sm"
        >
            Save
        </button>
    </div>
</form>
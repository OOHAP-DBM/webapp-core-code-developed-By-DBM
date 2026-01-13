<div class="space-y-5">

    {{-- Title --}}
    <h2 class="text-lg font-semibold text-gray-900">
        Edit Personal Info
    </h2>
    <input type="hidden" name="section" value="personal">

    {{-- Form Fields --}}
    <div class="space-y-4 text-sm">

        {{-- Name --}}
        <div>
            <label class="block text-gray-600 mb-1">
                Your Name
            </label>
            <input
                type="text"
                value="{{ auth()->user()->name }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-green-500"
            >
        </div>

        {{-- Email (Disabled) --}}
        <div>
            <label class="block text-gray-600 mb-1">
                Your Email Address
            </label>
            <input
                type="email"
                value="{{ auth()->user()->email }}"
                disabled
                class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-100 text-gray-500 cursor-not-allowed"
            >
        </div>

        {{-- Mobile --}}
        <div>
            <label class="block text-gray-600 mb-1">
                Your Mobile Number
            </label>
            <input
                type="text"
                value="{{ auth()->user()->phone }}"
                disabled
                class="w-full px-3 py-2 border border-gray-200 bg-gray-100 rounded-md focus:outline-none focus:ring-1 focus:ring-green-500 cursor-not-allowed"
            >
        </div>

    </div>

    {{-- Footer Buttons --}}
    <div class="flex justify-end gap-3 pt-4">

        <button
            type="button"
            @click="showModal = false"
            class="px-5 py-2 border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50"
        >
            Cancel
        </button>

        <button
            type="button"
            class="px-6 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700"
        >
            Save
        </button>

    </div>

</div>
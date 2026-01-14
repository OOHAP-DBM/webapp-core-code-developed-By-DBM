<form
    method="POST"
    action="{{ route('vendor.profile.update') }}"
    enctype="multipart/form-data"
    class="space-y-5"
>
    @csrf
    @method('PUT')

    {{-- IMPORTANT --}}
    <input type="hidden" name="section" value="personal">

    {{-- Title --}}
    <h2 class="text-lg font-semibold text-gray-900">
        Edit Personal Info
    </h2>

    {{-- Form Fields --}}
    <div class="space-y-4 text-sm">

        {{-- Name --}}
        <div>
            <label class="block text-gray-600 mb-1">
                Your Name
            </label>
            <input
                type="text"
                name="name"
                value="{{ auth()->user()->name }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-green-500"
            >
        </div>

        {{-- Email (Read-only) --}}
        <div>
            <label class="block text-gray-600 mb-1 flex items-center gap-2">
                Your Email Address
                @if(auth()->user()->email)
                    <span class="text-green-600 font-bold text-lg">✓</span>
                @endif
            </label>
            <input
                type="email"
                name="email"
                value="{{ auth()->user()->email }}"
                readonly
                class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-100 text-gray-500 cursor-not-allowed"
            >
        </div>

        {{-- Mobile --}}
        <div>
            <label class="block text-gray-600 mb-1 flex items-center gap-2">
                Your Mobile Number
                @if(auth()->user()->phone)
                    <span class="text-green-600 font-bold text-lg">✓</span>
                @endif
            </label>
            <input
                type="text"
                name="phone"
                value="{{ auth()->user()->phone }}"
                readonly
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
            type="submit"
            class="px-6 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700"
        >
            Save
        </button>

    </div>

</form>
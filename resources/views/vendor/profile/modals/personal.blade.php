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

        {{-- Avatar Upload --}}
        <div x-data="{ fileName: '' }">
            <label class="block text-gray-600 mb-2 font-medium">Profile Picture</label>
            <div class="flex items-center gap-4">
                <!-- Avatar Preview -->
                <div class="relative">
                    @if(auth()->user()->avatar)
                        <img
                            id="avatarPreview"
                            src="{{ route('vendor.view-avatar', auth()->user()->id) }}?t={{ time() }}"
                            alt="Avatar"
                            class="w-16 h-16 rounded-full object-cover border-2 border-gray-300"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                        <div
                            class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center border-2 border-gray-300 hidden"
                        >
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor" class="text-gray-400"/>
                            </svg>
                        </div>
                    @else
                        <div
                            id="avatarPreview"
                            class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center border-2 border-gray-300"
                        >
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor" class="text-gray-400"/>
                            </svg>
                        </div>
                    @endif
                </div>

                 <!-- Upload Input -->
                <div class="flex-1">
                    <input
                        type="file"
                        name="avatar"
                        accept="image/jpeg,image/jpg,image/png,image/gif"
                        @change="fileName = $event.target.files[0]?.name || ''"
                        id="avatarInput"
                        class="hidden"
                    >
                    <label
                        for="avatarInput"
                        class="inline-block px-4 py-2 bg-blue-500 text-white rounded-md text-xs font-medium cursor-pointer hover:bg-blue-600 transition"
                    >
                        Choose Photo
                    </label>
                    <p class="text-gray-500 text-xs mt-1" x-text="fileName ? `Selected: ${fileName}` : 'JPG, PNG or GIF (Max 2MB)'"></p>
                </div>
            </div>
        </div>

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
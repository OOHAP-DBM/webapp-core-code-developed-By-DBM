<div id="personalModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40 modal-overlay" onclick="closeModal('personalModal')"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-semibold text-gray-900">Personal Info</h3>
            <button type="button" onclick="closeModal('personalModal')" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <form method="POST" action="{{route('admin.profile.personal.update')}}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ $user->name }}"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ $user->email }}"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeModal('personalModal')"
                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded hover:bg-gray-50 cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-500 rounded hover:bg-green-600 cursor-pointer">
                    Save
                </button>
            </div>
        </form>

    </div>
</div>
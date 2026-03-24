<div id="changePasswordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40 modal-overlay" onclick="closeModal('changePasswordModal')"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-semibold text-gray-900">Change Password</h3>
            <button type="button" onclick="closeModal('changePasswordModal')" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <form method="POST" action="{{route('admin.profile.password.update')}}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Enter old password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="old_password" id="oldPassword"
                            class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm pr-9 focus:outline-none focus:ring-1 focus:ring-green-500">
                        <button type="button" onclick="togglePwd('oldPassword', this)" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587a2 2 0 002.829 2.829M9.88 9.88A3 3 0 0114.12 14.12M6.228 6.228A9.97 9.97 0 002.458 12C3.732 16.057 7.523 19 12 19c1.61 0 3.13-.38 4.477-1.053M9.88 4.122A9.953 9.953 0 0112 4c4.477 0 8.268 2.943 9.542 7a9.969 9.969 0 01-1.189 2.497M14.121 14.121L17.5 17.5" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Enter new password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="new_password" id="newPassword"
                            class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm pr-9 focus:outline-none focus:ring-1 focus:ring-green-500">
                        <button type="button" onclick="togglePwd('newPassword', this)" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587a2 2 0 002.829 2.829M9.88 9.88A3 3 0 0114.12 14.12M6.228 6.228A9.97 9.97 0 002.458 12C3.732 16.057 7.523 19 12 19c1.61 0 3.13-.38 4.477-1.053M9.88 4.122A9.953 9.953 0 0112 4c4.477 0 8.268 2.943 9.542 7a9.969 9.969 0 01-1.189 2.497M14.121 14.121L17.5 17.5" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Confirm new password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="new_password_confirmation" id="confirmPassword"
                            class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm pr-9 focus:outline-none focus:ring-1 focus:ring-green-500">
                        <button type="button" onclick="togglePwd('confirmPassword', this)" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587a2 2 0 002.829 2.829M9.88 9.88A3 3 0 0114.12 14.12M6.228 6.228A9.97 9.97 0 002.458 12C3.732 16.057 7.523 19 12 19c1.61 0 3.13-.38 4.477-1.053M9.88 4.122A9.953 9.953 0 0112 4c4.477 0 8.268 2.943 9.542 7a9.969 9.969 0 01-1.189 2.497M14.121 14.121L17.5 17.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeModal('changePasswordModal')"
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

<script>
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    // eye / eye-off icon swap
    btn.innerHTML = isText
    ? `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587a2 2 0 002.829 2.829M9.88 9.88A3 3 0 0114.12 14.12M6.228 6.228A9.97 9.97 0 002.458 12C3.732 16.057 7.523 19 12 19c1.61 0 3.13-.38 4.477-1.053M9.88 4.122A9.953 9.953 0 0112 4c4.477 0 8.268 2.943 9.542 7a9.969 9.969 0 01-1.189 2.497M14.121 14.121L17.5 17.5" /></svg>`
    : `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>`;
}
</script>
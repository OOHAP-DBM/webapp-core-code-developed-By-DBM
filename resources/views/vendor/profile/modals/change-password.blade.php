<div x-data="{ currentPasswordVisible: false, newPasswordVisible: false, confirmPasswordVisible: false }">
    <form
        method="POST"
        action="{{ route('vendor.profile.update') }}"
        class="space-y-5"
    >
        @csrf
        @method('PUT')

        {{-- IMPORTANT --}}
        <input type="hidden" name="section" value="password">

        {{-- Title --}}
        <h2 class="text-lg font-semibold text-gray-900">
            Change Password
        </h2>

        {{-- Form Fields --}}
        <div class="space-y-4 text-sm">

            {{-- Current Password --}}
            <div>
                <label class="block text-gray-900 mb-1">Enter old password<span class="text-red-500">*</span></label>
                <div class="relative flex items-center">
                    <input
                        :type="currentPasswordVisible ? 'text' : 'password'"
                        name="current_password"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-green-500"
                        required
                    >
                    <button
                        type="button"
                        @click.prevent="currentPasswordVisible = !currentPasswordVisible"
                        class="absolute right-3 text-gray-500 hover:text-gray-700 p-1"
                    >
                        <!-- Eye Open -->
                        <svg x-show="currentPasswordVisible" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer">
                            <path d="M12 5C7 5 2.73 8.11 1 12.46c1.73 4.35 6 7.54 11 7.54s9.27-3.19 11-7.54C21.27 8.11 17 5 12 5m0 12.5c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5m0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/>
                        </svg>
                        <!-- Eye Closed -->
                        <svg x-show="!currentPasswordVisible" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer">
                            <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46A11.804 11.804 0 001 11.5c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm6.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3-.05 0-.11 0-.17.02z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
                @error('current_password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- New Password --}}
            <div>
                <label class="block text-gray-900 mb-1">Enter new password<span class="text-red-500">*</span></label>
                <div class="relative flex items-center">
                    <input
                        :type="newPasswordVisible ? 'text' : 'password'"
                        name="password"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-green-500"
                        required
                    >
                    <button
                        type="button"
                        @click.prevent="newPasswordVisible = !newPasswordVisible"
                        class="absolute right-3 text-gray-500 hover:text-gray-700 p-1"
                    >
                        <!-- Eye Open -->
                        <svg x-show="newPasswordVisible" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer">
                            <path d="M12 5C7 5 2.73 8.11 1 12.46c1.73 4.35 6 7.54 11 7.54s9.27-3.19 11-7.54C21.27 8.11 17 5 12 5m0 12.5c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5m0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/>
                        </svg>
                        <!-- Eye Closed -->
                        <svg x-show="!newPasswordVisible" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer">
                            <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46A11.804 11.804 0 001 11.5c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm6.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3-.05 0-.11 0-.17.02z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Confirm Password --}}
            <div>
                <label class="block text-gray-900 mb-1">Confirm new password<span class="text-red-500">*</span></label>
                <div class="relative flex items-center">
                    <input
                        :type="confirmPasswordVisible ? 'text' : 'password'"
                        name="password_confirmation"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-green-500"
                        required
                    >
                    <button
                        type="button"
                        @click.prevent="confirmPasswordVisible = !confirmPasswordVisible"
                        class="absolute right-3 text-gray-500 hover:text-gray-700 p-1"
                    >
                        <!-- Eye Open -->
                        <svg x-show="confirmPasswordVisible" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer">
                            <path d="M12 5C7 5 2.73 8.11 1 12.46c1.73 4.35 6 7.54 11 7.54s9.27-3.19 11-7.54C21.27 8.11 17 5 12 5m0 12.5c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5m0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/>
                        </svg>
                        <!-- Eye Closed -->
                        <svg x-show="!confirmPasswordVisible" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer">
                            <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46A11.804 11.804 0 001 11.5c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm6.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3-.05 0-.11 0-.17.02z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Footer Buttons --}}
        <div class="flex justify-end gap-3 pt-4">

            <button
                type="button"
                @click="showModal = false"
                class="w-40 px-5 py-2 border border-gray-300 rounded-md text-sm text-gray-600 hover:bg-gray-50"
            >
                Cancel
            </button>

            <button
                type="submit"
                class="w-40 px-5 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700"
            >
                Update Password
            </button>


        </div>

    </form>
</div>

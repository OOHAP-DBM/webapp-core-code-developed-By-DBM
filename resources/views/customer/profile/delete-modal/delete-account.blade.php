<div
    id="deleteAccountModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 px-4"
>
    <div class="bg-white w-full max-w-md rounded-xl shadow-xl relative overflow-hidden">

        <!-- Close -->
        <button
            onclick="closeDeleteModal()"
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-xl"
        >
            ×
        </button>

        <!-- Header -->
        <div class="px-6 pt-6">
            <h2 class="text-base font-semibold text-center text-gray-900">
                Delete Account
            </h2>
        </div>

        <!-- Content -->
        <div class="px-6 pt-4 text-sm text-gray-700">

            <p class="mb-3 font-medium text-gray-700">
                Deleting your account will require
            </p>

            <ul class="space-y-2 mb-4">
                <li class="flex gap-2">
                    <span>+</span>
                    <span>Verification with business owner</span>
                </li>
                <li class="flex gap-2">
                    <span>+</span>
                    <span>
                        Meeting necessary terms and conditions as per
                        <a href="#" class="text-green-600 font-medium underline">
                            OOHAPP Privacy Policy
                        </a>
                    </span>
                </li>
            </ul>

            <hr class="my-4">

            <h3 class="font-semibold text-gray-900 mb-3">
                IMPORTANT
            </h3>

            <ul class="space-y-2 text-gray-600 text-xs leading-relaxed">
                <li class="flex gap-2">
                    <span>+</span>
                    <span>Upon confirmation, your pending orders will be cancelled.</span>
                </li>
                <li class="flex gap-2">
                    <span>+</span>
                    <span>
                        Your account will be permanently deleted after 90 days.
                        You will lose all benefits or rewards associated with your account.
                    </span>
                </li>
                <li class="flex gap-2">
                    <span>+</span>
                    <span>
                        Once your account is permanently deleted, you will not be able to
                        access or reactivate your account and cannot use the same business
                        documents to create a new account on OOHAPP.
                    </span>
                </li>
                <li class="flex gap-2">
                    <span>+</span>
                    <span>
                        OOHAPP may refuse or delay deletion in case you have any pending
                        grievances with us.
                    </span>
                </li>
            </ul>
        </div>

        <!-- Actions -->
        <div class="px-6 py-5 space-y-3">
            <button
                onclick="openNextDeleteStep()"
                class="w-full bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-md text-sm font-medium"
            >
                Continue Delete Account
            </button>

            <button
                onclick="closeDeleteModal()"
                class="w-full text-sm text-blue-600 hover:underline text-center"
            >
                Not Now
            </button>
        </div>
    </div>
</div>

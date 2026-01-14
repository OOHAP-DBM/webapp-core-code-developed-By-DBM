<div class="space-y-4 text-sm" x-data="{ showReasonModal: false, showConfirmModal: false, reason: '' }">

    {{-- Title --}}
    <h2 class="text-lg font-semibold text-gray-900">
        Delete Account
    </h2>

    {{-- Intro --}}
    <p class="text-gray-600">
        Deleting your account will require:
    </p>

    <ul class="list-disc ml-5 text-gray-600 space-y-1">
        <li>Verification with business owner</li>
        <li>Meeting necessary terms and conditions as per
            <span class="text-blue-600 cursor-pointer">OOHAPP Privacy Policy</span>
        </li>
    </ul>

    {{-- IMPORTANT --}}
    <div class="pt-3">
        <p class="font-semibold text-gray-900 mb-1">IMPORTANT</p>

        <ul class="list-disc ml-5 text-gray-600 space-y-1">
            <li>
                Upon confirmation, your listings and user will be deleted
                and pending orders will be cancelled.
            </li>
            <li>
                Your account will be permanently deleted. After 90 days,
                you will lose all benefits or rewards associated with your account.
            </li>
            <li>
                Once your account is permanently deleted, you will not be
                able to access or reactivate your account and cannot use the
                same business documents to create a new account on OOHAPP.
            </li>
            <li>
                OOHAPP may refuse or delay deletion in case you have any
                pending grievances with us.
            </li>
        </ul>
    </div>

    {{-- Footer Buttons --}}
    <div class="pt-4 space-y-2">

        <button
            type="button"
            @click="showReasonModal = true"
            class="w-full py-2 bg-red-500 text-white rounded-md text-sm font-medium hover:bg-red-600"
         >
            Continue Delete Account
        </button>


        <button
            type="button"
            @click="showModal = false"
            class="w-full py-2 text-blue-600 text-sm hover:underline"
        >
            Not Now
        </button>

    </div>

    {{-- REASON MODAL --}}
    <div 
        x-show="showReasonModal"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-3"
    >

        <!-- MODAL BOX -->
        <div
            @click.outside="showReasonModal = false"
            class="w-full max-w-md bg-white rounded-xl shadow-xl p-5 text-sm"
        >

            <!-- HEADER -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-semibold text-gray-900">
                    Delete Account
                </h3>

                <button @click="showReasonModal = false" class="text-gray-400 hover:text-gray-600">
                    ✕
                </button>
            </div>

            <!-- INFO -->
            <div class="space-y-3 text-gray-700">
                <p>
                    <span class="text-gray-500">Registered Email</span><br>
                    <span class="font-medium">{{ auth()->user()->email }}</span>
                </p>

                <p>
                    <span class="text-gray-500">Registered Mobile Number</span><br>
                    <span class="font-medium">{{ auth()->user()->phone }}</span>
                </p>
            </div>

            <!-- REASON -->
            <div class="mt-4">
                <p class="text-gray-600 mb-2">
                    Reason for Deletion
                </p>

                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" x-model="reason" value="Unable to make profits">
                        <span>Unable to make profits</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" x-model="reason" value="Found better platforms">
                        <span>Found better platforms</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" x-model="reason" value="Other">
                        <span>Some other reason</span>
                    </label>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="mt-5 space-y-2">
                <button
                    type="button"
                    @click="showReasonModal = false; showConfirmModal = true"
                    :disabled="!reason"
                    class="w-full py-2 rounded-md text-white text-sm font-medium
                           bg-red-500 hover:bg-red-600 disabled:bg-red-300 disabled:cursor-not-allowed"
                >
                    Continue Delete Account
                </button>

                <button
                    type="button"
                    @click="showReasonModal = false"
                    class="w-full py-2 text-blue-600 text-sm hover:underline"
                >
                    Not Now
                </button>
            </div>

        </div>
    </div>

    {{-- FINAL CONFIRMATION MODAL --}}
    <div 
        x-show="showConfirmModal"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-3"
    >
        <!-- MODAL BOX -->
        <div
            @click.outside="showConfirmModal = false"
            class="w-full max-w-md bg-white rounded-xl shadow-xl p-5 text-sm"
        >
            <!-- HEADER -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-semibold text-gray-900">
                    Are You Sure?
                </h3>

                <button @click="showConfirmModal = false" class="text-gray-400 hover:text-gray-600">
                    ✕
                </button>
            </div>

            <!-- MESSAGE -->
            <div class="space-y-3 text-gray-700 mb-5">
                <p class="text-red-600 font-medium">
                    ⚠️ This action cannot be undone!
                </p>
                <p>
                    Your account and all associated data will be permanently deleted.
                </p>
            </div>

            <!-- ACTIONS -->
            <div class="space-y-2">
                <form method="POST" action="{{ route('vendor.profile.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section" value="delete">
                    
                    <button
                        type="submit"
                        class="w-full py-2 rounded-md text-white text-sm font-medium
                               bg-red-600 hover:bg-red-700"
                    >
                        Yes, Delete My Account
                    </button>
                </form>

                <button
                    type="button"
                    @click="showConfirmModal = false; showReasonModal = true"
                    class="w-full py-2 text-blue-600 text-sm hover:underline"
                >
                    Cancel
                </button>
            </div>

        </div>
    </div>

</div>

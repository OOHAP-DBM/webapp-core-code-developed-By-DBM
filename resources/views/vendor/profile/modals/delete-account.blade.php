<div class="space-y-4 text-sm">

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

</div>

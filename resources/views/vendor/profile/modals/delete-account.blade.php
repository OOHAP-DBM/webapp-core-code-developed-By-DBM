 <div 
    class="space-y-4 text-sm"
    x-data="{
    showReasonModal: false,
    showOtpModal: false,
    showSuccessModal: false,


    reason: '',
    emailOtp: '',
    phoneOtp: '',

    emailTimer: 0,
    phoneTimer: 0,

    startTimer(type) {
        let seconds = 60;

        if (type === 'email') {
            this.emailTimer = seconds;
            const i = setInterval(() => {
                this.emailTimer--;
                if (this.emailTimer <= 0) clearInterval(i);
            }, 1000);
        }

        if (type === 'phone') {
            this.phoneTimer = seconds;
            const i = setInterval(() => {
                this.phoneTimer--;
                if (this.phoneTimer <= 0) clearInterval(i);
            }, 1000);
        }
    },

    sendOtp(type) {
        fetch('{{ route('vendor.profile.delete.sendOtp') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ type })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.startTimer(type);
            } else {
                Swal.fire('Error', data.message || 'Failed to send OTP', 'error');
            }
        });
    },

    submitDelete() {
        fetch('{{ route('vendor.profile.update') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                _method: 'PUT',
                section: 'delete',
                reason: this.reason,
                email_otp: this.emailOtp,
                phone_otp: this.phoneOtp
            })
        })
        .then(res => {
            const type = res.headers.get('content-type') || '';
            if (!type.includes('application/json')) {
                throw new Error('Server returned non-JSON response');
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                this.showOtpModal = false;
                this.showSuccessModal = true;

                setTimeout(() => {
                    window.location.href = '/';
                }, 2500);

            } else {
                Swal.fire('Oops!', data.message || 'Invalid OTP', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire(
                'Something went wrong',
                'Please refresh the page and try again.',
                'error'
            );
        });
    }


    }"


 >

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
            <a href="{{ route('privacy') }}" class="text-blue-600 underline" target="_blank">OOHAPP Privacy Policy</a>
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
                    @click="showReasonModal = false; showOtpModal = true"
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
    <!-- OTP MODAL -->
<div
    x-show="showOtpModal"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-3"
    >
    <div
        @click.outside="showOtpModal = false"
        class="w-full max-w-md bg-white rounded-xl shadow-xl p-6"
    >

        <!-- HEADER -->
        <div class="relative mb-4 text-center">
            <h3 class="text-lg font-semibold text-gray-900">
                Delete Account
            </h3>
            <button
                @click="showOtpModal = false"
                class="absolute right-0 top-0 text-gray-400 hover:text-gray-600"
            >
                ✕
            </button>
        </div>

        <!-- INFO -->
        <div class="space-y-3 text-sm text-gray-700 mb-4">
            <p>
                <span class="text-gray-500">Registered Email</span><br>
                <span class="font-medium">{{ auth()->user()->email }}</span>
            </p>

            <p>
                <span class="text-gray-500">Registered Mobile Number</span><br>
                <span class="font-medium">{{ auth()->user()->phone }}</span>
            </p>
        </div>

        <!-- EMAIL OTP -->
        <div class="mb-4">
            <label class="block text-gray-600 mb-1">
                Enter OTP sent to registered email
            </label>

            <input
                type="text"
                x-model="emailOtp"
                placeholder="Enter OTP"
                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring"
            />

            <button
                type="button"
                @click="sendOtp('email')"
                :disabled="emailTimer > 0"
                class="text-blue-600 text-xs mt-1 disabled:text-gray-400"
                   >
                <span x-show="emailTimer === 0">Send OTP</span>
                <span x-show="emailTimer > 0">
                    Resend in <span x-text="emailTimer"></span>s
                </span>
            </button>
        </div>

        <!-- MOBILE OTP -->
        <div class="mb-6">
            <label class="block text-gray-600 mb-1">
                Enter OTP sent to registered mobile number
            </label>

            <input
                type="text"
                x-model="phoneOtp"
                placeholder="Enter OTP"
                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring"
            />

            <button
                type="button"
                @click="sendOtp('phone')"
                :disabled="phoneTimer > 0"
                class="text-blue-600 text-xs mt-1 disabled:text-gray-400"
                 >
                <span x-show="phoneTimer === 0">Send OTP</span>
                <span x-show="phoneTimer > 0">
                    Resend in <span x-text="phoneTimer"></span>s
                </span>
            </button>
        </div>

        <!-- CONTINUE BUTTON -->
        <form method="POST" action="{{ route('vendor.profile.update') }}" @submit.prevent="submitDelete">
            @csrf
            @method('PUT')

            <input type="hidden" name="section" value="delete">
            <input type="hidden" name="reason" :value="reason">
            <input type="hidden" name="email_otp" :value="emailOtp">
            <input type="hidden" name="phone_otp" :value="phoneOtp">

            <button
                type="submit"
                :disabled="!emailOtp && !phoneOtp"
                class="w-full py-2 rounded-md text-white font-medium
                       bg-gray-300 disabled:cursor-not-allowed
                       enabled:bg-red-500 enabled:hover:bg-red-600"
            >
                Continue Delete Account
            </button>
        </form>

        <!-- FOOTER -->
        <button
            type="button"
            @click="showOtpModal = false"
            class="w-full mt-4 text-blue-600 text-sm hover:underline"
        >
            Not Now
        </button>

    </div>
</div>
<!-- SUCCESS MODAL -->
<div
    x-show="showSuccessModal"
    x-transition.opacity
    class="fixed inset-0 z-[999] flex items-center justify-center bg-black/60"
>
    <div
        class="bg-white rounded-2xl shadow-2xl w-[380px] text-center px-6 py-8"
    >
        <div class="flex justify-center">
            <svg width="119" height="119" viewBox="0 0 119 119" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <rect width="119" height="119" fill="url(#pattern0_9_22349)"/>
            <defs>
            <pattern id="pattern0_9_22349" patternContentUnits="objectBoundingBox" width="1" height="1">
            <use xlink:href="#image0_9_22349" transform="scale(0.00195312)"/>
            </pattern>
            <image id="image0_9_22349" width="512" height="512" preserveAspectRatio="none" xlink:href="data:image/png;base64,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"/>
            </defs>
            </svg>
        </div>

        <h2 class="text-lg font-semibold text-gray-900 mb-1">
            We will miss you!
        </h2>

       <p class="text-lg text-gray-500">
            Your account has been deleted successfully.
        </p>

    </div>
</div>
</div>


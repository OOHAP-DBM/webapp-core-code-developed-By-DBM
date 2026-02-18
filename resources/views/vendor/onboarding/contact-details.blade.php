@extends('layouts.app')

@section('title', 'Vendor Onboarding – Account Info')

@section('content')
<div class="min-h-screen bg-gray-50 pb-16 font-sans">

    <header class="flex items-center gap-2 px-8 py-6 bg-white border-b border-gray-100">
        <img src="{{ asset('assets/images/logo/logo_image.jpeg') }}" alt="OOHAPP" class="h-7">
        <span class="text-sm font-medium text-gray-500 uppercase tracking-wider">Vendor Portal</span>
    </header>

    <div class="max-w-4xl mx-auto mt-10 px-4">

        <div class="flex items-center mb-10">
            <div class="flex items-center text-gray-900 font-semibold">
                <span class="w-8 h-8 flex items-center justify-center rounded-full bg-green-500 text-white text-sm">1</span>
                <p class="ml-3 text-xs uppercase tracking-widest">Account Info</p>
            </div>
            <div class="flex-1 h-px bg-gray-200 mx-6"></div>
            <div class="flex items-center text-gray-400">
                <span class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-sm">2</span>
                <p class="ml-3 text-xs uppercase tracking-widest">Business Info</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <h2 class="text-2xl font-bold text-gray-800">Welcome to OOHAPP</h2>
            <p class="text-gray-500 text-sm mt-1 mb-8">Please verify your contact details to secure your account.</p>

            @php $user = auth()->user(); @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Mobile Number</label>
                    <div class="relative group">
                        <input type="tel" 
                               id="phoneInput"
                               class="w-full h-11 pl-4 pr-24 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all outline-none disabled:bg-gray-50 disabled:text-gray-500"
                               placeholder="e.g. 9876543210"
                               value="{{ $user->phone }}"
                               {{ $user->phone_verified_at ? 'disabled' : '' }}>

                        <div class="absolute right-3 top-1/2 -translate-y-1/2">
                            @if($user->phone_verified_at)
                                <span class="flex items-center gap-1 text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    VERIFIED
                                </span>
                            @else
                                <button type="button" 
                                        id="phoneBtn"
                                        class="text-xs font-bold text-green-600 hover:text-green-700 transition-colors uppercase tracking-tight cursor-pointer" 
                                        onclick="sendPhoneOtp()">
                                    Verify Now
                                </button>
                            @endif
                        </div>
                    </div>
                    <p id="phoneError" class="text-red-500 text-[11px] hidden"></p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Email Address</label>
                    <div class="relative group">
                        <input type="email" 
                               id="emailInput"
                               class="w-full h-11 pl-4 pr-24 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all outline-none disabled:bg-gray-50 disabled:text-gray-500"
                               placeholder="name@company.com"
                               value="{{ $user->email }}"
                               {{ $user->email_verified_at ? 'disabled' : '' }}>

                        <div class="absolute right-3 top-1/2 -translate-y-1/2">
                            @if($user->email_verified_at)
                                <span class="flex items-center gap-1 text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    VERIFIED
                                </span>
                            @else
                                <button type="button" 
                                        id="emailBtn"
                                        class="text-xs font-bold text-green-600 hover:text-green-700 transition-colors uppercase tracking-tight cursor-pointer" 
                                        onclick="sendEmailOtp()">
                                    Verify
                                </button>
                            @endif
                        </div>
                    </div>
                    <p id="emailError" class="text-red-500 text-[11px] hidden"></p>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-gray-50 flex flex-col items-center">
                <button class="cursor-pointer w-full md:w-64 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg shadow-lg shadow-green-200 transition-all active:scale-95" 
                        onclick="skipContactVerification()">
                    Skip & Continue
                </button>
                <p class="mt-4 text-xs text-gray-400">
                    You can finish verification later in your profile settings.
                </p>
            </div>
        </div>
    </div>
</div>

<div id="otpModal" class="hidden fixed inset-0 z-[1000] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeOtpModal()"></div>
    <div class="relative bg-white w-full max-w-sm rounded-2xl shadow-2xl p-8 animate-in fade-in zoom-in duration-200">
        <div class="text-center mb-6">
            <h4 class="text-xl font-bold text-gray-800">Verify Code</h4>
            <p class="text-sm text-gray-500 mt-1">Enter the 4-digit code sent to you.</p>
        </div>

        <input type="text" 
               id="otpInput" 
               maxlength="4"
               class="w-full h-14 text-center text-2xl font-bold tracking-[1rem] border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-0 transition-all outline-none"
               placeholder="0000">

        <button onclick="verifyOtp()" 
                id="verifySubmitBtn"
                class="w-full mt-6 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition-all disabled:opacity-50">
            Confirm & Verify
        </button>
        
        <button onclick="closeOtpModal()" class="w-full mt-3 text-xs text-gray-400 hover:text-gray-600 font-medium transition-colors">
            Cancel
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
let verifyType = null;
const csrf = document.querySelector('meta[name="csrf-token"]').content;

/* UI Helpers */
const toggleLoading = (btnId, isLoading) => {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    btn.disabled = isLoading;
    btn.innerText = isLoading ? 'Sending...' : (btnId === 'phoneBtn' ? 'Verify Now' : 'Verify');
};

const showError = (elementId, message) => {
    const el = document.getElementById(elementId);
    el.innerText = message;
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 5000);
};

function closeOtpModal() {
    document.getElementById('otpModal').classList.add('hidden');
}

/* ================= VALIDATION ================= */
const validateEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
const validatePhone = (phone) => /^\d{10,15}$/.test(phone);

/* ================= ACTIONS ================= */
function sendPhoneOtp() {
    const val = document.getElementById('phoneInput').value;
    if(!validatePhone(val)) return showError('phoneError', 'Please enter a valid phone number');
    
    verifyType = 'phone';
    sendOtp("{{ route('vendor.onboarding.send-phone') }}", { phone: val }, 'phoneBtn');
}

function sendEmailOtp() {
    const val = document.getElementById('emailInput').value;
    if(!validateEmail(val)) return showError('emailError', 'Please enter a valid email address');

    verifyType = 'email';
    sendOtp("{{ route('vendor.onboarding.send-email') }}", { email: val }, 'emailBtn');
}

function sendOtp(url, payload, btnId) {
    toggleLoading(btnId, true);
    
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(async res => {
        if (!res.ok) throw await res.json();
        document.getElementById('otpModal').classList.remove('hidden');
    })
    .catch(err => {
        alert(err.message || 'Verification service temporarily unavailable.');
    })
    .finally(() => toggleLoading(btnId, false));
}

function verifyOtp() {
    const otp = document.getElementById('otpInput').value;
    if(otp.length < 4) return alert('Enter 4-digit OTP');

    const submitBtn = document.getElementById('verifySubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerText = 'Verifying...';

    const url = verifyType === 'phone'
        ? "{{ route('vendor.onboarding.verify-phone') }}"
        : "{{ route('vendor.onboarding.verify-email') }}";

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
            otp,
            phone: document.getElementById('phoneInput').value,
            email: document.getElementById('emailInput').value
        })
    })
    .then(async res => {
        if (!res.ok) throw await res.json();
        window.location.reload();
    })
    .catch(err => {
        alert(err.message || 'Invalid OTP. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerText = 'Confirm & Verify';
    });
}

function skipContactVerification() {
    const skipBtn = event.currentTarget;
    const originalText = skipBtn.innerText;
    
    // UI Feedback: Disable button and show loading
    skipBtn.disabled = true;
    skipBtn.innerText = 'Processing...';

    fetch("{{ route('vendor.onboarding.skip-verification') }}", {
        method: 'POST',
        headers: { 
            'X-CSRF-TOKEN': csrf, 
            'Content-Type': 'application/json', 
            'Accept': 'application/json' 
        }
    })
    .then(async res => {
        const data = await res.json();
        if (res.ok && data.success) {
            // Redirect to the Business Info route
            window.location.href = "{{ route('vendor.onboarding.business-info') }}";
        } else {
            throw new Error(data.message || 'Something went wrong');
        }
    })
    .catch(err => {
        console.error('Skip Error:', err);
        alert(err.message || 'Failed to skip verification. Please try again.');
        
        // Reset button on error
        skipBtn.disabled = false;
        skipBtn.innerText = originalText;
    });
}
</script>
@endpush
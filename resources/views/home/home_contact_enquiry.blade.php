<!-- Direct Enquiry Modal - Revamped -->
<div id="directEnquiryModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gradient-to-br from-black/60 to-black/40 backdrop-blur-sm" onclick="closeDirectEnquiryModal()"></div>
    
    <!-- Modal Container -->
    <div class="relative h-full flex items-center justify-center p-4 sm:p-6">
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-2xl max-h-[95vh] overflow-hidden flex flex-col">
            
            <!-- Header - Sticky -->
            <div class="bg-gradient-to-r from-[#009A5C] to-[#00b36b] px-4 sm:px-6 py-4 sm:py-5 text-white flex justify-between items-start sticky top-0 z-10 shadow-lg">
                <div class="flex-1">
                    <h3 class="text-lg sm:text-xl font-bold mb-1">Get Your Hoarding Quote</h3>
                    <p class="text-xs sm:text-sm opacity-90">Fill in your details and we'll connect you with the best options</p>
                </div>
                <button onclick="closeDirectEnquiryModal()" 
                        class="ml-3 text-white/80 hover:text-white hover:rotate-90 transition-all duration-300 text-2xl leading-none mt-1">
                    &times;
                </button>
            </div>

            <!-- Form - Scrollable -->
            <div class="flex-1 overflow-y-auto">
                <form id="directEnquiryForm" action="{{ route('direct.enquiry.submit') }}" method="POST" class="p-4 sm:p-6 lg:p-8 space-y-5">
                    @csrf

                    <!-- Full Name -->
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Your Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="nameInput"
                               required 
                               minlength="3"
                               placeholder="e.g. Rajesh Kumar"
                               class="w-full px-4 py-2 border-2 border-gray-200 rounded-xl outline-none focus:border-[#009A5C] focus:ring-2 focus:ring-[#009A5C]/20 transition-all text-sm sm:text-base">
                    </div>

                    <!-- Phone with Auto-OTP -->
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Mobile Number <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium text-sm">
                                +91
                            </div>
                            <input type="tel" 
                                   id="phoneInput" 
                                   name="phone" 
                                   required 
                                   maxlength="10"
                                   pattern="[6-9][0-9]{9}"
                                   placeholder="10-digit mobile number" 
                                   class="w-full pl-14 pr-4 py-2 border-2 border-gray-200 rounded-xl outline-none focus:border-[#009A5C] focus:ring-2 focus:ring-[#009A5C]/20 transition-all text-sm sm:text-base">
                            
                            <!-- Verification Status Indicator -->
                            <div id="phoneStatusIndicator" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                                <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <input type="hidden" id="phoneVerified" name="phone_verified" value="0">
                        <p class="text-xs text-gray-500 mt-2">
                            <span class="inline-block w-2 h-2 bg-green-500 rounded-full animate-pulse mr-1"></span>
                            OTP will be sent automatically when you proceed
                        </p>
                    </div>

                    <!-- Email (No Verification Required) -->
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="emailInput" 
                               name="email" 
                               required 
                               placeholder="your.email@example.com" 
                               class="w-full px-4 py-2 border-2 border-gray-200 rounded-xl outline-none focus:border-[#009A5C] focus:ring-2 focus:ring-[#009A5C]/20 transition-all text-sm sm:text-base">
                    </div>

                    <!-- Hoarding Type -->
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
                            Hoarding Type <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="relative flex items-center px-4  border-2 border-gray-200 rounded-xl cursor-pointer hover:border-[#009A5C] transition-all group">
                                <input type="checkbox" name="hoarding_type[]" value="DOOH" class="peer sr-only">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-[#009A5C] peer-checked:border-[#009A5C] flex items-center justify-center transition-all">
                                    <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <span class="block font-semibold text-gray-800 text-sm">Digital (DOOH)</span>
                                    <span class="block text-xs text-gray-500">LED/LCD Displays</span>
                                </div>
                            </label>

                            <label class="relative flex items-center px-4 py-2 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-[#009A5C] transition-all group">
                                <input type="checkbox" name="hoarding_type[]" value="OOH" class="peer sr-only">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-[#009A5C] peer-checked:border-[#009A5C] flex items-center justify-center transition-all">
                                    <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <span class="block font-semibold text-gray-800 text-sm">Static (OOH)</span>
                                    <span class="block text-xs text-gray-500">Traditional Hoardings</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- City -->
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                          Campaign  City <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="location_city" 
                               id="cityInput"
                               required
                               placeholder="e.g. Lucknow, Mumbai, Delhi"
                               class="w-full px-4 py-2 border-2 border-gray-200 rounded-xl outline-none focus:border-[#009A5C] focus:ring-2 focus:ring-[#009A5C]/20 transition-all text-sm sm:text-base">
                        <!-- <p class="text-xs text-gray-500 mt-2">Don't worry about spelling - we'll find the right match!</p> -->
                    </div>

                    <!-- Preferred Locations -->
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Preferred Locations in City
                        </label>
                        <div id="locationWrapper" class="space-y-3">
                            <div class="location-item">
                                <input type="text" 
                                       name="preferred_locations[]" 
                                       placeholder="e.g. Hazratganj, Gomti Nagar"
                                       class="w-full px-4 py-2 border-2 border-gray-200 rounded-xl outline-none focus:border-[#009A5C] focus:ring-2 focus:ring-[#009A5C]/20 transition-all text-sm sm:text-base">
                            </div>
                        </div>
                        <button type="button" 
                                onclick="addAnotherLocation()" 
                                class="mt-3 text-[#009A5C] hover:text-[#007a4a] font-semibold text-sm flex items-center gap-2 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Add Another Location
                        </button>
                    </div>

                    <!-- Requirements/Remarks -->
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Your Requirements <span class="text-red-500">*</span>
                        </label>
                        <textarea name="remarks" 
                                  rows="3" 
                                  required
                                  minlength="5"
                                  placeholder="Tell us about your campaign: duration, budget, target audience, creative preferences, etc."
                                  class="w-full px-4 py-2 border-2 border-gray-200 rounded-xl outline-none focus:border-[#009A5C] focus:ring-2 focus:ring-[#009A5C]/20 transition-all resize-none text-sm sm:text-base"></textarea>
                    </div>

                    <!-- Preferred Communication -->
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
                            How Should We Reach You?
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-[#009A5C] transition-all">
                                <input type="checkbox" name="preferred_modes[]" value="Call" class="peer sr-only">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-[#009A5C] peer-checked:border-[#009A5C] flex items-center justify-center transition-all">
                                    <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="ml-2 text-sm font-medium text-gray-700">Phone Call</span>
                            </label>

                            <label class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-[#009A5C] transition-all">
                                <input type="checkbox" name="preferred_modes[]" value="WhatsApp" class="peer sr-only">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-[#009A5C] peer-checked:border-[#009A5C] flex items-center justify-center transition-all">
                                    <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="ml-2 text-sm font-medium text-gray-700">WhatsApp</span>
                            </label>

                            <label class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-[#009A5C] transition-all">
                                <input type="checkbox" name="preferred_modes[]" value="Email" class="peer sr-only">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-[#009A5C] peer-checked:border-[#009A5C] flex items-center justify-center transition-all">
                                    <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="ml-2 text-sm font-medium text-gray-700">Email</span>
                            </label>
                        </div>
                    </div>

                    <!-- Captcha -->
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Security Check <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <div class="flex-1 bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-2 flex items-center justify-between">
                                <span id="captchaText" class="font-bold text-lg text-gray-700"></span>
                                <button type="button" 
                                        onclick="regenerateCaptcha()" 
                                        class="text-[#009A5C] hover:text-[#007a4a] transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </div>
                            <input type="number" 
                                   name="captcha" 
                                   required 
                                   placeholder="Answer"
                                   class="w-24 sm:w-32 px-4 py-2 border-2 border-gray-200 rounded-xl outline-none focus:border-[#009A5C] focus:ring-2 focus:ring-[#009A5C]/20 transition-all text-center text-sm sm:text-base">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            id="submitBtn" 
                            disabled
                            class="w-full bg-gradient-to-r from-[#009A5C] to-[#00b36b] text-white font-bold py-4 rounded-xl shadow-lg disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 text-sm sm:text-base">
                        <span id="submitBtnText">Submit Enquiry</span>
                    </button>

                    <p class="text-xs text-center text-gray-500 mt-4">
                        By submitting, you agree to receive quotes from verified vendors
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- OTP Modal -->
<div id="otpModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeOtpModal()"></div>
    <div class="relative h-full flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 sm:p-8 text-center">
            <!-- Icon -->
            <div class="w-16 h-16 bg-[#009A5C]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-[#009A5C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>

            <h3 class="text-xl font-bold text-gray-800 mb-2">Verify Your Number</h3>
            <p class="text-sm text-gray-600 mb-6" id="otpForText">
                Enter the 4-digit code sent to <strong id="otpPhoneDisplay"></strong>
            </p>

            <!-- OTP Input -->
            <input type="text" 
                   id="otpInput" 
                   inputmode="numeric"
                   autocomplete="one-time-code"
                   maxlength="4" 
                   placeholder="• • • •"
                   class="w-full px-4 py-4 text-center text-2xl font-bold tracking-[1em] border-2 border-gray-300 rounded-xl outline-none focus:border-[#009A5C] focus:ring-2 focus:ring-[#009A5C]/20 transition-all mb-4">

            <!-- Resend OTP -->
            <div class="mb-6">
                <p class="text-sm text-gray-600">
                    Didn't receive code? 
                    <button type="button" 
                            id="resendOtpBtn" 
                            onclick="resendOTP()" 
                            class="text-[#009A5C] font-semibold hover:underline disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                        Resend <span id="resendTimer"></span>
                    </button>
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button type="button" 
                        onclick="closeOtpModal()" 
                        class="flex-1 bg-gray-200 text-gray-700 font-semibold py-2 rounded-xl hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button type="button" 
                        id="verifyOtpBtn" 
                        class="flex-1 bg-gradient-to-r from-[#009A5C] to-[#00b36b] text-white font-semibold py-2 rounded-xl hover:shadow-lg transition-all">
                    Verify
                </button>
            </div>

            <p id="otpMessage" class="text-sm font-medium mt-4"></p>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 z-[70] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
    <div class="relative h-full flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 text-center">
            <!-- Success Icon -->
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>

            <h3 class="text-2xl font-bold text-gray-800 mb-2">Enquiry Submitted!</h3>
            <p class="text-gray-600 mb-6">
                Thank you for your interest. We've notified relevant vendors in your area. 
                You'll receive quotes within 24-48 hours.
            </p>

            <button onclick="closeSuccessModal()" 
                    class="w-full bg-gradient-to-r from-[#009A5C] to-[#00b36b] text-white font-bold py-2 rounded-xl hover:shadow-lg transition-all">
                Got It!
            </button>
        </div>
    </div>
</div>
<script>
    /**
 * Direct Enquiry Form - Enhanced JavaScript
 * Features: Auto-OTP, Web OTP API, Fuzzy matching, Smart validation
 */

// =====================================================
// GLOBAL STATE & ELEMENTS
// =====================================================
const phoneInput = document.getElementById('phoneInput');
const emailInput = document.getElementById('emailInput');
const phoneVerifiedInput = document.getElementById('phoneVerified');
const submitBtn = document.getElementById('submitBtn');
const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

let otpCooldown = false;
let resendCooldown = 0;
let resendInterval = null;
let phoneVerified = false;
let otpSentAutomatically = false;

// =====================================================
// MODAL MANAGEMENT
// =====================================================
function openDirectEnquiryModal() {
    document.getElementById('directEnquiryModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    regenerateCaptcha();
}

function closeDirectEnquiryModal() {
    document.getElementById('directEnquiryModal').classList.add('hidden');
    document.body.style.overflow = '';
    localStorage.setItem('direct_enquiry_closed_at', Date.now());
}

function closeOtpModal() {
    document.getElementById('otpModal').classList.add('hidden');
    document.getElementById('otpInput').value = '';
    document.getElementById('otpMessage').textContent = '';
    otpSentAutomatically = false; // Allow OTP to be sent again
    if (resendInterval) {
        clearInterval(resendInterval);
    }
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    closeDirectEnquiryModal();
}

// =====================================================
// AUTO-OTP TRIGGER
// =====================================================
phoneInput.addEventListener('blur', function() {
    const phone = this.value.trim();
    // Validate phone number format
    if (phone.length === 10 && /^[6-9][0-9]{9}$/.test(phone)) {
        // Check if already verified
        if (phoneVerified) {
            return;
        }
        // Check if OTP already sent for this number
        if (!otpSentAutomatically) {
            sendOTPAutomatically(phone);
        }
    }
});
// Reset otpSentAutomatically if phone input changes
phoneInput.addEventListener('input', function() {
    otpSentAutomatically = false;
});

// Reset otpSentAutomatically if phone input changes
phoneInput.addEventListener('input', function() {
    otpSentAutomatically = false;
});

async function sendOTPAutomatically(phone) {
    if (otpCooldown) {
        showToast('Please wait before requesting OTP again', 'warning');
        return;
    }

    try {
        otpCooldown = true;
        otpSentAutomatically = true;
        
        showLoadingState(phoneInput, true);

        const response = await fetch('{{ route("direct.enquiry.otp.send") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ identifier: phone })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to send OTP');
        }

        showLoadingState(phoneInput, false);
        showOtpModal(phone);
        
        // Start 60 second cooldown
        setTimeout(() => {
            otpCooldown = false;
        }, 5000);

    } catch (error) {
        console.error('OTP Send Error:', error);
        showLoadingState(phoneInput, false);
        showToast(error.message || 'Failed to send OTP. Please try again.', 'error');
        otpCooldown = false;
        otpSentAutomatically = false;
    }
}

// =====================================================
// OTP MODAL & VERIFICATION
// =====================================================
function showOtpModal(phone) {
    document.getElementById('otpPhoneDisplay').textContent = `+91 ${phone}`;
    document.getElementById('otpModal').classList.remove('hidden');
    document.getElementById('otpInput').focus();
    
    // Start resend timer
    startResendTimer();
    
    // Enable Web OTP API (auto-fill OTP from SMS)
    if ('OTPCredential' in window) {
        navigator.credentials.get({
            otp: { transport: ['sms'] }
        }).then(otp => {
            document.getElementById('otpInput').value = otp.code;
            verifyOTP();
        }).catch(err => {
            console.log('Web OTP API Error:', err);
        });
    }
}

function startResendTimer() {
    resendCooldown = 60;
    const resendBtn = document.getElementById('resendOtpBtn');
    const resendTimer = document.getElementById('resendTimer');
    
    resendBtn.disabled = true;
    
    resendInterval = setInterval(() => {
        resendCooldown--;
        resendTimer.textContent = `(${resendCooldown}s)`;
        
        if (resendCooldown <= 0) {
            clearInterval(resendInterval);
            resendBtn.disabled = false;
            resendTimer.textContent = '';
        }
    }, 1000);
}

async function resendOTP() {
    const phone = phoneInput.value.trim();
    closeOtpModal();
    await sendOTPAutomatically(phone);
}

// OTP Input Auto-Submit on 4 digits
document.getElementById('otpInput').addEventListener('input', function() {
    const otp = this.value;
    if (otp.length === 4 && /^\d{4}$/.test(otp)) {
        verifyOTP();
    }
});

// Verify button click
document.getElementById('verifyOtpBtn').addEventListener('click', verifyOTP);

async function verifyOTP() {
    const otp = document.getElementById('otpInput').value.trim();
    const phone = phoneInput.value.trim();
    const otpMessage = document.getElementById('otpMessage');
    const verifyBtn = document.getElementById('verifyOtpBtn');

    if (!otp || otp.length !== 4) {
        otpMessage.textContent = 'Please enter a valid 4-digit OTP';
        otpMessage.className = 'text-sm font-medium mt-4 text-red-600';
        return;
    }

    try {
        verifyBtn.disabled = true;
        verifyBtn.textContent = 'Verifying...';

        const response = await fetch('{{ route("direct.enquiry.otp.verify") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                identifier: phone, 
                otp: otp 
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Invalid OTP');
        }

        // Success - mark as verified
        phoneVerified = true;
        phoneVerifiedInput.value = '1';
        phoneInput.readOnly = true;
        phoneInput.classList.add('bg-gray-50', 'cursor-not-allowed');
        
        // Show success indicator
        document.getElementById('phoneStatusIndicator').classList.remove('hidden');
        
        // Update submit button state
        updateSubmitButtonState();
        
        // Close OTP modal with success message
        showToast('Phone number verified successfully!', 'success');
        closeOtpModal();

    } catch (error) {
        console.error('OTP Verification Error:', error);
        otpMessage.textContent = error.message || 'Verification failed. Please try again.';
        otpMessage.className = 'text-sm font-medium mt-4 text-red-600';
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify';
    }
}

// =====================================================
// CAPTCHA
// =====================================================
async function regenerateCaptcha() {
    try {
        const response = await fetch('{{ route("direct.enquiry.captcha") }}');
        const data = await response.json();
        document.getElementById('captchaText').textContent = `${data.num1} + ${data.num2} = ?`;
        document.querySelector('input[name="captcha"]').value = '';
    } catch (error) {
        console.error('Captcha Error:', error);
    }
}

// =====================================================
// DYNAMIC LOCATION FIELDS
// =====================================================
function addAnotherLocation() {
    const wrapper = document.getElementById('locationWrapper');
    const newInput = document.createElement('div');
    newInput.className = 'location-item flex items-center gap-2';
    newInput.innerHTML = `
        <input type="text" 
               name="preferred_locations[]" 
               placeholder="e.g. Aminabad, Alambagh"
               class="flex-1 px-4 py-2 border-2 border-gray-200 rounded-xl outline-none focus:border-[#009A5C] focus:ring-2 focus:ring-[#009A5C]/20 transition-all text-sm sm:text-base">
        <button type="button" 
                onclick="removeLocation(this)" 
                class="text-red-500 hover:text-red-700 p-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    wrapper.appendChild(newInput);
}

function removeLocation(button) {
    button.closest('.location-item').remove();
}

// =====================================================
// FORM SUBMISSION
// =====================================================
document.getElementById('directEnquiryForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (submitBtn.disabled) {
        return;
    }

    // Validate phone verification
    if (!phoneVerified) {
        showToast('Please verify your phone number first', 'warning');
        phoneInput.focus();
        return;
    }

    // Validate hoarding type
    const hoardingTypes = document.querySelectorAll('input[name="hoarding_type[]"]:checked');
    if (hoardingTypes.length === 0) {
        showToast('Please select at least one hoarding type', 'warning');
        return;
    }

    try {
        submitBtn.disabled = true;
        document.getElementById('submitBtnText').textContent = 'Submitting...';

        const formData = new FormData(this);

        const response = await fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (!response.ok) {
            const errorMsg = data?.errors 
                ? Object.values(data.errors).flat().join(', ')
                : data.message || 'Submission failed';
            throw new Error(errorMsg);
        }

        // Success - reset form and show success modal
        this.reset();
        phoneVerified = false;
        otpSentAutomatically = false;
        phoneVerifiedInput.value = '0';
        phoneInput.readOnly = false;
        phoneInput.classList.remove('bg-gray-50', 'cursor-not-allowed');
        document.getElementById('phoneStatusIndicator').classList.add('hidden');
        
        regenerateCaptcha();
        updateSubmitButtonState();
        
        document.getElementById('successModal').classList.remove('hidden');

    } catch (error) {
        console.error('Form Submission Error:', error);
        showToast(error.message || 'Failed to submit enquiry. Please try again.', 'error');
        regenerateCaptcha();
    } finally {
        submitBtn.disabled = false;
        document.getElementById('submitBtnText').textContent = 'Submit Enquiry';
    }
});

// =====================================================
// SUBMIT BUTTON STATE MANAGEMENT
// =====================================================
function updateSubmitButtonState() {
    const allRequiredFieldsFilled = 
        document.getElementById('nameInput').value.trim() &&
        phoneInput.value.trim().length === 10 &&
        phoneVerified &&
        emailInput.value.trim() &&
        document.querySelector('input[name="hoarding_type[]"]:checked') &&
        document.getElementById('cityInput').value.trim() &&
        document.querySelector('textarea[name="remarks"]').value.trim().length >= 10 &&
        document.querySelector('input[name="captcha"]').value;

    submitBtn.disabled = !allRequiredFieldsFilled;
}

// Listen to form inputs for validation
document.querySelectorAll('#directEnquiryForm input, #directEnquiryForm textarea, #directEnquiryForm select').forEach(element => {
    element.addEventListener('input', updateSubmitButtonState);
    element.addEventListener('change', updateSubmitButtonState);
});

// =====================================================
// UTILITY FUNCTIONS
// =====================================================
function showLoadingState(element, loading) {
    if (loading) {
        element.classList.add('animate-pulse', 'bg-gray-50');
    } else {
        element.classList.remove('animate-pulse', 'bg-gray-50');
    }
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    const bgColor = {
        'success': 'bg-green-500',
        'error': 'bg-red-500',
        'warning': 'bg-yellow-500',
        'info': 'bg-blue-500'
    }[type] || 'bg-gray-500';

    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-2 rounded-lg shadow-lg z-[100] animate-slide-in-right`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('animate-fade-out');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// =====================================================
// INITIALIZATION
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    regenerateCaptcha();
    updateSubmitButtonState();
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slide-in-right {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes fade-out {
        to {
            opacity: 0;
            transform: translateY(-10px);
        }
    }
    
    .animate-slide-in-right {
        animation: slide-in-right 0.3s ease-out;
    }
    
    .animate-fade-out {
        animation: fade-out 0.3s ease-out forwards;
    }
    
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
`;
document.head.appendChild(style);
</script>
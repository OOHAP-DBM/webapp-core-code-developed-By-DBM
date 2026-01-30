<!-- Direct Enquiry Modal -->
<div id="directEnquiryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[90vh] overflow-y-auto scrollbar-hide">
        
        <!-- Header -->
        <div class="bg-[#009A5C] p-6 text-white flex justify-between items-center sticky top-0 z-10">
            <div>
                <h3 class="text-xl font-bold">Direct Enquiry</h3>
                <p class="text-xs opacity-80">Please fill the details to get a quote.</p>
            </div>
            <button onclick="toggleDirectEnquiryModal()" class="text-2xl font-light hover:rotate-90 transition-transform">&times;</button>
        </div>

        <form id="directEnquiryForm" action="{{ route('direct.enquiry.submit') }}" method="POST" class="p-8 grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf

            <!-- Full Name -->
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required minlength="3" placeholder="Enter your name"
                    class="w-full border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C] transition-colors">
            </div>

            <!-- Phone -->
            <div class="md:col-span-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Phone Number <span class="text-red-500">*</span></label>
                <div class="flex gap-2 items-center">
                    <input type="tel" id="phoneInput" name="phone" required maxlength="10" placeholder="10 digit mobile number" 
                        class="flex-1 border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C]">
                    <button type="button" id="sendPhoneOtpBtn" onclick="sendOTP('phone')" class="text-xs px-3 py-1 bg-[#000000] text-white rounded">Send OTP</button>
                    <span id="phoneVerifiedText" class="hidden text-green-600 text-xs font-semibold ml-2">Verified</span>
                </div>
                <input type="hidden" id="phone_verified" value="0">
            </div>

            <!-- Email -->
            <div class="md:col-span-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Email Address <span class="text-red-500">*</span></label>
                <div class="flex gap-2 items-center">
                    <input type="email" id="emailInput" name="email" required placeholder="example@domain.com" 
                        class="flex-1 border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C]">
                    <button type="button" id="sendEmailOtpBtn" onclick="sendOTP('email')" class="text-xs px-3 py-1 bg-[#000000] text-white rounded">Send OTP</button>
                    <span id="emailVerifiedText" class="hidden text-green-600 text-xs font-semibold ml-2">Verified</span>
                </div>
                <input type="hidden" id="email_verified" value="0">
            </div>

            <!-- Hoarding Type -->
            <div class="md:col-span-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Hoarding Type <span class="text-red-500">*</span></label>
                <div class="flex gap-4 mt-2">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="hoarding_type[]" value="DOOH" class="accent-[#009A5C]"> DOOH (Digital)
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="hoarding_type[]" value="OOH" class="accent-[#009A5C]"> OOH (Static Hoarding)
                    </label>
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-gray-500 uppercase">Hoarding City </label>
                <input type="text" name="location_city"  placeholder="e.g. Lucknow"
                    class="w-full border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C] transition-colors">
            </div>
            <!-- Preferred Locations -->
           <div class="md:col-span-2">
            <label class="text-xs font-bold text-gray-500 uppercase">
                Preferred Hoarding Locations
            </label>

            <div id="locationWrapper" class="flex flex-col gap-3 mt-2">
                <div class="flex items-center gap-2 location-item">
                    <input type="text" name="preferred_locations[]" placeholder="Enter location e.g. Hazratganj"
                        class="flex-1 border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C]" />
                </div>
            </div>

            <button type="button" onclick="addAnotherLocation()"
                class="mt-2 text-[#009A5C] font-semibold hover:underline">
                + Add Another Location
            </button>
        </div>


          


            <!-- Remarks -->
            <div class="md:col-span-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Your Requirements / Remarks <span class="text-red-500">*</span></label>
                <textarea name="remarks" rows="2" placeholder="Write your requirements in detail, including what you want, when you want it, your budget, etc." required
                    class="w-full border-2 border-gray-50 rounded-xl p-3 mt-1 text-sm outline-none focus:border-[#009A5C] transition-colors"></textarea>
            </div>

            <div class="md:col-span-2">
                <label class="text-xs font-bold text-gray-500 uppercase">
                    Preferred Communication Mode
                </label>
                <div class="flex gap-4 mt-2">
                    <label><input type="checkbox" name="preferred_modes[]" value="Call"> Call</label>
                    <label><input type="checkbox" name="preferred_modes[]" value="WhatsApp"> WhatsApp</label>
                    <label><input type="checkbox" name="preferred_modes[]" value="Email"> Email</label>
                </div>
            </div>

            <!-- Captcha -->
            <div class="md:col-span-2 flex items-center gap-4">
                <span id="captchaText" class="font-bold text-lg"></span>
                <input type="number" name="captcha" required placeholder="Enter captcha"
                    class="border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C] flex-1">
                <button type="button" onclick="regenerateCaptcha()" class="text-sm text-[#009A5C] hover:underline">Refresh</button>
            </div>
          

            <!-- Submit -->
            <button type="submit" id="submitBtn" disabled
                class="md:col-span-2 bg-[#009A5C] text-white font-bold py-4 rounded-xl mt-4 shadow-lg disabled:opacity-50 hover:bg-[#007a4a] transition-all">
                Submit Enquiry
            </button>
        </form>
    </div>
</div>

<!-- OTP Modal -->
<div id="otpModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/40 hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden text-center p-6">
        <h3 class="text-lg font-bold mb-2">Enter OTP</h3>
        <p class="text-sm text-gray-500 mb-4" id="otpForText"></p>
        <input type="text" id="otpInput" maxlength="4" placeholder="Enter OTP"
            class="w-full border px-3 py-2 rounded-xl mb-4 text-center outline-none focus:border-[#009A5C]">
        <div class="flex gap-2 justify-center mb-4">
            <button id="verifyOtpBtn" class="bg-[#009A5C] text-white font-bold py-2 px-6 rounded-xl hover:bg-[#007a4a]">Verify</button>
            <button onclick="closeOtpModal()" class="bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-xl hover:bg-gray-400">Cancel</button>
        </div>
        <span id="otpMessage" class="text-sm font-medium"></span>
    </div>
</div>

<!-- Status Modal -->
<div id="statusModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40 hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden text-center p-8">
        <div id="successIcon" class="flex justify-center mb-4 text-green-600" style="font-size:2.5rem;display:none;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-12 h-12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        </div>
        <div id="errorIcon" class="flex justify-center mb-4 text-red-600" style="font-size:2.5rem;display:none;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-12 h-12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </div>
        <h3 id="statusTitle" class="text-lg font-bold mb-2"></h3>
        <p id="statusMessage" class="text-sm text-gray-500 mb-4"></p>
        <button onclick="closeStatusModal()" class="bg-[#009A5C] text-white font-bold py-2 px-6 rounded-xl hover:bg-[#007a4a]">OK</button>
    </div>
</div>


<script>
/* =====================================================
   GLOBAL ELEMENTS & STATE
const phoneInput = document.getElementById('phoneInput');
const emailInput = document.getElementById('emailInput');
const phoneVerifiedInput = document.getElementById('phone_verified');
const emailVerifiedInput = document.getElementById('email_verified');
const submitBtn = document.getElementById('submitBtn');
const csrf = '{{ csrf_token() }}';

let otpCooldown = false;
let currentOtpType = null;
let currentIdentifier = null;

/* =====================================================
   MODAL LOGIC
    document.getElementById('directEnquiryModal').classList.add('hidden');
    localStorage.setItem('direct_enquiry_closed_at', Date.now());
}

function toggleDirectEnquiryModal() {
    closeDirectEnquiryModal();
}

/* =====================================================
   CAPTCHA
===================================================== */
function regenerateCaptcha() {
    fetch('{{ route("direct.enquiry.captcha") }}')
        .then(res => res.json())
        .then(data => {
            document.getElementById('captchaText').textContent =
                `${data.num1} + ${data.num2} = ?`;
            document.querySelector('input[name="captcha"]').value = '';
        })
        .catch(() => console.error('Captcha error'));
}

/* =====================================================
   LOCATIONS
}

/* =====================================================
   LOCATIONS
===================================================== */
function addAnotherLocation() {
    const wrapper = document.getElementById('locationWrapper');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'preferred_locations[]';
    input.placeholder = 'Enter location e.g. Aminabad';
    input.className = 'w-full border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C]';
    wrapper.appendChild(input);
}

function sendOTP(type) {
    const identifier = type === 'phone' ? phoneInput.value : emailInput.value;
    if (!identifier) return alert(`Enter ${type}`);
    if (type === 'phone' && identifier.length !== 10) {
        return alert('Phone number must be exactly 10 digits.');
    }
    if (type === 'email' && !/^\S+@\S+\.\S+$/.test(identifier)) {
        return alert('Enter a valid email address.');
    }
    if (otpCooldown) return Swal.fire({
        icon: 'warning',
        title: 'Hold on!',
        text: 'Please wait before resending OTP',
        timer: 3000,
        showConfirmButton: false
        });

    otpCooldown = true;
    setTimeout(() => otpCooldown = false, 6000); // 60 sec cooldown

    fetch('{{ route("direct.enquiry.otp.send") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ identifier })
    })
    .then(res => res.json())
    .then(res => {
        if (!res.success) return alert(res.message || 'OTP error');
        showOtpModal(type, identifier);
    })
    .catch(() => alert('OTP send failed'));
}

document.getElementById('verifyOtpBtn').addEventListener('click', () => {
    const otp = document.getElementById('otpInput').value;
    if (!otp) return alert('Enter OTP');

    fetch('{{ route("direct.enquiry.otp.verify") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ identifier: currentIdentifier, otp })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            document.getElementById('otpMessage').textContent =
                data.message || 'Invalid OTP';
            document.getElementById('otpMessage').classList.add('text-red-600');
            return;
        }

        if (currentOtpType === 'phone') {
            phoneVerifiedInput.value = '1';
            phoneInput.readOnly = true;
        } else {
            emailVerifiedInput.value = '1';
            emailInput.readOnly = true;
        }

        updateSubmit();
        closeOtpModal();
        // alert('OTP Verified Successfully');
         Swal.fire({
                icon: 'success',
                title: 'OTP Verified Successfully',
                text: 'OTP Verified Successfully'
            });

    })
    .catch(() => alert('OTP verification failed'));
});

/* =====================================================
   FORM SUBMIT
===================================================== */
document.getElementById('directEnquiryForm').addEventListener('submit', function (e) {
    e.preventDefault();
    if (submitBtn.disabled) return;

    submitBtn.disabled = true;
    submitBtn.innerText = 'Processing...';
    const formData = new FormData(this);

   fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async res => {
        const data = await res.json();
        submitBtn.disabled = false;

        submitBtn.innerText = 'Submit Enquiry';

        if (!res.ok) {
            const msg = data?.errors
                ? Object.values(data.errors)[0][0]
                : data.message || 'Something went wrong';
            alert(msg);
            regenerateCaptcha();
            return;
        }

        this.reset();
        phoneVerifiedInput.value = '0';
        emailVerifiedInput.value = '0';
        updateSubmit();
        regenerateCaptcha();
        closeDirectEnquiryModal();
        // Show custom success modal
         Swal.fire({
                icon: 'success',
                title: 'Enquiry Submitted!',
                text: 'Thank you! Our team will contact you shortly.'
            });

    })
    .catch(() => {
        submitBtn.innerText = 'Submit Enquiry';
        submitBtn.disabled = false;
        alert('Server error');
    });
});

/* =====================================================
   INPUT HELPERS

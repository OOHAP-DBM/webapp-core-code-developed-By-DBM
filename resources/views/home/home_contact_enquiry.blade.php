
<!-- Direct Enquiry Modal -->
<div id="directEnquiryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[90vh] overflow-y-auto">
        
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
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase">Phone Number <span class="text-red-500">*</span></label>
                <div class="flex gap-2 items-center">
                    <input type="tel" id="phoneInput" name="phone" required maxlength="10" placeholder="10 digit mobile number" 
                        class="flex-1 border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C]">
                    <button type="button" id="sendPhoneOtpBtn" onclick="sendOTP('phone')" class="text-xs px-3 py-1 bg-[#009A5C] text-white rounded">Send OTP</button>
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
                    <button type="button" id="sendEmailOtpBtn" onclick="sendOTP('email')" class="text-xs px-3 py-1 bg-[#009A5C] text-white rounded">Send OTP</button>
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
                        <input type="checkbox" name="hoarding_type[]" value="Static" class="accent-[#009A5C]"> OOH (Static Hoarding)
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
                <label class="text-xs font-bold text-gray-500 uppercase">Preferred Hoarding Locations</label>
                <div id="locationWrapper" class="flex flex-col gap-2">
                    <input type="text" name="preferred_locations[]" placeholder="Enter location e.g. Hazratganj"
                        class="w-full border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C]" />
                </div>
                <button type="button" onclick="addAnotherLocation()" class="mt-2 text-[#009A5C] font-semibold hover:underline">+ Add Another Location</button>
            </div>

            <!-- Remarks -->
            <div class="md:col-span-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Your Requirements / Remarks <span class="text-red-500">*</span></label>
                <textarea name="remarks" rows="2" placeholder="Write your requirements in detail..." required
                    class="w-full border-2 border-gray-50 rounded-xl p-3 mt-1 text-sm outline-none focus:border-[#009A5C] transition-colors"></textarea>
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

<script>
const phoneInput = document.getElementById('phoneInput');
const emailInput = document.getElementById('emailInput');
const phoneVerifiedInput = document.getElementById('phone_verified');
const emailVerifiedInput = document.getElementById('email_verified');
const submitBtn = document.getElementById('submitBtn');
const csrf = '{{ csrf_token() }}';
let otpCooldown = false;
let currentOtpType = null;
let currentIdentifier = null;

// Enable submit only if both verified
function updateSubmit() {
    submitBtn.disabled = !(phoneVerifiedInput.value === '1' && emailVerifiedInput.value === '1');
    // Phone
    if (phoneVerifiedInput.value === '1') {
        document.getElementById('sendPhoneOtpBtn').style.display = 'none';
        document.getElementById('phoneVerifiedText').classList.remove('hidden');
    } else {
        document.getElementById('sendPhoneOtpBtn').style.display = '';
        document.getElementById('phoneVerifiedText').classList.add('hidden');
    }
    // Email
    if (emailVerifiedInput.value === '1') {
        document.getElementById('sendEmailOtpBtn').style.display = 'none';
        document.getElementById('emailVerifiedText').classList.remove('hidden');
    } else {
        document.getElementById('sendEmailOtpBtn').style.display = '';
        document.getElementById('emailVerifiedText').classList.add('hidden');
    }
}

// Add another location
function addAnotherLocation() {
    const wrapper = document.getElementById('locationWrapper');
    const input = document.createElement('input');
    input.type='text';
    input.name='preferred_locations[]';
    input.placeholder='Enter location e.g. Aminabad';
    input.className='w-full border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C]';
    wrapper.appendChild(input);
}

// Toggle modal
function toggleDirectEnquiryModal() {
    document.getElementById('directEnquiryModal').classList.toggle('hidden');
}

// Captcha
function regenerateCaptcha() {
    fetch('{{ route("direct.enquiry.captcha") }}')
        .then(res => res.json())
        .then(data => {
            document.getElementById('captchaText').textContent = `${data.num1} + ${data.num2} = ?`;
        });
}

// ---------------------------
// OTP FUNCTIONS
// ---------------------------
function showOtpModal(type, identifier) {
    currentOtpType = type;
    currentIdentifier = identifier;
    document.getElementById('otpModal').classList.remove('hidden');
    document.getElementById('otpInput').value = '';
    document.getElementById('otpMessage').textContent = '';
    document.getElementById('otpForText').textContent = 'OTP sent to your ' + (type==='phone' ? 'Phone' : 'Email') + ': ' + identifier;
}

function closeOtpModal() {
    document.getElementById('otpModal').classList.add('hidden');
    currentOtpType = null;
    currentIdentifier = null;
}

// Send OTP
function sendOTP(type) {
    let identifier = type==='phone' ? phoneInput.value : emailInput.value;
    if (!identifier) return alert('Enter ' + type);
    if (otpCooldown) return alert('Please wait before resending OTP');

    otpCooldown = true;
    // setTimeout(()=> otpCooldown=false, 60000);
    setTimeout(()=> otpCooldown=false, 500);


    fetch('{{ route("direct.enquiry.otp.send") }}', {
        method:'POST',
        headers:{'X-CSRF-TOKEN': csrf,'Content-Type':'application/json'},
        body: JSON.stringify({identifier})
    })
    .then(res=>res.json())
    .then(res=>{
        if(res.success){
            alert('OTP sent!');
            showOtpModal(type, identifier);
        } else alert(res.message || 'Error sending OTP');
    })
    .catch(()=>alert('Error sending OTP'));
}

// Verify OTP
document.getElementById('verifyOtpBtn').addEventListener('click', ()=>{
    const otp = document.getElementById('otpInput').value;
    if(!otp) return alert('Enter OTP');

    fetch('{{ route("direct.enquiry.otp.verify") }}',{
        method:'POST',
        headers:{'X-CSRF-TOKEN':csrf,'Content-Type':'application/json'},
        body: JSON.stringify({identifier: currentIdentifier, otp})
    })
    .then(res=>res.json())
    .then(data=>{
        if(!data.success){
            document.getElementById('otpMessage').textContent = data.message || 'Invalid OTP';
            document.getElementById('otpMessage').classList.add('text-red-600');
            return;
        }

        if(currentOtpType==='phone') phoneVerifiedInput.value='1';
        else emailVerifiedInput.value='1';
        updateSubmit();
        closeOtpModal();
        alert('OTP Verified Successfully!');
    })
    .catch(()=>alert('Error verifying OTP'));
});

// Initial captcha
regenerateCaptcha();
</script>



<script>
// Logic to show/hide modals
function showDirectEnquiryModal() {
    var modal = document.getElementById('directEnquiryModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}
function toggleDirectEnquiryModal() {
    document.getElementById('directEnquiryModal').classList.add('hidden');
    localStorage.setItem('direct_enquiry_closed_at', Date.now());
}

function showStatusModal(isSuccess, title, message) {
    const modal = document.getElementById('statusModal');
    const sIcon = document.getElementById('successIcon');
    const eIcon = document.getElementById('errorIcon');
    // Set content
    document.getElementById('statusTitle').innerText = title;
    document.getElementById('statusMessage').innerText = message;
    // Show correct icon
    sIcon.style.display = isSuccess ? 'flex' : 'none';
    eIcon.style.display = isSuccess ? 'none' : 'flex';
    modal.classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    // If it was a success, reload the page to refresh captcha
    if (document.getElementById('successIcon').style.display === 'flex') {
       directEnquiryForm.reset(); 
    }
}

// Phone Number numeric filter
document.getElementById('phoneInput')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Auto-show timers
window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => { autoShowModal(); }, 600); // 1 min
    setInterval(() => { autoShowModal(); }, 120000); // 2 mins
    updateSubmit(); // Ensure correct state on load
});

function autoShowModal() {
    const modal = document.getElementById('directEnquiryModal');
    const lastClosed = localStorage.getItem('direct_enquiry_closed_at');
    const cooldown = 10 * 60 * 1000;
    if (modal.classList.contains('hidden')) {
        if (!lastClosed || (Date.now() - lastClosed) > cooldown) {
            modal.classList.remove('hidden');
        }
    }
}

// Form Submission
// document.getElementById('directEnquiryForm').addEventListener('submit', function(e) {
//     e.preventDefault();
//     const btn = document.getElementById('submitBtn');
//     const originalText = btn.innerText;
//     btn.disabled = true;
//     btn.innerText = 'Processing...';
//     fetch("{{ route('direct.enquiry.submit') }}", {
//         method: 'POST',
//         headers: {
//             'X-CSRF-TOKEN': '{{ csrf_token() }}',
//             'Accept': 'application/json',
//             'Content-Type': 'application/json'
//         },
//         body: JSON.stringify(Object.fromEntries(new FormData(this)))
//     })
//     .then(async res => {
//         const data = await res.json();

//         // VALIDATION ERROR
//         if (res.status === 422) {
//             btn.disabled = false;
//             btn.innerText = originalText;

//             const firstError =
//                 Object.values(data.errors)[0]?.[0] || 'Please check your input details.';

//             showStatusModal(false, 'Validation Error', firstError);
//             return;
//         }

//         // SERVER ERROR (500, 503, etc.)
//         if (!res.ok) {
//             btn.disabled = false;
//             btn.innerText = originalText;

//             showStatusModal(false, 'System Error', data.message || 'Something went wrong.');
//             return;
//         }

//         // ✅ SUCCESS (200–299 only)
//         document.getElementById('directEnquiryModal').classList.add('hidden');
       
//         showStatusModal(
//             true,
//             'Inquiry Sent!',
//             'Thank you! Our team will contact you shortly regarding your requirements.'
//         );

//         // ✅ RESET FORM + BUTTON STATE
//         const form = document.getElementById('directEnquiryForm');
//         const btn  = document.getElementById('submitBtn');

//         form.reset();
//         btn.disabled = false;
//         btn.innerText = 'Submit My Requirement';

//         localStorage.setItem('direct_enquiry_closed_at', Date.now() + 86400000);
//     })

//     .catch(err => {
//         btn.disabled = false;
//         btn.innerText = originalText;
//         showStatusModal(false, 'System Error', 'Something went wrong. Please try again later.');
//     });
// });

document.getElementById('directEnquiryForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const btn = document.getElementById('submitBtn');
    const originalText = btn.innerText;
    if (btn.disabled) return;
    btn.disabled = true;
    btn.innerText = 'Processing...';

    fetch("{{ route('direct.enquiry.submit') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(new FormData(this)))
    })
    .then(async res => {
        const data = await res.json();

        // ❌ Validation error (422)
        if (res.status === 422) {
            btn.disabled = false;
            btn.innerText = originalText;
            const msg = Object.values(data.errors)[0]?.[0] || 'Invalid input.';
            showStatusModal(false, 'Validation Error', msg);
            // Refresh captcha on validation error as well
            refreshDirectEnquiryCaptcha();
            return;
        }

        // ❌ Server error
        if (!res.ok) {
            btn.disabled = false;
            btn.innerText = originalText;
            showStatusModal(false, 'System Error', data.message || 'Something went wrong.');
            return;
        }

        // ✅ SUCCESS
        document.getElementById('directEnquiryModal').classList.add('hidden');
        showStatusModal(
            true,
            'Inquiry Sent!',
            'Thank you! Our team will contact you shortly.'
        );
        this.reset();
        btn.disabled = false;
        btn.innerText = originalText;
        // Refresh captcha after success
        refreshDirectEnquiryCaptcha();
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerText = originalText;
        showStatusModal(false, 'System Error', 'Please try again later.');
    });
});

function refreshDirectEnquiryCaptcha() {
    fetch("{{ route('direct.enquiry.captcha') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.num1 !== undefined && data.num2 !== undefined) {
            document.getElementById('captchaText').textContent = Security Check: ${data.num1} + ${data.num2} =;
            document.querySelector('input[name="captcha"]').value = '';
        }
    });
}

</script>
<div id="enquiryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden  p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[90vh] overflow-y-auto">
        
        <div class="bg-[#009A5C] p-6 text-white flex justify-between items-center sticky top-0 z-10">
            <div>
                <h3 class="text-xl font-bold">Direct Enquiry</h3>
                <p class="text-xs opacity-80">Please fill the details to get a quote.</p>
            </div>
            <button onclick="toggleEnquiryModal()" class="text-2xl font-light hover:rotate-90 transition-transform">&times;</button>
        </div>

        <form id="enquiryForm" action="{{ route('direct.enquiry.submit') }}" method="POST" class="p-8 grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required minlength="3" placeholder="Enter your name"
                    class="w-full border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C] transition-colors">
            </div>

           <div>
                <label class="text-xs font-bold text-gray-500 uppercase">Phone Number <span class="text-red-500">*</span></label>
                <input type="tel" 
                       name="phone" 
                       id="phoneInput"
                       required 
                       inputmode="numeric"
                       pattern="[0-9]{10}" 
                       maxlength="10"
                       placeholder="10 digit mobile number"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                       class="w-full border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C]">
            </div>

            <div class="md:col-span-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" required placeholder="example@domain.com"
                    class="w-full border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C] transition-colors">
            </div>

            <div>
                <label class="text-xs font-bold text-gray-500 uppercase">City <span class="text-red-500">*</span></label>
                <input type="text" name="location_city" required placeholder="e.g. Lucknow"
                    class="w-full border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C] transition-colors">
            </div>

            <div>
                <label class="text-xs font-bold text-gray-500 uppercase">Hoarding Type</label>
                <select name="hoarding_type" class="w-full border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C] bg-transparent">
                    <option value="DOOH">DOOH (Digital)</option>
                    <option value="Static">OOH (Static Hoarding)</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Preferred Location <span class="text-red-500">*</span></label>
                <input type="text" name="hoarding_location" required placeholder="Area or Landmark"
                    class="w-full border-b-2 border-gray-100 py-2 outline-none focus:border-[#009A5C] transition-colors">
            </div>

            <div class="md:col-span-2">
                <label class="text-xs font-bold text-gray-500 uppercase">Your Requirements / Remarks</label>
                <textarea name="remarks" rows="2" placeholder="Tell us more about your campaign needs..."
                    class="w-full border-2 border-gray-50 rounded-xl p-3 mt-1 text-sm outline-none focus:border-[#009A5C] transition-colors"></textarea>
            </div>

            <div class="md:col-span-2 bg-gray-50 p-4 rounded-xl flex items-center justify-between mt-2 border border-gray-100">
                <span class="text-sm font-bold text-gray-600 italic">Security Check: {{ $num1 }} + {{ $num2 }} =</span>
                <input type="number" name="captcha" required class="w-20 p-2 rounded-lg border-2 border-white text-center font-bold focus:border-[#009A5C] outline-none shadow-sm">
            </div>

            <button type="submit" id="submitBtn" class="md:col-span-2 bg-[#009A5C] text-white font-bold py-4 rounded-xl mt-4 shadow-lg active:scale-95 hover:bg-[#007a4a] transition-all">
                Submit My Requirement
            </button>
        </form>
    </div>
</div>


<div id="statusModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden text-center p-8 transform transition-all">
        <div id="successIcon" class="hidden mx-auto mb-4 items-center justify-center w-16 h-16 bg-green-100 text-green-500 rounded-full text-3xl">
            ✓
        </div>
        <div id="errorIcon" class="hidden mx-auto mb-4 items-center justify-center w-16 h-16 bg-red-100 text-red-500 rounded-full text-3xl">
            ✕
        </div>
        
        <h3 id="statusTitle" class="text-xl font-bold text-gray-800 mb-2"></h3>
        <p id="statusMessage" class="text-sm text-gray-500 mb-6"></p>
        
        <button onclick="closeStatusModal()" class="w-full bg-[#009A5C] text-white font-bold py-3 rounded-xl shadow-lg hover:bg-[#007a4a] transition-all">
            Continue
        </button>
    </div>
</div>

<script>
// Logic to show/hide modals
function toggleEnquiryModal() {
    document.getElementById('enquiryModal').classList.add('hidden');
    localStorage.setItem('enquiry_closed_at', Date.now());
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
       enquiryForm.reset(); 
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
});

function autoShowModal() {
    const modal = document.getElementById('enquiryModal');
    const lastClosed = localStorage.getItem('enquiry_closed_at');
    const cooldown = 10 * 60 * 1000;

    if (modal.classList.contains('hidden')) {
        if (!lastClosed || (Date.now() - lastClosed) > cooldown) {
            modal.classList.remove('hidden');
        }
    }
}

// Form Submission
document.getElementById('enquiryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('submitBtn');
    const originalText = btn.innerText;
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
        if (res.status === 422) {
            btn.disabled = false;
            btn.innerText = originalText;
            showStatusModal(false, 'Validation Error', data.errors.captcha ? data.errors.captcha[0] : 'Please check your input details.');
        } else {
            document.getElementById('enquiryModal').classList.add('hidden');
            showStatusModal(true, 'Inquiry Sent!', 'Thank you! Our team will contact you shortly regarding your requirements.');
            localStorage.setItem('enquiry_closed_at', Date.now() + 86400000); 
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = originalText;
        showStatusModal(false, 'System Error', 'Something went wrong. Please try again later.');
    });
});
</script>
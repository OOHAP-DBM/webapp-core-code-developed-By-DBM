<style>
    #enquiryModal {
        background: rgba(0,0,0,0.35);
    }

    /* ================= CARD ================= */
    #enquiryModal .modal-card {
        background:#F6FAF8;
    }

    /* ================= INPUTS ================= */
    .enquiry-input{
        width:100%;
        margin-top:6px;
        border:1px solid #E5E7EB;
        border-radius:8px;
        padding: 14px 14px;
        font-size:14px;
        background:#fff;
        color:#111827;
    }

    .enquiry-input::placeholder{
        color:#9CA3AF;
    }

    .enquiry-input:focus{
        border-color:#2F5D46;
        box-shadow:0 0 0 1px #2F5D46;
        outline:none;
    }

    .enquiry-input[readonly],
    .enquiry-input:disabled{
        background:#F3F4F6;
        color:#6B7280;
        cursor: not-allowed;
        opacity: 0.7;
    }

    /* ===== Mobile full screen modal ===== */
    @media (max-width: 640px) {

        #enquiryModal .modal-card {
            width: 100%;
            height: 100%;
            max-height: 100vh;
            border-radius: 0;
        }

        #enquiryModal .modal-card form {
            height: 100%;
        }
    }
    /* ================= MOBILE ONLY STABILITY FIX ================= */
    @media (max-width: 640px) {

        /* modal container fixed & stable */
        #enquiryModal {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100dvh; /* 🔥 IMPORTANT */
            overflow: hidden;
        }

        /* modal card full screen, no flex jump */
        #enquiryModal .modal-card {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100dvh;
            max-height: 100dvh;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
            border-radius: 0;
        }

        /* stop horizontal movement completely */
        html, body {
            overflow-x: hidden;
        }
    }
    #enquirySubmitBtn {
        position: relative;
        min-height: 48px;
        font-size: 1rem;
        line-height: 1.5;
    }
    #enquiryLoader {
        position: absolute;
        inset: 0;
        align-items: center;
        justify-content: center;
        background: rgba(47, 93, 70, 0.96);
        z-index: 2;
    }
    #enquiryLoaderText {
        display: flex;
        align-items: center;
        font-weight: 500;
        font-size: 1rem;
        letter-spacing: 0.02em;
    }
    .dot-one, .dot-two, .dot-three {
        opacity: 0.2;
        animation: blink 1.4s infinite both;
    }
    .dot-two { animation-delay: 0.2s; }
    .dot-three { animation-delay: 0.4s; }
    @keyframes blink {
        0%, 80%, 100% { opacity: 0.2; }
        40% { opacity: 1; }
    }
    .enquiry-input.readonly-clean {
        background: #ffffff !important;
        color: #111827 !important;
        opacity: 1 !important;
        cursor: default !important;
    }
    .enquiry-input.readonly-clean:focus{
        border-color:#E5E7EB !important;
        box-shadow:none !important;
    }
</style>



@php
    $modalHoarding = null;
    $packages = collect();
@endphp

{{-- ================= ENQUIRY MODAL ================= --}}
<div id="enquiryModal"
     class="fixed inset-0 z-50 hidden flex items-center justify-center">

    <div class="modal-card relative w-full h-full sm:h-auto sm:max-h-[90vh]
                sm:max-w-3xl flex flex-col overflow-hidden">

        {{-- HEADER --}}
        <div class="flex items-start justify-between px-4 py-4 sm:px-6 sm:py-5 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    Raise an Enquiry
                </h3>
                <p class="text-sm text-gray-500">
                    Please fill your general details
                </p>
            </div>
            <button type="button" onclick="closeEnquiryModal()"
                    class="text-gray-500 hover:text-black text-xl cursor-pointer">
                ✕
            </button>
        </div>

        {{-- FORM --}}
        <form id="enquiryForm"
              action="{{ route('enquiries.store') }}"
              method="POST"
              class="flex flex-col overflow-hidden">
                        @csrf
                      

            {{-- BODY --}}
            <div class="flex-1 overflow-y-auto px-6 py-6 space-y-5 text-sm">

                {{-- HIDDEN FIELDS (UNCHANGED) --}}
                <input type="hidden" id="enquiryHoardingId" name="hoarding_id">
                <input type="hidden" id="enquiryPackageId" name="package_id">
                <input type="hidden" id="enquiryPackageLabel" name="package_label">
                <input type="hidden" id="enquiryAmount" name="amount">
                <input type="hidden" id="enquiryDurationType" name="duration_type" value="months">
                <input type="hidden" id="enquiryEndDate" name="preferred_end_date">
                <input type="hidden" id="enquiryMonths" name="months[]" value="1">
                <input type="hidden" id="enquiryVideoDuration" name="video_duration_hidden">
                <input type="hidden" id="enquirySlotsCount" name="slots_count_hidden">

                <input type="hidden" id="hoardingType" value="{{ $modalHoarding?->hoarding_type }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">

                    <div>
                        <label class="font-medium">Full Name</label>
                        <input id="enquiryName"
                               name="customer_name"
                               class="enquiry-input"
                               placeholder="Enter full name"
                               value="{{ auth()->check() ? auth()->user()->name : '' }}"
                               readonly
                               >
                    </div>


                    <div>
                        <label class="font-medium">Email</label>
                        <input id="enquiryEmail"
                               name="customer_email"
                               type="email"
                               class="enquiry-input"
                               placeholder="Enter email address"
                               value="{{ auth()->check() ? auth()->user()->email : '' }}"
                        >
                    </div>

                    <div>
                        <label class="font-medium">Mobile *</label>
                        <div class="flex gap-3 items-center">
                            <input id="enquiryMobile"
                                name="customer_mobile"
                                class="enquiry-input"
                                placeholder="Enter mobile number"
                                value="{{ auth()->check() ? auth()->user()->phone : '' }}"
                                required>
                        </div>
                    </div>


                    <div>
                        <label class="font-medium">
                            When you want to start the campaign? *
                        </label>
                        <input type="date"
                               name="preferred_start_date"
                               id="enquiryStartDate"
                               class="enquiry-input"
                               required
                               min="{{ date('Y-m-d') }}">
                    </div>

                    <div id="monthWrapper">
                        <label class="font-medium" id="monthLabel">For how many months? *</label>
                        <select id="packageSelect" class="enquiry-input">
                            @for($i=1;$i<=12;$i++)
                                <option value="{{ $i }}">{{ $i }} Month</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="font-medium">No. of Selected Hoardings</label>
                        <input id="enquiryCount"
                               name="selected_hoardings"
                               readonly
                               class="enquiry-input bg-gray-100">
                    </div>

                </div>

                {{-- SELECTED OFFER --}}
                <div>
                    <label class="font-medium mb-2">Selected Offer</label>
                    <select id="enquiryPackage"
                            name="selected_package"
                            class="enquiry-input w-full">

                        <option value="base" id="basePriceOption">
                            Base Price
                        </option>
                        {{-- Packages loaded dynamically via JavaScript API --}}
                    </select>
                    
                </div>
                {{-- ADD THIS HERE --}}
               {{-- @dump($hoarding->hoarding_type) --}}
                <div id="doohFields" class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 p-4 mb-5 bg-green-50 rounded-lg border border-green-100" style="display:none;">
                    <div class="sm:col-span-2">
                        <p class="text-[#2F5D46] font-bold mb-1">Digital Ad Settings</p>
                        <hr class="border-green-200 mb-3">
                    </div>
                    <div>
                        <label class="font-medium text-gray-700">Slot Duration (Seconds) *</label>
                        <input type="number" name="video_duration" class="enquiry-input" value="" readonly>
                    </div>
                    <div>
                        <label class="font-medium text-gray-700">Slots per day *</label>
                        <input type="number" name="slots_count" class="enquiry-input" value="" min="1" max="10000" readonly>
                    </div>
                </div>
                <div id="doohNote" class="p-3 bg-blue-50 border border-blue-100 rounded-lg" style="display:none;">
                    <p class="text-blue-700 text-xs italic font-medium">Digital Note: This hoarding uses ad-loop slots. Final pricing depends on loop frequency.</p>
                </div>

                <div>
                    <label class="font-medium">Describe your requirement</label>
                    <textarea id="enquiryMessage"
                              name="message"
                              class="enquiry-input h-40 resize-none"
                              placeholder="Any specific campaign requirements, target audience, or other details.."></textarea>
                </div>

            </div>

            {{-- FOOTER --}}
          <div class="px-4 py-4 sm:px-6 border-t border-gray-200 bg-white">
                <button type="submit"
                        id="enquirySubmitBtn"
                        class="w-full bg-[#2F5D46] hover:bg-[#264B39]
                            text-white py-3 rounded-md font-semibold flex items-center justify-center relative overflow-hidden">
                    <span id="enquiryBtnText" class="flex items-center justify-center w-full h-full cursor-pointer">Enquire Now</span>
                    <span
                        id="enquiryLoader"
                        class="hidden absolute inset-0 flex items-center justify-center bg-[#2f5d46] bg-opacity-90"
                    >
                        <svg class="w-5 h-5 animate-spin text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span id="enquiryLoaderText" class="ml-1">Loading<span class="dot-one">.</span><span class="dot-two">.</span><span class="dot-three">.</span></span>
                    </span>
                </button>
            </div>

        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
        var email = document.getElementById('enquiryEmail');
        var phone = document.getElementById('enquiryMobile');
        var user = {
            email: '{{ auth()->check() ? auth()->user()->email : '' }}',
            phone: '{{ auth()->check() ? auth()->user()->phone : '' }}'
        };
        // If both email and phone exist, make email readonly
        if (user.email && user.phone) {
            email.readOnly = true;
            phone.readOnly = false;
        } else if (user.email) {
            email.readOnly = true;
            phone.readOnly = false;
        } else if (user.phone) {
            phone.readOnly = true;
            email.readOnly = false;
        } else {
            email.readOnly = false;
            phone.readOnly = false;
        }


            // Robust validation before submit
            document.getElementById('enquiryForm').addEventListener('submit', function(e) {
                // CRITICAL: Sync all fields BEFORE validation
                syncEnquiryHiddenFields();
                
                // CRITICAL: Update hoardingType from window state
                if (window.selectedPackageState && window.selectedPackageState.type) {
                    document.getElementById('hoardingType').value = window.selectedPackageState.type;
                }
                
                var hoardingType = document.getElementById('hoardingType')?.value || '';
                var name = document.getElementById('enquiryName')?.value.trim();
                var email = document.getElementById('enquiryEmail')?.value.trim();
                var mobile = document.getElementById('enquiryMobile')?.value.trim();
                var startDate = document.getElementById('enquiryStartDate')?.value;
                
                // CRITICAL: Read the ACTUAL VISIBLE FORM FIELDS
                var videoDurationField = document.querySelector('select[name="video_duration"]');
                var slotsCountField = document.querySelector('input[name="slots_count"]');
                var videoDuration = videoDurationField ? videoDurationField.value : '';
                var slotsCount = slotsCountField ? slotsCountField.value : '';
                
                var packageSelect = document.getElementById('enquiryPackage');
                var monthsSelect = document.getElementById('packageSelect');
                var errorMsg = '';
                
                console.log('[DEBUG] ===== FORM SUBMIT START =====');
                console.log('[DEBUG] hoardingType:', hoardingType);
                console.log('[DEBUG] videoDuration (from field):', videoDuration);
                console.log('[DEBUG] slotsCount (from field):', slotsCount);
                console.log('[DEBUG] videoDurationField exists:', !!videoDurationField);
                console.log('[DEBUG] slotsCountField exists:', !!slotsCountField);
                
                if (!name) errorMsg += 'Full Name is required.\n';
                if (!email) errorMsg += 'Email is required.\n';
                if (!mobile) errorMsg += 'Mobile is required.\n';
                if (!startDate) errorMsg += 'Start Date is required.\n';
                
                // DOOH-specific validation - ONLY if hoarding type is explicitly 'dooh'
                var isDoohHoarding = hoardingType && hoardingType.toLowerCase() === 'dooh';
                
                console.log('[DEBUG] isDoohHoarding:', isDoohHoarding);
                
                if (isDoohHoarding) {
                    console.log('[DEBUG] ✅ DOOH HOARDING DETECTED - Validating DOOH fields');
                    
                    // Check if fields are visible (they should be for DOOH)
                    var doohFieldsDiv = document.getElementById('doohFields');
                    var isVisible = doohFieldsDiv && doohFieldsDiv.style.display !== 'none';
                    console.log('[DEBUG] DOOH fields visible:', isVisible);
                    
                    // Validate filled values
                    if (!videoDuration || videoDuration === '') {
                        errorMsg += 'Video Duration is required.\n';
                        console.log('[DEBUG] ❌ Video Duration empty');
                    } else {
                        console.log('[DEBUG] ✅ Video Duration set to:', videoDuration);
                    }
                    
                    if (!slotsCount || slotsCount === '') {
                        errorMsg += 'Slots Count is required.\n';
                        console.log('[DEBUG] ❌ Slots Count empty');
                    } else {
                        console.log('[DEBUG] ✅ Slots Count set to:', slotsCount);
                    }
                    
                    // Validate video_duration is ONLY 15 or 30
                    var videoDurationInt = parseInt(videoDuration);
                    if (videoDuration && ![15, 30].includes(videoDurationInt)) {
                        errorMsg += 'Video Duration must be 15 or 30 seconds.\n';
                        console.log('[DEBUG] ❌ Video Duration invalid:', videoDurationInt);
                    } else if (videoDuration) {
                        console.log('[DEBUG] ✅ Video Duration valid:', videoDurationInt);
                    }
                    
                    // Validate slots_count is >= 1
                    var slotsCountInt = parseInt(slotsCount);
                    if (slotsCount && slotsCountInt < 1) {
                        errorMsg += 'Slots Count must be at least 1.\n';
                        console.log('[DEBUG] ❌ Slots Count invalid:', slotsCountInt);
                    } else if (slotsCount) {
                        console.log('[DEBUG] ✅ Slots Count valid:', slotsCountInt);
                    }
                } else {
                    console.log('[DEBUG] ⏭️ OOH HOARDING - Skipping DOOH field validation');
                }
                
                // If no package is available, set package_id to null and set months
                if (!packageSelect || packageSelect.value === 'base' || !packageSelect.value) {
                    document.getElementById('enquiryPackageId').value = '';
                    if (monthsSelect && monthsSelect.value) {
                        document.getElementById('enquiryMonths').value = monthsSelect.value;
                    } else {
                        document.getElementById('enquiryMonths').value = '1';
                    }
                } else {
                    document.getElementById('enquiryMonths').value = '';
                }
                
                // CONDITIONAL: Remove DOOH fields for OOH hoardings BEFORE validation
                if (hoardingType.toLowerCase() !== 'dooh') {
                    if (videoDurationField) videoDurationField.removeAttribute('name');
                    if (slotsCountField) slotsCountField.removeAttribute('name');
                    console.log('[DEBUG] OOH Hoarding - Removed DOOH field names before submission');
                }
                
                console.log('[DEBUG] ===== VALIDATION RESULT =====');
                console.log('[DEBUG] errorMsg:', errorMsg ? 'YES - ' + errorMsg : 'NONE');
                
                if (errorMsg) {
                    e.preventDefault();
                    alert(errorMsg);
                    console.log('[DEBUG] ❌ Form submission BLOCKED due to validation errors');
                    return false;
                }
                
                console.log('[DEBUG] ✅ All validation passed, allowing form submission');
            });
                
                // If no package is available, set package_id to null and set months
                console.log('[DEBUG] packageSelect:', packageSelect);
                console.log('[DEBUG] packageSelect.value:', packageSelect ? packageSelect.value : 'PACKAGE SELECT IS NULL');
                console.log('[DEBUG] monthsSelect:', monthsSelect);
                console.log('[DEBUG] monthsSelect.value:', monthsSelect ? monthsSelect.value : 'MONTHS SELECT IS NULL');
                
                // CRITICAL FIX: Check only if package value is 'base' (default/unselected)
                // Don't check options.length because for OOH with no packages, there's always just 1 option
                if (!packageSelect || packageSelect.value === 'base' || !packageSelect.value) {
                    console.log('[DEBUG] ✅ No package selected - setting months field from monthsSelect');
                    document.getElementById('enquiryPackageId').value = '';
                    if (monthsSelect && monthsSelect.value) {
                        document.getElementById('enquiryMonths').value = monthsSelect.value;
                        console.log('[DEBUG] Set enquiryMonths.value =', monthsSelect.value);
                    } else {
                        console.log('[DEBUG] ⚠️ Using default months value of 1');
                        document.getElementById('enquiryMonths').value = '1';
                    }
                } else {
                    console.log('[DEBUG] ✅ Package selected - no need for months field');
                    document.getElementById('enquiryMonths').value = '';
                }
                
                // CONDITIONAL: Remove DOOH fields for OOH hoardings BEFORE validation
                if (hoardingType.toLowerCase() !== 'dooh') {
                    var videoDurationField = document.querySelector('select[name="video_duration"]');
                    var slotsCountField = document.querySelector('input[name="slots_count"]');
                    if (videoDurationField) videoDurationField.removeAttribute('name');
                    if (slotsCountField) slotsCountField.removeAttribute('name');
                    console.log('[DEBUG] OOH Hoarding - Removed DOOH field names before submission');
                }
                
                // Log final form data before submission
                var formData = new FormData(this);
                console.log('[DEBUG] FINAL FORM DATA before submission:');
                for (var pair of formData.entries()) {
                    console.log('  ' + pair[0] + ':', pair[1]);
                }
                
                if (errorMsg) {
                    e.preventDefault();
                    alert(errorMsg);
                    console.log('[DEBUG] ❌ Form submission BLOCKED due to validation errors');
                    return false;
                }
                
                console.log('[DEBUG] ✅ All validation passed, allowing form submission');
                // Don't prevent default - let the form submit naturally
            });

        // Show/hide Digital Ad Settings based on hoardingType
        function toggleDoohFields() {
            var hoardingType = document.getElementById('hoardingType')?.value || '';
            var doohFields = document.getElementById('doohFields');
            var doohNote = document.getElementById('doohNote');
            // Use lowercase comparison for consistency
            if (hoardingType.toLowerCase() === 'dooh') {
                if (doohFields) doohFields.style.display = '';
                if (doohNote) doohNote.style.display = '';
            } else {
                if (doohFields) doohFields.style.display = 'none';
                if (doohNote) doohNote.style.display = 'none';
            }
        }
        // Initial call
        toggleDoohFields();
        // Also call when modal opens (in case hoardingType changes dynamically)
        document.getElementById('enquiryModal').addEventListener('modal:open', toggleDoohFields);
        // Or poll for changes if needed
        document.getElementById('hoardingType')?.addEventListener('change', toggleDoohFields);
});
</script>
<script>
/* ===== DOOH FIELD AUTO SYNC (NO CONFLICT, NO DELAY) ===== */
(function () {

    const typeInput  = document.getElementById('hoardingType');
    const doohFields = document.getElementById('doohFields');
    const doohNote   = document.getElementById('doohNote');

    if (!typeInput || !doohFields) return;

    function applyDoohState() {
        const type = (typeInput.value || '').toLowerCase();

        if (type === 'dooh') {
            doohFields.style.display = '';
            if (doohNote) doohNote.style.display = '';
        } else {
            doohFields.style.display = 'none';
            if (doohNote) doohNote.style.display = 'none';
        }
    }

    /* --- 1️⃣ When modal opens (instant) --- */
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.enquiry-btn');
        const slotDuration = btn.dataset.slotDuration;
        const totalSlots   = btn.dataset.totalSlots;
        const videoInput = document.querySelector('input[name="video_duration"]');
        const slotsInput = document.querySelector('input[name="slots_count"]');
        if (videoInput && slotDuration) {
            videoInput.value = slotDuration;
        }
        if (slotsInput && totalSlots) {
            slotsInput.value = totalSlots;
        }
        if(!btn) return;

        // wait 1 frame after openEnquiryModal sets value
        requestAnimationFrame(applyDoohState);
    }, true);

    /* --- 2️⃣ When JS changes hoardingType (API response) --- */
    const observer = new MutationObserver(applyDoohState);

    observer.observe(typeInput, {
        attributes: true,
        attributeFilter: ['value']
    });

    /* --- 3️⃣ safety (initial render) --- */
    setTimeout(applyDoohState, 50);

})();
</script>
<script>
/* ===== DOOH AUTO READONLY FIELDS ===== */
(function(){

    const typeInput = document.getElementById('hoardingType');

    function applyReadonly(){
        const isDooh = (typeInput.value || '').toLowerCase() === 'dooh';

        const video  = document.querySelector('input[name="video_duration"]');
        const slots  = document.querySelector('input[name="slots_count"]');

        if(!video || !slots) return;

        if(isDooh){
            video.readOnly = true;
            slots.readOnly = true;

            video.classList.add('readonly-clean');
            slots.classList.add('readonly-clean');
        }else{
            video.readOnly = false;
            slots.readOnly = false;

            video.classList.remove('readonly-clean');
            slots.classList.remove('readonly-clean');
        }
    }
    document.addEventListener('click', function(e){
        if(e.target.closest('.enquiry-btn')){
            requestAnimationFrame(applyReadonly);
        }
    }, true);
    const observer = new MutationObserver(applyReadonly);
    if(typeInput){
        observer.observe(typeInput,{attributes:true,attributeFilter:['value']});
    }
    setTimeout(applyReadonly,100);
})();
</script>
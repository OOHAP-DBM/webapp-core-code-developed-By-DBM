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
</style>


<style>
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
</style>



@php
    // Only use $hoarding if it is set and not on homepage
    // $modalHoarding is only used for SSR fallback. All dynamic data should be set by JS.
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
                    class="text-gray-500 hover:text-black text-xl">
                ✕
            </button>
        </div>

        {{-- FORM --}}
        <form id="enquiryForm"
              action="{{ route('enquiries.store') }}"
              method="POST"
              class="flex flex-col overflow-hidden">
                        @csrf
                        autocomplete="off">

            {{-- BODY --}}
            <div class="flex-1 overflow-y-auto px-6 py-6 space-y-5 text-sm">

                {{-- HIDDEN FIELDS (UNCHANGED) --}}
                <input type="hidden" id="enquiryHoardingId" name="hoarding_id">
                <input type="hidden" id="enquiryPackageId" name="package_id">
                <input type="hidden" id="enquiryPackageLabel" name="package_label">
                <input type="hidden" id="enquiryAmount" name="amount">
                <input type="hidden" id="enquiryDurationType" name="duration_type" value="months">
                <input type="hidden" id="enquiryEndDate" name="preferred_end_date">

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
                               required>
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
                    <label class="font-medium">Selected Offer</label>
                    <select id="enquiryPackage"
                            name="selected_package"
                            class="enquiry-input">

                        <option value="base" id="basePriceOption">
                            Base Price
                        </option>
                            {{-- Always render available packages if present --}}
                            @if($packages && $packages->count())
                                @foreach($packages as $pkg)
                                    @php
                                        $finalPrice = 0;
                                        $discountPercent = (int) ($pkg->discount_percent ?? 0);
                                        if ($pkg->type === 'ooh' || ($hoarding->hoarding_type ?? '') === 'ooh') {
                                            $monthly  = (int) ($hoarding->monthly_price ?? 0);
                                            $base = $monthly;
                                            $discountAmount = ($base * $discountPercent) / 100;
                                            $finalPrice = max(0, round($base - $discountAmount));
                                        } else {
                                            $finalPrice = (int) ($pkg->slots_per_month ?? 0);
                                        }
                                    @endphp
                                    <option value="{{ $pkg->id }}"
                                            data-label="{{ $pkg->package_name }}"
                                            data-price="{{ $finalPrice }}"
                                            data-months="{{ $pkg->min_booking_duration ?? 1 }}">
                                        {{ $pkg->package_name }}
                                        ({{ $pkg->min_booking_duration }} {{ $pkg->duration_unit }})
                                        – ₹{{ number_format($finalPrice) }}
                                        @if($discountPercent > 0)
                                            &nbsp;&nbsp; [{{ $discountPercent }}% OFF]
                                        @endif
                                    </option>
                                @endforeach
                            @endif

                        @foreach($packages as $pkg)
                            @php
                                $finalPrice = 0;
                                $discountPercent = (int) ($pkg->discount_percent ?? 0);

                                if ($hoarding->hoarding_type  === 'ooh') {
                                    $monthly  = (int) ($hoarding->monthly_price ?? 0);
                                    $base = $monthly;
                                    $discountAmount = ($base * $discountPercent) / 100;
                                    $finalPrice = max(0, round($base - $discountAmount));
                                } else {
                                    $finalPrice = (int) ($pkg->slots_per_month ?? 0);
                                }
                            @endphp

                            <option value="{{ $pkg->id }}"
                                    data-label="{{ $pkg->package_name }}"
                                    data-price="{{ $finalPrice }}"
                                    data-months="{{ $pkg->min_booking_duration ?? 1 }}">

                                {{ $pkg->package_name }}
                                ({{ $pkg->min_booking_duration }} {{ $pkg->duration_unit }})
                                – ₹{{ number_format($finalPrice) }}
                                @if($discountPercent > 0)
                                    &nbsp;&nbsp; [{{ $discountPercent }}% OFF]
                                @endif

                            </option>
                        @endforeach

                        

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
                        <label class="font-medium text-gray-700">Video Duration (Seconds) *</label>
                        <select name="video_duration" class="enquiry-input">
                            <option value="15">15 Seconds</option>
                            <option value="30">30 Seconds</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-medium text-gray-700">Required Slots per day *</label>
                        <input type="number" name="slots_count" class="enquiry-input" placeholder="e.g. 120" value="120" min="1">
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
                               text-white py-3 rounded-md font-semibold">
                    Enquire Now
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

            // Show/hide Digital Ad Settings based on hoardingType
            function toggleDoohFields() {
                var hoardingType = document.getElementById('hoardingType')?.value;
                var doohFields = document.getElementById('doohFields');
                var doohNote = document.getElementById('doohNote');
                if (hoardingType === 'dooh') {
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

            // Robust validation before submit
            document.getElementById('enquiryForm').addEventListener('submit', function(e) {
                var hoardingType = document.getElementById('hoardingType')?.value;
                var name = document.getElementById('enquiryName')?.value.trim();
                var email = document.getElementById('enquiryEmail')?.value.trim();
                var mobile = document.getElementById('enquiryMobile')?.value.trim();
                var startDate = document.getElementById('enquiryStartDate')?.value;
                var videoDuration = document.querySelector('select[name="video_duration"]')?.value;
                var slotsCount = document.querySelector('input[name="slots_count"]')?.value;
                var packageSelect = document.getElementById('enquiryPackage');
                var errorMsg = '';
                if (!name) errorMsg += 'Full Name is required.\n';
                if (!email) errorMsg += 'Email is required.\n';
                if (!mobile) errorMsg += 'Mobile is required.\n';
                if (!startDate) errorMsg += 'Start Date is required.\n';
                if (hoardingType === 'dooh') {
                    if (!videoDuration) errorMsg += 'Video Duration is required.\n';
                    if (!slotsCount) errorMsg += 'Slots Count is required.\n';
                }
                // If no package is available, set package_id to null
                if (!packageSelect || packageSelect.options.length <= 1) {
                    document.getElementById('enquiryPackageId').value = '';
                }
                if (errorMsg) {
                    e.preventDefault();
                    alert(errorMsg);
                    return false;
                }
            });

        // Show/hide Digital Ad Settings based on hoardingType
        function toggleDoohFields() {
            var hoardingType = document.getElementById('hoardingType')?.value;
            var doohFields = document.getElementById('doohFields');
            var doohNote = document.getElementById('doohNote');
            if (hoardingType === 'dooh') {
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

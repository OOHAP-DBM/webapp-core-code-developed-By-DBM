<style>
/* backdrop */
#enquiryModal {
    background: rgba(0,0,0,0.35);
}

/* modal card */
#enquiryModal > div > div {
    background: #F6FAF8;
}
</style>

<style>
.enquiry-input{
    width:100%;
    margin-top:6px;
    border:1px solid #E5E7EB;
    border-radius:8px;
    padding:12px 14px;
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

/* readonly fields */
.enquiry-input[readonly],
.enquiry-input:disabled{
    background:#F3F4F6;
    color:#6B7280;
}
</style>

@php
    $modalHoarding = $hoarding ?? null;
    $packages = $modalHoarding?->packages ?? collect();
    $priceType = $modalHoarding?->price_type ?? null;
@endphp

{{-- ================= ENQUIRY MODAL ================= --}}
<div id="enquiryModal"
     class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center">

    <div class="relative w-full h-full sm:h-auto sm:max-h-[90vh]
                sm:max-w-3xl bg-white sm:rounded-2xl
                flex flex-col overflow-hidden">

        {{-- HEADER --}}
        <div class="flex items-start justify-between px-6 py-5 border-b border-gray-200 bg-[#F6FAF8]">

            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    Raise an Enquiry
                </h3>
                <p class="text-sm text-gray-500">
                    Please fill your details
                </p>
            </div>
            <button type="button" onclick="closeEnquiryModal()"
                    class="text-gray-500 hover:text-black text-xl">
                ✕
            </button>
        </div>

        {{-- FORM --}}
        <form id="enquiryForm" action="{{route('enquiries.store')}}" method="POST" class="flex flex-col overflow-hidden">
            @csrf
            
            {{-- BODY --}}
            <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-5 space-y-5 text-sm">

                <input type="hidden" id="enquiryHoardingId" name="hoarding_id">
                <input type="hidden" id="enquiryPackageId" name="package_id">
                <input type="hidden" id="enquiryPackageLabel" name="package_label">
                <input type="hidden" id="enquiryAmount" name="amount">
                <input type="hidden" id="enquiryDurationType" name="duration_type" value="months">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>
                        <label class="font-medium">Full Name *</label>
                        <input id="enquiryName" name="customer_name" class="enquiry-input" placeholder="Enter full name" required>
                    </div>

                    <div>
                        <label class="font-medium">Email *</label>
                        <input id="enquiryEmail" name="customer_email" type="email" class="enquiry-input" placeholder="Enter email" required>
                    </div>

                    <div>
                        <label class="font-medium">Mobile *</label>
                        <input id="enquiryMobile" name="customer_mobile" class="enquiry-input" placeholder="Enter mobile number" required>
                    </div>

                    <div>
                        <label class="font-medium">When you want to start the Campain? *</label>
                        <input type="date" name="preferred_start_date" class="enquiry-input" id="enquiryStartDate" required>
                    </div>

                    {{-- PACKAGE SELECT --}}
                    <div id="monthWrapper">
                        <label class="font-medium">Select Month *</label>
                        <select id="packageSelect" class="enquiry-input">
                            <option value="1">1 Month</option>
                            <option value="2">2 Month</option>
                            <option value="3">3 Month</option>
                            <option value="4">4 Month</option>
                            <option value="5">5 Month</option>
                            <option value="6">6 Month</option>
                            <option value="7">7 Month</option>
                            <option value="8">8 Month</option>
                            <option value="9">9 Month</option>
                            <option value="10">10 Month</option>
                            <option value="11">11 Month</option>
                            <option value="12">12 Month</option>
                        </select>
                    </div>

                    <div>
                        <label class="font-medium">Selected Hoardings</label>
                        <input id="enquiryCount"
                               name="selected_hoardings"
                               readonly
                               class="enquiry-input bg-gray-50">
                    </div>

                </div>

                {{-- SELECTED PACKAGE --}}
                <div>
                    <label class="font-medium">Selected Package</label>
                    <select id="enquiryPackage"
                            name="selected_package"
                            class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2
                                text-sm focus:outline-none focus:ring-2 focus:ring-green-500">

                        {{-- DEFAULT : BASE PRICE --}}
                       <option value="base" id="basePriceOption">
                            Base Price
                        </option>

                        {{-- PACKAGES --}}
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}"
                                data-price="{{ $hoarding->price_type === 'ooh'
                                    ? ($pkg->base_price_per_month * $pkg->min_booking_duration)
                                    : $pkg->slots_per_month }}"
                                data-months="{{ $pkg->min_booking_duration }}">


                                {{ $pkg->package_name }}
                                (
                                {{ $hoarding->price_type === 'ooh'
                                    ? $pkg->min_booking_duration.' '.$pkg->duration_unit
                                    : $pkg->duration }}
                                )
                                – ₹{{ number_format(
                                    $hoarding->price_type === 'ooh'
                                        ? ($pkg->base_price_per_month * $pkg->min_booking_duration)
                                        : $pkg->slots_per_month
                                ) }}

                            </option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <label class="font-medium">Describe your requirement</label>
                    <textarea id="enquiryMessage" name="message" class="enquiry-input h-24"
                              placeholder="Write here..."></textarea>
                </div>

                <input type="hidden" id="enquiryEndDate" name="preferred_end_date">

            </div>

            {{-- FOOTER --}}
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-white">
                <button type="submit" id="enquirySubmitBtn"
                        class="w-full bg-green-700 hover:bg-green-800
                               text-white py-3 rounded-lg font-semibold">
                    Enquire Now
                </button>
            </div>

        </form>

    </div>
</div>

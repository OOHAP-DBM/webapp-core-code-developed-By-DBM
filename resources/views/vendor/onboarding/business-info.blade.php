@extends('layouts.app')

@section('title', 'Add Business Info')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

<div class="vendor-page-white min-h-screen bg-white pb-12">
    <div class="vendor-header px-4 md:px-8 py-6 flex items-center gap-2 mt-5">
        <img src="{{ asset('assets/images/logo/logo_image.jpeg') }}" alt="OOHAPP" class="h-8">
        <span class="text-gray-500 text-sm font-medium">| Vendor</span>
    </div>

    <div class="vendor-signup-wrapper max-w-4xl mx-auto px-4">
        <div class="signup-steps flex items-center mb-10 overflow-x-auto pb-4">
            <div class="step completed flex items-center gap-2 whitespace-nowrap">
                <span class="w-7 h-7 rounded-full bg-green-500 text-white flex items-center justify-center text-xs">1</span>
                <p class="text-xs font-bold text-gray-400 uppercase">User Info</p>
            </div>
            <div class="line flex-1 h-px bg-green-500 mx-4 min-w-[30px]"></div>
            <div class="step active flex items-center gap-2 whitespace-nowrap">
                <span class="w-7 h-7 rounded-full bg-green-500 text-white flex items-center justify-center text-xs">2</span>
                <p class="text-xs font-bold text-black uppercase">Business Info</p>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 mb-4 text-green-700 bg-green-100 rounded-lg border border-green-200">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="p-4 mb-4 text-red-700 bg-red-100 rounded-lg border border-red-200">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form class="signup-card space-y-6" method="POST" action="{{ route('vendor.onboarding.submitVendorInfo') }}" enctype="multipart/form-data" id="vendorOnboardingForm" novalidate>
            @csrf
            
            <div class="section-container border-b pb-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 border-l-4 border-green-500 pl-3">General Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-1">GSTIN Number<span class="text-red-500">*</span></label>
                        <input type="text" name="gstin" id="gstin" class="w-full border rounded-md p-2 uppercase" placeholder="22AAAAA0000A1Z5"  maxlength="15">
                        <div class="error-msg text-red-500 text-xs mt-1 hidden">Please enter a valid 15-digit GSTIN.</div>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-1">Business Type<span class="text-red-500">*</span></label>
                        <select name="business_type" id="business_type" class="w-full border rounded-md p-2 bg-white" >
                            <option value="">Choose Business Type</option>
                            <option value="Proprietorship">Proprietorship</option>
                            <option value="Partnership">Partnership</option>
                            <option value="Private Limited">Private Limited</option>
                        </select>
                        <div class="error-msg text-red-500 text-xs mt-1 hidden">Please select a business type.</div>
                    </div>
                    <div class="form-group md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Business Name<span class="text-red-500">*</span></label>
                        <input type="text" name="business_name" id="business_name" class="w-full border rounded-md p-2" placeholder="Legal Entity Name" >
                        <div class="error-msg text-red-500 text-xs mt-1 hidden">Business name is required.</div>
                    </div>
                </div>
            </div>

            <div class="section-container border-b pb-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 border-l-4 border-green-500 pl-3">Registered Address</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Street Address<span class="text-red-500">*</span></label>
                        <input type="text" name="registered_address" id="registered_address" class="w-full border rounded-md p-2"  maxlength="64">
                        <div class="flex justify-between mt-1">
                            <span class="error-msg text-red-500 text-xs hidden">Address is required.</span>
                            <small id="addressCharCount" class="text-gray-400 text-xs ml-auto">0/64 Characters</small>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Pincode<span class="text-red-500">*</span></label>
                        <input type="text" name="pincode" id="pincode" class="w-full border rounded-md p-2"  maxlength="6" pattern="[0-9]{6}">
                        <div class="error-msg text-red-500 text-xs mt-1 hidden">Valid 6-digit pincode required.</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">City<span class="text-red-500">*</span></label>
                        <input type="text" name="city" id="city" class="w-full border rounded-md p-2" >
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">State<span class="text-red-500">*</span></label>
                        <input type="text" name="state" id="state" class="w-full border rounded-md p-2" >
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Country</label>
                        <input type="text" name="country" id="country" value="India" class="w-full border rounded-md p-2 bg-gray-50">
                    </div>
                </div>
            </div>

            <div class="section-container border-b pb-6">
                <h3 class="text-lg font-semibold text-gray-800 border-l-4 border-green-500 pl-3">Bank Details</h3>
                <p class="text-xs text-gray-500 mb-4 mt-1">Account name must match GSTIN registration name.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-1">Bank Name<span class="text-red-500">*</span></label>
                        <select name="bank_name" id="bank_name" class="w-full border rounded-md p-2 bg-white" >
                            <option value="">Choose Bank</option>
                            <option value="SBI">State Bank of India</option>
                            <option value="HDFC">HDFC Bank</option>
                            <option value="ICICI">ICICI Bank</option>
                            <option value="Axis">Axis Bank</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-1">Account Number<span class="text-red-500">*</span></label>
                        <input type="password" name="account_number" id="account_number" class="w-full border rounded-md p-2" >
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-1">IFSC Code<span class="text-red-500">*</span></label>
                        <input type="text" name="ifsc_code" id="ifsc_code" class="w-full border rounded-md p-2 uppercase"  placeholder="SBIN0001234">
                        <div class="error-msg text-red-500 text-xs mt-1 hidden">Invalid IFSC format.</div>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-1">Account Holder Name<span class="text-red-500">*</span></label>
                        <input type="text" name="account_holder_name" id="account_holder_name" class="w-full border rounded-md p-2" >
                    </div>
                </div>
            </div>

            <div class="section-container">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 border-l-4 border-green-500 pl-3 flex items-center gap-2">
                    Identity Verification <span class="text-gray-400 text-xs cursor-help" title="PAN card is mandatory for tax compliance">ⓘ</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-1">PAN Number<span class="text-red-500">*</span></label>
                        <input type="text" name="pan_number" id="pan_number" class="w-full border rounded-md p-2 uppercase" placeholder="ABCDE1234F"  maxlength="10">
                        <div class="error-msg text-red-500 text-xs mt-1 hidden">Enter a valid 10-digit PAN.</div>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-1">Upload PAN (PDF/Image)<span class="text-red-500">*</span></label>
                        <div class="flex flex-col gap-2">
                            <input type="file" name="pan_card_document" id="pan_card_document" accept=".pdf,.jpg,.jpeg,.png" class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" >
                            <small class="text-gray-400">Max size: 5MB</small>
                        </div>
                    </div>
                </div>
            </div>

           <div class="flex flex-col md:flex-row items-center justify-between gap-4 mt-8 pb-10">
                <button type="submit" 
                        id="submitBtn" 
                        class="w-full md:w-48 py-3 rounded-lg font-bold bg-green-600 text-white shadow-lg hover:bg-green-700 transition-all active:scale-95 flex items-center justify-center">
                    <span>Continue</span>
                </button>

                <button type="button" 
                        onclick="skipBusinessInfo()" 
                        id="skipBtn"
                        class="w-full md:w-48 py-3 rounded-lg font-semibold text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-all flex items-center justify-center">
                    Skip for Now
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
{{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('vendorOnboardingForm');
        const submitBtn = document.getElementById('submitBtn');
        const addressInput = document.getElementById('registered_address');
        const addressCharCount = document.getElementById('addressCharCount');

        // Validation Patterns
        const patterns = {
            gstin: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/,
            pan: /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/,
            ifsc: /^[A-Z]{4}0[A-Z0-9]{6}$/,
            pincode: /^[1-9][0-9]{5}$/
        };

        function validateField(field) {
            let isValid = true;
            const errorDiv = field.parentElement.querySelector('.error-msg');
            
            // Reset state
            field.classList.remove('border-red-500', 'border-green-500');
            if(errorDiv) errorDiv.classList.add('hidden');

            // Check required
            // if (field.hasAttribute('required') && !field.value.trim()) {
            //     isValid = false;
            // }

            // Pattern matching
            if (isValid && field.value.trim()) {
                if (field.id === 'gstin' && !patterns.gstin.test(field.value.toUpperCase())) isValid = false;
                if (field.id === 'pan_number' && !patterns.pan.test(field.value.toUpperCase())) isValid = false;
                if (field.id === 'ifsc_code' && !patterns.ifsc.test(field.value.toUpperCase())) isValid = false;
                if (field.id === 'pincode' && !patterns.pincode.test(field.value)) isValid = false;
            }

            // File size check
            if (field.type === 'file' && field.files[0]) {
                if (field.files[0].size > 5 * 1024 * 1024) isValid = false;
            }

            // UI Feedback
            if (!isValid && field.value.trim() !== "") {
                field.classList.add('border-red-500');
                if(errorDiv) errorDiv.classList.remove('hidden');
            } else if (isValid && field.value.trim() !== "") {
                field.classList.add('border-green-500');
            }

            return isValid;
        }

        function checkFormValidity() {
            // const inputs = form.querySelectorAll('[required]');
            let isFormValid = true;

            // inputs.forEach(input => {
            //     if (!validateField(input)) isFormValid = false;
            // });

            submitBtn.disabled = !isFormValid;
            if (isFormValid) {
                submitBtn.classList.remove('bg-gray-200', 'text-gray-500');
                submitBtn.classList.add('bg-green-600', 'text-white', 'hover:bg-green-700');
            } else {
                submitBtn.classList.add('bg-gray-200', 'text-gray-500');
                submitBtn.classList.remove('bg-green-600', 'text-white');
            }
        }

        // Event Listeners
        form.addEventListener('input', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') {
                checkFormValidity();
            }
        });

        addressInput.addEventListener('input', function() {
            const len = this.value.length;
            addressCharCount.textContent = `${len}/64 Characters`;
            addressCharCount.classList.toggle('text-red-500', len > 64);
        });

        form.addEventListener('submit', function(e) {
            if (submitBtn.disabled) e.preventDefault();
        });

        
    });
</script> --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('vendorOnboardingForm');
    const submitBtn = document.getElementById('submitBtn');
    const skipBtn = document.getElementById('skipBtn');
    
    const patterns = {
        gstin: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/,
        pan: /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/,
        ifsc: /^[A-Z]{4}0[A-Z0-9]{6}$/,
        pincode: /^[1-9][0-9]{5}$/
    };

    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Define fields to validate
        const requiredFields = [
            { id: 'gstin', pattern: patterns.gstin },
            { id: 'business_type' },
            { id: 'business_name' },
            { id: 'registered_address' },
            { id: 'pincode', pattern: patterns.pincode },
            { id: 'city' },
            { id: 'state' },
            { id: 'bank_name' },
            { id: 'account_number' },
            { id: 'ifsc_code', pattern: patterns.ifsc },
            { id: 'account_holder_name' },
            { id: 'pan_number', pattern: patterns.pan }
        ];

        requiredFields.forEach(field => {
            const input = document.getElementById(field.id);
            if (!input) return;

            const errorMsg = input.parentElement.querySelector('.error-msg');
            let fieldValid = true;

            // 1. Check if empty
            if (!input.value.trim()) {
                fieldValid = false;
            } 
            // 2. Check pattern if provided
            else if (field.pattern && !field.pattern.test(input.value.toUpperCase())) {
                fieldValid = false;
            }

            // Apply UI Changes
            if (!fieldValid) {
                input.classList.add('border-red-500', 'bg-red-50');
                if (errorMsg) errorMsg.classList.remove('hidden');
                isValid = false;
            } else {
                input.classList.remove('border-red-500', 'bg-red-50');
                input.classList.add('border-green-500');
                if (errorMsg) errorMsg.classList.add('hidden');
            }
        });

        // Handle File Upload Validation (PAN Card)
        const panFile = document.getElementById('pan_card_document');
        if (panFile && panFile.files.length === 0) {
            panFile.classList.add('text-red-500');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault(); // Stop form submission
            // Scroll to the first error
            const firstError = document.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            // Show loading state on button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Processing...';
        }
    });

    // Optional: Clear error styling when user starts typing
    form.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('input', function() {
            this.classList.remove('border-red-500', 'bg-red-50');
            const err = this.parentElement.querySelector('.error-msg');
            if (err) err.classList.add('hidden');
        });
    });

    /**
     * LOGIC 2: SKIP (Bypass Validation)
     */
    window.skipBusinessInfo = function() {
    const isMobile = window.innerWidth < 640;

    Swal.fire({
        title: 'Skip Business Info?',
        text: 'You can add these details later from your profile.',
        icon: 'warning',

        // ✅ RESPONSIVE WIDTH (KEY FIX)
        width: isMobile ? '80%' : '450px',

        padding: '1rem',
        heightAuto: false,

        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Skip',
        cancelButtonText: 'Cancel',

        // ✅ CLEAN & ORGANIZED UI
        customClass: {
            popup: 'rounded-xl',
            title: 'text-base font-semibold',
            htmlContainer: 'text-sm',
            confirmButton: 'px-4 py-2 text-sm',
            cancelButton: 'px-4 py-2 text-sm'
        }
    }).then((result) => {
        if (!result.isConfirmed) return;

        const skipBtn = document.getElementById('skipBtn');
        skipBtn.disabled = true;
        skipBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Skipping...';

        fetch("{{ route('vendor.onboarding.skip-business-info') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            const data = await res.json();
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                throw new Error(data.message);
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message,
                width: isMobile ? '80%' : '450px'
            });
            skipBtn.disabled = false;
            skipBtn.innerText = 'Skip for Now';
        });
    });
};

});
</script>
@endpush
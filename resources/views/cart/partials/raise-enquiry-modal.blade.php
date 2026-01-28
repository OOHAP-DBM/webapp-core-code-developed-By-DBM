<!-- RAISE AN ENQUIRY MODAL -->
<div id="raiseEnquiryModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="relative bg-[#f6fbf8]  shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">

        <!-- LEFT ACCENT LINE -->
        <span class="absolute left-0 top-0 h-full w-1 bg-[#2f5d46] "></span>

        <!-- HEADER -->
        <div class="flex justify-between items-start p-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Raise an Enquiry</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Please fill your general details
                </p>
            </div>
            <button onclick="closeRaiseEnquiryModal()" class="text-gray-500 hover:text-gray-800 text-xl">
                ✕
            </button>
        </div>

        <!-- FORM -->
        <div class="px-6 pb-6">
            <form id="raiseEnquiryForm">

                <!-- ROW 1 -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">
                            Full Name<span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="enquiry_full_name"
                            placeholder="Enter full name"
                            class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">
                            Email<span class="text-red-500">*</span>
                        </label>
                        <input
                            type="email"
                            id="enquiry_email"
                            placeholder="Enter email address"
                            class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none"
                            required
                        >
                    </div>
                </div>

                <!-- ROW 2 -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">
                            Mobile<span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input
                                type="tel"
                                id="enquiry_mobile"
                                placeholder="Enter mobile number"
                                class="flex-1 px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none"
                                required
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">
                            No. of Selected Hoardings
                        </label>
                        <input
                            type="text"
                            id="enquiry_hoarding_count"
                            class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-md text-gray-600"
                            readonly
                        >
                    </div>
                </div>

                <!-- DATE -->
                <div class="mb-5">
                    <label class="block text-sm text-gray-700 mb-1">
                        When you want to start the campaign?<span class="text-red-500">*</span>
                    </label>
                    <input
                        type="date"
                        id="enquiry_campaign_date"
                        placeholder="Select date"
                        min="{{ now()->format('Y-m-d') }}"
                        class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none"
                        required
                    >
                </div>

                <!-- DESCRIPTION -->
                <div class="mb-6">
                    <label class="block text-sm text-gray-700 mb-1">
                        Describe your requirement
                    </label>
                    <textarea
                        id="enquiry_description"
                        rows="4"
                        placeholder="Write here..."
                        class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none"
                    ></textarea>
                </div>

                <!-- CTA -->
                <button
                    id="enquirySubmitBtn"
                    type="submit"
                    class="w-full py-3 bg-[#2f5d46] text-white font-semibold rounded-md hover:bg-[#274d3b] flex items-center justify-center relative overflow-hidden"
>
                    <span id="enquiryBtnText" class="flex items-center justify-center w-full h-full">Enquire Now</span>
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

            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
// Initialize date picker when modal opens
window.initEnquiryDatePicker = function() {
    const dateInput = document.getElementById('enquiry_campaign_date');
    if (dateInput && !dateInput.flatpickr) {
        flatpickr(dateInput, {
            minDate: new Date(),
            dateFormat: 'Y-m-d',
            disableMobile: false,
            onChange: function(selectedDates, dateStr) {
                console.log('📅 Campaign Date Selected:', dateStr);
            }
        });
    }
};

function openRaiseEnquiryModal(hoardingCount = 0) {
    const modal = document.getElementById('raiseEnquiryModal');
    const countInput = document.getElementById('enquiry_hoarding_count');
    
    if (countInput) {
        countInput.value = hoardingCount;
    }
    
    if (modal) {
        modal.classList.remove('hidden');
        
        // Auto-fill email and mobile from logged-in user data
        const userEmail = document.querySelector('meta[name="user-email"]')?.content || '';
        const userMobile = document.querySelector('meta[name="user-mobile"]')?.content || '';
        const userName = document.querySelector('meta[name="user-name"]')?.content || '';
        
        if (userEmail) {
            document.getElementById('enquiry_email').value = userEmail;
        }
        if (userMobile) {
            document.getElementById('enquiry_mobile').value = userMobile;
        }
        if (userName) {
            document.getElementById('enquiry_full_name').value = userName;
        }
        
        setTimeout(() => {
            initEnquiryDatePicker();
        }, 100);
        console.log('✅ Raise Enquiry Modal opened');
        console.log('📋 Selected Items:', window.enquiryState.items);
    } else {
        console.error('❌ raiseEnquiryModal element not found');
    }
}

function closeRaiseEnquiryModal() {
    const modal = document.getElementById('raiseEnquiryModal');
    if (modal) {
        modal.classList.add('hidden');
        // Reset form
        document.getElementById('raiseEnquiryForm').reset();
        console.log('✅ Raise Enquiry Modal closed');
    }
}

function verifyMobile() {
    const mobile = document.getElementById('enquiry_mobile').value;
    if (!mobile || mobile.length !== 10) {
        alert('Please enter a valid 10-digit mobile number');
        return;
    }
    alert('✅ Mobile number verified successfully!');
}

// Form submission
document.getElementById('raiseEnquiryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const submitBtn = document.getElementById('enquirySubmitBtn');
    const btnText = document.getElementById('enquiryBtnText');
    const loader = document.getElementById('enquiryLoader');

    // Show loader, hide text, disable button
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    loader.classList.remove('hidden');

    function restoreButton() {
        submitBtn.disabled = false;
        btnText.style.display = '';
        loader.classList.add('hidden');
    }

    const fullName = document.getElementById('enquiry_full_name').value.trim();
    const email = document.getElementById('enquiry_email').value.trim();
    const mobile = document.getElementById('enquiry_mobile').value.trim();
    const campaignDate = document.getElementById('enquiry_campaign_date').value.trim();

    // Validation
    if (!fullName) {
        Swal.fire({icon: 'error', title: 'Full Name Required', text: 'Please enter your full name'});
        restoreButton();
        return;
    }
    if (!email) {
        Swal.fire({icon: 'error', title: 'Email Required', text: 'Please enter your email'});
        restoreButton();
        return;
    }
    if (!mobile) {
        Swal.fire({icon: 'error', title: 'Mobile Required', text: 'Please enter your mobile number'});
        restoreButton();
        return;
    }
    if (!campaignDate) {
        Swal.fire({icon: 'error', title: 'Date Required', text: 'Please select a campaign start date'});
        restoreButton();
        return;
    }

    const selectedItems = Object.values(window.enquiryState.items);
    if (!selectedItems || selectedItems.length === 0) {
        Swal.fire({icon: 'error', title: 'No Hoardings Selected', text: 'No hoardings selected'});
        restoreButton();
        return;
    }

    const formData = {
        _token: document.querySelector('meta[name="csrf-token"]').content,
        duration_type: 'months',
        preferred_start_date: campaignDate,
        customer_name: fullName,
        customer_mobile: mobile,
        customer_email: email,
        message: document.getElementById('enquiry_description').value.trim(),
        hoarding_id: [],
        package_id: [],
        package_label: [],
        amount: [],
        months: []
    };

    selectedItems.forEach(item => {
        formData.hoarding_id.push(item.hoarding_id);
        formData.package_id.push(item.package_id || '');
        formData.package_label.push(item.package_label || 'Base Price');
        formData.amount.push(item.price);
        formData.months.push(item.months || 1);
    });

    fetch('/enquiries', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(res => {
        if (!res.ok) {
            return res.text().then(text => {
                throw new Error(`HTTP Error ${res.status}: ${text}`);
            });
        }
        return res.json();
    })
    .then(data => {
        restoreButton();
        if (data.success || data.enquiry_id) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Enquiry Submitted Successfully',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                background: '#ffffff',
                iconColor: '#14c871',
                customClass: {
                    popup: 'rounded-lg shadow-lg'
                }
            });
            setTimeout(() => {
                closeRaiseEnquiryModal();
                window.location.href = '/customer/enquiries';
            }, 1500);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'An error occurred. Please try again.',
                confirmButtonColor: '#2f5d46'
            });
        }
    })
    .catch(err => {
        restoreButton();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err.message,
            confirmButtonColor: '#2f5d46'
        });
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRaiseEnquiryModal();
    }
});

// Close modal when clicking outside
document.getElementById('raiseEnquiryModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRaiseEnquiryModal();
    }
});
</script>

<style>
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
</style>

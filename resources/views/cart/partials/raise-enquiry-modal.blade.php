<!-- RAISE AN ENQUIRY MODAL -->
<div id="raiseEnquiryModal" class="hidden fixed inset-0 backdrop-blur-sm z-50 flex items-center justify-center p-4">
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
                    type="submit"
                    class="w-full py-3 bg-[#2f5d46] text-white font-semibold rounded-md hover:bg-[#274d3b]"
                >
                    Enquire Now
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
    
    const fullName = document.getElementById('enquiry_full_name').value.trim();
    const email = document.getElementById('enquiry_email').value.trim();
    const mobile = document.getElementById('enquiry_mobile').value.trim();
    const campaignDate = document.getElementById('enquiry_campaign_date').value.trim();
    
    // Validation
    if (!fullName) {
        alert('Please enter your full name');
        return;
    }
    if (!email) {
        alert('Please enter your email');
        return;
    }
    if (!mobile) {
        alert('Please enter your mobile number');
        return;
    }
    if (!campaignDate) {
        alert('Please select a campaign start date');
        return;
    }
    
    // Collect all selected items with their details
    const selectedItems = Object.values(window.enquiryState.items);
    console.log('📋 Selected Items:', selectedItems);
    
    if (!selectedItems || selectedItems.length === 0) {
        alert('No hoardings selected');
        return;
    }
    
    // Format data for existing enquiries.store endpoint
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
    
    // Build arrays for each selected item
    selectedItems.forEach(item => {
        formData.hoarding_id.push(item.hoarding_id);
        formData.package_id.push(item.package_id || '');
        formData.package_label.push(item.package_label || 'Base Price');
        formData.amount.push(item.price);
        formData.months.push(item.months || 1);
    });
    
    console.log('📤 Final Form Data:', formData);
    console.log('🔗 Sending to: /enquiries');
    
    // Submit to backend using existing endpoint
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
        console.log('Response status:', res.status);
        console.log('Response headers:', res.headers);
        if (!res.ok) {
            return res.text().then(text => {
                throw new Error(`HTTP Error ${res.status}: ${text}`);
            });
        }
        return res.json();
    })
    .then(data => {
        console.log('✅ Response:', data);
        if (data.success || data.enquiry_id) {
            alert('✅ Enquiry submitted successfully!');
            closeRaiseEnquiryModal();
            window.location.href = '/customer/enquiries';
        } else {
            console.error('Error response:', data);
            alert('❌ Error: ' + (data.message || JSON.stringify(data)));
        }
    })
    .catch(err => {
        console.error('❌ Full Error:', err.message);
        console.error('Stack:', err.stack);
        alert('❌ Error: ' + err.message);
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

@extends('layouts.vendor')

@section('title', 'Create POS Booking')

@section('content')
<div class="px-6 py-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Main Form -->
        <div class="lg:col-span-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <!-- Header -->
                <div class="px-6 py-4 rounded-t-xl bg-blue-600 text-white">
                    <h4 class="text-lg font-semibold flex items-center gap-2">
                        ➕ Create New POS Booking
                    </h4>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <form id="pos-booking-form">
                        @csrf

                        <!-- Customer Details -->
                        <h5 class="text-md font-semibold mb-4">Customer Details</h5>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Customer Name *</label>
                                <input type="text" name="customer_name" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Phone *</label>
                                <input type="tel" name="customer_phone" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Email</label>
                                <input type="email" name="customer_email"
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">GSTIN</label>
                                <input type="text" name="customer_gstin" maxlength="15"
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-1">Address</label>
                            <textarea name="customer_address" rows="2"
                                class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200"></textarea>
                        </div>

                        <hr class="my-6">

                        <!-- Booking Details -->
                        <h5 class="text-md font-semibold mb-4">Booking Details</h5>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Booking Type *</label>
                                <select name="booking_type" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                                    <option value="ooh">OOH (Hoarding)</option>
                                    <option value="dooh">DOOH (Digital)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Select Hoarding *</label>
                                <select name="hoarding_id" id="hoarding-select" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                                    <option value="">-- Search & Select --</option>
                                </select>
                                <div id="hoarding-detail"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium mb-1">Start Date *</label>
                                <input type="date" name="start_date" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">End Date *</label>
                                <input type="date" name="end_date" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                        </div>

                        <hr class="my-6">

                        <!-- Pricing -->
                        <h5 class="text-md font-semibold mb-4">Pricing</h5>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Base Amount *</label>
                                <input type="number" step="0.01" id="base-amount" name="base_amount" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Discount Amount</label>
                                <input type="number" step="0.01" id="discount-amount" name="discount_amount" value="0"
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm mb-6">
                            <strong>Price Breakdown:</strong><br>
                            Base Amount: ₹<span id="display-base">0.00</span><br>
                            Discount: ₹<span id="display-discount">0.00</span><br>
                            After Discount: ₹<span id="display-after-discount">0.00</span><br>
                            GST (@<span id="gst-rate">18</span>%): ₹<span id="display-gst">0.00</span><br>
                            <strong>Total Amount: ₹<span id="display-total">0.00</span></strong>
                        </div>

                        <hr class="my-6">

                        <!-- Payment Details -->
                        <h5 class="text-md font-semibold mb-4">Payment Details</h5>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Payment Mode *</label>
                                <select name="payment_mode" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                                    <option value="cash">Cash</option>
                                    <option value="credit_note">Credit Note</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Payment Reference</label>
                                <input type="text" name="payment_reference"
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Payment Notes</label>
                            <textarea name="payment_notes" rows="2"
                                class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200"></textarea>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-1">Additional Notes</label>
                            <textarea name="notes" rows="2"
                                class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200"></textarea>
                        </div>

                        <!-- Error Display Container -->
                        <div id="form-error-container" class="mb-4 hidden">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <p class="text-red-700 font-semibold mb-2">⚠️ Form Errors:</p>
                                <ul id="error-list" class="text-red-600 text-sm space-y-1"></ul>
                            </div>
                        </div>

                        <!-- Success Display Container -->
                        <div id="form-success-container" class="mb-4 hidden">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <p id="success-message" class="text-green-700 font-semibold">✅ Booking created successfully!</p>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <a href="{{ route('vendor.pos.dashboard') }}"
                               class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                                Cancel
                            </a>

                            <button type="submit" id="submit-btn"
                                class="px-6 py-3 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 flex items-center gap-2">
                                <span id="submit-text">💾 Create Booking</span>
                                <span id="submit-spinner" class="hidden">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 rounded-t-xl bg-cyan-600 text-white">
                    <h5 class="font-semibold">POS Settings</h5>
                </div>

                <div class="p-6 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span>Auto-Approval</span>
                        <strong id="auto-approval-status">Loading...</strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Auto-Invoice</span>
                        <strong id="auto-invoice-status">Loading...</strong>
                    </div>
                    <div class="flex justify-between">
                        <span>GST Rate</span>
                        <strong id="gst-rate-display">18%</strong>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
const API_URL = '/api/v1/vendor/pos';
const TOKEN = localStorage.getItem('token');
let gstRate = 18; // Default, will be fetched from backend

// Fetch GST rate from backend settings
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Fetch POS settings to get actual GST rate
        const response = await fetch(`${API_URL}/settings`, {
            headers: {
                'Authorization': `Bearer ${TOKEN}`,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.data && data.data.gst_rate) {
                gstRate = parseFloat(data.data.gst_rate);
                document.getElementById('gst-rate').textContent = gstRate;
                document.getElementById('gst-rate-display').textContent = gstRate + '%';
            }
        }
    } catch (error) {
        console.warn('Could not fetch GST rate, using default:', gstRate);
    }

    // Attach event listeners for price calculation
    attachPriceCalculationListeners();
    
    // Attach form submission handler
    document.getElementById('pos-booking-form').addEventListener('submit', handleFormSubmit);
    
    // Attach hoarding selection handler
    document.getElementById('hoarding-select').addEventListener('change', handleHoardingChange);
});

/**
 * Calculate and update price preview
 * BACKEND RULE: total = (base - discount) + ((base - discount) * gst_rate / 100)
 */
function calculatePrice() {
    const baseAmount = parseFloat(document.getElementById('base-amount').value) || 0;
    const discountAmount = parseFloat(document.getElementById('discount-amount').value) || 0;

    const afterDiscount = Math.max(0, baseAmount - discountAmount);
    const gstAmount = (afterDiscount * gstRate) / 100;
    const totalAmount = afterDiscount + gstAmount;

    // Update display
    document.getElementById('display-base').textContent = baseAmount.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    document.getElementById('display-discount').textContent = discountAmount.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    document.getElementById('display-after-discount').textContent = afterDiscount.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    document.getElementById('display-gst').textContent = gstAmount.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    document.getElementById('display-total').textContent = totalAmount.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Attach event listeners for price calculation
 */
function attachPriceCalculationListeners() {
    document.getElementById('base-amount').addEventListener('input', calculatePrice);
    document.getElementById('discount-amount').addEventListener('input', calculatePrice);
    
    // Initial calculation
    calculatePrice();
}

/**
 * Handle hoarding selection - fetch hoarding details
 */
async function handleHoardingChange(event) {
    const hoardingId = event.target.value;
    
    if (!hoardingId) {
        document.getElementById('hoarding-detail').innerHTML = '';
        return;
    }

    try {
        const response = await fetch(`/api/v1/hoardings/${hoardingId}`, {
            headers: {
                'Authorization': `Bearer ${TOKEN}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            const h = data.data;
            
            // Create hoarding preview (will add this div to form)
            if (!document.getElementById('hoarding-detail')) {
                const detailDiv = document.createElement('div');
                detailDiv.id = 'hoarding-detail';
                document.getElementById('hoarding-select').parentElement.appendChild(detailDiv);
            }
            
            document.getElementById('hoarding-detail').innerHTML = `
                <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
                    <strong>${h.title}</strong><br>
                    Location: ${h.location_address}<br>
                    Size: ${h.size}<br>
                    Type: ${h.type}
                </div>
            `;
        }
    } catch (error) {
        console.warn('Could not fetch hoarding details:', error);
    }
}

/**
 * Handle form submission
 * BACKEND RULES: 
 * - POST /bookings with validation
 * - Returns 422 with field errors if validation fails
 * - Returns 201 with booking data if successful
 */
async function handleFormSubmit(event) {
    event.preventDefault();
    
    // Check token
    if (!TOKEN) {
        showError(['Session expired. Please log in again.']);
        return;
    }

    // Clear previous errors and success
    clearMessages();
    
    // Show loading state
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    document.getElementById('submit-text').classList.add('hidden');
    document.getElementById('submit-spinner').classList.remove('hidden');

    // Collect form data
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);

    try {
        const response = await fetch(`${API_URL}/bookings`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${TOKEN}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        if (response.status === 422) {
            // Validation error
            const errorData = await response.json();
            
            if (errorData.errors) {
                const errorMessages = Object.entries(errorData.errors).map(([field, messages]) => {
                    return `<strong>${field}:</strong> ${messages.join(', ')}`;
                });
                showError(errorMessages);
                
                // Highlight error fields
                Object.keys(errorData.errors).forEach(field => {
                    const fieldElement = document.querySelector(`[name="${field}"]`);
                    if (fieldElement) {
                        fieldElement.classList.add('border-red-500', 'border-2');
                    }
                });
            }
        } else if (response.status === 401) {
            // Unauthorized
            showError(['Session expired. Please log in again.']);
            setTimeout(() => window.location.href = '/login', 2000);
        } else if (response.status === 403) {
            // Forbidden
            showError(['You do not have permission to create bookings.']);
        } else if (response.ok || response.status === 201) {
            // Success
            const successData = await response.json();
            showSuccess(`Booking #${successData.data.invoice_number || successData.data.id} created successfully!`);
            
            // Redirect after 2 seconds
            setTimeout(() => {
                window.location.href = `/vendor/pos/bookings/${successData.data.id}`;
            }, 2000);
        } else {
            // Server error
            const errorData = await response.json();
            showError([errorData.message || 'An error occurred while creating the booking.']);
        }
    } catch (error) {
        console.error('Form submission error:', error);
        showError(['Network error. Please check your connection and try again.']);
    } finally {
        // Reset loading state
        submitBtn.disabled = false;
        document.getElementById('submit-text').classList.remove('hidden');
        document.getElementById('submit-spinner').classList.add('hidden');
    }
}

/**
 * Display error messages
 */
function showError(messages) {
    const container = document.getElementById('form-error-container');
    const errorList = document.getElementById('error-list');
    
    errorList.innerHTML = messages.map(msg => `<li>${msg}</li>`).join('');
    container.classList.remove('hidden');
    
    // Scroll to error
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Display success message
 */
function showSuccess(message) {
    const container = document.getElementById('form-success-container');
    document.getElementById('success-message').textContent = `✅ ${message}`;
    container.classList.remove('hidden');
    
    // Scroll to success
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Clear error and success messages
 */
function clearMessages() {
    document.getElementById('form-error-container').classList.add('hidden');
    document.getElementById('form-success-container').classList.add('hidden');
    
    // Remove error highlights from fields
    document.querySelectorAll('input, select, textarea').forEach(field => {
        field.classList.remove('border-red-500', 'border-2');
    });
}

/**
 * Update payment mode hint based on selection
 */
document.addEventListener('DOMContentLoaded', () => {
    const paymentModeSelect = document.querySelector('select[name="payment_mode"]');
    
    if (paymentModeSelect) {
        paymentModeSelect.addEventListener('change', function() {
            let hint = '';
            
            // BACKEND RULES: Different payment modes have different hold behaviors
            if (['cash', 'bank_transfer', 'cheque', 'online'].includes(this.value)) {
                hint = '⏰ Hold will expire in 7 days if payment is not received.';
            } else if (this.value === 'credit_note') {
                hint = '📄 Credit note - no payment hold. Validity will be set based on configuration.';
            }
            
            if (hint) {
                let hintDiv = document.getElementById('payment-mode-hint');
                if (!hintDiv) {
                    hintDiv = document.createElement('div');
                    hintDiv.id = 'payment-mode-hint';
                    paymentModeSelect.parentElement.appendChild(hintDiv);
                }
                hintDiv.innerHTML = `<p class="mt-1 text-xs text-blue-600">${hint}</p>`;
            }
        });
    }
});
</script>
@endsection

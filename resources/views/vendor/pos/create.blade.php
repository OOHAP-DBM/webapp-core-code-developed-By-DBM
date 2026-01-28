
@extends('layouts.vendor')

@section('title', 'Create POS Booking')

@section('content')
<div class="px-6 py-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- LEFT: Main Form -->
        <div class="lg:col-span-7">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 rounded-t-xl bg-blue-600 text-white">
                    <h4 class="text-lg font-semibold flex items-center gap-2">
                        ➕ Create New POS Booking
                    </h4>
                </div>
                <div class="p-6">
                    <form id="pos-booking-form" autocomplete="off">
                        @csrf
                        <!-- Customer Selection -->
                        <h5 class="text-md font-semibold mb-4">Customer</h5>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Search or Add Customer *</label>
                            <input type="text" id="customer-search" name="customer_search" placeholder="Type name, phone, or email..." autocomplete="off"
                                class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200" required>
                            <div id="customer-suggestions" class="bg-white border border-gray-200 rounded-lg mt-1 shadow-lg hidden absolute z-50"></div>
                        </div>
                        <div id="customer-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Name *</label>
                                <input type="text" name="customer_name" id="customer_name" required class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Phone *</label>
                                <input type="tel" name="customer_phone" id="customer_phone" required class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Email</label>
                                <input type="email" name="customer_email" id="customer_email" class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">GSTIN</label>
                                <input type="text" name="customer_gstin" id="customer_gstin" maxlength="15" class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-1">Address</label>
                                <textarea name="customer_address" id="customer_address" rows="2" class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200"></textarea>
                            </div>
                        </div>
                        <hr class="my-6">
                        <!-- Booking Details -->
                        <h5 class="text-md font-semibold mb-4">Booking Details</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Booking Type *</label>
                                <select name="booking_type" id="booking_type" required class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                                    <option value="ooh">OOH (Hoarding)</option>
                                    <option value="dooh">DOOH (Digital)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Selected Hoarding *</label>
                                <input type="hidden" name="hoarding_id" id="hoarding_id" required>
                                <div id="selected-hoarding-preview" class="mt-1"></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium mb-1">Start Date *</label>
                                <input type="date" name="start_date" required class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">End Date *</label>
                                <input type="date" name="end_date" required class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                        </div>
                        <hr class="my-6">
                        <!-- Pricing -->
                        <h5 class="text-md font-semibold mb-4">Pricing</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Base Amount *</label>
                                <input type="number" step="0.01" id="base-amount" name="base_amount" required class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Discount Amount</label>
                                <input type="number" step="0.01" id="discount-amount" name="discount_amount" value="0" class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
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
                                <select name="payment_mode" required class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                                    <option value="cash">Cash</option>
                                    <option value="credit_note">Credit Note</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Payment Reference</label>
                                <input type="text" name="payment_reference" class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Payment Notes</label>
                            <textarea name="payment_notes" rows="2" class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200"></textarea>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-1">Additional Notes</label>
                            <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200"></textarea>
                        </div>
                        <div id="form-error-container" class="mb-4 hidden">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <p class="text-red-700 font-semibold mb-2">⚠️ Form Errors:</p>
                                <ul id="error-list" class="text-red-600 text-sm space-y-1"></ul>
                            </div>
                        </div>
                        <div id="form-success-container" class="mb-4 hidden">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <p id="success-message" class="text-green-700 font-semibold">✅ Booking created successfully!</p>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <a href="/vendor/pos/bookings" class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">Cancel</a>
                            <button type="submit" id="submit-btn" class="px-6 py-3 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 flex items-center gap-2">
                                <span id="submit-text">💾 Create Booking</span>
                                <span id="submit-spinner" class="hidden"><svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- RIGHT: Hoardings Browser -->
        <div class="lg:col-span-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 rounded-t-xl bg-cyan-600 text-white flex items-center justify-between">
                    <h5 class="font-semibold">Browse Hoardings</h5>
                    <input type="text" id="hoarding-search" placeholder="Search hoardings..." class="rounded-lg border border-gray-300 px-2 py-1 text-sm text-black">
                </div>
                <div class="p-6">
                    <div id="hoardings-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-4"></div>
                    <div id="hoardings-empty" class="text-center text-gray-500 mt-4 hidden">No hoardings found.</div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- <script>
    const API_URL = '/api/v1/vendor/pos';
    const TOKEN = localStorage.getItem('token');
    consi
    let gstRate = 18;
    let hoardings = [];
    let selectedHoarding = null;
    let selectedCustomer = null;

    document.addEventListener('DOMContentLoaded', async () => {
        // Fetch GST rate
        try {
            const response = await fetch(`${API_URL}/settings`, { headers: { 'Authorization': `Bearer ${TOKEN}`, 'Accept': 'application/json' } });
            if (response.ok) {
                const data = await response.json();
                if (data.data && data.data.gst_rate) {
                    gstRate = parseFloat(data.data.gst_rate);
                    document.getElementById('gst-rate').textContent = gstRate;
                }
            }
        } catch {}
        // Load hoardings
        await loadHoardings();
        // Attach listeners
        attachPriceCalculationListeners();
        document.getElementById('pos-booking-form').addEventListener('submit', handleFormSubmit);
        document.getElementById('hoarding-search').addEventListener('input', filterHoardings);
        document.getElementById('customer-search').addEventListener('input', handleCustomerSearch);
    });

    async function loadHoardings() {
        const grid = document.getElementById('hoardings-grid');
        grid.innerHTML = '<div class="col-span-full text-center text-gray-400">Loading hoardings...</div>';
        try {
            const res = await fetch('/api/v1/vendor/hoardings', { headers: { 'Authorization': `Bearer ${TOKEN}`, 'Accept': 'application/json' } });
            const data = await res.json();
            hoardings = data.data || [];
            renderHoardings(hoardings);
        } catch {
            grid.innerHTML = '<div class="col-span-full text-center text-red-500">Failed to load hoardings.</div>';
        }
    }

    function renderHoardings(list) {
        const grid = document.getElementById('hoardings-grid');
        const empty = document.getElementById('hoardings-empty');
        grid.innerHTML = '';
        if (!list.length) {
            empty.classList.remove('hidden');
            return;
        }
        empty.classList.add('hidden');
        list.forEach(h => {
            const card = document.createElement('div');
            card.className = `rounded-lg border p-3 cursor-pointer transition shadow-sm ${selectedHoarding && selectedHoarding.id === h.id ? 'ring-2 ring-blue-500 border-blue-500 bg-blue-50' : 'hover:shadow-md'}`;
            card.innerHTML = `
                <img src="${h.image_url || '/images/hoarding-placeholder.png'}" alt="Hoarding" class="w-full h-32 object-cover rounded mb-2">
                <div class="font-semibold text-base mb-1">${h.title}</div>
                <div class="text-xs text-gray-600 mb-1">${h.location_address}</div>
                <div class="text-xs text-gray-500 mb-1">Size: ${h.size} | Type: ${h.type}</div>
                <div class="text-sm font-bold text-blue-700">₹${parseFloat(h.price_per_month || 0).toLocaleString('en-IN')}</div>
            `;
            card.onclick = () => selectHoarding(h);
            grid.appendChild(card);
        });
    }

    function selectHoarding(h) {
        selectedHoarding = h;
        document.getElementById('hoarding_id').value = h.id;
        renderHoardings(hoardings);
        document.getElementById('selected-hoarding-preview').innerHTML = `
            <div class="p-2 border rounded bg-blue-50 mt-1">
                <div class="font-semibold">${h.title}</div>
                <div class="text-xs text-gray-600">${h.location_address}</div>
                <div class="text-xs text-gray-500">Size: ${h.size} | Type: ${h.type}</div>
                <div class="text-sm font-bold text-blue-700">₹${parseFloat(h.price_per_month || 0).toLocaleString('en-IN')}</div>
            </div>
        `;
        // Optionally update base price
        if (h.price_per_month) {
            document.getElementById('base-amount').value = h.price_per_month;
            calculatePrice();
        }
    }

    function filterHoardings(e) {
        const q = e.target.value.toLowerCase();
        renderHoardings(hoardings.filter(h => h.title.toLowerCase().includes(q) || (h.location_address && h.location_address.toLowerCase().includes(q))));
    }

    // Customer Autocomplete
    let customerSearchTimeout = null;
    function handleCustomerSearch(e) {
        const q = e.target.value.trim();
        const suggestions = document.getElementById('customer-suggestions');
        if (customerSearchTimeout) clearTimeout(customerSearchTimeout);
        if (!q) {
            suggestions.classList.add('hidden');
            return;
        }
        customerSearchTimeout = setTimeout(async () => {
            try {
                const res = await fetch(`/api/v1/vendor/customers?search=${encodeURIComponent(q)}`, { headers: { 'Authorization': `Bearer ${TOKEN}`, 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.data && data.data.length) {
                    suggestions.innerHTML = data.data.map(c => `<div class="px-3 py-2 hover:bg-blue-100 cursor-pointer" onclick="selectCustomer(${encodeURIComponent(JSON.stringify(c))})">${c.name} <span class='text-xs text-gray-500'>${c.phone} ${c.email ? '· ' + c.email : ''}</span></div>`).join('');
                    suggestions.classList.remove('hidden');
                } else {
                    suggestions.innerHTML = '<div class="px-3 py-2 text-gray-400">No customers found</div>';
                    suggestions.classList.remove('hidden');
                }
            } catch {
                suggestions.innerHTML = '<div class="px-3 py-2 text-red-500">Error searching customers</div>';
                suggestions.classList.remove('hidden');
            }
        }, 300);
    }
    function selectCustomer(raw) {
        const c = typeof raw === 'string' ? JSON.parse(decodeURIComponent(raw)) : raw;
        document.getElementById('customer_name').value = c.name || '';
        document.getElementById('customer_phone').value = c.phone || '';
        document.getElementById('customer_email').value = c.email || '';
        document.getElementById('customer_gstin').value = c.gstin || '';
        document.getElementById('customer_address').value = c.address || '';
        document.getElementById('customer-suggestions').classList.add('hidden');
        selectedCustomer = c;
    }

    function calculatePrice() {
        const baseAmount = parseFloat(document.getElementById('base-amount').value) || 0;
        const discountAmount = parseFloat(document.getElementById('discount-amount').value) || 0;
        const afterDiscount = Math.max(0, baseAmount - discountAmount);
        const gstAmount = (afterDiscount * gstRate) / 100;
        const totalAmount = afterDiscount + gstAmount;
        document.getElementById('display-base').textContent = baseAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('display-discount').textContent = discountAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('display-after-discount').textContent = afterDiscount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('display-gst').textContent = gstAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('display-total').textContent = totalAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function attachPriceCalculationListeners() {
        document.getElementById('base-amount').addEventListener('input', calculatePrice);
        document.getElementById('discount-amount').addEventListener('input', calculatePrice);
        calculatePrice();
    }

    async function handleFormSubmit(event) {
        event.preventDefault();
        if (!TOKEN) {
            showError(['Session expired. Please log in again.']);
            return;
        }
        clearMessages();
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        document.getElementById('submit-text').classList.add('hidden');
        document.getElementById('submit-spinner').classList.remove('hidden');
        const formData = new FormData(event.target);
        // If customer selected, add customer_id
        if (selectedCustomer && selectedCustomer.id) {
            formData.append('customer_id', selectedCustomer.id);
        }
        const data = Object.fromEntries(formData);
        try {
            const response = await fetch(`${API_URL}/bookings`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${TOKEN}`, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (response.status === 422) {
                const errorData = await response.json();
                if (errorData.errors) {
                    const errorMessages = Object.entries(errorData.errors).map(([field, messages]) => `<strong>${field}:</strong> ${messages.join(', ')}`);
                    showError(errorMessages);
                    Object.keys(errorData.errors).forEach(field => {
                        const fieldElement = document.querySelector(`[name="${field}"]`);
                        if (fieldElement) fieldElement.classList.add('border-red-500', 'border-2');
                    });
                }
            } else if (response.status === 401) {
                showError(['Session expired. Please log in again.']);
                setTimeout(() => window.location.href = '/login', 2000);
            } else if (response.status === 403) {
                showError(['You do not have permission to create bookings.']);
            } else if (response.ok || response.status === 201) {
                const successData = await response.json();
                showSuccess(`Booking #${successData.data.invoice_number || successData.data.id} created successfully!`);
                setTimeout(() => { window.location.href = `/vendor/pos/bookings/${successData.data.id}`; }, 2000);
            } else {
                const errorData = await response.json();
                showError([errorData.message || 'An error occurred while creating the booking.']);
            }
        } catch (error) {
            showError(['Network error. Please check your connection and try again.']);
        } finally {
            submitBtn.disabled = false;
            document.getElementById('submit-text').classList.remove('hidden');
            document.getElementById('submit-spinner').classList.add('hidden');
        }
    }
    function showError(messages) {
        const container = document.getElementById('form-error-container');
        const errorList = document.getElementById('error-list');
        errorList.innerHTML = messages.map(msg => `<li>${msg}</li>`).join('');
        container.classList.remove('hidden');
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function showSuccess(message) {
        const container = document.getElementById('form-success-container');
        document.getElementById('success-message').textContent = `✅ ${message}`;
        container.classList.remove('hidden');
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function clearMessages() {
        document.getElementById('form-error-container').classList.add('hidden');
        document.getElementById('form-success-container').classList.add('hidden');
        document.querySelectorAll('input, select, textarea').forEach(field => { field.classList.remove('border-red-500', 'border-2'); });
    }
</script> -->
<script>
/**
 * Web POS – Session Auth (Laravel)
 * No tokens, no localStorage, no Bearer headers
 */

const API_URL = '/api/v1/vendor/pos';

let gstRate = 18;
let hoardings = [];
let selectedHoarding = null;
let selectedCustomer = null;

/* -------------------------------------------------------
   Helpers
------------------------------------------------------- */

const fetchJSON = async (url, options = {}) => {
    const res = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {})
        },
        ...options
    });

    if (!res.ok) {
        const data = await res.json().catch(() => ({}));
        throw { status: res.status, data };
    }

    return res.json();
};

const normalizeList = (response) => {
    if (Array.isArray(response.data)) return response.data;
    if (Array.isArray(response.data?.data)) return response.data.data;
    return [];
};

/* -------------------------------------------------------
   Init
------------------------------------------------------- */

document.addEventListener('DOMContentLoaded', async () => {
    await loadSettings();
    await loadHoardings();

    attachPriceCalculationListeners();

    document.getElementById('pos-booking-form')
        .addEventListener('submit', handleFormSubmit);

    document.getElementById('hoarding-search')
        .addEventListener('input', filterHoardings);

    document.getElementById('customer-search')
        .addEventListener('input', handleCustomerSearch);
});

/* -------------------------------------------------------
   Settings
------------------------------------------------------- */

async function loadSettings() {
    try {
        const res = await fetchJSON(`${API_URL}/settings`);
        if (res.data?.gst_rate) {
            gstRate = parseFloat(res.data.gst_rate);
            document.getElementById('gst-rate').textContent = gstRate;
        }
    } catch (_) {}
}

/* -------------------------------------------------------
   Hoardings
------------------------------------------------------- */

async function loadHoardings() {
    const grid = document.getElementById('hoardings-grid');
    grid.innerHTML = `<div class="col-span-full text-center text-gray-400">Loading hoardings...</div>`;

    try {
        const res = await fetchJSON(`${API_URL}/hoardings`);
        hoardings = normalizeList(res);
        renderHoardings(hoardings);
    } catch {
        grid.innerHTML = `<div class="col-span-full text-center text-red-500">Failed to load hoardings</div>`;
    }
}

function renderHoardings(list) {
    const grid = document.getElementById('hoardings-grid');
    const empty = document.getElementById('hoardings-empty');

    grid.innerHTML = '';

    if (!list.length) {
        empty.classList.remove('hidden');
        return;
    }

    empty.classList.add('hidden');

    list.forEach(h => {
        const card = document.createElement('div');
        card.className = `
            rounded-lg border p-3 cursor-pointer transition
            ${selectedHoarding?.id === h.id
                ? 'ring-2 ring-blue-500 border-blue-500 bg-blue-50'
                : 'hover:shadow-md'}
        `;

        card.innerHTML = `
            <img src="${h.image_url || '/images/hoarding-placeholder.png'}"
                 class="w-full h-32 object-cover rounded mb-2">

            <div class="font-semibold">${h.title}</div>
            <div class="text-xs text-gray-600">${h.location_address || ''}</div>
            <div class="text-xs text-gray-500">Size: ${h.size} | Type: ${h.type}</div>
            <div class="text-sm font-bold text-blue-700">
                ₹${Number(h.price_per_month || 0).toLocaleString('en-IN')}
            </div>
        `;

        card.onclick = () => selectHoarding(h);
        grid.appendChild(card);
    });
}

function selectHoarding(h) {
    selectedHoarding = h;
    document.getElementById('hoarding_id').value = h.id;

    renderHoardings(hoardings);

    document.getElementById('selected-hoarding-preview').innerHTML = `
        <div class="p-2 border rounded bg-blue-50">
            <div class="font-semibold">${h.title}</div>
            <div class="text-xs">${h.location_address || ''}</div>
            <div class="text-xs">Size: ${h.size} | Type: ${h.type}</div>
        </div>
    `;

    if (h.price_per_month) {
        document.getElementById('base-amount').value = h.price_per_month;
        calculatePrice();
    }
}

function filterHoardings(e) {
    const q = e.target.value.toLowerCase();
    renderHoardings(
        hoardings.filter(h =>
            h.title.toLowerCase().includes(q) ||
            (h.location_address || '').toLowerCase().includes(q)
        )
    );
}

/* -------------------------------------------------------
   Customers
------------------------------------------------------- */

let customerSearchTimeout = null;

function handleCustomerSearch(e) {
    const q = e.target.value.trim();
    const box = document.getElementById('customer-suggestions');

    clearTimeout(customerSearchTimeout);

    if (!q) {
        box.classList.add('hidden');
        return;
    }

    customerSearchTimeout = setTimeout(async () => {
        try {
            const res = await fetchJSON(
                `${API_URL}/customers?search=${encodeURIComponent(q)}`
            );

            const customers = normalizeList(res);

            box.innerHTML = customers.length
                ? customers.map(c => `
                    <div class="px-3 py-2 hover:bg-blue-100 cursor-pointer"
                         onclick='selectCustomer(${JSON.stringify(c)})'>
                        ${c.name}
                        <span class="text-xs text-gray-500">
                            ${c.phone} ${c.email ? '· ' + c.email : ''}
                        </span>
                    </div>
                `).join('')
                : `<div class="px-3 py-2 text-gray-400">No customers found</div>`;

            box.classList.remove('hidden');
        } catch {
            box.innerHTML = `<div class="px-3 py-2 text-red-500">Error loading customers</div>`;
            box.classList.remove('hidden');
        }
    }, 300);
}

function selectCustomer(c) {
    selectedCustomer = c;

    document.getElementById('customer_name').value = c.name || '';
    document.getElementById('customer_phone').value = c.phone || '';
    document.getElementById('customer_email').value = c.email || '';
    document.getElementById('customer_gstin').value = c.gstin || '';
    document.getElementById('customer_address').value = c.address || '';

    document.getElementById('customer-suggestions').classList.add('hidden');
}

/* -------------------------------------------------------
   Pricing
------------------------------------------------------- */

function calculatePrice() {
    const base = +document.getElementById('base-amount').value || 0;
    const discount = +document.getElementById('discount-amount').value || 0;

    const after = Math.max(0, base - discount);
    const gst = (after * gstRate) / 100;
    const total = after + gst;

    document.getElementById('display-base').textContent = base.toFixed(2);
    document.getElementById('display-discount').textContent = discount.toFixed(2);
    document.getElementById('display-after-discount').textContent = after.toFixed(2);
    document.getElementById('display-gst').textContent = gst.toFixed(2);
    document.getElementById('display-total').textContent = total.toFixed(2);
}

function attachPriceCalculationListeners() {
    ['base-amount', 'discount-amount'].forEach(id =>
        document.getElementById(id).addEventListener('input', calculatePrice)
    );
    calculatePrice();
}

/* -------------------------------------------------------
   Submit
------------------------------------------------------- */

async function handleFormSubmit(e) {
    e.preventDefault();
    clearMessages();

    const btn = document.getElementById('submit-btn');
    btn.disabled = true;

    const formData = Object.fromEntries(new FormData(e.target));

    if (selectedCustomer?.id) {
        formData.customer_id = selectedCustomer.id;
    }

    try {
        const res = await fetchJSON(`${API_URL}/bookings`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        showSuccess(`Booking #${res.data.invoice_number || res.data.id} created`);
        setTimeout(() => location.href = `/vendor/pos/bookings/${res.data.id}`, 1500);

    } catch (err) {
        if (err.status === 422) {
            showError(Object.entries(err.data.errors)
                .map(([k, v]) => `<strong>${k}:</strong> ${v.join(', ')}`));
        } else {
            showError(['Something went wrong. Please try again.']);
        }
    } finally {
        btn.disabled = false;
    }
}

/* -------------------------------------------------------
   UI Messages
------------------------------------------------------- */

function showError(messages) {
    const box = document.getElementById('form-error-container');
    document.getElementById('error-list').innerHTML =
        messages.map(m => `<li>${m}</li>`).join('');
    box.classList.remove('hidden');
}

function showSuccess(message) {
    const box = document.getElementById('form-success-container');
    document.getElementById('success-message').textContent = `✅ ${message}`;
    box.classList.remove('hidden');
}

function clearMessages() {
    document.getElementById('form-error-container').classList.add('hidden');
    document.getElementById('form-success-container').classList.add('hidden');
}
</script>

@endsection

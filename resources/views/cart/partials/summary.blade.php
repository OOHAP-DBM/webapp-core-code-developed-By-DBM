<div class="sticky top-24 bg-white border border-gray-200 rounded-xl p-5">

    {{-- PRICE BREAKDOWN --}}
    <div class="space-y-3 text-sm">
        <div class="flex justify-between">
            <span>Subtotal</span>
            <span id="summary-subtotal">₹0</span>
        </div>

        <div class="flex justify-between text-green-600">
            <span>Discount</span>
            <span id="summary-discount">-₹0</span>
        </div>
    </div>

    {{-- COUPON --}}
    <!-- <div class="mt-4">
        <input
            type="text"
            id="coupon-code"
            placeholder="Enter coupon code"
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"
        >
        <button
            id="apply-coupon"
            class="mt-2 w-full border border-gray-200 rounded-lg py-2 text-sm"
        >
            Apply
        </button>
    </div> -->

    {{-- TOTAL --}}
    <div class="border-t border-gray-200 mt-4 pt-4 flex justify-between font-semibold">
        <span>Total</span>
        <span id="summary-total">₹0</span>
    </div>

    <center>
        <button 
            id="enquireNowBtn"
            class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white py-2.5 px-4 rounded-lg font-semibold transition-colors"
            >
                Enquire Now
        </button>
    </center>

</div>

<div id="enquiryModal"
     class="hidden fixed inset-0 bg-black/50 z-[9999]
            flex justify-center items-start
            overflow-y-auto
            px-4 sm:px-6
            overflow-x-hidden mt-10">


<div class="bg-white
            w-full
            max-w-[calc(100vw-2rem)] sm:max-w-6xl
            my-8 sm:my-16
            border border-gray-200
            shadow-lg
            overflow-hidden">


        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold">Selected Hoardings</h2>
            <button onclick="closeEnquiryModal()" class="text-gray-500 hover:text-black text-lg">✕</button>
        </div>

        <!-- Table -->
        <div class="p-6 max-h-[70vh] overflow-y-auto">
            <table class="w-full text-sm border-collapse">
                 <thead class="bg-gray-50">
                    <tr class="text-left text-xs py-3 px-4 font-semibold text-gray-600 uppercase tracking-wide">

                        <th class="px-4 py-3">Hoarding</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Duration</th>
                        <th class="px-4 py-3">Select Package</th>
                        <th class="px-4 py-3">Rental</th>
                    </tr>
                </thead>
                <tbody id="enquiryTableBody">
                    <!-- JS se rows aayengi -->
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="flex justify-between items-center bg-green-100 px-6 py-4">
            <p class="font-semibold">
                Total Amount: ₹<span id="enquiryTotal">0</span>
            </p>

            <button onclick="openRaiseEnquiryModal(Object.keys(window.enquiryState.items).length); closeEnquiryModal();"
                    class="bg-green-700 text-white px-6 py-2 rounded">
                Raise an Enquiry
            </button>

        </div>

    </div>
</div>
@include('cart.partials.enquiry-success-modal')
<script>
window.enquiryState = {
    items: {} // hoarding_id => { package_id, package_label, price, months }
};
</script>
<script>
/* =====================================================
 | ENTRY POINT
 ===================================================== */
document.addEventListener('DOMContentLoaded', function () {

    const enquireBtn = document.getElementById('enquireNowBtn');

    if (enquireBtn) {
        enquireBtn.addEventListener('click', function () {
            openCartEnquiryModal();
            loadEnquiryHoardings();
        });
    }

});


/* =====================================================
 | MODAL CONTROLS - CART SPECIFIC
 ===================================================== */
function openCartEnquiryModal() {
    const modal = document.getElementById('enquiryModal');
    console.log('[DEBUG] openCartEnquiryModal called');
    console.log('[DEBUG] Modal element:', modal);
    
    if (modal) {
        modal.classList.remove('hidden');
        // Force display to ensure visibility
        modal.style.display = 'flex !important';
        console.log('[DEBUG] ✅ Cart modal opened successfully');
        console.log('[DEBUG] Modal classes:', modal.className);
    } else {
        console.error('[ERROR] enquiryModal element not found!');
    }
}

function closeEnquiryModal() {
    const modal = document.getElementById('enquiryModal');
    console.log('[DEBUG] closeEnquiryModal called');
    
    if (modal) {
        modal.classList.add('hidden');
        console.log('[DEBUG] ✅ Modal closed');
    }
}



/* =====================================================
 | LOAD CART → ENQUIRY MODAL
 ===================================================== */
function loadEnquiryHoardings() {
    console.log('[DEBUG] loadEnquiryHoardings called');
    
    fetch('/enquiry/shortlisted')
        .then(response => {
            console.log('[DEBUG] Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(items => {
            console.log('[DEBUG] Items received:', items);

            let tableHTML = '';
            let totalAmount = 0;

            // 🔥 RESET STATE (VERY IMPORTANT)
            window.enquiryState.items = {};

            items.forEach(item => {

                /* ===============================
                 | 1. PRICING LOGIC (OOH vs DOOH)
                 =============================== */
                const hoardingId = item.hoarding_id;
                const hoardingType = item.hoarding_type;
                const selectedPkg = item.selected_package;

                let displayPrice, months, packageId, packageLabel, priceLabel;

                // =========== OOH LOGIC ===========
                if (hoardingType === 'ooh') {
                    if (!selectedPkg) {
                        // NO PACKAGE: Use monthly_price
                        displayPrice = item.monthly_price > 0 ? item.monthly_price : item.base_monthly_price;
                        months = 1;
                        packageId = null;
                        packageLabel = 'Base';
                        priceLabel = '/ Month';
                    } else {
                        // WITH PACKAGE: Use base_monthly_price
                        const basePrice = item.base_monthly_price || 0;
                        months = selectedPkg.min_booking_duration || 1;
                        const totalPrice = basePrice * months;
                        const discountPercent = selectedPkg.discount_percent || 0;
                        displayPrice = discountPercent > 0 
                            ? totalPrice - (totalPrice * discountPercent / 100)
                            : totalPrice;
                        displayPrice = Math.round(displayPrice);
                        packageId = selectedPkg.id;
                        packageLabel = selectedPkg.package_name;
                        priceLabel = '/ Month';
                    }
                }
                // =========== DOOH LOGIC ===========
                else if (hoardingType === 'dooh') {
                    if (!selectedPkg) {
                        // NO PACKAGE: Use price_per_slot
                        displayPrice = item.price_per_slot || item.slot_price || 0;
                        months = 1;
                        packageId = null;
                        packageLabel = 'Base';
                        priceLabel = 'per  second ';
                    } else {
                        // WITH PACKAGE: Use package monthly logic
                        const basePrice = item.base_monthly_price || item.slot_price || 0;
                        months = selectedPkg.min_booking_duration || 1;
                        const discountPercent = selectedPkg.discount_percent || 0;
                        displayPrice = discountPercent > 0 
                            ? basePrice - (basePrice * discountPercent / 100)
                            : basePrice;
                        displayPrice = Math.round(displayPrice);
                        packageId = selectedPkg.id;
                        packageLabel = selectedPkg.package_name;
                        priceLabel = '/ Month';
                    }
                }

                /* ===============================
                 | 2. STATE FILL (SOURCE OF TRUTH)
                 =============================== */
                window.enquiryState.items[hoardingId] = {
                    hoarding_id: hoardingId,
                    hoarding_type: hoardingType,
                    package_id: packageId,
                    package_label: packageLabel,
                    price: displayPrice,
                    months: months,
                    price_label: priceLabel
                };

                /* ===============================
                 | 3. UI CALCULATION
                 =============================== */
                const rowTotal = displayPrice;
                totalAmount += rowTotal;

                /* ===============================
                 | 3. PACKAGE DROPDOWN
                 =============================== */
                let packageHTML = '—';

                // Calculate base price for both OOH and DOOH
                let basePrice = 0;
                if (hoardingType === 'ooh') {
                    basePrice = Math.round(item.monthly_price || item.base_monthly_price || 0);
                } else if (hoardingType === 'dooh') {
                    basePrice = Math.round(item.price_per_slot || item.slot_price || 0);
                }

                // ALWAYS SHOW BASE PRICE OPTION + PACKAGES (if available)
                packageHTML = `
                    <select class="border border-gray-200 px-2 py-1 rounded w-full"
                            onchange="onPackageChange(this, ${hoardingId}, '${hoardingType}')">
                        <option value=""
                            data-price="${basePrice}"
                            data-months="1"
                            data-discount="0"
                            ${!selectedPkg ? 'selected' : ''}>
                            Price (₹${basePrice})
                        </option>
                        ${item.packages?.length ? item.packages.map(pkg => {

                                let pkgPrice, pkgMonths = pkg.min_booking_duration ?? 1;

                                // OOH: Calculate price based on base_monthly_price * duration - discount
                                if (hoardingType === 'ooh') {
                                    const basePriceCalc = item.base_monthly_price || 0;
                                    const totalPrice = basePriceCalc * pkgMonths;
                                    const discountPercent = pkg.discount_percent || 0;
                                    pkgPrice = discountPercent > 0 
                                        ? totalPrice - (totalPrice * discountPercent / 100)
                                        : totalPrice;
                                    pkgPrice = Math.round(pkgPrice);
                                }
                                // DOOH: Calculate price based on base_monthly_price with discount
                                else if (hoardingType === 'dooh') {
                                    const basePriceCalc = item.base_monthly_price || item.slot_price || 0;
                                    const discountPercent = pkg.discount_percent || 0;
                                    pkgPrice = discountPercent > 0 
                                        ? basePriceCalc - (basePriceCalc * discountPercent / 100)
                                        : basePriceCalc;
                                    pkgPrice = Math.round(pkgPrice);
                                }

                                return `
                                    <option value="${pkg.id}"
                                        data-price="${pkgPrice}"
                                        data-months="${pkgMonths}"
                                        data-discount="${pkg.discount_percent || 0}"
                                        ${selectedPkg && Number(pkg.id) === Number(selectedPkg.id) ? 'selected' : ''}>
                                        offer- ${pkg.discount_percent || 0}% / ${pkgMonths} months
                                    </option>
                                `;
                            }).join('') : ''}
                    </select>
                `;

                /* ===============================
                 | 4. ROW HTML
                 =============================== */
                tableHTML += `
                    <tr data-hoarding-id="${hoardingId}" class="border-b border-gray-200 align-middle">
                        <td class="py-4 px-4 font-medium">${item.title}</td>
                        <td class="py-4 px-4 text-gray-600">
                            ${(item.locality ?? '')}${item.state ? ', ' + item.state : ''}
                        </td>
                        <td class="py-4 px-4">
                            <select class="border border-gray-200 px-2 py-1 rounded w-full" onchange="onDurationChange(this, ${hoardingId})">
                                <option value="1" data-months="1">1 Month</option>
                                <option value="2" data-months="2">2 Months</option>
                                <option value="3" data-months="3">3 Months</option>
                                <option value="6" data-months="6">6 Months</option>
                                <option value="12" data-months="12">12 Months</option>
                            </select>
                        </td>
                        <td class="py-4 px-4">${packageHTML}</td>
                        <td class="py-4 px-4 font-semibold rental-cell">
                            ₹${rowTotal} <span class="price-label text-sm"></span>
                            <div class="text-xs text-gray-500">
                                ${hoardingType === 'dooh' && priceLabel.includes('slot') ? 'per slot' : `${months} month${months > 1 ? '' : ''}`}
                            </div>
                        </td>
                    </tr>
                `;
            });

            document.getElementById('enquiryTableBody').innerHTML = tableHTML;
            document.getElementById('enquiryTotal').innerText = totalAmount;
            
            // 🔥 Apply duration state for items with selected packages on modal load
            items.forEach(item => {
                const hoardingId = item.hoarding_id;
                const selectedPkg = item.selected_package;
                
                if (selectedPkg) {
                    // Package is selected - disable duration select
                    const durationSelect = document.querySelector(`tr[data-hoarding-id="${hoardingId}"] select[onchange*="onDurationChange"]`);
                    if (durationSelect) {
                        const pkgMonths = selectedPkg.min_booking_duration || 1;
                        durationSelect.value = pkgMonths;
                        durationSelect.disabled = true;
                        durationSelect.style.opacity = '0.6';
                        durationSelect.style.cursor = 'not-allowed';
                    }
                }
            });
            
            console.log('[DEBUG] ✅ Modal loaded with', items.length, 'items');
        })
        .catch(error => {
            console.error('[ERROR] Failed to load enquiry hoardings:', error);
            alert('Failed to load hoardings. Please try again.');
            closeEnquiryModal();
        });
}



/* =====================================================
 | TOTAL RECALCULATION
 ===================================================== */
function recalculateEnquiryTotal() {

    let total = 0;

    Object.values(window.enquiryState.items).forEach(item => {

        let rowTotal = 0;

        // ✅ BASE PRICE CASE → multiply with duration
        if (!item.package_id) {
            rowTotal = Number(item.price || 0) * Number(item.months || 1);
        } 
        // ✅ PACKAGE CASE → backend price only
        else {
            rowTotal = Number(item.price || 0);
        }

        total += rowTotal;

        // 🔹 Update rental column (price same rahe, sirf text update)
        const row = document.querySelector(
            `tr[data-hoarding-id="${item.hoarding_id}"]`
        );

        if (row) {
            const rentalCell = row.querySelector('.rental-cell');
            if (rentalCell) {
                rentalCell.innerHTML = `
                    ₹${item.price}
                    <div class="text-xs text-gray-500">
                        ${item.months} month${item.months > 1 ? '' : ''}
                    </div>
                `;
            }
        }
    });

    document.getElementById('enquiryTotal').innerText = total;
}

</script>
<script>

function submitEnquiryFromCart() {
    const items = Object.values(window.enquiryState.items);
    if (!items.length) {
        alert('No hoardings selected');
        return;
    }
    // Validate campaign date for all hoardings
    let allDatesSelected = true;
    let campaignDates = [];
    items.forEach(item => {
        const dateInput = document.getElementById(`campaign-input-${item.hoarding_id}`);
        if (!dateInput || !dateInput.value) {
            allDatesSelected = false;
        } else {
            campaignDates.push(dateInput.value);
        }
    });
    if (!allDatesSelected) {
        alert('Please select campaign start date for all selected hoardings.');
        return;
    }

    // Prepare payload
    const payload = {
        _token: '{{ csrf_token() }}',
        duration_type: 'months',
        preferred_start_date: '{{ now()->toDateString() }}',
        customer_name: '{{ auth()->user()->name }}',
        customer_mobile: '{{ auth()->user()->phone ?? auth()->user()->mobile }}',
        hoarding_id: [],
        package_id: [],
        package_label: [],
        amount: [],
        campaign_start_date: [],
        min_booking_duration: [],
        months: []
    };

    items.forEach(item => {
        payload.hoarding_id.push(item.hoarding_id);
        payload.package_id.push(item.package_id ? item.package_id : '');
        payload.package_label.push(item.package_label);
        payload.amount.push(item.price * item.months);
        payload.campaign_start_date.push(document.getElementById(`campaign-input-${item.hoarding_id}`).value);
        if (item.package_id) {
            payload.min_booking_duration.push(item.months);
        } else {
            payload.months.push(item.months);
        }
    });

    // AJAX submission
    fetch("{{ route('enquiries.store') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showEnquirySuccessModal(data);
        } else {
            alert(data.message || 'Enquiry submission failed.');
        }
    })
    .catch(error => {
        console.error('Enquiry AJAX error:', error);
        alert('An error occurred while submitting your enquiry.');
    });
}
function showEnquirySuccessModal(data) {
    // Fill details if needed
    const detailsDiv = document.getElementById('enquirySuccessDetails');
    if (detailsDiv) {
        detailsDiv.innerHTML = data.details || '';
    }
    document.getElementById('enquirySuccessModal').classList.remove('hidden');
    closeEnquiryModal();
}

function onPackageChange(select, hoardingId, hoardingType) {

    const option = select.options[select.selectedIndex];

    const price = Number(option.dataset.price);
    const months = Number(option.dataset.months);
    const discount = Number(option.dataset.discount || 0);

    // Get duration select for this hoarding
    const row = select.closest('tr');
    const durationSelect = row.querySelector('select[onchange*="onDurationChange"]');

    // Determine price label based on hoarding type
    let priceLabel = '/ Month';
    if (hoardingType === 'dooh' && !option.value) {
        priceLabel = 'per  second';
    }

    // 🔥 UPDATE STATE
    window.enquiryState.items[hoardingId] = {
        hoarding_id: hoardingId,
        hoarding_type: hoardingType,
        package_id: option.value,
        package_label: option.text,
        price: price,
        months: months,
        price_label: priceLabel
    };

    // If package selected (not base price)
    if (option.value) {
        // Set duration to package duration and make readonly
        durationSelect.value = months;
        durationSelect.disabled = true;
        durationSelect.style.opacity = '0.6';
        durationSelect.style.cursor = 'not-allowed';
    } else {
        // Base price selected - enable duration selection
        durationSelect.disabled = false;
        durationSelect.style.opacity = '1';
        durationSelect.style.cursor = 'pointer';
        durationSelect.value = 1; // Reset to 1 month
    }

    recalculateEnquiryTotal();
}

function onDurationChange(select, hoardingId) {
    const months = Number(select.value);
    
    // Get current item data
    const item = window.enquiryState.items[hoardingId];
    if (item) {
        item.months = months;
    }
    
    recalculateEnquiryTotal();
}

</script>
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
    <div class="mt-4">
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
    </div>

    {{-- TOTAL --}}
    <div class="border-t border-gray-200 mt-4 pt-4 flex justify-between font-semibold">
        <span>Total</span>
        <span id="summary-total">₹0</span>
    </div>

    <center>
        <button 
            id="enquireNowBtn"
            class="text-blue-600 text-sm font-medium text-center"
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
            <table class="w-full text-sm">
                 <thead class="bg-gray-50">
                    <tr class="text-left text-xs py-2 font-semibold text-gray-600 uppercase tracking-wide">

                        <th>Hoarding</th>
                        <th>Location</th>
                        <th>Size</th>
                        <th>Select Package</th>
                        <th>Rental</th>
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

            <button onclick="submitEnquiryFromCart()"
                    class="bg-green-700 text-white px-6 py-2 rounded">
                Raise an Enquiry
            </button>

        </div>

    </div>
</div>
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
            openEnquiryModal();
            loadEnquiryHoardings();
        });
    }

});


/* =====================================================
 | MODAL CONTROLS
 ===================================================== */
function openEnquiryModal() {
    document.getElementById('enquiryModal').classList.remove('hidden');
    loadEnquiryHoardings();
}

function closeEnquiryModal() {
    document.getElementById('enquiryModal').classList.add('hidden');
}



/* =====================================================
 | LOAD CART → ENQUIRY MODAL
 ===================================================== */
function loadEnquiryHoardings() {

    fetch('/enquiry/shortlisted')
        .then(response => response.json())
        .then(items => {

            let tableHTML = '';
            let totalAmount = 0;

            // 🔥 RESET STATE (VERY IMPORTANT)
            window.enquiryState.items = {};

            items.forEach(item => {

                /* ===============================
                 | 1. STATE FILL (SOURCE OF TRUTH)
                 =============================== */
                const hoardingId = item.hoarding_id;

                const selectedPkg = item.selected_package;

                let price, months, packageId, packageLabel;

                if (selectedPkg) {
                    // ✅ PACKAGE MODE
                    price = Math.round(
                        item.base_monthly_price -
                        (item.base_monthly_price * selectedPkg.discount_percent / 100)
                    );
                    months = selectedPkg.min_booking_duration;
                    packageId = selectedPkg.id;
                    packageLabel = selectedPkg.package_name;
                } else {
                    // ✅ MONTHLY MODE
                    price = item.base_monthly_price;
                    months = 1;
                    packageId = null;
                    packageLabel = 'Base';
                }

                window.enquiryState.items[hoardingId] = {
                    hoarding_id: hoardingId,
                    package_id: packageId,
                    package_label: packageLabel,
                    price,
                    months
                };


                window.enquiryState.items[hoardingId] = {
                    hoarding_id: hoardingId,
                    package_id: selectedPkg?.id ?? null,
                    package_label: selectedPkg?.package_name ?? 'Base',
                    price,
                    months
                };

                /* ===============================
                 | 2. UI CALCULATION
                 =============================== */
                const rowTotal = price * months;
                totalAmount += rowTotal;

                /* ===============================
                 | 3. PACKAGE DROPDOWN
                 =============================== */
                let packageHTML = '—';

                if (item.packages?.length) {
                    packageHTML = `
                        <select class="border border-gray-200 px-2 py-1 rounded w-full"
                                onchange="onPackageChange(this, ${hoardingId})">
                            ${item.packages.map(pkg => {

                                const pkgPrice = Math.round(
                                    item.base_monthly_price -
                                    (item.base_monthly_price * pkg.discount_percent / 100)
                                );

                                const pkgMonths = pkg.min_booking_duration ?? 1;

                                return `
                                    <option value="${pkg.id}"
                                        data-price="${pkgPrice}"
                                        data-months="${pkgMonths}"
                                        ${selectedPkg && Number(pkg.id) === Number(selectedPkg.id) ? 'selected' : ''}>
                                        ${pkg.package_name} (${pkg.discount_percent}% off)
                                    </option>
                                `;
                            }).join('')}
                        </select>
                    `;

                }

                /* ===============================
                 | 4. ROW HTML
                 =============================== */
                tableHTML += `
                    <tr data-hoarding-id="${hoardingId}" class="border-b border-gray-200 align-middle">
                        <td class="py-4 font-medium">${item.title}</td>
                        <td class="py-4 text-gray-600">
                            ${(item.locality ?? '')}${item.state ? ', ' + item.state : ''}
                        </td>
                        <td class="py-4">${item.size ?? '—'}</td>
                        <td class="py-4">${packageHTML}</td>
                        <td class="py-4 font-semibold rental-cell">
                            ₹${rowTotal}
                            <div class="text-xs text-gray-500">
                                (${price} × ${months} months)
                            </div>
                        </td>
                    </tr>
                `;
            });

            document.getElementById('enquiryTableBody').innerHTML = tableHTML;
            document.getElementById('enquiryTotal').innerText = totalAmount;
        });
}



/* =====================================================
 | TOTAL RECALCULATION
 ===================================================== */
function recalculateEnquiryTotal() {

    let total = 0;

    document
        .querySelectorAll('#enquiryTableBody select')
        .forEach(select => {

            const option = select.options[select.selectedIndex];

            const price  = Number(option.dataset.price || 0);
            const months = Number(option.dataset.months || 1);

            const rowTotal = price * months;
            total += rowTotal;

            // 🔥 UPDATE RENTAL CELL
            const row = select.closest('tr');
            const rentalCell = row.querySelector('.rental-cell');

            if (rentalCell) {
                rentalCell.innerHTML = `
                    ₹${rowTotal}
                    <div class="text-xs text-gray-500">
                        (${price} × ${months} month${months > 1 ? 's' : ''})
                    </div>
                `;
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

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('enquiries.store') }}";

    let html = `
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="duration_type" value="months">
        <input type="hidden" name="preferred_start_date" value="{{ now()->toDateString() }}">
        <input type="hidden" name="customer_name" value="{{ auth()->user()->name }}">
        <input type="hidden" name="customer_mobile" value="{{ auth()->user()->phone ?? auth()->user()->mobile }}">
    `;

    items.forEach((item, index) => {
        html += `
            <input type="hidden" name="hoarding_id[]" value="${item.hoarding_id}">
            <input type="hidden" name="package_id[]" value="${item.package_id ? item.package_id : ''}">
            <input type="hidden" name="package_label[]" value="${item.package_label}">
            <input type="hidden" name="amount[]" value="${item.price * item.months}">
            <input type="hidden" name="campaign_start_date[]" value="${document.getElementById(`campaign-input-${item.hoarding_id}`).value}">
        `;
        if (item.package_id) {
            html += `<input type="hidden" name="min_booking_duration[]" value="${item.months}">`;
        } else {
            html += `<input type="hidden" name="months[]" value="${item.months}">`;
        }
    });

    form.innerHTML = html;
    document.body.appendChild(form);
    form.submit();
}

function onPackageChange(select, hoardingId) {

    const option = select.options[select.selectedIndex];

    const price  = Number(option.dataset.price);
    const months = Number(option.dataset.months);

    // 🔥 UPDATE STATE
    window.enquiryState.items[hoardingId] = {
        hoarding_id: hoardingId,
        package_id: option.value,
        package_label: option.text,
        price,
        months
    };

    recalculateEnquiryTotal();
}

</script>
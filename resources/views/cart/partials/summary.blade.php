<div class="bg-white border border-gray-200 rounded-xl p-5">
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

    <div class="border-t border-gray-200 mt-4 pt-4 flex justify-between font-semibold">
        <span>Total</span>
        <span id="summary-total">₹0</span>
    </div>

    <div class="flex justify-center">
        <button
            id="enquireNowBtn"
            class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white py-2.5 px-4 rounded-lg font-semibold transition-colors cursor-pointer">
            Enquire Now
        </button>
    </div>
</div>

@push('modals')
<div id="enquiryModal"
    class="hidden fixed inset-0 bg-black/50 flex justify-center items-center px-4 sm:px-6 overflow-x-hidden enquiry-overlay">

    <div class="bg-white w-full max-w-[calc(100vw-2rem)] sm:max-w-6xl my-8 sm:my-16 border border-gray-200 shadow-lg overflow-hidden">

        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold">Selected Hoardings</h2>
            <button onclick="closeEnquiryModal()" class="text-gray-500 hover:text-black text-lg cursor-pointer">✕</button>
        </div>

        <div class="p-6 max-h-[70vh] overflow-y-scroll scrollbar-visible-mobile">
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
                <tbody id="enquiryTableBody"></tbody>
            </table>
        </div>

        <div class="flex justify-between items-center bg-green-100 px-6 py-4">
            <p class="font-semibold">
                Total Amount: ₹<span id="enquiryTotal">0</span>
            </p>
            <button onclick="openRaiseEnquiryModal(Object.keys(window.enquiryState.items).length); closeEnquiryModal();"
                    class="bg-green-700 text-white px-6 py-2 rounded cursor-pointer">
                Raise an Enquiry
            </button>
        </div>

    </div>
</div>
@endpush

@include('cart.partials.enquiry-success-modal')

@push('scripts')
<script>
window.enquiryState = {
    items: {}
};
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const enquireBtn = document.getElementById('enquireNowBtn');
    if (enquireBtn) {
        enquireBtn.addEventListener('click', function () {
            openCartEnquiryModal();
            loadEnquiryHoardings();
        });
    }
});

function openCartEnquiryModal() {
    const modal = document.getElementById('enquiryModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeEnquiryModal() {
    const modal = document.getElementById('enquiryModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function loadEnquiryHoardings() {
    fetch('/enquiry/shortlisted')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(items => {
            let tableHTML = '';
            let totalAmount = 0;

            window.enquiryState.items = {};

            items.forEach(item => {
                const hoardingId  = item.hoarding_id;
                const hoardingType = item.hoarding_type;
                const selectedPkg  = item.selected_package;

                let displayPrice, months, packageId, packageLabel, priceLabel;

                if (!selectedPkg) {
                    displayPrice = item.monthly_price > 0 ? item.monthly_price : item.base_monthly_price;
                    months       = 1;
                    packageId    = null;
                    packageLabel = 'Base';
                    priceLabel   = '/ Month';
                } else {
                    const basePrice   = item.base_monthly_price || 0;
                    months            = selectedPkg.min_booking_duration || 1;
                    const totalPrice  = basePrice * months;
                    const discountPct = selectedPkg.discount_percent || 0;
                    displayPrice      = discountPct > 0 ? totalPrice - (totalPrice * discountPct / 100) : totalPrice;
                    displayPrice      = Math.round(displayPrice);
                    packageId         = selectedPkg.id;
                    packageLabel      = selectedPkg.package_name;
                    priceLabel        = '/ Month';
                }

                window.enquiryState.items[hoardingId] = {
                    hoarding_id:   hoardingId,
                    hoarding_type: hoardingType,
                    package_id:    packageId,
                    package_label: packageLabel,
                    price:         displayPrice,
                    months:        months,
                    price_label:   priceLabel
                };

                let rowTotal = displayPrice;
                if (!selectedPkg) rowTotal = displayPrice * 1;
                totalAmount += rowTotal;

                let basePrice = Math.round(item.monthly_price > 0 ? item.monthly_price : item.base_monthly_price || 0);

                const packageHTML = `
                    <div class="duration-select-wrapper">
                        <select class="duration-select border border-gray-200 px-2 py-1 rounded w-full"
                                onchange="onPackageChange(this, ${hoardingId}, '${hoardingType}')">
                            <option value="" data-price="${basePrice}" data-months="1" data-discount="0" ${!selectedPkg ? 'selected' : ''}>
                                Price (₹${basePrice})
                            </option>
                            ${item.packages?.length ? item.packages.map(pkg => {
                                const pkgMonths   = pkg.min_booking_duration ?? 1;
                                const basePriceC  = item.base_monthly_price || 0;
                                const tPrice      = basePriceC * pkgMonths;
                                const dPct        = pkg.discount_percent || 0;
                                const pkgPrice    = Math.round(dPct > 0 ? tPrice - (tPrice * dPct / 100) : tPrice);
                                return `<option value="${pkg.id}"
                                            data-price="${pkgPrice}"
                                            data-months="${pkgMonths}"
                                            data-discount="${dPct}"
                                            ${selectedPkg && Number(pkg.id) === Number(selectedPkg.id) ? 'selected' : ''}>
                                            offer- ${dPct}% / ${pkgMonths} months
                                        </option>`;
                            }).join('') : ''}
                        </select>
                        <svg class="duration-arrow w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>`;

                tableHTML += `
                    <tr data-hoarding-id="${hoardingId}" class="border-b border-gray-200 align-middle">
                        <td class="py-4 px-4 font-medium">${item.title}</td>
                        <td class="py-4 px-4 text-gray-600">
                            ${(item.locality ?? '')}${item.state ? ', ' + item.state : ''}
                        </td>
                        <td class="py-4 px-4">
                            <div class="duration-select-wrapper">
                                <select class="duration-select border border-gray-200 px-2 py-1 rounded w-full"
                                        name="duration"
                                        onchange="onDurationChange(this, ${hoardingId})">
                                    ${Array.from({length:12},(_,i)=>`<option value="${i+1}" data-months="${i+1}">${i+1} Month${i+1>1?'s':''}</option>`).join('')}
                                </select>
                                <svg class="duration-arrow w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </td>
                        <td class="py-4 px-4">${packageHTML}</td>
                        <td class="py-4 px-4 font-semibold rental-cell">
                            ₹${!selectedPkg ? (displayPrice * 1) : rowTotal}
                            <span class="price-label text-sm"></span>
                            <div class="text-xs text-gray-500">
                                ${hoardingType === 'dooh' && priceLabel.includes('slot') ? 'per slot' : `${months} month${months > 1 ? '' : ''}`}
                            </div>
                        </td>
                    </tr>`;
            });

            document.getElementById('enquiryTableBody').innerHTML = tableHTML;
            document.getElementById('enquiryTotal').innerText = totalAmount;

            items.forEach(item => {
                if (item.selected_package) {
                    const durationSelect = document.querySelector(`tr[data-hoarding-id="${item.hoarding_id}"] select[onchange*="onDurationChange"]`);
                    if (durationSelect) {
                        durationSelect.value           = item.selected_package.min_booking_duration || 1;
                        durationSelect.disabled        = true;
                        durationSelect.style.opacity   = '0.6';
                        durationSelect.style.cursor    = 'not-allowed';
                    }
                }
            });
        })
        .catch(error => {
            console.error('[ERROR] Failed to load enquiry hoardings:', error);
            alert('Failed to load hoardings. Please try again.');
            closeEnquiryModal();
        });
}

function recalculateEnquiryTotal() {
    let total = 0;
    Object.values(window.enquiryState.items).forEach(item => {
        let rowTotal = !item.package_id
            ? Number(item.price || 0) * Number(item.months || 1)
            : Number(item.price || 0);
        total += rowTotal;

        const row = document.querySelector(`tr[data-hoarding-id="${item.hoarding_id}"]`);
        if (row) {
            const rentalCell = row.querySelector('.rental-cell');
            if (rentalCell) {
                rentalCell.innerHTML = !item.package_id
                    ? `₹${Number(item.price || 0) * Number(item.months || 1)}<div class="text-xs text-gray-500">${item.months} month${item.months > 1 ? '' : ''}</div>`
                    : `₹${item.price}<div class="text-xs text-gray-500">${item.months} month${item.months > 1 ? '' : ''}</div>`;
            }
        }
    });
    document.getElementById('enquiryTotal').innerText = total;
}
</script>

<script>
function submitEnquiryFromCart() {
    const items = Object.values(window.enquiryState.items);
    if (!items.length) { alert('No hoardings selected'); return; }

    let allDatesSelected = true;
    items.forEach(item => {
        const dateInput = document.getElementById(`campaign-input-${item.hoarding_id}`);
        if (!dateInput || !dateInput.value) allDatesSelected = false;
    });
    if (!allDatesSelected) { alert('Please select campaign start date for all selected hoardings.'); return; }

    const payload = {
        _token: '{{ csrf_token() }}',
        duration_type: 'months',
        preferred_start_date: '{{ now()->toDateString() }}',
        customer_name: '{{ auth()->user()->name }}',
        customer_mobile: '{{ auth()->user()->phone ?? auth()->user()->mobile }}',
        hoarding_id: [], package_id: [], package_label: [],
        amount: [], campaign_start_date: [], min_booking_duration: [], months: []
    };

    items.forEach(item => {
        payload.hoarding_id.push(item.hoarding_id);
        payload.package_id.push(item.package_id ? item.package_id : '');
        payload.package_label.push(item.package_label);
        payload.amount.push(item.price * item.months);
        payload.campaign_start_date.push(document.getElementById(`campaign-input-${item.hoarding_id}`).value);
        if (item.package_id) { payload.min_booking_duration.push(item.months); }
        else { payload.months.push(item.months); }
    });

    fetch("{{ route('enquiries.store') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) showEnquirySuccessModal(data);
        else alert(data.message || 'Enquiry submission failed.');
    })
    .catch(error => {
        console.error('Enquiry AJAX error:', error);
        alert('An error occurred while submitting your enquiry.');
    });
}

function showEnquirySuccessModal(data) {
    const detailsDiv = document.getElementById('enquirySuccessDetails');
    if (detailsDiv) detailsDiv.innerHTML = data.details || '';
    document.getElementById('enquirySuccessModal').classList.remove('hidden');
    closeEnquiryModal();
}

function onPackageChange(select, hoardingId, hoardingType) {
    const option       = select.options[select.selectedIndex];
    const price        = Number(option.dataset.price);
    const months       = Number(option.dataset.months);
    const row          = select.closest('tr');
    const durationSelect = row.querySelector('select[onchange*="onDurationChange"]');

    let priceLabel = '/ Month';
    if (hoardingType === 'dooh' && !option.value) priceLabel = 'per  second';

    window.enquiryState.items[hoardingId] = {
        hoarding_id:   hoardingId,
        hoarding_type: hoardingType,
        package_id:    option.value,
        package_label: option.text,
        price:         price,
        months:        months,
        price_label:   priceLabel
    };

    if (option.value) {
        durationSelect.value           = months;
        durationSelect.disabled        = true;
        durationSelect.style.opacity   = '0.6';
        durationSelect.style.cursor    = 'not-allowed';
    } else {
        durationSelect.disabled        = false;
        durationSelect.style.opacity   = '1';
        durationSelect.style.cursor    = 'pointer';
        durationSelect.value           = 1;
    }

    recalculateEnquiryTotal();
}

function onDurationChange(select, hoardingId) {
    const item = window.enquiryState.items[hoardingId];
    if (item) item.months = Number(select.value);
    recalculateEnquiryTotal();
}
</script>
@endpush

<style>
.duration-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background: none;
    padding-right: 2rem;
    width: 100%;
    box-sizing: border-box;
}
.duration-select-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}
.duration-arrow {
    position: absolute;
    top: 50%;
    right: 0.75rem;
    transform: translateY(-50%);
    pointer-events: none;
}
.enquiry-overlay {
    z-index: 2147483647 !important;
}
</style>

<style>
/* Custom scrollbar for modal table area on mobile */
@media (max-width: 640px) {
    .scrollbar-visible-mobile {
        scrollbar-width: thin;
        scrollbar-color: #a3a3a3 #f3f4f6;
        -webkit-overflow-scrolling: touch;
    }
    .scrollbar-visible-mobile::-webkit-scrollbar {
        width: 8px;
        background: #f3f4f6;
        border-radius: 8px;
        display: block;
    }
    .scrollbar-visible-mobile::-webkit-scrollbar-thumb {
        background: #a3a3a3;
        border-radius: 8px;
        min-height: 40px;
        border: 2px solid #f3f4f6;
    }
    .scrollbar-visible-mobile::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 8px;
    }
}
</style>
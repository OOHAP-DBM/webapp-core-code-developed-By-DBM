<div class="w-full mx-auto">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Items Table -->
        <div class="flex-1 bg-white rounded-md shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h2 class="text-xl font-black text-gray-800">BOOKING PREVIEW</h2>
                <button onclick="backToSelection()" class="text-sm font-bold text-[#2D5A43]">← Edit Selection</button>
            </div>

            <div class="grid grid-cols-2 gap-8 p-6 border-b border-gray-100">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase">Customer</label>
                    <h3 id="preview-cust-name" class="font-bold text-gray-800">---</h3>
                    <p id="preview-cust-phone" class="text-xs text-gray-500">---</p>
                </div>
                <div class="text-right">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase">Inventory</label>
                    <p class="text-sm font-bold text-gray-800"><span id="preview-total-count">0</span> Items Selected</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-[10px] uppercase text-gray-400 font-bold">
                        <tr>
                            <th class="px-6 py-3">Hoarding</th>
                            <th class="px-6 py-3">Duration</th>
                            <th class="px-6 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="preview-ooh-list" class="divide-y divide-gray-50 text-sm"></tbody>
                    <tbody id="preview-dooh-list" class="divide-y divide-gray-50 text-sm"></tbody>
                </table>
            </div>
        </div>

        <!-- POS Checkout -->
        <div class="lg:w-80">
            <div class="bg-white rounded-md shadow-xl border border-gray-200 p-6 sticky top-6">
                <h3 class="font-bold text-gray-800 mb-6">POS Checkout</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Discount (₹)</label>
                        <input type="number" id="pos-discount" oninput="calculateFinalTotals()" value="0" class="w-full p-2 border border-gray-200 rounded-lg font-bold text-red-600">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Payment Mode</label>
                        <select id="pos-payment-mode" class="w-full p-2 border border-gray-200 rounded-lg text-sm font-semibold">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="online">UPI / Online</option>
                        </select>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-dashed space-y-3">
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Subtotal</span>
                        <span id="side-sub-total">₹0</span>
                    </div>
                    <div class="flex justify-between text-xs text-red-500">
                        <span>Discount</span>
                        <span id="side-discount-display">-₹0</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 font-bold">
                        <span>Tax (GST 18%)</span>
                        <span id="side-tax">₹0</span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="font-black text-gray-900">Total</span>
                        <span id="side-grand-total" class="text-xl font-black text-[#2D5A43]">₹0</span>
                    </div>
                </div>

                <button id="create-booking-btn" class="w-full mt-6 py-4 bg-[#2D5A43] text-white rounded-xl font-bold shadow-lg">
                    Finalize Booking
                </button>
            </div>
        </div>
    </div>
</div>

<script>
/* --- Move variables to a scope accessible by both files --- */
let globalBaseAmount = 0;

// Ensure duration helpers exist (fallback if main script hasn't defined them yet)
if (typeof getTieredMonths !== 'function') {
    function getTieredMonths(startDate, endDate) {
        if (!startDate || !endDate) return 0;
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        return Math.ceil(diffDays / 30);
    }

    function getTieredDurationLabel(startDate, endDate) {
        const m = getTieredMonths(startDate, endDate);
        if (m <= 0) return '0 Months';
        return m === 1 ? '1 Month' : `${m} Months`;
    }

    if (typeof calculateTieredPrice !== 'function') {
        function calculateTieredPrice(pricePerMonth, startDate, endDate) {
            return pricePerMonth * getTieredMonths(startDate, endDate);
        }
    }
}

/* --- Define functions globally --- */
function calculateFinalTotals() {
    const discountVal = parseFloat(document.getElementById('pos-discount')?.value || 0);
    const taxableAmount = Math.max(0, globalBaseAmount - discountVal);
    const tax = taxableAmount * 0.18;
    const grandTotal = taxableAmount + tax;

    // Use the formatINR function from the parent script
    const formattedDiscount = typeof formatINR === 'function' ? formatINR(discountVal) : `₹${discountVal}`;
    const formattedTax = typeof formatINR === 'function' ? formatINR(tax) : `₹${tax}`;
    const formattedTotal = typeof formatINR === 'function' ? formatINR(grandTotal) : `₹${grandTotal}`;

    const discDisplay = document.getElementById('side-discount-display');
    if (discDisplay) discDisplay.innerText = `- ${formattedDiscount}`;
    
    const taxDisplay = document.getElementById('side-tax');
    if (taxDisplay) taxDisplay.innerText = formattedTax;
    
    const totalDisplay = document.getElementById('side-grand-total');
    if (totalDisplay) totalDisplay.innerText = formattedTotal;
}

// Ensure other functions are also global
window.calculateFinalTotals = calculateFinalTotals;

document.addEventListener('DOMContentLoaded', () => {
    // Keep only the event listeners here
    document.getElementById('create-booking-btn')?.addEventListener('click', async () => {
       function populatePreview() {
        if (!selectedCustomer) return alert('Select a customer');

        safeSet('preview-cust-name', selectedCustomer.name);
        safeSet('preview-cust-phone', selectedCustomer.phone);
        safeSet('preview-total-count', selectedHoardings.size);

        const oohBody = document.getElementById('preview-ooh-list');
        const doohBody = document.getElementById('preview-dooh-list');
        if (!oohBody || !doohBody) return console.error('Preview DOM elements missing!');

        oohBody.innerHTML = '';
        doohBody.innerHTML = '';
        globalBaseAmount = 0;

        selectedHoardings.forEach(h => {
            const itemTotal = calculateTieredPrice(h.price_per_month, h.startDate, h.endDate);
            globalBaseAmount += itemTotal;

            const row = `
                <tr class="border-b border-gray-50">
                    <td class="px-6 py-4 font-bold text-gray-800">${h.title}</td>
                    <td class="px-6 py-4 text-gray-500">
                        ${h.startDate} to ${h.endDate}
                        <div class="text-[10px] text-gray-400 mt-1">${getTieredDurationLabel(h.startDate, h.endDate)}</div>
                    </td>
                    <td class="px-6 py-4 text-right font-bold">${formatINR(itemTotal)}</td>
                </tr>`;
            
            if (h.type?.toUpperCase() === 'DOOH') doohBody.innerHTML += row;
            else oohBody.innerHTML += row;
        });

        safeSet('side-sub-total', formatINR(globalBaseAmount));
        calculateFinalTotals();
    }

    function showPreview() {
        if (!selectedCustomer) return alert("Please select a customer first!");
        if (selectedHoardings.size === 0) return alert("Please select at least one hoarding!");

        document.getElementById('selection-screen')?.classList.add('hidden');
        document.getElementById('preview-screen')?.classList.remove('hidden');
        populatePreview();
    }

    function backToSelection() {
        document.getElementById('preview-screen')?.classList.add('hidden');
        document.getElementById('selection-screen')?.classList.remove('hidden');
    }

    document.getElementById('create-booking-btn')?.addEventListener('click', async () => {
        const btn = document.getElementById('create-booking-btn');
        if (btn.disabled) return;

        let sDate = null, eDate = null;
        selectedHoardings.forEach(h => {
            if (!sDate || h.startDate < sDate) sDate = h.startDate;
            if (!eDate || h.endDate > eDate) eDate = h.endDate;
        });

        const payload = {
            hoarding_ids: Array.from(selectedHoardings.keys()).join(','),
            customer_id: selectedCustomer?.id,
            customer_name: selectedCustomer?.name,
            customer_phone: selectedCustomer?.phone,
            customer_email: selectedCustomer?.email || '',
            customer_address: selectedCustomer?.address || 'N/A',
            booking_type: 'ooh',
            start_date: sDate,
            end_date: eDate,
            base_amount: globalBaseAmount - parseFloat(document.getElementById('pos-discount')?.value || 0),
            discount_amount: parseFloat(document.getElementById('pos-discount')?.value || 0),
            payment_mode: document.getElementById('pos-payment-mode')?.value || 'cash',
            payment_reference: document.getElementById('pos-payment-ref')?.value || '',
            payment_notes: 'POS Booking'
            // customer_gstin: selectedCustomer?.gstin || '', 
        };

        btn.disabled = true;
        btn.innerText = "Creating Booking...";

        try {
            const res = await fetch('/vendor/pos/api/bookings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify(payload)
            });

            const result = await res.json();
            if (!res.ok) throw new Error(Object.values(result.errors || {}).flat().join('\n') || "Error");

            alert("✅ Booking Success!");
            window.location.href = "/vendor/pos/bookings";
        } catch (e) {
            alert(e.message);
            btn.disabled = false;
            btn.innerText = "Finalize Booking";
        }
    });
    });
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var sel = document.getElementById('pos-payment-mode');
        if (sel && !sel.parentElement.classList.contains('select-wrapper')) {
            var wrapper = document.createElement('div');
            wrapper.className = 'select-wrapper';
            sel.parentNode.insertBefore(wrapper, sel);
            wrapper.appendChild(sel);
            var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('class', 'custom-select-arrow');
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
            svg.setAttribute('stroke-width', '2');
            svg.setAttribute('viewBox', '0 0 24 24');
            svg.innerHTML = '<path d="M19 9l-7 7-7-7"/>';
            wrapper.appendChild(svg);
        }
    });
</script>
<style>
    /* Hide default arrow */
    #pos-payment-mode {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: none;
        background-color: #fff;
        position: relative;
    }
    .custom-select-arrow {
        pointer-events: none;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: #2D5A43;
    }
    .select-wrapper { position: relative; }
</style>
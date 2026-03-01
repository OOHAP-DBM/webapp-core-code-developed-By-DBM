<div class="w-full mx-auto">
    <div class="flex flex-col lg:flex-row gap-8">

        {{-- ── Items Table ── --}}
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

        {{-- ── POS Checkout ── --}}
        <div class="lg:w-96">
            <div class="bg-white rounded-md shadow-xl border border-gray-200 p-6 sticky top-6 space-y-5">
                <h3 class="font-bold text-gray-800 text-lg">POS Checkout</h3>

                {{-- Discount --}}
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Discount (₹)</label>
                    <input type="number" id="pos-discount" oninput="calculateFinalTotals()" value="0"
                        class="w-full p-2 border border-gray-200 rounded-lg font-bold text-red-600 focus:ring-2 focus:ring-green-300 focus:border-green-400 outline-none">
                </div>

                {{-- Payment Mode --}}
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2">Payment Mode</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" onclick="selectPaymentMode('cash')"
                            class="payment-mode-btn active-mode flex flex-col items-center gap-1 p-3 border-2 rounded-xl text-xs font-bold transition" data-mode="cash">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M12 12a2 2 0 100-4 2 2 0 000 4z"/><path d="M6 12h.01M18 12h.01"/></svg>
                            Cash
                        </button>
                        <button type="button" onclick="selectPaymentMode('bank_transfer')"
                            class="payment-mode-btn flex flex-col items-center gap-1 p-3 border-2 rounded-xl text-xs font-bold transition" data-mode="bank_transfer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            Bank Transfer
                        </button>
                        <button type="button" onclick="selectPaymentMode('online')"
                            class="payment-mode-btn flex flex-col items-center gap-1 p-3 border-2 rounded-xl text-xs font-bold transition" data-mode="online">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                            UPI / Online
                        </button>
                    </div>
                </div>

                {{-- Bank Details Panel --}}
                <div id="bank-details-panel" class="hidden space-y-3 bg-blue-50 border border-blue-100 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="text-xs font-bold text-blue-700 uppercase tracking-wider">Bank Details</h4>
                        <span id="bank-saved-badge" class="hidden text-[10px] font-bold text-green-700 bg-green-100 px-2 py-0.5 rounded-full">✓ Saved</span>
                    </div>

                    {{-- Saved bank details card --}}
                    <div id="bank-saved-card" class="hidden bg-white border border-blue-200 rounded-lg p-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-bold text-gray-700" id="saved-bank-name">---</p>
                                <p class="text-[11px] text-gray-500 mt-0.5" id="saved-bank-acc">A/C: ---</p>
                                <p class="text-[11px] text-gray-500" id="saved-bank-holder">Holder: ---</p>
                                <p class="text-[11px] text-gray-400" id="saved-bank-ifsc">IFSC: ---</p>
                            </div>
                            <button onclick="editBankDetails()" class="text-blue-600 hover:text-blue-800 text-[11px] font-bold px-2 py-1 border border-blue-200 rounded-md">Change</button>
                        </div>
                    </div>

                    {{-- Bank input form --}}
                    <div id="bank-input-form" class="space-y-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">IFSC Code</label>
                            <div class="flex gap-2">
                                <input type="text" id="bank-ifsc" placeholder="e.g. SBIN0001234" maxlength="11"
                                    class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono uppercase focus:ring-2 focus:ring-blue-300 outline-none"
                                    oninput="this.value=this.value.toUpperCase()" onblur="fetchBankFromIFSC()">
                                <!-- <button onclick="fetchBankFromIFSC()" class="px-3 py-2 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700">Fetch</button> -->
                            </div>
                            <div id="ifsc-result" class="hidden mt-1 text-[11px] text-blue-700 bg-blue-100 rounded px-2 py-1 font-medium"></div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">Account Number</label>
                            <input type="text" id="bank-acc-number" placeholder="Enter account number"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">Account Holder Name</label>
                            <input type="text" id="bank-acc-holder" placeholder="As per bank records"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                        </div>
                        <button onclick="saveBankDetails()" class="w-full py-2 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition">
                            Save Bank Details
                        </button>
                    </div>
                </div>

                {{-- UPI Details Panel --}}
                <div id="upi-details-panel" class="hidden space-y-3 bg-purple-50 border border-purple-100 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="text-xs font-bold text-purple-700 uppercase tracking-wider">UPI / Online Details</h4>
                        <span id="upi-saved-badge" class="hidden text-[10px] font-bold text-green-700 bg-green-100 px-2 py-0.5 rounded-full">✓ Saved</span>
                    </div>

                    {{-- Saved UPI card --}}
                    <div id="upi-saved-card" class="hidden bg-white border border-purple-200 rounded-lg p-3">
                        <div class="flex justify-between items-start gap-3">
                            <div class="flex-1">
                                <p class="text-xs font-bold text-gray-700">UPI ID</p>
                                <p class="text-sm font-mono text-purple-700 mt-0.5" id="saved-upi-id">---</p>
                            </div>
                            <div id="saved-upi-qr" class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M3 3h7v7H3V3zm1 1v5h5V4H4zm1 1h3v3H5V5zm8-2h7v7h-7V3zm1 1v5h5V4h-5zm1 1h3v3h-3V5zM3 13h7v7H3v-7zm1 1v5h5v-5H4zm1 1h3v3H5v-3zm8 0h2v2h-2v-2zm0 4h2v2h-2v-2zm4-4h2v2h-2v-2zm0 4h2v2h-2v-2z"/></svg>
                            </div>
                            <button onclick="editUpiDetails()" class="text-purple-600 hover:text-purple-800 text-[11px] font-bold px-2 py-1 border border-purple-200 rounded-md self-start">Change</button>
                        </div>
                    </div>

                    {{-- UPI input form --}}
                    <div id="upi-input-form" class="space-y-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">UPI ID</label>
                            <input type="text" id="upi-id-input" placeholder="e.g. vendor@upi"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-300 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">QR Code Image <span class="text-gray-400">(optional)</span></label>
                            <div id="qr-upload-area" class="border-2 border-dashed border-purple-200 rounded-lg p-4 text-center cursor-pointer hover:border-purple-400 transition"
                                onclick="document.getElementById('qr-file-input').click()">
                                <svg class="w-8 h-8 text-purple-300 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="text-[11px] text-gray-400">Click to upload QR code</p>
                                <input type="file" id="qr-file-input" accept="image/*" class="hidden" onchange="previewQR(event)">
                            </div>
                            <div id="qr-preview-container" class="hidden mt-2 flex items-center gap-2">
                                <img id="qr-preview-img" src="" alt="QR" class="w-16 h-16 rounded border object-cover">
                                <button onclick="clearQR()" class="text-red-500 text-xs font-bold">Remove</button>
                            </div>
                        </div>
                        <button onclick="saveUpiDetails()" class="w-full py-2 bg-purple-600 text-white rounded-lg text-xs font-bold hover:bg-purple-700 transition">
                            Save UPI Details
                        </button>
                    </div>
                </div>

                {{-- Booking Hold Timer --}}
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                    <h4 class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-2">Booking Hold Duration</h4>
                    <p class="text-[11px] text-gray-500 mb-3">Booking will be released if payment is not received within this time.</p>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" onclick="selectHoldTime(15)" class="hold-time-btn py-2 text-xs font-bold border-2 rounded-lg transition" data-mins="15">15 min</button>
                        <button type="button" onclick="selectHoldTime(30)" class="hold-time-btn active-hold py-2 text-xs font-bold border-2 rounded-lg transition" data-mins="30">30 min</button>
                        <button type="button" onclick="selectHoldTime(60)" class="hold-time-btn py-2 text-xs font-bold border-2 rounded-lg transition" data-mins="60">1 hour</button>
                        <button type="button" onclick="selectHoldTime(120)" class="hold-time-btn py-2 text-xs font-bold border-2 rounded-lg transition" data-mins="120">2 hours</button>
                        <button type="button" onclick="selectHoldTime(1440)" class="hold-time-btn py-2 text-xs font-bold border-2 rounded-lg transition" data-mins="1440">1 day</button>
                        <button type="button" onclick="selectHoldTime(0)" class="hold-time-btn py-2 text-xs font-bold border-2 rounded-lg transition" data-mins="0">No limit</button>
                    </div>
                    <p id="hold-time-label" class="text-[11px] text-amber-600 font-semibold mt-2 text-center">Hold for 30 minutes</p>
                </div>

                {{-- Totals --}}
                <div class="pt-4 border-t border-dashed space-y-3">
                    <div class="flex justify-between text-xs text-gray-500"><span>Subtotal</span><span id="side-sub-total">₹0</span></div>
                    <div class="flex justify-between text-xs text-red-500"><span>Discount</span><span id="side-discount-display">-₹0</span></div>
                    <div class="flex justify-between text-xs text-gray-500 font-bold"><span>Tax (GST 18%)</span><span id="side-tax">₹0</span></div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="font-black text-gray-900">Final Amount</span>
                        <span id="side-grand-total" class="text-xl font-black text-[#2D5A43]">₹0</span>
                    </div>
                </div>

                <button id="create-booking-btn"
                    class="w-full py-4 bg-[#2D5A43] text-white rounded-xl font-bold shadow-lg hover:bg-opacity-90 transition active:scale-[0.98] flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    Finalize Booking
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Booking Confirmed Modal with Timer --}}
<div id="booking-confirmed-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="bg-[#2D5A43] px-6 py-5 text-white">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="font-black text-xl">Booking Created!</h3>
            </div>
            <p class="text-white/70 text-sm">Invoice <span id="modal-invoice-num" class="font-bold text-white"></span></p>
        </div>

        <div class="p-6 space-y-4">

            {{-- Timer --}}
            <div id="payment-timer-block" class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center">
                <p class="text-[11px] text-amber-600 font-bold uppercase tracking-wider mb-1">Payment Due In</p>
                <div id="countdown-display" class="text-3xl font-black text-amber-700 tracking-widest font-mono">--:--:--</div>
                <div class="mt-2 h-2 bg-amber-100 rounded-full overflow-hidden">
                    <div id="countdown-bar" class="h-full bg-amber-500 rounded-full transition-all duration-1000" style="width:100%"></div>
                </div>
            </div>

            <div id="no-timer-block" class="hidden bg-green-50 border border-green-200 rounded-xl p-3 text-center">
                <p class="text-sm text-green-700 font-bold">Booking is open indefinitely</p>
            </div>

            {{-- Payment info based on mode --}}
            <div id="modal-bank-info" class="hidden bg-blue-50 border border-blue-100 rounded-xl p-4 space-y-2">
                <h4 class="text-xs font-bold text-blue-700 uppercase tracking-wider">Bank Transfer Details</h4>
                <div class="grid grid-cols-2 gap-y-1 text-xs">
                    <span class="text-gray-500">Bank Name</span><span id="modal-bank-name" class="font-bold text-gray-800"></span>
                    <span class="text-gray-500">Account No.</span><span id="modal-bank-acc" class="font-bold font-mono text-gray-800"></span>
                    <span class="text-gray-500">Account Holder</span><span id="modal-bank-holder" class="font-bold text-gray-800"></span>
                    <span class="text-gray-500">IFSC Code</span><span id="modal-bank-ifsc" class="font-bold font-mono text-gray-800"></span>
                </div>
                <p class="text-[10px] text-blue-500 mt-1">Use the booking invoice number as payment reference.</p>
            </div>

            <div id="modal-upi-info" class="hidden bg-purple-50 border border-purple-100 rounded-xl p-4 space-y-2">
                <h4 class="text-xs font-bold text-purple-700 uppercase tracking-wider">UPI Payment Details</h4>
                <div class="flex items-center gap-4">
                    <div id="modal-upi-qr" class="w-20 h-20 rounded-xl border bg-white overflow-hidden flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8 text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M3 3h7v7H3V3zm1 1v5h5V4H4zm1 1h3v3H5V5zm8-2h7v7h-7V3zm1 1v5h5V4h-5zm1 1h3v3h-3V5zM3 13h7v7H3v-7zm1 1v5h5v-5H4zm1 1h3v3H5v-3zm8 0h2v2h-2v-2zm0 4h2v2h-2v-2zm4-4h2v2h-2v-2zm0 4h2v2h-2v-2z"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500">UPI ID</p>
                        <p id="modal-upi-id" class="text-sm font-bold font-mono text-purple-700"></p>
                    </div>
                </div>
            </div>

            {{-- Total --}}
            <div class="flex justify-between items-center bg-gray-50 rounded-xl px-4 py-3">
                <span class="text-sm text-gray-600 font-medium">Amount Due</span>
                <span id="modal-total-amount" class="text-xl font-black text-[#2D5A43]"></span>
            </div>

            <div class="flex gap-3 pt-2">
                <button onclick="closeConfirmedModal()" class="flex-1 py-3 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50">Close</button>
                <button onclick="window.location.href='/vendor/pos/bookings'" class="flex-1 py-3 bg-[#2D5A43] text-white rounded-xl text-sm font-bold hover:bg-opacity-90">
                    View Bookings
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.payment-mode-btn {
    border-color: #e5e7eb;
    color: #6b7280;
    background: #fff;
}
.payment-mode-btn:hover {
    border-color: #2D5A43;
    color: #2D5A43;
    background: #f0fdf4;
}
.payment-mode-btn.active-mode {
    border-color: #2D5A43;
    color: #2D5A43;
    background: #f0fdf4;
    box-shadow: 0 0 0 1px #2D5A43;
}
.hold-time-btn {
    border-color: #e5e7eb;
    color: #6b7280;
    background: #fff;
}
.hold-time-btn:hover {
    border-color: #d97706;
    color: #d97706;
    background: #fffbeb;
}
.hold-time-btn.active-hold {
    border-color: #d97706;
    color: #d97706;
    background: #fffbeb;
    box-shadow: 0 0 0 1px #d97706;
}
</style>

<script>
/* ======================================================
   PREVIEW SCREEN — Payment Mode + Timer + Bank/UPI Logic
   ====================================================== */

// State
let selectedPaymentMode = 'cash';
let selectedHoldMinutes  = 30;
let savedBankDetails     = null;
let savedUpiDetails      = null;
let countdownInterval    = null;

/* ── calculateFinalTotals (also called from create.blade.php) ── */
function calculateFinalTotals() {
    const discountVal   = parseFloat(document.getElementById('pos-discount')?.value || 0);
    const taxableAmount = Math.max(0, globalBaseAmount - discountVal);
    const tax           = taxableAmount * 0.18;
    const grandTotal    = taxableAmount + tax;
    const fmt = v => typeof formatINR === 'function' ? formatINR(v) : `₹${Math.round(v)}`;
    document.getElementById('side-discount-display').innerText = `- ${fmt(discountVal)}`;
    document.getElementById('side-tax').innerText              = fmt(tax);
    document.getElementById('side-grand-total').innerText      = fmt(grandTotal);
}
window.calculateFinalTotals = calculateFinalTotals;

/* ── Payment mode selection ── */
function selectPaymentMode(mode) {
    selectedPaymentMode = mode;
    document.querySelectorAll('.payment-mode-btn').forEach(btn => {
        btn.classList.toggle('active-mode', btn.dataset.mode === mode);
    });
    document.getElementById('bank-details-panel').classList.toggle('hidden', mode !== 'bank_transfer');
    document.getElementById('upi-details-panel').classList.toggle('hidden', mode !== 'online');

    if (mode === 'bank_transfer') loadSavedBankDetails();
    if (mode === 'online')        loadSavedUpiDetails();
}

/* ── Hold time selection ── */
function selectHoldTime(mins) {
    selectedHoldMinutes = mins;
    document.querySelectorAll('.hold-time-btn').forEach(btn => {
        btn.classList.toggle('active-hold', parseInt(btn.dataset.mins) === mins);
    });
    const labels = { 0: 'No time limit', 15: '15 minutes', 30: '30 minutes', 60: '1 hour', 120: '2 hours', 1440: '1 day' };
    document.getElementById('hold-time-label').innerText = `Hold for ${labels[mins] || mins + ' min'}`;
}

/* ── IFSC Fetch ── */
async function fetchBankFromIFSC() {
    const ifsc = document.getElementById('bank-ifsc').value.trim();
    if (ifsc.length !== 11) return;
    const el = document.getElementById('ifsc-result');
    el.classList.remove('hidden');
    el.innerText = 'Fetching...';
    try {
        const res  = await fetch(`https://ifsc.razorpay.com/${ifsc}`);
        if (!res.ok) throw new Error('Not found');
        const data = await res.json();
        el.innerText = `${data.BANK} — ${data.BRANCH}, ${data.CITY}`;
        el.style.color = '#1d4ed8';
        // Pre-fill bank name in holder placeholder as hint
        document.getElementById('bank-acc-holder').placeholder = `Account holder at ${data.BANK}`;
    } catch (e) {
        el.innerText = 'Invalid IFSC or not found';
        el.style.color = '#dc2626';
    }
}

/* ── Save bank details to server ── */
async function saveBankDetails() {
    const ifsc   = document.getElementById('bank-ifsc').value.trim();
    const acc    = document.getElementById('bank-acc-number').value.trim();
    const holder = document.getElementById('bank-acc-holder').value.trim();
    const ifscEl = document.getElementById('ifsc-result');
    const bankName = ifscEl.innerText.split('—')[0].trim() || 'Bank';

    if (!ifsc || !acc || !holder) { showToast('Please fill all bank fields', 'warning'); return; }

    try {
        const res = await fetch('/vendor/pos/api/payment-details', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: JSON.stringify({ type: 'bank', ifsc_code: ifsc, account_number: acc, account_holder: holder, bank_name: bankName })
        });
        const result = await res.json();
        if (!res.ok) throw new Error(result.message || 'Save failed');
        savedBankDetails = result.data;
        renderSavedBankCard(savedBankDetails);
        showToast('Bank details saved!', 'success');
    } catch (e) {
        showToast(e.message, 'error');
    }
}

function renderSavedBankCard(d) {
    if (!d) return;
    document.getElementById('saved-bank-name').innerText   = d.bank_name || '---';
    document.getElementById('saved-bank-acc').innerText    = 'A/C: ' + (d.account_number || '---');
    document.getElementById('saved-bank-holder').innerText = 'Holder: ' + (d.account_holder || '---');
    document.getElementById('saved-bank-ifsc').innerText   = 'IFSC: ' + (d.ifsc_code || '---');
    document.getElementById('bank-saved-card').classList.remove('hidden');
    document.getElementById('bank-input-form').classList.add('hidden');
    document.getElementById('bank-saved-badge').classList.remove('hidden');
}

function editBankDetails() {
    document.getElementById('bank-saved-card').classList.add('hidden');
    document.getElementById('bank-input-form').classList.remove('hidden');
}

async function loadSavedBankDetails() {
    try {
        const res  = await fetch('/vendor/pos/api/payment-details?type=bank', { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success && data.data) {
            savedBankDetails = data.data;
            // Pre-fill form
            document.getElementById('bank-ifsc').value       = data.data.ifsc_code || '';
            document.getElementById('bank-acc-number').value = data.data.account_number || '';
            document.getElementById('bank-acc-holder').value = data.data.account_holder || '';
            if (data.data.ifsc_code) {
                document.getElementById('ifsc-result').innerText = data.data.bank_name || '';
                document.getElementById('ifsc-result').classList.remove('hidden');
            }
            renderSavedBankCard(data.data);
        }
    } catch (e) { /* no saved details yet */ }
}

/* ── UPI ── */
function previewQR(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('qr-preview-img').src = e.target.result;
        document.getElementById('qr-preview-container').classList.remove('hidden');
        document.getElementById('qr-upload-area').classList.add('hidden');
    };
    reader.readAsDataURL(file);
}

function clearQR() {
    document.getElementById('qr-file-input').value = '';
    document.getElementById('qr-preview-img').src = '';
    document.getElementById('qr-preview-container').classList.add('hidden');
    document.getElementById('qr-upload-area').classList.remove('hidden');
}

async function saveUpiDetails() {
    const upiId = document.getElementById('upi-id-input').value.trim();
    if (!upiId) { showToast('Please enter UPI ID', 'warning'); return; }

    const formData = new FormData();
    formData.append('type', 'upi');
    formData.append('upi_id', upiId);
    const qrFile = document.getElementById('qr-file-input').files[0];
    if (qrFile) formData.append('qr_image', qrFile);

    try {
        const res = await fetch('/vendor/pos/api/payment-details', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: formData
        });
        const result = await res.json();
        if (!res.ok) throw new Error(result.message || 'Save failed');
        savedUpiDetails = result.data;
        renderSavedUpiCard(savedUpiDetails);
        showToast('UPI details saved!', 'success');
    } catch (e) {
        showToast(e.message, 'error');
    }
}

function renderSavedUpiCard(d) {
    if (!d) return;
    document.getElementById('saved-upi-id').innerText = d.upi_id || '---';
    if (d.qr_image_url) {
        document.getElementById('saved-upi-qr').innerHTML = `<img src="${d.qr_image_url}" class="w-full h-full object-cover">`;
    }
    document.getElementById('upi-saved-card').classList.remove('hidden');
    document.getElementById('upi-input-form').classList.add('hidden');
    document.getElementById('upi-saved-badge').classList.remove('hidden');
}

function editUpiDetails() {
    document.getElementById('upi-saved-card').classList.add('hidden');
    document.getElementById('upi-input-form').classList.remove('hidden');
}

async function loadSavedUpiDetails() {
    try {
        const res  = await fetch('/vendor/pos/api/payment-details?type=upi', { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success && data.data) {
            savedUpiDetails = data.data;
            document.getElementById('upi-id-input').value = data.data.upi_id || '';
            renderSavedUpiCard(data.data);
        }
    } catch (e) { /* no saved details yet */ }
}

/* ── Countdown Timer ── */
function startCountdown(expiresAt) {
    if (countdownInterval) clearInterval(countdownInterval);
    const total  = new Date(expiresAt) - new Date();
    const endMs  = Date.now() + total;

    function tick() {
        const remaining = endMs - Date.now();
        if (remaining <= 0) {
            clearInterval(countdownInterval);
            document.getElementById('countdown-display').innerText = '00:00:00';
            document.getElementById('countdown-bar').style.width   = '0%';
            return;
        }
        const h  = Math.floor(remaining / 3600000);
        const m  = Math.floor((remaining % 3600000) / 60000);
        const s  = Math.floor((remaining % 60000) / 1000);
        document.getElementById('countdown-display').innerText =
            [h, m, s].map(n => String(n).padStart(2, '0')).join(':');
        document.getElementById('countdown-bar').style.width =
            Math.max(0, (remaining / total * 100)).toFixed(1) + '%';
    }
    tick();
    countdownInterval = setInterval(tick, 1000);
}

function closeConfirmedModal() {
    document.getElementById('booking-confirmed-modal').classList.add('hidden');
    if (countdownInterval) clearInterval(countdownInterval);
}

function showBookingConfirmedModal(booking) {
    const modal     = document.getElementById('booking-confirmed-modal');
    const fmt       = v => typeof formatINR === 'function' ? formatINR(v) : `₹${Math.round(v)}`;
    const discount  = parseFloat(document.getElementById('pos-discount')?.value || 0);
    const taxable   = Math.max(0, globalBaseAmount - discount);
    const grandTotal= taxable + taxable * 0.18;

    document.getElementById('modal-invoice-num').innerText  = `#${booking.invoice_number ?? booking.id}`;
    document.getElementById('modal-total-amount').innerText = fmt(grandTotal);

    // Timer
    if (booking.hold_expiry_at && selectedHoldMinutes > 0) {
        document.getElementById('payment-timer-block').classList.remove('hidden');
        document.getElementById('no-timer-block').classList.add('hidden');
        startCountdown(booking.hold_expiry_at);
    } else {
        document.getElementById('payment-timer-block').classList.add('hidden');
        document.getElementById('no-timer-block').classList.remove('hidden');
    }

    // Payment info
    document.getElementById('modal-bank-info').classList.add('hidden');
    document.getElementById('modal-upi-info').classList.add('hidden');

    if (selectedPaymentMode === 'bank_transfer' && savedBankDetails) {
        document.getElementById('modal-bank-name').innerText   = savedBankDetails.bank_name   || '--';
        document.getElementById('modal-bank-acc').innerText    = savedBankDetails.account_number || '--';
        document.getElementById('modal-bank-holder').innerText = savedBankDetails.account_holder || '--';
        document.getElementById('modal-bank-ifsc').innerText   = savedBankDetails.ifsc_code   || '--';
        document.getElementById('modal-bank-info').classList.remove('hidden');
    }
    if (selectedPaymentMode === 'online' && savedUpiDetails) {
        document.getElementById('modal-upi-id').innerText = savedUpiDetails.upi_id || '--';
        if (savedUpiDetails.qr_image_url) {
            document.getElementById('modal-upi-qr').innerHTML = `<img src="${savedUpiDetails.qr_image_url}" class="w-full h-full object-cover">`;
        }
        document.getElementById('modal-upi-info').classList.remove('hidden');
    }

    modal.classList.remove('hidden');
}

/* ── Create Booking ── */
document.addEventListener('DOMContentLoaded', () => {
    selectPaymentMode('cash');
    selectHoldTime(30);

    document.getElementById('create-booking-btn')?.addEventListener('click', async (e) => {
        // Prevent accidental trigger if modal is closed
        if (document.getElementById('booking-confirmed-modal').classList.contains('hidden') === false) return;
        const btn = document.getElementById('create-booking-btn');
        if (btn.disabled) return;

        // Validate payment details for non-cash modes
        if (selectedPaymentMode === 'bank_transfer' && !savedBankDetails) {
            showToast('Please save bank details before finalizing', 'warning'); return;
        }
        if (selectedPaymentMode === 'online' && !savedUpiDetails) {
            showToast('Please save UPI details before finalizing', 'warning'); return;
        }

        // Build per-hoarding items
        const hoardingItems = Array.from(selectedHoardings.entries()).map(([id, h]) => ({
            hoarding_id:        id,
            start_date:         h.startDate,
            end_date:           h.endDate,
            price_per_month:    h.price_per_month,
            type:               h.type || 'ooh',
            total_slots_per_day: h.total_slots_per_day ?? null,
        }));

        let sDate = null, eDate = null;
        selectedHoardings.forEach(h => {
            if (!sDate || h.startDate < sDate) sDate = h.startDate;
            if (!eDate || h.endDate > eDate)   eDate = h.endDate;
        });

        const discountVal = parseFloat(document.getElementById('pos-discount')?.value || 0);

        const payload = {
            hoarding_ids:       Array.from(selectedHoardings.keys()),
            hoarding_items:     hoardingItems,
            customer_id:        selectedCustomer?.id   ?? null,
            customer_name:      selectedCustomer?.name ?? '',
            customer_phone:     selectedCustomer?.phone ?? '',
            customer_email:     selectedCustomer?.email ?? '',
            customer_address:   selectedCustomer?.address ?? 'N/A',
            customer_gstin:     selectedCustomer?.gstin ?? '',
            booking_type:       hoardingItems.some(i => i.type?.toUpperCase() === 'DOOH') ? 'dooh' : 'ooh',
            start_date:         sDate,
            end_date:           eDate,
            base_amount:        globalBaseAmount,
            discount_amount:    discountVal,
            payment_mode:       selectedPaymentMode,
            payment_reference:  '',
            payment_notes:      'POS Booking',
            hold_minutes:       selectedHoldMinutes,
            payment_details_type: selectedPaymentMode !== 'cash' ? selectedPaymentMode : null,
        };

        btn.disabled = true;
        btn.innerHTML = `<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Creating...`;

        try {
            const res    = await fetch('/vendor/pos/api/bookings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', 'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (!res.ok) throw new Error(result.message || Object.values(result.errors || {}).flat().join('\n') || 'Booking failed');

            showBookingConfirmedModal(result.data);

        } catch (e) {
            showToast(e.message, 'error');
            btn.disabled = false;
            btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg> Finalize Booking`;
        }
    });

    // Timer notification logic
    let timerNotifications = [];
    function showTimerNotification(booking) {
        // Remove existing for same booking
        timerNotifications = timerNotifications.filter(n => n.bookingId !== booking.id);
        // Create notification
        const notif = document.createElement('div');
        notif.className = 'fixed z-50 right-6 bottom-6 bg-amber-100 border border-amber-300 rounded-xl shadow-lg p-4 flex items-center gap-4 animate-fade-in';
        notif.style.minWidth = '260px';
        notif.innerHTML = `
            <div class="flex-1">
                <div class="font-bold text-amber-700 text-xs mb-1">Payment Due In</div>
                <div class="font-mono text-lg font-black text-amber-700" id="notif-timer-${booking.id}">--:--:--</div>
                <div class="text-xs text-gray-500 mt-1">Invoice #${booking.invoice_number ?? booking.id}</div>
            </div>
            <button class="ml-2 text-xs text-gray-500 hover:text-red-600 font-bold" onclick="this.parentElement.remove();localStorage.removeItem('activeBookingTimer');">✕</button>
        `;
        document.body.appendChild(notif);
        // Timer logic
        let interval = setInterval(() => {
            const remaining = new Date(booking.hold_expiry_at) - new Date();
            if (remaining <= 0) {
                notif.querySelector(`#notif-timer-${booking.id}`).innerText = '00:00:00';
                clearInterval(interval);
                setTimeout(() => notif.remove(), 3000);
                localStorage.removeItem('activeBookingTimer');
                return;
            }
            const h = Math.floor(remaining / 3600000);
            const m = Math.floor((remaining % 3600000) / 60000);
            const s = Math.floor((remaining % 60000) / 1000);
            notif.querySelector(`#notif-timer-${booking.id}`).innerText = [h, m, s].map(n => String(n).padStart(2, '0')).join(':');
        }, 1000);
        timerNotifications.push({ bookingId: booking.id, notif, interval });
    }

    // Patch closeConfirmedModal to show timer notification
    const origCloseConfirmedModal = window.closeConfirmedModal;
    window.closeConfirmedModal = function() {
        const modal = document.getElementById('booking-confirmed-modal');
        if (!modal.classList.contains('hidden')) {
            // If timer is running, show notification
            const bookingId = document.getElementById('modal-invoice-num')?.innerText?.replace('#', '');
            const timerBlock = document.getElementById('payment-timer-block');
            if (timerBlock && !timerBlock.classList.contains('hidden') && bookingId) {
                // Get hold_expiry_at from last booking (store in window)
                if (window.lastConfirmedBooking && window.lastConfirmedBooking.hold_expiry_at) {
                    // Store timer in localStorage for cross-page
                    localStorage.setItem('activeBookingTimer', JSON.stringify({
                        id: window.lastConfirmedBooking.id,
                        invoice_number: window.lastConfirmedBooking.invoice_number,
                        hold_expiry_at: window.lastConfirmedBooking.hold_expiry_at
                    }));
                    showTimerNotification(window.lastConfirmedBooking);
                }
            }
        }
            // On page load, show timer notification if present in localStorage
            document.addEventListener('DOMContentLoaded', () => {
                const timerData = localStorage.getItem('activeBookingTimer');
                if (timerData) {
                    try {
                        const booking = JSON.parse(timerData);
                        // Only show if timer is still valid
                        if (booking && booking.hold_expiry_at && new Date(booking.hold_expiry_at) > new Date()) {
                            showTimerNotification(booking);
                        } else {
                            localStorage.removeItem('activeBookingTimer');
                        }
                    } catch {}
                }
            });
        // Reset the create button state when closing modal
        const btn = document.getElementById('create-booking-btn');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg> Finalize Booking`;
        }
        // Redirect to booking details if available
        if (window.lastConfirmedBooking && window.lastConfirmedBooking.id) {
            window.location.href = `/vendor/pos/bookings/${window.lastConfirmedBooking.id}`;
            return;
        }
        origCloseConfirmedModal();
    };

    // Patch showBookingConfirmedModal to store last booking
    const origShowBookingConfirmedModal = window.showBookingConfirmedModal;
    window.showBookingConfirmedModal = function(booking) {
        window.lastConfirmedBooking = booking;
        origShowBookingConfirmedModal(booking);
    };
});
</script>
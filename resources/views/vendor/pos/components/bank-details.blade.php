{{-- Bank Details Panel --}}
<div id="bank-details-panel" class="hidden space-y-2 bg-blue-50 border border-blue-100 rounded-xl p-3">

    <div class="flex items-center justify-between mb-1">
        <h4 class="text-sm font-bold text-blue-700 uppercase tracking-wider">Bank Transfer Details</h4>
        <span id="bank-count-badge" class="text-[10px] font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">0 / 5</span>
    </div>

    {{-- Dropdown Selector --}}
    <div id="bd-panel" class="bg-white border border-blue-100 rounded-xl overflow-hidden">

        {{-- Trigger row --}}
        <div id="bd-trigger" onclick="toggleBankDropdown()"
            class="flex items-center justify-between gap-2 px-3 py-2.5 cursor-pointer hover:bg-blue-50 transition select-none">
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <div class="w-7 h-7 rounded-md bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                        <path d="M12 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path d="M6 12h.01M18 12h.01"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p id="bd-selected-name" class="text-sm font-bold text-gray-800 truncate">Select a bank account</p>
                    <p id="bd-selected-sub" class="text-[10px] text-gray-400 font-mono truncate">No bank selected</p>
                </div>
            </div>
            <svg id="bd-chevron" class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform duration-200"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </div>

        {{-- Dropdown body --}}
        <div id="bd-dropdown" class="border-t border-blue-50 overflow-hidden max-h-0 transition-all duration-200 ease-in-out">

            {{-- Bank list --}}
            <div id="bd-list" class="p-1.5 space-y-0.5"></div>

            {{-- Divider --}}
            <div class="border-t border-blue-50 mx-2"></div>

            {{-- Add row --}}
            <div id="bd-add-row" onclick="openBankAddForm()"
                class="flex items-center gap-2 px-2.5 py-2 mx-1.5 mb-1.5 mt-1 rounded-lg cursor-pointer text-blue-600 hover:bg-blue-50 transition">
                <div class="w-4 h-4 rounded-full border-[1.5px] border-current flex items-center justify-center text-sm font-bold leading-none flex-shrink-0">+</div>
                <span id="bd-add-label" class="text-sm font-bold">Add bank account</span>
            </div>

            {{-- Inline Add/Edit Form --}}
            <div id="bd-form" class="hidden border-t border-blue-100 p-3 space-y-2.5 bg-blue-50/50">
                <input type="hidden" id="bd-edit-id" value="">

                <div class="flex items-center justify-between mb-1">
                    <p id="bd-form-title" class="text-[10px] font-bold text-blue-700 uppercase tracking-wider">Add New Bank</p>
                    <button onclick="closeBankForm()" class="text-gray-400 hover:text-gray-600 text-sm leading-none font-bold px-1">✕</button>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">IFSC Code</label>
                    <input type="text" id="bank-ifsc" placeholder="e.g. SBIN0001234" maxlength="11"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono uppercase bg-white focus:ring-2 focus:ring-blue-300 outline-none"
                        oninput="this.value=this.value.toUpperCase()" onblur="fetchBankFromIFSC()">
                    <div id="ifsc-result" class="hidden mt-1 text-[10px] text-blue-700 bg-blue-100 rounded px-2 py-1 font-medium"></div>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">Account Number</label>
                    <input type="text" id="bank-acc-number" placeholder="Enter account number"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono bg-white focus:ring-2 focus:ring-blue-300 outline-none"
                        oninput="clearBankFieldError()">
                    <div id="bank-acc-error" class="hidden mt-1 text-[10px] text-red-600 font-medium"></div>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">Account Holder Name</label>
                    <input type="text" id="bank-acc-holder" placeholder="As per bank records"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-300 outline-none">
                </div>

                <div class="flex gap-2 pt-1">
                    <button onclick="closeBankForm()"
                        class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button onclick="saveBankDetails()"
                        class="flex-1 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition">
                        Save Bank
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/* ================================================================
   MULTI-BANK STATE
================================================================ */
let bankList        = [];
let selectedBankId  = null;
let _bdDropOpen     = false;

/* ── Helpers ── */
function _bdBankLabel(b) {
    return (b.bank_name || 'Bank') + ' ···' + String(b.account_number).slice(-4);
}
function _bdBankSub(b) {
    return b.account_number + ' · ' + b.ifsc_code;
}

/* ── Trigger display ── */
function _bdUpdateTrigger() {
    const b = bankList.find(x => x.id === selectedBankId);
    const nameEl = document.getElementById('bd-selected-name');
    const subEl  = document.getElementById('bd-selected-sub');
    if (b) {
        nameEl.textContent = _bdBankLabel(b);
        subEl.textContent  = _bdBankSub(b);
    } else {
        nameEl.textContent = 'Select a bank account';
        subEl.textContent  = 'No bank selected';
    }
}

/* ── Toggle dropdown open/close ── */
function toggleBankDropdown() {
    _bdDropOpen = !_bdDropOpen;
    const dropdown = document.getElementById('bd-dropdown');
    const chevron  = document.getElementById('bd-chevron');
    if (_bdDropOpen) {
        dropdown.style.maxHeight = dropdown.scrollHeight + 600 + 'px';
    } else {
        dropdown.style.maxHeight = '0';
        closeBankForm();     // close form when collapsing
    }
    chevron.style.transform = _bdDropOpen ? 'rotate(180deg)' : '';
}

/* ── Re-measure dropdown height after list changes ── */
function _bdRefreshHeight() {
    if (!_bdDropOpen) return;
    const dropdown = document.getElementById('bd-dropdown');
    dropdown.style.maxHeight = 'none';    // allow natural height measurement
    const h = dropdown.scrollHeight;
    dropdown.style.maxHeight = h + 'px';
}

/* ── Render bank list inside dropdown ── */
function renderBankList() {
    const container  = document.getElementById('bd-list');
    const countBadge = document.getElementById('bank-count-badge');
    const addLabel   = document.getElementById('bd-add-label');
    const addRow     = document.getElementById('bd-add-row');
    if (!container) return;

    countBadge.innerText = `${bankList.length} / 5`;
    container.innerHTML  = '';

    if (!bankList.length) {
        container.innerHTML = '<p class="text-[10px] text-gray-400 text-center py-3 px-2">No banks saved yet</p>';
        addLabel.textContent = 'Add bank account';
        addRow.style.display = '';
        _bdUpdateTrigger();
        _bdRefreshHeight();
        return;
    }

    addLabel.textContent = bankList.length >= 5 ? 'Max 5 banks reached' : 'Add another bank';
    addRow.style.display  = bankList.length >= 5 ? 'none' : '';

    bankList.forEach(bank => {
        const isSel    = bank.id === selectedBankId;
        const isDefault = bank.is_default;

        const row = document.createElement('div');
        row.className = `group flex items-center gap-2 px-2.5 py-2 rounded-lg cursor-pointer transition ${
            isSel ? 'bg-blue-50 border border-blue-200' : 'hover:bg-gray-50 border border-transparent'
        }`;

        row.innerHTML = `
            <div class="w-3.5 h-3.5 rounded-full border-[1.5px] flex-shrink-0 flex items-center justify-center transition
                ${isSel ? 'border-blue-600 bg-blue-600' : 'border-gray-300'}"
                onclick="selectBank(${bank.id})">
                ${isSel ? '<span class="w-1.5 h-1.5 rounded-full bg-white block"></span>' : ''}
            </div>
            <div class="flex-1 min-w-0" onclick="selectBank(${bank.id})">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-[11px] font-bold text-gray-800 truncate">${_bdBankLabel(bank)}</span>
                    ${isDefault ? `<span class="text-[9px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded">★ default</span>` : ''}
                </div>
                <p class="text-[10px] text-gray-500 truncate">${bank.account_holder}</p>
            </div>
            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                ${!isDefault ? `<button onclick="event.stopPropagation();setBankDefault(${bank.id})"
                    class="text-[9px] font-bold text-emerald-600 border border-emerald-200 rounded px-1.5 py-0.5 hover:bg-emerald-50 transition">
                    Default</button>` : ''}
                <button onclick="event.stopPropagation();editBank(${bank.id})"
                    class="text-[9px] font-bold text-blue-600 border border-blue-200 rounded px-1.5 py-0.5 hover:bg-blue-50 transition">
                    Edit</button>
                ${bankList.length > 1 ? `<button onclick="event.stopPropagation();deleteBankAccount(${bank.id})"
                    class="text-[9px] font-bold text-red-500 border border-red-200 rounded px-1.5 py-0.5 hover:bg-red-50 transition">
                    Delete</button>` : ''}
            </div>`;

        container.appendChild(row);
    });

    _bdUpdateTrigger();
    _bdRefreshHeight();
}

/* ── Select a bank for this booking ── */
function selectBank(id) {
    selectedBankId   = id;
    savedBankDetails = bankList.find(b => b.id === id) || null;
    renderBankList();
}

/* ── Open add form ── */
function openBankAddForm() {
    if (bankList.length >= 5) { showToast('Maximum 5 bank accounts allowed', 'warning'); return; }
    _bdClearForm();
    document.getElementById('bd-edit-id').value    = '';
    document.getElementById('bd-form-title').innerText = 'Add New Bank';
    document.getElementById('bd-form').classList.remove('hidden');
    document.getElementById('bd-add-row').style.display = 'none';
    _bdRefreshHeight();
}

/* ── Open edit form ── */
function editBank(id) {
    const bank = bankList.find(b => b.id === id);
    if (!bank) return;

    document.getElementById('bd-edit-id').value      = id;
    document.getElementById('bank-ifsc').value        = bank.ifsc_code      || '';
    document.getElementById('bank-acc-number').value  = bank.account_number || '';
    document.getElementById('bank-acc-holder').value  = bank.account_holder || '';
    window._resolvedBankName                          = bank.bank_name      || null;

    const ifscEl = document.getElementById('ifsc-result');
    ifscEl.innerText = bank.bank_name || '';
    ifscEl.classList.toggle('hidden', !bank.bank_name);

    document.getElementById('bd-form-title').innerText = 'Edit Bank';
    document.getElementById('bd-form').classList.remove('hidden');
    document.getElementById('bd-add-row').style.display = 'none';
    _bdRefreshHeight();
}

/* ── Close form ── */
function closeBankForm() {
    _bdClearForm();
    document.getElementById('bd-form').classList.add('hidden');
    document.getElementById('bd-add-row').style.display = bankList.length >= 5 ? 'none' : '';
    _bdRefreshHeight();
}

/* ── Clear form fields ── */
function _bdClearForm() {
    ['bd-edit-id', 'bank-ifsc', 'bank-acc-number', 'bank-acc-holder'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    window._resolvedBankName = null;
    const ifscEl  = document.getElementById('ifsc-result');
    const accErr  = document.getElementById('bank-acc-error');
    if (ifscEl) { ifscEl.classList.add('hidden'); ifscEl.innerText = ''; }
    if (accErr) { accErr.classList.add('hidden'); accErr.innerText = ''; }
}

function clearBankFieldError() {
    const el = document.getElementById('bank-acc-error');
    if (el) { el.classList.add('hidden'); el.innerText = ''; }
}

/* ── IFSC Fetch (same as before, renamed nothing) ── */
async function fetchBankFromIFSC() {
    const ifsc = document.getElementById('bank-ifsc').value.trim();
    if (ifsc.length !== 11) return;
    const el = document.getElementById('ifsc-result');
    el.classList.remove('hidden');
    el.innerText = 'Fetching…';
    el.style.color = '#1d4ed8';
    try {
        const res  = await fetch(`https://ifsc.razorpay.com/${ifsc}`);
        if (!res.ok) throw new Error('Not found');
        const data = await res.json();
        el.innerText = `${data.BANK} — ${data.BRANCH}, ${data.CITY}`;
        el.style.color = '#1d4ed8';
        window._resolvedBankName = data.BANK;
        document.getElementById('bank-acc-holder').placeholder = `Account holder at ${data.BANK}`;
    } catch (e) {
        el.innerText = 'Invalid IFSC or not found';
        el.style.color = '#dc2626';
        window._resolvedBankName = null;
    }
    _bdRefreshHeight();
}

/* ── Save (create or update) with duplicate account validation ── */
async function saveBankDetails() {
    const editId = document.getElementById('bd-edit-id').value;
    const ifsc   = document.getElementById('bank-ifsc').value.trim();
    const acc    = document.getElementById('bank-acc-number').value.trim();
    const holder = document.getElementById('bank-acc-holder').value.trim();

    if (!ifsc || !acc || !holder) {
        showToast('Please fill IFSC, Account Number and Holder Name', 'warning');
        return;
    }
    if (ifsc.length !== 11) {
        showToast('IFSC code must be exactly 11 characters', 'warning');
        return;
    }

    const bankName = window._resolvedBankName
        || (document.getElementById('ifsc-result').innerText.split('—')[0]?.trim())
        || 'Bank';

    const payload = {
        ifsc_code:      ifsc,
        account_number: acc,
        account_holder: holder,
        bank_name:      bankName,
    };

    const base   = `${window.POS_BASE_PATH || '/vendor/pos'}/api/payment-details/banks`;
    const url    = editId ? `${base}/${editId}` : base;
    const method = editId ? 'PUT' : 'POST';

    try {
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type':  'application/json',
                'Accept':        'application/json',
                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify(payload),
        });
        const result = await res.json();

        // ── Server-side duplicate / validation errors ───────────
        if (!res.ok) {
            if (result.errors?.account_number) {
                const errEl = document.getElementById('bank-acc-error');
                errEl.innerText = result.errors.account_number[0];
                errEl.classList.remove('hidden');
                _bdRefreshHeight();
                return;
            }
            throw new Error(result.message || 'Save failed');
        }

        showToast(editId ? 'Bank updated!' : 'Bank added!', 'success');
        await loadSavedBankDetails();

        // Auto-select the newly added bank
        if (!editId && result.data?.id) {
            selectBank(result.data.id);
        }
        closeBankForm();

    } catch (e) {
        showToast(e.message, 'error');
    }
}

/* ── Delete ── */
async function deleteBankAccount(id) {
    const bank = bankList.find(b => b.id === id);
    if (!bank) return;
    if (!confirm(`Remove ${bank.bank_name || 'this bank'}? This cannot be undone.`)) return;

    try {
        const res = await fetch(
            `${window.POS_BASE_PATH || '/vendor/pos'}/api/payment-details/banks/${id}`,
            {
                method: 'DELETE',
                headers: {
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
            }
        );
        const result = await res.json();
        if (!res.ok) throw new Error(result.message || 'Delete failed');

        if (selectedBankId === id) {
            selectedBankId   = null;
            savedBankDetails = null;
        }
        showToast('Bank removed', 'success');
        await loadSavedBankDetails();

        if (!selectedBankId && bankList.length > 0) {
            const def = bankList.find(b => b.is_default) || bankList[0];
            if (def) selectBank(def.id);
        }
    } catch (e) {
        showToast(e.message, 'error');
    }
}

/* ── Set default ── */
async function setBankDefault(id) {
    try {
        const res = await fetch(
            `${window.POS_BASE_PATH || '/vendor/pos'}/api/payment-details/banks/${id}/set-default`,
            {
                method: 'POST',
                headers: {
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
            }
        );
        const result = await res.json();
        if (!res.ok) throw new Error(result.message || 'Failed');
        showToast('Default bank updated', 'success');
        await loadSavedBankDetails();
    } catch (e) {
        showToast(e.message, 'error');
    }
}

/* ── Load all banks ── */
async function loadSavedBankDetails() {
    try {
        const res  = await fetch(
            `${window.POS_BASE_PATH || '/vendor/pos'}/api/payment-details/banks`,
            { headers: { 'Accept': 'application/json' } }
        );
        const data = await res.json();

        if (data.success && Array.isArray(data.data)) {
            bankList = data.data;

            // Keep selection if bank still exists
            if (selectedBankId && !bankList.find(b => b.id === selectedBankId)) {
                selectedBankId   = null;
                savedBankDetails = null;
            }
            // Auto-select default if nothing selected
            if (!selectedBankId && bankList.length > 0) {
                const def = bankList.find(b => b.is_default) || bankList[0];
                selectedBankId   = def.id;
                savedBankDetails = def;
            }
        } else {
            bankList = [];
        }
        renderBankList();
    } catch (e) {
        console.error('loadSavedBankDetails error', e);
        bankList = [];
        renderBankList();
    }
}

/* ── Aliases kept for backward compat ── */
function editBankDetails() { /* superseded by editBank(id) */ }
</script>
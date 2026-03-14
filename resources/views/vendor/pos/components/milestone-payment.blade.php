{{--
    ╔══════════════════════════════════════════════════════════════╗
    ║  POS MILESTONE COMPONENT                                     ║
    ║  Include this in preview-screen.blade.php AFTER the         ║
    ║  POS Checkout card, inside the right panel:                 ║
    ║                                                              ║
    ║  @include('vendor.pos.components.milestone-payment')         ║
    ║                                                              ║
    ║  This component is FULLY ISOLATED:                           ║
    ║  - Reads globalBaseAmount (set by existing populatePreview)  ║
    ║  - Only fires on "Finalize Booking" AFTER existing logic     ║
    ║  - Zero impact on cash / single-payment flow                 ║
    ╚══════════════════════════════════════════════════════════════╝
--}}

{{-- ── MILESTONE TOGGLE CARD ─────────────────────────────────── --}}
<div id="milestone-card" class="bg-white rounded-md shadow-xl border border-gray-200 p-4 lg:p-6 space-y-4">

    {{-- Header Toggle --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="font-bold text-gray-800 text-sm">Split Payment (Milestones)</h3>
            <p class="text-[11px] text-gray-400 mt-0.5">Divide total into multiple payment phases</p>
        </div>
        <button type="button"
                id="milestone-toggle-btn"
                onclick="toggleMilestoneSection()"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none bg-gray-200"
                role="switch" aria-checked="false">
            <span id="milestone-toggle-knob"
                  class="inline-block h-4 w-4 transform rounded-full bg-white shadow-md transition-transform translate-x-1">
            </span>
        </button>
    </div>

    {{-- Milestone Builder (hidden by default) --}}
    <div id="milestone-section" class="hidden space-y-4">

        {{-- Info Banner --}}
        <div class="bg-amber-50 border border-amber-100 rounded-lg p-3 flex gap-2">
            <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <p class="text-[11px] text-amber-700 leading-relaxed">
                Milestones split the total amount into scheduled payment phases.
                The sum of all milestones must equal <strong>100%</strong> of the total.
            </p>
        </div>

        {{-- Amount Type Toggle --}}
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2">Amount Type</label>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="setMilestoneAmountType('percentage')"
                        id="ms-type-percentage"
                        class="ms-type-btn active-ms-type py-2 text-xs font-bold border-2 rounded-lg transition">
                    % Percentage
                </button>
                <button type="button" onclick="setMilestoneAmountType('fixed')"
                        id="ms-type-fixed"
                        class="ms-type-btn py-2 text-xs font-bold border-2 rounded-lg transition">
                    ₹ Fixed Amount
                </button>
            </div>
        </div>

        {{-- Milestones List --}}
        <div id="milestone-list" class="space-y-3">
            {{-- Milestone rows injected by JS --}}
        </div>

        {{-- Validation Bar --}}
        <div id="milestone-validation-bar" class="rounded-lg overflow-hidden border border-gray-200">
            <div class="flex items-center justify-between px-3 py-2 bg-gray-50 text-xs">
                <span class="font-semibold text-gray-600">Total Allocated</span>
                <span id="ms-allocated-display" class="font-black text-gray-800">0%</span>
            </div>
            <div class="h-2 bg-gray-100">
                <div id="ms-progress-bar"
                     class="h-full transition-all duration-300 bg-green-500 rounded-r"
                     style="width: 0%"></div>
            </div>
            <div id="ms-validation-msg" class="hidden px-3 py-1.5 text-[10px] font-bold"></div>
        </div>

        {{-- Add Milestone Button --}}
        <button type="button" onclick="addMilestoneRow()"
                class="w-full py-2 border-2 border-dashed border-gray-300 text-gray-500 rounded-lg text-xs font-bold hover:border-green-400 hover:text-green-600 transition flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 4v16m8-8H4"/>
            </svg>
            Add Milestone
        </button>

        {{-- Quick Presets --}}
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2">Quick Presets</label>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" onclick="applyMilestonePreset('50-50')"
                        class="py-1.5 text-[10px] font-bold border border-gray-200 rounded-lg hover:border-green-400 hover:text-green-700 hover:bg-green-50 transition">
                    50 / 50
                </button>
                <button type="button" onclick="applyMilestonePreset('30-70')"
                        class="py-1.5 text-[10px] font-bold border border-gray-200 rounded-lg hover:border-green-400 hover:text-green-700 hover:bg-green-50 transition">
                    30 / 70
                </button>
                <button type="button" onclick="applyMilestonePreset('25-25-50')"
                        class="py-1.5 text-[10px] font-bold border border-gray-200 rounded-lg hover:border-green-400 hover:text-green-700 hover:bg-green-50 transition">
                    25/25/50
                </button>
            </div>
        </div>

    </div>
</div>

{{-- ── MILESTONE ROW TEMPLATE (hidden, cloned by JS) ──────────── --}}
<template id="milestone-row-template">
    <div class="milestone-row bg-gray-50 border border-gray-200 rounded-xl p-3 space-y-2 relative"
         data-milestone-index="">
        {{-- Remove Button --}}
        <button type="button" onclick="removeMilestoneRow(this)"
                class="absolute top-2 right-2 w-5 h-5 flex items-center justify-center text-gray-400 hover:text-red-500 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Row Header --}}
        <div class="flex items-center gap-2">
            <div class="ms-sequence-badge w-5 h-5 rounded-full bg-[#2D5A43] text-white text-[9px] font-black flex items-center justify-center flex-shrink-0">
                1
            </div>
            <input type="text"
                   class="ms-title-input flex-1 text-xs font-semibold bg-transparent border-0 border-b border-dashed border-gray-300 focus:border-green-400 focus:outline-none pb-0.5 pr-6"
                   placeholder="e.g. Advance Payment"
                   oninput="recalculateMilestones()">
        </div>

        <div class="grid grid-cols-2 gap-2">
            {{-- Amount --}}
            <div>
                <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">
                    <span class="ms-amount-label">Amount (%)</span>
                </label>
                <div class="relative">
                    <input type="number"
                           class="ms-amount-input w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs font-bold focus:ring-1 focus:ring-green-400 focus:border-green-400 outline-none pr-8"
                           placeholder="0"
                           min="0" step="0.01"
                           oninput="recalculateMilestones()">
                    <span class="ms-amount-suffix absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 font-bold pointer-events-none">%</span>
                </div>
                {{-- Computed ₹ amount --}}
                <p class="ms-computed-amount text-[10px] text-green-700 font-bold mt-1 hidden">= ₹0.00</p>
            </div>

            {{-- Due Date --}}
            <div>
                <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Due Date</label>
                <input type="date"
                       class="ms-due-date-input w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:ring-1 focus:ring-green-400 focus:border-green-400 outline-none"
                       min="{{ now()->format('Y-m-d') }}">
            </div>
        </div>

        {{-- Notes (optional) --}}
        <input type="text"
               class="ms-notes-input w-full border border-gray-200 rounded-lg px-2 py-1.5 text-[11px] text-gray-600 focus:ring-1 focus:ring-green-400 focus:border-green-400 outline-none"
               placeholder="Notes (optional)">
    </div>
</template>

<style>
/* ── Milestone Toggle Button ── */
#milestone-toggle-btn.ms-active {
    background-color: #2D5A43;
}
#milestone-toggle-btn.ms-active #milestone-toggle-knob {
    transform: translateX(20px);
}

/* ── Amount Type Buttons ── */
.ms-type-btn {
    border-color: #e5e7eb;
    color: #6b7280;
    background: #fff;
}
.ms-type-btn:hover {
    border-color: #2D5A43;
    color: #2D5A43;
    background: #f0fdf4;
}
.ms-type-btn.active-ms-type {
    border-color: #2D5A43;
    color: #2D5A43;
    background: #f0fdf4;
    box-shadow: 0 0 0 1px #2D5A43;
}

/* ── Progress Bar Colors ── */
#ms-progress-bar.ms-bar-ok       { background-color: #16a34a; }
#ms-progress-bar.ms-bar-over     { background-color: #dc2626; }
#ms-progress-bar.ms-bar-partial  { background-color: #f59e0b; }

/* ── Drag handle (future) ── */
.milestone-row { animation: msSlideIn 0.2s ease-out; }
@keyframes msSlideIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<script>
/* ═══════════════════════════════════════════════════════════════
   MILESTONE MODULE — Fully isolated, zero side effects on
   the existing booking / payment / timer logic.
   ═══════════════════════════════════════════════════════════════ */

// ── State ──────────────────────────────────────────────────────
window.milestoneEnabled     = false;
window.milestoneAmountType  = 'percentage';   // 'percentage' | 'fixed'
let   milestoneRowCounter   = 0;

// ── Toggle milestone section ───────────────────────────────────
function toggleMilestoneSection() {
    window.milestoneEnabled = !window.milestoneEnabled;
    const btn      = document.getElementById('milestone-toggle-btn');
    const section  = document.getElementById('milestone-section');

    btn.classList.toggle('ms-active', window.milestoneEnabled);
    btn.setAttribute('aria-checked', window.milestoneEnabled ? 'true' : 'false');
    section.classList.toggle('hidden', !window.milestoneEnabled);

    // Auto-seed two rows on first enable
    const list = document.getElementById('milestone-list');
    if (window.milestoneEnabled && list.children.length === 0) {
        applyMilestonePreset('50-50');
    }
}

// ── Amount type (percentage / fixed) ──────────────────────────
function setMilestoneAmountType(type) {
    window.milestoneAmountType = type;

    document.getElementById('ms-type-percentage').classList.toggle('active-ms-type', type === 'percentage');
    document.getElementById('ms-type-fixed').classList.toggle('active-ms-type', type === 'fixed');

    // Update suffix and label on all rows
    document.querySelectorAll('.milestone-row').forEach(row => {
        row.querySelector('.ms-amount-label').textContent  = type === 'percentage' ? 'Amount (%)' : 'Amount (₹)';
        row.querySelector('.ms-amount-suffix').textContent = type === 'percentage' ? '%' : '₹';
    });

    recalculateMilestones();
}

// ── Add a milestone row ────────────────────────────────────────
function addMilestoneRow(title = '', amount = '', dueDate = '', notes = '') {
    const template = document.getElementById('milestone-row-template');
    const clone    = template.content.cloneNode(true);
    const row      = clone.querySelector('.milestone-row');

    milestoneRowCounter++;
    row.dataset.milestoneIndex = milestoneRowCounter;

    // Pre-fill if provided
    if (title)   row.querySelector('.ms-title-input').value    = title;
    if (amount)  row.querySelector('.ms-amount-input').value   = amount;
    if (dueDate) row.querySelector('.ms-due-date-input').value = dueDate;
    if (notes)   row.querySelector('.ms-notes-input').value    = notes;

    // Apply current type labels
    row.querySelector('.ms-amount-label').textContent  = window.milestoneAmountType === 'percentage' ? 'Amount (%)' : 'Amount (₹)';
    row.querySelector('.ms-amount-suffix').textContent = window.milestoneAmountType === 'percentage' ? '%' : '₹';

    document.getElementById('milestone-list').appendChild(row);
    reindexMilestoneRows();
    recalculateMilestones();
}

// ── Remove a milestone row ─────────────────────────────────────
function removeMilestoneRow(btn) {
    const row = btn.closest('.milestone-row');
    row.style.opacity    = '0';
    row.style.transform  = 'translateY(-6px)';
    row.style.transition = 'all 0.15s ease-out';
    setTimeout(() => { row.remove(); reindexMilestoneRows(); recalculateMilestones(); }, 150);
}

// ── Reindex badge numbers ──────────────────────────────────────
function reindexMilestoneRows() {
    document.querySelectorAll('.milestone-row').forEach((row, idx) => {
        const badge = row.querySelector('.ms-sequence-badge');
        if (badge) badge.textContent = idx + 1;
    });
}

// ── Recalculate totals & update progress bar ───────────────────
function recalculateMilestones() {
    // Get current grand total from existing POS pricing logic
    const totalAmount = (typeof getPosPricingBreakdown === 'function')
        ? getPosPricingBreakdown().grandTotal
        : (typeof globalBaseAmount !== 'undefined' ? globalBaseAmount : 0);

    const rows  = document.querySelectorAll('.milestone-row');
    let sumPct  = 0;
    let sumFixed = 0;

    rows.forEach(row => {
        const val = parseFloat(row.querySelector('.ms-amount-input').value) || 0;

        if (window.milestoneAmountType === 'percentage') {
            sumPct += val;
            const computed = totalAmount > 0 ? (totalAmount * val / 100) : 0;
            const compEl   = row.querySelector('.ms-computed-amount');
            compEl.textContent = `= ₹${computed.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            compEl.classList.toggle('hidden', val <= 0);
        } else {
            sumFixed += val;
            row.querySelector('.ms-computed-amount').classList.add('hidden');
        }
    });

    // ── Update progress bar ──
    let pct, barClass, msgText, msgClass;

    if (window.milestoneAmountType === 'percentage') {
        pct      = Math.min(sumPct, 100);
        barClass = sumPct > 100 ? 'ms-bar-over' : sumPct === 100 ? 'ms-bar-ok' : 'ms-bar-partial';
        const display = document.getElementById('ms-allocated-display');
        display.textContent = `${sumPct.toFixed(1)}%`;

        if (sumPct > 100) {
            msgText  = `⚠ Over-allocated by ${(sumPct - 100).toFixed(1)}%`;
            msgClass = 'bg-red-50 text-red-700';
        } else if (sumPct === 100) {
            msgText  = '✓ Total is exactly 100%';
            msgClass = 'bg-green-50 text-green-700';
        } else {
            msgText  = `${(100 - sumPct).toFixed(1)}% remaining to allocate`;
            msgClass = 'bg-amber-50 text-amber-700';
        }
    } else {
        pct      = totalAmount > 0 ? Math.min((sumFixed / totalAmount) * 100, 100) : 0;
        barClass = sumFixed > totalAmount ? 'ms-bar-over' : Math.abs(sumFixed - totalAmount) < 0.01 ? 'ms-bar-ok' : 'ms-bar-partial';
        const display = document.getElementById('ms-allocated-display');
        display.textContent = `₹${sumFixed.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;

        const remaining = totalAmount - sumFixed;
        if (sumFixed > totalAmount) {
            msgText  = `⚠ Over-allocated by ₹${Math.abs(remaining).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
            msgClass = 'bg-red-50 text-red-700';
        } else if (Math.abs(remaining) < 0.01) {
            msgText  = '✓ Total matches exactly';
            msgClass = 'bg-green-50 text-green-700';
        } else {
            msgText  = `₹${remaining.toLocaleString('en-IN', { minimumFractionDigits: 2 })} remaining`;
            msgClass = 'bg-amber-50 text-amber-700';
        }
    }

    const bar    = document.getElementById('ms-progress-bar');
    const msgEl  = document.getElementById('ms-validation-msg');
    bar.style.width = `${pct}%`;
    bar.className   = `h-full transition-all duration-300 rounded-r ${barClass}`;
    msgEl.textContent = msgText;
    msgEl.className   = `px-3 py-1.5 text-[10px] font-bold ${msgClass}`;
    msgEl.classList.remove('hidden');
}

// ── Quick Presets ──────────────────────────────────────────────
function applyMilestonePreset(preset) {
    document.getElementById('milestone-list').innerHTML = '';
    milestoneRowCounter = 0;

    const presets = {
        '50-50': [
            { title: 'Advance Payment', amount: 50 },
            { title: 'Final Payment',   amount: 50 },
        ],
        '30-70': [
            { title: 'Advance Payment', amount: 30 },
            { title: 'Final Payment',   amount: 70 },
        ],
        '25-25-50': [
            { title: 'Advance Payment',    amount: 25 },
            { title: 'Mid-term Payment',   amount: 25 },
            { title: 'Final Payment',      amount: 50 },
        ],
    };

    // Make sure we're in percentage mode for presets
    setMilestoneAmountType('percentage');

    const rows = presets[preset] || [];
    rows.forEach(r => addMilestoneRow(r.title, r.amount));
    recalculateMilestones();
}

// ── Validate milestones before booking submission ──────────────
// Called by the existing "Finalize Booking" click handler via hook below.
// Returns { valid: bool, milestones: [] } or { valid: false, error: '' }
window.getMilestonePayload = function () {
    if (!window.milestoneEnabled) {
        return { enabled: false, milestones: [] };
    }

    const totalAmount = (typeof getPosPricingBreakdown === 'function')
        ? getPosPricingBreakdown().grandTotal
        : (typeof globalBaseAmount !== 'undefined' ? globalBaseAmount : 0);

    const rows      = document.querySelectorAll('.milestone-row');
    const milestones = [];
    let sumPct  = 0;
    let sumFixed = 0;

    for (let i = 0; i < rows.length; i++) {
        const row    = rows[i];
        const title  = row.querySelector('.ms-title-input').value.trim();
        const amount = parseFloat(row.querySelector('.ms-amount-input').value);
        const dueDate = row.querySelector('.ms-due-date-input').value;
        const notes  = row.querySelector('.ms-notes-input').value.trim();

        if (!title) {
            return { enabled: true, valid: false, error: `Milestone ${i + 1}: Title is required.` };
        }
        if (!amount || amount <= 0) {
            return { enabled: true, valid: false, error: `Milestone ${i + 1}: Amount must be greater than 0.` };
        }

        if (window.milestoneAmountType === 'percentage') {
            sumPct += amount;
        } else {
            sumFixed += amount;
        }

        milestones.push({
            title,
            description: notes || null,
            amount_type: window.milestoneAmountType,
            amount,
            due_date:    dueDate || null,
            vendor_notes: notes || null,
        });
    }

    if (milestones.length === 0) {
        return { enabled: true, valid: false, error: 'Please add at least one milestone.' };
    }

    // Validate totals
    if (window.milestoneAmountType === 'percentage') {
        if (Math.abs(sumPct - 100) > 0.01) {
            return { enabled: true, valid: false, error: `Milestone percentages must total 100%. Current total: ${sumPct.toFixed(1)}%.` };
        }
    } else {
        if (Math.abs(sumFixed - totalAmount) > 0.01) {
            return {
                enabled: true, valid: false,
                error: `Milestone amounts must total ₹${totalAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 })}. Current total: ₹${sumFixed.toLocaleString('en-IN', { minimumFractionDigits: 2 })}.`
            };
        }
    }

    return { enabled: true, valid: true, milestones };
};

// ── Hook into the existing "Finalize Booking" button ──────────
// We wrap the existing click listener after DOM ready.
// We do NOT overwrite it — we intercept BEFORE the fetch.
document.addEventListener('DOMContentLoaded', () => {
    const createBtn = document.getElementById('create-booking-btn');
    if (!createBtn) return;

    // Intercept by capturing the event in the capture phase (fires before bubbled listeners)
    createBtn.addEventListener('click', function milestoneInterceptor(e) {
        if (!window.milestoneEnabled) return; // Let existing logic run unaffected

        const result = window.getMilestonePayload();

        if (!result.valid) {
            e.stopImmediatePropagation(); // Block existing listener from firing
            if (typeof showToast === 'function') {
                showToast(result.error, 'error');
            } else {
                alert(result.error);
            }
            return;
        }

        // Attach milestone data to window so the existing payload builder can pick it up
        window._pendingMilestoneData = result.milestones;

    }, true); // ← capture: true ensures this fires BEFORE the existing listener
});

// ── Patch the existing payload builder to include milestones ──
// The existing code builds `payload` and sends it.
// We monkey-patch the fetch to inject milestones into the body.
(function patchMilestoneIntoFetch() {
    const origFetch = window.fetch;
    window.fetch = function (url, options = {}) {
        // Only intercept the bookings creation endpoint
        if (
            window._pendingMilestoneData &&
            typeof url === 'string' &&
            url.includes('/api/bookings') &&
            options.method === 'POST'
        ) {
            try {
                const body = JSON.parse(options.body || '{}');
                body.payment_mode       = 'milestone';
                body.milestone_data     = window._pendingMilestoneData;
                options.body            = JSON.stringify(body);
                window._pendingMilestoneData = null; // Clear after injection
            } catch (e) {
                console.error('[MilestoneModule] Failed to inject milestone payload', e);
            }
        }
        return origFetch.call(this, url, options);
    };
})();

// ── Recalculate when grand total changes (discount input fires) ──
// Safe: only calls recalculate if milestones are enabled
const _origCalculateFinalTotals = window.calculateFinalTotals;
window.calculateFinalTotals = function () {
    if (typeof _origCalculateFinalTotals === 'function') {
        _origCalculateFinalTotals();
    }
    if (window.milestoneEnabled) {
        recalculateMilestones();
    }
};
</script>
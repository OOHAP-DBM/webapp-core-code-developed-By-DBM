{{--
╔══════════════════════════════════════════════════════════════════════════╗
║  POS MILESTONE COMPONENT  v4                                             ║
║                                                                          ║
║  @include('vendor.pos.components.milestone-payment')                     ║
║                                                                          ║
║  Changes in v4:                                                          ║
║  • % symbol renders AFTER the value (right-side), ₹ stays left          ║
║  • Due dates cascade: milestone N min = milestone N-1's selected date    ║
║  • validate() enforces sequential due dates with clear error messages    ║
╚══════════════════════════════════════════════════════════════════════════╝
--}}

{{-- ══════════════════════════════════════════════
     MILESTONE TOGGLE CARD
══════════════════════════════════════════════ --}}
<div id="ms-card" class="rounded-xl border border-gray-200 bg-white overflow-hidden">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-1 bg-gray-50 border-b border-gray-200">
        <div>
            <p class="text-sm font-bold">Milestone Creation</p>
            <p class="text-[11px] mt-0.5">Define work stages with due dates, deliverables, and payment amounts</p>
        </div>
        <button type="button"
                id="ms-toggle-btn"
                onclick="MilestoneModule.toggle()"
                role="switch"
                aria-checked="false"
                class="relative inline-flex h-5 w-10 flex-shrink-0 items-center rounded-full bg-gray-300 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#2D5A43] focus:ring-offset-1">
            <span id="ms-toggle-knob"
                  class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform duration-200 translate-x-0.5">
            </span>
        </button>
    </div>

    {{-- Builder panel — hidden until toggle ON --}}
    <div id="ms-builder" class="hidden">

        {{-- Empty state --}}
        <div id="ms-empty-state" class="flex flex-col items-center justify-center py-6 text-center">
            <p class="text-sm mb-3">No Milestone Created</p>
            <button type="button" onclick="MilestoneModule.addRow()"
                    class="px-4 py-2 bg-[#2D5A43] text-white text-sm font-bold rounded-lg hover:bg-opacity-90 transition-colors">
                + Create Milestone
            </button>
        </div>

        {{-- Table header (shown once rows exist) --}}
        <div id="ms-table-header" class="hidden px-4 pt-3">
            <div class="flex items-center gap-2 mb-1">
                <div class="flex-1"></div>
                {{-- Amount type toggle: Flat / % --}}
                <div class="flex rounded-lg border border-gray-200 overflow-hidden text-[11px] font-bold ml-auto">
                    <button type="button" id="ms-btn-flat"
                            onclick="MilestoneModule.setAmountType('fixed')"
                            class="px-3 py-1.5 bg-[#2D5A43] text-white transition-colors text-[11px] font-bold">
                        Flat
                    </button>
                    <button type="button" id="ms-btn-pct"
                            onclick="MilestoneModule.setAmountType('percentage')"
                            class="px-3 py-1.5 bg-white hover:bg-gray-50 transition-colors text-[11px] font-bold">
                        %
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-2 px-1 mb-1">
                <div class="col-span-1 text-sm font-semibold">#</div>
                <div class="col-span-3 text-sm font-semibold">Due Date</div>
                <div class="col-span-5 text-sm font-semibold">Work Done</div>
                <div class="col-span-3 text-sm font-semibold text-right">Amount</div>
            </div>
        </div>

        {{-- Milestone rows --}}
        <div id="ms-rows-container" class="px-4 space-y-2"></div>

        {{-- Allocation bar --}}
        <div id="ms-alloc-wrap" class="mx-4 mt-2 mb-2 hidden">
            <div class="rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between px-3 py-1.5 text-[10px]">
                    <span class="font-semibold text-gray-500">Allocated</span>
                    <span id="ms-alloc-display" class="font-black text-gray-800">0</span>
                </div>
                <div class="h-1.5 bg-gray-200">
                    <div id="ms-alloc-bar" class="h-full rounded-r transition-all duration-300" style="width:0%"></div>
                </div>
                <p id="ms-alloc-msg" class="px-3 py-1 text-[10px] font-semibold hidden"></p>
            </div>
        </div>

        {{-- Add Milestone button (shown after first row) --}}
        <div id="ms-add-btn-wrap" class="px-4 pb-4 hidden">
            <button type="button" onclick="MilestoneModule.addRow()"
                    class="w-full flex items-center justify-center gap-1.5 py-2 rounded-lg border-2 border-dashed border-gray-300 text-[11px] font-bold hover:border-[#2D5A43] hover:text-[#2D5A43] transition-colors">
                + Add Milestone
            </button>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════
     MILESTONE ROW TEMPLATE
══════════════════════════════════════════════ --}}
<template id="ms-row-tpl">
    <div class="ms-row grid grid-cols-12 gap-2 items-start bg-white border border-gray-100 rounded-lg p-2" data-idx="">

        {{-- Col 1: Sequence badge --}}
        <div class="col-span-1 flex items-center justify-center pt-1.5">
            <div class="ms-seq w-5 h-5 rounded-full bg-[#2D5A43] text-white text-[9px] font-black flex items-center justify-center flex-shrink-0">#</div>
        </div>

        {{-- Col 2-4: Due Date --}}
        <div class="col-span-3">
            <input type="date"
                   class="ms-due-date w-full border border-gray-200 rounded text-sm px-1.5 py-1 focus:outline-none focus:border-[#2D5A43]"
                   min="{{ now()->format('Y-m-d') }}"
                   onchange="MilestoneModule.onDateChange(this)">
        </div>

        {{-- Col 5-9: Work Done description --}}
        <div class="col-span-5">
            <textarea class="ms-desc w-full border border-gray-200 rounded text-sm placeholder-gray-400 px-1.5 py-0.5 resize-none focus:outline-none focus:border-[#2D5A43]"
                      rows="1"
                      maxlength="64"
                      placeholder="Write here..."
                      style="min-height:2.6em;max-height:3.5em;"
                      oninput="MilestoneModule.onDescInput(this)"></textarea>
            <div class="text-right mt-0.5">
                <span class="ms-char-count text-[9px] text-gray-600">0/64</span>
            </div>
        </div>

        {{-- Col 10-12: Amount + delete --}}
        <div class="col-span-3">
            <div class="flex items-center gap-1">
                {{--
                    ₹ symbol: left side, shown in fixed mode
                    % symbol: right side, shown in percentage mode
                    Padding on the input swaps accordingly via JS
                --}}
                <div class="relative flex-1">
                    <span class="ms-sym-left absolute left-1.5 top-1/2 -translate-y-1/2 text-sm text-gray-500 font-bold pointer-events-none">₹</span>
                    <input type="number"
                           class="ms-amount w-full border border-gray-200 rounded py-1 text-sm font-bold text-gray-800 focus:outline-none focus:ring-1 focus:ring-[#2D5A43] focus:border-[#2D5A43]"
                           placeholder="0" min="0" step="0.01"
                           style="min-height:2.6em;max-height:3.5em;padding-left:1.25rem;padding-right:0.4rem;"
                           oninput="MilestoneModule.recalculate()">
                    <span class="ms-sym-right absolute right-1.5 top-1/2 -translate-y-1/2 text-sm text-gray-500 font-bold pointer-events-none hidden">%</span>
                </div>
                <button type="button" onclick="MilestoneModule.removeRow(this)"
                        class="flex-shrink-0 text-red-300 hover:text-red-500 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            {{-- Computed ₹ equivalent shown only in % mode --}}
            <p class="ms-computed text-[9px] text-[#2D5A43] font-semibold mt-0.5 text-right hidden"></p>
        </div>

    </div>
</template>

{{-- ══════════════════════════════════════════════
     STYLES
══════════════════════════════════════════════ --}}
<style>
#ms-toggle-btn.ms-on                  { background-color: #2D5A43; }
#ms-toggle-btn.ms-on #ms-toggle-knob  { transform: translateX(20px); }
#ms-alloc-bar.ms-ok                   { background-color: #16a34a; }
#ms-alloc-bar.ms-over                 { background-color: #dc2626; }
#ms-alloc-bar.ms-part                 { background-color: #f59e0b; }
.ms-row { animation: msRowIn .15s ease-out both; }
@keyframes msRowIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:none; } }
</style>

{{-- ══════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════ --}}
<script>
window.MilestoneModule = (function () {
    'use strict';

    /* ── State ───────────────────────────────────────────────────────── */
    var _enabled = false;
    var _type    = 'fixed';   // 'fixed' | 'percentage'
    var _seq     = 0;

    /* ── DOM helper ──────────────────────────────────────────────────── */
    var $ = function (id) { return document.getElementById(id); };

    /* ── Get booking grand total ─────────────────────────────────────── */
    function getTotal() {
        if (typeof window.getPosPricingBreakdown === 'function') {
            return window.getPosPricingBreakdown().grandTotal || 0;
        }
        var el = $('side-grand-total');
        if (el) return parseFloat(el.innerText.replace(/[^\d.]/g, '')) || 0;
        return (typeof globalBaseAmount !== 'undefined') ? (globalBaseAmount || 0) : 0;
    }

    /* ── All rows ────────────────────────────────────────────────────── */
    function rows() {
        return Array.from(document.querySelectorAll('#ms-rows-container .ms-row'));
    }

    /* ── Show / hide sections based on row count ─────────────────────── */
    function updateVisibility() {
        var count = rows().length;
        $('ms-empty-state').classList.toggle('hidden', count > 0);
        $('ms-table-header').classList.toggle('hidden', count === 0);
        $('ms-alloc-wrap').classList.toggle('hidden', count === 0);
        $('ms-add-btn-wrap').classList.toggle('hidden', count === 0);
    }

    /* ── Apply symbol layout to a single row ────────────────────────── */
    function applySymbolLayout(row, type) {
        var symLeft  = row.querySelector('.ms-sym-left');
        var symRight = row.querySelector('.ms-sym-right');
        var input    = row.querySelector('.ms-amount');

        if (type === 'percentage') {
            if (symLeft)  symLeft.classList.add('hidden');
            if (symRight) symRight.classList.remove('hidden');
            if (input) {
                input.style.paddingLeft  = '0.4rem';
                input.style.paddingRight = '1.25rem';
            }
        } else {
            if (symLeft)  symLeft.classList.remove('hidden');
            if (symRight) symRight.classList.add('hidden');
            if (input) {
                input.style.paddingLeft  = '1.25rem';
                input.style.paddingRight = '0.4rem';
            }
        }
    }

    /* ── Cascade due-date min values down all rows ───────────────────── */
    function updateDueDateMins() {
        var today = new Date().toISOString().split('T')[0];
        var list  = rows();

        list.forEach(function (row, i) {
            var dateInput = row.querySelector('.ms-due-date');
            if (!dateInput) return;

            var minVal;
            if (i === 0) {
                // First milestone: minimum is today
                minVal = today;
            } else {
                // Each subsequent milestone: minimum is the previous row's chosen date
                var prevVal = list[i - 1].querySelector('.ms-due-date').value;
                minVal = prevVal || today;
            }

            dateInput.min = minVal;

            // If the currently selected value is now earlier than the new min, clear it
            if (dateInput.value && dateInput.value < minVal) {
                dateInput.value = '';
            }
        });
    }

    /* ── Toggle milestone feature on/off ─────────────────────────────── */
    function toggle() {
        _enabled = !_enabled;
        $('ms-toggle-btn').classList.toggle('ms-on', _enabled);
        $('ms-toggle-btn').setAttribute('aria-checked', String(_enabled));
        $('ms-builder').classList.toggle('hidden', !_enabled);
    }

    /* ── Switch between Flat / % mode ───────────────────────────────── */
    function setAmountType(type) {
        _type = type;

        // Update toggle button styles
        $('ms-btn-flat').className = (type === 'fixed')
            ? 'px-3 py-1.5 bg-[#2D5A43] text-white transition-colors text-[11px] font-bold'
            : 'px-3 py-1.5 bg-white hover:bg-gray-50 transition-colors text-[11px] font-bold';

        $('ms-btn-pct').className = (type === 'percentage')
            ? 'px-3 py-1.5 bg-[#2D5A43] text-white transition-colors text-[11px] font-bold'
            : 'px-3 py-1.5 bg-white hover:bg-gray-50 transition-colors text-[11px] font-bold';

        // Update symbol position on every existing row
        rows().forEach(function (row) {
            applySymbolLayout(row, type);
        });

        recalculate();
    }

    /* ── Add a new milestone row ─────────────────────────────────────── */
    function addRow(defaultDesc) {
        var tpl   = document.getElementById('ms-row-tpl');
        var clone = tpl.content.cloneNode(true);
        var row   = clone.querySelector('.ms-row');

        _seq++;
        row.dataset.idx = _seq;

        // Pre-fill description if provided
        if (defaultDesc) {
            var desc = row.querySelector('.ms-desc');
            if (desc) {
                desc.value = defaultDesc;
                var ctr = row.querySelector('.ms-char-count');
                if (ctr) ctr.textContent = defaultDesc.length + '/64';
            }
        }

        // Apply correct symbol layout for current mode
        applySymbolLayout(row, _type);

        document.getElementById('ms-rows-container').appendChild(row);
        reindex();
        updateDueDateMins();
        updateVisibility();
        recalculate();
    }

    /* ── Remove a milestone row ──────────────────────────────────────── */
    function removeRow(btn) {
        var row = btn.closest('.ms-row');
        row.style.cssText += 'opacity:0;transform:translateY(-4px);transition:all .12s ease;';
        setTimeout(function () {
            row.remove();
            reindex();
            updateDueDateMins();
            updateVisibility();
            recalculate();
        }, 120);
    }

    /* ── Renumber badge labels 1, 2, 3 … ────────────────────────────── */
    function reindex() {
        rows().forEach(function (row, i) {
            var badge = row.querySelector('.ms-seq');
            var n = i + 1;
            row.dataset.idx = n;
            if (badge) badge.textContent = String(n);
        });
    }

    /* ── Live character counter for description textarea ────────────── */
    function onDescInput(el) {
        var ctr = el.closest('.ms-row').querySelector('.ms-char-count');
        if (ctr) ctr.textContent = el.value.length + '/64';
    }

    /* ── Date change: cascade mins and recalculate ───────────────────── */
    function onDateChange(el) {
        updateDueDateMins();
        recalculate();
    }

    /* ── Recalculate allocation progress bar ─────────────────────────── */
    function recalculate() {
        var total = getTotal();
        var sum   = 0;

        // Update computed ₹ labels in % mode and sum amounts
        rows().forEach(function (row) {
            var val  = parseFloat(row.querySelector('.ms-amount').value) || 0;
            sum += val;

            var comp = row.querySelector('.ms-computed');
            if (comp) {
                if (_type === 'percentage' && val > 0 && total > 0) {
                    comp.textContent = '= ₹' + Math.round(total * val / 100).toLocaleString('en-IN');
                    comp.classList.remove('hidden');
                } else {
                    comp.classList.add('hidden');
                }
            }
        });

        // Update progress bar
        var bar  = $('ms-alloc-bar');
        var disp = $('ms-alloc-display');
        var msg  = $('ms-alloc-msg');
        if (!bar) return;

        var pct, barCls, msgTxt, msgCls;

        if (_type === 'percentage') {
            pct  = Math.min(sum, 100);
            disp.textContent = sum.toFixed(1) + '%';

            if (sum > 100) {
                barCls = 'ms-over'; msgCls = 'text-red-600';
                msgTxt = '⚠ Over by ' + (sum - 100).toFixed(1) + '%';
            } else if (Math.abs(sum - 100) < 0.01) {
                barCls = 'ms-ok'; msgCls = 'text-green-700';
                msgTxt = '✓ Exactly 100% — ready';
            } else {
                barCls = 'ms-part'; msgCls = 'text-amber-700';
                msgTxt = (100 - sum).toFixed(1) + '% remaining';
            }
        } else {
            pct  = total > 0 ? Math.min((sum / total) * 100, 100) : 0;
            disp.textContent = '₹' + sum.toLocaleString('en-IN');

            var diff = total - sum;
            if (sum > total + 0.01) {
                barCls = 'ms-over'; msgCls = 'text-red-600';
                msgTxt = '⚠ Over by ₹' + Math.abs(diff).toLocaleString('en-IN');
            } else if (Math.abs(diff) < 0.01) {
                barCls = 'ms-ok'; msgCls = 'text-green-700';
                msgTxt = '✓ Total matches — ready';
            } else {
                barCls = 'ms-part'; msgCls = 'text-amber-700';
                msgTxt = '₹' + diff.toLocaleString('en-IN') + ' remaining';
            }
        }

        bar.style.width = pct + '%';
        bar.className   = 'h-full rounded-r transition-all duration-300 ' + barCls;
        msg.textContent = msgTxt;
        msg.className   = 'px-3 py-1 text-[10px] font-semibold ' + msgCls;
        msg.classList.remove('hidden');

        // Always keep date mins in sync after any amount/total change
        updateDueDateMins();
    }

    /* ── Validate all milestone rows and build payload ───────────────── */
    function validate() {
        if (!_enabled) return { enabled: false, valid: true, milestones: [] };

        var total    = getTotal();
        var list     = rows();
        var result   = [];
        var sumPct   = 0;
        var sumFixed = 0;

        if (list.length === 0) {
            return { enabled: true, valid: false, error: 'Add at least one milestone.' };
        }

        for (var i = 0; i < list.length; i++) {
            var row    = list[i];
            var desc   = (row.querySelector('.ms-desc').value || '').trim();
            var amount = parseFloat(row.querySelector('.ms-amount').value);
            var due    = row.querySelector('.ms-due-date').value;
            var n      = i + 1;

            // Due date required
            if (!due) {
                return {
                    enabled: true,
                    valid:   false,
                    error:   'Milestone ' + n + ': Due date is required.',
                };
            }

            // Due date must be ≥ previous milestone's due date
            if (i > 0) {
                var prevDue = list[i - 1].querySelector('.ms-due-date').value;
                if (prevDue && due < prevDue) {
                    return {
                        enabled: true,
                        valid:   false,
                        error:   'Milestone ' + n + ' due date (' + due + ') must be on or after Milestone ' + i + '\'s due date (' + prevDue + ').',
                    };
                }
            }

            // Amount required and > 0
            if (!amount || amount <= 0) {
                return {
                    enabled: true,
                    valid:   false,
                    error:   'Milestone ' + n + ': Amount must be greater than 0.',
                };
            }

            if (_type === 'percentage') sumPct  += amount;
            else                         sumFixed += amount;

            result.push({
                title:        desc || ('Milestone ' + n),
                description:  desc || null,
                amount_type:  _type,
                amount:       amount,
                due_date:     due,
                vendor_notes: desc || null,
            });
        }

        // Percentage must total exactly 100
        if (_type === 'percentage' && Math.abs(sumPct - 100) > 0.01) {
            return {
                enabled: true,
                valid:   false,
                error:   'Percentages must total 100%. Current total: ' + sumPct.toFixed(1) + '%.',
            };
        }

        // Fixed amounts must equal grand total
        if (_type === 'fixed' && Math.abs(sumFixed - total) > 0.01) {
            return {
                enabled: true,
                valid:   false,
                error:   'Fixed amounts must total ₹' + total.toLocaleString('en-IN', { minimumFractionDigits: 2 })
                       + '. Current total: ₹' + sumFixed.toLocaleString('en-IN', { minimumFractionDigits: 2 }) + '.',
            };
        }

        return { enabled: true, valid: true, milestones: result };
    }

    /* ── Hook: intercept Finalize Booking button click ───────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('create-booking-btn');
        if (!btn) return;

        btn.addEventListener('click', function (e) {
            if (!_enabled) return;

            var res = validate();
            if (!res.valid) {
                e.stopImmediatePropagation();
                if (typeof showToast === 'function') showToast(res.error, 'error');
                else alert(res.error);
                return;
            }
            window._pendingMilestoneData = res.milestones;
        }, true);
    });

    /* ── Hook: patch fetch to inject milestone data into booking POST ── */
    (function () {
        var _orig = window.fetch;
        window.fetch = function (url, options) {
            options = options || {};
            if (
                window._pendingMilestoneData &&
                typeof url === 'string' &&
                /\/api\/bookings\b/.test(url) &&
                (options.method || '').toUpperCase() === 'POST'
            ) {
                try {
                    var body = JSON.parse(options.body || '{}');
                    body.is_milestone   = true;
                    body.milestone_data = window._pendingMilestoneData;
                    options.body = JSON.stringify(body);
                    window._pendingMilestoneData = null;
                } catch (e) {
                    console.error('[MilestoneModule] fetch patch failed:', e);
                }
            }
            return _orig.call(this, url, options);
        };
    }());

    /* ── Hook: recalculate when booking totals change ────────────────── */
    window.addEventListener('load', function () {
        var orig = window.calculateFinalTotals;
        if (typeof orig === 'function') {
            window.calculateFinalTotals = function () {
                orig.apply(this, arguments);
                if (_enabled) recalculate();
            };
        }
    });

    /* ── Public API ──────────────────────────────────────────────────── */
    return {
        toggle:        toggle,
        setAmountType: setAmountType,
        addRow:        addRow,
        removeRow:     removeRow,
        onDescInput:   onDescInput,
        onDateChange:  onDateChange,
        recalculate:   recalculate,
        validate:      validate,
        isEnabled:     function () { return _enabled; },
    };
}());
</script>
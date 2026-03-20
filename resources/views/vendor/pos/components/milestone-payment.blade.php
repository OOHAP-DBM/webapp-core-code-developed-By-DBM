{{--
╔══════════════════════════════════════════════════════════════════════════╗
║  POS MILESTONE COMPONENT  v5                                             ║
║                                                                          ║
║  @include('vendor.pos.components.milestone-payment')                     ║
║                                                                          ║
║  Changes in v5:                                                          ║
║  • Replaced alert() with beautiful animated toast + modal popup system  ║
║  • Toast: slides in from top-right, auto-dismisses, progress bar        ║
║  • Modal: backdrop blur, smooth scale-in, keyboard accessible           ║
║  • All errors route through MsAlert with human-friendly titles          ║
╚══════════════════════════════════════════════════════════════════════════╝
--}}

{{-- ══════════════════════════════════════════════
     TOAST CONTAINER
══════════════════════════════════════════════ --}}
<div id="ms-toast-root"
     aria-live="polite"
     aria-atomic="false"
     style="position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;pointer-events:none;max-width:360px;width:calc(100vw - 2rem);">
</div>

{{-- ══════════════════════════════════════════════
     MODAL OVERLAY
══════════════════════════════════════════════ --}}
<div id="ms-modal-overlay"
     role="dialog"
     aria-modal="true"
     aria-labelledby="ms-modal-title"
     tabindex="-1"
     style="position:fixed;inset:0;z-index:10000;display:none;align-items:center;justify-content:center;padding:1rem;background:rgba(0,0,0,0.45);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);">
    <div id="ms-modal-box"
         style="background:#fff;border-radius:16px;padding:1.5rem;width:100%;max-width:400px;box-shadow:0 24px 64px rgba(0,0,0,0.18),0 4px 16px rgba(0,0,0,0.08);transform:scale(0.9) translateY(12px);opacity:0;transition:transform .24s cubic-bezier(.34,1.38,.64,1),opacity .2s ease;pointer-events:auto;">
        <div style="display:flex;align-items:flex-start;gap:1rem;">
            <div id="ms-modal-icon"
                 style="flex-shrink:0;width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.125rem;font-weight:800;"></div>
            <div style="flex:1;min-width:0;padding-top:2px;">
                <p id="ms-modal-title" style="font-size:1rem;font-weight:700;color:#111827;margin:0 0 0.375rem;line-height:1.3;"></p>
                <p id="ms-modal-body"  style="font-size:0.8125rem;color:#4B5563;margin:0;line-height:1.6;"></p>
            </div>
        </div>
        <div id="ms-modal-actions" style="display:flex;gap:0.5rem;margin-top:1.375rem;justify-content:flex-end;flex-wrap:wrap;"></div>
    </div>
</div>

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

    {{-- Builder panel --}}
    <div id="ms-builder" class="hidden">

        {{-- Empty state --}}
        <div id="ms-empty-state" class="flex flex-col items-center justify-center py-6 text-center">
            <p class="text-sm mb-3">No Milestone Created</p>
            <button type="button" onclick="MilestoneModule.addRow()"
                    class="px-4 py-2 bg-[#2D5A43] text-white text-sm font-bold rounded-lg hover:bg-opacity-90 transition-colors">
                + Create Milestone
            </button>
        </div>

        {{-- Table header --}}
        <div id="ms-table-header" class="hidden px-4 pt-3">
            <div class="flex items-center gap-2 mb-1">
                <div class="flex-1"></div>
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

        {{-- Add milestone button --}}
        <div id="ms-add-btn-wrap" class="px-4 pb-4 hidden">
            <button type="button" onclick="MilestoneModule.addRow()"
                    class="w-full flex items-center justify-center gap-1.5 py-2 rounded-lg border-2 border-dashed border-gray-300 text-[11px] font-bold hover:border-[#2D5A43] hover:text-[#2D5A43] transition-colors">
                + Add Milestone
            </button>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════
     ROW TEMPLATE
══════════════════════════════════════════════ --}}
<template id="ms-row-tpl">
    <div class="ms-row grid grid-cols-12 gap-2 items-start bg-white border border-gray-100 rounded-lg p-2" data-idx="">

        <div class="col-span-1 flex items-center justify-center pt-1.5">
            <div class="ms-seq w-5 h-5 rounded-full bg-[#2D5A43] text-white text-[9px] font-black flex items-center justify-center flex-shrink-0">#</div>
        </div>

        <div class="col-span-3">
            <input type="date"
                   class="ms-due-date w-full border border-gray-200 rounded text-sm px-1.5 py-1 focus:outline-none focus:border-[#2D5A43]"
                   min="{{ now()->format('Y-m-d') }}"
                   onchange="MilestoneModule.onDateChange(this)">
        </div>

        <div class="col-span-5">
            <textarea class="ms-desc w-full border border-gray-200 rounded text-sm placeholder-gray-400 px-1.5 py-0.5 resize-none focus:outline-none focus:border-[#2D5A43]"
                      rows="1" maxlength="64" placeholder="Write here..."
                      style="min-height:2.6em;max-height:3.5em;"
                      oninput="MilestoneModule.onDescInput(this)"></textarea>
            <div class="text-right mt-0.5">
                <span class="ms-char-count text-[9px] text-gray-600">0/64</span>
            </div>
        </div>

        <div class="col-span-3">
            <div class="flex items-center gap-1">
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
            <p class="ms-computed text-[9px] text-[#2D5A43] font-semibold mt-0.5 text-right hidden"></p>
        </div>

    </div>
</template>

{{-- ══════════════════════════════════════════════
     STYLES
══════════════════════════════════════════════ --}}
<style>
/* ── Toggle ─────────────────────────────────── */
#ms-toggle-btn.ms-on                  { background-color:#2D5A43; }
#ms-toggle-btn.ms-on #ms-toggle-knob  { transform:translateX(20px); }

/* ── Alloc bar ──────────────────────────────── */
#ms-alloc-bar.ms-ok   { background-color:#16a34a; }
#ms-alloc-bar.ms-over { background-color:#dc2626; }
#ms-alloc-bar.ms-part { background-color:#f59e0b; }

/* ── Row animation ──────────────────────────── */
.ms-row { animation:msRowIn .15s ease-out both; }
@keyframes msRowIn { from{opacity:0;transform:translateY(-4px)} to{opacity:1;transform:none} }

/* ════════════════════════════════════════════
   TOAST
════════════════════════════════════════════ */
.ms-toast {
    display:flex; align-items:flex-start; gap:0.625rem;
    padding:0.75rem 0.875rem 0.9rem;
    border-radius:12px;
    box-shadow:0 8px 32px rgba(0,0,0,0.12),0 2px 8px rgba(0,0,0,0.07);
    pointer-events:auto;
    position:relative; overflow:hidden;
    width:100%; max-width:360px;
    transform:translateX(calc(100% + 1.5rem));
    opacity:0;
    transition:transform .32s cubic-bezier(.34,1.28,.64,1), opacity .25s ease;
    border:1px solid transparent;
    will-change:transform,opacity;
}
.ms-toast.ms-t-show { transform:translateX(0); opacity:1; }
.ms-toast.ms-t-hide {
    transform:translateX(calc(100% + 1.5rem)); opacity:0;
    transition:transform .22s ease-in, opacity .18s ease-in;
}

/* type colours */
.ms-toast-error   { background:#FEF2F2; border-color:#FECACA; }
.ms-toast-warning { background:#FFFBEB; border-color:#FDE68A; }
.ms-toast-success { background:#F0FDF4; border-color:#BBF7D0; }
.ms-toast-info    { background:#EFF6FF; border-color:#BFDBFE; }

/* icon bubble */
.ms-t-icon {
    flex-shrink:0; width:30px; height:30px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:0.75rem; font-weight:800; line-height:1;
}
.ms-toast-error   .ms-t-icon { background:#FEE2E2; color:#DC2626; }
.ms-toast-warning .ms-t-icon { background:#FEF3C7; color:#D97706; }
.ms-toast-success .ms-t-icon { background:#DCFCE7; color:#16A34A; }
.ms-toast-info    .ms-t-icon { background:#DBEAFE; color:#2563EB; }

/* text */
.ms-t-body  { flex:1; min-width:0; }
.ms-t-title { font-size:0.8rem; font-weight:700; margin:0 0 2px; line-height:1.3; }
.ms-t-msg   { font-size:0.74rem; line-height:1.45; margin:0; }
.ms-toast-error   .ms-t-title,.ms-toast-error   .ms-t-msg   { color:#991B1B; }
.ms-toast-warning .ms-t-title,.ms-toast-warning .ms-t-msg   { color:#92400E; }
.ms-toast-success .ms-t-title,.ms-toast-success .ms-t-msg   { color:#14532D; }
.ms-toast-info    .ms-t-title,.ms-toast-info    .ms-t-msg   { color:#1E3A8A; }

/* close btn */
.ms-t-close {
    flex-shrink:0; width:18px; height:18px; border:none; background:transparent;
    cursor:pointer; padding:0; display:flex; align-items:center; justify-content:center;
    border-radius:4px; opacity:0.45; transition:opacity .15s; font-size:0.9rem; color:inherit;
}
.ms-t-close:hover { opacity:1; }

/* progress bar */
.ms-t-prog {
    position:absolute; bottom:0; left:0; height:3px; border-radius:0 0 0 12px;
    transition:width linear;
}
.ms-toast-error   .ms-t-prog { background:#DC2626; }
.ms-toast-warning .ms-t-prog { background:#D97706; }
.ms-toast-success .ms-t-prog { background:#16A34A; }
.ms-toast-info    .ms-t-prog { background:#2563EB; }

/* ════════════════════════════════════════════
   MODAL
════════════════════════════════════════════ */
#ms-modal-overlay.ms-modal-open { display:flex !important; }
#ms-modal-overlay.ms-modal-open #ms-modal-box {
    transform:scale(1) translateY(0) !important;
    opacity:1 !important;
}
.ms-mbtn {
    padding:0.5rem 1.125rem; border-radius:8px;
    font-size:0.8125rem; font-weight:700;
    border:none; cursor:pointer;
    transition:opacity .15s, transform .1s;
    line-height:1.2; white-space:nowrap;
}
.ms-mbtn:hover  { opacity:.87; }
.ms-mbtn:active { transform:scale(.96); }
.ms-mbtn-cancel  { background:#F3F4F6; color:#374151; }
.ms-mbtn-error   { background:#DC2626; color:#fff; }
.ms-mbtn-warning { background:#D97706; color:#fff; }
.ms-mbtn-success { background:#16A34A; color:#fff; }
.ms-mbtn-info    { background:#2563EB; color:#fff; }

/* ── Responsive ─────────────────────────────── */
@media (max-width:480px) {
    #ms-toast-root {
        top:auto !important; bottom:.75rem;
        right:.75rem; left:.75rem;
        max-width:100%; width:auto;
    }
    .ms-toast { max-width:100%; min-width:0; }
    #ms-modal-box { padding:1.25rem !important; }
    #ms-modal-actions { justify-content:stretch !important; }
    .ms-mbtn { flex:1; text-align:center; }
}
</style>

{{-- ══════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════ --}}
<script>
/* ════════════════════════════════════════════════════════════════════════
   MsAlert  —  toast + confirm modal
   ════════════════════════════════════════════════════════════════════════
   MsAlert.error('message', 'optional title')
   MsAlert.warning('message')
   MsAlert.success('message')
   MsAlert.info('message')
   MsAlert.toast('error'|'warning'|'success'|'info', title, message, ms)
   MsAlert.confirm({ type, title, body, confirmText, cancelText })
          .then(ok => { if(ok) doSomething() })
   ════════════════════════════════════════════════════════════════════════ */
window.MsAlert = (function () {
    'use strict';

    var ICONS = { error:'✕', warning:'!', success:'✓', info:'i' };
    var DEFAULT_TITLES = { error:'Error', warning:'Warning', success:'Success', info:'Info' };
    var DEFAULT_DURATION = 5500;

    /* ── Internal: escape HTML ───────────────────────────────────────── */
    function esc(s) {
        return String(s||'')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── Internal: dismiss one toast ────────────────────────────────── */
    function dismiss(el) {
        if (!el || el._ms_dismissing) return;
        el._ms_dismissing = true;
        el.classList.remove('ms-t-show');
        el.classList.add('ms-t-hide');
        setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 280);
    }

    /* ── toast(type, title, message, duration) ───────────────────────── */
    function toast(type, title, message, duration) {
        type     = type    || 'info';
        title    = title   || DEFAULT_TITLES[type] || 'Notice';
        message  = message || '';
        duration = (duration === undefined) ? DEFAULT_DURATION : duration;

        var root = document.getElementById('ms-toast-root');
        if (!root) return;

        var el = document.createElement('div');
        el.className = 'ms-toast ms-toast-' + type;
        el.setAttribute('role', 'alert');
        el.innerHTML =
            '<div class="ms-t-icon">' + (ICONS[type]||'i') + '</div>' +
            '<div class="ms-t-body">' +
                '<p class="ms-t-title">' + esc(title) + '</p>' +
                (message ? '<p class="ms-t-msg">' + esc(message) + '</p>' : '') +
            '</div>' +
            '<button class="ms-t-close" aria-label="Dismiss">&#x2715;</button>' +
            '<div class="ms-t-prog" style="width:100%"></div>';

        el.querySelector('.ms-t-close').addEventListener('click', function () { dismiss(el); });
        root.appendChild(el);

        /* entrance */
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { el.classList.add('ms-t-show'); });
        });

        /* progress bar countdown */
        var bar = el.querySelector('.ms-t-prog');
        var timer = null;

        function startTimer(remaining) {
            if (bar) {
                bar.style.transition = 'width ' + remaining + 'ms linear';
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () { bar.style.width = '0%'; });
                });
            }
            if (remaining > 0) {
                timer = setTimeout(function () { dismiss(el); }, remaining);
            }
        }

        if (duration > 0) {
            startTimer(duration);

            /* pause on hover */
            var pausedAt = null;
            el.addEventListener('mouseenter', function () {
                if (timer) { clearTimeout(timer); timer = null; }
                if (bar)   { pausedAt = parseFloat(bar.style.width || '100') / 100 * duration; bar.style.transition = 'none'; }
            });
            el.addEventListener('mouseleave', function () {
                var left = (pausedAt !== null) ? pausedAt : duration;
                pausedAt = null;
                startTimer(left);
            });
        }
    }

    /* ── shorthand helpers ───────────────────────────────────────────── */
    function error(msg, title)   { toast('error',   title || DEFAULT_TITLES.error,   msg); }
    function warning(msg, title) { toast('warning', title || DEFAULT_TITLES.warning, msg); }
    function success(msg, title) { toast('success', title || DEFAULT_TITLES.success, msg); }
    function info(msg, title)    { toast('info',    title || DEFAULT_TITLES.info,    msg); }

    /* ── confirm(opts) → Promise<boolean> ───────────────────────────── */
    function confirm(opts) {
        opts = opts || {};
        var type        = opts.type        || 'error';
        var title       = opts.title       || DEFAULT_TITLES[type];
        var body        = opts.body        || '';
        var confirmText = opts.confirmText || 'OK';
        var cancelText  = (opts.cancelText !== undefined) ? opts.cancelText : 'Cancel';

        return new Promise(function (resolve) {
            var overlay   = document.getElementById('ms-modal-overlay');
            var box       = document.getElementById('ms-modal-box');
            var iconEl    = document.getElementById('ms-modal-icon');
            var titleEl   = document.getElementById('ms-modal-title');
            var bodyEl    = document.getElementById('ms-modal-body');
            var actionsEl = document.getElementById('ms-modal-actions');
            if (!overlay) { window.alert(body); resolve(true); return; }

            /* icon colours */
            var IC = {
                error:   { bg:'#FEE2E2', color:'#DC2626' },
                warning: { bg:'#FEF3C7', color:'#D97706' },
                success: { bg:'#DCFCE7', color:'#16A34A' },
                info:    { bg:'#DBEAFE', color:'#2563EB' },
            };
            var ic = IC[type] || IC.info;
            iconEl.style.cssText = 'flex-shrink:0;width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.125rem;font-weight:800;background:' + ic.bg + ';color:' + ic.color + ';';
            iconEl.textContent = ICONS[type] || 'i';

            titleEl.textContent = title;
            bodyEl.textContent  = body;

            /* action buttons */
            actionsEl.innerHTML = '';

            if (cancelText) {
                var btnC = document.createElement('button');
                btnC.className   = 'ms-mbtn ms-mbtn-cancel';
                btnC.textContent = cancelText;
                btnC.onclick     = function () { close(false); };
                actionsEl.appendChild(btnC);
            }

            var btnOk = document.createElement('button');
            btnOk.className   = 'ms-mbtn ms-mbtn-' + type;
            btnOk.textContent = confirmText;
            btnOk.onclick     = function () { close(true); };
            actionsEl.appendChild(btnOk);

            /* close helpers */
            function close(result) {
                document.removeEventListener('keydown', onKey);
                box.style.transform = 'scale(0.9) translateY(12px)';
                box.style.opacity   = '0';
                setTimeout(function () {
                    overlay.style.display = 'none';
                    overlay.classList.remove('ms-modal-open');
                    resolve(result);
                }, 200);
            }

            /* keyboard */
            function onKey(e) {
                if (e.key === 'Escape') close(false);
                if (e.key === 'Enter')  close(true);
            }
            document.addEventListener('keydown', onKey);

            /* backdrop click */
            overlay.onclick = function (e) { if (e.target === overlay) close(false); };

            /* open */
            overlay.style.display = 'flex';
            overlay.classList.add('ms-modal-open');
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    box.style.transform = 'scale(1) translateY(0)';
                    box.style.opacity   = '1';
                    btnOk.focus();
                });
            });
        });
    }

    return { toast:toast, confirm:confirm, error:error, warning:warning, success:success, info:info };
}());


/* ════════════════════════════════════════════════════════════════════════
   MilestoneModule
   ════════════════════════════════════════════════════════════════════════ */
window.MilestoneModule = (function () {
    'use strict';

    var _enabled = false;
    var _type    = 'fixed';
    var _seq     = 0;
    var $        = function (id) { return document.getElementById(id); };

    /* ── Grand total from parent page ────────────────────────────────── */
    function getTotal() {
        if (typeof window.getPosPricingBreakdown === 'function') {
            // Round to whole number — no decimals in milestone display
            return Math.round(window.getPosPricingBreakdown().grandTotal || 0);
        }
        var base     = (typeof globalBaseAmount !== 'undefined') ? (globalBaseAmount || 0) : 0;
        var discount = parseFloat(document.getElementById('pos-discount')?.value || 0);
        var gstRate  = typeof window.POS_GST_RATE === 'number' ? window.POS_GST_RATE : 18;
        var taxable  = Math.max(0, base - Math.min(Math.max(0, discount), base));
        return Math.round(taxable + taxable * (gstRate / 100));
    }

    function rows() {
        return Array.from(document.querySelectorAll('#ms-rows-container .ms-row'));
    }

    /* ── Visibility ──────────────────────────────────────────────────── */
    function updateVisibility() {
        var n = rows().length;
        $('ms-empty-state').classList.toggle('hidden', n > 0);
        $('ms-table-header').classList.toggle('hidden', n === 0);
        $('ms-alloc-wrap').classList.toggle('hidden', n === 0);
        $('ms-add-btn-wrap').classList.toggle('hidden', n === 0);
    }

    /* ── Symbol layout (₹ left / % right) ───────────────────────────── */
    function applySymbolLayout(row, type) {
        var L = row.querySelector('.ms-sym-left');
        var R = row.querySelector('.ms-sym-right');
        var I = row.querySelector('.ms-amount');
        if (type === 'percentage') {
            if (L) L.classList.add('hidden');
            if (R) R.classList.remove('hidden');
            if (I) { I.style.paddingLeft='0.4rem'; I.style.paddingRight='1.25rem'; }
        } else {
            if (L) L.classList.remove('hidden');
            if (R) R.classList.add('hidden');
            if (I) { I.style.paddingLeft='1.25rem'; I.style.paddingRight='0.4rem'; }
        }
    }

    /* ── Cascade due-date minimums ───────────────────────────────────── */
    function updateDueDateMins() {
        var today = new Date().toISOString().split('T')[0];
        var list  = rows();
        list.forEach(function (row, i) {
            var d   = row.querySelector('.ms-due-date');
            if (!d) return;
            var min = (i === 0) ? today : (list[i-1].querySelector('.ms-due-date').value || today);
            d.min   = min;
            if (d.value && d.value < min) {
                d.value = '';
                if (i > 0) {
                    MsAlert.warning(
                        'Milestone ' + (i+1) + ' due date was cleared because it was earlier than Milestone ' + i + '\'s date.',
                        'Date Cleared'
                    );
                }
            }
        });
    }

    /* ── Toggle ──────────────────────────────────────────────────────── */
    function toggle() {
        _enabled = !_enabled;
        $('ms-toggle-btn').classList.toggle('ms-on', _enabled);
        $('ms-toggle-btn').setAttribute('aria-checked', String(_enabled));
        $('ms-builder').classList.toggle('hidden', !_enabled);

        // When disabling, clear all rows so validate() returns valid
        if (!_enabled) {
            document.getElementById('ms-rows-container').innerHTML = '';
            _seq = 0;
            updateVisibility();
            recalculate();
        }
    }

    /* ── Amount type ─────────────────────────────────────────────────── */
    function setAmountType(type) {
        _type = type;
        $('ms-btn-flat').className = (type==='fixed')
            ? 'px-3 py-1.5 bg-[#2D5A43] text-white transition-colors text-[11px] font-bold'
            : 'px-3 py-1.5 bg-white hover:bg-gray-50 transition-colors text-[11px] font-bold';
        $('ms-btn-pct').className = (type==='percentage')
            ? 'px-3 py-1.5 bg-[#2D5A43] text-white transition-colors text-[11px] font-bold'
            : 'px-3 py-1.5 bg-white hover:bg-gray-50 transition-colors text-[11px] font-bold';
        rows().forEach(function (r) { applySymbolLayout(r, type); });
        recalculate();
    }

    /* ── Add row ─────────────────────────────────────────────────────── */
    function addRow(defaultDesc) {
        var tpl   = document.getElementById('ms-row-tpl');
        var clone = tpl.content.cloneNode(true);
        var row   = clone.querySelector('.ms-row');
        _seq++;
        row.dataset.idx = _seq;
        if (defaultDesc) {
            var d = row.querySelector('.ms-desc');
            if (d) { d.value = defaultDesc; var c = row.querySelector('.ms-char-count'); if(c) c.textContent = defaultDesc.length+'/64'; }
        }
        applySymbolLayout(row, _type);
        document.getElementById('ms-rows-container').appendChild(row);
        reindex();
        updateDueDateMins();
        updateVisibility();
        recalculate();
    }

    /* ── Remove row ──────────────────────────────────────────────────── */
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

    /* ── Reindex ─────────────────────────────────────────────────────── */
    function reindex() {
        rows().forEach(function (row, i) {
            var b = row.querySelector('.ms-seq');
            row.dataset.idx = i+1;
            if (b) b.textContent = String(i+1);
        });
    }

    /* ── Desc char counter ───────────────────────────────────────────── */
    function onDescInput(el) {
        var c = el.closest('.ms-row').querySelector('.ms-char-count');
        if (c) c.textContent = el.value.length + '/64';
    }

    /* ── Date change ─────────────────────────────────────────────────── */
    function onDateChange(el) {
        updateDueDateMins();
        recalculate();
    }

    /* ── Recalculate allocation bar ──────────────────────────────────── */
   function recalculate() {
        var total = getTotal();
        var sum   = 0;

        rows().forEach(function (row) {
            var val  = parseFloat(row.querySelector('.ms-amount').value) || 0;
            sum += val;
            var comp = row.querySelector('.ms-computed');
            if (comp) {
                if (_type === 'percentage' && val > 0 && total > 0) {
                    var computed = Math.round(total * val / 100);
                    comp.textContent = '= ₹' + computed.toLocaleString('en-IN');
                    comp.classList.remove('hidden');
                } else {
                    comp.classList.add('hidden');
                }
            }
        });

        sum = Math.round(sum * 100) / 100;

        var bar  = $('ms-alloc-bar');
        var disp = $('ms-alloc-display');
        var msg  = $('ms-alloc-msg');
        if (!bar) return;

        var pct, barCls, msgTxt, msgCls;
        if (_type === 'percentage') {
            pct  = Math.min(sum, 100);
            disp.textContent = sum.toFixed(0) + '%';
            if (sum > 100)                       { barCls = 'ms-over'; msgCls = 'text-red-600';   msgTxt = '⚠ Over by ' + (sum - 100).toFixed(0) + '%'; }
            else if (Math.abs(sum - 100) < 0.01) { barCls = 'ms-ok';   msgCls = 'text-green-700'; msgTxt = '✓ Exactly 100% — ready'; }
            else                                 { barCls = 'ms-part'; msgCls = 'text-amber-700'; msgTxt = (100 - sum).toFixed(0) + '% remaining'; }
        } else {
            pct  = total > 0 ? Math.min((sum / total) * 100, 100) : 0;
            disp.textContent = '₹' + sum.toLocaleString('en-IN');
            var diff = Math.round((total - sum) * 100) / 100;
            if (sum > total + 0.01)           { barCls = 'ms-over'; msgCls = 'text-red-600';   msgTxt = '⚠ Over by ₹' + Math.abs(diff).toLocaleString('en-IN'); }
            else if (Math.abs(diff) < 0.01)   { barCls = 'ms-ok';   msgCls = 'text-green-700'; msgTxt = '✓ Total matches — ready'; }
            else                              { barCls = 'ms-part'; msgCls = 'text-amber-700'; msgTxt = '₹' + diff.toLocaleString('en-IN') + ' remaining'; }
        }

        bar.style.width = pct + '%';
        bar.className   = 'h-full rounded-r transition-all duration-300 ' + barCls;
        msg.textContent = msgTxt;
        msg.className   = 'px-3 py-1 text-[10px] font-semibold ' + msgCls;
        msg.classList.remove('hidden');
        updateDueDateMins();
    }
    /* ── Format ISO date → "12 Jan 2025" ────────────────────────────── */
    function fmtDate(iso) {
        if (!iso) return iso;
        var p = iso.split('-');
        var M = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return parseInt(p[2],10)+' '+(M[parseInt(p[1],10)-1]||'')+' '+p[0];
    }

    /* ── Validate and build payload ──────────────────────────────────── */
   function validate() {
        if (!_enabled) return { enabled: false, valid: true, milestones: [] };

        var list = rows();

        // Milestone was enabled but all rows removed — treat as disabled
        if (list.length === 0) {
            return { enabled: true, valid: true, milestones: [] };
            // Return valid with empty milestones — backend will treat as non-milestone booking
        }

        var total    = getTotal();
        var result   = [];
        var sumPct   = 0;
        var sumFixed = 0;

        for (var i = 0; i < list.length; i++) {
            var row    = list[i];
            var desc   = (row.querySelector('.ms-desc').value || '').trim();
            var amount = parseFloat(row.querySelector('.ms-amount').value);
            var due    = row.querySelector('.ms-due-date').value;
            var n      = i + 1;

            if (!due) {
                return { enabled: true, valid: false,
                        title: 'Missing Due Date',
                        error: 'Milestone ' + n + ' needs a due date. Please pick a date to continue.' };
            }

            if (i > 0) {
                var prevDue = list[i - 1].querySelector('.ms-due-date').value;
                if (prevDue && due < prevDue) {
                    return { enabled: true, valid: false,
                            title: 'Date Order Invalid',
                            error: 'Milestone ' + n + ' (' + fmtDate(due) + ') cannot be earlier than Milestone ' + i + ' (' + fmtDate(prevDue) + '). Due dates must go forward in time.' };
                }
            }

            if (!amount || amount <= 0) {
                return { enabled: true, valid: false,
                        title: 'Missing Amount',
                        error: 'Milestone ' + n + ' has no amount set. Please enter a value greater than zero.' };
            }

            if (_type === 'percentage') sumPct  += amount;
            else                        sumFixed += amount;

            result.push({
                title:        desc || ('Milestone ' + n),
                description:  desc || null,
                amount_type:  _type,
                amount:       amount,
                due_date:     due,
                vendor_notes: desc || null,
            });
        }

        if (_type === 'percentage' && Math.abs(sumPct - 100) > 0.01) {
            return { enabled: true, valid: false,
                    title: 'Percentages Don\'t Add Up',
                    error: 'All milestone percentages must total exactly 100%. Your current total is ' + sumPct.toFixed(1) + '%. Please adjust the values.' };
        }

        if (_type === 'fixed' && Math.abs(sumFixed - total) > 0.01) {
            return { enabled: true, valid: false,
                    title: 'Amount Mismatch',
                    error: 'Milestone amounts must sum to the booking total of ₹' + total.toLocaleString('en-IN', { minimumFractionDigits: 2 }) + '. Your current total is ₹' + sumFixed.toLocaleString('en-IN', { minimumFractionDigits: 2 }) + '.' };
        }

        return { enabled: true, valid: true, milestones: result };
    }

    /* ── Hook: Finalize Booking button ───────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('create-booking-btn');
        if (!btn) return;

        btn.addEventListener('click', function (e) {
            if (!_enabled) return;

            var res = validate();
            if (!res.valid) {
                e.stopImmediatePropagation();
                /* Show modal — user must fix before they can proceed */
                MsAlert.confirm({
                    type:        'error',
                    title:       res.title || 'Milestone Error',
                    body:        res.error,
                    confirmText: 'OK',
                    cancelText:  null,   /* no cancel — must fix */
                });
                return;
            }

            window._pendingMilestoneData = res.milestones;
            MsAlert.success(
                res.milestones.length + ' milestone(s) validated and ready.',
                'Milestones Confirmed'
            );
        }, true);
    });

    /* ── Hook: inject milestone data into booking POST fetch ─────────── */
    (function () {
        var _orig = window.fetch;
        window.fetch = function (url, options) {
            options = options || {};
            if (
                window._pendingMilestoneData &&
                typeof url === 'string' &&
                /\/api\/bookings\b/.test(url) &&
                (options.method||'').toUpperCase() === 'POST'
            ) {
                try {
                    var body = JSON.parse(options.body || '{}');
                    body.is_milestone   = true;
                    body.milestone_data = window._pendingMilestoneData;
                    options.body = JSON.stringify(body);
                    window._pendingMilestoneData = null;
                } catch(err) {
                    console.error('[MilestoneModule] fetch patch error:', err);
                }
            }
            return _orig.call(this, url, options);
        };
    }());

    /* ── Hook: recalculate when booking totals update ────────────────── */
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
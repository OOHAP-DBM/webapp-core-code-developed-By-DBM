{{-- ── STYLES ── --}}
<style>
  .hc-day {
    height: 36px; width: 100%;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; cursor: pointer;
    user-select: none; color: #111;
    position: relative;
  }
  .hc-day.hc-disabled { color: #d1d5db; cursor: default; pointer-events: none; }
  .hc-day.hc-empty    { pointer-events: none; }

  .hc-day::before {
    content: ''; position: absolute;
    top: 0; bottom: 0; left: 0; right: 0; z-index: 0;
  }
  .hc-day.hc-in-range::before    { background: rgba(0,168,107,0.13); }
  .hc-day.hc-hover-range::before { background: rgba(0,168,107,0.08); }
  .hc-day.hc-start::before { background: linear-gradient(to right, transparent 50%, rgba(0,168,107,0.13) 50%); }
  .hc-day.hc-end::before   { background: linear-gradient(to left,  transparent 50%, rgba(0,168,107,0.13) 50%); }
  .hc-day.hc-start.hc-end::before  { background: transparent; }
  .hc-day.hc-hover-end::before { background: linear-gradient(to left, transparent 50%, rgba(0,168,107,0.08) 50%); }

  .hc-dot {
    position: absolute; z-index: 1;
    width: 34px; height: 34px; border-radius: 50%;
    background: #00A86B; top: 1px; left: 50%; transform: translateX(-50%);
    pointer-events: none;
  }
  .hc-day.hc-hover-end .hc-dot { background: rgba(0,168,107,0.3); }

  .hc-num { position: relative; z-index: 2; pointer-events: none; }
  .hc-day.hc-start .hc-num,
  .hc-day.hc-end .hc-num { color: #fff; font-weight: 500; }
  .hc-day.hc-hover-end .hc-num { color: #00A86B; }

  .hc-day:hover:not(.hc-disabled):not(.hc-empty):not(.hc-start):not(.hc-end) .hc-num {
    background: #f0f0f0; border-radius: 50%;
    width: 34px; height: 34px;
    display: flex; align-items: center; justify-content: center;
  }

  /* Default state (top of page) */
#tabsBlock {
    opacity: 1;
    transform: translateY(0);
    transition: all 0.3s ease;
}
#hoardingRoot {
    position: relative;
    z-index: 30;
}

#searchBlock {
    opacity: 1;
    transform: translateY(0);
    transition: all 0.3s ease;
}

/* Scrolled state */
.scrolled #tabsBlock {
    opacity: 0;
    transform: translateY(-20px);
    pointer-events: none;
}
.scrolled #searchBlock {
    transform: translateY(-60px); /* move UP into tabs position */
}
/* smooth animation */
#mainHeader,
#headerInner,
#searchWrapper{
transition:all .3s ease;
}

/* normal header */
#headerInner{
max-width:1200px;
}

/* when scrolled */
.scrolled #headerInner{
max-width:850px;
}

/* shrink search */
.scrolled #searchWrapper{
transform:translateY(-55px) scale(.95);
}

/* reduce header spacing */
.scrolled{
padding-top:6px;
padding-bottom:6px;
}
</style>

{{-- ── WRAPPER ── --}}
<div class="relative transition-all duration-300" id="hoardingRoot">

    {{-- TABS --}}
    <div id="tabsBlock" class="flex space-x-8 items-center justify-center h-full transition-all duration-300">

        <a href="{{ url('/#best-hoardings-section') }}" type="button"
                onclick="hoardingTab.switchTab('hoardings', this)"
                id="tab-hoardings"
                class="tab-link flex items-center space-x-2 px-1 py-1 border-b-2 border-gray-400 text-sm font-medium text-gray-900"
                data-scroll-target="#best-hoardings-section">
            <svg width="43" height="37" viewBox="0 0 43 37" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path opacity="0.3" d="M42.9999 35.6624L40.3242 34.1382L38.7858 33.7182H36.5159L21.297 25.0398L20.2399 24.752H5.28516L8.21098 26.4178L8.24873 27.6966L9.79187 28.5886H10.0656L10.0325 27.4607L13.9918 29.707L14.0249 30.9906L15.5727 31.8825H15.8464L15.8087 30.7546L19.8152 33.0292L19.834 33.7182H13.0055L17.2244 36.6346H18.3003L20.0417 35.6624H24.4163L25.4403 36.2523L26.5021 36.5402H41.4568L39.9231 35.6624H42.9999Z" fill="#222121"/>
                <path d="M17.225 36.6346L11.3545 33.2463V32.7178L17.225 36.1061V36.6346Z" fill="#545454"/>
                <path d="M17.2246 36.634L19.7635 35.1711V34.6426L17.2246 36.1055V36.634Z" fill="#2E2E2E"/>
                <path d="M13.8933 31.25L11.3545 32.7176L17.225 36.1059L19.7639 34.643L13.8933 31.25Z" fill="#7A7A7A"/>
                <path opacity="0.3" d="M19.7637 34.6424L18.1498 33.708V34.7226L17.4561 35.1237H18.9331L19.7637 34.6424Z" fill="#222121"/>
                <path d="M17.4557 35.1243L14.9121 33.6567V6.05957L17.4557 7.5272V35.1243Z" fill="#545454"/>
                <path d="M17.4561 35.1242L18.1498 34.7231V7.12598L17.4561 7.5271V35.1242Z" fill="#2E2E2E"/>
                <path d="M15.6058 5.6582L14.9121 6.05933L17.4557 7.52696L18.1494 7.12583L15.6058 5.6582Z" fill="#7A7A7A"/>
                <path d="M19.1217 26.2708L0 15.2282V0.273438L19.1217 11.3161V26.2708Z" fill="#545454"/>
                <path d="M19.1221 26.2712L19.6034 25.9928V11.0381L19.1221 11.3165V26.2712Z" fill="#2E2E2E"/>
                <path d="M0.476623 0L0 0.273707L19.1217 11.3163L19.603 11.0379L0.476623 0Z" fill="#7A7A7A"/>
                <path d="M18.8384 11.5476V25.7001L0.306641 14.9972V0.844727L18.8384 11.5476Z" fill="url(#pg1)"/>
                <defs>
                    <linearGradient id="pg1" x1="0.146192" y1="0.891917" x2="19.0413" y2="25.7095" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#EAF3F4"/><stop offset="1" stop-color="#CEE2E5"/>
                    </linearGradient>
                </defs>
            </svg>
            <span class="md:text-[16px]">Best Hoardings</span>
        </a>

        <div class="h-8 w-px bg-gray-600"></div>

        <a href="{{ url('/#top-spots-section') }}" type="button"
                onclick="hoardingTab.switchTab('spots', this)"
                id="tab-spots"
                class="tab-link flex items-center space-x-2 px-1 py-1 text-sm font-medium text-gray-900"
                data-scroll-target="#top-spots-section">
            <svg width="43" height="37" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_9076_27421)">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M4.96259 0.0866751C5.50544 -0.000182058 6.13923 -0.0503963 6.68616 0.0771751L6.70245 0.0812465L11.4769 1.31625C11.6254 1.3556 11.7831 1.34118 11.922 1.27553L12.0591 1.21039C12.6589 0.922675 13.3375 0.596961 13.9482 0.428675C14.4544 0.288889 15.2131 0.0907465 15.9392 0.0242465C16.4155 -0.0205392 16.7684 0.241389 16.9692 0.444961C17.3221 0.801889 17.5162 1.23075 17.6369 1.62839C17.7374 1.95682 17.7998 2.30696 17.8514 2.60282C18.2191 4.57121 18.3635 6.57483 18.2816 8.5756C17.4117 7.94734 16.3852 7.57156 15.3153 7.48973C14.2454 7.4079 13.1737 7.62319 12.2184 8.11186C11.2631 8.60053 10.4613 9.34358 9.90158 10.259C9.34183 11.1745 9.04583 12.2268 9.04623 13.2998C9.04623 14.1684 9.22809 14.9854 9.53073 15.7427L6.6848 15.0057C6.58732 14.9785 6.48442 14.9771 6.38623 15.0017C5.88952 15.1374 5.37516 15.3206 4.82552 15.516C4.58485 15.602 4.33604 15.6893 4.07909 15.778C3.97413 15.8132 3.86602 15.8512 3.75473 15.892C3.33537 16.0412 2.87259 16.2041 2.43016 16.2882C1.97552 16.3751 1.61723 16.1607 1.40552 15.9734C0.998372 15.6137 0.79073 15.155 0.665872 14.7384C0.576725 14.4172 0.507381 14.0909 0.45823 13.7612C-0.142425 10.521 -0.153918 7.1989 0.424301 3.95453L0.492158 3.57725C0.573587 3.11582 0.678087 2.53903 0.95223 2.00703C1.03417 1.84375 1.13238 1.68916 1.24537 1.5456C1.38181 1.36734 1.5567 1.22214 1.75702 1.12082C2.29987 0.861604 2.90244 0.582032 3.45887 0.428675C3.85652 0.318747 4.40209 0.176247 4.96259 0.0866751Z" fill="#D9F2E6"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.8716 9.17323C14.3295 9.17288 13.7926 9.27937 13.2916 9.48663C12.7906 9.6939 12.3355 9.99786 11.952 10.3811C11.5686 10.7644 11.2645 11.2195 11.0571 11.7204C10.8497 12.2213 10.743 12.7582 10.7432 13.3003C10.7432 14.8949 11.6538 16.3077 12.5142 17.2713C12.9553 17.7639 13.4072 18.167 13.7777 18.452C13.9623 18.5945 14.136 18.7126 14.2839 18.7994C14.3563 18.8438 14.4314 18.8827 14.5092 18.9162C14.6235 18.9669 14.7466 18.9946 14.8716 18.9976C15.0412 18.9976 15.1837 18.9392 15.234 18.9162C15.3109 18.8827 15.3864 18.8438 15.4606 18.7994C15.6085 18.7126 15.7809 18.5945 15.9655 18.452C16.3373 18.167 16.7892 17.7639 17.2276 17.2713C18.0907 16.3091 19 14.8963 19 13.3003C19.0004 12.7581 18.8938 12.221 18.6865 11.72C18.4791 11.219 18.1751 10.7637 17.7916 10.3803C17.4082 9.99684 16.9529 9.69275 16.4519 9.48541C15.9509 9.27806 15.4138 9.17288 14.8716 9.17323Z" fill="#2CB67D"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.8726 12.3125C15.3408 12.3125 15.7208 12.6925 15.7208 13.1607V13.3656C15.7208 13.5906 15.6315 13.8064 15.4724 13.9654C15.3133 14.1245 15.0976 14.2139 14.8726 14.2139C14.6477 14.2139 14.4319 14.1245 14.2728 13.9654C14.1138 13.8064 14.0244 13.5906 14.0244 13.3656V13.1607C14.0244 12.6925 14.4044 12.3125 14.8726 12.3125Z" fill="#D9F2E6"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.0594 1.21431L12.2087 1.14374V8.12353C11.256 8.61283 10.4568 9.35542 9.89891 10.2696C9.34102 11.1838 9.04608 12.2341 9.04656 13.3051C9.04656 14.171 9.22978 14.9893 9.53106 15.7466L6.68513 15.0097C6.58753 14.9829 6.48464 14.982 6.38656 15.007L6.10156 15.0884V0.0078125C6.30242 0.0150506 6.4974 0.0399315 6.68649 0.0824554L6.70142 0.0865268L11.4772 1.32153C11.6253 1.36055 11.7824 1.34614 11.921 1.28081L12.0594 1.21431Z" fill="#2CB67D"/>
                </g>
                <defs>
                <clipPath id="clip0_9076_27421">
                <rect width="19" height="19" fill="white"/>
                </clipPath>
                </defs>
            </svg>

            <span class="md:text-[16px]">Top Spots</span>
        </a>

    </div>
    <div id="searchBlock" class="flex justify-center mt-[-10px] ">
        <form action="{{ route('search') }}" method="GET"
            id="hoardingSearchForm"
            onsubmit="hCal.apply(); return true;"
            class="inline-flex items-center bg-white border border-gray-200 rounded-full shadow-sm hover:shadow-md transition-shadow pl-2 pr-1 w-full max-w-2xl">

            {{-- WHERE --}}
            <div class="flex flex-col flex-grow px-6 border-r border-gray-200 w-[200px] xl:w-[400px]">
                <label class="text-sm font-bold uppercase tracking-wider text-gray-900">Where</label>
                <input type="text" name="location" onfocus="resetHeader()"
                    placeholder="Search Hoardings..."
                    value="{{ request('location') }}"  {{-- ← add this --}}
                    class="text-sm text-gray-500 outline-none placeholder-gray-400 border-none p-0 focus:ring-0 w-full"/>
            </div>
   
            {{-- WHEN trigger --}}
            <div class="flex flex-col flex-grow px-6 hidden md:flex xl:w-[400px] w-[200px] cursor-pointer select-none"
                 id="hoardingWhenTrigger"
                 onclick="resetHeader(); hCal.toggle(event)">
                <span class="text-sm font-bold uppercase tracking-wider text-gray-900">When</span>
                <span class="text-sm text-gray-400" id="hoardingDateDisplay">Select dates</span>
                <input type="hidden" name="date_from" id="hoardingDateFrom" value="{{ request('date_from') }}"/>
                <input type="hidden" name="date_to"   id="hoardingDateTo"   value="{{ request('date_to') }}"/>
            </div>

            {{-- SUBMIT --}}
            <button type="submit"
                    class="bg-[#00A86B] my-1 p-4 rounded-full text-white hover:bg-green-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
        </form>
    </div>

</div>{{-- end #hoardingRoot --}}


{{-- CALENDAR POPUP — intentionally OUTSIDE the form --}}
<div id="hoardingCalPopup"
     style="display:none; position:fixed; z-index:9999; background:#fff;
            border:1px solid #e5e7eb; border-radius:16px;
            box-shadow:0 8px 32px rgba(0,0,0,0.13); padding:24px;
            width:680px; max-width:96vw;"
     onclick="event.stopPropagation()">

    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
        <button type="button" onclick="hCal.changeMonth(-1)"
                style="width:32px;height:32px;border-radius:50%;border:1px solid #e5e7eb;
                       background:none;cursor:pointer;font-size:18px;display:flex;
                       align-items:center;justify-content:center;">&#8249;</button>
        <span style="font-size:12px;color:#6b7280;">Select check-in &amp; check-out</span>
        <button type="button" onclick="hCal.changeMonth(1)"
                style="width:32px;height:32px;border-radius:50%;border:1px solid #e5e7eb;
                       background:none;cursor:pointer;font-size:18px;display:flex;
                       align-items:center;justify-content:center;">&#8250;</button>
    </div>

    {{-- Two-month grid --}}
    <div id="hoardingCalMonths" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;"></div>

    {{-- Footer --}}
    <div style="display:flex;align-items:center;justify-content:space-between;
                margin-top:16px;padding-top:14px;border-top:1px solid #f3f4f6;">
        <span style="font-size:12px;color:#6b7280;" id="hoardingCalHint">Select a start date</span>
        <div style="display:flex;gap:8px;">
            <button type="button" onclick="hCal.clear()"
                    style="padding:6px 16px;font-size:12px;border:1px solid #e5e7eb;
                           border-radius:8px;background:#fff;cursor:pointer;">Clear</button>
            <button type="button" id="hoardingCalApply" onclick="hCal.apply()"
                    style="padding:6px 16px;font-size:12px;border:none;border-radius:8px;
                           background:#00A86B;color:#fff;cursor:pointer;opacity:0.4;pointer-events:none;">
                Apply
            </button>
        </div>
    </div>
</div>


<script>
(function () {
    'use strict';

    /* ── Tab switching ── */
    window.hoardingTab = {
        switchTab: function (tab, el) {
            ['tab-hoardings', 'tab-spots'].forEach(function (id) {
                var b = document.getElementById(id);
                b.classList.remove('border-b-2', 'border-gray-400', 'text-gray-900');
                b.classList.add('text-gray-500');
            });
            el.classList.add('border-b-2', 'border-gray-400', 'text-gray-900');
            el.classList.remove('text-gray-500');
            var target = document.querySelector(el.getAttribute('data-scroll-target'));
            if (target) window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - 100, behavior: 'smooth' });
        }
    };

    /* ── Calendar ── */
    window.hCal = {
        baseYear  : new Date().getFullYear(),
        baseMonth : new Date().getMonth(),
        startDate : null,
        endDate   : null,
        hoverDate : null,
        phase     : 0,
        _hoverTimer: null,
        MONTHS    : ['January','February','March','April','May','June','July','August','September','October','November','December'],
        DAYS      : ['Su','Mo','Tu','We','Th','Fr','Sa'],

        fmt: function (d) {
            return d.getDate() + ' ' + hCal.MONTHS[d.getMonth()].slice(0, 3) + ' ' + d.getFullYear();
        },

        toISO: function (d) {
            if (!d) return '';
            return d.getFullYear() + '-' +
                   String(d.getMonth() + 1).padStart(2, '0') + '-' +
                   String(d.getDate()).padStart(2, '0');
        },

        reposition: function () {
            var trigger = document.getElementById('hoardingWhenTrigger');
            var popup   = document.getElementById('hoardingCalPopup');
            var rect    = trigger.getBoundingClientRect();
            popup.style.top  = (rect.bottom + window.scrollY + 8) + 'px';
            var left = rect.left + window.scrollX + rect.width / 2 - 340;
            left = Math.max(8, Math.min(left, window.innerWidth - 688));
            popup.style.left = left + 'px';
        },

        toggle: function (e) {
            if (e) e.stopPropagation();
            var popup = document.getElementById('hoardingCalPopup');
            if (popup.style.display === 'none' || !popup.style.display) {
                hCal.reposition();
                popup.style.display = 'block';
                hCal.render();
            } else {
                popup.style.display = 'none';
            }
        },

        close: function () {
            document.getElementById('hoardingCalPopup').style.display = 'none';
        },

        changeMonth: function (dir) {
            hCal.baseMonth += dir;
            if (hCal.baseMonth > 11) { hCal.baseMonth = 0;  hCal.baseYear++; }
            if (hCal.baseMonth < 0)  { hCal.baseMonth = 11; hCal.baseYear--; }
            hCal.render();
        },

        setApply: function (on) {
            var btn = document.getElementById('hoardingCalApply');
            btn.style.opacity      = on ? '1'    : '0.4';
            btn.style.pointerEvents = on ? 'auto' : 'none';
        },

        /* Hover: debounced via rAF so it never interrupts a click */
        setHover: function (cd) {
            if (hCal.phase !== 1) return;
            if (hCal._hoverTimer) cancelAnimationFrame(hCal._hoverTimer);
            hCal._hoverTimer = requestAnimationFrame(function () {
                if (hCal.phase !== 1) return;
                hCal.hoverDate = cd;
                hCal.renderHover();
            });
        },

        handleClick: function (date) {
            /* Cancel any pending hover rAF so it can't race with this click */
            if (hCal._hoverTimer) { cancelAnimationFrame(hCal._hoverTimer); hCal._hoverTimer = null; }

            var sel = new Date(date.getFullYear(), date.getMonth(), date.getDate());

            if (hCal.phase === 0) {
                hCal.startDate = sel;
                hCal.endDate   = null;
                hCal.hoverDate = null;
                hCal.phase     = 1;
            } else {
                if (sel.getTime() < hCal.startDate.getTime()) {
                    hCal.startDate = sel;
                    hCal.endDate   = null;
                    hCal.hoverDate = null;
                } else if (sel.getTime() === hCal.startDate.getTime()) {
                    hCal.endDate   = null;
                    hCal.hoverDate = null;
                } else {
                    hCal.endDate   = sel;
                    hCal.hoverDate = null;
                    hCal.phase     = 0;
                }
            }

            hCal.setApply(!!hCal.startDate);
            hCal.render();
        },

        /* Lightweight hover update — toggles classes on existing nodes, no DOM rebuild */
        renderHover: function () {
            var startTs = hCal.startDate ? hCal.startDate.getTime() : null;
            var hoverTs = (hCal.phase === 1 && hCal.hoverDate) ? hCal.hoverDate.getTime() : null;

            document.querySelectorAll('#hoardingCalMonths .hc-day:not(.hc-disabled):not(.hc-empty)').forEach(function (cell) {
                var ts         = parseInt(cell.dataset.ts, 10);
                var inHover    = startTs && hoverTs && ts > startTs && ts < hoverTs;
                var isHoverEnd = startTs && hoverTs && ts === hoverTs;

                cell.classList.toggle('hc-hover-range', !!inHover);
                cell.classList.toggle('hc-hover-end',   !!isHoverEnd);

                /* manage the dot span for hover-end */
                var existingDot = cell.querySelector('.hc-dot');
                var isStart     = cell.classList.contains('hc-start');
                var isEnd       = cell.classList.contains('hc-end');

                if (isHoverEnd && !existingDot && !isStart && !isEnd) {
                    var dot = document.createElement('span');
                    dot.className = 'hc-dot';
                    cell.insertBefore(dot, cell.firstChild);
                } else if (!isHoverEnd && !isStart && !isEnd && existingDot) {
                    existingDot.remove();
                }
            });

            var hint = document.getElementById('hoardingCalHint');
            if (hCal.hoverDate && hCal.phase === 1 && hCal.startDate) {
                var days = Math.round((hCal.hoverDate - hCal.startDate) / 86400000);
                if (days > 0) hint.textContent = days + ' day' + (days === 1 ? '' : 's');
            }
        },

        buildMonth: function (year, month) {
            var wrap  = document.createElement('div');
            var title = document.createElement('div');
            title.style.cssText = 'text-align:center;font-size:13px;font-weight:500;color:#1f2937;margin-bottom:8px;';
            title.textContent   = hCal.MONTHS[month] + ' ' + year;
            wrap.appendChild(title);

            var grid = document.createElement('div');
            grid.style.cssText = 'display:grid;grid-template-columns:repeat(7,1fr);gap:1px;';

            hCal.DAYS.forEach(function (d) {
                var lbl = document.createElement('div');
                lbl.style.cssText = 'text-align:center;font-size:11px;color:#9ca3af;font-weight:500;padding-bottom:4px;';
                lbl.textContent   = d;
                grid.appendChild(lbl);
            });

            var today    = new Date(); today.setHours(0, 0, 0, 0);
            var firstDay = new Date(year, month, 1).getDay();
            var daysInM  = new Date(year, month + 1, 0).getDate();
            var startTs  = hCal.startDate ? hCal.startDate.getTime() : null;
            var endTs    = hCal.endDate   ? hCal.endDate.getTime()   : null;

            for (var i = 0; i < firstDay; i++) {
                var emp = document.createElement('div');
                emp.className = 'hc-day hc-empty';
                grid.appendChild(emp);
            }

            for (var day = 1; day <= daysInM; day++) {
                var cell     = document.createElement('div');
                var cellDate = new Date(year, month, day);
                var ts       = cellDate.getTime();
                var classes  = ['hc-day'];

                cell.dataset.ts = ts; /* stored for renderHover */

                if (cellDate < today) {
                    classes.push('hc-disabled');
                    cell.className = classes.join(' ');
                    var nd = document.createElement('span');
                    nd.className   = 'hc-num';
                    nd.textContent = day;
                    cell.appendChild(nd);
                    grid.appendChild(cell);
                    continue;
                }

                var isStart = !!(startTs && ts === startTs);
                var isEnd   = !!(endTs   && ts === endTs);
                var inRange = !!(startTs && endTs && ts > startTs && ts < endTs);

                if (isStart) classes.push('hc-start');
                if (isEnd)   classes.push('hc-end');
                if (inRange) classes.push('hc-in-range');

                cell.className = classes.join(' ');

                if (isStart || isEnd) {
                    var dot = document.createElement('span');
                    dot.className = 'hc-dot';
                    cell.appendChild(dot);
                }

                var num = document.createElement('span');
                num.className   = 'hc-num';
                num.textContent = day;
                cell.appendChild(num);

                (function (cd) {
                    cell.addEventListener('mouseenter', function () { hCal.setHover(cd); });
                    cell.addEventListener('click',      function () { hCal.handleClick(cd); });
                })(cellDate);

                grid.appendChild(cell);
            }

            wrap.appendChild(grid);
            return wrap;
        },

        render: function () {
            var container = document.getElementById('hoardingCalMonths');
            container.innerHTML = '';
            for (var i = 0; i < 2; i++) {
                var m = hCal.baseMonth + i, y = hCal.baseYear;
                if (m > 11) { m -= 12; y++; }
                container.appendChild(hCal.buildMonth(y, m));
            }

            var hint = document.getElementById('hoardingCalHint');
            if (!hCal.startDate) {
                hint.textContent = 'Select a start date';
            } else if (!hCal.endDate) {
                hint.textContent = 'Now select an end date (optional)';
            } else {
                var days = Math.round((hCal.endDate - hCal.startDate) / 86400000);
                hint.textContent = days + ' day' + (days === 1 ? '' : 's') + ' selected';
            }
        },

        apply: function () {
            var fromInput = document.getElementById('hoardingDateFrom');
            var toInput   = document.getElementById('hoardingDateTo');
            var disp      = document.getElementById('hoardingDateDisplay');

            if (!hCal.startDate) {
                fromInput.value = '';
                toInput.value   = '';
                hCal.close();
                return;
            }

            fromInput.value = hCal.toISO(hCal.startDate);
            toInput.value   = hCal.endDate ? hCal.toISO(hCal.endDate) : '';

            disp.textContent = hCal.startDate && hCal.endDate
                ? hCal.fmt(hCal.startDate) + ' → ' + hCal.fmt(hCal.endDate)
                : hCal.fmt(hCal.startDate);

            disp.style.color = '#374151';
            hCal.close();
        },

        clear: function () {
            hCal.startDate = null;
            hCal.endDate   = null;
            hCal.hoverDate = null;
            hCal.phase     = 0;
            if (hCal._hoverTimer) { cancelAnimationFrame(hCal._hoverTimer); hCal._hoverTimer = null; }
            hCal.setApply(false);
            var disp = document.getElementById('hoardingDateDisplay');
            disp.textContent = 'Select dates';
            disp.style.color = '';
            document.getElementById('hoardingDateFrom').value = '';
            document.getElementById('hoardingDateTo').value   = '';
            hCal.render();
        }
    };

    document.addEventListener('click', function (e) {
        var popup   = document.getElementById('hoardingCalPopup');
        var trigger = document.getElementById('hoardingWhenTrigger');
        if (popup && trigger && !popup.contains(e.target) && !trigger.contains(e.target)) {
            hCal.close();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        hoardingTab.switchTab('hoardings', document.getElementById('tab-hoardings'));
    });

})();
</script>

{{-- Pre-fill location from URL --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    hoardingTab.switchTab('hoardings', document.getElementById('tab-hoardings'));

    // ── Restore location ──────────────────────────────────────
    var locationParam = "{{ request('location') }}";
    if (locationParam) {
        var locationInput = document.querySelector('input[name="location"]');
        if (locationInput) locationInput.value = locationParam;
    }

    // ── Restore calendar dates ────────────────────────────────
    var dateFrom = "{{ request('date_from') }}";
    var dateTo   = "{{ request('date_to') }}";

    if (dateFrom) {
        // Parse YYYY-MM-DD safely without timezone shifting
        var parseDateLocal = function (str) {
            var parts = str.split('-');
            return new Date(
                parseInt(parts[0], 10),
                parseInt(parts[1], 10) - 1,
                parseInt(parts[2], 10)
            );
        };

        hCal.startDate = parseDateLocal(dateFrom);
        hCal.endDate   = dateTo ? parseDateLocal(dateTo) : null;
        hCal.phase     = 0; // range is complete, ready for fresh pick

        // Update the hidden inputs
        document.getElementById('hoardingDateFrom').value = dateFrom;
        document.getElementById('hoardingDateTo').value   = dateTo || '';

        // Update the display text
        var disp = document.getElementById('hoardingDateDisplay');
        if (hCal.startDate && hCal.endDate) {
            disp.textContent = hCal.fmt(hCal.startDate) + ' → ' + hCal.fmt(hCal.endDate);
        } else {
            disp.textContent = hCal.fmt(hCal.startDate);
        }
        disp.style.color = '#374151';

        // Enable the Apply button
        hCal.setApply(true);

        // Jump calendar to the month containing start date
        hCal.baseYear  = hCal.startDate.getFullYear();
        hCal.baseMonth = hCal.startDate.getMonth();
    }
});
</script>
<script>
    function resetHeader() {
        var root = document.getElementById('hoardingRoot');
        root.classList.remove('scrolled');
    }
(function () {
    var root = document.getElementById('hoardingRoot');
    var lastScroll = 0;

    window.addEventListener('scroll', function () {
        var currentScroll = window.scrollY;

        if (currentScroll > 50) {
            root.classList.add('scrolled');
        } else {
            root.classList.remove('scrolled');
        }

        lastScroll = currentScroll;
    });
})();
(function () {
    var root = document.getElementById('hoardingRoot');
    var isInteracting = false;

    window.resetHeader = function () {
        root.classList.remove('scrolled');
        isInteracting = true;

        // unlock after user finishes interaction
        setTimeout(function () {
            isInteracting = false;
        }, 1000);
    };

    window.addEventListener('scroll', function () {
        if (isInteracting) return;

        if (window.scrollY > 50) {
            root.classList.add('scrolled');
        } else {
            root.classList.remove('scrolled');
        }
    });
})();
</script>
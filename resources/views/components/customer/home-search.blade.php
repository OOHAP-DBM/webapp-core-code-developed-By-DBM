{{-- ============================================================
     DROP-IN REPLACEMENT — paste where your existing block was
     ============================================================ --}}

{{-- ── STYLES ── --}}
<style>
    .hc-day {
        height: 34px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        cursor: pointer;
        border-radius: 50%;
        user-select: none;
        color: #111;
        transition: background 0.1s;
    }
    .hc-day:hover:not(.hc-disabled):not(.hc-empty) { background: #f0f0f0; }
    .hc-day.hc-disabled { color: #d1d5db; cursor: default; pointer-events: none; }
    .hc-day.hc-empty    { pointer-events: none; }
    .hc-day.hc-start    { background: #00A86B !important; color: #fff !important; border-radius: 50% 0 0 50%; }
    .hc-day.hc-end      { background: #00A86B !important; color: #fff !important; border-radius: 0 50% 50% 0; }
    .hc-day.hc-start.hc-end { border-radius: 50%; }
    .hc-day.hc-in-range { background: rgba(0,168,107,0.13); border-radius: 0; color: #111; }
    .hc-day.hc-hover-range { background: rgba(0,168,107,0.07); border-radius: 0; }
</style>

{{-- ── WRAPPER ── --}}
<div class="relative" id="hoardingRoot">

    {{-- TABS --}}
    <div class="flex space-x-8 items-center justify-center h-full">

        <button type="button"
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
            <span>Best Hoardings</span>
        </button>

        <div class="h-8 w-px bg-gray-600"></div>

        <button type="button"
                onclick="hoardingTab.switchTab('spots', this)"
                id="tab-spots"
                class="tab-link flex items-center space-x-2 px-1 py-1 text-sm font-medium text-gray-900"
                data-scroll-target="#top-spots-section">
            <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.09552 0.141417C8.98123 -0.000297041 10.0153 -0.0822256 10.9077 0.125917L10.9342 0.13256L18.7241 2.14756C18.9664 2.21177 19.2237 2.18824 19.4504 2.08113L19.674 1.97485C20.6527 1.50542 21.7599 0.973989 22.7563 0.699417C23.5822 0.471346 24.82 0.14806 26.0047 0.0395601C26.7819 -0.0335113 27.3576 0.393846 27.6853 0.725989C28.261 1.30835 28.5777 2.00806 28.7747 2.65685C28.9386 3.1927 29.0404 3.76399 29.1246 4.2467C29.7246 7.45828 29.9601 10.7274 29.8265 13.9918C28.4073 12.9667 26.7324 12.3536 24.9868 12.2201C23.2412 12.0866 21.4926 12.4378 19.9339 13.2351C18.3753 14.0324 17.0672 15.2448 16.1539 16.7384C15.2406 18.2321 14.7576 19.949 14.7583 21.6997C14.7583 23.1168 15.055 24.4498 15.5488 25.6854L10.9054 24.4831C10.7464 24.4386 10.5785 24.4363 10.4183 24.4764C9.60787 24.6978 8.76866 24.9968 7.87187 25.3156C7.47921 25.4559 7.07325 25.5983 6.65402 25.743C6.48278 25.8006 6.30637 25.8626 6.1248 25.929C5.44059 26.1726 4.68552 26.4383 3.96366 26.5756C3.22187 26.7173 2.6373 26.3674 2.29187 26.0618C1.62759 25.4751 1.2888 24.7266 1.08509 24.0468C0.939636 23.5228 0.826495 22.9904 0.746301 22.4526C-0.233714 17.1658 -0.252466 11.7456 0.690944 6.45213L0.801658 5.83656C0.934516 5.0837 1.10502 4.14263 1.5523 3.27463C1.68599 3.00822 1.84623 2.75599 2.03059 2.52177C2.2532 2.23092 2.53854 1.99402 2.86537 1.8287C3.75109 1.40577 4.73423 0.949632 5.64209 0.699417C6.29087 0.52006 7.18102 0.28756 8.09552 0.141417Z" fill="#D9F2E6"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M24.2642 14.9661C23.3796 14.9655 22.5037 15.1393 21.6863 15.4774C20.869 15.8156 20.1263 16.3115 19.5007 16.9369C18.8751 17.5623 18.3789 18.3048 18.0405 19.122C17.7021 19.9393 17.528 20.8152 17.5283 21.6997C17.5283 24.3015 19.0141 26.6066 20.418 28.1787C21.1376 28.9825 21.875 29.6402 22.4795 30.1052C22.7806 30.3377 23.064 30.5303 23.3054 30.672C23.4235 30.7443 23.546 30.8078 23.673 30.8624C23.8594 30.9452 24.0603 30.9903 24.2642 30.9953C24.541 30.9953 24.7735 30.9001 24.8554 30.8624C24.9809 30.8078 25.1041 30.7443 25.2252 30.672C25.4665 30.5303 25.7478 30.3377 26.0489 30.1052C26.6556 29.6402 27.393 28.9825 28.1082 28.1787C29.5165 26.6088 31 24.3037 31 21.6997C31.0006 20.815 30.8268 19.9388 30.4885 19.1213C30.1502 18.3038 29.654 17.5611 29.0284 16.9355C28.4028 16.3099 27.6601 15.8137 26.8426 15.4754C26.0251 15.1371 25.1489 14.9655 24.2642 14.9661Z" fill="#2CB67D"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M24.2638 20.0947C25.0277 20.0947 25.6477 20.7147 25.6477 21.4787V21.813C25.6477 22.1801 25.5019 22.5321 25.2424 22.7916C24.9829 23.0511 24.6309 23.1969 24.2638 23.1969C23.8968 23.1969 23.5448 23.0511 23.2852 22.7916C23.0257 22.5321 22.8799 22.1801 22.8799 21.813V21.4787C22.8799 20.7147 23.4999 20.0947 24.2638 20.0947Z" fill="#D9F2E6"/>
            </svg>
            <span>Top Spots</span>
        </button>

    </div>

    {{-- SEARCH FORM --}}
    <div class="flex justify-center mt-[-10px] xl:py-1">
        <form action="{{ route('search') }}" method="GET"
            id="hoardingSearchForm" 
            onsubmit="if(hCal.startDate && !document.getElementById('hoardingDateFrom').value) hCal.apply();"
            class="inline-flex items-center bg-white border border-gray-200 rounded-full shadow-sm hover:shadow-md transition-shadow pl-2 pr-1 w-full max-w-2xl">
            {{-- WHERE --}}
            <div class="flex flex-col flex-grow px-6 border-r border-gray-200 xl:pr-40 xl:ml-5">
                <label class="text-sm font-bold uppercase tracking-wider text-gray-900">Where</label>
                <input type="text" name="location" placeholder="Search Hoardings..."
                       class="text-sm text-gray-500 outline-none placeholder-gray-400 border-none p-0 focus:ring-0 w-full"/>
            </div>

            {{-- WHEN trigger (not a button — just a div) --}}
            <div class="flex flex-col flex-grow px-6 hidden md:flex xl:mr-20 xl:ml-5 cursor-pointer select-none"
                 id="hoardingWhenTrigger"
                 onclick="hCal.toggle(event)">
                <span class="text-sm font-bold uppercase tracking-wider text-gray-900">When</span>
                <span class="text-sm text-gray-400" id="hoardingDateDisplay">Select dates</span>
                <input type="hidden" name="start_date" id="hoardingDateFrom"/>
                <input type="hidden" name="end_date"   id="hoardingDateTo"/>
            </div>

            {{-- SUBMIT --}}
            <button type="submit"
                    class="bg-[#00A86B] p-4 rounded-full text-white hover:bg-green-600 transition-colors">
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
                       align-items:center;justify-content:center;">
            &#8249;
        </button>
        <span style="font-size:12px;color:#6b7280;">Select check-in &amp; check-out</span>
        <button type="button" onclick="hCal.changeMonth(1)"
                style="width:32px;height:32px;border-radius:50%;border:1px solid #e5e7eb;
                       background:none;cursor:pointer;font-size:18px;display:flex;
                       align-items:center;justify-content:center;">
            &#8250;
        </button>
    </div>

    {{-- Two-month grid --}}
    <div id="hoardingCalMonths" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;"></div>

    {{-- Footer --}}
    <div style="display:flex;align-items:center;justify-content:space-between;
                margin-top:16px;padding-top:14px;border-top:1px solid #f3f4f6;">
        <span style="font-size:12px;color:#6b7280;" id="hoardingCalHint">Click a start date</span>
        <div style="display:flex;gap:8px;">
            <button type="button" onclick="hCal.clear()"
                    style="padding:6px 16px;font-size:12px;border:1px solid #e5e7eb;
                           border-radius:8px;background:#fff;cursor:pointer;">
                Clear
            </button>
            <button type="button" id="hoardingCalApply" onclick="hCal.apply()"
                    style="padding:6px 16px;font-size:12px;border:none;border-radius:8px;
                           background:#00A86B;color:#fff;cursor:pointer;opacity:0.4;pointer-events:none;">
                Apply
            </button>
        </div>
    </div>
</div>


{{-- ── SCRIPT ── --}}
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
        baseYear : new Date().getFullYear(),
        baseMonth: new Date().getMonth(),
        startDate: null,
        endDate  : null,
        hoverDate: null,
        phase    : 0,
        MONTHS   : ['January','February','March','April','May','June','July','August','September','October','November','December'],
        DAYS     : ['Su','Mo','Tu','We','Th','Fr','Sa'],

        fmt: function (d) {
            return d.getDate() + ' ' + hCal.MONTHS[d.getMonth()].slice(0,3) + ' ' + d.getFullYear();
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
            if (hCal.baseMonth > 11) { hCal.baseMonth = 0; hCal.baseYear++; }
            if (hCal.baseMonth < 0)  { hCal.baseMonth = 11; hCal.baseYear--; }
            hCal.render();
        },

        setApply: function (on) {
            var btn = document.getElementById('hoardingCalApply');
            if (on) {
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
            } else {
                btn.style.opacity = '0.4';
                btn.style.pointerEvents = 'none';
            }
        },

      handleClick: function (date) {
            // Create a clean date object at midnight local time
            const selectedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

            if (hCal.phase === 0) {
                // Starting a new selection
                hCal.startDate = selectedDate;
                hCal.endDate   = null;
                hCal.hoverDate = null;
                hCal.phase     = 1;
                hCal.setApply(true); 
            } else {
                // Selecting the end date
                if (selectedDate <= hCal.startDate) {
                    // If user selects a date before the start, make that the new start
                    hCal.startDate = selectedDate;
                    hCal.endDate   = null;
                } else {
                    // Valid range selected
                    hCal.endDate   = selectedDate;
                    hCal.phase     = 0; // Selection complete
                }
                hCal.setApply(true);
            }
            hCal.render();
        },

        buildMonth: function (year, month) {
            var wrap  = document.createElement('div');
            var title = document.createElement('div');
            title.style.cssText    = 'text-align:center;font-size:13px;font-weight:500;color:#1f2937;margin-bottom:8px;';
            title.textContent      = hCal.MONTHS[month] + ' ' + year;
            wrap.appendChild(title);

            var grid = document.createElement('div');
            grid.style.cssText = 'display:grid;grid-template-columns:repeat(7,1fr);gap:2px;';

            hCal.DAYS.forEach(function (d) {
                var lbl = document.createElement('div');
                lbl.style.cssText = 'text-align:center;font-size:11px;color:#9ca3af;font-weight:500;padding-bottom:4px;';
                lbl.textContent   = d;
                grid.appendChild(lbl);
            });

            var today    = new Date(); today.setHours(0,0,0,0);
            var firstDay = new Date(year, month, 1).getDay();
            var daysInM  = new Date(year, month + 1, 0).getDate();

            for (var i = 0; i < firstDay; i++) {
                var emp = document.createElement('div');
                emp.className = 'hc-day hc-empty';
                grid.appendChild(emp);
            }

            for (var d = 1; d <= daysInM; d++) {
                var cell     = document.createElement('div');
                cell.className   = 'hc-day';
                cell.textContent = d;

                var cellDate = new Date(year, month, d);
                var ts       = cellDate.getTime();
                var startTs  = hCal.startDate ? hCal.startDate.getTime() : null;
                var endTs    = hCal.endDate   ? hCal.endDate.getTime()   : null;
                var hoverTs  = hCal.hoverDate ? hCal.hoverDate.getTime() : null;

                if (cellDate < today) {
                    cell.classList.add('hc-disabled');
                    grid.appendChild(cell);
                    continue;
                }

                if (startTs && ts === startTs)                              cell.classList.add('hc-start');
                if (endTs   && ts === endTs)                                cell.classList.add('hc-end');
                if (startTs && endTs && ts > startTs && ts < endTs)        cell.classList.add('hc-in-range');
                if (startTs && !endTs && hoverTs && ts > startTs && ts < hoverTs) cell.classList.add('hc-hover-range');
                if (startTs && !endTs && hoverTs && ts === hoverTs)        cell.classList.add('hc-end');

                (function (cd) {
                    cell.addEventListener('mouseenter', function () {
                        if (hCal.phase === 1) { hCal.hoverDate = cd; hCal.render(); }
                    });
                    cell.addEventListener('click', function () { hCal.handleClick(cd); });
                })(cellDate);

                grid.appendChild(cell);
            }

            wrap.appendChild(grid);
            return wrap;
        },

       /* ... inside the hCal object ... */

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
                hint.textContent = 'Click a start date';
            } else if (!hCal.endDate) {
                // Corrected: Use hCal.startDate here
                hint.textContent = 'Select end date'; 
            } else {
                var days = Math.round((hCal.endDate - hCal.startDate) / 86400000);
                hint.textContent = days + ' day' + (days !== 1 ? 's' : '') + ' selected';
            }
        },

        apply: function () {
            if (!hCal.startDate) {
                hCal.close();
                return;
            }

            const fromInput = document.getElementById('hoardingDateFrom');
            const toInput   = document.getElementById('hoardingDateTo');
            const disp      = document.getElementById('hoardingDateDisplay');

            const toLocalISO = (date) => {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            };

            const finalStart = hCal.startDate;
            let finalEnd = hCal.endDate;

            // If user didn't pick an end date, treat as single day selection
            if (!finalEnd) {
                finalEnd = finalStart;
            }

            fromInput.value = toLocalISO(finalStart);
            toInput.value   = toLocalISO(finalEnd);

            // Update Display
            if (finalEnd.getTime() !== finalStart.getTime()) {
                disp.textContent = hCal.fmt(finalStart) + ' → ' + hCal.fmt(finalEnd);
            } else {
                disp.textContent = hCal.fmt(finalStart);
            }

            disp.style.color = '#374151';
            hCal.close();
        },
        clear: function () {
            hCal.startDate = null;
            hCal.endDate   = null;
            hCal.hoverDate = null;
            hCal.phase     = 0;
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
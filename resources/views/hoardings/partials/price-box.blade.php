<div class="w-full max-w-sm">

@php
    // Always use OOH price columns for both types
    $monthly = $hoarding->monthly_price;
    $base    = $hoarding->base_monthly_price;
    $isDooh = $hoarding->hoarding_type === 'dooh';
@endphp

<div>
    {{-- MAIN PRICE --}}
    <div class="text-xl font-bold">
        @if(empty($monthly) || $monthly == 0)
            ₹{{ number_format($base) }}/Month
        @else
            ₹{{ number_format($monthly) }}/Month
        @endif
    </div>

    {{-- CUT PRICE --}}
    @if(
        !empty($monthly)
        && $monthly > 0
        && !empty($base)
        && $base > $monthly
    )
        <div class="text-sm text-gray-400 line-through">
            ₹{{ number_format($base) }}
        </div>
    @endif

    {{-- PACKAGES (OPTIONAL) --}}
    @if($hoarding->packages->count())
        <p class="mt-4 font-semibold text-sm">Available Packages</p>

        @foreach($hoarding->packages as $pkg)
            @php
                $basePrice = $hoarding->base_monthly_price * $pkg->min_booking_duration;
                $discount  = ($basePrice * $pkg->discount_percent) / 100;
                $finalPrice = $basePrice - $discount;
            @endphp

            <div class="package-card"
                onclick="selectPackage({
                    id: {{ $pkg->id }},
                    months: {{ $pkg->min_booking_duration }},
                    discount: {{ $pkg->discount_percent }},
                    price: {{ round($finalPrice) }},
                    type: '{{ $isDooh ? 'dooh' : 'ooh' }}'
                }, this)">

                <p class="font-medium">{{ $pkg->package_name }}</p>

                <p class="text-xs text-gray-500">
                    {{ $pkg->min_booking_duration }} Month Package
                    @if($pkg->discount_percent)
                        • {{ $pkg->discount_percent }}% OFF
                    @endif
                </p>

                <p class="font-semibold">
                    ₹{{ number_format($finalPrice) }}
                </p>

                <p class="text-xs text-gray-400 line-through">
                    ₹{{ number_format($basePrice) }}
                </p>
            </div>
        @endforeach
    @endif
    @php
        $isOwnerVendor = false;
        if(
            auth()->check()
            && auth()->user()->active_role === 'vendor'
            && isset($hoarding->vendor_id)
            && auth()->id() === (int)$hoarding->vendor_id
        ){
            $isOwnerVendor = true;
        }
    @endphp
    <div class="bg-white border border-gray-200 p-4 mt-4" style="border-radius:5px;">
        @if($isOwnerVendor)
            <div class="flex justify-center mt-2">
                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-3 py-2 rounded-full">
                    ✔ Your Own Hoarding
                </span>
            </div>
            @else
                <button
                    id="cart-btn-{{ $hoarding->id }}"
                    data-in-cart="{{ $isInCart ? '1' : '0' }}"
                    data-auth="{{ auth()->check() ? '1' : '0' }}"
                    onclick="event.preventDefault(); toggleCart(this, {{ $hoarding->id }})"
                    class="cart-btn cart-btn--white flex-1 py-2 px-3 text-sm font-semibold rounded w-full
                        {{ $isInCart ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }}">
                    
                    {{ $isInCart ? 'Remove from Shortlist' : 'Add to Shortlist' }}
                </button>
                @auth
                    <div class="text-center">
                        <!-- Book Now Button triggers calendar modal, then submits form with selected dates -->
                        <form id="book-now-form-{{ $hoarding->id }}" action="{{ route('payment.billing') }}" method="GET">
                            <input type="hidden" name="hoarding_id" value="{{ $hoarding->id }}">
                            <input type="hidden" name="package_id" id="selected_package_id_{{ $hoarding->id }}">
                            <input type="hidden" name="price" id="selected_price_{{ $hoarding->id }}">
                            <input type="hidden" name="start_date" id="selected_start_date_{{ $hoarding->id }}">
                            <input type="hidden" name="end_date" id="selected_end_date_{{ $hoarding->id }}">
                            <button
                                type="button"
                                class="w-full bg-black hover:bg-gray-800 text-white py-2 px-3 rounded-lg font-semibold book-now-btn cursor-pointer mt-2"
                                data-hoarding-id="{{ $hoarding->id }}"
                                data-base-price="{{ (!empty($hoarding->monthly_price) && $hoarding->monthly_price > 0)
                                    ? $hoarding->monthly_price
                                    : ($hoarding->base_monthly_price ?? 0) }}"
                                data-hoarding-type="{{ $hoarding->hoarding_type }}">
                                Book Now
                            </button>
                        </form>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var modal = document.getElementById('bk-modal-{{ $hoarding->id }}');
                            if (modal) {
                                modal.addEventListener('bookingConfirmed', function(e) {
                                    var selections = e.detail.selections;
                                    if (selections && selections.length > 0) {
                                        // Use first selection for start/end
                                        var start = selections[0].start;
                                        var end = selections[0].end;
                                        document.getElementById('selected_start_date_{{ $hoarding->id }}').value = start;
                                        document.getElementById('selected_end_date_{{ $hoarding->id }}').value = end;
                                        // Optionally set package/price if you have package selection logic
                                        // document.getElementById('selected_package_id_{{ $hoarding->id }}').value = ...;
                                        // document.getElementById('selected_price_{{ $hoarding->id }}').value = ...;
                                        document.getElementById('book-now-form-{{ $hoarding->id }}').submit();
                                    }
                                });
                            }
                        });
                        </script>

                        <!-- Custom Calendar Modal for Booking (matches screenshot layout) -->
                        <div id="calendarModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
                            <div class="bg-white rounded-lg p-6 w-full max-w-2xl relative">
                                <button id="closeCalendar" class="absolute top-2 right-2 text-gray-500 hover:text-black">&times;</button>
                                <div id="customCalendar"></div>
                                <div class="mt-6">
                                    <div id="selectedRanges" class="text-sm font-semibold"></div>
                                    <button id="confirmCalendar" class="mt-4 float-right bg-black text-white px-6 py-2 rounded-lg font-semibold">Confirm</button>
                                </div>
                            </div>
                        </div>

                       
                        <button
                            type="button"
                            class="py-2 px-3 text-teal-600 hover:text-teal-700 font-medium text-sm font-semibold rounded enquiry-btn cursor-pointer"
                            data-hoarding-id="{{ $hoarding->id }}"
                            data-grace-days="{{ (int) $hoarding->grace_period_days }}"
                            data-base-price="{{ (!empty($hoarding->monthly_price) && $hoarding->monthly_price > 0)
                                ? $hoarding->monthly_price
                                : ($hoarding->base_monthly_price ?? 0)
                            }}"
                            data-slot-duration="{{ $hoarding->doohScreen->slot_duration_seconds ?? '' }}"
                            data-total-slots="{{ $hoarding->doohScreen->total_slots_per_day ?? '' }}"
                            data-base-monthly-price="{{ $hoarding->base_monthly_price ?? 0 }}"
                            data-hoarding-type="{{ $hoarding->hoarding_type}}"
                        >
                            Enquiry Now
                        </button>
                    </div>
                @else
                    <a href="/login?message={{ urlencode('Please login to raise an enquiry.') }}"
                    class="mt-3 block text-center text-xs text-teal-600 hover:text-teal-700 font-medium">
                        Enquire Now
                    </a>
                @endauth
        @endif
    </div>

</div>
<div class="vendor-card border border-gray-200 p-6 bg-white shadow-sm mt-4 mb-4">

    <div class="flex items-start gap-6">

        {{-- Vendor Image --}}
        <div class="w-20 h-20 flex-shrink-0 rounded-full overflow-hidden border border-gray-200">
            <img
                src="{{ route('view-avatar', $hoarding->vendor->id) }}?v={{ optional($hoarding->vendor->updated_at)->timestamp ?? time() }}"
                alt="Vendor Image"
                class="w-full h-full object-cover"
                onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($hoarding->vendor->name ?? 'N/A') }}&background=22c55e&color=fff&size=128'"
            >
        </div>

        {{-- Vendor Details --}}
        <div class="flex-1">

            <div class="flex items-center justify-between">

                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $hoarding->vendor->name ?? 'N/A' }}
                    </h2>

                    <p class="text-gray-500 text-sm">
                        Member since {{ optional($hoarding->vendor->created_at)->format('Y') }}
                    </p>
                </div>

                {{-- Verified Badge --}}
                <span class="flex items-center gap-2 bg-green-100 text-green-700 px-3 py-1 rounded-lg text-sm font-semibold" style="margin-top:0;">
                    Verified
                    <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 0L6 3H2L3 7L0 9L3 11L2 15H6L7 18L10 16L13 18L14 15H18L17 11L20 9L17 7L18 3H14L13 0L10 2L7 0ZM14 5L15 6L8 13L5 10L6 9L8 11L14 5Z" fill="#009A5C"/>
                    </svg>
                </span>

            </div>

            {{-- Contact Buttons --}}
            <div class="flex gap-4 mt-4">

                <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ $hoarding->vendor->email }}"
                    target="_blank"
                    class="flex items-center gap-2 px-5 py-1 rounded bg-orange-200 text-orange-800 font-medium hover:bg-orange-300 transition w-full max-w-xs justify-center"
                        <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.75 0.75V0.125C0.58424 0.125 0.425268 0.190848 0.308058 0.308058C0.190848 0.425268 0.125 0.58424 0.125 0.75H0.75ZM15.75 0.75H16.375C16.375 0.58424 16.3092 0.425268 16.1919 0.308058C16.0747 0.190848 15.9158 0.125 15.75 0.125V0.75ZM0.75 1.375H15.75V0.125H0.75V1.375ZM15.125 0.75V10.75H16.375V0.75H15.125ZM14.0833 11.7917H2.41667V13.0417H14.0833V11.7917ZM1.375 10.75V0.75H0.125V10.75H1.375ZM2.41667 11.7917C1.84167 11.7917 1.375 11.325 1.375 10.75H0.125C0.125 11.3578 0.366443 11.9407 0.796214 12.3705C1.22598 12.8002 1.80888 13.0417 2.41667 13.0417V11.7917ZM15.125 10.75C15.125 11.325 14.6583 11.7917 14.0833 11.7917V13.0417C14.6911 13.0417 15.274 12.8002 15.7038 12.3705C16.1336 11.9407 16.375 11.3578 16.375 10.75H15.125Z" fill="#AD4800"/>
                        <path d="M0.75 0.75L8.25 8.25L15.75 0.75" stroke="#AD4800" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Email
                    </a>

                     <a href="tel:{{ $hoarding->vendor->vendorProfile->phone ?? $hoarding->vendor->phone }}"
                           class="flex items-center gap-2 px-5 py-1 rounded bg-blue-300 text-blue-900 font-medium w-full max-w-xs justify-center"

                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14.9987 10.3833L10.607 9.875L8.50703 11.975C6.14153 10.7716 4.21875 8.84884 3.01536 6.48333L5.1237 4.375L4.61536 0H0.0236981C-0.459635 8.48333 6.51536 15.4583 14.9987 14.975V10.3833Z" fill="#0089E1"/>
                    </svg>
                    Call
                </a>

            </div>

            {{-- Bottom Link --}}
            <div class="mt-6">
                <a href="{{ route('vendors.show', $hoarding->vendor->id) }}"
                   class="flex items-center text-green-600 font-semibold hover:underline">

                    View all hoardings

                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>

                </a>
            </div>

        </div>

    </div>

</div>
{{-- ============================================================
     CALENDAR MODAL
     ============================================================ --}}
<div id="bk-modal-{{ $hoarding->id }}"
     class="bk-modal hidden fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(0,0,0,.45);">
 
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full relative" style="max-width:760px;">
 
        {{-- Close --}}
        <button class="bk-close absolute top-3 right-4 text-2xl text-gray-400 hover:text-black leading-none">&times;</button>
 
        {{-- Header --}}
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-gray-800">Select Booking Dates</h3>
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <span class="text-sm text-gray-600 font-medium">Weekly</span>
                <div class="bk-toggle relative w-11 h-6">
                    <input type="checkbox" class="bk-weekly-chk sr-only">
                    <span class="bk-track block w-11 h-6 rounded-full bg-gray-300 transition-colors duration-200"></span>
                    <span class="bk-thumb absolute top-1 left-1 w-4 h-4 rounded-full bg-white shadow transition-transform duration-200"></span>
                </div>
            </label>
        </div>
 
        {{-- Two-month grid --}}
        <div class="flex gap-8">
            <div class="flex-1" id="bk-m0-{{ $hoarding->id }}"></div>
            <div class="flex-1" id="bk-m1-{{ $hoarding->id }}"></div>
        </div>
 
        {{-- Summary + confirm --}}
        <div class="mt-5 flex items-end justify-between gap-4">
            <div class="flex-1" id="bk-summary-{{ $hoarding->id }}"></div>
            <button class="bk-confirm text-white text-sm font-semibold px-7 py-2.5 rounded-lg transition"
                    id="bk-confirm-{{ $hoarding->id }}"
                    style="background:#111;opacity:.4;cursor:not-allowed;"
                    disabled>
                Confirm
            </button>
        </div>
    </div>
</div>
 
@once
<style>
.bk-weekly-chk:checked ~ .bk-track { background:#22c55e; }
.bk-weekly-chk:checked ~ .bk-thumb { transform:translateX(20px); }
 
.bk-month-title { font-weight:700; text-align:center; margin-bottom:10px; font-size:1rem; color:#111; }
.bk-dow-row     { display:grid; grid-template-columns:repeat(7,1fr); margin-bottom:2px; }
.bk-dow-cell    { text-align:center; font-size:.75rem; color:#9ca3af; font-weight:500; padding:4px 0; }
.bk-days-grid   { display:grid; grid-template-columns:repeat(7,1fr); }
 
.bk-day {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    height:40px; font-size:.8125rem; cursor:pointer; position:relative;
    user-select:none; transition:background .1s; border-radius:0; color:#111;
}
.bk-day:hover:not(.bk-blocked):not(.bk-past):not(.bk-blank) { background:#dcfce7; border-radius:6px; z-index:1; }
 
.bk-day.bk-past  { color:#d1d5db !important; cursor:default; background:transparent !important; border-radius:0 !important; }
.bk-day.bk-blank { cursor:default; }
 
.bk-day.bk-blocked {
    background:#e5e7eb !important; color:#9ca3af !important;
    cursor:not-allowed; border-radius:6px !important; font-size:.78rem;
}
.bk-blocked-lbl { font-size:9px; line-height:1; margin-top:1px; color:#9ca3af; }
 
/* Monthly band */
.bk-day.bk-sel         { background:#bbf7d0 !important; border-radius:0 !important; color:#111; }
.bk-day.bk-sel-rs      { border-radius:8px 0 0 8px !important; }   /* row-start cap  */
.bk-day.bk-sel-re      { border-radius:0 8px 8px 0 !important; }   /* row-end cap    */
.bk-day.bk-sel-rs.bk-sel-re { border-radius:8px !important; }       /* single / both  */
 
/* anchor (waiting for end click) */
.bk-day.bk-anchor { background:#86efac !important; border-radius:8px !important; color:#111; }
 
/* Weekly band */
.bk-day.bk-wk     { background:#bbf7d0 !important; border-radius:0 !important; color:#111; }
.bk-day.bk-wk-s   { border-radius:8px 0 0 8px !important; }
.bk-day.bk-wk-e   { border-radius:0 8px 8px 0 !important; }
.bk-day.bk-wk-s.bk-wk-e { border-radius:8px !important; }
 
/* Summary */
.bk-sum-label { font-size:.8125rem; font-weight:700; color:#111; margin-bottom:6px; }
.bk-chips     { display:flex; flex-wrap:wrap; gap:6px; }
.bk-chip {
    display:inline-flex; align-items:center; gap:5px;
    border:1px solid #86efac; border-radius:999px;
    padding:3px 10px; font-size:.8rem; color:#374151; background:#fff;
}
.bk-chip-ck  { color:#22c55e; }
.bk-chip-rm  { cursor:pointer; color:#aaa; transition:color .1s; font-size:1rem; line-height:1; }
.bk-chip-rm:hover { color:#374151; }
</style>
@endonce
 
<script>
(function(){
    const HID       = {{ $hoarding->id }};
    const modal     = document.getElementById('bk-modal-'+HID);
    const m0el      = document.getElementById('bk-m0-'+HID);
    const m1el      = document.getElementById('bk-m1-'+HID);
    const summaryEl = document.getElementById('bk-summary-'+HID);
    const confirmEl = document.getElementById('bk-confirm-'+HID);
 
    let bookedDates = [];
    let selections  = [];
    let isWeekly    = false;
    let anchor      = null;
    let viewYear, viewMonth;
 
    /* utils */
    const pad  = n => String(n).padStart(2,'0');
    const fmt  = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
    const pd   = s => new Date(s+'T00:00:00');
    const add  = (d,n) => { const x=new Date(d); x.setDate(x.getDate()+n); return x; };
    const today = fmt(new Date());
 
    function weekOf(ds){ const d=pd(ds),dow=d.getDay(); return {start:fmt(add(d,-dow)),end:fmt(add(d,6-dow))}; }
    function inAny(ds){ return selections.some(r=>r.start<=ds&&ds<=r.end); }
    function rangeOf(ds){ return selections.find(r=>r.start<=ds&&ds<=r.end); }
 
    /* render one month */
    function renderMonth(el, yr, mo){
        const first=new Date(yr,mo,1), last=new Date(yr,mo+1,0);
        const name=first.toLocaleString('default',{month:'long'});
        let h=`<div class="bk-month-title">${name} ${yr}</div>`;
        h+='<div class="bk-dow-row">';
        ['Su','Mo','Tu','We','Th','Fr','Sa'].forEach(d=>h+=`<div class="bk-dow-cell">${d}</div>`);
        h+='</div><div class="bk-days-grid">';
 
        const startDow=first.getDay();
        for(let i=0;i<startDow;i++) h+='<div class="bk-day bk-blank"></div>';
 
        for(let day=1;day<=last.getDate();day++){
            const ds  = fmt(new Date(yr,mo,day));
            const dow = (startDow+day-1)%7;
            const isBlk = bookedDates.includes(ds);
            const isPst = ds<today;
            const inR   = inAny(ds);
            const isAnc = anchor===ds;
 
            let cls='bk-day';
 
            if(isPst){
                cls+=' bk-past';
            } else if(isBlk){
                cls+=' bk-blocked';
            } else if(isWeekly && inR){
                const r=rangeOf(ds);
                const isS=ds===r.start, isE=ds===r.end;
                cls+=' bk-wk';
                if(isS) cls+=' bk-wk-s';
                if(isE) cls+=' bk-wk-e';
            } else if(!isWeekly && inR){
                const r=rangeOf(ds);
                const atRS = ds===r.start || dow===0 || day===1;
                const atRE = ds===r.end   || dow===6 || day===last.getDate();
                cls+=' bk-sel';
                if(atRS) cls+=' bk-sel-rs';
                if(atRE) cls+=' bk-sel-re';
            } else if(isAnc){
                cls+=' bk-anchor';
            }
 
            const lbl = isBlk ? '<span class="bk-blocked-lbl">Blocked</span>' : '';
            h+=`<div class="${cls}" data-date="${ds}">${day}${lbl}</div>`;
        }
        h+='</div>';
        el.innerHTML=h;
 
        el.querySelectorAll('.bk-day:not(.bk-blocked):not(.bk-past):not(.bk-blank)').forEach(c=>{
            c.addEventListener('click',()=>onDay(c.dataset.date));
        });
    }
 
    function onDay(ds){
        if(isWeekly){
            const wb=weekOf(ds);
            const idx=selections.findIndex(r=>r.start===wb.start&&r.end===wb.end);
            if(idx>=0) selections.splice(idx,1); else selections.push(wb);
        } else {
            if(anchor===null){ anchor=ds; }
            else {
                const s=anchor<ds?anchor:ds, e=anchor<ds?ds:anchor;
                selections.push({start:s,end:e});
                anchor=null;
            }
        }
        refresh();
    }
 
    function removeChip(i){ selections.splice(i,1); refresh(); }
 
    function fmtD(ds){
        return pd(ds).toLocaleDateString('en-US',{month:'short',day:'2-digit',year:'numeric'});
    }
 
    function refresh(){
        const y2=viewMonth===11?viewYear+1:viewYear, m2=viewMonth===11?0:viewMonth+1;
        renderMonth(m0el,viewYear,viewMonth);
        renderMonth(m1el,y2,m2);
 
        const label=isWeekly?'Selected Week':'Selected Month';
 
        if(anchor && !isWeekly){
            summaryEl.innerHTML=`<div class="bk-sum-label">${label}</div><div style="font-size:.8rem;color:#9ca3af;">Now click an end date…</div>`;
            setConfirm(false); return;
        }
        if(!selections.length){
            summaryEl.innerHTML=''; setConfirm(false); return;
        }
 
        let inner=`<div class="bk-sum-label">${label}</div><div class="bk-chips">`;
        selections.forEach((r,i)=>{
            const txt=r.start===r.end?fmtD(r.start):`${fmtD(r.start)} - ${fmtD(r.end)}`;
            inner+=`<span class="bk-chip">${isWeekly?'<span class="bk-chip-ck">✓</span>':''} ${txt} <span class="bk-chip-rm" data-i="${i}">×</span></span>`;
        });
        inner+='</div>';
        summaryEl.innerHTML=inner;
        summaryEl.querySelectorAll('.bk-chip-rm').forEach(b=>b.addEventListener('click',()=>removeChip(+b.dataset.i)));
        setConfirm(true);
    }
 
    function setConfirm(on){
        confirmEl.disabled=!on;
        confirmEl.style.opacity=on?'1':'.4';
        confirmEl.style.cursor=on?'pointer':'not-allowed';
        confirmEl.style.background=on?'#111':'#111';
    }
 
    function openModal(){
        const now=new Date(); viewYear=now.getFullYear(); viewMonth=now.getMonth();
        selections=[]; anchor=null; isWeekly=false;
        modal.querySelector('.bk-weekly-chk').checked=false;
        modal.classList.remove('hidden');
        fetch(`/api/hoardings/${HID}/booked-dates`)
            .then(r=>r.json()).then(d=>{ bookedDates=Array.isArray(d.booked_dates)?d.booked_dates:[]; })
            .catch(()=>{ bookedDates=[]; }).finally(()=>refresh());
    }
 
    function closeModal(){
        modal.classList.add('hidden');
        selections=[]; anchor=null;
        summaryEl.innerHTML=''; setConfirm(false);
    }
 
    modal.querySelector('.bk-weekly-chk').addEventListener('change',function(){
        isWeekly=this.checked; selections=[]; anchor=null; refresh();
    });
 
    confirmEl.addEventListener('click',()=>{
        modal.dispatchEvent(new CustomEvent('bookingConfirmed',{
            detail:{hoardingId:HID,selections,mode:isWeekly?'weekly':'monthly'},bubbles:true
        }));
        console.log('Booking confirmed',{hoardingId:HID,selections,mode:isWeekly?'weekly':'monthly'});
        closeModal();
    });
 
    modal.querySelector('.bk-close').addEventListener('click',closeModal);
    modal.addEventListener('click',e=>{ if(e.target===modal) closeModal(); });
 
    function attach(){
        document.querySelectorAll(`.book-now-btn[data-hoarding-id="${HID}"]`).forEach(b=>{
            if(!b.__bk){ b.__bk=true; b.addEventListener('click',openModal); }
        });
    }
    document.readyState==='loading'?document.addEventListener('DOMContentLoaded',attach):attach();
})();
</script>
<style>
.cart-btn--white {
    color: #fff !important;
    background-color: #22c55e;
}
.cart-btn--white:hover {
    background-color: #16a34a !important;
    color: #fff !important;
}
.vendor-card{
    border-radius: 5px;
}
</style>
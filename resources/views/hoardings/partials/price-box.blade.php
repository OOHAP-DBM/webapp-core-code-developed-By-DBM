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
    <div class="bg-white border border-gray-200 p-4 mt-4 buttons-action-bar"
     style="border-radius:5px;">
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
                    
                    {{ $isInCart ? 'Remove' : 'Shortlist' }}
                </button>
                @auth
                    <div class="text-center">

                         <!-- Book Now Button -->
                        {{-- <button
                            type="button"
                            class="w-full bg-black hover:bg-gray-800 text-white py-2 px-3 rounded-lg font-semibold book-now-btn cursor-pointer mt-2"
                            data-hoarding-id="{{ $hoarding->id }}"
                            data-base-price="{{ (!empty($hoarding->monthly_price) && $hoarding->monthly_price > 0)
                                ? $hoarding->monthly_price
                                : ($hoarding->base_monthly_price ?? 0)
                            }}"
                            data-hoarding-type="{{ $hoarding->hoarding_type}}">
                            Book Now
                        </button> --}}

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

                        <script>
                        let bookedDates = [];
                        let selectedRanges = [];

                        function formatDate(date) {
                            return date.toISOString().slice(0,10);
                        }

                        function renderCalendar(year, month, containerId, blocked, selected) {
                            const container = document.getElementById(containerId);
                            container.innerHTML = '';
                            const firstDay = new Date(year, month, 1);
                            const lastDay = new Date(year, month+1, 0);
                            const monthName = firstDay.toLocaleString('default', { month: 'long' });
                            let html = `<div class="mb-2 text-center font-bold text-lg">${monthName} ${year}</div>`;
                            html += '<div class="grid grid-cols-7 gap-1 text-center text-gray-500 mb-1">';
                            ['Su','Mo','Tu','We','Th','Fr','Sa'].forEach(d => html += `<div>${d}</div>`);
                            html += '</div><div class="grid grid-cols-7 gap-1">';
                            let day = 1, started = false;
                            for(let i=0; i<42; i++) {
                                let date = new Date(year, month, day);
                                if(i < firstDay.getDay() || day > lastDay.getDate()) {
                                    html += '<div></div>';
                                } else {
                                    const dateStr = formatDate(date);
                                    let classes = 'py-2 rounded cursor-pointer relative ';
                                    let label = '';
                                    if (blocked.includes(dateStr)) {
                                        classes += 'bg-gray-300 text-gray-500 pointer-events-none';
                                        label = '<div class="text-xs absolute bottom-1 left-0 right-0 text-center">Blocked</div>';
                                    } else if (selected.includes(dateStr)) {
                                        classes += 'bg-green-200';
                                    } else {
                                        classes += 'hover:bg-green-100';
                                    }
                                    html += `<div class="${classes}" data-date="${dateStr}">${day}${label}</div>`;
                                    day++;
                                }
                            }
                            html += '</div>';
                            container.innerHTML = html;
                        }

                        function updateSelectedRangesDisplay() {
                            const el = document.getElementById('selectedRanges');
                            if (selectedRanges.length === 0) {
                                el.innerHTML = '';
                                return;
                            }
                            let html = '';
                            selectedRanges.forEach(r => {
                                html += `<div>${r.start} - ${r.end}</div>`;
                            });
                            el.innerHTML = `<div>Selected Month</div>${html}`;
                        }

                        function openCalendar(hoardingId) {
                            document.getElementById('calendarModal').classList.remove('hidden');
                            fetch(`/api/hoardings/${hoardingId}/booked-dates`)
                                .then(res => res.json())
                                .then(data => {
                                    bookedDates = Array.isArray(data.booked_dates) ? data.booked_dates : [];
                                    selectedRanges = [];
                                    renderBothMonths();
                                    updateSelectedRangesDisplay();
                                });
                        }

                        function renderBothMonths() {
                            const now = new Date();
                            const y = now.getFullYear(), m = now.getMonth();
                            document.getElementById('customCalendar').innerHTML = `
                                <div class="flex gap-8">
                                    <div id="month1" class="flex-1"></div>
                                    <div id="month2" class="flex-1"></div>
                                </div>
                            `;
                            let selected = [];
                            selectedRanges.forEach(r => {
                                let d = new Date(r.start);
                                const end = new Date(r.end);
                                while (d <= end) {
                                    selected.push(formatDate(d));
                                    d.setDate(d.getDate()+1);
                                }
                            });
                            renderCalendar(y, m, 'month1', bookedDates, selected);
                            renderCalendar(y, m+1, 'month2', bookedDates, selected);

                            document.querySelectorAll('#month1 [data-date], #month2 [data-date]').forEach(el => {
                                el.onclick = function() {
                                    const date = this.getAttribute('data-date');
                                    if (selectedRanges.length === 0 || selectedRanges[selectedRanges.length-1].end) {
                                        selectedRanges.push({start: date, end: null});
                                    } else {
                                        if (date >= selectedRanges[selectedRanges.length-1].start) {
                                            selectedRanges[selectedRanges.length-1].end = date;
                                        } else {
                                            selectedRanges[selectedRanges.length-1].end = selectedRanges[selectedRanges.length-1].start;
                                            selectedRanges[selectedRanges.length-1].start = date;
                                        }
                                    }
                                    renderBothMonths();
                                    updateSelectedRangesDisplay();
                                }
                            });
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            document.querySelectorAll('.book-now-btn').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    openCalendar(this.dataset.hoardingId);
                                });
                            });
                            document.getElementById('closeCalendar').onclick = function() {
                                document.getElementById('calendarModal').classList.add('hidden');
                                document.getElementById('customCalendar').innerHTML = '';
                                document.getElementById('selectedRanges').innerHTML = '';
                            };
                            document.getElementById('confirmCalendar').onclick = function() {
                                // Do something with selectedRanges
                                alert('Selected: ' + JSON.stringify(selectedRanges));
                                document.getElementById('calendarModal').classList.add('hidden');
                            };
                        });
                        </script>
                        <style>
                        #calendarModal .bg-gray-300 { background: #e5e7eb !important; }
                        #calendarModal .bg-green-200 { background: #bbf7d0 !important; }
                        #calendarModal .hover\:bg-green-100:hover { background: #dcfce7 !important; }
                        </style>

                        <!-- Calendar Modal (only one per page, shown for selected hoarding) -->
                        <div id="calendarModal-{{ $hoarding->id }}" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
                            <div class="bg-white rounded-lg p-6 w-full max-w-xl relative">
                                <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-black close-calendar-btn">&times;</button>
                                <div id="calendarContainer-{{ $hoarding->id }}"></div>
                            </div>
                        </div>

                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
                        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var btn = document.querySelector('.book-now-btn[data-hoarding-id="{{ $hoarding->id }}"]');
                            var modal = document.getElementById('calendarModal-{{ $hoarding->id }}');
                            var calendarContainer = document.getElementById('calendarContainer-{{ $hoarding->id }}');
                            var closeBtn = modal.querySelector('.close-calendar-btn');

                            btn.addEventListener('click', function() {
                                // Hide any other open modals
                                document.querySelectorAll('[id^="calendarModal-"]').forEach(function(m) { m.classList.add('hidden'); });
                                modal.classList.remove('hidden');
                                // Only load calendar if not already loaded
                                if (!calendarContainer.hasChildNodes()) {
                                    fetch(`/api/hoardings/${btn.dataset.hoardingId}/booked-dates`)
                                        .then(res => res.json())
                                        .then(data => {
                                            const booked = Array.isArray(data.booked_dates) ? data.booked_dates : [];
                                            flatpickr(calendarContainer, {
                                                inline: true,
                                                mode: "multiple",
                                                enable: [function(date) { return true; }],
                                                onDayCreate: function(dObj, dStr, fp, dayElem) {
                                                    const dateStr = dayElem.dateObj.toISOString().slice(0,10);
                                                    if (booked.includes(dateStr)) {
                                                        dayElem.classList.add('bg-red-400', 'text-white');
                                                        dayElem.innerHTML += '<div style="font-size:10px;">Blocked</div>';
                                                    }
                                                }
                                            });
                                        })
                                        .catch(() => {
                                            flatpickr(calendarContainer, { inline: true });
                                        });
                                }
                            });
                            closeBtn.addEventListener('click', function() {
                                modal.classList.add('hidden');
                                calendarContainer.innerHTML = '';
                            });
                        });
                        </script>
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
<div class="vendor-card border border-gray-200 p-6 bg-white mt-4 mb-4">

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
/* Mobile pe sticky */
@media (max-width: 639px) {
    .buttons-action-bar {
        position: fixed !important;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 50;
        margin-top: 0;
        border-radius: 0 !important;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    }
}

/* Desktop pe normal */
@media (min-width: 640px) {
    .buttons-action-bar {
        position: static !important;
        border: 1px solid #e5e7eb;
        border-radius: 5px !important;
        box-shadow: none;
        margin-top: 1rem;
    }
}
</style>
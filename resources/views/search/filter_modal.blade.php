<style>
    .filter-btn {
    padding: 8px 18px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    cursor: pointer;
    transition: all .2s ease;
    background: #fff;
    color: #111827;
    user-select: none;
    }

    .filter-btn:hover {
        border-color: #111827;
    }

    .filter-btn.active {
        background: #111827;
        color: #fff;
        border-color: #111827;
    }

    .vendor-pill {
        padding: 6px 14px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #111827;
        cursor: pointer;
        transition: all .2s ease;
        font-size: 13px;
        user-select: none;
        transition:0.5s ease-out;
    }

    .vendor-pill:hover {
        background: #111827;
        color: #fff;
    }

    .vendor-pill.active {
        background: #111827;
        color: #fff;
        border-color: #111827;
    }
    .rating-btn{
    display:flex;
    align-items:center;
    gap:6px;
    padding:10px 18px;
    border-radius:10px;
    border:1px solid #d1d5db;
    background:#fff;
    color:#6b7280;
    font-weight:500;
    cursor:pointer;
    transition:all .2s ease;
    }

    .rating-btn svg{
        width:14px;
        height:14px;
        fill:#fbbf24; /* yellow star */
    }

    .rating-btn:hover{
        border-color:#9ca3af;
    }

    .rating-btn.active{
        background:#111827;
        color:#fff;
        border-color:#111827;
    }

    .rating-btn.active svg{
        fill:#fbbf24;
    }
  
   

   .range-wrapper {
        position: relative;
        height: 32px;
        margin-top: 16px;
    }

    .range-base {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 100%;
        height: 4px;
        background: #e5e7eb;
        border-radius: 999px;
        z-index: 1;
    }

    #rangeTrack {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        height: 4px;
        background: #22c55e;
        border-radius: 999px;
        z-index: 2;
        transition: left .15s ease, width .15s ease;
    }

    input[type=range] {
        -webkit-appearance: none;
        appearance: none;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 100%;
        height: 32px;
        background: transparent;
        pointer-events: none;
        z-index: 5;
    }

    input[type=range]::-webkit-slider-thumb {
        -webkit-appearance: none;
        pointer-events: auto;
        width: 18px;
        height: 18px;
        background: #fff;
        border: 2px solid #7a7a7a;
        border-radius: 50%;
        cursor: pointer;
        position: relative;
        z-index: 10;
    }
    .rating-value,
    .rating-text {
        color: #6b7280; /* gray */
        transition: color .2s ease;
    }

    .rating-btn.active .rating-value,
    .rating-btn.active .rating-text {
        color: #fff; /* white */
    }
</style>
<form method="GET" action="{{ route('search.seo', ['city' => request('location', 'india'), 'area' => request('area')]) }}" id="filterForm">
    <div id="filterModal" class="fixed inset-0 z-[9999] hidden p-4 sm:p-6 md:p-8 lg:p-0">

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/40" onclick="closeFilterModal()"></div>

        <!-- Modal -->
        <div class="relative bg-white w-full max-w-4xl mx-auto mt-8 rounded-xl shadow-xl overflow-hidden">

            {{-- HEADER --}}
            <div class="relative flex items-center px-6 py-4 shadow">
                <h2 class="text-lg font-semibold absolute left-1/2 -translate-x-1/2">
                    Filters
                </h2>

                <button
                    type="button"
                    onclick="closeFilterModal()"
                    class="ml-auto text-xl"
                >
                    &times;
                </button>
            </div>


            {{-- BODY --}}
            <div class="p-6 max-h-[75vh] overflow-y-auto space-y-8">

                {{-- TYPES OF HOARDING --}}
                <section>
                    <h3 class="font-medium mb-3">Types of Hoarding</h3>
                    <input type="hidden" name="type" id="filterType" value="{{ request('type','') }}">

                    <div class="flex gap-3">
                        <button type="button" class="type-btn filter-btn"
                            onclick="setType('',this)" data-type="all">
                            Any Type
                        </button>

                        <button type="button" class="type-btn filter-btn"
                            onclick="setType('ooh',this)" data-type="ooh">
                            OOH
                        </button>

                        <button type="button" class="type-btn filter-btn"
                            onclick="setType('dooh',this)" data-type="dooh">
                            DOOH
                        </button>
                    </div>


                </section>

                <section class="type-section" data-type="ooh">
                    <h3 class="font-medium mb-2">Categories (OOH)</h3>
                    @foreach(['Unipole','Billboard','Gantry','Bus Shelter','Metro Pillars'] as $cat)
                        <label class="flex gap-2">
                            <input type="checkbox" name="category[]" value="{{ $cat }}">
                            {{ $cat }}
                        </label>
                    @endforeach
                </section>
                <section class="type-section" data-type="dooh">
                    <h3 class="font-medium mb-2">Digital Categories (DOOH)</h3>
                    @foreach(['LED Screens','Digital Standee','Metro Panels'] as $cat)
                        <label class="flex gap-2">
                            <input type="checkbox" name="category[]" value="{{ $cat }}">
                            {{ $cat }}
                        </label>
                    @endforeach
                </section>



                {{-- CAMPAIGN DURATION --}}
                <section>
                    <h3 class="font-medium mb-1">Campaign Duration</h3>
                    <p class="text-xs text-gray-500 mb-3">
                        Select campaign duration. like how long you want to book the hoarding.
                    </p>
                <div class="flex gap-3 text-sm">
                    <input type="hidden" name="duration" id="durationInput" value="{{ request('duration','monthly') }}">

                    <div class="filter-btn duration-btn active" onclick="setDuration('monthly',this)">
                        Monthly
                    </div>
                    <div class="filter-btn duration-btn" onclick="setDuration('weekly',this)">
                        Weekly
                    </div>
                </div>

                </section>

                {{-- PRICE RANGE --}}
                <section>
                    <h3 class="font-medium mb-1">Price range</h3>
                    <p class="text-xs text-gray-500 mb-2 ">
                        Select Price Range Monthly, Weekly & Yearly, with Tax & before Tax.
                    </p>

                    <!-- <div class="flex items-end gap-[2px] h-16 mb-4">
                        @for($i=1;$i<=40;$i++)
                            <div class="w-full bg-black" style="height: {{ rand(20,60) }}%"></div>
                        @endfor
                    </div> -->

                    <div class="flex gap-4 ">
                        <input type="number" name="min_price" value="{{ request('min_price') }}"
                            placeholder="Minimum ₹ 2,000"
                            class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm">

                            <span class="text-gray-400 font-semibold">-</span>

                        <input type="number" name="max_price" value="{{ request('max_price') }}"
                            placeholder="Maximum ₹ 10,000"
                            class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm">
                    </div>
                </section>

                {{-- VENDORS --}}
                <section>
                    <h3 class="font-medium mb-3">Vendors Hoardings</h3>

                    <div class="flex gap-3 flex-wrap">
                        @foreach(['Digital Brain Media','Shiva Hoardings'] as $v)
                        <label class="vendor-pill" onclick="toggleVendor(this)">
                            <input type="checkbox" name="vendor[]" value="{{ $v }}" class="hidden">
                            {{ $v }}
                        </label>
                        @endforeach
                    </div>

                </section>


                {{-- REVIEW SCORE --}}
               <section>
                <h3 class="font-medium mb-3">Review Score</h3>

                <div class="flex flex-wrap gap-4 text-sm">
                    @foreach([5,4,3,2,1] as $r)
                        @php
                            $checked = in_array($r, request('rating', []));
                        @endphp

                        <div
                            class="rating-btn {{ $checked ? 'active' : '' }}"
                            onclick="toggleRating(this)"
                             >
                            <span class="rating-value">
                                {{ $r }}
                            </span>

                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <polygon points="12 2 15 8.5 22 9.3 17 14.1 18.2 21 12 17.8 5.8 21 7 14.1 2 9.3 9 8.5 12 2" />
                            </svg>

                            <span class="rating-text">Rating</span>

                            <input
                                type="checkbox"
                                name="rating[]"
                                value="{{ $r }}"
                                class="hidden"
                                {{ $checked ? 'checked' : '' }}
                            >
                        </div>

                    @endforeach
                </div>
               </section>



                {{-- VISIBILITY --}}
                <section>
                    <h3 class="font-medium mb-3">Hoarding Visibility</h3>
                    <div class="flex flex-wrap gap-3 text-sm">
                        @foreach(['Metro Ride','From Flyover','From the Road','Roof Top'] as $v)
                            <label class="flex items-center gap-2 border border-gray-200 p-2 rounded-md">
                                <input type="checkbox" name="visibility[]" value="{{ $v }}"
                                    {{ in_array($v, request('visibility', [])) ? 'checked' : '' }}>
                                <span>{{ $v }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- GAZEFLOW --}}
                <section>
                    <h3 class="font-medium mb-1">Gazeflow</h3>
                    <p class="text-xs text-gray-500 mb-3">
                        Average number of peoples roaming near the hoarding.
                    </p>

                    <!-- <div class="flex items-end gap-[2px] h-16 mb-4">
                        @for($i=1;$i<=40;$i++)
                            <div class="w-full bg-black" style="height: {{ rand(20,60) }}%"></div>
                        @endfor
                    </div> -->

                    <div class="flex gap-4">
                        <input type="number" name="min_gazeflow" value="{{ request('min_gazeflow') }}"
                            placeholder="Minimum 100 Daily"
                            class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm">
                            
                            <span class="text-gray-400 font-semibold">-</span>

                        <input type="number" name="max_gazeflow" value="{{ request('max_gazeflow') }}"
                            placeholder="Maximum 100,000 Daily"
                            class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm">
                    </div>
                </section>

           {{-- HOARDING SIZE --}}
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold">Hoarding Size</h3>
                    <p class="text-sm text-gray-500">Select Hoarding Size</p>
                    <p>Minimum Size</p>

                    {{-- SLIDER --}}
                    <div class="range-wrapper">

                    <input
                        type="range"
                        min="8"
                        max="40"
                        step="1"
                        id="minRange"
                        value="{{ request('min_height',12) }}"
                    >

                    <input
                        type="range"
                        min="8"
                        max="40"
                        step="1"
                        id="maxRange"
                        value="{{ request('max_height',16) }}"
                    >

                    <div class="range-base">
                        <div id="rangeTrack"></div>
                    </div>

                </div>


                    {{-- INPUTS --}}
                    <div class="grid grid-cols-[1fr_auto_1fr] gap-4 text-sm mt-6 items-end">
    
                    <!-- MIN -->
                    <div>
                        <label class="block text-gray-500 mb-1">Select Minimum height</label>
                        <input
                            type="number"
                            name="min_height"
                            id="minInput"
                            value="{{ request('min_height') }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2"
                            placeholder="12 ft"
                        >
                    </div>

                    <!-- DASH -->
                    <div class="flex items-center justify-center mb-3 text-gray-400 font-semibold">
                        -
                    </div>

                    <!-- MAX -->
                    <div>
                        <label class="block text-gray-500 mb-1">Select Maximum height</label>
                        <input
                            type="number"
                            name="max_height"
                            id="maxInput"
                            value="{{ request('max_height') }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2"
                            placeholder="16 ft"
                        >
                    </div>

                </div>

                </section>


                {{-- AUDIENCE --}}
                <section>
                    <h3 class="font-medium mb-3">Audience Type</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        @foreach([
                            'Students','Political activities','Foodies','Public',
                            'Amusement Park Visitors','Business Travelers','Environmentalists',
                            'Small Business Owners','College Students','Art Lovers','Tourists'
                        ] as $aud)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="audience[]" value="{{ $aud }}"
                                    {{ in_array($aud, request('audience', [])) ? 'checked' : '' }}>
                                <span>{{ $aud }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>

            </div>

            {{-- FOOTER --}}
            <div class="flex items-center justify-between px-6 py-4 border border-gray-200">
                <a href="{{ route('search.seo', ['city' => 'india']) }}" class="text-lg text-black">Clear all</a>
                <button type="submit"
                    class="btn-color text-white px-14 py-2 rounded" style="cursor:pointer;">
                    Apply
                </button>
            </div>

        </div>
    </div>
</form>
<script>
/* =========================
   TYPE FILTER (Any / OOH / DOOH)
========================= */
function setType(type, el) {
    document.getElementById('filterType').value = type;

    // 🔒 sirf TYPE buttons reset honge
    document.querySelectorAll('.type-btn')
        .forEach(btn => btn.classList.remove('active'));

    el.classList.add('active');

    // categories show / hide
    document.querySelectorAll('.type-section').forEach(sec => {
        sec.style.display =
            (type === '' || sec.dataset.type === type) ? 'block' : 'none';
    });
}

/* =========================
   DURATION FILTER (Monthly / Weekly)
========================= */
function setDuration(val, el) {
    document.getElementById('durationInput').value = val;

    // 🔒 sirf DURATION buttons reset honge
    document.querySelectorAll('.duration-btn')
        .forEach(btn => btn.classList.remove('active'));

    el.classList.add('active');
}

/* =========================
   VENDOR TOGGLE
========================= */
function toggleVendor(el) {
    const cb = el.querySelector('input');
    cb.checked = !cb.checked;
    el.classList.toggle('active', cb.checked);
}

/* =========================
   RATING TOGGLE
========================= */
function toggleRating(el) {
    const input = el.querySelector('input');
    input.checked = !input.checked;
    el.classList.toggle('active', input.checked);
}

/* =========================
   RANGE SLIDER
========================= */
const minRange = document.getElementById('minRange');
const maxRange = document.getElementById('maxRange');
const minInput = document.getElementById('minInput');
const maxInput = document.getElementById('maxInput');
const track = document.getElementById('rangeTrack');

function updateTrack() {
    const min = +minRange.value;
    const max = +maxRange.value;

    const minVal = +minRange.min;
    const maxVal = +minRange.max;
    const sliderWidth = minRange.offsetWidth;
    const thumbWidth = 18;

    const minX = ((min - minVal) / (maxVal - minVal)) * (sliderWidth - thumbWidth) + thumbWidth / 2;
    const maxX = ((max - minVal) / (maxVal - minVal)) * (sliderWidth - thumbWidth) + thumbWidth / 2;

    track.style.left = `${minX}px`;
    track.style.width = `${maxX - minX}px`;
}

minRange.addEventListener('input', () => {
    if (+minRange.value >= +maxRange.value) {
        minRange.value = maxRange.value - 1;
    }
    minInput.value = minRange.value;
    updateTrack();
});

maxRange.addEventListener('input', () => {
    if (+maxRange.value <= +minRange.value) {
        maxRange.value = +minRange.value + 1;
    }
    maxInput.value = maxRange.value;
    updateTrack();
});

minInput.addEventListener('input', () => {
    minRange.value = minInput.value;
    updateTrack();
});

maxInput.addEventListener('input', () => {
    maxRange.value = maxInput.value;
    updateTrack();
});

/* =========================
   ON LOAD DEFAULT STATE
========================= */
document.addEventListener('DOMContentLoaded', () => {

    /* ---- TYPE DEFAULT ---- */
    const typeVal = document.getElementById('filterType').value || '';
    const typeBtn = document.querySelector(
        `.type-btn[data-type="${typeVal || 'all'}"]`
    );

    if (typeBtn) {
        setType(typeVal, typeBtn);
    }

    /* ---- DURATION DEFAULT ---- */
    const durationBtn = document.querySelector('.duration-btn.active')
        || document.querySelector('.duration-btn');

    if (durationBtn) {
        setDuration(
            durationBtn.textContent.trim().toLowerCase(),
            durationBtn
        );
    }

    updateTrack();
});
</script>


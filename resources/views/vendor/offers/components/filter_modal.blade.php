<!-- Filter Modal -->
@push('modals')
<div id="filterModal" class="fixed inset-0 z-50 hidden">

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/40" onclick="closeFilterModal()"></div>

    <!-- Panel -->
    <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4">
        <div class="relative bg-white w-full max-w-2xl shadow-xl overflow-y-auto max-h-[92vh] rounded-lg">

            <!-- Header -->
            <div class="flex items-center justify-between px-4 sm:px-6 py-4 bg-[#D9F2E6]">
                <h2 class="text-lg font-semibold text-gray-800">Filters</h2>
                <button onclick="closeFilterModal()" class="text-gray-600 text-xl cursor-pointer">✕</button>
            </div>

            <div class="p-4 sm:p-6 space-y-6 text-sm text-gray-700">

                <!-- Types of Hoarding -->
                <div>
                    <p class="font-semibold mb-2">Types of Hoarding</p>
                    <div class="flex flex-wrap gap-4 sm:gap-5">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="filter_type" value="all" checked onchange="handleTypeChange(this.value)"> All
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="filter_type" value="ooh" onchange="handleTypeChange(this.value)"> OOH
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="filter_type" value="dooh" onchange="handleTypeChange(this.value)"> DOOH
                        </label>
                    </div>
                </div>

                <!-- Resolution (DOOH only) -->
             

                <!-- Screen Size -->
                <div id="dooh-section">
                    <p class="font-semibold mb-2">Screen Size</p>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <input id="screen-size-min" name="filter_screen_min" type="number" min="0" max="1000" value="0"
                            class="w-full sm:w-1/2 border border-gray-200 px-3 py-2 text-xs bg-gray-50 min-h-[44px]" placeholder="Min (Sq.ft)">
                        <input id="screen-size-max" name="filter_screen_max" type="number" min="0" max="1000" value="1000"
                            class="w-full sm:w-1/2 border border-gray-200 px-3 py-2 text-xs bg-gray-50 min-h-[44px]" placeholder="Max (Sq.ft)">
                    </div>
                    <div class="relative w-full h-5 mt-3">
                        <input id="screen-size-range-min" type="range" min="0" max="1000" value="0" step="1"
                            class="dual-range absolute w-full pointer-events-none" style="z-index:3;">
                        <input id="screen-size-range-max" type="range" min="0" max="1000" value="1000" step="1"
                            class="dual-range absolute w-full pointer-events-none" style="z-index:4;">
                        <div class="absolute top-1/2 -translate-y-1/2 h-[3px] w-full bg-gray-200 rounded" style="z-index:1;"></div>
                        <div id="screen-size-fill" class="absolute top-1/2 -translate-y-1/2 h-[3px] bg-[#2D5A43] rounded" style="z-index:2;left:0%;width:100%;"></div>
                    </div>
                </div>

                <!-- Category (OOH only) -->
                <div id="ooh-section">
                    <p class="font-semibold mb-2">Category</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_category" value="billboard"> Bill Board</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_category" value="unipole"> Unipole</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_category" value="gantry"> Gantry</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_category" value="airport"> Air Port</label>
                    </div>
                </div>
                <!-- Located At -->
                <div>
                    <p class="font-semibold mb-2">Located At</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-center gap-2"><input type="checkbox" name="Located_at" value="highway_hoarding">Highway hoarding</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="Located_at" value="shopping_mall">Shopping Mall</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="Located_at" value="intracity_highway">Intracity Highway</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="Located_at" value="main_road">Main Road</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="Located_at" value="pause_area">Pause Area</label>
                    </div>
                </div>

                <!-- Hoarding Size -->
                <div>
                    <p class="font-semibold mb-2">Hoarding Size</p>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <input id="hoarding-size-min" name="filter_hoarding_min" type="number" min="0" max="1000" value="0"
                            class="w-full sm:w-1/2 border border-gray-200 px-3 py-2 text-xs bg-gray-50 min-h-[44px]" placeholder="Min (Sq.ft)">
                        <input id="hoarding-size-max" name="filter_hoarding_max" type="number" min="0" max="1000" value="1000"
                            class="w-full sm:w-1/2 border border-gray-200 px-3 py-2 text-xs bg-gray-50 min-h-[44px]" placeholder="Max (Sq.ft)">
                    </div>
                    <div class="relative w-full h-5 mt-3">
                        <input id="hoarding-size-range-min" type="range" min="0" max="1000" value="0" step="1"
                            class="dual-range absolute w-full pointer-events-none" style="z-index:3;">
                        <input id="hoarding-size-range-max" type="range" min="0" max="1000" value="1000" step="1"
                            class="dual-range absolute w-full pointer-events-none" style="z-index:4;">
                        <div class="absolute top-1/2 -translate-y-1/2 h-[3px] w-full bg-gray-200 rounded" style="z-index:1;"></div>
                        <div id="hoarding-size-fill" class="absolute top-1/2 -translate-y-1/2 h-[3px] bg-[#2D5A43] rounded" style="z-index:2;left:0%;width:100%;"></div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="flex flex-col-reverse sm:flex-row px-4 sm:px-6 py-4 gap-2 sm:gap-3 justify-end">
                <button type="button" onclick="resetFilters()" class="w-full sm:w-auto min-h-[44px] text-gray-600 font-semibold cursor-pointer">Reset</button>
                <button type="button" onclick="applyFilters()" class="w-full sm:w-auto min-h-[44px] bg-[#2D5A43] text-white px-5 py-2 text-sm cursor-pointer">
                    Apply Filter
                </button>
            </div>

        </div>
    </div>
</div>
@endpush

<style>
.dual-range {
    -webkit-appearance: none;
    appearance: none;
    height: 0;
    background: transparent;
    top: 50%;
    transform: translateY(-50%);
    left: 0;
}
.dual-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #2D5A43;
    cursor: pointer;
    pointer-events: all;
    position: relative;
    z-index: 5;
    box-shadow: 0 1px 4px rgba(0,0,0,0.15);
}
.dual-range::-moz-range-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #2D5A43;
    cursor: pointer;
    pointer-events: all;
}
</style>

<script>
/* ── OOH / DOOH section toggle ── */
function handleTypeChange(type) {
    var ooh  = document.getElementById('ooh-section');
    var dooh = document.getElementById('dooh-section');
    var hoardingSizeDiv = document.querySelector('input#hoarding-size-min').closest('div').parentElement;
    var screenSizeDiv = document.querySelector('input#screen-size-min').closest('div').parentElement;

    // Show/hide OOH/DOOH specific sections
    ooh.style.display  = (type === 'dooh') ? 'none' : '';
    dooh.style.display = (type === 'ooh')  ? 'none' : '';

    // Show/hide size filters
    if (type === 'all') {
        hoardingSizeDiv.style.display = '';
        screenSizeDiv.style.display = '';
    } else if (type === 'ooh') {
        hoardingSizeDiv.style.display = '';
        screenSizeDiv.style.display = 'none';
    } else if (type === 'dooh') {
        hoardingSizeDiv.style.display = 'none';
        screenSizeDiv.style.display = '';
    }
}

/* ── Dual-range slider ── */
function initDualRange(minInputId, maxInputId, rangeMinId, rangeMaxId, fillId) {
    var minInput = document.getElementById(minInputId);
    var maxInput = document.getElementById(maxInputId);
    var rangeMin = document.getElementById(rangeMinId);
    var rangeMax = document.getElementById(rangeMaxId);
    var fill     = document.getElementById(fillId);
    var MIN = parseInt(rangeMin.min);
    var MAX = parseInt(rangeMin.max);

    function updateFill() {
        var lo = ((parseInt(rangeMin.value) - MIN) / (MAX - MIN)) * 100;
        var hi = ((parseInt(rangeMax.value) - MIN) / (MAX - MIN)) * 100;
        fill.style.left  = lo + '%';
        fill.style.width = (hi - lo) + '%';
    }

    rangeMin.addEventListener('input', function () {
        if (+rangeMin.value > +rangeMax.value) rangeMin.value = rangeMax.value;
        minInput.value = rangeMin.value;
        updateFill();
    });
    rangeMax.addEventListener('input', function () {
        if (+rangeMax.value < +rangeMin.value) rangeMax.value = rangeMin.value;
        maxInput.value = rangeMax.value;
        updateFill();
    });
    minInput.addEventListener('input', function () {
        var v = Math.min(Math.max(parseInt(minInput.value) || MIN, MIN), parseInt(maxInput.value) || MAX);
        minInput.value = v; rangeMin.value = v; updateFill();
    });
    maxInput.addEventListener('input', function () {
        var v = Math.max(Math.min(parseInt(maxInput.value) || MAX, MAX), parseInt(minInput.value) || MIN);
        maxInput.value = v; rangeMax.value = v; updateFill();
    });
    updateFill();
}

document.addEventListener('DOMContentLoaded', function () {
    handleTypeChange('all');
    initDualRange('screen-size-min',   'screen-size-max',   'screen-size-range-min',   'screen-size-range-max',   'screen-size-fill');
    initDualRange('hoarding-size-min', 'hoarding-size-max', 'hoarding-size-range-min', 'hoarding-size-range-max', 'hoarding-size-fill');
});
</script>
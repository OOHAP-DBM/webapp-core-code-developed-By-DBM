<!-- Filter Modal -->
<div id="filterModal" class="fixed inset-0 z-50 hidden">

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/40" onclick="closeFilterModal()"></div>

    <!-- Panel -->
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white w-full max-w-2xl shadow-xl overflow-y-auto max-h-[90vh]">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 bg-[#D9F2E6]">
                <h2 class="text-lg font-semibold text-gray-800">Filters</h2>
                <button onclick="closeFilterModal()" class="text-gray-600 text-xl cursor-pointer">✕</button>
            </div>

            <div class="p-6 space-y-6 text-sm text-gray-700">

                <!-- Types of Hoarding -->
                <div>
                    <p class="font-semibold mb-2">Types of Hoarding</p>
                    <div class="flex gap-5">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="filter_type" value="all" checked onchange="handleTypeChange(this.value)"> All
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="filter_type" value="ooh" onchange="handleTypeChange(this.value)"> Ooh
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="filter_type" value="dooh" onchange="handleTypeChange(this.value)"> Dooh
                        </label>
                    </div>
                </div>

                <!-- Resolution (DOOH only) -->
                <div id="dooh-section">
                    <p class="font-semibold mb-2">Resolution</p>
                    <div class="flex gap-5">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="filter_resolution" value="led"> LED
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="filter_resolution" value="hd"> HD
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="filter_resolution" value="ultra_hd"> Ultra HD
                        </label>
                    </div>
                </div>

                <!-- Screen Size -->
                <div>
                    <p class="font-semibold mb-2">Screen Size</p>
                    <div class="flex items-center gap-3">
                        <input id="screen-size-min" name="filter_screen_min" type="number" min="0" max="1000" value="0"
                            class="w-1/2 border border-gray-200 px-3 py-2 text-xs bg-gray-50" placeholder="Min (Sq.ft)">
                        <input id="screen-size-max" name="filter_screen_max" type="number" min="0" max="1000" value="1000"
                            class="w-1/2 border border-gray-200 px-3 py-2 text-xs bg-gray-50" placeholder="Max (Sq.ft)">
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
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_category" value="billboard"> Bill Board</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_category" value="unipole"> Unipole</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_category" value="gantry"> Gantry</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_category" value="pole_kiosk"> Pole Kiosk</label>
                    </div>
                </div>

                <!-- Availability -->
                <div>
                    <p class="font-semibold mb-2">Availability</p>
                    <div class="flex gap-5">
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_availability" value="available"> Available Hoardings</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_availability" value="booked"> Booked Hoardings</label>
                    </div>
                </div>

                <!-- Surroundings -->
                <div>
                    <p class="font-semibold mb-2">Surroundings</p>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_surroundings" value="crossroad"> Crossroad</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_surroundings" value="highway"> Highway</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_surroundings" value="market_area"> Market Area</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_surroundings" value="commercial_complexes"> Commercial Complexes</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="filter_surroundings" value="main_road"> Main Road</label>
                    </div>
                </div>

                <!-- Hoarding Size -->
                <div>
                    <p class="font-semibold mb-2">Hoarding Size</p>
                    <div class="flex items-center gap-3">
                        <input id="hoarding-size-min" name="filter_hoarding_min" type="number" min="0" max="1000" value="0"
                            class="w-1/2 border border-gray-200 px-3 py-2 text-xs bg-gray-50" placeholder="Min (Sq.ft)">
                        <input id="hoarding-size-max" name="filter_hoarding_max" type="number" min="0" max="1000" value="1000"
                            class="w-1/2 border border-gray-200 px-3 py-2 text-xs bg-gray-50" placeholder="Max (Sq.ft)">
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
            <div class="flex px-6 py-4 gap-3 justify-end">
                <button type="button" onclick="resetFilters()" class="text-gray-600 font-semibold cursor-pointer">Reset</button>
                <button type="button" onclick="applyFilters()" class="bg-[#2D5A43] text-white px-5 py-2 text-sm cursor-pointer">
                    Apply Filter
                </button>
            </div>

        </div>
    </div>
</div>

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
    ooh.style.display  = (type === 'dooh') ? 'none' : '';
    dooh.style.display = (type === 'ooh')  ? 'none' : '';
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

/* ── Apply Filters → calls loadHoardings(params) from create.blade.php ── */
function applyFilters() {
    var params = {};

    // --- Type ---
    var typeEl = document.querySelector('input[name="filter_type"]:checked');
    params.type = typeEl ? typeEl.value.toUpperCase() : '';

    // --- Resolution ---
    var resolutions = Array.from(document.querySelectorAll('input[name="filter_resolution"]:checked'))
                           .map(function(i){ return i.value; });
    params.resolution = resolutions.join(',');

    // --- Screen size ---
    var sMin = document.getElementById('screen-size-min').value;
    var sMax = document.getElementById('screen-size-max').value;
    params.screen_size_min = sMin;
    params.screen_size_max = sMax;

    // --- Category ---
    var categories = Array.from(document.querySelectorAll('input[name="filter_category"]:checked'))
                          .map(function(i){ return i.value; });
    params.category = categories.join(',');

    // --- Availability ---
    var availability = Array.from(document.querySelectorAll('input[name="filter_availability"]:checked'))
                            .map(function(i){ return i.value; });
    params.availability = availability.join(',');

    // --- Surroundings ---
    var surroundings = Array.from(document.querySelectorAll('input[name="filter_surroundings"]:checked'))
                            .map(function(i){ return i.value; });
    params.surroundings = surroundings.join(',');

    // --- Hoarding size ---
    var hMin = document.getElementById('hoarding-size-min').value;
    var hMax = document.getElementById('hoarding-size-max').value;
    params.hoarding_size_min = hMin;
    params.hoarding_size_max = hMax;

    // Debugging feedback
    if (typeof window.showToast === 'function') {
        window.showToast('Applying filters...');
    }
    console.log('Applying filters:', params);

    closeFilterModal();
    if (typeof window.loadHoardings === 'function') {
        window.loadHoardings(params);
    } else {
        alert('loadHoardings not found!');
    }
}

/* ── Reset all filters ── */
function resetFilters() {
    document.querySelector('input[name="filter_type"][value="all"]').checked = true;
    handleTypeChange('all');

    document.querySelectorAll('#filterModal input[type="checkbox"]').forEach(function(cb){ cb.checked = false; });

    document.getElementById('screen-size-min').value   = 0;
    document.getElementById('screen-size-max').value   = 1000;
    document.getElementById('hoarding-size-min').value = 0;
    document.getElementById('hoarding-size-max').value = 1000;

    document.getElementById('screen-size-range-min').value   = 0;
    document.getElementById('screen-size-range-max').value   = 1000;
    document.getElementById('hoarding-size-range-min').value = 0;
    document.getElementById('hoarding-size-range-max').value = 1000;

    document.getElementById('screen-size-fill').style.left    = '0%';
    document.getElementById('screen-size-fill').style.width   = '100%';
    document.getElementById('hoarding-size-fill').style.left  = '0%';
    document.getElementById('hoarding-size-fill').style.width = '100%';

    closeFilterModal();
    loadHoardings(); // no params = all hoardings
}

/* ── Modal helpers (also used by create.blade.php button) ── */
function openFilterModal()  { document.getElementById('filterModal').classList.remove('hidden'); }
function closeFilterModal() { document.getElementById('filterModal').classList.add('hidden'); }
</script>
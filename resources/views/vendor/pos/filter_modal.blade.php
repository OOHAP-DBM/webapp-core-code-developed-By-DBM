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
                            <input type="radio" name="type" value="all" checked onchange="handleTypeChange(this.value)"> All
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="type" value="ooh" onchange="handleTypeChange(this.value)"> Ooh
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="type" value="dooh" onchange="handleTypeChange(this.value)"> Dooh
                        </label>
                    </div>
                </div>

                <!-- Resolution -->
                <div id="dooh-section">
                    <p class="font-semibold mb-2">Resolution</p>
                    <div class="flex gap-5">
                        <label class="flex items-center gap-2">
                            <input type="checkbox"> LED
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox"> HD
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox"> Ultra HD
                        </label>
                    </div>
                </div>

                <!-- Screen Size -->
                <div>
                    <p class="font-semibold mb-2">Screen Size</p>
                    <div class="flex items-center gap-3">
                        <input id="screen-size-min" type="number" min="0" max="100" value="12" class="w-1/2 border border-gray-200 px-3 py-2 text-xs bg-gray-50" placeholder="Min Height (Sq.ft)">
                        <input id="screen-size-max" type="number" min="0" max="100" value="16" class="w-1/2 border border-gray-200 px-3 py-2 text-xs bg-gray-50" placeholder="Max Height (Sq.ft)">
                    </div>
                    <input id="screen-size-range" type="range" min="0" max="100" value="12" class="w-full accent-[#2D5A43] mt-3" step="1">
                </div>

                <!-- Category -->
                <div id="ooh-section">
                    <p class="font-semibold mb-2">Category</p>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2"><input type="checkbox"> Bill Board</label>
                        <label class="flex items-center gap-2"><input type="checkbox"> Unipole</label>
                        <label class="flex items-center gap-2"><input type="checkbox"> Gantry</label>
                        <label class="flex items-center gap-2"><input type="checkbox"> Pole Kiosk</label>
                    </div>
                </div>

                <!-- Availability -->
                <div>
                    <p class="font-semibold mb-2">Availability</p>
                    <div class="flex gap-5">
                        <label class="flex items-center gap-2"><input type="checkbox"> Available Hoardings</label>
                        <label class="flex items-center gap-2"><input type="checkbox"> Booked Hoardings</label>
                    </div>
                </div>

                <!-- Surroundings -->
                <div>
                    <p class="font-semibold mb-2">Surroundings</p>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2"><input type="checkbox"> Crossroad</label>
                        <label class="flex items-center gap-2"><input type="checkbox"> Highway</label>
                        <label class="flex items-center gap-2"><input type="checkbox"> Market Area</label>
                        <label class="flex items-center gap-2"><input type="checkbox"> Commercial Complexes</label>
                        <label class="flex items-center gap-2"><input type="checkbox"> Main Road</label>
                    </div>
                </div>

                <!-- Hoarding Size -->
                <div>
                    <p class="font-semibold mb-2">Hoarding Size</p>
                    <div class="flex items-center gap-3">
                        <input id="hoarding-size-min" type="number" min="0" max="100" value="12" class="w-1/2 border border-gray-200 px-3 py-2 text-xs bg-gray-50" placeholder="Min Height (Sq.ft)">
                        <input id="hoarding-size-max" type="number" min="0" max="100" value="16" class="w-1/2 border border-gray-200 px-3 py-2 text-xs bg-gray-50" placeholder="Max Height (Sq.ft)">
                    </div>
                    <input id="hoarding-size-range" type="range" min="0" max="100" value="12" class="w-full accent-[#2D5A43] mt-3" step="1">
                </div>
            </div>

            <!-- Footer -->
            <div class="flex px-6 py-4 gap-3 justify-end">
                <button class="text-gray-600 font-semibold cursor-pointer">Reset</button>
                <button class="bg-[#2D5A43] text-white px-5 py-2 text-sm cursor-pointer">
                    Apply Filter
                </button>
            </div>

        </div>
    </div>
</div>

<script>
// Show/hide OOH/DOOH filter sections based on type selection
function handleTypeChange(type) {
    var oohSection = document.getElementById('ooh-section');
    var doohSection = document.getElementById('dooh-section');
    if (type === 'all') {
        oohSection.style.display = '';
        doohSection.style.display = '';
    } else if (type === 'ooh') {
        oohSection.style.display = '';
        doohSection.style.display = 'none';
    } else if (type === 'dooh') {
        oohSection.style.display = 'none';
        doohSection.style.display = '';
    }
}
// Range sync logic for Screen Size
document.addEventListener('DOMContentLoaded', function() {
    handleTypeChange('all');
    var screenMin = document.getElementById('screen-size-min');
    var screenMax = document.getElementById('screen-size-max');
    var screenRange = document.getElementById('screen-size-range');
    if (screenMin && screenMax && screenRange) {
        screenMin.addEventListener('input', function() {
            if (parseInt(screenMin.value) > parseInt(screenMax.value)) screenMin.value = screenMax.value;
            screenRange.value = screenMin.value;
        });
        screenMax.addEventListener('input', function() {
            if (parseInt(screenMax.value) < parseInt(screenMin.value)) screenMax.value = screenMin.value;
        });
        screenRange.addEventListener('input', function() {
            screenMin.value = screenRange.value;
        });
    }
    var hoardingMin = document.getElementById('hoarding-size-min');
    var hoardingMax = document.getElementById('hoarding-size-max');
    var hoardingRange = document.getElementById('hoarding-size-range');
    if (hoardingMin && hoardingMax && hoardingRange) {
        hoardingMin.addEventListener('input', function() {
            if (parseInt(hoardingMin.value) > parseInt(hoardingMax.value)) hoardingMin.value = hoardingMax.value;
            hoardingRange.value = hoardingMin.value;
        });
        hoardingMax.addEventListener('input', function() {
            if (parseInt(hoardingMax.value) < parseInt(hoardingMin.value)) hoardingMax.value = hoardingMin.value;
        });
        hoardingRange.addEventListener('input', function() {
            hoardingMin.value = hoardingRange.value;
        });
    }
});
</script>
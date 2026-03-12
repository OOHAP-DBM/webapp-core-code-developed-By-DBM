@php
    $selectedType = strtolower((string) request('hoarding_type', request('type', '')));
    $selectedResolution = collect((array) request('resolution', []))->map(function ($value) {
        return strtolower((string) $value);
    })->all();
    $selectedCategory = collect((array) request('category', []))->map(function ($value) {
        return strtolower((string) $value);
    })->all();
    $selectedAvailability = collect((array) request('availability', []))->map(function ($value) {
        return strtolower((string) $value);
    })->all();
    $selectedSurroundings = collect((array) request('surroundings', []))->map(function ($value) {
        return strtolower((string) $value);
    })->all();

    $resetFilterParams = array_filter([
        'tab' => $activeTab,
        'search' => request('search'),
        'letter' => request('letter'),
        'per_page' => request('per_page'),
    ]);

    $screenMinValue = request()->filled('screen_size_min') ? (int) request('screen_size_min') : 0;
    $screenMaxValue = request()->filled('screen_size_max') ? (int) request('screen_size_max') : 1000;
    $screenSliderMax = max(1000, $screenMinValue, $screenMaxValue);

    $hoardingMinValue = request()->filled('hoarding_size_min') ? (int) request('hoarding_size_min') : 0;
    $hoardingMaxValue = request()->filled('hoarding_size_max') ? (int) request('hoarding_size_max') : 1000;
    $hoardingSliderMax = max(1000, $hoardingMinValue, $hoardingMaxValue);
@endphp

<div id="hoarding-filter-modal" class="fixed inset-0 z-[1200] hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeHoardingFilterModal()"></div>

    <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4">
        <div class="relative bg-white w-full max-w-xl shadow-xl overflow-hidden max-h-[92vh] rounded-lg border border-gray-100">
            <div class="flex items-center justify-between px-4 sm:px-6 py-4 bg-[#D9F2E6] border-b border-[#CDE5DA]">
                <h4 class="text-lg font-semibold text-gray-800">Filters</h4>
                <button type="button" onclick="closeHoardingFilterModal()" class="text-gray-600 text-xl cursor-pointer leading-none" aria-label="Close filter popup">&times;</button>
            </div>

            <form id="hoardingFilterForm" method="GET" action="{{ route('vendor.hoardings.myHoardings') }}" onsubmit="return applyHoardingFilters(event)">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                @if(request()->filled('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(request()->filled('letter'))
                    <input type="hidden" name="letter" value="{{ request('letter') }}">
                @endif
                @if(request()->filled('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                @if(request()->filled('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request()->filled('booked'))
                    <input type="hidden" name="booked" value="{{ request('booked') }}">
                @endif
                @if(request()->filled('unsold'))
                    <input type="hidden" name="unsold" value="{{ request('unsold') }}">
                @endif

                <div class="p-4 sm:p-6 space-y-6 text-sm text-gray-700 overflow-y-auto max-h-[calc(92vh-132px)]">
                    <div>
                        <p class="font-semibold mb-2 text-gray-800">Types of Hoarding</p>
                        <div class="flex flex-wrap gap-4 sm:gap-5">
                            <label class="flex items-center gap-2"><input type="radio" name="hoarding_type" value="" {{ $selectedType === '' ? 'checked' : '' }}> All</label>
                            <label class="flex items-center gap-2"><input type="radio" name="hoarding_type" value="ooh" {{ $selectedType === 'ooh' ? 'checked' : '' }}> Ooh</label>
                            <label class="flex items-center gap-2"><input type="radio" name="hoarding_type" value="dooh" {{ $selectedType === 'dooh' ? 'checked' : '' }}> Dooh</label>
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold mb-2 text-gray-800">Resolution</p>
                        <div class="flex flex-wrap gap-4 sm:gap-5">
                            <label class="flex items-center gap-2"><input type="checkbox" name="resolution[]" value="led" {{ in_array('led', $selectedResolution, true) ? 'checked' : '' }}> LED</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="resolution[]" value="hd" {{ in_array('hd', $selectedResolution, true) ? 'checked' : '' }}> HD</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="resolution[]" value="ultra_hd" {{ in_array('ultra_hd', $selectedResolution, true) ? 'checked' : '' }}> Ultra HD</label>
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold mb-2 text-gray-800">Screen Size</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <input id="screen_size_min_input" type="number" name="screen_size_min" value="{{ $screenMinValue }}" placeholder="0" class="w-full border border-gray-200 rounded-md px-3 py-2 text-xs bg-gray-50 min-h-[44px] focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <input id="screen_size_max_input" type="number" name="screen_size_max" value="{{ $screenMaxValue }}" placeholder="1000" class="w-full border border-gray-200 rounded-md px-3 py-2 text-xs bg-gray-50 min-h-[44px] focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div class="range-slider mt-3" data-range="screen_size">
                            <div class="range-track"></div>
                            <div class="range-fill"></div>
                            <input id="screen_size_min_slider" type="range" min="0" max="{{ $screenSliderMax }}" value="{{ $screenMinValue }}">
                            <input id="screen_size_max_slider" type="range" min="0" max="{{ $screenSliderMax }}" value="{{ $screenMaxValue }}">
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold mb-2 text-gray-800">Category</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-2"><input type="checkbox" name="category[]" value="bill board" {{ in_array('bill board', $selectedCategory, true) ? 'checked' : '' }}> Bill Board</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="category[]" value="unipole" {{ in_array('unipole', $selectedCategory, true) ? 'checked' : '' }}> Unipole</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="category[]" value="gantry" {{ in_array('gantry', $selectedCategory, true) ? 'checked' : '' }}> Gantry</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="category[]" value="pole kiosk" {{ in_array('pole kiosk', $selectedCategory, true) ? 'checked' : '' }}> Pole Kiosk</label>
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold mb-2 text-gray-800">Availability</p>
                        <div class="flex flex-wrap gap-4 sm:gap-5">
                            <label class="flex items-center gap-2"><input type="checkbox" name="availability[]" value="available" {{ in_array('available', $selectedAvailability, true) ? 'checked' : '' }}> Available Hoardings</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="availability[]" value="booked" {{ in_array('booked', $selectedAvailability, true) ? 'checked' : '' }}> Booked Hoardings</label>
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold mb-2 text-gray-800">Surroundings</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-2"><input type="checkbox" name="surroundings[]" value="crossroad" {{ in_array('crossroad', $selectedSurroundings, true) ? 'checked' : '' }}> Crossroad</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="surroundings[]" value="highway" {{ in_array('highway', $selectedSurroundings, true) ? 'checked' : '' }}> Highway</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="surroundings[]" value="market area" {{ in_array('market area', $selectedSurroundings, true) ? 'checked' : '' }}> Market Area</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="surroundings[]" value="commercial complexes" {{ in_array('commercial complexes', $selectedSurroundings, true) ? 'checked' : '' }}> Commercial Complexes</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="surroundings[]" value="main road" {{ in_array('main road', $selectedSurroundings, true) ? 'checked' : '' }}> Main Road</label>
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold mb-2 text-gray-800">Hoarding Size</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <input id="hoarding_size_min_input" type="number" name="hoarding_size_min" value="{{ $hoardingMinValue }}" placeholder="0" class="w-full border border-gray-200 rounded-md px-3 py-2 text-xs bg-gray-50 min-h-[44px] focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <input id="hoarding_size_max_input" type="number" name="hoarding_size_max" value="{{ $hoardingMaxValue }}" placeholder="1000" class="w-full border border-gray-200 rounded-md px-3 py-2 text-xs bg-gray-50 min-h-[44px] focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div class="range-slider mt-3" data-range="hoarding_size">
                            <div class="range-track"></div>
                            <div class="range-fill"></div>
                            <input id="hoarding_size_min_slider" type="range" min="0" max="{{ $hoardingSliderMax }}" value="{{ $hoardingMinValue }}">
                            <input id="hoarding_size_max_slider" type="range" min="0" max="{{ $hoardingSliderMax }}" value="{{ $hoardingMaxValue }}">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row px-4 sm:px-6 py-4 gap-2 sm:gap-3 justify-end border-t border-gray-100 bg-white">
                    <a id="hoardingFilterResetLink" href="{{ route('vendor.hoardings.myHoardings', $resetFilterParams) }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center text-gray-600 font-semibold hover:text-gray-800">Reset</a>
                    <button type="submit" class="w-full sm:w-auto min-h-[44px] bg-[#2D5A43] hover:bg-[#234635] text-white px-5 py-2 text-sm font-medium rounded-md">
                        Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function applyHoardingFilters(event) {
    if (event) {
        event.preventDefault();
    }

    var form = document.getElementById('hoardingFilterForm');
    if (!form) {
        return false;
    }

    var formData = new FormData(form);
    var params = new URLSearchParams();
    var rangeKeys = {
        screen_size_min: true,
        screen_size_max: true,
        hoarding_size_min: true,
        hoarding_size_max: true
    };

    formData.forEach(function (value, key) {
        if (rangeKeys[key]) {
            return;
        }

        var normalized = (value === null || value === undefined ? '' : value).toString().trim();
        if (normalized === '' && key !== 'tab') {
            return;
        }
        params.append(key, normalized);
    });

    function appendRangeIfChanged(minInputId, maxInputId, minSliderId, maxSliderId, minKey, maxKey) {
        var minInput = document.getElementById(minInputId);
        var maxInput = document.getElementById(maxInputId);
        var minSlider = document.getElementById(minSliderId);
        var maxSlider = document.getElementById(maxSliderId);

        if (!minInput || !maxInput || !minSlider || !maxSlider) {
            return;
        }

        var defaultMin = Number(minSlider.min || 0);
        var defaultMax = Number(maxSlider.max || minSlider.max || 1000);

        var minValue = minInput.value === '' ? defaultMin : Number(minInput.value);
        var maxValue = maxInput.value === '' ? defaultMax : Number(maxInput.value);

        if (!Number.isFinite(minValue)) {
            minValue = defaultMin;
        }
        if (!Number.isFinite(maxValue)) {
            maxValue = defaultMax;
        }

        if (minValue > maxValue) {
            var temp = minValue;
            minValue = maxValue;
            maxValue = temp;
        }

        if (minValue === defaultMin && maxValue === defaultMax) {
            return;
        }

        params.append(minKey, String(minValue));
        params.append(maxKey, String(maxValue));
    }

    appendRangeIfChanged(
        'screen_size_min_input',
        'screen_size_max_input',
        'screen_size_min_slider',
        'screen_size_max_slider',
        'screen_size_min',
        'screen_size_max'
    );

    appendRangeIfChanged(
        'hoarding_size_min_input',
        'hoarding_size_max_input',
        'hoarding_size_min_slider',
        'hoarding_size_max_slider',
        'hoarding_size_min',
        'hoarding_size_max'
    );

    var queryString = params.toString();
    var targetUrl = form.getAttribute('action') + (queryString ? ('?' + queryString) : '');
    window.location.href = targetUrl;
    return false;
}
</script>

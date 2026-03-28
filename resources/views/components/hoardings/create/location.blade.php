@php
    $hoarding = $draft->hoarding ?? null;
@endphp
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- MarkerCluster CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- MarkerCluster JS -->
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100">

    <h3 class="text-lg font-bold text-[#009A5C] mb-6 flex items-center">
        <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
        Hoarding Location
    </h3>

    <!-- First Row: Pincode | City | State -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-6 mb-4">

        <!-- Pincode -->
        <div class="space-y-2">
            <label class="text-sm font-bold text-gray-700">Pincode <span class="text-red-500">*</span></label>
            <input type="text" name="pincode" id="pincode"
                value="{{ old('pincode', $hoarding?->pincode) }}"
                placeholder="226010"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:border-[#009A5C] outline-none">
            @error('pincode') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- City -->
        <div class="space-y-2">
            <label class="text-sm font-bold text-gray-700">City <span class="text-red-500">*</span></label>
            <input type="text" name="city" id="city"
                value="{{ old('city', $hoarding?->city) }}"
                placeholder="Lucknow"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:border-[#009A5C] outline-none">
            @error('city') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- State -->
        <div class="space-y-2">
            <label class="text-sm font-bold text-gray-700">State <span class="text-red-500">*</span></label>
            <input type="text" name="state" id="state"
                value="{{ old('state', $hoarding?->state) }}"
                placeholder="Uttar Pradesh"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:border-[#009A5C] outline-none">
            @error('state') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

        </div>

    </div>

    <!-- Second Row: Locality | Full Address -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 mb-4">

        <!-- Locality -->
        <div class="space-y-2">
            <label class="text-sm font-bold text-gray-700">Locality <span class="text-red-500">*</span></label>
            <input type="text" name="locality" id="locality"
                value="{{ old('locality', $hoarding?->locality) }}"
                placeholder="Indira Nagar"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:border-[#009A5C] outline-none">
            @error('locality') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Full Address -->
        <div class="space-y-2 md:col-span-1">
           <label class="text-sm font-bold text-gray-700">Full Address <span class="text-red-500">*</span></label>
            <input name="address" id="address"
                value="{{ old('address', $hoarding?->address) }}"
                placeholder="Enter exact address or landmark"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:border-[#009A5C] outline-none">
            @error('address') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

        </div>

    </div>

    <!-- Location Verification -->
    <div class="mt-6 bg-[#FBFBFB] rounded-3xl border border-gray-100 p-6 space-y-6">

        <div class="flex flex-wrap justify-between items-start sm:items-center gap-3">
            <h3 class="text-base font-bold text-gray-800">
            Location Verification
            </h3>

            <button type="button" id="geotagBtn"
                class="bg-[#009A5C] text-white text-sm font-bold px-6 py-2.5 rounded-xl hover:bg-green-700 transition whitespace-nowrap shrink-0">
                Sync to Map
            </button>
        </div>

        <div id="location-error" class="text-xs text-red-500 hidden bg-red-50 p-2 rounded"></div>

       <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="text-sm font-bold">Latitude *</label>
        <input type="text" name="lat" id="lat"value="{{ old('lat', $hoarding?->lat) }}"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5">
            @error('lat') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="text-sm font-bold">Longitude *</label>
        <input type="text" name="lng" id="lng" value="{{ old('lng', $hoarding?->lng) }}"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5">
            @error('lng') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>
</div>

        <div class="rounded-2xl overflow-hidden border border-gray-200">
            <div id="map" class="w-full h-[350px]"></div>
        </div>
    </div>

</div>

<script>
let map, marker;
let typingTimer;
const debounceTime = 800;

const inputs = {
    pincode: document.getElementById('pincode'),
    locality: document.getElementById('locality'),
    city: document.getElementById('city'),
    state: document.getElementById('state'),
    lat: document.getElementById('lat'),
    lng: document.getElementById('lng'),
    error: document.getElementById('location-error'),
    btn: document.getElementById('geotagBtn')
};

function initMap() {
    const lat = parseFloat(inputs.lat.value) || 26.8467;
    const lng = parseFloat(inputs.lng.value) || 81.0279;

    map = L.map('map').setView([lat, lng], inputs.lat.value ? 16 : 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    marker.on('dragend', function () {
        const pos = marker.getLatLng();
        reverseGeocode(pos.lat, pos.lng);
    });
}

/* ================================
   FORWARD GEOCODE (PINCODE FIRST)
================================ */
// async function syncAddressToMap() {

//     const pincode = inputs.pincode.value.trim();
// const locality = inputs.locality.value.trim();
// const city = inputs.city.value.trim();
// const state = inputs.state.value.trim();

// let query = "";

// if (pincode) {
//     query = `${locality}, ${city}, ${state}, ${pincode}, India`;
// } else {
//     query = [locality, city, state].filter(Boolean).join(", ");
// }

//     try {
//         inputs.error.classList.add('hidden');

//         const res = await fetch(`/api/geocode?q=${encodeURIComponent(query)}`);
//         const result = await res.json();

//         if (result.success && result.data.length) {

//             const r = result.data[0];
//             const lat = parseFloat(r.lat);
//             const lng = parseFloat(r.lon);

//             marker.setLatLng([lat, lng]);
//             map.setView([lat, lng], 16);

//             inputs.lat.value = lat.toFixed(6);
//             inputs.lng.value = lng.toFixed(6);

//             fillFields(r.address);

//         } else {
//             showError("Location not found.");
//         }

//     } catch (e) {
//         showError("Location service unavailable.");
//     }
// }
async function syncAddressToMap() {

    const pincode = inputs.pincode.value.trim();
    const locality = inputs.locality.value.trim();
    const city = inputs.city.value.trim();
    const state = inputs.state.value.trim();

    const queries = [
        `${locality}, ${city}, ${state}, India`,
        `${city}, ${state}, ${pincode}, India`,
        `${pincode}, India`,
        `${city}, ${state}, India`
    ];

    try {

        inputs.error.classList.add('hidden');

        for (let query of queries) {

            const res = await fetch(`/api/geocode?q=${encodeURIComponent(query)}`);
            const result = await res.json();

            if (result.success && result.data.length) {

                const r = result.data[0];

                const lat = parseFloat(r.lat);
                const lng = parseFloat(r.lon);

                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 16);

                inputs.lat.value = lat.toFixed(6);
                inputs.lng.value = lng.toFixed(6);

                fillFields(r.address);

                return;
            }
        }

        showError("Location not found.");

    } catch (e) {

        showError("Location service unavailable.");

    }

}

/* ================================
   REVERSE GEOCODE
================================ */
async function reverseGeocode(lat, lng) {

    inputs.lat.value = lat.toFixed(6);
    inputs.lng.value = lng.toFixed(6);

    try {
        const res = await fetch(`/api/reverse-geocode?lat=${lat}&lng=${lng}`);
        const result = await res.json();

        if (result.success) {
            fillFields(result.data.address, true, result.data);
        }

    } catch (e) {
        console.error("Reverse error");
    }
}

function fillFields(ad, forceUpdate = false, rawData = null) {
    if (forceUpdate || !inputs.locality.value.trim()) {
        inputs.locality.value =
            ad.suburb ||
            ad.neighbourhood ||
            ad.residential ||
            ad.hamlet ||
            ad.quarter ||
            ad.city_district ||
            ad.municipality ||
            ad.village ||
            ad.road ||
            rawData?.name ||
            rawData?.display_name?.split(',')?.[0]?.trim() ||
            "";
    }
        inputs.city.value =
        ad.city ||
        ad.town ||
        ad.county ||
        ad.state_district ||
        "";

    inputs.state.value = ad.state || "";

    const currentPincode = inputs.pincode.value.trim();
    const hasUserEnteredValidPincode = /^\d{6}$/.test(currentPincode);

    if (ad.postcode && (forceUpdate || !hasUserEnteredValidPincode)) {
        inputs.pincode.value = ad.postcode;
    }
}

function showError(message) {
    inputs.error.innerText = message;
    inputs.error.classList.remove('hidden');
}

/* ================================
   LISTENERS
================================ */
// [inputs.pincode, inputs.locality, inputs.city, inputs.state]
// .forEach(el => {
//     el.addEventListener('input', () => {
//         clearTimeout(typingTimer);
//         typingTimer = setTimeout(syncAddressToMap, debounceTime);
//     });
// });
/* ================================
   LISTENERS
================================ */

inputs.pincode.addEventListener('input', () => {

    const pin = inputs.pincode.value.trim();

    if (pin.length === 6) {
        lookupPincode(pin);
    }

});

[inputs.locality, inputs.city, inputs.state]
.forEach(el => {
    el.addEventListener('input', () => {

        clearTimeout(typingTimer);
        typingTimer = setTimeout(syncAddressToMap, debounceTime);

    });
});

inputs.btn.addEventListener('click', syncAddressToMap);

window.addEventListener('load', initMap);




// async function lookupPincode(pin) {

//     try {

//         const res = await fetch(`https://api.postalpincode.in/pincode/${pin}`);
//         const data = await res.json();

//         if (data[0].Status !== "Success") {
//             showError("Invalid Pincode");
//             return;
//         }

//         const post = data[0].PostOffice[0];

//         // Fill fields
//             inputs.locality.value = post.Name || "";
//             inputs.city.value = post.District || "";
//             inputs.state.value = post.State || "";
//         // Now zoom map using full address
//            syncAddressToMap();

//     } catch (e) {
//         showError("Pincode lookup failed");
//     }

// }

async function lookupPincode(pin) {
    try {
        const res  = await fetch(`https://api.postalpincode.in/pincode/${pin}`);
        const data = await res.json();

        if (data[0].Status !== "Success") {
            showError("Invalid Pincode");
            return;
        }

        const post = data[0].PostOffice[0];

        inputs.locality.value = post.Name     || "";
        inputs.city.value     = post.District || "";
        inputs.state.value    = post.State    || "";

        // ✅ Try pincode directly on Nominatim with postalcode param
        await syncByPincode(pin, post);

    } catch (e) {
        showError("Pincode lookup failed");
    }
}

async function syncByPincode(pin, post) {
    const city  = post?.District || inputs.city.value.trim();
    const state = post?.State    || inputs.state.value.trim();

    inputs.error.classList.add('hidden');

    // ✅ These query strategies work best for Indian pincodes on Nominatim
    const strategies = [
        // Strategy 1: direct postalcode param — most accurate for India
        `/api/geocode?postalcode=${encodeURIComponent(pin)}&countrycodes=in`,

        // Strategy 2: pincode + city
        `/api/geocode?q=${encodeURIComponent(`${pin}, ${city}, India`)}`,

        // Strategy 3: city + state + pincode
        `/api/geocode?q=${encodeURIComponent(`${city}, ${state}, ${pin}, India`)}`,

        // Strategy 4: city only as fallback
        `/api/geocode?q=${encodeURIComponent(`${city}, ${state}, India`)}`,
    ];

    for (let url of strategies) {
        try {
            const res    = await fetch(url);
            const result = await res.json();

            if (!result.success || !result.data?.length) continue;

            // ✅ Find best match — prefer result whose postcode matches
            const matched = result.data.find(r =>
                (r.address?.postcode || '').replace(/\s+/g, '') === pin
            ) || result.data[0];

            const lat = parseFloat(matched.lat);
            const lng = parseFloat(matched.lon);

            if (isNaN(lat) || isNaN(lng)) continue;

            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 15);

            inputs.lat.value = lat.toFixed(6);
            inputs.lng.value = lng.toFixed(6);

            fillFields(matched.address);
            return; // ✅ stop on first success

        } catch (e) {
            continue;
        }
    }

    showError("Location not found. Try entering address manually.");
}

async function syncAddressToMap() {
    const pincode  = inputs.pincode.value.trim();
    const locality = inputs.locality.value.trim();
    const city     = inputs.city.value.trim();
    const state    = inputs.state.value.trim();

    if (!locality && !city && !pincode) return;

    // ✅ If valid pincode exists use pincode strategy
    if (/^\d{6}$/.test(pincode)) {
        await syncByPincode(pincode, { District: city, State: state });
        return;
    }

    // ✅ Without pincode — locality+city works better than city first
    const queries = [
        `${locality}, ${city}, ${state}, India`,
        `${locality}, ${city}, India`,
        `${city}, ${state}, India`,
    ].filter(q => q.replace(/,\s*/g, '').trim() !== 'India');

    inputs.error.classList.add('hidden');

    for (let query of queries) {
        try {
            const res    = await fetch(`/api/geocode?q=${encodeURIComponent(query)}`);
            const result = await res.json();

            if (!result.success || !result.data?.length) continue;

            const r   = result.data[0];
            const lat = parseFloat(r.lat);
            const lng = parseFloat(r.lon);

            if (isNaN(lat) || isNaN(lng)) continue;

            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 15);

            inputs.lat.value = lat.toFixed(6);
            inputs.lng.value = lng.toFixed(6);

            fillFields(r.address);
            return;

        } catch (e) {
            continue;
        }
    }

    showError("Location not found.");
}
</script>

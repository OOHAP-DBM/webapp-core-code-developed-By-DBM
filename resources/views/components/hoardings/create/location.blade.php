@php
    $hoarding = $draft->hoarding ?? null;
@endphp

<div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">

    <h3 class="text-lg font-bold text-[#009A5C] mb-6 flex items-center">
        <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
        Hoarding Location
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-6">

        <div class="space-y-2">
            <label class="text-sm font-bold text-gray-700">Full Address</label>
            <input name="address" id="address"
                value="{{ old('address', $hoarding?->address) }}"
                placeholder="Enter complete address"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:border-[#009A5C] outline-none transition-all">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-bold text-gray-700">Pincode <span class="text-red-500">*</span></label>
            <input type="text" name="pincode" id="pincode"
                value="{{ old('pincode', $hoarding?->pincode) }}"
                placeholder="226010"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:border-[#009A5C] outline-none transition-all">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-bold text-gray-700">Locality <span class="text-red-500">*</span></label>
            <input type="text" name="locality" id="locality"
                value="{{ old('locality', $hoarding?->locality) }}"
                placeholder="Indira Nagar"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:border-[#009A5C] outline-none transition-all">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">City <span class="text-red-500">*</span></label>
                <input type="text" name="city" id="city"
                    value="{{ old('city', $hoarding?->city) }}"
                    placeholder="Lucknow"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-[#009A5C]">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">State <span class="text-red-500">*</span></label>
                <input type="text" name="state" id="state"
                    value="{{ old('state', $hoarding?->state) }}"
                    placeholder="Uttar Pradesh"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-[#009A5C]">
            </div>
        </div>
    </div>

    <div class="mt-8 bg-[#FBFBFB] rounded-3xl border border-gray-100 p-6 space-y-6">

        <div class="flex justify-between items-center">
            <h3 class="text-base font-bold text-gray-800">
                📍 Location Verification
            </h3>

            <button type="button" id="geotagBtn"
                class="bg-[#009A5C] text-white text-sm font-bold px-6 py-2.5 rounded-xl hover:bg-green-700 transition">
                Sync to Map
            </button>
        </div>

        <div id="location-error"
            class="text-xs text-red-500 hidden bg-red-50 p-2 rounded"></div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="text-sm font-bold">Latitude *</label>
                <input type="text" name="lat" id="lat"
                    value="{{ old('lat', $hoarding?->latitude) }}"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5">
            </div>
            <div>
                <label class="text-sm font-bold">Longitude *</label>
                <input type="text" name="lng" id="lng"
                    value="{{ old('lng', $hoarding?->longitude) }}"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5">
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
async function syncAddressToMap() {

    const pincode = inputs.pincode.value.trim();
    const locality = inputs.locality.value.trim();
    const city = inputs.city.value.trim();
    const state = inputs.state.value.trim();

    let query = "";

    if (/^\d{6}$/.test(pincode)) {
        query = pincode;
    } else if (locality || city || state) {
        query = [locality, city, state].filter(Boolean).join(", ");
    } else {
        return;
    }

    try {
        inputs.error.classList.add('hidden');

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

        } else {
            showError("Location not found.");
        }

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
            fillFields(result.data.address);
        }

    } catch (e) {
        console.error("Reverse error");
    }
}

function fillFields(ad) {
    if (!inputs.locality.value.trim()) {
        inputs.locality.value =
            ad.suburb ||
            ad.neighbourhood ||
            ad.village ||
            ad.road ||
            "";
    }
    inputs.city.value =
        ad.city ||
        ad.town ||
        ad.county ||
        "";

    inputs.state.value = ad.state || "";

    if (ad.postcode) {
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
[inputs.pincode, inputs.locality, inputs.city, inputs.state]
.forEach(el => {
    el.addEventListener('input', () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(syncAddressToMap, debounceTime);
    });
});

inputs.btn.addEventListener('click', syncAddressToMap);

window.addEventListener('load', initMap);
</script>

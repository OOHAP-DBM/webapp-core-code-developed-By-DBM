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
            <label class="text-sm font-bold text-gray-700">Full Address <span class="text-red-500">*</span></label>
            <input name="address" id="address" value="{{ old('address', $hoarding?->address) }}" required 
                placeholder="Enter complete address"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:border-[#009A5C] outline-none transition-all">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-bold text-gray-700">Pincode <span class="text-red-500">*</span></label>
            <input type="text" name="pincode" id="pincode" value="{{ old('pincode', $hoarding?->pincode) }}" placeholder="eg. 226010" required 
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:border-[#009A5C] outline-none transition-all">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-bold text-gray-700">Locality <span class="text-red-500">*</span></label>
            <input type="text" name="locality" id="locality" value="{{ old('locality', $hoarding?->locality) }}" placeholder="e.g. Indira Nagar" required 
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:border-[#009A5C] outline-none transition-all">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">City <span class="text-red-500">*</span></label>
                <input type="text" name="city" id="city" value="{{ old('city', $hoarding?->city) }}" placeholder="eg. Lucknow" 
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-[#009A5C]">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">State <span class="text-red-500">*</span></label>
                <input type="text" name="state" id="state" value="{{ old('state', $hoarding?->state) }}" placeholder="eg. Uttar Pradesh" 
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 outline-none focus:border-[#009A5C]">
            </div>
        </div>
    </div>

    <div class="mt-8 bg-[#FBFBFB] rounded-3xl border border-gray-100 p-5 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center gap-2">
                    <span class="w-2 h-2 bg-[#009A5C] rounded-full"></span>
                    Location Verification
                </h3>
                <p class="text-xs text-gray-500 mt-1">Fields will auto-fill when you move the pin or click sync.</p>
            </div>

            <button type="button" id="geotagBtn" 
                class="bg-[#009A5C] text-white text-sm font-bold px-6 py-2.5 rounded-xl shadow-sm hover:bg-green-700 active:scale-95 transition">
                📍 Sync Address to Map
            </button>
        </div>

        <div id="location-error" class="text-xs text-red-500 hidden bg-red-50 p-2 rounded"></div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="space-y-1">
                <label class="text-sm font-bold text-gray-700">Latitude <span class="text-red-500">*</span></label>
                <input type="text" name="lat" id="lat" value="{{ old('lat', $hoarding?->latitude) }}" required 
                    class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:border-[#009A5C] outline-none">
            </div>
            <div class="space-y-1">
                <label class="text-sm font-bold text-gray-700">Longitude <span class="text-red-500">*</span></label>
                <input type="text" name="lng" id="lng" value="{{ old('lng', $hoarding?->longitude) }}" required 
                    class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:border-[#009A5C] outline-none">
            </div>
        </div>

        <div class="relative rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
            <div id="map" class="w-full h-[350px]"></div>
            <div class="absolute bottom-2 right-2 bg-white/90 backdrop-blur text-[10px] text-gray-600 px-3 py-1 rounded-lg shadow">
                Drag pin to update details automatically
            </div>
        </div>
    </div>
</div>

<script>
/* ==========================================
   LOCATION SYNC LOGIC (FIXED AUTO-FILL)
   ========================================== */
let map, marker;
let typingTimer;
const doneTypingInterval = 1000; // 1 second delay

const inputs = {
    address:  document.getElementById('address'),
    pincode:  document.getElementById('pincode'),
    locality: document.getElementById('locality'),
    city:     document.getElementById('city'),
    state:    document.getElementById('state'),
    lat:      document.getElementById('lat'),
    lng:      document.getElementById('lng'),
    error:    document.getElementById('location-error'),
    btn:      document.getElementById('geotagBtn')
};

function initLocationComponent() {
    const startLat = parseFloat(inputs.lat.value) || 26.8467; // Defaults to Lucknow
    const startLng = parseFloat(inputs.lng.value) || 81.0279;

    map = L.map('map').setView([startLat, startLng], (inputs.lat.value ? 16 : 5));

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    marker = L.marker([startLat, startLng], { draggable: true }).addTo(map);

    marker.on('dragend', function() {
        const pos = marker.getLatLng();
        syncCoordsToFields(pos.lat, pos.lng);
    });
}

// 1. REVERSE: Map Drag -> Fill All Fields
async function syncCoordsToFields(lat, lng) {
    inputs.lat.value = lat.toFixed(6);
    inputs.lng.value = lng.toFixed(6);
    
    try {
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`);
        const data = await res.json();
        if (data && data.address) {
            fillAddressFields(data.address, data.display_name);
        }
    } catch (e) { console.error("Reverse sync error", e); }
}

// 2. FORWARD: Input Type -> Update Map & Fields
async function syncAddressToMap(specificQuery = null) {
    let query = specificQuery || inputs.address.value;
    if (!query || query.length < 3) return;

    // If only pincode is entered → force India context
    if (/^\d{6}$/.test(query)) {
        query = `${query}, India`;
    }

    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/search?` +
            `format=json&limit=1&addressdetails=1&q=${encodeURIComponent(query)}`
        );

        const data = await res.json();

        if (data && data.length) {
            const r = data[0];
            const lat = parseFloat(r.lat);
            const lng = parseFloat(r.lon);

            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 16);

            inputs.lat.value = lat.toFixed(6);
            inputs.lng.value = lng.toFixed(6);

            fillAddressFields(r.address, r.display_name);
            inputs.error.classList.add('hidden');
        } else {
            inputs.error.innerText = "No location found for this pincode.";
            inputs.error.classList.remove('hidden');
        }
    } catch (err) {
        console.error("Geocode error:", err);
    }
}


// Helper function to map API response to Form Fields
function fillAddressFields(ad, fullDisplayName) {
    // Locality: Try suburb, then neighbourhood, then road
        inputs.locality.value =
        ad.suburb ||
        ad.neighbourhood ||
        ad.residential ||
        ad.hamlet ||
        ad.village ||
        ad.subdistrict ||
        ad.road ||
        "";
    
    // City: Try city, then town, then village
    inputs.city.value = ad.city || ad.town || ad.village || ad.municipality || ad.county || ad.state_district || "";
    
    // State
    inputs.state.value = ad.state || "";
    
    // Pincode
    if (ad.postcode) {
        inputs.pincode.value = ad.postcode;
    }
    
    // Update display address if it was a pin drag
    // if (fullDisplayName && (inputs.address.value.length < 5)) {
    //     inputs.address.value = fullDisplayName;
    // }
}

/* --- LISTENERS --- */

// Listener for typing in Address or Pincode
[inputs.address, inputs.pincode].forEach(el => {
    el.addEventListener('input', () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            // Priority: if pincode is 6 digits, sync by pincode, else by address
            const query = (inputs.pincode.value.length === 6) ? inputs.pincode.value : inputs.address.value;
            syncAddressToMap(query);
        }, doneTypingInterval);
    });
});

// Listener for manual Lat/Lng typing
[inputs.lat, inputs.lng].forEach(el => {
    el.addEventListener('change', () => {
        const lat = parseFloat(inputs.lat.value);
        const lng = parseFloat(inputs.lng.value);
        if (!isNaN(lat) && !isNaN(lng)) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 16);
            syncCoordsToFields(lat, lng);
        }
    });
});

inputs.btn.addEventListener('click', () => syncAddressToMap());
window.addEventListener('load', initLocationComponent);
</script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
{{-- <div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-6"> --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <div class="md:p-8 md:space-y-8">
        <!-- Hoarding Details -->
        <div class="bg-white rounded-3xl p-8  shadow-sm border border-gray-100">
          <h3 class="text-lg font-bold text-[#009A5C] mb-6 flex items-center">
            <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
            Hoarding Details
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-6">
            <!-- Hoarding Type -->
            <div class="space-y-2">
              <label class="text-sm font-bold text-gray-700">Hoarding Type <span class="text-red-500">*</span></label>
              <div class="w-full bg-[#0094FF] border border-[#0094FF] rounded-xl px-4 py-3 text-white font-bold">
                DOOH (Digital Out-of-Home)
              </div>
            </div>

            <!-- Category -->
            <div class="space-y-2">
              <label class="text-sm font-bold text-gray-700">Category <span class="text-red-500">*</span></label>
              <select name="category" required
                class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#009A5C]/10 focus:border-[#009A5C] outline-none appearance-none bg-[url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%226b7280%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>')] bg-[length:20px] bg-[right_1rem_center] bg-no-repeat">
                <option value="">Select Category</option>
                @if(isset($attributes['category']))
                  @foreach($attributes['category'] as $cat)
                    <option value="{{ $cat->value }}">{{ $cat->value }}</option>
                  @endforeach
                @endif
              </select>
            </div>

            <!-- Screen Type -->
            <div class="md:col-span-2 space-y-2 mt-2">
              <label class="text-sm font-bold text-gray-700">Screen Type <span class="text-red-500">*</span></label>
              <select name="screen_type" required
                class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none appearance-none bg-[url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%226b7280%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>')] bg-[length:20px] bg-[right_1rem_center] bg-no-repeat">
                <option value="LED">LED</option>
                <option value="LCD">LCD</option>
              </select>
            </div>
          </div>

          <!-- Screen Size -->
          <div class="mt-8">
            <label class="text-sm font-bold text-gray-700 mb-4 block">Screen Size</label>
            <div class="grid grid-cols-4 gap-4 items-end">
              <!-- Unit -->
              <div class="space-y-1">
                <label class="text-xs font-bold text-gray-500">Unit</label>
                <select id="unit" name="measurement_unit" required class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 outline-none">
                  <option value="sqft">Sqft</option>
                  <option value="sqm">Sqm</option>
                </select>
              </div>

              <!-- Width -->
              <div class="space-y-1">
                <label class="text-xs font-bold text-gray-500">Screen Width <span class="text-red-500">*</span></label>
                <input type="number" id="width" name="width" placeholder="eg. 500" required min="1" class="w-full border border-gray-200 rounded-lg px-3 py-3 outline-none focus:border-[#009A5C]">
              </div>

              <!-- Height -->
              <div class="space-y-1">
                <label class="text-xs font-bold text-gray-500">Screen Height <span class="text-red-500">*</span></label>
                <input type="number" id="height" name="height" placeholder="eg.300" required min="1" class="w-full border border-gray-200 rounded-lg px-3 py-3 outline-none focus:border-[#009A5C]">
              </div>

              <!-- Size Preview -->
              <div class="space-y-1">
                <label class="text-xs font-bold text-gray-500">Size Preview</label>
                <input type="text" id="sizePreview" value="0 x 0 sq.ft" readonly class="w-full bg-[#E5E9F2] border-none rounded-lg px-3 py-3 text-gray-700 font-medium outline-none">
              </div>
            </div>
          </div>
          <div class="space-y-4 mt-2">
              <label class="text-sm font-semibold text-gray-700">
                  Screen Resolution <span class="text-red-500">*</span>
              </label>

              <select id="resolution_type" name="resolution_type" required
                class="w-full rounded-xl border px-4 py-3">
                <option value="">Select resolution</option>
                <option value="1920x1080">Full HD (1920 × 1080)</option>
                <option value="3840x2160">4K (3840 × 2160)</option>
                <option value="1280x720">HD (1280 × 720)</option>
                <option value="custom">Custom</option>
              </select>

              <div id="custom_resolution" class="hidden grid grid-cols-2 gap-4">
                <input type="number" id="custom_width" name="resolution_width"
                  placeholder="Width (px)"
                  class="rounded-xl border px-4 py-3">

                <input type="number" id="custom_height" name="resolution_height"
                  placeholder="Height (px)"
                  class="rounded-xl border px-4 py-3">
              </div>


              <p class="text-xs text-gray-500">
                  Used to ensure ad creatives fit perfectly on your screen.
              </p>
          </div>

        </div>

        <!-- Hoarding Location -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
          <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
            <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
            Hoarding Location
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-6">
            <div class="space-y-2">
              <label class="text-sm font-bold text-gray-700">Hoarding Address <span class="text-red-500">*</span></label>
              <input type="text" name="address" placeholder="eg. Opposite Ram Dharam Kanta B43 Sector 7" required 
                class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all">
            </div>

            <div class="space-y-2">
              <label class="text-sm font-bold text-gray-700">Pincode <span class="text-red-500">*</span></label>
              <input type="text" name="pincode" placeholder="eg. 226010" required 
                class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all">
            </div>

            <div class="space-y-2">
              <label class="text-sm font-bold text-gray-700">Locality <span class="text-red-500">*</span></label>
              <input type="text" name="locality" placeholder="e.g. Indira Nagar" required 
                class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all">
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">City <span class="text-red-500">*</span></label>
                <input type="text" name="city" placeholder="eg. Lucknow" class="w-full border border-gray-200 rounded-xl px-4 py-3 outline-none">
              </div>
              <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">State <span class="text-red-500">*</span></label>
                <input type="text" name="state" placeholder="eg. Uttar Pradesh" class="w-full border border-gray-200 rounded-xl px-4 py-3 outline-none">
              </div>
            </div>
          </div>

          <div class="mt-8 space-y-4">
                <!-- Nearby Landmarks -->
            <div class="mt-8 space-y-4">
              <div class="flex items-center justify-between">
                <label class="text-sm font-bold text-gray-700">Nearby Landmarks</label>
                <button type="button" id="addLandmarkBtn" class="bg-[#1A1A1A] text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-black transition-all">
                  + Add another landmark
                </button>
              </div>
              <div class="space-y-3" id="landmarksContainer">
                <input type="text" name="landmarks[]" placeholder="eg. Opposite Ram Dharam Kanta" class="w-full border border-gray-200 rounded-xl px-4 py-3 outline-none">
              </div>
            </div>
          </div>

         <!-- Location Verification Section -->
        <div class="mt-8 bg-[#FBFBFB] rounded-3xl border border-gray-100 p-5 sm:p-6 md:p-8 space-y-6">

          <!-- Section Header -->
          <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
              <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center gap-2">
                <span class="w-2 h-2 bg-[#009A5C] rounded-full"></span>
                Location Verification
              </h3>
              <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-md">
                Confirm the exact physical location of your hoarding using the map.
                This helps customers and admins verify visibility and accuracy.
              </p>
            </div>

            <!-- Mobile-first confirm button -->
            <button
              type="button"
              id="geotagBtn"
              class="w-full sm:w-auto inline-flex justify-center items-center gap-2
                    bg-[#009A5C] text-white text-sm font-bold
                    px-4 sm:px-6 py-3 rounded-xl
                    shadow-sm hover:bg-green-700 active:scale-95 transition">
              📍 Confirm Location
            </button>
          </div>

          <div id="location-error" class="text-xs text-red-500 hidden"></div>

          <!-- Coordinates -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">

            <div class="space-y-1">
              <label class="text-sm font-bold text-gray-700">
                Latitude <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                name="lat"
                id="lat"
                readonly
                required
                placeholder="Auto-filled after confirmation"
                class="w-full bg-gray-50 border border-gray-200 rounded-xl
                      px-4 py-3 text-sm font-mono text-gray-900
                      cursor-not-allowed"
              />
              <p class="text-[11px] text-gray-400">
                Mandatory • Locked after confirmation
              </p>
            </div>

            <div class="space-y-1">
              <label class="text-sm font-bold text-gray-700">
                Longitude <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                name="lng"
                id="lng"
                readonly
                required
                placeholder="Auto-filled after confirmation"
                class="w-full bg-gray-50 border border-gray-200 rounded-xl
                      px-4 py-3 text-sm font-mono text-gray-900
                      cursor-not-allowed"
              />
              <p class="text-[11px] text-gray-400">
                Mandatory • Locked after confirmation
              </p>
            </div>

          </div>

          <!-- Success State -->
          <div
            id="geotagSuccess"
            class="hidden flex items-center gap-2
                  bg-green-50 border border-green-200
                  text-green-700 text-sm
                  px-4 py-3 rounded-xl">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414L9 13.414l4.707-4.707z"
                clip-rule="evenodd" />
            </svg>
            <span>Location confirmed and locked</span>
          </div>

          <!-- Map Preview -->
          <div class="space-y-2">
            <label class="text-sm font-bold text-gray-700">
              Map Preview
            </label>

            <div class="relative rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
              <div id="map" class="w-full h-[300px] sm:h-[320px]"></div>

              <div class="absolute bottom-2 right-2
                          bg-white/90 backdrop-blur
                          text-xs text-gray-600
                          px-3 py-1 rounded-lg shadow">
                Drag pin to adjust
              </div>
            </div>
          </div>

        </div>



        </div>
        <!-- Pricing Details -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-[#009A5C] mb-2 flex items-center">
            <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
            Pricing<span class="text-red-500 ml-1">*</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Price Per Slot -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700">
                        Price Per 10 sec Slot (₹) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="price_per_10_sec_slot"
                        min="1"
                        step="0.01"
                        required
                        placeholder="e.g. 50"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all"
                    />
                    <p class="text-xs text-gray-400">
                        Cost per 10-second slot (recommended for DOOH)
                    </p>
                </div>

                 <!-- Price Per Slot -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700">
                        Price Per 30 sec Slot (₹) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="price_per_30_sec_slot"
                        min="1"
                        step="0.01"
                        required
                        placeholder="e.g. 50"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all"
                    />
                    <p class="text-xs text-gray-400">
                        Cost per 30-second slot (recommended for DOOH)
                    </p>
                </div>
            

            </div>
        </div>

        <!-- Upload Hoarding Media -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
          <h3 class="text-lg font-bold text-[#009A5C] mb-2 flex items-center">
            <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
            Upload Hoarding Media <span class="text-red-500 ml-1">*</span>
          </h3>
          <p class="text-sm text-gray-400 mb-6">High quality photos increase booking chances by 40%.</p>

          <div class="relative group border-2 border-dashed border-[#E5E7EB] rounded-2xl p-12 bg-[#FBFBFB] hover:bg-green-50/30 hover:border-[#009A5C] transition-all flex flex-col items-center justify-center">
            <input type="file" id="mediaUpload" name="media[]" multiple required accept="image/jpeg,image/png,image/jpg,image/webp" class="absolute inset-0 opacity-0 cursor-pointer">
            <div class="w-16 h-16 bg-white shadow-sm rounded-2xl flex items-center justify-center mb-4 text-[#009A5C] group-hover:scale-110 transition-transform">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                </path>
              </svg>
            </div>
            <p class="text-base font-bold text-gray-700">Drop your images here, or <span class="text-[#009A5C]">browse</span></p>
            <p class="text-xs text-gray-400 mt-2">Supports: JPG, JPEG, PNG, WEBP (Max 5MB per image)</p>

            <!-- File Preview -->
            <ul id="filePreview" class="mt-4 text-sm text-gray-600 space-y-1"></ul>
          </div>
        </div>
    </div>
{{-- </div> --}}
<script>
/* ===============================
   MAP + GEOCODING (FIXED)
================================ */

let map, marker;

// Inputs
const addressInput  = document.querySelector('input[name="address"]');
const localityInput = document.querySelector('input[name="locality"]');
const cityInput     = document.querySelector('input[name="city"]');
const stateInput    = document.querySelector('input[name="state"]');
const pincodeInput  = document.querySelector('input[name="pincode"]');

const latInput   = document.getElementById('lat');
const lngInput   = document.getElementById('lng');
const errorBox   = document.getElementById('location-error');
const geotagBtn  = document.getElementById('geotagBtn');

// Default India view
const INDIA_CENTER = [20.5937, 78.9629];

// Init map
map = L.map('map').setView(INDIA_CENTER, 5);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap'
}).addTo(map);

// Init marker
marker = L.marker(INDIA_CENTER, { draggable: true }).addTo(map);

// Marker drag → update lat/lng
marker.on('dragend', () => {
  const pos = marker.getLatLng();
  latInput.value = pos.lat.toFixed(6);
  lngInput.value = pos.lng.toFixed(6);
});

// Clear previous lat/lng
function resetLatLng() {
  latInput.value = '';
  lngInput.value = '';
}

// Build strict address string
function buildAddress() {
  return [
    localityInput.value,
    cityInput.value,
    stateInput.value,
    pincodeInput.value,
    'India'
  ].filter(Boolean).join(', ');
}

// Show error
function showError(msg) {
  errorBox.textContent = msg;
  errorBox.classList.remove('hidden');
}

// Hide error
function hideError() {
  errorBox.textContent = '';
  errorBox.classList.add('hidden');
}

// MAIN GEOCODE FUNCTION (FIXED)
async function geocodeAddress() {
  resetLatLng();
  hideError();

  const address = buildAddress();

  if (address.length < 10) {
    showError('Please enter complete address details.');
    return;
  }

  const url =
    `https://nominatim.openstreetmap.org/search` +
    `?format=json` +
    `&addressdetails=1` +
    `&limit=1` +
    `&countrycodes=in` +
    `&q=${encodeURIComponent(address)}`;

  try {
    const res = await fetch(url, {
      headers: { 'Accept': 'application/json' }
    });

    const data = await res.json();

    if (!data.length) {
      showError('Location not found. Please refine address.');
      return;
    }

    const result = data[0];

    // 🚨 STATE VALIDATION (prevents Wadgaon)
    if (
      result.address?.state &&
      !result.address.state.toLowerCase().includes('uttar pradesh')
    ) {
      showError('Geocode mismatch. Please refine address.');
      return;
    }

    const lat = parseFloat(result.lat);
    const lng = parseFloat(result.lon);

    latInput.value = lat.toFixed(6);
    lngInput.value = lng.toFixed(6);

    map.setView([lat, lng], 15);

    marker.setLatLng([lat, lng]);
    // ✅ MARK LOCATION AS CONFIRMED
    isLocationConfirmed = true;

    // Show success UI
    document.getElementById('geotagSuccess').classList.remove('hidden');

  } catch (e) {
    showError('Unable to fetch location. Try again.');
  }
}

// Explicit geotag action (BEST PRACTICE)
geotagBtn.addEventListener('click', geocodeAddress);
</script>


 

<script>

  // Dynamic Size Preview
  const widthInput = document.getElementById('width');
  const heightInput = document.getElementById('height');
  const unitSelect = document.getElementById('unit');
  const sizePreview = document.getElementById('sizePreview');

  function updateSizePreview() {
    const width = widthInput.value || 0;
    const height = heightInput.value || 0;
    const unit = unitSelect.value === 'sqft' ? 'sq.ft' : 'sq.m';
    sizePreview.value = `${width} x ${height} ${unit}`;
  }

  widthInput.addEventListener('input', updateSizePreview);
  heightInput.addEventListener('input', updateSizePreview);
  unitSelect.addEventListener('change', updateSizePreview);

  // Upload File Preview
  const mediaInput = document.getElementById('mediaUpload');
  const filePreview = document.getElementById('filePreview');

  // Image preview: always shows only currently selected files (not cumulative)
  mediaInput.addEventListener('change', (e) => {
    const files = Array.from(e.target.files);
    filePreview.innerHTML = '';
    files.forEach(file => {
      if (!file.type.match(/^image\/(jpeg|png|jpg|webp)$/)) {
        const li = document.createElement('li');
        li.className = 'text-red-500';
        li.textContent = `${file.name} is not a supported image format.`;
        filePreview.appendChild(li);
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        const li = document.createElement('li');
        li.className = 'text-red-500';
        li.textContent = `${file.name} exceeds 5MB size limit.`;
        filePreview.appendChild(li);
        return;
      }
      const li = document.createElement('li');
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.className = 'inline-block w-16 h-16 object-cover rounded-lg mr-2 border border-gray-200';
      img.onload = function() { URL.revokeObjectURL(this.src); };
      li.appendChild(img);
      li.appendChild(document.createTextNode(file.name));
      filePreview.appendChild(li);
    });
  });
  // In the Blade view (step1.blade.php), enhance JS for file validation

document.querySelector('form').addEventListener('submit', function(e) {
  // --- Latitude/Longitude validation ---
  const latInput = document.getElementById('lat');
  const lngInput = document.getElementById('lng');
  let latitude = latInput ? latInput.value.trim() : '';
  let longitude = lngInput ? lngInput.value.trim() : '';
  let latNum = parseFloat(latitude);
  let lngNum = parseFloat(longitude);

  let locError = document.getElementById('location-submit-error');
  if (!latitude || !longitude || isNaN(latNum) || isNaN(lngNum) || latNum < -90 || latNum > 90 || lngNum < -180 || lngNum > 180) {
    e.preventDefault();
    if (!locError) {
      locError = document.createElement('div');
      locError.id = 'location-submit-error';
      locError.className = 'bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl mt-4';
      document.getElementById('map').parentNode.appendChild(locError);
    }
    locError.innerHTML = '📍 Please confirm and select a valid location on map before proceeding.';
    document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  } else if (locError) {
    locError.remove();
  }

  // --- Media validation ---
  const mediaInput = document.getElementById('mediaUpload');
  let hasError = false;
  let errorMsg = '';
  const files = Array.from(mediaInput.files);
  if (!files.length) {
    hasError = true;
    errorMsg = 'At least one media file is required.';
  } else {
    files.forEach(file => {
      if (!file.type.match(/^image\/(jpeg|png|jpg|webp)$/)) {
        hasError = true;
        errorMsg = 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
      }
      if (file.size > 5 * 1024 * 1024) {
        hasError = true;
        errorMsg = 'Each image must not exceed 5MB.';
      }
    });
  }
  if (hasError) {
    e.preventDefault();
    let error = document.getElementById('media-error');
    if (!error) {
      error = document.createElement('div');
      error.id = 'media-error';
      error.className = 'text-red-500 text-xs mt-2';
      mediaInput.parentNode.appendChild(error);
    }
    error.textContent = errorMsg;
    mediaInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }

  // --- Offer price < base price validation ---
  const baseInput = document.querySelector('[name="base_monthly_price"]');
  const offerInput = document.querySelector('[name="monthly_price"]');
  if (baseInput && offerInput && offerInput.value) {
    const base = parseFloat(baseInput.value);
    const offer = parseFloat(offerInput.value);
    if (!isNaN(base) && !isNaN(offer) && offer >= base) {
      e.preventDefault();
      let error = document.getElementById('offer-error');
      if (!error) {
        error = document.createElement('div');
        error.id = 'offer-error';
        error.className = 'text-red-500 text-xs mt-2';
        offerInput.parentNode.appendChild(error);
      }
      error.textContent = 'Offer price must be less than the base monthly price.';
      offerInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
      let error = document.getElementById('offer-error');
      if (error) error.remove();
    }
  }
});
// --- Instant Offer Price Validation ---
document.addEventListener('DOMContentLoaded', function() {
  const baseInput = document.querySelector('[name="base_monthly_price"]');
  const offerInput = document.querySelector('[name="monthly_price"]');
  if (baseInput && offerInput) {
    function validateOffer() {
      const base = parseFloat(baseInput.value);
      const offer = parseFloat(offerInput.value);
      let error = document.getElementById('offer-error');
      if (!isNaN(base) && !isNaN(offer) && offer >= base) {
        if (!error) {
          error = document.createElement('div');
          error.id = 'offer-error';
          error.className = 'text-red-500 text-xs mt-2';
          offerInput.parentNode.appendChild(error);
        }
        error.textContent = 'Offer price must be less than the base monthly price.';
      } else {
        if (error) error.remove();
      }
    }
    offerInput.addEventListener('input', validateOffer);
    baseInput.addEventListener('input', validateOffer);
  }
});
// Landmark dynamic addition
const addLandmarkBtn = document.getElementById('addLandmarkBtn');
const landmarksContainer = document.getElementById('landmarksContainer');

addLandmarkBtn.addEventListener('click', function() {
  const input = document.createElement('input');
  input.type = 'text';
  input.name = 'landmarks[]';
  input.placeholder = 'Enter landmark';
  input.className = 'w-full border border-gray-200 rounded-xl px-4 py-3 outline-none mt-2';
  landmarksContainer.appendChild(input);
});


</script>
<script>
const resolutionSelect = document.getElementById('resolution_type');
const customBox = document.getElementById('custom_resolution');
const customWidth = document.getElementById('custom_width');
const customHeight = document.getElementById('custom_height');

resolutionSelect.addEventListener('change', function () {
  if (this.value === 'custom') {
    customBox.classList.remove('hidden');
    customWidth.required = true;
    customHeight.required = true;
  } else {
    customBox.classList.add('hidden');
    customWidth.required = false;
    customHeight.required = false;

    // Auto-split preset resolution
    if (this.value.includes('x')) {
      const [w, h] = this.value.split('x');
      customWidth.value = w;
      customHeight.value = h;
    }
  }
});
</script>
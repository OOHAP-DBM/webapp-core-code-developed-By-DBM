
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
{{-- <div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-6"> --}}
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <div class="p-8 space-y-8">
    <!-- Hoarding Details -->
    {{-- @dump($draft) --}}
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
      <h3 class="text-lg font-bold text-[#009A5C] mb-6 flex items-center">
        <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
        Hoarding Details
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-6">
        <!-- Hoarding Type -->
        <div class="space-y-2">
          <label class="text-sm font-bold text-gray-700">Hoarding Type <span class="text-red-500">*</span></label>
          <div class="w-full bg-[#0094FF] border border-[#0094FF] rounded-xl px-4 py-3 text-white font-bold">
            OOH (Out-of-Home)
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
                <option value="{{ $cat->value }}" {{ old('category', $draft->hoarding->category ?? '') == $cat->value ? 'selected' : '' }}>
                  {{ $cat->value }}
                </option>
              @endforeach
            @endif
          </select>
        </div>
      </div>

      <!-- Screen Size -->
      <div class="mt-8">
        <label class="text-sm font-bold text-gray-700 mb-1 block">Hoarding Size</label>
        <div class="grid grid-cols-4 gap-4 items-end">
          <!-- Unit -->
          <div class="space-y-1">
            <label class="text-xs font-bold text-gray-500">Unit</label>
            <select id="unit" name="measurement_unit" required class="w-full bg-white border border-gray-200 rounded-lg px-3 py-3 outline-none">
              <option value="sqft" {{ old('measurement_unit', $draft->measurement_unit ?? 'sqft') == 'sqft' ? 'selected' : '' }}>Sqft</option>
              <option value="sqm" {{ old('measurement_unit', $draft->measurement_unit ?? 'sqft') == 'sqm' ? 'selected' : '' }}>Sqm</option>
            </select>
          </div>

          <!-- Width -->
          <div class="space-y-1">
            <label class="text-xs font-bold text-gray-500">Width <span class="text-red-500">*</span></label>
            <input type="number" id="width" name="width" placeholder="eg.500" required 
              value="{{ old('width', $draft->width ?? '') }}"
              class="w-full border border-gray-200 rounded-lg px-3 py-3 outline-none focus:border-[#009A5C]">
          </div>

          <!-- Height -->
          <div class="space-y-1">
            <label class="text-xs font-bold text-gray-500">Height <span class="text-red-500">*</span></label>
            <input type="number" id="height" name="height" placeholder="eg.300" required 
              value="{{ old('height', $draft->height ?? '') }}" 
              class="w-full border border-gray-200 rounded-lg px-3 py-3 outline-none focus:border-[#009A5C]">
          </div>

          <!-- Size Preview (READ-ONLY, NO NAME) -->
          <div class="space-y-1">
            <label class="text-xs font-bold text-gray-500">Size Preview</label>
            <input type="text" id="sizePreview" readonly 
              placeholder="Auto-calculated"
              class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-3 cursor-not-allowed text-gray-600">
          </div>
        </div>
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
            value="{{ old('address', $draft->hoarding->address ?? '') }}"
            class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all">
        </div>

        <div class="space-y-2">
          <label class="text-sm font-bold text-gray-700">Pincode <span class="text-red-500">*</span></label>
          <input type="text" name="pincode" 
            value="{{ old('pincode', $draft->hoarding->pincode ?? '') }}" 
            placeholder="eg. 226010" required 
            class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all">
        </div>

        <div class="space-y-2">
          <label class="text-sm font-bold text-gray-700">Locality <span class="text-red-500">*</span></label>
          <input type="text" name="locality" 
            value="{{ old('locality', $draft->hoarding->locality ?? '') }}" 
            placeholder="eg. Indira Nagar" required 
            class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <label class="text-sm font-bold text-gray-700">City</label>
            <input type="text" name="city" 
              value="{{ old('city', $draft->hoarding->city ?? '') }}" 
              placeholder="eg. Lucknow" 
              class="w-full border border-gray-200 rounded-xl px-4 py-3 outline-none">
          </div>
          <div class="space-y-2">
            <label class="text-sm font-bold text-gray-700">State</label>
            <input type="text" name="state" 
              value="{{ old('state', $draft->hoarding->state ?? '') }}" 
              placeholder="eg. Uttar Pradesh" 
              class="w-full border border-gray-200 rounded-xl px-4 py-3 outline-none">
          </div>
        </div>
      </div>

      <div class="mt-8 space-y-4">
        <!-- Nearby Landmarks -->
        <div class="flex items-center justify-between">
          <label class="text-sm font-bold text-gray-700">Nearby Landmarks</label>
          <button type="button" id="addLandmarkBtn" class="bg-[#1A1A1A] text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-black transition-all">
            + Add another landmark
          </button>
        </div>
        <div class="space-y-3" id="landmarksContainer">
          @php
            $landmarks = old('landmarks', isset($draft->hoarding->landmarks) ? (is_array($draft->hoarding->landmarks) ? $draft->hoarding->landmarks : json_decode($draft->hoarding->landmarks, true)) : []);
            if (empty($landmarks)) $landmarks = [''];
          @endphp
          @foreach($landmarks as $index => $landmark)
            <input type="text" name="landmarks[]" 
              value="{{ $landmark }}" 
              placeholder="eg. Opposite Ram Dharam Kanta" 
              class="w-full border border-gray-200 rounded-xl px-4 py-3 outline-none">
          @endforeach
        </div>
      </div>

      <!-- Location Verification Section -->
      <div class="mt-8 bg-[#FBFBFB] rounded-3xl border border-gray-100 p-5 sm:p-6 md:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
          <div>
            <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center gap-2">
              <span class="w-2 h-2 bg-[#009A5C] rounded-full"></span>
              Location Verification
            </h3>
            <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-md">
              Confirm the exact physical location of your hoarding using the map.
            </p>
          </div>

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
              value="{{ old('lat', $draft->hoarding->latitude ?? '') }}"
              placeholder="Auto-filled after confirmation"
              class="w-full bg-gray-50 border border-gray-200 rounded-xl
                    px-4 py-3 text-sm font-mono text-gray-900
                    cursor-not-allowed"
            />
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
              value="{{ old('lng', $draft->hoarding->longitude ?? '') }}" 
              placeholder="Auto-filled after confirmation"
              class="w-full bg-gray-50 border border-gray-200 rounded-xl
                    px-4 py-3 text-sm font-mono text-gray-900
                    cursor-not-allowed"
            />
          </div>
        </div>

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

        <div class="space-y-2">
          <label class="text-sm font-bold text-gray-700">Map Preview</label>
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
        <!-- Monthly Base Price -->
        <div class="space-y-2">
          <label class="text-sm font-bold text-gray-700">
            Monthly Base Price (₹) <span class="text-red-500">*</span>
          </label>
          <input
            type="number"
            name="base_monthly_price"
            value="{{ old('base_monthly_price', $draft->hoarding->base_monthly_price ?? '') }}"
            min="1"
            step="0.01"
            required
            placeholder="e.g. 50,000"
            class="w-full border border-gray-200 rounded-xl px-4 py-3
                  focus:border-[#009A5C] outline-none transition-all"
          />
          <p class="text-xs text-gray-400">
            Standard monthly hoarding price (before discount)
          </p>
        </div>

        <!-- Monthly Offer Price -->
        <div class="space-y-2">
          <label class="text-sm font-bold text-gray-700">
            Monthly Discounted Price (₹)
          </label>
          <input
            type="number"
            name="monthly_price"
            min="1"
            step="0.01"
            value="{{ old('monthly_price', $draft->hoarding->monthly_price ?? '') }}"
            placeholder="Optional discounted price"
            class="w-full border border-gray-200 rounded-xl px-4 py-3
                  focus:border-[#009A5C] outline-none transition-all"
          />
          <p class="text-xs text-gray-400">
            Discounted price (optional)
          </p>
        </div>
      </div>
    </div>

    <!-- Upload Hoarding Media -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
               <h3 class="text-lg font-bold text-[#009A5C] mb-2 flex items-center">
                <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
                Upload Media<span class="text-red-500 ml-1">*</span>
              </h3>
              <p class="text-sm text-gray-500 mb-4">
                  Upload high-quality images or videos (Max 10 files).
              </p>

              {{-- Existing Media --}}
              <div class="flex flex-wrap gap-3 mb-4" id="existingMediaPreview">
                  @if(isset($draft) && $draft->hoarding->oohMedia && $draft->hoarding->oohMedia->count())
                      @foreach($draft->hoarding->oohMedia as $media)
                          <div class="relative w-28 h-28 rounded-lg overflow-hidden border bg-gray-50">
                              @if(Str::startsWith( $media->mime_type, 'image'))
                                  <img src="{{ asset('storage/'.$media->file_path) }}" ...>
                              @elseif(Str::startsWith($media->mime_type, 'video'))
                                  <video src="{{ asset('storage/'.$media->file_path) }}" ...></video>
                              @endif

                              <button type="button"
                                      onclick="removeExistingMedia({{ $media->id }})"
                                      class="absolute top-1 right-1 bg-white rounded-full p-1 shadow text-red-600 hover:bg-red-100">
                                  ✕
                              </button>
                          </div>
                      @endforeach
                  @endif
              </div>

              <input type="hidden" name="deleted_media_ids" id="deletedMediaIds">

              {{-- New Media Preview --}}
              <div id="newMediaPreview" class="flex flex-wrap gap-3 mb-4"></div>

              {{-- Styled File Input --}}
              <label for="mediaInput"
                    class="flex items-center justify-between w-full px-4 py-3 border border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition">
                  <span class="text-sm text-gray-600">Choose files</span>
                  <span class="text-xs text-gray-400">Browse</span>
              </label>

              <input
                  id="mediaInput"
                  type="file"
                  name="media[]"
                  multiple
                  accept="image/jpeg,image/png,image/webp,video/mp4,video/webm"
                  class="hidden"
                  @if(!(isset($draft) && $draft->hoarding->oohMedia && $draft->hoarding->oohMedia->count())) required @endif
              >

              <p class="text-xs text-gray-400 mt-2">
                  Supported: JPG, PNG, WEBP, MP4, WEBM • Max 10 files • 5MB each
              </p>
          </div>
  </div>
{{-- </div> --}}

<script>
/* MAP + GEOCODING */
let map, marker;

const addressInput  = document.querySelector('input[name="address"]');
const localityInput = document.querySelector('input[name="locality"]');
const cityInput     = document.querySelector('input[name="city"]');
const stateInput    = document.querySelector('input[name="state"]');
const pincodeInput  = document.querySelector('input[name="pincode"]');
const latInput   = document.getElementById('lat');
const lngInput   = document.getElementById('lng');
const errorBox   = document.getElementById('location-error');
const geotagBtn  = document.getElementById('geotagBtn');

const INDIA_CENTER = [20.5937, 78.9629];

map = L.map('map').setView(INDIA_CENTER, 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap'
}).addTo(map);

marker = L.marker(INDIA_CENTER, { draggable: true }).addTo(map);

marker.on('dragend', () => {
  const pos = marker.getLatLng();
  latInput.value = pos.lat.toFixed(6);
  lngInput.value = pos.lng.toFixed(6);
});

// Restore map if lat/lng exist
document.addEventListener('DOMContentLoaded', function() {
  const savedLat = latInput.value;
  const savedLng = lngInput.value;
  
  if (savedLat && savedLng) {
    const lat = parseFloat(savedLat);
    const lng = parseFloat(savedLng);
    if (!isNaN(lat) && !isNaN(lng)) {
      map.setView([lat, lng], 15);
      marker.setLatLng([lat, lng]);
      document.getElementById('geotagSuccess').classList.remove('hidden');
    }
  }
  
  // Auto-calculate size preview
  updateSizePreview();
  
  // Initialize upload count
  updateUploadCount();
});

function buildAddress() {
  return [
    localityInput.value,
    cityInput.value,
    stateInput.value,
    pincodeInput.value,
    'India'
  ].filter(Boolean).join(', ');
}

function showError(msg) {
  errorBox.textContent = msg;
  errorBox.classList.remove('hidden');
}

function hideError() {
  errorBox.textContent = '';
  errorBox.classList.add('hidden');
}

async function geocodeAddress() {
  hideError();
  const address = buildAddress();

  if (address.length < 10) {
    showError('Please enter complete address details.');
    return;
  }

  const url = `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=1&countrycodes=in&q=${encodeURIComponent(address)}`;

  try {
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();

    if (!data.length) {
      showError('Location not found. Please refine address.');
      return;
    }

    const result = data[0];
    const lat = parseFloat(result.lat);
    const lng = parseFloat(result.lon);

    latInput.value = lat.toFixed(6);
    lngInput.value = lng.toFixed(6);
    map.setView([lat, lng], 15);
    marker.setLatLng([lat, lng]);
    document.getElementById('geotagSuccess').classList.remove('hidden');
  } catch (e) {
    showError('Unable to fetch location. Try again.');
  }
}

geotagBtn.addEventListener('click', geocodeAddress);

// Size Preview
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

/* ===============================================
   MEDIA UPLOAD WITH VIDEO SUPPORT
   - Accumulates files using DataTransfer
   - Supports one-by-one and batch uploads  
   - Enforces 10-file limit (existing + new)
   - Video duration validation (max 30s)
   - Horizontal preview layout with remove buttons
   =============================================== */

let deletedMediaIds = [];
let newFiles = [];
const maxFiles = 10;
const maxFileSize = 5 * 1024 * 1024;

const mediaInput = document.getElementById('mediaInput');
const newMediaPreview = document.getElementById('newMediaPreview');
const existingMediaPreview = document.getElementById('existingMediaPreview');
const deletedMediaIdsInput = document.getElementById('deletedMediaIds');

function renderNewPreviews() {
  newMediaPreview.innerHTML = '';
  newFiles.forEach((file, idx) => {
    const url = URL.createObjectURL(file);
    let el;
    if (file.type.startsWith('image')) {
      el = `<div class='relative w-28 h-28 rounded overflow-hidden border bg-gray-50 flex items-center justify-center'>
        <img src='${url}' class='object-cover w-full h-full'>
        <button type='button' class='absolute top-1 right-1 bg-white rounded-full shadow p-1 text-red-600 hover:bg-red-100' onclick='removeNewFile(${idx})' title='Remove'>
          <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12' /></svg>
        </button>
      </div>`;
    } else if (file.type.startsWith('video')) {
      el = `<div class='relative w-28 h-28 rounded overflow-hidden border bg-gray-50 flex items-center justify-center'>
        <video src='${url}' controls class='object-cover w-full h-full'></video>
        <button type='button' class='absolute top-1 right-1 bg-white rounded-full shadow p-1 text-red-600 hover:bg-red-100' onclick='removeNewFile(${idx})' title='Remove'>
          <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12' /></svg>
        </button>
      </div>`;
    }
    newMediaPreview.insertAdjacentHTML('beforeend', el);
  });
}

function removeNewFile(idx) {
  newFiles.splice(idx, 1);
  updateInputFiles();
  renderNewPreviews();
}

function removeExistingMedia(id) {
  deletedMediaIds.push(id);
  deletedMediaIdsInput.value = deletedMediaIds.join(',');
  const el = existingMediaPreview.querySelector(`[onclick*='removeExistingMedia(${id})']`).parentElement;
  if (el) el.remove();
}

function updateInputFiles() {
  // Sync newFiles to input
  const dt = new DataTransfer();
  newFiles.forEach(f => dt.items.add(f));
  mediaInput.files = dt.files;
}

mediaInput.addEventListener('change', function(e) {
  const files = Array.from(e.target.files);
  for (const file of files) {
    if (newFiles.length >= maxFiles) break;
    if (!['image/jpeg','image/png','image/webp','video/mp4','video/webm'].includes(file.type)) continue;
    if (file.size > maxFileSize) continue;
    newFiles.push(file);
  }
  updateInputFiles();
  renderNewPreviews();
});

// Landmark Addition
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

// Form Validation
document.querySelector('form').addEventListener('submit', function(e) {
  const latitude = latInput.value.trim();
  const longitude = lngInput.value.trim();
  
  if (!latitude || !longitude) {
    e.preventDefault();
    showError('Please confirm location on map before proceeding.');
    document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }
});
</script>
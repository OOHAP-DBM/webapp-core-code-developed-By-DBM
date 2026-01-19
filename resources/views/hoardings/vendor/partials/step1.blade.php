
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
{{-- <div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-6"> --}}
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <div class="p-8 space-y-8">
    <!-- Hoarding Details -->
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
        Upload Hoarding Media <span class="text-red-500 ml-1">*</span>
      </h3>
      <p class="text-sm text-gray-400 mb-6">High quality photos increase booking chances by 40%.</p>

      @if(isset($draft) && $draft->hoarding && $draft->hoarding->getMedia('hoarding_media')->count() > 0)
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
          <h4 class="text-sm font-bold text-blue-900 mb-3 flex items-center justify-between">
            <span>Existing Media ({{ $draft->hoarding->getMedia('hoarding_media')->count() }})</span>
            <span class="text-xs font-normal text-blue-700">Click × to remove</span>
          </h4>
          <div id="existingMediaContainer" class="flex flex-wrap gap-3">
            @foreach($draft->hoarding->getMedia('hoarding_media') as $media)
              <div class="relative group" data-media-id="{{ $media->id }}">
                <img src="{{ $media->getUrl('thumb') }}" alt="Hoarding" 
                  class="w-24 h-24 object-cover rounded-lg border-2 border-blue-300 shadow-sm">
                <button type="button" 
                  onclick="removeExistingMedia({{ $media->id }})"
                  class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-red-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
                <input type="hidden" name="existing_media[]" value="{{ $media->id }}">
              </div>
            @endforeach
          </div>
        </div>
      @endif

      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-600">
            <span id="existingMediaCount" class="font-bold text-blue-600">{{ isset($draft) && $draft->hoarding ? $draft->hoarding->getMedia('hoarding_media')->count() : 0 }}</span> existing + 
            <span id="uploadCount" class="font-bold text-[#009A5C]">0</span> new = 
            <span id="totalCount" class="font-bold">0</span> / 10 total
          </p>
        </div>

        @if(isset($draft) && $draft->hoarding && $draft->hoarding->getMedia('hoarding_media')->count() > 0)
          <div class="mb-4 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <h4 class="text-sm font-semibold text-blue-900 mb-3">Existing Media (<span id="existingCountLabel">{{ $draft->hoarding->getMedia('hoarding_media')->count() }}</span>):</h4>
            <div id="existingMediaContainer" class="flex flex-wrap gap-3">
              @foreach($draft->hoarding->getMedia('hoarding_media') as $media)
                <div class="relative group" data-media-id="{{ $media->id }}">
                  <img src="{{ $media->getUrl('thumb') }}" alt="Media" class="w-24 h-24 object-cover rounded-lg border-2 border-blue-300">
                  <button type="button" onclick="removeExistingMedia({{ $media->id }})" 
                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-red-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                  <input type="hidden" name="existing_media_ids[]" value="{{ $media->id }}">
                </div>
              @endforeach
            </div>
          </div>
        @endif

        <div class="relative group border-2 border-dashed border-[#E5E7EB] rounded-2xl p-8 bg-[#FBFBFB] hover:bg-green-50/30 hover:border-[#009A5C] transition-all flex flex-col items-center justify-center">
          <input type="file" id="mediaUpload" name="media[]" multiple 
            {{ !isset($draft) || !$draft->hoarding || $draft->hoarding->getMedia('hoarding_media')->count() == 0 ? 'required' : '' }}
            accept="image/jpeg,image/png,image/jpg,image/webp,video/mp4,video/webm" 
            class="absolute inset-0 opacity-0 cursor-pointer">
          <div class="w-16 h-16 bg-white shadow-sm rounded-2xl flex items-center justify-center mb-4 text-[#009A5C] group-hover:scale-110 transition-transform">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
              </path>
            </svg>
          </div>
          <p class="text-base font-bold text-gray-700">Drop your media here, or <span class="text-[#009A5C]">browse</span></p>
          <p class="text-xs text-gray-400 mt-2">Images: JPG, PNG, WEBP (Max 5MB) • Videos: MP4, WEBM (Max 10MB, 30s)</p>
        </div>

        <div id="filePreview" class="flex flex-wrap gap-3 mt-4"></div>
      </div>
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

const mediaInput = document.getElementById('mediaUpload');
const filePreview = document.getElementById('filePreview');
const uploadCount = document.getElementById('uploadCount');
const existingMediaCount = document.getElementById('existingMediaCount');
const slotsAvailable = document.getElementById('slotsAvailable');

const MAX_FILES = 10;
let filesArray = []; // Use regular array instead of DataTransfer for reliability
let existingCount = existingMediaCount ? parseInt(existingMediaCount.textContent) || 0 : 0;

// Update counters
function updateUploadCount() {
  const newFilesCount = filesArray.length;
  const totalCount = existingCount + newFilesCount;
  
  uploadCount.textContent = `${newFilesCount}/${MAX_FILES - existingCount} selected`;
  
  if (slotsAvailable) {
    slotsAvailable.textContent = MAX_FILES - totalCount;
  }
  
  // Disable input if limit reached
  if (totalCount >= MAX_FILES) {
    mediaInput.disabled = true;
    mediaInput.parentElement.classList.add('opacity-50', 'pointer-events-none');
  } else {
    mediaInput.disabled = false;
    mediaInput.parentElement.classList.remove('opacity-50', 'pointer-events-none');
  }
}

// Validate video duration
function validateVideo(file) {
  return new Promise((resolve, reject) => {
    const video = document.createElement('video');
    video.preload = 'metadata';
    
    video.onloadedmetadata = function() {
      window.URL.revokeObjectURL(video.src);
      const duration = video.duration;
      
      if (duration > 30) {
        reject(`"${file.name}" exceeds 30 seconds limit (${Math.round(duration)}s detected)`);
      } else {
        resolve(true);
      }
    };
    
    video.onerror = function() {
      window.URL.revokeObjectURL(video.src);
      reject(`Cannot validate video "${file.name}"`);
    };
    
    video.src = URL.createObjectURL(file);
  });
}

// Remove file from array by index
function removePreviewImage(index) {
  console.log('Removing file at index:', index, 'Total files before:', filesArray.length);
  
  // Remove from array
  filesArray.splice(index, 1);
  
  console.log('Total files after:', filesArray.length);
  
  // Sync with input element
  syncFilesToInput();
  
  // Re-render previews
  renderPreviews();
}

// Sync filesArray to input element using DataTransfer
function syncFilesToInput() {
  const dt = new DataTransfer();
  filesArray.forEach(file => {
    dt.items.add(file);
  });
  mediaInput.files = dt.files;
}

// Render all preview thumbnails
function renderPreviews() {
  filePreview.innerHTML = '';
  
  if (filesArray.length === 0) {
    updateUploadCount();
    return;
  }
  
  filesArray.forEach((file, index) => {
    const isVideo = file.type.startsWith('video/');
    
    const div = document.createElement('div');
    div.className = 'relative group';
    div.setAttribute('data-file-index', index);
    
    // Create media element (video or image)
    if (isVideo) {
      const video = document.createElement('video');
      video.src = URL.createObjectURL(file);
      video.className = 'w-24 h-24 object-cover rounded-lg border-2 border-gray-200 shadow-sm';
      video.muted = true;
      video.preload = 'metadata';
      
      // Play icon overlay
      const playIcon = document.createElement('div');
      playIcon.className = 'absolute inset-0 flex items-center justify-center pointer-events-none';
      playIcon.innerHTML = '<svg class="w-8 h-8 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>';
      
      div.appendChild(video);
      div.appendChild(playIcon);
      
      video.onloadedmetadata = function() {
        URL.revokeObjectURL(this.src);
      };
    } else {
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.className = 'w-24 h-24 object-cover rounded-lg border-2 border-gray-200 shadow-sm';
      img.onload = function() { 
        URL.revokeObjectURL(this.src); 
      };
      div.appendChild(img);
    }
    
    // Remove button with data attribute
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-red-600';
    removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
    removeBtn.setAttribute('data-index', index);
    removeBtn.addEventListener('click', function() {
      const idx = parseInt(this.getAttribute('data-index'));
      removePreviewImage(idx);
    });
    
    // Filename label
    const fileName = document.createElement('p');
    fileName.className = 'text-xs text-gray-500 mt-1 truncate max-w-[96px]';
    fileName.textContent = file.name;
    
    div.appendChild(removeBtn);
    div.appendChild(fileName);
    filePreview.appendChild(div);
  });
  
  updateUploadCount();
}

// Handle file selection (supports one-by-one and batch uploads)
mediaInput.addEventListener('change', async function(e) {
  const newFiles = Array.from(e.target.files);
  let errorMessages = [];
  
  console.log('Files selected:', newFiles.length, 'Current files in array:', filesArray.length);
  
  // Reset input value FIRST to allow re-selecting same file
  e.target.value = '';
  
  // Check if adding new files exceeds limit
  const totalAfterAdd = existingCount + filesArray.length + newFiles.length;
  if (totalAfterAdd > MAX_FILES) {
    alert(`Cannot add ${newFiles.length} file(s). Maximum ${MAX_FILES} files allowed.\nCurrent: ${existingCount} existing + ${filesArray.length} selected = ${existingCount + filesArray.length}/${MAX_FILES}`);
    return;
  }
  
  // Validate and add each file
  for (const file of newFiles) {
    const isImage = /^image\/(jpeg|png|jpg|webp)$/i.test(file.type);
    const isVideo = /^video\/(mp4|webm)$/i.test(file.type);
    
    // Check format
    if (!isImage && !isVideo) {
      errorMessages.push(`"${file.name}" - unsupported format (only JPG, PNG, WEBP, MP4, WEBM)`);
      continue;
    }
    
    // Check image size
    if (isImage && file.size > 5 * 1024 * 1024) {
      errorMessages.push(`"${file.name}" - image exceeds 5MB limit`);
      continue;
    }
    
    // Check video size
    if (isVideo && file.size > 10 * 1024 * 1024) {
      errorMessages.push(`"${file.name}" - video exceeds 10MB limit`);
      continue;
    }
    
    // Validate video duration
    if (isVideo) {
      try {
        await validateVideo(file);
      } catch (error) {
        errorMessages.push(error);
        continue;
      }
    }
    
    // Check for duplicates
    const isDuplicate = filesArray.some(
      existingFile => existingFile.name === file.name && existingFile.size === file.size
    );
    
    if (isDuplicate) {
      errorMessages.push(`"${file.name}" - already selected`);
      continue;
    }
    
    // Add to array (accumulates files)
    console.log('Adding file to array:', file.name);
    filesArray.push(file);
  }
  
  console.log('Total files in array after adding:', filesArray.length);
  
  // Sync array to input element
  syncFilesToInput();
  
  // Show errors if any
  if (errorMessages.length > 0) {
    alert('Some files were not added:\n\n' + errorMessages.join('\n'));
  }
  
  // Render previews
  renderPreviews();
});

// Remove existing media function (called from blade template)
function removeExistingMedia(mediaId) {
  if (!confirm('Remove this media?')) return;
  
  const container = document.querySelector(`div[data-media-id="${mediaId}"]`);
  if (!container) {
    console.error('Media container not found:', mediaId);
    return;
  }
  
  // Remove the container (includes hidden input)
  container.remove();
  
  // Decrement existing count
  existingCount--;
  if (existingMediaCount) {
    existingMediaCount.textContent = existingCount;
  }
  
  // Update counters
  updateUploadCount();
  
  // Hide existing media section if all removed
  const existingContainer = document.getElementById('existingMediaContainer');
  if (existingContainer && existingContainer.children.length === 0) {
    existingContainer.closest('.mb-6').remove();
  }
}

// Initialize on page load
updateUploadCount();

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
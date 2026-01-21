<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
{{-- <div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-6"> --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @php
        $isEdit = isset($draft) && $draft->hoarding_id;
        $hoarding = $isEdit ? $draft->hoarding : null;
        $resolutionValue = ($draft?->resolution_width && $draft?->resolution_height)
            ? $draft->resolution_width.'x'.$draft->resolution_height
            : '';
        $landmarks = $hoarding?->landmark
          ? json_decode($hoarding->landmark, true)
          : [''];
          $existingMedia = $draft->media ?? collect();
       
          // Ensure we are looking at the hoarding relationship of the screen/draft
          $source = $draft->hoarding ?? null;
          $isWeeklyEnabled = old('enable_weekly_booking', $source->enable_weekly_booking ?? false);

    @endphp

    <div class="md:p-8 md:space-y-8">
      {{-- @dump($draft) --}}
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
                       <option value="{{ $cat->value }}"
                          {{ old('category', $screen->hoarding->category ?? '') == $cat->value ? 'selected' : '' }}>
                          {{ $cat->value }}
                        </option>
                  @endforeach
                @endif
              </select>
            </div>

            <!-- Screen Type -->
            <div class="md:col-span-2 space-y-2 mt-2">
              <label class="text-sm font-bold text-gray-700">Screen Type <span class="text-red-500">*</span></label>
              <select name="screen_type" required
                class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none appearance-none bg-[url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%226b7280%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>')] bg-[length:20px] bg-[right_1rem_center] bg-no-repeat">
                <option value="LED" {{ old('screen_type', $screen->hoarding->screen_type ?? '') == 'LED' ? 'selected' : '' }}>LED</option>
                <option value="LCD" {{ old('screen_type', $screen->hoarding->screen_type ?? '') == 'LCD' ? 'selected' : '' }}>LCD</option>
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
                 <option value="sqft" {{ old('measurement_unit', $draft->measurement_unit ?? '') == 'sqft' ? 'selected' : '' }}>Sqft</option>
                  <option value="sqm" {{ old('measurement_unit', $draft->measurement_unit ?? '') == 'sqm' ? 'selected' : '' }}>Sqm</option>
                </select>
              </div>

              <!-- Width -->
              <div class="space-y-1">
                <label class="text-xs font-bold text-gray-500">Screen Width <span class="text-red-500">*</span></label>
                <input type="number" id="width" name="width"   value="{{ old('width', $draft->width ?? '') }}" required  placeholder="eg. 500" required min="1" class="w-full border border-gray-200 rounded-lg px-3 py-3 outline-none focus:border-[#009A5C]">
              </div>

              <!-- Height -->
              <div class="space-y-1">
                <label class="text-xs font-bold text-gray-500">Screen Height <span class="text-red-500">*</span></label>
                <input type="number" id="height" name="height" value="{{ old('height', $draft->height ?? '') }}" placeholder="eg.300" required min="1" class="w-full border border-gray-200 rounded-lg px-3 py-3 outline-none focus:border-[#009A5C]">
              </div>

              <!-- Size Preview -->
              <div class="space-y-1">
                <label class="text-xs font-bold text-gray-500">Size Preview</label>
                <input type="text" id="sizePreview" value=" " readonly class="w-full bg-[#E5E9F2] border-none rounded-lg px-3 py-3 text-gray-700 font-medium outline-none">
              </div>
            </div>
          </div>
          <div class="space-y-4 mt-2">
              <label class="text-sm font-semibold text-gray-700">
                  Screen Resolution <span class="text-red-500">*</span>
              </label>

            <select id="resolution_type" name="resolution_type" required>
              <option value="">Select resolution</option>
              <option value="1920x1080" {{ $resolutionValue==='1920x1080'?'selected':'' }}>Full HD(1920x1080)</option>
              <option value="3840x2160" {{ $resolutionValue==='3840x2160'?'selected':'' }}>4K(3840x2160)</option>
              <option value="1280x720"  {{ $resolutionValue==='1280x720'?'selected':'' }}>HD(1280x720)</option>
              <option value="custom"
                {{ !in_array($resolutionValue,['1920x1080','3840x2160','1280x720']) && $resolutionValue ? 'selected':'' }}>
                Custom
              </option>
            </select>

            <div id="custom_resolution"
              class="{{ !in_array($resolutionValue,['1920x1080','3840x2160','1280x720']) ? '' : 'hidden' }}">
              <input type="number" name="resolution_width"
                value="{{ $draft?->resolution_width }}">
              <input type="number" name="resolution_height"
                value="{{ $draft?->resolution_height }}">
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
              <input name="address" value="{{ old('address', $hoarding?->address) }}" required
                class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all">
            </div>

            <div class="space-y-2">
              <label class="text-sm font-bold text-gray-700">Pincode <span class="text-red-500">*</span></label>
              <input type="text" name="pincode" value="{{ old('pincode', $hoarding?->pincode) }}" placeholder="eg. 226010" required 
                class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all">
            </div>

            <div class="space-y-2">
              <label class="text-sm font-bold text-gray-700">Locality <span class="text-red-500">*</span></label>
              <input type="text" name="locality" value="{{ old('locality', $hoarding?->locality) }}" placeholder="e.g. Indira Nagar" required 
                class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all">
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">City <span class="text-red-500">*</span></label>
                <input type="text" name="city" value="{{ old('city', $hoarding?->city) }}" placeholder="eg. Lucknow" class="w-full border border-gray-200 rounded-xl px-4 py-3 outline-none">
              </div>
              <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">State <span class="text-red-500">*</span></label>
                <input type="text" name="state" value="{{ old('state', $hoarding?->state) }}" placeholder="eg. Uttar Pradesh" class="w-full border border-gray-200 rounded-xl px-4 py-3 outline-none">
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
                  @foreach($landmarks as $lm)
                    <input type="text" name="landmarks[]" value="{{ $lm }}"
                      class="w-full border rounded-xl px-4 py-3">
                  @endforeach
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
                value="{{ old('lat', $hoarding?->latitude) }}"
                required
                placeholder="Auto-filled after confirmation"
                class="w-full  border border-gray-200 rounded-xl
                      px-4 py-3 text-sm font-mono text-gray-900
                      "
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
                value="{{ old('lng', $hoarding?->longitude) }}"
                required
                placeholder="Auto-filled after confirmation"
                class="w-full  border border-gray-200 rounded-xl
                      px-4 py-3 text-sm font-mono text-gray-900
                     "
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


        </div>
      <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
          <h3 class="text-lg font-bold text-[#009A5C] mb-6 flex items-center">
              <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
              Pricing
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                  <label class="text-sm font-bold text-gray-700">
                      Price Per sec  (₹) <span class="text-red-500">*</span>
                  </label>
                  <input type="number" name="price_per_slot" min="1" step="0.01" required
                      value="{{ old('price_per_slot', $draft?->price_per_slot) }}"
                      placeholder="e.g. 50"
                      class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all" />
                  <p class="text-xs text-gray-400">Cost per second (recommended for DOOH)</p>
              </div>

              <div class="space-y-2">
                  <label class="text-sm font-semibold text-gray-600">Enable Weekly Booking?</label>
                  <div class="flex items-center gap-4">
                      {{-- Hidden input handles the "unchecked" state --}}
                      <input type="hidden" name="enable_weekly_booking" value="0">
                      <input type="checkbox" id="enable_weekly_booking" name="enable_weekly_booking" value="1" 
                          class="w-5 h-5 rounded border-gray-300 text-[#009A5C] cursor-pointer" 
                          {{ $isWeeklyEnabled ? 'checked' : '' }}>
                      <span class="text-xs text-gray-500">Allow customers to book for weekly durations</span>
                  </div>
              </div>

              {{-- This section visibility is controlled by PHP on load and JS on change --}}
              <div id="weekly-section" class="{{ $isWeeklyEnabled ? 'grid' : 'hidden' }} grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                  @foreach(['1', '2', '3'] as $week)
                      <div class="space-y-2">
                          <label class="text-sm font-semibold text-gray-700">
                              Price per sec for {{ $week }} Week Booking 
                              @if($week == 1)<span class="text-red-500">*</span>@endif
                          </label>
                          <div class="relative">
                              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                              <input type="number" 
                                  name="weekly_price_{{$week}}" 
                                  id="weekly_price_{{$week}}"
                                  step="0.01"
                                  class="w-full rounded-xl border border-gray-200 pl-8 py-3 text-sm focus:border-[#009A5C] outline-none" 
                                  value="{{ old('weekly_price_'.$week, $source->{"weekly_price_$week"} ?? '') }}"
                                  {{ $isWeeklyEnabled && $week == 1 ? 'required' : '' }}>
                          </div>
                      </div>
                  @endforeach
              </div>
          </div>

      </div>
        {{-- DOOH Screen Media Upload Section --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 max-w-full mx-auto">
            <h3 class="text-lg sm:text-xl font-bold text-[#009A5C] mb-2 flex items-center">
                <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
                Upload Media<span class="text-red-500 ml-1">*</span>
            </h3>
            <p class="text-sm sm:text-base text-gray-500 mb-4">
                Upload high-quality images or videos (Max 10 files).
            </p>

            {{-- Existing Media --}}
            <div class="flex flex-wrap gap-3 mb-4" id="existingMediaPreview">
                @if(isset($screen) && $screen->media && $screen->media->count())
                    @foreach($screen->media as $media)
                        <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-lg overflow-hidden border bg-gray-50 flex-shrink-0">
                            @if(Str::startsWith($media->media_type, 'image'))
                                <img src="{{ asset('storage/'.$media->file_path) }}"
                                    class="w-full h-full object-cover">
                            @else
                                <video src="{{ asset('storage/'.$media->file_path) }}"
                                      class="w-full h-full object-cover" muted></video>
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
                  class="flex items-center justify-between w-full px-4 py-3 border border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition text-sm sm:text-base">
                <span class="text-gray-600">Choose files</span>
                <span class="text-gray-400 text-xs sm:text-sm">Browse</span>
            </label>

            <input
                id="mediaInput"
                type="file"
                name="media[]"
                multiple
                accept="image/jpeg,image/png,image/webp,video/mp4,video/webm"
                class="hidden"
                @if(!(isset($screen) && $screen->media && $screen->media->count())) required @endif
            >

            <p class="text-xs sm:text-sm text-gray-400 mt-2">
                Supported: JPG, PNG, WEBP, MP4, WEBM • Max 10 files • 5MB each
            </p>
        </div>


    </div>

<script>
// --- DOOH Media Upload/Preview/Remove ---
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
</script>
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
    const width = widthInput.value || '0';
    const height = heightInput.value || '0';
    const unit = unitSelect.value === 'sqft' ? 'sq.ft' : 'sq.m';
    
    if(sizePreview) {
        sizePreview.value = `${width} x ${height} ${unit}`;
    }
}

// Listeners
widthInput.addEventListener('input', updateSizePreview);
heightInput.addEventListener('input', updateSizePreview);
unitSelect.addEventListener('change', updateSizePreview);

// Run once on load to populate if editing
updateSizePreview();

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
// --- Landmark Dynamic Inputs ---
const addLandmarkBtn = document.getElementById('addLandmarkBtn');
const landmarksContainer = document.getElementById('landmarksContainer');

addLandmarkBtn.addEventListener('click', () => {
    const newInput = document.createElement('input');
    newInput.type = 'text';
    newInput.name = 'landmarks[]';
    newInput.className = 'w-full border rounded-xl px-4 py-3 mt-2'; // Added margin top for spacing
    newInput.placeholder = 'Enter landmark name';
    landmarksContainer.appendChild(newInput);
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
<script>
  @if($isEdit && $hoarding?->latitude && $hoarding?->longitude)
    const editLat = {{ $hoarding->latitude }};
    const editLng = {{ $hoarding->longitude }};
    map.setView([editLat, editLng], 15);
    marker.setLatLng([editLat, editLng]);
    document.getElementById('geotagSuccess').classList.remove('hidden');
  @endif
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const weeklyCheckbox = document.getElementById('enable_weekly_booking');
    const weeklySection = document.getElementById('weekly-section');
    const week1Input = document.getElementById('weekly_price_1');

    weeklyCheckbox.addEventListener('change', function() {
        if (this.checked) {
            // Show section and make first week mandatory
            weeklySection.classList.remove('hidden');
            weeklySection.classList.add('grid');
            week1Input.setAttribute('required', 'required');
        } else {
            // Hide section and remove mandatory requirement
            weeklySection.classList.add('hidden');
            weeklySection.classList.remove('grid');
            week1Input.removeAttribute('required');
        }
    });
});
</script>
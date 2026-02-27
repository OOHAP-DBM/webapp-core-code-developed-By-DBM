
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
            <label class="text-sm font-bold text-gray-700">
                Hoarding Type <span class="text-red-500">*</span>
            </label>
          <div class="w-full bg-[#0094FF] border border-[#0094FF] rounded-xl px-4 py-2.5 text-white font-bold">
            OOH (Out-of-Home)
          </div>
        </div>

        <!-- Category -->
        <div class="space-y-2">
          <!-- <label class="text-sm font-semibold text-gray-700"> <span class="text-red-500">*</span></label> -->
          <label class="text-sm font-bold text-gray-700">
                Category <span class="text-red-500">*</span>
            </label>
          <select name="category" required
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#009A5C]/10 focus:border-[#009A5C] outline-none appearance-none bg-[url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%226b7280%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>')] bg-[length:20px] bg-[right_1rem_center] bg-no-repeat">
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
          <label class="text-sm font-bold text-gray-700">
                Hoarding Size 
            </label>
        <div class="grid grid-cols-4 gap-4 items-end">
          <!-- Unit -->
          <div class="space-y-1">
            <label class="text-xs font-bold text-gray-500">Unit</label>
            <select id="unit" name="measurement_unit" required class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2.5 outline-none">
              <option value="sqft" {{ old('measurement_unit', $draft->measurement_unit ?? 'sqft') == 'sqft' ? 'selected' : '' }}>Sqft</option>
              <option value="sqm" {{ old('measurement_unit', $draft->measurement_unit ?? 'sqft') == 'sqm' ? 'selected' : '' }}>Sqm</option>
            </select>
          </div>

          <!-- Width -->
          <div class="space-y-1">
            <label class="text-xs font-bold text-gray-500">Width <span class="text-red-500">*</span></label>
            <input type="number" id="width" name="width" placeholder="eg.500" required 
              value="{{ old('width', $draft->width ?? '') }}"
              class="w-full border border-gray-200 rounded-lg px-3 py-2.5 outline-none focus:border-[#009A5C]">
          </div>

          <!-- Height -->
          <div class="space-y-1">
            <label class="text-xs font-bold text-gray-500">Height <span class="text-red-500">*</span></label>
            <input type="number" id="height" name="height" placeholder="eg.300" required 
              value="{{ old('height', $draft->height ?? '') }}" 
              class="w-full border border-gray-200 rounded-lg px-3 py-2.5 outline-none focus:border-[#009A5C]">
          </div>

          <!-- Size Preview (READ-ONLY, NO NAME) -->
          <div class="space-y-1">
            <label class="text-xs font-bold text-gray-500">Size Preview</label>
            <input type="text" id="sizePreview" readonly 
              placeholder="Auto-calculated"
              class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 cursor-not-allowed text-gray-600">
          </div>
        </div>
      </div>
    </div>

  @include('components.hoardings.create.location')

    <!-- Pricing Details -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
      <h3 class="text-lg font-bold text-[#009A5C] mb-2 flex items-center">
        <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
        Pricing
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
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5
                  focus:border-[#009A5C] outline-none transition-all"
          />
          <p class="text-xs text-gray-400">
            Standard monthly hoarding price (before discount)
          </p>
        </div>

        @php
          // Get the value from old input or the database
          $currentMonthlyPrice = old('monthly_price', $draft->hoarding->monthly_price ?? '');
          
          // Logic: If the value is 0, treat it as an empty string so the input box stays empty
          $displayValue = ($currentMonthlyPrice == 0) ? '' : $currentMonthlyPrice;
        @endphp
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
            value="{{ $displayValue }}"
            placeholder="Optional discounted price"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5
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
                                  onclick="removeExistingMedia({{ $media->id }}, '{{ Str::startsWith($media->mime_type, 'video') ? 'video' : 'image' }}', this)"
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
                    class="flex items-center justify-between w-full px-4 py-2.5 border border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition">
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
/* ── Size Preview ── */
const widthInput  = document.getElementById('width');
const heightInput = document.getElementById('height');
const unitSelect  = document.getElementById('unit');
const sizePreview = document.getElementById('sizePreview');

function updateSizePreview() {
  const width  = widthInput.value  || 0;
  const height = heightInput.value || 0;
  const unit   = unitSelect.value === 'sqft' ? 'sq.ft' : 'sq.m';
  sizePreview.value = `${width} x ${height} ${unit}`;
}
widthInput.addEventListener('input', updateSizePreview);
heightInput.addEventListener('input', updateSizePreview);
unitSelect.addEventListener('change', updateSizePreview);

/* ── Media Upload ── */
let deletedMediaIds    = [];
let newImageFiles      = [];
let newVideoFile       = null;
let existingVideoCount = {{ isset($draft) && $draft->hoarding->oohMedia ? $draft->hoarding->oohMedia->filter(fn($m) => str_starts_with($m->mime_type, 'video'))->count() : 0 }};

const MAX_IMAGES    = 10;
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

const mediaInput          = document.getElementById('mediaInput');
const newMediaPreview     = document.getElementById('newMediaPreview');
const existingMediaPreview= document.getElementById('existingMediaPreview');
const deletedMediaIdsInput= document.getElementById('deletedMediaIds');

/* ── Error toast ── */
function showMediaError(msg) {
  // reuse existing error box if present, else create one above the file input
  let box = document.getElementById('mediaErrorBox');
  if (!box) {
    box = document.createElement('div');
    box.id = 'mediaErrorBox';
    box.className = 'mb-3 px-4 py-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl';
    mediaInput.parentElement.insertBefore(box, mediaInput.previousElementSibling);
  }
  box.textContent = msg;
  box.style.display = 'block';
  clearTimeout(box._timer);
  box._timer = setTimeout(() => { box.style.display = 'none'; }, 5000);
}

/* ── Sync all chosen files into the real form input ── */
function syncFormInput() {
  const dt = new DataTransfer();
  newImageFiles.forEach(f => dt.items.add(f));
  if (newVideoFile) dt.items.add(newVideoFile);
  mediaInput.files = dt.files;
}

/* ── Remove button factory ── */
function makeRemoveBtn(onClick) {
  const btn = document.createElement('button');
  btn.type      = 'button';
  btn.className = 'absolute top-1 right-1 bg-white rounded-full shadow p-1 text-red-600 hover:bg-red-100 z-20';
  btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
    viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
  </svg>`;
  btn.addEventListener('click', onClick);
  return btn;
}

/* ── Video thumbnail via canvas ── */
function getVideoThumbnail(file) {
  return new Promise((resolve) => {
    const blobUrl = URL.createObjectURL(file);
    const video   = document.createElement('video');
    video.muted       = true;
    video.playsInline = true;
    video.preload     = 'auto';

    let resolved = false;
    const done = (result) => {
      if (resolved) return;
      resolved = true;
      URL.revokeObjectURL(blobUrl);
      video.src = '';
      resolve(result);
    };

    video.addEventListener('loadedmetadata', () => {
      video.currentTime = Math.min(0.5, video.duration * 0.1 || 0.1);
    });

    video.addEventListener('seeked', () => {
      try {
        const canvas = document.createElement('canvas');
        canvas.width = canvas.height = 112;
        canvas.getContext('2d').drawImage(video, 0, 0, 112, 112);
        done(canvas.toDataURL('image/jpeg', 0.85));
      } catch (e) { done(null); }
    });

    video.addEventListener('error', () => done(null));
    setTimeout(() => done(null), 8000);

    video.src = blobUrl;
    video.load();
  });
}

/* ── Render all previews ── */
function renderNewPreviews() {
  newMediaPreview.innerHTML = '';

  // Images
  newImageFiles.forEach((file, idx) => {
    const wrapper     = document.createElement('div');
    wrapper.className = 'relative w-28 h-28 rounded overflow-hidden border flex-shrink-0 bg-gray-100';
    const img         = document.createElement('img');
    img.src           = URL.createObjectURL(file);
    img.className     = 'w-full h-full object-cover';
    wrapper.appendChild(img);
    wrapper.appendChild(makeRemoveBtn(() => {
      newImageFiles.splice(idx, 1);
      syncFormInput();
      renderNewPreviews();
    }));
    newMediaPreview.appendChild(wrapper);
  });

  // Video
  if (newVideoFile) {
    const wrapper     = document.createElement('div');
    wrapper.className = 'relative w-28 h-28 rounded overflow-hidden border flex-shrink-0 bg-gray-800 flex items-center justify-center';
    wrapper.innerHTML = `<svg class="animate-spin h-6 w-6 text-white opacity-60"
      xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>`;
    newMediaPreview.appendChild(wrapper);

    const removeBtn = makeRemoveBtn(() => {
      newVideoFile = null;
      syncFormInput();
      renderNewPreviews();
    });

    getVideoThumbnail(newVideoFile).then((dataUrl) => {
      wrapper.innerHTML = '';
      if (dataUrl) {
        const thumb     = document.createElement('img');
        thumb.src       = dataUrl;
        thumb.className = 'w-full h-full object-cover';
        const overlay     = document.createElement('div');
        overlay.className = 'absolute inset-0 flex items-center justify-center bg-black/20 pointer-events-none';
        overlay.innerHTML = `<div class="bg-black/50 rounded-full p-1.5">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M8 5v14l11-7z"/>
          </svg></div>`;
        wrapper.appendChild(thumb);
        wrapper.appendChild(overlay);
      } else {
        wrapper.innerHTML = `<div class="flex flex-col items-center justify-center w-full h-full text-gray-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M4 8a2 2 0 012-2h9a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V8z"/>
          </svg>
          <span class="text-xs text-white">Video</span>
        </div>`;
      }
      wrapper.appendChild(removeBtn);
    });
  }
}

/* ── Existing media remove ── */
// ✅ FIXED — use button reference
function removeExistingMedia(id, type, btnEl) {
    deletedMediaIds.push(id);
    deletedMediaIdsInput.value = deletedMediaIds.join(',');
    const mediaItem = btnEl.closest('.relative');
    if (mediaItem) {
        mediaItem.style.transition = 'opacity 0.2s';
        mediaItem.style.opacity = '0';
        setTimeout(() => mediaItem.remove(), 200);
    }
    if (type === 'video') existingVideoCount = Math.max(0, existingVideoCount - 1);
}

/* ── Single file input handler ── */
mediaInput.addEventListener('change', function (e) {
  const files = Array.from(e.target.files);
  this.value  = ''; // reset so same file can be picked again

  files.forEach(file => {
    const isImage = file.type.startsWith('image/');
    const isVideo = file.type.startsWith('video/');

    // Size check
    if (file.size > MAX_FILE_SIZE) {
      showMediaError(`"${file.name}" exceeds the 10MB limit. Please compress it and try again.`);
      return;
    }

    if (isImage) {
      if (newImageFiles.length >= MAX_IMAGES) {
        showMediaError(`Maximum ${MAX_IMAGES} images allowed.`);
        return;
      }
      if (!['image/jpeg','image/png','image/webp'].includes(file.type)) {
        showMediaError(`"${file.name}" is not a supported image type.`);
        return;
      }
      newImageFiles.push(file);

    } else if (isVideo) {
      if (newVideoFile || existingVideoCount > 0) {
        showMediaError('Only 1 video is allowed. Remove the existing video first.');
        return;
      }
      if (!['video/mp4','video/webm'].includes(file.type)) {
        showMediaError(`"${file.name}" is not a supported video type. Use MP4 or WEBM.`);
        return;
      }
      newVideoFile = file;

    } else {
      showMediaError(`"${file.name}" is not a supported file type.`);
    }
  });

  syncFormInput();
  renderNewPreviews();
});

/* ── Landmark Addition ── */
const addLandmarkBtn     = document.getElementById('addLandmarkBtn');
const landmarksContainer = document.getElementById('landmarksContainer');

addLandmarkBtn.addEventListener('click', function () {
  const input       = document.createElement('input');
  input.type        = 'text';
  input.name        = 'landmarks[]';
  input.placeholder = 'Enter landmark';
  input.className   = 'w-full border border-gray-200 rounded-xl px-4 py-2.5 outline-none mt-2';
  landmarksContainer.appendChild(input);
});

/* ── Form Validation ── */
document.querySelector('form').addEventListener('submit', function (e) {
  const latitude  = latInput.value.trim();
  const longitude = lngInput.value.trim();
  if (!latitude || !longitude) {
    e.preventDefault();
    showError('Please confirm location on map before proceeding.');
    document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});
</script>
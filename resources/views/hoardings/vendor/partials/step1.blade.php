<div class="space-y-6">

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
              <option value="{{ $cat->value }}">{{ $cat->value }}</option>
            @endforeach
          @endif
        </select>
      </div>

      {{-- <!-- Screen Type -->
      <div class="md:col-span-2 space-y-2 mt-2">
        <label class="text-sm font-bold text-gray-700">Hoarding Type <span class="text-red-500">*</span></label>
        <select name="screen_type" required
          class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none appearance-none bg-[url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%226b7280%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>')] bg-[length:20px] bg-[right_1rem_center] bg-no-repeat">
          <option value="LED">LED</option>
          <option value="LCD">LCD</option>
        </select>
      </div> --}}
    </div>

    <!-- Screen Size -->
    <div class="mt-8">
      <label class="text-sm font-bold text-gray-700 mb-4 block">Hoarding Size</label>
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
          <label class="text-xs font-bold text-gray-500"> Width <span class="text-red-500">*</span></label>
          <input type="number" id="width" name="width" placeholder="500" required class="w-full border border-gray-200 rounded-lg px-3 py-3 outline-none focus:border-[#009A5C]">
        </div>

        <!-- Height -->
        <div class="space-y-1">
          <label class="text-xs font-bold text-gray-500"> Height <span class="text-red-500">*</span></label>
          <input type="number" id="height" name="height" placeholder="300" required class="w-full border border-gray-200 rounded-lg px-3 py-3 outline-none focus:border-[#009A5C]">
        </div>

        <!-- Size Preview -->
        <div class="space-y-1">
          <label class="text-xs font-bold text-gray-500">Size Preview</label>
          <input type="text" id="sizePreview" value="0 x 0 sq.ft" readonly class="w-full bg-[#E5E9F2] border-none rounded-lg px-3 py-3 text-gray-700 font-medium outline-none">
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
        <input type="text" name="address" placeholder="Opposite Ram Dharam Kanta B43 Sector 7" required 
          class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all">
      </div>

      <div class="space-y-2">
        <label class="text-sm font-bold text-gray-700">Pincode <span class="text-red-500">*</span></label>
        <input type="text" name="pincode" placeholder="226010" required 
          class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all">
      </div>

      <div class="space-y-2">
        <label class="text-sm font-bold text-gray-700">Locality <span class="text-red-500">*</span></label>
        <input type="text" name="locality" placeholder="e.g. Indira Nagar" required 
          class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none transition-all">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="space-y-2">
          <label class="text-sm font-bold text-gray-700">City</label>
          <input type="text" name="city" placeholder="Lucknow" class="w-full border border-gray-200 rounded-xl px-4 py-3 outline-none">
        </div>
        <div class="space-y-2">
          <label class="text-sm font-bold text-gray-700">State</label>
          <input type="text" name="state" placeholder="Uttar Pradesh" class="w-full border border-gray-200 rounded-xl px-4 py-3 outline-none">
        </div>
      </div>
    </div>

    <div class="mt-8 space-y-4">
      <div class="flex items-center justify-between">
        <label class="text-sm font-bold text-gray-700">Nearby Landmarks</label>
        <button type="button" class="bg-[#1A1A1A] text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-black transition-all">
          + Add another landmark
        </button>
      </div>
      <div class="space-y-3">
        <input type="text" placeholder="Opposite Ram Dharam Kanta" class="w-full border border-gray-200 rounded-xl px-4 py-3 outline-none">
      </div>
    </div>

    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6 p-6 bg-[#FBFBFB] rounded-2xl border border-gray-50">
      <div class="space-y-2">
        <label class="text-sm font-bold text-gray-700 flex items-center">
          Geotag <span class="ml-2 w-4 h-4 bg-[#009A5C] rounded flex items-center justify-center text-[10px] text-white">✓</span>
        </label>
        <input type="url" name="geotag" value="http://geotag.oohapp.com" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none">
      </div>
      <div class="space-y-2">
        <label class="text-sm font-bold text-gray-700">Latitude</label>
        <input type="text" name="lat" placeholder="26.84457..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none bg-white">
      </div>
      <div class="space-y-2">
        <label class="text-sm font-bold text-gray-700">Longitude</label>
        <input type="text" name="lng" placeholder="80.94577..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none bg-white">
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
                  name="monthly_base_price"
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
                  Monthly Offer Price (₹)
              </label>
              <input
                  type="number"
                  name="monthly_offer_price"
                  min="1"
                  step="0.01"
                  placeholder="e.g. 42,000"
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

    <div class="relative group border-2 border-dashed border-[#E5E7EB] rounded-2xl p-12 bg-[#FBFBFB] hover:bg-green-50/30 hover:border-[#009A5C] transition-all flex flex-col items-center justify-center">
      <input type="file" id="mediaUpload" name="media[]" multiple required class="absolute inset-0 opacity-0 cursor-pointer">
      <div class="w-16 h-16 bg-white shadow-sm rounded-2xl flex items-center justify-center mb-4 text-[#009A5C] group-hover:scale-110 transition-transform">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
          </path>
        </svg>
      </div>
      <p class="text-base font-bold text-gray-700">Drop your images here, or <span class="text-[#009A5C]">browse</span></p>
      <p class="text-xs text-gray-400 mt-2">Supports: JPG, PNG, MP4 (Max 10MB per file)</p>

      <!-- File Preview -->
      <ul id="filePreview" class="mt-4 text-sm text-gray-600 space-y-1"></ul>
    </div>
  </div>


  
  



    {{-- <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div class="space-y-4">
                <label class="text-sm font-bold text-gray-700">Nagar Nigam Approved? <span class="text-red-500">*</span></label>
                <div class="flex p-1.5 bg-gray-100 rounded-2xl w-fit">
                    <label class="cursor-pointer">
                        <input type="radio" name="approved" value="yes" class="peer sr-only" checked>
                        <div class="px-8 py-2.5 rounded-xl text-sm font-bold transition-all peer-checked:bg-white peer-checked:text-[#009A5C] peer-checked:shadow-sm text-gray-500">Yes</div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="approved" value="no" class="peer sr-only">
                        <div class="px-8 py-2.5 rounded-xl text-sm font-bold transition-all peer-checked:bg-white peer-checked:text-[#009A5C] peer-checked:shadow-sm text-gray-500">No</div>
                    </label>
                </div>
            </div>
            
            <div class="space-y-4">
                <label class="text-sm font-bold text-gray-700">Recently Booked By</label>
                <div class="flex items-center gap-4">
                    <button type="button" class="px-6 py-2.5 border-2 border-dashed border-gray-200 rounded-xl text-xs font-bold text-gray-400 hover:border-[#009A5C] hover:text-[#009A5C] transition-all">
                        + Add Logo
                    </button>
                    <span class="text-xs text-gray-400 italic">Up to 10 brand logos</span>
                </div>
            </div>
          
        </div>
    </div>

    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mt-6">
        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
            <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
            GazeFlow
        </h3>

        <div class="bg-[#FBFBFB] rounded-2xl p-6 border border-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700">Expected Footfall</label>
                    <input type="number" 
                        name="expected_footfall" 
                        placeholder="1000" 
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#009A5C]/10 focus:border-[#009A5C] outline-none transition-all bg-white shadow-inner">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700">Expected Eyeball</label>
                    <input type="number" 
                        name="expected_eyeball" 
                        placeholder="5000" 
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#009A5C]/10 focus:border-[#009A5C] outline-none transition-all bg-white shadow-inner">
                </div>
            </div>
            <p class="text-[11px] text-gray-400 mt-4 flex items-center italic">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                Metrics help advertisers understand the visibility potential of this digital asset.
            </p>
        </div>
    </div> --}}
    

</div>

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

  mediaInput.addEventListener('change', () => {
    filePreview.innerHTML = '';
    Array.from(mediaInput.files).forEach(file => {
      const li = document.createElement('li');
      li.textContent = file.name;
      filePreview.appendChild(li);
    });
  });
  // In the Blade view (step1.blade.php), enhance JS for file validation

document.querySelector('form').addEventListener('submit', function(e) {
  const mediaInput = document.getElementById('mediaUpload');
  if (!mediaInput.files.length) {
    e.preventDefault();
    const error = document.createElement('div');
    error.className = 'text-red-500 text-xs mt-2';
    error.textContent = 'At least one media file is required.';
    if (!document.getElementById('media-error')) {
      error.id = 'media-error';
      mediaInput.parentNode.appendChild(error);
    }
    mediaInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});
</script>

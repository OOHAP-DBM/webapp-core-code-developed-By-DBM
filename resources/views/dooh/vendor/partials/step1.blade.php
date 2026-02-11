<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
    $source = $draft->hoarding ?? null;
    $isWeeklyEnabled = old('enable_weekly_booking', $source->enable_weekly_booking ?? false);
@endphp
<div class="min-h-screen bg-gray-50 py-4 sm:py-6 lg:py-8">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Page Header -->
        <!-- <div class="mb-6 lg:mb-8">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900">
                {{ $isEdit ? 'Edit' : 'Create' }} DOOH Screen
            </h1>
            <p class="mt-2 text-sm sm:text-base text-gray-600">
                {{ $isEdit ? 'Update your digital hoarding details' : 'Add a new digital out-of-home advertising screen' }}
            </p>
        </div> -->

        <div class="space-y-6 lg:space-y-8">

            <!-- ========================================
                 SECTION 1: HOARDING DETAILS
            ======================================== -->
            <div class="bg-white rounded-2xl lg:rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-[#009A5C] mb-6 flex items-center">
                    <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
                    Hoarding Details
                </h3>

                <div class="space-y-6">
                    <!-- Row 1: Hoarding Type & Category -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Hoarding Type -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Hoarding Type <span class="text-red-500">*</span>
                            </label>
                            <div class="w-full bg-[#0094FF] border-2 border-[#0094FF] rounded-xl px-4 py-2.5 text-white font-bold  text-sm sm:text-base">
                                DOOH (Digital Out-of-Home)
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="category" required
                                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm sm:text-base
                                           focus:ring-2 focus:ring-[#009A5C]/20 focus:border-[#009A5C] outline-none
                                           appearance-none bg-white cursor-pointer transition-all
                                           hover:border-gray-300">
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
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Screen Type -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">
                            Screen Type <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="screen_type" required
                                class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm sm:text-base
                                       focus:ring-2 focus:ring-[#009A5C]/20 focus:border-[#009A5C] outline-none
                                       appearance-none bg-white cursor-pointer transition-all
                                       hover:border-gray-300">
                                <option value="LED" {{ old('screen_type', $screen->hoarding->screen_type ?? '') == 'LED' ? 'selected' : '' }}>LED Display</option>
                                <option value="LCD" {{ old('screen_type', $screen->hoarding->screen_type ?? '') == 'LCD' ? 'selected' : '' }}>LCD Display</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Screen Size -->
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-gray-700">
                               Screen Size 
                            </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                            <!-- Unit -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-medium text-gray-600">Unit</label>
                                <div class="relative">
                                    <select id="unit" name="measurement_unit" required 
                                        class="w-full border-2 border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                               focus:ring-2 focus:ring-[#009A5C]/20 focus:border-[#009A5C] outline-none
                                               appearance-none bg-white cursor-pointer">
                                        <option value="sqft" {{ old('measurement_unit', $draft->measurement_unit ?? '') == 'sqft' ? 'selected' : '' }}>Sqft</option>
                                        <option value="sqm" {{ old('measurement_unit', $draft->measurement_unit ?? '') == 'sqm' ? 'selected' : '' }}>Sqm</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Width -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-medium text-gray-600">
                                    Width <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="width" name="width" 
                                    value="{{ old('width', $draft->width ?? '') }}" 
                                    placeholder="500" required min="1" max="10000"
                                    class="w-full border-2 border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                           focus:ring-2 focus:ring-[#009A5C]/20 focus:border-[#009A5C] outline-none
                                           placeholder:text-gray-400">
                            </div>

                            <!-- Height -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-medium text-gray-600">
                                    Height <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="height" name="height" 
                                    value="{{ old('height', $draft->height ?? '') }}" 
                                    placeholder="300" required min="1" max="10000"
                                    class="w-full border-2 border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                           focus:ring-2 focus:ring-[#009A5C]/20 focus:border-[#009A5C] outline-none
                                           placeholder:text-gray-400">
                            </div>

                            <!-- Size Preview -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-medium text-gray-600">Preview</label>
                                <input type="text" id="sizePreview" readonly
                                    class="w-full bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-200 
                                           rounded-lg px-3 py-2.5 text-sm text-gray-700 font-medium outline-none">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================
                 SECTION 2: LOCATION
            ======================================== -->
            @include('components.hoardings.create.location')

            <!-- ========================================
                 SECTION 3: PRICING
            ======================================== -->
            <div class="bg-white rounded-2xl lg:rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-[#009A5C] mb-6 flex items-center">
                    <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
                    Pricing Details
                </h3>

                <div class="space-y-6">
                    <!-- System-locked Campaign Inputs -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 lg:gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Spot Duration (sec)  <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="spot_duration"  required min="1" max="10000" step="1"
                                value="{{ old('spot_duration', $draft?->slot_duration_seconds) }}"
                                class="w-full border-2 border-gray-200 rounded-xl pl-8 pr-4 py-2.5 text-sm sm:text-base
                                           focus:ring-2 focus:ring-[#009A5C]/20 focus:border-[#009A5C] outline-none
                                           placeholder:text-gray-400 ">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Price Per Spot (₹) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">₹</span>
                                <input type="number" name="price_per_slot" min="0.01" step="0.01" required 
                                    value="{{ old('price_per_slot', $draft?->price_per_slot) }}" 
                                    placeholder="" required min ="1"
                                    class="w-full border-2 border-gray-200 rounded-xl pl-8 pr-4 py-2.5 text-sm sm:text-base
                                           focus:ring-2 focus:ring-[#009A5C]/20 focus:border-[#009A5C] outline-none
                                           placeholder:text-gray-400">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Spots Per Day  <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="spots_per_day"  required min="1" max="5000" step="1"
                                value="{{ old('spots_per_day', $draft?->total_slots_per_day) }}"
                                class="w-full border-2 border-gray-200 rounded-xl pl-8 pr-4 py-2.5 text-sm sm:text-base
                                           focus:ring-2 focus:ring-[#009A5C]/20 focus:border-[#009A5C] outline-none
                                           placeholder:text-gray-400 ">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Daily Runtime (hrs) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="daily_runtime"  required min="0.5" max="24"   step="any"
                                value="{{ old('daily_runtime', $draft?->screen_run_time) }}"
                                class="w-full border-2 border-gray-200 rounded-xl pl-8 pr-4 py-2.5 text-sm sm:text-base
                                           focus:ring-2 focus:ring-[#009A5C]/20 focus:border-[#009A5C] outline-none
                                           placeholder:text-gray-400">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Campaign Price Monthly  (30 Days)
                            </label>
                            
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-700 font-bold">₹</span>
                                <input type="text" id="base_campaign_price" readonly min="1" step="any"
                                    class="w-full  bg-[#FFF5E7] 
                                           rounded-xl pl-8 pr-4 py-2.5 text-sm sm:text-base
                                           text-gray-900 font-bold cursor-not-allowed">
                                <input type="hidden" name="base_monthly_price" value="{{ old('base_monthly_price', $draft?->base_monthly_price) }} " id="base_monthly_price_input">
                            </div>
                        </div>

                         <!-- <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                              
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-700 font-bold">₹</span>
                                <input type="text" id="base_campaign_price" readonly
                                    class="w-full border-2 border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 
                                           rounded-xl pl-8 pr-4 py-2.5 text-sm sm:text-base
                                           text-gray-900 font-bold">
                            </div>
                        </div> -->
                    </div>

                    <!-- Discount -->
                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 lg:gap-6">

                       <div class="sm:col-span-1 space-y-3">
                            <label class="block text-sm font-semibold text-gray-700">
                                Discount Type
                            </label>

                            <div class="relative">
                               <select id="discount_type" name="discount_type"
    class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm sm:text-base
       focus:ring-2 focus:ring-[#009A5C]/20 focus:border-[#009A5C] outline-none
       appearance-none bg-white cursor-pointer transition-all
       hover:border-gray-300">

    <option value="percent"
        {{ old('discount_type', $hoarding->discount_type ?? '') == 'percent' ? 'selected' : '' }}>
        Percentage (%)
    </option>

    <option value="amount"
        {{ old('discount_type', $hoarding->discount_type ?? '') == 'amount' ? 'selected' : '' }}>
        Fixed (₹)
    </option>
</select>


                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-1 space-y-3">
                            <label class="block text-sm font-semibold text-gray-700">
                                Discount Value
                            </label>
                            <div class="relative">
                                <input type="number"
    id="discount_value"
    name="discount_value"
    value="{{ old('discount_value', $hoarding->discount_value ?? '') }}"
    min="0"
    step="0.01"
    placeholder="eg. 10"
    class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5
        focus:ring-2 focus:ring-[#009A5C]/20 focus:border-[#009A5C]">

                                        
                                <span id="discount_symbol"
                                    class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-500 font-medium">%</span>
                            </div>
                        </div>


                         <div class="sm:col-span-1 space-y-3">
                            <label class="block text-sm font-semibold text-gray-700">
                               Final  Campaign Price Monthly
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-700 font-bold">₹</span>
                                <input type="text" id="monthly_price" readonly  min="0" step="any"
                                    class="w-full  bg-[#FFF5E7] 
                                           rounded-xl pl-8 pr-4 py-2.5 text-sm sm:text-base
                                           text-gray-900 font-bold cursor-not-allowed">
                                    <input type="hidden" name="monthly_price" id="monthly_price_input">
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Info Banner -->
                    <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-bold text-blue-900 mb-1">Pricing Calculation</h4>
                                <p class="text-xs sm:text-sm text-blue-800">
                                    Base price = Spots per day × Campaign days (30) × Price per spot.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================
                 SECTION 4: MEDIA UPLOAD
            ======================================== -->
            <div class="bg-white rounded-2xl lg:rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100">
                <!-- <div class="flex items-center mb-6 lg:mb-8">
                    <span class="w-1.5 h-6 sm:h-7 bg-[#009A5C] rounded-full mr-3"></span>
                    <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-[#009A5C]">
                        Upload Media
                    </h3>
                </div> -->
                 <h3 class="text-lg font-bold text-[#009A5C] mb-6 flex items-center">
                    <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
                    Upload Media <span class="text-red-500">*</span>
                </h3>

                <p class="text-sm sm:text-base text-gray-600 mb-6">
                    Upload high-quality images or videos showcasing your screen (Max 10 files, 5MB each)
                </p>

                <!-- Existing Media Preview -->
                @if(isset($screen) && $screen->media && $screen->media->count())
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Existing Media</h3>
                        <div class="flex flex-wrap gap-3" id="existingMediaPreview">
                            @foreach($screen->media as $media)
                                <div class="relative w-24 h-24 sm:w-28 sm:h-28 lg:w-32 lg:h-32 rounded-xl overflow-hidden 
                                            border-2 border-gray-200 bg-gray-50 flex-shrink-0 group">
                                    @if(Str::startsWith($media->media_type, 'image'))
                                        <img src="{{ asset('storage/'.$media->file_path) }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                    @else
                                        <video src="{{ asset('storage/'.$media->file_path) }}"
                                            class="w-full h-full object-cover" muted></video>
                                    @endif
                                    <button type="button"
                                        onclick="removeExistingMedia({{ $media->id }})"
                                        class="absolute top-2 right-2 bg-white/90 backdrop-blur rounded-full p-1.5 
                                               shadow-lg text-red-600 hover:bg-red-50 hover:scale-110 
                                               transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <input type="hidden" name="deleted_media_ids" id="deletedMediaIds">

                <!-- New Media Preview -->
                <div id="newMediaPreview" class="flex flex-wrap gap-3 mb-6 empty:mb-0"></div>

                <!-- Upload Area -->
                <label for="mediaInput"
                    class="flex items-center justify-between w-full px-4 sm:px-6 py-4 sm:py-5
                           border-2 border-dashed border-gray-300 rounded-xl cursor-pointer
                           hover:border-[#009A5C] hover:bg-gray-50 transition-all duration-200 group">
                    <div class="flex items-center gap-3">
                        <div class="bg-gray-100 rounded-lg p-2.5 group-hover:bg-[#009A5C]/10 transition-colors">
                            <svg class="w-6 h-6 text-gray-600 group-hover:text-[#009A5C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-sm sm:text-base font-semibold text-gray-700">Choose files to upload</span>
                            <span class="block text-xs sm:text-sm text-gray-500 mt-0.5">or drag and drop</span>
                        </div>
                    </div>
                    <span class="text-sm font-medium text-[#009A5C] hidden sm:inline">Browse</span>
                </label>

                <input id="mediaInput" type="file" name="media[]" multiple
                    accept="image/jpeg,image/png,image/webp,video/mp4,video/webm"
                    class="hidden"
                    @if(!(isset($screen) && $screen->media && $screen->media->count())) required @endif>

                <p class="text-xs sm:text-sm text-gray-500 mt-3">
                    <span class="font-medium">Supported formats:</span> JPG, PNG, WEBP, MP4, WEBM • 
                    <span class="font-medium">Max:</span> 10 files, 5MB each
                </p>
            </div>

        </div>
    </div>
</div>

<!-- ========================================
     SCRIPTS
======================================== -->
<script>
// ============================================
// MEDIA UPLOAD/PREVIEW/REMOVE
// ============================================
let deletedMediaIds = [];
let newFiles = [];
const maxFiles = 10;
const maxFileSize = 5 * 1024 * 1024; // 5MB

const mediaInput = document.getElementById('mediaInput');
const newMediaPreview = document.getElementById('newMediaPreview');
const existingMediaPreview = document.getElementById('existingMediaPreview');
const deletedMediaIdsInput = document.getElementById('deletedMediaIds');

function renderNewPreviews() {
    newMediaPreview.innerHTML = '';
    newFiles.forEach((file, idx) => {
        const url = URL.createObjectURL(file);
        const isImage = file.type.startsWith('image');
        const isVideo = file.type.startsWith('video');
        
        if (!isImage && !isVideo) return;

        const mediaEl = isImage 
            ? `<img src='${url}' class='object-cover w-full h-full group-hover:scale-105 transition-transform duration-200'>`
            : `<video src='${url}' class='object-cover w-full h-full' muted></video>`;

        const el = `
            <div class='relative w-24 h-24 sm:w-28 sm:h-28 lg:w-32 lg:h-32 rounded-xl overflow-hidden 
                        border-2 border-gray-200 bg-gray-50 flex-shrink-0 group'>
                ${mediaEl}
                <button type='button' 
                        onclick='removeNewFile(${idx})' 
                        class='absolute top-2 right-2 bg-white/90 backdrop-blur rounded-full p-1.5 
                               shadow-lg text-red-600 hover:bg-red-50 hover:scale-110 
                               transition-all duration-200'
                        title='Remove'>
                    <svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'></path>
                    </svg>
                </button>
            </div>`;
        
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
    const button = existingMediaPreview.querySelector(`[onclick*='removeExistingMedia(${id})']`);
    if (button && button.parentElement) {
        button.parentElement.remove();
    }
}

function updateInputFiles() {
    const dt = new DataTransfer();
    newFiles.forEach(f => dt.items.add(f));
    mediaInput.files = dt.files;
}

mediaInput.addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'video/webm'];
    
    for (const file of files) {
        if (newFiles.length >= maxFiles) {
            alert(`Maximum ${maxFiles} files allowed`);
            break;
        }
        if (!validTypes.includes(file.type)) {
            alert(`${file.name} is not a supported format`);
            continue;
        }
        if (file.size > maxFileSize) {
            alert(`${file.name} exceeds 5MB size limit`);
            continue;
        }
        newFiles.push(file);
    }
    
    updateInputFiles();
    renderNewPreviews();
});

// ============================================
// PRICING CALCULATION
// ============================================
// document.addEventListener('DOMContentLoaded', function() {
//     const spotsPerDay = 300;
//     const campaignDays = 30;
//     const pricePerSpotInput = document.querySelector('[name="price_per_spot"]');
//     const discountInput = document.querySelector('[name="discount_percent"]');
//     const baseCampaignPriceInput = document.getElementById('base_campaign_price');

//     function updatePricing() {
//         const pricePerSpot = parseFloat(pricePerSpotInput?.value) || 0;
//         const discount = parseFloat(discountInput?.value) || 0;
//         const totalSpots = spotsPerDay * campaignDays;
        
//         let basePrice = totalSpots * pricePerSpot;
        
//         if (discount > 0 && discount <= 100) {
//             basePrice = basePrice * (1 - discount / 100);
//         }
        
//         if (baseCampaignPriceInput) {
//             baseCampaignPriceInput.value = basePrice > 0 
//                 ? basePrice.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
//                 : '';
//         }
//     }

//     if (pricePerSpotInput) pricePerSpotInput.addEventListener('input', updatePricing);
//     if (discountInput) discountInput.addEventListener('input', updatePricing);
    
//     // Initial calculation
//     updatePricing();
// });

// ============================================
// SIZE PREVIEW
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const widthInput = document.getElementById('width');
    const heightInput = document.getElementById('height');
    const unitSelect = document.getElementById('unit');
    const sizePreview = document.getElementById('sizePreview');

    if (!widthInput || !heightInput || !unitSelect || !sizePreview) return;

    function updateSizePreview() {
        const width = widthInput.value || '0';
        const height = heightInput.value || '0';
        const unit = unitSelect.value === 'sqft' ? 'sq.ft' : 'sq.m';
        sizePreview.value = `${width} × ${height} ${unit}`;
    }

    widthInput.addEventListener('input', updateSizePreview);
    heightInput.addEventListener('input', updateSizePreview);
    unitSelect.addEventListener('change', updateSizePreview);

    // Initial update
    updateSizePreview();
});

// ============================================
// MAP INITIALIZATION (if location component exists)
// ============================================
@if($isEdit && $hoarding?->latitude && $hoarding?->longitude)
document.addEventListener('DOMContentLoaded', function() {
    if (typeof map !== 'undefined' && typeof marker !== 'undefined') {
        const editLat = {{ $hoarding->latitude }};
        const editLng = {{ $hoarding->longitude }};
        map.setView([editLat, editLng], 15);
        marker.setLatLng([editLat, editLng]);
        const successEl = document.getElementById('geotagSuccess');
        if (successEl) successEl.classList.remove('hidden');
    }
});
@endif
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const campaignDays = 30;

    const pricePerSpotInput = document.querySelector('[name="price_per_slot"]');
    const spotsPerDayInput = document.querySelector('[name="spots_per_day"]');
    const discountTypeSelect = document.getElementById('discount_type');
    const discountValueInput = document.getElementById('discount_value');
    const discountSymbol = document.getElementById('discount_symbol');
    const basePriceHiddenInput = document.getElementById('base_monthly_price_input');
    const finalPriceHiddenInput = document.getElementById('monthly_price_input');

    const basePriceInput = document.getElementById('base_campaign_price');
    const finalPriceInput = document.getElementById('monthly_price');

    function formatINR(value) {
        return value.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function updateDiscountSymbol() {
        const discountType = discountTypeSelect.value;
        discountSymbol.textContent = discountType === 'percent' ? '%' : '₹';
        updatePricing();
    }

    function updatePricing() {
        const pricePerSpot = parseFloat(pricePerSpotInput?.value) || 0;
        const spotsPerDay = parseInt(spotsPerDayInput?.value) || 0;
        const discountValue = parseFloat(discountValueInput?.value) || 0;
        const discountType = discountTypeSelect.value;

        // Base price (NO discount)
        const basePrice = spotsPerDay * campaignDays * pricePerSpot;

        // Calculate discounted price
        let finalPrice = basePrice;
        let discountAmount = 0;

        if (discountValue > 0) {
            if (discountType === 'percent') {
                // Percentage discount
                if (discountValue <= 100) {
                    discountAmount = basePrice * (discountValue / 100);
                    finalPrice = basePrice - discountAmount;
                } else {
                    // Invalid percentage
                    discountValueInput.value = 100;
                    discountAmount = basePrice;
                    finalPrice = 0;
                }
            } else {
                // Fixed amount discount
                discountAmount = discountValue;
                finalPrice = basePrice - discountAmount;
                
                // Prevent negative final price
                if (finalPrice < 0) {
                    finalPrice = 0;
                    discountValueInput.value = basePrice.toFixed(2);
                }
            }
        }

        // Display values
        basePriceInput.value = basePrice > 0 ? formatINR(basePrice) : '';
        finalPriceInput.value = finalPrice >= 0 ? formatINR(finalPrice) : '';
        basePriceHiddenInput.value = basePrice > 0 ? basePrice.toFixed(2) : 0;
        finalPriceHiddenInput.value = finalPrice >= 0 ? finalPrice.toFixed(2) : 0;
    }

    // Event listeners
    pricePerSpotInput?.addEventListener('input', updatePricing);
    spotsPerDayInput?.addEventListener('input', updatePricing);
    discountTypeSelect?.addEventListener('change', updateDiscountSymbol);
    discountValueInput?.addEventListener('input', updatePricing);

    // Initial calculation
    updateDiscountSymbol();
    updatePricing();
});
</script>

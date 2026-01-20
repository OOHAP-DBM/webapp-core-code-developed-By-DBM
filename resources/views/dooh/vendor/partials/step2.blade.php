<!-- Step 2: Settings & Attributes -->
{{-- <div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-6"> --}}

    {{-- @dump($parentHoarding) --}}
  <!-- Body -->
  <div class="md:p-8 space-y-8">
    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
            <h3 class="text-xl font-bold text-gray-800">Additional Settings</h3>
        </div>

        <div class="space-y-6">
            <!-- Nagar Nigam Approval -->
            @php
                $nagarNigamApproved = old('nagar_nigam_approved', $parentHoarding->nagar_nigam_approved ?? 0);
                $permitNumber = old('permit_number', $parentHoarding->permit_number ?? '');
                $permitValidTill = old('permit_valid_till', $parentHoarding->permit_valid_till ?? '');
            @endphp
            <div class="flex items-center justify-between p-4 bg-green-50/30 rounded-2xl border border-green-100/50">
                <label class="text-sm font-bold text-gray-700">Nagar Nigam Approved? </label>
                <div class="flex items-center gap-6">
                    <label class="flex items-center cursor-pointer group">
                      <input type="radio" name="nagar_nigam_approved" value="1" 
                             class="hidden peer" id="nagar-yes"
                             {{ $nagarNigamApproved == 1 ? 'checked' : '' }}>
                      <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center peer-checked:border-[#009A5C] peer-checked:bg-[#009A5C] transition-all">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                      </div>
                      <span class="ml-2 text-sm font-semibold text-gray-600 peer-checked:text-[#009A5C]">Yes</span>
                    </label>
                    <label class="flex items-center cursor-pointer group">
                      <input type="radio" name="nagar_nigam_approved" value="0" 
                             class="hidden peer" id="nagar-no"
                             {{ $nagarNigamApproved == 0 ? 'checked' : '' }}>
                      <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center peer-checked:border-[#009A5C] peer-checked:bg-[#009A5C] transition-all">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                      </div>
                      <span class="ml-2 text-sm font-semibold text-gray-600 peer-checked:text-[#009A5C]">No</span>
                    </label>
                </div>
            </div>

            <!-- Nagar Nigam Modal -->
            <div id="nagarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 hidden">
              <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-xs">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Enter Permit Details</h2>
                <input type="text" id="permitNumberInput" 
                       value="{{ $permitNumber }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-3 mb-4 outline-none focus:border-[#009A5C]" 
                       placeholder="Permit Number">
                <input type="date" id="permitValidTillInput" 
                       value="{{ $permitValidTill }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-3 mb-4 outline-none focus:border-[#009A5C]" 
                       placeholder="Permit Valid Till">
                <div class="flex justify-end gap-2">
                  <button type="button" id="nagarCancelBtn" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold">Cancel</button>
                  <button type="button" id="nagarSaveBtn" class="px-4 py-2 rounded-lg bg-[#009A5C] text-white font-semibold">Save</button>
                </div>
              </div>
            </div>
            <input type="hidden" name="permit_number" id="permitNumberHidden" value="{{ $permitNumber }}">
            <input type="hidden" name="permit_valid_till" id="permitValidTillHidden" value="{{ $permitValidTill }}">
            @if($nagarNigamApproved == 1 && $permitNumber)
                <div class="mt-2 ml-3 text-xs text-gray-500">
                    <span class="font-semibold text-gray-700">Permit No:</span> {{ $permitNumber }}
                    |
                    <span class="font-semibold text-gray-700">Valid Till:</span>
                    {{ \Carbon\Carbon::parse($permitValidTill)->format('d M Y') }}

                </div>
            @endif

            <!-- Block Dates -->
            @php
                $hasBlockDates = old('block_dates', !empty($parentHoarding->block_dates ?? null) ? 1 : 0);
                $existingBlockDates = old('blocked_dates_json', !empty($parentHoarding->block_dates ?? null) ? json_encode($parentHoarding->block_dates) : '[]');
            @endphp
            <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100">
                <label class="text-sm font-bold text-gray-700">Do you want to block any certain dates?</label>
                <div class="flex items-center gap-6">
                  <label class="flex items-center cursor-pointer group">
                    <input type="radio" name="block_dates" value="1" class="hidden peer" id="block-yes"
                           {{ $hasBlockDates == 1 ? 'checked' : '' }}>
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center peer-checked:border-[#009A5C] peer-checked:bg-[#009A5C] transition-all">
                      <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-gray-600 peer-checked:text-[#009A5C]">Yes</span>
                  </label>
                  <label class="flex items-center cursor-pointer group">
                    <input type="radio" name="block_dates" value="0" class="hidden peer" id="block-no"
                           {{ $hasBlockDates == 0 ? 'checked' : '' }}>
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center peer-checked:border-[#009A5C] peer-checked:bg-[#009A5C] transition-all">
                      <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-gray-600 peer-checked:text-[#009A5C]">No</span>
                  </label>
                </div>
            </div>

            <!-- Block Dates Modal -->
            <div id="blockDatesModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 hidden">
              <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-xs">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Select Blocked Dates</h2>
                <input type="text" id="blockDatesCalendar" class="w-full border border-gray-200 rounded-xl px-4 py-3 mb-4 outline-none focus:border-[#009A5C]" placeholder="Pick dates to block...">
                <div class="flex justify-end gap-2">
                  <button type="button" id="blockDatesCancelBtn" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold">Cancel</button>
                  <button type="button" id="blockDatesSaveBtn" class="px-4 py-2 rounded-lg bg-[#009A5C] text-white font-semibold">Save</button>
                </div>
              </div>
            </div>
            <input type="hidden" name="blocked_dates_json" id="blockedDatesHidden" value="{{ $existingBlockDates }}">

            <!-- Grace Period -->
            @php
                $hasGracePeriod = old('needs_grace_period', !empty($parentHoarding->grace_period_days ?? null) ? 1 : 0);
                $gracePeriodValue = old('grace_period_days', $parentHoarding->grace_period_days ?? '');
            @endphp
            <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100">
                <label class="text-sm font-bold text-gray-700">Do you need grace period after booking?</label>
                <div class="flex items-center gap-6">
                  <label class="flex items-center cursor-pointer group">
                    <input type="radio" name="needs_grace_period" value="1" class="hidden peer" id="grace-yes"
                           {{ $hasGracePeriod == 1 ? 'checked' : '' }}>
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center peer-checked:border-[#009A5C] peer-checked:bg-[#009A5C] transition-all">
                      <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-gray-600 peer-checked:text-[#009A5C]">Yes</span>
                  </label>
                  <label class="flex items-center cursor-pointer group">
                    <input type="radio" name="needs_grace_period" value="0" class="hidden peer" id="grace-no"
                           {{ $hasGracePeriod == 0 ? 'checked' : '' }}>
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center peer-checked:border-[#009A5C] peer-checked:bg-[#009A5C] transition-all">
                      <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-gray-600 peer-checked:text-[#009A5C]">No</span>
                  </label>
                </div>
              </div>

              <!-- Grace Period Modal -->
              <div id="graceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 hidden">
                <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-xs">
                  <h2 class="text-lg font-bold mb-4 text-gray-800">Set Grace Period (in days)</h2>
                  <input type="number"  max="30" id="gracePeriodInput" 
                         value="{{ $gracePeriodValue }}"
                         class="w-full border border-gray-200 rounded-xl px-4 py-3 mb-4 outline-none focus:border-[#009A5C]" 
                         placeholder="Enter number of days">
                  <div class="flex justify-end gap-2">
                    <button type="button" id="graceCancelBtn" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold">Cancel</button>
                    <button type="button" id="graceSaveBtn" class="px-4 py-2 rounded-lg bg-[#009A5C] text-white font-semibold">Save</button>
                  </div>
                </div>
              </div>
              <input type="hidden" name="grace_period_days" id="gracePeriodDaysHidden" value="{{ $gracePeriodValue }}">
        </div>
    </div>
   
    <!-- GazeFlow Section -->
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
                        value="{{ old('expected_footfall', $parentHoarding->expected_footfall ?? '') }}"
                        placeholder="1000" 
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#009A5C]/10 focus:border-[#009A5C] outline-none transition-all bg-white shadow-inner">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700">Expected Eyeball</label>
                    <input type="number" 
                        name="expected_eyeball" 
                        value="{{ old('expected_eyeball', $parentHoarding->expected_eyeball ?? '') }}"
                        placeholder="5000" 
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#009A5C]/10 focus:border-[#009A5C] outline-none transition-all bg-white shadow-inner">
                </div>
            </div>
            <p class="text-[11px] text-gray-400 mt-4 flex items-center italic">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                Metrics help advertisers understand the visibility potential of this digital asset.
            </p>
        </div>
    </div>

    <!-- Audience Type Section -->
    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8 space-y-10">
        <div>
            <div class="flex items-center gap-3 mb-8">
                <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
                <h3 class="text-xl font-bold text-gray-800">Select Audience Type</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $audiences = ['Political activities', 'Students', 'Luxury consumers', 'Environments freeks', 'Average Class', 'Public', 'Tourism', 'Foodies'];
                    $selectedAudiences = old('audience_type', 
                        is_array($parentHoarding->audience_types ?? null) 
                            ? $parentHoarding->audience_types 
                            : (is_string($parentHoarding->audience_types ?? null) 
                                ? json_decode($parentHoarding->audience_types, true) 
                                : []
                            )
                    );
                @endphp
                @foreach($audiences as $audience)
                <label class="flex items-center space-x-3 cursor-pointer group">
                    <input type="checkbox" name="audience_type[]" value="{{ $audience }}" 
                           {{ in_array($audience, (array)$selectedAudiences) ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-gray-300 text-[#009A5C] focus:ring-[#009A5C]">
                    <span class="text-sm text-gray-600 group-hover:text-gray-900">{{ $audience }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Brand Logos Section -->
        <div>
            <div class="flex items-center gap-3 mb-8">
                <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
                <h3 class="text-xl font-bold text-gray-800">Recently Booked by</h3>
            </div>
            <p class="text-xs text-gray-400 mb-4">Upload up to 10 brand logos.</p>
            
            @if(isset($parentHoarding) && $parentHoarding->hasMedia('brand_logos'))
            <div class="mb-4 grid grid-cols-5 gap-4">
                @foreach($parentHoarding->getMedia('brand_logos') as $media)
                <div class="relative group">
                    <img src="{{ $media->getUrl() }}" alt="Brand Logo" class="w-full h-20 object-contain border border-gray-200 rounded-lg p-2">
                    <label class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <input type="checkbox" name="delete_brand_logos[]" value="{{ $media->id }}" class="w-4 h-4">
                    </label>
                </div>
                @endforeach
            </div>
            @endif

            <div class="flex items-center w-full">
                <label class="flex flex-row items-center w-full h-14 border border-gray-200 rounded-xl overflow-hidden cursor-pointer hover:border-[#009A5C] transition-all">
                    <div class="bg-gray-100 px-6 h-full flex items-center justify-center text-sm font-bold text-gray-500 border-r border-gray-200">
                        Browse
                    </div>
                    <div class="px-4 text-sm text-gray-400" id="brand-logo-name">Choose file</div>
                    <input type="file" name="brand_logos[]" multiple accept="image/*" class="hidden" id="brand-logos-input" onchange="document.getElementById('brand-logo-name').innerText = this.files.length + ' files selected'">
                </label>
            </div>
        </div>
    </div>

    <!-- Hoardings Attributes -->
    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
            <h3 class="text-xl font-bold text-gray-800">Hoardings Attributes</h3>
        </div>

        <!-- Visible From -->
        <div class="mb-8">
            <label class="text-sm font-bold text-gray-500 mb-4 block uppercase tracking-wider">Visible From</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php 
                    $visibleOptions = ['Metro Ride', 'From Flyover', 'From the road', 'Roof top', 'Wall hanging']; 
                    $selectedVisible = old('visible_from', 
                        is_array($parentHoarding->visibility_details ?? null) 
                            ? $parentHoarding->visibility_details 
                            : (is_string($parentHoarding->visibility_details ?? null) 
                                ? json_decode($parentHoarding->visible_from, true) 
                                : []
                            )
                    );
                @endphp
                @foreach($visibleOptions as $option)
                <label class="flex items-center p-4 border border-dashed border-gray-200 rounded-xl cursor-pointer hover:bg-green-50/50 hover:border-[#009A5C] transition-all group">
                    <input type="checkbox" name="visible_from[]" value="{{ $option }}" 
                           {{ in_array($option, (array)$selectedVisible) ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-gray-300 text-[#009A5C] focus:ring-[#009A5C]">
                    <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-[#009A5C]">{{ $option }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Located At -->
        <div>
            <label class="text-sm font-bold text-gray-500 mb-4 block uppercase tracking-wider">Located At</label>
            <div class="grid grid-cols-2 md:grid-cols-2 gap-y-4 gap-x-12">
                @php 
                    $locationOptions = ['Highway hoarding', 'At Square', 'Shopping Mall', 'Airport', 'Park', 'Main Road', 'Intracity Highway', 'Pause Area']; 
                    $selectedLocations = old('located_at', 
                        is_array($parentHoarding->located_at ?? null) 
                            ? $parentHoarding->located_at 
                            : (is_string($parentHoarding->located_at ?? null) 
                                ? json_decode($parentHoarding->located_at, true) 
                                : []
                            )
                    );
                @endphp
                @foreach($locationOptions as $loc)
                <label class="flex items-center space-x-3 cursor-pointer group">
                    <input type="checkbox" name="located_at[]" value="{{ $loc }}" 
                           {{ in_array($loc, (array)$selectedLocations) ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-gray-300 text-[#009A5C] focus:ring-[#009A5C]">
                    <span class="text-sm text-gray-600 group-hover:text-gray-900">{{ $loc }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Hoardings Visibility -->
    @php
        $currentVisibility = old('visibility_type', $parentHoarding->visibility_type ?? 'one_way');
        $visibilityData = old('visibility_data', 
            is_string($parentHoarding->visibility_start ?? null) 
                ? json_decode($parentHoarding->visibility_start, true) 
                : ($parentHoarding->visibility_start ?? [])
        );
        $oneWayStart = $visibilityData['one_way']['start'] ?? '';
        $oneWayEnd = $visibilityData['one_way']['end'] ?? '';
        $bothSideStart = $visibilityData['both_side']['start'] ?? '';
        $bothSideEnd = $visibilityData['both_side']['end'] ?? '';
    @endphp
    <div 
      x-data="{ visibility: '{{ $currentVisibility }}' }"
      class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8"
    >
      <h3 class="text-xl font-bold text-gray-800 mb-8 flex items-center">
        <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
        Hoardings View For Visitors
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- ONE WAY -->
        <div class="space-y-4">
          <label
            class="flex items-center p-4 rounded-2xl border-2 border-dashed cursor-pointer transition-all"
            :class="visibility === 'one_way'
              ? 'border-[#009A5C] bg-green-50/40'
              : 'border-gray-200 hover:border-[#009A5C]'"
          >
            <input
              type="radio"
              name="visibility_type"
              value="one_way"
              x-model="visibility"
              {{ $currentVisibility === 'one_way' ? 'checked' : '' }}
              class="w-5 h-5 text-[#009A5C] focus:ring-[#009A5C]"
            >
            <span class="ml-3 text-sm font-bold text-gray-700">
              One Way Visibility
            </span>
          </label>

          <div
            class="grid grid-cols-2 gap-4 transition-all"
            x-show="visibility === 'one_way'"
            x-transition
          >
            <div>
              <label class="text-xs font-bold text-gray-400 mb-1 block">Going From</label>
              <input
                type="text"
                name="visibility_start[]"
                value="{{ old('visibility_start.0', $oneWayStart) }}"
                placeholder="Eg. Santacruz"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#009A5C] outline-none"
              >
            </div>
            <div>
              <label class="text-xs font-bold text-gray-400 mb-1 block">To</label>
              <input
                type="text"
                name="visibility_end[]"
                value="{{ old('visibility_end.0', $oneWayEnd) }}"
                placeholder="Eg. Fun Mall"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#009A5C] outline-none"
              >
            </div>
          </div>
        </div>

        <!-- BOTH WAY -->
        <div class="space-y-4">
          <label
            class="flex items-center p-4 rounded-2xl border-2 border-dashed cursor-pointer transition-all"
            :class="visibility === 'both_side'
              ? 'border-[#009A5C] bg-green-50/40'
              : 'border-gray-200 hover:border-[#009A5C]'"
          >
            <input
              type="radio"
              name="visibility_type"
              value="both_side"
              x-model="visibility"
              {{ $currentVisibility === 'both_side' ? 'checked' : '' }}
              class="w-5 h-5 text-[#009A5C] focus:ring-[#009A5C]"
            >
            <span class="ml-3 text-sm font-bold text-gray-700">
              Both Side Visibility
            </span>
          </label>

          <div
            class="grid grid-cols-2 gap-4 transition-all"
            x-show="visibility === 'both_side'"
            x-transition
          >
            <div>
              <label class="text-xs font-bold text-gray-400 mb-1 block">Going From</label>
              <input
                type="text"
                name="visibility_start[]"
                value="{{ old('visibility_start.0', $bothSideStart) }}"
                placeholder="Eg. Santacruz"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#009A5C] outline-none"
              >
            </div>

            <div>
              <label class="text-xs font-bold text-gray-400 mb-1 block">To</label>
              <input
                type="text"
                name="visibility_end[]"
                value="{{ old('visibility_end.0', $bothSideEnd) }}"
                placeholder="Eg. Fun Mall"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#009A5C] outline-none"
              >
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
{{-- </div> --}}

<!-- JavaScript for Modal Interactions -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grace period modal logic
    const graceYes = document.getElementById('grace-yes');
    const graceNo = document.getElementById('grace-no');
    const graceModal = document.getElementById('graceModal');
    const graceInput = document.getElementById('gracePeriodInput');
    const graceSaveBtn = document.getElementById('graceSaveBtn');
    const graceCancelBtn = document.getElementById('graceCancelBtn');
    const graceHidden = document.getElementById('gracePeriodDaysHidden');

    if (graceYes && graceModal && graceInput && graceSaveBtn && graceCancelBtn && graceHidden) {
        // Show modal if grace period is already set
        if (graceHidden.value && parseInt(graceHidden.value) > 0) {
            graceYes.checked = true;
        }

        graceYes.addEventListener('change', function() {
            if (this.checked) {
                graceModal.classList.remove('hidden');
                graceInput.value = graceHidden.value || '';
            }
        });
        
        graceNo.addEventListener('change', function() {
            if (this.checked) {
                graceHidden.value = '';
                graceInput.value = '';
            }
        });
        
        graceSaveBtn.addEventListener('click', function() {
            const val = parseInt(graceInput.value, 10);
            if (!isNaN(val) && val > 0 && val <= 30) {
                graceHidden.value = val;
                graceModal.classList.add('hidden');
                graceInput.classList.remove('border-red-500');
            } else {
                alert('Please enter a valid number between 1 and 30');
                graceInput.classList.add('border-red-500');
                graceInput.focus();
            }
        });
        
        // graceCancelBtn.addEventListener('click', function() {
        //     graceModal.classList.add('hidden');
        //     if (!graceHidden.value) {
        //         graceYes.checked = false;
        //         graceNo.checked = true;
        //     }
        //     graceInput.classList.remove('border-red-500');
        // });
        graceCancelBtn.addEventListener('click', function () {
            graceModal.classList.add('hidden');

            // Restore value from hidden field
            graceInput.value = graceHidden.value || '';
            graceInput.classList.remove('border-red-500');
        });


        // Prevent form submission if grace period is selected but not filled
        const form = graceYes.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (graceYes.checked && (!graceHidden.value || graceHidden.value === '')) {
                    e.preventDefault();
                    alert('Please set the grace period days or select "No"');
                    graceModal.classList.remove('hidden');
                    graceInput.focus();
                    return false;
                }
            });
        }
    }

    // Block Dates modal logic
    const blockYes = document.getElementById('block-yes');
    const blockNo = document.getElementById('block-no');
    const blockModal = document.getElementById('blockDatesModal');
    const blockCalendar = document.getElementById('blockDatesCalendar');
    const blockSaveBtn = document.getElementById('blockDatesSaveBtn');
    const blockCancelBtn = document.getElementById('blockDatesCancelBtn');
    const blockHidden = document.getElementById('blockedDatesHidden');
    let blockSelectedDates = [];

    if (blockYes && blockModal && blockCalendar && blockSaveBtn && blockCancelBtn && blockHidden) {
        // Pre-populate blocked dates from existing data
        try {
            const existingDates = blockHidden.value ? JSON.parse(blockHidden.value) : [];
            if (existingDates.length > 0) {
                blockSelectedDates = existingDates;
                blockYes.checked = true;
            }
        } catch (e) {
            console.error('Error parsing blocked dates:', e);
        }

        blockYes.addEventListener('change', function() {
            if (this.checked) {
                blockModal.classList.remove('hidden');
            }
        });
        
        blockNo.addEventListener('change', function() {
            if (this.checked) {
                blockHidden.value = '[]';
                blockSelectedDates = [];
            }
        });
        
        blockSaveBtn.addEventListener('click', function() {
            if (blockSelectedDates.length > 0) {
                blockHidden.value = JSON.stringify(blockSelectedDates);
                blockModal.classList.add('hidden');
                blockCalendar.classList.remove('border-red-500');
            } else {
                blockCalendar.classList.add('border-red-500');
                blockCalendar.focus();
            }
        });
        
        blockCancelBtn.addEventListener('click', function() {
            blockModal.classList.add('hidden');
              try {
                blockSelectedDates = blockHidden.value
                    ? JSON.parse(blockHidden.value)
                    : [];
            } catch {
                blockSelectedDates = [];
            }

            blockCalendar.classList.remove('border-red-500');
        });

        // Initialize flatpickr calendar
        if (typeof flatpickr !== 'undefined') {
            flatpickr(blockCalendar, {
                mode: 'multiple',
                dateFormat: 'Y-m-d',
                minDate: new Date(),
                defaultDate: blockSelectedDates,
                onChange: function(selectedDates, dateStrArr) {
                    blockSelectedDates = dateStrArr;
                }
            });
        }
    }

    // Nagar Nigam modal logic
    const nagarYes = document.getElementById('nagar-yes');
    const nagarNo = document.getElementById('nagar-no');
    const nagarModal = document.getElementById('nagarModal');
    const permitNumberInput = document.getElementById('permitNumberInput');
    const permitValidTillInput = document.getElementById('permitValidTillInput');
    const nagarSaveBtn = document.getElementById('nagarSaveBtn');
    const nagarCancelBtn = document.getElementById('nagarCancelBtn');
    const permitNumberHidden = document.getElementById('permitNumberHidden');
    const permitValidTillHidden = document.getElementById('permitValidTillHidden');

    if (nagarYes && nagarModal && permitNumberInput && permitValidTillInput && nagarSaveBtn && nagarCancelBtn && permitNumberHidden && permitValidTillHidden) {
        nagarYes.addEventListener('change', function() {
            if (this.checked) {
                nagarModal.classList.remove('hidden');
                permitNumberInput.value = permitNumberHidden.value || '';
                permitValidTillInput.value = permitValidTillHidden.value || '';
            }
        });
        
        nagarNo.addEventListener('change', function() {
            if (this.checked) {
                permitNumberHidden.value = '';
                permitValidTillHidden.value = '';
                permitNumberInput.value = '';
                permitValidTillInput.value = '';
            }
        });
        
        nagarSaveBtn.addEventListener('click', function() {
            const permitNum = permitNumberInput.value.trim();
            const permitDate = permitValidTillInput.value;
            
            if (permitNum && permitDate) {
                permitNumberHidden.value = permitNum;
                permitValidTillHidden.value = permitDate;
                nagarModal.classList.add('hidden');
                permitNumberInput.classList.remove('border-red-500');
                permitValidTillInput.classList.remove('border-red-500');
            } else {
                if (!permitNum) permitNumberInput.classList.add('border-red-500');
                if (!permitDate) permitValidTillInput.classList.add('border-red-500');
                if (!permitNum) permitNumberInput.focus();
                else permitValidTillInput.focus();
            }
        });
        
        nagarCancelBtn.addEventListener('click', function() {
            nagarModal.classList.add('hidden');
            // nagarYes.checked = false;
            // nagarNo.checked = true;
             permitNumberHidden.value = permitNumberHidden.value || '';
            permitValidTillHidden.value = permitValidTillHidden.value || '';

            // Optionally reset modal inputs to hidden values
            permitNumberInput.value = permitNumberHidden.value;
            permitValidTillInput.value = permitValidTillHidden.value;
        });
    }
});
</script>
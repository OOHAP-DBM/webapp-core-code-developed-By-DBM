<!-- Step 2: -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-6">

 

  <!-- Body -->
  <div class="p-8 space-y-8">
<div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
    <div class="flex items-center gap-3 mb-8">
        <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
        <h3 class="text-xl font-bold text-gray-800">Additional Settings</h3>
    </div>

    <div class="space-y-6">
        <div class="flex items-center justify-between p-4 bg-green-50/30 rounded-2xl border border-green-100/50">
            <label class="text-sm font-bold text-gray-700">Nagar Nigam Approved? <span class="text-red-500">*</span></label>
            <div class="flex items-center gap-6">
                <label class="flex items-center cursor-pointer group">
                    <input type="radio" name="nagar_nigam_approved" value="1" class="hidden peer" checked>
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center peer-checked:border-[#009A5C] peer-checked:bg-[#009A5C] transition-all">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-gray-600 peer-checked:text-[#009A5C]">Yes</span>
                </label>
                <label class="flex items-center cursor-pointer group">
                    <input type="radio" name="nagar_nigam_approved" value="0" class="hidden peer">
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center peer-checked:border-[#009A5C] peer-checked:bg-[#009A5C] transition-all">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-gray-600 peer-checked:text-[#009A5C]">No</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100">
            <label class="text-sm font-bold text-gray-700">Do you want to block any certain dates?</label>
            <div class="flex items-center gap-6">
              <label class="flex items-center cursor-pointer group">
                <input type="radio" name="block_dates" value="1" class="hidden peer" x-model="blockDatesEnabled">
                <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center peer-checked:border-[#009A5C] peer-checked:bg-[#009A5C] transition-all">
                  <div class="w-2 h-2 bg-white rounded-full"></div>
                </div>
                <span class="ml-2 text-sm font-semibold text-gray-600 peer-checked:text-[#009A5C]">Yes</span>
              </label>
              <label class="flex items-center cursor-pointer group">
                <input type="radio" name="block_dates" value="0" class="hidden peer" x-model="blockDatesEnabled" checked>
                <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center peer-checked:border-[#009A5C] peer-checked:bg-[#009A5C] transition-all">
                  <div class="w-2 h-2 bg-white rounded-full"></div>
                </div>
                <span class="ml-2 text-sm font-semibold text-gray-600 peer-checked:text-[#009A5C]">No</span>
              </label>
            </div>
          </div>
            <!-- Calendar for blocking dates -->
            <div x-data="{ blockDatesEnabled: '{{ old('block_dates', '0') }}', blockedDates: [] }" class="mt-4" x-show="blockDatesEnabled == '1'" x-cloak>
              <label class="block text-sm font-bold text-gray-700 mb-2">Select dates to block</label>
              <input type="text" x-ref="blockedDatesCalendar" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none bg-white" placeholder="Pick dates to block...">
              <input type="hidden" name="blocked_dates_json" :value="JSON.stringify(blockedDates)">
              <div class="text-xs text-gray-500 mt-2" x-text="blockedDates.length ? 'Blocked: ' + blockedDates.join(', ') : 'No dates selected.'"></div>
            </div>
            <script>
            document.addEventListener('alpine:init', () => {
              Alpine.directive('flatpickr', (el, {expression}, {evaluateLater, effect}) => {
                let evaluate = evaluateLater(expression)
                effect(() => {
                  evaluate((dates) => {
                    flatpickr(el, {
                      mode: 'multiple',
                      dateFormat: 'Y-m-d',
                      onChange: function(selectedDates, dateStrArr) {
                        let input = el.closest('[x-data]').__x.$data;
                        input.blockedDates = dateStrArr;
                      }
                    });
                  })
                })
              })
            })
            </script>

          <!-- Calendar for blocking dates -->
          <div x-data="{ blockDatesEnabled: false, blockedDates: [] }" x-init="
            $watch('blockDatesEnabled', value => {
              if (value == '1') {
                flatpickr($refs.blockedDatesInput, {
                  mode: 'multiple',
                  dateFormat: 'Y-m-d',
                  onChange: function(selectedDates, dateStr, instance) {
                    $refs.blockedDatesHidden.value = JSON.stringify(selectedDates.map(d => instance.formatDate(d, 'Y-m-d')));
                  }
                });
              }
            });
          " class="mt-4" x-show="blockDatesEnabled == '1'">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Select Blocked Dates</label>
            <input type="text" x-ref="blockedDatesInput" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:border-[#009A5C] outline-none" placeholder="Pick one or more dates">
            <input type="hidden" name="blocked_dates_json" x-ref="blockedDatesHidden">
            <p class="text-xs text-gray-400 mt-1">Selected dates will be blocked for booking.</p>
          </div>

        <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100">
            <label class="text-sm font-bold text-gray-700">Do you need grace period after booking?</label>
            <div class="flex items-center gap-6">
                <label class="flex items-center cursor-pointer group">
                    <input type="radio" name="needs_grace_period" value="1" class="hidden peer">
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center peer-checked:border-[#009A5C] peer-checked:bg-[#009A5C] transition-all">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-gray-600 peer-checked:text-[#009A5C]">Yes</span>
                </label>
                <label class="flex items-center cursor-pointer group">
                    <input type="radio" name="needs_grace_period" value="0" class="hidden peer" checked>
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center peer-checked:border-[#009A5C] peer-checked:bg-[#009A5C] transition-all">
                        <div class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-gray-600 peer-checked:text-[#009A5C]">No</span>
                </label>
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
    </div>
    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8 space-y-10">
    
    <div>
        <div class="flex items-center gap-3 mb-8">
            <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
            <h3 class="text-xl font-bold text-gray-800">Select Audience Type</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $audiences = ['Political activities', 'Students', 'Luxury consumers', 'Environments freeks', 'Average Class', 'Public', 'Tourism', 'Foodies'];
            @endphp
            @foreach($audiences as $audience)
            <label class="flex items-center space-x-3 cursor-pointer group">
                <input type="checkbox" name="audience_type[]" value="{{ $audience }}" class="w-5 h-5 rounded border-gray-300 text-[#009A5C] focus:ring-[#009A5C]">
                <span class="text-sm text-gray-600 group-hover:text-gray-900">{{ $audience }}</span>
            </label>
            @endforeach
        </div>
    </div>

    <div>
        <div class="flex items-center gap-3 mb-8">
            <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
            <h3 class="text-xl font-bold text-gray-800">Recently Booked by</h3>
        </div>
        <p class="text-xs text-gray-400 mb-4">Upload up to 10 brand logos.</p>
        <div class="flex items-center w-full">
            <label class="flex flex-row items-center w-full h-14 border border-gray-200 rounded-xl overflow-hidden cursor-pointer hover:border-[#009A5C] transition-all">
                <div class="bg-gray-100 px-6 h-full flex items-center justify-center text-sm font-bold text-gray-500 border-r border-gray-200">
                    Browse
                </div>
                <div class="px-4 text-sm text-gray-400" id="brand-logo-name">Choose file</div>
                <input type="file" name="brand_logos[]" multiple class="hidden" id="brand-logos-input" onchange="document.getElementById('brand-logo-name').innerText = this.files.length + ' files selected'">
            </label>
        </div>
    </div>
</div>



    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
            <h3 class="text-xl font-bold text-gray-800">Hoardings Attributes</h3>
        </div>

    <div class="mb-8">
        <label class="text-sm font-bold text-gray-500 mb-4 block uppercase tracking-wider">Visible From</label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php $visibleOptions = ['Metro Ride', 'From Flyover', 'From the road', 'Roof top', 'Wall hanging']; @endphp
            @foreach($visibleOptions as $option)
            <label class="flex items-center p-4 border border-dashed border-gray-200 rounded-xl cursor-pointer hover:bg-green-50/50 hover:border-[#009A5C] transition-all group">
                <input type="checkbox" name="visible_from[]" value="{{ $option }}" class="w-5 h-5 rounded border-gray-300 text-[#009A5C] focus:ring-[#009A5C]">
                <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-[#009A5C]">{{ $option }}</span>
            </label>
            @endforeach
        </div>
    </div>

    <div>
        <label class="text-sm font-bold text-gray-500 mb-4 block uppercase tracking-wider">Located At</label>
        <div class="grid grid-cols-2 md:grid-cols-2 gap-y-4 gap-x-12">
            @php $locationOptions = ['Highway hoarding', 'At Square', 'Shopping Mall', 'Airport', 'Park', 'Main Road', 'Intracity Highway', 'Pause Area']; @endphp
            @foreach($locationOptions as $loc)
            <label class="flex items-center space-x-3 cursor-pointer group">
                <input type="checkbox" name="located_at[]" value="{{ $loc }}" class="w-5 h-5 rounded border-gray-300 text-[#009A5C] focus:ring-[#009A5C]">
                <span class="text-sm text-gray-600 group-hover:text-gray-900">{{ $loc }}</span>
            </label>
            @endforeach
        </div>
    </div>
</div>

<!-- Hoardings Visibility -->
<div 
  x-data="{ visibility: 'one_way' }"
  class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8"
>
  <h3 class="text-xl font-bold text-gray-800 mb-8 flex items-center">
    <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
    Hoardings Visibility
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
          <label class="text-xs font-bold text-gray-400 mb-1 block">To</label>
          <input
            type="text"
            name="visibility_to[]"
            placeholder="Eg. Fun Mall"
            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#009A5C] outline-none"
          >
        </div>

        <div>
          <label class="text-xs font-bold text-gray-400 mb-1 block">Going From</label>
          <input
            type="text"
            name="visibility_from[]"
            placeholder="Eg. Santacruz"
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
            name="both_visibility_from[]"
            placeholder="Eg. Santacruz"
            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#009A5C] outline-none"
          >
        </div>

        <div>
          <label class="text-xs font-bold text-gray-400 mb-1 block">To</label>
          <input
            type="text"
            name="both_visibility_to[]"
            placeholder="Eg. Fun Mall"
            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#009A5C] outline-none"
          >
        </div>
      </div>
    </div>

  </div>
</div>


  </div>
</div>

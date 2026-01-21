{{-- <div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-6"> --}}
  <div class="md:p-8 space-y-8">
    <!-- Weekly Rental -->
    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
        <h3 class="text-xl font-bold text-gray-800">Rental Offering</h3>
      </div>
      <p class="text-xs text-gray-500 mb-8">You can set price weekly rental for your listing</p>

      <div class="flex gap-8 border-b border-gray-100 mb-8">
        <button type="button" class="pb-2 border-b-2 border-[#009A5C] text-[#009A5C] text-sm font-bold uppercase tracking-wider">Weekly</button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-end">
        <div class="space-y-2">
          <label class="text-sm font-semibold text-gray-600">Enable Weekly Booking?</label>
          <div class="flex items-center gap-4">
            <input type="checkbox" id="enable_weekly_booking" name="enable_weekly_booking" value="1" 
              {{ old('enable_weekly_booking', $draft->hoarding->enable_weekly_booking ?? 0) ? 'checked' : '' }}
              class="w-5 h-5 rounded border-gray-300 text-[#009A5C] cursor-pointer">
            <span class="text-xs text-gray-500">Allow customers to book for weekly durations</span>
          </div>
        </div>
      </div>

      <div id="weekly-prices-section" class="{{ old('enable_weekly_booking', $draft->hoarding->enable_weekly_booking ?? 0) ? 'grid' : 'hidden' }} grid-cols-1 md:grid-cols-3 gap-8 mt-6">
        <div class="space-y-2">
          <label class="text-sm font-semibold text-gray-600">Price for 1 Week <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
            <input type="number" name="weekly_price_1" placeholder="1 Week Price" 
              value="{{ old('weekly_price_1', $draft->hoarding->weekly_price_1 ?? '') }}"
              class="w-full border border-gray-200 rounded-xl px-8 py-3.5 focus:border-[#009A5C] outline-none">
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-semibold text-gray-600">Price for 2 Weeks</label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
            <input type="number" name="weekly_price_2" placeholder="2 Weeks Price" 
              value="{{ old('weekly_price_2', $draft->hoarding->weekly_price_2 ?? '') }}"
              class="w-full border border-gray-200 rounded-xl px-8 py-3.5 focus:border-[#009A5C] outline-none">
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-semibold text-gray-600">Price for 3 Weeks</label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
            <input type="number" name="weekly_price_3" placeholder="3 Weeks Price" 
              value="{{ old('weekly_price_3', $draft->hoarding->weekly_price_3 ?? '') }}"
              class="w-full border border-gray-200 rounded-xl px-8 py-3.5 focus:border-[#009A5C] outline-none">
          </div>
        </div>
      </div>
    </div>
       @csrf
    
       @include('dooh.vendor.partials.package')

    <!-- Services -->
    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
        <h3 class="text-xl font-bold text-gray-800">Services Includes</h3>
      </div>
      <p class="text-xs text-gray-400 mb-8">Set Services provided for free (Yes) or as additional charges (No)</p>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
        <!-- Graphics (PARENT TABLE: hoardings) -->
        <div class="space-y-4 p-4 border border-gray-50 rounded-2xl">
          <div class="flex items-center justify-between">
            <label class="text-sm font-bold text-gray-700">Graphics Included (Free)?</label>
            <div class="flex items-center gap-2">
              <label class="cursor-pointer">
                <input type="radio" name="graphics_included" value="1" 
                  {{ old('graphics_included', $draft->hoarding->graphics_included ?? 0) == 1 ? 'checked' : '' }}
                  class="hidden peer toggle-service" data-target="graphics-box">
                <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">Yes</span>
              </label>
              <label class="cursor-pointer">
                <input type="radio" name="graphics_included" value="0" 
                  {{ old('graphics_included', $draft->hoarding->graphics_included ?? 0) == 0 ? 'checked' : '' }}
                  class="hidden peer toggle-service" data-target="graphics-box">
                <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">No</span>
              </label>
            </div>
          </div>
          <div id="graphics-box" class="transition-all {{ old('graphics_included', $draft->hoarding->graphics_included ?? 0) == 1 ? 'opacity-40 pointer-events-none' : '' }}">
            <label class="text-[10px] font-bold text-gray-400 uppercase">Graphics Charge</label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
              <input type="number" name="graphics_charge" placeholder="0.00" 
                value="{{ old('graphics_charge', $draft->hoarding->graphics_charge ?? '') }}"
                {{ old('graphics_included', $draft->hoarding->graphics_included ?? 0) == 1 ? 'disabled' : '' }}
                class="w-full border border-gray-200 rounded-xl pl-8 py-3 text-sm outline-none">
            </div>
          </div>
        </div>

        <!-- Printing (CHILD TABLE: ooh_hoardings) -->
        <div class="space-y-4 p-4 border border-gray-50 rounded-2xl">
          <div class="flex items-center justify-between">
            <label class="text-sm font-bold text-gray-700">Printing Included (Free)?</label>
            <div class="flex items-center gap-2">
              <label class="cursor-pointer">
                <input type="radio" name="printing_included" value="1" 
                  {{ old('printing_included', $draft->printing_included ?? 0) == 1 ? 'checked' : '' }}
                  class="hidden peer toggle-service" data-target="printing-box">
                <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">Yes</span>
              </label>
              <label class="cursor-pointer">
                <input type="radio" name="printing_included" value="0" 
                  {{ old('printing_included', $draft->printing_included ?? 0) == 0 ? 'checked' : '' }}
                  class="hidden peer toggle-service" data-target="printing-box">
                <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">No</span>
              </label>
            </div>
          </div>
          <div id="printing-box" class="space-y-3 transition-all {{ old('printing_included', $draft->printing_included ?? 0) == 1 ? 'opacity-40 pointer-events-none' : '' }}">
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
              <input type="number" name="printing_charge" placeholder="Price" 
                value="{{ old('printing_charge', $draft->printing_charge ?? '') }}"
                {{ old('printing_included', $draft->printing_included ?? 0) == 1 ? 'disabled' : '' }}
                class="w-full border border-gray-200 rounded-xl pl-8 py-3 text-sm outline-none">
            </div>
            <select name="material_type" 
              {{ old('printing_included', $draft->printing_included ?? 0) == 1 ? 'disabled' : '' }}
              class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none bg-white">
              <option value="">Select Material</option>
              <option value="flex" {{ old('material_type', $draft->material_type ?? '') == 'flex' ? 'selected' : '' }}>Flex</option>
              <option value="vinyl" {{ old('material_type', $draft->material_type ?? '') == 'vinyl' ? 'selected' : '' }}>Vinyl</option>
            </select>
          </div>
        </div>

        <!-- Mounting (CHILD TABLE: ooh_hoardings) -->
        <div class="space-y-4 p-4 border border-gray-50 rounded-2xl">
          <div class="flex items-center justify-between">
            <label class="text-sm font-bold text-gray-700">Mounting Included (Free)?</label>
            <div class="flex items-center gap-2">
              <label class="cursor-pointer">
                <input type="radio" name="mounting_included" value="1" 
                  {{ old('mounting_included', $draft->mounting_included ?? 0) == 1 ? 'checked' : '' }}
                  class="hidden peer toggle-service" data-target="mounting-box">
                <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">Yes</span>
              </label>
              <label class="cursor-pointer">
                <input type="radio" name="mounting_included" value="0" 
                  {{ old('mounting_included', $draft->mounting_included ?? 0) == 0 ? 'checked' : '' }}
                  class="hidden peer toggle-service" data-target="mounting-box">
                <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">No</span>
              </label>
            </div>
          </div>
          <div id="mounting-box" class="transition-all {{ old('mounting_included', $draft->mounting_included ?? 0) == 1 ? 'opacity-40 pointer-events-none' : '' }}">
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
              <input type="number" name="mounting_charge" placeholder="Price" 
                value="{{ old('mounting_charge', $draft->mounting_charge ?? '') }}"
                {{ old('mounting_included', $draft->mounting_included ?? 0) == 1 ? 'disabled' : '' }}
                class="w-full border border-gray-200 rounded-xl pl-8 py-3 text-sm outline-none">
            </div>
          </div>
        </div>

        <!-- Lighting (CHILD TABLE: ooh_hoardings) -->
        <div class="space-y-4 p-4 border border-gray-50 rounded-2xl">
          <div class="flex items-center justify-between">
            <label class="text-sm font-bold text-gray-700">Lighting Included (Free)?</label>
            <div class="flex items-center gap-2">
              <label class="cursor-pointer">
                <input type="radio" name="lighting_included" value="1" 
                  {{ old('lighting_included', $draft->lighting_included ?? 0) == 1 ? 'checked' : '' }}
                  class="hidden peer toggle-service" data-target="lighting-box">
                <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">Yes</span>
              </label>
              <label class="cursor-pointer">
                <input type="radio" name="lighting_included" value="0" 
                  {{ old('lighting_included', $draft->lighting_included ?? 0) == 0 ? 'checked' : '' }}
                  class="hidden peer toggle-service" data-target="lighting-box">
                <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">No</span>
              </label>
            </div>
          </div>
          <div id="lighting-box" class="space-y-3 transition-all">
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
              <input type="number" name="lighting_charge" placeholder="Lighting Charge" 
                value="{{ old('lighting_charge', $draft->lighting_charge ?? '') }}"
                {{ old('lighting_included', $draft->lighting_included ?? 0) == 1 ? 'disabled' : '' }}
                class="w-full border border-gray-200 rounded-xl pl-8 py-3 text-sm outline-none">
            </div>
            <select name="lighting_type" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none bg-white">
              <option value="">Select Lighting Type</option>
              <option value="front-lit" {{ old('lighting_type', $draft->lighting_type ?? '') == 'front-lit' ? 'selected' : '' }}>Front-lit</option>
              <option value="back-lit" {{ old('lighting_type', $draft->lighting_type ?? '') == 'back-lit' ? 'selected' : '' }}>Back-lit</option>
              <option value="led" {{ old('lighting_type', $draft->lighting_type ?? '') == 'led' ? 'selected' : '' }}>LED</option>
            </select>
          </div>
        </div>

        <!-- Fixed Charges -->
        <div class="md:col-span-2 bg-gray-50/50 p-6 rounded-2xl border border-dashed border-gray-200 space-y-4">
          <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Fixed Charges</h4>
          <div class="bg-white border border-gray-100 rounded-[1.5rem] p-8 space-y-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="space-y-1">
                <h4 class="text-sm font-bold text-gray-700">Remounting Service Charge?</h4>
                <p class="text-xs text-gray-400">Includes Mounting + Printing</p>
              </div>
              <div class="relative w-full md:w-72">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                <input type="number" name="remounting_charge" placeholder="Per remounting charge" 
                  value="{{ old('remounting_charge', $draft->remounting_charge ?? '') }}"
                  class="w-full border border-gray-200 rounded-xl pl-8 py-3 text-sm outline-none">
              </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="space-y-1">
                <h4 class="text-sm font-bold text-gray-700">Survey Charge?</h4>
                <p class="text-xs text-gray-400">One Time Survey Charge</p>
              </div>
              <div class="relative w-full md:w-72">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                <input type="number" name="survey_charge" placeholder="Per survey charge" 
                  value="{{ old('survey_charge', $draft->hoarding->survey_charge ?? '') }}"
                  class="w-full border border-gray-200 rounded-xl pl-8 py-3 text-sm outline-none">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
{{-- </div> --}}

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Weekly Pricing Toggle
  const enableWeekly = document.getElementById('enable_weekly_booking');
  const weeklyPrices = document.getElementById('weekly-prices-section');
  if (enableWeekly) {
    if (enableWeekly.checked) {
      weeklyPrices.classList.remove('hidden');
      weeklyPrices.classList.add('grid');
    }
    enableWeekly.addEventListener('change', function() {
      weeklyPrices.classList.toggle('hidden', !this.checked);
      weeklyPrices.classList.toggle('grid', this.checked);
    });
  }

  // Service Toggle Logic
  const serviceToggles = [
    { name: 'mounting_included', box: 'mounting-box' },
    { name: 'printing_included', box: 'printing-box' },
    { name: 'graphics_included', box: 'graphics-box' },
    { name: 'lighting_included', box: 'lighting-box' },
  ];

  serviceToggles.forEach(service => {
    const radios = document.querySelectorAll(`input[name="${service.name}"]`);
    const target = document.getElementById(service.box);
    
    function updateBox() {
      const checked = Array.from(radios).find(r => r.checked);
      if (!checked || !target) return;
      const isFree = checked.value === "1";
      
      if (isFree) {
        target.classList.add('opacity-40', 'pointer-events-none');
        target.querySelectorAll('input, select').forEach(el => {
          el.disabled = true;
        });
      } else {
        target.classList.remove('opacity-40', 'pointer-events-none');
        target.querySelectorAll('input, select').forEach(el => el.disabled = false);
      }
    }
    
    radios.forEach(radio => radio.addEventListener('change', updateBox));
    updateBox(); // Initialize on load
  });

  // ======================================================
// 3. Robust Package Serialization Logic
// ======================================================
// Select the specific form or find the closest form to the container
const offersContainer = document.getElementById('offers-container');
const offersJsonInput = document.getElementById('offers_json');
const mainForm = offersContainer ? offersContainer.closest('form') : document.querySelector('form');

if (mainForm && offersJsonInput && offersContainer) {
    // Listen to the submit event
    mainForm.addEventListener('submit', function (e) {
        const offers = [];
        
        // Find all package groups
        const groups = offersContainer.querySelectorAll('.group');
        
        groups.forEach(group => {
            const name = group.querySelector('.offer_name')?.value.trim();
            const duration = group.querySelector('.offer_duration')?.value;
            const unit = group.querySelector('.offer_unit')?.value;
            const discount = group.querySelector('.offer_discount')?.value;
            const endDate = group.querySelector('.offer_end_date')?.value;

            // Only push if there's data
            if (name || duration) {
                offers.push({
                    name: name,
                    duration: duration,
                    unit: unit,
                    discount: discount,
                    end_date: endDate,
                });
            }
        });

        // Critical: Update the hidden input value
        offersJsonInput.value = JSON.stringify(offers);
        
        // DEBUG: Uncomment the line below to verify before the page reloads
        console.log('Final JSON to be sent:', offersJsonInput.value);
    });
}
});
</script>
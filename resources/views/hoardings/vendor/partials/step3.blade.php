<div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-6">
  <div class="md:p-8 space-y-8">
    <!-- Weekly Rental -->
    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
      <input type="hidden" name="offers_json" id="offers_json">
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

    <!-- Campaign Packages -->
    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
        <h3 class="text-xl font-bold text-gray-800">Long term Campaign Packages</h3>
      </div>
      <p class="text-xs text-gray-400 mb-8">Create specific inventory bundles (e.g., Annual Bulk Booking)</p>

      <div class="space-y-6">
        <div id="offers-container" class="space-y-4">
          @php
            $existingOffers = [];
            if (old('offers_json')) {
              $existingOffers = json_decode(old('offers_json'), true) ?? [];
            } elseif (isset($draft->hoarding) && isset($draft->hoarding->packages) && $draft->hoarding->packages->count() > 0) {
              foreach ($draft->hoarding->packages as $package) {
                $existingOffers[] = [
                  'name' => $package->name,
                  'min_booking_duration' => $package->duration_value,
                  'duration_unit' => $package->duration_unit,
                  'discount' => $package->discount_percentage,
                  'end_date' => $package->valid_until,
                  'services' => $package->included_services ? json_decode($package->included_services, true) : []
                ];
              }
            }
          @endphp
          
          @foreach($existingOffers as $index => $offer)
          <div class="group bg-gray-50/50 rounded-2xl border border-gray-100 p-6 transition-all hover:border-[#009A5C] hover:bg-white">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
              <div class="md:col-span-4 space-y-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase">Offer Label</label>
                <input type="text" name="offer_name[{{ $index }}]" placeholder="e.g. Festival" 
                  value="{{ $offer['name'] ?? '' }}"
                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none">
              </div>
              <div class="md:col-span-3 space-y-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase">Min. Booking</label>
                <div class="flex">
                  <input type="number" name="offer_duration[{{ $index }}]" placeholder="Qty" 
                    value="{{ $offer['min_booking_duration'] ?? '' }}"
                    class="w-20 border border-gray-200 rounded-l-xl px-4 py-3 text-sm">
                  <select name="offer_unit[{{ $index }}]" class="flex-1 border border-l-0 border-gray-200 rounded-r-xl px-3 py-3 text-sm bg-white">
                    <option value="weeks" {{ ($offer['duration_unit'] ?? '') == 'weeks' ? 'selected' : '' }}>Weeks</option>
                    <option value="months" {{ ($offer['duration_unit'] ?? '') == 'months' ? 'selected' : '' }}>Months</option>
                  </select>
                </div>
              </div>
              <div class="md:col-span-2 space-y-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase">Discount (%)</label>
                <div class="relative">
                  <input type="number" name="offer_discount[{{ $index }}]" placeholder="0" 
                    value="{{ $offer['discount'] ?? '' }}"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm">
                  <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">%</span>
                </div>
              </div>
              <div class="md:col-span-2 space-y-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase">Offer End Date</label>
                <input type="date" name="offer_end_date[{{ $index }}]" 
                  value="{{ $offer['end_date'] ?? '' }}"
                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none">
              </div>
              <div class="md:col-span-1 flex justify-center pb-2">
                <button type="button" class="remove-offer text-gray-300 hover:text-red-500">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2"/></svg>
                </button>
              </div>
            </div>
            <div class="mt-6 border-t border-gray-100 pt-4">
              <label class="text-[10px] font-bold text-gray-400 uppercase mb-2 block">Included Free Services</label>
              <div class="flex flex-wrap gap-4">
                @foreach(['Printing', 'Mounting', 'Design', 'Survey'] as $service)
                <label class="flex items-center gap-2 text-xs font-semibold text-gray-600">
                  <input type="checkbox" name="offer_services[{{ $index }}][]" value="{{ strtolower($service) }}" 
                    {{ in_array(strtolower($service), $offer['services'] ?? []) ? 'checked' : '' }}
                    class="accent-[#009A5C]"> {{ $service }}
                </label>
                @endforeach
              </div>
            </div>
          </div>
          @endforeach
        </div>
        <button type="button" id="add-offer-btn" class="bg-[#1A1A1A] text-white px-8 py-3 rounded-xl text-sm font-bold hover:scale-[1.02] active:scale-95 transition-transform flex items-center gap-2 w-fit">
          <span>+</span> Add Campaign Package
        </button>
      </div>
    </div>

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
</div>

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

  // Campaign Packages
  const addBtn = document.getElementById('add-offer-btn');
  const container = document.getElementById('offers-container');

  container.querySelectorAll('.remove-offer').forEach(btn => {
    btn.onclick = () => btn.closest('.group').remove();
  });

  addBtn.addEventListener('click', function() {
    const index = Date.now();
    const html = `
      <div class="group bg-gray-50/50 rounded-2xl border border-gray-100 p-6 transition-all hover:border-[#009A5C] hover:bg-white">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
          <div class="md:col-span-4 space-y-2">
            <label class="text-[10px] font-bold text-gray-400 uppercase">Offer Label</label>
            <input type="text" name="offer_name[${index}]" placeholder="e.g. Festival" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none">
          </div>
          <div class="md:col-span-3 space-y-2">
            <label class="text-[10px] font-bold text-gray-400 uppercase">Min. Booking</label>
            <div class="flex">
              <input type="number" name="offer_duration[${index}]" placeholder="Qty" class="w-20 border border-gray-200 rounded-l-xl px-4 py-3 text-sm">
              <select name="offer_unit[${index}]" class="flex-1 border border-l-0 border-gray-200 rounded-r-xl px-3 py-3 text-sm bg-white">
                <option value="weeks">Weeks</option>
                <option value="months">Months</option>
              </select>
            </div>
          </div>
          <div class="md:col-span-2 space-y-2">
            <label class="text-[10px] font-bold text-gray-400 uppercase">Discount (%)</label>
            <div class="relative">
              <input type="number" name="offer_discount[${index}]" placeholder="0" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm">
              <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">%</span>
            </div>
          </div>
          <div class="md:col-span-2 space-y-2">
            <label class="text-[10px] font-bold text-gray-400 uppercase">Offer End Date</label>
            <input type="date" name="offer_end_date[${index}]" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none">
          </div>
          <div class="md:col-span-1 flex justify-center pb-2">
            <button type="button" class="remove-offer text-gray-300 hover:text-red-500">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2"/></svg>
            </button>
          </div>
        </div>
        <div class="mt-6 border-t border-gray-100 pt-4">
          <label class="text-[10px] font-bold text-gray-400 uppercase mb-2 block">Included Free Services</label>
          <div class="flex flex-wrap gap-4">
            <label class="flex items-center gap-2 text-xs font-semibold text-gray-600"><input type="checkbox" name="offer_services[${index}][]" value="printing" class="accent-[#009A5C]"> Printing</label>
            <label class="flex items-center gap-2 text-xs font-semibold text-gray-600"><input type="checkbox" name="offer_services[${index}][]" value="mounting" class="accent-[#009A5C]"> Mounting</label>
            <label class="flex items-center gap-2 text-xs font-semibold text-gray-600"><input type="checkbox" name="offer_services[${index}][]" value="design" class="accent-[#009A5C]"> Design</label>
            <label class="flex items-center gap-2 text-xs font-semibold text-gray-600"><input type="checkbox" name="offer_services[${index}][]" value="survey" class="accent-[#009A5C]"> Survey</label>
          </div>
        </div>
      </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    container.lastElementChild.querySelector('.remove-offer').onclick = function() {
      this.closest('.group').remove();
    };
  });
});
</script>
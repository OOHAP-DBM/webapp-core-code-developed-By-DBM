<div class="p-8 space-y-8">
    {{-- <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
            <h3 class="text-xl font-bold text-gray-800">Rental Offering</h3>
        </div>
        <p class="text-xs text-gray-500 mb-8">Set your rental price for weekly bookings.</p>
        <div id="enable-weekly-toggle" class="mb-8">
            <div class="space-y-2">
                <label class="text-sm font-semibold text-gray-600">Enable Weekly Booking?</label>
                <div class="flex items-center gap-4">
                    @php
                        // Enable if parent hoarding has it enabled OR if parentHoading has it
                        $isWeeklyEnabled = old('enable_weekly_booking', $parentHoading->enable_weekly_booking ?? $parentHoarding->enable_weekly_booking ?? false);
                    @endphp
                    <input type="checkbox" id="enable_weekly_booking" name="enable_weekly_booking" value="1" 
                        class="w-5 h-5 rounded border-gray-300 text-[#009A5C] cursor-pointer" 
                        {{ $isWeeklyEnabled ? 'checked' : '' }}>
                    <span class="text-xs text-gray-500">Allow customers to book for weekly durations</span>
                </div>
            </div>
        </div>

        <div id="weekly-section" class="{{ $isWeeklyEnabled ? 'grid' : 'hidden' }} grid-cols-1 md:grid-cols-3 gap-8 mt-8">
            @foreach(['1', '2', '3'] as $week)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $week }} Week Price @if($week == 1)<span class="text-red-500">*</span>@endif</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                        <input type="number" name="weekly_price_{{$week}}" 
                            class="w-full rounded-xl border border-gray-200 pl-8 py-2.5.5 text-sm focus:border-[#009A5C] outline-none" 
                            value="{{ old('weekly_price_'.$week, $parentHoading->{"weekly_price_$week"} ?? $parentHoarding->{"weekly_price_$week"} ?? '') }}">
                    </div>
                </div>
            @endforeach
        </div>
    </div> --}}

   <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
                <h3 class="text-xl font-bold text-gray-800">Available Slot</h3>
            </div>
            <button type="button" id="add-slot-btn" class="bg-[#1A1A1A] text-white px-6 py-2 rounded-xl text-sm font-bold flex items-center gap-2 transition-transform hover:scale-105 active:scale-95">
                <span>+</span> Add slot
            </button>
        </div>

        <div id="slots-container" class="space-y-4">
            @php
                // Fetch from old input (array), then draft, then parent hoarding
                $slots = old('slots', $draft->slots ?? $parentHoarding->slots ?? []);
                
                // If empty, provide hardcoded defaults
                if(count($slots) == 0) {
                    $slots = [
                        ['slot_name' => 'Early Morning', 'start_time' => '04:00 AM', 'end_time' => '08:00 AM', 'is_active' => 0],
                        ['slot_name' => 'Morning', 'start_time' => '08:00 AM', 'end_time' => '12:00 PM', 'is_active' => 0],
                        ['slot_name' => 'Afternoon', 'start_time' => '12:00 PM', 'end_time' => '03:00 PM', 'is_active' => 0],
                        ['slot_name' => 'Evening', 'start_time' => '04:00 PM', 'end_time' => '08:00 PM', 'is_active' => 0],
                        ['slot_name' => 'Night', 'start_time' => '08:00 PM', 'end_time' => '12:00 AM', 'is_active' => 0],
                        ['slot_name' => 'Midnight', 'start_time' => '12:00 AM', 'end_time' => '04:00 AM', 'is_active' => 0]
                    ];
                }
            @endphp

            @foreach($slots as $index => $slot)
                @php
                    // Use data_get to safely handle both Objects (Eloquent) and Arrays (Old Input)
                    $currentName = data_get($slot, 'slot_name') ?? data_get($slot, 'name');
                    $startTime   = data_get($slot, 'start_time');
                    $endTime     = data_get($slot, 'end_time');
                    $isActive    = data_get($slot, 'is_active') ?? data_get($slot, 'active');
                    
                    // Format time for display if it's in H:i:s format from DB
                    if (strlen($startTime) == 8) {
                        $startTime = date("h:i A", strtotime($startTime));
                        $endTime   = date("h:i A", strtotime($endTime));
                    }
                @endphp
                
                <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-50 hover:border-gray-200 transition-all">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-gray-700">{{ $currentName }}</span>
                        <span class="text-sm font-bold text-[#0094FF]">{{ $startTime }} - {{ $endTime }}</span>
                    </div>
                    
                    <input type="hidden" name="slots[{{ $index }}][slot_name]" value="{{ $currentName }}">
                    <input type="hidden" name="slots[{{ $index }}][start_time]" value="{{ $startTime }}">
                    <input type="hidden" name="slots[{{ $index }}][end_time]" value="{{ $endTime }}">
                    
                    <div class="flex items-center gap-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="slots[{{ $index }}][is_active]" value="1" class="sr-only peer" {{ $isActive ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#009A5C] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
            <h3 class="text-xl font-bold text-gray-800">Long term Campaign Packages</h3>
        </div>
        <p class="text-xs text-gray-400 mb-8">Create specific inventory bundles (e.g., Annual Bulk Booking)</p>
        <div class="space-y-6">
            <div id="offers-container" class="space-y-4"></div>
            <input type="hidden" name="offers_json" id="offers_json" value="{{ json_encode(old('offers_json', $draft->packages ?? $draft->packages ?? [])) }}">
            <button type="button" id="add-offer-btn" class="bg-[#1A1A1A] text-white px-8 py-2.5 rounded-xl text-sm font-bold hover:scale-[1.02] active:scale-95 transition-transform flex items-center gap-2 w-fit">
                <span>+</span> Add Campaign Package
            </button>
        </div>
    </div> --}}
     @include('components.hoardings.create.package')


    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
            <h3 class="text-xl font-bold text-gray-800">Services Includes</h3>
        </div>
        @php
            $graphicsCharge = old('graphics_charge', $parentHoading->graphics_charge ?? $parentHoarding->graphics_charge ?? 0);
            $isGraphicsFree = $graphicsCharge <= 0;
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
            <div class="space-y-4 p-4 border border-gray-50 rounded-2xl">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-bold text-gray-700">Graphics Included ?</label>
                    <div class="flex items-center gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="graphics_included" value="1" class="hidden peer toggle-service" {{ $isGraphicsFree ? 'checked' : '' }}>
                            <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">Yes</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="graphics_included" value="0" class="hidden peer toggle-service" {{ !$isGraphicsFree ? 'checked' : '' }}>
                            <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">No</span>
                        </label>
                    </div>
                </div>
                <div id="graphics-box" class="transition-all {{ $isGraphicsFree ? 'opacity-40 pointer-events-none' : '' }}">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Graphics Charge</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                        <input type="number" name="graphics_charge" value="{{ $graphicsCharge }}" class="w-full border border-gray-200 rounded-xl pl-8 py-2.5 text-sm outline-none" {{ $isGraphicsFree ? 'disabled' : '' }}>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="slot-modal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <div class="flex justify-end p-6 pb-0">
                <button type="button" onclick="closeSlotModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="px-10 pb-10">
                <h3 class="text-xl font-bold text-center text-gray-800 mb-8">Add new slot</h3>
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-700">Enter slot name</label>
                        <input type="text" id="modal-slot-name" placeholder="E.g Morning" class="w-full border border-gray-300 rounded-2xl px-5 py-4 focus:border-[#009A5C] outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" id="modal-slot-from" placeholder="From" class="time-picker w-full border border-gray-300 rounded-2xl px-5 py-4 outline-none cursor-pointer">
                        <input type="text" id="modal-slot-to" placeholder="To" class="time-picker w-full border border-gray-300 rounded-2xl px-5 py-4 outline-none cursor-pointer">
                    </div>
                    <button type="button" id="confirm-add-slot" class="w-full bg-[#E0E0E0] text-gray-400 font-bold py-5 rounded-2xl transition-all">Add</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- 2. Service Charge Toggle ---
    document.querySelectorAll('.toggle-service').forEach(radio => {
        radio.addEventListener('change', function() {
            const container = this.closest('.space-y-4').querySelector('#graphics-box');
            const input = container.querySelector('input');
            if (this.value === "1") {
                container.classList.add('opacity-40', 'pointer-events-none');
                input.disabled = true;
                input.value = '0';
            } else {
                container.classList.remove('opacity-40', 'pointer-events-none');
                input.disabled = false;
            }
        });
    });

    // --- 3. Campaign Packages (Pre-fill logic) ---
    // function renderOffer(data = {}) {
    //     const index = Date.now() + Math.floor(Math.random() * 1000);
    //     const html = `
    //         <div class="group bg-gray-50/50 rounded-2xl border border-gray-100 p-6 transition-all hover:border-[#009A5C] hover:bg-white">
    //             <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
    //                 <div class="md:col-span-4 space-y-2">
    //                     <label class="text-[10px] font-bold text-gray-400 uppercase">Offer Label</label>
    //                     <input type="text" class="offer_name w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none" value="${data.name || ''}" placeholder="e.g. Festival">
    //                 </div>
    //                 <div class="md:col-span-3 space-y-2">
    //                     <label class="text-[10px] font-bold text-gray-400 uppercase">Min. Booking</label>
    //                     <div class="flex">
    //                         <input type="number" class="offer_duration w-20 border border-gray-200 rounded-l-xl px-4 py-2.5 text-sm" value="${data.duration || ''}" placeholder="Qty">
    //                         <select class="offer_unit flex-1 border border-l-0 border-gray-200 rounded-r-xl px-3 py-2.5 text-sm bg-white">
    //                             <option value="weeks" ${data.unit === 'weeks' ? 'selected' : ''}>Weeks</option>
    //                             <option value="months" ${data.unit === 'months' ? 'selected' : ''}>Months</option>
    //                         </select>
    //                     </div>
    //                 </div>
    //                 <div class="md:col-span-2 space-y-2">
    //                     <label class="text-[10px] font-bold text-gray-400 uppercase">Discount (%)</label>
    //                     <div class="relative">
    //                         <input type="number" class="offer_discount w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm" value="${data.discount || ''}" placeholder="0">
    //                         <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">%</span>
    //                     </div>
    //                 </div>
    //                 <div class="md:col-span-2 space-y-2">
    //                     <label class="text-[10px] font-bold text-gray-400 uppercase">Offer End Date</label>
    //                     <input type="date" class="offer_end_date w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none" value="${data.end_date || ''}">
    //                 </div>
    //                 <div class="md:col-span-1 flex justify-center pb-2">
    //                     <button type="button" class="remove-offer text-gray-300 hover:text-red-500">
    //                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2"/></svg>
    //                     </button>
    //                 </div>
    //             </div>
    //         </div>`;
    //     offersContainer.insertAdjacentHTML('beforeend', html);
    //     offersContainer.lastElementChild.querySelector('.remove-offer').onclick = function() { this.closest('.group').remove(); };
    // }

    // Load existing packages from hidden JSON input
    // if (offersJsonInput.value) {
    //     try {
    //         const existing = JSON.parse(offersJsonInput.value);
    //         existing.forEach(pkg => renderOffer(pkg));
    //     } catch (e) { console.error("Invalid JSON in offers_json"); }
    // }

    document.getElementById('add-offer-btn').addEventListener('click', () => renderOffer());

    // --- 4. Form Submission Logic ---
    // const form = document.querySelector('form');
    // form?.addEventListener('submit', function() {
    //     const offers = [];
    //     offersContainer.querySelectorAll('.group').forEach(group => {
    //         offers.push({
    //             name: group.querySelector('.offer_name').value,
    //             duration: group.querySelector('.offer_duration').value,
    //             unit: group.querySelector('.offer_unit').value,
    //             discount: group.querySelector('.offer_discount').value,
    //             end_date: group.querySelector('.offer_end_date').value
    //         });
    //     });
    //     offersJsonInput.value = JSON.stringify(offers);
    // });

    // --- 5. Slot Picker (Standard Init) ---
    flatpickr(".time-picker", { enableTime: true, noCalendar: true, dateFormat: "h:i K" });


    // --- 6. Slot Modal Logic ---
    const slotModal = document.getElementById('slot-modal');
    const addSlotBtn = document.getElementById('add-slot-btn');
    const confirmAddSlotBtn = document.getElementById('confirm-add-slot');
    const slotsContainer = document.getElementById('slots-container');

    // Input fields inside modal
    const modalSlotName = document.getElementById('modal-slot-name');
    const modalSlotFrom = document.getElementById('modal-slot-from');
    const modalSlotTo = document.getElementById('modal-slot-to');

    // Open Modal
    addSlotBtn?.addEventListener('click', () => {
        slotModal.classList.remove('hidden');
    });

    // Close Modal Function (Global so the 'X' button can call it)
    window.closeSlotModal = function() {
        slotModal.classList.add('hidden');
        modalSlotName.value = '';
        modalSlotFrom.value = '';
        modalSlotTo.value = '';
    };

    // Handle "Add" Button click in modal
    confirmAddSlotBtn?.addEventListener('click', function() {
        const name = modalSlotName.value;
        const from = modalSlotFrom.value;
        const to = modalSlotTo.value;

        if (!name || !from || !to) {
            alert('Please fill all fields');
            return;
        }

        const index = Date.now(); // Unique index for the new input names

        const html = `
            <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 hover:border-gray-200 transition-all">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-gray-700">${name}</span>
                    <span class="text-sm font-bold text-[#0094FF]">${from} - ${to}</span>
                </div>
                
                <input type="hidden" name="slots[${index}][slot_name]" value="${name}">
                <input type="hidden" name="slots[${index}][start_time]" value="${from}">
                <input type="hidden" name="slots[${index}][end_time]" value="${to}">
                
                <div class="flex items-center gap-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="slots[${index}][is_active]" value="1" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#009A5C] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                    </label>
                </div>
            </div>`;

        slotsContainer.insertAdjacentHTML('beforeend', html);
        closeSlotModal();
    });

    // Enable/Disable "Add" button based on input
    [modalSlotName, modalSlotFrom, modalSlotTo].forEach(el => {
        el.addEventListener('input', () => {
            if (modalSlotName.value && modalSlotFrom.value && modalSlotTo.value) {
                confirmAddSlotBtn.classList.remove('bg-[#E0E0E0]', 'text-gray-400');
                confirmAddSlotBtn.classList.add('bg-[#1A1A1A]', 'text-white');
            } else {
                confirmAddSlotBtn.classList.add('bg-[#E0E0E0]', 'text-gray-400');
                confirmAddSlotBtn.classList.remove('bg-[#1A1A1A]', 'text-white');
            }
        });
    });
});
</script>
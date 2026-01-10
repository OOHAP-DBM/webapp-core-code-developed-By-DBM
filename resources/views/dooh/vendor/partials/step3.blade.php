<div class="bg-white rounded-3xl shadow-sm border border-gray-100 mb-6">
    <div class="p-8 space-y-8">

        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
             <div class="flex items-center gap-3 mb-4">
                <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
                <h3 class="text-xl font-bold text-gray-800">Slot Pricing</h3>
            </div>
            <p class="text-xs text-gray-500 mb-8">Set your display price per slot and video length.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-600">Display Price per slot <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                        <input type="number" name="price_per_slot" placeholder="Enter Price" required class="w-full border border-gray-200 rounded-xl pl-8 pr-20 py-3.5 focus:border-[#009A5C] outline-none transition-all">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-400">Times/hr</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-600">Video Length <span class="text-red-500">*</span></label>
                    <select name="video_length" required class="w-full border border-gray-200 rounded-xl px-4 py-3.5 focus:border-[#009A5C] outline-none transition-all cursor-pointer">
                        {{-- <option value="">Select Video Length</option>
                        <option value="10">10 Seconds</option>--}}
                        <option value="15">5 Seconds</option> 
                        <option value="30">15 Seconds</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
                <h3 class="text-xl font-bold text-gray-800">Rental Offering</h3>
            </div>
            <p class="text-xs text-gray-500 mb-8">Set your rental price for monthly or weekly bookings.</p>

            <div class="flex gap-8 border-b border-gray-100 mb-8">
                <button type="button" id="toggle-monthly" class="pb-2 border-b-2 border-[#009A5C] text-[#009A5C] text-sm font-bold uppercase tracking-wider">Monthly</button>
                <button type="button" id="toggle-weekly" class="pb-2 border-b-2 border-transparent text-gray-400 text-sm font-bold uppercase tracking-wider">Weekly</button>
            </div>

            <!-- Monthly Price Section (default visible) -->
            <div id="monthly-section" class="grid grid-cols-1 md:grid-cols-2 gap-8 items-end">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-600">Base Monthly Price <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                        <input type="number" name="base_monthly_price" placeholder="Enter base monthly price" required min="1" step="0.01" class="w-full border border-gray-200 rounded-xl pl-8 pr-20 py-3.5 focus:border-[#009A5C] outline-none transition-all">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-600">Monthly Offered Price</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                        <input type="number" name="monthly_offered_price" placeholder="Enter offered monthly price (if any)" min="1" step="0.01" class="w-full border border-gray-200 rounded-xl pl-8 pr-20 py-3.5 focus:border-[#009A5C] outline-none transition-all">
                    </div>
                </div>
            </div>

            <!-- Weekly Price Section (hidden by default, toggled) -->
            <div id="enable-weekly-toggle" class="hidden mb-8">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-600">Enable Weekly Booking?</label>
                    <div class="flex items-center gap-4">
                        <input type="checkbox" id="enable_weekly_booking" name="enable_weekly_booking" value="1" class="w-5 h-5 rounded border-gray-300 text-[#009A5C] cursor-pointer">
                        <span class="text-xs text-gray-500">Allow customers to book for weekly durations</span>
                    </div>
                </div>
            </div>
            <div id="weekly-section" class="hidden grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        1 Week Price <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                        <input type="number" name="weekly_price_1"
                            class="w-full rounded-xl border border-gray-200 pl-8 py-3.5 text-sm focus:border-[#009A5C] outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">2 Weeks Price</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                        <input type="number" name="weekly_price_2"
                            class="w-full rounded-xl border border-gray-200 pl-8 py-3.5 text-sm focus:border-[#009A5C] outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">3 Weeks Price</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                        <input type="number" name="weekly_price_3"
                            class="w-full rounded-xl border border-gray-200 pl-8 py-3.5 text-sm focus:border-[#009A5C] outline-none">
                    </div>
                </div>
            </div>
            

        
        </div>

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
                    $defaultSlots = [
                        ['name' => 'Early Morning', 'time' => '4:00 AM - 8:00 AM'],
                        ['name' => 'Morning', 'time' => '8:00 AM - 12:00 PM'],
                        ['name' => 'Afternoon', 'time' => '12:00 PM - 3:00 PM'],
                        ['name' => 'Evening', 'time' => '4:00 PM - 8:00 PM'],
                        ['name' => 'Night', 'time' => '8:00 PM - 12:00 AM'],
                        ['name' => 'Midnight', 'time' => '12:00 AM - 4:00 AM']
                    ];
                @endphp

                @foreach($defaultSlots as $index => $slot)
                    <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-50 hover:border-gray-200 transition-all">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-gray-700">{{ $slot['name'] }}</span>
                            <span class="text-sm font-bold text-[#0094FF]">{{ $slot['time'] }}</span>
                        </div>
                        
                        @php 
                            $times = explode(' - ', $slot['time']); 
                        @endphp
                        <input type="hidden" name="slots[{{ $index }}][name]" value="{{ $slot['name'] }}">
                        <input type="hidden" name="slots[{{ $index }}][start_time]" value="{{ $times[0] }}">
                        <input type="hidden" name="slots[{{ $index }}][end_time]" value="{{ $times[1] }}">

                        <div class="flex items-center gap-4">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="slots[{{ $index }}][active]" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#009A5C] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>



        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
                <h3 class="text-xl font-bold text-gray-800">Long term Campaign Packages</h3>
            </div>
            <p class="text-xs text-gray-400 mb-8">Create specific inventory bundles (e.g., Annual Bulk Booking)</p>

            <div class="space-y-6">
                <div id="offers-container" class="space-y-4"></div>
                <input type="hidden" name="offers_json" id="offers_json">
                <button type="button" id="add-offer-btn" class="bg-[#1A1A1A] text-white px-8 py-3 rounded-xl text-sm font-bold hover:scale-[1.02] active:scale-95 transition-transform flex items-center gap-2 w-fit">
                    <span>+</span> Add Campaign Package
                </button>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
                <h3 class="text-xl font-bold text-gray-800">Services Includes</h3>
            </div>
            <p class="text-xs text-gray-400 mb-8">Set Services provided for free (Yes) or as additional charges (No)</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
                <div class="space-y-4 p-4 border border-gray-50 rounded-2xl">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-bold text-gray-700">Graphics Included (Free)?</label>
                        <div class="flex items-center gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="graphics_included" value="1" class="hidden peer toggle-service" data-target="graphics-box">
                                <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">Yes</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="graphics_included" value="0" class="hidden peer toggle-service" data-target="graphics-box" checked>
                                <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">No</span>
                            </label>
                        </div>
                    </div>
                    <div id="graphics-box" class="transition-all">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Graphics Charge</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                            <input type="number" name="graphics_charge" placeholder="0.00" class="w-full border border-gray-200 rounded-xl pl-8 py-3 text-sm outline-none">
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
                            <input type="text" id="modal-slot-name" placeholder="E.g Morning" class="w-full border border-gray-300 rounded-2xl px-5 py-4 focus:border-[#009A5C] outline-none transition-all">
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
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Variable Declarations ---
        const modal = document.getElementById('slot-modal');
        const addSlotBtn = document.getElementById('add-slot-btn');
        const confirmSlotBtn = document.getElementById('confirm-add-slot');
        const slotsContainer = document.getElementById('slots-container');
        const addOfferBtn = document.getElementById('add-offer-btn');
        const offersContainer = document.getElementById('offers-container');
        
        const slotName = document.getElementById('modal-slot-name');
        const slotFrom = document.getElementById('modal-slot-from');
        const slotTo = document.getElementById('modal-slot-to');

        // --- 2. Initialize Plugins ---
        flatpickr(".time-picker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K",
            minuteIncrement: 15
        });

        // --- 3. Slot Modal Logic ---
        addSlotBtn.onclick = () => {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        window.closeSlotModal = function() {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            slotName.value = ''; slotFrom.value = ''; slotTo.value = '';
        };

        [slotName, slotFrom, slotTo].forEach(input => {
            input.addEventListener('input', () => {
                const isReady = slotName.value && slotFrom.value && slotTo.value;
                confirmSlotBtn.className = isReady 
                    ? "w-full bg-[#009A5C] text-white font-bold py-5 rounded-2xl shadow-md transition-all cursor-pointer"
                    : "w-full bg-[#E0E0E0] text-gray-400 font-bold py-5 rounded-2xl transition-all";
            });
        });

        confirmSlotBtn.onclick = () => {
            if(!slotName.value || !slotFrom.value || !slotTo.value) return;
            
            // Create a unique index based on current timestamp
            const index = Date.now(); 
            
            const html = `
                <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 group transition-all">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-gray-700">${slotName.value}</span>
                        <span class="text-sm font-bold text-[#0094FF]">${slotFrom.value} - ${slotTo.value}</span>
                    </div>

                    <input type="hidden" name="slots[${index}][name]" value="${slotName.value}">
                    <input type="hidden" name="slots[${index}][start_time]" value="${slotFrom.value}">
                    <input type="hidden" name="slots[${index}][end_time]" value="${slotTo.value}">

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="slots[${index}][active]" value="1" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#009A5C] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                    </label>
                </div>`;
                
            slotsContainer.insertAdjacentHTML('beforeend', html);
            closeSlotModal();
        };
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Weekly Pricing Toggle (show toggle on Weekly tab, show section if enabled)
        /* ===============================
       1. MONTHLY / WEEKLY TOGGLE
    =============================== */

    const weeklyBtn        = document.getElementById('toggle-weekly');
    const monthlyBtn       = document.getElementById('toggle-monthly');
    const weeklySection    = document.getElementById('weekly-section');
    const monthlySection   = document.getElementById('monthly-section');
    const enableWeekly     = document.getElementById('enable_weekly_booking');
    const weeklyToggleBox  = document.getElementById('enable-weekly-toggle');

    const showWeekly = () => {
        if (weeklyToggleBox) weeklyToggleBox.classList.remove('hidden');
        monthlySection?.classList.add('hidden');
        monthlySection?.classList.remove('grid');

        if (enableWeekly?.checked) {
            weeklySection?.classList.remove('hidden');
            weeklySection?.classList.add('grid');
        } else {
            weeklySection?.classList.add('hidden');
            weeklySection?.classList.remove('grid');
        }
    };

    const showMonthly = () => {
        weeklyToggleBox?.classList.add('hidden');
        weeklySection?.classList.add('hidden');
        weeklySection?.classList.remove('grid');

        monthlySection?.classList.remove('hidden');
        monthlySection?.classList.add('grid');
    };

    weeklyBtn?.addEventListener('click', showWeekly);
    monthlyBtn?.addEventListener('click', showMonthly);

    enableWeekly?.addEventListener('change', () => {
        if (enableWeekly.checked) {
            weeklySection?.classList.remove('hidden');
            weeklySection?.classList.add('grid');
        } else {
            weeklySection?.classList.add('hidden');
            weeklySection?.classList.remove('grid');
        }
    });

        // 2. Service Toggle Logic (Mounting, Lighting, Printing, Graphics independent)
        const serviceToggles = [
            { name: 'graphics_included', box: 'graphics-box', type: 'all' },
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
                        el.value = '';
                    });
                } else {
                    target.classList.remove('opacity-40', 'pointer-events-none');
                    target.querySelectorAll('input, select').forEach(el => el.disabled = false);
                }
                
            }
            radios.forEach(radio => {
                radio.addEventListener('change', updateBox);
                if (radio.checked) updateBox();
            });
        });

        // 3. Campaign Packages Logic
        const addBtn = document.getElementById('add-offer-btn');
        const container = document.getElementById('offers-container');

        addBtn.addEventListener('click', function() {
            const index = Date.now();
            const html = `
                <div class="group bg-gray-50/50 rounded-2xl border border-gray-100 p-6 transition-all hover:border-[#009A5C] hover:bg-white">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Offer Label</label>
                            <input type="text" class="offer_name w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none" placeholder="e.g. Festival">
                        </div>
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Min. Booking</label>
                            <div class="flex">
                                <input type="number" class="offer_duration w-20 border border-gray-200 rounded-l-xl px-4 py-3 text-sm" placeholder="Qty">
                                <select class="offer_unit flex-1 border border-l-0 border-gray-200 rounded-r-xl px-3 py-3 text-sm bg-white">
                                    <option value="weeks">Weeks</option>
                                    <option value="months">Months</option>
                                </select>
                            </div>
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Discount (%)</label>
                            <div class="relative">
                                <input type="number" class="offer_discount w-full border border-gray-200 rounded-xl px-4 py-3 text-sm" placeholder="0">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">%</span>
                            </div>
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Offer End Date</label>
                            <input type="date" class="offer_end_date w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none">
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
                            ${['Printing', 'Mounting', 'Design', 'Survey'].map(s => `
                                <label class="flex items-center gap-2 text-xs font-semibold text-gray-600">
                                    <input type="checkbox" class="offer_services accent-[#009A5C]" value="${s.toLowerCase()}"> ${s}
                                </label>
                            `).join('')}
                        </div>
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
            const last = container.lastElementChild;
            last.querySelector('.remove-offer').onclick = () => last.remove();
        });

        // On form submit, serialize offers to JSON
        const form = container.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const offers = [];
                container.querySelectorAll('.group').forEach(group => {
                    offers.push({
                        name: group.querySelector('.offer_name')?.value || '',
                        duration: group.querySelector('.offer_duration')?.value || '',
                        unit: group.querySelector('.offer_unit')?.value || '',
                        discount: group.querySelector('.offer_discount')?.value || '',
                        end_date: group.querySelector('.offer_end_date')?.value || '',
                        services: Array.from(group.querySelectorAll('.offer_services:checked')).map(cb => cb.value)
                    });
                });
                document.getElementById('offers_json').value = JSON.stringify(offers);
            });
        }
    });
</script>

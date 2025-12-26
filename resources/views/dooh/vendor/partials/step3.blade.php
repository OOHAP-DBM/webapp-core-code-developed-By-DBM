<div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
    <div class="flex items-center gap-3 mb-8">
        <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
        <h3 class="text-xl font-bold text-gray-800">Pricing</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-600">Display Price per 30 second <span class="text-red-500">*</span></label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                <input type="number" name="price_per_30s" placeholder="Enter Price" required class="w-full border border-gray-200 rounded-xl pl-8 pr-20 py-3.5 focus:border-[#009A5C] outline-none transition-all">
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-400">Times/hr</span>
            </div>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-600">Video Length <span class="text-red-500">*</span></label>
            <select name="video_length" required class="w-full border border-gray-200 rounded-xl px-4 py-3.5 focus:border-[#009A5C] outline-none transition-all cursor-pointer">
                <option value="">Select Video Length</option>
                <option value="10">10 Seconds</option>
                <option value="15">15 Seconds</option>
                <option value="30">30 Seconds</option>
            </select>
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

        @foreach($defaultSlots as $slot)
        <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-50 hover:border-gray-200 transition-all">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-gray-700">{{ $slot['name'] }}</span>
                <span class="text-sm font-bold text-[#0094FF]">{{ $slot['time'] }}</span>
            </div>
            <div class="flex items-center gap-4">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="slots[]" value="{{ $slot['name'] }}" class="sr-only peer">
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
        <h3 class="text-xl font-bold text-gray-800">Rental Offering</h3>
    </div>
    <p class="text-xs text-gray-500 mb-8">You can set price for monthly and weekly rental for your listing</p>

    <div class="flex gap-8 border-b border-gray-100 mb-8">
        <button type="button" class="pb-2 border-b-2 border-[#009A5C] text-[#009A5C] text-sm font-bold uppercase tracking-wider">Monthly</button>
        <button type="button" class="pb-2 border-transparent text-gray-400 text-sm font-bold uppercase tracking-wider hover:text-gray-600">Weekly</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-end">
        <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-600">Base Monthly Price <span class="text-red-500">*</span></label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                <input type="number" name="base_monthly_price" placeholder="PM" class="w-full border border-gray-200 rounded-xl px-8 py-3.5 focus:border-[#009A5C] outline-none transition-all">
            </div>
        </div>
        <div class="flex items-center gap-3 p-4">
            <input type="checkbox" name="offer_discount" class="w-5 h-5 rounded border-gray-300 text-[#009A5C] cursor-pointer">
            <label class="text-sm font-bold text-gray-700">Offering Discount on Base Monthly Price?</label>
        </div>
    </div>
</div>

<div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
        <h3 class="text-xl font-bold text-gray-800">Long term Campaign Packages</h3>
    </div>
    <p class="text-xs text-gray-400 mb-8">Create specific inventory bundles (e.g., Election Special or Annual Bulk Booking)</p>

    <div class="space-y-6">
        <div id="offers-container" class="space-y-4">
            </div>

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
    <p class="text-xs text-gray-400 mb-8">Set Services you wish to provide / not provide with the base rental</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <label class="text-sm font-bold text-gray-700">Graphics Included?</label>
                <div class="flex items-center gap-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="graphics_included" value="1" class="hidden peer">
                        <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white peer-checked:border-[#009A5C] transition-all">Yes</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="graphics_included" value="0" class="hidden peer" checked>
                        <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white peer-checked:border-[#009A5C] transition-all">No</span>
                    </label>
                </div>
            </div>
            
            <div id="graphics-price-container" class="space-y-2 opacity-50 transition-opacity">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-tighter">Enter Graphics Price</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                    <input type="number" name="graphics_price" id="graphics_price" placeholder="Enter Graphics Price" disabled
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-8 pr-4 py-3.5 outline-none text-sm transition-all focus:border-[#009A5C] focus:bg-white disabled:cursor-not-allowed">
                </div>
            </div>
        </div>

        <div class="space-y-6 pt-2 md:pt-0">
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">Survey Charge?</label>
                <p class="text-[10px] text-gray-400 italic">One Time Survey Charge</p>
                <div class="relative mt-4">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                    <input type="number" name="survey_charge" placeholder="Per survey charge" 
                        class="w-full border border-gray-200 rounded-xl pl-8 pr-4 py-3.5 outline-none text-sm focus:border-[#009A5C] transition-all">
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
        if(!slotName.value || !slotFrom.value) return;
        const html = `
            <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 group transition-all">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-gray-700">${slotName.value}</span>
                    <span class="text-sm font-bold text-[#0094FF]">${slotFrom.value} - ${slotTo.value}</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="slots[]" value="${slotName.value}" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#009A5C] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                </label>
            </div>`;
        slotsContainer.insertAdjacentHTML('beforeend', html);
        closeSlotModal();
    };

    // --- 4. Dynamic Campaign Packages (DOOH Style) ---
    addOfferBtn.addEventListener('click', function() {
        const offerHtml = `
            <div class="group bg-gray-50/50 rounded-2xl border border-gray-100 p-6 transition-all hover:border-[#009A5C] hover:bg-white animate-in fade-in slide-in-from-top-2">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Offer Label</label>
                        <input type="text" name="offer_name[]" placeholder="e.g. Festival Season" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#009A5C] outline-none bg-white">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Min. Booking</label>
                        <div class="flex">
                            <input type="number" name="offer_duration[]" placeholder="Qty" class="w-20 border border-gray-200 rounded-l-xl px-4 py-3 text-sm focus:border-[#009A5C] outline-none bg-white">
                            <select name="offer_unit[]" class="flex-1 border border-l-0 border-gray-200 rounded-r-xl px-3 py-3 text-sm outline-none bg-white">
                                <option value="months">Months</option>
                                <option value="weeks">Weeks</option>
                                <option value="days">Days</option>
                            </select>
                        </div>
                    </div>
                    <div class="md:col-span-3 space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Discount (%)</label>
                        <div class="relative">
                            <input type="number" name="offer_discount[]" placeholder="0" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-[#009A5C] outline-none bg-white">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">%</span>
                        </div>
                    </div>
                    <div class="md:col-span-1 flex justify-center pb-2">
                        <button type="button" class="remove-offer text-gray-300 hover:text-red-500 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            </div>`;
        offersContainer.insertAdjacentHTML('beforeend', offerHtml);
        const lastOffer = offersContainer.lastElementChild;
        lastOffer.querySelector('.remove-offer').onclick = () => lastOffer.remove();
    });

    // --- 5. Graphics Logic ---
    const graphicsRadios = document.querySelectorAll('input[name="graphics_included"]');
    const graphicsPriceDiv = document.getElementById('graphics-price-container');
    const graphicsInput = document.getElementById('graphics_price');

    graphicsRadios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            const isYes = e.target.value === "1";
            graphicsPriceDiv.classList.toggle('opacity-50', !isYes);
            graphicsInput.disabled = !isYes;
            graphicsInput.classList.toggle('bg-gray-50', !isYes);
            if(!isYes) graphicsInput.value = '';
        });
    });
});
</script>
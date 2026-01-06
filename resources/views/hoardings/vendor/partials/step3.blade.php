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
                <input type="checkbox" id="enable_weekly_booking" name="enable_weekly_booking" value="1" class="w-5 h-5 rounded border-gray-300 text-[#009A5C] cursor-pointer">
                <span class="text-xs text-gray-500">Allow customers to book for weekly durations</span>
            </div>
        </div>
    </div>

    <div id="weekly-prices-section" class="hidden grid-cols-1 md:grid-cols-3 gap-8 mt-6">
        <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-600">Price for 1 Week <span class="text-red-500">*</span></label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                <input type="number" name="weekly_price_1" placeholder="1 Week Price" class="w-full border border-gray-200 rounded-xl px-8 py-3.5 focus:border-[#009A5C] outline-none">
            </div>
        </div>
        <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-600">Price for 2 Weeks</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                <input type="number" name="weekly_price_2" placeholder="2 Weeks Price" class="w-full border border-gray-200 rounded-xl px-8 py-3.5 focus:border-[#009A5C] outline-none">
            </div>
        </div>
        <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-600">Price for 3 Weeks</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                <input type="number" name="weekly_price_3" placeholder="3 Weeks Price" class="w-full border border-gray-200 rounded-xl px-8 py-3.5 focus:border-[#009A5C] outline-none">
            </div>
        </div>
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

        <div class="space-y-4 p-4 border border-gray-50 rounded-2xl">
            <div class="flex items-center justify-between">
                <label class="text-sm font-bold text-gray-700">Printing Included (Free)?</label>
                <div class="flex items-center gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="printing_included" value="1" class="hidden peer toggle-service" data-target="printing-box">
                        <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">Yes</span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="printing_included" value="0" class="hidden peer toggle-service" data-target="printing-box" checked>
                        <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">No</span>
                    </label>
                </div>
            </div>
            <div id="printing-box" class="space-y-3 transition-all">
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                    <input type="number" name="printing_charge" placeholder="Price" class="w-full border border-gray-200 rounded-xl pl-8 py-3 text-sm outline-none">
                </div>
                <select name="printing_material_type" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none bg-white">
                    <option value="">Select Material</option>
                    <option value="flex">Flex</option>
                    <option value="vinyl">Vinyl</option>
                </select>
            </div>
        </div>

        <div class="space-y-4 p-4 border border-gray-50 rounded-2xl">
            <div class="flex items-center justify-between">
                <label class="text-sm font-bold text-gray-700">Mounting Included (Free)?</label>
                <div class="flex items-center gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="mounting_included" value="1" class="hidden peer toggle-service" data-target="mounting-box">
                        <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">Yes</span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="mounting_included" value="0" class="hidden peer toggle-service" data-target="mounting-box" checked>
                        <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">No</span>
                    </label>
                </div>
            </div>
            <div id="lighting-box" class="transition-all">
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                    <input type="number" name="lighting_charge" placeholder="Price" class="w-full border border-gray-200 rounded-xl pl-8 py-3 text-sm outline-none">
                </div>
            </div>
             <div class="flex items-center justify-between mt-20">
                <label class="text-sm font-bold text-gray-700">Lighting Included (Free)?</label>
                <div class="flex items-center gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="lighting_included" value="1" class="hidden peer toggle-service" data-target="lighting-box">
                        <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">Yes</span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="lighting_included" value="0" class="hidden peer toggle-service" data-target="lighting-box" checked>
                        <span class="px-4 py-1.5 rounded-lg border border-gray-200 text-xs font-bold peer-checked:bg-[#009A5C] peer-checked:text-white transition-all">No</span>
                    </label>
                </div>
            </div>
            <div id="mounting-box" class="transition-all">
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                    <input type="number" name="mounting_charge" placeholder="Price" class="w-full border border-gray-200 rounded-xl pl-8 py-3 text-sm outline-none">
                </div>
            </div>
        </div>

        <div class="bg-gray-50/50 p-6 rounded-2xl border border-dashed border-gray-200 space-y-4">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Fixed Charges</h4>
            <div class="bg-white border border-gray-100 rounded-[1.5rem] p-8 space-y-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-gray-700">Remounting Service Charge?</h4>
                        <p class="text-xs text-gray-400">Includes Mounting + Printing</p>
                    </div>
                    <div class="relative w-full md:w-72">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                        <input type="number" name="remounting_charge" placeholder="Per remounting charge" class="w-full border border-gray-200 rounded-xl pl-8 py-3 text-sm outline-none">
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-gray-700">Survey Charge?</h4>
                        <p class="text-xs text-gray-400">One Time Survey Charge</p>
                    </div>
                    <div class="relative w-full md:w-72">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">₹</span>
                        <input type="number" name="survey_charge" placeholder="Per survey charge" class="w-full border border-gray-200 rounded-xl pl-8 py-3 text-sm outline-none">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Weekly Pricing Toggle
    const enableWeekly = document.getElementById('enable_weekly_booking');
    const weeklyPrices = document.getElementById('weekly-prices-section');
    if (enableWeekly) {
        enableWeekly.addEventListener('change', function() {
            weeklyPrices.classList.toggle('hidden', !this.checked);
            weeklyPrices.classList.toggle('grid', this.checked);
        });
    }

    // 2. Universal Service Toggle Logic
    // Logic: YES (1) = Free (Disable/Hide Price) | NO (0) = Charges (Enable/Show Price)
    const toggles = document.querySelectorAll('.toggle-service');
    
    function handleToggle(radio) {
        const target = document.getElementById(radio.dataset.target);
        const isFree = radio.value === "1";
        
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

    toggles.forEach(radio => {
        if (radio.checked) handleToggle(radio);
        radio.addEventListener('change', () => handleToggle(radio));
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
                        <input type="text" name="offer_name[${index}]" placeholder="e.g. Festival" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Min. Booking</label>
                        <div class="flex">
                            <input type="number" name="offer_duration[${index}]" placeholder="Qty" class="w-20 border border-gray-200 rounded-l-xl px-4 py-3 text-sm">
                            <select name="offer_unit[${index}]" class="flex-1 border border-l-0 border-gray-200 rounded-r-xl px-3 py-3 text-sm bg-white">
                                <option value="weeks">Weeks</option>
                                <option value="months">Months</option>
                            </select>
                        </div>
                    </div>
                    <div class="md:col-span-3 space-y-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Discount (%)</label>
                        <div class="relative">
                            <input type="number" name="offer_discount[${index}]" placeholder="0" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">%</span>
                        </div>
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
                                <input type="checkbox" name="offer_services[${index}][]" value="${s.toLowerCase()}" class="accent-[#009A5C]"> ${s}
                            </label>
                        `).join('')}
                    </div>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        const last = container.lastElementChild;
        last.querySelector('.remove-offer').onclick = () => last.remove();
    });
});
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<div id="nagarModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-sm transform transition-all border border-gray-100">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
            <h2 class="text-xl font-bold text-gray-800">Permit Details</h2>
        </div>
        
        <div class="space-y-4">
            <div>
                <label class="text-xs font-bold text-gray-400 uppercase ml-1">Permit Number</label>
                <input type="text" id="permitNumberInput" 
                    class="w-full border border-gray-200 rounded-2xl px-4 py-3 mt-1 outline-none focus:border-[#009A5C] focus:ring-4 focus:ring-[#009A5C]/5 transition-all" 
                    placeholder="e.g. NN-2024-XYS">
            </div>
            
            <div>
                <label class="text-xs font-bold text-gray-400 uppercase ml-1">Valid Till</label>
                <input type="date" id="permitValidTillInput" 
                    class="w-full border border-gray-200 rounded-2xl px-4 py-3 mt-1 outline-none focus:border-[#009A5C] focus:ring-4 focus:ring-[#009A5C]/5 transition-all">
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-8">
            <button type="button" id="nagarCancelBtn" class="px-6 py-3 rounded-xl bg-gray-100 text-gray-600 font-bold hover:bg-gray-200 transition-all">Cancel</button>
            <button type="button" id="nagarSaveBtn" class="px-6 py-3 rounded-xl bg-[#009A5C] text-white font-bold shadow-lg shadow-green-200 hover:bg-[#007d4a] transition-all">Save Details</button>
        </div>
    </div>
</div>

<div id="blockDatesModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-sm border border-gray-100">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
            <h2 class="text-xl font-bold text-gray-800">Select Blocked Dates</h2>
        </div>

        <p class="text-sm text-gray-500 mb-4">Select multiple dates that are unavailable for booking.</p>
        
        <input type="text" id="blockDatesCalendar" 
            class="w-full border border-gray-200 rounded-2xl px-4 py-3 mb-2 outline-none focus:border-[#009A5C] bg-gray-50 cursor-pointer" 
            placeholder="Click to pick dates...">
        
        <div class="flex justify-end gap-3 mt-8">
            <button type="button" id="blockDatesCancelBtn" class="px-6 py-3 rounded-xl bg-gray-100 text-gray-600 font-bold hover:bg-gray-200">Cancel</button>
            <button type="button" id="blockDatesSaveBtn" class="px-6 py-3 rounded-xl bg-[#009A5C] text-white font-bold shadow-lg shadow-green-200 hover:bg-[#007d4a]">Save Dates</button>
        </div>
    </div>
</div>

<div id="graceModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-sm border border-gray-100">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
            <h2 class="text-xl font-bold text-gray-800">Set Grace Period</h2>
        </div>

        <div>
            <label class="text-xs font-bold text-gray-400 uppercase ml-1">Number of Days</label>
            <input type="number" min="1" max="30" id="gracePeriodInput" 
                class="w-full border border-gray-200 rounded-2xl px-4 py-3 mt-1 outline-none focus:border-[#009A5C]" 
                placeholder="e.g. 5">
            <p class="text-[10px] text-gray-400 mt-2 italic">*Maximum allowed grace period is 30 days.</p>
        </div>

        <div class="flex justify-end gap-3 mt-8">
            <button type="button" id="graceCancelBtn" class="px-6 py-3 rounded-xl bg-gray-100 text-gray-600 font-bold hover:bg-gray-200">Cancel</button>
            <button type="button" id="graceSaveBtn" class="px-6 py-3 rounded-xl bg-[#009A5C] text-white font-bold shadow-lg shadow-green-200 hover:bg-[#007d4a]">Set Days</button>
        </div>
    </div>
</div>
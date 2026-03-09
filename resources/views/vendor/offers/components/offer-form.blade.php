<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
        <h2 class="text-xl font-bold text-gray-800">Create Offer</h2>
        <span id="offer-date" class="text-xs text-gray-400 font-medium"></span>
    </div>
    <div class="p-6">
        {{-- Customer selection, offer details, etc. --}}
        <div class="mb-8">
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Customer Details</label>
            <input type="text" class="w-full border border-gray-300 focus:ring-green-500 text-sm py-2.5 px-2" placeholder="Search or enter customer...">
        </div>
        {{-- Add more offer fields as needed --}}
        <div class="flex gap-4 mt-12 pt-6 border-t border-gray-100">
            <button type="button" class="flex-1 py-3 bg-[#7A9C89] border border-gray-200 font-bold text-white transition cursor-pointer">Cancel</button>
            <button class="flex-1 py-3 bg-[#2E5B42] text-white font-bold shadow-lg shadow-green-900/20 hover:bg-opacity-90 active:scale-[0.98] transition cursor-pointer">Preview & Create Offer</button>
        </div>
    </div>
</div>
<div class="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-6">
    <div class="px-5 pt-5 gap-3 flex">
        <h3 class="font-bold text-gray-800">Available Hoardings</h3>
        <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full text-xs font-bold" id="available-count">0</span>
    </div>
    <div class="p-5">
        <input type="text" class="w-full border border-gray-300 text-sm focus:ring-green-500 mb-4" placeholder="Search for available hoardings...">
        <div id="hoardings-grid" class="grid grid-cols-2 gap-4 max-h-[calc(100vh-250px)] overflow-y-auto pr-2 custom-scrollbar">
            <div class="col-span-2 flex flex-col items-center justify-center py-12 text-center text-gray-400 italic">No hoardings found</div>
        </div>
    </div>
</div>
{{-- offer-preview-modal.blade.php --}}

{{-- ════════════════════════════════════════════════════════════
     OFFER PREVIEW MODAL (Screen 3)
════════════════════════════════════════════════════════════ --}}
<div id="offerPreviewModal" class="fixed inset-0 z-[9990] hidden items-center justify-center p-2 sm:p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="offerClosePreview()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-auto z-10 max-h-[95vh] flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="flex items-start justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
            <div>
                <h2 class="text-base font-bold text-gray-800">Offer Preview</h2>
                <p class="text-xs text-gray-400 mt-0.5">Send an offer for customer review</p>
            </div>
            <button onclick="offerClosePreview()" class="text-gray-400 hover:text-gray-600 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Scrollable body --}}
        <div class="overflow-y-auto flex-1 px-6 py-5">

            {{-- Info row --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                <div id="offerPreviewCustomer" class="text-xs"></div>
                <div id="offerPreviewHoardingSummary" class="text-xs"></div>
                <div class="text-xs">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Offer Details</p>
                    <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <p class="text-[9px] text-gray-400 font-semibold">Valid till</p>
                            <div id="offerPreviewValidity" class="text-xs font-bold text-gray-800"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Hoardings header --}}
            <div class="mb-3">
                <p class="text-xs font-bold text-gray-700">Total Hoardings (<span id="offerPreviewTotalCount">0</span>)</p>
                <p class="text-[10px] text-gray-400">View of all selected hoardings added to this offer</p>
            </div>

            {{-- OOH Table --}}
            <div id="offerPreviewOohSection" class="hidden mb-5">
                <div class="flex items-center gap-2 mb-2">
                    <h4 class="text-xs font-bold text-gray-700">OOH (<span id="offerPreviewOohCount">0</span>)</h4>
                    <span class="text-[10px] text-gray-400">— Selected Static Hoardings for the offer</span>
                </div>
                <div class="overflow-x-auto border border-gray-100 rounded-lg">
                    <table class="min-w-[600px] w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-400 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-3 py-2.5 font-semibold w-6">Sn↑</th>
                                <th class="px-3 py-2.5 font-semibold">Hoardings ↕</th>
                                <th class="px-3 py-2.5 font-semibold">Rental ↕</th>
                                <th class="px-3 py-2.5 font-semibold">Duration ↕</th>
                                <th class="px-3 py-2.5 font-semibold text-right">Final Price ↕</th>
                            </tr>
                        </thead>
                        <tbody id="offerPreviewOohBody" class="divide-y divide-gray-50 bg-white"></tbody>
                    </table>
                </div>
            </div>

            {{-- DOOH Table --}}
            <div id="offerPreviewDoohSection" class="hidden mb-5">
                <div class="flex items-center gap-2 mb-2">
                    <h4 class="text-xs font-bold text-gray-700">DIGITAL-DOOH (<span id="offerPreviewDoohCount">0</span>)</h4>
                    <span class="text-[10px] text-gray-400">— Selected Digital Screens for the offer</span>
                </div>
                <div class="overflow-x-auto border border-gray-100 rounded-lg">
                    <table class="min-w-[640px] w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-400 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-3 py-2.5 font-semibold w-6">Sn↑</th>
                                <th class="px-3 py-2.5 font-semibold">Hoardings ↕</th>
                                <th class="px-3 py-2.5 font-semibold">Rental ↕</th>
                                <th class="px-3 py-2.5 font-semibold text-center">Slot ↕</th>
                                <th class="px-3 py-2.5 font-semibold">Duration ↕</th>
                                <th class="px-3 py-2.5 font-semibold text-right">Final Price ↕</th>
                            </tr>
                        </thead>
                        <tbody id="offerPreviewDoohBody" class="divide-y divide-gray-50 bg-white"></tbody>
                    </table>
                </div>
            </div>

        </div>{{-- /scrollable body --}}

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-gray-100 flex-shrink-0 bg-gray-50/50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-600 mb-2">Where you want to send offer?</p>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="offerSendEmail"
                                class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-xs font-semibold text-gray-700">Email</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="offerSendWhatsapp"
                                class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-xs font-semibold text-gray-700">Whatsapp</span>
                        </label>
                    </div>
                    <p id="offerSendError" class="hidden text-[10px] text-red-500 mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Please select at least one sending option
                    </p>
                </div>
                <button id="offerConfirmSendBtn" onclick="offerConfirmAndSend()"
                    class="min-w-[180px] py-3 px-6 bg-[#2D5A43] text-white rounded-lg text-sm font-bold hover:bg-opacity-90 transition whitespace-nowrap">
                    Confirm &amp; Send offer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Update total count when preview opens
const _origOpenPreview = window.offerOpenPreview;
document.addEventListener('DOMContentLoaded', () => {
    // Patch to keep total count in sync
    const origRender = window.offerRenderPreview;
});

// Patch offerRenderPreview to update total count
const _patchPreviewCount = () => {
    const totalEl = document.getElementById('offerPreviewTotalCount');
    if (totalEl && typeof offerItems !== 'undefined') {
        totalEl.innerText = offerItems.size;
    }
};

// Override to patch in count update
const _origRenderPreview = typeof offerRenderPreview !== 'undefined' ? offerRenderPreview : null;
</script>
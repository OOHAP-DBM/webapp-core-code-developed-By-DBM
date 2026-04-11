{{-- offer-success-modal.blade.php --}}

{{-- ════════════════════════════════════════════════════════════
     OFFER SUCCESS MODAL (Screen 4)
════════════════════════════════════════════════════════════ --}}
<div id="offerSuccessModal" class="fixed inset-0 z-[9995] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-auto z-10 p-8 text-center">

        {{-- Success icon --}}
        <div class="mb-5 flex justify-center">
            <div class="relative">
                {{-- Outer badge ring --}}
                <svg class="w-24 h-24 text-[#2D5A43]" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="48" cy="48" r="44" stroke="#2D5A43" stroke-width="3" stroke-dasharray="8 4" opacity="0.3"/>
                    <circle cx="48" cy="48" r="36" fill="#2D5A43" opacity="0.08"/>
                    <circle cx="48" cy="48" r="30" fill="#2D5A43"/>
                    {{-- Person icon --}}
                    <circle cx="48" cy="40" r="8" fill="white"/>
                    <path d="M30 68c0-9.94 8.06-18 18-18s18 8.06 18 18" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    {{-- Ribbon tails --}}
                    <path d="M34 68l-8 14 8-4 4 6 8-16" fill="#2D5A43"/>
                    <path d="M62 68l8 14-8-4-4 6-8-16" fill="#2D5A43"/>
                </svg>
            </div>
        </div>

        <p id="offerSuccessId" class="text-lg font-black text-[#2D5A43] mb-2">Offer ID: #—</p>

        <p class="text-sm text-gray-500 mb-1">We are delighted to inform you that</p>
        <p class="text-sm text-gray-600 mb-1">you have successfully sent the
            <span class="text-[#2D5A43] font-semibold">offer</span> to the customer</p>
        <p class="text-xs text-gray-400 mb-6">You will get notify from customer shortly!</p>

        <button onclick="offerGoToManage()"
            class="w-full py-3 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-gray-800 transition">
            Go to manage offers
        </button>
    </div>
</div>
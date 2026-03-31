<div id="panModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40 modal-overlay" onclick="closeModal('panModal')"></div>

    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-semibold text-gray-900">PAN Document</h3>
            <button type="button" onclick="closeModal('panModal')" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex flex-col items-center justify-center space-y-4 p-2">
            <div class="bg-white rounded-none shadow p-3 w-full flex justify-center min-h-[160px]">
                @if(!empty($profile?->pan_document))
                    @php
                        $ext = strtolower(pathinfo($profile->pan_document, PATHINFO_EXTENSION));
                        $panUrl = route('admin.profile.pan.view');
                    @endphp

                    @if($ext === 'pdf')
                        <a href="{{ $panUrl }}" target="_blank"
                            class="text-blue-600 text-sm font-medium hover:underline cursor-pointer self-center">
                            View PDF Document
                        </a>
                    @else
                        <img
                            src="{{ $panUrl }}"
                            alt="PAN Card"
                            class="max-w-full p-4 max-h-48 rounded-md object-contain"
                        />
                    @endif
                @else
                    <p class="text-sm text-gray-500 italic self-center">
                        PAN card has not been uploaded yet.
                    </p>
                @endif
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="button" onclick="closeModal('panModal')"
                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded hover:bg-gray-50 cursor-pointer">
                Cancel
            </button>

            @if(!empty($profile?->pan_document))
                <a href="{{ route('admin.profile.pan.view') }}" target="_blank"
                    class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-500 rounded hover:bg-green-600 cursor-pointer text-center">
                    Open
                </a>
            @endif
        </div>
    </div>
</div>
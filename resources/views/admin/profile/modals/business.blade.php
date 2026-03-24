<div id="businessModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40 modal-overlay" onclick="closeModal('businessModal')"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-semibold text-gray-900">Business Details</h3>
            <button type="button" onclick="closeModal('businessModal')" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <form method="POST" action="{{route('admin.profile.business.update')}}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">GSTIN Number <span class="text-red-500">*</span></label>
                    <input type="text" name="gstin" value="{{ $profile->gstin ?? '' }}" placeholder="Enter GSTIN Number"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Business Name <span class="text-red-500">*</span></label>
                    <input type="text" name="company_name" value="{{ $profile->company_name ?? '' }}" placeholder="Enter Business Name"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Business Type <span class="text-red-500">*</span></label>
                    <select name="business_type" class="form-select block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500 bg-white">
                        <option value="">Select Business Type</option>
                        <option value="proprietorship" {{ ($profile->business_type ?? '') === 'proprietorship' ? 'selected' : '' }}>Proprietorship</option>
                        <option value="partnership" {{ ($profile->business_type ?? '') === 'partnership' ? 'selected' : '' }}>Partnership</option>
                        <option value="pvt_ltd" {{ ($profile->business_type ?? '') === 'pvt_ltd' ? 'selected' : '' }}>Pvt. Ltd.</option>
                        <option value="llp" {{ ($profile->business_type ?? '') === 'llp' ? 'selected' : '' }}>LLP</option>
                        <option value="public_ltd" {{ ($profile->business_type ?? '') === 'public_ltd' ? 'selected' : '' }}>Public Ltd.</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Upload PAN</label>
                    <label class="flex items-center border border-gray-300 rounded overflow-hidden w-full cursor-pointer hover:bg-gray-50 transition-colors">
                        <span class="flex-shrink-0 bg-gray-100 border-r border-gray-300 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-200 transition-colors">Browse</span>
                        <span id="panFileDisplay" class="px-4 py-2.5 text-sm text-gray-400 flex-1 truncate">
                            {{ !empty($profile?->pan_document) ? basename($profile->pan_document) : 'Choose file' }}
                        </span>
                        <input type="file" name="pan_document" accept="image/*,.pdf" class="hidden" onchange="document.getElementById('panFileDisplay').textContent = this.files[0]?.name || 'Choose file'">
                    </label>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeModal('businessModal')"
                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded hover:bg-gray-50 cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-500 rounded hover:bg-green-600 cursor-pointer">
                    Save
                </button>
            </div>
        </form>

    </div>
</div>
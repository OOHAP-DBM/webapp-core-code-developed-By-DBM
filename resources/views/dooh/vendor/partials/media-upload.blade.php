@php
    // $screen: DOOHScreen|null (edit mode), null (create mode)
    // $existingMedia: Collection of DOOHScreenMedia (edit mode), [] (create mode)
@endphp
<div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
    <h3 class="text-lg font-bold text-[#009A5C] mb-2 flex items-center">
        <span class="w-1.5 h-6 bg-[#009A5C] rounded-full mr-3"></span>
        Upload Screen Media <span class="text-red-500 ml-1">*</span>
    </h3>
    <p class="text-sm text-gray-400 mb-6">High quality photos/videos increase booking chances by 40%.</p>

    @if(isset($existingMedia) && $existingMedia->count() > 0)
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <h4 class="text-sm font-bold text-blue-900 mb-3 flex items-center justify-between">
                <span>Existing Media ({{ $existingMedia->count() }})</span>
                <span class="text-xs font-normal text-blue-700">Click × to remove</span>
            </h4>
            <div id="existingMediaContainer" class="flex flex-wrap gap-3">
                @foreach($existingMedia as $media)
                    <div class="relative group" data-media-id="{{ $media->id }}">
                        @if(Str::startsWith($media->media_type, 'image'))
                            <img src="{{ asset('storage/dooh/screens/' . $media->dooh_screen_id . '/' . $media->file_path) }}" class="w-24 h-24 object-cover rounded-lg border-2 border-gray-200 shadow-sm">
                        @elseif(Str::startsWith($media->media_type, 'video'))
                            <video src="{{ asset('storage/dooh/screens/' . $media->dooh_screen_id . '/' . $media->file_path) }}" class="w-24 h-24 object-cover rounded-lg border-2 border-gray-200 shadow-sm" controls></video>
                        @endif
                        <button type="button" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-red-600" onclick="removeExistingMedia({{ $media->id }})">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <input type="hidden" name="existing_media_ids[]" value="{{ $media->id }}">
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <input type="hidden" name="deleted_media_ids[]" id="deletedMediaIds">

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">
                <span id="existingMediaCount" class="font-bold text-blue-600">{{ isset($existingMedia) ? $existingMedia->count() : 0 }}</span> existing + 
                <span id="uploadCount" class="font-bold text-[#009A5C]">0</span> new = 
                <span id="totalCount" class="font-bold">0</span> / 10 total
            </p>
        </div>
        <div class="relative group border-2 border-dashed border-[#E5E7EB] rounded-2xl p-8 bg-[#FBFBFB] hover:bg-green-50/30 hover:border-[#009A5C] transition-all flex flex-col items-center justify-center">
            <input type="file" id="mediaUpload" name="media[]" multiple 
                @if(!isset($existingMedia) || $existingMedia->count() == 0) required @endif
                accept="image/jpeg,image/png,image/jpg,image/webp,video/mp4,video/webm" 
                class="absolute inset-0 opacity-0 cursor-pointer">
            <div class="w-16 h-16 bg-white shadow-sm rounded-2xl flex items-center justify-center mb-4 text-[#009A5C] group-hover:scale-110 transition-transform">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4a1 1 0 011-1h8a1 1 0 011 1v12m-4 4h-4a1 1 0 01-1-1v-1a1 1 0 011-1h4a1 1 0 011 1v1a1 1 0 01-1 1z" />
                </svg>
            </div>
            <p class="text-base font-bold text-gray-700">Drop your media here, or <span class="text-[#009A5C]">browse</span></p>
            <p class="text-xs text-gray-400 mt-2">Images: JPG, PNG, WEBP (Max 5MB) • Videos: MP4, WEBM (Max 5MB)</p>
        </div>
        <div id="filePreview" class="flex flex-wrap gap-3 mt-4"></div>
    </div>
</div>
<script src="/js/dooh-media-upload.js"></script>

<style>
.hoarding-image-box {
    width: 100%;
    height: 450px;
    overflow: hidden;
    border-radius: 12px;    
}
.hoarding-image-box img {
    width: 100%;
    height: 100%;
    object-fit: fill;
    display: block;
}

/* Horizontal thumbnail strip */
.thumbnail-strip {
    display: flex;
    flex-direction: row;
    gap: 10px;
    overflow-x: auto;
    padding: 10px 0;
    scrollbar-width: thin;
}
.thumbnail-strip::-webkit-scrollbar {
    height: 4px;
}
.thumbnail-strip::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
}
.thumbnail-item {
    flex: 0 0 80px;
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid #ddd;
    transition: border-color 0.2s;
}
.thumbnail-item:hover,
.thumbnail-item.active {
    border-color: #009A5C;
}
.thumbnail-item img,
.thumbnail-item video {
    width: 100%;
    height: 100%;
    object-fit: fill;
    display: block;
}
</style>
@php 
    $mediaItems = $hoarding->allMediaItems(); 
    $mainMedia  = $mediaItems->first();
@endphp

<div class="mb-4">

    {{-- MAIN IMAGE / VIDEO --}}
    <div class="hoarding-image-box">
        @if(!$mainMedia)
            <img src="{{ asset('assets/images/placeholder.jpg') }}" alt="Hoarding">
        @else
            <x-media-preview 
                :media="$mainMedia" 
                alt="Hoarding Image" 
            />
        @endif
    </div>

    {{-- HORIZONTAL THUMBNAIL STRIP --}}
    @if($mediaItems->count() > 1)
        <div class="thumbnail-strip mt-3">
            @foreach($mediaItems as $index => $media)
                <div
                    class="thumbnail-item {{ $index === 0 ? 'active' : '' }}"
                    onclick="switchMedia(this, '{{ asset('storage/' . $media->file_path) }}', {{ $media->isVideo() ? 'true' : 'false' }}, '{{ $media->normalizedMimeType() }}')">
                    <x-media-preview :media="$media" alt="" />
                </div>
            @endforeach
        </div>
    @endif

</div>

<script>
function switchMedia(thumbEl, src, isVideo, mimeType) {
    // Update main display
    const box = document.querySelector('.hoarding-image-box');
    if (isVideo) {
        box.innerHTML = `<video style="width:100%;height:100%;object-fit:fill;display:block;border-radius:12px;"
            autoplay muted loop playsinline preload="metadata">
            <source src="${src}" type="${mimeType}">
        </video>`;
    } else {
        box.innerHTML = `<img src="${src}" style="width:100%;height:100%;object-fit:fill;display:block;" alt="Hoarding">`;
    }

    // Update active thumbnail border
    document.querySelectorAll('.thumbnail-item').forEach(el => el.classList.remove('active'));
    thumbEl.classList.add('active');
}
</script>
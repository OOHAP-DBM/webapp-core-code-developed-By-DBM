@php
    $images = collect();
    // OOH
    if ($hoarding->hoarding_type === 'ooh' && $hoarding->hoardingMedia->isNotEmpty()) {
        $images = $hoarding->hoardingMedia->map(function ($m) {
            return [
                'url' => asset('storage/' . $m->file_path),
                'is_primary' => $m->is_primary ?? 0
            ];
        });
    }
    // DOOH
    elseif ($hoarding->hoarding_type === 'dooh' && $hoarding->doohScreen && $hoarding->doohScreen->media->isNotEmpty()) {
        $images = $hoarding->doohScreen->media->sortBy('sort_order')->map(function ($m) {
            return [
                'url' => asset('storage/' . $m->file_path),
                'is_primary' => $m->is_primary ?? 0
            ];
        });
    }
    // Fallback
    if ($images->isEmpty()) {
        $images = collect([
            ['url' => asset('assets/images/placeholder.jpg'), 'is_primary' => 1]
        ]);
    }
    $mainImage = $images->firstWhere('is_primary', 1) ?? $images->first();
    $thumbs = $images->filter(function($img) use ($mainImage) {
        return $img['url'] !== $mainImage['url'];
    })->values();
@endphp

<div class="bg-white rounded-lg p-4 mb-4">
    <div class="mb-2 text-base font-semibold text-[var(--accent-color)]">Media</div>
    <div class="flex gap-3">
        <div class="flex-1 min-w-0">
            <div
                class="w-full bg-gray-100 rounded-lg overflow-hidden
                    h-[220px] sm:h-[300px] md:h-[380px] lg:h-[450px]">
                <img
                    id="mainPreviewImage"
                    src="{{ $mainImage['url'] }}"
                    alt="Main Image"
                    class="w-full h-full object-cover"
                />
            </div>
        </div>

        <!-- Thumbnails -->
        <div class="flex flex-col gap-2 w-24">
            @foreach($thumbs->take(6) as $img)
                <div class="w-full aspect-[16/9] bg-gray-100 rounded-lg overflow-hidden cursor-pointer border border-gray-200 hover:border-green-500">
                    <img src="{{ $img['url'] }}" onclick="swapPreviewImage('{{ $img['url'] }}')" alt="Thumbnail" class="w-full h-full object-cover" />
                </div>
            @endforeach
            @if($thumbs->count() > 6)
                <div class="w-full aspect-[16/9] bg-gray-200 rounded-lg flex items-center justify-center text-gray-600 font-semibold text-lg">
                    +{{ $thumbs->count() - 6 }}
                </div>
            @endif
        </div>
    </div>
</div>
<style>
@media (min-width: 1280px) {
    .hoarding-preview-main-img-xl {
        height: 450px !important;
        aspect-ratio: unset !important;
    }
}
</style>
<script>
// Add the class to the main image container on xl screens
window.addEventListener('DOMContentLoaded', function() {
    function updateMainImgClass() {
        var el = document.querySelector('.hoarding-preview-main-img-xl');
        if (!el) return;
        if (window.innerWidth >= 1280) {
            el.classList.add('hoarding-preview-main-img-xl');
        } else {
            el.classList.remove('hoarding-preview-main-img-xl');
        }
    }
    var mainDiv = document.querySelector('.flex-1 > div');
    if (mainDiv) mainDiv.classList.add('hoarding-preview-main-img-xl');
    updateMainImgClass();
    window.addEventListener('resize', updateMainImgClass);
});
</script>
<script>
function swapPreviewImage(url) {
    const mainImg = document.getElementById('mainPreviewImage');
    if (!mainImg) return;

    // Same image dobara click ho to kuch mat karo
    if (mainImg.src === url) return;

    mainImg.src = url;
}
</script>

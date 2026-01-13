<style>

.hoarding-image-box {
    width: 100%;
    height: 450px;          /* adjust as you like */
    overflow: hidden;
    border-radius: 12px;    
}

.hoarding-image-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;      /* MOST IMPORTANT */
    display: block;
}


</style>
@php
    $images = collect();

    // ================= OOH =================
    if (
        $hoarding->hoarding_type === 'ooh'
        && $hoarding->hoardingMedia->isNotEmpty()
    ) {
        $images = $hoarding->hoardingMedia->map(function ($m) {
            return asset('storage/' . $m->file_path);
        });
    }

    // ================= DOOH =================
    elseif (
        $hoarding->hoarding_type === 'dooh'
        && $hoarding->doohScreen
        && $hoarding->doohScreen->media->isNotEmpty()
    ) {
        $images = $hoarding->doohScreen->media
            ->sortBy('sort_order')
            ->map(function ($m) {
                return asset('storage/' . $m->file_path);
            });
    }

    // ================= FALLBACK =================
    if ($images->isEmpty()) {
        $images = collect([asset('assets/images/placeholder.jpg')]);
    }

    $mainImage = $images->first();
@endphp



<div class="row g-3 mb-4">

    <!-- MAIN IMAGE -->
  <div class="col-lg-6">
    <div class="hoarding-image-box">
        <img
            id="mainImage"
            src="{{ $mainImage }}"
            alt="Hoarding Image"
        >
    </div>
</div>
</div>

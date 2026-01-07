<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <img id="mainImage"
             src="{{ asset($hoarding->media[0]->path ?? 'assets/images/placeholder.jpg') }}"
             class="img-fluid w-100 main-image">
    </div>

    <div class="col-lg-6">
        <div class="row g-3">
            @foreach($hoarding->media->take(4) as $media)
                <div class="col-6">
                    <img src="{{ asset($media->path) }}"
                         class="img-fluid w-100 thumb-image">
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>

.media-wrapper{
    display:flex;
    gap:16px;
}

/* MAIN IMAGE */

.hoarding-image-box{
    flex:1;
    aspect-ratio:16/9;
    max-height:550px;
    overflow:hidden;
    border-radius:12px;
    background:#f5f5f5;
    position:relative;
}

.hoarding-image-box img,
.hoarding-image-box video{
    width:100%;
    height:100%;
    object-fit:cover;
    position:absolute;
    inset:0;
}


/* THUMBNAILS */

.thumbnail-strip{
    width:280px;
    display:flex;
    flex-direction:column;
    gap:12px;
    overflow-y:auto;
    max-height:550px;
}

.thumbnail-item{
    position:relative;
    width:100%;
    height:180px;
    border-radius:10px;
    overflow:hidden;
    cursor:pointer;
    border:2px solid #ddd;
}

.thumbnail-item img,
.thumbnail-item video{
    width:100%;
    height:100%;
    object-fit:cover;
}

.thumbnail-item.active{
    border-color:#009A5C;
}


/* CAMERA ICON */

.thumb-more{
    position:absolute;
    inset:0;
    background:rgba(0,0,0,.6);
    display:flex;
    align-items:center;
    justify-content:center;
}

.thumb-more svg{
    width:34px;
    height:34px;
    fill:#fff;
}


/* MODAL */

.gallery-modal{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.45);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:9999;
}

.gallery-content{
    width:90%;
    max-width:1000px;
    position:relative;
    background:#fff;
    padding:40px;
    border-radius:5px;
}

.gallery-slide{
    display:none;
}

.gallery-slide.active{
    display:block;
}

.gallery-slide img,
.gallery-slide video{
    width:100%;
    max-height:70vh;     /* थोड़ा कम */
    object-fit:contain;
}

.gallery-close{
    position:absolute;
    top:10px;
    right:15px;
    font-size:26px;
    color:#000;
    cursor:pointer;
}

.gallery-nav{
    position:absolute;
    top:50%;
    transform:translateY(-50%);
    width:42px;
    height:42px;
    border-radius:50%;
    background:#f3f3f3;
    color:#000;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
    cursor:pointer;
    box-shadow:0 2px 6px rgba(0,0,0,.2);
}

.gallery-prev{
    left:20px;
}

.gallery-next{
    right:20px;
}


/* MOBILE */

@media (max-width:768px){

.media-wrapper{flex-direction:column;}

.hoarding-image-box{
    aspect-ratio:16/10;
    max-height:220px;
}

.thumbnail-strip{
    width:100%;
    flex-direction:row;
    overflow-x:auto;
}

.thumbnail-item{
    flex:0 0 85px;
    height:65px;
}

.gallery-prev{ left:10px; }
.gallery-next{ right:10px; }

}

</style>


@php
$mediaItems = $hoarding->allMediaItems();
$mainMedia  = $mediaItems->first();
@endphp


<div class="mb-4">

<div class="media-wrapper">

<div class="hoarding-image-box" id="main-media">

@if($mainMedia && $mainMedia->isVideo())

<video autoplay muted loop playsinline preload="auto">
<source src="{{ asset('storage/'.$mainMedia->file_path) }}" type="{{ $mainMedia->normalizedMimeType() }}">
</video>

@else

<img src="{{ asset('storage/'.($mainMedia->path_1500 ?? $mainMedia->file_path)) }}">

@endif

</div>



@if($mediaItems->count() > 1)

<div class="thumbnail-strip">

@foreach($mediaItems->take(4) as $index => $media)

<div
class="thumbnail-item {{ $index == 0 ? 'active' : '' }}"
onclick="switchMedia(this)"
data-src="{{ asset('storage/'.($media->path_1500 ?? $media->file_path)) }}"
data-video="{{ $media->isVideo() ? '1' : '0' }}"
data-mime="{{ $media->normalizedMimeType() }}"
>

@if($media->isVideo())
<video muted>
<source src="{{ asset('storage/'.$media->file_path) }}">
</video>
@else
<img src="{{ asset('storage/'.($media->path_100 ?? $media->file_path)) }}">
@endif


@if($index == 3 && $mediaItems->count() > 4)


<div class="thumb-more" onclick="openGallery(event)">
    <svg viewBox="0 0 24 24">
        <path d="M9 2L7.17 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3.17L15 2H9zm3 15a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/>
    </svg>
    <span style="position:absolute; bottom:8px; right:12px; background:rgba(0,0,0,0.7); color:#fff; font-weight:600; font-size:13px; padding:2px 8px; border-radius:12px; pointer-events:none;">
        {{ $mediaItems->count() }}
    </span>
</div>

@endif

</div>

@endforeach

</div>

@endif

</div>

</div>



<!-- MODAL GALLERY -->

<div class="gallery-modal" id="galleryModal">

<div class="gallery-content">

<div class="gallery-close" onclick="closeGallery()">×</div>

<div class="gallery-prev gallery-nav" onclick="changeSlide(-1)">‹</div>
<div class="gallery-next gallery-nav" onclick="changeSlide(1)">›</div>


@foreach($mediaItems as $index=>$media)

<div class="gallery-slide {{ $index==0?'active':'' }}">

@if($media->isVideo())

<video controls>
<source src="{{ asset('storage/'.$media->file_path) }}">
</video>

@else

<img src="{{ asset('storage/'.($media->path_1500 ?? $media->file_path)) }}">

@endif

</div>

@endforeach


</div>

</div>



<script>

function switchMedia(el){

const box=document.getElementById('main-media');

const src=el.dataset.src;
const isVideo=el.dataset.video==="1";
const mime=el.dataset.mime;

if(isVideo){

box.innerHTML=`<video autoplay muted loop playsinline>
<source src="${src}" type="${mime}">
</video>`;

}else{

box.innerHTML=`<img src="${src}">`;

}

document.querySelectorAll('.thumbnail-item').forEach(e=>e.classList.remove('active'));
el.classList.add('active');

}



/* MODAL */

let currentSlide=0;

function openGallery(e){
e.stopPropagation();
document.getElementById('galleryModal').style.display='flex';
}

function closeGallery(){
document.getElementById('galleryModal').style.display='none';
}

function changeSlide(dir){

const slides=document.querySelectorAll('.gallery-slide');

slides[currentSlide].classList.remove('active');

currentSlide+=dir;

if(currentSlide<0) currentSlide=slides.length-1;
if(currentSlide>=slides.length) currentSlide=0;

slides[currentSlide].classList.add('active');

}

</script>
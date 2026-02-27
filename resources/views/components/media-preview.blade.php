@php
    $mimeType = $media->normalizedMimeType();
    $isVideo  = $media->isVideo();
    $url      = asset('storage/' . $media->file_path);
    $classes  = $classes ?? 'w-full h-full object-cover';
@endphp

@if($isVideo)
    <video class="{{ $classes }}"
        style="width:100%; height:100%; object-fit:cover; display:block;"
        autoplay muted loop playsinline preload="metadata">
        <source src="{{ $url }}" type="{{ $mimeType }}">
    </video>
@else
    <img src="{{ $url }}" alt="{{ $alt ?? '' }}" class="{{ $classes }}">
@endif
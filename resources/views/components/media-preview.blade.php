@php
    $mimeType = $media->normalizedMimeType();
    $isVideo  = $media->isVideo();

    $url300  = asset('storage/' . ($media->path_300 ?? $media->file_path));
    $url600  = asset('storage/' . ($media->path_600 ?? $media->file_path));
    $url1000 = asset('storage/' . ($media->path_1000 ?? $media->file_path));
    $url1500 = asset('storage/' . ($media->path_1500 ?? $media->file_path));

    $classes  = $classes ?? 'w-full h-full';
@endphp

@if($isVideo)
    <video
        class="{{ $classes }}"
        autoplay muted loop playsinline preload="metadata"
        style="width:100%; height:100%; object-fit:contain;"
    >
        <source src="{{ $url1500 }}" type="{{ $mimeType }}">
    </video>
@else
    <img
        src="{{ $url300 }}"
        srcset="
            {{ $url300 }} 300w,
            {{ $url600 }} 600w,
            {{ $url1000 }} 1000w,
            {{ $url1500 }} 1500w
        "
        sizes="(max-width: 768px) 300px, (max-width: 1200px) 600px, 1000px"
        alt="{{ $alt ?? '' }}"
        class="{{ $classes }}"
        loading="lazy"
        style="width:100%; height:100%; object-fit:cover;"
    >
@endif
@php
    $mimeType = $media->normalizedMimeType();
    $isVideo  = $media->isVideo();

    $url300  = asset('storage/' . ($media->path_300  ?? $media->file_path));
    $url600  = asset('storage/' . ($media->path_600  ?? $media->file_path));
    $url720  = asset('storage/' . ($media->path_720  ?? $media->path_600 ?? $media->file_path));
    $url1000 = asset('storage/' . ($media->path_1000 ?? $media->file_path));
    $url1500 = asset('storage/' . ($media->path_1500 ?? $media->file_path));

    $classes = $classes ?? 'w-full h-full object-cover';
@endphp

@if($isVideo)
    <video
        class="{{ $classes }}"
        autoplay muted loop playsinline preload="metadata"
    >
        <source src="{{ $url600 }}" type="{{ $mimeType }}">
    </video>
@else
    <img
        src="{{ $url300 }}"
        srcset="
            {{ $url300 }}  300w,
            {{ $url600 }}  600w,
            {{ $url720 }}  720w,
            {{ $url1000 }} 1000w,
            {{ $url1500 }} 1500w
        "
        sizes="(max-width: 600px) 300px, 356px"
        alt="{{ $alt ?? 'Hoarding Image' }}"
        class="{{ $classes }}"
        loading="lazy"
        decoding="async"
        width="356"
        height="200"
    >
@endif
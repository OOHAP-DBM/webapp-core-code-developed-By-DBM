@props([
    'entity' => null,        // Model with media (e.g., Hoarding, BookingProof)
    'collection' => 'gallery', // Media collection name
    'alt' => 'Image',        // Alt text
    'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw',
    'class' => 'w-full h-full object-cover',
    'loadingStrategy' => 'lazy', // 'eager' (LCP) or 'lazy' (below-fold)
    'widths' => [480, 768, 1200] // Default responsive widths
])

@php
    // Exit early if no entity or media
    $media = $entity && method_exists($entity, 'getFirstMedia') 
        ? $entity->getFirstMedia($collection) 
        : null;
    
    if (!$media) {
        // Fallback for missing media
        echo view('components.placeholder-image', [
            'alt' => $alt,
            'class' => $class,
        ])->render();
        return;
    }

    // Build srcset for JPEG/original format
    $jpegSrcset = [];
    $webpSrcset = [];
    
    foreach ($widths as $width) {
        $jpegConversion = "responsive-{$width}";
        $webpConversion = "responsive-{$width}-webp";
        
        if ($media->hasGeneratedConversion($jpegConversion)) {
            $jpegSrcset[] = $media->getUrl($jpegConversion) . " {$width}w";
        }
        if ($media->hasGeneratedConversion($webpConversion)) {
            $webpSrcset[] = $media->getUrl($webpConversion) . " {$width}w";
        }
    }
    
    // Fallback to original if conversions not available
    $fallbackUrl = !empty($jpegSrcset) 
        ? (collect($jpegSrcset)->first() ?? $media->getUrl())
        : $media->getUrl();
    
    $jpegSrcsetString = implode(',', $jpegSrcset);
    $webpSrcsetString = implode(',', $webpSrcset);
    
    // Get dimensions (assume 16:9 for responsive images)
    $maxWidth = end($widths) ?? 1200;
    $maxHeight = intval($maxWidth * 9 / 16); // 16:9 aspect ratio
@endphp

<picture>
    @if(!empty($webpSrcsetString))
        <source type="image/webp"
                srcset="{{ $webpSrcsetString }}"
                sizes="{{ $sizes }}">
    @endif
    
    @if(!empty($jpegSrcsetString))
        <source srcset="{{ $jpegSrcsetString }}"
                sizes="{{ $sizes }}">
    @endif
    
    <img src="{{ $fallbackUrl }}"
         @if(!empty($jpegSrcsetString))
             srcset="{{ $jpegSrcsetString }}"
         @endif
         alt="{{ $alt }}"
         class="{{ $class }}"
         width="{{ $maxWidth }}"
         height="{{ $maxHeight }}"
         loading="{{ $loadingStrategy }}"
         decoding="async"
         {{ $attributes }}>
</picture>

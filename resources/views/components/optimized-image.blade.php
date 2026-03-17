@props([
    'src',
    'alt' => '',
    'class' => '',
    'width' => null,
    'height' => null,
    'loading' => 'lazy',
    'fetchpriority' => null,
    'srcset' => null,
    'sizes' => null,
    'webpSrcset' => null,
    'decoding' => 'async',
])

@php
    $isExternal = preg_match('/^https?:\/\//i', (string) $src) === 1;
    $normalizedSrc = ltrim((string) $src, '/');

    $imgUrl = $isExternal ? $src : asset($normalizedSrc);

    $computedWebp = null;
    if (!$isExternal) {
        $candidate = preg_replace('/\.(jpe?g|png)$/i', '.webp', $normalizedSrc);
        if ($candidate !== $normalizedSrc && file_exists(public_path($candidate))) {
            $computedWebp = asset($candidate);
        }
    }

    $resolvedWebpSrcset = $webpSrcset ?: $computedWebp;
@endphp

<picture>
    @if($resolvedWebpSrcset)
        <source srcset="{{ $resolvedWebpSrcset }}" type="image/webp">
    @endif

    <img
        src="{{ $imgUrl }}"
        alt="{{ $alt }}"
        class="{{ $class }}"
        @if($srcset) srcset="{{ $srcset }}" @endif
        @if($sizes) sizes="{{ $sizes }}" @endif
        @if($width) width="{{ $width }}" @endif
        @if($height) height="{{ $height }}" @endif
        loading="{{ $loading }}"
        decoding="{{ $decoding }}"
        @if($fetchpriority) fetchpriority="{{ $fetchpriority }}" @endif
    >
</picture>

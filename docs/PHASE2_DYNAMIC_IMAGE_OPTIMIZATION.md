# Phase 2: Dynamic Image Optimization - Media Library Setup

**Date**: March 17, 2026  
**Commits**: 
- Phase 1 (static images): `dbfede54e`
- Phase 2 (media library): `7466340f6`

## Overview

Phase 2 implements automatic WebP generation and responsive image variants for user-uploaded images (hoardings, booking proofs) via Spatie Laravel Media Library. This infrastructure prepares the platform for serving optimized images across all devices.

## Architecture

### Media Collections & Conversions

The system defines responsive conversions for each media collection:

#### 1. **Hero Images** (Hoarding Feature Images)
- **Collection**: `hero_image`
- **Widths**: 480, 768, 1200, 1920px
- **Aspect Ratio**: 16:9 (cinematic)
- **Heights**: 270, 432, 675, 1080px
- **Formats**: JPEG + WebP
- **Use Case**: Hero banner, featured listings

```php
// In Hoarding model
$this->addMediaCollection('hero_image')
    ->singleFile()
    ->registerMediaConversions(function () {
        $this->addMediaConversion('responsive-480')->width(480)->height(270);
        $this->addMediaConversion('responsive-480-webp')->width(480)->height(270)->format('webp');
        // ... repeats for 768, 1200, 1920
    });
```

#### 2. **Night Images** (Hoarding Night View)
- **Collection**: `night_image`
- **Widths**: 480, 768, 1200, 1920px
- **Aspect Ratio**: 16:9
- **Heights**: 270, 432, 675, 1080px
- **Formats**: JPEG + WebP

#### 3. **Gallery Images** (Hoarding Multiple Angles)
- **Collection**: `gallery`
- **Widths**: 480, 768, 1200px
- **Aspect Ratio**: 4:3 (wider previews)
- **Heights**: 360, 576, 900px
- **Formats**: JPEG + WebP
- **Use Case**: Card grid, lightbox gallery

#### 4. **Size Overlays** (Hoarding Dimension Diagrams)
- **Collection**: `size_overlay`
- **Widths**: 300px only
- **Formats**: JPEG + WebP
- **Use Case**: Hoarding dimension references

#### 5. **Proof Images** (Booking Verification)
- **Model**: `BookingProof`
- **Collection**: `proof`
- **Widths**: 480, 768, 1200px
- **Aspect Ratio**: 4:3
- **Formats**: JPEG + WebP (images only; videos not converted)
- **Use Case**: Post-execution proof gallery

## Helper Infrastructure

### ResponsiveImageTrait

Located at `app/Traits/ResponsiveImageTrait.php`, provides methods to generate responsive srcsets:

```php
// Add to any model with HasMedia interface
use ResponsiveImageTrait;

// In model class
class Hoarding extends Model implements HasMedia {
    use InteractsWithMedia, ResponsiveImageTrait;
}
```

**Available Methods**:

```php
// 1. Generate JPEG srcset
$hoarding->generateResponsiveSrcset('gallery', [480, 768, 1200]);
// Returns: "storage/image-480.jpg 480w, storage/image-768.jpg 768w, ..."

// 2. Generate WebP srcset
$hoarding->generateWebpSrcset('gallery', [480, 768, 1200]);
// Returns: "storage/image-480.webp 480w, storage/image-768.webp 768w, ..."

// 3. Generate complete picture HTML
$hoarding->generateResponsivePicture('gallery', 'Hoarding image', [
    'sizes' => '(max-width: 768px) 100vw, 50vw',
    'class' => 'rounded-lg',
    'width' => 1200,
    'height' => 900
]);

// 4. Get data for API responses
$images = $hoarding->getResponsiveImageData('gallery');
// Returns array with URLs by format and width:
// [
//     'original' => 'storage/...',
//     'webp' => [480 => '...', 768 => '...', 1200 => '...'],
//     'jpeg' => [480 => '...', 768 => '...', 1200 => '...'],
// ]

// 5. Convenience methods
$hoarding->heroImage();           // Get hero image (largest variant)
$hoarding->galleryThumb(480);      // Get gallery thumbnail at 480w
```

### Blade Components

#### `<x-responsive-media>` Component

Automatically handles responsive images with WebP fallback:

```blade
<x-responsive-media
    :entity="$hoarding"
    collection="gallery"
    alt="{{ $hoarding->title }}"
    sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
    class="rounded-lg shadow-lg"
    :widths="[480, 768, 1200]"
    loadingStrategy="lazy"
/>
```

**Props**:
- `entity` - Model with media (Hoarding, BookingProof, etc.)
- `collection` - Media collection name (default: 'gallery')
- `alt` - Alt text for accessibility
- `sizes` - CSS sizes hint (default: 100vw)
- `class` - Additional CSS classes
- `widths` - Array of responsive widths (default: [480, 768, 1200])
- `loadingStrategy` - 'lazy' or 'eager' (default: 'lazy')

#### `<x-placeholder-image>` Component

Provides gradient placeholder for missing images:

```blade
<x-placeholder-image
    alt="Missing image"
    class="w-full h-64 rounded"
/>
```

## Blade Template Updates

### welcome.blade.php

Updated to use the new responsive conversions with fallback:

```blade
@if($hoarding->hasMedia('gallery'))
    <picture>
        <source type="image/webp"
                srcset="{{ $hoarding->getFirstMedia('gallery')->getUrl('responsive-480-webp') }} 480w, ..."
                sizes="...">
        <img src="{{ $hoarding->getFirstMedia('gallery')->getUrl('responsive-768') }}"
             srcset="..."
             sizes="..."
             alt="{{ $hoarding->title }}"
             class="card-image"
             width="1200" height="900"
             loading="lazy" decoding="async">
    </picture>
@elseif($hoarding->hasMedia('hero_image'))
    {{-- Fallback to hero image if gallery unavailable --}}
@endif
```

## Workflow: Image Upload → Responsive Variants

When an image is uploaded to a media collection:

1. **Upload**: User uploads image to collection (e.g., gallery)
2. **Storage**: Original stored in `storage/media/{model_id}/{collection}/`
3. **Conversions**: Spatie generates responsive variants:
   - responsive-480.jpg, responsive-480.webp
   - responsive-768.jpg, responsive-768.webp
   - responsive-1200.jpg, responsive-1200.webp (if applicable)
4. **Serving**: Media routes return appropriate variant via `getUrl('responsive-{width}')`
5. **Client**: Browser uses srcset to request optimal size

### Media URL Patterns

```
storage/media/{model_id}/{collection}/
├── conversions/
│   ├── responsive-480.jpg
│   ├── responsive-480.webp
│   ├── responsive-768.jpg
│   ├── responsive-768.webp
│   ├── responsive-1200.jpg
│   ├── responsive-1200.webp
│   └── thumb.jpg
└── original.jpg
```

## API Usage

### Get Responsive Image Data

For API responses, use the helper method:

```php
// In API Controller
$hoarding = Hoarding::findOrFail($id);
$imageData = $hoarding->getResponsiveImageData('gallery');

return response()->json([
    'id' => $hoarding->id,
    'title' => $hoarding->title,
    'images' => $imageData  // Structured responsive URLs
]);
```

Response structure:
```json
{
  "images": {
    "original": "storage/media/123/gallery/original.jpg",
    "webp": {
      "480": "storage/media/123/gallery/conversions/responsive-480.webp",
      "768": "storage/media/123/gallery/conversions/responsive-768.webp",
      "1200": "storage/media/123/gallery/conversions/responsive-1200.webp"
    },
    "jpeg": {
      "480": "storage/media/123/gallery/conversions/responsive-480.jpg",
      "768": "storage/media/123/gallery/conversions/responsive-768.jpg",
      "1200": "storage/media/123/gallery/conversions/responsive-1200.jpg"
    }
  }
}
```

## Implementation Guide

### For New Features/Templates Using Responsive Images

#### Option 1: Using the Helper Method (Recommended for Controllers)

```php
// In controller
$hoarding = Hoarding::with('media')->find($id);
return view('hoarding.show', [
    'hoarding' => $hoarding,
    'imagery' => $hoarding->getResponsiveImageData('gallery')
]);
```

Then in Blade:

```blade
@if(isset($imagery['webp']))
    <picture>
        <source type="image/webp" srcset="{{ implode(', ', $imagery['webp']) }}" sizes="100vw">
        <img src="{{ $imagery['jpeg'][480] ?? $imagery['original'] }}" srcset="{{ implode(', ', $imagery['jpeg']) }}" sizes="100vw" alt="...">
    </picture>
@endif
```

#### Option 2: Using Blade Component (Recommended for Blade)

```blade
<x-responsive-media
    :entity="$hoarding"
    collection="gallery"
    alt="{{ $hoarding->title }}"
    sizes="(max-width: 768px) 100vw, 50vw"
    loadingStrategy="lazy"
/>
```

#### Option 3: Direct Media URLs

```blade
@if($hoarding->hasMedia('gallery'))
    @php
        $media = $hoarding->getFirstMedia('gallery');
        $webpSrcset = '480w: ' . $media->getUrl('responsive-480-webp');
        $jpegSrcset = '480w: ' . $media->getUrl('responsive-480');
    @endphp
    <img src="{{ $media->getUrl('responsive-768') }}" alt="..." />
@endif
```

## Migration Path

### From Legacy HoardingMedia to Spatie Media Library

**Current State**:
- OOH hoardings use `HoardingMedia` model (legacy) with pre-generated paths
- DOOH screens use `DOOHScreenMedia` (legacy)
- Blade templates use `media-preview` component with fixed paths

**Future Migration**:
1. Create migration to populate Spatie `media` table from legacy paths
2. Update models to check Spatie first, fallback to legacy
3. Gradually update Blade templates to use `x-responsive-media`
4. Retire legacy `HoardingMedia` once migration complete

## Performance Impact

### File Size Reduction

- **WebP conversion**: 25-35% smaller than JPEG
- **Example**: 500KB gallery image
  - JPEG-480: 40KB
  - WebP-480: 28KB (30% savings)
  - Total savings across 4 widths: ~48KB per image

### Request Optimization

- **Before**: Download 1920px image on mobile (500KB)
- **After**: Download 480px WebP (28KB) → 94% reduction
- **Lazy loading**: Below-fold images load only when needed

### Conversion Cost

- First request: Conversion happens on-demand (if queued=false)
- Subsequent requests: Serve pre-converted file
- `nonQueued()` flag ensures immediate availability

## Configuration Review

Check `config/media-library.php` for related settings:

```php
// Image optimization tools available
'image_optimizers' => [
    Spatie\ImageOptimizer\Optimizers\Jpegoptim::class,
    Spatie\ImageOptimizer\Optimizers\Pngquant::class,
    // ... others
    Spatie\ImageOptimizer\Optimizers\Cwebp::class,  // WebP conversion
],

// Conversions are generated non-queued (immediately available)
'generate_conversions_for_original_image' => true,
```

## Troubleshooting

### Conversions Not Generating

```bash
# Clear media cache
php artisan media-library:clean

# Check conversions exist
php artisan media-library:regenerate
```

### Images Not Serving Responsive Variants

1. Check conversion names match (`responsive-480`, not `responsive_480`)
2. Verify `hasGeneratedConversion()` returns true before serving
3. Check file permissions in `storage/media/`

### Missing WebP Format

- Ensure ImageMagick has WebP support: `convert -list format | grep WebP`
- May need to install: `sudo apt install imagemagick libwebp`

## Next Steps

1. **Test Pipeline**: Upload images to hoarding and verify conversions
2. **Monitoring**: Track conversion generation times in logs
3. **API Integration**: Update API endpoints to return responsive image data
4. **Frontend CDN**: Consider caching storage/ on CDN (e.g., CloudFront)
5. **Analytics**: Monitor device-specific image sizes being served

---

**Legend**:
- **Collection**: Media Library grouping for a model instance
- **Conversion**: Transformation rule (width, height, format)
- **Spatie**: Laravel Media Library package by Spatie
- **WebP**: Modern image format with superior compression
- **srcset**: HTML attribute for responsive images

<?php

namespace App\Traits;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * ResponsiveImageTrait
 *
 * Provides helper methods to generate responsive srcsets and picture elements
 * for media library conversions with WebP fallback support.
 */
trait ResponsiveImageTrait
{
    /**
     * Generate responsive srcset HTML for a media collection
     *
     * @param string $collectionName Media collection name
     * @param array $widths Widths to generate srcset for (e.g. [480, 768, 1200, 1920])
     * @return string Srcset attribute value or empty string if no media
     */
    public function generateResponsiveSrcset(string $collectionName, array $widths = [480, 768, 1200, 1920]): string
    {
        $media = $this->getFirstMedia($collectionName);
        if (!$media) {
            return '';
        }

        $srcset = [];
        foreach ($widths as $width) {
            $conversionName = "responsive-{$width}";
            if ($media->hasGeneratedConversion($conversionName)) {
                $srcset[] = "{$media->getUrl($conversionName)} {$width}w";
            }
        }

        return implode(', ', $srcset);
    }

    /**
     * Generate WebP srcset for responsive images
     *
     * @param string $collectionName Media collection name
     * @param array $widths Widths to generate srcset for
     * @return string WebP srcset attribute value or empty string if no media
     */
    public function generateWebpSrcset(string $collectionName, array $widths = [480, 768, 1200, 1920]): string
    {
        $media = $this->getFirstMedia($collectionName);
        if (!$media) {
            return '';
        }

        $srcset = [];
        foreach ($widths as $width) {
            $conversionName = "responsive-{$width}-webp";
            if ($media->hasGeneratedConversion($conversionName)) {
                $srcset[] = "{$media->getUrl($conversionName)} {$width}w";
            }
        }

        return implode(', ', $srcset);
    }

    /**
     * Generate a picture element with WebP and fallback
     *
     * @param string $collectionName Media collection name
     * @param string $alt Alt text for image
     * @param array $attrs Additional attributes (class, loading, etc.)
     * @param array $widths Widths for responsive variants
     * @return string HTML picture element or empty string if no media
     */
    public function generateResponsivePicture(
        string $collectionName,
        string $alt,
        array $attrs = [],
        array $widths = [480, 768, 1200, 1920]
    ): string {
        $media = $this->getFirstMedia($collectionName);
        if (!$media) {
            return '';
        }

        $webpSrcset = $this->generateWebpSrcset($collectionName, $widths);
        $jpegSrcset = $this->generateResponsiveSrcset($collectionName, $widths);

        // If no conversions were generated, return empty
        if (!$webpSrcset && !$jpegSrcset) {
            return '';
        }

        // Build attributes string
        $attrString = '';
        foreach ($attrs as $key => $value) {
            if ($value === true) {
                $attrString .= " {$key}";
            } else {
                $attrString .= " {$key}=\"{$value}\"";
            }
        }

        // Get fallback image (largest or preview conversion)
        $fallbackUrl = $media->hasGeneratedConversion('responsive-1920')
            ? $media->getUrl('responsive-1920')
            : ($media->hasGeneratedConversion('preview') ? $media->getUrl('preview') : $media->getUrl());

        $width = $attrs['width'] ?? '1920';
        $height = $attrs['height'] ?? '1080';

        $sizes = $attrs['sizes'] ?? '100vw';

        // Build picture HTML
        $html = '<picture>';
        if ($webpSrcset) {
            $html .= "<source type=\"image/webp\" srcset=\"{$webpSrcset}\" sizes=\"{$sizes}\">";
        }
        if ($jpegSrcset) {
            $html .= "<source srcset=\"{$jpegSrcset}\" sizes=\"{$sizes}\">";
        }
        $html .= "<img src=\"{$fallbackUrl}\" alt=\"{$alt}\" width=\"{$width}\" height=\"{$height}\"{$attrString}>";
        $html .= '</picture>';

        return $html;
    }

    /**
     * Get responsive image data for JSON/API responses
     *
     * @param string $collectionName Media collection name
     * @param array $widths Widths for responsive variants
     * @return array Image URLs by format and width
     */
    public function getResponsiveImageData(string $collectionName, array $widths = [480, 768, 1200, 1920]): array
    {
        $media = $this->getFirstMedia($collectionName);
        if (!$media) {
            return [];
        }

        $data = [
            'original' => $media->getUrl(),
            'webp' => [],
            'jpeg' => [],
        ];

        foreach ($widths as $width) {
            $webpConversion = "responsive-{$width}-webp";
            $jpegConversion = "responsive-{$width}";

            if ($media->hasGeneratedConversion($webpConversion)) {
                $data['webp'][$width] = $media->getUrl($webpConversion);
            }
            if ($media->hasGeneratedConversion($jpegConversion)) {
                $data['jpeg'][$width] = $media->getUrl($jpegConversion);
            }
        }

        return $data;
    }

    /**
     * Get hero image with responsive srcset
     * Convenience method for hoarding hero images
     *
     * @return string|null Fallback to original if conversions not ready
     */
    public function heroImage(): ?string
    {
        $media = $this->getFirstMedia('hero_image');
        return $media ? $media->getUrl('responsive-1920') ?? $media->getUrl() : null;
    }

    /**
     * Get gallery/preview thumbnail
     *
     * @param int $width Width variant (480, 768, 1200)
     * @return string|null Thumbnail URL
     */
    public function galleryThumb(int $width = 480): ?string
    {
        $media = $this->getFirstMedia('gallery');
        if (!$media) {
            return null;
        }

        $conversionName = "responsive-{$width}";
        return $media->hasGeneratedConversion($conversionName)
            ? $media->getUrl($conversionName)
            : $media->getUrl();
    }
}

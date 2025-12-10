<?php

namespace App\Services;

use App\Models\Hoarding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

/**
 * Hoarding Media Management Service
 * 
 * Handles image uploads, compression, and management for hoarding listings
 * using Spatie Media Library.
 */
class HoardingMediaService
{
    /**
     * Upload hero/primary image for hoarding.
     *
     * @param Hoarding $hoarding
     * @param UploadedFile $file
     * @param array $customProperties Additional properties to store with media
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Media
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function uploadHeroImage(Hoarding $hoarding, UploadedFile $file, array $customProperties = [])
    {
        // Delete existing hero image if present
        $hoarding->clearMediaCollection('hero_image');

        return $hoarding->addMedia($file)
            ->withCustomProperties(array_merge([
                'uploaded_by' => auth()->id(),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_at' => now()->toIso8601String(),
            ], $customProperties))
            ->usingFileName($this->generateFileName($file))
            ->toMediaCollection('hero_image');
    }

    /**
     * Upload night view image for hoarding.
     *
     * @param Hoarding $hoarding
     * @param UploadedFile $file
     * @param array $customProperties
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Media
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function uploadNightImage(Hoarding $hoarding, UploadedFile $file, array $customProperties = [])
    {
        // Delete existing night image if present
        $hoarding->clearMediaCollection('night_image');

        return $hoarding->addMedia($file)
            ->withCustomProperties(array_merge([
                'uploaded_by' => auth()->id(),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_at' => now()->toIso8601String(),
            ], $customProperties))
            ->usingFileName($this->generateFileName($file))
            ->toMediaCollection('night_image');
    }

    /**
     * Upload gallery/angle photos (multiple files).
     *
     * @param Hoarding $hoarding
     * @param array $files Array of UploadedFile instances
     * @param array $customProperties
     * @return array Array of Media models
     */
    public function uploadGalleryImages(Hoarding $hoarding, array $files, array $customProperties = []): array
    {
        $uploadedMedia = [];

        foreach ($files as $file) {
            try {
                $media = $hoarding->addMedia($file)
                    ->withCustomProperties(array_merge([
                        'uploaded_by' => auth()->id(),
                        'original_name' => $file->getClientOriginalName(),
                        'uploaded_at' => now()->toIso8601String(),
                    ], $customProperties))
                    ->usingFileName($this->generateFileName($file))
                    ->toMediaCollection('gallery');

                $uploadedMedia[] = $media;
            } catch (\Exception $e) {
                \Log::error("Failed to upload gallery image: " . $e->getMessage());
            }
        }

        return $uploadedMedia;
    }

    /**
     * Upload size/dimension overlay image.
     *
     * @param Hoarding $hoarding
     * @param UploadedFile $file
     * @param array $customProperties
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Media
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function uploadSizeOverlay(Hoarding $hoarding, UploadedFile $file, array $customProperties = [])
    {
        // Delete existing size overlay if present
        $hoarding->clearMediaCollection('size_overlay');

        return $hoarding->addMedia($file)
            ->withCustomProperties(array_merge([
                'uploaded_by' => auth()->id(),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_at' => now()->toIso8601String(),
            ], $customProperties))
            ->usingFileName($this->generateFileName($file))
            ->toMediaCollection('size_overlay');
    }

    /**
     * Delete a specific gallery image by media ID.
     *
     * @param Hoarding $hoarding
     * @param int $mediaId
     * @return bool
     */
    public function deleteGalleryImage(Hoarding $hoarding, int $mediaId): bool
    {
        try {
            $media = $hoarding->getMedia('gallery')->where('id', $mediaId)->first();
            
            if ($media) {
                $media->delete();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error("Failed to delete gallery image: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all images from a specific collection.
     *
     * @param Hoarding $hoarding
     * @param string $collection Collection name (hero_image, night_image, gallery, size_overlay)
     * @return bool
     */
    public function clearCollection(Hoarding $hoarding, string $collection): bool
    {
        try {
            $hoarding->clearMediaCollection($collection);
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to clear media collection {$collection}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all media for a hoarding organized by collection.
     *
     * @param Hoarding $hoarding
     * @return array
     */
    public function getAllMedia(Hoarding $hoarding): array
    {
        return [
            'hero_image' => $hoarding->getFirstMedia('hero_image'),
            'night_image' => $hoarding->getFirstMedia('night_image'),
            'gallery' => $hoarding->getMedia('gallery'),
            'size_overlay' => $hoarding->getFirstMedia('size_overlay'),
        ];
    }

    /**
     * Reorder gallery images.
     *
     * @param Hoarding $hoarding
     * @param array $mediaIds Array of media IDs in desired order
     * @return bool
     */
    public function reorderGallery(Hoarding $hoarding, array $mediaIds): bool
    {
        try {
            $galleryMedia = $hoarding->getMedia('gallery');

            foreach ($mediaIds as $order => $mediaId) {
                $media = $galleryMedia->where('id', $mediaId)->first();
                if ($media) {
                    $media->order_column = $order + 1;
                    $media->save();
                }
            }

            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to reorder gallery: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get hoarding media statistics.
     *
     * @param Hoarding $hoarding
     * @return array
     */
    public function getMediaStats(Hoarding $hoarding): array
    {
        $totalSize = 0;
        $totalCount = 0;

        foreach (['hero_image', 'night_image', 'gallery', 'size_overlay'] as $collection) {
            $media = $hoarding->getMedia($collection);
            $totalCount += $media->count();
            $totalSize += $media->sum('size');
        }

        return [
            'total_files' => $totalCount,
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / (1024 * 1024), 2),
            'has_hero_image' => $hoarding->hasMedia('hero_image'),
            'has_night_image' => $hoarding->hasMedia('night_image'),
            'gallery_count' => $hoarding->getMedia('gallery')->count(),
            'has_size_overlay' => $hoarding->hasMedia('size_overlay'),
        ];
    }

    /**
     * Upload multiple images at once (bulk upload).
     *
     * @param Hoarding $hoarding
     * @param array $images Array with keys: hero_image, night_image, gallery[], size_overlay
     * @return array Upload results
     */
    public function bulkUpload(Hoarding $hoarding, array $images): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        // Upload hero image
        if (isset($images['hero_image']) && $images['hero_image'] instanceof UploadedFile) {
            try {
                $media = $this->uploadHeroImage($hoarding, $images['hero_image']);
                $results['success'][] = ['type' => 'hero_image', 'media_id' => $media->id];
            } catch (\Exception $e) {
                $results['failed'][] = ['type' => 'hero_image', 'error' => $e->getMessage()];
            }
        }

        // Upload night image
        if (isset($images['night_image']) && $images['night_image'] instanceof UploadedFile) {
            try {
                $media = $this->uploadNightImage($hoarding, $images['night_image']);
                $results['success'][] = ['type' => 'night_image', 'media_id' => $media->id];
            } catch (\Exception $e) {
                $results['failed'][] = ['type' => 'night_image', 'error' => $e->getMessage()];
            }
        }

        // Upload gallery images
        if (isset($images['gallery']) && is_array($images['gallery'])) {
            $uploadedGallery = $this->uploadGalleryImages($hoarding, $images['gallery']);
            foreach ($uploadedGallery as $media) {
                $results['success'][] = ['type' => 'gallery', 'media_id' => $media->id];
            }
        }

        // Upload size overlay
        if (isset($images['size_overlay']) && $images['size_overlay'] instanceof UploadedFile) {
            try {
                $media = $this->uploadSizeOverlay($hoarding, $images['size_overlay']);
                $results['success'][] = ['type' => 'size_overlay', 'media_id' => $media->id];
            } catch (\Exception $e) {
                $results['failed'][] = ['type' => 'size_overlay', 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Generate a unique filename for uploaded file.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $random = substr(md5(uniqid()), 0, 8);
        
        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Validate image dimensions (optional helper).
     *
     * @param UploadedFile $file
     * @param int|null $minWidth
     * @param int|null $minHeight
     * @return bool
     */
    public function validateDimensions(UploadedFile $file, ?int $minWidth = null, ?int $minHeight = null): bool
    {
        try {
            $imageSize = getimagesize($file->getRealPath());
            
            if (!$imageSize) {
                return false;
            }

            [$width, $height] = $imageSize;

            if ($minWidth && $width < $minWidth) {
                return false;
            }

            if ($minHeight && $height < $minHeight) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get image metadata.
     *
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
     * @return array
     */
    public function getImageMetadata($media): array
    {
        return [
            'id' => $media->id,
            'collection' => $media->collection_name,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'size_bytes' => $media->size,
            'size_mb' => round($media->size / (1024 * 1024), 2),
            'url' => $media->getUrl(),
            'thumb_url' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : null,
            'preview_url' => $media->hasGeneratedConversion('preview') ? $media->getUrl('preview') : null,
            'custom_properties' => $media->custom_properties,
            'order' => $media->order_column,
            'created_at' => $media->created_at->toIso8601String(),
        ];
    }

    /**
     * Copy all media from one hoarding to another (useful for duplication).
     *
     * @param Hoarding $source
     * @param Hoarding $destination
     * @return bool
     */
    public function copyAllMedia(Hoarding $source, Hoarding $destination): bool
    {
        try {
            // Copy hero image
            if ($heroMedia = $source->getFirstMedia('hero_image')) {
                $heroMedia->copy($destination, 'hero_image');
            }

            // Copy night image
            if ($nightMedia = $source->getFirstMedia('night_image')) {
                $nightMedia->copy($destination, 'night_image');
            }

            // Copy gallery
            foreach ($source->getMedia('gallery') as $galleryMedia) {
                $galleryMedia->copy($destination, 'gallery');
            }

            // Copy size overlay
            if ($sizeMedia = $source->getFirstMedia('size_overlay')) {
                $sizeMedia->copy($destination, 'size_overlay');
            }

            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to copy media: " . $e->getMessage());
            return false;
        }
    }
}

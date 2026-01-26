<?php

namespace Modules\Hoardings\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use App\Services\HoardingMediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Hoarding Media Controller
 * 
 * Handles image uploads and management for hoarding listings.
 */
class HoardingMediaController extends Controller
{
    protected HoardingMediaService $mediaService;

    public function __construct(HoardingMediaService $mediaService)
    {
        $this->middleware(['auth', 'active_role:vendor']);
        $this->mediaService = $mediaService;
    }

    /**
     * Show media management page for a hoarding.
     */
    public function index(Hoarding $hoarding)
    {
        // Verify ownership
        if ($hoarding->vendor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this hoarding.');
        }

        $media = $this->mediaService->getAllMedia($hoarding);
        $stats = $this->mediaService->getMediaStats($hoarding);

        return view('vendor.hoardings.media', compact('hoarding', 'media', 'stats'));
    }

    /**
     * Upload hero/primary image.
     */
    public function uploadHero(Request $request, Hoarding $hoarding)
    {
        // Verify ownership
        if ($hoarding->vendor_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'hero_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // 10MB
        ]);

        try {
            $media = $this->mediaService->uploadHeroImage(
                $hoarding,
                $request->file('hero_image'),
                ['description' => $request->input('description')]
            );

            return response()->json([
                'success' => true,
                'message' => 'Hero image uploaded successfully.',
                'media' => $this->mediaService->getImageMetadata($media),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload night view image.
     */
    public function uploadNight(Request $request, Hoarding $hoarding)
    {
        // Verify ownership
        if ($hoarding->vendor_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'night_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        try {
            $media = $this->mediaService->uploadNightImage(
                $hoarding,
                $request->file('night_image'),
                ['description' => $request->input('description')]
            );

            return response()->json([
                'success' => true,
                'message' => 'Night image uploaded successfully.',
                'media' => $this->mediaService->getImageMetadata($media),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload gallery images (multiple files).
     */
    public function uploadGallery(Request $request, Hoarding $hoarding)
    {
        // Verify ownership
        if ($hoarding->vendor_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'gallery_images' => 'required|array|max:10',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        try {
            $uploadedMedia = $this->mediaService->uploadGalleryImages(
                $hoarding,
                $request->file('gallery_images')
            );

            $metadata = array_map(function ($media) {
                return $this->mediaService->getImageMetadata($media);
            }, $uploadedMedia);

            return response()->json([
                'success' => true,
                'message' => count($uploadedMedia) . ' gallery images uploaded successfully.',
                'media' => $metadata,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload size overlay image.
     */
    public function uploadSizeOverlay(Request $request, Hoarding $hoarding)
    {
        // Verify ownership
        if ($hoarding->vendor_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'size_overlay' => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:5120', // 5MB
        ]);

        try {
            $media = $this->mediaService->uploadSizeOverlay(
                $hoarding,
                $request->file('size_overlay'),
                ['description' => $request->input('description')]
            );

            return response()->json([
                'success' => true,
                'message' => 'Size overlay uploaded successfully.',
                'media' => $this->mediaService->getImageMetadata($media),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a specific gallery image.
     */
    public function deleteGalleryImage(Request $request, Hoarding $hoarding, $mediaId)
    {
        // Verify ownership
        if ($hoarding->vendor_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $result = $this->mediaService->deleteGalleryImage($hoarding, $mediaId);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found.',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete hero image.
     */
    public function deleteHero(Hoarding $hoarding)
    {
        // Verify ownership
        if ($hoarding->vendor_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $result = $this->mediaService->clearCollection($hoarding, 'hero_image');

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Hero image deleted successfully.' : 'Failed to delete hero image.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete night image.
     */
    public function deleteNight(Hoarding $hoarding)
    {
        // Verify ownership
        if ($hoarding->vendor_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $result = $this->mediaService->clearCollection($hoarding, 'night_image');

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Night image deleted successfully.' : 'Failed to delete night image.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete size overlay.
     */
    public function deleteSizeOverlay(Hoarding $hoarding)
    {
        // Verify ownership
        if ($hoarding->vendor_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $result = $this->mediaService->clearCollection($hoarding, 'size_overlay');

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Size overlay deleted successfully.' : 'Failed to delete size overlay.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder gallery images.
     */
    public function reorderGallery(Request $request, Hoarding $hoarding)
    {
        // Verify ownership
        if ($hoarding->vendor_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'integer',
        ]);

        try {
            $result = $this->mediaService->reorderGallery($hoarding, $request->input('media_ids'));

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Gallery reordered successfully.' : 'Failed to reorder gallery.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder gallery: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get media statistics.
     */
    public function stats(Hoarding $hoarding)
    {
        // Verify ownership
        if ($hoarding->vendor_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $stats = $this->mediaService->getMediaStats($hoarding);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}

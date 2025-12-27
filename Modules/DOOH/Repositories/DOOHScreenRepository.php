<?php
// Modules/DOOH/Repositories/DOOHScreenRepository.php

namespace Modules\DOOH\Repositories;

use Modules\DOOH\Models\DOOHScreen;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\DOOH\Models\DOOHScreenMedia;
class DOOHScreenRepository
{
    public function createStep1($vendor, $data)
    {
        $width = floatval($data['width']);
        $height = floatval($data['height']);
        $measurement_unit = $data['measurement_unit'] ?? $data['unit'] ?? null;
        $areaSqft = $measurement_unit === 'sqm'
            ? round($width * $height * 10.7639, 2)
            : round($width * $height, 2);

        return DOOHScreen::create([
            'vendor_id'        => $vendor->id,
            'category'         => $data['category'],
            'screen_type'      => $data['screen_type'],
            'width'            => $width,
            'height'           => $height,
            'measurement_unit' => $measurement_unit,
            'area_sqft'        => $areaSqft,

            'address'          => $data['address'],
            'pincode'          => $data['pincode'],
            'locality'         => $data['locality'],
            'city'             => $data['city'] ?? null,
            'state'            => $data['state'] ?? null,
            'lat'              => $data['lat'] ?? null,
            'lng'              => $data['lng'] ?? null,
            'price_per_slot'      => $data['price_per_slot'],
            'status'              => DOOHScreen::STATUS_DRAFT,
            'current_step'        => 1,
        ]);
    }

    /**
     * Store media (images/videos)
     */
    public function storeMedia(int $screenId, array $mediaFiles): array
    {
        $screen = DOOHScreen::findOrFail($screenId);

        [$shard1, $shard2] = $this->shardPath($screenId);

        $savedMedia = [];

        foreach ($mediaFiles as $index => $file) {
            $uuid = Str::uuid()->toString();
            $ext  = strtolower($file->getClientOriginalExtension());

            $directory = "dooh/screens/{$shard1}/{$shard2}/{$screenId}";
            $filename  = "{$uuid}.{$ext}";

            $path = $file->storeAs($directory, $filename, 'public');

            $savedMedia[] = DOOHScreenMedia::create([
                'dooh_screen_id' => $screenId,
                'file_path'      => $path,
                'media_type'     => in_array($ext, ['mp4', 'mov']) ? 'video' : 'image',
                'is_primary'     => $index === 0,
                'sort_order'     => $index,
            ]);
        }

        return $savedMedia;
    }

    /**
     * Delete media safely
     */
    public function deleteMedia(DOOHScreenMedia $media): void
    {
        Storage::disk('public')->delete($media->file_path);
        $media->delete();
    }

    /**
     * Folder sharding to avoid filesystem overload
     */
    private function shardPath(int $id): array
    {
        return [
            floor($id / 100) % 100,
            floor($id / 10) % 10,
        ];
    }
}

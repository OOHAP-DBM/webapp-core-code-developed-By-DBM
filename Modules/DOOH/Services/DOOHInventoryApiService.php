<?php

namespace Modules\DOOH\Services;

use Modules\DOOH\Models\DOOHScreen;
use Modules\DOOH\Models\DOOHPackage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

/**
 * DOOH Inventory API Integration Service
 * Syncs DOOH screens, packages, slots, and pricing from external API
 */
class DOOHInventoryApiService
{
    protected string $apiBaseUrl;
    protected string $apiKey;
    protected int $cacheMinutes = 60;

    public function __construct()
    {
        $this->apiBaseUrl = config('services.dooh_api.base_url', env('DOOH_API_URL'));
        $this->apiKey = config('services.dooh_api.key', env('DOOH_API_KEY'));
        if (empty($this->apiBaseUrl) || empty($this->apiKey)) {
            throw new \RuntimeException(
                'DOOH API configuration is missing. Check services.dooh_api config.'
            );
        }
    }

    /**
     * Sync all screens from external API
     */
    public function syncScreens(int $vendorId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(30)->get("{$this->apiBaseUrl}/screens");

            if (!$response->successful()) {
                throw new Exception('Failed to fetch screens from API: ' . $response->body());
            }

            $screensData = $response->json('data', []);
            $syncedCount = 0;
            $errors = [];

            foreach ($screensData as $screenData) {
                try {
                    $this->syncScreen($vendorId, $screenData);
                    $syncedCount++;
                } catch (Exception $e) {
                    $errors[] = [
                        'screen_id' => $screenData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];
                    
                    Log::error('Failed to sync DOOH screen', [
                        'screen_data' => $screenData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('DOOH screens sync completed', [
                'vendor_id' => $vendorId,
                'synced_count' => $syncedCount,
                'errors_count' => count($errors),
            ]);

            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'errors_count' => count($errors),
                'errors' => $errors,
            ];

        } catch (Exception $e) {
            Log::error('DOOH screens sync failed', [
                'vendor_id' => $vendorId,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Screen sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Sync single screen
     */
    protected function syncScreen(int $vendorId, array $screenData): DOOHScreen
    {
        $externalId = $screenData['id'];
        
        // Calculate slots per loop
        $slotDuration = $screenData['slot_duration_seconds'] ?? 10;
        $loopDuration = $screenData['loop_duration_seconds'] ?? 300;
        $slotsPerLoop = intval($loopDuration / $slotDuration);

        // Calculate total slots per day
        $totalSlotsPerDay = $screenData['total_slots_per_day'] ?? 
                           intval((24 * 60 * 60) / $loopDuration * $slotsPerLoop);

        $screenAttributes = [
            'vendor_id' => $vendorId,
            'name' => $screenData['name'],
            'description' => $screenData['description'] ?? null,
            'screen_type' => $screenData['screen_type'] ?? 'digital',
            'address' => $screenData['address'],
            'city' => $screenData['city'],
            'state' => $screenData['state'],
            'country' => $screenData['country'] ?? 'India',
            'lat' => $screenData['latitude'] ?? null,
            'lng' => $screenData['longitude'] ?? null,
            'resolution' => $screenData['resolution'] ?? null,
            'screen_size' => $screenData['screen_size'] ?? null,
            'width' => $screenData['width'] ?? null,
            'height' => $screenData['height'] ?? null,
            'slot_duration_seconds' => $slotDuration,
            'loop_duration_seconds' => $loopDuration,
            'slots_per_loop' => $slotsPerLoop,
            'min_slots_per_day' => $screenData['min_slots_per_day'] ?? 6,
            'price_per_slot' => $screenData['price_per_slot'] ?? 0,
            'price_per_month' => $screenData['price_per_month'] ?? null,
            'minimum_booking_amount' => $screenData['minimum_booking_amount'] ?? 5000,
            'total_slots_per_day' => $totalSlotsPerDay,
            'available_slots_per_day' => $screenData['available_slots_per_day'] ?? $totalSlotsPerDay,
            'allowed_formats' => $screenData['allowed_formats'] ?? ['mp4', 'jpg', 'png'],
            'max_file_size_mb' => $screenData['max_file_size_mb'] ?? 50,
            'status' => $this->mapStatus($screenData['status'] ?? 'active'),
            'sync_status' => DOOHScreen::SYNC_STATUS_SYNCED,
            'last_synced_at' => Carbon::now(),
            'sync_metadata' => $screenData['metadata'] ?? null,
        ];

        // Update or create screen
        $screen = DOOHScreen::updateOrCreate(
            ['external_screen_id' => $externalId],
            $screenAttributes
        );

        // Sync packages for this screen
        if (!empty($screenData['packages'])) {
            $this->syncPackages($screen->id, $screenData['packages']);
        }

        return $screen;
    }

    /**
     * Sync packages for a screen
     */
    protected function syncPackages(int $screenId, array $packagesData): void
    {
        foreach ($packagesData as $packageData) {
            $packageAttributes = [
                'dooh_screen_id' => $screenId,
                'package_name' => $packageData['name'],
                'description' => $packageData['description'] ?? null,
                'slots_per_day' => $packageData['slots_per_day'],
                'slots_per_month' => $packageData['slots_per_month'] ?? ($packageData['slots_per_day'] * 30),
                'loop_interval_minutes' => $packageData['loop_interval_minutes'] ?? 5,
                'time_slots' => $packageData['time_slots'] ?? null,
                'price_per_month' => $packageData['price_per_month'],
                'price_per_day' => $packageData['price_per_day'] ?? null,
                'min_booking_months' => $packageData['min_booking_months'] ?? 1,
                'max_booking_months' => $packageData['max_booking_months'] ?? 12,
                'discount_percent' => $packageData['discount_percent'] ?? 0,
                'package_type' => $packageData['package_type'] ?? DOOHPackage::TYPE_STANDARD,
                'is_active' => $packageData['is_active'] ?? true,
            ];

            DOOHPackage::updateOrCreate(
                [
                    'dooh_screen_id' => $screenId,
                    'package_name' => $packageData['name'],
                ],
                $packageAttributes
            );
        }
    }

    /**
     * Get available slots from API
     */
    public function getAvailableSlots(
        string $externalScreenId,
        string $startDate,
        string $endDate
    ): array {
        $cacheKey = "dooh_slots_{$externalScreenId}_{$startDate}_{$endDate}";

        return Cache::remember($cacheKey, $this->cacheMinutes, function () use ($externalScreenId, $startDate, $endDate) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->timeout(30)->get("{$this->apiBaseUrl}/screens/{$externalScreenId}/slots", [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);

                if (!$response->successful()) {
                    throw new Exception('Failed to fetch slots: ' . $response->body());
                }

                return $response->json('data', []);

            } catch (Exception $e) {
                Log::error('Failed to fetch DOOH slots', [
                    'screen_id' => $externalScreenId,
                    'error' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }

    /**
     * Update slot availability (after booking)
     */
    public function updateSlotAvailability(
        string $externalScreenId,
        string $startDate,
        string $endDate,
        int $slotsBooked
    ): bool {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(30)->post("{$this->apiBaseUrl}/screens/{$externalScreenId}/slots/book", [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'slots_booked' => $slotsBooked,
            ]);

            if (!$response->successful()) {
                Log::warning('Failed to update slot availability', [
                    'screen_id' => $externalScreenId,
                    'response' => $response->body(),
                ]);
                return false;
            }

            // Clear cache
            $cacheKey = "dooh_slots_{$externalScreenId}_{$startDate}_{$endDate}";
            Cache::forget($cacheKey);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to update slot availability', [
                'screen_id' => $externalScreenId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Release slots (after cancellation)
     */
    public function releaseSlots(
        string $externalScreenId,
        string $startDate,
        string $endDate,
        int $slotsToRelease
    ): bool {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(30)->post("{$this->apiBaseUrl}/screens/{$externalScreenId}/slots/release", [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'slots_released' => $slotsToRelease,
            ]);

            if (!$response->successful()) {
                Log::warning('Failed to release slots', [
                    'screen_id' => $externalScreenId,
                    'response' => $response->body(),
                ]);
                return false;
            }

            // Clear cache
            $cacheKey = "dooh_slots_{$externalScreenId}_{$startDate}_{$endDate}";
            Cache::forget($cacheKey);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to release slots', [
                'screen_id' => $externalScreenId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get screen details from API
     */
    public function getScreenDetails(string $externalScreenId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(30)->get("{$this->apiBaseUrl}/screens/{$externalScreenId}");

            if (!$response->successful()) {
                return null;
            }

            return $response->json('data');

        } catch (Exception $e) {
            Log::error('Failed to fetch screen details', [
                'screen_id' => $externalScreenId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(10)->get("{$this->apiBaseUrl}/health");

            return $response->successful();

        } catch (Exception $e) {
            Log::error('DOOH API connection test failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Map external status to internal status
     */
    protected function mapStatus(string $externalStatus): string
    {
        return match(strtolower($externalStatus)) {
            'active', 'available' => DOOHScreen::STATUS_ACTIVE,
            'inactive', 'unavailable' => DOOHScreen::STATUS_INACTIVE,
            'pending' => DOOHScreen::STATUS_PENDING_APPROVAL,
            'suspended' => DOOHScreen::STATUS_SUSPENDED,
            default => DOOHScreen::STATUS_DRAFT,
        };
    }

    /**
     * Clear all cache
     */
    public function clearCache(): void
    {
        // This is a simple implementation
        // In production, you might want to use tags or a more sophisticated approach
        Cache::flush();
        
        Log::info('DOOH API cache cleared');
    }
}

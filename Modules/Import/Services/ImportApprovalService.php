<?php

namespace Modules\Import\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Import\Entities\InventoryImportBatch;
use Modules\Import\Entities\InventoryImportStaging;
use App\Models\Hoarding;
use Modules\Hoardings\Models\OOHHoarding;
use Modules\Hoardings\Models\HoardingMedia;
use Modules\DOOH\Models\DOOHScreen;
use Modules\DOOH\Models\DOOHScreenMedia;
use Exception;

class ImportApprovalService
{
    /**
     * Process batch approval and create hoardings
     *
     * @param InventoryImportBatch $batch
     * @return array
     * @throws Exception
     */
    public function approveBatch(InventoryImportBatch $batch): array
    {
        try {
            // Validate batch status
            $this->validateBatchStatus($batch);

            $createdCount = 0;
            $failedCount = 0;

            // Wrap everything in transaction for atomicity
            DB::transaction(function () use ($batch, &$createdCount, &$failedCount) {
                // Get all valid staging records with eager loading
                $validRows = $batch->stagingRecords()
                    ->valid()
                    ->get();

                if ($validRows->isEmpty()) {
                    throw new Exception('No valid records found in batch to approve');
                }

                \Log::info('Starting batch approval', [
                    'batch_id' => $batch->id,
                    'valid_records' => $validRows->count(),
                ]);

                foreach ($validRows as $stagingRow) {
                    try {
                        $this->processRow($batch, $stagingRow);
                        $createdCount++;
                    } catch (Exception $e) {
                        $failedCount++;
                        \Log::error('Failed to process staging row', [
                            'batch_id' => $batch->id,
                            'row_id' => $stagingRow->id,
                            'code' => $stagingRow->code,
                            'error' => $e->getMessage(),
                        ]);
                        
                        // Mark row as failed
                        $stagingRow->markAsInvalid('Processing error: ' . $e->getMessage());
                        
                        // Don't throw - continue processing other rows
                    }
                }

                // Recalculate row counts after any row-level invalidation during approval
                $totalRows = $batch->stagingRecords()->count();
                $validRows = $batch->stagingRecords()->valid()->count();
                $invalidRows = max(0, $totalRows - $validRows);
                $batch->updateRowCounts($totalRows, $validRows, $invalidRows);

                // Mark approved only when every attempted valid row was created successfully
                if ($createdCount > 0 && $failedCount === 0) {
                    $batch->updateStatus('approved');
                }

                \Log::info('Batch approval completed', [
                    'batch_id' => $batch->id,
                    'created' => $createdCount,
                    'failed' => $failedCount,
                ]);
            }, attempts: 3);

            $wasApproved = ($createdCount > 0 && $failedCount === 0);

            // Send notifications if approval was successful
            if ($wasApproved) {
                // Notify all admins ONCE for the batch
                $admins = \App\Models\User::whereIn('active_role', ['admin', 'superadmin'])->get();
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\NewHoardingPendingApprovalNotification($batch));
                }

                // Notify vendor ONCE for the batch (email only, no in-app notification for bulk approval)
                $vendor = $batch->vendor;
                if ($vendor) {
                    $vendor->notifyVendorEmails(new \Modules\Mail\InventoryImportBatchApprovedMail($batch));
                }
            }

            return [
                'success' => $wasApproved,
                'created_count' => $createdCount,
                'failed_count' => $failedCount,
                'total_processed' => $createdCount + $failedCount,
                'status' => $batch->fresh()->status,
                'message' => $wasApproved
                    ? 'Import approved and hoardings created successfully'
                    : ($createdCount === 0
                        ? 'Approval failed: no rows could be created. Batch status unchanged.'
                        : 'Approval partially failed: some rows could not be created. Batch status unchanged.'),
            ];
        } catch (Exception $e) {
            \Log::error('Batch approval failed', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Validate batch is in correct status for approval
     *
     * @param InventoryImportBatch $batch
     * @throws Exception
     */
    protected function validateBatchStatus(InventoryImportBatch $batch): void
    {
        if ($batch->status === 'approved') {
            throw new Exception('This batch is already approved');
        }

        if (!in_array($batch->status, ['processed', 'completed'], true)) {
            throw new Exception(
                "Batch must be in 'processed' or 'completed' status to approve. Current status: {$batch->status}"
            );
        }

        if ($batch->valid_rows === 0) {
            throw new Exception('No valid rows to process in this batch');
        }
    }

    /**
     * Process single staging row to create hoarding and related records
     *
     * @param InventoryImportBatch $batch
     * @param InventoryImportStaging $stagingRow
     * @throws Exception
     */
    protected function processRow(InventoryImportBatch $batch, InventoryImportStaging $stagingRow): void
    {
        \Log::info('Processing staging row', [
            'batch_id' => $batch->id,
            'row_id' => $stagingRow->id,
            'code' => $stagingRow->code,
        ]);
        // dd($stagingRow->toArray());

        // STEP 1: Create parent Hoarding record
        // Use base_monthly_price if present, else fallback to d_c_p_m, else fallback to 0
        $baseMonthlyPrice = $this->stagingValue($stagingRow, 'base_monthly_price', null);
        if ($baseMonthlyPrice === null || $baseMonthlyPrice === '' || $baseMonthlyPrice == 0) {
            $baseMonthlyPrice = $this->stagingValue($stagingRow, 'd_c_p_m', 0);
        }
        $autoApproval = env('Auto_Hoarding_Approval', false);
        $hoardingStatus = $autoApproval ? 'active' : 'pending_approval';
        $hoarding = Hoarding::create([
            'vendor_id' => $batch->vendor_id,
            'title' => $stagingRow->code, // Temporary, will be auto-generated by model
            'hoarding_type' => $stagingRow->media_type,
            'category' => $this->stagingValue($stagingRow, 'category', null),
            'address' => $this->stagingValue($stagingRow, 'address', null),
            'city' => $stagingRow->city,
            'state' => $this->stagingValue($stagingRow, 'state', null),
            'locality' => $this->stagingValue($stagingRow, 'locality', null),
            'landmark' => $this->stagingValue($stagingRow, 'landmark', null),
            'pincode' => $this->stagingValue($stagingRow, 'pincode', null),
            'latitude' => $this->stagingValue($stagingRow, 'latitude', null),
            'longitude' => $this->stagingValue($stagingRow, 'longitude', null),
            'base_monthly_price' => $baseMonthlyPrice,
            'monthly_price' => $this->stagingValue($stagingRow, 'monthly_price', 0),
            'commission_percent' => (float) ($this->stagingValue($stagingRow, 'commission_percent', 0) ?? 0),
            'graphics_charge' => $this->stagingValue($stagingRow, 'graphics_charge', null),
            'survey_charge' => $this->stagingValue($stagingRow, 'survey_charge', null),
            'min_booking_duration' => $this->stagingValue($stagingRow, 'min_booking_duration', null),
            'discount_type' => $this->stagingValue($stagingRow, 'discount_type', null),
            'discount_value' => $this->stagingValue($stagingRow, 'discount_value', null),
            'currency' => $this->stagingValue($stagingRow, 'currency', 'INR'),
            'status' => $hoardingStatus,
        ]);

        if (!$hoarding) {
            throw new Exception('Failed to create hoarding record');
        }

        \Log::info('Created hoarding', [
            'hoarding_id' => $hoarding->id,
            'vendor_id' => $batch->vendor_id,
            'code' => $stagingRow->code,
        ]);

        // STEP 2: Create type-specific records
        if ($stagingRow->media_type === 'ooh') {
            $this->createOOHRecords($hoarding, $stagingRow);
        } elseif ($stagingRow->media_type === 'dooh') {
            $this->createDOOHRecords($hoarding, $stagingRow);
        } else {
            throw new Exception("Unknown media type: {$stagingRow->media_type}");
        }
    }

    /**
     * Create OOH hoarding records (OOHHoarding + HoardingMedia)
     *
     * @param Hoarding $hoarding
     * @param InventoryImportStaging $stagingRow
     * @throws Exception
     */
    protected function createOOHRecords(Hoarding $hoarding, InventoryImportStaging $stagingRow): void
    {
        try {
            // Create OOHHoarding record
            $oohHoarding = OOHHoarding::create([
                'hoarding_id' => $hoarding->id,
                'width' => $stagingRow->width,
                'height' => $stagingRow->height,
                'measurement_unit' => $this->stagingValue($stagingRow, 'measurement_unit', 'ft'),
                'lighting_type' => $this->stagingValue($stagingRow, 'lighting_type', null),
                'printing_charge' => $this->stagingValue($stagingRow, 'printing_charge', null),
                'mounting_charge' => $this->stagingValue($stagingRow, 'mounting_charge', null),
                'remounting_charge' => $this->stagingValue($stagingRow, 'remounting_charge', null),
                'lighting_charge' => $this->stagingValue($stagingRow, 'lighting_charge', null),
            ]);

            if (!$oohHoarding) {
                throw new Exception('Failed to create OOH hoarding record');
            }

            \Log::info('Created OOH hoarding', [
                'ooh_hoarding_id' => $oohHoarding->id,
                'hoarding_id' => $hoarding->id,
                'width' => $stagingRow->width,
                'height' => $stagingRow->height,
            ]);

            // Create HoardingMedia record if image exists
            if ($stagingRow->image_name) {
                $originalPath = $this->resolveImagePath($stagingRow);
                $disk = \Storage::disk('public');
                $directory = "hoardings/media/{$hoarding->id}";
                $uuid = \Illuminate\Support\Str::uuid()->toString();
                $ext = strtolower(pathinfo($stagingRow->image_name, PATHINFO_EXTENSION));
                $filename = "$uuid.$ext";
                $targetPath = "$directory/$filename";

                // Copy/move image to public storage
                if (\Storage::disk('local')->exists($originalPath)) {
                    $fileContents = \Storage::disk('local')->get($originalPath);
                    $disk->put($targetPath, $fileContents);
                }

                $media = HoardingMedia::create([
                    'hoarding_id' => $hoarding->id,
                    'file_path' => $targetPath,
                    'media_type' => 'image',
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);

                if (!$media) {
                    throw new Exception('Failed to create hoarding media record');
                }

                \Log::info('Created hoarding media', [
                    'media_id' => $media->id,
                    'hoarding_id' => $hoarding->id,
                    'image' => $stagingRow->image_name,
                ]);
            }
        } catch (Exception $e) {
            throw new Exception("Failed to create OOH records: {$e->getMessage()}");
        }
    }

    /**
     * Create DOOH screen records (DOOHScreen + DOOHScreenMedia)
     *
     * @param Hoarding $hoarding
     * @param InventoryImportStaging $stagingRow
     * @throws Exception
     */
    protected function createDOOHRecords(Hoarding $hoarding, InventoryImportStaging $stagingRow): void
    {
        try {
            // Create DOOHScreen record
            $doohScreen = DOOHScreen::create([
                'hoarding_id' => $hoarding->id,
                'screen_type' => $this->stagingValue($stagingRow, 'screen_type', 'led'),
                'width' => $stagingRow->width,
                'height' => $stagingRow->height,
                'measurement_unit' => $this->stagingValue($stagingRow, 'measurement_unit', 'px'),
                'slot_duration_seconds' => $this->stagingValue($stagingRow, 'slot_duration_seconds', 10),
                'price_per_slot' => $this->stagingValue($stagingRow, 'price_per_slot', 0),
                'screen_run_time' => $this->stagingValue($stagingRow, 'screen_run_time', null),
                'total_slots_per_day' => $this->stagingValue($stagingRow, 'total_slots_per_day', null),
                'total_slots_per_day' => $this->stagingValue($stagingRow, 'total_slots_per_day', null),
                // 'base_monthly_price' => $this->stagingValue($stagingRow, 'base_monthly_price', null),
                // 'monthly_price' => $this->stagingValue($stagingRow, 'monthly_price', null),
            ]);

            if (!$doohScreen) {
                throw new Exception('Failed to create DOOH screen record');
            }

            \Log::info('Created DOOH screen', [
                'screen_id' => $doohScreen->id,
                'hoarding_id' => $hoarding->id,
                'width' => $stagingRow->width,
                'height' => $stagingRow->height,
            ]);

            // Create DOOHScreenMedia record if image exists
            if ($stagingRow->image_name) {
                $originalPath = $this->resolveImagePath($stagingRow);
                $disk = \Storage::disk('public');
                // Sharding logic (same as storeMedia)
                $screenId = $doohScreen->id;
                $shard1 = (int) ($screenId / 1000000);
                $shard2 = (int) (($screenId % 1000000) / 1000);
                $directory = "dooh/screens/{$shard1}/{$shard2}/{$screenId}";
                $uuid = \Illuminate\Support\Str::uuid()->toString();
                $ext = strtolower(pathinfo($stagingRow->image_name, PATHINFO_EXTENSION));
                $filename = "$uuid.$ext";
                $targetPath = "$directory/$filename";

                // Copy/move image to public storage
                if (\Storage::disk('local')->exists($originalPath)) {
                    $fileContents = \Storage::disk('local')->get($originalPath);
                    $disk->put($targetPath, $fileContents);
                }

                $media = DOOHScreenMedia::create([
                    'dooh_screen_id' => $doohScreen->id,
                    'file_path' => $targetPath,
                    'media_type' => 'image',
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);

                if (!$media) {
                    throw new Exception('Failed to create DOOH screen media record');
                }

                \Log::info('Created DOOH screen media', [
                    'media_id' => $media->id,
                    'screen_id' => $doohScreen->id,
                    'image' => $stagingRow->image_name,
                ]);
            }
        } catch (Exception $e) {
            throw new Exception("Failed to create DOOH records: {$e->getMessage()}");
        }
    }

    /**
     * Resolve image file path from staging row
     *
     * @param InventoryImportStaging $stagingRow
     * @return string
     */
    protected function resolveImagePath(InventoryImportStaging $stagingRow): string
    {
        // Images are stored in: storage/app/imports/{batch_id}/images/{image_name}
        $imagePath = "imports/{$stagingRow->batch_id}/images/{$stagingRow->image_name}";

        $disk = Storage::disk('local');

        if ($disk->exists($imagePath)) {
            return $imagePath;
        }

        $searchRoot = "imports/{$stagingRow->batch_id}/images";
        if ($disk->exists($searchRoot)) {
            foreach ($disk->allFiles($searchRoot) as $file) {
                if (basename($file) === $stagingRow->image_name) {
                    return $file;
                }
            }
        }

        return $imagePath;
    }

    /**
     * Read value from explicit staging column, fallback to extra_attributes.
     *
     * @param InventoryImportStaging $stagingRow
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function stagingValue(InventoryImportStaging $stagingRow, string $key, $default = null)
    {
        $value = $stagingRow->{$key} ?? null;

        if ($value !== null && $value !== '') {
            return $value;
        }

        return $stagingRow->getExtraAttribute($key, $default);
    }
}

<?php

namespace Modules\Import\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\Import\Entities\InventoryImportBatch;
use Modules\Import\Services\PythonImportService;
use Modules\Import\Exceptions\ImportApiException;
use Exception;
use ZipArchive;

class ProcessInventoryImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Batch chunk size for bulk inserts
     */
    protected const CHUNK_SIZE = 500;

    /**
     * @var InventoryImportBatch
     */
    protected InventoryImportBatch $batch;

    /**
     * @var string
     */
    protected string $excelPath;

    /**
     * @var string
     */
    protected string $pptPath;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 900;

    /**
     * The number of seconds after which the job's unique lock expires.
     *
     * @var int
     */
    public $uniqueFor = 3600;

    /**
     * Create a new job instance.
     *
     * @param InventoryImportBatch $batch
     * @param string $excelPath
     * @param string $pptPath
     */
    public function __construct(InventoryImportBatch $batch, string $excelPath, string $pptPath)
    {
        $this->batch = $batch;
        $this->excelPath = $excelPath;
        $this->pptPath = $pptPath;
        $this->queue = config('import.batch.queue', 'default');
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        try {
            $this->batch->markAsProcessing();

            \Log::info('Starting inventory import processing', [
                'batch_id' => $this->batch->id,
                'vendor_id' => $this->batch->vendor_id,
                'media_type' => $this->batch->media_type,
                'excel_file' => [
                    'path' => $this->excelPath,
                    'name' => basename($this->excelPath),
                    'size_bytes' => @filesize($this->excelPath) ?: null,
                ],
                'ppt_file' => [
                    'path' => $this->pptPath,
                    'name' => basename($this->pptPath),
                    'size_bytes' => @filesize($this->pptPath) ?: null,
                ],
            ]);

            // Get API response from Python service
            $pythonService = app(PythonImportService::class);
            $apiResponse = $pythonService->processImport(
                $this->excelPath,
                $this->pptPath,
                $this->batch->vendor_id,
                $this->batch->media_type
            );

            if (!$apiResponse['success']) {
                throw ImportApiException::apiError(
                    $apiResponse['message'] ?? 'Import processing failed'
                );
            }

            $apiLogPayload = $apiResponse;
            unset($apiLogPayload['images_zip_base64']);

            \Log::info('Inventory import API data received', [
                'batch_id' => $this->batch->id,
                'api_response_without_base64_zip' => $apiLogPayload,
                'received_data' => $apiResponse['data'] ?? [],
            ]);

            // Store and extract image archive from Python response, if provided
            // This should not fail the whole import when ZIP handling is unavailable.
            try {
                $this->ingestImageArchive($apiResponse);
            } catch (Exception $e) {
                \Log::warning('Image archive ingestion skipped', [
                    'batch_id' => $this->batch->id,
                    'reason' => $e->getMessage(),
                ]);
            }

            // Process rows with bulk insert
            $this->processApiRows(
                $apiResponse['data'] ?? [],
                $apiResponse['total_rows'] ?? 0
            );

            // Mark as completed
            $this->batch->markAsCompleted();

            \Log::info('Inventory import processing completed', [
                'batch_id' => $this->batch->id,
                'total_rows' => $this->batch->total_rows,
                'valid_rows' => $this->batch->valid_rows,
                'invalid_rows' => $this->batch->invalid_rows,
            ]);
        } catch (ImportApiException $e) {
            $this->batch->markAsFailed($e->getMessage());
            \Log::error('Import API error', [
                'batch_id' => $this->batch->id,
                'api_code' => $e->getApiCode(),
                'error' => $e->getMessage(),
            ]);

            $apiCode = (string) $e->getApiCode();
            $isClientError = str_starts_with($apiCode, 'API_ERROR_401')
                || str_starts_with($apiCode, 'API_ERROR_403')
                || str_starts_with($apiCode, 'API_ERROR_404')
                || str_starts_with($apiCode, 'API_ERROR_422');

            if ($isClientError) {
                return;
            }

            throw $e;
        } catch (Exception $e) {
            $this->batch->markAsFailed($e->getMessage());
            \Log::error('Inventory import processing failed', [
                'batch_id' => $this->batch->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Process API rows and bulk insert into staging table
     *
     * @param array $apiRows
     * @param int $totalRows
     * @return void
     */
    protected function processApiRows(array $apiRows, int $totalRows): void
    {
        $validCount = 0;
        $invalidCount = 0;

        // Transform rows into staging format and collect in chunks
        $stagingRows = [];

        foreach ($apiRows as $row) {
            $transformed = $this->transformRow($row);

            // Track validity
            if ($transformed['status'] === 'valid') {
                $validCount++;
            } else {
                $invalidCount++;
            }

            $stagingRows[] = $transformed;

            // Insert chunk when reaching batch size to reduce memory usage
            if (count($stagingRows) >= self::CHUNK_SIZE) {
                $this->bulkInsertChunk($stagingRows);
                $stagingRows = [];
            }
        }

        // Insert remaining rows
        if (!empty($stagingRows)) {
            $this->bulkInsertChunk($stagingRows);
        }

        // Update batch row counts
        $this->batch->updateRowCounts($totalRows, $validCount, $invalidCount);
    }

    /**
     * Transform API row into staging table format
     *
     * @param array $row
     * @return array
     */
    protected function transformRow(array $row): array
    {
        try {
            $pythonStatus = strtolower((string) ($row['status'] ?? ''));
            $pythonErrors = $row['errors'] ?? [];

            if (is_string($pythonErrors)) {
                $pythonErrors = [$pythonErrors];
            }

            if (!is_array($pythonErrors)) {
                $pythonErrors = [];
            }

            if (
                $pythonStatus === 'invalid' ||
                !empty($pythonErrors) ||
                !empty($row['error_message'])
            ) {
                $errorMessage = $row['error_message'] ?? implode('; ', array_filter($pythonErrors));
                throw new Exception($errorMessage ?: 'Row marked invalid by Python API');
            }

            // Validate required fields
            $this->validateRowFields($row);

            return [
                'batch_id' => $this->batch->id,
                'vendor_id' => $this->batch->vendor_id,
                'media_type' => $this->batch->media_type,
                'code' => $this->toNullableString($this->rowValue($row, ['code', 'media_id', 'Media ID'], '')),
                'city' => $this->toNullableString($this->rowValue($row, ['city', 'City'], '')),
                'category' => $this->toNullableString($this->rowValue($row, ['category', 'media_type_name', 'Media Type'])),
                'address' => $this->toNullableString($this->rowValue($row, ['address', 'full_address', 'Full Address'])),
                'locality' => $this->toNullableString($this->rowValue($row, ['locality', 'Locality'])),
                'landmark' => $this->toNullableString($this->rowValue($row, ['landmark', 'Landmark'])),
                'state' => $this->toNullableString($this->rowValue($row, ['state', 'State'])),
                'pincode' => $this->toNullableString($this->rowValue($row, ['pincode', 'Pincode'])),
                'latitude' => $this->toNullableDecimal($this->rowValue($row, ['latitude', 'Latitude']), 7),
                'longitude' => $this->toNullableDecimal($this->rowValue($row, ['longitude', 'Longitude']), 7),
                'width' => $this->toNullableDecimal($this->rowValue($row, ['width', 'Width']), 2),
                'height' => $this->toNullableDecimal($this->rowValue($row, ['height', 'Height']), 2),
                'measurement_unit' => $this->toNullableString($this->rowValue($row, ['measurement_unit', 'unit', 'Unit'])),
                'lighting_type' => $this->toNullableString($this->rowValue($row, ['lighting_type', 'illumination', 'Illumination'])),
                'screen_type' => $this->toNullableString($this->rowValue($row, ['screen_type', 'Screen Type'])),
                // 'image_name' => $this->toNullableString($row['image_name'] ?? null),
               'image_name' => $this->toNullableString($row['image_name'] ?? null),
                // Map base_monthly_price from d_c_p_m if present, else fallback to other fields
                'base_monthly_price' => $this->toNullableDecimal($this->rowValue($row, ['display_monthly_price', 'base_monthly_price', 'd_c_p_m', 'dcpm_or_price', 'DCPM / Price', 'price']), 2),
                'monthly_price' => $this->toNullableDecimal($this->rowValue($row, ['sale_price', 'monthly_price', 'monthly_sale_price', 'Monthly Sale Price']), 2),
                'weekly_price_1' => $this->toNullableDecimal($this->rowValue($row, ['weekly_price_1']), 2),
                'weekly_price_2' => $this->toNullableDecimal($this->rowValue($row, ['weekly_price_2']), 2),
                'weekly_price_3' => $this->toNullableDecimal($this->rowValue($row, ['weekly_price_3']), 2),
                'price_per_slot' => $this->toNullableDecimal($this->rowValue($row, ['price_per_slot', 'price_per_spot', 'Price Per Spot (₹)']), 2),
                'slot_duration_seconds' => $this->toNullableInt($this->rowValue($row, ['slot_duration_seconds', 'ad_duration_sec', 'Ad Duration (Sec)'])),
                'screen_run_time' => $this->toNullableInt($this->rowValue($row, ['screen_run_time', 'daily_play_hours', 'Daily Play Hours'])),
                'total_slots_per_day' => $this->toNullableInt($this->rowValue($row, ['total_slots_per_day', 'spots_per_day', 'Spots Per Day'])),
                'total_slots_per_day' => $this->toNullableInt($this->rowValue($row, ['total_slots_per_day', 'spots_per_day', 'Spots Per Day'])),
                'min_booking_duration' => $this->toNullableInt($this->rowValue($row, ['min_booking_duration', 'minimum_duration_days', 'Minimum Duration (Days)'])),
                'graphics_charge' => $this->toNullableDecimal($this->rowValue($row, ['graphics_charge', 'designing_charge', 'Designing Charge']), 2),
                'survey_charge' => $this->toNullableDecimal($this->rowValue($row, ['survey_charge']), 2),
                'printing_charge' => $this->toNullableDecimal($this->rowValue($row, ['printing_charge', 'Printing Charge']), 2),
                'mounting_charge' => $this->toNullableDecimal($this->rowValue($row, ['mounting_charge', 'Mounting Charge']), 2),
                'remounting_charge' => $this->toNullableDecimal($this->rowValue($row, ['remounting_charge']), 2),
                'lighting_charge' => $this->toNullableDecimal($this->rowValue($row, ['lighting_charge']), 2),
                'discount_type' => $this->toNullableString($this->rowValue($row, ['discount_type', 'Discount Type'])),
                'discount_value' => $this->toNullableDecimal($this->rowValue($row, ['discount_value', 'Discount Value']), 2),
                'availability' => $this->toNullableString($this->rowValue($row, ['availability', 'Availability'])),
                'currency' => $this->toNullableString($this->rowValue($row, ['currency'], 'INR')),
                'available_from' => $this->toNullableDate($this->rowValue($row, ['available_from'])),
                'available_to' => $this->toNullableDate($this->rowValue($row, ['available_to'])),
                'extra_attributes' => $this->extractExtraAttributes($row),
                'status' => 'valid',
                'error_message' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        } catch (Exception $e) {
            \Log::info('Import row keys/values', ['row' => $row]);
            return [
                'batch_id' => $this->batch->id,
                'vendor_id' => $this->batch->vendor_id,
                'media_type' => $this->batch->media_type,
                'code' => $this->toNullableString($this->rowValue($row, ['code', 'media_id', 'Media ID'], 'UNKNOWN')) ?? 'UNKNOWN',
                'city' => null,
                'category' => null,
                'address' => null,
                'locality' => null,
                'landmark' => null,
                'state' => null,
                'pincode' => null,
                'latitude' => null,
                'longitude' => null,
                'width' => null,
                'height' => null,
                'measurement_unit' => null,
                'lighting_type' => null,
                'screen_type' => null,
                'image_name' => null,
                'base_monthly_price' => null,
                'monthly_price' => null,
                'weekly_price_1' => null,
                'weekly_price_2' => null,
                'weekly_price_3' => null,
                'price_per_slot' => null,
                'slot_duration_seconds' => null,
                'screen_run_time' => null,
                'total_slots_per_day' => null,
                'total_slots_per_day' => null,
                'min_booking_duration' => null,
                'graphics_charge' => null,
                'survey_charge' => null,
                'printing_charge' => null,
                'mounting_charge' => null,
                'remounting_charge' => null,
                'lighting_charge' => null,
                'discount_type' => null,
                'discount_value' => null,
                'availability' => null,
                'currency' => null,
                'available_from' => null,
                'available_to' => null,
                'extra_attributes' => null,
                'status' => 'invalid',
                'error_message' => $this->toNullableString($e->getMessage()) ?? 'Invalid row',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    /**
     * Validate required row fields
     *
     * @param array $row
     * @throws Exception
     */
    protected function validateRowFields(array $row): void
    {
        $code = $this->rowValue($row, ['code', 'media_id', 'Media ID']);

        if (empty($code)) {
            throw new Exception('Code field is required');
        }

        if (isset($row['width']) && !is_numeric($row['width'])) {
            throw new Exception('Width must be numeric');
        }

        if (isset($row['height']) && !is_numeric($row['height'])) {
            throw new Exception('Height must be numeric');
        }
    }

    /**
     * Extract extra attributes from row
     *
     * @param array $row
     * @return string|null
     */
    protected function extractExtraAttributes(array $row): ?string
    {
        \Log::info('Extracting extra attributes from row', ['row' => $row]);
        $standardFields = [
            'code',
            'media_id',
            'Media ID',
            'city',
            'City',
            'category',
            'media_type_name',
            'Media Type',
            'address',
            'full_address',
            'Full Address',
            'locality',
            'Locality',
            'landmark',
            'Landmark',
            'state',
            'State',
            'pincode',
            'Pincode',
            'latitude',
            'Latitude',
            'longitude',
            'Longitude',
            'width',
            'Width',
            'height',
            'Height',
            'measurement_unit',
            'unit',
            'Unit',
            'lighting_type',
            'illumination',
            'Illumination',
            'screen_type',
            'Screen Type',
            'image_name',
            'base_monthly_price',
            'dcpm_or_price',
            'DCPM / Price',
            'monthly_price',
            'monthly_sale_price',
            'Monthly Sale Price',
            'weekly_price_1',
            'weekly_price_2',
            'weekly_price_3',
            'price_per_slot',
            'price_per_spot',
            'Price Per Spot (₹)',
            'slot_duration_seconds',
            'ad_duration_sec',
            'Ad Duration (Sec)',
            'screen_run_time',
            'daily_play_hours',
            'Daily Play Hours',
            'total_slots_per_day',
            'spots_per_day',
            'Spots Per Day',
            'total_slots_per_day',
            'min_booking_duration',
            'minimum_duration_days',
            'Minimum Duration (Days)',
            'graphics_charge',
            'designing_charge',
            'Designing Charge',
            'survey_charge',
            'printing_charge',
            'Printing Charge',
            'mounting_charge',
            'Mounting Charge',
            'remounting_charge',
            'lighting_charge',
            'discount_type',
            'Discount Type',
            'discount_value',
            'Discount Value',
            'availability',
            'Availability',
            'currency',
            'available_from',
            'available_to',
            'status',
            'errors',
            'error_message',
        ];

        $extra = [];

        foreach ($row as $key => $value) {
            if (!in_array($key, $standardFields, true) && $value !== null && $value !== '') {
                $extra[$key] = $value;
            }
        }

        if (empty($extra)) {
            return null;
        }

        return json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Convert mixed value to a nullable string.
     *
     * @param mixed $value
     * @return string|null
     */
    protected function toNullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $encoded = $encoded === false ? null : trim($encoded);
            return $encoded === '' ? null : $encoded;
        }

        $stringValue = trim((string) $value);
        return $stringValue === '' ? null : $stringValue;
    }

    /**
     * Return first matching value from a row using an ordered list of keys.
     *
     * @param array $row
     * @param array $keys
     * @param mixed $default
     * @return mixed
     */
    protected function rowValue(array $row, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return $default;
    }

    /**
     * Convert mixed value to nullable decimal string.
     *
     * @param mixed $value
     * @param int $scale
     * @return string|null
     */
    protected function toNullableDecimal($value, int $scale = 2): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, $scale, '.', '');
    }

    /**
     * Convert mixed value to nullable integer.
     *
     * @param mixed $value
     * @return int|null
     */
    protected function toNullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    /**
     * Convert mixed value to nullable date (Y-m-d).
     *
     * @param mixed $value
     * @return string|null
     */
    protected function toNullableDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse((string) $value)->toDateString();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Bulk insert a chunk of rows using transaction
     *
     * @param array $rows
     * @return void
     */
    protected function bulkInsertChunk(array $rows): void
    {
        DB::transaction(function () use ($rows) {
            // Use insert() instead of create() for better performance
            // insert() bypasses model creation and directly inserts records
            DB::table('inventory_import_staging')->insert($rows);

            \Log::info('Inserted staging chunk', [
                'batch_id' => $this->batch->id,
                'rows_count' => count($rows),
            ]);
        });
    }

    /**
     * Ingest images archive from Python API response (base64 or URL).
     *
     * @param array $apiResponse
     * @return void
     * @throws Exception
     */
    protected function ingestImageArchive(array $apiResponse): void
    {
        $zipBase64 = $apiResponse['images_zip_base64'] ?? null;
        $zipUrl = $apiResponse['images_zip_url'] ?? null;

        if (empty($zipBase64) && empty($zipUrl)) {
            return;
        }

        $disk = Storage::disk('local');
        $batchRoot = "imports/{$this->batch->id}";
        $imagesDir = "{$batchRoot}/images";
        $zipPath = "{$batchRoot}/images_bundle.zip";

        $disk->makeDirectory($imagesDir);

        $zipBinary = null;

        if (!empty($zipBase64)) {
            $payload = (string) $zipBase64;

            if (str_starts_with($payload, 'data:')) {
                $parts = explode(',', $payload, 2);
                $payload = $parts[1] ?? '';
            }

            $decoded = base64_decode($payload, true);
            if ($decoded === false) {
                throw new Exception('Invalid images ZIP base64 payload from Python API');
            }

            $zipBinary = $decoded;
        } elseif (!empty($zipUrl)) {
            $response = Http::timeout((int) config('import.python_timeout', 300))
                ->get((string) $zipUrl);

            if ($response->failed()) {
                throw new Exception('Failed to download images ZIP from Python API URL');
            }

            $zipBinary = $response->body();
        }

        if ($zipBinary === null || $zipBinary === '') {
            throw new Exception('Empty images ZIP payload received from Python API');
        }

        $disk->put($zipPath, $zipBinary);

        $zipAbsolutePath = $disk->path($zipPath);
        $imagesAbsolutePath = $disk->path($imagesDir);

        if (!is_dir($imagesAbsolutePath)) {
            mkdir($imagesAbsolutePath, 0755, true);
        }

        if (!class_exists(ZipArchive::class)) {
            \Log::warning('ZipArchive extension is not available; storing ZIP without extraction', [
                'batch_id' => $this->batch->id,
                'zip_path' => $zipPath,
            ]);

            return;
        }

        $zip = new ZipArchive();
        $openResult = $zip->open($zipAbsolutePath);

        if ($openResult !== true) {
            throw new Exception('Unable to open images ZIP archive');
        }

        $zip->extractTo($imagesAbsolutePath);
        $zip->close();

        $allExtractedFiles = $disk->allFiles($imagesDir);

        \Log::info('Extracted image archive for import batch', [
            'batch_id' => $this->batch->id,
            'images_dir' => $imagesDir,
            'zip_path' => $zipPath,
            'extracted_files_count' => count($allExtractedFiles),
            'extracted_files' => $allExtractedFiles,
        ]);
    }

    /**
     * Get the unique ID for the job.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return "inventory-import-batch-{$this->batch->id}";
    }

    /**
     * Prepare the object for serialization.
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'batch_id' => $this->batch->id,
            'excelPath' => $this->excelPath,
            'pptPath' => $this->pptPath,
        ];
    }

    /**
     * Restore the object after unserialization.
     *
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->batch = InventoryImportBatch::find($data['batch_id']);
        $this->excelPath = $data['excelPath'];
        $this->pptPath = $data['pptPath'];
    }
}

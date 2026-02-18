<?php

namespace Modules\Import\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Modules\Import\Entities\InventoryImportBatch;
use Modules\Import\Services\PythonImportService;
use Modules\Import\Exceptions\ImportApiException;
use Exception;

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
            // Validate required fields
            $this->validateRowFields($row);

            return [
                'batch_id' => $this->batch->id,
                'vendor_id' => $this->batch->vendor_id,
                'media_type' => $this->batch->media_type,
                'code' => trim($row['code'] ?? ''),
                'city' => trim($row['city'] ?? '') ?: null,
                'width' => isset($row['width']) ? (float) $row['width'] : null,
                'height' => isset($row['height']) ? (float) $row['height'] : null,
                'image_name' => $row['image_name'] ?? null,
                'extra_attributes' => $this->extractExtraAttributes($row),
                'status' => 'valid',
                'error_message' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        } catch (Exception $e) {
            return [
                'batch_id' => $this->batch->id,
                'vendor_id' => $this->batch->vendor_id,
                'media_type' => $this->batch->media_type,
                'code' => trim($row['code'] ?? 'UNKNOWN'),
                'city' => null,
                'width' => null,
                'height' => null,
                'image_name' => null,
                'extra_attributes' => null,
                'status' => 'invalid',
                'error_message' => $e->getMessage(),
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
        if (empty($row['code'])) {
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
     * @return array|null
     */
    protected function extractExtraAttributes(array $row): ?array
    {
        $standardFields = ['code', 'city', 'width', 'height', 'image_name'];
        $extra = [];

        foreach ($row as $key => $value) {
            if (!in_array($key, $standardFields) && !empty($value)) {
                $extra[$key] = $value;
            }
        }

        return !empty($extra) ? $extra : null;
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

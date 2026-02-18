<?php

namespace Modules\Import\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Import\Http\Requests\UploadInventoryImportRequest;
use Modules\Import\Entities\InventoryImportBatch;
use Modules\Import\Jobs\ProcessInventoryImportJob;
use Exception;

class ImportController extends Controller
{
    /**
     * Store path for imports
     */
    protected const IMPORT_STORAGE_PATH = 'imports';

    /**
     * Disk name for storage
     */
    protected const IMPORT_DISK = 'local';

    /**
     * Upload and process inventory import
     *
     * @param UploadInventoryImportRequest $request
     * @return JsonResponse
     */
    public function uploadInventoryImport(UploadInventoryImportRequest $request): JsonResponse
    {
        try {
            // Create batch record
            $batch = InventoryImportBatch::create([
                'vendor_id' => auth()->id(),
                'media_type' => $request->input('media_type'),
                'status' => 'uploaded',
                'total_rows' => 0,
                'valid_rows' => 0,
                'invalid_rows' => 0,
            ]);

            \Log::info('Created import batch', [
                'batch_id' => $batch->id,
                'vendor_id' => auth()->id(),
                'media_type' => $request->input('media_type'),
            ]);

            // Create batch-specific storage path
            $batchPath = self::IMPORT_STORAGE_PATH . '/' . $batch->id;

            // Store files
            $excelPath = $this->storeFile(
                $request->file('excel'),
                $batchPath,
                'inventory'
            );

            $pptPath = $this->storeFile(
                $request->file('ppt'),
                $batchPath,
                'presentation'
            );

            \Log::info('Stored import files', [
                'batch_id' => $batch->id,
                'excel_path' => $excelPath,
                'ppt_path' => $pptPath,
            ]);

            // Dispatch processing job
            ProcessInventoryImportJob::dispatch(
                $batch,
                Storage::disk(self::IMPORT_DISK)->path($excelPath),
                Storage::disk(self::IMPORT_DISK)->path($pptPath)
            )->onQueue(config('import.batch.queue', 'default'));

            \Log::info('Dispatched import processing job', [
                'batch_id' => $batch->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Import started successfully',
                'batch_id' => $batch->id,
                'data' => [
                    'batch_id' => $batch->id,
                    'status' => $batch->status,
                    'media_type' => $batch->media_type,
                    'created_at' => $batch->created_at,
                ],
            ], 201);
        } catch (Exception $e) {
            \Log::error('Failed to upload import', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process import upload',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get batch status
     *
     * @param InventoryImportBatch $batch
     * @return JsonResponse
     */
    public function getImportStatus(InventoryImportBatch $batch): JsonResponse
    {
        try {
            // Authorize user
            $this->authorize('view', $batch);

            return response()->json([
                'success' => true,
                'data' => [
                    'batch_id' => $batch->id,
                    'status' => $batch->status,
                    'media_type' => $batch->media_type,
                    'total_rows' => $batch->total_rows,
                    'valid_rows' => $batch->valid_rows,
                    'invalid_rows' => $batch->invalid_rows,
                    'error_rate' => $batch->getErrorRatePercentage(),
                    'success_rate' => $batch->getSuccessRatePercentage(),
                    'created_at' => $batch->created_at,
                    'updated_at' => $batch->updated_at,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch import status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get batch details with staging records
     *
     * @param InventoryImportBatch $batch
     * @return JsonResponse
     */
    public function getImportDetails(InventoryImportBatch $batch): JsonResponse
    {
        try {
            // Authorize user
            $this->authorize('view', $batch);

            $invalidRecords = $batch->stagingRecords()
                ->invalid()
                ->select(['id', 'code', 'error_message', 'created_at'])
                ->limit(100)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'batch' => [
                        'batch_id' => $batch->id,
                        'status' => $batch->status,
                        'media_type' => $batch->media_type,
                        'total_rows' => $batch->total_rows,
                        'valid_rows' => $batch->valid_rows,
                        'invalid_rows' => $batch->invalid_rows,
                        'error_rate' => $batch->getErrorRatePercentage(),
                        'created_at' => $batch->created_at,
                        'updated_at' => $batch->updated_at,
                    ],
                    'invalid_records' => $invalidRecords,
                    'invalid_records_count' => $batch->stagingRecords()->invalid()->count(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch import details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store uploaded file
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $path
     * @param string $name
     * @return string
     * @throws Exception
     */
    protected function storeFile($file, string $path, string $name): string
    {
        try {
            $filename = $name . '_' . time() . '.' . $file->getClientOriginalExtension();
            $storagePath = $file->storeAs(
                $path,
                $filename,
                self::IMPORT_DISK
            );

            if (!$storagePath) {
                throw new Exception("Failed to store file: {$file->getClientOriginalName()}");
            }

            return $storagePath;
        } catch (Exception $e) {
            \Log::error('File storage failed', [
                'file' => $file->getClientOriginalName(),
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * List user's imports
     *
     * @return JsonResponse
     */
    public function listImports(): JsonResponse
    {
        try {
            $imports = InventoryImportBatch::byVendor(auth()->id())
                ->orderByDesc('created_at')
                ->paginate(15, ['*'], 'page', 1);

            return response()->json([
                'success' => true,
                'data' => $imports->map(fn ($batch) => [
                    'batch_id' => $batch->id,
                    'status' => $batch->status,
                    'media_type' => $batch->media_type,
                    'total_rows' => $batch->total_rows,
                    'valid_rows' => $batch->valid_rows,
                    'invalid_rows' => $batch->invalid_rows,
                    'error_rate' => $batch->getErrorRatePercentage(),
                    'created_at' => $batch->created_at,
                ]),
                'pagination' => [
                    'total' => $imports->total(),
                    'per_page' => $imports->perPage(),
                    'current_page' => $imports->currentPage(),
                    'last_page' => $imports->lastPage(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch imports',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel import
     *
     * @param InventoryImportBatch $batch
     * @return JsonResponse
     */
    public function cancelImport(InventoryImportBatch $batch): JsonResponse
    {
        try {
            $this->authorize('delete', $batch);

            // Only allow cancellation if not completed or failed
            if ($batch->status === 'completed' || $batch->status === 'failed') {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot cancel import with status: {$batch->status}",
                ], 422);
            }

            $batch->update(['status' => 'cancelled']);

            \Log::info('Import cancelled', [
                'batch_id' => $batch->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Import cancelled successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel import',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

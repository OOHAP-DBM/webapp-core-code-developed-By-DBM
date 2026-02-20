<?php

namespace Modules\Import\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\Import\Http\Requests\UploadInventoryImportRequest;
use Modules\Import\Entities\InventoryImportBatch;
use Modules\Import\Entities\InventoryImportStaging;
use Modules\Import\Jobs\ProcessInventoryImportJob;
use Exception;

class ImportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show import dashboard with role-based batch filtering
     *
     * Admins see all batches, vendors see only their own batches
     *
     * @return View
     */
    public function dashboard(): View
    {
        $user = auth()->user();
        
        // Build query based on user role
        $batchesQuery = InventoryImportBatch::query()
            ->latest('created_at');
        
        // Filter by vendor_id if user is not admin
        if (!$user->hasRole('admin')) {
            $batchesQuery->where('vendor_id', $user->id);
        }
        
        $batches = $batchesQuery->paginate(15);

        $viewName = $user->hasRole('admin') ? 'import::admin' : 'import::index';

        return view($viewName, [
            'batches' => $batches,
            'isAdmin' => $user->hasRole('admin'),
        ]);
    }

    /**
     * Show enhanced import dashboard as separate feature page.
     *
     * @return View
     */
    public function enhancedDashboard(): View
    {
        $user = auth()->user();

        $batchesQuery = InventoryImportBatch::query()->latest('created_at');

        if (!$user->hasRole('admin')) {
            $batchesQuery->where('vendor_id', $user->id);
        }

        $batches = $batchesQuery->paginate(15);

        return view('import::dashboard', [
            'batches' => $batches,
            'isAdmin' => $user->hasRole('admin'),
            'layout' => $user->hasRole('admin') ? 'layouts.admin' : 'layouts.vendor',
        ]);
    }

    /**
     * Show enhanced batch details page.
     *
     * @param InventoryImportBatch $batch
     * @return View
     */
    public function enhancedBatchShow(InventoryImportBatch $batch): View
    {
        $this->authorize('view', $batch);

        $user = auth()->user();

        return view('import::batch-details', [
            'batch' => $batch,
            'isAdmin' => $user->hasRole('admin'),
            'layout' => $user->hasRole('admin') ? 'layouts.admin' : 'layouts.vendor',
        ]);
    }

    /**
     * Serve extracted batch image.
     *
     * @param InventoryImportBatch $batch
     * @param string $imageName
     * @return StreamedResponse|JsonResponse
     */
    public function serveBatchImage(InventoryImportBatch $batch, string $imageName): StreamedResponse|JsonResponse
    {
        $this->authorize('view', $batch);

        $safeImageName = basename($imageName);
        if ($safeImageName !== $imageName) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid image name',
            ], 422);
        }

        $imagePath = self::IMPORT_STORAGE_PATH . '/' . $batch->id . '/images/' . $safeImageName;

        if (!Storage::disk(self::IMPORT_DISK)->exists($imagePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found',
            ], 404);
        }

        return Storage::disk(self::IMPORT_DISK)->response($imagePath);
    }

    /**
     * Download sample import template (Excel-compatible CSV).
     *
     * @param string $mediaType
     * @return StreamedResponse
     */
    public function downloadSampleTemplate(string $mediaType): StreamedResponse
    {
        $normalizedMediaType = strtolower(trim($mediaType));
        if (!in_array($normalizedMediaType, ['ooh', 'dooh'], true)) {
            abort(404);
        }

        $oohColumns = [
            'Media ID',
            'Hoarding Type',
            'Media Type',
            'Full Address',
            'Locality',
            'Landmark',
            'City',
            'State',
            'Pincode',
            'Width',
            'Height',
            'Unit',
            'Illumination',
            'Latitude',
            'Longitude',
            'Ad Duration (Sec)',
            'Daily Play Hours',
            'Minimum Duration (Days)',
            'DCPM / Price',
            'Availability',
            'Discount Type',
            'Discount Value',
            'Monthly Sale Price',
            'Designing Charge',
            'Printing Charge',
            'Mounting Charge',
            'Description',
        ];

        $doohColumns = [
            'Media ID',
            'Hoarding Type',
            'Media Type',
            'Full Address',
            'Locality',
            'Landmark',
            'City',
            'State',
            'Pincode',
            'Width',
            'Height',
            'Unit',
            'Illumination',
            'Latitude',
            'Longitude',
            'Ad Duration (Sec)',
            'Price Per Spot (₹)',
            'Spots Per Day',
            'Daily Play Hours',
            'Minimum Duration (Days)',
            'DCPM / Price',
            'Availability',
            'Discount Type',
            'Discount Value',
            'Monthly Sale Price',
            'Designing Charge',
            'Printing Charge',
            'Mounting Charge',
            'Description',
        ];

        $columns = $normalizedMediaType === 'dooh' ? $doohColumns : $oohColumns;

        $sampleRow = [
            'Media ID' => strtoupper($normalizedMediaType) . '001',
            'Hoarding Type' => strtoupper($normalizedMediaType),
            'Media Type' => 'Billboard',
            'Full Address' => 'Connaught Place, New Delhi',
            'Locality' => 'Connaught Place',
            'Landmark' => 'Near Central Park',
            'City' => 'Delhi',
            'State' => 'Delhi',
            'Pincode' => '110001',
            'Width' => '20',
            'Height' => '10',
            'Unit' => 'ft',
            'Illumination' => 'Front Lit',
            'Latitude' => '28.6315',
            'Longitude' => '77.2167',
            'Ad Duration (Sec)' => '10',
            'Price Per Spot (₹)' => '250',
            'Spots Per Day' => '120',
            'Daily Play Hours' => '18 Hrs',
            'Minimum Duration (Days)' => '30',
            'DCPM / Price' => '120000',
            'Availability' => 'Available',
            'Discount Type' => 'fixed',
            'Discount Value' => '5000',
            'Monthly Sale Price' => '45000',
            'Designing Charge' => '1500',
            'Printing Charge' => '3000',
            'Mounting Charge' => '2000',
            'Description' => 'Prime location inventory sample',
        ];

        $filename = 'import_sample_' . $normalizedMediaType . '.csv';

        return response()->streamDownload(function () use ($columns, $sampleRow) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $row = [];
            foreach ($columns as $column) {
                $row[] = $sampleRow[$column] ?? '';
            }

            fputcsv($handle, $row);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
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
    public function listImports(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $validated = $request->validate([
                'status' => ['nullable', 'string', 'max:50'],
                'search' => ['nullable', 'string', 'max:100'],
                'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
                'page' => ['nullable', 'integer', 'min:1'],
            ]);

            $summaryQuery = InventoryImportBatch::query();
            if (!$user->hasRole('admin')) {
                $summaryQuery->byVendor($user->id);
            }

            $summaryRows = $summaryQuery
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->get();

            $summary = [
                'total' => (int) $summaryRows->sum('total'),
                'processing' => 0,
                'completed' => 0,
                'failed' => 0,
            ];

            foreach ($summaryRows as $summaryRow) {
                $statusKey = strtolower((string) $summaryRow->status);
                if (array_key_exists($statusKey, $summary)) {
                    $summary[$statusKey] = (int) $summaryRow->total;
                }
            }
            
            // Admins see all batches, vendors see only their own
            $importsQuery = InventoryImportBatch::query()
                ->select([
                    'id',
                    'vendor_id',
                    'status',
                    'media_type',
                    'total_rows',
                    'valid_rows',
                    'invalid_rows',
                    'created_at',
                ]);
            
            if (!$user->hasRole('admin')) {
                $importsQuery->byVendor($user->id);
            }

            if (!empty($validated['status'])) {
                $importsQuery->where('status', strtolower((string) $validated['status']));
            }

            if (!empty($validated['search'])) {
                $search = trim((string) $validated['search']);
                $importsQuery->where(function ($query) use ($search) {
                    $query->where('media_type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            }

            $perPage = (int) ($validated['per_page'] ?? 15);
            
            $imports = $importsQuery->orderByDesc('created_at')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => collect($imports->items())->map(fn ($batch) => [
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
                    'from' => $imports->firstItem(),
                    'to' => $imports->lastItem(),
                ],
                'summary' => $summary,
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

    /**
     * Show batch details with paginated rows.
     *
     * @param Request $request
     * @param InventoryImportBatch $batch
     * @return JsonResponse
     */
    public function showBatch(Request $request, InventoryImportBatch $batch): JsonResponse
    {
        try {
            $this->authorize('view', $batch);

            $rowsQuery = $batch->stagingRecords()->latest('id');

            if ($request->filled('status')) {
                $rowsQuery->where('status', $request->input('status'));
            }

            if ($request->filled('search')) {
                $search = trim((string) $request->input('search'));
                $rowsQuery->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('image_name', 'like', "%{$search}%");
                });
            }

            $perPage = (int) $request->input('per_page', 15);
            $rows = $rowsQuery->paginate(max(1, min($perPage, 100)));

            return response()->json([
                'success' => true,
                'data' => [
                    'batch' => [
                        'id' => $batch->id,
                        'vendor_id' => $batch->vendor_id,
                        'media_type' => $batch->media_type,
                        'status' => $batch->status,
                        'total_rows' => $batch->total_rows,
                        'valid_rows' => $batch->valid_rows,
                        'invalid_rows' => $batch->invalid_rows,
                        'created_at' => $batch->created_at,
                        'updated_at' => $batch->updated_at,
                    ],
                    'rows' => $rows->items(),
                    'pagination' => [
                        'total' => $rows->total(),
                        'per_page' => $rows->perPage(),
                        'current_page' => $rows->currentPage(),
                        'last_page' => $rows->lastPage(),
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch batch',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an import batch.
     *
     * @param Request $request
     * @param InventoryImportBatch $batch
     * @return JsonResponse
     */
    public function updateBatch(Request $request, InventoryImportBatch $batch): JsonResponse
    {
        try {
            $this->authorize('update', $batch);

            $validated = $request->validate([
                'media_type' => ['required', Rule::in(['ooh', 'dooh', 'OOH', 'DOOH'])],
                'status' => ['nullable', Rule::in(['uploaded', 'processing', 'processed', 'approved', 'completed', 'cancelled', 'failed'])],
            ]);

            $updates = [
                'media_type' => strtolower($validated['media_type']),
            ];

            if (array_key_exists('status', $validated)) {
                $updates['status'] = $validated['status'];
            }

            $batch->update($updates);

            return response()->json([
                'success' => true,
                'message' => 'Batch updated successfully',
                'data' => $batch->fresh(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update batch',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an import batch and all staging rows.
     *
     * @param InventoryImportBatch $batch
     * @return JsonResponse
     */
    public function deleteBatch(InventoryImportBatch $batch): JsonResponse
    {
        try {
            $this->authorize('delete', $batch);

            if ($batch->status === 'processing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete a batch while processing',
                ], 422);
            }

            $batchDirectory = self::IMPORT_STORAGE_PATH . '/' . $batch->id;
            if (Storage::disk(self::IMPORT_DISK)->exists($batchDirectory)) {
                $deleted = Storage::disk(self::IMPORT_DISK)->deleteDirectory($batchDirectory);
                if (!$deleted) {
                    throw new Exception('Failed to delete stored import files');
                }
            }

            $batch->delete();

            return response()->json([
                'success' => true,
                'message' => 'Batch deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete batch',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List rows of a batch.
     *
     * @param Request $request
     * @param InventoryImportBatch $batch
     * @return JsonResponse
     */
    public function listBatchRows(Request $request, InventoryImportBatch $batch): JsonResponse
    {
        return $this->showBatch($request, $batch);
    }

    /**
     * Create a staging row under a batch.
     *
     * @param Request $request
     * @param InventoryImportBatch $batch
     * @return JsonResponse
     */
    public function createBatchRow(Request $request, InventoryImportBatch $batch): JsonResponse
    {
        try {
            $this->authorize('update', $batch);

            if ($batch->status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify rows for an approved batch',
                ], 422);
            }

            $validated = $request->validate([
                'code' => ['required', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:255'],
                'width' => ['nullable', 'numeric', 'min:0'],
                'height' => ['nullable', 'numeric', 'min:0'],
                'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
                'status' => ['required', Rule::in(['valid', 'invalid'])],
                'error_message' => ['nullable', 'string'],
                'extra_attributes' => ['nullable', 'array'],
            ]);

            $uploadedImageName = null;
            if ($request->hasFile('image')) {
                $uploadedImageName = $this->storeBatchImage($batch, $request->file('image'));
            }

            $row = InventoryImportStaging::create([
                'batch_id' => $batch->id,
                'vendor_id' => $batch->vendor_id,
                'media_type' => $batch->media_type,
                'code' => $validated['code'],
                'city' => $validated['city'] ?? null,
                'width' => $validated['width'] ?? null,
                'height' => $validated['height'] ?? null,
                'image_name' => $uploadedImageName,
                'status' => $validated['status'],
                'error_message' => $validated['error_message'] ?? null,
                'extra_attributes' => $validated['extra_attributes'] ?? null,
            ]);

            $this->refreshBatchCounts($batch);

            return response()->json([
                'success' => true,
                'message' => 'Row created successfully',
                'data' => $row,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create row',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a staging row under a batch.
     *
     * @param Request $request
     * @param InventoryImportBatch $batch
     * @param InventoryImportStaging $row
     * @return JsonResponse
     */
    public function updateBatchRow(Request $request, InventoryImportBatch $batch, InventoryImportStaging $row): JsonResponse
    {
        try {
            $this->authorize('update', $batch);

            if ($batch->status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify rows for an approved batch',
                ], 422);
            }

            if ((int) $row->batch_id !== (int) $batch->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Row does not belong to the selected batch',
                ], 422);
            }

            $validated = $request->validate([
                'code' => ['required', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:255'],
                'width' => ['nullable', 'numeric', 'min:0'],
                'height' => ['nullable', 'numeric', 'min:0'],
                'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
                'keep_previous_image' => ['nullable', 'boolean'],
                'status' => ['required', Rule::in(['valid', 'invalid'])],
                'error_message' => ['nullable', 'string'],
                'extra_attributes' => ['nullable', 'array'],
            ]);

            $updatePayload = [
                'code' => $validated['code'],
                'city' => $validated['city'] ?? null,
                'width' => $validated['width'] ?? null,
                'height' => $validated['height'] ?? null,
                'status' => $validated['status'],
                'error_message' => $validated['error_message'] ?? null,
                'extra_attributes' => $validated['extra_attributes'] ?? null,
            ];

            if ($request->hasFile('image')) {
                $keepPreviousImage = filter_var($request->input('keep_previous_image', false), FILTER_VALIDATE_BOOLEAN);

                if (!$keepPreviousImage) {
                    $oldImageName = $row->image_name;
                    $newImageName = $this->storeBatchImage($batch, $request->file('image'));
                    $updatePayload['image_name'] = $newImageName;

                    if (!empty($oldImageName)) {
                        $this->deleteBatchImageIfUnused($batch, $oldImageName, $row->id);
                    }
                }
            }

            $row->update($updatePayload);

            $this->refreshBatchCounts($batch);

            return response()->json([
                'success' => true,
                'message' => 'Row updated successfully',
                'data' => $row->fresh(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update row',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a staging row under a batch.
     *
     * @param InventoryImportBatch $batch
     * @param InventoryImportStaging $row
     * @return JsonResponse
     */
    public function deleteBatchRow(InventoryImportBatch $batch, InventoryImportStaging $row): JsonResponse
    {
        try {
            $this->authorize('update', $batch);

            if ($batch->status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify rows for an approved batch',
                ], 422);
            }

            if ((int) $row->batch_id !== (int) $batch->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Row does not belong to the selected batch',
                ], 422);
            }

            $imageName = $row->image_name;

            $row->delete();

            if (!empty($imageName)) {
                $this->deleteBatchImageIfUnused($batch, $imageName);
            }

            $this->refreshBatchCounts($batch);

            return response()->json([
                'success' => true,
                'message' => 'Row deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete row',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recalculate and refresh batch counters based on staging rows.
     *
     * @param InventoryImportBatch $batch
     * @return void
     */
    protected function refreshBatchCounts(InventoryImportBatch $batch): void
    {
        $totalRows = $batch->stagingRecords()->count();
        $validRows = $batch->stagingRecords()->valid()->count();
        $invalidRows = max(0, $totalRows - $validRows);

        $batch->update([
            'total_rows' => $totalRows,
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
        ]);
    }

    /**
     * Store uploaded row image in batch image folder.
     *
     * @param InventoryImportBatch $batch
     * @param \Illuminate\Http\UploadedFile $image
     * @return string
     */
    protected function storeBatchImage(InventoryImportBatch $batch, $image): string
    {
        $extension = strtolower((string) $image->getClientOriginalExtension());
        $filename = 'manual_' . now()->format('YmdHis') . '_' . substr(md5(uniqid((string) $batch->id, true)), 0, 10) . '.' . $extension;
        $path = self::IMPORT_STORAGE_PATH . '/' . $batch->id . '/images';

        $storedPath = $image->storeAs($path, $filename, self::IMPORT_DISK);
        if (!$storedPath) {
            throw new Exception('Failed to store uploaded image');
        }

        return $filename;
    }

    /**
     * Delete image file only if no staging row references it.
     *
     * @param InventoryImportBatch $batch
     * @param string $imageName
     * @param int|null $excludeRowId
     * @return void
     */
    protected function deleteBatchImageIfUnused(InventoryImportBatch $batch, string $imageName, ?int $excludeRowId = null): void
    {
        $query = $batch->stagingRecords()->where('image_name', $imageName);
        if ($excludeRowId !== null) {
            $query->where('id', '!=', $excludeRowId);
        }

        if ($query->exists()) {
            return;
        }

        $imagePath = self::IMPORT_STORAGE_PATH . '/' . $batch->id . '/images/' . basename($imageName);
        if (Storage::disk(self::IMPORT_DISK)->exists($imagePath)) {
            $deleted = Storage::disk(self::IMPORT_DISK)->delete($imagePath);
            if (!$deleted) {
                throw new Exception('Failed to delete stored row image: ' . basename($imageName));
            }
        }
    }

    /**
     * Admin: list roles and current import permissions.
     *
     * @return JsonResponse
     */
    public function listRoleImportPermissions(): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user || !$user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $permissions = Permission::query()
                ->where('name', 'like', 'import.%')
                ->orderBy('name')
                ->get(['id', 'name']);

            $roles = Role::query()
                ->with(['permissions' => function ($query) {
                    $query->where('name', 'like', 'import.%')->orderBy('name');
                }])
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => [
                    'permissions' => $permissions,
                    'roles' => $roles->map(function (Role $role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'permissions' => $role->permissions->pluck('name')->values(),
                        ];
                    })->values(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch role permissions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: sync import permissions for a role.
     *
     * @param Request $request
     * @param Role $role
     * @return JsonResponse
     */
    public function updateRoleImportPermissions(Request $request, Role $role): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user || !$user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $validated = $request->validate([
                'permissions' => ['required', 'array'],
                'permissions.*' => ['string'],
            ]);

            $incomingPermissions = collect($validated['permissions'])
                ->filter(fn ($permissionName) => is_string($permissionName) && str_starts_with($permissionName, 'import.'))
                ->values();

            $allowedPermissions = Permission::query()
                ->whereIn('name', $incomingPermissions)
                ->pluck('name');

            $role->permissions()
                ->where('name', 'like', 'import.%')
                ->detach();

            if ($allowedPermissions->isNotEmpty()) {
                $role->givePermissionTo($allowedPermissions->all());
            }

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return response()->json([
                'success' => true,
                'message' => 'Role import permissions updated successfully',
                'data' => [
                    'role' => $role->name,
                    'permissions' => $allowedPermissions->values(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role permissions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

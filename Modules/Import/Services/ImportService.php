<?php

namespace Modules\Import\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Import\Entities\Import;
use Modules\Import\Jobs\ProcessImportJob;
use Exception;

class ImportService
{
    /**
     * Create a new import
     *
     * @param UploadedFile $file
     * @param string $importType
     * @param int $userId
     * @return Import
     * @throws Exception
     */
    public function createImport(UploadedFile $file, string $importType, int $userId): Import
    {
        try {
            // Store the file
            $filePath = $this->storeFile($file);

            // Create import record
            $import = Import::create([
                'user_id' => $userId,
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientOriginalExtension(),
                'imported_type' => $importType,
                'status' => 'pending',
                'total_rows' => 0,
                'processed_rows' => 0,
                'failed_rows' => 0,
            ]);

            // Dispatch processing job
            ProcessImportJob::dispatch($import)->onQueue(config('import.batch.queue', 'default'));

            return $import;
        } catch (Exception $e) {
            \Log::error('Failed to create import', [
                'error' => $e->getMessage(),
                'userId' => $userId,
                'importType' => $importType,
            ]);
            throw $e;
        }
    }

    /**
     * Update import
     *
     * @param Import $import
     * @param array $data
     * @return Import
     */
    public function updateImport(Import $import, array $data): Import
    {
        try {
            $import->update($data);
            return $import;
        } catch (Exception $e) {
            \Log::error('Failed to update import', [
                'error' => $e->getMessage(),
                'importId' => $import->id,
            ]);
            throw $e;
        }
    }

    /**
     * Delete import
     *
     * @param Import $import
     * @return bool
     * @throws Exception
     */
    public function deleteImport(Import $import): bool
    {
        try {
            // Delete file from storage
            if ($import->file_path && Storage::disk(config('import.storage.disk'))->exists($import->file_path)) {
                Storage::disk(config('import.storage.disk'))->delete($import->file_path);
            }

            // Delete import record
            return $import->delete();
        } catch (Exception $e) {
            \Log::error('Failed to delete import', [
                'error' => $e->getMessage(),
                'importId' => $import->id,
            ]);
            throw $e;
        }
    }

    /**
     * Store file to disk
     *
     * @param UploadedFile $file
     * @return string
     */
    public function storeFile(UploadedFile $file): string
    {
        $disk = config('import.storage.disk', 'local');
        $path = config('import.storage.path', 'imports');

        return $file->store($path, $disk);
    }

    /**
     * Validate file
     *
     * @param UploadedFile $file
     * @param string $importType
     * @return array
     */
    public function validateFile(UploadedFile $file, string $importType): array
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            $supportedFormats = config('import.supported_formats', []);
            $maxFileSize = config('import.max_file_size', 10 * 1024 * 1024);

            // Validate file extension
            if (!in_array($extension, $supportedFormats)) {
                return [
                    'success' => false,
                    'message' => 'Unsupported file format',
                    'supported_formats' => $supportedFormats,
                ];
            }

            // Validate file size
            if ($file->getSize() > $maxFileSize) {
                return [
                    'success' => false,
                    'message' => 'File size exceeds maximum allowed',
                    'max_size' => $maxFileSize,
                ];
            }

            return [
                'success' => true,
                'message' => 'File is valid',
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_type' => $extension,
            ];
        } catch (Exception $e) {
            \Log::error('File validation failed', [
                'error' => $e->getMessage(),
                'importType' => $importType,
            ]);
            return [
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get template for import type
     *
     * @param string $type
     * @return array
     * @throws Exception
     */
    public function getTemplate(string $type): array
    {
        $templates = [
            'csv' => [
                'format' => 'csv',
                'headers' => ['id', 'name', 'email', 'description'],
                'sample_row' => [1, 'Sample', 'sample@example.com', 'Sample description'],
            ],
            'xlsx' => [
                'format' => 'xlsx',
                'headers' => ['id', 'name', 'email', 'description'],
                'sample_row' => [1, 'Sample', 'sample@example.com', 'Sample description'],
            ],
        ];

        if (!isset($templates[$type])) {
            throw new Exception("Template not found for type: {$type}");
        }

        return $templates[$type];
    }

    /**
     * Get import progress
     *
     * @param Import $import
     * @return array
     */
    public function getProgress(Import $import): array
    {
        return [
            'status' => $import->status,
            'total_rows' => $import->total_rows,
            'processed_rows' => $import->processed_rows,
            'failed_rows' => $import->failed_rows,
            'progress_percentage' => $import->total_rows > 0
                ? round(($import->processed_rows / $import->total_rows) * 100, 2)
                : 0,
            'started_at' => $import->started_at,
            'completed_at' => $import->completed_at,
        ];
    }
}

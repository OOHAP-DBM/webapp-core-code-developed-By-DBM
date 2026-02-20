<?php

namespace Modules\Import\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Modules\Import\Entities\Import;
use Exception;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Import
     */
    protected Import $import;

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
    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @param Import $import
     */
    public function __construct(Import $import)
    {
        $this->import = $import;
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
            $this->import->markAsProcessing();

            \Log::info('Starting import processing', [
                'import_id' => $this->import->id,
                'import_type' => $this->import->imported_type,
            ]);

            // Get file content
            $disk = config('import.storage.disk', 'local');
            $content = Storage::disk($disk)->get($this->import->file_path);

            // Process based on file type
            $rows = $this->parseFile($this->import->file_type, $content);

            // Store total rows
            $this->import->update(['total_rows' => count($rows)]);

            // Process rows in batches
            $batchSize = config('import.batch.size', 500);
            $processedRows = 0;
            $failedRows = 0;

            foreach (array_chunk($rows, $batchSize) as $batch) {
                foreach ($batch as $row) {
                    try {
                        // Process individual row (implement your logic here)
                        $this->processRow($row);
                        $processedRows++;
                    } catch (Exception $e) {
                        $failedRows++;
                        \Log::warning('Failed to process import row', [
                            'import_id' => $this->import->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Update progress
                $this->import->updateProgress($processedRows, $failedRows);
            }

            // Mark as completed
            $this->import->markAsCompleted();

            \Log::info('Import processing completed', [
                'import_id' => $this->import->id,
                'processed_rows' => $processedRows,
                'failed_rows' => $failedRows,
            ]);
        } catch (Exception $e) {
            $this->import->markAsFailed($e->getMessage());

            \Log::error('Import processing failed', [
                'import_id' => $this->import->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Parse file based on type
     *
     * @param string $fileType
     * @param string $content
     * @return array
     */
    protected function parseFile(string $fileType, string $content): array
    {
        $fileType = strtolower($fileType);

        return match ($fileType) {
            'csv' => $this->parseCSV($content),
            'json' => json_decode($content, true) ?? [],
            default => [],
        };
    }

    /**
     * Parse CSV content
     *
     * @param string $content
     * @return array
     */
    protected function parseCSV(string $content): array
    {
        $rows = [];
        $lines = explode("\n", $content);
        $headers = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if ($headers === null) {
                $headers = str_getcsv($line);
                continue;
            }

            $data = str_getcsv($line);
            if (count($data) === count($headers)) {
                $rows[] = array_combine($headers, $data);
            }
        }

        return $rows;
    }

    /**
     * Process individual row
     *
     * @param array $row
     * @return void
     */
    protected function processRow(array $row): void
    {
        // Implement your row processing logic here
        // This is a placeholder method
        \Log::info('Processing import row', ['row' => $row]);
    }
}

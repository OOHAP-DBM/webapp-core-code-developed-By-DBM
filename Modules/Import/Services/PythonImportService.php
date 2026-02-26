<?php

namespace Modules\Import\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Modules\Import\Exceptions\ImportApiException;

class PythonImportService
{
    /**
     * Request timeout in seconds
     */
    protected const REQUEST_TIMEOUT = 300;

    /**
     * Process import by sending files to Python API
     *
     * @param string $excelPath Path to the Excel file
     * @param string $pptPath Path to the PowerPoint file
     * @param int $vendorId Vendor ID
     * @param string $mediaType Media type for the import
     * @return array Structured response array
     * @throws ImportApiException
     */
    public function processImport(string $excelPath, string $pptPath, int $vendorId, string $mediaType): array
    {
        try {
            // Validate files exist
            $this->validateFileExists($excelPath);
            $this->validateFileExists($pptPath);

            \Log::info('Python import request prepared', [
                'vendor_id' => $vendorId,
                'media_type' => $mediaType,
                'excel_file' => [
                    'path' => $excelPath,
                    'name' => basename($excelPath),
                    'size_bytes' => @filesize($excelPath) ?: null,
                ],
                'ppt_file' => [
                    'path' => $pptPath,
                    'name' => basename($pptPath),
                    'size_bytes' => @filesize($pptPath) ?: null,
                ],
            ]);

            // Get configuration
            $baseUrl = $this->getBaseUrl();
            $token = $this->getAuthToken();

            // Send the multipart request
            $response = $this->sendImportRequest(
                $baseUrl,
                $token,
                $excelPath,
                $pptPath,
                $vendorId,
                $mediaType
            );

            // Process and return structured response
            $processedResponse = $this->processResponse($response);

            \Log::info('Python import response received', [
                'vendor_id' => $vendorId,
                'media_type' => $mediaType,
                'status' => $processedResponse['status'] ?? null,
                'success' => $processedResponse['success'] ?? null,
                'total_rows' => $processedResponse['total_rows'] ?? null,
                'valid_rows' => $processedResponse['valid_rows'] ?? null,
                'invalid_rows' => $processedResponse['invalid_rows'] ?? null,
                'images_zip_filename' => $processedResponse['images_zip_filename'] ?? null,
                'received_data' => $processedResponse['data'] ?? [],
                'full_response' => $processedResponse,
            ]);

            // New: Download ZIP if images_zip_filename is present
            if (!empty($processedResponse['images_zip_filename'] ?? null)) {
                $filename = $processedResponse['images_zip_filename'];
                $token = $this->getAuthToken();
                $baseUrl = $this->getBaseUrl();
                $zipResponse = \Illuminate\Support\Facades\Http::withToken($token)
                    ->timeout(self::REQUEST_TIMEOUT)
                    ->get(rtrim($baseUrl, '/') . "/download-images/{$vendorId}");
                if ($zipResponse->successful()) {
                    \Illuminate\Support\Facades\Storage::put('images/' . $filename, $zipResponse->body());
                } else {
                    \Log::error('Failed to download images ZIP from Python API', [
                        'vendor_id' => $vendorId,
                        'filename' => $filename,
                        'status' => $zipResponse->status(),
                        'body' => $zipResponse->body(),
                    ]);
                }
            }

            return $processedResponse;
        } catch (ImportApiException $e) {
            \Log::error('Python Import API Exception', [
                'error' => $e->getMessage(),
                'api_code' => $e->getApiCode(),
                'vendor_id' => $vendorId,
                'media_type' => $mediaType,
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Unexpected error in processImport', [
                'error' => $e->getMessage(),
                'vendor_id' => $vendorId,
                'media_type' => $mediaType,
            ]);
            throw ImportApiException::connectionFailed($e);
        }
    }

    /**
     * Send multipart request to Python API
     *
     * @param string $baseUrl
     * @param string $token
     * @param string $excelPath
     * @param string $pptPath
     * @param int $vendorId
     * @param string $mediaType
     * @return Response
     * @throws ImportApiException
     */
    protected function sendImportRequest(
        string $baseUrl,
        string $token,
        string $excelPath,
        string $pptPath,
        int $vendorId,
        string $mediaType
    ): Response {
        try {
            $configuredPath = (string) config('import.python_process_endpoint', '/process-import');
            $candidatePaths = array_values(array_unique([
                $configuredPath,
                '/process-import',
                '/import/process',
            ]));

            $http = Http::timeout((int) config('import.python_timeout', self::REQUEST_TIMEOUT));

            if (!empty($token)) {
                $http = $http->withToken($token);
            }

            $response = null;
            foreach ($candidatePaths as $path) {
                $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');

                $response = $http
                    ->attach('excel', fopen($excelPath, 'r'), basename($excelPath))
                    ->attach('ppt', fopen($pptPath, 'r'), basename($pptPath))
                    ->post($url, [
                        'vendor_id' => $vendorId,
                        'media_type' => $mediaType,
                    ]);

                if ($response->status() !== 404) {
                    break;
                }
            }

            \Log::info('Python import endpoint attempt result', [
                'base_url' => $baseUrl,
                'configured_endpoint' => $configuredPath,
                'candidate_endpoints' => $candidatePaths,
                'final_status' => $response?->status(),
                'final_effective_url' => $url ?? null,
                'token_present' => !empty($token),
                'response_body_preview' => mb_substr((string) ($response?->body() ?? ''), 0, 1000),
            ]);

            if ($response === null || $response->failed()) {
                throw ImportApiException::apiError(
                    $response?->body() ?? 'Python API request failed',
                    'API_ERROR_' . ($response?->status() ?? 500),
                    $response?->json()
                );
            }

            return $response;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            if (str_contains($e->getMessage(), 'timed out')) {
                throw ImportApiException::timeout($url ?? 'unknown');
            }
            throw ImportApiException::connectionFailed($e);
        } catch (\Exception $e) {
            if ($e instanceof ImportApiException) {
                throw $e;
            }
            throw ImportApiException::connectionFailed($e);
        }
    }

    /**
     * Process the API response
     *
     * @param Response $response
     * @return array
     * @throws ImportApiException
     */
    protected function processResponse(Response $response): array
    {
        try {
            $data = $response->json();

            if (!is_array($data)) {
                throw ImportApiException::invalidResponse(
                    'Response is not valid JSON',
                    $response->json()
                );
            }

            // Validate response structure
            if (!isset($data['status'])) {
                throw ImportApiException::invalidResponse(
                    'Missing "status" in response',
                    $data
                );
            }

            // Return structured response
            $rows = [];
            if (isset($data['rows']) && is_array($data['rows'])) {
                $rows = $data['rows'];
            } elseif (isset($data['data']['rows']) && is_array($data['data']['rows'])) {
                $rows = $data['data']['rows'];
            } elseif (isset($data['data']) && is_array($data['data']) && array_is_list($data['data'])) {
                $rows = $data['data'];
            }

            $normalizedRows = array_map(function ($row) {
                $row = is_array($row) ? $row : [];
                $errors = $row['errors'] ?? [];

                if (is_string($errors)) {
                    $errors = [$errors];
                }

                if (!is_array($errors)) {
                    $errors = [];
                }

                if (!isset($row['error_message']) && !empty($errors)) {
                    $row['error_message'] = implode('; ', array_filter($errors));
                }

                if (!isset($row['status']) || $row['status'] === null || $row['status'] === '') {
                    $row['status'] = empty($errors) ? 'valid' : 'invalid';
                }

                return $row;
            }, $rows);

            return [
                'success' => in_array(strtolower((string) $data['status']), ['success', 'ok', 'completed'], true),
                'status' => $data['status'],
                'message' => $data['message'] ?? '',
                'data' => $normalizedRows,
                'batch_id' => $data['batch_id'] ?? null,
                'total_rows' => (int) ($data['total_rows'] ?? count($normalizedRows)),
                'processed_rows' => (int) ($data['processed_rows'] ?? count($normalizedRows)),
                'valid_rows' => (int) ($data['valid_rows'] ?? count(array_filter($normalizedRows, fn ($row) => ($row['status'] ?? '') === 'valid'))),
                'invalid_rows' => (int) ($data['invalid_rows'] ?? count(array_filter($normalizedRows, fn ($row) => ($row['status'] ?? '') !== 'valid'))),
                'errors' => $data['errors'] ?? [],
                'images_zip_filename' => $data['images_zip_filename'] ?? null,
            ];
        } catch (\Illuminate\Http\Client\DecodeException $e) {
            throw ImportApiException::invalidResponse(
                'Failed to decode JSON response: ' . $e->getMessage(),
                null
            );
        } catch (ImportApiException $e) {
            throw $e;
        }
    }

    /**
     * Validate file exists
     *
     * @param string $filePath
     * @throws ImportApiException
     */
    protected function validateFileExists(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw ImportApiException::fileNotFound($filePath);
        }

        if (!is_readable($filePath)) {
            throw ImportApiException::fileNotFound("File is not readable: {$filePath}");
        }
    }

    /**
     * Get base URL from configuration
     *
     * @return string
     * @throws ImportApiException
     */
    protected function getBaseUrl(): string
    {
        $baseUrl = config('import.python_url', env('PYTHON_IMPORT_URL', 'http://127.0.0.1:9000'));

        if (empty($baseUrl)) {
            throw ImportApiException::missingConfig('import.python_url');
        }

        return $baseUrl;
    }

    /**
     * Get authentication token from configuration
     *
     * @return string
     * @throws ImportApiException
     */
    protected function getAuthToken(): string
    {
        return (string) config('import.python_token', env('PYTHON_IMPORT_TOKEN', ''));
    }

    /**
     * Get detailed error information
     *
     * @param ImportApiException $exception
     * @return array
     */
    public function getErrorDetails(ImportApiException $exception): array
    {
        return [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'api_code' => $exception->getApiCode(),
            'response_data' => $exception->getResponseData(),
        ];
    }
}

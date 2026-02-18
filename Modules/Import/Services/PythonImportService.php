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
            return $this->processResponse($response);
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
            $url = rtrim($baseUrl, '/') . '/import/process';

            $response = Http::timeout(self::REQUEST_TIMEOUT)
                ->withToken($token)
                ->attach('excel', fopen($excelPath, 'r'), basename($excelPath))
                ->attach('ppt', fopen($pptPath, 'r'), basename($pptPath))
                ->post($url, [
                    'vendor_id' => $vendorId,
                    'media_type' => $mediaType,
                ]);

            if ($response->failed()) {
                throw ImportApiException::apiError(
                    $response->body(),
                    'API_ERROR_' . $response->status(),
                    $response->json()
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
            return [
                'success' => $data['status'] === 'success',
                'status' => $data['status'],
                'message' => $data['message'] ?? '',
                'data' => $data['data'] ?? [],
                'batch_id' => $data['batch_id'] ?? null,
                'total_rows' => $data['total_rows'] ?? 0,
                'processed_rows' => $data['processed_rows'] ?? 0,
                'errors' => $data['errors'] ?? [],
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
        $baseUrl = config('import.python_url');

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
        $token = config('import.python_token');

        if (empty($token)) {
            throw ImportApiException::missingConfig('import.python_token');
        }

        return $token;
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

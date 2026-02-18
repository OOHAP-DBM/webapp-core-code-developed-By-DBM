<?php

namespace Modules\Import\Exceptions;

use Exception;

class ImportApiException extends Exception
{
    /**
     * The error code from the API
     *
     * @var string|null
     */
    protected ?string $apiCode = null;

    /**
     * The response data from the API
     *
     * @var array|null
     */
    protected ?array $responseData = null;

    /**
     * Create a new ImportApiException instance.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     * @param string|null $apiCode
     * @param array|null $responseData
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        Exception $previous = null,
        ?string $apiCode = null,
        ?array $responseData = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->apiCode = $apiCode;
        $this->responseData = $responseData;
    }

    /**
     * Get the API error code
     */
    public function getApiCode(): ?string
    {
        return $this->apiCode;
    }

    /**
     * Get the response data
     */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    /**
     * Create exception for connection failure
     */
    public static function connectionFailed(Exception $exception): self
    {
        return new self(
            'Failed to connect to Python Import API: ' . $exception->getMessage(),
            0,
            $exception,
            'CONNECTION_ERROR'
        );
    }

    /**
     * Create exception for timeout
     */
    public static function timeout(string $url): self
    {
        return new self(
            "Request to Python Import API timed out: {$url}",
            0,
            null,
            'TIMEOUT_ERROR'
        );
    }

    /**
     * Create exception for invalid response
     */
    public static function invalidResponse(string $reason, ?array $response = null): self
    {
        return new self(
            "Invalid response from Python Import API: {$reason}",
            0,
            null,
            'INVALID_RESPONSE',
            $response
        );
    }

    /**
     * Create exception for API error response
     */
    public static function apiError(string $message, ?string $code = null, ?array $data = null): self
    {
        return new self(
            "Python Import API error: {$message}",
            0,
            null,
            $code ?? 'API_ERROR',
            $data
        );
    }

    /**
     * Create exception for file not found
     */
    public static function fileNotFound(string $filePath): self
    {
        return new self(
            "File not found: {$filePath}",
            0,
            null,
            'FILE_NOT_FOUND'
        );
    }

    /**
     * Create exception for missing configuration
     */
    public static function missingConfig(string $configKey): self
    {
        return new self(
            "Missing required configuration: {$configKey}",
            0,
            null,
            'MISSING_CONFIG'
        );
    }
}

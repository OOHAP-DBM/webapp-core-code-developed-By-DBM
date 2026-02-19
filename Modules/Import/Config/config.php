<?php

return [
    'name' => 'Import',

    /**
     * Import Module Configuration
     */

    /**
     * Enable or disable the import module
     */
    'enabled' => env('IMPORT_MODULE_ENABLED', true),

    /**
     * Maximum file size for imports (in bytes)
     * Default: 10MB
     */
    'max_file_size' => env('IMPORT_MAX_FILE_SIZE', 10 * 1024 * 1024),

    /**
     * Supported file formats for imports
     */
    'supported_formats' => [
        'csv',
        'xlsx',
        'xls',
        'json',
    ],

    /**
     * Batch processing configuration
     */
    'batch' => [
        'size' => env('IMPORT_BATCH_SIZE', 500),
        'queue' => env('IMPORT_QUEUE', 'default'),
    ],

    /**
     * Storage configuration for import files
     */
    'storage' => [
        'disk' => env('IMPORT_DISK', 'local'),
        'path' => env('IMPORT_PATH', 'imports'),
    ],

    /**
     * Temporary storage for processing
     */
    'temp' => [
        'path' => env('IMPORT_TEMP_PATH', 'import-temp'),
    ],

    /**
     * Enable detailed logging for imports
     */
    'logging' => [
        'enabled' => env('IMPORT_LOGGING', true),
        'channel' => env('IMPORT_LOG_CHANNEL', 'single'),
    ],

    /**
     * Python API configuration for inventory imports
     */
    'python_url' => env('IMPORT_PYTHON_URL', env('PYTHON_IMPORT_URL', 'http://127.0.0.1:9000')),

    'python_process_endpoint' => env('IMPORT_PYTHON_PROCESS_ENDPOINT', env('PYTHON_IMPORT_PROCESS_ENDPOINT', '/process-import')),

    'python_token' => env('IMPORT_PYTHON_TOKEN', env('PYTHON_IMPORT_TOKEN', '')),

    /**
     * Python API request timeout (in seconds)
     */
    'python_timeout' => env('IMPORT_PYTHON_TIMEOUT', env('PYTHON_IMPORT_TIMEOUT', 30)),
];


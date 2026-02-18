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
    'python_url' => env('PYTHON_IMPORT_URL'),

    'python_token' => env('PYTHON_IMPORT_TOKEN'),

    /**
     * Python API request timeout (in seconds)
     */
    'python_timeout' => env('IMPORT_PYTHON_TIMEOUT', 300),
];

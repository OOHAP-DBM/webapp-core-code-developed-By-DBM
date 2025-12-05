<?php

return [
    /*
    |--------------------------------------------------------------------------
    | POD (Proof of Delivery) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the Proof of Delivery system including
    | geo-validation, file upload limits, and approval workflows.
    |
    */

    /**
     * Maximum distance in meters from hoarding location
     * POD uploads beyond this distance will be rejected
     */
    'max_distance_meters' => env('POD_MAX_DISTANCE_METERS', 100),

    /**
     * Allowed file types for POD uploads
     */
    'allowed_image_types' => ['jpg', 'jpeg', 'png'],
    'allowed_video_types' => ['mp4', 'mov'],

    /**
     * Maximum file size in kilobytes
     */
    'max_file_size_kb' => env('POD_MAX_FILE_SIZE_KB', 51200), // 50MB

    /**
     * Automatically approve POD after verification
     * If false, requires manual approval
     */
    'auto_approve' => env('POD_AUTO_APPROVE', false),

    /**
     * Require GPS accuracy threshold (in meters)
     * Uploads with GPS accuracy worse than this may be rejected
     */
    'required_gps_accuracy_meters' => env('POD_GPS_ACCURACY_METERS', 50),

    /**
     * Enable strict geo-validation
     * If true, POD must be within max_distance_meters
     * If false, distance is recorded but not enforced
     */
    'strict_geo_validation' => env('POD_STRICT_GEO_VALIDATION', true),
];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for different API endpoints and user roles.
    | Limits are defined as [requests, per_minutes].
    |
    */

    'default' => [
        'requests' => env('RATE_LIMIT_DEFAULT', 60),
        'per_minutes' => 1,
    ],

    'auth' => [
        'login' => [
            'requests' => env('RATE_LIMIT_LOGIN', 5),
            'per_minutes' => 10,
        ],
        'register' => [
            'requests_per_hour' => env('RATE_LIMIT_REGISTER_HOUR', 100),
            'requests_per_day' => env('RATE_LIMIT_REGISTER_DAY', 100),
        ],
    ],

    'otp' => [
        'per_identifier' => [
            'requests' => env('RATE_LIMIT_OTP_IDENTIFIER', 30),
            'per_minutes' => 50,
        ],
        'per_ip' => [
            'requests' => env('RATE_LIMIT_OTP_IP', 10),
            'per_minutes' => 50,
        ],
    ],

    'uploads' => [
        'admin' => env('RATE_LIMIT_UPLOAD_ADMIN', 100),
        'staff' => env('RATE_LIMIT_UPLOAD_STAFF', 100),
        'vendor' => env('RATE_LIMIT_UPLOAD_VENDOR', 100),
        'customer' => env('RATE_LIMIT_UPLOAD_CUSTOMER', 100),
        'default' => env('RATE_LIMIT_UPLOAD_DEFAULT', 50),
    ],

    'search' => [
        'admin' => env('RATE_LIMIT_SEARCH_ADMIN', 100),
        'staff' => env('RATE_LIMIT_SEARCH_STAFF', 100),
        'vendor' => env('RATE_LIMIT_SEARCH_VENDOR', 50),
        'customer' => env('RATE_LIMIT_SEARCH_CUSTOMER', 30),
        'guest' => env('RATE_LIMIT_SEARCH_GUEST', 10),
        'default' => env('RATE_LIMIT_SEARCH_DEFAULT', 20),
    ],

    'authenticated' => [
        'admin' => env('RATE_LIMIT_AUTH_ADMIN', 300),
        'staff' => env('RATE_LIMIT_AUTH_STAFF', 300),
        'vendor' => env('RATE_LIMIT_AUTH_VENDOR', 120),
        'customer' => env('RATE_LIMIT_AUTH_CUSTOMER', 60),
        'default' => env('RATE_LIMIT_AUTH_DEFAULT', 30),
        'guest' => env('RATE_LIMIT_AUTH_GUEST', 30),
    ],

    'critical' => [
        'requests' => env('RATE_LIMIT_CRITICAL', 10),
        'per_minutes' => 1,
    ],

    'webhooks' => [
        'requests' => env('RATE_LIMIT_WEBHOOKS', 100),
        'per_minutes' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist
    |--------------------------------------------------------------------------
    |
    | IPs in this list will not be rate limited. Useful for trusted services,
    | monitoring tools, and internal systems.
    |
    */

    'whitelist' => [
        // '127.0.0.1',
        // '::1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Error Messages
    |--------------------------------------------------------------------------
    |
    | Customize the error messages returned when rate limits are exceeded.
    |
    */

    'messages' => [
        'default' => 'Too many requests. Please slow down.',
        'login' => 'Too many login attempts. Please try again later.',
        'otp' => 'Too many OTP requests. Please wait before requesting again.',
        'register' => 'Too many registration attempts. Please try again later.',
        'critical' => 'Too many requests. Please slow down.',
    ],

];

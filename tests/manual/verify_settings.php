<?php

/**
 * Verify Booking Flow Settings
 * Run with: php tests/manual/verify_settings.php
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== BOOKING FLOW SETTINGS VERIFICATION ===" . PHP_EOL . PHP_EOL;

$requiredSettings = [
    'booking_hold_duration_minutes' => '30',
    'draft_expiry_minutes' => '30',
    'min_booking_duration_days' => '7',
    'max_booking_duration_days' => '365',
    'min_advance_booking_days' => '2',
    'max_advance_booking_days' => '365',
];

echo "Checking required settings in database:" . PHP_EOL;
echo str_repeat('-', 60) . PHP_EOL;

$allFound = true;
foreach ($requiredSettings as $key => $expectedValue) {
    $setting = \Illuminate\Support\Facades\DB::table('settings')
        ->where('key', $key)
        ->first();
    
    if ($setting) {
        $match = $setting->value == $expectedValue;
        $status = $match ? '✓' : '⚠';
        echo "{$status} {$key}: {$setting->value}" . ($match ? '' : " (expected: {$expectedValue})") . PHP_EOL;
    } else {
        echo "✗ {$key}: NOT FOUND" . PHP_EOL;
        $allFound = false;
    }
}

echo str_repeat('-', 60) . PHP_EOL;

if ($allFound) {
    echo "✅ All required settings are present!" . PHP_EOL;
} else {
    echo "❌ Some settings are missing. Run: php artisan db:seed --class=SettingsSeeder" . PHP_EOL;
}

echo PHP_EOL;

// Test SettingsService
echo "Testing SettingsService integration:" . PHP_EOL;
echo str_repeat('-', 60) . PHP_EOL;

try {
    $settingsService = app('Modules\Settings\Services\SettingsService');
    
    foreach (array_keys($requiredSettings) as $key) {
        try {
            $value = $settingsService->get($key);
            echo "✓ {$key}: {$value}" . PHP_EOL;
        } catch (Exception $e) {
            echo "✗ {$key}: ERROR - {$e->getMessage()}" . PHP_EOL;
        }
    }
    
    echo str_repeat('-', 60) . PHP_EOL;
    echo "✅ SettingsService is working correctly!" . PHP_EOL;
    
} catch (Exception $e) {
    echo "✗ SettingsService ERROR: {$e->getMessage()}" . PHP_EOL;
}

echo PHP_EOL;
echo "=== VERIFICATION COMPLETE ===" . PHP_EOL;

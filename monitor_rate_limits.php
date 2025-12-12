#!/usr/bin/env php
<?php

/**
 * Rate Limit Monitor Script
 * 
 * Usage: php monitor_rate_limits.php
 * 
 * Displays current rate limit violations and statistics
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║          OOHAPP API Rate Limit Monitor                      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Read log file
$logFile = storage_path('logs/laravel.log');

if (!file_exists($logFile)) {
    echo "❌ No log file found at: {$logFile}\n";
    exit(1);
}

// Get last 1000 lines
$lines = file($logFile);
$recentLines = array_slice($lines, -1000);

// Parse rate limit violations
$violations = [];
$violationPattern = '/API Rate Limit Exceeded/';

foreach ($recentLines as $line) {
    if (preg_match($violationPattern, $line)) {
        // Extract JSON context
        if (preg_match('/\{.*\}/', $line, $matches)) {
            $data = json_decode($matches[0], true);
            if ($data) {
                $violations[] = $data;
            }
        }
    }
}

if (empty($violations)) {
    echo "✅ No rate limit violations found in recent logs\n\n";
    exit(0);
}

echo "📊 Found " . count($violations) . " rate limit violations in last 1000 log entries\n\n";

// Group by endpoint
$byEndpoint = [];
foreach ($violations as $v) {
    $path = $v['path'] ?? 'unknown';
    if (!isset($byEndpoint[$path])) {
        $byEndpoint[$path] = [];
    }
    $byEndpoint[$path][] = $v;
}

echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ Violations by Endpoint                                      │\n";
echo "├─────────────────────────────────────────────────────────────┤\n";

foreach ($byEndpoint as $path => $pathViolations) {
    $count = count($pathViolations);
    printf("│ %-50s %6d │\n", substr($path, 0, 50), $count);
}

echo "└─────────────────────────────────────────────────────────────┘\n\n";

// Group by IP
$byIP = [];
foreach ($violations as $v) {
    $ip = $v['ip'] ?? 'unknown';
    if (!isset($byIP[$ip])) {
        $byIP[$ip] = 0;
    }
    $byIP[$ip]++;
}

arsort($byIP);
$topIPs = array_slice($byIP, 0, 10, true);

echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ Top 10 IPs with Rate Limit Violations                      │\n";
echo "├─────────────────────────────────────────────────────────────┤\n";

foreach ($topIPs as $ip => $count) {
    printf("│ %-50s %6d │\n", $ip, $count);
}

echo "└─────────────────────────────────────────────────────────────┘\n\n";

// Group by user role
$byRole = [];
foreach ($violations as $v) {
    $role = $v['user_role'] ?? 'guest';
    if (!isset($byRole[$role])) {
        $byRole[$role] = 0;
    }
    $byRole[$role]++;
}

arsort($byRole);

echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ Violations by User Role                                     │\n";
echo "├─────────────────────────────────────────────────────────────┤\n";

foreach ($byRole as $role => $count) {
    printf("│ %-50s %6d │\n", ucfirst($role), $count);
}

echo "└─────────────────────────────────────────────────────────────┘\n\n";

// Recent violations
$recentViolations = array_slice($violations, -5);

echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ 5 Most Recent Violations                                    │\n";
echo "├─────────────────────────────────────────────────────────────┤\n";

foreach ($recentViolations as $v) {
    $time = $v['timestamp'] ?? 'unknown';
    $ip = $v['ip'] ?? 'unknown';
    $path = $v['path'] ?? 'unknown';
    $method = $v['method'] ?? 'unknown';
    
    echo "│ Time: " . str_pad($time, 52) . "│\n";
    echo "│ IP: " . str_pad($ip, 54) . "│\n";
    echo "│ Endpoint: " . str_pad($method . ' ' . $path, 47) . "│\n";
    echo "├─────────────────────────────────────────────────────────────┤\n";
}

echo "└─────────────────────────────────────────────────────────────┘\n\n";

// Recommendations
echo "💡 Recommendations:\n\n";

// Check for suspicious IPs
foreach ($byIP as $ip => $count) {
    if ($count > 50) {
        echo "⚠️  IP {$ip} has {$count} violations - Consider blocking\n";
    }
}

// Check for endpoint abuse
foreach ($byEndpoint as $path => $pathViolations) {
    if (count($pathViolations) > 100) {
        echo "⚠️  Endpoint {$path} has " . count($pathViolations) . " violations - Consider stricter limits\n";
    }
}

// Check for role issues
foreach ($byRole as $role => $count) {
    if ($count > 100 && $role !== 'guest') {
        echo "⚠️  {$role} role has {$count} violations - Consider increasing limits\n";
    }
}

echo "\n✅ Analysis complete\n\n";

exit(0);

#!/usr/bin/env pwsh
# OOHAPP - Package Installation Script
# Run this script to install all required dependencies

Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "OOHAPP - Installing Required Packages" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""

# Composer Packages
Write-Host "[1/2] Installing PHP/Composer packages..." -ForegroundColor Yellow
Write-Host ""

$composerPackages = @(
    "spatie/laravel-permission:^6.0",
    "spatie/laravel-medialibrary:^11.0",
    "stancl/tenancy:^3.8",
    "razorpay/razorpay:^2.9",
    "guzzlehttp/guzzle:^7.8",
    "barryvdh/laravel-dompdf:^2.0",
    "maatwebsite/excel:^3.1"
)

foreach ($package in $composerPackages) {
    Write-Host "  Installing: $package" -ForegroundColor Green
    composer require $package
}

Write-Host ""
Write-Host "[Composer packages installed successfully!]" -ForegroundColor Green
Write-Host ""

# NPM Packages
Write-Host "[2/2] Installing NPM packages..." -ForegroundColor Yellow
Write-Host ""

$npmPackages = @(
    "@tailwindcss/forms",
    "@tailwindcss/typography",
    "alpinejs",
    "axios",
    "chart.js"
)

foreach ($package in $npmPackages) {
    Write-Host "  Installing: $package" -ForegroundColor Green
    npm install $package
}

Write-Host ""
Write-Host "[NPM packages installed successfully!]" -ForegroundColor Green
Write-Host ""

# Post-installation steps
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "Post-Installation Steps" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Run the following commands to complete setup:" -ForegroundColor Yellow
Write-Host ""
Write-Host "  1. php artisan vendor:publish --provider=`"Spatie\Permission\PermissionServiceProvider`"" -ForegroundColor White
Write-Host "  2. php artisan vendor:publish --provider=`"Spatie\MediaLibrary\MediaLibraryServiceProvider`"" -ForegroundColor White
Write-Host "  3. php artisan vendor:publish --tag=tenancy-migrations" -ForegroundColor White
Write-Host "  4. php artisan tenancy:install" -ForegroundColor White
Write-Host "  5. php artisan migrate" -ForegroundColor White
Write-Host "  6. php artisan db:seed" -ForegroundColor White
Write-Host "  7. npm run build" -ForegroundColor White
Write-Host ""
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "Installation Complete!" -ForegroundColor Green
Write-Host "===============================================" -ForegroundColor Cyan

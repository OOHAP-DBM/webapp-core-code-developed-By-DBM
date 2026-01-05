#!/usr/bin/env pwsh
# OOHAPP - Quick Start Script
# This script will help you get the project running quickly

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║          OOHAPP - Quick Start Setup Script            ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Step 1: Check prerequisites
Write-Host "[Step 1/8] Checking prerequisites..." -ForegroundColor Yellow
Write-Host ""

# Check PHP
$phpVersion = php -v 2>$null
if ($?) {
    Write-Host "  ✓ PHP detected" -ForegroundColor Green
} else {
    Write-Host "  ✗ PHP not found! Please install PHP 8.2+" -ForegroundColor Red
    exit 1
}

# Check Composer
$composerVersion = composer --version 2>$null
if ($?) {
    Write-Host "  ✓ Composer detected" -ForegroundColor Green
} else {
    Write-Host "  ✗ Composer not found! Please install Composer" -ForegroundColor Red
    exit 1
}

# Check Node
$nodeVersion = node -v 2>$null
if ($?) {
    Write-Host "  ✓ Node.js detected" -ForegroundColor Green
} else {
    Write-Host "  ✗ Node.js not found! Please install Node.js" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Step 2: Copy .env file
Write-Host "[Step 2/8] Setting up environment file..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "  ✓ .env file created" -ForegroundColor Green
} else {
    Write-Host "  ⚠ .env file already exists, skipping..." -ForegroundColor Yellow
}
Write-Host ""

# Step 3: Generate application key
Write-Host "[Step 3/8] Generating application key..." -ForegroundColor Yellow
php artisan key:generate
Write-Host ""

# Step 4: Install Composer dependencies
Write-Host "[Step 4/8] Installing Composer dependencies..." -ForegroundColor Yellow
Write-Host "  This may take a few minutes..." -ForegroundColor Gray
composer install
Write-Host ""

# Step 5: Install NPM dependencies
Write-Host "[Step 5/8] Installing NPM dependencies..." -ForegroundColor Yellow
Write-Host "  This may take a few minutes..." -ForegroundColor Gray
npm install
Write-Host ""

# Step 6: Install additional packages
Write-Host "[Step 6/8] Installing OOHAPP-specific packages..." -ForegroundColor Yellow
.\install-packages.ps1
Write-Host ""

# Step 7: Setup database
Write-Host "[Step 7/8] Database Setup" -ForegroundColor Yellow
Write-Host "  Please configure your database in .env file:" -ForegroundColor White
Write-Host "    DB_DATABASE=oohapp_db" -ForegroundColor Cyan
Write-Host "    DB_USERNAME=root" -ForegroundColor Cyan
Write-Host "    DB_PASSWORD=your_password" -ForegroundColor Cyan
Write-Host ""
$runMigrations = Read-Host "  Run migrations now? (y/n)"
if ($runMigrations -eq "y") {
    php artisan migrate
    Write-Host "  ✓ Migrations completed" -ForegroundColor Green
} else {
    Write-Host "  ⚠ Remember to run: php artisan migrate" -ForegroundColor Yellow
}
Write-Host ""

# Step 8: Build frontend
Write-Host "[Step 8/8] Building frontend assets..." -ForegroundColor Yellow
npm run build
Write-Host ""

# Success message
Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║                  Setup Complete! 🎉                    ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host ""
Write-Host "  1. Configure Razorpay credentials in .env:" -ForegroundColor White
Write-Host "     RAZORPAY_KEY_ID=your_key" -ForegroundColor Cyan
Write-Host "     RAZORPAY_KEY_SECRET=your_secret" -ForegroundColor Cyan
Write-Host ""
Write-Host "  2. Start the development server:" -ForegroundColor White
Write-Host "     php artisan serve" -ForegroundColor Cyan
Write-Host ""
Write-Host "  3. Visit: http://localhost:8000" -ForegroundColor White
Write-Host ""
Write-Host "  4. Read PROJECT_SCAFFOLD.md for detailed documentation" -ForegroundColor White
Write-Host ""
Write-Host "  5. Proceed with module-specific prompts (see PROMPT_1_OUTPUT.md)" -ForegroundColor White
Write-Host ""
Write-Host "Happy coding! 🚀" -ForegroundColor Green
Write-Host ""

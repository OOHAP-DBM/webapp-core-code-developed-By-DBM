# OOHAPP - Package Installation Script (Bash)
# Run: chmod +x install-packages.sh && ./install-packages.sh

echo "==============================================="
echo "OOHAPP - Installing Required Packages"
echo "==============================================="
echo ""

# Composer Packages
echo "[1/2] Installing PHP/Composer packages..."
echo ""

composer require spatie/laravel-permission:^6.0 \
                 spatie/laravel-medialibrary:^11.0 \
                 stancl/tenancy:^3.8 \
                 razorpay/razorpay:^2.9 \
                 guzzlehttp/guzzle:^7.8 \
                 barryvdh/laravel-dompdf:^2.0 \
                 maatwebsite/excel:^3.1

echo ""
echo "[Composer packages installed successfully!]"
echo ""

# NPM Packages
echo "[2/2] Installing NPM packages..."
echo ""

npm install @tailwindcss/forms @tailwindcss/typography alpinejs axios chart.js

echo ""
echo "[NPM packages installed successfully!]"
echo ""

# Post-installation steps
echo "==============================================="
echo "Post-Installation Steps"
echo "==============================================="
echo ""
echo "Run the following commands to complete setup:"
echo ""
echo "  1. php artisan vendor:publish --provider=\"Spatie\\Permission\\PermissionServiceProvider\""
echo "  2. php artisan vendor:publish --provider=\"Spatie\\MediaLibrary\\MediaLibraryServiceProvider\""
echo "  3. php artisan vendor:publish --tag=tenancy-migrations"
echo "  4. php artisan tenancy:install"
echo "  5. php artisan migrate"
echo "  6. php artisan db:seed"
echo "  7. npm run build"
echo ""
echo "==============================================="
echo "Installation Complete!"
echo "==============================================="

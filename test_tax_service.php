<?php

/**
 * Test script for Tax Rules Engine
 * Run with: php test_tax_service.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TaxRule;
use App\Models\Booking;
use App\Services\TaxService;

echo "=== Tax Rules Engine Test ===\n\n";

// Test 1: Check active rules
echo "1. Active Tax Rules:\n";
$activeRules = TaxRule::active()->get(['code', 'name', 'rate', 'tax_type']);
foreach ($activeRules as $rule) {
    echo "   - {$rule->code}: {$rule->name} ({$rule->rate}% - {$rule->tax_type})\n";
}
echo "   Total: " . $activeRules->count() . " active rules\n\n";

// Test 2: GST Calculation
echo "2. GST Calculation Test:\n";
$taxService = app(TaxService::class);
$gstResult = $taxService->calculateGST(10000.00, [
    'applies_to' => 'booking',
]);
echo "   Base Amount: ₹10,000.00\n";
echo "   GST Rate: {$gstResult['gst_rate']}%\n";
echo "   GST Amount: ₹{$gstResult['gst_amount']}\n";
echo "   Reverse Charge: " . ($gstResult['is_reverse_charge'] ? 'Yes' : 'No') . "\n\n";

// Test 3: TDS Calculation
echo "3. TDS Calculation Test (Above Threshold):\n";
$tdsResult = $taxService->calculateTDS(50000.00, [
    'applies_to' => 'payout',
    'vendor_type' => 'professional',
]);
echo "   Base Amount: ₹50,000.00\n";
echo "   TDS Applies: " . ($tdsResult['applies'] ? 'Yes' : 'No') . "\n";
if ($tdsResult['applies']) {
    echo "   TDS Section: {$tdsResult['tds_section']}\n";
    echo "   TDS Rate: {$tdsResult['tds_rate']}%\n";
    echo "   TDS Amount: ₹{$tdsResult['tds_amount']}\n";
}
echo "\n";

// Test 4: TDS Below Threshold
echo "4. TDS Calculation Test (Below Threshold):\n";
$tdsResult2 = $taxService->calculateTDS(20000.00, [
    'applies_to' => 'payout',
    'vendor_type' => 'professional',
]);
echo "   Base Amount: ₹20,000.00\n";
echo "   TDS Applies: " . ($tdsResult2['applies'] ? 'Yes' : 'No') . "\n";
if (!$tdsResult2['applies'] && isset($tdsResult2['reason'])) {
    echo "   Reason: {$tdsResult2['reason']}\n";
}
echo "\n";

// Test 5: Reverse Charge Check
echo "5. Reverse Charge Check (B2B):\n";
$isRC = $taxService->checkReverseCharge([
    'customer_type' => 'business',
    'vendor_type' => 'business',
    'has_gstin' => true,
]);
echo "   Customer Type: Business\n";
echo "   Has GSTIN: Yes\n";
echo "   Reverse Charge Applies: " . ($isRC ? 'Yes' : 'No') . "\n\n";

// Test 6: Get Default Tax Rate
echo "6. Default Tax Rates:\n";
echo "   Booking: " . $taxService->getDefaultTaxRate('booking') . "%\n";
echo "   Commission: " . $taxService->getDefaultTaxRate('commission') . "%\n";
echo "   Payout: " . $taxService->getDefaultTaxRate('payout') . "%\n\n";

echo "=== All Tests Completed ===\n";

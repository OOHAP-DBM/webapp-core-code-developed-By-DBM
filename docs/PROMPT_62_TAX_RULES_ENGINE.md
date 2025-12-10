# Dynamic Tax Rules Engine - Implementation Guide

## Overview

The Dynamic Tax Rules Engine allows you to configure and manage tax calculations (GST, TDS, reverse charge, etc.) through an admin panel without requiring code changes. All tax rules are stored in the database and can be activated, scheduled, and prioritized dynamically.

## Components

### 1. Database Tables

#### `tax_rules` Table
Stores all tax rule configurations:
- **Tax Configuration**: name, code, tax_type, rate, calculation_method
- **Applicability**: applies_to (booking/commission/payout/all), conditions (JSON)
- **Reverse Charge**: is_reverse_charge, reverse_charge_conditions
- **TDS**: is_tds, tds_threshold, tds_section
- **Geography**: country_code, applicable_states
- **Scheduling**: effective_from, effective_until
- **Priority**: Lower number = higher precedence

#### `tax_calculations` Table
Immutable audit trail of all tax calculations:
- Polymorphic relation to any taxable entity (Booking, BookingPayment, PayoutRequest)
- Stores complete calculation snapshot
- Tracks who paid the tax (for reverse charge)
- Records TDS deductions

### 2. Models

#### `TaxRule` Model
```php
// Key methods
$rule->isEffective();                          // Check if rule is active and within date range
$rule->appliesTo($amount);                     // Check if rule applies to given amount
$rule->calculateTaxAmount($baseAmount);        // Calculate tax amount
$rule->shouldApplyReverseCharge($context);     // Check reverse charge applicability

// Scopes
TaxRule::active()                              // Only active rules
TaxRule::byType('gst')                         // Filter by tax type
TaxRule::byAppliesTo('booking')                // Filter by applies_to
TaxRule::forDate(now())                        // Rules effective on given date
TaxRule::orderByPriority()                     // Order by priority (ascending)
```

#### `TaxCalculation` Model
```php
// Immutable audit record - cannot be updated or deleted
$calculation->taxable;                         // Get related entity (Booking, etc.)
$calculation->taxRule;                         // Get the rule used
$calculation->formatted_tax_amount;            // ₹1,234.56
$calculation->summary;                         // "GST 18% on ₹10,000 = ₹1,800"
```

### 3. TaxService

Main service for all tax calculations.

#### Core Methods

```php
use App\Services\TaxService;

$taxService = app(TaxService::class);

// 1. Apply tax to any entity
$result = $taxService->applyTax(
    $booking,                    // Taxable entity (Model)
    10000.00,                    // Base amount
    'booking',                   // Applies to: booking/commission/payout/all
    [                            // Context (optional)
        'customer_type' => 'business',
        'has_gstin' => true,
        'state' => 'MH',
    ]
);
// Returns:
// [
//     'tax_amount' => 1800.00,
//     'calculations' => [TaxCalculation, ...],
//     'total_with_tax' => 11800.00,
//     'breakdown' => [...]
// ]

// 2. Calculate GST specifically
$gst = $taxService->calculateGST(10000.00, [
    'applies_to' => 'booking',
    'customer_type' => 'business',
    'has_gstin' => true,
]);
// Returns:
// [
//     'gst_amount' => 1800.00,
//     'gst_rate' => 18.00,
//     'is_reverse_charge' => true,
//     'paid_by' => 'customer',
//     'rule' => TaxRule
// ]

// 3. Calculate TDS specifically
$tds = $taxService->calculateTDS(50000.00, [
    'applies_to' => 'payout',
    'vendor_type' => 'professional',
]);
// Returns:
// [
//     'tds_amount' => 5000.00,
//     'tds_rate' => 10.00,
//     'tds_section' => '194J',
//     'applies' => true,
//     'rule' => TaxRule
// ]

// 4. Check reverse charge
$isReverseCharge = $taxService->checkReverseCharge([
    'customer_type' => 'business',
    'vendor_type' => 'business',
    'has_gstin' => true,
]);

// 5. Get tax calculations for entity
$calculations = $taxService->getTaxCalculations($booking);

// 6. Get tax summary
$summary = $taxService->getTaxSummary($booking);
// Returns:
// [
//     'total_tax' => 1800.00,
//     'total_tds' => 500.00,
//     'total_gst' => 1800.00,
//     'reverse_charge_amount' => 1800.00,
//     'breakdown_by_type' => [...]
// ]
```

## Integration Examples

### Example 1: CommissionService Integration

**Before (Hardcoded):**
```php
public function calculateAndRecord(Booking $booking, string $razorpayPaymentId, string $razorpayOrderId): array
{
    $grossAmount = (float) $booking->total_amount;
    $adminCommission = 1500.00; // Calculated
    
    // OLD: Hardcoded tax from settings
    $taxRate = (float) $this->settingsService->get('commission_tax_rate', 0.00);
    $tax = round($adminCommission * ($taxRate / 100), 2);
    
    // ... rest of calculation
}
```

**After (Dynamic TaxService):**
```php
use App\Services\TaxService;

protected TaxService $taxService;

public function __construct(
    SettingsService $settingsService, 
    CommissionRuleService $ruleService,
    TaxService $taxService
) {
    $this->settingsService = $settingsService;
    $this->ruleService = $ruleService;
    $this->taxService = $taxService;
}

public function calculateAndRecord(Booking $booking, string $razorpayPaymentId, string $razorpayOrderId): array
{
    return DB::transaction(function () use ($booking, $razorpayPaymentId, $razorpayOrderId) {
        $grossAmount = (float) $booking->total_amount;
        $adminCommission = 1500.00; // Calculated
        
        // NEW: Dynamic tax calculation with TaxService
        $taxResult = $this->taxService->applyTax(
            $booking,                           // Entity for audit trail
            $adminCommission,                   // Base amount
            'commission',                       // Applies to
            [
                'calculated_by' => 'CommissionService',
                'booking_id' => $booking->id,
            ]
        );
        
        $tax = $taxResult['tax_amount'];
        
        // Create BookingPayment with tax calculations linked
        $bookingPayment = BookingPayment::create([
            'booking_id' => $booking->id,
            'gross_amount' => $grossAmount,
            'admin_commission_amount' => $adminCommission,
            'tax_amount' => $tax,
            // ... other fields
        ]);
        
        // Tax calculations are automatically created and linked to $booking
        
        return [$bookingPayment, $commissionLog];
    });
}
```

### Example 2: DynamicPriceCalculator Integration

**Before:**
```php
protected function getGSTRate(): float
{
    return (float) $this->settingsService->get('booking_tax_rate', 18.00);
}

public function calculateGST(float $amount, float $rate): float
{
    return round($amount * ($rate / 100), 2);
}
```

**After:**
```php
use App\Services\TaxService;

protected TaxService $taxService;

public function calculate($hoarding, $duration, $appliedCoupon = null): array
{
    // ... price calculation
    
    // NEW: Use TaxService for GST
    $gstResult = $this->taxService->calculateGST($priceAfterDiscount, [
        'applies_to' => 'booking',
        'customer_type' => $customer->type ?? 'individual',
        'has_gstin' => !empty($customer->gstin),
        'state' => $hoarding->state,
    ]);
    
    $gstAmount = $gstResult['gst_amount'];
    $gstRate = $gstResult['gst_rate'];
    $isReverseCharge = $gstResult['is_reverse_charge'];
    
    $finalPrice = $priceAfterDiscount + $gstAmount;
    
    return [
        'base_price' => $basePrice,
        'discount' => $discountAmount,
        'price_after_discount' => $priceAfterDiscount,
        'gst_rate' => $gstRate,
        'gst_amount' => $gstAmount,
        'is_reverse_charge' => $isReverseCharge,
        'final_price' => $finalPrice,
    ];
}
```

### Example 3: PayoutService Integration

**Before:**
```php
public function calculateGST(float $netBeforeGst, array $options = []): float
{
    $gstPercentage = $options['gst_percentage'] ?? 0;
    return $gstPercentage > 0 ? ($netBeforeGst * ($gstPercentage / 100)) : 0;
}
```

**After:**
```php
use App\Services\TaxService;

protected TaxService $taxService;

public function processPayout(PayoutRequest $payoutRequest): array
{
    $netBeforeGst = $payoutRequest->amount;
    $vendor = $payoutRequest->vendor;
    
    // Calculate GST using TaxService
    $gstResult = $this->taxService->calculateGST($netBeforeGst, [
        'applies_to' => 'payout',
        'vendor_type' => $vendor->vendor_type,
        'has_gstin' => !empty($vendor->gstin),
    ]);
    
    // Calculate TDS if applicable
    $tdsResult = $this->taxService->calculateTDS($netBeforeGst, [
        'applies_to' => 'payout',
        'vendor_type' => $vendor->vendor_type,
    ]);
    
    $gstAmount = $gstResult['gst_amount'];
    $tdsAmount = $tdsResult['tds_amount'];
    
    // Record calculations
    $this->taxService->applyTax(
        $payoutRequest,
        $netBeforeGst,
        'payout',
        [
            'vendor_id' => $vendor->id,
            'vendor_type' => $vendor->vendor_type,
            'calculated_by' => 'PayoutService',
        ]
    );
    
    $finalPayout = $netBeforeGst + $gstAmount - $tdsAmount;
    
    return [
        'net_before_gst' => $netBeforeGst,
        'gst_amount' => $gstAmount,
        'tds_amount' => $tdsAmount,
        'tds_section' => $tdsResult['tds_section'],
        'final_payout' => $finalPayout,
    ];
}
```

## Default Tax Rules

The system comes pre-configured with these tax rules:

### 1. GST - Standard Rate (18%)
- **Code**: `GST_IN_18`
- **Rate**: 18%
- **Applies To**: All
- **Priority**: 10
- **Status**: Active

### 2. GST - Reverse Charge (B2B)
- **Code**: `GST_IN_RC_B2B`
- **Rate**: 18%
- **Applies To**: Booking
- **Conditions**: customer_type = business, has_gstin = true
- **Priority**: 5 (higher than standard GST)
- **Status**: Active

### 3. TDS - Section 194J (Professional Services)
- **Code**: `TDS_IN_194J`
- **Rate**: 10%
- **Applies To**: Payout
- **Threshold**: ₹30,000
- **Priority**: 10
- **Status**: Active

### 4. TDS - Section 194C (Contractors)
- **Code**: `TDS_IN_194C`
- **Rate**: 1%
- **Applies To**: Payout
- **Threshold**: ₹30,000
- **Priority**: 10
- **Status**: Active

### 5. TDS - Section 194H (Commission)
- **Code**: `TDS_IN_194H`
- **Rate**: 5%
- **Applies To**: Commission
- **Threshold**: ₹15,000
- **Priority**: 10
- **Status**: Active

## Admin Panel (Future Implementation)

The admin panel will allow:

1. **View All Rules**: List with filters (type, status, applies_to)
2. **Create Rule**: Form with:
   - Basic: name, code, tax_type, rate
   - Applicability: applies_to, conditions (JSON editor)
   - Reverse Charge: toggle, conditions
   - TDS: toggle, threshold, section
   - Geography: country, states
   - Scheduling: effective_from, effective_until
   - Priority: number (lower = higher)

3. **Edit Rule**: Same as create
4. **Activate/Deactivate**: Quick toggle
5. **View Calculations**: See audit trail of all tax calculations
6. **Reports**: Tax collected, TDS deducted, by date range

## Context Parameters

When calling TaxService methods, provide context for accurate rule matching:

```php
$context = [
    // Required
    'applies_to' => 'booking',          // booking/commission/payout/all
    
    // Customer/User context
    'customer_type' => 'business',      // business/individual
    'user_type' => 'registered',        // registered/guest
    'has_gstin' => true,                // boolean
    
    // Vendor context (for payouts)
    'vendor_type' => 'professional',    // professional/contractor/agent
    
    // Geography
    'country_code' => 'IN',             // ISO country code
    'state' => 'MH',                    // State code
    
    // Date (defaults to now())
    'date' => Carbon::parse('2025-01-01'),
    
    // Audit
    'calculated_by' => 'ServiceName',
    'booking_id' => 123,
    'vendor_id' => 456,
    
    // Reverse charge
    'paid_by' => 'customer',            // customer/vendor/platform
];
```

## Rule Matching Logic

1. **Active Status**: Only `is_active = true` rules are considered
2. **Date Range**: Rule must be effective (`effective_from <= now <= effective_until`)
3. **Applies To**: Rule's `applies_to` must match or be 'all'
4. **Geography**: Rule's `country_code` must match, and state (if specified) must be in `applicable_states`
5. **Conditions**: Custom JSON conditions must match context
6. **Threshold**: For TDS, amount must be >= `tds_threshold`
7. **Priority**: If multiple rules match, highest priority (lowest number) wins

## Testing

```php
use App\Models\Booking;
use App\Services\TaxService;

// Test 1: Basic GST calculation
$booking = Booking::find(1);
$taxService = app(TaxService::class);

$result = $taxService->applyTax($booking, 10000.00, 'booking');
$this->assertEquals(1800.00, $result['tax_amount']);

// Test 2: Reverse charge for B2B
$result = $taxService->calculateGST(10000.00, [
    'applies_to' => 'booking',
    'customer_type' => 'business',
    'has_gstin' => true,
]);
$this->assertTrue($result['is_reverse_charge']);

// Test 3: TDS with threshold
$result = $taxService->calculateTDS(50000.00, [
    'applies_to' => 'payout',
    'vendor_type' => 'professional',
]);
$this->assertTrue($result['applies']);
$this->assertEquals('194J', $result['tds_section']);

// Test 4: TDS below threshold
$result = $taxService->calculateTDS(20000.00, [
    'applies_to' => 'payout',
    'vendor_type' => 'professional',
]);
$this->assertFalse($result['applies']);
```

## Migration Commands

```bash
# Run migrations
php artisan migrate

# Seed default tax rules
php artisan db:seed --class=TaxRulesSeeder

# Check created rules
php artisan tinker
>>> App\Models\TaxRule::count()
>>> App\Models\TaxRule::active()->get(['code', 'name', 'rate'])
```

## Backwards Compatibility

For services that still use the old settings-based approach, TaxService provides a fallback:

```php
$defaultRate = $taxService->getDefaultTaxRate('booking'); // Returns 18.00
```

This method checks for active rules first, then falls back to the highest priority active rule for the given applies_to value.

## Future Enhancements

1. **Admin UI**: Full CRUD interface for tax rules
2. **Rule Validation**: Real-time preview of tax calculation
3. **Bulk Operations**: Import/export rules via CSV/JSON
4. **Rule Templates**: Pre-configured rules for different countries
5. **Notifications**: Alert when rules expire or become active
6. **Analytics**: Tax collection reports, TDS reports
7. **API Endpoints**: RESTful API for rule management
8. **Webhooks**: Trigger events when tax rules change
9. **Multi-currency**: Support for different currencies
10. **Tax Holidays**: Special exemption periods

## Troubleshooting

### No rules found
- Check if rules are active: `TaxRule::active()->count()`
- Verify effective dates
- Check applies_to matches context

### Incorrect tax amount
- Check rule priority (lower number = higher priority)
- Verify conditions match context
- Review calculation_method (percentage/flat/tiered)

### TDS not applying
- Check threshold: amount must be >= tds_threshold
- Verify is_tds flag is true
- Check vendor_type matches conditions

### Reverse charge not applying
- Verify customer has GSTIN in context
- Check customer_type = 'business'
- Ensure rule has is_reverse_charge = true

## Support

For issues or questions, check:
- Database: `tax_rules` and `tax_calculations` tables
- Logs: Search for "Tax calculated" or "No tax rules found"
- Models: `App\Models\TaxRule`, `App\Models\TaxCalculation`
- Service: `App\Services\TaxService`

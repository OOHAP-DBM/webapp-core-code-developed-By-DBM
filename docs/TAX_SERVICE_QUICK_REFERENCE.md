# Tax Rules Engine - Quick Reference Card

## 🚀 Quick Start

### Use TaxService in Your Code
```php
use App\Services\TaxService;

$taxService = app(TaxService::class);
```

## 📋 Common Operations

### Calculate GST
```php
$result = $taxService->calculateGST(10000, [
    'applies_to' => 'booking',  // or 'commission', 'payout'
    'customer_type' => 'business',
    'has_gstin' => true,
    'state' => 'MH',
]);

// Returns:
// [
//     'gst_amount' => 1800.00,
//     'gst_rate' => 18.00,
//     'is_reverse_charge' => true,
//     'paid_by' => 'customer',
//     'rule' => TaxRule
// ]
```

### Calculate TDS
```php
$result = $taxService->calculateTDS(50000, [
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
```

### Apply Tax with Audit Trail
```php
$result = $taxService->applyTax(
    $booking,              // Taxable entity
    10000,                 // Base amount
    'booking',             // Type
    [
        'customer_type' => 'business',
        'state' => 'MH',
        'calculated_by' => 'YourService'
    ]
);

// Returns:
// [
//     'tax_amount' => 1800.00,
//     'calculations' => [TaxCalculation, ...],
//     'total_with_tax' => 11800.00,
//     'breakdown' => [...]
// ]
```

### Get Default Tax Rate (Backwards Compatible)
```php
$rate = $taxService->getDefaultTaxRate('booking'); // 18.00
```

### Get Tax Summary for Entity
```php
$summary = $taxService->getTaxSummary($booking);

// Returns:
// [
//     'total_tax' => 1800.00,
//     'total_tds' => 500.00,
//     'total_gst' => 1800.00,
//     'reverse_charge_amount' => 0,
//     'breakdown_by_type' => [...]
// ]
```

## 🎯 Context Parameters

### Required
- `applies_to`: `'booking'` | `'commission'` | `'payout'` | `'all'`

### Optional
```php
[
    'customer_type' => 'business',        // or 'individual'
    'vendor_type' => 'professional',      // or 'contractor', 'agent'
    'has_gstin' => true,                  // boolean
    'state' => 'MH',                      // State code
    'country_code' => 'IN',               // ISO country code
    'date' => Carbon::now(),              // Date for rule matching
    'calculated_by' => 'ServiceName',     // For audit trail
    'booking_id' => 123,                  // For reference
    'vendor_id' => 456,                   // For reference
]
```

## 📊 Default Tax Rules

| Code | Name | Rate | Type | Threshold | Status |
|------|------|------|------|-----------|--------|
| GST_IN_18 | GST Standard | 18% | GST | - | Active |
| GST_IN_RC_B2B | GST Reverse Charge | 18% | Reverse Charge | - | Active |
| TDS_IN_194J | TDS Professional | 10% | TDS | ₹30,000 | Active |
| TDS_IN_194C | TDS Contractors | 1% | TDS | ₹30,000 | Active |
| TDS_IN_194H | TDS Commission | 5% | TDS | ₹15,000 | Active |

## 🔍 Query Tax Rules

### Get Active Rules
```php
use App\Models\TaxRule;

$rules = TaxRule::active()->get();
```

### Get Rules by Type
```php
$gstRules = TaxRule::active()->byType('gst')->get();
$tdsRules = TaxRule::active()->byType('tds')->get();
```

### Get Rules for Specific Date
```php
$rules = TaxRule::active()
    ->forDate(Carbon::parse('2025-06-01'))
    ->orderByPriority()
    ->get();
```

### Get Applicable Rules
```php
$rules = TaxRule::active()
    ->byAppliesTo('booking')
    ->byCountry('IN')
    ->forDate(now())
    ->orderByPriority()
    ->get();
```

## 🔨 Database Commands

### Seed Tax Rules
```bash
php artisan db:seed --class=TaxRulesSeeder
```

### Check Active Rules
```bash
php artisan tinker
>>> App\Models\TaxRule::active()->count()
>>> App\Models\TaxRule::active()->pluck('code', 'name')
```

### Clear Tax Cache
```bash
>>> app(App\Services\TaxService::class)->clearCache()
```

## 📝 Tax Calculations Audit

### Get Calculations for Entity
```php
$calculations = TaxCalculation::forTaxable(Booking::class, $bookingId)->get();
```

### Get Recent Calculations
```php
$recent = TaxCalculation::forDate(now()->subDays(7), now())->get();
```

### Get by Tax Type
```php
$gstCalcs = TaxCalculation::byType('gst')->get();
$tdsCalcs = TaxCalculation::byType('tds')->get();
```

## 🎨 Model Scopes

### TaxRule
- `active()` - Only active rules
- `byType($type)` - Filter by tax type
- `byAppliesTo($appliesTo)` - Filter by applies_to
- `byCountry($countryCode)` - Filter by country
- `forDate($date)` - Effective on date
- `orderByPriority()` - Order by priority (asc)

### TaxCalculation
- `byType($type)` - Filter by tax type
- `forDate($start, $end)` - Date range
- `forTaxable($type, $id)` - For specific entity

## ⚠️ Important Notes

### TaxCalculation Records
- **Immutable**: Cannot be updated or deleted
- **Complete Snapshot**: Stores full calculation details
- **Polymorphic**: Links to any taxable entity

### Rule Matching Order
1. Active status (`is_active = true`)
2. Effective dates (within range)
3. Applies to (matches or 'all')
4. Geography (country + state)
5. Conditions (JSON matching)
6. Priority (lower number = higher)

### Backwards Compatibility
```php
// Old way (still works)
$rate = $settingsService->get('booking_tax_rate', 18.00);

// New way (recommended)
$rate = $taxService->getDefaultTaxRate('booking');
```

## 🐛 Troubleshooting

### No Rules Found
```php
// Check if rules are active
TaxRule::active()->count();

// Check effective dates
TaxRule::active()->forDate(now())->count();

// Check applies_to
TaxRule::active()->byAppliesTo('booking')->count();
```

### Tax Amount is Zero
- Verify rule is active and effective
- Check threshold (for TDS)
- Verify conditions match context
- Check priority (higher priority rule may match first)

### Wrong Tax Amount
- Check rule rate
- Verify calculation_method (percentage/flat/tiered)
- Review conditions in rule
- Check for multiple matching rules

## 📚 Documentation

- **Full Guide**: `docs/PROMPT_62_TAX_RULES_ENGINE.md`
- **Integration**: `docs/PROMPT_62_INTEGRATION_COMPLETE.md`
- **Summary**: `docs/PROMPT_62_SUMMARY.md`

## 🧪 Testing

```bash
# Run test script
php test_tax_service.php

# Run unit tests
php artisan test --filter=DynamicPriceCalculatorTest
```

## 📞 Support

For issues:
1. Check logs: Search for "Tax calculated" or "No tax rules found"
2. Verify database: Check `tax_rules` and `tax_calculations` tables
3. Review context: Ensure all required parameters provided
4. Test with tinker: Manual testing with `php artisan tinker`

---

**Version**: 1.0.0  
**Last Updated**: December 10, 2025  
**Status**: Production Ready ✅

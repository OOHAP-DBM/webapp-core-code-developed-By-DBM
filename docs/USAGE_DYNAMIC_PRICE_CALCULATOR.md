# Dynamic Price Calculator - Usage Guide

## Overview

The `DynamicPriceCalculator` service provides comprehensive pricing calculations for hoarding bookings with support for discounts, packages, and GST.

## Basic Usage

### 1. Simple Price Calculation

```php
use App\Services\DynamicPriceCalculator;
use Modules\Settings\Services\SettingsService;

// Initialize calculator
$settingsService = app(SettingsService::class);
$calculator = new DynamicPriceCalculator($settingsService);

// Calculate price
$result = $calculator->calculate(
    hoarding Id: 1,
    bookingStart: '2025-01-15',
    bookingEnd: '2025-02-15'
);

// Result structure:
// [
//     'base_price' => 30000.00,
//     'discount_applied' => 0.00,
//     'vendor_offer_applied' => 0.00,
//     'gst' => 5400.00,
//     'final_price' => 35400.00,
//     'breakdown' => [...]
// ]
```

### 2. With Percentage Discount

```php
$vendorDiscounts = [
    'type' => 'percent',
    'value' => 15 // 15% discount
];

$result = $calculator->calculate(
    hoardingId: 1,
    bookingStart: '2025-01-15',
    bookingEnd: '2025-02-15',
    packageId: null,
    vendorDiscounts: $vendorDiscounts
);

// base_price: 30000.00
// discount_applied: 4500.00 (15% of 30000)
// gst: 4590.00 (18% of 25500)
// final_price: 30090.00
```

### 3. With Fixed Amount Discount

```php
$vendorDiscounts = [
    'type' => 'fixed',
    'value' => 5000 // ₹5000 discount
];

$result = $calculator->calculate(
    hoardingId: 1,
    bookingStart: '2025-01-15',
    bookingEnd: '2025-02-15',
    packageId: null,
    vendorDiscounts: $vendorDiscounts
);

// base_price: 30000.00
// discount_applied: 5000.00
// gst: 4500.00 (18% of 25000)
// final_price: 29500.00
```

### 4. With DOOH Package

```php
// Package with 5% discount
$result = $calculator->calculate(
    hoardingId: 1,
    bookingStart: '2025-01-15',
    bookingEnd: '2025-03-15', // 2 months
    packageId: 5, // DOOH package ID
    vendorDiscounts: []
);

// Package discount automatically applied
// If package price_per_month = 25000 with 5% discount:
// base_price: 47500.00 (25000 * 2 * 0.95)
// gst: 8550.00
// final_price: 56050.00
```

### 5. Quick Estimate

For fast estimates without full validation (useful for previews):

```php
$estimate = $calculator->quickEstimate(
    hoardingId: 1,
    bookingStart: '2025-01-15',
    bookingEnd: '2025-02-15',
    vendorDiscounts: ['type' => 'percent', 'value' => 10]
);

// Returns same structure but skips some validations
```

### 6. Compare Prices

Compare prices with and without discount:

```php
$comparison = $calculator->compareWithDiscount(
    hoardingId: 1,
    bookingStart: '2025-01-15',
    bookingEnd: '2025-02-15',
    vendorDiscounts: ['type' => 'percent', 'value' => 20]
);

// Result:
// [
//     'without_discount' => [
//         'base_price' => 30000,
//         'gst' => 5400,
//         'final_price' => 35400
//     ],
//     'with_discount' => [
//         'base_price' => 30000,
//         'discount_applied' => 6000,
//         'gst' => 4320,
//         'final_price' => 28320
//     ],
//     'savings' => 7080,
//     'savings_percent' => 20
// ]
```

## Controller Integration Example

```php
namespace App\Http\Controllers;

use App\Services\DynamicPriceCalculator;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function calculatePrice(Request $request, DynamicPriceCalculator $calculator)
    {
        $validated = $request->validate([
            'hoarding_id' => 'required|exists:hoardings,id',
            'booking_start' => 'required|date|after:today',
            'booking_end' => 'required|date|after:booking_start',
            'package_id' => 'nullable|exists:dooh_packages,id',
            'discount_type' => 'nullable|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        $vendorDiscounts = [];
        if ($request->filled(['discount_type', 'discount_value'])) {
            $vendorDiscounts = [
                'type' => $request->discount_type,
                'value' => $request->discount_value,
            ];
        }

        try {
            $result = $calculator->calculate(
                $validated['hoarding_id'],
                $validated['booking_start'],
                $validated['booking_end'],
                $validated['package_id'] ?? null,
                $vendorDiscounts
            );

            return response()->json([
                'success' => true,
                'pricing' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
```

## API Endpoint Example

```php
// routes/api.php
Route::post('/bookings/calculate-price', [BookingController::class, 'calculatePrice'])
    ->middleware('auth:sanctum');
```

### Request:

```json
POST /api/bookings/calculate-price
{
    "hoarding_id": 1,
    "booking_start": "2025-01-15",
    "booking_end": "2025-02-15",
    "package_id": null,
    "discount_type": "percent",
    "discount_value": 15
}
```

### Response:

```json
{
    "success": true,
    "pricing": {
        "base_price": 30000.00,
        "discount_applied": 4500.00,
        "vendor_offer_applied": 4500.00,
        "gst": 4590.00,
        "final_price": 30090.00,
        "breakdown": {
            "hoarding": {
                "id": 1,
                "title": "Premium Billboard - MG Road",
                "monthly_price": 30000.00,
                "weekly_price": 8000.00
            },
            "duration": {
                "start_date": "2025-01-15",
                "end_date": "2025-02-15",
                "days": 31,
                "weeks": 4,
                "months": 1
            },
            "pricing": {
                "base_price": 30000.00,
                "discount": {
                    "type": "percent",
                    "value": 15,
                    "amount": 4500.00
                },
                "price_after_discount": 25500.00,
                "gst": {
                    "rate": 18,
                    "amount": 4590.00
                }
            },
            "package_used": false,
            "calculated_at": "2025-12-09 15:30:45"
        }
    }
}
```

## Blade View Integration

### Booking Form with Live Price Calculation

```html
<form id="booking-form">
    <div class="form-group">
        <label>Select Hoarding</label>
        <select name="hoarding_id" class="form-control" required>
            <option value="">Choose...</option>
            @foreach($hoardings as $hoarding)
                <option value="{{ $hoarding->id }}">
                    {{ $hoarding->title }} - ₹{{ number_format($hoarding->monthly_price) }}/month
                </option>
            @endforeach
        </select>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="booking_start" class="form-control" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="booking_end" class="form-control" required>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>Discount Code (Optional)</label>
        <input type="text" name="discount_code" class="form-control">
    </div>

    <div id="price-summary" class="card">
        <div class="card-body">
            <h5>Price Summary</h5>
            <div id="price-loading" class="text-center d-none">
                <div class="spinner-border" role="status"></div>
                <p>Calculating price...</p>
            </div>
            <div id="price-details" style="display: none;">
                <table class="table">
                    <tr>
                        <td>Base Price:</td>
                        <td class="text-right">₹<span id="base-price">0</span></td>
                    </tr>
                    <tr id="discount-row" style="display: none;">
                        <td>Discount:</td>
                        <td class="text-right text-success">-₹<span id="discount-amount">0</span></td>
                    </tr>
                    <tr>
                        <td>GST (18%):</td>
                        <td class="text-right">₹<span id="gst-amount">0</span></td>
                    </tr>
                    <tr class="font-weight-bold">
                        <td>Total Amount:</td>
                        <td class="text-right">₹<span id="final-price">0</span></td>
                    </tr>
                </table>
                <div id="duration-info" class="small text-muted"></div>
            </div>
            <div id="price-error" class="alert alert-danger" style="display: none;"></div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Proceed to Booking</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('booking-form');
    const hoardingSelect = form.querySelector('[name="hoarding_id"]');
    const startDate = form.querySelector('[name="booking_start"]');
    const endDate = form.querySelector('[name="booking_end"]');
    
    // Calculate price when inputs change
    [hoardingSelect, startDate, endDate].forEach(input => {
        input.addEventListener('change', calculatePrice);
    });
    
    async function calculatePrice() {
        const hoardingId = hoardingSelect.value;
        const start = startDate.value;
        const end = endDate.value;
        
        if (!hoardingId || !start || !end) {
            return;
        }
        
        // Show loading
        document.getElementById('price-loading').classList.remove('d-none');
        document.getElementById('price-details').style.display = 'none';
        document.getElementById('price-error').style.display = 'none';
        
        try {
            const response = await fetch('/api/bookings/calculate-price', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token')
                },
                body: JSON.stringify({
                    hoarding_id: hoardingId,
                    booking_start: start,
                    booking_end: end
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                displayPrice(data.pricing);
            } else {
                showError(data.message);
            }
        } catch (error) {
            showError('Failed to calculate price. Please try again.');
        } finally {
            document.getElementById('price-loading').classList.add('d-none');
        }
    }
    
    function displayPrice(pricing) {
        document.getElementById('base-price').textContent = formatPrice(pricing.base_price);
        document.getElementById('gst-amount').textContent = formatPrice(pricing.gst);
        document.getElementById('final-price').textContent = formatPrice(pricing.final_price);
        
        // Show/hide discount row
        const discountRow = document.getElementById('discount-row');
        if (pricing.discount_applied > 0) {
            document.getElementById('discount-amount').textContent = formatPrice(pricing.discount_applied);
            discountRow.style.display = '';
        } else {
            discountRow.style.display = 'none';
        }
        
        // Show duration info
        const breakdown = pricing.breakdown;
        const durationText = `${breakdown.duration.days} days (${breakdown.duration.months} month${breakdown.duration.months !== 1 ? 's' : ''})`;
        document.getElementById('duration-info').textContent = durationText;
        
        document.getElementById('price-details').style.display = 'block';
    }
    
    function showError(message) {
        const errorDiv = document.getElementById('price-error');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
    
    function formatPrice(amount) {
        return parseFloat(amount).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
});
</script>
```

## Validation Rules

### Date Validation
- `booking_start` must be today or future date
- `booking_end` must be after `booking_start`
- Same-day bookings are allowed (1-day duration)
- Maximum booking duration: 365 days (configurable via settings)

### Hoarding Validation
- Hoarding must exist in database
- Hoarding must have status = 'active'
- Hoarding must have valid pricing (monthly_price > 0)

### Discount Validation
- Discount type must be 'percent' or 'fixed'
- Discount value must be >= 0
- Discount cannot exceed base price
- For percent discount: 0-100
- For fixed discount: absolute amount

### Package Validation
- Package must exist and be active
- Package duration constraints:
  * Booking duration >= min_booking_months
  * Booking duration <= max_booking_months
- Package must belong to a DOOH screen linked to the hoarding

## Price Calculation Logic

### Monthly Bookings
```
days = end_date - start_date + 1
months = floor(days / 30)
remaining_days = days % 30

if months > 0:
    monthly_cost = months * monthly_price
    daily_cost = remaining_days * (monthly_price / 30)
    base_price = monthly_cost + daily_cost
else:
    base_price = days * (monthly_price / 30)
```

### Weekly Bookings
```
If enable_weekly_booking = true AND days <= 21:
    weeks = ceil(days / 7)
    base_price = weeks * weekly_price
```

### With Package
```
If DOOH package exists:
    monthly_count = ceil(days / 30)
    base_price = monthly_count * package.price_per_month
    
    If package has discount:
        discount = base_price * (package.discount_percent / 100)
        base_price = base_price - discount
```

### With Vendor Discount
```
If discount type = 'percent':
    discount_amount = base_price * (discount_value / 100)
elif discount type = 'fixed':
    discount_amount = min(discount_value, base_price)

price_after_discount = base_price - discount_amount
```

### GST Calculation
```
gst_rate = settings('gst_rate', 18) // Default 18%
gst_amount = price_after_discount * (gst_rate / 100)
final_price = price_after_discount + gst_amount
```

## Error Handling

```php
try {
    $result = $calculator->calculate(...);
} catch (\Exception $e) {
    // Possible exceptions:
    // - "Start date cannot be in the past"
    // - "End date must be after start date"
    // - "Hoarding not found"
    // - "Hoarding is not active"
    // - "Booking duration cannot exceed X days"
    // - "Package requires minimum X months"
    // - "Package allows maximum X months"
    
    Log::error('Price calculation failed', [
        'error' => $e->getMessage(),
        'hoarding_id' => $hoardingId,
        'dates' => [$startDate, $endDate]
    ]);
    
    return back()->withErrors(['price' => $e->getMessage()]);
}
```

## Configuration

Settings can be configured via `Modules\Settings\Services\SettingsService`:

```php
// GST Rate (default: 18%)
$settingsService->set('gst_rate', 18);

// Maximum booking duration in days (default: 365)
$settingsService->set('max_booking_duration_days', 365);
```

## Testing

Run the test suite:

```bash
php artisan test --filter=DynamicPriceCalculatorTest
```

Test coverage:
- ✓ Basic monthly booking calculations
- ✓ Weekly booking calculations
- ✓ Percentage discounts
- ✓ Fixed amount discounts
- ✓ Discount capping (no negative prices)
- ✓ Date validation (past dates, invalid ranges)
- ✓ Hoarding validation (status, existence)
- ✓ Single-day bookings
- ✓ Multi-month bookings
- ✓ Custom GST rates
- ✓ Price comparisons
- ✓ Detailed breakdowns
- ⊘ DOOH package integration (requires factory)

## Performance Considerations

1. **Caching**: Consider caching hoarding pricing data
2. **Database Queries**: Calculator makes 1-3 queries per calculation
3. **Quick Estimate**: Use `quickEstimate()` for preview/listing pages
4. **Batch Calculations**: For multiple hoardings, consider async processing

## Future Enhancements

Potential features to add:
- Seasonal pricing adjustments
- Bulk booking discounts
- Early bird discounts
- Last-minute booking premiums
- Multi-hoarding package deals
- Loyalty program integration
- Currency conversion support
- Tax exemption handling

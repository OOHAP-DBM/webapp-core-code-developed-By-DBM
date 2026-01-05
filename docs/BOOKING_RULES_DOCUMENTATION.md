# Booking Rules Configuration - Prompt 20 Implementation

## Overview
This implementation adds admin-configurable booking rules to the OohApp system, allowing admins to manage booking constraints dynamically without code changes.

## Features Implemented

### 1. Booking Rules Settings
Six configurable settings added to control booking behavior:

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| `booking_hold_minutes` | integer | 30 | Minutes to hold a booking before payment is required |
| `grace_period_minutes` | integer | 15 | Grace period before booking start time for cancellation |
| `max_future_booking_start_months` | integer | 12 | Maximum months in future a booking can start |
| `booking_min_duration_days` | integer | 7 | Minimum booking duration in days |
| `booking_max_duration_months` | integer | 12 | Maximum booking duration in months |
| `allow_weekly_booking` | boolean | false | Enable weekly booking option |

### 2. Admin Web Interface
- **Route**: `/admin/booking-rules`
- **Controller**: `App\Http\Controllers\Web\Admin\BookingRuleController`
- **View**: `resources/views/admin/settings/booking_rules.blade.php`
- **Features**:
  - Bootstrap 5 responsive UI
  - Form validation with visual feedback
  - Contextual icons and color coding
  - Guidelines and help text
  - Back to settings navigation

### 3. Admin API Interface
- **Base URL**: `/api/v1/admin/booking-rules`
- **Controller**: `App\Http\Controllers\Api\Admin\BookingRulesController`
- **Authentication**: Requires `auth:sanctum` + `role:super_admin|admin`

#### Endpoints:

**GET /api/v1/admin/booking-rules**
```json
{
  "success": true,
  "data": {
    "booking_hold_minutes": {
      "value": 30,
      "type": "integer",
      "description": "Minutes to hold a booking before payment required",
      "group": "booking"
    },
    // ... other rules
  }
}
```

**PUT /api/v1/admin/booking-rules**
```json
// Request
{
  "booking_hold_minutes": 45,
  "grace_period_minutes": 20,
  "max_future_booking_start_months": 18,
  "booking_min_duration_days": 7,
  "booking_max_duration_months": 12,
  "allow_weekly_booking": true
}

// Response
{
  "success": true,
  "message": "Booking rules updated successfully",
  "data": { /* updated values */ }
}
```

### 4. Service Integration

#### BookingService Updates
Updated `Modules\Bookings\Services\BookingService` to read from SettingsService:

**Methods Added:**
- `getBookingHoldMinutes()`: Returns configurable hold time
- `getGracePeriodMinutes()`: Returns grace period setting
- `getMaxFutureBookingStartMonths()`: Returns max future booking limit
- `getBookingMinDurationDays()`: Returns minimum duration
- `getBookingMaxDurationMonths()`: Returns maximum duration
- `isWeeklyBookingAllowed()`: Returns weekly booking toggle

**Updated Methods:**
- `createFromQuotation()`: Uses `getBookingHoldMinutes()` instead of hardcoded 30
- `updatePaymentAuthorized()`: Uses settings-based hold time

### 5. Database Seeder
- **File**: `database/seeders/BookingRulesSeeder.php`
- **Usage**: `php artisan db:seed --class=BookingRulesSeeder`
- **Purpose**: Initializes default booking rules in settings table

## File Structure

```
app/
  Http/
    Controllers/
      Api/
        Admin/
          BookingRulesController.php    [NEW]
      Web/
        Admin/
          BookingRuleController.php     [NEW]

database/
  seeders/
    BookingRulesSeeder.php              [NEW]

resources/
  views/
    admin/
      settings/
        booking_rules.blade.php         [NEW]

routes/
  web.php                               [UPDATED]
  api_v1/
    admin.php                           [UPDATED]

Modules/
  Bookings/
    Services/
      BookingService.php                [UPDATED]
```

## Routes Added

### Web Routes
```php
// Admin Panel - Booking Rules
GET  /admin/booking-rules       admin.booking-rules.index
PUT  /admin/booking-rules       admin.booking-rules.update
```

### API Routes
```php
// API v1 - Admin Booking Rules
GET  /api/v1/admin/booking-rules    [auth:sanctum, role:admin]
PUT  /api/v1/admin/booking-rules    [auth:sanctum, role:admin]
```

## Usage Examples

### Accessing Admin UI
1. Login as admin/super_admin
2. Navigate to `/admin/booking-rules`
3. Update settings as needed
4. Click "Save Booking Rules"

### Using API
```bash
# Get current rules
curl -X GET https://api.oohapp.com/api/v1/admin/booking-rules \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Update rules
curl -X PUT https://api.oohapp.com/api/v1/admin/booking-rules \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "booking_hold_minutes": 45,
    "allow_weekly_booking": true
  }'
```

### Using in Code
```php
// In any service with BookingService injected
$bookingService = app(BookingService::class);

// Check if weekly booking is allowed
if ($bookingService->isWeeklyBookingAllowed()) {
    // Show weekly booking option
}

// Validate booking duration
$minDays = $bookingService->getBookingMinDurationDays();
$maxMonths = $bookingService->getBookingMaxDurationMonths();

if ($durationDays < $minDays) {
    throw new Exception("Minimum booking duration is {$minDays} days");
}
```

## Validation Rules

### API Validation
```php
'booking_hold_minutes' => 'nullable|integer|min:1|max:1440',
'grace_period_minutes' => 'nullable|integer|min:0|max:1440',
'max_future_booking_start_months' => 'nullable|integer|min:1|max:24',
'booking_min_duration_days' => 'nullable|integer|min:1|max:365',
'booking_max_duration_months' => 'nullable|integer|min:1|max:36',
'allow_weekly_booking' => 'nullable|boolean',
```

### Web Validation
Same as API but all fields marked as `required` except `allow_weekly_booking`.

## Caching
- Settings are cached using `SettingsService` with 1-hour TTL
- Cache is automatically cleared when settings are updated
- Cache key pattern: `settings:{key}`

## Testing Checklist

- [x] Seeder creates default settings
- [x] Web interface loads correctly
- [x] API GET returns all booking rules
- [x] API PUT updates settings
- [x] BookingService reads from settings
- [x] Cache clears on update
- [ ] Validation works on form submission
- [ ] API authentication/authorization works
- [ ] Settings persist across requests
- [ ] Weekly booking toggle works correctly

## Future Enhancements

1. **Validation in Controllers**: Add validation logic in booking/quotation controllers to enforce these rules
2. **Frontend Integration**: Update customer booking flow to respect these settings
3. **Audit Trail**: Log changes to booking rules for compliance
4. **Time-based Rules**: Different rules for peak/off-peak seasons
5. **Hoarding-specific Rules**: Override global rules per hoarding
6. **Notification System**: Alert admins when rules are changed

## Notes

- Settings are global (tenant_id = null) by default
- All settings use the 'booking' group for easy filtering
- Type conversion is handled automatically by SettingsService
- Boolean settings stored as 0/1 in database, converted to bool on retrieval

## Deployment Steps

1. Run migrations (if any settings table changes needed)
2. Run seeder: `php artisan db:seed --class=BookingRulesSeeder`
3. Clear caches: `php artisan cache:clear`
4. Clear routes: `php artisan route:clear`
5. Verify routes: Check `/admin/booking-rules` loads
6. Test API with admin credentials

## Support

For issues or questions, check:
- SettingsService: `Modules\Settings\Services\SettingsService`
- BookingService: `Modules\Bookings\Services\BookingService`
- Settings table: `settings` (key, value, type, description, group columns)

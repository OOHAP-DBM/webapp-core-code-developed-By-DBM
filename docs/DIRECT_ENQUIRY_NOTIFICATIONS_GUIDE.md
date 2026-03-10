# Direct Enquiry Push & In-App Notifications Implementation

## Overview
Implemented comprehensive push notifications and in-app notifications for direct enquiries (both mobile API and website). Notifications are sent to:
- **Vendors**: When a new enquiry is received with hoarding details
- **Customers**: When their enquiry is submitted successfully
- **Admins**: Notification summary

---

## Changes Made

### 1. New Notification Class: CustomerDirectEnquiryNotification
**Location**: `Modules\Enquiries\Notifications\CustomerDirectEnquiryNotification.php`

**Purpose**: In-app notification for customers when they submit a direct enquiry

**Key Features**:
- Displays in customer's notification center on website
- Shows hoarding type and city information
- Includes action URL linking to enquiry details
- Uses `ShouldQueue` for background processing

**Data Stored**:
```php
- type: 'customer_direct_enquiry_submitted'
- enquiry_id
- title: 'Enquiry Submitted Successfully'
- message: "Your {DOOH/OOH} hoarding enquiry for {city} has been submitted..."
- hoarding_type
- city
- status
- action_url
```

---

### 2. Enhanced VendorDirectEnquiryNotification
**Location**: `Modules\Enquiries\Notifications\VendorDirectEnquiryNotification.php`

**Enhancements**:
- Added hoarding type details (DOOH/OOH)
- Added customer name, phone, email
- Added preferred locations array
- Added source (mobile_app | website)
- Enhanced message to include customer name and hoarding type
- Added type field for notification filtering

**Data Stored**:
```php
- type: 'vendor_direct_enquiry_received'
- enquiry_id
- title: 'New Hoarding Enquiry Received'
- message: "New {DOOH/OOH} enquiry from {customer_name} in {city}"
- customer_name
- customer_phone
- customer_email
- hoarding_type
- city
- locations (preferred_locations)
- status
- source
- action_url
```

---

### 3. Updated DirectEnquiryApiController (Mobile API)
**Location**: `Modules\Enquiries\Controllers\Api\DirectEnquiryApiController.php`

**Changes**:
- ✅ Added push notification to vendors with hoarding type details
- ✅ Enhanced vendor in-app notification with full enquiry details
- ✅ Added push notification to customers (if registered user)
- ✅ Added in-app notification to customers (if registered user)
- ✅ Improved push notification messages to include hoarding types
- ✅ Added logging for notification delivery

**Flow**:
```
1. Customer submits enquiry via mobile API
2. Enquiry is created with hoarding types
3. FOR EACH VENDOR:
   - Send email (VendorDirectEnquiryMail)
   - Send in-app notification (VendorDirectEnquiryNotification)
   - Send push notification with hoarding details
4. IF CUSTOMER IS REGISTERED:
   - Send email confirmation
   - Send in-app notification (CustomerDirectEnquiryNotification)
   - Send push notification with hoarding details
5. Notify admins (email + in-app)
```

**Example Push Notification to Vendor**:
```
Title: "New Hoarding Enquiry Received"
Body: "New DOOH, OOH enquiry from John Doe in Mumbai"
Data: {
  type: 'vendor_direct_enquiry',
  enquiry_id: 123,
  customer_name: 'John Doe',
  hoarding_type: 'DOOH,OOH',
  city: 'Mumbai',
  source: 'mobile_app'
}
```

**Example Push Notification to Customer**:
```
Title: "Enquiry Submitted Successfully"
Body: "Your DOOH, OOH hoarding enquiry for Mumbai has been submitted."
Data: {
  type: 'customer_direct_enquiry',
  enquiry_id: 123,
  hoarding_type: 'DOOH,OOH',
  city: 'Mumbai',
  status: 'submitted'
}
```

---

### 4. Updated DirectEnquiryController (Website)
**Location**: `Modules\Enquiries\Controllers\Web\DirectEnquiryController.php`

**Changes**:
- ✅ Unified notification logic with API controller
- ✅ Added in-app notifications for vendor and customer
- ✅ Enhanced push notifications with hoarding type details
- ✅ Removed manual FCM token handling (uses send() helper which respects preferences)
- ✅ Added logging for all notification events

**Improvements over old code**:
- Respects user notification preferences (`notification_push` field)
- Includes hoarding type in all notifications
- Added customer push notifications (previous version didn't have these)
- Better error logging
- Consistent with API notification pattern

---

## Key Features

### 1. Respects User Preferences
The `send()` helper function automatically checks:
```php
if (!$user->notification_push) {
    return false;  // Skip if user disabled push notifications
}
```

### 2. Hoarding Type Included
All notifications include the hoarding types requested:
- Extracted from `hoarding_type` field (comma-separated array)
- Formatted as "DOOH, OOH" for display
- Stored in notification data for filtering

### 3. In-App Notifications
Both vendor and customer notifications are stored in DB:
- Displayed in user's notification center
- Can be marked as read via API
- Include action URLs for quick access
- Support notification history

### 4. Push Notifications
Sent via FCM service with:
- Custom title and body with business context
- Structured data payload for app routing
- Respects notification preferences
- Logged for tracking

---

## Database Impact

### Notifications Table (Laravel's built-in)
```sql
- Vendor notifications stored with type 'vendor_direct_enquiry_received'
- Customer notifications stored with type 'customer_direct_enquiry_submitted'
- Data includes enquiry_id, hoarding_type, city, etc.
```

### No Schema Changes Required
Implementation uses:
- Laravel's built-in `notifications` table
- Existing FCM infrastructure via `send()` helper
- No new tables or migrations needed

---

## Testing Checklist

### API Endpoint: POST /api/v1/enquiries
```bash
# 1. Test vendor receives push notification
# - Check FCM logs for push sent to vendor
# - Check notifications table for in-app entry

# 2. Test customer receives push notification (if registered)
# - Create enquiry with registered customer email
# - Check FCM logs for push sent to customer
# - Check notifications table for in-app entry

# 3. Test notification preferences
# - Disable push notifications for vendor
# - Submit enquiry
# - Verify no push sent, but in-app notification still received

# 4. Test hoarding type in notifications
# - Submit enquiry with ['DOOH', 'OOH']
# - Verify notification message includes hoarding types
```

### Website Endpoint: POST /enquiry/direct
```bash
# Same tests as API endpoint
# Verify both mobile_app and website source are handled
```

---

## Notification Flow Diagram

```
DirectEnquiry Created
    ↓
Find Matching Vendors
    ↓
FOR EACH VENDOR:
    ├─→ Email (VendorDirectEnquiryMail)
    ├─→ In-App Notification (VendorDirectEnquiryNotification)
    └─→ Push Notification (send() helper)
    ↓
IF CUSTOMER IS REGISTERED USER:
    ├─→ Email Confirmation (UserDirectEnquiryConfirmation)
    ├─→ In-App Notification (CustomerDirectEnquiryNotification)
    └─→ Push Notification (send() helper)
    ↓
FOR EACH ADMIN:
    ├─→ Email (AdminDirectEnquiryMail)
    └─→ In-App Notification (AdminDirectEnquiryNotification)
    ↓
Cleanup: Delete OTP records
```

---

## Files Modified

1. ✅ `Modules\Enquiries\Controllers\Api\DirectEnquiryApiController.php`
   - Added push notifications to vendors and customers
   - Enhanced in-app notifications with hoarding details
   - Added import for CustomerDirectEnquiryNotification

2. ✅ `Modules\Enquiries\Controllers\Web\DirectEnquiryController.php`
   - Unified notification logic with API controller
   - Added push and in-app notifications for customers
   - Removed manual FCM token handling
   - Added import for CustomerDirectEnquiryNotification

3. ✅ `Modules\Enquiries\Notifications\VendorDirectEnquiryNotification.php`
   - Enhanced toArray() method with hoarding type and customer details
   - Added location array field
   - Improved message formatting with hoarding types

## Files Created

1. ✅ `Modules\Enquiries\Notifications\CustomerDirectEnquiryNotification.php`
   - New notification class for customer enquiry confirmation
   - Database channel only
   - Includes hoarding type and city information

---

## Future Enhancements

1. **SMS Notifications**: Send SMS to vendor and customer
2. **WhatsApp Notifications**: Send WhatsApp message with enquiry summary
3. **Email Template Variables**: Include hoarding quantity/specifications
4. **Scheduled Reminders**: Send follow-up reminders if vendor doesn't respond
5. **Notification Analytics**: Track which notification channels are most effective
6. **Bulk Operations**: Send notifications to multiple vendors simultaneously via jobs

---

## Notes

- Hoarding quantity/specifications are included in the request as `hoarding_type` array
- For detailed DOOH specifications, use the separate EnquiryItem system
- Push notifications respect user preferences in `notification_push` field
- In-app notifications are always sent (can be controlled separately if needed)
- All notifications are queued for background processing to avoid blocking requests


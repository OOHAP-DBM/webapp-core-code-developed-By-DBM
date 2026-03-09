# Direct Enquiry Notifications - Quick Implementation Summary

## What Was Implemented

### ✅ Push Notifications for Direct Enquiries
- **Vendors**: Receive push notifications when a new enquiry comes in
  - Shows hoarding type (DOOH/OOH) and customer name
  - Includes city and enquiry details
  - Respects vendor's notification preferences

- **Customers**: Receive push notifications when their enquiry is submitted
  - Confirmation with hoarding types and location
  - Includes link to view enquiry details
  - Only sent if customer is a registered user

### ✅ In-App Notifications for Direct Enquiries
- **Vendors**: Database notifications in notification center
  - Shows new enquiry summary with customer info
  - Includes hoarding requirements
  - Quick navigation to enquiry details

- **Customers**: Database notifications in notification center
  - Confirmation of submitted enquiry
  - Shows hoarding types and city
  - Link to track enquiry status

### ✅ Hoarding Quantity/Type Details
All notifications include:
- Hoarding types (DOOH/OOH)
- Customer information (name, phone, email for vendors)
- City and preferred locations
- Enquiry source (mobile_app or website)

---

## Files Modified

1. **API Controller** (`Modules\Enquiries\Controllers\Api\DirectEnquiryApiController.php`)
   - Added push notifications to vendors
   - Added push notifications to customers (if registered)
   - Enhanced in-app notifications with hoarding details

2. **Web Controller** (`Modules\Enquiries\Controllers\Web\DirectEnquiryController.php`)
   - Same push notification implementation as API
   - Unified notification logic
   - Added customer notifications

3. **Vendor Notification** (`Modules\Enquiries\Notifications\VendorDirectEnquiryNotification.php`)
   - Enhanced with hoarding type, customer name, email, phone
   - Added locations array
   - Improved message formatting

4. **New Customer Notification** (`Modules\Enquiries\Notifications\CustomerDirectEnquiryNotification.php`)
   - Created new notification class for customer confirmations
   - Shows hoarding types and city
   - Includes enquiry reference

---

## How It Works

### When Customer Submits Enquiry (Mobile API or Website)

```
1. DirectEnquiry record created with:
   - hoarding_type: 'DOOH,OOH' (comma-separated)
   - location_city: 'Mumbai'
   - customer name, phone, email

2. System finds matching vendors

3. FOR EACH VENDOR:
   ├─ Send EMAIL (already existed)
   ├─ Send IN-APP NOTIFICATION
   │  └ Message: "New DOOH, OOH enquiry from John Doe in Mumbai"
   └─ Send PUSH NOTIFICATION
      └ Title: "New Hoarding Enquiry Received"
      └ Body: "New DOOH, OOH enquiry from John Doe in Mumbai"

4. IF CUSTOMER IS REGISTERED:
   ├─ Send EMAIL confirmation (already existed)
   ├─ Send IN-APP NOTIFICATION
   │  └ Message: "Your DOOH, OOH hoarding enquiry for Mumbai has been submitted"
   └─ Send PUSH NOTIFICATION
      └ Title: "Enquiry Submitted Successfully"
      └ Body: "Your DOOH, OOH hoarding enquiry for Mumbai has been submitted"

5. NOTIFY ADMINS (already existed)
```

---

## Push Notification Examples

### Vendor Push Notification
```json
{
  "title": "New Hoarding Enquiry Received",
  "body": "New DOOH, OOH enquiry from John Doe in Mumbai",
  "data": {
    "type": "vendor_direct_enquiry",
    "enquiry_id": "123",
    "customer_name": "John Doe",
    "hoarding_type": "DOOH,OOH",
    "city": "Mumbai",
    "source": "mobile_app"
  }
}
```

### Customer Push Notification
```json
{
  "title": "Enquiry Submitted Successfully",
  "body": "Your DOOH, OOH hoarding enquiry for Mumbai has been submitted.",
  "data": {
    "type": "customer_direct_enquiry",
    "enquiry_id": "123",
    "hoarding_type": "DOOH,OOH",
    "city": "Mumbai",
    "status": "submitted"
  }
}
```

---

## In-App Notification Examples

### Vendor In-App Notification
```
Title: "New Hoarding Enquiry Received"
Message: "New DOOH, OOH enquiry from John Doe in Mumbai."

Details stored:
- customer_name: "John Doe"
- customer_phone: "98765XXXXX"
- customer_email: "john@example.com"
- hoarding_type: "DOOH,OOH"
- city: "Mumbai"
- locations: ["Andheri", "Bandra"]
```

### Customer In-App Notification
```
Title: "Enquiry Submitted Successfully"
Message: "Your DOOH, OOH hoarding enquiry for Mumbai has been submitted. 
         Vendors will contact you within 24-48 hours."

Details stored:
- hoarding_type: "DOOH,OOH"
- city: "Mumbai"
- status: "new"
```

---

## Testing the Implementation

### Test Case 1: Mobile API Submission
```bash
POST /api/v1/enquiries

Request:
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "9876543210",
  "hoarding_type": ["DOOH", "OOH"],
  "location_city": "Mumbai",
  "preferred_locations": ["Andheri", "Bandra"],
  "remarks": "Need premium hoarding spaces in high traffic areas",
  "preferred_modes": ["Call", "WhatsApp"],
  "phone_verified": true
}

Expected:
1. Vendors receive push notification: "New DOOH, OOH enquiry from John Doe in Mumbai"
2. Vendors see in-app notification with customer details
3. If John is a registered customer:
   - Receives push: "Your DOOH, OOH hoarding enquiry for Mumbai has been submitted"
   - Receives in-app notification confirming submission
4. Admins notified via email and in-app
```

### Test Case 2: Website Submission
```bash
POST /enquiry/direct (website form)

Same flow as API but source = 'website'
```

### Test Case 3: Verify Hoarding Details
- Open vendor notification → Should show "New DOOH, OOH enquiry"
- Open customer notification → Should show "DOOH, OOH hoarding enquiry"
- Hoarding types must be formatted as uppercase

### Test Case 4: Notification Preferences
- Disable push notifications on vendor account
- Submit enquiry
- Vendor should NOT receive push notification
- Vendor SHOULD still receive email and in-app notification
- Check: `user.notification_push = false` → No push sent

---

## Key Features

✅ **Respects User Preferences**
- The `send()` helper checks `notification_push` field before sending
- Can be disabled per user

✅ **Hoarding Type Included**
- All notifications show DOOH/OOH in readable format
- Data payload includes raw hoarding_type for filtering

✅ **Works for Both Sources**
- Mobile API: source = 'mobile_app'
- Website: source = 'website'

✅ **Customer Aware**
- Only sends customer notifications if registered user exists
- Graceful handling if customer email doesn't match any user

✅ **Comprehensive Logging**
- All notification events are logged
- Can track vendor, customer, and admin notifications

---

## Notification Preferences

Users can control notifications via:
```
settings → Notification Preferences
or
API: PUT /api/v1/notifications/preferences
```

Controls:
- `notification_email`: Email notifications on/off
- `notification_push`: Push notifications on/off
- `notification_whatsapp`: WhatsApp notifications on/off (future)

---

## Database Changes

**No migration required!**

Uses existing Laravel notifications table:
```sql
CREATE TABLE notifications (
  id uuid PRIMARY KEY,
  type VARCHAR(255),
  notifiable_type VARCHAR(255),
  notifiable_id BIGINT,
  data JSON,
  read_at TIMESTAMP NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

Records are stored automatically when `$user->notify()` is called.

---

## Next Steps

1. **Test the implementation** in your development environment
2. **Verify FCM push notifications** are being sent to vendor/customer
3. **Check in-app notifications** appear in notification center
4. **Monitor logs** for any issues
5. **Configure notification preferences** for test users

---

## Support Resources

- Notification System Docs: `/docs/NOTIFICATION_SYSTEM.md`
- Direct Enquiry Notifications Guide: `/docs/DIRECT_ENQUIRY_NOTIFICATIONS_GUIDE.md`
- Enquiry Notifications Service: `Modules\Enquiries\Services\EnquiryNotificationService.php`


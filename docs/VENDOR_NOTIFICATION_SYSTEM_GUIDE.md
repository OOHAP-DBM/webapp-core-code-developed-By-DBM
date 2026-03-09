# Direct Enquiry Notification Flow - Verified Implementation

## Current Status: ✅ WORKING

The system is fully implemented to send **both push notifications and in-app notifications** to vendors when a customer submits a direct enquiry.

---

## Notification Flow Diagram

```
CUSTOMER SUBMITS DIRECT ENQUIRY
    ↓
    ├─ MOBILE APP (Customer App) → /api/v1/enquiries
    │  or
    └─ WEBSITE → /enquiry/direct
    ↓
SYSTEM FINDS MATCHING VENDORS
    ↓
FOR EACH VENDOR:
    │
    ├─ 📧 EMAIL NOTIFICATION
    │  └─ Sent to vendor email
    │
    ├─ 📱 IN-APP NOTIFICATION (Web Dashboard)
    │  └─ Stored in 'notifications' table
    │  └─ Channel: 'database'
    │  └─ Type: 'vendor_direct_enquiry_received'
    │  └─ Shows in vendor's notification center
    │
    └─ 🔔 PUSH NOTIFICATION (Mobile App)
       └─ Sent via FCM (Firebase Cloud Messaging)
       └─ Title: "New Hoarding Enquiry Received"
       └─ Body: "New {HOARDING_TYPE} enquiry from {CUSTOMER_NAME} in {CITY}"
       └─ Received on vendor's mobile device
```

---

## What Vendor Receives

### 1️⃣ IN-APP NOTIFICATION (Web Dashboard)

**Stored in Database** - Visible in vendor's notification center

```
Title: "New Hoarding Enquiry Received"
Message: "New DOOH, OOH enquiry from John Doe in Mumbai"

Full Details:
{
  'type' => 'vendor_direct_enquiry_received',
  'enquiry_id' => 123,
  'title' => 'New Hoarding Enquiry Received',
  'message' => 'New DOOH, OOH enquiry from John Doe in Mumbai',
  'customer_name' => 'John Doe',
  'customer_phone' => '9876543210',
  'customer_email' => 'john@example.com',
  'hoarding_type' => 'DOOH,OOH',
  'city' => 'Mumbai',
  'locations' => ['Andheri', 'Bandra'],
  'status' => 'new',
  'source' => 'mobile_app' | 'website',
  'action_url' => '/vendor/direct-enquiries',
  'created_at' => '2026-03-09 10:30:00'
}
```

**Where Vendor Sees It**:
- Web Dashboard → Notifications Bell Icon
- Notification Center dropdown
- Mark as read / Delete options available

---

### 2️⃣ PUSH NOTIFICATION (Mobile App)

**Sent via FCM** - Appears on vendor's mobile device

```
Notification:
┌─────────────────────────────────────┐
│ New Hoarding Enquiry Received       │
│                                     │
│ New DOOH, OOH enquiry from          │
│ John Doe in Mumbai                  │
│                                     │
│ [View] [Dismiss]                    │
└─────────────────────────────────────┘
```

**Data Payload**:
```json
{
  "type": "vendor_direct_enquiry",
  "enquiry_id": "123",
  "customer_name": "John Doe",
  "hoarding_type": "DOOH,OOH",
  "city": "Mumbai",
  "source": "mobile_app"
}
```

**Where Vendor Sees It**:
- Mobile device notification tray
- App notification bell when app is open
- Can tap to navigate to enquiry details

---

## Implementation Details

### Code Path 1: Mobile App → API Controller

**File**: `Modules\Enquiries\Controllers\Api\DirectEnquiryApiController.php` (Line 177-203)

```php
foreach ($vendors as $vendor) {
    // ✅ EMAIL NOTIFICATION
    Mail::to($vendor->email)->queue(new VendorDirectEnquiryMail($enquiry, $vendor));

    // ✅ IN-APP NOTIFICATION (Web Dashboard)
    $vendor->notify(new VendorDirectEnquiryNotification($enquiry));

    // ✅ PUSH NOTIFICATION (Mobile App)
    send(
        $vendor,
        'New Hoarding Enquiry Received',
        "New {$hoardingTypes} enquiry from {$enquiry->name} in {$normalizedCity}",
        [
            'type'           => 'vendor_direct_enquiry',
            'enquiry_id'     => $enquiry->id,
            'customer_name'  => $enquiry->name,
            'hoarding_type'  => implode(',', $request->hoarding_type),
            'city'           => $normalizedCity,
            'source'         => 'mobile_app'
        ]
    );
}
```

---

### Code Path 2: Website Form → Web Controller

**File**: `Modules\Enquiries\Controllers\Web\DirectEnquiryController.php` (Line 327-352)

```php
foreach ($vendors as $vendor) {
    // ✅ EMAIL NOTIFICATION
    Mail::to($vendor->email)->queue(
        new VendorDirectEnquiryMail($enquiry, $vendor)
    );

    // ✅ IN-APP NOTIFICATION (Web Dashboard)
    $vendor->notify(new VendorDirectEnquiryNotification($enquiry));

    // ✅ PUSH NOTIFICATION (Mobile App)
    send(
        $vendor,
        'New Hoarding Enquiry Received',
        "New {$hoardingTypes} enquiry from {$enquiry->name} in {$normalizedCity}",
        [
            'type'           => 'vendor_direct_enquiry',
            'enquiry_id'     => $enquiry->id,
            'customer_name'  => $enquiry->name,
            'hoarding_type'  => implode(',', $data['hoarding_type']),
            'city'           => $normalizedCity,
            'source'         => 'website'
        ]
    );

    Log::info('Vendor notified of direct enquiry', [
        'vendor_id' => $vendor->id,
        'enquiry_id' => $enquiry->id,
    ]);
}
```

---

## Notification Channels Explained

### 📧 EMAIL Channel
- **Status**: ✅ Queued for background delivery
- **Recipient**: vendor.email
- **Template**: VendorDirectEnquiryMail
- **Timing**: Asynchronous (Laravel Queue)

### 📱 IN-APP (Web) Channel
- **Status**: ✅ Stored in `notifications` database table
- **Type**: Database notification via `$vendor->notify()`
- **Access**: Web dashboard notification center
- **Channels**: ['database']
- **Visible To**: Vendor viewing web dashboard

### 🔔 PUSH (Mobile) Channel
- **Status**: ✅ Sent via `send()` helper → FCM
- **Provider**: Firebase Cloud Messaging
- **Recipient**: Vendor's mobile device
- **Requirements**: FCM token stored in `users.fcm_token`
- **Respects**: `users.notification_push` preference
- **Visible To**: Vendor with mobile app installed

---

## Key Features

### ✅ Hoarding Details Included
All notifications include:
- Hoarding types: `DOOH`, `OOH`, or both
- Customer name: "John Doe"
- Customer contact: Phone & Email
- Location: City & preferred areas
- Source: mobile_app or website

### ✅ Works for Both Vendors & Customers
- Vendor receives notifications when customer submits enquiry
- Customer receives notifications when enquiry is submitted (if registered)
- Both receive in-app + push notifications

### ✅ Notification Preferences Respected
The `send()` helper automatically checks:
```php
if (!$vendor->notification_push) {
    return false;  // User disabled push notifications
}
```

Vendor does NOT receive push if they disabled it, but still gets email + in-app.

### ✅ Comprehensive Logging
All notification events are logged:
```php
Log::info('Vendor notified of direct enquiry', [
    'vendor_id' => $vendor->id,
    'enquiry_id' => $enquiry->id,
]);
```

---

## Testing Scenarios

### Scenario 1: When Vendor is Online

**Vendor opens web dashboard**:
1. ✅ Immediately sees notification bell update
2. ✅ Clicks bell to view in-app notification
3. ✅ Sees customer details (name, phone, email)
4. ✅ Clicks notification to view full enquiry

**Vendor has mobile app open**:
1. ✅ App receives push notification via FCM
2. ✅ Shows notification banner with hoarding type
3. ✅ Can tap to open enquiry details
4. ✅ Notification persists in app notification history

---

### Scenario 2: When Vendor is Offline

**Vendor's computer**:
1. ✅ Email received in inbox
2. ✅ When they next login to web dashboard, in-app notification appears
3. ✅ Cannot receive push (no FCM connection when offline)

**Vendor's mobile**:
1. ✅ Push notification stored by FCM
2. ✅ Appears in notification tray when next online
3. ✅ Can open when they check phone

---

## Database Tables Involved

### notifications (Laravel Built-in)
```sql
id (UUID)
type: 'vendor_direct_enquiry_received'
notifiable_type: 'App\Models\User'
notifiable_id: {vendor_id}
data: JSON (contains all notification details)
read_at: nullable
created_at
updated_at
```

### users (Existing)
```sql
id
fcm_token: 'token_for_push_notifications'
notification_push: boolean (1 = enabled, 0 = disabled)
notification_email: boolean
```

### direct_web_enquiries (Existing)
```sql
id
name: 'John Doe'
email: 'john@example.com'
phone: '9876543210'
hoarding_type: 'DOOH,OOH'
location_city: 'Mumbai'
preferred_locations: JSON array
status: 'new'
source: 'mobile_app' | 'website'
```

---

## Verification Checklist

- ✅ **Email Notification**: Check vendor email inbox
- ✅ **In-App Notification**: Check vendor web dashboard notification bell
- ✅ **Push Notification**: Check Firebase Console or mobile device notification tray
- ✅ **Hoarding Type**: Verify "DOOH, OOH" appears in all notifications
- ✅ **Customer Details**: Verify name, phone, email shown
- ✅ **Source Tracking**: Verify source = 'mobile_app' (API) or 'website'
- ✅ **Logging**: Check `storage/logs/laravel.log` for notification events

---

## Log Example

When a vendor receives notification, you'll see in logs:

```
[2026-03-09 10:30:15] local.INFO: Vendor notified of direct enquiry {
  "vendor_id": 42,
  "enquiry_id": 123
}

[2026-03-09 10:30:15] local.INFO: VendorDirectEnquiryNotification toArray called {
  "vendor_id": 42,
  "enquiry_id": 123
}

[2026-03-09 10:30:15] local.INFO: FCM sent to token {
  "response": "success"
}
```

---

## Support & Troubleshooting

### Push Notification Not Arriving?
1. Check vendor's `fcm_token` is saved
2. Verify `notification_push = 1` in users table
3. Check Firebase console for delivery status
4. Ensure vendor has mobile app installed

### In-App Notification Not Showing?
1. Check vendor is logged into web dashboard
2. Verify `notifications` table has entry
3. Check notification type = 'vendor_direct_enquiry_received'
4. Click notification bell to refresh

### Email Not Sending?
1. Check Laravel mail configuration
2. Check queue is running: `php artisan queue:work`
3. View queued jobs in `jobs` table
4. Check `storage/logs/laravel.log` for errors

---

## Summary

✅ **System is fully functional!**

When a customer submits a direct enquiry (via mobile app or website):
- Vendors receive 📧 **Email** immediately
- Vendors receive 📱 **In-App Notification** (web dashboard)  
- Vendors receive 🔔 **Push Notification** (mobile app)

All notifications include hoarding types, customer details, and location information.


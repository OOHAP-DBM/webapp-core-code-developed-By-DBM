# Direct Enquiry Notification System - Verification & Testing

## Quick Verification Checklist

### ✅ Step 1: Verify Notification Classes Exist

```bash
# Check all notification files exist
ls -la Modules/Enquiries/Notifications/
```

Expected files:
- ✅ `VendorDirectEnquiryNotification.php` - For vendor notifications
- ✅ `CustomerDirectEnquiryNotification.php` - For customer notifications

---

### ✅ Step 2: Verify Controllers Have Notification Code

**Mobile API Controller** (`Modules/Enquiries/Controllers/Api/DirectEnquiryApiController.php`):
```php
// Line 177-203: Vendor notifications
foreach ($vendors as $vendor) {
    // ✅ Email
    Mail::to($vendor->email)->queue(new VendorDirectEnquiryMail($enquiry, $vendor));
    
    // ✅ In-App Notification
    $vendor->notify(new VendorDirectEnquiryNotification($enquiry));
    
    // ✅ Push Notification
    send($vendor, 'New Hoarding Enquiry Received', ...);
}
```

**Website Controller** (`Modules/Enquiries/Controllers/Web/DirectEnquiryController.php`):
```php
// Line 327-352: Same structure as API controller
foreach ($vendors as $vendor) {
    // ✅ Email
    Mail::to($vendor->email)->queue(...);
    
    // ✅ In-App
    $vendor->notify(new VendorDirectEnquiryNotification($enquiry));
    
    // ✅ Push
    send($vendor, 'New Hoarding Enquiry Received', ...);
}
```

---

### ✅ Step 3: Database Requirements

**Required Tables**:
- ✅ `users` table - Must have:
  - `fcm_token` (nullable) - For push notifications
  - `notification_push` (boolean) - To control if push enabled

- ✅ `notifications` table - Laravel built-in table

- ✅ `direct_web_enquiries` table - For enquiry storage

**Verify Tables**:
```bash
# Check if columns exist
php artisan tinker

>>> Schema::getColumnListing('users')
# Should include: fcm_token, notification_push, notification_email

>>> Schema::getColumnListing('notifications')
# Should include: id, type, notifiable_type, notifiable_id, data, read_at

>>> Schema::getColumnListing('direct_web_enquiries')
# Should include: hoarding_type, location_city, preferred_locations, source
```

---

### ✅ Step 4: API Testing

#### Test Mobile App Submission

```bash
curl -X POST http://localhost/api/v1/enquiries \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Customer",
    "email": "customer@example.com",
    "phone": "9876543210",
    "hoarding_type": ["DOOH", "OOH"],
    "location_city": "Mumbai",
    "preferred_locations": ["Andheri", "Bandra"],
    "remarks": "Test enquiry for notification verification",
    "preferred_modes": ["Call", "WhatsApp"],
    "phone_verified": true
  }'
```

**Expected Response**:
```json
{
  "success": true,
  "message": "Enquiry submitted successfully!...",
  "data": {
    "enquiry_id": 123
  }
}
```

---

### ✅ Step 5: Verify Notifications Created

After submission, check database:

```bash
php artisan tinker

# Check vendor received notification
>>> DB::table('notifications')
     ->where('type', 'vendor_direct_enquiry_received')
     ->latest()
     ->first();

# Should show fresh notification with data containing:
# - enquiry_id: 123
# - customer_name: "Test Customer"
# - hoarding_type: "DOOH,OOH"
# - city: "Mumbai"
```

---

### ✅ Step 6: Verify Logs

Check `storage/logs/laravel.log` for:

```
[2026-03-09 10:30:15] local.INFO: Vendor notified of direct enquiry {
  "vendor_id": 3,
  "enquiry_id": 123
}

[2026-03-09 10:30:15] local.INFO: VendorDirectEnquiryNotification toArray called {
  "vendor_id": 3,
  "enquiry_id": 123
}

[2026-03-09 10:30:15] local.INFO: FCM sent to token {
  "response": "success"
}
```

---

## Manual Testing Guide

### Test 1: Web Dashboard Notification

**Steps**:
1. Login as Vendor in web dashboard
2. Submit direct enquiry as customer (via website or API)
3. Click notification bell icon
4. Verify notification appears with:
   - ✅ Title: "New Hoarding Enquiry Received"
   - ✅ Message: Shows hoarding type, customer name, city
   - ✅ Customer phone & email in details
   - ✅ Action link to enquiry

---

### Test 2: Mobile Push Notification

**Steps**:
1. Have vendor's mobile app running
2. Submit direct enquiry (via API)
3. Check mobile notification tray
4. Verify notification:
   - ✅ Title: "New Hoarding Enquiry Received"
   - ✅ Body: "New DOOH, OOH enquiry from {name} in {city}"
   - ✅ Tap to open enquiry details

---

### Test 3: Email Notification

**Steps**:
1. Submit direct enquiry
2. Check vendor's email inbox
3. Verify email:
   - ✅ Subject: "New Hoarding Enquiry in {city}"
   - ✅ Contains customer details
   - ✅ Shows hoarding requirements
   - ✅ Action button to view enquiry

---

### Test 4: Notification Preferences

**Steps**:
1. Login as vendor
2. Go to Notification Preferences
3. Disable "Push Notifications"
4. Submit direct enquiry
5. Verify:
   - ✅ No push notification sent
   - ✅ Email STILL sent
   - ✅ In-app notification STILL shown

---

## Common Issues & Fixes

### Issue: Push Notification Not Received

**Cause 1**: FCM token not set
```bash
# Check vendor's FCM token
php artisan tinker
>>> $vendor = App\Models\User::find(3);
>>> dd($vendor->fcm_token);  # Should not be null
```

**Fix**:
- Ensure mobile app is configured to send FCM token to backend
- Clear app cache and reinstall if needed
- Check Firebase Console for invalid tokens

---

**Cause 2**: Push notifications disabled
```bash
# Check notification preference
>>> $vendor->notification_push;  # Should be 1 (true)
```

**Fix**:
- Vendor Settings → Enable "Push Notifications"
- Or update in DB: `UPDATE users SET notification_push = 1 WHERE id = 3;`

---

### Issue: In-App Notification Not Showing

**Cause**: Notification not in database
```bash
# Check if notification was created
>>> DB::table('notifications')
     ->where('notifiable_id', 3)
     ->latest()
     ->first();
```

**Fix**:
- Check controller code is calling `$vendor->notify(...)`
- Verify VendorDirectEnquiryNotification has `via(['database'])`
- Restart queue worker if notifications are queued

---

### Issue: Email Not Sent

**Cause 1**: Queue not processing
```bash
# Check if queue jobs exist
>>> DB::table('jobs')->count();  # Should be 0 when queue running
```

**Fix**:
```bash
# Start queue worker
php artisan queue:work

# Or use supervisor to keep it running
```

---

## Monitoring Dashboard

### Real-time Notification Tracking

Use this command to monitor notifications:

```bash
# Watch notifications in real-time
php artisan tinker

# Monitor vendor notifications
>>> while(true) {
      $count = DB::table('notifications')
               ->where('type', 'vendor_direct_enquiry_received')
               ->whereNull('read_at')
               ->count();
      echo "Unread vendor notifications: $count\n";
      sleep(5);
    }
```

---

## Performance Considerations

### ✅ Notifications are Queued

```php
// In VendorDirectEnquiryNotification
use Illuminate\Contracts\Queue\ShouldQueue;

class VendorDirectEnquiryNotification extends Notification implements ShouldQueue
{
    // Won't block API response
}
```

**By default**, notifications are processed asynchronously via queue.

### Queue Configuration

Check `config/queue.php`:
```php
'default' => env('QUEUE_CONNECTION', 'database'),
// or 'redis', 'sync' (for testing only)
```

---

## Production Checklist

Before deploying to production:

- ✅ Queue driver configured (Redis or Database)
- ✅ Queue worker running: `php artisan queue:work --daemon`
- ✅ FCM credentials configured in Firebase Console
- ✅ SMS/Email service configured
- ✅ Supervisor configured to restart queue worker on failure
- ✅ Monitor queue jobs: `php artisan queue:failed`
- ✅ Setup notification preferences page for users
- ✅ Test push notifications with real devices
- ✅ Monitor logs for notification failures
- ✅ Setup alerts for queued failures

---

## Summary

The notification system is implemented with:
- ✅ **Email notifications** (Via Mail gateway)
- ✅ **In-app notifications** (Via database channel)
- ✅ **Push notifications** (Via FCM)

All three channels are sent when a direct enquiry is submitted, ensuring vendors don't miss any opportunities!


# Push Notifications for Hoarding Creation & Updates

## Implementation Summary

This guide documents the implementation of push notifications for vendors when they create or update OOH and DOOH hoardings.

---

## What Was Implemented

### 1. **New Notification Class**
- **File**: `app/Notifications/HoardingCreatedOrUpdatedNotification.php`
- **Purpose**: Sends in-app database notifications when a hoarding is created or updated
- **Features**:
  - Supports both 'created' and 'updated' actions
  - Supports both 'OOH' and 'DOOH' hoarding types
  - Includes hoarding details (title, address, type)
  - Provides action URL to view the hoarding

### 2. **OOH Hoarding Notifications**

#### Step 1 Creation (`storeStep1`)
- **File**: `Modules/Hoardings/Services/HoardingListService.php`
- **Notification Types**:
  - ✅ In-app database notification
  - ✅ Push notification via FCM (Firebase Cloud Messaging)
- **Message**: `"Your OOH hoarding '[title]' has been created successfully."`
- **Push Details**:
  ```
  {
    'type': 'vendor_hoarding_created',
    'hoarding_id': <id>,
    'hoarding_type': 'OOH',
    'action': 'created'
  }
  ```

#### Step 1 Update (`updateStep1`)
- **File**: `Modules/Hoardings/Services/HoardingListService.php`
- **Notification Types**:
  - ✅ In-app database notification
  - ✅ Push notification via FCM
- **Message**: `"Your OOH hoarding '[title]' has been updated successfully."`
- **Push Details**:
  ```
  {
    'type': 'vendor_hoarding_updated',
    'hoarding_id': <id>,
    'hoarding_type': 'OOH',
    'action': 'updated'
  }
  ```

#### Step 2 Update (`storeStep2`)
- **File**: `Modules/Hoardings/Services/HoardingListService.php`
- **Notification Types**:
  - ✅ In-app database notification
  - ✅ Push notification via FCM
- **Trigger**: When visibility, legal, and audience details are updated
- **Message**: `"Your OOH hoarding '[title]' has been updated successfully."`

#### Step 3 Completion (`storeStep3`)
- **Status**: Already implemented with push notifications
- **Notifications**:
  - ✅ Sends to vendor on step 3 completion
  - ✅ Includes approval/publication status

### 3. **DOOH Screen Notifications**

#### Step 1 Creation (`storeStep1`)
- **File**: `Modules/DOOH/Services/DOOHScreenService.php`
- **Notification Types**:
  - ✅ In-app database notification
  - ✅ Push notification via FCM
- **Message**: `"Your DOOH screen '[title]' has been created successfully."`
- **Push Details**:
  ```
  {
    'type': 'vendor_hoarding_created',
    'hoarding_id': <id>,
    'hoarding_type': 'DOOH',
    'action': 'created'
  }
  ```

#### Step 2 Update (`storeStep2`)
- **File**: `Modules/DOOH/Services/DOOHScreenService.php`
- **Notification Types**:
  - ✅ In-app database notification
  - ✅ Push notification via FCM
- **Trigger**: When brand logos, permits, and visibility are updated
- **Message**: `"Your DOOH screen '[title]' has been updated successfully."`

#### Step 3 Completion (`storeStep3`)
- **File**: `Modules/DOOH/Services/DOOHScreenService.php`
- **Updated**: Added push notification (was missing)
- **Message**: `"Your DOOH screen is now active and published."` or `"Your DOOH screen is pending approval."`
- **Push Details**:
  ```
  {
    'type': 'vendor_hoarding',
    'hoarding_id': <id>,
    'hoarding_type': 'DOOH',
    'status': 'active' | 'pending_approval',
    'action': 'completed'
  }
  ```

---

## How It Works

### Notification Flow

```
VENDOR CREATES/UPDATES HOARDING
    ↓
SERVICE PROCESSES REQUEST (storeStep1/storeStep2/updateStep1/etc.)
    ↓
    ├─ DATABASE NOTIFICATION
    │  └─ Stored in 'notifications' table
    │  └─ Visible in vendor notification center
    │  └─ Triggered if vendor->notification_push = true
    │
    └─ PUSH NOTIFICATION (FCM)
       └─ Sent via Firebase Cloud Messaging
       └─ Appears on vendor's mobile device
       └─ Only sent if vendor->fcm_token exists
```

### Prerequisites

1. **FCM Token**: Vendor must have an FCM token stored in `users.fcm_token`
   - Token is typically set during mobile app login
   - See: `Modules/Auth/Http/Controllers/Api/AuthController.php` for token storage

2. **Notification Preferences**: 
   - `vendor->notification_push` must be `true` (default behavior, checks if preference is enabled)
   - `vendor->notification_email` for email notifications

3. **Firebase Configuration**:
   - Ensure Firebase Cloud Messaging credentials are configured
   - Check `.env` for Firebase/FCM settings

---

## Code Examples

### Example 1: OOH Creation Triggers Notification
```php
// User creates OOH hoarding via API
POST /api/v1/hoardings/vendor/ooh/step-1

// Response:
{
  "success": true,
  "message": "Step 1 completed.",
  "data": { ... }
}

// What happens:
// 1. Hoarding is created in database
// 2. In-app notification is sent to vendor (if notification_push = true)
// 3. Push notification is sent to vendor's mobile (if fcm_token exists)
//    Title: "OOH Hoarding Created"
//    Body: "Your OOH hoarding '[name]' has been created successfully."
```

### Example 2: DOOH Update Triggers Notification
```php
// Vendor updates DOOH step 2
PUT /api/v1/dooh/vendor/{screenId}/step-2

// What happens:
// 1. Hoarding details are updated
// 2. In-app notification is sent
// 3. Push notification is sent to mobile device
//    Title: "DOOH Screen Updated"
//    Body: "Your DOOH screen '[name]' has been updated successfully."
```

---

## Configuration & Settings

### Check Vendor Notification Settings

```bash
# In tinker/artisan:
>>> $vendor = User::find($vendor_id);
>>> $vendor->fcm_token;        # Should not be null
>>> $vendor->notification_push; # Should be true/1
```

### Disable Notifications Temporarily

```php
// This checks vendor preferences, so you can disable via:
$vendor->update(['notification_push' => false]);
```

### Firebase/FCM Configuration

Add to `.env`:
```
FIREBASE_PROJECT_ID=your_project_id
FIREBASE_PRIVATE_KEY=your_private_key
FIREBASE_CLIENT_EMAIL=your_client_email
```

---

## Testing the Implementation

### 1. **Create OOH Hoarding**
```bash
curl -X POST http://localhost:8000/api/v1/hoardings/vendor/ooh/step-1 \
  -H "Authorization: Bearer <vendor_token>" \
  -F "category=Billboard" \
  -F "address=123 Main St" \
  -F "media=@image.jpg"

# Check vendor's notifications table
>>> Notification::where('notifiable_id', $vendor_id)->latest()->first();
```

### 2. **Update OOH Hoarding (Step 1)**
```bash
curl -X PUT http://localhost:8000/api/v1/vendor/ooh/{id}/step1 \
  -H "Authorization: Bearer <vendor_token>" \
  -F "address=Updated Address"

# Check notification was sent
>>> Notification::where('notifiable_id', $vendor_id)->latest()->first();
```

### 3. **Monitor Push Notifications**
- Check Firebase Console for FCM delivery status
- Check application logs: `storage/logs/laravel.log`
- Search for: `"FCM sent to token"` or `"push notification failed"`

### 4. **Verify in Database**
```sql
-- Check in-app notifications
SELECT * FROM notifications 
WHERE notifiable_id = <vendor_id> 
ORDER BY created_at DESC;

-- Check user's FCM token
SELECT id, name, fcm_token, notification_push 
FROM users 
WHERE id = <vendor_id>;
```

---

## Error Handling

### If Push Notifications Fail

**Logged as**: `"FCM push notification failed for vendor ID {$vendor->id}"`

**Reasons**:
1. Invalid/expired FCM token
2. Firebase credentials not configured
3. User's mobile app has revoked notification permission
4. Network issue with Firebase

**What happens**: In-app notification is still sent (doesn't fail)

### If In-App Notifications Fail

**Logged as**: `"Failed to send [OOH/DOOH] hoarding [creation/update] notification to vendor"`

**Reasons**:
1. Vendor record not found
2. `notification_push` preference is disabled
3. Database error

### Common Issues & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| No notifications | FCM token is null | Ensure vendor logged in via mobile app |
| Notifications don't appear on mobile | Notification permission revoked | Check mobile app settings |
| FCM sends but app doesn't show | Data payload format issue | Check Firebase console delivery status |
| Thousands of notifications queued | Job queue not running | Run `php artisan queue:work` |

---

## Files Modified

1. **New File**: `app/Notifications/HoardingCreatedOrUpdatedNotification.php`
   - New notification class for hoarding creation/update

2. **Modified**: `Modules/Hoardings/Services/HoardingListService.php`
   - Added notifications to `storeStep1()`
   - Added notifications to `updateStep1()`
   - Added notifications to `storeStep2()`
   - Existing notifications in `storeStep3()` remain unchanged

3. **Modified**: `Modules/DOOH/Services/DOOHScreenService.php`
   - Added notifications to `storeStep1()`
   - Added notifications to `storeStep2()`
   - Enhanced `storeStep3()` with push notification (was in-app only)

---

## Features of Implementation

✅ **Vendor Creation Notifications**: Notifies when vendor creates OOH/DOOH  
✅ **Vendor Update Notifications**: Notifies when vendor updates hoarding details  
✅ **In-App Notifications**: Stored in database, visible in notification center  
✅ **Push Notifications**: Sent via FCM to mobile devices  
✅ **Error Handling**: Graceful failure with logging  
✅ **Respects Preferences**: Only sends if `notification_push = true`  
✅ **Supports Both Types**: Works for OOH and DOOH hoardings  
✅ **All Steps Covered**: Notifications for Step 1, 2 updates + Step 3 completion  

---

## Rollback Instructions

If you need to revert these changes:

```bash
# Revert service files
git checkout Modules/Hoardings/Services/HoardingListService.php
git checkout Modules/DOOH/Services/DOOHScreenService.php

# Remove new notification class
rm app/Notifications/HoardingCreatedOrUpdatedNotification.php
```

---

## Future Enhancements

- [ ] Add email notifications for hoarding creation
- [ ] Add notification for admin when hoarding is created
- [ ] Add notification history/archiving
- [ ] Add notification templating for customization
- [ ] Add webhook support for hoarding events

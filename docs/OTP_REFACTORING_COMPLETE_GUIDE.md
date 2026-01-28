# OTP Implementation Refactoring - Complete Guide

## Overview

This document details the refactoring of OTP (One-Time Password) implementation to use the `user_otps` table and Twilio integration instead of custom OTP storage in the `users` table.

## Architecture Changes

### Before (Incorrect Approach)
```
users table:
├── mobile_otp (string)
├── mobile_otp_expires_at (timestamp)
├── mobile_otp_attempts (integer)
└── mobile_otp_last_sent_at (timestamp)

vendor_emails table:
├── otp (string)
├── otp_expires_at (timestamp)
├── otp_attempts (integer)
└── otp_last_sent_at (timestamp)

Services:
├── MobileOTPService (custom logic)
└── EmailVerificationService (custom logic)
```

### After (Correct Approach)
```
users table:
├── email
├── email_verified_at (timestamp)
├── phone
└── phone_verified_at (timestamp)

vendor_emails table:
├── user_id (foreign key)
├── email
├── verified_at (timestamp)
└── is_primary (boolean)

user_otps table (centralized):
├── user_id (foreign key)
├── identifier (email or phone number)
├── otp_hash (hashed OTP)
├── purpose ('vendor_email_verification', 'mobile_verification', etc.)
├── expires_at (timestamp)
└── verified_at (timestamp)

Services:
├── MobileOTPService (wraps user_otps with Twilio SMS)
└── EmailVerificationService (wraps user_otps with Laravel Mail)
```

## Key Benefits

1. **Single Source of Truth**: All OTPs in `user_otps` table
2. **Multi-Purpose Support**: Same table handles different OTP purposes
3. **Proper SMS Integration**: Twilio SMS delivery for mobile OTP
4. **Scalability**: No table bloat with new OTP fields
5. **Auditability**: Centralized OTP history and verification tracking
6. **Security**: OTP hash instead of plaintext in database

## Files Modified

### 1. Services

#### `app/Services/MobileOTPService.php` ✅ REFACTORED
**Changes:**
- Uses `user_otps` table instead of `users` table
- Twilio integration for SMS sending
- 6-digit OTP generation
- 10-minute expiry time
- Rate limiting (60 seconds between resends)
- Methods: `sendOTP()`, `verifyOTP()`, `resendOTP()`, `isMobileVerified()`

**Usage:**
```php
// Send OTP to vendor's phone
$mobileOTPService->sendOTP($vendor, 'mobile_verification');

// Verify OTP
$mobileOTPService->verifyOTP($vendor, $otp, 'mobile_verification');
```

#### `app/Services/EmailVerificationService.php` ✅ REFACTORED
**Changes:**
- Uses `user_otps` table for OTP storage
- Supports both primary email (users.email) and secondary emails (vendor_emails)
- Mail notification for OTP delivery
- 15-minute expiry time
- Methods: `sendOTP()`, `verifyOTP()`, `resendOTP()`, `getPendingEmails()`, `getVerifiedEmails()`

**Usage:**
```php
// Send OTP to new email
$emailService->sendOTP($vendor, 'new@example.com', 'vendor_email_verification');

// Verify OTP
$emailService->verifyOTP($vendor, 'new@example.com', $otp, 'vendor_email_verification');
```

### 2. Controllers

#### `app/Http/Controllers/Vendor/MobileOTPController.php` ✅ UPDATED
- Updated to pass `purpose` parameter to service methods
- `sendOTP()`: POST `/vendor/mobile/send-otp`
- `verify()`: POST `/vendor/mobile/verify`
- `resendOTP()`: POST `/vendor/mobile/resend-otp`
- `getStatus()`: GET `/vendor/mobile/status`

#### `app/Http/Controllers/Vendor/EmailVerificationController.php` ✅ REFACTORED
- Refactored to use refactored EmailVerificationService
- Works with both primary email and secondary emails
- `index()`: GET `/vendor/emails` - List all emails
- `store()`: POST `/vendor/emails/add` - Add new email
- `verify()`: POST `/vendor/emails/verify` - Verify email OTP
- `resendOTP()`: POST `/vendor/emails/resend-otp` - Resend OTP
- `destroy()`: DELETE `/vendor/emails` - Remove email
- `getStatus()`: GET `/vendor/emails/status` - Check verification status

#### `app/Http/Controllers/Auth/VendorRegisterController.php` ✅ NEW
- Handles vendor signup flow
- Sends both email and mobile OTP during registration
- Allows vendor to add additional emails after signup
- Email stored in `users.email` during signup
- Additional emails stored in `vendor_emails` table

### 3. Models

#### `app/Models/VendorEmail.php` ✅ UPDATED
**Changes:**
- Foreign key: `vendor_id` → `user_id`
- Column: `email_verified_at` → `verified_at`
- Removed OTP fields: `otp`, `otp_expires_at`, `otp_attempts`, `otp_last_sent_at`
- Removed methods: `generateOTP()`, `verifyOTP()`, `canResendOTP()`, `markAsPrimary()`
- Simple model now: just stores email addresses and verification status

**Relationship:**
```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id');
}
```

#### `app/Models/User.php` (unchanged)
- Already has `email`, `email_verified_at`, `phone`, `phone_verified_at` fields
- Relationships with `VendorEmail` model

#### `app/Models/UserOtp.php` (existing)
- Central OTP storage
- Fields: `user_id`, `identifier`, `otp_hash`, `purpose`, `expires_at`, `verified_at`
- Already in place, no changes needed

### 4. Migrations

#### `database/migrations/2026_01_27_000001_create_vendor_emails_table.php` ✅ UPDATED
**Changes:**
- Foreign key: `vendor_id` → `user_id`
- Removed OTP fields
- Column: `email_verified_at` → `verified_at`
- Added unique constraint: `UNIQUE(user_id, email)`

#### `database/migrations/2026_01_27_000003_add_mobile_otp_to_users_table.php` ❌ DELETED
- This migration is no longer needed
- File deleted from repository

#### `database/migrations/2026_01_27_000004_remove_mobile_otp_from_users_table.php` ✅ NEW
- Safely removes mobile OTP columns if they exist
- Prevents errors if old migration was run
- Drop columns: `mobile_otp`, `mobile_otp_expires_at`, `mobile_otp_attempts`, `mobile_otp_last_sent_at`

## OTP Flow Explanation

### For Mobile Verification (Vendor Signup)

```
1. Vendor registers with phone number
   ↓
2. MobileOTPService.sendOTP($vendor, 'mobile_verification') is called
   ↓
3. 6-digit OTP is generated
   ↓
4. OTP is stored in user_otps table:
   {
     user_id: vendor.id,
     identifier: vendor.phone,
     otp_hash: generated_otp,
     purpose: 'mobile_verification',
     expires_at: now() + 10 minutes,
     verified_at: null
   }
   ↓
5. SMS is sent via Twilio to vendor.phone
   Message: "Your OOH App verification code is: 123456. Valid for 10 minutes."
   ↓
6. Vendor receives SMS and enters OTP
   ↓
7. MobileOTPService.verifyOTP($vendor, $otp, 'mobile_verification') is called
   ↓
8. OTP is validated against user_otps table
   - Check: otp_hash matches
   - Check: expires_at is not past
   - Check: verified_at is null (not already verified)
   ↓
9. On success:
   - user_otps.verified_at = now()
   - users.phone_verified_at = now()
```

### For Email Verification (Vendor Signup)

```
1. Vendor registers with primary email
   ↓
2. EmailVerificationService.sendOTP($vendor, $email, 'vendor_email_verification') is called
   ↓
3. 6-digit OTP is generated
   ↓
4. OTP is stored in user_otps table:
   {
     user_id: vendor.id,
     identifier: email_address,
     otp_hash: generated_otp,
     purpose: 'vendor_email_verification',
     expires_at: now() + 15 minutes,
     verified_at: null
   }
   ↓
5. Email is sent with OTP
   To: vendor@example.com
   Subject: Email Verification - OOH App
   Body: Contains OTP: 123456
   ↓
6. Vendor receives email and enters OTP
   ↓
7. EmailVerificationService.verifyOTP($vendor, $email, $otp, 'vendor_email_verification') is called
   ↓
8. OTP is validated against user_otps table
   - Check: otp_hash matches
   - Check: expires_at is not past
   - Check: verified_at is null (not already verified)
   ↓
9. On success:
   - user_otps.verified_at = now()
   - For primary email (users.email): users.email_verified_at = now()
   - For secondary email: Create/update vendor_emails record with verified_at = now()
```

### For Secondary Email Addition

```
1. Vendor adds new email: another@example.com
   ↓
2. Email is added to vendor_emails table
   ↓
3. EmailVerificationService.sendOTP($vendor, 'another@example.com', 'vendor_email_verification')
   ↓
4. Same OTP flow as above
   ↓
5. vendor_emails record is marked verified_at = now()
```

## Configuration Required

### Twilio Configuration
Ensure `.env` has:
```
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=+1234567890
```

### Mail Configuration
Ensure `.env` has mail driver configured (SMTP, Mailgun, SES, etc.):
```
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@oohapp.com
MAIL_FROM_NAME="OOH App"
```

## API Endpoints

### Mobile OTP
- `POST /vendor/mobile/send-otp` - Send OTP to registered phone
- `POST /vendor/mobile/verify` - Verify mobile OTP
- `POST /vendor/mobile/resend-otp` - Resend OTP
- `GET /vendor/mobile/status` - Get mobile verification status

### Email Verification
- `GET /vendor/emails` - List all emails (verified & pending)
- `POST /vendor/emails/add` - Add new email
- `POST /vendor/emails/verify` - Verify email OTP
- `POST /vendor/emails/resend-otp` - Resend OTP
- `DELETE /vendor/emails` - Remove email
- `GET /vendor/emails/status` - Check email verification status

### Vendor Registration
- `POST /register` - Register new vendor (sends both email and mobile OTP)
- `POST /verify-email` - Verify primary email OTP
- `POST /verify-mobile` - Verify mobile OTP
- `POST /emails/add` - Add additional email (after signup)

## Request/Response Examples

### 1. Register Vendor
```json
// Request
POST /register
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "9876543210",
  "password": "password123",
  "password_confirmation": "password123",
  "business_name": "Doe Advertising"
}

// Response
{
  "success": true,
  "message": "Registration successful. Please verify your email and phone.",
  "vendor_id": 1,
  "email": "john@example.com",
  "phone": "9876543210",
  "next_step": "verify_email_and_phone"
}
```

### 2. Send Mobile OTP
```json
// Request
POST /vendor/mobile/send-otp

// Response
{
  "success": true,
  "message": "OTP sent to your registered mobile number",
  "phone": "9876543210"
}
```

### 3. Verify Mobile OTP
```json
// Request
POST /vendor/mobile/verify
{
  "otp": "123456"
}

// Response
{
  "success": true,
  "message": "Mobile number verified successfully",
  "verified_at": "2024-01-27T10:30:45Z"
}
```

### 4. Add Secondary Email
```json
// Request
POST /vendor/emails/add
{
  "email": "another@example.com"
}

// Response
{
  "success": true,
  "message": "Email added. OTP sent for verification.",
  "email": "another@example.com"
}
```

### 5. Verify Email OTP
```json
// Request
POST /vendor/emails/verify
{
  "email": "another@example.com",
  "otp": "654321"
}

// Response
{
  "success": true,
  "message": "Email verified successfully",
  "email": "another@example.com"
}
```

## Important Notes

1. **OTP Hash**: OTPs are hashed when stored in database for security
2. **Expiry Times**: Mobile OTP expires in 10 minutes, Email OTP in 15 minutes
3. **Rate Limiting**: Users must wait 60 seconds between OTP resends
4. **Purpose Field**: Different purposes can coexist (vendor_email_verification, mobile_verification, etc.)
5. **Single Verification**: Once OTP is verified, `verified_at` is set and cannot be verified again
6. **Primary Email**: Cannot be deleted, stored directly in `users.email`
7. **Secondary Emails**: Stored in `vendor_emails`, can be deleted if other verified emails exist

## Migration Steps

1. Run migration to create `vendor_emails` table (if not exists)
2. Run migration to remove mobile OTP fields from `users` table
3. Existing OTP records in old format can be cleared (one-time operation)
4. Deploy updated services and controllers
5. Test vendor signup and email/mobile verification flows

## Testing Checklist

- [ ] Vendor can register with email and phone
- [ ] Email OTP is sent and can be verified
- [ ] Mobile OTP is sent via Twilio SMS and can be verified
- [ ] Vendor can add secondary email after registration
- [ ] Secondary email OTP verification works correctly
- [ ] Rate limiting prevents too frequent resends
- [ ] Expired OTPs cannot be verified
- [ ] Wrong OTP shows appropriate error
- [ ] Resend functionality works correctly
- [ ] Cannot remove only verified email
- [ ] Primary email cannot be deleted

## Backward Compatibility

The refactoring maintains backward compatibility with:
- Existing user records (phone_verified_at field exists)
- OTP service pattern (same methods, different storage)
- Controller routes (same endpoints, improved logic)

No existing data loss occurs - migration safely handles both old and new structures.

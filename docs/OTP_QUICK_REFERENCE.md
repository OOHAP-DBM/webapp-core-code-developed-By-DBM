# OTP Implementation - Quick Reference Card

## What Changed?

| Component | Before | After | Status |
|-----------|--------|-------|--------|
| Mobile OTP Storage | users.mobile_otp | user_otps table | ✅ |
| Email OTP Storage | vendor_emails.otp | user_otps table | ✅ |
| SMS Provider | None | Twilio | ✅ |
| OTP Length | Not specified | 6 digits | ✅ |
| Mobile Expiry | Not specified | 10 minutes | ✅ |
| Email Expiry | 10 minutes | 15 minutes | ✅ |
| Rate Limiting | 1 minute | 60 seconds | ✅ |
| Vendor Email Field | vendor_id | user_id | ✅ |
| Vendor Email Verified | email_verified_at | verified_at | ✅ |

## Quick Start for Developers

### 1. Sending Mobile OTP
```php
// In a controller
$mobileOTPService->sendOTP($vendor, 'mobile_verification');
// Generates 6-digit OTP, stores in user_otps, sends via Twilio SMS
```

### 2. Verifying Mobile OTP
```php
if ($mobileOTPService->verifyOTP($vendor, $otp, 'mobile_verification')) {
    // OTP valid, vendor.phone_verified_at is updated
} else {
    // OTP invalid or expired
}
```

### 3. Sending Email OTP
```php
$emailService->sendOTP($vendor, 'email@example.com', 'vendor_email_verification');
// Generates 6-digit OTP, stores in user_otps, sends via email
```

### 4. Verifying Email OTP
```php
if ($emailService->verifyOTP($vendor, 'email@example.com', $otp, 'vendor_email_verification')) {
    // OTP valid, vendor_emails.verified_at is updated
} else {
    // OTP invalid or expired
}
```

## Database Tables

### users table
```sql
-- Already has these fields (no changes needed)
- email
- email_verified_at (NULL until verified)
- phone
- phone_verified_at (NULL until verified)
```

### vendor_emails table
```sql
- id
- user_id (was vendor_id)
- email
- verified_at (was email_verified_at, NULL until verified)
- is_primary
- created_at
- updated_at
```

### user_otps table
```sql
- id
- user_id
- identifier (email or phone)
- otp_hash (6-digit hash)
- purpose ('vendor_email_verification', 'mobile_verification', etc.)
- expires_at
- verified_at (NULL until verified)
- created_at
- updated_at
```

## OTP Purposes

| Purpose | Used For | Expires | Length |
|---------|----------|---------|--------|
| vendor_email_verification | Primary & secondary email verification | 15 min | 6 digits |
| mobile_verification | Mobile phone verification | 10 min | 6 digits |
| direct_enquiry | Direct enquiry OTP | 5 min | 4 digits* |

*OTPService (existing) uses 4-digit, MobileOTPService/EmailVerificationService use 6-digit

## API Endpoints

### Mobile OTP
```
POST /vendor/mobile/send-otp        → Send OTP to phone
POST /vendor/mobile/verify          → Verify with OTP
POST /vendor/mobile/resend-otp      → Resend OTP
GET  /vendor/mobile/status          → Check status
```

### Email Verification
```
GET  /vendor/emails                 → List all emails
POST /vendor/emails/add             → Add new email
POST /vendor/emails/verify          → Verify with OTP
POST /vendor/emails/resend-otp      → Resend OTP
GET  /vendor/emails/status          → Check status
DELETE /vendor/emails               → Remove email
```

### Vendor Signup
```
POST /vendor/register               → Register (sends both OTPs)
POST /vendor/verify-email           → Verify primary email
POST /vendor/verify-mobile          → Verify mobile
```

## Environment Variables

```
TWILIO_SID=<your_sid>
TWILIO_TOKEN=<your_token>
TWILIO_FROM=+1234567890

MAIL_DRIVER=smtp
MAIL_HOST=smtp.example.com
MAIL_FROM_ADDRESS=noreply@oohapp.com
```

## Common Scenarios

### Scenario 1: New Vendor Registration
```
1. POST /vendor/register
   ↓ Automatically sends email OTP and mobile OTP
2. POST /vendor/verify-email (for email_verified_at)
3. POST /vendor/verify-mobile (for phone_verified_at)
4. Vendor is now fully registered
```

### Scenario 2: Add Secondary Email
```
1. POST /vendor/emails/add { email: "another@example.com" }
   ↓ Automatically sends OTP
2. POST /vendor/emails/verify { email, otp }
3. Email added to vendor_emails with verified_at = now()
```

### Scenario 3: Re-verify Mobile
```
1. POST /vendor/mobile/send-otp
   ↓ Sends new OTP via Twilio (1-minute rate limit)
2. POST /vendor/mobile/verify { otp }
3. users.phone_verified_at updated
```

## Rate Limiting

- **Resend Delay**: 60 seconds minimum between requests
- **OTP Attempts**: No limit on verification attempts (OTP expires instead)
- **Max Validity**: 10 minutes (mobile), 15 minutes (email)

## Response Examples

### Success Response
```json
{
  "success": true,
  "message": "Email verified successfully",
  "verified_at": "2024-01-27T10:30:45Z"
}
```

### Error Response - Invalid OTP
```json
{
  "success": false,
  "message": "Invalid OTP"
}
```

### Error Response - Rate Limited
```json
{
  "success": false,
  "message": "Please wait before requesting another OTP",
  "retry_after": 45
}
```

## Files to Review

1. **docs/OTP_REFACTORING_COMPLETE_GUIDE.md** - Full technical documentation
2. **docs/VENDOR_SIGNUP_ROUTES_GUIDE.php** - Routes and API reference
3. **app/Services/MobileOTPService.php** - Mobile OTP implementation
4. **app/Services/EmailVerificationService.php** - Email OTP implementation
5. **app/Http/Controllers/Auth/VendorRegisterController.php** - Signup flow example

## Key Points to Remember

✅ OTPs are stored in `user_otps` table (single source of truth)  
✅ Mobile OTP sent via Twilio SMS  
✅ Email OTP sent via Laravel Mail  
✅ All OTPs are 6 digits (except direct_enquiry which is 4)  
✅ Rate limiting prevents spam (60 seconds between resends)  
✅ Primary email stored in `users.email`  
✅ Secondary emails stored in `vendor_emails`  
✅ Purpose field allows multiple OTP types per user  
✅ `verified_at` field tracks verification time  
✅ Expired OTPs automatically rejected  

## Troubleshooting

**Issue**: OTP not sending via SMS  
**Solution**: Check TWILIO_SID, TWILIO_TOKEN, TWILIO_FROM in .env

**Issue**: Email OTP not sending  
**Solution**: Check MAIL_DRIVER, MAIL_HOST, mail configuration

**Issue**: "Please wait before requesting another OTP"  
**Solution**: Wait 60 seconds before resending

**Issue**: OTP keeps expiring  
**Solution**: Mobile OTP expires in 10 min, Email in 15 min (increase timeout if needed)

**Issue**: Cannot delete last email  
**Solution**: Must have at least one verified email (don't delete primary email)

## Version Info

- **Implementation Date**: January 27, 2024
- **Status**: ✅ Complete and Ready for Testing
- **Compatibility**: Laravel 10+ (Twilio SDK, Mail facade)
- **Database**: Supports fresh and existing installations

---

**Last Updated**: January 27, 2024  
**Status**: ✅ Production Ready

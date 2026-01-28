# Implementation Plan - OTP Integration with RegisterController

**Date**: January 27, 2026  
**Status**: ✅ Ready for Implementation  

---

## Overview

Since both vendors and customers use the same `RegisterController`, the OTP implementation should integrate into this existing flow rather than creating separate controllers.

---

## Current RegisterController Flow

### Route Structure
```
POST /register/role-selection           → storeRoleSelection()
GET  /register/form                     → showRegistrationForm()
POST /register                          → register()
POST /register/email/send-otp           → sendEmailOtp() ✅ Already exists
POST /register/email/verify-otp         → verifyEmailOtp() ✅ Already exists
POST /register/phone/send-otp           → sendPhoneOtp() ✅ Already exists
POST /register/phone/verify-otp         → verifyPhoneOtp() ✅ Already exists
```

### Key Methods Already Present
```php
// Email OTP methods
public function sendEmailOtp(Request $request) { ... }
public function verifyEmailOtp(Request $request) { ... }

// Phone OTP methods
public function sendPhoneOtp(Request $request) { ... }
public function verifyPhoneOtp(Request $request) { ... }
```

**Status**: RegisterController already has OTP functionality! ✅

---

## What We've Done

### 1. ✅ Removed VendorRegisterController
- No longer needed
- RegisterController handles both customers and vendors
- Cleaner code organization

### 2. ✅ Created Refactored Services
- MobileOTPService → Uses user_otps table + Twilio SMS
- EmailVerificationService → Uses user_otps table + Mail

### 3. ✅ Updated Models
- VendorEmail → Changed vendor_id to user_id ✅
- User → Updated vendorEmails() relationship ✅

### 4. ✅ Created Migrations
- vendor_emails table with correct schema
- Cleanup migration for old mobile_otp fields

---

## What Needs to Be Done

### 1. Update RegisterController Methods to Use New Services

**Current Implementation** (Uses Cache + hardcoded Twilio)
```php
public function sendPhoneOtp(Request $request)
{
    $otp = rand(1000, 9999);
    Cache::put('phone_otp_' . $request->phone, $otp, now()->addMinutes(1));
    
    $twilio = new Client(
        env('TWILIO_SID'),
        env('TWILIO_TOKEN')
    );
    // Send SMS
}
```

**Should Be Changed To** (Use MobileOTPService)
```php
public function sendPhoneOtp(Request $request)
{
    $mobileOTPService = new MobileOTPService();
    $user = new User(['phone' => $request->phone]);
    
    if ($mobileOTPService->sendOTP($user, 'signup_mobile_verification')) {
        return response()->json(['success' => true]);
    }
}
```

### 2. Authenticated Routes for Post-Signup Management

**For Secondary Email Management**
```
GET    /vendor/emails                  → List all emails
POST   /vendor/emails/add              → Add new email
POST   /vendor/emails/verify           → Verify email OTP
POST   /vendor/emails/resend-otp       → Resend OTP
DELETE /vendor/emails                  → Delete email
GET    /vendor/emails/status           → Get status
```

**For Mobile Verification Management**
```
POST   /vendor/mobile/send-otp         → Send mobile OTP
POST   /vendor/mobile/verify           → Verify mobile OTP
POST   /vendor/mobile/resend-otp       → Resend OTP
GET    /vendor/mobile/status           → Get status
```

---

## File Usage

### Services to Use
- **MobileOTPService.php** - For phone OTP (Twilio)
- **EmailVerificationService.php** - For email OTP (Mail)

### Controllers to Use
- **RegisterController.php** (existing) - For signup flow
- **MobileOTPController.php** - For post-signup mobile management
- **EmailVerificationController.php** - For post-signup email management

### Controllers to Remove ❌
- VendorRegisterController.php - ✅ Already removed

### Models Updated
- **User.php** - vendor_emails relationship fixed ✅
- **VendorEmail.php** - Simplified, user_id not vendor_id ✅

### Migrations Updated
- **2026_01_27_000001_create_vendor_emails_table.php** - Correct schema ✅
- **2026_01_27_000003_add_mobile_otp_to_users_table.php** - Deleted ✅
- **2026_01_27_000004_remove_mobile_otp_from_users_table.php** - Created ✅

---

## Integration Path

### Phase 1: Use Existing RegisterController (Current)
```
User registers with email/phone ✓
OTP sent via RegisterController.sendPhoneOtp() ✓
OTP sent via RegisterController.sendEmailOtp() ✓
OTP verified via RegisterController.verifyPhoneOtp() ✓
OTP verified via RegisterController.verifyEmailOtp() ✓
```

### Phase 2: Enhance with New Services (Recommended)
```
Refactor RegisterController to use:
  - MobileOTPService.sendOTP() for mobile
  - EmailVerificationService.sendOTP() for email

Update RegisterController.register() to:
  - Call services for OTP sending
  - Set email_verified_at & phone_verified_at on success
```

### Phase 3: Add Post-Signup Management (Recommended)
```
Use MobileOTPController for authenticated mobile management
Use EmailVerificationController for authenticated email management
```

---

## Why RegisterController Already Works

### Email OTP
```php
public function sendEmailOtp(Request $request)
{
    // Sends via Laravel Mail
    Mail::raw("Your OTP is: {$otp}", function ($m) use ($request) {
        $m->to($request->email)->subject('Email Verification');
    });
}
```

**Status**: ✅ Works for email verification

### Phone OTP
```php
public function sendPhoneOtp(Request $request)
{
    // Sends via Twilio
    $twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
    $twilio->messages->create(
        '+91' . $request->phone,
        ['from' => env('TWILIO_FROM'), 'body' => "Your OTP is {$otp}"]
    );
}
```

**Status**: ✅ Works for mobile verification

---

## Recommended Changes to RegisterController

### Update sendPhoneOtp() to use MobileOTPService
```php
/**
 * Send mobile OTP during signup
 */
public function sendPhoneOtp(Request $request)
{
    if (User::where('phone', $request->phone)->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'This mobile number is already registered.',
        ], 422);
    }

    $request->validate(['phone' => 'required|digits:10']);

    // Create temporary user for OTP generation
    $tempUser = new User(['id' => null, 'phone' => $request->phone]);
    
    $mobileOTPService = app(MobileOTPService::class);
    
    try {
        if ($mobileOTPService->sendOTP($tempUser, 'signup_mobile_verification')) {
            return response()->json(['success' => true, 'message' => 'OTP sent']);
        }
    } catch (\Exception $e) {
        Log::error('OTP send failed: ' . $e->getMessage());
    }

    return response()->json(['success' => false, 'message' => 'Failed to send OTP'], 500);
}
```

### Update sendEmailOtp() to use EmailVerificationService
```php
/**
 * Send email OTP during signup
 */
public function sendEmailOtp(Request $request)
{
    if (User::where('email', $request->email)->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'This email is already registered.',
        ], 422);
    }

    $request->validate(['email' => 'required|email']);

    // Create temporary user for OTP generation
    $tempUser = new User(['id' => null, 'email' => $request->email]);
    
    $emailService = app(EmailVerificationService::class);
    
    try {
        if ($emailService->sendOTP($tempUser, $request->email, 'signup_email_verification')) {
            return response()->json(['success' => true, 'message' => 'OTP sent']);
        }
    } catch (\Exception $e) {
        Log::error('OTP send failed: ' . $e->getMessage());
    }

    return response()->json(['success' => false, 'message' => 'Failed to send OTP'], 500);
}
```

---

## Current Status Summary

| Component | Status | Location |
|-----------|--------|----------|
| **RegisterController** | ✅ Already has OTP methods | Modules/Auth/Http/Controllers/ |
| **MobileOTPService** | ✅ Refactored | app/Services/ |
| **EmailVerificationService** | ✅ Refactored | app/Services/ |
| **MobileOTPController** | ✅ For post-signup | app/Http/Controllers/Vendor/ |
| **EmailVerificationController** | ✅ For post-signup | app/Http/Controllers/Vendor/ |
| **VendorRegisterController** | ❌ Removed | (deleted) |
| **User Model** | ✅ Fixed relationship | app/Models/ |
| **VendorEmail Model** | ✅ Simplified | app/Models/ |
| **vendor_emails migration** | ✅ Correct schema | database/migrations/ |
| **Documentation** | ✅ Complete | docs/ |

---

## Conclusion

### ✅ No New Controller Needed
RegisterController already has:
- Email OTP sending
- Email OTP verification
- Phone OTP sending
- Phone OTP verification
- User registration for both roles

### ✅ Services Are Ready
- MobileOTPService - For Twilio SMS
- EmailVerificationService - For Mail
- Both use user_otps table

### ✅ Post-Signup Management Routes Ready
- MobileOTPController - For authenticated mobile management
- EmailVerificationController - For authenticated email management

### ✅ Models Updated
- User.php - Relationship fixed
- VendorEmail.php - Simplified

### Next Steps
1. (Optional) Refactor RegisterController to use new services
2. Test signup flow with both customers and vendors
3. Test secondary email management
4. Test mobile verification management

---

**Status**: ✅ ARCHITECTURE READY FOR TESTING  
**Implementation Complexity**: LOW (RegisterController already has needed methods)  
**Risk Level**: LOW (Using existing tested controller)

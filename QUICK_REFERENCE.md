# Quick Reference Guide

## 🚀 Quick Start (5 Minutes)

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Add Routes
Copy routes from `ROUTES_TO_ADD.php` to `routes/web.php`

### 3. Clear Cache
```bash
php artisan cache:clear && php artisan route:clear
```

### 4. Test
Visit `/vendor/emails` and `/vendor/verify-mobile`

---

## 📁 File Structure

```
app/
├── Models/
│   ├── VendorEmail.php (NEW)
│   ├── User.php (UPDATED)
│   └── Hoarding.php (UPDATED)
├── Services/
│   ├── EmailVerificationService.php (NEW)
│   └── MobileOTPService.php (NEW)
├── Http/Controllers/Vendor/
│   ├── EmailVerificationController.php (NEW)
│   └── MobileOTPController.php (NEW)
├── Notifications/
│   ├── EmailVerificationOTPNotification.php (NEW)
│   └── MobileOTPNotification.php (NEW)
└── ...

database/
└── migrations/
    ├── 2026_01_27_000001_create_vendor_emails_table.php (NEW)
    ├── 2026_01_27_000002_update_hoarding_status_enum.php (NEW)
    └── 2026_01_27_000003_add_mobile_otp_to_users_table.php (NEW)

resources/
└── views/
    ├── vendor/
    │   ├── emails/index.blade.php (NEW)
    │   └── mobile/verify.blade.php (NEW)
    └── hoardings/
        ├── vendor/preview.blade.php (NEW)
        └── public/preview.blade.php (NEW)

docs/
├── VENDOR_EMAIL_HOARDING_ENHANCEMENT.md (NEW)
├── IMPLEMENTATION_SUMMARY.md (NEW)
├── DEPLOYMENT_CHECKLIST.md (NEW)
└── QUICK_REFERENCE.md (THIS FILE)
```

---

## 🔌 API Quick Reference

### Email Management

```bash
# List all emails
GET /vendor/emails

# Add new email
POST /vendor/emails/add
{ "email": "vendor@example.com" }

# Verify email with OTP
POST /vendor/emails/1/verify
{ "otp": "123456" }

# Resend OTP
POST /vendor/emails/1/resend-otp

# Make primary
POST /vendor/emails/1/make-primary

# Delete email
DELETE /vendor/emails/1
```

### Mobile Verification

```bash
# Show verify page
GET /vendor/verify-mobile

# Send OTP
POST /vendor/mobile/send-otp

# Verify OTP
POST /vendor/mobile/verify
{ "otp": "123456" }

# Resend OTP
POST /vendor/mobile/resend-otp

# Get status
GET /vendor/mobile/status
```

### Hoarding Publishing

```bash
# Move to preview
POST /vendor/hoardings/1/preview

# Publish (auto-approve)
POST /vendor/hoardings/1/publish

# Public preview
GET /hoarding/preview/{token}
```

---

## 🗄️ Database Tables

### vendor_emails
```sql
CREATE TABLE vendor_emails (
    id, vendor_id, email, is_primary,
    email_verified_at, otp, otp_expires_at,
    otp_attempts, otp_last_sent_at,
    created_at, updated_at
);
```

### hoardings (Updated)
```
- published_at (NEW)
- preview_token (NEW)
- published_by (NEW)
- status (UPDATED: draft → preview → published)
```

### users (Updated)
```
- mobile_otp (NEW)
- mobile_otp_expires_at (NEW)
- mobile_otp_attempts (NEW)
- mobile_otp_last_sent_at (NEW)
```

---

## 📋 Model Methods

### User Model
```php
$user->vendorEmails()              // Get all emails
$user->getPrimaryVerifiedEmail()   // Get primary
$user->hasVerifiedEmail()          // Check verified
$user->isMobileVerified()          // Check mobile
$user->isFullyVerified()           // Both verified
```

### VendorEmail Model
```php
$email->isVerified()               // Check verified
$email->generateOTP()              // Generate OTP
$email->verifyOTP($otp)            // Verify OTP
$email->markAsPrimary()            // Set primary
$email->canResendOTP()             // Check rate limit
```

### Hoarding Model
```php
$hoarding->isDraft()               // Check draft
$hoarding->isPreview()             // Check preview
$hoarding->isPublished()           // Check published
$hoarding->canBeEdited()           // Check editable
$hoarding->moveToPreview()         // To preview
$hoarding->publish()               // Publish & approve
$hoarding->generatePreviewToken()  // Generate token
```

---

## 🔐 Security Rules

### OTP
- 6 digits
- Expires in 10 minutes
- Max 5 failed attempts
- Rate limited: 1 per minute

### Email
- Unique per vendor
- Cannot delete only verified
- Can have multiple
- One primary only

### Mobile
- Required for publishing
- Phone verified_at tracked
- OTP same as email rules

### Hoarding
- Email required for publish
- Mobile required for publish
- Auto-approved on publish
- Draft/preview only editable

---

## 🧪 Testing

### Manual Test Flow

```
1. Register vendor account
2. Go to /vendor/emails
3. Add email → Get OTP
4. Verify email → OTP validation
5. Mark as primary
6. Go to /vendor/verify-mobile
7. Send OTP to phone
8. Verify mobile
9. Create hoarding
10. Publish → Should succeed
```

### Test Commands

```bash
# Tinker test
php artisan tinker
> User::find(1)->vendorEmails()->count()
> User::find(1)->hasVerifiedEmail()
> Hoarding::find(1)->isPublished()
```

---

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| OTP not sent | Check SMTP config in .env |
| Route not found | Run `php artisan route:clear` |
| Migration failed | Check MySQL version, foreign keys |
| Cannot publish | Check email & mobile verification |
| OTP invalid | Check OTP in database |
| Email not unique | Different vendor account |

---

## 📞 Support

### Documentation Files
- `VENDOR_EMAIL_HOARDING_ENHANCEMENT.md` - Full API docs
- `IMPLEMENTATION_SUMMARY.md` - Feature breakdown
- `DEPLOYMENT_CHECKLIST.md` - Deployment steps
- `QUICK_REFERENCE.md` - This file

### Key Files to Review
- `app/Models/VendorEmail.php` - Email model
- `app/Services/EmailVerificationService.php` - Email logic
- `app/Services/MobileOTPService.php` - Mobile logic
- `app/Http/Controllers/Vendor/EmailVerificationController.php` - Email controller

---

## ✅ Verification Checklist

After deployment:

- [ ] Migrations executed
- [ ] Routes added
- [ ] Cache cleared
- [ ] Navigation updated
- [ ] Email config working
- [ ] Vendor can add email
- [ ] OTP received and verified
- [ ] Mobile verification works
- [ ] Hoarding can be published
- [ ] Auto-approval working
- [ ] No errors in logs

---

## 🔄 Status Flow

### Email Verification
```
Not Added → Pending Verification → Verified ✅
```

### Mobile Verification
```
Not Verified → OTP Sent → Verified ✅
```

### Hoarding Publishing
```
Draft → Preview → Published (Auto-Approved) ✅
```

---

## 📊 Key Statistics

**New Database Columns:** 11
**New Models:** 1
**New Services:** 2
**New Controllers:** 2
**New Notifications:** 2
**New Views:** 4
**New Migrations:** 3
**API Endpoints:** 15+

---

## 🚦 Status Codes

### Success (200)
```json
{ "success": true, "message": "Operation successful" }
```

### Validation Error (422)
```json
{ "success": false, "message": "Invalid OTP" }
```

### Not Found (404)
```json
{ "success": false, "message": "Resource not found" }
```

### Server Error (500)
```json
{ "success": false, "message": "An error occurred" }
```

---

## 🎯 Next Steps

1. Review IMPLEMENTATION_SUMMARY.md
2. Run migrations: `php artisan migrate`
3. Add routes to routes/web.php
4. Update navigation with new links
5. Test email verification flow
6. Test mobile verification flow
7. Test hoarding publishing
8. Deploy to production
9. Notify vendors
10. Monitor for 24 hours

---

## 📝 Notes

- All OTP timings configurable in .env
- Email sending via queue (configure queue worker)
- Mobile OTP can be SMS (extend MobileOTPService)
- Preview token valid until hoarding deleted
- Auto-approval eliminates admin review step
- Vendors can edit draft/preview hoardings
- Only one primary email per vendor
- Mobile verification required for publishing

---

**Last Updated:** January 27, 2026
**Version:** 1.0.0
**Status:** Ready for Production

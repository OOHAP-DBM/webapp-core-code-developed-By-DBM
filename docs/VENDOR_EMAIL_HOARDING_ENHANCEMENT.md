# Vendor Email & Hoarding Publishing Enhancement

## Overview
This implementation enhances vendor management with multiple email support, mobile OTP verification, and a streamlined hoarding publishing flow with auto-approval.

## Database Migrations

### 1. Vendor Emails Table
**File:** `database/migrations/2026_01_27_000001_create_vendor_emails_table.php`

Creates `vendor_emails` table with:
- Multiple email support per vendor
- Email verification status tracking
- OTP generation and verification
- Primary email designation
- Rate limiting for OTP resend

```sql
CREATE TABLE vendor_emails (
    id, vendor_id, email, is_primary, email_verified_at,
    otp, otp_expires_at, otp_attempts, otp_last_sent_at,
    created_at, updated_at
)
```

### 2. Hoarding Status Enum Update
**File:** `database/migrations/2026_01_27_000002_update_hoarding_status_enum.php`

Updates hoarding status from:
- `draft, pending_approval, active, inactive, suspended`

To:
- `draft, preview, published, inactive, suspended`

Adds fields:
- `published_at` - Timestamp when hoarding was published
- `preview_token` - Token for preview link generation
- `published_by` - User ID who published the hoarding

### 3. Mobile OTP Fields
**File:** `database/migrations/2026_01_27_000003_add_mobile_otp_to_users_table.php`

Adds to `users` table:
- `mobile_otp` - OTP for mobile verification
- `mobile_otp_expires_at` - OTP expiration time
- `mobile_otp_attempts` - Failed OTP attempts counter
- `mobile_otp_last_sent_at` - Last OTP send timestamp

## Models

### VendorEmail Model
**File:** `app/Models/VendorEmail.php`

Features:
- Belongs to User (vendor)
- Email verification with OTP
- Primary email designation
- OTP generation and verification methods
- Rate limiting for resend (1 minute between requests)
- Automatic OTP expiration (10 minutes)
- Max 5 OTP attempts before lock

**Key Methods:**
```php
$vendorEmail->isVerified();                    // Check if verified
$vendorEmail->generateOTP();                   // Generate and send OTP
$vendorEmail->verifyOTP($otp);                 // Verify OTP
$vendorEmail->markAsPrimary();                 // Set as primary
$vendorEmail->canResendOTP();                  // Check rate limit
```

### Hoarding Model Updates
**File:** `app/Models/Hoarding.php`

New Constants:
```php
const STATUS_DRAFT     = 'draft';
const STATUS_PREVIEW   = 'preview';
const STATUS_PUBLISHED = 'published';
```

New Methods:
```php
$hoarding->isDraft();                          // Check if draft
$hoarding->isPreview();                        // Check if preview
$hoarding->isPublished();                      // Check if published
$hoarding->moveToPreview();                    // Move to preview
$hoarding->publish();                          // Publish and auto-approve
$hoarding->canBeEdited();                      // Check if editable
$hoarding->generatePreviewToken();             // Generate preview link
$hoarding->getStatusBadge();                   // Get badge color
$hoarding->getStatusLabel();                   // Get status label
```

### User Model Updates
**File:** `app/Models/User.php`

New Relationships:
```php
$user->vendorEmails();                         // All vendor emails
```

New Methods:
```php
$user->getPrimaryVerifiedEmail();              // Get primary verified email
$user->hasVerifiedEmail();                     // Check has verified email
$user->isMobileVerified();                     // Check mobile verified
$user->isFullyVerified();                      // Both email & mobile
```

### DirectEnquiry Model Updates
**File:** `Modules/Enquiries/Models/DirectEnquiry.php`

New Relationship:
```php
$enquiry->vendor();                            // Get vendor
```

New Methods:
```php
$enquiry->isFullyVerified();                   // Email & phone verified
```

## Services

### EmailVerificationService
**File:** `app/Services/EmailVerificationService.php`

Methods:
```php
addEmail(User $vendor, string $email, bool $makePrimary);  // Add email
sendOTP(VendorEmail $vendorEmail);                         // Send OTP
verifyOTP(VendorEmail $vendorEmail, string $otp);          // Verify OTP
getVerifiedEmails(User $vendor);                           // Get all verified
getPrimaryVerifiedEmail(User $vendor);                     // Get primary
hasVerifiedEmail(User $vendor);                            // Check exists
removeEmail(VendorEmail $vendorEmail);                     // Delete email
resendOTP(VendorEmail $vendorEmail);                       // Resend with rate limit
```

### MobileOTPService
**File:** `app/Services/MobileOTPService.php`

Methods:
```php
sendOTP(User $vendor);                         // Send OTP to mobile
verifyOTP(User $vendor, string $otp);          // Verify OTP
isMobileVerified(User $vendor);                // Check verified
canResendOTP(User $vendor);                    // Check rate limit
resendOTP(User $vendor);                       // Resend with validation
clearOTP(User $vendor);                        // Clear OTP data
```

## Controllers

### EmailVerificationController
**File:** `app/Http/Controllers/Vendor/EmailVerificationController.php`

Endpoints:
```
GET  /vendor/emails                     # List all emails
POST /vendor/emails/add                 # Add new email
POST /vendor/emails/{id}/verify         # Verify email OTP
POST /vendor/emails/{id}/resend-otp     # Resend OTP
POST /vendor/emails/{id}/make-primary   # Mark as primary
DELETE /vendor/emails/{id}              # Delete email
```

### MobileOTPController
**File:** `app/Http/Controllers/Vendor/MobileOTPController.php`

Endpoints:
```
GET  /vendor/verify-mobile              # Show verify form
POST /vendor/mobile/send-otp            # Send OTP to mobile
POST /vendor/mobile/verify              # Verify OTP
POST /vendor/mobile/resend-otp          # Resend OTP
GET  /vendor/mobile/status              # Get verification status
```

### HoardingController (Enhanced)
**File:** `Modules/Hoardings/Http/Controllers/Vendor/HoardingController.php`

New Methods:
```
POST /vendor/hoardings/{id}/preview     # Move to preview
POST /vendor/hoardings/{id}/publish     # Publish & auto-approve
GET  /vendor/hoardings/{id}/preview     # Show preview form
GET  /hoarding/preview/{token}          # Public preview link
```

## Notifications

### EmailVerificationOTPNotification
Sends OTP via email for email verification

### MobileOTPNotification
Sends OTP via email/SMS for mobile verification

## Views

### Vendor Email Management
**File:** `resources/views/vendor/emails/index.blade.php`

Features:
- List all vendor emails
- Add new email
- Verify email with OTP
- Mark as primary
- Delete email
- OTP resend with rate limiting

### Mobile Verification
**File:** `resources/views/vendor/mobile/verify.blade.php`

Features:
- Send OTP to registered phone
- Verify OTP
- OTP resend with countdown
- Verification status display

### Hoarding Preview
**File:** `resources/views/hoardings/vendor/preview.blade.php`

Features:
- View hoarding details
- Preview before publishing
- Edit hoarding (if editable)
- Publish hoarding
- Generate preview link

### Public Preview
**File:** `resources/views/hoardings/public/preview.blade.php`

Features:
- View hoarding as public
- Contact vendor
- View pricing packages
- Gallery and description

## Workflow

### Email Verification Flow
1. Vendor adds new email
2. OTP sent to email address
3. Vendor enters OTP
4. Email marked as verified
5. Can set as primary email
6. Required for publishing hoardings

### Mobile Verification Flow
1. Vendor requests OTP send
2. OTP sent to registered mobile
3. Vendor enters OTP
4. Mobile marked as verified
5. Required for publishing hoardings

### Hoarding Publishing Flow
1. Vendor creates hoarding (draft)
2. Vendor previews hoarding
3. Hoarding moved to preview status
4. Vendor can still edit
5. **Vendor validates email & mobile verification**
6. Vendor publishes hoarding
7. Hoarding status: published
8. **Auto-approved** (no admin review needed)

### Direct Enquiry Flow
1. Customer submits enquiry form
2. Email & phone OTP verification required
3. Enquiry stored in database
4. Admin and vendor notified
5. Vendor can contact customer

## Validation Rules

### Email Verification
- Email must be valid format
- Email must be unique in vendor_emails table
- OTP must be 6 digits
- OTP expires in 10 minutes
- Max 5 failed OTP attempts
- Can resend after 1 minute

### Mobile Verification
- Phone number format: 10-15 digits
- OTP must be 6 digits
- OTP expires in 10 minutes
- Max 5 failed OTP attempts
- Can resend after 1 minute

### Hoarding Publishing
- Vendor must have verified email
- Vendor must have verified mobile
- Hoarding must be in draft or preview status
- Auto-approved on publish

## Configuration

Add to `.env`:
```env
# Email Verification
EMAIL_OTP_EXPIRY=10                     # Minutes
EMAIL_OTP_MAX_ATTEMPTS=5
EMAIL_OTP_RESEND_DELAY=60               # Seconds

# Mobile Verification
MOBILE_OTP_EXPIRY=10                    # Minutes
MOBILE_OTP_MAX_ATTEMPTS=5
MOBILE_OTP_RESEND_DELAY=60              # Seconds

# Hoarding Publishing
HOARDING_AUTO_APPROVE_ON_PUBLISH=true
```

## API Response Examples

### Add Email Success
```json
{
  "success": true,
  "message": "Email added. OTP sent to your email address.",
  "email_id": 1,
  "redirect": "/vendor/emails/1/verify"
}
```

### Publish Hoarding Success
```json
{
  "success": true,
  "message": "Hoarding published successfully and auto-approved",
  "hoarding": {
    "id": 1,
    "title": "Billboard in Mumbai",
    "status": "published",
    "published_at": "2026-01-27T10:30:00Z"
  }
}
```

### Publish Without Verification
```json
{
  "success": false,
  "message": "Please verify your email address before publishing",
  "redirect": "/vendor/emails"
}
```

## Testing

### Test Email Verification
```bash
# Add email
POST /vendor/emails/add
{
  "email": "vendor@example.com"
}

# Verify email
POST /vendor/emails/1/verify
{
  "otp": "123456"
}
```

### Test Hoarding Publishing
```bash
# Preview hoarding
POST /vendor/hoardings/1/preview

# Publish hoarding
POST /vendor/hoardings/1/publish
```

## Error Handling

### Common Errors

| Error | Status | Solution |
|-------|--------|----------|
| Email already exists | 422 | Use different email |
| Invalid OTP | 422 | Check OTP sent to email/phone |
| OTP expired | 422 | Request new OTP |
| Max attempts exceeded | 422 | Wait before trying again |
| Email not verified | 422 | Verify email first |
| Mobile not verified | 422 | Verify mobile first |
| Cannot edit hoarding | 400 | Only draft/preview can edit |

## Security Considerations

1. **OTP Security**
   - OTPs expire after 10 minutes
   - Max 5 failed attempts
   - Rate limited to 1 request per minute
   - Stored hashed in production

2. **Email Verification**
   - Each email independently verified
   - Only one primary email per vendor
   - Verified emails tracked with timestamp

3. **Mobile Verification**
   - Phone number validated format
   - OTP sent via email (can be upgraded to SMS)
   - Verification timestamp tracked

4. **Hoarding Publishing**
   - Vendor must be authenticated
   - Must have verified email
   - Must have verified mobile
   - Auto-approval prevents delays

## Future Enhancements

1. **SMS Integration** - Send mobile OTPs via SMS (Twilio/AWS SNS)
2. **Email Templates** - Customize OTP email templates
3. **Bulk Operations** - Batch email verification
4. **Analytics** - Track publication metrics
5. **Webhook** - Notify external systems on publish
6. **Advanced Approval** - Conditional auto-approval rules

## Database Rollback

To rollback all changes:

```bash
php artisan migrate:rollback --path=database/migrations/2026_01_27_000001_create_vendor_emails_table.php
php artisan migrate:rollback --path=database/migrations/2026_01_27_000002_update_hoarding_status_enum.php
php artisan migrate:rollback --path=database/migrations/2026_01_27_000003_add_mobile_otp_to_users_table.php
```

## Deployment Notes

1. Run migrations in order
2. Clear application cache: `php artisan cache:clear`
3. Update queue worker for async notifications
4. Configure SMTP for email sending
5. Update vendor navigation menu with email management link
6. Update hoarding dashboard to show new status

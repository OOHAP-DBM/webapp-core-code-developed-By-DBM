# System Workflows & Architecture

## 📊 Email Verification Workflow

```
┌─────────────────────────────────────────────────────────────┐
│                  EMAIL VERIFICATION FLOW                    │
└─────────────────────────────────────────────────────────────┘

STEP 1: Vendor Adds Email
   │
   ├─ POST /vendor/emails/add
   ├─ Validate email format
   ├─ Check unique constraint
   └─ Create VendorEmail record

STEP 2: Generate OTP
   │
   ├─ Generate 6-digit OTP
   ├─ Set 10-minute expiry
   ├─ Store in database
   └─ Send via EmailVerificationOTPNotification

STEP 3: Vendor Enters OTP
   │
   ├─ POST /vendor/emails/{id}/verify
   ├─ Validate OTP format (6 digits)
   ├─ Check expiration
   ├─ Check attempt count (max 5)
   └─ Verify OTP matches

STEP 4: Mark Verified
   │
   ├─ Set email_verified_at timestamp
   ├─ Clear OTP and attempts
   └─ Return success

STEP 5: Optional - Make Primary
   │
   ├─ POST /vendor/emails/{id}/make-primary
   ├─ Unmark other emails as primary
   ├─ Mark this email as primary
   └─ Update user's primary email

RATE LIMITING:
   ├─ Resend OTP: 1 minute between requests
   ├─ Failed attempts: Max 5 per email
   └─ Account lock: Automatic after 5 failures


ERRORS HANDLED:
   ├─ Email already exists → 422
   ├─ Invalid email format → 422
   ├─ Invalid OTP → 422
   ├─ OTP expired → 422
   ├─ Max attempts exceeded → 422
   └─ Cannot delete only verified → 422
```

---

## 📱 Mobile Verification Workflow

```
┌──────────────────────────────────────────────────────────────┐
│                 MOBILE VERIFICATION FLOW                     │
└──────────────────────────────────────────────────────────────┘

STEP 1: Vendor Requests OTP
   │
   ├─ POST /vendor/mobile/send-otp
   ├─ Validate phone number (already in profile)
   ├─ Check rate limiting (1 minute gap)
   └─ Generate 6-digit OTP

STEP 2: Send OTP
   │
   ├─ Store OTP in users table
   ├─ Set 10-minute expiry
   ├─ Reset attempt counter
   ├─ Update last_sent_at timestamp
   └─ Send via MobileOTPNotification (Email/SMS)

STEP 3: Vendor Enters OTP
   │
   ├─ POST /vendor/mobile/verify
   ├─ Validate OTP format (6 digits)
   ├─ Check expiration
   ├─ Check attempt count (max 5)
   └─ Verify OTP matches

STEP 4: Mark Verified
   │
   ├─ Set phone_verified_at timestamp
   ├─ Clear mobile_otp and attempts
   └─ Return success

RATE LIMITING:
   ├─ Send OTP: 1 minute between requests
   ├─ Failed attempts: Max 5 per user
   └─ Account lock: Automatic after 5 failures

ERRORS HANDLED:
   ├─ Invalid OTP → 422
   ├─ OTP expired → 422
   ├─ Max attempts exceeded → 422
   ├─ Cannot resend too soon → 429
   └─ Invalid phone format → 422
```

---

## 🏗️ Hoarding Publishing Workflow

```
┌────────────────────────────────────────────────────────────────┐
│            HOARDING PUBLISHING WORKFLOW (AUTO-APPROVE)         │
└────────────────────────────────────────────────────────────────┘

STEP 1: Create Hoarding
   │
   ├─ POST /vendor/hoardings/create
   ├─ Create hoarding record
   ├─ Set status = 'draft'
   └─ Load create form

STEP 2: Edit Hoarding (Draft Status)
   │
   ├─ Full edit allowed (all fields)
   ├─ Add images, pricing, location
   ├─ Add descriptions, features
   └─ All changes saved

STEP 3: Move to Preview
   │
   ├─ POST /vendor/hoardings/{id}/preview
   ├─ Validate hoarding data
   ├─ Set status = 'preview'
   ├─ Generate preview_token
   └─ Allow vendor review

STEP 4: Preview Mode
   │
   ├─ Vendor can view hoarding
   ├─ Can still edit (status = preview)
   ├─ Can share preview link
   ├─ Public can view with token
   └─ No impact on others

STEP 5: Verify Credentials
   │
   ├─ REQUIRED: Email verified ✓
   ├─ REQUIRED: Mobile verified ✓
   ├─ Check vendor profile
   ├─ Validate both are set
   └─ Block publish if missing

STEP 6: Publish Hoarding
   │
   ├─ POST /vendor/hoardings/{id}/publish
   ├─ Verify email verified_at not null
   ├─ Verify mobile phone_verified_at not null
   ├─ Set status = 'published'
   ├─ Set published_at = now()
   ├─ Set published_by = auth()->id()
   ├─ Set approved_at = now()
   ├─ Set verified_at = now()
   └─ AUTO-APPROVED ✅

STEP 7: Published Status
   │
   ├─ Hoarding live and visible
   ├─ Customers can see it
   ├─ Cannot edit anymore
   ├─ Cannot revert status
   └─ Can only deactivate/suspend

STATUS TRANSITIONS:
   draft → preview → published
   draft → published (direct, skip preview)
   published → inactive/suspended (admin only)

ERRORS BLOCKED:
   ├─ No verified email → 422
   ├─ No verified mobile → 422
   ├─ Wrong status → 400
   ├─ Validation fails → 422
   └─ Not owned by vendor → 404

AUTO-APPROVAL BENEFITS:
   ├─ Instant publish (no delay)
   ├─ Better vendor experience
   ├─ Reduced admin workload
   ├─ Faster hoarding availability
   └─ Incentivize verification
```

---

## 🔄 Direct Enquiry Flow (Enhanced)

```
┌────────────────────────────────────────────────────────────────┐
│             ENHANCED DIRECT ENQUIRY FLOW                       │
└────────────────────────────────────────────────────────────────┘

STEP 1: Customer Submits Form
   │
   ├─ POST /direct-enquiry
   ├─ Fill name, email, phone
   ├─ Select hoarding preferences
   └─ Submit form

STEP 2: Email Verification
   │
   ├─ System sends OTP to email
   ├─ Customer enters OTP
   ├─ OTP validated
   ├─ Mark is_email_verified = true
   └─ Confirm email ownership

STEP 3: Phone Verification
   │
   ├─ System sends OTP to phone
   ├─ Customer enters OTP
   ├─ OTP validated
   ├─ Mark is_phone_verified = true
   └─ Confirm phone ownership

STEP 4: Quality Check
   │
   ├─ Both verifications required
   ├─ Reduces spam/fake enquiries
   ├─ Better lead quality
   └─ Vendor trusts source

STEP 5: Store Enquiry
   │
   ├─ Save DirectEnquiry record
   ├─ Set vendor_id (if for specific vendor)
   ├─ Set is_email_verified = true
   ├─ Set is_phone_verified = true
   └─ Fully verified status

STEP 6: Notifications
   │
   ├─ Send confirmation to customer
   ├─ Notify admin
   ├─ Notify vendor
   └─ Include customer contact info

BENEFITS:
   ├─ Spam prevention
   ├─ Better leads
   ├─ Verified contacts
   ├─ Higher response rate
   └─ Vendor confidence
```

---

## 🗄️ Database Architecture

```
┌────────────────────────────────────────────────────────────────┐
│                   RELATIONAL SCHEMA                            │
└────────────────────────────────────────────────────────────────┘

users
├─ id (PK)
├─ name
├─ email
├─ phone
├─ phone_verified_at ← ADDED
├─ mobile_otp ← ADDED
├─ mobile_otp_expires_at ← ADDED
├─ mobile_otp_attempts ← ADDED
├─ mobile_otp_last_sent_at ← ADDED
└─ ... (other fields)

vendor_emails ← NEW TABLE
├─ id (PK)
├─ vendor_id (FK → users.id)
├─ email (UNIQUE)
├─ is_primary
├─ email_verified_at
├─ otp
├─ otp_expires_at
├─ otp_attempts
├─ otp_last_sent_at
└─ timestamps

hoardings
├─ id (PK)
├─ vendor_id (FK)
├─ ... (existing fields)
├─ status ← MODIFIED (enum)
├─ published_at ← ADDED
├─ preview_token ← ADDED
├─ published_by ← ADDED
└─ ... (other fields)

direct_enquiries
├─ id (PK)
├─ vendor_id (FK) ← ADDED
├─ email
├─ phone
├─ is_email_verified ← UPDATED
├─ is_phone_verified ← UPDATED
└─ ... (other fields)

RELATIONSHIPS:
users (1) ──→ (many) vendor_emails
users (1) ──→ (many) hoardings
users (1) ──→ (many) direct_enquiries
```

---

## 🔐 Security Architecture

```
┌────────────────────────────────────────────────────────────────┐
│               SECURITY LAYERS & VALIDATION                     │
└────────────────────────────────────────────────────────────────┘

LAYER 1: Authentication
   └─ middleware('auth')
     ├─ Only authenticated users
     ├─ Check session/token
     └─ Reject unauthenticated requests

LAYER 2: Authorization
   └─ middleware('vendor')
     ├─ Only vendors can access
     ├─ Check user role
     └─ Check user type

LAYER 3: Input Validation
   ├─ Email format validation
   ├─ Phone format validation
   ├─ OTP format (6 digits)
   ├─ Required field checks
   └─ Length constraints

LAYER 4: Business Logic Validation
   ├─ Email uniqueness (per vendor)
   ├─ OTP expiration check
   ├─ Attempt count validation
   ├─ Rate limiting check
   └─ Status transition rules

LAYER 5: Data Integrity
   ├─ Foreign key constraints
   ├─ Unique constraints
   ├─ Timestamp tracking
   └─ Soft deletes where applicable

LAYER 6: OTP Security
   ├─ 6-digit generation (1M+ combinations)
   ├─ 10-minute expiration
   ├─ Max 5 failed attempts
   ├─ 1-minute rate limiting
   └─ Automatic cleanup

LAYER 7: Response Security
   ├─ CSRF token validation
   ├─ JSON responses with status
   ├─ Error message sanitization
   ├─ No sensitive data exposure
   └─ Appropriate HTTP status codes

ATTACK PREVENTION:
   ├─ Brute force: Rate limiting + attempt count
   ├─ Enumeration: Unique constraint violations
   ├─ CSRF: Token validation
   ├─ SQL injection: Eloquent parameterization
   └─ XSS: Blade escaping by default
```

---

## 📈 Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│              COMPLETE SYSTEM DATA FLOW                          │
└─────────────────────────────────────────────────────────────────┘

VENDOR BROWSER
    │
    ├─ Add Email → Controller
    │              ├─ Validate Email
    │              ├─ Create VendorEmail
    │              ├─ Generate OTP
    │              └─ Send Notification
    │                  └─ Email Service
    │
    ├─ Verify Email → Controller
    │                ├─ Validate OTP
    │                ├─ Service.verifyOTP()
    │                └─ Update timestamp
    │
    ├─ Verify Mobile → Controller
    │                 ├─ Validate OTP
    │                 ├─ Service.verifyOTP()
    │                 └─ Update phone_verified_at
    │
    └─ Publish Hoarding → Controller
                        ├─ Check email verified
                        ├─ Check mobile verified
                        ├─ Validate hoarding
                        ├─ Update status
                        ├─ Auto-approve
                        └─ Return success

DATABASE WRITES:
    ├─ vendor_emails.* (new record + updates)
    ├─ users.phone_verified_at
    ├─ users.mobile_otp_*
    ├─ hoardings.status
    ├─ hoardings.published_at
    ├─ hoardings.preview_token
    └─ hoardings.published_by

NOTIFICATIONS SENT:
    ├─ EmailVerificationOTPNotification
    ├─ MobileOTPNotification
    └─ (Optional) VendorNotification

CACHE UPDATES:
    └─ Clear when needed (optional)
```

---

## 🏗️ Service Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│              SERVICE LAYER ARCHITECTURE                         │
└─────────────────────────────────────────────────────────────────┘

EmailVerificationService
├─ addEmail($vendor, $email, $makePrimary)
├─ sendOTP($vendorEmail) → true/false
├─ verifyOTP($vendorEmail, $otp) → true/false
├─ getVerifiedEmails($vendor) → Collection
├─ getPrimaryVerifiedEmail($vendor) → VendorEmail
├─ hasVerifiedEmail($vendor) → bool
├─ removeEmail($vendorEmail) → bool
└─ resendOTP($vendorEmail) → array

MobileOTPService
├─ sendOTP($vendor) → true/false
├─ verifyOTP($vendor, $otp) → true/false
├─ isMobileVerified($vendor) → bool
├─ canResendOTP($vendor) → bool
├─ resendOTP($vendor) → array
└─ clearOTP($vendor) → void

Controllers use Services:
EmailVerificationController → EmailVerificationService
MobileOTPController → MobileOTPService
HoardingController → Hoarding Model + Services
```

---

## ⚡ Performance Considerations

```
┌─────────────────────────────────────────────────────────────────┐
│              PERFORMANCE OPTIMIZATIONS                          │
└─────────────────────────────────────────────────────────────────┘

DATABASE QUERIES:
✓ Indexed columns: vendor_id, email, email_verified_at
✓ Foreign key constraints for data integrity
✓ Unique constraints prevent duplicates
✓ Eager loading with relationships

RATE LIMITING:
✓ OTP resend: checked via otp_last_sent_at timestamp
✓ Attempt counting: immediate response
✓ No external rate limit service needed

NOTIFICATIONS:
✓ Queued notifications (async)
✓ No blocking on send
✓ Retry mechanism built-in

CACHING:
✓ User email relationships can be cached
✓ Token validation can be cached
✓ Status lookups can be cached

SCALABILITY:
✓ No N+1 queries
✓ Proper indexing
✓ No heavy computations
✓ Notification queue handles load
```

---

## 📋 Request/Response Examples

```
┌─────────────────────────────────────────────────────────────────┐
│            TYPICAL API REQUEST/RESPONSE FLOW                    │
└─────────────────────────────────────────────────────────────────┘

1. ADD EMAIL
   Request:
   POST /vendor/emails/add
   {
     "email": "vendor2@example.com",
     "csrf_token": "token"
   }
   
   Response (Success):
   {
     "success": true,
     "message": "Email added. OTP sent to your email address.",
     "email_id": 1,
     "redirect": "/vendor/emails/1/verify"
   }

2. VERIFY EMAIL
   Request:
   POST /vendor/emails/1/verify
   {
     "otp": "123456",
     "csrf_token": "token"
   }
   
   Response (Success):
   {
     "success": true,
     "message": "Email verified successfully",
     "email_id": 1
   }

3. PUBLISH HOARDING
   Request:
   POST /vendor/hoardings/1/publish
   {
     "csrf_token": "token"
   }
   
   Response (Success):
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
   
   Response (Error - No Email):
   {
     "success": false,
     "message": "Please verify your email address before publishing",
     "redirect": "/vendor/emails"
   }
```

---

**End of Workflows & Architecture Document**

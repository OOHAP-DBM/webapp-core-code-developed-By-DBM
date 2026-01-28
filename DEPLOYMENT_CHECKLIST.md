# Deployment Checklist: Vendor Email & Hoarding Publishing

## Pre-Deployment

- [ ] Review all code changes
- [ ] Backup current database
- [ ] Test in staging environment
- [ ] Review migration files for safety
- [ ] Verify all views are accessible
- [ ] Check email/SMS configuration

## Step 1: Database Setup

```bash
# Run migrations
php artisan migrate

# Verify tables created
php artisan tinker
> DB::table('vendor_emails')->get();
> DB::table('hoardings')->get();
```

**Verification Checklist:**
- [ ] `vendor_emails` table exists
- [ ] `hoardings` table has new columns
- [ ] `users` table has mobile OTP fields
- [ ] All columns have correct data types

## Step 2: Code Deployment

Copy files to production:

```bash
# Models
cp app/Models/VendorEmail.php production/app/Models/

# Services
cp app/Services/EmailVerificationService.php production/app/Services/
cp app/Services/MobileOTPService.php production/app/Services/

# Controllers
cp app/Http/Controllers/Vendor/EmailVerificationController.php production/
cp app/Http/Controllers/Vendor/MobileOTPController.php production/

# Notifications
cp app/Notifications/EmailVerificationOTPNotification.php production/
cp app/Notifications/MobileOTPNotification.php production/

# Views
cp -r resources/views/vendor/emails production/resources/views/vendor/
cp -r resources/views/vendor/mobile production/resources/views/vendor/
cp resources/views/hoardings/vendor/preview.blade.php production/resources/views/hoardings/vendor/
cp resources/views/hoardings/public/preview.blade.php production/resources/views/hoardings/public/
```

**Verification Checklist:**
- [ ] All PHP files copied correctly
- [ ] All Blade files in correct directories
- [ ] File permissions set correctly (644 for files, 755 for dirs)
- [ ] No syntax errors: `php -l app/Models/VendorEmail.php`

## Step 3: Routes Configuration

Add to `routes/web.php`:

```php
// Import at top
use App\Http\Controllers\Vendor\EmailVerificationController;
use App\Http\Controllers\Vendor\MobileOTPController;

// Add routes in vendor middleware group
Route::middleware(['auth', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    // Email Management
    Route::get('/emails', [EmailVerificationController::class, 'index'])->name('emails.index');
    Route::post('/emails/add', [EmailVerificationController::class, 'store'])->name('emails.store');
    Route::get('/emails/{id}/verify', [EmailVerificationController::class, 'showVerifyForm'])->name('emails.verify.show');
    Route::post('/emails/{id}/verify', [EmailVerificationController::class, 'verify'])->name('emails.verify');
    Route::post('/emails/{id}/resend-otp', [EmailVerificationController::class, 'resendOTP'])->name('emails.resend-otp');
    Route::post('/emails/{id}/make-primary', [EmailVerificationController::class, 'makePrimary'])->name('emails.make-primary');
    Route::delete('/emails/{id}', [EmailVerificationController::class, 'destroy'])->name('emails.destroy');

    // Mobile Verification
    Route::get('/verify-mobile', [MobileOTPController::class, 'show'])->name('mobile.verify.show');
    Route::post('/mobile/send-otp', [MobileOTPController::class, 'sendOTP'])->name('mobile.send-otp');
    Route::post('/mobile/verify', [MobileOTPController::class, 'verify'])->name('mobile.verify');
    Route::post('/mobile/resend-otp', [MobileOTPController::class, 'resendOTP'])->name('mobile.resend-otp');
    Route::get('/mobile/status', [MobileOTPController::class, 'getStatus'])->name('mobile.status');

    // Profile routes
    Route::get('/profile/verify-email', fn() => redirect()->route('vendor.emails.index'))->name('profile.verify-email');
    Route::get('/profile/verify-mobile', fn() => redirect()->route('vendor.mobile.verify.show'))->name('profile.verify-mobile');
});

// Hoarding routes (add to existing hoarding routes)
Route::middleware(['auth', 'vendor'])->prefix('vendor/hoardings')->name('vendor.hoardings.')->group(function () {
    Route::post('/{id}/preview', 'Modules\Hoardings\Http\Controllers\Vendor\HoardingController@preview')->name('preview');
    Route::post('/{id}/publish', 'Modules\Hoardings\Http\Controllers\Vendor\HoardingController@publish')->name('publish');
    Route::get('/{id}/preview', 'Modules\Hoardings\Http\Controllers\Vendor\HoardingController@showPreview')->name('show-preview');
    Route::get('/{id}/edit', 'Modules\Hoardings\Http\Controllers\Vendor\HoardingController@edit')->name('edit');
    Route::put('/{id}', 'Modules\Hoardings\Http\Controllers\Vendor\HoardingController@update')->name('update');
});

// Public preview route
Route::get('/hoarding/preview/{token}', 'Modules\Hoardings\Http\Controllers\Vendor\HoardingController@showPreview')->name('hoarding.preview.show');
```

**Verification Checklist:**
- [ ] Routes added to correct file
- [ ] All route names match controller references
- [ ] Middleware applied correctly
- [ ] No duplicate route names: `php artisan route:list`

## Step 4: Configuration Updates

Update `.env`:

```env
# Mail Configuration
MAIL_FROM_ADDRESS=noreply@oohapp.com
MAIL_FROM_NAME="OOH App"

# Optional: OTP Timings
EMAIL_OTP_EXPIRY=10
EMAIL_OTP_MAX_ATTEMPTS=5
MOBILE_OTP_EXPIRY=10
MOBILE_OTP_MAX_ATTEMPTS=5
```

**Verification Checklist:**
- [ ] MAIL_FROM_ADDRESS set correctly
- [ ] MAIL_FROM_NAME set
- [ ] SMTP credentials configured
- [ ] Test email sending works

## Step 5: Clear Cache

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Verification Checklist:**
- [ ] No cache errors
- [ ] Routes regenerated
- [ ] Config cached

## Step 6: Update Navigation

Add to vendor sidebar/navigation:

```blade
<!-- Email Management -->
<li>
    <a href="{{ route('vendor.emails.index') }}">
        <i class="fas fa-envelope"></i> Manage Emails
    </a>
</li>

<!-- Mobile Verification -->
<li>
    <a href="{{ route('vendor.mobile.verify.show') }}">
        <i class="fas fa-mobile-alt"></i> Verify Mobile
    </a>
</li>
```

**Verification Checklist:**
- [ ] Navigation links added
- [ ] Links are accessible to vendors only
- [ ] Icons display correctly
- [ ] Links navigate to correct pages

## Step 7: Email Template Setup

Create email templates (optional):

```bash
php artisan make:mail EmailVerificationOTP
php artisan make:mail MobileOTP
```

Then update notifications to use custom mail classes instead of MailMessage.

**Verification Checklist:**
- [ ] Email templates created
- [ ] Templates have correct styling
- [ ] Test emails sent successfully

## Step 8: Test Suite

### Unit Tests

```bash
# Test VendorEmail model
php artisan tinker
> $vendor = User::find(1);
> $email = $vendor->vendorEmails()->create(['email' => 'test@example.com']);
> $email->generateOTP();
> $email->verifyOTP('123456');
```

### Integration Tests

```bash
# Test email verification flow
curl -X POST /vendor/emails/add \
  -H "Content-Type: application/json" \
  -d '{"email":"vendor@example.com"}'

# Test mobile verification
curl -X POST /vendor/mobile/send-otp \
  -H "X-CSRF-TOKEN: token"

# Test hoarding publish
curl -X POST /vendor/hoardings/1/publish \
  -H "X-CSRF-TOKEN: token"
```

### Functional Tests

- [ ] Add email and verify OTP
- [ ] Resend OTP works with rate limiting
- [ ] Mark email as primary
- [ ] Delete non-primary email
- [ ] Cannot delete only verified email
- [ ] Send mobile OTP
- [ ] Verify mobile OTP
- [ ] Publish hoarding without verification (should fail)
- [ ] Publish hoarding with verification (should succeed)

## Step 9: Database Verification

```sql
-- Check vendor_emails table
SELECT * FROM vendor_emails LIMIT 1;

-- Check hoarding status values
SELECT DISTINCT status FROM hoardings;

-- Check users mobile OTP fields
SELECT id, phone, phone_verified_at, mobile_otp_last_sent_at FROM users LIMIT 1;

-- Check direct enquiries with verification
SELECT * FROM direct_enquiries WHERE is_email_verified = 1 AND is_phone_verified = 1;
```

**Verification Checklist:**
- [ ] vendor_emails table has data structure
- [ ] hoardings has new status values
- [ ] users table has mobile OTP fields
- [ ] Sample data can be queried

## Step 10: Monitor & Test

### Monitoring

```bash
# Check for errors
tail -f storage/logs/laravel.log

# Monitor queue
php artisan queue:work

# Test notifications
php artisan tinker
> Notification::send(User::find(1), new EmailVerificationOTPNotification(...))
```

### Test Scenarios

1. **Happy Path - Email Verification**
   - Add email → Get OTP → Enter OTP → Success
   - Expected: Email marked verified

2. **Happy Path - Mobile Verification**
   - Send OTP → Get OTP → Enter OTP → Success
   - Expected: Mobile marked verified

3. **Happy Path - Hoarding Publishing**
   - Create hoarding → Add email → Add mobile → Publish
   - Expected: Hoarding published and auto-approved

4. **Error - Missing Verification**
   - Try to publish without email verification
   - Expected: Error message about email verification

5. **Error - Rate Limiting**
   - Send OTP twice within 1 minute
   - Expected: Second request rejected

6. **Error - Wrong OTP**
   - Enter wrong OTP 5 times
   - Expected: Account locked

**Verification Checklist:**
- [ ] All happy paths work
- [ ] All error cases handled
- [ ] Error messages are clear
- [ ] No database errors

## Step 11: User Communication

Send notification to vendors:

```
Subject: New Email Management & Easy Publishing

Dear Valued Vendor,

We're excited to announce new features:

1. Multiple Email Support
   - Add and manage multiple email addresses
   - Each verified independently via OTP
   - Choose a primary email for notifications

2. Mobile Verification
   - Verify your phone number for security
   - Receive important alerts

3. Instant Hoarding Publishing
   - Publish hoardings instantly
   - Auto-approved, no waiting for admin
   - Preview before going live

Getting Started:
1. Log in to your vendor dashboard
2. Go to "Manage Emails" to add and verify emails
3. Go to "Verify Mobile" to complete mobile verification
4. Create and publish hoardings with one click!

Questions? Contact support@oohapp.com

Best regards,
OOH App Team
```

**Verification Checklist:**
- [ ] Email sent to all vendors
- [ ] Support team briefed
- [ ] FAQ updated
- [ ] Help documentation created

## Post-Deployment

### Monitor First 24 Hours

- [ ] Check error logs for issues
- [ ] Monitor email delivery
- [ ] Monitor OTP generation
- [ ] Check hoarding publications
- [ ] Monitor direct enquiries

### Performance Check

```bash
# Check query performance
php artisan tinker
> DB::enableQueryLog();
> User::with('vendorEmails')->get();
> DB::getQueryLog();
```

- [ ] Queries are optimized
- [ ] No N+1 queries
- [ ] Response times acceptable

### Backup & Recovery

- [ ] Database backup created
- [ ] Code backup created
- [ ] Rollback plan documented
- [ ] Emergency contact list

## Rollback Plan

If critical issues found:

```bash
# Step 1: Stop queue workers
supervisorctl stop all

# Step 2: Rollback migrations
php artisan migrate:rollback --step=3

# Step 3: Remove new files
rm app/Models/VendorEmail.php
rm app/Services/Email*
rm app/Services/Mobile*
rm app/Http/Controllers/Vendor/Email*
rm app/Http/Controllers/Vendor/Mobile*

# Step 4: Remove routes from routes/web.php

# Step 5: Clear cache
php artisan cache:clear
php artisan route:clear

# Step 6: Restart queue workers
supervisorctl start all

# Step 7: Notify team
# Send alert to team about rollback
```

**Verification Checklist:**
- [ ] Rollback tested in staging
- [ ] All old data intact
- [ ] No migration conflicts
- [ ] Application working normally

## Success Criteria

- ✅ All migrations run successfully
- ✅ No database errors
- ✅ All routes accessible
- ✅ Email verification working
- ✅ Mobile verification working
- ✅ Hoarding publishing working
- ✅ Auto-approval working
- ✅ No critical errors in logs
- ✅ Vendors notified
- ✅ Support team trained

## Contacts & Escalation

- **Dev Lead**: [Name/Email]
- **DevOps**: [Name/Email]
- **Product**: [Name/Email]
- **Support**: [Name/Email]

## Sign-Off

- [ ] Development Lead: _____________ Date: _______
- [ ] QA Lead: _____________ Date: _______
- [ ] DevOps: _____________ Date: _______
- [ ] Product Manager: _____________ Date: _______

---

**Deployment completed at:** _______________
**Deployed by:** _______________
**Approved by:** _______________

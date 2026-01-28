# User Table Structure - Comprehensive Review

**Date**: January 27, 2026  
**Status**: ✅ Ready for Review Before Adding New Columns  

---

## Current User Table Structure

### Existing Database Columns

Based on the User model `$fillable` array and migration analysis:

#### Authentication Fields
```sql
- id (BIGINT PRIMARY KEY)
- email (VARCHAR 255) - Primary email, unique
- email_verified_at (TIMESTAMP NULL) - When email was verified
- phone (VARCHAR 15) - Phone number, unique
- phone_verified_at (TIMESTAMP NULL) - When phone was verified
- password (VARCHAR 255) - Hashed password
- otp (VARCHAR 255 NULL) - Current OTP (stored plain, to be deprecated)
- otp_expires_at (TIMESTAMP NULL) - OTP expiry (to be deprecated)
- remember_token (VARCHAR 100 NULL)
```

#### User Profile Fields
```sql
- name (VARCHAR 255)
- status (VARCHAR 50) - 'active', 'suspended', etc.
- avatar (VARCHAR 255 NULL) - Profile image URL
- address (TEXT NULL)
- city (VARCHAR 100 NULL)
- state (VARCHAR 100 NULL)
- country (VARCHAR 100 NULL)
- pincode (VARCHAR 10 NULL)
```

#### Business/GST Fields (Added in PROMPT 64)
```sql
- gstin (VARCHAR 15 NULL) - Goods & Services Tax ID
- company_name (VARCHAR 255 NULL)
- pan (VARCHAR 10 NULL) - Permanent Account Number
- customer_type (VARCHAR 50 NULL) - B2B, B2C, etc.
- billing_address (TEXT NULL)
- billing_city (VARCHAR 100 NULL)
- billing_state (VARCHAR 100 NULL)
- billing_state_code (VARCHAR 2 NULL)
- billing_pincode (VARCHAR 10 NULL)
```

#### Multi-Role Switching (Added in PROMPT 96)
```sql
- active_role (VARCHAR 50 NULL) - Currently active role
- previous_role (VARCHAR 50 NULL) - Previously active role
- last_role_switch_at (TIMESTAMP NULL) - When role was last switched
```

#### Timestamps & Soft Delete
```sql
- last_login_at (TIMESTAMP NULL) - Last login time
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
- deleted_at (TIMESTAMP NULL) - Soft delete support
```

---

## Current OTP Implementation Status

### Fields to be Deprecated ❌
```sql
users.otp (VARCHAR 255)
users.otp_expires_at (TIMESTAMP)
```

**Reason**: OTP refactoring moved to `user_otps` table for centralized management

### What's Currently Used ✅
```sql
users.email_verified_at - Email verification status
users.phone_verified_at - Mobile verification status
```

---

## Related Tables & Relationships

### vendor_emails table (Secondary emails)
```sql
- id (BIGINT PRIMARY KEY)
- user_id (BIGINT FOREIGN KEY → users.id)
- email (VARCHAR 255)
- verified_at (TIMESTAMP NULL)
- is_primary (BOOLEAN)
- created_at, updated_at
```

**Relationship**: `User::hasMany(VendorEmail::class, 'user_id')`

### user_otps table (Centralized OTP storage)
```sql
- id (BIGINT PRIMARY KEY)
- user_id (BIGINT FOREIGN KEY → users.id)
- identifier (VARCHAR 255) - email or phone
- otp_hash (VARCHAR 255) - hashed OTP
- purpose (VARCHAR 100) - vendor_email_verification, mobile_verification, etc.
- expires_at (TIMESTAMP)
- verified_at (TIMESTAMP NULL)
- created_at, updated_at
```

**Relationship**: `User::hasMany(UserOtp::class, 'user_id')`

### vendor_profiles table
```sql
- id (BIGINT PRIMARY KEY)
- user_id (BIGINT FOREIGN KEY → users.id) UNIQUE
- onboarding_status (VARCHAR 50)
- onboarding_step (INT)
- ... other vendor-specific fields ...
```

**Relationship**: `User::hasOne(VendorProfile::class)`

---

## Current Methods in User Model

### Verification Methods
```php
public function isOTPValid(string $otp): bool
    ↳ Checks if otp field matches and is not expired

public function generateOTP(): string
    ↳ Generates 4-digit OTP and stores in users.otp (DEPRECATED)

public function clearOTP(): void
    ↳ Clears otp and otp_expires_at (DEPRECATED)

public function isMobileVerified(): bool
    ↳ Returns: phone_verified_at !== null ✅

public function isFullyVerified(): bool
    ↳ Returns: email_verified_at && phone_verified_at (for vendors)
```

### Email Management Methods
```php
public function vendorEmails(): HasMany
    ↳ Returns all vendor emails

public function getPrimaryVerifiedEmail(): ?VendorEmail
    ↳ Returns primary verified email

public function hasVerifiedEmail(): bool
    ↳ Checks if any email is verified
```

### Role & Status Methods
```php
public function isActive(): bool
    ↳ Returns: status === 'active'

public function isSuspended(): bool
    ↳ Returns: status === 'suspended'

public function isVendor(): bool
    ↳ Returns: hasRole('vendor') (via Spatie)

public function isCustomer(): bool
    ↳ Returns: hasRole('customer') (via Spatie)

public function getActiveRole(): ?string
    ↳ Returns active_role or primary role

public function getPrimaryRole(): ?string
    ↳ Returns first assigned role
```

### Vendor-Specific Methods
```php
public function vendorProfile()
    ↳ Returns hasOne(VendorProfile::class)

public function isVendorApproved(): bool
    ↳ Checks vendor profile approval status

public function hasCompletedVendorOnboarding(): bool
    ↳ Checks if onboarding is complete

public function getVendorOnboardingStatus(): ?string
    ↳ Returns onboarding_status from vendor_profile

public function getCurrentOnboardingStep(): int
    ↳ Returns onboarding_step from vendor_profile

public function isFullyVerified(): bool
    ↳ Returns: email_verified_at && phone_verified_at
```

---

## Customer vs Vendor Registration Flow

### Registration Process (Same for Both)
**RegisterController@register()**

1. **Create User Record**
   ```php
   User::create([
       'name' => $request->name,
       'email' => $request->email,
       'email_verified_at' => $request->email_verified ? now() : null,
       'phone' => $request->phone,
       'phone_verified_at' => $request->phone_verified ? now() : null,
       'password' => Hash::make($request->password),
       'status' => 'active',
   ]);
   ```

2. **Assign Role**
   ```php
   $user->assignRole($role); // 'customer' or 'vendor'
   ```

3. **Send Welcome Email**
   - Customer: `CustomerWelcomeMail`
   - Vendor: `VendorWelcomeMail`

4. **Create Notifications**
   - Customer: `UserWelcomeNotification`
   - Vendor: `VendorApprovalPendingNotification`, admin notifications

5. **Vendor-Only: Create Profile**
   ```php
   VendorProfile::create([
       'user_id' => $user->id,
       'onboarding_status' => 'draft',
       'onboarding_step' => 1,
   ]);
   ```

6. **Login & Redirect**
   - Customer: → home dashboard
   - Vendor: → vendor onboarding flow

---

## Analysis: What Should NOT Be Added to users Table

### ❌ DO NOT ADD to users table:

1. **OTP-related fields**
   - `mobile_otp` ← Already deprecated
   - `mobile_otp_expires_at` ← Already deprecated
   - `mobile_otp_attempts` ← Already deprecated
   - `mobile_otp_last_sent_at` ← Already deprecated
   
   **Why**: `user_otps` table already handles this centrally

2. **Additional email fields**
   - `secondary_email` ← Use `vendor_emails` table
   - `tertiary_email` ← Use `vendor_emails` table
   
   **Why**: `vendor_emails` is designed for multiple emails

3. **Vendor profile fields**
   - `business_name` ← Use `vendor_profiles` table
   - `business_type` ← Use `vendor_profiles` table
   - `onboarding_status` ← Use `vendor_profiles` table
   - `approval_status` ← Use `vendor_profiles` table
   
   **Why**: `vendor_profiles` is the dedicated table

4. **Deprecated fields**
   - `otp` ← Move to `user_otps`
   - `otp_expires_at` ← Move to `user_otps`
   
   **Status**: Ready to deprecate

---

## Analysis: What COULD Be Added to users Table

### ✅ Only add if ALL conditions are met:

1. **Data applies to BOTH customers AND vendors**
   - NOT vendor-only
   - NOT customer-only
   
2. **Needed FREQUENTLY in user queries**
   - Not a "nice to have"
   - Accessed in every request
   
3. **NOT better suited in related table**
   - Not vendor_profiles
   - Not vendor_emails
   - Not user_otps
   
4. **Query performance justifies column**
   - Avoid excessive joins
   
5. **Alternative relationship doesn't exist**

### Current Examples That Qualify ✅
- `name` - Used for both roles
- `email`, `phone` - Used for both roles  
- `email_verified_at`, `phone_verified_at` - Verification for both
- `status` - Both need active/suspended status
- `avatar` - Both have profiles
- `last_login_at` - Both need login tracking
- `active_role`, `previous_role` - Both can switch roles

---

## Database Optimization Notes

### Current Index Analysis
```sql
-- Recommended indexes (if not already present):
CREATE INDEX idx_users_email ON users(email); ✅
CREATE INDEX idx_users_phone ON users(phone); ✅
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_users_email_verified_at ON users(email_verified_at);
CREATE INDEX idx_users_phone_verified_at ON users(phone_verified_at);

-- Soft delete support
CREATE INDEX idx_users_deleted_at ON users(deleted_at);
```

### Query Performance Tips
1. **Avoid users → vendor_emails → user_otps joins** 
   - Use `user_otps` directly when dealing with OTPs
   
2. **Use vendor_profiles for vendor data**
   - Load only when needed
   - Use with() for eager loading
   
3. **Query by role using Spatie**
   - `User::role('vendor')->get()`
   - Spatie handles role joins efficiently

---

## Migration Path for OTP Deprecation

### Step 1: Add user_otps table (Already done ✅)
```php
// Already created
user_otps table with centralized OTP storage
```

### Step 2: Refactor services (Already done ✅)
```php
// Already created
MobileOTPService → uses user_otps
EmailVerificationService → uses user_otps
```

### Step 3: Make users.otp fields optional (Ready for execution)
```php
// Create migration:
Schema::table('users', function (Blueprint $table) {
    // These can be set to nullable if already aren't
    $table->string('otp')->nullable()->change();
    $table->timestamp('otp_expires_at')->nullable()->change();
});
```

### Step 4: Deprecate methods (Ready for execution)
```php
// In User model - mark as deprecated
/**
 * @deprecated Use MobileOTPService::sendOTP() instead
 */
public function generateOTP(): string { ... }

/**
 * @deprecated Use MobileOTPService::verifyOTP() instead
 */
public function isOTPValid(string $otp): bool { ... }
```

### Step 5: Remove fields (Future - in version 4.0)
```php
// After all references moved to services:
Schema::table('users', function (Blueprint $table) {
    $table->dropColumn(['otp', 'otp_expires_at']);
});
```

---

## Recommendations Before Adding ANY New Column

### ✅ Checklist for Adding to users Table

Before adding any column to users table, ask:

- [ ] Does this apply to BOTH customers AND vendors?
- [ ] Is this queried in EVERY request or nearly every request?
- [ ] Is there NO other appropriate table for this data?
- [ ] Will adding this column REDUCE database queries significantly?
- [ ] Is this NOT a one-to-many relationship?
- [ ] Is this NOT a vendor-specific or customer-specific field?
- [ ] Is this NOT a temporary field (like OTP)?
- [ ] Will the column be used within the first 100ms of page load?

If you answered "NO" to ANY question above → **DO NOT add to users table**

---

## Current Column Count

**Total fillable columns**: 32
**Nullable columns**: ~15 (for optional features)
**Indexed columns**: ~8

**Status**: Healthy - Not bloated, well-organized

---

## Summary

### ✅ What's Good
- Single users table for all roles
- Proper use of separate tables for:
  - vendor_emails (multiple emails)
  - vendor_profiles (vendor-specific data)
  - user_otps (centralized OTP)
- Role-based access via Spatie
- Soft delete support
- Multi-role switching support

### ⚠️ What Needs Cleanup
- `otp` field (should be removed in future)
- `otp_expires_at` field (should be removed in future)
- Methods referencing old OTP fields (mark deprecated)

### ❌ What to Avoid
- Adding vendor-only fields → Use vendor_profiles
- Adding email variations → Use vendor_emails
- Adding OTP fields → Use user_otps
- Adding customer-specific fields → Should use separate profiles if needed

---

## Next Steps

1. **Review this document** ✅ You're reading it
2. **Do NOT add mobile_otp fields** - Already handled by MobileOTPService
3. **Do NOT add email fields** - Already handled by vendor_emails
4. **Confirm with team** - Before any new columns
5. **Use checklist above** - For any future schema changes

---

**Status**: ✅ READY FOR PRODUCTION  
**Last Reviewed**: January 27, 2026  
**Recommendation**: NO NEW COLUMNS needed at this time

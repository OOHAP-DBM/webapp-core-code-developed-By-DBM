# Vendor SLA Tracking System - PROMPT 68

## Overview

The Vendor SLA Tracking System monitors vendor performance in responding to enquiries and submitting quotes. It automatically tracks violations, adjusts vendor reliability scores, and provides comprehensive reporting.

## Key Features

### 1. SLA Configuration
- **3 Pre-configured Tiers**: Default (24h/48h), Premium (12h/24h), Relaxed (48h/96h)
- **Custom SLA Settings**: Admins can create unlimited custom SLA configurations
- **Business Hours Support**: Optional calculation mode respecting work hours and holidays
- **Grace Periods**: Configurable grace period before marking violations
- **Warning Thresholds**: Automatic warnings when approaching deadlines (default: 75%)

### 2. Reliability Scoring
- **Scale**: 0-100 (vendors start at 100.00)
- **Penalty System**:
  - Minor violations: -1.00 points (within grace period)
  - Major violations: -5.00 points (beyond grace period)
  - Critical violations: -10.00 points (repeated or severe delays)
- **Recovery Mechanism**: +0.50 points per day with no violations (30-day recovery period)
- **Tiers**:
  - Excellent: 90-100
  - Good: 75-89
  - Average: 60-74
  - Poor: 40-59
  - Critical: <40

### 3. Violation Tracking
- **Types**: Enquiry acceptance late, quote submission late, quote revision late, no response
- **Statuses**: Pending, confirmed, disputed, resolved, waived, escalated
- **Vendor Disputes**: Vendors can challenge violations with explanations
- **Admin Review**: Admins can waive violations or resolve disputes

### 4. Automated Monitoring
- **Hourly Checks**: Scheduled task monitors all pending deadlines
- **Warning Notifications**: Sent when 75% of deadline elapsed
- **Violation Notifications**: Sent to vendor and admin (for critical)
- **Daily Recovery**: Compliant vendors gain points back
- **Monthly Reset**: Violation counts reset on 1st of month

## Database Schema

### vendor_sla_settings (24 columns)
Configuration table for different SLA tiers.

**Key Fields**:
- `name`, `description`: Setting identification
- `enquiry_acceptance_hours`: Time vendor has to accept enquiry (default: 24)
- `quote_submission_hours`: Time vendor has to submit quote (default: 48)
- `warning_threshold_percentage`: When to send warning (default: 75%)
- `grace_period_hours`: Grace before marking violation (default: 2)
- `minor_violation_penalty`: Points deducted (default: 1.00)
- `major_violation_penalty`: Points deducted (default: 5.00)
- `critical_violation_penalty`: Points deducted (default: 10.00)
- `auto_mark_violated`: Auto-confirm violations
- `auto_notify_vendor`: Auto-send notifications
- `reliability_recovery_days`: Clean period before recovery (default: 30)
- `recovery_rate_per_day`: Points recovered daily (default: 0.50)
- `applies_to`: Target vendor category (all/new/verified/premium)

### vendor_sla_violations (40+ columns)
Comprehensive violation tracking.

**Key Fields**:
- `vendor_id`, `sla_setting_id`: References
- `violatable_type`, `violatable_id`: Polymorphic relation (QuoteRequest or VendorQuote)
- `violation_type`: Type of violation
- `severity`: minor/major/critical
- `deadline`, `actual_time`: Timing details
- `delay_hours`, `delay_minutes`: Calculated delay
- `penalty_points`: Points deducted
- `reliability_score_before`, `reliability_score_after`: Score impact
- `status`: Current violation status
- `vendor_explanation`: Vendor's dispute reason
- `vendor_dispute_status`: Dispute state
- `reviewed_by`, `admin_notes`: Admin review
- `waived_by`, `waiver_reason`: Admin waiver

### users (18 new columns)
Vendor reliability metrics.

**Key Fields**:
- `reliability_score`: 0-100 scale (starts at 100.00)
- `reliability_tier`: excellent/good/average/poor/critical
- `sla_violations_count`: Total violations
- `sla_violations_this_month`: Current month violations
- `total_penalty_points`: Cumulative penalty points
- `enquiries_accepted_count`: Performance tracking
- `quotes_submitted_count`: Performance tracking
- `on_time_acceptance_rate`: % accepted on time
- `on_time_quote_rate`: % submitted on time
- `avg_acceptance_time_hours`: Average response time
- `avg_quote_time_hours`: Average quote time
- `vendor_sla_setting_id`: Custom SLA override

### quote_requests (7 new columns)
SLA tracking for RFPs.

**Key Fields**:
- `vendor_notified_at`: When vendor was notified
- `sla_acceptance_deadline`: When vendor must accept
- `sla_quote_deadline`: When vendor must submit quote
- `vendor_accepted_at`: When vendor accepted
- `sla_acceptance_violated`: Acceptance deadline missed
- `sla_quote_violated`: Quote deadline missed
- `sla_setting_id`: SLA configuration used

### vendor_quotes (4 new columns)
SLA tracking for quotes.

**Key Fields**:
- `sla_submission_deadline`: When quote was due
- `sla_violated`: Quote submitted late
- `sla_violation_time`: When violation occurred
- `sla_setting_id`: SLA configuration used

## Workflow

### 1. Quote Request Published
```
Customer creates QuoteRequest
    ↓
System notifies vendor (vendor_notified_at set)
    ↓
SLATrackingService::setSLADeadlinesForQuoteRequest()
    ↓
- Get vendor's SLA setting (custom > category > default)
- Calculate sla_acceptance_deadline (notified_at + acceptance_hours)
- Save to quote_requests table
```

### 2. Vendor Accepts Enquiry
```
Vendor accepts QuoteRequest
    ↓
SLATrackingService::handleVendorAcceptance()
    ↓
- Set vendor_accepted_at
- Check if acceptance was on time
- Calculate sla_quote_deadline (accepted_at + quote_hours)
- Update on-time acceptance stats
```

### 3. Vendor Submits Quote
```
Vendor submits VendorQuote
    ↓
SLATrackingService::handleQuoteSubmission()
    ↓
- Set sent_at
- Check if submission was on time
- Update on-time quote stats
- Mark sla_violated if late
```

### 4. Automated Monitoring (Hourly)
```
MonitorVendorSLAs command runs
    ↓
SLATrackingService::monitorPendingDeadlines()
    ↓
For each pending acceptance:
  - Check if deadline passed → recordAcceptanceViolation()
  - Check if in warning zone → sendAcceptanceWarning()
    ↓
For each pending quote:
  - Check if deadline passed → recordQuoteViolation()
  - Check if in warning zone → sendQuoteWarning()
```

### 5. Violation Recording
```
Violation detected
    ↓
- Calculate delay (hours, minutes)
- Get vendor's violation count this month
- Determine severity (minor/major/critical)
- Calculate penalty points
- Create VendorSLAViolation record
- Update quote_requests/vendor_quotes (mark violated)
- Auto-confirm if configured
- Send notifications (vendor, admin if critical)
- Auto-escalate if critical
```

### 6. Vendor Disputes Violation
```
Vendor challenges violation
    ↓
VendorSLAViolation::dispute($explanation)
    ↓
- Status → disputed
- vendor_explanation saved
- vendor_dispute_status → disputed
- Admin notified for review
```

### 7. Admin Reviews Dispute
```
Admin reviews dispute
    ↓
VendorSLAViolation::resolveDispute($admin, $accepted, $notes)
    ↓
If accepted:
  - Status → waived
  - Reverse penalty (add points back)
  - vendor_dispute_status → accepted
If rejected:
  - Status → confirmed
  - Apply penalty (if not already applied)
  - vendor_dispute_status → rejected
```

### 8. Daily Recovery (Midnight)
```
SLATrackingService::processDailyRecovery()
    ↓
For each vendor with score < 100:
  - Get last violation date
  - If clean for >= recovery_days (30):
    - Add recovery_rate_per_day (0.50) to score
    - Update reliability_tier
    - Cap at 100.00
```

### 9. Monthly Reset (1st of Month)
```
SLATrackingService::resetMonthlyViolationCounts()
    ↓
Reset sla_violations_this_month = 0 for all vendors
```

## API Endpoints

### Admin Routes
```php
// SLA Settings Management
GET    /api/admin/sla-settings              // List all SLA settings
POST   /api/admin/sla-settings              // Create new setting
GET    /api/admin/sla-settings/{id}         // View setting details
PUT    /api/admin/sla-settings/{id}         // Update setting
DELETE /api/admin/sla-settings/{id}         // Delete setting
POST   /api/admin/sla-settings/{id}/default // Set as default
POST   /api/admin/sla-settings/{id}/toggle  // Toggle active status
GET    /api/admin/sla-settings/statistics   // Get SLA statistics

// Vendor Reliability Management
GET    /api/admin/vendor-reliability         // List vendors with scores
GET    /api/admin/vendor-reliability/{id}    // Vendor details
GET    /api/admin/vendor-reliability/{id}/violations // Vendor violations
POST   /api/admin/vendor-reliability/{id}/adjust-score // Manual adjustment
POST   /api/admin/vendor-reliability/{id}/assign-sla  // Assign custom SLA
DELETE /api/admin/vendor-reliability/{id}/assign-sla  // Remove custom SLA
GET    /api/admin/vendor-reliability/at-risk         // Vendors at risk
GET    /api/admin/vendor-reliability/top-performers  // Top vendors
GET    /api/admin/vendor-reliability/export          // Export report

// Violation Management
GET    /api/admin/sla-violations              // List all violations
GET    /api/admin/sla-violations/{id}         // View violation
POST   /api/admin/sla-violations/{id}/waive   // Waive violation
POST   /api/admin/sla-violations/{id}/resolve-dispute // Resolve dispute
POST   /api/admin/sla-violations/{id}/escalate // Escalate violation
POST   /api/admin/sla-violations/{id}/resolve  // Mark resolved
GET    /api/admin/sla-violations/pending      // Pending violations
GET    /api/admin/sla-violations/disputed     // Disputed violations
GET    /api/admin/sla-violations/critical     // Critical violations
```

### Vendor Routes
```php
// Vendor Performance Dashboard
GET  /api/vendor/sla-performance              // Dashboard overview
GET  /api/vendor/sla-performance/violations   // My violations
GET  /api/vendor/sla-performance/violations/{id} // View violation
POST /api/vendor/sla-performance/violations/{id}/dispute // Dispute violation
GET  /api/vendor/sla-performance/statistics   // My statistics
GET  /api/vendor/sla-performance/history      // Performance history
```

## Service Methods

### VendorSLASetting Model

```php
// Static Methods
VendorSLASetting::getDefault()                    // Get default setting
VendorSLASetting::getForVendor(User $vendor)      // Get vendor's SLA

// Deadline Calculations
$setting->calculateAcceptanceDeadline(Carbon $notifiedAt)
$setting->calculateQuoteDeadline(Carbon $acceptedAt)
$setting->calculateResponseDeadline(Carbon $notifiedAt)
$setting->calculateWarningTime(Carbon $deadline)

// Violation Analysis
$setting->isInWarningZone(Carbon $deadline)
$setting->calculateDelay(Carbon $deadline, Carbon $actualTime)
$setting->calculateViolationSeverity(int $delayHours, int $violationCount)
$setting->getPenaltyPoints(string $severity)

// Business Hours
$setting->addBusinessHours(Carbon $start, int $hours)
$setting->isExcludedDay(Carbon $date)
```

### VendorSLAViolation Model

```php
// Violation Management
$violation->confirm()                    // Confirm and apply penalty
$violation->applyPenalty()               // Deduct points from vendor
$violation->waive(User $admin, string $reason) // Waive violation
$violation->dispute(string $explanation) // Vendor disputes
$violation->resolveDispute(User $admin, bool $accepted, string $notes)
$violation->escalate()                   // Escalate to admin
$violation->resolve(string $notes)       // Mark resolved

// Helper Methods
$violation->getDelayFormatted()          // "2 hours 15 minutes"
$violation->isWithinGracePeriod()        // Check grace period
$violation->isCritical()                 // Check if critical
$violation->getSeverityColor()           // UI color
$violation->getStatusColor()             // UI color
```

### SLATrackingService

```php
// Core Monitoring
$service->monitorPendingDeadlines()           // Check all deadlines
$service->checkAcceptanceDeadlines()          // Check acceptance deadlines
$service->checkQuoteDeadlines()               // Check quote deadlines

// Lifecycle Hooks
$service->setSLADeadlinesForQuoteRequest(QuoteRequest $request)
$service->handleVendorAcceptance(QuoteRequest $request)
$service->handleQuoteSubmission(VendorQuote $quote)

// Maintenance
$service->processDailyRecovery()              // Daily recovery points
$service->resetMonthlyViolationCounts()       // Monthly reset
```

### User Model (Vendor Methods)

```php
// Relationships
$vendor->slaViolations()                     // HasMany violations
$vendor->customSLASetting()                  // BelongsTo custom SLA

// Reliability Management
$vendor->updateReliabilityTier()             // Update tier based on score
$vendor->getReliabilityTierColor()           // UI color
$vendor->getReliabilityScorePercentage()     // Formatted score
$vendor->isReliable()                        // Score >= 75
$vendor->hasCriticalReliability()            // Score < 40
$vendor->isAtRisk()                          // Check risk factors
$vendor->getPerformanceSummary()             // Full performance data
```

## Scheduled Tasks

Configure in `routes/console.php`:

```php
// Monitor SLA deadlines (hourly)
Schedule::command('sla:monitor')
    ->hourly()
    ->name('monitor-vendor-slas')
    ->withoutOverlapping(10)
    ->onOneServer();

// Daily recovery (midnight)
Schedule::call(function () {
    app(SLATrackingService::class)->processDailyRecovery();
})->daily()->at('00:00');

// Monthly reset (1st of month)
Schedule::call(function () {
    app(SLATrackingService::class)->resetMonthlyViolationCounts();
})->monthlyOn(1, '00:00');
```

## Notifications

### SLAWarningNotification
- **Trigger**: 75% of deadline elapsed (configurable)
- **Recipients**: Vendor
- **Channels**: Email, Database
- **Content**: Deadline approaching warning

### SLAViolationNotification
- **Trigger**: Deadline missed
- **Recipients**: Vendor
- **Channels**: Email, Database
- **Content**: Violation details, penalty impact

### SLACriticalViolationNotification
- **Trigger**: Critical severity violation
- **Recipients**: Admins
- **Channels**: Email, Database
- **Content**: Vendor details, violation severity, requires review

## Integration Points

### With QuoteRequest Model
Add these methods to `QuoteRequest` model:

```php
public function setSLADeadlines()
{
    app(SLATrackingService::class)->setSLADeadlinesForQuoteRequest($this);
}

public function acceptEnquiry()
{
    $this->update(['vendor_accepted_at' => now()]);
    app(SLATrackingService::class)->handleVendorAcceptance($this);
}

public function checkAcceptanceSLA(): bool
{
    if (!$this->sla_acceptance_deadline) return true;
    return $this->vendor_accepted_at <= $this->sla_acceptance_deadline;
}

public function getAcceptanceStatus(): string
{
    if (!$this->vendor_accepted_at) return 'pending';
    if ($this->sla_acceptance_violated) return 'violated';
    return $this->checkAcceptanceSLA() ? 'on_time' : 'late';
}
```

### With VendorQuote Model
Add these methods to `VendorQuote` model:

```php
public function checkSubmissionSLA(): bool
{
    $request = $this->quoteRequest;
    if (!$request || !$request->sla_quote_deadline) return true;
    return $this->sent_at <= $request->sla_quote_deadline;
}

public function getSubmissionStatus(): string
{
    if ($this->sla_violated) return 'violated';
    return $this->checkSubmissionSLA() ? 'on_time' : 'late';
}
```

## Deployment Checklist

### 1. Run Migrations
```bash
php artisan migrate
```
This creates:
- `vendor_sla_settings` table (with 3 default settings)
- `vendor_sla_violations` table
- Modifies `quote_requests` table (7 columns)
- Modifies `vendor_quotes` table (4 columns)
- Modifies `users` table (18 columns, initializes existing vendors)

### 2. Schedule Commands
Ensure Laravel scheduler is running:
```bash
# Add to crontab
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Configure Notifications
Set up notification channels in `config/mail.php`:
- Email notifications for warnings and violations
- Database notifications for in-app alerts

### 4. Admin Permissions
Assign permissions for SLA management:
- `manage-sla-settings`
- `view-vendor-reliability`
- `waive-violations`
- `adjust-vendor-scores`

### 5. Testing
Run comprehensive tests:
```bash
# Test SLA monitoring
php artisan sla:monitor

# Check default settings loaded
php artisan tinker
>>> VendorSLASetting::all()

# Verify vendor initialization
>>> User::where('role', 'vendor')->first()->reliability_score
```

## Usage Examples

### Example 1: Create Custom SLA Setting
```php
$premiumSetting = VendorSLASetting::create([
    'name' => 'Premium Vendors',
    'description' => 'Stricter SLA for premium vendors',
    'enquiry_acceptance_hours' => 12,
    'quote_submission_hours' => 24,
    'warning_threshold_percentage' => 80,
    'grace_period_hours' => 1,
    'minor_violation_penalty' => 2.00,
    'major_violation_penalty' => 7.00,
    'critical_violation_penalty' => 15.00,
    'applies_to' => 'premium',
    'is_active' => true,
]);
```

### Example 2: Assign Custom SLA to Vendor
```php
$vendor = User::find($vendorId);
$vendor->update([
    'vendor_sla_setting_id' => $premiumSetting->id
]);
```

### Example 3: Handle Quote Request Publication
```php
// In QuoteRequestController@publish
$quoteRequest->update(['status' => 'published']);
app(SLATrackingService::class)->setSLADeadlinesForQuoteRequest($quoteRequest);
```

### Example 4: Handle Vendor Acceptance
```php
// In QuoteRequestController@accept
$quoteRequest->update([
    'status' => 'accepted',
    'vendor_accepted_at' => now()
]);
app(SLATrackingService::class)->handleVendorAcceptance($quoteRequest);
```

### Example 5: Handle Quote Submission
```php
// In VendorQuoteController@submit
$quote->update(['sent_at' => now()]);
app(SLATrackingService::class)->handleQuoteSubmission($quote);
```

### Example 6: Vendor Disputes Violation
```php
$violation = VendorSLAViolation::find($violationId);
$violation->dispute('Internet outage prevented timely response');
```

### Example 7: Admin Waives Violation
```php
$violation->waive(auth()->user(), 'Legitimate emergency situation');
```

### Example 8: Get Vendor Performance Summary
```php
$vendor = User::find($vendorId);
$summary = $vendor->getPerformanceSummary();
/*
[
  'reliability_score' => 95.50,
  'reliability_tier' => 'excellent',
  'total_violations' => 2,
  'monthly_violations' => 0,
  'on_time_acceptance_rate' => 98.50,
  'on_time_quote_rate' => 96.00,
  'avg_acceptance_time' => 8.5,
  'avg_quote_time' => 18.2,
  'last_violation' => '2025-01-15 14:30:00'
]
*/
```

## Troubleshooting

### Violations Not Being Detected
- Check scheduled task is running: `php artisan schedule:list`
- Manually run monitor: `php artisan sla:monitor`
- Verify deadlines set: Check `quote_requests.sla_acceptance_deadline`

### Notifications Not Sending
- Check notification configuration in `config/mail.php`
- Verify queue is running if using queues
- Check notification logs: `storage/logs/laravel.log`

### Scores Not Updating
- Check violation status: Should be "confirmed" to apply penalty
- Verify auto_mark_violated in SLA setting
- Manually confirm violation: `$violation->confirm()`

### Recovery Not Working
- Check last_sla_violation_at date
- Verify recovery_days setting (default: 30)
- Manually trigger: `app(SLATrackingService::class)->processDailyRecovery()`

## Performance Considerations

- **Indexes**: All foreign keys and frequently queried columns indexed
- **Pagination**: All listing endpoints paginated (default: 20 per page)
- **Eager Loading**: Relations loaded with `with()` to prevent N+1
- **Queue Jobs**: Notifications sent via queue for better performance
- **Scheduled Tasks**: Run with `withoutOverlapping()` to prevent concurrent runs

## Security

- **Authorization**: All admin routes require admin role
- **Vendor Isolation**: Vendors can only view own violations
- **Dispute Validation**: Max 1000 chars to prevent abuse
- **Score Adjustments**: Limited to ±50 points
- **Audit Trail**: All actions logged with admin IDs

## Future Enhancements

1. **Email/SMS Reminders**: Multiple reminders before deadline
2. **Vendor Dashboard Widget**: Real-time SLA countdown
3. **Customer Impact Tracking**: Link violations to customer complaints
4. **Automated Tier Adjustment**: Auto-upgrade/downgrade vendor tiers
5. **SLA Templates**: Pre-built templates for common scenarios
6. **Bulk Operations**: Bulk waive, bulk assign SLA settings
7. **Export/Import**: CSV export of violations, settings
8. **Analytics Dashboard**: Charts, trends, predictions
9. **Mobile App Integration**: Push notifications for vendors
10. **Webhook Support**: Notify external systems of violations

---

**Documentation Version**: 1.0  
**Created**: 2025-12-11  
**PROMPT**: 68 - Vendor SLA Tracking System  
**Author**: AI Assistant (GitHub Copilot)

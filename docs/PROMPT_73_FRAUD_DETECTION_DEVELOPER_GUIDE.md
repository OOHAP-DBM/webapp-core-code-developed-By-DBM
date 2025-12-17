# PROMPT 73: Fraud Detection & Risk Management System
## Developer Documentation

**Implementation Date:** December 11, 2024  
**Version:** 1.0.0  
**Status:** ✅ Production Ready

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [Core Services](#core-services)
5. [Fraud Detection Rules](#fraud-detection-rules)
6. [Risk Scoring Algorithm](#risk-scoring-algorithm)
7. [Event Logging](#event-logging)
8. [Admin Dashboard](#admin-dashboard)
9. [Scheduled Tasks](#scheduled-tasks)
10. [Integration Guide](#integration-guide)
11. [API Reference](#api-reference)
12. [Testing](#testing)
13. [Deployment](#deployment)

---

## 🎯 Overview

The Fraud Detection System is a comprehensive risk management solution that automatically identifies suspicious activities, calculates user risk scores, and manages fraud alerts across the OohApp platform.

### Key Features

- **6 Automated Fraud Checks** - High-value frequency, GST mismatch, payment failures, suspicious patterns, velocity anomalies, amount spikes
- **Risk Scoring (0-100)** - Dynamic risk calculation based on user behavior
- **Auto-Blocking** - Automatic account suspension for critical fraud
- **Event Logging** - Complete audit trail of all user activities
- **Admin Dashboard** - Real-time monitoring and management interface
- **Scheduled Monitoring** - Hourly checks, daily notifications, weekly cleanup

### Business Impact

- **Fraud Prevention** - Detect and block fraudulent bookings before payment
- **Risk Mitigation** - Identify high-risk users early in their journey
- **Compliance** - GST verification and audit trail for regulatory requirements
- **Revenue Protection** - Prevent chargebacks and payment fraud

---

## 🏗️ Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐    │
│  │   Booking    │  │   Payment    │  │     Auth     │    │
│  │  Controller  │  │  Controller  │  │  Controller  │    │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘    │
│         │                 │                 │             │
│         └─────────────────┼─────────────────┘             │
│                           │                               │
│                  ┌────────▼────────┐                      │
│                  │ BookingObserver │                      │
│                  └────────┬────────┘                      │
│                           │                               │
├───────────────────────────┼───────────────────────────────┤
│                Service Layer                              │
├───────────────────────────┼───────────────────────────────┤
│                           │                               │
│         ┌─────────────────▼─────────────────┐            │
│         │  FraudDetectionService            │            │
│         │  - checkBooking()                 │            │
│         │  - checkHighValueFrequency()      │            │
│         │  - checkGSTMismatch()             │            │
│         │  - checkPaymentFailures()         │            │
│         │  - checkSuspiciousPatterns()      │            │
│         │  - checkVelocityAnomaly()         │            │
│         │  - checkAmountSpike()             │            │
│         └─────────────────┬─────────────────┘            │
│                           │                               │
│         ┌─────────────────▼─────────────────┐            │
│         │  FraudEventLogger                 │            │
│         │  - logBookingAttempt()            │            │
│         │  - logPaymentAttempt()            │            │
│         │  - logSuspiciousActivity()        │            │
│         └─────────────────┬─────────────────┘            │
│                           │                               │
├───────────────────────────┼───────────────────────────────┤
│                  Data Layer                               │
├───────────────────────────┼───────────────────────────────┤
│                           │                               │
│  ┌────────────┐  ┌───────▼──────┐  ┌─────────────┐      │
│  │   Fraud    │  │    Fraud     │  │    Risk     │      │
│  │   Alerts   │  │    Events    │  │  Profiles   │      │
│  └────────────┘  └──────────────┘  └─────────────┘      │
│                                                           │
│  ┌────────────┐  ┌──────────────┐                       │
│  │  Payment   │  │     GST      │                       │
│  │ Anomalies  │  │Verifications │                       │
│  └────────────┘  └──────────────┘                       │
│                                                           │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

1. **User Action** → Controller receives booking/payment request
2. **Observer Trigger** → BookingObserver intercepts creation
3. **Fraud Check** → FraudDetectionService runs all checks
4. **Alert Creation** → FraudAlert created if suspicious activity detected
5. **Event Logging** → FraudEventLogger records the event
6. **Risk Update** → RiskProfile recalculated
7. **Auto-Block** → User blocked if critical threshold exceeded

---

## 💾 Database Schema

### 1. fraud_alerts

Stores fraud alerts with severity levels and resolution status.

```sql
CREATE TABLE fraud_alerts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    alert_type VARCHAR(255),          -- Type of fraud detected
    severity ENUM('low','medium','high','critical'),
    status ENUM('pending','reviewing','resolved','false_positive','confirmed_fraud'),
    
    -- Polymorphic relation to any entity
    alertable_type VARCHAR(255),
    alertable_id BIGINT,
    
    -- User details
    user_id BIGINT,
    user_type VARCHAR(255),
    user_email VARCHAR(255),
    user_phone VARCHAR(255),
    
    -- Alert information
    description TEXT,
    metadata JSON,                    -- Additional context
    risk_score DECIMAL(5,2),         -- 0-100
    confidence_level INT,             -- 0-100
    
    -- Related entities
    related_bookings JSON,
    related_transactions JSON,
    
    -- Review tracking
    reviewed_by BIGINT,
    reviewed_at TIMESTAMP,
    review_notes TEXT,
    
    -- Actions
    user_blocked BOOLEAN DEFAULT FALSE,
    automatic_block BOOLEAN DEFAULT FALSE,
    action_taken TEXT,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    
    INDEX idx_type_severity (alert_type, severity),
    INDEX idx_status_created (status, created_at),
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_risk_score (risk_score),
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);
```

**Alert Types:**
- `high_value_frequency` - Multiple high-value bookings
- `gst_mismatch` - GST verification failed
- `multiple_gst_numbers` - Multiple GST attempts
- `repeated_payment_failures` - Excessive failed payments
- `new_account_high_value` - New account with large booking
- `unverified_high_value` - Unverified email + high value
- `same_day_registration_booking` - Immediate booking after signup
- `booking_velocity_anomaly` - Too many bookings too fast
- `excessive_booking_frequency` - Critical velocity threshold
- `amount_spike_anomaly` - Unusual amount deviation

### 2. fraud_events

Comprehensive event logging for all user activities.

```sql
CREATE TABLE fraud_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_type VARCHAR(255),          -- Specific event
    event_category VARCHAR(255),      -- booking, payment, auth, etc.
    
    -- User context
    user_id BIGINT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(255),
    
    -- Event details
    event_data JSON,                  -- Full event context
    is_suspicious BOOLEAN DEFAULT FALSE,
    risk_score DECIMAL(5,2),
    
    -- Polymorphic relation
    eventable_type VARCHAR(255),
    eventable_id BIGINT,
    
    -- Related alert
    fraud_alert_id BIGINT,
    
    -- Geolocation
    country VARCHAR(255),
    city VARCHAR(255),
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_type_created (event_type, created_at),
    INDEX idx_suspicious (is_suspicious, created_at),
    INDEX idx_ip (ip_address),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (fraud_alert_id) REFERENCES fraud_alerts(id)
);
```

**Event Categories:**
- `booking` - Booking creation, updates, cancellations
- `payment` - Payment attempts, successes, failures
- `authentication` - Login, logout, failed attempts
- `profile` - Profile updates, verification changes
- `verification` - GST, email, phone verification
- `suspicious` - Flagged suspicious activities

### 3. risk_profiles

User behavior tracking and risk assessment.

```sql
CREATE TABLE risk_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE,
    
    -- Risk assessment
    overall_risk_score DECIMAL(5,2) DEFAULT 0,  -- 0-100
    risk_level ENUM('low','medium','high','critical'),
    
    -- Behavioral statistics
    total_bookings INT DEFAULT 0,
    cancelled_bookings INT DEFAULT 0,
    successful_payments INT DEFAULT 0,
    failed_payments INT DEFAULT 0,
    disputed_transactions INT DEFAULT 0,
    
    -- Financial metrics
    total_spent DECIMAL(12,2) DEFAULT 0,
    average_booking_value DECIMAL(10,2) DEFAULT 0,
    highest_booking_value DECIMAL(10,2) DEFAULT 0,
    
    -- Fraud indicators
    fraud_alerts_count INT DEFAULT 0,
    confirmed_fraud_count INT DEFAULT 0,
    known_ip_addresses JSON,
    known_devices JSON,
    
    -- Verification status
    email_verified BOOLEAN DEFAULT FALSE,
    phone_verified BOOLEAN DEFAULT FALSE,
    gst_verified BOOLEAN DEFAULT FALSE,
    identity_verified BOOLEAN DEFAULT FALSE,
    
    -- Trust indicators
    account_age_days INT DEFAULT 0,
    first_booking_at TIMESTAMP,
    last_booking_at TIMESTAMP,
    last_fraud_check_at TIMESTAMP,
    
    -- Flags
    is_blocked BOOLEAN DEFAULT FALSE,
    requires_manual_review BOOLEAN DEFAULT FALSE,
    block_reason TEXT,
    blocked_at TIMESTAMP,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_risk_level (risk_level),
    INDEX idx_score (overall_risk_score),
    INDEX idx_blocked (is_blocked, requires_manual_review),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 4. payment_anomalies

Payment-specific fraud tracking.

```sql
CREATE TABLE payment_anomalies (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    payment_id VARCHAR(255),
    anomaly_type VARCHAR(255),        -- repeated_failure, velocity_check, amount_spike
    
    -- Payment details
    amount DECIMAL(10,2),
    payment_method VARCHAR(255),
    status VARCHAR(255),
    
    -- Anomaly metrics
    failure_count_24h INT DEFAULT 0,
    attempt_count_1h INT DEFAULT 0,
    amount_deviation_percent DECIMAL(5,2),
    
    -- Context
    context JSON,
    flagged_for_review BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_type (anomaly_type),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 5. gst_verifications

GST number validation logs.

```sql
CREATE TABLE gst_verifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    gst_number VARCHAR(255),
    
    -- Verification status
    status ENUM('pending','verified','failed','mismatch'),
    api_response JSON,
    
    -- Company details from API
    registered_name VARCHAR(255),
    registered_address TEXT,
    registered_state VARCHAR(255),
    
    -- Mismatch flags
    name_mismatch BOOLEAN DEFAULT FALSE,
    address_mismatch BOOLEAN DEFAULT FALSE,
    mismatch_details TEXT,
    
    -- User provided
    user_provided_name VARCHAR(255),
    user_provided_address TEXT,
    
    verified_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_user_status (user_id, status),
    INDEX idx_gst (gst_number),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 🛠️ Core Services

### FraudDetectionService

Main service for fraud detection and risk management.

**Location:** `app/Services/FraudDetectionService.php`

#### Methods

##### `checkBooking(Booking $booking): array`

Runs comprehensive fraud checks on a booking.

```php
use App\Services\FraudDetectionService;

$fraudService = app(FraudDetectionService::class);
$alerts = $fraudService->checkBooking($booking);

// Returns array of FraudAlert instances
foreach ($alerts as $alert) {
    echo "Alert: {$alert->alert_type} (Risk: {$alert->risk_score})";
}
```

**Checks Performed:**
1. High-value frequency
2. GST mismatch
3. Payment failures
4. Suspicious patterns
5. Velocity anomaly
6. Amount spike

##### `getOrCreateRiskProfile(User $user): RiskProfile`

Get or create risk profile for a user.

```php
$riskProfile = $fraudService->getOrCreateRiskProfile($user);

echo "Risk Score: {$riskProfile->overall_risk_score}";
echo "Risk Level: {$riskProfile->risk_level}";
echo "Is Blocked: " . ($riskProfile->is_blocked ? 'Yes' : 'No');
```

##### `updateRiskProfileFromBooking(User $user, Booking $booking): void`

Update risk profile after booking.

```php
$fraudService->updateRiskProfileFromBooking($user, $booking);
```

##### `verifyGST(User $user, string $gstNumber): array`

Verify GST number (integrate with actual GST API).

```php
$result = $fraudService->verifyGST($user, '29ABCDE1234F1Z5');

// Returns:
// [
//     'status' => 'verified|failed|mismatch',
//     'registered_name' => 'Company Name',
//     'name_mismatch' => false,
//     ...
// ]
```

#### Configuration

Risk thresholds in `FraudDetectionService`:

```php
private const RISK_THRESHOLDS = [
    'high_value_booking' => 50000,              // ₹50,000+
    'booking_velocity_time_window' => 24,       // hours
    'booking_velocity_count' => 3,              // bookings
    'payment_failure_threshold' => 5,           // attempts
    'payment_failure_time_window' => 24,        // hours
    'suspicious_amount_spike_percent' => 300,   // 3x average
];
```

### FraudEventLogger

Service for logging all fraud-related events.

**Location:** `app/Services/FraudEventLogger.php`

#### Methods

##### `logBookingAttempt($user, $booking, array $additionalData = []): FraudEvent`

```php
use App\Services\FraudEventLogger;

$logger = app(FraudEventLogger::class);
$event = $logger->logBookingAttempt($user, $booking, [
    'source' => 'web',
    'referrer' => 'search',
]);
```

##### `logPaymentAttempt($user, $transaction, bool $isSuccess, array $additionalData = []): FraudEvent`

```php
$event = $logger->logPaymentAttempt($user, $transaction, false, [
    'error_code' => 'INSUFFICIENT_FUNDS',
]);
```

##### `logSuspiciousActivity($user, string $activityType, array $details = []): FraudEvent`

```php
$event = $logger->logSuspiciousActivity($user, 'rapid_booking_creation', [
    'count' => 5,
    'time_window' => '10 minutes',
    'risk_score' => 85,
]);
```

##### Other Methods

- `logProfileUpdate()` - Track profile changes
- `logAuthentication()` - Login/logout events
- `logQuotationSubmission()` - Quotation creation
- `logGSTVerification()` - GST validation attempts
- `logCancellation()` - Booking cancellations
- `logRefundRequest()` - Refund requests

---

## 🚨 Fraud Detection Rules

### 1. High-Value Frequency Detection

**Trigger:** 3+ bookings ≥ ₹50,000 in 24 hours

**Risk Score:** 75  
**Severity:** High  
**Auto-Block:** If 5+ bookings

```php
// Example detection
User creates:
- Booking #1: ₹60,000 at 10:00 AM
- Booking #2: ₹75,000 at 11:30 AM
- Booking #3: ₹55,000 at 02:00 PM

→ Alert: "high_value_frequency"
→ Description: "User created 3 high-value bookings in 4 hours totaling ₹1,90,000"
```

### 2. GST Mismatch Detection

**Trigger:** GST verification fails (name/address mismatch)

**Risk Score:** 60  
**Severity:** Medium

```php
// Example
User Profile: "ABC Company Pvt Ltd"
GST Registered Name: "XYZ Corporation"

→ Alert: "gst_mismatch"
→ Metadata: {
    "name_mismatch": true,
    "user_name": "ABC Company Pvt Ltd",
    "registered_name": "XYZ Corporation"
}
```

### 3. Repeated Payment Failures

**Trigger:** 5+ failed payments in 24 hours

**Risk Score:** 50 + (failures × 5), max 90  
**Severity:** Medium to High

```php
// Example
User attempts:
- Payment #1: ₹25,000 → Failed
- Payment #2: ₹25,000 → Failed
- Payment #3: ₹30,000 → Failed
- Payment #4: ₹20,000 → Failed
- Payment #5: ₹25,000 → Failed

→ Alert: "repeated_payment_failures"
→ Risk Score: 75
→ Flags user for manual review if 10+ failures
```

### 4. Suspicious Patterns

#### Pattern A: New Account + High Value

**Trigger:** Account < 7 days old, booking > ₹30,000

**Risk Score:** 65  
**Severity:** Medium

```php
Account created: Dec 5, 2024
Booking attempt: Dec 7, 2024 (2 days old)
Booking amount: ₹45,000

→ Alert: "new_account_high_value"
```

#### Pattern B: Unverified Email + High Value

**Trigger:** Email not verified, booking > ₹20,000

**Risk Score:** 70  
**Severity:** Medium

#### Pattern C: Same-Day Registration + Booking

**Trigger:** Registration and booking on same day, amount > ₹15,000

**Risk Score:** 80  
**Severity:** High

```php
Registered: Dec 11, 2024 09:30 AM
First booking: Dec 11, 2024 10:15 AM
Amount: ₹35,000

→ Alert: "same_day_registration_booking"
```

### 5. Velocity Anomaly Detection

**Trigger Level 1:** 3+ bookings in 1 hour  
**Risk Score:** 85, Severity: High

**Trigger Level 2:** 10+ bookings in 24 hours  
**Risk Score:** 95, Severity: Critical, Auto-Block

```php
// Example Critical Case
10:00 AM - Booking #1
10:15 AM - Booking #2
10:30 AM - Booking #3
10:45 AM - Booking #4
...
11:30 AM - Booking #11

→ Alert: "excessive_booking_frequency"
→ User automatically blocked
```

### 6. Amount Spike Detection

**Trigger:** Booking amount > 300% of user's average

**Risk Score:** 50 + (deviation% / 10), max 90  
**Severity:** Medium

```php
User's average booking: ₹10,000
Current booking: ₹45,000
Deviation: 350%

→ Alert: "amount_spike_anomaly"
→ Risk Score: 85
```

---

## 📊 Risk Scoring Algorithm

### Calculation Components

```php
function calculateRiskScore(): float
{
    $score = 0;
    
    // 1. Cancellation Rate (0-20 points)
    $cancellationRate = ($cancelled_bookings / total_bookings) * 100;
    if ($cancellationRate > 50) $score += 20;
    elseif ($cancellationRate > 30) $score += 15;
    elseif ($cancellationRate > 15) $score += 10;
    
    // 2. Payment Failure Rate (0-25 points)
    $failureRate = ($failed_payments / (successful + failed)) * 100;
    if ($failureRate > 70) $score += 25;
    elseif ($failureRate > 50) $score += 20;
    elseif ($failureRate > 30) $score += 15;
    elseif ($failureRate > 15) $score += 10;
    
    // 3. Fraud Alerts (0-30 points)
    if ($confirmed_fraud_count > 0) $score += 30;
    elseif ($fraud_alerts_count > 5) $score += 25;
    elseif ($fraud_alerts_count > 2) $score += 15;
    elseif ($fraud_alerts_count > 0) $score += 10;
    
    // 4. Verification Bonuses (-15 points max)
    $bonus = 0;
    if ($email_verified) $bonus += 3;
    if ($phone_verified) $bonus += 3;
    if ($gst_verified) $bonus += 5;
    if ($identity_verified) $bonus += 4;
    $score -= $bonus;
    
    // 5. Account Age Bonus (-10 points max)
    if ($account_age_days > 365) $score -= 10;
    elseif ($account_age_days > 180) $score -= 5;
    elseif ($account_age_days > 90) $score -= 3;
    
    // 6. Disputed Transactions (0-10 points)
    if ($disputed_transactions > 3) $score += 10;
    elseif ($disputed_transactions > 1) $score += 5;
    
    // Clamp to 0-100
    return max(0, min(100, $score));
}
```

### Risk Level Mapping

| Score Range | Risk Level | Actions |
|------------|------------|---------|
| 0-29 | **Low** | Normal processing |
| 30-59 | **Medium** | Enhanced monitoring |
| 60-79 | **High** | Flag for review |
| 80-100 | **Critical** | Auto-block consideration |

### Example Calculations

**Example 1: Trusted User**
```
Total Bookings: 25
Cancelled: 2 (8%)
Successful Payments: 23
Failed Payments: 2 (8%)
Fraud Alerts: 0
Verifications: Email ✓, Phone ✓, GST ✓
Account Age: 400 days

Calculation:
+ 0 (cancellation < 15%)
+ 0 (failure < 15%)
+ 0 (no alerts)
- 11 (email 3 + phone 3 + GST 5)
- 10 (account > 365 days)
= -21 → 0 (clamped)

Risk Score: 0
Risk Level: LOW
```

**Example 2: Suspicious User**
```
Total Bookings: 10
Cancelled: 6 (60%)
Successful Payments: 4
Failed Payments: 8 (67%)
Fraud Alerts: 3
Verifications: None
Account Age: 5 days

Calculation:
+ 20 (cancellation > 50%)
+ 20 (failure > 50%)
+ 15 (alerts > 2)
- 0 (no verifications)
- 0 (account < 90 days)
= 55

Risk Score: 55
Risk Level: MEDIUM
```

**Example 3: High-Risk User**
```
Total Bookings: 8
Cancelled: 7 (88%)
Failed Payments: 15 (79%)
Confirmed Fraud: 1
Disputed: 4
Verifications: None
Account Age: 2 days

Calculation:
+ 20 (cancellation > 50%)
+ 25 (failure > 70%)
+ 30 (confirmed fraud)
+ 10 (disputes > 3)
- 0 (no verifications)
- 0 (new account)
= 85

Risk Score: 85
Risk Level: CRITICAL
```

---

## 📝 Event Logging

### Event Structure

All fraud events are logged with consistent structure:

```json
{
  "event_type": "booking_attempt",
  "event_category": "booking",
  "user_id": 123,
  "ip_address": "192.168.1.1",
  "user_agent": "Mozilla/5.0...",
  "session_id": "abc123xyz",
  "event_data": {
    "booking_id": 456,
    "amount": 50000,
    "quotation_id": 789,
    "start_date": "2024-12-15",
    "end_date": "2024-12-20"
  },
  "is_suspicious": false,
  "risk_score": 25.5,
  "country": "India",
  "city": "Mumbai",
  "created_at": "2024-12-11 10:30:45"
}
```

### Event Types by Category

#### Booking Events
- `booking_attempt` - User initiates booking
- `booking_created` - Booking successfully created
- `booking_updated` - Booking modified
- `booking_cancellation` - Booking cancelled
- `booking_fraud_check` - Fraud check completed

#### Payment Events
- `payment_attempt` - Payment initiated
- `payment_success` - Payment completed
- `payment_failure` - Payment failed
- `refund_request` - Refund requested
- `refund_processed` - Refund completed

#### Authentication Events
- `login_attempt` - Login initiated
- `login_success` - Successful login
- `login_failure` - Failed login
- `logout` - User logged out
- `password_reset` - Password reset requested

#### Profile Events
- `profile_update` - Profile modified
- `email_verification` - Email verified
- `phone_verification` - Phone verified
- `gst_verification` - GST verified

#### Suspicious Events
- `blocked_user_attempt` - Blocked user tried to access
- `manual_review_required` - High-risk action flagged
- `high_risk_user_activity` - High-risk user activity
- `rapid_booking_creation` - Velocity anomaly detected

### Querying Events

```php
use App\Services\FraudEventLogger;

$logger = app(FraudEventLogger::class);

// Get user's recent events
$events = $logger->getUserEvents($userId, $hours = 24);

// Get all suspicious events
$suspicious = $logger->getSuspiciousEvents($hours = 24);

// Get events by category
$bookings = $logger->getEventsByCategory('booking', $limit = 100);

// Get payment failures
$failures = $logger->getPaymentFailures($userId, $hours = 24);

// Check IP activity
$ipActivity = $logger->checkIPActivity('192.168.1.1', $hours = 24);
// Returns:
// [
//     'total_events' => 50,
//     'suspicious_count' => 15,
//     'suspicion_rate' => 30.0,
//     'is_high_risk' => true
// ]
```

---

## 📊 Admin Dashboard

### Overview Page

**Route:** `/admin/fraud/dashboard`  
**View:** `resources/views/admin/fraud/dashboard.blade.php`

#### Statistics Cards

1. **Critical Alerts** - Red, requires immediate action
2. **Pending Review** - Yellow, awaiting review
3. **Blocked Users** - Blue, currently blocked count
4. **Suspicious Events (24h)** - Cyan, recent activity

#### Charts

**Alert Type Distribution** (Bar Chart)
- Shows count per alert type (last 30 days)
- Types: High Value Frequency, GST Mismatch, Payment Failures, etc.

**Risk Level Distribution** (Doughnut Chart)
- Critical, High, Medium, Low alerts
- Color-coded: Red, Orange, Blue, Gray

#### Filters

```html
- Alert Type: Dropdown (all fraud check types)
- Severity: low | medium | high | critical
- Status: pending | reviewing | resolved | false_positive | confirmed_fraud
- Time Range: 24h | 7d | 30d | all
- Search: Email, phone, user ID
```

#### Critical Alerts Table

Always visible at top, shows:
- Alert ID, Type, User, Description
- Risk Score (progress bar)
- Created time (relative)
- Quick actions: View, Block User, Resolve

#### All Alerts Table

Paginated list with:
- Full alert details
- Status badges (color-coded)
- Created timestamp
- View details action

#### Recent Suspicious Events

Last 10 suspicious events with:
- Timestamp, Event Type, User
- IP Address, Risk Score
- View details button

### Alert Details Modal

Triggered by clicking "View" on any alert:

```javascript
function viewAlert(alertId) {
    // Fetches /admin/fraud/alerts/{id}
    // Displays:
    // - Full alert information
    // - User details
    // - Metadata/context
    // - Resolution actions:
    //   * Mark Resolved
    //   * False Positive
    //   * Confirm Fraud
}
```

### Admin Actions

#### 1. Resolve Alert

```javascript
POST /admin/fraud/alerts/{id}/resolve
{
    "resolution": "resolved|false_positive|confirmed_fraud",
    "notes": "Optional admin notes"
}
```

**Effects:**
- Updates alert status
- Records reviewer and timestamp
- If confirmed_fraud: Increments user's fraud count

#### 2. Block User

```javascript
POST /admin/fraud/users/{userId}/block
{
    "reason": "Confirmed fraudulent activity",
    "alert_id": 123  // Optional
}
```

**Effects:**
- Sets `is_blocked = true` in risk_profile
- Records block reason and timestamp
- Updates related alert with action taken
- Future requests blocked by middleware

#### 3. Unblock User

```javascript
POST /admin/fraud/users/{userId}/unblock
```

#### 4. Export Report

```
GET /admin/fraud/export?status=pending&severity=high
```

Downloads CSV with:
- Alert ID, Type, Severity
- User Email, Risk Score
- Status, Created Date

---

## ⏰ Scheduled Tasks

### 1. Hourly Fraud Monitoring

**Command:** `php artisan fraud:monitor`  
**Schedule:** Every hour  
**Configuration:** `routes/console.php`

```php
Schedule::command('fraud:monitor')
    ->hourly()
    ->name('fraud-monitoring')
    ->withoutOverlapping(15)
    ->onOneServer();
```

**Actions:**
- Check for new critical alerts
- Update risk profiles for active users (last 7 days activity)
- Flag high-risk users for manual review
- Auto-block users with confirmed fraud
- Auto-block excessive payment failures (15+ in 7 days)

**Output:**
```
🛡️  Starting Fraud Monitoring Task...
Checking for critical alerts...
⚠️  Found 3 critical alerts requiring attention!
┌────┬─────────────────────────┬──────────────────────┬────────────┬────────────────┐
│ ID │ Type                    │ User                 │ Risk Score │ Created        │
├────┼─────────────────────────┼──────────────────────┼────────────┼────────────────┤
│ 45 │ high_value_frequency    │ user@example.com     │ 75.00      │ 2 hours ago    │
│ 46 │ excessive_booking_freq  │ suspect@test.com     │ 95.00      │ 30 minutes ago │
│ 47 │ same_day_registration   │ newuser@domain.com   │ 80.00      │ 1 hour ago     │
└────┴─────────────────────────┴──────────────────────┴────────────┴────────────────┘

Updating risk profiles...
  → High-risk user detected: suspect@test.com (Score: 87.5)

Flagging high-risk users...
  → Flagged user #456 for manual review (Score: 85.0)

Checking for auto-block conditions...
  🚫 Auto-blocked user #789 (confirmed fraud activity detected)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 Fraud Monitoring Summary
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
┌──────────────────────────────┬───────┐
│ Metric                       │ Count │
├──────────────────────────────┼───────┤
│ Critical Alerts Found        │ 3     │
│ Risk Profiles Updated        │ 25    │
│ Users Flagged for Review     │ 2     │
│ Auto-Blocked Users           │ 1     │
│ Notifications Sent           │ 0     │
│ Old Alerts Cleaned           │ 0     │
└──────────────────────────────┴───────┘
⏱️  Completed in 3 seconds
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📈 Current System Status:
  • Total Alerts: 147
  • Pending Alerts: 12
  • Blocked Users: 8
```

### 2. Daily Notifications

**Command:** `php artisan fraud:monitor --notify`  
**Schedule:** Daily at 8:00 AM

```php
Schedule::command('fraud:monitor --notify')
    ->dailyAt('08:00')
    ->name('fraud-daily-notify')
    ->withoutOverlapping(15)
    ->onOneServer();
```

**Actions:**
- Same as hourly + send admin notifications
- Email summary of pending critical alerts
- Slack/Teams notification (if configured)

### 3. Weekly Cleanup

**Command:** `php artisan fraud:monitor --cleanup`  
**Schedule:** Sundays at midnight

```php
Schedule::command('fraud:monitor --cleanup')
    ->weekly()
    ->sundays()
    ->at('00:00')
    ->name('fraud-cleanup')
    ->withoutOverlapping(15)
    ->onOneServer();
```

**Actions:**
- Soft delete resolved/false_positive alerts older than 90 days
- Archive old fraud events (optional)
- Cleanup orphaned records

### 4. Manual Commands

```bash
# Run monitoring immediately
php artisan fraud:monitor

# With notifications
php artisan fraud:monitor --notify

# Cleanup old data
php artisan fraud:monitor --cleanup

# Recalculate all risk profiles
php artisan fraud:monitor --recalculate

# Combine options
php artisan fraud:monitor --notify --cleanup --recalculate
```

---

## 🔗 Integration Guide

### Step 1: Run Migration

```bash
php artisan migrate
```

Creates all 5 fraud detection tables.

### Step 2: Register Observer

In `app/Providers/AppServiceProvider.php`:

```php
use App\Models\Booking;
use App\Observers\BookingObserver;

public function boot(): void
{
    Booking::observe(BookingObserver::class);
}
```

### Step 3: Register Middleware

In `bootstrap/app.php` or route middleware:

```php
use App\Http\Middleware\FraudCheckMiddleware;

// Global middleware
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(FraudCheckMiddleware::class);
})

// Or route-specific
Route::middleware(['auth', 'fraud.check'])->group(function () {
    // Protected routes
});

// With parameters
Route::post('/booking/create')
    ->middleware('fraud.check:high_value')
    ->uses([BookingController::class, 'store']);
```

### Step 4: Add Routes

In `routes/web.php`:

```php
use App\Http\Controllers\Admin\FraudController;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    
    // Fraud dashboard
    Route::get('/fraud/dashboard', [FraudController::class, 'dashboard'])
        ->name('fraud.dashboard');
    
    // Alert details (API)
    Route::get('/fraud/alerts/{alert}', [FraudController::class, 'showAlert'])
        ->name('fraud.alerts.show');
    
    // Resolve alert
    Route::post('/fraud/alerts/{alert}/resolve', [FraudController::class, 'resolveAlert'])
        ->name('fraud.alerts.resolve');
    
    // Block/unblock user
    Route::post('/fraud/users/{user}/block', [FraudController::class, 'blockUser'])
        ->name('fraud.users.block');
    
    Route::post('/fraud/users/{user}/unblock', [FraudController::class, 'unblockUser'])
        ->name('fraud.users.unblock');
    
    // Export report
    Route::get('/fraud/export', [FraudController::class, 'export'])
        ->name('fraud.export');
    
    // User risk profile
    Route::get('/fraud/users/{user}/profile', [FraudController::class, 'userRiskProfile'])
        ->name('fraud.users.profile');
});
```

### Step 5: Integrate in Booking Flow

In your `BookingController`:

```php
use App\Services\FraudDetectionService;
use App\Services\FraudEventLogger;

public function store(Request $request)
{
    $user = auth()->user();
    
    // ... validation and booking creation
    
    $booking = Booking::create([...]);
    
    // Fraud checks run automatically via BookingObserver
    // But you can also check manually:
    
    $fraudService = app(FraudDetectionService::class);
    $alerts = $fraudService->checkBooking($booking);
    
    if (count($alerts) > 0) {
        // Handle alerts
        $highRiskAlerts = collect($alerts)->where('severity', 'critical');
        
        if ($highRiskAlerts->isNotEmpty()) {
            // Optionally block the booking
            return response()->json([
                'error' => 'This booking requires manual review',
                'alert_id' => $highRiskAlerts->first()->id
            ], 403);
        }
    }
    
    return response()->json([
        'success' => true,
        'booking' => $booking
    ]);
}
```

### Step 6: Handle Blocked Users

Create a blocked account page:

**Route:**
```php
Route::get('/account/blocked', function () {
    return view('account.blocked');
})->name('account.blocked');
```

**View:** `resources/views/account/blocked.blade.php`

```blade
<div class="container">
    <div class="alert alert-danger">
        <h3>Account Blocked</h3>
        <p>Your account has been temporarily blocked due to suspicious activity.</p>
        <p>Please contact our support team at support@oohapp.com for assistance.</p>
    </div>
</div>
```

### Step 7: Configure GST Verification (Optional)

Integrate with actual GST API in `FraudDetectionService::verifyGST()`:

```php
public function verifyGST(User $user, string $gstNumber): array
{
    // Example using MasterIndia GST API
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . config('services.gst_api.key'),
    ])->get('https://api.mastergst.com/einvoice/authenticate', [
        'gstin' => $gstNumber,
    ]);
    
    $data = $response->json();
    
    $verification = [
        'status' => $data['error'] ? 'failed' : 'verified',
        'registered_name' => $data['legal_name'] ?? null,
        'registered_address' => $data['address'] ?? null,
        'registered_state' => $data['state_jurisdiction'] ?? null,
    ];
    
    // Check for mismatches
    $nameSimilarity = similar_text(
        strtolower($user->name), 
        strtolower($verification['registered_name']), 
        $percent
    );
    
    $verification['name_mismatch'] = $percent < 70;
    
    // Store verification
    DB::table('gst_verifications')->insert([
        'user_id' => $user->id,
        'gst_number' => $gstNumber,
        'status' => $verification['name_mismatch'] ? 'mismatch' : 'verified',
        'registered_name' => $verification['registered_name'],
        'name_mismatch' => $verification['name_mismatch'],
        'user_provided_name' => $user->name,
        'verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    return $verification;
}
```

---

## 📚 API Reference

### Admin Fraud Controller

#### GET /admin/fraud/dashboard

**Query Parameters:**
- `alert_type` (optional) - Filter by alert type
- `severity` (optional) - Filter by severity (low|medium|high|critical)
- `status` (optional) - Filter by status
- `time_range` (optional) - Time range (24h|7d|30d|all)
- `search` (optional) - Search by email/phone/ID

**Response:** HTML view with dashboard

#### GET /admin/fraud/alerts/{alert}

**Response:**
```json
{
  "id": 123,
  "alert_type": "High Value Frequency",
  "severity": "high",
  "status": "pending",
  "user_id": 456,
  "user_email": "user@example.com",
  "user_phone": "+91XXXXXXXXXX",
  "description": "User created 3 high-value bookings...",
  "metadata": {
    "booking_count": 3,
    "total_amount": 190000,
    "time_window_hours": 24
  },
  "risk_score": 75.00,
  "confidence_level": 85,
  "created_at": "Dec 11, 2024 10:30:45",
  "reviewed_at": null,
  "review_notes": null
}
```

#### POST /admin/fraud/alerts/{alert}/resolve

**Request:**
```json
{
  "resolution": "resolved|false_positive|confirmed_fraud",
  "notes": "Optional admin notes"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Alert marked as resolved"
}
```

#### POST /admin/fraud/users/{user}/block

**Request:**
```json
{
  "reason": "Confirmed fraudulent activity",
  "alert_id": 123
}
```

**Response:**
```json
{
  "success": true,
  "message": "User has been blocked successfully"
}
```

#### POST /admin/fraud/users/{user}/unblock

**Response:**
```json
{
  "success": true,
  "message": "User has been unblocked"
}
```

#### GET /admin/fraud/export

**Query Parameters:**
- `status` (optional)
- `severity` (optional)

**Response:** CSV file download

---

## 🧪 Testing

### Unit Tests

Create `tests/Unit/FraudDetectionServiceTest.php`:

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Services\FraudDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FraudDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_high_value_frequency_detection()
    {
        $user = User::factory()->create();
        $fraudService = app(FraudDetectionService::class);
        
        // Create 3 high-value bookings
        for ($i = 0; $i < 3; $i++) {
            $booking = Booking::factory()->create([
                'customer_id' => $user->id,
                'total_amount' => 60000,
            ]);
        }
        
        $booking = Booking::first();
        $alerts = $fraudService->checkBooking($booking);
        
        $this->assertNotEmpty($alerts);
        $this->assertEquals('high_value_frequency', $alerts[0]->alert_type);
        $this->assertEquals('high', $alerts[0]->severity);
    }
    
    public function test_velocity_anomaly_detection()
    {
        $user = User::factory()->create();
        $fraudService = app(FraudDetectionService::class);
        
        // Create 4 bookings in quick succession
        for ($i = 0; $i < 4; $i++) {
            Booking::factory()->create([
                'customer_id' => $user->id,
                'created_at' => now()->subMinutes($i * 10),
            ]);
        }
        
        $booking = Booking::latest()->first();
        $alerts = $fraudService->checkBooking($booking);
        
        $velocityAlert = collect($alerts)
            ->where('alert_type', 'booking_velocity_anomaly')
            ->first();
        
        $this->assertNotNull($velocityAlert);
        $this->assertEquals('high', $velocityAlert->severity);
    }
    
    public function test_risk_profile_calculation()
    {
        $user = User::factory()->create();
        $fraudService = app(FraudDetectionService::class);
        
        $riskProfile = $fraudService->getOrCreateRiskProfile($user);
        
        // Simulate bad behavior
        $riskProfile->update([
            'total_bookings' => 10,
            'cancelled_bookings' => 7,  // 70% cancellation
            'failed_payments' => 8,
            'successful_payments' => 2, // 80% failure rate
        ]);
        
        $riskProfile->recalculateRiskScore();
        
        $this->assertGreaterThan(60, $riskProfile->overall_risk_score);
        $this->assertContains($riskProfile->risk_level, ['high', 'critical']);
    }
}
```

### Feature Tests

Create `tests/Feature/FraudMiddlewareTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\FraudDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FraudMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocked_user_cannot_access_routes()
    {
        $user = User::factory()->create();
        $fraudService = app(FraudDetectionService::class);
        
        // Block the user
        $riskProfile = $fraudService->getOrCreateRiskProfile($user);
        $riskProfile->blockUser('Test block');
        
        $response = $this->actingAs($user)
            ->post('/bookings/create', [
                // booking data
            ]);
        
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'error_code' => 'ACCOUNT_BLOCKED',
        ]);
    }
}
```

### Manual Testing

```bash
# 1. Run migration
php artisan migrate

# 2. Test fraud monitoring command
php artisan fraud:monitor

# 3. Create test scenario
php artisan tinker

# In tinker:
$user = User::first();
$booking = \App\Models\Booking::factory()->create([
    'customer_id' => $user->id,
    'total_amount' => 75000
]);

$fraudService = app(\App\Services\FraudDetectionService::class);
$alerts = $fraudService->checkBooking($booking);
dd($alerts);

# 4. Check risk profile
$riskProfile = $fraudService->getOrCreateRiskProfile($user);
dd($riskProfile->toArray());
```

---

## 🚀 Deployment

### Pre-Deployment Checklist

- [ ] Database migration tested on staging
- [ ] Risk thresholds reviewed and configured
- [ ] GST API integration configured (if using)
- [ ] Admin routes protected with authentication
- [ ] Scheduled tasks configured in cron
- [ ] Email/notification channels configured
- [ ] Blocked user page created
- [ ] Observer registered in AppServiceProvider
- [ ] Middleware registered (if using)

### Production Deployment Steps

```bash
# 1. Backup database
php artisan backup:run

# 2. Run migration
php artisan migrate --force

# 3. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 4. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Test scheduled command
php artisan fraud:monitor --notify

# 6. Monitor logs
tail -f storage/logs/laravel.log
```

### Cron Configuration

Add to server crontab:

```bash
* * * * * cd /path/to/oohapp && php artisan schedule:run >> /dev/null 2>&1
```

The schedule already includes:
- Hourly: `fraud:monitor`
- Daily 8 AM: `fraud:monitor --notify`
- Weekly Sunday: `fraud:monitor --cleanup`

### Environment Variables

Add to `.env` (if using GST API):

```env
# GST Verification API
GST_API_KEY=your_api_key_here
GST_API_URL=https://api.mastergst.com/einvoice
```

### Monitoring

```bash
# Check scheduled tasks
php artisan schedule:list

# View fraud alerts
php artisan tinker
>>> FraudAlert::where('status', 'pending')->count()

# Check blocked users
>>> RiskProfile::where('is_blocked', true)->count()

# View recent events
>>> FraudEvent::suspicious()->latest()->limit(10)->get()
```

### Performance Optimization

```php
// In config/database.php - add indexes
Schema::table('fraud_alerts', function (Blueprint $table) {
    $table->index(['created_at', 'severity']);
    $table->index(['user_id', 'status']);
});

Schema::table('fraud_events', function (Blueprint $table) {
    $table->index(['created_at', 'is_suspicious']);
});
```

### Scaling Considerations

For high-volume applications:

1. **Event Logging** - Queue event logging
```php
// In FraudEventLogger
dispatch(function() use ($data) {
    FraudEvent::create($data);
})->afterResponse();
```

2. **Risk Calculation** - Queue risk profile updates
```php
// After booking creation
UpdateRiskProfileJob::dispatch($user)->delay(now()->addMinutes(5));
```

3. **Database** - Partition fraud_events table by month
4. **Cache** - Cache risk profiles for active users
5. **Archive** - Move old events to archive table monthly

---

## 📞 Support

### Common Issues

**Issue:** Fraud checks slowing down booking creation

**Solution:** Fraud checks run via Observer after booking creation, so they shouldn't block the user. If needed, queue the checks:

```php
// In BookingObserver
public function created(Booking $booking): void
{
    CheckBookingFraudJob::dispatch($booking)->delay(now()->addSeconds(30));
}
```

**Issue:** Too many false positives

**Solution:** Adjust risk thresholds in `FraudDetectionService::RISK_THRESHOLDS`

**Issue:** GST verification failing

**Solution:** Check API credentials, implement fallback logic:

```php
try {
    $result = $fraudService->verifyGST($user, $gstNumber);
} catch (\Exception $e) {
    // Log and continue without GST check
    Log::warning('GST verification failed', ['error' => $e->getMessage()]);
}
```

### Logging

All fraud-related activities are logged:

```php
// View logs
tail -f storage/logs/laravel.log | grep "fraud"

// Search for specific alert
grep "alert_id:123" storage/logs/laravel.log

// View critical alerts
grep "Critical fraud alert" storage/logs/laravel.log
```

### Debug Mode

Enable detailed fraud logging:

```php
// In FraudDetectionService
Log::debug('Fraud check details', [
    'user_id' => $user->id,
    'booking_id' => $booking->id,
    'checks_performed' => [
        'high_value_frequency' => $highValueResult,
        'gst_mismatch' => $gstResult,
        // ...
    ]
]);
```

---

## 📈 Metrics & KPIs

### Key Metrics to Track

1. **Alert Volume**
   - Total alerts per day
   - Critical alerts per day
   - Alert resolution time

2. **Fraud Detection Rate**
   - Confirmed fraud / Total alerts
   - False positive rate
   - Average risk score of confirmed fraud

3. **User Impact**
   - Blocked users count
   - Manual review queue size
   - Average time to unblock

4. **System Performance**
   - Fraud check execution time
   - Event logging latency
   - Risk calculation time

### Reporting Queries

```sql
-- Daily fraud summary
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total_alerts,
    SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical,
    SUM(CASE WHEN status = 'confirmed_fraud' THEN 1 ELSE 0 END) as confirmed
FROM fraud_alerts
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- Top alert types
SELECT 
    alert_type,
    COUNT(*) as count,
    AVG(risk_score) as avg_risk_score
FROM fraud_alerts
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY alert_type
ORDER BY count DESC;

-- High-risk users
SELECT 
    u.id,
    u.email,
    rp.overall_risk_score,
    rp.fraud_alerts_count,
    rp.is_blocked
FROM users u
JOIN risk_profiles rp ON u.id = rp.user_id
WHERE rp.risk_level IN ('high', 'critical')
ORDER BY rp.overall_risk_score DESC
LIMIT 20;
```

---

## 🔒 Security Considerations

1. **Data Privacy** - Fraud events contain sensitive data (IP, user agent). Ensure GDPR compliance.

2. **Access Control** - Restrict fraud dashboard to admin roles only.

3. **Audit Trail** - All admin actions (blocking, resolving alerts) are logged.

4. **Rate Limiting** - Add rate limiting to prevent abuse:
```php
Route::middleware(['throttle:60,1'])->group(function () {
    // Fraud-related routes
});
```

5. **Encryption** - Consider encrypting sensitive fraud event data at rest.

---

## 📚 References

- Laravel Eloquent: https://laravel.com/docs/11.x/eloquent
- Laravel Observers: https://laravel.com/docs/11.x/eloquent#observers
- Laravel Scheduling: https://laravel.com/docs/11.x/scheduling
- Chart.js Documentation: https://www.chartjs.org/docs/

---

**Last Updated:** December 11, 2024  
**Document Version:** 1.0.0  
**Maintained By:** Development Team

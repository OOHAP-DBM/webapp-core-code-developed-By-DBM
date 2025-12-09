# Notification Template System - Developer Documentation

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [Core Components](#core-components)
5. [How to Use](#how-to-use)
6. [Adding New Event Types](#adding-new-event-types)
7. [Adding New Channels](#adding-new-channels)
8. [Customizing Templates](#customizing-templates)
9. [Integration Guide](#integration-guide)
10. [Common Tasks](#common-tasks)
11. [Troubleshooting](#troubleshooting)

---

## Overview

The Notification Template System is a flexible, multi-channel notification engine that allows administrators to create and manage notification templates for various events in the application. It supports Email, SMS, WhatsApp, and Web notifications.

### Key Features
- **Multi-Channel Support**: Email, SMS, WhatsApp, Web notifications
- **13 Pre-configured Event Types**: OTP, Enquiry, Offers, Quotations, Payments, etc.
- **Dynamic Placeholders**: Automatically replace placeholders with actual data
- **Delivery Tracking**: Track notification status (pending → sent → delivered → read)
- **Retry Mechanism**: Automatically retry failed notifications
- **Admin Interface**: Complete UI for template management

---

## Architecture

### Flow Diagram
```
Event Trigger → NotificationService → Template Selection → Placeholder Replacement
                                                            ↓
                                                   Create Log Entry
                                                            ↓
                                              Send via Channel (Email/SMS/WhatsApp/Web)
                                                            ↓
                                                  Update Delivery Status
```

### File Structure
```
app/
├── Models/
│   ├── NotificationTemplate.php    # Template model with placeholders
│   └── NotificationLog.php          # Delivery tracking model
├── Services/
│   └── NotificationService.php      # Core notification sending logic
└── Http/Controllers/Admin/
    └── NotificationTemplateController.php  # Admin management

database/
├── migrations/
│   ├── 2025_12_09_090249_create_notification_templates_table.php
│   └── 2025_12_09_090258_create_notification_logs_table.php
└── seeders/
    └── NotificationTemplateSeeder.php  # Default templates

resources/views/admin/notifications/
├── templates/
│   ├── index.blade.php   # Template list
│   ├── create.blade.php  # Create form
│   ├── edit.blade.php    # Edit form
│   └── show.blade.php    # Template details
└── logs/
    └── index.blade.php   # Notification logs
```

---

## Database Schema

### `notification_templates` Table
Stores reusable notification templates.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `name` | string(100) | Template name (e.g., "OTP Email") |
| `slug` | string(100) | Unique identifier (auto-generated) |
| `event_type` | string(50) | Event trigger (e.g., "otp", "offer_created") |
| `channel` | string(20) | Delivery channel (email/sms/whatsapp/web) |
| `subject` | string | Email subject (nullable, email only) |
| `body` | text | Plain text template |
| `html_body` | text | HTML template (nullable, email only) |
| `available_placeholders` | json | List of available placeholders |
| `is_active` | boolean | Template enabled/disabled |
| `is_system_default` | boolean | System template (cannot be deleted) |
| `priority` | integer | Selection priority (higher = first) |
| `metadata` | json | Additional settings |

### `notification_logs` Table
Tracks all sent notifications.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `notification_template_id` | bigint | Template used (nullable) |
| `user_id` | bigint | Recipient user (nullable) |
| `recipient_identifier` | string | Email/phone/user_id |
| `event_type` | string(50) | Event that triggered notification |
| `channel` | string(20) | Channel used |
| `status` | string(20) | pending/sent/delivered/failed/read |
| `sent_at` | timestamp | When sent |
| `delivered_at` | timestamp | When delivered |
| `read_at` | timestamp | When read (web only) |
| `provider` | string(50) | Provider used (smtp, twilio, etc.) |
| `provider_message_id` | string | Provider's message ID |
| `retry_count` | integer | Number of retry attempts |
| `related_type` | string | Polymorphic relation (Booking, Enquiry, etc.) |
| `related_id` | bigint | Related entity ID |
| `placeholders_data` | json | Actual data used in placeholders |

---

## Core Components

### 1. NotificationTemplate Model

**Location**: `app/Models/NotificationTemplate.php`

**Key Constants:**
```php
// Event Types
EVENT_OTP = 'otp'
EVENT_ENQUIRY_RECEIVED = 'enquiry_received'
EVENT_OFFER_CREATED = 'offer_created'
EVENT_PAYMENT_COMPLETE = 'payment_complete'
// ... and 9 more

// Channels
CHANNEL_EMAIL = 'email'
CHANNEL_SMS = 'sms'
CHANNEL_WHATSAPP = 'whatsapp'
CHANNEL_WEB = 'web'
```

**Important Methods:**
```php
// Get default placeholders for an event type
NotificationTemplate::getDefaultPlaceholders(string $eventType): array

// Render template with data
$template->render(array $data): array
// Returns: ['subject' => '...', 'body' => '...', 'html_body' => '...']

// Duplicate template
$template->duplicate(string $newName = null): NotificationTemplate

// Check if can be deleted
$template->canBeDeleted(): bool
```

### 2. NotificationService

**Location**: `app/Services/NotificationService.php`

**Main Methods:**

#### Send Single Notification
```php
use App\Services\NotificationService;

$notificationService = app(NotificationService::class);

$log = $notificationService->sendFromTemplate(
    eventType: 'otp',           // Event type constant
    channel: 'email',            // Channel constant
    placeholdersData: [          // Data for placeholders
        'user_name' => 'John Doe',
        'otp_code' => '123456',
        'expiry_minutes' => '10'
    ],
    recipient: $user,            // User model or email/phone string
    relatedEntity: null          // Optional: related model (Booking, etc.)
);
```

#### Send Bulk Notifications
```php
$logs = $notificationService->sendBulk(
    eventType: 'offer_created',
    channel: 'email',
    placeholdersData: $data,
    recipients: [$user1, $user2, $user3],
    relatedEntity: $offer
);
```

#### Get User Notifications (Web only)
```php
$notifications = $notificationService->getUserNotifications(
    user: $user,
    unreadOnly: true  // Only unread
);
```

#### Mark as Read
```php
$count = $notificationService->markAsRead(
    user: $user,
    logIds: [1, 2, 3]  // Optional: specific IDs, empty = all
);
```

### 3. NotificationLog Model

**Location**: `app/Models/NotificationLog.php`

**Status Tracking:**
```php
// Mark statuses
$log->markAsSent($providerId, $providerResponse);
$log->markAsDelivered();
$log->markAsFailed($errorMessage);
$log->markAsRead();

// Check retry eligibility
if ($log->canRetry($maxRetries = 3)) {
    $notificationService->retry($log);
}
```

---

## How to Use

### Basic Usage in Your Code

**Example 1: Send OTP Email**
```php
use App\Services\NotificationService;
use App\Models\User;

class AuthController extends Controller
{
    public function sendOTP(Request $request)
    {
        $user = User::find($request->user_id);
        $otp = rand(100000, 999999);
        
        // Send OTP email
        app(NotificationService::class)->sendFromTemplate(
            eventType: 'otp',
            channel: 'email',
            placeholdersData: [
                'user_name' => $user->name,
                'otp_code' => $otp,
                'expiry_minutes' => '10'
            ],
            recipient: $user
        );
        
        // Also send SMS
        app(NotificationService::class)->sendFromTemplate(
            eventType: 'otp',
            channel: 'sms',
            placeholdersData: [
                'otp_code' => $otp,
                'expiry_minutes' => '10'
            ],
            recipient: $user->phone
        );
    }
}
```

**Example 2: Send Payment Confirmation**
```php
use App\Services\NotificationService;

class PaymentController extends Controller
{
    public function onPaymentSuccess(BookingPayment $payment)
    {
        $booking = $payment->booking;
        $customer = $booking->customer;
        
        app(NotificationService::class)->sendFromTemplate(
            eventType: 'payment_complete',
            channel: 'email',
            placeholdersData: [
                'customer_name' => $customer->name,
                'payment_id' => $payment->id,
                'booking_id' => $booking->id,
                'payment_amount' => number_format($payment->amount, 2),
                'payment_date' => now()->format('M d, Y'),
                'payment_method' => $payment->payment_method,
                'invoice_url' => route('invoices.show', $payment->id)
            ],
            recipient: $customer,
            relatedEntity: $payment
        );
    }
}
```

**Example 3: Send to Multiple Recipients**
```php
// Notify all vendors about new enquiry
$vendors = User::where('role', 'vendor')->get();

app(NotificationService::class)->sendBulk(
    eventType: 'enquiry_received',
    channel: 'email',
    placeholdersData: [
        'customer_name' => $enquiry->customer->name,
        'enquiry_id' => $enquiry->id,
        'property_name' => $enquiry->property->name,
        'enquiry_date' => $enquiry->created_at->format('M d, Y'),
        'enquiry_url' => route('enquiries.show', $enquiry->id)
    ],
    recipients: $vendors,
    relatedEntity: $enquiry
);
```

---

## Adding New Event Types

### Step 1: Add Event Constant to Model

**File**: `app/Models/NotificationTemplate.php`

```php
class NotificationTemplate extends Model
{
    // Add your new event constant
    public const EVENT_YOUR_NEW_EVENT = 'your_new_event';
    
    // Update getEventTypes() method
    public static function getEventTypes(): array
    {
        return [
            // ... existing events
            self::EVENT_YOUR_NEW_EVENT => 'Your New Event Label',
        ];
    }
    
    // Update getDefaultPlaceholders() method
    public static function getDefaultPlaceholders(string $eventType): array
    {
        $eventSpecific = match ($eventType) {
            // ... existing events
            self::EVENT_YOUR_NEW_EVENT => [
                '{{placeholder_1}}' => 'Description of placeholder 1',
                '{{placeholder_2}}' => 'Description of placeholder 2',
                '{{custom_data}}' => 'Your custom data',
            ],
            default => [],
        };
        
        return array_merge($common, $eventSpecific);
    }
}
```

### Step 2: Create Default Templates (Optional)

**File**: `database/seeders/NotificationTemplateSeeder.php`

Add to the `getDefaultTemplates()` array:

```php
[
    'name' => 'Your Event - Email',
    'event_type' => NotificationTemplate::EVENT_YOUR_NEW_EVENT,
    'channel' => NotificationTemplate::CHANNEL_EMAIL,
    'subject' => 'Subject with {{placeholder_1}}',
    'body' => "Plain text template with {{placeholder_1}} and {{placeholder_2}}",
    'html_body' => '<h3>HTML template</h3><p>{{placeholder_1}}</p>',
    'description' => 'Description of when this is sent',
    'is_active' => true,
    'is_system_default' => true,
    'priority' => 90,
],
```

### Step 3: Use in Your Code

```php
app(NotificationService::class)->sendFromTemplate(
    eventType: 'your_new_event',
    channel: 'email',
    placeholdersData: [
        'placeholder_1' => 'Value 1',
        'placeholder_2' => 'Value 2',
        'custom_data' => 'Custom value'
    ],
    recipient: $user
);
```

### Step 4: Run Seeder (if you created templates)

```bash
php artisan db:seed --class=NotificationTemplateSeeder
```

---

## Adding New Channels

### Step 1: Add Channel Constant

**File**: `app/Models/NotificationTemplate.php`

```php
class NotificationTemplate extends Model
{
    public const CHANNEL_YOUR_CHANNEL = 'your_channel';
    
    public static function getChannels(): array
    {
        return [
            // ... existing channels
            self::CHANNEL_YOUR_CHANNEL => 'Your Channel Name',
        ];
    }
}
```

### Step 2: Implement Channel Handler

**File**: `app/Services/NotificationService.php`

Add method in the `sendViaChannel()` match statement:

```php
protected function sendViaChannel(NotificationLog $log, array $recipientInfo): void
{
    try {
        match ($log->channel) {
            // ... existing channels
            'your_channel' => $this->sendYourChannel($log, $recipientInfo),
            default => throw new Exception("Unsupported channel: {$log->channel}"),
        };
    } catch (Exception $e) {
        $log->markAsFailed($e->getMessage());
        throw $e;
    }
}

protected function sendYourChannel(NotificationLog $log, array $recipientInfo): void
{
    try {
        // Implement your channel logic here
        // Example: Call API, send to queue, etc.
        
        $response = YourChannelProvider::send([
            'to' => $recipientInfo['identifier'],
            'message' => $log->body,
            // ... other parameters
        ]);
        
        $log->markAsSent(
            providerId: $response['message_id'],
            providerResponse: json_encode($response)
        );
        
        Log::info("Your channel notification sent", [
            'log_id' => $log->id,
            'recipient' => $recipientInfo['identifier'],
        ]);
    } catch (Exception $e) {
        $log->markAsFailed("Your channel failed: {$e->getMessage()}");
        throw $e;
    }
}
```

### Step 3: Update Badge Colors (Optional)

In both models, add color for your channel:

```php
public function getChannelColorAttribute(): string
{
    return match ($this->channel) {
        // ... existing
        'your_channel' => 'warning',
        default => 'secondary',
    };
}
```

---

## Customizing Templates

### Via Admin Interface

1. **Navigate**: Go to `/admin/notifications/templates`
2. **Edit Template**: Click edit button on any template
3. **Modify Content**: Update subject, body, or HTML
4. **Use Placeholders**: Available placeholders shown in sidebar
5. **Test Send**: Use "Test Send" button to verify
6. **Save**: Submit the form

### Programmatically

```php
use App\Models\NotificationTemplate;

// Find and update template
$template = NotificationTemplate::where('event_type', 'otp')
    ->where('channel', 'email')
    ->first();

$template->update([
    'subject' => 'Your new subject with {{otp_code}}',
    'body' => 'Updated plain text body',
    'html_body' => '<p>Updated HTML body</p>',
]);

// Create new template
NotificationTemplate::create([
    'name' => 'Custom OTP SMS',
    'event_type' => 'otp',
    'channel' => 'sms',
    'body' => 'Your OTP: {{otp_code}}',
    'is_active' => true,
    'priority' => 10,
]);

// Duplicate and modify
$copy = $template->duplicate('OTP Email - Custom Version');
$copy->update(['body' => 'Modified copy']);
```

---

## Integration Guide

### Integrate with Existing Events

**Example: Send notification when booking is created**

**File**: `app/Listeners/OnBookingCreated.php`

```php
<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Services\NotificationService;

class OnBookingCreated
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}
    
    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking;
        
        // Notify customer
        $this->notificationService->sendFromTemplate(
            eventType: 'booking_completed',
            channel: 'email',
            placeholdersData: [
                'customer_name' => $booking->customer->name,
                'booking_id' => $booking->id,
                'vendor_name' => $booking->vendor->name,
                'property_name' => $booking->property->name,
                'completion_date' => now()->format('M d, Y'),
                'booking_url' => route('bookings.show', $booking->id),
            ],
            recipient: $booking->customer,
            relatedEntity: $booking
        );
        
        // Notify vendor
        $this->notificationService->sendFromTemplate(
            eventType: 'booking_completed',
            channel: 'email',
            placeholdersData: [
                'vendor_name' => $booking->vendor->name,
                'booking_id' => $booking->id,
                'customer_name' => $booking->customer->name,
                // ... vendor-specific data
            ],
            recipient: $booking->vendor,
            relatedEntity: $booking
        );
    }
}
```

### Register Listener

**File**: `app/Providers/EventServiceProvider.php`

```php
protected $listen = [
    BookingCreated::class => [
        OnBookingCreated::class,
    ],
];
```

---

## Common Tasks

### Task 1: Change Email Content

**Via Admin**: 
1. Go to `/admin/notifications/templates`
2. Find template (filter by event type)
3. Click "Edit"
4. Modify `Subject`, `Body`, or `HTML Body`
5. Click "Update Template"

**Via Code**:
```php
NotificationTemplate::where('event_type', 'payment_complete')
    ->where('channel', 'email')
    ->update([
        'subject' => 'New Subject - Payment Confirmation',
        'body' => 'New plain text version',
    ]);
```

### Task 2: Disable a Template

```php
$template = NotificationTemplate::find($id);
$template->update(['is_active' => false]);
```

Or via admin: Click the status badge to toggle.

### Task 3: View Notification History

**Via Admin**:
- Go to `/admin/notifications/logs`
- Filter by channel, status, event type, date
- Click "View" to see details

**Via Code**:
```php
use App\Models\NotificationLog;

// Get all failed notifications
$failed = NotificationLog::failed()->get();

// Get notifications for specific user
$userLogs = NotificationLog::where('user_id', $userId)
    ->orderBy('created_at', 'desc')
    ->get();

// Get notifications for specific event
$paymentLogs = NotificationLog::where('event_type', 'payment_complete')
    ->where('created_at', '>=', now()->subDays(7))
    ->get();
```

### Task 4: Retry Failed Notification

**Via Admin**:
- Go to `/admin/notifications/logs`
- Filter status = "Failed"
- Click retry button (if eligible)

**Via Code**:
```php
$log = NotificationLog::find($id);

if ($log->canRetry()) {
    app(NotificationService::class)->retry($log);
}
```

### Task 5: Get Notification Statistics

```php
$stats = app(NotificationService::class)->getStatistics([
    'date_from' => '2025-01-01',
    'date_to' => '2025-12-31',
    'channel' => 'email',
    'event_type' => 'payment_complete',
]);

// Returns:
// [
//     'total_sent' => 1500,
//     'pending' => 10,
//     'sent' => 1200,
//     'delivered' => 1150,
//     'failed' => 50,
//     'read' => 800,
//     'by_channel' => ['email' => 1000, 'sms' => 500],
//     'by_event' => ['otp' => 500, 'payment_complete' => 300, ...]
// ]
```

### Task 6: Send Test Notification

**Via Admin**:
1. Go to template detail page
2. Click "Test Send"
3. Enter recipient email/phone
4. Click "Send Test"

**Via Code**:
```php
app(NotificationService::class)->sendFromTemplate(
    eventType: 'otp',
    channel: 'email',
    placeholdersData: [
        'user_name' => 'Test User',
        'otp_code' => '999999',
        'expiry_minutes' => '10'
    ],
    recipient: 'test@example.com'
);
```

---

## Troubleshooting

### Issue 1: Template Not Found

**Symptoms**: `No active template found for event: X, channel: Y`

**Solutions**:
1. Check template exists: `/admin/notifications/templates`
2. Verify template is active (green badge)
3. Check event_type and channel match exactly
4. Verify template priority if multiple exist

```php
// Debug: Check available templates
$templates = NotificationTemplate::active()
    ->forEvent('otp')
    ->forChannel('email')
    ->get();
    
dd($templates);
```

### Issue 2: Placeholder Not Replaced

**Symptoms**: Email shows `{{user_name}}` instead of actual name

**Solutions**:
1. Verify placeholder format: `{{placeholder_name}}`
2. Check data is provided in `placeholdersData` array
3. Key must match placeholder without braces

```php
// Wrong
placeholdersData: ['{{user_name}}' => 'John']

// Correct
placeholdersData: ['user_name' => 'John']
// or
placeholdersData: ['{{user_name}}' => 'John']  // Both work
```

### Issue 3: Email Not Sending

**Symptoms**: Notification logged but status stays "pending"

**Solutions**:
1. Check mail configuration in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="${APP_NAME}"
```

2. Test mail config:
```bash
php artisan tinker
Mail::raw('Test email', function($msg) {
    $msg->to('test@example.com')->subject('Test');
});
```

3. Check logs: `storage/logs/laravel.log`

### Issue 4: SMS/WhatsApp Not Working

**Symptoms**: Status shows "SMS provider not configured"

**Solution**: These are mock implementations. You need to integrate actual providers:

**For SMS (Twilio example)**:
```php
// In NotificationService.php, replace sendSms() method:
protected function sendSms(NotificationLog $log, array $recipientInfo): void
{
    try {
        $twilio = new \Twilio\Rest\Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
        
        $message = $twilio->messages->create(
            $recipientInfo['identifier'],
            [
                'from' => config('services.twilio.from'),
                'body' => $log->body
            ]
        );
        
        $log->markAsSent(
            providerId: $message->sid,
            providerResponse: json_encode($message->toArray())
        );
    } catch (Exception $e) {
        $log->markAsFailed("SMS failed: {$e->getMessage()}");
        throw $e;
    }
}
```

### Issue 5: High Notification Volume

**Symptoms**: Slow response when sending bulk notifications

**Solution**: Use queues

1. Create notification job:
```php
php artisan make:job SendNotificationJob
```

2. Implement job:
```php
class SendNotificationJob implements ShouldQueue
{
    public function __construct(
        public string $eventType,
        public string $channel,
        public array $placeholdersData,
        public $recipient,
        public $relatedEntity = null
    ) {}
    
    public function handle(NotificationService $service): void
    {
        $service->sendFromTemplate(
            $this->eventType,
            $this->channel,
            $this->placeholdersData,
            $this->recipient,
            $this->relatedEntity
        );
    }
}
```

3. Dispatch job:
```php
SendNotificationJob::dispatch(
    eventType: 'payment_complete',
    channel: 'email',
    placeholdersData: $data,
    recipient: $user,
    relatedEntity: $payment
);
```

---

## Best Practices

### 1. Always Use Placeholders
```php
// Bad - Hardcoded values
'body' => "Hello John, your OTP is 123456"

// Good - Use placeholders
'body' => "Hello {{user_name}}, your OTP is {{otp_code}}"
```

### 2. Provide All Required Data
```php
// Get available placeholders
$placeholders = NotificationTemplate::getDefaultPlaceholders('payment_complete');

// Ensure all placeholders have values
$data = [];
foreach ($placeholders as $key => $description) {
    $key = str_replace(['{{', '}}'], '', $key);
    $data[$key] = $actualValue ?? 'N/A';
}
```

### 3. Log Related Entities
```php
// Good - Easier to track
$notificationService->sendFromTemplate(
    // ... parameters
    relatedEntity: $booking  // Links notification to booking
);
```

### 4. Handle Failures Gracefully
```php
try {
    $notificationService->sendFromTemplate(...);
} catch (Exception $e) {
    Log::error('Notification failed', [
        'error' => $e->getMessage(),
        'event' => 'payment_complete',
        'user_id' => $user->id,
    ]);
    // Continue execution - don't fail main process
}
```

### 5. Test Templates Before Production
Always use "Test Send" feature in admin to verify:
- Placeholders are replaced correctly
- Email formatting looks good
- Links work
- Content makes sense

---

## Quick Reference

### Common Event Types
- `otp` - One-time password
- `enquiry_received` - New enquiry
- `offer_created` - Vendor creates offer
- `offer_accepted` - Customer accepts offer
- `payment_complete` - Payment successful
- `refund_issued` - Refund processed
- `booking_completed` - Booking finished

### Common Channels
- `email` - Email (requires subject + body/html_body)
- `sms` - SMS (body only, keep under 160 chars)
- `whatsapp` - WhatsApp message
- `web` - In-app notification

### Status Flow
```
pending → sent → delivered → read (success path)
pending → sent → failed (can retry)
```

### Key Routes
- Templates List: `/admin/notifications/templates`
- Create Template: `/admin/notifications/templates/create`
- Notification Logs: `/admin/notifications/logs`

---

## Support

For issues or questions:
1. Check this documentation
2. Review code comments in core files
3. Check `storage/logs/laravel.log`
4. Test with "Test Send" feature
5. Review notification logs in admin panel

---

**Last Updated**: December 9, 2025  
**Version**: 1.0

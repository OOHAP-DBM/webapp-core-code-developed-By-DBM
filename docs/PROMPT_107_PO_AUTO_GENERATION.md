# PROMPT 107: Auto-Generate Purchase Order After Quotation Acceptance

## Overview

**Feature**: Automatically generate Purchase Order (PO) document when customer accepts a quotation, then attach it to the conversation thread and notify vendor.

**Status**: ✅ Complete

**Dependencies**: 
- PROMPT 23 (Quotation System)
- PROMPT 105 (Offer Expiry)
- Thread System

---

## Business Flow

```
Customer Approves Quotation
         ↓
QuotationApproved Event Fired
         ↓
GeneratePurchaseOrder Listener Triggered
         ↓
   Create PO Record
         ├─> Generate unique PO number (PO-202512-0001)
         ├─> Copy quotation details (items, amounts, milestones)
         └─> Set status = PENDING
         ↓
   Generate PDF Document
         ├─> Load PO template with all details
         ├─> Render professional PDF layout
         └─> Store in private/purchase-orders/{id}/
         ↓
   Attach to Conversation Thread
         ├─> Get or create thread for enquiry
         ├─> Create system message with PDF attachment
         ├─> Increment unread counts for both parties
         └─> Update thread last_message_at
         ↓
   Send Notifications
         ├─> Email + database notification to vendor
         ├─> Email + database notification to customer
         └─> Different content for each role
         ↓
   Mark PO as SENT
         └─> Update sent_at timestamp
```

---

## Files Created/Modified

### Models

**1. PurchaseOrder Model** (`app/Models/PurchaseOrder.php` - 265 lines)

**Key Fields**:
- `po_number` - Unique identifier (e.g., PO-202512-0001)
- `quotation_id` - Reference to quotation
- `customer_id`, `vendor_id` - Parties involved
- `enquiry_id`, `offer_id` - Traceability
- `items` - JSON array of line items
- `total_amount`, `tax`, `discount`, `grand_total` - Pricing
- `has_milestones`, `payment_mode`, `milestone_summary` - Payment details
- `pdf_path`, `pdf_generated_at` - PDF storage
- `status` - pending, sent, confirmed, cancelled
- `thread_id`, `thread_message_id` - Thread integration

**Status Constants**:
```php
const STATUS_PENDING = 'pending';
const STATUS_SENT = 'sent';
const STATUS_CONFIRMED = 'confirmed';
const STATUS_CANCELLED = 'cancelled';
```

**Key Methods**:
- `generatePoNumber()` - Creates unique PO number with YYYYMM-sequence format
- `markAsSent()` - Transitions to SENT status
- `markAsConfirmed()` - Customer confirms PO
- `vendorAcknowledge()` - Vendor acknowledges receipt
- `cancel($reason, $cancelledBy)` - Cancels PO with audit trail
- `canConfirm()`, `canCancel()` - Business rule checks
- `getPdfFilename()` - Returns formatted PDF filename
- `getFormattedGrandTotal()` - Returns ₹ formatted amount
- `getStatusLabel()`, `getStatusBadgeColor()` - UI helpers
- `hasPdf()` - Checks if PDF generated

---

### Services

**2. PurchaseOrderService** (`app/Services/PurchaseOrderService.php` - 380 lines)

**Purpose**: Core business logic for PO generation and management

**Key Methods**:

```php
// PO Generation
generateFromQuotation(Quotation): PurchaseOrder
createPurchaseOrder(Quotation): PurchaseOrder

// PDF Management
generatePDF(PurchaseOrder): string
regeneratePDF(PurchaseOrder): string

// Thread Integration
attachToThread(PurchaseOrder): ?ThreadMessage
getThreadMessage(PurchaseOrder): string

// Notifications
notifyVendor(PurchaseOrder): void

// PO Management
cancelPO(PurchaseOrder, $reason, $cancelledBy): PurchaseOrder

// Queries
getByQuotation(int $quotationId): ?PurchaseOrder
getCustomerPOs(int $customerId): Collection
getVendorPOs(int $vendorId): Collection

// Utilities
getDefaultTermsAndConditions(): string
```

**Transaction Safety**:
All operations wrapped in database transactions for consistency.

**Error Handling**:
Comprehensive logging at every step, graceful degradation for non-critical failures.

---

### Event Listeners

**3. GeneratePurchaseOrder Listener** (`Modules/Quotations/Listeners/GeneratePurchaseOrder.php` - 60 lines)

**Trigger**: Listens to `QuotationApproved` event

**Features**:
- Implements `ShouldQueue` for background processing
- Uses `InteractsWithQueue` for job control
- Comprehensive error logging
- Does not block other listeners on failure

**Behavior**:
```php
// When quotation approved
QuotationApproved event fired
         ↓
Listener receives event
         ↓
Calls PurchaseOrderService.generateFromQuotation()
         ↓
PO generated with PDF, thread attachment, notifications
         ↓
Logs success or error (without throwing exception)
```

---

### Notifications

**4. PurchaseOrderGeneratedNotification** (`app/Notifications/PurchaseOrderGeneratedNotification.php` - 105 lines)

**Channels**: `['mail', 'database']`

**Features**:
- Implements `ShouldQueue` for background sending
- Role-specific content (customer vs vendor)

**Email Content (Vendor)**:
- Subject: "Purchase Order Generated - PO-202512-0001"
- Greeting with vendor name
- PO details (number, customer name, amount, payment mode)
- Next steps (review, acknowledge, execute)
- Action button linking to thread
- Professional closing

**Email Content (Customer)**:
- Subject: "Purchase Order Generated - PO-202512-0001"
- Greeting with customer name
- PO details and confirmation that it's attached to thread
- Instructions to review and confirm
- Action button linking to thread
- Thank you message

**Database Payload**:
```php
[
    'type' => 'purchase_order_generated',
    'po_id' => int,
    'po_number' => string,
    'quotation_id' => int,
    'customer_id' => int,
    'vendor_id' => int,
    'amount' => float,
    'payment_mode' => string,
    'thread_id' => int,
    'message' => string,
    'created_at' => ISO8601,
]
```

---

### PDF Templates

**5. Purchase Order PDF** (`resources/views/pdf/purchase-order.blade.php` - 430 lines)

**Design**: Professional, print-ready document with complete details

**Sections**:
1. **Header** - Company branding, PO title
2. **PO Number and Date** - Unique identifier and generation timestamp
3. **Parties Information** - Customer (Bill To) and Vendor (Ship To) details
4. **Order Information** - Quotation ID, Enquiry ID, Offer ID, Payment Mode, Status
5. **Line Items Table** - Description, Quantity, Rate, Amount columns
6. **Summary** - Subtotal, Tax, Discount, Grand Total (highlighted)
7. **Milestone Details** - If milestone payment, shows schedule table
8. **Notes** - Custom notes from quotation
9. **Terms and Conditions** - Standard 8 terms
10. **Footer** - System-generated notice, generation timestamp
11. **Signature Section** - Placeholders for Customer and Vendor

**Styling**:
- Professional blue color scheme (#2563eb)
- Clear typography with DejaVu Sans font
- Responsive table layouts
- Badge indicators for status
- Highlighted grand total section
- Color-coded milestone section (blue)
- Warning-colored terms section (yellow)

---

### Migrations

**6. Create Purchase Orders Table** (`database/migrations/2025_12_13_create_purchase_orders_table.php`)

**Schema**:
```sql
CREATE TABLE purchase_orders (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    po_number VARCHAR UNIQUE,
    quotation_id BIGINT FOREIGN KEY,
    customer_id BIGINT FOREIGN KEY,
    vendor_id BIGINT FOREIGN KEY,
    enquiry_id BIGINT FOREIGN KEY,
    offer_id BIGINT FOREIGN KEY,
    items JSON,
    total_amount DECIMAL(12,2),
    tax DECIMAL(12,2),
    discount DECIMAL(12,2),
    grand_total DECIMAL(12,2),
    has_milestones BOOLEAN,
    payment_mode VARCHAR,
    milestone_count INTEGER,
    milestone_summary JSON,
    pdf_path VARCHAR,
    pdf_generated_at TIMESTAMP,
    status ENUM('pending','sent','confirmed','cancelled'),
    sent_at TIMESTAMP,
    confirmed_at TIMESTAMP,
    cancelled_at TIMESTAMP,
    cancelled_by VARCHAR,
    cancellation_reason TEXT,
    customer_approved_at TIMESTAMP,
    vendor_acknowledged_at TIMESTAMP,
    thread_id BIGINT FOREIGN KEY,
    thread_message_id BIGINT FOREIGN KEY,
    notes TEXT,
    terms_and_conditions TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

**Indexes**:
- `po_number` (unique, indexed)
- `quotation_id`, `customer_id`, `vendor_id` (foreign keys + indexes)
- `status`, `sent_at` (query optimization)
- Composite index on `(quotation_id, status)`

---

### Tests

**7. PurchaseOrderServiceTest** (`tests/Feature/PurchaseOrderServiceTest.php` - 460 lines, 27 tests)

**Test Coverage**:

**Generation Tests**:
- ✅ Generates PO from approved quotation
- ✅ Throws exception if quotation not approved
- ✅ Does not create duplicate PO for same quotation
- ✅ Generates unique PO numbers
- ✅ Copies quotation details correctly

**PDF Tests**:
- ✅ Generates PDF for PO
- ✅ Can regenerate PDF
- ✅ Checks PDF existence
- ✅ Generates correct PDF filename

**Thread Integration Tests**:
- ✅ Attaches PO to thread
- ✅ Increments thread unread counts
- ✅ Posts system message with attachment

**Notification Tests**:
- ✅ Sends notification to vendor
- ✅ Sends notification to customer

**Milestone Tests**:
- ✅ Handles milestone payments correctly

**Status Tests**:
- ✅ PO status changes correctly
- ✅ Has correct status labels
- ✅ Has correct badge colors

**Management Tests**:
- ✅ Can cancel PO
- ✅ Posts system message on cancellation
- ✅ Vendor can acknowledge PO

**Query Tests**:
- ✅ Retrieves PO by quotation
- ✅ Retrieves customer POs
- ✅ Retrieves vendor POs

**Event Tests**:
- ✅ Triggers PO generation on quotation approved event

**Utility Tests**:
- ✅ Formats grand total correctly

---

### Factories

**8. PurchaseOrderFactory** (`database/factories/PurchaseOrderFactory.php` - 130 lines)

**States**:
- `sent()` - PO in sent status
- `confirmed()` - PO confirmed by customer
- `cancelled()` - PO cancelled
- `withMilestones()` - PO with milestone payments
- `withPdf()` - PO with generated PDF

**Usage**:
```php
// Basic PO
PurchaseOrder::factory()->create();

// Sent PO
PurchaseOrder::factory()->sent()->create();

// Confirmed PO with milestones
PurchaseOrder::factory()->confirmed()->withMilestones()->create();

// Cancelled PO
PurchaseOrder::factory()->cancelled()->create();
```

---

### Event Registration

**9. AppServiceProvider** (`app/Providers/AppServiceProvider.php` - Modified)

Added event listener registration:
```php
Event::listen(
    QuotationApproved::class,
    \Modules\Quotations\Listeners\GeneratePurchaseOrder::class
);
```

---

## Usage Examples

### Automatic Generation (Primary Flow)

```php
// Customer approves quotation (in QuotationService)
$quotation = $quotationService->approveQuotation($quotationId);

// Behind the scenes:
// 1. QuotationApproved event fired
// 2. GeneratePurchaseOrder listener triggered
// 3. PO auto-generated with PDF, thread attachment, notifications
```

---

### Manual Generation (Admin/Debugging)

```php
use App\Services\PurchaseOrderService;

$poService = app(PurchaseOrderService::class);
$po = $poService->generateFromQuotation($quotation);

// Returns:
// - PO record created
// - PDF generated and stored
// - Thread message with attachment created
// - Notifications sent to customer and vendor
// - Status set to SENT
```

---

### Check If PO Exists

```php
$po = $poService->getByQuotation($quotationId);

if ($po) {
    echo "PO Number: {$po->po_number}";
    echo "Status: {$po->getStatusLabel()}";
    echo "PDF: " . ($po->hasPdf() ? 'Yes' : 'No');
}
```

---

### Regenerate PDF

```php
$newPath = $poService->regeneratePDF($po);
// Old PDF deleted, new PDF generated
```

---

### Customer Confirms PO

```php
if ($po->canConfirm()) {
    $po->markAsConfirmed();
    // status = confirmed
    // confirmed_at = now()
    // customer_approved_at = now()
}
```

---

### Vendor Acknowledges PO

```php
$po->vendorAcknowledge();
// vendor_acknowledged_at = now()
```

---

### Cancel PO

```php
$po = $poService->cancelPO($po, 'Customer changed requirements', $customerId);

// Result:
// - status = cancelled
// - cancelled_at = now()
// - cancelled_by = customer ID
// - cancellation_reason saved
// - System message posted to thread
```

---

### Get Customer's POs

```php
$customerPOs = $poService->getCustomerPOs($customerId);

foreach ($customerPOs as $po) {
    echo "{$po->po_number} - {$po->getFormattedGrandTotal()} - {$po->getStatusLabel()}\n";
}
```

---

### Get Vendor's POs

```php
$vendorPOs = $poService->getVendorPOs($vendorId);

foreach ($vendorPOs as $po) {
    echo "{$po->po_number} from {$po->customer->name} - {$po->getFormattedGrandTotal()}\n";
}
```

---

## PO Number Format

**Pattern**: `PO-{YYYYMM}-{Sequence}`

**Examples**:
- `PO-202512-0001` - First PO of December 2025
- `PO-202512-0042` - 42nd PO of December 2025
- `PO-202601-0001` - First PO of January 2026 (sequence resets monthly)

**Generation Logic**:
1. Get current month (YYYYMM)
2. Find last PO number for this month
3. Extract sequence number and increment
4. If no POs this month, start with 0001
5. Pad sequence to 4 digits

---

## Thread Integration

### System Message Format

```
📄 **Purchase Order Generated**

PO Number: PO-202512-0001
Quotation ID: #42
Amount: ₹ 70,800.00

A Purchase Order has been automatically generated based on the approved quotation. 
Please download the attached PDF for complete details.

**Next Steps:**
- Customer: Review and confirm the PO
- Vendor: Acknowledge receipt and proceed with order execution
```

### Attachment Structure

```json
{
    "name": "purchase-order-PO-202512-0001.pdf",
    "path": "purchase-orders/123/purchase-order-PO-202512-0001.pdf",
    "size": 45678,
    "type": "application/pdf",
    "url": "https://example.com/storage/private/purchase-orders/123/..."
}
```

---

## Notification Examples

### Vendor Email

**Subject**: Purchase Order Generated - PO-202512-0001

```
Hello ABC Vendors!

Great news! A Purchase Order has been issued for quotation #42.

**Purchase Order Details:**
PO Number: PO-202512-0001
Customer: John Doe
Quotation ID: #42
Amount: ₹ 70,800.00
Payment Mode: Full

**Next Steps:**
1. Review the PO document attached to the conversation thread
2. Acknowledge receipt of the PO
3. Proceed with order execution as per timeline

[View Conversation]

Please ensure timely delivery and quality standards.

Thank you for being our valued partner!
```

---

### Customer Email

**Subject**: Purchase Order Generated - PO-202512-0001

```
Hello John Doe!

A Purchase Order has been automatically generated for your approved quotation.

**Purchase Order Details:**
PO Number: PO-202512-0001
Quotation ID: #42
Amount: ₹ 70,800.00
Payment Mode: Full

The PO document has been attached to your conversation thread.
You can review and confirm the purchase order to proceed.

[View Conversation]

Thank you for using our platform!
```

---

## Deployment Steps

### 1. Install Dependencies

```bash
composer require barryvdh/laravel-dompdf
```

✅ Already done

### 2. Run Migrations

```bash
php artisan migrate
```

**Creates**: `purchase_orders` table

✅ Migration run successfully

### 3. Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 4. Test Generation

```bash
php artisan test tests/Feature/PurchaseOrderServiceTest.php
```

**Expected**: All 27 tests passing

### 5. Verify Event Registration

```bash
php artisan event:list | grep QuotationApproved
```

**Expected**: Shows `GeneratePurchaseOrder` listener

### 6. Test in Browser

1. Login as customer
2. Navigate to quotation
3. Approve quotation
4. Check thread for PO attachment
5. Check email for PO notifications

---

## Troubleshooting

### Issue: PO Not Generated

**Symptoms**: Quotation approved but no PO created

**Possible Causes**:
1. Queue not processing
2. Event listener not registered
3. Storage permissions

**Solutions**:
```bash
# 1. Check queue
php artisan queue:work

# 2. Check event listeners
php artisan event:list

# 3. Check storage permissions
chmod -R 775 storage/app/private

# 4. Check logs
tail -f storage/logs/laravel.log | grep "Purchase Order"
```

---

### Issue: PDF Not Generated

**Symptoms**: PO created but pdf_path is null

**Possible Causes**:
1. DomPDF not installed
2. Storage disk misconfigured
3. Template rendering error

**Solutions**:
```bash
# 1. Verify DomPDF
composer show barryvdh/laravel-dompdf

# 2. Test PDF generation
php artisan tinker
>>> $po = App\Models\PurchaseOrder::first();
>>> app(App\Services\PurchaseOrderService::class)->generatePDF($po);

# 3. Check template errors
# Review storage/logs/laravel.log for rendering errors
```

---

### Issue: Notifications Not Sent

**Symptoms**: PO generated but no emails received

**Possible Causes**:
1. Queue not processing
2. Email configuration
3. Notification class errors

**Solutions**:
```bash
# 1. Process queue
php artisan queue:work

# 2. Test email config
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));

# 3. Check notification table
>>> App\Models\User::find(1)->notifications()->count();
```

---

### Issue: Duplicate PO Numbers

**Symptoms**: Two POs with same number

**Cause**: Race condition in concurrent requests

**Solution**: Ensure database transaction isolation and unique constraint on `po_number`

```php
// Already handled via:
// 1. Database unique constraint
// 2. Transaction wrapping
// 3. Duplicate check in generateFromQuotation()
```

---

### Issue: Thread Attachment Not Showing

**Symptoms**: PO created but not visible in thread

**Possible Causes**:
1. Thread service error
2. Storage URL misconfigured
3. Private disk access issue

**Solutions**:
```bash
# 1. Check thread message
php artisan tinker
>>> $po = App\Models\PurchaseOrder::find(1);
>>> $message = Modules\Threads\Models\ThreadMessage::find($po->thread_message_id);
>>> $message->attachments;

# 2. Verify storage URL
>>> Storage::disk('private')->url('test.pdf');

# 3. Check filesystem config
# Review config/filesystems.php for 'private' disk
```

---

## Best Practices

### 1. Always Use Service Layer

```php
// ✅ Good
$poService = app(PurchaseOrderService::class);
$po = $poService->generateFromQuotation($quotation);

// ❌ Bad
$po = PurchaseOrder::create([...]);
// Missing PDF, thread, notifications
```

### 2. Check Status Before Actions

```php
// ✅ Good
if ($po->canConfirm()) {
    $po->markAsConfirmed();
}

// ❌ Bad
$po->markAsConfirmed();
// May fail if already confirmed or cancelled
```

### 3. Handle Errors Gracefully

```php
// ✅ Good
try {
    $po = $poService->generateFromQuotation($quotation);
} catch (\Exception $e) {
    Log::error('PO generation failed', ['error' => $e->getMessage()]);
    return response()->json(['error' => 'Could not generate PO'], 500);
}

// ❌ Bad
$po = $poService->generateFromQuotation($quotation);
// No error handling
```

### 4. Use Factories in Tests

```php
// ✅ Good
$po = PurchaseOrder::factory()->confirmed()->create();

// ❌ Bad
$po = PurchaseOrder::create([
    'po_number' => 'PO-TEST-001',
    // ... 30 more fields
]);
```

---

## Summary

**PROMPT 107 Status**: ✅ Complete

**Files Created**: 9 files
- 1 Migration
- 1 Model
- 1 Service
- 1 Event Listener
- 1 Notification
- 1 PDF Template
- 1 Factory
- 1 Test File (27 tests)
- 1 Modified (AppServiceProvider)

**Total Lines**: ~2,100 lines (production + tests)

**Features Delivered**:
- ✅ Auto-generate PO when quotation approved
- ✅ Generate professional PDF document
- ✅ Attach PDF to conversation thread
- ✅ Send email + database notifications to vendor
- ✅ Send email + database notifications to customer
- ✅ Unique PO numbering with monthly sequence
- ✅ Support for milestone payments
- ✅ Complete audit trail (timestamps, status changes)
- ✅ Cancellation workflow
- ✅ PDF regeneration capability
- ✅ Comprehensive test coverage (27 tests)
- ✅ Professional PDF template with all details
- ✅ Full documentation

**Integration Points**:
- ✅ Quotation System (PROMPT 23)
- ✅ Event System (QuotationApproved)
- ✅ Thread System (attachments, messages)
- ✅ Notification System (mail + database)
- ✅ PDF Generation (DomPDF)
- ✅ Storage System (private disk)

---

**Version**: 1.0  
**Date**: December 13, 2025  
**Status**: ✅ Ready for Production  
**Dependencies**: barryvdh/laravel-dompdf ^3.1

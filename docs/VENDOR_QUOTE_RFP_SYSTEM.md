# Vendor Quote & RFP System (PROMPT 44 & 45)

## Overview
This module implements a comprehensive **Request for Proposal (RFP)** and **Vendor Quote Management System** for the OOH App platform. It enables customers to request quotes from vendors and vendors to submit professional, itemized quotes with automatic PDF generation and email delivery.

## Features

### PROMPT 44: Vendor Quote Generator
- ✅ Auto-generates professional PDF quotes
- ✅ Sends quotes to customers via email with PDF attachment
- ✅ Stores immutable JSON snapshots of quote data
- ✅ Comprehensive pricing breakdown:
  - Base price
  - Printing cost
  - Mounting cost
  - Survey cost
  - Lighting cost
  - Maintenance cost
  - Other charges
  - Discount (amount or percentage)
  - Tax/GST
  - Grand total
- ✅ Quote versioning system (create revisions)
- ✅ Vendor notes and terms & conditions
- ✅ Quote expiry management (default 7 days)
- ✅ Status workflow: draft → sent → viewed → accepted/rejected

### PROMPT 45: Customer RFP Workflow
- ✅ Customer creates Quote Request (RFP)
- ✅ Multi-vendor support:
  - Invite specific vendors
  - Open to all eligible vendors
  - Single or multiple vendor selection
- ✅ Vendors receive notifications about new RFPs
- ✅ Vendors submit quotes against RFP
- ✅ Customer compares multiple quotes
- ✅ Customer accepts quote → automatic booking creation
- ✅ Deadline tracking (response deadline, decision deadline)
- ✅ Budget range specification

## Database Schema

### Tables Created
1. **`quote_requests`** - Customer RFP system
2. **`vendor_quotes`** - Vendor quote submissions

### Relationships
```
QuoteRequest (1) → (Many) VendorQuotes
Enquiry (1) → (Many) VendorQuotes (optional)
VendorQuote → Booking (when accepted)
VendorQuote → VendorQuote (parent-child for revisions)
```

## API Endpoints

### Vendor Quote Endpoints

#### Vendor Routes
```
GET     /api/v1/vendor-quotes                  - List vendor's quotes
POST    /api/v1/vendor-quotes/from-request     - Create quote from RFP
POST    /api/v1/vendor-quotes/from-enquiry     - Create quote from enquiry
PUT     /api/v1/vendor-quotes/{id}             - Update draft quote
POST    /api/v1/vendor-quotes/{id}/send        - Send quote to customer
POST    /api/v1/vendor-quotes/{id}/revise      - Create revision
```

#### Customer Routes
```
GET     /api/v1/vendor-quotes/customer         - List received quotes
POST    /api/v1/vendor-quotes/{id}/accept      - Accept quote (creates booking)
POST    /api/v1/vendor-quotes/{id}/reject      - Reject quote
```

#### Common Routes
```
GET     /api/v1/vendor-quotes/{id}             - View quote details
GET     /api/v1/vendor-quotes/{id}/pdf         - Download PDF
POST    /api/v1/vendor-quotes/calculate-pricing - Calculate pricing breakdown
```

### Quote Request Endpoints

#### Customer Routes
```
GET     /api/v1/quote-requests                 - List customer's RFPs
POST    /api/v1/quote-requests                 - Create new RFP
GET     /api/v1/quote-requests/{id}            - View RFP details
PUT     /api/v1/quote-requests/{id}            - Update draft RFP
POST    /api/v1/quote-requests/{id}/publish    - Publish RFP (notify vendors)
GET     /api/v1/quote-requests/{id}/comparison - Compare received quotes
POST    /api/v1/quote-requests/{id}/accept-quote - Accept a quote
POST    /api/v1/quote-requests/{id}/close      - Close RFP
POST    /api/v1/quote-requests/{id}/cancel     - Cancel RFP
```

#### Vendor Routes
```
GET     /api/v1/quote-requests/vendor/pending  - List pending RFPs for vendor
```

## Business Workflow

### 1. Customer RFP Flow
```mermaid
Customer creates RFP → Specifies requirements → Invites vendors → Publishes RFP
                                                                        ↓
Customer accepts quote ← Customer compares quotes ← Vendors submit quotes
         ↓
  Booking created automatically
```

### 2. Vendor Quote Flow
```mermaid
Vendor receives RFP notification → Creates quote → Adds pricing → Sends to customer
                                                                         ↓
Vendor receives acceptance notification ← Customer accepts ← Customer views quote
                   ↓
            Booking confirmed
```

### 3. Quote Versioning
```mermaid
Original Quote (v1) → Customer requests changes → Vendor creates Revision (v2)
                                                            ↓
                                                  New quote with parent_quote_id
```

## Models

### VendorQuote Model
**Location**: `app/Models/VendorQuote.php` (470 lines)

**Key Features**:
- 8 relationships (quoteRequest, enquiry, hoarding, customer, vendor, parentQuote, revisions, booking)
- Auto-generates unique quote_number (VQ-XXXXXXXXXX)
- Auto-calculates totals (subtotal, tax, grand_total)
- Status workflow management
- Expiry tracking (default 7 days)
- Immutable snapshots (hoarding_snapshot, quote_snapshot)
- Soft deletes for audit trail

**Key Methods**:
```php
// Calculations
$quote->calculateSubtotal()      // Sum all charges
$quote->calculateTax($subtotal)  // Apply tax percentage
$quote->calculateGrandTotal()    // subtotal - discount + tax
$quote->recalculateTotals()      // Update all totals

// Actions
$quote->markAsSent()             // Mark quote as sent
$quote->markAsViewed()           // Mark quote as viewed
$quote->accept()                 // Accept quote (creates snapshot)
$quote->reject($reason)          // Reject with reason
$quote->createRevision()         // Create new version

// Checks
$quote->canSend()
$quote->canAccept()
$quote->canReject()
$quote->canRevise()

// Helpers
$quote->getPdfFilename()         // Returns: "quote-VQ-XXX-v1.pdf"
$quote->getFormattedGrandTotal() // Returns: "₹ 50,000.00"
$quote->getDaysUntilExpiry()
```

### QuoteRequest Model
**Location**: `app/Models/QuoteRequest.php` (330 lines)

**Key Features**:
- Auto-generates unique request_number (QR-XXXXXXXXXX)
- Multi-vendor support (invited_vendor_ids, open_to_all_vendors)
- Budget range specification
- Requirements tracking (printing, mounting, lighting, additional services)
- Deadline management (response_deadline, decision_deadline)
- Hoarding snapshot for immutability
- Soft deletes

**Key Methods**:
```php
// Actions
$request->publish()              // Publish and notify vendors
$request->selectQuote($quote)    // Accept a quote
$request->close()                // Close request
$request->cancel()               // Cancel request
$request->markExpired()          // Mark as expired

// Checks
$request->canPublish()
$request->canReceiveQuotes()
$request->canSelectQuote()
$request->isVendorEligible($vendorId)
$request->hasVendorSubmittedQuote($vendorId)

// Helpers
$request->getEligibleVendors()   // Get vendor IDs who can quote
$request->getQuotesComparison()  // Get all quotes sorted by price
$request->getDaysUntilDeadline()
```

## Services

### VendorQuoteService
**Location**: `app/Services/VendorQuoteService.php` (280 lines)

**Methods**:
```php
// Quote Creation
createFromQuoteRequest($quoteRequest, $vendor, $data)
createFromEnquiry($enquiry, $vendor, $data)
updateQuote($quote, $data)

// Quote Actions
sendQuote($quote)                // Generate PDF + send email
generatePDF($quote)              // Create PDF from template
acceptQuote($quote)              // Accept + create booking
rejectQuote($quote, $reason)
createRevision($quote, $changes)

// Utilities
calculatePricing($data)          // Calculate all pricing fields
getDefaultTerms()                // Default T&C structure
```

### QuoteRequestService
**Location**: `app/Services/QuoteRequestService.php` (260 lines)

**Methods**:
```php
// Request Management
createRequest($customer, $data)
updateRequest($quoteRequest, $data)
publishRequest($quoteRequest)    // Publish + notify vendors
closeRequest($quoteRequest)
cancelRequest($quoteRequest, $reason)

// Vendor Actions
notifyVendors($quoteRequest)
submitVendorQuote($quoteRequest, $vendor, $quoteData)
canVendorSubmitQuote($quoteRequest, $vendor)

// Customer Actions
acceptQuote($quoteRequest, $quote) // Accept + create booking
getQuoteComparison($quoteRequest)  // Compare all quotes

// Utilities
getPendingRequestsForVendor($vendor)
getCustomerRequests($customer, $filters)
markExpiredRequests()            // Cron job to mark expired
```

## PDF Template

**Location**: `resources/views/pdf/vendor-quote.blade.php` (350 lines)

**Features**:
- Professional layout with branding
- Complete quote details
- Pricing breakdown table
- Vendor notes section
- Terms & conditions
- Status badges
- Validity information
- Contact details

**Styling**:
- Uses inline CSS for PDF compatibility
- DejaVu Sans font (supports special characters)
- Responsive table layout
- Color-coded status badges
- Highlighted important information

## Notifications

### 1. VendorQuoteSentNotification
**Location**: `app/Notifications/VendorQuoteSentNotification.php`

**Triggers**: When vendor sends quote to customer
**Channels**: Email + Database
**Attachments**: Quote PDF
**Recipient**: Customer

### 2. QuoteAcceptedNotification
**Location**: `app/Notifications/QuoteAcceptedNotification.php`

**Triggers**: When customer accepts quote
**Channels**: Email + Database
**Recipient**: Vendor
**Includes**: Booking details

### 3. QuoteRequestPublishedNotification
**Location**: `app/Notifications/QuoteRequestPublishedNotification.php`

**Triggers**: When customer publishes RFP
**Channels**: Email + Database
**Recipients**: Invited vendors
**Includes**: Requirements, budget, deadline

### 4. QuoteRequestClosedNotification
**Location**: `app/Notifications/QuoteRequestClosedNotification.php`

**Triggers**: When RFP is closed
**Channels**: Email + Database
**Recipient**: Customer
**Includes**: Selected quote details

## Controllers

### VendorQuoteController
**Location**: `app/Http/Controllers/Api/V1/VendorQuoteController.php` (390 lines)

**Responsibilities**:
- CRUD operations for quotes
- Send/accept/reject actions
- PDF download
- Quote revisions
- Pricing calculator

### QuoteRequestController
**Location**: `app/Http/Controllers/Api/V1/QuoteRequestController.php` (260 lines)

**Responsibilities**:
- CRUD operations for RFPs
- Publish/close/cancel actions
- Quote comparison
- Accept quote and create booking
- Vendor-specific RFP listing

## Usage Examples

### Example 1: Customer Creates RFP
```php
POST /api/v1/quote-requests
{
  "hoarding_id": 123,
  "preferred_start_date": "2025-01-15",
  "preferred_end_date": "2025-02-15",
  "duration_days": 31,
  "duration_type": "days",
  "requirements": "Need high-quality printing and professional mounting",
  "printing_required": true,
  "mounting_required": true,
  "lighting_required": false,
  "budget_min": 40000,
  "budget_max": 60000,
  "vendor_selection_mode": "multiple",
  "open_to_all_vendors": true,
  "response_deadline": "2025-01-10 23:59:59"
}
```

### Example 2: Vendor Submits Quote
```php
POST /api/v1/vendor-quotes/from-request
{
  "quote_request_id": 456,
  "base_price": 35000,
  "printing_cost": 8000,
  "mounting_cost": 5000,
  "survey_cost": 1000,
  "lighting_cost": 0,
  "maintenance_cost": 2000,
  "discount_percentage": 5,
  "tax_percentage": 18,
  "vendor_notes": "Premium quality printing included. Installation within 2 days.",
  "auto_send": true
}
```

### Example 3: Customer Compares Quotes
```php
GET /api/v1/quote-requests/456/comparison

Response:
{
  "request": { ... },
  "quotes": [
    {
      "quote_id": 789,
      "vendor_name": "Vendor A",
      "grand_total": 48500.00,
      "base_price": 35000,
      "survey_cost": 1000,
      "lighting_cost": 0,
      "tax_amount": 7830,
      "sent_at": "2025-01-08 10:30:00"
    },
    {
      "quote_id": 790,
      "vendor_name": "Vendor B",
      "grand_total": 52000.00,
      ...
    }
  ]
}
```

### Example 4: Customer Accepts Quote
```php
POST /api/v1/quote-requests/456/accept-quote
{
  "quote_id": 789
}

Response:
{
  "success": true,
  "message": "Quote accepted and booking created successfully",
  "data": {
    "quote_request": { ... },
    "quote": { ... },
    "booking": {
      "id": 1001,
      "booking_number": "BK-ABC123",
      "status": "confirmed",
      "total_amount": 48500.00
    }
  }
}
```

## Configuration

### Environment Variables
```env
# PDF Generation
PDF_FONT_PATH=/fonts
PDF_DEFAULT_FONT=dejavu-sans

# Quote Settings
QUOTE_DEFAULT_EXPIRY_DAYS=7
QUOTE_DEFAULT_TAX_RATE=18

# Email Settings
MAIL_FROM_ADDRESS=quotes@oohapp.com
MAIL_FROM_NAME="OOH App Quotes"
```

### Storage Configuration
Quotes PDFs are stored in: `storage/app/private/quotes/{quote_id}/`

## Testing

### Run Migrations
```bash
php artisan migrate
```

### Test PDF Generation
```php
php artisan tinker
>>> $quote = VendorQuote::first();
>>> $service = app(VendorQuoteService::class);
>>> $path = $service->generatePDF($quote);
>>> echo $path;
```

### Test Email Sending
```php
php artisan tinker
>>> $quote = VendorQuote::first();
>>> $service = app(VendorQuoteService::class);
>>> $service->sendQuote($quote);
```

## Scheduled Tasks

Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // Mark expired quote requests
    $schedule->call(function () {
        app(QuoteRequestService::class)->markExpiredRequests();
    })->daily();
}
```

## Integration Points

### Existing Systems
1. **Enquiry System**: Vendors can create quotes from existing enquiries
2. **Booking System**: Accepted quotes automatically create bookings
3. **Thread System**: Can be integrated for quote discussions
4. **Notification System**: Uses existing notification infrastructure
5. **Audit System**: Uses Auditable trait for change tracking
6. **Snapshot System**: Uses HasSnapshots trait for immutability

### New vs Existing Quotation System
- **Existing**: `Quotation` model (offer-based quotations)
- **New**: `VendorQuote` model (RFP-based quotes)
- Both systems coexist and serve different workflows
- VendorQuote supports both quote_request_id and enquiry_id

## Security

### Authorization
- Vendors can only view/edit their own quotes
- Customers can only view quotes sent to them
- Quote PDFs protected by authentication
- Quote acceptance requires ownership verification

### Data Integrity
- Immutable snapshots on acceptance
- Soft deletes for audit trail
- Version tracking for quote revisions
- Parent-child relationship for quote history

## Performance

### Database Indexes
- `quote_number` (unique index)
- `status` (index for filtering)
- `vendor_id` (index for vendor queries)
- `customer_id` (index for customer queries)
- `quote_request_id` (foreign key index)
- `expires_at` (index for expiry checks)

### Query Optimization
- Eager loading relationships (`with()`)
- Pagination for large result sets
- Scopes for common queries
- Efficient JSON queries for snapshots

## Troubleshooting

### PDF Generation Issues
```bash
# Check PDF library installation
composer show barryvdh/laravel-dompdf

# Clear cache
php artisan config:clear
php artisan view:clear
```

### Migration Issues
```bash
# Check migration status
php artisan migrate:status

# Rollback specific migration
php artisan migrate:rollback --step=1

# Fresh migration (WARNING: Drops all tables)
php artisan migrate:fresh
```

### Email Not Sending
```bash
# Test mail configuration
php artisan tinker
>>> Mail::raw('Test', function($msg) { $msg->to('test@example.com'); });

# Check queue jobs
php artisan queue:work
```

## File Structure
```
app/
├── Models/
│   ├── VendorQuote.php           (470 lines)
│   └── QuoteRequest.php          (330 lines)
├── Services/
│   ├── VendorQuoteService.php    (280 lines)
│   └── QuoteRequestService.php   (260 lines)
├── Http/Controllers/Api/V1/
│   ├── VendorQuoteController.php (390 lines)
│   └── QuoteRequestController.php (260 lines)
└── Notifications/
    ├── VendorQuoteSentNotification.php
    ├── QuoteAcceptedNotification.php
    ├── QuoteRequestPublishedNotification.php
    └── QuoteRequestClosedNotification.php

database/migrations/
├── 2025_12_10_053806_create_quote_requests_table.php
└── 2025_12_10_053900_create_vendor_quotes_table.php

resources/views/pdf/
└── vendor-quote.blade.php        (350 lines)

routes/api_v1/
└── vendor-quotes.php             (50 lines)
```

## Dependencies

### Required Packages
```json
{
  "barryvdh/laravel-dompdf": "^2.0",
  "laravel/framework": "^10.0"
}
```

### Install PDF Library
```bash
composer require barryvdh/laravel-dompdf
```

## Future Enhancements

### Potential Features
1. **Quote Templates**: Pre-defined pricing templates for vendors
2. **Quote Analytics**: Track quote acceptance rates, average quote values
3. **Negotiation System**: Allow customers to request price adjustments
4. **Bulk Quoting**: Vendors submit quotes for multiple RFPs at once
5. **Smart Matching**: AI-based vendor-RFP matching
6. **Quote Comparison Tools**: Advanced comparison with charts
7. **Custom PDF Templates**: Vendor-specific PDF branding
8. **Multi-currency Support**: International quotes
9. **Quote Approvals**: Internal approval workflow for vendors
10. **Historical Analysis**: Track pricing trends over time

## Support

For issues or questions:
- Check error logs: `storage/logs/laravel.log`
- Enable debug mode: `APP_DEBUG=true`
- Review API documentation: `/api/documentation`

## Credits

- **Implementation**: PROMPT 44 & 45
- **Date**: December 10, 2025
- **Total Lines**: ~2,500+ lines of code
- **Files Created**: 13 files
- **Status**: ✅ Complete and tested

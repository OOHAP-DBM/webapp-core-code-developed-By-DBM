# Vendor POS Booking Module - Complete Documentation

## Overview
The Vendor POS (Point of Sale) Booking Module enables vendors to create bookings directly from their panel without requiring customers to go through the online booking flow. This is ideal for walk-in customers, phone orders, or offline deals.

## Key Features

### 1. **No Online Payment Required**
- Vendors can create bookings without payment gateway integration
- Support for cash, credit notes, cheques, bank transfers
- Execute orders even without receiving customer payment
- Track payment status separately from booking status

### 2. **Payment Modes**
- **Cash**: Mark as cash collected with optional reference
- **Credit Note**: Issue credit note with configurable validity (default: 30 days)
- **Bank Transfer**: Record bank transfer details
- **Cheque**: Track cheque payments
- **Online**: For payment gateway transactions
- **Cancel Credit Note**: Ability to void credit notes with reason

### 3. **Admin-Configurable Settings**
All settings are managed through the `settings` table with the `pos` group:

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| `pos_auto_approval` | boolean | true | Auto-approve bookings without admin review |
| `pos_allow_cash_payment` | boolean | true | Allow cash payments |
| `pos_auto_invoice` | boolean | true | Auto-generate invoice on booking creation |
| `pos_credit_note_days` | integer | 30 | Credit note validity in days |
| `pos_gst_rate` | decimal | 18 | GST rate percentage |
| `pos_enable_sms_notification` | boolean | true | Send SMS to customers |
| `pos_enable_whatsapp_notification` | boolean | true | Send WhatsApp messages |
| `pos_enable_email_notification` | boolean | true | Send email notifications |
| `pos_allow_credit_note` | boolean | true | Enable credit note creation |
| `pos_require_customer_gstin` | boolean | false | Make GSTIN mandatory |

## Database Schema

### `pos_bookings` Table

```sql
id                      - Primary key
vendor_id               - Vendor who created the booking
customer_id             - Customer user ID (nullable for guests)
customer_name           - Customer name (required)
customer_email          - Customer email (optional)
customer_phone          - Customer phone (required)
customer_address        - Customer address (optional)
customer_gstin          - Customer GSTIN for GST invoice (optional)
booking_type            - 'ooh' or 'dooh'
hoarding_id             - Hoarding reference (nullable)
dooh_slot_id            - DOOH slot reference (nullable, for future)
start_date              - Booking start date
end_date                - Booking end date
duration_type           - 'days', 'weeks', or 'months'
duration_days           - Duration in days (calculated)
base_amount             - Base price before discount
discount_amount         - Discount applied
tax_amount              - GST/tax amount
total_amount            - Final amount (base - discount + tax)
payment_mode            - cash, credit_note, online, bank_transfer, cheque
payment_status          - paid, unpaid, partial, credit
paid_amount             - Amount paid so far
payment_reference       - Payment reference number
payment_notes           - Payment notes
credit_note_number      - Unique credit note number
credit_note_date        - Credit note issue date
credit_note_due_date    - Credit note validity date
credit_note_status      - active, cancelled, settled
status                  - draft, confirmed, active, completed, cancelled
invoice_number          - Unique invoice number
invoice_date            - Invoice date
invoice_path            - Path to invoice PDF
auto_approved           - Boolean flag
approved_at             - Approval timestamp
approved_by             - Admin who approved
booking_snapshot        - JSON snapshot of booking details
notes                   - Additional notes
cancellation_reason     - Reason for cancellation
confirmed_at            - Confirmation timestamp
cancelled_at            - Cancellation timestamp
created_at              - Creation timestamp
updated_at              - Last update timestamp
deleted_at              - Soft delete timestamp
```

### Indexes
- vendor_id, customer_id, booking_type
- payment_mode, payment_status, status
- invoice_number, credit_note_number (unique)
- hoarding_id + start_date + end_date (composite)
- status + payment_status (composite)

## API Endpoints

### Base URL: `/api/v1/vendor/pos`
**Authentication**: Required (`auth:sanctum`)  
**Authorization**: `role:vendor`

### 1. Dashboard Statistics
```
GET /dashboard
```
**Response**:
```json
{
  "success": true,
  "data": {
    "total_bookings": 150,
    "active_bookings": 25,
    "total_revenue": 450000.00,
    "pending_payments": 75000.00,
    "active_credit_notes": 10,
    "credit_notes_value": 50000.00
  }
}
```

### 2. Search Hoardings
```
GET /search-hoardings?search=mumbai&start_date=2025-12-10&end_date=2025-12-20
```
**Query Parameters**:
- `search` (optional): Search term for title, location
- `start_date` (optional): Check availability from date
- `end_date` (optional): Check availability to date

**Response**: Paginated list of available hoardings

### 3. Calculate Price
```
POST /calculate-price
```
**Request Body**:
```json
{
  "base_amount": 10000,
  "discount_amount": 1000
}
```
**Response**:
```json
{
  "success": true,
  "data": {
    "base_amount": 10000.00,
    "discount_amount": 1000.00,
    "amount_after_discount": 9000.00,
    "gst_rate": 18,
    "tax_amount": 1620.00,
    "total_amount": 10620.00
  }
}
```

### 4. List Bookings
```
GET /bookings?status=confirmed&payment_status=unpaid&search=John&per_page=20
```
**Query Parameters**:
- `status`: draft, confirmed, active, completed, cancelled
- `payment_status`: paid, unpaid, partial, credit
- `booking_type`: ooh, dooh
- `search`: Search in customer name, phone, invoice number
- `per_page`: Results per page (default: 15)

**Response**: Paginated bookings list with relationships

### 5. Create Booking
```
POST /bookings
```
**Request Body**:
```json
{
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "9876543210",
  "customer_address": "123 Main St, Mumbai",
  "customer_gstin": "27AAAAA0000A1Z5",
  "booking_type": "ooh",
  "hoarding_id": 123,
  "start_date": "2025-12-10",
  "end_date": "2025-12-20",
  "duration_type": "days",
  "base_amount": 10000,
  "discount_amount": 1000,
  "payment_mode": "cash",
  "payment_reference": "CASH001",
  "payment_notes": "Collected at office",
  "notes": "VIP customer"
}
```

**Validation Rules**:
- `customer_name`: required, max:255
- `customer_email`: optional, email
- `customer_phone`: required, max:20
- `booking_type`: required, in:ooh,dooh
- `hoarding_id`: required_if:booking_type,ooh
- `start_date`: required, date, after_or_equal:today
- `end_date`: required, date, after:start_date
- `base_amount`: required, numeric, min:0
- `payment_mode`: required, in:cash,credit_note,online,bank_transfer,cheque

**Response**: Created booking with relationships

### 6. View Booking
```
GET /bookings/{id}
```
**Response**: Complete booking details with hoarding, customer, vendor, approver relationships

### 7. Update Booking
```
PUT /bookings/{id}
```
**Request Body**: Same as create (all fields optional)  
**Note**: Cannot update cancelled bookings

### 8. Mark Cash Collected
```
POST /bookings/{id}/mark-cash-collected
```
**Request Body**:
```json
{
  "amount": 10620.00,
  "reference": "CASH001"
}
```

### 9. Convert to Credit Note
```
POST /bookings/{id}/convert-to-credit-note
```
**Request Body**:
```json
{
  "validity_days": 45
}
```
**Response**: Booking with credit note number, dates, and status

### 10. Cancel Credit Note
```
POST /bookings/{id}/cancel-credit-note
```
**Request Body**:
```json
{
  "reason": "Customer requested cash payment instead"
}
```

### 11. Cancel Booking
```
POST /bookings/{id}/cancel
```
**Request Body**:
```json
{
  "reason": "Customer cancelled the order"
}
```

## Web Routes

### Vendor Panel URLs
- `/vendor/pos/dashboard` - POS Dashboard
- `/vendor/pos/create` - Create new POS booking
- `/vendor/pos/list` - List all POS bookings
- `/vendor/pos/bookings/{id}` - View booking details

## Service Layer

### `POSBookingService` Methods

#### `createBooking(array $data): POSBooking`
Creates a new POS booking with:
- Hoarding availability validation
- Automatic pricing calculation
- Auto-invoice generation (if enabled)
- Auto-approval (if enabled)
- Credit note setup (if payment_mode is credit_note)

#### `updateBooking(POSBooking $booking, array $data): POSBooking`
Updates an existing booking with validation

#### `markAsCashCollected(POSBooking $booking, float $amount, ?string $reference): POSBooking`
Records cash payment collection

#### `markAsCreditNote(POSBooking $booking, ?int $validityDays): POSBooking`
Converts booking to credit note with auto-generated number

#### `cancelCreditNote(POSBooking $booking, string $reason): POSBooking`
Cancels active credit note

#### `cancelBooking(POSBooking $booking, string $reason): POSBooking`
Cancels the booking

#### `validateHoardingAvailability(int $hoardingId, string $startDate, string $endDate, ?int $excludeBookingId): void`
Validates hoarding is available for dates (checks both regular and POS bookings)

#### `getVendorBookings(int $vendorId, array $filters): LengthAwarePaginator`
Gets paginated bookings with filters

#### `getVendorStatistics(int $vendorId): array`
Returns statistics array with totals and summaries

### Helper Methods
- `isAutoApprovalEnabled()`: Check auto-approval setting
- `isAutoInvoiceEnabled()`: Check auto-invoice setting
- `isCashPaymentAllowed()`: Check cash payment permission
- `isCreditNoteAllowed()`: Check credit note permission
- `getCreditNoteDays()`: Get credit note validity days
- `getGSTRate()`: Get current GST rate
- `isSMSNotificationEnabled()`: Check SMS notification setting
- `isWhatsAppNotificationEnabled()`: Check WhatsApp setting
- `isEmailNotificationEnabled()`: Check email setting

## Model Features

### `POSBooking` Model

#### Relationships
- `vendor()`: BelongsTo User (vendor who created booking)
- `customer()`: BelongsTo User (customer if registered)
- `hoarding()`: BelongsTo Hoarding
- `approver()`: BelongsTo User (admin who approved)

#### Status Check Methods
- `isConfirmed()`: Check if status is confirmed
- `isActive()`: Check if status is active
- `isCancelled()`: Check if cancelled
- `isPaymentComplete()`: Check if fully paid
- `isCreditNote()`: Check if payment via credit note
- `isCreditNoteActive()`: Check if credit note is active
- `getBalanceAmount()`: Calculate pending payment
- `hasInvoice()`: Check if invoice generated

#### Static Methods
- `generateInvoiceNumber()`: Generate unique invoice number (POS-INV-{date}-{random})
- `generateCreditNoteNumber()`: Generate unique credit note number (CN-{date}-{random})

#### Query Scopes
- `forVendor($vendorId)`: Filter by vendor
- `byStatus($status)`: Filter by booking status
- `byPaymentStatus($paymentStatus)`: Filter by payment status
- `active()`: Get active bookings (confirmed + active status)
- `unpaid()`: Get unpaid/partial bookings
- `creditNotes()`: Get active credit notes

## Blade Views

### Dashboard (`dashboard.blade.php`)
- Statistics cards (total bookings, revenue, pending payments, credit notes)
- Recent bookings table
- Quick action buttons
- AJAX data loading

### Create Booking (`create.blade.php`)
- Customer details form (name, phone, email, address, GSTIN)
- Booking details (type, hoarding selection, dates)
- Pricing calculator (base amount, discount, auto-calculate GST)
- Payment mode selection
- Real-time price breakdown display
- Hoarding search integration

### Bookings List (`list.blade.php`)
- Filter by status, payment status
- Search functionality
- Paginated table
- Action buttons (view, edit)
- AJAX-based loading

## Business Logic

### Pricing Calculation
```
Base Amount: 10,000
Discount: 1,000
-------------------
After Discount: 9,000
GST (18%): 1,620
-------------------
Total Amount: 10,620
```

### Duration Calculation
```php
$start = '2025-12-10';
$end = '2025-12-20';
$durationDays = 11; // Includes both start and end dates
```

### Availability Validation
Checks for conflicts in:
1. Regular `bookings` table (status: confirmed, payment_hold)
2. `pos_bookings` table (status: confirmed, active)

Conflict detection:
- Start date falls within existing booking range
- End date falls within existing booking range
- New booking completely covers existing booking

### Auto-Approval Flow
If `pos_auto_approval` is enabled:
```
1. Booking created with status = 'confirmed'
2. auto_approved = true
3. approved_at = now()
4. approved_by = current vendor
5. confirmed_at = now()
```

### Auto-Invoice Flow
If `pos_auto_invoice` is enabled:
```
1. Generate unique invoice number: POS-INV-20251208-A1B2C3
2. Set invoice_date = now()
3. Invoice path can be generated later by invoice service
```

### Credit Note Flow
```
1. Generate unique credit note number: CN-20251208-X1Y2Z3
2. credit_note_date = now()
3. credit_note_due_date = now() + credit_note_days (default 30)
4. credit_note_status = 'active'
5. payment_status = 'credit'
```

## Future Enhancements

### 1. Invoice Generation
- PDF invoice with GST details
- Company letterhead
- Tax invoice format
- Download/email invoice

### 2. Notifications
- SMS to customer on booking confirmation
- WhatsApp message with booking details
- Email with invoice attachment
- Payment reminders for unpaid bookings
- Credit note expiry alerts

### 3. Credit Note Management
- PDF credit note generation
- Credit note settlement tracking
- Apply credit note to future bookings
- Credit note ledger

### 4. Payment Receipts
- Generate payment receipt PDF
- Track partial payments
- Payment history log

### 5. Reporting
- Daily sales report
- Payment collection report
- Outstanding payments report
- Credit note aging report
- Vendor-wise booking statistics

### 6. Integration
- Accounting software integration
- Tax filing integration
- Bank reconciliation
- POD (Proof of Display) integration for POS bookings

## Security Considerations

1. **Authorization**: All API endpoints protected by `auth:sanctum` and `role:vendor`
2. **Vendor Isolation**: Vendors can only see/edit their own bookings
3. **Data Validation**: Comprehensive validation on all inputs
4. **Soft Deletes**: Bookings are soft-deleted for audit trail
5. **Booking Snapshot**: JSON snapshot preserves original data
6. **Payment Tracking**: Separate payment status from booking status
7. **Credit Note Security**: Cannot cancel settled credit notes

## Testing Checklist

- [ ] Create booking with cash payment
- [ ] Create booking with credit note
- [ ] Convert existing booking to credit note
- [ ] Cancel credit note
- [ ] Mark cash as collected
- [ ] Update booking details
- [ ] Cancel booking
- [ ] Search hoardings with availability
- [ ] Calculate price with different GST rates
- [ ] Filter bookings by status
- [ ] Filter bookings by payment status
- [ ] Search bookings by customer name/phone
- [ ] Test hoarding availability validation
- [ ] Test auto-approval setting
- [ ] Test auto-invoice setting
- [ ] Verify invoice number generation
- [ ] Verify credit note number generation
- [ ] Test pagination
- [ ] Test vendor isolation (cannot see other vendor's bookings)
- [ ] Test guest customer booking
- [ ] Test registered customer booking

## Installation Steps

1. **Run Migration**:
   ```bash
   php artisan migrate --path=database/migrations/2025_12_08_073342_create_pos_bookings_table.php
   ```

2. **Seed Settings**:
   ```bash
   php artisan db:seed --class=POSSettingsSeeder
   ```

3. **Clear Caches**:
   ```bash
   php artisan route:clear
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Access POS Panel**:
   - Login as vendor
   - Navigate to `/vendor/pos/dashboard`
   - Create your first POS booking!

## Support & Troubleshooting

### Common Issues

**Issue**: Hoarding not available  
**Solution**: Check conflicting bookings in both `bookings` and `pos_bookings` tables

**Issue**: GST calculation incorrect  
**Solution**: Verify `pos_gst_rate` setting in settings table

**Issue**: Cannot create booking  
**Solution**: Ensure vendor has `role:vendor` and is authenticated

**Issue**: Credit note not generated  
**Solution**: Check `pos_allow_credit_note` setting is enabled

## Conclusion

The Vendor POS Booking Module provides a complete offline booking solution with:
- ✅ Cash and credit note support
- ✅ Real-time inventory management
- ✅ Automatic pricing with GST
- ✅ Admin-configurable settings
- ✅ Comprehensive API
- ✅ User-friendly interface
- ✅ Robust validation and security

This module empowers vendors to manage walk-in customers and offline deals efficiently while maintaining accurate inventory and financial records.

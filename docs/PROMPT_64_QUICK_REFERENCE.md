# PROMPT 64 - GST Invoice System Quick Reference

## 🚀 Setup Commands

```bash
# 1. Run migrations
php artisan migrate

# 2. Seed invoice settings
php artisan db:seed --class=InvoiceSettingsSeeder

# 3. Install dependencies (if needed)
composer require barryvdh/laravel-dompdf
composer require simplesoftwareio/simple-qrcode
```

## 📋 Invoice Number Format

```
INV/2024-25/000001
│   │       └─ Sequential number (resets April 1)
│   └─ Financial Year (April 1 - March 31)
└─ Prefix (configurable)
```

## 🔧 Quick Usage

### Generate Invoice
```php
use App\Services\InvoiceService;

$invoiceService = app(InvoiceService::class);
$invoice = $invoiceService->generateInvoiceForBooking($booking);
```

### Check Invoice Status
```php
$invoice->isPaid()        // true/false
$invoice->isOverdue()     // true/false
$invoice->getBalanceDue() // 5000.00
```

### Financial Year
```php
use App\Models\InvoiceSequence;

InvoiceSequence::getCurrentFinancialYear()    // "2024-25"
InvoiceSequence::getNextInvoiceNumber('INV') // "INV/2024-25/000001"
```

## 🎨 GST Calculation

### Intra-State (Same State)
```
Maharashtra → Maharashtra
CGST: 9% + SGST: 9% = 18%
```

### Inter-State (Different States)
```
Maharashtra → Delhi
IGST: 18%
```

### Reverse Charge
```php
// B2B with GSTIN = Reverse Charge
if ($customer->customer_type === 'business' && $customer->gstin) {
    $invoice->is_reverse_charge = true;
}
```

## 📊 Database Tables

- **invoices** - Main invoice records (40+ columns)
- **invoice_items** - Line items with tax breakdown
- **invoice_sequences** - Financial year tracking
- **users** - Added: gstin, company_name, pan, billing_*

## 🌐 Routes

### Customer
```
GET  /my/invoices                      - List
GET  /my/invoices/{invoice}            - View
GET  /my/invoices/{invoice}/download   - PDF
POST /my/invoices/{invoice}/email      - Send
```

### Admin
```
GET  /admin/invoices                   - List all
POST /admin/invoices/{invoice}/cancel  - Cancel
POST /admin/invoices/{invoice}/mark-paid - Mark paid
```

## ⚙️ Settings (Admin Panel)

Key settings in `invoice` group:
- `company_gstin` - Company GST number
- `company_state_code` - State code (2 digits)
- `hsn_advertising_services` - HSN/SAC code (998599)
- `gst_rate` - Tax rate (18.00)
- `invoice_auto_send_email` - Auto-send email (true)

## 🔍 Model Scopes

```php
Invoice::byFinancialYear('2024-25')->get()
Invoice::paid()->get()
Invoice::unpaid()->get()
Invoice::overdue()->get()
Invoice::forCustomer($userId)->get()
```

## 📧 Email Integration

```php
$invoiceService->sendInvoiceEmail($invoice);

// Auto-send on generation (check setting)
Setting::getValue('invoice_auto_send_email', true)
```

## 🧪 Test Invoice

```php
// Generate test invoice
$booking = Booking::first();
$invoice = app(InvoiceService::class)->generateInvoiceForBooking($booking);

// Verify
echo $invoice->invoice_number;           // INV/2024-25/000001
echo $invoice->getFormattedGrandTotal(); // ₹12,345.67
echo $invoice->hasPDF() ? 'Yes' : 'No';  // Yes
```

## ✅ Checklist

- [ ] Migrations run successfully
- [ ] Invoice settings seeded
- [ ] Company GSTIN updated
- [ ] Test invoice generated
- [ ] PDF downloads correctly
- [ ] Invoice number increments
- [ ] GST calculated correctly (CGST+SGST or IGST)
- [ ] Auto-generation works on payment capture

## 📝 HSN/SAC Codes

- **998599** - Outdoor advertising services
- **998914** - Printing services  
- **995415** - Mounting/installation services

## 🐛 Troubleshooting

**Invoice not generating?**
- Check CommissionService integration
- Verify invoice_auto_send_email setting
- Check logs: `storage/logs/laravel.log`

**PDF not showing?**
- Ensure DomPDF installed: `composer require barryvdh/laravel-dompdf`
- Check storage permissions: `storage/app/public/invoices/`
- Run: `php artisan storage:link`

**Sequence not incrementing?**
- Check `invoice_sequences` table
- Verify financial year is active
- Run: `InvoiceSequence::getCurrentSequence()`

## 💡 Pro Tips

1. **Financial Year Rollover**: Happens automatically on April 1
2. **State Code**: First 2 digits of GSTIN (e.g., 27 = Maharashtra)
3. **Round Off**: Grand total is rounded, difference stored in `round_off`
4. **Soft Deletes**: Cancelled invoices are soft-deleted (recoverable)
5. **Audit Trail**: All actions tracked via `created_by`, `cancelled_by`

## 📚 Full Documentation

See `docs/PROMPT_64_INVOICE_SYSTEM_COMPLETE.md` for complete reference.

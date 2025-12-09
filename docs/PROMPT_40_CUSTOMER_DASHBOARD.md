# PROMPT 40: Customer Dashboard + Reports

## Table of Contents
1. [Overview](#overview)
2. [Features](#features)
3. [Architecture](#architecture)
4. [Database Schema](#database-schema)
5. [Core Components](#core-components)
6. [Dashboard Pages](#dashboard-pages)
7. [Filtering System](#filtering-system)
8. [Export Functionality](#export-functionality)
9. [API Reference](#api-reference)
10. [Usage Examples](#usage-examples)
11. [Troubleshooting](#troubleshooting)

---

## Overview

The **Customer Dashboard + Reports** system provides a comprehensive, customer-facing interface for managing and tracking all aspects of their OOH advertising activities. It includes real-time statistics, interactive charts, advanced filtering, and export capabilities for bookings, payments, enquiries, offers, quotations, invoices, and communication threads.

### What Problems Does It Solve?

1. **Centralized View**: All customer data in one unified dashboard
2. **Self-Service**: Customers can track everything without contacting support
3. **Transparency**: Complete visibility into bookings, payments, and transactions
4. **Reporting**: Export capabilities for offline analysis and record-keeping
5. **Performance Tracking**: Visual charts showing booking trends and spending patterns
6. **Easy Navigation**: Filter and search across all entities quickly
7. **Mobile Friendly**: Responsive design works on all devices

### Key Features

- ✅ Real-time dashboard statistics with auto-refresh
- ✅ Interactive charts (booking trends, spending summary)
- ✅ Advanced filtering (date range, status, search)
- ✅ Export functionality (PDF, CSV formats)
- ✅ Pagination for large datasets
- ✅ Sortable tables
- ✅ Responsive Bootstrap 5 design
- ✅ Color-coded status badges
- ✅ Recent activity timeline
- ✅ Upcoming bookings widget
- ✅ Pending payments alert

---

## Features

### 1. Main Dashboard

**Statistics Overview**:
- Total bookings (with active, completed, cancelled breakdown)
- Total paid amount (with pending breakdown)
- Enquiry statistics (total, pending, responded)
- Invoice statistics (total, paid, unpaid)
- Offers, quotations, and thread counts

**Visual Components**:
- Progress bars showing completion rates
- Booking trends line chart (monthly data)
- Spending summary doughnut chart (by hoarding type)
- Recent activity feed (last 10 activities)
- Upcoming bookings widget (next 5 bookings)
- Pending payments alert

**Refresh Capability**:
- Statistics cached for 1 hour
- Manual refresh button available
- Auto-recalculation when cache expires

### 2. My Bookings

**Features**:
- Complete booking list with hoarding details
- Period display (start - end dates)
- Amount and payment status
- Status badges (pending, confirmed, active, completed, cancelled)
- Quick actions (view, pay, cancel)

**Filters**:
- Search by booking number or hoarding name
- Filter by booking status
- Filter by payment status
- Date range filter (from/to)
- Sort by booking number or created date

**Export**:
- PDF export with professional formatting
- CSV export for spreadsheet analysis
- Exports respect active filters

### 3. My Payments

**Features**:
- Transaction history with IDs
- Booking number reference
- Payment method display
- Status tracking (pending, completed, failed, refunded)
- Amount breakdown

**Filters**:
- Search by transaction ID or booking number
- Filter by payment status
- Filter by payment method (Razorpay, bank transfer, cash)
- Date range filter

**Export**:
- PDF report with transaction details
- CSV export for accounting
- Summary totals included

### 4. My Enquiries

**Features**:
- Enquiry list with hoarding and vendor info
- Status tracking (pending, responded, converted)
- Date submitted
- Quick view action

**Filters**:
- Search by enquiry ID or hoarding
- Filter by status
- Date range filter

### 5. My Offers

**Features**:
- Grid layout for visual appeal
- Offer code display
- Discount percentage/amount
- Validity period
- Status badges (active, used, expired)
- Apply offer button for active offers

**Filters**:
- Search by offer code
- Filter by status
- Date range filter

### 6. My Quotations

**Features**:
- Quotation number display
- Total amount
- Valid until date
- Status tracking (pending, approved, rejected)
- Accept quotation action

**Filters**:
- Search by quotation number
- Filter by status
- Date range filter

### 7. My Invoices

**Features**:
- Invoice and booking number
- Invoice date and due date
- Payment status
- Total amount
- Actions (view, download PDF, pay)

**Filters**:
- Search by invoice or booking number
- Filter by payment status
- Date range filter

**Export**:
- PDF invoice report
- CSV export with all invoices
- Outstanding amount calculation

### 8. My Threads

**Features**:
- Message thread list
- Subject display
- Last message preview
- Unread indicators
- Status badges (active, closed)
- Time ago display

**Filters**:
- Search by subject
- Filter by status
- Unread only checkbox
- Date range filter

---

## Architecture

### System Flow

```
Customer Browser
    ↓
Customer Dashboard Controller
    ↓
Customer Dashboard Service ← Statistics Calculation
    ↓
Customer Dashboard Stat Model (Cached Stats)
    ↓
Database (Bookings, Payments, etc.)
```

### Components

1. **Model**: `CustomerDashboardStat` - Stores cached statistics
2. **Service**: `CustomerDashboardService` - Calculates statistics and provides data
3. **Controller**: `CustomerDashboardController` - Handles HTTP requests
4. **Views**: Blade templates for each dashboard section
5. **Routes**: 15 routes under `/customer/my` prefix

---

## Database Schema

### `customer_dashboard_stats` Table

```sql
CREATE TABLE customer_dashboard_stats (
    id BIGINT UNSIGNED PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE,
    
    -- Booking Stats
    total_bookings INT DEFAULT 0,
    active_bookings INT DEFAULT 0,
    completed_bookings INT DEFAULT 0,
    cancelled_bookings INT DEFAULT 0,
    total_booking_amount DECIMAL(12,2) DEFAULT 0,
    
    -- Payment Stats
    total_payments INT DEFAULT 0,
    total_paid DECIMAL(12,2) DEFAULT 0,
    total_pending DECIMAL(12,2) DEFAULT 0,
    total_refunded DECIMAL(12,2) DEFAULT 0,
    
    -- Enquiry Stats
    total_enquiries INT DEFAULT 0,
    pending_enquiries INT DEFAULT 0,
    responded_enquiries INT DEFAULT 0,
    
    -- Offer Stats
    total_offers INT DEFAULT 0,
    active_offers INT DEFAULT 0,
    accepted_offers INT DEFAULT 0,
    
    -- Quotation Stats
    total_quotations INT DEFAULT 0,
    pending_quotations INT DEFAULT 0,
    approved_quotations INT DEFAULT 0,
    
    -- Invoice Stats
    total_invoices INT DEFAULT 0,
    paid_invoices INT DEFAULT 0,
    unpaid_invoices INT DEFAULT 0,
    total_invoice_amount DECIMAL(12,2) DEFAULT 0,
    
    -- Thread Stats
    total_threads INT DEFAULT 0,
    unread_threads INT DEFAULT 0,
    
    -- Cache Control
    last_calculated_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Key Fields Explained

| Field | Purpose | Example |
|-------|---------|---------|
| `total_bookings` | Count of all bookings | 15 |
| `active_bookings` | Currently active bookings | 3 |
| `total_booking_amount` | Sum of all booking amounts | 125000.00 |
| `total_paid` | Total amount paid by customer | 80000.00 |
| `total_pending` | Outstanding payment amount | 45000.00 |
| `pending_enquiries` | Enquiries awaiting response | 2 |
| `last_calculated_at` | When stats were last updated | 2025-12-09 10:30:00 |

---

## Core Components

### 1. CustomerDashboardStat Model

**Purpose**: Represents cached customer statistics

**Key Methods**:

```php
// Check if recalculation needed (> 1 hour old)
$stats->needsRecalculation(); // Returns bool

// Computed attributes
$stats->booking_completion_rate;    // Percentage of completed bookings
$stats->payment_completion_rate;    // Percentage of paid amount
$stats->enquiry_response_rate;      // Percentage of responded enquiries
$stats->offer_acceptance_rate;      // Percentage of accepted offers
$stats->quotation_approval_rate;    // Percentage of approved quotations
$stats->invoice_payment_rate;       // Percentage of paid invoices
$stats->average_booking_amount;     // Average booking value
```

**Example**:
```php
use App\Models\CustomerDashboardStat;

$stats = CustomerDashboardStat::where('user_id', auth()->id())->first();

echo "Booking completion rate: {$stats->booking_completion_rate}%";
echo "Total spent: ₹{$stats->total_paid}";
echo "Average booking: ₹{$stats->average_booking_amount}";
```

### 2. CustomerDashboardService

**Purpose**: Calculate statistics and provide dashboard data

**Key Methods**:

#### getStats(User $customer, bool $forceRecalculate = false)
Get statistics, calculating if needed
```php
$service = app(CustomerDashboardService::class);
$stats = $service->getStats(auth()->user());
```

#### calculateStats(User $customer, CustomerDashboardStat $stats)
Recalculate all statistics
```php
$service->calculateStats($customer, $stats);
```

#### getRecentActivities(User $customer, int $limit = 10)
Get timeline of recent actions
```php
$activities = $service->getRecentActivities($customer, 10);

foreach ($activities as $activity) {
    echo "{$activity['type']}: {$activity['title']} - {$activity['status']}\n";
}
```

#### getUpcomingBookings(User $customer, int $limit = 5)
Get next scheduled bookings
```php
$upcoming = $service->getUpcomingBookings($customer, 5);
```

#### getPendingPayments(User $customer)
Get outstanding payments
```php
$pending = $service->getPendingPayments($customer);
$totalPending = $pending->sum('amount');
```

#### getBookingChartData(User $customer, string $period = 'monthly')
Get chart data for bookings over time
```php
$chartData = $service->getBookingChartData($customer, 'monthly');

// Returns:
[
    'labels' => ['2025-01', '2025-02', '2025-03', ...],
    'bookings' => [5, 8, 12, ...],
    'amounts' => [25000, 40000, 60000, ...]
]
```

#### getSpendingSummary(User $customer)
Get spending breakdown by hoarding type
```php
$summary = $service->getSpendingSummary($customer);

// Returns:
[
    'categories' => ['Billboard', 'Bus Shelter', 'LED Screen'],
    'spending' => [50000, 30000, 45000],
    'counts' => [10, 5, 8]
]
```

### 3. CustomerDashboardController

**Purpose**: Handle HTTP requests for dashboard pages

**Key Methods**:

#### index()
Main dashboard with charts and statistics
```php
Route: GET /customer/my/dashboard
View: customer.dashboard.index
```

#### myBookings(Request $request)
Bookings list with filters
```php
Route: GET /customer/my/bookings
Parameters: search, status, payment_status, date_from, date_to, sort_by, sort_order
View: customer.dashboard.bookings
```

#### myPayments(Request $request)
Payments list with filters
```php
Route: GET /customer/my/payments
Parameters: search, status, payment_method, date_from, date_to
View: customer.dashboard.payments
```

#### exportBookings(Request $request, string $format)
Export bookings as PDF/CSV
```php
Route: GET /customer/my/bookings/export/{format}
Formats: pdf, csv, excel
Returns: Download file
```

#### refreshStats()
Manually refresh dashboard statistics
```php
Route: POST /customer/my/refresh-stats
Redirects: Back to previous page
```

---

## Dashboard Pages

### Main Dashboard (/customer/my/dashboard)

**Sections**:
1. Statistics Cards (4 primary metrics)
2. Secondary Stats Row (offers, quotations, threads)
3. Booking Trends Chart (line chart)
4. Spending Summary Chart (doughnut chart)
5. Upcoming Bookings Widget
6. Recent Activity Feed
7. Pending Payments Alert

**Code Example**:
```blade
@extends('layouts.customer')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <h3>{{ $stats->total_bookings }}</h3>
                <p>Total Bookings</p>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $stats->booking_completion_rate }}%"></div>
                </div>
            </div>
        </div>
        <!-- More cards... -->
    </div>
    
    <!-- Charts -->
    <canvas id="bookingChart"></canvas>
</div>
@endsection
```

### Bookings Page (/customer/my/bookings)

**Features**:
- Summary cards (total, amount, paid, pending)
- Advanced filters
- Sortable table
- Export buttons
- Pagination

**Filter Example**:
```html
<form method="GET">
    <input type="text" name="search" placeholder="Search...">
    <select name="status">
        <option value="">All Statuses</option>
        <option value="confirmed">Confirmed</option>
        <option value="active">Active</option>
    </select>
    <input type="date" name="date_from">
    <input type="date" name="date_to">
    <button type="submit">Filter</button>
</form>
```

---

## Filtering System

### How Filters Work

1. **User submits filter form**
2. **Query parameters added to URL**
3. **Controller applies filters to query**
4. **Results paginated and returned**
5. **Filters preserved in pagination links**

### Common Filters

#### Text Search
```php
if ($request->filled('search')) {
    $query->where(function($q) use ($request) {
        $q->where('booking_number', 'like', "%{$request->search}%")
          ->orWhereHas('hoarding', function($hq) use ($request) {
              $hq->where('title', 'like', "%{$request->search}%");
          });
    });
}
```

#### Status Filter
```php
if ($request->filled('status')) {
    $query->where('status', $request->status);
}
```

#### Date Range Filter
```php
if ($request->filled('date_from')) {
    $query->where('start_date', '>=', $request->date_from);
}

if ($request->filled('date_to')) {
    $query->where('end_date', '<=', $request->date_to);
}
```

#### Sorting
```php
$sortBy = $request->get('sort_by', 'created_at');
$sortOrder = $request->get('sort_order', 'desc');
$query->orderBy($sortBy, $sortOrder);
```

### Filter Persistence

Filters are preserved across pages using query strings:
```php
$bookings = $query->paginate(15)->withQueryString();
```

### Clear Filters

```blade
@if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
<a href="{{ route('customer.my.bookings') }}" class="btn btn-secondary">
    Clear Filters
</a>
@endif
```

---

## Export Functionality

### PDF Export

**Uses**: DomPDF library (`barryvdh/laravel-dompdf`)

**Implementation**:
```php
use Barryvdh\DomPDF\Facade\Pdf;

public function exportBookingsPDF($bookings, $customer)
{
    $pdf = PDF::loadView('customer.dashboard.exports.bookings-pdf', compact('bookings', 'customer'));
    return $pdf->download('my-bookings-' . now()->format('Y-m-d') . '.pdf');
}
```

**PDF Template Example**:
```blade
<!DOCTYPE html>
<html>
<head>
    <title>My Bookings Report</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
    <h1>My Bookings Report</h1>
    <p>Customer: {{ $customer->name }}</p>
    
    <table>
        <thead>
            <tr>
                <th>Booking #</th>
                <th>Hoarding</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr>
                <td>{{ $booking->booking_number }}</td>
                <td>{{ $booking->hoarding->title }}</td>
                <td>₹{{ number_format($booking->total_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
```

### CSV Export

**Implementation**:
```php
protected function exportBookingsCSV($bookings, $customer)
{
    $filename = 'my-bookings-' . now()->format('Y-m-d') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    $callback = function() use ($bookings) {
        $file = fopen('php://output', 'w');
        
        // Headers
        fputcsv($file, ['Booking Number', 'Hoarding', 'Start Date', 'End Date', 'Amount', 'Status']);

        // Data
        foreach ($bookings as $booking) {
            fputcsv($file, [
                $booking->booking_number,
                $booking->hoarding->title,
                $booking->start_date,
                $booking->end_date,
                $booking->total_amount,
                $booking->status,
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
```

### Export Routes

```php
// PDF Export
GET /customer/my/bookings/export/pdf

// CSV Export
GET /customer/my/bookings/export/csv

// With filters
GET /customer/my/bookings/export/pdf?status=confirmed&date_from=2025-01-01
```

### Export Button
```blade
<div class="btn-group">
    <a href="{{ route('customer.my.bookings.export', 'pdf') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
       class="btn btn-outline-danger">
        <i class="bi bi-file-pdf"></i> PDF
    </a>
    <a href="{{ route('customer.my.bookings.export', 'csv') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
       class="btn btn-outline-success">
        <i class="bi bi-file-excel"></i> CSV
    </a>
</div>
```

---

## API Reference

### Routes

```php
// Main Dashboard
GET    /customer/my/dashboard                      → index()

// Bookings
GET    /customer/my/bookings                       → myBookings()
GET    /customer/my/bookings/export/{format}       → exportBookings()

// Payments
GET    /customer/my/payments                       → myPayments()
GET    /customer/my/payments/export/{format}       → exportPayments()

// Enquiries
GET    /customer/my/enquiries                      → myEnquiries()

// Offers
GET    /customer/my/offers                         → myOffers()

// Quotations
GET    /customer/my/quotations                     → myQuotations()

// Invoices
GET    /customer/my/invoices                       → myInvoices()
GET    /customer/my/invoices/export/{format}       → exportInvoices()

// Threads
GET    /customer/my/threads                        → myThreads()

// Statistics
POST   /customer/my/refresh-stats                  → refreshStats()
```

### Query Parameters

#### Common Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `search` | string | Search term | `?search=BKG-001` |
| `status` | string | Filter by status | `?status=confirmed` |
| `payment_status` | string | Filter by payment status | `?payment_status=paid` |
| `date_from` | date | Start date | `?date_from=2025-01-01` |
| `date_to` | date | End date | `?date_to=2025-12-31` |
| `sort_by` | string | Sort column | `?sort_by=created_at` |
| `sort_order` | string | Sort direction | `?sort_order=desc` |
| `page` | integer | Page number | `?page=2` |

#### Examples

**Filter bookings by status and date**:
```
GET /customer/my/bookings?status=confirmed&date_from=2025-01-01&date_to=2025-12-31
```

**Search payments by transaction ID**:
```
GET /customer/my/payments?search=TXN123456
```

**Export filtered invoices as PDF**:
```
GET /customer/my/invoices/export/pdf?payment_status=unpaid
```

---

## Usage Examples

### Example 1: Access Dashboard

```php
// User navigates to dashboard
Route: /customer/my/dashboard

// Controller
public function index()
{
    $customer = auth()->user();
    $stats = $this->dashboardService->getStats($customer);
    $recentActivities = $this->dashboardService->getRecentActivities($customer);
    $upcomingBookings = $this->dashboardService->getUpcomingBookings($customer);
    
    return view('customer.dashboard.index', compact(
        'stats',
        'recentActivities',
        'upcomingBookings'
    ));
}
```

### Example 2: Filter Bookings

```php
// User filters bookings by status and date
Route: /customer/my/bookings?status=confirmed&date_from=2025-01-01

// Controller applies filters
$query = Booking::where('customer_id', $customer->id);

if ($request->filled('status')) {
    $query->where('status', $request->status);
}

if ($request->filled('date_from')) {
    $query->where('start_date', '>=', $request->date_from);
}

$bookings = $query->paginate(15)->withQueryString();
```

### Example 3: Export Report

```php
// User clicks PDF export button
Route: /customer/my/bookings/export/pdf?status=confirmed

// Controller
public function exportBookings(Request $request, string $format)
{
    $customer = auth()->user();
    $bookings = Booking::where('customer_id', $customer->id);
    
    // Apply filters (same as list view)
    if ($request->filled('status')) {
        $bookings->where('status', $request->status);
    }
    
    $bookings = $bookings->get();
    
    if ($format === 'pdf') {
        $pdf = PDF::loadView('exports.bookings-pdf', compact('bookings', 'customer'));
        return $pdf->download('bookings-' . now()->format('Y-m-d') . '.pdf');
    }
}
```

### Example 4: Refresh Statistics

```php
// User clicks "Refresh Stats" button
Route: POST /customer/my/refresh-stats

// Controller
public function refreshStats()
{
    $customer = auth()->user();
    $this->dashboardService->getStats($customer, true); // Force recalculation
    
    return redirect()->back()->with('success', 'Statistics refreshed!');
}
```

### Example 5: View Chart Data

```blade
{{-- In view --}}
<canvas id="bookingChart"></canvas>

<script>
const ctx = document.getElementById('bookingChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($bookingChart['labels']),
        datasets: [{
            label: 'Bookings',
            data: @json($bookingChart['bookings']),
            borderColor: '#0d6efd'
        }]
    }
});
</script>
```

---

## Troubleshooting

### Issue 1: Statistics Not Updating

**Problem**: Dashboard shows old statistics

**Solution**:
```php
// Click "Refresh Stats" button, or
// Manually trigger recalculation
$service = app(CustomerDashboardService::class);
$stats = $service->getStats(auth()->user(), true); // Force refresh
```

### Issue 2: Export Not Working

**Problem**: PDF export fails or shows blank page

**Debug**:
```php
// Check if DomPDF is installed
composer require barryvdh/laravel-dompdf

// Check view exists
resources/views/customer/dashboard/exports/bookings-pdf.blade.php

// Test view directly
return view('customer.dashboard.exports.bookings-pdf', compact('bookings', 'customer'));
```

### Issue 3: Filters Not Working

**Problem**: Filters don't apply or reset

**Check**:
```php
// Ensure withQueryString() is used
$bookings = $query->paginate(15)->withQueryString();

// Check form method is GET
<form method="GET" action="{{ route('customer.my.bookings') }}">
```

### Issue 4: Charts Not Displaying

**Problem**: Charts show blank or don't render

**Solution**:
```blade
{{-- Ensure Chart.js is loaded --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

{{-- Check data is valid JSON --}}
labels: @json($bookingChart['labels']),
data: @json($bookingChart['bookings'])
```

### Issue 5: Permission Denied

**Problem**: Customer can't access dashboard

**Check**:
```php
// Ensure middleware is applied
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::prefix('my')->name('my.')->group(function () {
        // Dashboard routes
    });
});

// Check user has customer role
$user->hasRole('customer');
```

---

## Summary

**PROMPT 40: Customer Dashboard + Reports** provides:

✅ **Complete Dashboard**: Statistics, charts, widgets  
✅ **7 Management Pages**: Bookings, payments, enquiries, offers, quotations, invoices, threads  
✅ **Advanced Filtering**: Search, status, date range, sorting  
✅ **Export Functionality**: PDF and CSV reports  
✅ **Real-Time Statistics**: Cached for performance, auto-refresh  
✅ **Visual Charts**: Booking trends and spending breakdown  
✅ **Responsive Design**: Works on all devices  
✅ **Self-Service**: Customers manage everything independently  

**Key Files**:
- `app/Models/CustomerDashboardStat.php` - Statistics model
- `app/Services/CustomerDashboardService.php` - Statistics service
- `app/Http/Controllers/Customer/CustomerDashboardController.php` - Controller
- `resources/views/customer/dashboard/*.blade.php` - Views

**Routes**: 15 routes under `/customer/my/*`

**Benefits**:
- Improved customer experience
- Reduced support workload
- Better transparency
- Self-service capabilities
- Performance tracking
- Export for records

For implementation details, see the source code and inline comments.

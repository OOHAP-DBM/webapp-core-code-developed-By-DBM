@extends('layouts.admin')

@section('title', 'Price Breakdown - Booking #' . $booking->id)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-receipt-cutoff text-primary"></i>
                        Price Breakdown
                    </h2>
                    <p class="text-muted mb-0">
                        Immutable price snapshot for Booking #{{ $booking->id }}
                    </p>
                </div>
                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Booking
                </a>
            </div>

            <!-- Booking Info Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <p class="text-muted small mb-1">Booking ID</p>
                            <p class="fw-semibold mb-0">#{{ $booking->id }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <p class="text-muted small mb-1">Status</p>
                            <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'cancelled' ? 'danger' : 'warning') }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </div>
                        <div class="col-md-3 mb-3">
                            <p class="text-muted small mb-1">Customer</p>
                            <p class="mb-0">{{ $priceSnapshot->quotation_metadata['customer_name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <p class="text-muted small mb-1">Vendor</p>
                            <p class="mb-0">{{ $priceSnapshot->quotation_metadata['vendor_name'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Price Summary Card -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-calculator"></i> Price Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Services Price -->
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div>
                                    <p class="mb-0 text-muted small">Services Price</p>
                                    <small class="text-muted">Base price for all services</small>
                                </div>
                                <p class="mb-0 fs-5 fw-semibold">
                                    {{ $priceSnapshot->currency }} {{ number_format($priceSnapshot->services_price, 2) }}
                                </p>
                            </div>

                            <!-- Discounts -->
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div>
                                    <p class="mb-0 text-muted small">Discounts</p>
                                    <small class="text-success">{{ $priceSnapshot->discount_percentage }}% off</small>
                                </div>
                                <p class="mb-0 fs-5 fw-semibold text-success">
                                    - {{ $priceSnapshot->currency }} {{ number_format($priceSnapshot->discounts, 2) }}
                                </p>
                            </div>

                            <!-- Effective Price -->
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div>
                                    <p class="mb-0 text-muted small">Effective Price</p>
                                    <small class="text-muted">After discounts</small>
                                </div>
                                <p class="mb-0 fs-5 fw-semibold">
                                    {{ $priceSnapshot->currency }} {{ number_format($priceSnapshot->effective_price, 2) }}
                                </p>
                            </div>

                            <!-- Taxes -->
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div>
                                    <p class="mb-0 text-muted small">Taxes (GST)</p>
                                    <small class="text-info">{{ $priceSnapshot->tax_percentage }}%</small>
                                </div>
                                <p class="mb-0 fs-5 fw-semibold text-info">
                                    + {{ $priceSnapshot->currency }} {{ number_format($priceSnapshot->taxes, 2) }}
                                </p>
                            </div>

                            <!-- Total Amount -->
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <p class="mb-0 fw-bold">Total Amount</p>
                                    <small class="text-muted">Final payable</small>
                                </div>
                                <p class="mb-0 fs-3 fw-bold text-primary">
                                    {{ $priceSnapshot->currency }} {{ number_format($priceSnapshot->total_amount, 2) }}
                                </p>
                            </div>

                            <!-- Snapshot Timestamp -->
                            <div class="mt-3 pt-3 border-top text-center">
                                <small class="text-muted">
                                    <i class="bi bi-clock-history"></i>
                                    Snapshot created: {{ $priceSnapshot->created_at->format('M d, Y h:i A') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line Items Card -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul"></i> Line Items Breakdown
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(count($priceSnapshot->line_items) > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Description</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-end">Unit Price</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($priceSnapshot->line_items as $index => $item)
                                                <tr>
                                                    <td class="text-muted">{{ $index + 1 }}</td>
                                                    <td>
                                                        <div>
                                                            <p class="mb-0 fw-semibold">{{ $item['description'] ?? $item['name'] ?? 'Service' }}</p>
                                                            @if(isset($item['notes']) && $item['notes'])
                                                                <small class="text-muted">{{ $item['notes'] }}</small>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-dark">
                                                            {{ $item['quantity'] ?? 1 }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        {{ $priceSnapshot->currency }} {{ number_format($item['unit_price'] ?? $item['price'] ?? 0, 2) }}
                                                    </td>
                                                    <td class="text-end fw-semibold">
                                                        {{ $priceSnapshot->currency }} {{ number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? $item['price'] ?? 0), 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">Subtotal</td>
                                                <td class="text-end fw-bold">
                                                    {{ $priceSnapshot->currency }} {{ number_format($priceSnapshot->services_price, 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    No line items found in the price snapshot.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quotation Metadata Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text"></i> Quotation Metadata
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <p class="text-muted small mb-1">Quotation ID</p>
                            <p class="mb-0">#{{ $priceSnapshot->quotation_metadata['quotation_id'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <p class="text-muted small mb-1">Quotation Version</p>
                            <span class="badge bg-info">v{{ $priceSnapshot->quotation_metadata['quotation_version'] ?? '1' }}</span>
                        </div>
                        <div class="col-md-3 mb-3">
                            <p class="text-muted small mb-1">Customer Email</p>
                            <p class="mb-0">{{ $priceSnapshot->quotation_snapshot['customer_email'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <p class="text-muted small mb-1">Vendor Email</p>
                            <p class="mb-0">{{ $priceSnapshot->quotation_snapshot['vendor_email'] ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <p class="text-muted small mb-1">Hoarding</p>
                            <p class="mb-0 fw-semibold">{{ $priceSnapshot->quotation_snapshot['hoarding_title'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <p class="text-muted small mb-1">Location</p>
                            <p class="mb-0">{{ $priceSnapshot->quotation_snapshot['hoarding_location'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <p class="text-muted small mb-1">Dimensions</p>
                            <p class="mb-0">{{ $priceSnapshot->quotation_snapshot['hoarding_dimensions'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warning Notice -->
            <div class="alert alert-warning d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>
                    <strong>Immutable Snapshot Notice:</strong>
                    This price breakdown is a point-in-time snapshot captured at booking creation. 
                    All calculations and billing must use these frozen values, not recalculated from current quotation data.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}
</style>
@endsection

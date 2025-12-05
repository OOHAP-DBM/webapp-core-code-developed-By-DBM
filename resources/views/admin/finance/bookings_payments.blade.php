@extends('layouts.admin')

@section('title', 'Booking Payments & Payouts')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-currency-rupee text-success"></i>
                Booking Payments & Vendor Payouts
            </h2>
            <p class="text-muted mb-0">
                Manage commission tracking and vendor payout processing
            </p>
        </div>
        <div>
            <button class="btn btn-primary" id="exportBtn">
                <i class="bi bi-download"></i> Export Report
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 small">Total Revenue</p>
                            <h3 class="mb-0">₹{{ number_format($stats['total_gross'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 small">Platform Commission</p>
                            <h3 class="mb-0">₹{{ number_format($stats['total_commission'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="bi bi-bank fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 small">Pending Payouts</p>
                            <h3 class="mb-0">₹{{ number_format($stats['pending_payout'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 small">Completed Payouts</p>
                            <h3 class="mb-0">₹{{ number_format($stats['completed_payout'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.finance.bookings-payments') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Payout Status</label>
                    <select name="payout_status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('payout_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('payout_status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ request('payout_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="on_hold" {{ request('payout_status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('admin.finance.bookings-payments') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Ledger -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="bi bi-table"></i> Booking Payments Ledger
            </h5>
        </div>
        <div class="card-body p-0">
            @if($bookingPayments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking ID</th>
                                <th>Vendor</th>
                                <th>Gross Amount</th>
                                <th>Commission</th>
                                <th>PG Fee</th>
                                <th>Vendor Payout</th>
                                <th>Payout Status</th>
                                <th>Payment Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookingPayments as $payment)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.bookings.show', $payment->booking_id) }}" class="fw-semibold text-decoration-none">
                                            #{{ $payment->booking_id }}
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <p class="mb-0 fw-semibold">{{ $payment->booking->vendor->name ?? 'N/A' }}</p>
                                            <small class="text-muted">{{ $payment->booking->vendor->email ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td class="fw-semibold">₹{{ number_format($payment->gross_amount, 2) }}</td>
                                    <td class="text-success">₹{{ number_format($payment->admin_commission_amount, 2) }}</td>
                                    <td class="text-danger">₹{{ number_format($payment->pg_fee_amount, 2) }}</td>
                                    <td class="fw-bold text-primary">₹{{ number_format($payment->vendor_payout_amount, 2) }}</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'completed' => 'success',
                                                'failed' => 'danger',
                                                'on_hold' => 'secondary',
                                            ];
                                            $color = $statusColors[$payment->vendor_payout_status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">
                                            {{ ucfirst(str_replace('_', ' ', $payment->vendor_payout_status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($payment->paid_at)
                                            <small>{{ $payment->paid_at->format('M d, Y') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button 
                                                class="btn btn-outline-primary btn-sm view-details-btn" 
                                                data-payment-id="{{ $payment->id }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#paymentDetailsModal">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            
                                            @if($payment->vendor_payout_status === 'pending')
                                                <button 
                                                    class="btn btn-outline-success btn-sm mark-paid-btn" 
                                                    data-payment-id="{{ $payment->id }}"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#markPaidModal">
                                                    <i class="bi bi-check-circle"></i> Pay
                                                </button>
                                            @endif
                                            
                                            @if($payment->vendor_payout_status === 'pending')
                                                <button 
                                                    class="btn btn-outline-warning btn-sm hold-payout-btn" 
                                                    data-payment-id="{{ $payment->id }}">
                                                    <i class="bi bi-pause-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="fw-bold">Totals (Current Page)</td>
                                <td class="fw-bold">₹{{ number_format($bookingPayments->sum('gross_amount'), 2) }}</td>
                                <td class="fw-bold text-success">₹{{ number_format($bookingPayments->sum('admin_commission_amount'), 2) }}</td>
                                <td class="fw-bold text-danger">₹{{ number_format($bookingPayments->sum('pg_fee_amount'), 2) }}</td>
                                <td class="fw-bold text-primary">₹{{ number_format($bookingPayments->sum('vendor_payout_amount'), 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer bg-white">
                    {{ $bookingPayments->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No booking payments found</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-text"></i> Payment Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle text-success"></i> Mark Payout as Paid
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="markPaidForm">
                <div class="modal-body">
                    <input type="hidden" id="mark-paid-payment-id">
                    
                    <div class="mb-3">
                        <label class="form-label">Payout Mode <span class="text-danger">*</span></label>
                        <select class="form-select" id="payout-mode" required>
                            <option value="">Select Mode</option>
                            <option value="bank_transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                            <option value="razorpay_transfer">Razorpay Route Transfer</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                            <option value="manual">Manual/Cash</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reference Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="payout-reference" placeholder="UTR/Transaction ID" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="payout-notes" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                    
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle"></i>
                        This action will mark the vendor payout as completed. Ensure payment has been successfully transferred before confirming.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="confirmMarkPaidBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="mark-paid-spinner"></span>
                        Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // View Details
    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const paymentId = this.getAttribute('data-payment-id');
            
            fetch(`/api/v1/admin/booking-payments/${paymentId}`, {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const payment = data.data;
                    document.getElementById('paymentDetailsContent').innerHTML = `
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <p class="text-muted small mb-1">Booking ID</p>
                                <p class="fw-semibold">#${payment.booking_id}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="text-muted small mb-1">Razorpay Payment ID</p>
                                <p class="font-monospace small">${payment.razorpay_payment_id || 'N/A'}</p>
                            </div>
                            <div class="col-12"><hr></div>
                            <div class="col-md-6 mb-3">
                                <p class="text-muted small mb-1">Gross Amount</p>
                                <p class="fw-bold">₹${parseFloat(payment.gross_amount).toFixed(2)}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="text-muted small mb-1">Admin Commission (${payment.commission_percentage}%)</p>
                                <p class="fw-bold text-success">₹${parseFloat(payment.admin_commission_amount).toFixed(2)}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="text-muted small mb-1">PG Fee</p>
                                <p class="fw-bold text-danger">₹${parseFloat(payment.pg_fee_amount).toFixed(2)}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="text-muted small mb-1">Vendor Payout</p>
                                <p class="fw-bold text-primary">₹${parseFloat(payment.vendor_payout_amount).toFixed(2)}</p>
                            </div>
                            ${payment.payout_reference ? `
                            <div class="col-12"><hr></div>
                            <div class="col-md-6 mb-3">
                                <p class="text-muted small mb-1">Payout Mode</p>
                                <p>${payment.payout_mode || 'N/A'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="text-muted small mb-1">Payout Reference</p>
                                <p class="font-monospace">${payment.payout_reference}</p>
                            </div>
                            ` : ''}
                        </div>
                    `;
                }
            });
        });
    });
    
    // Mark Paid
    document.querySelectorAll('.mark-paid-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const paymentId = this.getAttribute('data-payment-id');
            document.getElementById('mark-paid-payment-id').value = paymentId;
        });
    });
    
    document.getElementById('markPaidForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const paymentId = document.getElementById('mark-paid-payment-id').value;
        const mode = document.getElementById('payout-mode').value;
        const reference = document.getElementById('payout-reference').value;
        const notes = document.getElementById('payout-notes').value;
        
        const spinner = document.getElementById('mark-paid-spinner');
        const btn = document.getElementById('confirmMarkPaidBtn');
        
        spinner.classList.remove('d-none');
        btn.disabled = true;
        
        fetch(`/api/v1/admin/booking-payments/${paymentId}/mark-paid`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
            },
            body: JSON.stringify({
                payout_mode: mode,
                payout_reference: reference,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            spinner.classList.add('d-none');
            btn.disabled = false;
            
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('markPaidModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to mark payout as paid'));
            }
        });
    });
    
    // Hold Payout
    document.querySelectorAll('.hold-payout-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const paymentId = this.getAttribute('data-payment-id');
            const reason = prompt('Enter reason for holding payout:');
            
            if (reason) {
                fetch(`/api/v1/admin/booking-payments/${paymentId}/hold`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
                    },
                    body: JSON.stringify({ reason })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to hold payout'));
                    }
                });
            }
        });
    });
});
</script>

<style>
.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
    cursor: pointer;
}

.card {
    transition: all 0.3s ease;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
@endsection

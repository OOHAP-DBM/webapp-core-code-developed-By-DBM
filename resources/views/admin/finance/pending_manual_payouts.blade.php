@extends('layouts.admin')

@section('title', 'Pending Manual Payouts')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Pending Manual Payouts</h2>
                    <p class="text-muted mb-0">Process payouts for vendors with incomplete KYC verification</p>
                </div>
                <div>
                    <a href="{{ url('/admin/finance/bookings-payments') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> All Payments
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning text-uppercase mb-1">Pending Manual</h6>
                            <h3 class="mb-0">{{ $stats['total_pending'] }}</h3>
                            <small class="text-muted">₹{{ number_format($stats['total_amount'], 2) }}</small>
                        </div>
                        <i class="fas fa-exclamation-circle fa-2x text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success text-uppercase mb-1">Auto Paid</h6>
                            <h3 class="mb-0">{{ $stats['auto_paid_count'] }}</h3>
                            <small class="text-muted">₹{{ number_format($stats['auto_paid_amount'], 2) }}</small>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-info text-uppercase mb-1">Avg. Payout</h6>
                            <h3 class="mb-0">₹{{ $stats['total_pending'] > 0 ? number_format($stats['total_amount'] / $stats['total_pending'], 2) : '0.00' }}</h3>
                        </div>
                        <i class="fas fa-rupee-sign fa-2x text-info opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-primary text-uppercase mb-1">Automation Rate</h6>
                            <h3 class="mb-0">{{ $stats['auto_paid_count'] + $stats['total_pending'] > 0 ? number_format(($stats['auto_paid_count'] / ($stats['auto_paid_count'] + $stats['total_pending'])) * 100, 1) : '0' }}%</h3>
                        </div>
                        <i class="fas fa-robot fa-2x text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ url('/admin/finance/pending-manual-payouts') }}" class="row g-3">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Search by booking reference, vendor name, or email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payouts List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Manual Payouts Queue ({{ $manualPayouts->total() }} pending)</h5>
        </div>
        <div class="card-body p-0">
            @if($manualPayouts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking Ref</th>
                                <th>Vendor</th>
                                <th>Payout Amount</th>
                                <th>KYC Status</th>
                                <th>Reason</th>
                                <th>Payment Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($manualPayouts as $payout)
                            <tr>
                                <td>
                                    <strong>{{ $payout->booking->booking_reference }}</strong><br>
                                    <small class="text-muted">ID: {{ $payout->booking_id }}</small>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $payout->booking->vendor->name }}</strong><br>
                                        <small class="text-muted">{{ $payout->booking->vendor->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-success">₹{{ number_format($payout->vendor_payout_amount, 2) }}</strong><br>
                                    <small class="text-muted">Gross: ₹{{ number_format($payout->gross_amount, 2) }}</small>
                                </td>
                                <td>
                                    @php
                                        $vendorKyc = $payout->booking->vendor->vendorKYC;
                                        $kycStatus = $vendorKyc ? $vendorKyc->verification_status : 'not_submitted';
                                        $payoutStatus = $vendorKyc ? $vendorKyc->payout_status : 'N/A';
                                    @endphp
                                    <span class="badge bg-{{ $kycStatus === 'approved' ? 'success' : 'warning' }}">
                                        KYC: {{ strtoupper(str_replace('_', ' ', $kycStatus)) }}
                                    </span><br>
                                    <span class="badge bg-{{ $payoutStatus === 'verified' ? 'success' : 'secondary' }} mt-1">
                                        Razorpay: {{ strtoupper($payoutStatus) }}
                                    </span>
                                </td>
                                <td>
                                    @if(isset($payout->metadata['manual_payout_reason']))
                                        <small class="text-danger">{{ $payout->metadata['manual_payout_reason'] }}</small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $payout->created_at->format('M d, Y') }}</small><br>
                                    <small class="text-muted">{{ $payout->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="processManualPayout({{ $payout->id }}, '{{ $payout->booking->vendor->name }}', {{ $payout->vendor_payout_amount }})">
                                        <i class="fas fa-money-bill-wave"></i> Process
                                    </button>
                                    <a href="{{ url('/admin/vendor/kyc-reviews/' . ($vendorKyc ? $vendorKyc->id : '#')) }}" class="btn btn-sm btn-outline-secondary" {{ !$vendorKyc ? 'disabled' : '' }}>
                                        <i class="fas fa-file-alt"></i> KYC
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $manualPayouts->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">No pending manual payouts! All payouts are processed.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Process Manual Payout Modal -->
<div class="modal fade" id="processPayoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Manual Payout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="processPayoutForm">
                <div class="modal-body">
                    <input type="hidden" id="payout_id" name="payout_id">
                    
                    <div class="alert alert-info">
                        <strong>Vendor:</strong> <span id="vendor_name"></span><br>
                        <strong>Amount:</strong> ₹<span id="payout_amount"></span>
                    </div>

                    <div class="mb-3">
                        <label for="payout_mode" class="form-label">Payout Mode <span class="text-danger">*</span></label>
                        <select class="form-select" id="payout_mode" name="payout_mode" required>
                            <option value="">Select Mode</option>
                            <option value="bank_transfer">Bank Transfer (NEFT/RTGS/IMPS)</option>
                            <option value="upi">UPI Transfer</option>
                            <option value="razorpay_transfer">Razorpay Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="manual">Manual/Cash</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="payout_reference" class="form-label">Transaction Reference <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="payout_reference" name="payout_reference" placeholder="UTR / Transaction ID / Cheque No." required>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Confirm Payout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.border-left-warning {
    border-left: 4px solid #ffc107;
}
.border-left-success {
    border-left: 4px solid #28a745;
}
.border-left-info {
    border-left: 4px solid #17a2b8;
}
.border-left-primary {
    border-left: 4px solid #007bff;
}
.opacity-25 {
    opacity: 0.25;
}
</style>

<script>
const apiToken = '{{ session("api_token") ?? "" }}';
let processModal;

document.addEventListener('DOMContentLoaded', function() {
    processModal = new bootstrap.Modal(document.getElementById('processPayoutModal'));
});

function processManualPayout(payoutId, vendorName, amount) {
    document.getElementById('payout_id').value = payoutId;
    document.getElementById('vendor_name').textContent = vendorName;
    document.getElementById('payout_amount').textContent = parseFloat(amount).toFixed(2);
    
    // Reset form
    document.getElementById('processPayoutForm').reset();
    document.getElementById('payout_id').value = payoutId;
    
    processModal.show();
}

document.getElementById('processPayoutForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const payoutId = document.getElementById('payout_id').value;
    const payoutMode = document.getElementById('payout_mode').value;
    const payoutReference = document.getElementById('payout_reference').value;
    const notes = document.getElementById('notes').value;

    if (!payoutMode || !payoutReference) {
        alert('Please fill all required fields');
        return;
    }

    // Disable submit button
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    fetch(`/api/v1/admin/booking-payments/${payoutId}/process-manual-payout`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${apiToken}`,
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            payout_mode: payoutMode,
            payout_reference: payoutReference,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Manual payout processed successfully!');
            processModal.hide();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to process payout'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Payout';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to process payout');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Payout';
    });
});
</script>
@endsection

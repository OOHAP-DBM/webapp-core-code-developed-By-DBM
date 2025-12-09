@extends('layouts.vendor')

@section('page-title', 'Payouts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Payouts</h2>
        <p class="text-muted mb-0">Track earnings and payment history</p>
    </div>
    <button class="btn btn-vendor-primary" data-bs-toggle="modal" data-bs-target="#withdrawModal">
        <i class="bi bi-cash-stack me-2"></i>Request Payout
    </button>
</div>

<!-- Balance Overview -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="vendor-card">
            <div class="vendor-card-body">
                <div class="stat-icon" style="background: #d1fae5; color: #10b981;">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-label">Available Balance</div>
                <div class="stat-value">₹{{ number_format($balance['available'] ?? 0, 2) }}</div>
                <small class="text-muted">Ready to withdraw</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="vendor-card">
            <div class="vendor-card-body">
                <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-label">Pending</div>
                <div class="stat-value">₹{{ number_format($balance['pending'] ?? 0, 2) }}</div>
                <small class="text-muted">Processing payments</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="vendor-card">
            <div class="vendor-card-body">
                <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div class="stat-label">This Month</div>
                <div class="stat-value">₹{{ number_format($balance['this_month'] ?? 0, 2) }}</div>
                <small class="text-muted">{{ date('F') }} earnings</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="vendor-card">
            <div class="vendor-card-body">
                <div class="stat-icon" style="background: #e0e7ff; color: #6366f1;">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="stat-label">Total Earned</div>
                <div class="stat-value">₹{{ number_format($balance['total_earned'] ?? 0, 2) }}</div>
                <small class="text-muted">All time</small>
            </div>
        </div>
    </div>
</div>

<!-- Bank Account Info -->
<div class="vendor-card mb-4">
    <div class="vendor-card-header">
        <h6 class="vendor-card-title mb-0">
            <i class="bi bi-bank me-2"></i>Bank Account
        </h6>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bankDetailsModal">
            <i class="bi bi-pencil"></i> Edit
        </button>
    </div>
    <div class="vendor-card-body">
        @if($bankDetails ?? false)
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-muted small">Account Holder</label>
                    <div class="fw-semibold">{{ $bankDetails->account_holder_name }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Account Number</label>
                    <div class="fw-semibold">XXXX XXXX {{ substr($bankDetails->account_number, -4) }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">IFSC Code</label>
                    <div class="fw-semibold">{{ $bankDetails->ifsc_code }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Bank Name</label>
                    <div class="fw-semibold">{{ $bankDetails->bank_name }}</div>
                </div>
            </div>
        @else
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Please add your bank account details to receive payouts
            </div>
        @endif
    </div>
</div>

<!-- Payout History -->
<div class="vendor-card">
    <div class="vendor-card-header">
        <h6 class="vendor-card-title mb-0">Payout History</h6>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" style="width: 150px;" id="statusFilter">
                <option value="">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="failed">Failed</option>
            </select>
            <button class="btn btn-sm btn-outline-primary">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
    </div>
    <div class="vendor-card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Reference</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payouts ?? [] as $payout)
                        <tr>
                            <td><strong>#{{ $payout->transaction_id }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($payout->created_at)->format('d M Y, H:i') }}</td>
                            <td><strong>₹{{ number_format($payout->amount, 2) }}</strong></td>
                            <td>
                                <i class="bi bi-bank me-1"></i>
                                {{ $payout->method === 'bank_transfer' ? 'Bank Transfer' : ucfirst($payout->method) }}
                            </td>
                            <td>
                                <span class="badge 
                                    @if($payout->status === 'completed') bg-success
                                    @elseif($payout->status === 'pending') bg-warning text-dark
                                    @elseif($payout->status === 'processing') bg-info
                                    @elseif($payout->status === 'failed') bg-danger
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst($payout->status) }}
                                </span>
                            </td>
                            <td>
                                @if($payout->reference_number)
                                    <small class="text-muted">{{ $payout->reference_number }}</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewPayout('{{ $payout->id }}')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No payout history yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($payouts) && $payouts->hasPages())
            <div class="mt-3">
                {{ $payouts->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Withdraw Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Payout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('vendor.payouts.request') }}" method="POST" id="withdrawForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Available Balance: <strong>₹{{ number_format($balance['available'] ?? 0, 2) }}</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" name="amount" 
                                   min="100" max="{{ $balance['available'] ?? 0 }}" 
                                   step="0.01" required>
                        </div>
                        <small class="text-muted">Minimum withdrawal: ₹100</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Withdrawal Method</label>
                        <select class="form-select" name="method" required>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                    
                    <div class="alert alert-warning mb-0">
                        <small><i class="bi bi-clock me-1"></i> Payouts are processed within 2-3 business days</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vendor-primary">Request Payout</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bank Details Modal -->
<div class="modal fade" id="bankDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bank Account Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('vendor.payouts.update-bank') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Account Holder Name *</label>
                        <input type="text" class="form-control" name="account_holder_name" 
                               value="{{ $bankDetails->account_holder_name ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Number *</label>
                        <input type="text" class="form-control" name="account_number" 
                               value="{{ $bankDetails->account_number ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Account Number *</label>
                        <input type="text" class="form-control" name="account_number_confirmation" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">IFSC Code *</label>
                        <input type="text" class="form-control" name="ifsc_code" 
                               value="{{ $bankDetails->ifsc_code ?? '' }}" 
                               pattern="^[A-Z]{4}0[A-Z0-9]{6}$" required>
                        <small class="text-muted">11 character IFSC code</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank Name *</label>
                        <input type="text" class="form-control" name="bank_name" 
                               value="{{ $bankDetails->bank_name ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch Name</label>
                        <input type="text" class="form-control" name="branch_name" 
                               value="{{ $bankDetails->branch_name ?? '' }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vendor-primary">Save Details</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewPayout(id) {
    window.location.href = `/vendor/payouts/${id}`;
}

document.getElementById('statusFilter')?.addEventListener('change', function() {
    const url = new URL(window.location);
    if (this.value) {
        url.searchParams.set('status', this.value);
    } else {
        url.searchParams.delete('status');
    }
    window.location = url;
});

// Form validation
document.getElementById('withdrawForm')?.addEventListener('submit', function(e) {
    const amount = parseFloat(this.querySelector('[name="amount"]').value);
    const available = {{ $balance['available'] ?? 0 }};
    
    if (amount > available) {
        e.preventDefault();
        alert('Insufficient balance. Available: ₹' + available.toFixed(2));
    }
});
</script>
@endpush

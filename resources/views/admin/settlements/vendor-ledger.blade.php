@extends('layouts.admin')

@section('title', 'Vendor Ledger - ' . $vendor->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.settlements.ledgers') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left"></i> Back to Ledgers
            </a>
            <h2 class="mb-1">{{ $vendor->name }}</h2>
            <p class="text-muted mb-0">{{ $vendor->email }}</p>
        </div>
        <div>
            @if($balance['on_hold_amount'] > 0)
                <form method="POST" action="{{ route('admin.settlements.release-hold', $vendor) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Release all held amounts for this vendor?')">
                        <i class="fas fa-unlock"></i> Release Held Amounts
                    </button>
                </form>
            @endif
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adjustmentModal">
                <i class="fas fa-edit"></i> Manual Adjustment
            </button>
        </div>
    </div>

    <!-- Balance Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Current Balance</p>
                            <h3 class="mb-0">₹{{ number_format($balance['current_balance'], 2) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2">
                            <i class="fas fa-wallet fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75">Total ledger balance</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Available Balance</p>
                            <h3 class="mb-0">₹{{ number_format($balance['available_balance'], 2) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75">Ready for payout</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">On Hold</p>
                            <h3 class="mb-0">₹{{ number_format($balance['on_hold_amount'], 2) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2">
                            <i class="fas fa-lock fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75">Due to incomplete KYC</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Total Earnings</p>
                            <h3 class="mb-0">₹{{ number_format($balance['total_earnings'], 2) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2">
                            <i class="fas fa-arrow-up fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75">All time</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.settlements.vendor-ledger', $vendor) }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate?->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate?->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.settlements.vendor-ledger', $vendor) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Transaction History</h5>
        </div>
        <div class="card-body">
            @if($entries->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No transactions found</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Balance Before</th>
                                <th>Balance After</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entries as $entry)
                                <tr class="{{ $entry->is_on_hold ? 'table-warning' : '' }}">
                                    <td>
                                        <small>{{ $entry->transaction_date->format('M d, Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <code class="small">{{ $entry->transaction_reference }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $entry->transactionTypeColor }}">
                                            {{ $entry->transactionTypeLabel }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $entry->description }}
                                        @if($entry->is_on_hold)
                                            <br><small class="text-warning">
                                                <i class="fas fa-lock"></i> On Hold
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="{{ $entry->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $entry->formatted_amount }}
                                        </strong>
                                    </td>
                                    <td>₹{{ number_format($entry->balance_before, 2) }}</td>
                                    <td><strong>₹{{ number_format($entry->balance_after, 2) }}</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $entry->status === 'completed' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($entry->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Section -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Credits Summary</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td>Total Earnings</td>
                            <td class="text-end"><strong class="text-success">₹{{ number_format($balance['total_earnings'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Adjustments (Credit)</td>
                            <td class="text-end"><strong>₹{{ number_format(max(0, $balance['total_adjustments']), 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Debits Summary</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td>Commission Deductions</td>
                            <td class="text-end"><strong class="text-danger">₹{{ number_format($balance['total_commissions'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Payouts</td>
                            <td class="text-end"><strong class="text-danger">₹{{ number_format($balance['total_payouts'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Refunds</td>
                            <td class="text-end"><strong class="text-danger">₹{{ number_format($balance['total_refunds'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Penalties</td>
                            <td class="text-end"><strong class="text-danger">₹{{ number_format($balance['total_penalties'], 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Manual Adjustment Modal -->
<div class="modal fade" id="adjustmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.settlements.adjustment', $vendor) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Manual Ledger Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> Manual adjustments directly affect vendor balance. Use with caution.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" required placeholder="Use negative for debit, positive for credit">
                        <small class="text-muted">Example: -100 for ₹100 debit, 100 for ₹100 credit</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" required placeholder="Reason for adjustment">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Additional details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Record Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

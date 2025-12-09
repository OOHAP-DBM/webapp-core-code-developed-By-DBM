@extends('layouts.customer')

@section('title', 'My Payments')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Payments</h1>
            <p class="text-muted">Track all your payment transactions</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('customer.my.payments.export', 'pdf') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
               class="btn btn-outline-danger"><i class="bi bi-file-pdf"></i> PDF</a>
            <a href="{{ route('customer.my.payments.export', 'csv') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
               class="btn btn-outline-success"><i class="bi bi-file-excel"></i> CSV</a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Paid</h6>
                    <h3 class="mb-0 text-success">₹{{ number_format($summary['total_paid'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Pending</h6>
                    <h3 class="mb-0 text-warning">₹{{ number_format($summary['total_pending'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Refunded</h6>
                    <h3 class="mb-0 text-info">₹{{ number_format($summary['total_refunded'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Transaction ID or Booking..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="">All</option>
                        <option value="razorpay" {{ request('payment_method') == 'razorpay' ? 'selected' : '' }}>Razorpay</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Transaction ID</th>
                            <th>Booking</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td><code>{{ $payment->transaction_id ?? 'N/A' }}</code></td>
                            <td><strong>{{ $payment->booking_number }}</strong></td>
                            <td><strong>₹{{ number_format($payment->amount, 2) }}</strong></td>
                            <td>{{ ucfirst($payment->payment_method ?? 'N/A') }}</td>
                            <td>
                                @php
                                $colors = ['pending' => 'warning', 'completed' => 'success', 'failed' => 'danger', 'refunded' => 'info'];
                                @endphp
                                <span class="badge bg-{{ $colors[$payment->status] ?? 'secondary' }}">{{ ucfirst($payment->status) }}</span>
                            </td>
                            <td><small>{{ \Carbon\Carbon::parse($payment->created_at)->format('M d, Y H:i') }}</small></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" title="View Receipt"><i class="bi bi-receipt"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-wallet fs-1 text-muted d-block mb-3"></i>
                                <p class="text-muted">No payments found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
        <div class="card-footer bg-white">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection

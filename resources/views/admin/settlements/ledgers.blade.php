@extends('layouts.admin')

@section('title', 'Vendor Ledgers')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Vendor Ledgers</h2>
            <p class="text-muted mb-0">Track all vendor transactions and balances</p>
        </div>
        <div>
            <a href="{{ route('admin.settlements.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Settlements
            </a>
        </div>
    </div>

    <!-- Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.settlements.ledgers') }}">
                <div class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Search by vendor name or email..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Vendors Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Vendor</th>
                            <th>Current Balance</th>
                            <th>Available Balance</th>
                            <th>On Hold</th>
                            <th>Total Earnings</th>
                            <th>Total Payouts</th>
                            <th>Entries</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                            <tr>
                                <td>
                                    <strong>{{ $vendor->name }}</strong><br>
                                    <small class="text-muted">{{ $vendor->email }}</small>
                                </td>
                                <td>
                                    <strong class="{{ $vendor->ledger_balance['current_balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ₹{{ number_format($vendor->ledger_balance['current_balance'], 2) }}
                                    </strong>
                                </td>
                                <td>
                                    <span class="text-success">
                                        ₹{{ number_format($vendor->ledger_balance['available_balance'], 2) }}
                                    </span>
                                </td>
                                <td>
                                    @if($vendor->ledger_balance['on_hold_amount'] > 0)
                                        <span class="badge bg-warning">
                                            ₹{{ number_format($vendor->ledger_balance['on_hold_amount'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>₹{{ number_format($vendor->ledger_balance['total_earnings'], 2) }}</td>
                                <td>₹{{ number_format($vendor->ledger_balance['total_payouts'], 2) }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $vendor->ledger_entries_count }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.settlements.vendor-ledger', $vendor) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-book"></i> View Ledger
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    No vendors found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $vendors->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

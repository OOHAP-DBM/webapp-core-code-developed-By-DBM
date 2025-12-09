@extends('layouts.customer')

@section('title', 'My Quotations')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Quotations</h1>
            <p class="text-muted">Review and manage your quotations</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Quotations</h6>
                    <h3 class="mb-0">{{ $summary['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Pending</h6>
                    <h3 class="mb-0 text-warning">{{ $summary['pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Approved</h6>
                    <h3 class="mb-0 text-success">{{ $summary['approved'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Amount</h6>
                    <h3 class="mb-0">₹{{ number_format($summary['total_amount'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Quotation number..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quotations Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Quotation #</th>
                            <th>Amount</th>
                            <th>Valid Until</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations as $quotation)
                        <tr>
                            <td><strong>{{ $quotation->quotation_number }}</strong></td>
                            <td><strong>₹{{ number_format($quotation->total_amount, 2) }}</strong></td>
                            <td><small>{{ \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') }}</small></td>
                            <td>
                                @php
                                $colors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $colors[$quotation->status] ?? 'secondary' }}">{{ ucfirst($quotation->status) }}</span>
                            </td>
                            <td><small>{{ \Carbon\Carbon::parse($quotation->created_at)->format('M d, Y') }}</small></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('customer.quotations.show', $quotation->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($quotation->status == 'pending')
                                    <button class="btn btn-outline-success btn-sm" title="Accept">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-file-text fs-1 text-muted d-block mb-3"></i>
                                <p class="text-muted">No quotations found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($quotations->hasPages())
        <div class="card-footer bg-white">{{ $quotations->links() }}</div>
        @endif
    </div>
</div>
@endsection

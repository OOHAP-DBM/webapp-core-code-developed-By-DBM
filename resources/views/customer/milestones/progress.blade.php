@extends('layouts.customer')

@section('title', 'Milestone Payments')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">Milestone Payments</h2>
            <p class="text-muted mb-0">Track and manage your installment payments</p>
        </div>
        <a href="{{ route('customer.quotations.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Quotations
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Quotation Info -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-file-text"></i> Quotation #{{ $quotation->id }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Vendor:</strong><br>
                    {{ $quotation->enquiry->vendor->name ?? 'N/A' }}
                </div>
                <div class="col-md-3">
                    <strong>Total Amount:</strong><br>
                    ₹{{ number_format($quotation->grand_total, 2) }}
                </div>
                <div class="col-md-3">
                    <strong>Milestones:</strong><br>
                    {{ $quotation->milestone_count }} installments
                </div>
                <div class="col-md-3">
                    <strong>Status:</strong><br>
                    <span class="badge bg-{{ $quotation->status === 'approved' ? 'success' : 'warning' }}">
                        {{ ucfirst($quotation->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success mb-0">{{ $milestones->where('status', 'paid')->count() }}</h3>
                    <p class="text-muted mb-0 small">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning mb-0">{{ $milestones->where('status', 'due')->count() }}</h3>
                    <p class="text-muted mb-0 small">Due Now</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger mb-0">{{ $milestones->where('status', 'overdue')->count() }}</h3>
                    <p class="text-muted mb-0 small">Overdue</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-secondary">
                <div class="card-body text-center">
                    <h3 class="text-secondary mb-0">{{ $milestones->where('status', 'pending')->count() }}</h3>
                    <p class="text-muted mb-0 small">Upcoming</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Progress Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="mb-3">Payment Progress</h6>
            @php
                $totalPaid = $milestones->where('status', 'paid')->sum('calculated_amount');
                $progress = $quotation->grand_total > 0 ? ($totalPaid / $quotation->grand_total) * 100 : 0;
            @endphp
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-success" role="progressbar" 
                     style="width: {{ $progress }}%;" 
                     aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                    {{ number_format($progress, 1) }}% Complete
                </div>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small class="text-muted">Paid: ₹{{ number_format($totalPaid, 2) }}</small>
                <small class="text-muted">Remaining: ₹{{ number_format($quotation->grand_total - $totalPaid, 2) }}</small>
            </div>
        </div>
    </div>

    <!-- Milestones Timeline -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-task"></i> Payment Milestones</h5>
        </div>
        <div class="card-body">
            @foreach($milestones as $index => $milestone)
            <div class="card mb-3 {{ $milestone->isOverdue() ? 'border-danger' : ($milestone->status === 'paid' ? 'border-success' : 'border-secondary') }}">
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Milestone Number & Status -->
                        <div class="col-md-1 text-center">
                            <div class="milestone-badge">
                                @if($milestone->status === 'paid')
                                    <span class="badge bg-success rounded-circle p-3">
                                        <i class="bi bi-check-lg fs-4"></i>
                                    </span>
                                @elseif($milestone->status === 'overdue')
                                    <span class="badge bg-danger rounded-circle p-3">
                                        <i class="bi bi-exclamation-lg fs-4"></i>
                                    </span>
                                @elseif($milestone->status === 'due')
                                    <span class="badge bg-warning rounded-circle p-3">
                                        <i class="bi bi-clock fs-4"></i>
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-circle p-3">
                                        <i class="bi bi-circle fs-4"></i>
                                    </span>
                                @endif
                                <div class="mt-2"><strong>#{{ $index + 1 }}</strong></div>
                            </div>
                        </div>

                        <!-- Milestone Details -->
                        <div class="col-md-7">
                            <h5 class="mb-1">{{ $milestone->title }}</h5>
                            @if($milestone->description)
                                <p class="text-muted small mb-2">{{ $milestone->description }}</p>
                            @endif
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Amount:</strong> ₹{{ number_format($milestone->calculated_amount, 2) }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Due Date:</strong> 
                                    @if($milestone->due_date)
                                        {{ $milestone->due_date->format('M d, Y') }}
                                        @if($milestone->isOverdue())
                                            <span class="text-danger">({{ $milestone->due_date->diffForHumans() }})</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <strong>Status:</strong>
                                    <span class="badge bg-{{ 
                                        $milestone->status === 'paid' ? 'success' : 
                                        ($milestone->status === 'overdue' ? 'danger' : 
                                        ($milestone->status === 'due' ? 'warning' : 'secondary'))
                                    }}">
                                        {{ ucfirst($milestone->status) }}
                                    </span>
                                </div>
                            </div>
                            @if($milestone->paid_at)
                                <div class="mt-2">
                                    <i class="bi bi-check-circle text-success"></i> 
                                    Paid on {{ $milestone->paid_at->format('M d, Y h:i A') }}
                                    @if($milestone->invoice_number)
                                        | <a href="{{ route('customer.invoices.show', $milestone->invoice_number) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-file-text"></i> View Invoice
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="col-md-4 text-end">
                            @if($milestone->status === 'paid')
                                <button class="btn btn-success" disabled>
                                    <i class="bi bi-check-circle"></i> Paid
                                </button>
                            @elseif($milestone->status === 'overdue' || $milestone->status === 'due')
                                <a href="{{ route('customer.milestones.pay', $milestone->id) }}" 
                                   class="btn btn-{{ $milestone->status === 'overdue' ? 'danger' : 'warning' }}">
                                    <i class="bi bi-credit-card"></i> Pay Now
                                </a>
                                @if($milestone->status === 'overdue')
                                    <div class="text-danger small mt-2">
                                        ⚠️ Payment overdue - please pay immediately
                                    </div>
                                @endif
                            @else
                                <button class="btn btn-secondary" disabled>
                                    <i class="bi bi-clock"></i> Not Due Yet
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            @if($milestones->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="text-muted mt-3">No milestones configured for this quotation</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment History -->
    @if($milestones->where('status', 'paid')->count() > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Payment History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Milestone</th>
                            <th>Amount</th>
                            <th>Paid On</th>
                            <th>Transaction ID</th>
                            <th>Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($milestones->where('status', 'paid') as $milestone)
                        <tr>
                            <td>{{ $milestone->title }}</td>
                            <td>₹{{ number_format($milestone->calculated_amount, 2) }}</td>
                            <td>{{ $milestone->paid_at->format('M d, Y h:i A') }}</td>
                            <td><code>{{ $milestone->payment_transaction_id ?? 'N/A' }}</code></td>
                            <td>
                                @if($milestone->invoice_number)
                                    <a href="{{ route('customer.invoices.show', $milestone->invoice_number) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-text"></i> View
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.milestone-badge {
    display: flex;
    flex-direction: column;
    align-items: center;
}
</style>
@endsection

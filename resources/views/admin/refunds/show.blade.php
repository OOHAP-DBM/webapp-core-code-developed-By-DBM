@extends('layouts.admin')

@section('title', 'Refund Details - ' . $refund->refund_reference)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.refunds.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left"></i> Back to Refunds
            </a>
            <h2 class="mb-1">Refund Details</h2>
            <p class="text-muted mb-0">Reference: <code class="text-primary">{{ $refund->refund_reference }}</code></p>
        </div>
        <div>
            <span class="badge bg-{{ $refund->statusColor }} fs-6 px-3 py-2">
                {{ ucfirst($refund->status) }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Booking Reference</label>
                            <div>
                                <a href="#" class="text-decoration-none">
                                    {{ $refund->booking_type }} #{{ $refund->booking_id }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Refund Type</label>
                            <div>
                                <span class="badge bg-primary">{{ ucfirst($refund->refund_type) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Refund Method</label>
                            <div>
                                @if($refund->refund_method === 'auto')
                                    <span class="badge bg-info">Auto Refund</span>
                                @else
                                    <span class="badge bg-secondary">Manual Processing</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Cancellation Policy</label>
                            <div>
                                @if($refund->cancellationPolicy)
                                    <a href="{{ route('admin.cancellation-policies.index') }}" class="text-decoration-none">
                                        {{ $refund->cancellationPolicy->name }}
                                    </a>
                                @else
                                    <span class="text-muted">No policy</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amounts Breakdown -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Amounts Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td><strong>Original Booking Amount</strong></td>
                                    <td class="text-end">₹{{ number_format($refund->booking_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Refundable Amount (Policy)</td>
                                    <td class="text-end text-success">₹{{ number_format($refund->refundable_amount, 2) }}</td>
                                </tr>
                                @if($refund->customer_fee > 0)
                                <tr>
                                    <td>Customer Cancellation Fee</td>
                                    <td class="text-end text-danger">- ₹{{ number_format($refund->customer_fee, 2) }}</td>
                                </tr>
                                @endif
                                @if($refund->vendor_penalty > 0)
                                <tr>
                                    <td>Vendor Penalty</td>
                                    <td class="text-end text-warning">+ ₹{{ number_format($refund->vendor_penalty, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="border-top">
                                    <td><strong class="fs-5">Final Refund Amount</strong></td>
                                    <td class="text-end"><strong class="fs-5 text-success">₹{{ number_format($refund->refund_amount, 2) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cancellation Context -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Cancellation Context</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Cancelled By</label>
                            <div>
                                <span class="badge bg-light text-dark">{{ ucfirst($refund->cancelled_by_role) }}</span>
                                @if($refund->cancelledBy)
                                    <br><small class="text-muted">{{ $refund->cancelledBy->name ?? $refund->cancelledBy->email }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Hours Before Start</label>
                            <div>{{ $refund->hours_before_start }} hours</div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="text-muted small">Cancellation Reason</label>
                            <div class="p-3 bg-light rounded">
                                {{ $refund->cancellation_reason ?? 'No reason provided' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Gateway Status -->
            @if($refund->refund_method === 'auto')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Gateway Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">PG Refund ID</label>
                            <div>
                                @if($refund->pg_refund_id)
                                    <code>{{ $refund->pg_refund_id }}</code>
                                @else
                                    <span class="text-muted">Not yet processed</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">PG Payment ID</label>
                            <div>
                                @if($refund->pg_payment_id)
                                    <code>{{ $refund->pg_payment_id }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">PG Status</label>
                            <div>
                                @if($refund->pg_status)
                                    <span class="badge bg-info">{{ $refund->pg_status }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                        @if($refund->pg_error)
                        <div class="col-12 mb-3">
                            <label class="text-muted small">PG Error</label>
                            <div class="alert alert-danger">{{ $refund->pg_error }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Calculation Details -->
            @if($refund->calculation_details)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Calculation Details</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded">{{ json_encode($refund->calculation_details, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif

            <!-- Policy Snapshot -->
            @if($refund->policy_snapshot)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        Policy Snapshot
                        <button class="btn btn-sm btn-outline-secondary float-end" type="button" data-bs-toggle="collapse" data-bs-target="#policySnapshot">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </h5>
                </div>
                <div class="collapse" id="policySnapshot">
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded">{{ json_encode($refund->policy_snapshot, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Timeline -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @if($refund->initiated_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="badge bg-primary rounded-circle" style="width: 32px; height: 32px; padding-top: 8px;">
                                        <i class="fas fa-play fa-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Initiated</strong>
                                    <br><small class="text-muted">{{ $refund->initiated_at->format('M d, Y H:i:s') }}</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($refund->approved_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="badge bg-success rounded-circle" style="width: 32px; height: 32px; padding-top: 8px;">
                                        <i class="fas fa-check fa-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Approved</strong>
                                    <br><small class="text-muted">{{ $refund->approved_at->format('M d, Y H:i:s') }}</small>
                                    @if($refund->approvedBy)
                                        <br><small class="text-muted">by {{ $refund->approvedBy->name }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($refund->processed_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="badge bg-info rounded-circle" style="width: 32px; height: 32px; padding-top: 8px;">
                                        <i class="fas fa-cog fa-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Processing</strong>
                                    <br><small class="text-muted">{{ $refund->processed_at->format('M d, Y H:i:s') }}</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($refund->completed_at)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="badge bg-success rounded-circle" style="width: 32px; height: 32px; padding-top: 8px;">
                                        <i class="fas fa-check-circle fa-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Completed</strong>
                                    <br><small class="text-muted">{{ $refund->completed_at->format('M d, Y H:i:s') }}</small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Admin Actions -->
            @if($refund->isPending())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Admin Actions</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.refunds.approve', $refund) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Admin Notes</label>
                            <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add approval notes..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check"></i> Approve Refund
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Admin Notes -->
            @if($refund->admin_notes)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Admin Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $refund->admin_notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

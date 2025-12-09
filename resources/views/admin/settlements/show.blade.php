@extends('layouts.admin')

@section('title', 'Settlement Batch Details')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <a href="{{ route('admin.settlements.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h2 class="mb-1">
                {{ $batch->batch_reference }}
                <span class="badge bg-{{ $batch->status_color }} ms-2">{{ ucfirst(str_replace('_', ' ', $batch->status)) }}</span>
            </h2>
            <p class="text-muted mb-0">{{ $batch->formatted_period }}</p>
        </div>
        <div>
            @if($batch->isDraft())
                <form method="POST" action="{{ route('admin.settlements.submit', $batch) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane"></i> Submit for Approval
                    </button>
                </form>
            @elseif($batch->isPendingApproval())
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="fas fa-check"></i> Approve Batch
                </button>
            @elseif($batch->isApproved())
                <form method="POST" action="{{ route('admin.settlements.process', $batch) }}" class="d-inline" 
                      onsubmit="return confirm('Process this batch? This will initiate Razorpay transfers for KYC-verified vendors.')">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-cog"></i> Process Batch
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Amount</h6>
                            <h3 class="mb-0">₹{{ number_format($batch->total_bookings_amount, 2) }}</h3>
                        </div>
                        <i class="fas fa-rupee-sign fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 mb-1">Admin Commission</h6>
                            <h3 class="mb-0">₹{{ number_format($batch->total_admin_commission, 2) }}</h3>
                        </div>
                        <i class="fas fa-percent fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 mb-1">Vendor Payout</h6>
                            <h3 class="mb-0">₹{{ number_format($batch->total_vendor_payout, 2) }}</h3>
                        </div>
                        <i class="fas fa-wallet fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 mb-1">PG Fees</h6>
                            <h3 class="mb-0">₹{{ number_format($batch->total_pg_fees, 2) }}</h3>
                        </div>
                        <i class="fas fa-credit-card fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Counts -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Bookings</h6>
                            <h4 class="mb-0">{{ $batch->total_bookings_count }}</h4>
                        </div>
                        <i class="fas fa-file-invoice fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Vendors</h6>
                            <h4 class="mb-0">{{ $batch->vendors_count }}</h4>
                        </div>
                        <i class="fas fa-users fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending KYC</h6>
                            <h4 class="mb-0">
                                {{ $batch->pending_kyc_count }}
                                @if($batch->pending_kyc_count > 0)
                                    <span class="badge bg-warning text-dark ms-2">Requires Attention</span>
                                @endif
                            </h4>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Workflow -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-tasks"></i> Approval Workflow</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th width="150">Created By:</th>
                            <td>{{ $batch->creator->name ?? 'System' }}</td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td>{{ $batch->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if($batch->approved_by)
                        <tr>
                            <th>Approved By:</th>
                            <td>{{ $batch->approver->name }}</td>
                        </tr>
                        <tr>
                            <th>Approved At:</th>
                            <td>{{ $batch->approved_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
                <div class="col-md-6">
                    @if($batch->approval_notes)
                    <div class="alert alert-info mb-0">
                        <strong>Approval Notes:</strong>
                        <p class="mb-0">{{ $batch->approval_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Processing Results -->
    @if($batch->isCompleted() || $batch->isFailed())
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Processing Results</h5>
        </div>
        <div class="card-body">
            @php
                $results = $batch->processing_errors ?? [];
                $successCount = $results['success_count'] ?? 0;
                $heldCount = $results['held_count'] ?? 0;
                $failedCount = $results['failed_count'] ?? 0;
            @endphp

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="alert alert-success mb-0">
                        <h5>{{ $successCount }}</h5>
                        <small>Successfully Processed</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-warning mb-0">
                        <h5>{{ $heldCount }}</h5>
                        <small>Held (KYC Incomplete)</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-danger mb-0">
                        <h5>{{ $failedCount }}</h5>
                        <small>Failed</small>
                    </div>
                </div>
            </div>

            @if(!empty($results['failed']))
            <div class="alert alert-danger">
                <strong>Failed Transfers:</strong>
                <ul class="mb-0">
                    @foreach($results['failed'] as $failed)
                        <li>Payment #{{ $failed['payment_id'] }}: {{ $failed['error'] }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Booking Payments -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list"></i> Booking Payments</h5>
        </div>
        <div class="card-body">
            @if($batch->bookingPayments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Vendor</th>
                            <th>Booking Amount</th>
                            <th>Commission</th>
                            <th>Vendor Payout</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batch->bookingPayments as $payment)
                        <tr>
                            <td>
                                <a href="{{ route('admin.bookings.show', $payment->booking_id) }}" class="text-decoration-none">
                                    #{{ $payment->booking_id }}
                                </a>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $payment->booking->vendor->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $payment->booking->vendor->email }}</small>
                                </div>
                            </td>
                            <td>₹{{ number_format($payment->gross_amount, 2) }}</td>
                            <td>₹{{ number_format($payment->admin_commission_amount, 2) }}</td>
                            <td>₹{{ number_format($payment->vendor_payout_amount, 2) }}</td>
                            <td>
                                @if($payment->vendor_payout_status === 'auto_paid')
                                    <span class="badge bg-success">Auto Paid</span>
                                @elseif($payment->vendor_payout_status === 'pending_manual_payout')
                                    <span class="badge bg-warning">Pending Manual</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($payment->vendor_payout_status) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $payment->booking_id) }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i> No booking payments found for this batch period.
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.settlements.approve', $batch) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Settlement Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Batch: {{ $batch->batch_reference }}</strong><br>
                        <small>{{ $batch->formatted_period }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Approval Notes</label>
                        <textarea name="approval_notes" class="form-control" rows="3" placeholder="Enter any notes or comments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Batch</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

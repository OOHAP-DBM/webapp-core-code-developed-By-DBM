@extends('layouts.admin')

@section('title', 'Payment Settlements')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Payment Settlement Engine</h2>
            <p class="text-muted mb-0">Manage vendor payout batches with KYC-based split logic</p>
        </div>
        <div>
            <a href="{{ route('admin.settlements.ledgers') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-book"></i> Vendor Ledgers
            </a>
            <a href="{{ route('admin.settlements.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Batch
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Pending Approval</p>
                            <h3 class="mb-0">{{ $statistics['pending_approval'] }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75">Awaiting admin review</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Processing</p>
                            <h3 class="mb-0">{{ $statistics['processing_batches'] }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2">
                            <i class="fas fa-cog fa-lg fa-spin"></i>
                        </div>
                    </div>
                    <small class="opacity-75">In progress</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Settled Amount</p>
                            <h3 class="mb-0">₹{{ number_format($statistics['total_settled_amount'], 0) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75">Completed batches</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Pending Settlement</p>
                            <h3 class="mb-0">₹{{ number_format($statistics['pending_settlement_amount'], 0) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2">
                            <i class="fas fa-hourglass-half fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75">Draft + Approved</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.settlements.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.settlements.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Settlement Batches Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($batches->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No settlement batches found</p>
                    <a href="{{ route('admin.settlements.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create First Batch
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Batch Reference</th>
                                <th>Period</th>
                                <th>Bookings</th>
                                <th>Vendors</th>
                                <th>Pending KYC</th>
                                <th>Total Payout</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $batch)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.settlements.show', $batch) }}" class="text-decoration-none">
                                            <code class="text-primary">{{ $batch->batch_reference }}</code>
                                        </a>
                                        @if($batch->batch_name)
                                            <br><small class="text-muted">{{ $batch->batch_name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $batch->formatted_period }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($batch->total_bookings_count) }}</strong>
                                    </td>
                                    <td>{{ number_format($batch->vendors_count) }}</td>
                                    <td>
                                        @if($batch->pending_kyc_count > 0)
                                            <span class="badge bg-warning">{{ $batch->pending_kyc_count }}</span>
                                        @else
                                            <span class="badge bg-success">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-success">₹{{ number_format($batch->total_vendor_payout, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $batch->statusColor }}">
                                            {{ ucfirst(str_replace('_', ' ', $batch->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $batch->created_at->format('M d, Y H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.settlements.show', $batch) }}" class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($batch->isDraft())
                                                <form method="POST" action="{{ route('admin.settlements.submit', $batch) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-info" title="Submit for Approval">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($batch->isPendingApproval())
                                                <button type="button" class="btn btn-outline-success" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#approveModal{{ $batch->id }}" 
                                                        title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                            @if($batch->isApproved())
                                                <form method="POST" action="{{ route('admin.settlements.process', $batch) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-primary" 
                                                            title="Process Batch"
                                                            onclick="return confirm('Process this settlement batch? This will initiate Razorpay transfers.')">
                                                        <i class="fas fa-cogs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Approve Modal -->
                                @if($batch->isPendingApproval())
                                <div class="modal fade" id="approveModal{{ $batch->id }}" tabindex="-1">
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
                                                        <strong>Batch:</strong> {{ $batch->batch_reference }}<br>
                                                        <strong>Total Payout:</strong> ₹{{ number_format($batch->total_vendor_payout, 2) }}<br>
                                                        <strong>Vendors:</strong> {{ $batch->vendors_count }} ({{ $batch->pending_kyc_count }} with pending KYC)
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Approval Notes (Optional)</label>
                                                        <textarea name="approval_notes" class="form-control" rows="3" placeholder="Add any notes about this approval..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fas fa-check"></i> Approve Batch
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $batches->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

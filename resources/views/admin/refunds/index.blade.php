@extends('layouts.admin')

@section('title', 'Refund Management')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Refund Management</h2>
            <p class="text-muted mb-0">Track and process booking refunds</p>
        </div>
        <div>
            <a href="{{ route('admin.cancellation-policies.index') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-cog"></i> Manage Policies
            </a>
            <a href="{{ route('admin.refunds.export') }}" class="btn btn-success">
                <i class="fas fa-download"></i> Export Report
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
                            <p class="mb-1 opacity-75">Total Refunds</p>
                            <h3 class="mb-0">{{ $statistics['total_refunds'] }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2">
                            <i class="fas fa-undo fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75">All time refunds</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Pending Approval</p>
                            <h3 class="mb-0">{{ $statistics['pending_refunds'] }}</h3>
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
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Total Amount</p>
                            <h3 class="mb-0">₹{{ number_format($statistics['total_refund_amount'], 2) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2">
                            <i class="fas fa-rupee-sign fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75">Refunded to customers</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Completed</p>
                            <h3 class="mb-0">{{ $statistics['completed_refunds'] }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-2">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75">Successfully processed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.refunds.index') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small">Refund Method</label>
                        <select name="refund_method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="auto" {{ request('refund_method') == 'auto' ? 'selected' : '' }}>Auto Refund</option>
                            <option value="manual" {{ request('refund_method') == 'manual' ? 'selected' : '' }}>Manual</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small">Cancelled By</label>
                        <select name="cancelled_by_role" class="form-select">
                            <option value="">All Roles</option>
                            <option value="customer" {{ request('cancelled_by_role') == 'customer' ? 'selected' : '' }}>Customer</option>
                            <option value="vendor" {{ request('cancelled_by_role') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                            <option value="admin" {{ request('cancelled_by_role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Refunds Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($refunds->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No refunds found</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Booking</th>
                                <th>Booking Amount</th>
                                <th>Customer Fee</th>
                                <th>Vendor Penalty</th>
                                <th>Refund Amount</th>
                                <th>Status</th>
                                <th>Method</th>
                                <th>Cancelled By</th>
                                <th>Initiated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($refunds as $refund)
                                <tr>
                                    <td>
                                        <code class="text-primary">{{ $refund->refund_reference }}</code>
                                    </td>
                                    <td>
                                        <a href="#" class="text-decoration-none">
                                            {{ $refund->booking_type }} #{{ $refund->booking_id }}
                                        </a>
                                    </td>
                                    <td>₹{{ number_format($refund->booking_amount, 2) }}</td>
                                    <td>
                                        @if($refund->customer_fee > 0)
                                            <span class="text-danger">-₹{{ number_format($refund->customer_fee, 2) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($refund->vendor_penalty > 0)
                                            <span class="text-warning">₹{{ number_format($refund->vendor_penalty, 2) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-success">₹{{ number_format($refund->refund_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $refund->statusColor }}">
                                            {{ ucfirst($refund->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($refund->refund_method === 'auto')
                                            <span class="badge bg-info">Auto</span>
                                        @else
                                            <span class="badge bg-secondary">Manual</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ ucfirst($refund->cancelled_by_role) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $refund->initiated_at ? $refund->initiated_at->format('M d, Y H:i') : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.refunds.show', $refund) }}" class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($refund->isPending())
                                                <button type="button" class="btn btn-outline-success" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#approveModal{{ $refund->id }}" 
                                                        title="Approve Refund">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Approve Modal -->
                                @if($refund->isPending())
                                <div class="modal fade" id="approveModal{{ $refund->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.refunds.approve', $refund) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Approve Refund</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-info">
                                                        <strong>Refund Amount:</strong> ₹{{ number_format($refund->refund_amount, 2) }}<br>
                                                        <strong>Method:</strong> {{ ucfirst($refund->refund_method) }}
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Admin Notes (Optional)</label>
                                                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add any notes about this approval..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fas fa-check"></i> Approve Refund
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
                    {{ $refunds->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

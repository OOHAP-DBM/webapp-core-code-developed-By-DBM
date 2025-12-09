@extends('layouts.admin')

@section('title', 'Cancellation Policies')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Cancellation Policies</h2>
            <p class="text-muted mb-0">Configure refund rules and cancellation policies</p>
        </div>
        <div>
            <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-list"></i> View Refunds
            </a>
            <a href="{{ route('admin.cancellation-policies.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Policy
            </a>
        </div>
    </div>

    <!-- Active Policies -->
    @if($policies->where('is_active', true)->count() > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Active Policies</h5>
        </div>
        <div class="card-body">
            @foreach($policies->where('is_active', true) as $policy)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                {{ $policy->name }}
                                @if($policy->is_default)
                                    <span class="badge bg-primary">Default</span>
                                @endif
                            </h6>
                            <p class="text-muted small mb-2">{{ $policy->description }}</p>
                            
                            <div class="row g-2 mt-2">
                                <div class="col-auto">
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-user-tag"></i> 
                                        {{ ucfirst($policy->applies_to) }}
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-tag"></i> 
                                        {{ strtoupper($policy->booking_type) }}
                                    </span>
                                </div>
                                @if($policy->auto_refund_enabled)
                                <div class="col-auto">
                                    <span class="badge bg-info">
                                        <i class="fas fa-robot"></i> Auto Refund
                                    </span>
                                </div>
                                @endif
                                @if($policy->pos_auto_refund_disabled)
                                <div class="col-auto">
                                    <span class="badge bg-warning">
                                        <i class="fas fa-ban"></i> POS Auto-Refund Disabled
                                    </span>
                                </div>
                                @endif
                            </div>

                            <!-- Time Windows Preview -->
                            @if($policy->time_windows && count($policy->time_windows) > 0)
                            <div class="mt-3">
                                <small class="text-muted fw-bold">Refund Schedule:</small>
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Hours Before</th>
                                                <th>Refund %</th>
                                                <th>Customer Fee %</th>
                                                <th>Vendor Penalty %</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($policy->time_windows as $window)
                                            <tr>
                                                <td>≥ {{ $window['hours_before'] }}h</td>
                                                <td><span class="badge bg-success">{{ $window['refund_percent'] }}%</span></td>
                                                <td>
                                                    @if(isset($window['customer_fee_percent']))
                                                        <span class="badge bg-danger">{{ $window['customer_fee_percent'] }}%</span>
                                                    @else
                                                        <span class="text-muted">Policy Default</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($window['vendor_penalty_percent']))
                                                        <span class="badge bg-warning">{{ $window['vendor_penalty_percent'] }}%</span>
                                                    @else
                                                        <span class="text-muted">Policy Default</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="ms-3">
                            <div class="btn-group btn-group-sm">
                                <a href="#" class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" title="Deactivate">
                                    <i class="fas fa-toggle-on"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Inactive Policies -->
    @if($policies->where('is_active', false)->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Inactive Policies</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Applies To</th>
                            <th>Booking Type</th>
                            <th>Time Windows</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($policies->where('is_active', false) as $policy)
                        <tr>
                            <td>
                                {{ $policy->name }}
                                @if($policy->is_default)
                                    <span class="badge bg-primary">Default</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ ucfirst($policy->applies_to) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ strtoupper($policy->booking_type) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ count($policy->time_windows ?? []) }} tiers
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $policy->created_at->format('M d, Y') }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="#" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-success" title="Activate">
                                        <i class="fas fa-toggle-off"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($policies->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
            <p class="text-muted">No cancellation policies found</p>
            <a href="{{ route('admin.cancellation-policies.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create First Policy
            </a>
        </div>
    </div>
    @endif
</div>
@endsection

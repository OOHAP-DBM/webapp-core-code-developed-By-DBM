@extends('layouts.admin')

@section('title', 'Commission Rule Detail')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-info-circle me-2"></i>{{ $commissionRule->name }}</h4>
            <div>
                <a href="{{ route('admin.commission-rules.edit', $commissionRule) }}" class="btn btn-light btn-sm me-2">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <a href="{{ route('admin.commission-rules.index') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <h6 class="text-muted border-bottom pb-2">Basic Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Name:</th>
                            <td>{{ $commissionRule->name }}</td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $commissionRule->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Rule Type:</th>
                            <td><span class="badge bg-info">{{ $commissionRule->getRuleTypeLabel() }}</span></td>
                        </tr>
                        <tr>
                            <th>Priority:</th>
                            <td><span class="badge bg-dark">{{ $commissionRule->priority }}</span></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $commissionRule->is_active ? 'success' : 'secondary' }}">
                                    {{ $commissionRule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    <h6 class="text-muted border-bottom pb-2 mt-4">Scope & Filters</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Vendor:</th>
                            <td>{{ $commissionRule->vendor->name ?? 'All Vendors' }}</td>
                        </tr>
                        <tr>
                            <th>Hoarding:</th>
                            <td>{{ $commissionRule->hoarding->title ?? 'All Hoardings' }}</td>
                        </tr>
                        <tr>
                            <th>City:</th>
                            <td>{{ $commissionRule->city ?? 'All Cities' }}</td>
                        </tr>
                        <tr>
                            <th>Area:</th>
                            <td>{{ $commissionRule->area ?? 'All Areas' }}</td>
                        </tr>
                        <tr>
                            <th>Hoarding Type:</th>
                            <td>{{ $commissionRule->hoarding_type ? ucfirst($commissionRule->hoarding_type) : 'All Types' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <h6 class="text-muted border-bottom pb-2">Commission Configuration</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Commission Type:</th>
                            <td><span class="badge bg-success">{{ $commissionRule->getCommissionTypeLabel() }}</span></td>
                        </tr>
                        <tr>
                            <th>Commission Value:</th>
                            <td>
                                @if($commissionRule->commission_type === 'percentage')
                                <strong class="text-success">{{ $commissionRule->commission_value }}%</strong>
                                @else
                                <strong class="text-primary">₹{{ number_format($commissionRule->commission_value, 2) }}</strong>
                                @endif
                            </td>
                        </tr>
                    </table>

                    <h6 class="text-muted border-bottom pb-2 mt-4">Validity Period</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Valid From:</th>
                            <td>{{ $commissionRule->valid_from?->format('F d, Y') ?? 'No start date' }}</td>
                        </tr>
                        <tr>
                            <th>Valid To:</th>
                            <td>{{ $commissionRule->valid_to?->format('F d, Y') ?? 'No end date' }}</td>
                        </tr>
                        @if($commissionRule->is_seasonal)
                        <tr>
                            <th>Seasonal:</th>
                            <td><span class="badge bg-warning">{{ $commissionRule->season_name }}</span></td>
                        </tr>
                        @endif
                    </table>

                    <h6 class="text-muted border-bottom pb-2 mt-4">Booking Constraints</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Amount Range:</th>
                            <td>
                                {{ $commissionRule->min_booking_amount ? '₹' . number_format($commissionRule->min_booking_amount, 2) : 'No min' }}
                                -
                                {{ $commissionRule->max_booking_amount ? '₹' . number_format($commissionRule->max_booking_amount, 2) : 'No max' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Duration Range:</th>
                            <td>
                                {{ $commissionRule->min_duration_days ?? 'No min' }} days
                                -
                                {{ $commissionRule->max_duration_days ?? 'No max' }} days
                            </td>
                        </tr>
                    </table>

                    <h6 class="text-muted border-bottom pb-2 mt-4">Usage Statistics</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Usage Count:</th>
                            <td><strong>{{ $commissionRule->usage_count }}</strong></td>
                        </tr>
                        <tr>
                            <th>Last Used:</th>
                            <td>{{ $commissionRule->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                        </tr>
                        <tr>
                            <th>Created By:</th>
                            <td>{{ $commissionRule->creator->name ?? 'N/A' }} on {{ $commissionRule->created_at->format('M d, Y') }}</td>
                        </tr>
                        @if($commissionRule->updater)
                        <tr>
                            <th>Updated By:</th>
                            <td>{{ $commissionRule->updater->name }} on {{ $commissionRule->updated_at->format('M d, Y') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

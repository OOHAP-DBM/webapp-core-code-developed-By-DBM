@extends('layouts.admin')

@section('title', 'Commission Rule Detail')

@section('content')
<div class="container-fluid py-3 py-md-4 px-3 px-md-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h4 class="mb-0 fs-6 fs-md-4 text-truncate" style="max-width: 60%;">
                <i class="bi bi-info-circle me-2"></i>{{ $commissionRule->name }}
            </h4>
            <div class="d-flex gap-2 flex-shrink-0">
                <a href="{{ route('admin.commission-rules.edit', $commissionRule) }}" class="btn btn-light btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <a href="{{ route('admin.commission-rules.index') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        <div class="card-body p-3 p-md-4">
            <div class="row g-4">

                <!-- Left Column -->
                <div class="col-12 col-md-6">
                    <h6 class="text-muted border-bottom pb-2 mb-3">Basic Information</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th class="text-muted fw-semibold" style="width:45%; font-size:0.82rem;">Name</th>
                            <td>{{ $commissionRule->name }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Description</th>
                            <td>{{ $commissionRule->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Rule Type</th>
                            <td><span class="badge bg-info">{{ $commissionRule->getRuleTypeLabel() }}</span></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Priority</th>
                            <td><span class="badge bg-dark">{{ $commissionRule->priority }}</span></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Status</th>
                            <td>
                                <span class="badge bg-{{ $commissionRule->is_active ? 'success' : 'secondary' }}">
                                    {{ $commissionRule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    <h6 class="text-muted border-bottom pb-2 mb-3 mt-4">Scope &amp; Filters</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th class="text-muted fw-semibold" style="width:45%; font-size:0.82rem;">Vendor</th>
                            <td>{{ $commissionRule->vendor->name ?? 'All Vendors' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Hoarding</th>
                            <td>{{ $commissionRule->hoarding->title ?? 'All Hoardings' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">City</th>
                            <td>{{ $commissionRule->city ?? 'All Cities' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Area</th>
                            <td>{{ $commissionRule->area ?? 'All Areas' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Hoarding Type</th>
                            <td>{{ $commissionRule->hoarding_type ? ucfirst($commissionRule->hoarding_type) : 'All Types' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Right Column -->
                <div class="col-12 col-md-6">
                    <h6 class="text-muted border-bottom pb-2 mb-3">Commission Configuration</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th class="text-muted fw-semibold" style="width:45%; font-size:0.82rem;">Commission Type</th>
                            <td><span class="badge bg-success">{{ $commissionRule->getCommissionTypeLabel() }}</span></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Commission Value</th>
                            <td>
                                @if($commissionRule->commission_type === 'percentage')
                                <strong class="text-success">{{ $commissionRule->commission_value }}%</strong>
                                @else
                                <strong class="text-primary">₹{{ number_format($commissionRule->commission_value, 2) }}</strong>
                                @endif
                            </td>
                        </tr>
                    </table>

                    <h6 class="text-muted border-bottom pb-2 mb-3 mt-4">Validity Period</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th class="text-muted fw-semibold" style="width:45%; font-size:0.82rem;">Valid From</th>
                            <td>{{ $commissionRule->valid_from?->format('F d, Y') ?? 'No start date' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Valid To</th>
                            <td>{{ $commissionRule->valid_to?->format('F d, Y') ?? 'No end date' }}</td>
                        </tr>
                        @if($commissionRule->is_seasonal)
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Seasonal</th>
                            <td><span class="badge bg-warning text-dark">{{ $commissionRule->season_name }}</span></td>
                        </tr>
                        @endif
                    </table>

                    <h6 class="text-muted border-bottom pb-2 mb-3 mt-4">Booking Constraints</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th class="text-muted fw-semibold" style="width:45%; font-size:0.82rem;">Amount Range</th>
                            <td>
                                {{ $commissionRule->min_booking_amount ? '₹' . number_format($commissionRule->min_booking_amount, 2) : 'No min' }}
                                –
                                {{ $commissionRule->max_booking_amount ? '₹' . number_format($commissionRule->max_booking_amount, 2) : 'No max' }}
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Duration Range</th>
                            <td>
                                {{ $commissionRule->min_duration_days ?? 'No min' }} days
                                –
                                {{ $commissionRule->max_duration_days ?? 'No max' }} days
                            </td>
                        </tr>
                    </table>

                    <h6 class="text-muted border-bottom pb-2 mb-3 mt-4">Usage Statistics</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th class="text-muted fw-semibold" style="width:45%; font-size:0.82rem;">Usage Count</th>
                            <td><strong>{{ $commissionRule->usage_count }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Last Used</th>
                            <td>{{ $commissionRule->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Created By</th>
                            <td>
                                {{ $commissionRule->creator->name ?? 'N/A' }}
                                <small class="text-muted d-block">{{ $commissionRule->created_at->format('M d, Y') }}</small>
                            </td>
                        </tr>
                        @if($commissionRule->updater)
                        <tr>
                            <th class="text-muted fw-semibold" style="font-size:0.82rem;">Updated By</th>
                            <td>
                                {{ $commissionRule->updater->name }}
                                <small class="text-muted d-block">{{ $commissionRule->updated_at->format('M d, Y') }}</small>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
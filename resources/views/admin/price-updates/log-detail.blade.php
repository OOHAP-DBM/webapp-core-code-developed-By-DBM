@extends('layouts.admin')

@section('title', 'Price Update Log Detail')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Main Log Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-file-text me-2"></i>Update Log #{{ $log->id }}</h4>
                    <a href="{{ route('admin.price-updates.logs') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back to Logs
                    </a>
                </div>
                <div class="card-body">
                    <!-- Basic Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Update Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Date & Time:</th>
                                    <td>{{ $log->created_at->format('F d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Update Type:</th>
                                    <td>
                                        <span class="badge {{ $log->update_type === 'single' ? 'bg-primary' : 'bg-info' }}">
                                            {{ ucfirst($log->update_type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Performed By:</th>
                                    <td>{{ $log->admin->name ?? 'N/A' }} ({{ $log->admin->email ?? 'N/A' }})</td>
                                </tr>
                                @if($log->batch_id)
                                <tr>
                                    <th>Batch ID:</th>
                                    <td><code>{{ $log->batch_id }}</code></td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Affected Hoardings:</th>
                                    <td>{{ $log->affected_hoardings_count }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Price Changes</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Old Weekly Price:</th>
                                    <td>₹{{ number_format($log->old_weekly_price ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>New Weekly Price:</th>
                                    <td>₹{{ number_format($log->new_weekly_price ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Old Monthly Price:</th>
                                    <td>₹{{ number_format($log->old_monthly_price, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>New Monthly Price:</th>
                                    <td>₹{{ number_format($log->new_monthly_price, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Monthly Change:</th>
                                    <td>
                                        <span class="{{ $log->monthly_price_change >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $log->monthly_price_change >= 0 ? '+' : '' }}₹{{ number_format(abs($log->monthly_price_change), 2) }}
                                            ({{ number_format($log->monthly_price_change_percent, 2) }}%)
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Hoarding Info -->
                    <div class="mb-4">
                        <h6 class="text-muted border-bottom pb-2">Hoarding Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Title:</strong> {{ $log->hoarding->title ?? 'N/A' }}<br>
                                <strong>Vendor:</strong> {{ $log->hoarding->vendor->name ?? 'N/A' }}<br>
                                <strong>Type:</strong> {{ ucfirst($log->hoarding->type ?? 'N/A') }}<br>
                            </div>
                            <div class="col-md-6">
                                <strong>Address:</strong> {{ $log->hoarding->address ?? 'N/A' }}<br>
                                <strong>Status:</strong> {{ ucfirst($log->hoarding->status ?? 'N/A') }}<br>
                                <a href="{{ route('admin.hoardings.show', $log->hoarding_id) }}" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="bi bi-eye me-1"></i>View Hoarding
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Update Details -->
                    @if($log->update_type === 'bulk' && $log->bulk_criteria)
                    <div class="mb-4">
                        <h6 class="text-muted border-bottom pb-2">Bulk Update Criteria</h6>
                        <div class="row">
                            @foreach($log->formatted_criteria as $key => $value)
                            <div class="col-md-4 mb-2">
                                <strong>{{ $key }}:</strong> {{ $value }}
                            </div>
                            @endforeach
                        </div>
                        @if($log->update_method)
                        <div class="mt-3">
                            <strong>Update Method:</strong> {{ ucfirst($log->update_method) }}
                            @if($log->update_value)
                            (Value: {{ $log->update_value }})
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Reason -->
                    @if($log->reason)
                    <div class="mb-4">
                        <h6 class="text-muted border-bottom pb-2">Reason for Update</h6>
                        <p class="mb-0">{{ $log->reason }}</p>
                    </div>
                    @endif

                    <!-- Hoarding Snapshot -->
                    @if($log->hoarding_snapshot)
                    <div class="mb-4">
                        <h6 class="text-muted border-bottom pb-2">Hoarding Snapshot (At Time of Update)</h6>
                        <div class="accordion" id="snapshotAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#snapshotData">
                                        View Complete Snapshot Data
                                    </button>
                                </h2>
                                <div id="snapshotData" class="accordion-collapse collapse" data-bs-parent="#snapshotAccordion">
                                    <div class="accordion-body">
                                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($log->hoarding_snapshot, JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Batch Logs (if bulk update) -->
            @if($batchLogs && $batchLogs->count() > 1)
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-collection me-2"></i>All Updates in This Batch ({{ $batchLogs->count() }} hoardings)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Hoarding ID</th>
                                    <th>Title</th>
                                    <th>Old Price</th>
                                    <th>New Price</th>
                                    <th>Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batchLogs as $bLog)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.price-updates.logs.show', $bLog->id) }}">
                                            #{{ $bLog->hoarding_id }}
                                        </a>
                                    </td>
                                    <td>{{ $bLog->hoarding->title ?? 'N/A' }}</td>
                                    <td>₹{{ number_format($bLog->old_monthly_price, 2) }}</td>
                                    <td>₹{{ number_format($bLog->new_monthly_price, 2) }}</td>
                                    <td>
                                        <span class="{{ $bLog->monthly_price_change >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $bLog->monthly_price_change >= 0 ? '+' : '' }}₹{{ number_format(abs($bLog->monthly_price_change), 2) }}
                                        </span>
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
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Create Settlement Batch')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="mb-4">
                <a href="{{ route('admin.settlements.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <h2 class="mb-1">Create Settlement Batch</h2>
                <p class="text-muted mb-0">Create a new batch to group vendor payouts for a specific period</p>
            </div>

            <!-- Form Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settlements.store') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label">Batch Name (Optional)</label>
                            <input type="text" name="batch_name" class="form-control @error('batch_name') is-invalid @enderror" 
                                   placeholder="e.g., Weekly Settlement - Week 1" value="{{ old('batch_name') }}">
                            @error('batch_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">If not provided, will be auto-generated based on dates</small>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Period Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="period_start" class="form-control @error('period_start') is-invalid @enderror" 
                                       value="{{ old('period_start') }}" required>
                                @error('period_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Period End Date <span class="text-danger">*</span></label>
                                <input type="date" name="period_end" class="form-control @error('period_end') is-invalid @enderror" 
                                       value="{{ old('period_end') }}" required>
                                @error('period_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Settlement Process</h6>
                            <ol class="mb-0 ps-3">
                                <li>Batch will be created in <strong>draft</strong> status</li>
                                <li>Review the batch details and submit for approval</li>
                                <li>Admin approves the batch</li>
                                <li>Process the batch to initiate Razorpay transfers</li>
                                <li>KYC-verified vendors receive auto-payout</li>
                                <li>Non-KYC vendors marked for manual payout</li>
                            </ol>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.settlements.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Batch
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Date Ranges -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Quick Date Ranges</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="setDateRange('last_week')">
                                <i class="fas fa-calendar-week"></i> Last Week
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="setDateRange('this_month')">
                                <i class="fas fa-calendar-alt"></i> This Month
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="setDateRange('last_month')">
                                <i class="fas fa-calendar"></i> Last Month
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setDateRange(range) {
    const today = new Date();
    let startDate, endDate;

    switch (range) {
        case 'last_week':
            startDate = new Date(today.setDate(today.getDate() - 7));
            endDate = new Date();
            break;
        case 'this_month':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date();
            break;
        case 'last_month':
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
    }

    document.querySelector('[name="period_start"]').value = startDate.toISOString().split('T')[0];
    document.querySelector('[name="period_end"]').value = endDate.toISOString().split('T')[0];
}
</script>
@endsection

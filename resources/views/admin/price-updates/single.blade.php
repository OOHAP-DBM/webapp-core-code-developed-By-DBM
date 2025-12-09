@extends('layouts.admin')

@section('title', 'Single Price Update')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Single Price Update</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.price-updates.single.store') }}" method="POST">
                        @csrf
                        
                        <!-- Hoarding Selection -->
                        <div class="mb-4">
                            <label for="hoarding_id" class="form-label fw-bold">Select Hoarding <span class="text-danger">*</span></label>
                            <select class="form-select @error('hoarding_id') is-invalid @enderror" 
                                    id="hoarding_id" 
                                    name="hoarding_id" 
                                    required>
                                <option value="">-- Choose Hoarding --</option>
                                @foreach($hoardings as $h)
                                <option value="{{ $h->id }}" 
                                        data-weekly="{{ $h->weekly_price }}"
                                        data-monthly="{{ $h->monthly_price }}"
                                        {{ (old('hoarding_id', $hoarding->id ?? null) == $h->id) ? 'selected' : '' }}>
                                    {{ $h->title }} - {{ $h->vendor->name ?? 'N/A' }} ({{ $h->address }})
                                </option>
                                @endforeach
                            </select>
                            @error('hoarding_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Prices Display -->
                        <div class="alert alert-info" id="current-prices" style="display: none;">
                            <h6 class="alert-heading">Current Prices:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Weekly Price:</strong> ₹<span id="current-weekly">0.00</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Monthly Price:</strong> ₹<span id="current-monthly">0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- New Prices -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="weekly_price" class="form-label fw-bold">New Weekly Price (₹)</label>
                                <input type="number" 
                                       class="form-control @error('weekly_price') is-invalid @enderror" 
                                       id="weekly_price" 
                                       name="weekly_price" 
                                       step="0.01" 
                                       min="0"
                                       value="{{ old('weekly_price', $hoarding->weekly_price ?? '') }}"
                                       placeholder="Leave empty if not applicable">
                                @error('weekly_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="monthly_price" class="form-label fw-bold">New Monthly Price (₹) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('monthly_price') is-invalid @enderror" 
                                       id="monthly_price" 
                                       name="monthly_price" 
                                       step="0.01" 
                                       min="0"
                                       value="{{ old('monthly_price', $hoarding->monthly_price ?? '') }}"
                                       required>
                                @error('monthly_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="mb-4">
                            <label for="reason" class="form-label fw-bold">Reason for Update</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                      id="reason" 
                                      name="reason" 
                                      rows="3"
                                      placeholder="Optional: Explain why prices are being updated">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.price-updates.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Update Price
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('hoarding_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const weeklyPrice = selectedOption.dataset.weekly || '0.00';
        const monthlyPrice = selectedOption.dataset.monthly || '0.00';
        
        if (this.value) {
            document.getElementById('current-prices').style.display = 'block';
            document.getElementById('current-weekly').textContent = parseFloat(weeklyPrice).toFixed(2);
            document.getElementById('current-monthly').textContent = parseFloat(monthlyPrice).toFixed(2);
            
            // Pre-fill new prices with current values
            document.getElementById('weekly_price').value = weeklyPrice;
            document.getElementById('monthly_price').value = monthlyPrice;
        } else {
            document.getElementById('current-prices').style.display = 'none';
        }
    });

    // Trigger on page load if hoarding is pre-selected
    if (document.getElementById('hoarding_id').value) {
        document.getElementById('hoarding_id').dispatchEvent(new Event('change'));
    }
</script>
@endpush
@endsection

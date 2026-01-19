@extends('layouts.admin')

@section('title', 'Bulk Price Update')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="bi bi-collection me-2"></i>Bulk Price Update</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.price-updates.bulk.store') }}" method="POST" id="bulkUpdateForm">
                @csrf
                
                <!-- Step 1: Filter Criteria -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">Step 1: Select Hoardings (Filter Criteria)</h5>
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label">Vendor</label>
                            <select class="form-select" name="vendor_id">
                                <option value="">All Vendors</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type">
                                <option value="">All Types</option>
                                @foreach($types as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" placeholder="e.g., Mumbai">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Area</label>
                            <input type="text" class="form-control" name="area" placeholder="e.g., Andheri">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Property Type</label>
                            <input type="text" class="form-control" name="property_type" placeholder="e.g., Billboard">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Min Price (₹)</label>
                            <input type="number" class="form-control" name="min_price" step="0.01" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Price (₹)</label>
                            <input type="number" class="form-control" name="max_price" step="0.01" min="0">
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary mt-3" id="previewBtn">
                        <i class="bi bi-search me-2"></i>Preview Matching Hoardings
                    </button>
                </div>

                <!-- Preview Results -->
                <div id="previewResults" style="display: none;" class="mb-4">
                    <div class="alert alert-success">
                        <strong>Found <span id="matchCount">0</span> matching hoardings</strong>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Vendor</th>
                                    <th>Current Monthly Price</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody id="previewTable"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Step 2: Update Method -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">Step 2: Define Price Update Method</h5>
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label">Update Method <span class="text-danger">*</span></label>
                            <select class="form-select @error('update_method') is-invalid @enderror" 
                                    name="update_method" 
                                    id="updateMethod" 
                                    required>
                                <option value="">Choose Method</option>
                                <option value="fixed">Set Fixed Price</option>
                                <option value="percentage">Increase/Decrease by %</option>
                                <option value="increment">Increase by Amount</option>
                                <option value="decrement">Decrease by Amount</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Value <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('update_value') is-invalid @enderror" 
                                   name="update_value" 
                                   step="0.01" 
                                   required>
                            <small class="text-muted" id="valueHelp">Enter value based on method</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Apply To <span class="text-danger">*</span></label>
                            <select class="form-select @error('price_type') is-invalid @enderror" 
                                    name="price_type" 
                                    required>
                                <option value="monthly">Monthly Price Only</option>
                                <option value="weekly">Weekly Price Only</option>
                                <option value="both">Both Prices</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Reason -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">Step 3: Add Reason (Optional)</h5>
                    <textarea class="form-control mt-2" 
                              name="reason" 
                              rows="3"
                              placeholder="Explain why these prices are being updated"></textarea>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.price-updates.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="bi bi-save me-2"></i>Apply Bulk Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let matchedCount = 0;

    // Update method help text
    document.getElementById('updateMethod').addEventListener('change', function() {
        const helpText = document.getElementById('valueHelp');
        const valueInput = document.querySelector('[name="update_value"]');
        
        switch(this.value) {
            case 'fixed':
                helpText.textContent = 'Enter the new fixed price (e.g., 5000)';
                break;
            case 'percentage':
                helpText.textContent = 'Enter percentage (e.g., 10 for +10%, -5 for -5%)';
                break;
            case 'increment':
                helpText.textContent = 'Enter amount to add (e.g., 500)';
                valueInput.min = 0;
                break;
            case 'decrement':
                helpText.textContent = 'Enter amount to subtract (e.g., 500)';
                valueInput.min = 0;
                break;
        }
    });

    // Preview matching hoardings
    document.getElementById('previewBtn').addEventListener('click', async function() {
        const form = document.getElementById('bulkUpdateForm');
        const formData = new FormData(form);
        
        try {
            const response = await fetch('{{ route('admin.price-updates.bulk.preview') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                matchedCount = data.count;
                document.getElementById('matchCount').textContent = data.count;
                
                const tbody = document.getElementById('previewTable');
                tbody.innerHTML = '';
                
                data.preview.forEach(hoarding => {
                    tbody.innerHTML += `
                        <tr>
                            <td><a href="/hoardings/${hoarding.id}" target="_blank">${hoarding.title}</a></td>
                            <td>${hoarding.vendor}</td>
                            <td>₹${parseFloat(hoarding.current_monthly_price).toFixed(2)}</td>
                            <td>${hoarding.address.substring(0, 50)}...</td>
                        </tr>
                    `;
                });
                
                document.getElementById('previewResults').style.display = 'block';
                document.getElementById('submitBtn').disabled = (matchedCount === 0);
            }
        } catch (error) {
            alert('Error fetching preview: ' + error.message);
        }
    });

    // Form validation
    document.getElementById('bulkUpdateForm').addEventListener('submit', function(e) {
        if (matchedCount === 0) {
            e.preventDefault();
            alert('Please preview matching hoardings first');
            return false;
        }
        
        if (!confirm(`Are you sure you want to update ${matchedCount} hoardings?`)) {
            e.preventDefault();
            return false;
        }
    });
</script>
@endpush
@endsection

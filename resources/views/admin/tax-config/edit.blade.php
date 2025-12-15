@extends('layouts.admin')

@section('title', 'Edit Tax Configuration')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Edit: {{ $taxConfig->name }}</h1>
                <a href="{{ route('admin.tax-config.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.tax-config.update', $taxConfig) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Config Info -->
                        <div class="alert alert-secondary">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Key:</strong> <code>{{ $taxConfig->key }}</code>
                                </div>
                                <div class="col-md-6">
                                    <strong>Type:</strong> 
                                    <span class="badge bg-primary">{{ strtoupper($taxConfig->config_type) }}</span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <strong>Data Type:</strong> 
                                    <span class="badge bg-info">{{ strtoupper($taxConfig->data_type) }}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Group:</strong> {{ $taxConfig->group ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="2">{{ old('description', $taxConfig->description) }}</textarea>
                            <small class="text-muted">Optional: Update the description for this configuration</small>
                        </div>

                        <!-- Value Input (Dynamic based on data type) -->
                        <div class="mb-3">
                            <label for="value" class="form-label">Value *</label>
                            
                            @if($taxConfig->data_type === 'boolean')
                                <select class="form-select @error('value') is-invalid @enderror" 
                                        id="value" 
                                        name="value" 
                                        required>
                                    <option value="1" {{ old('value', $taxConfig->getTypedValue()) ? 'selected' : '' }}>Yes / Enabled / True</option>
                                    <option value="0" {{ !old('value', $taxConfig->getTypedValue()) ? 'selected' : '' }}>No / Disabled / False</option>
                                </select>

                            @elseif($taxConfig->data_type === 'integer')
                                <input type="number" 
                                       class="form-control @error('value') is-invalid @enderror" 
                                       id="value" 
                                       name="value" 
                                       value="{{ old('value', $taxConfig->getTypedValue()) }}"
                                       step="1"
                                       required>
                                @if(str_contains($taxConfig->key, 'threshold') || str_contains($taxConfig->key, 'amount'))
                                    <small class="text-muted">Amount in paisa (e.g., 50000000 = ₹5 Crore)</small>
                                @endif

                            @elseif($taxConfig->data_type === 'float')
                                <input type="number" 
                                       class="form-control @error('value') is-invalid @enderror" 
                                       id="value" 
                                       name="value" 
                                       value="{{ old('value', $taxConfig->getTypedValue()) }}"
                                       step="0.01"
                                       required>
                                @if(str_contains($taxConfig->key, 'rate') || str_contains($taxConfig->key, 'percentage'))
                                    <small class="text-muted">Enter percentage (e.g., 18 for 18%)</small>
                                @endif

                            @elseif($taxConfig->data_type === 'array')
                                <textarea class="form-control @error('value') is-invalid @enderror font-monospace" 
                                          id="value" 
                                          name="value" 
                                          rows="5"
                                          required>{{ old('value', json_encode($taxConfig->getTypedValue(), JSON_PRETTY_PRINT)) }}</textarea>
                                <small class="text-muted">JSON array format. Example: ["invoice", "purchase_order"]</small>

                            @else
                                <input type="text" 
                                       class="form-control @error('value') is-invalid @enderror" 
                                       id="value" 
                                       name="value" 
                                       value="{{ old('value', $taxConfig->value) }}"
                                       required>
                            @endif

                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $taxConfig->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                                <br>
                                <small class="text-muted">Inactive configurations will be ignored in calculations</small>
                            </div>
                        </div>

                        <!-- Validation Rules (if any) -->
                        @if(!empty($taxConfig->validation_rules))
                        <div class="alert alert-info">
                            <strong>Validation Rules:</strong>
                            <pre class="mb-0">{{ json_encode($taxConfig->validation_rules, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        @endif

                        <!-- Help Text -->
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-info-circle"></i> Configuration Hints:</h6>
                            <ul class="mb-0 small">
                                @if(str_contains($taxConfig->key, 'gst'))
                                    <li>GST rates are typically 5%, 12%, 18%, or 28%</li>
                                    <li>Company GSTIN should be 15 characters (22AAAAA0000A1Z5 format)</li>
                                    <li>State codes: MH (Maharashtra), DL (Delhi), KA (Karnataka), etc.</li>
                                @endif
                                @if(str_contains($taxConfig->key, 'tcs'))
                                    <li>TCS threshold is ₹50 Lakh (Section 206C(1H))</li>
                                    <li>TCS rate is typically 0.1% for goods</li>
                                    <li>Applies to sale of goods exceeding threshold</li>
                                @endif
                                @if(str_contains($taxConfig->key, 'tds'))
                                    <li>TDS thresholds vary by section (194C, 194J, etc.)</li>
                                    <li>Common threshold: ₹30,000 for contractors</li>
                                    <li>Rates: 1-10% depending on transaction type</li>
                                @endif
                            </ul>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('admin.tax-config.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Current Value Display -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Current Configuration State</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Raw Value:</strong> <code>{{ $taxConfig->value }}</code>
                        </div>
                        <div class="col-md-6">
                            <strong>Typed Value:</strong> 
                            @if($taxConfig->data_type === 'boolean')
                                <span class="badge bg-{{ $taxConfig->getTypedValue() ? 'success' : 'secondary' }}">
                                    {{ $taxConfig->getTypedValue() ? 'TRUE' : 'FALSE' }}
                                </span>
                            @elseif($taxConfig->data_type === 'array')
                                <code>{{ json_encode($taxConfig->getTypedValue()) }}</code>
                            @else
                                <code>{{ $taxConfig->getTypedValue() }}</code>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// JSON validation for array type
@if($taxConfig->data_type === 'array')
document.querySelector('form').addEventListener('submit', function(e) {
    const textarea = document.getElementById('value');
    try {
        JSON.parse(textarea.value);
    } catch (error) {
        e.preventDefault();
        alert('Invalid JSON format. Please check the syntax.');
        textarea.focus();
    }
});
@endif
</script>
@endpush
@endsection

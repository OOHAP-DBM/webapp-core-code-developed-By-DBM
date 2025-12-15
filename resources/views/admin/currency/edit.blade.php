@extends('layouts.admin')

@section('title', 'Edit Currency')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Edit Currency: {{ $currency->code }}</h1>
                <a href="{{ route('admin.currency.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.currency.update', $currency) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Currency Code (Read-only) -->
                        <div class="alert alert-secondary">
                            <strong>Currency Code:</strong> {{ $currency->code }}
                            <br><small>Currency code cannot be changed after creation</small>
                        </div>

                        <!-- Basic Info -->
                        <h5 class="mb-3">Basic Information</h5>
                        <div class="mb-3">
                            <label for="name" class="form-label">Currency Name *</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $currency->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Symbol & Formatting -->
                        <h5 class="mb-3 mt-4">Symbol & Formatting</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="symbol" class="form-label">Symbol *</label>
                                    <input type="text" 
                                           class="form-control @error('symbol') is-invalid @enderror" 
                                           id="symbol" 
                                           name="symbol" 
                                           value="{{ old('symbol', $currency->symbol) }}"
                                           required>
                                    @error('symbol')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="symbol_position" class="form-label">Symbol Position *</label>
                                    <select class="form-select @error('symbol_position') is-invalid @enderror" 
                                            id="symbol_position" 
                                            name="symbol_position" 
                                            required>
                                        <option value="before" {{ old('symbol_position', $currency->symbol_position) === 'before' ? 'selected' : '' }}>Before</option>
                                        <option value="after" {{ old('symbol_position', $currency->symbol_position) === 'after' ? 'selected' : '' }}>After</option>
                                    </select>
                                    @error('symbol_position')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="decimal_places" class="form-label">Decimal Places *</label>
                                    <input type="number" 
                                           class="form-control @error('decimal_places') is-invalid @enderror" 
                                           id="decimal_places" 
                                           name="decimal_places" 
                                           value="{{ old('decimal_places', $currency->decimal_places) }}"
                                           min="0"
                                           max="4"
                                           required>
                                    @error('decimal_places')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="decimal_separator" class="form-label">Decimal Separator *</label>
                                    <select class="form-select @error('decimal_separator') is-invalid @enderror" 
                                            id="decimal_separator" 
                                            name="decimal_separator" 
                                            required>
                                        <option value="." {{ old('decimal_separator', $currency->decimal_separator) === '.' ? 'selected' : '' }}>Period (.)</option>
                                        <option value="," {{ old('decimal_separator', $currency->decimal_separator) === ',' ? 'selected' : '' }}>Comma (,)</option>
                                    </select>
                                    @error('decimal_separator')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="thousand_separator" class="form-label">Thousand Separator *</label>
                                    <select class="form-select @error('thousand_separator') is-invalid @enderror" 
                                            id="thousand_separator" 
                                            name="thousand_separator" 
                                            required>
                                        <option value="," {{ old('thousand_separator', $currency->thousand_separator) === ',' ? 'selected' : '' }}>Comma (,)</option>
                                        <option value="." {{ old('thousand_separator', $currency->thousand_separator) === '.' ? 'selected' : '' }}>Period (.)</option>
                                        <option value=" " {{ old('thousand_separator', $currency->thousand_separator) === ' ' ? 'selected' : '' }}>Space ( )</option>
                                    </select>
                                    @error('thousand_separator')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Exchange Rate -->
                        <h5 class="mb-3 mt-4">Exchange Rate</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="exchange_rate" class="form-label">Exchange Rate to INR *</label>
                                    <input type="number" 
                                           class="form-control @error('exchange_rate') is-invalid @enderror" 
                                           id="exchange_rate" 
                                           name="exchange_rate" 
                                           value="{{ old('exchange_rate', $currency->exchange_rate) }}"
                                           step="0.000001"
                                           min="0.000001"
                                           required>
                                    <small class="text-muted">1 {{ $currency->code }} = ? INR</small>
                                    @error('exchange_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="country_code" class="form-label">Country Code</label>
                                    <input type="text" 
                                           class="form-control @error('country_code') is-invalid @enderror" 
                                           id="country_code" 
                                           name="country_code" 
                                           value="{{ old('country_code', $currency->country_code) }}"
                                           maxlength="2"
                                           style="text-transform: uppercase">
                                    @error('country_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <h5 class="mb-3 mt-4">Status</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1"
                                           {{ old('is_active', $currency->is_active) ? 'checked' : '' }}
                                           {{ $currency->is_default ? 'disabled' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                    @if($currency->is_default)
                                        <br><small class="text-muted">Cannot deactivate default currency</small>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_default" 
                                           name="is_default" 
                                           value="1"
                                           {{ old('is_default', $currency->is_default) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_default">
                                        Default Currency
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="alert alert-info mt-4">
                            <h6>Current Format Preview:</h6>
                            <p class="mb-0">{{ $currency->format(1234.56) }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('admin.currency.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Currency
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

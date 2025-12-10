@extends('layouts.vendor')

@section('title', 'Edit Hoarding')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Edit Hoarding</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('vendor.hoarding.calendar', $hoarding->id) }}" class="btn btn-outline-success">
                        <i class="bi bi-calendar3 me-2"></i>View Calendar
                    </a>
                    <a href="{{ route('vendor.hoardings.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Edit Hoarding Details</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('vendor.hoardings.update', $hoarding->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                id="title" name="title" value="{{ old('title', $hoarding->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" name="description" rows="4">{{ old('description', $hoarding->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div class="mb-3">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Select Type</option>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ old('type', $hoarding->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                id="address" name="address" rows="2" required>{{ old('address', $hoarding->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Location Coordinates -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="lat" class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="number" step="0.0000001" class="form-control @error('lat') is-invalid @enderror" 
                                    id="lat" name="lat" value="{{ old('lat', $hoarding->lat) }}" required>
                                @error('lat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="lng" class="form-label">Longitude <span class="text-danger">*</span></label>
                                <input type="number" step="0.0000001" class="form-control @error('lng') is-invalid @enderror" 
                                    id="lng" name="lng" value="{{ old('lng', $hoarding->lng) }}" required>
                                @error('lng')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="monthly_price" class="form-label">Monthly Price (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('monthly_price') is-invalid @enderror" 
                                    id="monthly_price" name="monthly_price" value="{{ old('monthly_price', $hoarding->monthly_price) }}" required>
                                @error('monthly_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="weekly_price" class="form-label">Weekly Price (₹)</label>
                                <input type="number" step="0.01" class="form-control @error('weekly_price') is-invalid @enderror" 
                                    id="weekly_price" name="weekly_price" value="{{ old('weekly_price', $hoarding->weekly_price) }}">
                                @error('weekly_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Enable Weekly Booking -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enable_weekly_booking" 
                                    name="enable_weekly_booking" value="1" {{ old('enable_weekly_booking', $hoarding->enable_weekly_booking) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_weekly_booking">
                                    Enable Weekly Booking
                                </label>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                @foreach($statuses as $key => $label)
                                    @if($key !== 'suspended')
                                        <option value="{{ $key }}" {{ old('status', $hoarding->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Hoarding
                            </button>
                            <a href="{{ route('vendor.hoardings.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

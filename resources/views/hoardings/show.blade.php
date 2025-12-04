@extends('layouts.app')

@section('title', $hoarding->title)

@section('content')
<div class="container-fluid p-0">
    <!-- Hero Section with Image/Map Placeholder -->
    <div class="row g-0">
        <div class="col-12">
            <div class="position-relative" style="height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white">
                    <div class="text-center">
                        <i class="bi bi-geo-alt" style="font-size: 4rem; opacity: 0.5;"></i>
                        <p class="mt-2 opacity-75">Map Integration Coming Soon</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row g-4">
            <!-- Left Column - Hoarding Details -->
            <div class="col-lg-8">
                <!-- Back Button -->
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Back to Listings
                </a>

                <!-- Title and Status -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h1 class="display-5 fw-bold mb-0">{{ $hoarding->title }}</h1>
                    <span class="badge bg-success px-3 py-2">{{ $hoarding->status_label }}</span>
                </div>

                <!-- Meta Information -->
                <div class="d-flex gap-4 mb-4 text-muted">
                    <div>
                        <i class="bi bi-tag-fill"></i> {{ $hoarding->type_label }}
                    </div>
                    <div>
                        <i class="bi bi-person-fill"></i> {{ $hoarding->vendor->name }}
                    </div>
                    <div>
                        <i class="bi bi-clock-fill"></i> Listed {{ $hoarding->created_at->diffForHumans() }}
                    </div>
                </div>

                <!-- Description -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-info-circle text-primary"></i> Description
                        </h5>
                        <p class="card-text" style="white-space: pre-wrap;">{{ $hoarding->description ?? 'No description provided.' }}</p>
                    </div>
                </div>

                <!-- Location -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-geo-alt-fill text-danger"></i> Location
                        </h5>
                        <p class="mb-2"><strong>Address:</strong></p>
                        <p class="text-muted">{{ $hoarding->address }}</p>
                        <p class="mb-2"><strong>Coordinates:</strong></p>
                        <div class="d-flex gap-3">
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-compass"></i> Lat: {{ $hoarding->lat }}
                            </span>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-compass"></i> Lng: {{ $hoarding->lng }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Specifications -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-list-check text-success"></i> Specifications
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <p class="text-muted small mb-1">Hoarding Type</p>
                                    <p class="fw-bold mb-0">{{ $hoarding->type_label }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <p class="text-muted small mb-1">Booking Options</p>
                                    <p class="fw-bold mb-0">
                                        Monthly
                                        @if($hoarding->supportsWeeklyBooking())
                                            <span class="text-success">+ Weekly</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Booking Card -->
            <div class="col-lg-4">
                <div class="card shadow sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Pricing</h5>
                        
                        <!-- Monthly Price -->
                        <div class="mb-4">
                            <div class="d-flex align-items-baseline mb-2">
                                <h2 class="mb-0 text-primary">₹{{ number_format($hoarding->monthly_price, 2) }}</h2>
                                <span class="text-muted ms-2">/ month</span>
                            </div>
                            <p class="text-muted small mb-0">Monthly rental rate</p>
                        </div>

                        @if($hoarding->supportsWeeklyBooking())
                            <!-- Weekly Price -->
                            <div class="mb-4 pb-4 border-bottom">
                                <div class="d-flex align-items-baseline mb-2">
                                    <h3 class="mb-0 text-success">₹{{ number_format($hoarding->weekly_price, 2) }}</h3>
                                    <span class="text-muted ms-2">/ week</span>
                                </div>
                                <p class="text-muted small mb-0">Weekly rental option available</p>
                            </div>
                        @endif

                        <!-- Booking Button -->
                        <div class="d-grid gap-2 mb-3">
                            <button class="btn btn-primary btn-lg" onclick="alert('Booking functionality coming soon!')">
                                <i class="bi bi-calendar-check"></i> Book Now
                            </button>
                            <button class="btn btn-outline-secondary" onclick="alert('Inquiry feature coming soon!')">
                                <i class="bi bi-envelope"></i> Send Inquiry
                            </button>
                        </div>

                        <!-- Quick Info -->
                        <div class="border-top pt-3">
                            <p class="text-muted small mb-2"><i class="bi bi-shield-check"></i> Verified Listing</p>
                            <p class="text-muted small mb-2"><i class="bi bi-lightning"></i> Instant Booking Available</p>
                            <p class="text-muted small mb-0"><i class="bi bi-headset"></i> 24/7 Support</p>
                        </div>
                    </div>
                </div>

                <!-- Vendor Contact Card -->
                <div class="card shadow mt-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Contact Vendor</h6>
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <span class="fs-5 fw-bold">{{ strtoupper(substr($hoarding->vendor->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="fw-bold mb-0">{{ $hoarding->vendor->name }}</p>
                                <p class="text-muted small mb-0">Verified Vendor</p>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="mailto:{{ $hoarding->vendor->email }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-envelope"></i> Email
                            </a>
                            <a href="tel:{{ $hoarding->vendor->phone }}" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-telephone"></i> Call
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .sticky-top {
        z-index: 1020;
    }
</style>
@endsection

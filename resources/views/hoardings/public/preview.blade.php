@extends('layouts.app')

@section('title', 'Hoarding Preview')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="mb-3">{{ $hoarding->title }}</h2>

                    <!-- Hero Image -->
                    @if($hoarding->hero_image_url)
                    <div class="mb-4">
                        <img src="{{ $hoarding->hero_image_url }}" class="img-fluid rounded" alt="{{ $hoarding->title }}">
                    </div>
                    @endif

                    <!-- Basic Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Location</h5>
                            <p>{{ $hoarding->display_location }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Type</h5>
                            <p>{{ ucfirst($hoarding->hoarding_type) }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($hoarding->description)
                    <div class="mb-4">
                        <h5>About this hoarding</h5>
                        <p>{{ $hoarding->description }}</p>
                    </div>
                    @endif

                    <!-- Gallery -->
                    @if($hoarding->gallery_images)
                    <div class="mb-4">
                        <h5>Gallery</h5>
                        <div class="row g-3">
                            @foreach($hoarding->gallery_images as $image)
                            <div class="col-md-4">
                                <img src="{{ $image->getUrl() }}" class="img-fluid rounded" alt="Image">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Pricing -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Monthly Price</h5>
                            <p class="text-success"><strong>₹{{ number_format($hoarding->monthly_price, 2) }}</strong></p>
                        </div>
                        @if($hoarding->enable_weekly_booking)
                        <div class="col-md-6">
                            <h5>Weekly Packages</h5>
                            <ul class="list-unstyled">
                                @if($hoarding->weekly_price_1)
                                <li>Week 1: ₹{{ number_format($hoarding->weekly_price_1, 2) }}</li>
                                @endif
                                @if($hoarding->weekly_price_2)
                                <li>Week 2: ₹{{ number_format($hoarding->weekly_price_2, 2) }}</li>
                                @endif
                                @if($hoarding->weekly_price_3)
                                <li>Week 3: ₹{{ number_format($hoarding->weekly_price_3, 2) }}</li>
                                @endif
                            </ul>
                        </div>
                        @endif
                    </div>

                    <!-- Audience & Traffic -->
                    @if($hoarding->audience_types)
                    <div class="mb-4">
                        <h5>Audience Types</h5>
                        <p>{{ is_array($hoarding->audience_types) ? implode(', ', $hoarding->audience_types) : $hoarding->audience_types }}</p>
                    </div>
                    @endif

                    <!-- Visibility -->
                    <div class="mb-4">
                        <h5>Visibility</h5>
                        <p>
                            @if($hoarding->visibility_start && $hoarding->visibility_end)
                                {{ $hoarding->visibility_start }} - {{ $hoarding->visibility_end }}
                            @else
                                24/7
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <!-- Booking Section -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Interested?</h5>
                </div>
                <div class="card-body">
                    <p class="mb-4">Contact the vendor to book this hoarding</p>
                    <a href="mailto:{{ $hoarding->vendor->email }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-envelope"></i> Send Inquiry
                    </a>
                    <a href="tel:{{ $hoarding->vendor->phone }}" class="btn btn-success w-100">
                        <i class="fas fa-phone"></i> Call Vendor
                    </a>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="text-muted">Vendor</h6>
                    <p class="mb-3"><strong>{{ $hoarding->vendor->name }}</strong></p>

                    @if($hoarding->vendor->company_name)
                    <h6 class="text-muted">Company</h6>
                    <p class="mb-3">{{ $hoarding->vendor->company_name }}</p>
                    @endif

                    <div class="d-grid gap-2">
                        <a href="{{ route('hoarding.index') }}" class="btn btn-outline-primary btn-sm">
                            View All Hoardings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.staff')

@section('title', 'Upload POD')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Upload Proof of Delivery (POD)</h4>
                </div>
                
                <div class="card-body">
                    <!-- Booking Info -->
                    @if(isset($booking))
                    <div class="alert alert-info">
                        <h5>Booking: {{ $booking->booking_reference }}</h5>
                        <p class="mb-1"><strong>Hoarding:</strong> {{ $booking->hoarding->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Location:</strong> {{ $booking->hoarding->address ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Status:</strong> 
                            <span class="badge bg-{{ $booking->status === 'confirmed' ? 'warning' : 'info' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </p>
                    </div>
                    @endif

                    <!-- Upload Form -->
                    <form id="pod-upload-form" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- File Upload -->
                        <div class="mb-3">
                            <label for="file" class="form-label">Photo or Video <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="file" name="file" accept="image/jpeg,image/jpg,image/png,video/mp4,video/quicktime" required>
                            <div class="form-text">Accepted formats: JPG, PNG, MP4, MOV. Maximum size: 50MB</div>
                            
                            <!-- Preview -->
                            <div id="preview-container" class="mt-3" style="display: none;">
                                <img id="image-preview" class="img-fluid" style="max-height: 300px; display: none;">
                                <video id="video-preview" controls class="w-100" style="max-height: 300px; display: none;"></video>
                            </div>
                        </div>

                        <!-- GPS Location -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="latitude" name="latitude" step="0.000001" min="-90" max="90" required readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="longitude" class="form-label">Longitude <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="longitude" name="longitude" step="0.000001" min="-180" max="180" required readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-primary" id="get-location-btn">
                                <i class="bi bi-geo-alt"></i> Get Current Location
                            </button>
                            <span id="location-status" class="ms-2"></span>
                        </div>

                        <!-- GPS Accuracy -->
                        <div class="mb-3">
                            <label for="gps_accuracy" class="form-label">GPS Accuracy (meters)</label>
                            <input type="number" class="form-control" id="gps_accuracy" name="gps_accuracy" readonly>
                            <div class="form-text">Automatically captured from device</div>
                        </div>

                        <!-- Device Info -->
                        <div class="mb-3">
                            <label for="device_info" class="form-label">Device Information</label>
                            <input type="text" class="form-control" id="device_info" name="device_info" readonly>
                        </div>

                        <!-- Distance Info (shown after location capture) -->
                        <div id="distance-info" class="alert alert-secondary" style="display: none;">
                            <h6>Distance from Hoarding</h6>
                            <p class="mb-0">Distance: <strong><span id="calculated-distance">-</span> meters</strong></p>
                            <p class="mb-0 text-muted small">Maximum allowed: {{ config('pod.max_distance_meters', 100) }}m</p>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" disabled>
                                <i class="bi bi-cloud-upload"></i> Upload POD
                            </button>
                        </div>
                    </form>

                    <!-- Progress -->
                    <div id="upload-progress" class="mt-3" style="display: none;">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                        </div>
                        <p class="text-center mt-2 text-muted">Uploading...</p>
                    </div>
                </div>
            </div>

            <!-- Existing PODs -->
            @if(isset($existingPODs) && $existingPODs->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Uploaded PODs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Uploaded At</th>
                                    <th>Status</th>
                                    <th>Distance</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($existingPODs as $pod)
                                <tr>
                                    <td>{{ ucfirst($pod->type) }}</td>
                                    <td>{{ $pod->uploaded_at?->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $pod->status === 'approved' ? 'success' : ($pod->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($pod->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $pod->distance_from_hoarding }}m</td>
                                    <td>
                                        <a href="{{ $pod->proof_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
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

@push('scripts')
<script>
    let bookingId = {{ $booking->id ?? 'null' }};
    let hoardingLat = {{ $booking->hoarding->latitude ?? 'null' }};
    let hoardingLng = {{ $booking->hoarding->longitude ?? 'null' }};
    let userLat = null;
    let userLng = null;

    // Get device info
    $('#device_info').val(navigator.userAgent);

    // File preview
    $('#file').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const fileType = file.type;
            $('#preview-container').show();
            
            if (fileType.startsWith('image/')) {
                $('#image-preview').attr('src', URL.createObjectURL(file)).show();
                $('#video-preview').hide();
            } else if (fileType.startsWith('video/')) {
                $('#video-preview').attr('src', URL.createObjectURL(file)).show();
                $('#image-preview').hide();
            }
            
            checkFormReady();
        }
    });

    // Get current location
    $('#get-location-btn').on('click', function() {
        $('#location-status').html('<span class="spinner-border spinner-border-sm"></span> Getting location...');
        
        if (!navigator.geolocation) {
            $('#location-status').html('<span class="text-danger">Geolocation not supported</span>');
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                userLat = position.coords.latitude;
                userLng = position.coords.longitude;
                
                $('#latitude').val(userLat.toFixed(6));
                $('#longitude').val(userLng.toFixed(6));
                $('#gps_accuracy').val(position.coords.accuracy.toFixed(2));
                
                $('#location-status').html('<span class="text-success"><i class="bi bi-check-circle"></i> Location captured</span>');
                
                // Calculate distance
                if (hoardingLat && hoardingLng) {
                    const distance = calculateDistance(userLat, userLng, hoardingLat, hoardingLng);
                    $('#calculated-distance').text(distance.toFixed(0));
                    $('#distance-info').show();
                    
                    const maxDistance = {{ config('pod.max_distance_meters', 100) }};
                    if (distance > maxDistance) {
                        $('#distance-info').removeClass('alert-secondary alert-success').addClass('alert-danger');
                        $('#distance-info').append('<p class="mb-0 text-danger mt-2"><strong>Warning:</strong> You are too far from the hoarding location!</p>');
                    } else {
                        $('#distance-info').removeClass('alert-secondary alert-danger').addClass('alert-success');
                    }
                }
                
                checkFormReady();
            },
            function(error) {
                $('#location-status').html('<span class="text-danger">Failed: ' + error.message + '</span>');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });

    // Calculate distance using Haversine formula
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Earth radius in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    // Check if form is ready
    function checkFormReady() {
        const fileSelected = $('#file')[0].files.length > 0;
        const locationCaptured = $('#latitude').val() && $('#longitude').val();
        
        if (fileSelected && locationCaptured) {
            $('#submit-btn').prop('disabled', false);
        }
    }

    // Form submission
    $('#pod-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!bookingId) {
            alert('Booking ID not found');
            return;
        }
        
        const formData = new FormData(this);
        
        $('#submit-btn').prop('disabled', true);
        $('#upload-progress').show();
        
        $.ajax({
            url: `/api/v1/staff/bookings/${bookingId}/upload-pod`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                'Accept': 'application/json'
            },
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        $('.progress-bar').css('width', percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    alert('POD uploaded successfully! Waiting for vendor approval.');
                    window.location.reload();
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Failed to upload POD');
                $('#submit-btn').prop('disabled', false);
                $('#upload-progress').hide();
            }
        });
    });
</script>
@endpush

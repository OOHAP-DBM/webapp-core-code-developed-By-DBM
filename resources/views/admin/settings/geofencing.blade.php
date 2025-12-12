@extends('layouts.admin')

@section('title', 'Geo-Fencing Settings')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="bi bi-geo-alt-fill text-primary me-2"></i>Geo-Fencing Settings</h2>
                    <p class="text-muted mb-0">Configure location validation for mounting tasks and POD uploads</p>
                </div>
                <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Settings
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.settings.geofencing.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Primary Geo-Fence Settings -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="bi bi-bullseye me-2"></i>Primary Geo-Fence Validation</h5>
                    </div>
                    <div class="card-body">
                        <!-- Geofence Radius -->
                        <div class="mb-4">
                            <label for="geofence_radius" class="form-label fw-bold">
                                Geo-Fence Radius (meters)
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('geofence_radius') is-invalid @enderror" 
                                       id="geofence_radius" 
                                       name="geofence_radius" 
                                       value="{{ old('geofence_radius', $settings['pod.geofence_radius_meters'] ?? 100) }}" 
                                       min="10" 
                                       max="1000"
                                       required>
                                <span class="input-group-text">meters</span>
                            </div>
                            <div class="form-text">
                                Mounters must be within this distance from the hoarding to upload POD.
                                <br><strong>Recommended:</strong> 50-200 meters depending on site accessibility.
                            </div>
                            @error('geofence_radius')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div id="radius-visualization" class="mt-3">
                                <small class="text-muted d-block mb-1">Visual Reference:</small>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar" id="radius-bar" role="progressbar" style="width: 10%">
                                        <span id="radius-text">100m</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">10m (Very Strict)</small>
                                    <small class="text-muted">500m (Moderate)</small>
                                    <small class="text-muted">1000m (Lenient)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Strict Validation -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="strict_validation" 
                                       name="strict_validation" 
                                       value="1"
                                       {{ old('strict_validation', $settings['pod.strict_geofence_validation'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="strict_validation">
                                    Enable Strict Geo-Fence Validation
                                </label>
                            </div>
                            <div class="form-text">
                                If enabled, POD uploads outside the radius will be <strong class="text-danger">rejected</strong>.
                                <br>If disabled, distance is recorded for admin review but uploads are <strong class="text-success">allowed</strong>.
                            </div>
                        </div>

                        <!-- Require GPS Coordinates -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="require_gps" 
                                       name="require_gps" 
                                       value="1"
                                       {{ old('require_gps', $settings['pod.require_gps_coordinates'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="require_gps">
                                    Require GPS Coordinates
                                </label>
                            </div>
                            <div class="form-text">
                                Force mounters to capture GPS location before uploading POD.
                                <br><strong class="text-warning">Recommended:</strong> Keep enabled for location verification.
                            </div>
                        </div>

                        <!-- GPS Accuracy Threshold -->
                        <div class="mb-4">
                            <label for="gps_accuracy" class="form-label fw-bold">
                                Maximum GPS Accuracy (meters)
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="gps_accuracy" 
                                       name="gps_accuracy" 
                                       value="{{ old('gps_accuracy', $settings['pod.max_gps_accuracy_meters'] ?? 50) }}" 
                                       min="5" 
                                       max="200">
                                <span class="input-group-text">meters</span>
                            </div>
                            <div class="form-text">
                                POD uploads with GPS accuracy worse than this may be flagged.
                                <br><strong>Lower = More accurate.</strong> Typical mobile GPS accuracy: 5-30 meters.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Settings -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="bi bi-sliders me-2"></i>Advanced Settings</h5>
                    </div>
                    <div class="card-body">
                        <!-- Log Violations -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="log_violations" 
                                       name="log_violations" 
                                       value="1"
                                       {{ old('log_violations', $settings['pod.log_geofence_violations'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="log_violations">
                                    Log Geo-Fence Violations
                                </label>
                            </div>
                            <div class="form-text">
                                Record all validation failures for security audit and staff monitoring.
                            </div>
                        </div>

                        <!-- Show Distance to Mounter -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="show_distance" 
                                       name="show_distance" 
                                       value="1"
                                       {{ old('show_distance', $settings['pod.show_distance_to_mounter'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="show_distance">
                                    Show Distance to Mounter
                                </label>
                            </div>
                            <div class="form-text">
                                Display real-time distance when mounter captures GPS. Helps them move closer if needed.
                            </div>
                        </div>

                        <!-- Alert Threshold -->
                        <div class="mb-4">
                            <label for="alert_threshold" class="form-label fw-bold">
                                Alert Threshold (meters)
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="alert_threshold" 
                                       name="alert_threshold" 
                                       value="{{ old('alert_threshold', $settings['geofencing.alert_threshold_meters'] ?? 150) }}" 
                                       min="50" 
                                       max="500">
                                <span class="input-group-text">meters</span>
                            </div>
                            <div class="form-text">
                                Alert admin about suspicious POD uploads beyond this distance (even if within allowed radius).
                            </div>
                        </div>

                        <!-- Enable for Dismounting -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="enable_dismounting" 
                                       name="enable_dismounting" 
                                       value="1"
                                       {{ old('enable_dismounting', $settings['geofencing.enable_for_dismounting'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="enable_dismounting">
                                    Apply Geo-Fence for Dismounting
                                </label>
                            </div>
                            <div class="form-text">
                                Require location validation for campaign completion/dismounting proof as well.
                            </div>
                        </div>

                        <!-- Auto-Approve Within Radius -->
                        <div class="mb-0">
                            <label for="auto_approve_radius" class="form-label fw-bold">
                                Auto-Approve Within Radius (meters)
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="auto_approve_radius" 
                                       name="auto_approve_radius" 
                                       value="{{ old('auto_approve_radius', $settings['geofencing.auto_approve_within_radius'] ?? 50) }}" 
                                       min="0" 
                                       max="200">
                                <span class="input-group-text">meters</span>
                            </div>
                            <div class="form-text">
                                Auto-approve POD if mounter is within this distance. Set to 0 to disable auto-approval.
                                <br><strong class="text-info">Note:</strong> Only applies if GPS accuracy is good.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-check-circle me-2"></i>Save Geo-Fencing Settings
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Sidebar -->
            <div class="col-lg-4">
                <!-- Current Status -->
                <div class="card border-0 shadow-sm mb-4 bg-light">
                    <div class="card-header bg-transparent border-bottom-0">
                        <h6 class="mb-0"><i class="bi bi-info-circle text-info me-2"></i>Current Configuration</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <strong>Radius:</strong> 
                                <span class="badge bg-primary">{{ $settings['pod.geofence_radius_meters'] ?? 100 }}m</span>
                            </li>
                            <li class="mb-2">
                                <strong>Strict Mode:</strong> 
                                @if($settings['pod.strict_geofence_validation'] ?? true)
                                    <span class="badge bg-danger">Enabled</span>
                                @else
                                    <span class="badge bg-warning">Disabled</span>
                                @endif
                            </li>
                            <li class="mb-2">
                                <strong>GPS Required:</strong> 
                                @if($settings['pod.require_gps_coordinates'] ?? true)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </li>
                            <li class="mb-0">
                                <strong>Auto-Approve:</strong> 
                                @if(($settings['geofencing.auto_approve_within_radius'] ?? 50) > 0)
                                    <span class="badge bg-success">Within {{ $settings['geofencing.auto_approve_within_radius'] }}m</span>
                                @else
                                    <span class="badge bg-secondary">Disabled</span>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Best Practices -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0"><i class="bi bi-lightbulb text-warning me-2"></i>Best Practices</h6>
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0">
                            <li class="mb-2">Set radius based on site accessibility (urban: 50m, rural: 200m)</li>
                            <li class="mb-2">Always enable strict validation for high-value campaigns</li>
                            <li class="mb-2">Keep GPS accuracy threshold at 50m or less</li>
                            <li class="mb-2">Enable logging for compliance and dispute resolution</li>
                            <li class="mb-0">Review geo-fence violations monthly</li>
                        </ul>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0"><i class="bi bi-bar-chart text-success me-2"></i>Usage Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="display-6 text-primary">{{ $stats['total_pods'] ?? 0 }}</div>
                                <small class="text-muted">Total PODs</small>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="display-6 text-success">{{ $stats['approved_pods'] ?? 0 }}</div>
                                <small class="text-muted">Approved</small>
                            </div>
                            <div class="col-6">
                                <div class="display-6 text-danger">{{ $stats['violations'] ?? 0 }}</div>
                                <small class="text-muted">Violations</small>
                            </div>
                            <div class="col-6">
                                <div class="display-6 text-info">{{ $stats['avg_distance'] ?? 0 }}m</div>
                                <small class="text-muted">Avg Distance</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Update radius visualization
const radiusInput = document.getElementById('geofence_radius');
const radiusBar = document.getElementById('radius-bar');
const radiusText = document.getElementById('radius-text');

function updateRadiusVisualization() {
    const value = parseInt(radiusInput.value);
    const percentage = (value / 1000) * 100;
    radiusBar.style.width = percentage + '%';
    radiusText.textContent = value + 'm';
    
    // Change color based on strictness
    radiusBar.className = 'progress-bar';
    if (value < 75) {
        radiusBar.classList.add('bg-danger');
    } else if (value < 150) {
        radiusBar.classList.add('bg-warning');
    } else if (value < 300) {
        radiusBar.classList.add('bg-success');
    } else {
        radiusBar.classList.add('bg-info');
    }
}

radiusInput.addEventListener('input', updateRadiusVisualization);
updateRadiusVisualization();

// Reset form
function resetForm() {
    if (confirm('Reset all settings to current saved values?')) {
        location.reload();
    }
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const radius = parseInt(radiusInput.value);
    const strictMode = document.getElementById('strict_validation').checked;
    
    if (radius < 20 && strictMode) {
        if (!confirm('Warning: A radius below 20m with strict validation may cause many legitimate uploads to fail. Continue?')) {
            e.preventDefault();
        }
    }
    
    if (radius > 500) {
        if (!confirm('Warning: A radius above 500m may allow fraudulent uploads. Continue?')) {
            e.preventDefault();
        }
    }
});
</script>
@endpush
@endsection

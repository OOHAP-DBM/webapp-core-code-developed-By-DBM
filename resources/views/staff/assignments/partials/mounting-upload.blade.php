<!-- Mounter Upload Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-geo-alt text-success me-2"></i>Upload Proof of Display (POD)
        </h5>
    </div>
    <div class="card-body">
        @if($assignment->status === 'in_progress' || $assignment->status === 'pending')
            <form action="{{ route('staff.assignments.upload-proof', $assignment->id) }}" 
                  method="POST" enctype="multipart/form-data" id="mountingUploadForm">
                @csrf
                <input type="hidden" name="type" value="mounting">
                
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Mounting Guidelines:</strong> Take photos/videos from the hoarding location.
                    Include wide shots showing the entire hoarding and close-ups of details.
                    <strong>This triggers campaign start approval.</strong>
                </div>

                <!-- Location Verification -->
                <div class="mb-3">
                    <label class="form-label">Current Location</label>
                    <button type="button" class="btn btn-outline-primary w-100" onclick="getLocation()">
                        <i class="bi bi-geo-alt me-2"></i>Capture GPS Location
                    </button>
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <small class="text-muted" id="locationStatus">Location not captured yet</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">POD Photos (Mandatory) *</label>
                    <input type="file" class="form-control" name="pod_photos[]" 
                           multiple accept="image/*" required capture="camera">
                    <small class="text-muted">Upload at least 4 photos: Wide shot, close-up, left angle, right angle. Max 10MB each.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">POD Video (Optional)</label>
                    <input type="file" class="form-control" name="pod_video" 
                           accept="video/*" capture="camera">
                    <small class="text-muted">Upload a short video showing the installed hoarding. Max 50MB.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Installation Checklist</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="checklist[]" value="properly_mounted" required>
                        <label class="form-check-label">Material Properly Mounted</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="checklist[]" value="no_wrinkles" required>
                        <label class="form-check-label">No Wrinkles or Bubbles</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="checklist[]" value="edges_secured" required>
                        <label class="form-check-label">All Edges Secured</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="checklist[]" value="clean_visible" required>
                        <label class="form-check-label">Clean and Clearly Visible</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="checklist[]" value="lighting_checked">
                        <label class="form-check-label">Lighting Checked (if applicable)</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Installation Notes</label>
                    <textarea class="form-control" name="notes" rows="3" 
                              placeholder="Add notes about installation, weather conditions, visibility, etc."></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Installation Date & Time</label>
                        <input type="datetime-local" class="form-control" name="installation_datetime" 
                               value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Weather Conditions</label>
                        <select class="form-select" name="weather">
                            <option value="clear">Clear/Sunny</option>
                            <option value="cloudy">Cloudy</option>
                            <option value="rainy">Rainy</option>
                            <option value="windy">Windy</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Team Members Present</label>
                    <input type="text" class="form-control" name="team_members" 
                           placeholder="Names of mounting team members">
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="notify_all" checked>
                    <label class="form-check-label">
                        Notify vendor and customer about installation completion
                    </label>
                </div>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Once you submit this POD, the campaign will be marked as started 
                    and customer billing will begin.
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-upload me-2"></i>Submit POD & Start Campaign
                </button>
            </form>

            <div id="uploadProgress" class="mt-3" style="display: none;">
                <div class="d-flex justify-content-between mb-1">
                    <span>Uploading...</span>
                    <span id="progressPercent">0%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         id="progressBar" style="width: 0%"></div>
                </div>
            </div>
        @else
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Upload is not available for assignments with status: {{ ucfirst($assignment->status) }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function getLocation() {
    const statusEl = document.getElementById('locationStatus');
    
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }
    
    statusEl.textContent = 'Getting location...';
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
            statusEl.textContent = `Location captured: ${position.coords.latitude.toFixed(6)}, ${position.coords.longitude.toFixed(6)}`;
            statusEl.classList.add('text-success');
        },
        function(error) {
            alert('Unable to get location: ' + error.message);
            statusEl.textContent = 'Failed to capture location';
            statusEl.classList.add('text-danger');
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

document.getElementById('mountingUploadForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Check if location is captured
    if (!document.getElementById('latitude').value) {
        if (!confirm('GPS location not captured. Continue anyway?')) {
            return;
        }
    }
    
    const formData = new FormData(this);
    const progressDiv = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    
    progressDiv.style.display = 'block';
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('POD uploaded successfully! Campaign has been started.');
            window.location.reload();
        } else {
            alert(data.message || 'Upload failed');
            progressDiv.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Upload failed. Please try again.');
        progressDiv.style.display = 'none';
    });
});
</script>
@endpush

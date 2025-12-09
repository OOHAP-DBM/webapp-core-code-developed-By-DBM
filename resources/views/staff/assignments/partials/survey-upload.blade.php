<!-- Surveyor Upload Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-clipboard-check text-warning me-2"></i>Upload Survey Report
        </h5>
    </div>
    <div class="card-body">
        @if($assignment->status === 'in_progress' || $assignment->status === 'pending')
            <form action="{{ route('staff.assignments.upload-proof', $assignment->id) }}" 
                  method="POST" enctype="multipart/form-data" id="surveyUploadForm">
                @csrf
                <input type="hidden" name="type" value="survey">
                
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Survey Guidelines:</strong> Conduct thorough site inspection.
                    Document location, visibility, traffic, and any issues.
                </div>

                <!-- Location Details -->
                <div class="mb-3">
                    <label class="form-label">Survey Location</label>
                    <button type="button" class="btn btn-outline-primary w-100" onclick="getSurveyLocation()">
                        <i class="bi bi-geo-alt me-2"></i>Capture GPS Location
                    </button>
                    <input type="hidden" name="latitude" id="surveyLatitude">
                    <input type="hidden" name="longitude" id="surveyLongitude">
                    <small class="text-muted" id="surveyLocationStatus">Location not captured yet</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Site Photos *</label>
                    <input type="file" class="form-control" name="site_photos[]" 
                           multiple accept="image/*" required capture="camera">
                    <small class="text-muted">Upload photos from multiple angles. Minimum 5 photos required. Max 10MB each.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Survey Report Document</label>
                    <input type="file" class="form-control" name="survey_document" 
                           accept=".pdf,.doc,.docx">
                    <small class="text-muted">Upload detailed survey report (PDF or Word)</small>
                </div>

                <!-- Survey Assessment -->
                <div class="mb-3">
                    <label class="form-label">Site Assessment</label>
                    
                    <div class="mb-2">
                        <label class="small text-muted">Visibility Rating *</label>
                        <select class="form-select" name="visibility_rating" required>
                            <option value="">Select Rating</option>
                            <option value="excellent">Excellent - Clearly visible from all angles</option>
                            <option value="good">Good - Visible from most angles</option>
                            <option value="average">Average - Partially visible</option>
                            <option value="poor">Poor - Limited visibility</option>
                        </select>
                    </div>
                    
                    <div class="mb-2">
                        <label class="small text-muted">Traffic Density *</label>
                        <select class="form-select" name="traffic_density" required>
                            <option value="">Select Density</option>
                            <option value="very_high">Very High - Major traffic route</option>
                            <option value="high">High - Busy area</option>
                            <option value="medium">Medium - Moderate traffic</option>
                            <option value="low">Low - Light traffic</option>
                        </select>
                    </div>
                    
                    <div class="mb-2">
                        <label class="small text-muted">Site Condition *</label>
                        <select class="form-select" name="site_condition" required>
                            <option value="">Select Condition</option>
                            <option value="excellent">Excellent - No issues</option>
                            <option value="good">Good - Minor wear</option>
                            <option value="fair">Fair - Some damage</option>
                            <option value="poor">Poor - Requires repair</option>
                        </select>
                    </div>
                </div>

                <!-- Measurements -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Hoarding Width (feet)</label>
                        <input type="number" class="form-control" name="width" step="0.1" 
                               placeholder="Enter measured width">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hoarding Height (feet)</label>
                        <input type="number" class="form-control" name="height" step="0.1" 
                               placeholder="Enter measured height">
                    </div>
                </div>

                <!-- Surrounding Details -->
                <div class="mb-3">
                    <label class="form-label">Nearby Landmarks</label>
                    <textarea class="form-control" name="landmarks" rows="2" 
                              placeholder="List nearby prominent landmarks"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Competitor Hoardings</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_competitors" value="yes">
                        <label class="form-check-label">Yes - Competitors nearby</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_competitors" value="no" checked>
                        <label class="form-check-label">No - No competitors nearby</label>
                    </div>
                    <textarea class="form-control mt-2" name="competitor_details" rows="2" 
                              placeholder="Details about competitor hoardings (if any)"></textarea>
                </div>

                <!-- Issues and Recommendations -->
                <div class="mb-3">
                    <label class="form-label">Issues Found</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="issues[]" value="structural_damage">
                        <label class="form-check-label">Structural Damage</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="issues[]" value="poor_lighting">
                        <label class="form-check-label">Poor/No Lighting</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="issues[]" value="obstructed_view">
                        <label class="form-check-label">Obstructed View</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="issues[]" value="weather_damage">
                        <label class="form-check-label">Weather Damage</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="issues[]" value="access_difficulty">
                        <label class="form-check-label">Difficult Access</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Detailed Observations *</label>
                    <textarea class="form-control" name="observations" rows="4" required
                              placeholder="Provide detailed observations about the site, traffic patterns, visibility, and any other relevant information"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Recommendations</label>
                    <textarea class="form-control" name="recommendations" rows="3" 
                              placeholder="Suggest improvements or actions needed"></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Survey Date & Time</label>
                        <input type="datetime-local" class="form-control" name="survey_datetime" 
                               value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Weather During Survey</label>
                        <select class="form-select" name="weather">
                            <option value="clear">Clear/Sunny</option>
                            <option value="cloudy">Cloudy</option>
                            <option value="rainy">Rainy</option>
                            <option value="foggy">Foggy</option>
                        </select>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="notify_vendor" checked>
                    <label class="form-check-label">
                        Notify vendor about survey completion
                    </label>
                </div>

                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-upload me-2"></i>Submit Survey Report
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
function getSurveyLocation() {
    const statusEl = document.getElementById('surveyLocationStatus');
    
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }
    
    statusEl.textContent = 'Getting location...';
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            document.getElementById('surveyLatitude').value = position.coords.latitude;
            document.getElementById('surveyLongitude').value = position.coords.longitude;
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

document.getElementById('surveyUploadForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
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
            alert('Survey report uploaded successfully!');
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

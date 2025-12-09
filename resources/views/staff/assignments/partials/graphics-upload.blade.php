<!-- Graphics Designer Upload Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-palette text-primary me-2"></i>Upload Design Files
        </h5>
    </div>
    <div class="card-body">
        @if($assignment->status === 'in_progress' || $assignment->status === 'pending')
            <form action="{{ route('staff.assignments.upload-proof', $assignment->id) }}" 
                  method="POST" enctype="multipart/form-data" id="graphicsUploadForm">
                @csrf
                <input type="hidden" name="type" value="graphics">
                
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Graphics Guidelines:</strong> Upload design files in high resolution (minimum 300 DPI).
                    Accepted formats: AI, PSD, PDF, PNG, JPG
                </div>

                <div class="mb-3">
                    <label class="form-label">Design Files *</label>
                    <input type="file" class="form-control" name="design_files[]" 
                           multiple accept=".ai,.psd,.pdf,.png,.jpg,.jpeg" required>
                    <small class="text-muted">You can upload multiple files. Max 50MB per file.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Design Description</label>
                    <textarea class="form-control" name="description" rows="3" 
                              placeholder="Describe the design concept, colors used, etc."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Design Notes</label>
                    <textarea class="form-control" name="notes" rows="2" 
                              placeholder="Any special notes for vendor/customer"></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Design Dimensions</label>
                        <input type="text" class="form-control" name="dimensions" 
                               placeholder="e.g., 10ft x 20ft">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">File Format</label>
                        <select class="form-select" name="primary_format">
                            <option value="ai">Adobe Illustrator (.ai)</option>
                            <option value="psd">Photoshop (.psd)</option>
                            <option value="pdf">PDF (.pdf)</option>
                            <option value="png">PNG (.png)</option>
                        </select>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="notify_customer" checked>
                    <label class="form-check-label">
                        Notify customer about design upload
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload me-2"></i>Upload Design Files
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
document.getElementById('graphicsUploadForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const progressDiv = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    
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
            alert('Design files uploaded successfully!');
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

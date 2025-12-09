<!-- Printer Upload Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-printer text-info me-2"></i>Upload Printing Proof
        </h5>
    </div>
    <div class="card-body">
        @if($assignment->status === 'in_progress' || $assignment->status === 'pending')
            <form action="{{ route('staff.assignments.upload-proof', $assignment->id) }}" 
                  method="POST" enctype="multipart/form-data" id="printingUploadForm">
                @csrf
                <input type="hidden" name="type" value="printing">
                
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Printing Guidelines:</strong> Upload clear photos of the printed material.
                    Show quality, colors, and any defects if present.
                </div>

                <div class="mb-3">
                    <label class="form-label">Printed Material Photos *</label>
                    <input type="file" class="form-control" name="printing_photos[]" 
                           multiple accept="image/*" required capture="camera">
                    <small class="text-muted">Upload at least 3 photos showing different angles. Max 10MB per photo.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Print Quality Check</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="quality_checks[]" value="color_accuracy">
                        <label class="form-check-label">Color Accuracy Verified</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="quality_checks[]" value="no_defects">
                        <label class="form-check-label">No Defects or Smudges</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="quality_checks[]" value="correct_size">
                        <label class="form-check-label">Correct Size/Dimensions</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="quality_checks[]" value="proper_finish">
                        <label class="form-check-label">Proper Finish Applied</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Printing Notes</label>
                    <textarea class="form-control" name="notes" rows="3" 
                              placeholder="Add notes about print quality, issues, or special considerations"></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Material Type</label>
                        <select class="form-select" name="material_type">
                            <option value="vinyl">Vinyl</option>
                            <option value="flex">Flex</option>
                            <option value="canvas">Canvas</option>
                            <option value="fabric">Fabric</option>
                            <option value="paper">Paper</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Print Method</label>
                        <select class="form-select" name="print_method">
                            <option value="digital">Digital Printing</option>
                            <option value="offset">Offset Printing</option>
                            <option value="screen">Screen Printing</option>
                            <option value="large_format">Large Format</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Estimated Completion Date</label>
                    <input type="datetime-local" class="form-control" name="estimated_completion">
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="notify_vendor" checked>
                    <label class="form-check-label">
                        Notify vendor about printing completion
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload me-2"></i>Upload Printing Proof
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
document.getElementById('printingUploadForm')?.addEventListener('submit', function(e) {
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
            alert('Printing proof uploaded successfully!');
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

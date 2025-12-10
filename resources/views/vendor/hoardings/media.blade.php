@extends('layouts.vendor')

@section('title', 'Media Manager - ' . $hoarding->title)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Media Manager</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('vendor.hoardings.index') }}">Hoardings</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('vendor.hoardings.show', $hoarding->id) }}">{{ $hoarding->title }}</a></li>
                            <li class="breadcrumb-item active">Media Manager</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('vendor.hoardings.show', $hoarding->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Hoarding
                </a>
            </div>
        </div>
    </div>

    <!-- Media Statistics Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="mb-2">
                                <i class="bi bi-image text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="mb-1">{{ $stats['total_files'] }}</h4>
                            <p class="text-muted mb-0 small">Total Images</p>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <i class="bi bi-hdd text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="mb-1">{{ $stats['total_size_mb'] }} MB</h4>
                            <p class="text-muted mb-0 small">Total Size</p>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <i class="bi bi-images text-info" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="mb-1">{{ $stats['gallery_count'] }}</h4>
                            <p class="text-muted mb-0 small">Gallery Images</p>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <i class="bi bi-check-circle {{ $stats['has_hero_image'] ? 'text-success' : 'text-muted' }}" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="mb-1">{{ $stats['has_hero_image'] ? 'Yes' : 'No' }}</h4>
                            <p class="text-muted mb-0 small">Hero Image</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hero Image Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-star text-warning me-2"></i>Hero / Primary Image
                    </h5>
                    <small class="text-muted">Main display image for this hoarding (Max: 10MB, Auto-compressed)</small>
                </div>
                <div class="card-body">
                    @if($media['hero_image'])
                        <!-- Existing Hero Image -->
                        <div class="row">
                            <div class="col-md-6">
                                <img src="{{ $media['hero_image']->getUrl('preview') }}" alt="Hero Image" class="img-fluid rounded shadow-sm mb-3">
                            </div>
                            <div class="col-md-6">
                                <h6>Image Details</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Filename:</strong> {{ $media['hero_image']->file_name }}</li>
                                    <li><strong>Size:</strong> {{ round($media['hero_image']->size / 1024 / 1024, 2) }} MB</li>
                                    <li><strong>Uploaded:</strong> {{ $media['hero_image']->created_at->format('M d, Y h:i A') }}</li>
                                </ul>
                                <div class="mt-3">
                                    <a href="{{ $media['hero_image']->getUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                        <i class="bi bi-eye me-1"></i>View Full Size
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteImage('hero')">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Upload Form -->
                        <form id="heroUploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="file" class="form-control" id="heroImage" name="hero_image" accept="image/jpeg,image/png,image/jpg,image/webp" required>
                                    <small class="text-muted">Supported formats: JPEG, PNG, JPG, WEBP</small>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-upload me-2"></i>Upload Hero Image
                                    </button>
                                </div>
                            </div>
                            <div id="heroPreview" class="mt-3"></div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Night View Image Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-moon text-info me-2"></i>Night View Image
                    </h5>
                    <small class="text-muted">Night-time view of the hoarding (Max: 10MB)</small>
                </div>
                <div class="card-body">
                    @if($media['night_image'])
                        <!-- Existing Night Image -->
                        <div class="row">
                            <div class="col-md-6">
                                <img src="{{ $media['night_image']->getUrl('preview') }}" alt="Night Image" class="img-fluid rounded shadow-sm mb-3">
                            </div>
                            <div class="col-md-6">
                                <h6>Image Details</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Filename:</strong> {{ $media['night_image']->file_name }}</li>
                                    <li><strong>Size:</strong> {{ round($media['night_image']->size / 1024 / 1024, 2) }} MB</li>
                                    <li><strong>Uploaded:</strong> {{ $media['night_image']->created_at->format('M d, Y h:i A') }}</li>
                                </ul>
                                <div class="mt-3">
                                    <a href="{{ $media['night_image']->getUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                        <i class="bi bi-eye me-1"></i>View Full Size
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteImage('night')">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Upload Form -->
                        <form id="nightUploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="file" class="form-control" id="nightImage" name="night_image" accept="image/jpeg,image/png,image/jpg,image/webp">
                                    <small class="text-muted">Supported formats: JPEG, PNG, JPG, WEBP</small>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-info text-white w-100">
                                        <i class="bi bi-upload me-2"></i>Upload Night Image
                                    </button>
                                </div>
                            </div>
                            <div id="nightPreview" class="mt-3"></div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery / Angle Photos Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="bi bi-images text-success me-2"></i>Gallery / Angle Photos
                            </h5>
                            <small class="text-muted">Multiple images showing different angles (Max: 10 images, 10MB each)</small>
                        </div>
                        @if($media['gallery']->count() > 0)
                            <span class="badge bg-success">{{ $media['gallery']->count() }} images</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Upload Form -->
                    <form id="galleryUploadForm" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <input type="file" class="form-control" id="galleryImages" name="gallery_images[]" accept="image/jpeg,image/png,image/jpg,image/webp" multiple>
                                <small class="text-muted">Select multiple images (Ctrl/Cmd + Click)</small>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-upload me-2"></i>Upload Gallery Images
                                </button>
                            </div>
                        </div>
                        <div id="galleryPreview" class="mt-3"></div>
                    </form>

                    <!-- Existing Gallery Images -->
                    @if($media['gallery']->count() > 0)
                        <div class="row g-3" id="galleryContainer">
                            @foreach($media['gallery'] as $image)
                                <div class="col-md-3" data-media-id="{{ $image->id }}">
                                    <div class="card border">
                                        <img src="{{ $image->getUrl('thumb') }}" alt="Gallery Image" class="card-img-top">
                                        <div class="card-body p-2">
                                            <small class="d-block text-muted mb-2">{{ round($image->size / 1024 / 1024, 2) }} MB</small>
                                            <div class="btn-group btn-group-sm w-100">
                                                <a href="{{ $image->getUrl() }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteGalleryImage({{ $image->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>No gallery images uploaded yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Size Overlay Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-rulers text-danger me-2"></i>Size / Dimension Overlay
                    </h5>
                    <small class="text-muted">Dimension diagram or size specification image (Max: 5MB)</small>
                </div>
                <div class="card-body">
                    @if($media['size_overlay'])
                        <!-- Existing Size Overlay -->
                        <div class="row">
                            <div class="col-md-6">
                                <img src="{{ $media['size_overlay']->getUrl('thumb') }}" alt="Size Overlay" class="img-fluid rounded shadow-sm mb-3">
                            </div>
                            <div class="col-md-6">
                                <h6>Image Details</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Filename:</strong> {{ $media['size_overlay']->file_name }}</li>
                                    <li><strong>Size:</strong> {{ round($media['size_overlay']->size / 1024 / 1024, 2) }} MB</li>
                                    <li><strong>Uploaded:</strong> {{ $media['size_overlay']->created_at->format('M d, Y h:i A') }}</li>
                                </ul>
                                <div class="mt-3">
                                    <a href="{{ $media['size_overlay']->getUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                        <i class="bi bi-eye me-1"></i>View Full Size
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteImage('size_overlay')">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Upload Form -->
                        <form id="sizeOverlayUploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="file" class="form-control" id="sizeOverlay" name="size_overlay" accept="image/jpeg,image/png,image/jpg,image/webp,image/svg+xml">
                                    <small class="text-muted">Supported formats: JPEG, PNG, JPG, WEBP, SVG</small>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="bi bi-upload me-2"></i>Upload Size Overlay
                                    </button>
                                </div>
                            </div>
                            <div id="sizeOverlayPreview" class="mt-3"></div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Hero Image Upload
document.getElementById('heroUploadForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const fileInput = document.getElementById('heroImage');
    
    if (!fileInput.files[0]) {
        alert('Please select an image.');
        return;
    }
    
    formData.append('hero_image', fileInput.files[0]);
    
    try {
        const response = await fetch('{{ route("vendor.hoardings.media.upload-hero", $hoarding->id) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Upload failed: ' + error.message);
    }
});

// Night Image Upload
document.getElementById('nightUploadForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const fileInput = document.getElementById('nightImage');
    
    if (!fileInput.files[0]) {
        alert('Please select an image.');
        return;
    }
    
    formData.append('night_image', fileInput.files[0]);
    
    try {
        const response = await fetch('{{ route("vendor.hoardings.media.upload-night", $hoarding->id) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Upload failed: ' + error.message);
    }
});

// Gallery Upload
document.getElementById('galleryUploadForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const fileInput = document.getElementById('galleryImages');
    
    if (!fileInput.files.length) {
        alert('Please select at least one image.');
        return;
    }
    
    for (let file of fileInput.files) {
        formData.append('gallery_images[]', file);
    }
    
    try {
        const response = await fetch('{{ route("vendor.hoardings.media.upload-gallery", $hoarding->id) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Upload failed: ' + error.message);
    }
});

// Size Overlay Upload
document.getElementById('sizeOverlayUploadForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const fileInput = document.getElementById('sizeOverlay');
    
    if (!fileInput.files[0]) {
        alert('Please select an image.');
        return;
    }
    
    formData.append('size_overlay', fileInput.files[0]);
    
    try {
        const response = await fetch('{{ route("vendor.hoardings.media.upload-size-overlay", $hoarding->id) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Upload failed: ' + error.message);
    }
});

// Delete Image
async function deleteImage(type) {
    if (!confirm('Are you sure you want to delete this image?')) return;
    
    const routes = {
        hero: '{{ route("vendor.hoardings.media.delete-hero", $hoarding->id) }}',
        night: '{{ route("vendor.hoardings.media.delete-night", $hoarding->id) }}',
        size_overlay: '{{ route("vendor.hoardings.media.delete-size-overlay", $hoarding->id) }}'
    };
    
    try {
        const response = await fetch(routes[type], {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Delete failed: ' + error.message);
    }
}

// Delete Gallery Image
async function deleteGalleryImage(mediaId) {
    if (!confirm('Are you sure you want to delete this image?')) return;
    
    try {
        const response = await fetch('{{ route("vendor.hoardings.media.delete-gallery", ["hoarding" => $hoarding->id, "mediaId" => "__ID__"]) }}'.replace('__ID__', mediaId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Delete failed: ' + error.message);
    }
}
</script>
@endpush
@endsection

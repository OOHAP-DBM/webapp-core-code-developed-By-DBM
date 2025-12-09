@extends('layouts.admin')

@section('title', 'Edit Template - ' . $template->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="mb-4">
                <a href="{{ route('admin.notifications.templates.show', $template) }}" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <h2 class="mb-1">Edit Template</h2>
                <p class="text-muted mb-0">{{ $template->name }}</p>
            </div>

            <form method="POST" action="{{ route('admin.notifications.templates.update', $template) }}">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <!-- Basic Information -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $template->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="2">{{ old('description', $template->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Event Type <span class="text-danger">*</span></label>
                                        <select name="event_type" id="event_type" class="form-select @error('event_type') is-invalid @enderror" required>
                                            @foreach($eventTypes as $key => $label)
                                                <option value="{{ $key }}" {{ old('event_type', $template->event_type) === $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('event_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Channel <span class="text-danger">*</span></label>
                                        <select name="channel" id="channel" class="form-select @error('channel') is-invalid @enderror" required>
                                            @foreach($channels as $key => $label)
                                                <option value="{{ $key }}" {{ old('channel', $template->channel) === $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('channel')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Template Content -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Template Content</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3" id="subject_field">
                                    <label class="form-label">Subject <span class="text-danger" id="subject_required">*</span></label>
                                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" 
                                           value="{{ old('subject', $template->subject) }}">
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Only for email notifications</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Body (Plain Text) <span class="text-danger">*</span></label>
                                    <textarea name="body" class="form-control @error('body') is-invalid @enderror" 
                                              rows="6" required>{{ old('body', $template->body) }}</textarea>
                                    @error('body')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Use placeholders like {{otp_code}}, {{user_name}}, etc.</small>
                                </div>

                                <div class="mb-3" id="html_body_field">
                                    <label class="form-label">HTML Body (For Email)</label>
                                    <textarea name="html_body" class="form-control @error('html_body') is-invalid @enderror" 
                                              rows="8">{{ old('html_body', $template->html_body) }}</textarea>
                                    @error('html_body')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Optional HTML version for email</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-md-4">
                        <!-- Settings -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Priority</label>
                                    <input type="number" name="priority" class="form-control @error('priority') is-invalid @enderror" 
                                           value="{{ old('priority', $template->priority) }}" min="0">
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Higher priority templates are used first</small>
                                </div>

                                <div class="form-check form-switch">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                                           value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>

                                @if($template->is_system_default)
                                    <div class="alert alert-info mt-3 mb-0">
                                        <small><i class="fas fa-info-circle"></i> This is a system default template</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Available Placeholders -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0">Available Placeholders</h6>
                            </div>
                            <div class="card-body">
                                <div id="placeholders_list">
                                    @if($template->available_placeholders)
                                        <div class="list-group list-group-flush">
                                            @foreach($template->available_placeholders as $placeholder => $description)
                                                <div class="list-group-item px-0 py-2 border-0">
                                                    <code class="text-primary">{{ $placeholder }}</code>
                                                    <br><small class="text-muted">{{ $description }}</small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.notifications.templates.show', $template) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Template
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Placeholder data for each event type
const eventPlaceholders = @json(array_map(fn($event) => \App\Models\NotificationTemplate::getDefaultPlaceholders($event), array_keys($eventTypes)));

// Toggle subject/html fields based on channel
document.getElementById('channel').addEventListener('change', function() {
    const subjectField = document.getElementById('subject_field');
    const htmlField = document.getElementById('html_body_field');
    const subjectRequired = document.getElementById('subject_required');
    
    if (this.value === 'email') {
        subjectField.style.display = 'block';
        htmlField.style.display = 'block';
        subjectRequired.style.display = 'inline';
        document.querySelector('[name="subject"]').required = true;
    } else {
        subjectField.style.display = 'none';
        htmlField.style.display = 'none';
        subjectRequired.style.display = 'none';
        document.querySelector('[name="subject"]').required = false;
    }
});

// Show placeholders based on event type
document.getElementById('event_type').addEventListener('change', function() {
    const placeholdersList = document.getElementById('placeholders_list');
    const eventType = this.value;
    
    if (eventType && eventPlaceholders[eventType]) {
        let html = '<div class="list-group list-group-flush">';
        
        for (const [placeholder, description] of Object.entries(eventPlaceholders[eventType])) {
            html += `
                <div class="list-group-item px-0 py-2 border-0">
                    <code class="text-primary">${placeholder}</code>
                    <br><small class="text-muted">${description}</small>
                </div>
            `;
        }
        
        html += '</div>';
        placeholdersList.innerHTML = html;
    }
});

// Trigger on page load
document.getElementById('channel').dispatchEvent(new Event('change'));
</script>
@endsection

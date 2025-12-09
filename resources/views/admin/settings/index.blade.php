@extends('layouts.admin')

@section('title', 'Settings Management')

@section('content')
<style>
    .settings-tabs {
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 2rem;
    }
    .settings-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 1rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s;
    }
    .settings-tabs .nav-link:hover {
        color: #0d6efd;
        background: #f8f9fa;
        border-bottom-color: #dee2e6;
    }
    .settings-tabs .nav-link.active {
        color: #0d6efd;
        background: transparent;
        border-bottom-color: #0d6efd;
    }
    .setting-card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        margin-bottom: 1.5rem;
    }
    .setting-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        border: none;
        padding: 1.25rem;
    }
    .setting-group {
        background: white;
        border-radius: 0.5rem;
        padding: 2rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    .setting-item {
        padding: 1.5rem;
        border-bottom: 1px solid #e9ecef;
        transition: background 0.2s;
    }
    .setting-item:last-child {
        border-bottom: none;
    }
    .setting-item:hover {
        background: #f8f9fa;
    }
    .setting-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .setting-description {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 0.75rem;
    }
    .setting-input {
        border: 2px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 0.75rem;
        transition: all 0.2s;
    }
    .setting-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
    .badge-setting-type {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        font-weight: 500;
    }
    .save-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: transform 0.2s;
    }
    .save-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(102, 126, 234, 0.3);
    }
    .tab-icon {
        margin-right: 0.5rem;
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">⚙️ System Settings</h1>
            <p class="text-muted mb-0">Configure and manage all system settings</p>
        </div>
        <div class="btn-group">
            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-clockwise"></i> Clear Cache
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Validation Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Settings Navigation Tabs -->
    <ul class="nav nav-tabs settings-tabs" role="tablist">
        @foreach($groups as $groupKey => $groupLabel)
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeGroup === $groupKey ? 'active' : '' }}" 
               href="{{ route('admin.settings.index', ['group' => $groupKey]) }}">
                @switch($groupKey)
                    @case('general')
                        <i class="bi bi-gear tab-icon"></i>
                        @break
                    @case('booking')
                        <i class="bi bi-calendar-check tab-icon"></i>
                        @break
                    @case('payment')
                        <i class="bi bi-credit-card tab-icon"></i>
                        @break
                    @case('commission')
                        <i class="bi bi-percent tab-icon"></i>
                        @break
                    @case('notification')
                        <i class="bi bi-bell tab-icon"></i>
                        @break
                    @case('kyc')
                        <i class="bi bi-shield-check tab-icon"></i>
                        @break
                    @case('dooh')
                        <i class="bi bi-display tab-icon"></i>
                        @break
                    @case('automation')
                        <i class="bi bi-robot tab-icon"></i>
                        @break
                    @case('cancellation')
                        <i class="bi bi-x-circle tab-icon"></i>
                        @break
                    @case('refund')
                        <i class="bi bi-cash-coin tab-icon"></i>
                        @break
                @endswitch
                {{ $groupLabel }}
            </a>
        </li>
        @endforeach
    </ul>

    <!-- Settings Form -->
    @if($settings->isEmpty())
    <div class="setting-group text-center py-5">
        <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
        <h4 class="mt-3 text-muted">No Settings Found</h4>
        <p class="text-muted">There are no settings configured for this group yet.</p>
        <p class="text-muted small">Run the settings seeder to populate default values.</p>
    </div>
    @else
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="group" value="{{ $activeGroup }}">

        <div class="setting-group">
            @foreach($settings as $setting)
            <div class="setting-item">
                <div class="setting-label">
                    {{ ucwords(str_replace('_', ' ', str_replace($activeGroup . '.', '', $setting->key))) }}
                    <span class="badge badge-setting-type bg-secondary">{{ strtoupper($setting->type) }}</span>
                </div>
                
                @if($setting->description)
                <div class="setting-description">{{ $setting->description }}</div>
                @endif

                <div class="setting-control">
                    @if($setting->type === 'boolean')
                    <div class="form-check form-switch">
                        <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                        <input 
                            class="form-check-input" 
                            type="checkbox" 
                            id="setting_{{ $setting->key }}" 
                            name="settings[{{ $setting->key }}]" 
                            value="1"
                            {{ $setting->getTypedValue() ? 'checked' : '' }}
                            style="width: 3rem; height: 1.5rem; cursor: pointer;">
                        <label class="form-check-label ms-2" for="setting_{{ $setting->key }}" style="cursor: pointer;">
                            {{ $setting->getTypedValue() ? 'Enabled' : 'Disabled' }}
                        </label>
                    </div>
                    @elseif($setting->type === 'integer')
                    <input 
                        type="number" 
                        class="form-control setting-input" 
                        id="setting_{{ $setting->key }}" 
                        name="settings[{{ $setting->key }}]" 
                        value="{{ $setting->getTypedValue() }}"
                        step="1">
                    @elseif($setting->type === 'float')
                    <input 
                        type="number" 
                        class="form-control setting-input" 
                        id="setting_{{ $setting->key }}" 
                        name="settings[{{ $setting->key }}]" 
                        value="{{ $setting->getTypedValue() }}"
                        step="0.01">
                    @elseif(in_array($setting->type, ['json', 'array']))
                    <textarea 
                        class="form-control setting-input font-monospace" 
                        id="setting_{{ $setting->key }}" 
                        name="settings[{{ $setting->key }}]"
                        rows="5"
                        style="font-size: 0.875rem;">{{ is_array($setting->getTypedValue()) ? json_encode($setting->getTypedValue(), JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                    <small class="text-muted">JSON format required</small>
                    @else
                    <input 
                        type="text" 
                        class="form-control setting-input" 
                        id="setting_{{ $setting->key }}" 
                        name="settings[{{ $setting->key }}]" 
                        value="{{ $setting->getTypedValue() }}">
                    @endif
                </div>
            </div>
            @endforeach

            <!-- Submit Button -->
            <div class="d-flex justify-content-end mt-4 pt-3">
                <button type="submit" class="btn btn-primary save-btn">
                    <i class="bi bi-save me-2"></i> Save {{ $groups[$activeGroup] }}
                </button>
            </div>
        </div>
    </form>
    @endif
</div>

@push('scripts')
<script>
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Update checkbox label text on toggle
    document.querySelectorAll('.form-check-input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (label && label.classList.contains('form-check-label')) {
                label.textContent = this.checked ? 'Enabled' : 'Disabled';
            }
        });
    });

    // Validate JSON fields before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const jsonFields = document.querySelectorAll('textarea[name^="settings"][name$="]"]');
        let hasError = false;

        jsonFields.forEach(field => {
            const fieldName = field.getAttribute('name');
            // Check if this is a JSON/array field by looking for the badge
            const settingItem = field.closest('.setting-item');
            const badge = settingItem?.querySelector('.badge-setting-type');
            
            if (badge && (badge.textContent.includes('JSON') || badge.textContent.includes('ARRAY'))) {
                try {
                    JSON.parse(field.value);
                    field.classList.remove('is-invalid');
                } catch (error) {
                    hasError = true;
                    field.classList.add('is-invalid');
                    alert('Invalid JSON format in field: ' + fieldName);
                    e.preventDefault();
                }
            }
        });
    });
</script>
@endpush
@endsection

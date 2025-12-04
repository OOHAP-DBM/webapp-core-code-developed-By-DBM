@php
    $value = $setting->getTypedValue();
    $fieldName = "settings[{$setting->key}]";
@endphp

<div class="col-md-6">
    <div class="form-group">
        <label for="{{ $setting->key }}" class="form-label fw-semibold">
            {{ ucwords(str_replace('_', ' ', str_replace($setting->group . '_', '', $setting->key))) }}
            @if($setting->type === 'boolean')
                <span class="badge bg-secondary">Toggle</span>
            @elseif($setting->type === 'integer' || $setting->type === 'float')
                <span class="badge bg-info">Number</span>
            @endif
        </label>
        
        @if($setting->type === 'boolean')
            <!-- Boolean Toggle Switch -->
            <div class="form-check form-switch">
                <input 
                    type="hidden" 
                    name="{{ $fieldName }}" 
                    value="0"
                >
                <input 
                    type="checkbox" 
                    class="form-check-input" 
                    id="{{ $setting->key }}" 
                    name="{{ $fieldName }}" 
                    value="1"
                    {{ $value ? 'checked' : '' }}
                >
                <label class="form-check-label" for="{{ $setting->key }}">
                    {{ $value ? 'Enabled' : 'Disabled' }}
                </label>
            </div>
            
        @elseif($setting->type === 'integer')
            <!-- Integer Input -->
            <input 
                type="number" 
                class="form-control" 
                id="{{ $setting->key }}" 
                name="{{ $fieldName }}" 
                value="{{ $value }}"
                step="1"
            >
            
        @elseif($setting->type === 'float')
            <!-- Float Input -->
            <input 
                type="number" 
                class="form-control" 
                id="{{ $setting->key }}" 
                name="{{ $fieldName }}" 
                value="{{ $value }}"
                step="0.01"
            >
            
        @elseif($setting->type === 'json' || $setting->type === 'array')
            <!-- JSON/Array Textarea -->
            <textarea 
                class="form-control font-monospace" 
                id="{{ $setting->key }}" 
                name="{{ $fieldName }}" 
                rows="3"
            >{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value }}</textarea>
            
        @else
            <!-- String Input (default) -->
            <input 
                type="text" 
                class="form-control" 
                id="{{ $setting->key }}" 
                name="{{ $fieldName }}" 
                value="{{ $value }}"
            >
        @endif

        @if($setting->description)
            <small class="form-text text-muted d-block mt-1">
                <i class="bi bi-info-circle me-1"></i>{{ $setting->description }}
            </small>
        @endif
    </div>
</div>

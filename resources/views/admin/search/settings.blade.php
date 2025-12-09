<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Search Ranking Settings - Admin Panel</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
        }
        
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .form-group input[type="range"] {
            width: 100%;
        }
        
        .slider-value {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .weight-total {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            margin-top: 20px;
        }
        
        .weight-total.valid {
            background: #4CAF50;
            color: white;
        }
        
        .weight-total.invalid {
            background: #f44336;
            color: white;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e5e5e5;
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
        }
        
        .btn-danger:hover {
            background: #da190b;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .preview-box {
            background: #f9f9f9;
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .preview-title {
            font-size: 18px;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .preview-result {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
        }
        
        .score-display {
            font-size: 32px;
            font-weight: bold;
            color: #4CAF50;
            text-align: center;
            margin: 15px 0;
        }
        
        .breakdown {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 15px;
        }
        
        .breakdown-item {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
        
        .breakdown-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .breakdown-value {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ Search Ranking Settings</h1>
            <p>Configure how search results are ranked and displayed</p>
        </div>
        
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-error">
                    <ul style="margin-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form method="POST" action="{{ route('admin.search-settings.update') }}" id="settings-form">
                @csrf
                @method('PUT')
                
                <!-- Ranking Weights Section -->
                <div class="section">
                    <h2 class="section-title">📊 Ranking Factor Weights</h2>
                    <p style="color: #666; margin-bottom: 20px;">Adjust how much each factor influences the ranking score (must total 100)</p>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>
                                Distance Weight
                                <span class="slider-value" id="distance-weight-value">{{ old('distance_weight', $settings->distance_weight) }}</span>
                            </label>
                            <input type="range" name="distance_weight" id="distance-weight" min="0" max="100" value="{{ old('distance_weight', $settings->distance_weight) }}" oninput="updateWeights()">
                        </div>
                        
                        <div class="form-group">
                            <label>
                                Price Weight
                                <span class="slider-value" id="price-weight-value">{{ old('price_weight', $settings->price_weight) }}</span>
                            </label>
                            <input type="range" name="price_weight" id="price-weight" min="0" max="100" value="{{ old('price_weight', $settings->price_weight) }}" oninput="updateWeights()">
                        </div>
                        
                        <div class="form-group">
                            <label>
                                Availability Weight
                                <span class="slider-value" id="availability-weight-value">{{ old('availability_weight', $settings->availability_weight) }}</span>
                            </label>
                            <input type="range" name="availability_weight" id="availability-weight" min="0" max="100" value="{{ old('availability_weight', $settings->availability_weight) }}" oninput="updateWeights()">
                        </div>
                        
                        <div class="form-group">
                            <label>
                                Rating Weight
                                <span class="slider-value" id="rating-weight-value">{{ old('rating_weight', $settings->rating_weight) }}</span>
                            </label>
                            <input type="range" name="rating_weight" id="rating-weight" min="0" max="100" value="{{ old('rating_weight', $settings->rating_weight) }}" oninput="updateWeights()">
                        </div>
                        
                        <div class="form-group">
                            <label>
                                Popularity Weight
                                <span class="slider-value" id="popularity-weight-value">{{ old('popularity_weight', $settings->popularity_weight) }}</span>
                            </label>
                            <input type="range" name="popularity_weight" id="popularity-weight" min="0" max="100" value="{{ old('popularity_weight', $settings->popularity_weight) }}" oninput="updateWeights()">
                        </div>
                        
                        <div class="form-group">
                            <label>
                                Recency Weight
                                <span class="slider-value" id="recency-weight-value">{{ old('recency_weight', $settings->recency_weight) }}</span>
                            </label>
                            <input type="range" name="recency_weight" id="recency-weight" min="0" max="100" value="{{ old('recency_weight', $settings->recency_weight) }}" oninput="updateWeights()">
                        </div>
                    </div>
                    
                    <div class="weight-total" id="weight-total">
                        Total Weight: <strong id="total-value">100</strong> / 100
                    </div>
                </div>
                
                <!-- Boost Factors Section -->
                <div class="section">
                    <h2 class="section-title">🚀 Boost Factors</h2>
                    <p style="color: #666; margin-bottom: 20px;">Percentage boost applied to base score (multiplicative)</p>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Featured Boost (%)</label>
                            <input type="number" name="featured_boost" min="0" max="100" value="{{ old('featured_boost', $settings->featured_boost) }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Verified Vendor Boost (%)</label>
                            <input type="number" name="verified_vendor_boost" min="0" max="100" value="{{ old('verified_vendor_boost', $settings->verified_vendor_boost) }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Premium Boost (%)</label>
                            <input type="number" name="premium_boost" min="0" max="100" value="{{ old('premium_boost', $settings->premium_boost) }}">
                        </div>
                    </div>
                </div>
                
                <!-- Search Behavior Section -->
                <div class="section">
                    <h2 class="section-title">🔍 Search Behavior</h2>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Default Radius (km)</label>
                            <input type="number" name="default_radius_km" min="1" max="100" value="{{ old('default_radius_km', $settings->default_radius_km) }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Min Radius (km)</label>
                            <input type="number" name="min_radius_km" min="1" max="50" value="{{ old('min_radius_km', $settings->min_radius_km) }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Max Radius (km)</label>
                            <input type="number" name="max_radius_km" min="1" max="500" value="{{ old('max_radius_km', $settings->max_radius_km) }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Results Per Page</label>
                            <input type="number" name="results_per_page" min="10" max="100" value="{{ old('results_per_page', $settings->results_per_page) }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Max Results</label>
                            <input type="number" name="max_results" min="100" max="10000" value="{{ old('max_results', $settings->max_results) }}">
                        </div>
                    </div>
                </div>
                
                <!-- Map Settings Section -->
                <div class="section">
                    <h2 class="section-title">🗺️ Map Settings</h2>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Default Center Latitude</label>
                            <input type="number" name="default_center[lat]" step="0.000001" min="-90" max="90" value="{{ old('default_center.lat', $settings->default_center['lat']) }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Default Center Longitude</label>
                            <input type="number" name="default_center[lng]" step="0.000001" min="-180" max="180" value="{{ old('default_center.lng', $settings->default_center['lng']) }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Default Zoom Level (1-20)</label>
                            <input type="number" name="default_zoom_level" min="1" max="20" value="{{ old('default_zoom_level', $settings->default_zoom_level) }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Cluster Markers</label>
                            <select name="cluster_markers">
                                <option value="1" {{ old('cluster_markers', $settings->cluster_markers) ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !old('cluster_markers', $settings->cluster_markers) ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Cluster Radius (px)</label>
                            <input type="number" name="cluster_radius" min="10" max="200" value="{{ old('cluster_radius', $settings->cluster_radius) }}">
                        </div>
                    </div>
                </div>
                
                <!-- Autocomplete Settings -->
                <div class="section">
                    <h2 class="section-title">✨ Autocomplete Settings</h2>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Enable Autocomplete</label>
                            <select name="enable_autocomplete">
                                <option value="1" {{ old('enable_autocomplete', $settings->enable_autocomplete) ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !old('enable_autocomplete', $settings->enable_autocomplete) ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Min Characters</label>
                            <input type="number" name="autocomplete_min_chars" min="1" max="10" value="{{ old('autocomplete_min_chars', $settings->autocomplete_min_chars) }}">
                        </div>
                        
                        <div class="form-group">
                            <label>Max Results</label>
                            <input type="number" name="autocomplete_max_results" min="1" max="50" value="{{ old('autocomplete_max_results', $settings->autocomplete_max_results) }}">
                        </div>
                    </div>
                </div>
                
                <!-- Hidden fields for enabled_filters and filter_defaults -->
                <input type="hidden" name="enabled_filters[]" value="price">
                <input type="hidden" name="enabled_filters[]" value="type">
                <input type="hidden" name="enabled_filters[]" value="size">
                <input type="hidden" name="enabled_filters[]" value="availability">
                <input type="hidden" name="enabled_filters[]" value="rating">
                <input type="hidden" name="enabled_filters[]" value="city">
                
                <!-- Actions -->
                <div class="actions">
                    <button type="submit" class="btn btn-primary">💾 Save Settings</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.reload()">↩️ Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="resetToDefaults()">🔄 Reset to Defaults</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function updateWeights() {
            const weights = [
                'distance-weight',
                'price-weight',
                'availability-weight',
                'rating-weight',
                'popularity-weight',
                'recency-weight'
            ];
            
            let total = 0;
            weights.forEach(id => {
                const input = document.getElementById(id);
                const value = parseInt(input.value);
                document.getElementById(id + '-value').textContent = value;
                total += value;
            });
            
            const totalElement = document.getElementById('total-value');
            const containerElement = document.getElementById('weight-total');
            
            totalElement.textContent = total;
            
            if (total === 100) {
                containerElement.className = 'weight-total valid';
            } else {
                containerElement.className = 'weight-total invalid';
            }
        }
        
        function resetToDefaults() {
            if (confirm('Are you sure you want to reset all settings to defaults?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.search-settings.reset") }}';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
                
                form.appendChild(csrfInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            updateWeights();
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Map Search - OOH Advertising Platform</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            overflow: hidden;
        }
        
        #app-container {
            display: flex;
            height: 100vh;
        }
        
        /* Sidebar */
        #sidebar {
            width: 380px;
            background: white;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        #sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        #sidebar-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }
        
        /* Filters */
        #filters {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }
        
        .filter-group {
            margin-bottom: 20px;
        }
        
        .filter-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #4CAF50;
        }
        
        .range-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #4CAF50;
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover {
            background: #45a049;
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
            margin-top: 10px;
            width: 100%;
        }
        
        .btn-secondary:hover {
            background: #e5e5e5;
        }
        
        .btn-location {
            background: #2196F3;
            color: white;
            width: 100%;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-location:hover {
            background: #1976D2;
        }
        
        /* Results */
        #results {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
            display: none;
        }
        
        #results.visible {
            display: block;
        }
        
        .result-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .result-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .result-card h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 8px;
        }
        
        .result-meta {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .result-price {
            font-size: 18px;
            font-weight: bold;
            color: #4CAF50;
            margin-top: 10px;
        }
        
        .result-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 5px;
        }
        
        .badge-featured {
            background: #FFD700;
            color: #333;
        }
        
        .badge-premium {
            background: #9C27B0;
            color: white;
        }
        
        .badge-verified {
            background: #4CAF50;
            color: white;
        }
        
        /* Map */
        #map-container {
            flex: 1;
            position: relative;
        }
        
        #map {
            width: 100%;
            height: 100%;
        }
        
        /* Loading */
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #4CAF50;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error-message {
            background: #f44336;
            color: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        
        .success-message {
            background: #4CAF50;
            color: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        
        /* Radius Slider */
        .radius-slider {
            width: 100%;
        }
        
        .radius-value {
            font-size: 16px;
            font-weight: 600;
            color: #4CAF50;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div id="app-container">
        <!-- Sidebar -->
        <div id="sidebar">
            <div id="sidebar-header">
                <h1>🗺️ Map Search</h1>
                <p style="color: #666; font-size: 14px;">Find hoardings near you</p>
            </div>
            
            <!-- Filters Section -->
            <div id="filters">
                <button class="btn btn-location" onclick="detectLocation()">
                    📍 Use My Location
                </button>
                
                <div class="filter-group">
                    <label>Search Location</label>
                    <input type="text" id="search-location" placeholder="Enter city or address...">
                </div>
                
                <div class="filter-group">
                    <label>Search Radius: <span id="radius-value" class="radius-value">{{ $settings->default_radius_km }} km</span></label>
                    <input 
                        type="range" 
                        id="radius" 
                        class="radius-slider"
                        min="{{ $settings->min_radius_km }}" 
                        max="{{ $settings->max_radius_km }}" 
                        value="{{ $settings->default_radius_km }}"
                        oninput="updateRadiusLabel(this.value)"
                    >
                </div>
                
                <div class="filter-group">
                    <label>Price Range (₹/month)</label>
                    <div class="range-inputs">
                        <input type="number" id="min-price" placeholder="Min">
                        <input type="number" id="max-price" placeholder="Max">
                    </div>
                </div>
                
                <div class="filter-group">
                    <label>Property Type</label>
                    <select id="property-type">
                        <option value="">All Types</option>
                        <option value="billboard">Billboard</option>
                        <option value="hoarding">Hoarding</option>
                        <option value="bus_shelter">Bus Shelter</option>
                        <option value="digital">Digital Screen</option>
                        <option value="wall">Wall Painting</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Availability</label>
                    <select id="availability">
                        <option value="">All</option>
                        <option value="available">Available</option>
                        <option value="available_soon">Available Soon</option>
                        <option value="partially_available">Partially Available</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Minimum Rating</label>
                    <select id="min-rating">
                        <option value="">Any Rating</option>
                        <option value="4">4+ Stars</option>
                        <option value="3">3+ Stars</option>
                        <option value="2">2+ Stars</option>
                    </select>
                </div>
                
                <button class="btn btn-primary" onclick="searchHoardings()">
                    🔍 Search
                </button>
                
                <button class="btn btn-secondary" onclick="clearFilters()">
                    Clear Filters
                </button>
            </div>
            
            <!-- Results Section -->
            <div id="results">
                <h2 style="margin-bottom: 15px; font-size: 18px;">Search Results (<span id="result-count">0</span>)</h2>
                <div id="results-list"></div>
            </div>
        </div>
        
        <!-- Map Container -->
        <div id="map-container">
            <div id="map"></div>
        </div>
    </div>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    
    <script>
        // Initialize map
        let map;
        let markers = new L.MarkerClusterGroup({
            chunkedLoading: true,
            maxClusterRadius: {{ $settings->cluster_radius }}
        });
        let radiusCircle;
        let currentLocation = null;
        
        // Initialize map with default center
        function initMap() {
            const defaultCenter = {!! json_encode($settings->default_center) !!};
            map = L.map('map').setView([defaultCenter.lat, defaultCenter.lng], {{ $settings->default_zoom_level }});
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            map.addLayer(markers);
        }
        
        // Detect user's location
        function detectLocation() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser');
                return;
            }
            
            showLoading('Detecting your location...');
            
            navigator.geolocation.getCurrentPosition(
                position => {
                    currentLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    map.setView([currentLocation.lat, currentLocation.lng], 13);
                    
                    // Add marker for current location
                    L.marker([currentLocation.lat, currentLocation.lng], {
                        icon: L.divIcon({
                            className: 'current-location-marker',
                            html: '<div style="background: #2196F3; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>'
                        })
                    }).addTo(map).bindPopup('Your Location');
                    
                    hideLoading();
                    searchHoardings();
                },
                error => {
                    hideLoading();
                    alert('Unable to detect location: ' + error.message);
                }
            );
        }
        
        // Update radius label
        function updateRadiusLabel(value) {
            document.getElementById('radius-value').textContent = value + ' km';
            
            // Update radius circle on map
            if (radiusCircle) {
                map.removeLayer(radiusCircle);
            }
            
            if (currentLocation) {
                radiusCircle = L.circle([currentLocation.lat, currentLocation.lng], {
                    color: '#4CAF50',
                    fillColor: '#4CAF50',
                    fillOpacity: 0.1,
                    radius: value * 1000 // Convert km to meters
                }).addTo(map);
            }
        }
        
        // Search hoardings
        async function searchHoardings() {
            if (!currentLocation) {
                alert('Please enable location detection first');
                return;
            }
            
            showLoading('Searching hoardings...');
            
            const params = {
                latitude: currentLocation.lat,
                longitude: currentLocation.lng,
                radius_km: document.getElementById('radius').value,
                min_price: document.getElementById('min-price').value || null,
                max_price: document.getElementById('max-price').value || null,
                property_type: document.getElementById('property-type').value || null,
                availability: document.getElementById('availability').value || null,
                min_rating: document.getElementById('min-rating').value || null,
            };
            
            try {
                const response = await fetch('{{ route("api.map.search") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(params)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayResults(data.data);
                    displayMarkersOnMap(data.data);
                    
                    // Show results section
                    document.getElementById('results').classList.add('visible');
                    document.getElementById('result-count').textContent = data.meta.total;
                } else {
                    alert('Search failed');
                }
            } catch (error) {
                console.error('Search error:', error);
                alert('An error occurred during search');
            } finally {
                hideLoading();
            }
        }
        
        // Display results in sidebar
        function displayResults(results) {
            const resultsList = document.getElementById('results-list');
            resultsList.innerHTML = '';
            
            if (results.length === 0) {
                resultsList.innerHTML = '<p style="text-align: center; color: #666;">No hoardings found</p>';
                return;
            }
            
            results.forEach(hoarding => {
                const card = document.createElement('div');
                card.className = 'result-card';
                card.onclick = () => focusOnMarker(hoarding.latitude, hoarding.longitude);
                
                let badges = '';
                if (hoarding.is_featured) badges += '<span class="result-badge badge-featured">Featured</span>';
                if (hoarding.is_premium) badges += '<span class="result-badge badge-premium">Premium</span>';
                
                card.innerHTML = `
                    <h3>${hoarding.name || 'Hoarding #' + hoarding.id}</h3>
                    ${badges}
                    <div class="result-meta">📍 ${hoarding.city || hoarding.address}</div>
                    <div class="result-meta">📏 ${hoarding.distance_km ? hoarding.distance_km.toFixed(2) + ' km away' : ''}</div>
                    <div class="result-meta">⭐ Rating: ${hoarding.rating || 'N/A'}/5</div>
                    <div class="result-meta">📊 Score: ${hoarding.ranking_score ? hoarding.ranking_score.toFixed(2) : 'N/A'}</div>
                    <div class="result-price">₹${hoarding.price ? hoarding.price.toLocaleString() : 'N/A'}/month</div>
                `;
                
                resultsList.appendChild(card);
            });
        }
        
        // Display markers on map
        function displayMarkersOnMap(results) {
            markers.clearLayers();
            
            results.forEach(hoarding => {
                if (hoarding.latitude && hoarding.longitude) {
                    const marker = L.marker([hoarding.latitude, hoarding.longitude]);
                    
                    let badges = '';
                    if (hoarding.is_featured) badges += '<span style="background: #FFD700; color: #333; padding: 2px 6px; border-radius: 3px; font-size: 10px; margin-right: 4px;">Featured</span>';
                    if (hoarding.is_premium) badges += '<span style="background: #9C27B0; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px;">Premium</span>';
                    
                    marker.bindPopup(`
                        <div style="min-width: 200px;">
                            <h3 style="margin-bottom: 8px;">${hoarding.name || 'Hoarding #' + hoarding.id}</h3>
                            ${badges}
                            <p style="margin: 5px 0;"><strong>Price:</strong> ₹${hoarding.price ? hoarding.price.toLocaleString() : 'N/A'}/month</p>
                            <p style="margin: 5px 0;"><strong>Distance:</strong> ${hoarding.distance_km ? hoarding.distance_km.toFixed(2) + ' km' : 'N/A'}</p>
                            <p style="margin: 5px 0;"><strong>Rating:</strong> ${hoarding.rating || 'N/A'}/5</p>
                            <p style="margin: 5px 0;"><strong>Type:</strong> ${hoarding.type || 'N/A'}</p>
                        </div>
                    `);
                    
                    markers.addLayer(marker);
                }
            });
            
            // Fit map to markers
            if (results.length > 0) {
                map.fitBounds(markers.getBounds(), { padding: [50, 50] });
            }
        }
        
        // Focus on specific marker
        function focusOnMarker(lat, lng) {
            map.setView([lat, lng], 15);
        }
        
        // Clear filters
        function clearFilters() {
            document.getElementById('min-price').value = '';
            document.getElementById('max-price').value = '';
            document.getElementById('property-type').value = '';
            document.getElementById('availability').value = '';
            document.getElementById('min-rating').value = '';
            document.getElementById('radius').value = {{ $settings->default_radius_km }};
            updateRadiusLabel({{ $settings->default_radius_km }});
        }
        
        // Loading helpers
        function showLoading(message) {
            // Implement loading indicator if needed
            console.log(message);
        }
        
        function hideLoading() {
            // Hide loading indicator
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            initMap();
        });
    </script>
</body>
</html>

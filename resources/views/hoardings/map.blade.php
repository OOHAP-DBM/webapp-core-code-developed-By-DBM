@extends('layouts.app')

@section('title', 'Find Hoardings Near You')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet MarkerCluster CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

<style>
    #map {
        height: calc(100vh - 56px);
        width: 100%;
    }

    .map-sidebar {
        position: absolute;
        top: 20px;
        left: 20px;
        width: 300px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        padding: 20px;
    }

    .filter-group {
        margin-bottom: 15px;
    }

    .filter-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 5px;
        color: #374151;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    .map-controls {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }

    .map-controls button {
        display: block;
        width: 40px;
        height: 40px;
        margin-bottom: 10px;
        background: white;
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .map-controls button:hover {
        background: #f3f4f6;
    }

    /* Custom marker styles */
    .custom-marker {
        background-color: #3b82f6;
        border: 3px solid white;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }

    /* Preview Modal */
    .preview-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }

    .preview-modal.show {
        display: flex;
    }

    .preview-modal-content {
        background: white;
        border-radius: 16px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .preview-modal-header {
        position: relative;
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px 16px 0 0;
        padding: 20px;
        color: white;
    }

    .preview-modal-close {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .preview-modal-close:hover {
        background: rgba(255,255,255,0.3);
    }

    .preview-modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .preview-modal-type {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .preview-modal-body {
        padding: 24px;
    }

    .preview-info-row {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .preview-info-row:last-child {
        border-bottom: none;
    }

    .preview-info-icon {
        width: 40px;
        height: 40px;
        background: #f3f4f6;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        color: #6b7280;
    }

    .preview-info-content {
        flex: 1;
    }

    .preview-info-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .preview-info-value {
        font-size: 1rem;
        color: #111827;
        font-weight: 600;
    }

    .preview-modal-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    .preview-modal-actions a {
        flex: 1;
        text-align: center;
        padding: 12px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-primary-preview {
        background: #3b82f6;
        color: white;
    }

    .btn-primary-preview:hover {
        background: #2563eb;
    }

    .btn-secondary-preview {
        background: #f3f4f6;
        color: #374151;
    }

    .btn-secondary-preview:hover {
        background: #e5e7eb;
    }

    .loading-spinner {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 999;
    }

    @media (max-width: 768px) {
        .map-sidebar {
            width: 280px;
        }

        .preview-modal-content {
            width: 95%;
        }
    }
</style>
@endsection

@section('content')
<div id="map"></div>

<!-- Sidebar Filters -->
<div class="map-sidebar">
    <h5 class="mb-3" style="font-weight: 700; color: #111827;">Filter Hoardings</h5>
    
    <div class="filter-group">
        <label for="filterType">Type</label>
        <select id="filterType" class="form-select">
            <option value="">All Types</option>
            <option value="billboard">Billboard</option>
            <option value="digital">Digital Screen</option>
            <option value="transit">Transit Advertising</option>
            <option value="street_furniture">Street Furniture</option>
            <option value="wallscape">Wallscape</option>
            <option value="mobile">Mobile Billboard</option>
        </select>
    </div>

    <div class="filter-group">
        <label for="filterRadius">Search Radius (km)</label>
        <input type="number" id="filterRadius" class="form-control" value="10" min="1" max="100">
    </div>

    <button class="btn btn-primary w-100" onclick="applyFilters()">
        <i class="bi bi-search"></i> Apply Filters
    </button>

    <hr class="my-3">

    <div id="resultsCount" class="text-center text-muted" style="font-size: 0.875rem;">
        Loading...
    </div>
</div>

<!-- Map Controls -->
<div class="map-controls">
    <button onclick="locateMe()" title="My Location">
        <i class="bi bi-crosshair" style="font-size: 18px;"></i>
    </button>
    <button onclick="resetMap()" title="Reset View">
        <i class="bi bi-arrow-clockwise" style="font-size: 18px;"></i>
    </button>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="preview-modal" onclick="closeModal(event)">
    <div class="preview-modal-content" onclick="event.stopPropagation()">
        <div class="preview-modal-header">
            <button class="preview-modal-close" onclick="closeModal()">&times;</button>
            <div class="preview-modal-title" id="modalTitle">Loading...</div>
            <span class="preview-modal-type" id="modalType"></span>
        </div>
        <div class="preview-modal-body">
            <div class="preview-info-row">
                <div class="preview-info-icon">
                    <i class="bi bi-cash" style="font-size: 20px;"></i>
                </div>
                <div class="preview-info-content">
                    <div class="preview-info-label">Monthly Price</div>
                    <div class="preview-info-value" id="modalPrice">₹0</div>
                </div>
            </div>

            <div class="preview-info-row" id="weeklyPriceRow" style="display: none;">
                <div class="preview-info-icon">
                    <i class="bi bi-calendar-week" style="font-size: 20px;"></i>
                </div>
                <div class="preview-info-content">
                    <div class="preview-info-label">Weekly Price</div>
                    <div class="preview-info-value" id="modalWeeklyPrice">₹0</div>
                </div>
            </div>

            <div class="preview-info-row">
                <div class="preview-info-icon">
                    <i class="bi bi-geo-alt" style="font-size: 20px;"></i>
                </div>
                <div class="preview-info-content">
                    <div class="preview-info-label">Location</div>
                    <div class="preview-info-value" id="modalLocation">Lat, Lng</div>
                </div>
            </div>

            <div class="preview-modal-actions">
                <a href="#" id="modalViewDetails" class="btn-primary-preview">View Details</a>
                <a href="#" id="modalSendInquiry" class="btn-secondary-preview">Send Inquiry</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Leaflet MarkerCluster JS -->
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
let map;
let markerClusterGroup;
let currentFilters = {};
let userLocation = null;

// Initialize map
function initMap() {
    // Default center (Mumbai, India)
    const defaultCenter = [19.0760, 72.8777];
    const defaultZoom = 12;

    map = L.map('map').setView(defaultCenter, defaultZoom);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    // Initialize marker cluster group
    markerClusterGroup = L.markerClusterGroup({
        chunkedLoading: true,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true
    });

    map.addLayer(markerClusterGroup);

    // Try to get user location
    locateMe();

    // Load initial markers
    loadMarkers();

    // Update markers when map moves
    map.on('moveend', debounce(loadMarkers, 500));
}

// Load markers from API
function loadMarkers() {
    const bounds = map.getBounds();
    const bbox = `${bounds.getSouth()},${bounds.getWest()},${bounds.getNorth()},${bounds.getEast()}`;

    const params = new URLSearchParams({
        bbox: bbox,
        ...currentFilters
    });

    fetch(`/api/v1/hoardings/map-pins?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateMarkers(data.data);
                updateResultsCount(data.total);
            }
        })
        .catch(error => {
            console.error('Error loading markers:', error);
            updateResultsCount(0);
        });
}

// Update markers on map
function updateMarkers(hoardings) {
    markerClusterGroup.clearLayers();

    hoardings.forEach(hoarding => {
        const marker = L.marker([hoarding.lat, hoarding.lng], {
            icon: L.divIcon({
                className: 'custom-marker',
                html: `<i class="bi bi-geo-alt-fill"></i>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            })
        });

        marker.on('click', () => showPreview(hoarding));
        markerClusterGroup.addLayer(marker);
    });
}

// Show preview modal
function showPreview(hoarding) {
    document.getElementById('modalTitle').textContent = hoarding.title;
    document.getElementById('modalType').textContent = hoarding.type.replace('_', ' ');
    document.getElementById('modalPrice').textContent = `₹${parseFloat(hoarding.price).toLocaleString('en-IN')}`;
    document.getElementById('modalLocation').textContent = `${hoarding.lat.toFixed(4)}, ${hoarding.lng.toFixed(4)}`;

    // Weekly price
    if (hoarding.weekly_price) {
        document.getElementById('weeklyPriceRow').style.display = 'flex';
        document.getElementById('modalWeeklyPrice').textContent = `₹${parseFloat(hoarding.weekly_price).toLocaleString('en-IN')}`;
    } else {
        document.getElementById('weeklyPriceRow').style.display = 'none';
    }

    // Update links
    document.getElementById('modalViewDetails').href = `/hoardings/${hoarding.id}`;
    document.getElementById('modalSendInquiry').href = `/inquiry/create?hoarding_id=${hoarding.id}`;

    // Show modal
    document.getElementById('previewModal').classList.add('show');
}

// Close modal
function closeModal(event) {
    if (!event || event.target.id === 'previewModal') {
        document.getElementById('previewModal').classList.remove('show');
    }
}

// Apply filters
function applyFilters() {
    currentFilters = {};

    const type = document.getElementById('filterType').value;
    if (type) {
        currentFilters.type = type;
    }

    const radius = document.getElementById('filterRadius').value;
    if (radius && userLocation) {
        currentFilters.near = `${userLocation.lat},${userLocation.lng}`;
        currentFilters.radius = radius;
    }

    loadMarkers();
}

// Locate user
function locateMe() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                map.setView([userLocation.lat, userLocation.lng], 13);
                
                // Add user marker
                L.marker([userLocation.lat, userLocation.lng], {
                    icon: L.divIcon({
                        className: 'custom-marker',
                        html: `<i class="bi bi-person-fill"></i>`,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    })
                }).addTo(map).bindPopup('You are here').openPopup();
            },
            error => {
                console.error('Geolocation error:', error);
                alert('Unable to get your location. Please enable location services.');
            }
        );
    } else {
        alert('Geolocation is not supported by your browser.');
    }
}

// Reset map
function resetMap() {
    map.setView([19.0760, 72.8777], 12);
    currentFilters = {};
    document.getElementById('filterType').value = '';
    document.getElementById('filterRadius').value = '10';
    loadMarkers();
}

// Update results count
function updateResultsCount(count) {
    document.getElementById('resultsCount').textContent = `${count} hoarding${count !== 1 ? 's' : ''} found`;
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initMap);
</script>
@endsection

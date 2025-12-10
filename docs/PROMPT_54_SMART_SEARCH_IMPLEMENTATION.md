# PROMPT 54: Smart Search Algorithm Implementation

## 📋 Overview

**Implementation Date**: December 10, 2025  
**Status**: ✅ Completed  
**Laravel Version**: 10.x  
**Feature**: Multi-factor intelligent search with geolocation, advanced filtering, and relevance scoring

---

## 🎯 Objectives

Implement a smart search system that:
- Uses coordinates + radius filtering (Haversine formula)
- Filters by hoarding type, price range, vendor rating, and availability
- Returns results sorted by: **Relevance → Price Match → Visibility Score**
- Provides intelligent multi-factor relevance scoring (0-100%)
- Caches expensive queries for performance

---

## 🏗️ Architecture

### Core Components

```
┌─────────────────────────────────────────────────┐
│           Smart Search System                    │
├─────────────────────────────────────────────────┤
│                                                  │
│  ┌──────────────────────────────────────────┐  │
│  │   SmartSearchService (700+ lines)        │  │
│  │   - Multi-factor scoring algorithm        │  │
│  │   - Geolocation filtering                 │  │
│  │   - Tri-level sorting                     │  │
│  │   - Performance caching                   │  │
│  └──────────────────────────────────────────┘  │
│                    ↓                             │
│  ┌──────────────────────────────────────────┐  │
│  │   SearchController                        │  │
│  │   - Web view (customer.search)            │  │
│  │   - JSON API (/api/search)                │  │
│  │   - Filter metadata (/api/search/filters) │  │
│  └──────────────────────────────────────────┘  │
│                    ↓                             │
│  ┌──────────────────────────────────────────┐  │
│  │   Enhanced Search View                    │  │
│  │   - Location detection                    │  │
│  │   - Radius slider (1-100km)               │  │
│  │   - Vendor rating filter                  │  │
│  │   - Availability filter                   │  │
│  │   - Real-time results display             │  │
│  └──────────────────────────────────────────┘  │
│                                                  │
└─────────────────────────────────────────────────┘
```

---

## 🧮 Multi-Factor Scoring Algorithm

### Relevance Score Calculation

The system calculates a composite relevance score (0-100%) using 6 weighted factors:

```php
Relevance Score = (
    Distance Score      × 0.25   // 25% weight
  + Price Score         × 0.20   // 20% weight
  + Vendor Rating Score × 0.20   // 20% weight
  + Visibility Score    × 0.15   // 15% weight
  + Availability Score  × 0.10   // 10% weight
  + Text Relevance      × 0.10   // 10% weight
) = 0-100%
```

### 1. Distance Score (25%)

**Formula**: Linear decay from location center

```php
if (distance <= max_radius) {
    score = 100 - ((distance / max_radius) * 100)
} else {
    score = 0
}
```

**Example**:
- 0 km away → 100 points
- 5 km away (max 10km) → 50 points
- 10 km away (max 10km) → 0 points

### 2. Price Score (20%)

**Formula**: Prefers mid-range pricing within budget

```php
if (price within range) {
    if (price close to midpoint) {
        score = 100
    } else {
        score = 80 - penalty_for_extremes
    }
} else if (price below min) {
    score = 50  // Too cheap = suspicious
} else {
    score = 0   // Out of budget
}
```

**Example** (Budget: ₹5,000-₹15,000):
- ₹10,000 (midpoint) → 100 points
- ₹7,500 or ₹12,500 → 80 points
- ₹3,000 (too cheap) → 50 points
- ₹20,000 (over budget) → 0 points

### 3. Vendor Rating Score (20%)

**Formula**: 5-star rating to 0-100 scale

```php
score = (average_rating / 5.0) × 100
```

**Example**:
- 5.0 stars → 100 points
- 4.0 stars → 80 points
- 3.0 stars → 60 points
- No rating → 50 points (neutral)

### 4. Visibility Score (15%)

**Formula**: Composite of booking history, recency, and featured status

```php
booking_points = min(bookings_count × 5, 40)  // Max 40 pts
recency_points = based on days_since_update   // Max 30 pts
featured_points = is_featured ? 30 : 0        // 30 pts if featured

score = booking_points + recency_points + featured_points
```

**Example**:
- 8+ bookings + updated today + featured → 100 points
- 4 bookings + updated 10 days ago → 40 points
- New listing → 30 points

### 5. Availability Score (10%)

**Formula**: Based on booking status

```php
if (no active bookings) {
    score = 100  // Available now
} else if (bookings end within 30 days) {
    score = 70   // Available soon
} else if (bookings end within 90 days) {
    score = 40   // Medium-term booking
} else {
    score = 10   // Long-term booked
}
```

### 6. Text Relevance Score (10%)

**Formula**: Keyword matching across fields

```php
title_match = 40 points       // Most important
address_match = 30 points     // Location context
description_match = 20 points // Content relevance
vendor_name_match = 10 points // Brand recognition

score = sum of matched fields
```

---

## 🔄 Tri-Level Sorting

Results are sorted using a **three-tier cascade**:

```php
sortBy([
    ['relevance_score', 'desc'],    // Primary: Best match first
    ['price_score', 'desc'],        // Secondary: Better value
    ['visibility_score', 'desc']    // Tertiary: Popular choices
])
```

**Sorting Logic**:
1. **First**: Group by relevance (highest relevance first)
2. **Then**: Within same relevance, sort by price match
3. **Finally**: Within same price match, sort by visibility

**Example Ranking**:
```
1. Hoarding A: 95% relevance, 90% price, 85% visibility
2. Hoarding B: 95% relevance, 88% price, 90% visibility
3. Hoarding C: 88% relevance, 95% price, 92% visibility
4. Hoarding D: 88% relevance, 85% price, 88% visibility
```

---

## 🔍 Filter Parameters

### Request Parameters

| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| `search` | string | No | max:255 | Text search (title, address, description) |
| `latitude` | float | No* | -90 to 90 | User's latitude (*required with radius) |
| `longitude` | float | No* | -180 to 180 | User's longitude (*required with radius) |
| `radius` | integer | No | 1-100 | Search radius in kilometers (default: 10) |
| `types[]` | array | No | valid types | Hoarding types (billboard, digital, etc.) |
| `min_price` | integer | No | ≥0 | Minimum weekly price in ₹ |
| `max_price` | integer | No | ≥0 | Maximum weekly price in ₹ |
| `min_rating` | float | No | 0-5 | Minimum vendor rating (stars) |
| `availability` | string | No | available, available_soon, booked | Availability status |
| `sort` | string | No | relevance, price_low, price_high | Manual sort override |
| `per_page` | integer | No | 12-60 | Results per page (default: 12) |
| `page` | integer | No | ≥1 | Current page number |

### Available Hoarding Types

Types are dynamically fetched from database:
- `billboard`
- `digital_billboard`
- `unipole`
- `hoarding`
- `transit` (buses, metro)
- `kiosk`
- `street_furniture`

---

## 📊 API Endpoints

### 1. Web Search View

**Endpoint**: `GET /customer/search`  
**Returns**: Blade view with search results and filters

**Example**:
```
GET /customer/search?search=Mumbai&latitude=19.0760&longitude=72.8777&radius=5&types[]=billboard&min_price=5000&max_price=15000&min_rating=4
```

**View Data**:
```php
[
    'hoardings' => LengthAwarePaginator,
    'availableTypes' => ['billboard', 'digital_billboard', ...],
    'priceRange' => ['min' => 2000, 'max' => 50000, 'avg' => 12000],
    'searchResults' => [
        'filters_applied' => [...],
        'meta' => [
            'total' => 45,
            'page' => 1,
            'per_page' => 12
        ]
    ]
]
```

### 2. JSON Search API

**Endpoint**: `POST /customer/api/search`  
**Returns**: JSON response with scored results

**Request Body**:
```json
{
    "search": "Mumbai",
    "latitude": 19.0760,
    "longitude": 72.8777,
    "radius": 10,
    "types": ["billboard", "digital_billboard"],
    "min_price": 5000,
    "max_price": 15000,
    "min_rating": 4.0,
    "availability": "available",
    "per_page": 12,
    "page": 1
}
```

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 123,
            "title": "Prime Mumbai Billboard",
            "address": "Bandra West, Mumbai",
            "type": "billboard",
            "weekly_price": 12000,
            "relevance_score": 95.5,
            "distance_score": 100,
            "price_score": 85,
            "vendor_rating_score": 90,
            "visibility_score": 88,
            "availability_score": 100,
            "text_relevance_score": 70,
            "distance_km": 0.5,
            "vendor_avg_rating": 4.5,
            "booking_count": 12,
            "is_available": true
        }
    ],
    "meta": {
        "total": 45,
        "per_page": 12,
        "current_page": 1,
        "last_page": 4,
        "from": 1,
        "to": 12
    },
    "filters_applied": {
        "location": true,
        "radius": 10,
        "types": ["billboard", "digital_billboard"],
        "price_range": [5000, 15000],
        "min_rating": 4.0,
        "availability": "available"
    }
}
```

### 3. Filter Metadata API

**Endpoint**: `GET /customer/api/search/filters`  
**Returns**: Available filter options

**Response**:
```json
{
    "success": true,
    "data": {
        "types": [
            "billboard",
            "digital_billboard",
            "unipole",
            "hoarding"
        ],
        "price_range": {
            "min": 2000,
            "max": 50000,
            "avg": 12000
        },
        "availability_options": [
            "available",
            "available_soon",
            "booked"
        ]
    }
}
```

---

## ⚡ Performance Optimizations

### 1. Caching Strategy

```php
// Vendor ratings cached for 1 hour
Cache::remember("vendor_rating_{$vendorId}", 3600, function() {
    return Booking::where('vendor_id', $vendorId)
        ->whereNotNull('customer_rating')
        ->avg('customer_rating');
});

// Available types cached for 24 hours
Cache::remember('hoarding_available_types', 86400, function() {
    return Hoarding::distinct()->pluck('type');
});

// Price range cached for 1 hour
Cache::remember('hoarding_price_range', 3600, function() {
    return [
        'min' => Hoarding::min('weekly_price'),
        'max' => Hoarding::max('weekly_price'),
        'avg' => Hoarding::avg('weekly_price')
    ];
});
```

### 2. Database Indexes

**Required indexes** (add if not exists):

```sql
-- Geolocation queries
CREATE INDEX idx_hoardings_lat_lng ON hoardings(latitude, longitude);

-- Type filtering
CREATE INDEX idx_hoardings_type ON hoardings(type);

-- Price filtering
CREATE INDEX idx_hoardings_price ON hoardings(weekly_price, monthly_price);

-- Status filtering
CREATE INDEX idx_hoardings_status ON hoardings(status);

-- Vendor rating lookups
CREATE INDEX idx_bookings_vendor_rating ON bookings(vendor_id, customer_rating);

-- Availability checks
CREATE INDEX idx_bookings_dates ON bookings(hoarding_id, start_date, end_date);
```

### 3. Query Optimization

**Haversine Distance Calculation** (computed in SQL):

```sql
SELECT *,
    (6371 * acos(
        cos(radians(?)) * cos(radians(latitude)) *
        cos(radians(longitude) - radians(?)) +
        sin(radians(?)) * sin(radians(latitude))
    )) AS distance_km
FROM hoardings
HAVING distance_km <= ?
ORDER BY distance_km
```

**Benefits**:
- Single database query
- MySQL/PostgreSQL optimization
- No PHP distance calculations
- Efficient for large datasets

---

## 🎨 Frontend Features

### Location Detection

```javascript
// HTML5 Geolocation API
navigator.geolocation.getCurrentPosition(
    function(position) {
        latitude = position.coords.latitude;
        longitude = position.coords.longitude;
        // Auto-submit search form
    },
    function(error) {
        alert('Location detection failed');
    }
);
```

### Radius Slider

```html
<input type="range" name="radius" min="1" max="100" value="10">
<div class="radius-value">
    <span id="radiusValue">10</span> km
</div>
```

### Vendor Rating Filter

```html
<div class="rating-filter">
    <button class="rating-option">Any</button>
    <button class="rating-option">3+ ⭐</button>
    <button class="rating-option">3.5+ ⭐</button>
    <button class="rating-option">4+ ⭐</button>
    <button class="rating-option">4.5+ ⭐</button>
</div>
```

### Relevance Score Display

```html
<div class="score-badges">
    <span class="score-badge high">Relevance: 95%</span>
    <span class="score-badge">2.5 km away</span>
    <span class="score-badge">⭐ 4.5</span>
</div>
```

---

## 🧪 Testing Guide

### Manual Testing Scenarios

#### 1. Location-Based Search
```
1. Click "Use My Location" button
2. Allow browser location access
3. Set radius to 5km
4. Verify results show distance
5. Verify results sorted by distance + relevance
```

#### 2. Type Filter
```
1. Select "Billboard" checkbox
2. Select "Digital Billboard" checkbox
3. Apply filters
4. Verify only selected types appear
5. Clear filters and verify all types return
```

#### 3. Price Range
```
1. Set min_price: 5000
2. Set max_price: 15000
3. Apply filters
4. Verify all results within range
5. Verify price score reflected in relevance
```

#### 4. Vendor Rating
```
1. Select "4+ ⭐" rating
2. Apply filters
3. Verify all vendors have 4+ average rating
4. Verify rating score reflected in relevance
```

#### 5. Availability
```
1. Select "Available Now"
2. Apply filters
3. Verify results have no active bookings
4. Select "Available Soon" and verify bookings end soon
```

#### 6. Combined Filters
```
1. Use location + radius
2. Select types
3. Set price range
4. Set min rating
5. Set availability
6. Verify all filters applied
7. Verify relevance scores make sense
```

### API Testing (cURL)

```bash
# Test JSON API
curl -X POST http://127.0.0.1:8000/customer/api/search \
  -H "Content-Type: application/json" \
  -d '{
    "search": "Mumbai",
    "latitude": 19.0760,
    "longitude": 72.8777,
    "radius": 10,
    "types": ["billboard"],
    "min_price": 5000,
    "max_price": 15000,
    "min_rating": 4.0,
    "availability": "available"
  }'

# Test filter metadata
curl http://127.0.0.1:8000/customer/api/search/filters
```

### Performance Testing

```php
// Test with 1000+ hoardings
php artisan tinker

// Create test data
\App\Models\Hoarding::factory()->count(1000)->create();

// Benchmark search
$start = microtime(true);
$results = app(\App\Services\SmartSearchService::class)->search([
    'latitude' => 19.0760,
    'longitude' => 72.8777,
    'radius' => 10
]);
$duration = microtime(true) - $start;
echo "Search took: {$duration} seconds\n";
```

**Expected Performance**:
- < 500ms for 1,000 hoardings
- < 1s for 10,000 hoardings
- < 2s for 100,000 hoardings

---

## 📁 Files Modified

### Created Files (1)
1. **app/Services/SmartSearchService.php** (700+ lines)
   - Multi-factor scoring algorithm
   - Geolocation filtering
   - Vendor rating calculations
   - Caching layer

### Modified Files (3)
1. **app/Http/Controllers/Web/Customer/SearchController.php** (115→150+ lines)
   - SmartSearchService integration
   - API endpoints added
   - Enhanced validation

2. **resources/views/customer/search.blade.php** (353→500+ lines)
   - Location detection UI
   - Radius slider
   - Vendor rating filter
   - Availability filter
   - Relevance score display

3. **routes/web.php**
   - Added POST /customer/api/search
   - Added GET /customer/api/search/filters

### Database Changes (1)
1. **database/migrations/2025_12_10_000001_add_customer_rating_to_bookings_table.php**
   - Added `customer_rating` column (DECIMAL 3,2)
   - Added `customer_review` text column
   - Added `rated_at` timestamp
   - Added composite index for fast lookups

---

## 🔮 Future Enhancements

### Phase 2 Improvements

1. **Machine Learning Integration**
   ```
   - Click-through rate tracking
   - Conversion rate analysis
   - Personalized ranking per user
   - A/B testing of scoring weights
   ```

2. **Advanced Filters**
   ```
   - Footfall data (high/medium/low traffic)
   - Audience demographics
   - Nearby landmarks (schools, malls, stations)
   - Time-of-day availability
   - Seasonal pricing
   ```

3. **Map View Enhancement**
   ```
   - Google Maps/Leaflet integration
   - Cluster markers for nearby hoardings
   - Draw radius circle on map
   - Click marker to view details
   - Filter directly on map
   ```

4. **Save Search & Alerts**
   ```
   - Save search criteria
   - Email alerts for new matches
   - Price drop notifications
   - Availability change alerts
   ```

5. **Social Proof**
   ```
   - "12 people viewed this today"
   - "3 bookings in last month"
   - Verified vendor badges
   - Customer testimonials
   ```

---

## 🐛 Troubleshooting

### Common Issues

#### Issue: Location not detected
**Solution**: 
- Check browser permissions (chrome://settings/content/location)
- Must use HTTPS in production (HTTP allowed on localhost)
- Verify GPS/location services enabled on device

#### Issue: No results with location
**Solution**:
- Verify latitude/longitude are valid numbers
- Check radius is reasonable (1-100km)
- Ensure hoardings have latitude/longitude in database
- Check Haversine formula constants (Earth radius = 6371 km)

#### Issue: Relevance scores all 0
**Solution**:
- Verify bookings have customer_rating populated
- Run: `php artisan cache:clear`
- Check scoring weights add up to 1.0
- Ensure hoardings have required fields (price, type, status)

#### Issue: Slow search performance
**Solution**:
- Add database indexes (see Performance section)
- Verify caching enabled in config/cache.php
- Use Redis/Memcached instead of file cache
- Profile query with `DB::enableQueryLog()`

#### Issue: Filters not working
**Solution**:
- Check validation rules in SearchController
- Verify input names match controller parameters
- Inspect browser console for JavaScript errors
- Test API endpoint directly with cURL

---

## 📚 Code Examples

### Using SmartSearchService in Code

```php
use App\Services\SmartSearchService;

// Inject in controller
public function __construct(
    private SmartSearchService $smartSearch
) {}

// Basic search
$results = $this->smartSearch->search([
    'search' => 'Mumbai',
    'per_page' => 12
]);

// Location-based search
$results = $this->smartSearch->search([
    'latitude' => 19.0760,
    'longitude' => 72.8777,
    'radius' => 10,
    'types' => ['billboard', 'digital_billboard'],
    'min_price' => 5000,
    'max_price' => 15000,
    'min_rating' => 4.0,
    'availability' => 'available'
]);

// Access results
foreach ($results['results'] as $hoarding) {
    echo "Title: {$hoarding->title}\n";
    echo "Relevance: {$hoarding->relevance_score}%\n";
    echo "Distance: {$hoarding->distance_km} km\n";
    echo "Vendor Rating: {$hoarding->vendor_avg_rating}\n";
}

// Check metadata
$meta = $results['meta'];
echo "Found {$meta['total']} results\n";

// Check applied filters
$filters = $results['filters_applied'];
if ($filters['location']) {
    echo "Location filter applied\n";
}
```

### Testing Vendor Ratings

```php
use App\Models\Booking;

// Add sample ratings
Booking::where('vendor_id', 1)->update([
    'customer_rating' => 4.5,
    'rated_at' => now()
]);

Booking::where('vendor_id', 2)->update([
    'customer_rating' => 3.8,
    'rated_at' => now()
]);

// Clear cache and test
Cache::forget('vendor_rating_1');
Cache::forget('vendor_rating_2');

// Run search with min_rating filter
$results = app(SmartSearchService::class)->search([
    'min_rating' => 4.0
]);
```

---

## ✅ Completion Checklist

- [x] SmartSearchService created with 6-factor scoring
- [x] SearchController updated with validation
- [x] API endpoints added (JSON search + filter metadata)
- [x] Search view enhanced with smart filters
- [x] Location detection implemented
- [x] Radius slider added (1-100km)
- [x] Vendor rating filter added
- [x] Availability filter added
- [x] Relevance score display added
- [x] Distance display added
- [x] customer_rating column added to bookings
- [x] Migration run successfully
- [x] Caching implemented (vendor ratings, types, prices)
- [x] Tri-level sorting implemented
- [x] Documentation created

---

## 📞 Support

For questions or issues with the smart search implementation:

1. Check this documentation
2. Review SmartSearchService.php comments
3. Test API endpoints with sample data
4. Check Laravel logs: `storage/logs/laravel.log`
5. Enable query logging: `DB::enableQueryLog()`

---

**Document Version**: 1.0  
**Last Updated**: December 10, 2025  
**Author**: GitHub Copilot  
**Status**: Production Ready ✅

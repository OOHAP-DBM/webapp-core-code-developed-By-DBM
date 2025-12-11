# SEO Optimization Documentation

## Overview
The SEO Optimization system provides comprehensive search engine optimization for hoarding listings, including auto-generated metadata, structured data, dynamic sitemaps, social media optimization, and analytics tracking.

---

## Table of Contents
1. [Features Overview](#features-overview)
2. [Database Schema](#database-schema)
3. [Core Components](#core-components)
4. [Meta Tags & SEO](#meta-tags--seo)
5. [Structured Data](#structured-data)
6. [Sitemap Generation](#sitemap-generation)
7. [Analytics Tracking](#analytics-tracking)
8. [Social Sharing](#social-sharing)
9. [API Endpoints](#api-endpoints)
10. [Usage Examples](#usage-examples)

---

## Features Overview

### 1. Auto-Generated Metadata
- **Meta Title**: Optimized 70-character titles
- **Meta Description**: Compelling 160-character descriptions
- **Meta Keywords**: Relevant keyword arrays
- **SEO-Friendly Slugs**: Human-readable URLs

### 2. Structured Data (Schema.org)
- **Product Schema**: Hoarding as commercial product
- **BreadcrumbList Schema**: Navigation hierarchy
- **GeoCoordinates**: Location-based SEO
- **Offer Schema**: Pricing information

### 3. Social Media Optimization
- **Open Graph Tags**: 9 OG properties for Facebook
- **Twitter Cards**: Summary with large image
- **Social Sharing Buttons**: Facebook, Twitter, WhatsApp, Copy Link

### 4. Dynamic Sitemaps
- **Sitemap Index**: Main sitemap pointer
- **Static Pages**: Core site pages
- **Hoardings Sitemap**: All approved listings (up to 10,000)
- **Locations Sitemap**: City-based landing pages

### 5. Analytics & Tracking
- **Page Views**: Count and timestamp
- **UTM Parameters**: Campaign tracking
- **Device Detection**: Mobile/Tablet/Desktop
- **Geolocation**: Country and city
- **Referrer Tracking**: Traffic source analysis

### 6. Interactive Features
- **Leaflet.js Maps**: Interactive location display
- **Image Carousels**: Multiple hoarding photos
- **Breadcrumbs**: SEO-friendly navigation
- **Similar Hoardings**: Related listings

---

## Database Schema

### 1. Hoardings Table Enhancement
```sql
ALTER TABLE hoardings ADD:
- slug: VARCHAR(255) UNIQUE INDEX
- meta_title: VARCHAR(70)
- meta_description: TEXT
- meta_keywords: JSON
- og_image: VARCHAR(500)
- index_page: BOOLEAN (default true)
- view_count: INTEGER (default 0)
- last_viewed_at: TIMESTAMP

INDEXES:
- slug (unique)
- city, board_type (composite)
- view_count
```

**Purpose**: Store SEO metadata directly on hoarding records

### 2. Hoarding Page Views
**Purpose**: Detailed analytics tracking

```sql
CREATE TABLE hoarding_page_views (
    id: BIGINT PRIMARY KEY
    hoarding_id: FOREIGN KEY
    ip_address: VARCHAR(45)
    user_agent: TEXT
    referer: VARCHAR(500)
    utm_source: VARCHAR(100)
    utm_medium: VARCHAR(100)
    utm_campaign: VARCHAR(100)
    utm_term: VARCHAR(100)
    utm_content: VARCHAR(100)
    country: VARCHAR(2)
    city: VARCHAR(100)
    device_type: ENUM('mobile', 'tablet', 'desktop')
    user_id: FOREIGN KEY (nullable)
    viewed_at: TIMESTAMP
)

INDEXES:
- hoarding_id
- viewed_at
- device_type
- utm_campaign
- user_id
```

**Use Cases**:
- Track most viewed hoardings
- Analyze traffic sources (organic, paid, social)
- Device usage patterns
- Geographic popularity
- Campaign performance (UTM tracking)
- User behavior (logged-in vs anonymous)

### 3. Sitemap Entries
**Purpose**: Dynamic sitemap management

```sql
CREATE TABLE sitemap_entries (
    id: BIGINT PRIMARY KEY
    loc: VARCHAR(500) UNIQUE
    lastmod: TIMESTAMP
    changefreq: ENUM('always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never')
    priority: DECIMAL(2,1) (0.0 - 1.0)
    type: ENUM('hoarding', 'location', 'static')
    reference_id: BIGINT (nullable)
    is_active: BOOLEAN (default true)
)

INDEXES:
- loc (unique)
- type, is_active
- reference_id
```

**Features**:
- Automatically updated on hoarding approval/edit
- Priority calculation based on view count
- Change frequency based on update patterns
- Supports static pages, hoardings, location pages

### 4. Location Pages
**Purpose**: SEO landing pages for cities/areas

```sql
CREATE TABLE location_pages (
    id: BIGINT PRIMARY KEY
    slug: VARCHAR(255) UNIQUE
    city: VARCHAR(100) INDEX
    state: VARCHAR(100)
    area: VARCHAR(100) (nullable)
    meta_title: VARCHAR(70)
    meta_description: VARCHAR(160)
    meta_keywords: JSON
    content: TEXT (rich text)
    header_image: VARCHAR(500)
    highlights: JSON
    hoarding_count: INTEGER (default 0)
    min_price: DECIMAL
    max_price: DECIMAL
    view_count: INTEGER (default 0)
    is_published: BOOLEAN (default false)
)

INDEXES:
- slug (unique)
- city, state
- is_published
```

**Example**:
```
URL: /hoardings/bangalore
Title: "Billboard Hoardings in Bangalore - 150+ Locations Available"
Description: "Book premium billboard hoardings in Bangalore. Prices from ₹25,000/month. High-traffic locations across MG Road, Whitefield, and more."
```

### 5. Breadcrumb Configs
**Purpose**: Navigation hierarchy for SEO

```sql
CREATE TABLE breadcrumb_configs (
    id: BIGINT PRIMARY KEY
    route_name: VARCHAR(100) INDEX
    label: VARCHAR(100)
    parent_route: VARCHAR(100) (nullable)
    position: INTEGER
    params: JSON
    is_active: BOOLEAN (default true)
)
```

**Default Configurations**:
1. Home → /
2. Hoardings → /hoardings
3. City → /hoardings/{city}
4. Hoarding Detail → /hoardings/{slug}

**Schema.org Output**:
```json
{
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://oohapp.com"},
    {"@type": "ListItem", "position": 2, "name": "Hoardings", "item": "https://oohapp.com/hoardings"},
    {"@type": "ListItem", "position": 3, "name": "Bangalore"},
    {"@type": "ListItem", "position": 4, "name": "MG Road Billboard"}
  ]
}
```

---

## Core Components

### SEOService

**Location**: `app/Services/SEOService.php`

#### Main Methods

##### 1. `generateHoardingMetadata($hoarding)`
Complete SEO package generation.

**Returns**:
```php
[
    'slug' => 'mg-road-billboard-bangalore',
    'meta_title' => 'Billboard Hoarding at MG Road, Bangalore - 20x10m',
    'meta_description' => 'Book illuminated billboard hoarding...',
    'meta_keywords' => ['billboard', 'bangalore', 'mg road', 'hoarding', ...]
]
```

**Usage**:
```php
$seoService = app(SEOService::class);
$metadata = $seoService->generateHoardingMetadata($hoarding);
$hoarding->update($metadata);
```

##### 2. `generateSlug($hoarding)`
SEO-friendly URL slug generation.

**Format**: `{location-name}-{city}`

**Features**:
- Lowercase conversion
- Special character removal
- Space to hyphen conversion
- Uniqueness guarantee (appends -2, -3, etc. if duplicate)

**Examples**:
```
"MG Road, Bangalore" → "mg-road-bangalore"
"Times Square, Mumbai" → "times-square-mumbai"
"Park Street @ Kolkata" → "park-street-kolkata"
```

##### 3. `generateMetaTitle($hoarding)`
Optimized title tag (70 character limit).

**Format**: `{Type} Hoarding at {Location}, {City} - {Width}x{Height}m`

**Examples**:
```
"Billboard Hoarding at MG Road, Bangalore - 20x10m"
"Unipole Hoarding at Marine Drive, Mumbai - 15x8m"
"Digital Hoarding at Connaught Place, Delhi - 12x6m"
```

**SEO Best Practices**:
- Primary keyword at start (hoarding type)
- Location specificity (area + city)
- Unique identifier (size)
- Under 70 characters for full display in SERPs

##### 4. `generateMetaDescription($hoarding)`
Compelling description (160 character target).

**Includes**:
- Hoarding type
- Location details
- Dimensions
- Pricing
- Special features (illuminated, traffic density)
- Call to action

**Example**:
```
"Book illuminated billboard hoarding in MG Road, Bangalore. Size: 20x10m. 
Price: ₹50,000/month. High traffic area with 100,000+ daily views. 
Premium location for brand visibility."
```

##### 5. `generateKeywords($hoarding)`
Relevant keyword array (5-10 keywords).

**Keyword Types**:
1. Board type (billboard, unipole, gantry, etc.)
2. City name
3. Specific location
4. State
5. Illumination (lit/unlit)
6. Traffic density (high/medium)
7. Generic terms (hoarding, outdoor advertising, OOH)

**Example**:
```php
[
    'billboard hoarding',
    'bangalore',
    'mg road',
    'karnataka',
    'illuminated hoarding',
    'high traffic',
    'outdoor advertising'
]
```

##### 6. `generateStructuredData($hoarding)`
Schema.org Product markup (JSON-LD).

**Schema Type**: Product

**Properties**:
```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Billboard Hoarding at MG Road, Bangalore",
  "description": "Premium billboard hoarding...",
  "image": "https://oohapp.com/storage/hoardings/image.jpg",
  "brand": {
    "@type": "Brand",
    "name": "ABC Advertising Pvt Ltd"
  },
  "offers": {
    "@type": "Offer",
    "price": "50000",
    "priceCurrency": "INR",
    "availability": "https://schema.org/InStock"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "12.9716",
    "longitude": "77.5946"
  },
  "additionalProperty": [
    {"@type": "PropertyValue", "name": "Width", "value": "20m"},
    {"@type": "PropertyValue", "name": "Height", "value": "10m"},
    {"@type": "PropertyValue", "name": "Location", "value": "MG Road, Bangalore"},
    {"@type": "PropertyValue", "name": "Illuminated", "value": "Yes"}
  ]
}
```

**SEO Benefits**:
- Rich snippets in Google search
- Price display in results
- Location display with map
- Increased click-through rates

##### 7. `generateOpenGraphData($hoarding)`
Facebook Open Graph tags.

**Returns**:
```php
[
    'og:title' => 'Billboard Hoarding at MG Road, Bangalore - 20x10m',
    'og:description' => 'Book illuminated billboard...',
    'og:image' => 'https://oohapp.com/storage/hoardings/image.jpg',
    'og:url' => 'https://oohapp.com/hoardings/mg-road-bangalore',
    'og:type' => 'product',
    'og:site_name' => 'OohApp',
    'og:locale' => 'en_IN',
    'product:price:amount' => '50000',
    'product:price:currency' => 'INR'
]
```

**Usage**: Powers social media previews when links are shared

##### 8. `generateTwitterCardData($hoarding)`
Twitter Card metadata.

**Returns**:
```php
[
    'twitter:card' => 'summary_large_image',
    'twitter:title' => 'Billboard Hoarding at MG Road, Bangalore - 20x10m',
    'twitter:description' => 'Book illuminated billboard...',
    'twitter:image' => 'https://oohapp.com/storage/hoardings/image.jpg'
]
```

**Card Type**: `summary_large_image`
- Large banner image
- Title and description
- Clickable to website

##### 9. `trackPageView($hoarding, $additionalData = [])`
Record detailed page view analytics.

**Parameters**:
```php
$additionalData = [
    'utm_source' => 'google',
    'utm_medium' => 'cpc',
    'utm_campaign' => 'summer_sale',
    'utm_term' => 'bangalore hoardings',
    'utm_content' => 'ad_variant_a'
];
```

**Automatically Captures**:
- IP address
- User agent (browser, OS)
- Referrer URL
- Device type (mobile/tablet/desktop)
- Authenticated user (if logged in)
- Timestamp

**Updates Hoarding**:
- Increments `view_count`
- Updates `last_viewed_at`

**Device Detection**:
- Mobile: iPhone, Android, Mobile, iPod
- Tablet: iPad, Tablet
- Desktop: Everything else

##### 10. `generateBreadcrumbs($hoarding)`
Navigation breadcrumbs with schema markup.

**Returns**:
```php
[
    ['label' => 'Home', 'url' => 'https://oohapp.com'],
    ['label' => 'Hoardings', 'url' => 'https://oohapp.com/hoardings'],
    ['label' => 'Bangalore', 'url' => 'https://oohapp.com/hoardings/bangalore'],
    ['label' => 'MG Road Billboard', 'url' => null] // Current page
]
```

**SEO Benefits**:
- Helps search engines understand site structure
- Breadcrumb rich snippets in search results
- Improved user navigation

##### 11. `updateSitemapEntry($hoarding)`
Create/update sitemap entry for hoarding.

**Logic**:
- Only creates entry if hoarding is approved AND available
- Sets priority based on view count:
  - 0.9: 1000+ views
  - 0.8: 500-999 views
  - 0.7: 100-499 views
  - 0.6: 50-99 views
  - 0.5: < 50 views
- Change frequency: 'weekly' (default)
- Updates lastmod on every change

##### 12. `generateLocationPageMetadata($city, $area = null)`
Create SEO metadata for city landing pages.

**Returns**:
```php
[
    'hoarding_count' => 150,
    'min_price' => 15000,
    'max_price' => 100000,
    'meta_title' => 'Billboard Hoardings in Bangalore - 150+ Locations',
    'meta_description' => 'Book premium billboard hoardings in Bangalore...',
    'meta_keywords' => ['bangalore hoardings', 'billboard bangalore', ...]
]
```

**Use Case**: Create /hoardings/bangalore pages with dynamic data

##### 13. `getPopularLocations($limit = 10)`
Get top cities by hoarding count.

**Returns**:
```php
[
    [
        'city' => 'Bangalore',
        'count' => 150,
        'slug' => 'bangalore',
        'url' => 'https://oohapp.com/hoardings/bangalore'
    ],
    // ... more cities
]
```

**Use Case**: Footer links, location navigation

---

### SitemapController

**Location**: `app/Http/Controllers/SitemapController.php`

#### Routes & Methods

##### 1. `GET /sitemap.xml` - Sitemap Index
**Method**: `index()`

**Returns**: XML with 3 sub-sitemaps
```xml
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <sitemap>
    <loc>https://oohapp.com/sitemap-static.xml</loc>
    <lastmod>2025-12-11T10:30:00+00:00</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://oohapp.com/sitemap-hoardings.xml</loc>
    <lastmod>2025-12-11T10:30:00+00:00</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://oohapp.com/sitemap-locations.xml</loc>
    <lastmod>2025-12-11T10:30:00+00:00</lastmod>
  </sitemap>
</sitemapindex>
```

##### 2. `GET /sitemap-static.xml` - Static Pages
**Method**: `static()`

**Includes** (5 URLs):
- Home (priority 1.0, daily)
- Hoardings Index (priority 0.9, weekly)
- Search (priority 0.8, weekly)
- Hoardings Map (priority 0.8, weekly)
- DOOH Index (priority 0.7, weekly)

##### 3. `GET /sitemap-hoardings.xml` - Hoardings
**Method**: `hoardings()`

**Filter**: Approved + Available + index_page=true

**Limit**: 10,000 URLs (Google's limit per sitemap)

**Dynamic Priority**: Based on view count
- 1000+ views → 0.9
- 500-999 → 0.8
- 100-499 → 0.7
- 50-99 → 0.6
- < 50 → 0.5

**Change Frequency**: weekly

**Example Output**:
```xml
<url>
  <loc>https://oohapp.com/hoardings/mg-road-bangalore</loc>
  <lastmod>2025-12-11T10:30:00+00:00</lastmod>
  <changefreq>weekly</changefreq>
  <priority>0.8</priority>
</url>
```

##### 4. `GET /sitemap-locations.xml` - Locations
**Method**: `locations()`

**Groups hoardings by city**

**Priority Logic**:
- > 10 hoardings → 0.8
- ≤ 10 hoardings → 0.7

**Example**:
```xml
<url>
  <loc>https://oohapp.com/hoardings/bangalore</loc>
  <lastmod>2025-12-11T10:30:00+00:00</lastmod>
  <changefreq>weekly</changefreq>
  <priority>0.8</priority>
</url>
```

---

## Meta Tags & SEO

### SEO-Optimized Hoarding Page

**View**: `resources/views/hoardings/show-seo.blade.php`

#### Head Section Meta Tags

```html
<!-- Primary Meta Tags -->
<title>{{ $hoarding->meta_title ?? $hoarding->location_name }}</title>
<meta name="title" content="{{ $hoarding->meta_title }}">
<meta name="description" content="{{ $hoarding->meta_description }}">
<meta name="keywords" content="{{ implode(', ', $hoarding->meta_keywords ?? []) }}">

<!-- Robots -->
<meta name="robots" content="{{ $hoarding->index_page ? 'index, follow' : 'noindex, nofollow' }}">

<!-- Canonical URL -->
<link rel="canonical" href="{{ route('hoardings.show', $hoarding->slug) }}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="product">
<meta property="og:url" content="{{ route('hoardings.show', $hoarding->slug) }}">
<meta property="og:title" content="{{ $ogData['og:title'] }}">
<meta property="og:description" content="{{ $ogData['og:description'] }}">
<meta property="og:image" content="{{ $ogData['og:image'] }}">
<meta property="og:site_name" content="OohApp">
<meta property="og:locale" content="en_IN">
<meta property="product:price:amount" content="{{ $hoarding->price_per_month }}">
<meta property="product:price:currency" content="INR">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ route('hoardings.show', $hoarding->slug) }}">
<meta property="twitter:title" content="{{ $twitterData['twitter:title'] }}">
<meta property="twitter:description" content="{{ $twitterData['twitter:description'] }}">
<meta property="twitter:image" content="{{ $twitterData['twitter:image'] }}">

<!-- Structured Data (JSON-LD) -->
<script type="application/ld+json">
{!! json_encode($structuredData) !!}
</script>

<script type="application/ld+json">
{!! json_encode($breadcrumbSchema) !!}
</script>
```

#### Page Components

##### 1. Breadcrumbs
```html
<nav aria-label="breadcrumb">
  <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
    @foreach($breadcrumbs as $index => $crumb)
    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
      @if($crumb['url'])
        <a href="{{ $crumb['url'] }}" itemprop="item">
          <span itemprop="name">{{ $crumb['label'] }}</span>
        </a>
      @else
        <span itemprop="name">{{ $crumb['label'] }}</span>
      @endif
      <meta itemprop="position" content="{{ $index + 1 }}">
    </li>
    @endforeach
  </ol>
</nav>
```

##### 2. Hero Image Carousel
```html
<div id="hoardingCarousel" class="carousel slide">
  <div class="carousel-inner">
    @foreach($hoarding->images as $index => $image)
    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
      <img src="{{ Storage::url($image) }}" alt="{{ $hoarding->location_name }}">
    </div>
    @endforeach
  </div>
  <!-- Carousel controls -->
</div>
```

##### 3. Title & Location (H1)
```html
<h1>{{ $hoarding->location_name }}</h1>
<p class="text-muted">
  <i class="fas fa-map-marker-alt"></i>
  {{ $hoarding->address }}, {{ $hoarding->city }}, {{ $hoarding->state }} - {{ $hoarding->pincode }}
</p>
```

##### 4. Key Metrics Cards
```html
<div class="row">
  <div class="col-md-3">
    <div class="metric-card">
      <i class="fas fa-sign"></i>
      <h6>Type</h6>
      <p>{{ ucfirst($hoarding->board_type) }}</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="metric-card">
      <i class="fas fa-ruler-combined"></i>
      <h6>Size</h6>
      <p>{{ $hoarding->width }} x {{ $hoarding->height }} m</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="metric-card">
      <i class="fas fa-lightbulb"></i>
      <h6>Illuminated</h6>
      <p>{{ $hoarding->is_lit ? 'Yes' : 'No' }}</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="metric-card">
      <i class="fas fa-traffic-light"></i>
      <h6>Traffic</h6>
      <p>{{ ucfirst($hoarding->traffic_density) }}</p>
    </div>
  </div>
</div>
```

##### 5. Social Sharing Buttons
```html
<div class="social-share">
  <h6>Share This Hoarding</h6>
  <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('hoardings.show', $hoarding->slug)) }}" 
     target="_blank" class="btn btn-facebook">
    <i class="fab fa-facebook-f"></i> Facebook
  </a>
  <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('hoardings.show', $hoarding->slug)) }}&text={{ urlencode($hoarding->meta_title) }}" 
     target="_blank" class="btn btn-twitter">
    <i class="fab fa-twitter"></i> Twitter
  </a>
  <a href="https://wa.me/?text={{ urlencode($hoarding->meta_title . ' - ' . route('hoardings.show', $hoarding->slug)) }}" 
     target="_blank" class="btn btn-whatsapp">
    <i class="fab fa-whatsapp"></i> WhatsApp
  </a>
  <button onclick="copyToClipboard('{{ route('hoardings.show', $hoarding->slug) }}')" class="btn btn-secondary">
    <i class="fas fa-link"></i> Copy Link
  </button>
</div>
```

##### 6. Interactive Map (Leaflet.js)
```html
@if($hoarding->latitude && $hoarding->longitude)
<div id="map" style="height: 400px;"></div>

<script>
  var map = L.map('map').setView([{{ $hoarding->latitude }}, {{ $hoarding->longitude }}], 15);
  
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);
  
  L.marker([{{ $hoarding->latitude }}, {{ $hoarding->longitude }}])
    .addTo(map)
    .bindPopup('{{ $hoarding->location_name }}')
    .openPopup();
</script>
@endif
```

##### 7. Pricing Card (Sticky Sidebar)
```html
<div class="pricing-card sticky-top">
  <h3>₹{{ number_format($hoarding->price_per_month) }}<span>/month</span></h3>
  
  <span class="badge badge-success">Available</span>
  
  <button class="btn btn-primary btn-block">Send Enquiry</button>
  <button class="btn btn-outline-primary btn-block">Book Now</button>
  
  <div class="view-stats">
    <i class="fas fa-eye"></i> {{ $hoarding->view_count }} views
  </div>
</div>
```

##### 8. Similar Hoardings Section
```html
<section class="similar-hoardings">
  <h3>Similar Hoardings</h3>
  <div class="row">
    @foreach($similarHoardings as $similar)
    <div class="col-md-3">
      <div class="hoarding-card">
        <img src="{{ Storage::url($similar->images[0] ?? '') }}" alt="{{ $similar->location_name }}">
        <h5>{{ $similar->location_name }}</h5>
        <p>{{ $similar->city }}</p>
        <p class="price">₹{{ number_format($similar->price_per_month) }}/mo</p>
        <a href="{{ route('hoardings.show', $similar->slug) }}" class="btn btn-sm btn-outline-primary">View Details</a>
      </div>
    </div>
    @endforeach
  </div>
</section>
```

---

## Structured Data

### Product Schema (JSON-LD)
```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Billboard Hoarding at MG Road, Bangalore",
  "description": "Premium billboard hoarding in high traffic area",
  "image": "https://oohapp.com/storage/hoardings/image1.jpg",
  "brand": {
    "@type": "Brand",
    "name": "ABC Advertising Pvt Ltd"
  },
  "offers": {
    "@type": "Offer",
    "url": "https://oohapp.com/hoardings/mg-road-bangalore",
    "priceCurrency": "INR",
    "price": "50000",
    "availability": "https://schema.org/InStock"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "12.9716",
    "longitude": "77.5946"
  },
  "additionalProperty": [
    {
      "@type": "PropertyValue",
      "name": "Width",
      "value": "20m"
    },
    {
      "@type": "PropertyValue",
      "name": "Height",
      "value": "10m"
    },
    {
      "@type": "PropertyValue",
      "name": "Location",
      "value": "MG Road, Bangalore, Karnataka"
    },
    {
      "@type": "PropertyValue",
      "name": "Illuminated",
      "value": "Yes"
    }
  ]
}
```

### BreadcrumbList Schema (JSON-LD)
```json
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "https://oohapp.com"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Hoardings",
      "item": "https://oohapp.com/hoardings"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "Bangalore",
      "item": "https://oohapp.com/hoardings/bangalore"
    },
    {
      "@type": "ListItem",
      "position": 4,
      "name": "MG Road Billboard"
    }
  ]
}
```

### SEO Benefits of Structured Data

1. **Rich Snippets**: Enhanced search results with:
   - Product name and image
   - Price and currency
   - Availability status
   - Location map link

2. **Knowledge Graph**: Potential inclusion in Google Knowledge Graph

3. **Voice Search**: Better understanding for voice assistants

4. **CTR Improvement**: Rich snippets increase click-through rates by 20-30%

---

## Sitemap Generation

### Sitemap Architecture

```
sitemap.xml (Index)
├── sitemap-static.xml (5 URLs)
├── sitemap-hoardings.xml (up to 10,000 URLs)
└── sitemap-locations.xml (city pages)
```

### Google Search Console Setup

1. **Submit Sitemap**:
   - URL: `https://oohapp.com/sitemap.xml`
   - Google Search Console → Sitemaps → Add new sitemap

2. **Monitor Coverage**:
   - Check indexed pages
   - Review errors/warnings
   - Track indexing trends

3. **Update Frequency**:
   - Sitemaps auto-update on hoarding approval/edit
   - Manual regeneration not required

### Sitemap Best Practices

1. **Priority Values** (0.0 - 1.0):
   - 1.0: Homepage
   - 0.9: Main category pages (Hoardings Index)
   - 0.8: Popular hoardings (1000+ views)
   - 0.7: Medium hoardings (100-999 views)
   - 0.5: New/low-traffic hoardings

2. **Change Frequency**:
   - daily: Homepage, frequently updated pages
   - weekly: Hoarding listings (default)
   - monthly: Static pages

3. **Last Modified**:
   - Automatically set to hoarding's `updated_at`
   - Helps search engines prioritize fresh content

4. **URL Limits**:
   - 10,000 URLs per sitemap (Google limit)
   - If exceeded, create additional hoarding sitemaps (sitemap-hoardings-2.xml, etc.)

---

## Analytics Tracking

### Page View Tracking

**Automatic Tracking**:
Every hoarding page view automatically records:
- IP address
- User agent (browser, OS, device)
- Referrer URL
- UTM parameters (if present)
- Geolocation (country, city)
- Device type (mobile/tablet/desktop)
- Authenticated user (if logged in)
- Timestamp

**Implementation**:
```php
// In HoardingController@show
$seoService->trackPageView($hoarding, [
    'utm_source' => request('utm_source'),
    'utm_medium' => request('utm_medium'),
    'utm_campaign' => request('utm_campaign'),
    'utm_term' => request('utm_term'),
    'utm_content' => request('utm_content')
]);
```

### UTM Parameter Tracking

**Campaign URL Format**:
```
https://oohapp.com/hoardings/mg-road-bangalore
  ?utm_source=google
  &utm_medium=cpc
  &utm_campaign=summer_sale
  &utm_term=bangalore+hoardings
  &utm_content=ad_variant_a
```

**UTM Parameters**:
- `utm_source`: Traffic source (google, facebook, email, etc.)
- `utm_medium`: Marketing medium (cpc, social, email, etc.)
- `utm_campaign`: Campaign name (summer_sale, launch_promo, etc.)
- `utm_term`: Paid keyword (for PPC campaigns)
- `utm_content`: Ad variant (A/B testing)

**Analytics Queries**:
```php
// Most popular traffic sources
HoardingPageView::select('utm_source', DB::raw('count(*) as views'))
    ->groupBy('utm_source')
    ->orderByDesc('views')
    ->get();

// Campaign performance
HoardingPageView::where('utm_campaign', 'summer_sale')
    ->count();

// Device breakdown
HoardingPageView::select('device_type', DB::raw('count(*) as views'))
    ->groupBy('device_type')
    ->get();
```

### Device Detection

**Logic**:
```php
private function detectDeviceType($userAgent)
{
    if (preg_match('/mobile|iphone|ipod|android/i', $userAgent)) {
        return 'mobile';
    } elseif (preg_match('/ipad|tablet/i', $userAgent)) {
        return 'tablet';
    }
    return 'desktop';
}
```

**Use Cases**:
- Optimize mobile experience
- Target device-specific ads
- Responsive design improvements

---

## Social Sharing

### Sharing Features

#### 1. Facebook Share
```html
<a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}" 
   target="_blank">
  Share on Facebook
</a>
```

**Preview**: Uses Open Graph tags (og:title, og:description, og:image)

#### 2. Twitter Share
```html
<a href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}&text={{ urlencode($title) }}" 
   target="_blank">
  Share on Twitter
</a>
```

**Preview**: Uses Twitter Card tags (twitter:title, twitter:description, twitter:image)

#### 3. WhatsApp Share
```html
<a href="https://wa.me/?text={{ urlencode($title . ' - ' . $url) }}" 
   target="_blank">
  Share on WhatsApp
</a>
```

**Mobile**: Opens WhatsApp app directly

#### 4. Copy Link
```javascript
function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(function() {
    alert('Link copied to clipboard!');
  }, function() {
    // Fallback for older browsers
    var textArea = document.createElement("textarea");
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
    alert('Link copied!');
  });
}
```

### Social Media Optimization Tips

1. **Images**:
   - Use high-quality images (min 1200x630px for Facebook)
   - Aspect ratio 1.91:1 for best display
   - File size < 8MB

2. **Titles**:
   - Keep under 60 characters for full display
   - Include primary keyword
   - Make compelling and clickable

3. **Descriptions**:
   - 2-3 sentences
   - Include call-to-action
   - Highlight unique value

4. **Testing**:
   - Facebook Sharing Debugger: https://developers.facebook.com/tools/debug/
   - Twitter Card Validator: https://cards-dev.twitter.com/validator

---

## API Endpoints

### Public Routes

| Method | Endpoint | Controller@Method | Description |
|--------|----------|-------------------|-------------|
| GET | `/sitemap.xml` | SitemapController@index | Sitemap index |
| GET | `/sitemap-static.xml` | SitemapController@static | Static pages |
| GET | `/sitemap-hoardings.xml` | SitemapController@hoardings | All hoardings |
| GET | `/sitemap-locations.xml` | SitemapController@locations | City pages |
| GET | `/hoardings/{slug}` | HoardingController@show | SEO hoarding page |

---

## Usage Examples

### Example 1: Auto-Generate SEO Metadata on Hoarding Creation

```php
use App\Services\SEOService;

// In HoardingController@store or HoardingObserver@created
$seoService = app(SEOService::class);

// Generate complete SEO package
$metadata = $seoService->generateHoardingMetadata($hoarding);

// Update hoarding
$hoarding->update($metadata);

// Update sitemap
$seoService->updateSitemapEntry($hoarding);
```

**Result**:
- Slug: `mg-road-billboard-bangalore`
- Meta title: `Billboard Hoarding at MG Road, Bangalore - 20x10m`
- Meta description: Auto-generated compelling description
- Keywords: `['billboard', 'bangalore', 'mg road', ...]`
- Sitemap entry created

### Example 2: Track Page View with UTM Parameters

```php
// In HoardingController@show
use App\Services\SEOService;

public function show($slug)
{
    $hoarding = Hoarding::where('slug', $slug)->firstOrFail();
    
    $seoService = app(SEOService::class);
    
    // Track view with UTM params
    $seoService->trackPageView($hoarding, [
        'utm_source' => request('utm_source'),
        'utm_medium' => request('utm_medium'),
        'utm_campaign' => request('utm_campaign'),
        'utm_term' => request('utm_term'),
        'utm_content' => request('utm_content')
    ]);
    
    // Generate SEO data
    $structuredData = $seoService->generateStructuredData($hoarding);
    $ogData = $seoService->generateOpenGraphData($hoarding);
    $twitterData = $seoService->generateTwitterCardData($hoarding);
    $breadcrumbs = $seoService->generateBreadcrumbs($hoarding);
    
    return view('hoardings.show-seo', compact(
        'hoarding',
        'structuredData',
        'ogData',
        'twitterData',
        'breadcrumbs'
    ));
}
```

### Example 3: Create City Landing Page

```php
use App\Services\SEOService;
use App\Models\LocationPage;

$seoService = app(SEOService::class);

// Generate metadata for Bangalore page
$metadata = $seoService->generateLocationPageMetadata('Bangalore');

// Create location page
$locationPage = LocationPage::create([
    'slug' => 'bangalore',
    'city' => 'Bangalore',
    'state' => 'Karnataka',
    'meta_title' => $metadata['meta_title'],
    'meta_description' => $metadata['meta_description'],
    'meta_keywords' => $metadata['meta_keywords'],
    'hoarding_count' => $metadata['hoarding_count'],
    'min_price' => $metadata['min_price'],
    'max_price' => $metadata['max_price'],
    'is_published' => true
]);
```

**Result**: SEO-optimized page at `/hoardings/bangalore`

### Example 4: Get Popular Locations for Footer

```php
// In AppServiceProvider or BaseController
use App\Services\SEOService;

$seoService = app(SEOService::class);
$popularCities = $seoService->getPopularLocations(10);

// Returns:
// [
//   ['city' => 'Bangalore', 'count' => 150, 'slug' => 'bangalore', 'url' => '...'],
//   ['city' => 'Mumbai', 'count' => 120, 'slug' => 'mumbai', 'url' => '...'],
//   ...
// ]

// Use in footer links
@foreach($popularCities as $city)
  <a href="{{ $city['url'] }}">{{ $city['city'] }} ({{ $city['count'] }})</a>
@endforeach
```

### Example 5: Analytics Dashboard Query

```php
use App\Models\HoardingPageView;

// Most viewed hoardings (last 30 days)
$topHoardings = HoardingPageView::select('hoarding_id', DB::raw('count(*) as views'))
    ->where('viewed_at', '>=', now()->subDays(30))
    ->groupBy('hoarding_id')
    ->orderByDesc('views')
    ->limit(10)
    ->with('hoarding')
    ->get();

// Traffic source breakdown
$sources = HoardingPageView::select('utm_source', DB::raw('count(*) as views'))
    ->whereNotNull('utm_source')
    ->groupBy('utm_source')
    ->orderByDesc('views')
    ->get();

// Device usage
$devices = HoardingPageView::select('device_type', DB::raw('count(*) as views'))
    ->groupBy('device_type')
    ->get();

// Campaign performance
$campaigns = HoardingPageView::select(
        'utm_campaign',
        DB::raw('count(*) as views'),
        DB::raw('count(DISTINCT hoarding_id) as unique_hoardings'),
        DB::raw('count(DISTINCT user_id) as unique_users')
    )
    ->whereNotNull('utm_campaign')
    ->groupBy('utm_campaign')
    ->orderByDesc('views')
    ->get();
```

---

## Migration & Setup

### Run Migration
```bash
php artisan migrate
```

Creates:
- 4 new tables
- Adds 8 SEO fields to hoardings table
- Inserts default breadcrumb configurations

### Generate Metadata for Existing Hoardings
```bash
php artisan tinker
```

```php
use App\Models\Hoarding;
use App\Services\SEOService;

$seoService = app(SEOService::class);

Hoarding::where('approval_status', 'approved')
    ->where('status', 'available')
    ->chunk(100, function ($hoardings) use ($seoService) {
        foreach ($hoardings as $hoarding) {
            $metadata = $seoService->generateHoardingMetadata($hoarding);
            $hoarding->update($metadata);
            $seoService->updateSitemapEntry($hoarding);
        }
    });
```

### Submit Sitemap to Google
1. Visit: https://search.google.com/search-console
2. Add property: https://oohapp.com
3. Verify ownership (HTML tag method)
4. Navigate to: Sitemaps
5. Add sitemap: `https://oohapp.com/sitemap.xml`
6. Click "Submit"

---

## Best Practices

### For SEO

1. **Content Quality**:
   - Write unique descriptions (no duplicates)
   - Include relevant keywords naturally
   - Keep titles under 60 characters
   - Keep descriptions under 160 characters

2. **Images**:
   - Use descriptive filenames (mg-road-billboard.jpg)
   - Add alt text to all images
   - Compress images for fast loading
   - Use WebP format when possible

3. **Performance**:
   - Enable page caching
   - Minimize CSS/JS
   - Use CDN for images
   - Enable gzip compression
   - Target < 3 second load time

4. **Mobile Optimization**:
   - Responsive design (Bootstrap grid)
   - Touch-friendly buttons (min 44x44px)
   - Fast mobile load times
   - Mobile-friendly popups

5. **Internal Linking**:
   - Link to related hoardings
   - City landing pages
   - Breadcrumb navigation
   - Footer sitemap

### For Analytics

1. **UTM Tracking**:
   - Always use UTM parameters for paid campaigns
   - Standardize naming conventions
   - Track all traffic sources

2. **Regular Analysis**:
   - Review top performing hoardings monthly
   - Analyze traffic sources
   - Monitor device trends
   - Track campaign ROI

3. **A/B Testing**:
   - Test different meta titles
   - Experiment with descriptions
   - Try various OG images
   - Track conversion rates

---

## Troubleshooting

### Issue: Meta Tags Not Showing in Social Previews

**Cause**: Facebook/Twitter cache not refreshed

**Solution**:
1. Facebook: https://developers.facebook.com/tools/debug/
   - Enter URL
   - Click "Scrape Again"
2. Twitter: https://cards-dev.twitter.com/validator
   - Enter URL
   - Preview card

### Issue: Sitemap Not Being Crawled

**Cause**: Not submitted to Google Search Console

**Solution**:
1. Submit sitemap.xml to Google Search Console
2. Wait 24-48 hours for initial crawl
3. Check Coverage report for errors

### Issue: Duplicate Meta Descriptions

**Cause**: Auto-generation using same template

**Solution**:
1. Review SEOService@generateMetaDescription() logic
2. Add more variation based on hoarding features
3. Manually edit high-priority hoardings

### Issue: Low Click-Through Rate (CTR)

**Possible Causes**:
- Boring meta titles/descriptions
- Missing structured data
- Poor OG images
- Slow page load

**Solutions**:
1. A/B test different titles
2. Use compelling CTAs in descriptions
3. Add high-quality images
4. Optimize page speed

---

## Future Enhancements

### Planned Features

1. **Auto-Generated Content**:
   - AI-powered meta descriptions
   - Dynamic content based on user location
   - Personalized recommendations

2. **Advanced Analytics**:
   - Heat maps (where users click)
   - Scroll depth tracking
   - Conversion funnel analysis
   - A/B testing framework

3. **Video Sitemaps**:
   - For hoardings with video content
   - YouTube integration

4. **AMP Pages**:
   - Accelerated Mobile Pages for ultra-fast loading
   - Google AMP carousel eligibility

5. **Progressive Web App (PWA)**:
   - Offline functionality
   - Push notifications
   - App-like experience

6. **Voice Search Optimization**:
   - FAQ schema markup
   - Natural language content
   - Featured snippet targeting

---

## Resources

### SEO Tools
- **Google Search Console**: https://search.google.com/search-console
- **Google Analytics**: https://analytics.google.com
- **PageSpeed Insights**: https://pagespeed.web.dev
- **Schema Markup Validator**: https://validator.schema.org
- **Facebook Debugger**: https://developers.facebook.com/tools/debug/
- **Twitter Card Validator**: https://cards-dev.twitter.com/validator

### Learning Resources
- **Google SEO Starter Guide**: https://developers.google.com/search/docs/beginner/seo-starter-guide
- **Schema.org Documentation**: https://schema.org
- **Open Graph Protocol**: https://ogp.me
- **Twitter Cards Guide**: https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/abouts-cards

---

## Support

For issues or questions:
- **Email**: seo@oohapp.com
- **Documentation**: https://docs.oohapp.com/seo-optimization
- **SEO Team**: Contact marketing department

---

**Last Updated**: December 11, 2025  
**Version**: 1.0  
**Author**: OohApp Development Team

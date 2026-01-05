# OOHAPP Customer Home Page - Implementation Guide

## Overview
This guide explains the complete Customer Home Page implementation following the Figma design.

## File Structure

```
resources/
├── views/
│   ├── layouts/
│   │   └── app.blade.php                    # Main layout file
│   ├── home/
│   │   └── index.blade.php                   # Home page (main)
│   └── components/
│       └── customer/
│           ├── navbar.blade.php              # Sticky navigation
│           ├── hero-banner.blade.php         # Hero section
│           ├── search-bar.blade.php          # Search form
│           ├── hoarding-card.blade.php       # Hoarding card component
│           ├── dooh-card.blade.php           # DOOH screen card component
│           ├── category-card.blade.php       # City category card
│           ├── cta-section.blade.php         # Why Choose OOHAPP section
│           └── footer.blade.php              # Footer component
```

## Routes Configuration

The routes are already configured in `routes/web.php`:

```php
// Home page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Other customer routes
Route::get('/hoardings', [HoardingController::class, 'index'])->name('hoardings.index');
Route::get('/hoardings/{id}', [HoardingController::class, 'show'])->name('hoardings.show');
Route::get('/dooh', [DOOHController::class, 'index'])->name('dooh.index');
Route::get('/dooh/{id}', [DOOHController::class, 'show'])->name('dooh.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
```

## Controller - HomeController.php

Location: `app/Http/Controllers/Web/HomeController.php`

The controller provides the following data to the view:

```php
public function index(): View
{
    return view('home.index', [
        'stats' => [
            'total_hoardings' => 150,  // Total active hoardings
            'total_vendors' => 45,      // Total vendors
            'total_bookings' => 280,    // Total bookings
        ],
        'bestHoardings' => // 8 latest active hoardings,
        'topDOOHs' => // 8 latest DOOH screens,
        'topCities' => // Array of 8 cities with images
    ]);
}
```

## Component Usage

### 1. **Navbar Component**
Located: `resources/views/components/customer/navbar.blade.php`

Features:
- Sticky header
- Responsive mobile menu
- Auth buttons (Login/Sign Up or Dashboard)
- Navigation links

### 2. **Hero Banner Component**
Located: `resources/views/components/customer/hero-banner.blade.php`

Features:
- Gradient background (purple to violet)
- Main headline and description
- Two CTA buttons (Explore Hoardings & Browse DOOH)
- SVG wave bottom decoration

### 3. **Search Bar Component**
Located: `resources/views/components/customer/search-bar.blade.php`

Features:
- Location search input
- Category/Type dropdown
- Budget range selector
- Search button

Form submits to the `/search` route.

### 4. **Hoarding Card Component**
Located: `resources/views/components/customer/hoarding-card.blade.php`

Usage:
```blade
@foreach($bestHoardings as $hoarding)
    @include('components.customer.hoarding-card', ['hoarding' => $hoarding])
@endforeach
```

Required data:
- `$hoarding->id`
- `$hoarding->title`
- `$hoarding->address`
- `$hoarding->type`
- `$hoarding->monthly_price`
- `$hoarding->weekly_price` (optional)
- `$hoarding->hasMedia('images')` method
- `$hoarding->getFirstMediaUrl('images')` method

### 5. **DOOH Card Component**
Located: `resources/views/components/customer/dooh-card.blade.php`

Usage:
```blade
@foreach($topDOOHs as $dooh)
    @include('components.customer.dooh-card', ['dooh' => $dooh])
@endforeach
```

Required data:
- `$dooh->id`
- `$dooh->name`
- `$dooh->city`
- `$dooh->state`
- `$dooh->screen_type`
- `$dooh->resolution`
- `$dooh->total_slots_per_day`
- `$dooh->price_per_slot`

### 6. **Category/City Card Component**
Located: `resources/views/components/customer/category-card.blade.php`

Usage:
```blade
@foreach($topCities as $city)
    @include('components.customer.category-card', ['city' => $city])
@endforeach
```

Required data:
```php
$city = [
    'name' => 'JAIPUR',
    'count' => 25,  // Number of hoardings
    'image' => 'https://images.unsplash.com/...'  // City image URL
]
```

### 7. **CTA Section Component**
Located: `resources/views/components/customer/cta-section.blade.php`

Features:
- "Why Choose OOHAPP" section
- 3 feature cards (Instant Booking, Verified Vendors, Best Prices)
- Statistics display (hoardings, vendors, bookings, cities)
- Call-to-action button

Uses `$stats` variable from controller.

### 8. **Footer Component**
Located: `resources/views/components/customer/footer.blade.php`

Features:
- 4-column layout (About, Quick Links, For Vendors, Support)
- Social media icons
- Copyright and legal links

## Tailwind CSS Configuration

The UI uses Tailwind CSS classes. Make sure your `tailwind.config.js` includes:

```js
content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
],
```

## Color Scheme (from Figma)

Primary Colors:
- Blue: `blue-600` (#2563EB)
- Purple: `purple-600` (#9333EA)
- Pink: `pink-600` (#DB2777)
- Orange: `orange-600` (#EA580C)

Gradients:
- Hero: `from-indigo-600 via-purple-600 to-purple-700`
- CTA: `from-blue-600 to-purple-600`
- DOOH: `from-pink-600 to-orange-600`

## Responsive Breakpoints

- Mobile: < 768px
- Tablet: 768px - 1024px
- Desktop: > 1024px

Grid columns adapt:
- Mobile: 1 column
- Tablet: 2 columns
- Desktop: 4 columns

## Testing the Homepage

1. Start the Laravel development server:
```bash
php artisan serve
```

2. Visit: `http://127.0.0.1:8000`

3. You should see:
   - Sticky navigation bar
   - Hero section with gradient background
   - Search form
   - Best Hoardings grid (8 cards)
   - Top DOOHs grid (8 cards)
   - Top Cities grid (8 cards)
   - Why Choose OOHAPP section
   - Footer

## Adding Sample Data

If you don't have data yet, you can create seeders:

```php
// database/seeders/HoardingSeeder.php
Hoarding::create([
    'title' => 'Premium Billboard - MG Road',
    'address' => 'MG Road, Bangalore, Karnataka',
    'type' => 'billboard',
    'status' => 'active',
    'monthly_price' => 50000,
    'weekly_price' => 15000,
]);

// Run seeder
php artisan db:seed --class=HoardingSeeder
```

## Customization

To match your exact Figma design:

1. **Colors**: Update the gradient colors in `hero-banner.blade.php`
2. **Fonts**: Change the Google Font in `app.blade.php`
3. **Spacing**: Adjust padding/margin classes (py-16, px-4, etc.)
4. **Card Layout**: Modify grid columns in `home/index.blade.php`
5. **Images**: Update city images in `HomeController->getTopCities()`

## Performance Tips

1. **Eager Loading**: Load relationships in controller
```php
Hoarding::with(['vendor', 'media'])->get();
```

2. **Caching**: Cache top cities data
```php
Cache::remember('top_cities', 3600, function() {
    return $this->getTopCities();
});
```

3. **Image Optimization**: Use responsive images
```blade
<img srcset="..." sizes="..." />
```

## Next Steps

1. ✅ Home page UI complete
2. Create `hoardings/index.blade.php` (listing page)
3. Create `hoardings/show.blade.php` (detail page)
4. Create `dooh/index.blade.php` (DOOH listing)
5. Create `dooh/show.blade.php` (DOOH detail)
6. Create `search/index.blade.php` (search results)

Each of these pages can reuse the same components (navbar, footer, cards).

---

**Implementation Status**: ✅ COMPLETE

All Blade components and views have been created following the Figma design with Tailwind CSS and responsive layouts.

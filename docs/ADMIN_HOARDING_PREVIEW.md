# Admin Hoarding Preview Feature

## Overview
This feature provides a comprehensive, read-only preview interface for administrators to review hoarding details before approval. It displays ALL hoarding information including media, specifications, pricing, packages, and vendor details.

## Files Created

### 1. Controller
**Location:** `Modules/Admin/Controllers/Web/HoardingController.php`

**Purpose:** Handles admin-side hoarding preview operations

**Key Methods:**
- `show($id)` - Displays comprehensive hoarding preview with all relationships
- `index()` - Lists all hoardings (placeholder)
- `approve($id)` - Hoarding approval (placeholder for existing workflow)
- `reject($id)` - Hoarding rejection (placeholder for existing workflow)

**Features:**
- Eager loads ALL necessary relationships to prevent N+1 queries
- Conditional logic for OOH vs DOOH data processing
- Organizes media files for gallery display
- Extracts type-specific pricing and technical specifications
- Handles package loading based on hoarding type

### 2. View
**Location:** `resources/views/hoardings/admin/preview.blade.php`

**Purpose:** Admin-specific comprehensive hoarding preview UI

**Layout:** 
- Uses `layouts.admin` 
- Responsive grid layout (2/3 main content + 1/3 sidebar)
- Tailwind CSS styling

**Sections Displayed:**

#### Left Column (Main Content)
1. **Media Gallery**
   - Main image display with thumbnail switcher
   - Supports both OOH and DOOH media
   - Fallback to placeholder if no media

2. **Basic Information**
   - Title, type, category, description
   - View count statistics
   - Status badges

3. **Location Details**
   - Full address with city, state, pincode
   - Landmark information
   - GPS coordinates with verification status

4. **Physical Specifications**
   - Width, height, unit
   - Area (OOH) or screen size (DOOH)
   - Facing direction

5. **Technical Specifications**
   - **DOOH:** Screen type, resolution, slot details, video requirements, allowed formats
   - **OOH:** Lighting type, material type, mounting type
   - Road type and traffic type

6. **Visibility & Audience**
   - Visibility hours and level
   - Expected footfall and eyeball counts
   - Target audience tags

7. **Packages & Offers**
   - All active packages with pricing
   - Package details (duration, discounts, validity)
   - Type-specific pricing display

8. **Legal & Permits**
   - Nagar Nigam approval status
   - Permit number and validity
   - Expiry warnings

#### Right Column (Sidebar)
1. **Pricing Summary (Sticky)**
   - **DOOH:** Per-slot pricing, 10-sec/30-sec rates, minimum booking
   - **OOH:** Monthly/weekly pricing with base price comparison
   - Additional charges (printing, mounting, lighting, graphics, survey)
   - Platform commission percentage

2. **Vendor Information**
   - Vendor name, email, phone

3. **Booking Rules**
   - Min/max booking duration
   - Grace period
   - Availability dates

4. **Meta Information**
   - Created/updated timestamps
   - Total bookings count
   - Last booked date

## Route Configuration

**Existing Route (in `routes/web.php`):**
```php
Route::get('/hoardings/{id}', [\Modules\Admin\Controllers\Web\HoardingController::class, 'show'])
    ->name('hoardings.show');
```

**Access:** Admin middleware protected
**URL Pattern:** `/admin/hoardings/{id}`
**Route Name:** `hoardings.show`

## Usage

### From Admin Panel
Clicking on any hoarding title in the admin tables will redirect to the preview page:

```blade
<a href="{{ route('hoardings.show', $hoarding->id) }}">
    {{ $hoarding->title }}
</a>
```

### Direct Access
```php
return redirect()->route('hoardings.show', ['id' => $hoardingId]);
```

## Data Flow

### Controller Processing
```
1. Load Hoarding with eager loading
   ├── vendor
   ├── hoardingMedia (OOH)
   ├── ooh
   ├── doohScreen (DOOH)
   ├── doohScreen.media
   └── packages (type-specific)

2. Process based on hoarding_type
   ├── DOOH
   │   ├── Extract screen specs
   │   ├── Set slot-based pricing
   │   └── Load DOOH packages
   └── OOH
       ├── Extract physical dimensions
       ├── Set monthly/weekly pricing
       └── Load OOH packages

3. Organize media files
   ├── Map to consistent structure
   └── Add fallback placeholder

4. Return view with processed data
```

### View Rendering
```
1. Display header with status badges
2. Render media gallery with switcher
3. Show all information sections
4. Display pricing sidebar
5. Add interactive elements (image switching)
```

## Database Relationships Used

### Hoarding Model
- `vendor` - BelongsTo User
- `hoardingMedia` - HasMany HoardingMedia (OOH)
- `ooh` - HasOne OOHHoarding
- `doohScreen` - HasOne DOOHScreen
- `oohPackages` - HasMany HoardingPackage

### DOOHScreen Model
- `media` - HasMany DOOHScreenMedia
- `packages` - HasMany DOOHPackage

## Security

### Middleware Protection
- Only accessible to authenticated admin users
- Uses existing admin middleware and guards

### No Modifications
- **READ-ONLY:** This feature does NOT modify any data
- Approval/rejection logic is placeholder only
- No status changes or updates performed

## Responsive Design

### Desktop (lg+)
- 2/3 + 1/3 grid layout
- Sticky sidebar for pricing
- Full media gallery

### Mobile
- Single column stack
- Touch-friendly image gallery
- Optimized font sizes

## Error Handling

### Not Found
- Uses `findOrFail()` - automatically returns 404 if hoarding doesn't exist

### Missing Relationships
- Null-safe operators (`?->`) throughout
- Fallback values for missing data
- Conditional rendering based on data availability

### Missing Media
- Automatic fallback to placeholder image
- Empty state messages

## Customization Points

### Adding New Fields
1. Update controller to extract field from model
2. Add display section in view
3. Use existing styling patterns

### Modifying Layout
- Adjust grid classes: `lg:col-span-2` and `lg:col-span-1`
- Add/remove sections as needed
- All sections are independent

### Styling Changes
- Uses Tailwind CSS utility classes
- Consistent color scheme:
  - Green: Primary actions/prices
  - Gray: Text/borders
  - Status-specific: green, yellow, red, orange

## Performance Considerations

### Eager Loading
- All relationships loaded in single query
- Prevents N+1 query problems
- Optimized for large datasets

### Conditional Loading
- Only loads packages for active items
- Filters by validity dates
- Orders by duration for UX

### Image Optimization
- Uses existing media processing
- Storage URLs for CDN compatibility
- Lazy loading ready (can add `loading="lazy"`)

## Testing Checklist

- [ ] Preview OOH hoarding with all fields populated
- [ ] Preview DOOH hoarding with all fields populated
- [ ] Preview hoarding with minimal data (test fallbacks)
- [ ] Preview hoarding with no media (placeholder test)
- [ ] Preview hoarding with packages
- [ ] Preview hoarding without packages
- [ ] Test image gallery switching
- [ ] Test responsive layout on mobile
- [ ] Verify back button functionality
- [ ] Check status badge display
- [ ] Verify vendor information display
- [ ] Test with expired permits
- [ ] Test with different approval statuses

## Known Limitations

1. **Approval Workflow:** Approve/reject methods are placeholders - implement based on existing workflow
2. **Map Display:** No map integration yet - can add Google Maps embed if needed
3. **Print View:** No print-optimized layout - can add `@media print` styles
4. **Export:** No PDF export functionality - can integrate with DomPDF if needed

## Future Enhancements

### Potential Additions
- [ ] Inline approval/rejection (if workflow permits)
- [ ] Interactive map with marker
- [ ] Booking calendar visualization
- [ ] Performance metrics charts
- [ ] Comparison with similar hoardings
- [ ] Audit log display
- [ ] Comment/notes system for internal review
- [ ] PDF export for offline review
- [ ] Share preview link with external reviewers

### Performance Optimizations
- [ ] Cache frequently accessed hoardings
- [ ] Lazy load packages section
- [ ] Progressive image loading
- [ ] API endpoint for partial data updates

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify database relationships are intact
3. Ensure media files exist in storage
4. Check admin middleware is active

## Changelog

### Version 1.0 (Initial Release)
- Created Admin HoardingController with comprehensive show method
- Created admin preview view with all hoarding details
- Supports both OOH and DOOH hoarding types
- Responsive design with Tailwind CSS
- Eager loading for performance
- Complete documentation

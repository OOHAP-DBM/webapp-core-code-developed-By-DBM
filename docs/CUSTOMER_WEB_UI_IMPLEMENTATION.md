# PROMPT 25 - Customer Web UI Implementation

## Overview
Complete Figma-based customer-facing web interface for OOHAPP platform with modern design, responsive layouts, and API-backed functionality.

## ✅ Components Created

### 1. Reusable UI Components (`resources/views/components/`)
- ✅ **hoarding-card.blade.php** - Dynamic inventory card with wishlist, pricing, specs
- ✅ **search-filters.blade.php** - Advanced search with city/state/type/price filters
- ✅ **notification-item.blade.php** - Notification center item with icons and actions
- ✅ **order-card.blade.php** - Order/booking card with status and progress

### 2. Authentication Pages (`resources/views/auth/`)
- ✅ **login.blade.php** - Modern gradient login with OTP option (UPDATED)
- ⏳ **login-v2.blade.php** - Alternative login design
- ⏳ **register.blade.php** - Multi-step registration (TO UPDATE)
- ⏳ **otp.blade.php** - OTP verification screen (TO UPDATE)

### 3. Customer Pages (TO CREATE)

#### Home & Discovery
- ⏳ **customer/home.blade.php** - Geofencing-enabled home with featured hoardings
- ⏳ **customer/search.blade.php** - Advanced search results with filters
- ⏳ **customer/shortlist.blade.php** - Wishlist/saved hoardings

#### Enquiry & Booking Flow
- ⏳ **customer/enquiries/index.blade.php** - My enquiries list
- ⏳ **customer/enquiries/create.blade.php** - Create new enquiry
- ⏳ **customer/enquiries/show.blade.php** - Enquiry details with offers
- ⏳ **customer/quotations/show.blade.php** - Quotation review
- ⏳ **customer/bookings/create.blade.php** - Booking confirmation
- ⏳ **customer/bookings/payment.blade.php** - Payment page (EXISTS, needs update)

#### Account Management
- ⏳ **customer/profile/index.blade.php** - Profile settings
- ⏳ **customer/profile/kyc.blade.php** - KYC submission
- ⏳ **customer/orders/index.blade.php** - My orders/bookings
- ⏳ **customer/orders/show.blade.php** - Order details

#### Communication
- ⏳ **customer/threads/index.blade.php** - Thread inbox
- ⏳ **customer/threads/show.blade.php** - Conversation view
- ⏳ **customer/notifications/index.blade.php** - Notification center

---

## 🎨 Design System

### Color Palette
```css
--primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
--primary-color: #667eea;
--primary-dark: #764ba2;
--success-color: #10b981;
--warning-color: #f59e0b;
--danger-color: #ef4444;
--text-primary: #1e293b;
--text-secondary: #64748b;
--text-muted: #94a3b8;
--border-color: #e2e8f0;
--bg-light: #f8fafc;
```

### Typography
- **Headings**: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI'
- **Body**: Same stack, 15px base
- **Labels**: 14px, font-weight: 500
- **Small Text**: 12-13px

### Border Radius
- Cards: 16-24px
- Buttons: 12px
- Inputs: 12px
- Badges: 6-8px

### Spacing Scale
- Base unit: 4px
- Common: 8px, 12px, 16px, 24px, 32px, 48px

---

## 🔧 Features Implemented

### Dynamic Inventory Cards
```blade
<x-hoarding-card 
    :hoarding="$hoarding" 
    :showActions="true" 
    :isWishlisted="$hoarding->isWishlisted()"
/>
```

**Features:**
- Responsive image with fallback gradient
- Wishlist heart button (toggleable)
- Status badge (available/booked)
- Location, dimensions, lighting specs
- Starting price display
- View Details & Enquire CTAs

### Search & Filter Component
```blade
<x-search-filters 
    :filters="$filters" 
    :cities="$cities" 
    :states="$states"
/>
```

**Features:**
- Search by location/title
- City, State, Type dropdowns
- Price range filters
- Advanced filters (collapse): illumination, dimensions, sort
- Auto-submit on city/state change
- Reset functionality

### Notification System
```blade
<x-notification-item :notification="$notification" />
```

**Features:**
- Type-based icons (booking/payment/enquiry/quotation)
- Read/unread status
- Relative timestamps
- Action buttons with URLs
- Hover effects

### Order/Booking Cards
```blade
<x-order-card :booking="$booking" />
```

**Features:**
- Thumbnail image
- Booking number, status badge
- Start/end dates
- Location info
- Payment status
- Progress timeline (for active bookings)
- Action buttons (view, pay, message)

---

## 📱 Responsive Design

### Breakpoints
```css
/* Mobile First */
@media (min-width: 576px) { /* sm */ }
@media (min-width: 768px) { /* md */ }
@media (min-width: 992px) { /* lg */ }
@media (min-width: 1200px) { /* xl */ }
```

### Grid System
- Mobile: 1 column
- Tablet (md): 2 columns
- Desktop (lg): 3-4 columns

### Component Behavior
- **Cards**: Stack on mobile, grid on tablet+
- **Filters**: Collapse on mobile, inline on desktop
- **Sidebar**: Offcanvas on mobile, fixed on desktop
- **Navbar**: Hamburger on mobile, full nav on desktop

---

## 🌐 Geofencing Implementation (TO DO)

### Strategy
1. **Browser Geolocation API**
```javascript
navigator.geolocation.getCurrentPosition(position => {
    const {latitude, longitude} = position.coords;
    fetch('/api/v1/customer/hoardings/nearby', {
        method: 'POST',
        body: JSON.stringify({lat: latitude, lng: longitude, radius: 10})
    });
});
```

2. **Laravel Backend**
```php
// HoardingController
public function nearby(Request $request) {
    $lat = $request->lat;
    $lng = $request->lng;
    $radius = $request->radius ?? 10; // km
    
    $hoardings = Hoarding::selectRaw("
        *, ( 6371 * acos( cos( radians(?) ) *
        cos( radians( latitude ) ) *
        cos( radians( longitude ) - radians(?) ) +
        sin( radians(?) ) *
        sin( radians( latitude ) ) ) ) AS distance
    ", [$lat, $lng, $lat])
    ->having('distance', '<', $radius)
    ->orderBy('distance')
    ->get();
}
```

3. **Frontend Integration**
- Show "Use My Location" button on home page
- Display distance in hoarding cards
- Filter results by radius
- Map view with pins

---

## 🔗 API Integration

### Customer API Endpoints
All routes use `/api/v1/customer/*` with `auth:sanctum` middleware.

#### Hoardings
```
GET  /hoardings              - List all available hoardings (with filters)
GET  /hoardings/{id}         - Hoarding details
POST /hoardings/nearby       - Get nearby hoardings (geofencing)
```

#### Wishlist/Shortlist
```
GET    /wishlist             - Get user's wishlisted hoardings
POST   /wishlist/{id}        - Add to wishlist
DELETE /wishlist/{id}        - Remove from wishlist
```

#### Enquiries
```
GET  /enquiries              - My enquiries
POST /enquiries              - Create enquiry
GET  /enquiries/{id}         - Enquiry details
GET  /enquiries/{id}/offers  - Offers for enquiry
```

#### Bookings
```
GET  /bookings               - My bookings
POST /bookings               - Create booking
GET  /bookings/{id}          - Booking details
```

#### Profile & KYC
```
GET  /profile                - Get profile
PUT  /profile                - Update profile
POST /profile/kyc            - Submit KYC
GET  /profile/kyc            - KYC status
```

#### Notifications
```
GET  /notifications          - Get notifications
POST /notifications/{id}/read - Mark as read
POST /notifications/read-all  - Mark all as read
```

#### Threads
```
GET  /threads                - My threads
GET  /threads/{id}           - Thread messages
POST /threads/{id}/messages  - Send message
```

---

## 🎯 Implementation Priority

### Phase 1: Core Pages (CURRENT)
1. ✅ Reusable components
2. ✅ Modern login page
3. ⏳ Register page with OTP
4. ⏳ Home page with geofencing
5. ⏳ Search results page

### Phase 2: Booking Flow
6. ⏳ Enquiry creation
7. ⏳ Quotation review
8. ⏳ Booking confirmation
9. ⏳ Payment page (update existing)

### Phase 3: Account & Orders
10. ⏳ Profile & KYC
11. ⏳ My Orders list
12. ⏳ Order details

### Phase 4: Communication
13. ⏳ Thread inbox
14. ⏳ Notification center

---

## 📦 Required Controllers

### Create/Update:
```
app/Http/Controllers/Web/Customer/
├── HomeController.php (geofencing logic)
├── SearchController.php (filters, sorting)
├── ShortlistController.php (wishlist CRUD)
├── EnquiryController.php (create, list, show)
├── QuotationController.php (review, accept)
├── BookingController.php (create, list, show)
├── ProfileController.php (edit, update, KYC)
├── NotificationController.php (index, mark read)
└── ThreadController.php (index, show, send message)
```

---

## 🧪 Testing Checklist

### Responsive Testing
- [ ] Mobile (320px-767px)
- [ ] Tablet (768px-1023px)
- [ ] Desktop (1024px+)

### Browser Testing
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

### Feature Testing
- [ ] Geolocation permission flow
- [ ] Search with multiple filters
- [ ] Wishlist add/remove
- [ ] Enquiry submission
- [ ] Payment flow
- [ ] Image lazy loading
- [ ] API error handling

---

## 📝 Next Steps

1. **Complete Auth Pages**
   - Update register.blade.php with OTP flow
   - Update otp.blade.php with modern design
   - Add phone verification

2. **Build Home Page**
   - Implement geofencing UI
   - Featured hoardings carousel
   - Category quick filters
   - Recent searches

3. **Create Search Results**
   - Grid/list view toggle
   - Map view integration
   - Infinite scroll
   - Save search functionality

4. **Build Enquiry Flow**
   - Multi-step enquiry form
   - Offer comparison table
   - Accept/reject offers
   - Convert to quotation

5. **Complete Booking Flow**
   - Date picker for booking
   - Price calculator
   - Terms acceptance
   - Razorpay integration

6. **Build Profile Section**
   - Edit personal info
   - Upload documents for KYC
   - View KYC status
   - Change password

7. **Create Orders Page**
   - Filter by status
   - Search bookings
   - Export invoices
   - Track campaign status

8. **Build Communication**
   - Real-time thread updates
   - File attachments
   - Read receipts
   - Push notifications

---

## 🔒 Security Considerations

- CSRF protection on all forms
- Sanitize user inputs
- Rate limiting on API endpoints
- Secure file uploads (validate type, size)
- XSS prevention
- SQL injection protection (Eloquent ORM)

---

## 🚀 Performance Optimizations

- Lazy load images
- Minimize API calls (use caching)
- Debounce search inputs
- Paginate large lists
- Optimize database queries (eager loading)
- Use CDN for assets
- Minify CSS/JS

---

## 📚 Resources

- **Figma Design**: https://www.figma.com/design/IVKPt4p1lcnVswR8pUkkMS/Oohapp--Customer-Web---Updated
- **Bootstrap 5 Docs**: https://getbootstrap.com/docs/5.3
- **Laravel Docs**: https://laravel.com/docs/10.x
- **Geolocation API**: https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API

---

**Status**: 🟡 In Progress (Components & Auth Pages Complete, Core Pages Pending)
**Last Updated**: December 9, 2025

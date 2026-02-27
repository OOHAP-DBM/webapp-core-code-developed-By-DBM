# Import Module - Frontend Documentation

**Version:** 1.0  
**Status:** Production Ready ✅  
**Technology:** Laravel Blade + Tailwind CSS + Axios

---

## 📋 Overview

Complete responsive frontend for the Import module with:
- ✅ Modern, clean UI using Tailwind CSS
- ✅ Drag & drop file upload
- ✅ Real-time batch status tracking
- ✅ Error management with modal display
- ✅ Batch approval workflow
- ✅ Toast notifications
- ✅ Mobile-first responsive design
- ✅ Zero jQuery (vanilla JS + Axios)

---

## 🎨 UI Components

### 1. Upload Section (Left Sidebar)
**File:** `Resources/views/index.blade.php` (Lines 1-150)

**Features:**
- Excel (.xlsx) file upload with drag & drop
- PowerPoint (.pptx) file upload with drag & drop
- Media type selection (OOH/DOOH)
- File preview after selection
- Submit button with loading spinner
- Error message display
- Upload guidelines card

**CSS Classes:**
- `border-dashed` for drop zones
- `hover:bg-blue-50` for interactivity
- `disabled:opacity-50` for loading state
- `animate-spin` for spinner

**JavaScript:**
- `setupFileInputs()` - Initialize file inputs
- `setupDragDrop()` - Enable drag & drop
- `updateFileDisplay()` - Show selected file names
- `submitUpload()` - Post to `/api/import/upload`

### 2. Stats Cards (Right Column)
**Features:**
- Total Batches count
- Processing count
- Completed count
- Failed count
- Color-coded icons (blue, yellow, green, red)

**Data Source:**
- Refreshed every 5 seconds via `loadBatches()`
- Calculated from batch status

### 3. Batch List Table
**Features:**
- Responsive horizontal scroll on mobile
- Columns: ID, Type, Total, Valid, Invalid, Status, Date, Actions
- Status badges with color coding:
  - Gray: `uploaded`
  - Yellow: `processing`
  - Blue: `processed`
  - Green: `completed`
  - Red: `failed`

**Actions Column:**
- "Approve" button (if status = `processed`)
- "Errors" button (if invalid_rows > 0)
- "View" button (always visible)

**Data Source:**
- `GET /api/import` via Axios
- Cached and refreshed every 5 seconds

### 4. Approval Modal
**Features:**
- Confirmation dialog before approval
- Shows Batch ID and Valid Records count
- "Cancel" and "Approve" buttons
- Loading state on confirm
- Uses `POST /api/import/{batch}/approve`

**Functions:**
- `openApproveModal(batchId, validRows)` - Open modal
- `closeApproveModal()` - Close modal
- `confirmApprove()` - Submit approval

### 5. Error Details Modal
**Features:**
- Scrollable table of invalid records
- Columns: Row #, Code, Error Message
- Loads via `GET /api/import/{batch}/details`
- Close button and backdrop overlay
- Max width: 2xl (42rem)

**Functions:**
- `openErrorModal(batchId)` - Load and show errors
- `renderErrorTable(records)` - Render error data
- `closeErrorModal()` - Close modal

### 6. Toast Notifications
**Features:**
- Auto-dismiss after 4 seconds
- 3 types: success (green), error (red), info (blue)
- Positioned top-right
- Smooth fade animation
- Uses z-index 50

**Function:**
```javascript
showToast(message, type = 'info')
```

---

## 🚀 API Integration

### Routes

```
GET    /import              → Dashboard page
GET    /api/import          → List batches (JSON)
POST   /api/import/upload   → Upload files
GET    /api/import/{batch}/status   → Get batch status
GET    /api/import/{batch}/details  → Get batch details + errors
POST   /api/import/{batch}/approve  → Approve batch
DELETE /api/import/{batch}   → Cancel batch
```

### Authentication

All API endpoints require:
- Header: `Authorization: Bearer {token}`
- Middleware: `auth:sanctum`

Token retrieved via:
```javascript
getAuthToken() // From meta tag or data attribute
```

### Request/Response Examples

**Upload Files:**
```javascript
POST /api/import/upload
Content-Type: multipart/form-data

file: (binary)
ppt_file: (binary)
media_type: "ooh" | "dooh"

Response:
{
  "success": true,
  "data": {
    "batch_id": 1,
    "status": "uploaded",
    "total_rows": 100
  }
}
```

**List Batches:**
```javascript
GET /api/import

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "vendor_id": 5,
      "media_type": "ooh",
      "status": "processed",
      "total_rows": 100,
      "valid_rows": 95,
      "invalid_rows": 5,
      "created_at": "2025-02-18T10:30:00Z"
    }
  ]
}
```

**Approve Batch:**
```javascript
POST /api/import/1/approve

Response:
{
  "success": true,
  "data": {
    "batch_id": 1,
    "created_count": 95,
    "failed_count": 0,
    "total_processed": 95,
    "status": "completed"
  }
}
```

---

## 🎯 User Workflows

### Upload Workflow (5 Steps)

```
1. Select Excel & PPT files (optional)
   ├─ Drag & drop OR click to select
   ├─ File preview shows after selection
   └─ Max 20MB (excel) & 50MB (ppt)

2. Select Import Type
   ├─ OOH (default)
   └─ DOOH

3. Click "Upload Files"
   ├─ Button disabled + spinner
   ├─ Auto-refreshes batch list
   └─ Toast notification on success

4. Monitor Processing
   ├─ Status refreshes every 5 seconds
   ├─ Yellow badge while processing
   └─ Blue badge when processed

5. Review & Approve
   ├─ See invalid rows with errors
   ├─ Click "Approve" for valid batches
   └─ Green badge when completed
```

### Approval Workflow (4 Steps)

```
1. Click "Approve" button
   └─ Modal appears with confirmation

2. Review Details
   ├─ Batch ID shown
   ├─ Valid records count
   └─ Confirmation message

3. Click "Approve" in modal
   ├─ Button disabled + spinner
   ├─ Service creates hoardings atomically
   └─ Toast notification on success

4. Batch Completed
   ├─ Status changes to "completed"
   ├─ "Approve" button removed
   └─ Stats updated
```

### Error Handling Workflow (3 Steps)

```
1. Click "Errors" button
   ├─ Modal opens with loading
   └─ Fetches invalid records

2. View Error Table
   ├─ Row number
   ├─ Record code
   ├─ Error message
   └─ Scrollable if many errors

3. Decide Next Step
   ├─ Re-upload corrected file
   ├─ Contact support
   └─ Cancel batch
```

---

## 🛠️ JavaScript Functions

### Data Loading

```javascript
loadBatches()              // Load all batches from API
renderBatches(batches)     // Render to table
updateStats(batches)       // Update stat cards
```

### File Upload

```javascript
setupFileInputs()          // Init file input listeners
setupDragDrop(input)       // Enable drag & drop
updateFileDisplay(id, name) // Show file preview
submitUpload(e)            // Submit form via Axios
```

### Status & Badges

```javascript
getStatusBadge(status)     // Return HTML badge
formatDate(dateString)     // Format timestamp
```

### Modals

```javascript
openApproveModal(id, rows) // Show approval confirmation
closeApproveModal()        // Hide approval modal
openErrorModal(batchId)    // Load & show errors
renderErrorTable(records)  // Render error data
closeErrorModal()          // Hide error modal
```

### Notifications

```javascript
showToast(message, type)   // Show toast notification
displayErrors(errors)      // Show validation errors
showError(message)         // Show single error
```

### Utilities

```javascript
getAuthToken()             // Get bearer token
refreshBatches()           // Reload batch list
filterByStatus(status)     // Filter by status (placeholder)
loadBatchDetails(batchId)  // Load batch details
```

### Search

```javascript
// Real-time search on batch table
document.getElementById('searchInput')
  .addEventListener('input', (e) => { ... })
```

---

## 📱 Responsive Design

### Breakpoints (Tailwind)

| Breakpoint | Width | Usage |
|-----------|-------|-------|
| sm | 640px | Small tablets |
| lg | 1024px | Desktops |
| (no prefix) | All | Mobile |

### Layout Adjustments

**Mobile (< 1024px):**
- Stats cards stack vertically (1 column)
- Upload form takes full width
- Table scrolls horizontally
- Modals full-width with padding

**Desktop (≥ 1024px):**
- Upload form 1/3 width (left)
- Stats cards 2/3 width (right) in 2x2 grid
- Table full width below
- Modals centered max-w-2xl

### Key Classes

```tailwind
container mx-auto px-4 sm:px-6 lg:px-8  → Responsive padding
grid grid-cols-1 lg:grid-cols-3 gap-8   → Mobile-first grid
overflow-x-auto                          → Table scroll on mobile
max-h-screen                             → Modal height capped
```

---

## 🎨 Tailwind Design System

### Colors

| Component | Color | Tailwind Class |
|-----------|-------|----------------|
| Primary | Blue | bg-blue-600 |
| Success | Green | bg-green-600 |
| Warning | Yellow | bg-yellow-600 |
| Error | Red | bg-red-600 |
| Neutral | Gray | bg-gray-600 |

### Spacing

| Unit | Pixels | Usage |
|------|--------|-------|
| px-6 | 24px | Card padding |
| py-4 | 16px | Table cell padding |
| space-y-6 | 24px gap | Vertical spacing |
| gap-8 | 32px gap | Grid gap |

### Shadows

```tailwind
shadow-lg      → Cards at rest
shadow-xl      → Cards on hover (transition-shadow)
```

### Rounded Corners

```tailwind
rounded-xl     → Cards, modals, buttons
rounded-lg     → Form inputs, smaller elements
rounded-full   → Avatar placeholders
```

---

## 🔐 Security

### CSRF Protection

Blade view includes:
```html
{{ csrf_field() }}
<!-- OR -->
@csrf
```

### Authorization

- All routes require `auth` middleware
- API endpoints require `auth:sanctum`
- Policy checks via `$this->authorize()` in controller
- Only vendor's own batches visible

### Input Validation

**Client-side:**
- File type validation (accept attribute)
- File size validation (form request)

**Server-side:**
- Laravel FormRequest validation
- Custom validation rules
- Type casting and sanitization

---

## ⚡ Performance

### Optimization Strategies

1. **API Caching**
   - Batch list refreshes every 5 seconds
   - Individual batch loads on-demand
   - No unnecessary queries

2. **DOM Updates**
   - Table rendered once, updated incrementally
   - Toast notifications auto-remove
   - Modals use display:none (no DOM destruction)

3. **Animations**
   - CSS keyframes (efficient)
   - Limited to critical feedback (spinners, toasts)
   - Smooth transitions (duration-200 to 500)

4. **Bundle Size**
   - Axios only (no jQuery)
   - Tailwind utility classes (tree-shaken in production)
   - Vanilla JavaScript (no framework overhead)

---

## 🧪 Testing Guide

### Manual Testing

**1. Upload Files:**
```
Navigate to /import
Select Excel file
Select PPT file
Choose "OOH"
Click "Upload Files"
Verify:
  - Loading spinner shows
  - Button disabled while uploading
  - Success toast appears
  - Batch appears in table with "uploaded" status
```

**2. Monitor Processing:**
```
After upload:
- Wait for Python API to validate rows
- Status changes from "uploaded" → "processing" → "processed"
- Stats card shows processing count increment
- When complete: status => "processed", blue badge
```

**3. View Errors:**
```
Click "Errors" button
Modal opens showing invalid rows:
  - Row number
  - Record code
  - Error message
Close modal
```

**4. Approve Batch:**
```
Click "Approve" button
Modal asks for confirmation
Shows:
  - Batch ID
  - Valid records count
Click "Approve"
Verify:
  - Spinner shows
  - Toast confirms success
  - Status changes to "completed"
  - "Approve" button removed
  - Completed count increments
```

---

## 🐛 Debugging

### Browser Console

```javascript
// Check API connectivity
axios.get('/api/import', {
  headers: { 'Authorization': 'Bearer your-token' }
})

// Load current batches
console.log(window.currentBatches)

// Inspect auth token
console.log(getAuthToken())
```

### Network Tab (DevTools)

Monitor:
- `GET /api/import` - List batches
- `POST /api/import/upload` - Upload files
- `POST /api/import/{id}/approve` - Approve
- `GET /api/import/{id}/details` - Error details

### Common Issues

| Issue | Solution |
|-------|----------|
| API 401 (Unauthorized) | Check token in browser storage |
| API 403 (Forbidden) | Vendor must own batch |
| File not uploaded | Check file size & format |
| Modal won't close | Press Esc or click backdrop |
| Table not refreshing | Check network/console errors |

---

## 📁 File Structure

```
Modules/Import/Resources/views/
├── index.blade.php          (Complete dashboard - 800+ lines)
└── (Future: Additional components)

Routes/
├── web.php                  (Dashboard route)
└── api.php                  (API endpoints)
```

---

## 🚀 Installation & Setup

### 1. View Registration

Ensure ServiceProvider publishes views:

```php
// In ImportServiceProvider.php
$this->loadViewsFrom(__DIR__.'/../Resources/views', 'import');
```

### 2. Route Registration

Web route for dashboard:
```php
// Routes/web.php
Route::get('/', [ImportController::class, 'dashboard'])->name('dashboard');
```

### 3. Access Dashboard

Navigate to:
```
http://yourdomain.com/import
```

### 4. Verify Auth

Ensure user is authenticated with valid Sanctum token in:
- Local storage
- Session cookie
- Request header

---

## 🎯 Next Steps

### Enhancements

- [ ] Download batch template
- [ ] Bulk actions (select multiple, mass approve)
- [ ] Advanced filtering (date range, status)
- [ ] Export batch results to CSV
- [ ] Batch scheduling (import later)
- [ ] Progress bar during upload
- [ ] Real-time updates via WebSocket
- [ ] Dark mode toggle

### Integration Points

- Update main layout template
- Add navigation menu item
- Create sidebar widget
- Add permission gates
- Setup audit logging

---

## 📞 Support

**Issues?**

1. Check browser console for errors
2. Verify API endpoints are accessible
3. Confirm Sanctum token is valid
4. Check Laravel logs in `storage/logs/`
5. Test API directly with Postman

---

**Status: Production Ready ✅**

Dashboard available at: `/import`  
API available at: `/api/import`

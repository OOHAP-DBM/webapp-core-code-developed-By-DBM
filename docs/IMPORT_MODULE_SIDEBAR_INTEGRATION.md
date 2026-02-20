# Sidebar Integration - Import Module

## Overview
The Import module has been fully integrated into both Admin and Vendor sidebars with permission-based access control using Spatie Permission package. Only users with the `import.manage` permission can access the feature.

## Integration Steps

### 1. Run Permission Seeder
Create the `import.manage` permission and assign it to admin and vendor roles:

```bash
# Run the permission seeder
php artisan db:seed --class=ImportPermissionSeeder
```

This command:
- Creates `import.manage` permission
- Assigns it to `admin` role
- Assigns it to `vendor` role
- Logs the success message

### 2. Verify Sidebar Integration
The following files have been updated:

#### Admin Sidebar
- **File**: `resources/views/layouts/partials/admin/sidebar.blade.php`
- **Location**: After "All Hoardings" section
- **Visibility**: Wrapped with `@can('import.manage')` directive
- **Menu Item**: "Inventory Import"
- **Route**: `import.dashboard`
- **Active State**: `request()->routeIs('import.dashboard')`

#### Vendor Sidebar
- **File**: `resources/views/layouts/partials/vendor/sidebar.blade.php`
- **Location**: After "My Hoardings" section
- **Visibility**: Wrapped with `@can('import.manage')` directive
- **Menu Item**: "Inventory Import"
- **Route**: `import.dashboard`
- **Active State**: `request()->routeIs('import.dashboard')`

### 3. Route Configuration
The Import module routes have been updated with permission middleware:

```php
Route::middleware(['auth', 'permission:import.manage'])->group(function () {
    Route::get('/', [ImportController::class, 'dashboard'])
        ->name('dashboard');
});
```

**Route Name**: `import.dashboard`  
**URL**: `/import`  
**Middleware**: `auth`, `permission:import.manage`

### 4. Controller Enhancements

The `ImportController::dashboard()` method now includes role-based batch filtering:

```php
public function dashboard(): View
{
    $user = auth()->user();
    
    // Build query based on user role
    $batchesQuery = InventoryImportBatch::query()
        ->latest('created_at');
    
    // Filter by vendor_id if user is not admin
    if (!$user->hasRole('admin')) {
        $batchesQuery->where('vendor_id', $user->id);
    }
    
    $batches = $batchesQuery->paginate(15);
    
    return view('import::index', [
        'batches' => $batches,
        'isAdmin' => $user->hasRole('admin'),
    ]);
}
```

**Behavior**:
- **Admin**: Sees ALL import batches from all vendors
- **Vendor**: Sees only their own import batches (filtered by `vendor_id`)
- Batches are sorted by creation date (newest first)
- Pagination: 15 items per page

## Sidebar Menu Item HTML/Blade

### Admin Sidebar Code
```blade
@can('import.manage')
<div class="space-y-1">
    <a href="{{ route('import.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('import.dashboard') ? 'bg-[#00995c] text-white' : 'text-gray-700 hover:bg-gray-50' }}">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="3" y="3" width="7" height="7" rx="1" fill="currentColor"/>
            <rect x="14" y="3" width="7" height="7" rx="1" fill="currentColor"/>
            <rect x="3" y="14" width="7" height="7" rx="1" fill="currentColor"/>
            <path opacity="0.5" d="M14 14L21 14M21 14L19 12M21 14L19 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Inventory Import
    </a>
</div>
@endcan
```

### Vendor Sidebar Code
Identical to admin sidebar code - same menu item, same styling, same permissions.

## Styling Details

### Active State
- **Background**: `bg-[#00995c]` (Green)
- **Text**: `text-white` (White)
- **Trigger**: `request()->routeIs('import.dashboard')`

### Inactive State
- **Text Color**: `text-gray-700` (Dark Gray)
- **Hover Background**: `hover:bg-gray-50` (Light Gray)

### Icon
- **Size**: 24×24 pixels
- **Color**: `currentColor` (inherits from parent text color)
- **SVG**: Upload-related grid icon with arrow

## Sidebar Placement

### Admin Sidebar Hierarchy
```
├── Dashboard
├── All Hoardings (expandable)
├── Inventory Import ← NEW
├── My Order (expandable)
├── Display Enquiries (expandable)
├── Vendor Management (expandable)
├── Customer Management (expandable)
└── LogOut
```

### Vendor Sidebar Hierarchy
```
├── Dashboard
├── My Order (expandable)
├── Display Enquiries (expandable)
├── My Hoardings (expandable)
├── Inventory Import ← NEW
├── Settings
└── LogOut
```

## Permission Management

### Creating the Permission Manually (Alternative)
If the seeder doesn't run, create manually:

```php
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Create permission
$permission = Permission::create(['name' => 'import.manage', 'guard_name' => 'web']);

// Assign to admin role
$admin = Role::findByName('admin');
$admin->givePermissionTo('import.manage');

// Assign to vendor role
$vendor = Role::findByName('vendor');
$vendor->givePermissionTo('import.manage');
```

### Checking User Permission
In blade templates:
```blade
@can('import.manage')
    <!-- Menu item content -->
@endcan
```

In PHP code:
```php
if (auth()->user()->can('import.manage')) {
    // User can import
}
```

### Assigning/Revoking Permission Programmatically
```php
$user = auth()->user();

// Check if user has permission
$hasPermission = $user->hasPermissionTo('import.manage');

// Assign directly to user
$user->givePermissionTo('import.manage');

// Revoke from user
$user->revokePermissionFrom('import.manage');

// Check via role
$user->hasRole('admin'); // Admin users automatically have import.manage
$user->hasRole('vendor'); // Vendor users automatically have import.manage
```

## Testing Checklist

- [ ] Run permission seeder: `php artisan db:seed --class=ImportPermissionSeeder`
- [ ] Login as admin user
- [ ] Verify "Inventory Import" appears in admin sidebar
- [ ] Click menu item and verify dashboard loads
- [ ] Verify admin can see all batches
- [ ] Logout and login as vendor
- [ ] Verify "Inventory Import" appears in vendor sidebar
- [ ] Click menu item and verify dashboard loads
- [ ] Verify vendor only sees their own batches
- [ ] Logout and login as non-admin/vendor user
- [ ] Verify "Inventory Import" does NOT appear in sidebar
- [ ] Try accessing `/import` directly - should see permission denied
- [ ] Verify active state highlighting works (green background on Import menu)
- [ ] Verify hover state works (light gray background)

## File Changes Summary

| File | Changes |
|------|---------|
| `database/seeders/ImportPermissionSeeder.php` | NEW - Creates permission and assigns to roles |
| `resources/views/layouts/partials/admin/sidebar.blade.php` | Added Import menu item after All Hoardings |
| `resources/views/layouts/partials/vendor/sidebar.blade.php` | Added Import menu item after My Hoardings |
| `Modules/Import/Routes/web.php` | Added permission middleware to routes |
| `Modules/Import/Http/Controllers/ImportController.php` | Added role-based batch filtering to dashboard method |

## Troubleshooting

### Menu Item Not Appearing
**Problem**: User doesn't see "Inventory Import" in sidebar  
**Solutions**:
1. Verify seeder has been run: `php artisan db:seed --class=ImportPermissionSeeder`
2. Verify user has admin or vendor role: Check `role_user` table
3. Clear cache: `php artisan cache:clear`
4. Check user permissions: `php artisan tinker` → `auth()->user()->permissions`

### Route Not Found
**Problem**: Clicking menu item results in "404 Not Found"  
**Solutions**:
1. Verify routes are registered: `php artisan route:list | grep import`
2. Ensure `RouteServiceProvider` is loading module routes
3. Clear routes cache: `php artisan route:clear`

### Permission Middleware Error
**Problem**: "403 Forbidden - This action is unauthorized"  
**Solutions**:
1. User doesn't have `import.manage` permission
2. Run seeder again: `php artisan db:seed --class=ImportPermissionSeeder`
3. Assign permission manually in database `role_has_permissions` table

### Batches Not Loading
**Problem**: Dashboard loads but no batches shown  
**Solutions**:
1. Check `inventory_import_batches` table is created
2. Verify migrations are run: `php artisan migrate`
3. Check database connection
4. For vendors: ensure `vendor_id` matches `auth()->id()`
5. View database records: `php artisan tinker` → `Modules\Import\Entities\InventoryImportBatch::all()`

## Security Notes

1. **Permission Middleware**: The `permission:import.manage` middleware prevents unauthorized access to the import dashboard
2. **Role-Based Filtering**: Vendors automatically see only their own batches
3. **Soft Deletes**: Consider adding soft deletes to batches for audit trails
4. **Audit Logging**: All imports are logged with batch IDs for tracking
5. **File Validation**: Upload request validates file types and sizes

## API Integration Note

The sidebar only shows the web dashboard. API endpoints have separate route protection in `Modules/Import/Routes/api.php` and require `api:auth` middleware.

API Routes:
- `GET /api/import` - List batches
- `POST /api/import/upload` - Upload files
- `POST /api/import/{batch}/approve` - Approve batch
- `POST /api/import/{batch}/reject` - Reject batch
- And more...

All API routes also respect permission checks via Laravel authorization policies.

# Import Module Sidebar Integration - Quick Reference

## Quick Start (3 Steps)

### Step 1: Run Permission Seeder
```bash
php artisan db:seed --class=ImportPermissionSeeder
```

### Step 2: Clear Cache
```bash
php artisan cache:clear
php artisan route:clear
```

### Step 3: Test
- Login as admin → Should see "Inventory Import" in sidebar
- Login as vendor → Should see "Inventory Import" in sidebar
- Login as other role → Should NOT see "Inventory Import"

---

## Code Snippets

### 1. Permission Seeder (`database/seeders/ImportPermissionSeeder.php`)
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ImportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permission
        $permission = Permission::firstOrCreate(
            ['name' => 'import.manage'],
            ['guard_name' => 'web']
        );

        // Get or create roles
        $adminRole = Role::where('name', 'admin')->first();
        $vendorRole = Role::where('name', 'vendor')->first();

        // Assign permission to admin role
        if ($adminRole && !$adminRole->hasPermissionTo('import.manage')) {
            $adminRole->givePermissionTo('import.manage');
        }

        // Assign permission to vendor role
        if ($vendorRole && !$vendorRole->hasPermissionTo('import.manage')) {
            $vendorRole->givePermissionTo('import.manage');
        }

        $this->command->info('✓ Import permissions created and assigned successfully');
    }
}
```

### 2. Admin Sidebar Update (`resources/views/layouts/partials/admin/sidebar.blade.php`)
**Location**: After the closing `</div>` of "All Hoardings" expandable section (around line 213)

```blade
{{-- Import Menu Item --}}
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

### 3. Vendor Sidebar Update (`resources/views/layouts/partials/vendor/sidebar.blade.php`)
**Location**: After the closing `</div>` of "My Hoardings" expandable section (around line 305)

```blade
{{-- Import Menu Item --}}
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

### 4. Route Configuration (`Modules/Import/Routes/web.php`)
```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Import\Http\Controllers\ImportController;

/**
 * Import Module Web Routes
 *
 * Prefix: /import
 * Middleware: web, auth, permission
 */

Route::middleware(['auth', 'permission:import.manage'])->group(function () {
    // Import dashboard - requires import.manage permission
    Route::get('/', [ImportController::class, 'dashboard'])
        ->name('dashboard');
});
```

### 5. Controller Role-Based Filtering (`Modules/Import/Http/Controllers/ImportController.php`)
**Replace the `dashboard()` method:**

```php
/**
 * Show import dashboard with role-based batch filtering
 *
 * Admins see all batches, vendors see only their own batches
 *
 * @return View
 */
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

---

## Files Modified

| File | Action | Line # |
|------|--------|--------|
| `database/seeders/ImportPermissionSeeder.php` | CREATE | - |
| `resources/views/layouts/partials/admin/sidebar.blade.php` | MODIFY | ~213 |
| `resources/views/layouts/partials/vendor/sidebar.blade.php` | MODIFY | ~305 |
| `Modules/Import/Routes/web.php` | MODIFY | - |
| `Modules/Import/Http/Controllers/ImportController.php` | MODIFY | 20-40 |

---

## Verification Commands

```bash
# 1. Verify permission was created
php artisan tinker
>>> use Spatie\Permission\Models\Permission;
>>> Permission::where('name', 'import.manage')->first();

# 2. Verify admin has permission
>>> auth()->loginUsingId(1); // Login as user 1 (usually admin)
>>> auth()->user()->hasPermissionTo('import.manage');
// Should return: true

# 3. Verify vendor has permission
>>> auth()->loginUsingId(2); // Login as user 2 (test vendor)
>>> auth()->user()->hasPermissionTo('import.manage');
// Should return: true

# 4. Verify routes are registered
php artisan route:list | grep import
// Should show: GET  import/... import.dashboard

# 5. Check route name
>>> route('import.dashboard')
// Should return: /import
```

---

## Rollback Instructions (If Needed)

### Remove Permission
```php
// In artisan tinker:
use Spatie\Permission\Models\Permission;
Permission::where('name', 'import.manage')->delete();
```

### Revert Sidebar Changes
Remove or comment out the `@can('import.manage')` sections from both:
- `resources/views/layouts/partials/admin/sidebar.blade.php`
- `resources/views/layouts/partials/vendor/sidebar.blade.php`

### Revert Route Changes
```php
// Change back to just 'auth' middleware
Route::middleware(['auth'])->group(function () {
    Route::get('/', [ImportController::class, 'dashboard'])->name('dashboard');
});
```

---

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| Menu doesn't appear | Run seeder: `php artisan db:seed --class=ImportPermissionSeeder` |
| 403 Forbidden error | User missing `import.manage` - run seeder for role |
| 404 Not Found | Routes not cached properly - run `php artisan route:clear` |
| Vendor sees all batches | Check controller - vendor filter may not be applied |
| Styling different from others | Check Tailwind config for `#00995c` color |

---

## Permission System Overview

```
┌─────────────────────────────────────────────┐
│ Permission: import.manage                   │
├─────────────────────────────────────────────┤
│ Assigned to Roles:                          │
│  • admin                                     │
│  • vendor                                    │
├─────────────────────────────────────────────┤
│ Protects:                                    │
│  • Sidebar menu visibility                   │
│  • Route: GET /import (dashboard)            │
│  • API endpoints: /api/import/*              │
│  • Controller methods                        │
├─────────────────────────────────────────────┤
│ Checked By:                                  │
│  • @can('import.manage') - Blade            │
│  • auth()->can('import.manage') - PHP       │
│  • permission:import.manage - Middleware     │
└─────────────────────────────────────────────┘
```

---

## Database Changes

### New Permission Record (Spatie)
```sql
-- In: permissions table
INSERT INTO permissions (name, guard_name, created_at, updated_at) 
VALUES ('import.manage', 'web', NOW(), NOW());
```

### New Role-Permission Link
```sql
-- In: role_has_permissions table
-- After running seeder, should have 2 records:
-- | role_id (admin) | permission_id (import.manage) |
-- | role_id (vendor) | permission_id (import.manage) |
```

---

## Deployment Checklist

- [ ] Copy `ImportPermissionSeeder.php` to `database/seeders/`
- [ ] Update admin sidebar file with Import menu item
- [ ] Update vendor sidebar file with Import menu item
- [ ] Update `Modules/Import/Routes/web.php` with permission middleware
- [ ] Update `ImportController::dashboard()` with role-based filtering
- [ ] Run seeder: `php artisan db:seed --class=ImportPermissionSeeder`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Clear routes: `php artisan route:clear`
- [ ] Test as admin user
- [ ] Test as vendor user
- [ ] Test as other role (should not see menu)
- [ ] Verify active state highlighting
- [ ] Verify data filtering works correctly

---

## Support & Debugging

For more details, see: `docs/IMPORT_MODULE_SIDEBAR_INTEGRATION.md`

For Import module overview, see: `docs/IMPORT_MODULE_DOCUMENTATION.md`

For frontend implementation details, see: `docs/IMPORT_FRONTEND_GUIDE.md`

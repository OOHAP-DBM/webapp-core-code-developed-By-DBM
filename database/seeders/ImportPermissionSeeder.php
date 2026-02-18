<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ImportPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates 'import.manage' permission and assigns to admin & vendor roles
     */
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

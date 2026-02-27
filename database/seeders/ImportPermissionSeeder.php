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
        $permissions = [
            'import.manage',
            'import.batch.view',
            'import.batch.create',
            'import.batch.update',
            'import.batch.delete',
            'import.batch.approve',
            'import.row.view',
            'import.row.create',
            'import.row.update',
            'import.row.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Get or create roles
        $adminRole = Role::where('name', 'admin')->first();
        $vendorRole = Role::where('name', 'vendor')->first();

        // Assign all import permissions to admin role
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // Assign vendor import permissions
        if ($vendorRole) {
            $vendorRole->givePermissionTo([
                'import.manage',
                'import.batch.view',
                'import.batch.create',
                'import.batch.update',
                'import.batch.delete',
                'import.batch.approve',
                'import.row.view',
                'import.row.create',
                'import.row.update',
                'import.row.delete',
            ]);
        }

        $this->command->info('✓ Import permissions created and assigned successfully');
    }
}

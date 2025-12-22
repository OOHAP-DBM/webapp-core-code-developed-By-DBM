<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // --------------------
        // Permissions
        // --------------------
        $permissions = [
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Hoarding Management
            'hoardings.view',
            'hoardings.create',
            'hoardings.edit',
            'hoardings.delete',
            'hoardings.approve',

            // DOOH
            'dooh.view',
            'dooh.create',
            'dooh.edit',
            'dooh.delete',
            'dooh.approve',

            // Enquiries
            'enquiries.view',
            'enquiries.create',
            'enquiries.respond',
            'enquiries.delete',

            // Offers
            'offers.view',
            'offers.create',
            'offers.edit',
            'offers.delete',

            // Quotations
            'quotations.view',
            'quotations.create',
            'quotations.edit',
            'quotations.delete',
            'quotations.approve',

            // Bookings
            'bookings.view',
            'bookings.create',
            'bookings.edit',
            'bookings.cancel',
            'bookings.approve',

            // Payments
            'payments.view',
            'payments.process',
            'payments.refund',
            'payments.payouts',

            // Vendors
            'vendors.view',
            'vendors.approve',
            'vendors.suspend',
            'vendors.delete',

            // KYC
            'kyc.view',
            'kyc.submit',
            'kyc.approve',
            'kyc.reject',

            // Staff
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
            'staff.assign',

            // Settings
            'settings.view',
            'settings.edit',

            // Reports
            'reports.view',
            'reports.export',

            // Notifications
            'notifications.send',

            // Media
            'media.upload',
            'media.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        // --------------------
        // Roles
        // --------------------

        // ADMIN → FULL ACCESS
        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web']
        );
        $admin->syncPermissions(Permission::all());

        // Vendor
        $vendor = Role::firstOrCreate(['name' => 'vendor']);
        $vendor->syncPermissions([
            'hoardings.view',
            'hoardings.create',
            'hoardings.edit',
            'hoardings.delete',
            'dooh.view',
            'dooh.create',
            'dooh.edit',
            'dooh.delete',
            'enquiries.view',
            'enquiries.respond',
            'offers.view',
            'offers.create',
            'offers.edit',
            'offers.delete',
            'quotations.view',
            'quotations.create',
            'quotations.edit',
            'bookings.view',
            'bookings.approve',
            'payments.view',
            'kyc.view',
            'kyc.submit',
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.assign',
            'reports.view',
            'media.upload',
            'media.delete',
        ]);

        // Subvendor
        $subvendor = Role::firstOrCreate(['name' => 'subvendor']);
        $subvendor->syncPermissions([
            'hoardings.view',
            'hoardings.create',
            'hoardings.edit',
            'dooh.view',
            'dooh.create',
            'dooh.edit',
            'enquiries.view',
            'enquiries.respond',
            'offers.view',
            'offers.create',
            'quotations.view',
            'bookings.view',
            'payments.view',
            'media.upload',
        ]);

        // Customer
        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->syncPermissions([
            'hoardings.view',
            'dooh.view',
            'enquiries.view',
            'enquiries.create',
            'offers.view',
            'quotations.view',
            'bookings.view',
            'bookings.create',
            'bookings.cancel',
            'payments.view',
            'media.upload',
        ]);

        // Staff
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->syncPermissions([
            'bookings.view',
            'media.upload',
        ]);

        $this->command->info('✅ Roles & permissions seeded successfully');
        $this->command->info('Roles: admin, vendor, subvendor, customer, staff');
        $this->command->info('Permissions: ' . count($permissions));
    }
}

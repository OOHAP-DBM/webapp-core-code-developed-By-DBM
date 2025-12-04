<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
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
            
            // DOOH Management
            'dooh.view',
            'dooh.create',
            'dooh.edit',
            'dooh.delete',
            'dooh.approve',
            
            // Enquiry Management
            'enquiries.view',
            'enquiries.create',
            'enquiries.respond',
            'enquiries.delete',
            
            // Offer Management
            'offers.view',
            'offers.create',
            'offers.edit',
            'offers.delete',
            
            // Quotation Management
            'quotations.view',
            'quotations.create',
            'quotations.edit',
            'quotations.delete',
            'quotations.approve',
            
            // Booking Management
            'bookings.view',
            'bookings.create',
            'bookings.edit',
            'bookings.cancel',
            'bookings.approve',
            
            // Payment Management
            'payments.view',
            'payments.process',
            'payments.refund',
            'payments.payouts',
            
            // Vendor Management
            'vendors.view',
            'vendors.approve',
            'vendors.suspend',
            'vendors.delete',
            
            // KYC Management
            'kyc.view',
            'kyc.submit',
            'kyc.approve',
            'kyc.reject',
            
            // Staff Management
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
            'staff.assign',
            
            // Settings Management
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
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin - All permissions
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - Most permissions except critical system settings
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'users.view', 'users.create', 'users.edit',
            'hoardings.view', 'hoardings.approve',
            'dooh.view', 'dooh.approve',
            'enquiries.view',
            'offers.view',
            'quotations.view', 'quotations.approve',
            'bookings.view', 'bookings.approve',
            'payments.view', 'payments.process', 'payments.refund', 'payments.payouts',
            'vendors.view', 'vendors.approve', 'vendors.suspend',
            'kyc.view', 'kyc.approve', 'kyc.reject',
            'staff.view',
            'settings.view',
            'reports.view', 'reports.export',
            'notifications.send',
        ]);

        // Vendor - Manage own inventory and bookings
        $vendor = Role::create(['name' => 'vendor']);
        $vendor->givePermissionTo([
            'hoardings.view', 'hoardings.create', 'hoardings.edit', 'hoardings.delete',
            'dooh.view', 'dooh.create', 'dooh.edit', 'dooh.delete',
            'enquiries.view', 'enquiries.respond',
            'offers.view', 'offers.create', 'offers.edit', 'offers.delete',
            'quotations.view', 'quotations.create', 'quotations.edit',
            'bookings.view', 'bookings.approve',
            'payments.view',
            'kyc.view', 'kyc.submit',
            'staff.view', 'staff.create', 'staff.edit', 'staff.assign',
            'reports.view',
            'media.upload', 'media.delete',
        ]);

        // Subvendor - Limited vendor permissions
        $subvendor = Role::create(['name' => 'subvendor']);
        $subvendor->givePermissionTo([
            'hoardings.view', 'hoardings.create', 'hoardings.edit',
            'dooh.view', 'dooh.create', 'dooh.edit',
            'enquiries.view', 'enquiries.respond',
            'offers.view', 'offers.create',
            'quotations.view',
            'bookings.view',
            'payments.view',
            'media.upload',
        ]);

        // Customer - Browse and book
        $customer = Role::create(['name' => 'customer']);
        $customer->givePermissionTo([
            'hoardings.view',
            'dooh.view',
            'enquiries.view', 'enquiries.create',
            'offers.view',
            'quotations.view',
            'bookings.view', 'bookings.create', 'bookings.cancel',
            'payments.view',
            'media.upload',
        ]);

        // Staff - Task execution (Designer, Printer, Mounter, Surveyor)
        $staff = Role::create(['name' => 'staff']);
        $staff->givePermissionTo([
            'bookings.view',
            'media.upload',
        ]);

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Created roles: super_admin, admin, vendor, subvendor, customer, staff');
        $this->command->info('Created ' . count($permissions) . ' permissions');
    }
}

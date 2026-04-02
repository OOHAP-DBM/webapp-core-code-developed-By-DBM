<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the admin role exists
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Create or update the default admin user
        $admin = User::updateOrCreate(
            [
                'email' => 'admin@oohapp.com',
            ],
            [
                'name' => 'Admin',
                'password' => Hash::make('ChangeThis!'), // Change after first login
                'status' => 'active',
            ]
        );

        // Assign admin role if not already assigned
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }
    }
}

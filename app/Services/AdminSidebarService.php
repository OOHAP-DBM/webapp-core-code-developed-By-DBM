<?php

namespace App\Services;

use App\Models\VendorProfile;
use App\Models\User;

class AdminSidebarService
{
    /**
     * Get counts for sidebar badges
     * @return array
     */
    public function getSidebarCounts(): array
    {
        // Requested Vendors count
        $requestedVendorCount = VendorProfile::where('onboarding_status', 'pending_approval')->count();

        // Total Customers count
        $customers = User::where('active_role', 'customer')
            ->select('id', 'name', 'email', 'created_at')
            ->orderByDesc('created_at')
            ->get();

        $totalCustomerCount = $customers->count();

        return [
            'requestedVendorCount' => $requestedVendorCount,
            'totalCustomerCount' => $totalCustomerCount,
        ];
    }
}

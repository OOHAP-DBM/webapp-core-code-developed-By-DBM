<?php

namespace App\Policies;

use App\Models\User;

class BrandManagerPolicy
{
    /**
     * Determine if the user can access the brand manager features.
     */
    public function access(User $user): bool
    {
        return $user->hasRole('brand_manager');
    }

    /**
     * Example: Determine if the user can manage campaigns for assigned brands/customers.
     */
    public function manageCampaigns(User $user, $customerOrBrand): bool
    {
        // Implement logic to check if $user is assigned to $customerOrBrand
        return $user->hasRole('brand_manager');
    }
}

<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
// use App\Models\Booking;
// use App\Models\Hoarding;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Services\Vendor\VendorDashboardService;
class DashboardController extends Controller
{

  public function __construct(private VendorDashboardService $dashboardService)
    {
    }
    /**
     * Vendor dashboard data for mobile API.
     *
     * GET /api/vendor/dashboard
     */
    public function index(): JsonResponse
    {
        $vendor = Auth::user();
        $userId = $vendor->id;
        $profile = $vendor->vendorProfile;

        $onboardingStatus = $profile->onboarding_status ?? 'not_started';
        $onboardingStep   = $profile->onboarding_step ?? 1;

        /* ─── ONBOARDING GUARD ───────────────────────────────────── */
        if (!$profile || !in_array($profile->onboarding_status, ['pending_approval', 'approved'])) {
            $step = $profile ? $profile->onboarding_step : 1;

            return response()->json([
                'success'         => false,
                'onboarding'      => true,
                'onboarding_step' => $step,
                'message'         => 'Please complete your vendor onboarding.',
            ], 403);
        }

              $stats        = $this->dashboardService->getStats($userId);
        $topHoardings = $this->dashboardService->getTopHoardings($userId);
        $topCustomers = $this->dashboardService->getTopCustomers($userId);
        $transactions = $this->dashboardService->getRecentTransactions($userId);
        /* ─── RESPONSE ───────────────────────────────────────────── */
        return response()->json([
            'success' => true,
            'data'    => [
                'stats'         => $stats,
                'top_hoardings' => $topHoardings,
                'top_customers' => $topCustomers,
                'transactions'  => $transactions,
            ],
             'onboarding' => [
                'status' => $onboardingStatus,
                'step'   => $onboardingStep,
            ],
        ], 200);
    }
}
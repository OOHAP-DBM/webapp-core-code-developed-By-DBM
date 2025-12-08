<?php

namespace Modules\DOOH\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\DOOH\Services\DOOHPackageBookingService;
use Modules\DOOH\Services\DOOHInventoryApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * DOOH Package Booking API Controller
 * Handles DOOH screen browsing, package selection, and bookings
 */
class DOOHPackageBookingController extends Controller
{
    protected DOOHPackageBookingService $bookingService;
    protected DOOHInventoryApiService $inventoryService;

    public function __construct(
        DOOHPackageBookingService $bookingService,
        DOOHInventoryApiService $inventoryService
    ) {
        $this->bookingService = $bookingService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get available DOOH screens
     * GET /api/v1/customer/dooh/screens
     */
    public function getScreens(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['city', 'state', 'search', 'min_slots', 'per_page']);
            
            $screens = $this->bookingService->getAvailableScreens($filters);

            return response()->json([
                'success' => true,
                'data' => $screens,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch DOOH screens', [
                'error' => $e->getMessage(),
                'filters' => $filters ?? [],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch screens',
            ], 500);
        }
    }

    /**
     * Get screen details with packages
     * GET /api/v1/customer/dooh/screens/{id}
     */
    public function getScreenDetails(int $id): JsonResponse
    {
        try {
            $screen = $this->bookingService->getScreenDetails($id);

            if (!$screen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Screen not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $screen,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch screen details', [
                'screen_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch screen details',
            ], 500);
        }
    }

    /**
     * Check package availability
     * POST /api/v1/customer/dooh/packages/{id}/check-availability
     */
    public function checkAvailability(Request $request, int $packageId): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $availability = $this->bookingService->checkPackageAvailability(
                $packageId,
                $validated['start_date'],
                $validated['end_date']
            );

            return response()->json([
                'success' => true,
                'data' => $availability,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to check package availability', [
                'package_id' => $packageId,
                'dates' => $validated,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create DOOH booking
     * POST /api/v1/customer/dooh/bookings
     */
    public function createBooking(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dooh_package_id' => 'required|exists:dooh_packages,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'customer_notes' => 'nullable|string|max:1000',
            'survey_required' => 'nullable|boolean',
        ]);

        try {
            $validated['customer_id'] = Auth::id();

            $booking = $this->bookingService->createBooking($validated);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $booking,
            ], 201);

        } catch (Exception $e) {
            Log::error('Failed to create DOOH booking', [
                'user_id' => Auth::id(),
                'data' => $validated,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get booking details
     * GET /api/v1/customer/dooh/bookings/{id}
     */
    public function getBooking(int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getCustomerBookings(Auth::id(), [])
                ->where('id', $id)
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $booking->load(['screen', 'package', 'vendor']),
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch booking', [
                'booking_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch booking',
            ], 500);
        }
    }

    /**
     * Initiate payment for booking
     * POST /api/v1/customer/dooh/bookings/{id}/initiate-payment
     */
    public function initiatePayment(int $id): JsonResponse
    {
        try {
            $paymentData = $this->bookingService->initiatePayment($id, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => $paymentData,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to initiate DOOH payment', [
                'booking_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Confirm payment
     * POST /api/v1/customer/dooh/bookings/{id}/confirm-payment
     */
    public function confirmPayment(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        try {
            $booking = $this->bookingService->confirmPayment(
                $id,
                Auth::id(),
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed successfully',
                'data' => $booking,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to confirm DOOH payment', [
                'booking_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Upload content files
     * POST /api/v1/customer/dooh/bookings/{id}/upload-content
     */
    public function uploadContent(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'files' => 'required|array|min:1|max:5',
            'files.*' => 'required|file|mimes:mp4,mov,avi,jpg,jpeg,png,gif|max:51200', // 50MB
        ]);

        try {
            $booking = $this->bookingService->uploadContent(
                $id,
                Auth::id(),
                $request->file('files')
            );

            return response()->json([
                'success' => true,
                'message' => 'Content uploaded successfully',
                'data' => $booking,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to upload DOOH content', [
                'booking_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel booking
     * POST /api/v1/customer/dooh/bookings/{id}/cancel
     */
    public function cancelBooking(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $booking = $this->bookingService->cancelBooking(
                $id,
                Auth::id(),
                $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'data' => $booking,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to cancel DOOH booking', [
                'booking_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get customer's bookings list
     * GET /api/v1/customer/dooh/bookings
     */
    public function getCustomerBookings(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'start_date', 'per_page']);
            
            $bookings = $this->bookingService->getCustomerBookings(Auth::id(), $filters);

            return response()->json([
                'success' => true,
                'data' => $bookings,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch customer DOOH bookings', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bookings',
            ], 500);
        }
    }

    // ============================================
    // VENDOR ENDPOINTS
    // ============================================

    /**
     * Get vendor's bookings
     * GET /api/v1/vendor/dooh/bookings
     */
    public function getVendorBookings(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'screen_id', 'per_page']);
            
            $bookings = $this->bookingService->getVendorBookings(Auth::id(), $filters);

            return response()->json([
                'success' => true,
                'data' => $bookings,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch vendor DOOH bookings', [
                'vendor_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bookings',
            ], 500);
        }
    }

    /**
     * Approve content
     * POST /api/v1/vendor/dooh/bookings/{id}/approve-content
     */
    public function approveContent(int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->approveContent($id, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Content approved successfully',
                'data' => $booking,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to approve DOOH content', [
                'booking_id' => $id,
                'vendor_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reject content
     * POST /api/v1/vendor/dooh/bookings/{id}/reject-content
     */
    public function rejectContent(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $booking = $this->bookingService->rejectContent(
                $id,
                Auth::id(),
                $validated['reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Content rejected',
                'data' => $booking,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to reject DOOH content', [
                'booking_id' => $id,
                'vendor_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Sync screens from external API
     * POST /api/v1/vendor/dooh/sync-screens
     */
    public function syncScreens(): JsonResponse
    {
        try {
            $result = $this->inventoryService->syncScreens(Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Screens synced successfully',
                'data' => $result,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to sync DOOH screens', [
                'vendor_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test API connection
     * GET /api/v1/vendor/dooh/test-connection
     */
    public function testConnection(): JsonResponse
    {
        try {
            $connected = $this->inventoryService->testConnection();

            return response()->json([
                'success' => $connected,
                'message' => $connected ? 'API connection successful' : 'API connection failed',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace Modules\DOOH\Services;

use Modules\DOOH\Models\DOOHScreen;
use Modules\DOOH\Models\DOOHPackage;
use Modules\DOOH\Models\DOOHBooking;
use Modules\Settings\Services\SettingsService;
use App\Services\RazorpayService;
use App\Services\TaxService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

/**
 * DOOH Package Booking Service
 * Handles DOOH screen bookings based on packages with slot allocation
 */
class DOOHPackageBookingService
{
    protected SettingsService $settingsService;
    protected RazorpayService $razorpayService;
    protected TaxService $taxService;

    public function __construct(
        SettingsService $settingsService,
        RazorpayService $razorpayService,
        TaxService $taxService
    ) {
        $this->settingsService = $settingsService;
        $this->razorpayService = $razorpayService;
        $this->taxService = $taxService;
    }

    /**
     * Get available DOOH screens with filters
     */
    public function getAvailableScreens(array $filters = [])
    {
        $query = DOOHScreen::with(['vendor', 'activePackages'])
            ->active()
            ->synced();

        // Filter by city
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        // Filter by state
        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        // Search by name/address
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('address', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Filter by minimum slots available
        if (!empty($filters['min_slots'])) {
            $query->where('available_slots_per_day', '>=', $filters['min_slots']);
        }

        // Pagination
        $perPage = $filters['per_page'] ?? 15;
        
        return $query->latest()->paginate($perPage);
    }

    /**
     * Get screen with packages and availability
     */
    public function getScreenDetails(int $screenId): ?DOOHScreen
    {
        return DOOHScreen::with([
            'vendor',
            'activePackages' => function ($query) {
                $query->orderBy('slots_per_day');
            }
        ])->find($screenId);
    }

    /**
     * Check package availability for given dates
     */
    public function checkPackageAvailability(
        int $packageId,
        string $startDate,
        string $endDate
    ): array {
        $package = DOOHPackage::with('screen')->findOrFail($packageId);
        $screen = $package->screen;

        // Check if dates are valid
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->isPast()) {
            return [
                'available' => false,
                'message' => 'Start date cannot be in the past',
            ];
        }

        if ($end->lessThanOrEqualTo($start)) {
            return [
                'available' => false,
                'message' => 'End date must be after start date',
            ];
        }

        // Calculate duration in months
        $durationMonths = $start->diffInMonths($end);
        
        if ($durationMonths < $package->min_booking_months) {
            return [
                'available' => false,
                'message' => "Minimum booking period is {$package->min_booking_months} months",
            ];
        }

        if ($durationMonths > $package->max_booking_months) {
            return [
                'available' => false,
                'message' => "Maximum booking period is {$package->max_booking_months} months",
            ];
        }

        // Check if slots are available
        $availableSlots = $screen->getAvailableSlots($startDate, $endDate);
        
        if ($availableSlots < $package->slots_per_day) {
            return [
                'available' => false,
                'message' => 'Not enough slots available for this package',
                'available_slots' => $availableSlots,
                'required_slots' => $package->slots_per_day,
            ];
        }

        // Check minimum booking amount
        $pricing = $this->calculatePricing($package, $startDate, $endDate);
        
        if ($pricing['grand_total'] < $screen->minimum_booking_amount) {
            return [
                'available' => false,
                'message' => "Minimum booking amount is ₹" . number_format($screen->minimum_booking_amount, 2),
                'minimum_amount' => $screen->minimum_booking_amount,
                'calculated_amount' => $pricing['grand_total'],
            ];
        }

        return [
            'available' => true,
            'message' => 'Package is available for booking',
            'pricing' => $pricing,
        ];
    }

    /**
     * Calculate pricing for package booking
     */
    public function calculatePricing(
        DOOHPackage $package,
        string $startDate,
        string $endDate
    ): array {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $durationDays = $start->diffInDays($end);
        $durationMonths = max(1, ceil($durationDays / 30)); // Round up to nearest month

        // Base package price
        $packagePrice = $package->price_per_month;
        $totalAmount = $packagePrice * $durationMonths;

        // Apply package discount
        $discountAmount = 0;
        if ($package->discount_percent > 0) {
            $discountAmount = ($totalAmount * $package->discount_percent) / 100;
        }

        // Calculate tax using TaxService
        $taxableAmount = $totalAmount - $discountAmount;
        $taxResult = $this->taxService->calculateGST($taxableAmount, [
            'applies_to' => 'booking',
        ]);
        $taxAmount = $taxResult['gst_amount'];
        $taxRate = $taxResult['gst_rate'];

        // Grand total
        $grandTotal = $taxableAmount + $taxAmount;

        // Total slots
        $totalSlots = $package->slots_per_day * $durationDays;

        return [
            'package_price' => round($packagePrice, 2),
            'duration_days' => $durationDays,
            'duration_months' => $durationMonths,
            'total_amount' => round($totalAmount, 2),
            'discount_percent' => $package->discount_percent,
            'discount_amount' => round($discountAmount, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => round($taxAmount, 2),
            'grand_total' => round($grandTotal, 2),
            'total_slots' => $totalSlots,
            'slots_per_day' => $package->slots_per_day,
            'slot_frequency_minutes' => $package->loop_interval_minutes,
        ];
    }

    /**
     * Create DOOH package booking
     */
    public function createBooking(array $data): DOOHBooking
    {
        return DB::transaction(function () use ($data) {
            // Validate package and availability
            $package = DOOHPackage::with('screen')->findOrFail($data['dooh_package_id']);
            $screen = $package->screen;

            $availability = $this->checkPackageAvailability(
                $package->id,
                $data['start_date'],
                $data['end_date']
            );

            if (!$availability['available']) {
                throw new Exception($availability['message']);
            }

            // Calculate pricing
            $pricing = $availability['pricing'];

            // Generate booking number
            $bookingNumber = DOOHBooking::generateBookingNumber();

            // Create booking snapshot
            $snapshot = $this->createBookingSnapshot($screen, $package, $data);

            // Determine if survey is required
            $surveyRequired = $data['survey_required'] ?? false;
            $surveyStatus = $surveyRequired ? DOOHBooking::SURVEY_STATUS_PENDING : DOOHBooking::SURVEY_STATUS_NOT_REQUIRED;

            // Create booking
            $booking = DOOHBooking::create([
                'dooh_screen_id' => $screen->id,
                'dooh_package_id' => $package->id,
                'customer_id' => $data['customer_id'],
                'vendor_id' => $screen->vendor_id,
                'booking_number' => $bookingNumber,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'duration_months' => $pricing['duration_months'],
                'duration_days' => $pricing['duration_days'],
                'slots_per_day' => $pricing['slots_per_day'],
                'total_slots' => $pricing['total_slots'],
                'slot_frequency_minutes' => $pricing['slot_frequency_minutes'],
                'package_price' => $pricing['package_price'],
                'total_amount' => $pricing['total_amount'],
                'discount_amount' => $pricing['discount_amount'],
                'tax_amount' => $pricing['tax_amount'],
                'grand_total' => $pricing['grand_total'],
                'payment_status' => DOOHBooking::PAYMENT_STATUS_PENDING,
                'status' => DOOHBooking::STATUS_DRAFT,
                'booking_snapshot' => $snapshot,
                'customer_notes' => $data['customer_notes'] ?? null,
                'survey_required' => $surveyRequired,
                'survey_status' => $surveyStatus,
            ]);

            Log::info('DOOH booking created', [
                'booking_id' => $booking->id,
                'booking_number' => $bookingNumber,
                'screen_id' => $screen->id,
                'package_id' => $package->id,
                'customer_id' => $data['customer_id'],
            ]);

            return $booking->load(['screen', 'package', 'customer', 'vendor']);
        });
    }

    /**
     * Create booking snapshot
     */
    protected function createBookingSnapshot(
        DOOHScreen $screen,
        DOOHPackage $package,
        array $data
    ): array {
        return [
            'screen' => [
                'id' => $screen->id,
                'name' => $screen->name,
                'address' => $screen->address,
                'city' => $screen->city,
                'state' => $screen->state,
                'screen_type' => $screen->screen_type,
                'resolution' => $screen->resolution,
                'screen_size' => $screen->screen_size,
                'slot_duration_seconds' => $screen->slot_duration_seconds,
                'loop_duration_seconds' => $screen->loop_duration_seconds,
            ],
            'package' => [
                'id' => $package->id,
                'package_name' => $package->package_name,
                'package_type' => $package->package_type,
                'slots_per_day' => $package->slots_per_day,
                'loop_interval_minutes' => $package->loop_interval_minutes,
                'price_per_month' => $package->price_per_month,
                'discount_percent' => $package->discount_percent,
            ],
            'vendor' => [
                'id' => $screen->vendor->id,
                'name' => $screen->vendor->name,
                'email' => $screen->vendor->email,
                'phone' => $screen->vendor->phone ?? null,
            ],
            'captured_at' => Carbon::now()->toDateTimeString(),
        ];
    }

    /**
     * Initiate payment for booking
     */
    public function initiatePayment(int $bookingId, int $customerId): array
    {
        $booking = DOOHBooking::where('id', $bookingId)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        if ($booking->status !== DOOHBooking::STATUS_DRAFT) {
            throw new Exception('Booking is not in draft status');
        }

        // Create Razorpay order
        $receipt = "DOOH-{$booking->booking_number}";
        
        try {
            $order = $this->razorpayService->createOrder(
                $booking->grand_total,
                'INR',
                $receipt,
                'manual' // Manual capture for 30-min hold
            );

            // Update booking with payment details
            $holdMinutes = $this->settingsService->get('booking_hold_minutes', 30);
            
            $booking->update([
                'razorpay_order_id' => $order['id'],
                'hold_expiry_at' => Carbon::now()->addMinutes($holdMinutes),
                'status' => DOOHBooking::STATUS_PAYMENT_PENDING,
            ]);

            Log::info('DOOH payment initiated', [
                'booking_id' => $booking->id,
                'order_id' => $order['id'],
                'amount' => $booking->grand_total,
            ]);

            return [
                'success' => true,
                'order_id' => $order['id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
                'razorpay_key' => config('services.razorpay.key_id'),
                'booking' => $booking,
            ];

        } catch (Exception $e) {
            Log::error('DOOH payment initiation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            
            throw new Exception('Payment initiation failed: ' . $e->getMessage());
        }
    }

    /**
     * Confirm payment and capture
     */
    public function confirmPayment(
        int $bookingId,
        int $customerId,
        array $paymentData
    ): DOOHBooking {
        return DB::transaction(function () use ($bookingId, $customerId, $paymentData) {
            $booking = DOOHBooking::where('id', $bookingId)
                ->where('customer_id', $customerId)
                ->lockForUpdate()
                ->firstOrFail();

            // Validate payment data
            if (empty($paymentData['razorpay_payment_id']) || 
                empty($paymentData['razorpay_order_id'])) {
                throw new Exception('Invalid payment data');
            }

            // Verify order ID matches
            if ($booking->razorpay_order_id !== $paymentData['razorpay_order_id']) {
                throw new Exception('Order ID mismatch');
            }

            // Capture payment
            try {
                $captureResponse = $this->razorpayService->capturePayment(
                    $paymentData['razorpay_payment_id'],
                    $booking->grand_total
                );

                $booking->update([
                    'razorpay_payment_id' => $paymentData['razorpay_payment_id'],
                    'payment_status' => DOOHBooking::PAYMENT_STATUS_CAPTURED,
                    'payment_captured_at' => Carbon::now(),
                    'status' => DOOHBooking::STATUS_CONFIRMED,
                    'confirmed_at' => Carbon::now(),
                ]);

                // Move to content pending if content review is required
                $contentReviewRequired = $this->settingsService->get('dooh_content_review_required', true);
                if ($contentReviewRequired) {
                    $booking->update(['status' => DOOHBooking::STATUS_CONTENT_PENDING]);
                }

                Log::info('DOOH payment confirmed', [
                    'booking_id' => $booking->id,
                    'payment_id' => $paymentData['razorpay_payment_id'],
                ]);

                return $booking->load(['screen', 'package']);

            } catch (Exception $e) {
                Log::error('DOOH payment capture failed', [
                    'booking_id' => $booking->id,
                    'payment_id' => $paymentData['razorpay_payment_id'],
                    'error' => $e->getMessage(),
                ]);

                throw new Exception('Payment capture failed: ' . $e->getMessage());
            }
        });
    }

    /**
     * Upload content files for booking
     */
    public function uploadContent(int $bookingId, int $customerId, array $files): DOOHBooking
    {
        $booking = DOOHBooking::where('id', $bookingId)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        if (!in_array($booking->status, [
            DOOHBooking::STATUS_CONFIRMED,
            DOOHBooking::STATUS_CONTENT_PENDING,
            DOOHBooking::STATUS_CONTENT_APPROVED
        ])) {
            throw new Exception('Cannot upload content for this booking');
        }

        $uploadedFiles = [];
        $screen = $booking->screen;
        $allowedFormats = $screen->allowed_formats ?? ['mp4', 'jpg', 'png', 'gif'];
        $maxFileSize = $screen->max_file_size_mb * 1024 * 1024; // Convert to bytes

        foreach ($files as $file) {
            // Validate file
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $allowedFormats)) {
                throw new Exception("File format .{$extension} is not allowed. Allowed: " . implode(', ', $allowedFormats));
            }

            if ($file->getSize() > $maxFileSize) {
                throw new Exception("File size exceeds maximum limit of {$screen->max_file_size_mb}MB");
            }

            // Store file
            $path = Storage::disk('public')->putFile(
                "dooh_content/{$booking->id}",
                $file
            );

            $uploadedFiles[] = [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'extension' => $extension,
                'uploaded_at' => Carbon::now()->toDateTimeString(),
            ];
        }

        // Update booking with content files
        $existingFiles = $booking->content_files ?? [];
        $allFiles = array_merge($existingFiles, $uploadedFiles);

        $booking->update([
            'content_files' => $allFiles,
            'content_status' => DOOHBooking::CONTENT_STATUS_PENDING,
            'status' => DOOHBooking::STATUS_CONTENT_PENDING,
        ]);

        Log::info('DOOH content uploaded', [
            'booking_id' => $booking->id,
            'files_count' => count($uploadedFiles),
        ]);

        return $booking->fresh();
    }

    /**
     * Approve content (Admin/Vendor)
     */
    public function approveContent(int $bookingId, int $approverId): DOOHBooking
    {
        $booking = DOOHBooking::findOrFail($bookingId);

        if ($booking->content_status !== DOOHBooking::CONTENT_STATUS_PENDING) {
            throw new Exception('Content is not pending approval');
        }

        $booking->update([
            'content_status' => DOOHBooking::CONTENT_STATUS_APPROVED,
            'content_approved_at' => Carbon::now(),
            'content_approved_by' => $approverId,
            'status' => DOOHBooking::STATUS_CONTENT_APPROVED,
        ]);

        // If start date is today or in past, activate campaign
        if (Carbon::parse($booking->start_date)->lessThanOrEqualTo(Carbon::today())) {
            $booking->update([
                'status' => DOOHBooking::STATUS_ACTIVE,
                'campaign_started_at' => Carbon::now(),
            ]);
        }

        Log::info('DOOH content approved', [
            'booking_id' => $booking->id,
            'approved_by' => $approverId,
        ]);

        return $booking->fresh();
    }

    /**
     * Reject content
     */
    public function rejectContent(int $bookingId, int $rejectorId, string $reason): DOOHBooking
    {
        $booking = DOOHBooking::findOrFail($bookingId);

        $booking->update([
            'content_status' => DOOHBooking::CONTENT_STATUS_REJECTED,
            'content_rejection_reason' => $reason,
        ]);

        Log::info('DOOH content rejected', [
            'booking_id' => $booking->id,
            'rejected_by' => $rejectorId,
            'reason' => $reason,
        ]);

        return $booking->fresh();
    }

    /**
     * Cancel booking with auto-refund within 30 minutes
     */
    public function cancelBooking(
        int $bookingId,
        int $customerId,
        string $reason
    ): DOOHBooking {
        return DB::transaction(function () use ($bookingId, $customerId, $reason) {
            $booking = DOOHBooking::where('id', $bookingId)
                ->where('customer_id', $customerId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($booking->status === DOOHBooking::STATUS_CANCELLED) {
                throw new Exception('Booking is already cancelled');
            }

            // Check if within refund window
            $canRefund = $booking->isWithinCancellationWindow() && 
                        $booking->payment_status === DOOHBooking::PAYMENT_STATUS_CAPTURED;

            if ($canRefund) {
                try {
                    // Process refund
                    $refund = $this->razorpayService->createRefund(
                        $booking->razorpay_payment_id,
                        $booking->grand_total
                    );

                    $booking->update([
                        'refund_id' => $refund['id'],
                        'refund_amount' => $booking->grand_total,
                        'refunded_at' => Carbon::now(),
                        'payment_status' => DOOHBooking::PAYMENT_STATUS_REFUNDED,
                    ]);

                    Log::info('DOOH booking refunded', [
                        'booking_id' => $booking->id,
                        'refund_id' => $refund['id'],
                        'amount' => $booking->grand_total,
                    ]);

                } catch (Exception $e) {
                    Log::error('DOOH refund failed', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                    
                    throw new Exception('Refund failed: ' . $e->getMessage());
                }
            }

            // Cancel booking
            $booking->update([
                'status' => DOOHBooking::STATUS_CANCELLED,
                'cancelled_at' => Carbon::now(),
                'cancellation_reason' => $reason,
            ]);

            Log::info('DOOH booking cancelled', [
                'booking_id' => $booking->id,
                'refunded' => $canRefund,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Get customer bookings
     */
    public function getCustomerBookings(int $customerId, array $filters = [])
    {
        $query = DOOHBooking::with(['screen', 'package', 'vendor'])
            ->byCustomer($customerId);

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (!empty($filters['start_date'])) {
            $query->where('start_date', '>=', $filters['start_date']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->latest()->paginate($perPage);
    }

    /**
     * Get vendor bookings
     */
    public function getVendorBookings(int $vendorId, array $filters = [])
    {
        $query = DOOHBooking::with(['screen', 'package', 'customer'])
            ->byVendor($vendorId);

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by screen
        if (!empty($filters['screen_id'])) {
            $query->where('dooh_screen_id', $filters['screen_id']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->latest()->paginate($perPage);
    }
}

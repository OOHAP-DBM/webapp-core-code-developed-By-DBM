<?php

namespace Modules\POS\Services;

use Modules\POS\Models\POSBooking;
use Modules\Hoardings\Models\Hoarding;
use Modules\Settings\Services\SettingsService;
use App\Services\TaxService;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class POSBookingService
{
    protected SettingsService $settingsService;
    protected TaxService $taxService;
    protected InvoiceService $invoiceService;

    public function __construct(
        SettingsService $settingsService,
        TaxService $taxService,
        InvoiceService $invoiceService
    ) {
        $this->settingsService = $settingsService;
        $this->taxService = $taxService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Create a new POS booking
     */
    public function createBooking(array $data): POSBooking
    {
        return DB::transaction(function () use ($data) {
            // Validate hoarding availability
            if (isset($data['hoarding_id'])) {
                $this->validateHoardingAvailability(
                    $data['hoarding_id'],
                    $data['start_date'],
                    $data['end_date']
                );
            }

            // Calculate pricing
            $pricing = $this->calculatePricing($data);

            // Note: GST-compliant invoice will be generated after booking creation

            // Determine payment status and hold expiry based on payment mode
            $paymentMode = $data['payment_mode'] ?? POSBooking::PAYMENT_MODE_CASH;
            $paymentStatus = POSBooking::PAYMENT_STATUS_UNPAID;
            $holdExpiryAt = null;
            
            // Set hold expiry for payment modes that require payment
            $holdDays = 7; // Grace period before auto-release
            if (in_array($paymentMode, [
                POSBooking::PAYMENT_MODE_CASH,
                POSBooking::PAYMENT_MODE_BANK_TRANSFER,
                POSBooking::PAYMENT_MODE_CHEQUE,
                'online' // For backward compatibility
            ])) {
                $holdExpiryAt = now()->addDays($holdDays);
            }

            // Credit note handling (no hold, status = credit)
            $creditNoteData = [];
            if ($paymentMode === POSBooking::PAYMENT_MODE_CREDIT_NOTE) {
                $creditNoteDays = $this->getCreditNoteDays();
                $paymentStatus = POSBooking::PAYMENT_STATUS_CREDIT;
                $holdExpiryAt = null; // No hold for credit notes
                $creditNoteData = [
                    'credit_note_number' => $this->generateCreditNoteNumber(),
                    'credit_note_date' => now(),
                    'credit_note_due_date' => now()->addDays($creditNoteDays),
                    'credit_note_status' => 'active',
                    'payment_status' => POSBooking::PAYMENT_STATUS_CREDIT,
                ];
            }

            // Auto-approval handling
            $approvalData = [];
            if ($this->isAutoApprovalEnabled()) {
                $approvalData = [
                    'auto_approved' => true,
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                    'status' => POSBooking::STATUS_CONFIRMED,
                    'confirmed_at' => now(),
                ];
            }

            // Create booking
            $booking = POSBooking::create(array_merge([
                'vendor_id' => Auth::id(),
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'],
                'customer_address' => $data['customer_address'] ?? null,
                'customer_gstin' => $data['customer_gstin'] ?? null,
                'booking_type' => $data['booking_type'] ?? 'ooh',
                'hoarding_id' => $data['hoarding_id'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'duration_type' => $data['duration_type'] ?? 'days',
                'duration_days' => $this->calculateDurationDays($data['start_date'], $data['end_date']),
                'payment_mode' => $paymentMode,
                'payment_status' => $paymentStatus,
                'hold_expiry_at' => $holdExpiryAt,
                'reminder_count' => 0,
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_notes' => $data['payment_notes'] ?? null,
                'notes' => $data['notes'] ?? null,
                'booking_snapshot' => $this->createBookingSnapshot($data),
            ], $pricing, $creditNoteData, $approvalData));

            // PROMPT 64: Generate GST-compliant invoice if auto-invoice enabled
            if ($this->isAutoInvoiceEnabled()) {
                try {
                    $invoice = $this->invoiceService->generateInvoiceForBooking(
                        $booking,
                        null, // No payment record for POS
                        \App\Models\Invoice::TYPE_POS,
                        Auth::id()
                    );
                    
                    // Store invoice reference in POS booking
                    $booking->update([
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_date' => $invoice->invoice_date,
                        'invoice_path' => $invoice->pdf_path,
                    ]);
                    
                    Log::info('POS invoice generated', [
                        'pos_booking_id' => $booking->id,
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to generate POS invoice', [
                        'pos_booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't fail the booking if invoice fails
                }
            }

            return $booking->fresh();
        });
    }

    /**
     * Update POS booking
     */
    public function updateBooking(POSBooking $booking, array $data): POSBooking
    {
        return DB::transaction(function () use ($booking, $data) {
            // Validate hoarding availability if dates changed
            if (isset($data['hoarding_id']) || isset($data['start_date']) || isset($data['end_date'])) {
                $hoardingId = $data['hoarding_id'] ?? $booking->hoarding_id;
                $startDate = $data['start_date'] ?? $booking->start_date;
                $endDate = $data['end_date'] ?? $booking->end_date;

                $this->validateHoardingAvailability($hoardingId, $startDate, $endDate, $booking->id);
            }

            // Recalculate pricing if relevant fields changed
            if (isset($data['base_amount']) || isset($data['discount_amount'])) {
                $pricing = $this->calculatePricing(array_merge($booking->toArray(), $data));
                $data = array_merge($data, $pricing);
            }

            $booking->update($data);
            return $booking->fresh();
        });
    }

    /**
     * Mark payment as cash collected
     */
    public function markAsCashCollected(POSBooking $booking, float $amount, ?string $reference = null): POSBooking
    {
        return DB::transaction(function () use ($booking, $amount, $reference) {
            $booking->update([
                'payment_mode' => POSBooking::PAYMENT_MODE_CASH,
                'payment_status' => POSBooking::PAYMENT_STATUS_PAID,
                'paid_amount' => $amount,
                'payment_reference' => $reference,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Mark as credit note
     */
    public function markAsCreditNote(POSBooking $booking, ?int $validityDays = null): POSBooking
    {
        return DB::transaction(function () use ($booking, $validityDays) {
            $days = $validityDays ?? $this->getCreditNoteDays();

            $booking->update([
                'payment_mode' => POSBooking::PAYMENT_MODE_CREDIT_NOTE,
                'payment_status' => POSBooking::PAYMENT_STATUS_CREDIT,
                'credit_note_number' => POSBooking::generateCreditNoteNumber(),
                'credit_note_date' => now(),
                'credit_note_due_date' => now()->addDays($days),
                'credit_note_status' => POSBooking::CREDIT_NOTE_STATUS_ACTIVE,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Cancel credit note
     */
    public function cancelCreditNote(POSBooking $booking, string $reason): POSBooking
    {
        return DB::transaction(function () use ($booking, $reason) {
            if (!$booking->isCreditNote()) {
                throw new \Exception('This booking is not a credit note');
            }

            $booking->update([
                'credit_note_status' => POSBooking::CREDIT_NOTE_STATUS_CANCELLED,
                'payment_status' => POSBooking::PAYMENT_STATUS_UNPAID,
                'cancellation_reason' => $reason,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Cancel booking
     */
    public function cancelBooking(POSBooking $booking, string $reason): POSBooking
    {
        return DB::transaction(function () use ($booking, $reason) {
            $booking->update([
                'status' => POSBooking::STATUS_CANCELLED,
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Validate hoarding availability
     */
    protected function validateHoardingAvailability(int $hoardingId, string $startDate, string $endDate, ?int $excludeBookingId = null): void
    {
        // Check regular bookings
        $conflictingBookings = DB::table('bookings')
            ->where('hoarding_id', $hoardingId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->whereIn('status', ['confirmed', 'payment_hold'])
            ->when($excludeBookingId, function ($query, $excludeBookingId) {
                return $query->where('id', '!=', $excludeBookingId);
            })
            ->count();

        // Check POS bookings
        $conflictingPOSBookings = POSBooking::where('hoarding_id', $hoardingId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->whereIn('status', [POSBooking::STATUS_CONFIRMED, POSBooking::STATUS_ACTIVE])
            ->when($excludeBookingId, function ($query, $excludeBookingId) {
                return $query->where('id', '!=', $excludeBookingId);
            })
            ->count();

        if ($conflictingBookings > 0 || $conflictingPOSBookings > 0) {
            throw new \Exception('Hoarding not available for the selected dates. There are conflicting bookings.');
        }
    }

    /**
     * Calculate pricing
     */
    protected function calculatePricing(array $data): array
    {
        $baseAmount = $data['base_amount'] ?? 0;
        $discountAmount = $data['discount_amount'] ?? 0;
        $gstRate = $this->getGSTRate();

        $amountAfterDiscount = $baseAmount - $discountAmount;
        $taxAmount = ($amountAfterDiscount * $gstRate) / 100;
        $totalAmount = $amountAfterDiscount + $taxAmount;

        return [
            'base_amount' => $baseAmount,
            'discount_amount' => $discountAmount,
            'tax_amount' => round($taxAmount, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * Calculate duration in days
     */
    protected function calculateDurationDays(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        return $start->diffInDays($end) + 1; // +1 to include both start and end dates
    }

    /**
     * Create booking snapshot
     */
    protected function createBookingSnapshot(array $data): array
    {
        $hoarding = null;
        if (isset($data['hoarding_id'])) {
            $hoarding = Hoarding::with('vendor')->find($data['hoarding_id']);
        }

        return [
            'created_at' => now()->toIso8601String(),
            'created_by' => Auth::user()->name ?? 'System',
            'hoarding_details' => $hoarding ? [
                'id' => $hoarding->id,
                'title' => $hoarding->title,
                'location' => $hoarding->location_address,
                'size' => $hoarding->size,
                'type' => $hoarding->type,
            ] : null,
            'settings_snapshot' => [
                'auto_approval' => $this->isAutoApprovalEnabled(),
                'auto_invoice' => $this->isAutoInvoiceEnabled(),
                'gst_rate' => $this->getGSTRate(),
            ],
        ];
    }

    /**
     * Check if auto-approval is enabled
     */
    public function isAutoApprovalEnabled(): bool
    {
        return (bool) $this->settingsService->get('pos_auto_approval', true);
    }

    /**
     * Check if auto-invoice is enabled
     */
    public function isAutoInvoiceEnabled(): bool
    {
        return (bool) $this->settingsService->get('pos_auto_invoice', true);
    }

    /**
     * Check if cash payment is allowed
     */
    public function isCashPaymentAllowed(): bool
    {
        return (bool) $this->settingsService->get('pos_allow_cash_payment', true);
    }

    /**
     * Check if credit note is allowed
     */
    public function isCreditNoteAllowed(): bool
    {
        return (bool) $this->settingsService->get('pos_allow_credit_note', true);
    }

    /**
     * Get credit note validity days
     */
    public function getCreditNoteDays(): int
    {
        return (int) $this->settingsService->get('pos_credit_note_days', 30);
    }

    /**
     * Get GST rate (backwards compatible)
     */
    // public function getGSTRate(): float
    // {
    //     return $this->taxService->getDefaultTaxRate('booking');
    // }

    /**
     * Check if SMS notification is enabled
     */
    public function isSMSNotificationEnabled(): bool
    {
        return (bool) $this->settingsService->get('pos_enable_sms_notification', true);
    }

    /**
     * Check if WhatsApp notification is enabled
     */
    public function isWhatsAppNotificationEnabled(): bool
    {
        return (bool) $this->settingsService->get('pos_enable_whatsapp_notification', true);
    }

    /**
     * Check if email notification is enabled
     */
    public function isEmailNotificationEnabled(): bool
    {
        return (bool) $this->settingsService->get('pos_enable_email_notification', true);
    }

    /**
     * Get vendor's POS bookings
     */
    public function getVendorBookings(int $vendorId, array $filters = [])
    {
        $query = POSBooking::with(['hoarding', 'customer', 'approver'])
            ->forVendor($vendorId);

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['payment_status'])) {
            $query->byPaymentStatus($filters['payment_status']);
        }

        if (isset($filters['booking_type'])) {
            $query->where('booking_type', $filters['booking_type']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhere('credit_note_number', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get booking statistics for vendor
     */
    public function getVendorStatistics(int $vendorId): array
    {
        $bookings = POSBooking::forVendor($vendorId);

        return [
            'total_bookings' => $bookings->count(),
            'active_bookings' => $bookings->active()->count(),
            'total_revenue' => $bookings->where('payment_status', POSBooking::PAYMENT_STATUS_PAID)->sum('total_amount'),
            'pending_payments' => $bookings->unpaid()->sum('total_amount'),
            'active_credit_notes' => $bookings->creditNotes()->count(),
            'credit_notes_value' => $bookings->creditNotes()->sum('total_amount'),
        ];
    }

    /**
     * CRITICAL: Mark booking payment as received
     * Transitions unpaid → paid
     * Clears hold_expiry_at to allow campaign to start
     */
    public function markPaymentReceived(POSBooking $booking, float $amount, \Carbon\Carbon $paymentDate, ?string $notes = null): POSBooking
    {
        return DB::transaction(function () use ($booking, $amount, $paymentDate, $notes) {
            // Validate state
            if (!in_array($booking->payment_status, ['unpaid', 'partial'])) {
                throw new \Exception('Booking is not in payable state');
            }

            // Determine if full or partial payment
            $newStatus = abs($amount - $booking->total_amount) < 0.01 
                ? POSBooking::PAYMENT_STATUS_PAID 
                : POSBooking::PAYMENT_STATUS_PARTIAL;

            // Update booking
            $booking->update([
                'paid_amount' => $amount,
                'payment_status' => $newStatus,
                'payment_received_at' => $paymentDate,
                'hold_expiry_at' => null, // Clear hold when payment received
                'reminder_count' => 0,    // Reset reminders
                'last_reminder_at' => null,
            ]);

            // Log payment transaction
            Log::info('Payment marked as received', [
                'booking_id' => $booking->id,
                'vendor_id' => $booking->vendor_id,
                'amount' => $amount,
                'total' => $booking->total_amount,
                'status' => $newStatus,
                'notes' => $notes,
            ]);

            // TODO: Unblock hoarding inventory (when release logic is added)
            // $this->releaseHoardingInventory($booking);

            return $booking->fresh();
        });
    }

    /**
     * CRITICAL: Release booking hold (free hoarding, mark as cancelled)
     * Used for order cancellations or customer rejections
     * Transitions: unpaid → released
     */
    public function releaseBooking(POSBooking $booking, string $reason): POSBooking
    {
        return DB::transaction(function () use ($booking, $reason) {
            // Validate state
            if ($booking->payment_status !== POSBooking::PAYMENT_STATUS_UNPAID) {
                throw new \Exception('Can only release unpaid bookings');
            }

            if (!in_array($booking->status, ['draft', 'confirmed'])) {
                throw new \Exception('Cannot release started campaigns');
            }

            // Cancel the booking
            $booking->update([
                'status' => POSBooking::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'hold_expiry_at' => null,
                'reminder_count' => 0,
                'last_reminder_at' => null,
            ]);

            Log::info('Booking released/cancelled', [
                'booking_id' => $booking->id,
                'vendor_id' => $booking->vendor_id,
                'reason' => $reason,
            ]);

            // TODO: Release hoarding inventory here
            // $this->releaseHoardingInventory($booking);

            return $booking->fresh();
        });
    }

    /**
     * Helper: Generate credit note number
     */
    private function generateCreditNoteNumber(): string
    {
        $prefix = 'CN-' . date('Y');
        $latest = POSBooking::where('credit_note_number', 'like', $prefix . '%')
            ->latest()
            ->first();

        $number = 1001; // Start from 1001
        if ($latest && preg_match('/CN-\d+-(\d+)/', $latest->credit_note_number, $matches)) {
            $number = (int)$matches[1] + 1;
        }

        return $prefix . '-' . $number;
    }

    /**
     * Helper: Get GST rate from settings
     */
    public function getGSTRate(): float
    {
        return (float) ($this->settingsService->get('pos_gst_rate') ?? 18);
    }
//    public function getGSTRate(): float
//     {
//         return $this->taxService->getDefaultTaxRate('booking');
//     }
    /**
     * Helper: Check if auto-approval is enabled
     */
    // private function isAutoApprovalEnabled(): bool
    // {
    //     return (bool) $this->settingsService->get('pos_auto_approve', false);
    // }

    /**
     * Helper: Check if auto-invoice generation is enabled
     */
    // private function isAutoInvoiceEnabled(): bool
    // {
    //     return (bool) $this->settingsService->get('pos_auto_invoice', true);
    // }

    /**
     * Helper: Get credit note validity days from settings
     */
    // private function getCreditNoteDays(): int
    // {
    //     return (int) ($this->settingsService->get('pos_credit_note_days') ?? 30);
    // }
}


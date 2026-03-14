<?php

namespace Modules\POS\Services;

use Modules\POS\Models\POSBooking;
use  App\Models\Hoarding;
use Modules\Hoardings\Services\HoardingBookingService;
use Modules\Settings\Services\SettingsService;
use App\Services\TaxService;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;
class POSBookingService
  
{
    protected SettingsService $settingsService;
    protected TaxService $taxService;
    protected InvoiceService $invoiceService;
    protected HoardingBookingService $hoardingBookingService;

    public function __construct(
        SettingsService $settingsService,
        TaxService $taxService,
        InvoiceService $invoiceService,
        HoardingBookingService $hoardingBookingService
    ) {
        $this->settingsService = $settingsService;
        $this->taxService = $taxService;
        $this->invoiceService = $invoiceService;
        $this->hoardingBookingService = $hoardingBookingService;
    }

    /**
     * Create a new POS booking
     */
    // public function createBooking(array $data): POSBooking
    // {
    //     return DB::transaction(function () use ($data) {
    //         // Validate hoarding availability
    //         if (isset($data['hoarding_id'])) {
    //             $this->validateHoardingAvailability(
    //                 $data['hoarding_id'],
    //                 $data['start_date'],
    //                 $data['end_date']
    //             );
    //         }

    //         // Calculate pricing
    //         $pricing = $this->calculatePricing($data);

    //         // Note: GST-compliant invoice will be generated after booking creation

    //         // Determine payment status and hold expiry based on payment mode
    //         $paymentMode = $data['payment_mode'] ?? POSBooking::PAYMENT_MODE_CASH;
    //         $paymentStatus = POSBooking::PAYMENT_STATUS_UNPAID;
    //         $holdExpiryAt = null;
            
    //         // Set hold expiry for payment modes that require payment
    //         $holdDays = 7; // Grace period before auto-release
    //         if (in_array($paymentMode, [
    //             POSBooking::PAYMENT_MODE_CASH,
    //             POSBooking::PAYMENT_MODE_BANK_TRANSFER,
    //             POSBooking::PAYMENT_MODE_CHEQUE,
    //             'online' // For backward compatibility
    //         ])) {
    //             $holdExpiryAt = now()->addDays($holdDays);
    //         }

    //         // Credit note handling (no hold, status = credit)
    //         $creditNoteData = [];
    //         if ($paymentMode === POSBooking::PAYMENT_MODE_CREDIT_NOTE) {
    //             $creditNoteDays = $this->getCreditNoteDays();
    //             $paymentStatus = POSBooking::PAYMENT_STATUS_CREDIT;
    //             $holdExpiryAt = null; // No hold for credit notes
    //             $creditNoteData = [
    //                 'credit_note_number' => $this->generateCreditNoteNumber(),
    //                 'credit_note_date' => now(),
    //                 'credit_note_due_date' => now()->addDays($creditNoteDays),
    //                 'credit_note_status' => 'active',
    //                 'payment_status' => POSBooking::PAYMENT_STATUS_CREDIT,
    //             ];
    //         }

    //         // Auto-approval handling
    //         $approvalData = [];
    //         if ($this->isAutoApprovalEnabled()) {
    //             $approvalData = [
    //                 'auto_approved' => true,
    //                 'approved_at' => now(),
    //                 'approved_by' => Auth::id(),
    //                 'status' => POSBooking::STATUS_CONFIRMED,
    //                 'confirmed_at' => now(),
    //             ];
    //         }

    //         // Create booking
    //         $booking = POSBooking::create(array_merge([
    //             'vendor_id' => Auth::id(),
    //             'customer_name' => $data['customer_name'],
    //             'customer_email' => $data['customer_email'] ?? null,
    //             'customer_phone' => $data['customer_phone'],
    //             'customer_address' => $data['customer_address'] ?? null,
    //             'customer_gstin' => $data['customer_gstin'] ?? null,
    //             'booking_type' => $data['booking_type'] ?? 'ooh',
    //             'hoarding_id' => $data['hoarding_id'] ?? null,
    //             'start_date' => $data['start_date'],
    //             'end_date' => $data['end_date'],
    //             'duration_type' => $data['duration_type'] ?? 'days',
    //             'duration_days' => $this->calculateDurationDays($data['start_date'], $data['end_date']),
    //             'payment_mode' => $paymentMode,
    //             'payment_status' => $paymentStatus,
    //             'hold_expiry_at' => $holdExpiryAt,
    //             'reminder_count' => 0,
    //             'payment_reference' => $data['payment_reference'] ?? null,
    //             'payment_notes' => $data['payment_notes'] ?? null,
    //             'notes' => $data['notes'] ?? null,
    //             'booking_snapshot' => $this->createBookingSnapshot($data),
    //         ], $pricing, $creditNoteData, $approvalData));

    //         // PROMPT 64: Generate GST-compliant invoice if auto-invoice enabled
    //         if ($this->isAutoInvoiceEnabled()) {
    //             try {
    //                 $invoice = $this->invoiceService->generateInvoiceForBooking(
    //                     $booking,
    //                     null, // No payment record for POS
    //                     \App\Models\Invoice::TYPE_POS,
    //                     Auth::id()
    //                 );
                    
    //                 // Store invoice reference in POS booking
    //                 $booking->update([
    //                     'invoice_number' => $invoice->invoice_number,
    //                     'invoice_date' => $invoice->invoice_date,
    //                     'invoice_path' => $invoice->pdf_path,
    //                 ]);
                    
    //                 Log::info('POS invoice generated', [
    //                     'pos_booking_id' => $booking->id,
    //                     'invoice_id' => $invoice->id,
    //                     'invoice_number' => $invoice->invoice_number,
    //                 ]);
    //             } catch (\Exception $e) {
    //                 Log::error('Failed to generate POS invoice', [
    //                     'pos_booking_id' => $booking->id,
    //                     'error' => $e->getMessage(),
    //                 ]);
    //                 // Don't fail the booking if invoice fails
    //             }
    //         }

    //         return $booking->fresh();
    //     });
    // }
//     public function createBooking(array $data): POSBooking
// {
//     return DB::transaction(function () use ($data) {
//         // 1. DATA NORMALIZATION (Fixes the 422 error)
//         // If the frontend sends 'booking_date', use it for start/end if they are missing
//         $fromDate = $data['start_date'] ?? $data['booking_date'] ?? now()->format('Y-m-d');
//         $toDate = $data['end_date'] ?? $data['booking_date'] ?? now()->format('Y-m-d');
        
//         $hoardingIds = $data['hoarding_ids'] ?? [];
//         if (empty($hoardingIds)) {
//             throw new \Illuminate\Validation\ValidationException(null, ['hoarding_ids' => 'At least one hoarding must be selected']);
//         }

//         // 2. AVAILABILITY CHECK WITH LOCKING
//         // Fetch and lock hoardings to prevent double-booking during this request
//         $hoardings = Hoarding::whereIn('id', $hoardingIds)->lockForUpdate()->get();

//         if ($hoardings->count() !== count($hoardingIds)) {
//             throw new \Exception('One or more selected hoardings are invalid.');
//         }

//         foreach ($hoardings as $hoarding) {
//             if (!$this->checkAvailability($hoarding->id, $fromDate, $toDate)) {
//                 throw new \Exception("Hoarding '{$hoarding->title}' is already booked for these dates.");
//             }
//         }

//         // 3. DYNAMIC PRICING CALCULATION
//         // Calculate total based on the sum of individual hoarding rates
//         $calc = $this->calculateMultiHoardingPricing($hoardings, $fromDate, $toDate, $data['discount_amount'] ?? 0);

//         // 4. CREATE MAIN BOOKING RECORD
//         $booking = POSBooking::create([
//             'vendor_id' => Auth::id(),
//             'customer_id' => $data['customer_id'] ?? null,
//             'customer_name' => $data['customer_name'] ?? 'Walk-in Customer',
//             'customer_email' => $data['customer_email'] ?? null,
//             'customer_phone' => $data['customer_phone'] ?? null,
//             'customer_address' => $data['customer_address'] ?? null,
//             'customer_gstin' => $data['customer_gstin'] ?? null,
//             'booking_type' => $data['booking_type'] ?? 'ooh',
//             'start_date' => $fromDate,
//             'end_date' => $toDate,
//             'duration_days' => $this->calculateDurationDays($fromDate, $toDate),
//             'base_amount' => $calc['base_amount'],
//             'discount_amount' => $calc['discount_amount'],
//             'tax_amount' => $calc['tax_amount'],
//             'total_amount' => $calc['total_amount'],
//             'payment_mode' => $data['payment_mode'] ?? 'cash',
//             'payment_status' => POSBooking::PAYMENT_STATUS_UNPAID,
//             'status' => 'pending_payment',
//             'notes' => $data['notes'] ?? null,
//             'booking_snapshot' => $this->createMultiBookingSnapshot($hoardings),
//         ]);

//         // 5. ATTACH HOARDINGS & SET HOLDS
//         foreach ($hoardings as $hoarding) {
//             POSBookingHoarding::create([
//                 'pos_booking_id' => $booking->id,
//                 'hoarding_id' => $hoarding->id,
//                 'start_date' => $fromDate,
//                 'end_date' => $toDate,
//                 'duration_days' => $booking->duration_days,
//                 'status' => 'pending',
//             ]);

//             // Mark hoarding as on hold
//             $hoarding->update([
//                 'is_on_hold' => true,
//                 'hold_till' => now()->addMinutes($this->settingsService->get('pos_hold_minutes', 15)),
//                 'held_by_booking_id' => $booking->id
//             ]);
//         }

//         return $booking->fresh(['bookingHoardings.hoarding']);
//     });
// }

public function createBooking(array $data): POSBooking
{
    Log::info('POSBookingService.createBooking start', ['vendor_id' => ($data['vendor_id'] ?? Auth::id()), 'data_preview' => array_intersect_key($data, array_flip(['hoarding_ids','start_date','end_date','payment_mode','customer_id']))]);
    Log::info('', ['full_data' => $data]); // Log full data for debugging (remove in production)
    return DB::transaction(function () use ($data) {
        $effectiveVendorId = (int) ($data['vendor_id'] ?? Auth::id());
        // Normalization: Ensure dates exist
        $start = $data['start_date'] ?? $data['booking_date'] ?? null;
        $end = $data['end_date'] ?? $data['booking_date'] ?? null;

        if (!$start || !$end) {
            Log::warning('POSBookingService.createBooking missing dates', ['data' => $data]);
            throw new \Exception("The booking dates are required.");
        }

        $hoardingIds = $data['hoarding_ids'] ?? [];

        $hoardingItemsMap = [];
        if (!empty($data['hoarding_items']) && is_array($data['hoarding_items'])) {
            foreach ($data['hoarding_items'] as $item) {
                if (isset($item['hoarding_id'])) {
                    $hoardingItemsMap[(int) $item['hoarding_id']] = $item;
                }
            }
        }
        
        // Lock and Fetch
        $hoardings = \App\Models\Hoarding::whereIn('id', $hoardingIds)
            ->lockForUpdate()
            ->get();

        Log::info('POSBookingService locked hoardings', ['vendor_id' => $effectiveVendorId, 'hoarding_ids' => $hoardings->pluck('id')->all(), 'count' => $hoardings->count()]);

        // Perform Pricing Calculation
        $pricing = $this->calculateMultiHoardingPricing(
            $hoardings,
            $start,
            $end,
            (float) ($data['discount_amount'] ?? 0),
            $hoardingItemsMap
        );

        Log::info('POSBookingService calculated pricing', ['vendor_id' => $effectiveVendorId, 'pricing' => $pricing]);

        // Create the record
        $bookingPayload = [
            'vendor_id'       => $effectiveVendorId,
            'customer_id'     => $data['customer_id'] ?? null,
            'customer_name'   => $data['customer_name'], // Required by DB
            'customer_phone'  => $data['customer_phone'], // Required by DB
            'customer_email'  => $data['customer_email'] ?? null,
            'customer_address'=> $data['customer_address'] ?? null,
            'customer_gstin'  => $data['customer_gstin'] ?? null,
            'booking_type'    => $data['booking_type'] ?? 'ooh',
            'start_date'      => $start,
            'end_date'        => $end,
            'duration_days'   => $this->calculateDurationDays($start, $end),
            'base_amount'     => $pricing['base_amount'],
            'discount_amount' => $pricing['discount_amount'],
            'tax_amount'      => $pricing['tax_amount'],
            'total_amount'    => $pricing['total_amount'],
            'payment_mode'    => $data['payment_mode'] ?? 'cash',
            'payment_reference' => $data['payment_reference'] ?? null,
            'payment_notes'   => $data['payment_notes'] ?? null,
            'notes'           => $data['notes'] ?? null,
            'payment_status'  => 'unpaid',
            'status'          => 'pending_payment',
            'hold_expiry_at'  => $data['hold_expiry_at'] ?? null,

               // Milestone flag (WHEN) — bool stored on booking
            'is_milestone'     => (bool) ($data['is_milestone'] ?? false),
 
            // Milestone counters — set after milestone creation below
            'milestone_total'            => 0,
            'milestone_paid'             => 0,
            'milestone_amount_paid'      => 0,
            'milestone_amount_remaining' => 0,
        ];

        Log::info('POSBookingService creating booking record', ['vendor_id' => $effectiveVendorId, 'booking_payload_preview' => array_intersect_key($bookingPayload, array_flip(['vendor_id','start_date','end_date','total_amount']))]);

        $booking = POSBooking::create($bookingPayload);

        // Inventory Linking
        foreach ($hoardings as $hoarding) {
            $item = $pricing['line_items'][$hoarding->id] ?? ($hoardingItemsMap[$hoarding->id] ?? null);
            $itemStart = $item['start_date'] ?? $start;
            $itemEnd = $item['end_date'] ?? $end;
            $itemPrice = $item['price_per_month'] ?? null;
            $itemDiscount = $item['discount_amount'] ?? 0;
            $this->attachHoardingToBooking($booking, $hoarding, $itemStart, $itemEnd, $itemPrice, $itemDiscount, $item ?? []);
        }

        Log::info('POSBookingService attached hoardings to booking', ['vendor_id' => $effectiveVendorId, 'pos_booking_id' => $booking->id, 'attached_count' => $booking->bookingHoardings->count()]);


         // ── Milestone creation (owned by service, not controller) ─────
        if ($booking->is_milestone && !empty($data['milestone_data'])) {
            $this->createMilestonesForPOSBooking($booking, $data['milestone_data']);
        }
        // Generate invoice after booking creation (auto-invoice)
        try {
            if ($this->isAutoInvoiceEnabled()) {
                $invoice = $this->invoiceService->generateInvoiceForPOSBooking(
                    $booking,
                    $effectiveVendorId
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
            } else {
                Log::info('POS auto-invoice disabled; skipping invoice generation', [
                    'pos_booking_id' => $booking->id,
                    'vendor_id' => $effectiveVendorId,
                    'setting_key' => 'pos_auto_invoice',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate POS invoice', [
                'pos_booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the booking if invoice fails
        }

        DB::afterCommit(function () use ($booking) {
            // 1. Domain event (existing)
            event(new \Modules\POS\Events\PosBookingCreated(
                $booking->load(['customer', 'vendor', 'bookingHoardings.hoarding'])
            ));
        
            // 2. Notifications — runs after commit so booking is fully persisted
            $this->dispatchBookingCreatedNotifications($booking);
        });
        return $booking->fresh(['bookingHoardings']);
    });

      
    }

/**
 * Calculates pricing for multiple hoardings
 */
protected function calculateMultiHoardingPricing($hoardings, $start, $end, $discount = 0, array $hoardingItemsMap = []): array
{
    $gstRate = $this->getGSTRate();
    $lineItems = [];
    $totalBase = 0.0;

    foreach ($hoardings as $hoarding) {
        $item = $hoardingItemsMap[$hoarding->id] ?? [];
        $itemStart = $item['start_date'] ?? $start;
        $itemEnd = $item['end_date'] ?? $end;
        $durationDays = $this->calculateDurationDays($itemStart, $itemEnd);
        $months = (int) ceil($durationDays / 30);
        $monthlyRate = isset($item['price_per_month'])
            ? (float) $item['price_per_month']
            : (float) ($hoarding->monthly_price ?? $hoarding->base_monthly_price ?? 0);
        $baseAmount = round($monthlyRate * $months, 2);

        $lineItems[(int) $hoarding->id] = [
            'hoarding_id' => (int) $hoarding->id,
            'start_date' => $itemStart,
            'end_date' => $itemEnd,
            'duration_days' => $durationDays,
            'price_per_month' => $monthlyRate,
            'base_amount' => $baseAmount,
        ];

        $totalBase += $baseAmount;
    }

    $totalBase = round($totalBase, 2);
    $normalizedDiscount = round(min(max(0, (float) $discount), $totalBase), 2);
    $remainingDiscount = $normalizedDiscount;
    $lineIds = array_keys($lineItems);
    $lineCount = count($lineIds);

    foreach ($lineIds as $index => $lineId) {
        $lineBase = (float) $lineItems[$lineId]['base_amount'];

        if ($lineCount === 0 || $normalizedDiscount <= 0) {
            $lineDiscount = 0.0;
        } elseif ($index === $lineCount - 1) {
            $lineDiscount = $remainingDiscount;
        } else {
            $lineDiscount = round(($lineBase / max($totalBase, 0.01)) * $normalizedDiscount, 2);
            $lineDiscount = min($lineBase, $lineDiscount, $remainingDiscount);
        }

        $remainingDiscount = round($remainingDiscount - $lineDiscount, 2);
        $taxableAmount = max(0, round($lineBase - $lineDiscount, 2));
        $taxAmount = round($taxableAmount * ($gstRate / 100), 2);
        $totalAmount = round($taxableAmount + $taxAmount, 2);

        $lineItems[$lineId]['discount_amount'] = round($lineDiscount, 2);
        $lineItems[$lineId]['tax_amount'] = $taxAmount;
        $lineItems[$lineId]['total_amount'] = $totalAmount;
    }

    return [
        'base_amount' => round(array_sum(array_column($lineItems, 'base_amount')), 2),
        'discount_amount' => round(array_sum(array_column($lineItems, 'discount_amount')), 2),
        'tax_amount' => round(array_sum(array_column($lineItems, 'tax_amount')), 2),
        'total_amount' => round(array_sum(array_column($lineItems, 'total_amount')), 2),
        'line_items' => $lineItems,
    ];
}

public function checkAvailability($hoardingId, $fromDate, $toDate): bool
{
    $hoarding = Hoarding::lockForUpdate()->find($hoardingId);
    if (!$hoarding) return false;

    // Check if on hold by another booking
    if ($hoarding->is_on_hold && $hoarding->hold_till && $hoarding->hold_till->isFuture()) {
        return false;
    }

    // Check for overlapping bookings
    $hasBooking = POSBookingHoarding::where('hoarding_id', $hoardingId)
        ->where(function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('start_date', [$fromDate, $toDate])
              ->orWhereBetween('end_date', [$fromDate, $toDate])
              ->orWhere(function ($q2) use ($fromDate, $toDate) {
                  $q2->where('start_date', '<=', $fromDate)
                     ->where('end_date', '>=', $toDate);
              });
        })
        ->whereHas('booking', function ($q) {
            $q->whereIn('status', ['pending_payment', 'confirmed', 'active']);
        })
        ->exists();

    return !$hasBooking;
}

public function holdHoardings(POSBooking $booking)
{
    foreach ($booking->bookingHoardings as $bh) {
        $hoarding = $bh->hoarding;
        $hoarding->is_on_hold = true;
        $hoarding->hold_till = now()->addMinutes(15);
        $hoarding->held_by_booking_id = $booking->id;
        $hoarding->save();
    }
}

public function releaseHoardings(POSBooking $booking)
{
    foreach ($booking->bookingHoardings as $bh) {
        $hoarding = $bh->hoarding;
        if ($hoarding->held_by_booking_id == $booking->id) {
            $hoarding->is_on_hold = false;
            $hoarding->hold_till = null;
            $hoarding->held_by_booking_id = null;
            $hoarding->save();
        }
    }
}


/**
 * Dispatch all post-booking notifications.
 *
 * Runs inside DB::afterCommit so the booking record is guaranteed to
 * exist before any notification reads it.
 *
 * Called automatically by createBooking() — no controller needs to
 * trigger this manually. Both web and API bookings benefit.
 */
private function dispatchBookingCreatedNotifications(POSBooking $booking): void
{
    // ── WhatsApp ──────────────────────────────────────────────────────
    try {
        $phone = $booking->customer_phone
            ?? ($booking->customer_id
                ? optional(\App\Models\User::find($booking->customer_id))->phone
                : null);
 
        if ($phone && $phone !== 'N/A') {
            $this->sendWhatsAppNotification($booking, $phone);
        }
    } catch (\Throwable $e) {
        Log::warning('POS WhatsApp notification failed', [
            'booking_id' => $booking->id,
            'error'      => $e->getMessage(),
        ]);
    }
 
    // ── Email + Push ──────────────────────────────────────────────────
    try {
        if (!$this->isEmailNotificationEnabled()) {
            return;
        }
 
        // Customer
        if (!empty($booking->customer_id)) {
            $customer = \App\Models\User::find($booking->customer_id);
 
            if ($customer?->notification_email
                && filter_var($customer->email ?? '', FILTER_VALIDATE_EMAIL)) {
                \Mail::to($customer->email)
                    ->queue(new \App\Mail\PosBookingCreatedMail($booking, $customer, 'customer'));
            }
 
            if ($customer?->notification_push) {
                send(
                    $customer,
                    'Booking Created Successfully',
                    "Your POS booking #{$booking->invoice_number} for ₹" . number_format($booking->total_amount, 2) . " has been created",
                    [
                        'type'           => 'pos_booking_created',
                        'booking_id'     => $booking->id,
                        'invoice_number' => $booking->invoice_number,
                        'total_amount'   => $booking->total_amount,
                        'source'         => 'pos_system',
                    ]
                );
            }
        }
 
        // Vendor
        $vendor = \App\Models\User::find($booking->vendor_id);
        if ($vendor?->notification_email) {
            foreach (array_unique(array_filter($vendor->notification_emails ?? [])) as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    \Mail::to($email)
                        ->queue(new \App\Mail\PosBookingCreatedMail($booking, $vendor, 'vendor'));
                }
            }
        }
 
        // Admins
        \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'super_admin']);
        })->get()->each(function ($admin) use ($booking) {
            if ($admin->notification_email
                && filter_var($admin->email ?? '', FILTER_VALIDATE_EMAIL)) {
                \Mail::to($admin->email)
                    ->queue(new \App\Mail\PosBookingCreatedMail($booking, $admin, 'admin'));
            }
        });
 
    } catch (\Throwable $e) {
        Log::warning('POS email/push notification failed', [
            'booking_id' => $booking->id,
            'error'      => $e->getMessage(),
        ]);
    }
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
     * Create milestones for a POS booking.
     *
     * Owned by the service (Single Responsibility).
     * Called only from createBooking() — never from a controller.
     *
     * @param  POSBooking  $booking
     * @param  array       $milestonesData  Validated milestone rows from request
     * @return void
     */
    private function createMilestonesForPOSBooking(POSBooking $booking, array $milestonesData): void
    {
        $totalAmount  = (float) $booking->total_amount;
        $amountType   = $milestonesData[0]['amount_type'] ?? 'percentage';
        $created      = [];
        $sequenceNo   = 1;
    
        // ── Server-side total validation ──────────────────────────────────
        $sumPct   = 0.0;
        $sumFixed = 0.0;
        foreach ($milestonesData as $m) {
            if (($m['amount_type'] ?? '') === 'percentage') {
                $sumPct += (float) ($m['amount'] ?? 0);
            } else {
                $sumFixed += (float) ($m['amount'] ?? 0);
            }
        }
    
        if ($amountType === 'percentage' && abs($sumPct - 100) > 0.01) {
            Log::warning('Milestone percentages do not total 100 — skipping milestone creation', [
                'pos_booking_id' => $booking->id,
                'sum'            => $sumPct,
            ]);
            $booking->update(['is_milestone' => false]);
            return;
        }
    
        if ($amountType === 'fixed' && abs($sumFixed - $totalAmount) > 0.01) {
            Log::warning('Milestone fixed amounts do not match booking total — skipping milestone creation', [
                'pos_booking_id' => $booking->id,
                'sum'            => $sumFixed,
                'total'          => $totalAmount,
            ]);
            $booking->update(['is_milestone' => false]);
            return;
        }
    
        // ── Create milestone records ──────────────────────────────────────
        foreach ($milestonesData as $idx => $milestoneData) {
            $isFirst    = ($idx === 0);
            $calculated = $milestoneData['amount_type'] === 'percentage'
                ? round(($totalAmount * (float) $milestoneData['amount']) / 100, 2)
                : (float) $milestoneData['amount'];
    
            $milestone = \App\Models\QuotationMilestone::create([
                'pos_booking_id'   => $booking->id,
                'quotation_id'     => null,
                'title'            => $milestoneData['title'],
                'description'      => $milestoneData['description'] ?? null,
                'sequence_no'      => $sequenceNo++,
                'amount_type'      => $milestoneData['amount_type'],
                'amount'           => $milestoneData['amount'],
                'calculated_amount'=> $calculated,
                'status'           => $isFirst ? 'due' : 'pending',
                'due_date'         => $milestoneData['due_date'] ?? null,
                'vendor_notes'     => $milestoneData['vendor_notes'] ?? null,
            ]);
    
            $created[] = $milestone;
        }
    
        // ── Update booking milestone counters ─────────────────────────────
        $booking->update([
            'milestone_total'            => count($created),
            'milestone_paid'             => 0,
            'milestone_amount_paid'      => 0,
            'milestone_amount_remaining' => $totalAmount,
            'current_milestone_id'       => $created[0]->id ?? null,
        ]);
    
        Log::info('POSBookingService: milestones created', [
            'pos_booking_id' => $booking->id,
            'count'          => count($created),
            'amount_type'    => $amountType,
        ]);
    }
    /**
     * Cancel booking
     */
    // public function cancelBooking(POSBooking $booking, string $reason): POSBooking
    // {
    //     return DB::transaction(function () use ($booking, $reason) {
    //         $booking->update([
    //             'status' => POSBooking::STATUS_CANCELLED,
    //             'cancellation_reason' => $reason,
    //             'cancelled_at' => now(),
    //         ]);

    //         return $booking->fresh();
    //     });
    // }

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
        $baseAmount = round((float) ($data['base_amount'] ?? 0), 2);
        $discountAmount = round(min(max(0, (float) ($data['discount_amount'] ?? 0)), $baseAmount), 2);
        $gstRate = $this->getGSTRate();

        $amountAfterDiscount = max(0, round($baseAmount - $discountAmount, 2));
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
        $query = POSBooking::with(['hoardings', 'customer', 'approver',''])
            ->forVendor($vendorId);
        \Log::info('POSBookingService.getVendorBookings query', ['vendor_id' => $vendorId, 'filters' => $filters]);

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
            'total_customers'  => POSBooking::where('vendor_id', $vendorId)
                                ->distinct('customer_phone') // or customer_email
                                ->count('customer_phone'),
        ];
    }

    /**
     * CRITICAL: Mark booking payment as received
     * Transitions unpaid → paid
     * Clears hold_expiry_at to allow campaign to start
     */
    // public function markPaymentReceived(POSBooking $booking, float $amount, \Carbon\Carbon $paymentDate, ?string $notes = null): POSBooking
    // {
    //     return DB::transaction(function () use ($booking, $amount, $paymentDate, $notes) {
    //         // Validate state
    //         if (!in_array($booking->payment_status, ['unpaid', 'partial'])) {
    //             throw new \Exception('Booking is not in payable state');
    //         }

    //         if ($amount <= 0) {
    //             throw new \Exception('Payment amount must be greater than zero');
    //         }

    //         $existingPaid = (float) ($booking->paid_amount ?? 0);
    //         $totalAmount = (float) $booking->total_amount;
    //         $remainingAmount = max(0, $totalAmount - $existingPaid);

    //         if ($amount > ($remainingAmount + 0.01)) {
    //             throw new \Exception('Payment amount cannot be greater than remaining payable amount');
    //         }

    //         $newPaidAmount = min($totalAmount, $existingPaid + $amount);

    //         // Determine if full or partial payment
    //         $newStatus = abs($newPaidAmount - $totalAmount) < 0.01
    //             ? POSBooking::PAYMENT_STATUS_PAID 
    //             : POSBooking::PAYMENT_STATUS_PARTIAL;

    //         // Update booking
    //         $booking->update([
    //             'paid_amount' => $newPaidAmount,
    //             'payment_status' => $newStatus,
    //             'payment_received_at' => $paymentDate,
    //             'hold_expiry_at' => null, // Clear hold when payment received
    //             'reminder_count' => 0,    // Reset reminders
    //             'status' => POSBooking::STATUS_CONFIRMED,
    //             'last_reminder_at' => null,
    //         ]);

    //         // Lock inventory as booked/confirmed for this POS booking
    //         foreach ($booking->bookingHoardings as $bookingHoarding) {
    //             $this->hoardingBookingService->confirmInventoryForPosBookingHoarding($bookingHoarding);
    //         }

    //         // Log payment transaction
    //         Log::info('Payment marked as received', [
    //             'booking_id' => $booking->id,
    //             'vendor_id' => $booking->vendor_id,
    //             'amount' => $amount,
    //             'paid_amount_before' => $existingPaid,
    //             'paid_amount_after' => $newPaidAmount,
    //             'total' => $totalAmount,
    //             'status' => $newStatus,
    //             'notes' => $notes,
    //         ]);

    //         // TODO: Unblock hoarding inventory (when release logic is added)
    //         // $this->releaseHoardingInventory($booking);

    //         return $booking->fresh();
    //     });
    // }
    /**
     * CRITICAL: Mark booking payment as received
     * Transitions: unpaid / partial → paid / partially_paid
     * Also syncs the linked Invoice status + regenerates PDF
     */
    public function markPaymentReceived(
        \Modules\POS\Models\POSBooking $booking,
        float $amount,
        \Carbon\Carbon $paymentDate,
        ?string $notes = null
    ): \Modules\POS\Models\POSBooking {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($booking, $amount, $paymentDate, $notes) {

            // ── Validate state ──────────────────────────────────────────────
            if (!in_array($booking->payment_status, ['unpaid', 'partial'])) {
                throw new \Exception('Booking is not in payable state');
            }
            if ($amount <= 0) {
                throw new \Exception('Payment amount must be greater than zero');
            }

            $existingPaid  = (float) ($booking->paid_amount ?? 0);
            $totalAmount   = (float) $booking->total_amount;
            $remaining     = max(0, $totalAmount - $existingPaid);

            if ($amount > ($remaining + 0.01)) {
                throw new \Exception('Payment amount cannot exceed the remaining payable amount');
            }

            $newPaidAmount = min($totalAmount, $existingPaid + $amount);
            $newStatus     = abs($newPaidAmount - $totalAmount) < 0.01
                ? \Modules\POS\Models\POSBooking::PAYMENT_STATUS_PAID
                : \Modules\POS\Models\POSBooking::PAYMENT_STATUS_PARTIAL;

            // ── Update POS booking ──────────────────────────────────────────
            $booking->update([
                'paid_amount'          => $newPaidAmount,
                'payment_status'       => $newStatus,
                'payment_received_at'  => $paymentDate,
                'hold_expiry_at'       => null,
                'reminder_count'       => 0,
                'status'               => \Modules\POS\Models\POSBooking::STATUS_CONFIRMED,
                'last_reminder_at'     => null,
            ]);

            // ── Lock inventory as confirmed ─────────────────────────────────
            foreach ($booking->bookingHoardings as $bookingHoarding) {
                $this->hoardingBookingService->confirmInventoryForPosBookingHoarding($bookingHoarding);
            }

            // ── Sync Invoice payment status ─────────────────────────────────
            // This updates invoice.status, invoice.paid_amount, and regenerates the PDF
            try {
                $this->invoiceService->syncPaymentStatusFromPOSBooking(
                    $booking,
                    $newPaidAmount,
                    $paymentDate
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to sync invoice payment status', [
                    'pos_booking_id' => $booking->id,
                    'error'          => $e->getMessage(),
                ]);
                // Non-fatal — booking payment is still recorded
            }

            \Illuminate\Support\Facades\Log::info('Payment marked as received', [
                'booking_id'          => $booking->id,
                'vendor_id'           => $booking->vendor_id,
                'amount'              => $amount,
                'paid_amount_before'  => $existingPaid,
                'paid_amount_after'   => $newPaidAmount,
                'total'               => $totalAmount,
                'status'              => $newStatus,
                'notes'               => $notes,
            ]);

            \Illuminate\Support\Facades\DB::afterCommit(function () use ($booking) {
                $this->sendPaymentReceivedNotifications((int) $booking->id);
            });

            return $booking->fresh();
        });
    }

    protected function sendPaymentReceivedNotifications(int $bookingId): void
    {
        try {
            $booking = \Modules\POS\Models\POSBooking::query()->find($bookingId);

            if (!$booking) {
                return;
            }

            $notification = new \App\Notifications\PosBookingConfirmedNotification($booking);

            $recipientIds = collect();

            if (!empty($booking->customer_id)) {
                $recipientIds->push((int) $booking->customer_id);
            }

            if (!empty($booking->vendor_id)) {
                $recipientIds->push((int) $booking->vendor_id);
            }

            $adminIds = \App\Models\User::query()
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['admin', 'super_admin']);
                })
                ->pluck('id');

            $recipientIds = $recipientIds
                ->merge($adminIds)
                ->filter(fn ($id) => (int) $id > 0)
                ->unique()
                ->values();

            if ($recipientIds->isEmpty()) {
                return;
            }

            \App\Models\User::query()
                ->whereIn('id', $recipientIds->all())
                ->get()
                ->each(function ($user) use ($notification) {
                    if (method_exists($user, 'notify')) {
                        $user->notify($notification);
                    }
                });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('POS payment received notification dispatch failed', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage(),
            ]);
        }
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
        return (float) ($this->settingsService->get('gst_rate') ?? 18);
    }


      /**
     * Attach a hoarding to a booking and save to pos_booking_hoardings table
     */
    protected function attachHoardingToBooking(POSBooking $booking, Hoarding $hoarding, $start, $end, $pricePerMonth = null, $discount = 0, array $pricingLine = [])
    {
        \Log::info('Attaching hoarding to booking', [
            'pos_booking_id' => $booking->id,
            'hoarding_id' => $hoarding->id,
            'start_date' => $start,
            'end_date' => $end,
            'price_per_month' => $pricePerMonth,
            'discount' => $discount,
        ]);
        $durationDays = $pricingLine['duration_days'] ?? $this->calculateDurationDays($start, $end);
        $monthlyRate = $pricePerMonth !== null ? (float) $pricePerMonth : ($hoarding->monthly_price ?? $hoarding->base_monthly_price ?? 0);
        $months = (int) ceil($durationDays / 30);
        $base = array_key_exists('base_amount', $pricingLine)
            ? (float) $pricingLine['base_amount']
            : round($monthlyRate * $months, 2);
        $gstRate = $this->getGSTRate();
        $discount = array_key_exists('discount_amount', $pricingLine)
            ? (float) $pricingLine['discount_amount']
            : (float) $discount;
        $taxableAmount = max(0, round($base - $discount, 2));
        $tax = array_key_exists('tax_amount', $pricingLine)
            ? (float) $pricingLine['tax_amount']
            : round($taxableAmount * ($gstRate / 100), 2);
        $total = array_key_exists('total_amount', $pricingLine)
            ? (float) $pricingLine['total_amount']
            : round($taxableAmount + $tax, 2);

        \Modules\POS\Models\POSBookingHoarding::create([
            'pos_booking_id'   => $booking->id,
            'hoarding_id'      => $hoarding->id,
            'hoarding_price'   => round($base, 2),
            'hoarding_discount'=> round($discount, 2),
            'hoarding_tax'     => round($tax, 2),
            'hoarding_total'   => round($total, 2),
            // 'url'              => $hoarding->getUrlAttribute(),
            'start_date'       => $start,
            'end_date'         => $end,
            'duration_days'    => $durationDays,
            'duration_type'    => 'days',
            'status'           => 'pending',
        ]);
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


    public function transitionStatus(POSBooking $booking, string $newStatus): POSBooking
{
    $validTransitions = [
        'draft' => ['confirmed'],
        'confirmed' => ['partial_paid', 'cancelled'],
        'partial_paid' => ['paid', 'cancelled'],
        'paid' => ['completed'],
    ];

    $current = $booking->status;
    if (!isset($validTransitions[$current]) || !in_array($newStatus, $validTransitions[$current])) {
        throw new \Exception("Invalid status transition from {$current} to {$newStatus}");
    }

    return DB::transaction(function () use ($booking, $newStatus) {
        $booking->status = $newStatus;
        $booking->save();

        // Inventory hold logic
        if (in_array($newStatus, ['confirmed', 'partial_paid', 'paid'])) {
            foreach ($booking->bookingHoardings as $bh) {
                $hoarding = $bh->hoarding;
                $hoarding->is_on_hold = true;
                $hoarding->hold_till = $booking->end_date;
                $hoarding->held_by_booking_id = $booking->id;
                $hoarding->save();
            }
        }

        // Release inventory on cancel
        if ($newStatus === 'cancelled') {
            foreach ($booking->bookingHoardings as $bh) {
                $hoarding = $bh->hoarding;
                if ($hoarding->held_by_booking_id == $booking->id) {
                    $hoarding->is_on_hold = false;
                    $hoarding->hold_till = null;
                    $hoarding->held_by_booking_id = null;
                    $hoarding->save();
                }
            }
        }

        // Mark as completed after end_date
        if ($newStatus === 'completed') {
            foreach ($booking->bookingHoardings as $bh) {
                $hoarding = $bh->hoarding;
                if ($hoarding->held_by_booking_id == $booking->id) {
                    $hoarding->is_on_hold = false;
                    $hoarding->hold_till = null;
                    $hoarding->held_by_booking_id = null;
                    $hoarding->save();
                }
            }
        }

        return $booking->fresh();
    });
}

public function cancelBooking(POSBooking $booking, string $reason): POSBooking
{
    return $this->transitionStatus($booking, 'cancelled');
}
// ADDITION (STEP 4)


// ADDITION (STEP 4)
public function isHoardingAvailable($hoardingId, $fromDate, $toDate, $excludeBookingId = null): bool
{
    // Check for overlapping bookings or holds
    $query = \Modules\POS\Models\POSBookingHoarding::where('hoarding_id', $hoardingId)
        ->where(function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('start_date', [$fromDate, $toDate])
              ->orWhereBetween('end_date', [$fromDate, $toDate])
              ->orWhere(function ($q2) use ($fromDate, $toDate) {
                  $q2->where('start_date', '<=', $fromDate)
                     ->where('end_date', '>=', $toDate);
              });
        })
        ->whereHas('booking', function ($q) use ($excludeBookingId) {
            $q->whereIn('status', ['confirmed', 'partial_paid', 'paid'])
              ->when($excludeBookingId, function ($q2) use ($excludeBookingId) {
                  $q2->where('id', '!=', $excludeBookingId);
              });
        });

    $hoarding = \App\Models\Hoarding::find($hoardingId);
    if ($hoarding && $hoarding->is_on_hold && $hoarding->hold_till && $hoarding->hold_till >= now() && $hoarding->held_by_booking_id !== $excludeBookingId) {
        return false;
    }

    return !$query->exists();
}
// ADDITION (STEP 4)


// File: Modules/POS/Models/POSBooking.php

// ADDITION (STEP 4)

}
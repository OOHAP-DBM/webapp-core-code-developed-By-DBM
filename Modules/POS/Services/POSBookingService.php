<?php

namespace Modules\POS\Services;

use Modules\POS\Models\POSBooking;
use App\Models\Hoarding;
use Modules\Hoardings\Services\HoardingBookingService;
use Modules\Settings\Services\SettingsService;
use App\Services\TaxService;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Mail\PosBookingCancelledMail;
use App\Notifications\PosBookingCancelledNotification;
use App\Notifications\PosBookingCreatedNotification;
use App\Notifications\PosBookingConfirmedNotification;
use mail;
use App\Services\Whatsapp\TwilioWhatsappService;

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
        $this->settingsService        = $settingsService;
        $this->taxService             = $taxService;
        $this->invoiceService         = $invoiceService;
        $this->hoardingBookingService = $hoardingBookingService;
    }
    // charge dispute form
    /* =========================================================
     *  CREATE BOOKING
     *  Handles: cash | bank_transfer | online | credit_note
     * ========================================================= */
    public function createBooking(array $data): POSBooking
    {
        Log::info('POSBookingService.createBooking start', [
            'vendor_id'    => ($data['vendor_id'] ?? Auth::id()),
            'data_preview' => array_intersect_key($data, array_flip([
                'hoarding_ids',
                'start_date',
                'end_date',
                'payment_mode',
                'customer_id',
            ])),
        ]);

        return DB::transaction(function () use ($data) {
            $effectiveVendorId = (int) ($data['vendor_id'] ?? Auth::id());

            // ── Date normalisation ───────────────────────────────────────
            $start = $data['start_date'] ?? $data['booking_date'] ?? null;
            $end   = $data['end_date']   ?? $data['booking_date'] ?? null;

            if (!$start || !$end) {
                throw new \Exception('The booking dates are required.');
            }

            $hoardingIds    = $data['hoarding_ids'] ?? [];
            $hoardingItemsMap = [];
            if (!empty($data['hoarding_items']) && is_array($data['hoarding_items'])) {
                foreach ($data['hoarding_items'] as $item) {
                    if (isset($item['hoarding_id'])) {
                        $hoardingItemsMap[(int) $item['hoarding_id']] = $item;
                    }
                }
            }

            // ── Lock & fetch hoardings ───────────────────────────────────
            $hoardings = \App\Models\Hoarding::whereIn('id', $hoardingIds)
                ->lockForUpdate()
                ->get();

            // ── Pricing ─────────────────────────────────────────────────
            $pricing = $this->calculateMultiHoardingPricing(
                $hoardings,
                $start,
                $end,
                (float) ($data['discount_amount'] ?? 0),
                $hoardingItemsMap
            );

            // ── Payment mode logic ───────────────────────────────────────
            $paymentMode = $data['payment_mode'] ?? POSBooking::PAYMENT_MODE_CASH;
            $isCreditNote = ($paymentMode === POSBooking::PAYMENT_MODE_CREDIT_NOTE);

            // Credit note: auto-confirm, no hold expiry
            // All other modes: set hold_expiry_at from hold_minutes
            $holdExpiryAt  = null;
            $bookingStatus = 'pending_payment';
            $paymentStatus = POSBooking::PAYMENT_STATUS_UNPAID;
            $creditNoteData = [];

            if ($isCreditNote) {
                $creditNoteDays   = (int) ($data['credit_note_days'] ?? $this->getCreditNoteDays());
                $bookingStatus    = POSBooking::STATUS_CONFIRMED;   // auto-confirmed
                $paymentStatus    = POSBooking::PAYMENT_STATUS_CREDIT;
                $holdExpiryAt     = null;                           // no timer

                $creditNoteData = [
                    'credit_note_number'   => $this->generateCreditNoteNumber(),
                    'credit_note_date'     => now(),
                    'credit_note_due_date' => now()->addDays($creditNoteDays),
                    'credit_note_status'   => POSBooking::CREDIT_NOTE_STATUS_ACTIVE,
                ];

                Log::info('POSBookingService: credit note booking', [
                    'vendor_id'        => $effectiveVendorId,
                    'credit_note_days' => $creditNoteDays,
                    'due_date'         => $creditNoteData['credit_note_due_date'],
                ]);
            } else {
                // Compute hold expiry from hold_minutes sent by front-end
                $holdMinutes = (int) ($data['hold_minutes'] ?? 30);
                if ($holdMinutes > 0) {
                    $holdExpiryAt = now()->addMinutes($holdMinutes);
                }
            }

            // ── Build booking payload ────────────────────────────────────
            $bookingPayload = array_merge([
                'vendor_id'        => $effectiveVendorId,
                'customer_id'      => $data['customer_id'] ?? null,
                'customer_name'    => $data['customer_name'],
                'customer_phone'   => $data['customer_phone'],
                'customer_email'   => $data['customer_email'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'customer_gstin'   => $data['customer_gstin'] ?? null,
                'booking_type'     => $data['booking_type'] ?? 'ooh',
                'start_date'       => $start,
                'end_date'         => $end,
                'duration_days'    => $this->calculateDurationDays($start, $end),
                'base_amount'      => $pricing['base_amount'],
                'discount_amount'  => $pricing['discount_amount'],
                'tax_amount'       => $pricing['tax_amount'],
                'total_amount'     => $pricing['total_amount'],
                'payment_mode'     => $paymentMode,
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_notes'    => $data['payment_notes'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'payment_status'   => $paymentStatus,
                'status'           => $bookingStatus,
                'hold_expiry_at'   => $holdExpiryAt,

                // Milestone fields
                'is_milestone'               => (bool) ($data['is_milestone'] ?? false),
                'milestone_total'            => 0,
                'milestone_paid'             => 0,
                'milestone_amount_paid'      => 0,
                'milestone_amount_remaining' => 0,
            ], $creditNoteData);

            $booking = POSBooking::create($bookingPayload);

            // ── Inventory linking ────────────────────────────────────────
            foreach ($hoardings as $hoarding) {
                $item        = $pricing['line_items'][$hoarding->id] ?? ($hoardingItemsMap[$hoarding->id] ?? null);
                $itemStart   = $item['start_date']      ?? $start;
                $itemEnd     = $item['end_date']        ?? $end;
                $itemPrice   = $item['price_per_month'] ?? null;
                $itemDiscount = $item['discount_amount'] ?? 0;
                $this->attachHoardingToBooking($booking, $hoarding, $itemStart, $itemEnd, $itemPrice, $itemDiscount, $item ?? []);
            }

            // ── Milestones ───────────────────────────────────────────────
            if ($booking->is_milestone && !empty($data['milestone_data'])) {
                $this->createMilestonesForPOSBooking($booking, $data['milestone_data']);
            }

            // ── Auto-invoice ─────────────────────────────────────────────
            try {
                if ($this->isAutoInvoiceEnabled()) {
                    $invoice = $this->invoiceService->generateInvoiceForPOSBooking(
                        $booking,
                        $effectiveVendorId
                    );
                    $booking->update([
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_date'   => $invoice->invoice_date,
                        'invoice_path'   => $invoice->pdf_path,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to generate POS invoice', [
                    'pos_booking_id' => $booking->id,
                    'error'          => $e->getMessage(),
                ]);
            }

            DB::afterCommit(function () use ($booking) {
                event(new \Modules\POS\Events\PosBookingCreated(
                    $booking->load(['customer', 'vendor', 'bookingHoardings.hoarding'])
                ));
                $this->dispatchBookingCreatedNotifications($booking);
            });

            return $booking->fresh(['bookingHoardings']);
        });
    }

    /* =========================================================
     *  PRICING
     * ========================================================= */
    protected function calculateMultiHoardingPricing($hoardings, $start, $end, $discount = 0, array $hoardingItemsMap = []): array
    {
        $gstRate    = $this->getGSTRate();
        $lineItems  = [];
        $totalBase  = 0.0;

        foreach ($hoardings as $hoarding) {
            $item         = $hoardingItemsMap[$hoarding->id] ?? [];
            $itemStart    = $item['start_date'] ?? $start;
            $itemEnd      = $item['end_date']   ?? $end;
            $durationDays = $this->calculateDurationDays($itemStart, $itemEnd);
            $months       = (int) ceil($durationDays / 30);
            $monthlyRate  = isset($item['price_per_month'])
                ? (float) $item['price_per_month']
                : (float) ($hoarding->monthly_price ?? $hoarding->base_monthly_price ?? 0);
            $baseAmount   = round($monthlyRate * $months, 2);

            $lineItems[(int) $hoarding->id] = [
                'hoarding_id'   => (int) $hoarding->id,
                'start_date'    => $itemStart,
                'end_date'      => $itemEnd,
                'duration_days' => $durationDays,
                'price_per_month' => $monthlyRate,
                'base_amount'   => $baseAmount,
            ];

            $totalBase += $baseAmount;
        }

        $totalBase          = round($totalBase, 2);
        $normalizedDiscount = round(min(max(0, (float) $discount), $totalBase), 2);
        $remainingDiscount  = $normalizedDiscount;
        $lineIds            = array_keys($lineItems);
        $lineCount          = count($lineIds);

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

            $remainingDiscount  = round($remainingDiscount - $lineDiscount, 2);
            $taxableAmount      = max(0, round($lineBase - $lineDiscount, 2));
            $taxAmount          = round($taxableAmount * ($gstRate / 100), 2);
            $totalAmount        = round($taxableAmount + $taxAmount, 2);

            $lineItems[$lineId]['discount_amount'] = round($lineDiscount, 2);
            $lineItems[$lineId]['tax_amount']      = $taxAmount;
            $lineItems[$lineId]['total_amount']    = $totalAmount;
        }

        return [
            'base_amount'     => round(array_sum(array_column($lineItems, 'base_amount')), 2),
            'discount_amount' => round(array_sum(array_column($lineItems, 'discount_amount')), 2),
            'tax_amount'      => round(array_sum(array_column($lineItems, 'tax_amount')), 2),
            'total_amount'    => round(array_sum(array_column($lineItems, 'total_amount')), 2),
            'line_items'      => $lineItems,
        ];
    }

    /* =========================================================
     *  NOTIFICATIONS
     * ========================================================= */

    /**
     * Dispatches all post-booking notifications.
     * Runs inside DB::afterCommit so the record is guaranteed to exist.
     * The notification message automatically adapts for credit note bookings.
     */
    private function dispatchBookingCreatedNotifications(POSBooking $booking): void
    {
        $isCreditNote = ($booking->payment_mode === POSBooking::PAYMENT_MODE_CREDIT_NOTE);

        // ── WhatsApp ──────────────────────────────────────────────────
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

        // ── Email + Push ──────────────────────────────────────────────
        try {
            if (!$this->isEmailNotificationEnabled()) {
                return;
            }

            // Customer
            if (!empty($booking->customer_id)) {
                $customer = \App\Models\User::find($booking->customer_id);

                if ($customer?->notification_email && filter_var($customer->email ?? '', FILTER_VALIDATE_EMAIL)) {
                    \Mail::to($customer->email)->queue(
                        new \App\Mail\PosBookingCreatedMail($booking, $customer, 'customer')
                    );
                }

                if ($customer?->notification_push) {
                    $pushTitle   = $isCreditNote ? 'Credit Note Booking Confirmed' : 'Booking Created Successfully';
                    $pushMessage = $isCreditNote
                        ? "Your credit note booking #{$booking->invoice_number} for ₹" . number_format($booking->total_amount, 2) . " has been confirmed. Credit note: {$booking->credit_note_number}"
                        : "Your POS booking #{$booking->invoice_number} for ₹" . number_format($booking->total_amount, 2) . " has been created";

                    send($customer, $pushTitle, $pushMessage, [
                        'type'              => $isCreditNote ? 'pos_credit_note_booking_created' : 'pos_booking_created',
                        'booking_id'        => $booking->id,
                        'invoice_number'    => $booking->invoice_number,
                        'total_amount'      => $booking->total_amount,
                        'source'            => 'pos_system',
                        'credit_note_number' => $booking->credit_note_number ?? null,
                    ]);
                }
            }

            // Vendor
            $vendor = \App\Models\User::find($booking->vendor_id);
            if ($vendor?->notification_email) {
                foreach (array_unique(array_filter($vendor->notification_emails ?? [])) as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        \Mail::to($email)->queue(
                            new \App\Mail\PosBookingCreatedMail($booking, $vendor, 'vendor')
                        );
                    }
                }
            }

            // Admins
            \App\Models\User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'super_admin']);
            })->get()->each(function ($admin) use ($booking) {
                if ($admin->notification_email && filter_var($admin->email ?? '', FILTER_VALIDATE_EMAIL)) {
                    \Mail::to($admin->email)->queue(
                        new \App\Mail\PosBookingCreatedMail($booking, $admin, 'admin')
                    );
                }
            });
        } catch (\Throwable $e) {
            Log::warning('POS email/push notification failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function dispatchBookingCancelledNotifications(int $bookingId, ?string $reason = null): void
    {
        try {
            $booking = POSBooking::query()
                ->with(['customer', 'vendor', 'bookingHoardings.hoarding'])
                ->find($bookingId);

            if (!$booking) {
                return;
            }

            $normalizedReason = trim((string) ($reason ?? $booking->cancellation_reason ?? ''));
            $hoardingTitles = $booking->bookingHoardings
                ->map(function ($bookingHoarding) {
                    return trim((string) ($bookingHoarding->hoarding->title ?? ''));
                })
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($hoardingTitles) && !empty($booking->hoarding?->title)) {
                $hoardingTitles[] = trim((string) $booking->hoarding->title);
            }

            $hoardingPreview = 'N/A';
            if (!empty($hoardingTitles)) {
                $preview = array_slice($hoardingTitles, 0, 2);
                $remaining = count($hoardingTitles) - count($preview);
                $hoardingPreview = implode(', ', $preview);
                if ($remaining > 0) {
                    $hoardingPreview .= ' +' . $remaining . ' more';
                }
            }

            $customer = $booking->customer;
            $vendor = $booking->vendor;

            $inAppNotification = new PosBookingCancelledNotification(
                $booking,
                $hoardingTitles,
                $normalizedReason !== '' ? $normalizedReason : null
            );

            if ($customer && method_exists($customer, 'notify')) {
                try {
                    $customer->notify($inAppNotification);
                } catch (\Throwable $e) {
                    Log::warning('POS customer cancellation in-app notification failed', [
                        'booking_id' => $booking->id,
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($vendor && method_exists($vendor, 'notify')) {
                try {
                    $vendor->notify($inAppNotification);
                } catch (\Throwable $e) {
                    Log::warning('POS vendor cancellation in-app notification failed', [
                        'booking_id' => $booking->id,
                        'vendor_id' => $vendor->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            try {
                if ($customer) {
                    $customerPushMessage = 'Your POS booking #' . ($booking->invoice_number ?? $booking->id)
                        . ' has been cancelled for hoarding: ' . $hoardingPreview;
                    if ($normalizedReason !== '') {
                        $customerPushMessage .= '. Reason: ' . Str::limit($normalizedReason, 140);
                    }

                    send($customer, 'POS Booking Cancelled', $customerPushMessage, [
                        'type' => 'pos_booking_cancelled',
                        'booking_id' => $booking->id,
                        'invoice_number' => $booking->invoice_number,
                        'status' => $booking->status,
                        'hoarding_title' => $hoardingPreview,
                        'cancellation_reason' => $normalizedReason !== '' ? $normalizedReason : null,
                        'recipient' => 'customer',
                        'source' => 'pos_system',
                    ]);
                }

                if ($vendor) {
                    $vendorPushMessage = 'POS booking #' . ($booking->invoice_number ?? $booking->id)
                        . ' has been cancelled. Hoarding: ' . $hoardingPreview;
                    if ($normalizedReason !== '') {
                        $vendorPushMessage .= '. Reason: ' . Str::limit($normalizedReason, 140);
                    }

                    send($vendor, 'POS Booking Cancelled', $vendorPushMessage, [
                        'type' => 'pos_booking_cancelled',
                        'booking_id' => $booking->id,
                        'invoice_number' => $booking->invoice_number,
                        'status' => $booking->status,
                        'hoarding_title' => $hoardingPreview,
                        'cancellation_reason' => $normalizedReason !== '' ? $normalizedReason : null,
                        'recipient' => 'vendor',
                        'source' => 'pos_system',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('POS cancellation push notification failed', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $bookingCustomerEmail = trim((string) ($booking->customer_email ?? ''));
            $customerProfileEmail = trim((string) ($customer->email ?? ''));

            $emailRecipient = null;
            if (!empty($bookingCustomerEmail) && filter_var($bookingCustomerEmail, FILTER_VALIDATE_EMAIL)) {
                $emailRecipient = $bookingCustomerEmail;
            } elseif (!empty($customerProfileEmail) && filter_var($customerProfileEmail, FILTER_VALIDATE_EMAIL)) {
                $emailRecipient = $customerProfileEmail;
            }

            if (!empty($emailRecipient)) {
                try {
                    \Mail::to($emailRecipient)->queue(
                        new PosBookingCancelledMail(
                            $booking,
                            $customer,
                            $hoardingTitles,
                            $normalizedReason !== '' ? $normalizedReason : null
                        )
                    );
                } catch (\Throwable $e) {
                    Log::warning('POS cancellation email failed', [
                        'booking_id' => $booking->id,
                        'email' => $emailRecipient,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                Log::warning('POS cancellation email skipped - no valid customer email', [
                    'booking_id' => $booking->id,
                    'booking_customer_email' => $booking->customer_email,
                    'customer_id' => $booking->customer_id,
                    'customer_profile_email' => $customer?->email,
                ]);
            }

            // ── WhatsApp to customer ──────────────────────────────────
            try {
                $phone = $booking->customer_phone
                    ?? ($booking->customer_id
                        ? optional(\App\Models\User::find($booking->customer_id))->phone
                        : null);

                if ($phone && $phone !== 'N/A') {
                    $normalizedPhone = preg_replace('/\D+/', '', $phone);

                    if (!empty($normalizedPhone) && strlen($normalizedPhone) >= 10) {
                        if (!str_starts_with($normalizedPhone, '91')) {
                            $normalizedPhone = '91' . ltrim($normalizedPhone, '0');
                        }
                        $normalizedPhone = '+' . $normalizedPhone;

                        $vendorName = $booking->vendor?->name ?? 'Vendor';
                        $message = "❌ *POS Booking Cancelled*\n\n"
                            . "Hello *{$booking->customer_name}*,\n\n"
                            . "Your POS booking has been cancelled by *{$vendorName}*.\n\n"
                            . "📋 *Booking Details:*\n"
                            . "Invoice: #{$booking->invoice_number}\n"
                            . "Status: ❌ *CANCELLED*\n\n"
                            . "🏛️ *Hoardings:*\n" . implode("\n", array_map(fn($t) => "• {$t}", $hoardingTitles ?: ['N/A'])) . "\n\n"
                            . ($normalizedReason !== '' ? "📝 *Reason:* {$normalizedReason}\n\n" : '')
                            . "If you have any questions, please contact us.";

                        $whatsapp = app(TwilioWhatsappService::class);
                        $sent = $whatsapp->send($normalizedPhone, $message);

                        Log::info('POS cancellation WhatsApp dispatched', [
                            'booking_id' => $booking->id,
                            'phone'      => $normalizedPhone,
                            'sent'       => $sent,
                        ]);
                    } else {
                        Log::warning('POS cancellation WhatsApp skipped - invalid phone', [
                            'booking_id' => $booking->id,
                            'phone'      => $phone,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('POS cancellation WhatsApp notification failed', [
                    'booking_id' => $booking->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('POS cancellation notification dispatch failed', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /* =========================================================
     *  MARK PAYMENT RECEIVED
     * ========================================================= */
    public function markPaymentReceived(
        POSBooking $booking,
        float $amount,
        Carbon $paymentDate,
        ?string $notes = null,
        array $milestoneIds = []
    ): POSBooking {
        return DB::transaction(function () use ($booking, $amount, $paymentDate, $notes, $milestoneIds) {
            if (!in_array($booking->payment_status, ['unpaid', 'partial', 'credit'], true)) {
                throw new \Exception('Booking is not in payable state');
            }
            if ($amount <= 0) {
                throw new \Exception('Payment amount must be greater than zero');
            }

            $existingPaid = (float) ($booking->paid_amount ?? 0);
            $totalAmount  = (float) $booking->total_amount;
            $remaining    = max(0, $totalAmount - $existingPaid);

            if ($amount > ($remaining + 0.01)) {
                throw new \Exception('Payment amount cannot exceed the remaining payable amount');
            }

            $newPaidAmount = min($totalAmount, $existingPaid + $amount);
            $newStatus     = abs($newPaidAmount - $totalAmount) < 0.01
                ? POSBooking::PAYMENT_STATUS_PAID
                : POSBooking::PAYMENT_STATUS_PARTIAL;
            $paidMilestoneIdsForNotification = [];

            $bookingMilestones = \App\Models\QuotationMilestone::query()
                ->where('pos_booking_id', $booking->id)
                ->orderBy('sequence_no')
                ->lockForUpdate()
                ->get();

            $isMilestoneFlow = ((int) ($booking->is_milestone ?? 0) === 1) || $bookingMilestones->isNotEmpty();

            if ($isMilestoneFlow) {
                $dueMilestones = $bookingMilestones->filter(function ($item) {
                    return in_array($item->status, ['due', 'overdue'], true);
                })->values();

                if ($dueMilestones->isEmpty()) {
                    $nextPendingMilestone = $bookingMilestones->first(function ($item) {
                        return $item->status === 'pending';
                    });

                    if ($nextPendingMilestone) {
                        $nextPendingMilestone->update(['status' => 'due']);
                        $bookingMilestones = \App\Models\QuotationMilestone::query()
                            ->where('pos_booking_id', $booking->id)
                            ->orderBy('sequence_no')
                            ->lockForUpdate()
                            ->get();
                        $dueMilestones = $bookingMilestones->filter(function ($item) {
                            return in_array($item->status, ['due', 'overdue'], true);
                        })->values();
                    }
                }

                if ($dueMilestones->isEmpty()) {
                    throw new \Exception('No due milestone found for this booking.');
                }

                $normalizedMilestoneIds = collect($milestoneIds)
                    ->map(function ($id) {
                        return (int) $id;
                    })
                    ->filter(function ($id) {
                        return $id > 0;
                    })
                    ->unique()
                    ->values();

                if ($normalizedMilestoneIds->isEmpty()) {
                    $normalizedMilestoneIds = collect([(int) $dueMilestones->first()->id]);
                }

                $selectedMilestones = $dueMilestones->filter(function ($item) use ($normalizedMilestoneIds) {
                    return $normalizedMilestoneIds->contains((int) $item->id);
                })->values();

                if ($selectedMilestones->count() !== $normalizedMilestoneIds->count()) {
                    throw new \Exception('Only due milestones can be paid.');
                }

                $selectedMilestoneAmount = (float) $selectedMilestones->sum(function ($item) {
                    return (float) ($item->calculated_amount ?? $item->amount ?? 0);
                });

                if (abs($amount - $selectedMilestoneAmount) > 0.01) {
                    throw new \Exception('Payment amount must match selected due milestone amount (₹' . number_format($selectedMilestoneAmount, 2) . ').');
                }

                $paidMilestoneIdsForNotification = $selectedMilestones
                    ->pluck('id')
                    ->map(function ($id) {
                        return (int) $id;
                    })
                    ->values()
                    ->all();

                foreach ($selectedMilestones as $milestone) {
                    $milestone->update([
                        'status'  => 'paid',
                        'paid_at' => $paymentDate,
                    ]);
                }
            }

            $updateData = [
                'paid_amount'         => $newPaidAmount,
                'payment_status'      => $newStatus,
                'payment_received_at' => $paymentDate,
                'hold_expiry_at'      => null,
                'reminder_count'      => 0,
                'status'              => POSBooking::STATUS_CONFIRMED,
                'last_reminder_at'    => null,
            ];

            // If this was a credit note and now fully paid, mark it as redeemed
            if ($booking->payment_mode === POSBooking::PAYMENT_MODE_CREDIT_NOTE && $newStatus === POSBooking::PAYMENT_STATUS_PAID) {
                $updateData['credit_note_status'] = 'redeemed';
            }

            $booking->update($updateData);

            if ($isMilestoneFlow) {
                $milestonesAfterPayment = \App\Models\QuotationMilestone::query()
                    ->where('pos_booking_id', $booking->id)
                    ->orderBy('sequence_no')
                    ->lockForUpdate()
                    ->get();

                $hasDueOrOverdue = $milestonesAfterPayment->contains(function ($item) {
                    return in_array($item->status, ['due', 'overdue'], true);
                });

                if (!$hasDueOrOverdue) {
                    $nextPendingMilestone = $milestonesAfterPayment->first(function ($item) {
                        return $item->status === 'pending';
                    });

                    if ($nextPendingMilestone) {
                        $nextPendingMilestone->update(['status' => 'due']);
                    }

                    $milestonesAfterPayment = \App\Models\QuotationMilestone::query()
                        ->where('pos_booking_id', $booking->id)
                        ->orderBy('sequence_no')
                        ->lockForUpdate()
                        ->get();
                }

                $milestonePaidCount = (int) $milestonesAfterPayment->where('status', 'paid')->count();
                $milestoneAmountPaid = (float) $milestonesAfterPayment
                    ->where('status', 'paid')
                    ->sum(function ($item) {
                        return (float) ($item->calculated_amount ?? $item->amount ?? 0);
                    });

                $currentMilestone = $milestonesAfterPayment->first(function ($item) {
                    return in_array($item->status, ['due', 'overdue'], true);
                })
                    ?? $milestonesAfterPayment->first(function ($item) {
                        return $item->status === 'pending';
                    });

                $allMilestonesPaid = $milestonesAfterPayment->isNotEmpty()
                    && $milestonePaidCount === $milestonesAfterPayment->count();

                $booking->update([
                    'milestone_total'            => (int) $milestonesAfterPayment->count(),
                    'milestone_paid'             => $milestonePaidCount,
                    'milestone_amount_paid'      => round($milestoneAmountPaid, 2),
                    'milestone_amount_remaining' => max(0, round($totalAmount - $milestoneAmountPaid, 2)),
                    'current_milestone_id'       => $currentMilestone?->id,
                    'all_milestones_paid_at'     => $allMilestonesPaid ? now() : null,
                ]);
            }

            foreach ($booking->bookingHoardings as $bookingHoarding) {
                $this->hoardingBookingService->confirmInventoryForPosBookingHoarding($bookingHoarding);
            }

            try {
                $this->invoiceService->syncPaymentStatusFromPOSBooking(
                    $booking,
                    $newPaidAmount,
                    $paymentDate
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to sync invoice payment status', [
                    'pos_booking_id' => $booking->id,
                    'error'          => $e->getMessage(),
                ]);
            }

            Log::info('Payment marked as received', [
                'booking_id'         => $booking->id,
                'vendor_id'          => $booking->vendor_id,
                'amount'             => $amount,
                'paid_amount_before' => $existingPaid,
                'paid_amount_after'  => $newPaidAmount,
                'total'              => $totalAmount,
                'status'             => $newStatus,
                'notes'              => $notes,
            ]);

            DB::afterCommit(function () use ($booking, $amount, $paymentDate, $paidMilestoneIdsForNotification) {
                $this->sendPaymentReceivedNotifications((int) $booking->id, [
                    'payment_amount'     => round((float) $amount, 2),
                    'payment_date'       => $paymentDate->toDateString(),
                    'paid_milestone_ids' => $paidMilestoneIdsForNotification,
                ]);
            });

            return $booking->fresh();
        });
    }

    /* =========================================================
     *  UPDATE BOOKING
     * ========================================================= */
    public function updateBooking(POSBooking $booking, array $data): POSBooking
    {
        return DB::transaction(function () use ($booking, $data) {
            if (isset($data['hoarding_id']) || isset($data['start_date']) || isset($data['end_date'])) {
                $hoardingId = $data['hoarding_id'] ?? $booking->hoarding_id;
                $startDate  = $data['start_date']  ?? $booking->start_date;
                $endDate    = $data['end_date']     ?? $booking->end_date;
                $this->validateHoardingAvailability($hoardingId, $startDate, $endDate, $booking->id);
            }

            if (isset($data['base_amount']) || isset($data['discount_amount'])) {
                $pricing = $this->calculatePricing(array_merge($booking->toArray(), $data));
                $data    = array_merge($data, $pricing);
            }

            $booking->update($data);
            return $booking->fresh();
        });
    }

    /* =========================================================
     *  MARK AS CREDIT NOTE
     * ========================================================= */
    public function markAsCreditNote(POSBooking $booking, ?int $validityDays = null): POSBooking
    {
        return DB::transaction(function () use ($booking, $validityDays) {
            $days = $validityDays ?? $this->getCreditNoteDays();

            $booking->update([
                'payment_mode'         => POSBooking::PAYMENT_MODE_CREDIT_NOTE,
                'payment_status'       => POSBooking::PAYMENT_STATUS_CREDIT,
                'status'               => POSBooking::STATUS_CONFIRMED,
                'hold_expiry_at'       => null,
                'credit_note_number'   => $this->generateCreditNoteNumber(),
                'credit_note_date'     => now(),
                'credit_note_due_date' => now()->addDays($days),
                'credit_note_status'   => POSBooking::CREDIT_NOTE_STATUS_ACTIVE,
            ]);

            return $booking->fresh();
        });
    }

    /* =========================================================
     *  CANCEL CREDIT NOTE
     * ========================================================= */
    public function cancelCreditNote(POSBooking $booking, string $reason): POSBooking
    {
        return DB::transaction(function () use ($booking, $reason) {
            if (!$booking->isCreditNote()) {
                throw new \Exception('This booking is not a credit note');
            }

            $booking->update([
                'credit_note_status' => POSBooking::CREDIT_NOTE_STATUS_CANCELLED,
                'payment_status'     => POSBooking::PAYMENT_STATUS_UNPAID,
                'cancellation_reason' => $reason,
            ]);

            return $booking->fresh();
        });
    }

    /* =========================================================
     *  RELEASE / CANCEL BOOKING
     * ========================================================= */
    public function releaseBooking(POSBooking $booking, string $reason): POSBooking
    {
        return DB::transaction(function () use ($booking, $reason) {
            if (!in_array($booking->payment_status, [POSBooking::PAYMENT_STATUS_UNPAID, POSBooking::PAYMENT_STATUS_CREDIT])) {
                throw new \Exception('Can only release unpaid or credit bookings');
            }

            $booking->update([
                'status'              => POSBooking::STATUS_CANCELLED,
                'cancelled_at'        => now(),
                'cancellation_reason' => $reason,
                'hold_expiry_at'      => null,
                'reminder_count'      => 0,
                'last_reminder_at'    => null,
            ]);

            return $booking->fresh();
        });
    }

    public function cancelBooking(POSBooking $booking, string $reason): POSBooking
    {
        return DB::transaction(function () use ($booking, $reason) {
            if ($booking->status === POSBooking::STATUS_CANCELLED) {
                throw new \Exception('Booking is already cancelled');
            }

            $booking->update([
                'status'              => POSBooking::STATUS_CANCELLED,
                'cancelled_at'        => now(),
                'cancellation_reason' => $reason,
                'hold_expiry_at'      => null,
                'reminder_count'      => 0,
                'last_reminder_at'    => null,
            ]);

            foreach ($booking->bookingHoardings as $bh) {
                $hoarding = $bh->hoarding;
                if ($hoarding && (int) $hoarding->held_by_booking_id === (int) $booking->id) {
                    $hoarding->is_on_hold = false;
                    $hoarding->hold_till = null;
                    $hoarding->held_by_booking_id = null;
                    $hoarding->save();
                }
            }

            DB::afterCommit(function () use ($booking, $reason) {
                $this->dispatchBookingCancelledNotifications((int) $booking->id, $reason);
            });

            return $booking->fresh();
        });
    }

    /* =========================================================
     *  STATUS TRANSITION
     * ========================================================= */
    public function transitionStatus(POSBooking $booking, string $newStatus): POSBooking
    {
        $validTransitions = [
            'draft'        => ['confirmed'],
            'confirmed'    => ['partial_paid', 'cancelled'],
            'partial_paid' => ['paid', 'cancelled'],
            'paid'         => ['completed'],
        ];

        $current = $booking->status;
        if (!isset($validTransitions[$current]) || !in_array($newStatus, $validTransitions[$current])) {
            throw new \Exception("Invalid status transition from {$current} to {$newStatus}");
        }

        return DB::transaction(function () use ($booking, $newStatus) {
            $booking->status = $newStatus;
            $booking->save();

            if (in_array($newStatus, ['confirmed', 'partial_paid', 'paid'])) {
                foreach ($booking->bookingHoardings as $bh) {
                    $hoarding = $bh->hoarding;
                    $hoarding->is_on_hold            = true;
                    $hoarding->hold_till             = $booking->end_date;
                    $hoarding->held_by_booking_id    = $booking->id;
                    $hoarding->save();
                }
            }

            if ($newStatus === 'cancelled' || $newStatus === 'completed') {
                foreach ($booking->bookingHoardings as $bh) {
                    $hoarding = $bh->hoarding;
                    if ($hoarding->held_by_booking_id == $booking->id) {
                        $hoarding->is_on_hold         = false;
                        $hoarding->hold_till          = null;
                        $hoarding->held_by_booking_id = null;
                        $hoarding->save();
                    }
                }
            }

            return $booking->fresh();
        });
    }

    /* =========================================================
     *  AVAILABILITY
     * ========================================================= */
    public function checkAvailability($hoardingId, $fromDate, $toDate): bool
    {
        $hoarding = Hoarding::lockForUpdate()->find($hoardingId);
        if (!$hoarding) return false;

        if ($hoarding->is_on_hold && $hoarding->hold_till && $hoarding->hold_till->isFuture()) {
            return false;
        }

        $hasBooking = \Modules\POS\Models\POSBookingHoarding::where('hoarding_id', $hoardingId)
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('start_date', [$fromDate, $toDate])
                    ->orWhereBetween('end_date', [$fromDate, $toDate])
                    ->orWhere(function ($q2) use ($fromDate, $toDate) {
                        $q2->where('start_date', '<=', $fromDate)->where('end_date', '>=', $toDate);
                    });
            })
            ->whereHas('booking', function ($q) {
                $q->whereIn('status', ['pending_payment', 'confirmed', 'active']);
            })
            ->exists();

        return !$hasBooking;
    }

    public function isHoardingAvailable($hoardingId, $fromDate, $toDate, $excludeBookingId = null): bool
    {
        $query = \Modules\POS\Models\POSBookingHoarding::where('hoarding_id', $hoardingId)
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('start_date', [$fromDate, $toDate])
                    ->orWhereBetween('end_date', [$fromDate, $toDate])
                    ->orWhere(function ($q2) use ($fromDate, $toDate) {
                        $q2->where('start_date', '<=', $fromDate)->where('end_date', '>=', $toDate);
                    });
            })
            ->whereHas('booking', function ($q) use ($excludeBookingId) {
                $q->whereIn('status', ['confirmed', 'partial_paid', 'paid'])
                    ->when($excludeBookingId, fn($q2) => $q2->where('id', '!=', $excludeBookingId));
            });

        $hoarding = \App\Models\Hoarding::find($hoardingId);
        if (
            $hoarding &&
            $hoarding->is_on_hold &&
            $hoarding->hold_till &&
            $hoarding->hold_till >= now() &&
            $hoarding->held_by_booking_id !== $excludeBookingId
        ) {
            return false;
        }

        return !$query->exists();
    }

    public function holdHoardings(POSBooking $booking): void
    {
        foreach ($booking->bookingHoardings as $bh) {
            $hoarding                     = $bh->hoarding;
            $hoarding->is_on_hold         = true;
            $hoarding->hold_till          = now()->addMinutes(15);
            $hoarding->held_by_booking_id = $booking->id;
            $hoarding->save();
        }
    }

    public function releaseHoardings(POSBooking $booking): void
    {
        foreach ($booking->bookingHoardings as $bh) {
            $hoarding = $bh->hoarding;
            if ($hoarding->held_by_booking_id == $booking->id) {
                $hoarding->is_on_hold         = false;
                $hoarding->hold_till          = null;
                $hoarding->held_by_booking_id = null;
                $hoarding->save();
            }
        }
    }

    /* =========================================================
     *  STATISTICS & QUERIES
     * ========================================================= */
    public function getVendorBookings(int $vendorId, array $filters = [])
    {
        $query = POSBooking::with(['hoardings', 'customer', 'approver'])
            ->forVendor($vendorId);

        if (isset($filters['status']))         $query->byStatus($filters['status']);
        if (isset($filters['payment_status'])) $query->byPaymentStatus($filters['payment_status']);
        if (isset($filters['booking_type']))   $query->where('booking_type', $filters['booking_type']);

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

    public function getVendorStatistics(int $vendorId): array
    {
        $bookings = POSBooking::forVendor($vendorId);

        return [
            'total_bookings'       => $bookings->count(),
            'active_bookings'      => $bookings->active()->count(),
            'total_revenue'        => $bookings->where('payment_status', POSBooking::PAYMENT_STATUS_PAID)->sum('total_amount'),
            'pending_payments'     => $bookings->unpaid()->sum('total_amount'),
            'active_credit_notes'  => $bookings->creditNotes()->count(),
            'credit_notes_value'   => $bookings->creditNotes()->sum('total_amount'),
            'total_customers'      => POSBooking::where('vendor_id', $vendorId)
                ->distinct('customer_phone')
                ->count('customer_phone'),
        ];
    }

    /* =========================================================
     *  HELPERS — private / protected
     * ========================================================= */

    protected function attachHoardingToBooking(
        POSBooking $booking,
        Hoarding $hoarding,
        $start,
        $end,
        $pricePerMonth = null,
        $discount = 0,
        array $pricingLine = []
    ): void {
        $durationDays = $pricingLine['duration_days'] ?? $this->calculateDurationDays($start, $end);
        $monthlyRate  = $pricePerMonth !== null
            ? (float) $pricePerMonth
            : (float) ($hoarding->monthly_price ?? $hoarding->base_monthly_price ?? 0);
        $months       = (int) ceil($durationDays / 30);
        $base         = array_key_exists('base_amount', $pricingLine)
            ? (float) $pricingLine['base_amount']
            : round($monthlyRate * $months, 2);
        $gstRate      = $this->getGSTRate();
        $discount     = array_key_exists('discount_amount', $pricingLine)
            ? (float) $pricingLine['discount_amount']
            : (float) $discount;
        $taxableAmount = max(0, round($base - $discount, 2));
        $tax           = array_key_exists('tax_amount', $pricingLine)
            ? (float) $pricingLine['tax_amount']
            : round($taxableAmount * ($gstRate / 100), 2);
        $total         = array_key_exists('total_amount', $pricingLine)
            ? (float) $pricingLine['total_amount']
            : round($taxableAmount + $tax, 2);

        \Modules\POS\Models\POSBookingHoarding::create([
            'pos_booking_id'    => $booking->id,
            'hoarding_id'       => $hoarding->id,
            'hoarding_price'    => round($base, 2),
            'hoarding_discount' => round($discount, 2),
            'hoarding_tax'      => round($tax, 2),
            'hoarding_total'    => round($total, 2),
            'start_date'        => $start,
            'end_date'          => $end,
            'duration_days'     => $durationDays,
            'duration_type'     => 'days',
            'status'            => 'pending',
        ]);
    }

    private function createMilestonesForPOSBooking(POSBooking $booking, array $milestonesData): void
    {
        $totalAmount = round((float) $booking->total_amount, 2);
        $amountType  = $milestonesData[0]['amount_type'] ?? 'percentage';

        $sumPct = 0.0;
        $sumFixed = 0.0;
        foreach ($milestonesData as $m) {
            if (($m['amount_type'] ?? '') === 'percentage') {
                $sumPct += (float) ($m['amount'] ?? 0);
            } else {
                $sumFixed += (float) ($m['amount'] ?? 0);
            }
        }

        if ($amountType === 'percentage' && abs($sumPct - 100) > 0.01) {
            $booking->update(['is_milestone' => false]);
            return;
        }

        if ($amountType === 'fixed') {
            $delta = round($totalAmount - $sumFixed, 2);
            if (abs($delta) > 0.01 && !empty($milestonesData)) {
                $lastIndex = count($milestonesData) - 1;
                $adjustedLastAmount = round((float) ($milestonesData[$lastIndex]['amount'] ?? 0) + $delta, 2);

                if ($adjustedLastAmount <= 0) {
                    $booking->update(['is_milestone' => false]);
                    return;
                }

                $milestonesData[$lastIndex]['amount'] = $adjustedLastAmount;
            }

            $sumFixed = round(array_sum(array_map(function ($m) {
                return (float) ($m['amount'] ?? 0);
            }, $milestonesData)), 2);

            if (abs($sumFixed - $totalAmount) > 0.01) {
                $booking->update(['is_milestone' => false]);
                return;
            }
        }

        \App\Models\QuotationMilestone::query()
            ->where('pos_booking_id', $booking->id)
            ->delete();

        $created = [];
        $sequenceNo = 1;

        foreach ($milestonesData as $idx => $milestoneData) {
            $isFirst = ($idx === 0);
            $milestoneAmountType = $milestoneData['amount_type'] ?? $amountType;
            $calculated = $milestoneAmountType === 'percentage'
                ? round(($totalAmount * (float) ($milestoneData['amount'] ?? 0)) / 100, 2)
                : round((float) ($milestoneData['amount'] ?? 0), 2);

            $created[] = \App\Models\QuotationMilestone::create([
                'pos_booking_id'    => $booking->id,
                'quotation_id'      => null,
                'title'             => $milestoneData['title'] ?? ('Milestone ' . $sequenceNo),
                'description'       => $milestoneData['description'] ?? null,
                'sequence_no'       => $sequenceNo++,
                'amount_type'       => $milestoneAmountType,
                'amount'            => round((float) ($milestoneData['amount'] ?? 0), 2),
                'calculated_amount' => $calculated,
                'status'            => $isFirst ? 'due' : 'pending',
                'due_date'          => $milestoneData['due_date'] ?? null,
                'vendor_notes'      => $milestoneData['vendor_notes'] ?? null,
            ]);
        }

        $booking->update([
            'milestone_total'            => count($created),
            'milestone_paid'             => 0,
            'milestone_amount_paid'      => 0,
            'milestone_amount_remaining' => $totalAmount,
            'current_milestone_id'       => $created[0]->id ?? null,
            'all_milestones_paid_at'     => null,
        ]);
    }

    protected function sendPaymentReceivedNotifications(int $bookingId, array $context = []): void
    {
        try {
            $booking = POSBooking::query()->find($bookingId);
            if (!$booking) {
                return;
            }

            $notification = new \App\Notifications\PosBookingConfirmedNotification($booking, $context);
            $recipientIds = collect();

            if (!empty($booking->customer_id)) {
                $recipientIds->push((int) $booking->customer_id);
            }

            if (!empty($booking->vendor_id)) {
                $recipientIds->push((int) $booking->vendor_id);
            }

            $adminIds = \App\Models\User::query()
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'super_admin']))
                ->pluck('id');

            $recipientIds = $recipientIds
                ->merge($adminIds)
                ->filter(fn($id) => (int) $id > 0)
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
                        \Illuminate\Support\Facades\Notification::sendNow($user, $notification);
                    }
                });
        } catch (\Throwable $e) {
            Log::warning('POS payment received notification dispatch failed', [
                'booking_id' => $bookingId,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    protected function validateHoardingAvailability(int $hoardingId, string $startDate, string $endDate, ?int $excludeBookingId = null): void
    {
        $conflictingBookings = DB::table('bookings')
            ->where('hoarding_id', $hoardingId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(fn($q) => $q->where('start_date', '<=', $startDate)->where('end_date', '>=', $endDate));
            })
            ->whereIn('status', ['confirmed', 'payment_hold'])
            ->when($excludeBookingId, fn($query) => $query->where('id', '!=', $excludeBookingId))
            ->count();

        $conflictingPOSBookings = POSBooking::where('hoarding_id', $hoardingId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(fn($q) => $q->where('start_date', '<=', $startDate)->where('end_date', '>=', $endDate));
            })
            ->whereIn('status', [POSBooking::STATUS_CONFIRMED, POSBooking::STATUS_ACTIVE])
            ->when($excludeBookingId, fn($query) => $query->where('id', '!=', $excludeBookingId))
            ->count();

        if ($conflictingBookings > 0 || $conflictingPOSBookings > 0) {
            throw new \Exception('Hoarding not available for the selected dates. There are conflicting bookings.');
        }
    }

    protected function calculatePricing(array $data): array
    {
        $baseAmount     = round((float) ($data['base_amount'] ?? 0), 2);
        $discountAmount = round(min(max(0, (float) ($data['discount_amount'] ?? 0)), $baseAmount), 2);
        $gstRate        = $this->getGSTRate();
        $afterDiscount  = max(0, round($baseAmount - $discountAmount, 2));
        $taxAmount      = ($afterDiscount * $gstRate) / 100;
        $totalAmount    = $afterDiscount + $taxAmount;

        return [
            'base_amount'     => $baseAmount,
            'discount_amount' => $discountAmount,
            'tax_amount'      => round($taxAmount, 2),
            'total_amount'    => round($totalAmount, 2),
        ];
    }

    protected function calculateDurationDays(string $startDate, string $endDate): int
    {
        return Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
    }

    private function generateCreditNoteNumber(): string
    {
        $prefix = 'CN-' . date('Y');
        $latest = POSBooking::where('credit_note_number', 'like', $prefix . '%')->latest()->first();

        $number = 1001;
        if ($latest && preg_match('/CN-\d+-(\d+)/', $latest->credit_note_number, $matches)) {
            $number = (int) $matches[1] + 1;
        }

        return $prefix . '-' . $number;
    }

    /* =========================================================
     *  SETTINGS HELPERS
     * ========================================================= */
    public function getGSTRate(): float
    {
        return (float) ($this->settingsService->get('gst_rate') ?? 18);
    }

    public function isAutoApprovalEnabled(): bool
    {
        return (bool) $this->settingsService->get('pos_auto_approval', true);
    }

    public function isAutoInvoiceEnabled(): bool
    {
        return (bool) $this->settingsService->get('pos_auto_invoice', true);
    }

    public function isCashPaymentAllowed(): bool
    {
        return (bool) $this->settingsService->get('pos_allow_cash_payment', true);
    }

    public function isCreditNoteAllowed(): bool
    {
        return (bool) $this->settingsService->get('pos_allow_credit_note', true);
    }

    public function getCreditNoteDays(): int
    {
        return (int) ($this->settingsService->get('pos_credit_note_days') ?? 30);
    }

    public function isSMSNotificationEnabled(): bool
    {
        return (bool) $this->settingsService->get('pos_enable_sms_notification', true);
    }

    public function isWhatsAppNotificationEnabled(): bool
    {
        return (bool) $this->settingsService->get('pos_enable_whatsapp_notification', true);
    }

    public function isEmailNotificationEnabled(): bool
    {
        return (bool) $this->settingsService->get('pos_enable_email_notification', true);
    }

    public function markAsCashCollected(POSBooking $booking, float $amount, ?string $reference = null): POSBooking
    {
        return DB::transaction(function () use ($booking, $amount, $reference) {
            $booking->update([
                'payment_mode'    => POSBooking::PAYMENT_MODE_CASH,
                'payment_status'  => POSBooking::PAYMENT_STATUS_PAID,
                'paid_amount'     => $amount,
                'payment_reference' => $reference,
            ]);
            return $booking->fresh();
        });
    }
}

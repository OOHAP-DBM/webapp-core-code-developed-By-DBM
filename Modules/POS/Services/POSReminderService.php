<?php

namespace Modules\POS\Services;

use App\Mail\PosPaymentReminderMail;
use App\Notifications\PosPaymentReminderInAppNotification;
use App\Services\Whatsapp\TwilioWhatsappService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Modules\POS\Jobs\ProcessScheduledPosReminderJob;
use Modules\POS\Models\POSBooking;
use Modules\POS\Models\POSBookingReminder;

class POSReminderService
{
    public const MAX_REMINDERS = 3;

    public function getRemainingReminderSlots(POSBooking $booking): int
    {
        $pendingCount = $booking->relationLoaded('scheduledReminders')
            ? $booking->scheduledReminders->where('status', POSBookingReminder::STATUS_PENDING)->count()
            : $booking->scheduledReminders()->pending()->count();

        return max(0, self::MAX_REMINDERS - $pendingCount);
    }

    public function serializeScheduledReminders(POSBooking $booking): array
    {
        $reminders = $booking->relationLoaded('scheduledReminders')
            ? $booking->scheduledReminders
            : $booking->scheduledReminders()
            ->whereIn('status', [POSBookingReminder::STATUS_PENDING, POSBookingReminder::STATUS_SENT])
            ->orderBy('scheduled_at')
            ->get();

        return $reminders
            ->sortBy('scheduled_at')
            ->values()
            ->map(function (POSBookingReminder $reminder) {
                return [
                    'id' => (int) $reminder->id,
                    'scheduled_at' => optional($reminder->scheduled_at)->toIso8601String(),
                    'status' => $reminder->status,
                    'sent_at' => optional($reminder->sent_at)->toIso8601String(),
                    'can_edit' => $reminder->status === POSBookingReminder::STATUS_PENDING,
                ];
            })
            ->all();
    }

    public function replacePendingSchedules(POSBooking $booking, array $scheduledReminders, ?int $actorId = null): array
    {
        $this->ensureBookingCanReceiveReminders($booking);

        $normalizedReminders = collect($scheduledReminders)
            ->map(function ($item) {
                if (is_array($item)) {
                    return $item['scheduled_at'] ?? null;
                }

                return $item;
            })
            ->filter()
            ->map(function ($value) {
                return Carbon::parse((string) $value)->seconds(0);
            })
            ->sort()
            ->unique(function (Carbon $scheduledAt) {
                return $scheduledAt->toDateTimeString();
            })
            ->values();

        if ($normalizedReminders->isEmpty()) {
            throw ValidationException::withMessages([
                'scheduled_reminders' => 'Add at least one reminder before scheduling.',
            ]);
        }

        foreach ($normalizedReminders as $scheduledAt) {
            if ($scheduledAt->lt(now()->startOfMinute())) {
                throw ValidationException::withMessages([
                    'scheduled_reminders' => 'Reminder time must be now or in the future.',
                ]);
            }
        }

        $maxPendingAllowed = self::MAX_REMINDERS;
        if ($normalizedReminders->count() > $maxPendingAllowed) {
            throw ValidationException::withMessages([
                'scheduled_reminders' => "Only {$maxPendingAllowed} reminder slot(s) are available for this booking.",
            ]);
        }

        DB::transaction(function () use ($booking, $normalizedReminders, $actorId) {
            $booking->scheduledReminders()
                ->where('status', POSBookingReminder::STATUS_PENDING)
                ->delete();

            foreach ($normalizedReminders as $scheduledAt) {
                $reminder = $booking->scheduledReminders()->create([
                    'scheduled_at' => $scheduledAt,
                    'status' => POSBookingReminder::STATUS_PENDING,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]);

                // Queue an exact-time processor so reminders still work when the scheduler is not running.
                ProcessScheduledPosReminderJob::dispatch((int) $reminder->id)
                    ->delay($scheduledAt->copy()->timezone(config('app.timezone')));
            }
        });

        $booking->unsetRelation('scheduledReminders');
        $booking->load(['scheduledReminders' => function ($query) {
            $query->whereIn('status', [POSBookingReminder::STATUS_PENDING, POSBookingReminder::STATUS_SENT])
                ->orderBy('scheduled_at');
        }]);

        return [
            'scheduled_reminders' => $this->serializeScheduledReminders($booking),
            'remaining_reminder_slots' => $this->getRemainingReminderSlots($booking),
        ];
    }

    public function scheduleSingleReminder(POSBooking $booking, Carbon $scheduledAt, ?int $actorId = null): array
    {
        $existingPendingReminders = $booking->scheduledReminders()
            ->pending()
            ->orderBy('scheduled_at')
            ->pluck('scheduled_at')
            ->map(function ($value) {
                return Carbon::parse($value)->toDateTimeString();
            })
            ->all();

        $existingPendingReminders[] = $scheduledAt->toDateTimeString();

        return $this->replacePendingSchedules($booking, $existingPendingReminders, $actorId);
    }

    public function sendImmediateReminder(POSBooking $booking, ?POSBookingReminder $scheduledReminder = null, ?int $actorId = null): array
    {
        // Log entry to confirm this function is called and queue worker is running
        \Log::info('[REMINDER] sendImmediateReminder called', [
            'booking_id' => $booking->id,
            'scheduled_reminder_id' => $scheduledReminder ? $scheduledReminder->id : null,
            'actor_id' => $actorId,
            'queue_connection' => config('queue.default'),
            'queue_worker_pid' => getmypid(),
            'env' => app()->environment(),
            'timestamp' => now()->toDateTimeString(),
        ]);
        $bookingError = $this->getBookingReminderError($booking);
        if ($bookingError !== null) {
            return [
                'success' => false,
                'status' => 422,
                'message' => $bookingError,
            ];
        }

        if ($scheduledReminder === null && $this->getRemainingReminderSlots($booking) <= 0) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'Maximum pending reminders already scheduled (3 limit)',
            ];
        }

        $nextReminderCount = ((int) $booking->reminder_count) + 1;
        $reminderSentAt = now();

        DB::transaction(function () use ($booking, $scheduledReminder, $nextReminderCount, $reminderSentAt) {
            $booking->update([
                'reminder_count' => $nextReminderCount,
                'last_reminder_at' => $reminderSentAt,
            ]);

            if ($scheduledReminder) {
                $scheduledReminder->update([
                    'updated_by' => $scheduledReminder->updated_by,
                ]);
            }
        });

        if (!$booking->relationLoaded('customer')) {
            $booking->load('customer:id,email');
        }

        $bookingCustomerEmail = trim((string) ($booking->customer_email ?? ''));
        $customerProfileEmail = trim((string) ($booking->customer?->email ?? ''));

        $emailRecipient = null;
        if (!empty($bookingCustomerEmail) && filter_var($bookingCustomerEmail, FILTER_VALIDATE_EMAIL)) {
            $emailRecipient = $bookingCustomerEmail;
        } elseif (!empty($customerProfileEmail) && filter_var($customerProfileEmail, FILTER_VALIDATE_EMAIL)) {
            $emailRecipient = $customerProfileEmail;
        }

        $emailSent = false;
        $emailError = null;

        try {
            if (!empty($emailRecipient)) {
                Mail::to($emailRecipient)
                    ->sendNow(new PosPaymentReminderMail($booking, $booking->customer, $nextReminderCount));

                Log::info('Payment reminder email sent', [
                    'booking_id' => $booking->id,
                    'email' => $emailRecipient,
                    'reminder_count' => $nextReminderCount,
                ]);

                $emailSent = true;
            } else {
                $emailError = 'No valid customer email found on booking or customer profile';
                Log::warning('Payment reminder email skipped - no valid recipient email', [
                    'booking_id' => $booking->id,
                    'booking_customer_email' => $booking->customer_email,
                    'customer_id' => $booking->customer_id,
                    'customer_profile_email' => $booking->customer?->email,
                ]);
            }
        } catch (\Exception $e) {
            $emailError = $e->getMessage();
            Log::warning('Failed to send payment reminder email', [
                'booking_id' => $booking->id,
                'email' => $emailRecipient,
                'error' => $e->getMessage(),
            ]);
        }

        $inAppSent = false;
        $inAppError = null;

        try {
            if (!empty($booking->customer_id) && $booking->customer) {
                $booking->customer->notify(new PosPaymentReminderInAppNotification($booking, $nextReminderCount));
                $inAppSent = true;
            } else {
                $inAppError = 'Customer account not found for in-app notification';
                Log::warning('Payment reminder in-app skipped - customer not found', [
                    'booking_id' => $booking->id,
                    'customer_id' => $booking->customer_id,
                ]);
            }
        } catch (\Throwable $e) {
            $inAppError = $e->getMessage();
            Log::warning('Failed to send payment reminder in-app notification', [
                'booking_id' => $booking->id,
                'customer_id' => $booking->customer_id,
                'error' => $e->getMessage(),
            ]);
        }

        $whatsappSent = $this->sendReminderWhatsAppMessage($booking);

        $deliverySuccess = $emailSent || $whatsappSent || $inAppSent;

        if ($emailSent && $whatsappSent && $inAppSent) {
            $message = 'Reminder sent successfully via email, WhatsApp, and in-app notification';
        } elseif ($deliverySuccess) {
            $successfulChannels = [];
            if ($emailSent) {
                $successfulChannels[] = 'email';
            }
            if ($whatsappSent) {
                $successfulChannels[] = 'WhatsApp';
            }
            if ($inAppSent) {
                $successfulChannels[] = 'in-app notification';
            }
            $message = 'Reminder sent via ' . implode(', ', $successfulChannels);
        } else {
            $message = 'Reminder could not be delivered via email, WhatsApp, or in-app notification';
        }

        $historyReminder = $scheduledReminder;
        if ($historyReminder === null) {
            $historyReminder = $booking->scheduledReminders()->create([
                'scheduled_at' => $reminderSentAt,
                'status' => $deliverySuccess ? POSBookingReminder::STATUS_SENT : POSBookingReminder::STATUS_FAILED,
                'sent_at' => $deliverySuccess ? $reminderSentAt : null,
                'created_by' => $actorId,
                'updated_by' => $actorId,
                'error_message' => $deliverySuccess ? null : $message,
            ]);
        } else {
            $historyReminder->update([
                'status' => $deliverySuccess ? POSBookingReminder::STATUS_SENT : POSBookingReminder::STATUS_FAILED,
                'sent_at' => $deliverySuccess ? $reminderSentAt : null,
                'error_message' => $deliverySuccess ? null : $message,
            ]);
        }

        $booking->unsetRelation('scheduledReminders');
        $booking->load(['scheduledReminders' => function ($query) {
            $query->whereIn('status', [POSBookingReminder::STATUS_PENDING, POSBookingReminder::STATUS_SENT])
                ->orderBy('scheduled_at');
        }]);

        return [
            'success' => $deliverySuccess,
            'status' => $deliverySuccess ? 200 : 500,
            'message' => $message,
            'data' => [
                'reminder_count' => $nextReminderCount,
                'last_reminder_at' => $reminderSentAt,
                'remaining_reminder_slots' => $this->getRemainingReminderSlots($booking),
                'scheduled_reminders' => $this->serializeScheduledReminders($booking),
                'channels' => [
                    'email' => [
                        'sent' => $emailSent,
                        'recipient' => $emailRecipient,
                        'error' => $emailError,
                    ],
                    'whatsapp' => [
                        'sent' => $whatsappSent,
                    ],
                    'in_app' => [
                        'sent' => $inAppSent,
                        'error' => $inAppError,
                    ],
                ],
            ],
        ];
    }

    public function processDueReminders(int $limit = 25): int
    {
        $dueReminderIds = POSBookingReminder::query()
            ->pending()
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->pluck('id');

        $processedCount = 0;
        foreach ($dueReminderIds as $reminderId) {
            if ($this->processScheduledReminder((int) $reminderId)) {
                $processedCount++;
            }
        }

        return $processedCount;
    }

    public function processScheduledReminder(int $reminderId): bool
    {
        $lockKey = "pos-booking-scheduled-reminder:{$reminderId}";

        return (bool) Cache::lock($lockKey, 30)->get(function () use ($reminderId) {
            $reminder = POSBookingReminder::with(['booking.customer'])
                ->find($reminderId);

            if (!$reminder || $reminder->status !== POSBookingReminder::STATUS_PENDING) {
                return false;
            }

            if ($reminder->scheduled_at && $reminder->scheduled_at->isFuture()) {
                return false;
            }

            $booking = $reminder->booking;
            if (!$booking) {
                $reminder->update([
                    'status' => POSBookingReminder::STATUS_FAILED,
                    'error_message' => 'Booking not found for scheduled reminder.',
                ]);

                return false;
            }

            $bookingError = $this->getBookingReminderError($booking);
            if ($bookingError !== null) {
                $reminder->update([
                    'status' => POSBookingReminder::STATUS_CANCELLED,
                    'error_message' => $bookingError,
                ]);

                return false;
            }

            $result = $this->sendImmediateReminder($booking, $reminder);

            return (bool) ($result['success'] ?? false);
        });
    }

    private function ensureBookingCanReceiveReminders(POSBooking $booking): void
    {
        $message = $this->getBookingReminderError($booking);
        if ($message !== null) {
            throw ValidationException::withMessages([
                'scheduled_reminders' => $message,
            ]);
        }
    }

    private function getBookingReminderError(POSBooking $booking): ?string
    {
        if (
            !in_array($booking->payment_status, [POSBooking::PAYMENT_STATUS_UNPAID, POSBooking::PAYMENT_STATUS_PARTIAL, POSBooking::PAYMENT_STATUS_CREDIT], true)
            || $booking->status === POSBooking::STATUS_CANCELLED  || ($booking->payment_mode === POSBooking::PAYMENT_MODE_CREDIT_NOTE && $booking->credit_note_status !== POSBooking::CREDIT_NOTE_STATUS_ACTIVE)
        ) {
            return 'Can only send reminders for unpaid, partial paid, or credit bookings that are not cancelled';
        }

        return null;
    }

    private function sendReminderWhatsAppMessage(POSBooking $booking): bool
    {
        try {
            if (!$booking->relationLoaded('bookingHoardings')) {
                $booking->load('bookingHoardings.hoarding');
            }

            $phone = $booking->customer_phone;
            if (empty($phone) || $phone === 'N/A') {
                Log::warning('Reminder WhatsApp skipped - no valid phone', [
                    'booking_id' => $booking->id,
                    'phone' => $phone,
                ]);
                return false;
            }

            $hoardingLines = $booking->bookingHoardings->map(function ($bookingHoarding) {
                $hoarding = $bookingHoarding->hoarding;

                return '• ' . ($hoarding->title ?? 'Hoarding') . " ({$bookingHoarding->start_date} → {$bookingHoarding->end_date})";
            })->implode("\n");

            $totalAmount = (float) $booking->total_amount;
            $paidAmount = (float) ($booking->paid_amount ?? 0);
            $remainingAmount = max(0, $totalAmount - $paidAmount);
            $paidFormatted = number_format($paidAmount, 2);
            $totalFormatted = number_format($totalAmount, 2);
            $remainingFormatted = number_format($remainingAmount, 2);

            $message = "⏰ *Payment Reminder - Invoice #{$booking->invoice_number}*\n\n"
                . "Hello *{$booking->customer_name}*,\n\n"
                . "This is a friendly payment reminder for your POS booking.\n\n"
                . "📋 *Booking Details:*\n"
                . "Status: {$booking->status}\n"
                . "Reminder Count: {$booking->reminder_count}/3\n\n"
                . "💰 *Payment Status:*\n"
                . "Total Amount: ₹{$totalFormatted}\n"
                . "Paid Amount: ₹{$paidFormatted}\n"
                . "Outstanding Balance: ₹{$remainingFormatted}\n\n"
                . "🏛️ *Hoardings Booked:*\n{$hoardingLines}\n\n"
                . "Please clear the outstanding balance at your earliest convenience.\n\n"
                . 'Thank you!';

            $normalizedPhone = preg_replace('/\D+/', '', $phone);

            if (empty($normalizedPhone) || strlen($normalizedPhone) < 10) {
                Log::warning('Reminder WhatsApp skipped - invalid phone', [
                    'booking_id' => $booking->id,
                    'original_phone' => $phone,
                    'normalized_phone' => $normalizedPhone,
                ]);
                return false;
            }

            if (!str_starts_with($normalizedPhone, '91')) {
                $normalizedPhone = '91' . ltrim($normalizedPhone, '0');
            }

            $normalizedPhone = '+' . $normalizedPhone;

            Log::info('Reminder WhatsApp attempting to send', [
                'booking_id' => $booking->id,
                'phone' => $normalizedPhone,
                'reminder_count' => $booking->reminder_count,
            ]);

            $whatsapp = app(TwilioWhatsappService::class);
            $sent = $whatsapp->send($normalizedPhone, $message);

            Log::info('Reminder WhatsApp notification dispatched', [
                'booking_id' => $booking->id,
                'phone' => $normalizedPhone,
                'sent' => $sent,
                'reminder_count' => $booking->reminder_count,
                'message_preview' => substr($message, 0, 100),
            ]);

            return (bool) $sent;
        } catch (\Throwable $e) {
            Log::error('Reminder WhatsApp notification failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}

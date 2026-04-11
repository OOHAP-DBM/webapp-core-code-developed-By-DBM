<?php

namespace App\Mail;

use App\Models\QuotationMilestone;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Route;
use Modules\POS\Models\POSBooking;

class PosPaymentReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Queue worker timeout safety for SMTP delays.
     */
    public int $timeout = 120;

    public int $tries = 3;

    public array $backoff = [10, 60, 180];

    public function __construct(
        public POSBooking $booking,
        public ?User $customer = null,
        public int $reminderCount = 1
    ) {}

    public function envelope(): Envelope
    {
        $invoiceNumber = $this->booking->invoice_number ?? $this->booking->id;
        $milestone = $this->resolveReminderMilestone();
        $subject = $milestone
            ? 'Payment Reminder - Invoice ' . $invoiceNumber . ' (Milestone Due).'
            : 'Payment Reminder (' . $this->reminderCount . '/3) - Invoice #' . $invoiceNumber;

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $totalAmount = (float) ($this->booking->total_amount ?? 0);
        $paidAmount = (float) ($this->booking->paid_amount ?? 0);
        $remainingAmount = max(0, $totalAmount - $paidAmount);
        $milestone = $this->resolveReminderMilestone();
        $hasMilestoneReminder = $milestone !== null;

        $bookingCreatedAt = $this->booking->created_at;
        $bookingDate = $bookingCreatedAt
            ? $bookingCreatedAt->format('d M Y, h:i A')
            : 'N/A';

        $milestoneAmountDue = $milestone
            ? number_format((float) ($milestone->calculated_amount ?? $milestone->amount ?? 0), 2)
            : null;

        $milestoneDueDate = $milestone?->due_date
            ? $milestone->due_date->format('d M, y')
            : 'N/A';

        return new Content(
            view: 'emails.pos_payment_reminder',
            with: [
                'booking' => $this->booking,
                'customer' => $this->customer,
                'greetingName' => $this->customer?->name ?? ($this->booking->customer_name ?? 'Customer'),
                'reminderCount' => $this->reminderCount,
                'totalAmount' => number_format($totalAmount, 2),
                'paidAmount' => number_format($paidAmount, 2),
                'remainingAmount' => number_format($remainingAmount, 2),
                'actionUrl' => $this->resolveActionUrl(),
                'hasMilestoneReminder' => $hasMilestoneReminder,
                'milestoneName' => $milestone?->title ?? 'Milestone Payment',
                'milestoneAmountDue' => $milestoneAmountDue,
                'milestoneDueDate' => $milestoneDueDate,
                'hoardingName' => $this->resolveHoardingName(),
                'bookingDateLabel' => $bookingDate,
                'durationLabel' => $this->resolveDurationLabel(),
            ]
        );
    }

    private function resolveReminderMilestone(): ?QuotationMilestone
    {
        if (!$this->booking->relationLoaded('milestones')) {
            $this->booking->load(['milestones' => function ($query) {
                $query->orderBy('sequence_no');
            }]);
        }

        $milestones = $this->booking->milestones;
        if ($milestones->isEmpty()) {
            return null;
        }

        $currentMilestoneId = (int) ($this->booking->current_milestone_id ?? 0);
        if ($currentMilestoneId > 0) {
            $currentMilestone = $milestones->first(function (QuotationMilestone $item) use ($currentMilestoneId) {
                return (int) $item->id === $currentMilestoneId
                    && !in_array($item->status, [QuotationMilestone::STATUS_PAID, QuotationMilestone::STATUS_CANCELLED], true);
            });

            if ($currentMilestone) {
                return $currentMilestone;
            }
        }

        return $milestones->first(function (QuotationMilestone $item) {
            return in_array($item->status, [QuotationMilestone::STATUS_DUE, QuotationMilestone::STATUS_OVERDUE], true);
        })
            ?? $milestones->first(function (QuotationMilestone $item) {
                return $item->status === QuotationMilestone::STATUS_PENDING;
            })
            ?? $milestones->first(function (QuotationMilestone $item) {
                return !in_array($item->status, [QuotationMilestone::STATUS_PAID, QuotationMilestone::STATUS_CANCELLED], true);
            });
    }

    private function resolveHoardingName(): string
    {
        if (!$this->booking->relationLoaded('bookingHoardings')) {
            $this->booking->load('bookingHoardings.hoarding');
        }

        $names = $this->booking->bookingHoardings
            ->map(function ($bookingHoarding) {
                return trim((string) ($bookingHoarding->hoarding->title ?? ''));
            })
            ->filter()
            ->unique()
            ->values();

        if ($names->isEmpty()) {
            return 'N/A';
        }

        if ($names->count() === 1) {
            return $names->first();
        }

        $preview = $names->take(2)->implode(', ');
        $remainingCount = $names->count() - 2;

        return $remainingCount > 0
            ? $preview . ' +' . $remainingCount . ' more'
            : $preview;
    }

    private function resolveDurationLabel(): string
    {
        $startDate = $this->booking->start_date;
        $endDate = $this->booking->end_date;

        if ($startDate && $endDate) {
            $days = max(1, (int) $startDate->diffInDays($endDate) + 1);
            $dayLabel = $days === 1 ? 'day' : 'days';

            return $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y') . " ({$days} {$dayLabel})";
        }

        $durationDays = (int) ($this->booking->duration_days ?? 0);
        if ($durationDays > 0) {
            return $durationDays . ' day' . ($durationDays === 1 ? '' : 's');
        }

        return 'N/A';
    }

    protected function resolveActionUrl(): string
    {
        return app(\Modules\POS\Services\PosBookingUrlResolver::class)
            ->resolve($this->booking, $this->customer);
    }

    public function attachments(): array
    {
        return [];
    }
}

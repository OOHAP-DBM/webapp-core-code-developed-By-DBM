<?php

namespace App\Notifications;

use App\Models\QuotationMilestone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;
use Modules\POS\Models\POSBooking;

class PosBookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Prevent short worker timeouts from killing slower mail transports.
     */
    public int $timeout = 120;

    /**
     * Keep retries bounded to avoid noisy repeated failures.
     */
    public int $tries = 3;

    /**
     * Retry with progressive delay when SMTP/network is temporarily slow.
     */
    public array $backoff = [10, 60, 180];

    public function __construct(protected POSBooking $booking, protected array $context = []) {}

    public function via($notifiable): array
    {
        $channels = ['database'];

        if (($notifiable->notification_email ?? true) && !empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $totalAmount = (float) $this->booking->total_amount;
        $paidAmount = (float) ($this->booking->paid_amount ?? 0);
        $remainingAmount = max(0, $totalAmount - $paidAmount);
        $isFullyPaid = $remainingAmount < 0.01;
        $paidMilestones = $this->resolvePaidMilestones();
        $nextMilestone = $this->resolveNextMilestone();
        $subject = ($isFullyPaid ? 'POS Booking Confirmed' : 'POS Payment Received')
            . ' - Invoice #' . ($this->booking->invoice_number ?? $this->booking->id);

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.pos_payment_received', [
                'booking' => $this->booking,
                'greetingName' => $notifiable->name ?? ($this->booking->customer_name ?? 'Customer'),
                'isFullyPaid' => $isFullyPaid,
                'totalAmount' => number_format($totalAmount, 2),
                'paidAmount' => number_format($paidAmount, 2),
                'remainingAmount' => number_format($remainingAmount, 2),
                'paidMilestones' => $paidMilestones,
                'nextMilestone' => $nextMilestone,
                'actionUrl' => $this->resolveActionUrl($notifiable),
            ]);
    }

    public function toArray($notifiable): array
    {
        $totalAmount = (float) $this->booking->total_amount;
        $paidAmount = (float) ($this->booking->paid_amount ?? 0);
        $remainingAmount = max(0, $totalAmount - $paidAmount);
        $isFullyPaid = $remainingAmount < 0.01;
        $paidMilestones = $this->resolvePaidMilestones();

        $payload = [
            'type' => 'pos_booking_confirmed',
            'booking_id' => $this->booking->id,
            'invoice_number' => $this->booking->invoice_number,
            'status' => $this->booking->status,
            'payment_status' => $this->booking->payment_status,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'total_amount_formatted' => '₹' . number_format($totalAmount, 2),
            'paid_amount_formatted' => '₹' . number_format($paidAmount, 2),
            'remaining_amount_formatted' => '₹' . number_format($remainingAmount, 2),
            'message' => $isFullyPaid
                ? 'Payment received. POS booking has been confirmed.'
                : 'Partial payment received for POS booking. Awaiting remaining amount.',
            'action_url' => $this->resolveActionUrl($notifiable),
        ];

        if ($paidMilestones->isNotEmpty()) {
            $payload['paid_milestones'] = $paidMilestones->map(function (QuotationMilestone $milestone) {
                return [
                    'id' => (int) $milestone->id,
                    'title' => $milestone->title,
                    'sequence_no' => (int) ($milestone->sequence_no ?? 0),
                    'status' => $milestone->status,
                    'amount' => (float) ($milestone->calculated_amount ?? $milestone->amount ?? 0),
                    'due_date' => $milestone->due_date ? $milestone->due_date->format('Y-m-d') : null,
                ];
            })->values()->all();
        }

        return $payload;
    }

    protected function resolvePaidMilestones(): Collection
    {
        $paidMilestoneIds = collect($this->context['paid_milestone_ids'] ?? [])
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values();

        if ($paidMilestoneIds->isEmpty()) {
            return collect();
        }

        return QuotationMilestone::query()
            ->where('pos_booking_id', $this->booking->id)
            ->whereIn('id', $paidMilestoneIds->all())
            ->orderBy('sequence_no')
            ->get();
    }

    protected function resolveNextMilestone(): ?QuotationMilestone
    {
        $hasMilestoneFlow = ((int) ($this->booking->is_milestone ?? 0) === 1)
            || QuotationMilestone::query()->where('pos_booking_id', $this->booking->id)->exists();

        if (!$hasMilestoneFlow) {
            return null;
        }

        return QuotationMilestone::query()
            ->where('pos_booking_id', $this->booking->id)
            ->whereIn('status', [
                QuotationMilestone::STATUS_DUE,
                QuotationMilestone::STATUS_OVERDUE,
                QuotationMilestone::STATUS_PENDING,
            ])
            ->orderByRaw("CASE WHEN status IN ('due', 'overdue') THEN 0 ELSE 1 END")
            ->orderBy('sequence_no')
            ->first();
    }

    protected function resolveActionUrl($notifiable): string
    {
        if (Route::has('pos.bookings.redirect')) {
            return route('pos.bookings.redirect', ['id' => $this->booking->id]);
        }

        $notifiableId = (int) ($notifiable->id ?? 0);

        if ($this->hasAnyRole($notifiable, ['admin', 'superadmin', 'super_admin'])) {
            if (Route::has('admin.pos.show')) {
                return route('admin.pos.show', ['id' => $this->booking->id]);
            }

            return url('/admin/pos/bookings/' . $this->booking->id);
        }

        if ($this->hasAnyRole($notifiable, ['vendor']) || ($notifiableId > 0 && $notifiableId === (int) $this->booking->vendor_id)) {
            if (Route::has('vendor.pos.bookings.show')) {
                return route('vendor.pos.bookings.show', ['id' => $this->booking->id]);
            }

            return url('/vendor/pos/bookings/' . $this->booking->id);
        }

        if (
            ($notifiableId > 0 && $notifiableId === (int) $this->booking->customer_id)
            || $this->hasAnyRole($notifiable, ['customer'])
        ) {
            if (Route::has('customer.pos.booking.show')) {
                return route('customer.pos.booking.show', ['booking' => $this->booking->id]);
            }

            return url('/customer/pos-booking/' . $this->booking->id);
        }

        return url('/');
    }

    protected function hasAnyRole($notifiable, array $roles): bool
    {
        if (method_exists($notifiable, 'hasAnyRole')) {
            return (bool) $notifiable->hasAnyRole($roles);
        }

        if (!method_exists($notifiable, 'hasRole')) {
            return false;
        }

        foreach ($roles as $role) {
            if ($notifiable->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}

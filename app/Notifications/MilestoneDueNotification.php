<?php

namespace App\Notifications;

use App\Models\QuotationMilestone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

/**
 * MilestoneDueNotification
 * 
 * PROMPT 70 Phase 2: Notify customer when milestone payment is due
 */
class MilestoneDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected QuotationMilestone $milestone;
    protected int $daysUntilDue;

    public function __construct(QuotationMilestone $milestone, int $daysUntilDue = 0)
    {
        $this->milestone = $milestone;
        $this->daysUntilDue = $daysUntilDue;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $milestone = $this->milestone;
        $quotation = $milestone->quotation;
        
        $subject = $this->daysUntilDue > 0
            ? "Milestone Payment Due in {$this->daysUntilDue} Days"
            : "Milestone Payment Due Now";

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line("Your milestone payment is due" . ($this->daysUntilDue > 0 ? " in {$this->daysUntilDue} days" : " now") . ".")
            ->line("**Milestone:** {$milestone->title}")
            ->line("**Amount:** ₹" . number_format($milestone->calculated_amount, 2))
            ->line("**Due Date:** {$milestone->due_date->format('M d, Y')}")
            ->line("**Quotation:** #{$quotation->id}")
            ->action('Pay Now', route('customer.milestones.pay', $milestone->id))
            ->line('Please complete the payment by the due date to avoid delays in your booking.')
            ->line('Thank you for your business!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'milestone_due',
            'milestone_id' => $this->milestone->id,
            'quotation_id' => $this->milestone->quotation_id,
            'title' => $this->milestone->title,
            'amount' => $this->milestone->calculated_amount,
            'due_date' => $this->milestone->due_date->toDateString(),
            'days_until_due' => $this->daysUntilDue,
            'message' => $this->daysUntilDue > 0
                ? "Milestone payment '{$this->milestone->title}' due in {$this->daysUntilDue} days"
                : "Milestone payment '{$this->milestone->title}' is due now",
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }
}

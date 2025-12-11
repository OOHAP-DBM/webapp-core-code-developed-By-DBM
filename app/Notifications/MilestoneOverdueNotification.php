<?php

namespace App\Notifications;

use App\Models\QuotationMilestone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * MilestoneOverdueNotification
 * 
 * PROMPT 70 Phase 2: Notify customer and vendor when milestone is overdue
 */
class MilestoneOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected QuotationMilestone $milestone;
    protected int $daysOverdue;

    public function __construct(QuotationMilestone $milestone, int $daysOverdue = 1)
    {
        $this->milestone = $milestone;
        $this->daysOverdue = $daysOverdue;
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
        
        $isCustomer = $notifiable->role === 'customer';
        
        $message = (new MailMessage)
            ->subject("Milestone Payment Overdue - {$this->daysOverdue} Day(s)")
            ->greeting("Hello {$notifiable->name},")
            ->error();

        if ($isCustomer) {
            $message->line("Your milestone payment is now **{$this->daysOverdue} day(s) overdue**.")
                ->line("**Milestone:** {$milestone->title}")
                ->line("**Amount:** ₹" . number_format($milestone->calculated_amount, 2))
                ->line("**Due Date:** {$milestone->due_date->format('M d, Y')}")
                ->line("**Days Overdue:** {$this->daysOverdue}")
                ->action('Pay Now', route('customer.milestones.pay', $milestone->id))
                ->line('⚠️ Please complete the payment immediately to avoid further delays or cancellation of your booking.')
                ->line('If you have any questions, please contact us immediately.');
        } else {
            // Vendor notification
            $message->line("A milestone payment from your quotation #{$quotation->id} is now **{$this->daysOverdue} day(s) overdue**.")
                ->line("**Milestone:** {$milestone->title}")
                ->line("**Amount:** ₹" . number_format($milestone->calculated_amount, 2))
                ->line("**Customer:** {$quotation->enquiry->customer->name}")
                ->line("**Due Date:** {$milestone->due_date->format('M d, Y')}")
                ->line("**Days Overdue:** {$this->daysOverdue}")
                ->action('View Quotation', route('vendor.quotations.show', $quotation->id))
                ->line('You may want to follow up with the customer regarding this overdue payment.');
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'milestone_overdue',
            'milestone_id' => $this->milestone->id,
            'quotation_id' => $this->milestone->quotation_id,
            'title' => $this->milestone->title,
            'amount' => $this->milestone->calculated_amount,
            'due_date' => $this->milestone->due_date->toDateString(),
            'days_overdue' => $this->daysOverdue,
            'message' => "Milestone payment '{$this->milestone->title}' is {$this->daysOverdue} day(s) overdue",
        ];
    }
}

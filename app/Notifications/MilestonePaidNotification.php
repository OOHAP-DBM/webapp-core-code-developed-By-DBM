<?php

namespace App\Notifications;

use App\Models\QuotationMilestone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * MilestonePaidNotification
 * 
 * PROMPT 70 Phase 2: Notify customer and vendor when milestone payment is completed
 */
class MilestonePaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected QuotationMilestone $milestone;

    public function __construct(QuotationMilestone $milestone)
    {
        $this->milestone = $milestone;
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
            ->subject("Milestone Payment Received - {$milestone->title}")
            ->greeting("Hello {$notifiable->name},")
            ->success();

        if ($isCustomer) {
            $message->line("Your milestone payment has been successfully received!")
                ->line("**Milestone:** {$milestone->title}")
                ->line("**Amount Paid:** ₹" . number_format($milestone->calculated_amount, 2))
                ->line("**Payment Date:** {$milestone->paid_at->format('M d, Y h:i A')}")
                ->line("**Transaction ID:** {$milestone->payment_transaction_id}")
                ->action('View Invoice', route('customer.invoices.show', $milestone->invoice_number))
                ->line('Thank you for your payment! We will proceed with the next steps of your booking.');
            
            // Check for next milestone
            $nextMilestone = $quotation->milestones()
                ->where('sequence_no', '>', $milestone->sequence_no)
                ->where('status', '!=', 'paid')
                ->first();
            
            if ($nextMilestone) {
                $message->line("**Next Milestone:** {$nextMilestone->title} - ₹" . number_format($nextMilestone->calculated_amount, 2))
                    ->line("**Due Date:** {$nextMilestone->due_date->format('M d, Y')}");
            }
        } else {
            // Vendor notification
            $message->line("A milestone payment for your quotation #{$quotation->id} has been received!")
                ->line("**Milestone:** {$milestone->title}")
                ->line("**Amount:** ₹" . number_format($milestone->calculated_amount, 2))
                ->line("**Customer:** {$quotation->enquiry->customer->name}")
                ->line("**Payment Date:** {$milestone->paid_at->format('M d, Y h:i A')}")
                ->action('View Quotation', route('vendor.quotations.show', $quotation->id))
                ->line('The payment will be processed as per settlement schedule.');
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'milestone_paid',
            'milestone_id' => $this->milestone->id,
            'quotation_id' => $this->milestone->quotation_id,
            'title' => $this->milestone->title,
            'amount' => $this->milestone->calculated_amount,
            'paid_at' => $this->milestone->paid_at->toDateTimeString(),
            'transaction_id' => $this->milestone->payment_transaction_id,
            'message' => "Milestone payment '{$this->milestone->title}' received - ₹" . number_format($this->milestone->calculated_amount, 2),
        ];
    }
}

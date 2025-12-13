<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PROMPT 107: Purchase Order Generated Notification
 * 
 * Notifies vendor and customer when PO is auto-generated
 */
class PurchaseOrderGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected PurchaseOrder $po;
    protected bool $isCustomer;

    /**
     * Create a new notification instance.
     */
    public function __construct(PurchaseOrder $po, bool $isCustomer = false)
    {
        $this->po = $po;
        $this->isCustomer = $isCustomer;
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
        $po = $this->po;
        $subject = "Purchase Order Generated - {$po->po_number}";

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!');

        if ($this->isCustomer) {
            // Customer email
            $message->line('A Purchase Order has been automatically generated for your approved quotation.')
                ->line('')
                ->line('**Purchase Order Details:**')
                ->line("PO Number: {$po->po_number}")
                ->line("Quotation ID: #{$po->quotation_id}")
                ->line("Amount: {$po->getFormattedGrandTotal()}")
                ->line("Payment Mode: " . ucfirst($po->payment_mode ?? 'full'))
                ->line('')
                ->line('The PO document has been attached to your conversation thread.')
                ->line('You can review and confirm the purchase order to proceed.')
                ->action('View Conversation', url("/threads/{$po->thread_id}"))
                ->line('Thank you for using our platform!');
        } else {
            // Vendor email
            $message->line('Great news! A Purchase Order has been issued for quotation #{$po->quotation_id}.')
                ->line('')
                ->line('**Purchase Order Details:**')
                ->line("PO Number: {$po->po_number}")
                ->line("Customer: {$po->customer->name}")
                ->line("Quotation ID: #{$po->quotation_id}")
                ->line("Amount: {$po->getFormattedGrandTotal()}")
                ->line("Payment Mode: " . ucfirst($po->payment_mode ?? 'full'))
                ->line('')
                ->line('**Next Steps:**')
                ->line('1. Review the PO document attached to the conversation thread')
                ->line('2. Acknowledge receipt of the PO')
                ->line('3. Proceed with order execution as per timeline')
                ->line('')
                ->action('View Conversation', url("/vendor/threads/{$po->thread_id}"))
                ->line('Please ensure timely delivery and quality standards.')
                ->line('Thank you for being our valued partner!');
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'purchase_order_generated',
            'po_id' => $this->po->id,
            'po_number' => $this->po->po_number,
            'quotation_id' => $this->po->quotation_id,
            'customer_id' => $this->po->customer_id,
            'vendor_id' => $this->po->vendor_id,
            'amount' => (float) $this->po->grand_total,
            'payment_mode' => $this->po->payment_mode,
            'thread_id' => $this->po->thread_id,
            'message' => $this->isCustomer
                ? "Purchase Order {$this->po->po_number} has been generated for your quotation"
                : "New Purchase Order {$this->po->po_number} issued for quotation #{$this->po->quotation_id}",
            'created_at' => $this->po->created_at->toIso8601String(),
        ];
    }
}

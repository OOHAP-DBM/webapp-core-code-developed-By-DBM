<?php

namespace App\Notifications;

use App\Models\VendorQuote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class VendorQuoteSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $quote;

    public function __construct(VendorQuote $quote)
    {
        $this->quote = $quote;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('New Quote Received - ' . $this->quote->quote_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a new quote from ' . $this->quote->vendor->name)
            ->line('**Quote Details:**')
            ->line('Quote Number: ' . $this->quote->quote_number)
            ->line('Hoarding: ' . $this->quote->hoarding->title)
            ->line('Duration: ' . $this->quote->duration_days . ' ' . $this->quote->duration_type)
            ->line('Grand Total: ₹' . number_format($this->quote->grand_total, 2))
            ->line('Valid Until: ' . $this->quote->expires_at->format('d M Y'))
            ->action('View Quote', url('/quotes/' . $this->quote->id))
            ->line('Please review the quote and let us know if you have any questions.');

        // Attach PDF if available
        if ($this->quote->pdf_path && Storage::disk('private')->exists($this->quote->pdf_path)) {
            $mail->attach(
                Storage::disk('private')->path($this->quote->pdf_path),
                [
                    'as' => $this->quote->getPdfFilename(),
                    'mime' => 'application/pdf',
                ]
            );
        }

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'vendor_quote_sent',
            'quote_id' => $this->quote->id,
            'quote_number' => $this->quote->quote_number,
            'vendor_id' => $this->quote->vendor_id,
            'vendor_name' => $this->quote->vendor->name,
            'hoarding_id' => $this->quote->hoarding_id,
            'hoarding_title' => $this->quote->hoarding->title,
            'grand_total' => $this->quote->grand_total,
            'expires_at' => $this->quote->expires_at,
            'message' => 'You have received a new quote from ' . $this->quote->vendor->name,
        ];
    }
}

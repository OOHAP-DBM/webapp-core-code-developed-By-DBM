<?php

namespace Modules\Enquiries\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VendorEnquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $enquiry;
    protected $items;

    public function __construct($enquiry, $items)
    {
        $this->enquiry = $enquiry;
        $this->items = $items; // Array of EnquiryItem objects
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('New Campaign Enquiry: ' . count($this->items) . ' Hoarding(s)')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new enquiry has been raised for your hoarding(s) by **' . $this->enquiry->meta['customer_name'] ?? 'A Client' . '**.')
            ->line('**Customer Contact:** ' . $this->enquiry->contact_number??$this->enquiry->meta['customer_mobile']?? $this->enquiry->meta['customer_email'] ?? 'N/A')
            ->line('**Items Requested:**');

        foreach ($this->items as $item) {
            $type = strtoupper($item->hoarding_type);
            $duration = $item->expected_duration;
            $mail->line("- **{$item->hoarding->title}** ({$type})");
            $mail->line("  Duration: {$duration} | Start: " . $item->preferred_start_date->format('d M, Y'));

            // Handle DOOH specific details in email
            if ($item->hoarding_type === 'dooh' && isset($item->meta['dooh_specs'])) {
                $specs = $item->meta['dooh_specs'];
                $mail->line("  *Digital Specs: {$specs['slots_per_day']} slots per day ({$specs['video_duration']}s loop)*");
            }
        }

        return $mail->action('View Full Enquiry', url('/vendor/enquiries/' . $this->enquiry->id))
            ->line('Please respond to the client at the earliest to confirm availability.');
    }

    public function toArray($notifiable)
    {
        return [
            'enquiry_id' => $this->enquiry->id,
            'customer_name' => $this->enquiry->meta['customer_name'] ?? 'New Client',
            'item_count' => count($this->items),
            'message' => 'New enquiry raised for ' . count($this->items) . ' of your hoardings.',
        ];
    }
}

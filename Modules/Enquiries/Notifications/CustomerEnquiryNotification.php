<?php

namespace Modules\Enquiries\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class CustomerEnquiryNotification extends Notification implements ShouldQueue
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
            ->subject('✅ Your Campaign Enquiry #' . $this->enquiry->id . ' Has Been Received')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Thank you for submitting your campaign enquiry! We have received your request and our team will review the details shortly.')
            ->line('Enquiry Details:')
            ->line('Enquiry ID: #' . $this->enquiry->id)
            ->line('Submitted: ' . $this->enquiry->created_at->format('d M, Y H:i A'))
            ->line('Number of Hoardings: ' . count($this->items))
            ->line('')
            ->line('Selected Hoardings:');

        foreach ($this->items as $item) {
            $type = strtoupper($item->hoarding_type);
            // Get duration from meta if available, default to 1
            $duration = $item->meta['months'] ?? 1;
            $hoardingTitle = $item->hoarding?->title ?? 'Unknown Hoarding';
            
            $mail->line("- {$hoardingTitle} ({$type})")
                ->line("  Duration: {$duration} Month(s) | Start Date: " . \Carbon\Carbon::parse($item->preferred_start_date)->format('d M, Y'));

            // Show package details if selected
            if ($item->package_id && $item->package) {
                $package = $item->package;
                $discount = $item->meta['amount'] ?? 0;  // amount field stores the discount
                $packageName = $package->package_name ?? $package->name ?? 'N/A';
                $mail->line("  Package: {$packageName} (SAVE {$discount}%)");
            } else {
                $mail->line('  Package: Base Price (No discount)');
            }

            // Handle DOOH specific details
            if ($item->hoarding_type === 'dooh' && isset($item->meta['dooh_specs'])) {
                $specs = $item->meta['dooh_specs'];
                $duration_text = $specs['video_duration'] ?? 15;
                $slots = $specs['slots_per_day'] ?? 120;
                $mail->line("  Digital Specs: {$slots} slots per day ({$duration_text}s video)");
            }
        }

        $mail->line('')
            ->line('What Happens Next?')
            ->line('1. Our vendors will review your enquiry within 24-48 hours')
            ->line('2. You will receive detailed quotations for each hoarding')
            ->line('3. Our team will be available to discuss any questions or modifications')
            ->action('View Your Enquiry', url('/customer/enquiries/' . $this->enquiry->id))
            ->line('If you have any immediate questions, please feel free to contact us or reply to this email.')
            ->salutation('Best regards, OOH App Team');

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'enquiry_id' => $this->enquiry->id,
            'item_count' => count($this->items),
            'status' => 'submitted',
            'message' => 'Your enquiry for ' . count($this->items) . ' hoarding(s) has been received.',
        ];
    }
}

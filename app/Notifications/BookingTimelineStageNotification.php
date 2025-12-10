<?php

namespace App\Notifications;

use App\Models\BookingTimelineEvent;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingTimelineStageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $event;
    protected $recipientType; // 'customer', 'vendor', 'admin'

    public function __construct(Booking $booking, BookingTimelineEvent $event, string $recipientType)
    {
        $this->booking = $booking;
        $this->event = $event;
        $this->recipientType = $recipientType;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject($this->getSubject())
            ->greeting($this->getGreeting($notifiable))
            ->line($this->getMainMessage())
            ->line('')
            ->line('**Booking Details:**')
            ->line('Booking ID: #' . $this->booking->id)
            ->line('Hoarding: ' . $this->booking->hoarding->title)
            ->line('Location: ' . $this->booking->hoarding->location)
            ->line('Campaign Duration: ' . $this->booking->start_date->format('M d, Y') . ' - ' . $this->booking->end_date->format('M d, Y'))
            ->line('')
            ->line('**Timeline Event:**')
            ->line('Stage: ' . $this->event->title)
            ->line('Status: ' . ucfirst($this->event->status))
            ->line('Description: ' . $this->event->description);

        if ($this->event->scheduled_at) {
            $mail->line('Scheduled: ' . $this->event->scheduled_at->format('M d, Y H:i'));
        }

        if ($this->event->completed_at) {
            $mail->line('Completed: ' . $this->event->completed_at->format('M d, Y H:i'));
        }

        if ($this->event->user_name) {
            $mail->line('Updated By: ' . $this->event->user_name);
        }

        $mail->action('View Booking Timeline', $this->getActionUrl());

        $mail->line($this->getClosingMessage());

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'booking_timeline_stage',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->id,
            'event_id' => $this->event->id,
            'event_type' => $this->event->event_type,
            'event_title' => $this->event->title,
            'event_status' => $this->event->status,
            'event_category' => $this->event->event_category,
            'hoarding_title' => $this->booking->hoarding->title,
            'recipient_type' => $this->recipientType,
            'message' => $this->getMainMessage(),
        ];
    }

    protected function getSubject(): string
    {
        $statusText = match($this->event->status) {
            'pending' => 'Scheduled',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            default => 'Updated',
        };

        return "Booking #{$this->booking->id} - {$this->event->title} {$statusText}";
    }

    protected function getGreeting($notifiable): string
    {
        return match($this->recipientType) {
            'customer' => "Hello {$notifiable->name}!",
            'vendor' => "Hello {$notifiable->name}!",
            'admin' => "Hello Admin!",
            default => "Hello {$notifiable->name}!",
        };
    }

    protected function getMainMessage(): string
    {
        return match($this->recipientType) {
            'customer' => $this->getCustomerMessage(),
            'vendor' => $this->getVendorMessage(),
            'admin' => $this->getAdminMessage(),
            default => "Timeline stage '{$this->event->title}' has been updated.",
        };
    }

    protected function getCustomerMessage(): string
    {
        return match($this->event->status) {
            'pending' => "Your booking timeline has been updated. The '{$this->event->title}' stage is scheduled.",
            'in_progress' => "The '{$this->event->title}' stage for your booking is now in progress.",
            'completed' => "Great news! The '{$this->event->title}' stage for your booking has been completed.",
            'failed' => "We encountered an issue with the '{$this->event->title}' stage. Our team is working on it.",
            'cancelled' => "The '{$this->event->title}' stage has been cancelled.",
            default => "The '{$this->event->title}' stage has been updated.",
        };
    }

    protected function getVendorMessage(): string
    {
        return match($this->event->status) {
            'pending' => "Action required: The '{$this->event->title}' stage for booking #{$this->booking->id} is scheduled.",
            'in_progress' => "The '{$this->event->title}' stage is now in progress for booking #{$this->booking->id}.",
            'completed' => "The '{$this->event->title}' stage has been completed for booking #{$this->booking->id}.",
            'failed' => "The '{$this->event->title}' stage has failed. Please review and take action.",
            'cancelled' => "The '{$this->event->title}' stage has been cancelled.",
            default => "Timeline updated for booking #{$this->booking->id}.",
        };
    }

    protected function getAdminMessage(): string
    {
        return match($this->event->status) {
            'pending' => "Booking #{$this->booking->id}: '{$this->event->title}' stage scheduled.",
            'in_progress' => "Booking #{$this->booking->id}: '{$this->event->title}' stage in progress.",
            'completed' => "Booking #{$this->booking->id}: '{$this->event->title}' stage completed successfully.",
            'failed' => "ALERT: Booking #{$this->booking->id}: '{$this->event->title}' stage failed. Action required.",
            'cancelled' => "Booking #{$this->booking->id}: '{$this->event->title}' stage cancelled.",
            default => "Booking #{$this->booking->id}: '{$this->event->title}' stage updated.",
        };
    }

    protected function getClosingMessage(): string
    {
        return match($this->recipientType) {
            'customer' => 'Thank you for choosing our platform. If you have any questions, please contact support.',
            'vendor' => 'Please ensure all timeline stages are completed on time. Contact support if you need assistance.',
            'admin' => 'Monitor the booking timeline for any issues or delays.',
            default => 'Thank you!',
        };
    }

    protected function getActionUrl(): string
    {
        return match($this->recipientType) {
            'customer' => url("/customer/bookings/{$this->booking->id}"),
            'vendor' => url("/vendor/bookings/{$this->booking->id}"),
            'admin' => url("/admin/bookings/{$this->booking->id}/timeline"),
            default => url("/bookings/{$this->booking->id}"),
        };
    }
}

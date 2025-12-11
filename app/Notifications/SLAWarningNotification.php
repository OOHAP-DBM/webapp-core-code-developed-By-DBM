<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

/**
 * SLAWarningNotification
 * PROMPT 68: Vendor SLA Tracking System
 * 
 * Sent to vendors when they're approaching an SLA deadline
 */
class SLAWarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->data['type'];
        $deadline = Carbon::parse($this->data['deadline']);
        $timeRemaining = $this->data['time_remaining'];

        $subject = $type === 'acceptance' 
            ? 'SLA Warning: Enquiry Acceptance Deadline Approaching'
            : 'SLA Warning: Quote Submission Deadline Approaching';

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('This is a reminder that you have an upcoming SLA deadline.')
            ->line('**Type:** ' . ucwords(str_replace('_', ' ', $type)))
            ->line('**Deadline:** ' . $deadline->format('d M Y, h:i A'))
            ->line('**Time Remaining:** ' . $timeRemaining)
            ->warning('Please take action before the deadline to avoid SLA violation and impact on your reliability score.')
            ->action('View Details', url('/vendor/quote-requests/' . $this->data['quote_request_id']))
            ->line('Thank you for your prompt attention to this matter.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'sla_warning',
            'warning_type' => $this->data['type'],
            'quote_request_id' => $this->data['quote_request_id'],
            'deadline' => $this->data['deadline'],
            'time_remaining' => $this->data['time_remaining'],
            'message' => 'SLA deadline approaching for ' . ucwords(str_replace('_', ' ', $this->data['type'])),
        ];
    }
}

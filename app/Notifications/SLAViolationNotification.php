<?php

namespace App\Notifications;

use App\Models\VendorSLAViolation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * SLAViolationNotification
 * PROMPT 68: Vendor SLA Tracking System
 * 
 * Sent to vendors when they violate an SLA
 */
class SLAViolationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected VendorSLAViolation $violation;

    /**
     * Create a new notification instance.
     */
    public function __construct(VendorSLAViolation $violation)
    {
        $this->violation = $violation;
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
        $violation = $this->violation;
        
        $subject = 'SLA Violation: ' . ucwords(str_replace('_', ' ', $violation->violation_type));

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->error('An SLA violation has been recorded on your account.')
            ->line('**Violation Type:** ' . ucwords(str_replace('_', ' ', $violation->violation_type)))
            ->line('**Severity:** ' . ucfirst($violation->severity))
            ->line('**Deadline:** ' . $violation->deadline->format('d M Y, h:i A'))
            ->line('**Actual Time:** ' . $violation->actual_time->format('d M Y, h:i A'))
            ->line('**Delay:** ' . $violation->getDelayFormatted())
            ->line('**Penalty Points:** ' . $violation->penalty_points)
            ->line('**Previous Score:** ' . $violation->reliability_score_before)
            ->line('**New Score:** ' . $violation->reliability_score_after);

        if ($violation->severity === VendorSLAViolation::SEVERITY_CRITICAL) {
            $message->warning('This is a CRITICAL violation that has been escalated to administration.');
        }

        $message->line('You can dispute this violation if you believe it was recorded in error.')
            ->action('View Violation Details', url('/vendor/sla-violations/' . $violation->id))
            ->line('To maintain a high reliability score, please ensure all future deadlines are met.');

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
            'type' => 'sla_violation',
            'violation_id' => $this->violation->id,
            'violation_type' => $this->violation->violation_type,
            'severity' => $this->violation->severity,
            'penalty_points' => $this->violation->penalty_points,
            'reliability_score_after' => $this->violation->reliability_score_after,
            'message' => 'SLA violation recorded: ' . ucwords(str_replace('_', ' ', $this->violation->violation_type)),
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\VendorSLAViolation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * SLACriticalViolationNotification
 * PROMPT 68: Vendor SLA Tracking System
 * 
 * Sent to admins when a vendor has a critical SLA violation
 */
class SLACriticalViolationNotification extends Notification implements ShouldQueue
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
        $vendor = $violation->vendor;
        
        $subject = 'CRITICAL: Vendor SLA Violation Requires Attention';

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello Admin,')
            ->error('A CRITICAL SLA violation has been recorded and requires your attention.')
            ->line('**Vendor:** ' . $vendor->name . ' (ID: ' . $vendor->id . ')')
            ->line('**Violation Type:** ' . ucwords(str_replace('_', ' ', $violation->violation_type)))
            ->line('**Severity:** CRITICAL')
            ->line('**Deadline:** ' . $violation->deadline->format('d M Y, h:i A'))
            ->line('**Actual Time:** ' . $violation->actual_time->format('d M Y, h:i A'))
            ->line('**Delay:** ' . $violation->getDelayFormatted())
            ->line('**Penalty Points:** ' . $violation->penalty_points)
            ->line('**Vendor Reliability Score:** ' . $violation->reliability_score_after)
            ->line('**Violations This Month:** ' . $violation->violation_count_this_month)
            ->line('**Total Violations:** ' . $violation->violation_count_total);

        if ($vendor->hasCriticalReliability()) {
            $message->warning('WARNING: Vendor has reached CRITICAL reliability status (score < 40).');
        }

        $message->action('Review Violation', url('/admin/sla-violations/' . $violation->id))
            ->line('Please review this violation and take appropriate action.');

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
            'type' => 'sla_critical_violation',
            'violation_id' => $this->violation->id,
            'vendor_id' => $this->violation->vendor_id,
            'vendor_name' => $this->violation->vendor->name,
            'violation_type' => $this->violation->violation_type,
            'penalty_points' => $this->violation->penalty_points,
            'reliability_score_after' => $this->violation->reliability_score_after,
            'violations_this_month' => $this->violation->violation_count_this_month,
            'message' => 'CRITICAL SLA violation by ' . $this->violation->vendor->name,
        ];
    }
}

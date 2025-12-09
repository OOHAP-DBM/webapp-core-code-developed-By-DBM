<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send notification using template
     */
    public function sendFromTemplate(
        string $eventType,
        string $channel,
        array $placeholdersData,
        $recipient,
        $relatedEntity = null
    ): ?NotificationLog {
        try {
            // Get active template
            $template = NotificationTemplate::active()
                ->forEvent($eventType)
                ->forChannel($channel)
                ->orderBy('priority', 'desc')
                ->first();

            if (!$template) {
                Log::warning("No active template found for event: {$eventType}, channel: {$channel}");
                return null;
            }

            // Render template with placeholders
            $rendered = $template->render($this->preparePlaceholders($placeholdersData));

            // Extract recipient info
            $recipientInfo = $this->extractRecipientInfo($recipient, $channel);

            // Create log entry
            $log = NotificationLog::create([
                'notification_template_id' => $template->id,
                'user_id' => $recipient instanceof User ? $recipient->id : null,
                'recipient_type' => $this->getRecipientType($recipient),
                'recipient_identifier' => $recipientInfo['identifier'],
                'event_type' => $eventType,
                'channel' => $channel,
                'subject' => $rendered['subject'],
                'body' => $rendered['body'],
                'html_body' => $rendered['html_body'],
                'status' => NotificationLog::STATUS_PENDING,
                'related_type' => $relatedEntity ? get_class($relatedEntity) : null,
                'related_id' => $relatedEntity?->id,
                'placeholders_data' => $placeholdersData,
            ]);

            // Send via appropriate channel
            $this->sendViaChannel($log, $recipientInfo);

            return $log;
        } catch (Exception $e) {
            Log::error("Failed to send notification: {$e->getMessage()}", [
                'event_type' => $eventType,
                'channel' => $channel,
                'exception' => $e,
            ]);

            return null;
        }
    }

    /**
     * Send notification to multiple recipients
     */
    public function sendBulk(
        string $eventType,
        string $channel,
        array $placeholdersData,
        array $recipients,
        $relatedEntity = null
    ): array {
        $results = [];

        foreach ($recipients as $recipient) {
            $results[] = $this->sendFromTemplate(
                $eventType,
                $channel,
                $placeholdersData,
                $recipient,
                $relatedEntity
            );
        }

        return array_filter($results); // Remove nulls
    }

    /**
     * Prepare placeholders with common data
     */
    protected function preparePlaceholders(array $data): array
    {
        return array_merge([
            '{{app_name}}' => config('app.name'),
            '{{app_url}}' => config('app.url'),
            '{{current_date}}' => now()->format('F d, Y'),
            '{{current_time}}' => now()->format('h:i A'),
        ], $data);
    }

    /**
     * Extract recipient information based on channel
     */
    protected function extractRecipientInfo($recipient, string $channel): array
    {
        if ($recipient instanceof User) {
            return match ($channel) {
                'email' => [
                    'identifier' => $recipient->email,
                    'name' => $recipient->name,
                ],
                'sms', 'whatsapp' => [
                    'identifier' => $recipient->phone,
                    'name' => $recipient->name,
                ],
                'web' => [
                    'identifier' => (string) $recipient->id,
                    'name' => $recipient->name,
                ],
                default => [
                    'identifier' => $recipient->email,
                    'name' => $recipient->name,
                ],
            };
        }

        // If recipient is array
        if (is_array($recipient)) {
            return [
                'identifier' => $recipient['email'] ?? $recipient['phone'] ?? $recipient['id'] ?? '',
                'name' => $recipient['name'] ?? 'User',
            ];
        }

        // If recipient is string (email/phone)
        return [
            'identifier' => $recipient,
            'name' => 'User',
        ];
    }

    /**
     * Get recipient type
     */
    protected function getRecipientType($recipient): string
    {
        if ($recipient instanceof User) {
            return $recipient->role ?? 'user';
        }

        return 'user';
    }

    /**
     * Send via specific channel
     */
    protected function sendViaChannel(NotificationLog $log, array $recipientInfo): void
    {
        try {
            match ($log->channel) {
                'email' => $this->sendEmail($log, $recipientInfo),
                'sms' => $this->sendSms($log, $recipientInfo),
                'whatsapp' => $this->sendWhatsApp($log, $recipientInfo),
                'web' => $this->sendWebNotification($log, $recipientInfo),
                default => throw new Exception("Unsupported channel: {$log->channel}"),
            };
        } catch (Exception $e) {
            $log->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Send email notification
     */
    protected function sendEmail(NotificationLog $log, array $recipientInfo): void
    {
        try {
            Mail::send([], [], function ($message) use ($log, $recipientInfo) {
                $message->to($recipientInfo['identifier'], $recipientInfo['name'])
                    ->subject($log->subject);

                if ($log->html_body) {
                    $message->html($log->html_body);
                } else {
                    $message->text($log->body);
                }
            });

            $log->markAsSent(
                providerId: null,
                providerResponse: 'Email queued successfully'
            );

            Log::info("Email notification sent", [
                'log_id' => $log->id,
                'recipient' => $recipientInfo['identifier'],
            ]);
        } catch (Exception $e) {
            $log->markAsFailed("Email failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Send SMS notification
     */
    protected function sendSms(NotificationLog $log, array $recipientInfo): void
    {
        try {
            // TODO: Integrate with SMS provider (Razorpay SMS, Twilio, etc.)
            // For now, just log
            Log::info("SMS notification would be sent", [
                'log_id' => $log->id,
                'phone' => $recipientInfo['identifier'],
                'body' => $log->body,
            ]);

            $log->update([
                'status' => NotificationLog::STATUS_SENT,
                'sent_at' => now(),
                'provider' => 'mock_sms',
                'provider_response' => 'SMS provider not configured',
            ]);
        } catch (Exception $e) {
            $log->markAsFailed("SMS failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Send WhatsApp notification
     */
    protected function sendWhatsApp(NotificationLog $log, array $recipientInfo): void
    {
        try {
            // TODO: Integrate with WhatsApp Business API
            Log::info("WhatsApp notification would be sent", [
                'log_id' => $log->id,
                'phone' => $recipientInfo['identifier'],
                'body' => $log->body,
            ]);

            $log->update([
                'status' => NotificationLog::STATUS_SENT,
                'sent_at' => now(),
                'provider' => 'mock_whatsapp',
                'provider_response' => 'WhatsApp provider not configured',
            ]);
        } catch (Exception $e) {
            $log->markAsFailed("WhatsApp failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Send web notification
     */
    protected function sendWebNotification(NotificationLog $log, array $recipientInfo): void
    {
        try {
            // Store as web notification in database
            // Can be displayed in user's notification center
            $log->markAsSent(
                providerId: "web_" . $log->id,
                providerResponse: 'Web notification created'
            );

            Log::info("Web notification created", [
                'log_id' => $log->id,
                'user_id' => $log->user_id,
            ]);
        } catch (Exception $e) {
            $log->markAsFailed("Web notification failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Retry failed notification
     */
    public function retry(NotificationLog $log, int $maxRetries = 3): bool
    {
        if (!$log->canRetry($maxRetries)) {
            return false;
        }

        try {
            $log->incrementRetry();
            $log->update(['status' => NotificationLog::STATUS_PENDING]);

            $recipientInfo = [
                'identifier' => $log->recipient_identifier,
                'name' => $log->user?->name ?? 'User',
            ];

            $this->sendViaChannel($log, $recipientInfo);

            return true;
        } catch (Exception $e) {
            Log::error("Retry failed for notification log {$log->id}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get notification statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $query = NotificationLog::query();

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }

        if (isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        return [
            'total_sent' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', NotificationLog::STATUS_PENDING)->count(),
            'sent' => (clone $query)->where('status', NotificationLog::STATUS_SENT)->count(),
            'delivered' => (clone $query)->where('status', NotificationLog::STATUS_DELIVERED)->count(),
            'failed' => (clone $query)->where('status', NotificationLog::STATUS_FAILED)->count(),
            'read' => (clone $query)->where('status', NotificationLog::STATUS_READ)->count(),
            'by_channel' => (clone $query)->selectRaw('channel, count(*) as count')
                ->groupBy('channel')
                ->pluck('count', 'channel')
                ->toArray(),
            'by_event' => (clone $query)->selectRaw('event_type, count(*) as count')
                ->groupBy('event_type')
                ->pluck('count', 'event_type')
                ->toArray(),
        ];
    }

    /**
     * Get user's unread notifications
     */
    public function getUserNotifications(User $user, bool $unreadOnly = false): \Illuminate\Database\Eloquent\Collection
    {
        $query = NotificationLog::where('user_id', $user->id)
            ->where('channel', 'web')
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->whereIn('status', [NotificationLog::STATUS_SENT, NotificationLog::STATUS_DELIVERED]);
        }

        return $query->get();
    }

    /**
     * Mark user notifications as read
     */
    public function markAsRead(User $user, array $logIds = []): int
    {
        $query = NotificationLog::where('user_id', $user->id)
            ->whereIn('status', [NotificationLog::STATUS_SENT, NotificationLog::STATUS_DELIVERED]);

        if (!empty($logIds)) {
            $query->whereIn('id', $logIds);
        }

        return $query->update([
            'status' => NotificationLog::STATUS_READ,
            'read_at' => now(),
        ]);
    }
}

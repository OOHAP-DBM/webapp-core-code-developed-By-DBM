<?php

namespace Modules\Import\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Import\Entities\InventoryImportBatch;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Hoarding;


class BulkImportApprovedForVendorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected InventoryImportBatch $batch;
    protected int $createdCount;
    protected int $failedCount;
    protected array $createdHoardingIds;

    public function __construct(
        InventoryImportBatch $batch,
        int $createdCount,
        int $failedCount,
        array $createdHoardingIds = []  // ADD THIS
    ) {
        $this->batch = $batch;
        $this->createdCount = $createdCount;
        $this->failedCount = $failedCount;
        $this->createdHoardingIds = $createdHoardingIds; // ADD THIS
    }

    /**
     * Channels for the notification.
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Email content for the vendor.
     */
      public function toMail($notifiable)
    {
        $subject = 'Your Bulk Import Batch has been Approved!';
        $batchUrl = route('vendor.import.enhanced.batch.show', $this->batch->id);

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line('Your Bulk Import batch has been successfully approved.')
            ->line("**Hoardings Created:** {$this->createdCount}")
            ->line("**Failed Records:** {$this->failedCount}");
            // Add individual hoarding links if we have them
        if (!empty($this->createdHoardingIds)) {
            $mail->line('---');
            $mail->line('**Your Created Hoardings:**');

            // Fetch hoarding details for richer links (title/code)
            $hoardings = Hoarding::whereIn('id', $this->createdHoardingIds)
                ->select('id', 'title')
                ->get();

            foreach ($hoardings as $hoarding) {
                $hoardingUrl = route('vendor.myHoardings.show', $hoarding->id);
                $label = $hoarding->title ? "#{$hoarding->id} — {$hoarding->title}" : "Hoarding #{$hoarding->id}";
                $mail->line("[{$label}]({$hoardingUrl})");
            }

            $mail->line('---');
        }

        $mail->action('View Your Import Batch', $batchUrl)
             ->line('Thank you for using our platform!');

        return $mail;
    }

    /**
     * Database content for vendor notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'type' => 'bulk_import_approved_vendor',
            'title' => 'Bulk Import Approved',
            'message' => "Your bulk import batch has been approved. {$this->createdCount} hoardings created, {$this->failedCount} failed.",
            'batch_id' => $this->batch->id,
            'created_count' => $this->createdCount,
            'failed_count' => $this->failedCount,
            'action_url' => route('vendor.import.enhanced.batch.show', $this->batch->id),
        ];
    }

    /**
     * Send push notification after database and mail notifications.
     */
    public function afterCommit($notifiable)
    {
        // Check if vendor has FCM token and push notifications enabled
        if (!$notifiable->fcm_token || !$notifiable->notification_push) {
            return;
        }

        try {
            $message = "Your import has been approved! {$this->createdCount} " . \Illuminate\Support\Str::plural('hoarding', $this->createdCount) . " created";
            if ($this->failedCount > 0) {
                $message .= " and {$this->failedCount} failed";
            }
            $message .= ".";

            send(
                $notifiable->fcm_token,
                'Import Approved ✅',
                $message,
                [
                    'type'            => 'import_approved',
                    'batch_id'        => $this->batch->id,
                    'created_count'   => $this->createdCount,
                    'failed_count'    => $this->failedCount,
                    'total_processed' => $this->createdCount + $this->failedCount,
                    'action'          => 'view_import'
                ]
            );
        } catch (\Throwable $e) {
            \Log::error("Failed to send push notification to vendor on import approval", [
                'vendor_id' => $notifiable->id,
                'batch_id'  => $this->batch->id,
                'error'     => $e->getMessage()
            ]);
        }
    }
}

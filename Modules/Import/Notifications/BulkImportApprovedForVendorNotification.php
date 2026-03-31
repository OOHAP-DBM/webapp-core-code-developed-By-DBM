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
    protected bool $autoApproval;

    public function __construct(
        InventoryImportBatch $batch,
        int $createdCount,
        int $failedCount,
        array $createdHoardingIds = [],  // ADD THIS
        bool $autoApproval = false 
    ) {
        $this->batch = $batch;
        $this->createdCount = $createdCount;
        $this->failedCount = $failedCount;
        $this->createdHoardingIds = $createdHoardingIds; // ADD THIS
         $this->autoApproval = $autoApproval; // ✅ ADD
    }

    /**
     * Channels for the notification.
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Email content for the vendor using a custom Blade view.
     */
    public function toMail($notifiable)
    {
        $statusType = $this->autoApproval ? 'live' : 'approved';

        $subject = $this->autoApproval
            ? 'Your Hoardings are Now Live!'
            : 'Your Bulk Import Hoardings Has Been Created Successfully';
        $batchUrl = route('vendor.import.enhanced.batch.show', $this->batch->id);

        // Fetch hoarding details for richer links (id, title, hoarding_type)
      $hoardings = Hoarding::whereIn('id', $this->createdHoardingIds ?? [])
        ->select('id', 'title', 'hoarding_type', 'name')
        ->latest()
        ->take(10)
        ->get();

        return (new MailMessage)
            ->subject($subject)
            ->view(
                'emails.vendor_bulk_import_status',
                [
                    'greeting'     => 'Hello ' . ($notifiable->name ?? ''),
                    'createdCount' => $this->createdCount,
                    'failedCount'  => $this->failedCount,
                    'batchId'      => $this->batch->id,
                    'batchUrl'     => $batchUrl,
                    'hoardings'    => $hoardings,
                    'isLive'       => $this->autoApproval, 
                ]
            );
    }

    /**
     * Database content for vendor notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'type' => 'bulk_import_approved_vendor',
            'title' => 'Bulk Import Created Successfully',
            'message' => "Your bulk import inventory has been approved. {$this->createdCount} hoardings created, {$this->failedCount} failed.",
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

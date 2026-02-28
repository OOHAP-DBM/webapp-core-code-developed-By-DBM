<?php

namespace Modules\Import\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Import\Entities\InventoryImportBatch;
use Illuminate\Notifications\Messages\MailMessage;

class BulkImportApprovedForVendorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected InventoryImportBatch $batch;
    protected int $createdCount;
    protected int $failedCount;

    public function __construct(InventoryImportBatch $batch, int $createdCount, int $failedCount)
    {
        $this->batch = $batch;
        $this->createdCount = $createdCount;
        $this->failedCount = $failedCount;
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
        $actionUrl = route('vendor.import.enhanced.batch.show', $this->batch->id);

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line('Your Bulk Import batch has been successfully approved.')
            ->line("Hoardings Created: {$this->createdCount}")
            ->line("Failed Records: {$this->failedCount}")
            ->action('View Your Bulk Import Batch', $actionUrl)
            ->line('Thank you for using our platform!');
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
}
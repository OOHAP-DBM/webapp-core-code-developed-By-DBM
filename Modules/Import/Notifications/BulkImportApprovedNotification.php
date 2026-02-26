<?php

namespace Modules\Import\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Import\Entities\InventoryImportBatch;

class BulkImportApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $batch;
    protected $createdCount;
    protected $failedCount;

    public function __construct(
        InventoryImportBatch $batch,
        int $createdCount,
        int $failedCount
    ) {
        $this->batch = $batch;
        $this->createdCount = $createdCount;
        $this->failedCount = $failedCount;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $subject = 'Bulk Import Approved Successfully';

        $actionUrl = route('admin.import.batches.show', $this->batch->id);

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject($subject)
            ->greeting('Hello!')
            ->line('A Bulk Import batch has been approved.')
            ->line("Successfully Created: {$this->createdCount} hoardings")
            ->line("Failed Records: {$this->failedCount}")
            ->action('View Bulk Import Batch', $actionUrl)
            ->line('Thank you for using our platform!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type'          => 'bulk_import_approved',
            'title'         => 'Bulk Import Approved',
            'message'       => "{$this->createdCount} hoardings created successfully via Bulk Import.",
            'batch_id'      => $this->batch->id,
            'created_count' => $this->createdCount,
            'failed_count'  => $this->failedCount,
            'action_url'    => route('admin.import.batches.show', $this->batch->id),
        ];
    }
}
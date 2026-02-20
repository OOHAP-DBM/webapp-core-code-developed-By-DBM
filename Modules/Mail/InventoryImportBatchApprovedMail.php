<?php

namespace Modules\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Import\Entities\InventoryImportBatch;

class InventoryImportBatchApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $batch;

    /**
     * Create a new message instance.
     *
     * @param InventoryImportBatch $batch
     */
    public function __construct(InventoryImportBatch $batch)
    {
        $this->batch = $batch;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your Inventory Import Batch Has Been Approved')
            ->view('mail.inventory_import_batch_approved')
            ->with([
                'batch' => $this->batch,
            ]);
    }
}

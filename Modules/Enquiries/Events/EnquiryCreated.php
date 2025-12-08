<?php

namespace Modules\Enquiries\Events;

use Modules\Enquiries\Models\Enquiry;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnquiryCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Enquiry $enquiry;

    /**
     * Create a new event instance.
     */
    public function __construct(Enquiry $enquiry)
    {
        $this->enquiry = $enquiry;
    }
}


<?php

namespace Modules\Offers\Events;

use Modules\Offers\Models\Offer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Offer $offer;

    /**
     * Create a new event instance.
     */
    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
    }
}


<?php

namespace Modules\Payment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentAuthorized
{
    use Dispatchable, SerializesModels;

    public string $paymentId;
    public string $orderId;
    public array $webhookPayload;

    /**
     * Create a new event instance.
     */
    public function __construct(string $paymentId, string $orderId, array $webhookPayload)
    {
        $this->paymentId = $paymentId;
        $this->orderId = $orderId;
        $this->webhookPayload = $webhookPayload;
    }
}

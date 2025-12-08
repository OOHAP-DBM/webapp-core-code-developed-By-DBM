<?php

namespace Modules\Payment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCaptured
{
    use Dispatchable, SerializesModels;

    public string $paymentId;
    public string $orderId;
    public float $amount;
    public array $webhookPayload;

    /**
     * Create a new event instance.
     */
    public function __construct(string $paymentId, string $orderId, float $amount, array $webhookPayload)
    {
        $this->paymentId = $paymentId;
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->webhookPayload = $webhookPayload;
    }
}

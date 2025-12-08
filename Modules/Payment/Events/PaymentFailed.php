<?php

namespace Modules\Payment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed
{
    use Dispatchable, SerializesModels;

    public string $paymentId;
    public string $orderId;
    public string $errorCode;
    public string $errorDescription;
    public array $webhookPayload;

    /**
     * Create a new event instance.
     */
    public function __construct(
        string $paymentId,
        string $orderId,
        string $errorCode,
        string $errorDescription,
        array $webhookPayload
    ) {
        $this->paymentId = $paymentId;
        $this->orderId = $orderId;
        $this->errorCode = $errorCode;
        $this->errorDescription = $errorDescription;
        $this->webhookPayload = $webhookPayload;
    }
}


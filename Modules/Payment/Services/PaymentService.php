<?php

declare(strict_types=1);

namespace Modules\Payment\Services;

use App\Services\PaymentService as CorePaymentService;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;

class PaymentService extends CorePaymentService
{
    /**
     * Check if a webhook event has already been processed (idempotency).
     */
    public function isWebhookProcessed(string $eventId): bool
    {
        if (!$eventId) return false;
        return PaymentTransaction::whereJsonContains('metadata->processed_webhook_events', $eventId)->exists();
    }

    /**
     * Mark a payment as failed (atomic, idempotent).
     */
    public function markPaymentFailed(string $gatewayPaymentId, array $payload = []): void
    {
        if (!$gatewayPaymentId) return;
        DB::transaction(function () use ($gatewayPaymentId, $payload) {
            $transaction = PaymentTransaction::where('gateway_payment_id', $gatewayPaymentId)->lockForUpdate()->first();
            if (!$transaction) return;
            if (in_array($transaction->status, [PaymentTransaction::STATUS_FAILED, PaymentTransaction::STATUS_SUCCESS], true)) return;
            $meta = $transaction->metadata ?? [];
            $meta['last_failed_payload'] = $payload;
            $transaction->metadata = $meta;
            $transaction->status = PaymentTransaction::STATUS_FAILED;
            $transaction->failure_reason = $payload['error_reason'] ?? $payload['reason'] ?? null;
            $transaction->failure_code = $payload['error_code'] ?? null;
            $transaction->save();
        });
    }
}

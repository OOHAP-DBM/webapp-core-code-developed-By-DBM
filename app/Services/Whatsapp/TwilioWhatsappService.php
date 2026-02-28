<?php

namespace App\Services\Whatsapp;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioWhatsappService
{
    protected Client $client;
    protected string $from;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        $this->from = 'whatsapp:' . config('services.twilio.whatsapp');
    }

    public function send(string $to, string $message): bool
    {
        try {
            $this->client->messages->create(
                'whatsapp:' . $to,
                [
                    'from' => $this->from,
                    'body' => $message,
                ]
            );

            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp send failed', [
                'error' => $e->getMessage(),
                'to' => $to,
            ]);

            return false;
        }
    }
}

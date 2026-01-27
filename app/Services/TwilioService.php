<?php
namespace App\Services;

use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected TwilioClient $client;

    public function __construct()
    {
        $this->client = new TwilioClient(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    /**
     * Send SMS
     */
    public function sendSMS(string $to, string $message): bool
    {
        try {
            $this->client->messages->create($to, [
                'from' => config('services.twilio.from'),
                'body' => $message,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Twilio SMS failed: ' . $e->getMessage());
            return false;
        }
    }
}

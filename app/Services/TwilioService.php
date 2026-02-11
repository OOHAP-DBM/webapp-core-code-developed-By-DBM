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

            // Format number to E.164
            $to = $this->formatPhoneNumber($to);

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

    protected function formatPhoneNumber(string $phone): string
    {
        // Remove everything except digits
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If 10 digit Indian number → add +91
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        // Add + if missing
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }
}

<?php

namespace App\Services\Whatsapp;

use Twilio\Rest\Client;
use Twilio\Http\CurlClient;
use Illuminate\Support\Facades\Log;

class TwilioWhatsappService
{
    protected Client $client;
    protected string $from;

    public function __construct()
    {
        // Create Twilio client with custom HTTP client for SSL handling
        $httpClient = new CurlClient();

        // In local development, you can disable SSL verification
        // NEVER do this in production!
        if (config('app.env') === 'local' && config('services.twilio.disable_ssl_verify', false)) {
            $httpClient->setOption(CURLOPT_SSL_VERIFYHOST, 0);
            $httpClient->setOption(CURLOPT_SSL_VERIFYPEER, false);

            Log::warning('Twilio SSL verification disabled - LOCAL DEVELOPMENT ONLY');
        }

        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token'),
            null,
            null,
            $httpClient
        );

        $this->from = 'whatsapp:' . config('services.twilio.whatsapp');
    }

    public function send(string $to, string $message): bool
    {
        try {
            Log::debug('Twilio WhatsApp sending message', [
                'to' => $to,
                'from' => $this->from,
                'message_length' => strlen($message),
            ]);

            $result = $this->client->messages->create(
                'whatsapp:' . $to,
                [
                    'from' => $this->from,
                    'body' => $message,
                ]
            );

            Log::info('Twilio WhatsApp message sent successfully', [
                'to' => $to,
                'sid' => $result->sid,
                'status' => $result->status,
            ]);

            return true;
        } catch (\Twilio\Exceptions\TwilioException $e) {
            Log::error('Twilio WhatsApp API error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'to' => $to,
                'more_info' => method_exists($e, 'getMoreInfo') ? $e->getMoreInfo() : null,
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('WhatsApp send failed', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'to' => $to,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}

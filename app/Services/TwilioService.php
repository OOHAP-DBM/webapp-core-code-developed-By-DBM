<?php

namespace App\Services;

use App\Models\Setting;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TwilioService
{
    private const GATEWAY_TWILIO = 'twilio';
    private const GATEWAY_MSG91 = 'msg91';
    private const GATEWAY_CLICKATELL = 'clickatell';

    /**
     * Send SMS via the active gateway.
     */
    public function sendSMS(string $to, string $message): bool
    {
        if (!$this->isSmsEnabled()) {
            Log::warning('SMS skipped because SMS service is disabled in settings.');
            return false;
        }

        try {
            return match ($this->getActiveGateway()) {
                self::GATEWAY_MSG91 => $this->sendViaMsg91($to, $message),
                self::GATEWAY_CLICKATELL => $this->sendViaClickatell($to, $message),
                default => $this->sendViaTwilio($to, $message),
            };
        } catch (Throwable $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Return currently active SMS gateway.
     */
    public function getActiveGateway(): string
    {
        $gateway = strtolower((string) $this->setting('sms_active_gateway', self::GATEWAY_TWILIO));

        return in_array($gateway, [self::GATEWAY_TWILIO, self::GATEWAY_MSG91, self::GATEWAY_CLICKATELL], true)
            ? $gateway
            : self::GATEWAY_TWILIO;
    }

    /**
     * Send SMS via Twilio.
     */
    protected function sendViaTwilio(string $to, string $message): bool
    {
        $sid = $this->settingOrConfig('sms_twilio_sid', 'services.twilio.sid');
        $token = $this->settingOrConfig('sms_twilio_auth_token', 'services.twilio.token');
        $alphaSenderId = (string) $this->setting('sms_twilio_alphanumeric_sender_id', '');
        $from = $alphaSenderId !== ''
            ? $alphaSenderId
            : $this->settingOrConfig('sms_twilio_from', 'services.twilio.from');

        if (!$sid || !$token || !$from) {
            Log::error('Twilio SMS failed: Missing SID, auth token, or sender number.');
            return false;
        }

        try {
            $client = new TwilioClient($sid, $token);
            $client->messages->create($this->formatPhoneNumber($to), [
                'from' => $from,
                'body' => $message,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('Twilio SMS failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS via MSG91.
     */
    protected function sendViaMsg91(string $to, string $message): bool
    {
        $authKey = (string) $this->setting('sms_msg91_auth_key', '');
        $senderId = (string) $this->setting('sms_msg91_sender_id', '');
        $route = (string) $this->setting('sms_msg91_route', '4');
        $country = (string) $this->setting('sms_msg91_country', '91');
        $baseUrl = (string) $this->setting('sms_msg91_base_url', 'https://api.msg91.com/api/v2/sendsms');

        if (!$authKey || !$senderId) {
            Log::error('MSG91 SMS failed: Missing auth key or sender ID.');
            return false;
        }

        $mobile = $this->formatPhoneDigits($to, $country);

        try {
            $response = Http::withHeaders([
                'authkey' => $authKey,
                'content-type' => 'application/json',
            ])->post($baseUrl, [
                'sender' => $senderId,
                'route' => $route,
                'country' => $country,
                'sms' => [[
                    'message' => $message,
                    'to' => [$mobile],
                ]],
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('MSG91 SMS failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('MSG91 SMS failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS via Clickatell.
     */
    protected function sendViaClickatell(string $to, string $message): bool
    {
        $apiKey = (string) $this->setting('sms_clickatell_api_key', '');
        $from = (string) $this->setting('sms_clickatell_from', '');
        $baseUrl = (string) $this->setting('sms_clickatell_base_url', 'https://platform.clickatell.com/messages/http/send');

        if (!$apiKey) {
            Log::error('Clickatell SMS failed: Missing API key.');
            return false;
        }

        try {
            if (str_contains($baseUrl, '/messages/http/send')) {
                $query = [
                    'apiKey' => $apiKey,
                    'to' => ltrim($this->formatPhoneNumber($to), '+'),
                    'content' => $message,
                ];

                if ($from !== '') {
                    $query['from'] = $from;
                }

                $response = Http::get($baseUrl, $query);
                $body = $response->body();

                if ($response->successful() && !str_contains(strtoupper($body), 'ERR:')) {
                    return true;
                }

                Log::error('Clickatell HTTP SMS failed', [
                    'status' => $response->status(),
                    'response' => $body,
                ]);

                return false;
            }

            $payload = [
                'content' => $message,
                'to' => [$this->formatPhoneNumber($to)],
            ];

            if ($from !== '') {
                $payload['from'] = $from;
            }

            $response = Http::withHeaders([
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($baseUrl, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error('Clickatell JSON SMS failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('Clickatell SMS failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Determine if SMS sending is globally enabled.
     */
    protected function isSmsEnabled(): bool
    {
        return filter_var($this->setting('sms_service_enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Resolve setting value with safe fallback.
     */
    protected function setting(string $key, $default = null)
    {
        try {
            return Setting::get($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }

    /**
     * Resolve SMS config from settings first, then config file fallback.
     */
    protected function settingOrConfig(string $settingKey, string $configKey, $default = null)
    {
        $value = $this->setting($settingKey, null);

        if ($value === null || $value === '') {
            return config($configKey, $default);
        }

        return $value;
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

    protected function formatPhoneDigits(string $phone, string $countryCode = '91'): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if ($countryCode !== '' && str_starts_with($digits, $countryCode) && strlen($digits) > strlen($countryCode)) {
            return substr($digits, strlen($countryCode));
        }

        return $digits;
    }
}

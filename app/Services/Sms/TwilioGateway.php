<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class TwilioGateway implements SmsGatewayInterface
{
    public function send($mobile, $message)
    {
        $authKey = Setting::get('sms_twilio_auth_key');
        $sender  = Setting::get('sms_twilio_sender_id');
        $route   = Setting::get('sms_twilio_route', 4);
        $country = Setting::get('sms_twilio_country', 91);
        $apiUrl  = Setting::get('sms_twilio_api_url');

        $response = Http::withHeaders([
            'authkey' => $authKey
        ])->post($apiUrl, [
            'sender' => $sender,
            'route' => $route,
            'country' => $country,
            'sms' => [
                [
                    'message' => $message,
                    'to' => [$mobile]
                ]
            ]
        ]);

        return $response->json();
    }
}

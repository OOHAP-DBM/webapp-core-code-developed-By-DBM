<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class Msg91Gateway implements SmsGatewayInterface
{
    public function send($mobile, $message)
    {
        $authKey = Setting::get('sms_msg91_auth_key');
        $sender  = Setting::get('sms_msg91_sender_id');
        $route   = Setting::get('sms_msg91_route', 4);
        $country = Setting::get('sms_msg91_country', 91);
        $apiUrl  = Setting::get('sms_msg91_api_url');

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

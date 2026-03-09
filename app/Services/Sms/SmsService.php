<?php

namespace App\Services\Sms;

use App\Models\Setting;
use App\Services\Sms\Msg91Gateway;
use App\Services\Sms\TwilioGateway;

class SmsService
{
    protected $gateway;

    public function __construct()
    {
        $activeGateway = Setting::get('sms_gateway');

        switch ($activeGateway) {

            case 'msg91':
                $this->gateway = new Msg91Gateway();
                break;

            case 'twilio':
                $this->gateway = new TwilioGateway();
                break;

            default:
                throw new \Exception("No SMS gateway configured");
        }
    }

    public function send($mobile, $message)
    {
        return $this->gateway->send($mobile, $message);
    }
}

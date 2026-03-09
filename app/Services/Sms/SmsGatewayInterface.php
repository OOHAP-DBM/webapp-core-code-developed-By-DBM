<?php

namespace App\Services\Sms;

interface SmsGatewayInterface
{
    public function send($mobile, $message);
}

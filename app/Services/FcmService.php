<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;

class FcmService
{
    protected Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Send a notification to a single device token
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [])
    {
        $message = CloudMessage::fromArray([
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);

        return $this->messaging->send($message);
    }

    /**
     * Send a notification to multiple device tokens
     */
    public function sendToMultiple(array $tokens, string $title, string $body, array $data = [])
    {
        $messages = [];
        foreach ($tokens as $token) {
            $messages[] = CloudMessage::fromArray([
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ]);
        }

        return $this->messaging->sendMulticast($messages);
    }

    /**
     * Send a notification to a topic
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = [])
    {
        $message = CloudMessage::fromArray([
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);

        return $this->messaging->send($message);
    }
}

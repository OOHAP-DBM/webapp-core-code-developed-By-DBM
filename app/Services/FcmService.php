<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;

class FcmService
{
    protected function getAccessToken()
    {
        $credentialsPath = base_path(config('app.firebase_credentials'));

        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $credentialsPath
        );

        $token = $credentials->fetchAuthToken();

        return $token['access_token'] ?? null;
    }

    public function send($fcmToken, $title, $body, $data = [])
    {
        if (!$fcmToken) {
            return false;
        }

        $credentialsPath = base_path(config('app.firebase_credentials'));
        $projectId = json_decode(file_get_contents($credentialsPath))->project_id;

        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return false;
        }

        $response = Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                "message" => [
                    "token" => $fcmToken,
                    "notification" => [
                        "title" => $title,
                        "body"  => $body,
                    ],
                    "data" => $data,
                ]
            ]);

        return $response->successful();
    }
}

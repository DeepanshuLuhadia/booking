<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Booking;

class FcmService
{
    protected $credentialsPath;
    protected $projectId;

    public function __construct()
    {
        // Path should ideally point to the actual storage path
        $this->credentialsPath = storage_path('app/fcm.json');
    }

    protected function getProjectId()
    {
        if (!$this->projectId) {
            if (!file_exists($this->credentialsPath)) {
                return null;
            }
            $json = json_decode(file_get_contents($this->credentialsPath), true);
            $this->projectId = $json['project_id'] ?? null;
        }
        return $this->projectId;
    }

    protected function getAccessToken()
    {
        if (!file_exists($this->credentialsPath)) {
            Log::warning("FCM credentials file missing at {$this->credentialsPath}");
            return null;
        }

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($this->credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $token = $client->fetchAccessTokenWithAssertion();
            return $token['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error("Failed to generate FCM access token: " . $e->getMessage());
            return null;
        }
    }

    public function sendToToken($token, $title, $body, $data = [])
    {
        if (!$token) {
            return false;
        }

        $projectId = $this->getProjectId();
        $accessToken = $this->getAccessToken();

        if (!$projectId || !$accessToken) {
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                // FCM data payload requires all values to be strings AND the field to be a
                // JSON object. Casting to (object) makes an empty array serialize as {} rather
                // than [] — otherwise FCM rejects the whole message with INVALID_ARGUMENT
                // ("Cannot bind a list to map for field 'data'").
                'data' => (object) array_map('strval', $data),
                'android' => [
                    'notification' => [
                        'sound' => 'default',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                ],
                'webpush' => [
                    'notification' => [
                        'sound' => '/audio/notification.wav',
                        'icon' => '/favicon.ico',
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withToken($accessToken)->post($url, $message);
            
            if ($response->failed()) {
                Log::error("FCM Send Failed: " . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("FCM Send Exception: " . $e->getMessage());
            return false;
        }
    }
}

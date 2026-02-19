<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    public static function sendNotification($token, $title, $body, $data = [])
    {
        // 1. PAKSA LOAD AUTOLOAD (Karena Composer Windows sering macet) ✅
        $autoloadPath = base_path('vendor/autoload.php');
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }

        try {
            // 2. Gunakan Backslash (\) di depan Google agar tidak nyasar
            $client = new \Google\Client(); 
            $credentialsPath = storage_path('app/firebase/firebase_key.json');
            
            if (!file_exists($credentialsPath)) {
                Log::error("File kunci Firebase tidak ditemukan di: " . $credentialsPath);
                return false;
            }

            $client->setAuthConfig($credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/cloud-platform');

            $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];
            
            $jsonKey = json_decode(file_get_contents($credentialsPath), true);
            $projectId = $jsonKey['project_id'];

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $response = Http::withToken($accessToken)->post($url, [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => array_merge($data, [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',
                    ]),
                ],
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
            return false;
        }
    }
}
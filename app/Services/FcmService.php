<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Client;

class FcmService
{
    public static function sendNotification($token, $title, $body, $data = [])
    {
        if (empty($token)) {
            Log::warning("FCM: Gagal kirim, Token kosong.");
            return false;
        }

        try {
            $credentialsPath = storage_path('app/firebase/firebase_key.json');

            if (!file_exists($credentialsPath)) {
                Log::error("FCM: File JSON tidak ditemukan di $credentialsPath");
                return false;
            }

            $client = new Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/cloud-platform');
            
            $authData    = $client->fetchAccessTokenWithAssertion();
            $accessToken = $authData['access_token'];

            $jsonKey   = json_decode(file_get_contents($credentialsPath), true);
            $projectId = $jsonKey['project_id'];

            $stringifiedData = array_map(fn($v) => (string)$v, $data);
            $stringifiedData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'android' => [
                        'priority' => 'high',
                        'ttl'      => '86400s',
                        'notification' => [
                            // ✅ FIX: Samakan dengan channel Flutter
                            'channel_id'            => 'high_importance_channel',
                            'sound'                 => 'default',
                            'click_action'          => 'FLUTTER_NOTIFICATION_CLICK',
                            'color'                 => '#EA580C',
                            'notification_priority' => 'PRIORITY_MAX',
                            'visibility'            => 'PUBLIC',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound'             => 'default',
                                'badge'             => 1,
                                'content-available' => 1,
                                'mutable-content'   => 1,
                            ],
                        ],
                    ],
                    'data' => $stringifiedData,
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

            if ($response->successful()) {
                Log::info("FCM Sent: {$title} to " . substr($token, 0, 10));
                return true;
            }

            Log::error("FCM Error: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::critical("FCM Critical: " . $e->getMessage());
            return false;
        }
    }
}
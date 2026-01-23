<?php

namespace App\Libraries;

class PushService
{
    protected $logger;

    public function __construct()
    {
        $this->logger = \Config\Services::logger();
    }

    public function sendNotification($receiverId, $messageText, $senderName, $url = null, $senderId = null, $excludeEndpoint = null)
    {
        // Now exclusively using FCM
        return $this->sendFCMNotification($receiverId, $messageText, $senderName, $url, $senderId, $excludeEndpoint);
    }

    protected function sendFCMNotification($receiverId, $messageText, $senderName, $url = null, $senderId = null, $excludeEndpoint = null)
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('fcm_subscriptions');
            
            if (is_array($receiverId)) {
                $builder->whereIn('user_id', $receiverId);
            } else {
                $builder->where('user_id', $receiverId);
            }

            if ($excludeEndpoint) {
                $builder->where('fcm_token !=', $excludeEndpoint);
            }
            
            $subscriptions = $builder->get()->getResultArray();
            $this->logger->info("FCM: Found " . count($subscriptions) . " subscriptions for receiver(s).");
            
            if (empty($subscriptions)) {
                $this->logger->warning("FCM: No subscriptions found for " . (is_array($receiverId) ? implode(',', $receiverId) : $receiverId));
                return false;
            }

            $accessToken = $this->getFCMAccessToken();
            if (!$accessToken) {
                $this->logger->error("FCM: Failed to get Access Token. Check JSON Key file.");
                return false;
            }

            $title = $senderName;
            if ($senderName !== 'System' && strpos($title, 'Pesan dari') === false) {
                $title = 'Pesan dari ' . $senderName;
            }

            $successCount = 0;
            $fcmUrl = 'https://fcm.googleapis.com/v1/projects/jimpitan-app-a7by777/messages:send';

            foreach ($subscriptions as $sub) {
                $token = $sub['fcm_token'];
                
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => mb_substr($messageText, 0, 100, 'UTF-8')
                        ],
                        'webpush' => [
                            'fcm_options' => [
                                'link' => $url ?: '/chat'
                            ],
                            'notification' => [
                                'icon' => 'https://jimpitan.appsbee.my.id/favicon.ico',
                                'badge' => 'https://jimpitan.appsbee.my.id/favicon.ico',
                                'tag' => 'jimpitan-global',
                                'renotify' => true,
                                'requireInteraction' => true,
                                'vibrate' => [200, 100, 200, 100, 200]
                            ]
                        ],
                        'data' => [
                            'url' => $url ?: '/chat',
                            'title' => $title,
                            'body' => (string)$messageText
                        ]
                    ]
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $fcmUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    $successCount++;
                } else {
                    $respDecoded = json_decode($response, true);
                    $errMsg = $respDecoded['error']['message'] ?? $response;
                    $this->logger->error("FCM Send Error to Token " . substr($token, 0, 10) . "... ($httpCode): " . $errMsg);
                    
                    if ($httpCode === 404 || $httpCode === 400 || (isset($respDecoded['error']['status']) && $respDecoded['error']['status'] === 'UNREGISTERED')) {
                         $db->table('fcm_subscriptions')->where('fcm_token', $token)->delete();
                         $this->logger->info("FCM: Deleted invalid/unregistered token.");
                    }
                }
            }

            return $successCount > 0;

        } catch (\Exception $e) {
            $this->logger->error("FCM Exception: " . $e->getMessage());
            return false;
        }
    }

    public function getFCMAccessToken()
    {
        try {
            $jsonFile = ROOTPATH . 'jimpitan-app-a7by777-firebase-adminsdk-fbsvc-bd65b27251.json';
            if (!file_exists($jsonFile)) {
                $this->logger->error("FCM: Service Account JSON not found.");
                return null;
            }

            $config = json_decode(file_get_contents($jsonFile), true);
            $clientEmail = $config['client_email'];
            $privateKey = $config['private_key'];
            $tokenUri = $config['token_uri'];

            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $now = time();
            $payload = json_encode([
                'iss' => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $tokenUri,
                'exp' => $now + 3600,
                'iat' => $now
            ]);

            $base64UrlHeader = $this->base64UrlEncode($header);
            $base64UrlPayload = $this->base64UrlEncode($payload);

            $signature = '';
            if (!openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) return null;
            $base64UrlSignature = $this->base64UrlEncode($signature);

            $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tokenUri);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]));

            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            return $data['access_token'] ?? null;

        } catch (\Exception $e) {
            $this->logger->error("FCM Token Error: " . $e->getMessage());
            return null;
        }
    }

    protected function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}

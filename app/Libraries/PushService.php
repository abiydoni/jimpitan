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
                        // 'notification' block removed to prevent double notification (Browser Auto + SW)
                        // Android Wake-Up Config
                        'android' => [
                            'priority' => 'high',
                            'ttl' => '4500s'
                        ],
                        // HYBRID STRATEGY: Send Notification Block to ensure delivery, but use TAG to merge/deduplicate.
                        'notification' => [
                            'title' => $title,
                            'body' => mb_substr($messageText, 0, 100, 'UTF-8'),
                        ],
                        'webpush' => [
                            'headers' => [
                                'Urgency' => 'high',
                                'TTL' => '4500'
                            ]
                        ],
                        'data' => [
                            'url' => $url ?: '/chat',
                            'title' => $title,
                            'body' => (string)$messageText,
                            'click_action' => $url ?: '/chat', // Legacy support
                            'sender_id' => (string)$senderId,
                            // SERVER-DRIVEN CONFIGURATION
                            // Change these in PHP, and JS will obey. No need to edit JS anymore.
                            'tag' => 'jimpitan-chat', 
                            'renotify' => 'true',
                            'auto_close' => '5000', // 5 seconds (Set to '0' to disable auto-close)
                            'require_interaction' => 'false',
                            'icon' => base_url('jimpitan1.png'), // Use confirmed Manifest Icon
                            'badge' => base_url('jimpitan1.png'),
                            'sound' => 'default',
                            'vibrate' => json_encode([200, 100, 200]) // Send array as JSON string
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
                    
                    // Strict check: Only delete if explicitly UNREGISTERED or INVALID_ARGUMENT (Token)
                    $errorCode = $respDecoded['error']['status'] ?? '';
                    $errorDetails = $respDecoded['error']['message'] ?? '';
                    
                    if ($errorCode === 'UNREGISTERED' || 
                       ($errorCode === 'INVALID_ARGUMENT' && stripos($errorDetails, 'token') !== false)) {
                         $db->table('fcm_subscriptions')->where('fcm_token', $token)->delete();
                         $this->logger->info("FCM: Deleted invalid/unregistered token: " . substr($token, 0, 10));
                    } else {
                         // Just log other errors (e.g. Quota, Internal, Malformed Payload)
                         $this->logger->warning("FCM Error (Retrying next time): $errorCode - $errorDetails");
                    }
                }
            }

            return $successCount > 0;

        } catch (\Exception $e) {
            $this->logger->error("FCM Exception: " . $e->getMessage());
            return false;
        }
    }

    private $lastError = null;

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getFCMAccessToken()
    {
        try {
            $jsonFile = ROOTPATH . 'jimpitan-app-a7by777-firebase-adminsdk-fbsvc-bd65b27251.json';
            if (!file_exists($jsonFile)) {
                $this->lastError = "Service Account JSON not found at $jsonFile";
                $this->logger->error("FCM: " . $this->lastError);
                return null;
            }

            $jsonContent = file_get_contents($jsonFile);
            $config = json_decode($jsonContent, true);
            if (!$config) {
                $this->lastError = "Invalid JSON format in Service Account file.";
                $this->logger->error("FCM: " . $this->lastError);
                return null;
            }

            $clientEmail = $config['client_email'] ?? null;
            $privateKey = $config['private_key'] ?? null;
            $tokenUri = $config['token_uri'] ?? 'https://oauth2.googleapis.com/token';

            if (!$clientEmail || !$privateKey) {
                $this->lastError = "Missing client_email or private_key in JSON.";
                $this->logger->error("FCM: " . $this->lastError);
                return null;
            }

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
            // Suppress warnings for openssl_sign to capture them if needed, or rely on return false
            if (!openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                $sslError = "";
                while ($msg = openssl_error_string()) {
                    $sslError .= $msg . "; ";
                }
                $this->lastError = "openssl_sign failed. OpenSSL Error: $sslError";
                $this->logger->error("FCM: " . $this->lastError);
                return null;
            }
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
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode !== 200) {
                $this->lastError = "FCM Token API Error ($httpCode): $response. Curl Error: $curlError";
                $this->logger->error("FCM: " . $this->lastError);
                return null;
            }

            $data = json_decode($response, true);
            return $data['access_token'] ?? null;

        } catch (\Exception $e) {
            $this->lastError = "FCM Token Exception: " . $e->getMessage();
            $this->logger->error($this->lastError);
            return null;
        }
    }

    protected function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}

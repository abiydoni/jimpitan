<?php

namespace App\Libraries;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushService
{
    protected $logger;

    public function __construct()
    {
        $this->logger = \Config\Services::logger();
    }

    public function sendNotification($receiverId, $messageText, $senderName, $url = null)
    {
        try {
            // Check library
            if (!class_exists('Minishlink\WebPush\WebPush')) {
                $this->logger->error("Push error: WebPush library class not found!");
                return false;
            }

            // Ensure OPENSSL_CONF is set
            $opensslConfigPath = FCPATH . '../openssl.cnf';
            $realPath = realpath($opensslConfigPath);
            
            if ($realPath && file_exists($realPath)) {
                putenv("OPENSSL_CONF=" . $realPath);
            }

            $db = \Config\Database::connect();
            $subscriptions = $db->table('push_subscriptions')
                                ->where('user_id', $receiverId)
                                ->get()
                                ->getResultArray();

            if (empty($subscriptions)) return false;

            // Load VAPID Keys
            $envPath = FCPATH . '../.env';
            $publicKey = '';
            $privateKey = '';
            
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                if (preg_match('/^VAPID_PUBLIC_KEY=(.*)$/m', $envContent, $matches)) {
                    $publicKey = trim($matches[1], "\"' \t\n\r\0\x0B");
                }
                if (preg_match('/^VAPID_PRIVATE_KEY=(.*)$/m', $envContent, $matches)) {
                    $privateKey = trim($matches[1], "\"' \t\n\r\0\x0B");
                }
            }
            
            if (empty($publicKey)) $publicKey = getenv('VAPID_PUBLIC_KEY');
            if (empty($privateKey)) $privateKey = getenv('VAPID_PRIVATE_KEY');

            $privateKey = trim($privateKey, "\"' \t\n\r\0\x0B");
            $publicKey  = trim($publicKey, "\"' \t\n\r\0\x0B");

            // Format Key
            if (strpos($privateKey, '\n') !== false) {
                 $privateKey = str_replace('\n', "\n", $privateKey);
            }
            if (strpos($privateKey, 'BEGIN') === false && strlen($privateKey) > 60) {
                 $privateKey = "-----BEGIN EC PRIVATE KEY-----\n" . 
                               chunk_split($privateKey, 64, "\n") . 
                               "-----END EC PRIVATE KEY-----";
            }

            $subject = 'https://jimpitan.appsbee.my.id/';
            if (!filter_var($subject, FILTER_VALIDATE_URL) && strpos($subject, 'mailto:') !== 0) {
                $subject = 'mailto:admin@jimpitan.appsbee.my.id'; 
            }

            $auth = [
                'VAPID' => [
                    'subject' => $subject,
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ];

            $webPush = new WebPush($auth);

            // Construct Title and URL
            $title = $senderName;
            if ($senderName !== 'System' && strpos($title, 'Pesan dari') === false) {
                $title = 'Pesan dari ' . $senderName;
            }

            // Default URL logic if not provided
            if (!$url) {
                // If we are in a session context (e.g. Chat Controller), we might want to link to THIS user?
                // No, link to the SENDER.
                // But $receiverId is the recipient. 
                // We don't have senderId here easily passing through unless we add it to args.
                // But for now, let's just default to /chat
                $url = '/chat';
            }

            $payloadData = [
                'title' => $title,
                'body' => mb_substr($messageText, 0, 100, 'UTF-8'),
                'url' => $url
            ];
            
            $payload = json_encode($payloadData, JSON_UNESCAPED_UNICODE);

            foreach ($subscriptions as $sub) {
                if (empty($sub['endpoint'])) continue;

                $subscription = Subscription::create([
                    'endpoint' => $sub['endpoint'],
                    'keys' => [
                        'p256dh' => $sub['p256dh'],
                        'auth' => $sub['auth'],
                    ],
                ]);

                $webPush->queueNotification($subscription, $payload);
            }

            $successCount = 0;
            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();
                if (!$report->isSuccess()) {
                    $this->logger->error("Push fail for $endpoint: " . $report->getReason());
                    if ($report->isSubscriptionExpired()) {
                        $db->table('push_subscriptions')->where('endpoint', $endpoint)->delete();
                    }
                } else {
                    $successCount++;
                }
            }
            
            return $successCount > 0;

        } catch (\Exception $e) {
            $this->logger->error("Push Exception: " . $e->getMessage());
            return false;
        }
    }
}

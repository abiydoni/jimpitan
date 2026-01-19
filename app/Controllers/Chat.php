<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ChatModel;
use App\Models\UserModel;

class Chat extends BaseController
{
    protected $chatModel;
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->chatModel = new ChatModel();
        $this->userModel = new UserModel();
        $this->session = session();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        $data = [
            'title' => 'Ruang Pesan',
            'user_id' => session()->get('id_code'),
            'user_name' => session()->get('name'),
            'vapid_public_key' => getenv('VAPID_PUBLIC_KEY')
        ];
        return view('chat/index', $data);
    }

    public function getUsers()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $currentUserId = session()->get('id_code');
        $users = $this->userModel->where('id_code !=', $currentUserId)->findAll();
        
        // Get last activity times
        $activityTimes = $this->chatModel->getLastActivityTimes($currentUserId);
        
        // Add unread count and last activity for each user
        foreach ($users as &$user) {
            $user['unread_count'] = $this->chatModel->where('sender_id', $user['id_code'])
                                                    ->where('receiver_id', $currentUserId)
                                                    ->where('is_read', 0)
                                                    ->countAllResults();
            
            // Activity time
            $user['last_activity'] = $activityTimes[$user['id_code']] ?? '0000-00-00 00:00:00';
        }

        // Add Group Chat Option
        $groupChat = [
            'id_code' => 'GROUP_ALL',
            'name' => 'Forum Warga',
            'user_name' => 'Grup',
            'role' => 'group',
            'foto' => 'group_icon.png', // We handle this in frontend or use default
            'unread_count' => 0, // Future work
            'last_activity' => $activityTimes['GROUP_ALL'] ?? '0000-00-00 00:00:00'
        ];
        
        // Add group to array
        $users[] = $groupChat;
        
        // Sort by last_activity DESC
        usort($users, function ($a, $b) {
            return strcmp($b['last_activity'], $a['last_activity']);
        });

        // Re-index array (optional but safer for JSON)
        $users = array_values($users);

        return $this->response->setJSON($users);
    }

    public function getMessages()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $currentUserId = session()->get('id_code');
        $otherUserId = $this->request->getGet('user_id');

        if (!$otherUserId) {
            return $this->response->setJSON([]);
        }

        // Mark messages as read
        if ($otherUserId !== 'GROUP_ALL') {
            $this->chatModel->markAsRead($otherUserId, $currentUserId);
            $messages = $this->chatModel->getConversation($currentUserId, $otherUserId);
        } else {
            $messages = $this->chatModel->getGroupMessages();
        }
        
        return $this->response->setJSON($messages);
    }

    public function sendMessage()
    {
        log_message('error', 'DEBUG: Chat::sendMessage called');
        
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $currentUserId = session()->get('id_code');
        $receiverId = $this->request->getPost('receiver_id');
        $message = $this->request->getPost('message');
        $replyToId = $this->request->getPost('reply_to_id'); // Optional

        log_message('error', "DEBUG: Params - Receiver: $receiverId, Msg: " . substr($message, 0, 10));

        if (!$receiverId || !$message) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing fields']);
        }

        $data = [
            'sender_id' => $currentUserId,
            'receiver_id' => $receiverId,
            'message' => $message,
            'reply_to_id' => $replyToId ?: null,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Check Db connection
        if (!$this->chatModel) {
            log_message('error', 'DEBUG: ChatModel is null');
        }

        if ($this->chatModel->insert($data)) {
            log_message('error', 'DEBUG: Insert success. Triggering Push...');
            // Trigger Push Notification
            try {
                $this->triggerPush($receiverId, $message, session()->get('name'));
            } catch (\Exception $e) {
                log_message('error', 'DEBUG: triggerPush threw Exception: ' . $e->getMessage());
            }
        } else {
             log_message('error', 'DEBUG: Insert failed.');
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    private function triggerPush($receiverId, $messageText, $senderName)
    {
        try {
            // Check library
            if (!class_exists('Minishlink\WebPush\WebPush')) {
                log_message('error', "Push error: WebPush library class not found!");
                return; 
            }

            // FIX: Ensure OPENSSL_CONF is set
            $opensslConfigPath = FCPATH . '../openssl.cnf';
            $realPath = realpath($opensslConfigPath);
            
            if ($realPath && file_exists($realPath)) {
                putenv("OPENSSL_CONF=" . $realPath);
            } else {
                log_message('warning', "Push warning: openssl.cnf not found!");
            }

            $db = \Config\Database::connect();
            $subscriptions = $db->table('push_subscriptions')
                                ->where('user_id', $receiverId)
                                ->get()
                                ->getResultArray();

            if (empty($subscriptions)) return;

            // BYPASS CACHE: Read .env directly because getenv() might be stale
            $envPath = FCPATH . '../.env';
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                if (preg_match('/^VAPID_PUBLIC_KEY=(.*)$/m', $envContent, $matches)) {
                    $publicKey = trim($matches[1], "\"' \t\n\r\0\x0B");
                }
                if (preg_match('/^VAPID_PRIVATE_KEY=(.*)$/m', $envContent, $matches)) {
                    $privateKey = trim($matches[1], "\"' \t\n\r\0\x0B");
                }
            }
            
            // Fallback to getenv if parse failed
            if (empty($publicKey)) $publicKey = getenv('VAPID_PUBLIC_KEY');
            if (empty($privateKey)) $privateKey = getenv('VAPID_PRIVATE_KEY');

            // FIX: Trim strict quotes again just in case
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

            $webPush = new \Minishlink\WebPush\WebPush($auth);

            $payloadData = [
                'title' => 'Pesan dari ' . $senderName,
                'body' => mb_substr($messageText, 0, 100, 'UTF-8'),
                'url' => '/chat?user_id=' . session()->get('id_code')
            ];
            $payload = json_encode($payloadData, JSON_UNESCAPED_UNICODE);

            foreach ($subscriptions as $sub) {
                if (empty($sub['endpoint'])) continue;

                $subscription = \Minishlink\WebPush\Subscription::create([
                    'endpoint' => $sub['endpoint'],
                    'keys' => [
                        'p256dh' => $sub['p256dh'],
                        'auth' => $sub['auth'],
                    ],
                ]);

                $webPush->queueNotification($subscription, $payload);
            }

            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();
                if (!$report->isSuccess()) {
                    log_message('error', "Push fail for $endpoint: " . $report->getReason());
                    if ($report->isSubscriptionExpired()) {
                        $db->table('push_subscriptions')->where('endpoint', $endpoint)->delete();
                    }
                }
            }

        } catch (\Exception $e) {
            log_message('error', "Push Exception: " . $e->getMessage());
        }
    }
    
    public function pollUpdates()
    {
         if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }
        
        $currentUserId = session()->get('id_code');
        $activeChatUser = $this->request->getGet('active_user');
        
        $updates = [
            'total_unread' => $this->chatModel->getUnreadCount($currentUserId),
            'recent_chats' => $this->chatModel->getRecentConversations($currentUserId),
            'messages' => []
        ];
        
        if ($activeChatUser) {
             if ($activeChatUser === 'GROUP_ALL') {
                 $updates['messages'] = $this->chatModel->getGroupMessages();
             } else {
                 $updates['messages'] = $this->chatModel->getConversation($currentUserId, $activeChatUser);
                 // Also mark read if currently viewing
                 $this->chatModel->markAsRead($activeChatUser, $currentUserId);
             }
        }
        
        return $this->response->setJSON($updates);
    }
}

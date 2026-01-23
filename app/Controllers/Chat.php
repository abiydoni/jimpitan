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
            'user_foto' => session()->get('foto'),
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
        $db = \Config\Database::connect();
        
        // Auto-fix & Column Check
        $hasLastActiveCol = $db->fieldExists('last_active_at', 'users');
        if (!$hasLastActiveCol) {
            try {
                $db->query("ALTER TABLE users ADD COLUMN last_active_at DATETIME DEFAULT NULL");
                $hasLastActiveCol = true;
            } catch (\Exception $e) {
                log_message('error', 'Failed to add last_active_at column: ' . $e->getMessage());
            }
        }

        // Update current user activity if column exists
        if ($hasLastActiveCol) {
            try {
                $this->userModel->update($currentUserId, ['last_active_at' => date('Y-m-d H:i:s')]);
            } catch (\Exception $e) {}
        }
        
        // Select only existing columns to prevent "Unknown column" error
        $builder = $this->userModel->where('id_code !=', $currentUserId);
        if ($hasLastActiveCol) {
            $users = $builder->findAll();
        } else {
            // Select everything EXCEPT last_active_at if it's still missing for some reason
            $fields = ['id_code', 'user_name', 'name', 'password', 'role', 'shift', 'remember_token', 'nikk', 'tarif'];
            $users = $builder->select(implode(',', $fields))->findAll();
        }
        
        // Fetch Photos from tb_warga
        $names = array_column($users, 'name');
        $photoMap = [];
        if (!empty($names)) {
            $wargas = $db->table('tb_warga')
                         ->select('nama, foto')
                         ->whereIn('nama', $names)
                         ->get()->getResultArray();
                         
            foreach ($wargas as $w) {
                if (!empty($w['foto'])) {
                    $photoMap[$w['nama']] = $w['foto'];
                }
            }
        }
        
        // Get last activity times
        $activityTimes = $this->chatModel->getLastActivityTimes($currentUserId);
        
        $now = time();
        $onlineThreshold = 120; // 2 minutes

        // Add unread count and last activity for each user
        foreach ($users as &$user) {
            $user['unread_count'] = $this->chatModel->where('sender_id', $user['id_code'])
                                                    ->where('receiver_id', $currentUserId)
                                                    ->where('is_read', 0)
                                                    ->countAllResults();
            
            // Activity time
            $user['last_activity'] = $activityTimes[$user['id_code']] ?? '0000-00-00 00:00:00';
            
            // Online Status based on last_active_at
            $lastActive = isset($user['last_active_at']) ? strtotime($user['last_active_at']) : 0;
            $user['is_online'] = ($now - $lastActive) < $onlineThreshold;

            // Add Photo
            $user['foto'] = $photoMap[$user['name']] ?? null;
        }

        // Group Chat Unread Logic
        $db = \Config\Database::connect();
        $lastReadId = 0;
        try {
            $groupRead = $db->table('chat_groups_read')->where('user_id', $currentUserId)->get()->getRowArray();
            $lastReadId = $groupRead ? $groupRead['last_read_message_id'] : 0;
        } catch (\Exception $e) {
            // Table might be missing, ignore and default to 0
        }
        
        $groupUnread = $this->chatModel->where('receiver_id', 'GROUP_ALL')
                                       ->where('id >', $lastReadId)
                                       ->countAllResults();

        // Add Group Chat Option
        $groupChat = [
            'id_code' => 'GROUP_ALL',
            'name' => 'Forum Warga',
            'user_name' => 'Grup',
            'role' => 'group',
            'foto' => 'group_icon.png', // We handle this in frontend or use default
            'unread_count' => $groupUnread,
            'is_online' => true,
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
            
            // Mark Group as Read (Upsert)
            if (!empty($messages)) {
                try {
                    $db = \Config\Database::connect();
                    // We assume successful load means user sees LATEST messages. 
                    // So we set last_read to the absolute max ID in the group channel.
                    $maxGroupMsg = $this->chatModel->selectMax('id')->where('receiver_id', 'GROUP_ALL')->first();
                    $maxId = $maxGroupMsg ? $maxGroupMsg['id'] : 0;
                    
                    if ($maxId > 0) {
                         $sql = "INSERT INTO chat_groups_read (user_id, last_read_message_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE last_read_message_id = GREATEST(last_read_message_id, VALUES(last_read_message_id))";
                         $db->query($sql, [$currentUserId, $maxId]);
                    }
                } catch (\Exception $e) {
                    // Ignore if table missing
                }
            }
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
            // Get ID if needed, but we just trigger push
            $newMsgId = $this->chatModel->getInsertID();
            
            log_message('error', 'DEBUG: Insert success. Triggering Push...');
            
            // Trigger Push Notification via Service
            try {
                $pushService = new \App\Libraries\PushService();
                $senderName = session()->get('name');
                
                // Determine URL based on Receiver
                // If receiver is a User, they need to click and go to ?user_id=SENDER
                // If receiver is GROUP, they go to ?user_id=GROUP_ALL
                
                // Currently Chat::sendMessage handles ONE receiver. 
                // If receiver_id == 'GROUP_ALL', we should probably handle it specially, 
                // but PushService expects a single User ID for lookup usually.
                // However, let's just pass the correct URL for Personal Chat for now.
                
                $currentUserId = session()->get('id_code');
                $excludeEndpoint = $this->request->getPost('exclude_endpoint');
                log_message('info', "Chat::sendMessage - Sender: $currentUserId, ExcludeEndpoint: " . ($excludeEndpoint ?: 'NULL'));
                $redirectUrl = '/chat';
                if ($receiverId !== 'GROUP_ALL') {
                     $redirectUrl = '/chat?user_id=' . $currentUserId;
                } else {
                     $redirectUrl = '/chat?user_id=GROUP_ALL';
                }
                
                $success = false;

                if ($receiverId === 'GROUP_ALL') {
                    // GROUP MSG
                    $db = \Config\Database::connect();
                    $users = $db->table('users')
                                ->select('id_code')
                                ->where('id_code !=', $currentUserId)
                                ->get()->getResultArray();
                    
                    // Optimized: Batch Send with Sender Exclusion
                    $userIds = array_column($users, 'id_code');
                    $success = $pushService->sendNotification($userIds, $message, $senderName, $redirectUrl, $currentUserId, $excludeEndpoint);

                } else {
                    // PERSONAL MSG
                    $success = $pushService->sendNotification($receiverId, $message, $senderName, $redirectUrl, $currentUserId, $excludeEndpoint);
                }
                
                if ($success) {
                     $this->chatModel->update($newMsgId, ['notification_sent' => 1]);
                     log_message('info', "Push Success for Msg ID $newMsgId");
                } else {
                     log_message('error', "Push Failed for Msg ID $newMsgId. Will be picked up by Cron.");
                     // Leave notification_sent = 0 so Cron Job picks it up!
                     $this->chatModel->update($newMsgId, ['notification_sent' => 0]); 
                }

            } catch (\Exception $e) {
                log_message('error', 'DEBUG: PushService threw Exception: ' . $e->getMessage());
                // Ensure it's 0 so Cron picks it up
                $this->chatModel->update($newMsgId, ['notification_sent' => 0]); 
            }
        } else {
             log_message('error', 'DEBUG: Insert failed.');
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => ['id' => $newMsgId ?? null]
        ]);
    }

    /**
     * API Endpoint for External Systems (e.g., auto_send_test.php)
     * To make cron-based messages real-time.
     */
    public function sendSystemMessage()
    {
        $apiKey = $this->request->getGet('key') ?: $this->request->getPost('key');
        $expectedKey = getenv('CHAT_SYSTEM_KEY');

        if (!$expectedKey || $apiKey !== $expectedKey) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid API Key'])->setStatusCode(403);
        }

        $receiverId = $this->request->getPost('receiver_id');
        $message = $this->request->getPost('message');
        $senderId = $this->request->getPost('sender_id') ?: 'SYSTEM';
        $senderName = $this->request->getPost('sender_name') ?: 'System';

        if (!$receiverId || !$message) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'receiver_id and message are required'])->setStatusCode(400);
        }

        $data = [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $message,
            'is_read' => 0,
            'notification_sent' => 0,
            'reply_to_id' => null, // Ditambahkan agar semua kolom terisi
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->chatModel->insert($data)) {
            $newMsgId = $this->chatModel->getInsertID();
            
            // Trigger Immediate Push
            try {
                $pushService = new \App\Libraries\PushService();
                $redirectUrl = ($receiverId === 'GROUP_ALL') ? '/chat?user_id=GROUP_ALL' : '/chat?user_id=' . $senderId;
                
                $success = false;
                if ($receiverId === 'GROUP_ALL') {
                    $db = \Config\Database::connect();
                    $users = $db->table('users')->select('id_code')->where('id_code !=', $senderId)->get()->getResultArray();
                    $userIds = array_column($users, 'id_code');
                    $success = $pushService->sendNotification($userIds, $message, $senderName, $redirectUrl, $senderId);
                } else {
                    $success = $pushService->sendNotification($receiverId, $message, $senderName, $redirectUrl, $senderId);
                }

                if ($success) {
                    $this->chatModel->update($newMsgId, ['notification_sent' => 1]);
                }
            } catch (\Exception $e) {
                log_message('error', 'System Chat API Push Error: ' . $e->getMessage());
            }

            return $this->response->setJSON([
                'status' => 'success',
                'chat_id' => $newMsgId,
                'notification' => isset($success) && $success ? 'sent' : 'queued'
            ]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save message'])->setStatusCode(500);
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
    
    // Calculate Group Unread
    $db = \Config\Database::connect();
    $groupRead = $db->table('chat_groups_read')->where('user_id', $currentUserId)->get()->getRowArray();
    $lastReadId = $groupRead ? $groupRead['last_read_message_id'] : 0;
    
    $groupUnread = $this->chatModel->where('receiver_id', 'GROUP_ALL')
                                   ->where('id >', $lastReadId)
                                   ->countAllResults();
    
    $privateUnread = $this->chatModel->getUnreadCount($currentUserId);

    $updates = [
        'total_unread' => $privateUnread + $groupUnread,
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

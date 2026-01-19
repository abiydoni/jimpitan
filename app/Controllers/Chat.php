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
        $users = $this->userModel->where('id_code !=', $currentUserId)->findAll();
        
        // Fetch Photos from tb_warga
        $names = array_column($users, 'name');
        $photoMap = [];
        if (!empty($names)) {
            $db = \Config\Database::connect();
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
        
        // Add unread count and last activity for each user
        foreach ($users as &$user) {
            $user['unread_count'] = $this->chatModel->where('sender_id', $user['id_code'])
                                                    ->where('receiver_id', $currentUserId)
                                                    ->where('is_read', 0)
                                                    ->countAllResults();
            
            // Activity time
            $user['last_activity'] = $activityTimes[$user['id_code']] ?? '0000-00-00 00:00:00';
            
            // Add Photo
            $user['foto'] = $photoMap[$user['name']] ?? null;
        }

        // Group Chat Unread Logic
        $db = \Config\Database::connect();
        $groupRead = $db->table('chat_groups_read')->where('user_id', $currentUserId)->get()->getRowArray();
        $lastReadId = $groupRead ? $groupRead['last_read_message_id'] : 0;
        
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
                $db = \Config\Database::connect();
                // We assume successful load means user sees LATEST messages. 
                // So we set last_read to the absolute max ID in the group channel.
                $maxGroupMsg = $this->chatModel->selectMax('id')->where('receiver_id', 'GROUP_ALL')->first();
                $maxId = $maxGroupMsg ? $maxGroupMsg['id'] : 0;
                
                if ($maxId > 0) {
                     $sql = "INSERT INTO chat_groups_read (user_id, last_read_message_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE last_read_message_id = GREATEST(last_read_message_id, VALUES(last_read_message_id))";
                     $db->query($sql, [$currentUserId, $maxId]);
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
                
                $redirectUrl = '/chat';
                if ($receiverId !== 'GROUP_ALL') {
                     $currentUserId = session()->get('id_code'); // The sender
                     $redirectUrl = '/chat?user_id=' . $currentUserId;
                } else {
                     $redirectUrl = '/chat?user_id=GROUP_ALL';
                }
                
                $success = false;

                if ($receiverId === 'GROUP_ALL') {
                    // GROUP MSG: Send to ALL users except sender
                    $db = \Config\Database::connect();
                    $currentUserId = session()->get('id_code');
                    $users = $db->table('users')
                                ->select('id_code')
                                ->where('id_code !=', $currentUserId)
                                ->get()->getResultArray();
                    
                    $sentCount = 0;
                    foreach ($users as $u) {
                        // For group, Title usually indicates it's a group msg
                        // But here we rely on the Sender Name.
                        // Let's assume standard behavior.
                        $isSent = $pushService->sendNotification($u['id_code'], $message, $senderName, $redirectUrl);
                        if ($isSent) $sentCount++;
                    }
                    
                    // Consider success if at least one person got it? 
                    // Or just mark as enabled. 
                    // NOTE: If we mark success=false, Cron will retry loop for ALL users again.
                    // This might cause spam for those who already got it.
                    // Ideally Cron should be smart, but for now let's say:
                    // If we attempted to send to everyone, mark as done.
                    $success = true; 

                } else {
                    // PERSONAL MSG
                    $success = $pushService->sendNotification($receiverId, $message, $senderName, $redirectUrl);
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

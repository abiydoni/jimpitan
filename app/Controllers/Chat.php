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
            'user_name' => session()->get('name')
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
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $currentUserId = session()->get('id_code');
        $receiverId = $this->request->getPost('receiver_id');
        $message = $this->request->getPost('message');
        $replyToId = $this->request->getPost('reply_to_id'); // Optional

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

        $this->chatModel->insert($data);

        return $this->response->setJSON(['status' => 'success']);
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
            'unread_messages' => $this->chatModel->getRecentConversations($currentUserId), // Reusing key 'unread_messages' or change to 'recent_chats'? 
            // Let's keep key 'unread_messages' to minimize frontend change if structure is compatible, 
            // BUT semantically it's wrong. Let's call it 'recent_chats' and update frontend.
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

<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatModel extends Model
{
    protected $table            = 'chats';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['sender_id', 'receiver_id', 'message', 'is_read', 'reply_to_id', 'created_at', 'updated_at'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'sender_id'   => 'required',
        'receiver_id' => 'required',
        'message'     => 'required',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getConversation($user1, $user2)
    {
        return $this->select('chats.*, 
                            reply.message as reply_message, 
                            reply_user.name as reply_sender')
                    ->join('chats as reply', 'reply.id = chats.reply_to_id', 'left')
                    ->join('users as reply_user', 'reply_user.id_code = reply.sender_id', 'left')
                    ->where("(chats.sender_id = '$user1' AND chats.receiver_id = '$user2') OR (chats.sender_id = '$user2' AND chats.receiver_id = '$user1')")
                    ->orderBy('chats.created_at', 'ASC')
                    ->findAll();
    }
    
    public function getGroupMessages()
    {
        return $this->select('chats.*, 
                            users.name as sender_name,
                            reply.message as reply_message, 
                            reply_user.name as reply_sender')
                    ->join('users', 'users.id_code = chats.sender_id', 'left')
                    ->join('chats as reply', 'reply.id = chats.reply_to_id', 'left')
                    ->join('users as reply_user', 'reply_user.id_code = reply.sender_id', 'left')
                    ->where('chats.receiver_id', 'GROUP_ALL')
                    ->orderBy('chats.created_at', 'ASC')
                    ->findAll();
    }
    
    public function markAsRead($sender_id, $receiver_id)
    {
        return $this->where('sender_id', $sender_id)
                    ->where('receiver_id', $receiver_id)
                    ->where('is_read', 0)
                    ->set(['is_read' => 1])
                    ->update();
    }
    
    public function getUnreadCount($receiver_id)
    {
         return $this->where('receiver_id', $receiver_id)
                     ->where('is_read', 0)
                     ->countAllResults();
    }
    
    public function getUnreadMessagesWithInfo($receiver_id)
    {
        // Join with Users table to get Sender Name
        return $this->select('chats.*, users.name as sender_name')
                    ->join('users', 'users.id_code = chats.sender_id')
                    ->where('chats.receiver_id', $receiver_id)
                    ->where('chats.is_read', 0)
                    ->orderBy('chats.created_at', 'DESC')
                    ->groupBy('chats.sender_id') 
                    ->findAll();
    }
    
    // Better implementation: Get latest unread message per sender
    public function getRecentUnread($receiver_id, $limit = 5)
    {
         return $this->select('chats.*, users.name as sender_name')
                    ->join('users', 'users.id_code = chats.sender_id')
                    ->where('chats.receiver_id', $receiver_id)
                    ->where('chats.is_read', 0)
                    ->orderBy('chats.created_at', 'DESC')
                    ->findAll($limit);
    }

    public function getRecentConversations($userId, $limit = 5)
    {
         // Modified query to include Group Chat
         $messages = $this->groupStart()
                         ->where('sender_id', $userId)
                         ->orWhere('receiver_id', $userId)
                         ->orWhere('receiver_id', 'GROUP_ALL') // Include Group Messages
                         ->groupEnd()
                         ->orderBy('created_at', 'DESC')
                         ->findAll(50);
                         
        $chats = [];
        $recordedPartners = [];
        $db = \Config\Database::connect();
        
        foreach ($messages as $msg) {
            $isGroup = $msg['receiver_id'] === 'GROUP_ALL';
            
            if ($isGroup) {
                $partnerId = 'GROUP_ALL';
            } else {
                $isSender = $msg['sender_id'] == $userId;
                $partnerId = $isSender ? $msg['receiver_id'] : $msg['sender_id'];
            }
            
            if (in_array($partnerId, $recordedPartners)) continue;
            
            $recordedPartners[] = $partnerId;
            
            if ($isGroup) {
                $msg['partner_name'] = 'Forum Warga'; // Default Name
                $msg['partner_id'] = 'GROUP_ALL';
                $msg['unread_count'] = 0; 
            } else {
                $partner = $db->table('users')->select('name')->where('id_code', $partnerId)->get()->getRowArray();
                $msg['partner_name'] = $partner ? $partner['name'] : 'Unknown';
                $msg['partner_id'] = $partnerId;
                
                $msg['unread_count'] = $this->where('sender_id', $partnerId)
                                            ->where('receiver_id', $userId)
                                            ->where('is_read', 0)
                                            ->countAllResults();
            }
            
            $chats[] = $msg;
            
            if (count($chats) >= $limit) break;
        }
        
        return $chats;
    }
}

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
        $partnerIdsToFetch = [];
        
        // First pass: identify unique conversations
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
            if (!$isGroup) {
                $partnerIdsToFetch[] = $partnerId;
            }
            
            // Initialize basic data
            $msg['partner_id'] = $partnerId;
            $msg['unread_count'] = 0; // Default
            $msg['partner_name'] = 'Unknown'; // Default
            
            $chats[$partnerId] = $msg;
            
            if (count($chats) >= $limit) break;
        }

        // Batch Fetch User Names
        $userMap = [];
        if (!empty($partnerIdsToFetch)) {
            $db = \Config\Database::connect();
            $users = $db->table('users')
                        ->select('id_code, name')
                        ->whereIn('id_code', $partnerIdsToFetch)
                        ->get()
                        ->getResultArray();
            
            foreach ($users as $u) {
                $userMap[$u['id_code']] = $u['name'];
            }
        }

        // Batch Fetch Unread Counts
        $unreadMap = [];
        if (!empty($partnerIdsToFetch)) {
            $unreads = $this->select('sender_id, COUNT(*) as cnt')
                            ->where('receiver_id', $userId)
                            ->where('is_read', 0)
                            ->whereIn('sender_id', $partnerIdsToFetch)
                            ->groupBy('sender_id')
                            ->findAll();
                            
            foreach ($unreads as $u) {
                $unreadMap[$u['sender_id']] = $u['cnt'];
            }
        }

        // Final Assembly
        // We use array_values to reset keys and ensure order from first pass is preserved (which was by date)
        $finalChats = [];
        foreach ($recordedPartners as $pid) {
            if (!isset($chats[$pid])) continue;
            
            $c = $chats[$pid];
            
            if ($pid === 'GROUP_ALL') {
                $c['partner_name'] = 'Forum Warga';
                $c['unread_count'] = 0;
            } else {
                $c['partner_name'] = $userMap[$pid] ?? 'Unknown';
                $c['unread_count'] = $unreadMap[$pid] ?? 0;
            }
            
            $finalChats[] = $c;
        }
        
        return $finalChats;
    }
    public function getLastActivityTimes($userId)
    {
         $db = \Config\Database::connect();
         
         // Query to find latest interaction between user and others
         $sql = "
            SELECT partner_id, MAX(created_at) as last_activity 
            FROM (
                SELECT receiver_id as partner_id, created_at FROM chats WHERE sender_id = ? AND receiver_id != 'GROUP_ALL'
                UNION ALL
                SELECT sender_id as partner_id, created_at FROM chats WHERE receiver_id = ?
            ) as combined
            GROUP BY partner_id
         ";
         
         $query = $db->query($sql, [$userId, $userId]);
         $individual = $query->getResultArray();
         
         $result = [];
         foreach ($individual as $row) {
             $result[$row['partner_id']] = $row['last_activity'];
         }

         // Group chat activity
         $groupLast = $this->where('receiver_id', 'GROUP_ALL')
                           ->orderBy('created_at', 'DESC')
                           ->first();
                           
         if ($groupLast) {
            $result['GROUP_ALL'] = $groupLast['created_at'];
         }

         return $result;
    }
}

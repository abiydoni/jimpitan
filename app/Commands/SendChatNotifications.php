<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\PushService;

class SendChatNotifications extends BaseCommand
{
    protected $group       = 'Chat';
    protected $name        = 'chat:notify';
    protected $description = 'Sends push notifications for un-notified messages.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $logger = \Config\Services::logger();
        
        $pushService = new PushService();
        
        // 1. Get messages not notified
        $builder = $db->table('chats');
        $builder->where('notification_sent', 0);
        // Only verify messages from last 24 hours to prevent spamming old stuff upon migration
        $builder->where('created_at >=', date('Y-m-d H:i:s', strtotime('-1 day')));
        
        // Exclude messages where I am the sender? No, we don't know who "I" am in CLI.
        // But usually we don't notify the sender. The receiver_id is who gets notified.
        // We only care about sending to receiver_id.
        
        $builder->orderBy('created_at', 'ASC');
        $builder->limit(20); // Process in batches
        
        $messages = $builder->get()->getResultArray();
        
        if (empty($messages)) {
            // Quietly exit if nothing to do
            return;
        }
        
        CLI::write('Found ' . count($messages) . ' messages to notify.', 'green');

        foreach ($messages as $msg) {
            $senderName = 'System';
            $url = '/chat';
            $senderId = $msg['sender_id'];

            // Try to resolve sender name
            if ($senderId) {
                $user = $db->table('users')->select('name')->where('id_code', $senderId)->get()->getRowArray();
                if ($user) {
                    $senderName = $user['name'];
                    $url = '/chat?user_id=' . $senderId;
                } else {
                    // Check if group?
                    // If sender is group.. but sender is usually a user.
                    // If message is in group chat (receiver_id = GROUP_ALL)
                    // Then we need to notify ALL users except sender.
                }
            }
            
            // Logic for Group Chat
            if ($msg['receiver_id'] === 'GROUP_ALL') {
                 // We need to notify all users except sender
                 // PushService expects a single receiverId.
                 // We should iterate over all users.
                 
                 // Getting all users
                 $users = $db->table('users')->select('id_code')->where('id_code !=', $senderId)->get()->getResultArray();
                 
                 $successAny = false;
                 foreach($users as $u) {
                     // For group chat, URL should probably be the group chat
                     // Or just /chat?user_id=GROUP_ALL
                     // Wait, frontend handles GROUP_ALL as user_id?
                     // Chat.php: getUsers() -> id_code = GROUP_ALL
                     
                     $groupUrl = '/chat?user_id=GROUP_ALL';
                     $groupTitle = "Grup: " . $senderName;
                     
                     // We use sendNotification logic
                     // But sendNotification implementation fetches subscriptions for one user.
                     // So we call it for each user.
                     $sent = $pushService->sendNotification($u['id_code'], $msg['message'], $groupTitle, $groupUrl);
                     if ($sent) $successAny = true;
                 }
                 
                 // Mark as sent if we at least tried.
                 // In group context, "notification_sent" means "processed".
                 $db->table('chats')->where('id', $msg['id'])->update(['notification_sent' => 1]);
                 
            } else {
                // Personal Chat
                $sent = $pushService->sendNotification($msg['receiver_id'], $msg['message'], $senderName, $url);
                
                // Always mark as sent to avoid loops, even if failure (logs will show error)
                // Or maybe only if sent? If we fail, we might want to retry?
                // But if it's a permanent failure (no keys, no sub), we will loop forever.
                // Better to mark as sent.
                
                $db->table('chats')->where('id', $msg['id'])->update(['notification_sent' => 1]);
                
                if ($sent) {
                    CLI::write("Sent notification for msg #{$msg['id']} to {$msg['receiver_id']}", 'cyan');
                } else {
                    CLI::write("Failed/Skipped msg #{$msg['id']} to {$msg['receiver_id']}", 'red');
                }
            }
        }
    }
}

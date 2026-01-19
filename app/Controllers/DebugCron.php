<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\PushService; // Service yang sudah FIX
use Config\Database;

class DebugCron extends BaseController
{
    public function index()
    {
        // Security: Prevent public access in production if desired
        // if (env('CI_ENVIRONMENT') !== 'development') die('Access Denied');

        echo "<h1>Debug Cron Job Logic</h1>";
        echo "<pre>";

        $db = Database::connect();
        $pushService = new PushService();
        $logger = \Config\Services::logger();

        // 1. Cek Parameter Waktu
        $lookback = '-30 days'; 
        $timeLimit = date('Y-m-d H:i:s', strtotime($lookback));
        echo "Time Limit (Lookback $lookback): " . $timeLimit . "\n";
        echo "Server Time: " . date('Y-m-d H:i:s') . "\n";

        // 2. Cek Jumlah Pesan Pending
        // Gunakan Logic Query yang SAMA PERSIS dengan Command
        $builder = $db->table('chats');
        $builder->where('notification_sent', 0);
        $builder->where('created_at >=', $timeLimit); // Pesan 30 hari terakhir
        $builder->where('created_at <', date('Y-m-d H:i:s', strtotime('-1 minute'))); // Beri jeda 1 menit (agar tidak tabrakan dengan realtime)
        $builder->orderBy('created_at', 'ASC');
        
        // Debug: Tampilkan Query
        echo "\nRunning Query...\n";
        $pendingMessages = $builder->get()->getResultArray();
        echo "Found: " . count($pendingMessages) . " pending messages.\n";

        if (empty($pendingMessages)) {
            echo "No messages to process. (System is clean or query mismatch?)\n";
            echo "Try sending a message and turning off internet receiver to create a pending one.\n";
            echo "</pre>";
            return;
        }

        // 3. Process Messages
        $processed = 0;
        foreach ($pendingMessages as $msg) {
            echo "\nProcessing Msg ID: " . $msg['id'] . " | Sender: " . $msg['sender_id'] . "\n";
            
            $senderId = $msg['sender_id'];
            $receiverId = $msg['receiver_id'];
            $messageContent = $msg['message'];
            
            // Get Sender Name
            $senderName = 'System';
            $url = '/chat';

            if ($senderId === 'SYSTEM') {
                $senderName = 'Informasi Sistem';
                $url = '/chat'; 
            } else {
                $user = $db->table('users')->select('name')->where('id_code', $senderId)->get()->getRowArray();
                if ($user) {
                    $senderName = $user['name'];
                } 
                $url = '/chat?user_id=' . $senderId;
            }
            
            echo "  Sender Name: $senderName\n";
            echo "  Target URL: $url\n";
            
            // Logic Group (Sederhana)
            if ($receiverId === 'GROUP_ALL') {
                 echo "  Skipping Group Message logic for simpler debug (focus on personal first).\n";
                 continue;
            }

            // ACTION: Send Notification
            echo "  Sending Push via PushService...\n";
            // Gunakan library yang 'sudah bisa'
            $success = $pushService->sendNotification($receiverId, $messageContent, $senderName, $url);
            
            if ($success) {
                echo "  RESULT: SUCCESS. Marking DB as sent.\n";
                $update = $db->table('chats')->where('id', $msg['id'])->update(['notification_sent' => 1]);
                echo "  DB Update: " . ($update ? "OK" : "FAILED") . "\n";
                $processed++;
            } else {
                echo "  RESULT: FAILED. Check Logs.\n";
            }
        }

        echo "\nTotal Processed Legacy: $processed\n";
        echo "</pre>";
    }
}

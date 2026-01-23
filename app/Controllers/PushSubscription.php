<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Config\Database;

class PushSubscription extends ResourceController
{
    protected $format = 'json';

    public function subscribe()
    {
        $json = $this->request->getJSON();
        
        if (!$json || !isset($json->endpoint)) {
            return $this->fail('Invalid subscription data', 400);
        }
        
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->failUnauthorized('User not logged in');
        }
        
        $userId = $session->get('id_code'); // Assuming 'id_code' matches users table
        
        $db = Database::connect();
        $builder = $db->table('push_subscriptions');
        
        // Check if endpoint exists
        $exists = $builder->where('endpoint', $json->endpoint)->countAllResults();
        
        if ($exists) {
            // Update timestamp
            $builder->where('endpoint', $json->endpoint)
                    ->update(['updated_at' => date('Y-m-d H:i:s'), 'user_id' => $userId]);
            return $this->respond(['status' => 'updated']);
        } else {
            // Insert
            $data = [
                'user_id' => $userId,
                'endpoint' => $json->endpoint,
                'p256dh'   => $json->keys->p256dh ?? null,
                'auth'     => $json->keys->auth ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $builder->insert($data);
            return $this->respondCreated(['status' => 'subscribed']);
        }
    }
    public function unsubscribeAll()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
             return $this->failUnauthorized('User not logged in');
        }
        
        $userId = $session->get('id_code');
        $db = Database::connect();
        
        // NUCLEAR OPTION: Delete ALL subscriptions for this user
        $db->table('push_subscriptions')->where('user_id', $userId)->delete();
        
        return $this->respond(['status' => 'all_deleted', 'message' => 'Semua notifikasi berhasil di-reset.']);
    }

    public function subscribe_fcm()
    {
        $json = $this->request->getJSON();
        
        if (!$json || !isset($json->token)) {
            return $this->fail('Invalid FCM registration data', 400);
        }
        
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->failUnauthorized('User not logged in');
        }
        
        $userId = $session->get('id_code');
        $token = $json->token;
        $deviceType = $json->device_type ?? 'web';
        
        $db = Database::connect();
        $builder = $db->table('fcm_subscriptions');
        
        // Check if token already exists for this user
        $exists = $builder->where('fcm_token', $token)->where('user_id', $userId)->countAllResults();
        
        if ($exists) {
            $builder->where('fcm_token', $token)->where('user_id', $userId)
                    ->update(['updated_at' => date('Y-m-d H:i:s')]);
            return $this->respond(['status' => 'updated']);
        } else {
            $data = [
                'user_id' => $userId,
                'fcm_token' => $token,
                'device_info' => $deviceType,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $builder->insert($data);
            return $this->respondCreated(['status' => 'subscribed']);
        }
    }

    public function check_fcm()
    {
        $json = $this->request->getJSON();
        if (!$json || !isset($json->token)) {
            return $this->respond(['subscribed' => false]);
        }
        
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->respond(['subscribed' => false]);
        }
        
        $userId = $session->get('id_code');
        $token = $json->token;
        
        $db = Database::connect();
        $exists = $db->table('fcm_subscriptions')
                     ->where('fcm_token', $token)
                     ->where('user_id', $userId)
                     ->countAllResults();
                     
        return $this->respond(['subscribed' => $exists > 0]);
    }

    public function debugTokens()
    {
        $db = Database::connect();
        $rows = $db->table('fcm_subscriptions')->get()->getResultArray();
        return $this->respond($rows);
    }

    public function testPush()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
             return $this->respond(['error' => 'Not logged in']);
        }
        
        $userId = $session->get('id_code');
        $db = Database::connect();
        $subs = $db->table('fcm_subscriptions')->where('user_id', $userId)->get()->getResultArray();
        
        $pushService = new \App\Libraries\PushService();
        $accessToken = $pushService->getFCMAccessToken();
        
        // --- DIAGNOSTICS ---
        $jsonFileName = 'jimpitan-app-a7by777-firebase-adminsdk-fbsvc-bd65b27251.json';
        $jsonPath = ROOTPATH . $jsonFileName;
        
        $diagnostics = [
            'root_path' => ROOTPATH,
            'expected_file' => $jsonPath,
            'file_exists' => file_exists($jsonPath),
            'openssl_working' => function_exists('openssl_sign'),
            'json_files_found_in_root' => glob(ROOTPATH . '*.json')
        ];
        // -------------------

        $results = [];
        if ($accessToken && !empty($subs)) {
            foreach ($subs as $sub) {
                $token = $sub['fcm_token'];
                $fcmUrl = 'https://fcm.googleapis.com/v1/projects/jimpitan-app-a7by777/messages:send';
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => ['title' => 'Test Debug Controller', 'body' => 'Bismillah bunyi!'],
                        'data' => ['url' => '/chat']
                    ]
                ];

                $ch = curl_init($fcmUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                $results[] = [
                    'token_prefix' => substr($token, 0, 10),
                    'http_code' => $httpCode,
                    'response' => json_decode($response, true)
                ];
            }
        }
        
        return $this->respond([
            'user_id' => $userId,
            'access_token_status' => $accessToken ? 'success' : 'failed',
            'error_detail' => $accessToken ? null : $pushService->getLastError(),
            'diagnostics' => $diagnostics,
            'results' => $results
        ]);
    }
}

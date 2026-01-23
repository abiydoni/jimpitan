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
}

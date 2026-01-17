<?php

if (!function_exists('log_activity')) {
    function log_activity($action, $description = null)
    {
        $session = session();
        $db      = \Config\Database::connect();
        
        $data = [
            'user_id'     => $session->get('id_code') ?? null, // Can be null for login failures or pre-login
            'username'    => $session->get('user_name') ?? 'GUEST',
            'role'        => $session->get('role') ?? 'GUEST',
            'action'      => $action,
            'description' => $description,
            'ip_address'  => service('request')->getIPAddress(),
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        $db->table('tb_logs')->insert($data);
    }
}

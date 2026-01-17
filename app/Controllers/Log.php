<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Log extends BaseController
{
    public function index()
    {
        $session = session();
        $role = $session->get('role');

        if ($role !== 's_admin' && $role !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        
        // Pagination & Search
        $keyword = $this->request->getGet('q');
        $model = new \App\Models\LogModel();
        
        if ($keyword) {
            $model->groupStart()
                ->like('username', $keyword)
                ->orLike('action', $keyword)
                ->orLike('description', $keyword)
            ->groupEnd();
        }

        $model->orderBy('created_at', 'DESC');
        $logs = $model->limit(500)->findAll();

        return view('logs/index', [
            'title' => 'Log Aktivitas System',
            'profil' => $profil,
            'logs' => $logs,
            'keyword' => $keyword
        ]);
    }
}

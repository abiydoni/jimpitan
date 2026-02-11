<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class KK extends BaseController
{
    /**
     * Display the main page for Data KK
     */
    public function index()
    {
        // Check Access
        if (session()->get('role') !== 's_admin' && session()->get('role') !== 'admin' && !$this->hasMenuAccess('kk')) {
             return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        
        $role = session()->get('role');
        
        // Define who can manage (Create/Update/Delete)
        // Admin OR (Has Access AND Not View Only)
        $accessType = $this->getMenuAccessType('kk');
        $isViewOnly = ($accessType === 'view');
        $canManage = in_array($role, ['s_admin', 'admin']) || (!$isViewOnly && $this->hasMenuAccess('kk'));

        // Fetch all data
        $dataKK = $db->table('master_kk')
                     ->orderBy('kk_name', 'ASC')
                     ->get()
                     ->getResultArray();

        return view('kk', [
            'profil' => $profil,
            'dataKK' => $dataKK,
            'canManage' => $canManage,
            'isViewOnly' => $isViewOnly,
            'role' => $role
        ]);
    }

    /**
     * Store new KK data
     */
    public function store()
    {
        if (!$this->_checkAccess()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses.']);
        }

        $rules = [
            'code_id' => 'required|is_unique[master_kk.code_id]',
            'kk_name' => 'required',
            'nikk'    => 'required|is_unique[master_kk.nikk]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Validasi gagal.',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $db = \Config\Database::connect();
        $data = [
            'code_id' => $this->request->getPost('code_id'),
            'nikk'    => $this->request->getPost('nikk'),
            'kk_name' => $this->request->getPost('kk_name')
        ];

        if ($db->table('master_kk')->insert($data)) {
            log_activity('CREATE_KK', 'Created KK: ' . $data['kk_name'] . ' (' . $data['nikk'] . ')');
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data KK berhasil ditambahkan.']);
        }
    }

    /**
     * Update existing KK data
     */
    public function update()
    {
        if (!$this->_checkAccess()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses.']);
        }

        $id = $this->request->getPost('id'); // Primary Key (id or code_id distinct?)
        
        $rules = [
            'code_id' => 'required', // Unique check excluded for self (complex in CI4 validation string, easier manually if code changed)
            'kk_name' => 'required',
            'nikk'    => 'required'
        ];
        
        if (!$this->validate($rules)) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Validasi gagal.']);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('master_kk');
        
        // Data to update
        $updateData = [
            'code_id' => $this->request->getPost('code_id'),
            'nikk'    => $this->request->getPost('nikk'),
            'kk_name' => $this->request->getPost('kk_name')
        ];

        // Check for duplicate code_id if changed
        // We'll trust the ID passed from form.
        
        $builder->where('id', $id);
        if ($builder->update($updateData)) {
            log_activity('UPDATE_KK', 'Updated KK ID: ' . $id);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data KK berhasil diperbarui.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui data.']);
    }

    /**
     * Delete KK data
     */
    public function delete()
    {
        if (!$this->_checkAccess()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses.']);
        }

        $id = $this->request->getPost('id');
        $db = \Config\Database::connect();
        
        if ($db->table('master_kk')->where('id', $id)->delete()) {
            log_activity('DELETE_KK', 'Deleted KK ID: ' . $id);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data KK berhasil dihapus.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }

    private function _checkAccess()
    {
        $role = session()->get('role');
        if (in_array($role, ['s_admin', 'admin'])) return true;
        
        // Check dynamic access
        if ($this->hasMenuAccess('kk') && $this->getMenuAccessType('kk') !== 'view') {
            return true;
        }
        
        return false;
    }

    /**
     * AJAX Search for Warga
     */
    public function searchWarga()
    {
        $request = service('request');
        $q = $request->getGet('q');

        if (!$q) return $this->response->setJSON([]);

        $db = \Config\Database::connect();
        
        // Improved Search:
        // 1. Find matching rows (Member or Head)
        // 2. Join to get Head of Family name for context
        $queryStr = "
            SELECT 
                w.nikk,
                w.nama as found_name,
                w.hubungan as found_rel,
                h.nama as head_name
            FROM tb_warga w
            LEFT JOIN tb_warga h ON w.nikk = h.nikk AND h.hubungan = 'Kepala Keluarga'
            WHERE (w.nikk LIKE ? OR w.nama LIKE ?) 
              AND w.nikk != ''
            GROUP BY w.nikk
            ORDER BY h.nama ASC
            LIMIT 50
        ";

        $results = $db->query($queryStr, ["%$q%", "%$q%"])->getResultArray();
        
        $data = array_map(function($row) {
            // Determine label
            $head = $row['head_name'] ?: 'Tanpa Kepala Keluarga';
            $found = $row['found_name'];
            $rel = $row['found_rel'];
            
            // If the found person IS the head, just show Head
            // If searching NIKK, implies head context usually
            // If found person is different, show "Head (via Member)"
            
            if ($found === $head || $rel === 'Kepala Keluarga') {
                $text = $row['nikk'] . ' - ' . $head;
                $displayCheck = $head;
            } else {
                $text = $row['nikk'] . ' - ' . $head . ' (' . $found . ')';
                $displayCheck = $head . ' (' . $found . ')';
            }

            return [
                'value' => $row['nikk'],
                'text'  => $text,
                'nama'  => $displayCheck
            ];
        }, $results);

        return $this->response->setJSON($data);
    }
}

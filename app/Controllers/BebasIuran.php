<?php

namespace App\Controllers;

class BebasIuran extends BaseController
{
    public function index()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || ($session->get('role') !== 's_admin' && $session->get('role') !== 'admin')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        
        // Fetch exemptions with joins
        $exemptions = $db->table('tb_bebas_iuran')
            ->select('tb_bebas_iuran.id, tb_bebas_iuran.nikk, tb_bebas_iuran.kode_tarif, tb_bebas_iuran.created_at, master_kk.kk_name, tb_tarif.nama_tarif')
            ->join('master_kk', 'master_kk.nikk = tb_bebas_iuran.nikk', 'left')
            ->join('tb_tarif', 'tb_tarif.kode_tarif = tb_bebas_iuran.kode_tarif', 'left')
            ->orderBy('tb_bebas_iuran.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Fetch Tariffs for dropdown
        $tariffs = $db->table('tb_tarif')->where('status', 1)->get()->getResultArray();

        return view('bebas_iuran/index', [
            'profil' => $profil,
            'title' => 'Kelola Bebas Iuran',
            'exemptions' => $exemptions,
            'tariffs' => $tariffs
        ]);
    }

    public function store()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || ($session->get('role') !== 's_admin' && $session->get('role') !== 'admin')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $nikk = $this->request->getPost('nikk');
        $kode_tarif = $this->request->getPost('kode_tarif');

        if (!$nikk || !$kode_tarif) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        $db = \Config\Database::connect();
        
        // Check uniqueness
        $exists = $db->table('tb_bebas_iuran')
            ->where('nikk', $nikk)
            ->where('kode_tarif', $kode_tarif)
            ->countAllResults();

        if ($exists > 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data sudah ada']);
        }

        $data = [
            'nikk' => $nikk,
            'kode_tarif' => $kode_tarif
        ];

        if ($db->table('tb_bebas_iuran')->insert($data)) {
            log_activity('CREATE_BEBAS_IURAN', 'Added Exemption for NIKK: ' . $nikk . ' Tariff: ' . $kode_tarif);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data']);
    }

    public function delete()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || ($session->get('role') !== 's_admin' && $session->get('role') !== 'admin')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id');
        $db = \Config\Database::connect();

        if ($db->table('tb_bebas_iuran')->where('id', $id)->delete()) {
            log_activity('DELETE_BEBAS_IURAN', 'Deleted Exemption ID: ' . $id);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil dihapus']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data']);
    }

    // Helper for Select2/TomSelect search
    public function searchWarga() 
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON([]);

        $q = $this->request->getGet('q');
        $db = \Config\Database::connect();
        
        $data = $db->table('master_kk')
            ->select('nikk, kk_name')
            ->like('kk_name', $q)
            ->orLike('nikk', $q)
            ->limit(20)
            ->get()
            ->getResultArray();
            
        return $this->response->setJSON($data);
    }
}

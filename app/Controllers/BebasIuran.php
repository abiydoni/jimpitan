<?php

namespace App\Controllers;

class BebasIuran extends BaseController
{
    public function index()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Check Dynamic Access (respects tb_pengurus_menu)
        $access = $this->getMenuAccessType('bebas_iuran');
        if (!$access) {
             return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        
        // --- START TARIFF FILTER LOGIC ---
        $allowedCodes = [];
        $userId = session()->get('id_code');
        $userTarif = session()->get('tarif') ?? 0;
        
        // 1. Admin / Super Admin view all
        if ($userTarif == 100 || session()->get('role') == 's_admin' || session()->get('role') == 'admin') {
             // Allowed all
        } else {
            // 2. Check Pengurus Assignment
            $pengurus = $db->table('tb_pengurus')->where('id', $userTarif)->get()->getRowArray();
            if (!$pengurus) {
                 $pengurus = $db->table('tb_pengurus')->where('nama_pengurus', session()->get('role'))->get()->getRowArray();
            }

            if($pengurus && !empty($pengurus['kode_tarif'])) {
                 $allowedCodes[] = $pengurus['kode_tarif'];
            }
            elseif ($pengurus) {
                 // Check tb_pengurus_menu for granular access
                 // We look for 'akses_tarif' in ANY menu assignment for this pengurus, 
                 // OR specifically for this menu if we want to be strict. 
                 // Let's use the same logic as Keuangan.php: gather ALL accessible tariffs from all menus.
                 $assignments = $db->table('tb_pengurus_menu')
                    ->where('id_pengurus', $pengurus['id'])
                    ->like('akses_tarif', ',', 'both') // Simple check
                    ->orWhere('id_pengurus', $pengurus['id'])
                    ->get()->getResultArray();

                foreach ($assignments as $asm) {
                    if (!empty($asm['akses_tarif'])) {
                        $ids = explode(',', $asm['akses_tarif']);
                        // Convert IDs to Codes
                        if(!empty($ids)) {
                             $tList = $db->table('tb_tarif')->whereIn('id', $ids)->get()->getResultArray();
                             foreach($tList as $tl) $allowedCodes[] = $tl['kode_tarif'];
                        }
                    }
                }
            }
            
            // 3. Fallback (Direct assignment in users table if not 100)
            if (empty($allowedCodes) && $userTarif > 0 && $userTarif != 100 && !$pengurus) {
                 $t = $db->table('tb_tarif')->where('id', $userTarif)->get()->getRowArray();
                 if($t) $allowedCodes[] = $t['kode_tarif'];
            }
        }
        
        // Fetch Tariffs based on filter
        $tariffBuilder = $db->table('tb_tarif')->where('status', 1);
        if (!empty($allowedCodes)) {
            $allowedCodes = array_unique($allowedCodes);
            $tariffBuilder->whereIn('kode_tarif', $allowedCodes);
        } elseif (!($userTarif == 100 || session()->get('role') == 's_admin' || session()->get('role') == 'admin')) {
            // If not admin and no allowed codes found -> Empty
            $tariffBuilder->where('1=0');
        }
        $tariffs = $tariffBuilder->get()->getResultArray();
        // --- END TARIFF FILTER LOGIC ---


        // Fetch exemptions with joins, filtered by allowed tariffs if strict
        $exemptionBuilder = $db->table('tb_bebas_iuran')
            ->select('tb_bebas_iuran.id, tb_bebas_iuran.nikk, tb_bebas_iuran.kode_tarif, tb_bebas_iuran.created_at, master_kk.kk_name, tb_tarif.nama_tarif')
            ->join('master_kk', 'master_kk.nikk = tb_bebas_iuran.nikk', 'left')
            ->join('tb_tarif', 'tb_tarif.kode_tarif = tb_bebas_iuran.kode_tarif', 'left')
            ->orderBy('tb_bebas_iuran.created_at', 'DESC');
            
        if (!empty($allowedCodes)) {
             $exemptionBuilder->whereIn('tb_bebas_iuran.kode_tarif', $allowedCodes);
        } elseif (!($userTarif == 100 || session()->get('role') == 's_admin' || session()->get('role') == 'admin')) {
             $exemptionBuilder->where('1=0');
        }
        
        $exemptions = $exemptionBuilder->get()->getResultArray();

        return view('bebas_iuran/index', [
            'profil' => $profil,
            'title' => 'Kelola Bebas Iuran',
            'exemptions' => $exemptions,
            'tariffs' => $tariffs,
            'isViewOnly' => ($access === 'view')
        ]);
    }

    public function store()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        // Check Write Access
        $access = $this->getMenuAccessType('bebas_iuran');
        if ($access !== 'full') {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki akses tulis.']);
        }

        $nikk = $this->request->getPost('nikk');
        $kode_tarif = $this->request->getPost('kode_tarif');

        if (!$nikk || !$kode_tarif) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        try {
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
        } catch (\Throwable $e) {
            log_message('error', '[BebasIuran::store] ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data']);
    }

    public function delete()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $access = $this->getMenuAccessType('bebas_iuran');
        if ($access !== 'full') {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki akses hapus.']);
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
    public function update()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        // Check Write Access
        $access = $this->getMenuAccessType('bebas_iuran');
        if ($access !== 'full') {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki akses tulis.']);
        }

        $id = $this->request->getPost('id');
        $nikk = $this->request->getPost('nikk');
        $kode_tarif = $this->request->getPost('kode_tarif');

        if (!$id || !$nikk || !$kode_tarif) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        try {
            $db = \Config\Database::connect();
            
            // Check uniqueness excluding current ID
            $exists = $db->table('tb_bebas_iuran')
                ->where('nikk', $nikk)
                ->where('kode_tarif', $kode_tarif)
                ->where('id !=', $id)
                ->countAllResults();

            if ($exists > 0) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Data sudah ada']);
            }

            $data = [
                'nikk' => $nikk,
                'kode_tarif' => $kode_tarif
            ];

            if ($db->table('tb_bebas_iuran')->where('id', $id)->update($data)) {
                log_activity('UPDATE_BEBAS_IURAN', 'Updated Exemption ID: ' . $id . ' New NIKK: ' . $nikk . ' Tariff: ' . $kode_tarif);
                return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil diperbarui']);
            }
        } catch (\Throwable $e) {
            log_message('error', '[BebasIuran::update] ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui data']);
    }
}


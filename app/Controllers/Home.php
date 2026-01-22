<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        // Prevent caching of the dashboard
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');

        $session = session();
        if ($session->get('isLoggedIn') && (!$session->get('name') || !$session->get('role') || !$session->get('shift'))) {
            $model = new \App\Models\UserModel();
            $user = $model->find($session->get('id_code'));
            if ($user) {
                if ($user['name']) $session->set('name', $user['name']);
                if ($user['role']) $session->set('role', $user['role']);
                if ($user['shift']) $session->set('shift', $user['shift']);
            }
            
            if (!$session->get('name')) {
                $session->set('name', $session->get('user_name'));
            }
        }
        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        
        $role = session()->get('role');
        $menus = $db->table('tb_menu')
                    ->where('status', 1)
                    ->groupStart()
                        ->where("FIND_IN_SET('$role', role_access) >", 0)
                        ->orWhere('role_access', '')
                        ->orWhere('role_access', null)
                    ->groupEnd()
                    ->orderBy('nama', 'ASC')
                    ->get()
                    ->getResultArray();

        // Filter Menu based on Tarif Logic
        $userId = session()->get('id_code');
        $user = $db->table('users')->select('tarif')->where('id_code', $userId)->get()->getRowArray();
        $userTarif = $user['tarif'] ?? 0;

        // Get Valid Tarif IDs
        $validTarifIds = $db->table('tb_tarif')->select('id')->get()->getResultArray();
        $validIds = array_column($validTarifIds, 'id');

        $menus = array_filter($menus, function($m) use ($userTarif, $validIds) {
            $url = $m['alamat_url'];
            $isJurnalSub = strpos($url, 'jurnal_sub') !== false;
            $isJurnalUmum = strpos($url, 'jurnal_umum') !== false;

            // Only filter Jurnal menus
            if (!$isJurnalSub && !$isJurnalUmum) return true;

            // Rule 1: Tarif 100 (Super Admin) - Show Both
            if ($userTarif == 100) return true;

            // Rule 2: Tarif 99 (Bendahara Umum) - Only Show Jurnal Umum
            if ($userTarif == 99) {
                if ($isJurnalSub) return false;
                return true;
            }

            // Rule 3: Valid Specific Tarif - Show Jurnal Sub, Hide Jurnal Umum (Refined Logic)
            if (in_array($userTarif, $validIds)) {
                if ($isJurnalUmum) return false; // Specific tariff users focus on their Sub Journal
                return true;
            }

            // Rule 4: Invalid/Unknown Tarif - Hide Both
            return false;
        });

        // Get Bill Data
        $billData = $this->_getBillData();

        // Get Notification Logs (Personal Activity)
        $userId = session()->get('id_code');
        $myLogs = $db->table('tb_logs')
                     ->where('user_id', $userId)
                     ->orderBy('created_at', 'DESC')
                     ->limit(5)
                     ->get()
                     ->getResultArray();

        return view('welcome_message', array_merge([
            'profil' => $profil,
            'menus'  => $menus,
            'myLogs' => $myLogs,
        ], $billData));
    }

    public function bill_details()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        
        // Get Bill Data
        $billData = $this->_getBillData();

        return view('bill_details', array_merge([
            'profil' => $profil,
            'title' => 'Rincian Tagihan'
        ], $billData));
    }

    private function _getBillData()
    {
        $db = \Config\Database::connect();
        $userId = session()->get('id_code');
        
        $currentYear = date('Y');
        $lastYear = $currentYear - 1;

        // Fetch user payments
        $totalPaid = 0;
        $userPayments = [];

        if ($userId) {
            // Find NIKK from users table directly
            $user = $db->table('users')
                       ->select('nikk')
                       ->where('id_code', $userId)
                       ->get()
                       ->getRowArray();
            
            $targetNikk = $user ? $user['nikk'] : null;

            // Verify if NIKK exists in master_kk
            if ($targetNikk) {
                $existsInMaster = $db->table('master_kk')->where('nikk', $targetNikk)->countAllResults();
                if ($existsInMaster > 0) {
                    $showBill = true;
                    
                    $userPayments = $db->table('tb_iuran')
                        ->select('tb_iuran.*, tb_tarif.nama_tarif')
                        ->join('tb_tarif', 'tb_tarif.kode_tarif = tb_iuran.kode_tarif', 'left')
                        ->where('nikk', $targetNikk)
                        ->whereIn('tahun', [$lastYear, $currentYear])
                        ->where('tahun >=', 2026) // Billing starts from 2026
                        ->orderBy('tb_tarif.nama_tarif', 'ASC')
                        ->orderBy('tgl_bayar', 'DESC')
                        ->get()
                        ->getResultArray();
                    
                    foreach ($userPayments as $p) {
                        $totalPaid += $p['jml_bayar'];
                    }
                }
            }
        }
        
        // Group payments by Tariff Name
        $groupedPayments = [];
        foreach ($userPayments as $p) {
            $groupName = $p['nama_tarif'] ?? 'Lain-lain';
            $groupedPayments[$groupName][] = $p;
        }


            // Fetch active tariffs
        $tariffs = $db->table('tb_tarif')
            ->where('status', 1)
            ->get()
            ->getResultArray();

        // New Logic: Fetch Exemptions
        $exemptions = [];
        if ($targetNikk) {
            $exResult = $db->table('tb_bebas_iuran')
                ->select('kode_tarif')
                ->where('nikk', $targetNikk)
                ->get()
                ->getResultArray();
            $exemptions = array_column($exResult, 'kode_tarif');
        }

        $totalObligation = 0;
        $billDetails = []; 

        foreach ($tariffs as $t) {
            $nominal = $t['tarif'];
            $method = $t['metode'];
            $name = $t['nama_tarif'];
            $code = $t['kode_tarif'];
            
            // Skip if exempted
            if (in_array($code, $exemptions)) continue;

            if ($method == 0) continue; 

            // Helper to sum payments for this tariff and year
            $getPaid = function($year) use ($userPayments, $code) {
                $sum = 0;
                foreach ($userPayments as $p) {
                    if ($p['kode_tarif'] == $code && $p['tahun'] == $year) {
                        $sum += $p['jml_bayar'];
                    }
                }
                return $sum;
            };

            if ($method == 1) { // Bulanan
                // Last Year
                if ($lastYear >= 2026) {
                    $amountLastYear = $nominal * 12;
                    $paidLastYear = $getPaid($lastYear);
                    $remLastYear = max(0, $amountLastYear - $paidLastYear);
                    
                    $totalObligation += $amountLastYear;
                    $billDetails[] = [
                        'item' => "$name $lastYear (12 Bulan)",
                        'amount' => $amountLastYear,
                        'paid' => $paidLastYear,
                        'remaining' => $remLastYear
                    ];
                }
                
                // This Year (Full 12 months)
                if ($currentYear >= 2026) {
                    $amountThisYear = $nominal * 12;
                    $paidThisYear = $getPaid($currentYear);
                    $remThisYear = max(0, $amountThisYear - $paidThisYear);

                    $totalObligation += $amountThisYear;
                    $billDetails[] = [
                        'item' => "$name $currentYear (12 Bulan)",
                        'amount' => $amountThisYear,
                        'paid' => $paidThisYear,
                        'remaining' => $remThisYear
                    ];
                }

            } elseif ($method == 2) { // Tahunan
                // Last Year
                if ($lastYear >= 2026) {
                    $paidLastYear = $getPaid($lastYear);
                    $remLastYear = max(0, $nominal - $paidLastYear);

                    $totalObligation += $nominal;
                    $billDetails[] = [
                        'item' => "$name $lastYear",
                        'amount' => $nominal,
                        'paid' => $paidLastYear,
                        'remaining' => $remLastYear
                    ];
                }
                
                // This Year
                if ($currentYear >= 2026) {
                    $paidThisYear = $getPaid($currentYear);
                    $remThisYear = max(0, $nominal - $paidThisYear);

                    $totalObligation += $nominal;
                    $billDetails[] = [
                        'item' => "$name $currentYear",
                        'amount' => $nominal,
                        'paid' => $paidThisYear,
                        'remaining' => $remThisYear
                    ];
                }

            } elseif ($method == 3) { // Satu Kali
                 if ($currentYear >= 2026) {
                     $paidThisYear = $getPaid($currentYear);
                     $remThisYear = max(0, $nominal - $paidThisYear);

                     $totalObligation += $nominal;
                     $billDetails[] = [
                        'item' => "$name",
                        'amount' => $nominal,
                        'paid' => $paidThisYear,
                        'remaining' => $remThisYear
                    ];
                 }
            }
        }

        $bill = $totalObligation - $totalPaid;
        // Ensure bill is not negative
        if ($bill < 0) $bill = 0; 

        return [
            'bill' => $bill,
            'totalObligation' => $totalObligation,
            'paid' => $totalPaid, // Only correct if we fetch payments correctly
            'billDetails' => $billDetails,
            'groupedPayments' => $groupedPayments,
            'currentYear' => $currentYear,
            'showBill' => $showBill ?? false
        ];
    }

    public function jadwal_jaga(): string
    {
        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        $role = session()->get('role');
        $canManage = ($role === 's_admin' || $role === 'admin');
        
        $users = $db->table('users')
                    ->select('id_code, name, user_name, shift')
                    ->where('shift !=', '-')
                    ->where('shift !=', '')
                    ->where('shift IS NOT NULL')
                    ->orderBy('FIELD(shift, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")')
                    ->get()
                    ->getResultArray();

        $jadwal = [];
        foreach ($users as $u) {
            $jadwal[$u['shift']][] = [
                'id' => $u['id_code'],
                'name' => $u['name'] ?: $u['user_name']
            ];
        }

        return view('jadwal_jaga', [
            'profil' => $profil,
            'jadwal' => $jadwal,
            'canManage' => $canManage
        ]);
    }

    public function getUsersForJadwal()
    {
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();
        $users = $db->table('users')
                    ->select('id_code as id, name, shift')
                    ->orderBy('name', 'ASC')
                    ->get()
                    ->getResultArray();

        return $this->response->setJSON($users);
    }

    public function updateJadwal()
    {
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id_code');
        $shift = $this->request->getPost('shift'); // 'Monday', 'Tuesday', or '-' to remove

        $model = new \App\Models\UserModel();
        if ($model->update($id, ['shift' => $shift])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Jadwal berhasil diperbarui']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui jadwal']);
    }

    public function users()
    {
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return redirect()->to('/');
        }

        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        $roles = $db->table('tb_role')->get()->getResultArray();
        
        $model = new \App\Models\UserModel();
        // Join with master_kk to get linked resident name
        $users = $model->select('users.*, master_kk.kk_name as linked_kk_name')
                       ->join('master_kk', 'master_kk.nikk = users.nikk', 'left')
                       ->orderBy('users.name', 'ASC')
                       ->findAll();

        $tarifs = $db->table('tb_tarif')->orderBy('nama_tarif', 'ASC')->get()->getResultArray();

        return view('users', [
            'profil' => $profil,
            'users' => $users,
            'roles' => $roles,
            'tarifs' => $tarifs,
            'currentUserRole' => $role,
            'roleWeights' => $this->_getRoleWeights()
        ]);
    }

    private function _getRoleWeights()
    {
        return [
            's_admin'  => 100,
            'admin'    => 80,
            'pengurus' => 60,
            'user'     => 40,
            'warga'    => 20
        ];
    }

    private function _checkHierarchy($targetRole)
    {
        $myRole = session()->get('role');
        $weights = $this->_getRoleWeights();
        
        $myWeight = $weights[$myRole] ?? 0;
        $targetWeight = $weights[$targetRole] ?? 0;

        // Special rule for s_admin: can manage peers (other s_admins)
        if ($myRole === 's_admin') {
            return $myWeight >= $targetWeight;
        }

        return $myWeight > $targetWeight;
    }

    public function storeUser()
    {
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $targetRole = $this->request->getPost('role');
        $weights = $this->_getRoleWeights();
        if (($weights[$role] ?? 0) < ($weights[$targetRole] ?? 0)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak bisa memberikan role yang lebih tinggi dari diri sendiri']);
        }

        $rules = [
            'user_name' => 'required|is_unique[users.user_name]',
            'name'      => 'required',
            'password'  => 'required|min_length[4]',
            'role'      => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Validasi gagal', 'errors' => $this->validator->getErrors()]);
        }

        $model = new \App\Models\UserModel();
        $data = [
            'user_name' => $this->request->getPost('user_name'),
            'name'      => $this->request->getPost('name'),
            'password'  => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'      => $targetRole,
            'shift'     => $this->request->getPost('shift') ?: '-',
            'nikk'      => $this->request->getPost('nikk') ?: null, // Optional NIKK
            'tarif'     => $this->request->getPost('tarif') ?: 0
        ];

        if ($model->insert($data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'User berhasil ditambahkan']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menambahkan user']);
    }

    public function updateUser()
    {
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id_code');
        $model = new \App\Models\UserModel();
        $existingUser = $model->find($id);

        if (!$existingUser) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User tidak ditemukan']);
        }

        // Hierarchy check for existing user
        if (!$this->_checkHierarchy($existingUser['role']) && $existingUser['id_code'] != session()->get('id_code')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki otoritas untuk mengedit user dengan role setara atau lebih tinggi']);
        }

        // Hierarchy check for target role
        $targetRole = $this->request->getPost('role');
        $weights = $this->_getRoleWeights();
        if (($weights[$role] ?? 0) < ($weights[$targetRole] ?? 0)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak bisa memberikan role yang lebih tinggi dari diri sendiri']);
        }

        $rules = [
            'name'      => 'required',
            'role'      => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Validasi gagal', 'errors' => $this->validator->getErrors()]);
        }

        $data = [
            'name'      => $this->request->getPost('name'),
            'role'      => $targetRole,
            'shift'     => $this->request->getPost('shift') ?: '-',
            'nikk'      => $this->request->getPost('nikk') ?: null, // Optional NIKK
            'tarif'     => $this->request->getPost('tarif') ?: 0
        ];

        if ($this->request->getPost('password')) {
            $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        if ($model->update($id, $data)) {
            log_activity('UPDATE_USER', 'Updated user: ' . $existingUser['user_name']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'User berhasil diperbarui']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui user']);
    }

    public function deleteUser()
    {
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id_code');
        if ($id == session()->get('id_code')) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak bisa menghapus akun sendiri']);
        }

        $model = new \App\Models\UserModel();
        $existingUser = $model->find($id);

        if (!$existingUser) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User tidak ditemukan']);
        }

        // Hierarchy check
        if (!$this->_checkHierarchy($existingUser['role'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki otoritas untuk menghapus user dengan role setara atau lebih tinggi']);
        }
        
        // Use explicit where clause to avoid database restriction
        if ($model->where('id_code', $id)->delete()) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'User berhasil dihapus']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus user']);
    }
    public function profile()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        $userId = session()->get('id_code');
        
        $user = $db->table('users')->where('id_code', $userId)->get()->getRowArray();
        $familyMembers = [];
        $kkData = null;

        if ($user && !empty($user['nikk'])) {
            // Get Family Members using NIKK
            $familyMembers = $db->table('tb_warga')
                                ->where('nikk', $user['nikk'])
                                ->orderBy('hubungan', 'ASC') // Kepala Keluarga usually first if sorted alpha or by logic, but let's just get list
                                ->get()
                                ->getResultArray();
            
            // Get KK Data for address/context if needed
            $kkData = $db->table('master_kk')->where('nikk', $user['nikk'])->get()->getRowArray();
        }

        // Get Current User's Warga Data (for photo)
        $userWarga = null;
        if ($user && !empty($user['nikk'])) {
             // Match by NIKK and Name to find the specific person
             $userWarga = $db->table('tb_warga')
                            ->where('nikk', $user['nikk'])
                            ->like('nama', $user['name'], 'both') 
                            ->get()
                            ->getRowArray();
        }

        // Get Head of Family Data (for photo)
        $headWarga = null;
        if ($user && !empty($user['nikk'])) {
             // Find the head of family
             $headWarga = $db->table('tb_warga')
                            ->where('nikk', $user['nikk'])
                            ->like('hubungan', 'kepala', 'both') // Contains "Kepala"
                            ->get()
                            ->getRowArray();
        }

        return view('profile', [
            'profil' => $profil,
            'user' => $user,
            'familyMembers' => $familyMembers,
            'kkData' => $kkData,
            'userWarga' => $userWarga,
            'headWarga' => $headWarga // Pass to view
        ]);
    }
    public function updateMemberPhoto()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $idWarga = $this->request->getPost('id_warga');
        $file = $this->request->getFile('foto');

        if (!$file || !$file->isValid()) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak valid']);
        }

        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Format file harus gambar (jpg, png, webp)']);
        }

        if (!$file->hasMoved()) {
            $newName = $file->getRandomName();
            
            // Database Connect
            $db = \Config\Database::connect();
            
            // Security check: Ensure this warga belongs to the logged-in user's family (NIKK check)
            $userId = session()->get('id_code');
            $user = $db->table('users')->select('nikk')->where('id_code', $userId)->get()->getRowArray();
            $userNikk = $user['nikk'] ?? '';

            if (empty($userNikk)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Akun anda tidak terhubung dengan data warga.']);
            }

            // Get target warga
            $targetWarga = $db->table('tb_warga')->where('id_warga', $idWarga)->get()->getRowArray();
            
            if (!$targetWarga || $targetWarga['nikk'] !== $userNikk) {
                 return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak berhak mengubah data ini.']);
            }
            
            // Move file
            try {
                $file->move(FCPATH . 'img/warga', $newName);
                
                // Robust Compression
                $this->_compressImage($newName);

            } catch (\Exception $e) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan file ke server.']);
            }

            // Delete old photo if exists
            if (!empty($targetWarga['foto']) && file_exists(FCPATH . 'img/warga/' . $targetWarga['foto'])) {
                unlink(FCPATH . 'img/warga/' . $targetWarga['foto']);
            }

            // Update DB
            $db->table('tb_warga')->where('id_warga', $idWarga)->update(['foto' => $newName]);

            // Update Session if this is the logged-in user's photo
            $currentUserWarga = $db->table('tb_warga')->where('nik', function($builder) use ($userId) {
                $builder->select('nik')->from('users')->where('id_code', $userId);
            })->get()->getRowArray();

            // We can also check if $idWarga matches the id_warga linked to the current user
            // Since we established $userNikk check, we can just check if this specific updated Id matches the session's 'id_warga'
            // But session might not have id_warga stored directly or reliably?
            // Let's rely on retrieving the user's data again or just updating if it matches.
            
            // Simpler: Check if the updated ID matches the one associated with the user account
            // We already fetched $userNikk. Let's find the warga for this user.
            // Actually, in Home::profil we see: $userWarga = $userModel->getWargaData($userId);
            // Let's just assume we want to update the session 'foto' if the updated record corresponds to the `user_name` or `id_code`.
            
            // Update Session if this is the logged-in user's photo
            $session = session();
            
            // Check if the updated record belongs to the logged-in user
            // We compare the name of the updated Warga with the name in the session
            // This relies on the assumption that session('name') is consistent with tb_warga.nama
            if ($targetWarga && $targetWarga['nama'] === $session->get('name')) {
                $session->set('foto', $newName);
            }

            return $this->response->setJSON([
                'status' => 'success', 
                'message' => 'Foto berhasil diperbarui',
                'new_photo' => $newName
            ]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengupload foto']);
    }

    private function _compressImage($fileName)
    {
        if (!extension_loaded('gd')) return;

        try {
            $path = FCPATH . 'img/warga/' . $fileName;
            \Config\Services::image()
                ->withFile($path)
                ->resize(800, 800, true, 'auto')
                ->save($path, 80);
        } catch (\Exception $e) {
            // Fail silently, keeping original file
        }
    }
}

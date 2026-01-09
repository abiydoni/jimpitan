<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
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

        // Bill Calculation Logic
        $currentYear = date('Y');
        $lastYear = $currentYear - 1;
        $currentMonth = date('n');

        // Fetch user payments
        $totalPaid = 0;
        $userPayments = [];
        if (session()->get('id_code')) {
            $userPayments = $db->table('tb_iuran')
                ->where('nikk', session()->get('id_code'))
                ->whereIn('tahun', [$lastYear, $currentYear])
                ->where('tahun >=', 2026) // Billing starts from 2026
                ->get()
                ->getResultArray();
            
            foreach ($userPayments as $p) {
                $totalPaid += $p['jml_bayar'];
            }
        }

        // Fetch active tariffs
        $tariffs = $db->table('tb_tarif')
            ->where('status', 1)
            ->get()
            ->getResultArray();

        $totalObligation = 0;
        $billDetails = []; 

        foreach ($tariffs as $t) {
            $nominal = $t['tarif'];
            $method = $t['metode'];
            $name = $t['nama_tarif'];
            
            if ($method == 0) continue; 

            if ($method == 1) { // Bulanan
                // Last Year
                if ($lastYear >= 2026) {
                    $amountLastYear = $nominal * 12;
                    $totalObligation += $amountLastYear;
                    $billDetails[] = [
                        'item' => "$name $lastYear (12 Bulan)",
                        'amount' => $amountLastYear
                    ];
                }
                
                // This Year (Full 12 months as per request)
                if ($currentYear >= 2026) {
                    $amountThisYear = $nominal * 12;
                    $totalObligation += $amountThisYear;
                    $billDetails[] = [
                        'item' => "$name $currentYear (12 Bulan)",
                        'amount' => $amountThisYear
                    ];
                }

            } elseif ($method == 2) { // Tahunan
                // Last Year
                if ($lastYear >= 2026) {
                    $totalObligation += $nominal;
                    $billDetails[] = [
                        'item' => "$name $lastYear",
                        'amount' => $nominal
                    ];
                }
                
                // This Year
                if ($currentYear >= 2026) {
                    $totalObligation += $nominal;
                    $billDetails[] = [
                        'item' => "$name $currentYear",
                        'amount' => $nominal
                    ];
                }

            } elseif ($method == 3) { // Satu Kali
                 // Assumption: 'Satu Kali' also respects the start year rule?
                 // If it's a one-time thing, maybe we just show it for the current year.
                 // Simplification: Showing it if current year >= 2026.
                 if ($currentYear >= 2026) {
                     $totalObligation += $nominal;
                     $billDetails[] = [
                        'item' => "$name",
                        'amount' => $nominal
                    ];
                 }
            }
        }

        $bill = $totalObligation - $totalPaid;
        // Ensure bill is not negative (if paid more than obligation) - optionally could show minus as credit
        if ($bill < 0) $bill = 0; 

        return view('welcome_message', [
            'profil' => $profil,
            'menus'  => $menus,
            'bill'   => $bill,
            'billDetails' => $billDetails,
            'userPayments' => $userPayments,
            'totalObligation' => $totalObligation,
            'totalPaid' => $totalPaid
        ]);
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
        $users = $model->orderBy('name', 'ASC')->findAll();

        return view('users', [
            'profil' => $profil,
            'users' => $users,
            'roles' => $roles,
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
            'shift'     => $this->request->getPost('shift') ?: '-'
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
            'shift'     => $this->request->getPost('shift') ?: '-'
        ];

        if ($this->request->getPost('password')) {
            $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        if ($model->update($id, $data)) {
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
}

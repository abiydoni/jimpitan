<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Scan extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // Fetch Jimpitan Tariff (TR001) for context if needed, though UI is changing
        $tariff = $db->table('tb_tarif')->where('kode_tarif', 'TR001')->get()->getRowArray();
        
        // Count Today's Scans & Total Nominal
        $today = date('Y-m-d');
        $query = $db->table('report')
                    ->selectCount('id', 'count')
                    ->selectSum('nominal', 'total')
                    ->where('jimpitan_date', $today)
                    ->get()
                    ->getRowArray();
        
        $scanCount = $query['count'];
        $totalNominal = $query['total'] ?? 0;

        // Fetch Profil for Title
        $profil = $db->table('tb_profil')->get()->getRowArray();
        $profilName = $profil['nama'] ?? 'Jimpitan';

        return view('scan/index', [
            'tariff' => $tariff,
            'scanCount' => $scanCount,
            'totalNominal' => $totalNominal,
            'title' => "Scan $profilName"
        ]);
    }

    // Endpoint for Realtime Detail List
    public function getRecentScans()
    {
        $db = \Config\Database::connect();
        $request = service('request');
        $today = $request->getGet('date') ?? date('Y-m-d');
        
        $data = $db->table('report')
                   ->select('report.id, report.nominal, report.collector, report.scan_time, master_kk.kk_name as nama_warga')
                   ->join('master_kk', 'master_kk.code_id = report.report_id', 'left')
                   ->where('report.jimpitan_date', $today)
                   ->orderBy('report.scan_time', 'DESC')
                   ->get()
                   ->getResultArray();

        // Calculate Stats
        $count = count($data);
        $total = array_sum(array_column($data, 'nominal'));

        // Format data for easier JS consumption
        $formatted = array_map(function($row) {
            return [
                'id' => $row['id'],
                'nama' => $row['nama_warga'] ?? 'Warga Tidak Dikenal', // Fallback if join fails
                'nominal' => $row['nominal'],
                'collector' => $row['collector'],
                'waktu' => date('H:i', strtotime($row['scan_time']))
            ];
        }, $data);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $formatted,
            'count' => $count,
            'total_nominal' => $total
        ]);
    }

    public function store()
    {
        $request = service('request');
        $codeId = $request->getJsonVar('code_id');

        if (!$codeId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'QR Code tidak valid.']);
        }

        $db = \Config\Database::connect();

        // 1. Validate QR Code against Master KK
        $warga = $db->table('master_kk')->where('code_id', $codeId)->get()->getRowArray();

        if (!$warga) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data KK tidak ditemukan.']);
        }

        // 1.5 Validate User Shift (Jadwal Jaga) & Handle Post-Midnight Logic
        $userId = session()->get('id_code');
        $userRole = session()->get('role'); // Get role from session
        
        $today = date('Y-m-d'); // Default to actual today

        // Only enforce shift validation if NOT Admin/Super Admin
        if ($userRole !== 's_admin' && $userRole !== 'admin') {
            $user = $db->table('users')->select('shift')->where('id_code', $userId)->get()->getRowArray();
            
            if ($user && $user['shift'] !== '-') {
                $currentDay = date('l'); // Monday...

                // Logic:
                // STRICT MATCH: Shift MUST equal Current Day.
                
                if ($user['shift'] !== $currentDay) {
                    // Translate for clear error message
                    $days = [
                        'Monday'    => 'Senin',
                        'Tuesday'   => 'Selasa',
                        'Wednesday' => 'Rabu',
                        'Thursday'  => 'Kamis',
                        'Friday'    => 'Jumat',
                        'Saturday'  => 'Sabtu',
                        'Sunday'    => 'Minggu'
                    ];
                    
                    $yourShift = $days[$user['shift']] ?? $user['shift'];
                    $todayName = $days[$currentDay] ?? $currentDay;
                    
                    return $this->response->setJSON([
                        'status' => 'error', 
                        'message' => "Gagal! Jadwal jaga Anda hari $yourShift, sekarang hari $todayName. Silahkan hubungi admin."
                    ]);
                }
            }
        }

        // 2. Get Nominal (Server Validated)
        $tariff = $db->table('tb_tarif')->where('kode_tarif', 'TR001')->get()->getRowArray();
        $nominal = $tariff['tarif'] ?? 500;

        // 3. Check for Existing Record Today (Using Adjusted or Actual $today)

        // NOTE: User requested 'report_id' to hold the Resident Code (master_kk.code_id)
        // so we must check for duplicates using 'report_id' now, not 'kode_u'.
        $existing = $db->table('report')
                       ->where('report_id', $warga['code_id']) 
                       ->where('jimpitan_date', $today)
                       ->get()
                       ->getRowArray();

        if ($existing) {
            // Check if client confirmed deletion
            $confirmDelete = $request->getJsonVar('confirm_delete');

            if ($confirmDelete === true) {
                // Perform Deletion
                try {
                    $db->table('report')->where('id', $existing['id'])->delete();
                    log_activity('DELETE_JIMPITAN', 'Deleted Jimpitan for Report ID: ' . $warga['code_id']);
                    return $this->response->setJSON([
                        'status' => 'deleted',
                        'message' => 'Data jimpitan dihapus.',
                        'data' => [
                            'nama' => $warga['kk_name'],
                            'nominal' => $nominal
                        ]
                    ]);
                } catch (\Exception $e) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data: ' . $e->getMessage()]);
                }
            } else {
                // Return Confirmation Request
                 return $this->response->setJSON([
                    'status' => 'confirm_delete',
                    'message' => 'Data sudah ada. Hapus data ini?',
                    'data' => [
                        'nama' => $warga['kk_name'],
                        'nominal' => $nominal
                    ]
                ]);
            }
        }

        // 4. Insert into Report if Not Exists
        // User Request Mapping:
        // report_id -> master_kk.code_id (Resident Code)
        // kode_u    -> users.id_code (Officer ID)
        // nama_u    -> users.name (Officer Name)
        // collector -> users.user_name (Officer Username)
        
        $data = [
            'report_id'     => $warga['code_id'],
            'jimpitan_date' => $today,
            'nominal'       => $nominal,
            'collector'     => session()->get('user_name'),
            'kode_u'        => session()->get('id_code'),
            'nama_u'        => session()->get('name'),

            'alasan'        => '-',
            'status'        => 1,
            'scan_time'     => date('Y-m-d H:i:s')
        ];

        try {
            $db->table('report')->insert($data);
            log_activity('SCAN_JIMPITAN', 'Scanned Jimpitan for: ' . $warga['kk_name'] . ' (' . $warga['code_id'] . ')');
            return $this->response->setJSON([
                'status' => 'success', 
                'message' => 'Jimpitan berhasil dicatat!',
                'data' => [
                    'nama' => $warga['kk_name'],
                    'nominal' => $nominal,
                    'waktu' => date('H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }
    // Manual Entry Page
    public function manual()
    {
        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        return view('jimpitan_manual', [
            'profil' => $profil,
            'title' => 'Input Manual - ' . ($profil['nama'] ?? 'Jimpitan')
        ]);
    }

    public function today()
    {
        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        return view('scan_today', [
            'profil' => $profil,
            'title' => 'Scan Hari Ini - ' . ($profil['nama'] ?? 'Jimpitan')
        ]);
    }

    // Process Manual Entry
    public function storeManual()
    {
        $request = service('request');
        $codeId = $request->getPost('code_id');
        $date   = $request->getPost('jimpitan_date');
        $alasan = $request->getPost('alasan');

        if (!$codeId || !$date) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data warga dan tanggal harus diisi.']);
        }

        if (empty($alasan)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Alasan wajib diisi untuk input manual.']);
        }

        $db = \Config\Database::connect();
        
        // 1. Validate Code
        $warga = $db->table('master_kk')->where('code_id', $codeId)->get()->getRowArray();
        if (!$warga) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data KK tidak valid.']);
        }

        // 2. Get Nominal
        $tariff = $db->table('tb_tarif')->where('kode_tarif', 'TR001')->get()->getRowArray();
        $nominal = $tariff['tarif'] ?? 500;

        // 3. Check Duplicate
        $existing = $db->table('report')
                       ->where('report_id', $codeId)
                       ->where('jimpitan_date', $date)
                       ->get()
                       ->getRowArray();

        if ($existing) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Data jimpitan untuk warga ini pada tanggal ' . $date . ' sudah ada.']);
        }

        // 4. Insert (Override User with System)
        $data = [
            'report_id'     => $codeId,
            'jimpitan_date' => $date,
            'nominal'       => $nominal,
            'collector'     => 'System', // Hardcoded as requested
            'kode_u'        => 'SYSTEM',
            'nama_u'        => 'System',
            'alasan'        => $alasan,
            'status'        => 1,
            'scan_time'     => date('Y-m-d H:i:s')
        ];

        try {
            $db->table('report')->insert($data);
            log_activity('MANUAL_SCAN_JIMPITAN', 'Manual Jimpitan for: ' . $warga['kk_name'] . ' Date: ' . $date);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data jimpitan manual berhasil disimpan.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }
    }

    // Search Target (Master KK) for Manual Entry
    public function searchTarget()
    {
        $request = service('request');
        $q = $request->getGet('q');

        if (!$q) return $this->response->setJSON([]);

        $db = \Config\Database::connect();
        
        // Search master_kk Table
        $results = $db->table('master_kk')
             ->like('kk_name', $q)
             ->orLike('code_id', $q)
             ->orLike('nikk', $q)
             ->limit(20)
             ->get()
             ->getResultArray();

        $data = array_map(function($row) {
            return [
                'value' => $row['code_id'],
                'nikk'  => $row['nikk'], // Added NIKK field
                'nama'  => $row['kk_name'],
                'text'  => $row['kk_name'] . ' (' . $row['code_id'] . ')'
            ];
        }, $results);

        return $this->response->setJSON($data);
    }

    // --- Not Scanned Feature ---
    
    public function notScanned()
    {
        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        
        return view('scan/not_scanned', [
            'title' => 'Belum Scan Hari Ini',
            'profil' => $profil
        ]);
    }

    public function getNotScannedJson()
    {
        $request = service('request');
        $date = $request->getGet('date') ?? date('Y-m-d');
        
        $db = \Config\Database::connect();
        
        // Logic: Get All Warga NOT IN Report(date)
        // Subquery approach
        $subquery = $db->table('report')
                       ->select('report_id')
                       ->where('jimpitan_date', $date);
                       
        // Build Main Query
        $notScanned = $db->table('master_kk')
                         ->whereNotIn('code_id', $subquery)
                         ->orderBy('kk_name', 'ASC')
                         ->get()
                         ->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'date' => $date,
            'count' => count($notScanned),
            'data' => $notScanned
        ]);
    }

    // --- Leaderboard Feature ---
    public function leaderboard()
    {
        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        
        // Ranking Query: ALL TIME (status=1)
        // No month filter, No limit
        $rankings = $db->table('report')
                       ->select('nama_u, COUNT(id) as total_scan')
                       ->where('status', 1) 
                       ->groupBy('nama_u')
                       ->orderBy('total_scan', 'DESC')
                       ->get() // Get ALL
                       ->getResultArray();

        return view('scan/leaderboard', [
            'title' => 'Top Petugas Scan',
            'profil' => $profil,
            'rankings' => $rankings,
            'month' => 'Semua Waktu' // Changed label
        ]);
    }

    public function resetLeaderboard()
    {
        $session = session();
        $db = \Config\Database::connect();
        
        // 1. Role Check
        if ($session->get('role') !== 'admin') {
             return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Akses ditolak. Hanya Admin yang bisa mereset.'
            ]);
        }

        // 2. Password Check
        $password = $this->request->getPost('password');
        if (!$password) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Password diperlukan.'
            ]);
        }

        $userId = $session->get('id_code'); // Use correct ID session key
        // Verify against DB user
        $user = $db->table('tb_user')->where('id_code', $userId)->get()->getRowArray();

        if (!$user || !password_verify($password, $user['password'])) {
             return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Password salah!'
            ]);
        }

        // 3. Execution (Soft Delete / Hide)
        // Set status = 0 for all active records
        $db->table('report')->where('status', 1)->update(['status' => 0]);
        log_activity('RESET_LEADERBOARD', 'Leaderboard reset by: ' . $session->get('user_name'));

        return $this->response->setJSON([
            'status' => 'success', 
            'message' => 'Leaderboard berhasil di-reset.'
        ]);
    }
}

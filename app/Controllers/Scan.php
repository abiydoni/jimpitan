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
        $today = date('Y-m-d');
        
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

        // 2. Get Nominal (Server Validated)
        $tariff = $db->table('tb_tarif')->where('kode_tarif', 'TR001')->get()->getRowArray();
        $nominal = $tariff['tarif'] ?? 500;

        // 3. Check for Existing Record Today
        $today = date('Y-m-d');
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
            'scan_time'     => date('Y-m-d H:i:s')
        ];

        try {
            $db->table('report')->insert($data);
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
}

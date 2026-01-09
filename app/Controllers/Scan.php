<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Scan extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // Fetch Jimpitan Tariff (TR001)
        $tariff = $db->table('tb_tarif')->where('kode_tarif', 'TR001')->get()->getRowArray();
        
        return view('scan/index', [
            'tariff' => $tariff,
            'title' => 'Scan QR Jimpitan'
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
        $existing = $db->table('report')
                       ->where('kode_u', $warga['code_id'])
                       ->where('jimpitan_date', $today)
                       ->get()
                       ->getRowArray();

        if ($existing) {
            // Logic: Undo/Delete if already scanned today
            try {
                $db->table('report')->where('id', $existing['id'])->delete();
                return $this->response->setJSON([
                    'status' => 'deleted',
                    'message' => 'Data jimpitan dibatalkan/dihapus.',
                    'data' => [
                        'nama' => $warga['kk_name'],
                        'nominal' => $nominal
                    ]
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data: ' . $e->getMessage()]);
            }
        }

        // 4. Insert into Report if Not Exists
        $data = [
            'report_id'     => 'RPT-' . date('YmdHis') . '-' . rand(100, 999),
            'jimpitan_date' => $today,
            'nominal'       => $nominal,
            'collector'     => session()->get('name') ?? 'System',
            'kode_u'        => $warga['code_id'],
            'nama_u'        => $warga['kk_name'],
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

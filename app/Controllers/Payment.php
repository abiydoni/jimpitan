<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TarifModel;
use App\Models\IuranModel;
use App\Models\MasterKkModel; // Assuming this exists or using equivalent query
use App\Models\WargaModel;

class Payment extends BaseController
{
    protected $tarifModel;
    protected $iuranModel;
    protected $wargaModel;
    protected $kasSubModel;
    protected $db;

    public function __construct()
    {
        $this->tarifModel = new TarifModel();
        $this->iuranModel = new IuranModel();
        $this->wargaModel = new WargaModel();
        $this->kasSubModel = new \App\Models\KasSubModel();
        $this->db = \Config\Database::connect();
        
        // Ensure coa_code column exists in tb_tarif (Auto-Migration)
        $fields = $this->db->getFieldData('tb_tarif');
        $hasCoa = false;
        foreach($fields as $field) {
            if ($field->name === 'coa_code') {
                $hasCoa = true;
                break;
            }
        }
        
        if (!$hasCoa) {
            $forge = \Config\Database::forge();
            $col = ['coa_code' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'tarif']];
            $forge->addColumn('tb_tarif', $col);
        }
    }

    public function index()
    {
        // 1. Get User Permission
        $userId = session()->get('id_code');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);
        $userTarif = $user['tarif'] ?? 0;

        // 2. Determine Data Source
        $tarifs = [];
        if ($userTarif == 100) {
            $tarifs = $this->tarifModel->where('status', 1)->where('metode !=', 0)->findAll();
        } elseif ($userTarif > 0) {
            $tarifs = $this->tarifModel->where('status', 1)->where('metode !=', 0)->where('id', $userTarif)->findAll();
        }

        $data = [
            'title' => 'Pilih Jenis Pembayaran',
            'tarifs' => $tarifs
        ];
        return view('payment/index', $data);
    }

    public function warga($kode_tarif)
    {
        $tarif = $this->tarifModel->where('kode_tarif', $kode_tarif)->first();
        
        if (!$tarif) {
            return redirect()->to('/payment')->with('error', 'Tarif tidak ditemukan.');
        }

        $builder = $this->db->table('tb_warga');
        $builder->select('tb_warga.*, master_kk.code_id as no_kk');
        $builder->join('master_kk', 'master_kk.code_id = tb_warga.nikk', 'left');
        $builder->where('tb_warga.hubungan', 'Kepala Keluarga');
        $builder->orderBy('tb_warga.nama', 'ASC');
        $wargaList = $builder->get()->getResultArray();

        $exemptions = $this->db->table('tb_bebas_iuran')
            ->where('kode_tarif', $kode_tarif)
            ->get()
            ->getResultArray();
        $exemptNikk = array_column($exemptions, 'nikk');

        $data = [
            'title' => 'Pilih Warga - ' . $tarif['nama_tarif'],
            'tarif' => $tarif,
            'warga_list' => $wargaList,
            'exempt_nikk' => $exemptNikk
        ];

        return view('payment/list_warga', $data);
    }

    public function detail($kode_tarif, $nikk)
    {
        $tarif = $this->tarifModel->where('kode_tarif', $kode_tarif)->first();
        if (!$tarif) return redirect()->to('/payment');

        $warga = $this->db->table('tb_warga')
            ->where('nikk', $nikk)
            ->where('hubungan', 'Kepala Keluarga')
            ->get()->getRowArray();

        if (!$warga) {
             return redirect()->to('/payment/warga/' . $kode_tarif)->with('error', 'Data Warga tidak ditemukan');
        }

        // Ambil tahun dari parameter GET, default ke tahun sekarang
        $year = $this->request->getGet('year') ?? date('Y');
        
        $rawPayments = $this->iuranModel
            ->where('kode_tarif', $kode_tarif)
            ->where('nikk', $nikk)
            ->where('tahun', $year)
            ->findAll();

        $paymentSummary = [];
        $totalYearlyPaid = 0;

        foreach ($rawPayments as $p) {
            $m = $p['bulan'];
            if (!isset($paymentSummary[$m])) $paymentSummary[$m] = 0;
            $paymentSummary[$m] += $p['jumlah'];
            $totalYearlyPaid += $p['jumlah'];
        }

        $data = [
            'title' => 'Detail Pembayaran',
            'tarif' => $tarif,
            'warga' => $warga,
            'rawPayments' => $rawPayments,
            'paymentSummary' => $paymentSummary,
            'totalYearlyPaid' => $totalYearlyPaid,
            'year'  => $year
        ];

        return view('payment/detail', $data);
    }

    public function process()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $json = $this->request->getJSON();
        
        if (!$json->kode_tarif || !$json->nikk || !isset($json->nominal)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        $data = [
            'kode_tarif' => $json->kode_tarif,
            'nikk'       => $json->nikk,
            'bulan'      => $json->bulan,
            'tahun'      => $json->tahun,
            'jumlah'     => $json->nominal,
            'jml_bayar'  => $json->nominal,
            'status'     => 'Lunas', 
            'tgl_bayar'  => date('Y-m-d H:i:s'),
            'keterangan' => 'Dibayar via System',
            'jenis_iuran'=> 'wajib' 
        ];

        // 1. Insert Payment
        $this->iuranModel->insert($data);
        $newId = $this->iuranModel->getInsertID();
        
        // 2. Auto-Journal to Kas Sub
        try {
            $tarif = $this->tarifModel->where('kode_tarif', $json->kode_tarif)->first();
            $warga = $this->wargaModel->where('nikk', $json->nikk)->first(); // Simple fetch for name
            
            // Get Month Name
            $monthName = $json->bulan > 0 ? date("M", mktime(0, 0, 0, $json->bulan, 10)) : 'Tahunan';
            
            $journalData = [
                'date_trx'  => date('Y-m-d'),
                'coa_code'  => $tarif['coa_code'] ?? '', // Fallback empty if not set
                'desc_trx'  => "{$tarif['nama_tarif']} - {$monthName} {$json->tahun} ({$warga['nama']})",
                'debet'     => $json->nominal,
                'kredit'    => 0,
                'reff'      => $tarif['kode_tarif'] . '_AUTO'
            ];
            
            // Only insert journal if nominal > 0
            if ($json->nominal > 0) {
                $this->kasSubModel->insert($journalData);
            }
        } catch (\Exception $e) {
            log_message('error', 'Auto-Journal Failed: ' . $e->getMessage());
            // Proceed without failing the main payment, but log it.
        }

        log_activity('PAYMENT_PROCESSED', 'Processed payment: ' . $json->nominal . ' for NIKK: ' . $json->nikk);
        return $this->response->setJSON(['status' => 'success']);
    }

    public function delete()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $id = $this->request->getPost('id');
        $payment = $this->iuranModel->find($id); // Get Data Before Delete
        
        if($payment && $this->iuranModel->delete($id)) {
            
            // Auto-Journal Reversal (Jurnal Balik)
            try {
                $tarif = $this->tarifModel->where('kode_tarif', $payment['kode_tarif'])->first();
                $warga = $this->wargaModel->where('nikk', $payment['nikk'])->first();
                
                $monthName = $payment['bulan'] > 0 ? date("M", mktime(0, 0, 0, $payment['bulan'], 10)) : 'Tahunan';

                $reversalData = [
                    'date_trx'  => date('Y-m-d'),
                    'coa_code'  => $tarif['coa_code'] ?? '',
                    'desc_trx'  => "Koreksi/Hapus: {$tarif['nama_tarif']} - {$monthName} {$payment['tahun']} ({$warga['nama']})",
                    'debet'     => 0,
                    'kredit'    => $payment['jumlah'], // Reversal: Credit the amount
                    'reff'      => $tarif['kode_tarif'] . '_AUTO'
                ];

                if ($payment['jumlah'] > 0) {
                    $this->kasSubModel->insert($reversalData);
                }
            } catch (\Exception $e) {
                log_message('error', 'Auto-Journal Reversal Failed: ' . $e->getMessage());
            }

            log_activity('DELETE_PAYMENT', 'Deleted Payment ID: ' . $id);
            return $this->response->setJSON(['status' => 'success']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data']);
    }

    public function get_global_summary()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            $year = $this->request->getGet('year') ?? date('Y');
            $month = $this->request->getGet('month'); // Optional

            $db = \Config\Database::connect();
            $builder = $db->table('tb_iuran');
            $builder->select('tb_tarif.nama_tarif, tb_tarif.kode_tarif, SUM(tb_iuran.jumlah) as total, COUNT(tb_iuran.id_iuran) as count');
            $builder->join('tb_tarif', 'tb_tarif.kode_tarif = tb_iuran.kode_tarif');
            
            // Filter by Transaction Year
            $builder->where("YEAR(tb_iuran.tgl_bayar)", $year);
            
            // Filter by Transaction Month if provided
            if ($month) {
                $builder->where("MONTH(tb_iuran.tgl_bayar)", $month);
            }

            $builder->groupBy('tb_iuran.kode_tarif');
            $builder->orderBy('total', 'DESC');
            
            $summary = $builder->get()->getResultArray();

            return $this->response->setJSON($summary);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function get_global_history($kode_tarif)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            $year = $this->request->getGet('year') ?? date('Y');

            // 1. Get Tariff Info
            $tarif = $this->tarifModel->where('kode_tarif', $kode_tarif)->first();
            if (!$tarif) throw new \Exception("Tarif not found");

            $targetAmount = $tarif['tarif'] * ($tarif['metode'] == 1 ? 12 : 1);

            $db = \Config\Database::connect();
            
            // 2. Fetch All Transactions Joined with Warga
            $builder = $db->table('tb_iuran');
            $builder->select('tb_iuran.*, tb_warga.nama');
            $builder->join('tb_warga', 'tb_warga.nikk = tb_iuran.nikk AND tb_warga.hubungan = "Kepala Keluarga"', 'left', false);
            
            $builder->where('tb_iuran.kode_tarif', $kode_tarif);
            $builder->where('YEAR(tb_iuran.tgl_bayar)', $year);
            $builder->orderBy('tb_iuran.tgl_bayar', 'DESC');
            
            $payments = $builder->get()->getResultArray();

            // 3. Group by NIKK
            $grouped = [];
            
            // First pass: Calculate totals per NIKK to determine status
            $totals = [];
            foreach($payments as $p) {
                $nikk = $p['nikk'];
                if(!isset($totals[$nikk])) $totals[$nikk] = 0;
                $totals[$nikk] += $p['jumlah'];
            }

            // Second pass: Build Grouped Structure
            foreach($payments as $p) {
                $nikk = $p['nikk'];
                
                if(!isset($grouped[$nikk])) {
                    $totalPaid = $totals[$nikk];
                    $grouped[$nikk] = [
                        'nikk' => $nikk,
                        'nama' => $p['nama'] ?? 'Warga',
                        'total_paid_year' => $totalPaid,
                        'target_amount' => $targetAmount, // Pass target amount to frontend
                        'is_lunas_tahun' => $totalPaid >= $targetAmount,
                        'transactions' => []
                    ];
                }
                
                $grouped[$nikk]['transactions'][] = $p;
            }

            // Reset keys to return array
            return $this->response->setJSON(array_values($grouped));
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function get_personal_history($kode_tarif, $nikk)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            $year = $this->request->getGet('year') ?? date('Y');

            // Get Tariff Info for Status Calculation
            $tarif = $this->tarifModel->where('kode_tarif', $kode_tarif)->first();
            if (!$tarif) throw new \Exception("Tarif not found");

            $db = \Config\Database::connect();
            $builder = $db->table('tb_iuran');
            $builder->where('kode_tarif', $kode_tarif);
            $builder->where('nikk', $nikk);
            $builder->where('tahun', $year);
            $builder->orderBy('tgl_bayar', 'DESC');
            
            $payments = $builder->get()->getResultArray();

            // Group by Month
            $grouped = [];
            foreach ($payments as $p) {
                $m = $p['bulan']; // 0 for yearly/others
                if (!isset($grouped[$m])) {
                    $grouped[$m] = [
                        'bulan' => $m,
                        'total_bayar' => 0,
                        'transaksi' => []
                    ];
                }
                $grouped[$m]['total_bayar'] += $p['jumlah'];
                $grouped[$m]['transaksi'][] = $p;
            }

            // Calculate Status
            $result = [];
            foreach ($grouped as $m => $data) {
                $isLunas = $data['total_bayar'] >= $tarif['tarif'];
                $data['status'] = $isLunas ? 'Lunas' : 'Belum Lunas';
                $data['kurang'] = $tarif['tarif'] - $data['total_bayar'];
                if ($data['kurang'] < 0) $data['kurang'] = 0;
                
                $result[] = $data;
            }

            // Sort: Monthly desc, then Yearly (0)
            usort($result, function($a, $b) {
                if ($a['bulan'] == 0) return -1; // Keep yearly at top or bottom? usually 0 is separate. Let's desc sort: 12..1, 0.
                if ($b['bulan'] == 0) return 1;
                return $b['bulan'] <=> $a['bulan'];
            });

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}

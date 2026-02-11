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
        
        // Auto-migration removed for performance

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
        
        // Scenario A: Super Admin (Legacy 100) or explicitly 's_admin' role (check session role too if reliable)
        if ($userTarif == 100 || session()->get('role') == 's_admin') {
            $tarifs = $this->tarifModel->where('status', 1)->where('metode !=', 0)->findAll();
        } 
        // Scenario B: Check if User ID maps to a Pengurus
        else {
            // Check tb_pengurus
            $pengurus = $this->db->table('tb_pengurus')->where('id', $userTarif)->get()->getRowArray();
            
            if ($pengurus) {
                // Fetch tariffs assigned to this Pengurus (aggregating from all menus or specific context?)
                // Since this is the "Payment" controller, we ideally want tariffs for 'iuran' menus.
                // But getting *all* assigned tariffs is a safe starting point for the index.
                $assignments = $this->db->table('tb_pengurus_menu')
                    ->where('id_pengurus', $pengurus['id'])
                    ->like('akses_tarif', ',', 'both') // Optimize? No, regex or just get all non-empty
                    ->orWhere('id_pengurus', $pengurus['id']) // Just get all for this pengurus
                    ->get()->getResultArray();

                $allowedIds = [];
                foreach ($assignments as $asm) {
                    if (!empty($asm['akses_tarif'])) {
                        $ids = explode(',', $asm['akses_tarif']);
                        foreach($ids as $tid) $allowedIds[] = trim($tid);
                    }
                }
                
                $allowedIds = array_unique($allowedIds);
                
                if (!empty($allowedIds)) {
                     $tarifs = $this->tarifModel->where('status', 1)
                                                ->where('metode !=', 0)
                                                ->whereIn('id', $allowedIds)
                                                ->findAll();
                }
            } 
            // Scenario C: Legacy direct tariff ID (Fallback)
            elseif ($userTarif > 0) {
                 $tarifs = $this->tarifModel->where('status', 1)->where('metode !=', 0)->where('id', $userTarif)->findAll();
            }
        }

        // 3. Auto-Redirect if only 1 Tariff
        // User requested: "langsung menuju kode tersebut"
        if (count($tarifs) === 1) {
            return redirect()->to('/payment/warga/' . $tarifs[0]['kode_tarif']);
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
        
        // Fetch Profil for View
        $profil = $this->db->table('tb_profil')->get()->getRowArray() ?? [];
        // Use 'payment' or 'transaksi' depending on what code is used in tb_menu
        // Let's assume 'payment' for now or 'iuran'
        // Actually, let's allow if role is admin OR hasMenuAccess('payment')
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin' && !$this->hasMenuAccess('payment')) {
             return redirect()->to('/')->with('error', 'Akses ditolak.');
        }
        
        // Determine View Only status
        $accessType = $this->getMenuAccessType('payment'); // Assuming 'payment' is the code
        $isViewOnly = ($accessType === 'view');

        if (!$tarif) {
            return redirect()->to('/payment')->with('error', 'Tarif tidak ditemukan.');
        }

        // Get User & Tariff for Access Check
        $userId = session()->get('id_code');
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);
        $userTarif = $user['tarif'] ?? 0;
        
        // --- Access Check Logic ---
        // Basic Role Check
        $isPowerUser = ($role === 's_admin' || $role === 'admin');
        
        // If not power user, check if this specific tariff is allowed
        if (!$isPowerUser) {
             // 1. Direct Tariff Assignment (Legacy)
             if ($userTarif == $tarif['id']) {
                 // Allowed
             } else {
                 // 2. Check Pengurus Menu Assignment
                 // We need to check if user has access to THIS tariff specifically via tb_pengurus_menu
                 $db = \Config\Database::connect();
                 
                 // Get Pengurus ID
                 $pengurus = $db->table('tb_pengurus')->where('id', $userTarif)->get()->getRowArray();
                 if (!$pengurus) {
                      $pengurus = $db->table('tb_pengurus')->where('nama_pengurus', $role)->get()->getRowArray();
                 }
                 
                 $hasAccess = false;
                 if ($pengurus) {
                      // Check ALL menu assignments for this pengurus to see if this tariff ID is listed
                      $assignments = $db->table('tb_pengurus_menu')
                         ->where('id_pengurus', $pengurus['id'])
                         ->like('akses_tarif', ',', 'both')
                         ->orWhere('id_pengurus', $pengurus['id'])
                         ->get()->getResultArray();

                      foreach ($assignments as $asm) {
                          if (!empty($asm['akses_tarif'])) {
                              $ids = explode(',', $asm['akses_tarif']);
                              if (in_array($tarif['id'], $ids)) {
                                  $hasAccess = true;
                                  break;
                              }
                          }
                      }
                 }
                 
                 if (!$hasAccess) {
                      return redirect()->to('/payment')->with('error', 'Akses ditolak untuk tarif ini.');
                 }
             }
        }
        
        // Fetch Warga List
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
        // Check if user has access to multiple tariffs to decide Back Button target
        $singleTariff = false;
        if (!$isPowerUser) {
             // Re-check logic similar to index() or simplified
             // If userTarif > 0 (Direct Assign), it's single.
             // If Pengurus, might be multiple. 
             // Ideally we shouldn't repeat the query.
             // Let's just pass a safe guess or do a quick check.
             // Actually, the easiest way is to check the REFERER or just provide a "Home" button for non-admins?
             // No, let's be precise.
             
             if ($userTarif == $tarif['id']) {
                 $singleTariff = true;
             } elseif ($pengurus) {
                  // Check count of assigned tariffs
                  $asmCount = $db->table('tb_pengurus_menu')
                     ->where('id_pengurus', $pengurus['id'])
                     ->like('akses_tarif', ',', 'both')
                     ->orWhere('id_pengurus', $pengurus['id'])
                     ->countAllResults();
                  // This is rough because comma separated. 
                  // Let's assume if not 's_admin'/'admin', they might want to go to Dashboard 
                  // OR we just link to /payment and let the controller handle it?
                  // The controller redirects back. So we MUST link to /.
                  // Let's passed 'backUrl'
             }
        }
        
        // BETTER APPROACH:
        // Always link to `/payment`. 
        // BUT modify `index()` to validly show the list if accessed explicitly? 
        // No, `index()` auto-redirects.
        // So we must direct to `/` if auto-redirect is active.
        
        // Let's Check if we should link to Dashboard
        // If (User has 1 tariff permitted), Back -> Dashboard.
        // If (User has > 1), Back -> Payment List.
        
        // Re-use logic from index() implies code duplication.
        // Quick fix: If not admin, link to `/`. 
        // Wait, some Pengurus manage 2 tariffs (e.g. Sampah & Sosial).
        
        // Let's pass $backUrl
        $backUrl = '/payment';
        if (!$isPowerUser) {
             // Check how many they have
             // We reuse the $assignments logic roughly? 
             // Or just check if direct assignment
             if ($userTarif > 0 && $userTarif != 100 && !$pengurus) {
                 $backUrl = '/';
             }
             // If pengurus, we'd need to know count.
             // Let's cheat: If we are here, and we accidentally go to /payment and get redirected back, it's annoying.
             // Let's providing a "Home" link for everyone is safer? 
             // No, admins need to switch tariffs.
             
             // Let's do the count check properly.
             if ($pengurus) {
                  $allIds = [];
                  $assignments = $db->table('tb_pengurus_menu')
                         ->where('id_pengurus', $pengurus['id'])
                         ->get()->getResultArray();
                  foreach($assignments as $a) {
                      if($a['akses_tarif']) {
                          foreach(explode(',', $a['akses_tarif']) as $i) $allIds[] = $i;
                      }
                  }
                  $allIds = array_unique($allIds);
                  if (count($allIds) <= 1) $backUrl = '/';
             }
        }

        $exemptNikk = array_column($exemptions, 'nikk');

        $data = [
            'profil' => $profil,
            'title' => 'Pilih Warga - ' . $tarif['nama_tarif'],
            'tarif' => $tarif,
            'warga_list' => $wargaList,
            'exempt_nikk' => $exemptNikk,
            'userTarif' => $userTarif,
            'isViewOnly' => $isViewOnly,
            'canManage' => ($isPowerUser || !$isViewOnly),
            'backUrl' => $backUrl
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

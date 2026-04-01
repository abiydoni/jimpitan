<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KasSubModel;
use App\Models\KasUmumModel;
use App\Models\CoaModel;
use App\Models\TarifModel;

class Keuangan extends BaseController
{
    protected $kasSubModel;
    protected $kasUmumModel;
    protected $coaModel;
    protected $tarifModel;
    protected $db;

    public function __construct()
    {
        $this->kasSubModel = new KasSubModel();
        $this->kasUmumModel = new KasUmumModel();
        $this->coaModel = new CoaModel();
        $this->tarifModel = new TarifModel();
        $this->db = \Config\Database::connect();
    }

    private function getCommonData($title)
    {
        $profil = $this->db->table('tb_profil')->get()->getRowArray() ?? [];
        // Use 'id_code' to match the column name in 'users' table
        $user = $this->db->table('users')->where('id_code', session()->get('id_code'))->get()->getRowArray();
        
        return [
            'profil' => $profil,
            'title' => $title,
            'user' => session()->get()
        ];
    }

    public function jurnal_sub()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $data = $this->getCommonData('Arus Kas Khusus'); // Title updated to match previous user edit
        
        try {
            // Get User Tariff Permission
            $userId = session()->get('id_code');
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($userId);
            $userTarif = $user['tarif'] ?? 0;

            $builder = $this->kasSubModel
                ->select('kas_sub.date_trx, kas_sub.reff, kas_sub.coa_code, MAX(kas_sub.sub_reff) as sub_reff, MAX(kas_sub.id_trx) as id_trx, SUM(kas_sub.debet) as debet, SUM(kas_sub.kredit) as kredit, COUNT(kas_sub.id_trx) as count_trx, GROUP_CONCAT(CONCAT(kas_sub.desc_trx, "||", GREATEST(kas_sub.debet, kas_sub.kredit), "||", IFNULL(kas_sub.sub_reff, "")) SEPARATOR ";;;") as detail_trx, max(tb_coa.name) as nama_akun, max(tb_coa.code) as kode_akun, max(tb_tarif.nama_tarif) as nama_tarif')
                ->join('tb_coa', 'tb_coa.code = kas_sub.coa_code', 'left')
                ->join('tb_tarif', 'tb_tarif.kode_tarif = kas_sub.reff', 'left')
                ->groupBy(['kas_sub.date_trx', 'kas_sub.reff', 'kas_sub.coa_code'])
                ->orderBy('date_trx', 'DESC')
                ->orderBy('id_trx', 'DESC');
                
            // Separate builder for calculating totals (ignoring pagination)
            // Use db->table to ensure it's a fresh instance and doesn't conflict with Model's builder
            $totalBuilder = $this->db->table('kas_sub'); 

            // Apply Filter
            $selectedFilter = '';
            
            // Scenario A: Super Admin (Legacy 100) or Role s_admin
            if ($userTarif == 100 || session()->get('role') == 's_admin') {
                $selectedFilter = $this->request->getGet('filter_tarif');
                if ($selectedFilter) {
                     $builder->like('reff', $selectedFilter, 'after');
                     $totalBuilder->like('reff', $selectedFilter, 'after');
                }
            }
            // Scenario B: Check Pengurus Access (Simplify: Use tb_pengurus.kode_tarif)
            else {
                $allowedCodes = [];
                
                // 1. Check if linked to Pengurus ID
                $pengurus = $this->db->table('tb_pengurus')->where('id', $userTarif)->get()->getRowArray();

                // 2. Priority 2: Check by Role Name (fallback) - Align with Home.php logic
                if (!$pengurus) {
                     $pengurus = $this->db->table('tb_pengurus')->where('nama_pengurus', session()->get('role'))->get()->getRowArray();
                }

                if($pengurus && !empty(trim($pengurus['kode_tarif'] ?? ''))) {
                     $allowedCodes[] = trim($pengurus['kode_tarif']);
                }
                // 2. Fallback: If tb_pengurus.kode_tarif is empty, maybe check legacy or menu assignments? 
                // User said "biar gampang" using tb_pengurus.kode_tarif. 
                // But let's keep the menu check as fallback OR just rely on this?
                // Let's rely on this primarily. If empty, maybe no access or full access? 
                // Safe default: If empty kode_tarif, try menu whitelist logic (previous logic) 
                // OR if user implies STRICT usage, we show nothing.
                // Assuming we keep the granular check as fallback ensures we don't break existing setup if kode_tarif is null.
                elseif ($pengurus) {
                     $assignments = $this->db->table('tb_pengurus_menu')
                        ->where('id_pengurus', $pengurus['id'])
                        ->like('akses_tarif', ',', 'both')
                        ->orWhere('id_pengurus', $pengurus['id'])
                        ->get()->getResultArray();

                    $allowedIds = [];
                    foreach ($assignments as $asm) {
                        if (!empty($asm['akses_tarif'])) {
                            $ids = explode(',', $asm['akses_tarif']);
                            foreach($ids as $tid) $allowedIds[] = trim($tid);
                        }
                    }
                    if(!empty($allowedIds)) {
                        $allowedIds = array_unique($allowedIds);
                        $tList = $this->tarifModel->whereIn('id', $allowedIds)->findAll();
                        foreach($tList as $tl) $allowedCodes[] = $tl['kode_tarif'];
                    }
                }
                
                // 3. Legacy Fallback (Specific Tarif ID directly in user table)
                if (empty($allowedCodes) && $userTarif > 0 && !$pengurus) {
                     $t = $this->tarifModel->find($userTarif);
                     if($t) $allowedCodes[] = $t['kode_tarif'];
                }

                // Apply Query Filter
                if (!empty($allowedCodes)) {
                    // Group Start for Multiple LIKE OR
                    $builder->groupStart();
                    $totalBuilder->groupStart();
                    foreach ($allowedCodes as $code) {
                        $builder->orLike('reff', $code, 'after');
                        $totalBuilder->orLike('reff', $code, 'after');
                    }
                    $builder->groupEnd();
                    $totalBuilder->groupEnd();
                    
                    // Filter the available Tariff Dropdown for View
                    $data['tarif'] = $this->tarifModel->whereIn('kode_tarif', $allowedCodes)->findAll();
                } else {
                    // IF no specific restrictions found for this pengurus, ALLOW ALL
                    // (Ensure they have menu access via lower check which we rely on)
                    $data['tarif'] = $this->tarifModel->findAll();
                }
            }

            // CHECK MENU ACCESS TYPE (View Only vs Full)
            // Use the centralized helper from BaseController
            // Note: We need to know the exact menu code. 
            // Based on previous edits, we assumed 'keuangan' or looked up by URL.
            // Let's assume the menu code for "Arus Kas Khusus" is 'jurnal_sub' or find via query if needed.
            // Safe approach: Query tb_menu by URL to get code, then check.
            // CHECK MENU ACCESS TYPE (View Only vs Full)
            // Normalize menu code for permission check
            $menuCode = 'jurnal_sub'; 
            
            // Note: BaseController resolves by kode or alamat_url.

            $accessType = $this->getMenuAccessType($menuCode);

            // If null, it means no access at all. We should redirect rather than giving false hope
            if ($accessType === null) {
                return redirect()->to('/')->with('error', 'Akses ditolak. Anda tidak memiliki akses ke menu ini.');
            }
            $data['isViewOnly'] = ($accessType === 'view');

            // Calculate Totals
            $totals = $totalBuilder->selectSum('debet')->selectSum('kredit')->get()->getRowArray();
            $data['totalDebetAll'] = $totals['debet'] ?? 0;
            $data['totalKreditAll'] = $totals['kredit'] ?? 0;
            $data['saldoAll'] = $data['totalDebetAll'] - $data['totalKreditAll'];

            $data['transaksi'] = $builder->paginate(20);
            $data['pager'] = $this->kasSubModel->pager;
            
        } catch (\Exception $e) {
             $data['error'] = 'Database Error: ' . $e->getMessage();
             $data['transaksi'] = [];
        }

        $data['coa'] = $this->coaModel->findAll();
        // If not already set by specific filter above, load all (for admin)
        if (!isset($data['tarif'])) {
            $data['tarif'] = $this->tarifModel->findAll();
        }

        $data['userTarif'] = $userTarif;
        $data['selectedFilter'] = $selectedFilter ?? '';

        return view('keuangan/jurnal_sub', $data);
    }

    public function jurnal_jimpitan()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $data = $this->getCommonData('Jurnal Jimpitan');
        
        try {
            $builder = $this->kasSubModel
                ->select('kas_sub.date_trx, kas_sub.reff, kas_sub.coa_code, MAX(kas_sub.id_trx) as id_trx, SUM(kas_sub.debet) as debet, SUM(kas_sub.kredit) as kredit, COUNT(kas_sub.id_trx) as count_trx, GROUP_CONCAT(CONCAT(kas_sub.desc_trx, "||", GREATEST(kas_sub.debet, kas_sub.kredit)) SEPARATOR ";;;") as detail_trx, max(tb_coa.name) as nama_akun, max(tb_coa.code) as kode_akun')
                ->join('tb_coa', 'tb_coa.code = kas_sub.coa_code', 'left')
                ->join('tb_setoran_jimpitan', 'tb_setoran_jimpitan.id_kas_sub = kas_sub.id_trx', 'left')
                ->where('kas_sub.reff', 'TR001')
                ->groupBy('IF(tb_setoran_jimpitan.id IS NOT NULL, CAST(kas_sub.date_trx AS CHAR), CONCAT("manual_", kas_sub.id_trx))')
                ->orderBy('date_trx', 'DESC')
                ->orderBy('id_trx', 'DESC');
                
            $totalBuilder = $this->db->table('kas_sub')->where('reff', 'TR001'); 

            $accessType = $this->getMenuAccessType('jurnal_jimpitan');
            $data['isViewOnly'] = ($accessType === 'view');

            // Calculate Totals
            $totals = $totalBuilder->selectSum('debet')->selectSum('kredit')->get()->getRowArray();
            $data['totalDebetAll'] = $totals['debet'] ?? 0;
            $data['totalKreditAll'] = $totals['kredit'] ?? 0;
            $data['saldoAll'] = $data['totalDebetAll'] - $data['totalKreditAll'];

            $data['transaksi'] = $builder->paginate(20, 'jimpitan');
            $data['pager'] = $this->kasSubModel->pager;
            
        } catch (\Exception $e) {
             $data['error'] = 'Database Error: ' . $e->getMessage();
             $data['transaksi'] = [];
        }

        $data['coa'] = $this->coaModel->findAll();
        $data['userTarif'] = session()->get('id_code'); 

        return view('keuangan/jurnal_jimpitan', $data);
    }

    public function get_unsettled_jimpitan()
    {
        if (!is_cli() && !session()->get('isLoggedIn')) return $this->response->setJSON([]);

        $profil = $this->db->table('tb_profil')->get()->getRowArray();
        // Temporarily broaden to see if ANY data exists
        $startDate = !empty($profil['jimpitan_start_date']) ? $profil['jimpitan_start_date'] : '2024-01-01';

        // Use array for whereNotIn to be safe
        $setoran = $this->db->table('tb_setoran_jimpitan')->select('tanggal_jimpitan')->get()->getResultArray();
        $alreadySetor = array_column($setoran, 'tanggal_jimpitan');
        
        $builder = $this->db->table('report')
                    ->select('jimpitan_date as tanggal, SUM(nominal) as total, COUNT(id) as jml_scan')
                    ->where('status', 1)
                    ->where('jimpitan_date >=', $startDate)
                    ->where('nominal >', 0);

        if (!empty($alreadySetor)) {
            $builder->whereNotIn('jimpitan_date', $alreadySetor);
        }

        $dates = $builder->groupBy('jimpitan_date')
                    ->orderBy('jimpitan_date', 'DESC')
                    ->get()
                    ->getResultArray();

        // Result
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $dates
        ]);
    }

    public function setor_jimpitan()
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        
        if ($this->getMenuAccessType('jurnal_jimpitan') !== 'full') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak. Anda hanya memiliki akses lihat.']);
        }

        $tanggal = $this->request->getPost('tanggal');
        if (!$tanggal) return $this->response->setJSON(['status' => 'error', 'message' => 'Tanggal wajib diisi']);

        $existing = $this->db->table('tb_setoran_jimpitan')->where('tanggal_jimpitan', $tanggal)->get()->getRowArray();
        if ($existing) return $this->response->setJSON(['status' => 'error', 'message' => 'Data untuk tanggal ini sudah disetor.']);

        $reportData = $this->db->table('report')
                         ->selectSum('nominal', 'total')
                         ->where('jimpitan_date', $tanggal)
                         ->get()
                         ->getRowArray();
        
        $total = $reportData['total'] ?? 0;
        if ($total <= 0) return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak ada nominal jimpitan untuk disetor pada tanggal tersebut.']);

        $coa = $this->db->table('tb_coa')->like('name', 'Jimpitan')->get()->getRowArray();
        $coaCode = $coa['code'] ?? '41101'; 

        $this->db->transStart();

        $jurnalData = [
            'date_trx' => $tanggal, 
            'coa_code' => $coaCode,
            'desc_trx' => "Setoran Jimpitan Bersih Tgl " . date('d/m/Y', strtotime($tanggal)),
            'debet'    => $total,
            'kredit'   => 0,
            'reff'     => 'TR001'
        ];
        $this->kasSubModel->insert($jurnalData);
        $idTrx = $this->db->insertID();

        $this->db->table('tb_setoran_jimpitan')->insert([
            'tanggal_jimpitan' => $tanggal,
            'total_nominal'    => $total,
            'id_kas_sub'       => $idTrx,
            'created_at'       => date('Y-m-d H:i:s')
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memproses setoran.']);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Setoran jimpitan berhasil diproses.']);
    }


    public function jurnal_umum()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        // Check Menu Access using normalized code
        $accessType = $this->getMenuAccessType('jurnal_umum');
        $isViewOnly = ($accessType === 'view');

        $data = $this->getCommonData('Arus Kas Umum');
        $data['isViewOnly'] = $isViewOnly;
        
        try {
            $data['transaksi'] = $this->kasUmumModel
                ->select('kas_umum.*, tb_coa.name as nama_akun, tb_coa.code as kode_akun')
                ->join('tb_coa', 'tb_coa.code = kas_umum.coa_code', 'left')
                ->orderBy('date_trx', 'DESC')
                ->orderBy('id_trx', 'DESC')
                ->paginate(20);

            $data['pager'] = $this->kasUmumModel->pager;

        } catch (\Exception $e) {
             $data['error'] = 'Database Error: ' . $e->getMessage();
             $data['transaksi'] = [];
        }

        $data['coa'] = $this->coaModel->findAll();

        return view('keuangan/jurnal_umum', $data);
    }

    public function save_sub()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        // Permission Check
        if ($this->getMenuAccessType('jurnal_sub') !== 'full') {
            return redirect()->back()->with('error', 'Akses ditolak. Anda hanya memiliki akses lihat.');
        }

        $rules = [
            'tanggal' => 'required',
            'coa_code' => 'required', // Changed from id_coa
            'nominal' => 'required',
            'jenis' => 'required|in_list[masuk,keluar]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal. Cek inputan anda.');
        }

        $jenis = $this->request->getPost('jenis');
        $nominal = str_replace(['Rp', '.', ' '], '', $this->request->getPost('nominal'));
        
        // Fetch COA if needed to get ID? Schema uses coa_code.
        $coaCode = $this->request->getPost('coa_code');

        $data = [
            'date_trx' => $this->request->getPost('tanggal'),
            'coa_code' => $coaCode,
            'desc_trx' => $this->request->getPost('keterangan'),
            'debet' => ($jenis == 'masuk') ? $nominal : 0,
            'kredit' => ($jenis == 'keluar') ? $nominal : 0,
            'reff'   => $this->request->getPost('kode_tarif')
        ];

        $this->kasSubModel->save($data);
        $redirectUrl = $this->request->getPost('redirect_url') ?: '/keuangan/jurnal_sub';
        return redirect()->to($redirectUrl)->with('success', 'Data berhasil disimpan');
    }


    public function save_umum()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        // Permission Check
        if ($this->getMenuAccessType('jurnal_umum') !== 'full') {
            return redirect()->back()->with('error', 'Akses ditolak. Anda hanya memiliki akses lihat.');
        }

         $rules = [
            'tanggal' => 'required',
            'coa_code' => 'required',
            'nominal' => 'required',
            'jenis' => 'required|in_list[masuk,keluar]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal. Cek inputan anda.');
        }

        $jenis = $this->request->getPost('jenis');
        $nominal = str_replace(['Rp', '.', ' '], '', $this->request->getPost('nominal'));
        $coaCode = $this->request->getPost('coa_code');

        $data = [
            'date_trx' => $this->request->getPost('tanggal'),
            'coa_code' => $coaCode,
            'desc_trx' => $this->request->getPost('keterangan'),
            'debet' => ($jenis == 'masuk') ? $nominal : 0,
            'kredit' => ($jenis == 'keluar') ? $nominal : 0,
        ];

        $this->kasUmumModel->save($data);
        return redirect()->to('/keuangan/jurnal_umum')->with('success', 'Data berhasil disimpan');
    }

    public function hutang_jimpitan()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $data = $this->getCommonData('Hutang Jimpitan');
        
        $search = $this->request->getGet('search');
        $builder = $this->db->table('master_kk');
        
        if ($search) {
            $builder->like('kk_name', $search)->orLike('code_id', $search);
        }
        
        $data['dataKK'] = $builder->orderBy('kk_name', 'ASC')->get()->getResultArray();
        $data['search'] = $search;

        return view('keuangan/hutang_jimpitan_list', $data);
    }

    public function detail_hutang_jimpitan($code_id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $builder = $this->db->table('master_kk');
        $builder->where('code_id', $code_id);
        $kk = $builder->get()->getRowArray();
        
        if (!$kk) return redirect()->to('/keuangan/hutang_jimpitan')->with('error', 'Data KK tidak ditemukan');

        // Fetch Photo from tb_warga
        $warga = $this->db->table('tb_warga')
                          ->where(['nikk' => $kk['nikk'], 'hubungan' => 'Kepala Keluarga'])
                          ->get()->getRowArray();
        $kk['foto'] = $warga['foto'] ?? null;

        $year = $this->request->getGet('year') ?? date('Y');
        $data = $this->getCommonData('Detail Hutang - ' . $kk['kk_name']);
        
        // Get Jimpitan Tariff
        $tarif = $this->db->table('tb_tarif')->where('kode_tarif', 'TR001')->get()->getRowArray();
        $nominalTarif = $tarif['tarif'] ?? 500;

        // Get Jimpitan Start Date
        $profil = $this->db->table('tb_profil')->get()->getRowArray();
        $startDate = $profil['jimpitan_start_date'] ?? '0000-00-00';

        $monthlyData = [];
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');

        for ($m = 1; $m <= 12; $m++) {
            // Precise Range Intersection Logic: [Max(MonthStart, StartDate), Min(MonthEnd, Today)]
            $monthStart = strtotime("$year-$m-01");
            $monthEnd = strtotime(date('Y-m-t', $monthStart));
            $sysStart = strtotime($startDate);
            $yesterday = strtotime('-1 day', strtotime(date('Y-m-d')));

            $effectiveStart = max($monthStart, $sysStart);
            $effectiveEnd = min($monthEnd, $yesterday);

            $target = 0;
            if ($effectiveStart <= $effectiveEnd) {
                $days = ($effectiveEnd - $effectiveStart) / 86400 + 1;
                $target = round($days) * $nominalTarif;
            }

            // Get Scans (from report table)
            $scans = $this->db->table('report')
                        ->where('report_id', $code_id)
                        ->where('YEAR(jimpitan_date)', $year)
                        ->where('MONTH(jimpitan_date)', $m)
                        ->where('status', 1)
                        ->countAllResults();
            $scannedAmount = $scans * $nominalTarif;

            // Get Manual Payments (from tb_iuran)
            $payments = $this->db->table('tb_iuran')
                        ->selectSum('jml_bayar')
                        ->where('nikk', $kk['nikk'])
                        ->where('kode_tarif', 'TR001')
                        ->where('tahun', $year)
                        ->where('bulan', $m)
                        ->get()
                        ->getRowArray();
            $paidAmount = $payments['jml_bayar'] ?? 0;

            $totalPaid = $scannedAmount + $paidAmount;
            $debt = $target - $totalPaid;

            // Logic: Pay button for months that have elapsed OR the current month (daily debt)
            $mStart = strtotime("$year-$m-01");
            $yesterday = strtotime('-1 day', strtotime(date('Y-m-d')));
            $isPast = $mStart <= $yesterday;

            $monthlyData[] = [
                'bulan' => $m,
                'nama_bulan' => date('F', mktime(0, 0, 0, $m, 10)),
                'target' => $target,
                'total_paid' => $totalPaid,
                'paid_manual' => $paidAmount,
                'debt' => $debt > 0 ? $debt : 0,
                'status' => $totalPaid >= $target ? 'Lunas' : 'Hutang',
                'is_past' => $isPast
            ];
        }

        $data['kk'] = $kk;
        $data['year'] = $year;
        $data['monthlyData'] = $monthlyData;
        $data['nominalTarif'] = $nominalTarif;

        return view('keuangan/hutang_jimpitan_detail', $data);
    }

    public function get_hutang_summary($code_id)
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $kk = $this->db->table('master_kk')->where('code_id', $code_id)->get()->getRowArray();
        if (!$kk) return $this->response->setJSON(['status' => 'error', 'message' => 'Data KK tidak ditemukan']);

        $year = $this->request->getGet('year') ?? date('Y');
        
        // Get Jimpitan Tariff
        $tarif = $this->db->table('tb_tarif')->where('kode_tarif', 'TR001')->get()->getRowArray();
        $nominalTarif = $tarif['tarif'] ?? 500;

        // Get Jimpitan Start Date
        $profil = $this->db->table('tb_profil')->get()->getRowArray();
        $startDate = $profil['jimpitan_start_date'] ?? '0000-00-00';

        $monthlyData = [];
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');

        for ($m = 1; $m <= 12; $m++) {
            // Precise Range Intersection Logic: [Max(MonthStart, StartDate), Min(MonthEnd, Today)]
            $monthStart = strtotime("$year-$m-01");
            $monthEnd = strtotime(date('Y-m-t', $monthStart));
            $sysStart = strtotime($startDate);
            $yesterday = strtotime('-1 day', strtotime(date('Y-m-d')));

            $effectiveStart = max($monthStart, $sysStart);
            $effectiveEnd = min($monthEnd, $yesterday);

            $target = 0;
            if ($effectiveStart <= $effectiveEnd) {
                $days = ($effectiveEnd - $effectiveStart) / 86400 + 1;
                $target = round($days) * $nominalTarif;
            }

            // Get Scans (from report table)
            $scans = $this->db->table('report')
                        ->where('report_id', $code_id)
                        ->where('YEAR(jimpitan_date)', $year)
                        ->where('MONTH(jimpitan_date)', $m)
                        ->where('status', 1)
                        ->countAllResults();
            $scannedAmount = $scans * $nominalTarif;

            // Get Manual Payments (from tb_iuran)
            $payments = $this->db->table('tb_iuran')
                        ->selectSum('jml_bayar')
                        ->where('nikk', $kk['nikk'])
                        ->where('kode_tarif', 'TR001')
                        ->where('tahun', $year)
                        ->where('bulan', $m)
                        ->get()
                        ->getRowArray();
            $paidAmount = $payments['jml_bayar'] ?? 0;

            $totalPaid = $scannedAmount + $paidAmount;
            $debt = $target - $totalPaid;

            // Logic: Include current month as payable
            $mStart = strtotime("$year-$m-01");
            $yesterday = strtotime('-1 day', strtotime(date('Y-m-d')));
            $isPast = $mStart <= $yesterday;

            $monthlyData[] = [
                'bulan' => $m,
                'nama_bulan' => date('F', mktime(0, 0, 0, $m, 10)),
                'target' => $target,
                'total_paid' => $totalPaid,
                'debt' => $debt > 0 ? $debt : 0,
                'status' => $totalPaid >= $target ? 'Lunas' : 'Hutang',
                'is_past' => $isPast
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'kk' => $kk,
            'year' => $year,
            'summary' => $monthlyData,
            'nominalTarif' => $nominalTarif
        ]);
    }

    public function get_daily_detail($code_id, $bulan, $tahun)
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $kk = $this->db->table('master_kk')->where('code_id', $code_id)->get()->getRowArray();
        if (!$kk) return $this->response->setJSON(['status' => 'error', 'message' => 'Data KK tidak ditemukan']);

        // Get Jimpitan Tariff
        $tarif = $this->db->table('tb_tarif')->where('kode_tarif', 'TR001')->get()->getRowArray();
        $nominalTarif = $tarif['tarif'] ?? 500;

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        $scans = $this->db->table('report')
                    ->where('report_id', $code_id)
                    ->where('YEAR(jimpitan_date)', $tahun)
                    ->where('MONTH(jimpitan_date)', $bulan)
                    ->get()
                    ->getResultArray();
        
        $scanMap = [];
        foreach ($scans as $s) {
            $day = (int)date('j', strtotime($s['jimpitan_date']));
            $scanMap[$day] = $s;
        }

        $dailyData = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $hasScan = isset($scanMap[$d]) && $scanMap[$d]['status'] == 1;
            $dailyData[] = [
                'tgl' => $d,
                'date' => sprintf('%04d-%02d-%02d', $tahun, $bulan, $d),
                'scanned' => $hasScan,
                'collector' => $hasScan ? $scanMap[$d]['collector'] : '-',
                'scan_time' => $hasScan ? $scanMap[$d]['scan_time'] : '-'
            ];
        }

        // Get Manual Payments for this specific month
        $payments = $this->db->table('tb_iuran')
                    ->where('nikk', $kk['nikk'])
                    ->where('kode_tarif', 'TR001')
                    ->where('tahun', $tahun)
                    ->where('bulan', $bulan)
                    ->get()
                    ->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'kk_name' => $kk['kk_name'],
            'month_name' => date('F', mktime(0, 0, 0, $bulan, 10)),
            'year' => $tahun,
            'daily' => $dailyData,
            'payments' => $payments,
            'nominalTarif' => $nominalTarif
        ]);
    }

    public function bayar_hutang_jimpitan()
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $code_id = $this->request->getPost('code_id');
        $bulan   = $this->request->getPost('bulan');
        $tahun   = $this->request->getPost('tahun');
        $nominal = $this->request->getPost('nominal');

        $kk = $this->db->table('master_kk')->where('code_id', $code_id)->get()->getRowArray();
        if (!$kk) return $this->response->setJSON(['status' => 'error', 'message' => 'Data KK tidak ditemukan']);

        $this->db->transStart();

        $dataPayment = [
            'kode_tarif'  => 'TR001',
            'nikk'        => $kk['nikk'],
            'jenis_iuran' => 'wajib',
            'bulan'       => $bulan,
            'tahun'       => $tahun,
            'jumlah'      => $nominal,
            'jml_bayar'   => $nominal,
            'status'      => 'Lunas',
            'tgl_bayar'   => date('Y-m-d H:i:s'),
            'keterangan'  => 'Pelunasan Hutang Jimpitan',
            'created_at'  => date('Y-m-d H:i:s')
        ];
        $this->db->table('tb_iuran')->insert($dataPayment);

        // Auto-Journal
        $tarif = $this->db->table('tb_tarif')->where('kode_tarif', 'TR001')->get()->getRowArray();
        $coaCode = $tarif['coa_code'] ?? '41101';
        
        $journalData = [
            'date_trx' => date('Y-m-d'),
            'coa_code' => $coaCode,
            'desc_trx' => "Bayar Hutang Jimpitan - {$kk['kk_name']} (Bln {$bulan}/{$tahun})",
            'debet'    => $nominal,
            'kredit'   => 0,
            'reff'     => 'TR001_AUTO',
            'sub_reff' => $code_id
        ];
        $this->kasSubModel->insert($journalData);

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memproses pembayaran']);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Pembayaran berhasil dicatat']);
    }

    public function batal_bayar_hutang_jimpitan()
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $code_id = $this->request->getPost('code_id');
        $bulan   = $this->request->getPost('bulan');
        $tahun   = $this->request->getPost('tahun');

        $kk = $this->db->table('master_kk')->where('code_id', $code_id)->get()->getRowArray();
        if (!$kk) return $this->response->setJSON(['status' => 'error', 'message' => 'Data KK tidak ditemukan']);

        $this->db->transStart();

        // 1. Remove from tb_iuran (Manual Payments)
        $this->db->table('tb_iuran')
            ->where('nikk', $kk['nikk'])
            ->where('kode_tarif', 'TR001')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('keterangan', 'Pelunasan Hutang Jimpitan')
            ->delete();

        // 2. Remove from kas_sub (Journal)
        $this->db->table('kas_sub')
            ->where('sub_reff', $code_id)
            ->like('desc_trx', "Hutang Jimpitan", 'both')
            ->like('desc_trx', "Bln {$bulan}/{$tahun}", 'both')
            ->delete();

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membatalkan pembayaran']);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Pembayaran berhasil dibatalkan']);
    }

    public function hapus_pembayaran_item()
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $id = $this->request->getPost('id');
        if (!$id) return $this->response->setJSON(['status' => 'error', 'message' => 'ID Pembayaran tidak valid']);

        $payment = $this->db->table('tb_iuran')->where('id_iuran', $id)->get()->getRowArray();
        if (!$payment) return $this->response->setJSON(['status' => 'error', 'message' => 'Data pembayaran tidak ditemukan']);

        // Only allow deletion of manual payments
        if ($payment['keterangan'] !== 'Pelunasan Hutang Jimpitan') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Hanya pembayaran manual yang dapat dihapus secara individual']);
        }

        $kk = $this->db->table('master_kk')->where('nikk', $payment['nikk'])->get()->getRowArray();
        if (!$kk) return $this->response->setJSON(['status' => 'error', 'message' => 'Data KK tidak ditemukan']);

        $this->db->transStart();

        // 1. Identify and remove matching journal entry in kas_sub
        // Description pattern: "Bayar Hutang Jimpitan - [Name] (Bln [Month]/[Year])"
        $descPattern = "Bln {$payment['bulan']}/{$payment['tahun']}";
        
        $this->db->table('kas_sub')
            ->where('sub_reff', $kk['code_id'])
            ->where('debet', $payment['jml_bayar'])
            ->like('desc_trx', 'Hutang Jimpitan', 'both')
            ->delete();

        // 2. Remove the payment from tb_iuran
        $this->db->table('tb_iuran')->where('id_iuran', $id)->delete();

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus pembayaran']);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Pembayaran berhasil dihapus']);
    }
}

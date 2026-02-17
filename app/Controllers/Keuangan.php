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
                ->select('kas_sub.date_trx, kas_sub.reff, kas_sub.coa_code, MAX(kas_sub.id_trx) as id_trx, SUM(kas_sub.debet) as debet, SUM(kas_sub.kredit) as kredit, COUNT(kas_sub.id_trx) as count_trx, GROUP_CONCAT(CONCAT(kas_sub.desc_trx, "||", GREATEST(kas_sub.debet, kas_sub.kredit)) SEPARATOR ";;;") as detail_trx, max(tb_coa.name) as nama_akun, max(tb_coa.code) as kode_akun, max(tb_tarif.nama_tarif) as nama_tarif')
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

                if($pengurus && !empty($pengurus['kode_tarif'])) {
                     $allowedCodes[] = $pengurus['kode_tarif'];
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
        return redirect()->to('/keuangan/jurnal_sub')->with('success', 'Data berhasil disimpan');
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
}

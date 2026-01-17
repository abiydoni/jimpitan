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
                ->select('kas_sub.*, tb_coa.name as nama_akun, tb_coa.code as kode_akun')
                ->join('tb_coa', 'tb_coa.code = kas_sub.coa_code', 'left')
                ->orderBy('date_trx', 'DESC')
                ->orderBy('id_trx', 'DESC');

            // Apply Filter if not Super Admin (100)
            $selectedFilter = '';
            if ($userTarif != 100 && $userTarif > 0) {
                // Get kode_tarif for the assigned tarif id
                $tarif = $this->tarifModel->find($userTarif);
                if ($tarif) {
                    $builder->like('reff', $tarif['kode_tarif'], 'after');
                } else {
                    // Assigned tarif ID not found, show nothing or handle error
                    $builder->where('1=0'); 
                }
            } elseif ($userTarif == 100) {
                // Admin can filter by dropdown
                $selectedFilter = $this->request->getGet('filter_tarif');
                if ($selectedFilter) {
                     $builder->like('reff', $selectedFilter, 'after');
                }
            }

            $data['transaksi'] = $builder->paginate(20);
            $data['pager'] = $this->kasSubModel->pager;
            
        } catch (\Exception $e) {
             $data['error'] = 'Database Error: ' . $e->getMessage();
             $data['transaksi'] = [];
        }

        $data['coa'] = $this->coaModel->findAll();
        $data['tarif'] = $this->tarifModel->findAll();
        $data['userTarif'] = $userTarif;
        $data['selectedFilter'] = $selectedFilter ?? '';

        return view('keuangan/jurnal_sub', $data);
    }

    public function jurnal_umum()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $data = $this->getCommonData('Arus Kas Umum');
        
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

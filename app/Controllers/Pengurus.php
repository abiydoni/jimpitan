<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PengurusModel;
use App\Models\PengurusMenuModel;
use App\Models\MenuModel;

class Pengurus extends BaseController
{
    protected $pengurusModel;
    protected $pengurusMenuModel;
    protected $menuModel;
    protected $tarifModel;

    public function __construct()
    {
        $this->pengurusModel = new PengurusModel();
        $this->pengurusMenuModel = new PengurusMenuModel();
        $this->menuModel = new MenuModel();
        $this->tarifModel = new \App\Models\TarifModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        
        $pengurus = $this->pengurusModel->findAll();
        // Get all active menus for the modal
        $menus = $this->menuModel->where('status', '1')->orderBy('nama', 'ASC')->findAll();
        // Get all tariffs (active and inactive) for the dropdown
        $tarifs = $this->tarifModel->orderBy('nama_tarif', 'ASC')->findAll();

        $data = [
            'profil' => $profil,
            'title' => 'Manajemen Pengurus',
            'pengurus' => $pengurus,
            'menus' => $menus,
            'tarifs' => $tarifs
        ];

        return view('pengurus/index', $data);
    }

    public function store()
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $namaPengurus = $this->request->getPost('nama_pengurus');
        $selectedMenus = $this->request->getPost('menus'); // Array of menu codes
        $accessTypes = $this->request->getPost('access_types'); // Array keyed by menu code
        $aksesTarifs = $this->request->getPost('akses_tarif'); // Array keyed by menu code [menu_id => [tarif_id, ...]]

        $kodeTarif = $this->request->getPost('kode_tarif');

        if (empty($namaPengurus)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nama Pengurus harus diisi']);
        }

        $this->pengurusModel->db->transStart();

        // 1. Insert Pengurus
        $insertId = $this->pengurusModel->insert([
            'nama_pengurus' => $namaPengurus,
            'kode_tarif'    => !empty($kodeTarif) ? $kodeTarif : null
        ]);
        if (!$insertId) {
            $this->pengurusModel->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data pengurus']);
        }

        // 2. Insert Menus
        if (!empty($selectedMenus) && is_array($selectedMenus)) {
            $batchData = [];
            foreach ($selectedMenus as $kodeMenu) {
                // Default to 'full' if not set or invalid
                $type = isset($accessTypes[$kodeMenu]) ? $accessTypes[$kodeMenu] : 'full';
                
                // Handle Tariff Access (Implode to string if exists)
                $tarifAccessString = null;
                if (isset($aksesTarifs[$kodeMenu]) && is_array($aksesTarifs[$kodeMenu])) {
                    $tarifAccessString = implode(',', $aksesTarifs[$kodeMenu]);
                }

                $batchData[] = [
                    'id_pengurus' => $insertId,
                    'kode_menu'   => $kodeMenu,
                    'tipe_akses'  => $type,
                    'akses_tarif' => $tarifAccessString
                ];
            }
            $this->pengurusMenuModel->insertBatch($batchData);
        }

        $this->pengurusModel->db->transComplete();

        if ($this->pengurusModel->db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan transaksi']);
        }

        log_activity('CREATE_PENGURUS', 'Added pengurus: ' . $namaPengurus);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Pengurus berhasil ditambahkan']);
    }

    public function get($id)
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        
        $data = $this->pengurusModel->find($id);
        if (!$data) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        // Get assigned menus with access type
        $assigned = $this->pengurusMenuModel->where('id_pengurus', $id)->findAll();
        // $assignedIds = array_column($assigned, 'kode_menu'); 
        // Return full objects or map for easier JS handling
        
        return $this->response->setJSON([
            'status' => 'success', 
            'data' => $data,
            'assigned_menus' => $assigned // Returns array of objects {kode_menu: 1, tipe_akses: 'full'}
        ]);
    }

    public function update()
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id');
        $namaPengurus = $this->request->getPost('nama_pengurus');
        $selectedMenus = $this->request->getPost('menus');
        $accessTypes = $this->request->getPost('access_types');
        $aksesTarifs = $this->request->getPost('akses_tarif');

        $kodeTarif = $this->request->getPost('kode_tarif');

        if (empty($id) || empty($namaPengurus)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID dan Nama harus diisi']);
        }

        $this->pengurusModel->db->transStart();

        // 1. Update Pengurus
        $this->pengurusModel->update($id, [
            'nama_pengurus' => $namaPengurus,
            'kode_tarif'    => !empty($kodeTarif) ? $kodeTarif : null
        ]);

        // 2. Sync Menus (Delete All then Insert New)
        $this->pengurusMenuModel->where('id_pengurus', $id)->delete();

        if (!empty($selectedMenus) && is_array($selectedMenus)) {
            $batchData = [];
            foreach ($selectedMenus as $kodeMenu) {
                // Default to 'full' if not set
                $type = isset($accessTypes[$kodeMenu]) ? $accessTypes[$kodeMenu] : 'full';

                // Handle Tariff Access
                $tarifAccessString = null;
                if (isset($aksesTarifs[$kodeMenu]) && is_array($aksesTarifs[$kodeMenu])) {
                    $tarifAccessString = implode(',', $aksesTarifs[$kodeMenu]);
                }

                $batchData[] = [
                    'id_pengurus' => $id,
                    'kode_menu'   => $kodeMenu,
                    'tipe_akses'  => $type,
                    'akses_tarif' => $tarifAccessString
                ];
            }
            $this->pengurusMenuModel->insertBatch($batchData);
        }

        $this->pengurusModel->db->transComplete();

        if ($this->pengurusModel->db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui data']);
        }

        log_activity('UPDATE_PENGURUS', 'Updated pengurus ID: ' . $id);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil diperbarui']);
    }

    public function delete()
    {
        if (!session()->get('isLoggedIn')) return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);

        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id');

        // Optional: Check if used anywhere else before delete? 
        // For now, cascade is handled by DB FK or we just delete.
        // The DB schema I gave has ON DELETE CASCADE, so deleting pengurus handles the pivot table.
        
        if ($this->pengurusModel->delete($id)) {
            // Pivot table auto-clears due to FK Cascade if set, otherwise manual:
            // $this->pengurusMenuModel->where('id_pengurus', $id)->delete(); 
            // BUT I defined FK constraint in previous turn, so we rely on DB or just to be safe:
            // (Note: Model delete triggers might not fire simple SQL, codeigniter soft delete is false by default so it sends DELETE command)

            log_activity('DELETE_PENGURUS', 'Deleted pengurus ID: ' . $id);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data berhasil dihapus']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data']);
    }
}

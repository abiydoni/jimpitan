<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TarifModel;

class Tarif extends BaseController
{
    protected $tarifModel;

    public function __construct()
    {
        $this->tarifModel = new TarifModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        // Authorization Check
        if (session()->get('role') !== 's_admin' && session()->get('role') !== 'admin' && !$this->hasMenuAccess('tarif')) {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        
        // Determine Access Level
        $accessType = $this->getMenuAccessType('tarif');
        $isViewOnly = ($accessType === 'view');
        $role = session()->get('role');
        
        $canManage = in_array($role, ['s_admin', 'admin']) || (!$isViewOnly && $this->hasMenuAccess('tarif'));

        $tariffs = $this->tarifModel->orderBy('id', 'ASC')->findAll();

        $data = [
            'profil' => $profil,
            'title' => 'Manajemen Tarif',
            'tariffs' => $tariffs,
            'canManage' => $canManage,
            'isViewOnly' => $isViewOnly
        ];

        return view('tarif/index', $data);
    }

    public function store()
    {
        $role = session()->get('role');
        $isAdmin = in_array($role, ['s_admin', 'admin']);
        
        if (!$isAdmin) {
             if (!$this->hasMenuAccess('tarif') || $this->getMenuAccessType('tarif') === 'view') {
                  return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
             }
        }

        $rules = [
            'kode_tarif' => 'required|is_unique[tb_tarif.kode_tarif]',
            'nama_tarif' => 'required',
            'tarif'      => 'required|numeric',
            'metode'     => 'required'
        ];

        if (!$this->validate($rules)) {
             return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Validasi gagal',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'kode_tarif' => $this->request->getPost('kode_tarif'),
            'nama_tarif' => $this->request->getPost('nama_tarif'),
            'tarif'      => $this->request->getPost('tarif'),
            'metode'     => $this->request->getPost('metode'),
            'icon'       => $this->request->getPost('icon') ?: 'cash-outline', // Default icon
            'status'     => 1, // Default active
            'date_update'=> date('Y-m-d H:i:s')
        ];

        if ($this->tarifModel->insert($data)) {
            log_activity('CREATE_TARIFF', 'Added tariff: ' . $data['nama_tarif']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Tarif berhasil ditambahkan']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menambahkan tarif']);
    }

    public function update()
    {
        $role = session()->get('role');
        $isAdmin = in_array($role, ['s_admin', 'admin']);
        
        if (!$isAdmin) {
             if (!$this->hasMenuAccess('tarif') || $this->getMenuAccessType('tarif') === 'view') {
                  return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
             }
        }

        $id = $this->request->getPost('id');
        $existing = $this->tarifModel->find($id);

        if (!$existing) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        // Validate unique code only if changed
        $kodeTarif = $this->request->getPost('kode_tarif');
        $rules = [
            'kode_tarif' => "required|is_unique[tb_tarif.kode_tarif,id,{$id}]",
            'nama_tarif' => 'required',
            'tarif'      => 'required|numeric',
            'metode'     => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Validasi gagal', 
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'kode_tarif' => $kodeTarif,
            'nama_tarif' => $this->request->getPost('nama_tarif'),
            'tarif'      => $this->request->getPost('tarif'),
            'metode'     => $this->request->getPost('metode'),
            'icon'       => $this->request->getPost('icon'),
            'date_update'=> date('Y-m-d H:i:s')
        ];

        if ($this->tarifModel->update($id, $data)) {
            log_activity('UPDATE_TARIFF', 'Updated tariff: ' . $existing['nama_tarif']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Tarif berhasil diperbarui']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui tarif']);
    }

    public function delete()
    {
        $role = session()->get('role');
        $isAdmin = in_array($role, ['s_admin', 'admin']);
        
        if (!$isAdmin) {
             if (!$this->hasMenuAccess('tarif') || $this->getMenuAccessType('tarif') === 'view') {
                  return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
             }
        }

        $id = $this->request->getPost('id');
        $existing = $this->tarifModel->find($id);

        if (!$existing) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        if ($this->tarifModel->delete($id)) {
            log_activity('DELETE_TARIFF', 'Deleted tariff: ' . $existing['nama_tarif']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Tarif berhasil dihapus']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus tarif']);
    }

    public function toggleStatus()
    {
         $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');
        
        if ($this->tarifModel->update($id, ['status' => $status])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Status berhasil diubah']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengubah status']);
    }
}

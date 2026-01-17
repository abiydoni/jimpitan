<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MenuModel;

class Menu extends BaseController
{
    protected $menuModel;

    public function __construct()
    {
        $this->menuModel = new MenuModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        
        // Authorization Check (Admin/S_Admin only)
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();
        
        $menus = $this->menuModel->orderBy('nama', 'ASC')->findAll();

        $data = [
            'profil' => $profil,
            'title' => 'Manajemen Menu',
            'menus' => $menus
        ];

        return view('menu/index', $data);
    }

    public function store()
    {
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $rules = [
            'nama' => 'required',
            'alamat_url'  => 'required',
            'ikon' => 'required'
        ];

        if (!$this->validate($rules)) {
             return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Validasi gagal',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $roles = $this->request->getPost('roles'); // Array
        $roleAccess = $roles ? implode(',', $roles) : '';

        $data = [
            'nama' => $this->request->getPost('nama'),
            'alamat_url'  => $this->request->getPost('alamat_url'),
            'ikon' => $this->request->getPost('ikon'),
            'role_access' => $roleAccess,
            'status' => 1
        ];

        if ($this->menuModel->insert($data)) {
            log_activity('CREATE_MENU', 'Added menu: ' . $data['nama']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Menu berhasil ditambahkan']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menambahkan menu']);
    }

    public function update()
    {
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $kode = $this->request->getPost('kode');
        $existing = $this->menuModel->find($kode);

        if (!$existing) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        $rules = [
            'nama' => 'required',
            'alamat_url'  => 'required',
            'ikon' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Validasi gagal', 
                'errors' => $this->validator->getErrors()
            ]);
        }

        $roles = $this->request->getPost('roles'); // Array
        $roleAccess = $roles ? implode(',', $roles) : '';

        $data = [
            'nama' => $this->request->getPost('nama'),
            'alamat_url'  => $this->request->getPost('alamat_url'),
            'ikon' => $this->request->getPost('ikon'),
            'role_access' => $roleAccess
        ];

        if ($this->menuModel->update($kode, $data)) {
            log_activity('UPDATE_MENU', 'Updated menu: ' . $existing['nama']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Menu berhasil diperbarui']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui menu']);
    }

    public function delete()
    {
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $kode = $this->request->getPost('kode');
        $existing = $this->menuModel->find($kode);

        if (!$existing) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        if ($this->menuModel->delete($kode)) {
            log_activity('DELETE_MENU', 'Deleted menu: ' . $existing['nama']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Menu berhasil dihapus']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus menu']);
    }

    public function toggleStatus()
    {
         $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $kode = $this->request->getPost('kode');
        $status = $this->request->getPost('status');
        
        if ($this->menuModel->update($kode, ['status' => $status])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Status berhasil diubah']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengubah status']);
    }
}

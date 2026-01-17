<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RoleModel;

class Role extends BaseController
{
    protected $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
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
        
        $roles = $this->roleModel->findAll();

        $data = [
            'profil' => $profil,
            'title' => 'Manajemen Role',
            'roles' => $roles
        ];

        return view('role/index', $data);
    }

    public function store()
    {
        // Auth check...
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'remark' => $this->request->getPost('remark')
        ];

        if ($this->roleModel->save($data)) {
            log_activity('CREATE_ROLE', 'Added role: ' . $data['name']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Role berhasil ditambahkan']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menambahkan role', 'errors' => $this->roleModel->errors()]);
    }

    public function update()
    {
        // Auth check...
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id');
        
        // Prevent editing core roles
        $protected = ['s_admin', 'admin', 'user'];
        $existing = $this->roleModel->find($id);
        
        // If needed we can block renaming of core roles, but let's allow remark edit.
        
        $data = [
            'id' => $id,
            'name' => $this->request->getPost('name'),
            'remark' => $this->request->getPost('remark')
        ];

        if ($this->roleModel->save($data)) {
            log_activity('UPDATE_ROLE', 'Updated role: ' . $data['name']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Role berhasil diperbarui']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui role', 'errors' => $this->roleModel->errors()]);
    }

    public function delete()
    {
        // Auth check...
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id');
        $existing = $this->roleModel->find($id);

        if (!$existing) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        // Protect Core Roles
        $protected = ['s_admin', 'admin', 'user', 'pengurus', 'warga'];
        if (in_array($existing['name'], $protected)) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Role bawaan sistem tidak dapat dihapus!']);
        }

        if ($this->roleModel->delete($id)) {
            log_activity('DELETE_ROLE', 'Deleted role: ' . $existing['name']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Role berhasil dihapus']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus role']);
    }
}

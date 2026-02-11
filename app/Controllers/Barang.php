<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BarangModel;

class Barang extends BaseController
{
    protected $barangModel;

    public function __construct()
    {
        $this->barangModel = new BarangModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');
        
        // Check Access
        if (session()->get('role') !== 's_admin' && session()->get('role') !== 'admin' && !$this->hasMenuAccess('barang')) {
             return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();

        // Determine View Only
        $accessType = $this->getMenuAccessType('barang');
        $isViewOnly = ($accessType === 'view');
        $role = session()->get('role');
        
        $canManage = in_array($role, ['s_admin', 'admin']) || (!$isViewOnly && $this->hasMenuAccess('barang'));

        $keyword = $this->request->getGet('search');
        if ($keyword) {
            $this->barangModel->like('nama', $keyword)
                              ->orLike('kode_brg', $keyword);
        }
        
        $barang = $this->barangModel->orderBy('nama', 'ASC')->paginate(12, 'barang');
        $pager = $this->barangModel->pager;

        $data = [
            'profil' => $profil,
            'title' => 'Inventori Barang',
            'barang' => $barang,
            'pager' => $pager,
            'search' => $keyword,
            'canManage' => $canManage,
            'isViewOnly' => $isViewOnly,
            'role' => $role
        ];

        return view('barang/index', $data);
    }

    public function store()
    {
        $role = session()->get('role');
        $isAdmin = in_array($role, ['s_admin', 'admin']);

        // Access Check
        if (!$isAdmin) {
             if (!$this->hasMenuAccess('barang') || $this->getMenuAccessType('barang') === 'view') {
                  return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
             }
        }

        // Validation rules handled by Model, but we can do pre-validation here if needed
        // Or rely on Model->save() returning false and getErrors()

        $data = [
            'kode_brg' => $this->request->getPost('kode_brg'),
            'nama'     => $this->request->getPost('nama'),
            'jumlah'   => $this->request->getPost('jumlah'), // Changed from stok
            'tanggal'  => date('Y-m-d')
        ];

        if ($this->barangModel->save($data)) {
            log_activity('CREATE_BARANG', 'Added barang: ' . $data['nama']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Barang berhasil ditambahkan']);
        }

        return $this->response->setJSON([
            'status' => 'error', 
            'message' => 'Gagal menyimpan data',
            'errors' => $this->barangModel->errors()
        ]);
    }

    public function update()
    {
        $role = session()->get('role');
        $isAdmin = in_array($role, ['s_admin', 'admin']);

        // Access Check
        if (!$isAdmin) {
             if (!$this->hasMenuAccess('barang') || $this->getMenuAccessType('barang') === 'view') {
                  return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
             }
        }

        $id = $this->request->getPost('id');

        $data = [
            'kode'     => $id,
            'kode_brg' => $this->request->getPost('kode_brg'),
            'nama'     => $this->request->getPost('nama'),
            'jumlah'   => $this->request->getPost('jumlah'), // Changed from stok
        ];

        if ($this->barangModel->save($data)) {
            log_activity('UPDATE_BARANG', 'Updated barang: ' . $data['nama']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Barang berhasil diperbarui']);
        }

         return $this->response->setJSON([
            'status' => 'error', 
            'message' => 'Gagal memperbarui data',
            'errors' => $this->barangModel->errors()
        ]);
    }

    public function delete()
    {
        $role = session()->get('role');
        $isAdmin = in_array($role, ['s_admin', 'admin']);

        // Access Check
        if (!$isAdmin) {
             if (!$this->hasMenuAccess('barang') || $this->getMenuAccessType('barang') === 'view') {
                  return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
             }
        }

        $id = $this->request->getPost('id');
        $existing = $this->barangModel->find($id);

        if (!$existing) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        if ($this->barangModel->delete($id)) {
            log_activity('DELETE_BARANG', 'Deleted barang: ' . $existing['nama']);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Barang berhasil dihapus']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data']);
    }
}

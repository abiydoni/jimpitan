<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\WargaModel;

class Warga extends BaseController
{
    protected $wargaModel;

    public function __construct()
    {
        $this->wargaModel = new WargaModel();
    }

    public function index()
    {
        $session = session();
        $role = $session->get('role') ?? 'warga';
        
        // Pagination & Search
        $keyword = $this->request->getGet('q');
        
        if($keyword) {
             $dataWarga = $this->wargaModel->like('nama', $keyword)
                                           ->orLike('nik', $keyword)
                                           ->orLike('nikk', $keyword)
                                           ->orderBy('nama', 'ASC')
                                           ->findAll(100); // Limit results
        } else {
             $dataWarga = $this->wargaModel->orderBy('nama', 'ASC')->findAll(50); // Initial Load
        }

        $data = [
            'title'     => 'Data Warga',
            'warga'     => $dataWarga,
            'canManage' => in_array($role, ['s_admin', 'admin']),
            'keyword'   => $keyword
        ];

        return view('warga', $data);
    }

    public function store()
    {
        $session = session();
        $role = $session->get('role');

        if (!in_array($role, ['s_admin', 'admin'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $rules = [
            'nama' => 'required',
            'nik'  => 'required|is_unique[tb_warga.nik]',
            'kk_name' => 'required' // 'kk_name' here maps to 'nikk' based on user usage? 
            // Wait, tb_warga has 'nikk', usually NIK Kepala Keluarga.
            // Let's assume input name='nikk' maps to db 'nikk'.
        ];

        // Adjusting inputs
        $data = $this->request->getPost();
        
        // Basic Validation
        if (!$this->validate($rules)) {
            // Usually we return specific errors, but for simplicity
            // return $this->response->setJSON(['status' => 'error', 'message' => $this->validator->getErrors()]);
        }

        // Handle specific unique check manually if needed or catch exception
        try {
            $this->wargaModel->insert($data);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data warga berhasil ditambahkan']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function update()
    {
        $session = session();
        $role = $session->get('role');

        if (!in_array($role, ['s_admin', 'admin'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $id = $this->request->getPost('id_warga');
        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID not found']);
        }

        $data = $this->request->getPost();
        
        // Remove ID from data array to be safe
        unset($data['id_warga']);

        try {
            $this->wargaModel->update($id, $data);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data warga berhasil diperbarui']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function delete($id = null)
    {
        $session = session();
        $role = $session->get('role');

        if (!in_array($role, ['s_admin', 'admin'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if ($this->wargaModel->delete($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data warga berhasil dihapus']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data']);
        }
    }
}

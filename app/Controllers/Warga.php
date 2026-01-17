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
             $dataWarga = $this->wargaModel->orderBy('nama', 'ASC')->findAll(); // Show All
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
        ];

        // Adjusting inputs
        $data = $this->request->getPost();
        
        // Basic Validation
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['status' => 'error', 'message' => $this->validator->listErrors()]);
        }

        // Handle specific unique check manually if needed or catch exception
        // Handle File Upload
        $img = $this->request->getFile('foto');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $img->move(FCPATH . 'img/warga', $newName);
            
            // GD Resize removed for stability
            // $data['foto'] = $newName;

            $data['foto'] = $newName;
        }

        try {
            $this->wargaModel->insert($data);
            log_activity('CREATE_WARGA', 'Added new warga: ' . ($data['nama'] ?? 'Unknown'));
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
            // Handle File Upload
            $img = $this->request->getFile('foto');
            if ($img && $img->isValid() && !$img->hasMoved()) {
                $newName = $img->getRandomName();
                $img->move(FCPATH . 'img/warga', $newName);

                // GD Resize removed for stability

                $data['foto'] = $newName;

                // Delete old photo
                $oldData = $this->wargaModel->find($id);
                if($oldData && !empty($oldData['foto']) && file_exists(FCPATH . 'img/warga/' . $oldData['foto'])) {
                    @unlink(FCPATH . 'img/warga/' . $oldData['foto']);
                }
            } else {
                unset($data['foto']);
            }

            $this->wargaModel->update($id, $data);
            log_activity('UPDATE_WARGA', 'Updated warga ID: ' . $id);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data warga berhasil diperbarui']);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function delete($id = null)
    {
        $session = session();
        $role = $session->get('role');

        if (!in_array($role, ['s_admin', 'admin'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        // Delete photo if exists
        $warga = $this->wargaModel->find($id);
        if(!empty($warga['foto']) && file_exists(FCPATH . 'img/warga/' . $warga['foto'])) {
            @unlink(FCPATH . 'img/warga/' . $warga['foto']);
        }

        if ($this->wargaModel->delete($id)) {
            log_activity('DELETE_WARGA', 'Deleted warga ID: ' . $id . ' Name: ' . ($warga['nama'] ?? 'Unknown'));
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data warga berhasil dihapus']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data']);
        }
    }
}

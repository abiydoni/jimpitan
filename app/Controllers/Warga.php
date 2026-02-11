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
        
        // Check Access
        if ($role !== 's_admin' && $role !== 'admin' && !$this->hasMenuAccess('warga')) {
             return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        // Determine View Only
        $accessType = $this->getMenuAccessType('warga');
        $isViewOnly = ($accessType === 'view');
        $isAdmin = in_array($role, ['s_admin', 'admin']);
        
        // canManage means they can Add/Edit/Delete
        // If View Only, canManage is false regardless of other roles (unless admin)
        // If Admin, canManage is true.
        // If Pengurus (Full), canManage is true.
        $canManage = $isAdmin || (!$isViewOnly && $this->hasMenuAccess('warga'));

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
            'canManage' => $canManage,
            'isViewOnly'=> $isViewOnly,
            'keyword'   => $keyword
        ];

        return view('warga', $data);
    }

    public function store()
    {
        $session = session();
        $role = $session->get('role');
        $isAdmin = in_array($role, ['s_admin', 'admin']);
        
        // Access Check: Must be Admin OR (Have Access AND Not View Only)
        if (!$isAdmin) {
            if (!$this->hasMenuAccess('warga') || $this->getMenuAccessType('warga') === 'view') {
                 return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
            }
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
            
            // Robust Compression
            $this->_compressImage($newName);

            $data['foto'] = $newName;
        } elseif ($img && $img->getError() != 4) {
             // Error present (and not just "no file")
             return $this->response->setJSON(['status' => 'error', 'message' => 'Upload Gagal: ' . $img->getErrorString() . ' (Cek ukuran file)']);
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
        $isAdmin = in_array($role, ['s_admin', 'admin']);

        // Access Check
        if (!$isAdmin) {
             if (!$this->hasMenuAccess('warga') || $this->getMenuAccessType('warga') === 'view') {
                  return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
             }
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
                
                // Check if user uploaded a file
                if ($img->isValid() && !$img->hasMoved()) {
                    $newName = $img->getRandomName();
                    $img->move(FCPATH . 'img/warga', $newName);
    
                    // Robust Compression
                    $this->_compressImage($newName);
    
                    $data['foto'] = $newName;
    
                    // Delete old photo
                    $oldData = $this->wargaModel->find($id);
                    if($oldData && !empty($oldData['foto']) && file_exists(FCPATH . 'img/warga/' . $oldData['foto'])) {
                        @unlink(FCPATH . 'img/warga/' . $oldData['foto']);
                    }
                } elseif ($img->getError() != 4) { 
                    // Error 4 means "No file was uploaded" (User didn't want to change photo) - Ignore
                    // Any other error means the upload FAILED (e.g. Size Exceeded)
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Upload Gagal: ' . $img->getErrorString() . ' (Cek ukuran file)']);
                } else {
                    // No file uploaded, keep old
                    unset($data['foto']);
                }

            $this->wargaModel->update($id, $data);
            
            // Sync Session if Admin updates their own data
            $session = session();
            // Check if name matches (Simple check) or strict ID check if possible
            // Since we don't have user_id link in warga table easily, we use name match like in Home.php
            $updatedWarga = $this->wargaModel->find($id);
            if ($updatedWarga && $updatedWarga['nama'] === $session->get('name') && isset($data['foto'])) {
                $session->set('foto', $data['foto']);
            }

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
        $isAdmin = in_array($role, ['s_admin', 'admin']);

        // Access Check
        if (!$isAdmin) {
             if (!$this->hasMenuAccess('warga') || $this->getMenuAccessType('warga') === 'view') {
                  return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
             }
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

    private function _compressImage($fileName)
    {
        if (!extension_loaded('gd')) return;

        try {
            $path = FCPATH . 'img/warga/' . $fileName;
            \Config\Services::image()
                ->withFile($path)
                ->resize(800, 800, true, 'auto')
                ->save($path, 80);
        } catch (\Exception $e) {
            // Fail silently, keeping original file
        }
    }
}

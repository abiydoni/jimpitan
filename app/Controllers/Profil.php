<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProfilModel;

class Profil extends BaseController
{
    protected $profilModel;

    public function __construct()
    {
        $this->profilModel = new ProfilModel();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Authorization Check (Admin/S_Admin only for editing profile usually)
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        // Fetch current profile (Assuming kode=1)
        $profil = $this->profilModel->find(1);
        
        // If not exists, maybe create default or handle error, but usually exists
        if (!$profil) {
             // Optional: Create default if table is empty
             $this->profilModel->insert(['kode' => 1, 'nama' => 'Jimpitan App']);
             $profil = $this->profilModel->find(1);
        }

        $data = [
            'profil' => $profil,
            'title' => 'Setting Profil Jimpitan'
        ];

        return view('profil/index', $data);
    }

    public function update()
    {
        $role = session()->get('role');
        if ($role !== 's_admin' && $role !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $kode = 1; // Always updating record 1
        $existing = $this->profilModel->find($kode);
        
        if (!$existing) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Data profil tidak ditemukan']);
        }

        // Validation rules
        $rules = [
            'nama'    => 'required',
            'cp'      => 'required',
            'hp'      => 'required',
            'alamat'  => 'required',
            // Files are optional
            'logo' => [
                'rules' => 'is_image[logo]|mime_in[logo,image/jpg,image/jpeg,image/png,image/webp]|max_size[logo,2048]',
                'errors' => [
                    'is_image' => 'File logo harus berupa gambar',
                    'mime_in' => 'Format logo harus jpg, jpeg, png, atau webp',
                    'max_size' => 'Ukuran logo maksimal 2MB'
                ]
            ],
            'gambar' => [
                 'rules' => 'is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png,image/webp]|max_size[gambar,2048]',
                 'errors' => [
                    'is_image' => 'File gambar utama harus berupa gambar',
                    'mime_in' => 'Format gambar harus jpg, jpeg, png, atau webp',
                    'max_size' => 'Ukuran gambar maksimal 2MB'
                 ]
            ]
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Validasi gagal', 
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'nama'    => $this->request->getPost('nama'),
            'alamat'  => $this->request->getPost('alamat'),
            'cp'      => $this->request->getPost('cp'),
            'hp'      => $this->request->getPost('hp'),
            'catatan' => $this->request->getPost('catatan'),
        ];

        // Handle File Uploads
        $logoFile = $this->request->getFile('logo');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            $newName = $logoFile->getRandomName();
            $logoFile->move(FCPATH . 'assets/img', $newName); // Save to assets/img
            $data['logo'] = $newName;
             // Optional: Delete old file if strictly managed
        }

        $gambarFile = $this->request->getFile('gambar');
        if ($gambarFile && $gambarFile->isValid() && !$gambarFile->hasMoved()) {
             $newName = $gambarFile->getRandomName();
             $gambarFile->move(FCPATH . 'assets/img', $newName);
             $data['gambar'] = $newName;
        }

        if ($this->profilModel->update($kode, $data)) {
            log_activity('UPDATE_PROFILE', 'Updated profile settings');
            return $this->response->setJSON(['status' => 'success', 'message' => 'Profil berhasil diperbarui']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui profil']);
    }
}

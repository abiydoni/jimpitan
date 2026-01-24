<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AnnouncementModel;

class Announcement extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new AnnouncementModel();
    }

    public function index()
    {
        $role = session()->get('role');
        if($role !== 's_admin' && $role !== 'admin') {
            return redirect()->to('/');
        }
        
        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();

        $data = [
            'profil' => $profil,
            'title' => 'Kelola Pengumuman',
            'announcements' => $this->model->orderBy('created_at', 'DESC')->findAll()
        ];
        
        return view('announcement/index', $data);
    }

    public function create()
    {
        $role = session()->get('role');
        if($role !== 's_admin' && $role !== 'admin') {
            return redirect()->to('/');
        }

        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();

        return view('announcement/form', [
            'profil' => $profil,
            'title' => 'Tambah Pengumuman',
            'action' => 'store'
        ]);
    }

    public function store()
    {
        $role = session()->get('role');
        if($role !== 's_admin' && $role !== 'admin') {
            return redirect()->to('/');
        }

        $rules = [
            'title' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'image' => 'max_size[image,2048]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('image');
        $imageName = null;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $imageName = $file->getRandomName();
            $file->move(FCPATH . 'img/announcement', $imageName);
            // Compression
            if (extension_loaded('gd')) {
                 try {
                    $path = FCPATH . 'img/announcement/' . $imageName;
                    \Config\Services::image()
                        ->withFile($path)
                        ->resize(800, 800, true, 'auto')
                        ->save($path, 80);
                } catch (\Exception $e) {}
            }
        }

        $this->model->save([
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'is_transparent' => $this->request->getPost('is_transparent') ? 1 : 0,
            'hide_text' => $this->request->getPost('hide_text') ? 1 : 0,
            'image' => $imageName
        ]);

        return redirect()->to('/announcement')->with('success', 'Pengumuman berhasil ditambahkan');
    }

    public function edit($id)
    {
        $role = session()->get('role');
        if($role !== 's_admin' && $role !== 'admin') {
            return redirect()->to('/');
        }

        $data = $this->model->find($id);
        if(!$data) return redirect()->to('/announcement');

        $db = \Config\Database::connect();
        $profil = $db->table('tb_profil')->get()->getRowArray();

        return view('announcement/form', [
            'profil' => $profil,
            'title' => 'Edit Pengumuman',
            'action' => 'update',
            'data' => $data
        ]);
    }

    public function update($id)
    {
        $role = session()->get('role');
        if($role !== 's_admin' && $role !== 'admin') {
            return redirect()->to('/');
        }

        $rules = [
            'title' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'image' => 'max_size[image,2048]|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('image');
        $imageName = $this->request->getPost('old_image');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newImageName = $file->getRandomName();
            $file->move(FCPATH . 'img/announcement', $newImageName);
            
             // Compression
             if (extension_loaded('gd')) {
                try {
                   $path = FCPATH . 'img/announcement/' . $newImageName;
                   \Config\Services::image()
                       ->withFile($path)
                       ->resize(800, 800, true, 'auto')
                       ->save($path, 80);
               } catch (\Exception $e) {}
           }
            
            // Delete old image
            if($imageName && file_exists(FCPATH . 'img/announcement/' . $imageName)) {
                unlink(FCPATH . 'img/announcement/' . $imageName);
            }
            $imageName = $newImageName;
        }

        $this->model->update($id, [
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'is_transparent' => $this->request->getPost('is_transparent') ? 1 : 0,
            'hide_text' => $this->request->getPost('hide_text') ? 1 : 0,
            'image' => $imageName
        ]);

        return redirect()->to('/announcement')->with('success', 'Pengumuman berhasil diperbarui');
    }

    public function delete($id)
    {
        $role = session()->get('role');
        if($role !== 's_admin' && $role !== 'admin') {
            return redirect()->to('/');
        }
        
        $data = $this->model->find($id);
         if($data['image'] && file_exists(FCPATH . 'img/announcement/' . $data['image'])) {
            unlink(FCPATH . 'img/announcement/' . $data['image']);
        }

        $this->model->delete($id);
        return redirect()->to('/announcement')->with('success', 'Pengumuman dihapus');
    }
}

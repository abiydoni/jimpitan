<?php

namespace App\Controllers;

use App\Models\UserModel;
// use CodeIgniter\HTTP\ResponseInterface; // This line is removed as per the instruction

class Auth extends BaseController
{
    public function index()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }
        return view('auth/login');
    }

    public function login()
    {
        $session = session();
        $model = new UserModel();
        $user_name = $this->request->getVar('user_name');
        $password = $this->request->getVar('password');
        
        $data = $model->where('user_name', $user_name)->first();
        
        if($data){
            $pass = $data['password'];
            $authenticatePassword = password_verify($password, $pass);
            if($authenticatePassword){
                $ses_data = [
                    'id_code' => $data['id_code'],
                    'user_name' => $data['user_name'],
                    'name' => $data['name'] ?? $data['user_name'],
                    'role' => $data['role'],
                    'shift' => $data['shift'],
                    'isLoggedIn' => TRUE
                ];
                $session->set($ses_data);
                return redirect()->to('/');
            }else{
                $session->setFlashdata('msg', 'Password salah.');
                return redirect()->to('/login');
            }
        }else{
            $session->setFlashdata('msg', 'Username tidak ditemukan.');
            return redirect()->to('/login');
        }
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/login');
    }


    public function updateProfile()
    {
        $session = session();
        $model = new UserModel();
        $id = $session->get('id_code');
        $name = $this->request->getVar('name');

        if (!$name) {
            $session->setFlashdata('error', 'Nama tidak boleh kosong.');
            return redirect()->back();
        }

        $model->update($id, ['name' => $name]);
        $session->set('name', $name);
        
        $session->setFlashdata('success', 'Profil berhasil diperbarui.');
        return redirect()->to('/');
    }


    public function updatePassword()
    {
        $session = session();
        $model = new UserModel();
        $id = $session->get('id_code');
        
        $current_password = $this->request->getVar('current_password');
        $new_password = $this->request->getVar('new_password');
        $confirm_password = $this->request->getVar('confirm_password');

        $user = $model->find($id);

        if (!password_verify($current_password, $user['password'])) {
            $session->setFlashdata('error', 'Password saat ini salah.');
            return redirect()->back();
        }

        if ($new_password !== $confirm_password) {
            $session->setFlashdata('error', 'Konfirmasi password tidak cocok.');
            return redirect()->back();
        }

        $model->update($id, [
            'password' => password_hash($new_password, PASSWORD_DEFAULT)
        ]);

        $session->setFlashdata('success', 'Password berhasil diubah.');
        return redirect()->to('/');
    }
}

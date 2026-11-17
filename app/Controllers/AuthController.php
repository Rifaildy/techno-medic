<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ─────────────────────────────────────────────
    //  GET /login
    // ─────────────────────────────────────────────
    public function login(): string
    {
        // Kalau sudah login, langsung ke dashboard
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login', [
            'title' => 'Login — TechnoMedic',
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /login
    // ─────────────────────────────────────────────
    public function attemptLogin()
    {
        $rules = [
            'identifier' => 'required|min_length[3]',
            'password'   => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                             ->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $identifier = $this->request->getPost('identifier');
        $password   = $this->request->getPost('password');

        $user = $this->userModel->findByUsernameOrEmail($identifier);

        // Validasi user & password
        if (! $user || ! password_verify($password, $user['password'])) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Username/email atau password salah.');
        }

        // Cek status akun
        if ($user['status'] !== 'active') {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        // Ambil permissions user
        $permissions = $this->userModel->getUserPermissions($user['id']);

        // Simpan ke session
        session()->set([
            'logged_in'   => true,
            'user_id'     => $user['id'],
            'username'    => $user['username'],
            'fullname'    => $user['fullname'],
            'role_name'   => $user['role_name'],
            'role_label'  => $user['role_label'],
            'permissions' => $permissions,
        ]);

        return redirect()->to('/dashboard')->with('success', 'Selamat datang, ' . $user['fullname'] . '!');
    }

    // ─────────────────────────────────────────────
    //  POST /logout
    // ─────────────────────────────────────────────
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda berhasil logout.');
    }
}

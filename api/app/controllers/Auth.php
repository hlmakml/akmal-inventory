<?php

// Controller: Authentication (login/logout)
class Auth extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Show login page
    public function index()
    {
        if (isset($_SESSION['user'])) {
            $this->redirect('/home');
        }

        $data['judul'] = 'Login';
        $this->view('auth/login', $data);
    }

    // Process login (POST only)
    public function prosesLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            Flasher::set('Username dan password wajib diisi.', 'danger');
            $this->redirect('/auth');
        }

        $pengguna = $this->model('Pengguna_model')->getPenggunaByUsername($username);

        if ($pengguna && password_verify($password, $pengguna['password'])) {
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id'       => $pengguna['id'],
                'nama'     => $pengguna['nama'],
                'username' => $pengguna['username'],
                'role'     => $pengguna['role'],
            ];

            $this->redirect('/home');
        }

        Flasher::set('Gagal! Username atau password salah.', 'danger');
        $this->redirect('/auth');
    }

    // Logout and destroy session
    public function logout()
    {
        $_SESSION = [];
        session_destroy();

        session_start();
        session_regenerate_id(true);

        Flasher::set('Anda berhasil logout.', 'success');
        $this->redirect('/auth');
    }

    private function redirect($path)
    {
        header('Location: ' . BASEURL . $path);
        exit;
    }
}

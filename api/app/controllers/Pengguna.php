<?php

// Controller: User management (admin only)
class Pengguna extends Controller
{
    private $penggunaModel;
    private $roleValid = ['admin', 'staff_gudang'];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check login
        if (!isset($_SESSION['user'])) {
            $this->redirect('/auth', 'Silakan login terlebih dahulu.', 'danger');
        }

        // Admin only
        if ($_SESSION['user']['role'] !== 'admin') {
            $this->redirect('/home', 'Akses Ditolak! Anda tidak memiliki hak akses ke halaman tersebut.', 'danger');
        }

        $this->penggunaModel = $this->model('Pengguna_model');
    }

    public function index()
    {
        $data['judul']    = 'Manajemen Pengguna';
        $data['halaman']  = 'pengguna';
        $data['pengguna'] = $this->penggunaModel->getAllPengguna();
        $data['id_saya']  = (int) $_SESSION['user']['id'];

        $this->view('layout/header', $data);
        $this->view('pengguna/index', $data);
        $this->view('layout/footer', $data);
    }

    // Add new user (POST)
    public function tambah()
    {
        $this->wajibPost();

        $input = $this->ambilInput();
        $error = $this->validasi($input, true);
        if ($error !== null) {
            $this->redirect('/pengguna', $error, 'danger');
        }

        // Check if username already exists
        if ($this->penggunaModel->getPenggunaByUsername($input['username'])) {
            $this->redirect('/pengguna', 'Username ' . $input['username'] . ' sudah dipakai pengguna lain.', 'danger');
        }

        try {
            if ($this->penggunaModel->tambahDataPengguna($input) > 0) {
                $this->redirect('/pengguna', 'Pengguna ' . $input['nama'] . ' berhasil ditambahkan.', 'success');
            }
            $this->redirect('/pengguna', 'Pengguna gagal ditambahkan.', 'danger');
        } catch (PDOException $e) {
            $this->redirect('/pengguna', 'Pengguna gagal disimpan. Periksa kembali data yang diisi.', 'danger');
        }
    }

    // Update user (POST)
    public function ubah()
    {
        $this->wajibPost();

        $input = $this->ambilInput();
        $lama  = $this->penggunaModel->getPenggunaById($input['id']);

        if ($input['id'] <= 0 || !$lama) {
            $this->redirect('/pengguna', 'Data pengguna tidak ditemukan.', 'danger');
        }

        // Cannot change own role
        $akunSendiri = ($input['id'] === (int) $_SESSION['user']['id']);
        if ($akunSendiri) {
            $input['role'] = $lama['role'];
        }

        $error = $this->validasi($input, false);
        if ($error !== null) {
            $this->redirect('/pengguna', $error, 'danger');
        }

        // Check username conflict with other users
        $kembar = $this->penggunaModel->getPenggunaByUsername($input['username']);
        if ($kembar && (int) $kembar['id'] !== $input['id']) {
            $this->redirect('/pengguna', 'Username ' . $input['username'] . ' sudah dipakai pengguna lain.', 'danger');
        }

        try {
            $berubah = $this->penggunaModel->ubahDataPengguna($input);

            // Update session if editing own account
            if ($akunSendiri) {
                $_SESSION['user']['nama']     = $input['nama'];
                $_SESSION['user']['username'] = $input['username'];
            }

            if ($berubah > 0) {
                $this->redirect('/pengguna', 'Pengguna ' . $input['nama'] . ' berhasil diperbarui.', 'success');
            }
            $this->redirect('/pengguna', 'Tidak ada perubahan data yang disimpan.', 'info');
        } catch (PDOException $e) {
            $this->redirect('/pengguna', 'Pengguna gagal diperbarui. Periksa kembali data yang diisi.', 'danger');
        }
    }

    // Delete user (POST)
    public function hapus($id = 0)
    {
        $this->wajibPost();

        $id       = (int) $id;
        $pengguna = $this->penggunaModel->getPenggunaById($id);

        if (!$pengguna) {
            $this->redirect('/pengguna', 'Data pengguna tidak ditemukan.', 'danger');
        }

        // Cannot delete own account
        if ($id === (int) $_SESSION['user']['id']) {
            $this->redirect('/pengguna', 'Anda tidak dapat menghapus akun yang sedang digunakan.', 'danger');
        }

        try {
            if ($this->penggunaModel->hapusDataPengguna($id) > 0) {
                $this->redirect('/pengguna', 'Pengguna ' . $pengguna['nama'] . ' berhasil dihapus.', 'success');
            }
            $this->redirect('/pengguna', 'Pengguna gagal dihapus.', 'danger');
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $this->redirect('/pengguna', 'Pengguna ' . $pengguna['nama'] . ' tidak dapat dihapus karena sudah tercatat di transaksi.', 'danger');
            }
            $this->redirect('/pengguna', 'Pengguna gagal dihapus.', 'danger');
        }
    }

    // Helper functions

    private function redirect($path, $pesan = null, $warna = 'danger')
    {
        if ($pesan !== null) {
            Flasher::set($pesan, $warna);
        }
        header('Location: ' . BASEURL . $path);
        exit;
    }

    private function wajibPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pengguna');
        }
    }

    private function post($key)
    {
        return (isset($_POST[$key]) && is_string($_POST[$key])) ? trim($_POST[$key]) : '';
    }

    private function postMentah($key)
    {
        return (isset($_POST[$key]) && is_string($_POST[$key])) ? $_POST[$key] : '';
    }

    private function ambilInput()
    {
        return [
            'id'                  => (int) $this->post('id'),
            'nama'                => $this->post('nama'),
            'username'            => strtolower($this->post('username')),
            'role'                => $this->post('role'),
            'password'            => $this->postMentah('password'),
            'konfirmasi_password' => $this->postMentah('konfirmasi_password'),
        ];
    }

    // Server-side validation
    private function validasi(array $in, $passwordWajib)
    {
        if ($in['nama'] === '') {
            return 'Nama wajib diisi.';
        }
        if (strlen($in['nama']) > 100) {
            return 'Nama maksimal 100 karakter.';
        }

        // Username: 3-50 chars, lowercase/digits/dot/underscore only
        if (!preg_match('/^[a-z0-9._]{3,50}$/', $in['username'])) {
            return 'Username harus 3-50 karakter dan hanya boleh berisi huruf, angka, titik, atau garis bawah.';
        }

        if (!in_array($in['role'], $this->roleValid, true)) {
            return 'Role tidak valid.';
        }

        // Validate password
        if ($passwordWajib || $in['password'] !== '') {
            $panjang = strlen($in['password']);
            if ($panjang < 6 || $panjang > 72) {
                return 'Password harus 6 sampai 72 karakter.';
            }
            if ($in['password'] !== $in['konfirmasi_password']) {
                return 'Konfirmasi password tidak sama dengan password.';
            }
        }

        return null;
    }
}

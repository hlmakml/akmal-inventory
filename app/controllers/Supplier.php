<?php

// Controller: Supplier management (admin only)
class Supplier extends Controller
{
    private $supplierModel;

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

        $this->supplierModel = $this->model('Supplier_model');
    }

    public function index()
    {
        $data['judul']    = 'Supplier';
        $data['halaman']  = 'supplier';
        $data['supplier'] = $this->supplierModel->getAllSupplier();

        $this->view('layout/header', $data);
        $this->view('supplier/index', $data);
        $this->view('layout/footer', $data);
    }

    // Add new supplier (POST)
    public function tambah()
    {
        $this->wajibPost();

        $input = $this->ambilInput();
        $error = $this->validasi($input);
        if ($error !== null) {
            $this->redirect('/supplier', $error, 'danger');
        }

        try {
            $kode = $this->supplierModel->tambahDataSupplier($input);

            if ($kode !== false) {
                $this->redirect('/supplier', 'Supplier ' . $input['nama_supplier'] . ' berhasil ditambahkan dengan kode ' . $kode . '.', 'success');
            }
            $this->redirect('/supplier', 'Supplier gagal ditambahkan.', 'danger');
        } catch (PDOException $e) {
            error_log('[Supplier::tambah] ' . $e->getMessage());
            $this->redirect('/supplier', 'Supplier gagal disimpan. Periksa kembali data yang diisi, lalu coba lagi.', 'danger');
        }
    }

    // Update supplier (POST)
    public function ubah()
    {
        $this->wajibPost();

        $input = $this->ambilInput();

        if ($input['id'] <= 0 || !$this->supplierModel->getSupplierById($input['id'])) {
            $this->redirect('/supplier', 'Data supplier tidak ditemukan.', 'danger');
        }

        $error = $this->validasi($input);
        if ($error !== null) {
            $this->redirect('/supplier', $error, 'danger');
        }

        try {
            if ($this->supplierModel->ubahDataSupplier($input) > 0) {
                $this->redirect('/supplier', 'Supplier ' . $input['nama_supplier'] . ' berhasil diperbarui.', 'success');
            }
            $this->redirect('/supplier', 'Tidak ada perubahan data yang disimpan.', 'info');
        } catch (PDOException $e) {
            $this->redirect('/supplier', 'Supplier gagal diperbarui. Periksa kembali data yang diisi.', 'danger');
        }
    }

    // Delete supplier (POST)
    public function hapus($id = 0)
    {
        $this->wajibPost();

        $id       = (int) $id;
        $supplier = $this->supplierModel->getSupplierById($id);

        if (!$supplier) {
            $this->redirect('/supplier', 'Data supplier tidak ditemukan.', 'danger');
        }

        try {
            if ($this->supplierModel->hapusDataSupplier($id) > 0) {
                $this->redirect('/supplier', 'Supplier ' . $supplier['nama_supplier'] . ' berhasil dihapus.', 'success');
            }
            $this->redirect('/supplier', 'Supplier gagal dihapus.', 'danger');
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $this->redirect('/supplier', 'Supplier ' . $supplier['nama_supplier'] . ' tidak dapat dihapus karena sudah tercatat di transaksi barang masuk.', 'danger');
            }
            $this->redirect('/supplier', 'Supplier gagal dihapus.', 'danger');
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
            $this->redirect('/supplier');
        }
    }

    private function post($key)
    {
        return (isset($_POST[$key]) && is_string($_POST[$key])) ? trim($_POST[$key]) : '';
    }

    private function ambilInput()
    {
        return [
            'id'            => (int) $this->post('id'),
            'nama_supplier' => $this->post('nama_supplier'),
            'kontak'        => $this->post('kontak'),
            'email'         => $this->post('email'),
            'alamat'        => $this->post('alamat'),
        ];
    }

    // Server-side validation
    private function validasi(array $in)
    {
        if ($in['nama_supplier'] === '') {
            return 'Nama supplier wajib diisi.';
        }
        if (strlen($in['nama_supplier']) > 150) {
            return 'Nama supplier maksimal 150 karakter.';
        }

        // Validate contact (optional)
        if ($in['kontak'] !== '') {
            if (strlen($in['kontak']) > 20) {
                return 'Kontak maksimal 20 karakter.';
            }
            if (!preg_match('/^[0-9+()\s-]+$/', $in['kontak'])) {
                return 'Kontak hanya boleh berisi angka, spasi, tanda + - ( ).';
            }
        }

        // Validate email (optional)
        if ($in['email'] !== '') {
            if (strlen($in['email']) > 100) {
                return 'Email maksimal 100 karakter.';
            }
            if (!filter_var($in['email'], FILTER_VALIDATE_EMAIL)) {
                return 'Format email tidak valid.';
            }
        }

        if (strlen($in['alamat']) > 1000) {
            return 'Alamat maksimal 1000 karakter.';
        }

        return null;
    }
}

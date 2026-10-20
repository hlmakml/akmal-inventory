<?php

// Controller: Item management (admin only)
class Barang extends Controller
{
    private $barangModel;

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

        $this->barangModel = $this->model('Barang_model');
    }

    public function index()
    {
        $data['judul']    = 'Data Barang';
        $data['halaman']  = 'barang';
        $data['barang']   = $this->barangModel->getAllBarang();
        $data['kategori'] = $this->barangModel->getAllKategori();

        $this->view('layout/header', $data);
        $this->view('barang/index', $data);
        $this->view('layout/footer', $data);
    }

    // Add new item (POST)
    public function tambah()
    {
        $this->wajibPost();

        $input = $this->ambilInput();
        $error = $this->validasi($input);
        if ($error !== null) {
            $this->redirect('/barang', $error, 'danger');
        }

        try {
            $kode = $this->barangModel->tambahDataBarang($input);

            if ($kode !== false) {
                $this->redirect('/barang', 'Barang ' . $input['nama_barang'] . ' berhasil ditambahkan dengan kode ' . $kode . '.', 'success');
            }
            $this->redirect('/barang', 'Barang gagal ditambahkan.', 'danger');
        } catch (PDOException $e) {
            error_log('[Barang::tambah] ' . $e->getMessage());
            $this->redirect('/barang', 'Barang gagal disimpan. Periksa kembali kategori barang, lalu coba lagi.', 'danger');
        }
    }

    // Update item (POST)
    public function ubah()
    {
        $this->wajibPost();

        $input = $this->ambilInput();

        if ($input['id'] <= 0 || !$this->barangModel->getBarangById($input['id'])) {
            $this->redirect('/barang', 'Data barang tidak ditemukan.', 'danger');
        }

        $error = $this->validasi($input);
        if ($error !== null) {
            $this->redirect('/barang', $error, 'danger');
        }

        try {
            if ($this->barangModel->ubahDataBarang($input) > 0) {
                $this->redirect('/barang', 'Barang ' . $input['nama_barang'] . ' berhasil diperbarui.', 'success');
            }
            $this->redirect('/barang', 'Tidak ada perubahan data yang disimpan.', 'info');
        } catch (PDOException $e) {
            error_log('[Barang::ubah] ' . $e->getMessage());
            $this->redirect('/barang', 'Barang gagal diperbarui. Periksa kembali kategori barang.', 'danger');
        }
    }

    // Delete item (POST)
    public function hapus($id = 0)
    {
        $this->wajibPost();

        $id     = (int) $id;
        $barang = $this->barangModel->getBarangById($id);

        if (!$barang) {
            $this->redirect('/barang', 'Data barang tidak ditemukan.', 'danger');
        }

        try {
            if ($this->barangModel->hapusDataBarang($id) > 0) {
                $this->redirect('/barang', 'Barang ' . $barang['nama_barang'] . ' berhasil dihapus.', 'success');
            }
            $this->redirect('/barang', 'Barang gagal dihapus.', 'danger');
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $this->redirect('/barang', 'Barang ' . $barang['nama_barang'] . ' tidak dapat dihapus karena sudah tercatat di transaksi.', 'danger');
            }
            $this->redirect('/barang', 'Barang gagal dihapus.', 'danger');
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
            $this->redirect('/barang');
        }
    }

    private function post($key)
    {
        return (isset($_POST[$key]) && is_string($_POST[$key])) ? trim($_POST[$key]) : '';
    }

    private function ambilInput()
    {
        return [
            'id'           => (int) $this->post('id'),
            'nama_barang'  => $this->post('nama_barang'),
            'kategori_id'  => (int) $this->post('kategori_id'),
            'satuan'       => $this->post('satuan'),
            'stok_minimum' => $this->post('stok_minimum'),
            'harga_beli'   => $this->post('harga_beli'),
            'harga_jual'   => $this->post('harga_jual'),
            'lokasi_rak'   => $this->post('lokasi_rak'),
        ];
    }

    // Server-side validation
    private function validasi(array &$in)
    {
        if ($in['nama_barang'] === '' || $in['satuan'] === '') {
            return 'Nama barang dan satuan wajib diisi.';
        }

        if (strlen($in['nama_barang']) > 150) {
            return 'Nama barang maksimal 150 karakter.';
        }
        if (strlen($in['satuan']) > 20) {
            return 'Satuan maksimal 20 karakter.';
        }

        // Validate rack location format: A-01, B-12, etc
        if ($in['lokasi_rak'] !== '' && !preg_match('/^[A-Z]-[0-9]{2}$/D', $in['lokasi_rak'])) {
            return 'Format lokasi rak tidak valid. Gunakan satu huruf kapital, tanda strip, dan dua angka, misalnya A-01 atau B-12.';
        }

        if ($in['kategori_id'] <= 0) {
            return 'Kategori barang wajib dipilih.';
        }

        // Validate minimum stock
        $stokMin = filter_var($in['stok_minimum'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 2147483647],
        ]);
        if ($stokMin === false) {
            return 'Stok minimum harus berupa bilangan bulat 0 atau lebih.';
        }
        $in['stok_minimum'] = $stokMin;

        // Validate prices
        foreach (['harga_beli' => 'Harga beli', 'harga_jual' => 'Harga jual'] as $kolom => $label) {
            if (!is_numeric($in[$kolom]) || $in[$kolom] < 0 || $in[$kolom] > 9999999999999) {
                return $label . ' harus berupa angka yang valid (tidak boleh negatif).';
            }
            $in[$kolom] = number_format((float) $in[$kolom], 2, '.', '');
        }

        return null;
    }
}

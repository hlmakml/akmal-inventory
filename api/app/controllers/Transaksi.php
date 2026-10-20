<?php

// Controller: Transaction management (incoming/outgoing items)
class Transaksi extends Controller
{
    private $transaksiModel;
    private $barangModel;
    private $supplierModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        date_default_timezone_set('Asia/Jakarta');

        // Check login
        if (!isset($_SESSION['user'])) {
            $this->redirect('/auth', 'Silakan login terlebih dahulu.', 'danger');
        }

        $this->transaksiModel = $this->model('Transaksi_model');
        $this->barangModel    = $this->model('Barang_model');
        $this->supplierModel  = $this->model('Supplier_model');
    }

    public function index()
    {
        $this->redirect('/transaksi/masuk');
    }

    // Incoming items page
    public function masuk()
    {
        $data['judul']    = 'Barang Masuk';
        $data['halaman']  = 'masuk';
        $data['hari_ini'] = date('Y-m-d');
        $data['barang']   = $this->barangModel->getAllBarang();
        $data['supplier'] = $this->supplierModel->getAllSupplier();
        $data['riwayat']  = $this->transaksiModel->getTransaksiMasuk();

        $this->view('layout/header', $data);
        $this->view('transaksi/masuk', $data);
        $this->view('layout/footer', $data);
    }

    // Outgoing items page
    public function keluar()
    {
        $data['judul']    = 'Barang Keluar';
        $data['halaman']  = 'keluar';
        $data['hari_ini'] = date('Y-m-d');
        $data['barang']   = $this->barangModel->getAllBarang();
        $data['riwayat']  = $this->transaksiModel->getTransaksiKeluar();

        $this->view('layout/header', $data);
        $this->view('transaksi/keluar', $data);
        $this->view('layout/footer', $data);
    }

    // Save incoming transaction (POST)
    public function prosesMasuk()
    {
        $this->wajibPost('/transaksi/masuk');

        $barangId   = (int) $this->post('barang_id');
        $supplierId = (int) $this->post('supplier_id');
        $jumlah     = $this->validasiJumlah($this->post('jumlah'));
        $tanggal    = $this->post('tanggal_masuk');
        $keterangan = $this->post('keterangan');

        // Validate input
        if ($barangId <= 0) {
            $this->redirect('/transaksi/masuk', 'Pilih barang terlebih dahulu.', 'danger');
        }
        if ($supplierId <= 0) {
            $this->redirect('/transaksi/masuk', 'Pilih supplier terlebih dahulu.', 'danger');
        }
        if ($jumlah === false) {
            $this->redirect('/transaksi/masuk', 'Jumlah harus berupa bilangan bulat minimal 1.', 'danger');
        }
        if (!$this->tanggalValid($tanggal)) {
            $this->redirect('/transaksi/masuk', 'Tanggal masuk tidak valid atau melebihi hari ini.', 'danger');
        }
        if (strlen($keterangan) > 255) {
            $this->redirect('/transaksi/masuk', 'Keterangan maksimal 255 karakter.', 'danger');
        }

        // Save transaction and update stock
        $hasil = $this->transaksiModel->tambahTransaksiMasuk([
            'barang_id'     => $barangId,
            'supplier_id'   => $supplierId,
            'pengguna_id'   => (int) $_SESSION['user']['id'],
            'jumlah'        => $jumlah,
            'tanggal_masuk' => $tanggal,
            'keterangan'    => $keterangan,
        ]);

        $this->redirect('/transaksi/masuk', $hasil['pesan'], $hasil['sukses'] ? 'success' : 'danger');
    }

    // Save outgoing transaction (POST)
    public function prosesKeluar()
    {
        $this->wajibPost('/transaksi/keluar');

        $barangId   = (int) $this->post('barang_id');
        $jumlah     = $this->validasiJumlah($this->post('jumlah'));
        $tanggal    = $this->post('tanggal_keluar');
        $tujuan     = $this->post('tujuan');
        $keterangan = $this->post('keterangan');

        // Validate input
        if ($barangId <= 0) {
            $this->redirect('/transaksi/keluar', 'Pilih barang terlebih dahulu.', 'danger');
        }
        if ($jumlah === false) {
            $this->redirect('/transaksi/keluar', 'Jumlah harus berupa bilangan bulat minimal 1.', 'danger');
        }
        if (!$this->tanggalValid($tanggal)) {
            $this->redirect('/transaksi/keluar', 'Tanggal keluar tidak valid atau melebihi hari ini.', 'danger');
        }
        if ($tujuan === '') {
            $this->redirect('/transaksi/keluar', 'Tujuan barang keluar wajib diisi.', 'danger');
        }
        if (strlen($tujuan) > 150) {
            $this->redirect('/transaksi/keluar', 'Tujuan maksimal 150 karakter.', 'danger');
        }
        if (strlen($keterangan) > 255) {
            $this->redirect('/transaksi/keluar', 'Keterangan maksimal 255 karakter.', 'danger');
        }

        // Save transaction and update stock
        $hasil = $this->transaksiModel->tambahTransaksiKeluar([
            'barang_id'      => $barangId,
            'pengguna_id'    => (int) $_SESSION['user']['id'],
            'jumlah'         => $jumlah,
            'tanggal_keluar' => $tanggal,
            'tujuan'         => $tujuan,
            'keterangan'     => $keterangan,
        ]);

        $this->redirect('/transaksi/keluar', $hasil['pesan'], $hasil['sukses'] ? 'success' : 'danger');
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

    private function wajibPost($kembaliKe)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($kembaliKe);
        }
    }

    private function post($key)
    {
        return (isset($_POST[$key]) && is_string($_POST[$key])) ? trim($_POST[$key]) : '';
    }

    private function validasiJumlah($nilai)
    {
        return filter_var($nilai, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 2147483647],
        ]);
    }

    private function tanggalValid($tanggal)
    {
        $d = DateTime::createFromFormat('Y-m-d', $tanggal);
        if (!$d || $d->format('Y-m-d') !== $tanggal) {
            return false;
        }
        return $tanggal <= date('Y-m-d');
    }
}

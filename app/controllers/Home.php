<?php

// Controller: Dashboard page
class Home extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        date_default_timezone_set('Asia/Jakarta');
    }

    public function index()
    {
        // Check login
        if (!isset($_SESSION['user'])) {
            Flasher::set('Silakan login terlebih dahulu.', 'danger');
            header('Location: ' . BASEURL . '/auth');
            exit;
        }

        $barang    = $this->model('Barang_model');
        $transaksi = $this->model('Transaksi_model');

        $hariIni   = date('Y-m-d');
        $ringkasan = $transaksi->getRingkasanHariIni($hariIni);

        $data['judul']   = 'Dashboard';
        $data['halaman'] = 'dashboard';
        $data['user']    = $_SESSION['user'];
        $data['hari_ini'] = $hariIni;

        $data['total_barang']       = $barang->hitungTotalBarang();
        $data['total_kategori']     = $barang->hitungTotalKategori();
        $data['stok_kritis']        = $barang->hitungStokKritis();
        $data['transaksi_hari_ini'] = $ringkasan['transaksi_masuk'] + $ringkasan['transaksi_keluar'];

        $data['daftar_kritis'] = $barang->getBarangStokKritis(5);
        $data['aktivitas'] = $transaksi->getAktivitasTerbaru(8);
        $data['ringkasan'] = $ringkasan;

        $this->view('layout/header', $data);
        $this->view('home/index', $data);
        $this->view('layout/footer', $data);
    }
}

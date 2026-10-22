<?php

// Controller: Reports - stock and transaction reports (admin only)
class Laporan extends Controller
{
    private $laporanModel;
    private $barangModel;

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

        // Admin only
        if ($_SESSION['user']['role'] !== 'admin') {
            $this->redirect('/home', 'Akses Ditolak! Anda tidak memiliki hak akses ke halaman tersebut.', 'danger');
        }

        $this->laporanModel = $this->model('Laporan_model');
        $this->barangModel  = $this->model('Barang_model');
    }

    // Report page
    public function index()
    {
        $kategori = $this->barangModel->getAllKategori();
        $filter   = $this->filterTersimpan($kategori);

        $stok  = $this->laporanModel->getLaporanStok($filter['kategori']);
        $total = $this->laporanModel->getTotalTransaksi($filter['dari'], $filter['sampai'], $filter['kategori']);

        // Count critical stock items
        $kritis = 0;
        foreach ($stok as $barang) {
            if ((int) $barang['stok'] <= (int) $barang['stok_minimum']) {
                $kritis++;
            }
        }

        $data['judul']          = 'Laporan';
        $data['halaman']        = 'laporan';
        $data['user']           = $_SESSION['user'];
        $data['filter']         = $filter;
        $data['kategori']       = $kategori;
        $data['stok']           = $stok;
        $data['total_masuk']    = $total['masuk'];
        $data['total_keluar']   = $total['keluar'];
        $data['stok_kritis']    = $kritis;
        $data['label_periode']  = $this->labelPeriode($filter['dari'], $filter['sampai']);
        $data['label_kategori'] = $this->labelKategori($filter['kategori'], $kategori);

        $this->view('layout/header', $data);
        $this->view('laporan/index', $data);
        $this->view('layout/footer', $data);
    }

    // Apply filter (POST)
    public function filter()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/laporan');
        }

        $dari       = $this->post('dari');
        $sampai     = $this->post('sampai');
        $kategoriId = (int) $this->post('kategori_id');

        // Validate
        if ($dari !== '' && !$this->tanggalValid($dari)) {
            $this->redirect('/laporan', 'Tanggal awal tidak valid.', 'danger');
        }
        if ($sampai !== '' && !$this->tanggalValid($sampai)) {
            $this->redirect('/laporan', 'Tanggal akhir tidak valid.', 'danger');
        }
        if ($dari !== '' && $sampai !== '' && $dari > $sampai) {
            $this->redirect('/laporan', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.', 'danger');
        }
        if ($kategoriId < 0 || ($kategoriId > 0 && !$this->kategoriAda($kategoriId, $this->barangModel->getAllKategori()))) {
            $this->redirect('/laporan', 'Kategori yang dipilih tidak ditemukan.', 'danger');
        }

        // Save filter in session
        $_SESSION['laporan_filter'] = [
            'dari'     => $dari,
            'sampai'   => $sampai,
            'kategori' => $kategoriId,
        ];

        $this->redirect('/laporan');
    }

    // Reset filter
    public function reset()
    {
        unset($_SESSION['laporan_filter']);
        $this->redirect('/laporan');
    }

    // Export to Excel
    public function exportExcel()
    {
        $kategori = $this->barangModel->getAllKategori();
        $filter   = $this->filterTersimpan($kategori);

        $stok  = $this->laporanModel->getLaporanStok($filter['kategori']);
        $total = $this->laporanModel->getTotalTransaksi($filter['dari'], $filter['sampai'], $filter['kategori']);

        $kritis = 0;
        foreach ($stok as $barang) {
            if ((int) $barang['stok'] <= (int) $barang['stok_minimum']) {
                $kritis++;
            }
        }

        $data['waktu_export']   = date('d/m/Y H:i');
        $data['user']           = $_SESSION['user'];
        $data['stok']           = $stok;
        $data['total_masuk']    = $total['masuk'];
        $data['total_keluar']   = $total['keluar'];
        $data['stok_kritis']    = $kritis;
        $data['label_periode']  = $this->labelPeriode($filter['dari'], $filter['sampai']);
        $data['label_kategori'] = $this->labelKategori($filter['kategori'], $kategori);

        $namaFile = 'Laporan_Stok_Gudang_' . date('Ymd') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $namaFile . '"');
        header('Cache-Control: max-age=0');

        echo "\xEF\xBB\xBF";

        $this->view('laporan/excel', $data);
        exit;
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

    private function post($key)
    {
        return (isset($_POST[$key]) && is_string($_POST[$key])) ? trim($_POST[$key]) : '';
    }

    private function filterTersimpan(array $kategori)
    {
        $filter = ['dari' => '', 'sampai' => '', 'kategori' => 0];
        $simpan = $_SESSION['laporan_filter'] ?? null;

        if (is_array($simpan)) {
            if (isset($simpan['dari']) && is_string($simpan['dari']) && $this->tanggalValid($simpan['dari'])) {
                $filter['dari'] = $simpan['dari'];
            }
            if (isset($simpan['sampai']) && is_string($simpan['sampai']) && $this->tanggalValid($simpan['sampai'])) {
                $filter['sampai'] = $simpan['sampai'];
            }
            if (isset($simpan['kategori']) && $this->kategoriAda((int) $simpan['kategori'], $kategori)) {
                $filter['kategori'] = (int) $simpan['kategori'];
            }
        }

        return $filter;
    }

    private function tanggalValid($tanggal)
    {
        $d = DateTime::createFromFormat('Y-m-d', $tanggal);
        return $d && $d->format('Y-m-d') === $tanggal;
    }

    private function kategoriAda($id, array $kategori)
    {
        foreach ($kategori as $k) {
            if ((int) $k['id'] === (int) $id) {
                return true;
            }
        }
        return false;
    }

    private function labelPeriode($dari, $sampai)
    {
        $tgl = function ($iso) {
            return date('d/m/Y', strtotime($iso));
        };

        if ($dari !== '' && $sampai !== '') {
            return $tgl($dari) . ' s.d. ' . $tgl($sampai);
        }
        if ($dari !== '') {
            return 'mulai ' . $tgl($dari);
        }
        if ($sampai !== '') {
            return 'sampai ' . $tgl($sampai);
        }
        return 'semua tanggal';
    }

    private function labelKategori($id, array $kategori)
    {
        foreach ($kategori as $k) {
            if ((int) $k['id'] === (int) $id) {
                return $k['nama_kategori'];
            }
        }
        return 'Semua kategori';
    }
}

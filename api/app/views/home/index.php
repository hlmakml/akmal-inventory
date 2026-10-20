<?php
/**
 * View : Dashboard
 * --------------------------------------------------------------
 * Letak file : app/views/home/index.php
 * Dipanggil  : Home::index() (di antara layout/header dan layout/footer)
 *
 * Variabel dari controller:
 *   $data['user']               -> data session user (id, nama, username, role)
 *   $data['hari_ini']           -> tanggal hari ini (Y-m-d)
 *   $data['total_barang']       -> jumlah jenis barang
 *   $data['total_kategori']     -> jumlah kategori barang
 *   $data['stok_kritis']        -> jumlah barang dengan stok <= stok_minimum
 *   $data['transaksi_hari_ini'] -> jumlah transaksi masuk + keluar hari ini
 *   $data['daftar_kritis']      -> maksimal 5 barang stok kritis
 *   $data['aktivitas']          -> aktivitas transaksi terbaru (array), atau null jika gagal dimuat
 *   $data['ringkasan']          -> transaksi_masuk, transaksi_keluar, qty_masuk, qty_keluar, supplier_aktif
 */

/** @var array $data */

$e = function ($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
};

// Format angka Indonesia: 1234 -> 1.234
$angka = function ($nilai) {
    return number_format((int) $nilai, 0, ',', '.');
};

$jumlahKritis = (int) $data['stok_kritis'];
$ringkasan    = $data['ringkasan'];

// Halaman Data Barang hanya bisa dibuka admin, jadi tautan ke sana disembunyikan untuk staff
$adalahAdmin = (($data['user']['role'] ?? '') === 'admin');

// Tanggal hari ini dalam bahasa Indonesia, mis. "Senin, 21 September 2026"
$namaHari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$waktuHariIni = strtotime($data['hari_ini']);
$labelTanggal = $namaHari[(int) date('w', $waktuHariIni)] . ', '
    . date('j', $waktuHariIni) . ' '
    . $namaBulan[(int) date('n', $waktuHariIni)] . ' '
    . date('Y', $waktuHariIni);
?>

<!-- ===================== SAPAAN ===================== -->
<div class="mb-4">
    <h1 class="h3 fw-semibold mb-1">Halo, <?= $e($data['user']['nama']); ?></h1>
    <p class="text-muted-app mb-0"><?= $e($labelTanggal); ?></p>
</div>

<!-- ===================== 4 KARTU STATISTIK ===================== -->
<div class="row g-3 mb-4">

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-app p-4 h-100 d-flex align-items-center gap-3">
            <div class="stat-icon steel"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="stat-number"><?= $angka($data['total_barang']); ?></div>
                <div class="fw-medium">Total Barang</div>
                <div class="small text-muted-app">jenis barang terdaftar</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-app p-4 h-100 d-flex align-items-center gap-3">
            <div class="stat-icon steel"><i class="bi bi-tags"></i></div>
            <div>
                <div class="stat-number"><?= $angka($data['total_kategori']); ?></div>
                <div class="fw-medium">Total Kategori</div>
                <div class="small text-muted-app">kategori barang</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-app p-4 h-100 d-flex align-items-center gap-3">
            <div class="stat-icon <?= ($jumlahKritis > 0) ? 'amber' : 'steel'; ?>"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="stat-number"><?= $angka($jumlahKritis); ?></div>
                <div class="fw-medium">Stok Kritis</div>
                <div class="small text-muted-app"><?= ($jumlahKritis > 0) ? 'perlu segera di-restock' : 'semua stok masih aman'; ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card-app p-4 h-100 d-flex align-items-center gap-3">
            <div class="stat-icon steel"><i class="bi bi-arrow-left-right"></i></div>
            <div>
                <div class="stat-number"><?= $angka($data['transaksi_hari_ini']); ?></div>
                <div class="fw-medium">Transaksi Hari Ini</div>
                <div class="small text-muted-app">
                    <?= $angka($ringkasan['transaksi_masuk']); ?> masuk, <?= $angka($ringkasan['transaksi_keluar']); ?> keluar
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ===================== ALERT STOK KRITIS ===================== -->
<?php if ($jumlahKritis > 0) : ?>
    <div class="alert-kritis d-flex flex-column flex-sm-row align-items-sm-center gap-3 p-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-4" style="color: #C77D0A;"></i>
        <div class="flex-grow-1">
            <strong><?= $angka($jumlahKritis); ?> barang</strong>
            stoknya sudah sama dengan atau di bawah batas minimum. Segera lakukan pengadaan.
        </div>
        <?php if ($adalahAdmin) : ?>
            <a href="<?= BASEURL; ?>/barang#kritis" class="btn btn-sm btn-steel">Lihat barang kritis</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="row g-4">

    <!-- ===================== KOLOM KANAN: RINGKASAN LOGISTIK ===================== -->
    <!-- Di layar kecil tampil lebih dulu (order-1), di layar lebar pindah ke kanan -->
    <div class="col-12 col-xl-4 order-1 order-xl-2">
        <div class="card-app">
            <div class="p-3 border-bottom" style="border-color: var(--line) !important;">
                <h2 class="h6 fw-semibold mb-1">Ringkasan logistik hari ini</h2>
                <div class="small text-muted-app"><?= $e($labelTanggal); ?></div>
            </div>

            <div class="px-3">

                <div class="d-flex align-items-center gap-3 py-3 border-bottom" style="border-color: var(--line) !important;">
                    <div class="stat-icon steel"><i class="bi bi-box-arrow-in-down"></i></div>
                    <div class="flex-grow-1">
                        <div class="text-muted-app small">Total barang masuk</div>
                        <div class="fs-5 fw-semibold qty-masuk">+<?= $angka($ringkasan['qty_masuk']); ?> unit</div>
                    </div>
                    <div class="small text-muted-app text-end"><?= $angka($ringkasan['transaksi_masuk']); ?> transaksi</div>
                </div>

                <div class="d-flex align-items-center gap-3 py-3 border-bottom" style="border-color: var(--line) !important;">
                    <div class="stat-icon red"><i class="bi bi-box-arrow-up"></i></div>
                    <div class="flex-grow-1">
                        <div class="text-muted-app small">Total barang keluar</div>
                        <div class="fs-5 fw-semibold qty-keluar">-<?= $angka($ringkasan['qty_keluar']); ?> unit</div>
                    </div>
                    <div class="small text-muted-app text-end"><?= $angka($ringkasan['transaksi_keluar']); ?> transaksi</div>
                </div>

                <div class="d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon amber"><i class="bi bi-truck"></i></div>
                    <div class="flex-grow-1">
                        <div class="text-muted-app small">Supplier aktif</div>
                        <div class="fs-5 fw-semibold"><?= $angka($ringkasan['supplier_aktif']); ?> supplier</div>
                    </div>
                    <div class="small text-muted-app text-end">mengirim barang</div>
                </div>

            </div>
        </div>
    </div>

    <!-- ===================== KOLOM KIRI: TABEL ===================== -->
    <div class="col-12 col-xl-8 order-2 order-xl-1">

        <!-- Tabel: Daftar Barang Perlu Restock -->
        <div class="card-app mb-4">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom" style="border-color: var(--line) !important;">
                <h2 class="h6 fw-semibold mb-0">Daftar barang perlu restock</h2>
                <?php if ($jumlahKritis > 0 && $adalahAdmin) : ?>
                    <a href="<?= BASEURL; ?>/barang#kritis" class="small link-secondary">
                        Lihat semua<?= ($jumlahKritis > count($data['daftar_kritis'])) ? ' (' . $angka($jumlahKritis) . ')' : ''; ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($data['daftar_kritis'])) : ?>
                <div class="text-center text-muted-app p-5">
                    <i class="bi bi-check-circle fs-2 d-block mb-2" style="color: var(--steel);"></i>
                    Semua stok masih di atas batas minimum.
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-app align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama barang</th>
                                <th class="text-end">Stok</th>
                                <th class="text-end">Minimum</th>
                                <th>Lokasi rak</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['daftar_kritis'] as $item) : ?>
                                <tr>
                                    <td class="fw-medium text-nowrap"><?= $e($item['kode_barang']); ?></td>
                                    <td><?= $e($item['nama_barang']); ?></td>
                                    <td class="text-end text-nowrap">
                                        <span class="badge badge-kritis"><?= $angka($item['stok']); ?> <?= $e($item['satuan']); ?></span>
                                    </td>
                                    <td class="text-end"><?= $angka($item['stok_minimum']); ?></td>
                                    <td class="text-nowrap"><?= $e($item['lokasi_rak']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tabel: Aktivitas Transaksi Terbaru -->
        <div class="card-app">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom" style="border-color: var(--line) !important;">
                <h2 class="h6 fw-semibold mb-0">Aktivitas transaksi terbaru</h2>
                <span class="small text-muted-app">8 terakhir</span>
            </div>

            <?php if ($data['aktivitas'] === null) : ?>

                <!-- Query gagal (umumnya karena migrasi database Step 6 belum dijalankan) -->
                <div class="p-4 text-muted-app">
                    <i class="bi bi-info-circle me-1"></i>
                    Aktivitas terbaru belum dapat dimuat. Pastikan file <code>migrasi_step6.sql</code> sudah dijalankan di phpMyAdmin.
                </div>

            <?php elseif (empty($data['aktivitas'])) : ?>

                <div class="text-center text-muted-app p-5">
                    <i class="bi bi-inbox fs-2 d-block mb-2" style="color: var(--steel);"></i>
                    Belum ada transaksi yang tercatat.
                </div>

            <?php else : ?>

                <div class="table-responsive">
                    <table class="table table-app align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Barang</th>
                                <th>Jenis</th>
                                <th class="text-end">Jumlah</th>
                                <th>Petugas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['aktivitas'] as $a) : ?>
                                <?php
                                $waktu   = strtotime($a['dibuat_pada']);
                                $isMasuk = ($a['jenis'] === 'masuk');
                                ?>
                                <tr>
                                    <td class="text-nowrap">
                                        <div class="fw-medium"><?= $e(date('H:i', $waktu)); ?></div>
                                        <div class="small text-muted-app"><?= $e(date('d/m/Y', $waktu)); ?></div>
                                    </td>
                                    <td>
                                        <div><?= $e($a['nama_barang']); ?></div>
                                        <div class="small text-muted-app"><?= $e($a['kode_transaksi']); ?></div>
                                    </td>
                                    <td>
                                        <?php if ($isMasuk) : ?>
                                            <span class="badge badge-masuk"><i class="bi bi-box-arrow-in-down me-1"></i>Masuk</span>
                                        <?php else : ?>
                                            <span class="badge badge-keluar"><i class="bi bi-box-arrow-up me-1"></i>Keluar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <span class="<?= $isMasuk ? 'qty-masuk' : 'qty-keluar'; ?>"><?= $isMasuk ? '+' : '-'; ?><?= $angka($a['jumlah']); ?></span>
                                        <span class="small text-muted-app"><?= $e($a['satuan']); ?></span>
                                    </td>
                                    <td><?= $e($a['nama_pengguna']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>

    </div>
</div>

<?php
/**
 * View : Laporan
 * --------------------------------------------------------------
 * Letak file : app/views/laporan/index.php
 * Dipanggil  : Laporan::index() (di antara layout/header dan layout/footer)
 *
 * Variabel dari controller:
 *   $data['user']           -> data session user
 *   $data['filter']         -> filter yang SEDANG diterapkan: dari, sampai, kategori (id, 0 = semua)
 *   $data['kategori']       -> daftar kategori dari database (id, nama_kategori)
 *   $data['stok']           -> daftar barang (sudah disaring kategori): kode_barang, nama_barang, satuan, stok, stok_minimum
 *   $data['total_masuk']    -> total kuantitas barang masuk pada rentang tanggal (sudah disaring)
 *   $data['total_keluar']   -> total kuantitas barang keluar pada rentang tanggal (sudah disaring)
 *   $data['stok_kritis']    -> jumlah jenis barang dengan stok <= stok minimum (sudah disaring kategori)
 *   $data['label_periode']  -> teks periode, mis. "01/09/2026 s.d. 20/09/2026" atau "semua tanggal"
 *   $data['label_kategori'] -> nama kategori terpilih atau "Semua kategori"
 *
 * Semua filter diproses di server (Laporan.php + Laporan_model.php) dan disimpan di session,
 * sehingga tombol "Export Excel" (menuju /laporan/exportExcel) selalu mengunduh data yang
 * sama persis dengan yang sedang tampil di halaman ini.
 */

/** @var array $data */

$e = function ($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
};

// Format angka Indonesia: 1234 -> 1.234
$angka = function ($nilai) {
    return number_format((int) $nilai, 0, ',', '.');
};

$f            = $data['filter'];
$jumlahBarang = count($data['stok']);
?>

<!-- ===================== JUDUL HALAMAN ===================== -->
<div class="mb-4">
    <h1 class="h3 fw-semibold mb-1">Laporan</h1>
    <p class="text-muted-app mb-0">Laporan stok barang serta total barang masuk dan keluar.</p>
</div>

<!-- ===================== FILTER + TOMBOL EXPORT EXCEL ===================== -->
<!-- Filter langsung diterapkan (auto-submit) begitu tanggal atau kategori diganti;
     lihat script di bawah. Tidak perlu tombol "Terapkan". -->
<form id="formFilter" method="POST" action="<?= BASEURL; ?>/laporan/filter" class="card-app p-3 mb-4">

    <div class="row g-3 align-items-end">

        <div class="col-6 col-lg-2">
            <label for="inputDari" class="form-label fw-medium">Dari tanggal</label>
            <input type="date" class="form-control" id="inputDari" name="dari" value="<?= $e($f['dari']); ?>">
        </div>

        <div class="col-6 col-lg-2">
            <label for="inputSampai" class="form-label fw-medium">Sampai tanggal</label>
            <input type="date" class="form-control" id="inputSampai" name="sampai" value="<?= $e($f['sampai']); ?>">
        </div>

        <div class="col-12 col-lg-3">
            <label for="inputKategori" class="form-label fw-medium">Kategori Barang</label>
            <select class="form-select" id="inputKategori" name="kategori_id">
                <option value="0">Semua kategori</option>
                <?php foreach ($data['kategori'] as $k) : ?>
                    <option value="<?= $e($k['id']); ?>" <?= ((int) $k['id'] === (int) $f['kategori']) ? 'selected' : ''; ?>>
                        <?= $e($k['nama_kategori']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-lg-auto">
            <a href="<?= BASEURL; ?>/laporan/reset" class="btn btn-outline-secondary">Reset</a>
        </div>

        <!-- Export Excel (aksen amber), di sisi kanan baris filter. Tautan biasa menuju
             /laporan/exportExcel, yang membaca filter dari session (selalu sinkron). -->
        <div class="col-12 col-lg text-lg-end">
            <a href="<?= BASEURL; ?>/laporan/exportExcel" class="btn btn-amber">
                <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
            </a>
        </div>

    </div>
</form>

<!-- ===================== 3 KARTU STATISTIK ===================== -->
<div class="row g-3 mb-4">

    <div class="col-12 col-sm-4">
        <div class="card-app p-4 h-100 d-flex align-items-center gap-3">
            <div class="stat-icon steel"><i class="bi bi-box-arrow-in-down"></i></div>
            <div>
                <div class="stat-number"><?= $angka($data['total_masuk']); ?></div>
                <div class="fw-medium">Total Barang Masuk</div>
                <div class="small text-muted-app">unit, <?= $e($data['label_periode']); ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-4">
        <div class="card-app p-4 h-100 d-flex align-items-center gap-3">
            <div class="stat-icon red"><i class="bi bi-box-arrow-up"></i></div>
            <div>
                <div class="stat-number"><?= $angka($data['total_keluar']); ?></div>
                <div class="fw-medium">Total Barang Keluar</div>
                <div class="small text-muted-app">unit, <?= $e($data['label_periode']); ?></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-4">
        <div class="card-app p-4 h-100 d-flex align-items-center gap-3">
            <div class="stat-icon amber"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="stat-number"><?= $angka($data['stok_kritis']); ?></div>
                <div class="fw-medium">Barang Stok Kritis</div>
                <div class="small text-muted-app">dari <?= $angka($jumlahBarang); ?> jenis barang</div>
            </div>
        </div>
    </div>

</div>

<!-- ===================== TABEL LAPORAN ===================== -->
<div class="card-app">
    <div class="p-3 border-bottom" style="border-color: var(--line) !important;">
        <h2 class="h6 fw-semibold mb-1">Daftar stok barang</h2>
        <div class="small text-muted-app">
            Kategori: <?= $e($data['label_kategori']); ?>,
            <?= $angka($jumlahBarang); ?> jenis barang. Stok yang ditampilkan adalah stok saat ini.
        </div>
    </div>

    <?php if ($jumlahBarang === 0) : ?>

        <div class="text-center text-muted-app p-5">
            <i class="bi bi-inbox fs-2 d-block mb-2" style="color: var(--steel);"></i>
            Tidak ada barang pada kategori ini.
        </div>

    <?php else : ?>

        <div class="table-responsive">
            <table class="table table-app align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th class="text-end">Stok Saat Ini</th>
                        <th class="text-end">Stok Minimum</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['stok'] as $b) : ?>
                        <?php $kritis = ((int) $b['stok'] <= (int) $b['stok_minimum']); ?>
                        <tr>
                            <td class="fw-medium text-nowrap"><?= $e($b['kode_barang']); ?></td>
                            <td><?= $e($b['nama_barang']); ?></td>
                            <td class="text-end text-nowrap">
                                <span class="fw-semibold"><?= $angka($b['stok']); ?></span>
                                <span class="small text-muted-app"><?= $e($b['satuan']); ?></span>
                            </td>
                            <td class="text-end"><?= $angka($b['stok_minimum']); ?></td>
                            <td class="text-center">
                                <?php if ($kritis) : ?>
                                    <!-- Stok saat ini <= stok minimum -->
                                    <span class="badge rounded-pill text-bg-danger">Kritis</span>
                                <?php else : ?>
                                    <!-- Stok saat ini > stok minimum -->
                                    <span class="badge rounded-pill text-bg-success">Aman</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>


<!-- Script halaman Laporan (lihat public/js/laporan.js) -->
<script src="<?= BASEURL; ?>/js/laporan.js"></script>

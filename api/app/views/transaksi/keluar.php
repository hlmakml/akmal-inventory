<?php
/**
 * View : Barang Keluar
 * --------------------------------------------------------------
 * Letak file : app/views/transaksi/keluar.php
 * Dipanggil  : Transaksi::keluar() (di antara layout/header dan layout/footer)
 *
 * Variabel dari controller:
 *   $data['barang']   -> daftar barang beserta stok (untuk dropdown)
 *   $data['riwayat']  -> riwayat barang keluar
 *   $data['hari_ini'] -> tanggal hari ini (Y-m-d)
 */

/** @var array $data */

$e = function ($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
};

$bisaInput = !empty($data['barang']);

// Halaman Data Barang hanya bisa dibuka admin: staff diarahkan untuk menghubungi admin
$adalahAdmin = (($_SESSION['user']['role'] ?? '') === 'admin');
?>

<!-- ===================== JUDUL HALAMAN ===================== -->
<div class="mb-4">
    <h1 class="h3 fw-semibold mb-1">Barang Keluar</h1>
    <p class="text-muted-app mb-0">Catat barang yang dikeluarkan dari gudang. Stok berkurang otomatis setelah disimpan.</p>
</div>

<!-- ===================== FORM TRANSAKSI ===================== -->
<div class="card-app p-4 mb-4">
    <h2 class="h6 fw-semibold mb-3">Catat barang keluar</h2>

    <?php if (!$bisaInput) : ?>

        <p class="text-muted-app mb-0">
            Belum ada barang.
            <?php if ($adalahAdmin) : ?>
                Tambahkan barang di <a href="<?= BASEURL; ?>/barang">Data Barang</a> terlebih dahulu.
            <?php else : ?>
                Silakan hubungi admin gudang untuk menambahkannya.
            <?php endif; ?>
        </p>

    <?php else : ?>

        <form id="formTransaksi" method="POST" action="<?= BASEURL; ?>/transaksi/prosesKeluar">
            <div class="row g-3">

                <div class="col-md-6">
                    <label for="pilihBarang" class="form-label fw-medium">Barang</label>
                    <select class="form-select" id="pilihBarang" name="barang_id" required>
                        <option value="">Pilih barang</option>
                        <?php foreach ($data['barang'] as $b) : ?>
                            <?php $habis = ((int) $b['stok'] <= 0); ?>
                            <option value="<?= $e($b['id']); ?>"
                                data-stok="<?= $e($b['stok']); ?>"
                                data-satuan="<?= $e($b['satuan']); ?>"
                                <?= $habis ? 'disabled' : ''; ?>>
                                <?= $e($b['kode_barang'] . ' - ' . $b['nama_barang']); ?>
                                (<?= $habis ? 'stok habis' : 'stok ' . number_format((int) $b['stok'], 0, ',', '.') . ' ' . $e($b['satuan']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text" id="infoStok">Pilih barang untuk melihat stok yang tersedia.</div>
                </div>

                <div class="col-md-6">
                    <label for="inputTujuan" class="form-label fw-medium">Tujuan</label>
                    <input type="text" class="form-control" id="inputTujuan" name="tujuan"
                        maxlength="150" placeholder="Contoh: Divisi IT, Toko Cabang, atau nama pelanggan" required>
                </div>

                <div class="col-md-3">
                    <label for="inputJumlah" class="form-label fw-medium">Jumlah keluar</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="inputJumlah" name="jumlah"
                            min="1" step="1" inputmode="numeric" placeholder="0" required>
                        <span class="input-group-text" id="satuanBarang">satuan</span>
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="inputTanggal" class="form-label fw-medium">Tanggal keluar</label>
                    <input type="date" class="form-control" id="inputTanggal" name="tanggal_keluar"
                        value="<?= $e($data['hari_ini']); ?>" max="<?= $e($data['hari_ini']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="inputKeterangan" class="form-label fw-medium">Keterangan</label>
                    <input type="text" class="form-control" id="inputKeterangan" name="keterangan"
                        maxlength="255" placeholder="Opsional, misalnya nomor permintaan barang">
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-steel" id="tombolSimpan">
                    <i class="bi bi-check2 me-1"></i>Simpan barang keluar
                </button>
            </div>
        </form>

    <?php endif; ?>
</div>

<!-- ===================== RIWAYAT TRANSAKSI ===================== -->
<div class="card-app">
    <div class="p-3 border-bottom" style="border-color: var(--line) !important;">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-4">
                <h2 class="h6 fw-semibold mb-1">Riwayat barang keluar</h2>
                <div class="small text-muted-app" id="infoRiwayat"><?= count($data['riwayat']); ?> transaksi</div>
            </div>

            <?php if (!empty($data['riwayat'])) : ?>
                <!-- Filter tanggal (diproses di browser, tanpa reload halaman) -->
                <div class="col-12 col-lg-8">
                    <div class="row g-2 justify-content-lg-end align-items-end">
                        <div class="col-6 col-sm-3">
                            <label for="filterDari" class="form-label small mb-1">Dari tanggal</label>
                            <input type="date" class="form-control form-control-sm" id="filterDari">
                        </div>
                        <div class="col-6 col-sm-3">
                            <label for="filterSampai" class="form-label small mb-1">Sampai tanggal</label>
                            <input type="date" class="form-control form-control-sm" id="filterSampai">
                        </div>
                        <div class="col-8 col-sm-4">
                            <label for="urutRiwayat" class="form-label small mb-1">Urutkan</label>
                            <!-- Pengurutan dijalankan oleh script di layout/footer.php lewat atribut data-urut-tabel.
                                 "Nama" = nama barang; "ditambahkan" mengikuti urutan pencatatan (id transaksi). -->
                            <select class="form-select form-select-sm" id="urutRiwayat" data-urut-tabel="tabelRiwayat">
                                <option value="terbaru" selected>Terakhir ditambahkan</option>
                                <option value="terlama">Pertama ditambahkan</option>
                                <option value="nama-asc">Nama barang (A-Z)</option>
                                <option value="nama-desc">Nama barang (Z-A)</option>
                            </select>
                        </div>
                        <div class="col-4 col-sm-2 col-lg-auto">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="resetFilter" title="Kosongkan filter tanggal">Reset</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($data['riwayat'])) : ?>

        <div class="text-center text-muted-app p-5">
            <i class="bi bi-inbox fs-2 d-block mb-2" style="color: var(--steel);"></i>
            Belum ada transaksi barang keluar.
        </div>

    <?php else : ?>

        <div class="table-responsive">
            <table class="table table-app table-hover align-middle mb-0" id="tabelRiwayat">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode transaksi</th>
                        <th>Barang</th>
                        <th class="text-end">Jumlah</th>
                        <th>Tujuan</th>
                        <th>Petugas</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['riwayat'] as $t) : ?>
                        <tr data-tanggal="<?= $e($t['tanggal_keluar']); ?>"
                            data-nama="<?= $e($t['nama_barang']); ?>"
                            data-waktu="<?= $e($t['id']); ?>">
                            <td class="text-nowrap"><?= $e(date('d/m/Y', strtotime($t['tanggal_keluar']))); ?></td>
                            <td class="text-nowrap fw-medium"><?= $e($t['kode_transaksi']); ?></td>
                            <td>
                                <div><?= $e($t['nama_barang']); ?></div>
                                <div class="small text-muted-app"><?= $e($t['kode_barang']); ?></div>
                            </td>
                            <td class="text-end text-nowrap">
                                <span class="qty-keluar">-<?= number_format((int) $t['jumlah'], 0, ',', '.'); ?></span>
                                <span class="small text-muted-app"><?= $e($t['satuan']); ?></span>
                            </td>
                            <td><?= $e($t['tujuan']); ?></td>
                            <td><?= $e($t['nama_pengguna']); ?></td>
                            <td><?= $e($t['keterangan']); ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <tr id="barisKosong" class="d-none">
                        <td colspan="7" class="text-center text-muted-app py-5">Tidak ada transaksi pada rentang tanggal tersebut.</td>
                    </tr>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>


<!-- Script halaman Barang Keluar (lihat public/js/transaksi-keluar.js) -->
<script src="<?= BASEURL; ?>/js/transaksi-keluar.js"></script>

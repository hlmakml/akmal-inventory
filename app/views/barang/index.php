<?php
/**
 * View : Data Barang (CRUD)
 * --------------------------------------------------------------
 * Letak file : app/views/barang/index.php
 * Dipanggil  : Barang::index() (di antara layout/header dan layout/footer)
 *
 * Variabel dari controller:
 *   $data['barang']   -> semua barang (+ nama_kategori)
 *   $data['kategori'] -> daftar kategori untuk dropdown
 *
 * Satu modal form dipakai untuk Tambah dan Ubah. Tombol "Ubah" di tiap baris
 * membawa data barang lewat atribut data-*, lalu diisikan ke form oleh JavaScript
 * di bagian bawah file ini.
 */

/** @var array $data */

// Helper kecil: escape output HTML
$e = function ($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
};

// Helper kecil: format rupiah (pakai 2 desimal hanya jika memang ada pecahan)
$rp = function ($nilai) {
    $angka = (float) $nilai;
    $desimal = (floor($angka) == $angka) ? 0 : 2;
    return 'Rp ' . number_format($angka, $desimal, ',', '.');
};

$adaBarang = !empty($data['barang']);
?>

<!-- ===================== JUDUL HALAMAN ===================== -->
<div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-semibold mb-1">Data Barang</h1>
        <p class="text-muted-app mb-0"><?= count($data['barang']); ?> jenis barang terdaftar.</p>
    </div>
    <button type="button" class="btn btn-steel" data-bs-toggle="modal" data-bs-target="#modalBarang" data-mode="tambah">
        <i class="bi bi-plus-lg me-1"></i>Tambah barang
    </button>
</div>

<?php if (!$adaBarang) : ?>

    <!-- ===================== KONDISI KOSONG ===================== -->
    <div class="card-app text-center p-5">
        <i class="bi bi-box-seam fs-1 d-block mb-2" style="color: var(--steel);"></i>
        <h2 class="h5 fw-semibold">Belum ada barang</h2>
        <p class="text-muted-app mb-3">Tambahkan barang pertama untuk mulai mencatat stok gudang.</p>
        <div>
            <button type="button" class="btn btn-steel" data-bs-toggle="modal" data-bs-target="#modalBarang" data-mode="tambah">
                <i class="bi bi-plus-lg me-1"></i>Tambah barang
            </button>
        </div>
    </div>

<?php else : ?>

    <!-- ===================== PENCARIAN & FILTER ===================== -->
    <div class="card-app p-3 mb-3">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" id="cariBarang" class="form-control"
                        placeholder="Cari kode, nama, kategori, atau lokasi rak" aria-label="Cari barang">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <!-- Pengurutan dijalankan oleh script di layout/footer.php lewat atribut data-urut-tabel -->
                <select class="form-select" id="urutBarang" data-urut-tabel="tabelBarang" aria-label="Urutkan barang">
                    <option value="nama-asc" selected>Nama (A-Z)</option>
                    <option value="nama-desc">Nama (Z-A)</option>
                    <option value="terbaru">Terakhir ditambahkan</option>
                    <option value="terlama">Pertama ditambahkan</option>
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex justify-content-md-end">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="filterKritis">
                    <label class="form-check-label" for="filterKritis">Tampilkan stok kritis saja</label>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== TABEL BARANG ===================== -->
    <div class="card-app">
        <div class="table-responsive">
            <table class="table table-app table-hover align-middle mb-0" id="tabelBarang">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama barang</th>
                        <th>Kategori</th>
                        <th class="text-end">Stok</th>
                        <th class="text-end">Minimum</th>
                        <th class="text-end">Harga beli</th>
                        <th class="text-end">Harga jual</th>
                        <th>Rak</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['barang'] as $b) : ?>
                        <?php $kritis = ((int) $b['stok'] <= (int) $b['stok_minimum']); ?>
                        <tr data-cari="<?= $e($b['kode_barang'] . ' ' . $b['nama_barang'] . ' ' . $b['nama_kategori'] . ' ' . $b['lokasi_rak']); ?>"
                            data-kritis="<?= $kritis ? '1' : '0'; ?>"
                            data-nama="<?= $e($b['nama_barang']); ?>"
                            data-waktu="<?= $e($b['id']); ?>">
                            <td class="fw-medium text-nowrap"><?= $e($b['kode_barang']); ?></td>
                            <td><?= $e($b['nama_barang']); ?></td>
                            <td><?= $e($b['nama_kategori']); ?></td>
                            <td class="text-end text-nowrap">
                                <span class="fw-semibold"><?= number_format((int) $b['stok'], 0, ',', '.'); ?></span>
                                <span class="text-muted-app small"><?= $e($b['satuan']); ?></span>
                                <?php if ($kritis) : ?>
                                    <span class="d-block"><span class="badge badge-kritis">Stok kritis</span></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><?= number_format((int) $b['stok_minimum'], 0, ',', '.'); ?></td>
                            <td class="text-end text-nowrap"><?= $rp($b['harga_beli']); ?></td>
                            <td class="text-end text-nowrap"><?= $rp($b['harga_jual']); ?></td>
                            <td class="text-nowrap"><?= $e($b['lokasi_rak']); ?></td>
                            <td class="text-end text-nowrap">
                                <!-- Tombol ubah: membawa semua data barang lewat atribut data-* -->
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal" data-bs-target="#modalBarang" data-mode="ubah"
                                    data-id="<?= $e($b['id']); ?>"
                                    data-kode="<?= $e($b['kode_barang']); ?>"
                                    data-nama="<?= $e($b['nama_barang']); ?>"
                                    data-kategori="<?= $e($b['kategori_id']); ?>"
                                    data-satuan="<?= $e($b['satuan']); ?>"
                                    data-stok="<?= $e($b['stok']); ?>"
                                    data-stok-min="<?= $e($b['stok_minimum']); ?>"
                                    data-harga-beli="<?= $e($b['harga_beli']); ?>"
                                    data-harga-jual="<?= $e($b['harga_jual']); ?>"
                                    data-rak="<?= $e($b['lokasi_rak']); ?>"
                                    title="Ubah" aria-label="Ubah <?= $e($b['nama_barang']); ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <!-- Tombol hapus: membuka modal konfirmasi -->
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal" data-bs-target="#modalHapus"
                                    data-id="<?= $e($b['id']); ?>"
                                    data-nama="<?= $e($b['nama_barang']); ?>"
                                    title="Hapus" aria-label="Hapus <?= $e($b['nama_barang']); ?>">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Muncul jika pencarian/filter tidak menemukan apa pun -->
                    <tr id="barisKosong" class="d-none">
                        <td colspan="9" class="text-center text-muted-app py-5">Tidak ada barang yang cocok dengan pencarian.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>


<!-- ===================== MODAL TAMBAH / UBAH ===================== -->
<div class="modal fade" id="modalBarang" tabindex="-1" aria-labelledby="judulModalBarang" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <!-- URL tujuan form diganti oleh JavaScript sesuai mode (tambah / ubah) -->
            <form id="formBarang" method="POST"
                action="<?= BASEURL; ?>/barang/tambah"
                data-url-tambah="<?= BASEURL; ?>/barang/tambah"
                data-url-ubah="<?= BASEURL; ?>/barang/ubah">

                <div class="modal-header">
                    <h2 class="modal-title h5 fw-semibold" id="judulModalBarang">Tambah barang</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <!-- id hanya terisi saat mode ubah -->
                    <input type="hidden" name="id" id="inputId" value="">

                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="inputKode" class="form-label fw-medium">Kode barang</label>
                            <!-- Hanya tampilan: tanpa atribut name, jadi tidak dikirim ke server.
                                 Kode dibuat otomatis oleh sistem (BRG-001, BRG-002, ...) dan tidak bisa diubah. -->
                            <input type="text" class="form-control" id="inputKode"
                                placeholder="Dibuat otomatis" readonly aria-describedby="infoKode">
                            <div class="form-text" id="infoKode">Diisi otomatis saat disimpan.</div>
                        </div>
                        <div class="col-md-7">
                            <label for="inputNama" class="form-label fw-medium">Nama barang</label>
                            <input type="text" class="form-control" id="inputNama" name="nama_barang"
                                maxlength="150" placeholder="Nama lengkap barang" required>
                        </div>

                        <div class="col-md-6">
                            <label for="inputKategori" class="form-label fw-medium">Kategori</label>
                            <select class="form-select" id="inputKategori" name="kategori_id" required>
                                <option value="">Pilih kategori</option>
                                <?php foreach ($data['kategori'] as $k) : ?>
                                    <option value="<?= $e($k['id']); ?>"><?= $e($k['nama_kategori']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="inputSatuan" class="form-label fw-medium">Satuan</label>
                            <input type="text" class="form-control" id="inputSatuan" name="satuan"
                                maxlength="20" list="daftarSatuan" placeholder="pcs, box, rim, dus..." required>
                            <datalist id="daftarSatuan">
                                <option value="pcs">
                                <option value="box">
                                <option value="dus">
                                <option value="rim">
                                <option value="roll">
                                <option value="set">
                                <option value="botol">
                                <option value="kg">
                            </datalist>
                        </div>

                        <div class="col-md-4">
                            <label for="inputStok" class="form-label fw-medium">Stok saat ini</label>
                            <!-- Hanya tampilan: tanpa atribut name, jadi tidak ikut terkirim ke server -->
                            <input type="number" class="form-control" id="inputStok"
                                value="0" disabled aria-describedby="infoStok">
                            <div class="form-text" id="infoStok">Berubah otomatis lewat transaksi barang masuk dan keluar.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="inputStokMin" class="form-label fw-medium">Stok minimum</label>
                            <input type="number" class="form-control" id="inputStokMin" name="stok_minimum"
                                min="0" step="1" value="0" inputmode="numeric" required>
                        </div>
                        <div class="col-md-4">
                            <label for="inputRak" class="form-label fw-medium">Lokasi rak</label>
                            <input type="text" class="form-control" id="inputRak" name="lokasi_rak"
                                maxlength="4" pattern="[A-Z]-[0-9]{2}" placeholder="A-01" autocomplete="off"
                                title="Satu huruf kapital, tanda strip, dan dua angka. Contoh: A-01 atau B-12"
                                aria-describedby="infoRak" style="text-transform: uppercase;">
                            <div class="form-text" id="infoRak">Format: A-01, B-12</div>
                        </div>

                        <div class="col-md-6">
                            <label for="inputHargaBeli" class="form-label fw-medium">Harga beli</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="inputHargaBeli" name="harga_beli"
                                    min="0" step="0.01" value="0" inputmode="decimal" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="inputHargaJual" class="form-label fw-medium">Harga jual</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="inputHargaJual" name="harga_jual"
                                    min="0" step="0.01" value="0" inputmode="decimal" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-steel" id="tombolSimpan">Simpan barang</button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- ===================== MODAL KONFIRMASI HAPUS ===================== -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="judulModalHapus" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Dikirim dengan POST; id barang ditambahkan ke URL oleh JavaScript -->
            <form id="formHapus" method="POST" action="#" data-url="<?= BASEURL; ?>/barang/hapus/">
                <div class="modal-header">
                    <h2 class="modal-title h5 fw-semibold" id="judulModalHapus">Hapus barang</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Hapus <strong id="namaHapus"></strong> dari daftar barang?</p>
                    <p class="text-muted-app small mb-0">
                        Barang yang sudah tercatat di transaksi masuk atau keluar tidak dapat dihapus.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus barang</button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- Script halaman Data Barang (lihat public/js/barang.js) -->
<script src="<?= BASEURL; ?>/js/barang.js"></script>

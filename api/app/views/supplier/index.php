<?php
/**
 * View : Supplier
 * --------------------------------------------------------------
 * Letak file : app/views/supplier/index.php
 * Dipanggil  : Supplier::index() (di antara layout/header dan layout/footer)
 *
 * Variabel dari controller:
 *   $data['supplier'] -> semua supplier
 *
 * Satu modal form dipakai untuk Tambah dan Ubah (data baris dibawa lewat atribut
 * data-* pada tombol Ubah, lalu diisikan ke form oleh JavaScript di bawah).
 */

/** @var array $data */

$e = function ($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
};

$adaSupplier = !empty($data['supplier']);
?>

<!-- ===================== JUDUL HALAMAN ===================== -->
<div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-semibold mb-1">Supplier</h1>
        <p class="text-muted-app mb-0"><?= count($data['supplier']); ?> supplier terdaftar.</p>
    </div>
    <button type="button" class="btn btn-steel" data-bs-toggle="modal" data-bs-target="#modalSupplier" data-mode="tambah">
        <i class="bi bi-plus-lg me-1"></i>Tambah supplier
    </button>
</div>

<?php if (!$adaSupplier) : ?>

    <!-- ===================== KONDISI KOSONG ===================== -->
    <div class="card-app text-center p-5">
        <i class="bi bi-truck fs-1 d-block mb-2" style="color: var(--steel);"></i>
        <h2 class="h5 fw-semibold">Belum ada supplier</h2>
        <p class="text-muted-app mb-3">Tambahkan supplier agar bisa dipilih saat mencatat barang masuk.</p>
        <div>
            <button type="button" class="btn btn-steel" data-bs-toggle="modal" data-bs-target="#modalSupplier" data-mode="tambah">
                <i class="bi bi-plus-lg me-1"></i>Tambah supplier
            </button>
        </div>
    </div>

<?php else : ?>

    <!-- ===================== PENCARIAN ===================== -->
    <div class="card-app p-3 mb-3">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" id="cariSupplier" class="form-control"
                        placeholder="Cari kode, nama, kontak, email, atau alamat" aria-label="Cari supplier">
                </div>
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <!-- Pengurutan dijalankan oleh script di layout/footer.php lewat atribut data-urut-tabel -->
                <select class="form-select" id="urutSupplier" data-urut-tabel="tabelSupplier" aria-label="Urutkan supplier">
                    <option value="nama-asc" selected>Nama (A-Z)</option>
                    <option value="nama-desc">Nama (Z-A)</option>
                    <option value="terbaru">Terakhir ditambahkan</option>
                    <option value="terlama">Pertama ditambahkan</option>
                </select>
            </div>
        </div>
    </div>

    <!-- ===================== TABEL SUPPLIER ===================== -->
    <div class="card-app">
        <div class="table-responsive">
            <table class="table table-app table-hover align-middle mb-0" id="tabelSupplier">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama supplier</th>
                        <th>Kontak</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['supplier'] as $s) : ?>
                        <tr data-cari="<?= $e($s['kode_supplier'] . ' ' . $s['nama_supplier'] . ' ' . $s['kontak'] . ' ' . $s['email'] . ' ' . $s['alamat']); ?>"
                            data-nama="<?= $e($s['nama_supplier']); ?>"
                            data-waktu="<?= $e($s['id']); ?>">
                            <td class="fw-medium text-nowrap"><?= $e($s['kode_supplier']); ?></td>
                            <td class="fw-medium"><?= $e($s['nama_supplier']); ?></td>
                            <td class="text-nowrap"><?= $e($s['kontak']); ?></td>
                            <td><?= $e($s['email']); ?></td>
                            <td style="min-width: 14rem;"><?= $e($s['alamat']); ?></td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal" data-bs-target="#modalSupplier" data-mode="ubah"
                                    data-id="<?= $e($s['id']); ?>"
                                    data-kode="<?= $e($s['kode_supplier']); ?>"
                                    data-nama="<?= $e($s['nama_supplier']); ?>"
                                    data-kontak="<?= $e($s['kontak']); ?>"
                                    data-email="<?= $e($s['email']); ?>"
                                    data-alamat="<?= $e($s['alamat']); ?>"
                                    title="Ubah" aria-label="Ubah <?= $e($s['nama_supplier']); ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal" data-bs-target="#modalHapus"
                                    data-id="<?= $e($s['id']); ?>"
                                    data-nama="<?= $e($s['nama_supplier']); ?>"
                                    title="Hapus" aria-label="Hapus <?= $e($s['nama_supplier']); ?>">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <tr id="barisKosong" class="d-none">
                        <td colspan="6" class="text-center text-muted-app py-5">Tidak ada supplier yang cocok dengan pencarian.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>


<!-- ===================== MODAL TAMBAH / UBAH ===================== -->
<div class="modal fade" id="modalSupplier" tabindex="-1" aria-labelledby="judulModalSupplier" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <form id="formSupplier" method="POST"
                action="<?= BASEURL; ?>/supplier/tambah"
                data-url-tambah="<?= BASEURL; ?>/supplier/tambah"
                data-url-ubah="<?= BASEURL; ?>/supplier/ubah">

                <div class="modal-header">
                    <h2 class="modal-title h5 fw-semibold" id="judulModalSupplier">Tambah supplier</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="inputId" value="">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="inputKode" class="form-label fw-medium">Kode supplier</label>
                            <!-- Hanya tampilan: tanpa atribut name, jadi tidak dikirim ke server.
                                 Kode dibuat otomatis oleh sistem (SPL-001, SPL-002, ...) dan tidak bisa diubah. -->
                            <input type="text" class="form-control" id="inputKode"
                                placeholder="Dibuat otomatis" readonly aria-describedby="infoKode">
                            <div class="form-text" id="infoKode">Diisi otomatis saat disimpan.</div>
                        </div>
                        <div class="col-md-8">
                            <label for="inputNama" class="form-label fw-medium">Nama supplier</label>
                            <input type="text" class="form-control" id="inputNama" name="nama_supplier"
                                maxlength="150" placeholder="Contoh: PT Sinar Elektronik Jaya" required>
                        </div>

                        <div class="col-md-6">
                            <label for="inputKontak" class="form-label fw-medium">Kontak</label>
                            <input type="tel" class="form-control" id="inputKontak" name="kontak"
                                maxlength="20" placeholder="Contoh: 081234567890">
                        </div>
                        <div class="col-md-6">
                            <label for="inputEmail" class="form-label fw-medium">Email</label>
                            <input type="email" class="form-control" id="inputEmail" name="email"
                                maxlength="100" placeholder="Contoh: sales@perusahaan.co.id">
                        </div>

                        <div class="col-12">
                            <label for="inputAlamat" class="form-label fw-medium">Alamat</label>
                            <textarea class="form-control" id="inputAlamat" name="alamat" rows="3"
                                maxlength="1000" placeholder="Alamat lengkap supplier"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-steel" id="tombolSimpan">Simpan supplier</button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- ===================== MODAL KONFIRMASI HAPUS ===================== -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="judulModalHapus" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Dikirim dengan POST; id supplier ditambahkan ke URL oleh JavaScript -->
            <form id="formHapus" method="POST" action="#" data-url="<?= BASEURL; ?>/supplier/hapus/">
                <div class="modal-header">
                    <h2 class="modal-title h5 fw-semibold" id="judulModalHapus">Hapus supplier</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Hapus <strong id="namaHapus"></strong> dari daftar supplier?</p>
                    <p class="text-muted-app small mb-0">
                        Supplier yang sudah tercatat di transaksi barang masuk tidak dapat dihapus.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus supplier</button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- Script halaman Supplier (lihat public/js/supplier.js) -->
<script src="<?= BASEURL; ?>/js/supplier.js"></script>

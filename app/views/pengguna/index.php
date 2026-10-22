<?php
/**
 * View : Manajemen Pengguna
 * --------------------------------------------------------------
 * Letak file : app/views/pengguna/index.php
 * Dipanggil  : Pengguna::index() (di antara layout/header dan layout/footer)
 *
 * Variabel dari controller:
 *   $data['pengguna'] -> semua pengguna (tanpa password)
 *   $data['id_saya']  -> id akun yang sedang login
 */

/** @var array $data */

$e = function ($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
};

// 'staff_gudang' -> 'Staff gudang'
$labelRole = function ($role) {
    return ucfirst(str_replace('_', ' ', $role));
};
?>

<!-- ===================== JUDUL HALAMAN ===================== -->
<div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h3 fw-semibold mb-1">Manajemen Pengguna</h1>
        <p class="text-muted-app mb-0"><?= count($data['pengguna']); ?> akun terdaftar. Hanya admin yang dapat membuka halaman ini.</p>
    </div>
    <button type="button" class="btn btn-steel" data-bs-toggle="modal" data-bs-target="#modalPengguna" data-mode="tambah">
        <i class="bi bi-person-plus me-1"></i>Tambah pengguna
    </button>
</div>

<!-- ===================== TABEL PENGGUNA ===================== -->
<div class="card-app">
    <div class="table-responsive">
        <table class="table table-app table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['pengguna'] as $p) : ?>
                    <?php $saya = ((int) $p['id'] === (int) $data['id_saya']); ?>
                    <tr>
                        <td class="fw-medium">
                            <?= $e($p['nama']); ?>
                            <?php if ($saya) : ?>
                                <span class="badge text-bg-light border ms-1">Akun Anda</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $e($p['username']); ?></td>
                        <td>
                            <span class="badge <?= ($p['role'] === 'admin') ? 'badge-role-admin' : 'badge-role-staff'; ?>">
                                <?= $e($labelRole($p['role'])); ?>
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="modal" data-bs-target="#modalPengguna" data-mode="ubah"
                                data-id="<?= $e($p['id']); ?>"
                                data-nama="<?= $e($p['nama']); ?>"
                                data-username="<?= $e($p['username']); ?>"
                                data-role="<?= $e($p['role']); ?>"
                                data-saya="<?= $saya ? '1' : '0'; ?>"
                                title="Ubah" aria-label="Ubah <?= $e($p['nama']); ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <?php if (!$saya) : ?>
                                <!-- Akun yang sedang dipakai tidak boleh dihapus, jadi tombolnya tidak ditampilkan -->
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal" data-bs-target="#modalHapus"
                                    data-id="<?= $e($p['id']); ?>"
                                    data-nama="<?= $e($p['nama']); ?>"
                                    title="Hapus" aria-label="Hapus <?= $e($p['nama']); ?>">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- ===================== MODAL TAMBAH / UBAH ===================== -->
<div class="modal fade" id="modalPengguna" tabindex="-1" aria-labelledby="judulModalPengguna" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <form id="formPengguna" method="POST" autocomplete="off"
                action="<?= BASEURL; ?>/pengguna/tambah"
                data-url-tambah="<?= BASEURL; ?>/pengguna/tambah"
                data-url-ubah="<?= BASEURL; ?>/pengguna/ubah">

                <div class="modal-header">
                    <h2 class="modal-title h5 fw-semibold" id="judulModalPengguna">Tambah pengguna</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="inputId" value="">

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="inputNama" class="form-label fw-medium">Nama lengkap</label>
                            <input type="text" class="form-control" id="inputNama" name="nama"
                                maxlength="100" placeholder="Nama pengguna" required>
                        </div>

                        <div class="col-md-6">
                            <label for="inputUsername" class="form-label fw-medium">Username</label>
                            <input type="text" class="form-control" id="inputUsername" name="username"
                                minlength="3" maxlength="50" autocomplete="off"
                                pattern="[a-zA-Z0-9._]+" placeholder="Huruf, angka, titik, atau garis bawah" required>
                        </div>

                        <div class="col-md-6">
                            <label for="inputRole" class="form-label fw-medium">Role</label>
                            <!-- Urutan sengaja: Staff gudang di atas, supaya pilihan bawaan adalah hak akses terendah -->
                            <select class="form-select" id="inputRole" name="role" required>
                                <option value="staff_gudang">Staff gudang</option>
                                <option value="admin">Admin</option>
                            </select>
                            <div class="form-text" id="infoRole"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="inputPassword" class="form-label fw-medium">Password</label>
                            <input type="password" class="form-control" id="inputPassword" name="password"
                                minlength="6" maxlength="72" autocomplete="new-password" required>
                            <div class="form-text" id="infoPassword">Minimal 6 karakter.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="inputKonfirmasi" class="form-label fw-medium">Konfirmasi password</label>
                            <input type="password" class="form-control" id="inputKonfirmasi" name="konfirmasi_password"
                                maxlength="72" autocomplete="new-password" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-steel" id="tombolSimpan">Simpan pengguna</button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- ===================== MODAL KONFIRMASI HAPUS ===================== -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="judulModalHapus" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Dikirim dengan POST; id pengguna ditambahkan ke URL oleh JavaScript -->
            <form id="formHapus" method="POST" action="#" data-url="<?= BASEURL; ?>/pengguna/hapus/">
                <div class="modal-header">
                    <h2 class="modal-title h5 fw-semibold" id="judulModalHapus">Hapus pengguna</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Hapus akun <strong id="namaHapus"></strong>?</p>
                    <p class="text-muted-app small mb-0">
                        Pengguna yang sudah tercatat sebagai petugas di transaksi tidak dapat dihapus.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus pengguna</button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- Script halaman Manajemen Pengguna (lihat public/js/pengguna.js) -->
<script src="<?= BASEURL; ?>/js/pengguna.js"></script>

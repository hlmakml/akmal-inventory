<?php
/**
 * View : Halaman Login
 * --------------------------------------------------------------
 * Letak file : app/views/auth/login.php
 * Dipanggil  : $this->view('auth/login', $data) dari Auth::index()
 *
 * Halaman ini berdiri sendiri (punya <html> lengkap), jadi JANGAN
 * di-include header/footer template bawaan di sini.
 *
 * Variabel yang tersedia: $data['judul'] (dikirim oleh controller)
 */

/** @var array $data */

// Ambil pesan flash (kalau ada). Flasher::get() mengembalikan array
// ['message' => ..., 'color' => ...] lalu langsung menghapusnya dari session,
// atau null jika tidak ada pesan.
$flash = Flasher::get();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($data['judul'] ?? 'Login'); ?> | A.K.M.A.L Inventory</title>

    <!-- Bootstrap 5, Bootstrap Icons, dan font (semua lewat CDN) -->
    <!-- Bootstrap, Bootstrap Icons, dan IBM Plex Sans dihosting sendiri (lihat public/libs/) -->
    <link rel="icon" type="image/svg+xml" href="<?= BASEURL; ?>/images/favicon.svg">
    <link href="<?= BASEURL; ?>/libs/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASEURL; ?>/libs/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Gaya khusus halaman Login (lihat public/css/login.css) -->
    <link href="<?= BASEURL; ?>/css/login.css" rel="stylesheet">
</head>

<body>
    <main class="container-fluid">
        <div class="row min-vh-100">

            <!-- ============ PANEL KIRI : identitas aplikasi ============ -->
            <section class="col-lg-6 d-none d-lg-flex brand-panel" aria-hidden="true">
                <div class="d-flex align-items-center gap-2 fs-5 fw-semibold">
                    <i class="bi bi-box-seam"></i>
                    <span>Sistem Inventory Gudang</span>
                </div>

                <!-- Ilustrasi rak gudang (SVG dekoratif) -->
                <img src="<?= BASEURL; ?>/images/logo_login.svg" class="w-100 my-4" style="max-width: 30rem;" role="presentation">

                <p class="brand-copy mb-0">
                    Pantau stok, barang masuk, dan barang keluar dalam satu tempat.
                </p>
            </section>

            <!-- ============ PANEL KANAN : form login ============ -->
            <section class="col-12 col-lg-6 d-flex align-items-center justify-content-center p-4">
                <div class="login-box w-100">

                    <!-- Logo A.K.M.A.L Inventory (tampil di semua ukuran layar, di atas form) -->
                    <div class="mb-4" role="img" aria-label="A.K.M.A.L Inventory">
                        <img src="<?= BASEURL; ?>/images/logo_akmal_login.svg" class="logo-akmal" alt="A.K.M.A.L Inventory Logo" width="190">
                    </div>

                    <h1 class="h3 fw-semibold mb-1">Masuk</h1>
                    <p class="text-secondary mb-4">Gunakan akun yang diberikan admin gudang.</p>

                    <!-- Pesan flash (error login / sukses logout) -->
                    <?php if ($flash) : ?>
                        <div class="alert alert-<?= htmlspecialchars($flash['color']); ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($flash['message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form dikirim dengan POST ke Auth::prosesLogin() -->
                    <form action="<?= BASEURL; ?>/auth/prosesLogin" method="POST">

                        <div class="mb-3">
                            <label for="username" class="form-label fw-medium">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="username" name="username"
                                    placeholder="Masukkan username" autocomplete="username" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-medium">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Masukkan password" autocomplete="current-password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword"
                                    aria-label="Tampilkan password" aria-pressed="false">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-masuk w-100">Masuk</button>
                    </form>

                    <p class="text-secondary small text-center mt-4 mb-0">
                        Lupa password atau belum punya akun? Hubungi admin gudang.
                    </p>
                </div>
            </section>

        </div>
    </main>

    <!-- Bootstrap JS (dihosting sendiri, lihat public/libs/): dibutuhkan agar tombol tutup pada alert berfungsi -->
    <script src="<?= BASEURL; ?>/libs/bootstrap/bootstrap.bundle.min.js"></script>

    <!-- Tombol tampil/sembunyikan password (lihat public/js/login.js) -->
    <script src="<?= BASEURL; ?>/js/login.js"></script>
</body>

</html>

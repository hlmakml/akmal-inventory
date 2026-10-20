<?php
/**
 * Layout : Header (dipakai bersama oleh semua halaman setelah login)
 * --------------------------------------------------------------
 * Letak file : app/views/layout/header.php
 *
 * Berisi: <head> (memuat Bootstrap/Icons/Font lokal dari public/libs/, dan gaya
 * bersama dari public/css/app.css), TOP HEADER di atas area konten (judul halaman +
 * nama & role user yang sedang login sebagai teks read-only), dan tempat pesan flash.
 * Semua CSS (termasuk sidebar, gaya cetak/print, dan komponen umum) ada di
 * public/css/app.css -- bukan inline lagi -- supaya bisa di-cache browser.
 *
 * SIDEBAR ada di file terpisah, app/views/layout/sidebar.php, dan di-require dari
 * file ini (tepat setelah <body> dibuka) -- bukan dipanggil langsung oleh controller,
 * dan tidak ada $this->view('layout/sidebar', ...) di mana pun.
 *
 * Ditutup oleh app/views/layout/footer.php.
 *
 * Variabel dari controller:
 *   $data['judul']   -> judul tab browser
 *   $data['halaman'] -> 'dashboard' | 'barang' | 'masuk' | 'keluar' | 'supplier' |
 *                       'laporan' | 'pengguna'   (menu yang ditandai aktif; dipakai
 *                       oleh sidebar.php untuk menandai menu yang sedang aktif)
 */

/** @var array $data */

$user    = $_SESSION['user'] ?? null;
$halaman = $data['halaman'] ?? '';

// Ambil pesan flash (sekali tampil, lalu otomatis terhapus dari session)
$flash = Flasher::get();

// 'staff_gudang' -> 'Staff gudang'
$labelRole = $user ? ucfirst(str_replace('_', ' ', $user['role'])) : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($data['judul'] ?? 'Inventory Gudang'); ?> | A.K.M.A.L Inventory</title>

    <!-- Bootstrap, Bootstrap Icons, dan IBM Plex Sans dihosting sendiri (lihat public/libs/) -->
    <link rel="icon" type="image/svg+xml" href="<?= BASEURL; ?>/images/favicon.svg">
    <link href="<?= BASEURL; ?>/libs/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASEURL; ?>/libs/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Gaya bersama seluruh halaman setelah login (lihat public/css/app.css) -->
    <link href="<?= BASEURL; ?>/css/app.css" rel="stylesheet">
</head>

<body>
    <div class="app-shell">

        <?php require __DIR__ . '/sidebar.php'; ?>

        <!-- ===================== AREA ISI ===================== -->
        <div class="app-main">

            <!-- Top header: tombol buka sidebar (layar kecil), judul halaman, dan nama + role user di kanan -->
            <header class="app-header">
                <button type="button" class="app-header-toggle" id="sidebarToggle"
                    aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false">
                    <i class="bi bi-list"></i>
                </button>

                <div class="app-header-judul text-truncate">
                    <div class="small text-muted-app d-none d-sm-block">A.K.M.A.L Inventory</div>
                    <div class="fw-semibold text-truncate"><?= htmlspecialchars($data['judul'] ?? 'Inventory Gudang'); ?></div>
                </div>

                <?php if ($user) : ?>
                    <!-- Teks biasa (read-only): tanpa tautan dan tidak bisa diklik -->
                    <div class="app-user">
                        <div class="app-user-nama text-truncate"><?= htmlspecialchars($user['nama']); ?></div>
                        <div class="app-user-role"><?= htmlspecialchars($labelRole); ?></div>
                    </div>
                <?php endif; ?>
            </header>

            <main class="app-content">

                <?php if ($flash) : ?>
                    <div class="alert alert-<?= htmlspecialchars($flash['color']); ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($flash['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                <?php endif; ?>

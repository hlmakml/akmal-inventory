<?php
/**
 * Layout : Sidebar
 * --------------------------------------------------------------
 * Letak file : app/views/layout/sidebar.php
 * Dipanggil  : HANYA dari app/views/layout/header.php lewat `require`, tepat setelah
 *              <body><div class="app-shell"> dibuka. TIDAK pernah dipanggil langsung
 *              oleh controller -- tidak ada $this->view('layout/sidebar', ...) di mana pun.
 *
 * Karena di-require dari DALAM header.php (bukan lewat Controller::view()), file ini
 * otomatis mewarisi variabel yang sudah ada di scope header.php saat itu -- termasuk
 * $data dari controller dan konstanta BASEURL -- tanpa perlu dioper manual.
 *
 * Variabel dari controller (lewat $data, sama seperti yang diterima header.php):
 *   $data['halaman'] -> 'dashboard' | 'barang' | 'masuk' | 'keluar' | 'supplier' |
 *                       'laporan' | 'pengguna'   (menandai menu mana yang aktif)
 *
 * CSS untuk sidebar (.app-sidebar, .sidebar-*, .sidebar-backdrop) SENGAJA tetap berada
 * di <style> pada app/views/layout/header.php, bukan di sini -- supaya seluruh gaya
 * halaman terkumpul dalam satu <head>, dan file ini murni berisi markup + logika menu.
 */

$halaman = $data['halaman'] ?? '';
$role    = $_SESSION['user']['role'] ?? '';

// ---------- DAFTAR MENU SIDEBAR ----------
// 'roles'    : role mana yang boleh MELIHAT menu ini (hanya tampilan; penjagaan
//              akses yang sebenarnya ada di constructor tiap controller).
// 'tersedia' : false = halamannya belum dibuat, menu tampil redup dengan label
//              "Segera" dan tidak bisa diklik. Semua halaman sekarang sudah ada.
$menu = [
    [
        'key' => 'dashboard', 'label' => 'Dashboard', 'url' => '/home', 'icon' => 'bi-grid-1x2',
        'roles' => ['admin', 'staff_gudang'], 'tersedia' => true,
    ],
    [
        'key' => 'barang', 'label' => 'Data Barang', 'url' => '/barang', 'icon' => 'bi-box-seam',
        'roles' => ['admin'], 'tersedia' => true,
    ],
    [
        'key' => 'masuk', 'label' => 'Barang Masuk', 'url' => '/transaksi/masuk', 'icon' => 'bi-box-arrow-in-down',
        'roles' => ['admin', 'staff_gudang'], 'tersedia' => true,
    ],
    [
        'key' => 'keluar', 'label' => 'Barang Keluar', 'url' => '/transaksi/keluar', 'icon' => 'bi-box-arrow-up',
        'roles' => ['admin', 'staff_gudang'], 'tersedia' => true,
    ],
    [
        'key' => 'supplier', 'label' => 'Supplier', 'url' => '/supplier', 'icon' => 'bi-truck',
        'roles' => ['admin'], 'tersedia' => true,
    ],
    [
        'key' => 'laporan', 'label' => 'Laporan', 'url' => '/laporan', 'icon' => 'bi-file-earmark-bar-graph',
        'roles' => ['admin'], 'tersedia' => true,
    ],
    // Pengaturan = Kelola Pengguna. Hanya untuk admin; staff gudang tidak memiliki menu ini.
    [
        'key' => 'pengguna', 'label' => 'Pengaturan', 'hint' => 'Kelola pengguna', 'url' => '/pengguna', 'icon' => 'bi-gear',
        'roles' => ['admin'], 'tersedia' => true,
    ],
];

// Saring: hanya menu yang boleh dilihat oleh role user saat ini
$menuTampil = [];
foreach ($menu as $item) {
    if (in_array($role, $item['roles'], true)) {
        $menuTampil[] = $item;
    }
}
?>
<!-- ===================== SIDEBAR ===================== -->
<aside class="app-sidebar" id="sidebar" aria-label="Navigasi utama">

    <!-- Logo A.K.M.A.L Inventory (putih + amber) -->
    <a class="sidebar-brand" href="<?= BASEURL; ?>/home" aria-label="A.K.M.A.L Inventory, ke Dashboard">
        <img src="<?= BASEURL; ?>/images/logo_akmal_sidebar.svg" class="logo-akmal" alt="A.K.M.A.L Inventory Logo" width="190">
    </a>

    <!-- Menu utama (disaring berdasarkan role) -->
    <nav aria-label="Menu utama">
        <ul class="sidebar-menu">
            <?php foreach ($menuTampil as $item) : ?>
                <?php $aktif = ($halaman === $item['key']); ?>
                <li>
                    <?php if ($item['tersedia']) : ?>
                        <a class="sidebar-link <?= $aktif ? 'active' : ''; ?>"
                            href="<?= BASEURL . $item['url']; ?>"
                            <?= !empty($item['hint']) ? 'title="' . htmlspecialchars($item['hint']) . '"' : ''; ?>
                            <?= $aktif ? 'aria-current="page"' : ''; ?>>
                            <i class="bi <?= $item['icon']; ?>"></i>
                            <span><?= $item['label']; ?></span>
                        </a>
                    <?php else : ?>
                        <!-- Halaman belum dibuat: tampil redup, tidak bisa diklik -->
                        <span class="sidebar-link is-disabled" aria-disabled="true"
                            title="<?= htmlspecialchars(($item['hint'] ?? $item['label']) . ' (halaman belum tersedia)'); ?>">
                            <i class="bi <?= $item['icon']; ?>"></i>
                            <span><?= $item['label']; ?></span>
                            <span class="sidebar-soon">Segera</span>
                        </span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Bagian paling bawah: Logout (info akun ada di top header, lihat header.php) -->
    <div class="sidebar-bottom">
        <a class="sidebar-logout" href="<?= BASEURL; ?>/auth/logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<!-- Latar gelap saat sidebar dibuka di layar kecil -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

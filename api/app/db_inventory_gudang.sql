-- =====================================================================
--  SISTEM INVENTORY GUDANG  -  STEP 1 : RANCANGAN DATABASE (MySQL/MariaDB)
--  Cara pakai : phpMyAdmin -> tab "Import" -> pilih file ini -> Go
--  CATATAN    : nama database di bawah HARUS sama dengan konstanta DB_NAME
--               pada file config milik template MVC kamu.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `db_inventory_gudang`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `db_inventory_gudang`;

-- ---------------------------------------------------------------------
--  Reset (aman dijalankan ulang): hapus view & tabel dari yang paling
--  bergantung (child) ke yang paling dasar (parent)
-- ---------------------------------------------------------------------
DROP VIEW  IF EXISTS `v_stok_kritis`;
DROP TABLE IF EXISTS `transaksi_keluar`;
DROP TABLE IF EXISTS `transaksi_masuk`;
DROP TABLE IF EXISTS `barang`;
DROP TABLE IF EXISTS `pengguna`;
DROP TABLE IF EXISTS `supplier`;
DROP TABLE IF EXISTS `kategori_barang`;


-- =====================================================================
--  1. TABEL KATEGORI_BARANG
-- =====================================================================
CREATE TABLE `kategori_barang` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_kategori` VARCHAR(100) NOT NULL,
  `keterangan`    VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kategori_nama` (`nama_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  2. TABEL SUPPLIER
-- =====================================================================
CREATE TABLE `supplier` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_supplier` VARCHAR(20)  NOT NULL,          -- otomatis oleh aplikasi: SPL-001, SPL-002, ...
  `nama_supplier` VARCHAR(150) NOT NULL,
  `kontak`        VARCHAR(20)  NULL DEFAULT NULL,
  `alamat`        TEXT         NULL,
  `email`         VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_supplier_kode` (`kode_supplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  3. TABEL PENGGUNA
--     password disimpan dalam bentuk HASH (bcrypt = 60 karakter),
--     dibuat VARCHAR(255) supaya aman jika algoritma hash berubah.
-- =====================================================================
CREATE TABLE `pengguna` (
  `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`     VARCHAR(100) NOT NULL,
  `username` VARCHAR(50)  NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role`     ENUM('admin','staff_gudang') NOT NULL DEFAULT 'staff_gudang',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pengguna_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  4. TABEL BARANG
-- =====================================================================
CREATE TABLE `barang` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_barang`  VARCHAR(30)  NOT NULL,
  `nama_barang`  VARCHAR(150) NOT NULL,
  `kategori_id`  INT UNSIGNED NOT NULL,
  `satuan`       VARCHAR(20)  NOT NULL,
  `stok`         INT UNSIGNED NOT NULL DEFAULT 0,
  `stok_minimum` INT UNSIGNED NOT NULL DEFAULT 0,
  `harga_beli`   DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `harga_jual`   DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `lokasi_rak`   VARCHAR(30)  NULL DEFAULT NULL,
  PRIMARY KEY (`id`),

  -- INDEX untuk pencarian cepat berdasarkan kode barang.
  -- Dibuat UNIQUE karena satu kode barang tidak boleh kembar.
  UNIQUE INDEX `idx_barang_kode_barang` (`kode_barang`),

  KEY `idx_barang_kategori` (`kategori_id`),

  CONSTRAINT `fk_barang_kategori`
    FOREIGN KEY (`kategori_id`) REFERENCES `kategori_barang` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  5. TABEL TRANSAKSI_MASUK  (barang masuk dari supplier)
-- =====================================================================
CREATE TABLE `transaksi_masuk` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_transaksi` VARCHAR(30)  NOT NULL,
  `barang_id`      INT UNSIGNED NOT NULL,
  `supplier_id`    INT UNSIGNED NOT NULL,
  `pengguna_id`    INT UNSIGNED NOT NULL,
  `jumlah`         INT UNSIGNED NOT NULL,
  `tanggal_masuk`  DATE         NOT NULL,
  `keterangan`     VARCHAR(255) NULL DEFAULT NULL,
  `dibuat_pada`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,   -- jam pencatatan (untuk dashboard)
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_masuk_kode` (`kode_transaksi`),
  KEY `idx_masuk_barang`   (`barang_id`),
  KEY `idx_masuk_supplier` (`supplier_id`),
  KEY `idx_masuk_pengguna` (`pengguna_id`),
  KEY `idx_masuk_tanggal`  (`tanggal_masuk`),

  CONSTRAINT `fk_masuk_barang`
    FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_masuk_supplier`
    FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_masuk_pengguna`
    FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,

  CONSTRAINT `chk_masuk_jumlah` CHECK (`jumlah` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  6. TABEL TRANSAKSI_KELUAR  (barang keluar dari gudang)
-- =====================================================================
CREATE TABLE `transaksi_keluar` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_transaksi` VARCHAR(30)  NOT NULL,
  `barang_id`      INT UNSIGNED NOT NULL,
  `pengguna_id`    INT UNSIGNED NOT NULL,
  `jumlah`         INT UNSIGNED NOT NULL,
  `tanggal_keluar` DATE         NOT NULL,
  `tujuan`         VARCHAR(150) NULL DEFAULT NULL,
  `keterangan`     VARCHAR(255) NULL DEFAULT NULL,
  `dibuat_pada`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,   -- jam pencatatan (untuk dashboard)
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_keluar_kode` (`kode_transaksi`),
  KEY `idx_keluar_barang`   (`barang_id`),
  KEY `idx_keluar_pengguna` (`pengguna_id`),
  KEY `idx_keluar_tanggal`  (`tanggal_keluar`),

  CONSTRAINT `fk_keluar_barang`
    FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_keluar_pengguna`
    FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,

  CONSTRAINT `chk_keluar_jumlah` CHECK (`jumlah` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  VIEW : v_stok_kritis
--  Menampilkan barang yang stoknya sudah <= stok minimum
-- =====================================================================
CREATE OR REPLACE VIEW `v_stok_kritis` AS
SELECT
    b.`id`,
    b.`kode_barang`,
    b.`nama_barang`,
    k.`nama_kategori`,
    b.`satuan`,
    b.`stok`,
    b.`stok_minimum`,
    (b.`stok_minimum` - b.`stok`) AS `kekurangan`,
    b.`lokasi_rak`
FROM `barang` b
INNER JOIN `kategori_barang` k ON k.`id` = b.`kategori_id`
WHERE b.`stok` <= b.`stok_minimum`;


-- =====================================================================
--  DUMMY DATA
--  (urutan INSERT mengikuti dependensi Foreign Key: parent dulu)
-- =====================================================================

-- ---------- kategori_barang ----------
INSERT INTO `kategori_barang` (`id`, `nama_kategori`, `keterangan`) VALUES
(1, 'Elektronik',        'Perangkat dan aksesoris elektronik'),
(2, 'Alat Tulis Kantor', 'Perlengkapan tulis dan administrasi'),
(3, 'Makanan & Minuman', 'Produk konsumsi kemasan'),
(4, 'Perkakas',          'Alat kerja dan perkakas tangan'),
(5, 'Kebersihan',        'Perlengkapan kebersihan dan sanitasi');

-- ---------- supplier ----------
INSERT INTO `supplier` (`id`, `kode_supplier`, `nama_supplier`, `kontak`, `alamat`, `email`) VALUES
(1, 'SPL-001', 'PT Sinar Elektronik Jaya',  '081234567801', 'Jl. Pemuda No. 12, Semarang',        'sales@sinarelektronik.co.id'),
(2, 'SPL-002', 'CV Maju Kertas Nusantara',  '081234567802', 'Jl. Kaligawe Raya No. 45, Semarang', 'order@majukertas.co.id'),
(3, 'SPL-003', 'UD Segar Sejahtera',        '081234567803', 'Jl. Ahmad Yani No. 88, Ungaran',     'segarsejahtera@gmail.com'),
(4, 'SPL-004', 'PT Perkasa Tools Indonesia','081234567804', 'Jl. Industri No. 7, Kendal',         'info@perkasatools.co.id');

-- ---------- pengguna ----------
-- Password di bawah ini sudah berupa HASH bcrypt ($2y$), cocok dengan
-- password_verify() di PHP:
--     admin   -> password: admin123
--     staff01 -> password: staff123
--     staff02 -> password: staff123
INSERT INTO `pengguna` (`id`, `nama`, `username`, `password`, `role`) VALUES
(1, 'Administrator Gudang', 'admin',   '$2y$10$RgXSnh/WHp1F0Baz7zrEbeW8gEPIiu4DoaD7DWOkQ8BvN1tp8oxOO', 'admin'),
(2, 'Budi Santoso',         'staff01', '$2y$10$D.zYPIZ5BtEI6U94BLB7PO/n7BfkYEk2JXCrVFMLox3ybXFPRT4TC', 'staff_gudang'),
(3, 'Siti Rahmawati',       'staff02', '$2y$10$D.zYPIZ5BtEI6U94BLB7PO/n7BfkYEk2JXCrVFMLox3ybXFPRT4TC', 'staff_gudang');

-- ---------- barang ----------
-- Sengaja ada 3 barang dengan stok <= stok_minimum (BRG-002, BRG-004,
-- BRG-007) supaya view v_stok_kritis langsung bisa diuji.
INSERT INTO `barang`
(`id`, `kode_barang`, `nama_barang`, `kategori_id`, `satuan`, `stok`, `stok_minimum`, `harga_beli`, `harga_jual`, `lokasi_rak`) VALUES
(1, 'BRG-001', 'Mouse Wireless M185',        1, 'pcs',   45, 10,  95000.00, 125000.00, 'A-01'),
(2, 'BRG-002', 'Keyboard USB Standar',       1, 'pcs',    8, 10,  85000.00, 115000.00, 'A-02'),
(3, 'BRG-003', 'Kertas HVS A4 80gsm',        2, 'rim',  120, 30,  42000.00,  52000.00, 'B-01'),
(4, 'BRG-004', 'Pulpen Standard AE7 (Box)',  2, 'box',    5,  5,  28000.00,  36000.00, 'B-02'),
(5, 'BRG-005', 'Lakban Bening 48mm',         2, 'roll',  70, 20,   6500.00,   9000.00, 'B-03'),
(6, 'BRG-006', 'Air Mineral 600ml',          3, 'dus',   60, 20,  38000.00,  45000.00, 'C-01'),
(7, 'BRG-007', 'Sabun Cuci Tangan 500ml',    5, 'botol',  3, 12,  14000.00,  19000.00, 'E-01'),
(8, 'BRG-008', 'Obeng Set 6 Pcs',            4, 'set',   25,  5,  35000.00,  55000.00, 'D-02');

-- ---------- transaksi_masuk ----------
INSERT INTO `transaksi_masuk`
(`id`, `kode_transaksi`, `barang_id`, `supplier_id`, `pengguna_id`, `jumlah`, `tanggal_masuk`, `keterangan`) VALUES
(1, 'TRM-20260901-001', 1, 1, 2,  30, '2026-09-01', 'Restock bulanan mouse'),
(2, 'TRM-20260903-001', 3, 2, 2,  80, '2026-09-03', 'Pembelian kertas untuk kebutuhan kantor'),
(3, 'TRM-20260905-001', 6, 3, 3,  40, '2026-09-05', 'Stok air mineral'),
(4, 'TRM-20260908-001', 8, 4, 2,  15, '2026-09-08', 'Pengadaan perkakas baru'),
(5, 'TRM-20260912-001', 2, 1, 3,  20, '2026-09-12', 'Keyboard USB batch pertama'),
(6, 'TRM-20260915-001', 5, 2, 3,  50, '2026-09-15', 'Restock lakban');

-- ---------- transaksi_keluar ----------
INSERT INTO `transaksi_keluar`
(`id`, `kode_transaksi`, `barang_id`, `pengguna_id`, `jumlah`, `tanggal_keluar`, `tujuan`, `keterangan`) VALUES
(1, 'TRK-20260902-001', 1, 2,  10, '2026-09-02', 'Divisi IT',         'Permintaan mouse untuk komputer lab'),
(2, 'TRK-20260904-001', 3, 3,  25, '2026-09-04', 'Bagian Administrasi','Kebutuhan cetak bulanan'),
(3, 'TRK-20260910-001', 2, 2,  12, '2026-09-10', 'Ruang Server',      'Penggantian keyboard rusak'),
(4, 'TRK-20260914-001', 7, 3,   9, '2026-09-14', 'Bagian Kebersihan', 'Distribusi sabun ke seluruh toilet'),
(5, 'TRK-20260918-001', 4, 2,   7, '2026-09-18', 'Ruang Guru',        'Permintaan pulpen');


-- ---------- jam pencatatan untuk data dummy ----------
-- (data baru dari aplikasi otomatis memakai jam saat transaksi dicatat)
UPDATE `transaksi_masuk`
   SET `dibuat_pada` = TIMESTAMP(`tanggal_masuk`, SEC_TO_TIME(28800 + (`id` % 8) * 2700))
 WHERE `id` > 0;
UPDATE `transaksi_keluar`
   SET `dibuat_pada` = TIMESTAMP(`tanggal_keluar`, SEC_TO_TIME(46800 + (`id` % 6) * 2400))
 WHERE `id` > 0;


-- =====================================================================
--  TEST CEPAT (jalankan manual di tab SQL phpMyAdmin)
--    SELECT * FROM v_stok_kritis;
--    -> harus muncul 3 baris: BRG-002, BRG-004, BRG-007
-- =====================================================================

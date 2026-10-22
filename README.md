# A.K.M.A.L Inventory - Sistem Inventory Gudang

**A.K.M.A.L** = *Automated Kode Management Application for Logistics*

Sistem manajemen inventory gudang berbasis web dengan arsitektur MVC untuk tracking barang masuk dan keluar, pengelolaan supplier, serta monitoring stok secara real-time.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi Stack](#teknologi-stack)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Penggunaan](#penggunaan)
- [Struktur Folder](#struktur-folder)
- [Akun Demo](#akun-demo)
- [Troubleshooting](#troubleshooting)
  
---

## Fitur Utama

### Dashboard
- Ringkasan harian (transaksi masuk/keluar, stok kritis)
- Alert stok barang di bawah batas minimum
- Aktivitas transaksi terbaru
- Statistik total pengguna, barang, dan kategori

### Manajemen Barang
- Tambah barang dengan pembuatan kode otomatis (contoh: BRG-001)
- Edit informasi barang (nama, kategori, satuan, harga)
- Hapus barang dengan validasi relasi transaksi
- Pencarian dan filter stok kritis
- Pengelolaan lokasi rak penyimpanan (format: A-01)

### Transaksi
- **Barang Masuk:** Pencatatan dari supplier dengan pembaruan stok otomatis
- **Barang Keluar:** Pengeluaran barang dengan validasi ketersediaan stok
- Pembuatan kode transaksi otomatis (format: TRM-YYYYMMDD-001 / TRK-YYYYMMDD-001)
- Dukungan transaksi database (atomicity) untuk konsistensi data

### Laporan
- Laporan stok barang real-time
- Filter laporan berdasarkan rentang tanggal dan kategori barang
- Ekspor data ke format Excel

### Manajemen Pengguna
- **Admin:** Akses penuh (pengguna, barang, supplier, laporan)
- **Staff Gudang:** Akses terbatas (pencatatan barang masuk dan keluar)
- Keamanan password menggunakan bcrypt hashing
- Proteksi akses berbasis peran (RBAC)

### Manajemen Supplier
- Tambah supplier dengan pembuatan kode otomatis (contoh: SPL-001)
- Edit dan hapus data supplier dengan validasi transaksi terkait

---

## Teknologi Stack

### Backend
- **PHP 7.4+** (Native MVC Architecture)
- **PDO (PHP Data Objects)** untuk abstraksi database
- **Database Transactions** untuk dukungan integritas data

### Frontend
- **HTML5 & CSS3**
- **Bootstrap 5** untuk UI framework responsif
- **Bootstrap Icons**
- **JavaScript (Vanilla)** untuk interaksi form, modal, dan tabel

### Database
- **MySQL / MariaDB 5.7+**
- **UTF-8MB4 Encoding**
- **InnoDB Engine** untuk dukungan transaksi dan foreign keys

---

## Instalasi

### Prasyarat
- Laragon / XAMPP / WAMP / LAMP
- PHP 7.4+ dengan ekstensi PDO dan PDO_MySQL
- MySQL / MariaDB 5.7+

### Langkah 1: Kloning atau Unduh Proyek
```bash
cd akmal_inventory
```

### Langkah 2: Setup Database
1. Buat database baru dengan nama `db_inventory_gudang` (charset `utf8mb4`).
2. Impor file skema database dari `app/db_inventory_gudang.sql` melalui phpMyAdmin atau command line:
```bash
mysql -u root -p db_inventory_gudang < app/db_inventory_gudang.sql
```

### Langkah 3: Konfigurasi Aplikasi
Sesuaikan konfigurasi database pada file `app/config/config.php`:
```php
<?php
define('BASEURL', 'http://localhost/akmal_inventory/public');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_inventory_gudang');
```

### Langkah 4: Menjalankan Server
1. Aktifkan layanan Apache dan MySQL melalui Laragon atau XAMPP.
2. Akses aplikasi melalui browser pada alamat:
   `http://localhost/akmal_inventory/public`

---

## Struktur Folder

```
akmal_inventory/
├── app/
│   ├── config/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   ├── core/
│   ├── init.php
│   └── db_inventory_gudang.sql
├── public/
│   ├── index.php
│   ├── css/
│   ├── js/
│   ├── images/
│   └── libs/
├── .htaccess
├── README.md
```

---

## Akun Demo

### Administrator
- **Username:** `admin`
- **Password:** `admin123`

### Staff Gudang
- **Username:** `staff01`
- **Password:** `staff123`
- **Username:** `staff02`
- **Password:** `staff123`

---

## Troubleshooting

- **Database Connection Failed:** Pastikan layanan MySQL aktif dan kredensial pada `app/config/config.php` sudah benar.
- **403 Forbidden / URL Not Found:** Pastikan modul `mod_rewrite` pada Apache aktif dan pengaturan virtual host / `.htaccess` sudah sesuai.
- **Transaksi Gagal:** Pastikan engine database menggunakan InnoDB dan ketersediaan stok mencukupi untuk transaksi barang keluar.

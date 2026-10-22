# A.K.M.A.L Inventory - Sistem Inventory Gudang

**A.K.M.A.L** = *Automated Kode Management Application for Logistics*

Sistem manajemen inventory gudang berbasis web dengan MVC architecture untuk tracking barang masuk/keluar, kelola supplier, dan monitoring stok real-time.

![Status](https://img.shields.io/badge/Status-Production-green) ![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue) ![License](https://img.shields.io/badge/License-MIT-brightgreen)

---

## 📋 Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi Stack](#teknologi-stack)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Penggunaan](#penggunaan)
- [Struktur Folder](#struktur-folder)
- [Akun Demo](#akun-demo)
- [API Endpoints](#api-endpoints)
- [Dokumentasi](#dokumentasi)
- [Troubleshooting](#troubleshooting)
- [Kontribusi](#kontribusi)
- [Lisensi](#lisensi)

---

## ✨ Fitur Utama

### Dashboard
- 📊 Ringkasan harian (transaksi masuk/keluar, stok kritis)
- 🔴 Alert stok barang di bawah minimum
- 📈 Aktivitas transaksi terbaru (8 item)
- 👥 Jumlah pengguna, barang, dan kategori

### Manajemen Barang
- ➕ Tambah barang (auto generate kode: BRG-001, BRG-002, ...)
- ✏️ Edit informasi barang (nama, kategori, satuan, harga)
- 🗑️ Hapus barang (jika tidak ada transaksi)
- 🔍 Search dan filter stok kritis
- 📦 Tracking lokasi rak (format: A-01, B-12)

### Transaksi
- 📥 **Barang Masuk:** Dari supplier (auto update stok +)
- 📤 **Barang Keluar:** Ke distribusi (auto update stok -, dengan validasi stok)
- 🧾 Auto generate kode transaksi (TRM-20261020-001, TRK-20261020-001)
- 📝 Keterangan transaksi
- 🔒 Database transaction (atomicity: semua berhasil atau semua gagal)

### Laporan
- 📋 Laporan stok barang real-time
- 📊 Laporan transaksi dengan filter:
  - Tanggal range (dari-sampai)
  - Kategori barang
- 📥 Export ke Excel (`.xls` format)
- 📈 Total qty masuk/keluar

### Manajemen Pengguna
- 👤 Admin: akses penuh (kelola user, barang, supplier, laporan)
- 🔐 Staff Gudang: akses terbatas (hanya barang masuk/keluar)
- 🔑 Password hashing (bcrypt) + session regeneration
- 🚫 Proteksi role-based access control (RBAC)

### Manajemen Supplier
- ➕ Tambah supplier (auto generate kode: SPL-001, SPL-002, ...)
- ✏️ Edit data supplier (nama, kontak, email, alamat)
- 🗑️ Hapus supplier (jika tidak ada transaksi)
- 📧 Email validation
- 📞 Kontak validation

---

## 🛠️ Teknologi Stack

### Backend
- **PHP 7.4+** (pure PHP, no framework dependency)
- **PDO (PHP Data Objects)** - database abstraction layer
- **Database Transaction** - atomicity support (BEGIN/COMMIT/ROLLBACK)
- **MVC Architecture** - separation of concerns

### Frontend
- **HTML5** - semantic markup
- **Bootstrap 5** - responsive UI framework
- **Bootstrap Icons** - icon library
- **JavaScript (Vanilla)** - form handling, modal, table sorting
- **CSS3** - custom styling

### Database
- **MySQL/MariaDB 5.7+**
- **UTF-8MB4 Encoding** - support emoji & special characters
- **InnoDB Engine** - transaction support, foreign keys

### Server Requirements
- **Apache 2.4+** atau **Nginx**
- **PHP 7.4+** dengan extension: PDO, PDO_MySQL
- **MySQL 5.7+** atau **MariaDB 10.3+**

---

## 📦 Instalasi

### Prasyarat
- Laragon / XAMPP / WAMP / LAMP sudah installed
- MySQL service running
- PHP 7.4+ 

### Step 1: Clone/Download Proyek

```bash
# Gunakan Git
git clone https://github.com/yourusername/akmal_inventory.git

# Atau extract .zip ke folder
cd akmal_inventory
```

### Step 2: Setup Database

#### Via phpMyAdmin GUI
1. Buka http://localhost/phpmyadmin
2. Klik **Import** (tab atas)
3. Pilih file: `app/db_inventory_gudang.sql`
4. Klik **Go** untuk import schema + dummy data

#### Via MySQL Command Line
```bash
mysql -u root -p < app/db_inventory_gudang.sql
```

#### Via Laragon
1. Klik **Database** → **Create new database**
2. Nama: `db_inventory_gudang`
3. Charset: `utf8mb4`
4. Import file `app/db_inventory_gudang.sql`

### Step 3: Konfigurasi

Edit file `app/config/config.php`:

```php
<?php

// Base URL aplikasi (sesuaikan dengan setup lokal)
define('BASEURL', 'http://localhost/akmal_inventory/public');

// Database credentials
define('DB_HOST', 'localhost');     // MySQL host
define('DB_USER', 'root');          // MySQL user
define('DB_PASS', '');              // MySQL password (kosong untuk Laragon)
define('DB_NAME', 'db_inventory_gudang');
```

### Step 4: Start Server

#### Laragon
1. Klik **Start All**
2. Buka http://localhost/akmal_inventory/public

#### XAMPP / Manual
```bash
# Via PHP built-in server
php -S localhost:8000 -t public/

# Atau gunakan Apache (sesuaikan vhost)
```

### Step 5: Akses Aplikasi

Buka di browser: **http://localhost/akmal_inventory/public**

---

## ⚙️ Konfigurasi

### Environment Variables (Optional - Best Practice)

Buat file `.env` di root folder:

```
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=db_inventory_gudang
BASEURL=http://localhost/akmal_inventory/public
APP_ENV=development
```

Update `app/config/config.php` untuk baca dari `.env`:

```php
<?php

// Load .env
if (file_exists(__DIR__ . '/../../.env')) {
    $env = parse_ini_file(__DIR__ . '/../../.env');
    define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
    define('DB_USER', $env['DB_USER'] ?? 'root');
    define('DB_PASS', $env['DB_PASS'] ?? '');
    define('DB_NAME', $env['DB_NAME'] ?? 'db_inventory_gudang');
    define('BASEURL', $env['BASEURL'] ?? 'http://localhost/akmal_inventory/public');
} else {
    // Fallback ke hardcode
    define('BASEURL', 'http://localhost/akmal_inventory/public');
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'db_inventory_gudang');
}
```

### .htaccess Configuration

Pastikan Apache module `mod_rewrite` enabled:

```bash
# Check (Linux/Mac)
a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

---

## 🚀 Penggunaan

### Login

1. Buka: http://localhost/akmal_inventory/public/auth
2. Masukkan kredensial:

   **Admin Account:**
   ```
   Username: admin
   Password: admin123
   ```

   **Staff Account:**
   ```
   Username: staff01
   Password: staff123
   ```

### Dashboard

Setelah login, Anda akan melihat dashboard dengan:
- Summary harian (transaksi masuk/keluar)
- Stok barang kritis (stok <= minimum)
- Aktivitas transaksi terbaru
- Total barang, kategori, pengguna

### Kelola Barang (Admin Only)

**URL:** http://localhost/akmal_inventory/public/barang

**Fitur:**
- **Tambah Barang:** Klik tombol "Tambah barang"
  - Isi: nama, kategori, satuan, harga beli/jual, stok minimum, lokasi rak
  - Kode otomatis: BRG-001, BRG-002, ...
  
- **Edit Barang:** Klik tombol "Ubah" di row barang
  - Update informasi barang
  - Stok TIDAK bisa diubah dari sini (hanya via transaksi)
  
- **Hapus Barang:** Klik tombol "Hapus"
  - Hanya bisa hapus jika belum ada transaksi
  
- **Filter:** 
  - Cari barang berdasarkan nama
  - Filter stok kritis (stok <= stok minimum)

### Transaksi Barang Masuk (Staff/Admin)

**URL:** http://localhost/akmal_inventory/public/transaksi/masuk

**Cara:**
1. Pilih barang dari dropdown
2. Pilih supplier
3. Masukkan jumlah
4. Pilih tanggal masuk
5. Tambahkan keterangan (optional)
6. Klik "Simpan"

**Auto Update:**
- Stok barang langsung bertambah
- Kode transaksi otomatis (TRM-20261020-001)
- Jam pencatatan otomatis

### Transaksi Barang Keluar (Staff/Admin)

**URL:** http://localhost/akmal_inventory/public/transaksi/keluar

**Cara:**
1. Pilih barang dari dropdown
2. Masukkan jumlah
3. Pilih tanggal keluar
4. Masukkan tujuan barang
5. Tambahkan keterangan (optional)
6. Klik "Simpan"

**Validasi:**
- Stok harus cukup (akan error jika kurang)
- Stok otomatis berkurang
- Kode transaksi otomatis (TRK-20261020-001)

### Laporan (Admin Only)

**URL:** http://localhost/akmal_inventory/public/laporan

**Filter:**
- **Tanggal:** Range dari-sampai
- **Kategori:** Filter by kategori barang
- **Auto Update:** Laporan update otomatis saat filter berubah

**Export Excel:**
- Klik tombol "Export Excel"
- Download file `.xls` dengan data stok + transaksi
- Include timestamp & user yang export

### Manajemen Supplier (Admin Only)

**URL:** http://localhost/akmal_inventory/public/supplier

**Operasi:**
- Tambah supplier (auto generate kode: SPL-001, SPL-002, ...)
- Edit supplier (nama, kontak, email, alamat)
- Hapus supplier (jika tidak ada transaksi)

### Manajemen Pengguna (Admin Only)

**URL:** http://localhost/akmal_inventory/public/pengguna

**Operasi:**
- Tambah pengguna (role: admin/staff_gudang)
- Edit pengguna (nama, username, role, password optional)
- Hapus pengguna (tidak bisa hapus akun sendiri)

**Validasi:**
- Username 3-50 karakter, lowercase/digits/dot/underscore
- Password 6-72 karakter, bcrypt hashing
- Role tidak bisa diubah untuk akun sendiri

### Logout

Klik nama user di top-right → **Logout**

---

## 📁 Struktur Folder

```
akmal_inventory/
├── app/                           # Aplikasi inti (MVC)
│   ├── config/
│   │   └── config.php             # Konfigurasi database & URL
│   │
│   ├── controllers/               # Business logic
│   │   ├── Auth.php               # Login/Logout
│   │   ├── Barang.php             # CRUD Barang
│   │   ├── Supplier.php           # CRUD Supplier
│   │   ├── Pengguna.php           # CRUD Pengguna
│   │   ├── Transaksi.php          # Barang masuk/keluar
│   │   ├── Laporan.php            # Laporan & export
│   │   └── Home.php               # Dashboard
│   │
│   ├── models/                    # Database layer
│   │   ├── Barang_model.php
│   │   ├── Supplier_model.php
│   │   ├── Pengguna_model.php
│   │   ├── Transaksi_model.php
│   │   └── Laporan_model.php
│   │
│   ├── views/                     # Presentation layer
│   │   ├── auth/login.php
│   │   ├── barang/
│   │   ├── supplier/
│   │   ├── pengguna/
│   │   ├── transaksi/
│   │   ├── laporan/
│   │   ├── home/
│   │   └── layout/
│   │       ├── header.php
│   │       ├── sidebar.php
│   │       └── footer.php
│   │
│   ├── core/                      # Framework core
│   │   ├── App.php                # URL routing
│   │   ├── Controller.php         # Base controller
│   │   ├── Database.php           # PDO wrapper
│   │   └── Flasher.php            # Flash message
│   │
│   ├── init.php                   # Bootstrap
│   └── db_inventory_gudang.sql    # Database schema
│
├── public/                        # Web root (accessible)
│   ├── index.php                  # Front controller
│   ├── css/
│   │   ├── app.css                # Global styles
│   │   └── login.css
│   ├── js/
│   │   ├── app.js
│   │   ├── barang.js
│   │   ├── pengguna.js
│   │   ├── supplier.js
│   │   ├── transaksi-masuk.js
│   │   ├── transaksi-keluar.js
│   │   ├── laporan.js
│   │   └── login.js
│   ├── images/
│   │   ├── favicon.svg
│   │   ├── logo_akmal_login.svg
│   │   ├── logo_akmal_sidebar.svg
│   │   └── logo_login.svg
│   └── libs/
│       ├── bootstrap/
│       └── bootstrap-icons/
│
├── .htaccess                      # Redirect rules
├── README.md                      # Dokumentasi ini
├── pemetaan_dan_analisis_projek.md        # Analisis SKKNI
├── PENJELASAN_MVC_DAN_CORE.md     # Penjelasan MVC
└── akmal_inventory.zip            # Backup proyek
```

---

## 👤 Akun Demo

### Admin (Full Access)
```
Username: admin
Password: admin123
Role: Administrator Gudang
```

### Staff Gudang (Limited Access)
```
Username: staff01
Password: staff123
Role: Staff Gudang
Akses: Transaksi masuk/keluar saja
```

```
Username: staff02
Password: staff123
Role: Staff Gudang
```

---

## 🔌 API Endpoints

*Akan ditambahkan dalam versi mendatang (REST API dengan JWT)*

Untuk sekarang, aplikasi menggunakan server-side form submission (traditional MVC).

---

## 📚 Dokumentasi

Dokumentasi lengkap tersedia di:

1. **`pemetaan_dan_analisis_projek.md`** - Analisis berdasarkan SKKNI No. 282 Tahun 2016
   - Pemetaan struktur folder
   - Alur kerja sistem
   - Penerapan unit kompetensi
   - Celah keamanan & rekomendasi
   - Soal latihan (5 soal + pembahasan)

2. **`PENJELASAN_MVC_DAN_CORE.md`** - Penjelasan arsitektur MVC
   - Visual diagram alur request
   - Detail komponen (Core, Controller, Model, View)
   - Alur CRUD praktis
   - Analogi & kesimpulan

---

## 🔐 Security Features

✅ **Implemented:**
- ✅ PDO Prepared Statements (SQL Injection protection)
- ✅ Password Hashing (bcrypt, PASSWORD_DEFAULT)
- ✅ Session Regeneration (login, logout)
- ✅ Input Validation (server-side)
- ✅ Output Escaping (htmlspecialchars, XSS protection)
- ✅ Database Transaction (atomicity)
- ✅ Row Locking (FOR UPDATE, race condition prevention)
- ✅ Role-Based Access Control (RBAC)
- ✅ Error Logging (error_log)

⚠️ **Recommended:**
- [ ] CSRF Token protection
- [ ] Rate Limiting (login attempts)
- [ ] HTTPS/SSL
- [ ] Two-Factor Authentication
- [ ] API Key management (jika ada API)

---

## 🐛 Troubleshooting

### Error: "Database connection failed"

**Solusi:**
1. Pastikan MySQL service running
2. Cek credentials di `app/config/config.php`
3. Cek database `db_inventory_gudang` sudah dibuat:
   ```bash
   mysql -u root -p -e "SHOW DATABASES;" | grep db_inventory_gudang
   ```
4. Jika belum ada, import schema:
   ```bash
   mysql -u root -p < app/db_inventory_gudang.sql
   ```

### Error: "Undefined array key"

**Solusi:**
1. Pastikan POST data lengkap
2. Cek browser console untuk error JavaScript
3. Verifikasi form HTML di view file

### 403 Forbidden (Access Denied)

**Solusi:**
1. Cek permission file/folder:
   ```bash
   chmod 755 public/
   chmod 644 app/config/config.php
   ```
2. Pastikan mod_rewrite enabled (Apache)

### Modal tidak muncul / JavaScript tidak jalan

**Solusi:**
1. Cek browser console (F12) untuk error
2. Verifikasi Bootstrap CDN/local file loaded
3. Cek `.htaccess` redirect jangan interfere

### Transaksi gagal / Stok tidak update

**Solusi:**
1. Cek error log: `error_log()`
2. Verify database transaction support (InnoDB engine)
3. Pastikan quantity cukup untuk barang keluar
4. Cek foreign key constraint tidak violated

---

## 📊 Database Schema

### Tabel Utama

**1. pengguna** - User account
```
├─ id (PK)
├─ nama
├─ username (UNIQUE)
├─ password (bcrypt hash)
└─ role (ENUM: 'admin', 'staff_gudang')
```

**2. kategori_barang** - Item category
```
├─ id (PK)
├─ nama_kategori (UNIQUE)
└─ keterangan
```

**3. barang** - Item master data
```
├─ id (PK)
├─ kode_barang (UNIQUE, auto: BRG-001)
├─ nama_barang
├─ kategori_id (FK → kategori_barang)
├─ satuan
├─ stok
├─ stok_minimum
├─ harga_beli (DECIMAL 15,2)
├─ harga_jual (DECIMAL 15,2)
└─ lokasi_rak (format: A-01)
```

**4. supplier** - Supplier master data
```
├─ id (PK)
├─ kode_supplier (UNIQUE, auto: SPL-001)
├─ nama_supplier
├─ kontak
├─ email
└─ alamat
```

**5. transaksi_masuk** - Incoming item transaction
```
├─ id (PK)
├─ kode_transaksi (UNIQUE, auto: TRM-20261020-001)
├─ barang_id (FK → barang)
├─ supplier_id (FK → supplier)
├─ pengguna_id (FK → pengguna)
├─ jumlah
├─ tanggal_masuk
├─ keterangan
└─ dibuat_pada (TIMESTAMP)
```

**6. transaksi_keluar** - Outgoing item transaction
```
├─ id (PK)
├─ kode_transaksi (UNIQUE, auto: TRK-20261020-001)
├─ barang_id (FK → barang)
├─ pengguna_id (FK → pengguna)
├─ jumlah
├─ tanggal_keluar
├─ tujuan
├─ keterangan
└─ dibuat_pada (TIMESTAMP)
```

**7. v_stok_kritis** - View (barang dengan stok <= minimum)

---

## 📈 Fitur Upcoming

- [ ] REST API dengan JWT authentication
- [ ] Mobile app (React Native / Flutter)
- [ ] Dashboard analytics (chart.js / apex charts)
- [ ] Barcode scanning
- [ ] Multi-warehouse support
- [ ] Purchase order (PO) management
- [ ] Email notification (low stock alert)
- [ ] Two-factor authentication (2FA)
- [ ] Backup/restore otomatis
- [ ] Unit testing (PHPUnit)

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Fork repository, buat branch feature, dan submit pull request.

**Guideline:**
1. Fork repository
2. Buat branch: `git checkout -b feature/feature-name`
3. Commit: `git commit -am 'Add feature'`
4. Push: `git push origin feature/feature-name`
5. Submit Pull Request

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah **MIT License** - silakan gunakan untuk project pribadi dan komersial.

---

## 📞 Support & Contact

**Untuk pertanyaan, bug report, atau feature request:**
- Open issue di GitHub
- Email: admin@akmal-inventory.local
- Documentation: Lihat folder `docs/`

---

## 👨‍💻 Author

**Akmal Inventory Development Team**  
Created: 2026-10-20  
Version: 1.0.0

---

## 🙏 Acknowledgments

- Bootstrap 5 team untuk UI framework
- PHP-FIG untuk coding standards
- OWASP untuk security guidelines
- SKKNI No. 282 Tahun 2016 untuk skill mapping

---

**Selamat menggunakan A.K.M.A.L Inventory!** 🚀

Untuk bantuan setup atau konfigurasi lebih lanjut, baca file dokumentasi lainnya:
- `pemetaan_dan_analisis_projek.md`
- `PENJELASAN_MVC_DAN_CORE.md`
# akmal-inventory

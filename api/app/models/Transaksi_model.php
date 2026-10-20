<?php

// Model Transaksi - kelola transaksi masuk/keluar dan update stok
// Stok hanya berubah lewat model ini dengan Database Transaction
class Transaksi_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Ambil riwayat barang masuk
    public function getTransaksiMasuk()
    {
        $this->db->query(
            'SELECT t.id, t.kode_transaksi, t.jumlah, t.tanggal_masuk, t.keterangan,
                    b.kode_barang, b.nama_barang, b.satuan,
                    s.nama_supplier,
                    p.nama AS nama_pengguna
               FROM transaksi_masuk t
         INNER JOIN barang   b ON b.id = t.barang_id
         INNER JOIN supplier s ON s.id = t.supplier_id
         INNER JOIN pengguna p ON p.id = t.pengguna_id
           ORDER BY t.tanggal_masuk DESC, t.id DESC'
        );
        return $this->db->resultSet();
    }

    // Ambil riwayat barang keluar
    public function getTransaksiKeluar()
    {
        $this->db->query(
            'SELECT t.id, t.kode_transaksi, t.jumlah, t.tanggal_keluar, t.tujuan, t.keterangan,
                    b.kode_barang, b.nama_barang, b.satuan,
                    p.nama AS nama_pengguna
               FROM transaksi_keluar t
         INNER JOIN barang   b ON b.id = t.barang_id
         INNER JOIN pengguna p ON p.id = t.pengguna_id
           ORDER BY t.tanggal_keluar DESC, t.id DESC'
        );
        return $this->db->resultSet();
    }

    // Tambah transaksi masuk + update stok
    public function tambahTransaksiMasuk($data)
    {
        $this->db->beginTransaction();

        try {
            // Ambil dan kunci barang
            $barang = $this->ambilBarangTerkunci($data['barang_id']);
            if (!$barang) {
                $this->db->rollBack();
                return $this->hasil(false, 'Barang tidak ditemukan.');
            }

            // Buat kode transaksi
            $kode = $this->buatKode('transaksi_masuk', 'TRM', $data['tanggal_masuk']);

            // Insert transaksi
            $this->db->query(
                'INSERT INTO transaksi_masuk
                    (kode_transaksi, barang_id, supplier_id, pengguna_id, jumlah, tanggal_masuk, keterangan)
                 VALUES
                    (:kode, :barang_id, :supplier_id, :pengguna_id, :jumlah, :tanggal, :keterangan)'
            );
            $this->db->bind(':kode', $kode);
            $this->db->bind(':barang_id', $data['barang_id']);
            $this->db->bind(':supplier_id', $data['supplier_id']);
            $this->db->bind(':pengguna_id', $data['pengguna_id']);
            $this->db->bind(':jumlah', $data['jumlah']);
            $this->db->bind(':tanggal', $data['tanggal_masuk']);
            $this->db->bind(':keterangan', $data['keterangan'] === '' ? null : $data['keterangan']);
            $this->db->execute();

            // Update stok
            $this->db->query('UPDATE barang SET stok = stok + :jumlah WHERE id = :id');
            $this->db->bind(':jumlah', $data['jumlah']);
            $this->db->bind(':id', $data['barang_id']);
            $this->db->execute();

            $this->db->commit();

            $stokBaru = (int) $barang['stok'] + (int) $data['jumlah'];
            return $this->hasil(true, sprintf(
                'Barang masuk %s tercatat: stok %s bertambah %s menjadi %s %s.',
                $kode,
                $barang['nama_barang'],
                number_format($data['jumlah'], 0, ',', '.'),
                number_format($stokBaru, 0, ',', '.'),
                $barang['satuan']
            ));

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[Transaksi_model::tambahTransaksiMasuk] ' . $e->getMessage());
            return $this->hasil(false, 'Transaksi gagal disimpan dan tidak ada data yang berubah. Pastikan barang dan supplier valid, lalu coba lagi.');
        }
    }

    // Tambah transaksi keluar + update stok
    public function tambahTransaksiKeluar($data)
    {
        $this->db->beginTransaction();

        try {
            // Ambil dan kunci barang
            $barang = $this->ambilBarangTerkunci($data['barang_id']);
            if (!$barang) {
                $this->db->rollBack();
                return $this->hasil(false, 'Barang tidak ditemukan.');
            }

            // Cek stok cukup
            if ((int) $data['jumlah'] > (int) $barang['stok']) {
                $this->db->rollBack();
                return $this->hasil(false, sprintf(
                    'Stok tidak mencukupi! Stok %s saat ini hanya %s %s.',
                    $barang['nama_barang'],
                    number_format($barang['stok'], 0, ',', '.'),
                    $barang['satuan']
                ));
            }

            // Buat kode transaksi
            $kode = $this->buatKode('transaksi_keluar', 'TRK', $data['tanggal_keluar']);

            // Insert transaksi
            $this->db->query(
                'INSERT INTO transaksi_keluar
                    (kode_transaksi, barang_id, pengguna_id, jumlah, tanggal_keluar, tujuan, keterangan)
                 VALUES
                    (:kode, :barang_id, :pengguna_id, :jumlah, :tanggal, :tujuan, :keterangan)'
            );
            $this->db->bind(':kode', $kode);
            $this->db->bind(':barang_id', $data['barang_id']);
            $this->db->bind(':pengguna_id', $data['pengguna_id']);
            $this->db->bind(':jumlah', $data['jumlah']);
            $this->db->bind(':tanggal', $data['tanggal_keluar']);
            $this->db->bind(':tujuan', $data['tujuan'] === '' ? null : $data['tujuan']);
            $this->db->bind(':keterangan', $data['keterangan'] === '' ? null : $data['keterangan']);
            $this->db->execute();

            // Update stok
            $this->db->query('UPDATE barang SET stok = stok - :jumlah WHERE id = :id AND stok >= :jumlah_cek');
            $this->db->bind(':jumlah', $data['jumlah']);
            $this->db->bind(':id', $data['barang_id']);
            $this->db->bind(':jumlah_cek', $data['jumlah']);
            $this->db->execute();

            if ($this->db->rowCount() !== 1) {
                $this->db->rollBack();
                return $this->hasil(false, 'Stok tidak mencukupi!');
            }

            $this->db->commit();

            $stokBaru = (int) $barang['stok'] - (int) $data['jumlah'];
            return $this->hasil(true, sprintf(
                'Barang keluar %s tercatat: stok %s berkurang %s menjadi %s %s.',
                $kode,
                $barang['nama_barang'],
                number_format($data['jumlah'], 0, ',', '.'),
                number_format($stokBaru, 0, ',', '.'),
                $barang['satuan']
            ));

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[Transaksi_model::tambahTransaksiKeluar] ' . $e->getMessage());
            return $this->hasil(false, 'Transaksi gagal disimpan dan tidak ada data yang berubah. Silakan coba lagi.');
        }
    }

    // Ringkasan transaksi hari ini
    public function getRingkasanHariIni($tanggal)
    {
        $this->db->query(
            'SELECT COUNT(*) AS jumlah_transaksi,
                    COALESCE(SUM(jumlah), 0) AS total_qty,
                    COUNT(DISTINCT supplier_id) AS supplier_aktif
               FROM transaksi_masuk
              WHERE tanggal_masuk = :tanggal'
        );
        $this->db->bind(':tanggal', $tanggal);
        $masuk = $this->db->single();

        $this->db->query(
            'SELECT COUNT(*) AS jumlah_transaksi,
                    COALESCE(SUM(jumlah), 0) AS total_qty
               FROM transaksi_keluar
              WHERE tanggal_keluar = :tanggal'
        );
        $this->db->bind(':tanggal', $tanggal);
        $keluar = $this->db->single();

        return [
            'transaksi_masuk'  => (int) $masuk['jumlah_transaksi'],
            'transaksi_keluar' => (int) $keluar['jumlah_transaksi'],
            'qty_masuk'        => (int) $masuk['total_qty'],
            'qty_keluar'       => (int) $keluar['total_qty'],
            'supplier_aktif'   => (int) $masuk['supplier_aktif'],
        ];
    }

    // Aktivitas transaksi terbaru (gabungan masuk + keluar)
    public function getAktivitasTerbaru($limit = 8)
    {
        $limit = (int) $limit;

        try {
            $this->db->query(
                "(SELECT 'masuk' AS jenis, t.id, t.kode_transaksi, t.jumlah, t.dibuat_pada,
                         b.nama_barang, b.satuan, p.nama AS nama_pengguna
                    FROM transaksi_masuk t
              INNER JOIN barang   b ON b.id = t.barang_id
              INNER JOIN pengguna p ON p.id = t.pengguna_id)
                 UNION ALL
                 (SELECT 'keluar' AS jenis, t.id, t.kode_transaksi, t.jumlah, t.dibuat_pada,
                         b.nama_barang, b.satuan, p.nama AS nama_pengguna
                    FROM transaksi_keluar t
              INNER JOIN barang   b ON b.id = t.barang_id
              INNER JOIN pengguna p ON p.id = t.pengguna_id)
                 ORDER BY dibuat_pada DESC, id DESC
                    LIMIT " . $limit
            );
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log('[Transaksi_model::getAktivitasTerbaru] ' . $e->getMessage());
            return null;
        }
    }

    // Ambil barang dan kunci barisnya (FOR UPDATE)
    private function ambilBarangTerkunci($id)
    {
        $this->db->query('SELECT id, nama_barang, satuan, stok FROM barang WHERE id = :id FOR UPDATE');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Buat kode transaksi: AWALAN-YYYYMMDD-NNN
    private function buatKode($tabel, $awalan, $tanggal)
    {
        $prefix = $awalan . '-' . str_replace('-', '', $tanggal) . '-';

        $this->db->query(
            'SELECT kode_transaksi FROM ' . $tabel . '
              WHERE kode_transaksi LIKE :prefix
           ORDER BY LENGTH(kode_transaksi) DESC, kode_transaksi DESC
              LIMIT 1'
        );
        $this->db->bind(':prefix', $prefix . '%');
        $terakhir = $this->db->single();

        $nomor = $terakhir ? ((int) substr($terakhir['kode_transaksi'], strlen($prefix)) + 1) : 1;

        return $prefix . str_pad((string) $nomor, 3, '0', STR_PAD_LEFT);
    }

    private function hasil($sukses, $pesan)
    {
        return ['sukses' => $sukses, 'pesan' => $pesan];
    }
}

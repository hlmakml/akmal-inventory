<?php

// Model Laporan - laporan stok dan transaksi dengan filter
class Laporan_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Ambil laporan stok barang (dengan filter kategori)
    public function getLaporanStok($kategoriId = 0)
    {
        $sql = 'SELECT b.id, b.kode_barang, b.nama_barang, b.satuan,
                       b.stok, b.stok_minimum, k.nama_kategori
                  FROM barang b
            INNER JOIN kategori_barang k ON k.id = b.kategori_id';

        if ($kategoriId > 0) {
            $sql .= ' WHERE b.kategori_id = :kategori';
        }
        $sql .= ' ORDER BY b.nama_barang ASC';

        $this->db->query($sql);
        if ($kategoriId > 0) {
            $this->db->bind(':kategori', (int) $kategoriId);
        }
        return $this->db->resultSet();
    }

    // Total transaksi masuk dan keluar (dengan filter tanggal dan kategori)
    public function getTotalTransaksi($dari = '', $sampai = '', $kategoriId = 0)
    {
        return [
            'masuk'  => $this->jumlahkan('transaksi_masuk', 'tanggal_masuk', $dari, $sampai, $kategoriId),
            'keluar' => $this->jumlahkan('transaksi_keluar', 'tanggal_keluar', $dari, $sampai, $kategoriId),
        ];
    }

    // Jumlahkan qty transaksi dengan filter
    private function jumlahkan($tabel, $kolomTanggal, $dari, $sampai, $kategoriId)
    {
        $sql = 'SELECT COALESCE(SUM(t.jumlah), 0) AS total
                  FROM ' . $tabel . ' t
            INNER JOIN barang b ON b.id = t.barang_id';

        $kondisi = [];
        if ($dari !== '') {
            $kondisi[] = 't.' . $kolomTanggal . ' >= :dari';
        }
        if ($sampai !== '') {
            $kondisi[] = 't.' . $kolomTanggal . ' <= :sampai';
        }
        if ($kategoriId > 0) {
            $kondisi[] = 'b.kategori_id = :kategori';
        }
        if (!empty($kondisi)) {
            $sql .= ' WHERE ' . implode(' AND ', $kondisi);
        }

        $this->db->query($sql);
        if ($dari !== '') {
            $this->db->bind(':dari', $dari);
        }
        if ($sampai !== '') {
            $this->db->bind(':sampai', $sampai);
        }
        if ($kategoriId > 0) {
            $this->db->bind(':kategori', (int) $kategoriId);
        }

        $hasil = $this->db->single();
        return (int) $hasil['total'];
    }
}

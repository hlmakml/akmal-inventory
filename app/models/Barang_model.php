<?php

// Model: Item and category data
class Barang_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Get all items with category name
    public function getAllBarang()
    {
        $this->db->query(
            'SELECT b.*, k.nama_kategori
               FROM barang b
         INNER JOIN kategori_barang k ON k.id = b.kategori_id
           ORDER BY b.nama_barang ASC'
        );
        return $this->db->resultSet();
    }

    // Get one item by id
    public function getBarangById($id)
    {
        $this->db->query('SELECT * FROM barang WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get one item by code
    public function getBarangByKode($kode)
    {
        $this->db->query('SELECT id, kode_barang FROM barang WHERE kode_barang = :kode LIMIT 1');
        $this->db->bind(':kode', $kode);
        return $this->db->single();
    }

    // Get all categories for dropdown
    public function getAllKategori()
    {
        $this->db->query('SELECT id, nama_kategori FROM kategori_barang ORDER BY nama_kategori ASC');
        return $this->db->resultSet();
    }

    // Add new item
    public function tambahDataBarang($data)
    {
        // Try generating code up to 3 times if duplicate
        for ($percobaan = 1; $percobaan <= 3; $percobaan++) {
            $kode = $this->buatKodeBarang();

            try {
                $this->db->query(
                    'INSERT INTO barang
                        (kode_barang, nama_barang, kategori_id, satuan, stok_minimum, harga_beli, harga_jual, lokasi_rak)
                     VALUES
                        (:kode_barang, :nama_barang, :kategori_id, :satuan, :stok_minimum, :harga_beli, :harga_jual, :lokasi_rak)'
                );
                $this->db->bind(':kode_barang', $kode);
                $this->bindDataBarang($data);
                $this->db->execute();

                return ($this->db->rowCount() > 0) ? $kode : false;

            } catch (PDOException $e) {
                // 1062 = duplicate entry, retry
                $kodeError = isset($e->errorInfo[1]) ? (int) $e->errorInfo[1] : 0;
                if ($kodeError === 1062 && $percobaan < 3) {
                    continue;
                }
                throw $e;
            }
        }

        return false;
    }

    // Update item
    public function ubahDataBarang($data)
    {
        $this->db->query(
            'UPDATE barang SET
                nama_barang  = :nama_barang,
                kategori_id  = :kategori_id,
                satuan       = :satuan,
                stok_minimum = :stok_minimum,
                harga_beli   = :harga_beli,
                harga_jual   = :harga_jual,
                lokasi_rak   = :lokasi_rak
             WHERE id = :id'
        );
        $this->bindDataBarang($data);
        $this->db->bind(':id', $data['id']);
        $this->db->execute();

        return $this->db->rowCount();
    }

    // Delete item
    public function hapusDataBarang($id)
    {
        $this->db->query('DELETE FROM barang WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();

        return $this->db->rowCount();
    }

    // Count total items
    public function hitungTotalBarang()
    {
        $this->db->query('SELECT COUNT(*) AS total FROM barang');
        $hasil = $this->db->single();
        return (int) $hasil['total'];
    }

    // Count total categories
    public function hitungTotalKategori()
    {
        $this->db->query('SELECT COUNT(*) AS total FROM kategori_barang');
        $hasil = $this->db->single();
        return (int) $hasil['total'];
    }

    // Count critical stock items
    public function hitungStokKritis()
    {
        $this->db->query('SELECT COUNT(*) AS total FROM v_stok_kritis');
        $hasil = $this->db->single();
        return (int) $hasil['total'];
    }

    // Get critical stock items list
    public function getBarangStokKritis($limit = 5)
    {
        $limit = (int) $limit;
        $this->db->query(
            'SELECT kode_barang, nama_barang, satuan, stok, stok_minimum, lokasi_rak
               FROM v_stok_kritis
           ORDER BY kekurangan DESC, nama_barang ASC
              LIMIT ' . $limit
        );
        return $this->db->resultSet();
    }

    // Generate item code: BRG-001, BRG-002, ...
    private function buatKodeBarang()
    {
        $this->db->query(
            "SELECT MAX(CAST(SUBSTRING(kode_barang, 5) AS UNSIGNED)) AS maks
               FROM barang
              WHERE kode_barang REGEXP '^BRG-[0-9]+$'"
        );
        $hasil = $this->db->single();
        $nomor = ((int) $hasil['maks']) + 1;

        return 'BRG-' . str_pad((string) $nomor, 3, '0', STR_PAD_LEFT);
    }

    // Bind data for INSERT and UPDATE
    private function bindDataBarang($data)
    {
        $lokasi = ($data['lokasi_rak'] === '' || $data['lokasi_rak'] === null) ? null : $data['lokasi_rak'];

        $this->db->bind(':nama_barang', $data['nama_barang']);
        $this->db->bind(':kategori_id', $data['kategori_id']);
        $this->db->bind(':satuan', $data['satuan']);
        $this->db->bind(':stok_minimum', $data['stok_minimum']);
        $this->db->bind(':harga_beli', $data['harga_beli']);
        $this->db->bind(':harga_jual', $data['harga_jual']);
        $this->db->bind(':lokasi_rak', $lokasi);
    }
}

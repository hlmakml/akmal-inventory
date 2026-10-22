<?php

// Model Supplier - kelola data supplier
class Supplier_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Ambil semua supplier
    public function getAllSupplier()
    {
        $this->db->query('SELECT id, kode_supplier, nama_supplier, kontak, alamat, email FROM supplier ORDER BY nama_supplier ASC');
        return $this->db->resultSet();
    }

    // Ambil satu supplier by id
    public function getSupplierById($id)
    {
        $this->db->query('SELECT id, kode_supplier, nama_supplier, kontak, alamat, email FROM supplier WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Hitung total supplier
    public function hitungTotalSupplier()
    {
        $this->db->query('SELECT COUNT(*) AS total FROM supplier');
        $hasil = $this->db->single();
        return (int) $hasil['total'];
    }

    // Tambah supplier baru
    public function tambahDataSupplier($data)
    {
        // Coba buat kode sampai 3x jika bentrok
        for ($percobaan = 1; $percobaan <= 3; $percobaan++) {
            $kode = $this->buatKodeSupplier();

            try {
                $this->db->query(
                    'INSERT INTO supplier (kode_supplier, nama_supplier, kontak, alamat, email)
                     VALUES (:kode_supplier, :nama_supplier, :kontak, :alamat, :email)'
                );
                $this->db->bind(':kode_supplier', $kode);
                $this->bindDataSupplier($data);
                $this->db->execute();

                return ($this->db->rowCount() > 0) ? $kode : false;

            } catch (PDOException $e) {
                // 1062 = duplicate entry, coba lagi
                $kodeError = isset($e->errorInfo[1]) ? (int) $e->errorInfo[1] : 0;
                if ($kodeError === 1062 && $percobaan < 3) {
                    continue;
                }
                throw $e;
            }
        }

        return false;
    }

    // Update supplier
    public function ubahDataSupplier($data)
    {
        $this->db->query(
            'UPDATE supplier SET
                nama_supplier = :nama_supplier,
                kontak        = :kontak,
                alamat        = :alamat,
                email         = :email
             WHERE id = :id'
        );
        $this->bindDataSupplier($data);
        $this->db->bind(':id', $data['id']);
        $this->db->execute();

        return $this->db->rowCount();
    }

    // Hapus supplier
    public function hapusDataSupplier($id)
    {
        $this->db->query('DELETE FROM supplier WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();

        return $this->db->rowCount();
    }

    // Buat kode supplier otomatis: SPL-001, SPL-002, ...
    private function buatKodeSupplier()
    {
        $this->db->query(
            "SELECT MAX(CAST(SUBSTRING(kode_supplier, 5) AS UNSIGNED)) AS maks
               FROM supplier
              WHERE kode_supplier REGEXP '^SPL-[0-9]+$'"
        );
        $hasil = $this->db->single();
        $nomor = ((int) $hasil['maks']) + 1;

        return 'SPL-' . str_pad((string) $nomor, 3, '0', STR_PAD_LEFT);
    }

    // Bind data untuk INSERT dan UPDATE
    private function bindDataSupplier($data)
    {
        $this->db->bind(':nama_supplier', $data['nama_supplier']);
        $this->db->bind(':kontak', $data['kontak'] === '' ? null : $data['kontak']);
        $this->db->bind(':alamat', $data['alamat'] === '' ? null : $data['alamat']);
        $this->db->bind(':email', $data['email'] === '' ? null : $data['email']);
    }
}

<?php

// Model: User data management
class Pengguna_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Get user by username (for login)
    public function getPenggunaByUsername($username)
    {
        $this->db->query(
            'SELECT id, nama, username, password, role
               FROM pengguna
              WHERE username = :username
              LIMIT 1'
        );
        $this->db->bind(':username', $username);

        return $this->db->single();
    }

    // Get all users (without password)
    public function getAllPengguna()
    {
        $this->db->query('SELECT id, nama, username, role FROM pengguna ORDER BY role ASC, nama ASC');
        return $this->db->resultSet();
    }

    // Get one user by id (without password)
    public function getPenggunaById($id)
    {
        $this->db->query('SELECT id, nama, username, role FROM pengguna WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Add new user
    public function tambahDataPengguna($data)
    {
        $this->db->query(
            'INSERT INTO pengguna (nama, username, password, role)
             VALUES (:nama, :username, :password, :role)'
        );
        $this->db->bind(':nama', $data['nama']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':role', $data['role']);
        $this->db->execute();

        return $this->db->rowCount();
    }

    // Update user (password optional)
    public function ubahDataPengguna($data)
    {
        $gantiPassword = ($data['password'] !== '');

        $sql = 'UPDATE pengguna SET nama = :nama, username = :username, role = :role';
        if ($gantiPassword) {
            $sql .= ', password = :password';
        }
        $sql .= ' WHERE id = :id';

        $this->db->query($sql);
        $this->db->bind(':nama', $data['nama']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':role', $data['role']);
        if ($gantiPassword) {
            $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        }
        $this->db->bind(':id', $data['id']);
        $this->db->execute();

        return $this->db->rowCount();
    }

    // Delete user
    public function hapusDataPengguna($id)
    {
        $this->db->query('DELETE FROM pengguna WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();

        return $this->db->rowCount();
    }
}

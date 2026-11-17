<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * RoleModel
 *
 * Mengelola level pengguna (roles) untuk RBAC sistem:
 * 1. Admin (Administrator Sistem)
 * 2. Petugas (Petugas Pendaftaran)
 * 3. Dokter (Dokter Pemeriksa)
 */
class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'name',
        'label',
        'description',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua peran yang tersedia.
     */
    public function getAllRoles(): array
    {
        return $this->orderBy('id', 'ASC')->findAll();
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['role_id', 'username', 'email', 'password', 'fullname', 'status'];
    protected $useTimestamps    = true;
    protected $deletedField     = 'deleted_at';

    /**
     * Cari user berdasarkan username atau email untuk proses login.
     */
    public function findByUsernameOrEmail(string $identifier): ?array
    {
        return $this->select('users.*, roles.name as role_name, roles.label as role_label')
                    ->join('roles', 'roles.id = users.role_id')
                    ->where('users.deleted_at', null)
                    ->groupStart()
                        ->where('users.username', $identifier)
                        ->orWhere('users.email', $identifier)
                    ->groupEnd()
                    ->first();
    }

    /**
     * Ambil semua permission milik user berdasarkan role-nya.
     */
    public function getUserPermissions(int $userId): array
    {
        $db = \Config\Database::connect();
        $result = $db->table('permissions p')
                     ->select('p.name')
                     ->join('role_permissions rp', 'rp.permission_id = p.id')
                     ->join('users u', 'u.role_id = rp.role_id')
                     ->where('u.id', $userId)
                     ->get()
                     ->getResultArray();

        return array_column($result, 'name');
    }
}

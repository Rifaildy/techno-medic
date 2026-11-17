<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UserModel
 *
 * Mengelola data akun pengguna, relasi peran (roles), izin akses (permissions),
 * serta operasi CRUD pengguna untuk administrasi sistem.
 */
class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'role_id',
        'username',
        'email',
        'password',
        'fullname',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

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

    /**
     * Mengambil daftar pengguna dengan filter, pencarian, dan paginasi.
     *
     * @param array $params
     * @return array{items: array, pagination: array}
     */
    public function getPaginatedUsers(array $params = []): array
    {
        $search  = trim((string) ($params['search'] ?? ''));
        $roleId  = (int) ($params['role_id'] ?? 0);
        $status  = trim((string) ($params['status'] ?? ''));
        $page    = max(1, (int) ($params['page'] ?? 1));
        $perPage = (int) ($params['per_page'] ?? 10);

        $builder = $this->db->table($this->table . ' u')
            ->select('u.id, u.role_id, u.username, u.email, u.fullname, u.status, u.created_at, u.updated_at,
                      r.name as role_name, r.label as role_label')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.deleted_at', null);

        if ($roleId > 0) {
            $builder->where('u.role_id', $roleId);
        }

        if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
            $builder->where('u.status', $status);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('u.username', $search)
                ->orLike('u.fullname', $search)
                ->orLike('u.email', $search)
                ->orLike('r.label', $search)
            ->groupEnd();
        }

        $totalRows  = $builder->countAllResults(false);
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;
        $offset     = ($page - 1) * $perPage;

        $items = $builder->orderBy('u.id', 'ASC')
                         ->limit($perPage, $offset)
                         ->get()
                         ->getResultArray();

        return [
            'items'      => $items,
            'pagination' => [
                'total'        => $totalRows,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => $totalPages,
                'from'         => $totalRows === 0 ? 0 : $offset + 1,
                'to'           => min($offset + $perPage, $totalRows),
            ],
        ];
    }

    /**
     * Ambil detail lengkap 1 user beserta nama role.
     */
    public function getUserDetail(int $id): ?array
    {
        return $this->db->table($this->table . ' u')
            ->select('u.id, u.role_id, u.username, u.email, u.fullname, u.status, u.created_at, u.updated_at,
                      r.name as role_name, r.label as role_label')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.id', $id)
            ->where('u.deleted_at', null)
            ->get()
            ->getRowArray();
    }

    /**
     * Ambil statistik ringkasan pengguna sistem.
     */
    public function getSummaryCounts(): array
    {
        $total = $this->where('deleted_at', null)->countAllResults();
        $active = $this->where('status', 'active')->where('deleted_at', null)->countAllResults();
        $inactive = $this->where('status', 'inactive')->where('deleted_at', null)->countAllResults();

        $admins = $this->where('role_id', 1)->where('deleted_at', null)->countAllResults();
        $petugas = $this->where('role_id', 2)->where('deleted_at', null)->countAllResults();
        $doctors = $this->where('role_id', 3)->where('deleted_at', null)->countAllResults();

        return [
            'total'    => $total,
            'active'   => $active,
            'inactive' => $inactive,
            'admins'   => $admins,
            'petugas'  => $petugas,
            'doctors'  => $doctors,
        ];
    }

    /**
     * Ambil daftar user dokter yang belum ditautkan ke master dokter (atau milik dokter tertentu).
     */
    public function getAvailableDoctorAccounts(?int $currentUserId = null): array
    {
        $builder = $this->db->table($this->table . ' u')
            ->select('u.id, u.username, u.fullname, u.email')
            ->where('u.role_id', 3) // role dokter
            ->where('u.status', 'active')
            ->where('u.deleted_at', null);

        // Subquery: user_id yang sudah digunakan di tabel doctors
        $usedUserIds = $this->db->table('doctors')
            ->select('user_id')
            ->where('user_id IS NOT NULL', null, false)
            ->where('deleted_at', null);

        if ($currentUserId !== null) {
            $usedUserIds->where('user_id !=', $currentUserId);
        }

        $usedArray = array_column($usedUserIds->get()->getResultArray(), 'user_id');

        if (! empty($usedArray)) {
            $builder->whereNotIn('u.id', $usedArray);
        }

        return $builder->orderBy('u.fullname', 'ASC')->get()->getResultArray();
    }
}

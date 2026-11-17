<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * DoctorModel
 *
 * Mengelola data master dokter rumah sakit, relasi dengan poliklinik, dan akun login dokter.
 */
class DoctorModel extends Model
{
    protected $table            = 'doctors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'polyclinic_id',
        'nik',
        'sip',
        'name',
        'specialization',
        'phone',
        'email',
        'gender',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Ambil daftar dokter aktif berdasarkan poliklinik terpilih.
     * Digunakan untuk dependency dropdown poliklinik -> dokter.
     */
    public function getDoctorsByPolyclinic(int $polyclinicId): array
    {
        return $this->select('id, name, specialization, sip, gender, polyclinic_id')
                    ->where('polyclinic_id', $polyclinicId)
                    ->where('status', 'active')
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil seluruh dokter aktif beserta nama polikliniknya.
     */
    public function getActiveDoctorsWithPoly(): array
    {
        return $this->select('doctors.*, polyclinics.name as polyclinic_name, polyclinics.code as polyclinic_code')
                    ->join('polyclinics', 'polyclinics.id = doctors.polyclinic_id', 'left')
                    ->where('doctors.status', 'active')
                    ->orderBy('polyclinics.name', 'ASC')
                    ->orderBy('doctors.name', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil daftar dokter dengan paginasi, pencarian, dan filter poliklinik/status.
     */
    public function getPaginatedDoctors(array $params = []): array
    {
        $search       = trim((string) ($params['search'] ?? ''));
        $polyclinicId = (int) ($params['polyclinic_id'] ?? 0);
        $gender       = trim((string) ($params['gender'] ?? ''));
        $status       = trim((string) ($params['status'] ?? ''));
        $page         = max(1, (int) ($params['page'] ?? 1));
        $perPage      = (int) ($params['per_page'] ?? 10);

        $builder = $this->db->table($this->table . ' d')
            ->select('d.*, 
                      p.name as polyclinic_name, p.code as polyclinic_code, p.location as polyclinic_location,
                      u.username as user_username, u.fullname as user_fullname, u.email as user_email')
            ->join('polyclinics p', 'p.id = d.polyclinic_id', 'left')
            ->join('users u', 'u.id = d.user_id', 'left')
            ->where('d.deleted_at', null);

        if ($polyclinicId > 0) {
            $builder->where('d.polyclinic_id', $polyclinicId);
        }

        if ($gender !== '' && in_array($gender, ['L', 'P'], true)) {
            $builder->where('d.gender', $gender);
        }

        if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
            $builder->where('d.status', $status);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('d.name', $search)
                ->orLike('d.nik', $search)
                ->orLike('d.sip', $search)
                ->orLike('d.specialization', $search)
                ->orLike('p.name', $search)
            ->groupEnd();
        }

        $totalRows  = $builder->countAllResults(false);
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;
        $offset     = ($page - 1) * $perPage;

        $items = $builder->orderBy('d.name', 'ASC')
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
     * Ambil detail 1 dokter lengkap beserta info poli dan akun pengguna.
     */
    public function getDoctorDetail(int $id): ?array
    {
        return $this->db->table($this->table . ' d')
            ->select('d.*, 
                      p.name as polyclinic_name, p.code as polyclinic_code, p.location as polyclinic_location,
                      u.username as user_username, u.fullname as user_fullname, u.email as user_email')
            ->join('polyclinics p', 'p.id = d.polyclinic_id', 'left')
            ->join('users u', 'u.id = d.user_id', 'left')
            ->where('d.id', $id)
            ->where('d.deleted_at', null)
            ->get()
            ->getRowArray();
    }

    /**
     * Ambil statistik ringkasan dokter.
     */
    public function getSummaryCounts(): array
    {
        $total    = $this->where('deleted_at', null)->countAllResults();
        $active   = $this->where('status', 'active')->where('deleted_at', null)->countAllResults();
        $inactive = $this->where('status', 'inactive')->where('deleted_at', null)->countAllResults();
        $male     = $this->where('gender', 'L')->where('deleted_at', null)->countAllResults();
        $female   = $this->where('gender', 'P')->where('deleted_at', null)->countAllResults();

        return [
            'total'    => $total,
            'active'   => $active,
            'inactive' => $inactive,
            'male'     => $male,
            'female'   => $female,
        ];
    }
}

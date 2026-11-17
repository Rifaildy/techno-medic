<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * PolyclinicModel
 *
 * Mengelola data master poliklinik rawat jalan, relasi dokter, serta ringkasan pelayanan.
 */
class PolyclinicModel extends Model
{
    protected $table            = 'polyclinics';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'code',
        'name',
        'location',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Ambil semua poliklinik yang berstatus aktif.
     */
    public function getActivePolyclinics(): array
    {
        return $this->where('status', 'active')
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil daftar poliklinik dengan paginasi, pencarian, dan hitungan dokter aktif.
     */
    public function getPaginatedPolyclinics(array $params = []): array
    {
        $search  = trim((string) ($params['search'] ?? ''));
        $status  = trim((string) ($params['status'] ?? ''));
        $page    = max(1, (int) ($params['page'] ?? 1));
        $perPage = (int) ($params['per_page'] ?? 10);

        $today = date('Y-m-d');

        // Subquery untuk menghitung dokter aktif di poliklinik
        $builder = $this->db->table($this->table . ' p')
            ->select('p.*, 
                      (SELECT COUNT(*) FROM doctors d WHERE d.polyclinic_id = p.id AND d.status = "active" AND d.deleted_at IS NULL) as active_doctors_count,
                      (SELECT COUNT(*) FROM registrations r WHERE r.polyclinic_id = p.id AND r.registration_date = ' . $this->db->escape($today) . ' AND r.deleted_at IS NULL) as today_visits_count')
            ->where('p.deleted_at', null);

        if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
            $builder->where('p.status', $status);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('p.code', $search)
                ->orLike('p.name', $search)
                ->orLike('p.location', $search)
            ->groupEnd();
        }

        $totalRows  = $builder->countAllResults(false);
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;
        $offset     = ($page - 1) * $perPage;

        $items = $builder->orderBy('p.id', 'ASC')
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
     * Ambil detail poliklinik beserta daftar dokter yang bertugas.
     */
    public function getPolyclinicDetail(int $id): ?array
    {
        $poly = $this->where('id', $id)->first();
        if (! $poly) {
            return null;
        }

        // Ambil dokter bertugas di poli ini
        $doctors = $this->db->table('doctors d')
            ->select('d.id, d.name, d.specialization, d.sip, d.phone, d.status')
            ->where('d.polyclinic_id', $id)
            ->where('d.deleted_at', null)
            ->orderBy('d.name', 'ASC')
            ->get()
            ->getResultArray();

        $poly['doctors'] = $doctors;

        return $poly;
    }

    /**
     * Ambil statistik ringkasan poliklinik.
     */
    public function getSummaryCounts(): array
    {
        $total    = $this->where('deleted_at', null)->countAllResults();
        $active   = $this->where('status', 'active')->where('deleted_at', null)->countAllResults();
        $inactive = $this->where('status', 'inactive')->where('deleted_at', null)->countAllResults();

        return [
            'total'    => $total,
            'active'   => $active,
            'inactive' => $inactive,
        ];
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * PatientModel
 *
 * Mengelola operasi database untuk tabel `patients`, termasuk:
 * - Pembuatan Nomor Rekam Medis (No. RM) unik secara berurutan (RM-XXXXXX)
 * - Pencarian berdasarkan No. RM, NIK, atau Nama Pasien
 * - Paginasi dinamis (10/20/30/40/50) serta dukungan soft delete (aktif/nonaktif)
 * - Validasi riwayat pemeriksaan sebelum penghapusan permanen
 */
class PatientModel extends Model
{
    protected $table            = 'patients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'no_rm',
        'nik',
        'name',
        'gender',
        'pob',
        'dob',
        'phone',
        'address',
        'blood_type',
        'insurance_type',
        'insurance_number',
    ];

    // Pengaturan Timestamp & Soft Delete otomatis CI4
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Menghasilkan Nomor Rekam Medis unik berikutnya dengan format RM-XXXXXX.
     * Memeriksa termasuk data yang sudah di-soft-delete agar tidak melanggar constraint UNIQUE.
     *
     * @return string
     */
    public function generateNoRM(): string
    {
        $lastPatient = $this->withDeleted()
            ->select('no_rm')
            ->like('no_rm', 'RM-', 'after')
            ->orderBy('id', 'DESC')
            ->first();

        $nextNumber = 1;

        if ($lastPatient && ! empty($lastPatient['no_rm'])) {
            $numericPart = (int) preg_replace('/[^0-9]/', '', $lastPatient['no_rm']);
            $nextNumber  = $numericPart + 1;
        }

        // Pastikan nomor yang dihasilkan benar-benar belum terpakai
        do {
            $candidate = 'RM-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
            $exists    = $this->withDeleted()->where('no_rm', $candidate)->countAllResults() > 0;
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $candidate;
    }

    /**
     * Mengambil daftar pasien dengan pencarian AJAX, filter, dan paginasi.
     *
     * @param string $search    Kata kunci pencarian (No. RM / NIK / Nama)
     * @param int    $perPage   Jumlah data per halaman (10, 20, 30, 40, 50)
     * @param int    $page      Nomor halaman saat ini
     * @param string $status    Filter status: 'active' | 'inactive' | 'all'
     * @param string $insurance Filter penjamin: '' | 'UMUM' | 'BPJS'
     *
     * @return array{items: array, pagination: array}
     */
    public function getPaginatedPatients(
        string $search = '',
        int $perPage = 10,
        int $page = 1,
        string $status = 'active',
        string $insurance = ''
    ): array {
        // Reset builder agar query tetap bersih antar pemanggilan
        $builder = $this->builder();

        // Filter status aktif / nonaktif (soft delete)
        if ($status === 'inactive') {
            $this->onlyDeleted();
        } elseif ($status === 'all') {
            $this->withDeleted();
        }

        // Filter jenis penjamin (UMUM / BPJS)
        if (in_array($insurance, ['UMUM', 'BPJS'], true)) {
            $this->where('insurance_type', $insurance);
        }

        // Pencarian berdasarkan No. Rekam Medis, NIK, atau Nama Lengkap
        $search = trim($search);
        if ($search !== '') {
            $this->groupStart()
                ->like('no_rm', $search)
                ->orLike('nik', $search)
                ->orLike('name', $search)
                ->groupEnd();
        }

        $this->orderBy('id', 'DESC');

        $items = $this->paginate($perPage, 'default', $page);
        $pager = $this->pager;

        // Tandai apakah masing-masing pasien sudah pernah menjalani pemeriksaan
        foreach ($items as &$item) {
            $item['has_examination'] = $this->hasExaminationHistory((int) $item['id']);
        }
        unset($item);

        $total       = $pager->getTotal('default');
        $currentPage = $pager->getCurrentPage('default');
        $pageCount   = $pager->getPageCount('default');

        $from = $total > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
        $to   = $total > 0 ? min($currentPage * $perPage, $total) : 0;

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $currentPage,
                'per_page'     => $perPage,
                'total'        => $total,
                'total_pages'  => max(1, $pageCount),
                'from'         => $from,
                'to'           => $to,
            ],
        ];
    }

    /**
     * Memeriksa apakah pasien sudah pernah menjalani pemeriksaan / kunjungan.
     * Mengembalikan true jika ditemukan minimal 1 riwayat pemeriksaan/kunjungan.
     */
    public function hasExaminationHistory(int $patientId): bool
    {
        $db = $this->db;

        // Daftar tabel relasi pemeriksaan / rekam medis / kunjungan rawat jalan.
        // Sesuaikan jika nama tabel di database Anda berbeda.
        $relatedTables = [
            'examinations',
            'medical_records',
            'visits',
            'registrations',
        ];

        foreach ($relatedTables as $table) {
            if ($db->tableExists($table) && $db->fieldExists('patient_id', $table)) {
                $exists = $db->table($table)
                    ->select('1', false)
                    ->where('patient_id', $patientId)
                    ->limit(1)
                    ->get()
                    ->getRow();

                if ($exists !== null) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Menghapus data pasien secara permanen dari database (purge = true).
     */
    public function permanentDeletePatient(int $patientId): bool
    {
        return (bool) $this->delete($patientId, true);
    }

    /**
     * Menghitung ringkasan statistik data pasien untuk kartu KPI.
     *
     * @return array{total_active: int, total_umum: int, total_bpjs: int, total_inactive: int}
     */
    public function getSummaryCounts(): array
    {
        $db = $this->db;

        $totalActive = (int) $db->table($this->table)
            ->where('deleted_at', null)
            ->countAllResults();

        $totalUmum = (int) $db->table($this->table)
            ->where('deleted_at', null)
            ->where('insurance_type', 'UMUM')
            ->countAllResults();

        $totalBpjs = (int) $db->table($this->table)
            ->where('deleted_at', null)
            ->where('insurance_type', 'BPJS')
            ->countAllResults();

        $totalInactive = (int) $db->table($this->table)
            ->where('deleted_at IS NOT NULL', null, false)
            ->countAllResults();

        return [
            'total_active'   => $totalActive,
            'total_umum'     => $totalUmum,
            'total_bpjs'     => $totalBpjs,
            'total_inactive' => $totalInactive,
        ];
    }
}
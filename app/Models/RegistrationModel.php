<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * RegistrationModel
 *
 * Mengelola pendaftaran pasien rawat jalan dengan aturan bisnis:
 * 1. Pasien harus sudah terdaftar.
 * 2. Dokter harus sesuai dengan poliklinik yang dipilih.
 * 3. Nomor pendaftaran unik dan tidak boleh duplikat.
 * 4. Pasien tidak boleh memiliki pendaftaran aktif ganda pada dokter dan tanggal yang sama.
 * 5. Status pendaftaran: MENUNGGU, DIPERIKSA, SELESAI, BATAL.
 */
class RegistrationModel extends Model
{
    protected $table            = 'registrations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'registration_number',
        'registration_date',
        'patient_id',
        'polyclinic_id',
        'doctor_id',
        'queue_number',
        'payment_type',
        'complaint',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Menghasilkan nomor pendaftaran unik berikutnya dengan format REG/YYYYMMDD/XXX.
     *
     * @param string $date Tanggal kunjungan (format: Y-m-d)
     * @return string
     */
    public function generateRegistrationNumber(string $date): string
    {
        $cleanDate   = date('Ymd', strtotime($date));
        $prefix      = 'REG/' . $cleanDate . '/';

        $lastRecord = $this->withDeleted()
            ->select('registration_number')
            ->like('registration_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        $nextNumber = 1;

        if ($lastRecord && ! empty($lastRecord['registration_number'])) {
            $parts = explode('/', $lastRecord['registration_number']);
            $lastIndex = end($parts);
            $nextNumber = ((int) $lastIndex) + 1;
        }

        do {
            $candidate = $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            $exists    = $this->withDeleted()->where('registration_number', $candidate)->countAllResults() > 0;
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $candidate;
    }

    /**
     * Menghitung nomor antrean berikutnya untuk poliklinik, dokter, dan tanggal tertentu.
     *
     * @param int    $polyclinicId
     * @param int    $doctorId
     * @param string $date (format: Y-m-d)
     * @return int
     */
    public function getNextQueueNumber(int $polyclinicId, int $doctorId, string $date): int
    {
        $maxQueue = $this->withDeleted()
            ->selectMax('queue_number')
            ->where('polyclinic_id', $polyclinicId)
            ->where('doctor_id', $doctorId)
            ->where('registration_date', $date)
            ->first();

        return (isset($maxQueue['queue_number']) && $maxQueue['queue_number'] > 0)
            ? ((int) $maxQueue['queue_number']) + 1
            : 1;
    }

    /**
     * Memeriksa apakah pasien sudah memiliki pendaftaran aktif pada dokter & tanggal yang sama.
     * Pasien dikatakan memiliki pendaftaran aktif jika status != 'BATAL' dan belum di-soft-delete.
     *
     * @param int      $patientId
     * @param int      $doctorId
     * @param string   $date (format: Y-m-d)
     * @param int|null $excludeId ID pendaftaran yang dikecualikan (misal saat update)
     * @return bool
     */
    public function hasActiveRegistration(int $patientId, int $doctorId, string $date, ?int $excludeId = null): bool
    {
        $builder = $this->builder();
        $builder->where('patient_id', $patientId)
                ->where('doctor_id', $doctorId)
                ->where('registration_date', $date)
                ->where('status !=', 'BATAL')
                ->where('deleted_at', null);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Mengambil daftar pendaftaran pasien dengan filter, pencarian, dan paginasi.
     *
     * @param array $params
     * @return array{items: array, pagination: array}
     */
    public function getPaginatedRegistrations(array $params = []): array
    {
        $search       = trim((string) ($params['search'] ?? ''));
        $date         = trim((string) ($params['date'] ?? ''));
        $polyclinicId = (int) ($params['polyclinic_id'] ?? 0);
        $doctorId     = (int) ($params['doctor_id'] ?? 0);
        $status       = trim((string) ($params['status'] ?? ''));
        $paymentType  = trim((string) ($params['payment_type'] ?? ''));
        $page         = max(1, (int) ($params['page'] ?? 1));
        $perPage      = (int) ($params['per_page'] ?? 10);

        $builder = $this->db->table($this->table . ' r')
            ->select('r.*, 
                      p.no_rm, p.nik as patient_nik, p.name as patient_name, p.gender as patient_gender, 
                      p.phone as patient_phone, p.dob as patient_dob,
                      poly.name as polyclinic_name, poly.code as polyclinic_code, poly.location as polyclinic_location,
                      d.name as doctor_name, d.specialization as doctor_specialization, d.sip as doctor_sip,
                      e.id as examination_id, e.examination_time')
            ->join('patients p', 'p.id = r.patient_id')
            ->join('polyclinics poly', 'poly.id = r.polyclinic_id')
            ->join('doctors d', 'd.id = r.doctor_id')
            ->join('examinations e', 'e.registration_id = r.id', 'left')
            ->where('r.deleted_at', null);

        if ($date !== '') {
            $builder->where('r.registration_date', $date);
        }

        if ($polyclinicId > 0) {
            $builder->where('r.polyclinic_id', $polyclinicId);
        }

        if ($doctorId > 0) {
            $builder->where('r.doctor_id', $doctorId);
        }

        if ($status !== '' && in_array($status, ['MENUNGGU', 'DIPERIKSA', 'SELESAI', 'BATAL'], true)) {
            $builder->where('r.status', $status);
        }

        if ($paymentType !== '' && in_array($paymentType, ['UMUM', 'BPJS'], true)) {
            $builder->where('r.payment_type', $paymentType);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('r.registration_number', $search)
                ->orLike('p.name', $search)
                ->orLike('p.no_rm', $search)
                ->orLike('p.nik', $search)
                ->orLike('d.name', $search)
                ->orLike('poly.name', $search)
            ->groupEnd();
        }

        // Hitung total data yang cocok
        $totalRows = $builder->countAllResults(false);
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;
        $offset     = ($page - 1) * $perPage;

        // Ambil data halaman saat ini
        $items = $builder->orderBy('r.registration_date', 'DESC')
                         ->orderBy('r.queue_number', 'ASC')
                         ->orderBy('r.id', 'DESC')
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
     * Ambil data lengkap 1 pendaftaran beserta relasi pasien, dokter, poli, dan pemeriksaan (jika ada).
     *
     * @param int $id
     * @return array|null
     */
    public function getRegistrationDetail(int $id): ?array
    {
        return $this->db->table($this->table . ' r')
            ->select('r.*, 
                      p.no_rm, p.nik as patient_nik, p.name as patient_name, p.gender as patient_gender, 
                      p.pob as patient_pob, p.dob as patient_dob, p.phone as patient_phone, 
                      p.address as patient_address, p.blood_type as patient_blood_type,
                      p.insurance_type as patient_insurance_type, p.insurance_number as patient_insurance_number,
                      poly.name as polyclinic_name, poly.code as polyclinic_code, poly.location as polyclinic_location,
                      d.name as doctor_name, d.specialization as doctor_specialization, d.sip as doctor_sip,
                      e.id as examination_id, e.examination_time, e.systolic_bp, e.diastolic_bp,
                      e.pulse, e.temperature, e.respiratory_rate, e.weight, e.height,
                      e.subjective, e.objective, e.assessment, e.plan, e.notes,
                      u.fullname as examiner_name')
            ->join('patients p', 'p.id = r.patient_id')
            ->join('polyclinics poly', 'poly.id = r.polyclinic_id')
            ->join('doctors d', 'd.id = r.doctor_id')
            ->join('examinations e', 'e.registration_id = r.id', 'left')
            ->join('users u', 'u.id = e.examiner_id', 'left')
            ->where('r.id', $id)
            ->where('r.deleted_at', null)
            ->get()
            ->getRowArray();
    }

    /**
     * Ambil ringkasan statistik pendaftaran (hari ini atau tanggal tertentu).
     *
     * @param string|null $date Format Y-m-d (default: hari ini)
     * @return array
     */
    public function getSummaryCounts(?string $date = null): array
    {
        $targetDate = $date ?? date('Y-m-d');

        $totalToday = $this->where('registration_date', $targetDate)
                           ->where('deleted_at', null)
                           ->countAllResults();

        $waiting = $this->where('registration_date', $targetDate)
                        ->where('status', 'MENUNGGU')
                        ->where('deleted_at', null)
                        ->countAllResults();

        $examining = $this->where('registration_date', $targetDate)
                          ->where('status', 'DIPERIKSA')
                          ->where('deleted_at', null)
                          ->countAllResults();

        $done = $this->where('registration_date', $targetDate)
                     ->where('status', 'SELESAI')
                     ->where('deleted_at', null)
                     ->countAllResults();

        $canceled = $this->where('registration_date', $targetDate)
                         ->where('status', 'BATAL')
                         ->where('deleted_at', null)
                         ->countAllResults();

        return [
            'target_date' => $targetDate,
            'total'       => $totalToday,
            'waiting'     => $waiting,
            'examining'   => $examining,
            'done'        => $done,
            'canceled'    => $canceled,
        ];
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ExaminationModel
 *
 * Mengelola rekam medis pemeriksaan rawat jalan serta pencatatan tanda-tanda vital (vital signs).
 */
class ExaminationModel extends Model
{
    protected $table            = 'examinations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'registration_id',
        'examiner_id',
        'examination_time',
        'systolic_bp',
        'diastolic_bp',
        'pulse',
        'temperature',
        'respiratory_rate',
        'weight',
        'height',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Cari hasil pemeriksaan berdasarkan ID Pendaftaran (1 Pendaftaran = 1 Pemeriksaan).
     *
     * @param int $registrationId
     * @return array|null
     */
    public function findByRegistrationId(int $registrationId): ?array
    {
        return $this->select('examinations.*, users.fullname as examiner_name')
                    ->join('users', 'users.id = examinations.examiner_id', 'left')
                    ->where('examinations.registration_id', $registrationId)
                    ->where('examinations.deleted_at', null)
                    ->first();
    }

    /**
     * Mengambil daftar pemeriksaan dengan filter, pencarian, dan paginasi.
     *
     * @param array $params
     * @return array{items: array, pagination: array}
     */
    public function getPaginatedExaminations(array $params = []): array
    {
        $search       = trim((string) ($params['search'] ?? ''));
        $date         = trim((string) ($params['date'] ?? ''));
        $polyclinicId = (int) ($params['polyclinic_id'] ?? 0);
        $doctorId     = (int) ($params['doctor_id'] ?? 0);
        $page         = max(1, (int) ($params['page'] ?? 1));
        $perPage      = (int) ($params['per_page'] ?? 10);

        $builder = $this->db->table($this->table . ' e')
            ->select('e.*, 
                      r.registration_number, r.registration_date, r.queue_number, r.status as registration_status, r.payment_type,
                      p.no_rm, p.nik as patient_nik, p.name as patient_name, p.gender as patient_gender, p.dob as patient_dob,
                      poly.name as polyclinic_name, poly.code as polyclinic_code,
                      d.name as doctor_name, d.specialization as doctor_specialization,
                      u.fullname as examiner_name')
            ->join('registrations r', 'r.id = e.registration_id')
            ->join('patients p', 'p.id = r.patient_id')
            ->join('polyclinics poly', 'poly.id = r.polyclinic_id')
            ->join('doctors d', 'd.id = r.doctor_id')
            ->join('users u', 'u.id = e.examiner_id', 'left')
            ->where('e.deleted_at', null);

        if ($date !== '') {
            $builder->where('r.registration_date', $date);
        }

        if ($polyclinicId > 0) {
            $builder->where('r.polyclinic_id', $polyclinicId);
        }

        if ($doctorId > 0) {
            $builder->where('r.doctor_id', $doctorId);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('r.registration_number', $search)
                ->orLike('p.name', $search)
                ->orLike('p.no_rm', $search)
                ->orLike('p.nik', $search)
                ->orLike('d.name', $search)
                ->orLike('e.assessment', $search)
            ->groupEnd();
        }

        $totalRows  = $builder->countAllResults(false);
        $totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;
        $offset     = ($page - 1) * $perPage;

        $items = $builder->orderBy('e.examination_time', 'DESC')
                         ->orderBy('e.id', 'DESC')
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
     * Ambil detail lengkap 1 data pemeriksaan rekam medis.
     *
     * @param int $id
     * @return array|null
     */
    public function getExaminationDetail(int $id): ?array
    {
        return $this->db->table($this->table . ' e')
            ->select('e.*, 
                      r.registration_number, r.registration_date, r.queue_number, r.status as registration_status, 
                      r.payment_type, r.complaint as initial_complaint,
                      p.id as patient_id, p.no_rm, p.nik as patient_nik, p.name as patient_name, 
                      p.gender as patient_gender, p.pob as patient_pob, p.dob as patient_dob, 
                      p.phone as patient_phone, p.address as patient_address, p.blood_type as patient_blood_type,
                      p.insurance_type as patient_insurance_type, p.insurance_number as patient_insurance_number,
                      poly.name as polyclinic_name, poly.code as polyclinic_code, poly.location as polyclinic_location,
                      d.name as doctor_name, d.specialization as doctor_specialization, d.sip as doctor_sip,
                      u.fullname as examiner_name, u.username as examiner_username')
            ->join('registrations r', 'r.id = e.registration_id')
            ->join('patients p', 'p.id = r.patient_id')
            ->join('polyclinics poly', 'poly.id = r.polyclinic_id')
            ->join('doctors d', 'd.id = r.doctor_id')
            ->join('users u', 'u.id = e.examiner_id', 'left')
            ->where('e.id', $id)
            ->where('e.deleted_at', null)
            ->get()
            ->getRowArray();
    }
}

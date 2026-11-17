<?php

namespace App\Controllers;

use App\Models\DoctorModel;
use App\Models\ExaminationModel;
use App\Models\PatientModel;
use App\Models\PolyclinicModel;
use App\Models\RegistrationModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * ExaminationController
 *
 * Menangani pemeriksaan pasien rawat jalan dan pencatatan tanda-tanda vital (vital signs):
 * - Tekanan darah sistolik & diastolik (mmHg)
 * - Denyut nadi (x/menit)
 * - Suhu tubuh (°C)
 * - Frekuensi pernapasan (x/menit)
 * - Berat badan (kg) & Tinggi badan (cm)
 * - Keluhan (anamnesis), pemeriksaan fisik, diagnosis (assessment), dan rencana tindakan/resep (plan)
 * - Waktu pemeriksaan & petugas pemeriksa otomatis
 */
class ExaminationController extends BaseController
{
    protected ExaminationModel $examinationModel;
    protected RegistrationModel $registrationModel;
    protected PatientModel $patientModel;
    protected PolyclinicModel $polyclinicModel;
    protected DoctorModel $doctorModel;

    private const ALLOWED_PER_PAGE = [10, 20, 30, 40, 50];

    public function __construct()
    {
        $this->examinationModel  = new ExaminationModel();
        $this->registrationModel = new RegistrationModel();
        $this->patientModel      = new PatientModel();
        $this->polyclinicModel   = new PolyclinicModel();
        $this->doctorModel       = new DoctorModel();
    }

    /**
     * Halaman utama pencatatan tanda vital & pemeriksaan rawat jalan.
     */
    public function index(): string
    {
        $today = date('Y-m-d');

        $data = [
            'title'       => 'Pemeriksaan & Tanda Vital Pasien',
            'today'       => $today,
            'polyclinics' => $this->polyclinicModel->getActivePolyclinics(),
            'doctors'     => $this->doctorModel->getActiveDoctorsWithPoly(),
            'examiner'    => [
                'id'   => session('user_id'),
                'name' => session('fullname'),
                'role' => session('role_label'),
            ],
        ];

        return view('examinations/index', $data);
    }

    /**
     * Endpoint AJAX (GET): Mengambil daftar antrean pasien yang menunggu atau sedang diperiksa hari ini.
     */
    public function queueList(): ResponseInterface
    {
        try {
            $date         = (string) ($this->request->getGet('date') ?? date('Y-m-d'));
            $polyclinicId = (int) ($this->request->getGet('polyclinic_id') ?? 0);
            $doctorId     = (int) ($this->request->getGet('doctor_id') ?? 0);

            $builder = $this->registrationModel->db->table('registrations r')
                ->select('r.id, r.registration_number, r.registration_date, r.queue_number, r.status, r.payment_type, r.complaint,
                          p.no_rm, p.nik, p.name as patient_name, p.gender as patient_gender, p.dob as patient_dob,
                          poly.name as polyclinic_name, poly.code as polyclinic_code,
                          d.name as doctor_name,
                          e.id as examination_id, e.examination_time')
                ->join('patients p', 'p.id = r.patient_id')
                ->join('polyclinics poly', 'poly.id = r.polyclinic_id')
                ->join('doctors d', 'd.id = r.doctor_id')
                ->join('examinations e', 'e.registration_id = r.id', 'left')
                ->where('r.registration_date', $date)
                ->whereIn('r.status', ['MENUNGGU', 'DIPERIKSA'])
                ->where('r.deleted_at', null);

            if ($polyclinicId > 0) {
                $builder->where('r.polyclinic_id', $polyclinicId);
            }

            if ($doctorId > 0) {
                $builder->where('r.doctor_id', $doctorId);
            }

            $items = $builder->orderBy('r.status', 'DESC') // DIPERIKSA lebih dulu dari MENUNGGU
                             ->orderBy('r.queue_number', 'ASC')
                             ->get()
                             ->getResultArray();

            return $this->jsonResponse([
                'status' => true,
                'data'   => $items,
            ]);
        } catch (Throwable $e) {
            log_message('error', '[ExaminationController::queueList] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal memuat antrean pemeriksaan.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (GET): Riwayat hasil pemeriksaan (rekam medis) dengan paginasi & filter.
     */
    public function list(): ResponseInterface
    {
        try {
            $search       = (string) ($this->request->getGet('search') ?? '');
            $date         = (string) ($this->request->getGet('date') ?? '');
            $polyclinicId = (int) ($this->request->getGet('polyclinic_id') ?? 0);
            $doctorId     = (int) ($this->request->getGet('doctor_id') ?? 0);
            $page         = max(1, (int) ($this->request->getGet('page') ?? 1));
            $perPage      = (int) ($this->request->getGet('per_page') ?? 10);

            if (! in_array($perPage, self::ALLOWED_PER_PAGE, true)) {
                $perPage = 10;
            }

            $params = [
                'search'        => $search,
                'date'          => $date,
                'polyclinic_id' => $polyclinicId,
                'doctor_id'     => $doctorId,
                'page'          => $page,
                'per_page'      => $perPage,
            ];

            $result = $this->examinationModel->getPaginatedExaminations($params);

            return $this->jsonResponse([
                'status'     => true,
                'message'    => 'Data rekam medis berhasil dimuat.',
                'data'       => $result['items'],
                'pagination' => $result['pagination'],
            ]);
        } catch (Throwable $e) {
            log_message('error', '[ExaminationController::list] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal memuat riwayat pemeriksaan.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (GET): Mengambil data pendaftaran + pemeriksaan yang ada untuk form modal vital sign.
     */
    public function getByRegistration(int $registrationId): ResponseInterface
    {
        try {
            $registration = $this->registrationModel->getRegistrationDetail($registrationId);

            if (! $registration) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Data pendaftaran tidak ditemukan.',
                ], 404);
            }

            $examination = $this->examinationModel->findByRegistrationId($registrationId);

            return $this->jsonResponse([
                'status'       => true,
                'registration' => $registration,
                'examination'  => $examination,
                'current_user' => [
                    'id'       => session('user_id'),
                    'fullname' => session('fullname'),
                ],
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal mengambil data pemeriksaan pasien.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (GET): Mengambil detail 1 pemeriksaan rekam medis lengkap.
     */
    public function show(int $id): ResponseInterface
    {
        try {
            $detail = $this->examinationModel->getExaminationDetail($id);

            if (! $detail) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Data pemeriksaan tidak ditemukan.',
                ], 404);
            }

            return $this->jsonResponse([
                'status' => true,
                'data'   => $detail,
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal memuat detail pemeriksaan.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Simpan pencatatan tanda-tanda vital & hasil pemeriksaan pasien.
     */
    public function save(): ResponseInterface
    {
        try {
            $rules = [
                'registration_id'  => 'required|is_natural_no_zero',
                'subjective'       => 'required|min_length[3]',
                'systolic_bp'      => 'permit_empty|is_natural|greater_than_equal_to[40]|less_than_equal_to[300]',
                'diastolic_bp'     => 'permit_empty|is_natural|greater_than_equal_to[30]|less_than_equal_to[200]',
                'pulse'            => 'permit_empty|is_natural|greater_than_equal_to[30]|less_than_equal_to[250]',
                'temperature'      => 'permit_empty|numeric|greater_than_equal_to[30]|less_than_equal_to[45]',
                'respiratory_rate' => 'permit_empty|is_natural|greater_than_equal_to[5]|less_than_equal_to[80]',
                'weight'           => 'permit_empty|numeric|greater_than[0]|less_than_equal_to[500]',
                'height'           => 'permit_empty|numeric|greater_than[0]|less_than_equal_to[300]',
                'final_status'     => 'required|in_list[DIPERIKSA,SELESAI]',
            ];

            $messages = [
                'registration_id' => [
                    'required' => 'Pendaftaran pasien tidak teridentifikasi.',
                ],
                'subjective' => [
                    'required'   => 'Keluhan / Anamnesis pasien wajib dicatat.',
                    'min_length' => 'Keluhan minimal 3 karakter.',
                ],
                'systolic_bp' => [
                    'is_natural'            => 'Tekanan darah sistolik harus angka positif.',
                    'greater_than_equal_to' => 'Sistolik minimal 40 mmHg.',
                    'less_than_equal_to'    => 'Sistolik maksimal 300 mmHg.',
                ],
                'diastolic_bp' => [
                    'is_natural'            => 'Tekanan darah diastolik harus angka positif.',
                    'greater_than_equal_to' => 'Diastolik minimal 300 mmHg.',
                    'less_than_equal_to'    => 'Diastolik maksimal 200 mmHg.',
                ],
                'pulse' => [
                    'is_natural'            => 'Denyut nadi harus angka bulat.',
                    'greater_than_equal_to' => 'Denyut nadi minimal 30 bpm.',
                    'less_than_equal_to'    => 'Denyut nadi maksimal 250 bpm.',
                ],
                'temperature' => [
                    'numeric'               => 'Suhu tubuh harus angka desimal atau bulat (contoh: 36.8).',
                    'greater_than_equal_to' => 'Suhu minimal 30 °C.',
                    'less_than_equal_to'    => 'Suhu maksimal 45 °C.',
                ],
                'respiratory_rate' => [
                    'is_natural'            => 'Frekuensi pernapasan harus angka bulat.',
                    'greater_than_equal_to' => 'Pernapasan minimal 5 x/menit.',
                    'less_than_equal_to'    => 'Pernapasan maksimal 80 x/menit.',
                ],
                'weight' => [
                    'numeric'               => 'Berat badan harus angka.',
                    'greater_than'          => 'Berat badan harus lebih dari 0 kg.',
                ],
                'height' => [
                    'numeric'               => 'Tinggi badan harus angka.',
                    'greater_than'          => 'Tinggi badan harus lebih dari 0 cm.',
                ],
                'final_status' => [
                    'required' => 'Pilih status tindak lanjut kunjungan (Sedang Diperiksa atau Selesai).',
                    'in_list'  => 'Status tindak lanjut hanya boleh DIPERIKSA atau SELESAI.',
                ],
            ];

            if (! $this->validate($rules, $messages)) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ], 422);
            }

            $registrationId = (int) $this->request->getPost('registration_id');
            $registration   = $this->registrationModel->find($registrationId);

            if (! $registration) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Pendaftaran tidak ditemukan.',
                ], 404);
            }

            $examinerId = (int) (session('user_id') ?? 1);
            $examTime   = trim((string) ($this->request->getPost('examination_time') ?? ''));
            if ($examTime === '') {
                $examTime = date('Y-m-d H:i:s');
            }

            $data = [
                'registration_id'  => $registrationId,
                'examiner_id'      => $examinerId,
                'examination_time' => $examTime,
                'systolic_bp'      => $this->request->getPost('systolic_bp') !== '' ? (int) $this->request->getPost('systolic_bp') : null,
                'diastolic_bp'     => $this->request->getPost('diastolic_bp') !== '' ? (int) $this->request->getPost('diastolic_bp') : null,
                'pulse'            => $this->request->getPost('pulse') !== '' ? (int) $this->request->getPost('pulse') : null,
                'temperature'      => $this->request->getPost('temperature') !== '' ? (float) $this->request->getPost('temperature') : null,
                'respiratory_rate' => $this->request->getPost('respiratory_rate') !== '' ? (int) $this->request->getPost('respiratory_rate') : null,
                'weight'           => $this->request->getPost('weight') !== '' ? (float) $this->request->getPost('weight') : null,
                'height'           => $this->request->getPost('height') !== '' ? (float) $this->request->getPost('height') : null,
                'subjective'       => trim((string) $this->request->getPost('subjective')),
                'objective'        => trim((string) $this->request->getPost('objective')),
                'assessment'       => trim((string) $this->request->getPost('assessment')),
                'plan'             => trim((string) $this->request->getPost('plan')),
                'notes'            => trim((string) $this->request->getPost('notes')),
            ];

            // Cek apakah pemeriksaan sudah pernah dicatat sebelumnya
            $existing = $this->examinationModel->where('registration_id', $registrationId)->first();

            if ($existing) {
                $this->examinationModel->update($existing['id'], $data);
                $examinationId = $existing['id'];
            } else {
                $examinationId = $this->examinationModel->insert($data);
            }

            // Update status kunjungan pada pendaftaran
            $finalStatus = trim((string) $this->request->getPost('final_status'));
            $this->registrationModel->update($registrationId, ['status' => $finalStatus]);

            $savedDetail = $this->examinationModel->getExaminationDetail($examinationId);

            return $this->jsonResponse([
                'status'  => true,
                'message' => 'Pencatatan tanda vital dan pemeriksaan pasien berhasil disimpan!',
                'data'    => $savedDetail,
            ]);
        } catch (Throwable $e) {
            log_message('error', '[ExaminationController::save] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan data pemeriksaan: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function jsonResponse(array $payload, int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setContentType('application/json')
            ->setJSON($payload);
    }
}

<?php

namespace App\Controllers;

use App\Models\DoctorModel;
use App\Models\PatientModel;
use App\Models\PolyclinicModel;
use App\Models\RegistrationModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * RegistrationController
 *
 * Menangani alur pendaftaran pasien rawat jalan oleh petugas, mencakup:
 * - Menampilkan daftar antrean & riwayat pendaftaran
 * - Pemilihan poliklinik & filter dokter yang sesuai secara real-time
 * - Validasi keabsahan data pasien & pencegahan pendaftaran ganda aktif
 * - Pengelolaan status kunjungan (MENUNGGU, DIPERIKSA, SELESAI, BATAL)
 */
class RegistrationController extends BaseController
{
    protected RegistrationModel $registrationModel;
    protected PatientModel $patientModel;
    protected PolyclinicModel $polyclinicModel;
    protected DoctorModel $doctorModel;

    private const ALLOWED_PER_PAGE = [10, 20, 30, 40, 50];

    public function __construct()
    {
        $this->registrationModel = new RegistrationModel();
        $this->patientModel      = new PatientModel();
        $this->polyclinicModel   = new PolyclinicModel();
        $this->doctorModel       = new DoctorModel();
    }

    /**
     * Halaman utama pendaftaran rawat jalan.
     */
    public function index(): string
    {
        $today = date('Y-m-d');

        $data = [
            'title'               => 'Pendaftaran Pasien Rawat Jalan',
            'today'               => $today,
            'polyclinics'         => $this->polyclinicModel->getActivePolyclinics(),
            'doctors'             => $this->doctorModel->getActiveDoctorsWithPoly(),
            'summary'             => $this->registrationModel->getSummaryCounts($today),
            'next_reg_number'     => $this->registrationModel->generateRegistrationNumber($today),
        ];

        return view('registrations/index', $data);
    }

    /**
     * Endpoint AJAX (GET): Mengambil daftar pendaftaran dengan pencarian, filter, dan paginasi.
     */
    public function list(): ResponseInterface
    {
        try {
            $search       = (string) ($this->request->getGet('search') ?? '');
            $date         = (string) ($this->request->getGet('date') ?? '');
            $polyclinicId = (int) ($this->request->getGet('polyclinic_id') ?? 0);
            $doctorId     = (int) ($this->request->getGet('doctor_id') ?? 0);
            $status       = (string) ($this->request->getGet('status') ?? '');
            $paymentType  = (string) ($this->request->getGet('payment_type') ?? '');
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
                'status'        => $status,
                'payment_type'  => $paymentType,
                'page'          => $page,
                'per_page'      => $perPage,
            ];

            $result  = $this->registrationModel->getPaginatedRegistrations($params);
            $summary = $this->registrationModel->getSummaryCounts($date !== '' ? $date : null);

            return $this->jsonResponse([
                'status'          => true,
                'message'         => 'Data pendaftaran berhasil dimuat.',
                'data'            => $result['items'],
                'pagination'      => $result['pagination'],
                'summary'         => $summary,
                'next_reg_number' => $this->registrationModel->generateRegistrationNumber($date !== '' ? $date : date('Y-m-d')),
            ]);
        } catch (Throwable $e) {
            log_message('error', '[RegistrationController::list] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat memuat data pendaftaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (GET): Mengambil daftar dokter aktif yang terikat pada poliklinik tertentu.
     * Digunakan untuk dependency dropdown poliklinik -> dokter.
     */
    public function getDoctorsByPoly(int $polyclinicId): ResponseInterface
    {
        try {
            $doctors = $this->doctorModel->getDoctorsByPolyclinic($polyclinicId);

            return $this->jsonResponse([
                'status' => true,
                'data'   => $doctors,
            ]);
        } catch (Throwable $e) {
            log_message('error', '[RegistrationController::getDoctorsByPoly] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal memuat daftar dokter.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (GET): Pencarian pasien terdaftar (live search untuk pendaftaran).
     */
    public function searchPatients(): ResponseInterface
    {
        try {
            $query = trim((string) ($this->request->getGet('q') ?? ''));

            $builder = $this->patientModel->builder();
            $builder->select('id, no_rm, nik, name, gender, dob, phone, insurance_type, insurance_number')
                    ->where('deleted_at', null);

            if ($query !== '') {
                $builder->groupStart()
                        ->like('name', $query)
                        ->orLike('no_rm', $query)
                        ->orLike('nik', $query)
                        ->orLike('phone', $query)
                        ->groupEnd();
            }

            $patients = $builder->orderBy('name', 'ASC')
                                ->limit(20)
                                ->get()
                                ->getResultArray();

            return $this->jsonResponse([
                'status' => true,
                'data'   => $patients,
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal mencari pasien.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (GET): Detail 1 pendaftaran pasien.
     */
    public function show(int $id): ResponseInterface
    {
        try {
            $registration = $this->registrationModel->getRegistrationDetail($id);

            if (! $registration) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Data pendaftaran tidak ditemukan.',
                ], 404);
            }

            return $this->jsonResponse([
                'status' => true,
                'data'   => $registration,
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat memuat detail pendaftaran.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Tambah Pendaftaran Pasien Rawat Jalan Baru.
     */
    public function store(): ResponseInterface
    {
        try {
            // 1. Validasi input awal
            $rules = [
                'patient_id'        => 'required|is_natural_no_zero',
                'polyclinic_id'     => 'required|is_natural_no_zero',
                'doctor_id'         => 'required|is_natural_no_zero',
                'registration_date' => 'required|valid_date[Y-m-d]',
                'payment_type'      => 'required|in_list[UMUM,BPJS]',
                'complaint'         => 'required|min_length[3]|max_length[1000]',
            ];

            $messages = [
                'patient_id' => [
                    'required'            => 'Pasien wajib dipilih dari data pasien terdaftar.',
                    'is_natural_no_zero'  => 'Pilihan pasien tidak valid.',
                ],
                'polyclinic_id' => [
                    'required'            => 'Poliklinik tujuan wajib dipilih.',
                    'is_natural_no_zero'  => 'Pilihan poliklinik tidak valid.',
                ],
                'doctor_id' => [
                    'required'            => 'Dokter pemeriksa wajib dipilih.',
                    'is_natural_no_zero'  => 'Pilihan dokter tidak valid.',
                ],
                'registration_date' => [
                    'required'            => 'Tanggal kunjungan wajib diisi.',
                    'valid_date'          => 'Format tanggal kunjungan harus YYYY-MM-DD.',
                ],
                'payment_type' => [
                    'required'            => 'Jenis pembayaran wajib dipilih.',
                    'in_list'             => 'Jenis pembayaran hanya boleh UMUM atau BPJS.',
                ],
                'complaint' => [
                    'required'            => 'Keluhan utama pasien wajib diisi.',
                    'min_length'          => 'Keluhan utama minimal 3 karakter.',
                    'max_length'          => 'Keluhan utama maksimal 1000 karakter.',
                ],
            ];

            if (! $this->validate($rules, $messages)) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ], 422);
            }

            $patientId        = (int) $this->request->getPost('patient_id');
            $polyclinicId     = (int) $this->request->getPost('polyclinic_id');
            $doctorId         = (int) $this->request->getPost('doctor_id');
            $registrationDate = trim((string) $this->request->getPost('registration_date'));
            $paymentType      = trim((string) $this->request->getPost('payment_type'));
            $complaint        = trim((string) $this->request->getPost('complaint'));

            // 2. Ketentuan: Pasien harus sudah terdaftar dan aktif
            $patient = $this->patientModel->find($patientId);
            if (! $patient) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => [
                        'patient_id' => 'Pasien belum terdaftar atau telah dinonaktifkan dari sistem.',
                    ],
                ], 422);
            }

            // 3. Ketentuan: Poliklinik harus aktif
            $polyclinic = $this->polyclinicModel->find($polyclinicId);
            if (! $polyclinic || $polyclinic['status'] !== 'active') {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => [
                        'polyclinic_id' => 'Poliklinik yang dipilih tidak aktif atau tidak ditemukan.',
                    ],
                ], 422);
            }

            // 4. Ketentuan: Dokter harus sudah sesuai dengan poliklinik yang dipilih
            $doctor = $this->doctorModel->find($doctorId);
            if (! $doctor || $doctor['status'] !== 'active') {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => [
                        'doctor_id' => 'Dokter yang dipilih tidak aktif atau tidak ditemukan.',
                    ],
                ], 422);
            }

            if ((int) $doctor['polyclinic_id'] !== $polyclinicId) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => [
                        'doctor_id' => 'Dokter ' . esc($doctor['name']) . ' tidak sesuai dengan poliklinik ' . esc($polyclinic['name']) . ' yang dipilih.',
                    ],
                ], 422);
            }

            // 5. Ketentuan: Pasien tidak boleh mempunyai pendaftaran aktif ganda pada dokter dan tanggal yang sama
            if ($this->registrationModel->hasActiveRegistration($patientId, $doctorId, $registrationDate)) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => [
                        'patient_id' => 'Pasien ' . esc($patient['name']) . ' (' . esc($patient['no_rm']) . ') sudah memiliki pendaftaran aktif pada ' . esc($doctor['name']) . ' untuk tanggal kunjungan ' . date('d/m/Y', strtotime($registrationDate)) . '.',
                    ],
                ], 422);
            }

            // 6. Generate nomor pendaftaran unik & nomor antrean urut
            $regNumber   = $this->registrationModel->generateRegistrationNumber($registrationDate);
            $queueNumber = $this->registrationModel->getNextQueueNumber($polyclinicId, $doctorId, $registrationDate);

            // 7. Simpan pendaftaran dengan status awal 'MENUNGGU'
            $dataToInsert = [
                'registration_number' => $regNumber,
                'registration_date'   => $registrationDate,
                'patient_id'          => $patientId,
                'polyclinic_id'       => $polyclinicId,
                'doctor_id'           => $doctorId,
                'queue_number'        => $queueNumber,
                'payment_type'        => $paymentType,
                'complaint'           => $complaint,
                'status'              => 'MENUNGGU',
            ];

            $insertId = $this->registrationModel->insert($dataToInsert);

            if (! $insertId) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Gagal menyimpan data pendaftaran ke basis data.',
                ], 500);
            }

            $newRecord = $this->registrationModel->getRegistrationDetail($insertId);

            return $this->jsonResponse([
                'status'          => true,
                'message'         => "Pendaftaran berhasil disimpan! Nomor Pendaftaran: {$regNumber}, Antrean: {$queueNumber}.",
                'data'            => $newRecord,
                'next_reg_number' => $this->registrationModel->generateRegistrationNumber($registrationDate),
            ], 201);
        } catch (Throwable $e) {
            log_message('error', '[RegistrationController::store] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat mendaftarkan pasien: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Mengubah status pendaftaran pasien (MENUNGGU, DIPERIKSA, SELESAI, BATAL).
     */
    public function updateStatus(int $id): ResponseInterface
    {
        try {
            $registration = $this->registrationModel->find($id);
            if (! $registration) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Data pendaftaran tidak ditemukan.',
                ], 404);
            }

            $newStatus = trim((string) $this->request->getPost('status'));
            $allowedStatuses = ['MENUNGGU', 'DIPERIKSA', 'SELESAI', 'BATAL'];

            if (! in_array($newStatus, $allowedStatuses, true)) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Status pendaftaran tidak valid. Pilihan: ' . implode(', ', $allowedStatuses),
                ], 422);
            }

            // Update status
            $this->registrationModel->update($id, ['status' => $newStatus]);

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Status pendaftaran {$registration['registration_number']} berhasil diperbarui menjadi {$newStatus}.",
                'data'    => [
                    'id'     => $id,
                    'status' => $newStatus,
                ],
            ]);
        } catch (Throwable $e) {
            log_message('error', '[RegistrationController::updateStatus] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat memperbarui status pendaftaran.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Batalkan pendaftaran pasien.
     */
    public function cancel(int $id): ResponseInterface
    {
        try {
            $registration = $this->registrationModel->find($id);
            if (! $registration) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Data pendaftaran tidak ditemukan.',
                ], 404);
            }

            if ($registration['status'] === 'SELESAI') {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Pendaftaran yang sudah selesai diperiksa tidak dapat dibatalkan.',
                ], 422);
            }

            $this->registrationModel->update($id, ['status' => 'BATAL']);

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Pendaftaran {$registration['registration_number']} berhasil dibatalkan.",
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat membatalkan pendaftaran.',
            ], 500);
        }
    }

    /**
     * Helper standar respon JSON.
     */
    private function jsonResponse(array $payload, int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setContentType('application/json')
            ->setJSON($payload);
    }
}

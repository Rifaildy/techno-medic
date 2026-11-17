<?php

namespace App\Controllers;

use App\Models\PatientModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * PatientController
 *
 * Menangani halaman pengelolaan data pasien rawat jalan serta seluruh
 * endpoint AJAX (list dengan pencarian & paginasi, detail, tambah, ubah,
 * nonaktifkan/hapus lunak, dan aktifkan kembali).
 */
class PatientController extends BaseController
{
    protected PatientModel $patientModel;

    /**
     * Pilihan jumlah baris per halaman yang diizinkan.
     */
    private const ALLOWED_PER_PAGE = [10, 20, 30, 40, 50];

    public function __construct()
    {
        $this->patientModel = new PatientModel();
    }

    /**
     * Menampilkan halaman utama pengelolaan data pasien.
     */
    public function index(): string
    {
        $data = [
            'title'      => 'Pengelolaan Data Pasien',
            'next_no_rm' => $this->patientModel->generateNoRM(),
            'summary'    => $this->patientModel->getSummaryCounts(),
        ];

        return view('patients/index', $data);
    }

    /**
     * Endpoint AJAX (GET): Mengambil daftar pasien dengan pencarian & paginasi.
     */
    public function list(): ResponseInterface
    {
        try {
            $search    = (string) ($this->request->getGet('search') ?? '');
            $status    = (string) ($this->request->getGet('status') ?? 'active');
            $insurance = (string) ($this->request->getGet('insurance') ?? '');
            $page      = max(1, (int) ($this->request->getGet('page') ?? 1));
            $perPage   = (int) ($this->request->getGet('per_page') ?? 10);

            if (! in_array($perPage, self::ALLOWED_PER_PAGE, true)) {
                $perPage = 10;
            }

            if (! in_array($status, ['active', 'inactive', 'all'], true)) {
                $status = 'active';
            }

            $result  = $this->patientModel->getPaginatedPatients($search, $perPage, $page, $status, $insurance);
            $summary = $this->patientModel->getSummaryCounts();

            return $this->jsonResponse([
                'status'     => true,
                'message'    => 'Data pasien berhasil dimuat.',
                'data'       => $result['items'],
                'pagination' => $result['pagination'],
                'summary'    => $summary,
                'next_no_rm' => $this->patientModel->generateNoRM(),
            ]);
        } catch (Throwable $e) {
            log_message('error', '[PatientController::list] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat memuat data pasien.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (GET): Mengambil detail satu pasien berdasarkan ID.
     */
    public function show(int $id): ResponseInterface
    {
        try {
            $patient = $this->patientModel->withDeleted()->find($id);

            if (! $patient) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Data pasien tidak ditemukan.',
                ], 404);
            }

            return $this->jsonResponse([
                'status' => true,
                'data'   => $patient,
            ]);
        } catch (Throwable $e) {
            log_message('error', '[PatientController::show] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal mengambil detail pasien.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Menyimpan data pasien baru.
     */
    public function store(): ResponseInterface
    {
        try {
            $payload = $this->sanitizePayload($this->request->getPost());
            $errors  = $this->validatePatient($payload);

            if (! empty($errors)) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Periksa kembali isian formulir Anda.',
                    'errors'  => $errors,
                ], 422);
            }

            // Generate No. RM otomatis secara aman di sisi server
            $payload['no_rm'] = $this->patientModel->generateNoRM();

            $insertId = $this->patientModel->insert($payload, true);

            if (! $insertId) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Gagal menyimpan data pasien ke database.',
                    'errors'  => $this->patientModel->errors(),
                ], 400);
            }

            $createdPatient = $this->patientModel->find($insertId);

            return $this->jsonResponse([
                'status'     => true,
                'message'    => "Pasien {$createdPatient['name']} ({$createdPatient['no_rm']}) berhasil ditambahkan.",
                'data'       => $createdPatient,
                'next_no_rm' => $this->patientModel->generateNoRM(),
            ], 201);
        } catch (Throwable $e) {
            log_message('error', '[PatientController::store] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan data pasien.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Memperbarui data pasien yang sudah ada.
     */
    public function update(int $id): ResponseInterface
    {
        try {
            $existing = $this->patientModel->withDeleted()->find($id);

            if (! $existing) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Data pasien tidak ditemukan.',
                ], 404);
            }

            $payload = $this->sanitizePayload($this->request->getPost());
            $errors  = $this->validatePatient($payload, $id);

            if (! empty($errors)) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Periksa kembali isian formulir Anda.',
                    'errors'  => $errors,
                ], 422);
            }

            // No. RM tidak diubah saat edit agar integritas rekam medis terjaga
            unset($payload['no_rm']);

            $updated = $this->patientModel->update($id, $payload);

            if (! $updated) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Gagal memperbarui data pasien.',
                    'errors'  => $this->patientModel->errors(),
                ], 400);
            }

            $updatedPatient = $this->patientModel->withDeleted()->find($id);

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Data pasien {$updatedPatient['name']} berhasil diperbarui.",
                'data'    => $updatedPatient,
            ]);
        } catch (Throwable $e) {
            log_message('error', '[PatientController::update] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat memperbarui data pasien.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Menonaktifkan / menghapus lunak data pasien (soft delete).
     * Riwayat rekam medis dan kunjungan tetap aman di database.
     */
    public function delete(int $id): ResponseInterface
    {
        try {
            $patient = $this->patientModel->find($id);

            if (! $patient) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Data pasien tidak ditemukan atau sudah dinonaktifkan.',
                ], 404);
            }

            $this->patientModel->delete($id);

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Pasien {$patient['name']} ({$patient['no_rm']}) berhasil dinonaktifkan.",
            ]);
        } catch (Throwable $e) {
            log_message('error', '[PatientController::delete] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal menonaktifkan data pasien.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Mengaktifkan kembali pasien yang sebelumnya dinonaktifkan.
     */
    public function restore(int $id): ResponseInterface
    {
        try {
            $patient = $this->patientModel->onlyDeleted()->find($id);

            if (! $patient) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Data pasien nonaktif tidak ditemukan.',
                ], 404);
            }

            // Pulihkan kolom deleted_at menjadi NULL
            $this->patientModel->builder()
                ->where('id', $id)
                ->update([
                    'deleted_at' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Pasien {$patient['name']} ({$patient['no_rm']}) berhasil diaktifkan kembali.",
            ]);
        } catch (Throwable $e) {
            log_message('error', '[PatientController::restore] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal mengaktifkan kembali data pasien.',
            ], 500);
        }
    }

    /**
     * Membersihkan dan menormalisasi input dari request POST.
     */
    private function sanitizePayload(array $raw): array
    {
        $insuranceType   = strtoupper(trim((string) ($raw['insurance_type'] ?? 'UMUM')));
        $insuranceNumber = trim((string) ($raw['insurance_number'] ?? ''));

        if ($insuranceType !== 'BPJS') {
            $insuranceType   = 'UMUM';
            $insuranceNumber = null;
        }

        $bloodType = strtoupper(trim((string) ($raw['blood_type'] ?? 'UNKNOWN')));
        if (! in_array($bloodType, ['A', 'B', 'AB', 'O', 'UNKNOWN'], true)) {
            $bloodType = 'UNKNOWN';
        }

        return [
            'nik'              => preg_replace('/\D/', '', (string) ($raw['nik'] ?? '')),
            'name'             => trim((string) ($raw['name'] ?? '')),
            'gender'           => strtoupper(trim((string) ($raw['gender'] ?? ''))),
            'pob'              => trim((string) ($raw['pob'] ?? '')) ?: null,
            'dob'              => trim((string) ($raw['dob'] ?? '')),
            'phone'            => trim((string) ($raw['phone'] ?? '')) ?: null,
            'address'          => trim((string) ($raw['address'] ?? '')) ?: null,
            'blood_type'       => $bloodType,
            'insurance_type'   => $insuranceType,
            'insurance_number' => $insuranceNumber ?: null,
        ];
    }

    /**
     * Validasi aturan bisnis data pasien sesuai skema tabel `patients`.
     *
     * @param array    $data      Data yang sudah dibersihkan
     * @param int|null $ignoreId  ID pasien yang diabaikan pada pengecekan NIK unik (saat edit)
     *
     * @return array<string, string> Daftar error per field
     */
    private function validatePatient(array $data, ?int $ignoreId = null): array
    {
        $validation = \Config\Services::validation();

        $nikUniqueRule = $ignoreId !== null
            ? "is_unique[patients.nik,id,{$ignoreId}]"
            : 'is_unique[patients.nik]';

        $rules = [
            'nik' => [
                'label'  => 'NIK',
                'rules'  => "required|numeric|exact_length[16]|{$nikUniqueRule}",
                'errors' => [
                    'required'     => 'NIK wajib diisi.',
                    'numeric'      => 'NIK harus berupa angka.',
                    'exact_length' => 'NIK harus terdiri dari tepat 16 digit.',
                    'is_unique'    => 'NIK ini sudah terdaftar pada pasien lain.',
                ],
            ],
            'name' => [
                'label'  => 'Nama Lengkap',
                'rules'  => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required'   => 'Nama lengkap pasien wajib diisi.',
                    'min_length' => 'Nama lengkap minimal 3 karakter.',
                    'max_length' => 'Nama lengkap maksimal 100 karakter.',
                ],
            ],
            'gender' => [
                'label'  => 'Jenis Kelamin',
                'rules'  => 'required|in_list[L,P]',
                'errors' => [
                    'required' => 'Jenis kelamin wajib dipilih.',
                    'in_list'  => 'Jenis kelamin harus Laki-laki (L) atau Perempuan (P).',
                ],
            ],
            'pob' => [
                'label'  => 'Tempat Lahir',
                'rules'  => 'permit_empty|max_length[50]',
                'errors' => [
                    'max_length' => 'Tempat lahir maksimal 50 karakter.',
                ],
            ],
            'dob' => [
                'label'  => 'Tanggal Lahir',
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required'   => 'Tanggal lahir wajib diisi.',
                    'valid_date' => 'Format tanggal lahir tidak valid (YYYY-MM-DD).',
                ],
            ],
            'phone' => [
                'label'  => 'Nomor Telepon',
                'rules'  => 'permit_empty|max_length[20]',
                'errors' => [
                    'max_length' => 'Nomor telepon maksimal 20 karakter.',
                ],
            ],
            'blood_type' => [
                'label'  => 'Golongan Darah',
                'rules'  => 'required|in_list[A,B,AB,O,UNKNOWN]',
                'errors' => [
                    'in_list' => 'Pilihan golongan darah tidak valid.',
                ],
            ],
            'insurance_type' => [
                'label'  => 'Jenis Penjamin',
                'rules'  => 'required|in_list[UMUM,BPJS]',
                'errors' => [
                    'in_list' => 'Jenis penjamin harus UMUM atau BPJS.',
                ],
            ],
        ];

        if (($data['insurance_type'] ?? 'UMUM') === 'BPJS') {
            $rules['insurance_number'] = [
                'label'  => 'Nomor Kartu BPJS',
                'rules'  => 'required|min_length[8]|max_length[50]',
                'errors' => [
                    'required'   => 'Nomor kartu BPJS wajib diisi untuk pasien BPJS.',
                    'min_length' => 'Nomor kartu BPJS minimal 8 karakter.',
                    'max_length' => 'Nomor kartu BPJS maksimal 50 karakter.',
                ],
            ];
        }

        $validation->setRules($rules);
        $validation->run($data);
        $errors = $validation->getErrors();

        // Validasi tambahan: Tanggal lahir tidak boleh melebihi tanggal hari ini
        if (! isset($errors['dob']) && ! empty($data['dob'])) {
            if (strtotime($data['dob']) > strtotime(date('Y-m-d'))) {
                $errors['dob'] = 'Tanggal lahir tidak boleh melebihi hari ini.';
            }
        }

        return $errors;
    }

    /**
     * Menghapus data pasien secara permanen (Hard Delete).
     * Validasi ketat: Hanya diizinkan jika pasien belum memiliki riwayat
     * pemeriksaan atau kunjungan sama sekali.
     */
    public function permanentDelete(int $id): ResponseInterface
    {
        try {
            // Cek pasien termasuk yang berstatus nonaktif (soft-deleted)
            $patient = $this->patientModel->withDeleted()->find($id);

            if (! $patient) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Data pasien tidak ditemukan.',
                ], 404);
            }

            // Validasi: Pastikan pasien belum menjalani pemeriksaan sama sekali
            if ($this->patientModel->hasExaminationHistory($id)) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => "Pasien {$patient['name']} ({$patient['no_rm']}) tidak dapat dihapus karena sudah memiliki riwayat pemeriksaan atau kunjungan. Gunakan fitur Nonaktifkan.",
                ], 422);
            }

            $deleted = $this->patientModel->permanentDeletePatient($id);

            if (! $deleted) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Gagal menghapus data pasien secara permanen.',
                ], 500);
            }

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Data pasien {$patient['name']} ({$patient['no_rm']}) berhasil dihapus secara permanen.",
            ]);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('warning', '[PatientController::permanentDelete] FK Constraint: ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Data pasien tidak dapat dihapus karena masih terhubung dengan transaksi/pemeriksaan lain di sistem.',
            ], 422);
        } catch (Throwable $e) {
            log_message('error', '[PatientController::permanentDelete] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat menghapus data pasien.',
            ], 500);
        }
    }

    /**
     * Helper respons JSON standar dengan pembaruan token CSRF otomatis.
     */
    private function jsonResponse(array $payload, int $statusCode = 200): ResponseInterface
    {
        $payload['csrf'] = [
            'name' => csrf_token(),
            'hash' => csrf_hash(),
        ];

        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($payload);
    }
}
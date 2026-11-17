<?php

namespace App\Controllers;

use App\Models\DoctorModel;
use App\Models\PolyclinicModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * DoctorController
 *
 * Mengelola data master dokter rumah sakit, mencakup:
 * - Menampilkan daftar dokter dengan filter poliklinik, gender, & status
 * - Penambahan dokter baru dengan verifikasi NIK (16 digit) & SIP unik
 * - Penautan akun user login dokter
 * - Perubahan informasi kepegawaian & spesialisasi dokter
 * - Pengaktifan / penonaktifan & penghapusan dokter
 */
class DoctorController extends BaseController
{
    protected DoctorModel $doctorModel;
    protected PolyclinicModel $polyclinicModel;
    protected UserModel $userModel;

    private const ALLOWED_PER_PAGE = [10, 20, 30, 40, 50];

    public function __construct()
    {
        $this->doctorModel     = new DoctorModel();
        $this->polyclinicModel = new PolyclinicModel();
        $this->userModel       = new UserModel();
    }

    /**
     * Halaman utama master dokter.
     */
    public function index(): string
    {
        $data = [
            'title'       => 'Manajemen Dokter Spesialis & Umum',
            'polyclinics' => $this->polyclinicModel->getActivePolyclinics(),
            'users'       => $this->userModel->getAvailableDoctorAccounts(),
            'summary'     => $this->doctorModel->getSummaryCounts(),
        ];

        return view('doctors/index', $data);
    }

    /**
     * Endpoint AJAX (GET): Daftar dokter dengan filter, pencarian, dan paginasi.
     */
    public function list(): ResponseInterface
    {
        try {
            $search       = (string) ($this->request->getGet('search') ?? '');
            $polyclinicId = (int) ($this->request->getGet('polyclinic_id') ?? 0);
            $gender       = (string) ($this->request->getGet('gender') ?? '');
            $status       = (string) ($this->request->getGet('status') ?? '');
            $page         = max(1, (int) ($this->request->getGet('page') ?? 1));
            $perPage      = (int) ($this->request->getGet('per_page') ?? 10);

            if (! in_array($perPage, self::ALLOWED_PER_PAGE, true)) {
                $perPage = 10;
            }

            $params = [
                'search'        => $search,
                'polyclinic_id' => $polyclinicId,
                'gender'        => $gender,
                'status'        => $status,
                'page'          => $page,
                'per_page'      => $perPage,
            ];

            $result  = $this->doctorModel->getPaginatedDoctors($params);
            $summary = $this->doctorModel->getSummaryCounts();

            return $this->jsonResponse([
                'status'     => true,
                'data'       => $result['items'],
                'pagination' => $result['pagination'],
                'summary'    => $summary,
            ]);
        } catch (Throwable $e) {
            log_message('error', '[DoctorController::list] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal memuat data dokter: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (GET): Detail 1 dokter.
     */
    public function show(int $id): ResponseInterface
    {
        try {
            $doctor = $this->doctorModel->getDoctorDetail($id);

            if (! $doctor) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Data dokter tidak ditemukan.',
                ], 404);
            }

            return $this->jsonResponse([
                'status' => true,
                'data'   => $doctor,
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat memuat detail dokter.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (GET): Akun pengguna dokter yang belum ditautkan.
     */
    public function getAvailableUsers(?int $currentDoctorId = null): ResponseInterface
    {
        try {
            $currentUserId = null;
            if ($currentDoctorId !== null) {
                $doc = $this->doctorModel->find($currentDoctorId);
                if ($doc && ! empty($doc['user_id'])) {
                    $currentUserId = (int) $doc['user_id'];
                }
            }

            $accounts = $this->userModel->getAvailableDoctorAccounts($currentUserId);

            return $this->jsonResponse([
                'status' => true,
                'data'   => $accounts,
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal mengambil akun pengguna dokter.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Tambah dokter baru.
     */
    public function store(): ResponseInterface
    {
        try {
            $rules = [
                'nik'            => 'required|exact_length[16]|numeric|is_unique[doctors.nik]',
                'sip'            => 'required|min_length[3]|max_length[50]|is_unique[doctors.sip]',
                'name'           => 'required|min_length[3]|max_length[100]',
                'polyclinic_id'  => 'required|is_natural_no_zero',
                'specialization' => 'permit_empty|max_length[100]',
                'phone'          => 'permit_empty|min_length[8]|max_length[20]',
                'email'          => 'permit_empty|valid_email|max_length[100]',
                'gender'         => 'required|in_list[L,P]',
                'user_id'        => 'permit_empty|is_natural_no_zero',
                'status'         => 'required|in_list[active,inactive]',
            ];

            $messages = [
                'nik' => [
                    'required'     => 'NIK dokter wajib diisi.',
                    'exact_length' => 'NIK harus tepat 16 digit angka.',
                    'numeric'      => 'NIK hanya boleh berisi angka.',
                    'is_unique'    => 'NIK ini sudah terdaftar di sistem.',
                ],
                'sip' => [
                    'required'   => 'Surat Izin Praktik (SIP) wajib diisi.',
                    'is_unique'  => 'Nomor SIP ini sudah terdaftar.',
                ],
                'name' => [
                    'required'   => 'Nama lengkap dokter wajib diisi.',
                    'min_length' => 'Nama dokter minimal 3 karakter.',
                ],
                'polyclinic_id' => [
                    'required' => 'Poliklinik penugasan dokter wajib dipilih.',
                ],
                'gender' => [
                    'required' => 'Jenis kelamin wajib dipilih.',
                ],
                'status' => [
                    'required' => 'Status operasional dokter wajib dipilih.',
                ],
            ];

            if (! $this->validate($rules, $messages)) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ], 422);
            }

            $userId = $this->request->getPost('user_id') !== '' ? (int) $this->request->getPost('user_id') : null;

            $data = [
                'nik'            => trim((string) $this->request->getPost('nik')),
                'sip'            => trim((string) $this->request->getPost('sip')),
                'name'           => trim((string) $this->request->getPost('name')),
                'polyclinic_id'  => (int) $this->request->getPost('polyclinic_id'),
                'specialization' => trim((string) ($this->request->getPost('specialization') ?? '')),
                'phone'          => trim((string) ($this->request->getPost('phone') ?? '')),
                'email'          => trim((string) ($this->request->getPost('email') ?? '')),
                'gender'         => trim((string) $this->request->getPost('gender')),
                'user_id'        => $userId,
                'status'         => trim((string) $this->request->getPost('status')),
            ];

            $insertId = $this->doctorModel->insert($data);

            if (! $insertId) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Gagal menyimpan data dokter ke basis data.',
                ], 500);
            }

            $newDoctor = $this->doctorModel->getDoctorDetail($insertId);

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Dokter {$data['name']} berhasil ditambahkan ke master dokter!",
                'data'    => $newDoctor,
            ], 201);
        } catch (Throwable $e) {
            log_message('error', '[DoctorController::store] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat membuat dokter: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Perbarui data dokter.
     */
    public function update(int $id): ResponseInterface
    {
        try {
            $doctor = $this->doctorModel->find($id);
            if (! $doctor) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Dokter tidak ditemukan.',
                ], 404);
            }

            $rules = [
                'nik'            => "required|exact_length[16]|numeric|is_unique[doctors.nik,id,{$id}]",
                'sip'            => "required|min_length[3]|max_length[50]|is_unique[doctors.sip,id,{$id}]",
                'name'           => 'required|min_length[3]|max_length[100]',
                'polyclinic_id'  => 'required|is_natural_no_zero',
                'specialization' => 'permit_empty|max_length[100]',
                'phone'          => 'permit_empty|min_length[8]|max_length[20]',
                'email'          => 'permit_empty|valid_email|max_length[100]',
                'gender'         => 'required|in_list[L,P]',
                'user_id'        => 'permit_empty|is_natural_no_zero',
                'status'         => 'required|in_list[active,inactive]',
            ];

            $messages = [
                'nik' => [
                    'required'     => 'NIK dokter wajib diisi.',
                    'exact_length' => 'NIK harus tepat 16 digit angka.',
                    'numeric'      => 'NIK hanya boleh berisi angka.',
                    'is_unique'    => 'NIK ini sudah terdaftar pada dokter lain.',
                ],
                'sip' => [
                    'required'  => 'Surat Izin Praktik (SIP) wajib diisi.',
                    'is_unique' => 'Nomor SIP ini sudah terdaftar pada dokter lain.',
                ],
                'name' => [
                    'required' => 'Nama lengkap dokter wajib diisi.',
                ],
            ];

            if (! $this->validate($rules, $messages)) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ], 422);
            }

            $userId = $this->request->getPost('user_id') !== '' ? (int) $this->request->getPost('user_id') : null;

            $data = [
                'nik'            => trim((string) $this->request->getPost('nik')),
                'sip'            => trim((string) $this->request->getPost('sip')),
                'name'           => trim((string) $this->request->getPost('name')),
                'polyclinic_id'  => (int) $this->request->getPost('polyclinic_id'),
                'specialization' => trim((string) ($this->request->getPost('specialization') ?? '')),
                'phone'          => trim((string) ($this->request->getPost('phone') ?? '')),
                'email'          => trim((string) ($this->request->getPost('email') ?? '')),
                'gender'         => trim((string) $this->request->getPost('gender')),
                'user_id'        => $userId,
                'status'         => trim((string) $this->request->getPost('status')),
            ];

            $this->doctorModel->update($id, $data);

            $updatedDoctor = $this->doctorModel->getDoctorDetail($id);

            return $this->jsonResponse([
                'status'  => true,
                'message' => 'Data dokter berhasil diperbarui!',
                'data'    => $updatedDoctor,
            ]);
        } catch (Throwable $e) {
            log_message('error', '[DoctorController::update] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat memperbarui data dokter: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Beralih status aktif / nonaktif dokter.
     */
    public function toggleStatus(int $id): ResponseInterface
    {
        try {
            $doctor = $this->doctorModel->find($id);
            if (! $doctor) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Dokter tidak ditemukan.',
                ], 404);
            }

            $newStatus = $doctor['status'] === 'active' ? 'inactive' : 'active';
            $this->doctorModel->update($id, ['status' => $newStatus]);

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Status {$doctor['name']} sekarang " . ($newStatus === 'active' ? 'aktif bertugas' : 'nonaktif') . '.',
                'data'    => [
                    'id'     => $id,
                    'status' => $newStatus,
                ],
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal mengubah status dokter.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Hapus dokter (soft delete).
     */
    public function delete(int $id): ResponseInterface
    {
        try {
            $doctor = $this->doctorModel->find($id);
            if (! $doctor) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Dokter tidak ditemukan.',
                ], 404);
            }

            // Periksa apakah dokter masih memiliki pendaftaran aktif yang sedang menunggu atau diperiksa
            $activeVisits = $this->doctorModel->db->table('registrations')
                ->where('doctor_id', $id)
                ->whereIn('status', ['MENUNGGU', 'DIPERIKSA'])
                ->where('deleted_at', null)
                ->countAllResults();

            if ($activeVisits > 0) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => "Dokter {$doctor['name']} masih memiliki {$activeVisits} pasien dalam antrean aktif. Selesaikan atau batalkan kunjungan terlebih dahulu.",
                ], 422);
            }

            $this->doctorModel->delete($id);

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Dokter {$doctor['name']} berhasil dihapus dari sistem.",
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal menghapus dokter.',
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

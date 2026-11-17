<?php

namespace App\Controllers;

use App\Models\PolyclinicModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * PolyclinicController
 *
 * Mengelola master data poliklinik rawat jalan, mencakup:
 * - Menampilkan daftar poliklinik beserta statistik dokter & kunjungan
 * - Penambahan poliklinik baru dengan verifikasi kode unik
 * - Perubahan informasi dan lokasi poliklinik
 * - Pengaktifan / penonaktifan & penghapusan data poliklinik
 */
class PolyclinicController extends BaseController
{
    protected PolyclinicModel $polyclinicModel;

    private const ALLOWED_PER_PAGE = [10, 20, 30, 40, 50];

    public function __construct()
    {
        $this->polyclinicModel = new PolyclinicModel();
    }

    /**
     * Halaman utama master poliklinik.
     */
    public function index(): string
    {
        $data = [
            'title'   => 'Manajemen Poliklinik Rawat Jalan',
            'summary' => $this->polyclinicModel->getSummaryCounts(),
        ];

        return view('polyclinics/index', $data);
    }

    /**
     * Endpoint AJAX (GET): Daftar poliklinik dengan pencarian, filter, dan paginasi.
     */
    public function list(): ResponseInterface
    {
        try {
            $search  = (string) ($this->request->getGet('search') ?? '');
            $status  = (string) ($this->request->getGet('status') ?? '');
            $page    = max(1, (int) ($this->request->getGet('page') ?? 1));
            $perPage = (int) ($this->request->getGet('per_page') ?? 10);

            if (! in_array($perPage, self::ALLOWED_PER_PAGE, true)) {
                $perPage = 10;
            }

            $params = [
                'search'   => $search,
                'status'   => $status,
                'page'     => $page,
                'per_page' => $perPage,
            ];

            $result  = $this->polyclinicModel->getPaginatedPolyclinics($params);
            $summary = $this->polyclinicModel->getSummaryCounts();

            return $this->jsonResponse([
                'status'     => true,
                'data'       => $result['items'],
                'pagination' => $result['pagination'],
                'summary'    => $summary,
            ]);
        } catch (Throwable $e) {
            log_message('error', '[PolyclinicController::list] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal memuat data poliklinik: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (GET): Detail 1 poliklinik beserta dokter yang terikat.
     */
    public function show(int $id): ResponseInterface
    {
        try {
            $poly = $this->polyclinicModel->getPolyclinicDetail($id);

            if (! $poly) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Data poliklinik tidak ditemukan.',
                ], 404);
            }

            return $this->jsonResponse([
                'status' => true,
                'data'   => $poly,
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat memuat detail poliklinik.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Tambah poliklinik baru.
     */
    public function store(): ResponseInterface
    {
        try {
            $rules = [
                'code'     => 'required|min_length[3]|max_length[20]|alpha_dash|is_unique[polyclinics.code]',
                'name'     => 'required|min_length[3]|max_length[100]',
                'location' => 'permit_empty|max_length[100]',
                'status'   => 'required|in_list[active,inactive]',
            ];

            $messages = [
                'code' => [
                    'required'   => 'Kode poliklinik wajib diisi.',
                    'min_length' => 'Kode poliklinik minimal 3 karakter.',
                    'alpha_dash' => 'Kode poliklinik hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).',
                    'is_unique'  => 'Kode poliklinik ini sudah digunakan.',
                ],
                'name' => [
                    'required'   => 'Nama poliklinik wajib diisi.',
                    'min_length' => 'Nama poliklinik minimal 3 karakter.',
                ],
                'status' => [
                    'required' => 'Status operasional wajib dipilih.',
                ],
            ];

            if (! $this->validate($rules, $messages)) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ], 422);
            }

            $data = [
                'code'     => strtoupper(trim((string) $this->request->getPost('code'))),
                'name'     => trim((string) $this->request->getPost('name')),
                'location' => trim((string) ($this->request->getPost('location') ?? '')),
                'status'   => trim((string) $this->request->getPost('status')),
            ];

            $insertId = $this->polyclinicModel->insert($data);

            if (! $insertId) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Gagal menyimpan data poliklinik.',
                ], 500);
            }

            $newPoly = $this->polyclinicModel->getPolyclinicDetail($insertId);

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Poliklinik {$data['name']} ({$data['code']}) berhasil ditambahkan!",
                'data'    => $newPoly,
            ], 201);
        } catch (Throwable $e) {
            log_message('error', '[PolyclinicController::store] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat membuat poliklinik: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Perbarui data poliklinik.
     */
    public function update(int $id): ResponseInterface
    {
        try {
            $poly = $this->polyclinicModel->find($id);
            if (! $poly) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Poliklinik tidak ditemukan.',
                ], 404);
            }

            $rules = [
                'code'     => "required|min_length[3]|max_length[20]|alpha_dash|is_unique[polyclinics.code,id,{$id}]",
                'name'     => 'required|min_length[3]|max_length[100]',
                'location' => 'permit_empty|max_length[100]',
                'status'   => 'required|in_list[active,inactive]',
            ];

            $messages = [
                'code' => [
                    'required'  => 'Kode poliklinik wajib diisi.',
                    'is_unique' => 'Kode poliklinik ini sudah digunakan oleh poli lain.',
                ],
                'name' => [
                    'required' => 'Nama poliklinik wajib diisi.',
                ],
            ];

            if (! $this->validate($rules, $messages)) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ], 422);
            }

            $data = [
                'code'     => strtoupper(trim((string) $this->request->getPost('code'))),
                'name'     => trim((string) $this->request->getPost('name')),
                'location' => trim((string) ($this->request->getPost('location') ?? '')),
                'status'   => trim((string) $this->request->getPost('status')),
            ];

            $this->polyclinicModel->update($id, $data);

            $updatedPoly = $this->polyclinicModel->getPolyclinicDetail($id);

            return $this->jsonResponse([
                'status'  => true,
                'message' => 'Data poliklinik berhasil diperbarui!',
                'data'    => $updatedPoly,
            ]);
        } catch (Throwable $e) {
            log_message('error', '[PolyclinicController::update] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat memperbarui poliklinik: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Beralih status aktif / nonaktif poliklinik.
     */
    public function toggleStatus(int $id): ResponseInterface
    {
        try {
            $poly = $this->polyclinicModel->find($id);
            if (! $poly) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Poliklinik tidak ditemukan.',
                ], 404);
            }

            $newStatus = $poly['status'] === 'active' ? 'inactive' : 'active';
            $this->polyclinicModel->update($id, ['status' => $newStatus]);

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Poliklinik {$poly['name']} sekarang " . ($newStatus === 'active' ? 'aktif' : 'nonaktif') . '.',
                'data'    => [
                    'id'     => $id,
                    'status' => $newStatus,
                ],
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal mengubah status poliklinik.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Hapus poliklinik (soft delete).
     */
    public function delete(int $id): ResponseInterface
    {
        try {
            $poly = $this->polyclinicModel->find($id);
            if (! $poly) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Poliklinik tidak ditemukan.',
                ], 404);
            }

            // Cek apakah ada dokter yang terikat
            $activeDoctors = $this->polyclinicModel->db->table('doctors')
                ->where('polyclinic_id', $id)
                ->where('deleted_at', null)
                ->countAllResults();

            if ($activeDoctors > 0) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => "Poliklinik {$poly['name']} masih memiliki {$activeDoctors} dokter terikat. Pindahkan dokter ke poli lain terlebih dahulu.",
                ], 422);
            }

            $this->polyclinicModel->delete($id);

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Poliklinik {$poly['name']} ({$poly['code']}) berhasil dihapus.",
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal menghapus poliklinik.',
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

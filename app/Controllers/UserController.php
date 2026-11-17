<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * UserController
 *
 * Mengelola data akun pengguna sistem rawat jalan, mencakup:
 * - Menampilkan daftar user dengan pencarian, filter peran, & status
 * - Pendaftaran user baru dengan verifikasi keunikan username & email
 * - Perubahan profil user & reset password
 * - Pengaktifan / penonaktifan akun dengan proteksi akun aktif diri sendiri
 */
class UserController extends BaseController
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;

    private const ALLOWED_PER_PAGE = [10, 20, 30, 40, 50];

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    /**
     * Halaman utama manajemen pengguna.
     */
    public function index(): string
    {
        $data = [
            'title'   => 'Manajemen Pengguna Sistem',
            'roles'   => $this->roleModel->getAllRoles(),
            'summary' => $this->userModel->getSummaryCounts(),
        ];

        return view('users/index', $data);
    }

    /**
     * Endpoint AJAX (GET): Daftar pengguna dengan pencarian, filter, dan paginasi.
     */
    public function list(): ResponseInterface
    {
        try {
            $search  = (string) ($this->request->getGet('search') ?? '');
            $roleId  = (int) ($this->request->getGet('role_id') ?? 0);
            $status  = (string) ($this->request->getGet('status') ?? '');
            $page    = max(1, (int) ($this->request->getGet('page') ?? 1));
            $perPage = (int) ($this->request->getGet('per_page') ?? 10);

            if (! in_array($perPage, self::ALLOWED_PER_PAGE, true)) {
                $perPage = 10;
            }

            $params = [
                'search'   => $search,
                'role_id'  => $roleId,
                'status'   => $status,
                'page'     => $page,
                'per_page' => $perPage,
            ];

            $result  = $this->userModel->getPaginatedUsers($params);
            $summary = $this->userModel->getSummaryCounts();

            return $this->jsonResponse([
                'status'     => true,
                'data'       => $result['items'],
                'pagination' => $result['pagination'],
                'summary'    => $summary,
            ]);
        } catch (Throwable $e) {
            log_message('error', '[UserController::list] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal memuat data pengguna: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (GET): Detail 1 pengguna.
     */
    public function show(int $id): ResponseInterface
    {
        try {
            $user = $this->userModel->getUserDetail($id);

            if (! $user) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Pengguna tidak ditemukan.',
                ], 404);
            }

            // Hilangkan field sensitif
            unset($user['password']);

            return $this->jsonResponse([
                'status' => true,
                'data'   => $user,
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat memuat detail pengguna.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Tambah pengguna baru.
     */
    public function store(): ResponseInterface
    {
        try {
            $rules = [
                'username'         => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
                'email'            => 'required|valid_email|max_length[100]|is_unique[users.email]',
                'fullname'         => 'required|min_length[3]|max_length[100]',
                'role_id'          => 'required|is_natural_no_zero',
                'password'         => 'required|min_length[6]',
                'password_confirm' => 'required|matches[password]',
                'status'           => 'required|in_list[active,inactive]',
            ];

            $messages = [
                'username' => [
                    'required'    => 'Username wajib diisi.',
                    'min_length'  => 'Username minimal 3 karakter.',
                    'max_length'  => 'Username maksimal 50 karakter.',
                    'is_unique'   => 'Username ini sudah digunakan akun lain.',
                ],
                'email' => [
                    'required'    => 'Email wajib diisi.',
                    'valid_email' => 'Format email tidak valid.',
                    'is_unique'   => 'Email ini sudah terdaftar.',
                ],
                'fullname' => [
                    'required'    => 'Nama lengkap wajib diisi.',
                    'min_length'  => 'Nama lengkap minimal 3 karakter.',
                ],
                'role_id' => [
                    'required' => 'Peran pengguna (role) wajib dipilih.',
                ],
                'password' => [
                    'required'   => 'Password wajib diisi.',
                    'min_length' => 'Password minimal 6 karakter.',
                ],
                'password_confirm' => [
                    'required' => 'Konfirmasi password wajib diisi.',
                    'matches'  => 'Konfirmasi password tidak cocok dengan password.',
                ],
                'status' => [
                    'required' => 'Status akun wajib ditentukan.',
                    'in_list'  => 'Status harus aktif atau nonaktif.',
                ],
            ];

            if (! $this->validate($rules, $messages)) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ], 422);
            }

            $data = [
                'username' => trim((string) $this->request->getPost('username')),
                'email'    => trim((string) $this->request->getPost('email')),
                'fullname' => trim((string) $this->request->getPost('fullname')),
                'role_id'  => (int) $this->request->getPost('role_id'),
                'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_BCRYPT),
                'status'   => trim((string) $this->request->getPost('status')),
            ];

            $insertId = $this->userModel->insert($data);

            if (! $insertId) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Gagal menyimpan data pengguna.',
                ], 500);
            }

            $newUser = $this->userModel->getUserDetail($insertId);
            unset($newUser['password']);

            return $this->jsonResponse([
                'status'  => true,
                'message' => 'Pengguna baru berhasil ditambahkan!',
                'data'    => $newUser,
            ], 201);
        } catch (Throwable $e) {
            log_message('error', '[UserController::store] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat membuat pengguna: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Perbarui data pengguna.
     */
    public function update(int $id): ResponseInterface
    {
        try {
            $user = $this->userModel->find($id);
            if (! $user) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Pengguna tidak ditemukan.',
                ], 404);
            }

            $rules = [
                'username' => "required|min_length[3]|max_length[50]|is_unique[users.username,id,{$id}]",
                'email'    => "required|valid_email|max_length[100]|is_unique[users.email,id,{$id}]",
                'fullname' => 'required|min_length[3]|max_length[100]',
                'role_id'  => 'required|is_natural_no_zero',
                'status'   => 'required|in_list[active,inactive]',
            ];

            $password = trim((string) $this->request->getPost('password'));
            if ($password !== '') {
                $rules['password']         = 'min_length[6]';
                $rules['password_confirm'] = 'matches[password]';
            }

            $messages = [
                'username' => [
                    'required'  => 'Username wajib diisi.',
                    'is_unique' => 'Username sudah digunakan akun lain.',
                ],
                'email' => [
                    'required'  => 'Email wajib diisi.',
                    'is_unique' => 'Email sudah terdaftar.',
                ],
                'fullname' => [
                    'required' => 'Nama lengkap wajib diisi.',
                ],
                'password' => [
                    'min_length' => 'Password baru minimal 6 karakter.',
                ],
                'password_confirm' => [
                    'matches' => 'Konfirmasi password tidak sesuai.',
                ],
            ];

            if (! $this->validate($rules, $messages)) {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ], 422);
            }

            // Proteksi: jangan izinkan user menonaktifkan akunnya sendiri
            $currentUserId = (int) session('user_id');
            $newStatus     = trim((string) $this->request->getPost('status'));
            if ($id === $currentUserId && $newStatus === 'inactive') {
                return $this->jsonResponse([
                    'status' => false,
                    'errors' => [
                        'status' => 'Anda tidak dapat menonaktifkan akun Anda sendiri saat sedang masuk.',
                    ],
                ], 422);
            }

            $data = [
                'username' => trim((string) $this->request->getPost('username')),
                'email'    => trim((string) $this->request->getPost('email')),
                'fullname' => trim((string) $this->request->getPost('fullname')),
                'role_id'  => (int) $this->request->getPost('role_id'),
                'status'   => $newStatus,
            ];

            if ($password !== '') {
                $data['password'] = password_hash($password, PASSWORD_BCRYPT);
            }

            $this->userModel->update($id, $data);

            $updatedUser = $this->userModel->getUserDetail($id);
            unset($updatedUser['password']);

            return $this->jsonResponse([
                'status'  => true,
                'message' => 'Data pengguna berhasil diperbarui!',
                'data'    => $updatedUser,
            ]);
        } catch (Throwable $e) {
            log_message('error', '[UserController::update] ' . $e->getMessage());

            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat memperbarui pengguna: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Beralih status aktif / nonaktif pengguna.
     */
    public function toggleStatus(int $id): ResponseInterface
    {
        try {
            $user = $this->userModel->find($id);
            if (! $user) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Pengguna tidak ditemukan.',
                ], 404);
            }

            $currentUserId = (int) session('user_id');
            if ($id === $currentUserId) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Anda tidak dapat mengubah status akun Anda sendiri.',
                ], 422);
            }

            $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
            $this->userModel->update($id, ['status' => $newStatus]);

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Akun {$user['username']} sekarang " . ($newStatus === 'active' ? 'aktif' : 'nonaktif') . '.',
                'data'    => [
                    'id'     => $id,
                    'status' => $newStatus,
                ],
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal mengubah status pengguna.',
            ], 500);
        }
    }

    /**
     * Endpoint AJAX (POST): Hapus pengguna (soft delete).
     */
    public function delete(int $id): ResponseInterface
    {
        try {
            $user = $this->userModel->find($id);
            if (! $user) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Pengguna tidak ditemukan.',
                ], 404);
            }

            $currentUserId = (int) session('user_id');
            if ($id === $currentUserId) {
                return $this->jsonResponse([
                    'status'  => false,
                    'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
                ], 422);
            }

            $this->userModel->delete($id);

            return $this->jsonResponse([
                'status'  => true,
                'message' => "Pengguna {$user['username']} ({$user['fullname']}) berhasil dihapus.",
            ]);
        } catch (Throwable $e) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal menghapus pengguna.',
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

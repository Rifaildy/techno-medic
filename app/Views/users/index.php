<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="tm-users-wrapper space-y-6">

    <!-- ══════════════════════════════════════════════
         HEADER HALAMAN
    ══════════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Pengguna Sistem</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola hak akses pengguna, verifikasi peran RBAC (Admin, Petugas, Dokter), dan keamanan akun.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="loadUsers()" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-semibold rounded-xl shadow-sm transition">
                <i class="fa-solid fa-arrows-rotate text-slate-400"></i>
                <span>Segarkan</span>
            </button>
            <button type="button" onclick="openCreateUserModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-primaryHover text-white text-sm font-semibold rounded-xl shadow-md shadow-primary/20 transition">
                <i class="fa-solid fa-user-plus"></i>
                <span>Tambah Pengguna</span>
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         STATISTIK PENGGUNA
    ══════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-users-gear"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Pengguna</p>
                <p class="text-2xl font-bold text-slate-800" id="stat-total"><?= number_format($summary['total'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-purple-700 uppercase tracking-wider">Administrator</p>
                <p class="text-2xl font-bold text-purple-700" id="stat-admins"><?= number_format($summary['admins'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-blue-700 uppercase tracking-wider">Petugas Loket</p>
                <p class="text-2xl font-bold text-blue-700" id="stat-petugas"><?= number_format($summary['petugas'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-teal-700 uppercase tracking-wider">Dokter</p>
                <p class="text-2xl font-bold text-teal-700" id="stat-doctors"><?= number_format($summary['doctors'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5 col-span-2 md:col-span-1">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-emerald-700 uppercase tracking-wider">Akun Aktif</p>
                <p class="text-2xl font-bold text-emerald-700" id="stat-active"><?= number_format($summary['active'] ?? 0) ?></p>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         TOOLBAR FILTER & PENCARIAN
    ══════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
            
            <div class="lg:col-span-5 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </span>
                <input type="text" id="filter-search" placeholder="Cari Username / Nama Lengkap / Email..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                       onkeyup="handleSearchKey(event)">
            </div>

            <div class="lg:col-span-3">
                <select id="filter-role" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadUsers(1)">
                    <option value="">Semua Peran (Role)</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= esc($r['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lg:col-span-2">
                <select id="filter-status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadUsers(1)">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <select id="filter-per-page" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadUsers(1)">
                    <option value="10">10 / hal</option>
                    <option value="20">20 / hal</option>
                    <option value="30">30 / hal</option>
                    <option value="50">50 / hal</option>
                </select>
            </div>

        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         TABEL PENGGUNA
    ══════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Nama Lengkap & User</th>
                        <th class="py-3.5 px-4">Email</th>
                        <th class="py-3.5 px-4">Peran (Role)</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody id="users-tbody" class="divide-y divide-slate-100 text-slate-700">
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                            <p class="text-sm">Memuat data pengguna...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3.5 bg-slate-50/60 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div id="pagination-info">Menampilkan data...</div>
            <div class="flex items-center gap-1.5" id="pagination-buttons"></div>
        </div>
    </div>

</div>

<!-- ══════════════════════════════════════════════
     MODAL: TAMBAH / EDIT PENGGUNA
══════════════════════════════════════════════ -->
<div id="modal-user" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        
        <div class="px-6 py-4.5 bg-gradient-to-r from-sidebarBg to-darkteal text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-user-gear text-primary text-lg"></i>
                <h3 class="text-base font-bold text-white" id="modal-user-title">Tambah Pengguna Baru</h3>
            </div>
            <button type="button" onclick="closeUserModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="form-user" onsubmit="handleUserSubmit(event)" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" id="form-user-id" value="">

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="fullname" id="user-fullname" required placeholder="Contoh: Budi Santoso"
                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" id="user-username" required placeholder="username_unik"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email Aktif <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="user-email" required placeholder="user@rumahsakit.com"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Peran (Role) <span class="text-rose-500">*</span></label>
                    <select name="role_id" id="user-role-id" required
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="">-- Pilih Peran --</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= esc($r['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status Akun <span class="text-rose-500">*</span></label>
                    <select name="status" id="user-status" required
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5" id="password-group">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Password <span class="text-rose-500" id="password-required-mark">*</span>
                    </label>
                    <input type="password" name="password" id="user-password" placeholder="Minimal 6 karakter"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    <p class="text-[10px] text-slate-400 mt-1" id="password-edit-hint" style="display:none;">Kosongkan jika tidak ingin mengubah password.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Konfirmasi Password <span class="text-rose-500" id="password-confirm-required-mark">*</span>
                    </label>
                    <input type="password" name="password_confirm" id="user-password-confirm" placeholder="Ulangi password"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeUserModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" id="btn-submit-user" class="px-5 py-2 bg-primary hover:bg-primaryHover text-white text-xs font-semibold rounded-xl shadow-md shadow-primary/20 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-check"></i>
                    <span id="btn-submit-user-label">Simpan Pengguna</span>
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ══════════════════════════════════════════════
     SCRIPTS & CLIENT-SIDE LOGIC
══════════════════════════════════════════════ -->
<script>
    let currentUsersPage = 1;
    let usersSearchTimeout = null;
    const currentLoggedInId = <?= (int) session('user_id') ?>;

    document.addEventListener('DOMContentLoaded', () => {
        loadUsers(1);
    });

    async function loadUsers(page = 1) {
        currentUsersPage = page;
        const tbody = document.getElementById('users-tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="py-12 text-center text-slate-400">
                    <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                    <p class="text-sm">Memuat data pengguna...</p>
                </td>
            </tr>
        `;

        const search  = document.getElementById('filter-search').value.trim();
        const roleId  = document.getElementById('filter-role').value;
        const status  = document.getElementById('filter-status').value;
        const perPage = document.getElementById('filter-per-page').value;

        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: search,
            role_id: roleId,
            status: status,
        });

        try {
            const res = await fetch(`<?= base_url('users/list') ?>?${params.toString()}`);
            const result = await res.json();

            if (!result.status) {
                throw new Error(result.message || 'Gagal memuat pengguna.');
            }

            if (result.summary) {
                document.getElementById('stat-total').textContent = Number(result.summary.total || 0).toLocaleString();
                document.getElementById('stat-admins').textContent = Number(result.summary.admins || 0).toLocaleString();
                document.getElementById('stat-petugas').textContent = Number(result.summary.petugas || 0).toLocaleString();
                document.getElementById('stat-doctors').textContent = Number(result.summary.doctors || 0).toLocaleString();
                document.getElementById('stat-active').textContent = Number(result.summary.active || 0).toLocaleString();
            }

            renderUsersTable(result.data);
            renderUsersPagination(result.pagination);

        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-rose-500">${escapeHtml(err.message)}</td></tr>`;
        }
    }

    function renderUsersTable(items) {
        const tbody = document.getElementById('users-tbody');

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="py-16 text-center text-slate-400">
                        <i class="fa-solid fa-user-slash text-3xl mb-2 text-slate-300"></i>
                        <p class="text-sm font-semibold text-slate-600">Tidak ada pengguna ditemukan</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        items.forEach(u => {
            const isMe = Number(u.id) === currentLoggedInId;

            // Role Badge
            let roleBadge = '';
            if (u.role_name === 'admin') {
                roleBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200"><i class="fa-solid fa-shield-halved text-[9px]"></i> ${escapeHtml(u.role_label)}</span>`;
            } else if (u.role_name === 'dokter') {
                roleBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-teal-700 border border-teal-200"><i class="fa-solid fa-user-doctor text-[9px]"></i> ${escapeHtml(u.role_label)}</span>`;
            } else {
                roleBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-headset text-[9px]"></i> ${escapeHtml(u.role_label)}</span>`;
            }

            // Status Badge
            const statusBadge = u.status === 'active'
                ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-circle text-[6px]"></i> Aktif</span>`
                : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200"><i class="fa-solid fa-circle text-[6px]"></i> Nonaktif</span>`;

            html += `
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-700 uppercase">
                                ${escapeHtml(u.username.substring(0, 2))}
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-xs">
                                    ${escapeHtml(u.fullname)}
                                    ${isMe ? '<span class="ml-1 text-[10px] font-semibold text-primary bg-primary/10 px-1.5 py-0.5 rounded">Akun Anda</span>' : ''}
                                </p>
                                <p class="text-[11px] text-slate-400 font-mono">@${escapeHtml(u.username)}</p>
                            </div>
                        </div>
                    </td>

                    <td class="py-3.5 px-4 text-xs font-mono text-slate-600">
                        ${escapeHtml(u.email)}
                    </td>

                    <td class="py-3.5 px-4">
                        ${roleBadge}
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        ${statusBadge}
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        <div class="inline-flex items-center gap-1.5">
                            <button type="button" onclick="openEditUserModal(${u.id})" title="Edit Pengguna"
                                    class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs transition">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            ${!isMe ? `
                                <button type="button" onclick="toggleUserStatus(${u.id}, '${escapeHtml(u.username)}')" title="${u.status === 'active' ? 'Nonaktifkan Akun' : 'Aktifkan Akun'}"
                                        class="w-7 h-7 rounded-lg ${u.status === 'active' ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200'} flex items-center justify-center text-xs transition">
                                    <i class="fa-solid ${u.status === 'active' ? 'fa-user-slash' : 'fa-user-check'}"></i>
                                </button>

                                <button type="button" onclick="confirmDeleteUser(${u.id}, '${escapeHtml(u.username)}')" title="Hapus Pengguna"
                                        class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center text-xs transition">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function renderUsersPagination(p) {
        const info = document.getElementById('pagination-info');
        const btns = document.getElementById('pagination-buttons');

        if (!p || p.total === 0) {
            info.textContent = 'Menampilkan 0 data';
            btns.innerHTML = '';
            return;
        }

        info.textContent = `Menampilkan ${p.from} - ${p.to} dari total ${p.total} pengguna`;
        let btnHtml = '';

        btnHtml += `
            <button type="button" onclick="loadUsers(${p.current_page - 1})" ${p.current_page <= 1 ? 'disabled class="opacity-40 cursor-not-allowed px-2.5 py-1 rounded-lg border border-slate-200 text-slate-400"' : 'class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 transition"'}>
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </button>
        `;

        for (let i = 1; i <= p.last_page; i++) {
            if (i === 1 || i === p.last_page || (i >= p.current_page - 1 && i <= p.current_page + 1)) {
                btnHtml += `
                    <button type="button" onclick="loadUsers(${i})" class="px-2.5 py-1 rounded-lg text-xs font-semibold transition ${i === p.current_page ? 'bg-primary text-white shadow-sm' : 'border border-slate-200 hover:bg-slate-100 text-slate-700'}">
                        ${i}
                    </button>
                `;
            }
        }

        btnHtml += `
            <button type="button" onclick="loadUsers(${p.current_page + 1})" ${p.current_page >= p.last_page ? 'disabled class="opacity-40 cursor-not-allowed px-2.5 py-1 rounded-lg border border-slate-200 text-slate-400"' : 'class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 transition"'}>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        `;

        btns.innerHTML = btnHtml;
    }

    function handleSearchKey(e) {
        clearTimeout(usersSearchTimeout);
        usersSearchTimeout = setTimeout(() => {
            loadUsers(1);
        }, 400);
    }

    // Modal Create / Edit
    function openCreateUserModal() {
        document.getElementById('form-user').reset();
        document.getElementById('form-user-id').value = '';
        document.getElementById('modal-user-title').textContent = 'Tambah Pengguna Baru';
        document.getElementById('btn-submit-user-label').textContent = 'Simpan Pengguna';
        
        document.getElementById('user-password').required = true;
        document.getElementById('user-password-confirm').required = true;
        document.getElementById('password-required-mark').style.display = 'inline';
        document.getElementById('password-confirm-required-mark').style.display = 'inline';
        document.getElementById('password-edit-hint').style.display = 'none';

        document.getElementById('modal-user').classList.remove('hidden');
    }

    async function openEditUserModal(id) {
        document.getElementById('form-user').reset();
        document.getElementById('form-user-id').value = id;
        document.getElementById('modal-user-title').textContent = 'Edit Pengguna';
        document.getElementById('btn-submit-user-label').textContent = 'Perbarui Pengguna';

        document.getElementById('user-password').required = false;
        document.getElementById('user-password-confirm').required = false;
        document.getElementById('password-required-mark').style.display = 'none';
        document.getElementById('password-confirm-required-mark').style.display = 'none';
        document.getElementById('password-edit-hint').style.display = 'block';

        try {
            const res = await fetch(`<?= base_url('users/show') ?>/${id}`);
            const result = await res.json();

            if (!result.status || !result.data) {
                throw new Error(result.message || 'Gagal memuat pengguna.');
            }

            const u = result.data;
            document.getElementById('user-fullname').value = u.fullname;
            document.getElementById('user-username').value = u.username;
            document.getElementById('user-email').value = u.email;
            document.getElementById('user-role-id').value = u.role_id;
            document.getElementById('user-status').value = u.status;

            document.getElementById('modal-user').classList.remove('hidden');
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message,
            });
        }
    }

    function closeUserModal() {
        document.getElementById('modal-user').classList.add('hidden');
    }

    async function handleUserSubmit(e) {
        e.preventDefault();

        const id = document.getElementById('form-user-id').value;
        const form = document.getElementById('form-user');
        const submitBtn = document.getElementById('btn-submit-user');
        const formData = new FormData(form);

        const url = id ? `<?= base_url('users/update') ?>/${id}` : `<?= base_url('users/store') ?>`;

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...`;

        try {
            const res = await fetch(url, {
                method: 'POST',
                body: formData,
            });
            const result = await res.json();

            if (!result.status) {
                let errorMsg = result.message || 'Gagal menyimpan pengguna.';
                if (result.errors) {
                    errorMsg = Object.values(result.errors).join('<br>');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: errorMsg,
                    confirmButtonColor: '#04BBA6',
                });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.message,
                timer: 2000,
                showConfirmButton: false,
            });

            closeUserModal();
            loadUsers(currentUsersPage);

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Sistem',
                text: err.message,
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fa-solid fa-check"></i> <span id="btn-submit-user-label">${id ? 'Perbarui Pengguna' : 'Simpan Pengguna'}</span>`;
        }
    }

    async function toggleUserStatus(id, username) {
        try {
            const res = await fetch(`<?= base_url('users/toggle-status') ?>/${id}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await res.json();

            if (!result.status) {
                throw new Error(result.message || 'Gagal mengubah status.');
            }

            Swal.fire({
                icon: 'success',
                title: 'Status Diubah',
                text: result.message,
                timer: 1500,
                showConfirmButton: false,
            });

            loadUsers(currentUsersPage);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message,
            });
        }
    }

    function confirmDeleteUser(id, username) {
        Swal.fire({
            title: 'Hapus Pengguna?',
            html: `Apakah Anda yakin ingin menghapus akun <strong class="text-rose-600">@${escapeHtml(username)}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch(`<?= base_url('users/delete') ?>/${id}`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const resJson = await res.json();

                    if (!resJson.status) {
                        throw new Error(resJson.message || 'Gagal menghapus pengguna.');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus',
                        text: resJson.message,
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    loadUsers(currentUsersPage);
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: err.message,
                    });
                }
            }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>

<?= $this->endSection() ?>

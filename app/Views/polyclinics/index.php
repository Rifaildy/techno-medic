<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="tm-polyclinics-wrapper space-y-6">

    <!-- ══════════════════════════════════════════════
         HEADER HALAMAN
    ══════════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Poliklinik</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola unit layanan rawat jalan, kode poliklinik, penempatan lokasi gedung, dan dokter penanggung jawab.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="loadPolyclinics()" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-semibold rounded-xl shadow-sm transition">
                <i class="fa-solid fa-arrows-rotate text-slate-400"></i>
                <span>Segarkan</span>
            </button>
            <button type="button" onclick="openCreatePolyModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-primaryHover text-white text-sm font-semibold rounded-xl shadow-md shadow-primary/20 transition">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Poliklinik</span>
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         STATISTIK POLIKLINIK
    ══════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4.5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center flex-shrink-0 text-xl">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Poliklinik</p>
                <p class="text-2xl font-bold text-slate-800" id="stat-total"><?= number_format($summary['total'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4.5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-emerald-700 uppercase tracking-wider">Poliklinik Aktif</p>
                <p class="text-2xl font-bold text-emerald-700" id="stat-active"><?= number_format($summary['active'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4.5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0 text-xl">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Poliklinik Nonaktif</p>
                <p class="text-2xl font-bold text-slate-600" id="stat-inactive"><?= number_format($summary['inactive'] ?? 0) ?></p>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         TOOLBAR FILTER & PENCARIAN
    ══════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            
            <div class="sm:col-span-7 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </span>
                <input type="text" id="filter-search" placeholder="Cari Kode Poli / Nama Poliklinik / Lokasi Gedung..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                       onkeyup="handleSearchKey(event)">
            </div>

            <div class="sm:col-span-3">
                <select id="filter-status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadPolyclinics(1)">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select id="filter-per-page" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadPolyclinics(1)">
                    <option value="10">10 / hal</option>
                    <option value="20">20 / hal</option>
                    <option value="30">30 / hal</option>
                </select>
            </div>

        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         TABEL POLIKLINIK
    ══════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-28">Kode Poli</th>
                        <th class="py-3.5 px-4">Nama Poliklinik</th>
                        <th class="py-3.5 px-4">Lokasi Gedung / Ruangan</th>
                        <th class="py-3.5 px-4 text-center">Dokter Aktif</th>
                        <th class="py-3.5 px-4 text-center">Kunjungan Hari Ini</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody id="polyclinics-tbody" class="divide-y divide-slate-100 text-slate-700">
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                            <p class="text-sm">Memuat data poliklinik...</p>
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
     MODAL: TAMBAH / EDIT POLIKLINIK
══════════════════════════════════════════════ -->
<div id="modal-poly" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
        
        <div class="px-6 py-4.5 bg-gradient-to-r from-sidebarBg to-darkteal text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-building-columns text-primary text-lg"></i>
                <h3 class="text-base font-bold text-white" id="modal-poly-title">Tambah Poliklinik Baru</h3>
            </div>
            <button type="button" onclick="closePolyModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="form-poly" onsubmit="handlePolySubmit(event)" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" id="form-poly-id" value="">

            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kode Poli <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" id="poly-code" required placeholder="POL-XXX"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-mono uppercase font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status <span class="text-rose-500">*</span></label>
                    <select name="status" id="poly-status" required
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Poliklinik <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="poly-name" required placeholder="Contoh: Poli Mata, Poli Saraf"
                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Lokasi / Gedung / Ruangan</label>
                <input type="text" name="location" id="poly-location" placeholder="Contoh: Gedung A - Lantai 2 (Ruang 204)"
                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closePolyModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" id="btn-submit-poly" class="px-5 py-2 bg-primary hover:bg-primaryHover text-white text-xs font-semibold rounded-xl shadow-md shadow-primary/20 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-check"></i>
                    <span id="btn-submit-poly-label">Simpan Poliklinik</span>
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL: DETAIL POLIKLINIK & DAFTAR DOKTER
══════════════════════════════════════════════ -->
<div id="modal-poly-detail" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        
        <div class="px-6 py-4 bg-slate-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-hospital text-primary text-lg"></i>
                <h3 class="text-base font-bold text-white">Detail Poliklinik</h3>
            </div>
            <button type="button" onclick="closePolyDetailModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs" id="poly-detail-content">
            <!-- Konten detail dimuat via JS -->
        </div>

        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
            <button type="button" onclick="closePolyDetailModal()" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition">
                Tutup
            </button>
        </div>

    </div>
</div>

<!-- ══════════════════════════════════════════════
     SCRIPTS & CLIENT-SIDE LOGIC
══════════════════════════════════════════════ -->
<script>
    let currentPolyPage = 1;
    let polySearchTimeout = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadPolyclinics(1);
    });

    async function loadPolyclinics(page = 1) {
        currentPolyPage = page;
        const tbody = document.getElementById('polyclinics-tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="py-12 text-center text-slate-400">
                    <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                    <p class="text-sm">Memuat data poliklinik...</p>
                </td>
            </tr>
        `;

        const search  = document.getElementById('filter-search').value.trim();
        const status  = document.getElementById('filter-status').value;
        const perPage = document.getElementById('filter-per-page').value;

        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: search,
            status: status,
        });

        try {
            const res = await fetch(`<?= base_url('polyclinics/list') ?>?${params.toString()}`);
            const result = await res.json();

            if (!result.status) {
                throw new Error(result.message || 'Gagal memuat poliklinik.');
            }

            if (result.summary) {
                document.getElementById('stat-total').textContent = Number(result.summary.total || 0).toLocaleString();
                document.getElementById('stat-active').textContent = Number(result.summary.active || 0).toLocaleString();
                document.getElementById('stat-inactive').textContent = Number(result.summary.inactive || 0).toLocaleString();
            }

            renderPolyTable(result.data);
            renderPolyPagination(result.pagination);

        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-rose-500">${escapeHtml(err.message)}</td></tr>`;
        }
    }

    function renderPolyTable(items) {
        const tbody = document.getElementById('polyclinics-tbody');

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="py-16 text-center text-slate-400">
                        <i class="fa-solid fa-hospital text-3xl mb-2 text-slate-300"></i>
                        <p class="text-sm font-semibold text-slate-600">Tidak ada poliklinik ditemukan</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        items.forEach(p => {
            const statusBadge = p.status === 'active'
                ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-circle text-[6px]"></i> Aktif</span>`
                : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200"><i class="fa-solid fa-circle text-[6px]"></i> Nonaktif</span>`;

            html += `
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3.5 px-4">
                        <span class="font-mono font-bold text-xs bg-slate-100 text-slate-800 px-2 py-1 rounded-md border border-slate-200">
                            ${escapeHtml(p.code)}
                        </span>
                    </td>

                    <td class="py-3.5 px-4 font-bold text-slate-800 text-xs">
                        ${escapeHtml(p.name)}
                    </td>

                    <td class="py-3.5 px-4 text-xs text-slate-600">
                        <i class="fa-solid fa-location-dot text-slate-400 mr-1 text-[10px]"></i>
                        ${escapeHtml(p.location || 'Belum ditentukan')}
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        <span class="inline-flex items-center gap-1 font-bold text-xs text-teal-800 bg-teal-50 border border-teal-200 px-2.5 py-0.5 rounded-full">
                            <i class="fa-solid fa-user-doctor text-[10px]"></i> ${p.active_doctors_count || 0} dokter
                        </span>
                    </td>

                    <td class="py-3.5 px-4 text-center font-bold text-slate-700 text-xs">
                        ${p.today_visits_count || 0} pasien
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        ${statusBadge}
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        <div class="inline-flex items-center gap-1.5">
                            <button type="button" onclick="openPolyDetailModal(${p.id})" title="Lihat Dokter Poli"
                                    class="w-7 h-7 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 flex items-center justify-center text-xs transition">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button type="button" onclick="openEditPolyModal(${p.id})" title="Edit Poliklinik"
                                    class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs transition">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <button type="button" onclick="togglePolyStatus(${p.id}, '${escapeHtml(p.name)}')" title="${p.status === 'active' ? 'Nonaktifkan' : 'Aktifkan'}"
                                    class="w-7 h-7 rounded-lg ${p.status === 'active' ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200'} flex items-center justify-center text-xs transition">
                                <i class="fa-solid ${p.status === 'active' ? 'fa-power-off' : 'fa-check'}"></i>
                            </button>

                            <button type="button" onclick="confirmDeletePoly(${p.id}, '${escapeHtml(p.name)}')" title="Hapus Poliklinik"
                                    class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center text-xs transition">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function renderPolyPagination(p) {
        const info = document.getElementById('pagination-info');
        const btns = document.getElementById('pagination-buttons');

        if (!p || p.total === 0) {
            info.textContent = 'Menampilkan 0 data';
            btns.innerHTML = '';
            return;
        }

        info.textContent = `Menampilkan ${p.from} - ${p.to} dari total ${p.total} poliklinik`;
        let btnHtml = '';

        btnHtml += `
            <button type="button" onclick="loadPolyclinics(${p.current_page - 1})" ${p.current_page <= 1 ? 'disabled class="opacity-40 cursor-not-allowed px-2.5 py-1 rounded-lg border border-slate-200 text-slate-400"' : 'class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 transition"'}>
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </button>
        `;

        for (let i = 1; i <= p.last_page; i++) {
            if (i === 1 || i === p.last_page || (i >= p.current_page - 1 && i <= p.current_page + 1)) {
                btnHtml += `
                    <button type="button" onclick="loadPolyclinics(${i})" class="px-2.5 py-1 rounded-lg text-xs font-semibold transition ${i === p.current_page ? 'bg-primary text-white shadow-sm' : 'border border-slate-200 hover:bg-slate-100 text-slate-700'}">
                        ${i}
                    </button>
                `;
            }
        }

        btnHtml += `
            <button type="button" onclick="loadPolyclinics(${p.current_page + 1})" ${p.current_page >= p.last_page ? 'disabled class="opacity-40 cursor-not-allowed px-2.5 py-1 rounded-lg border border-slate-200 text-slate-400"' : 'class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 transition"'}>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        `;

        btns.innerHTML = btnHtml;
    }

    function handleSearchKey(e) {
        clearTimeout(polySearchTimeout);
        polySearchTimeout = setTimeout(() => {
            loadPolyclinics(1);
        }, 400);
    }

    // Modal Create / Edit
    function openCreatePolyModal() {
        document.getElementById('form-poly').reset();
        document.getElementById('form-poly-id').value = '';
        document.getElementById('modal-poly-title').textContent = 'Tambah Poliklinik Baru';
        document.getElementById('btn-submit-poly-label').textContent = 'Simpan Poliklinik';
        document.getElementById('modal-poly').classList.remove('hidden');
    }

    async function openEditPolyModal(id) {
        document.getElementById('form-poly').reset();
        document.getElementById('form-poly-id').value = id;
        document.getElementById('modal-poly-title').textContent = 'Edit Poliklinik';
        document.getElementById('btn-submit-poly-label').textContent = 'Perbarui Poliklinik';

        try {
            const res = await fetch(`<?= base_url('polyclinics/show') ?>/${id}`);
            const result = await res.json();

            if (!result.status || !result.data) {
                throw new Error(result.message || 'Gagal memuat poliklinik.');
            }

            const p = result.data;
            document.getElementById('poly-code').value = p.code;
            document.getElementById('poly-name').value = p.name;
            document.getElementById('poly-location').value = p.location || '';
            document.getElementById('poly-status').value = p.status;

            document.getElementById('modal-poly').classList.remove('hidden');
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message,
            });
        }
    }

    function closePolyModal() {
        document.getElementById('modal-poly').classList.add('hidden');
    }

    async function handlePolySubmit(e) {
        e.preventDefault();

        const id = document.getElementById('form-poly-id').value;
        const form = document.getElementById('form-poly');
        const submitBtn = document.getElementById('btn-submit-poly');
        const formData = new FormData(form);

        const url = id ? `<?= base_url('polyclinics/update') ?>/${id}` : `<?= base_url('polyclinics/store') ?>`;

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...`;

        try {
            const res = await fetch(url, {
                method: 'POST',
                body: formData,
            });
            const result = await res.json();

            if (!result.status) {
                let errorMsg = result.message || 'Gagal menyimpan poliklinik.';
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

            closePolyModal();
            loadPolyclinics(currentPolyPage);

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Sistem',
                text: err.message,
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fa-solid fa-check"></i> <span id="btn-submit-poly-label">${id ? 'Perbarui Poliklinik' : 'Simpan Poliklinik'}</span>`;
        }
    }

    async function togglePolyStatus(id, name) {
        try {
            const res = await fetch(`<?= base_url('polyclinics/toggle-status') ?>/${id}`, {
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

            loadPolyclinics(currentPolyPage);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message,
            });
        }
    }

    function confirmDeletePoly(id, name) {
        Swal.fire({
            title: 'Hapus Poliklinik?',
            html: `Apakah Anda yakin ingin menghapus <strong>${escapeHtml(name)}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch(`<?= base_url('polyclinics/delete') ?>/${id}`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const resJson = await res.json();

                    if (!resJson.status) {
                        throw new Error(resJson.message || 'Gagal menghapus poliklinik.');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus',
                        text: resJson.message,
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    loadPolyclinics(currentPolyPage);
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

    // Modal Detail Dokter Bertugas
    async function openPolyDetailModal(id) {
        const modal = document.getElementById('modal-poly-detail');
        const container = document.getElementById('poly-detail-content');

        container.innerHTML = `
            <div class="py-10 text-center text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                <p>Memuat dokter poliklinik...</p>
            </div>
        `;
        modal.classList.remove('hidden');

        try {
            const res = await fetch(`<?= base_url('polyclinics/show') ?>/${id}`);
            const result = await res.json();

            if (!result.status || !result.data) {
                throw new Error(result.message || 'Gagal memuat detail.');
            }

            const p = result.data;
            const doctors = p.doctors || [];

            let docsHtml = '';
            if (doctors.length === 0) {
                docsHtml = `
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center text-slate-400">
                        <i class="fa-solid fa-user-doctor text-xl mb-1 text-slate-300"></i>
                        <p class="font-semibold text-slate-600">Belum ada dokter yang ditugaskan</p>
                        <a href="<?= base_url('doctors') ?>" class="text-primary hover:underline text-[11px] mt-1 inline-block">+ Tambah Dokter ke Poli ini</a>
                    </div>
                `;
            } else {
                doctors.forEach(d => {
                    docsHtml += `
                        <div class="p-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between text-xs hover:border-teal-300 transition">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-user-doctor"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800">${escapeHtml(d.name)}</p>
                                    <p class="text-slate-400 font-mono text-[11px]">${escapeHtml(d.specialization || 'Dokter Umum')} &bull; SIP: ${escapeHtml(d.sip)}</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold ${d.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'}">
                                ${d.status === 'active' ? 'Aktif' : 'Nonaktif'}
                            </span>
                        </div>
                    `;
                });
            }

            container.innerHTML = `
                <div class="p-4 bg-teal-50 border border-teal-200 rounded-2xl flex items-center justify-between">
                    <div>
                        <span class="font-mono font-bold text-xs bg-teal-200/60 text-teal-800 px-2 py-0.5 rounded">${escapeHtml(p.code)}</span>
                        <h4 class="text-base font-bold text-teal-950 mt-1">${escapeHtml(p.name)}</h4>
                        <p class="text-xs text-slate-600"><i class="fa-solid fa-location-dot text-slate-400 mr-1"></i> ${escapeHtml(p.location || 'Lokasi belum ditentukan')}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold ${p.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'}">
                        ${p.status === 'active' ? 'Operasional Aktif' : 'Nonaktif'}
                    </span>
                </div>

                <div>
                    <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-user-doctor text-primary"></i>
                        Dokter yang Bertugas (${doctors.length})
                    </h5>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        ${docsHtml}
                    </div>
                </div>
            `;

        } catch (err) {
            container.innerHTML = `<p class="py-8 text-center text-rose-500">${escapeHtml(err.message)}</p>`;
        }
    }

    function closePolyDetailModal() {
        document.getElementById('modal-poly-detail').classList.add('hidden');
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

<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="tm-doctors-wrapper space-y-6">

    <!-- ══════════════════════════════════════════════
         HEADER HALAMAN
    ══════════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Dokter</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola data dokter spesialis dan umum, nomor SIP resmi, penugasan poliklinik, dan akun login rekam medis.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="loadDoctors()" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-semibold rounded-xl shadow-sm transition">
                <i class="fa-solid fa-arrows-rotate text-slate-400"></i>
                <span>Segarkan</span>
            </button>
            <button type="button" onclick="openCreateDoctorModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-primaryHover text-white text-sm font-semibold rounded-xl shadow-md shadow-primary/20 transition">
                <i class="fa-solid fa-user-doctor"></i>
                <span>Tambah Dokter Baru</span>
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         STATISTIK DOKTER
    ══════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Dokter</p>
                <p class="text-2xl font-bold text-slate-800" id="stat-total"><?= number_format($summary['total'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-emerald-700 uppercase tracking-wider">Aktif Bertugas</p>
                <p class="text-2xl font-bold text-emerald-700" id="stat-active"><?= number_format($summary['active'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-user-slash"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Nonaktif</p>
                <p class="text-2xl font-bold text-slate-600" id="stat-inactive"><?= number_format($summary['inactive'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-mars"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-blue-700 uppercase tracking-wider">Dokter Pria</p>
                <p class="text-2xl font-bold text-blue-700" id="stat-male"><?= number_format($summary['male'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5 col-span-2 md:col-span-1">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-venus"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-rose-700 uppercase tracking-wider">Dokter Wanita</p>
                <p class="text-2xl font-bold text-rose-700" id="stat-female"><?= number_format($summary['female'] ?? 0) ?></p>
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
                <input type="text" id="filter-search" placeholder="Cari Nama Dokter / NIK / SIP / Spesialisasi..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                       onkeyup="handleSearchKey(event)">
            </div>

            <div class="lg:col-span-3">
                <select id="filter-polyclinic" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadDoctors(1)">
                    <option value="">Semua Poliklinik</option>
                    <?php foreach ($polyclinics as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lg:col-span-2">
                <select id="filter-gender" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadDoctors(1)">
                    <option value="">Semua Gender</option>
                    <option value="L">Laki-Laki (L)</option>
                    <option value="P">Perempuan (P)</option>
                </select>
            </div>

            <div class="lg:col-span-2 flex items-center gap-2">
                <select id="filter-status" class="w-1/2 px-2 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadDoctors(1)">
                    <option value="">Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
                <select id="filter-per-page" class="w-1/2 px-2 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadDoctors(1)">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="30">30</option>
                </select>
            </div>

        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         TABEL DOKTER
    ══════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Nama Dokter & SIP</th>
                        <th class="py-3.5 px-4">Poliklinik Penugasan</th>
                        <th class="py-3.5 px-4">Spesialisasi</th>
                        <th class="py-3.5 px-4">Kontak & Akun Login</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody id="doctors-tbody" class="divide-y divide-slate-100 text-slate-700">
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                            <p class="text-sm">Memuat data dokter...</p>
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
     MODAL: TAMBAH / EDIT DOKTER
══════════════════════════════════════════════ -->
<div id="modal-doctor" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-xl overflow-hidden animate-in fade-in zoom-in duration-200">
        
        <div class="px-6 py-4.5 bg-gradient-to-r from-sidebarBg to-darkteal text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-user-doctor text-primary text-lg"></i>
                <h3 class="text-base font-bold text-white" id="modal-doctor-title">Tambah Dokter Baru</h3>
            </div>
            <button type="button" onclick="closeDoctorModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="form-doctor" onsubmit="handleDoctorSubmit(event)" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" id="form-doctor-id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">NIK (16 Digit) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nik" id="doc-nik" required maxlength="16" placeholder="3171011204800001"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Surat Izin Praktik (SIP) <span class="text-rose-500">*</span></label>
                    <input type="text" name="sip" id="doc-sip" required placeholder="SIP/503/001/IDI/2022"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="doc-name" required placeholder="Contoh: dr. Agus Wijaya, Sp.PD"
                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Poliklinik Penugasan <span class="text-rose-500">*</span></label>
                    <select name="polyclinic_id" id="doc-poly-id" required
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="">-- Pilih Poliklinik --</option>
                        <?php foreach ($polyclinics as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?> (<?= esc($p['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Spesialisasi Bidang</label>
                    <input type="text" name="specialization" id="doc-specialization" placeholder="Contoh: Dokter Umum, Spesialis Anak"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="gender" id="doc-gender" required
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="L">Laki-Laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">No. Telepon / WhatsApp</label>
                    <input type="text" name="phone" id="doc-phone" placeholder="081234567890"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status <span class="text-rose-500">*</span></label>
                    <select name="status" id="doc-status" required
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="active">Aktif Bertugas</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email Dokter</label>
                    <input type="email" name="email" id="doc-email" placeholder="dokter@rumahsakit.com"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tautkan Akun Login Sistem</label>
                    <select name="user_id" id="doc-user-id"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="">-- Tanpa Akun Pengguna --</option>
                        <!-- Pilihan user dokter dimuat dinamis -->
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Hanya menampilkan akun dengan peran Dokter.</p>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeDoctorModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" id="btn-submit-doc" class="px-5 py-2 bg-primary hover:bg-primaryHover text-white text-xs font-semibold rounded-xl shadow-md shadow-primary/20 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-check"></i>
                    <span id="btn-submit-doc-label">Simpan Dokter</span>
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL: DETAIL DOKTER LENGKAP
══════════════════════════════════════════════ -->
<div id="modal-doc-detail" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        
        <div class="px-6 py-4 bg-slate-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-id-badge text-primary text-lg"></i>
                <h3 class="text-base font-bold text-white">Profil Dokter</h3>
            </div>
            <button type="button" onclick="closeDoctorDetailModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs" id="doc-detail-content">
            <!-- Konten detail dimuat via JS -->
        </div>

        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
            <button type="button" onclick="closeDoctorDetailModal()" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition">
                Tutup
            </button>
        </div>

    </div>
</div>

<!-- ══════════════════════════════════════════════
     SCRIPTS & CLIENT-SIDE LOGIC
══════════════════════════════════════════════ -->
<script>
    let currentDocPage = 1;
    let docSearchTimeout = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadDoctors(1);
    });

    async function loadDoctors(page = 1) {
        currentDocPage = page;
        const tbody = document.getElementById('doctors-tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="py-12 text-center text-slate-400">
                    <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                    <p class="text-sm">Memuat data dokter...</p>
                </td>
            </tr>
        `;

        const search  = document.getElementById('filter-search').value.trim();
        const polyId  = document.getElementById('filter-polyclinic').value;
        const gender  = document.getElementById('filter-gender').value;
        const status  = document.getElementById('filter-status').value;
        const perPage = document.getElementById('filter-per-page').value;

        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: search,
            polyclinic_id: polyId,
            gender: gender,
            status: status,
        });

        try {
            const res = await fetch(`<?= base_url('doctors/list') ?>?${params.toString()}`);
            const result = await res.json();

            if (!result.status) {
                throw new Error(result.message || 'Gagal memuat dokter.');
            }

            if (result.summary) {
                document.getElementById('stat-total').textContent = Number(result.summary.total || 0).toLocaleString();
                document.getElementById('stat-active').textContent = Number(result.summary.active || 0).toLocaleString();
                document.getElementById('stat-inactive').textContent = Number(result.summary.inactive || 0).toLocaleString();
                document.getElementById('stat-male').textContent = Number(result.summary.male || 0).toLocaleString();
                document.getElementById('stat-female').textContent = Number(result.summary.female || 0).toLocaleString();
            }

            renderDoctorsTable(result.data);
            renderDoctorsPagination(result.pagination);

        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-rose-500">${escapeHtml(err.message)}</td></tr>`;
        }
    }

    function renderDoctorsTable(items) {
        const tbody = document.getElementById('doctors-tbody');

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-16 text-center text-slate-400">
                        <i class="fa-solid fa-user-doctor text-3xl mb-2 text-slate-300"></i>
                        <p class="text-sm font-semibold text-slate-600">Tidak ada dokter ditemukan</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        items.forEach(d => {
            const statusBadge = d.status === 'active'
                ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-circle text-[6px]"></i> Aktif</span>`
                : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200"><i class="fa-solid fa-circle text-[6px]"></i> Nonaktif</span>`;

            html += `
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl ${d.gender === 'L' ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700'} flex items-center justify-center font-bold text-xs flex-shrink-0">
                                ${d.gender === 'L' ? 'L' : 'P'}
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-xs">${escapeHtml(d.name)}</p>
                                <p class="text-[11px] text-slate-400 font-mono mt-0.5">SIP: ${escapeHtml(d.sip)} &bull; NIK: ${escapeHtml(d.nik)}</p>
                            </div>
                        </div>
                    </td>

                    <td class="py-3.5 px-4 font-semibold text-teal-800 text-xs">
                        <span class="inline-flex items-center gap-1">
                            <i class="fa-solid fa-hospital text-[10px] text-teal-600"></i>
                            ${escapeHtml(d.polyclinic_name || 'Belum Ditugaskan')}
                        </span>
                    </td>

                    <td class="py-3.5 px-4 text-xs text-slate-700">
                        ${escapeHtml(d.specialization || 'Dokter Umum')}
                    </td>

                    <td class="py-3.5 px-4 text-xs">
                        <p class="text-slate-600">${escapeHtml(d.phone || '-')}</p>
                        ${d.user_username ? `
                            <span class="inline-flex items-center gap-1 font-mono text-[10px] text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200 mt-0.5">
                                <i class="fa-solid fa-user-lock text-[8px]"></i> @${escapeHtml(d.user_username)}
                            </span>
                        ` : '<span class="text-[10px] text-slate-400 italic">Tanpa akun login</span>'}
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        ${statusBadge}
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        <div class="inline-flex items-center gap-1.5">
                            <button type="button" onclick="openDoctorDetailModal(${d.id})" title="Lihat Detail Profil Dokter"
                                    class="w-7 h-7 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 flex items-center justify-center text-xs transition">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button type="button" onclick="openEditDoctorModal(${d.id})" title="Edit Data Dokter"
                                    class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs transition">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <button type="button" onclick="toggleDoctorStatus(${d.id}, '${escapeHtml(d.name)}')" title="${d.status === 'active' ? 'Nonaktifkan' : 'Aktifkan'}"
                                    class="w-7 h-7 rounded-lg ${d.status === 'active' ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200'} flex items-center justify-center text-xs transition">
                                <i class="fa-solid ${d.status === 'active' ? 'fa-power-off' : 'fa-check'}"></i>
                            </button>

                            <button type="button" onclick="confirmDeleteDoctor(${d.id}, '${escapeHtml(d.name)}')" title="Hapus Dokter"
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

    function renderDoctorsPagination(p) {
        const info = document.getElementById('pagination-info');
        const btns = document.getElementById('pagination-buttons');

        if (!p || p.total === 0) {
            info.textContent = 'Menampilkan 0 data';
            btns.innerHTML = '';
            return;
        }

        info.textContent = `Menampilkan ${p.from} - ${p.to} dari total ${p.total} dokter`;
        let btnHtml = '';

        btnHtml += `
            <button type="button" onclick="loadDoctors(${p.current_page - 1})" ${p.current_page <= 1 ? 'disabled class="opacity-40 cursor-not-allowed px-2.5 py-1 rounded-lg border border-slate-200 text-slate-400"' : 'class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 transition"'}>
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </button>
        `;

        for (let i = 1; i <= p.last_page; i++) {
            if (i === 1 || i === p.last_page || (i >= p.current_page - 1 && i <= p.current_page + 1)) {
                btnHtml += `
                    <button type="button" onclick="loadDoctors(${i})" class="px-2.5 py-1 rounded-lg text-xs font-semibold transition ${i === p.current_page ? 'bg-primary text-white shadow-sm' : 'border border-slate-200 hover:bg-slate-100 text-slate-700'}">
                        ${i}
                    </button>
                `;
            }
        }

        btnHtml += `
            <button type="button" onclick="loadDoctors(${p.current_page + 1})" ${p.current_page >= p.last_page ? 'disabled class="opacity-40 cursor-not-allowed px-2.5 py-1 rounded-lg border border-slate-200 text-slate-400"' : 'class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 transition"'}>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        `;

        btns.innerHTML = btnHtml;
    }

    function handleSearchKey(e) {
        clearTimeout(docSearchTimeout);
        docSearchTimeout = setTimeout(() => {
            loadDoctors(1);
        }, 400);
    }

    // Modal Create / Edit
    async function openCreateDoctorModal() {
        document.getElementById('form-doctor').reset();
        document.getElementById('form-doctor-id').value = '';
        document.getElementById('modal-doctor-title').textContent = 'Tambah Dokter Baru';
        document.getElementById('btn-submit-doc-label').textContent = 'Simpan Dokter';

        await loadAvailableUsersDropdown();
        document.getElementById('modal-doctor').classList.remove('hidden');
    }

    async function openEditDoctorModal(id) {
        document.getElementById('form-doctor').reset();
        document.getElementById('form-doctor-id').value = id;
        document.getElementById('modal-doctor-title').textContent = 'Edit Data Dokter';
        document.getElementById('btn-submit-doc-label').textContent = 'Perbarui Dokter';

        try {
            await loadAvailableUsersDropdown(id);

            const res = await fetch(`<?= base_url('doctors/show') ?>/${id}`);
            const result = await res.json();

            if (!result.status || !result.data) {
                throw new Error(result.message || 'Gagal memuat dokter.');
            }

            const d = result.data;
            document.getElementById('doc-nik').value = d.nik;
            document.getElementById('doc-sip').value = d.sip;
            document.getElementById('doc-name').value = d.name;
            document.getElementById('doc-poly-id').value = d.polyclinic_id;
            document.getElementById('doc-specialization').value = d.specialization || '';
            document.getElementById('doc-gender').value = d.gender;
            document.getElementById('doc-phone').value = d.phone || '';
            document.getElementById('doc-email').value = d.email || '';
            document.getElementById('doc-status').value = d.status;

            if (d.user_id) {
                document.getElementById('doc-user-id').value = d.user_id;
            }

            document.getElementById('modal-doctor').classList.remove('hidden');
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message,
            });
        }
    }

    async function loadAvailableUsersDropdown(doctorId = null) {
        const select = document.getElementById('doc-user-id');
        select.innerHTML = '<option value="">-- Tanpa Akun Pengguna --</option>';

        try {
            const url = doctorId ? `<?= base_url('doctors/available-users') ?>/${doctorId}` : `<?= base_url('doctors/available-users') ?>`;
            const res = await fetch(url);
            const result = await res.json();

            if (result.status && result.data) {
                result.data.forEach(u => {
                    select.innerHTML += `<option value="${u.id}">${escapeHtml(u.fullname)} (@${escapeHtml(u.username)})</option>`;
                });
            }
        } catch (err) {
            console.error('Gagal memuat akun user dokter:', err);
        }
    }

    function closeDoctorModal() {
        document.getElementById('modal-doctor').classList.add('hidden');
    }

    async function handleDoctorSubmit(e) {
        e.preventDefault();

        const id = document.getElementById('form-doctor-id').value;
        const form = document.getElementById('form-doctor');
        const submitBtn = document.getElementById('btn-submit-doc');
        const formData = new FormData(form);

        const url = id ? `<?= base_url('doctors/update') ?>/${id}` : `<?= base_url('doctors/store') ?>`;

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...`;

        try {
            const res = await fetch(url, {
                method: 'POST',
                body: formData,
            });
            const result = await res.json();

            if (!result.status) {
                let errorMsg = result.message || 'Gagal menyimpan data dokter.';
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

            closeDoctorModal();
            loadDoctors(currentDocPage);

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Sistem',
                text: err.message,
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fa-solid fa-check"></i> <span id="btn-submit-doc-label">${id ? 'Perbarui Dokter' : 'Simpan Dokter'}</span>`;
        }
    }

    async function toggleDoctorStatus(id, name) {
        try {
            const res = await fetch(`<?= base_url('doctors/toggle-status') ?>/${id}`, {
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

            loadDoctors(currentDocPage);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message,
            });
        }
    }

    function confirmDeleteDoctor(id, name) {
        Swal.fire({
            title: 'Hapus Data Dokter?',
            html: `Apakah Anda yakin ingin menghapus data <strong>${escapeHtml(name)}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch(`<?= base_url('doctors/delete') ?>/${id}`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const resJson = await res.json();

                    if (!resJson.status) {
                        throw new Error(resJson.message || 'Gagal menghapus dokter.');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus',
                        text: resJson.message,
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    loadDoctors(currentDocPage);
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

    // Modal Detail Profil Dokter
    async function openDoctorDetailModal(id) {
        const modal = document.getElementById('modal-doc-detail');
        const container = document.getElementById('doc-detail-content');

        container.innerHTML = `
            <div class="py-10 text-center text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                <p>Memuat profil dokter...</p>
            </div>
        `;
        modal.classList.remove('hidden');

        try {
            const res = await fetch(`<?= base_url('doctors/show') ?>/${id}`);
            const result = await res.json();

            if (!result.status || !result.data) {
                throw new Error(result.message || 'Gagal memuat profil dokter.');
            }

            const d = result.data;

            container.innerHTML = `
                <div class="p-4 bg-teal-50 border border-teal-200 rounded-2xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl ${d.gender === 'L' ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700'} flex items-center justify-center font-bold text-lg">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-teal-950">${escapeHtml(d.name)}</h4>
                            <p class="text-xs text-teal-800 font-semibold">${escapeHtml(d.specialization || 'Dokter Umum')}</p>
                            <p class="text-[11px] text-slate-500 font-mono mt-0.5">SIP: ${escapeHtml(d.sip)}</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold ${d.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'}">
                        ${d.status === 'active' ? 'Aktif' : 'Nonaktif'}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Nomor Induk Kependudukan (NIK)</span>
                        <span class="font-mono font-bold text-slate-800">${escapeHtml(d.nik)}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Jenis Kelamin</span>
                        <span class="font-semibold text-slate-800">${d.gender === 'L' ? 'Laki-Laki (Pria)' : 'Perempuan (Wanita)'}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Poliklinik Penugasan</span>
                        <span class="font-bold text-teal-700">${escapeHtml(d.polyclinic_name || 'Belum Ditugaskan')}</span>
                        <span class="text-slate-400 block text-[10px]">${escapeHtml(d.polyclinic_location || '-')}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Akun Login Rekam Medis</span>
                        ${d.user_username ? `
                            <span class="font-mono text-purple-700 font-bold block">@${escapeHtml(d.user_username)}</span>
                            <span class="text-slate-500 text-[10px]">${escapeHtml(d.user_email || '')}</span>
                        ` : '<span class="text-slate-400 italic">Belum ditautkan ke akun login</span>'}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Nomor Telepon</span>
                        <span class="text-slate-800 font-medium">${escapeHtml(d.phone || '-')}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Email Korespondensi</span>
                        <span class="text-slate-800 font-medium">${escapeHtml(d.email || '-')}</span>
                    </div>
                </div>
            `;

        } catch (err) {
            container.innerHTML = `<p class="py-8 text-center text-rose-500">${escapeHtml(err.message)}</p>`;
        }
    }

    function closeDoctorDetailModal() {
        document.getElementById('modal-doc-detail').classList.add('hidden');
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

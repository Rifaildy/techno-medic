<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="tm-patients-wrapper space-y-6">

    <!-- ══════════════════════════════════════════════
         HEADER HALAMAN
    ══════════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Pengelolaan Data Pasien</h1>
            <p class="text-sm text-slate-500 mt-1">
                Master data pasien rawat jalan, validasi NIK dan Nomor Rekam Medis terintegrasi.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="loadPatients()" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-semibold rounded-xl shadow-sm transition">
                <i class="fa-solid fa-arrows-rotate text-slate-400"></i>
                <span>Segarkan</span>
            </button>
            <button type="button" onclick="openCreatePatientModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-primaryHover text-white text-sm font-semibold rounded-xl shadow-md shadow-primary/20 transition">
                <i class="fa-solid fa-user-plus"></i>
                <span>Tambah Pasien Baru</span>
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         RINGKASAN STATISTIK PASIEN
    ══════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-hospital-user"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Pasien Aktif</p>
                <p class="text-2xl font-bold text-slate-800" id="stat-active"><?= number_format($summary['total_active'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-shield-heart"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-blue-700 uppercase tracking-wider">Penjamin BPJS</p>
                <p class="text-2xl font-bold text-blue-700" id="stat-bpjs"><?= number_format($summary['total_bpjs'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-emerald-700 uppercase tracking-wider">Pasien Umum / Mandiri</p>
                <p class="text-2xl font-bold text-emerald-700" id="stat-umum"><?= number_format($summary['total_umum'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Pasien Nonaktif</p>
                <p class="text-2xl font-bold text-slate-600" id="stat-inactive"><?= number_format($summary['total_inactive'] ?? 0) ?></p>
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
                <input type="text" id="filter-search" placeholder="Cari No. RM / NIK / Nama Pasien..."
                       class="w-full pl-10 pr-9 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                       onkeyup="handleSearchKey(event)">
                <button type="button" id="btn-clear-search" onclick="clearSearchInput()" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500">
                    <i class="fa-solid fa-circle-xmark text-sm"></i>
                </button>
            </div>

            <div class="lg:col-span-3">
                <select id="filter-status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadPatients(1)">
                    <option value="active" selected>Status: Pasien Aktif</option>
                    <option value="inactive">Status: Pasien Nonaktif</option>
                    <option value="all">Semua Status</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <select id="filter-insurance" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadPatients(1)">
                    <option value="">Semua Penjamin</option>
                    <option value="UMUM">UMUM (Mandiri)</option>
                    <option value="BPJS">BPJS Kesehatan</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <select id="filter-per-page" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadPatients(1)">
                    <option value="10">10 / hal</option>
                    <option value="20">20 / hal</option>
                    <option value="30">30 / hal</option>
                    <option value="50">50 / hal</option>
                </select>
            </div>

        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         TABEL DATA PASIEN
    ══════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-14 text-center">No</th>
                        <th class="py-3.5 px-4 w-32">No. RM</th>
                        <th class="py-3.5 px-4">NIK</th>
                        <th class="py-3.5 px-4">Nama Pasien & Tanggal Lahir</th>
                        <th class="py-3.5 px-4">Gender & Gol. Darah</th>
                        <th class="py-3.5 px-4">No. Telepon</th>
                        <th class="py-3.5 px-4">Penjamin</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center w-44">Aksi</th>
                    </tr>
                </thead>
                <tbody id="patients-tbody" class="divide-y divide-slate-100 text-slate-700">
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                            <p class="text-sm">Memuat data pasien...</p>
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
     MODAL: TAMBAH / EDIT PASIEN
══════════════════════════════════════════════ -->
<div id="modal-patient" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-xl overflow-hidden animate-in fade-in zoom-in duration-200">
        
        <div class="px-6 py-4.5 bg-gradient-to-r from-sidebarBg to-darkteal text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-hospital-user text-primary text-lg"></i>
                <h3 class="text-base font-bold text-white" id="modal-patient-title">Tambah Pasien Baru</h3>
            </div>
            <button type="button" onclick="closePatientModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="form-patient" onsubmit="handlePatientSubmit(event)" class="p-6 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" id="form-patient-id" name="id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">No. Rekam Medis (Otomatis)</label>
                    <input type="text" id="patient-no-rm" name="no_rm" value="<?= esc($next_no_rm ?? 'RM-000001') ?>" readonly
                           class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono font-bold text-teal-800 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">NIK (16 Digit) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nik" id="patient-nik" required maxlength="16" placeholder="3201012505900001"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                           oninput="this.value = this.value.replace(/\D/g, '').slice(0, 16)">
                    <p class="text-[11px] text-rose-500 mt-0.5 hidden" id="err-nik"></p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Lengkap Pasien <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="patient-name" required placeholder="Nama lengkap sesuai KTP / Akta"
                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                <p class="text-[11px] text-rose-500 mt-0.5 hidden" id="err-name"></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="gender" id="patient-gender" required
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="L">Laki-Laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                    <p class="text-[11px] text-rose-500 mt-0.5 hidden" id="err-gender"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Golongan Darah</label>
                    <select name="blood_type" id="patient-blood-type"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="UNKNOWN">Tidak Diketahui (UNKNOWN)</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="AB">AB</option>
                        <option value="O">O</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tempat Lahir</label>
                    <input type="text" name="pob" id="patient-pob" placeholder="Kota / Kabupaten"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Lahir <span class="text-rose-500">*</span></label>
                    <input type="date" name="dob" id="patient-dob" required max="<?= date('Y-m-d') ?>"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    <p class="text-[11px] text-rose-500 mt-0.5 hidden" id="err-dob"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" id="patient-phone" placeholder="081234567890"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jenis Penjamin <span class="text-rose-500">*</span></label>
                    <select name="insurance_type" id="patient-insurance-type" required
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                            onchange="toggleBpjsInput(this.value)">
                        <option value="UMUM">UMUM (Mandiri)</option>
                        <option value="BPJS">BPJS Kesehatan</option>
                    </select>
                </div>
            </div>

            <div id="wrapper-bpjs-number" class="hidden">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nomor Kartu BPJS <span class="text-rose-500">*</span></label>
                <input type="text" name="insurance_number" id="patient-insurance-number" placeholder="Nomor peserta BPJS 13 digit"
                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                <p class="text-[11px] text-rose-500 mt-0.5 hidden" id="err-insurance_number"></p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Alamat Lengkap Domisili</label>
                <textarea name="address" id="patient-address" rows="2" placeholder="Nama Jalan, RT/RW, Desa/Kelurahan, Kecamatan"
                          class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closePatientModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" id="btn-submit-patient" class="px-5 py-2 bg-primary hover:bg-primaryHover text-white text-xs font-semibold rounded-xl shadow-md shadow-primary/20 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-check"></i>
                    <span id="btn-submit-patient-label">Simpan Data Pasien</span>
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL: DETAIL PASIEN LENGKAP
══════════════════════════════════════════════ -->
<div id="modal-patient-detail" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        
        <div class="px-6 py-4 bg-slate-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-address-card text-primary text-lg"></i>
                <h3 class="text-base font-bold text-white">Profil Pasien Terdaftar</h3>
            </div>
            <button type="button" onclick="closePatientDetailModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs" id="patient-detail-content">
            <!-- Konten detail dimuat via JS -->
        </div>

        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
            <button type="button" onclick="closePatientDetailModal()" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition">
                Tutup
            </button>
        </div>

    </div>
</div>

<!-- ══════════════════════════════════════════════
     SCRIPTS & CLIENT-SIDE LOGIC
══════════════════════════════════════════════ -->
<script>
    let currentPatientsPage = 1;
    let patientsSearchTimeout = null;
    let nextNoRmGlobal = '<?= esc($next_no_rm ?? 'RM-000001') ?>';

    document.addEventListener('DOMContentLoaded', () => {
        loadPatients(1);
    });

    async function loadPatients(page = 1) {
        currentPatientsPage = page;
        const tbody = document.getElementById('patients-tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="py-12 text-center text-slate-400">
                    <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                    <p class="text-sm">Memuat data pasien...</p>
                </td>
            </tr>
        `;

        const search    = document.getElementById('filter-search').value.trim();
        const status    = document.getElementById('filter-status').value;
        const insurance = document.getElementById('filter-insurance').value;
        const perPage   = document.getElementById('filter-per-page').value;

        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: search,
            status: status,
            insurance: insurance,
        });

        try {
            const res = await fetch(`<?= base_url('patients/list') ?>?${params.toString()}`);
            const result = await res.json();

            if (!result.status) {
                throw new Error(result.message || 'Gagal memuat data pasien.');
            }

            if (result.next_no_rm) {
                nextNoRmGlobal = result.next_no_rm;
            }

            if (result.summary) {
                document.getElementById('stat-active').textContent   = Number(result.summary.total_active || 0).toLocaleString();
                document.getElementById('stat-bpjs').textContent     = Number(result.summary.total_bpjs || 0).toLocaleString();
                document.getElementById('stat-umum').textContent     = Number(result.summary.total_umum || 0).toLocaleString();
                document.getElementById('stat-inactive').textContent = Number(result.summary.total_inactive || 0).toLocaleString();
            }

            renderPatientsTable(result.data, result.pagination);
            renderPatientsPagination(result.pagination);

        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-rose-500">${escapeHtml(err.message)}</td></tr>`;
        }
    }

    function renderPatientsTable(items, pagination) {
        const tbody = document.getElementById('patients-tbody');

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="py-16 text-center text-slate-400">
                        <i class="fa-solid fa-user-slash text-3xl mb-2 text-slate-300"></i>
                        <p class="text-sm font-semibold text-slate-600">Tidak ada data pasien yang sesuai kriteria</p>
                    </td>
                </tr>
            `;
            return;
        }

        const startNumber = (pagination.current_page - 1) * pagination.per_page;

        let html = '';
        items.forEach((p, idx) => {
            const rowNumber = startNumber + idx + 1;
            const isInactive = Boolean(p.deleted_at);
            const age = calculateAge(p.dob);

            const insuranceBadge = p.insurance_type === 'BPJS'
                ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-shield-heart text-[9px]"></i> BPJS</span>
                   <p class="text-[10px] text-slate-400 font-mono mt-0.5">${escapeHtml(p.insurance_number || '-')}</p>`
                : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-money-bill text-[9px]"></i> UMUM</span>`;

            const statusBadge = isInactive
                ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200"><i class="fa-solid fa-circle text-[6px]"></i> Nonaktif</span>`
                : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-circle text-[6px]"></i> Aktif</span>`;

            html += `
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3.5 px-4 text-center font-bold text-xs text-slate-400">
                        ${rowNumber}
                    </td>

                    <td class="py-3.5 px-4 font-mono font-bold text-xs text-teal-800">
                        <span class="bg-teal-50 border border-teal-200 px-2 py-0.5 rounded-md">
                            ${escapeHtml(p.no_rm)}
                        </span>
                    </td>

                    <td class="py-3.5 px-4 font-mono text-xs text-slate-600">
                        ${escapeHtml(p.nik)}
                    </td>

                    <td class="py-3.5 px-4">
                        <p class="font-bold text-slate-800 text-xs">${escapeHtml(p.name)}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            ${p.pob ? escapeHtml(p.pob) + ', ' : ''}${formatDateIndo(p.dob)} ${age ? '<span class="font-semibold text-slate-600">(' + age + ')</span>' : ''}
                        </p>
                    </td>

                    <td class="py-3.5 px-4 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="w-6 h-6 rounded-lg ${p.gender === 'L' ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700'} flex items-center justify-center font-bold text-[10px]">
                                ${p.gender === 'L' ? 'L' : 'P'}
                            </span>
                            <span class="font-medium text-slate-600">Gol. ${escapeHtml(p.blood_type || 'UNKNOWN')}</span>
                        </div>
                    </td>

                    <td class="py-3.5 px-4 text-xs font-medium text-slate-600">
                        ${escapeHtml(p.phone || '-')}
                    </td>

                    <td class="py-3.5 px-4">
                        ${insuranceBadge}
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        ${statusBadge}
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        <div class="inline-flex items-center gap-1.5">
                            <button type="button" onclick="openPatientDetailModal(${p.id})" title="Detail Pasien"
                                    class="w-7 h-7 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 flex items-center justify-center text-xs transition">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button type="button" onclick="openEditPatientModal(${p.id})" title="Edit Pasien"
                                    class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs transition">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            ${isInactive ? `
                                <button type="button" onclick="restorePatient(${p.id}, '${escapeHtml(p.name)}', '${escapeHtml(p.no_rm)}')" title="Aktifkan Kembali"
                                        class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center text-xs transition">
                                    <i class="fa-solid fa-user-check"></i>
                                </button>
                            ` : `
                                <button type="button" onclick="deactivatePatient(${p.id}, '${escapeHtml(p.name)}', '${escapeHtml(p.no_rm)}')" title="Nonaktifkan Pasien"
                                        class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 border border-amber-200 flex items-center justify-center text-xs transition">
                                    <i class="fa-solid fa-user-slash"></i>
                                </button>
                            `}

                            ${p.has_examination ? `
                                <button type="button" onclick="showCannotDeleteAlert('${escapeHtml(p.name)}', '${escapeHtml(p.no_rm)}')" title="Tidak dapat dihapus (sudah pernah menjalani pemeriksaan)"
                                        class="w-7 h-7 rounded-lg bg-slate-50 text-slate-300 border border-slate-200 cursor-not-allowed flex items-center justify-center text-xs transition">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            ` : `
                                <button type="button" onclick="deletePatientPermanent(${p.id}, '${escapeHtml(p.name)}', '${escapeHtml(p.no_rm)}')" title="Hapus Permanen Pasien"
                                        class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center text-xs transition">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            `}
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function renderPatientsPagination(p) {
        const info = document.getElementById('pagination-info');
        const btns = document.getElementById('pagination-buttons');

        if (!p || p.total === 0) {
            info.textContent = 'Menampilkan 0 data';
            btns.innerHTML = '';
            return;
        }

        info.textContent = `Menampilkan ${p.from} - ${p.to} dari total ${p.total} data pasien`;
        let btnHtml = '';

        btnHtml += `
            <button type="button" onclick="loadPatients(${p.current_page - 1})" ${p.current_page <= 1 ? 'disabled class="opacity-40 cursor-not-allowed px-2.5 py-1 rounded-lg border border-slate-200 text-slate-400"' : 'class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 transition"'}>
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </button>
        `;

        for (let i = 1; i <= p.last_page; i++) {
            if (i === 1 || i === p.last_page || (i >= p.current_page - 1 && i <= p.current_page + 1)) {
                btnHtml += `
                    <button type="button" onclick="loadPatients(${i})" class="px-2.5 py-1 rounded-lg text-xs font-semibold transition ${i === p.current_page ? 'bg-primary text-white shadow-sm' : 'border border-slate-200 hover:bg-slate-100 text-slate-700'}">
                        ${i}
                    </button>
                `;
            } else if (i === p.current_page - 2 || i === p.current_page + 2) {
                btnHtml += `<span class="px-1 text-slate-400">&hellip;</span>`;
            }
        }

        btnHtml += `
            <button type="button" onclick="loadPatients(${p.current_page + 1})" ${p.current_page >= p.last_page ? 'disabled class="opacity-40 cursor-not-allowed px-2.5 py-1 rounded-lg border border-slate-200 text-slate-400"' : 'class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 transition"'}>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        `;

        btns.innerHTML = btnHtml;
    }

    function handleSearchKey(e) {
        const val = document.getElementById('filter-search').value;
        document.getElementById('btn-clear-search').classList.toggle('hidden', val.length === 0);

        clearTimeout(patientsSearchTimeout);
        patientsSearchTimeout = setTimeout(() => {
            loadPatients(1);
        }, 300);
    }

    function clearSearchInput() {
        document.getElementById('filter-search').value = '';
        document.getElementById('btn-clear-search').classList.add('hidden');
        loadPatients(1);
    }

    function toggleBpjsInput(val) {
        const wrapper = document.getElementById('wrapper-bpjs-number');
        const input = document.getElementById('patient-insurance-number');
        if (val === 'BPJS') {
            wrapper.classList.remove('hidden');
            input.required = true;
        } else {
            wrapper.classList.add('hidden');
            input.required = false;
            input.value = '';
        }
    }

    function clearFormErrors() {
        document.querySelectorAll('[id^="err-"]').forEach(el => {
            el.textContent = '';
            el.classList.add('hidden');
        });
    }

    function openCreatePatientModal() {
        document.getElementById('form-patient').reset();
        document.getElementById('form-patient-id').value = '';
        document.getElementById('patient-no-rm').value = nextNoRmGlobal;
        document.getElementById('modal-patient-title').textContent = 'Tambah Pasien Baru';
        document.getElementById('btn-submit-patient-label').textContent = 'Simpan Data Pasien';
        toggleBpjsInput('UMUM');
        clearFormErrors();
        document.getElementById('modal-patient').classList.remove('hidden');
    }

    async function openEditPatientModal(id) {
        document.getElementById('form-patient').reset();
        document.getElementById('form-patient-id').value = id;
        document.getElementById('modal-patient-title').textContent = 'Edit Data Pasien';
        document.getElementById('btn-submit-patient-label').textContent = 'Perbarui Data Pasien';
        clearFormErrors();

        try {
            const res = await fetch(`<?= base_url('patients/show') ?>/${id}`);
            const result = await res.json();

            if (!result.status || !result.data) {
                throw new Error(result.message || 'Gagal memuat data pasien.');
            }

            const p = result.data;
            document.getElementById('patient-no-rm').value = p.no_rm;
            document.getElementById('patient-nik').value = p.nik;
            document.getElementById('patient-name').value = p.name;
            document.getElementById('patient-gender').value = p.gender;
            document.getElementById('patient-blood-type').value = p.blood_type || 'UNKNOWN';
            document.getElementById('patient-pob').value = p.pob || '';
            document.getElementById('patient-dob').value = p.dob || '';
            document.getElementById('patient-phone').value = p.phone || '';
            document.getElementById('patient-insurance-type').value = p.insurance_type || 'UMUM';
            document.getElementById('patient-insurance-number').value = p.insurance_number || '';
            document.getElementById('patient-address').value = p.address || '';

            toggleBpjsInput(p.insurance_type);
            document.getElementById('modal-patient').classList.remove('hidden');

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message,
            });
        }
    }

    function closePatientModal() {
        document.getElementById('modal-patient').classList.add('hidden');
    }

    async function handlePatientSubmit(e) {
        e.preventDefault();
        clearFormErrors();

        const id = document.getElementById('form-patient-id').value;
        const form = document.getElementById('form-patient');
        const submitBtn = document.getElementById('btn-submit-patient');
        const formData = new FormData(form);

        const url = id ? `<?= base_url('patients/update') ?>/${id}` : `<?= base_url('patients/store') ?>`;

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...`;

        try {
            const res = await fetch(url, {
                method: 'POST',
                body: formData,
            });
            const result = await res.json();

            if (!result.status) {
                if (result.errors) {
                    Object.entries(result.errors).forEach(([field, msg]) => {
                        const errEl = document.getElementById(`err-${field}`);
                        if (errEl) {
                            errEl.textContent = msg;
                            errEl.classList.remove('hidden');
                        }
                    });
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: result.message || 'Periksa kembali isian form Anda.',
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

            closePatientModal();
            loadPatients(currentPatientsPage);

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Sistem',
                text: err.message,
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fa-solid fa-check"></i> <span id="btn-submit-patient-label">${id ? 'Perbarui Data Pasien' : 'Simpan Data Pasien'}</span>`;
        }
    }

    async function openPatientDetailModal(id) {
        const modal = document.getElementById('modal-patient-detail');
        const container = document.getElementById('patient-detail-content');

        container.innerHTML = `
            <div class="py-10 text-center text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                <p>Memuat profil pasien...</p>
            </div>
        `;
        modal.classList.remove('hidden');

        try {
            const res = await fetch(`<?= base_url('patients/show') ?>/${id}`);
            const result = await res.json();

            if (!result.status || !result.data) {
                throw new Error(result.message || 'Gagal memuat profil pasien.');
            }

            const p = result.data;
            const age = calculateAge(p.dob);

            container.innerHTML = `
                <div class="p-4 bg-teal-50 border border-teal-200 rounded-2xl flex items-center justify-between">
                    <div>
                        <span class="font-mono font-bold text-xs bg-teal-200/60 text-teal-800 px-2 py-0.5 rounded">${escapeHtml(p.no_rm)}</span>
                        <h4 class="text-base font-bold text-teal-950 mt-1">${escapeHtml(p.name)}</h4>
                        <p class="text-xs text-slate-600 font-mono">NIK: ${escapeHtml(p.nik)}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold ${p.deleted_at ? 'bg-slate-200 text-slate-700' : 'bg-emerald-100 text-emerald-800'}">
                        ${p.deleted_at ? 'Nonaktif' : 'Aktif Terdaftar'}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Jenis Kelamin / Gol. Darah</span>
                        <span class="font-semibold text-slate-800">${p.gender === 'L' ? 'Laki-Laki' : 'Perempuan'} / Gol. ${escapeHtml(p.blood_type || 'UNKNOWN')}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Tempat, Tanggal Lahir</span>
                        <span class="font-semibold text-slate-800">${p.pob ? escapeHtml(p.pob) + ', ' : ''}${formatDateIndo(p.dob)} ${age ? '(' + age + ')' : ''}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Nomor Telepon</span>
                        <span class="font-medium text-slate-800">${escapeHtml(p.phone || '-')}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Penjamin Asuransi</span>
                        <span class="font-bold ${p.insurance_type === 'BPJS' ? 'text-blue-700' : 'text-emerald-700'}">${escapeHtml(p.insurance_type)}</span>
                        ${p.insurance_number ? `<p class="text-slate-500 font-mono text-[10px]">${escapeHtml(p.insurance_number)}</p>` : ''}
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-0.5">Alamat Domisili</span>
                    <p class="text-slate-700 leading-relaxed">${escapeHtml(p.address || 'Alamat belum dilengkapi')}</p>
                </div>
            `;

        } catch (err) {
            container.innerHTML = `<p class="py-8 text-center text-rose-500">${escapeHtml(err.message)}</p>`;
        }
    }

    function closePatientDetailModal() {
        document.getElementById('modal-patient-detail').classList.add('hidden');
    }

    function deactivatePatient(id, name, rm) {
        Swal.fire({
            title: 'Nonaktifkan Pasien?',
            html: `Apakah Anda yakin ingin menonaktifkan pasien <strong>${escapeHtml(name)}</strong> (${escapeHtml(rm)})? Data rekam medis tetap tersimpan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Nonaktifkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch(`<?= base_url('patients/delete') ?>/${id}`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const resJson = await res.json();

                    if (!resJson.status) {
                        throw new Error(resJson.message || 'Gagal menonaktifkan pasien.');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Dinonaktifkan',
                        text: resJson.message,
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    loadPatients(currentPatientsPage);
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

    function restorePatient(id, name, rm) {
        Swal.fire({
            title: 'Aktifkan Kembali Pasien?',
            html: `Aktifkan kembali pasien <strong>${escapeHtml(name)}</strong> (${escapeHtml(rm)}) agar dapat melakukan pendaftaran berobat?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Aktifkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#04BBA6',
            cancelButtonColor: '#64748b',
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch(`<?= base_url('patients/restore') ?>/${id}`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const resJson = await res.json();

                    if (!resJson.status) {
                        throw new Error(resJson.message || 'Gagal mengaktifkan pasien.');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Diaktifkan Kembali',
                        text: resJson.message,
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    loadPatients(currentPatientsPage);
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

    function showCannotDeleteAlert(name, rm) {
        Swal.fire({
            icon: 'info',
            title: 'Tidak Dapat Dihapus Permanen',
            html: `Pasien <strong>${escapeHtml(name)}</strong> (${escapeHtml(rm)}) sudah memiliki riwayat pemeriksaan atau kunjungan.<br><br><span class="text-xs text-slate-500">Gunakan tombol <strong>Nonaktifkan</strong> jika pasien sudah tidak aktif berobat.</span>`,
            confirmButtonColor: '#04BBA6',
        });
    }

    function deletePatientPermanent(id, name, rm) {
        Swal.fire({
            title: 'Hapus Permanen Data Pasien?',
            html: `Data pasien <strong>${escapeHtml(name)}</strong> (${escapeHtml(rm)}) akan <strong>dihapus secara permanen</strong> dari sistem dan tidak dapat dipulihkan kembali.<br><br><span class="text-xs text-rose-600 font-medium">*Hanya berlaku bagi pasien yang belum pernah menjalani pemeriksaan.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus Permanen',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const csrfInput = document.querySelector('#form-patient input[name="<?= csrf_token() ?>"]');
                    const formData = new FormData();
                    if (csrfInput) {
                        formData.append(csrfInput.name, csrfInput.value);
                    }

                    const res = await fetch(`<?= base_url('patients/permanent-delete') ?>/${id}`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData,
                    });
                    const resJson = await res.json();

                    // Perbarui token CSRF jika tersedia dari respons server
                    if (resJson.csrf && csrfInput) {
                        csrfInput.value = resJson.csrf.hash;
                    }

                    if (!resJson.status) {
                        throw new Error(resJson.message || 'Gagal menghapus data pasien.');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus Permanen',
                        text: resJson.message,
                        timer: 1800,
                        showConfirmButton: false,
                    });

                    loadPatients(currentPatientsPage);
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Penghapusan Ditolak',
                        text: err.message,
                        confirmButtonColor: '#04BBA6',
                    });
                }
            }
        });
    }

    // Helper formatting
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDateIndo(dateStr) {
        if (!dateStr) return '-';
        const parts = dateStr.split(' ')[0].split('-');
        if (parts.length !== 3) return dateStr;
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const mIdx = parseInt(parts[1], 10) - 1;
        return `${parts[2]} ${months[mIdx] || parts[1]} ${parts[0]}`;
    }

    function calculateAge(dobStr) {
        if (!dobStr) return '';
        const birthDate = new Date(dobStr);
        if (isNaN(birthDate.getTime())) return '';
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age >= 0 ? `${age} thn` : '';
    }
</script>

<?= $this->endSection() ?>

<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="tm-registration-wrapper space-y-6">

    <!-- ══════════════════════════════════════════════
         HEADER HALAMAN & STATISTIK
    ══════════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Pendaftaran Rawat Jalan</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola pendaftaran antrean pasien, verifikasi dokter dan poliklinik, serta status kunjungan.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="loadRegistrations()" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-semibold rounded-xl shadow-sm transition">
                <i class="fa-solid fa-arrows-rotate text-slate-400"></i>
                <span>Segarkan</span>
            </button>
            <button type="button" onclick="openNewRegistrationModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-primaryHover text-white text-sm font-semibold rounded-xl shadow-md shadow-primary/20 transition">
                <i class="fa-solid fa-plus"></i>
                <span>Daftarkan Pasien Baru</span>
            </button>
        </div>
    </div>

    <!-- Ringkasan Statistik Hari Ini -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Kunjungan</p>
                <p class="text-2xl font-bold text-slate-800" id="stat-total"><?= number_format($summary['total'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-amber-700 uppercase tracking-wider">Menunggu</p>
                <p class="text-2xl font-bold text-amber-700" id="stat-waiting"><?= number_format($summary['waiting'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-stethoscope"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-blue-700 uppercase tracking-wider">Diperiksa</p>
                <p class="text-2xl font-bold text-blue-700" id="stat-examining"><?= number_format($summary['examining'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-emerald-700 uppercase tracking-wider">Selesai</p>
                <p class="text-2xl font-bold text-emerald-700" id="stat-done"><?= number_format($summary['done'] ?? 0) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5 col-span-2 md:col-span-1">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0 text-lg">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-rose-700 uppercase tracking-wider">Dibatalkan</p>
                <p class="text-2xl font-bold text-rose-700" id="stat-canceled"><?= number_format($summary['canceled'] ?? 0) ?></p>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         TOOLBAR FILTER & PENCARIAN
    ══════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
            
            <!-- Pencarian kata kunci -->
            <div class="lg:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" id="filter-search" placeholder="Cari No. Reg / Pasien / No. RM / Dokter..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                       onkeyup="handleSearchKey(event)">
            </div>

            <!-- Filter Tanggal Kunjungan -->
            <div class="lg:col-span-2">
                <input type="date" id="filter-date" value="<?= esc($today) ?>"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                       onchange="loadRegistrations(1)">
            </div>

            <!-- Filter Poliklinik -->
            <div class="lg:col-span-2">
                <select id="filter-polyclinic" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadRegistrations(1)">
                    <option value="">Semua Poliklinik</option>
                    <?php foreach ($polyclinics as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Status Pendaftaran -->
            <div class="lg:col-span-2">
                <select id="filter-status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadRegistrations(1)">
                    <option value="">Semua Status</option>
                    <option value="MENUNGGU">Menunggu</option>
                    <option value="DIPERIKSA">Sedang Diperiksa</option>
                    <option value="SELESAI">Selesai</option>
                    <option value="BATAL">Batal</option>
                </select>
            </div>

            <!-- Filter Pembayaran & Per Page -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <select id="filter-payment" class="w-1/2 px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadRegistrations(1)">
                    <option value="">Semua Bayar</option>
                    <option value="UMUM">UMUM</option>
                    <option value="BPJS">BPJS</option>
                </select>
                <select id="filter-per-page" class="w-1/2 px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                        onchange="loadRegistrations(1)">
                    <option value="10">10 / hal</option>
                    <option value="20">20 / hal</option>
                    <option value="30">30 / hal</option>
                    <option value="50">50 / hal</option>
                </select>
            </div>

        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         TABEL PENDAFTARAN RAWAT JALAN
    ══════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-16">Antrean</th>
                        <th class="py-3.5 px-4">No. Pendaftaran & Tgl</th>
                        <th class="py-3.5 px-4">Pasien</th>
                        <th class="py-3.5 px-4">Poliklinik & Dokter</th>
                        <th class="py-3.5 px-4">Pembayaran</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody id="registrations-tbody" class="divide-y divide-slate-100 text-slate-700">
                    <!-- Data dimuat via AJAX -->
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                            <p class="text-sm">Memuat data pendaftaran...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="px-5 py-3.5 bg-slate-50/60 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div id="pagination-info">
                Menampilkan data...
            </div>
            <div class="flex items-center gap-1.5" id="pagination-buttons">
                <!-- Tombol paginasi di-generate otomatis -->
            </div>
        </div>

    </div>

</div>

<!-- ══════════════════════════════════════════════
     MODAL: DAFTARKAN PASIEN BARU
══════════════════════════════════════════════ -->
<div id="modal-registration" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-2xl overflow-hidden transform transition-all animate-in fade-in zoom-in duration-200">
        
        <!-- Modal Header -->
        <div class="px-6 py-5 bg-gradient-to-r from-sidebarBg to-darkteal text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/20 border border-primary/30 flex items-center justify-center text-primary">
                    <i class="fa-solid fa-notes-medical text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white leading-tight">Form Pendaftaran Rawat Jalan</h3>
                    <p class="text-xs text-white/60">Pastikan pasien terdaftar dan dokter sesuai dengan poliklinik</p>
                </div>
            </div>
            <button type="button" onclick="closeRegistrationModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form id="form-registration" onsubmit="handleRegistrationSubmit(event)" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <!-- Baris 1: Info No. Registrasi & Tanggal Kunjungan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">No. Pendaftaran (Otomatis)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-hashtag text-xs"></i>
                        </span>
                        <input type="text" id="form-reg-number" value="<?= esc($next_reg_number) ?>" readonly
                               class="w-full pl-8 pr-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono font-semibold text-slate-700 cursor-not-allowed">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Kunjungan <span class="text-rose-500">*</span></label>
                    <input type="date" name="registration_date" id="form-reg-date" value="<?= esc($today) ?>" required
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                           onchange="updateGeneratedRegNumber(this.value)">
                </div>
            </div>

            <!-- Baris 2: Pemilihan Pasien (Harus Sudah Terdaftar) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-semibold text-slate-600">Pilih Pasien Terdaftar <span class="text-rose-500">*</span></label>
                    <a href="<?= base_url('patients') ?>" target="_blank" class="text-xs text-primary hover:text-primaryHover font-medium inline-flex items-center gap-1">
                        <i class="fa-solid fa-user-plus text-[10px]"></i> Pasien Baru Belum Ada? Daftarkan Pasien
                    </a>
                </div>

                <!-- Input Pencarian Pasien Real-Time -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fa-solid fa-user-tag text-xs"></i>
                    </span>
                    <input type="text" id="patient-search-input" placeholder="Ketik Nama Pasien / No. Rekam Medis / NIK..." autocomplete="off"
                           class="w-full pl-9 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                           oninput="debouncePatientSearch(this.value)" onfocus="showPatientDropdown()">
                    <button type="button" id="btn-clear-patient" onclick="clearSelectedPatient()" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                    <input type="hidden" name="patient_id" id="form-patient-id" required>

                    <!-- Dropdown Hasil Pencarian Pasien -->
                    <div id="patient-results-dropdown" class="hidden absolute z-30 left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-xl divide-y divide-slate-100">
                        <div class="p-3 text-xs text-slate-400 text-center">Ketik minimal 2 karakter untuk mencari...</div>
                    </div>
                </div>

                <!-- Kartu Preview Pasien Terpilih -->
                <div id="selected-patient-card" class="hidden mt-2 p-3 bg-teal-50/70 border border-teal-200/60 rounded-xl flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-primary text-white flex items-center justify-center font-bold">
                            <i class="fa-solid fa-hospital-user"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800" id="preview-patient-name">-</p>
                            <p class="text-slate-500 font-mono">No. RM: <span id="preview-patient-norm">-</span> &bull; NIK: <span id="preview-patient-nik">-</span></p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full font-semibold text-[10px]" id="preview-patient-insurance">-</span>
                </div>
            </div>

            <!-- Baris 3: Pemilihan Poliklinik & Dokter (Dependent Filter) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Poliklinik -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Poliklinik Tujuan <span class="text-rose-500">*</span></label>
                    <select name="polyclinic_id" id="form-poly-id" required
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                            onchange="handlePolyclinicChange(this.value)">
                        <option value="">-- Pilih Poliklinik --</option>
                        <?php foreach ($polyclinics as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?> (<?= esc($p['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Dokter (Terikat ke Poliklinik) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Dokter Pemeriksa <span class="text-rose-500">*</span></label>
                    <select name="doctor_id" id="form-doctor-id" required disabled
                            class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition cursor-not-allowed">
                        <option value="">-- Pilih Poliklinik Dahulu --</option>
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1" id="doctor-hint">Pilihan dokter disesuaikan otomatis dengan poliklinik terpilih.</p>
                </div>

            </div>

            <!-- Baris 4: Jenis Pembayaran -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jenis Pembayaran <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition">
                        <input type="radio" name="payment_type" value="UMUM" checked class="text-primary focus:ring-primary">
                        <span class="text-xs font-semibold text-slate-700"><i class="fa-solid fa-money-bill-wave text-emerald-600 mr-1"></i> UMUM / Mandiri</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition">
                        <input type="radio" name="payment_type" value="BPJS" class="text-primary focus:ring-primary">
                        <span class="text-xs font-semibold text-slate-700"><i class="fa-solid fa-shield-heart text-blue-600 mr-1"></i> BPJS Kesehatan</span>
                    </label>
                </div>
            </div>

            <!-- Baris 5: Keluhan Utama Pasien -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Keluhan Utama Pasien <span class="text-rose-500">*</span></label>
                <textarea name="complaint" id="form-complaint" rows="3" required placeholder="Contoh: Sakit kepala berdenyut sejak 3 hari lalu, demam naik turun..."
                          class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary transition"></textarea>
            </div>

            <!-- Modal Footer Buttons -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeRegistrationModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" id="btn-submit-registration" class="px-5 py-2 bg-primary hover:bg-primaryHover text-white text-xs font-semibold rounded-xl shadow-md shadow-primary/20 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-check"></i>
                    <span>Simpan Pendaftaran</span>
                </button>
            </div>

        </form>

    </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL: DETAIL PENDAFTARAN LENGKAP
══════════════════════════════════════════════ -->
<div id="modal-detail" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-xl overflow-hidden animate-in fade-in zoom-in duration-200">
        
        <div class="px-6 py-4 bg-slate-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-id-card text-primary text-lg"></i>
                <h3 class="text-base font-bold text-white">Detail Kunjungan Pasien</h3>
            </div>
            <button type="button" onclick="closeDetailModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs" id="detail-content">
            <!-- Konten dimuat lewat JS -->
        </div>

        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <div id="detail-action-buttons" class="flex items-center gap-2"></div>
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition">
                Tutup
            </button>
        </div>

    </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL CEPAT: INPUT TANDA-TANDA VITAL
══════════════════════════════════════════════ -->
<div id="modal-vital-signs" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in duration-200">
        
        <!-- Header -->
        <div class="px-6 py-4.5 bg-gradient-to-r from-teal-800 to-emerald-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center text-accent text-lg">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Pencatatan Tanda-Tanda Vital & Pemeriksaan</h3>
                    <p class="text-xs text-white/70" id="vs-modal-subtitle">Pasien: -</p>
                </div>
            </div>
            <button type="button" onclick="closeVitalSignsModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Form Tanda Vital -->
        <form id="form-vital-signs" onsubmit="handleVitalSignsSubmit(event)" class="p-6 space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" name="registration_id" id="vs-registration-id">

            <!-- Identitas Pasien Ringkas -->
            <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Nama Pasien</span>
                    <span class="font-bold text-slate-800" id="vs-patient-name">-</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">No. RM / NIK</span>
                    <span class="font-mono text-slate-700" id="vs-patient-rm">-</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Poliklinik</span>
                    <span class="font-semibold text-teal-700" id="vs-polyclinic">-</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Dokter Pemeriksa</span>
                    <span class="font-semibold text-slate-800" id="vs-doctor">-</span>
                </div>
            </div>

            <!-- 1. Grid Input Tanda-Tanda Vital (Vital Signs) -->
            <div>
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5 flex items-center gap-2">
                    <i class="fa-solid fa-gauge text-teal-600"></i>
                    Informasi Tanda-Tanda Vital Pasien
                </h4>
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    
                    <!-- Sistolik -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl">
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">TD Sistolik</label>
                        <div class="flex items-center gap-1.5">
                            <input type="number" name="systolic_bp" id="vs-systolic" placeholder="120" min="40" max="300"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-1 focus:ring-teal-500">
                            <span class="text-[11px] text-slate-400 font-medium">mmHg</span>
                        </div>
                    </div>

                    <!-- Diastolik -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl">
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">TD Diastolik</label>
                        <div class="flex items-center gap-1.5">
                            <input type="number" name="diastolic_bp" id="vs-diastolic" placeholder="80" min="30" max="200"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-1 focus:ring-teal-500">
                            <span class="text-[11px] text-slate-400 font-medium">mmHg</span>
                        </div>
                    </div>

                    <!-- Denyut Nadi -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl">
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Denyut Nadi</label>
                        <div class="flex items-center gap-1.5">
                            <input type="number" name="pulse" id="vs-pulse" placeholder="80" min="30" max="250"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-1 focus:ring-teal-500">
                            <span class="text-[11px] text-slate-400 font-medium">x/mnt</span>
                        </div>
                    </div>

                    <!-- Suhu Tubuh -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl">
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Suhu Tubuh</label>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="0.1" name="temperature" id="vs-temp" placeholder="36.5" min="30" max="45"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-1 focus:ring-teal-500">
                            <span class="text-[11px] text-slate-400 font-medium">&deg;C</span>
                        </div>
                    </div>

                    <!-- Frekuensi Pernapasan -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl">
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Frekuensi Napas</label>
                        <div class="flex items-center gap-1.5">
                            <input type="number" name="respiratory_rate" id="vs-resp" placeholder="18" min="5" max="80"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-1 focus:ring-teal-500">
                            <span class="text-[11px] text-slate-400 font-medium">x/mnt</span>
                        </div>
                    </div>

                    <!-- Berat Badan -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl">
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Berat Badan</label>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="0.1" name="weight" id="vs-weight" placeholder="60.0" min="1" max="500"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-1 focus:ring-teal-500">
                            <span class="text-[11px] text-slate-400 font-medium">kg</span>
                        </div>
                    </div>

                    <!-- Tinggi Badan -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl">
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tinggi Badan</label>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="0.1" name="height" id="vs-height" placeholder="165.0" min="10" max="300"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-1 focus:ring-teal-500">
                            <span class="text-[11px] text-slate-400 font-medium">cm</span>
                        </div>
                    </div>

                    <!-- Waktu Pemeriksaan -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl">
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Waktu Periksa</label>
                        <input type="text" name="examination_time" id="vs-exam-time" readonly
                               class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-[11px] font-mono text-slate-600">
                    </div>

                </div>
            </div>

            <!-- 2. Catatan Medis & Keluhan (Subjective, Objective, Assessment, Plan) -->
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keluhan / Anamnesis (Subjective) <span class="text-rose-500">*</span></label>
                    <textarea name="subjective" id="vs-subjective" rows="2" required placeholder="Keluhan utama dan riwayat penyakit saat ini..."
                              class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hasil Pemeriksaan Fisik (Objective)</label>
                        <textarea name="objective" id="vs-objective" rows="2" placeholder="Keadaan umum, temuan fisik..."
                                  class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Diagnosis / Assessment (ICD-10)</label>
                        <textarea name="assessment" id="vs-assessment" rows="2" placeholder="Diagnosis medis pasien..."
                                  class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"></textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rencana Terapi / Resep Obat (Plan)</label>
                    <textarea name="plan" id="vs-plan" rows="2" placeholder="Resep obat, tindakan medis, edukasi pasien..."
                              class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"></textarea>
                </div>
            </div>

            <!-- 3. Status Akhir Kunjungan & Petugas Pemeriksa -->
            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div>
                    <span class="text-slate-500 font-medium">Petugas Pemeriksa:</span>
                    <span class="font-bold text-slate-800 ml-1"><?= esc(session('fullname')) ?></span>
                    <span class="text-slate-400 text-[10px] ml-1">(<?= esc(session('role_label')) ?>)</span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-700">Tindak Lanjut:</span>
                    <label class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white border border-slate-200 rounded-lg cursor-pointer">
                        <input type="radio" name="final_status" value="DIPERIKSA" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-blue-700 font-semibold text-xs">Sedang Diperiksa</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white border border-slate-200 rounded-lg cursor-pointer">
                        <input type="radio" name="final_status" value="SELESAI" checked class="text-emerald-600 focus:ring-emerald-500">
                        <span class="text-emerald-700 font-semibold text-xs">Selesai Pemeriksaan</span>
                    </label>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-2 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeVitalSignsModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" id="btn-save-vital-signs" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-emerald-600/20 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Rekam Medis & Tanda Vital</span>
                </button>
            </div>

        </form>

    </div>
</div>

<!-- ══════════════════════════════════════════════
     SCRIPTS & CLIENT-SIDE LOGIC
══════════════════════════════════════════════ -->
<script>
    // State lokal halaman pendaftaran
    let currentPage = 1;
    let searchDebounceTimeout = null;
    let patientSearchDebounceTimeout = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadRegistrations(1);

        // Tutup dropdown pencarian pasien saat klik di luar
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('patient-results-dropdown');
            const input = document.getElementById('patient-search-input');
            if (dropdown && !dropdown.contains(e.target) && e.target !== input) {
                dropdown.classList.add('hidden');
            }
        });
    });

    /**
     * Memuat data pendaftaran dari server via AJAX
     */
    async function loadRegistrations(page = 1) {
        currentPage = page;
        const tbody = document.getElementById('registrations-tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="py-12 text-center text-slate-400">
                    <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                    <p class="text-sm">Memuat data pendaftaran...</p>
                </td>
            </tr>
        `;

        const search       = document.getElementById('filter-search').value.trim();
        const date         = document.getElementById('filter-date').value;
        const polyclinicId = document.getElementById('filter-polyclinic').value;
        const status       = document.getElementById('filter-status').value;
        const paymentType  = document.getElementById('filter-payment').value;
        const perPage      = document.getElementById('filter-per-page').value;

        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: search,
            date: date,
            polyclinic_id: polyclinicId,
            status: status,
            payment_type: paymentType,
        });

        try {
            const res = await fetch(`<?= base_url('registrations/list') ?>?${params.toString()}`);
            const result = await res.json();

            if (!result.status) {
                throw new Error(result.message || 'Gagal memuat data pendaftaran.');
            }

            // Perbarui statistik
            if (result.summary) {
                document.getElementById('stat-total').textContent = Number(result.summary.total || 0).toLocaleString();
                document.getElementById('stat-waiting').textContent = Number(result.summary.waiting || 0).toLocaleString();
                document.getElementById('stat-examining').textContent = Number(result.summary.examining || 0).toLocaleString();
                document.getElementById('stat-done').textContent = Number(result.summary.done || 0).toLocaleString();
                document.getElementById('stat-canceled').textContent = Number(result.summary.canceled || 0).toLocaleString();
            }

            // Render baris tabel
            renderTableRows(result.data);
            renderPagination(result.pagination);

        } catch (err) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="py-10 text-center text-rose-500">
                        <i class="fa-solid fa-circle-exclamation text-2xl mb-2"></i>
                        <p class="text-sm font-semibold">${escapeHtml(err.message)}</p>
                        <button type="button" onclick="loadRegistrations(${page})" class="mt-2 text-xs text-primary underline">Coba lagi</button>
                    </td>
                </tr>
            `;
        }
    }

    /**
     * Render daftar pendaftaran ke baris tabel HTML
     */
    function renderTableRows(items) {
        const tbody = document.getElementById('registrations-tbody');

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="py-16 text-center text-slate-400">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 mb-3 text-2xl">
                            <i class="fa-solid fa-clipboard-question"></i>
                        </div>
                        <p class="text-base font-semibold text-slate-700">Tidak ada data pendaftaran ditemukan</p>
                        <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter tanggal atau daftarkan pasien baru.</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        items.forEach((item) => {
            // Badge Status
            let statusBadge = '';
            if (item.status === 'MENUNGGU') {
                statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="fa-solid fa-clock text-[9px]"></i> Menunggu
                               </span>`;
            } else if (item.status === 'DIPERIKSA') {
                statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                <i class="fa-solid fa-stethoscope text-[9px]"></i> Diperiksa
                               </span>`;
            } else if (item.status === 'SELESAI') {
                statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fa-solid fa-circle-check text-[9px]"></i> Selesai
                               </span>`;
            } else {
                statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                <i class="fa-solid fa-ban text-[9px]"></i> Batal
                               </span>`;
            }

            // Badge Pembayaran
            const paymentBadge = item.payment_type === 'BPJS'
                ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-shield-heart text-[9px]"></i> BPJS</span>`
                : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-money-bill text-[9px]"></i> UMUM</span>`;

            // Tanggal formatted
            const regDateFormatted = formatDateIndo(item.registration_date);

            html += `
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <!-- Nomor Antrean -->
                    <td class="py-3.5 px-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-teal-50 text-teal-700 border border-teal-200/80 font-bold text-sm">
                            ${item.queue_number}
                        </span>
                    </td>

                    <!-- Nomor Pendaftaran & Tanggal -->
                    <td class="py-3.5 px-4">
                        <p class="font-mono font-bold text-slate-800 text-xs">${escapeHtml(item.registration_number)}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                            <i class="fa-regular fa-calendar text-[10px]"></i> ${regDateFormatted}
                        </p>
                    </td>

                    <!-- Identitas Pasien -->
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg ${item.patient_gender === 'L' ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700'} flex items-center justify-center font-bold text-xs flex-shrink-0">
                                ${item.patient_gender === 'L' ? 'L' : 'P'}
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-xs">${escapeHtml(item.patient_name)}</p>
                                <p class="text-[11px] text-slate-400 font-mono">${escapeHtml(item.no_rm)} &bull; ${escapeHtml(item.patient_nik || '-')}</p>
                            </div>
                        </div>
                    </td>

                    <!-- Poliklinik & Dokter -->
                    <td class="py-3.5 px-4">
                        <p class="font-semibold text-slate-800 text-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-hospital text-teal-600 text-[10px]"></i>
                            ${escapeHtml(item.polyclinic_name)}
                        </p>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            ${escapeHtml(item.doctor_name)} <span class="text-slate-400">(${escapeHtml(item.doctor_specialization || 'Umum')})</span>
                        </p>
                    </td>

                    <!-- Jenis Pembayaran -->
                    <td class="py-3.5 px-4">
                        ${paymentBadge}
                    </td>

                    <!-- Status Kunjungan -->
                    <td class="py-3.5 px-4 text-center">
                        ${statusBadge}
                    </td>

                    <!-- Kolom Aksi -->
                    <td class="py-3.5 px-4 text-center">
                        <div class="inline-flex items-center gap-1">
                            <!-- Detail -->
                            <button type="button" onclick="openDetailModal(${item.id})" title="Lihat Detail Pendaftaran"
                                    class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs transition">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <!-- Pemeriksaan / TTV -->
                            <button type="button" onclick="openVitalSignsModal(${item.id})" title="Input / Lihat Tanda Vital & Pemeriksaan"
                                    class="w-7 h-7 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 flex items-center justify-center text-xs transition">
                                <i class="fa-solid fa-heart-pulse"></i>
                            </button>

                            <!-- Ubah Status Cepat -->
                            <button type="button" onclick="promptChangeStatus(${item.id}, '${item.status}')" title="Ubah Status Kunjungan"
                                    class="w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 flex items-center justify-center text-xs transition">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <!-- Batalkan jika bukan selesai atau sudah batal -->
                            ${item.status !== 'BATAL' && item.status !== 'SELESAI' ? `
                                <button type="button" onclick="confirmCancelRegistration(${item.id}, '${escapeHtml(item.registration_number)}')" title="Batalkan Pendaftaran"
                                        class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center text-xs transition">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    /**
     * Render komponen navigasi paginasi
     */
    function renderPagination(p) {
        const info = document.getElementById('pagination-info');
        const btns = document.getElementById('pagination-buttons');

        if (!p || p.total === 0) {
            info.textContent = 'Menampilkan 0 dari 0 data';
            btns.innerHTML = '';
            return;
        }

        info.textContent = `Menampilkan ${p.from} - ${p.to} dari total ${p.total} pendaftaran`;

        let btnHtml = '';

        // Tombol Sebelumnya
        btnHtml += `
            <button type="button" onclick="loadRegistrations(${p.current_page - 1})" ${p.current_page <= 1 ? 'disabled class="opacity-40 cursor-not-allowed px-2.5 py-1 rounded-lg border border-slate-200 text-slate-400"' : 'class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 transition"'}>
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </button>
        `;

        // Nomor Halaman Sederhana
        for (let i = 1; i <= p.last_page; i++) {
            if (i === 1 || i === p.last_page || (i >= p.current_page - 1 && i <= p.current_page + 1)) {
                btnHtml += `
                    <button type="button" onclick="loadRegistrations(${i})" class="px-2.5 py-1 rounded-lg text-xs font-semibold transition ${i === p.current_page ? 'bg-primary text-white shadow-sm' : 'border border-slate-200 hover:bg-slate-100 text-slate-700'}">
                        ${i}
                    </button>
                `;
            } else if (i === p.current_page - 2 || i === p.current_page + 2) {
                btnHtml += `<span class="px-1 text-slate-400">&hellip;</span>`;
            }
        }

        // Tombol Selanjutnya
        btnHtml += `
            <button type="button" onclick="loadRegistrations(${p.current_page + 1})" ${p.current_page >= p.last_page ? 'disabled class="opacity-40 cursor-not-allowed px-2.5 py-1 rounded-lg border border-slate-200 text-slate-400"' : 'class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 transition"'}>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        `;

        btns.innerHTML = btnHtml;
    }

    /**
     * Handle input search dengan debounce
     */
    function handleSearchKey(e) {
        clearTimeout(searchDebounceTimeout);
        searchDebounceTimeout = setTimeout(() => {
            loadRegistrations(1);
        }, 400);
    }

    // ══════════════════════════════════════════════════════
    // LOGIKA MODAL TAMBAH PENDAFTARAN
    // ══════════════════════════════════════════════════════

    function openNewRegistrationModal() {
        document.getElementById('form-registration').reset();
        clearSelectedPatient();
        document.getElementById('form-reg-date').value = document.getElementById('filter-date').value || '<?= esc($today) ?>';
        
        // Reset dropdown dokter
        const doctorSelect = document.getElementById('form-doctor-id');
        doctorSelect.innerHTML = '<option value="">-- Pilih Poliklinik Dahulu --</option>';
        doctorSelect.disabled = true;
        doctorSelect.classList.add('bg-slate-100', 'cursor-not-allowed');

        document.getElementById('modal-registration').classList.remove('hidden');
    }

    function closeRegistrationModal() {
        document.getElementById('modal-registration').classList.add('hidden');
    }

    /**
     * Dependency Dropdown: Ketika poliklinik dipilih, ambil daftar dokter yang sesuai!
     */
    async function handlePolyclinicChange(polyclinicId) {
        const doctorSelect = document.getElementById('form-doctor-id');
        const hint = document.getElementById('doctor-hint');

        if (!polyclinicId) {
            doctorSelect.innerHTML = '<option value="">-- Pilih Poliklinik Dahulu --</option>';
            doctorSelect.disabled = true;
            doctorSelect.classList.add('bg-slate-100', 'cursor-not-allowed');
            hint.textContent = 'Pilihan dokter disesuaikan otomatis dengan poliklinik terpilih.';
            hint.className = 'text-[11px] text-slate-400 mt-1';
            return;
        }

        doctorSelect.disabled = true;
        doctorSelect.innerHTML = '<option value="">Memuat daftar dokter...</option>';

        try {
            const res = await fetch(`<?= base_url('registrations/doctors-by-poly') ?>/${polyclinicId}`);
            const result = await res.json();

            if (!result.status || !result.data || result.data.length === 0) {
                doctorSelect.innerHTML = '<option value="">-- Tidak ada dokter aktif di poli ini --</option>';
                hint.textContent = 'Perhatian: Tidak ada dokter bertugas aktif di poliklinik ini saat ini.';
                hint.className = 'text-[11px] text-rose-500 font-semibold mt-1';
                return;
            }

            let optHtml = '<option value="">-- Pilih Dokter Pemeriksa --</option>';
            result.data.forEach(d => {
                optHtml += `<option value="${d.id}">${escapeHtml(d.name)} (${escapeHtml(d.specialization || 'Dokter')})</option>`;
            });

            doctorSelect.innerHTML = optHtml;
            doctorSelect.disabled = false;
            doctorSelect.classList.remove('bg-slate-100', 'cursor-not-allowed');
            hint.textContent = `Tersedia ${result.data.length} dokter untuk poliklinik terpilih.`;
            hint.className = 'text-[11px] text-emerald-600 font-semibold mt-1';

        } catch (err) {
            doctorSelect.innerHTML = '<option value="">Gagal memuat dokter</option>';
            hint.textContent = 'Gagal memuat dokter: ' + err.message;
            hint.className = 'text-[11px] text-rose-500 mt-1';
        }
    }

    /**
     * Update perkiraan No. Pendaftaran saat tanggal kunjungan diganti
     */
    function updateGeneratedRegNumber(dateVal) {
        if (!dateVal) return;
        const cleanDate = dateVal.replaceAll('-', '');
        const currentField = document.getElementById('form-reg-number');
        currentField.value = `REG/${cleanDate}/...`;
    }

    /**
     * Pencarian pasien autocomplete live search
     */
    function debouncePatientSearch(query) {
        clearTimeout(patientSearchDebounceTimeout);
        if (query.trim().length < 2) {
            document.getElementById('patient-results-dropdown').classList.add('hidden');
            return;
        }

        patientSearchDebounceTimeout = setTimeout(async () => {
            const dropdown = document.getElementById('patient-results-dropdown');
            dropdown.classList.remove('hidden');
            dropdown.innerHTML = `<div class="p-3 text-xs text-slate-400 text-center"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Mencari pasien...</div>`;

            try {
                const res = await fetch(`<?= base_url('registrations/search-patients') ?>?q=${encodeURIComponent(query.trim())}`);
                const result = await res.json();

                if (!result.status || !result.data || result.data.length === 0) {
                    dropdown.innerHTML = `
                        <div class="p-4 text-center text-xs text-slate-500">
                            <p class="font-semibold">Pasien tidak ditemukan</p>
                            <a href="<?= base_url('patients') ?>" target="_blank" class="text-primary hover:underline mt-1 inline-block">
                                + Daftarkan Pasien Baru
                            </a>
                        </div>
                    `;
                    return;
                }

                let itemsHtml = '';
                result.data.forEach(p => {
                    itemsHtml += `
                        <div onclick='selectPatient(${JSON.stringify(p)})' class="p-3 hover:bg-teal-50/80 cursor-pointer transition flex items-center justify-between text-xs">
                            <div>
                                <p class="font-bold text-slate-800">${escapeHtml(p.name)}</p>
                                <p class="text-slate-500 font-mono text-[11px]">RM: ${escapeHtml(p.no_rm)} &bull; NIK: ${escapeHtml(p.nik || '-')}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold ${p.insurance_type === 'BPJS' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'}">
                                ${escapeHtml(p.insurance_type || 'UMUM')}
                            </span>
                        </div>
                    `;
                });
                dropdown.innerHTML = itemsHtml;

            } catch (err) {
                dropdown.innerHTML = `<div class="p-3 text-xs text-rose-500 text-center">Gagal memuat hasil.</div>`;
            }
        }, 300);
    }

    function showPatientDropdown() {
        const input = document.getElementById('patient-search-input');
        if (input.value.trim().length >= 2) {
            document.getElementById('patient-results-dropdown').classList.remove('hidden');
        }
    }

    function selectPatient(patient) {
        document.getElementById('form-patient-id').value = patient.id;
        document.getElementById('patient-search-input').value = `${patient.name} (${patient.no_rm})`;
        document.getElementById('patient-results-dropdown').classList.add('hidden');
        document.getElementById('btn-clear-patient').classList.remove('hidden');

        // Tampilkan preview
        document.getElementById('preview-patient-name').textContent = patient.name;
        document.getElementById('preview-patient-norm').textContent = patient.no_rm;
        document.getElementById('preview-patient-nik').textContent = patient.nik || '-';
        
        const insBadge = document.getElementById('preview-patient-insurance');
        insBadge.textContent = patient.insurance_type || 'UMUM';
        insBadge.className = patient.insurance_type === 'BPJS' ? 'px-2 py-0.5 rounded-full font-semibold text-[10px] bg-blue-100 text-blue-700' : 'px-2 py-0.5 rounded-full font-semibold text-[10px] bg-emerald-100 text-emerald-700';

        document.getElementById('selected-patient-card').classList.remove('hidden');

        // Otomatis pilih metode pembayaran sesuai asuransi pasien
        if (patient.insurance_type === 'BPJS') {
            const bpjsRadio = document.querySelector('input[name="payment_type"][value="BPJS"]');
            if (bpjsRadio) bpjsRadio.checked = true;
        } else {
            const umumRadio = document.querySelector('input[name="payment_type"][value="UMUM"]');
            if (umumRadio) umumRadio.checked = true;
        }
    }

    function clearSelectedPatient() {
        document.getElementById('form-patient-id').value = '';
        document.getElementById('patient-search-input').value = '';
        document.getElementById('btn-clear-patient').classList.add('hidden');
        document.getElementById('selected-patient-card').classList.add('hidden');
        document.getElementById('patient-results-dropdown').classList.add('hidden');
    }

    /**
     * Submit Form Tambah Pendaftaran Pasien Rawat Jalan
     */
    async function handleRegistrationSubmit(e) {
        e.preventDefault();

        const form = document.getElementById('form-registration');
        const submitBtn = document.getElementById('btn-submit-registration');
        const patientId = document.getElementById('form-patient-id').value;

        if (!patientId) {
            Swal.fire({
                icon: 'warning',
                title: 'Pasien Belum Dipilih',
                text: 'Silakan pilih pasien yang sudah terdaftar di sistem.',
                confirmButtonColor: '#04BBA6',
            });
            return;
        }

        const formData = new FormData(form);

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...`;

        try {
            const res = await fetch('<?= base_url('registrations/store') ?>', {
                method: 'POST',
                body: formData,
            });

            const result = await res.json();

            if (!result.status) {
                // Tampilkan pesan error validasi atau bisnis
                let errorMsg = result.message || 'Gagal menyimpan pendaftaran.';
                if (result.errors) {
                    errorMsg = Object.values(result.errors).join('<br>');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Pendaftaran Ditolak',
                    html: errorMsg,
                    confirmButtonColor: '#04BBA6',
                });
                return;
            }

            // Berhasil
            Swal.fire({
                icon: 'success',
                title: 'Pendaftaran Berhasil!',
                text: result.message,
                timer: 2500,
                showConfirmButton: false,
            });

            closeRegistrationModal();
            loadRegistrations(1);

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Sistem',
                text: err.message,
                confirmButtonColor: '#04BBA6',
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fa-solid fa-check"></i> <span>Simpan Pendaftaran</span>`;
        }
    }

    // ══════════════════════════════════════════════════════
    // DETAIL & UBAH STATUS PENDAFTARAN
    // ══════════════════════════════════════════════════════

    async function openDetailModal(id) {
        const modal = document.getElementById('modal-detail');
        const container = document.getElementById('detail-content');
        const btnContainer = document.getElementById('detail-action-buttons');
        
        container.innerHTML = `
            <div class="py-10 text-center text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                <p>Memuat detail kunjungan...</p>
            </div>
        `;
        btnContainer.innerHTML = '';
        modal.classList.remove('hidden');

        try {
            const res = await fetch(`<?= base_url('registrations/show') ?>/${id}`);
            const result = await res.json();

            if (!result.status || !result.data) {
                throw new Error(result.message || 'Data tidak ditemukan.');
            }

            const d = result.data;

            container.innerHTML = `
                <!-- Nomor Antrean & Registrasi -->
                <div class="p-4 bg-teal-50/70 border border-teal-200/60 rounded-2xl flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-teal-800 tracking-wider">Nomor Pendaftaran</span>
                        <p class="text-base font-mono font-bold text-teal-950">${escapeHtml(d.registration_number)}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Tanggal Kunjungan: <strong class="text-slate-700">${formatDateIndo(d.registration_date)}</strong></p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">Antrean</span>
                        <span class="text-3xl font-extrabold text-teal-700 font-mono">${d.queue_number}</span>
                    </div>
                </div>

                <!-- Info Pasien -->
                <div class="grid grid-cols-2 gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                    <div>
                        <p class="text-[10px] uppercase font-semibold text-slate-400">Nama Pasien</p>
                        <p class="font-bold text-slate-800">${escapeHtml(d.patient_name)}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-semibold text-slate-400">No. Rekam Medis</p>
                        <p class="font-mono font-bold text-slate-700">${escapeHtml(d.no_rm)}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-semibold text-slate-400">NIK & Jenis Kelamin</p>
                        <p class="text-slate-700">${escapeHtml(d.patient_nik || '-')} (${d.patient_gender === 'L' ? 'Laki-Laki' : 'Perempuan'})</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-semibold text-slate-400">No. Telepon / Alamat</p>
                        <p class="text-slate-700">${escapeHtml(d.patient_phone || '-')} / ${escapeHtml(d.patient_address || '-')}</p>
                    </div>
                </div>

                <!-- Info Poliklinik & Dokter -->
                <div class="grid grid-cols-2 gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                    <div>
                        <p class="text-[10px] uppercase font-semibold text-slate-400">Poliklinik Tujuan</p>
                        <p class="font-bold text-teal-700">${escapeHtml(d.polyclinic_name)}</p>
                        <p class="text-[10px] text-slate-500">${escapeHtml(d.polyclinic_location || '-')}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-semibold text-slate-400">Dokter Bertugas</p>
                        <p class="font-bold text-slate-800">${escapeHtml(d.doctor_name)}</p>
                        <p class="text-[10px] text-slate-500">${escapeHtml(d.doctor_specialization || 'Dokter Spesialis')}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-semibold text-slate-400">Pembayaran</p>
                        <span class="font-bold ${d.payment_type === 'BPJS' ? 'text-blue-700' : 'text-emerald-700'}">${escapeHtml(d.payment_type)}</span>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-semibold text-slate-400">Status Kunjungan</p>
                        <span class="font-bold">${escapeHtml(d.status)}</span>
                    </div>
                </div>

                <!-- Keluhan Utama -->
                <div class="p-3 bg-amber-50/60 border border-amber-200/60 rounded-xl">
                    <p class="text-[10px] uppercase font-bold text-amber-800 tracking-wider">Keluhan Utama Pasien</p>
                    <p class="text-xs text-slate-700 mt-1">${escapeHtml(d.complaint || '-')}</p>
                </div>

                <!-- Ringkasan Tanda Vital jika sudah ada pemeriksaan -->
                ${d.examination_id ? `
                    <div class="p-3.5 bg-emerald-50/70 border border-emerald-200/70 rounded-xl space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] uppercase font-bold text-emerald-800 flex items-center gap-1">
                                <i class="fa-solid fa-heart-pulse"></i> Hasil Pemeriksaan & Tanda Vital
                            </span>
                            <span class="text-[10px] text-emerald-700">${escapeHtml(d.examination_time || '')}</span>
                        </div>
                        <div class="grid grid-cols-4 gap-2 text-center text-[11px]">
                            <div class="p-1.5 bg-white rounded-lg border border-emerald-100">
                                <span class="text-slate-400 block text-[9px]">TD</span>
                                <span class="font-bold text-slate-800">${d.systolic_bp || '-'}/${d.diastolic_bp || '-'}</span>
                            </div>
                            <div class="p-1.5 bg-white rounded-lg border border-emerald-100">
                                <span class="text-slate-400 block text-[9px]">Nadi</span>
                                <span class="font-bold text-slate-800">${d.pulse || '-'} bpm</span>
                            </div>
                            <div class="p-1.5 bg-white rounded-lg border border-emerald-100">
                                <span class="text-slate-400 block text-[9px]">Suhu</span>
                                <span class="font-bold text-slate-800">${d.temperature || '-'} &deg;C</span>
                            </div>
                            <div class="p-1.5 bg-white rounded-lg border border-emerald-100">
                                <span class="text-slate-400 block text-[9px]">Pemeriksa</span>
                                <span class="font-bold text-slate-800 truncate block">${escapeHtml(d.examiner_name || '-')}</span>
                            </div>
                        </div>
                        ${d.assessment ? `<p class="text-[11px] text-slate-700 pt-1 border-t border-emerald-100"><strong>Diagnosis:</strong> ${escapeHtml(d.assessment)}</p>` : ''}
                    </div>
                ` : `
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center text-slate-400">
                        <i class="fa-solid fa-stethoscope mr-1"></i> Belum ada pemeriksaan / tanda vital yang dicatat.
                    </div>
                `}
            `;

            // Tombol aksi di modal detail
            btnContainer.innerHTML = `
                <button type="button" onclick="closeDetailModal(); openVitalSignsModal(${d.id})" class="px-3.5 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-heart-pulse"></i> ${d.examination_id ? 'Perbarui Tanda Vital' : 'Input Tanda Vital'}
                </button>
            `;

        } catch (err) {
            container.innerHTML = `<p class="py-8 text-center text-rose-500">${escapeHtml(err.message)}</p>`;
        }
    }

    function closeDetailModal() {
        document.getElementById('modal-detail').classList.add('hidden');
    }

    /**
     * Konfirmasi Pembatalan Pendaftaran
     */
    function confirmCancelRegistration(id, regNumber) {
        Swal.fire({
            title: 'Batalkan Pendaftaran?',
            html: `Apakah Anda yakin ingin membatalkan pendaftaran <strong class="text-rose-600">${escapeHtml(regNumber)}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Tutup',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const res = await fetch(`<?= base_url('registrations/cancel') ?>/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });
                    const resJson = await res.json();

                    if (!resJson.status) {
                        throw new Error(resJson.message || 'Gagal membatalkan pendaftaran.');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Dibatalkan',
                        text: resJson.message,
                        timer: 2000,
                        showConfirmButton: false,
                    });

                    loadRegistrations(currentPage);
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

    /**
     * Dialog Cepat Ubah Status Pendaftaran
     */
    function promptChangeStatus(id, currentStatus) {
        Swal.fire({
            title: 'Ubah Status Kunjungan',
            input: 'select',
            inputOptions: {
                'MENUNGGU': 'MENUNGGU (Dalam Antrean)',
                'DIPERIKSA': 'DIPERIKSA (Sedang Bersama Dokter)',
                'SELESAI': 'SELESAI (Pemeriksaan Tuntas)',
                'BATAL': 'BATAL (Dibatalkan Pasien/Petugas)'
            },
            inputValue: currentStatus,
            showCancelButton: true,
            confirmButtonText: 'Simpan Status',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#04BBA6',
            inputValidator: (value) => {
                if (!value) {
                    return 'Pilih salah satu status!';
                }
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const formData = new FormData();
                    formData.append('status', result.value);

                    const res = await fetch(`<?= base_url('registrations/update-status') ?>/${id}`, {
                        method: 'POST',
                        body: formData,
                    });
                    const resJson = await res.json();

                    if (!resJson.status) {
                        throw new Error(resJson.message || 'Gagal mengubah status.');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Status Diperbarui',
                        text: resJson.message,
                        timer: 2000,
                        showConfirmButton: false,
                    });

                    loadRegistrations(currentPage);
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

    // ══════════════════════════════════════════════════════
    // MODAL PENCATATAN TANDA-TANDA VITAL & REKAM MEDIS
    // ══════════════════════════════════════════════════════

    async function openVitalSignsModal(registrationId) {
        const modal = document.getElementById('modal-vital-signs');
        const form = document.getElementById('form-vital-signs');
        form.reset();

        document.getElementById('vs-registration-id').value = registrationId;

        // Buka modal
        modal.classList.remove('hidden');

        try {
            const res = await fetch(`<?= base_url('examinations/by-registration') ?>/${registrationId}`);
            const result = await res.json();

            if (!result.status || !result.registration) {
                throw new Error(result.message || 'Gagal memuat data pendaftaran.');
            }

            const reg = result.registration;
            const exam = result.examination;

            // Isi identitas
            document.getElementById('vs-modal-subtitle').textContent = `Pasien: ${reg.patient_name} (${reg.no_rm}) - Poli ${reg.polyclinic_name}`;
            document.getElementById('vs-patient-name').textContent = reg.patient_name;
            document.getElementById('vs-patient-rm').textContent = `${reg.no_rm} / ${reg.patient_nik || '-'}`;
            document.getElementById('vs-polyclinic').textContent = reg.polyclinic_name;
            document.getElementById('vs-doctor').textContent = reg.doctor_name;

            // Waktu pemeriksaan
            const nowFormatted = new Date().toISOString().slice(0, 19).replace('T', ' ');
            document.getElementById('vs-exam-time').value = (exam && exam.examination_time) ? exam.examination_time : nowFormatted;

            // Keluhan bawaan dari pendaftaran jika subjective masih kosong
            document.getElementById('vs-subjective').value = (exam && exam.subjective) ? exam.subjective : (reg.complaint || '');

            // Isi tanda vital jika sudah ada
            if (exam) {
                document.getElementById('vs-systolic').value = exam.systolic_bp || '';
                document.getElementById('vs-diastolic').value = exam.diastolic_bp || '';
                document.getElementById('vs-pulse').value = exam.pulse || '';
                document.getElementById('vs-temp').value = exam.temperature || '';
                document.getElementById('vs-resp').value = exam.respiratory_rate || '';
                document.getElementById('vs-weight').value = exam.weight || '';
                document.getElementById('vs-height').value = exam.height || '';
                document.getElementById('vs-objective').value = exam.objective || '';
                document.getElementById('vs-assessment').value = exam.assessment || '';
                document.getElementById('vs-plan').value = exam.plan || '';
            }

            // Pilih status kunjungan yang sesuai
            const radioStatus = document.querySelector(`input[name="final_status"][value="${reg.status === 'DIPERIKSA' ? 'DIPERIKSA' : 'SELESAI'}"]`);
            if (radioStatus) radioStatus.checked = true;

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message,
            });
            closeVitalSignsModal();
        }
    }

    function closeVitalSignsModal() {
        document.getElementById('modal-vital-signs').classList.add('hidden');
    }

    /**
     * Submit Form Pencatatan Tanda Vital & Pemeriksaan
     */
    async function handleVitalSignsSubmit(e) {
        e.preventDefault();

        const form = document.getElementById('form-vital-signs');
        const submitBtn = document.getElementById('btn-save-vital-signs');
        const formData = new FormData(form);

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...`;

        try {
            const res = await fetch('<?= base_url('examinations/save') ?>', {
                method: 'POST',
                body: formData,
            });

            const result = await res.json();

            if (!result.status) {
                let errorMsg = result.message || 'Gagal menyimpan hasil pemeriksaan.';
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
                title: 'Berhasil Disimpan!',
                text: result.message,
                timer: 2500,
                showConfirmButton: false,
            });

            closeVitalSignsModal();
            loadRegistrations(currentPage);

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Sistem',
                text: err.message,
                confirmButtonColor: '#04BBA6',
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fa-solid fa-floppy-disk"></i> <span>Simpan Rekam Medis & Tanda Vital</span>`;
        }
    }

    // ══════════════════════════════════════════════════════
    // UTILITAS FORMATTING
    // ══════════════════════════════════════════════════════

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
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    }
</script>

<?= $this->endSection() ?>

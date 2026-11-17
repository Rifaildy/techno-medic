<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="tm-examination-wrapper space-y-6">

    <!-- ══════════════════════════════════════════════
         HEADER HALAMAN
    ══════════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Pemeriksaan & Tanda Vital Pasien</h1>
            <p class="text-sm text-slate-500 mt-1">
                Pencatatan tanda-tanda vital (vital signs), anamnesis keluhan, diagnosis medis, dan rekam medis rawat jalan.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="refreshCurrentTab()" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-semibold rounded-xl shadow-sm transition">
                <i class="fa-solid fa-arrows-rotate text-slate-400"></i>
                <span>Segarkan</span>
            </button>
            <a href="<?= base_url('registrations') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-50 text-teal-700 hover:bg-teal-100 border border-teal-200 text-sm font-semibold rounded-xl transition">
                <i class="fa-solid fa-clipboard-list"></i>
                <span>Ke Antrean Pendaftaran</span>
            </a>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         TAB SWITCHER: ANTREAN PERIKSA & RIWAYAT SELESAI
    ══════════════════════════════════════════════ -->
    <div class="border-b border-slate-200 flex items-center gap-6 text-sm font-semibold">
        <button type="button" id="tab-btn-queue" onclick="switchTab('queue')"
                class="pb-3 border-b-2 border-primary text-primary flex items-center gap-2 transition">
            <i class="fa-solid fa-user-clock"></i>
            <span>Antrean Pasien Hari Ini</span>
            <span id="queue-badge-count" class="px-2 py-0.5 rounded-full text-xs bg-primary/10 text-primary font-bold">0</span>
        </button>

        <button type="button" id="tab-btn-history" onclick="switchTab('history')"
                class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2 transition">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span>Riwayat Rekam Medis & Tanda Vital</span>
        </button>
    </div>

    <!-- ══════════════════════════════════════════════
         TAB 1: ANTREAN PASIEN SIAP DIPERIKSA
    ══════════════════════════════════════════════ -->
    <div id="tab-content-queue" class="space-y-4">
        
        <!-- Filter Cepat Antrean -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-wrap items-center justify-between gap-3 text-sm">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-semibold">Tanggal:</span>
                    <input type="date" id="queue-filter-date" value="<?= esc($today) ?>"
                           class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-primary focus:border-primary"
                           onchange="loadQueueList()">
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-semibold">Poli:</span>
                    <select id="queue-filter-poly" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-1 focus:ring-primary focus:border-primary"
                            onchange="loadQueueList()">
                        <option value="">Semua Poliklinik</option>
                        <?php foreach ($polyclinics as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="text-xs text-slate-400">
                Menampilkan pasien berstatus <span class="text-amber-600 font-semibold">MENUNGGU</span> atau <span class="text-blue-600 font-semibold">SEDANG DIPERIKSA</span>
            </div>
        </div>

        <!-- Grid Kartu Antrean Pasien -->
        <div id="queue-cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="col-span-full py-16 text-center text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                <p class="text-sm">Memuat antrean pasien...</p>
            </div>
        </div>

    </div>

    <!-- ══════════════════════════════════════════════
         TAB 2: RIWAYAT REKAM MEDIS & PEMERIKSAAN
    ══════════════════════════════════════════════ -->
    <div id="tab-content-history" class="space-y-4 hidden">
        
        <!-- Filter Riwayat -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                <div class="lg:col-span-5 relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" id="history-filter-search" placeholder="Cari Pasien / No. RM / No. Reg / Diagnosis..."
                           class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                           onkeyup="handleHistorySearch(event)">
                </div>

                <div class="lg:col-span-3">
                    <input type="date" id="history-filter-date"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                           onchange="loadHistoryList(1)">
                </div>

                <div class="lg:col-span-4">
                    <select id="history-filter-poly" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                            onchange="loadHistoryList(1)">
                        <option value="">Semua Poliklinik</option>
                        <?php foreach ($polyclinics as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Pemeriksaan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4">Waktu Periksa</th>
                            <th class="py-3.5 px-4">Pasien & No. RM</th>
                            <th class="py-3.5 px-4">Poliklinik & Dokter</th>
                            <th class="py-3.5 px-4">Tanda-Tanda Vital</th>
                            <th class="py-3.5 px-4">Diagnosis (Assessment)</th>
                            <th class="py-3.5 px-4">Pemeriksa</th>
                            <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="history-tbody" class="divide-y divide-slate-100 text-slate-700">
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                                <p class="text-sm">Memuat riwayat rekam medis...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="px-5 py-3.5 bg-slate-50/60 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div id="history-pagination-info">Menampilkan data...</div>
                <div class="flex items-center gap-1.5" id="history-pagination-buttons"></div>
            </div>
        </div>

    </div>

</div>

<!-- ══════════════════════════════════════════════
     MODAL: INPUT / EDIT TANDA VITAL & REKAM MEDIS
══════════════════════════════════════════════ -->
<div id="modal-exam-form" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in duration-200">
        
        <!-- Header -->
        <div class="px-6 py-4.5 bg-gradient-to-r from-sidebarBg to-darkteal text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/20 border border-primary/30 flex items-center justify-center text-primary text-lg">
                    <i class="fa-solid fa-stethoscope"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Pencatatan Tanda Vital & Rekam Medis Rawat Jalan</h3>
                    <p class="text-xs text-white/60" id="exam-modal-subtitle">Pasien: -</p>
                </div>
            </div>
            <button type="button" onclick="closeExamModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="form-exam-record" onsubmit="handleExamSubmit(event)" class="p-6 space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" name="registration_id" id="exam-reg-id">

            <!-- Identitas Pasien Terpilih -->
            <div class="p-4 bg-teal-50/60 border border-teal-200/60 rounded-2xl grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Nama Pasien</span>
                    <span class="font-bold text-slate-800 text-sm" id="exam-patient-name">-</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">No. RM / NIK</span>
                    <span class="font-mono text-slate-700 font-semibold" id="exam-patient-rm">-</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Poliklinik Tujuan</span>
                    <span class="font-bold text-teal-800" id="exam-poly-name">-</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Dokter Bertugas</span>
                    <span class="font-bold text-slate-800" id="exam-doc-name">-</span>
                </div>
            </div>

            <!-- Tanda-Tanda Vital Lengkap dengan Panduan Normal -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-heart-pulse text-rose-500"></i>
                        Parameter Tanda-Tanda Vital Pasien
                    </h4>
                    <span class="text-[11px] text-slate-400">Nilai normal dicantumkan sebagai referensi</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    
                    <!-- Sistolik -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-xs">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1">
                            <span>TD Sistolik</span>
                            <span class="text-[9px] text-slate-400">(90-120)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <input type="number" name="systolic_bp" id="field-systolic" placeholder="120" min="40" max="300"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <span class="text-[10px] text-slate-400 font-semibold">mmHg</span>
                        </div>
                    </div>

                    <!-- Diastolik -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-xs">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1">
                            <span>TD Diastolik</span>
                            <span class="text-[9px] text-slate-400">(60-80)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <input type="number" name="diastolic_bp" id="field-diastolic" placeholder="80" min="30" max="200"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <span class="text-[10px] text-slate-400 font-semibold">mmHg</span>
                        </div>
                    </div>

                    <!-- Denyut Nadi -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-xs">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1">
                            <span>Denyut Nadi</span>
                            <span class="text-[9px] text-slate-400">(60-100)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <input type="number" name="pulse" id="field-pulse" placeholder="80" min="30" max="250"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <span class="text-[10px] text-slate-400 font-semibold">x/mnt</span>
                        </div>
                    </div>

                    <!-- Suhu Tubuh -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-xs">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1">
                            <span>Suhu Tubuh</span>
                            <span class="text-[9px] text-slate-400">(36.5-37.5)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="0.1" name="temperature" id="field-temp" placeholder="36.5" min="30" max="45"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <span class="text-[10px] text-slate-400 font-semibold">&deg;C</span>
                        </div>
                    </div>

                    <!-- Frekuensi Pernapasan -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-xs">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1">
                            <span>Frekuensi Napas</span>
                            <span class="text-[9px] text-slate-400">(12-20)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <input type="number" name="respiratory_rate" id="field-resp" placeholder="18" min="5" max="80"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <span class="text-[10px] text-slate-400 font-semibold">x/mnt</span>
                        </div>
                    </div>

                    <!-- Berat Badan -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-xs">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1">
                            <span>Berat Badan</span>
                            <span class="text-[9px] text-slate-400">kg</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="0.1" name="weight" id="field-weight" placeholder="60.0" min="1" max="500"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                   oninput="calculateBMI()">
                            <span class="text-[10px] text-slate-400 font-semibold">kg</span>
                        </div>
                    </div>

                    <!-- Tinggi Badan -->
                    <div class="p-3 bg-white border border-slate-200 rounded-xl shadow-xs">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1">
                            <span>Tinggi Badan</span>
                            <span class="text-[9px] text-slate-400">cm</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="0.1" name="height" id="field-height" placeholder="165.0" min="10" max="300"
                                   class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-800 focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                   oninput="calculateBMI()">
                            <span class="text-[10px] text-slate-400 font-semibold">cm</span>
                        </div>
                    </div>

                    <!-- Kalkulasi BMI & Waktu -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl shadow-xs flex flex-col justify-between">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600">
                            <span>Indeks BMI</span>
                            <span id="label-bmi" class="font-bold text-primary">-</span>
                        </div>
                        <input type="hidden" name="examination_time" id="field-exam-time">
                        <p class="text-[10px] text-slate-400" id="label-exam-time-display">-</p>
                    </div>

                </div>
            </div>

            <!-- Rekam Medis (SOAP Format) -->
            <div class="space-y-3">
                <!-- S: Subjective / Keluhan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Keluhan Utama Pasien / Anamnesis (Subjective) <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="subjective" id="field-subjective" rows="2" required placeholder="Keluhan yang dirasakan pasien saat ini..."
                              class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- O: Objective / Temuan Fisik -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Pemeriksaan Fisik & Temuan Klinis (Objective)
                        </label>
                        <textarea name="objective" id="field-objective" rows="2" placeholder="Hasil inspeksi, auskultasi, palpasi..."
                                  class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
                    </div>

                    <!-- A: Assessment / Diagnosis -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Diagnosis Medis (Assessment / ICD-10)
                        </label>
                        <textarea name="assessment" id="field-assessment" rows="2" placeholder="Nama penyakit / kode ICD-10..."
                                  class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- P: Plan / Terapi -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Rencana Terapi / Resep Obat (Plan)
                        </label>
                        <textarea name="plan" id="field-plan" rows="2" placeholder="Instruksi obat, anjuran istirahat..."
                                  class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
                    </div>

                    <!-- Notes / Catatan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Catatan Tambahan Petugas
                        </label>
                        <textarea name="notes" id="field-notes" rows="2" placeholder="Catatan kontrol lanjutan atau rujukan..."
                                  class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
                    </div>
                </div>
            </div>

            <!-- Petugas & Tindak Lanjut Kunjungan -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div>
                    <span class="text-slate-500 font-medium">Petugas Pemeriksa:</span>
                    <strong class="text-slate-800 ml-1"><?= esc(session('fullname')) ?></strong>
                    <span class="text-slate-400 text-[11px]">(<?= esc(session('role_label')) ?>)</span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-700">Status Pendaftaran:</span>
                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 rounded-xl cursor-pointer hover:bg-blue-50 transition">
                        <input type="radio" name="final_status" value="DIPERIKSA" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-blue-700 font-semibold text-xs">Sedang Diperiksa</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 rounded-xl cursor-pointer hover:bg-emerald-50 transition">
                        <input type="radio" name="final_status" value="SELESAI" checked class="text-emerald-600 focus:ring-emerald-500">
                        <span class="text-emerald-700 font-semibold text-xs">Selesai Pemeriksaan</span>
                    </label>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-2 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeExamModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" id="btn-submit-exam" class="px-5 py-2.5 bg-primary hover:bg-primaryHover text-white text-xs font-semibold rounded-xl shadow-md shadow-primary/20 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Rekam Medis Pasien</span>
                </button>
            </div>

        </form>

    </div>
</div>

<!-- ══════════════════════════════════════════════
     MODAL: LIHAT DETAIL REKAM MEDIS LENGKAP
══════════════════════════════════════════════ -->
<div id="modal-view-record" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        
        <div class="px-6 py-4 bg-slate-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-file-medical text-primary text-lg"></i>
                <h3 class="text-base font-bold text-white">Lembar Hasil Pemeriksaan Pasien</h3>
            </div>
            <button type="button" onclick="closeViewRecordModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs" id="view-record-content">
            <!-- Konten rekam medis dimuat via JS -->
        </div>

        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <button type="button" onclick="closeViewRecordModal()" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-semibold rounded-xl transition">
                Tutup
            </button>
        </div>

    </div>
</div>

<!-- ══════════════════════════════════════════════
     SCRIPTS & CLIENT-SIDE LOGIC
══════════════════════════════════════════════ -->
<script>
    let activeTab = 'queue';
    let historyCurrentPage = 1;
    let historySearchTimeout = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadQueueList();
    });

    function switchTab(tab) {
        activeTab = tab;
        const btnQueue = document.getElementById('tab-btn-queue');
        const btnHistory = document.getElementById('tab-btn-history');
        const contentQueue = document.getElementById('tab-content-queue');
        const contentHistory = document.getElementById('tab-content-history');

        if (tab === 'queue') {
            btnQueue.className = 'pb-3 border-b-2 border-primary text-primary flex items-center gap-2 transition';
            btnHistory.className = 'pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2 transition';
            contentQueue.classList.remove('hidden');
            contentHistory.classList.add('hidden');
            loadQueueList();
        } else {
            btnHistory.className = 'pb-3 border-b-2 border-primary text-primary flex items-center gap-2 transition';
            btnQueue.className = 'pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2 transition';
            contentHistory.classList.remove('hidden');
            contentQueue.classList.add('hidden');
            loadHistoryList(1);
        }
    }

    function refreshCurrentTab() {
        if (activeTab === 'queue') {
            loadQueueList();
        } else {
            loadHistoryList(historyCurrentPage);
        }
    }

    // ══════════════════════════════════════════════════════
    // TAB 1: ANTREAN PEMERIKSAAN HARI INI
    // ══════════════════════════════════════════════════════

    async function loadQueueList() {
        const container = document.getElementById('queue-cards-container');
        const countBadge = document.getElementById('queue-badge-count');
        const date = document.getElementById('queue-filter-date').value;
        const polyId = document.getElementById('queue-filter-poly').value;

        container.innerHTML = `
            <div class="col-span-full py-16 text-center text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                <p class="text-sm">Memuat antrean pasien...</p>
            </div>
        `;

        const params = new URLSearchParams({
            date: date,
            polyclinic_id: polyId,
        });

        try {
            const res = await fetch(`<?= base_url('examinations/queue-list') ?>?${params.toString()}`);
            const result = await res.json();

            if (!result.status) {
                throw new Error(result.message || 'Gagal memuat antrean.');
            }

            const items = result.data || [];
            countBadge.textContent = items.length;

            if (items.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full py-16 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-teal-50 text-teal-600 mb-3 text-2xl">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <p class="text-base font-bold text-slate-700">Tidak ada pasien dalam antrean periksa</p>
                        <p class="text-xs text-slate-400 mt-1">Semua pasien pada tanggal ini telah selesai diperiksa atau belum ada pendaftaran baru.</p>
                    </div>
                `;
                return;
            }

            let cardsHtml = '';
            items.forEach(q => {
                const isExamining = q.status === 'DIPERIKSA';
                const statusBadge = isExamining
                    ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 animate-pulse"><i class="fa-solid fa-stethoscope text-[9px]"></i> Sedang Diperiksa</span>`
                    : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200"><i class="fa-solid fa-clock text-[9px]"></i> Menunggu</span>`;

                cardsHtml += `
                    <div class="bg-white rounded-2xl p-4 border ${isExamining ? 'border-blue-300 ring-2 ring-blue-500/10' : 'border-slate-200/80'} shadow-sm hover:shadow-md transition space-y-3.5 flex flex-col justify-between">
                        
                        <!-- Top Info -->
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-xl bg-teal-50 text-teal-700 border border-teal-200 font-extrabold text-sm flex items-center justify-center">
                                        ${q.queue_number}
                                    </span>
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm leading-tight">${escapeHtml(q.patient_name)}</p>
                                        <p class="text-[11px] text-slate-400 font-mono">RM: ${escapeHtml(q.no_rm)} &bull; ${q.patient_gender === 'L' ? 'Laki-Laki' : 'Perempuan'}</p>
                                    </div>
                                </div>
                                ${statusBadge}
                            </div>

                            <!-- Poliklinik & Dokter -->
                            <div class="p-2.5 bg-slate-50 rounded-xl space-y-1 text-xs">
                                <p class="font-semibold text-teal-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-hospital text-[10px]"></i> ${escapeHtml(q.polyclinic_name)}
                                </p>
                                <p class="text-slate-600 flex items-center gap-1.5">
                                    <i class="fa-solid fa-user-doctor text-[10px] text-slate-400"></i> ${escapeHtml(q.doctor_name)}
                                </p>
                            </div>

                            <!-- Keluhan Awal -->
                            <div class="mt-2 text-xs">
                                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Keluhan Pasien:</span>
                                <p class="text-slate-700 line-clamp-2 mt-0.5 italic">"${escapeHtml(q.complaint || '-')}"</p>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] font-mono text-slate-400">${escapeHtml(q.registration_number)}</span>
                            <button type="button" onclick="openExamModal(${q.id})" class="px-3.5 py-2 bg-primary hover:bg-primaryHover text-white text-xs font-semibold rounded-xl shadow-sm flex items-center gap-1.5 transition">
                                <i class="fa-solid fa-heart-pulse"></i>
                                <span>${isExamining ? 'Lanjutkan Periksa' : 'Mulai Periksa & TTV'}</span>
                            </button>
                        </div>

                    </div>
                `;
            });

            container.innerHTML = cardsHtml;

        } catch (err) {
            container.innerHTML = `<div class="col-span-full py-8 text-center text-rose-500">${escapeHtml(err.message)}</div>`;
        }
    }

    // ══════════════════════════════════════════════════════
    // TAB 2: RIWAYAT REKAM MEDIS & PEMERIKSAAN SELESAI
    // ══════════════════════════════════════════════ -->

    async function loadHistoryList(page = 1) {
        historyCurrentPage = page;
        const tbody = document.getElementById('history-tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="py-12 text-center text-slate-400">
                    <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                    <p class="text-sm">Memuat riwayat rekam medis...</p>
                </td>
            </tr>
        `;

        const search = document.getElementById('history-filter-search').value.trim();
        const date = document.getElementById('history-filter-date').value;
        const polyId = document.getElementById('history-filter-poly').value;

        const params = new URLSearchParams({
            page: page,
            per_page: 10,
            search: search,
            date: date,
            polyclinic_id: polyId,
        });

        try {
            const res = await fetch(`<?= base_url('examinations/list') ?>?${params.toString()}`);
            const result = await res.json();

            if (!result.status) {
                throw new Error(result.message || 'Gagal memuat rekam medis.');
            }

            const items = result.data || [];
            if (items.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="py-16 text-center text-slate-400">
                            <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-300"></i>
                            <p class="text-sm font-semibold text-slate-600">Belum ada riwayat pemeriksaan</p>
                            <p class="text-xs text-slate-400 mt-0.5">Pasien yang selesai diperiksa akan tercatat di sini.</p>
                        </td>
                    </tr>
                `;
                renderHistoryPagination({ total: 0 });
                return;
            }

            let html = '';
            items.forEach(item => {
                // Formatting TTV ringkas
                const bp = (item.systolic_bp && item.diastolic_bp) ? `${item.systolic_bp}/${item.diastolic_bp} mmHg` : '-';
                const pulse = item.pulse ? `${item.pulse} bpm` : '-';
                const temp = item.temperature ? `${item.temperature} &deg;C` : '-';

                html += `
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3.5 px-4">
                            <p class="font-bold text-slate-800 text-xs">${formatDateTimeIndo(item.examination_time)}</p>
                            <p class="text-[10px] text-slate-400 font-mono mt-0.5">${escapeHtml(item.registration_number)}</p>
                        </td>

                        <td class="py-3.5 px-4">
                            <p class="font-bold text-slate-800 text-xs">${escapeHtml(item.patient_name)}</p>
                            <p class="text-[11px] text-slate-400 font-mono">${escapeHtml(item.no_rm)} &bull; ${item.patient_gender === 'L' ? 'Laki-Laki' : 'Perempuan'}</p>
                        </td>

                        <td class="py-3.5 px-4">
                            <p class="font-semibold text-slate-800 text-xs">${escapeHtml(item.polyclinic_name)}</p>
                            <p class="text-[11px] text-slate-500">${escapeHtml(item.doctor_name)}</p>
                        </td>

                        <td class="py-3.5 px-4">
                            <div class="text-[11px] space-y-0.5">
                                <span class="font-semibold text-slate-700 block">TD: ${bp}</span>
                                <span class="text-slate-500">Nadi: ${pulse} &bull; Suhu: ${temp}</span>
                            </div>
                        </td>

                        <td class="py-3.5 px-4">
                            <p class="text-xs font-semibold text-slate-800 line-clamp-1">${escapeHtml(item.assessment || 'Pemeriksaan Umum')}</p>
                            <p class="text-[11px] text-slate-500 line-clamp-1 italic">"${escapeHtml(item.subjective || '-')}"</p>
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="text-xs font-medium text-slate-700">${escapeHtml(item.examiner_name || '-')}</span>
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <button type="button" onclick="openViewRecordModal(${item.id})" title="Lihat Lembar Rekam Medis"
                                        class="w-7 h-7 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 flex items-center justify-center text-xs transition">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <button type="button" onclick="openExamModal(${item.registration_id})" title="Edit Rekam Medis"
                                        class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            renderHistoryPagination(result.pagination);

        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-rose-500">${escapeHtml(err.message)}</td></tr>`;
        }
    }

    function renderHistoryPagination(p) {
        const info = document.getElementById('history-pagination-info');
        const btns = document.getElementById('history-pagination-buttons');

        if (!p || p.total === 0) {
            info.textContent = 'Menampilkan 0 data';
            btns.innerHTML = '';
            return;
        }

        info.textContent = `Menampilkan ${p.from} - ${p.to} dari total ${p.total} pemeriksaan`;
        let btnHtml = '';

        btnHtml += `
            <button type="button" onclick="loadHistoryList(${p.current_page - 1})" ${p.current_page <= 1 ? 'disabled class="opacity-40 cursor-not-allowed px-2.5 py-1 rounded-lg border border-slate-200 text-slate-400"' : 'class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 transition"'}>
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </button>
        `;

        for (let i = 1; i <= p.last_page; i++) {
            if (i === 1 || i === p.last_page || (i >= p.current_page - 1 && i <= p.current_page + 1)) {
                btnHtml += `
                    <button type="button" onclick="loadHistoryList(${i})" class="px-2.5 py-1 rounded-lg text-xs font-semibold transition ${i === p.current_page ? 'bg-primary text-white shadow-sm' : 'border border-slate-200 hover:bg-slate-100 text-slate-700'}">
                        ${i}
                    </button>
                `;
            }
        }

        btnHtml += `
            <button type="button" onclick="loadHistoryList(${p.current_page + 1})" ${p.current_page >= p.last_page ? 'disabled class="opacity-40 cursor-not-allowed px-2.5 py-1 rounded-lg border border-slate-200 text-slate-400"' : 'class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 transition"'}>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        `;

        btns.innerHTML = btnHtml;
    }

    function handleHistorySearch(e) {
        clearTimeout(historySearchTimeout);
        historySearchTimeout = setTimeout(() => {
            loadHistoryList(1);
        }, 400);
    }

    // ══════════════════════════════════════════════════════
    // FORM MODAL PENCATATAN PEMERIKSAAN
    // ══════════════════════════════════════════════════════

    async function openExamModal(registrationId) {
        const modal = document.getElementById('modal-exam-form');
        const form = document.getElementById('form-exam-record');
        form.reset();

        document.getElementById('exam-reg-id').value = registrationId;
        modal.classList.remove('hidden');

        try {
            const res = await fetch(`<?= base_url('examinations/by-registration') ?>/${registrationId}`);
            const result = await res.json();

            if (!result.status || !result.registration) {
                throw new Error(result.message || 'Gagal memuat data pendaftaran.');
            }

            const reg = result.registration;
            const exam = result.examination;

            document.getElementById('exam-modal-subtitle').textContent = `Pasien: ${reg.patient_name} (${reg.no_rm}) - Poli ${reg.polyclinic_name}`;
            document.getElementById('exam-patient-name').textContent = reg.patient_name;
            document.getElementById('exam-patient-rm').textContent = `${reg.no_rm} / ${reg.patient_nik || '-'}`;
            document.getElementById('exam-poly-name').textContent = reg.polyclinic_name;
            document.getElementById('exam-doc-name').textContent = reg.doctor_name;

            const nowTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
            const finalExamTime = (exam && exam.examination_time) ? exam.examination_time : nowTime;
            document.getElementById('field-exam-time').value = finalExamTime;
            document.getElementById('label-exam-time-display').textContent = 'Waktu: ' + finalExamTime;

            document.getElementById('field-subjective').value = (exam && exam.subjective) ? exam.subjective : (reg.complaint || '');

            if (exam) {
                document.getElementById('field-systolic').value = exam.systolic_bp || '';
                document.getElementById('field-diastolic').value = exam.diastolic_bp || '';
                document.getElementById('field-pulse').value = exam.pulse || '';
                document.getElementById('field-temp').value = exam.temperature || '';
                document.getElementById('field-resp').value = exam.respiratory_rate || '';
                document.getElementById('field-weight').value = exam.weight || '';
                document.getElementById('field-height').value = exam.height || '';
                document.getElementById('field-objective').value = exam.objective || '';
                document.getElementById('field-assessment').value = exam.assessment || '';
                document.getElementById('field-plan').value = exam.plan || '';
                document.getElementById('field-notes').value = exam.notes || '';
                calculateBMI();
            }

            const radio = document.querySelector(`input[name="final_status"][value="${reg.status === 'DIPERIKSA' ? 'DIPERIKSA' : 'SELESAI'}"]`);
            if (radio) radio.checked = true;

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message,
            });
            closeExamModal();
        }
    }

    function closeExamModal() {
        document.getElementById('modal-exam-form').classList.add('hidden');
    }

    function calculateBMI() {
        const weight = parseFloat(document.getElementById('field-weight').value);
        const height = parseFloat(document.getElementById('field-height').value);
        const label = document.getElementById('label-bmi');

        if (weight > 0 && height > 0) {
            const heightInMeters = height / 100;
            const bmi = weight / (heightInMeters * heightInMeters);
            let category = 'Normal';
            if (bmi < 18.5) category = 'Kurang (Underweight)';
            else if (bmi >= 25 && bmi < 30) category = 'Kelebihan (Overweight)';
            else if (bmi >= 30) category = 'Obesitas';

            label.textContent = `${bmi.toFixed(1)} (${category})`;
        } else {
            label.textContent = '-';
        }
    }

    async function handleExamSubmit(e) {
        e.preventDefault();

        const form = document.getElementById('form-exam-record');
        const submitBtn = document.getElementById('btn-submit-exam');
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
                title: 'Tersimpan!',
                text: result.message,
                timer: 2000,
                showConfirmButton: false,
            });

            closeExamModal();
            refreshCurrentTab();

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Sistem',
                text: err.message,
                confirmButtonColor: '#04BBA6',
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fa-solid fa-floppy-disk"></i> <span>Simpan Rekam Medis Pasien</span>`;
        }
    }

    // ══════════════════════════════════════════════════════
    // LIHAT DETAIL LEMBAR REKAM MEDIS
    // ══════════════════════════════════════════════════════

    async function openViewRecordModal(id) {
        const modal = document.getElementById('modal-view-record');
        const container = document.getElementById('view-record-content');

        container.innerHTML = `
            <div class="py-10 text-center text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-primary mb-2"></i>
                <p>Memuat rekam medis...</p>
            </div>
        `;
        modal.classList.remove('hidden');

        try {
            const res = await fetch(`<?= base_url('examinations/show') ?>/${id}`);
            const result = await res.json();

            if (!result.status || !result.data) {
                throw new Error(result.message || 'Data tidak ditemukan.');
            }

            const d = result.data;

            container.innerHTML = `
                <!-- Kop Pasien -->
                <div class="p-4 bg-teal-50 border border-teal-200 rounded-2xl flex items-center justify-between">
                    <div>
                        <h4 class="text-base font-bold text-teal-900">${escapeHtml(d.patient_name)}</h4>
                        <p class="text-xs text-slate-600 font-mono">No. RM: <strong>${escapeHtml(d.no_rm)}</strong> &bull; NIK: ${escapeHtml(d.patient_nik || '-')}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">${d.patient_gender === 'L' ? 'Laki-Laki' : 'Perempuan'} &bull; ${escapeHtml(d.polyclinic_name)} &bull; ${escapeHtml(d.doctor_name)}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold text-teal-700 block">Waktu Periksa</span>
                        <span class="text-xs font-bold text-slate-800">${formatDateTimeIndo(d.examination_time)}</span>
                    </div>
                </div>

                <!-- Hasil Vital Signs Lengkap -->
                <div class="border border-slate-200 rounded-2xl p-4 space-y-2">
                    <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-heart-pulse text-rose-500"></i>
                        Tanda-Tanda Vital Pasien
                    </h5>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-center">
                        <div class="p-2 bg-slate-50 rounded-xl">
                            <span class="text-[10px] text-slate-400 block font-semibold">Tekanan Darah</span>
                            <span class="font-extrabold text-slate-800 text-sm">${d.systolic_bp || '-'}/${d.diastolic_bp || '-'} <small class="font-normal text-[10px]">mmHg</small></span>
                        </div>
                        <div class="p-2 bg-slate-50 rounded-xl">
                            <span class="text-[10px] text-slate-400 block font-semibold">Denyut Nadi</span>
                            <span class="font-extrabold text-slate-800 text-sm">${d.pulse || '-'} <small class="font-normal text-[10px]">x/mnt</small></span>
                        </div>
                        <div class="p-2 bg-slate-50 rounded-xl">
                            <span class="text-[10px] text-slate-400 block font-semibold">Suhu Tubuh</span>
                            <span class="font-extrabold text-slate-800 text-sm">${d.temperature || '-'} <small class="font-normal text-[10px]">&deg;C</small></span>
                        </div>
                        <div class="p-2 bg-slate-50 rounded-xl">
                            <span class="text-[10px] text-slate-400 block font-semibold">Frekuensi Napas</span>
                            <span class="font-extrabold text-slate-800 text-sm">${d.respiratory_rate || '-'} <small class="font-normal text-[10px]">x/mnt</small></span>
                        </div>
                        <div class="p-2 bg-slate-50 rounded-xl">
                            <span class="text-[10px] text-slate-400 block font-semibold">Berat Badan</span>
                            <span class="font-extrabold text-slate-800 text-sm">${d.weight || '-'} <small class="font-normal text-[10px]">kg</small></span>
                        </div>
                        <div class="p-2 bg-slate-50 rounded-xl">
                            <span class="text-[10px] text-slate-400 block font-semibold">Tinggi Badan</span>
                            <span class="font-extrabold text-slate-800 text-sm">${d.height || '-'} <small class="font-normal text-[10px]">cm</small></span>
                        </div>
                        <div class="p-2 bg-slate-50 rounded-xl col-span-2">
                            <span class="text-[10px] text-slate-400 block font-semibold">Pemeriksa</span>
                            <span class="font-bold text-slate-800 text-xs">${escapeHtml(d.examiner_name || '-')}</span>
                        </div>
                    </div>
                </div>

                <!-- SOAP Catatan Klinis -->
                <div class="space-y-2.5">
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">S (Subjective) - Keluhan & Anamnesis:</span>
                        <p class="text-xs text-slate-800 mt-0.5">${escapeHtml(d.subjective || '-')}</p>
                    </div>

                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">O (Objective) - Pemeriksaan Fisik:</span>
                        <p class="text-xs text-slate-800 mt-0.5">${escapeHtml(d.objective || '-')}</p>
                    </div>

                    <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl">
                        <span class="text-[10px] uppercase font-bold text-emerald-800 block">A (Assessment) - Diagnosis Medis (ICD-10):</span>
                        <p class="text-xs font-bold text-emerald-950 mt-0.5">${escapeHtml(d.assessment || '-')}</p>
                    </div>

                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block">P (Plan) - Terapi / Resep Obat:</span>
                        <p class="text-xs text-slate-800 mt-0.5 whitespace-pre-line">${escapeHtml(d.plan || '-')}</p>
                    </div>

                    ${d.notes ? `
                        <div class="p-3 bg-amber-50/50 border border-amber-200 rounded-xl">
                            <span class="text-[10px] uppercase font-bold text-amber-800 block">Catatan Tambahan:</span>
                            <p class="text-xs text-slate-700 mt-0.5">${escapeHtml(d.notes)}</p>
                        </div>
                    ` : ''}
                </div>
            `;

        } catch (err) {
            container.innerHTML = `<p class="py-8 text-center text-rose-500">${escapeHtml(err.message)}</p>`;
        }
    }

    function closeViewRecordModal() {
        document.getElementById('modal-view-record').classList.add('hidden');
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

    function formatDateTimeIndo(dateTimeStr) {
        if (!dateTimeStr) return '-';
        const d = new Date(dateTimeStr);
        if (isNaN(d.getTime())) return dateTimeStr;
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }
</script>

<?= $this->endSection() ?>

<?php $this->extend('layouts/dashboard') ?>
<?php $this->section('content') ?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Dashboard</h2>
        <p class="text-sm text-slate-500 mt-0.5">
            Selamat datang, <span class="font-semibold text-primary"><?= esc(session('fullname')) ?></span>
            &mdash; <?= date('l, d F Y') ?>
        </p>
    </div>
    <div class="flex items-center gap-2 bg-primary/10 text-primary text-xs font-semibold px-3 py-1.5 rounded-full">
        <i class="fa-solid fa-circle text-[8px] animate-pulse"></i>
        Sistem Aktif
    </div>
</div>

<!-- ══════════════════════════════════════════════
     STAT CARDS
══════════════════════════════════════════════ -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <!-- Total Pasien -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-users text-primary text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium">Total Pasien</p>
            <p class="text-2xl font-bold text-slate-800"><?= number_format($totalPatients) ?></p>
        </div>
    </div>

    <!-- Kunjungan Hari Ini -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-calendar-day text-secondary text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium">Kunjungan Hari Ini</p>
            <p class="text-2xl font-bold text-slate-800"><?= number_format($totalVisitsToday) ?></p>
        </div>
    </div>

    <!-- Menunggu -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-clock text-amber-500 text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium">Menunggu</p>
            <p class="text-2xl font-bold text-slate-800"><?= number_format($totalWaiting) ?></p>
        </div>
    </div>

    <!-- Sedang Diperiksa -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-stethoscope text-blue-500 text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium">Diperiksa</p>
            <p class="text-2xl font-bold text-slate-800"><?= number_format($totalExamining) ?></p>
        </div>
    </div>

</div>

<!-- ══════════════════════════════════════════════
     BARIS BAWAH: Antrean Aktif + Stat Poli
══════════════════════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- Antrean Aktif (2/3 lebar) -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-list-ol text-primary"></i>
                <h3 class="text-sm font-semibold text-slate-700">Antrean Aktif Hari Ini</h3>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs bg-primary/10 text-primary font-semibold px-2.5 py-1 rounded-full">
                    <?= count($activeQueue) ?> pasien
                </span>
                <a href="<?= base_url('registrations') ?>" class="text-xs font-semibold text-primary hover:text-primaryHover ml-1">
                    Lihat Semua &rarr;
                </a>
            </div>
        </div>

        <?php if (empty($activeQueue)): ?>
        <div class="flex flex-col items-center justify-center py-12 text-slate-400">
            <i class="fa-solid fa-inbox text-4xl mb-3 opacity-30"></i>
            <p class="text-sm">Tidak ada antrean aktif saat ini</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-xs text-slate-500 font-semibold uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">No.</th>
                        <th class="px-5 py-3 text-left">Pasien</th>
                        <th class="px-5 py-3 text-left">Poliklinik</th>
                        <th class="px-5 py-3 text-left">Dokter</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($activeQueue as $q): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-primary/10 text-primary font-bold text-xs">
                                <?= $q['queue_number'] ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-slate-800"><?= esc($q['patient_name']) ?></p>
                            <p class="text-xs text-slate-400"><?= esc($q['no_rm']) ?></p>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600"><?= esc($q['poly_name']) ?></td>
                        <td class="px-5 py-3.5 text-slate-600"><?= esc($q['doctor_name']) ?></td>
                        <td class="px-5 py-3.5 text-center">
                            <?php if ($q['status'] === 'MENUNGGU'): ?>
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-2.5 py-0.5">
                                <i class="fa-solid fa-clock text-[8px]"></i> Menunggu
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-full px-2.5 py-0.5">
                                <i class="fa-solid fa-stethoscope text-[8px]"></i> Diperiksa
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <a href="<?= base_url('examinations') ?>" class="inline-flex items-center gap-1 px-2.5 py-1 bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 rounded-lg text-xs font-semibold transition" title="Periksa Pasien">
                                <i class="fa-solid fa-heart-pulse text-[10px]"></i> Periksa
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Kunjungan per Poli (1/3 lebar) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
            <i class="fa-solid fa-chart-pie text-primary"></i>
            <h3 class="text-sm font-semibold text-slate-700">Kunjungan per Poli</h3>
        </div>

        <div class="p-5 space-y-4">
            <?php if (empty($visitsByPoly)): ?>
            <p class="text-sm text-slate-400 text-center py-6">Belum ada data hari ini</p>
            <?php else: ?>
            <?php
                $maxVisit = max(array_column($visitsByPoly, 'total')) ?: 1;
                $colors = ['bg-primary', 'bg-secondary', 'bg-darkteal', 'bg-accent'];
            ?>
            <?php foreach ($visitsByPoly as $i => $poly): ?>
            <div>
                <div class="flex justify-between items-center mb-1">
                    <p class="text-xs font-medium text-slate-700 truncate max-w-[70%]"><?= esc($poly['poly_name']) ?></p>
                    <p class="text-xs font-bold text-slate-800"><?= $poly['total'] ?> <span class="text-slate-400 font-normal">pasien</span></p>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="<?= $colors[$i % count($colors)] ?> h-2 rounded-full transition-all duration-500"
                         style="width: <?= round(($poly['total'] / $maxVisit) * 100) ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Ringkasan status -->
        <div class="border-t border-slate-100 px-5 py-4 grid grid-cols-2 gap-3">
            <div class="text-center bg-emerald-50 rounded-xl py-3">
                <p class="text-xl font-bold text-emerald-700"><?= $totalDone ?></p>
                <p class="text-[10px] font-semibold text-emerald-600 uppercase tracking-wide">Selesai</p>
            </div>
            <div class="text-center bg-slate-50 rounded-xl py-3">
                <p class="text-xl font-bold text-slate-600"><?= $totalVisitsToday - $totalWaiting - $totalExamining - $totalDone ?></p>
                <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Dibatal</p>
            </div>
        </div>
    </div>

</div>

<?php $this->endSection() ?>

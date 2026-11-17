<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'TechnoMedic') ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary:      '#04BBA6',
                        primaryHover: '#039E8C',
                        secondary:    '#1ABBCB',
                        darkteal:     '#007B84',
                        brandDark:    '#0F172A',
                        brandMuted:   '#64748B',
                        sidebarBg:    '#0F1E24',
                        sidebarActive:'#173641',
                        accent:       '#D5E022',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-slate-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- ══════════════════════════════════════════
             SIDEBAR
        ══════════════════════════════════════════ -->
        <aside id="sidebar" class="w-64 flex-shrink-0 bg-sidebarBg flex flex-col transition-all duration-300 z-40">

            <!-- Brand -->
            <div class="flex items-center gap-3 px-6 py-5 border-b border-white/5">
                <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center shadow-lg">
                    <i class="fa-solid fa-hospital-user text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-white font-bold text-sm leading-tight tracking-wide">TechnoMedic</p>
                    <p class="text-white/40 text-[10px] font-medium uppercase tracking-widest">SIMRS Rawat Jalan</p>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

                <p class="px-3 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-widest text-white/30">Menu Utama</p>

                <a href="<?= base_url('dashboard') ?>"
                   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/70 hover:bg-sidebarActive hover:text-white transition-colors <?= (current_url() === base_url('dashboard')) ? 'bg-sidebarActive !text-white' : '' ?>">
                    <i class="fa-solid fa-gauge-high w-5 text-center text-primary"></i>
                    <span>Dashboard</span>
                </a>

                <p class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-widest text-white/30">Manajemen</p>

                <a href="#"
                   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/70 hover:bg-sidebarActive hover:text-white transition-colors">
                    <i class="fa-solid fa-users w-5 text-center text-primary"></i>
                    <span>Data Pasien</span>
                </a>

                <a href="#"
                   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/70 hover:bg-sidebarActive hover:text-white transition-colors">
                    <i class="fa-solid fa-clipboard-list w-5 text-center text-primary"></i>
                    <span>Pendaftaran</span>
                </a>

                <a href="#"
                   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/70 hover:bg-sidebarActive hover:text-white transition-colors">
                    <i class="fa-solid fa-stethoscope w-5 text-center text-primary"></i>
                    <span>Pemeriksaan</span>
                </a>

                <?php if (session('role_name') === 'admin'): ?>
                <p class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-widest text-white/30">Administrasi</p>

                <a href="#"
                   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/70 hover:bg-sidebarActive hover:text-white transition-colors">
                    <i class="fa-solid fa-user-gear w-5 text-center text-primary"></i>
                    <span>Pengguna</span>
                </a>

                <a href="#"
                   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/70 hover:bg-sidebarActive hover:text-white transition-colors">
                    <i class="fa-solid fa-building-columns w-5 text-center text-primary"></i>
                    <span>Poliklinik</span>
                </a>

                <a href="#"
                   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/70 hover:bg-sidebarActive hover:text-white transition-colors">
                    <i class="fa-solid fa-user-doctor w-5 text-center text-primary"></i>
                    <span>Dokter</span>
                </a>
                <?php endif; ?>

            </nav>

            <!-- User info (bottom) -->
            <div class="border-t border-white/5 px-4 py-3 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-user text-primary text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-semibold truncate"><?= esc(session('fullname')) ?></p>
                    <p class="text-white/40 text-[10px] truncate"><?= esc(session('role_label')) ?></p>
                </div>
            </div>

        </aside>

        <!-- ══════════════════════════════════════════
             KONTEN UTAMA
        ══════════════════════════════════════════ -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Topbar -->
            <header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-6 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="text-slate-500 hover:text-slate-800 transition-colors">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <h1 class="text-sm font-semibold text-slate-700"><?= esc($title ?? 'Dashboard') ?></h1>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Tanggal -->
                    <span class="text-xs text-slate-500 hidden sm:block" id="currentDate"></span>

                    <!-- Logout -->
                    <form action="<?= base_url('logout') ?>" method="POST" id="logoutForm">
                        <?= csrf_field() ?>
                        <button type="button" onclick="confirmLogout()"
                                class="flex items-center gap-2 text-xs text-slate-600 hover:text-red-600 font-medium border border-slate-200 hover:border-red-300 rounded-lg px-3 py-1.5 transition-colors">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">

                <!-- Flash Messages -->
                <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span><?= esc(session()->getFlashdata('success')) ?></span>
                </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
                    <i class="fa-solid fa-circle-xmark text-red-500"></i>
                    <span><?= esc(session()->getFlashdata('error')) ?></span>
                </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>
            </main>

        </div>
    </div>

    <script>
        // Tanggal real-time
        function updateDate() {
            const now = new Date();
            const opts = { weekday:'long', year:'numeric', month:'long', day:'numeric' };
            document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', opts);
        }
        updateDate();

        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('w-64');
            sidebar.classList.toggle('w-0');
            sidebar.classList.toggle('overflow-hidden');
        }

        // Konfirmasi logout
        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                text: 'Anda yakin ingin keluar dari sistem?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#ef4444',
                cancelButtonColor:  '#64748b',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logoutForm').submit();
                }
            });
        }

        // SweetAlert untuk flash message
        <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= addslashes(session()->getFlashdata('success')) ?>',
            timer: 2500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
        });
        <?php endif; ?>
    </script>

</body>
</html>

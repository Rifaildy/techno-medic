<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'TechnoMedic — Login') ?></title>

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
                        primary:       '#04BBA6',
                        primaryHover:  '#039E8C',
                        secondary:     '#1ABBCB',
                        darkteal:      '#007B84',
                        brandDark:     '#0F172A',
                        brandMuted:    '#64748B',
                        sidebarBg:     '#0F1E24',
                        sidebarActive: '#173641',
                        accent:        '#D5E022',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        html, body {
            background-color: #ffffff;
            color: #0f172a;
        }

        .login-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.04);
        }

        .input-field {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            transition: all 0.2s ease;
        }

        .input-field:focus {
            background-color: #ffffff;
            border-color: #04BBA6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(4, 187, 166, 0.18);
        }

        .input-field::placeholder {
            color: #94a3b8;
            opacity: 1;
        }

        /* Menjaga warna input tetap terang dan teks gelap saat browser melakukan autofill */
        .input-field:-webkit-autofill,
        .input-field:-webkit-autofill:hover,
        .input-field:-webkit-autofill:focus,
        .input-field:-webkit-autofill:active {
            -webkit-text-fill-color: #0f172a !important;
            -webkit-box-shadow: 0 0 0 1000px #f8fafc inset !important;
            transition: background-color 5000s ease-in-out 0s;
            caret-color: #0f172a;
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-white text-slate-900">

    <div class="min-h-screen flex items-center justify-center px-4 py-10 bg-white">
        <div class="w-full max-w-sm">

            <!-- Logo & Judul -->
            <div class="text-center mb-7">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary shadow-lg shadow-primary/25 mb-4">
                    <i class="fa-solid fa-hospital-user text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">TechnoMedic</h1>
                <p class="text-slate-500 text-sm mt-1">Sistem Informasi Rawat Jalan</p>
            </div>

            <!-- Card Form -->
            <div class="login-card rounded-2xl p-7">

                <h2 class="text-base font-bold text-slate-900 mb-1">Masuk ke Sistem</h2>
                <p class="text-slate-500 text-xs mb-6">Gunakan akun yang diberikan administrator</p>

                <!-- Error message -->
                <?php if (session()->getFlashdata('error')): ?>
                <div class="flex items-start gap-2.5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-5">
                    <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 flex-shrink-0"></i>
                    <p class="text-red-700 text-sm"><?= esc(session()->getFlashdata('error')) ?></p>
                </div>
                <?php endif; ?>

                <!-- Success message -->
                <?php if (session()->getFlashdata('success')): ?>
                <div class="flex items-start gap-2.5 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 mb-5">
                    <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 flex-shrink-0"></i>
                    <p class="text-emerald-700 text-sm"><?= esc(session()->getFlashdata('success')) ?></p>
                </div>
                <?php endif; ?>

                <!-- Validation errors -->
                <?php if (session()->getFlashdata('errors')): ?>
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-5 space-y-1">
                    <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                    <p class="text-red-700 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-xmark text-red-500"></i> <?= esc($err) ?>
                    </p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form action="<?= base_url('login') ?>" method="POST" autocomplete="off">
                    <?= csrf_field() ?>

                    <!-- Username / Email -->
                    <div class="mb-4">
                        <label for="identifierField" class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Username / Email
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3.5 flex items-center text-slate-400 pointer-events-none">
                                <i class="fa-solid fa-user text-sm"></i>
                            </span>
                            <input type="text"
                                   id="identifierField"
                                   name="identifier"
                                   value="<?= esc(old('identifier')) ?>"
                                   placeholder="Masukkan username atau email"
                                   class="input-field w-full rounded-xl pl-10 pr-4 py-2.5 text-sm"
                                   required autofocus>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-6">
                        <label for="passwordField" class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">
                            Password
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3.5 flex items-center text-slate-400 pointer-events-none">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </span>
                            <input type="password"
                                   name="password"
                                   id="passwordField"
                                   placeholder="••••••••"
                                   class="input-field w-full rounded-xl pl-10 pr-10 py-2.5 text-sm"
                                   required>
                            <button type="button"
                                    onclick="togglePassword()"
                                    title="Tampilkan / Sembunyikan Password"
                                    class="absolute inset-y-0 right-3.5 flex items-center text-slate-400 hover:text-darkteal transition-colors">
                                <i id="eyeIcon" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                            class="w-full bg-primary hover:bg-primaryHover text-white font-semibold text-sm rounded-xl py-2.5 transition-colors shadow-md shadow-primary/25 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Masuk</span>
                    </button>

                </form>
            </div>

            <!-- Hint akun demo (Klik untuk isi cepat) -->
            <div class="mt-5 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5">
                <p class="text-slate-600 text-[11px] font-semibold uppercase tracking-wider mb-2.5 flex items-center justify-between">
                    <span><i class="fa-solid fa-circle-info text-primary mr-1"></i> Akun Demo</span>
                    <span class="text-[10px] font-normal normal-case text-slate-400">Klik untuk isi otomatis</span>
                </p>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <button type="button"
                            onclick="fillDemoAccount('admin')"
                            class="bg-white hover:bg-teal-50/70 border border-slate-200 hover:border-primary rounded-lg py-2 px-1 transition-all shadow-sm">
                        <p class="text-slate-700 text-[11px] font-semibold">Admin</p>
                        <p class="text-darkteal font-mono font-semibold text-[11px] mt-0.5">admin</p>
                    </button>
                    <button type="button"
                            onclick="fillDemoAccount('petugas1')"
                            class="bg-white hover:bg-teal-50/70 border border-slate-200 hover:border-primary rounded-lg py-2 px-1 transition-all shadow-sm">
                        <p class="text-slate-700 text-[11px] font-semibold">Petugas</p>
                        <p class="text-darkteal font-mono font-semibold text-[11px] mt-0.5">petugas1</p>
                    </button>
                    <button type="button"
                            onclick="fillDemoAccount('dr.agus')"
                            class="bg-white hover:bg-teal-50/70 border border-slate-200 hover:border-primary rounded-lg py-2 px-1 transition-all shadow-sm">
                        <p class="text-slate-700 text-[11px] font-semibold">Dokter</p>
                        <p class="text-darkteal font-mono font-semibold text-[11px] mt-0.5">dr.agus</p>
                    </button>
                </div>
                <p class="text-slate-600 text-[11px] text-center mt-2.5">
                    Password: <span class="text-darkteal font-mono font-semibold">password123</span>
                </p>
            </div>

            <p class="text-center text-slate-400 text-xs mt-6">
                © <?= date('Y') ?> TechnoMedic — SIMRS Rawat Jalan
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const field    = document.getElementById('passwordField');
            const icon     = document.getElementById('eyeIcon');
            const isPass   = field.type === 'password';
            field.type     = isPass ? 'text' : 'password';
            icon.className = isPass ? 'fa-solid fa-eye-slash text-sm' : 'fa-solid fa-eye text-sm';
        }

        function fillDemoAccount(username) {
            document.getElementById('identifierField').value = username;
            document.getElementById('passwordField').value   = 'password123';
            document.getElementById('identifierField').focus();
        }
    </script>

</body>
</html>
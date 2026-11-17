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

    <style>
        .login-bg {
            background: linear-gradient(135deg, #0F1E24 0%, #0a3038 50%, #07404a 100%);
        }
        .glass-card {
            background: rgba(255,255,255,0.03);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .input-field {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            transition: all 0.2s;
        }
        .input-field:focus {
            background: rgba(4,187,166,0.08);
            border-color: #04BBA6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(4,187,166,0.15);
        }
        .input-field::placeholder { color: rgba(255,255,255,0.3); }
        .dot-pattern {
            background-image: radial-gradient(rgba(4,187,166,0.15) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="h-full font-sans antialiased login-bg dot-pattern">

    <!-- Decorative blobs -->
    <div class="fixed top-[-80px] right-[-80px] w-72 h-72 rounded-full bg-primary/10 blur-3xl pointer-events-none"></div>
    <div class="fixed bottom-[-60px] left-[-60px] w-56 h-56 rounded-full bg-secondary/10 blur-3xl pointer-events-none"></div>

    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-sm">

            <!-- Logo & Judul -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary shadow-lg shadow-primary/30 mb-4">
                    <i class="fa-solid fa-hospital-user text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white">TechnoMedic</h1>
                <p class="text-white/40 text-sm mt-1">Sistem Informasi Rawat Jalan</p>
            </div>

            <!-- Card Form -->
            <div class="glass-card rounded-2xl p-7">

                <h2 class="text-base font-semibold text-white mb-1">Masuk ke Sistem</h2>
                <p class="text-white/40 text-xs mb-6">Gunakan akun yang diberikan administrator</p>

                <!-- Error message -->
                <?php if (session()->getFlashdata('error')): ?>
                <div class="flex items-start gap-2.5 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3 mb-5">
                    <i class="fa-solid fa-circle-exclamation text-red-400 mt-0.5 flex-shrink-0"></i>
                    <p class="text-red-300 text-sm"><?= esc(session()->getFlashdata('error')) ?></p>
                </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')): ?>
                <div class="flex items-start gap-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-4 py-3 mb-5">
                    <i class="fa-solid fa-circle-check text-emerald-400 mt-0.5 flex-shrink-0"></i>
                    <p class="text-emerald-300 text-sm"><?= esc(session()->getFlashdata('success')) ?></p>
                </div>
                <?php endif; ?>

                <!-- Validation errors -->
                <?php if (session()->getFlashdata('errors')): ?>
                <div class="bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3 mb-5">
                    <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                    <p class="text-red-300 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-xmark"></i> <?= esc($err) ?>
                    </p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form action="<?= base_url('login') ?>" method="POST" autocomplete="off">
                    <?= csrf_field() ?>

                    <!-- Username / Email -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-white/60 mb-1.5 uppercase tracking-wider">
                            Username / Email
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-white/30 pointer-events-none">
                                <i class="fa-solid fa-user text-sm"></i>
                            </span>
                            <input type="text"
                                   name="identifier"
                                   value="<?= esc(old('identifier')) ?>"
                                   placeholder="username atau email"
                                   class="input-field w-full rounded-xl pl-9 pr-4 py-2.5 text-sm"
                                   required autofocus>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-6">
                        <label class="block text-xs font-semibold text-white/60 mb-1.5 uppercase tracking-wider">
                            Password
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-white/30 pointer-events-none">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </span>
                            <input type="password"
                                   name="password"
                                   id="passwordField"
                                   placeholder="••••••••"
                                   class="input-field w-full rounded-xl pl-9 pr-10 py-2.5 text-sm"
                                   required>
                            <button type="button"
                                    onclick="togglePassword()"
                                    class="absolute inset-y-0 right-3 flex items-center text-white/30 hover:text-white/70 transition-colors">
                                <i id="eyeIcon" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                            class="w-full bg-primary hover:bg-primaryHover text-white font-semibold text-sm rounded-xl py-2.5 transition-colors shadow-lg shadow-primary/20 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Masuk
                    </button>

                </form>
            </div>

            <!-- Hint akun demo -->
            <div class="mt-5 glass-card rounded-xl px-4 py-3">
                <p class="text-white/30 text-[11px] font-semibold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-circle-info mr-1"></i> Akun Demo
                </p>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="bg-white/5 rounded-lg py-2 px-1">
                        <p class="text-white/70 text-[11px] font-semibold">Admin</p>
                        <p class="text-primary text-[10px] mt-0.5">admin</p>
                    </div>
                    <div class="bg-white/5 rounded-lg py-2 px-1">
                        <p class="text-white/70 text-[11px] font-semibold">Petugas</p>
                        <p class="text-primary text-[10px] mt-0.5">petugas1</p>
                    </div>
                    <div class="bg-white/5 rounded-lg py-2 px-1">
                        <p class="text-white/70 text-[11px] font-semibold">Dokter</p>
                        <p class="text-primary text-[10px] mt-0.5">dr.agus</p>
                    </div>
                </div>
                <p class="text-white/20 text-[10px] text-center mt-2">Password: <span class="text-white/40">password123</span></p>
            </div>

            <p class="text-center text-white/20 text-[10px] mt-6">
                © <?= date('Y') ?> TechnoMedic — SIMRS Rawat Jalan
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const field   = document.getElementById('passwordField');
            const icon    = document.getElementById('eyeIcon');
            const isPass  = field.type === 'password';
            field.type    = isPass ? 'text' : 'password';
            icon.className = isPass ? 'fa-solid fa-eye-slash text-sm' : 'fa-solid fa-eye text-sm';
        }
    </script>

</body>
</html>

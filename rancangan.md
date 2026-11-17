sebuah rumah sakit butuh sistem informasi untuk pendaftaran pasien rawat jalan dengan permasalahan saat ini
data pasien ganda, kesalahan pemilihan dokter dan poli, kesulitan mengetahui jumlah kunjuan pasien, tidak tersedianya informasi antrean pasien, kesulitan menelusuri riwayat pemeriksaan pasien

konsep RBAC

fitur yang kemungkinan ada
pengelolaan data pasien(no rekam medis[unik], nik[unik], nama lengkap[wajib diisi], jenis kelamin, tanggal lahir[tidak melebihi tanggal hari ini, alamat, no tlpn)

fitur pendaftaran
memiliki informasi pendaftaran
no pendaftaran, tanggal kunjungan, pasien, poliklinik, dokter, jenis pembayaran(UMUM/BPJS), keluhan utama, status pendaftaran
dengan ketentuan
pasien harus sudah terdaftar
dokter harus sudah sesuai dengan poliklinik yang dipilih
no pendaftaran tidak boleh duplikat
pasien tidak boleh mempunyai pendaftaran aktif ganda pada dokter dan tanggal yang sama
status pendaftaran terdiri dari(MENUNGGU/DIPERIKSA/SELESAI/BATAL)
buat halaman pendaftaran yang menampilkan informasi pasien dokter poliklinik dan status kunjungan

buat juga fitur pencatatan tanda tanda vital pasien
informasi pemeriksaan
keluhan, tekanan darah sistolik, tekanan darah diastolik, denyut nadi, suhu tubuh, frekuensi pernapasan, berat badan, tinggi badan, waktu pemeriksaan, nama petugas pemeriksa

dashboard menampilkan:
total pasien terdaftar
total kunjungan hari ini
jumlah pasien menunggu
jumlah pasien sedang diperiksa
jumlah pasien sedang diperiksa
jumlah kunjungan berdasarkan poliklinik

gunakan format layout agar konsisten disetiap tampilan
<!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome & SweetAlert2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#04BBA6',      // Teal Kemenkes
                        primaryHover: '#039E8C',
                        secondary: '#1ABBCB',    // Cyan Muda
                        darkteal: '#007B84',     // Teal Gelap
                        brandDark: '#0F172A',    // Slate Dark
                        brandMuted: '#64748B',
                        sidebarBg: '#0F1E24',    // Dark Teal Navy
                        sidebarActive: '#173641',
                        accent: '#D5E022',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

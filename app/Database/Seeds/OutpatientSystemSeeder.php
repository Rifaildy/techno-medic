<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder Script: Outpatient Information System & RBAC Dummy Data
 * 
 * Memuat master data role, permission, user login (Admin, Petugas, Dokter),
 * polyclinic, dokter, pasien, serta data transaksi pendaftaran & pemeriksaan.
 * 
 * Perintah Jalankan Seeder di CLI:
 * php spark db:seed OutpatientSystemSeeder
 */
class OutpatientSystemSeeder extends Seeder
{
    public function run()
    {
        // Nonaktifkan sementara pemeriksaan FK untuk eksekusi seeder yang bersih
        $this->db->disableForeignKeyChecks();

        $now   = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        // Password default untuk semua user dummy: "password123"
        $defaultPassword = password_hash('password123', PASSWORD_BCRYPT);

        // ==========================================
        // 1. SEED ROLES
        // ==========================================
        $rolesData = [
            [
                'id'          => 1,
                'name'        => 'admin',
                'label'       => 'Administrator Sistem',
                'description' => 'Administrator Sistem & Hak Akses Penuh',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 2,
                'name'        => 'petugas',
                'label'       => 'Petugas Pendaftaran',
                'description' => 'Petugas Pendaftaran Pasien & Loket Antrean',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 3,
                'name'        => 'dokter',
                'label'       => 'Dokter Pemeriksa',
                'description' => 'Dokter Poli & Pemeriksa Pasien Rawat Jalan',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];
        $this->db->table('roles')->insertBatch($rolesData);

        // ==========================================
        // 2. SEED PERMISSIONS
        // ==========================================
        $permissionsData = [
            // Dashboard
            ['id' => 1, 'name' => 'dashboard.view', 'label' => 'Lihat Dashboard', 'module' => 'Dashboard'],
            // Master Pasien
            ['id' => 2, 'name' => 'patients.view', 'label' => 'Lihat Data Pasien', 'module' => 'Pasien'],
            ['id' => 3, 'name' => 'patients.create', 'label' => 'Tambah Pasien Baru', 'module' => 'Pasien'],
            ['id' => 4, 'name' => 'patients.edit', 'label' => 'Ubah Data Pasien', 'module' => 'Pasien'],
            // Pendaftaran
            ['id' => 5, 'name' => 'registrations.view', 'label' => 'Lihat Antrean Pendaftaran', 'module' => 'Pendaftaran'],
            ['id' => 6, 'name' => 'registrations.create', 'label' => 'Daftarkan Pasien', 'module' => 'Pendaftaran'],
            ['id' => 7, 'name' => 'registrations.cancel', 'label' => 'Batalkan Pendaftaran', 'module' => 'Pendaftaran'],
            // Pemeriksaan
            ['id' => 8, 'name' => 'examinations.view', 'label' => 'Lihat Rekam Medis', 'module' => 'Pemeriksaan'],
            ['id' => 9, 'name' => 'examinations.create', 'label' => 'Input Hasil Pemeriksaan & Vital Signs', 'module' => 'Pemeriksaan'],
        ];
        foreach ($permissionsData as &$p) {
            $p['created_at'] = $now;
            $p['updated_at'] = $now;
        }
        $this->db->table('permissions')->insertBatch($permissionsData);

        // ==========================================
        // 3. SEED ROLE_PERMISSIONS (PIVOT RBAC)
        // ==========================================
        $rolePermissions = [
            // Admin: Memiliki Semua Permission (1 - 9)
            ['role_id' => 1, 'permission_id' => 1],
            ['role_id' => 1, 'permission_id' => 2],
            ['role_id' => 1, 'permission_id' => 3],
            ['role_id' => 1, 'permission_id' => 4],
            ['role_id' => 1, 'permission_id' => 5],
            ['role_id' => 1, 'permission_id' => 6],
            ['role_id' => 1, 'permission_id' => 7],
            ['role_id' => 1, 'permission_id' => 8],
            ['role_id' => 1, 'permission_id' => 9],

            // Petugas Pendaftaran: Dashboard, Manajemen Pasien, Pendaftaran, & Lihat Rekam Medis
            ['role_id' => 2, 'permission_id' => 1],
            ['role_id' => 2, 'permission_id' => 2],
            ['role_id' => 2, 'permission_id' => 3],
            ['role_id' => 2, 'permission_id' => 4],
            ['role_id' => 2, 'permission_id' => 5],
            ['role_id' => 2, 'permission_id' => 6],
            ['role_id' => 2, 'permission_id' => 7],
            ['role_id' => 2, 'permission_id' => 8],

            // Dokter: Dashboard, Lihat Pasien, Lihat Antrean Pendaftaran, & Pencatatan Pemeriksaan
            ['role_id' => 3, 'permission_id' => 1],
            ['role_id' => 3, 'permission_id' => 2],
            ['role_id' => 3, 'permission_id' => 5],
            ['role_id' => 3, 'permission_id' => 8],
            ['role_id' => 3, 'permission_id' => 9],
        ];
        $this->db->table('role_permissions')->insertBatch($rolePermissions);

        // ==========================================
        // 4. SEED USERS (Pengguna Akun System)
        // ==========================================
        $usersData = [
            [
                'id'         => 1,
                'role_id'    => 1,
                'username'   => 'admin',
                'email'      => 'admin@rumahsakit.com',
                'password'   => $defaultPassword,
                'fullname'   => 'Super Administrator',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 2,
                'role_id'    => 2,
                'username'   => 'petugas1',
                'email'      => 'petugas1@rumahsakit.com',
                'password'   => $defaultPassword,
                'fullname'   => 'Budi Santoso (Petugas Loket)',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 3,
                'role_id'    => 3,
                'username'   => 'dr.agus',
                'email'      => 'dr.agus@rumahsakit.com',
                'password'   => $defaultPassword,
                'fullname'   => 'dr. Agus Wijaya',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 4,
                'role_id'    => 3,
                'username'   => 'dr.dewi',
                'email'      => 'dr.dewi@rumahsakit.com',
                'password'   => $defaultPassword,
                'fullname'   => 'drg. Dewi Lestari, Sp.KG',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 5,
                'role_id'    => 3,
                'username'   => 'dr.siti',
                'email'      => 'dr.siti@rumahsakit.com',
                'password'   => $defaultPassword,
                'fullname'   => 'dr. Siti Rahma, Sp.A',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 6,
                'role_id'    => 3,
                'username'   => 'dr.hendra',
                'email'      => 'dr.hendra@rumahsakit.com',
                'password'   => $defaultPassword,
                'fullname'   => 'dr. Hendra Pratama, Sp.PD',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        $this->db->table('users')->insertBatch($usersData);

        // ==========================================
        // 5. SEED POLYCLINICS (Master Poliklinik)
        // ==========================================
        $polyclinicsData = [
            [
                'id'         => 1,
                'code'       => 'POL-UMU',
                'name'       => 'Poli Umum',
                'location'   => 'Gedung Utama - Lantai 1 (Ruang 101)',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 2,
                'code'       => 'POL-GIG',
                'name'       => 'Poli Gigi & Mulut',
                'location'   => 'Gedung Utama - Lantai 1 (Ruang 105)',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 3,
                'code'       => 'POL-ANA',
                'name'       => 'Poli Spesialis Anak',
                'location'   => 'Gedung B - Lantai 2 (Ruang 202)',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 4,
                'code'       => 'POL-PDL',
                'name'       => 'Poli Penyakit Dalam',
                'location'   => 'Gedung B - Lantai 2 (Ruang 208)',
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        $this->db->table('polyclinics')->insertBatch($polyclinicsData);

        // ==========================================
        // 6. SEED DOCTORS (Master Dokter Terkait Poli)
        // ==========================================
        $doctorsData = [
            [
                'id'            => 1,
                'user_id'       => 3, // dr. Agus Wijaya
                'polyclinic_id' => 1, // Poli Umum
                'nik'           => '3171011204800001',
                'sip'           => 'SIP/503/001/IDI/2022',
                'name'          => 'dr. Agus Wijaya',
                'specialization'=> 'Dokter Umum',
                'phone'         => '081234567801',
                'email'         => 'dr.agus@rumahsakit.com',
                'gender'        => 'L',
                'status'        => 'active',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'id'            => 2,
                'user_id'       => 4, // drg. Dewi Lestari
                'polyclinic_id' => 2, // Poli Gigi
                'nik'           => '3171015508850002',
                'sip'           => 'SIP/503/008/PDGI/2021',
                'name'          => 'drg. Dewi Lestari, Sp.KG',
                'specialization'=> 'Spesialis Konservasi Gigi',
                'phone'         => '081234567802',
                'email'         => 'dr.dewi@rumahsakit.com',
                'gender'        => 'P',
                'status'        => 'active',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'id'            => 3,
                'user_id'       => 5, // dr. Siti Rahma
                'polyclinic_id' => 3, // Poli Anak
                'nik'           => '3171016010880003',
                'sip'           => 'SIP/503/012/IDAI/2023',
                'name'          => 'dr. Siti Rahma, Sp.A',
                'specialization'=> 'Spesialis Anak',
                'phone'         => '081234567803',
                'email'         => 'dr.siti@rumahsakit.com',
                'gender'        => 'P',
                'status'        => 'active',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'id'            => 4,
                'user_id'       => 6, // dr. Hendra Pratama
                'polyclinic_id' => 4, // Poli Penyakit Dalam
                'nik'           => '3171011802790004',
                'sip'           => 'SIP/503/019/PAPDI/2020',
                'name'          => 'dr. Hendra Pratama, Sp.PD',
                'specialization'=> 'Spesialis Penyakit Dalam',
                'phone'         => '081234567804',
                'email'         => 'dr.hendra@rumahsakit.com',
                'gender'        => 'L',
                'status'        => 'active',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];
        $this->db->table('doctors')->insertBatch($doctorsData);

        // ==========================================
        // 7. SEED PATIENTS (Master Pasien - Unique RM & NIK)
        // ==========================================
        $patientsData = [
            [
                'id'               => 1,
                'no_rm'            => 'RM-2026-0001',
                'nik'              => '3201011503850001',
                'name'             => 'Ahmad Dahlan',
                'gender'           => 'L',
                'pob'              => 'Jakarta',
                'dob'              => '1985-03-15',
                'phone'            => '081311223344',
                'address'          => 'Jl. Merdeka No. 12, Kebayoran Baru, Jakarta Selatan',
                'blood_type'       => 'O',
                'insurance_type'   => 'BPJS',
                'insurance_number' => '0001234567891',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'id'               => 2,
                'no_rm'            => 'RM-2026-0002',
                'nik'              => '3201015208920002',
                'name'             => 'Siti Aminah',
                'gender'           => 'P',
                'pob'              => 'Bandung',
                'dob'              => '1992-08-12',
                'phone'            => '081322334455',
                'address'          => 'Jl. Mawar No. 45, Coblong, Bandung',
                'blood_type'       => 'A',
                'insurance_type'   => 'UMUM',
                'insurance_number' => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'id'               => 3,
                'no_rm'            => 'RM-2026-0003',
                'nik'              => '3201012011900003',
                'name'             => 'Bambang Pamungkas',
                'gender'           => 'L',
                'pob'              => 'Semarang',
                'dob'              => '1990-11-20',
                'phone'            => '081333445566',
                'address'          => 'Jl. Pemuda No. 88, Semarang Tengah',
                'blood_type'       => 'B',
                'insurance_type'   => 'BPJS',
                'insurance_number' => '0001234567893',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'id'               => 4,
                'no_rm'            => 'RM-2026-0004',
                'nik'              => '3201016504980004',
                'name'             => 'Dewi Lestari',
                'gender'           => 'P',
                'pob'              => 'Surakarta',
                'dob'              => '1998-04-25',
                'phone'            => '081344556677',
                'address'          => 'Jl. Slamet Riyadi No. 102, Surakarta',
                'blood_type'       => 'AB',
                'insurance_type'   => 'UMUM',
                'insurance_number' => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'id'               => 5,
                'no_rm'            => 'RM-2026-0005',
                'nik'              => '3201011001010005',
                'name'             => 'Eko Prasetyo',
                'gender'           => 'L',
                'pob'              => 'Yogyakarta',
                'dob'              => '2001-01-10',
                'phone'            => '081355667788',
                'address'          => 'Jl. Malioboro No. 15, Yogyakarta',
                'blood_type'       => 'O',
                'insurance_type'   => 'BPJS',
                'insurance_number' => '0001234567895',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'id'               => 6,
                'no_rm'            => 'RM-2026-0006',
                'nik'              => '3201014506150006',
                'name'             => 'Fitriani Putri',
                'gender'           => 'P',
                'pob'              => 'Surabaya',
                'dob'              => '2015-06-05', // Pasien Anak
                'phone'            => '081366778899',
                'address'          => 'Jl. Raya Darmo No. 50, Surabaya',
                'blood_type'       => 'A',
                'insurance_type'   => 'UMUM',
                'insurance_number' => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];
        $this->db->table('patients')->insertBatch($patientsData);

        // ==========================================
        // 8. SEED REGISTRATIONS (Pendaftaran Pasien Hari Ini)
        // Simulasi Kunjungan Hari Ini untuk Menguji Statistik Dashboard
        // ==========================================
        $registrationsData = [
            // Pasien 1 -> Poli Umum (Dokter Agus) -> Status SELESAI
            [
                'id'                  => 1,
                'registration_number' => 'REG/' . date('Ymd') . '/001',
                'registration_date'   => $today,
                'patient_id'          => 1,
                'polyclinic_id'       => 1, // Poli Umum
                'doctor_id'           => 1, // dr. Agus
                'queue_number'        => 1,
                'payment_type'        => 'BPJS',
                'complaint'           => 'Demam tinggi sejak 2 hari yang lalu disertai sakit kepala dan lemas.',
                'status'              => 'SELESAI',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            // Pasien 2 -> Poli Gigi (Dokter Dewi) -> Status DIPERIKSA
            [
                'id'                  => 2,
                'registration_number' => 'REG/' . date('Ymd') . '/002',
                'registration_date'   => $today,
                'patient_id'          => 2,
                'polyclinic_id'       => 2, // Poli Gigi
                'doctor_id'           => 2, // drg. Dewi
                'queue_number'        => 1,
                'payment_type'        => 'UMUM',
                'complaint'           => 'Gigi geraham kanan bawah berlubang dan ngilu saat minum dingin.',
                'status'              => 'DIPERIKSA',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            // Pasien 3 -> Poli Penyakit Dalam (Dokter Hendra) -> Status MENUNGGU
            [
                'id'                  => 3,
                'registration_number' => 'REG/' . date('Ymd') . '/003',
                'registration_date'   => $today,
                'patient_id'          => 3,
                'polyclinic_id'       => 4, // Poli Penyakit Dalam
                'doctor_id'           => 4, // dr. Hendra
                'queue_number'        => 1,
                'payment_type'        => 'BPJS',
                'complaint'           => 'Nyeri ulu hati persisten dan sering mual setelah makan.',
                'status'              => 'MENUNGGU',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            // Pasien 6 -> Poli Anak (Dokter Siti) -> Status MENUNGGU
            [
                'id'                  => 4,
                'registration_number' => 'REG/' . date('Ymd') . '/004',
                'registration_date'   => $today,
                'patient_id'          => 6,
                'polyclinic_id'       => 3, // Poli Anak
                'doctor_id'           => 3, // dr. Siti
                'queue_number'        => 1,
                'payment_type'        => 'UMUM',
                'complaint'           => 'Batuk pilek dan nafsu makan menurun selama 3 hari.',
                'status'              => 'MENUNGGU',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            // Pasien 5 -> Poli Umum (Dokter Agus) -> Status BATAL
            [
                'id'                  => 5,
                'registration_number' => 'REG/' . date('Ymd') . '/005',
                'registration_date'   => $today,
                'patient_id'          => 5,
                'polyclinic_id'       => 1, // Poli Umum
                'doctor_id'           => 1, // dr. Agus
                'queue_number'        => 2,
                'payment_type'        => 'BPJS',
                'complaint'           => 'Kontrol rutin tekanan darah.',
                'status'              => 'BATAL',
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
        ];
        $this->db->table('registrations')->insertBatch($registrationsData);

        // ==========================================
        // 9. SEED EXAMINATIONS (Rekam Medis & Tanda-Tanda Vital)
        // ==========================================
        $examinationsData = [
            // Pemeriksaan Lengkap (Selesai) untuk Registrasi ID 1 (Pasien Ahmad Dahlan)
            [
                'id'               => 1,
                'registration_id'  => 1,
                'examiner_id'      => 3, // dr. Agus Wijaya (User ID 3)
                'examination_time' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                // Tanda-Tanda Vital
                'systolic_bp'      => 120,
                'diastolic_bp'     => 80,
                'pulse'            => 84,
                'temperature'      => 38.2,
                'respiratory_rate' => 18,
                'weight'           => 68.5,
                'height'           => 170.0,
                // Catatan Rekam Medis (SOAP)
                'subjective'       => 'Pasien mengeluh demam sejak 2 hari, disertai pusing dan nyeri otot ringan.',
                'objective'        => 'Tampak lemas, febris (Suhu 38.2°C), tenggorokan hiperemis (-), paru bersih.',
                'assessment'       => 'Febris e.c Suspek Infeksi Virus / Flu Biasa (ICD-10: R50.9)',
                'plan'             => '1. Parasetamol 3x500mg (prn demam)\n2. Vitamin C 1x500mg\n3. Istirahat cukup dan banyak minum air putih.',
                'notes'            => 'Bila demam menetap >3 hari, disarankan cek laboratorium Darah Rutin.',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            // Pemeriksaan Dalam Proses (Diperiksa) untuk Registrasi ID 2 (Pasien Siti Aminah)
            [
                'id'               => 2,
                'registration_id'  => 2,
                'examiner_id'      => 4, // drg. Dewi Lestari (User ID 4)
                'examination_time' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
                // Vital Signs Awal
                'systolic_bp'      => 115,
                'diastolic_bp'     => 75,
                'pulse'            => 78,
                'temperature'      => 36.6,
                'respiratory_rate' => 16,
                'weight'           => 54.0,
                'height'           => 160.0,
                // Catatan Rekam Medis Sementara
                'subjective'       => 'Gigi molar 1 kanan bawah terasa ngilu saat terkena makanan/minuman manis.',
                'objective'        => 'Karies profunda pada elemen 46, tes perkusi (-), sondasi (+).',
                'assessment'       => 'Pulpitis Reversibel Elemen 46 (ICD-10: K04.0)',
                'plan'             => 'Tindakan penambalan sementara (Eugenol) & rencana penambalan komposit minggu depan.',
                'notes'            => 'Pasien dianjurkan menjaga kebersihan mulut.',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];
        $this->db->table('examinations')->insertBatch($examinationsData);

        // Aktifkan kembali pemeriksaan FK
        $this->db->enableForeignKeyChecks();
    }
}
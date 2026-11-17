<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration Script: Sistem Informasi Pendaftaran Pasien Rawat Jalan & RBAC
 * 
 * Tabel yang dibuat:
 * 1. roles            - Level pengguna (Admin, Petugas Pendaftaran, Dokter)
 * 2. permissions      - Hak akses sistem (RBAC)
 * 3. role_permissions - Tabel pivot RBAC (Role <-> Permission)
 * 4. users            - Akun login pengguna sistem (FK ke roles)
 * 5. polyclinics      - Master Poliklinik
 * 6. doctors          - Master Dokter (FK ke polyclinics & users)
 * 7. patients         - Data Pasien (NIK & No. RM UNIQUE)
 * 8. registrations    - Pendaftaran Rawat Jalan (Pencegahan antrean ganda & status)
 * 9. examinations     - Rekam Medis & Tanda-Tanda Vital (FK ke registrations & users)
 */
class CreateHospitalOutpatientSystem extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        // ==========================================
        // 1. TABEL ROLES
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Kode Role (misal: admin, petugas, dokter)',
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Nama Tampilan Role (misal: Petugas Pendaftaran)',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('roles', true);

        // ==========================================
        // 2. TABEL PERMISSIONS
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Kode Permission (misal: patient.create, examination.view)',
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'module' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Grup Modul (misal: Pasien, Pendaftaran, Pemeriksaan)',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('permissions', true);

        // ==========================================
        // 3. TABEL ROLE_PERMISSIONS (PIVOT RBAC)
        // ==========================================
        $this->forge->addField([
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'permission_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);
        $this->forge->addKey(['role_id', 'permission_id'], true); // Composite Primary Key
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('role_permissions', true);

        // ==========================================
        // 4. TABEL USERS (Autentikasi Pengguna)
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'fullname' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('username');
        $this->forge->addUniqueKey('email');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('users', true);

        // ==========================================
        // 5. TABEL POLYCLINICS (Poliklinik)
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('polyclinics', true);

        // ==========================================
        // 6. TABEL DOCTORS (Dokter Terhubung ke Poliklinik & User)
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Opsional link ke akun user login dokter',
            ],
            'polyclinic_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Memastikan dokter terikat pada poliklinik tertentu',
            ],
            'nik' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'null'       => false,
            ],
            'sip' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Surat Izin Praktik',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'specialization' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['L', 'P'],
                'null'       => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nik');
        $this->forge->addUniqueKey('sip');
        $this->forge->addForeignKey('polyclinic_id', 'polyclinics', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('doctors', true);

        // ==========================================
        // 7. TABEL PATIENTS (Pasien - Mencegah Data Pasien Ganda)
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'no_rm' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'Nomor Rekam Medis Unik (RM-XXXXXX)',
            ],
            'nik' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'null'       => false,
                'comment'    => 'NIK 16 Digit Unik',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Nama Lengkap (Wajib Diisi)',
            ],
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['L', 'P'],
                'null'       => false,
            ],
            'pob' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Tempat Lahir',
            ],
            'dob' => [
                'type'    => 'DATE',
                'null'    => false,
                'comment' => 'Tanggal Lahir (Validasi <= hari ini di aplikasi)',
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'blood_type' => [
                'type'       => 'ENUM',
                'constraint' => ['A', 'B', 'AB', 'O', 'UNKNOWN'],
                'default'    => 'UNKNOWN',
            ],
            'insurance_type' => [
                'type'       => 'ENUM',
                'constraint' => ['UMUM', 'BPJS'],
                'default'    => 'UMUM',
            ],
            'insurance_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('no_rm'); // Solusi: Pencegahan No. RM Ganda
        $this->forge->addUniqueKey('nik');   // Solusi: Pencegahan Pasien Ganda berdasarkan NIK
        $this->forge->createTable('patients', true);

        // ==========================================
        // 8. TABEL REGISTRATIONS (Pendaftaran Pasien Rawat Jalan)
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'registration_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
                'comment'    => 'Nomor Pendaftaran Unik (REG/YYYYMMDD/XXX)',
            ],
            'registration_date' => [
                'type'    => 'DATE',
                'null'    => false,
                'comment' => 'Tanggal Kunjungan Berobat',
            ],
            'patient_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'polyclinic_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'doctor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'queue_number' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Informasi Nomor Antrean Pasien Poli Hari Ini',
            ],
            'payment_type' => [
                'type'       => 'ENUM',
                'constraint' => ['UMUM', 'BPJS'],
                'default'    => 'UMUM',
            ],
            'complaint' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Keluhan Utama Pasien',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['MENUNGGU', 'DIPERIKSA', 'SELESAI', 'BATAL'],
                'default'    => 'MENUNGGU',
                'comment'    => 'Status Antrean Kunjungan',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('registration_number');
        
        // Key Constraint: Pasien TIDAK BISA daftar ganda di Dokter & Tanggal yang sama
        $this->forge->addUniqueKey(['patient_id', 'doctor_id', 'registration_date'], 'uk_patient_doctor_date');

        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('polyclinic_id', 'polyclinics', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('doctor_id', 'doctors', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('registrations', true);

        // ==========================================
        // 9. TABEL EXAMINATIONS (Pemeriksaan & Tanda Vital)
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'registration_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'FK ke Pendaftaran (1 Pendaftaran = 1 Pemeriksaan)',
            ],
            'examiner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Nama/User Petugas atau Dokter Pemeriksa',
            ],
            'examination_time' => [
                'type' => 'DATETIME',
                'null' => false,
                'comment' => 'Waktu Pelaksanaan Pemeriksaan',
            ],
            // --- Fitur Pencatatan Tanda-Tanda Vital ---
            'systolic_bp' => [
                'type'       => 'INT',
                'constraint' => 3,
                'null'       => true,
                'comment'    => 'Tekanan Darah Sistolik (mmHg)',
            ],
            'diastolic_bp' => [
                'type'       => 'INT',
                'constraint' => 3,
                'null'       => true,
                'comment'    => 'Tekanan Darah Diastolik (mmHg)',
            ],
            'pulse' => [
                'type'       => 'INT',
                'constraint' => 3,
                'null'       => true,
                'comment'    => 'Denyut Nadi (x/menit)',
            ],
            'temperature' => [
                'type'       => 'DECIMAL',
                'constraint' => '4,1',
                'null'       => true,
                'comment'    => 'Suhu Tubuh (°C)',
            ],
            'respiratory_rate' => [
                'type'       => 'INT',
                'constraint' => 3,
                'null'       => true,
                'comment'    => 'Frekuensi Pernapasan (x/menit)',
            ],
            'weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Berat Badan (kg)',
            ],
            'height' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Tinggi Badan (cm)',
            ],
            // --- Catatan Medis ---
            'subjective' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Keluhan Pasien (Anamnesis)',
            ],
            'objective' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Hasil Pemeriksaan Fisik',
            ],
            'assessment' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Diagnosis Medis (ICD-10)',
            ],
            'plan' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Rencana Resep / Action Plan',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Catatan Tambahan Petugas',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('registration_id');
        $this->forge->addForeignKey('registration_id', 'registrations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('examiner_id', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('examinations', true);

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('examinations', true);
        $this->forge->dropTable('registrations', true);
        $this->forge->dropTable('patients', true);
        $this->forge->dropTable('doctors', true);
        $this->forge->dropTable('polyclinics', true);
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('roles', true);

        $this->db->enableForeignKeyChecks();
    }
}
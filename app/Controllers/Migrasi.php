<?php

namespace App\Controllers;

class Migrasi extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // 1. Tabel Skema Pajak (PPh 21)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'tax_schemes')
            CREATE TABLE tax_schemes (
                id INT IDENTITY(1,1) PRIMARY KEY,
                nama NVARCHAR(100) NOT NULL,
                metode NVARCHAR(50) DEFAULT 'Gross',
                ptkp_status NVARCHAR(10) DEFAULT 'TK/0',
                deskripsi NVARCHAR(MAX),
                bpjs_kes_karyawan DECIMAL(5,2) DEFAULT 1.0,
                bpjs_kes_perusahaan DECIMAL(5,2) DEFAULT 4.0,
                bpjs_kes_max_salary DECIMAL(15,2) DEFAULT 12000000.0,
                bpjs_jht_karyawan DECIMAL(5,2) DEFAULT 2.0,
                bpjs_jht_perusahaan DECIMAL(5,2) DEFAULT 3.7,
                bpjs_jp_karyawan DECIMAL(5,2) DEFAULT 1.0,
                bpjs_jp_perusahaan DECIMAL(5,2) DEFAULT 2.0,
                bpjs_jp_max_salary DECIMAL(15,2) DEFAULT 10024600.0,
                bpjs_jkk_perusahaan DECIMAL(5,2) DEFAULT 0.24,
                bpjs_jkm_perusahaan DECIMAL(5,2) DEFAULT 0.30,
                created_at DATETIME DEFAULT GETDATE()
            )");

        // Ensure BPJS columns exist in tax_schemes for older dbs
        $bpjsCols = [
            'bpjs_kes_karyawan' => 'DECIMAL(5,2) DEFAULT 1.0',
            'bpjs_kes_perusahaan' => 'DECIMAL(5,2) DEFAULT 4.0',
            'bpjs_kes_max_salary' => 'DECIMAL(15,2) DEFAULT 12000000.0',
            'bpjs_jht_karyawan' => 'DECIMAL(5,2) DEFAULT 2.0',
            'bpjs_jht_perusahaan' => 'DECIMAL(5,2) DEFAULT 3.7',
            'bpjs_jp_karyawan' => 'DECIMAL(5,2) DEFAULT 1.0',
            'bpjs_jp_perusahaan' => 'DECIMAL(5,2) DEFAULT 2.0',
            'bpjs_jp_max_salary' => 'DECIMAL(15,2) DEFAULT 10024600.0',
            'bpjs_jkk_perusahaan' => 'DECIMAL(5,2) DEFAULT 0.24',
            'bpjs_jkm_perusahaan' => 'DECIMAL(5,2) DEFAULT 0.30'
        ];
        foreach ($bpjsCols as $col => $definition) {
            $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('tax_schemes') AND name = '$col')
                ALTER TABLE tax_schemes ADD $col $definition");
        }

        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('tax_schemes') AND name = 'tipe')
            ALTER TABLE tax_schemes ADD tipe NVARCHAR(20) DEFAULT 'pph21'");
        $db->query("UPDATE tax_schemes SET tipe = 'pph21' WHERE tipe IS NULL");


        // 2. Tabel Mapping (Menempelkan Skema ke Klien)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'client_payroll_configs')
            CREATE TABLE client_payroll_configs (
                id INT IDENTITY(1,1) PRIMARY KEY,
                client_id INT,
                payroll_scheme_id INT,
                tax_scheme_id INT,
                compensation_scheme_id INT,
                pay_date INT DEFAULT 25,
                cutoff_start INT DEFAULT 21,
                cutoff_end INT DEFAULT 20,
                created_at DATETIME DEFAULT GETDATE()
            )");
            
        // Ensure compensation_scheme_id column exists for older dbs
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('client_payroll_configs') AND name = 'compensation_scheme_id')
            ALTER TABLE client_payroll_configs ADD compensation_scheme_id INT");

        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('client_payroll_configs') AND name = 'bpjs_scheme_id')
            ALTER TABLE client_payroll_configs ADD bpjs_scheme_id INT NULL");

        // Ensure payroll_type, minimum_wage_id, and custom_nominal columns exist for wage integration
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('client_payroll_configs') AND name = 'payroll_type')
            ALTER TABLE client_payroll_configs ADD payroll_type NVARCHAR(20) NULL");

        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('client_payroll_configs') AND name = 'minimum_wage_id')
            ALTER TABLE client_payroll_configs ADD minimum_wage_id INT NULL");

        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('client_payroll_configs') AND name = 'custom_nominal')
            ALTER TABLE client_payroll_configs ADD custom_nominal DECIMAL(15,2) NULL");

        // 3. Tabel PKWT (Kontrak Kerja)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'pkwt')
            CREATE TABLE pkwt (
                id INT IDENTITY(1,1) PRIMARY KEY,
                client_id INT,
                employee_name NVARCHAR(255) NOT NULL,
                position_name NVARCHAR(255),
                start_date DATE,
                end_date DATE,
                status NVARCHAR(20) DEFAULT 'Active',
                created_at DATETIME DEFAULT GETDATE()
            )");

        // Ensure tipe_perjanjian column exists in pkwt table for contract types (PKWT, PKWTT, PKHL)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('pkwt') AND name = 'tipe_perjanjian')
            ALTER TABLE pkwt ADD tipe_perjanjian NVARCHAR(50) NULL");

        // 4. Tabel Rincian Gaji PKWT
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'pkwt_components')
            CREATE TABLE pkwt_components (
                id INT IDENTITY(1,1) PRIMARY KEY,
                pkwt_id INT,
                nama NVARCHAR(100),
                tipe NVARCHAR(20),
                nilai DECIMAL(15,2),
                is_persentase BIT DEFAULT 0,
                created_at DATETIME DEFAULT GETDATE()
            )");

        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('pkwt_components') AND name = 'jenis_komponen')
            ALTER TABLE pkwt_components ADD jenis_komponen NVARCHAR(50) DEFAULT 'kompensasi'");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('pkwt_components') AND name = 'sifat_kompensasi')
            ALTER TABLE pkwt_components ADD sifat_kompensasi NVARCHAR(20) DEFAULT 'tetap'");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('pkwt_components') AND name = 'sumber_nilai')
            ALTER TABLE pkwt_components ADD sumber_nilai NVARCHAR(50) DEFAULT 'nominal'");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('pkwt_components') AND name = 'periode')
            ALTER TABLE pkwt_components ADD periode NVARCHAR(20) DEFAULT 'bulan'");

        // 5. Tabel Periode Gaji
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'payroll_periods')
            CREATE TABLE payroll_periods (
                id INT IDENTITY(1,1) PRIMARY KEY,
                nama NVARCHAR(50) NOT NULL,
                bulan INT,
                tahun INT,
                status NVARCHAR(20) DEFAULT 'Open',
                created_at DATETIME DEFAULT GETDATE()
            )");

        // 6. Tabel Data Cut-Off (Absensi & Lembur)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'payroll_attendance')
            CREATE TABLE payroll_attendance (
                id INT IDENTITY(1,1) PRIMARY KEY,
                period_id INT,
                pkwt_id INT,
                hari_kerja INT DEFAULT 0,
                jam_lembur DECIMAL(10,2) DEFAULT 0,
                potongan_absensi DECIMAL(15,2) DEFAULT 0,
                bonus_tambahan DECIMAL(15,2) DEFAULT 0,
                created_at DATETIME DEFAULT GETDATE()
            )");

        // 7. Tabel Hasil Gaji Akhir
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'payroll_final')
            CREATE TABLE payroll_final (
                id INT IDENTITY(1,1) PRIMARY KEY,
                period_id INT,
                pkwt_id INT,
                total_pendapatan DECIMAL(15,2),
                total_potongan DECIMAL(15,2),
                take_home_pay DECIMAL(15,2),
                status_approval NVARCHAR(20) DEFAULT 'Pending',
                approved_by NVARCHAR(100),
                bpjs_kes_karyawan DECIMAL(15,2) DEFAULT 0,
                bpjs_kes_perusahaan DECIMAL(15,2) DEFAULT 0,
                bpjs_jht_karyawan DECIMAL(15,2) DEFAULT 0,
                bpjs_jht_perusahaan DECIMAL(15,2) DEFAULT 0,
                bpjs_jp_karyawan DECIMAL(15,2) DEFAULT 0,
                bpjs_jp_perusahaan DECIMAL(15,2) DEFAULT 0,
                bpjs_jkk_perusahaan DECIMAL(15,2) DEFAULT 0,
                bpjs_jkm_perusahaan DECIMAL(15,2) DEFAULT 0,
                pph21 DECIMAL(15,2) DEFAULT 0,
                tax_allowance DECIMAL(15,2) DEFAULT 0,
                tax_method NVARCHAR(20) DEFAULT 'Gross',
                ptkp_status NVARCHAR(10) DEFAULT 'TK/0',
                gaji_pokok DECIMAL(15,2) DEFAULT 0,
                potongan_absen DECIMAL(15,2) DEFAULT 0,
                jam_lembur DECIMAL(10,2) DEFAULT 0,
                lembur_pay DECIMAL(15,2) DEFAULT 0,
                bonus_tambahan DECIMAL(15,2) DEFAULT 0,
                raw_components NVARCHAR(MAX),
                created_at DATETIME DEFAULT GETDATE()
            )");

        // Ensure new calculation columns exist in payroll_final for older dbs
        $finalCols = [
            'bpjs_kes_karyawan' => 'DECIMAL(15,2) DEFAULT 0',
            'bpjs_kes_perusahaan' => 'DECIMAL(15,2) DEFAULT 0',
            'bpjs_jht_karyawan' => 'DECIMAL(15,2) DEFAULT 0',
            'bpjs_jht_perusahaan' => 'DECIMAL(15,2) DEFAULT 0',
            'bpjs_jp_karyawan' => 'DECIMAL(15,2) DEFAULT 0',
            'bpjs_jp_perusahaan' => 'DECIMAL(15,2) DEFAULT 0',
            'bpjs_jkk_perusahaan' => 'DECIMAL(15,2) DEFAULT 0',
            'bpjs_jkm_perusahaan' => 'DECIMAL(15,2) DEFAULT 0',
            'pph21' => 'DECIMAL(15,2) DEFAULT 0',
            'tax_allowance' => 'DECIMAL(15,2) DEFAULT 0',
            'tax_method' => "NVARCHAR(20) DEFAULT 'Gross'",
            'ptkp_status' => "NVARCHAR(10) DEFAULT 'TK/0'",
            'gaji_pokok' => 'DECIMAL(15,2) DEFAULT 0',
            'potongan_absen' => 'DECIMAL(15,2) DEFAULT 0',
            'jam_lembur' => 'DECIMAL(10,2) DEFAULT 0',
            'lembur_pay' => 'DECIMAL(15,2) DEFAULT 0',
            'bonus_tambahan' => 'DECIMAL(15,2) DEFAULT 0',
            'raw_components' => 'NVARCHAR(MAX)'
        ];
        foreach ($finalCols as $col => $definition) {
            $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_final') AND name = '$col')
                ALTER TABLE payroll_final ADD $col $definition");
        }


        // 8. Tabel UMP/UMK (Minimum Wages)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'minimum_wages')
            CREATE TABLE minimum_wages (
                id INT IDENTITY(1,1) PRIMARY KEY,
                tipe NVARCHAR(10),
                kode_daerah NVARCHAR(20),
                nama_daerah NVARCHAR(100),
                provinsi NVARCHAR(100),
                nominal DECIMAL(15,2),
                tahun INT,
                created_at DATETIME DEFAULT GETDATE()
            )");

        // 9. Tabel Kompensasi Klien (Basic Salary, Meal, dll) (LEGACY)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'client_compensations')
            CREATE TABLE client_compensations (
                id INT IDENTITY(1,1) PRIMARY KEY,
                client_id INT,
                nama NVARCHAR(100) NOT NULL,
                tipe NVARCHAR(20) DEFAULT 'pendapatan',
                nominal DECIMAL(15,2) DEFAULT 0,
                created_at DATETIME DEFAULT GETDATE()
            )");

        // 9b. Master Skema Kompensasi (Global Templates)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'compensation_schemes')
            CREATE TABLE compensation_schemes (
                id INT IDENTITY(1,1) PRIMARY KEY,
                nama NVARCHAR(100) NOT NULL,
                deskripsi NVARCHAR(MAX),
                created_at DATETIME DEFAULT GETDATE()
            )");

        // 9c. Master Komponen Kompensasi (Items in Templates)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'compensation_components')
            CREATE TABLE compensation_components (
                id INT IDENTITY(1,1) PRIMARY KEY,
                scheme_id INT,
                nama NVARCHAR(100) NOT NULL,
                tipe NVARCHAR(20) DEFAULT 'pendapatan',
                nilai DECIMAL(15,2) DEFAULT 0,
                is_persentase BIT DEFAULT 0,
                created_at DATETIME DEFAULT GETDATE()
            )");

        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('compensation_components') AND name = 'tipe')
            ALTER TABLE compensation_components ADD tipe NVARCHAR(20) DEFAULT 'pendapatan'");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('compensation_components') AND name = 'nilai')
            ALTER TABLE compensation_components ADD nilai DECIMAL(15,2) DEFAULT 0");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('compensation_components') AND name = 'is_persentase')
            ALTER TABLE compensation_components ADD is_persentase BIT DEFAULT 0");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('compensation_components') AND name = 'jenis_komponen')
            ALTER TABLE compensation_components ADD jenis_komponen NVARCHAR(50) DEFAULT 'kompensasi'");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('compensation_components') AND name = 'sumber_nilai')
            ALTER TABLE compensation_components ADD sumber_nilai NVARCHAR(50) DEFAULT 'nominal'");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('compensation_components') AND name = 'periode')
            ALTER TABLE compensation_components ADD periode NVARCHAR(20) DEFAULT 'bulan'");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('compensation_components') AND name = 'sifat_kompensasi')
            ALTER TABLE compensation_components ADD sifat_kompensasi NVARCHAR(20) DEFAULT 'tetap'");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'client_absence_configs')
            CREATE TABLE client_absence_configs (
                id INT IDENTITY(1,1) PRIMARY KEY,
                client_id INT,
                prorate INT DEFAULT 0,
                absen_tidak_potong INT DEFAULT 0,
                created_at DATETIME DEFAULT GETDATE()
            )");

        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('client_absence_configs') AND name = 'nominal_potongan')
            ALTER TABLE client_absence_configs ADD nominal_potongan DECIMAL(15,2) DEFAULT 0");

        // 11. Tambah kolom level pada positions (jika belum ada)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('positions') AND name = 'level')
            ALTER TABLE positions ADD level NVARCHAR(50) DEFAULT ''");

        // Tambah kolom hari_kerja pada positions (jika belum ada)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('positions') AND name = 'hari_kerja')
            ALTER TABLE positions ADD hari_kerja INT DEFAULT 5");

        // 12. Tambah kolom alamat pada employees (jika belum ada)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('employees') AND name = 'alamat')
            ALTER TABLE employees ADD alamat NVARCHAR(MAX) NULL");

        // 13. Tambah tabel status_logs (tanpa primary key)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'status_logs')
            CREATE TABLE status_logs (
                description NVARCHAR(255),
                user_action NVARCHAR(100),
                created_at DATETIME DEFAULT GETDATE()
            )");
        
        // Fix column naming for existing tables
        $db->query("IF EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('status_logs') AND name = 'action')
            EXEC sp_rename 'status_logs.action', 'description', 'COLUMN'");
        $db->query("IF EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('system_logs') AND name = 'status_log')
            EXEC sp_rename 'system_logs.status_log', 'description', 'COLUMN'");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('system_logs') AND name = 'user_name')
            ALTER TABLE system_logs ADD user_name NVARCHAR(100) NULL");

        // 14. Pastikan kolom-kolom baru ada di payroll_components untuk sinkronisasi UMP/UMK
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_components') AND name = 'jenis_komponen')
            ALTER TABLE payroll_components ADD jenis_komponen NVARCHAR(50) DEFAULT 'basic_salary'");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_components') AND name = 'sumber_nilai')
            ALTER TABLE payroll_components ADD sumber_nilai NVARCHAR(50) DEFAULT 'nominal'");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_components') AND name = 'periode')
            ALTER TABLE payroll_components ADD periode NVARCHAR(20) DEFAULT 'bulan'");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_components') AND name = 'sifat_kompensasi')
            ALTER TABLE payroll_components ADD sifat_kompensasi NVARCHAR(20) DEFAULT 'tetap'");

        // 15. Pastikan kolom-kolom baru ada di payroll_schemes untuk kompensasi & absen
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_schemes') AND name = 'compensation_scheme_id')
            ALTER TABLE payroll_schemes ADD compensation_scheme_id INT NULL");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_schemes') AND name = 'prorate')
            ALTER TABLE payroll_schemes ADD prorate INT DEFAULT 0");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_schemes') AND name = 'absen_tidak_potong')
            ALTER TABLE payroll_schemes ADD absen_tidak_potong INT DEFAULT 0");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_schemes') AND name = 'nominal_potongan')
            ALTER TABLE payroll_schemes ADD nominal_potongan DECIMAL(15,2) DEFAULT 0");

        // 16. Relaksasi kolom legacy payroll_components agar scheme-based insert berfungsi
        $db->query("ALTER TABLE payroll_components ALTER COLUMN client_id INT NULL");
        $db->query("ALTER TABLE payroll_components ALTER COLUMN nama_komponen VARCHAR(100) NULL");
        $db->query("ALTER TABLE payroll_components ALTER COLUMN tipe VARCHAR(20) NULL");
        $db->query("ALTER TABLE payroll_components ALTER COLUMN jenis_nilai VARCHAR(20) NULL");

        // 17. Tambahkan kolom division_id, department_id, dan position_id di client_payroll_configs
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('client_payroll_configs') AND name = 'division_id')
            ALTER TABLE client_payroll_configs ADD division_id INT NULL");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('client_payroll_configs') AND name = 'department_id')
            ALTER TABLE client_payroll_configs ADD department_id INT NULL");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('client_payroll_configs') AND name = 'position_id')
            ALTER TABLE client_payroll_configs ADD position_id INT NULL");
        // 18. Tabel Global STO (Struktur Organisasi Global)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'global_divisions')
            CREATE TABLE global_divisions (
                id INT IDENTITY(1,1) PRIMARY KEY,
                nama NVARCHAR(255) NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )");

        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'global_departments')
            CREATE TABLE global_departments (
                id INT IDENTITY(1,1) PRIMARY KEY,
                nama NVARCHAR(255) NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )");

        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'global_positions')
            CREATE TABLE global_positions (
                id INT IDENTITY(1,1) PRIMARY KEY,
                nama NVARCHAR(255) NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )");

        // 19. Tambahkan kolom flag BPJS & PPh21 untuk 6 tunjangan di payroll_scheme_templates
        $allowanceFlags = [
            'bpjs_inc_transport' => 'TINYINT DEFAULT 0',
            'pph_inc_transport' => 'TINYINT DEFAULT 1',
            'bpjs_inc_makan' => 'TINYINT DEFAULT 0',
            'pph_inc_makan' => 'TINYINT DEFAULT 1',
            'bpjs_inc_komunikasi' => 'TINYINT DEFAULT 1',
            'pph_inc_komunikasi' => 'TINYINT DEFAULT 1',
            'bpjs_inc_jabatan' => 'TINYINT DEFAULT 1',
            'pph_inc_jabatan' => 'TINYINT DEFAULT 1',
            'bpjs_inc_kehadiran' => 'TINYINT DEFAULT 0',
            'pph_inc_kehadiran' => 'TINYINT DEFAULT 1',
            'bpjs_inc_kinerja' => 'TINYINT DEFAULT 0',
            'pph_inc_kinerja' => 'TINYINT DEFAULT 1',
        ];
        foreach ($allowanceFlags as $col => $def) {
            $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('payroll_scheme_templates') AND name = '$col')
                ALTER TABLE payroll_scheme_templates ADD $col $def");
        }

        // 20. Tambahkan kolom is_bpjs dan is_pph21 ke tabel compensation_components dan pkwt_components
        $compFlags = [
            'is_bpjs' => 'TINYINT DEFAULT 0',
            'is_pph21' => 'TINYINT DEFAULT 1',
        ];
        foreach ($compFlags as $col => $def) {
            $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('compensation_components') AND name = '$col')
                ALTER TABLE compensation_components ADD $col $def");
            $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('pkwt_components') AND name = '$col')
                ALTER TABLE pkwt_components ADD $col $def");
        }

        return "Migrasi Berhasil! (semua tabel termasuk kompensasi, absensi, kolom level posisi, kolom alamat karyawan, tabel status log, kolom payroll_components, kolom baru payroll_schemes, kolom departemen/posisi client_payroll_configs, tabel global STO, dan flag BPJS/PPh21 tunjangan)";
    }

    /**
     * Seed data UMP 34 provinsi Indonesia
     * Akses: /migrasi/seed-ump
     */
    public function seedUmp()
    {
        $db = \Config\Database::connect();
        $tahun = date('Y');
        
        // Hapus data lama UMP tahun ini agar tidak duplikat
        $db->table('minimum_wages')->where('tipe', 'UMP')->where('tahun', $tahun)->delete();

        $provinces = [
            ['ID 11', 'ACEH', 3413666],
            ['ID 12', 'SUMATERA UTARA', 2710493],
            ['ID 13', 'SUMATERA BARAT', 2742476],
            ['ID 14', 'RIAU', 3191662],
            ['ID 15', 'JAMBI', 2944448],
            ['ID 16', 'SUMATERA SELATAN', 3456874],
            ['ID 17', 'BENGKULU', 2515000],
            ['ID 18', 'LAMPUNG', 2633284],
            ['ID 19', 'KEP. BANGKA BELITUNG', 3498479],
            ['ID 21', 'KEP. RIAU', 3279194],
            ['ID 31', 'DKI JAKARTA', 5067381],
            ['ID 32', 'JAWA BARAT', 2057495],
            ['ID 33', 'JAWA TENGAH', 2036947],
            ['ID 34', 'DI YOGYAKARTA', 2125897],
            ['ID 35', 'JAWA TIMUR', 2165244],
            ['ID 36', 'BANTEN', 2661280],
            ['ID 51', 'BALI', 2813672],
            ['ID 52', 'NUSA TENGGARA BARAT', 2371407],
            ['ID 53', 'NUSA TENGGARA TIMUR', 2123994],
            ['ID 61', 'KALIMANTAN BARAT', 2608601],
            ['ID 62', 'KALIMANTAN TENGAH', 3181013],
            ['ID 63', 'KALIMANTAN SELATAN', 3140443],
            ['ID 64', 'KALIMANTAN TIMUR', 3202100],
            ['ID 65', 'KALIMANTAN UTARA', 3251702],
            ['ID 71', 'SULAWESI UTARA', 3485000],
            ['ID 72', 'SULAWESI TENGAH', 2599546],
            ['ID 73', 'SULAWESI SELATAN', 3385145],
            ['ID 74', 'SULAWESI TENGGARA', 2758984],
            ['ID 75', 'GORONTALO', 2989350],
            ['ID 76', 'SULAWESI BARAT', 2678869],
            ['ID 81', 'MALUKU', 2816554],
            ['ID 82', 'MALUKU UTARA', 2976720],
            ['ID 91', 'PAPUA', 3864696],
            ['ID 92', 'PAPUA BARAT', 3282000],
        ];

        foreach ($provinces as $prov) {
            $db->table('minimum_wages')->insert([
                'tipe'         => 'UMP',
                'kode_daerah'  => $prov[0],
                'nama_daerah'  => $prov[1],
                'nominal'      => $prov[2],
                'tahun'        => $tahun,
            ]);
        }

        return "Berhasil seed " . count($provinces) . " data UMP provinsi Indonesia tahun " . $tahun . "!";
    }
}

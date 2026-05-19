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
                created_at DATETIME DEFAULT GETDATE()
            )");

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
                created_at DATETIME DEFAULT GETDATE()
            )");

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

        // Ensure compensation_components has all required columns (for older tables)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('compensation_components') AND name = 'tipe')
            ALTER TABLE compensation_components ADD tipe NVARCHAR(20) DEFAULT 'pendapatan'");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('compensation_components') AND name = 'nilai')
            ALTER TABLE compensation_components ADD nilai DECIMAL(15,2) DEFAULT 0");
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('compensation_components') AND name = 'is_persentase')
            ALTER TABLE compensation_components ADD is_persentase BIT DEFAULT 0");

        // 10. Tabel Konfigurasi Absensi Klien
        $db->query("IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'client_absence_configs')
            CREATE TABLE client_absence_configs (
                id INT IDENTITY(1,1) PRIMARY KEY,
                client_id INT,
                prorate INT DEFAULT 0,
                absen_tidak_potong INT DEFAULT 0,
                created_at DATETIME DEFAULT GETDATE()
            )");

        // 11. Tambah kolom level pada positions (jika belum ada)
        $db->query("IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('positions') AND name = 'level')
            ALTER TABLE positions ADD level NVARCHAR(50) DEFAULT ''");

        return "Migrasi Berhasil! (semua tabel termasuk kompensasi, absensi, dan kolom level posisi)";
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

<?php

namespace App\Controllers;

class Migrasi extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // 1. Tabel Skema Pajak (PPh 21)
        $db->query("CREATE TABLE IF NOT EXISTS tax_schemes (
            id SERIAL PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            metode VARCHAR(50) DEFAULT 'Gross', -- Gross, Gross Up, Nett
            ptkp_status VARCHAR(10) DEFAULT 'TK/0',
            deskripsi TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 2. Tabel Mapping (Menempelkan Skema ke Klien)
        $db->query("CREATE TABLE IF NOT EXISTS client_payroll_configs (
            id SERIAL PRIMARY KEY,
            client_id INTEGER REFERENCES clients(id) ON DELETE CASCADE,
            payroll_scheme_id INTEGER REFERENCES payroll_schemes(id),
            tax_scheme_id INTEGER REFERENCES tax_schemes(id),
            pay_date INTEGER DEFAULT 25, -- Tanggal gajian
            cutoff_start INTEGER DEFAULT 21,
            cutoff_end INTEGER DEFAULT 20,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 3. Tabel PKWT (Kontrak Kerja)
        $db->query("CREATE TABLE IF NOT EXISTS pkwt (
            id SERIAL PRIMARY KEY,
            client_id INTEGER REFERENCES clients(id),
            employee_name VARCHAR(255) NOT NULL,
            position_name VARCHAR(255),
            start_date DATE,
            end_date DATE,
            status VARCHAR(20) DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 4. Tabel Rincian Gaji PKWT (Generated from Scheme)
        $db->query("CREATE TABLE IF NOT EXISTS pkwt_components (
            id SERIAL PRIMARY KEY,
            pkwt_id INTEGER REFERENCES pkwt(id) ON DELETE CASCADE,
            nama VARCHAR(100),
            tipe VARCHAR(20), -- pendapatan/potongan
            nilai DECIMAL(15,2),
            is_persentase BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 5. Tabel Periode Gaji (Mei 2024, Juni 2024, dll)
        $db->query("CREATE TABLE IF NOT EXISTS payroll_periods (
            id SERIAL PRIMARY KEY,
            nama VARCHAR(50) NOT NULL, -- Contoh: Mei 2024
            bulan INTEGER,
            tahun INTEGER,
            status VARCHAR(20) DEFAULT 'Open', -- Open, Locked (sudah gajian)
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 6. Tabel Data Cut-Off (Absensi & Lembur)
        $db->query("CREATE TABLE IF NOT EXISTS payroll_attendance (
            id SERIAL PRIMARY KEY,
            period_id INTEGER REFERENCES payroll_periods(id) ON DELETE CASCADE,
            pkwt_id INTEGER REFERENCES pkwt(id) ON DELETE CASCADE,
            hari_kerja INTEGER DEFAULT 0,
            jam_lembur DECIMAL(10,2) DEFAULT 0,
            potongan_absensi DECIMAL(15,2) DEFAULT 0,
            bonus_tambahan DECIMAL(15,2) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 7. Tabel Hasil Gaji Akhir
        $db->query("CREATE TABLE IF NOT EXISTS payroll_final (
            id SERIAL PRIMARY KEY,
            period_id INTEGER REFERENCES payroll_periods(id),
            pkwt_id INTEGER REFERENCES pkwt(id),
            total_pendapatan DECIMAL(15,2),
            total_potongan DECIMAL(15,2),
            take_home_pay DECIMAL(15,2),
            status_approval VARCHAR(20) DEFAULT 'Pending', -- Pending, Approved
            approved_by VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 8. Tabel UMP/UMK (Minimum Wages)
        $db->query("CREATE TABLE IF NOT EXISTS minimum_wages (
            id SERIAL PRIMARY KEY,
            tipe VARCHAR(10), -- UMP / UMK
            kode_daerah VARCHAR(20),
            nama_daerah VARCHAR(100),
            provinsi VARCHAR(100), -- Khusus UMK
            nominal DECIMAL(15,2),
            tahun INTEGER,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        return "Migrasi Tahap 4 (UMP/UMK) Berhasil!";
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


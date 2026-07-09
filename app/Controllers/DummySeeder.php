<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class DummySeeder extends Controller
{
    public function run()
    {
        $db = \Config\Database::connect();

        // ========== CLEAN TABLES FIRST ==========
        $db->table('contracts')->where('1=1')->delete();
        $db->table('attendances')->where('1=1')->delete();
        $db->table('payroll_details')->where('1=1')->delete();
        $db->table('payrolls')->where('1=1')->delete();
        $db->table('employees')->where('1=1')->delete();
        $db->table('work_locations')->where('1=1')->delete();
        $db->table('positions')->where('1=1')->delete();
        $db->table('departments')->where('1=1')->delete();
        $db->table('divisions')->where('1=1')->delete();
        $db->table('clients')->where('1=1')->delete();
        $db->table('employee_sequences')->where('1=1')->delete();

        // ========== HELPER DATA ==========
        $namaDepan = ['Andi','Budi','Citra','Dewi','Eko','Fani','Gita','Hendra','Irma','Joko',
            'Kartini','Lukman','Maya','Nanda','Oscar','Putri','Rudi','Sari','Tono','Umar',
            'Vina','Wawan','Xenia','Yudi','Zahra','Agus','Bambang','Dina','Eka','Fajar',
            'Gina','Hadi','Indra','Juli','Kiki','Lina','Maman','Nina','Oki','Pandu',
            'Rina','Siti','Tari','Umi','Vera','Wahyu','Yanti','Zaki','Ari','Bella',
            'Cahya','Doni','Elsa','Feri','Galih','Hani','Ivan','Joni','Kiki','Lia',
            'Mira','Nita','Oki','Pipit','Raka','Santi','Tika','Uli','Vivi','Wulan',
            'Yogi','Zara','Adit','Bayu','Chika','Dian','Erwin','Fitri','Gilang','Hana'];
        $namaAkhir = ['Pratama','Sari','Wijaya','Kusuma','Hidayat','Rahayu','Putra','Lestari',
            'Nugroho','Santoso','Wibowo','Handoko','Setiawan','Susanto','Gunawan','Hartono',
            'Saputra','Utami','Purnama','Dewi','Aryanto','Permana','Kurniawan','Suryadi',
            'Budiman','Ramadhan','Maulana','Fitriani','Anggraeni','Safitri'];

        $sektorList = ['Teknologi Informasi','Manufaktur','Perdagangan','Keuangan & Perbankan','Konstruksi & Properti'];
        $kota = ['Jakarta Selatan','Jakarta Pusat','Bandung','Surabaya','Yogyakarta','Semarang','Medan','Makassar','Tangerang','Bekasi'];
        $jalan = ['Jl. Sudirman','Jl. Thamrin','Jl. Gatot Subroto','Jl. HR Rasuna Said','Jl. Ahmad Yani','Jl. Diponegoro','Jl. Merdeka','Jl. Asia Afrika','Jl. Pemuda','Jl. Veteran'];
        $tempatLahir = ['Jakarta','Bandung','Surabaya','Semarang','Yogyakarta','Medan','Makassar','Denpasar','Malang','Solo','Palembang','Tangerang'];
        $bankList = ['BCA','BRI','Mandiri','BNI','CIMB Niaga','Danamon','Permata','BTN'];
        $statusNikah = ['Belum Kawin','Kawin','Cerai Hidup','Cerai Mati'];
        $ptkpList = ['TK/0','K/0','K/1','K/2','K/3'];
        $tipePerjanjian = ['PKWT','PKWTT'];

        // ========== 5 CLIENTS ==========
        $clients = [
            ['nama' => 'PT Maju Bersama Digital', 'sektor' => $sektorList[0]],
            ['nama' => 'PT Karya Mandiri Sejahtera', 'sektor' => $sektorList[1]],
            ['nama' => 'CV Berkah Abadi Trading', 'sektor' => $sektorList[2]],
            ['nama' => 'PT Finansia Gemilang', 'sektor' => $sektorList[3]],
            ['nama' => 'PT Graha Konstruksi Nusantara', 'sektor' => $sektorList[4]],
        ];
        $clientIds = [];
        $noKlien = 1001;
        foreach ($clients as $c) {
            $kotaRand = $kota[array_rand($kota)];
            $jalanRand = $jalan[array_rand($jalan)];
            $db->table('clients')->insert([
                'no_klien'   => 'KL-' . $noKlien++,
                'nama'       => $c['nama'],
                'email'      => strtolower(str_replace([' ', '.'], ['', ''], $c['nama'])) . '@mail.com',
                'telepon'    => '021' . rand(10000000, 99999999),
                'sektor'     => $c['sektor'],
                'nib'        => rand(1000000000000, 9999999999999),
                'npwp'       => rand(10, 99) . '.' . rand(100, 999) . '.' . rand(100, 999) . '.' . rand(1, 9) . '-' . rand(100, 999) . '.' . rand(100, 999),
                'tgl_gabung' => date('Y-m-d', strtotime('-' . rand(1, 5) . ' years -' . rand(0, 11) . ' months')),
                'alamat'     => $jalanRand . ' No. ' . rand(1, 200) . ', ' . $kotaRand,
                'status'     => 'Aktif',
            ]);
            $clientIds[] = $db->insertID();
        }

        // ========== 20 WORK LOCATIONS (4 per client) ==========
        $workLocationsTemplates = [
            [
                ['lokasi_kerja' => 'Maju Bersama HQ Jakarta', 'location_code' => 'MBD-HQ-JKT', 'provinsi' => 'DKI Jakarta', 'kota_kabupaten' => 'Jakarta Selatan'],
                ['lokasi_kerja' => 'MBD R&D Bandung', 'location_code' => 'MBD-RD-BDG', 'provinsi' => 'Jawa Barat', 'kota_kabupaten' => 'Bandung'],
                ['lokasi_kerja' => 'MBD Dev Center Yogyakarta', 'location_code' => 'MBD-DC-YOG', 'provinsi' => 'DI Yogyakarta', 'kota_kabupaten' => 'Yogyakarta'],
                ['lokasi_kerja' => 'MBD Support Surabaya', 'location_code' => 'MBD-SP-SBY', 'provinsi' => 'Jawa Timur', 'kota_kabupaten' => 'Surabaya'],
            ],
            [
                ['lokasi_kerja' => 'KMS Factory Karawang', 'location_code' => 'KMS-FAC-KRW', 'provinsi' => 'Jawa Barat', 'kota_kabupaten' => 'Karawang'],
                ['lokasi_kerja' => 'KMS Warehouse Bekasi', 'location_code' => 'KMS-WH-BKS', 'provinsi' => 'Jawa Barat', 'kota_kabupaten' => 'Bekasi'],
                ['lokasi_kerja' => 'KMS Assembly Tangerang', 'location_code' => 'KMS-ASM-TNG', 'provinsi' => 'Banten', 'kota_kabupaten' => 'Tangerang'],
                ['lokasi_kerja' => 'KMS Sales Jakarta', 'location_code' => 'KMS-SLS-JKT', 'provinsi' => 'DKI Jakarta', 'kota_kabupaten' => 'Jakarta Pusat'],
            ],
            [
                ['lokasi_kerja' => 'BAT Dist Center Surabaya', 'location_code' => 'BAT-DC-SBY', 'provinsi' => 'Jawa Timur', 'kota_kabupaten' => 'Surabaya'],
                ['lokasi_kerja' => 'BAT Warehouse Semarang', 'location_code' => 'BAT-WH-SMG', 'provinsi' => 'Jawa Tengah', 'kota_kabupaten' => 'Semarang'],
                ['lokasi_kerja' => 'BAT Branch Office Medan', 'location_code' => 'BAT-BO-MDN', 'provinsi' => 'Sumatera Utara', 'kota_kabupaten' => 'Medan'],
                ['lokasi_kerja' => 'BAT Branch Office Makassar', 'location_code' => 'BAT-BO-MKS', 'provinsi' => 'Sulawesi Selatan', 'kota_kabupaten' => 'Makassar'],
            ],
            [
                ['lokasi_kerja' => 'Finansia HQ Jakarta', 'location_code' => 'FG-HQ-JKT', 'provinsi' => 'DKI Jakarta', 'kota_kabupaten' => 'Jakarta Selatan'],
                ['lokasi_kerja' => 'Finansia Surabaya Branch', 'location_code' => 'FG-BR-SBY', 'provinsi' => 'Jawa Timur', 'kota_kabupaten' => 'Surabaya'],
                ['lokasi_kerja' => 'Finansia Bandung Branch', 'location_code' => 'FG-BR-BDG', 'provinsi' => 'Jawa Barat', 'kota_kabupaten' => 'Bandung'],
                ['lokasi_kerja' => 'Finansia Medan Branch', 'location_code' => 'FG-BR-MDN', 'provinsi' => 'Sumatera Utara', 'kota_kabupaten' => 'Medan'],
            ],
            [
                ['lokasi_kerja' => 'GKN Project Site Jakarta', 'location_code' => 'GKN-PRJ-JKT', 'provinsi' => 'DKI Jakarta', 'kota_kabupaten' => 'Jakarta Utara'],
                ['lokasi_kerja' => 'GKN Resort Project Bali', 'location_code' => 'GKN-PRJ-BAL', 'provinsi' => 'Bali', 'kota_kabupaten' => 'Badung'],
                ['lokasi_kerja' => 'GKN IKN Site Office', 'location_code' => 'GKN-IKN-PEN', 'provinsi' => 'Kalimantan Timur', 'kota_kabupaten' => 'Penajam Paser Utara'],
                ['lokasi_kerja' => 'GKN Residential Bandung', 'location_code' => 'GKN-PRJ-BDG', 'provinsi' => 'Jawa Barat', 'kota_kabupaten' => 'Bandung'],
            ],
        ];

        $clientWorkLocations = [];
        foreach ($clientIds as $idx => $cid) {
            $clientWorkLocations[$cid] = [];
            foreach ($workLocationsTemplates[$idx] as $wl) {
                $db->table('work_locations')->insert([
                    'client_id'      => $cid,
                    'lokasi_kerja'   => $wl['lokasi_kerja'],
                    'location_code'  => $wl['location_code'],
                    'provinsi'       => $wl['provinsi'],
                    'kota_kabupaten' => $wl['kota_kabupaten'],
                    'created_at'     => date('Y-m-d H:i:s'),
                    'updated_at'     => date('Y-m-d H:i:s'),
                ]);
                $clientWorkLocations[$cid][] = $db->insertID();
            }
        }

        // ========== 20 DIVISIONS (4 per client) ==========
        $divisionNames = [
            'Operasional','Keuangan','Sumber Daya Manusia','Teknologi Informasi',
            'Pemasaran','Produksi','Riset & Pengembangan','Legal & Compliance',
            'Pengadaan','Customer Service','Quality Assurance','Logistik',
            'Public Relations','Audit Internal','Business Development','Supply Chain',
            'Administrasi Umum','Desain & Kreatif','Data Analytics','Corporate Strategy'
        ];
        $divisionIds = [];
        $divIdx = 0;
        foreach ($clientIds as $cid) {
            for ($i = 0; $i < 4; $i++) {
                $db->table('divisions')->insert([
                    'nama'      => $divisionNames[$divIdx],
                    'client_id' => $cid,
                ]);
                $divisionIds[] = ['id' => $db->insertID(), 'client_id' => $cid];
                $divIdx++;
            }
        }

        // ========== 50 DEPARTMENTS (distribute across divisions) ==========
        $deptNames = [
            'Akuntansi','Pajak','Treasury','Rekrutmen','Pelatihan & Pengembangan',
            'Payroll & Benefit','Infrastruktur IT','Pengembangan Software','Cyber Security','Digital Marketing',
            'Brand Management','Content & Media','Produksi Lini A','Produksi Lini B','Quality Control',
            'Riset Produk','Inovasi Teknologi','Hukum Korporasi','Kepatuhan Regulasi','Procurement',
            'Vendor Management','Help Desk','Customer Success','Layanan Pelanggan','Audit Keuangan',
            'Audit Operasional','Pengembangan Bisnis','Kerjasama Strategis','Warehouse','Distribusi',
            'Hubungan Media','Komunikasi Korporat','Data Science','Business Intelligence','Perencanaan Strategis',
            'Administrasi','Sekretariat','UI/UX Design','Graphic Design','Motion & Video',
            'Manajemen Proyek','Pengendalian Mutu','Safety & Health','Facility Management','General Affairs',
            'Ekspor Impor','Hubungan Investor','Training Center','Document Control','IT Support'
        ];
        $departmentIds = [];
        for ($i = 0; $i < 50; $i++) {
            $div = $divisionIds[$i % count($divisionIds)];
            $db->table('departments')->insert([
                'nama'        => $deptNames[$i],
                'division_id' => $div['id'],
                'client_id'   => $div['client_id'],
            ]);
            $departmentIds[] = ['id' => $db->insertID(), 'client_id' => $div['client_id'], 'division_id' => $div['id']];
        }

        // ========== 80 POSITIONS (distribute across departments) ==========
        $positionNames = [
            'Staff','Senior Staff','Supervisor','Manager','Assistant Manager',
            'Junior Staff','Team Lead','Coordinator','Analyst','Senior Analyst',
            'Specialist','Senior Specialist','Officer','Senior Officer','Executive',
            'Director','Head of Division','Head of Department','General Manager','Vice President',
        ];
        $positionIds = [];
        for ($i = 0; $i < 80; $i++) {
            $dept = $departmentIds[$i % count($departmentIds)];
            $posName = $positionNames[$i % count($positionNames)];
            $level = '';
            if (in_array($posName, ['Staff','Junior Staff'])) $level = 'Staff';
            elseif (in_array($posName, ['Senior Staff','Officer','Senior Officer'])) $level = 'Senior';
            elseif (in_array($posName, ['Supervisor','Team Lead','Coordinator'])) $level = 'Supervisor';
            elseif (in_array($posName, ['Analyst','Senior Analyst','Specialist','Senior Specialist'])) $level = 'Specialist';
            elseif (in_array($posName, ['Assistant Manager','Manager','Head of Department'])) $level = 'Manager';
            else $level = 'Director';

            $db->table('positions')->insert([
                'nama'          => $posName,
                'level'         => $level,
                'employee_name' => '',
                'email'         => '',
                'phone'         => '',
                'department_id' => $dept['id'],
            ]);
            $positionIds[] = ['id' => $db->insertID(), 'client_id' => $dept['client_id']];
        }

        // ========== 100 EMPLOYEES ==========
        $usedNik = [];
        $usedEmail = [];
        $usedNames = [];

        // Prepare sequence tracking
        $seqCounters = [];

        for ($i = 0; $i < 100; $i++) {
            // Unique name
            do {
                $first = $namaDepan[array_rand($namaDepan)];
                $last = $namaAkhir[array_rand($namaAkhir)];
                $fullName = $first . ' ' . $last;
            } while (in_array($fullName, $usedNames));
            $usedNames[] = $fullName;

            // Unique email
            $emailBase = strtolower($first) . '.' . strtolower($last);
            $email = $emailBase . '@company.com';
            $suffix = 1;
            while (in_array($email, $usedEmail)) {
                $email = $emailBase . $suffix . '@company.com';
                $suffix++;
            }
            $usedEmail[] = $email;

            // Pick a position (and therefore a client)
            $pos = $positionIds[$i % count($positionIds)];
            $clientId = $pos['client_id'];

            // Random dates
            $tglMasuk = date('Y-m-d', strtotime('-' . rand(0, 4) . ' years -' . rand(0, 11) . ' months -' . rand(0, 28) . ' days'));
            $contractYear = date('Y', strtotime($tglMasuk));
            $tglLahir = date('Y-m-d', strtotime('-' . rand(22, 50) . ' years -' . rand(0, 11) . ' months'));
            $startContract = $tglMasuk;
            $endContract = date('Y-m-d', strtotime('+1 year', strtotime($startContract)));

            $gajiPokok = rand(4, 15) * 500000; // 2jt - 7.5jt
            $statusNk = $statusNikah[array_rand($statusNikah)];
            $jumlahAnak = ($statusNk === 'Belum Kawin') ? 0 : rand(0, 3);
            $ptkp = $ptkpList[array_rand($ptkpList)];
            $tipe = $tipePerjanjian[array_rand($tipePerjanjian)];
            $bank = $bankList[array_rand($bankList)];
            $noRek = rand(1000000000, 9999999999);

            // Employ ID / NIK (sequential)
            if (!isset($seqCounters[$contractYear])) {
                $seqCounters[$contractYear] = 0;
            }
            $seqCounters[$contractYear]++;
            $employId = $contractYear . str_pad($seqCounters[$contractYear], 5, '0', STR_PAD_LEFT);

            // Pick a work location
            $workLocs = $clientWorkLocations[$clientId] ?? [];
            $workLocationId = !empty($workLocs) ? $workLocs[array_rand($workLocs)] : null;

            $alamatEmp = $jalan[array_rand($jalan)] . ' No. ' . rand(1, 150) . ' RT ' . str_pad(rand(1,20),2,'0',STR_PAD_LEFT) . '/RW ' . str_pad(rand(1,10),2,'0',STR_PAD_LEFT) . ', ' . $kota[array_rand($kota)];

            $db->table('employees')->insert([
                'nik'               => $employId,
                'nama'              => $fullName,
                'email'             => $email,
                'no_rekening'       => $noRek,
                'bank_name'         => $bank,
                'ptkp'              => $ptkp,
                'gaji_pokok'        => $gajiPokok,
                'position_id'       => $pos['id'],
                'client_id'         => $clientId,
                'tgl_masuk'         => $tglMasuk,
                'status'            => 'Aktif',
                'alamat'            => $alamatEmp,
                'tempat_lahir'      => $tempatLahir[array_rand($tempatLahir)],
                'tanggal_lahir'     => $tglLahir,
                'npwp'              => rand(10, 99) . '.' . rand(100, 999) . '.' . rand(100, 999) . '.' . rand(1, 9) . '-' . rand(100, 999) . '.' . rand(100, 999),
                'start_contract'    => $startContract,
                'end_contract'      => $endContract,
                'tipe_perjanjian'   => $tipe,
                'status_pernikahan' => $statusNk,
                'jumlah_anak'       => $jumlahAnak,
                'employ_id'         => $employId,
                'work_location_id'  => $workLocationId,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);

            $empId = $db->insertID();

            // Also create a contract (PKWT)
            $db->table('contracts')->insert([
                'employee_id'   => $empId,
                'client_id'     => $clientId,
                'no_kontrak'    => 'PKWT-' . date('Ym', strtotime($startContract)) . '-' . str_pad($empId, 4, '0', STR_PAD_LEFT),
                'tgl_mulai'     => $startContract,
                'tgl_berakhir'  => $endContract,
                'gaji_pokok'    => $gajiPokok,
                'status_pkwt'   => 'Aktif',
            ]);
        }

        // Update employee_sequences table
        foreach ($seqCounters as $year => $lastSeq) {
            $existing = $db->table('employee_sequences')->where('year', $year)->get()->getRow();
            if ($existing) {
                $newSeq = max($existing->last_sequence, $lastSeq);
                $db->table('employee_sequences')->where('year', $year)->update(['last_sequence' => $newSeq]);
            } else {
                $db->table('employee_sequences')->insert(['year' => $year, 'last_sequence' => $lastSeq]);
            }
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Dummy data seeded successfully!',
            'summary' => [
                'clients'        => count($clientIds),
                'divisions'      => count($divisionIds),
                'departments'    => count($departmentIds),
                'positions'      => count($positionIds),
                'work_locations' => 20,
                'employees'      => 100,
            ]
        ]);
    }
}

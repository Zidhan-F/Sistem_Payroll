<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Api extends ResourceController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getNotifications()
    {
        $notifications = [];

        // 1. Check if there are active clients
        $clients = $this->db->table('clients')->get()->getResultArray();
        
        // Get current month and year
        $currentMonth = intval(date('n'));
        $currentYear = intval(date('Y'));
        
        $monthsWord = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthsWord[$currentMonth] ?? date('F');

        foreach ($clients as $client) {
            // Check if there is an active payroll period for this client, month, year
            $period = $this->db->table('payroll_periods')
                               ->where('client_id', $client['id'])
                               ->where('bulan', $currentMonth)
                               ->where('tahun', $currentYear)
                               ->get()
                               ->getRow();
            if (!$period) {
                $notifications[] = [
                    'id' => 'cutoff_' . $client['id'],
                    'type' => 'warning',
                    'title' => 'Cut-off Periode Belum Dibuat',
                    'message' => "Klien <strong>" . esc($client['nama']) . "</strong> belum memiliki tanggal cut-off / periode aktif untuk bulan " . esc($monthName) . " " . esc($currentYear) . "!",
                    'link' => 'klien',
                    'client_id' => intval($client['id']),
                    'client_name' => $client['nama'],
                    'client_sektor' => $client['sektor']
                ];
            } else {
                // Period exists, check if attendance is uploaded H-3 before cutoff
                $config = $this->db->table('client_payroll_configs')
                                   ->where('client_id', $client['id'])
                                   ->get()
                                   ->getRow();
                if ($config) {
                    $daysInMonth = date('t', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
                    $cutoffStart = $config->cutoff_start ? intval($config->cutoff_start) : 21;
                    $cutoffEnd = $config->cutoff_end ? intval($config->cutoff_end) : 20;
                    if ($cutoffStart <= 1) {
                        $cutoffDay = $daysInMonth;
                    } else {
                        $cutoffDay = min($cutoffEnd, $daysInMonth);
                    }
                    $cutoffEndDateStr = sprintf('%d-%02d-%02d', $currentYear, $currentMonth, $cutoffDay);
                    
                    $todayStr = date('Y-m-d');
                    $todayTime = strtotime($todayStr);
                    $cutoffTime = strtotime($cutoffEndDateStr);
                    $diffDays = intval(round(($cutoffTime - $todayTime) / 86400));

                    if ($diffDays <= 3) {
                        // Check if client has uploaded attendance
                        $employees = $this->db->table('employees')->where('client_id', $client['id'])->get()->getResultArray();
                        $employeeIds = array_column($employees, 'id');
                        
                        $hasUploaded = false;
                        if (!empty($employeeIds)) {
                            $payoutPeriodStr = $currentMonth . '-' . $currentYear;
                            $attendanceCount = $this->db->table('attendance_logs')
                                                        ->whereIn('employee_id', $employeeIds)
                                                        ->where('payout_period', $payoutPeriodStr)
                                                        ->countAllResults();
                            if ($attendanceCount > 0) {
                                    $hasUploaded = true;
                            }
                        }
                        
                        if (!$hasUploaded && !empty($employeeIds)) {
                            if ($diffDays >= 0) {
                                $daysText = $diffDays == 0 ? "hari ini adalah hari terakhir" : "tinggal " . $diffDays . " hari lagi";
                                $notifications[] = [
                                    'id' => 'attendance_cutoff_warning_' . $client['id'],
                                    'type' => 'warning',
                                    'title' => 'Absensi Belum Diunggah (H-' . $diffDays . ')',
                                    'message' => "Klien <strong>" . esc($client['nama']) . "</strong> belum mengunggah data absensi. Batas akhir cut-off adalah tanggal " . esc($cutoffDay) . " " . esc($monthName) . " " . esc($currentYear) . " (" . $daysText . ")!",
                                    'link' => 'attendance',
                                    'client_id' => intval($client['id']),
                                    'client_name' => $client['nama'],
                                    'client_sektor' => $client['sektor']
                                ];
                            } else {
                                $lateDays = abs($diffDays);
                                $notifications[] = [
                                    'id' => 'attendance_cutoff_late_' . $client['id'],
                                    'type' => 'error',
                                    'title' => 'Terlambat Mengunggah Absensi (Telat ' . $lateDays . ' Hari)',
                                    'message' => "Klien <strong>" . esc($client['nama']) . "</strong> terlambat mengunggah data absensi. Batas akhir cut-off adalah tanggal " . esc($cutoffDay) . " " . esc($monthName) . " " . esc($currentYear) . " (terlewat " . $lateDays . " hari)!",
                                    'link' => 'attendance',
                                    'client_id' => intval($client['id']),
                                    'client_name' => $client['nama'],
                                    'client_sektor' => $client['sektor']
                                ];
                            }
                        }
                    }
                }
            }
        }

        // 2. Check if there are any payrolls on hold (PKWT)
        $holdPayrolls = $this->db->table('payroll_final')
                                 ->select('payroll_final.*, pkwt.employee_name, clients.nama as client_name, clients.sektor as client_sektor, payroll_periods.bulan, payroll_periods.tahun, clients.id as client_id')
                                 ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                                 ->join('clients', 'clients.id = pkwt.client_id')
                                 ->join('payroll_periods', 'payroll_periods.id = payroll_final.period_id')
                                 ->where('payroll_final.status_approval', 'Hold')
                                 ->get()
                                 ->getResultArray();
                                 
        $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        foreach ($holdPayrolls as $hp) {
            $periodName = ($months[intval($hp['bulan']) - 1] ?? '') . " " . $hp['tahun'];
            $isRapel = (floatval($hp['take_home_pay'] ?? 0) == 0);
            
            $notifications[] = [
                'id' => 'payroll_hold_' . $hp['id'],
                'type' => 'error',
                'title' => $isRapel ? 'Gaji Dirapel (Hold)' : 'Gaji Ditunda (Hold)',
                'message' => $isRapel 
                    ? "Gaji karyawan <strong>" . esc($hp['employee_name']) . "</strong> (Klien: " . esc($hp['client_name']) . ") pada periode " . esc($periodName) . " ditunda karena karyawan baru bergabung setelah tanggal cut-off (gaji akan dirapel bulan depan)."
                    : "Gaji karyawan <strong>" . esc($hp['employee_name']) . "</strong> (Klien: " . esc($hp['client_name']) . ") pada periode " . esc($periodName) . " ditunda karena terdapat ketidakhadiran (absen) di hari kerja setelah cut-off (gaji akan dirapel digabungkan dengan bulan depan).",
                'link' => 'process',
                'client_id' => intval($hp['client_id']),
                'client_name' => $hp['client_name'],
                'client_sektor' => $hp['client_sektor']
            ];
        }

        // 3. Check if there are any payrolls on hold (Legacy / Regular)
        $holdPayrollsLegacy = $this->db->table('payrolls')
                                       ->select('payrolls.*, employees.nama as employee_name, clients.nama as client_name, clients.sektor as client_sektor, clients.id as client_id')
                                       ->join('employees', 'employees.id = payrolls.employee_id')
                                       ->join('clients', 'clients.id = employees.client_id')
                                       ->where('payrolls.status_pembayaran', 'Hold')
                                       ->get()
                                       ->getResultArray();
                                       
        foreach ($holdPayrollsLegacy as $hp) {
            $monthsWord = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $pName = ($monthsWord[intval($hp['bulan'])] ?? '') . ' ' . $hp['tahun'];
            $isRapel = (floatval($hp['take_home_pay'] ?? 0) == 0);
            
            $notifications[] = [
                'id' => 'payroll_hold_legacy_' . $hp['id'],
                'type' => 'error',
                'title' => $isRapel ? 'Gaji Dirapel (Hold)' : 'Gaji Ditunda (Hold)',
                'message' => $isRapel
                    ? "Gaji karyawan <strong>" . esc($hp['employee_name']) . "</strong> (Klien: " . esc($hp['client_name']) . ") pada periode " . esc($pName) . " ditunda karena karyawan baru bergabung setelah tanggal cut-off (gaji akan dirapel bulan depan)."
                    : "Gaji karyawan <strong>" . esc($hp['employee_name']) . "</strong> (Klien: " . esc($hp['client_name']) . ") pada periode " . esc($pName) . " ditunda karena terdapat ketidakhadiran (absen) di hari kerja setelah cut-off (gaji akan dirapel digabungkan dengan bulan depan).",
                'link' => 'process',
                'client_id' => intval($hp['client_id']),
                'client_name' => $hp['client_name'],
                'client_sektor' => $hp['client_sektor']
            ];
        }

        // Filter out dismissed notifications
        $dismissed = $this->db->table('dismissed_notifications')->get()->getResultArray();
        $dismissedIds = array_column($dismissed, 'notification_id');
        if (!empty($dismissedIds)) {
            $notifications = array_values(array_filter($notifications, function($n) use ($dismissedIds) {
                return !in_array($n['id'], $dismissedIds);
            }));
        }

        return $this->respond([
            'status' => 200,
            'data' => $notifications,
            'count' => count($notifications)
        ]);
    }

    public function dismissNotification()
    {
        $json = $this->request->getJSON();
        $notificationId = $json->notification_id ?? '';

        if (empty($notificationId)) {
            return $this->fail('Notification ID is required');
        }

        $exists = $this->db->table('dismissed_notifications')->where('notification_id', $notificationId)->countAllResults();
        if ($exists === 0) {
            $this->db->table('dismissed_notifications')->insert([
                'notification_id' => $notificationId,
                'dismissed_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Notification dismissed successfully'
        ]);
    }

    // --- AUTH ---
    public function login()
    {
        $json = $this->request->getJSON();
        $usernameOrEmail = trim($json->username ?? '');
        $password = $json->password ?? '';

        if (empty($usernameOrEmail) || empty($password)) {
            return $this->fail('Username/Email dan password wajib diisi');
        }

        // Cek apakah input adalah email dengan memindai karakter '@'
        $isEmail = strpos($usernameOrEmail, '@') !== false;

        $query = $this->db->table('users');
        if ($isEmail) {
            // Validasi email
            if (!filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
                return $this->fail('Format email tidak valid');
            }
            $query->where('email', $usernameOrEmail);
        } else {
            $query->where('username', $usernameOrEmail);
        }

        $user = $query->get()->getRow();

        if ($user && $password === $user->password) { // Catatan: Sebaiknya gunakan password_verify
            // Cek apakah user aktif
            if (isset($user->is_active) && !$user->is_active) {
                return $this->failUnauthorized('Akun Anda telah dinonaktifkan. Hubungi administrator.');
            }

            // Cek apakah role masih pending
            if (($user->role ?? '') === 'pending') {
                return $this->failUnauthorized('Akun Anda belum disetujui atau belum diberi role oleh Administrator.');
            }

            $this->logActivity("User login berhasil", $user->username);
            return $this->respond([
                'message' => 'Login berhasil',
                'user' => [
                    'id'        => $user->id,
                    'username'  => $user->username,
                    'role'      => $user->role ?? 'admin',
                    'full_name' => $user->full_name ?? $user->username
                ]
            ]);
        }

        return $this->failUnauthorized('Username/Email atau password salah');
    }

    public function getMinimumWages()
    {
        $tipe = $this->request->getGet('tipe') ?: 'UMP';
        $tahun = $this->request->getGet('tahun');
        
        $query = $this->db->table('minimum_wages');
        if ($tipe !== 'all') {
            $query->where('tipe', $tipe);
        }
        if ($tahun) {
            $query->where('tahun', $tahun);
        }
        $data = $query->get()->getResult();
        return $this->respond($data);
    }

    public function saveMinimumWages()
    {
        $data = $this->request->getJSON(true);
        
        $items = isset($data['items']) ? $data['items'] : [$data];
        if (empty($items)) {
            return $this->respondCreated(['message' => 'Tidak ada data untuk disimpan']);
        }
        
        // Extract the tipe and tahun from the first item to define our upload scope
        $firstItem = $items[0];
        $tipe = $firstItem['tipe'] ?? 'UMP';
        $tahun = $firstItem['tahun'] ?? 2026;
        
        // Strictly delete ONLY the existing records of this specific tipe and tahun!
        // This guarantees that UMK is NEVER touched when uploading UMP, and vice versa!
        $this->db->table('minimum_wages')
            ->where('tipe', $tipe)
            ->where('tahun', $tahun)
            ->delete();
            
        // Insert the uploaded list for this specific type and year
        foreach ($items as $item) {
            // Force strict consistency
            $item['tipe'] = $tipe;
            $item['tahun'] = $tahun;
            $this->db->table('minimum_wages')->insert($item);
        }
        
        return $this->respondCreated(['message' => 'Data gaji minimum berhasil disimpan']);
    }

    // --- CLIENTS ---
    public function getClients()
    {
        $clients = $this->db->table('clients')->get()->getResult();
        foreach ($clients as &$client) {
            if (isset($client->npwp)) {
                $client->npwp = (string)$client->npwp;
            }
        }
        return $this->respond($clients);
    }

    public function createClient()
    {
        $data = $this->request->getJSON(true);
        if (isset($data['npwp'])) {
            $data['npwp'] = (string)$data['npwp'];
        }
        $this->db->table('clients')->insert($data);
        $this->logActivity("Membuat klien baru: " . ($data['nama'] ?? ''));
        return $this->respondCreated(['message' => 'Klien berhasil ditambahkan']);
    }

    public function createClientBulk()
    {
        $json = $this->request->getJSON(true);
        if (empty($json) || !is_array($json)) {
            return $this->fail('Data tidak valid atau kosong.');
        }

        // Start transaction
        $this->db->transStart();

        $insertedCount = 0;
        $errors = [];

        foreach ($json as $index => $row) {
            $rowNum = $index + 2; // Assuming row 1 is header

            $nama = trim($row['nama'] ?? '');
            $sektor = trim($row['sektor'] ?? '');
            $email = trim($row['email'] ?? '');
            $telepon = trim($row['telepon'] ?? '');
            $tgl_gabung = trim($row['tgl_gabung'] ?? '');
            $alamat = trim($row['alamat'] ?? '');
            $nib = trim($row['nib'] ?? '');
            $npwp = trim($row['npwp'] ?? '');

            // Basic validation
            if (empty($nama)) {
                $errors[] = "Baris {$rowNum}: Nama klien wajib diisi.";
                continue;
            }
            if (empty($sektor)) {
                $errors[] = "Baris {$rowNum}: Sektor klien wajib diisi.";
                continue;
            }
            if (empty($email)) {
                $errors[] = "Baris {$rowNum}: Email klien wajib diisi.";
                continue;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Baris {$rowNum}: Format email '{$email}' tidak valid.";
                continue;
            }
            if (empty($tgl_gabung)) {
                $errors[] = "Baris {$rowNum}: Tanggal bergabung wajib diisi.";
                continue;
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_gabung)) {
                $errors[] = "Baris {$rowNum}: Format tanggal '{$tgl_gabung}' harus YYYY-MM-DD.";
                continue;
            }

            // Check if email already exists in DB
            $existing = $this->db->table('clients')->where('email', $email)->get()->getRow();
            if ($existing) {
                $errors[] = "Baris {$rowNum}: Email '{$email}' sudah terdaftar.";
                continue;
            }

            // Generate No Klien (Format: CLI-001)
            $count = $this->db->table('clients')->countAllResults();
            $no_klien = 'CLI-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

            $data = [
                'no_klien' => $no_klien,
                'nama' => $nama,
                'sektor' => $sektor,
                'email' => $email,
                'telepon' => $telepon,
                'tgl_gabung' => $tgl_gabung,
                'alamat' => $alamat,
                'nib' => $nib,
                'npwp' => $npwp,
                'status' => 'Aktif'
            ];

            $this->db->table('clients')->insert($data);
            $insertedCount++;
        }

        if (!empty($errors)) {
            $this->db->transRollback();
            return $this->respond([
                'status' => 'error',
                'errors' => $errors
            ], 400);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->fail('Gagal melakukan penyimpanan massal data klien.');
        }

        $this->logActivity("Meng-upload massal {$insertedCount} klien baru dari Excel.");
        return $this->respondCreated([
            'status' => 'success',
            'message' => "Berhasil menambahkan {$insertedCount} klien baru."
        ]);
    }

    public function updateClient($id)
    {
        $data = $this->request->getJSON(true);
        if (isset($data['npwp'])) {
            $data['npwp'] = (string)$data['npwp'];
        }
        $this->db->table('clients')->where('id', $id)->update($data);
        $this->logActivity("Mengupdate klien ID: " . $id . " (" . ($data['nama'] ?? '') . ")");
        return $this->respond(['message' => 'Klien berhasil diupdate']);
    }

    public function deleteClient($id)
    {
        $client = $this->db->table('clients')->where('id', $id)->get()->getRow();
        $clientName = $client ? $client->nama : 'Unknown';
        $this->db->table('clients')->where('id', $id)->delete();
        $this->logActivity("Menghapus klien ID: " . $id . " (" . $clientName . ")");
        return $this->respondDeleted(['message' => 'Klien berhasil dihapus']);
    }

    // --- ORGANISASI ---
    public function getOrg()
    {
        $clientId = $this->request->getGet('client_id');
        $query = $this->db->table('divisions');
        if ($clientId) $query->where('client_id', $clientId);
        
        $divisions = $query->get()->getResultArray();

        foreach ($divisions as &$div) {
            $div['departments'] = $this->db->table('departments')
                                           ->where('division_id', $div['id'])
                                           ->get()
                                           ->getResultArray();
            
            foreach ($div['departments'] as &$dept) {
                $dept['positions'] = $this->db->table('positions')
                                              ->where('department_id', $dept['id'])
                                              ->get()
                                              ->getResultArray();
            }
        }

        return $this->respond($divisions);
    }

    public function createDivision()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('divisions')->insert($data);
        return $this->respondCreated(['message' => 'Divisi berhasil ditambahkan']);
    }

    public function createDepartment()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('departments')->insert($data);
        return $this->respondCreated(['message' => 'Department berhasil ditambahkan']);
    }

    public function createPosition()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('positions')->insert($data);
        return $this->respondCreated(['message' => 'Posisi berhasil ditambahkan']);
    }

    public function updateOrg($type, $id)
    {
        $data = $this->request->getJSON(true);
        $table = '';
        if ($type === 'divisi') $table = 'divisions';
        elseif ($type === 'department') $table = 'departments';
        elseif ($type === 'posisi') $table = 'positions';

        if ($table) {
            $this->db->table($table)->where('id', $id)->update($data);
            return $this->respond(['message' => 'Data berhasil diupdate']);
        }
        return $this->fail('Tipe organisasi tidak valid');
    }

    public function deleteOrg($type, $id)
    {
        $table = '';
        if ($type === 'divisi') $table = 'divisions';
        elseif ($type === 'department') $table = 'departments';
        elseif ($type === 'posisi') $table = 'positions';

        if ($table) {
            $this->db->table($table)->where('id', $id)->delete();
            return $this->respondDeleted(['message' => 'Data berhasil dihapus']);
        }
        return $this->fail('Tipe organisasi tidak valid');
    }

    // --- PAYROLL ---
    public function getPayrollSchemes()
    {
        $schemes = $this->db->table('payroll_schemes')->get()->getResultArray();
        foreach ($schemes as &$scheme) {
            $scheme['components'] = $this->db->table('payroll_components')
                                             ->where('scheme_id', $scheme['id'])
                                             ->get()
                                             ->getResultArray();
        }
        return $this->respond($schemes);
    }

    public function createPayrollScheme()
    {
        $requestData = $this->request->getJSON(true);
        
        $schemeData = [
            'nama' => $requestData['nama'] ?? '',
            'deskripsi' => $requestData['deskripsi'] ?? '',
            'tipe' => $requestData['tipe'] ?? 'bulanan',
            'compensation_scheme_id' => !empty($requestData['compensation_scheme_id']) ? intval($requestData['compensation_scheme_id']) : null,
            'prorate' => isset($requestData['prorate']) ? intval($requestData['prorate']) : 0,
            'absen_tidak_potong' => isset($requestData['absen_tidak_potong']) ? intval($requestData['absen_tidak_potong']) : 0,
            'nominal_potongan' => isset($requestData['nominal_potongan']) ? floatval($requestData['nominal_potongan']) : 0,
            'grace_period_late' => isset($requestData['grace_period_late']) ? intval($requestData['grace_period_late']) : 0,
            'grace_period_early' => isset($requestData['grace_period_early']) ? intval($requestData['grace_period_early']) : 0,
            'min_overtime' => isset($requestData['min_overtime']) ? intval($requestData['min_overtime']) : 30,
            'max_early_arrival_minutes' => isset($requestData['max_early_arrival_minutes']) ? intval($requestData['max_early_arrival_minutes']) : 180,
            'denda_terlambat_per_jam' => isset($requestData['denda_terlambat_per_jam']) ? floatval($requestData['denda_terlambat_per_jam']) : 0,
            'denda_alfa_per_hari' => isset($requestData['denda_alfa_per_hari']) ? floatval($requestData['denda_alfa_per_hari']) : 0,
            'early_leave_threshold' => isset($requestData['early_leave_threshold']) ? intval($requestData['early_leave_threshold']) : 0,
            'overtime_type' => $requestData['overtime_type'] ?? 'standard',
            'lumpsum_subtype' => $requestData['lumpsum_subtype'] ?? null,
            'lumpsum_nominal' => isset($requestData['lumpsum_nominal']) ? floatval($requestData['lumpsum_nominal']) : 0,
        ];
        
        $this->db->table('payroll_schemes')->insert($schemeData);
        $schemeId = $this->db->insertID();
        
        $componentData = [
            'scheme_id' => $schemeId,
            'nama' => 'Gaji Pokok',
            'tipe' => 'pendapatan',
            'nilai' => $requestData['nilai'] ?? 0,
            'is_persentase' => isset($requestData['is_persentase']) ? intval($requestData['is_persentase']) : 0,
            'jenis_komponen' => 'basic_salary',
            'sumber_nilai' => $requestData['sumber_nilai'] ?? 'nominal',
            'periode' => $requestData['periode'] ?? 'bulan',
            'sifat_kompensasi' => 'tetap',
            'is_bpjs' => 1,
            'is_pph21' => 1
        ];
        
        $this->db->table('payroll_components')->insert($componentData);

        // Save selected compensation components
        if (!empty($requestData['components'])) {
            foreach ($requestData['components'] as $comp) {
                $compData = [
                    'scheme_id' => $schemeId,
                    'nama' => $comp['nama'] ?? '',
                    'tipe' => $comp['tipe'] ?? 'pendapatan',
                    'nilai' => isset($comp['nilai']) ? floatval($comp['nilai']) : 0,
                    'is_persentase' => isset($comp['is_persentase']) ? intval($comp['is_persentase']) : 0,
                    'jenis_komponen' => 'kompensasi',
                    'sumber_nilai' => $comp['sumber_nilai'] ?? 'nominal',
                    'periode' => $comp['periode'] ?? 'bulan',
                    'sifat_kompensasi' => $comp['sifat_kompensasi'] ?? 'tetap',
                    'is_bpjs' => isset($comp['is_bpjs']) ? intval($comp['is_bpjs']) : 0,
                    'is_pph21' => isset($comp['is_pph21']) ? intval($comp['is_pph21']) : 1
                ];
                $this->db->table('payroll_components')->insert($compData);
            }
        }
        
        $this->logActivity("Membuat skema payroll baru: " . ($schemeData['nama'] ?? ''));
        return $this->respondCreated(['message' => 'Skema berhasil ditambahkan']);
    }

    public function updatePayrollScheme($id)
    {
        $requestData = $this->request->getJSON(true);
        
        $schemeData = [
            'nama' => $requestData['nama'] ?? '',
            'deskripsi' => $requestData['deskripsi'] ?? '',
            'tipe' => $requestData['tipe'] ?? 'bulanan',
            'compensation_scheme_id' => !empty($requestData['compensation_scheme_id']) ? intval($requestData['compensation_scheme_id']) : null,
            'prorate' => isset($requestData['prorate']) ? intval($requestData['prorate']) : 0,
            'absen_tidak_potong' => isset($requestData['absen_tidak_potong']) ? intval($requestData['absen_tidak_potong']) : 0,
            'nominal_potongan' => isset($requestData['nominal_potongan']) ? floatval($requestData['nominal_potongan']) : 0,
            'grace_period_late' => isset($requestData['grace_period_late']) ? intval($requestData['grace_period_late']) : 0,
            'grace_period_early' => isset($requestData['grace_period_early']) ? intval($requestData['grace_period_early']) : 0,
            'min_overtime' => isset($requestData['min_overtime']) ? intval($requestData['min_overtime']) : 30,
            'max_early_arrival_minutes' => isset($requestData['max_early_arrival_minutes']) ? intval($requestData['max_early_arrival_minutes']) : 180,
            'denda_terlambat_per_jam' => isset($requestData['denda_terlambat_per_jam']) ? floatval($requestData['denda_terlambat_per_jam']) : 0,
            'denda_alfa_per_hari' => isset($requestData['denda_alfa_per_hari']) ? floatval($requestData['denda_alfa_per_hari']) : 0,
            'early_leave_threshold' => isset($requestData['early_leave_threshold']) ? intval($requestData['early_leave_threshold']) : 0,
            'overtime_type' => $requestData['overtime_type'] ?? 'standard',
            'lumpsum_subtype' => $requestData['lumpsum_subtype'] ?? null,
            'lumpsum_nominal' => isset($requestData['lumpsum_nominal']) ? floatval($requestData['lumpsum_nominal']) : 0,
        ];
        
        $this->db->table('payroll_schemes')->where('id', $id)->update($schemeData);
        
        $componentData = [
            'nama' => 'Gaji Pokok',
            'tipe' => 'pendapatan',
            'nilai' => $requestData['nilai'] ?? 0,
            'is_persentase' => isset($requestData['is_persentase']) ? intval($requestData['is_persentase']) : 0,
            'jenis_komponen' => 'basic_salary',
            'sumber_nilai' => $requestData['sumber_nilai'] ?? 'nominal',
            'periode' => $requestData['periode'] ?? 'bulan',
            'sifat_kompensasi' => 'tetap',
            'is_bpjs' => 1,
            'is_pph21' => 1
        ];
        
        $existing = $this->db->table('payroll_components')->where('scheme_id', $id)->where('jenis_komponen', 'basic_salary')->get()->getRow();
        if ($existing) {
            $this->db->table('payroll_components')->where('id', $existing->id)->update($componentData);
        } else {
            $componentData['scheme_id'] = $id;
            $this->db->table('payroll_components')->insert($componentData);
        }

        // Delete existing non-basic components
        $this->db->table('payroll_components')
                 ->where('scheme_id', $id)
                 ->where('jenis_komponen !=', 'basic_salary')
                 ->delete();

        // Save selected compensation components
        if (!empty($requestData['components'])) {
            foreach ($requestData['components'] as $comp) {
                $compData = [
                    'scheme_id' => $id,
                    'nama' => $comp['nama'] ?? '',
                    'tipe' => $comp['tipe'] ?? 'pendapatan',
                    'nilai' => isset($comp['nilai']) ? floatval($comp['nilai']) : 0,
                    'is_persentase' => isset($comp['is_persentase']) ? intval($comp['is_persentase']) : 0,
                    'jenis_komponen' => 'kompensasi',
                    'sumber_nilai' => $comp['sumber_nilai'] ?? 'nominal',
                    'periode' => $comp['periode'] ?? 'bulan',
                    'sifat_kompensasi' => $comp['sifat_kompensasi'] ?? 'tetap',
                    'is_bpjs' => isset($comp['is_bpjs']) ? intval($comp['is_bpjs']) : 0,
                    'is_pph21' => isset($comp['is_pph21']) ? intval($comp['is_pph21']) : 1
                ];
                $this->db->table('payroll_components')->insert($compData);
            }
        }
        
        $this->logActivity("Mengupdate skema payroll ID: " . $id . " (" . ($schemeData['nama'] ?? '') . ")");
        return $this->respond(['message' => 'Skema berhasil diupdate']);
    }

    public function deletePayrollScheme($id)
    {
        $scheme = $this->db->table('payroll_schemes')->where('id', $id)->get()->getRow();
        $schemeName = $scheme ? $scheme->nama : 'Unknown';
        $this->db->table('payroll_components')->where('scheme_id', $id)->delete();
        $this->db->table('payroll_schemes')->where('id', $id)->delete();
        $this->logActivity("Menghapus skema payroll ID: " . $id . " (" . $schemeName . ")");
        return $this->respondDeleted(['message' => 'Skema berhasil dihapus']);
    }

    public function createPayrollComponent()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('payroll_components')->insert($data);
        return $this->respondCreated(['message' => 'Komponen berhasil ditambahkan']);
    }

    public function updatePayrollComponent($id)
    {
        $data = $this->request->getJSON(true);
        $this->db->table('payroll_components')->where('id', $id)->update($data);
        return $this->respond(['message' => 'Komponen berhasil diupdate']);
    }

    public function deletePayrollComponent($id)
    {
        $this->db->table('payroll_components')->where('id', $id)->delete();
        return $this->respondDeleted(['message' => 'Komponen berhasil dihapus']);
    }

    // --- TAX SCHEMES ---
    public function getTaxSchemes()
    {
        $schemes = $this->db->table('tax_schemes')->get()->getResult();
        return $this->respond($schemes);
    }

    public function createTaxScheme()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('tax_schemes')->insert($data);
        $id = $this->db->insertID();
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Skema pajak berhasil ditambahkan',
            'id' => $id
        ]);
    }

    public function updateTaxScheme($id)
    {
        $data = $this->request->getJSON(true);
        $this->db->table('tax_schemes')->where('id', $id)->update($data);
        return $this->respond(['message' => 'Skema pajak berhasil diupdate']);
    }

    public function deleteTaxScheme($id)
    {
        $this->db->table('tax_schemes')->where('id', $id)->delete();
        return $this->respondDeleted(['message' => 'Skema pajak berhasil dihapus']);
    }

    // --- COMPENSATION SCHEMES ---
    public function getCompensationSchemes()
    {
        $schemes = $this->db->table('compensation_schemes')->get()->getResultArray();
        foreach ($schemes as &$scheme) {
            $scheme['components'] = $this->db->table('compensation_components')
                                             ->where('scheme_id', $scheme['id'])
                                             ->get()
                                             ->getResultArray();
        }
        return $this->respond($schemes);
    }

    public function createCompensationScheme()
    {
        $requestData = $this->request->getJSON(true);
        
        $schemeData = [
            'nama' => $requestData['nama'] ?? '',
            'deskripsi' => $requestData['deskripsi'] ?? ''
        ];
        
        $this->db->table('compensation_schemes')->insert($schemeData);
        $schemeId = $this->db->insertID();
        $namaKompensasi = $requestData['nama'] ?? '';
        $sifatKompensasi = ($requestData['sifat_kompensasi'] ?? '') === 'tidak_tetap' ? 'tidak_tetap' : 'tetap';
        
        $componentData = [
            'scheme_id' => $schemeId,
            'nama' => $namaKompensasi,
            'tipe' => $requestData['tipe'] ?? 'pendapatan',
            'nilai' => $requestData['nilai'] ?? 0,
            'is_persentase' => isset($requestData['is_persentase']) ? intval($requestData['is_persentase']) : 0,
            'jenis_komponen' => 'kompensasi',
            'sumber_nilai' => $requestData['sumber_nilai'] ?? 'nominal',
            'periode' => $requestData['periode'] ?? 'bulan',
            'sifat_kompensasi' => $sifatKompensasi
        ];
        
        $this->db->table('compensation_components')->insert($componentData);
        
        $this->logActivity("Membuat skema kompensasi baru: " . ($schemeData['nama'] ?? ''));
        return $this->respondCreated(['message' => 'Skema kompensasi berhasil ditambahkan']);
    }

    public function updateCompensationScheme($id)
    {
        $requestData = $this->request->getJSON(true);
        
        $schemeData = [
            'nama' => $requestData['nama'] ?? '',
            'deskripsi' => $requestData['deskripsi'] ?? ''
        ];
        
        $this->db->table('compensation_schemes')->where('id', $id)->update($schemeData);
        
        $namaKompensasi = $requestData['nama'] ?? '';
        $sifatKompensasi = ($requestData['sifat_kompensasi'] ?? '') === 'tidak_tetap' ? 'tidak_tetap' : 'tetap';

        $componentData = [
            'nama' => $namaKompensasi,
            'tipe' => $requestData['tipe'] ?? 'pendapatan',
            'nilai' => $requestData['nilai'] ?? 0,
            'is_persentase' => isset($requestData['is_persentase']) ? intval($requestData['is_persentase']) : 0,
            'jenis_komponen' => 'kompensasi',
            'sumber_nilai' => $requestData['sumber_nilai'] ?? 'nominal',
            'periode' => $requestData['periode'] ?? 'bulan',
            'sifat_kompensasi' => $sifatKompensasi
        ];
        
        $existing = $this->db->table('compensation_components')->where('scheme_id', $id)->get()->getRow();
        if ($existing) {
            $this->db->table('compensation_components')->where('id', $existing->id)->update($componentData);
        } else {
            $componentData['scheme_id'] = $id;
            $this->db->table('compensation_components')->insert($componentData);
        }
        
        $this->logActivity("Mengupdate skema kompensasi ID: " . $id . " (" . ($schemeData['nama'] ?? '') . ")");
        return $this->respond(['message' => 'Skema kompensasi berhasil diupdate']);
    }

    public function deleteCompensationScheme($id)
    {
        $scheme = $this->db->table('compensation_schemes')->where('id', $id)->get()->getRow();
        $schemeName = $scheme ? $scheme->nama : 'Unknown';
        $this->db->table('compensation_schemes')->where('id', $id)->delete();
        $this->db->table('compensation_components')->where('scheme_id', $id)->delete(); // Cascade
        $this->logActivity("Menghapus skema kompensasi ID: " . $id . " (" . $schemeName . ")");
        return $this->respondDeleted(['message' => 'Skema kompensasi berhasil dihapus']);
    }

    // --- SCHEDULE TEMPLATES ---
    public function getScheduleTemplates()
    {
        $tahun = $this->request->getGet('tahun');
        $builder = $this->db->table('payroll_schedules');
        if ($tahun) {
            $builder->where('tahun', intval($tahun));
        }
        $schedules = $builder->get()->getResultArray();
        return $this->respond($schedules);
    }

    public function createScheduleTemplate()
    {
        $data = $this->request->getJSON(true);
        $insertData = [
            'nama' => $data['nama'] ?? '',
            'pay_date' => isset($data['pay_date']) ? intval($data['pay_date']) : 25,
            'cutoff_start' => isset($data['cutoff_start']) ? intval($data['cutoff_start']) : 21,
            'cutoff_end' => isset($data['cutoff_end']) ? intval($data['cutoff_end']) : 20,
            'deskripsi' => $data['deskripsi'] ?? null,
            'tahun' => isset($data['tahun']) ? intval($data['tahun']) : intval(date('Y'))
        ];

        $this->db->table('payroll_schedules')->insert($insertData);
        $this->logActivity("Membuat master schedule baru: " . ($insertData['nama'] ?? ''));
        return $this->respondCreated(['message' => 'Schedule template berhasil ditambahkan']);
    }

    public function updateScheduleTemplate($id)
    {
        $data = $this->request->getJSON(true);
        $updateData = [
            'nama' => $data['nama'] ?? '',
            'pay_date' => isset($data['pay_date']) ? intval($data['pay_date']) : 25,
            'cutoff_start' => isset($data['cutoff_start']) ? intval($data['cutoff_start']) : 21,
            'cutoff_end' => isset($data['cutoff_end']) ? intval($data['cutoff_end']) : 20,
            'deskripsi' => $data['deskripsi'] ?? null,
            'tahun' => isset($data['tahun']) ? intval($data['tahun']) : intval(date('Y'))
        ];

        $this->db->table('payroll_schedules')->where('id', $id)->update($updateData);
        $this->logActivity("Mengupdate master schedule ID: " . $id . " (" . ($updateData['nama'] ?? '') . ")");
        return $this->respond(['message' => 'Schedule template berhasil diupdate']);
    }

    public function deleteScheduleTemplate($id)
    {
        $schedule = $this->db->table('payroll_schedules')->where('id', $id)->get()->getRow();
        $name = $schedule ? $schedule->nama : 'Unknown';
        $this->db->table('payroll_schedules')->where('id', $id)->delete();
        $this->logActivity("Menghapus master schedule ID: " . $id . " (" . $name . ")");
        return $this->respondDeleted(['message' => 'Schedule template berhasil dihapus']);
    }

    private function resolveClientConfigForEmployee($employeeId)
    {
        $db = \Config\Database::connect();
        $emp = $db->table('employees')->where('id', intval($employeeId))->get()->getRow();
        if (!$emp) {
            return null;
        }
        $clientId = $emp->client_id;
        $divId = $emp->division_id ?? null;
        $deptId = $emp->department_id ?? null;
        $posId = $emp->position_id ?? null;

        $config = null;
        if ($posId) {
            $config = $db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('position_id', $posId)
                ->get()->getRow();
        }
        if (!$config && $deptId) {
            $config = $db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('department_id', $deptId)
                ->where('position_id IS NULL')
                ->get()->getRow();
        }
        if (!$config && $divId) {
            $config = $db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('division_id', $divId)
                ->where('department_id IS NULL')
                ->where('position_id IS NULL')
                ->get()->getRow();
        }
        if (!$config) {
            $config = $db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('division_id IS NULL')
                ->where('department_id IS NULL')
                ->where('position_id IS NULL')
                ->get()->getRow();
        }
        return $config;
    }

    // --- ATTENDANCE LOGS ---
    private function calculateShiftAttendance($employeeId, $tanggal, $jamMasuk, $jamKeluar, $status, $providedShiftSchemeId = null, $payoutPeriod = null)
    {
        $db = \Config\Database::connect();
        
        // 1. Determine Shift Scheme
        $shiftSchemeId = null;
        if (!empty($providedShiftSchemeId)) {
            $shiftSchemeId = intval($providedShiftSchemeId);
        } else {
            // Find active shift assignment for this employee
            $empShift = $db->table('employee_shifts')
                ->where('employee_id', intval($employeeId))
                ->where('start_date <=', $tanggal)
                ->groupStart()
                    ->where('end_date IS NULL')
                    ->orWhere('end_date >=', $tanggal)
                ->groupEnd()
                ->orderBy('start_date', 'DESC')
                ->get()->getRow();
            if ($empShift) {
                $shiftSchemeId = intval($empShift->shift_scheme_id);
            }
        }

        $result = [
            'shift_scheme_id'            => $shiftSchemeId,
            'calculated_work_hours'      => 0.0,
            'calculated_overtime_hours'  => 0.0,
            'late_hours'                 => 0.0,
            'late_minutes'               => 0,
            'late_penalty_hours'         => 0,
            'denda_terlambat'            => 0.0,
            'early_leave_hours'          => 0.0,
            'is_early_leave_alfa'        => 0,
            'denda_alfa'                 => 0.0,
            'absent_penalty'             => 0.0,
            'is_incomplete'              => 0,
            'is_rapel'                   => 0,
            'payout_period'              => null,
        ];

        // 2. Determine rapel status based on client's cut-off
        $emp = $db->table('employees')->where('id', intval($employeeId))->get()->getRow();
        $clientId = $emp ? $emp->client_id : null;

        $cutoffGajiPokokStart = 21;
        $cutoffGajiPokokEnd = 20;
        $cutoffLemburStart = 21;
        $cutoffLemburEnd = 20;

        $clientConfig = null;
        if ($clientId && $emp) {
            $clientConfig = $this->resolveClientConfigForEmployee($employeeId);
        }

        // Helper function to resolve dynamic cutoff dates from schedule or direct configs
        $resolveCutoffDates = function($config, $component) use ($db) {
            $startField = "cutoff_{$component}_start";
            $endField = "cutoff_{$component}_end";
            $refField = "cutoff_{$component}_schedule_ref";
            
            $start = ($config && isset($config->$startField)) ? intval($config->$startField) : null;
            $end = ($config && isset($config->$endField)) ? intval($config->$endField) : null;
            $refId = ($config && isset($config->$refField)) ? intval($config->$refField) : null;
            
            if ($refId) {
                $sched = $db->table('payroll_schedules')->where('id', $refId)->get()->getRow();
                if ($sched) {
                    $start = intval($sched->cutoff_start);
                    $end = intval($sched->cutoff_end);
                }
            }
            
            if ($start === null) {
                if ($component === 'gaji_pokok' && $config && isset($config->cutoff_start)) {
                    $start = intval($config->cutoff_start);
                } else {
                    $start = 21;
                }
            }
            if ($end === null) {
                if ($component === 'gaji_pokok' && $config && isset($config->cutoff_end)) {
                    $end = intval($config->cutoff_end);
                } else {
                    $end = $start - 1;
                    if ($end < 1) $end = 31;
                }
            }
            return [$start, $end];
        };

        if ($clientConfig) {
            list($cutoffGajiPokokStart, $cutoffGajiPokokEnd) = $resolveCutoffDates($clientConfig, 'gaji_pokok');
            list($cutoffLemburStart, $cutoffLemburEnd) = $resolveCutoffDates($clientConfig, 'lembur');
        }

        $ts = strtotime($tanggal);
        $tYear = intval(date('Y', $ts));
        $tMonth = intval(date('n', $ts));
        $tDay = intval(date('j', $ts));

        $resolvePeriodAndRapel = function($startDay, $endDay) use ($tanggal, $tYear, $tMonth, $tDay, $payoutPeriod) {
            $actualEndDay = $endDay;
            if ($actualEndDay <= 0) {
                $actualEndDay = date('t', strtotime($tanggal));
            }
            if ($tDay > $actualEndDay) {
                $natMonth = $tMonth + 1;
                $natYear = $tYear;
                if ($natMonth > 12) {
                    $natMonth = 1;
                    $natYear++;
                }
            } else {
                $natMonth = $tMonth;
                $natYear = $tYear;
            }
            $naturalPeriod = $natMonth . '-' . $natYear;
            $isRapel = 0;
            $finalPayoutPeriod = $naturalPeriod;

            if (!empty($payoutPeriod)) {
                $naturalVal = $natYear * 12 + $natMonth;
                $parts = explode('-', $payoutPeriod);
                if (count($parts) === 2) {
                    $payoutMonth = intval($parts[0]);
                    $payoutYear = intval($parts[1]);
                    $payoutVal = $payoutYear * 12 + $payoutMonth;
                    if ($naturalVal > $payoutVal) {
                        $isRapel = 0;
                        $finalPayoutPeriod = $naturalPeriod;
                    } elseif ($naturalVal < $payoutVal) {
                        $isRapel = 1;
                        $finalPayoutPeriod = $payoutPeriod;
                    } else {
                        $isRapel = 0;
                        $finalPayoutPeriod = $naturalPeriod;
                    }
                }
            }
            return [$isRapel, $finalPayoutPeriod];
        };

        list($gpIsRapel, $gpPayoutPeriod) = $resolvePeriodAndRapel($cutoffGajiPokokStart, $cutoffGajiPokokEnd);
        $result['is_rapel'] = $gpIsRapel;
        $result['payout_period'] = $gpPayoutPeriod;
        
        // Also pre-resolve Overtime period and rapel for later sync
        list($otIsRapel, $otPayoutPeriod) = $resolvePeriodAndRapel($cutoffLemburStart, $cutoffLemburEnd);

        // Auto-detect shift jika tidak ada assignment di employee_shifts
        if (!$shiftSchemeId && !empty($jamMasuk)) {
            $jamMasukInt = intval(explode(':', $jamMasuk)[0]);
            $namaShiftGuess = $jamMasukInt < 12 ? 'Pagi' : 'Siang';
            $matchedShift = $db->table('shift_schemes')
                ->where('name', $namaShiftGuess)
                ->get()->getRow();
            if ($matchedShift) {
                $shiftSchemeId = intval($matchedShift->id);
                $result['shift_scheme_id'] = $shiftSchemeId;
            }
        }

        // Fallback: ambil shift pertama yang tersedia
        if (!$shiftSchemeId) {
            $firstShift = $db->table('shift_schemes')->orderBy('id', 'ASC')->get()->getRow();
            if ($firstShift) {
                $shiftSchemeId = intval($firstShift->id);
                $result['shift_scheme_id'] = $shiftSchemeId;
            }
        }

        if (!$shiftSchemeId) {
            return $result;
        }

        $shift = $db->table('shift_schemes')->where('id', $shiftSchemeId)->get()->getRow();
        if (!$shift) {
            return $result;
        }

        if ($status !== 'Hadir') {
            return $result;
        }

        if (empty($jamMasuk)) {
            // Check if this is a rest day (weekend or public holiday) — if so, do NOT mark as incomplete
            $dayOfWeek = intval(date('w', strtotime($tanggal))); // 0=Sunday, 6=Saturday
            $workDaysConfig = 5; // default
            if ($emp) {
                $workDaysConfig = intval($emp->hari_kerja ?? 5);
                if ($workDaysConfig < 1) {
                    // Try position hari_kerja
                    $posId = $emp->position_id ?? null;
                    if ($posId) {
                        $pos = $db->table('positions')->where('id', $posId)->get()->getRow();
                        if ($pos && isset($pos->hari_kerja) && intval($pos->hari_kerja) > 0) {
                            $workDaysConfig = intval($pos->hari_kerja);
                        } else {
                            $workDaysConfig = 5;
                        }
                    } else {
                        $workDaysConfig = 5;
                    }
                }
            }
            $isRestDay = false;
            if ($workDaysConfig === 5) {
                $isRestDay = ($dayOfWeek === 0 || $dayOfWeek === 6);
            } elseif ($workDaysConfig === 6) {
                $isRestDay = ($dayOfWeek === 0);
            }
            // Also check if it's a public holiday from holiday_calendar
            if (!$isRestDay) {
                $publicHoliday = $db->table('holiday_calendar')->where('tanggal', $tanggal)->get()->getRow();
                if ($publicHoliday) {
                    $isRestDay = true;
                }
            }
            if (!$isRestDay) {
                $result['is_incomplete'] = 1;
            }
            return $result;
        }

        // 3. Perform calculations
        $inTime = strtotime($tanggal . ' ' . $jamMasuk);
        $shiftIn = strtotime($tanggal . ' ' . $shift->start_time);
        $shiftOut = strtotime($tanggal . ' ' . $shift->end_time);

        $graceLate  = 0;
        $graceEarly = 0;
        $minOvertime          = 30;
        $dendaTerlambatPerJam = 0.0;

        // Skema absensi (untuk early leave / alfa) — dari payroll_scheme
        $absenTidakPotong    = 0;    // 1 = tidak potong gaji
        $absenNominalPerHari = 0.0;  // nominal potongan per hari (Attendance Deducts Nominal)
        $absenProrate        = 1;    // default prorate (gaji/hari kerja)

        if ($clientId) {
            $schemeTemplateModel = new \App\Models\PayrollSchemeTemplateModel();
            $stoScheme = $schemeTemplateModel->getSchemeForEmployee(
                $clientId,
                $emp->division_id ?? null,
                $emp->department_id ?? null,
                $emp->position_id ?? null
            );

            if ($stoScheme) {
                $graceLate            = intval($stoScheme['grace_period_late'] ?? 0);
                $graceEarly           = intval($stoScheme['grace_period_early'] ?? 0);
                $minOvertime          = intval($stoScheme['min_overtime'] ?? 30);
                $dendaTerlambatPerJam = floatval($stoScheme['denda_terlambat_per_jam'] ?? 0);
            } else {
                $clientConfig = $db->table('client_payroll_configs')
                                   ->where('client_id', $clientId)
                                   ->get()->getRow();
                if ($clientConfig && !empty($clientConfig->payroll_scheme_id)) {
                    $payrollScheme = $db->table('payroll_schemes')
                                        ->where('id', $clientConfig->payroll_scheme_id)
                                        ->get()->getRow();
                    if ($payrollScheme) {
                        $graceLate            = intval($payrollScheme->grace_period_late ?? 0);
                        $graceEarly           = intval($payrollScheme->grace_period_early ?? 0);
                        $minOvertime          = intval($payrollScheme->min_overtime ?? 30);
                        $dendaTerlambatPerJam = floatval($payrollScheme->denda_terlambat_per_jam ?? 0);

                        // Baca skema absensi dari payroll_scheme
                        $absenTidakPotong    = intval($payrollScheme->absen_tidak_potong ?? 0);
                        $absenNominalPerHari = floatval($payrollScheme->nominal_potongan ?? 0);
                        // prorate = 1 jika bukan "tidak potong" dan bukan "nominal"
                        $absenProrate = ($absenTidakPotong == 0 && $absenNominalPerHari == 0) ? 1 : 0;
                    }
                }
            }
        }

        // ── ALFA / ABSEN ──────────────────────────────────────────────────────
        if ($status !== 'Hadir') {
            // Tidak hitung denda di sini — potongan alfa dihitung di Payroll.php
            // berdasarkan skema absensi (prorate / nominal / tidak potong)
            return $result;
        }

        // ── HADIR: hitung keterlambatan ───────────────────────────────────────
        if (empty($jamMasuk)) {
            // Reuse weekend/holiday rest-day check — do NOT mark as incomplete on rest days or public holidays
            $dayOfWeek2 = intval(date('w', strtotime($tanggal)));
            $wdc2 = 5;
            if ($emp) {
                $wdc2 = intval($emp->hari_kerja ?? 5);
                if ($wdc2 < 1) {
                    $posId2 = $emp->position_id ?? null;
                    if ($posId2) {
                        $pos2 = $db->table('positions')->where('id', $posId2)->get()->getRow();
                        $wdc2 = ($pos2 && isset($pos2->hari_kerja) && intval($pos2->hari_kerja) > 0) ? intval($pos2->hari_kerja) : 5;
                    } else {
                        $wdc2 = 5;
                    }
                }
            }
            $isRestDay2 = false;
            if ($wdc2 === 5) { $isRestDay2 = ($dayOfWeek2 === 0 || $dayOfWeek2 === 6); }
            elseif ($wdc2 === 6) { $isRestDay2 = ($dayOfWeek2 === 0); }
            // Also check holiday_calendar for public holidays
            if (!$isRestDay2) {
                $pubHol2 = $db->table('holiday_calendar')->where('tanggal', $tanggal)->get()->getRow();
                if ($pubHol2) { $isRestDay2 = true; }
            }
            if (!$isRestDay2) {
                $result['is_incomplete'] = 1;
            }
            return $result;
        }

        $inTime   = strtotime($tanggal . ' ' . $jamMasuk);
        $shiftIn  = strtotime($tanggal . ' ' . $shift->start_time);
        $shiftOut = strtotime($tanggal . ' ' . $shift->end_time);

        // Keterlambatan
        $lateMinutes      = 0;
        $latePenaltyHours = 0;
        $dendaTerlambat   = 0.0;

        if ($inTime > ($shiftIn + ($graceLate * 60))) {
            $lateMinutes      = intval(ceil(($inTime - $shiftIn) / 60));
            $latePenaltyHours = intval(ceil($lateMinutes / 60.0));
            $dendaTerlambat   = $latePenaltyHours * $dendaTerlambatPerJam;
        }

        $result['late_hours']         = round($lateMinutes / 60.0, 2);
        $result['late_minutes']       = $lateMinutes;
        $result['late_penalty_hours'] = $latePenaltyHours;
        $result['denda_terlambat']    = $dendaTerlambat;

        // ── Jam keluar ────────────────────────────────────────────────────────
        if (empty($jamKeluar)) {
            $result['is_incomplete'] = 1;
            return $result;
        }

        $outTime = strtotime($tanggal . ' ' . $jamKeluar);
        if ($outTime <= $inTime) {
            $result['is_incomplete'] = 1;
            return $result;
        }

        // ── Early Leave ───────────────────────────────────────────────────────
        $earlyLeaveMinutes = 0;
        $isEarlyLeaveAlfa  = 0;
        $earlyLeaveHours   = 0.0;

        if ($outTime < $shiftOut) {
            $earlyLeaveMinutes = intval(ceil(($shiftOut - $outTime) / 60));
            $earlyLeaveHours   = round($earlyLeaveMinutes / 60.0, 2);

            // Jika early leave melebihi toleransi grace → dihitung sebagai Alfa
            if ($earlyLeaveMinutes > $graceEarly) {
                $result['is_incomplete'] = 1;
                $isEarlyLeaveAlfa = 1;
                // Potongan untuk early leave dihitung di Payroll.php (ikut skema absensi)
            }
        }

        $result['early_leave_hours']   = $earlyLeaveHours;
        $result['is_early_leave_alfa'] = $isEarlyLeaveAlfa;
        $result['denda_alfa']          = 0.0;   // Tidak dipakai, dihitung di Payroll.php
        $result['absent_penalty']      = 0.0;   // Tidak dipakai, dihitung di Payroll.php

        // ── Jam Kerja Aktual ──────────────────────────────────────────────────
        $breakDuration       = isset($shift->break_duration) ? floatval($shift->break_duration) : 0.0;
        $actualDurationHours = max(0, (($outTime - $inTime) / 3600) - $breakDuration);
        $standardDuration    = floatval($shift->duration);

        $workHours = round(min($standardDuration, $actualDurationHours), 2);
        $result['calculated_work_hours'] = min(9999.99, max(0, $workHours));

        // ── Overtime ──────────────────────────────────────────────────────────
        $otHours   = 0.0;
        $otMinutes = 0;

        if ($outTime > $shiftOut) {
            $otMinutes = ($outTime - $shiftOut) / 60;
            $otHours   = $otMinutes / 60.0;
        }

        if ($otMinutes >= $minOvertime) {
            $result['calculated_overtime_hours'] = min(9999.99, round(max(0, $otHours), 2));
        } else {
            $result['calculated_overtime_hours'] = 0.0;
        }

        // ── Sync ke overtime_logs ─────────────────────────────────────────────
        if ($result['calculated_overtime_hours'] > 0) {
            $existingOt = $db->table('overtime_logs')
                ->where('employee_id', intval($employeeId))
                ->where('tanggal', $tanggal)
                ->get()->getRow();

            $isHoliday = 0;
            $dayOfWeek = date('w', strtotime($tanggal));
            if ($dayOfWeek == 0) {
                $isHoliday = 1;
            } else {
                $holiday = $db->table('holiday_calendar')->where('tanggal', $tanggal)->get()->getRow();
                if ($holiday) {
                    $isHoliday = 1;
                } elseif ($dayOfWeek == 6) {
                    // Check employee's working days config
                    $emp = $db->table('employees')
                        ->select('employees.hari_kerja, positions.hari_kerja as position_hari_kerja')
                        ->join('positions', 'positions.id = employees.position_id', 'left')
                        ->where('employees.id', intval($employeeId))
                        ->get()->getRow();
                    $workDaysPerWeek = 5;
                    if ($emp) {
                        $workDaysPerWeek = intval($emp->hari_kerja ?: ($emp->position_hari_kerja ?: 5));
                    }
                    $isHoliday = ($workDaysPerWeek < 6) ? 1 : 0;
                }
            }

            $jamLemburVal = floatval($result['calculated_overtime_hours']);
            if (!$isHoliday && $jamLemburVal > 3.0) {
                $jamLemburVal = 3.0;
            }

            $otData = [
                'jam_lembur'    => $jamLemburVal,
                'is_holiday'    => $isHoliday,
                'keterangan'    => 'Auto: shift ' . $shift->name,
                'status'        => 'Pending',
                'approved_by'   => null,
                'approved_at'   => null,
                'is_rapel'      => $otIsRapel,
                'payout_period' => $otPayoutPeriod
            ];

            if ($existingOt) {
                $db->table('overtime_logs')->where('id', $existingOt->id)->update($otData);
            } else {
                $otData['employee_id'] = intval($employeeId);
                $otData['tanggal']     = $tanggal;
                $db->table('overtime_logs')->insert($otData);
            }
        } else {
            $db->table('overtime_logs')
                ->where('employee_id', intval($employeeId))
                ->where('tanggal', $tanggal)
                ->delete();
        }

        return $result;
    }

    public function getAttendanceLogs()
    {
        $employeeId = $this->request->getGet('employee_id');
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');
        $clientId = $this->request->getGet('client_id');
        $tanggal = $this->request->getGet('tanggal');

        $builder = $this->db->table('attendance_logs');
        $builder->select('attendance_logs.id, attendance_logs.employee_id, attendance_logs.log_date as tanggal, 
                          attendance_logs.status, attendance_logs.check_in as jam_masuk, 
                          attendance_logs.check_out as jam_keluar, attendance_logs.notes as keterangan, 
                          attendance_logs.created_at, attendance_logs.shift_scheme_id, attendance_logs.is_rapel, 
                          attendance_logs.payout_period, attendance_logs.calculated_work_hours, 
                          attendance_logs.calculated_overtime_hours, attendance_logs.is_incomplete,
                          employees.nama as employee_name, shift_schemes.name as shift_name,
                          employees.hari_kerja as employee_hari_kerja,
                          positions.hari_kerja as position_hari_kerja,
                          holiday_calendar.deskripsi as holiday_deskripsi,
                          early_arrival.eligible_minutes as ea_eligible_minutes,
                          early_arrival.status as ea_status');
        $builder->join('employees', 'employees.id = attendance_logs.employee_id', 'left');
        $builder->join('shift_schemes', 'shift_schemes.id = attendance_logs.shift_scheme_id', 'left');
        $builder->join('positions', 'positions.id = employees.position_id', 'left');
        $builder->join('holiday_calendar', 'holiday_calendar.tanggal = attendance_logs.log_date', 'left');
        $builder->join('early_arrival', 'early_arrival.attendance_id = attendance_logs.id', 'left');

        if ($employeeId) {
            $builder->where('attendance_logs.employee_id', intval($employeeId));
        }
        if ($clientId) {
            $builder->where('employees.client_id', intval($clientId));
        }
        if ($tanggal) {
            $builder->where('attendance_logs.log_date', $tanggal);
        } elseif ($bulan && $tahun) {
            $payoutPeriodStr = intval($bulan) . '-' . intval($tahun);
            $builder->groupStart()
                ->groupStart()
                    ->where('MONTH(attendance_logs.log_date)', intval($bulan))
                    ->where('YEAR(attendance_logs.log_date)', intval($tahun))
                ->groupEnd()
                ->orWhere('attendance_logs.payout_period', $payoutPeriodStr)
            ->groupEnd();
        }

        $builder->orderBy('attendance_logs.log_date', 'ASC');
        $logs = $builder->get()->getResultArray();

        // Enrich each log with is_holiday flag based on holiday_calendar + day of week + work schedule
        foreach ($logs as &$log) {
            $isHoliday = 0;
            $holidayName = $log['holiday_deskripsi'] ?? null;
            
            if (!empty($holidayName)) {
                // It's in the holiday_calendar
                $isHoliday = 1;
            } else {
                $dayOfWeek = date('w', strtotime($log['tanggal']));
                if ($dayOfWeek == 0) {
                    // Sunday is always a holiday
                    $isHoliday = 1;
                    $holidayName = 'Hari Minggu';
                } elseif ($dayOfWeek == 6) {
                    // Saturday is holiday only for 5-day work week
                    $workDays = intval($log['employee_hari_kerja'] ?: ($log['position_hari_kerja'] ?: 5));
                    if ($workDays < 6) {
                        $isHoliday = 1;
                        $holidayName = 'Hari Sabtu (5 hari kerja)';
                    }
                }
            }
            
            $log['is_holiday'] = $isHoliday;
            $log['holiday_name'] = $holidayName;
        }
        unset($log); // break reference

        $cutoffDay = 21;
        if ($clientId) {
            $config = $this->db->table('client_payroll_configs')
                ->where('client_id', intval($clientId))
                ->get()->getRow();
            if ($config && isset($config->cutoff_start)) {
                $cutoffDay = intval($config->cutoff_start);
            }
        }

        $isLateUpload = false;
        $cutoffDateStr = null;
        // Fetch holidays for the requested period
        $holidays = [];
        if ($bulan && $tahun) {
            $daysInMonth = intval(date('t', mktime(0, 0, 0, intval($bulan), 1, intval($tahun))));
            // Cutoff is for next month's payroll since this month's attendance is for next month's payroll
            $nextMonth = intval($bulan) + 1;
            $nextYear = intval($tahun);
            if ($nextMonth > 12) {
                $nextMonth = 1;
                $nextYear++;
            }
            $daysInNextMonth = intval(date('t', mktime(0, 0, 0, $nextMonth, 1, $nextYear)));
            $dayVal = min($cutoffDay, $daysInNextMonth);
            $cutoffDateStr = sprintf('%04d-%02d-%02d', $nextYear, $nextMonth, $dayVal);
            if (date('Y-m-d') > $cutoffDateStr) {
                $isLateUpload = true;
            }

            // Get all holidays for the month/year
            $startDate = sprintf('%04d-%02d-01', intval($tahun), intval($bulan));
            $endDate = sprintf('%04d-%02d-%02d', intval($tahun), intval($bulan), $daysInMonth);
            $holidayRows = $this->db->table('holiday_calendar')
                ->where('tanggal >=', $startDate)
                ->where('tanggal <=', $endDate)
                ->orderBy('tanggal', 'ASC')
                ->get()->getResultArray();
            foreach ($holidayRows as $h) {
                $holidays[$h['tanggal']] = $h['deskripsi'];
            }
        }

        return $this->respond([
            'data' => $logs,
            'is_late_upload' => $isLateUpload,
            'cutoff_date' => $cutoffDateStr,
            'holidays' => $holidays
        ]);
    }

    public function createAttendanceLog()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['employee_id']) || empty($data['tanggal'])) {
            return $this->failValidationErrors('employee_id dan tanggal wajib diisi');
        }

        $empInfo = $this->db->table('employees')
            ->select('tgl_masuk, start_contract, nama')
            ->where('id', intval($data['employee_id']))
            ->get()->getRow();

        if ($empInfo) {
            $joinDate = !empty($empInfo->tgl_masuk) ? $empInfo->tgl_masuk : ($empInfo->start_contract ?? null);
            if (!empty($joinDate) && strtotime($data['tanggal']) < strtotime($joinDate)) {
                return $this->failValidationErrors("Karyawan '{$empInfo->nama}' belum bergabung pada tanggal tersebut (Tanggal bergabung: " . date('d-m-Y', strtotime($joinDate)) . ").");
            }
        }

        $calc = $this->calculateShiftAttendance(
            $data['employee_id'],
            $data['tanggal'],
            $data['jam_masuk'] ?? null,
            $data['jam_keluar'] ?? null,
            $data['status'] ?? 'Hadir',
            $data['shift_scheme_id'] ?? null,
            $data['payout_period'] ?? null
        );

        // Cek duplikat hanya berdasarkan employee_id + log_date (abaikan shift_scheme_id)
        $existing = $this->db->table('attendance_logs')
            ->where('employee_id', intval($data['employee_id']))
            ->where('log_date', $data['tanggal'])
            ->get()->getRow();

        if ($existing) {
            $this->db->table('attendance_logs')->where('id', $existing->id)->update([
                'status' => $data['status'] ?? 'Hadir',
                'check_in' => $data['jam_masuk'] ?? null,
                'check_out' => $data['jam_keluar'] ?? null,
                'notes' => $data['keterangan'] ?? null,
                'shift_scheme_id'            => $calc['shift_scheme_id'],
                'is_rapel'                   => $calc['is_rapel'],
                'payout_period'              => $calc['payout_period'],
                'calculated_work_hours'      => $calc['calculated_work_hours'],
                'calculated_overtime_hours'  => $calc['calculated_overtime_hours'],
                'late_hours'                 => $calc['late_hours'],
                'late_minutes'               => $calc['late_minutes'],
                'late_penalty_hours'         => $calc['late_penalty_hours'],
                'denda_terlambat'            => $calc['denda_terlambat'],
                'early_leave_hours'          => $calc['early_leave_hours'],
                'early_leave_minutes'        => $calc['early_leave_minutes'] ?? 0,
                'is_early_leave_alfa'        => $calc['is_early_leave_alfa'],
                'denda_alfa'                 => $calc['denda_alfa'],
                'absent_penalty'             => $calc['absent_penalty'],
                'is_incomplete'              => $calc['is_incomplete'],
            ]);
            $this->syncEarlyArrival(
                $existing->id,
                intval($data['employee_id']),
                $data['tanggal'],
                $data['jam_masuk'] ?? null,
                $calc['shift_scheme_id'],
                $calc['payout_period']
            );
            return $this->respond([
                'message' => 'Attendance log berhasil diupdate',
                'payout_period' => $calc['payout_period']
            ]);
        }

        $insertData = [
            'employee_id'                => intval($data['employee_id']),
            'log_date'                   => $data['tanggal'],
            'status'                     => $data['status'] ?? 'Hadir',
            'check_in'                   => $data['jam_masuk'] ?? null,
            'check_out'                  => $data['jam_keluar'] ?? null,
            'notes'                      => $data['keterangan'] ?? null,
            'shift_scheme_id'            => $calc['shift_scheme_id'],
            'is_rapel'                   => $calc['is_rapel'],
            'payout_period'              => $calc['payout_period'],
            'calculated_work_hours'      => $calc['calculated_work_hours'],
            'calculated_overtime_hours'  => $calc['calculated_overtime_hours'],
            'late_hours'                 => $calc['late_hours'],
            'late_minutes'               => $calc['late_minutes'],
            'late_penalty_hours'         => $calc['late_penalty_hours'],
            'denda_terlambat'            => $calc['denda_terlambat'],
            'early_leave_hours'          => $calc['early_leave_hours'],
            'early_leave_minutes'        => $calc['early_leave_minutes'] ?? 0,
            'is_early_leave_alfa'        => $calc['is_early_leave_alfa'],
            'denda_alfa'                 => $calc['denda_alfa'],
            'absent_penalty'             => $calc['absent_penalty'],
            'is_incomplete'              => $calc['is_incomplete'],
        ];

        $this->db->table('attendance_logs')->insert($insertData);
        $attendanceId = $this->db->insertID();
        $this->syncEarlyArrival(
            $attendanceId,
            intval($data['employee_id']),
            $data['tanggal'],
            $data['jam_masuk'] ?? null,
            $calc['shift_scheme_id'],
            $calc['payout_period']
        );
        return $this->respondCreated([
            'message' => 'Attendance log berhasil ditambahkan',
            'payout_period' => $calc['payout_period']
        ]);
    }

    public function createAttendanceBulk()
    {
        $data = $this->request->getJSON(true);
        $logs = $data['logs'] ?? [];
        log_message('error', 'BULK LOGS RECEIVED: ' . json_encode($logs));
        $count = 0;
        $skippedCount = 0;
        $employeesById = [];
        $clientId = null;
        $payoutPeriod = null;

        if (!empty($logs)) {
            $firstLog = $logs[0];
            $empId = intval($firstLog['employee_id']);
            $payoutPeriod = $firstLog['payout_period'] ?? null;
            
            if ($empId && $payoutPeriod) {
                $emp = $this->db->table('employees')->where('id', $empId)->get()->getRow();
                if ($emp) {
                    $clientId = $emp->client_id;
                    $employees = $this->db->table('employees')->where('client_id', $clientId)->get()->getResultArray();
                    foreach ($employees as $e) {
                        $employeesById[intval($e['id'])] = $e;
                    }
                    $employeeIds = array_column($employees, 'id');
                    
                    if (!empty($employeeIds)) {
                        // Delete existing attendance logs for this client and payout period
                        $this->db->table('attendance_logs')
                            ->whereIn('employee_id', $employeeIds)
                            ->where('payout_period', $payoutPeriod)
                            ->delete();

                        // Delete existing early arrival logs for this client and payout period
                        $this->db->table('early_arrival')
                            ->whereIn('employee_id', $employeeIds)
                            ->where('payroll_period', $payoutPeriod)
                            ->delete();
                            
                        // Collect all unique dates from uploaded logs to clean up overtime
                        $uploadedDates = [];
                        foreach ($logs as $l) {
                            if (!empty($l['tanggal'])) {
                                $uploadedDates[] = $l['tanggal'];
                            }
                        }
                        $uploadedDates = array_unique($uploadedDates);
                        
                        // Delete ALL auto-generated pending overtime logs for these employees + dates
                        // regardless of payout_period (fixes cross-period cleanup)
                        if (!empty($uploadedDates)) {
                            $this->db->table('overtime_logs')
                                ->whereIn('employee_id', $employeeIds)
                                ->whereIn('tanggal', $uploadedDates)
                                ->where('status', 'Pending')
                                ->like('keterangan', 'Auto: shift', 'after')
                                ->delete();
                        }
                        
                        // Also clean up by payout_period for any dates NOT in the upload
                        $parts = explode('-', $payoutPeriod);
                        if (count($parts) === 2) {
                            $pMonth = intval($parts[0]);
                            $pYear = intval($parts[1]);
                            $this->db->table('overtime_logs')
                                ->whereIn('employee_id', $employeeIds)
                                ->where('status', 'Pending')
                                ->like('keterangan', 'Auto: shift', 'after')
                                ->groupStart()
                                    ->where('payout_period', $payoutPeriod)
                                    ->orGroupStart()
                                        ->groupStart()
                                            ->where('payout_period IS NULL')
                                            ->orWhere('payout_period', '')
                                        ->groupEnd()
                                        ->where('MONTH(tanggal)', $pMonth)
                                        ->where('YEAR(tanggal)', $pYear)
                                    ->groupEnd()
                                ->groupEnd()
                                ->delete();
                        }
                    }
                }
            }
        }

        foreach ($logs as $log) {
            if (empty($log['employee_id']) || empty($log['tanggal'])) {
                log_message('error', 'SKIPPED LOG DUE TO EMPTY FIELD: ' . json_encode($log));
                continue;
            }

            $currentEmpId = intval($log['employee_id']);
            $empInfo = null;
            if (isset($employeesById[$currentEmpId])) {
                $empInfo = $employeesById[$currentEmpId];
            } else {
                $empRow = $this->db->table('employees')->where('id', $currentEmpId)->get()->getRowArray();
                if ($empRow) {
                    $employeesById[$currentEmpId] = $empRow;
                    $empInfo = $empRow;
                }
            }

            if ($empInfo) {
                $joinDate = !empty($empInfo['tgl_masuk']) ? $empInfo['tgl_masuk'] : ($empInfo['start_contract'] ?? null);
                if (!empty($joinDate) && strtotime($log['tanggal']) < strtotime($joinDate)) {
                    log_message('info', "SKIPPED LOG: Employee {$empInfo['nama']} has not joined yet on {$log['tanggal']} (joined {$joinDate})");
                    $skippedCount++;
                    continue;
                }
            }

            $shiftSchemeId = $log['shift_scheme_id'] ?? null;

            // Jika shift_scheme_id kosong, coba resolve dari shift_name
            if (empty($shiftSchemeId) && !empty($log['shift_name'])) {
                $matchedShift = $this->db->table('shift_schemes')
                    ->where('LOWER(name)', strtolower(trim($log['shift_name'])))
                    ->get()->getRow();
                if (!$matchedShift) {
                    // Coba partial match
                    $matchedShift = $this->db->table('shift_schemes')
                        ->like('name', trim($log['shift_name']), 'none')
                        ->get()->getRow();
                }
                if ($matchedShift) {
                    $shiftSchemeId = $matchedShift->id;
                }
            }

            // Jika masih kosong, coba auto-detect shift dari jam masuk
            if (empty($shiftSchemeId) && !empty($log['jam_masuk'])) {
                $jamMasukInt = intval(explode(':', $log['jam_masuk'])[0]);
                // Shift pagi: masuk sebelum jam 12
                // Shift siang: masuk jam 12 ke atas
                $namaShiftGuess = $jamMasukInt < 12 ? 'Pagi' : 'Siang';
                $matchedShift = $this->db->table('shift_schemes')
                    ->where('name', $namaShiftGuess)
                    ->get()->getRow();
                if ($matchedShift) {
                    $shiftSchemeId = $matchedShift->id;
                }
            }

            $calc = $this->calculateShiftAttendance(
                $log['employee_id'],
                $log['tanggal'],
                $log['jam_masuk'] ?? null,
                $log['jam_keluar'] ?? null,
                $log['status'] ?? 'Hadir',
                $shiftSchemeId,
                $log['payout_period'] ?? null
            );

            // Cek duplikat hanya berdasarkan employee_id + log_date (abaikan shift_scheme_id)
            $existing = $this->db->table('attendance_logs')
                ->where('employee_id', intval($log['employee_id']))
                ->where('log_date', $log['tanggal'])
                ->get()->getRow();

            $logData = [
                'status'                     => $log['status'] ?? 'Hadir',
                'check_in'                   => $log['jam_masuk'] ?? null,
                'check_out'                  => $log['jam_keluar'] ?? null,
                'notes'                      => $log['keterangan'] ?? null,
                'shift_scheme_id'            => $calc['shift_scheme_id'],
                'is_rapel'                   => $calc['is_rapel'],
                'payout_period'              => $calc['payout_period'],
                'calculated_work_hours'      => min(9999.99, max(0, floatval($calc['calculated_work_hours']))),
                'calculated_overtime_hours'  => min(9999.99, max(0, floatval($calc['calculated_overtime_hours']))),
                'late_hours'                 => min(9999.99, max(0, floatval($calc['late_hours']))),
                'late_minutes'               => intval($calc['late_minutes']),
                'late_penalty_hours'         => intval($calc['late_penalty_hours']),
                'denda_terlambat'            => floatval($calc['denda_terlambat']),
                'early_leave_hours'          => min(9999.99, max(0, floatval($calc['early_leave_hours']))),
                'early_leave_minutes'        => intval($calc['early_leave_minutes'] ?? 0),
                'is_early_leave_alfa'        => intval($calc['is_early_leave_alfa']),
                'denda_alfa'                 => floatval($calc['denda_alfa']),
                'absent_penalty'             => floatval($calc['absent_penalty']),
                'is_incomplete'              => $calc['is_incomplete'],
            ];

            if ($existing) {
                $this->db->table('attendance_logs')->where('id', $existing->id)->update($logData);
                $this->syncEarlyArrival(
                    $existing->id,
                    intval($log['employee_id']),
                    $log['tanggal'],
                    $log['jam_masuk'] ?? null,
                    $calc['shift_scheme_id'],
                    $calc['payout_period']
                );
            } else {
                $logData['employee_id'] = intval($log['employee_id']);
                $logData['log_date'] = $log['tanggal'];
                $this->db->table('attendance_logs')->insert($logData);
                $attendanceId = $this->db->insertID();
                $this->syncEarlyArrival(
                    $attendanceId,
                    intval($log['employee_id']),
                    $log['tanggal'],
                    $log['jam_masuk'] ?? null,
                    $calc['shift_scheme_id'],
                    $calc['payout_period']
                );
            }
            $count++;
        }

        // Trigger automatic recalculation of payroll summaries
        if ($payoutPeriod && $clientId) {
            $parts = explode('-', $payoutPeriod);
            if (count($parts) === 2) {
                $pMonth = intval($parts[0]);
                $pYear = intval($parts[1]);
                $period = $this->db->table('payroll_periods')
                                   ->where('client_id', $clientId)
                                   ->where('bulan', $pMonth)
                                   ->where('tahun', $pYear)
                                   ->get()->getRow();
                if ($period) {
                    // Reset is_manual = 0 for client employees in this period so that recalculation is allowed
                    $pkwtIds = $this->db->table('pkwt')
                                        ->select('id')
                                        ->where('client_id', intval($clientId))
                                        ->get()->getResultArray();
                    $pkwtIdsList = array_column($pkwtIds, 'id');
                    if (!empty($pkwtIdsList)) {
                        $this->db->table('payroll_attendance')
                                 ->where('period_id', $period->id)
                                 ->whereIn('pkwt_id', $pkwtIdsList)
                                 ->update(['is_manual' => 0]);
                    }
                    // Run the sync methods immediately to update payroll_attendance summaries
                    $this->syncEmployeesToPKWT($clientId);
                    $this->syncOvertimeToPayrollAttendance($period->id, $clientId);
                    $this->syncEarlyArrivalToPayrollAttendance($period->id, $clientId);
                }
            }
        }

        $msg = "Berhasil menyimpan {$count} attendance logs.";
        if ($skippedCount > 0) {
            $msg .= " {$skippedCount} logs dilewati karena tanggal absensi mendahului tanggal bergabung karyawan.";
        }
        return $this->respondCreated(['message' => $msg]);
    }

    public function updateAttendanceLog($id)
    {
        $data = $this->request->getJSON(true);
        $old = $this->db->table('attendance_logs')->where('id', $id)->get()->getRow();
        if (!$old) {
            return $this->fail('Attendance log tidak ditemukan');
        }

        $empInfo = $this->db->table('employees')
            ->select('tgl_masuk, start_contract, nama')
            ->where('id', intval($old->employee_id))
            ->get()->getRow();

        if ($empInfo) {
            $joinDate = !empty($empInfo->tgl_masuk) ? $empInfo->tgl_masuk : ($empInfo->start_contract ?? null);
            if (!empty($joinDate)) {
                $tanggal = $data['tanggal'] ?? $old->log_date;
                if (strtotime($tanggal) < strtotime($joinDate)) {
                    return $this->failValidationErrors("Karyawan '{$empInfo->nama}' belum bergabung pada tanggal tersebut (Tanggal bergabung: " . date('d-m-Y', strtotime($joinDate)) . ").");
                }
            }
        }

        $calc = $this->calculateShiftAttendance(
            $old->employee_id,
            $old->log_date,
            $data['jam_masuk'] ?? $old->check_in,
            $data['jam_keluar'] ?? $old->check_out,
            $data['status'] ?? $old->status,
            $data['shift_scheme_id'] ?? $old->shift_scheme_id,
            $data['payout_period'] ?? $old->payout_period
        );

        $updateData = [
            'status' => $data['status'] ?? $old->status,
            'check_in' => $data['jam_masuk'] ?? $old->check_in,
            'check_out' => $data['jam_keluar'] ?? $old->check_out,
            'notes' => $data['keterangan'] ?? $old->notes,
            'shift_scheme_id' => $calc['shift_scheme_id'],
            'is_rapel' => $calc['is_rapel'],
            'payout_period' => $calc['payout_period'],
            'calculated_work_hours' => $calc['calculated_work_hours'],
            'calculated_overtime_hours' => $calc['calculated_overtime_hours'],
            'late_hours' => $calc['late_hours'],
            'late_minutes' => $calc['late_minutes'] ?? 0,
            'late_penalty_hours' => $calc['late_penalty_hours'] ?? 0,
            'denda_terlambat' => $calc['denda_terlambat'] ?? 0,
            'early_leave_hours' => $calc['early_leave_hours'],
            'early_leave_minutes' => $calc['early_leave_minutes'] ?? 0,
            'is_early_leave_alfa' => $calc['is_early_leave_alfa'] ?? 0,
            'denda_alfa' => $calc['denda_alfa'] ?? 0,
            'absent_penalty' => $calc['absent_penalty'] ?? 0,
            'is_incomplete' => $calc['is_incomplete']
        ];

        $this->db->table('attendance_logs')->where('id', $id)->update($updateData);
        $this->syncEarlyArrival(
            $id,
            $old->employee_id,
            $old->log_date,
            $data['jam_masuk'] ?? $old->check_in,
            $calc['shift_scheme_id'],
            $calc['payout_period']
        );
        return $this->respond([
            'message' => 'Attendance log berhasil diupdate',
            'payout_period' => $calc['payout_period']
        ]);
    }

    public function deleteAttendanceLog($id)
    {
        $old = $this->db->table('attendance_logs')->where('id', $id)->get()->getRow();
        if ($old) {
            // Also clean up automatic overtime log
            $this->db->table('overtime_logs')
                ->where('employee_id', $old->employee_id)
                ->where('tanggal', $old->log_date)
                ->delete();
        }
        // Clean up early arrival log
        $this->db->table('early_arrival')->where('attendance_id', $id)->delete();
        $this->db->table('attendance_logs')->where('id', $id)->delete();
        return $this->respondDeleted(['message' => 'Attendance log berhasil dihapus']);
    }

    // --- OVERTIME LOGS ---
    public function getOvertimeLogs()
    {
        $employeeId = $this->request->getGet('employee_id');
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');
        $clientId = $this->request->getGet('client_id');

        $builder = $this->db->table('overtime_logs');
        $builder->select('overtime_logs.*, employees.nama as employee_name, employees.nik as employee_nik, attendance_logs.check_in as jam_masuk, attendance_logs.check_out as jam_keluar');
        $builder->join('employees', 'employees.id = overtime_logs.employee_id', 'left');
        $builder->join('attendance_logs', 'attendance_logs.employee_id = overtime_logs.employee_id AND attendance_logs.log_date = overtime_logs.tanggal', 'left');

        if ($employeeId) {
            $builder->where('overtime_logs.employee_id', intval($employeeId));
        }
        if ($clientId) {
            $builder->where('employees.client_id', intval($clientId));
        }
        if ($bulan && $tahun) {
            $payoutPeriodStr = intval($bulan) . '-' . intval($tahun);
            $builder->groupStart()
                ->groupStart()
                    ->where('MONTH(overtime_logs.tanggal)', intval($bulan))
                    ->where('YEAR(overtime_logs.tanggal)', intval($tahun))
                ->groupEnd()
                ->orWhere('overtime_logs.payout_period', $payoutPeriodStr)
            ->groupEnd();
        }

        $builder->orderBy('overtime_logs.tanggal', 'ASC');
        $logs = $builder->get()->getResultArray();
        return $this->respond($logs);
    }

    public function createOvertimeLog()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['employee_id']) || empty($data['tanggal']) || !isset($data['jam_lembur'])) {
            return $this->failValidationErrors('employee_id, tanggal, dan jam_lembur wajib diisi');
        }

        $jamLembur = floatval($data['jam_lembur']);
        $isHoliday = intval($data['is_holiday'] ?? 0);

        if ($jamLembur <= 0) {
            return $this->failValidationErrors('Jam lembur harus lebih dari 0!');
        }

        $empInfo = $this->db->table('employees')
            ->select('tgl_masuk, start_contract, nama')
            ->where('id', intval($data['employee_id']))
            ->get()->getRow();

        if ($empInfo) {
            $joinDate = !empty($empInfo->tgl_masuk) ? $empInfo->tgl_masuk : ($empInfo->start_contract ?? null);
            if (!empty($joinDate) && strtotime($data['tanggal']) < strtotime($joinDate)) {
                return $this->failValidationErrors("Karyawan '{$empInfo->nama}' belum bergabung pada tanggal tersebut (Tanggal bergabung: " . date('d-m-Y', strtotime($joinDate)) . ").");
            }
        }

        // Auto-detect holiday from holiday_calendar and employee's work schedule
        if (!$isHoliday) {
            $holiday = $this->db->table('holiday_calendar')
                ->where('tanggal', $data['tanggal'])
                ->get()->getRow();
            if ($holiday) {
                $isHoliday = 1;
            } else {
                $dayOfWeek = date('w', strtotime($data['tanggal']));
                if ($dayOfWeek == 0) {
                    $isHoliday = 1;
                } elseif ($dayOfWeek == 6) {
                    // Check employee's working days config
                    $emp = $this->db->table('employees')
                        ->select('employees.hari_kerja, positions.hari_kerja as position_hari_kerja')
                        ->join('positions', 'positions.id = employees.position_id', 'left')
                        ->where('employees.id', intval($data['employee_id']))
                        ->get()->getRow();
                    $workDaysPerWeek = 5;
                    if ($emp) {
                        $workDaysPerWeek = intval($emp->hari_kerja ?: ($emp->position_hari_kerja ?: 5));
                    }
                    // Saturday is weekend/holiday only for 5-day work weeks
                    $isHoliday = ($workDaysPerWeek < 6) ? 1 : 0;
                }
            }
        }

        // Cap regular working day overtime to 3 hours
        if (!$isHoliday && $jamLembur > 3.0) {
            $jamLembur = 3.0;
        }

        // Check duplicate
        $existing = $this->db->table('overtime_logs')
            ->where('employee_id', intval($data['employee_id']))
            ->where('tanggal', $data['tanggal'])
            ->get()->getRow();
        if ($existing) {
            $this->db->table('overtime_logs')->where('id', $existing->id)->update([
                'jam_lembur' => $jamLembur,
                'is_holiday' => $isHoliday,
                'keterangan' => $data['keterangan'] ?? null,
                'status' => 'Pending',
                'approved_by' => null,
                'approved_at' => null
            ]);
            $this->syncPayrollAttendanceOvertimeForLog($existing->id);
            return $this->respond(['message' => 'Overtime log berhasil diupdate']);
        }

        $insertData = [
            'employee_id' => intval($data['employee_id']),
            'tanggal' => $data['tanggal'],
            'jam_lembur' => $jamLembur,
            'is_holiday' => $isHoliday,
            'keterangan' => $data['keterangan'] ?? null,
            'status' => 'Pending',
            'approved_by' => null,
            'approved_at' => null
        ];

        $this->db->table('overtime_logs')->insert($insertData);
        $this->syncPayrollAttendanceOvertime($insertData['employee_id'], $insertData['tanggal']);
        return $this->respondCreated(['message' => 'Overtime log berhasil ditambahkan']);
    }

    public function importOvertimeLogs()
    {
        $db = \Config\Database::connect();
        $json = $this->request->getJSON(true);
        $logs = $json['logs'] ?? [];
        $payoutPeriodStr = $json['payout_period'] ?? null;
        
        if (empty($logs)) {
            return $this->failValidationErrors('Tidak ada data lembur yang diunggah.');
        }

        if (!empty($logs) && !empty($payoutPeriodStr)) {
            $firstLog = $logs[0];
            $nik = trim($firstLog['nik'] ?? '');
            $nama = trim($firstLog['nama'] ?? '');
            
            $employee = null;
            if (!empty($nik)) {
                $employee = $db->table('employees')->where('nik', $nik)->get()->getRowArray();
            }
            if (!$employee && !empty($nama)) {
                $employee = $db->table('employees')->where('LOWER(nama)', strtolower($nama))->get()->getRowArray();
            }
            if (!$employee && !empty($nama)) {
                $employee = $db->table('employees')->like('nama', $nama)->get()->getRowArray();
            }
            
            if ($employee) {
                $clientId = $employee['client_id'];
                $employees = $db->table('employees')->where('client_id', $clientId)->get()->getResultArray();
                $employeeIds = array_column($employees, 'id');
                
                if (!empty($employeeIds)) {
                    // Delete existing imported overtime logs for this client and period
                    // Only delete records that were clearly from a previous Excel import
                    $db->table('overtime_logs')
                        ->whereIn('employee_id', $employeeIds)
                        ->where('payout_period', $payoutPeriodStr)
                        ->like('keterangan', 'Imported from Excel')
                        ->delete();
                }
            }
        }

        $successCount = 0;
        $errorLogs = [];

        foreach ($logs as $index => $row) {
            $nik = trim($row['nik'] ?? '');
            $nama = trim($row['nama'] ?? '');
            $tanggal = trim($row['tanggal'] ?? '');
            $nominal = floatval($row['nominal'] ?? 0);

            if (empty($tanggal) || $nominal <= 0 || (empty($nik) && empty($nama))) {
                $errorLogs[] = "Baris " . ($index + 1) . ": Data tidak lengkap atau nominal <= 0.";
                continue;
            }

            // 1. Lookup Employee
            $employee = null;
            if (!empty($nik)) {
                $employee = $db->table('employees')->where('nik', $nik)->get()->getRowArray();
            }
            if (!$employee && !empty($nama)) {
                $employee = $db->table('employees')->where('LOWER(nama)', strtolower($nama))->get()->getRowArray();
            }
            if (!$employee && !empty($nama)) {
                $employee = $db->table('employees')->like('nama', $nama)->get()->getRowArray();
            }

            if (!$employee) {
                $errorLogs[] = "Baris " . ($index + 1) . ": Karyawan '" . ($nik ?: $nama) . "' tidak ditemukan.";
                continue;
            }

            // Check join date validation
            $joinDate = !empty($employee['tgl_masuk']) ? $employee['tgl_masuk'] : ($employee['start_contract'] ?? null);
            if (!empty($joinDate)) {
                if (strtotime($tanggal) < strtotime($joinDate)) {
                    $errorLogs[] = "Baris " . ($index + 1) . ": Karyawan '" . $employee['nama'] . "' belum bergabung pada tanggal tersebut (Tanggal bergabung: " . date('d-m-Y', strtotime($joinDate)) . ").";
                    continue;
                }
            }

            $empId = $employee['id'];
            $clientId = $employee['client_id'];

            // 2. Resolve Base Salary
            $baseSalary = floatval($employee['gaji_pokok'] ?? 0);
            $activeContract = $db->table('contracts')
                ->where('employee_id', $empId)
                ->where('status_pkwt', 'Aktif')
                ->orderBy('tgl_mulai', 'DESC')
                ->get()->getRow();
            if ($activeContract && floatval($activeContract->gaji_pokok) > 0) {
                $baseSalary = floatval($activeContract->gaji_pokok);
            } else {
                $payrollConfig = $db->table('client_payroll_configs')->where('client_id', $clientId)->get()->getRow();
                if ($payrollConfig) {
                    if ($payrollConfig->payroll_type === 'UMP' || $payrollConfig->payroll_type === 'UMK') {
                        if ($payrollConfig->minimum_wage_nominal > 0) {
                            $baseSalary = floatval($payrollConfig->minimum_wage_nominal);
                        }
                    } elseif ($payrollConfig->payroll_type === 'Nominal') {
                        if ($payrollConfig->custom_nominal > 0) {
                            $baseSalary = floatval($payrollConfig->custom_nominal);
                        }
                    }
                }
            }

            // 3. Resolve PKWT components
            $pkwt = $db->table('pkwt')
                ->where('client_id', $clientId)
                ->where('employee_name', $employee['nama'])
                ->where('status', 'Active')
                ->get()->getRow();

            $empComponents = [];
            if ($pkwt) {
                $dbComponents = $db->table('pkwt_components')
                    ->where('pkwt_id', $pkwt->id)
                    ->get()->getResultArray();
                foreach ($dbComponents as $comp) {
                    $isBasic = (isset($comp['jenis_komponen']) && $comp['jenis_komponen'] === 'basic_salary') || (stripos($comp['nama'], 'Gaji Pokok') !== false);
                    if (!$isBasic) {
                        $empComponents[] = [
                            'nama_komponen' => $comp['nama'],
                            'nilai' => floatval($comp['nilai']),
                            'sumber_nilai' => $comp['sumber_nilai'] ?? '',
                        ];
                    }
                }
            }

            // Resolve UMP/UMK values for UMP/UMK fallback in components
            $empMinimumWage = 0.0;
            if ($employee['minimum_wage_id']) {
                $mw = $db->table('minimum_wages')->where('id', $employee['minimum_wage_id'])->get()->getRow();
                if ($mw) {
                    $empMinimumWage = floatval($mw->nominal);
                }
            }
            $umpWageValue = $empMinimumWage;
            $umkWageValue = $empMinimumWage;

            // 4. Resolve Nominal Lembur Bulanan
            $nominalLemburBulanan = 0.0;
            foreach ($empComponents as $comp) {
                $compName = $comp['nama_komponen'] ?? '';
                if (stripos($compName, 'Lembur') !== false || stripos($compName, 'Overtime') !== false) {
                    $baseVal = floatval($comp['nilai']);
                    $sumberVal = $comp['sumber_nilai'] ?? 'nominal';
                    if ($sumberVal === 'ump') {
                        $baseVal = $umpWageValue * ($baseVal / 100);
                    } else if ($sumberVal === 'umk') {
                        $baseVal = $umkWageValue * ($baseVal / 100);
                    } else if ($sumberVal === 'ump_umk') {
                        $baseVal = $empMinimumWage * ($baseVal / 100);
                    }
                    $nominalLemburBulanan = $baseVal;
                    break;
                }
            }
            if ($nominalLemburBulanan <= 0.0) {
                $nominalLemburBulanan = $baseSalary;
            }

            // 5. Resolve hourly rate
            $workDaysConfig = isset($employee['hari_kerja']) ? intval($employee['hari_kerja']) : 5;
            $standardHours = ($workDaysConfig === 6) ? 48.0 : 40.0;
            $upahPerJam = $nominalLemburBulanan / $standardHours;

            $keterangan = trim($row['keterangan'] ?? '');
            $jamLembur = 0;

            if ($upahPerJam > 0) {
                // Normal path: reverse-calculate hours from nominal and hourly rate
                $jamLembur = round($nominal / $upahPerJam, 1);
            } else {
                // Fallback: try to extract hours from keterangan (e.g., "Lembur 2 jam", "3 hours", "5 jam")
                if (!empty($keterangan) && preg_match('/(\d+(?:[.,]\d+)?)\s*(?:jam|hour|hours|hr|hrs)/i', $keterangan, $matches)) {
                    $jamLembur = floatval(str_replace(',', '.', $matches[1]));
                }

                // If still no hours found, try extracting any number from keterangan
                if ($jamLembur <= 0 && !empty($keterangan) && preg_match('/(\d+(?:[.,]\d+)?)/', $keterangan, $matches)) {
                    $jamLembur = floatval(str_replace(',', '.', $matches[1]));
                }

                // Last resort: use nominal directly as hours (assume nominal = hours if small number)
                if ($jamLembur <= 0) {
                    if ($nominal <= 24) {
                        // If nominal is small enough to be hours directly
                        $jamLembur = $nominal;
                    } else {
                        // Skip this row - can't determine hours
                        $errorLogs[] = "Row " . ($index + 1) . ": Cannot calculate overtime hours for '" . $employee['nama'] . "' (no salary config and no hours in description).";
                        continue;
                    }
                }
            }

            if (empty($keterangan)) {
                $keterangan = 'Imported from Excel';
            }

            // 6. Detect holiday
            $isHoliday = 0;
            $holiday = $db->table('holiday_calendar')->where('tanggal', $tanggal)->get()->getRow();
            if ($holiday) {
                $isHoliday = 1;
            } else {
                $dayOfWeek = date('w', strtotime($tanggal));
                if ($dayOfWeek == 0) {
                    $isHoliday = 1;
                } elseif ($dayOfWeek == 6) {
                    $isHoliday = ($workDaysConfig < 6) ? 1 : 0;
                }
            }

            // Cap regular working day overtime to 3 hours
            if (!$isHoliday && $jamLembur > 3.0) {
                $jamLembur = 3.0;
            }

            // 7. Detect rapel status based on client's cut-off
            $cutoffStart = 21;
            $cutoffEnd = 20;
            $clientConfig = $this->resolveClientConfigForEmployee($empId);
            if ($clientConfig) {
                if (isset($clientConfig->cutoff_start) && $clientConfig->cutoff_start !== null && $clientConfig->cutoff_start !== '') {
                    $cutoffStart = intval($clientConfig->cutoff_start);
                }
                if (isset($clientConfig->cutoff_end) && $clientConfig->cutoff_end !== null && $clientConfig->cutoff_end !== '') {
                    $cutoffEnd = intval($clientConfig->cutoff_end);
                } else {
                    $cutoffEnd = $cutoffStart - 1;
                    if ($cutoffEnd < 1) {
                        $cutoffEnd = 31;
                    }
                }
            }

            $ts = strtotime($tanggal);
            $tYear = intval(date('Y', $ts));
            $tMonth = intval(date('n', $ts));
            $tDay = intval(date('j', $ts));

            if ($tDay > $cutoffEnd) {
                $natMonth = $tMonth + 1;
                $natYear = $tYear;
                if ($natMonth > 12) {
                    $natMonth = 1;
                    $natYear++;
                }
            } else {
                $natMonth = $tMonth;
                $natYear = $tYear;
            }
            $naturalPeriod = $natMonth . '-' . $natYear;

            $isRapel = 0;
            $finalPayoutPeriod = $naturalPeriod;

            if (!empty($payoutPeriodStr)) {
                $naturalVal = $natYear * 12 + $natMonth;
                
                $payoutMonth = null;
                $payoutYear = null;
                $parts = explode('-', $payoutPeriodStr);
                if (count($parts) === 2) {
                    $payoutMonth = intval($parts[0]);
                    $payoutYear = intval($parts[1]);
                }
                
                if ($payoutMonth && $payoutYear) {
                    $payoutVal = $payoutYear * 12 + $payoutMonth;
                    if ($naturalVal > $payoutVal) {
                        // Future natural period relative to upload period
                        $isRapel = 0;
                        $finalPayoutPeriod = $naturalPeriod;
                    } elseif ($naturalVal < $payoutVal) {
                        // Past natural period (rapel)
                        $isRapel = 1;
                        $finalPayoutPeriod = $payoutPeriodStr;
                    } else {
                        $isRapel = 0;
                        $finalPayoutPeriod = $naturalPeriod;
                    }
                }
            }

            // 8. Insert or Update Log
            $existing = $db->table('overtime_logs')
                ->where('employee_id', $empId)
                ->where('tanggal', $tanggal)
                ->get()->getRow();

            $approvedBy = session()->get('username') ?: 'Admin';

            $logData = [
                'employee_id'   => $empId,
                'tanggal'       => $tanggal,
                'jam_lembur'    => $jamLembur,
                'is_holiday'    => $isHoliday,
                'keterangan'    => $keterangan,
                'status'        => 'Pending',
                'approved_by'   => null,
                'approved_at'   => null,
                'is_rapel'      => $isRapel,
                'payout_period' => $finalPayoutPeriod
            ];

            if ($existing) {
                $db->table('overtime_logs')->where('id', $existing->id)->update($logData);
            } else {
                $db->table('overtime_logs')->insert($logData);
            }
            $this->syncPayrollAttendanceOvertime($empId, $tanggal);

            $successCount++;
        }

        return $this->respond([
            'success' => true,
            'imported_count' => $successCount,
            'errors' => $errorLogs
        ]);
    }

    public function updateOvertimeLog($id)
    {
        $data = $this->request->getJSON(true);
        $jamLembur = floatval($data['jam_lembur'] ?? 0);
        $isHoliday = intval($data['is_holiday'] ?? 0);

        if (!$isHoliday && $jamLembur > 3) {
            return $this->failValidationErrors('Lembur hari kerja maksimal 3 jam per hari!');
        }

        $updateData = [
            'jam_lembur' => $jamLembur,
            'is_holiday' => $isHoliday,
            'keterangan' => $data['keterangan'] ?? null,
            'status' => 'Pending',
            'approved_by' => null,
            'approved_at' => null
        ];

        $this->db->table('overtime_logs')->where('id', $id)->update($updateData);
        $this->syncPayrollAttendanceOvertimeForLog($id);
        return $this->respond(['message' => 'Overtime log berhasil diupdate']);
    }

    public function deleteOvertimeLog($id)
    {
        $log = $this->db->table('overtime_logs')->where('id', intval($id))->get()->getRow();
        if ($log) {
            $employeeId = $log->employee_id;
            $tanggal = $log->tanggal;
            $this->db->table('overtime_logs')->where('id', $id)->delete();
            $this->syncPayrollAttendanceOvertime($employeeId, $tanggal);
        } else {
            $this->db->table('overtime_logs')->where('id', $id)->delete();
        }
        return $this->respondDeleted(['message' => 'Overtime log berhasil dihapus']);
    }

    public function approveOvertimeLog($id)
    {
        $approvedBy = session()->get('username') ?: 'Admin';
        $this->db->table('overtime_logs')->where('id', $id)->update([
            'status' => 'Approved',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
        $this->syncPayrollAttendanceOvertimeForLog($id);
        return $this->respond(['message' => 'Overtime log berhasil disetujui.']);
    }

    public function rejectOvertimeLog($id)
    {
        $approvedBy = session()->get('username') ?: 'Admin';
        $this->db->table('overtime_logs')->where('id', $id)->update([
            'status' => 'Rejected',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
        $this->syncPayrollAttendanceOvertimeForLog($id);
        return $this->respond(['message' => 'Overtime log berhasil ditolak.']);
    }

    public function bulkApproveOvertimeLogs()
    {
        $json = $this->request->getJSON(true);
        $ids = $json['ids'] ?? [];
        if (empty($ids)) {
            return $this->failValidationErrors('Tidak ada ID lembur yang dipilih.');
        }
        $approvedBy = session()->get('username') ?: 'Admin';
        
        $logs = $this->db->table('overtime_logs')->whereIn('id', $ids)->get()->getResult();
        
        $this->db->table('overtime_logs')->whereIn('id', $ids)->update([
            'status' => 'Approved',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ]);

        foreach ($logs as $log) {
            $this->syncPayrollAttendanceOvertime($log->employee_id, $log->tanggal);
        }
        
        return $this->respond(['message' => count($ids) . ' data lembur berhasil disetujui.']);
    }

    public function bulkRejectOvertimeLogs()
    {
        $json = $this->request->getJSON(true);
        $ids = $json['ids'] ?? [];
        if (empty($ids)) {
            return $this->failValidationErrors('Tidak ada ID lembur yang dipilih.');
        }
        $approvedBy = session()->get('username') ?: 'Admin';
        
        $logs = $this->db->table('overtime_logs')->whereIn('id', $ids)->get()->getResult();
        
        $this->db->table('overtime_logs')->whereIn('id', $ids)->update([
            'status' => 'Rejected',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ]);

        foreach ($logs as $log) {
            $this->syncPayrollAttendanceOvertime($log->employee_id, $log->tanggal);
        }
        
        return $this->respond(['message' => count($ids) . ' data lembur berhasil ditolak.']);
    }

    // --- EARLY ARRIVAL LOGS ---
    public function getEarlyArrivalLogs()
    {
        $employeeId = $this->request->getGet('employee_id');
        $clientId = $this->request->getGet('client_id');
        $departmentId = $this->request->getGet('department_id');
        $status = $this->request->getGet('status');
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');

        $builder = $this->db->table('early_arrival');
        $builder->select('early_arrival.*, employees.nama as employee_name, employees.nik as employee_nik, shift_schemes.name as shift_name');
        $builder->join('employees', 'employees.id = early_arrival.employee_id', 'left');
        $builder->join('shift_schemes', 'shift_schemes.id = early_arrival.shift_id', 'left');

        if ($employeeId) {
            $builder->where('early_arrival.employee_id', intval($employeeId));
        }
        if ($clientId) {
            $builder->where('employees.client_id', intval($clientId));
        }
        if ($departmentId) {
            $builder->where('employees.department_id', intval($departmentId));
        }
        if ($status) {
            $builder->where('early_arrival.status', $status);
        }
        if ($bulan && $tahun) {
            $payoutPeriodStr = intval($bulan) . '-' . intval($tahun);
            $builder->groupStart()
                ->groupStart()
                    ->where('MONTH(early_arrival.date)', intval($bulan))
                    ->where('YEAR(early_arrival.date)', intval($tahun))
                ->groupEnd()
                ->orWhere('early_arrival.payroll_period', $payoutPeriodStr)
            ->groupEnd();
        }

        $builder->orderBy('early_arrival.date', 'ASC');
        $logs = $builder->get()->getResultArray();
        return $this->respond($logs);
    }

    public function approveEarlyArrivalLog($id)
    {
        $approvedBy = session()->get('username') ?: 'Admin';
        $this->db->table('early_arrival')->where('id', $id)->update([
            'status' => 'APPROVED',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->respond(['message' => 'Early arrival log berhasil disetujui.']);
    }

    public function rejectEarlyArrivalLog($id)
    {
        $approvedBy = session()->get('username') ?: 'Admin';
        $this->db->table('early_arrival')->where('id', $id)->update([
            'status' => 'REJECTED',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->respond(['message' => 'Early arrival log berhasil ditolak.']);
    }

    public function bulkApproveEarlyArrivalLogs()
    {
        $json = $this->request->getJSON(true);
        $ids = $json['ids'] ?? [];
        if (empty($ids)) {
            return $this->failValidationErrors('Tidak ada ID early arrival yang dipilih.');
        }
        $approvedBy = session()->get('username') ?: 'Admin';
        
        $this->db->table('early_arrival')->whereIn('id', $ids)->update([
            'status' => 'APPROVED',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->respond(['message' => count($ids) . ' data early arrival berhasil disetujui.']);
    }

    public function bulkRejectEarlyArrivalLogs()
    {
        $json = $this->request->getJSON(true);
        $ids = $json['ids'] ?? [];
        if (empty($ids)) {
            return $this->failValidationErrors('Tidak ada ID early arrival yang dipilih.');
        }
        $approvedBy = session()->get('username') ?: 'Admin';
        
        $this->db->table('early_arrival')->whereIn('id', $ids)->update([
            'status' => 'REJECTED',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->respond(['message' => count($ids) . ' data early arrival berhasil ditolak.']);
    }

    public function resetEarlyArrivalLog($id)
    {
        $this->db->table('early_arrival')->where('id', $id)->update([
            'status' => 'PENDING',
            'approved_by' => null,
            'approved_at' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->respond(['message' => 'Early arrival log berhasil dikembalikan ke pending.']);
    }

    private function syncEarlyArrival($attendanceId, $employeeId, $tanggal, $checkIn, $shiftSchemeId = null, $payoutPeriod = null)
    {
        $db = \Config\Database::connect();

        // 1. Resolve employee's payroll scheme and get max early arrival limit
        $clientConfig = $this->resolveClientConfigForEmployee($employeeId);
        $maxMinutes = 180; // Default fallback
        if ($clientConfig && !empty($clientConfig->payroll_scheme_id)) {
            $payrollScheme = $db->table('payroll_schemes')->where('id', $clientConfig->payroll_scheme_id)->get()->getRow();
            if ($payrollScheme && isset($payrollScheme->max_early_arrival_minutes)) {
                $maxMinutes = intval($payrollScheme->max_early_arrival_minutes);
            }
        }

        // If early arrival is disabled (maxMinutes <= 0), delete any existing early arrival log for this attendance
        if ($maxMinutes <= 0) {
            $db->table('early_arrival')->where('attendance_id', $attendanceId)->delete();
            return;
        }

        // 2. Resolve shift scheme and get shift start time
        if (!$shiftSchemeId) {
            $att = $db->table('attendance_logs')->where('id', $attendanceId)->get()->getRow();
            if ($att) {
                $shiftSchemeId = $att->shift_scheme_id;
            }
        }

        if (!$shiftSchemeId) {
            $db->table('early_arrival')->where('attendance_id', $attendanceId)->delete();
            return;
        }

        $shift = $db->table('shift_schemes')->where('id', $shiftSchemeId)->get()->getRow();
        if (!$shift || empty($shift->start_time) || empty($checkIn)) {
            $db->table('early_arrival')->where('attendance_id', $attendanceId)->delete();
            return;
        }

        // 3. Compare check-in and shift start
        $shiftStart = $shift->start_time; // e.g. "08:00"
        
        $shiftStartSecs = strtotime($tanggal . ' ' . $shiftStart);
        $checkInSecs = strtotime($tanggal . ' ' . $checkIn);

        if ($checkInSecs === false || $shiftStartSecs === false) {
            $db->table('early_arrival')->where('attendance_id', $attendanceId)->delete();
            return;
        }

        // Check if Check-in < Shift Start
        if ($checkInSecs < $shiftStartSecs) {
            $earlyMinutes = intval(($shiftStartSecs - $checkInSecs) / 60);
            
            $eligibleMinutes = $earlyMinutes;
            if ($eligibleMinutes > $maxMinutes) {
                $eligibleMinutes = $maxMinutes;
            }

            $existing = $db->table('early_arrival')->where('attendance_id', $attendanceId)->get()->getRow();

            $data = [
                'attendance_id' => $attendanceId,
                'employee_id' => $employeeId,
                'date' => $tanggal,
                'shift_id' => $shiftSchemeId,
                'shift_start_time' => $shiftStart,
                'check_in_time' => $checkIn,
                'early_minutes' => $earlyMinutes,
                'eligible_minutes' => $eligibleMinutes,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($existing) {
                if ($existing->payroll_status !== 'PROCESSED') {
                    if ($existing->check_in_time !== $checkIn || $existing->early_minutes !== $earlyMinutes) {
                        $data['status'] = 'PENDING';
                    }
                    $db->table('early_arrival')->where('id', $existing->id)->update($data);
                }
            } else {
                $data['status'] = 'PENDING';
                $data['payroll_status'] = 'NOT_PROCESSED';
                $data['payroll_period'] = $payoutPeriod;
                $data['created_at'] = date('Y-m-d H:i:s');
                $db->table('early_arrival')->insert($data);
            }
        } else {
            $db->table('early_arrival')->where('attendance_id', $attendanceId)->delete();
        }
    }

    // --- HOLIDAY CALENDAR ---
    public function getHolidays()
    {
        $tahun = $this->request->getGet('tahun');
        $builder = $this->db->table('holiday_calendar');
        if ($tahun) {
            $builder->where('tahun', intval($tahun));
        }
        $builder->orderBy('tanggal', 'ASC');
        $holidays = $builder->get()->getResultArray();
        return $this->respond($holidays);
    }

    public function createHoliday()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['tanggal']) || empty($data['deskripsi'])) {
            return $this->failValidationErrors('tanggal dan deskripsi wajib diisi');
        }

        // Check duplicate
        $existing = $this->db->table('holiday_calendar')
            ->where('tanggal', $data['tanggal'])
            ->get()->getRow();
        if ($existing) {
            return $this->failValidationErrors('Tanggal libur sudah terdaftar!');
        }

        $tahun = intval(date('Y', strtotime($data['tanggal'])));
        $insertData = [
            'tanggal' => $data['tanggal'],
            'deskripsi' => $data['deskripsi'],
            'tahun' => $tahun,
        ];

        $this->db->table('holiday_calendar')->insert($insertData);
        $this->logActivity("Menambahkan hari libur: " . $data['deskripsi'] . " (" . $data['tanggal'] . ")");
        return $this->respondCreated(['message' => 'Hari libur berhasil ditambahkan']);
    }

    public function syncGoogleHolidays()
    {
        $url = 'https://calendar.google.com/calendar/ical/id.indonesian%23holiday%40group.v.calendar.google.com/public/basic.ics';
        
        // Fetch ICS using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $icsContent = curl_exec($ch);
        curl_close($ch);

        if (!$icsContent) {
            return $this->fail('Gagal mengunduh data dari Google Calendar.');
        }

        // Parse ICS file
        $events = [];
        $lines = explode("\n", $icsContent);
        $currentEvent = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if ($line === 'BEGIN:VEVENT') {
                $currentEvent = [];
            } elseif ($line === 'END:VEVENT') {
                if ($currentEvent && !empty($currentEvent['start']) && !empty($currentEvent['summary'])) {
                    $events[] = $currentEvent;
                }
                $currentEvent = null;
            } elseif ($currentEvent !== null) {
                if (strpos($line, ':') !== false) {
                    list($key, $val) = explode(':', $line, 2);
                    $keyClean = explode(';', $key)[0]; // strip parameters like VALUE=DATE

                    if ($keyClean === 'DTSTART') {
                        $dateStr = trim($val);
                        if (strlen($dateStr) >= 8) {
                            $yyyy = substr($dateStr, 0, 4);
                            $mm = substr($dateStr, 4, 2);
                            $dd = substr($dateStr, 6, 2);
                            $currentEvent['start'] = "$yyyy-$mm-$dd";
                        }
                    } elseif ($keyClean === 'SUMMARY') {
                        $currentEvent['summary'] = str_replace(['\\,', '\\;'], [',', ';'], trim($val));
                    }
                }
            }
        }

        if (empty($events)) {
            return $this->fail('Tidak ada hari libur yang ditemukan dalam Google Calendar.');
        }

        $insertedCount = 0;
        $updatedCount = 0;

        foreach ($events as $evt) {
            $tanggal = $evt['start'];
            $deskripsi = $evt['summary'];
            $tahun = intval(date('Y', strtotime($tanggal)));

            // Check if already exists
            $existing = $this->db->table('holiday_calendar')
                ->where('tanggal', $tanggal)
                ->get()->getRow();

            if ($existing) {
                if ($existing->deskripsi !== $deskripsi) {
                    $this->db->table('holiday_calendar')
                        ->where('id', $existing->id)
                        ->update(['deskripsi' => $deskripsi]);
                    $updatedCount++;
                }
            } else {
                $this->db->table('holiday_calendar')->insert([
                    'tanggal' => $tanggal,
                    'deskripsi' => $deskripsi,
                    'tahun' => $tahun
                ]);
                $insertedCount++;
            }
        }

        $this->logActivity("Sinkronisasi hari libur dengan Google Calendar: Berhasil menambahkan {$insertedCount} dan memperbarui {$updatedCount} hari libur.");

        return $this->respond([
            'message' => "Sinkronisasi berhasil! Menambahkan {$insertedCount} hari libur baru, memperbarui {$updatedCount} hari libur."
        ]);
    }

    public function updateHoliday($id)
    {
        $data = $this->request->getJSON(true);
        $updateData = [
            'tanggal' => $data['tanggal'] ?? null,
            'deskripsi' => $data['deskripsi'] ?? null,
        ];
        if (!empty($updateData['tanggal'])) {
            $updateData['tahun'] = intval(date('Y', strtotime($updateData['tanggal'])));
        }

        $this->db->table('holiday_calendar')->where('id', $id)->update($updateData);
        return $this->respond(['message' => 'Hari libur berhasil diupdate']);
    }

    public function deleteHoliday($id)
    {
        $holiday = $this->db->table('holiday_calendar')->where('id', $id)->get()->getRow();
        $desc = $holiday ? $holiday->deskripsi : 'Unknown';
        $this->db->table('holiday_calendar')->where('id', $id)->delete();
        $this->logActivity("Menghapus hari libur: " . $desc);
        return $this->respondDeleted(['message' => 'Hari libur berhasil dihapus']);
    }

    public function createCompensationComponent()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('compensation_components')->insert($data);
        return $this->respondCreated(['message' => 'Komponen kompensasi berhasil ditambahkan']);
    }

    public function updateCompensationComponent($id)
    {
        $data = $this->request->getJSON(true);
        $this->db->table('compensation_components')->where('id', $id)->update($data);
        return $this->respond(['message' => 'Komponen kompensasi berhasil diupdate']);
    }

    public function deleteCompensationComponent($id)
    {
        $this->db->table('compensation_components')->where('id', $id)->delete();
        return $this->respondDeleted(['message' => 'Komponen kompensasi berhasil dihapus']);
    }

    // --- CLIENT PAYROLL CONFIGS ---
    public function getClientConfigs()
    {
        $configs = $this->db->table('clients')
                            ->select('clients.id as client_id, clients.nama as client_name, client_payroll_configs.id as setup_id, payroll_schemes.nama as payroll_scheme_name, tax_schemes.nama as tax_scheme_name, bpjs_schemes.nama as bpjs_scheme_name, compensation_schemes.nama as compensation_scheme_name, client_payroll_configs.pay_date, client_payroll_configs.cutoff_start, client_payroll_configs.cutoff_end, client_payroll_configs.payroll_scheme_id, client_payroll_configs.tax_scheme_id, client_payroll_configs.bpjs_scheme_id, client_payroll_configs.compensation_scheme_id, client_payroll_configs.payroll_type, client_payroll_configs.minimum_wage_id, client_payroll_configs.custom_nominal, minimum_wages.nama_daerah as minimum_wage_region, minimum_wages.nominal as minimum_wage_nominal, client_payroll_configs.division_id, client_payroll_configs.department_id, client_payroll_configs.position_id, divisions.nama as division_name, departments.nama as department_name, positions.nama as position_name')
                            ->join('client_payroll_configs', 'client_payroll_configs.client_id = clients.id', 'left')
                            ->join('payroll_schemes', 'payroll_schemes.id = client_payroll_configs.payroll_scheme_id', 'left')
                            ->join('tax_schemes', 'tax_schemes.id = client_payroll_configs.tax_scheme_id', 'left')
                            ->join('tax_schemes as bpjs_schemes', 'bpjs_schemes.id = client_payroll_configs.bpjs_scheme_id', 'left')
                            ->join('compensation_schemes', 'compensation_schemes.id = client_payroll_configs.compensation_scheme_id', 'left')
                            ->join('minimum_wages', 'minimum_wages.id = client_payroll_configs.minimum_wage_id', 'left')
                            ->join('divisions', 'divisions.id = client_payroll_configs.division_id', 'left')
                            ->join('departments', 'departments.id = client_payroll_configs.department_id', 'left')
                            ->join('positions', 'positions.id = client_payroll_configs.position_id', 'left')
                            ->get()
                            ->getResult();
        return $this->respond($configs);
    }

    public function saveClientConfig()
    {
        $data = $this->request->getJSON(true);
        $clientId = $data['client_id'];
        
        $divId = $data['division_id'] ?? null;
        $deptId = $data['department_id'] ?? null;
        $posId = $data['position_id'] ?? null;
        
        $divisionId = !empty($data['division_id']) ? intval($data['division_id']) : null;
        $departmentId = !empty($data['department_id']) ? intval($data['department_id']) : null;
        $positionId = !empty($data['position_id']) ? intval($data['position_id']) : null;

        $data['division_id'] = $divisionId;
        $data['department_id'] = $departmentId;
        $data['position_id'] = $positionId;
        
        // Handle hari_kerja for positions
        if ($positionId && isset($data['hari_kerja']) && $data['hari_kerja'] !== '') {
            $this->db->table('positions')->where('id', $positionId)->update(['hari_kerja' => intval($data['hari_kerja'])]);
        }
        unset($data['hari_kerja']);
        
        // Handle multi cut-off dates and legacy fallback
        if (isset($data['cutoff_gaji_pokok_start'])) {
            $data['cutoff_gaji_pokok_start'] = intval($data['cutoff_gaji_pokok_start']);
            $cutoffEnd = $data['cutoff_gaji_pokok_start'] - 1;
            $data['cutoff_gaji_pokok_end'] = $cutoffEnd < 1 ? 0 : $cutoffEnd;
            
            // Sync legacy fields for backward compatibility
            $data['cutoff_start'] = $data['cutoff_gaji_pokok_start'];
            $data['cutoff_end'] = $data['cutoff_gaji_pokok_end'];
        }

        if (isset($data['cutoff_lembur_start'])) {
            $data['cutoff_lembur_start'] = intval($data['cutoff_lembur_start']);
            $cutoffEnd = $data['cutoff_lembur_start'] - 1;
            $data['cutoff_lembur_end'] = $cutoffEnd < 1 ? 0 : $cutoffEnd;
        }

        if (isset($data['cutoff_insentif_start'])) {
            $data['cutoff_insentif_start'] = intval($data['cutoff_insentif_start']);
            $cutoffEnd = $data['cutoff_insentif_start'] - 1;
            $data['cutoff_insentif_end'] = $cutoffEnd < 1 ? 0 : $cutoffEnd;
        }

        // Explicitly cast rapel flags
        if (isset($data['is_rapel_gaji_pokok'])) {
            $data['is_rapel_gaji_pokok'] = $data['is_rapel_gaji_pokok'] ? 1 : 0;
        }
        if (isset($data['is_rapel_lembur'])) {
            $data['is_rapel_lembur'] = $data['is_rapel_lembur'] ? 1 : 0;
        }
        if (isset($data['is_rapel_insentif'])) {
            $data['is_rapel_insentif'] = $data['is_rapel_insentif'] ? 1 : 0;
        }

        
        // Cek jika sudah ada skema untuk level organisasi ini yang SPESIFIK
        $query = $this->db->table('client_payroll_configs')->where('client_id', $clientId);
        if ($divisionId) $query->where('division_id', $divisionId); else $query->where('division_id IS NULL');
        if ($departmentId) $query->where('department_id', $departmentId); else $query->where('department_id IS NULL');
        if ($positionId) $query->where('position_id', $positionId); else $query->where('position_id IS NULL');
        
        $existing = $query->get()->getRow();
        
        // JIKA SUDAH ADA, LANGSUNG UPDATE (UPSERT)
        $isUpdateAction = isset($data['id']) && $data['id'] > 0;
        
        if ($existing && !$isUpdateAction) {
            $data['id'] = $existing->id;
        }
        
        if (isset($data['id'])) {
            $idToUpdate = $data['id'];
            unset($data['id']); // Mencegah update kolom identity
            $this->db->table('client_payroll_configs')->where('id', $idToUpdate)->update($data);
        } else {
            $this->db->table('client_payroll_configs')->insert($data);
        }

        return $this->respond(['status' => 'success', 'message' => 'Pilihan skema berhasil disimpan']);
    }

    public function getClientConfigMappings($clientId)
    {
        $configs = $this->db->table('client_payroll_configs')
            ->select('
                client_payroll_configs.*,
                divisions.nama as division_name,
                departments.nama as department_name,
                positions.nama as position_name,
                payroll_schemes.nama as payroll_scheme_name,
                tax_schemes.nama as tax_scheme_name,
                bpjs_schemes.nama as bpjs_scheme_name,
                compensation_schemes.nama as compensation_scheme_name,
                minimum_wages.nama_daerah as minimum_wage_region,
                minimum_wages.nominal as minimum_wage_nominal
            ')
            ->join('divisions', 'divisions.id = client_payroll_configs.division_id', 'left')
            ->join('departments', 'departments.id = client_payroll_configs.department_id', 'left')
            ->join('positions', 'positions.id = client_payroll_configs.position_id', 'left')
            ->join('payroll_schemes', 'payroll_schemes.id = client_payroll_configs.payroll_scheme_id', 'left')
            ->join('tax_schemes', 'tax_schemes.id = client_payroll_configs.tax_scheme_id', 'left')
            ->join('tax_schemes as bpjs_schemes', 'bpjs_schemes.id = client_payroll_configs.bpjs_scheme_id', 'left')
            ->join('compensation_schemes', 'compensation_schemes.id = client_payroll_configs.compensation_scheme_id', 'left')
            ->join('minimum_wages', 'minimum_wages.id = client_payroll_configs.minimum_wage_id', 'left')
            ->where('client_payroll_configs.client_id', $clientId)
            ->get()->getResultArray();
            
        return $this->respond($configs);
    }
    
    public function deleteClientConfig($id)
    {
        $this->db->table('client_payroll_configs')->where('id', $id)->delete();
        return $this->respondDeleted(['id' => $id, 'message' => 'Mapping skema berhasil dihapus']);
    }

    // --- PKWT ---
    public function getPKWT()
    {
        $clientId = $this->request->getGet('client_id');
        $query = $this->db->table('pkwt')
                         ->select('pkwt.*, clients.nama as client_name')
                         ->join('clients', 'clients.id = pkwt.client_id');
        if ($clientId) {
            $query->where('pkwt.client_id', $clientId);
        }
        $data = $query->get()->getResultArray();
        
        foreach ($data as &$row) {
            $row['components'] = $this->db->table('pkwt_components')
                                          ->where('pkwt_id', $row['id'])
                                          ->get()
                                          ->getResultArray();

            // Try to find the employee to resolve UMP/UMK nominal
            $emp = $this->db->table('employees')
                            ->select('employees.*, minimum_wages.nominal as wage_nominal, minimum_wages.tipe as wage_tipe')
                            ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                            ->where('employees.client_id', $row['client_id'])
                            ->where('employees.nama', $row['employee_name'])
                            ->get()
                            ->getRow();

            foreach ($row['components'] as &$comp) {
                $comp['nilai_nominal'] = floatval($comp['nilai']);
                $isBasic = (stripos($comp['nama'], 'Gaji Pokok') !== false || ($comp['jenis_komponen'] ?? '') === 'basic_salary');
                if ($isBasic) {
                    if (isset($comp['sumber_nilai']) && ($comp['sumber_nilai'] === 'ump' || $comp['sumber_nilai'] === 'umk')) {
                        // Resolve proper UMP/UMK values using the enhanced resolver
                        $mwId = ($emp && isset($emp->minimum_wage_id)) ? $emp->minimum_wage_id : null;
                        $empProvince = null;
                        if ($emp && !empty($emp->work_location_id)) {
                            $wl = $this->db->table('work_locations')->where('id', $emp->work_location_id)->get()->getRow();
                            if ($wl && !empty($wl->provinsi)) {
                                $empProvince = $wl->provinsi;
                            }
                        }
                        $resolved = $this->resolveUmpUmk($mwId, null, $empProvince);
                        $wageValue = ($comp['sumber_nilai'] === 'ump') ? $resolved['ump'] : $resolved['umk'];
                        if ($wageValue > 0) {
                            $comp['nilai_nominal'] = $wageValue * (floatval($comp['nilai']) / 100);
                        }
                    }
                }
            }
        }
        
        return $this->respond($data);
    }

    public function createPKWT()
    {
        $data = $this->request->getJSON(true);
        $basicSalary = $data['basic_salary'] ?? 0;
        unset($data['basic_salary']);

        // 1. Insert PKWT
        $this->db->table('pkwt')->insert($data);
        $pkwtId = $this->db->insertID();

        $this->syncPKWTComponents($pkwtId, $basicSalary);

        $this->logActivity("Membuat PKWT baru untuk karyawan: " . ($data['employee_name'] ?? ''));
        return $this->respondCreated(['message' => 'PKWT berhasil dibuat dan gaji telah tergenerate']);
    }

    public function deletePKWT($id)
    {
        $pkwt = $this->db->table('pkwt')->where('id', $id)->get()->getRow();
        $employeeName = $pkwt ? $pkwt->employee_name : 'Unknown';
        $this->db->table('pkwt')->where('id', $id)->delete();
        $this->logActivity("Menghapus PKWT karyawan: " . $employeeName . " (ID: " . $id . ")");
        return $this->respondDeleted(['message' => 'PKWT berhasil dihapus']);
    }

    public function syncEmployeesPKWTEndpoint()
    {
        $clientId = $this->request->getGet('client_id');
        if (empty($clientId)) {
            return $this->fail('Client ID required');
        }
        
        $this->syncEmployeesToPKWT($clientId);
        return $this->respond(['message' => 'Employees synchronized to PKWT successfully']);
    }

    // --- PERIODS ---
    public function getPeriods()
    {
        $clientId = $this->request->getGet('client_id');
        $query = $this->db->table('payroll_periods')->orderBy('tahun', 'DESC')->orderBy('bulan', 'DESC');
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        $data = $query->get()->getResult();
        foreach ($data as &$row) {
            $row = $this->formatPeriodRow($row);
        }
        return $this->respond($data);
    }

    public function createPeriod()
    {
        $data = $this->request->getJSON(true);
        $clientId = $data['client_id'] ?? null;
        $bulan = intval($data['bulan']);
        $tahun = intval($data['tahun']);

        $exists = $this->db->table('payroll_periods')
                           ->where('client_id', $clientId)
                           ->where('bulan', $bulan)
                           ->where('tahun', $tahun)
                           ->get()->getRow();

        if ($exists) {
            return $this->respond(['message' => 'Periode sudah ada'], 200);
        }
        
        $insertData = [
            'client_id' => $clientId,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'status_cutoff' => 'Open',
            'pay_date' => null
        ];
        
        $this->db->table('payroll_periods')->insert($insertData);
        return $this->respondCreated(['message' => 'Periode baru berhasil dibuka']);
    }

    // --- ATTENDANCE / CUT-OFF ---
    public function getAttendance($periodId)
    {
        $clientId = $this->request->getGet('client_id');
        $this->syncEmployeesToPKWT($clientId);
        $this->syncOvertimeToPayrollAttendance($periodId, $clientId);
        $this->syncEarlyArrivalToPayrollAttendance($periodId, $clientId);

        $period = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        if (!$period) {
            return $this->respond([]);
        }

        $clientConfig = $this->db->table('client_payroll_configs')->where('client_id', $clientId)->get()->getRow();
        $daysInMonth = date('t', mktime(0, 0, 0, intval($period->bulan), 1, intval($period->tahun)));
        $cutoffStart = $clientConfig ? intval($clientConfig->cutoff_start) : 21;
        $cutoffEnd = $clientConfig ? intval($clientConfig->cutoff_end) : 20;
        if ($cutoffStart <= 0) $cutoffStart = 21;
        if ($cutoffEnd <= 0) $cutoffEnd = $daysInMonth;

        $bulan_start = intval($period->bulan);
        $tahun_start = intval($period->tahun);
        $bulan_end = $bulan_start;
        $tahun_end = $tahun_start;
        if ($cutoffStart > $cutoffEnd && $cutoffStart > 1) {
            $bulan_start -= 1;
            if ($bulan_start < 1) {
                $bulan_start = 12;
                $tahun_start -= 1;
            }
        }
        $startDateStr = sprintf('%04d-%02d-%02d', $tahun_start, $bulan_start, $cutoffStart);
        $endDateStr = sprintf('%04d-%02d-%02d', $tahun_end, $bulan_end, $cutoffEnd);

        // Get all PKWT and their attendance for this period
        $query = $this->db->table('pkwt')
                          ->select('pkwt.id as pkwt_id, pkwt.employee_name, pkwt.tipe_perjanjian, 
                                    payroll_attendance.hari_kerja, payroll_attendance.jam_lembur, 
                                    payroll_attendance.jam_lembur_hari_biasa, payroll_attendance.jam_lembur_hari_libur,
                                    payroll_attendance.potongan_absensi, payroll_attendance.bonus_tambahan,
                                    payroll_attendance.early_arrival_minutes,
                                    employees.id as employee_id, employees.employ_id, employees.nik, employees.gaji_pokok,
                                    employees.hari_kerja as employee_hari_kerja, positions.hari_kerja as position_hari_kerja,
                                    employees.tgl_masuk, employees.start_contract')
                          ->join('payroll_attendance', "payroll_attendance.pkwt_id = pkwt.id AND payroll_attendance.period_id = $periodId", 'left')
                          ->join('employees', "employees.client_id = pkwt.client_id AND employees.nama = pkwt.employee_name", 'left')
                          ->join('positions', 'positions.id = employees.position_id', 'left');
        if ($clientId) {
            $query->where('pkwt.client_id', $clientId);
        }
        $pkwts = $query->get()->getResult();

        foreach ($pkwts as $row) {
            $row->rapel_hari_kerja = 0;
            $row->rapel_jam_lembur = 0.0;
            $row->rapel_payout_period = '';

            // Override normal values to 0 if the employee joined after the cutoff date of the period
            $joinDate = !empty($row->tgl_masuk) ? $row->tgl_masuk : (!empty($row->start_contract) ? $row->start_contract : null);
            if ($joinDate && $joinDate > $endDateStr) {
                $row->hari_kerja = 0;
                $row->jam_lembur = 0.0;
                $row->jam_lembur_hari_biasa = 0.0;
                $row->jam_lembur_hari_libur = 0.0;
                $row->early_arrival_minutes = 0;
                $row->potongan_absensi = 0.0;
                $row->bonus_tambahan = 0.0;
            }

            if (!empty($row->employee_id)) {
                $payoutPeriodStr = intval($period->bulan) . '-' . intval($period->tahun);
                
                // Outgoing rapel search range (current calendar month)
                $monthStart = sprintf('%04d-%02d-01', $period->tahun, $period->bulan);
                $monthEnd = date('Y-m-t', strtotime($monthStart));

                // Query attendance logs with is_rapel = 1 that are either:
                // 1. Paid in this period (payout_period = $payoutPeriodStr) - Incoming rapel
                // 2. Logged in this period's calendar month - Outgoing rapel
                $rapelDays = $this->db->table('attendance_logs')
                                      ->where('employee_id', $row->employee_id)
                                      ->where('status', 'Hadir')
                                      ->where('is_rapel', 1)
                                      ->groupStart()
                                          ->where('payout_period', $payoutPeriodStr)
                                          ->orGroupStart()
                                              ->where('log_date >=', $monthStart)
                                              ->where('log_date <=', $monthEnd)
                                          ->groupEnd()
                                      ->groupEnd()
                                      ->get()->getResultArray();
                
                $row->rapel_hari_kerja = count($rapelDays);
                
                // Fallback for new hire rapel case before salary is generated (is_rapel = 0)
                $isNewHireRapelCase = ($joinDate && $joinDate > $endDateStr);
                if ($row->rapel_hari_kerja === 0 && $isNewHireRapelCase) {
                    $rawRapelDays = $this->db->table('attendance_logs')
                                          ->where('employee_id', $row->employee_id)
                                          ->where('status', 'Hadir')
                                          ->where('log_date >=', $joinDate)
                                          ->where('log_date <=', $monthEnd)
                                          ->get()->getResultArray();
                    $row->rapel_hari_kerja = count($rawRapelDays);

                    // If still 0, check if no logs exist at all for this employee in that range
                    if ($row->rapel_hari_kerja === 0) {
                        $totalLogsInRange = $this->db->table('attendance_logs')
                                                     ->where('employee_id', $row->employee_id)
                                                     ->where('log_date >=', $joinDate)
                                                     ->where('log_date <=', $monthEnd)
                                                     ->countAllResults();
                        if ($totalLogsInRange === 0) {
                            // Fallback to calendar working days based on work schedule config
                            $workDaysConfig = 5;
                            if (isset($row->employee_hari_kerja) && intval($row->employee_hari_kerja) > 0) {
                                $workDaysConfig = intval($row->employee_hari_kerja);
                            } elseif (isset($row->position_hari_kerja) && intval($row->position_hari_kerja) > 0) {
                                $workDaysConfig = intval($row->position_hari_kerja);
                            }
                            $row->rapel_hari_kerja = $this->getStandardWorkingDaysInRange($joinDate, $monthEnd, $workDaysConfig);
                        }
                    }
                }

                if ($row->rapel_hari_kerja > 0) {
                    $row->rapel_payout_period = !empty($rapelDays) ? $rapelDays[0]['payout_period'] : '';
                }

                // Query overtime logs with is_rapel = 1 and status = Approved
                $rapelOtSum = $this->db->table('overtime_logs')
                                      ->selectSum('jam_lembur')
                                      ->where('employee_id', $row->employee_id)
                                      ->where('status', 'Approved')
                                      ->where('is_rapel', 1)
                                      ->groupStart()
                                          ->where('payout_period', $payoutPeriodStr)
                                          ->orGroupStart()
                                              ->where('tanggal >=', $monthStart)
                                              ->where('tanggal <=', $monthEnd)
                                          ->groupEnd()
                                      ->groupEnd()
                                      ->get()->getRow();
                $row->rapel_jam_lembur = $rapelOtSum ? floatval($rapelOtSum->jam_lembur) : 0.0;

                if ($row->rapel_jam_lembur > 0 && empty($row->rapel_payout_period)) {
                    $firstOt = $this->db->table('overtime_logs')
                                        ->where('employee_id', $row->employee_id)
                                        ->where('status', 'Approved')
                                        ->where('is_rapel', 1)
                                        ->groupStart()
                                            ->where('payout_period', $payoutPeriodStr)
                                            ->orGroupStart()
                                                ->where('tanggal >=', $monthStart)
                                                ->where('tanggal <=', $monthEnd)
                                            ->groupEnd()
                                        ->groupEnd()
                                        ->get()->getRow();
                    if ($firstOt) {
                        $row->rapel_payout_period = $firstOt->payout_period;
                    }
                }
            }
        }

        return $this->respond($pkwts);
    }

    public function saveAttendance()
    {
        $data = $this->request->getJSON(true);
        $periodId = $data['period_id'];
        $pkwtId = $data['pkwt_id'];

        $existing = $this->db->table('payroll_attendance')
                             ->where('period_id', $periodId)
                             ->where('pkwt_id', $pkwtId)
                             ->get()->getRow();
        
        $data['is_manual'] = 1;

        if ($existing) {
            $this->db->table('payroll_attendance')->where('id', $existing->id)->update($data);
        } else {
            $this->db->table('payroll_attendance')->insert($data);
        }
        return $this->respond(['message' => 'Data cut-off berhasil disimpan']);
    }

    public function saveAttendanceBulk()
    {
        $records = $this->request->getJSON(true);
        if (empty($records) || !is_array($records)) {
            return $this->failValidationErrors('Data absensi kosong.');
        }

        $count = 0;
        foreach ($records as $record) {
            $periodId = $record['period_id'] ?? null;
            $pkwtId = $record['pkwt_id'] ?? null;
            if (!$periodId || !$pkwtId) {
                continue;
            }

            $pkwt = $this->db->table('pkwt')->where('id', $pkwtId)->get()->getRow();
            $period = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
            $employee = null;
            if ($pkwt) {
                $employee = $this->db->table('employees')
                                     ->where('client_id', $pkwt->client_id)
                                     ->where('nama', $pkwt->employee_name)
                                     ->get()->getRow();
            }

            $hariKerja = $record['hari_kerja'] ?? 0;
            $jamLembur = $record['jam_lembur'] ?? 0;
            $potonganAbsensi = $record['potongan_absensi'] ?? 0;

            if ($employee && $period) {
                $clientId = $pkwt->client_id;
                $clientConfig = $this->resolveClientConfig($clientId, $pkwt->position_name);

                $prevMonth = intval($period->bulan) - 1;
                $prevYear = intval($period->tahun);
                if ($prevMonth == 0) {
                    $prevMonth = 12;
                    $prevYear--;
                }
                $startDateStr = sprintf('%04d-%02d-01', $prevYear, $prevMonth);
                $endDateStr = date('Y-m-t', strtotime($startDateStr));


                $effectiveStartDateStr = !empty($employee->tgl_masuk) ? max($startDateStr, date('Y-m-d', strtotime($employee->tgl_masuk))) : $startDateStr;

                // 1. Query total hari_kerja (Hadir)
                $hadirCount = $this->db->table('attendance_logs')
                                       ->where('employee_id', $employee->id)
                                       ->where('log_date >=', $effectiveStartDateStr)
                                       ->where('log_date <=', $endDateStr)
                                       ->where('status', 'Hadir')
                                       ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                                       ->countAllResults();
                
                // 2. Query total jam_lembur (ONLY APPROVED) dari overtime_logs — SPLIT by is_holiday
                // 2a. Lembur Hari Biasa (is_holiday = 0)
                $lemburBiasaObj = $this->db->table('overtime_logs')
                                         ->selectSum('jam_lembur')
                                         ->where('employee_id', $employee->id)
                                         ->where('tanggal >=', $effectiveStartDateStr)
                                         ->where('tanggal <=', $endDateStr)
                                         ->where('status', 'Approved')
                                         ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                                         ->where('(is_holiday = 0 OR is_holiday IS NULL)')
                                         ->get()->getRow();
                $lemburBiasa = $lemburBiasaObj ? floatval($lemburBiasaObj->jam_lembur) : 0.0;

                // 2b. Lembur Hari Libur (is_holiday = 1)
                $lemburLiburObj = $this->db->table('overtime_logs')
                                         ->selectSum('jam_lembur')
                                         ->where('employee_id', $employee->id)
                                         ->where('tanggal >=', $effectiveStartDateStr)
                                         ->where('tanggal <=', $endDateStr)
                                         ->where('status', 'Approved')
                                         ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                                         ->where('is_holiday', 1)
                                         ->get()->getRow();
                $lemburLibur = $lemburLiburObj ? floatval($lemburLiburObj->jam_lembur) : 0.0;

                $lemburSum = $lemburBiasa + $lemburLibur;

                // 3. Loop daily logs to determine alpaCount automatically
                $currTime = strtotime($startDateStr);
                $endTime = strtotime($endDateStr);
                $alpaCount = 0;
                
                // Fetch all attendance logs for this employee in the period to optimize
                $attLogs = $this->db->table('attendance_logs')
                                    ->where('employee_id', $employee->id)
                                    ->where('log_date >=', $startDateStr)
                                    ->where('log_date <=', $endDateStr)
                                    ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                                    ->get()->getResultArray();
                                    
                $logsByDate = [];
                foreach ($attLogs as $log) {
                    $logsByDate[$log['log_date']] = $log;
                }

                // Determine workDaysConfig
                $workDaysConfig = 5; // default
                if ($employee) {
                    $workDaysConfig = intval($employee->hari_kerja ?? 5);
                    if ($workDaysConfig < 1) {
                        $posId = $employee->position_id ?? null;
                        if ($posId) {
                            $pos = $this->db->table('positions')->where('id', $posId)->get()->getRow();
                            if ($pos && isset($pos->hari_kerja) && intval($pos->hari_kerja) > 0) {
                                $workDaysConfig = intval($pos->hari_kerja);
                            }
                        }
                    }
                }

                // Fetch holidays in range for absence counting
                $holidaysList = [];
                $holidayRowsForAlpa = $this->db->table('holiday_calendar')
                                  ->where('tanggal >=', $startDateStr)
                                  ->where('tanggal <=', $endDateStr)
                                  ->get()->getResultArray();
                foreach ($holidayRowsForAlpa as $h) {
                    $holidaysList[$h['tanggal']] = true;
                }

                while ($currTime <= $endTime) {
                    $currDateStr = date('Y-m-d', $currTime);
                    $dayOfWeek = intval(date('w', $currTime)); // 0 = Sunday, 6 = Saturday
                    
                    // Skip dates before employee's join date
                    $joinDate = !empty($employee->tgl_masuk) ? $employee->tgl_masuk : ($employee->start_contract ?? null);
                    if (!empty($joinDate) && strtotime($currDateStr) < strtotime($joinDate)) {
                        $currTime = strtotime('+1 day', $currTime);
                        continue;
                    }

                    // Determine if it is a working day (exclude holidays)
                    $isWorkingDay = false;
                    if (!isset($holidaysList[$currDateStr])) {
                        if ($workDaysConfig === 5) {
                            $isWorkingDay = ($dayOfWeek !== 0 && $dayOfWeek !== 6);
                        } elseif ($workDaysConfig === 6) {
                            $isWorkingDay = ($dayOfWeek !== 0);
                        } else {
                            $isWorkingDay = true;
                        }
                    }
                    
                    if ($isWorkingDay) {
                        if (!isset($logsByDate[$currDateStr])) {
                            $alpaCount++;
                        } else {
                            $logStatus = $logsByDate[$currDateStr]['status'] ?? 'Hadir';
                            $logStatusNorm = strtolower(trim($logStatus));
                            if ($logStatusNorm === 'absen' || $logStatusNorm === 'alpa' || $logStatusNorm === 'absent' || $logStatusNorm === 'missing') {
                                $alpaCount++;
                            }
                        }
                    }
                    
                    $currTime = strtotime('+1 day', $currTime);
                }

                // 4. Hitung potongan_absensi berdasarkan gaji pokok & hari_kerja config
                $workDaysConfig = isset($employee->hari_kerja) ? intval($employee->hari_kerja) : 5;
                $gajiPokok = floatval($employee->gaji_pokok);
                
                // Check if employee has an active contract that sets a different basic salary
                $activeContract = $this->db->table('contracts')
                                            ->where('employee_id', $employee->id)
                                            ->where('status_pkwt', 'Aktif')
                                            ->orderBy('tgl_mulai', 'DESC')
                                            ->get()->getRow();
                if ($activeContract && floatval($activeContract->gaji_pokok) > 0) {
                    $gajiPokok = floatval($activeContract->gaji_pokok);
                }

                if ($gajiPokok <= 0 && $pkwtId) {
                    $basicComp = $this->db->table('pkwt_components')
                                          ->where('pkwt_id', $pkwtId)
                                          ->groupStart()
                                              ->where('jenis_komponen', 'basic_salary')
                                              ->orLike('nama', 'Gaji Pokok')
                                          ->groupEnd()
                                          ->get()->getRow();
                    if ($basicComp) {
                        $base_nilai = floatval($basicComp->nilai);
                        $sumber_nilai = $basicComp->sumber_nilai ?? 'nominal';
                        
                        if ($sumber_nilai === 'ump' || $sumber_nilai === 'umk' || $sumber_nilai === 'ump_umk') {
                            $mwId = $employee->minimum_wage_id ?? null;
                            $minimumWage = 0;
                            if ($employee && isset($employee->minimum_wage_id)) {
                                $mw = $this->db->table('minimum_wages')->where('id', $employee->minimum_wage_id)->get()->getRow();
                                if ($mw) {
                                    $minimumWage = floatval($mw->nominal);
                                    $mwId = $mw->id;
                                }
                            } else if ($clientConfig && isset($clientConfig->minimum_wage_id)) {
                                $mw = $this->db->table('minimum_wages')->where('id', $clientConfig->minimum_wage_id)->get()->getRow();
                                if ($mw) {
                                    $minimumWage = floatval($mw->nominal);
                                    $mwId = $mw->id;
                                }
                            }

                            $empProvince = null;
                            if ($employee && !empty($employee->work_location_id)) {
                                $wl = $this->db->table('work_locations')->where('id', $employee->work_location_id)->get()->getRow();
                                if ($wl && !empty($wl->provinsi)) {
                                    $empProvince = $wl->provinsi;
                                }
                            }

                            $resolvedWages = $this->resolveUmpUmk($mwId, null, $empProvince);
                            $umpWageValue = $resolvedWages['ump'];
                            $umkWageValue = $resolvedWages['umk'];

                            if ($sumber_nilai === 'ump') {
                                $base_nilai = $umpWageValue * ($base_nilai / 100);
                            } else if ($sumber_nilai === 'umk') {
                                $base_nilai = $umkWageValue * ($base_nilai / 100);
                            } else if ($sumber_nilai === 'ump_umk') {
                                $base_nilai = $minimumWage * ($base_nilai / 100);
                            }
                        }
                        
                        $gajiPokok = $base_nilai;
                    }
                }

                $divider = $this->getStandardWorkingDaysInRange($startDateStr, $endDateStr, $workDaysConfig);
                if ($divider <= 0) {
                    $divider = ($workDaysConfig === 5) ? 22 : (($workDaysConfig === 6) ? 26 : 30);
                }
                $dendaAbsenPerDay = $gajiPokok / $divider;
                $calculatedPotongan = $alpaCount * $dendaAbsenPerDay;

                // Override values with DB calculated ones if we found daily logs
                $hasAnyLogs = $this->db->table('attendance_logs')
                                       ->where('employee_id', $employee->id)
                                       ->where('log_date >=', $effectiveStartDateStr)
                                       ->where('log_date <=', $endDateStr)
                                       ->countAllResults();
                
                $earlyArrivalMinutes = 0;
                if ($hasAnyLogs > 0) {
                    $hariKerja = $hadirCount;
                    $jamLembur = $lemburSum;
                    $potonganAbsensi = $calculatedPotongan;
                    
                    // Hitung total APPROVED early arrival minutes dari tabel logs
                    $eaSumObj = $this->db->table('early_arrival')
                                         ->selectSum('eligible_minutes')
                                         ->where('employee_id', $employee->id)
                                         ->where('date >=', $effectiveStartDateStr)
                                         ->where('date <=', $endDateStr)
                                         ->where('status', 'APPROVED')
                                         ->get()->getRow();
                    $earlyArrivalMinutes = $eaSumObj ? intval($eaSumObj->eligible_minutes) : 0;
                } else {
                    $earlyArrivalMinutes = intval($record['early_arrival_minutes'] ?? 0);
                }

                $isJoinedPrevMonth = false;
                $isActiveRegularPrevMonth = false;
                if ($employee && !empty($employee->tgl_masuk)) {
                    $joinTs = strtotime($employee->tgl_masuk);
                    $joinYear = intval(date('Y', $joinTs));
                    $joinMonth = intval(date('n', $joinTs));
                    
                    $prevMonth = intval($period->bulan) - 1;
                    $prevYear = intval($period->tahun);
                    if ($prevMonth == 0) {
                        $prevMonth = 12;
                        $prevYear--;
                    }
                    if ($joinYear === $prevYear && $joinMonth === $prevMonth) {
                        $cutoffStartEnd = $this->resolveCutoffStartEnd($clientConfig);
                        $joinDateStr = date('Y-m-d', $joinTs);
                        $prevCutoffEndDate = sprintf('%04d-%02d-%02d', $prevYear, $prevMonth, $cutoffStartEnd['end']);
                        if ($joinDateStr > $prevCutoffEndDate) {
                            $isJoinedPrevMonth = true;
                        } else {
                            // Joined in previous month before cutoff. Check if active in current month (dates 1 to cutoffEnd)
                            $currentMonthStartStr = sprintf('%04d-%02d-01', $period->tahun, $period->bulan);
                            $currentCutoffEndStr = sprintf('%04d-%02d-%02d', $period->tahun, $period->bulan, $cutoffStartEnd['end']);
                            $currentLogsCount = $this->db->table('attendance_logs')
                                                         ->where('employee_id', $employee->id)
                                                         ->where('log_date >=', $currentMonthStartStr)
                                                         ->where('log_date <=', $currentCutoffEndStr)
                                                         ->countAllResults();
                            
                            $prevCutoffStartStr = date('Y-m-d', strtotime('+1 day', strtotime($prevCutoffEndDate)));
                            $prevMonthLogsCount = $this->db->table('attendance_logs')
                                                         ->where('employee_id', $employee->id)
                                                         ->where('log_date >=', $prevCutoffStartStr)
                                                         ->where('log_date <', $currentMonthStartStr)
                                                         ->countAllResults();
                            
                            if ($currentLogsCount > 0 && $prevMonthLogsCount === 0) {
                                $isActiveRegularPrevMonth = true;
                            }
                        }
                    }
                }

                if ($isJoinedPrevMonth || $isActiveRegularPrevMonth) {
                    if ($hasAnyLogs === 0) {
                        $hariKerja = $this->getStandardWorkingDaysInRange($startDateStr, $endDateStr, $workDaysConfig);
                    }
                    $potonganAbsensi = 0.0;
                }

                // Set breakdown values (only available when we have daily logs)
                $jamLemburHariBiasa = $lemburBiasa ?? 0;
                $jamLemburHariLibur = $lemburLibur ?? 0;
            }

            $existing = $this->db->table('payroll_attendance')
                                 ->where('period_id', $periodId)
                                 ->where('pkwt_id', $pkwtId)
                                 ->get()->getRow();

            $saveData = [
                'period_id' => $periodId,
                'pkwt_id' => $pkwtId,
                'hari_kerja' => $hariKerja,
                'jam_lembur' => $jamLembur,
                'jam_lembur_hari_biasa' => $jamLemburHariBiasa ?? 0,
                'jam_lembur_hari_libur' => $jamLemburHariLibur ?? 0,
                'early_arrival_minutes' => $earlyArrivalMinutes,
                'potongan_absensi' => $potonganAbsensi,
                'bonus_tambahan' => $record['bonus_tambahan'] ?? 0,
                'is_manual' => 1
            ];

            if ($existing) {
                $this->db->table('payroll_attendance')->where('id', $existing->id)->update($saveData);
            } else {
                $this->db->table('payroll_attendance')->insert($saveData);
            }
            $count++;
        }

        return $this->respond(['message' => "Berhasil menyimpan {$count} data cut-off"]);
    }

    // --- GENERATE PAYROLL ---
    public function generatePayroll($periodId)
    {
        $clientId = ($this->request !== null) ? $this->request->getGet('client_id') : ($_GET['client_id'] ?? null);
        $this->syncEmployeesToPKWT($clientId);
        $this->syncOvertimeToPayrollAttendance($periodId, $clientId);
        $period = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        if (!$period) {
            return $this->failNotFound('Periode tidak ditemukan');
        }

        // Reset early arrival records for this period back to NOT_PROCESSED
        $payoutPeriodStr = ($period->bulan < 10 ? '0' : '') . $period->bulan . '/' . $period->tahun;
        $this->db->table('early_arrival')
                 ->where('payroll_period', $payoutPeriodStr)
                 ->update([
                     'payroll_status' => 'NOT_PROCESSED',
                     'payroll_period' => null,
                     'updated_at' => date('Y-m-d H:i:s')
                 ]);

        $this->syncEarlyArrivalToPayrollAttendance($periodId, $clientId);
        $daysInMonth = date('t', mktime(0, 0, 0, intval($period->bulan), 1, intval($period->tahun)));

        // 1. Get all PKWTs
        $query = $this->db->table('pkwt');
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        $pkwts = $query->get()->getResult();

        foreach ($pkwts as $pkwt) {
            // 4. Resolve Employee / Client UMR (Minimum Wage)
            $emp = $this->db->table('employees')
                            ->select('employees.*, minimum_wages.nominal as umr_nominal, minimum_wages.id as mw_id, positions.hari_kerja as position_hari_kerja')
                            ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                            ->join('positions', 'positions.id = employees.position_id', 'left')
                            ->where('employees.nama', $pkwt->employee_name)
                            ->where('employees.client_id', $pkwt->client_id)
                            ->get()
                            ->getRow();

            if (!$emp) {
                continue;
            }

            // Get client config to find payroll scheme ID
            $clientConfig = $this->resolveClientConfig($pkwt->client_id, $pkwt->position_name);

            // Verify if join date (tgl_masuk) is after the cutoff date
            $cutoffStart = $clientConfig ? intval($clientConfig->cutoff_start) : 21;
            $cutoffEnd = $clientConfig ? intval($clientConfig->cutoff_end) : 20;

            if ($clientConfig) {
                $startField = "cutoff_gaji_pokok_start";
                $endField = "cutoff_gaji_pokok_end";
                $refField = "cutoff_gaji_pokok_schedule_ref";
                
                $start = isset($clientConfig->$startField) ? intval($clientConfig->$startField) : null;
                $end = isset($clientConfig->$endField) ? intval($clientConfig->$endField) : null;
                $refId = isset($clientConfig->$refField) ? intval($clientConfig->$refField) : null;
                
                if ($refId) {
                    $sched = $this->db->table('payroll_schedules')->where('id', $refId)->get()->getRow();
                    if ($sched) {
                        $start = intval($sched->cutoff_start);
                        $end = intval($sched->cutoff_end);
                    }
                }
                
                if ($start === null) {
                    if (isset($clientConfig->cutoff_start)) {
                        $start = intval($clientConfig->cutoff_start);
                    } else {
                        $start = 21;
                    }
                }
                if ($end === null) {
                    if (isset($clientConfig->cutoff_end)) {
                        $end = intval($clientConfig->cutoff_end);
                    } else {
                        $end = $start - 1;
                        if ($end < 1) $end = $daysInMonth;
                    }
                }
                
                $cutoffStart = $start;
                $cutoffEnd = $end;
            }

            if ($cutoffStart <= 0) $cutoffStart = 1;
            if ($cutoffEnd <= 0) {
                $cutoffEnd = $cutoffStart - 1;
                if ($cutoffEnd < 1) $cutoffEnd = $daysInMonth;
            }

            // Check if employee is on hold due to absence in days after cutoff
            $holdPayroll = false;
            $tYear = intval($period->tahun);
            $tMonth = intval($period->bulan);
            $prevMonth = $tMonth - 1;
            $prevYear = $tYear;
            if ($prevMonth == 0) {
                $prevMonth = 12;
                $prevYear--;
            }
            if ($cutoffStart > 1) {
                $remainingStartDate = sprintf('%04d-%02d-%02d', $prevYear, $prevMonth, $cutoffStart);
                $remainingEndDate = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $prevYear, $prevMonth)));
                
                $absentCountRemaining = $this->db->table('attendance_logs')
                    ->where('employee_id', $emp->id)
                    ->where('log_date >=', $remainingStartDate)
                    ->where('log_date <=', $remainingEndDate)
                    ->where('status', 'Absen')
                    ->countAllResults();
                if ($absentCountRemaining > 0) {
                    $holdPayroll = true;
                }
            }

            // Calculate cutoff-based period dates (not calendar month)
            // For cutoff_start > cutoff_end (cross-month cutoff, e.g. 5th to 4th):
            //   period bulan=7 means: June 5 → July 4
            // For cutoff_start <= cutoff_end (same-month cutoff, e.g. 1st to 31st):
            //   period bulan=7 means: July 1 → July 31
            $bulan_start_period = intval($period->bulan);
            $tahun_start_period = intval($period->tahun);
            $bulan_end_period = $bulan_start_period;
            $tahun_end_period = $tahun_start_period;
            if ($cutoffStart > $cutoffEnd && $cutoffStart > 1) {
                $bulan_start_period -= 1;
                if ($bulan_start_period < 1) {
                    $bulan_start_period = 12;
                    $tahun_start_period -= 1;
                }
            }

            $daysInStartMonth = date('t', mktime(0, 0, 0, $bulan_start_period, 1, $tahun_start_period));
            $daysInEndMonth = date('t', mktime(0, 0, 0, $bulan_end_period, 1, $tahun_end_period));

            $actualCutoffStart = min($cutoffStart, $daysInStartMonth);
            $actualCutoffEnd = min($cutoffEnd, $daysInEndMonth);

            $startDateStr = sprintf('%04d-%02d-%02d', $tahun_start_period, $bulan_start_period, $actualCutoffStart);
            $endDateStr = sprintf('%04d-%02d-%02d', $tahun_end_period, $bulan_end_period, $actualCutoffEnd);

            // Reset old rapel flags for this employee in the current period range
            if ($emp) {
                $this->db->table('attendance_logs')
                    ->where('employee_id', $emp->id)
                    ->where('log_date >=', $startDateStr)
                    ->where('log_date <=', $endDateStr)
                    ->update([
                        'is_rapel' => 0,
                        'payout_period' => null
                    ]);

                $this->db->table('overtime_logs')
                    ->where('employee_id', $emp->id)
                    ->where('tanggal >=', $startDateStr)
                    ->where('tanggal <=', $endDateStr)
                    ->update([
                        'is_rapel' => 0,
                        'payout_period' => null
                    ]);
            }

            $isNewHireRapel = false;
            $nextPeriodStr = '';
            if ($emp && !empty($emp->tgl_masuk)) {
                $joinTs = strtotime($emp->tgl_masuk);
                $joinDateStr = date('Y-m-d', $joinTs);
                
                $currentPeriodCutoffEnd = sprintf('%04d-%02d-%02d', $tYear, $tMonth, $cutoffEnd);
                
                if ($joinDateStr > $currentPeriodCutoffEnd) {
                    $isRapelGP = ($clientConfig && isset($clientConfig->is_rapel_gaji_pokok)) ? intval($clientConfig->is_rapel_gaji_pokok) : 1;
                    $joinYear = intval(date('Y', $joinTs));
                    $joinMonth = intval(date('n', $joinTs));
                    $joinDay = intval(date('j', $joinTs));
                    
                    if ($joinYear === $tYear && $joinMonth === $tMonth && $joinDay >= $cutoffStart && $isRapelGP === 1) {
                        $isNewHireRapel = true;
                        
                        $nextMonth = $tMonth + 1;
                        $nextYear = $tYear;
                        if ($nextMonth > 12) {
                            $nextMonth = 1;
                            $nextYear++;
                        }
                        $nextPeriodStr = $nextMonth . '-' . $nextYear;
                    } else {
                        // Skip employee because join date is after cutoff date and not in current calendar month
                        $this->db->table('payroll_final')
                             ->where('period_id', $periodId)
                             ->where('pkwt_id', $pkwt->id)
                             ->delete();
                        continue;
                    }
                }
            }
            
            $isProrate = false;
            $isAbsenTidakPotong = false;
            $nominalPotonganAbsen = 0;
            $payrollScheme = null;
            
            // Try resolving from payroll scheme first
            if ($clientConfig && $clientConfig->payroll_scheme_id) {
                $payrollScheme = $this->db->table('payroll_schemes')->where('id', $clientConfig->payroll_scheme_id)->get()->getRow();
                if ($payrollScheme) {
                    $isProrate = ($payrollScheme->prorate == 1);
                    $isAbsenTidakPotong = ($payrollScheme->absen_tidak_potong == 1);
                    $nominalPotonganAbsen = floatval($payrollScheme->nominal_potongan);
                }
            }
            
            // Fallback to client_absence_configs
            $absenceConfig = $this->db->table('client_absence_configs')->where('client_id', $pkwt->client_id)->get()->getRow();
            if ($clientConfig && !$isProrate && !$isAbsenTidakPotong && $nominalPotonganAbsen == 0) {
                if ($absenceConfig) {
                    $isProrate = ($absenceConfig->prorate == 1);
                    $isAbsenTidakPotong = ($absenceConfig->absen_tidak_potong == 1);
                    $nominalPotonganAbsen = floatval($absenceConfig->nominal_potongan);
                }
            }

            // Get Client's overtime_rate_per_hour (manual flat rate, or 0 = use formula)
            $client = $this->db->table('clients')->where('id', $pkwt->client_id)->get()->getRow();
            $clientManualOtRate = ($client && isset($client->overtime_rate_per_hour)) ? floatval($client->overtime_rate_per_hour) : 0;
            // Also check scheme template rate
            // Check if employee joined in the previous calendar month
            $isJoinedPrevMonth = false;
            $prevMonth = 0;
            $prevYear = 0;
            $joinTs = 0;
            if ($emp && !empty($emp->tgl_masuk)) {
                $joinTs = strtotime($emp->tgl_masuk);
                $joinYear = intval(date('Y', $joinTs));
                $joinMonth = intval(date('n', $joinTs));
                
                $prevMonth = intval($period->bulan) - 1;
                $prevYear = intval($period->tahun);
                if ($prevMonth == 0) {
                    $prevMonth = 12;
                    $prevYear--;
                }
                
                if ($joinYear === $prevYear && $joinMonth === $prevMonth) {
                    $joinDateStr = date('Y-m-d', $joinTs);
                    $prevCutoffEndDate = sprintf('%04d-%02d-%02d', $prevYear, $prevMonth, $cutoffEnd);
                    if ($joinDateStr > $prevCutoffEndDate) {
                        $isJoinedPrevMonth = true;
                    }
                }
            }

            $isActiveRegularPrevMonth = false;
            if ($emp && !empty($emp->tgl_masuk) && !$isJoinedPrevMonth) {
                $joinTs = strtotime($emp->tgl_masuk);
                $joinYear = intval(date('Y', $joinTs));
                $joinMonth = intval(date('n', $joinTs));
                
                $prevMonth = intval($period->bulan) - 1;
                $prevYear = intval($period->tahun);
                if ($prevMonth == 0) {
                    $prevMonth = 12;
                    $prevYear--;
                }
                
                if ($joinYear === $prevYear && $joinMonth === $prevMonth) {
                    $cutoffStartEnd = $this->resolveCutoffStartEnd($clientConfig);
                    $cutoffEndVal = $cutoffStartEnd['end'];
                    
                    $currentMonthStartStr = sprintf('%04d-%02d-01', intval($period->tahun), intval($period->bulan));
                    $currentCutoffEndStr = sprintf('%04d-%02d-%02d', intval($period->tahun), intval($period->bulan), $cutoffEndVal);
                    
                    $currentLogsCount = $this->db->table('attendance_logs')
                                                 ->where('employee_id', $emp->id)
                                                 ->where('log_date >=', $currentMonthStartStr)
                                                 ->where('log_date <=', $currentCutoffEndStr)
                                                 ->countAllResults();
                    
                    $prevCutoffEndDate = sprintf('%04d-%02d-%02d', $prevYear, $prevMonth, $cutoffEndVal);
                    $prevCutoffStartStr = date('Y-m-d', strtotime('+1 day', strtotime($prevCutoffEndDate)));
                    $prevMonthLogsCount = $this->db->table('attendance_logs')
                                                 ->where('employee_id', $emp->id)
                                                 ->where('log_date >=', $prevCutoffStartStr)
                                                 ->where('log_date <', $currentMonthStartStr)
                                                 ->countAllResults();
                    
                    if ($currentLogsCount > 0 && $prevMonthLogsCount === 0) {
                        $isActiveRegularPrevMonth = true;
                    }
                }
            }

            // 2. Get Fixed Components from PKWT
            $rawComponents = $this->db->table('pkwt_components')->where('pkwt_id', $pkwt->id)->get()->getResult();
            $components = [];
            foreach ($rawComponents as $comp) {
                // Check if Ad-hoc and verify period matching current month/year
                $isAdhoc = isset($comp->allowance_type) && $comp->allowance_type === 'Ad-hoc';
                if ($isAdhoc) {
                    $payoutPeriod = trim($comp->payout_period ?? '');
                    $currentPeriod1 = intval($period->bulan) . '-' . intval($period->tahun);
                    $currentPeriod2 = sprintf('%02d-%d', intval($period->bulan), intval($period->tahun));
                    if ($payoutPeriod !== $currentPeriod1 && $payoutPeriod !== $currentPeriod2) {
                        continue; // Skip this component
                    }
                }
                $components[] = $comp;
            }
            
            // 3. Get Attendance Data
            $att = $this->db->table('payroll_attendance')
                            ->where('period_id', $periodId)
                            ->where('pkwt_id', $pkwt->id)
                            ->get()->getRow();

            $workDaysConfig = 5;
            if ($emp) {
                if (isset($emp->hari_kerja) && intval($emp->hari_kerja) > 0) {
                    $workDaysConfig = intval($emp->hari_kerja);
                } elseif (isset($emp->position_hari_kerja) && intval($emp->position_hari_kerja) > 0) {
                    $workDaysConfig = intval($emp->position_hari_kerja);
                }
            }
            $systemStandardDays = ($clientConfig && isset($clientConfig->standard_work_days) && intval($clientConfig->standard_work_days) > 0)
                ? intval($clientConfig->standard_work_days)
                : $this->getStandardWorkingDaysInRange($startDateStr, $endDateStr, $workDaysConfig);
            
            $stdWorkingDays = ($emp && isset($emp->custom_standard_days) && intval($emp->custom_standard_days) > 0)
                ? intval($emp->custom_standard_days)
                : $systemStandardDays;

            // Calculate expected working days since join date
            $expectedWorkingDays = $stdWorkingDays;
            $effectiveStartDateStr = $startDateStr;
            if ($emp && !empty($emp->tgl_masuk)) {
                $joinDateStr = date('Y-m-d', strtotime($emp->tgl_masuk));
                if ($joinDateStr >= $startDateStr && $joinDateStr <= $endDateStr) {
                    $effectiveStartDateStr = $joinDateStr;
                    // Calculate expected working days from join date to end date
                    $expectedWorkingDays = $this->getStandardWorkingDaysInRange($joinDateStr, $endDateStr, $workDaysConfig);
                }
            }
            
            $prevMonthStart = $effectiveStartDateStr;
            $prevMonthEnd = $endDateStr;
            
            $minimumWage = 0;
            $mwId = null;
            if ($emp && isset($emp->umr_nominal) && $emp->umr_nominal > 0) {
                $minimumWage = floatval($emp->umr_nominal);
                $mwId = $emp->mw_id;
            } else {
                // Fallback to client config's minimum wage
                if ($clientConfig && isset($clientConfig->minimum_wage_id)) {
                    $mw = $this->db->table('minimum_wages')->where('id', $clientConfig->minimum_wage_id)->get()->getRow();
                    if ($mw) {
                        $minimumWage = floatval($mw->nominal);
                        $mwId = $mw->id;
                    }
                }
            }

            // Resolve UMP and UMK values - pass employee's work location province for UMP lookup
            $empProvince = null;
            if ($emp && !empty($emp->work_location_id)) {
                $wl = $this->db->table('work_locations')->where('id', $emp->work_location_id)->get()->getRow();
                if ($wl && !empty($wl->provinsi)) {
                    $empProvince = $wl->provinsi;
                }
            }
            $resolvedWages = $this->resolveUmpUmk($mwId, null, $empProvince);
            $umpWageValue = $resolvedWages['ump'];
            $umkWageValue = $resolvedWages['umk'];

            // Check if we have a basic salary component with source = kompensasi
            $hasKompensasiSource = false;
            foreach ($components as $comp) {
                $isBasicSalary = (isset($comp->jenis_komponen) && $comp->jenis_komponen === 'basic_salary') || (stripos($comp->nama, 'Gaji Pokok') !== false);
                if ($isBasicSalary && isset($comp->sumber_nilai) && $comp->sumber_nilai === 'kompensasi') {
                    $hasKompensasiSource = true;
                    break;
                }
            }

            // 5. First pass: Find and calculate Gaji Pokok (Basic Salary)
            $gajiPokok = 0;
            $unproratedGajiPokok = 0;
            foreach ($components as $comp) {
                $isBasicSalary = false;
                if (isset($comp->jenis_komponen) && $comp->jenis_komponen === 'basic_salary') {
                    $isBasicSalary = true;
                } elseif (stripos($comp->nama, 'Gaji Pokok') !== false) {
                    $isBasicSalary = true;
                }

                if ($isBasicSalary) {
                    $base_nilai = floatval($comp->nilai);
                    $sumber_nilai = $comp->sumber_nilai ?? 'nominal';
                    
                    if ($sumber_nilai === 'ump') {
                        $base_nilai = $umpWageValue * ($base_nilai / 100);
                    } else if ($sumber_nilai === 'umk') {
                        $base_nilai = $umkWageValue * ($base_nilai / 100);
                    } else if ($sumber_nilai === 'ump_umk') {
                        $base_nilai = $minimumWage * ($base_nilai / 100);
                    } else if ($sumber_nilai === 'kompensasi') {
                        $kompTetapValue = 0;
                        foreach ($components as $c) {
                            if (($c->jenis_komponen ?? '') === 'kompensasi' && ($c->sifat_kompensasi ?? '') === 'tetap') {
                                $kompTetapValue += floatval($c->nilai);
                            }
                        }
                        $base_nilai = $kompTetapValue * ($base_nilai / 100);
                    } else {
                        // Force base_nilai to use Employee's setup if available!
                        if ($emp && isset($emp->gaji_pokok) && floatval($emp->gaji_pokok) > 0) {
                            $base_nilai = floatval($emp->gaji_pokok);
                        } else if ($minimumWage > 0) {
                            $base_nilai = $minimumWage;
                        }
                    }
                    
                    $unproratedGajiPokok = $base_nilai;
                    
                    // Scale by period
                    if (isset($comp->periode)) {
                        if ($comp->periode === 'hari' || $comp->periode === 'hari_kerja') {
                            $days = ($att && isset($att->hari_kerja) && $att->hari_kerja !== null) ? intval($att->hari_kerja) : $stdWorkingDays;
                            $base_nilai = $base_nilai * $days;
                        } elseif ($comp->periode === 'minggu') {
                            $base_nilai = $base_nilai * 4;
                        } elseif ($comp->periode === 'tahun') {
                            $base_nilai = $base_nilai / 12;
                        }
                    }
                    
                    $gajiPokok = $base_nilai;
                    break; // Assume only one basic salary component
                }
            }

            if ($gajiPokok <= 0 && $emp && isset($emp->gaji_pokok)) {
                $unproratedGajiPokok = floatval($emp->gaji_pokok);
                $gajiPokok = $unproratedGajiPokok;
            }

            if ($isJoinedPrevMonth) {
                // Calculate Rapel Gaji for the previous month using resolved unprorated basic salary
                $prevStartDateStr = sprintf('%04d-%02d-01', $prevYear, $prevMonth);
                $prevEndDateStr = date('Y-m-t', strtotime($prevStartDateStr));
                
                $workDaysConfig = 5;
                if (isset($emp->hari_kerja) && intval($emp->hari_kerja) > 0) {
                    $workDaysConfig = intval($emp->hari_kerja);
                } elseif (isset($emp->position_hari_kerja) && intval($emp->position_hari_kerja) > 0) {
                    $workDaysConfig = intval($emp->position_hari_kerja);
                }
                
                $prevStdWorkingDays = $this->getStandardWorkingDaysInRange($prevStartDateStr, $prevEndDateStr, $workDaysConfig);
                
                $joinDateStr = date('Y-m-d', $joinTs);
                $hasAnyLogsPrev = $this->db->table('attendance_logs')
                                           ->where('employee_id', $emp->id)
                                           ->where('log_date >=', $joinDateStr)
                                           ->where('log_date <=', $prevEndDateStr)
                                           ->countAllResults() > 0;
                
                if ($hasAnyLogsPrev) {
                    $actualDaysWorkedPrev = $this->db->table('attendance_logs')
                                                 ->where('employee_id', $emp->id)
                                                 ->where('log_date >=', $joinDateStr)
                                                 ->where('log_date <=', $prevEndDateStr)
                                                 ->where('status', 'Hadir')
                                                 ->countAllResults();
                } else {
                    $actualDaysWorkedPrev = $this->getStandardWorkingDaysInRange($joinDateStr, $prevEndDateStr, $workDaysConfig);
                }
                
                $prevMonthComponents = [];
                foreach ($rawComponents as $comp) {
                    $isAdhoc = isset($comp->allowance_type) && $comp->allowance_type === 'Ad-hoc';
                    if ($isAdhoc) {
                        $payoutPeriod = trim($comp->payout_period ?? '');
                        $prevPeriod1 = intval($prevMonth) . '-' . intval($prevYear);
                        $prevPeriod2 = sprintf('%02d-%d', intval($prevMonth), intval($prevYear));
                        if ($payoutPeriod !== $prevPeriod1 && $payoutPeriod !== $prevPeriod2) {
                            continue; // Skip this component
                        }
                    }
                    $prevMonthComponents[] = $comp;
                }

                $prevProrateFactor = ($prevStdWorkingDays > 0) ? ($actualDaysWorkedPrev / $prevStdWorkingDays) : 0.0;
                $totalPrevEarnings = 0.0;
                $totalPrevDeductions = 0.0;

                foreach ($prevMonthComponents as $comp) {
                    $isBasic = (isset($comp->jenis_komponen) && $comp->jenis_komponen === 'basic_salary') || (stripos($comp->nama, 'Gaji Pokok') !== false);
                    
                    if ($isBasic) {
                        $nilai = $unproratedGajiPokok * $prevProrateFactor;
                    } else {
                        $base_nilai = floatval($comp->nilai);
                        if (isset($comp->sumber_nilai)) {
                            if ($comp->sumber_nilai === 'ump') {
                                $base_nilai = $umpWageValue * ($base_nilai / 100);
                            } else if ($comp->sumber_nilai === 'umk') {
                                $base_nilai = $umkWageValue * ($base_nilai / 100);
                            } else if ($comp->sumber_nilai === 'ump_umk') {
                                $base_nilai = $minimumWage * ($base_nilai / 100);
                            }
                        }
                        
                        // Determine period and scale
                        $periode = $comp->periode ?? 'bulan';
                        if ($periode === 'hari' || $periode === 'hari_kerja') {
                            $nilai = $base_nilai * $actualDaysWorkedPrev;
                        } elseif ($periode === 'minggu') {
                            $nilai = $base_nilai * 4 * $prevProrateFactor;
                        } elseif ($periode === 'tahun') {
                            $nilai = ($base_nilai / 12) * $prevProrateFactor;
                        } else {
                            // monthly/bulan
                            $nilai = $base_nilai * $prevProrateFactor;
                        }
                    }

                    if (($comp->tipe ?? 'pendapatan') === 'pendapatan') {
                        $totalPrevEarnings += $nilai;
                    } else {
                        $totalPrevDeductions += $nilai;
                    }
                }
                $rapelAmount = $totalPrevEarnings - $totalPrevDeductions;
                if ($rapelAmount < 0) $rapelAmount = 0.0;
                
                if ($rapelAmount > 0) {
                    $payoutPeriodStr = intval($period->bulan) . '-' . intval($period->tahun);
                    $existingRapel = $this->db->table('pkwt_components')
                                              ->where('pkwt_id', $pkwt->id)
                                              ->where('allowance_type', 'Ad-hoc')
                                              ->where('payout_period', $payoutPeriodStr)
                                              ->like('nama', 'Rapel')
                                              ->get()->getRow();
                    $compData = [
                        'pkwt_id' => $pkwt->id,
                        'nama' => 'Rapel Gaji (Prorata Bulan Pertama)',
                        'tipe' => 'pendapatan',
                        'nilai' => $rapelAmount,
                        'is_persentase' => 0,
                        'jenis_komponen' => 'kompensasi',
                        'sifat_kompensasi' => 'tidak_tetap',
                        'sumber_nilai' => 'nominal',
                        'periode' => 'bulan',
                        'allowance_type' => 'Ad-hoc',
                        'payout_period' => $payoutPeriodStr
                    ];
                    if (!$existingRapel) {
                        $this->db->table('pkwt_components')->insert($compData);
                        $compData['id'] = $this->db->insertID();
                        $components[] = (object) $compData;
                    } else {
                        $this->db->table('pkwt_components')
                                 ->where('id', $existingRapel->id)
                                 ->update([
                                     'nilai' => $rapelAmount
                                 ]);
                        $foundInMemory = false;
                        foreach ($components as &$c) {
                            if (isset($c->id) && $c->id == $existingRapel->id) {
                                $c->nilai = $rapelAmount;
                                $foundInMemory = true;
                                break;
                            }
                        }
                        if (!$foundInMemory) {
                            $compData['id'] = $existingRapel->id;
                            $components[] = (object) $compData;
                        }
                    }
                }
            }

            // Prorate basic salary if the employee is a new hire joining within this period
            if (!$isNewHireRapel && !$isJoinedPrevMonth && $emp && !empty($emp->tgl_masuk)) {
                $joinTs = strtotime($emp->tgl_masuk);
                $joinDateStr = date('Y-m-d', $joinTs);
                if ($joinDateStr >= $startDateStr && $joinDateStr <= $endDateStr) {
                    $joinMonth = intval(date('n', $joinTs));
                    $joinYear = intval(date('Y', $joinTs));
                    $isSameMonth = ($joinYear === $tYear && $joinMonth === $tMonth);

                    if (!$isSameMonth && $expectedWorkingDays < $stdWorkingDays && $stdWorkingDays > 0) {
                        $gajiPokok = ($expectedWorkingDays / $stdWorkingDays) * $gajiPokok;
                    }
                }
            }

            if ($isNewHireRapel) {
                // Calculate actual present days from attendance_logs
                $actualDaysWorked = 0;
                
                $currMonthStart = sprintf('%04d-%02d-01', $tYear, $tMonth);
                $currMonthEnd = date('Y-m-t', strtotime($currMonthStart));
                $currStdWorkingDays = $this->getStandardWorkingDaysInRange($currMonthStart, $currMonthEnd, $workDaysConfig);
                
                $joinDateStr = date('Y-m-d', $joinTs);
                $hasAnyLogsPrev = $this->db->table('attendance_logs')
                                           ->where('employee_id', $emp->id)
                                           ->where('log_date >=', $joinDateStr)
                                           ->where('log_date <=', $currMonthEnd)
                                           ->countAllResults() > 0;
                
                if ($hasAnyLogsPrev) {
                    $actualDaysWorkedPrev = $this->db->table('attendance_logs')
                                                 ->where('employee_id', $emp->id)
                                                 ->where('log_date >=', $joinDateStr)
                                                 ->where('log_date <=', $currMonthEnd)
                                                 ->where('status', 'Hadir')
                                                 ->countAllResults();
                } else {
                    $actualDaysWorkedPrev = $this->getStandardWorkingDaysInRange($joinDateStr, $currMonthEnd, $workDaysConfig);
                }
                
                $rapelAmount = 0.0;
                $totalEarnings = 0.0;
                $totalDeductions = 0.0;
                $prorateFactor = ($currStdWorkingDays > 0) ? ($actualDaysWorkedPrev / $currStdWorkingDays) : 0.0;

                foreach ($components as $comp) {
                    $isBasic = (isset($comp->jenis_komponen) && $comp->jenis_komponen === 'basic_salary') || (stripos($comp->nama, 'Gaji Pokok') !== false);
                    
                    if ($isBasic) {
                        $nilai = $unproratedGajiPokok * $prorateFactor;
                        $gajiPokok = $nilai;
                    } else {
                        $base_nilai = floatval($comp->nilai);
                        if (isset($comp->sumber_nilai)) {
                            if ($comp->sumber_nilai === 'ump') {
                                $base_nilai = $umpWageValue * ($base_nilai / 100);
                            } else if ($comp->sumber_nilai === 'umk') {
                                $base_nilai = $umkWageValue * ($base_nilai / 100);
                            } else if ($comp->sumber_nilai === 'ump_umk') {
                                $base_nilai = $minimumWage * ($base_nilai / 100);
                            }
                        }
                        
                        // Determine period and scale
                        $periode = $comp->periode ?? 'bulan';
                        if ($periode === 'hari' || $periode === 'hari_kerja') {
                            $nilai = $base_nilai * $actualDaysWorkedPrev;
                        } elseif ($periode === 'minggu') {
                            $nilai = $base_nilai * 4 * $prorateFactor;
                        } elseif ($periode === 'tahun') {
                            $nilai = ($base_nilai / 12) * $prorateFactor;
                        } else {
                            // monthly/bulan
                            $nilai = $base_nilai * $prorateFactor;
                        }
                    }

                    if (($comp->tipe ?? 'pendapatan') === 'pendapatan') {
                        $totalEarnings += $nilai;
                    } else {
                        $totalDeductions += $nilai;
                    }
                }

                $rapelAmount = $totalEarnings - $totalDeductions;
                if ($rapelAmount < 0) $rapelAmount = 0.0;

                if ($rapelAmount > 0) {
                    $existingRapel = $this->db->table('pkwt_components')
                        ->where('pkwt_id', $pkwt->id)
                        ->where('allowance_type', 'Ad-hoc')
                        ->where('payout_period', $nextPeriodStr)
                        ->like('nama', 'Rapel')
                        ->get()->getRow();
                    
                    if (!$existingRapel) {
                        $this->db->table('pkwt_components')->insert([
                            'pkwt_id' => $pkwt->id,
                            'nama' => 'Rapel Gaji (Prorata Bulan Pertama)',
                            'tipe' => 'pendapatan',
                            'nilai' => $rapelAmount,
                            'is_persentase' => 0,
                            'jenis_komponen' => 'kompensasi',
                            'sifat_kompensasi' => 'tidak_tetap',
                            'sumber_nilai' => 'nominal',
                            'periode' => 'bulan',
                            'allowance_type' => 'Ad-hoc',
                            'payout_period' => $nextPeriodStr
                        ]);
                    } else {
                        $this->db->table('pkwt_components')
                            ->where('id', $existingRapel->id)
                            ->update([
                                'nilai' => $rapelAmount
                            ]);
                    }
                }

                // Update logs
                $this->db->table('attendance_logs')
                    ->where('employee_id', $emp->id)
                    ->where('log_date >=', $startDateStr)
                    ->where('log_date <=', $endDateStr)
                    ->where('status', 'Hadir')
                    ->update([
                        'is_rapel' => 1,
                        'payout_period' => $nextPeriodStr
                    ]);

                $this->db->table('overtime_logs')
                    ->where('employee_id', $emp->id)
                    ->where('tanggal >=', $startDateStr)
                    ->where('tanggal <=', $endDateStr)
                    ->update([
                        'is_rapel' => 1,
                        'payout_period' => $nextPeriodStr
                    ]);

                $this->db->table('payroll_attendance')
                    ->where('period_id', $periodId)
                    ->where('pkwt_id', $pkwt->id)
                    ->update([
                        'hari_kerja' => 0,
                        'jam_lembur' => 0.0,
                        'early_arrival_minutes' => 0,
                        'potongan_absensi' => 0.0,
                        'bonus_tambahan' => 0.0
                    ]);

                // Save draft to payroll_final with 0 values
                $existingFinal = $this->db->table('payroll_final')
                                          ->where('period_id', $periodId)
                                          ->where('pkwt_id', $pkwt->id)
                                          ->get()->getRow();
                
                $finalData = [
                    'period_id' => $periodId,
                    'pkwt_id' => $pkwt->id,
                    'total_pendapatan' => $rapelAmount,
                    'total_potongan' => 0.0,
                    'take_home_pay' => $rapelAmount,
                    'status_approval' => 'Hold',
                    'bpjs_kes_karyawan' => 0.0,
                    'bpjs_kes_perusahaan' => 0.0,
                    'bpjs_jht_karyawan' => 0.0,
                    'bpjs_jht_perusahaan' => 0.0,
                    'bpjs_jp_karyawan' => 0.0,
                    'bpjs_jp_perusahaan' => 0.0,
                    'bpjs_jkk_perusahaan' => 0.0,
                    'bpjs_jkm_perusahaan' => 0.0,
                    'pph21' => 0.0,
                    'tax_allowance' => 0.0,
                    'tax_method' => ($client && isset($client->tax_method)) ? $client->tax_method : 'Gross',
                    'ptkp_status' => ($emp && isset($emp->ptkp)) ? $emp->ptkp : 'TK/0',
                    'gaji_pokok' => $gajiPokok,
                    'potongan_absen' => 0.0,
                    'jam_lembur' => 0.0,
                    'jam_lembur_biasa' => 0.0,
                    'jam_lembur_libur' => 0.0,
                    'lembur_pay' => 0.0,
                    'bonus_tambahan' => 0.0,
                    'early_arrival_minutes' => 0,
                    'early_arrival_pay' => 0.0,
                    'raw_components' => json_encode($components)
                ];

                if ($existingFinal) {
                    $this->db->table('payroll_final')->where('id', $existingFinal->id)->update($finalData);
                } else {
                    $this->db->table('payroll_final')->insert($finalData);
                }

                continue; // Skip the rest of the loop!
            }

            // 6. Second pass: Calculate all components
            $totalPendapatan = 0;
            $totalPotongan = 0;
            $tunjanganTetap = 0;

            // Match payroll scheme template for organizational matching
            $schemeModel = new \App\Models\PayrollSchemeTemplateModel();
            $schemeTemplateObj = $schemeModel->getSchemeForEmployee(
                $pkwt->client_id,
                $emp->division_id ?? null,
                $emp->department_id ?? null,
                $emp->position_id ?? null
            );
            $schemeTemplate = $schemeTemplateObj ? (array)$schemeTemplateObj : null;
            $schemeOtRate = ($schemeTemplate && isset($schemeTemplate['rate_lembur_per_jam'])) ? floatval($schemeTemplate['rate_lembur_per_jam']) : 0;

            $bpjsWageBase = 0;
            $pphWageBase = 0;

            foreach ($components as $comp) {
                $isBasic = false;
                if (isset($comp->jenis_komponen) && $comp->jenis_komponen === 'basic_salary') {
                    $isBasic = true;
                } elseif (stripos($comp->nama, 'Gaji Pokok') !== false) {
                    $isBasic = true;
                }

                $nilai = 0;

                if ($isBasic) {
                    $nilai = $gajiPokok;
                } else {
                    if ($hasKompensasiSource && ($comp->jenis_komponen ?? '') === 'kompensasi' && ($comp->sifat_kompensasi ?? '') === 'tetap') {
                        $nilai = 0; // Prevent double-counting by setting it to 0
                    } else if (isset($comp->jenis_komponen) && !empty($comp->jenis_komponen)) {
                        // New Master Compensation component logic
                        $base_nilai = floatval($comp->nilai);
                        
                        if (isset($comp->sumber_nilai)) {
                            if ($comp->sumber_nilai === 'ump') {
                                $base_nilai = $umpWageValue * ($base_nilai / 100);
                            } else if ($comp->sumber_nilai === 'umk') {
                                $base_nilai = $umkWageValue * ($base_nilai / 100);
                            } else if ($comp->sumber_nilai === 'ump_umk') {
                                $base_nilai = $minimumWage * ($base_nilai / 100);
                            }
                        }
                        
                        // Scale by period
                        if ($comp->periode === 'hari' || $comp->periode === 'hari_kerja') {
                            $days = ($att && isset($att->hari_kerja) && $att->hari_kerja !== null) ? intval($att->hari_kerja) : $stdWorkingDays;
                            $nilai = $base_nilai * $days;
                        } elseif ($comp->periode === 'minggu') {
                            $nilai = $base_nilai * 4;
                        } elseif ($comp->periode === 'tahun') {
                            $nilai = $base_nilai / 12;
                        } else {
                            // bulanan
                            // Tunjangan tetap: Nilai tunjangan tetap bersifat konstan setiap periode (TIDAK terprorate)
                            $isCompAdhoc = isset($comp->allowance_type) && $comp->allowance_type === 'Ad-hoc';
                            if ($isProrate && isset($comp->sifat_kompensasi) && $comp->sifat_kompensasi === 'tidak_tetap' && !$isCompAdhoc) {
                                $days = ($att && isset($att->hari_kerja) && $att->hari_kerja !== null) ? intval($att->hari_kerja) : $stdWorkingDays;
                                $nilai = $base_nilai * ($days / $stdWorkingDays);
                            } else {
                                $nilai = $base_nilai;
                            }
                        }
                    } else {
                        // Legacy component logic
                        $nilai = floatval($comp->nilai);
                        if (intval($comp->is_persentase) === 1 || $comp->is_persentase === true) {
                            $nilai = $gajiPokok * ($nilai / 100);
                        }
                    }
                }

                if ($comp->tipe === 'pendapatan') {
                    $totalPendapatan += $nilai;

                    // Resolve inclusion flags
                    $nameClean = strtolower(trim($comp->nama));
                    $isBpjsInc = false;
                    $isPphInc = true;

                    if ($isBasic) {
                        $isBpjsInc = true;
                        $isPphInc = true;
                    } else {
                        $isBpjsInc = (isset($comp->is_bpjs) && $comp->is_bpjs == 1);
                        $isPphInc = (!isset($comp->is_pph21) || $comp->is_pph21 == 1);
                    }

                    if ($isBpjsInc) {
                        $bpjsWageBase += $nilai;
                    }
                    if ($isPphInc) {
                        $pphWageBase += $nilai;
                    }
                } else {
                    $totalPotongan += $nilai;
                }
            }

            // 7. Add Variable Data from Attendance
            $potonganAbsenVal = 0;
            $overtimePay = 0;
            $earlyArrivalMinutes = 0;
            $earlyArrivalPay = 0.0;
            if ($att) {

                if (!$isAbsenTidakPotong) {
                    $potongan_absen = floatval($att->potongan_absensi);
                    if ($potongan_absen == 0 && !$isJoinedPrevMonth && !$isActiveRegularPrevMonth) {
                        $prevMonth = intval($period->bulan) - 1;
                        $prevYear = intval($period->tahun);
                        if ($prevMonth == 0) {
                            $prevMonth = 12;
                            $prevYear--;
                        }
                        $prevMonthStart = sprintf('%04d-%02d-01', $prevYear, $prevMonth);
                        $prevMonthEnd = date('Y-m-t', strtotime($prevMonthStart));
                        $prevCalendarStdDays = ($emp && isset($emp->custom_standard_days) && intval($emp->custom_standard_days) > 0)
                            ? intval($emp->custom_standard_days)
                            : (($clientConfig && isset($clientConfig->standard_work_days) && intval($clientConfig->standard_work_days) > 0)
                                ? intval($clientConfig->standard_work_days)
                                : $this->getStandardWorkingDaysInRange($prevMonthStart, $prevMonthEnd, $workDaysConfig));

                        $expectedWorkingDaysCalendar = $prevCalendarStdDays;
                        if ($emp && !empty($emp->tgl_masuk)) {
                            $joinDateStr = date('Y-m-d', strtotime($emp->tgl_masuk));
                            if ($joinDateStr >= $prevMonthStart && $joinDateStr <= $prevMonthEnd) {
                                $expectedWorkingDaysCalendar = $this->getStandardWorkingDaysInRange($joinDateStr, $prevMonthEnd, $workDaysConfig);
                            }
                        }

                        $missingDays = max(0, $expectedWorkingDaysCalendar - intval($att->hari_kerja));
                        if ($missingDays > 0) {
                            if ($isProrate) {
                                // Pro-rate: potongan = Base Salary * (Hari Tidak Masuk / Hari Kerja Standard)
                                $div = ($prevCalendarStdDays > 0) ? $prevCalendarStdDays : ($stdWorkingDays > 0 ? $stdWorkingDays : 22);
                                $potongan_absen = $unproratedGajiPokok * ($missingDays / $div);
                            } else {
                                // Deduction tetap: potongan = nominal yang ditetapkan per hari absen
                                $nominalPotongan = ($nominalPotonganAbsen > 0) ? $nominalPotonganAbsen : (($absenceConfig && isset($absenceConfig->nominal_potongan)) ? floatval($absenceConfig->nominal_potongan) : 0);
                                $potongan_absen = $missingDays * $nominalPotongan;
                            }
                        }
                    }
                    $potonganAbsenVal = $potongan_absen;
                    $totalPotongan += $potongan_absen;
                }
                
                // Potongan Late dan Early Leave based on attendance_logs
                $cutoffStart = $clientConfig ? intval($clientConfig->cutoff_start) : 1;
                $daysInMonth = date('t', mktime(0, 0, 0, intval($period->bulan), 1, intval($period->tahun)));
                $cutoffEnd = $clientConfig ? intval($clientConfig->cutoff_end) : ($cutoffStart - 1 > 0 ? $cutoffStart - 1 : $daysInMonth);
                if ($cutoffStart === 0) $cutoffStart = 1;
                if ($cutoffEnd === 0) {
                    $cutoffEnd = $cutoffStart - 1;
                    if ($cutoffEnd < 1) $cutoffEnd = $daysInMonth;
                }

                $bulan_start = intval($period->bulan);
                $tahun_start = intval($period->tahun);
                $bulan_end = $bulan_start;
                $tahun_end = $tahun_start;
                if ($cutoffStart > $cutoffEnd && $cutoffStart > 1) {
                    $bulan_start -= 1;
                    if ($bulan_start < 1) {
                        $bulan_start = 12;
                        $tahun_start -= 1;
                    }
                }

                $daysInStartMonth = date('t', mktime(0, 0, 0, $bulan_start, 1, $tahun_start));
                $daysInEndMonth = date('t', mktime(0, 0, 0, $bulan_end, 1, $tahun_end));

                $actualCutoffStart = min($cutoffStart, $daysInStartMonth);
                $actualCutoffEnd = min($cutoffEnd, $daysInEndMonth);

                $startDateStr = sprintf('%04d-%02d-%02d', $tahun_start, $bulan_start, $actualCutoffStart);
                $endDateStr = sprintf('%04d-%02d-%02d', $tahun_end, $bulan_end, $actualCutoffEnd);

                $lateEarlySum = null;
                if ($emp && isset($emp->id)) {
                    $lateEarlySum = $this->db->table('attendance_logs')
                        ->selectSum('late_hours')
                        ->selectSum('early_leave_hours')
                        ->where('employee_id', $emp->id)
                        ->where('log_date >=', $effectiveStartDateStr)
                        ->where('log_date <=', $endDateStr)
                        ->get()->getRow();
                }
                
                $lateHours = $lateEarlySum ? floatval($lateEarlySum->late_hours) : 0;
                $earlyHours = $lateEarlySum ? floatval($lateEarlySum->early_leave_hours) : 0;
                
                $standardHours = 160.0;
                $upahPerJam = $gajiPokok / $standardHours;
                
                $potonganLate = $lateHours * $upahPerJam;
                $potonganEarly = $earlyHours * $upahPerJam;
                
                $totalPotongan += $potonganLate + $potonganEarly;
                
                // === Early Arrival Calculation ===
                $companySetting = $this->db->table('company_payroll_setting')->get()->getRowArray();
                $minMinutes = isset($companySetting['early_arrival_min_minutes']) ? intval($companySetting['early_arrival_min_minutes']) : 30;
                $calcUnit = isset($companySetting['early_arrival_calculation_unit']) ? intval($companySetting['early_arrival_calculation_unit']) : 60;
                $roundingMethod = $companySetting['early_arrival_rounding_method'] ?? 'CEILING';
                $maxMinutes = isset($companySetting['max_early_arrival_minutes']) ? intval($companySetting['max_early_arrival_minutes']) : 180;
                $earlyArrivalEnabled = isset($companySetting['early_arrival_enabled']) ? intval($companySetting['early_arrival_enabled']) : 1;

                $totalEarlyArrivalBobotHours = 0.0;
                $earlyArrivalMinutes = isset($att->early_arrival_minutes) ? intval($att->early_arrival_minutes) : 0;

                if ($earlyArrivalEnabled) {
                    // Cari total APPROVED early arrival logs untuk menentukan override
                    $approvedEarlyArrivals = [];
                    if ($emp && isset($emp->id)) {
                        $approvedEarlyArrivals = $this->db->table('early_arrival')
                            ->where('employee_id', $emp->id)
                            ->where('date >=', $effectiveStartDateStr)
                            ->where('date <=', $endDateStr)
                            ->where('status', 'APPROVED')
                            ->where('payroll_status', 'NOT_PROCESSED')
                            ->get()->getResultArray();
                    }

                    $sumEligibleMinutes = 0;
                    foreach ($approvedEarlyArrivals as $eaLog) {
                        $sumEligibleMinutes += intval($eaLog['eligible_minutes']);
                    }

                    // Check override manual (bila data di payroll_attendance.early_arrival_minutes berbeda dengan logs di DB)
                    $isOverride = ($att && ($earlyArrivalMinutes !== $sumEligibleMinutes || (empty($approvedEarlyArrivals) && $earlyArrivalMinutes > 0)));

                    if ($isOverride) {
                        // Gunakan nilai override dari payroll_attendance
                        if ($earlyArrivalMinutes >= $minMinutes) {
                            $jamEA = ($roundingMethod === 'CEILING') ? ceil($earlyArrivalMinutes / $calcUnit) : ($earlyArrivalMinutes / $calcUnit);
                            
                            // Batasi max_early_arrival_minutes
                            $maxHours = $maxMinutes / $calcUnit;
                            if ($jamEA > $maxHours) {
                                $jamEA = $maxHours;
                            }
                            
                            // Hitung bobot jam Kemnaker
                            if ($jamEA >= 1) {
                                $totalEarlyArrivalBobotHours = 1.5 + ($jamEA - 1) * 2.0;
                            }
                        }
                    } else {
                        // Hitung per transaksi harian
                        foreach ($approvedEarlyArrivals as $eaLog) {
                            $minutes = intval($eaLog['eligible_minutes']);
                            if ($minutes >= $minMinutes) {
                                $jamEA = ($roundingMethod === 'CEILING') ? ceil($minutes / $calcUnit) : ($minutes / $calcUnit);
                                
                                // Batasi max_early_arrival_minutes
                                $maxHours = $maxMinutes / $calcUnit;
                                if ($jamEA > $maxHours) {
                                    $jamEA = $maxHours;
                                }
                                
                                // Hitung bobot jam Kemnaker
                                if ($jamEA >= 1) {
                                    $totalEarlyArrivalBobotHours += (1.5 + ($jamEA - 1) * 2.0);
                                }
                            }
                        }
                    }

                    // Update early arrival records to PROCESSED
                    $eaIds = [];
                    foreach ($approvedEarlyArrivals as $eaLog) {
                        $eaIds[] = $eaLog['id'];
                    }

                    if (!empty($eaIds)) {
                        $payoutPeriodStr = ($period->bulan < 10 ? '0' : '') . $period->bulan . '/' . $period->tahun;
                        $this->db->table('early_arrival')
                            ->whereIn('id', $eaIds)
                            ->update([
                                'payroll_status' => 'PROCESSED',
                                'payroll_period' => $payoutPeriodStr,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                    }
                }

                $totalTunjanganTetap = 0.0;
                
                // 1. Dari $components (dari pkwt_components)
                foreach ($components as $comp) {
                    $isTunjangan = (isset($comp->tipe) && (strtolower($comp->tipe) === 'pendapatan' || strtolower($comp->tipe) === 'tunjangan'));
                    $isTetap = (isset($comp->sifat_kompensasi) && strtolower($comp->sifat_kompensasi) === 'tetap');
                    $isBulanan = (empty($comp->periode) || strtolower($comp->periode) === 'bulan');
                    $isBasic = (isset($comp->jenis_komponen) && $comp->jenis_komponen === 'basic_salary') || (stripos($comp->nama, 'Gaji Pokok') !== false);
                    
                    if ($isTunjangan && $isTetap && $isBulanan && !$isBasic) {
                        $base_nilai = floatval($comp->nilai);
                        $sumber_nilai = $comp->sumber_nilai ?? 'nominal';
                        if ($sumber_nilai === 'ump') {
                            $base_nilai = $umpWageValue * ($base_nilai / 100);
                        } else if ($sumber_nilai === 'umk') {
                            $base_nilai = $umkWageValue * ($base_nilai / 100);
                        } else if ($sumber_nilai === 'ump_umk') {
                            $base_nilai = $minimumWage * ($base_nilai / 100);
                        }
                        $totalTunjanganTetap += $base_nilai;
                    }
                }

                // 2. Dari $schemeTemplate
                if ($schemeTemplate) {
                    $totalTunjanganTetap += floatval($schemeTemplate['tunjangan_transport'] ?? 0);
                    $totalTunjanganTetap += floatval($schemeTemplate['tunjangan_komunikasi'] ?? 0);
                    $totalTunjanganTetap += floatval($schemeTemplate['tunjangan_jabatan'] ?? 0);
                    $totalTunjanganTetap += floatval($schemeTemplate['tunjangan_kehadiran'] ?? 0);
                    $totalTunjanganTetap += floatval($schemeTemplate['tunjangan_kinerja'] ?? 0);
                }

                $eaDivisor = ($clientConfig && isset($clientConfig->overtime_divisor) && intval($clientConfig->overtime_divisor) > 0) ? intval($clientConfig->overtime_divisor) : 173.0;
                $gajiPokokForEa = ($unproratedGajiPokok > 0) ? $unproratedGajiPokok : $gajiPokok;
                $eaUpahPerJam = ($gajiPokokForEa + $totalTunjanganTetap) / $eaDivisor;

                $earlyArrivalPay = 0.0;
                if ($totalEarlyArrivalBobotHours > 0) {
                    $earlyArrivalPay = $totalEarlyArrivalBobotHours * $eaUpahPerJam;
                    $totalPendapatan += $earlyArrivalPay;
                }
                
                $totalPendapatan += $att->bonus_tambahan;
                
                // === Overtime Calculation — PP No. 35 Tahun 2021 (Progressive Multiplier) ===
                $jamLemburBiasa = floatval($att->jam_lembur_hari_biasa ?? 0);
                $jamLemburLibur = floatval($att->jam_lembur_hari_libur ?? 0);

                // Fallback: if breakdown columns are empty but total jam_lembur has value,
                // treat all as regular day overtime (backward compatibility)
                if ($jamLemburBiasa == 0 && $jamLemburLibur == 0 && floatval($att->jam_lembur) > 0) {
                    $jamLemburBiasa = floatval($att->jam_lembur);
                }

                // Determine working days per week for holiday multiplier bracket
                $workDaysPerWeek = 5;
                if ($emp && isset($emp->position_hari_kerja)) {
                    $posHkOt = intval($emp->position_hari_kerja);
                    if ($posHkOt == 6) $workDaysPerWeek = 6;
                }

                // Calculate upah sejam (hourly rate for overtime)
                $overtimeType = ($payrollScheme && !empty($payrollScheme->overtime_type)) ? $payrollScheme->overtime_type : 'standard';
                $overtimePay = 0;

                // Resolve calendar month range start/end for overtime logs query
                $calPrevMonth = intval($period->bulan) - 1;
                $calPrevYear = intval($period->tahun);
                if ($calPrevMonth == 0) {
                    $calPrevMonth = 12;
                    $calPrevYear--;
                }
                $calStartDateStr = sprintf('%04d-%02d-01', $calPrevYear, $calPrevMonth);
                $calEndDateStr = date('Y-m-t', strtotime($calStartDateStr));
                $calEffectiveStartDateStr = !empty($emp->tgl_masuk) ? max($calStartDateStr, date('Y-m-d', strtotime($emp->tgl_masuk))) : $calStartDateStr;

                if ($overtimeType === 'lumpsum') {
                    $lumpsumSubtype = $payrollScheme->lumpsum_subtype ?? 'per_jam';
                    $lumpsumNominal = floatval($payrollScheme->lumpsum_nominal ?? 0);
                    
                    if ($lumpsumSubtype === 'per_jam') {
                        $overtimePay = ($jamLemburBiasa + $jamLemburLibur) * $lumpsumNominal;
                    } elseif ($lumpsumSubtype === 'harian') {
                        // Count the number of distinct approved overtime logs in the calendar month range for this employee
                        $numDaysLembur = 0;
                        if ($emp && isset($emp->id)) {
                            $otLogsQuery = $this->db->table('overtime_logs')
                                ->select('tanggal')
                                ->where('employee_id', $emp->id)
                                ->where('tanggal >=', $calEffectiveStartDateStr)
                                ->where('tanggal <=', $calEndDateStr)
                                ->where('status', 'Approved')
                                ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                                ->get()
                                ->getResultArray();
                            
                            $distinctDays = [];
                            foreach ($otLogsQuery as $otLog) {
                                $distinctDays[$otLog['tanggal']] = true;
                            }
                            $numDaysLembur = count($distinctDays);
                        }
                        $overtimePay = $numDaysLembur * $lumpsumNominal;
                    } elseif ($lumpsumSubtype === 'bulanan') {
                        $overtimePay = (($jamLemburBiasa + $jamLemburLibur) > 0) ? $lumpsumNominal : 0.0;
                    }
                } else {
                    // Priority: manual client rate > scheme template rate > formula (salary/173)
                    $upahSejamLembur = 0;
                    if ($clientManualOtRate > 0) {
                        $upahSejamLembur = $clientManualOtRate;
                    } elseif ($schemeOtRate > 0) {
                        $upahSejamLembur = $schemeOtRate;
                    } else {
                        // Use formula: Gaji Pokok / divisor (PP 35/2021)
                        $otDivisor = ($clientConfig && isset($clientConfig->overtime_divisor) && intval($clientConfig->overtime_divisor) > 0) ? intval($clientConfig->overtime_divisor) : 173;
                        $gajiPokokForOt = ($unproratedGajiPokok > 0) ? $unproratedGajiPokok : $gajiPokok;
                        $upahSejamLembur = ($gajiPokokForOt > 0) ? ($gajiPokokForOt / $otDivisor) : 0;
                    }

                    // Query daily overtime logs in the calendar month range
                    $otLogsQuery = [];
                    if ($emp && isset($emp->id)) {
                        $otLogsQuery = $this->db->table('overtime_logs')
                            ->where('employee_id', $emp->id)
                            ->where('tanggal >=', $calEffectiveStartDateStr)
                            ->where('tanggal <=', $calEndDateStr)
                            ->where('status', 'Approved')
                            ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                            ->get()
                            ->getResultArray();
                    }

                    if (!empty($otLogsQuery)) {
                        $jamKonversiBiasa = 0.0;
                        $jamKonversiLibur = 0.0;
                        foreach ($otLogsQuery as $otLog) {
                            $otHours = floatval($otLog['jam_lembur']);
                            $isOtHoliday = isset($otLog['is_holiday']) && intval($otLog['is_holiday']) === 1;
                            if ($isOtHoliday) {
                                $jamKonversiLibur += $this->hitungJamLemburKonversi($otHours, true, $workDaysPerWeek, true);
                            } else {
                                $jamKonversiBiasa += $this->hitungJamLemburKonversi($otHours, false, $workDaysPerWeek, true);
                            }
                        }
                    } else {
                        // Fallback to monthly total, without daily cap
                        $jamKonversiBiasa = $this->hitungJamLemburKonversi($jamLemburBiasa, false, $workDaysPerWeek, false);
                        $jamKonversiLibur = $this->hitungJamLemburKonversi($jamLemburLibur, true, $workDaysPerWeek, false);
                    }

                    $overtimePay = ($jamKonversiBiasa + $jamKonversiLibur) * $upahSejamLembur;
                }
                
                $totalPendapatan += $overtimePay;
            }

            // Calculate scheme template allowances and deductions
            $days = ($att && isset($att->hari_kerja) && $att->hari_kerja !== null) ? intval($att->hari_kerja) : $stdWorkingDays;
            $schemeAllowances = [];
            $schemeDeductions = [];

            if ($schemeTemplate) {
                $schemeAllowances = [
                    'Tunjangan Transport' => floatval($schemeTemplate['tunjangan_transport'] ?? 0),
                    'Tunjangan Makan Harian' => floatval($schemeTemplate['tunjangan_makan'] ?? 0),
                    'Tunjangan Komunikasi' => floatval($schemeTemplate['tunjangan_komunikasi'] ?? 0),
                    'Tunjangan Jabatan' => floatval($schemeTemplate['tunjangan_jabatan'] ?? 0),
                    'Tunjangan Kehadiran' => floatval($schemeTemplate['tunjangan_kehadiran'] ?? 0),
                    'Tunjangan Kinerja' => floatval($schemeTemplate['tunjangan_kinerja'] ?? 0),
                ];

                foreach ($schemeAllowances as $allowanceName => $allowanceValue) {
                    if ($allowanceValue > 0) {
                        if ($allowanceName === 'Tunjangan Makan Harian') {
                            $finalValue = $allowanceValue * $days;
                        } else {
                            $finalValue = $allowanceValue;
                        }

                        $totalPendapatan += $finalValue;

                        // Check BPJS and PPh inclusion for this allowance
                        $bpjsField = '';
                        $pphField = '';
                        switch ($allowanceName) {
                            case 'Tunjangan Transport':
                                $bpjsField = 'bpjs_inc_transport';
                                $pphField = 'pph_inc_transport';
                                break;
                            case 'Tunjangan Makan Harian':
                                $bpjsField = 'bpjs_inc_makan';
                                $pphField = 'pph_inc_makan';
                                break;
                            case 'Tunjangan Komunikasi':
                                $bpjsField = 'bpjs_inc_komunikasi';
                                $pphField = 'pph_inc_komunikasi';
                                break;
                            case 'Tunjangan Jabatan':
                                $bpjsField = 'bpjs_inc_jabatan';
                                $pphField = 'pph_inc_jabatan';
                                break;
                            case 'Tunjangan Kehadiran':
                                $bpjsField = 'bpjs_inc_kehadiran';
                                $pphField = 'pph_inc_kehadiran';
                                break;
                            case 'Tunjangan Kinerja':
                                $bpjsField = 'bpjs_inc_kinerja';
                                $pphField = 'pph_inc_kinerja';
                                break;
                        }

                        $isBpjsInc = !empty($bpjsField) && ($schemeTemplate[$bpjsField] ?? 0) == 1;
                        $isPphInc = !empty($pphField) && ($schemeTemplate[$pphField] ?? 0) == 1;

                        if ($isBpjsInc) {
                            $bpjsWageBase += $finalValue;
                        }
                        if ($isPphInc) {
                            $pphWageBase += $finalValue;
                        }

                        // Append to components array for raw_components JSON storage
                        $compObj = new \stdClass();
                        $compObj->nama = $allowanceName;
                        $compObj->tipe = 'pendapatan';
                        $compObj->nilai = $finalValue;
                        $compObj->is_persentase = 0;
                        $compObj->jenis_komponen = 'scheme_template';
                        $components[] = $compObj;
                    }
                }

                $schemeDeductions = [
                    'Potongan Pinjaman' => floatval($schemeTemplate['potongan_pinjaman'] ?? 0),
                    'Potongan Kasbon' => floatval($schemeTemplate['potongan_kasbon'] ?? 0),
                    'Potongan Lainnya' => floatval($schemeTemplate['potongan_lainnya'] ?? 0),
                ];

                foreach ($schemeDeductions as $deductionName => $deductionValue) {
                    if ($deductionValue > 0) {
                        $totalPotongan += $deductionValue;

                        // Append to components array for raw_components JSON storage
                        $compObj = new \stdClass();
                        $compObj->nama = $deductionName;
                        $compObj->tipe = 'potongan';
                        $compObj->nilai = $deductionValue;
                        $compObj->is_persentase = 0;
                        $compObj->jenis_komponen = 'scheme_template';
                        $components[] = $compObj;
                    }
                }
            }

            // Adjust PPh wage base for attendance variations
            $pphWageBaseFinal = $pphWageBase;
            if ($att) {
                $pphWageBaseFinal += $overtimePay + floatval($att->bonus_tambahan) + $earlyArrivalPay - $potonganAbsenVal;
            }

            // Fallback for BPJS wage base to minimumWage if lower
            if ($minimumWage > 0 && $bpjsWageBase < $minimumWage) {
                $bpjsWageBase = $minimumWage;
            }

            // Calculate BPJS & Tax
            $taxScheme = null;
            if ($clientConfig && $clientConfig->tax_scheme_id) {
                $taxScheme = $this->db->table('tax_schemes')->where('id', $clientConfig->tax_scheme_id)->get()->getRow();
            }

            $bpjsScheme = null;
            if ($clientConfig && !empty($clientConfig->bpjs_scheme_id)) {
                $bpjsScheme = $this->db->table('tax_schemes')->where('id', $clientConfig->bpjs_scheme_id)->get()->getRow();
            }
            if (!$bpjsScheme) {
                $bpjsScheme = $taxScheme;
            }

            $ptkpStatus = 'TK/0';
            if ($emp && !empty($emp->ptkp)) {
                $ptkpStatus = $emp->ptkp;
            } elseif ($taxScheme && !empty($taxScheme->ptkp_status)) {
                $ptkpStatus = $taxScheme->ptkp_status;
            } elseif ($schemeTemplate && !empty($schemeTemplate['ptkp_status'])) {
                $ptkpStatus = $schemeTemplate['ptkp_status'];
            }

            // Resolve BPJS & Tax calculations
            $calc = $this->calculateBpjsAndTax($gajiPokok, $bpjsWageBase, $pphWageBaseFinal, $schemeTemplate, $taxScheme, $minimumWage, $ptkpStatus, $bpjsScheme, intval($period->bulan), intval($period->tahun), $pkwt->id);

            // Deductions from Employee: BPJS Kes Karyawan, JHT Karyawan, JP Karyawan
            $employeeBpjsDeductions = $calc['bpjs_kes_karyawan'] + $calc['bpjs_jht_karyawan'] + $calc['bpjs_jp_karyawan'];

            if ($calc['metode_pajak'] === 'Gross Up') {
                $totalPendapatan += $calc['tax_allowance'];
                $totalPotongan += $employeeBpjsDeductions + $calc['pph21'];
            } elseif ($calc['metode_pajak'] === 'Gross') {
                $totalPotongan += $employeeBpjsDeductions + $calc['pph21'];
            } else { // Nett
                $totalPotongan += $employeeBpjsDeductions;
            }

            $thp = $totalPendapatan - $totalPotongan;

            // 8. Save to payroll_final
            $existingFinal = $this->db->table('payroll_final')
                                      ->where('period_id', $periodId)
                                      ->where('pkwt_id', $pkwt->id)
                                      ->get()->getRow();
            
            $finalData = [
                'period_id' => $periodId,
                'pkwt_id' => $pkwt->id,
                'total_pendapatan' => $totalPendapatan,
                'total_potongan' => $totalPotongan,
                'take_home_pay' => $thp,
                'status_approval' => $holdPayroll ? 'Hold' : 'Pending',
                'bpjs_kes_karyawan' => $calc['bpjs_kes_karyawan'],
                'bpjs_kes_perusahaan' => $calc['bpjs_kes_perusahaan'],
                'bpjs_jht_karyawan' => $calc['bpjs_jht_karyawan'],
                'bpjs_jht_perusahaan' => $calc['bpjs_jht_perusahaan'],
                'bpjs_jp_karyawan' => $calc['bpjs_jp_karyawan'],
                'bpjs_jp_perusahaan' => $calc['bpjs_jp_perusahaan'],
                'bpjs_jkk_perusahaan' => $calc['bpjs_jkk_perusahaan'],
                'bpjs_jkm_perusahaan' => $calc['bpjs_jkm_perusahaan'],
                'pph21' => $calc['pph21'],
                'tax_allowance' => $calc['tax_allowance'],
                'tax_method' => $calc['metode_pajak'],
                'ptkp_status' => $ptkpStatus,
                'gaji_pokok' => $gajiPokok,
                'potongan_absen' => $potonganAbsenVal,
                'jam_lembur' => ($att) ? floatval($att->jam_lembur) : 0,
                'jam_lembur_biasa' => ($att) ? floatval($att->jam_lembur_hari_biasa ?? 0) : 0,
                'jam_lembur_libur' => ($att) ? floatval($att->jam_lembur_hari_libur ?? 0) : 0,
                'lembur_pay' => ($att) ? $overtimePay : 0,
                'bonus_tambahan' => ($att) ? floatval($att->bonus_tambahan) : 0,
                'early_arrival_minutes' => $earlyArrivalMinutes,
                'early_arrival_pay' => $earlyArrivalPay,
                'raw_components' => json_encode($components)
            ];

            if ($existingFinal) {
                $this->db->table('payroll_final')->where('id', $existingFinal->id)->update($finalData);
            } else {
                $this->db->table('payroll_final')->insert($finalData);
            }

        }

        $period = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        $period = $this->formatPeriodRow($period);
        $periodName = $period ? $period->nama : "ID: $periodId";
        $this->logActivity("Men-generate payroll untuk periode: " . $periodName);
        return $this->respond(['message' => 'Gaji bulanan berhasil di-generate untuk periode ini']);
    }

    public function getPayrollResults($periodId)
    {
        $clientId = $this->request->getGet('client_id');
        $query = $this->db->table('payroll_final')
                         ->select('
                             payroll_final.*, 
                             pkwt.employee_name, 
                             pkwt.tipe_perjanjian, 
                             pkwt.position_name as pkwt_position_name,
                             pkwt.client_id,
                             clients.nama as client_name,
                             divisions.nama as division_name,
                             departments.nama as department_name,
                             positions.nama as position_name
                         ')
                         ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                         ->join('clients', 'clients.id = pkwt.client_id', 'left')
                         ->join('employees', 'employees.nama = pkwt.employee_name AND employees.client_id = pkwt.client_id', 'left')
                         ->join('positions', 'positions.id = employees.position_id', 'left')
                         ->join('departments', 'departments.id = positions.department_id', 'left')
                         ->join('divisions', 'divisions.id = departments.division_id', 'left')
                         ->where('payroll_final.period_id', $periodId);
        if ($clientId) {
            $query->where('pkwt.client_id', $clientId);
        }
        $data = $query->get()->getResult();

        $period = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        $tYear = $period ? intval($period->tahun) : intval(date('Y'));
        $tMonth = $period ? intval($period->bulan) : intval(date('n'));

        foreach ($data as &$row) {
            $row->division_name = $row->division_name ?? '-';
            $row->department_name = $row->department_name ?? '-';
            $row->position_name = $row->position_name ?? $row->pkwt_position_name ?? '-';

            // Resolve Scheme Name
            $schemeName = '-';
            $clientConfig = $this->resolveClientConfig($row->client_id, $row->position_name);
            if ($clientConfig) {
                if ($clientConfig->payroll_type === 'Nominal') {
                    $schemeName = 'Nominal (Rp ' . number_format($clientConfig->custom_nominal, 0, ',', '.') . ')';
                } elseif ($clientConfig->payroll_type === 'UMP/UMK' || $clientConfig->payroll_type === 'UMP' || $clientConfig->payroll_type === 'UMK') {
                    $schemeName = $clientConfig->payroll_type;
                } elseif ($clientConfig->payroll_type === 'Template' && $clientConfig->payroll_scheme_id) {
                    $payrollScheme = $this->db->table('payroll_schemes')
                                              ->where('id', $clientConfig->payroll_scheme_id)
                                              ->get()
                                              ->getRow();
                    if ($payrollScheme) {
                        $schemeName = $payrollScheme->nama;
                    } else {
                        $schemeName = 'Template';
                    }
                }
            }
            $row->scheme_name = $schemeName;

            // Resolve new hire rapel details for frontend display
            $emp = $this->db->table('employees')
                            ->where('nama', $row->employee_name)
                            ->where('client_id', $row->client_id)
                            ->get()->getRow();

            $isNewHireRapel = false;
            $nextPeriodStr = '';
            if ($emp && !empty($emp->tgl_masuk)) {
                $joinTs = strtotime($emp->tgl_masuk);
                $joinDateStr = date('Y-m-d', $joinTs);

                $start = null;
                $end = null;
                $refId = null;
                if ($clientConfig) {
                    $startField = "cutoff_gaji_pokok_start";
                    $endField = "cutoff_gaji_pokok_end";
                    $refField = "cutoff_gaji_pokok_schedule_ref";
                    
                    $start = isset($clientConfig->$startField) ? intval($clientConfig->$startField) : null;
                    $end = isset($clientConfig->$endField) ? intval($clientConfig->$endField) : null;
                    $refId = isset($clientConfig->$refField) ? intval($clientConfig->$refField) : null;
                    
                    if ($refId) {
                        $sched = $this->db->table('payroll_schedules')->where('id', $refId)->get()->getRow();
                        if ($sched) {
                            $start = intval($sched->cutoff_start);
                            $end = intval($sched->cutoff_end);
                        }
                    }
                }
                
                if ($start === null) {
                    $start = ($clientConfig && isset($clientConfig->cutoff_start)) ? intval($clientConfig->cutoff_start) : 21;
                }
                if ($end === null) {
                    $end = ($clientConfig && isset($clientConfig->cutoff_end)) ? intval($clientConfig->cutoff_end) : ($start - 1);
                    if ($end < 1) $end = 31;
                }
                
                $cutoffStart = $start;
                $cutoffEnd = $end;
                
                $currentPeriodCutoffEnd = sprintf('%04d-%02d-%02d', $tYear, $tMonth, $cutoffEnd);
                
                if ($joinDateStr > $currentPeriodCutoffEnd) {
                    $isRapelGP = ($clientConfig && isset($clientConfig->is_rapel_gaji_pokok)) ? intval($clientConfig->is_rapel_gaji_pokok) : 1;
                    $joinYear = intval(date('Y', $joinTs));
                    $joinMonth = intval(date('n', $joinTs));
                    $joinDay = intval(date('j', $joinTs));
                    
                    if ($joinYear === $tYear && $joinMonth === $tMonth && $joinDay >= $cutoffStart && $isRapelGP === 1) {
                        $isNewHireRapel = true;
                        
                        $nextMonth = $tMonth + 1;
                        $nextYear = $tYear;
                        if ($nextMonth > 12) {
                            $nextMonth = 1;
                            $nextYear++;
                        }
                        $nextPeriodStr = $nextMonth . '-' . $nextYear;
                    }
                }
            }

            $row->is_new_hire_rapel = $isNewHireRapel;
            $row->rapel_payout_period = $nextPeriodStr;
        }

        return $this->respond($data);
    }

    public function approvePayroll($id)
    {
        $row = $this->db->table('payroll_final')
                        ->select('payroll_final.*, pkwt.employee_name, pkwt.position_name, pkwt.client_id')
                        ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                        ->where('payroll_final.id', $id)
                        ->get()
                        ->getRow();
        if (!$row) {
            return $this->failNotFound('Data gaji tidak ditemukan');
        }
        
        if ($row->status_approval === 'Hold') {
            return $this->fail('Gaji untuk karyawan ' . $row->employee_name . ' tidak dapat disetujui karena berstatus Hold (dirapel ke bulan depan).');
        }
        
        // Resolve Scheme
        $clientConfig = $this->resolveClientConfig($row->client_id, $row->position_name);
        if (!$clientConfig) {
            return $this->fail('Gaji untuk karyawan ' . $row->employee_name . ' tidak dapat disetujui karena belum memiliki skema payroll.');
        }
        
        // Check Net Salary (take_home_pay)
        if (floatval($row->take_home_pay) <= 0) {
            return $this->fail('Gaji untuk karyawan ' . $row->employee_name . ' tidak dapat disetujui karena Net Salary bernilai Rp 0 atau kurang.');
        }

        $username = $this->request->getHeaderLine('X-User-Action') ?: 'Admin';
        $this->db->table('payroll_final')->where('id', $id)->update([
            'status_approval' => 'Approved',
            'approved_by' => $username
        ]);
        
        $employeeName = $row->employee_name;
        $this->logActivity("Menyetujui payroll karyawan: " . $employeeName . " (Payroll ID: " . $id . ")");
        return $this->respond(['message' => 'Gaji telah disetujui']);
    }

    public function approvePayrollBulk()
    {
        $json = $this->request->getJSON(true);
        $ids = $json['ids'] ?? [];
        if (empty($ids)) {
            return $this->fail('Tidak ada data gaji yang dipilih.');
        }

        // Validation check for all selected IDs first
        foreach ($ids as $id) {
            $row = $this->db->table('payroll_final')
                            ->select('payroll_final.*, pkwt.employee_name, pkwt.position_name, pkwt.client_id')
                            ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                            ->where('payroll_final.id', $id)
                            ->get()
                            ->getRow();
            if (!$row) {
                return $this->failNotFound('Data gaji tidak ditemukan (ID: ' . $id . ')');
            }
            
            if ($row->status_approval === 'Hold') {
                return $this->fail('Gaji untuk karyawan ' . $row->employee_name . ' tidak dapat disetujui karena berstatus Hold (dirapel ke bulan depan).');
            }
            
            // Resolve Scheme
            $clientConfig = $this->resolveClientConfig($row->client_id, $row->position_name);
            if (!$clientConfig) {
                return $this->fail('Gaji untuk karyawan ' . $row->employee_name . ' tidak dapat disetujui karena belum memiliki skema payroll.');
            }
            
            // Check Net Salary (take_home_pay)
            if (floatval($row->take_home_pay) <= 0) {
                return $this->fail('Gaji untuk karyawan ' . $row->employee_name . ' tidak dapat disetujui karena Net Salary bernilai Rp 0 atau kurang.');
            }
        }

        $username = $this->request->getHeaderLine('X-User-Action') ?: 'Admin';
        $this->db->table('payroll_final')
                 ->whereIn('id', $ids)
                 ->update([
                     'status_approval' => 'Approved',
                     'approved_by' => $username
                 ]);
                 
        // Log activity for each approved employee
        $finals = $this->db->table('payroll_final')
                          ->select('payroll_final.id, pkwt.employee_name')
                          ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                          ->whereIn('payroll_final.id', $ids)
                          ->get()
                          ->getResult();
                          
        foreach ($finals as $final) {
            $this->logActivity("Menyetujui payroll karyawan: " . $final->employee_name . " (Payroll ID: " . $final->id . ")");
        }
        
        return $this->respond(['status' => 'success', 'message' => 'Semua data gaji terpilih berhasil disetujui']);
    }

    public function uploadManualPayroll()
    {
        $json = $this->request->getJSON(true);
        $periodId = $json['period_id'] ?? null;
        $clientId = $json['client_id'] ?? null;
        $rows = $json['rows'] ?? [];

        if (!$periodId) {
            return $this->fail('Parameter period_id dibutuhkan.');
        }
        if (empty($rows)) {
            return $this->fail('Tidak ada data gaji untuk di-upload.');
        }

        $period = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        if (!$period) {
            return $this->failNotFound('Periode tidak ditemukan.');
        }

        $periodStatus = strtolower($period->status ?? 'open');
        if ($periodStatus !== 'open' && $periodStatus !== 'active' && $periodStatus !== 'terbuka') {
            return $this->fail('Periode payroll sudah ditutup, tidak dapat meng-upload data gaji.');
        }

        $this->db->transStart();

        foreach ($rows as $row) {
            $pkwtId = $row['pkwt_id'] ?? null;
            if (!$pkwtId) continue;

            // Fetch PKWT
            $pkwt = $this->db->table('pkwt')->where('id', $pkwtId)->get()->getRow();
            if (!$pkwt) continue;

            // Fetch Employee
            $emp = $this->db->table('employees')
                            ->select('employees.*, minimum_wages.nominal as umr_nominal, minimum_wages.id as mw_id, positions.hari_kerja as position_hari_kerja')
                            ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                            ->join('positions', 'positions.id = employees.position_id', 'left')
                            ->where('employees.nama', $pkwt->employee_name)
                            ->where('employees.client_id', $pkwt->client_id)
                            ->get()
                            ->getRow();
            if (!$emp) continue;

            // Resolve Client Config
            $clientConfig = $this->resolveClientConfig($pkwt->client_id, $pkwt->position_name);

            // Update payroll_attendance first to match manual upload values
            $att = $this->db->table('payroll_attendance')
                            ->where('period_id', $periodId)
                            ->where('pkwt_id', $pkwtId)
                            ->get()->getRow();

            $attData = [
                'period_id' => $periodId,
                'pkwt_id' => $pkwtId,
                'hari_kerja' => $row['working_days'],
                'jam_lembur' => $row['overtime_hours'],
                'early_arrival_minutes' => intval(($row['early_arrival_hours'] ?? 0) * 60),
                'potongan_absensi' => $row['potongan_absen'],
                'bonus_tambahan' => $row['bonus_tambahan'],
                'is_manual' => 1
            ];

            if ($att) {
                $this->db->table('payroll_attendance')->where('id', $att->id)->update($attData);
            } else {
                $this->db->table('payroll_attendance')->insert($attData);
            }

            // Resolve UMP & UMK minimum wages
            $minimumWage = 0;
            $mwId = null;
            if ($emp && isset($emp->umr_nominal) && $emp->umr_nominal > 0) {
                $minimumWage = floatval($emp->umr_nominal);
                $mwId = $emp->mw_id;
            } else {
                if ($clientConfig && isset($clientConfig->minimum_wage_id)) {
                    $mw = $this->db->table('minimum_wages')->where('id', $clientConfig->minimum_wage_id)->get()->getRow();
                    if ($mw) {
                        $minimumWage = floatval($mw->nominal);
                        $mwId = $mw->id;
                    }
                }
            }

            $empProvince = null;
            if ($emp && !empty($emp->work_location_id)) {
                $wl = $this->db->table('work_locations')->where('id', $emp->work_location_id)->get()->getRow();
                if ($wl && !empty($wl->provinsi)) {
                    $empProvince = $wl->provinsi;
                }
            }
            $resolvedWages = $this->resolveUmpUmk($mwId, null, $empProvince);
            $umpWageValue = $resolvedWages['ump'];
            $umkWageValue = $resolvedWages['umk'];

            // Fetch and build components
            $rawComponents = $this->db->table('pkwt_components')->where('pkwt_id', $pkwtId)->get()->getResult();
            $components = [];
            $hasBasicSalaryInComponents = false;
            $hasRapelInComponents = false;
            $hasPotonganLainInComponents = false;

            foreach ($rawComponents as $comp) {
                // If it is rapel, we override its value with the uploaded rapel
                if (stripos($comp->nama, 'Rapel') !== false) {
                    $comp->nilai = $row['rapel'];
                    $hasRapelInComponents = true;
                }
                
                // Skip ad-hoc components if period doesn't match and it is not a rapel component
                $isAdhoc = isset($comp->allowance_type) && $comp->allowance_type === 'Ad-hoc';
                if ($isAdhoc) {
                    $payoutPeriod = trim($comp->payout_period ?? '');
                    $currentPeriod1 = intval($period->bulan) . '-' . intval($period->tahun);
                    $currentPeriod2 = sprintf('%02d-%d', intval($period->bulan), intval($period->tahun));
                    if ($payoutPeriod !== $currentPeriod1 && $payoutPeriod !== $currentPeriod2 && stripos($comp->nama, 'Rapel') === false) {
                        continue;
                    }
                }

                $isBasic = (isset($comp->jenis_komponen) && $comp->jenis_komponen === 'basic_salary') || (stripos($comp->nama, 'Gaji Pokok') !== false);
                if ($isBasic) {
                    $comp->nilai = $row['gaji_pokok'];
                    $hasBasicSalaryInComponents = true;
                }
                
                if (stripos($comp->nama, 'Potongan Lain') !== false || stripos($comp->nama, 'Potongan Lainnya') !== false) {
                    $comp->nilai = $row['potongan_lain'];
                    $hasPotonganLainInComponents = true;
                }

                $components[] = $comp;
            }

            if (!$hasBasicSalaryInComponents) {
                $compObj = new \stdClass();
                $compObj->nama = 'Gaji Pokok';
                $compObj->tipe = 'pendapatan';
                $compObj->nilai = $row['gaji_pokok'];
                $compObj->is_persentase = 0;
                $compObj->jenis_komponen = 'basic_salary';
                $components[] = $compObj;
            }

            if (!$hasRapelInComponents && $row['rapel'] > 0) {
                $compObj = new \stdClass();
                $compObj->nama = 'Rapel Gaji (Manual Upload)';
                $compObj->tipe = 'pendapatan';
                $compObj->nilai = $row['rapel'];
                $compObj->is_persentase = 0;
                $compObj->jenis_komponen = 'kompensasi';
                $compObj->sifat_kompensasi = 'tidak_tetap';
                $compObj->allowance_type = 'Ad-hoc';
                $components[] = $compObj;
            }

            if (!$hasPotonganLainInComponents && $row['potongan_lain'] > 0) {
                $compObj = new \stdClass();
                $compObj->nama = 'Potongan Lain (Manual Upload)';
                $compObj->tipe = 'potongan';
                $compObj->nilai = $row['potongan_lain'];
                $compObj->is_persentase = 0;
                $compObj->jenis_komponen = 'scheme_template';
                $components[] = $compObj;
            }

            // Calculate wage bases
            $bpjsWageBase = 0;
            $pphWageBase = 0;
            $totalPendapatan = 0;
            $totalPotongan = 0;

            foreach ($components as $comp) {
                $isBasic = (isset($comp->jenis_komponen) && $comp->jenis_komponen === 'basic_salary') || (stripos($comp->nama, 'Gaji Pokok') !== false);
                $nilai = 0;

                if ($isBasic) {
                    $nilai = $row['gaji_pokok'];
                } else {
                    if (($comp->jenis_komponen ?? '') === 'kompensasi' && ($comp->sifat_kompensasi ?? '') === 'tetap') {
                        $nilai = floatval($comp->nilai);
                    } else if (isset($comp->jenis_komponen) && !empty($comp->jenis_komponen)) {
                        $base_nilai = floatval($comp->nilai);
                        if (isset($comp->sumber_nilai)) {
                            if ($comp->sumber_nilai === 'ump') {
                                $base_nilai = $umpWageValue * ($base_nilai / 100);
                            } else if ($comp->sumber_nilai === 'umk') {
                                $base_nilai = $umkWageValue * ($base_nilai / 100);
                            } else if ($comp->sumber_nilai === 'ump_umk') {
                                $base_nilai = $minimumWage * ($base_nilai / 100);
                            }
                        }
                        // Scale by period
                        $periode = $comp->periode ?? 'bulan';
                        if ($periode === 'hari' || $periode === 'hari_kerja') {
                            $nilai = $base_nilai * $row['working_days'];
                        } elseif ($periode === 'minggu') {
                            $nilai = $base_nilai * 4;
                        } elseif ($periode === 'tahun') {
                            $nilai = $base_nilai / 12;
                        } else {
                            $nilai = $base_nilai;
                        }
                    } else {
                        $nilai = floatval($comp->nilai);
                        if (intval($comp->is_persentase) === 1 || $comp->is_persentase === true) {
                            $nilai = $row['gaji_pokok'] * ($nilai / 100);
                        }
                    }
                }

                $comp->nilai = $nilai;

                if ($comp->tipe === 'pendapatan') {
                    $totalPendapatan += $nilai;

                    $isBpjsInc = false;
                    $isPphInc = true;
                    if ($isBasic) {
                        $isBpjsInc = true;
                        $isPphInc = true;
                    } else {
                        $isBpjsInc = (isset($comp->is_bpjs) && $comp->is_bpjs == 1);
                        $isPphInc = (!isset($comp->is_pph21) || $comp->is_pph21 == 1);
                    }

                    if ($isBpjsInc) {
                        $bpjsWageBase += $nilai;
                    }
                    if ($isPphInc) {
                        $pphWageBase += $nilai;
                    }
                } else {
                    $totalPotongan += $nilai;
                }
            }

            // Calculate overtime pay and early arrival pay automatically
            $overtimePay = 0.0;
            $earlyArrivalPay = 0.0;

            // 1. Tentukan rate lembur per jam
            $otDivisor = ($clientConfig && isset($clientConfig->overtime_divisor) && intval($clientConfig->overtime_divisor) > 0) ? intval($clientConfig->overtime_divisor) : 173;
            $clientManualOtRate = ($clientConfig && isset($clientConfig->overtime_rate_per_hour)) ? floatval($clientConfig->overtime_rate_per_hour) : 0;
            
            // Get scheme template
            $schemeModel = new \App\Models\PayrollSchemeTemplateModel();
            $schemeTemplateObj = $schemeModel->getSchemeForEmployee(
                $pkwt->client_id,
                $emp->division_id ?? null,
                $emp->department_id ?? null,
                $emp->position_id ?? null
            );
            $schemeTemplate = $schemeTemplateObj ? (array)$schemeTemplateObj : null;
            $schemeOtRate = ($schemeTemplate && isset($schemeTemplate['rate_lembur_per_jam'])) ? floatval($schemeTemplate['rate_lembur_per_jam']) : 0;

            $upahSejamLembur = ($clientManualOtRate > 0) ? $clientManualOtRate : (($schemeOtRate > 0) ? $schemeOtRate : (($row['gaji_pokok'] > 0) ? ($row['gaji_pokok'] / $otDivisor) : 0));

            // Tentukan working days per week
            $workDaysPerWeek = 5;
            if ($emp) {
                $posHkOt = intval($emp->position_hari_kerja ?? 5);
                if ($posHkOt == 6) $workDaysPerWeek = 6;
            }

            // Hitung konversi jam lembur
            $jamKonversiBiasa = $this->hitungJamLemburKonversi(floatval($row['overtime_hours']), false, $workDaysPerWeek, false);
            $overtimePay = $jamKonversiBiasa * $upahSejamLembur;

            // 2. Hitung early arrival pay
            $companySetting = $this->db->table('company_payroll_setting')->get()->getRowArray();
            $minMinutes = isset($companySetting['early_arrival_min_minutes']) ? intval($companySetting['early_arrival_min_minutes']) : 30;
            $calcUnit = isset($companySetting['early_arrival_calculation_unit']) ? intval($companySetting['early_arrival_calculation_unit']) : 60;
            $roundingMethod = $companySetting['early_arrival_rounding_method'] ?? 'CEILING';
            $maxMinutes = isset($companySetting['max_early_arrival_minutes']) ? intval($companySetting['max_early_arrival_minutes']) : 180;
            $earlyArrivalEnabled = isset($companySetting['early_arrival_enabled']) ? intval($companySetting['early_arrival_enabled']) : 1;

            $earlyArrivalMinutes = intval(floatval($row['early_arrival_hours']) * 60);

            if ($earlyArrivalEnabled && $earlyArrivalMinutes >= $minMinutes) {
                $jamEA = ($roundingMethod === 'CEILING') ? ceil($earlyArrivalMinutes / $calcUnit) : ($earlyArrivalMinutes / $calcUnit);
                
                // Batasi max_early_arrival_minutes
                $maxHours = $maxMinutes / $calcUnit;
                if ($jamEA > $maxHours) {
                    $jamEA = $maxHours;
                }
                
                // Hitung bobot jam Kemnaker
                $totalEarlyArrivalBobotHours = 0;
                if ($jamEA >= 1) {
                    $totalEarlyArrivalBobotHours = 1.5 + ($jamEA - 1) * 2.0;
                }

                // Hitung totalTunjanganTetap
                $totalTunjanganTetap = 0;
                foreach ($components as $comp) {
                    if (($comp->tipe ?? '') === 'pendapatan') {
                        $isBasic = (isset($comp->jenis_komponen) && $comp->jenis_komponen === 'basic_salary') || (stripos($comp->nama, 'Gaji Pokok') !== false);
                        $isTetap = (isset($comp->sifat_kompensasi) && strtolower($comp->sifat_kompensasi) === 'tetap');
                        $isBulanan = (empty($comp->periode) || strtolower($comp->periode) === 'bulan');
                        
                        if ($isTetap && $isBulanan && !$isBasic) {
                            $totalTunjanganTetap += floatval($comp->nilai);
                        }
                    }
                }

                if ($schemeTemplate) {
                    $totalTunjanganTetap += floatval($schemeTemplate['tunjangan_transport'] ?? 0);
                    $totalTunjanganTetap += floatval($schemeTemplate['tunjangan_komunikasi'] ?? 0);
                    $totalTunjanganTetap += floatval($schemeTemplate['tunjangan_jabatan'] ?? 0);
                    $totalTunjanganTetap += floatval($schemeTemplate['tunjangan_kehadiran'] ?? 0);
                    $totalTunjanganTetap += floatval($schemeTemplate['tunjangan_kinerja'] ?? 0);
                }

                $eaDivisor = ($clientConfig && isset($clientConfig->overtime_divisor) && intval($clientConfig->overtime_divisor) > 0) ? intval($clientConfig->overtime_divisor) : 173.0;
                $eaUpahPerJam = ($row['gaji_pokok'] + $totalTunjanganTetap) / $eaDivisor;
                $earlyArrivalPay = $totalEarlyArrivalBobotHours * $eaUpahPerJam;
            }

            // Include calculated Overtime Pay & Early Arrival Pay to total income and PPh wage base
            $totalPendapatan += $overtimePay + $earlyArrivalPay;
            $pphWageBaseFinal = $pphWageBase + $overtimePay + $earlyArrivalPay - $row['potongan_absen'];

            // Cap/Floor BPJS base
            if ($minimumWage > 0 && $bpjsWageBase < $minimumWage) {
                $bpjsWageBase = $minimumWage;
            }

            // Resolve Schemes and PTKP
            $taxScheme = null;
            if ($clientConfig && $clientConfig->tax_scheme_id) {
                $taxScheme = $this->db->table('tax_schemes')->where('id', $clientConfig->tax_scheme_id)->get()->getRow();
            }
            $bpjsScheme = null;
            if ($clientConfig && !empty($clientConfig->bpjs_scheme_id)) {
                $bpjsScheme = $this->db->table('tax_schemes')->where('id', $clientConfig->bpjs_scheme_id)->get()->getRow();
            }
            if (!$bpjsScheme) {
                $bpjsScheme = $taxScheme;
            }
            $ptkpStatus = 'TK/0';
            if ($emp && !empty($emp->ptkp)) {
                $ptkpStatus = $emp->ptkp;
            }

            // Recalculate BPJS and Tax
            $calc = $this->calculateBpjsAndTax($row['gaji_pokok'], $bpjsWageBase, $pphWageBaseFinal, null, $taxScheme, $minimumWage, $ptkpStatus, $bpjsScheme, intval($period->bulan), intval($period->tahun), $pkwtId);

            // Compute net take home pay
            $employeeBpjsDeductions = $calc['bpjs_kes_karyawan'] + $calc['bpjs_jht_karyawan'] + $calc['bpjs_jp_karyawan'];
            $totalPotongan += $row['potongan_absen'] + $row['potongan_lain'];

            if ($calc['metode_pajak'] === 'Gross Up') {
                $totalPendapatan += $calc['tax_allowance'];
                $totalPotongan += $employeeBpjsDeductions + $calc['pph21'];
            } elseif ($calc['metode_pajak'] === 'Gross') {
                $totalPotongan += $employeeBpjsDeductions + $calc['pph21'];
            } else { // Nett
                $totalPotongan += $employeeBpjsDeductions;
            }

            $thp = $totalPendapatan - $totalPotongan;

            // Save to payroll_final
            $existingFinal = $this->db->table('payroll_final')
                                      ->where('period_id', $periodId)
                                      ->where('pkwt_id', $pkwtId)
                                      ->get()->getRow();

            $finalData = [
                'period_id' => $periodId,
                'pkwt_id' => $pkwtId,
                'total_pendapatan' => $totalPendapatan,
                'total_potongan' => $totalPotongan,
                'take_home_pay' => $thp,
                'status_approval' => 'Pending',
                'bpjs_kes_karyawan' => $calc['bpjs_kes_karyawan'],
                'bpjs_kes_perusahaan' => $calc['bpjs_kes_perusahaan'],
                'bpjs_jht_karyawan' => $calc['bpjs_jht_karyawan'],
                'bpjs_jht_perusahaan' => $calc['bpjs_jht_perusahaan'],
                'bpjs_jp_karyawan' => $calc['bpjs_jp_karyawan'],
                'bpjs_jp_perusahaan' => $calc['bpjs_jp_perusahaan'],
                'bpjs_jkk_perusahaan' => $calc['bpjs_jkk_perusahaan'],
                'bpjs_jkm_perusahaan' => $calc['bpjs_jkm_perusahaan'],
                'pph21' => $calc['pph21'],
                'tax_allowance' => $calc['tax_allowance'],
                'tax_method' => $calc['metode_pajak'],
                'ptkp_status' => $ptkpStatus,
                'gaji_pokok' => $row['gaji_pokok'],
                'potongan_absen' => $row['potongan_absen'],
                'jam_lembur' => $row['overtime_hours'],
                'jam_lembur_biasa' => $row['overtime_hours'],
                'jam_lembur_libur' => 0,
                'lembur_pay' => $overtimePay,
                'bonus_tambahan' => $row['bonus_tambahan'],
                'early_arrival_minutes' => $earlyArrivalMinutes,
                'early_arrival_pay' => $earlyArrivalPay,
                'raw_components' => json_encode($components)
            ];

            if ($existingFinal) {
                $this->db->table('payroll_final')->where('id', $existingFinal->id)->update($finalData);
            } else {
                $this->db->table('payroll_final')->insert($finalData);
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->fail('Gagal menyimpan atau memproses data upload gaji.');
        }

        $username = $this->request->getHeaderLine('X-User-Action') ?: 'Admin';
        $periodName = $period ? $period->nama : "ID: $periodId";
        $this->logActivity("Meng-upload gaji manual dari Excel untuk periode: " . $periodName . " oleh " . $username);

        return $this->respond(['status' => 'success', 'message' => 'Gaji manual berhasil di-upload dan dikalkulasi otomatis.']);
    }

    public function getSlipDetails($id)
    {
        // Get Final Result
        $final = $this->db->table('payroll_final')
                          ->select('payroll_final.*, pkwt.employee_name, pkwt.position_name, pkwt.client_id, payroll_periods.bulan, payroll_periods.tahun, clients.nama as client_name, employees.employ_id')
                          ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                          ->join('payroll_periods', 'payroll_periods.id = payroll_final.period_id')
                          ->join('clients', 'clients.id = pkwt.client_id')
                          ->join('employees', 'employees.nama = pkwt.employee_name AND employees.client_id = pkwt.client_id', 'left')
                          ->where('payroll_final.id', $id)
                          ->get()
                          ->getRowArray();
        
        if (!$final) return $this->failNotFound('Data tidak ditemukan');

        $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        $final['period_name'] = ($months[intval($final['bulan'] ?? 1) - 1] ?? '') . " " . ($final['tahun'] ?? '');

        // Load active period to calculate calendar days
        $period = $this->db->table('payroll_periods')->where('id', $final['period_id'])->get()->getRow();
        $period = $this->formatPeriodRow($period);
        $daysInMonth = $period ? date('t', mktime(0, 0, 0, intval($period->bulan), 1, intval($period->tahun))) : 30;

        // Get client config to find payroll scheme ID
        $clientConfig = $this->resolveClientConfig($final['client_id'], $final['position_name'] ?? null);
        
        $isProrate = false;
        $isAbsenTidakPotong = false;
        $nominalPotonganAbsen = 0;
        $payrollScheme = null;
        
        // Try resolving from payroll scheme first
        if ($clientConfig && $clientConfig->payroll_scheme_id) {
            $payrollScheme = $this->db->table('payroll_schemes')->where('id', $clientConfig->payroll_scheme_id)->get()->getRow();
            if ($payrollScheme) {
                $isProrate = ($payrollScheme->prorate == 1);
                $isAbsenTidakPotong = ($payrollScheme->absen_tidak_potong == 1);
                $nominalPotonganAbsen = floatval($payrollScheme->nominal_potongan);
            }
        }
        
        // Fallback to client_absence_configs
        $absenceConfig = $this->db->table('client_absence_configs')->where('client_id', $final['client_id'])->get()->getRow();
        if ($clientConfig && !$isProrate && !$isAbsenTidakPotong && $nominalPotonganAbsen == 0) {
            if ($absenceConfig) {
                $isProrate = ($absenceConfig->prorate == 1);
                $isAbsenTidakPotong = ($absenceConfig->absen_tidak_potong == 1);
                $nominalPotonganAbsen = floatval($absenceConfig->nominal_potongan);
            }
        }

        // Get Client's overtime_rate_per_hour
        $client = $this->db->table('clients')->where('id', $final['client_id'])->get()->getRow();
        $otRate = ($client && isset($client->overtime_rate_per_hour) && floatval($client->overtime_rate_per_hour) > 0)
                  ? floatval($client->overtime_rate_per_hour)
                  : 20000;

        // Get Fixed Components
        $fixed = [];
        if (!empty($final['raw_components'])) {
            $fixed = json_decode($final['raw_components'], true);
        } else {
            $fixed = $this->db->table('pkwt_components')
                              ->where('pkwt_id', $final['pkwt_id'])
                              ->get()
                              ->getResultArray();
        }
        
        // Get Variable Components (Attendance)
        $att = $this->db->table('payroll_attendance')
                        ->where('period_id', $final['period_id'])
                        ->where('pkwt_id', $final['pkwt_id'])
                        ->get()
                        ->getRowArray();

        // 1. Resolve Employee / Client UMR (Minimum Wage)
        $emp = $this->db->table('employees')
                        ->select('employees.*, minimum_wages.nominal as umr_nominal, minimum_wages.id as mw_id, positions.hari_kerja as position_hari_kerja')
                        ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                        ->join('positions', 'positions.id = employees.position_id', 'left')
                        ->where('employees.nama', $final['employee_name'])
                        ->where('employees.client_id', $final['client_id'])
                        ->get()
                        ->getRow();

        $workDaysConfig = 5;
        if ($emp) {
            if (isset($emp->hari_kerja) && intval($emp->hari_kerja) > 0) {
                $workDaysConfig = intval($emp->hari_kerja);
            } elseif (isset($emp->position_hari_kerja) && intval($emp->position_hari_kerja) > 0) {
                $workDaysConfig = intval($emp->position_hari_kerja);
            }
        }
        $prevMonth = intval($period->bulan) - 1;
        $prevYear = intval($period->tahun);
        if ($prevMonth == 0) {
            $prevMonth = 12;
            $prevYear--;
        }
        $startDateStr = sprintf('%04d-%02d-01', $prevYear, $prevMonth);
        $endDateStr = date('Y-m-t', strtotime($startDateStr));
        
        $systemStandardDays = ($clientConfig && isset($clientConfig->standard_work_days) && intval($clientConfig->standard_work_days) > 0)
            ? intval($clientConfig->standard_work_days)
            : $this->getStandardWorkingDaysInRange($startDateStr, $endDateStr, $workDaysConfig);

        $stdWorkingDays = ($emp && isset($emp->custom_standard_days) && intval($emp->custom_standard_days) > 0)
            ? intval($emp->custom_standard_days)
            : $systemStandardDays;
        
        if ($emp && !empty($emp->tgl_masuk)) {
            $joinDateStr = date('Y-m-d', strtotime($emp->tgl_masuk));
            if ($joinDateStr >= $startDateStr && $joinDateStr <= $endDateStr) {
                $startDateStr = $joinDateStr;
            }
        }

        $isHoldNewHire = false;
        $holdActualDaysWorked = 0;
        $holdStdWorkingDays = $stdWorkingDays;

        if (($final['status_approval'] ?? '') === 'Hold' && $emp && !empty($emp->tgl_masuk)) {
            $joinTs = strtotime($emp->tgl_masuk);
            $joinYear = intval(date('Y', $joinTs));
            $joinMonth = intval(date('n', $joinTs));
            
            $tYear = intval($final['tahun']);
            $tMonth = intval($final['bulan']);
            
            if ($joinYear === $tYear && $joinMonth === $tMonth) {
                $isHoldNewHire = true;
                
                $currMonthStart = sprintf('%04d-%02d-01', $tYear, $tMonth);
                $currMonthEnd = date('Y-m-t', strtotime($currMonthStart));
                $holdStdWorkingDays = $this->getStandardWorkingDaysInRange($currMonthStart, $currMonthEnd, $workDaysConfig);
                
                $joinDateStr = date('Y-m-d', $joinTs);
                $hasAnyLogsPrev = $this->db->table('attendance_logs')
                                           ->where('employee_id', $emp->id)
                                           ->where('log_date >=', $joinDateStr)
                                           ->where('log_date <=', $currMonthEnd)
                                           ->countAllResults() > 0;
                
                if ($hasAnyLogsPrev) {
                    $holdActualDaysWorked = $this->db->table('attendance_logs')
                                                 ->where('employee_id', $emp->id)
                                                 ->where('log_date >=', $joinDateStr)
                                                 ->where('log_date <=', $currMonthEnd)
                                                 ->where('status', 'Hadir')
                                                 ->countAllResults();
                } else {
                    $holdActualDaysWorked = $this->getStandardWorkingDaysInRange($joinDateStr, $currMonthEnd, $workDaysConfig);
                }
            }
        }
        
        // Resolve Scheme Template and Overtime Rate
        $schemeModel = new \App\Models\PayrollSchemeTemplateModel();
        $schemeTemplateObj = $schemeModel->getSchemeForEmployee(
            $final['client_id'],
            $emp->division_id ?? null,
            $emp->department_id ?? null,
            $emp->position_id ?? null
        );
        $schemeTemplate = $schemeTemplateObj ? (array)$schemeTemplateObj : null;
        $schemeOtRate = ($schemeTemplate && isset($schemeTemplate['rate_lembur_per_jam'])) ? floatval($schemeTemplate['rate_lembur_per_jam']) : 0;
        
        $minimumWage = 0;
        $mwId = null;
        if ($emp && isset($emp->umr_nominal) && $emp->umr_nominal > 0) {
            $minimumWage = floatval($emp->umr_nominal);
            $mwId = $emp->mw_id;
        } else {
            // Fallback to client config's minimum wage
            if ($clientConfig && isset($clientConfig->minimum_wage_id)) {
                $mw = $this->db->table('minimum_wages')->where('id', $clientConfig->minimum_wage_id)->get()->getRow();
                if ($mw) {
                    $minimumWage = floatval($mw->nominal);
                    $mwId = $mw->id;
                }
            }
        }

        // Resolve UMP and UMK values - pass employee's work location province for UMP lookup
        $empProvince = null;
        if ($emp && !empty($emp->work_location_id)) {
            $wl = $this->db->table('work_locations')->where('id', $emp->work_location_id)->get()->getRow();
            if ($wl && !empty($wl->provinsi)) {
                $empProvince = $wl->provinsi;
            }
        }
        $resolvedWages = $this->resolveUmpUmk($mwId, null, $empProvince);
        $umpWageValue = $resolvedWages['ump'];
        $umkWageValue = $resolvedWages['umk'];

        // Check if we have a basic salary component with source = kompensasi
        $hasKompensasiSource = false;
        foreach ($fixed as $comp) {
            $isBasic = (isset($comp['jenis_komponen']) && $comp['jenis_komponen'] === 'basic_salary') || (stripos($comp['nama'], 'Gaji Pokok') !== false);
            if ($isBasic && isset($comp['sumber_nilai']) && $comp['sumber_nilai'] === 'kompensasi') {
                $hasKompensasiSource = true;
                break;
            }
        }

        // 2. First pass: Find and calculate Gaji Pokok (Basic Salary)
        $gajiPokok = 0;
        $unproratedGajiPokok = 0;
        foreach ($fixed as $comp) {
            $isBasicSalary = false;
            if (isset($comp['jenis_komponen']) && $comp['jenis_komponen'] === 'basic_salary') {
                $isBasicSalary = true;
            } elseif (stripos($comp['nama'], 'Gaji Pokok') !== false) {
                $isBasicSalary = true;
            }

            if ($isBasicSalary) {
                $base_nilai = floatval($comp['nilai']);
                $sumber_nilai = $comp['sumber_nilai'] ?? 'nominal';
                
                // Force check against actual client scheme if available
                if (isset($clientConfig) && $clientConfig->payroll_scheme_id) {
                    $schemeComp = $this->db->table('payroll_components')
                        ->where('scheme_id', $clientConfig->payroll_scheme_id)
                        ->where('jenis_komponen', 'basic_salary')
                        ->get()->getRow();
                    if ($schemeComp) {
                        $sumber_nilai = $schemeComp->sumber_nilai ?? 'nominal';
                        $base_nilai = floatval($schemeComp->nilai);
                    }
                }
                
                if ($sumber_nilai === 'ump') {
                    $base_nilai = $umpWageValue * ($base_nilai / 100);
                } else if ($sumber_nilai === 'umk') {
                    $base_nilai = $umkWageValue * ($base_nilai / 100);
                } else if ($sumber_nilai === 'ump_umk') {
                    $base_nilai = $minimumWage * ($base_nilai / 100);
                } else if ($sumber_nilai === 'kompensasi') {
                    $kompTetapValue = 0;
                    foreach ($fixed as $c) {
                        if (($c['jenis_komponen'] ?? '') === 'kompensasi' && ($c['sifat_kompensasi'] ?? '') === 'tetap') {
                            $kompTetapValue += floatval($c['nilai']);
                        }
                    }
                    $base_nilai = $kompTetapValue * ($base_nilai / 100);
                } else {
                    // Force base_nilai to use Employee's setup if available!
                    if ($emp && isset($emp->gaji_pokok) && floatval($emp->gaji_pokok) > 0) {
                        $base_nilai = floatval($emp->gaji_pokok);
                    } else if ($minimumWage > 0) {
                        $base_nilai = $minimumWage;
                    }
                }

                $unproratedGajiPokok = $base_nilai;

                // Scale by period
                if (isset($comp['periode'])) {
                    if ($comp['periode'] === 'hari' || $comp['periode'] === 'hari_kerja') {
                        $days = ($att && isset($att['hari_kerja'])) ? intval($att['hari_kerja']) : 0;
                        $base_nilai = $base_nilai * $days;
                    } elseif ($comp['periode'] === 'minggu') {
                        $base_nilai = $base_nilai * 4;
                    } elseif ($comp['periode'] === 'tahun') {
                        $base_nilai = $base_nilai / 12;
                    }
                }
                
                $gajiPokok = $base_nilai;
                break; // Assume only one basic salary component
            }
        }

        if ($gajiPokok <= 0 && $emp && isset($emp->gaji_pokok)) {
            $unproratedGajiPokok = floatval($emp->gaji_pokok);
            $gajiPokok = $unproratedGajiPokok;
        }

        $earnings = [];
        $deductions = [];

        // 3. Calculate components
        foreach ($fixed as $comp) {
            $isCompAdhoc = isset($comp['allowance_type']) && $comp['allowance_type'] === 'Ad-hoc';
            if ($isCompAdhoc) {
                $payoutPeriod = trim($comp['payout_period'] ?? '');
                $currentPeriod1 = intval($final['bulan']) . '-' . intval($final['tahun']);
                $currentPeriod2 = sprintf('%02d-%d', intval($final['bulan']), intval($final['tahun']));
                if ($payoutPeriod !== $currentPeriod1 && $payoutPeriod !== $currentPeriod2) {
                    continue; // Skip this component
                }
            }
            $isBasic = false;
            if (isset($comp['jenis_komponen']) && $comp['jenis_komponen'] === 'basic_salary') {
                $isBasic = true;
            } elseif (stripos($comp['nama'], 'Gaji Pokok') !== false) {
                $isBasic = true;
            }

            $nilai = 0;

            if ($isBasic) {
                $nilai = isset($final['gaji_pokok']) ? floatval($final['gaji_pokok']) : $gajiPokok;
            } else {
                if ($hasKompensasiSource && ($comp['jenis_komponen'] ?? '') === 'kompensasi' && ($comp['sifat_kompensasi'] ?? '') === 'tetap') {
                    $nilai = 0; // Prevent double-counting by setting it to 0
                } else if (isset($comp['jenis_komponen']) && !empty($comp['jenis_komponen'])) {
                    // New Master Compensation component logic
                    $base_nilai = floatval($comp['nilai']);
                    
                    if (isset($comp['sumber_nilai'])) {
                        if ($comp['sumber_nilai'] === 'ump') {
                            $base_nilai = $umpWageValue * ($base_nilai / 100);
                        } else if ($comp['sumber_nilai'] === 'umk') {
                            $base_nilai = $umkWageValue * ($base_nilai / 100);
                        } else if ($comp['sumber_nilai'] === 'ump_umk') {
                            $base_nilai = $minimumWage * ($base_nilai / 100);
                        }
                    }
                    
                    // Scale by period
                    if ($comp['periode'] === 'hari' || $comp['periode'] === 'hari_kerja') {
                        $days = ($att && isset($att['hari_kerja'])) ? intval($att['hari_kerja']) : 0;
                        if ($isHoldNewHire) {
                            $days = $holdActualDaysWorked;
                        }
                        $nilai = $base_nilai * $days;
                    } elseif ($comp['periode'] === 'minggu') {
                        $nilai = $base_nilai * 4;
                        if ($isHoldNewHire) {
                            $nilai = $nilai * ($holdStdWorkingDays > 0 ? $holdActualDaysWorked / $holdStdWorkingDays : 0);
                        }
                    } elseif ($comp['periode'] === 'tahun') {
                        $nilai = $base_nilai / 12;
                        if ($isHoldNewHire) {
                            $nilai = $nilai * ($holdStdWorkingDays > 0 ? $holdActualDaysWorked / $holdStdWorkingDays : 0);
                        }
                    } else {
                        // bulanan
                        // Tunjangan tetap: Nilai tunjangan tetap bersifat konstan setiap periode (TIDAK terprorate)
                        $isCompAdhoc = isset($comp['allowance_type']) && $comp['allowance_type'] === 'Ad-hoc';
                        if ($isProrate && isset($comp['sifat_kompensasi']) && $comp['sifat_kompensasi'] === 'tidak_tetap' && !$isCompAdhoc) {
                            $days = ($att && isset($att['hari_kerja'])) ? intval($att['hari_kerja']) : 0;
                            $stdDays = $stdWorkingDays;
                            if ($isHoldNewHire) {
                                $days = $holdActualDaysWorked;
                                $stdDays = $holdStdWorkingDays;
                            }
                            $nilai = $base_nilai * ($days / $stdDays);
                        } else {
                            if ($isHoldNewHire && !$isCompAdhoc) {
                                $nilai = $base_nilai * ($holdStdWorkingDays > 0 ? $holdActualDaysWorked / $holdStdWorkingDays : 0);
                            } else {
                                $nilai = $base_nilai;
                            }
                        }
                    }
                } else {
                    // Legacy component logic
                    $nilai = floatval($comp['nilai']);
                    if (intval($comp['is_persentase']) === 1 || $comp['is_persentase'] === true) {
                        $nilai = (isset($final['gaji_pokok']) ? floatval($final['gaji_pokok']) : $gajiPokok) * ($nilai / 100);
                    } else {
                        if ($isHoldNewHire) {
                            $nilai = $nilai * ($holdStdWorkingDays > 0 ? $holdActualDaysWorked / $holdStdWorkingDays : 0);
                        }
                    }
                }
            }

            if ($nilai > 0) {
                if ($comp['tipe'] === 'pendapatan') {
                    $earnings[] = ['nama' => $comp['nama'], 'nilai' => $nilai];
                } else {
                    $deductions[] = ['nama' => $comp['nama'], 'nilai' => $nilai];
                }
            }
        }

        if ($att) {
            // === Overtime Breakdown in Salary Slip ===
            $jamLemburBiasa = floatval($final['jam_lembur_biasa'] ?? 0);
            $jamLemburLibur = floatval($final['jam_lembur_libur'] ?? 0);
            $lemburPayTotal = floatval($final['lembur_pay'] ?? 0);

            $overtimeType = ($payrollScheme && !empty($payrollScheme->overtime_type)) ? $payrollScheme->overtime_type : 'standard';

            if ($overtimeType === 'lumpsum' && $lemburPayTotal > 0) {
                $lumpsumSubtype = $payrollScheme->lumpsum_subtype ?? 'per_jam';
                if ($lumpsumSubtype === 'per_jam') {
                    $totalJam = $jamLemburBiasa + $jamLemburLibur;
                    $earnings[] = [
                        'nama' => 'Lembur Lumpsum Per Jam (' . $totalJam . ' jam)',
                        'nilai' => $lemburPayTotal
                    ];
                } elseif ($lumpsumSubtype === 'harian') {
                    $numDaysLembur = 0;
                    if ($emp && isset($emp->id)) {
                        $cutoffStart = $clientConfig ? intval($clientConfig->cutoff_start) : 21;
                        $cutoffEnd = $clientConfig ? intval($clientConfig->cutoff_end) : 20;
                        if ($clientConfig) {
                            $startField = "cutoff_lembur_start";
                            $endField = "cutoff_lembur_end";
                            $refField = "cutoff_lembur_schedule_ref";
                            
                            $start = isset($clientConfig->$startField) ? intval($clientConfig->$startField) : null;
                            $end = isset($clientConfig->$endField) ? intval($clientConfig->$endField) : null;
                            $refId = isset($clientConfig->$refField) ? intval($clientConfig->$refField) : null;
                            
                            if ($refId) {
                                $sched = $this->db->table('payroll_schedules')->where('id', $refId)->get()->getRow();
                                if ($sched) {
                                    $start = intval($sched->cutoff_start);
                                    $end = intval($sched->cutoff_end);
                                }
                            }
                            
                            if ($start === null) {
                                if (isset($clientConfig->cutoff_start)) {
                                    $start = intval($clientConfig->cutoff_start);
                                } else {
                                    $start = 21;
                                }
                            }
                            if ($end === null) {
                                if (isset($clientConfig->cutoff_end)) {
                                    $end = intval($clientConfig->cutoff_end);
                                } else {
                                    $end = $start - 1;
                                    if ($end < 1) $end = 31;
                                }
                            }
                            
                            $cutoffStart = $start;
                            $cutoffEnd = $end;
                        }

                        if ($cutoffStart <= 0) $cutoffStart = 1;
                        if ($cutoffEnd <= 0) {
                            $cutoffEnd = $cutoffStart - 1;
                            if ($cutoffEnd < 1) $cutoffEnd = 31;
                        }

                        $bulan_start = intval($period->bulan);
                        $tahun_start = intval($period->tahun);
                        $bulan_end = $bulan_start;
                        $tahun_end = $tahun_start;
                        if ($cutoffStart > $cutoffEnd && $cutoffStart > 1) {
                            $bulan_start -= 1;
                            if ($bulan_start < 1) {
                                $bulan_start = 12;
                                $tahun_start -= 1;
                            }
                        }
                        $cutoffStartDateStr = sprintf('%04d-%02d-%02d', $tahun_start, $bulan_start, $cutoffStart);
                        $cutoffEndDateStr = sprintf('%04d-%02d-%02d', $tahun_end, $bulan_end, $cutoffEnd);

                        // Resolve calendar month range
                        $calPrevMonth = intval($period->bulan) - 1;
                        $calPrevYear = intval($period->tahun);
                        if ($calPrevMonth == 0) {
                            $calPrevMonth = 12;
                            $calPrevYear--;
                        }
                        $calStartDateStr = sprintf('%04d-%02d-01', $calPrevYear, $calPrevMonth);
                        $calEndDateStr = date('Y-m-t', strtotime($calStartDateStr));
                        $calEffectiveStartDateStr = !empty($emp->tgl_masuk) ? max($calStartDateStr, date('Y-m-d', strtotime($emp->tgl_masuk))) : $calStartDateStr;

                        $otLogsSlip = $this->db->table('overtime_logs')
                            ->select('tanggal')
                            ->where('employee_id', $emp->id)
                            ->where('tanggal >=', $calEffectiveStartDateStr)
                            ->where('tanggal <=', $calEndDateStr)
                            ->where('status', 'Approved')
                            ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                            ->get()
                            ->getResultArray();
                        
                        $distinctDaysSlip = [];
                        foreach ($otLogsSlip as $otLog) {
                            $distinctDaysSlip[$otLog['tanggal']] = true;
                        }
                        $numDaysLembur = count($distinctDaysSlip);
                    }
                    $earnings[] = [
                        'nama' => 'Lembur Lumpsum Harian (' . $numDaysLembur . ' hari)',
                        'nilai' => $lemburPayTotal
                    ];
                } elseif ($lumpsumSubtype === 'bulanan') {
                    $earnings[] = [
                        'nama' => 'Lembur Lumpsum Bulanan',
                        'nilai' => $lemburPayTotal
                    ];
                }
            } else if ($lemburPayTotal > 0 && ($jamLemburBiasa > 0 || $jamLemburLibur > 0)) {
                // We have breakdown data — show separate lines
                // Determine working days per week for conversion calc
                $wdPerWeekSlip = 5;
                if ($emp && isset($emp->position_hari_kerja) && intval($emp->position_hari_kerja) == 6) {
                    $wdPerWeekSlip = 6;
                }

                // Calculate upah sejam for proportional split
                $clientManualOtRateSlip = ($client && isset($client->overtime_rate_per_hour)) ? floatval($client->overtime_rate_per_hour) : 0;
                $unproratedGpSlip = floatval($final['gaji_pokok'] ?? 0);
                $otDivisorSlip = ($clientConfig && isset($clientConfig->overtime_divisor) && intval($clientConfig->overtime_divisor) > 0) ? intval($clientConfig->overtime_divisor) : 173;
                $upahSejamSlip = ($clientManualOtRateSlip > 0) ? $clientManualOtRateSlip : (($schemeOtRate > 0) ? $schemeOtRate : (($unproratedGpSlip > 0) ? ($unproratedGpSlip / $otDivisorSlip) : 0));

                // Resolve calendar month range
                $calPrevMonth = intval($period->bulan) - 1;
                $calPrevYear = intval($period->tahun);
                if ($calPrevMonth == 0) {
                    $calPrevMonth = 12;
                    $calPrevYear--;
                }
                $calStartDateStr = sprintf('%04d-%02d-01', $calPrevYear, $calPrevMonth);
                $calEndDateStr = date('Y-m-t', strtotime($calStartDateStr));
                $calEffectiveStartDateStr = !empty($emp->tgl_masuk) ? max($calStartDateStr, date('Y-m-d', strtotime($emp->tgl_masuk))) : $calStartDateStr;

                $otLogsQuery = [];
                if ($emp && isset($emp->id)) {
                    $otLogsQuery = $this->db->table('overtime_logs')
                        ->where('employee_id', $emp->id)
                        ->where('tanggal >=', $calEffectiveStartDateStr)
                        ->where('tanggal <=', $calEndDateStr)
                        ->where('status', 'Approved')
                        ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                        ->get()
                        ->getResultArray();
                }

                if (!empty($otLogsQuery)) {
                    $konversiBiasa = 0.0;
                    $konversiLibur = 0.0;
                    foreach ($otLogsQuery as $otLog) {
                        $otHours = floatval($otLog['jam_lembur']);
                        $isOtHoliday = isset($otLog['is_holiday']) && intval($otLog['is_holiday']) === 1;
                        if ($isOtHoliday) {
                            $konversiLibur += $this->hitungJamLemburKonversi($otHours, true, $wdPerWeekSlip, true);
                        } else {
                            $konversiBiasa += $this->hitungJamLemburKonversi($otHours, false, $wdPerWeekSlip, true);
                        }
                    }
                } else {
                    $konversiBiasa = $this->hitungJamLemburKonversi($jamLemburBiasa, false, $wdPerWeekSlip, false);
                    $konversiLibur = $this->hitungJamLemburKonversi($jamLemburLibur, true, $wdPerWeekSlip, false);
                }

                if ($jamLemburBiasa > 0 && $jamLemburLibur > 0) {
                    $payBiasa = round($konversiBiasa * $upahSejamSlip, 2);
                    $payLibur = $lemburPayTotal - $payBiasa;
                    
                    $earnings[] = [
                        'nama' => 'Lembur Hari Kerja (' . $jamLemburBiasa . ' jam → ' . $konversiBiasa . 'x upah perjam)',
                        'nilai' => $payBiasa
                    ];
                    $earnings[] = [
                        'nama' => 'Lembur Hari Libur (' . $jamLemburLibur . ' jam → ' . $konversiLibur . 'x upah perjam)',
                        'nilai' => $payLibur
                    ];
                } else if ($jamLemburBiasa > 0) {
                    $earnings[] = [
                        'nama' => 'Lembur Hari Kerja (' . $jamLemburBiasa . ' jam → ' . $konversiBiasa . 'x upah perjam)',
                        'nilai' => $lemburPayTotal
                    ];
                } else if ($jamLemburLibur > 0) {
                    $earnings[] = [
                        'nama' => 'Lembur Hari Libur (' . $jamLemburLibur . ' jam → ' . $konversiLibur . 'x upah perjam)',
                        'nilai' => $lemburPayTotal
                    ];
                }
            } else if ($lemburPayTotal > 0) {
                // Fallback: no breakdown, show single line
                $earnings[] = ['nama' => 'Lembur', 'nilai' => $lemburPayTotal];
            } else if (floatval($att['jam_lembur'] ?? 0) > 0) {
                // Legacy fallback: calculate from attendance
                $earnings[] = ['nama' => 'Lembur', 'nilai' => floatval($att['jam_lembur']) * $otRate];
            }

            if (isset($final['bonus_tambahan']) && floatval($final['bonus_tambahan']) > 0) {
                $earnings[] = ['nama' => 'Bonus/Lainnya', 'nilai' => floatval($final['bonus_tambahan'])];
            } else if ($att['bonus_tambahan'] > 0) {
                $earnings[] = ['nama' => 'Bonus/Lainnya', 'nilai' => $att['bonus_tambahan']];
            }

            if (isset($final['early_arrival_pay']) && floatval($final['early_arrival_pay']) > 0) {
                $earnings[] = [
                    'nama' => 'Early Arrival (' . round(intval($final['early_arrival_minutes']) / 60) . ' jam)',
                    'nilai' => floatval($final['early_arrival_pay'])
                ];
            }
            
            if (array_key_exists('potongan_absen', $final) && $final['potongan_absen'] !== null) {
                $potonganAbsenDb = floatval($final['potongan_absen']);
                if ($potonganAbsenDb > 0) {
                    $deductions[] = ['nama' => 'Potongan Absen', 'nilai' => $potonganAbsenDb];
                }
            } else if (!$isAbsenTidakPotong) {
                $potongan_absen = floatval($att['potongan_absensi']);
                if ($potongan_absen == 0) {
                    $missingDays = max(0, $stdWorkingDays - intval($att['hari_kerja']));
                    if ($missingDays > 0) {
                        if ($isProrate) {
                            $potongan_absen = $unproratedGajiPokok * ($missingDays / $stdWorkingDays);
                        } else {
                            $nominalPotongan = ($nominalPotonganAbsen > 0) ? $nominalPotonganAbsen : (($absenceConfig && isset($absenceConfig->nominal_potongan)) ? floatval($absenceConfig->nominal_potongan) : 0);
                            $potongan_absen = $missingDays * $nominalPotongan;
                        }
                    }
                }
                if ($potongan_absen > 0) {
                    $deductions[] = ['nama' => 'Potongan Absen', 'nilai' => $potongan_absen];
                }
            }
        }

        // Match payroll scheme template for organizational matching
        // Scheme template is already resolved at the beginning of the function

        $workingDays = ($att && isset($att['hari_kerja']) && $att['hari_kerja'] !== null) ? intval($att['hari_kerja']) : $stdWorkingDays;

        if ($schemeTemplate) {
            $schemeAllowances = [
                'Tunjangan Transport' => floatval($schemeTemplate['tunjangan_transport'] ?? 0),
                'Tunjangan Makan Harian' => floatval($schemeTemplate['tunjangan_makan'] ?? 0),
                'Tunjangan Komunikasi' => floatval($schemeTemplate['tunjangan_komunikasi'] ?? 0),
                'Tunjangan Jabatan' => floatval($schemeTemplate['tunjangan_jabatan'] ?? 0),
                'Tunjangan Kehadiran' => floatval($schemeTemplate['tunjangan_kehadiran'] ?? 0),
                'Tunjangan Kinerja' => floatval($schemeTemplate['tunjangan_kinerja'] ?? 0),
            ];

            foreach ($schemeAllowances as $allowanceName => $allowanceValue) {
                if ($allowanceValue > 0) {
                    if ($allowanceName === 'Tunjangan Makan Harian') {
                        $finalValue = $allowanceValue * $workingDays;
                    } else {
                        $finalValue = $allowanceValue;
                    }

                    $earnings[] = ['nama' => $allowanceName, 'nilai' => $finalValue];
                }
            }

            $schemeDeductions = [
                'Potongan Pinjaman' => floatval($schemeTemplate['potongan_pinjaman'] ?? 0),
                'Potongan Kasbon' => floatval($schemeTemplate['potongan_kasbon'] ?? 0),
                'Potongan Lainnya' => floatval($schemeTemplate['potongan_lainnya'] ?? 0),
            ];

            foreach ($schemeDeductions as $deductionName => $deductionValue) {
                if ($deductionValue > 0) {
                    $deductions[] = ['nama' => $deductionName, 'nilai' => $deductionValue];
                }
            }
        }

        // Resolve BPJS Rates dynamically
        $taxScheme = null;
        if ($clientConfig && $clientConfig->tax_scheme_id) {
            $taxScheme = $this->db->table('tax_schemes')->where('id', $clientConfig->tax_scheme_id)->get()->getRow();
        }
        $bpjsScheme = null;
        if ($clientConfig && !empty($clientConfig->bpjs_scheme_id)) {
            $bpjsScheme = $this->db->table('tax_schemes')->where('id', $clientConfig->bpjs_scheme_id)->get()->getRow();
        }
        if (!$bpjsScheme) {
            $bpjsScheme = $taxScheme;
        }

        $bpjsSrc = $bpjsScheme ?: ($schemeTemplate ?: $taxScheme);
        $isBpjsTemplate = is_array($bpjsSrc);

        $kesRateEmp = 1.0;
        $jhtRateEmp = 2.0;
        $jpRateEmp = 1.0;
        $kesRateComp = 4.0;
        $jhtRateComp = 3.7;
        $jpRateComp = 2.0;
        $jkkRateComp = 0.24;
        $jkmRateComp = 0.30;

        if ($bpjsSrc) {
            $kesRateEmp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_kes_karyawan'] ?? 1.0) : ($bpjsSrc->bpjs_kes_karyawan ?? 1.0));
            $jhtRateEmp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jht_karyawan'] ?? 2.0) : ($bpjsSrc->bpjs_jht_karyawan ?? 2.0));
            $jpRateEmp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jp_karyawan'] ?? 1.0) : ($bpjsSrc->bpjs_jp_karyawan ?? 1.0));
            $kesRateComp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_kes_perusahaan'] ?? 4.0) : ($bpjsSrc->bpjs_kes_perusahaan ?? 4.0));
            $jhtRateComp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jht_perusahaan'] ?? 3.7) : ($bpjsSrc->bpjs_jht_perusahaan ?? 3.7));
            $jpRateComp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jp_perusahaan'] ?? 2.0) : ($bpjsSrc->bpjs_jp_perusahaan ?? 2.0));
            $jkkRateComp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jkk_perusahaan'] ?? 0.24) : ($bpjsSrc->bpjs_jkk_perusahaan ?? 0.24));
            $jkmRateComp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jkm_perusahaan'] ?? 0.30) : ($bpjsSrc->bpjs_jkm_perusahaan ?? 0.30));
        }

        // Add BPJS and PPh 21 detailed lines
        if (isset($final['bpjs_kes_karyawan'])) {
            if (floatval($final['bpjs_kes_karyawan']) > 0) {
                $deductions[] = ['nama' => 'BPJS Kesehatan (' . floatval($kesRateEmp) . '% Karyawan)', 'nilai' => floatval($final['bpjs_kes_karyawan'])];
            }
            if (floatval($final['bpjs_jht_karyawan']) > 0) {
                $deductions[] = ['nama' => 'BPJS TK JHT (' . floatval($jhtRateEmp) . '% Karyawan)', 'nilai' => floatval($final['bpjs_jht_karyawan'])];
            }
            if (floatval($final['bpjs_jp_karyawan']) > 0) {
                $deductions[] = ['nama' => 'BPJS TK JP (' . floatval($jpRateEmp) . '% Karyawan)', 'nilai' => floatval($final['bpjs_jp_karyawan'])];
            }

            if (floatval($final['pph21']) > 0) {
                $isDecember = (intval($final['bulan'] ?? 0) === 12);
                $allowanceLabel = $isDecember ? 'Tunjangan Pajak (Gross Up Pasal 17)' : 'Tunjangan Pajak (Gross Up TER)';
                $taxLabel = $isDecember ? 'Potongan Pajak PPh 21 (Pasal 17)' : 'Potongan Pajak PPh 21 (TER)';

                if ($final['tax_method'] === 'Gross Up') {
                    $earnings[] = ['nama' => $allowanceLabel, 'nilai' => floatval($final['tax_allowance'])];
                    $deductions[] = ['nama' => $taxLabel, 'nilai' => floatval($final['pph21'])];
                } elseif (strcasecmp($final['tax_method'] ?? '', 'Net') !== 0 && strcasecmp($final['tax_method'] ?? '', 'Nett') !== 0) {
                    $deductions[] = ['nama' => $taxLabel, 'nilai' => floatval($final['pph21'])];
                }
            }
        }

        // Add company burdens (informational)
        $companyBurdens = [];
        if (isset($final['bpjs_kes_perusahaan'])) {
            if (floatval($final['bpjs_kes_perusahaan']) > 0) {
                $companyBurdens[] = ['nama' => 'BPJS Kesehatan (' . floatval($kesRateComp) . '% Beban Perusahaan)', 'nilai' => floatval($final['bpjs_kes_perusahaan'])];
            }
            if (floatval($final['bpjs_jht_perusahaan']) > 0) {
                $companyBurdens[] = ['nama' => 'BPJS TK JHT (' . floatval($jhtRateComp) . '% Beban Perusahaan)', 'nilai' => floatval($final['bpjs_jht_perusahaan'])];
            }
            if (floatval($final['bpjs_jp_perusahaan']) > 0) {
                $companyBurdens[] = ['nama' => 'BPJS TK JP (' . floatval($jpRateComp) . '% Beban Perusahaan)', 'nilai' => floatval($final['bpjs_jp_perusahaan'])];
            }
            if (floatval($final['bpjs_jkk_perusahaan']) > 0) {
                $companyBurdens[] = ['nama' => 'BPJS TK JKK (' . floatval($jkkRateComp) . '% Beban Perusahaan)', 'nilai' => floatval($final['bpjs_jkk_perusahaan'])];
            }
            if (floatval($final['bpjs_jkm_perusahaan']) > 0) {
                $companyBurdens[] = ['nama' => 'BPJS TK JKM (' . floatval($jkmRateComp) . '% Beban Perusahaan)', 'nilai' => floatval($final['bpjs_jkm_perusahaan'])];
            }
        }

        return $this->respond([
            'info' => $final,
            'earnings' => $earnings,
            'deductions' => $deductions,
            'company_burdens' => $companyBurdens
        ]);
    }

    public function exportPayrollCsv($periodId)
    {
        $clientId = $this->request->getGet('client_id');
        
        $query = $this->db->table('payroll_final')
                          ->select('
                              payroll_final.*, 
                              pkwt.employee_name, 
                              pkwt.position_name, 
                              pkwt.tipe_perjanjian, 
                              pkwt.client_id, 
                              clients.nama as client_name,
                              employees.employ_id,
                              employees.tempat_lahir,
                              employees.tanggal_lahir,
                              employees.npwp,
                              divisions.nama as division_name,
                              departments.nama as department_name,
                              work_locations.lokasi_kerja as location_name,
                              minimum_wages.nominal as min_wage
                          ')
                          ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                          ->join('clients', 'clients.id = pkwt.client_id')
                          ->join('employees', 'employees.nama = pkwt.employee_name AND employees.client_id = pkwt.client_id', 'left')
                          ->join('positions', 'positions.id = employees.position_id', 'left')
                          ->join('departments', 'departments.id = positions.department_id', 'left')
                          ->join('divisions', 'divisions.id = departments.division_id', 'left')
                          ->join('work_locations', 'work_locations.id = employees.work_location_id', 'left')
                          ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                          ->where('payroll_final.period_id', $periodId);
                          
        if ($clientId) {
            $query->where('pkwt.client_id', $clientId);
        }
        
        $results = $query->get()->getResultArray();
        
        if ($this->request->getGet('format') === 'json') {
            return $this->respond($results);
        }
        
        // Define file name
        $periodInfo = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        $periodInfo = $this->formatPeriodRow($periodInfo);
        $periodName = $periodInfo ? $periodInfo->nama : 'Unknown_Period';
        $filename = "Payroll_Report_" . str_replace(' ', '_', $periodName) . ".csv";
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM to fix UTF-8 in Excel
        fputs($output, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));

        // Write Header Row
        fputcsv($output, [
            'No', 
            'Company / Client',
            'Employee ID (NIK)',
            'Employee Name', 
            'Place & Date of Birth',
            'NPWP',
            'Division',
            'Department',
            'Position / Role', 
            'Work Location',
            'Min. Wage (UMP/UMK)',
            'Basic Salary', 
            'Overtime Pay',
            'Early Arrival',
            'Rapel',
            'Tunjangan Lainnya/Bonus',
            'Total Income (Pendapatan)', 
            'Absence Deduction',
            'BPJS Kes (Karyawan)',
            'BPJS JHT (Karyawan)',
            'BPJS JP (Karyawan)',
            'Tax (PPh21)',
            'Potongan Lainnya',
            'Total Deductions (Potongan)', 
            'Take Home Pay', 
            'Status'
        ], ';');
        
        // Write Data Rows
        $no = 1;
        foreach ($results as $row) {
            $placeDob = '';
            if (!empty($row['tempat_lahir']) && !empty($row['tanggal_lahir'])) {
                $placeDob = $row['tempat_lahir'] . ', ' . $row['tanggal_lahir'];
            } elseif (!empty($row['tempat_lahir'])) {
                $placeDob = $row['tempat_lahir'];
            } elseif (!empty($row['tanggal_lahir'])) {
                $placeDob = $row['tanggal_lahir'];
            }
            
            $gp = floatval($row['gaji_pokok'] ?? 0);
            $ot = floatval($row['lembur_pay'] ?? 0);
            $ea = floatval($row['early_arrival_pay'] ?? 0);
            $taxAllowance = floatval($row['tax_allowance'] ?? 0);
            $totalPendapatan = floatval($row['total_pendapatan'] ?? 0);
            
            // Extract rapel
            $rapelVal = 0.0;
            if (!empty($row['raw_components'])) {
                try {
                    $comps = json_decode($row['raw_components'], true);
                    if (is_array($comps)) {
                        foreach ($comps as $c) {
                            if (isset($c['nama']) && stripos($c['nama'], 'rapel') !== false) {
                                $rapelVal += floatval($c['nilai'] ?? 0);
                            }
                        }
                    }
                } catch (\Exception $e) {
                }
            }
            
            $tunjanganLainnya = $totalPendapatan - ($gp + $ot + $ea + $rapelVal + $taxAllowance);
            if ($tunjanganLainnya < 0) $tunjanganLainnya = 0;
            
            $potAbsen = floatval($row['potongan_absen'] ?? 0);
            $bpjsKes = floatval($row['bpjs_kes_karyawan'] ?? 0);
            $bpjsJht = floatval($row['bpjs_jht_karyawan'] ?? 0);
            $bpjsJp = floatval($row['bpjs_jp_karyawan'] ?? 0);
            $pph21 = floatval($row['pph21'] ?? 0);
            $totalPotongan = floatval($row['total_potongan'] ?? 0);
            
            $taxMethod = $row['tax_method'] ?? 'Gross';
            $pajakDikurangi = ($taxMethod === 'Net') ? 0 : $pph21;
            
            $potonganLainnya = $totalPotongan - ($potAbsen + $bpjsKes + $bpjsJht + $bpjsJp + $pajakDikurangi);
            if ($potonganLainnya < 0) $potonganLainnya = 0;
            
            fputcsv($output, [
                $no++,
                $row['client_name'] ?? '-',
                $row['employ_id'] ?? '-',
                $row['employee_name'] ?? '-',
                $placeDob ?: '-',
                $row['npwp'] ?? '-',
                $row['division_name'] ?? '-',
                $row['department_name'] ?? '-',
                $row['position_name'] ?? '-',
                $row['location_name'] ?? '-',
                isset($row['min_wage']) ? 'Rp ' . number_format((float)$row['min_wage'], 0, ',', '.') : '-',
                'Rp ' . number_format($gp, 0, ',', '.'),
                'Rp ' . number_format($ot, 0, ',', '.'),
                'Rp ' . number_format($ea, 0, ',', '.'),
                'Rp ' . number_format($rapelVal, 0, ',', '.'),
                'Rp ' . number_format($tunjanganLainnya, 0, ',', '.'),
                'Rp ' . number_format($totalPendapatan, 0, ',', '.'),
                'Rp ' . number_format($potAbsen, 0, ',', '.'),
                'Rp ' . number_format($bpjsKes, 0, ',', '.'),
                'Rp ' . number_format($bpjsJht, 0, ',', '.'),
                'Rp ' . number_format($bpjsJp, 0, ',', '.'),
                'Rp ' . number_format($pph21, 0, ',', '.'),
                'Rp ' . number_format($potonganLainnya, 0, ',', '.'),
                'Rp ' . number_format($totalPotongan, 0, ',', '.'),
                'Rp ' . number_format((float)($row['take_home_pay'] ?? 0), 0, ',', '.'),
                $row['status_approval'] ?? 'Pending'
            ], ';');
        }
        
        fclose($output);
        exit;
    }


    // --- EMPLOYEES ---
    public function getEmployees()
    {
        $clientId = $this->request->getGet('client_id');
        $query = $this->db->table('positions')
                             ->select('positions.*, departments.nama as department_name, divisions.nama as division_name')
                             ->join('departments', 'departments.id = positions.department_id')
                             ->join('divisions', 'divisions.id = departments.division_id');
        if ($clientId) {
            $query->where('divisions.client_id', $clientId);
        }
        $employees = $query->get()->getResult();
        return $this->respond($employees);
    }

    // --- CLIENT COMPENSATIONS ---
    public function getCompensations($clientId)
    {
        $data = $this->db->table('client_compensations')
                         ->where('client_id', $clientId)
                         ->orderBy('id', 'ASC')
                         ->get()
                         ->getResultArray();
        return $this->respond($data);
    }

    public function createCompensation()
    {
        $data = $this->request->getJSON(true);
        $this->db->table('client_compensations')->insert($data);
        return $this->respondCreated(['message' => 'Komponen kompensasi berhasil ditambahkan']);
    }

    public function updateCompensation($id)
    {
        $data = $this->request->getJSON(true);
        unset($data['id']);
        $this->db->table('client_compensations')->where('id', $id)->update($data);
        return $this->respond(['message' => 'Komponen kompensasi berhasil diupdate']);
    }

    public function deleteCompensation($id)
    {
        $this->db->table('client_compensations')->where('id', $id)->delete();
        return $this->respondDeleted(['message' => 'Komponen kompensasi berhasil dihapus']);
    }

    // --- CLIENT ABSENCE CONFIG ---
    public function getAbsenceConfig($clientId)
    {
        $data = $this->db->table('client_absence_configs')
                         ->where('client_id', $clientId)
                         ->get()
                         ->getRowArray();
        return $this->respond($data ?: []);
    }

    public function saveAbsenceConfig()
    {
        $data = $this->request->getJSON(true);
        $clientId = $data['client_id'];

        $existing = $this->db->table('client_absence_configs')
                             ->where('client_id', $clientId)
                             ->get()
                             ->getRow();

        if ($existing) {
            $this->db->table('client_absence_configs')->where('client_id', $clientId)->update($data);
        } else {
            $this->db->table('client_absence_configs')->insert($data);
        }

        $this->logActivity("Menyimpan konfigurasi absensi klien ID: " . $clientId);
        return $this->respond(['message' => 'Konfigurasi absensi berhasil disimpan']);
    }

    // --- LOGS ---
    public function getLogs()
    {
        $logs = $this->db->table('status_logs')
                         ->orderBy('created_at', 'DESC')
                         ->get()
                         ->getResultArray();
        return $this->respond($logs ?: []);
    }

    protected function logActivity($action, $username = null)
    {
        if ($username === null) {
            $username = ($this->request !== null) ? ($this->request->getHeaderLine('X-User-Action') ?: 'System') : 'System';
        }
        
        $this->db->table('status_logs')->insert([
            'description' => $action,
            'user_action' => $username,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    protected function resolveUmpUmk($minimumWageId, $tahun = null, $employeeProvince = null)
    {
        $res = ['ump' => 0, 'umk' => 0];
        if (!$minimumWageId) return $res;

        $currentWage = $this->db->table('minimum_wages')->where('id', $minimumWageId)->get()->getRow();
        if (!$currentWage) return $res;

        // Try current year first, then wage record's year
        $currentYear = date('Y');
        $wageYear = $currentWage->tahun ?: $currentYear;
        $year = $tahun ?: $currentYear;

        if ($currentWage->tipe === 'UMP') {
            // If the stored UMP is for the current year, use it directly
            if ($currentWage->tahun == $currentYear) {
                $res['ump'] = floatval($currentWage->nominal);
            } else {
                // Try to find a more recent UMP for the same region
                $searchName = $currentWage->nama_daerah ?: $currentWage->provinsi;
                $newerUmp = $this->db->table('minimum_wages')
                                     ->where('tipe', 'UMP')
                                     ->where('tahun', $currentYear)
                                     ->groupStart()
                                         ->where('nama_daerah', $searchName)
                                         ->orWhere('provinsi', $searchName)
                                     ->groupEnd()
                                     ->get()
                                     ->getRow();
                $res['ump'] = $newerUmp ? floatval($newerUmp->nominal) : floatval($currentWage->nominal);
            }
            // Fallback UMK to the same value
            $res['umk'] = $res['ump'];
        } else if ($currentWage->tipe === 'UMK') {
            // For UMK, also try to find the current year's UMK
            if ($currentWage->tahun == $currentYear) {
                $res['umk'] = floatval($currentWage->nominal);
            } else {
                $searchName = $currentWage->nama_daerah;
                $newerUmk = $this->db->table('minimum_wages')
                                     ->where('tipe', 'UMK')
                                     ->where('tahun', $currentYear)
                                     ->where('nama_daerah', $searchName)
                                     ->get()
                                     ->getRow();
                $res['umk'] = $newerUmk ? floatval($newerUmk->nominal) : floatval($currentWage->nominal);
            }
            
            // Find corresponding UMP for the province
            // Build list of province names to search for
            $provinceSearchNames = [];
            if (!empty($currentWage->provinsi)) {
                $provinceSearchNames[] = $currentWage->provinsi;
            }
            if (!empty($employeeProvince)) {
                $provinceSearchNames[] = $employeeProvince;
                // Also try uppercase variant
                $provinceSearchNames[] = strtoupper($employeeProvince);
            }
            // Also try to extract province from kode_daerah (e.g. "ID 31.73" -> province code 31)
            if (!empty($currentWage->kode_daerah)) {
                $parts = explode(' ', $currentWage->kode_daerah);
                if (count($parts) >= 2) {
                    $codeParts = explode('.', $parts[1]);
                    $provCode = $codeParts[0]; // e.g. "31"
                    // Look for UMP with matching province code prefix
                    $umpByCode = $this->db->table('minimum_wages')
                                         ->where('tipe', 'UMP')
                                         ->where('tahun', $currentYear)
                                         ->like('kode_daerah', "ID $provCode", 'after')
                                         ->get()
                                         ->getRow();
                    if (!$umpByCode) {
                        // Fallback to wage record's year
                        $umpByCode = $this->db->table('minimum_wages')
                                             ->where('tipe', 'UMP')
                                             ->where('tahun', $wageYear)
                                             ->like('kode_daerah', "ID $provCode", 'after')
                                             ->get()
                                             ->getRow();
                    }
                    if ($umpByCode) {
                        $res['ump'] = floatval($umpByCode->nominal);
                        return $res;
                    }
                }
            }
            
            // Search UMP by province name
            $umpWage = null;
            $yearsToTry = array_unique([$currentYear, $wageYear]);
            foreach ($yearsToTry as $tryYear) {
                foreach ($provinceSearchNames as $provName) {
                    if (empty($provName)) continue;
                    $umpWage = $this->db->table('minimum_wages')
                                        ->where('tipe', 'UMP')
                                        ->where('tahun', $tryYear)
                                        ->groupStart()
                                            ->where('nama_daerah', $provName)
                                            ->orWhere('provinsi', $provName)
                                            ->orWhere('nama_daerah', strtoupper($provName))
                                            ->orWhere('provinsi', strtoupper($provName))
                                        ->groupEnd()
                                        ->get()
                                        ->getRow();
                    if ($umpWage) break 2;
                }
            }
            
            if ($umpWage) {
                $res['ump'] = floatval($umpWage->nominal);
            } else {
                $res['ump'] = $res['umk']; // fallback UMP to UMK value
            }
        }
        return $res;
    }

    public function checkSchema()
    {
        $clientId = $this->request->getGet('client_id');
        $divId = $this->request->getGet('division_id');
        $deptId = $this->request->getGet('department_id');
        $posId = $this->request->getGet('position_id');

        if (!$clientId) return $this->fail('Client ID required');

        // Cek Posisi
        if ($posId) {
            $config = $this->db->table('client_payroll_configs')->where(['client_id' => $clientId, 'position_id' => $posId])->get()->getRow();
            if ($config) return $this->respond(['level' => 'Posisi', 'config' => $config]);
        }
        
        // Cek Departemen
        if ($deptId) {
            $config = $this->db->table('client_payroll_configs')->where(['client_id' => $clientId, 'department_id' => $deptId, 'position_id' => null])->get()->getRow();
            if ($config) return $this->respond(['level' => 'Departemen', 'config' => $config]);
        }

        // Cek Divisi
        if ($divId) {
            $config = $this->db->table('client_payroll_configs')->where(['client_id' => $clientId, 'division_id' => $divId, 'department_id' => null, 'position_id' => null])->get()->getRow();
            if ($config) return $this->respond(['level' => 'Divisi', 'config' => $config]);
        }

        // Cek General Client
        $config = $this->db->table('client_payroll_configs')->where(['client_id' => $clientId, 'division_id' => null, 'department_id' => null, 'position_id' => null])->get()->getRow();
        if ($config) return $this->respond(['level' => 'General Client', 'config' => $config]);

    }

    public function previewPayroll()
    {
        $clientId = $this->request->getGet('client_id');
        $divId = $this->request->getGet('division_id');
        $deptId = $this->request->getGet('department_id');
        $posId = $this->request->getGet('position_id');
        $workLocId = $this->request->getGet('work_location_id');

        if (!$clientId) return $this->fail('Client ID required');

        // Resolve Config
        $config = null;
        $level = 'Tidak Ada';
        
        if ($posId) {
            $config = $this->db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('position_id', $posId)
                ->get()->getRow();
            if ($config) $level = 'Posisi';
        }
        if (!$config && $deptId) {
            $config = $this->db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('department_id', $deptId)
                ->where('position_id IS NULL')
                ->get()->getRow();
            if ($config) $level = 'Departemen';
        }
        if (!$config && $divId) {
            $config = $this->db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('division_id', $divId)
                ->where('department_id IS NULL')
                ->where('position_id IS NULL')
                ->get()->getRow();
            if ($config) $level = 'Divisi';
        }
        if (!$config) {
            $config = $this->db->table('client_payroll_configs')
                ->where('client_id', $clientId)
                ->where('division_id IS NULL')
                ->where('department_id IS NULL')
                ->where('position_id IS NULL')
                ->get()->getRow();
            if ($config) $level = 'General Client';
        }

        if (!$config) {
            return $this->respond(['status' => 'error', 'message' => 'Skema tidak ditemukan']);
        }

        $effectivePayrollType = $config->payroll_type;
        $resolvedPayrollType = $config->payroll_type;
        if ($config->payroll_type === 'Template' && $config->payroll_scheme_id) {
            $basicComp = $this->db->table('payroll_components')
                ->where('scheme_id', $config->payroll_scheme_id)
                ->groupStart()
                    ->where('jenis_komponen', 'basic_salary')
                    ->orLike('nama', 'Gaji Pokok')
                ->groupEnd()
                ->get()
                ->getRow();
            if ($basicComp && !empty($basicComp->sumber_nilai)) {
                if ($basicComp->sumber_nilai === 'ump') {
                    $resolvedPayrollType = 'UMP';
                    $effectivePayrollType = 'UMP';
                } else if ($basicComp->sumber_nilai === 'umk') {
                    $resolvedPayrollType = 'UMK';
                    $effectivePayrollType = 'UMK';
                } else if ($basicComp->sumber_nilai === 'ump_umk') {
                    $resolvedPayrollType = 'UMP/UMK';
                    $effectivePayrollType = 'UMP/UMK';
                }
            }
        }

        $gajiPokok = 0;
        $desc = "Tipe Skema: {$config->payroll_type}. ";

        if ($config->payroll_type === 'Nominal') {
            $gajiPokok = floatval($config->custom_nominal);
            $desc .= "Menggunakan nominal kustom Rp " . number_format($gajiPokok, 0, ',', '.');
        } else if ($effectivePayrollType === 'UMP/UMK' || $effectivePayrollType === 'UMP' || $effectivePayrollType === 'UMK') {
            $mw = null;
            if ($workLocId) {
                $loc = $this->db->table('work_locations')->where('id', $workLocId)->get()->getRow();
                if ($loc) {
                    $searchCity = !empty($loc->kota_kabupaten) ? trim(strtolower($loc->kota_kabupaten)) : null;
                    $searchProv = !empty($loc->provinsi) ? trim(strtolower($loc->provinsi)) : null;
                    
                    if ($effectivePayrollType === 'UMK') {
                        if ($searchCity) {
                            $mw = $this->db->table('minimum_wages')->where(['tipe' => 'UMK', 'nama_daerah' => $loc->kota_kabupaten])->orderBy('tahun', 'DESC')->get()->getRow();
                            if (!$mw) {
                                $mw = $this->db->table('minimum_wages')->where('tipe', 'UMK')->where('LOWER(nama_daerah)', $searchCity)->orderBy('tahun', 'DESC')->get()->getRow();
                            }
                        }
                    } else if ($effectivePayrollType === 'UMP') {
                        if ($searchProv) {
                            $mw = $this->db->table('minimum_wages')->where(['tipe' => 'UMP', 'nama_daerah' => $loc->provinsi])->orderBy('tahun', 'DESC')->get()->getRow();
                            if (!$mw) {
                                $mw = $this->db->table('minimum_wages')->where('tipe', 'UMP')->where('LOWER(nama_daerah)', $searchProv)->orderBy('tahun', 'DESC')->get()->getRow();
                            }
                        }
                    } else { // UMP/UMK
                        if ($searchCity) {
                            $mw = $this->db->table('minimum_wages')->where(['tipe' => 'UMK', 'nama_daerah' => $loc->kota_kabupaten])->orderBy('tahun', 'DESC')->get()->getRow();
                            if (!$mw) {
                                $mw = $this->db->table('minimum_wages')->where('tipe', 'UMK')->where('LOWER(nama_daerah)', $searchCity)->orderBy('tahun', 'DESC')->get()->getRow();
                            }
                        }
                        if (!$mw && $searchProv) {
                            $mw = $this->db->table('minimum_wages')->where(['tipe' => 'UMP', 'nama_daerah' => $loc->provinsi])->orderBy('tahun', 'DESC')->get()->getRow();
                            if (!$mw) {
                                $mw = $this->db->table('minimum_wages')->where('tipe', 'UMP')->where('LOWER(nama_daerah)', $searchProv)->orderBy('tahun', 'DESC')->get()->getRow();
                            }
                        }
                    }
                    
                    if ($mw) {
                        $desc .= "Mendeteksi lokasi {$mw->nama_daerah}. ";
                    }
                }
            }
            if (!$mw && $config->minimum_wage_id) {
                $mw = $this->db->table('minimum_wages')->where('id', $config->minimum_wage_id)->get()->getRow();
            }
            
            if ($mw) {
                $gajiPokok = floatval($mw->nominal);
                if ($config->payroll_type === 'Template' && isset($basicComp) && in_array($basicComp->sumber_nilai, ['ump', 'umk', 'ump_umk'])) {
                    $multiplier = floatval($basicComp->nilai);
                    $gajiPokok = $gajiPokok * ($multiplier / 100);
                    $desc .= "Menggunakan UMR {$mw->nama_daerah} (Rp " . number_format($mw->nominal, 0, ',', '.') . ") dengan multiplier {$multiplier}% menjadi Rp " . number_format($gajiPokok, 0, ',', '.');
                } else {
                    $desc .= "Menggunakan UMR {$mw->nama_daerah} (Rp " . number_format($gajiPokok, 0, ',', '.') . ")";
                }
            } else {
                $desc .= "UMR tidak ditemukan untuk lokasi ini.";
            }
        } else if ($config->payroll_type === 'Template') {
            // Find template components if possible. For now, zero as fallback if no fixed income found.
            $desc .= "Menggunakan Template. Silakan sesuaikan komponen gaji pokok jika diperlukan.";
            $gajiPokok = 0;
        }

        $hariKerja = 5;
        if ($posId) {
            $pos = $this->db->table('positions')->where('id', $posId)->get()->getRow();
            if ($pos && isset($pos->hari_kerja)) {
                $hariKerja = intval($pos->hari_kerja);
            }
        }

        $hariKerjaBulanan = 22;
        if ($hariKerja == 6) $hariKerjaBulanan = 26;
        if ($hariKerja == 7) $hariKerjaBulanan = 30;

        $gajiHarian = $gajiPokok / $hariKerjaBulanan;
        $dendaAbsen = $gajiHarian;

        return $this->respond([
            'status' => 'success',
            'level' => $level,
            'payroll_type' => $resolvedPayrollType,
            'gaji_pokok' => $gajiPokok,
            'hari_kerja' => $hariKerja,
            'gaji_harian' => round($gajiHarian),
            'denda_absen' => round($dendaAbsen),
            'description' => $desc
        ]);
    }

    /**
     * Hitung Jam Lembur Konversi berdasarkan PP No. 35 Tahun 2021.
     * 
     * Hari Kerja Biasa:
     *   Jam pertama  => x 1.5
     *   Jam ke-2 dst => x 2.0
     * 
     * Hari Libur / Tanggal Merah (5 hari kerja/minggu):
     *   8 jam pertama => x 2.0
     *   Jam ke-9      => x 3.0
     *   Jam ke-10+    => x 4.0
     * 
     * Hari Libur / Tanggal Merah (6 hari kerja/minggu):
     *   7 jam pertama => x 2.0
     *   Jam ke-8      => x 3.0
     *   Jam ke-9+     => x 4.0
     *
     * @param float $jamLembur  Total jam lembur riil
     * @param bool  $isHoliday  Apakah lembur di hari libur/tanggal merah
     * @param int   $workingDaysPerWeek  Jumlah hari kerja per minggu (5 atau 6)
     * @return float Jam lembur konversi (sudah dikalikan multiplier)
     */
    private function hitungJamLemburKonversi(float $jamLembur, bool $isHoliday = false, int $workingDaysPerWeek = 5, bool $applyDailyCap = true): float
    {
        if ($jamLembur <= 0) {
            return 0.0;
        }

        $jamKonversi = 0.0;

        if (!$isHoliday) {
            // === Lembur di Hari Kerja Biasa ===
            // Batas lembur maksimal 3 jam sehari (Kepmen 102/2004)
            if ($applyDailyCap) {
                $jamLembur = min(3.0, $jamLembur);
            }

            // Jam ke-1 x 1.5
            $jamPertama = min(1.0, $jamLembur);
            $jamKonversi += $jamPertama * 1.5;

            // Jam ke-2 s.d ke-8 x 2.0
            if ($jamLembur > 1.0) {
                $jamBerikutnya = min(7.0, $jamLembur - 1.0);
                $jamKonversi += $jamBerikutnya * 2.0;
            }

            // Jam ke-9 dst x 3.0
            if ($jamLembur > 8.0) {
                $jamSembilanDst = $jamLembur - 8.0;
                $jamKonversi += $jamSembilanDst * 3.0;
            }
        } else {
            // === Lembur di Hari Libur / Tanggal Merah ===
            if ($workingDaysPerWeek == 6) {
                // Skema 6 hari kerja: 7 jam x2, jam ke-8 x3, jam ke-9+ x4 (maksimal 11 jam)
                $jamLembur = min(11.0, $jamLembur);
                $batasAwal = 7.0;
            } else {
                // Skema 5 hari kerja: 8 jam x2, jam ke-9 x3, jam ke-10 s/d ke-12 x4 (maksimal 12 jam)
                $jamLembur = min(12.0, $jamLembur);
                $batasAwal = 8.0;
            }

            if ($jamLembur <= $batasAwal) {
                $jamKonversi += $jamLembur * 2.0;
            } else {
                $jamKonversi += $batasAwal * 2.0; // Jam awal

                $sisa = $jamLembur - $batasAwal;
                if ($sisa <= 1.0) {
                    // Jam transisi x 3.0
                    $jamKonversi += $sisa * 3.0;
                } else {
                    // Jam transisi penuh x 3.0
                    $jamKonversi += 1.0 * 3.0;
                    // Jam selebihnya x 4.0
                    $jamKonversi += ($sisa - 1.0) * 4.0;
                }
            }
        }

        return round($jamKonversi, 2);
    }

    private function getStandardWorkingDays(int $year, int $month, int $workDaysConfig): int
    {
        $db = \Config\Database::connect();
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));
        $holidays = [];
        $holidayRows = $db->table('holiday_calendar')
                          ->where('tanggal >=', $startDate)
                          ->where('tanggal <=', $endDate)
                          ->get()->getResultArray();
        foreach ($holidayRows as $h) {
            $holidays[$h['tanggal']] = true;
        }

        $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
        $stdDays = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $currDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
            if (isset($holidays[$currDate])) {
                continue; // Skip holiday calendar dates
            }

            $dayOfWeek = date('w', mktime(0, 0, 0, $month, $d, $year)); // 0 = Sunday, 6 = Saturday
            if ($workDaysConfig === 5) {
                if ($dayOfWeek != 0 && $dayOfWeek != 6) {
                    $stdDays++;
                }
            } elseif ($workDaysConfig === 6) {
                if ($dayOfWeek != 0) {
                    $stdDays++;
                }
            } else {
                $stdDays++;
            }
        }
        return $stdDays;
    }

    private function getCutoffRange($period, $clientConfig)
    {
        $cutoffStart = $clientConfig ? intval($clientConfig->cutoff_start) : 21;
        $cutoffEnd = $clientConfig ? intval($clientConfig->cutoff_end) : 20;
        
        if ($cutoffStart <= 0) $cutoffStart = 1;
        if ($cutoffEnd <= 0) {
            $cutoffEnd = $cutoffStart - 1;
            if ($cutoffEnd < 1) $cutoffEnd = 31;
        }

        $bulan_start = intval($period->bulan);
        $tahun_start = intval($period->tahun);
        $bulan_end = $bulan_start;
        $tahun_end = $tahun_start;

        if ($cutoffStart > $cutoffEnd && $cutoffStart > 1) {
            $bulan_start -= 1;
            if ($bulan_start < 1) {
                $bulan_start = 12;
                $tahun_start -= 1;
            }
        } else if ($cutoffStart == 1) {
            $daysInMonth = date('t', mktime(0, 0, 0, $bulan_end, 1, $tahun_end));
            $cutoffEnd = $daysInMonth;
        }

        $startDateStr = sprintf('%04d-%02d-%02d', $tahun_start, $bulan_start, $cutoffStart);
        $endDateStr = sprintf('%04d-%02d-%02d', $tahun_end, $bulan_end, $cutoffEnd);

        return [
            'start_date' => $startDateStr,
            'end_date' => $endDateStr,
            'cutoff_start' => $cutoffStart,
            'cutoff_end' => $cutoffEnd
        ];
    }

    private function getStandardWorkingDaysInRange(string $startDate, string $endDate, int $workDaysConfig): int
    {
        $db = \Config\Database::connect();
        $holidays = [];
        $holidayRows = $db->table('holiday_calendar')
                          ->where('tanggal >=', $startDate)
                          ->where('tanggal <=', $endDate)
                          ->get()->getResultArray();
        foreach ($holidayRows as $h) {
            $holidays[$h['tanggal']] = true;
        }

        $startTs = strtotime($startDate);
        $endTs = strtotime($endDate);
        $stdDays = 0;
        for ($curr = $startTs; $curr <= $endTs; $curr = strtotime('+1 day', $curr)) {
            $currDate = date('Y-m-d', $curr);
            if (isset($holidays[$currDate])) {
                continue; // Skip holiday calendar dates
            }

            $dayOfWeek = date('w', $curr);
            if ($workDaysConfig === 5) {
                if ($dayOfWeek != 0 && $dayOfWeek != 6) {
                    $stdDays++;
                }
            } elseif ($workDaysConfig === 6) {
                if ($dayOfWeek != 0) {
                    $stdDays++;
                }
            } else {
                $stdDays++;
            }
        }
        return $stdDays;
    }


    private function calculateBpjsAndTax($gajiPokok, $bpjsWageBase, $pphWageBase, $schemeTemplate, $taxScheme, $minimumWage, $ptkpStatus, $bpjsScheme = null, $bulan = null, $tahun = null, $pkwtId = null)
    {
        $result = [
            'bpjs_kes_karyawan' => 0,
            'bpjs_kes_perusahaan' => 0,
            'bpjs_jht_karyawan' => 0,
            'bpjs_jht_perusahaan' => 0,
            'bpjs_jp_karyawan' => 0,
            'bpjs_jp_perusahaan' => 0,
            'bpjs_jkk_perusahaan' => 0,
            'bpjs_jkm_perusahaan' => 0,
            'pph21' => 0,
            'tax_allowance' => 0,
            'ter_rate' => 0,
            'metode_pajak' => 'Gross'
        ];

        // Resolve tax method
        if ($schemeTemplate) {
            $result['metode_pajak'] = $schemeTemplate['metode_pajak'] ?? ($taxScheme ? ($taxScheme->metode ?? 'Gross') : 'Gross');
        } elseif ($taxScheme) {
            $result['metode_pajak'] = $taxScheme->metode ?? 'Gross';
        }

        if ($gajiPokok <= 0) {
            return $result;
        }

        // Determine Rates source
        $bpjsSrc = $bpjsScheme ?: ($schemeTemplate ?: $taxScheme);
        $taxSrc = $schemeTemplate ?: $taxScheme;

        if ($taxSrc) {
            $isTaxTemplate = is_array($taxSrc);
            $result['metode_pajak'] = $isTaxTemplate ? ($taxSrc['metode_pajak'] ?? 'Gross') : ($taxSrc->metode ?? 'Gross');
        }

        if ($bpjsSrc) {
            $isBpjsTemplate = is_array($bpjsSrc);
            $kesRateEmp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_kes_karyawan'] ?? 1.0) : ($bpjsSrc->bpjs_kes_karyawan ?? 1.0)) / 100;
            $kesRateCo = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_kes_perusahaan'] ?? 4.0) : ($bpjsSrc->bpjs_kes_perusahaan ?? 4.0)) / 100;
            $kesMaxSal = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_kes_max_salary'] ?? 12000000) : ($bpjsSrc->bpjs_kes_max_salary ?? 12000000));
            $jhtRateEmp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jht_karyawan'] ?? 2.0) : ($bpjsSrc->bpjs_jht_karyawan ?? 2.0)) / 100;
            $jhtRateCo = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jht_perusahaan'] ?? 3.7) : ($bpjsSrc->bpjs_jht_perusahaan ?? 3.7)) / 100;
            $jpRateEmp = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jp_karyawan'] ?? 1.0) : ($bpjsSrc->bpjs_jp_karyawan ?? 1.0)) / 100;
            $jpRateCo = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jp_perusahaan'] ?? 2.0) : ($bpjsSrc->bpjs_jp_perusahaan ?? 2.0)) / 100;
            $jpMaxSal = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jp_max_salary'] ?? 10024600) : ($bpjsSrc->bpjs_jp_max_salary ?? 10024600));
            $jkkRateCo = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jkk_perusahaan'] ?? 0.24) : ($bpjsSrc->bpjs_jkk_perusahaan ?? 0.24)) / 100;
            $jkmRateCo = floatval($isBpjsTemplate ? ($bpjsSrc['bpjs_jkm_perusahaan'] ?? 0.30) : ($bpjsSrc->bpjs_jkm_perusahaan ?? 0.30)) / 100;
        } else {
            // Default BPJS rates
            $kesRateEmp = 0.01;
            $kesRateCo = 0.04;
            $kesMaxSal = 12000000;
            $jhtRateEmp = 0.02;
            $jhtRateCo = 0.037;
            $jpRateEmp = 0.01;
            $jpRateCo = 0.02;
            $jpMaxSal = 10024600;
            $jkkRateCo = 0.0024;
            $jkmRateCo = 0.0030;
        }

        // Apply caps
        $kesWageBase = min($bpjsWageBase, $kesMaxSal);
        $jpWageBase = min($bpjsWageBase, $jpMaxSal);

        // Calculations
        $result['bpjs_kes_karyawan'] = $kesWageBase * $kesRateEmp;
        $result['bpjs_kes_perusahaan'] = $kesWageBase * $kesRateCo;

        $result['bpjs_jht_karyawan'] = $bpjsWageBase * $jhtRateEmp;
        $result['bpjs_jht_perusahaan'] = $bpjsWageBase * $jhtRateCo;

        $result['bpjs_jp_karyawan'] = $jpWageBase * $jpRateEmp;
        $result['bpjs_jp_perusahaan'] = $jpWageBase * $jpRateCo;

        $result['bpjs_jkk_perusahaan'] = $bpjsWageBase * $jkkRateCo;
        $result['bpjs_jkm_perusahaan'] = $bpjsWageBase * $jkmRateCo;

        // PPh 21 TER 2024 Calculation
        $bpjsCoPremiums = $result['bpjs_kes_perusahaan'] + $result['bpjs_jkk_perusahaan'] + $result['bpjs_jkm_perusahaan'];
        
        $ptkpCategory = $this->determineTerCategory($ptkpStatus);

        // Rekonsiliasi Desember: gunakan tarif progresif Pasal 17
        if ($bulan == 12 && $tahun && $pkwtId) {
            $decResult = $this->calculateDecemberTaxReconciliation(
                $pkwtId, $tahun, $pphWageBase, $bpjsCoPremiums,
                $result['bpjs_jht_karyawan'], $result['bpjs_jp_karyawan'],
                $ptkpStatus, $result['metode_pajak']
            );
            $result['pph21'] = $decResult['pph21'];
            $result['tax_allowance'] = $decResult['tax_allowance'];
            $result['ter_rate'] = 0; // Not applicable for December progressive
            return $result;
        }

        if ($result['metode_pajak'] === 'Gross Up') {
            // Iteration loop for Gross Up
            $allowance = 0;
            for ($i = 0; $i < 5; $i++) {
                $brutoPajak = $pphWageBase + $bpjsCoPremiums + $allowance;
                $terRate = $this->getTerRate($ptkpCategory, $brutoPajak);
                $allowance = $brutoPajak * ($terRate / 100);
            }
            $result['tax_allowance'] = $allowance;
            $result['pph21'] = $allowance;
            $result['ter_rate'] = $terRate;
        } else {
            $brutoPajak = $pphWageBase + $bpjsCoPremiums;
            $terRate = $this->getTerRate($ptkpCategory, $brutoPajak);
            $result['pph21'] = $brutoPajak * ($terRate / 100);
            $result['ter_rate'] = $terRate;
        }

        return $result;
    }

    /**
     * Rekonsiliasi PPh 21 Desember - Tarif Progresif Pasal 17 UU HPP
     * Menghitung PPh 21 terutang setahun dikurangi akumulasi PPh 21 Jan-Nov
     * Menggunakan tabel payroll_final + payroll_periods
     */
    private function calculateDecemberTaxReconciliation($pkwtId, $tahun, $decPphWageBase, $decBpjsCoPremiums, $decJhtKaryawan, $decJpKaryawan, $ptkpStatus, $metodePajak)
    {
        // 1. Ambil data kumulatif Jan-Nov dari payroll_final
        $prevPayrolls = $this->db->table('payroll_final')
            ->select('payroll_final.*')
            ->join('payroll_periods', 'payroll_periods.id = payroll_final.period_id')
            ->where('payroll_final.pkwt_id', $pkwtId)
            ->where('payroll_final.status_approval', 'Approved')
            ->where('payroll_periods.tahun', $tahun)
            ->where('payroll_periods.bulan >=', 1)
            ->where('payroll_periods.bulan <=', 11)
            ->get()->getResultArray();

        $totalBrutoJanNov = 0;
        $totalPph21JanNov = 0;
        $totalJhtKaryawanJanNov = 0;
        $totalJpKaryawanJanNov = 0;

        foreach ($prevPayrolls as $pp) {
            // Bruto = total_pendapatan - potongan_absen + bpjs perusahaan (kes+jkk+jkm)
            // total_pendapatan already includes gaji_pokok + tunjangan + lembur etc.
            $ppBruto = floatval($pp['total_pendapatan'] ?? 0)
                     - floatval($pp['potongan_absen'] ?? 0)
                     + floatval($pp['bpjs_kes_perusahaan'] ?? 0)
                     + floatval($pp['bpjs_jkk_perusahaan'] ?? 0)
                     + floatval($pp['bpjs_jkm_perusahaan'] ?? 0);
            $totalBrutoJanNov += $ppBruto;
            $totalPph21JanNov += floatval($pp['pph21'] ?? 0);
            $totalJhtKaryawanJanNov += floatval($pp['bpjs_jht_karyawan'] ?? 0);
            $totalJpKaryawanJanNov += floatval($pp['bpjs_jp_karyawan'] ?? 0);
        }

        // 2. Hitung Bruto Desember
        $brutoDesember = $decPphWageBase + $decBpjsCoPremiums;

        // Untuk Gross Up, tambahkan tunjangan pajak iteratif
        $taxAllowanceDec = 0;
        if ($metodePajak === 'Gross Up') {
            for ($i = 0; $i < 10; $i++) {
                $brutoSetahun = $totalBrutoJanNov + $brutoDesember + $taxAllowanceDec;
                $biayaJabatanSetahun = min($brutoSetahun * 0.05, 6000000);
                $iuranJhtJpSetahun = $totalJhtKaryawanJanNov + $decJhtKaryawan + $totalJpKaryawanJanNov + $decJpKaryawan;
                $nettoSetahun = $brutoSetahun - $biayaJabatanSetahun - $iuranJhtJpSetahun;
                $ptkpSetahun = $this->getPtkpAmount($ptkpStatus);
                $pkpSetahun = max(0, floor(($nettoSetahun - $ptkpSetahun) / 1000) * 1000);
                $pph21Setahun = $this->calculateProgressiveTax($pkpSetahun);
                $pph21Desember = max(0, $pph21Setahun - $totalPph21JanNov);
                $taxAllowanceDec = $pph21Desember;
            }
            return [
                'pph21' => $taxAllowanceDec,
                'tax_allowance' => $taxAllowanceDec
            ];
        }

        // 3. Hitung Bruto Setahun (Gross / Nett)
        $brutoSetahun = $totalBrutoJanNov + $brutoDesember;

        // 4. Pengurang: Biaya Jabatan (5% dari bruto, max 6 juta/tahun)
        $biayaJabatanSetahun = min($brutoSetahun * 0.05, 6000000);

        // 5. Pengurang: Iuran JHT + JP Karyawan setahun
        $iuranJhtJpSetahun = $totalJhtKaryawanJanNov + $decJhtKaryawan + $totalJpKaryawanJanNov + $decJpKaryawan;

        // 6. Netto Setahun
        $nettoSetahun = $brutoSetahun - $biayaJabatanSetahun - $iuranJhtJpSetahun;

        // 7. PTKP Setahun
        $ptkpSetahun = $this->getPtkpAmount($ptkpStatus);

        // 8. PKP Setahun (dibulatkan ke ribuan ke bawah)
        $pkpSetahun = max(0, floor(($nettoSetahun - $ptkpSetahun) / 1000) * 1000);

        // 9. PPh 21 Terutang Setahun (Tarif Progresif Pasal 17)
        $pph21Setahun = $this->calculateProgressiveTax($pkpSetahun);

        // 10. PPh 21 Desember = PPh 21 Setahun - Total PPh 21 Jan-Nov
        $pph21Desember = max(0, $pph21Setahun - $totalPph21JanNov);

        return [
            'pph21' => $pph21Desember,
            'tax_allowance' => 0
        ];
    }

    /**
     * Mengembalikan nilai PTKP setahun berdasarkan status
     */
    private function getPtkpAmount($ptkpStatus)
    {
        $ptkpStatus = strtoupper(trim($ptkpStatus ?? 'TK/0'));
        $ptkpMap = [
            'TK/0' => 54000000,
            'TK/1' => 58500000,
            'K/0'  => 58500000,
            'TK/2' => 63000000,
            'K/1'  => 63000000,
            'TK/3' => 67500000,
            'K/2'  => 67500000,
            'K/3'  => 72000000,
        ];
        return $ptkpMap[$ptkpStatus] ?? 54000000;
    }

    /**
     * Menghitung PPh 21 dengan tarif progresif Pasal 17 UU HPP
     */
    private function calculateProgressiveTax($pkp)
    {
        if ($pkp <= 0) return 0;

        $tax = 0;
        // Lapisan 1: s/d 60 juta → 5%
        if ($pkp > 0) {
            $layer = min($pkp, 60000000);
            $tax += $layer * 0.05;
            $pkp -= $layer;
        }
        // Lapisan 2: >60 juta s/d 250 juta → 15%
        if ($pkp > 0) {
            $layer = min($pkp, 190000000);
            $tax += $layer * 0.15;
            $pkp -= $layer;
        }
        // Lapisan 3: >250 juta s/d 500 juta → 25%
        if ($pkp > 0) {
            $layer = min($pkp, 250000000);
            $tax += $layer * 0.25;
            $pkp -= $layer;
        }
        // Lapisan 4: >500 juta s/d 5 miliar → 30%
        if ($pkp > 0) {
            $layer = min($pkp, 4500000000);
            $tax += $layer * 0.30;
            $pkp -= $layer;
        }
        // Lapisan 5: >5 miliar → 35%
        if ($pkp > 0) {
            $tax += $pkp * 0.35;
        }

        return $tax;
    }

    private function determineTerCategory($ptkpStatus)
    {
        $ptkpStatus = strtoupper(trim($ptkpStatus ?? 'TK/0'));
        if (in_array($ptkpStatus, ['TK/0', 'TK/1', 'K/0'])) {
            return 'A';
        }
        if (in_array($ptkpStatus, ['TK/2', 'TK/3', 'K/1', 'K/2'])) {
            return 'B';
        }
        if ($ptkpStatus === 'K/3') {
            return 'C';
        }
        return 'A';
    }

    private function getTerRate($category, $bruto)
    {
        if ($category === 'A') {
            if ($bruto <= 5400000) return 0.0;
            if ($bruto <= 5650000) return 0.25;
            if ($bruto <= 5950000) return 0.5;
            if ($bruto <= 6300000) return 0.75;
            if ($bruto <= 6750000) return 1.0;
            if ($bruto <= 7500000) return 1.25;
            if ($bruto <= 8550000) return 1.5;
            if ($bruto <= 9650000) return 1.75;
            if ($bruto <= 10950000) return 2.0;
            if ($bruto <= 12950000) return 3.0;
            if ($bruto <= 15000000) return 4.0;
            if ($bruto <= 17850000) return 5.0;
            if ($bruto <= 21050000) return 6.0;
            if ($bruto <= 24450000) return 7.0;
            if ($bruto <= 29350000) return 8.0;
            if ($bruto <= 35900000) return 9.0;
            if ($bruto <= 43850000) return 10.0;
            if ($bruto <= 54200000) return 11.0;
            if ($bruto <= 68600000) return 12.0;
            if ($bruto <= 83700000) return 13.0;
            if ($bruto <= 99600000) return 14.0;
            if ($bruto <= 165600000) return 15.0;
            if ($bruto <= 219200000) return 19.0;
            if ($bruto <= 276000000) return 20.0;
            if ($bruto <= 346500000) return 21.0;
            if ($bruto <= 439700000) return 22.0;
            if ($bruto <= 563800000) return 23.0;
            if ($bruto <= 775200000) return 24.0;
            if ($bruto <= 1121200000) return 25.0;
            if ($bruto <= 1512200000) return 26.0;
            if ($bruto <= 2000000000) return 30.0;
            return 34.0;
        } elseif ($category === 'B') {
            if ($bruto <= 6200000) return 0.0;
            if ($bruto <= 6500000) return 0.25;
            if ($bruto <= 6850000) return 0.5;
            if ($bruto <= 7300000) return 0.75;
            if ($bruto <= 7800000) return 1.0;
            if ($bruto <= 8850000) return 1.25;
            if ($bruto <= 9800000) return 1.5;
            if ($bruto <= 10950000) return 1.75;
            if ($bruto <= 12300000) return 2.0;
            if ($bruto <= 14850000) return 3.0;
            if ($bruto <= 17200000) return 4.0;
            if ($bruto <= 19550000) return 5.0;
            if ($bruto <= 22700000) return 6.0;
            if ($bruto <= 26600000) return 7.0;
            if ($bruto <= 31850000) return 8.0;
            if ($bruto <= 39400000) return 9.0;
            if ($bruto <= 48250000) return 10.0;
            if ($bruto <= 58750000) return 11.0;
            if ($bruto <= 72050000) return 12.0;
            if ($bruto <= 88750000) return 13.0;
            if ($bruto <= 107800000) return 14.0;
            if ($bruto <= 168600000) return 15.0;
            if ($bruto <= 219900000) return 19.0;
            if ($bruto <= 276300000) return 20.0;
            if ($bruto <= 346800000) return 21.0;
            if ($bruto <= 439900000) return 22.0;
            if ($bruto <= 564000000) return 23.0;
            if ($bruto <= 775400000) return 24.0;
            if ($bruto <= 1121500000) return 25.0;
            if ($bruto <= 1512500000) return 26.0;
            if ($bruto <= 2000000000) return 30.0;
            return 34.0;
        } else { // Category C
            if ($bruto <= 6600000) return 0.0;
            if ($bruto <= 6950000) return 0.25;
            if ($bruto <= 7350000) return 0.5;
            if ($bruto <= 7800000) return 0.75;
            if ($bruto <= 8300000) return 1.0;
            if ($bruto <= 9550000) return 1.25;
            if ($bruto <= 10650000) return 1.5;
            if ($bruto <= 11850000) return 1.75;
            if ($bruto <= 13600000) return 2.0;
            if ($bruto <= 16000000) return 3.0;
            if ($bruto <= 18550000) return 4.0;
            if ($bruto <= 20850000) return 5.0;
            if ($bruto <= 24550000) return 6.0;
            if ($bruto <= 28600000) return 7.0;
            if ($bruto <= 34600000) return 8.0;
            if ($bruto <= 42300000) return 9.0;
            if ($bruto <= 51600000) return 10.0;
            if ($bruto <= 62900000) return 11.0;
            if ($bruto <= 77250000) return 12.0;
            if ($bruto <= 95100000) return 13.0;
            if ($bruto <= 115400000) return 14.0;
            if ($bruto <= 179800000) return 15.0;
            if ($bruto <= 233700000) return 19.0;
            if ($bruto <= 293700000) return 20.0;
            if ($bruto <= 368500000) return 21.0;
            if ($bruto <= 467400000) return 22.0;
            if ($bruto <= 599500000) return 23.0;
            if ($bruto <= 824200000) return 24.0;
            if ($bruto <= 1192200000) return 25.0;
            if ($bruto <= 1607700000) return 26.0;
            if ($bruto <= 2126000000) return 30.0;
            return 34.0;
        }
    }

    private function resolveClientConfig($clientId, $positionName = null)
    {
        $positions = [];
        if (!empty($positionName)) {
            // Find the client-specific position and its org hierarchy
            $positions = $this->db->table('positions')
                      ->select('positions.id as position_id, departments.id as department_id, divisions.id as division_id')
                      ->join('departments', 'departments.id = positions.department_id', 'left')
                      ->join('divisions', 'divisions.id = departments.division_id', 'left')
                      ->where('positions.nama', $positionName)
                      ->where('divisions.client_id', $clientId)
                      ->get()
                      ->getResult();
        }
        
        if (!empty($positions)) {
            // 2. Try to search by position_id
            foreach ($positions as $p) {
                if ($p->position_id) {
                    $config = $this->db->table('client_payroll_configs')
                                 ->where('client_id', $clientId)
                                 ->where('position_id', $p->position_id)
                                 ->get()
                                 ->getRow();
                    if ($config && ($config->payroll_scheme_id || $config->tax_scheme_id || $config->compensation_scheme_id)) {
                        return $config;
                    }
                }
            }
            
            // 3. Fallback to department_id
            foreach ($positions as $p) {
                if ($p->department_id) {
                    $config = $this->db->table('client_payroll_configs')
                                 ->where('client_id', $clientId)
                                 ->where('department_id', $p->department_id)
                                 ->where('position_id IS NULL')
                                 ->get()
                                 ->getRow();
                    if ($config && ($config->payroll_scheme_id || $config->tax_scheme_id || $config->compensation_scheme_id)) {
                        return $config;
                    }
                }
            }
            
            // 4. Fallback to division_id
            foreach ($positions as $p) {
                if ($p->division_id) {
                    $config = $this->db->table('client_payroll_configs')
                                 ->where('client_id', $clientId)
                                 ->where('division_id', $p->division_id)
                                 ->where('department_id IS NULL')
                                 ->where('position_id IS NULL')
                                 ->get()
                                 ->getRow();
                    if ($config && ($config->payroll_scheme_id || $config->tax_scheme_id || $config->compensation_scheme_id)) {
                        return $config;
                    }
                }
            }
        }
        
        // 5. Fallback to global config (where all org columns are NULL)
        return $this->db->table('client_payroll_configs')
                  ->where('client_id', $clientId)
                  ->where('division_id IS NULL')
                  ->where('department_id IS NULL')
                  ->where('position_id IS NULL')
                  ->get()
                  ->getRow();
    }

    public function syncPKWTComponents($pkwtId, $defaultBasicSalary = null)
    {
        $pkwt = $this->db->table('pkwt')->where('id', $pkwtId)->get()->getRow();
        if (!$pkwt) {
            return;
        }

        $basicSalary = 0;
        if ($defaultBasicSalary !== null && floatval($defaultBasicSalary) > 0) {
            $basicSalary = floatval($defaultBasicSalary);
        } else {
            // Fallback to employee's current gaji_pokok in database
            $emp = $this->db->table('employees')
                            ->where('client_id', $pkwt->client_id)
                            ->where('nama', $pkwt->employee_name)
                            ->get()
                            ->getRow();
            if ($emp) {
                $basicSalary = floatval($emp->gaji_pokok);
            } else {
                // Last fallback: existing basic component
                $existingBasic = $this->db->table('pkwt_components')
                                          ->where('pkwt_id', $pkwtId)
                                          ->groupStart()
                                              ->like('nama', 'Gaji Pokok')
                                              ->orWhere('jenis_komponen', 'basic_salary')
                                          ->groupEnd()
                                          ->get()
                                          ->getRow();
                if ($existingBasic) {
                    $basicSalary = floatval($existingBasic->nilai);
                }
            }
        }

        // Clear existing pkwt components (except Ad-hoc components, e.g. rapel)
        $this->db->table('pkwt_components')
                 ->where('pkwt_id', $pkwtId)
                 ->where('(allowance_type != \'Ad-hoc\' OR allowance_type IS NULL)')
                 ->delete();

        // Get current active Client Scheme Config
        $config = $this->resolveClientConfig($pkwt->client_id, $pkwt->position_name);

        $hasComponents = false;
        if ($config && $config->payroll_scheme_id) {
            // Fetch components from scheme
            $components = $this->db->table('payroll_components')
                                   ->where('scheme_id', $config->payroll_scheme_id)
                                   ->get()
                                   ->getResult();

            if (!empty($components)) {
                $hasComponents = true;
                foreach ($components as $comp) {
                    $nilai = $comp->nilai;
                    if (stripos($comp->nama, 'Gaji Pokok') !== false || ($comp->jenis_komponen ?? '') === 'basic_salary') {
                        if (isset($comp->sumber_nilai) && ($comp->sumber_nilai === 'ump' || $comp->sumber_nilai === 'umk' || $comp->sumber_nilai === 'kompensasi')) {
                            $nilai = $comp->nilai;
                        } else {
                            $nilai = $basicSalary;
                        }
                    }

                    $this->db->table('pkwt_components')->insert([
                        'pkwt_id' => $pkwtId,
                        'nama' => $comp->nama,
                        'tipe' => $comp->tipe,
                        'nilai' => $nilai,
                        'is_persentase' => $comp->is_persentase,
                        'jenis_komponen' => $comp->jenis_komponen ?? 'basic_salary',
                        'sifat_kompensasi' => $comp->sifat_kompensasi ?? 'tetap',
                        'sumber_nilai' => $comp->sumber_nilai ?? 'nominal',
                        'periode' => $comp->periode ?? 'bulan',
                        'is_bpjs' => $comp->is_bpjs ?? 0,
                        'is_pph21' => $comp->is_pph21 ?? 1
                    ]);
                }
            }
        }

        if (!$hasComponents) {
            // Default Gaji Pokok component
            $this->db->table('pkwt_components')->insert([
                'pkwt_id' => $pkwtId,
                'nama' => 'Gaji Pokok',
                'tipe' => 'pendapatan',
                'nilai' => $basicSalary,
                'is_persentase' => false,
                'is_bpjs' => 1,
                'is_pph21' => 1
            ]);
        }

        // Add global compensation scheme components if configured
        $compensationSchemeId = null;
        if ($config && $config->payroll_scheme_id) {
            $payrollScheme = $this->db->table('payroll_schemes')->where('id', $config->payroll_scheme_id)->get()->getRow();
            if ($payrollScheme && !empty($payrollScheme->compensation_scheme_id)) {
                $compensationSchemeId = $payrollScheme->compensation_scheme_id;
            }
        }
        if (!$compensationSchemeId && $config && $config->compensation_scheme_id) {
            $compensationSchemeId = $config->compensation_scheme_id;
        }

        if ($compensationSchemeId) {
            $compComponents = $this->db->table('compensation_components')
                                       ->where('scheme_id', $compensationSchemeId)
                                       ->get()
                                       ->getResult();
            foreach ($compComponents as $comp) {
                // Check if this component name is already added as part of payroll scheme (to avoid duplicate)
                $dup = $this->db->table('pkwt_components')
                                ->where('pkwt_id', $pkwtId)
                                ->where('nama', $comp->nama)
                                ->get()
                                ->getRow();
                if (!$dup) {
                    $this->db->table('pkwt_components')->insert([
                        'pkwt_id' => $pkwtId,
                        'nama' => $comp->nama,
                        'tipe' => $comp->tipe,
                        'nilai' => $comp->nilai,
                        'is_persentase' => $comp->is_persentase,
                        'jenis_komponen' => $comp->jenis_komponen ?? 'kompensasi',
                        'sifat_kompensasi' => $comp->sifat_kompensasi ?? 'tetap',
                        'sumber_nilai' => $comp->sumber_nilai ?? 'nominal',
                        'periode' => $comp->periode ?? 'bulan',
                        'is_bpjs' => $comp->is_bpjs ?? 0,
                        'is_pph21' => $comp->is_pph21 ?? 1
                    ]);
                }
            }
        }
    }

    public function syncEmployeesToPKWT($clientId = null)
    {
        if (empty($clientId)) {
            return;
        }

        $activeEmployees = $this->db->table('employees')
                                     ->select('employees.nama, employees.gaji_pokok, employees.tgl_masuk, employees.tipe_perjanjian, positions.nama as position_name')
                                     ->join('positions', 'positions.id = employees.position_id', 'left')
                                     ->where('employees.client_id', $clientId)
                                     ->where('employees.status', 'Aktif')
                                     ->get()
                                     ->getResult();

        foreach ($activeEmployees as $emp) {
            // Check if PKWT record already exists for this employee
            $exists = $this->db->table('pkwt')
                               ->where('client_id', $clientId)
                               ->where('employee_name', $emp->nama)
                               ->get()
                               ->getRow();

            if (!$exists) {
                // Create PKWT record
                $tglMulai = !empty($emp->tgl_masuk) ? $emp->tgl_masuk : date('Y-m-d');
                $tglBerakhir = date('Y-m-d', strtotime('+1 year', strtotime($tglMulai)));
                
                $pkwtData = [
                    'client_id' => $clientId,
                    'employee_name' => $emp->nama,
                    'position_name' => $emp->position_name ?? 'Staff',
                    'tipe_perjanjian' => $emp->tipe_perjanjian ?? 'PKWT',
                    'start_date' => $tglMulai,
                    'end_date' => $tglBerakhir,
                    'status' => 'Active',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->table('pkwt')->insert($pkwtData);
                $pkwtId = $this->db->insertID();

                $this->syncPKWTComponents($pkwtId, $emp->gaji_pokok);
            } else {
                if (empty($exists->tipe_perjanjian) && !empty($emp->tipe_perjanjian)) {
                    $this->db->table('pkwt')
                             ->where('id', $exists->id)
                             ->update(['tipe_perjanjian' => $emp->tipe_perjanjian]);
                }
                // ALWAYS synchronize the PKWT components to keep it up to date!
                $this->syncPKWTComponents($exists->id, $emp->gaji_pokok);
            }
        }
    }

    public function exportExcel($periodId)
    {
        $clientId = $this->request->getGet('client_id');
        if (!$clientId) {
            return $this->fail('Client ID required');
        }

        // Fetch client name for filename
        $clientName = 'All_Clients';
        $client = $this->db->table('clients')->where('id', $clientId)->get()->getRow();
        if ($client) {
            $clientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $client->nama);
        }

        // Fetch period name
        $periodName = 'Unknown_Period';
        $period = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        $period = $this->formatPeriodRow($period);
        if ($period) {
            $periodName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $period->nama);
        }

        $filename = "Payroll_{$clientName}_{$periodName}.csv";

        // Query payroll results joining employees to get NIK & bank details
        $query = $this->db->table('payroll_final')
                         ->select('payroll_final.*, pkwt.employee_name, pkwt.position_name, pkwt.tipe_perjanjian, employees.nik, employees.bank_name, employees.no_rekening')
                         ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                         ->join('employees', 'employees.nama = pkwt.employee_name', 'left')
                         ->where('payroll_final.period_id', $periodId)
                         ->where('pkwt.client_id', $clientId);

        $data = $query->get()->getResultArray();

        // Stream the CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM for proper Excel encoding
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Define Headers
        $headers = [
            'No', 'NIK', 'Nama Karyawan', 'Tipe Perjanjian', 'Jabatan', 'Metode Pajak', 'Status PTKP',
            'Gaji Pokok', 'Tunjangan Lembur', 'Early Arrival', 'Rapel', 'Bonus Tambahan', 'Tunjangan Lainnya',
            'Total Pendapatan', 'Potongan Absensi', 'BPJS Kes (Karyawan)', 'BPJS JHT (Karyawan)',
            'BPJS JP (Karyawan)', 'PPh 21', 'Potongan Lainnya', 'Total Potongan',
            'Take Home Pay (THP)', 'Nama Bank', 'No Rekening'
        ];
        fputcsv($output, $headers);
        
        $no = 1;
        foreach ($data as $row) {
            // Compute Tunjangan Lainnya
            $lemburPay = floatval($row['lembur_pay'] ?? 0);
            $bonus = floatval($row['bonus_tambahan'] ?? 0);
            $taxAllowance = floatval($row['tax_allowance'] ?? 0);
            $totalPendapatan = floatval($row['total_pendapatan'] ?? 0);
            $gajiPokok = floatval($row['gaji_pokok'] ?? 0);
            $ea = floatval($row['early_arrival_pay'] ?? 0);
            
            // Extract rapel
            $rapelVal = 0.0;
            if (!empty($row['raw_components'])) {
                try {
                    $comps = json_decode($row['raw_components'], true);
                    if (is_array($comps)) {
                        foreach ($comps as $c) {
                            if (isset($c['nama']) && stripos($c['nama'], 'rapel') !== false) {
                                $rapelVal += floatval($c['nilai'] ?? 0);
                            }
                        }
                    }
                } catch (\Exception $e) {
                }
            }
            
            $tunjanganLainnya = $totalPendapatan - ($gajiPokok + $lemburPay + $bonus + $ea + $rapelVal + $taxAllowance);
            if ($tunjanganLainnya < 0) $tunjanganLainnya = 0;
            
            // Compute Potongan Lainnya
            $bpjsKes = floatval($row['bpjs_kes_karyawan'] ?? 0);
            $bpjsJht = floatval($row['bpjs_jht_karyawan'] ?? 0);
            $bpjsJp = floatval($row['bpjs_jp_karyawan'] ?? 0);
            $pph21 = floatval($row['pph21'] ?? 0);
            $potonganAbsen = floatval($row['potongan_absen'] ?? 0);
            $totalPotongan = floatval($row['total_potongan'] ?? 0);
            
            $taxMethod = $row['tax_method'] ?? 'Gross';
            $pajakDikurangi = ($taxMethod === 'Net') ? 0 : $pph21;
            
            $potonganLainnya = $totalPotongan - ($potonganAbsen + $bpjsKes + $bpjsJht + $bpjsJp + $pajakDikurangi);
            if ($potonganLainnya < 0) $potonganLainnya = 0;
            
            fputcsv($output, [
                $no++,
                $row['nik'] ?? '-',
                $row['employee_name'],
                $row['tipe_perjanjian'] ?? 'PKWT',
                $row['position_name'] ?? '-',
                $taxMethod,
                $row['ptkp_status'] ?? '-',
                'Rp ' . number_format($gajiPokok, 0, ',', '.'),
                'Rp ' . number_format($lemburPay, 0, ',', '.'),
                'Rp ' . number_format($ea, 0, ',', '.'),
                'Rp ' . number_format($rapelVal, 0, ',', '.'),
                'Rp ' . number_format($bonus, 0, ',', '.'),
                'Rp ' . number_format($tunjanganLainnya, 0, ',', '.'),
                'Rp ' . number_format($totalPendapatan, 0, ',', '.'),
                'Rp ' . number_format($potonganAbsen, 0, ',', '.'),
                'Rp ' . number_format($bpjsKes, 0, ',', '.'),
                'Rp ' . number_format($bpjsJht, 0, ',', '.'),
                'Rp ' . number_format($bpjsJp, 0, ',', '.'),
                'Rp ' . number_format($pph21, 0, ',', '.'),
                'Rp ' . number_format($potonganLainnya, 0, ',', '.'),
                'Rp ' . number_format($totalPotongan, 0, ',', '.'),
                'Rp ' . number_format(floatval($row['take_home_pay'] ?? 0), 0, ',', '.'),
                $row['bank_name'] ?? '-',
                $row['no_rekening'] ?? '-'
            ]);
        }
        
        fclose($output);
        exit();
    }

    private function formatPeriodRow($row)
    {
        if (!$row) return $row;
        $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        
        if (is_array($row)) {
            if (empty($row['nama']) && isset($row['bulan'], $row['tahun'])) {
                $row['nama'] = ($months[intval($row['bulan']) - 1] ?? '') . " " . $row['tahun'];
            }
            if (!isset($row['status']) && isset($row['status_cutoff'])) {
                $row['status'] = $row['status_cutoff'];
            }
            // Calculate dynamic start_date and end_date from client cutoff config
            if (isset($row['client_id'])) {
                $dates = $this->calculatePeriodDates(intval($row['bulan']), intval($row['tahun']), $row['client_id']);
                $row['start_date'] = $dates[0];
                $row['end_date'] = $dates[1];
            }
            return $row;
        } else if (is_object($row)) {
            if (empty($row->nama) && isset($row->bulan, $row->tahun)) {
                $row->nama = ($months[intval($row->bulan) - 1] ?? '') . " " . $row->tahun;
            }
            if (!isset($row->status) && isset($row->status_cutoff)) {
                $row->status = $row->status_cutoff;
            }
            // Calculate dynamic start_date and end_date from client cutoff config
            if (isset($row->client_id)) {
                $dates = $this->calculatePeriodDates(intval($row->bulan), intval($row->tahun), $row->client_id);
                $row->start_date = $dates[0];
                $row->end_date = $dates[1];
            }
            return $row;
        }
        return $row;
    }

    /**
     * Calculate dynamic period start/end dates from client cutoff configuration.
     * For cutoff_start=5 cutoff_end=4, period June 2026 = May 5 to June 4.
     */
    private function calculatePeriodDates($bulan, $tahun, $clientId)
    {
        $config = $this->db->table('client_payroll_configs')
                           ->where('client_id', $clientId)
                           ->where('division_id IS NULL')
                           ->where('department_id IS NULL')
                           ->where('position_id IS NULL')
                           ->get()->getRow();
        
        $cutoffStart = ($config && isset($config->cutoff_start)) ? intval($config->cutoff_start) : 21;
        $cutoffEnd = ($config && isset($config->cutoff_end)) ? intval($config->cutoff_end) : 20;
        
        if ($cutoffStart <= 0) $cutoffStart = 1;
        if ($cutoffEnd <= 0) {
            $cutoffEnd = $cutoffStart - 1;
            if ($cutoffEnd < 1) $cutoffEnd = 31;
        }

        $tMonth = intval($bulan);
        $tYear = intval($tahun);

        $endDay = min($cutoffEnd, date('t', mktime(0, 0, 0, $tMonth, 1, $tYear)));
        $endDateStr = sprintf('%04d-%02d-%02d', $tYear, $tMonth, $endDay);

        $bulan_start = $tMonth;
        $tahun_start = $tYear;
        if ($cutoffStart > $cutoffEnd && $cutoffStart > 1) {
            $bulan_start--;
            if ($bulan_start < 1) {
                $bulan_start = 12;
                $tahun_start--;
            }
        }
        $startDateStr = sprintf('%04d-%02d-%02d', $tahun_start, $bulan_start, $cutoffStart);
        
        return [$startDateStr, $endDateStr];
    }

    public function getSettings()
    {
        $settings = $this->db->table('system_settings')->get()->getResultArray();

        // Get company payroll settings for early arrival
        $companySetting = $this->db->table('company_payroll_setting')->get()->getRowArray();
        if ($companySetting) {
            $settings[] = [
                'setting_key' => 'early_arrival_enabled',
                'setting_value' => strval($companySetting['early_arrival_enabled']),
                'updated_at' => $companySetting['updated_at'] ?? null
            ];
            $settings[] = [
                'setting_key' => 'max_early_arrival_minutes',
                'setting_value' => strval($companySetting['max_early_arrival_minutes']),
                'updated_at' => $companySetting['updated_at'] ?? null
            ];
            $settings[] = [
                'setting_key' => 'early_arrival_min_minutes',
                'setting_value' => strval($companySetting['early_arrival_min_minutes'] ?? 30),
                'updated_at' => $companySetting['updated_at'] ?? null
            ];
            $settings[] = [
                'setting_key' => 'early_arrival_calculation_unit',
                'setting_value' => strval($companySetting['early_arrival_calculation_unit'] ?? 60),
                'updated_at' => $companySetting['updated_at'] ?? null
            ];
            $settings[] = [
                'setting_key' => 'early_arrival_rounding_method',
                'setting_value' => strval($companySetting['early_arrival_rounding_method'] ?? 'CEILING'),
                'updated_at' => $companySetting['updated_at'] ?? null
            ];
        }

        return $this->respond($settings);
    }

    public function saveSettings()
    {
        $data = $this->request->getJSON(true);
        if (!is_array($data)) {
            return $this->fail('Invalid JSON data format');
        }

        $companySettingsUpdate = [];
        $systemSettingsData = [];

        foreach ($data as $key => $val) {
            if ($key === 'early_arrival_enabled') {
                $companySettingsUpdate['early_arrival_enabled'] = ($val === 'true' || $val === '1' || $val === true || $val === 1) ? 1 : 0;
            } elseif ($key === 'max_early_arrival_minutes') {
                $companySettingsUpdate['max_early_arrival_minutes'] = intval($val);
            } elseif ($key === 'early_arrival_min_minutes') {
                $companySettingsUpdate['early_arrival_min_minutes'] = intval($val);
            } elseif ($key === 'early_arrival_calculation_unit') {
                $companySettingsUpdate['early_arrival_calculation_unit'] = intval($val);
            } elseif ($key === 'early_arrival_rounding_method') {
                $companySettingsUpdate['early_arrival_rounding_method'] = strval($val);
            } else {
                $systemSettingsData[$key] = $val;
            }
        }

        if (!empty($companySettingsUpdate)) {
            $companySettingsUpdate['updated_at'] = date('Y-m-d H:i:s');
            $exists = $this->db->table('company_payroll_setting')->get()->getRow();
            if ($exists) {
                $this->db->table('company_payroll_setting')->update($companySettingsUpdate);
            } else {
                $this->db->table('company_payroll_setting')->insert($companySettingsUpdate);
            }
        }

        foreach ($systemSettingsData as $key => $val) {
            $exists = $this->db->table('system_settings')->where('setting_key', $key)->get()->getRow();
            if ($exists) {
                $this->db->table('system_settings')->where('setting_key', $key)->update([
                    'setting_value' => strval($val),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->db->table('system_settings')->insert([
                    'setting_key' => $key,
                    'setting_value' => strval($val)
                ]);
            }
        }

        $this->logActivity("Memperbarui konfigurasi sistem");
        return $this->respond(['message' => 'Settings saved successfully']);
    }
    // --- SHIFT SCHEMES ---
    public function getShiftSchemes()
    {
        $schemes = $this->db->table('shift_schemes')->get()->getResultArray();
        return $this->respond($schemes);
    }

    public function createShiftScheme()
    {
        $data = $this->request->getJSON(true);
        if (empty($data['name']) || empty($data['start_time']) || empty($data['end_time'])) {
            return $this->failValidationErrors('Name, start time, dan end time wajib diisi');
        }

        $duration = 8.0;
        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            try {
                $startTime = new \DateTime($data['start_time']);
                $endTime = new \DateTime($data['end_time']);
                if ($endTime < $startTime) {
                    $endTime->modify('+1 day');
                }
                $diff = $startTime->diff($endTime);
                $duration = floatval(number_format($diff->h + ($diff->i / 60), 1));
            } catch (\Exception $e) {
                $duration = isset($data['duration']) ? floatval($data['duration']) : 8.0;
            }
        }

        $insertData = [
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'duration' => $duration,
            'grace_period_late' => isset($data['grace_period_late']) ? intval($data['grace_period_late']) : 0,
            'grace_period_early' => isset($data['grace_period_early']) ? intval($data['grace_period_early']) : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('shift_schemes')->insert($insertData);
        $this->logActivity("Membuat skema shift baru: " . $data['name']);
        return $this->respondCreated(['message' => 'Skema shift berhasil dibuat']);
    }

    public function updateShiftScheme($id)
    {
        $data = $this->request->getJSON(true);
        if (empty($data['name']) || empty($data['start_time']) || empty($data['end_time'])) {
            return $this->failValidationErrors('Name, start time, dan end time wajib diisi');
        }

        $duration = 8.0;
        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            try {
                $startTime = new \DateTime($data['start_time']);
                $endTime = new \DateTime($data['end_time']);
                if ($endTime < $startTime) {
                    $endTime->modify('+1 day');
                }
                $diff = $startTime->diff($endTime);
                $duration = floatval(number_format($diff->h + ($diff->i / 60), 1));
            } catch (\Exception $e) {
                $duration = isset($data['duration']) ? floatval($data['duration']) : 8.0;
            }
        }

        $updateData = [
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'duration' => $duration,
            'grace_period_late' => isset($data['grace_period_late']) ? intval($data['grace_period_late']) : 0,
            'grace_period_early' => isset($data['grace_period_early']) ? intval($data['grace_period_early']) : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('shift_schemes')->where('id', $id)->update($updateData);
        $this->logActivity("Mengubah skema shift ID: " . $id);
        return $this->respond(['message' => 'Skema shift berhasil diupdate']);
    }

    public function deleteShiftScheme($id)
    {
        $referenced = $this->db->table('employee_shifts')->where('shift_scheme_id', $id)->get()->getRow();
        if ($referenced) {
            return $this->fail('Skema shift ini sedang digunakan oleh karyawan dan tidak dapat dihapus');
        }

        $this->db->table('shift_schemes')->where('id', $id)->delete();
        $this->logActivity("Menghapus skema shift ID: " . $id);
        return $this->respondDeleted(['message' => 'Skema shift berhasil dihapus']);
    }

    // --- EMPLOYEE SHIFTS ---
    public function getEmployeeShifts()
    {
        $employeeId = $this->request->getGet('employee_id');
        $builder = $this->db->table('employee_shifts')
            ->select('employee_shifts.*, shift_schemes.name as shift_name, shift_schemes.start_time, shift_schemes.end_time, employees.nama as employee_name')
            ->join('shift_schemes', 'shift_schemes.id = employee_shifts.shift_scheme_id', 'inner')
            ->join('employees', 'employees.id = employee_shifts.employee_id', 'inner');

        if (!empty($employeeId)) {
            $builder->where('employee_shifts.employee_id', intval($employeeId));
        }

        $shifts = $builder->orderBy('employee_shifts.start_date', 'DESC')->get()->getResultArray();
        return $this->respond($shifts);
    }

    public function assignEmployeeShift()
    {
        $data = $this->request->getJSON(true);
        if (empty($data['employee_id']) || empty($data['shift_scheme_id']) || empty($data['start_date'])) {
            return $this->failValidationErrors('Employee ID, Shift Scheme ID, dan Start Date wajib diisi');
        }

        $empId = intval($data['employee_id']);
        $shiftId = intval($data['shift_scheme_id']);
        $startDate = $data['start_date'];
        $endDate = !empty($data['end_date']) ? $data['end_date'] : null;

        $yesterday = date('Y-m-d', strtotime($startDate . ' -1 day'));

        $activeShifts = $this->db->table('employee_shifts')
            ->where('employee_id', $empId)
            ->where("(end_date IS NULL OR end_date >= '{$startDate}')")
            ->get()->getResultArray();

        foreach ($activeShifts as $active) {
            if ($active['start_date'] < $startDate) {
                $this->db->table('employee_shifts')
                    ->where('id', $active['id'])
                    ->update([
                        'end_date' => $yesterday,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            } else {
                $this->db->table('employee_shifts')
                    ->where('id', $active['id'])
                    ->delete();
            }
        }

        $insertData = [
            'employee_id' => $empId,
            'shift_scheme_id' => $shiftId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('employee_shifts')->insert($insertData);
        $this->logActivity("Menugaskan skema shift ke karyawan ID: " . $empId);
        return $this->respondCreated(['message' => 'Shift berhasil ditugaskan ke karyawan']);
    }

    public function deleteEmployeeShift($id)
    {
        $this->db->table('employee_shifts')->where('id', $id)->delete();
        $this->logActivity("Menghapus alokasi shift karyawan ID: " . $id);
        return $this->respondDeleted(['message' => 'Alokasi shift berhasil dihapus']);
    }

    private function syncPayrollAttendanceOvertime($employeeId, $date)
    {
        $employee = $this->db->table('employees')->where('id', intval($employeeId))->get()->getRow();
        if (!$employee) return;
        $clientId = $employee->client_id;

        $periods = $this->db->table('payroll_periods')
                            ->where('client_id', $clientId)
                            ->get()->getResult();

        foreach ($periods as $period) {
            $clientConfig = $this->db->table('client_payroll_configs')
                                 ->where('client_id', $clientId)
                                 ->get()->getRow();
            $daysInMonth = date('t', mktime(0, 0, 0, intval($period->bulan), 1, intval($period->tahun)));
            $cutoffStart = $clientConfig ? intval($clientConfig->cutoff_start) : 21;
            $cutoffEnd = $clientConfig ? intval($clientConfig->cutoff_end) : 20;

            if ($cutoffStart <= 0) $cutoffStart = 1;
            if ($cutoffEnd <= 0) {
                $cutoffEnd = $cutoffStart - 1;
                if ($cutoffEnd < 1) $cutoffEnd = 31;
            }

            $prevMonth = intval($period->bulan) - 1;
            $prevYear = intval($period->tahun);
            if ($prevMonth == 0) {
                $prevMonth = 12;
                $prevYear--;
            }
            $startDateStr = sprintf('%04d-%02d-01', $prevYear, $prevMonth);
            $endDateStr = date('Y-m-t', strtotime($startDateStr));

            $effectiveStartDateStr = !empty($employee->tgl_masuk) ? max($startDateStr, date('Y-m-d', strtotime($employee->tgl_masuk))) : $startDateStr;

            if ($date >= $effectiveStartDateStr && $date <= $endDateStr) {
                // Query regular and holiday overtime separately
                $lemburBiasaObj = $this->db->table('overtime_logs')
                                         ->selectSum('jam_lembur')
                                         ->where('employee_id', $employeeId)
                                         ->where('tanggal >=', $effectiveStartDateStr)
                                         ->where('tanggal <=', $endDateStr)
                                         ->where('status', 'Approved')
                                         ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                                         ->where('(is_holiday = 0 OR is_holiday IS NULL)')
                                         ->get()->getRow();
                $lemburBiasa = $lemburBiasaObj ? floatval($lemburBiasaObj->jam_lembur) : 0.0;

                $lemburLiburObj = $this->db->table('overtime_logs')
                                         ->selectSum('jam_lembur')
                                         ->where('employee_id', $employeeId)
                                         ->where('tanggal >=', $effectiveStartDateStr)
                                         ->where('tanggal <=', $endDateStr)
                                         ->where('status', 'Approved')
                                         ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                                         ->where('is_holiday', 1)
                                         ->get()->getRow();
                $lemburLibur = $lemburLiburObj ? floatval($lemburLiburObj->jam_lembur) : 0.0;
                $lemburLibur = $lemburLiburObj ? floatval($lemburLiburObj->jam_lembur) : 0.0;
                $lemburSum = $lemburBiasa + $lemburLibur;

                $pkwt = $this->db->table('pkwt')
                                 ->where('client_id', $clientId)
                                 ->where('employee_name', $employee->nama)
                                 ->get()->getRow();
                if (!$pkwt) continue;

                $existing = $this->db->table('payroll_attendance')
                                     ->where('period_id', $period->id)
                                     ->where('pkwt_id', $pkwt->id)
                                     ->get()->getRow();

                if ($existing && intval($existing->is_manual ?? 0) === 1) {
                    continue;
                }

                if ($existing) {
                    $this->db->table('payroll_attendance')
                             ->where('id', $existing->id)
                             ->update([
                                 'jam_lembur' => $lemburSum,
                                 'jam_lembur_hari_biasa' => $lemburBiasa,
                                 'jam_lembur_hari_libur' => $lemburLibur,
                             ]);
                } else {
                    $hasAnyClientLogs = $this->db->table('attendance_logs')
                                                 ->join('employees', 'employees.id = attendance_logs.employee_id')
                                                 ->where('employees.client_id', $clientId)
                                                 ->where('attendance_logs.log_date >=', $startDateStr)
                                                 ->where('attendance_logs.log_date <=', $endDateStr)
                                                 ->countAllResults();

                    if ($hasAnyClientLogs > 0) {
                        $stdWorkingDays = 0;
                    } else {
                        $stdWorkingDays = 20;
                        $posHk = 5;
                        if ($employee->position_id) {
                            $pos = $this->db->table('positions')->where('id', $employee->position_id)->get()->getRow();
                            if ($pos && isset($pos->hari_kerja)) {
                                $posHk = intval($pos->hari_kerja);
                            }
                        }
                        if ($posHk === 6) {
                            $stdWorkingDays = 25;
                        } elseif ($posHk === 7) {
                            $stdWorkingDays = 30;
                        }
                        
                        if (isset($employee->hari_kerja) && intval($employee->hari_kerja) > 0) {
                            $stdWorkingDays = ($employee->hari_kerja === 6) ? 25 : (($employee->hari_kerja === 7) ? 30 : 20);
                        }
                    }

                    $this->db->table('payroll_attendance')->insert([
                        'period_id' => $period->id,
                        'pkwt_id' => $pkwt->id,
                        'hari_kerja' => $stdWorkingDays,
                        'jam_lembur' => $lemburSum,
                        'jam_lembur_hari_biasa' => $lemburBiasa,
                        'jam_lembur_hari_libur' => $lemburLibur,
                        'potongan_absensi' => 0.0,
                        'bonus_tambahan' => 0.0
                    ]);
                }
            }
        }
    }

    private function resolveCutoffStartEnd($clientConfig)
    {
        $cutoffStart = $clientConfig ? intval($clientConfig->cutoff_start) : 21;
        $cutoffEnd = $clientConfig ? intval($clientConfig->cutoff_end) : 20;

        if ($clientConfig) {
            $startField = "cutoff_gaji_pokok_start";
            $endField = "cutoff_gaji_pokok_end";
            $refField = "cutoff_gaji_pokok_schedule_ref";
            
            $start = isset($clientConfig->$startField) ? intval($clientConfig->$startField) : null;
            $end = isset($clientConfig->$endField) ? intval($clientConfig->$endField) : null;
            $refId = isset($clientConfig->$refField) ? intval($clientConfig->$refField) : null;
            
            if ($refId) {
                $sched = $this->db->table('payroll_schedules')->where('id', $refId)->get()->getRow();
                if ($sched) {
                    $start = intval($sched->cutoff_start);
                    $end = intval($sched->cutoff_end);
                }
            }
            
            if ($start === null) {
                if (isset($clientConfig->cutoff_start)) {
                    $start = intval($clientConfig->cutoff_start);
                } else {
                    $start = 21;
                }
            }
            if ($end === null) {
                if (isset($clientConfig->cutoff_end)) {
                    $end = intval($clientConfig->cutoff_end);
                } else {
                    $end = $start - 1;
                    if ($end < 1) $end = 31;
                }
            }
            
            $cutoffStart = $start;
            $cutoffEnd = $end;
        }

        if ($cutoffStart <= 0) $cutoffStart = 1;
        if ($cutoffEnd <= 0) {
            $cutoffEnd = $cutoffStart - 1;
            if ($cutoffEnd < 1) $cutoffEnd = 31;
        }

        return ['start' => $cutoffStart, 'end' => $cutoffEnd];
    }

    private function syncPayrollAttendanceOvertimeForLog($id)
    {
        $log = $this->db->table('overtime_logs')->where('id', intval($id))->get()->getRow();
        if ($log) {
            $this->syncPayrollAttendanceOvertime($log->employee_id, $log->tanggal);
        }
    }

    private function syncOvertimeToPayrollAttendance($periodId, $clientId = null)
    {
        $period = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        if (!$period) return;

        $pkwtQuery = $this->db->table('pkwt');
        if ($clientId) {
            $pkwtQuery->where('client_id', intval($clientId));
        }
        $pkwts = $pkwtQuery->get()->getResult();

        foreach ($pkwts as $pkwt) {
            // Skip sync if manually overridden
            $existing = $this->db->table('payroll_attendance')
                                 ->where('period_id', $periodId)
                                 ->where('pkwt_id', $pkwt->id)
                                 ->get()->getRow();
            if ($existing && intval($existing->is_manual ?? 0) === 1) {
                continue;
            }

            $emp = $this->db->table('employees')
                            ->where('client_id', $pkwt->client_id)
                            ->where('nama', $pkwt->employee_name)
                            ->get()->getRow();
            if (!$emp) continue;

            $clientConfig = $this->resolveClientConfig($pkwt->client_id, $pkwt->position_name);
            $prevMonth = intval($period->bulan) - 1;
            $prevYear = intval($period->tahun);
            if ($prevMonth == 0) {
                $prevMonth = 12;
                $prevYear--;
            }
            $startDateStr = sprintf('%04d-%02d-01', $prevYear, $prevMonth);
            $endDateStr = date('Y-m-t', strtotime($startDateStr));

            $effectiveStartDateStr = !empty($emp->tgl_masuk) ? max($startDateStr, date('Y-m-d', strtotime($emp->tgl_masuk))) : $startDateStr;

            // 2a. Lembur Hari Kerja (is_holiday = 0 OR is_holiday IS NULL)
            $lemburBiasaObj = $this->db->table('overtime_logs')
                                     ->selectSum('jam_lembur')
                                     ->where('employee_id', $emp->id)
                                     ->where('tanggal >=', $effectiveStartDateStr)
                                     ->where('tanggal <=', $endDateStr)
                                     ->where('status', 'Approved')
                                     ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                                     ->where('(is_holiday = 0 OR is_holiday IS NULL)')
                                     ->get()->getRow();
            $lemburBiasa = $lemburBiasaObj ? floatval($lemburBiasaObj->jam_lembur) : 0.0;

            // 2b. Lembur Hari Libur (is_holiday = 1)
            $lemburLiburObj = $this->db->table('overtime_logs')
                                     ->selectSum('jam_lembur')
                                     ->where('employee_id', $emp->id)
                                     ->where('tanggal >=', $effectiveStartDateStr)
                                     ->where('tanggal <=', $endDateStr)
                                     ->where('status', 'Approved')
                                     ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                                     ->where('is_holiday', 1)
                                     ->get()->getRow();
            $lemburLibur = $lemburLiburObj ? floatval($lemburLiburObj->jam_lembur) : 0.0;

            $lemburSum = $lemburBiasa + $lemburLibur;

            // Calculate actual present days from attendance_logs
            $actualHadir = $this->db->table('attendance_logs')
                                    ->where('employee_id', $emp->id)
                                    ->where('log_date >=', $effectiveStartDateStr)
                                    ->where('log_date <=', $endDateStr)
                                    ->where('status', 'Hadir')
                                    ->where('(is_rapel = 0 OR is_rapel IS NULL)')
                                    ->countAllResults();

            // Check if there are any logs uploaded for this client in this period range
            $hasAnyClientLogs = $this->db->table('attendance_logs')
                                         ->join('employees', 'employees.id = attendance_logs.employee_id')
                                         ->where('employees.client_id', $pkwt->client_id)
                                         ->where('attendance_logs.log_date >=', $startDateStr)
                                         ->where('attendance_logs.log_date <=', $endDateStr)
                                         ->countAllResults();

            if ($actualHadir > 0) {
                $hariKerjaVal = $actualHadir;
            } else {
                if ($hasAnyClientLogs > 0) {
                    // Logs were uploaded, but this employee had 0 present days (e.g. not present or not in Excel)
                    $hariKerjaVal = 0;
                } else {
                    // No logs uploaded yet for this client, fall back to standard working days
                    $posHk = 5;
                    if ($emp->position_id) {
                        $pos = $this->db->table('positions')->where('id', $emp->position_id)->get()->getRow();
                        if ($pos && isset($pos->hari_kerja)) {
                            $posHk = intval($pos->hari_kerja);
                        }
                    }
                    if (isset($emp->hari_kerja) && intval($emp->hari_kerja) > 0) {
                        $posHk = intval($emp->hari_kerja);
                    }
                    $stdWorkingDays = $this->getStandardWorkingDaysInRange($startDateStr, $endDateStr, $posHk);
                    $hariKerjaVal = $stdWorkingDays;
                }
            }

            $isJoinedPrevMonth = false;
            $isActiveRegularPrevMonth = false;
            if ($emp && !empty($emp->tgl_masuk)) {
                $joinTs = strtotime($emp->tgl_masuk);
                $joinYear = intval(date('Y', $joinTs));
                $joinMonth = intval(date('n', $joinTs));
                
                $prevMonth = intval($period->bulan) - 1;
                $prevYear = intval($period->tahun);
                if ($prevMonth == 0) {
                    $prevMonth = 12;
                    $prevYear--;
                }
                if ($joinYear === $prevYear && $joinMonth === $prevMonth) {
                    $cutoffStartEnd = $this->resolveCutoffStartEnd($clientConfig);
                    $joinDateStr = date('Y-m-d', $joinTs);
                    $prevCutoffEndDate = sprintf('%04d-%02d-%02d', $prevYear, $prevMonth, $cutoffStartEnd['end']);
                    if ($joinDateStr > $prevCutoffEndDate) {
                        $isJoinedPrevMonth = true;
                    } else {
                        // Joined in previous month before cutoff. Check if active in current month (dates 1 to cutoffEnd)
                        $currentMonthStartStr = sprintf('%04d-%02d-01', $period->tahun, $period->bulan);
                        $currentCutoffEndStr = sprintf('%04d-%02d-%02d', $period->tahun, $period->bulan, $cutoffStartEnd['end']);
                        $currentLogsCount = $this->db->table('attendance_logs')
                                                     ->where('employee_id', $emp->id)
                                                     ->where('log_date >=', $currentMonthStartStr)
                                                     ->where('log_date <=', $currentCutoffEndStr)
                                                     ->countAllResults();
                        
                        $prevCutoffStartStr = date('Y-m-d', strtotime('+1 day', strtotime($prevCutoffEndDate)));
                        $prevMonthLogsCount = $this->db->table('attendance_logs')
                                                     ->where('employee_id', $emp->id)
                                                     ->where('log_date >=', $prevCutoffStartStr)
                                                     ->where('log_date <', $currentMonthStartStr)
                                                     ->countAllResults();
                        
                        if ($currentLogsCount > 0 && $prevMonthLogsCount === 0) {
                            $isActiveRegularPrevMonth = true;
                        }
                    }
                }
            }

            if ($isJoinedPrevMonth || $isActiveRegularPrevMonth) {
                if ($actualHadir === 0 && $hasAnyClientLogs === 0) {
                    $posHk = 5;
                    if ($emp->position_id) {
                        $pos = $this->db->table('positions')->where('id', $emp->position_id)->get()->getRow();
                        if ($pos && isset($pos->hari_kerja)) {
                            $posHk = intval($pos->hari_kerja);
                        }
                    }
                    if (isset($emp->hari_kerja) && intval($emp->hari_kerja) > 0) {
                        $posHk = intval($emp->hari_kerja);
                    }
                    $stdWorkingDays = $this->getStandardWorkingDaysInRange($startDateStr, $endDateStr, $posHk);
                    $hariKerjaVal = $stdWorkingDays;
                }
            }

            $existing = $this->db->table('payroll_attendance')
                                 ->where('period_id', $periodId)
                                 ->where('pkwt_id', $pkwt->id)
                                 ->get()->getRow();

            if ($existing) {
                $updateData = [
                    'jam_lembur' => $lemburSum,
                    'jam_lembur_hari_biasa' => $lemburBiasa,
                    'jam_lembur_hari_libur' => $lemburLibur,
                    'hari_kerja' => $hariKerjaVal
                ];
                if ($isJoinedPrevMonth || $isActiveRegularPrevMonth) {
                    $updateData['potongan_absensi'] = 0.0;
                }
                $this->db->table('payroll_attendance')
                         ->where('id', $existing->id)
                         ->update($updateData);
            } else {
                $this->db->table('payroll_attendance')->insert([
                    'period_id' => $periodId,
                    'pkwt_id' => $pkwt->id,
                    'hari_kerja' => $hariKerjaVal,
                    'jam_lembur' => $lemburSum,
                    'jam_lembur_hari_biasa' => $lemburBiasa,
                    'jam_lembur_hari_libur' => $lemburLibur,
                    'potongan_absensi' => 0.0,
                    'bonus_tambahan' => 0.0
                ]);
            }
        }
    }

    private function syncEarlyArrivalToPayrollAttendance($periodId, $clientId = null)
    {
        $period = $this->db->table('payroll_periods')->where('id', $periodId)->get()->getRow();
        if (!$period) return;

        $pkwtQuery = $this->db->table('pkwt');
        if ($clientId) {
            $pkwtQuery->where('client_id', intval($clientId));
        }
        $pkwts = $pkwtQuery->get()->getResult();

        foreach ($pkwts as $pkwt) {
            // Skip sync if manually overridden
            $existing = $this->db->table('payroll_attendance')
                                 ->where('period_id', $periodId)
                                 ->where('pkwt_id', $pkwt->id)
                                 ->get()->getRow();
            if ($existing && intval($existing->is_manual ?? 0) === 1) {
                continue;
            }

            $emp = $this->db->table('employees')
                            ->where('client_id', $pkwt->client_id)
                            ->where('nama', $pkwt->employee_name)
                            ->get()->getRow();
            if (!$emp) continue;

            $clientConfig = $this->resolveClientConfig($pkwt->client_id, $pkwt->position_name);

            // Use cutoff dates from client config (consistent with getAttendance)
            $cutoffStartEnd = $this->resolveCutoffStartEnd($clientConfig);
            $cutoffStart = $cutoffStartEnd['start'];
            $cutoffEnd = $cutoffStartEnd['end'];

            $bulan_start = intval($period->bulan);
            $tahun_start = intval($period->tahun);
            $bulan_end = $bulan_start;
            $tahun_end = $tahun_start;
            if ($cutoffStart > $cutoffEnd && $cutoffStart > 1) {
                $bulan_start -= 1;
                if ($bulan_start < 1) {
                    $bulan_start = 12;
                    $tahun_start -= 1;
                }
            }
            $daysInStartMonth = date('t', mktime(0, 0, 0, $bulan_start, 1, $tahun_start));
            $daysInEndMonth = date('t', mktime(0, 0, 0, $bulan_end, 1, $tahun_end));
            $effectiveCutoffStart = min($cutoffStart, $daysInStartMonth);
            $effectiveCutoffEnd = min($cutoffEnd, $daysInEndMonth);
            $startDateStr = sprintf('%04d-%02d-%02d', $tahun_start, $bulan_start, $effectiveCutoffStart);
            $endDateStr = sprintf('%04d-%02d-%02d', $tahun_end, $bulan_end, $effectiveCutoffEnd);

            $effectiveStartDateStr = !empty($emp->tgl_masuk) ? max($startDateStr, date('Y-m-d', strtotime($emp->tgl_masuk))) : $startDateStr;

            // Also match by payroll_period column for records assigned to this period
            $payoutPeriodStr = intval($period->bulan) . '-' . intval($period->tahun);
            $payoutPeriodStrPadded = sprintf('%02d/%04d', intval($period->bulan), intval($period->tahun));

            $eaSumObj = $this->db->table('early_arrival')
                                 ->selectSum('eligible_minutes')
                                 ->where('employee_id', $emp->id)
                                 ->where('status', 'APPROVED')
                                 ->groupStart()
                                     ->groupStart()
                                         ->where('date >=', $effectiveStartDateStr)
                                         ->where('date <=', $endDateStr)
                                     ->groupEnd()
                                     ->orWhere('payroll_period', $payoutPeriodStr)
                                     ->orWhere('payroll_period', $payoutPeriodStrPadded)
                                 ->groupEnd()
                                 ->get()->getRow();
            $eaSum = $eaSumObj ? intval($eaSumObj->eligible_minutes) : 0;

            $existing = $this->db->table('payroll_attendance')
                                 ->where('period_id', $periodId)
                                 ->where('pkwt_id', $pkwt->id)
                                 ->get()->getRow();

            if ($existing) {
                $this->db->table('payroll_attendance')
                         ->where('id', $existing->id)
                         ->update([
                             'early_arrival_minutes' => $eaSum
                         ]);
            } else {
                $this->db->table('payroll_attendance')->insert([
                    'period_id' => $periodId,
                    'pkwt_id' => $pkwt->id,
                    'hari_kerja' => 20,
                    'jam_lembur' => 0.0,
                    'early_arrival_minutes' => $eaSum,
                    'potongan_absensi' => 0.0,
                    'bonus_tambahan' => 0.0
                ]);
            }
        }
    }
}


<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Config\Database;
use Config\Services;

class Ai extends ResourceController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Helper to send request to Google Gemini API
     */
    private function callGemini($prompt, $systemInstruction = '', $enableSearch = false)
    {
        $apiKey = env('GEMINI_API_KEY') ?: getenv('GEMINI_API_KEY');
        
        // Fallback jika API Key tidak diset
        if (empty($apiKey)) {
            return $this->getMockResponse($prompt);
        }

        // Tipe model: gunakan gemini-2.5-flash sebagai default tercepat dan termurah
        $model = 'gemini-2.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $client = Services::curlrequest();

        $contents = [];
        $contents[] = [
            'parts' => [
                ['text' => $prompt]
            ]
        ];

        $payload = [
            'contents' => $contents
        ];

        // Tambahkan tools (Google Search grounding) jika diaktifkan
        if ($enableSearch) {
            $payload['tools'] = [
                [
                    'googleSearch' => (object)[]
                ]
            ];
        }

        // Tambahkan systemInstruction jika diset
        if (!empty($systemInstruction)) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ];
        }

        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $payload,
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

            if ($statusCode !== 200) {
                log_message('error', "Gemini API Error (status {$statusCode}): " . $body);
                return "Maaf, terjadi kesalahan saat menghubungi server AI (HTTP Status {$statusCode}). Silakan periksa konfigurasi API Key Anda.";
            }

            $result = json_decode($body, true);
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return $result['candidates'][0]['content']['parts'][0]['text'];
            }

            return "Maaf, AI tidak mengembalikan respon yang valid.";
        } catch (\Exception $e) {
            log_message('error', "Gemini Connection Exception: " . $e->getMessage());
            return "Koneksi ke AI terputus. Silakan coba beberapa saat lagi. Detail error: " . $e->getMessage();
        }
    }

    /**
     * Fallback dummy responses if GEMINI_API_KEY is not defined
     */
    private function getMockResponse($prompt)
    {
        $promptLower = strtolower($prompt);
        
        if (strpos($promptLower, 'dashboard') !== false || strpos($promptLower, 'ringkas sistem') !== false) {
            return "### 📊 Rangkuman Eksekutif Sistem (Mode Simulasi Offline)\n\n" .
                   "Saat ini **Sistem Payroll** berjalan dengan baik. Berikut adalah rangkuman dari data master:\n\n" .
                   "1. **Karyawan Aktif**: Terdaftar karyawan aktif di seluruh cabang.\n" .
                   "2. **Mitra Klien**: Perusahaan klien aktif yang dikelola payroll-nya.\n" .
                   "3. **Struktur Organisasi**: Divisi, departemen, dan posisi telah dikonfigurasi secara lengkap.\n" .
                   "4. **Status Operasional**: Semua konfigurasi cut-off absensi dan perhitungan UMP/UMK telah siap digunakan.\n\n" .
                   "> 💡 *Catatan: Atur `GEMINI_API_KEY` di file `.env` untuk mendapatkan analisis data real-time bertenaga AI.*";
        }
        
        if (strpos($promptLower, 'payroll') !== false || strpos($promptLower, 'gaji') !== false) {
            return "### 💸 Analisis Laporan Payroll Klien (Mode Simulasi Offline)\n\n" .
                   "Berdasarkan data perhitungan payroll terbaru:\n\n" .
                   "- **Total Pembayaran**: Estimasi pengeluaran payroll bulanan tercatat stabil.\n" .
                   "- **Denda & Lembur**: Lembur terdistribusi normal. Denda keterlambatan absensi otomatis dihitung berdasarkan skema aktif.\n" .
                   "- **Pajak & BPJS**: PPh21 (Gross/Net) dan persentase BPJS karyawan telah dipotong sesuai aturan pemerintah.\n\n" .
                   "Semua data payroll siap ditransfer ke rekening bank masing-masing karyawan.";
        }

        return "### 🤖 Asisten AI Payroll (Offline)\n\n" .
               "Halo! Saya adalah Asisten AI Payroll Anda. \n\n" .
               "Saat ini saya berjalan dalam **Mode Simulasi Offline** karena kunci API (`GEMINI_API_KEY`) belum terpasang di `.env`.\n\n" .
               "Hubungi admin untuk menyetel kunci API agar saya dapat menganalisis data keuangan, absensi, dan performa karyawan Anda secara langsung.";
    }

    /**
     * POST /api/ai/summarize-dashboard
     */
    public function summarizeDashboard()
    {
        try {
            // Fetch stats
            $totalClients = $this->db->table('clients')->countAllResults();
            $totalEmployees = $this->db->table('employees')->where('status', 'Aktif')->countAllResults();
            $totalDivisions = $this->db->table('divisions')->countAllResults();
            $totalDepartments = $this->db->table('departments')->countAllResults();
            $totalPositions = $this->db->table('positions')->countAllResults();

            // Recent system logs
            $recentLogs = $this->db->table('system_logs')
                                   ->select('action, description, user_name, created_at')
                                   ->orderBy('created_at', 'DESC')
                                   ->limit(5)
                                   ->get()
                                   ->getResultArray();

            $logsText = "";
            foreach ($recentLogs as $log) {
                $logsText .= "- [{$log['created_at']}] {$log['user_name']}: {$log['action']} ({$log['description']})\n";
            }

            // Construct prompt
            $prompt = "Berikut adalah data statistik utama Sistem Payroll hari ini:\n" .
                      "- Total Mitra Klien: {$totalClients}\n" .
                      "- Total Karyawan Aktif: {$totalEmployees}\n" .
                      "- Jumlah Divisi: {$totalDivisions}, Departemen: {$totalDepartments}, Posisi: {$totalPositions}\n\n" .
                      "Aktivitas Terkini di Sistem:\n{$logsText}\n" .
                      "Buatkan rangkuman eksekutif singkat dan profesional tentang status operasional perusahaan ini dalam 2-3 paragraf. Sebutkan jika ada aktivitas terbaru yang menonjol.";

            $systemInstruction = "Anda adalah Chief HR Officer virtual. Berikan rangkuman analisis yang profesional, formal, padat, dan optimis dalam Bahasa Indonesia. Gunakan Markdown formatting.";

            $summary = $this->callGemini($prompt, $systemInstruction);

            return $this->respond([
                'status' => 200,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return $this->fail("Gagal membuat rangkuman dashboard: " . $e->getMessage());
        }
    }

    /**
     * POST /api/ai/summarize-payroll
     */
    public function summarizePayroll()
    {
        try {
            $json = $this->request->getJSON();
            $clientId = $json->client_id ?? null;
            $periodId = $json->period_id ?? null;

            if (empty($clientId)) {
                return $this->fail("client_id harus diisi");
            }

            // Get Client name
            $client = $this->db->table('clients')->where('id', $clientId)->get()->getRowArray();
            if (!$client) {
                return $this->fail("Klien tidak ditemukan");
            }

            // Get Period
            $periodQuery = $this->db->table('payroll_periods')
                                    ->where('client_id', $clientId);
            if (!empty($periodId)) {
                $periodQuery->where('id', $periodId);
            } else {
                $periodQuery->orderBy('tahun', 'DESC')->orderBy('bulan', 'DESC');
            }
            $period = $periodQuery->get()->getRowArray();

            if (!$period) {
                return $this->respond([
                    'status' => 404,
                    'summary' => "Laporan payroll untuk klien ini belum tersedia karena periode payroll aktif belum dibuat atau belum diproses."
                ]);
            }

            // Get Payroll Summary
            $payrolls = $this->db->table('payroll_final')
                                 ->select('
                                     SUM(payroll_final.gaji_pokok) as total_gp, 
                                     SUM(payroll_final.total_pendapatan - payroll_final.gaji_pokok - payroll_final.lembur_pay - payroll_final.bonus_tambahan) as total_tunj, 
                                     SUM(payroll_final.total_potongan) as total_pot, 
                                     SUM(payroll_final.take_home_pay) as total_thp, 
                                     COUNT(payroll_final.id) as emp_count,
                                     SUM(payroll_final.potongan_absen) as total_denda_alfa,
                                     SUM(payroll_final.bpjs_kes_karyawan) as total_bpjs_kes,
                                     SUM(payroll_final.bpjs_jht_karyawan) as total_bpjs_jht,
                                     SUM(payroll_final.bpjs_jp_karyawan) as total_bpjs_jp,
                                     SUM(CASE WHEN payroll_final.tax_method = \'Net\' OR payroll_final.tax_method = \'Nett\' THEN 0 ELSE payroll_final.pph21 END) as total_pph,
                                     SUM(payroll_final.jam_lembur) as total_ot_hours
                                 ')
                                 ->join('pkwt', 'pkwt.id = payroll_final.pkwt_id')
                                 ->where('payroll_final.period_id', $period['id'])
                                 ->where('pkwt.client_id', intval($clientId))
                                 ->get()
                                 ->getRowArray();

            if (empty($payrolls['emp_count'])) {
                return $this->respond([
                    'status' => 200,
                    'summary' => "Proses payroll untuk periode " . $period['bulan'] . "/" . $period['tahun'] . " belum dijalankan. Silakan jalankan 'Generate Salary' terlebih dahulu untuk menganalisis data."
                ]);
            }

            $totalGpVal = $payrolls['total_gp'] ?? 0;
            $totalTunjVal = $payrolls['total_tunj'] ?? 0;
            $totalPotVal = $payrolls['total_pot'] ?? 0;
            $totalThpVal = $payrolls['total_thp'] ?? 0;
            $totalDendaAlfaVal = $payrolls['total_denda_alfa'] ?? 0;
            $bpjsKaryawan = ($payrolls['total_bpjs_kes'] ?? 0) + ($payrolls['total_bpjs_jht'] ?? 0) + ($payrolls['total_bpjs_jp'] ?? 0);
            $pphKaryawan = $payrolls['total_pph'] ?? 0;

            $totalDendaTelatVal = max(0, $totalPotVal - $bpjsKaryawan - $pphKaryawan - $totalDendaAlfaVal);

            $totalGp = number_format($totalGpVal, 0, ',', '.');
            $totalTunj = number_format($totalTunjVal, 0, ',', '.');
            $totalPot = number_format($totalPotVal, 0, ',', '.');
            $totalThp = number_format($totalThpVal, 0, ',', '.');
            $dendaTelat = number_format($totalDendaTelatVal, 0, ',', '.');
            $dendaAlfa = number_format($totalDendaAlfaVal, 0, ',', '.');
            $otHours = number_format($payrolls['total_ot_hours'] ?? 0, 1, ',', '.');

            // Construct Prompt
            $prompt = "Berikut adalah ringkasan data payroll klien {$client['nama']} untuk Periode {$period['bulan']}/{$period['tahun']}:\n" .
                      "- Jumlah Karyawan Diproses: {$payrolls['emp_count']} orang\n" .
                      "- Total Gaji Pokok: Rp {$totalGp}\n" .
                      "- Total Tunjangan: Rp {$totalTunj}\n" .
                      "- Total Potongan: Rp {$totalPot}\n" .
                      "- Total Take Home Pay (Dibayarkan): Rp {$totalThp}\n\n" .
                      "Detail Kehadiran & Denda:\n" .
                      "- Total Jam Lembur Karyawan: {$otHours} jam\n" .
                      "- Total Denda Terlambat Masuk: Rp {$dendaTelat}\n" .
                      "- Total Denda Absen Alfa: Rp {$dendaAlfa}\n\n" .
                      "Buatkan laporan analisis payroll eksekutif yang informatif. Berikan breakdown presentase tunjangan/potongan terhadap total pembayaran, sorot denda absensi jika nilainya tinggi, dan buat rekomendasi optimasi biaya HR.";

            $systemInstruction = "Anda adalah auditor keuangan dan analis kompensasi senior. Berikan analisis laporan payroll yang logis, tajam, profesional, dengan saran optimasi yang taktis. Gunakan format Markdown yang sangat rapi.";

            $summary = $this->callGemini($prompt, $systemInstruction);

            return $this->respond([
                'status' => 200,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return $this->fail("Gagal merangkum payroll: " . $e->getMessage());
        }
    }

    /**
     * POST /api/ai/chat
     */
    public function chat()
    {
        try {
            $json = $this->request->getJSON(true);
            $userMessage = $json['message'] ?? '';
            $history = $json['history'] ?? [];
            $context = $json['context'] ?? [];

            if (empty($userMessage)) {
                return $this->fail("Pesan user tidak boleh kosong");
            }

            // Build General Database Stats for AI Intelligence
            $totalClients = $this->db->table('clients')->countAllResults();
            $totalEmployees = $this->db->table('employees')->where('status', 'Aktif')->countAllResults();
            $totalDivisions = $this->db->table('divisions')->countAllResults();
            
            // Get Client Names
            $clients = $this->db->table('clients')->select('id, nama, sektor')->limit(10)->get()->getResultArray();
            $clientsStr = "";
            foreach ($clients as $c) {
                $clientsStr .= "- [ID: {$c['id']}] {$c['nama']} ({$c['sektor']})\n";
            }

            // Get Active periods
            $periods = $this->db->table('payroll_periods')
                                ->select('client_id, bulan, tahun, status_cutoff')
                                ->where('status_cutoff', 'Open')
                                ->get()
                                ->getResultArray();
            $periodsStr = "";
            foreach ($periods as $p) {
                $clientName = '-';
                foreach ($clients as $c) {
                    if ($c['id'] == $p['client_id']) {
                        $clientName = $c['nama'];
                        break;
                    }
                }
                $periodsStr .= "- {$clientName} untuk Periode {$p['bulan']}/{$p['tahun']}\n";
            }

            // Current context details from UI
            $currentView = $context['current_view'] ?? 'Dashboard';
            $selectedClientName = 'None';
            if (!empty($context['selected_client_id'])) {
                $selClient = $this->db->table('clients')->where('id', $context['selected_client_id'])->get()->getRow();
                if ($selClient) {
                    $selectedClientName = $selClient->nama;
                }
            }

            // Prepare prompt instructions
            $systemInstruction = "Anda adalah 'AI Assistant', asisten AI internal Sistem Payroll yang ramah, profesional, cerdas, dan membantu. " .
                                 "Tugas Anda adalah membantu user (HR Manager/Admin) memahami data, fitur, dan operasional sistem.\n" .
                                 "Jawab pertanyaan user menggunakan data konteks sistem di bawah ini. Jika ada pertanyaan spesifik tentang data di luar ini, sarankan user untuk mengecek menu terkait atau minta data tambahan.\n" .
                                 "Format jawaban Anda menggunakan Markdown yang rapi (bold, list, tabel jika relevan). Jangan terlalu panjang, berikan poin penting secara to-the-point.\n\n" .
                                 "INFORMASI SISTEM AKTIF:\n" .
                                 "- Total Perusahaan Mitra (Klien): {$totalClients}\n" .
                                 "- Total Karyawan Aktif: {$totalEmployees}\n" .
                                 "- Total Divisi Struktur Organisasi: {$totalDivisions}\n" .
                                 "DAFTAR KLIEN SISTEM:\n{$clientsStr}\n" .
                                 "PERIODE PAYROLL AKTIF (OPEN):\n{$periodsStr}\n" .
                                 "POSISI USER SAAT INI:\n" .
                                 "- Menu Aktif: {$currentView}\n" .
                                 "- Klien yang Sedang Dibuka: {$selectedClientName}\n";

            // Format chat history for Gemini
            $chatPrompt = "";
            if (!empty($history)) {
                $chatPrompt .= "Riwayat Percakapan Sebelumnya:\n";
                foreach ($history as $msg) {
                    $role = ($msg['role'] === 'user') ? 'User' : 'Asisten';
                    $chatPrompt .= "{$role}: {$msg['text']}\n";
                }
                $chatPrompt .= "\n";
            }

            $chatPrompt .= "Pertanyaan User: {$userMessage}\nAsisten:";

            $reply = $this->callGemini($chatPrompt, $systemInstruction, true);

            return $this->respond([
                'status' => 200,
                'reply' => $reply
            ]);
        } catch (\Exception $e) {
            return $this->fail("Gagal memproses percakapan AI: " . $e->getMessage());
        }
    }
}

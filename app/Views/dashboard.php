<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Payroll - Manajemen Klien</title>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div style="padding: 0 25px; display: flex; align-items: center; gap: 10px;">
            <div
                style="width: 35px; height: 35px; background: var(--primary-color); border-radius: 8px; display: grid; place-items: center; color: white;">
                <i class="fas fa-building"></i>
            </div>
            <h3 style="color: var(--primary-dark)">Payroll App</h3>
        </div>
        <ul class="sidebar-menu">
            <li id="menuDashboard" class="active" onclick="switchView('dashboard')">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </li>
            <li id="menuKlien" onclick="switchView('klien')">
                <i class="fas fa-users"></i>
                <span>Manajemen Klien</span>
            </li>
            <li id="menuManajemenKaryawan" onclick="switchView('manajemenKaryawan')">
                <i class="fas fa-user-friends"></i>
                <span>Manajemen Karyawan</span>
            </li>
            <li id="menuPayroll" onclick="switchView('payroll')">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Master Skema Payroll</span>
            </li>
            <li id="menuPajak" onclick="switchView('pajak')">
                <i class="fas fa-percent"></i>
                <span>Master Skema Pajak</span>
            </li>

            <li id="menuMasterKompensasi" onclick="switchView('masterKompensasi')">
                <i class="fas fa-coins"></i> <span>Master Skema Kompensasi</span>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <div class="header-left">
                <i class="fas fa-bars" style="cursor: pointer;"></i>
                <h2 id="viewTitle" style="font-size: 18px;">Client</h2>
            </div>
            <div class="user-profile" style="cursor: pointer;" onclick="logout()">
                <i class="fas fa-user-circle" style="font-size: 20px;"></i>
                <span id="headerUserName" style="font-size: 14px; font-weight: 500;">User</span>
                <i class="fas fa-sign-out-alt" style="font-size: 14px; margin-left: 10px;" title="Logout"></i>
            </div>
        </header>

        <div class="container">
            <!-- Section: Dashboard -->
            <div id="viewDashboard" class="view-section active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(243, 156, 18, 0.1); color: var(--primary-color);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h4 id="statTotalKlien">0</h4>
                            <p>Total Klien</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(52, 152, 219, 0.1); color: var(--info);">
                            <i class="fas fa-sitemap"></i>
                        </div>
                        <div class="stat-info">
                            <h4 id="statTotalDivisi">0</h4>
                            <p>Total Divisi</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(46, 204, 113, 0.1); color: var(--success);">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="stat-info">
                            <h4 id="statTotalKaryawan">0</h4>
                            <p>Total Karyawan</p>
                        </div>
                    </div>
                </div>

                <div class="content-card" style="margin-top: 30px;">
                    <h3 style="font-size: 16px; margin-bottom: 20px;">Aktivitas Terbaru</h3>
                    <p style="color: var(--text-muted); font-size: 14px;">Selamat datang kembali, <span id="welcomeName">Admin</span>! Berikut adalah ringkasan sistem hari ini.</p>
                </div>
            </div>

            <!-- Section: Klien -->
            <div id="viewKlien" class="view-section">
                <div class="content-card">
                    <div class="section-header">
                        <h3 style="font-size: 16px; color: var(--secondary-color);">Data Klien</h3>
                        <button class="btn-add" onclick="bukaModal('tambah')">
                            <i class="fas fa-plus"></i> Tambah
                        </button>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Nama Klien</th>
                                <th>Bisnis Klien</th>
                                <th>NPWP</th>
                                <th>NIB</th>
                                <th>Tanggal Bergabung</th>
                                <th>Alamat</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tabelKlienBody">
                            <!-- Data injected by app.js -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section: Manajemen Karyawan (Global) -->
            <div id="viewManajemenKaryawan" class="view-section">
                <div class="content-card">
                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Data Karyawan (Global)</h3>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <input type="text" id="cariKaryawanGlobal" placeholder="Cari nama, nik, posisi..." oninput="cariKaryawanGlobalAktif()" style="padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px; width: 250px;">
                            <button class="btn-add" onclick="bukaModalKaryawanGlobal()" style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                                <i class="fas fa-plus"></i> Tambah Karyawan
                            </button>
                        </div>
                    </div>

                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>Perusahaan / Klien</th>
                                <th>Posisi</th>
                                <th>Department</th>
                                <th>Divisi</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tabelKaryawanGlobalBody">
                            <!-- Injected by app.js -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section: Client Workspace -->
            <div id="viewClientWorkspace" class="view-section">
                <!-- Header with Client Name and Back Button -->
                <div class="workspace-header" style="margin-bottom: 25px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <button class="btn-back" onclick="backToClientList()">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </button>
                            <h2 id="clientWorkspaceTitle" style="font-size: 22px; font-weight: 700; color: var(--secondary-color); margin: 0;">🏢 -</h2>
                        </div>
                        <div id="clientWorkspaceMeta" style="font-size: 14px; color: var(--text-muted); font-weight: 500;">
                            Sektor: <strong id="clientWorkspaceSektor" style="color: var(--primary-color);">-</strong>
                        </div>
                    </div>
                </div>

                <!-- Workspace Tabs (Horizontal below client name) -->
                <div class="workspace-tabs">
                    <button class="ws-tab active" data-wtab="karyawan" onclick="switchWorkspaceTab('karyawan')">Karyawan</button>
                    <button class="ws-tab" data-wtab="struktur" onclick="switchWorkspaceTab('struktur')">Struktur Organisasi</button>
                    <button class="ws-tab" data-wtab="kompensasi" onclick="switchWorkspaceTab('kompensasi')">Pilihan Skema</button>
                    <button class="ws-tab" data-wtab="setup" onclick="switchWorkspaceTab('setup')">Setup Payroll</button>
                    <button class="ws-tab" data-wtab="pkwt" onclick="switchWorkspaceTab('pkwt')">Kontrak PKWT</button>
                    <button class="ws-tab" data-wtab="proses" onclick="switchWorkspaceTab('proses')">Proses Payroll</button>
                </div>

                <!-- Content Panels -->
                <div class="workspace-content">
                        <!-- Panel: Karyawan -->
                        <div id="viewKaryawan" class="w-tab-panel active">
                            <div class="content-card">
                                <div class="section-header">
                                    <h3 style="font-size: 16px; color: var(--secondary-color);">Data Karyawan</h3>
                                    <div style="display: flex; gap: 12px; align-items: center;">
                                        <div class="search-box" style="margin-bottom: 0;">
                                            <i class="fas fa-search"></i>
                                            <input type="text" id="searchKaryawan" placeholder="Cari nama atau posisi..." onkeyup="filterKaryawan()">
                                        </div>
                                    </div>
                                </div>
                                <div class="table-container">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Nama Karyawan</th>
                                                <th>Posisi</th>
                                                <th>Department</th>
                                                <th>Divisi</th>
                                                <th>Kontak</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabelKaryawanBody">
                                            <!-- Data injected by app.js -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Panel: Struktur Organisasi -->
                        <div id="viewStruktur" class="w-tab-panel">
                            <div class="content-card">
                                <div class="section-header">
                                    <h3 style="font-size: 16px; color: var(--secondary-color);">Struktur Organisasi</h3>
                                    <button class="btn-add" onclick="bukaModalOrg('divisi', 'tambah')">
                                        <i class="fas fa-plus"></i> Tambah Divisi
                                    </button>
                                </div>

                                <div id="clientOrgContainer" class="org-tree">
                                    <!-- Hierarki organisasi akan di-render di sini -->
                                </div>
                            </div>
                        </div>

                        <!-- Panel: Setup Payroll Klien -->
                        <div id="viewSetup" class="w-tab-panel">
                            <div class="content-card" style="max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 16px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                                <div style="text-align: center; margin-bottom: 25px;">
                                    <div style="background: rgba(243, 156, 18, 0.1); width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                        <i class="fas fa-cog" style="font-size: 28px; color: var(--primary-color);"></i>
                                    </div>
                                    <h3 style="font-size: 18px; font-weight: 700; color: var(--secondary-color);">Konfigurasi Payroll Klien</h3>
                                    <p style="color: #64748b; font-size: 13px;">Pengaturan skema perhitungan gaji, pajak, dan siklus cut-off bulanan.</p>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 15px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                        <span style="font-weight: 500; color: #64748b;">Nama Klien</span>
                                        <strong id="wSetupClientName" style="color: #1e293b;">-</strong>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                        <span style="font-weight: 500; color: #64748b;">Skema Payroll</span>
                                        <span id="wSetupPayrollScheme" class="scheme-badge bulanan" style="font-weight: 600;">-</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                        <span style="font-weight: 500; color: #64748b;">Skema Pajak</span>
                                        <span id="wSetupTaxScheme" class="scheme-badge" style="background:#fee2e2; color:#dc2626; font-weight: 600;">-</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                        <span style="font-weight: 500; color: #64748b;">Tanggal Pembayaran</span>
                                        <strong id="wSetupPayDate" style="color: #1e293b;">Tgl -</strong>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding-bottom: 5px;">
                                        <span style="font-weight: 500; color: #64748b;">Siklus Cut-Off</span>
                                        <strong id="wSetupCutoff" style="color: #1e293b;">-</strong>
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: center;">
                                    <button class="btn-save" onclick="bukaModalSetup(window.selectedClientId, window.selectedClientName)" style="background: var(--primary-color); display: flex; align-items: center; gap: 8px; font-weight: 600; padding: 12px 24px; border-radius: 10px;">
                                        <i class="fas fa-edit"></i> Edit Konfigurasi
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Panel: Kontrak PKWT -->
                        <div id="viewPkwt" class="w-tab-panel">
                            <div class="content-card">
                                <div class="section-header">
                                    <h3 style="font-size: 16px; color: var(--secondary-color);">Daftar Kontrak PKWT</h3>
                                    <button class="btn-add" onclick="bukaModalPKWT()">
                                        <i class="fas fa-plus"></i> Buat Kontrak Baru
                                    </button>
                                </div>
                                <div class="table-container">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Karyawan</th>
                                                <th>Klien</th>
                                                <th>Posisi</th>
                                                <th>Tgl Mulai</th>
                                                <th>Status</th>
                                                <th>Gaji Pokok</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabelPKWTBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Panel: Proses Payroll -->
                        <div id="viewProses" class="w-tab-panel">
                            <div class="section-header" style="margin-bottom: 20px;">
                                <h3 style="font-size: 16px; color: var(--secondary-color);">Pemrosesan Gaji Bulanan</h3>
                                <div style="display: flex; gap: 12px; align-items: center;">
                                    <select id="selectPeriodInput" onchange="if(this.value) selectPeriod(this.value, this.options[this.selectedIndex].text)" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e0; outline: none; background: white; font-weight: 600; color: #4a5568; cursor: pointer; min-width: 200px;">
                                        <option value="">-- Pilih Periode --</option>
                                    </select>
                                    <button class="btn-add" onclick="bukaModalPeriode()" style="background: #2c3e50; font-weight: 600;">
                                        <i class="fas fa-calendar-plus"></i> Buka Periode Baru
                                    </button>
                                </div>
                            </div>

                            <div class="content-card">
                                <div id="prosesEmptyState" style="text-align: center; padding: 60px 20px; color: #a0aec0;">
                                    <i class="fas fa-calendar-check" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5; color: var(--primary-color);"></i>
                                    <h4 style="font-weight: 700; color: var(--secondary-color); margin-bottom: 5px;">Belum Ada Periode Terpilih</h4>
                                    <p style="font-size: 13px; color: #718096; max-width: 400px; margin: 0 auto;">Silakan pilih salah satu periode dari menu dropdown di atas atau buka periode baru untuk memproses gaji.</p>
                                </div>

                                <div id="prosesActions" style="display: none;">
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <h4 id="activePeriodName" style="margin:0; color: var(--primary-color);">Pilih Periode</h4>
                                            <span class="status-badge success">Periode Terbuka</span>
                                        </div>
                                        <button class="btn-save" onclick="generateGaji()" style="background: var(--primary-color);">
                                            <i class="fas fa-sync-alt"></i> Generate Gaji
                                        </button>
                                    </div>
                                    <div class="table-container">
                                        <table id="tabelCutOff">
                                            <thead>
                                                <tr>
                                                    <th>Nama Karyawan</th>
                                                    <th>Hari Kerja</th>
                                                    <th>Lembur</th>
                                                    <th>Potongan</th>
                                                    <th>Bonus</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabelCutOffBody"></tbody>
                                        </table>
                                    </div>
                                    <div id="resultSection" style="margin-top: 30px; display: none;">
                                        <h4 style="font-size: 14px; margin-bottom: 10px; color: var(--success);">HASIL PERHITUNGAN GAJI</h4>
                                        <div class="table-container">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>Karyawan</th>
                                                        <th>Pendapatan</th>
                                                        <th>Potongan</th>
                                                        <th>THP</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tabelReviewGajiBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel: Pilihan Skema -->
                        <div id="viewKompensasi" class="w-tab-panel">
                            <div class="content-card" style="max-width: 800px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 16px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                                <div style="text-align: center; margin-bottom: 30px;">
                                    <div style="background: rgba(243, 156, 18, 0.1); width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                        <i class="fas fa-layer-group" style="font-size: 28px; color: var(--primary-color);"></i>
                                    </div>
                                    <h3 style="font-size: 18px; font-weight: 700; color: var(--secondary-color);">Pilihan Skema</h3>
                                    <p style="color: #64748b; font-size: 13px;">Pilih skema payroll, pajak, dan kompensasi untuk klien ini.</p>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 20px;">
                                    <!-- Skema Payroll Card -->
                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                            <div style="width: 40px; height: 40px; background: rgba(52,152,219,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-file-invoice-dollar" style="font-size: 18px; color: #3498db;"></i>
                                            </div>
                                            <div>
                                                <h4 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0;">Skema Payroll</h4>
                                                <p style="font-size: 12px; color: #94a3b8; margin: 0;">Metode perhitungan gaji karyawan</p>
                                            </div>
                                        </div>
                                        <select id="pilihanSkemaPayrollTipe" onchange="handlePilihanSkemaPayrollTipeChange()" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e0; outline: none; font-size: 14px; font-weight: 500; background: white; cursor: pointer; margin-bottom: 12px;">
                                            <option value="">-- Pilih Tipe Skema Payroll --</option>
                                            <option value="UMP">UMP (Upah Minimum Provinsi)</option>
                                            <option value="UMK">UMK (Upah Minimum Kota/Kabupaten)</option>
                                            <option value="Nominal">Nominal Custom</option>
                                        </select>

                                        <div id="pilihanSkemaPayrollWilayahContainer" style="display: none; margin-bottom: 12px;">
                                            <label style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px; display: block;">Pilih Wilayah UMP/UMK</label>
                                            <select id="pilihanSkemaPayrollWilayah" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e0; outline: none; font-size: 14px; font-weight: 500; background: white; cursor: pointer;">
                                                <option value="">-- Pilih Wilayah --</option>
                                            </select>
                                        </div>

                                        <div id="pilihanSkemaPayrollNominalContainer" style="display: none; margin-bottom: 12px;">
                                            <label style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px; display: block;">Masukkan Nominal Gaji Pokok (Rp)</label>
                                            <input type="number" id="pilihanSkemaPayrollNominal" placeholder="Contoh: 5000000" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e0; outline: none; font-size: 14px; font-weight: 500; background: white;">
                                        </div>

                                        <div id="pilihanSkemaPayrollTemplateContainer" style="display: none; margin-bottom: 12px;">
                                            <label style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px; display: block;">Pilih Template Skema</label>
                                            <select id="pilihanSkemaPayroll" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e0; outline: none; font-size: 14px; font-weight: 500; background: white; cursor: pointer;">
                                                <option value="">-- Pilih Skema Payroll --</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Skema Pajak Card -->
                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                            <div style="width: 40px; height: 40px; background: rgba(231,76,60,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-percent" style="font-size: 18px; color: #e74c3c;"></i>
                                            </div>
                                            <div>
                                                <h4 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0;">Skema Pajak</h4>
                                                <p style="font-size: 12px; color: #94a3b8; margin: 0;">Metode perhitungan PPh 21</p>
                                            </div>
                                        </div>
                                        <select id="pilihanSkemaPajak" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e0; outline: none; font-size: 14px; font-weight: 500; background: white; cursor: pointer;">
                                            <option value="">-- Pilih Skema Pajak --</option>
                                        </select>
                                    </div>

                                    <!-- Skema Kompensasi Card -->
                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <div style="width: 40px; height: 40px; background: rgba(16,185,129,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-coins" style="font-size: 18px; color: #10b981;"></i>
                                                </div>
                                                <div>
                                                    <h4 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0;">Skema Kompensasi</h4>
                                                    <p style="font-size: 12px; color: #94a3b8; margin: 0;">Komponen pendapatan & potongan</p>
                                                </div>
                                            </div>
                                            <button onclick="goToMasterKompensasi()" style="background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                                                <i class="fas fa-external-link-alt"></i> Kelola di Master
                                            </button>
                                        </div>
                                        <div style="margin-bottom: 12px;">
                                            <select id="pilihanSkemaKompensasi" onchange="renderPilihanKompensasiSummary(this.value)" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e0; outline: none; font-size: 14px; font-weight: 500; background: white; cursor: pointer;">
                                                <option value="">-- Pilih Skema Kompensasi --</option>
                                            </select>
                                        </div>
                                        <div id="pilihanKompensasiSummary" style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; min-height: 60px;">
                                            <p style="text-align: center; color: #94a3b8; font-size: 13px; margin: 0;">Pilih skema kompensasi di atas untuk melihat detailnya.</p>
                                        </div>
                                    </div>
                                </div>

                                <div style="text-align: center; margin-top: 25px;">
                                    <button onclick="simpanPilihanSkema()" class="btn-save" style="background: var(--primary-color); padding: 12px 30px; border-radius: 10px; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-save"></i> Simpan Pilihan Skema
                                    </button>
                                </div>
                            </div>
                        </div>



                    </div>
            </div>

            <!-- Section: Skema Payroll (Master) -->
            <div id="viewPayroll" class="view-section">
                <!-- Custom Sub-Tabs for Skema Payroll & UMP/UMK -->
                <div class="payroll-main-tabs" style="display: flex; gap: 0; border-bottom: 2px solid #e2e8f0; margin-bottom: 25px; background: #f8fafc; padding: 10px 10px 0 10px; border-radius: 8px 8px 0 0;">
                    <button id="subTabSkema" class="payroll-subtab-btn active" onclick="switchPayrollSubTab('skema')" style="background: white; border: 1px solid #e2e8f0; border-bottom: 1px solid white; padding: 12px 24px; font-weight: 600; font-size: 14px; cursor: pointer; color: #0d6efd; margin-bottom: -1px; border-radius: 6px 6px 0 0; z-index: 2; display: flex; align-items: center; gap: 8px; outline: none;">
                        <i class="fas fa-file-invoice-dollar"></i> Skema Payroll
                    </button>
                    <button id="subTabUmr" class="payroll-subtab-btn" onclick="switchPayrollSubTab('umr')" style="background: transparent; border: 1px solid transparent; border-bottom: none; padding: 12px 24px; font-weight: 600; font-size: 14px; cursor: pointer; color: #475569; margin-bottom: -1px; border-radius: 6px 6px 0 0; z-index: 1; display: flex; align-items: center; gap: 8px; outline: none;">
                        <i class="fas fa-file-upload"></i> UMP/UMK
                    </button>
                </div>

                <!-- Sub-tab 1: Skema Payroll Container -->
                <div id="payrollSkemaContainer">
                    <div class="section-header" style="margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color);">Master Skema Payroll</h3>
                        <button class="btn-add" onclick="bukaModalSkema('tambah')">
                            <i class="fas fa-plus"></i> Tambah Skema
                        </button>
                    </div>
                    <div id="payrollSchemesContainer" class="schemes-grid">
                        <!-- Scheme cards will be rendered by app.js -->
                    </div>
                </div>

                <!-- Sub-tab 2: UMP/UMK Container -->
                <div id="payrollUmrContainer" style="display: none;">
                    <div class="content-card" style="padding: 0; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
                        <!-- Custom Sub-Tabs for UMP & UMK -->
                        <div class="umr-tabs" style="display: flex; gap: 0; border-bottom: 1px solid #ddd; padding: 15px 20px 0 20px; background: #fafafa;">
                            <button id="tabUmp" class="umr-tab-btn active" onclick="switchUmrTab('UMP')" style="background: white; border: 1px solid #ddd; border-bottom: 1px solid white; padding: 12px 25px; font-weight: 500; font-size: 14px; cursor: pointer; color: #0d6efd; margin-bottom: -1px; border-radius: 4px 4px 0 0; z-index: 2;">UMP</button>
                            <button id="tabUmk" class="umr-tab-btn" onclick="switchUmrTab('UMK')" style="background: transparent; border: 1px solid transparent; border-bottom: none; padding: 12px 25px; font-weight: 500; font-size: 14px; cursor: pointer; color: #0d6efd; margin-bottom: -1px; border-radius: 4px 4px 0 0; z-index: 1;">UMK</button>
                            <button id="tabNominal" class="umr-tab-btn" onclick="switchUmrTab('NOMINAL')" style="background: transparent; border: 1px solid transparent; border-bottom: none; padding: 12px 25px; font-weight: 500; font-size: 14px; cursor: pointer; color: #0d6efd; margin-bottom: -1px; border-radius: 4px 4px 0 0; z-index: 1;">Nominal</button>
                        </div>

                        <!-- Main Container below tabs -->
                        <div id="umrTableArea" style="padding: 20px;">
                            <!-- Action Buttons and Select filters -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                                
                                <!-- Left Side: Search and Download -->
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <!-- State Tracker for UMR type -->
                                    <input type="hidden" id="selectUmrType" value="UMP">
                                    
                                    <!-- Search Input Bar -->
                                    <div class="search-box" style="width: 240px; margin-bottom: 0;">
                                        <i class="fas fa-search"></i>
                                        <input type="text" id="searchUmr" placeholder="Cari Provinsi..." onkeyup="filterUmrTable()">
                                    </div>

                                    <!-- Blue Download Template Button -->
                                    <button class="btn-add" onclick="downloadTemplateUmr()" style="background: #0d6efd; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-weight: 500; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                </div>

                                <!-- Right Side: Upload Button -->
                                <div>
                                    <button class="btn-add" onclick="bukaModalUploadUmr()" style="background: #ffc107; color: #000; border: none; padding: 10px 20px; border-radius: 4px; font-weight: 500; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-upload"></i> Upload
                                    </button>
                                </div>
                            </div>

                            <!-- UMR Table -->
                            <div class="table-container" style="overflow-x: auto; background: white; border: 1px solid #ddd;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th id="colUmrCode" style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">Kode Provinsi</th>
                                            <th id="colUmrName" style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">Provinsi</th>
                                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelUmrBody">
                                        <!-- Data will be loaded via AJAX from API -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination and Entry summary Footer -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 25px; flex-wrap: wrap; gap: 15px;">
                                <span id="umrPaginationInfo" style="font-size: 14px; color: var(--text-muted); font-weight: 500;">
                                    Menampilkan 0 - 0 dari 0 data
                                </span>
                                <div id="umrPaginationControls" class="pagination" style="display: flex; gap: 5px; list-style: none;">
                                    <!-- Pagination buttons will be dynamically rendered here -->
                                </div>
                            </div>
                        </div> <!-- End of umrTableArea -->

                        <!-- Nominal Input Area (Hidden by default) -->
                        <div id="umrNominalArea" style="display: none; padding: 40px 20px; text-align: center; background: white;">
                            <div style="background: rgba(40, 167, 69, 0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                <i class="fas fa-money-bill-wave" style="font-size: 32px; color: #28a745;"></i>
                            </div>
                            <h3 style="margin-bottom: 10px; color: #2c3e50; font-size: 22px;">Input Gaji Disepakati (Nominal)</h3>
                            <p style="color: #6c757d; margin-bottom: 30px; font-size: 14px;">Masukkan besaran gaji yang telah disepakati untuk keperluan simulasi.</p>
                            
                            <div style="max-width: 400px; margin: 0 auto; text-align: left;">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block; color: #4a5568;">Nominal Kesepakatan (Rp)</label>
                                <input type="text" id="inputUmrNominal" placeholder="Contoh: 5000000" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 14px 16px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 16px; margin-bottom: 24px; transition: border-color 0.3s;" onfocus="this.style.borderColor='#0d6efd'" onblur="this.style.borderColor='#ddd'">
                                
                                <button class="btn-save" onclick="simpanNominalManual()" style="width: 100%; padding: 14px 15px; background: #0d6efd; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 10px; transition: all 0.3s; box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);">
                                    <i class="fas fa-save"></i> Simpan Nominal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section: Skema Pajak (Master) -->
            <div id="viewPajak" class="view-section">
                <div class="section-header" style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; color: var(--secondary-color);">Master Skema Pajak (PPh 21)</h3>
                    <button class="btn-add" onclick="bukaModalPajak('tambah')">
                        <i class="fas fa-plus"></i> Tambah Skema Pajak
                    </button>
                </div>
                <div id="taxSchemesContainer" class="schemes-grid">
                    <!-- Tax cards will be rendered by app.js -->
                </div>
            </div>

            <!-- Section: Simulasi Gaji -->
            <div id="viewSimulasi" class="view-section">
                <div class="content-card" style="max-width: 600px; margin: 0 auto;">
                    <div class="section-header" style="justify-content: center; flex-direction: column; text-align: center;">
                        <div style="background: rgba(52, 152, 219, 0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                            <i class="fas fa-calculator" style="font-size: 32px; color: var(--info);"></i>
                        </div>
                        <h2 style="font-size: 24px; font-weight: 700; color: #2c3e50; margin-bottom: 10px;">Upload UMP/UMK</h2>
                        <p style="color: #666; font-size: 14px;">Hitung estimasi Take Home Pay berdasarkan UMP/UMK daerah</p>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: 600; margin-bottom: 8px; display: block;">Tipe Daerah</label>
                            <select id="simulasiType" onchange="loadSimulasiRegions()" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; font-family: inherit;">
                                <option value="UMP">Provinsi (UMP)</option>
                                <option value="UMK">Kota/Kabupaten (UMK)</option>
                                <option value="NOMINAL">Nominal (Input Manual)</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: 600; margin-bottom: 8px; display: block;">Pilih Daerah</label>
                            <select id="simulasiRegion" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; font-family: inherit;">
                                <option value="">-- Pilih Provinsi --</option>
                                <option value="p1">ACEH</option>
                                <option value="p2">SUMATERA UTARA</option>
                                <option value="p3">SUMATERA BARAT</option>
                                <option value="p4">RIAU</option>
                                <option value="p5">JAMBI</option>
                                <option value="p6">SUMATERA SELATAN</option>
                                <option value="p7">BENGKULU</option>
                                <option value="p8">LAMPUNG</option>
                                <option value="p9">KEP. BANGKA BELITUNG</option>
                                <option value="p10">KEPULAUAN RIAU</option>
                                <option value="p11">DKI JAKARTA</option>
                                <option value="p12">JAWA BARAT</option>
                                <option value="p13">JAWA TENGAH</option>
                                <option value="p14">DI YOGYAKARTA</option>
                                <option value="p15">JAWA TIMUR</option>
                                <option value="p16">BANTEN</option>
                                <option value="p17">BALI</option>
                                <option value="p18">NUSA TENGGARA BARAT</option>
                                <option value="p19">NUSA TENGGARA TIMUR</option>
                                <option value="p20">KALIMANTAN BARAT</option>
                                <option value="p21">KALIMANTAN TENGAH</option>
                                <option value="p22">KALIMANTAN SELATAN</option>
                                <option value="p23">KALIMANTAN TIMUR</option>
                                <option value="p24">KALIMANTAN UTARA</option>
                                <option value="p25">SULAWESI UTARA</option>
                                <option value="p26">SULAWESI TENGAH</option>
                                <option value="p27">SULAWESI SELATAN</option>
                                <option value="p28">SULAWESI TENGGARA</option>
                                <option value="p29">GORONTALO</option>
                                <option value="p30">SULAWESI BARAT</option>
                                <option value="p31">MALUKU</option>
                                <option value="p32">MALUKU UTARA</option>
                                <option value="p33">PAPUA</option>
                                <option value="p34">PAPUA BARAT</option>
                                <option value="p35">PAPUA SELATAN</option>
                                <option value="p36">PAPUA TENGAH</option>
                                <option value="p37">PAPUA PEGUNUNGAN</option>
                                <option value="p38">PAPUA BARAT DAYA</option>
                            </select>
                        </div>

                        <button class="btn-save" onclick="hitungSimulasiGaji()" style="width: 100%; padding: 15px; background: var(--primary-color); color: white; border-radius: 10px; font-weight: 600; cursor: pointer; border: none; transition: 0.3s; margin-top: 10px;">
                            <i class="fas fa-search-dollar" style="margin-right: 8px;"></i> Cek Estimasi Gaji
                        </button>

                        <div id="simulasiResult" style="display: none; margin-top: 30px; padding: 25px; background: #f8f9fa; border-radius: 16px; border: 1px solid #eee; animation: fadeIn 0.5s;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                <span style="color: #666; font-size: 14px;">Gaji Pokok:</span>
                                <span id="simBasic" style="font-weight: 600; color: #2c3e50;">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                <span style="color: #666; font-size: 14px;">Tunjangan Tetap (10%):</span>
                                <span id="simAllowance" style="font-weight: 600; color: #2c3e50;">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding-top: 20px; border-top: 2px dashed #ddd; margin-top: 20px;">
                                <span style="font-weight: 700; color: #2c3e50;">Total Estimasi THP:</span>
                                <span id="simTotal" style="font-weight: 800; color: #27ae60; font-size: 22px;">-</span>
                            </div>
                            <p style="font-size: 11px; color: #999; text-align: center; margin-top: 20px; line-height: 1.5;">
                                *Hasil simulasi ini hanya perkiraan. Nilai riil dapat berbeda tergantung kebijakan potongan BPJS, pajak, dan komponen lainnya.
                            </p>
                        </div>
                    </div>
                </div>
            </div>



        <!-- Section: Master Skema Kompensasi -->
        <div id="viewMasterKompensasi" class="view-section">
            <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
                <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Master Skema Kompensasi</h3>
                        <p style="color: #64748b; font-size: 13px; margin: 0;">Kelola komponen pendapatan dan potongan secara global.</p>
                    </div>
                    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <button class="btn-add" onclick="bukaModalSkemaKompensasi('tambah')" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px;">
                            <i class="fas fa-plus"></i> Tambah Skema
                        </button>
                    </div>
                </div>

                <div id="compensationSchemesContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
                    <!-- Cards will be dynamically rendered here -->
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Slip Gaji -->
    <div id="overlay" onclick="tutupSemuaModal()"></div>

    <!-- Modal Form Skema Payroll -->
    <div id="modalSkema" class="modal-skema">
        <div class="modal-header">
            <h3 id="modalSkemaTitle">Tambah Skema Payroll</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalSkema()"></i>
        </div>
        <form id="formSkema">
            <div class="modal-body">
                <input type="hidden" id="skemaId">
                <div class="form-group">
                    <label>Nama Skema</label>
                    <input type="text" id="skemaNama" placeholder="Contoh: Skema Gaji Staff" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="skemaDeskripsi" rows="2" placeholder="Deskripsi singkat skema payroll"></textarea>
                </div>
                <div class="form-group">
                    <label>Tipe</label>
                    <select id="skemaTipe" required>
                        <option value="bulanan">Bulanan</option>
                        <option value="rutin">Rutin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalSkema()">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>

    <!-- Modal Form Komponen Payroll -->
    <div id="modalKomponen" class="modal-skema">
        <div class="modal-header" style="background: var(--info);">
            <h3 id="modalKomponenTitle">Tambah Komponen</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalKomponen()"></i>
        </div>
        <form id="formKomponen">
            <div class="modal-body">
                <input type="hidden" id="komponenId">
                <input type="hidden" id="komponenSchemeId">
                <div class="form-group">
                    <label>Kategori</label>
                    <select id="komponenKategori" required onchange="onKategoriChange()">
                        <option value="gaji">Gaji Pokok</option>
                        <option value="tunjangan">Tunjangan</option>
                        <option value="insentif">Insentif</option>
                        <option value="lembur">Lembur</option>
                        <option value="absensi">Potongan Absensi</option>
                        <option value="bpjs_kesehatan">BPJS Kesehatan</option>
                        <option value="bpjs_ketenagakerjaan">BPJS Ketenagakerjaan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nama Komponen</label>
                    <input type="text" id="komponenNama" placeholder="Contoh: Gaji Pokok" required>
                </div>
                <div class="form-group">
                    <label>Tipe</label>
                    <select id="komponenTipe" required>
                        <option value="pendapatan">Pendapatan (+)</option>
                        <option value="potongan">Potongan (-)</option>
                    </select>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nilai (Rp atau %)</label>
                        <input type="number" id="komponenNilai" placeholder="0" step="any" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label>Satuan</label>
                        <select id="komponenIsPersentase">
                            <option value="false">Rupiah (Rp)</option>
                            <option value="true">Persentase (%)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Keterangan (opsional)</label>
                    <input type="text" id="komponenKeterangan" placeholder="Catatan tambahan">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalKomponen()">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>

    <!-- Modal Form Klien -->
    <div id="modalClient">
        <div class="modal-header">
            <h3 id="modalTitle">Tambah Data Client</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModal()"></i>
        </div>
        <form id="formKlien">
            <div class="modal-body">
                <input type="hidden" id="clientId">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Klien</label>
                        <input type="text" id="namaKlien" placeholder="Masukkan nama klien" required>
                    </div>
                    <div class="form-group">
                        <label>Email Klien</label>
                        <input type="email" id="emailKlien" placeholder="klien@gmail.com" required>
                    </div>
                    <div class="form-group">
                        <label>Pilih Sektor Klien</label>
                        <select id="sektorKlien" required>
                            <option value="">-- Pilih Sektor --</option>
                            <option value="Retail">Retail</option>
                            <option value="Manufaktur">Manufaktur</option>
                            <option value="Jasa">Jasa</option>
                            <option value="Teknologi">Teknologi</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nomor Induk Berusaha</label>
                        <input type="text" id="nib" placeholder="Masukkan NIB" required>
                    </div>
                    <div class="form-group">
                        <label>NPWP</label>
                        <input type="text" id="npwp" placeholder="Masukkan NPWP" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Bergabung</label>
                        <input type="date" id="tanggalBergabung" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Alamat</label>
                        <textarea id="alamat" rows="3" placeholder="Masukkan alamat lengkap" required></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModal()">Batal</button>
                <button type="submit" id="btnSubmit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>

    <!-- Modal Form Organisasi -->
    <div id="modalOrg" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; width: 450px; border-radius: 12px; z-index: 1100; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <div class="modal-header">
            <h3 id="modalOrgTitle">Tambah Divisi</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalOrg()"></i>
        </div>
        <form id="formOrg">
            <div class="modal-body">
                <input type="hidden" id="orgId">
                <input type="hidden" id="orgParentId">
                <input type="hidden" id="orgType">

                <div class="form-group">
                    <label id="labelOrgName">Nama Divisi</label>
                    <input type="text" id="orgName" placeholder="Masukkan nama" required>
                    <div id="quickBadgeContainer" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;"></div>
                </div>
                <!-- Extra Fields (Hanya untuk Posisi) -->
                <div id="posExtraFields" style="display: none;">
                    <div class="form-group">
                        <label>Level Posisi</label>
                        <select id="posLevel" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none; font-size: 14px;">
                            <option value="">-- Pilih Level --</option>
                            <option value="Intern">Intern</option>
                            <option value="Junior">Junior</option>
                            <option value="Staff">Staff</option>
                            <option value="Staff Senior">Staff Senior</option>
                            <option value="Assistant Manager">Assistant Manager</option>
                            <option value="Manager">Manager</option>
                            <option value="Lead">Lead</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalOrg()">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>

    <!-- Modal Form Skema Pajak -->
    <div id="modalPajak" class="modal-skema">
        <div class="modal-header" style="background: var(--danger);">
            <h3 id="modalPajakTitle">Tambah Skema Pajak</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalPajak()"></i>
        </div>
        <form id="formPajak">
            <div class="modal-body">
                <input type="hidden" id="pajakId">
                <div class="form-group">
                    <label>Nama Skema Pajak</label>
                    <input type="text" id="pajakNama" placeholder="Contoh: PPh 21 Tetap" required>
                </div>
                <div class="form-group">
                    <label>Metode Pajak</label>
                    <select id="pajakMetode" required>
                        <option value="Gross">Gross (Pajak ditanggung Karyawan)</option>
                        <option value="Gross Up">Gross Up (Tunjangan Pajak)</option>
                        <option value="Nett">Nett (Pajak ditanggung Perusahaan)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status PTKP Default</label>
                    <select id="pajakPtkp" required>
                        <option value="TK/0">TK/0 (Tidak Kawin)</option>
                        <option value="K/0">K/0 (Kawin)</option>
                        <option value="K/1">K/1 (Kawin Anak 1)</option>
                        <option value="K/2">K/2 (Kawin Anak 2)</option>
                        <option value="K/3">K/3 (Kawin Anak 3)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="pajakDeskripsi" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalPajak()">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--danger);">Simpan</button>
            </div>
        </form>
    </div>

    <!-- Modal Setup Payroll Klien -->
    <div id="modalSetup" class="modal-skema">
        <div class="modal-header" style="background: var(--secondary-color);">
            <h3 id="modalSetupTitle">Setup Payroll Klien</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalSetup()"></i>
        </div>
        <form id="formSetup">
            <div class="modal-body">
                <input type="hidden" id="setupId">
                <input type="hidden" id="setupClientId">
                <div class="form-group">
                    <label>Klien</label>
                    <input type="text" id="setupClientNama" readonly style="background: #f0f0f0;">
                </div>
                <div class="form-group">
                    <label>Pilih Tipe Skema Payroll</label>
                    <select id="setupPayrollSchemeTipe" onchange="handleSetupPayrollSchemeTipeChange()" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; margin-bottom: 10px;">
                        <option value="">-- Pilih Tipe Skema --</option>
                        <option value="UMP">UMP (Upah Minimum Provinsi)</option>
                        <option value="UMK">UMK (Upah Minimum Kota/Kabupaten)</option>
                        <option value="Nominal">Nominal Custom</option>
                    </select>
                </div>
                <div class="form-group" id="setupPayrollSchemeWilayahContainer" style="display: none; margin-bottom: 10px;">
                    <label>Pilih Wilayah UMP/UMK</label>
                    <select id="setupPayrollSchemeWilayah" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="">-- Pilih Wilayah --</option>
                    </select>
                </div>
                <div class="form-group" id="setupPayrollSchemeNominalContainer" style="display: none; margin-bottom: 10px;">
                    <label>Nominal Gaji Pokok (Rp)</label>
                    <input type="number" id="setupPayrollSchemeNominal" placeholder="Contoh: 5000000" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                </div>
                <div class="form-group" id="setupPayrollSchemeTemplateContainer" style="display: none; margin-bottom: 10px;">
                    <label>Pilih Template Skema</label>
                    <select id="setupPayrollScheme" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <!-- Injected by app.js -->
                    </select>
                </div>
                <div class="form-group">
                    <label>Pilih Skema Pajak</label>
                    <select id="setupTaxScheme" required>
                        <!-- Injected by app.js -->
                    </select>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tanggal Gajian</label>
                        <input type="number" id="setupPayDate" min="1" max="31" value="25" required>
                    </div>
                    <div class="form-group">
                        <label>Start Cutoff (Tgl)</label>
                        <input type="number" id="setupCutoffStart" min="1" max="31" value="21" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalSetup()">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--secondary-color);">Simpan Konfigurasi</button>
            </div>
        </form>
    </div>


    <!-- Custom Toast Container -->
    <div id="toastContainer"></div>

    <!-- Custom Confirm Dialog -->
    <div id="confirmOverlay" class="confirm-overlay"></div>
    <div id="confirmDialog" class="confirm-dialog">
        <div class="confirm-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 id="confirmTitle" class="confirm-title">Konfirmasi</h3>
        <p id="confirmMessage" class="confirm-message">Apakah Anda yakin?</p>
        <div class="confirm-actions">
            <button id="confirmCancel" class="confirm-btn confirm-btn-cancel">Batal</button>
            <button id="confirmOk" class="confirm-btn confirm-btn-ok">Ya, Hapus</button>
        </div>
    </div>

    </div>

    <!-- Modal Form PKWT -->
    <div id="modalPKWT" class="modal-skema">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalPKWTTitle">Buat Kontrak PKWT</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalPKWT()"></i>
        </div>
        <form id="formPKWT">
            <div class="modal-body">
                <input type="hidden" id="pkwtId">
                <div class="form-group">
                    <label>Nama Karyawan</label>
                    <input type="text" id="pkwtEmployeeName" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="form-group">
                    <label>Klien</label>
                    <select id="pkwtClientId" required onchange="updatePKWTSchemeInfo()">
                        <option value="">-- Pilih Klien --</option>
                        <!-- Injected by app.js -->
                    </select>
                </div>
                <div id="pkwtSchemeInfo" style="margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; font-size: 12px; display: none;">
                    <i class="fas fa-info-circle"></i> <span id="pkwtSchemeText">Skema: -</span>
                </div>
                <div class="form-group">
                    <label>Posisi / Jabatan</label>
                    <input type="text" id="pkwtPositionName" placeholder="Contoh: Staff Admin" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" id="pkwtStartDate" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Berakhir</label>
                        <input type="date" id="pkwtEndDate" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Gaji Pokok (Rp)</label>
                    <input type="number" id="pkwtBasicSalary" placeholder="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalPKWT()">Batal</button>
                <button type="submit" class="btn-save">Generate & Simpan PKWT</button>
            </div>
        </form>
    </div>

    </div>

    <!-- Modal Kelola Periode -->
    <div id="modalPeriode" class="modal-skema">
        <div class="modal-header" style="background: var(--info);">
            <h3>Manajemen Periode Payroll</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalPeriode()"></i>
        </div>
        <div class="modal-body">
            <form id="formPeriode" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Bulan</label>
                        <select id="periodMonth" required>
                            <option value="1">Januari</option><option value="2">Februari</option><option value="3">Maret</option>
                            <option value="4">April</option><option value="5">Mei</option><option value="6">Juni</option>
                            <option value="7">Juli</option><option value="8">Agustus</option><option value="9">September</option>
                            <option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" id="periodYear" value="2024" required>
                    </div>
                </div>
                <button type="submit" class="btn-save" style="width: 100%; background: var(--info);">Buka Periode Baru</button>
            </form>
            
            <h4 style="margin-bottom: 10px; font-size: 14px;">Riwayat Periode</h4>
            <div id="periodHistoryList" style="max-height: 200px; overflow-y: auto;">
                <!-- List of periods injected by app.js -->
            </div>
        </div>
    </div>

    <!-- Modal Input Cut-Off -->
    <div id="modalCutOff" class="modal-skema">
        <div class="modal-header" style="background: var(--secondary-color);">
            <h3 id="modalCutOffTitle">Input Data Cut-Off</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalCutOff()"></i>
        </div>
        <form id="formCutOff">
            <div class="modal-body">
                <input type="hidden" id="cutoffPkwtId">
                <div class="form-group">
                    <label>Nama Karyawan</label>
                    <input type="text" id="cutoffEmployeeName" readonly style="background: #f0f0f0;">
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Hari Kerja</label>
                        <input type="number" id="cutoffHariKerja" value="22" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Lembur (Jam)</label>
                        <input type="number" id="cutoffJamLembur" step="0.5" value="0" required>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Potongan Absensi (Rp)</label>
                        <input type="number" id="cutoffPotongan" value="0" required>
                    </div>
                    <div class="form-group">
                        <label>Bonus/Lainnya (Rp)</label>
                        <input type="number" id="cutoffBonus" value="0" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalCutOff()">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--secondary-color);">Simpan Data</button>
            </div>
        </form>
    </div>

    <!-- Modal Upload CSV UMP/UMK -->
    <div id="modalUploadUmr" class="modal-skema" style="display: none;">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3>Upload Data UMP/UMK</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalUploadUmr()"></i>
        </div>
        <form id="formUploadUmr">
            <div class="modal-body" style="padding: 25px;">
                <input type="hidden" id="uploadUmrTipe" value="UMP">
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Tahun Minimum Wage</label>
                    <select id="uploadUmrTahun" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">File CSV</label>
                    <!-- Drag & Drop Uploader Area -->
                    <div id="umrDropZone" style="border: 2px dashed #ddd; border-radius: 12px; padding: 35px 20px; text-align: center; background: #fafafa; cursor: pointer; transition: all 0.3s ease;">
                        <i class="fas fa-file-csv" style="font-size: 48px; color: var(--primary-color); margin-bottom: 15px;"></i>
                        <h4 style="font-size: 14px; font-weight: 600; color: #333; margin-bottom: 6px;">Seret & Lepas file di sini</h4>
                        <p style="font-size: 12px; color: #7f8c8d; margin-bottom: 15px;">atau klik untuk menelusuri file dari komputer Anda</p>
                        <span id="umrFileName" style="font-size: 13px; font-weight: 600; color: var(--info); display: block; word-break: break-all;">Belum ada file terpilih</span>
                        <input type="file" id="fileUmr" accept=".csv" style="display: none;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalUploadUmr()">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Unggah Sekarang</button>
            </div>
        </form>
    </div>

    <!-- Modal Input Manual UMP/UMK -->
    <div id="modalManualUmr" class="modal-skema" style="display: none;">
        <div class="modal-header" style="background: #2c3e50;">
            <h3 id="modalManualTitle">Tambah Data Minimum Wage</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalManualUmr()"></i>
        </div>
        <form id="formManualUmr">
            <div class="modal-body" style="padding: 25px;">
                <input type="hidden" id="manualUmrId">
                <input type="hidden" id="manualUmrTipe" value="UMP">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Kode Daerah (Provinsi / Kota / Kab)</label>
                    <input type="text" id="manualUmrKode" placeholder="Contoh: ID 31 atau 31.71" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Nama Daerah</label>
                    <input type="text" id="manualUmrNama" placeholder="Contoh: DKI JAKARTA atau JAKARTA SELATAN" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Provinsi Induk (Khusus UMK)</label>
                    <input type="text" id="manualUmrProvinsi" placeholder="Contoh: JAWA BARAT (kosongkan jika UMP)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Nominal (Rupiah)</label>
                        <input type="number" id="manualUmrNominal" placeholder="Contoh: 5067381" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Tahun</label>
                        <input type="number" id="manualUmrTahun" placeholder="2026" required value="2026" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalManualUmr()">Batal</button>
                <button type="submit" class="btn-save" style="background: #2c3e50;">Simpan Data</button>
            </div>
        </form>
    </div>

    <!-- Modal Data Karyawan -->
    <div id="modalKaryawan" class="modal-skema" style="display: none; z-index: 1000;">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalKaryawanTitle">Tambah Data Karyawan</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalKaryawan()"></i>
        </div>
        <form id="formKaryawan">
            <div class="modal-body" style="padding: 25px; max-height: 70vh; overflow-y: auto;">
                <input type="hidden" id="employeeId">
                
                <div class="form-group" id="empClientIdContainer" style="margin-bottom: 15px; display: none;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Pilih Perusahaan / Klien</label>
                    <select id="empClientId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="">-- Pilih Klien --</option>
                    </select>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">NIK Karyawan</label>
                        <input type="text" id="empNik" placeholder="Contoh: 317301XXXXXXXXXX" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Nama Lengkap</label>
                        <input type="text" id="empNama" placeholder="Nama Karyawan" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Email</label>
                        <input type="email" id="empEmail" placeholder="email@domain.com" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Tanggal Masuk</label>
                        <input type="date" id="empTglMasuk" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Pilih Divisi</label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <select id="empDivisionId" required style="flex-grow: 1; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                                <option value="">-- Pilih Divisi --</option>
                            </select>
                            <button type="button" onclick="bukaModalOrg('divisi','tambah')" style="background: var(--primary-color); border: none; color: white; width: 38px; height: 38px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px;" title="Tambah Divisi Baru"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Pilih Department</label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <select id="empDepartmentId" required style="flex-grow: 1; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                                <option value="">-- Pilih Department --</option>
                            </select>
                            <button type="button" onclick="tambahDeptInline()" style="background: var(--primary-color); border: none; color: white; width: 38px; height: 38px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px;" title="Tambah Department Baru"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Pilih Posisi / Jabatan</label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <select id="empPositionId" required style="flex-grow: 1; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                                <option value="">-- Pilih Posisi --</option>
                            </select>
                            <button type="button" onclick="tambahPosisiInline()" style="background: var(--primary-color); border: none; color: white; width: 38px; height: 38px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px;" title="Tambah Posisi Baru"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Gaji Pokok (Rp)</label>
                        <input type="number" id="empGaji" placeholder="Contoh: 5000000" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Nama Bank</label>
                        <input type="text" id="empBankName" placeholder="Contoh: BCA, Mandiri, BRI" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">No. Rekening</label>
                        <input type="text" id="empRekening" placeholder="Contoh: 1234567890" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Status PTKP Pajak</label>
                    <select id="empPtkp" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="TK/0">TK/0 (Tidak Kawin, 0 Tanggungan)</option>
                        <option value="TK/1">TK/1 (Tidak Kawin, 1 Tanggungan)</option>
                        <option value="TK/2">TK/2 (Tidak Kawin, 2 Tanggungan)</option>
                        <option value="TK/3">TK/3 (Tidak Kawin, 3 Tanggungan)</option>
                        <option value="K/0">K/0 (Kawin, 0 Tanggungan)</option>
                        <option value="K/1">K/1 (Kawin, 1 Tanggungan)</option>
                        <option value="K/2">K/2 (Kawin, 2 Tanggungan)</option>
                        <option value="K/3">K/3 (Kawin, 3 Tanggungan)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalKaryawan()">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Simpan Data</button>
            </div>
        </form>
    </div>

    <!-- Modal Skema Kompensasi (Master) -->
    <div id="modalSkemaKompensasi" class="modal-skema" style="display: none;">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalSkemaKompensasiTitle">Tambah Skema Kompensasi</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalSkemaKompensasi()"></i>
        </div>
        <form id="formSkemaKompensasi">
            <div class="modal-body">
                <input type="hidden" id="skemaKompensasiId">
                <div class="form-group">
                    <label>Nama Skema</label>
                    <input type="text" id="skemaKompensasiNama" placeholder="Contoh: Staff Kantor, Operator Pabrik" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="skemaKompensasiDeskripsi" placeholder="Tulis deskripsi skema di sini..." style="width:100%; height:80px; resize:none; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-family: inherit; font-size: 14px;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalSkemaKompensasi()">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Simpan Skema</button>
            </div>
        </form>
    </div>

    <!-- Modal Komponen Kompensasi (Master) -->
    <div id="modalKomponenKompensasi" class="modal-skema" style="display: none;">
        <div class="modal-header" style="background: #10b981;">
            <h3 id="modalKomponenKompensasiTitle">Tambah Komponen Kompensasi</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalKomponenKompensasi()"></i>
        </div>
        <form id="formKomponenKompensasi">
            <div class="modal-body">
                <input type="hidden" id="komponenKompensasiId">
                <input type="hidden" id="komponenKompensasiSchemeId">
                <div class="form-group">
                    <label>Nama Komponen</label>
                    <input type="text" id="komponenKompensasiNama" placeholder="Contoh: Tunjangan Makan, Potongan Kehadiran" required>
                </div>
                <div class="form-group">
                    <label>Tipe Komponen</label>
                    <select id="komponenKompensasiTipe" required>
                        <option value="pendapatan">Pendapatan (+)</option>
                        <option value="potongan">Potongan (-)</option>
                    </select>
                </div>
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Nilai / Nominal</label>
                        <input type="number" id="komponenKompensasiNilai" placeholder="0" value="0" required>
                    </div>
                    <div class="form-group">
                        <label>Format Nilai</label>
                        <select id="komponenKompensasiIsPersentase" required>
                            <option value="0">Rupiah (Rp)</option>
                            <option value="1">Persentase (%)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalKomponenKompensasi()">Batal</button>
                <button type="submit" class="btn-save" style="background: #10b981;">Simpan Komponen</button>
            </div>
        </form>
    </div>

    <!-- Custom Confirm Dialog -->
    <div id="confirmOverlay" class="confirm-overlay"></div>
    <div id="confirmDialog" class="confirm-dialog">
        <div class="confirm-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h3 id="confirmTitle">Konfirmasi</h3>
        <p id="confirmMessage">Apakah Anda yakin?</p>
        <div class="confirm-actions">
            <button id="confirmCancel" class="btn-cancel">Batal</button>
            <button id="confirmOk" class="btn-save" style="background: var(--danger); color: white; border: none; border-radius: 8px; padding: 10px 20px; font-weight: 600; cursor: pointer;">Ya, Lanjutkan</button>
        </div>
    </div>

    <script>
        const BASE_URL = "<?= base_url() ?>";
    </script>
    <script src="<?= base_url('js/app.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/app-org.js?v=' . time()) ?>"></script>
</body>

</html>

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

    <?= view('partials/_sidebar') ?>

    <!-- Main Content -->
    <div class="main-content">
        <?= view('partials/_header') ?>

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

                <!-- Quick Actions Panel -->
                <div class="content-card" style="margin-top: 30px; padding: 25px;">
                    <div style="margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Menu Aksi Cepat</h3>
                        <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Pintas navigasi untuk melakukan tugas administratif utama secara instan.</p>
                    </div>
                    
                    <div class="quick-actions-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                        <!-- Action 0: Dashboard -->
                        <div class="quick-action-card" onclick="quickAction('dashboard')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(63, 81, 181, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/1828/1828765.png" alt="Dashboard" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Dashboard</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Kembali ke halaman utama.</p>
                            </div>
                        </div>

                        <!-- Action 1: Tambah Klien -->
                        <div class="quick-action-card" onclick="quickAction('tambah-klien')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(243, 156, 18, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/4300/4300058.png" alt="Klien" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Tambah Klien</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Daftarkan perusahaan baru.</p>
                            </div>
                        </div>

                        <!-- Action 2: Tambah Karyawan -->
                        <div class="quick-action-card" onclick="quickAction('tambah-karyawan')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(52, 152, 219, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Karyawan" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Tambah Karyawan</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Daftarkan karyawan baru.</p>
                            </div>
                        </div>

                        <!-- Action 3: Proses Payroll -->
                        <div class="quick-action-card" onclick="quickAction('proses-payroll')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(46, 204, 113, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135706.png" alt="Payroll" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Proses Payroll</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Generate & hitung gaji bulanan.</p>
                            </div>
                        </div>

                        <!-- Action 4: Pengaturan Skema -->
                        <div class="quick-action-card" onclick="quickAction('pengaturan-skema')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(155, 89, 182, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/2040/2040504.png" alt="Skema" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Pengaturan Skema</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Atur skema BPJS & pajak.</p>
                            </div>
                        </div>

                        <!-- Action 5: Lokasi Kerja -->
                        <div class="quick-action-card" onclick="quickAction('lokasi-kerja')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(26, 188, 156, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/1865/1865269.png" alt="Lokasi Kerja" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Lokasi Kerja</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Kelola wilayah & kantor kerja.</p>
                            </div>
                        </div>

                        <!-- Action 6: Upload UMK UMP -->
                        <div class="quick-action-card" onclick="quickAction('upload-umk-ump')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(231, 76, 60, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/1091/1091210.png" alt="Upload UMK UMP" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Upload UMK UMP</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Unggah ketetapan UMP & UMK.</p>
                            </div>
                        </div>

                        <!-- Action 7: Struktur Gaji -->
                        <div class="quick-action-card" onclick="quickAction('struktur-gaji')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(39, 174, 96, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/2489/2489756.png" alt="Struktur Gaji" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Struktur Gaji</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Skema komponen pendapatan.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Section: Klien -->
            <div id="viewKlien" class="view-section">
                <div class="content-card">
                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Data Klien</h3>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <input type="text" id="cariKlienGlobal" placeholder="Cari klien..." oninput="cariKlienAktif()" style="padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px; width: 250px;">
                            <button class="btn-add" onclick="bukaModal('tambah')" style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
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
                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Data Karyawan</h3>
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
                                <th>Perusahaan / Klien</th>
                                <th>Employ ID (NIK)</th>
                                <th>Nama Karyawan</th>
                                <th>Tempat Tanggal Lahir</th>
                                <th>NPWP</th>
                                <th>Divisi</th>
                                <th>Departemen</th>
                                <th>Posisi/Jabatan</th>
                                <th>Lokasi Kerja</th>
                                <th>Gaji Harian / Denda</th>
                                <th>Kontrak</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tabelKaryawanGlobalBody">
                            <!-- Injected by app.js -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section: Global Lokasi Kerja -->
            <div id="viewGlobalLokasiKerja" class="view-section">
                <div class="content-card">
                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Data Lokasi Kerja</h3>
                        <button class="btn-add" onclick="bukaModalLokasiKerja()" style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                            <i class="fas fa-plus"></i> Tambah Lokasi Kerja
                        </button>
                    </div>

                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Lokasi Kerja</th>
                                <th>Location Code</th>
                                <th>Klien / Perusahaan</th>

                                <th>Provinsi</th>
                                <th>Kota/Kabupaten</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tabelGlobalLokasiKerjaBody">
                            <!-- Injected by JS -->
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
                            <!-- Sub Tabs for Workspace Karyawan -->
                            <div class="sub-tabs-container" style="display: flex; gap: 8px; border-bottom: 2px solid #f1f5f9; margin-bottom: 20px; padding-bottom: 2px;">
                                <button class="sub-tab-btn active" id="subTabLokasiKerja" onclick="switchClientKaryawanSubTab('lokasi_kerja')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: var(--primary-color); cursor: pointer; border-bottom: 2px solid var(--primary-color); margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Lokasi Kerja</button>
                                <button class="sub-tab-btn" id="subTabKaryawanData" onclick="switchClientKaryawanSubTab('karyawan_data')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Data Karyawan</button>
                            </div>

                            <!-- Sub Panel 1: Lokasi Kerja (Active by default) -->
                            <div id="panelLokasiKerja" class="client-karyawan-subpanel">
                                <div class="content-card">
                                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Daftar Lokasi Kerja</h3>
                                        <button class="btn-add" onclick="bukaModalLokasiKerja()" style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                                            <i class="fas fa-plus"></i> Tambah Lokasi Kerja
                                        </button>
                                    </div>
                                    <div class="table-container">
                                        <table style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>Lokasi Kerja</th>
                                                    <th>Location Code</th>

                                                    <th>Provinsi</th>
                                                    <th>Kota/Kabupaten</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabelLokasiKerjaBody">
                                                <!-- Injected by JS -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Sub Panel 2: Data Karyawan -->
                            <div id="panelKaryawanData" class="client-karyawan-subpanel" style="display: none;">
                                <div class="content-card">
                                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Data Karyawan</h3>
                                        <div class="search-box" style="margin-bottom: 0;">
                                            <i class="fas fa-search"></i>
                                            <input type="text" id="searchKaryawan" placeholder="Cari nama atau posisi..." onkeyup="filterKaryawan()">
                                        </div>
                                    </div>
                                    <div class="table-container">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Perusahaan / Klien</th>
                                                    <th>Employ ID (NIK)</th>
                                                    <th>Nama Karyawan</th>
                                                    <th>Tempat Tanggal Lahir</th>
                                                    <th>NPWP</th>
                                                    <th>Divisi</th>
                                                    <th>Departemen</th>
                                                    <th>Posisi/Jabatan</th>
                                                    <th>Lokasi Kerja</th>
                                                    <th>Gaji Harian / Denda</th>
                                                    <th>Kontrak</th>
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
                                            <span id="activePeriodStatus" class="status-badge success">Periode Terbuka</span>
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
                                    <div style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
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
                                            <option value="Template">Template Skema Payroll</option>
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

                                    <!-- Skema Komponen Card -->
                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <div style="width: 40px; height: 40px; background: rgba(16,185,129,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-coins" style="font-size: 18px; color: #10b981;"></i>
                                                </div>
                                                <div>
                                                    <h4 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0;">Skema Komponen</h4>
                                                    <p style="font-size: 12px; color: #94a3b8; margin: 0;">Komponen pendapatan & potongan</p>
                                                </div>
                                            </div>
                                            <button onclick="goToMasterKompensasi()" style="background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                                                <i class="fas fa-external-link-alt"></i> Kelola di Master
                                            </button>
                                        </div>
                                        <div style="margin-bottom: 12px;">
                                            <select id="pilihanSkemaKompensasi" onchange="renderPilihanKompensasiSummary(this.value)" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e0; outline: none; font-size: 14px; font-weight: 500; background: white; cursor: pointer;">
                                                <option value="">-- Pilih Skema Komponen --</option>
                                            </select>
                                        </div>
                                        <div id="pilihanKompensasiSummary" style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; min-height: 60px;">
                                            <p style="text-align: center; color: #94a3b8; font-size: 13px; margin: 0;">Pilih skema komponen di atas untuk melihat detailnya.</p>
                                        </div>
                                    </div>

                                    <!-- Skema Absensi Card -->
                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                                            <div style="width: 40px; height: 40px; background: rgba(231, 76, 60, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-calendar-times" style="font-size: 18px; color: #e74c3c;"></i>
                                            </div>
                                            <div>
                                                <h4 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0;">Skema Absensi</h4>
                                                <p style="font-size: 12px; color: #94a3b8; margin: 0;">Aturan prorate dan pemotongan absen</p>
                                            </div>
                                        </div>
                                        <div style="display: flex; flex-direction: column; gap: 12px;">
                                            <label class="absen-config-item" style="cursor: pointer;">
                                                <div>
                                                    <strong style="font-size: 14px; color: #1e293b; display: block; margin-bottom: 2px;">Prorate Gaji</strong>
                                                    <span style="font-size: 12px; color: #94a3b8;">Hitung gaji secara proporsional sesuai hari kerja</span>
                                                </div>
                                                <input type="checkbox" id="cfgProrate" style="width: 20px; height: 20px; cursor: pointer;">
                                            </label>
                                            <label class="absen-config-item" style="cursor: pointer;">
                                                <div>
                                                    <strong style="font-size: 14px; color: #1e293b; display: block; margin-bottom: 2px;">Absen Tidak Potong Gaji</strong>
                                                    <span style="font-size: 12px; color: #94a3b8;">Absensi ketidakhadiran tidak memotong gaji bulanan</span>
                                                </div>
                                                <input type="checkbox" id="cfgAbsenTidakPotong" style="width: 20px; height: 20px; cursor: pointer;">
                                            </label>
                                            <div class="absen-config-item" style="display: flex; justify-content: space-between; align-items: center; gap: 20px; padding: 20px 24px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                                                <div>
                                                    <strong style="font-size: 14px; color: #1e293b; display: block; margin-bottom: 2px;">Nominal Potongan Absen</strong>
                                                    <span style="font-size: 12px; color: #94a3b8;">Jumlah potongan per hari absen</span>
                                                </div>
                                                <input type="text" id="cfgNominalPotongan" onkeyup="formatRupiahInput(this)" placeholder="0" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; width: 150px; text-align: right; font-size: 14px; font-weight: 600; outline: none;">
                                            </div>
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
                <div class="payroll-main-tabs" style="display: none; gap: 0; border-bottom: 2px solid #e2e8f0; margin-bottom: 25px; background: #f8fafc; padding: 10px 10px 0 10px; border-radius: 8px 8px 0 0;">
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
                            <button id="tabNominal" class="umr-tab-btn" onclick="switchUmrTab('NOMINAL')" style="display: none;">Nominal</button>
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

                                <div id="displayNominalTersimpan" style="margin-top: 25px; padding: 15px; border-radius: 8px; background: #e8fdf0; border: 1px solid #d4edda; text-align: center; display: none;">
                                    <span style="font-size: 14px; color: #155724; font-weight: 500;">Nominal Aktif Saat Ini:</span>
                                    <h4 id="valNominalTersimpan" style="font-size: 22px; color: #2ecc71; margin: 5px 0 0 0; font-weight: 700;">-</h4>
                                </div>
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



        <!-- Section: Master Skema Komponen -->
        <div id="viewMasterKompensasi" class="view-section">
            <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
                <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Master Skema Komponen</h3>
                        <p style="color: #64748b; font-size: 13px; margin: 0;">Kelola komponen pendapatan dan potongan secara global.</p>
                    </div>
                    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <button class="btn-add" onclick="bukaModalSkemaKompensasi('tambah')" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px;">
                            <i class="fas fa-plus"></i> Tambah Skema
                        </button>
                    </div>
                </div>

                <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="width: 60px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">No</th>
                                <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Nama / Tipe</th>
                                <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Sumber & Nilai</th>
                                <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Periode</th>
                                <th style="width: 150px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="compensationSchemesContainer">
                            <!-- Rows will be dynamically rendered here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?= view('partials/_modals') ?>
    <?= view('partials/_scripts') ?>

</body>

</html>

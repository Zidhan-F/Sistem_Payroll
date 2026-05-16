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
                <span>Klien</span>
            </li>
            <li id="menuKaryawan" onclick="switchView('karyawan')">
                <i class="fas fa-user-friends"></i>
                <span>Karyawan</span>
            </li>
            <li id="menuStruktur" onclick="switchView('struktur')">
                <i class="fas fa-sitemap"></i>
                <span>Struktur Organisasi</span>
            </li>
            <li id="menuPayroll" onclick="switchView('payroll')">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Skema Payroll</span>
            </li>
            <li id="menuPajak" onclick="switchView('pajak')">
                <i class="fas fa-percent"></i>
                <span>Skema Pajak</span>
            </li>
            <li id="menuSetup" onclick="switchView('setup')">
                <i class="fas fa-cog"></i>
                <span>Setup Payroll Klien</span>
            </li>
            <li id="menuPKWT" onclick="switchView('pkwt')">
                <i class="fas fa-file-contract"></i>
                <span>Kontrak PKWT</span>
            </li>
            <li id="menuProses" onclick="switchView('proses')">
                <i class="fas fa-calculator"></i>
                <span>Proses Payroll</span>
            </li>
            <li onclick="switchView('simulasi')">
                <i class="fas fa-file-upload"></i> <span>Upload UMP/UMK</span>
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

            <!-- Section: Karyawan -->
            <div id="viewKaryawan" class="view-section">
                <div class="content-card">
                    <div class="section-header">
                        <h3 style="font-size: 16px; color: var(--secondary-color);">Data Seluruh Karyawan</h3>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchKaryawan" placeholder="Cari nama atau posisi..." onkeyup="filterKaryawan()">
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
                                </tr>
                            </thead>
                            <tbody id="tabelKaryawanBody">
                                <!-- Data injected by app.js -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Section: Struktur Organisasi -->
            <div id="viewStruktur" class="view-section">
                <div class="content-card">
                    <div class="section-header">
                        <h3 style="font-size: 16px; color: var(--secondary-color);">Struktur Organisasi</h3>
                        <button class="btn-add" onclick="bukaModalOrg('divisi', 'tambah')">
                            <i class="fas fa-plus"></i> Tambah Divisi
                        </button>
                    </div>

                    <div id="orgTreeContainer" class="org-tree">
                        <!-- Hierarki organisasi akan di-render di sini -->
                    </div>
                </div>
            </div>

            <!-- Section: Skema Payroll -->
            <div id="viewPayroll" class="view-section">
                <div class="section-header" style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; color: var(--secondary-color);">Skema Payroll</h3>
                    <button class="btn-add" onclick="bukaModalSkema('tambah')">
                        <i class="fas fa-plus"></i> Tambah Skema
                    </button>
                </div>
                <div id="payrollSchemesContainer" class="schemes-grid">
                    <!-- Scheme cards will be rendered by app.js -->
                </div>
            </div>

            <!-- Section: Skema Pajak -->
            <div id="viewPajak" class="view-section">
                <div class="section-header" style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; color: var(--secondary-color);">Skema Pajak (PPh 21)</h3>
                    <button class="btn-add" onclick="bukaModalPajak('tambah')">
                        <i class="fas fa-plus"></i> Tambah Skema Pajak
                    </button>
                </div>
                <div id="taxSchemesContainer" class="schemes-grid">
                    <!-- Tax cards will be rendered by app.js -->
                </div>
            </div>

            <!-- Section: Setup Payroll Klien -->
            <div id="viewSetup" class="view-section">
                <div class="content-card">
                    <div class="section-header">
                        <h3 style="font-size: 16px; color: var(--secondary-color);">Penempelan Skema ke Klien</h3>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Klien</th>
                                    <th>Skema Payroll</th>
                                    <th>Skema Pajak</th>
                                    <th>Tgl Gajian</th>
                                    <th>Cut-off</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="tabelSetupBody">
                                <!-- Data injected by app.js -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Module: PKWT, Proses, UMR Sections -->
            <div id="viewPkwt" class="view-section">
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

            <!-- Section: Proses Payroll -->
            <div id="viewProses" class="view-section">
                <div class="section-header" style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; color: var(--secondary-color);">Pemrosesan Gaji Bulanan</h3>
                    <button class="btn-add" onclick="bukaModalPeriode()" style="background: #2c3e50;">
                        <i class="fas fa-calendar-plus"></i> Buka Periode Baru
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: 280px 1fr; gap: 20px;">
                    <div class="content-card" style="padding: 15px;">
                        <h4 style="font-size: 13px; margin-bottom: 15px; color: #888;">RIWAYAT PERIODE</h4>
                        <div id="periodHistoryList" class="period-list"></div>
                    </div>
                    <div class="content-card">
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
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: 600; margin-bottom: 8px; display: block;">Pilih Daerah</label>
                            <select id="simulasiRegion" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; font-family: inherit;">
                                <option value="">-- Pilih Daerah --</option>
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
                        <input type="number" id="nib" placeholder="Masukkan NIB" required>
                    </div>
                    <div class="form-group">
                        <label>NPWP</label>
                        <input type="number" id="npwp" placeholder="Masukkan NPWP" required>
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
    <div id="modalOrg" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; width: 450px; border-radius: 12px; z-index: 1000; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <div class="modal-header">
            <h3 id="modalOrgTitle">Tambah Divisi</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalOrg()"></i>
        </div>
        <form id="formOrg">
            <div class="modal-body">
                <input type="hidden" id="orgId">
                <input type="hidden" id="orgParentId">
                <input type="hidden" id="orgType">
                <!-- Nama Karyawan (Hanya untuk Posisi) -->
                <div id="posEmployeeField" style="display: none;">
                    <div class="form-group">
                        <label>Nama Karyawan</label>
                        <input type="text" id="posEmployeeName" placeholder="Nama lengkap karyawan">
                    </div>
                </div>

                <div class="form-group">
                    <label id="labelOrgName">Nama Divisi</label>
                    <input type="text" id="orgName" placeholder="Masukkan nama" required>
                </div>
                <!-- Contact Fields (Hanya untuk Posisi) -->
                <div id="posContactFields" style="display: none;">
                    <div class="form-group">
                        <label>Email Karyawan</label>
                        <input type="email" id="posEmail" placeholder="contoh@mail.com">
                    </div>
                    <div class="form-group">
                        <label>No HP</label>
                        <input type="text" id="posPhone" placeholder="Masukkan no HP">
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Gaji Pokok</label>
                            <input type="number" id="posSalary" placeholder="0">
                        </div>
                        <div class="form-group">
                            <label>Tunjangan</label>
                            <input type="number" id="posAllowance" placeholder="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Potongan</label>
                        <input type="number" id="posDeduction" placeholder="0">
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
                    <label>Pilih Skema Payroll</label>
                    <select id="setupPayrollScheme" required>
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

    <script>
        const BASE_URL = "<?= base_url() ?>";
    </script>
    <script src="<?= base_url('js/app.js') ?>"></script>
</body>

</html>

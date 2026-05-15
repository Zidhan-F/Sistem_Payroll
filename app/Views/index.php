<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Payroll - B2B SaaS</title>
    <meta name="description" content="Sistem Payroll B2B SaaS untuk manajemen gaji, PKWT, dan slip karyawan">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div style="padding: 0 25px; display: flex; align-items: center; gap: 10px;">
            <div style="width: 35px; height: 35px; background: var(--primary-color); border-radius: 8px; display: grid; place-items: center; color: white;">
                <i class="fas fa-building"></i>
            </div>
            <h3 style="color: var(--primary-dark)">Payroll App</h3>
        </div>
        <ul class="sidebar-menu">
            <li id="menuDashboard" class="active" onclick="switchView('dashboard')">
                <i class="fas fa-chart-line"></i><span>Dashboard</span>
            </li>
            <li id="menuKlien" onclick="switchView('klien')">
                <i class="fas fa-building"></i><span>Manajemen Klien</span>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <div class="header-left">
                <i id="toggleSidebar" class="fas fa-bars" style="cursor: pointer;"></i>
                <h2 id="viewTitle" style="font-size: 18px;">Dashboard</h2>
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
                    <div class="stat-card"><div class="stat-icon" style="background: rgba(243,156,18,0.1); color: var(--primary-color);"><i class="fas fa-users"></i></div><div class="stat-info"><h4 id="statTotalKlien">0</h4><p>Total Klien</p></div></div>
                    <div class="stat-card"><div class="stat-icon" style="background: rgba(52,152,219,0.1); color: var(--info);"><i class="fas fa-sitemap"></i></div><div class="stat-info"><h4 id="statTotalDivisi">0</h4><p>Total Divisi</p></div></div>
                    <div class="stat-card"><div class="stat-icon" style="background: rgba(46,204,113,0.1); color: var(--success);"><i class="fas fa-user-tie"></i></div><div class="stat-info"><h4 id="statTotalKaryawan">0</h4><p>Total Karyawan</p></div></div>
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
                        <button class="btn-add" onclick="bukaModal('tambah')"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                    <div id="tabelKlienContainer">
                        <table><thead><tr><th>No Klien</th><th>Nama Klien</th><th>Kontak</th><th>Sektor</th><th>Alamat</th><th>Status</th><th>Action</th></tr></thead><tbody id="tabelKlienBody"></tbody></table>
                    </div>

                    <!-- Client Detail (Hidden by default) -->
                    <div id="clientOrgDetail" style="display: none; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                        <div class="section-header" style="flex-direction: column; align-items: flex-start; gap: 15px;">
                            <button class="btn-cancel" onclick="backToClientList()" style="padding: 5px 12px; font-size: 13px;"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Klien</button>
                            <h3 id="clientDetailTitle" style="font-size: 18px; color: var(--primary-dark); margin-bottom: 5px;">Struktur Klien</h3>
                            <!-- Tab System -->
                            <div class="tab-container" style="display: flex; gap: 0; border-bottom: 2px solid #eee; width: 100%;">
                                <div id="tabStruktur" class="tab-item active" onclick="switchClientTab('struktur')"><i class="fas fa-sitemap"></i> Struktur</div>
                                <div id="tabKaryawan" class="tab-item" onclick="switchClientTab('karyawan')"><i class="fas fa-user-friends"></i> Karyawan</div>
                                <div id="tabPKWT" class="tab-item" onclick="switchClientTab('pkwt')"><i class="fas fa-file-contract"></i> PKWT</div>
                                <div id="tabKomponen" class="tab-item" onclick="switchClientTab('komponen')"><i class="fas fa-puzzle-piece"></i> Komponen</div>
                                <div id="tabPayroll" class="tab-item" onclick="switchClientTab('payroll')"><i class="fas fa-file-invoice-dollar"></i> Payroll</div>
                            </div>
                        </div>
                        
                        <!-- Tab: Struktur -->
                        <div id="contentStruktur" class="client-tab-content"><div id="clientOrgContainer" class="org-tree"></div></div>

                        <!-- Tab: Karyawan -->
                        <div id="contentKaryawan" class="client-tab-content" style="display: none; padding-top: 20px;">
                            <div class="content-card" style="border: 1px solid #eee; box-shadow: none; margin-bottom: 30px; background: #f8fafc;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                    <h4 style="color: var(--primary-dark); margin: 0;"><i class="fas fa-cog" style="color: var(--primary-color);"></i> Konfigurasi Skema Payroll & Pajak</h4>
                                </div>
                                <form id="formSchema">
                                    <div class="form-grid">
                                        <div class="form-group"><label>BPJS Kesehatan (%)</label><input type="number" step="0.01" id="schemaBpjsKes" value="1.00"></div>
                                        <div class="form-group"><label>BPJS Ketenagakerjaan (%)</label><input type="number" step="0.01" id="schemaBpjsJht" value="2.00"></div>
                                        <div class="form-group"><label>Metode Pajak</label><select id="schemaTaxMethod"><option value="Gross">Gross (Pajak ditanggung Karyawan)</option><option value="Net">Net (Pajak ditanggung Perusahaan)</option><option value="Gross Up">Gross Up (Tunjangan Pajak)</option></select></div>
                                        <div class="form-group"><label>Tanggal Cut Off</label><div style="display: flex; align-items: center; gap: 10px;"><input type="number" id="schemaCutOffStart" value="21" style="width: 60px; padding: 10px;"> <span style="font-size: 14px; color: #64748b;">s/d</span> <input type="number" id="schemaCutOffEnd" value="20" style="width: 60px; padding: 10px;"></div></div>
                                    </div>
                                    <button type="submit" class="btn-save" style="margin-top: 15px; padding: 10px 20px; font-size: 13px;"><i class="fas fa-save"></i> Simpan Skema</button>
                                </form>
                            </div>
                            <div class="section-header" style="margin-bottom: 20px;">
                                <h4 style="color: var(--primary-dark); margin: 0;"><i class="fas fa-users" style="color: var(--primary-color);"></i> Daftar Karyawan</h4>
                                <button class="btn-add" onclick="bukaModalKaryawanSpecific()"><i class="fas fa-plus"></i> Tambah Karyawan</button>
                            </div>
                            <div id="tabelKaryawanClientContainer">
                                <table><thead><tr><th>Nama</th><th>NIK</th><th>PTKP</th><th>Gaji Pokok</th><th>Status</th><th>Action</th></tr></thead><tbody id="tabelKaryawanClientBody"></tbody></table>
                            </div>
                        </div>

                        <!-- Tab: PKWT -->
                        <div id="contentPKWT" class="client-tab-content" style="display: none; padding-top: 20px;">
                            <div class="section-header" style="margin-bottom: 20px;">
                                <h4 style="color: var(--primary-dark); margin: 0;"><i class="fas fa-file-contract" style="color: var(--info);"></i> Daftar PKWT / Kontrak</h4>
                                <button class="btn-add" onclick="bukaModalPKWT()"><i class="fas fa-plus"></i> Buat PKWT</button>
                            </div>
                            <table><thead><tr><th>No Kontrak</th><th>Karyawan</th><th>Periode</th><th>Gaji Pokok</th><th>Status</th><th>Action</th></tr></thead><tbody id="tabelPKWTBody"></tbody></table>
                        </div>

                        <!-- Tab: Komponen Payroll -->
                        <div id="contentKomponen" class="client-tab-content" style="display: none; padding-top: 20px;">
                            <div class="section-header" style="margin-bottom: 20px;">
                                <h4 style="color: var(--primary-dark); margin: 0;"><i class="fas fa-puzzle-piece" style="color: #8b5cf6;"></i> Komponen Payroll (Tunjangan & Potongan)</h4>
                                <button class="btn-add" style="background: #8b5cf6;" onclick="bukaModalKomponen()"><i class="fas fa-plus"></i> Tambah Komponen</button>
                            </div>
                            <div id="komponenList"></div>
                        </div>

                        <!-- Tab: Payroll -->
                        <div id="contentPayroll" class="client-tab-content" style="display: none; padding-top: 20px;">
                            <!-- Stepper -->
                            <div class="payroll-stepper" id="payrollStepper">
                                <div class="step active" data-step="1"><div class="step-circle">1</div><div class="step-label">Input Cut-Off</div></div>
                                <div class="step-line"></div>
                                <div class="step" data-step="2"><div class="step-circle">2</div><div class="step-label">Generate Gaji</div></div>
                                <div class="step-line"></div>
                                <div class="step" data-step="3"><div class="step-circle">3</div><div class="step-label">Pengecekan</div></div>
                                <div class="step-line"></div>
                                <div class="step" data-step="4"><div class="step-circle">4</div><div class="step-label">Approval</div></div>
                                <div class="step-line"></div>
                                <div class="step" data-step="5"><div class="step-circle">5</div><div class="step-label">Slip Gaji</div></div>
                            </div>
                            <div style="display: flex; gap: 15px; margin-bottom: 25px; background: #f8f9fa; padding: 20px; border-radius: 12px; align-items: flex-end;">
                                <div class="form-group" style="margin-bottom: 0;"><label>Bulan</label><select id="payrollBulan" style="padding: 8px;"><option value="1">Januari</option><option value="2">Februari</option><option value="3">Maret</option><option value="4">April</option><option value="5">Mei</option><option value="6">Juni</option><option value="7">Juli</option><option value="8">Agustus</option><option value="9">September</option><option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option></select></div>
                                <div class="form-group" style="margin-bottom: 0;"><label>Tahun</label><select id="payrollTahun" style="padding: 8px;"><option value="2026">2026</option><option value="2027">2027</option></select></div>
                                <button class="btn-add" style="background: var(--info);" onclick="filterPayrollByClient()"><i class="fas fa-sync"></i> Refresh</button>
                            </div>
                            <div id="tabelPayrollContainer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay -->
    <div id="overlay" onclick="tutupSemuaModal()"></div>

    <!-- Modal: Client -->
    <div id="modalClient">
        <div class="modal-header"><h3 id="modalTitle">Tambah Data Client</h3><i class="fas fa-times" style="cursor: pointer;" onclick="tutupModal()"></i></div>
        <form id="formKlien">
            <div class="modal-body">
                <input type="hidden" id="clientId">
                <div class="form-grid">
                    <div class="form-group"><label>No Klien</label><input type="text" id="noKlien" placeholder="CLI-XXXX" readonly style="background: #f0f0f0;"></div>
                    <div class="form-group"><label>Nama Klien</label><input type="text" id="namaKlien" placeholder="Masukkan nama klien" required></div>
                    <div class="form-group"><label>Email Klien</label><input type="email" id="emailKlien" placeholder="klien@gmail.com" required></div>
                    <div class="form-group"><label>No Telepon</label><input type="text" id="teleponKlien" placeholder="0812xxxx" required></div>
                    <div class="form-group"><label>Pilih Sektor</label><select id="sektorKlien" required><option value="">-- Pilih Sektor --</option><option value="Retail">Retail</option><option value="Manufaktur">Manufaktur</option><option value="Jasa">Jasa</option><option value="Teknologi">Teknologi</option></select></div>
                    <div class="form-group"><label>NIB</label><input type="number" id="nib" placeholder="Masukkan NIB" required></div>
                    <div class="form-group"><label>NPWP</label><input type="number" id="npwp" placeholder="Masukkan NPWP" required></div>
                    <div class="form-group"><label>Tanggal Bergabung</label><input type="date" id="tanggalBergabung" required></div>
                    <div class="form-group"><label>Status</label><select id="statusKlien" required><option value="Aktif">Aktif</option><option value="Non-Aktif">Non-Aktif</option></select></div>
                    <div class="form-group full-width"><label>Alamat</label><textarea id="alamat" rows="3" placeholder="Masukkan alamat lengkap" required></textarea></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-cancel" onclick="tutupModal()">Batal</button><button type="submit" id="btnSubmit" class="btn-save">Simpan</button></div>
        </form>
    </div>

    <!-- Modal: Organisasi -->
    <div id="modalOrg" class="modal-box" style="width: 450px;">
        <div class="modal-header"><h3 id="modalOrgTitle">Tambah Divisi</h3><i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalOrg()"></i></div>
        <form id="formOrg">
            <div class="modal-body">
                <input type="hidden" id="orgId"><input type="hidden" id="orgParentId"><input type="hidden" id="orgType">
                <div id="posEmployeeField" style="display: none;"><div class="form-group"><label>Nama Karyawan</label><input type="text" id="posEmployeeName" placeholder="Masukkan nama lengkap"></div></div>
                <div class="form-group"><label id="labelOrgName">Nama Divisi</label><input type="text" id="orgName" placeholder="Masukkan nama" required></div>
                <div id="posExtraFields" style="display: none;">
                    <div class="form-group"><label>NIK Karyawan <span style="color: red;">*</span></label><input type="text" id="posNik" placeholder="Masukkan NIK"></div>
                    <div class="form-group"><label>Email / No HP</label><div style="display: flex; gap: 10px;"><input type="email" id="posEmail" placeholder="Email" style="flex: 1;"><input type="text" id="posPhone" placeholder="No HP" style="flex: 1;"></div></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-cancel" onclick="tutupModalOrg()">Batal</button><button type="submit" class="btn-save">Simpan</button></div>
        </form>
    </div>

    <!-- Modal: Karyawan -->
    <div id="modalKaryawan" class="modal-box" style="width: 800px;">
        <div class="modal-header"><h3 id="modalKaryawanTitle">Tambah Karyawan</h3><i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalKaryawan()"></i></div>
        <form id="formKaryawan">
            <div class="modal-body">
                <input type="hidden" id="employeeId">
                <div class="form-grid">
                    <div class="form-group"><label>NIK</label><input type="text" id="empNik" placeholder="Nomor Induk Karyawan" required></div>
                    <div class="form-group"><label>Nama Lengkap</label><input type="text" id="empNama" placeholder="Nama Lengkap" required></div>
                    <div class="form-group"><label>Email</label><input type="email" id="empEmail" placeholder="email@karyawan.com"></div>
                    <div class="form-group"><label>Bank</label><select id="empBankName"><option value="">-- Pilih Bank --</option><option value="BCA">BCA</option><option value="Mandiri">Mandiri</option><option value="BNI">BNI</option><option value="BRI">BRI</option><option value="Lainnya">Lainnya</option></select></div>
                    <div class="form-group"><label>No Rekening</label><input type="text" id="empRekening" placeholder="Nomor Rekening" required></div>
                    <div class="form-group"><label>Gaji Pokok</label><input type="number" id="empGaji" placeholder="Nominal Gaji Pokok" required></div>
                    <div class="form-group"><label>Status Pajak (PTKP)</label><select id="empPtkp"><option value="TK/0">TK/0 (Lajang)</option><option value="K/0">K/0 (Menikah, 0 Anak)</option><option value="K/1">K/1 (Menikah, 1 Anak)</option><option value="K/2">K/2 (Menikah, 2 Anak)</option><option value="K/3">K/3 (Menikah, 3 Anak)</option></select></div>
                    <div class="form-group"><label>Klien</label><select id="empClientId" onchange="loadPositions(this.value)" required><option value="">-- Pilih Klien --</option></select></div>
                    <div class="form-group"><label>Posisi</label><select id="empPositionId" required><option value="">-- Pilih Posisi --</option></select></div>
                    <div class="form-group"><label>Tanggal Masuk</label><input type="date" id="empTglMasuk" required></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-cancel" onclick="tutupModalKaryawan()">Batal</button><button type="submit" class="btn-save">Simpan</button></div>
        </form>
    </div>

    <!-- Modal: PKWT -->
    <div id="modalPKWT" class="modal-box" style="width: 600px;">
        <div class="modal-header" style="background: var(--info);"><h3>Buat PKWT / Kontrak Kerja</h3><i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalPKWT()"></i></div>
        <form id="formPKWT">
            <div class="modal-body">
                <input type="hidden" id="pkwtId">
                <div class="form-grid">
                    <div class="form-group full-width"><label>Karyawan</label><select id="pkwtEmployeeId" required><option value="">-- Pilih Karyawan --</option></select></div>
                    <div class="form-group"><label>Tanggal Mulai</label><input type="date" id="pkwtTglMulai" required></div>
                    <div class="form-group"><label>Tanggal Berakhir</label><input type="date" id="pkwtTglBerakhir" required></div>
                    <div class="form-group full-width"><label>Gaji Pokok (sesuai skema)</label><input type="number" id="pkwtGajiPokok" placeholder="Nominal gaji pokok" required></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-cancel" onclick="tutupModalPKWT()">Batal</button><button type="submit" class="btn-save">Simpan PKWT</button></div>
        </form>
    </div>

    <!-- Modal: Komponen Payroll -->
    <div id="modalKomponen" class="modal-box" style="width: 500px;">
        <div class="modal-header" style="background: #8b5cf6;"><h3>Tambah Komponen Payroll</h3><i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalKomponen()"></i></div>
        <form id="formKomponen">
            <div class="modal-body">
                <input type="hidden" id="komponenId">
                <div class="form-group"><label>Nama Komponen</label><input type="text" id="komponenNama" placeholder="e.g. Tunjangan Makan, Insentif" required></div>
                <div class="form-group"><label>Tipe</label><select id="komponenTipe" required><option value="Tunjangan">Tunjangan (+)</option><option value="Potongan">Potongan (-)</option></select></div>
                <div class="form-group"><label>Jenis Nilai</label><select id="komponenJenis" required><option value="Tetap">Nominal Tetap (Rp)</option><option value="Persentase">Persentase dari Gaji Pokok (%)</option></select></div>
                <div class="form-group"><label>Nilai</label><input type="number" step="0.01" id="komponenNilai" placeholder="Masukkan nilai" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-cancel" onclick="tutupModalKomponen()">Batal</button><button type="submit" class="btn-save" style="background: #8b5cf6;">Simpan</button></div>
        </form>
    </div>

    <!-- Modal: Confirm -->
    <div id="modalConfirm" class="modal-box modal-confirm" style="width: 400px; padding: 30px; text-align: center;">
        <div style="width: 60px; height: 60px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: grid; place-items: center; margin: 0 auto 20px; font-size: 24px;"><i class="fas fa-exclamation-triangle"></i></div>
        <h3 style="margin-bottom: 10px; color: #111827;">Konfirmasi</h3>
        <p id="confirmMessage" style="color: #6b7280; font-size: 14px; margin-bottom: 25px;"></p>
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button id="btnConfirmCancel" class="btn-cancel" style="flex: 1; padding: 12px;">Batal</button>
            <button id="btnConfirmYes" class="btn-save" style="flex: 1; padding: 12px; background: #ef4444;">Ya</button>
        </div>
    </div>

    <!-- Modal: Slip Gaji -->
    <div id="modalSlip" class="modal-box" style="width: 600px;">
        <div class="modal-header" style="background: var(--info);"><h3>Slip Gaji</h3><i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalSlip()"></i></div>
        <div class="modal-body" id="slipContent"></div>
        <div class="modal-footer"><button class="btn-cancel" onclick="tutupModalSlip()">Tutup</button><button class="btn-save" style="background: var(--success);" onclick="window.print()"><i class="fas fa-print"></i> Cetak</button></div>
    </div>

    <script src="<?= base_url('js/app-core.js') ?>"></script>
    <script src="<?= base_url('js/app-org.js') ?>"></script>
    <script src="<?= base_url('js/app-payroll.js') ?>"></script>
</body>
</html>
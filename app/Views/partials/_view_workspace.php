            <!-- Section: Client Workspace -->
            <div id="viewClientWorkspace" class="view-section">
                <!-- Header with Client Name and Back Button -->
                <div class="workspace-header" style="margin-bottom: 25px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <button class="btn-back" onclick="backToClientList()">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </button>
                            <h2 id="clientWorkspaceTitle" style="font-size: 22px; font-weight: 700; color: var(--secondary-color); margin: 0;">ðŸ¢ -</h2>
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
                                                    <th>Divisi</th>
                                                    <th>Department</th>
                                                    <th>Posisi / Jabatan</th>
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
                                                    <th>NIK</th>
                                                    <th>Nama Karyawan</th>
                                                    <th>Lahir</th>
                                                    <th>NPWP</th>
                                                    <th>Perusahaan / Klien</th>
                                                    <th>Kontrak</th>
                                                    <th>Lokasi Kerja</th>
                                                    <th>Status Nikah</th>
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
                                        <span style="font-weight: 500; color: #64748b;">Skema BPJS & Pajak</span>
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
                            <div class="content-card" style="max-width: 100%; border: 1px solid #e2e8f0; border-radius: 16px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">

                                <div style="display: flex; flex-direction: column; gap: 20px;">
                                    
                                    <!-- Section: Daftar Skema Payroll Klien -->
                                    <div style="margin-bottom: 30px;">
                                        <div class="section-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                            <div>
                                                <h3 style="font-size: 18px; color: var(--secondary-color); margin: 0; font-weight: 700;">Daftar Skema Payroll</h3>
                                                <p style="font-size: 13px; color: #64748b; margin: 5px 0 0 0;">Kelola skema payroll untuk setiap divisi, departemen, dan posisi</p>
                                            </div>
                                            <button class="btn-add" onclick="openModalPilihanSkema()" style="background: #f39c12; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; font-size: 14px; box-shadow: 0 2px 8px rgba(243, 156, 18, 0.25); transition: all 0.3s;">
                                                <i class="fas fa-plus"></i> Tambah Skema
                                            </button>
                                        </div>
                                        
                                        <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px;">
                                            <table style="width: 100%; border-collapse: collapse; background: white;">
                                                <thead>
                                                    <tr style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white;">
                                                        <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 13px;">Divisi</th>
                                                        <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 13px;">Departemen</th>
                                                        <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 13px;">Posisi</th>
                                                        <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 13px;">Skema Payroll</th>
                                                        <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 13px;">Skema BPJS & Pajak</th>
                                                        <th style="padding: 15px; text-align: center; font-weight: 600; font-size: 13px;">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tabelPilihanSkemaKlien">
                                                    <tr>
                                                        <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                                                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                                                            Belum ada skema payroll yang terdaftar. Klik tombol "Tambah Skema" untuk mengkonfigurasi.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <!-- Hidden Fields to maintain JS compatibility -->
                                    <div style="display: none;">
                                        <select id="pilihanSkemaPayrollTipe">
                                            <option value="Template" selected>Template</option>
                                        </select>
                                        <div id="pilihanSkemaPayrollWilayahContainer">
                                            <select id="pilihanSkemaPayrollWilayah"></select>
                                        </div>
                                        <div id="pilihanSkemaPayrollNominalContainer">
                                            <input type="number" id="pilihanSkemaPayrollNominal">
                                        </div>
                                        <div id="pilihanSkemaPayrollTemplateContainer"></div>
                                        <select id="pilihanSkemaKompensasi">
                                            <option value="">-- Pilih Skema Komponen --</option>
                                        </select>
                                        <div id="pilihanKompensasiSummary"></div>
                                        <input type="text" id="pilihanSkemaNamaKlien">
                                        <select id="pilihanSkemaDivisi"></select>
                                        <select id="pilihanSkemaDepartemen"></select>
                                        <select id="pilihanSkemaPosisi"></select>
                                        <select id="pilihanSkemaHariKerja"></select>
                                        <select id="pilihanSkemaPayroll"></select>
                                        <select id="pilihanSkemaPajak"></select>
                                    </div>
                                </div>
                            </div>
                        </div>



                    </div>
            </div>

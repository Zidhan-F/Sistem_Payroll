            <!-- Section: Client Workspace -->
            <div id="viewClientWorkspace" class="view-section">
                <!-- Header with Client Name and Back Button -->
                <div class="workspace-header" style="margin-bottom: 25px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <button class="btn-back" onclick="backToClientList()">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <h2 id="clientWorkspaceTitle" style="font-size: 22px; font-weight: 700; color: var(--secondary-color); margin: 0;">🏢 -</h2>
                        </div>
                        <div id="clientWorkspaceMeta" style="font-size: 14px; color: var(--text-muted); font-weight: 500;">
                            Sector: <strong id="clientWorkspaceSektor" style="color: var(--primary-color);">-</strong>
                        </div>
                    </div>
                </div>

                <!-- Workspace Tabs (Horizontal below client name) -->
                <div class="workspace-tabs">
                    <button class="ws-tab active" data-wtab="karyawan" onclick="switchWorkspaceTab('karyawan')">Employee</button>
                    <button class="ws-tab" data-wtab="struktur" onclick="switchWorkspaceTab('struktur')">Organization Structure</button>
                    <button class="ws-tab" data-wtab="kompensasi" onclick="switchWorkspaceTab('kompensasi')">Scheme Choices</button>
                    <button class="ws-tab" data-wtab="setup" onclick="switchWorkspaceTab('setup')">Setup Payroll</button>
                    <button class="ws-tab" data-wtab="pkwt" onclick="switchWorkspaceTab('pkwt')">PKWT Contract</button>
                    <button class="ws-tab" data-wtab="proses" onclick="switchWorkspaceTab('proses')">Process Payroll</button>
                </div>

                <!-- Content Panels -->
                <div class="workspace-content">
                        <!-- Panel: Karyawan -->
                        <div id="viewKaryawan" class="w-tab-panel active">
                            <!-- Sub Tabs for Workspace Karyawan -->
                            <div class="sub-tabs-container" style="display: flex; gap: 8px; border-bottom: 2px solid #f1f5f9; margin-bottom: 20px; padding-bottom: 2px;">
                                <button class="sub-tab-btn active" id="subTabLokasiKerja" onclick="switchClientKaryawanSubTab('lokasi_kerja')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: var(--primary-color); cursor: pointer; border-bottom: 2px solid var(--primary-color); margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Work Location</button>
                                <button class="sub-tab-btn" id="subTabKaryawanData" onclick="switchClientKaryawanSubTab('karyawan_data')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Employee Data</button>
                            </div>

                            <!-- Sub Panel 1: Lokasi Kerja (Active by default) -->
                            <div id="panelLokasiKerja" class="client-karyawan-subpanel">
                                <div class="content-card">
                                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Work Location List</h3>
                                    </div>
                                    <div class="table-container">
                                        <table style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>Work Location</th>
                                                    <th>Location Code</th>
                                                    <th>Province</th>
                                                    <th>City/Regency</th>
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
                                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Employee Data</h3>
                                        <div class="search-box" style="margin-bottom: 0;">
                                            <i class="fas fa-search"></i>
                                            <input type="text" id="searchKaryawan" placeholder="Search name or position..." onkeyup="filterKaryawan()">
                                        </div>
                                    </div>
                                    <div class="table-container">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>NIK</th>
                                                    <th>Employee Name</th>
                                                    <th>Birth Date</th>
                                                    <th>NPWP</th>
                                                    <th>Company / Client</th>
                                                    <th>Contract</th>
                                                    <th>Work Location</th>
                                                    <th>Marital Status</th>
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
                                    <h3 style="font-size: 16px; color: var(--secondary-color);">Organization Structure</h3>
                                    <button class="btn-add" onclick="bukaModalOrg('divisi', 'tambah')">
                                        <i class="fas fa-plus"></i> Add Division
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
                                    <h3 style="font-size: 18px; font-weight: 700; color: var(--secondary-color);">Client Payroll Configuration</h3>
                                    <p style="color: #64748b; font-size: 13px;">Settings for salary schemes, taxes, and monthly cut-off cycles.</p>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 15px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                        <span style="font-weight: 500; color: #64748b;">Client Name</span>
                                        <strong id="wSetupClientName" style="color: #1e293b;">-</strong>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                        <span style="font-weight: 500; color: #64748b;">Payroll Scheme</span>
                                        <span id="wSetupPayrollScheme" class="scheme-badge bulanan" style="font-weight: 600;">-</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                        <span style="font-weight: 500; color: #64748b;">BPJS & Tax Scheme</span>
                                        <span id="wSetupTaxScheme" class="scheme-badge" style="background:#fee2e2; color:#dc2626; font-weight: 600;">-</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                        <span style="font-weight: 500; color: #64748b;">Payment Date</span>
                                        <strong id="wSetupPayDate" style="color: #1e293b;">Date -</strong>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding-bottom: 5px;">
                                        <span style="font-weight: 500; color: #64748b;">Cut-Off Cycle</span>
                                        <strong id="wSetupCutoff" style="color: #1e293b;">-</strong>
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: center;">
                                    <button class="btn-save" onclick="bukaModalSetup(window.selectedClientId, window.selectedClientName)" style="background: var(--primary-color); display: flex; align-items: center; gap: 8px; font-weight: 600; padding: 12px 24px; border-radius: 10px;">
                                        <i class="fas fa-edit"></i> Edit Configuration
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Panel: Kontrak PKWT -->
                        <div id="viewPkwt" class="w-tab-panel">
                            <div class="content-card">
                                <div class="section-header">
                                    <h3 style="font-size: 16px; color: var(--secondary-color);">PKWT Contract List</h3>
                                    <button class="btn-add" onclick="bukaModalPKWT()">
                                        <i class="fas fa-plus"></i> Create New Contract
                                    </button>
                                </div>
                                <div class="table-container">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Client</th>
                                                <th>Position</th>
                                                <th>Start Date</th>
                                                <th>Status</th>
                                                <th>Basic Salary</th>
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
                                <h3 style="font-size: 16px; color: var(--secondary-color);">Monthly Salary Processing</h3>
                                <div style="display: flex; gap: 12px; align-items: center;">
                                    <select id="selectPeriodInput" onchange="if(this.value) selectPeriod(this.value, this.options[this.selectedIndex].text)" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e0; outline: none; background: white; font-weight: 600; color: #4a5568; cursor: pointer; min-width: 200px;">
                                        <option value="">-- Select Period --</option>
                                    </select>
                                    <button class="btn-add" onclick="bukaModalPeriode()" style="background: #2c3e50; font-weight: 600;">
                                        <i class="fas fa-calendar-plus"></i> Open New Period
                                    </button>
                                </div>
                            </div>

                            <div class="content-card">
                                <div id="prosesEmptyState" style="text-align: center; padding: 60px 20px; color: #a0aec0;">
                                    <i class="fas fa-calendar-check" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5; color: var(--primary-color);"></i>
                                    <h4 style="font-weight: 700; color: var(--secondary-color); margin-bottom: 5px;">No Period Selected Yet</h4>
                                    <p style="font-size: 13px; color: #718096; max-width: 400px; margin: 0 auto;">Please select a period from the dropdown above or open a new period to process payroll.</p>
                                </div>

                                <div id="prosesActions" style="display: none;">
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <h4 id="activePeriodName" style="margin:0; color: var(--primary-color);">Select Period</h4>
                                            <span id="activePeriodStatus" class="status-badge success">Open Period</span>
                                        </div>
                                        <button class="btn-save" onclick="generateGaji()" style="background: var(--primary-color);">
                                            <i class="fas fa-sync-alt"></i> Generate Salary
                                        </button>
                                    </div>
                                    <div class="table-container">
                                        <table id="tabelCutOff">
                                            <thead>
                                                <tr>
                                                    <th>Employee Name</th>
                                                    <th>Working Days</th>
                                                    <th>Overtime</th>
                                                    <th>Deduction</th>
                                                    <th>Bonus</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabelCutOffBody"></tbody>
                                        </table>
                                    </div>
                                    <div id="resultSection" style="margin-top: 30px; display: none;">
                                        <h4 style="font-size: 14px; margin-bottom: 10px; color: var(--success);">SALARY CALCULATION RESULTS</h4>
                                        <div class="table-container">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>Employee</th>
                                                        <th>Earnings</th>
                                                        <th>Deductions</th>
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
                                                <h3 style="font-size: 18px; color: var(--secondary-color); margin: 0; font-weight: 700;">Payroll Scheme List</h3>
                                                <p style="font-size: 13px; color: #64748b; margin: 5px 0 0 0;">Manage payroll schemes for each division, department, and position</p>
                                            </div>
                                            <button class="btn-add" onclick="openModalPilihanSkema()" style="background: #f39c12; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; font-size: 14px; box-shadow: 0 2px 8px rgba(243, 156, 18, 0.25); transition: all 0.3s;">
                                                <i class="fas fa-plus"></i> Add Scheme
                                            </button>
                                        </div>
                                        
                                        <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px;">
                                            <table style="width: 100%; border-collapse: collapse; background: white;">
                                                <thead>
                                                    <tr style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white;">
                                                        <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 13px;">Division</th>
                                                        <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 13px;">Department</th>
                                                        <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 13px;">Position</th>
                                                        <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 13px;">Payroll Scheme</th>
                                                        <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 13px;">BPJS & Tax Scheme</th>
                                                        <th style="padding: 15px; text-align: center; font-weight: 600; font-size: 13px;">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tabelPilihanSkemaKlien">
                                                    <tr>
                                                        <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                                                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                                                            No payroll schemes registered yet. Click the "Add Scheme" button to configure.
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
                                            <option value="">-- Select Allowance Scheme --</option>
                                        </select>
                                        <div id="pilihanKompensasiSummary"></div>
                                        <input type="text" id="pilihanSkemaNamaKlien">
                                        <select id="pilihanSkemaDivisi"></select>
                                        <select id="pilihanSkemaDepartemen"></select>
                                        <select id="pilihanSkemaPosisi"></select>
                                        <select id="pilihanSkemaHariKerja"></select>
                                        <select id="pilihanSkemaPayroll"></select>
                                        <select id="pilihanSkemaPajak"></select>
                                        <select id="pilihanSkemaLevel">
                                            <option value="general" selected>general</option>
                                        </select>
                                        <input type="hidden" id="pilihanSkemaDivisiId">
                                        <input type="hidden" id="pilihanSkemaDeptId">
                                        <input type="hidden" id="pilihanSkemaPosisiId">
                                    </div>
                                </div>
                            </div>
                        </div>



                    </div>
            </div>

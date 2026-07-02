<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiPayroll - Client Management</title>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/ai-assistant.css?v=' . time()) ?>">
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
                            <p>Total Clients</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(52, 152, 219, 0.1); color: var(--info);">
                            <i class="fas fa-sitemap"></i>
                        </div>
                        <div class="stat-info">
                            <h4 id="statTotalDivisi">0</h4>
                            <p>Total Divisions</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(46, 204, 113, 0.1); color: var(--success);">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="stat-info">
                            <h4 id="statTotalKaryawan">0</h4>
                            <p>Total Employees</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Panel -->
                <div class="content-card" style="margin-top: 30px; padding: 25px;">
                    <div style="margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Quick Actions Menu</h3>
                        <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Navigation shortcuts to perform key administrative tasks instantly.</p>
                    </div>
                    
                    <div class="quick-actions-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                        <!-- Action 0: Dashboard -->
                        <div id="qaDashboard" class="quick-action-card" onclick="quickAction('dashboard')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(63, 81, 181, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/1828/1828765.png" alt="Dashboard" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Dashboard</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Return to the main page.</p>
                            </div>
                        </div>

                        <!-- Action 1: Tambah Klien -->
                        <div id="qaAddClient" class="quick-action-card" onclick="quickAction('tambah-klien')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(243, 156, 18, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/4300/4300058.png" alt="Klien" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Add Client</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Register a new company.</p>
                            </div>
                        </div>

                        <!-- Action 2: Tambah Karyawan -->
                        <div id="qaAddEmployee" class="quick-action-card" onclick="quickAction('tambah-karyawan')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(52, 152, 219, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Karyawan" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Add Employee</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Register a new employee.</p>
                            </div>
                        </div>

                        <!-- Action 8: Add STO Global -->
                        <div id="qaAddStoGlobal" class="quick-action-card" onclick="quickAction('add-sto-global')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(243, 156, 18, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/4205/4205934.png" alt="Add STO Global" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Add STO Global</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Manage divisions, departments, & positions.</p>
                            </div>
                        </div>

                        <!-- Action 3: Struktur Gaji -->
                        <div id="qaSalaryStructure" class="quick-action-card" onclick="quickAction('struktur-gaji')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(46, 204, 113, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135706.png" alt="Struktur Gaji" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Salary Structure</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Income allowance scheme.</p>
                            </div>
                        </div>



                         <!-- Action 5: Lokasi Kerja -->
                        <div id="qaWorkLocation" class="quick-action-card" onclick="quickAction('lokasi-kerja')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(26, 188, 156, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/1865/1865269.png" alt="Lokasi Kerja" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Work Location</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Manage work regions & offices.</p>
                            </div>
                        </div>

                        <!-- Action 6: Upload UMK UMP -->
                        <div id="qaUploadUmkUmp" class="quick-action-card" onclick="quickAction('upload-umk-ump')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(231, 76, 60, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/1091/1091210.png" alt="Upload UMK UMP" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Upload UMK UMP</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Upload regional minimum wage (UMP & UMK) rates.</p>
                            </div>
                        </div>

                        <!-- Action 7: Setting Skema -->
                        <div id="qaSchemeSettings" class="quick-action-card" onclick="quickAction('setting-skema')" style="background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div class="quick-action-icon-wrapper" style="width: 50px; height: 50px; border-radius: 10px; background: rgba(39, 174, 96, 0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <img src="https://cdn-icons-png.flaticon.com/512/2489/2489756.png" alt="Setting Skema" style="width: 32px; height: 32px; object-fit: contain;">
                            </div>
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary-color); margin: 0 0 2px 0;">Scheme Settings</h4>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0; line-height: 1.3;">Configure payroll parameters & templates.</p>
                            </div>
                        </div>




                    </div>
                </div>

            </div>

            <!-- Section: Klien -->
            <div id="viewKlien" class="view-section">
                <div class="content-card">
                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Client Data</h3>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <input type="text" id="cariKlienGlobal" placeholder="Search clients..." oninput="cariKlienAktif()" style="padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px; width: 250px;">
                            <button class="btn-add" onclick="bukaModal('tambah')" style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Client Name</th>
                                    <th>Client Sector</th>
                                    <th>NPWP</th>
                                    <th>NIB</th>
                                    <th>Join Date</th>
                                    <th>Address</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="tabelKlienBody">
                                <!-- Data injected by app.js -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Section: Manajemen Karyawan (Global) -->
            <div id="viewManajemenKaryawan" class="view-section">
                <div class="content-card">
                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Employee Data</h3>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <input type="text" id="cariKaryawanGlobal" placeholder="Search name, NIK, position..." oninput="cariKaryawanGlobalAktif()" style="padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px; width: 250px;">
                            <button class="btn-add" onclick="bukaModalKaryawanGlobal()" style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                                <img src="https://cdn-icons-png.flaticon.com/512/992/992651.png" style="width: 16px; height: 16px; object-fit: contain; filter: brightness(0) invert(1);"> Add Employee
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Company / Client</th>
                                    <th>Employee ID (NIK)</th>
                                    <th>Employee Name</th>
                                    <th>Place & Date of Birth</th>
                                    <th>NPWP</th>
                                    <th>Division</th>
                                    <th>Department</th>
                                    <th>Position / Role</th>
                                    <th>Work Location</th>
                                    <th>City/Regency</th>
                                    <th>Province</th>
                                    <th>Shift</th>
                                    <th>Min. Wage (UMP/UMK)</th>
                                    <th>Contract</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="tabelKaryawanGlobalBody">
                                <!-- Injected by app.js -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Section: Global Lokasi Kerja -->
            <div id="viewGlobalLokasiKerja" class="view-section">
                <div class="content-card">
                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Work Location Data</h3>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <input type="text" id="cariLokasiKerjaGlobal" placeholder="Search work location, province, city..." oninput="cariLokasiKerjaGlobalAktif()" style="padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px; width: 250px;">
                            <button id="btnTambahLokasiKerjaGlobal" class="btn-add" onclick="bukaModalLokasiKerja()" style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                                <i class="fas fa-plus"></i> Add Work Location
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Work Location</th>
                                    <th>Location Code</th>
                                    <th>Client / Company</th>
                                    <th>Province</th>
                                    <th>City/Regency</th>
                                    <th id="thActionLokasiKerjaGlobal">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tabelGlobalLokasiKerjaBody">
                                <!-- Injected by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Section: STO (Struktur Organisasi) Global -->
            <div id="viewSto" class="view-section">
                <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
                    <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                        <div>
                            <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Global STO Master</h3>
                            <p style="color: #64748b; font-size: 13px; margin: 0;">Manage Divisions, Departments, and Positions that can be used across all clients.</p>
                        </div>
                    </div>

                    <!-- Custom Tabs for Global STO -->
                    <div class="workspace-tabs">
                        <button class="ws-tab active" data-stotab="divisi" onclick="switchStoTab('divisi')">Division Master</button>
                        <button class="ws-tab" data-stotab="departemen" onclick="switchStoTab('departemen')">Department Master</button>
                        <button class="ws-tab" data-stotab="posisi" onclick="switchStoTab('posisi')">Position Master</button>
                    </div>

                    <!-- Tab Panel: Division -->
                    <div id="stoTabDivisi" class="sto-tab-panel" style="display: block;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <input type="text" id="searchGlobalDivisi" placeholder="Search division..." oninput="filterGlobalSto('divisi')" style="padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px; width: 250px;">
                            <button class="btn-add" onclick="bukaModalGlobalSto('divisi', 'tambah')" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-plus"></i> Add Global Division
                            </button>
                        </div>
                        <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f8fafc;">
                                        <th style="width: 60px; text-align: center; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">No</th>
                                        <th style="text-align: left; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">Division Name</th>
                                        <th style="width: 150px; text-align: center; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tableGlobalDivisiBody">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Panel: Department -->
                    <div id="stoTabDepartemen" class="sto-tab-panel" style="display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <input type="text" id="searchGlobalDepartemen" placeholder="Search department..." oninput="filterGlobalSto('departemen')" style="padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px; width: 250px;">
                            <button class="btn-add" onclick="bukaModalGlobalSto('departemen', 'tambah')" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-plus"></i> Add Global Department
                            </button>
                        </div>
                        <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f8fafc;">
                                        <th style="width: 60px; text-align: center; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">No</th>
                                        <th style="text-align: left; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">Department Name</th>
                                        <th style="width: 150px; text-align: center; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tableGlobalDepartemenBody">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Panel: Position -->
                    <div id="stoTabPosisi" class="sto-tab-panel" style="display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <input type="text" id="searchGlobalPosisi" placeholder="Search position..." oninput="filterGlobalSto('posisi')" style="padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px; width: 250px;">
                            <button class="btn-add" onclick="bukaModalGlobalSto('posisi', 'tambah')" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-plus"></i> Add Global Position
                            </button>
                        </div>
                        <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f8fafc;">
                                        <th style="width: 60px; text-align: center; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">No</th>
                                        <th style="text-align: left; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">Position Name</th>
                                        <th style="width: 150px; text-align: center; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tableGlobalPosisiBody">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal: Global STO CRUD -->
            <div id="modalGlobalSto" class="modal" style="display: none; position: fixed; z-index: 1000; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 400px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                    <h4 id="globalStoModalTitle" style="margin: 0; color: var(--secondary-color); font-weight: 700; font-size: 16px;">Add Global Division</h4>
                    <button onclick="tutupModalGlobalSto()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;"><i class="fas fa-times"></i></button>
                </div>

                
                <form id="formGlobalSto" onsubmit="handleGlobalStoSubmit(event)">
                    <input type="hidden" id="globalStoType" value="divisi">
                    <input type="hidden" id="globalStoId" value="">
                    <div style="margin-bottom: 20px;">
                        <label id="globalStoLabelName" style="display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 13px;">Division Name</label>
                        <input type="text" id="globalStoName" required style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px; box-sizing: border-box;" placeholder="Enter name...">
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn-back" onclick="tutupModalGlobalSto()" style="padding: 10px 16px;">Cancel</button>
                        <button type="submit" class="btn-add" style="padding: 10px 20px; background: var(--primary-color);">Save</button>
                    </div>
                </form>                
            </div>

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
                    <button class="ws-tab active" data-wtab="karyawan" onclick="switchWorkspaceTab('karyawan')">Employees</button>
                    <button class="ws-tab" data-wtab="struktur" onclick="switchWorkspaceTab('struktur')">Org Structure</button>
                    <button class="ws-tab" data-wtab="kompensasi" onclick="switchWorkspaceTab('kompensasi')">Scheme Selections</button>
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
                                                    <th>Company / Client</th>
                                                    <th>Employee ID (NIK)</th>
                                                    <th>Employee Name</th>
                                                    <th>Place & Date of Birth</th>
                                                    <th>NPWP</th>
                                                    <th>Division</th>
                                                    <th>Department</th>
                                                    <th>Position / Role</th>
                                                    <th>Work Location</th>
                                                    <th>City/Regency</th>
                                                    <th>Province</th>
                                                    <th>Shift</th>
                                                    <th>Min. Wage (UMP/UMK)</th>
                                                    <th>Contract</th>
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

                        <!-- Panel: Kontrak PKWT -->
                        <div id="viewPkwt" class="w-tab-panel">
                            <div class="content-card">
                                <div class="section-header">
                                    <h3 style="font-size: 16px; color: var(--secondary-color);">PKWT Contract List</h3>
                                </div>
                                <div class="table-container">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Client</th>
                                                <th>Position</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
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
                            <!-- Sub Tabs for Process Payroll -->
                            <div class="sub-tabs-container" style="display: flex; gap: 8px; border-bottom: 2px solid #f1f5f9; margin-bottom: 20px; padding-bottom: 2px;">
                                <button class="sub-tab-btn active" id="subTabSalaryProcessing" onclick="switchPayrollProcessSubTab('processing')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: var(--primary-color); cursor: pointer; border-bottom: 2px solid var(--primary-color); margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Monthly Salary Processing</button>
                                <button class="sub-tab-btn" id="subTabCalculationResults" onclick="switchPayrollProcessSubTab('results')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Salary Calculation Results</button>
                            </div>

                            <!-- Sub Panel 1: Monthly Salary Processing -->
                            <div id="panelSalaryProcessing" class="client-proses-subpanel">
                                <div class="section-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                    <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Monthly Salary Processing</h3>
                                    <div style="display: flex; gap: 12px; align-items: center;">
                                        <select id="processMonthSelect" onchange="onProcessPeriodChange()" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e0; outline: none; background: white; font-weight: 600; color: #4a5568; cursor: pointer; min-width: 130px;">
                                            <option value="1">January</option><option value="2">February</option><option value="3">March</option>
                                            <option value="4">April</option><option value="5">May</option><option value="6">June</option>
                                            <option value="7">July</option><option value="8">August</option><option value="9">September</option>
                                            <option value="10">October</option><option value="11">November</option><option value="12">December</option>
                                        </select>
                                        <select id="processYearSelect" onchange="onProcessPeriodChange()" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e0; outline: none; background: white; font-weight: 600; color: #4a5568; cursor: pointer; min-width: 100px;">
                                            <?php 
                                            $currentYear = intval(date('Y'));
                                            for ($y = $currentYear - 2; $y <= $currentYear + 3; $y++) {
                                                $selected = ($y === $currentYear) ? 'selected' : '';
                                                echo "<option value=\"$y\" $selected>$y</option>";
                                            }
                                            ?>
                                        </select>
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
                                            <div style="display: flex; gap: 8px; align-items: center;">
                                                <button class="btn-save" onclick="downloadSalaryTemplate()" style="background: #27ae60; border-radius: 8px; padding: 10px 16px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px; border: none; color: white; cursor: pointer;">
                                                    <i class="fas fa-file-download"></i> Download Template
                                                </button>
                                                <button class="btn-save" onclick="bukaModalUploadManualSalary()" style="background: #2980b9; border-radius: 8px; padding: 10px 16px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px; border: none; color: white; cursor: pointer;">
                                                    <i class="fas fa-file-upload"></i> Upload Excel
                                                </button>
                                                <button class="btn-save" onclick="generateGaji()" style="background: var(--primary-color); border-radius: 8px; padding: 10px 16px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px; border: none; color: white; cursor: pointer;">
                                                    <i class="fas fa-sync-alt"></i> Generate Salary
                                                </button>
                                                <input type="file" id="salaryExcelFile" accept=".xlsx, .xls" onchange="uploadSalaryExcel(event)" style="display: none;">
                                            </div>
                                        </div>
                                        <div class="table-container">
                                            <table id="tabelCutOff">
                                                <thead>
                                                    <tr>
                                                        <th>Employee Name</th>
                                                        <th>Working Days</th>
                                                        <th>Overtime</th>
                                                        <th>Early Arrival</th>
                                                        <th>Deductions</th>
                                                        <th>Bonus</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tabelCutOffBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sub Panel 2: Salary Calculation Results -->
                            <div id="panelCalculationResults" class="client-proses-subpanel" style="display: none;">
                                <div class="content-card">
                                    <div id="resultsEmptyState" style="text-align: center; padding: 60px 20px; color: #a0aec0;">
                                        <i class="fas fa-calculator" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5; color: var(--primary-color);"></i>
                                        <h4 style="font-weight: 700; color: var(--secondary-color); margin-bottom: 5px;">No Period Selected Yet</h4>
                                        <p style="font-size: 13px; color: #718096; max-width: 400px; margin: 0 auto;">Please select a period in the Processing tab first to view calculation results.</p>
                                    </div>

                                    <div id="resultSection" style="display: none;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 15px;">
                                            <h4 style="font-size: 14px; margin: 0; color: #000000; font-weight: 700;">SALARY CALCULATION RESULTS</h4>
                                            <div style="display: flex; gap: 10px;">
                                                 <button class="btn-save" id="btnApproveSelectedGaji" onclick="approveSelectedGaji()" style="background: #3498db; border-radius: 8px; padding: 8px 16px; font-weight: 600; display: flex; align-items: center; gap: 8px; border: none; color: white; cursor: pointer;">
                                                     <i class="fas fa-check-double"></i> Approve Selected
                                                 </button>
                                                 <button class="btn-save" onclick="exportGajiToExcel()" style="background: #27ae60; border-radius: 8px; padding: 8px 16px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                                <i class="fas fa-file-excel"></i> Export CSV/Excel
                                            </button>
                                             </div>
                                        </div>
                                        <div class="table-container">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px; text-align: center; vertical-align: middle;"><input type="checkbox" id="selectAllReviewGaji" style="transform: scale(1.2); cursor: pointer;"></th>
                                                        <th>Employee</th>
                                                        <th>Division</th>
                                                        <th>Department</th>
                                                        <th>Position</th>
                                                        <th>Scheme</th>
                                                        <th>Gaji Pokok</th>
                                                        <th>Lembur</th>
                                                        <th>Early Arrival</th>
                                                        <th>Rapel</th>
                                                        <th>Lainnya/Bonus</th>
                                                        <th>Total Earnings</th>
                                                        <th>Potongan Absen</th>
                                                        <th>BPJS Kes</th>
                                                        <th>BPJS JHT</th>
                                                        <th>BPJS JP</th>
                                                        <th>PPh 21</th>
                                                        <th>Potongan Lain</th>
                                                        <th>Total Deductions</th>
                                                        <th>Net Salary</th>
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
                                                        <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 13px;">BPJS Scheme</th>
                                                        <th style="padding: 15px; text-align: left; font-weight: 600; font-size: 13px;">PPh 21 Scheme</th>
                                                        <th style="padding: 15px; text-align: center; font-weight: 600; font-size: 13px;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tabelPilihanSkemaKlien">
                                                    <tr>
                                                        <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                                                            No payroll schemes registered yet. Click "Add Scheme" to configure.
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
                                    </div>
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
                        <i class="fas fa-file-invoice-dollar"></i> Payroll Scheme
                    </button>
                    <button id="subTabUmr" class="payroll-subtab-btn" onclick="switchPayrollSubTab('umr')" style="background: transparent; border: 1px solid transparent; border-bottom: none; padding: 12px 24px; font-weight: 600; font-size: 14px; cursor: pointer; color: #475569; margin-bottom: -1px; border-radius: 6px 6px 0 0; z-index: 1; display: flex; align-items: center; gap: 8px; outline: none;">
                        <i class="fas fa-file-upload"></i> UMP/UMK
                    </button>
                </div>

                <!-- Sub-tab 1: Skema Payroll Container -->
                <div id="payrollSkemaContainer">
                    <div class="section-header" style="margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color);">Master Payroll Scheme</h3>
                        <button class="btn-add" onclick="bukaModalSkema('tambah')">
                            <i class="fas fa-plus"></i> Add Scheme
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
                                
                                <!-- Left Side: Search -->
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <!-- State Tracker for UMR type -->
                                    <input type="hidden" id="selectUmrType" value="UMP">
                                    
                                    <!-- Search Input Bar -->
                                    <div class="search-box" style="width: 240px; margin-bottom: 0;">
                                        <i class="fas fa-search"></i>
                                        <input type="text" id="searchUmr" placeholder="Search Province..." onkeyup="filterUmrTable()">
                                    </div>
                                </div>

                                <!-- Right Side: Buttons -->
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <!-- Download Button (Orange) -->
                                    <button class="btn-add" onclick="downloadTemplateUmr()" style="background: #f39c12; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-weight: 500; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                    <!-- Upload Button (Blue) -->
                                    <button class="btn-add" onclick="bukaModalUploadUmr()" style="background: #0d6efd; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-weight: 500; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-upload"></i> Upload
                                    </button>
                                </div>
                            </div>

                            <!-- UMR Table -->
                            <div class="table-container" style="overflow-x: auto; background: white; border: 1px solid #ddd;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th id="colUmrCode" style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">Province Code</th>
                                            <th id="colUmrName" style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">Province</th>
                                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">Amount</th>
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
                                    Showing 0 - 0 of 0 entries
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
                            <h3 style="margin-bottom: 10px; color: #2c3e50; font-size: 22px;">Agreed Salary Input (Nominal)</h3>
                            <p style="color: #6c757d; margin-bottom: 30px; font-size: 14px;">Enter the agreed salary amount for simulation purposes.</p>
                            
                            <div style="max-width: 400px; margin: 0 auto; text-align: left;">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block; color: #4a5568;">Agreed Amount (IDR)</label>
                                <input type="text" id="inputUmrNominal" placeholder="Example: 5000000" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 14px 16px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 16px; margin-bottom: 24px; transition: border-color 0.3s;" onfocus="this.style.borderColor='#0d6efd'" onblur="this.style.borderColor='#ddd'">
                                
                                <button class="btn-save" onclick="simpanNominalManual()" style="width: 100%; padding: 14px 15px; background: #0d6efd; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 10px; transition: all 0.3s; box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);">
                                    <i class="fas fa-save"></i> Save Amount
                                </button>

                                <div id="displayNominalTersimpan" style="margin-top: 25px; padding: 15px; border-radius: 8px; background: #e8fdf0; border: 1px solid #d4edda; text-align: center; display: none;">
                                    <span style="font-size: 14px; color: #155724; font-weight: 500;">Currently Active Amount:</span>
                                    <h4 id="valNominalTersimpan" style="font-size: 22px; color: #2ecc71; margin: 5px 0 0 0; font-weight: 700;">-</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?= view('partials/_view_pajak') ?>
            <?= view('partials/_view_schedule') ?>
            <?= view('partials/_view_skema_shift') ?>

            <!-- Section: Simulasi Gaji -->
            <div id="viewSimulasi" class="view-section">
                <div class="content-card" style="max-width: 600px; margin: 0 auto;">
                    <div class="section-header" style="justify-content: center; flex-direction: column; text-align: center;">
                        <div style="background: rgba(52, 152, 219, 0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                            <i class="fas fa-calculator" style="font-size: 32px; color: var(--info);"></i>
                        </div>
                        <h2 style="font-size: 24px; font-weight: 700; color: #2c3e50; margin-bottom: 10px;">Regional Minimum Wage (UMP/UMK)</h2>
                        <p style="color: #666; font-size: 14px;">Calculate Take Home Pay estimation based on regional minimum wage (UMP/UMK)</p>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: 600; margin-bottom: 8px; display: block;">Region Type</label>
                            <select id="simulasiType" onchange="loadSimulasiRegions()" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; font-family: inherit;">
                                <option value="UMP">Province (UMP)</option>
                                <option value="UMK">City/Regency (UMK)</option>
                                <option value="NOMINAL">Nominal (Manual Input)</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: 600; margin-bottom: 8px; display: block;">Select Region</label>
                            <select id="simulasiRegion" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; font-family: inherit;">
                                <option value="">-- Select Province --</option>
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
                            <i class="fas fa-search-dollar" style="margin-right: 8px;"></i> Check Estimated Salary
                        </button>

                        <div id="simulasiResult" style="display: none; margin-top: 30px; padding: 25px; background: #f8f9fa; border-radius: 16px; border: 1px solid #eee; animation: fadeIn 0.5s;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                <span style="color: #666; font-size: 14px;">Basic Salary:</span>
                                <span id="simBasic" style="font-weight: 600; color: #2c3e50;">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                <span style="color: #666; font-size: 14px;">Fixed Allowance (10%):</span>
                                <span id="simAllowance" style="font-weight: 600; color: #2c3e50;">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding-top: 20px; border-top: 2px dashed #ddd; margin-top: 20px;">
                                <span style="font-weight: 700; color: #2c3e50;">Total Estimated THP:</span>
                                <span id="simTotal" style="font-weight: 800; color: #27ae60; font-size: 22px;">-</span>
                            </div>
                            <p style="font-size: 11px; color: #999; text-align: center; margin-top: 20px; line-height: 1.5;">
                                *This simulation result is an estimate only. The actual value may differ depending on BPJS, tax deductions, and other allowance policies.
                            </p>
                        </div>
                    </div>
                </div>
            </div>



        <!-- Section: Master Skema Tunjangan -->
        <div id="viewMasterKompensasi" class="view-section">
            <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
                <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Master Allowance Scheme</h3>
                        <p style="color: #64748b; font-size: 13px; margin: 0;">Manage income allowances and deductions globally.</p>
                    </div>
                    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <button class="btn-add" onclick="bukaModalSkemaKompensasi('tambah')" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px;">
                            <i class="fas fa-plus"></i> Add Scheme
                        </button>
                    </div>
                </div>

                <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="width: 60px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">No</th>
                                <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Name / Type</th>
                                <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Source & Value</th>
                                <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Period</th>
                                <th style="width: 150px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="compensationSchemesContainer">
                            <!-- Rows will be dynamically rendered here -->
                        </tbody>
                    </table>
            </div>
        </div>
        <!-- Section: Log Aktivitas Removed -->
    </div>

    <?= view('partials/_view_user_management') ?>
    <?= view('partials/_modals') ?>
    <?= view('partials/_scripts') ?>
</body>
</html>

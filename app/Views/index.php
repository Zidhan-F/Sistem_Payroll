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
            <li id="menuStruktur" onclick="switchView('struktur')">
                <i class="fas fa-sitemap"></i>
                <span>Struktur Organisasi</span>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <div class="header-left">
                <i id="toggleSidebar" class="fas fa-bars" style="cursor: pointer;"></i>
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

                    <div id="tabelKlienContainer">
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

                    <!-- Client Org Detail (Hidden by default) -->
                    <div id="clientOrgDetail" style="display: none; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                        <div class="section-header">
                            <button class="btn-cancel" onclick="backToClientList()" style="margin-bottom: 15px; padding: 5px 12px; font-size: 13px;">
                                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Klien
                            </button>
                            <h3 id="clientDetailTitle" style="font-size: 16px; color: var(--primary-dark);">Struktur Klien: [Nama Klien]</h3>
                        </div>
                        
                        <div id="clientOrgContainer" class="org-tree">
                            <!-- Drilled down content will go here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section: Struktur Organisasi (Empty as requested) -->
            <div id="viewStruktur" class="view-section">
                <div class="content-card">
                    <div class="section-header">
                        <h3 style="font-size: 16px; color: var(--secondary-color);">Struktur Organisasi</h3>
                    </div>
                    <div class="empty-state" style="text-align: center; padding: 50px; background: #f9f9f9; border-radius: 12px; border: 2px dashed #ddd;">
                        <i class="fas fa-sitemap" style="font-size: 40px; color: #ccc; margin-bottom: 15px;"></i>
                        <p style="color: #888;">Struktur organisasi sekarang dikelola langsung di dalam masing-masing Klien.</p>
                        <p style="font-size: 12px; color: var(--text-muted);">Silakan buka menu <strong>Klien</strong> dan klik salah satu klien untuk melihat strukturnya.</p>
                    </div>
                </div>
            </div>

                    <div id="orgTreeContainer" class="org-tree">
                        <!-- Hierarki organisasi akan di-render di sini -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Overlay -->
    <div id="overlay" onclick="tutupSemuaModal()"></div>

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
                <button type="submit" id="btnSubmit" class="btn-save">Edit</button>
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
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalOrg()">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>


    <script src="<?= base_url('js/app.js') ?>"></script>
</body>

</html>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <h3>Payroll App</h3>
    </div>
    <ul class="sidebar-menu">
        <li id="menuDashboard" class="active" onclick="switchView('dashboard')">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </li>
        <li id="menuKlien" onclick="switchView('klien')">
            <i class="fas fa-users"></i>
            <span>Client Management</span>
        </li>
        <li id="menuSto" onclick="switchView('sto')">
            <i class="fas fa-sitemap"></i>
            <span>STO (Struktur Organisasi)</span>
        </li>
        <li id="menuManajemenKaryawan" class="has-submenu">
            <div class="menu-item-header" onclick="toggleSubmenu(event, 'submenuKaryawan')">
                <i class="fas fa-user-friends"></i>
                <span>Employee Management</span>
                <i class="fas fa-chevron-down submenu-arrow" style="margin-left: auto; font-size: 11px; transition: transform 0.3s ease;"></i>
            </div>
            <ul id="submenuKaryawan" class="sidebar-submenu" style="display: none;">
                <li id="submenuLokasiKerja" onclick="switchKaryawanSubMenu('lokasi_kerja', event)">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Work Location</span>
                </li>
                <li id="submenuTambahKaryawan" onclick="switchKaryawanSubMenu('tambah_karyawan', event)">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Employee</span>
                </li>
            </ul>
        </li>
        <li id="menuPayroll" onclick="togglePayrollSubmenu()">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Master Payroll Scheme</span>
            <i class="fas fa-chevron-down submenu-arrow"></i>
        </li>
        <ul id="submenuPayroll" class="sidebar-submenu" style="display: none;">
            <li id="submenu_uploadUmr" onclick="switchPayrollSub('uploadUmr')">
                <i class="fas fa-file-upload"></i>
                <span>Upload UMK and UMP</span>
            </li>
            <li id="submenu_kompensasi" onclick="switchPayrollSub('kompensasi')">
                <i class="fas fa-coins"></i>
                <span>Salary Structure</span>
            </li>
            <li id="submenu_setting" onclick="switchPayrollSub('setting')">
                <i class="fas fa-cog"></i>
                <span>Scheme Settings</span>
            </li>
    </ul>
    <li id="menuSchedule" onclick="switchView('schedule')">
        <i class="fas fa-calendar-alt"></i>
        <span>Schedule</span>
    </li>
    </ul>
</div>

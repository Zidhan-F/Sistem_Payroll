<?php
$userRole = session()->get('role') ?? $_COOKIE['user_role'] ?? '';
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand" style="display: flex; align-items: center; gap: 10px; padding: 20px 15px;">
        <img src="<?= base_url('images/logo.png') ?>" alt="BiPayroll Logo" style="width: 35px; height: 35px; object-fit: contain; background: white; border-radius: 50%; padding: 2px;">
        <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 20px; color: var(--secondary-color); letter-spacing: -0.5px;">BiPayroll</h3>
    </div>
    <ul class="sidebar-menu">
        <?php if ($userRole !== 'staff'): ?>
        <li id="menuDashboard" class="active" onclick="switchView('dashboard')">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </li>
        <?php endif; ?>
        <?php if ($userRole === 'payroll'): ?>
        <li id="menuUploadUmrPayroll" onclick="switchPayrollSub('uploadUmr')">
            <i class="fas fa-file-upload"></i>
            <span>Upload UMP and UMK</span>
        </li>
        <?php endif; ?>
        <?php if ($userRole === 'staff'): ?>
        <li id="menuMySalary" onclick="if(window.selectedClientId && typeof selectClient === 'function'){ selectClient(window.selectedClientId, window.selectedClientName, window.selectedClientSektor); } else { showToast('Your salary data is loading. Please wait a moment...', 'info'); }">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>My Payslip</span>
        </li>
        <?php else: ?>
        <li id="menuKlien" onclick="switchView('klien')">
            <i class="fas fa-users"></i>
            <span><?= ($userRole === 'payroll') ? 'Generate salary' : 'Client Management' ?></span>
        </li>
        <?php endif; ?>
        <li id="menuSto" onclick="switchView('sto')" <?= (!in_array($userRole, ['admin', 'hc_ops'])) ? 'style="display: none;"' : '' ?>>
            <i class="fas fa-sitemap"></i>
            <span><?= ($userRole === 'hc_ops') ? 'Master STO' : 'STO (Org Structure)' ?></span>
        </li>
        <li id="menuManajemenKaryawan" class="has-submenu" <?= (!in_array($userRole, ['admin', 'recruiter', 'hc_ops'])) ? 'style="display: none;"' : '' ?>>
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
                <li id="submenuSkemaShift" onclick="switchKaryawanSubMenu('skema_shift', event)">
                    <i class="fas fa-clock"></i>
                    <span>Shift Scheme</span>
                </li>
            </ul>
        </li>
        <li id="menuPayroll" onclick="togglePayrollSubmenu()" <?= (!in_array($userRole, ['admin', 'hc_ops'])) ? 'style="display: none;"' : '' ?>>
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
    <li id="menuSchedule" onclick="switchView('schedule')" <?= (!in_array($userRole, ['admin', 'hc_ops'])) ? 'style="display: none;"' : '' ?>>
        <i class="fas fa-calendar-alt"></i>
        <span><?= ($userRole === 'hc_ops') ? 'Setting Holiday Calendar' : 'Schedule' ?></span>
    </li>
    </ul>
</div>

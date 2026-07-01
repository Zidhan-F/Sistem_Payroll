// Fetch Interceptor to inject logged-in user header
const originalFetch = window.fetch;
window.fetch = async function(...args) {
    let [resource, config] = args;
    if (!config) {
        config = {};
    }
    if (!config.headers) {
        config.headers = {};
    }
    const userJson = localStorage.getItem('user');
    if (userJson) {
        try {
            const user = JSON.parse(userJson);
            if (user && user.username) {
                if (config.headers instanceof Headers) {
                    if (!config.headers.has('X-User-Action')) {
                        config.headers.set('X-User-Action', user.username);
                    }
                } else {
                    if (!config.headers['X-User-Action']) {
                        config.headers['X-User-Action'] = user.username;
                    }
                }
            }
        } catch (e) {
            console.error('Error parsing user from localStorage:', e);
        }
    }
    args[1] = config;
    return originalFetch.apply(this, args);
};

// Cek Login
const currentUser = JSON.parse(localStorage.getItem('user'));
if (!currentUser) {
    window.location.href = BASE_URL + 'index.php/login';
}

// API URL is now declared globally in _scripts.php

let umrAllData = [];
let simulasiAllData = [];

// Update Header Nama & Role Badge
if (currentUser && document.getElementById('headerUserName')) {
    document.getElementById('headerUserName').innerText = currentUser.full_name || currentUser.username;
}

// ===== RBAC - Role Based Access Control =====
const ROLE_PERMISSIONS = {
    admin: ['*'], // Akses semua
    payroll: ['dashboard', 'klien', 'payroll', 'pajak', 'masterKompensasi', 'clientWorkspace'],
    business_development: ['dashboard', 'klien', 'sto', 'clientWorkspace', 'payroll', 'masterKompensasi', 'pajak'],
    recruiter: ['dashboard', 'klien', 'manajemenKaryawan', 'clientWorkspace'],
    client_superior: ['dashboard', 'klien', 'clientWorkspace'],
    hc_ops: ['dashboard', 'klien', 'schedule', 'manajemenKaryawan', 'globalLokasiKerja', 'skemaShift', 'clientWorkspace'],
    staff: ['dashboard', 'klien', 'clientWorkspace']
};

const ROLE_LABELS = {
    admin: 'Admin',
    payroll: 'Payroll',
    business_development: 'Business Dev',
    recruiter: 'Recruiter',
    client_superior: 'Client / Superior',
    hc_ops: 'HC Ops',
    staff: 'Staff'
};

const ROLE_COLORS = {
    admin: '#8b5cf6',
    payroll: '#06b6d4',
    business_development: '#f59e0b',
    recruiter: '#10b981',
    client_superior: '#ef4444',
    hc_ops: '#3b82f6',
    staff: '#6b7280'
};

function getCurrentRole() {
    return (currentUser && currentUser.role) ? currentUser.role : 'admin';
}

function hasPermission(view) {
    const role = getCurrentRole();
    const perms = ROLE_PERMISSIONS[role] || [];
    if (perms.includes('*')) return true;
    return perms.includes(view);
}

function applyWorkspaceTabRestrictions() {
    const role = getCurrentRole();
    const tabs = document.querySelectorAll('.ws-tab');
    if (!tabs.length) return;

    const allowedTabs = {
        admin: ['karyawan', 'struktur', 'kompensasi', 'pkwt', 'proses'],
        payroll: ['proses'],
        business_development: ['struktur', 'kompensasi'],
        recruiter: ['karyawan', 'pkwt'],
        client_superior: ['proses'],
        hc_ops: ['proses'],
        staff: ['proses']
    };

    const allowed = allowedTabs[role] || [];
    tabs.forEach(tabBtn => {
        const wtab = tabBtn.getAttribute('data-wtab');
        if (role === 'admin' || allowed.includes(wtab)) {
            tabBtn.style.display = '';
        } else {
            tabBtn.style.display = 'none';
        }
    });

    // Handle sub-tabs in 'proses' tab
    const subTabProcessing = document.getElementById('subTabSalaryProcessing');
    const subTabResults = document.getElementById('subTabCalculationResults');
    
    const canProcess = (role === 'admin' || role === 'payroll' || role === 'hc_ops');
    const canViewResults = (role === 'admin' || role === 'payroll' || role === 'client_superior' || role === 'staff' || role === 'hc_ops');

    if (subTabProcessing) subTabProcessing.style.display = canProcess ? '' : 'none';
    if (subTabResults) subTabResults.style.display = canViewResults ? '' : 'none';
}

function applyRoleRestrictions() {
    const role = getCurrentRole();
    const perms = ROLE_PERMISSIONS[role] || [];
    const isAdmin = perms.includes('*');

    // Set body class untuk styling CSS berbasis peran
    document.body.className = '';
    document.body.classList.add('role-' + role);

    // Map sidebar menu IDs ke view names
    const menuMapping = {
        'menuDashboard': 'dashboard',
        'menuKlien': 'klien',
        'menuSto': 'sto',
        'menuManajemenKaryawan': 'manajemenKaryawan',
        'menuPayroll': 'payroll',
        'menuSchedule': 'schedule',
        'menuUserManagement': 'userManagement'
    };

    Object.entries(menuMapping).forEach(([menuId, view]) => {
        const el = document.getElementById(menuId);
        if (!el) return;

        if (menuId === 'menuUserManagement') {
            // User Management hanya untuk admin
            el.style.display = isAdmin ? '' : 'none';
        } else if (menuId === 'menuManajemenKaryawan') {
            // Cek akses ke submenu karyawan
            const hasAny = isAdmin || 
                perms.includes('manajemenKaryawan') || 
                perms.includes('globalLokasiKerja') || 
                perms.includes('skemaShift');
            el.style.display = hasAny ? '' : 'none';
        } else if (menuId === 'menuPayroll') {
            const hasAny = isAdmin || 
                perms.includes('payroll') || 
                perms.includes('pajak') || 
                perms.includes('masterKompensasi');
            el.style.display = hasAny ? '' : 'none';
        } else {
            el.style.display = (isAdmin || perms.includes(view)) ? '' : 'none';
        }
    });

    // Submenu Karyawan detail restrictions
    const subLokasi = document.getElementById('submenuLokasiKerja');
    const subTambah = document.getElementById('submenuTambahKaryawan');
    const subShift = document.getElementById('submenuSkemaShift');

    if (subLokasi) subLokasi.style.display = (isAdmin || role === 'hc_ops') ? '' : 'none';
    if (subTambah) subTambah.style.display = (isAdmin || role === 'recruiter') ? '' : 'none';
    if (subShift) subShift.style.display = (isAdmin || role === 'hc_ops') ? '' : 'none';

    // Submenu Payroll detail restrictions
    const subUploadUmr = document.getElementById('submenu_uploadUmr');
    const subKompensasi = document.getElementById('submenu_kompensasi');
    const subSetting = document.getElementById('submenu_setting');
    const subPajak = document.getElementById('submenu_pajak');

    if (subUploadUmr) subUploadUmr.style.display = (isAdmin || role === 'payroll') ? '' : 'none';
    if (subKompensasi) subKompensasi.style.display = (isAdmin || role === 'business_development') ? '' : 'none';
    if (subSetting) subSetting.style.display = (isAdmin || role === 'business_development') ? '' : 'none';
    if (subPajak) subPajak.style.display = (isAdmin || role === 'business_development') ? '' : 'none';

    // Tampilkan role badge di header
    const headerRoleBadge = document.getElementById('headerRoleBadge');
    if (headerRoleBadge) {
        const color = ROLE_COLORS[role] || '#6b7280';
        headerRoleBadge.textContent = ROLE_LABELS[role] || role;
        headerRoleBadge.style.background = color + '22';
        headerRoleBadge.style.color = color;
        headerRoleBadge.style.display = 'inline-block';
    }

    // Quick Actions Panel Detail Restrictions
    const qaDashboard = document.getElementById('qaDashboard');
    const qaAddClient = document.getElementById('qaAddClient');
    const qaAddEmployee = document.getElementById('qaAddEmployee');
    const qaAddStoGlobal = document.getElementById('qaAddStoGlobal');
    const qaSalaryStructure = document.getElementById('qaSalaryStructure');
    const qaBpjsTaxScheme = document.getElementById('qaBpjsTaxScheme');
    const qaWorkLocation = document.getElementById('qaWorkLocation');
    const qaUploadUmkUmp = document.getElementById('qaUploadUmkUmp');
    const qaSchemeSettings = document.getElementById('qaSchemeSettings');

    if (qaDashboard) qaDashboard.style.display = '';
    if (qaAddClient) qaAddClient.style.display = (isAdmin || role === 'business_development') ? '' : 'none';
    if (qaAddEmployee) qaAddEmployee.style.display = (isAdmin || role === 'recruiter') ? '' : 'none';
    if (qaAddStoGlobal) qaAddStoGlobal.style.display = (isAdmin || role === 'business_development') ? '' : 'none';
    if (qaSalaryStructure) qaSalaryStructure.style.display = (isAdmin || role === 'business_development') ? '' : 'none';
    if (qaBpjsTaxScheme) qaBpjsTaxScheme.style.display = (isAdmin || role === 'business_development') ? '' : 'none';
    if (qaWorkLocation) qaWorkLocation.style.display = (isAdmin || role === 'hc_ops') ? '' : 'none';
    if (qaUploadUmkUmp) qaUploadUmkUmp.style.display = (isAdmin || role === 'payroll') ? '' : 'none';
    if (qaSchemeSettings) qaSchemeSettings.style.display = (isAdmin || role === 'business_development') ? '' : 'none';

    // Work Location Recruiter Restrictions
    const btnTambahLokasi = document.getElementById('btnTambahLokasiKerjaGlobal');
    const thActionLokasi = document.getElementById('thActionLokasiKerjaGlobal');
    if (role === 'recruiter') {
        if (btnTambahLokasi) btnTambahLokasi.style.display = 'none';
        if (thActionLokasi) thActionLokasi.style.display = 'none';
    } else {
        if (btnTambahLokasi) btnTambahLokasi.style.display = '';
        if (thActionLokasi) thActionLokasi.style.display = '';
    }

    // Terapkan restriksi ke client workspace tabs
    applyWorkspaceTabRestrictions();
}

// Terapkan restriksi role saat page load
document.addEventListener('DOMContentLoaded', () => {
    applyRoleRestrictions();
});

// ===== Global State =====
if (typeof window.clients === 'undefined') window.clients = [];
if (typeof window.orgData === 'undefined') window.orgData = [];
if (typeof window.selectedClientId === 'undefined') window.selectedClientId = null;
if (typeof window.payrollSchemes === 'undefined') window.payrollSchemes = [];
if (typeof window.taxSchemes === 'undefined') window.taxSchemes = [];
if (typeof window.clientConfigs === 'undefined') window.clientConfigs = [];
if (typeof window.pkwtData === 'undefined') window.pkwtData = [];
if (typeof window.currentPeriodId === 'undefined') window.currentPeriodId = null;


function formatRupiah(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
}

function formatTimeHM(timeStr) {
    if (!timeStr || timeStr === '-') return '-';
    const parts = timeStr.split(':');
    if (parts.length >= 2) {
        const hour = parts[0].padStart(2, '0');
        const minute = parts[1].padStart(2, '0');
        return `${hour}:${minute}`;
    }
    return timeStr;
}

function formatRupiahInput(element) {
    let value = element.value.replace(/[^,\d]/g, '').toString();
    let split = value.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    element.value = rupiah ? rupiah : '';
}

function parseFormattedNumber(val) {
    if (!val) return 0;
    // Strip currency symbols/text (anything other than digits, dots, commas, minus)
    let clean = String(val).replace(/[^0-9.,\-]/g, '');
    // Remove dot as thousands separator
    clean = clean.replace(/\./g, '');
    // Replace comma as decimal separator with dot
    clean = clean.replace(/,/g, '.');
    return parseFloat(clean) || 0;
}

function handleKomponenKompensasiNilaiInput(element) {
    const isPersentase = document.getElementById('komponenKompensasiIsPersentase')?.value === '1';
    if (!isPersentase) {
        formatRupiahInput(element);
    }
}

function handleKomponenNilaiInput(element) {
    const isPersentase = document.getElementById('komponenIsPersentase')?.value === 'true';
    if (!isPersentase) {
        formatRupiahInput(element);
    }
}


function showToast(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-times-circle' : 'fa-info-circle');
    toast.innerHTML = `<i class="fas ${icon}"></i><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function showConfirm(message, title = 'Confirmation', okText = 'Yes, Delete', cancelText = 'Cancel', variant = 'danger') {
    return new Promise((resolve) => {
        const overlay = document.getElementById('confirmOverlay');
        const dialog = document.getElementById('confirmDialog');
        document.getElementById('confirmTitle').innerText = title;
        document.getElementById('confirmMessage').innerText = message;
        
        const okBtn = document.getElementById('confirmOk');
        const cancelBtn = document.getElementById('confirmCancel');
        const iconDiv = document.querySelector('.confirm-icon');
        const iconEl = iconDiv ? iconDiv.querySelector('i') : null;
        
        if (okBtn) okBtn.innerText = okText;
        if (cancelBtn) cancelBtn.innerText = cancelText;
        
        if (okBtn) {
            okBtn.classList.remove('btn-primary', 'btn-success', 'btn-danger');
            if (variant === 'primary') {
                okBtn.classList.add('btn-primary');
            } else if (variant === 'success') {
                okBtn.classList.add('btn-success');
            } else {
                okBtn.classList.add('btn-danger');
            }
        }
        
        if (iconDiv && iconEl) {
            iconDiv.className = 'confirm-icon'; // Reset
            iconEl.className = 'fas'; // Reset
            
            if (variant === 'primary') {
                iconDiv.style.background = 'linear-gradient(135deg, #dbeafe, #bfdbfe)';
                iconDiv.style.color = '#2563eb';
                iconDiv.style.boxShadow = '0 4px 16px rgba(37, 99, 235, 0.2)';
                iconEl.classList.add('fa-question-circle');
            } else if (variant === 'success') {
                iconDiv.style.background = 'linear-gradient(135deg, #d1fae5, #a7f3d0)';
                iconDiv.style.color = '#059669';
                iconDiv.style.boxShadow = '0 4px 16px rgba(5, 150, 105, 0.2)';
                iconEl.classList.add('fa-check-circle');
            } else {
                iconDiv.style.background = '';
                iconDiv.style.color = '';
                iconDiv.style.boxShadow = '';
                iconDiv.classList.add('confirm-icon-danger');
                iconEl.classList.add('fa-exclamation-triangle');
            }
        }

        overlay.classList.add('show');
        dialog.classList.add('show');
        const handleCancel = () => {
            overlay.classList.remove('show');
            dialog.classList.remove('show');
            resolve(false);
            cleanup();
        };
        const handleOk = () => {
            overlay.classList.remove('show');
            dialog.classList.remove('show');
            resolve(true);
            cleanup();
        };
        const cleanup = () => {
            document.getElementById('confirmCancel').removeEventListener('click', handleCancel);
            document.getElementById('confirmOk').removeEventListener('click', handleOk);
        };
        document.getElementById('confirmCancel').addEventListener('click', handleCancel);
        document.getElementById('confirmOk').addEventListener('click', handleOk);
    });
}

function logout() {
    localStorage.removeItem('user');
    window.location.href = BASE_URL + 'index.php/login';
}

function formatTimeAgo(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffSec = Math.floor(diffMs / 1000);
    const diffMin = Math.floor(diffSec / 60);
    const diffHr = Math.floor(diffMin / 60);
    const diffDays = Math.floor(diffHr / 24);

    if (diffSec < 60) return 'just now';
    if (diffMin < 60) return `${diffMin} minutes ago`;
    if (diffHr < 24) return `${diffHr} hours ago`;
    return `${diffDays} days ago`;
}

async function updateDashboardStats() {
    const statKlien = document.getElementById('statTotalKlien');
    const statKaryawan = document.getElementById('statTotalKaryawan');
    const statDivisi = document.getElementById('statTotalDivisi');

    // Show skeleton shimmer animation immediately
    if (statKlien) statKlien.innerHTML = '<span class="skeleton skeleton-text"></span>';
    if (statKaryawan) statKaryawan.innerHTML = '<span class="skeleton skeleton-text"></span>';
    if (statDivisi) statDivisi.innerHTML = '<span class="skeleton skeleton-text"></span>';

    try {
        // Fetch in parallel using Promise.all
        const [rc, re, ro] = await Promise.all([
            fetch(`${API_URL}/clients`),
            fetch(`${API_URL}/employees`),
            fetch(`${API_URL}/org`)
        ]);

        const [cd, ed, od] = await Promise.all([
            rc.json(),
            re.json(),
            ro.json()
        ]);

        if (statKlien) statKlien.innerText = cd.length || 0;
        if (statKaryawan) statKaryawan.innerText = ed.length || 0;
        if (statDivisi) statDivisi.innerText = od.length || 0;

    } catch (err) {
        console.error('Error updating dashboard stats:', err);
        if (statKlien) statKlien.innerText = '0';
        if (statKaryawan) statKaryawan.innerText = '0';
        if (statDivisi) statDivisi.innerText = '0';
    }
}

// ===== Navigation =====
function switchPayrollSubTab(tab) {
    const subTabSkema = document.getElementById('subTabSkema');
    const subTabUmr = document.getElementById('subTabUmr');
    const skemaCont = document.getElementById('payrollSkemaContainer');
    const umrCont = document.getElementById('payrollUmrContainer');

    if (tab === 'skema') {
        if (subTabSkema) {
            subTabSkema.className = 'payroll-subtab-btn active';
            subTabSkema.style.background = 'white';
            subTabSkema.style.border = '1px solid #e2e8f0';
            subTabSkema.style.borderBottom = '1px solid white';
            subTabSkema.style.color = '#0d6efd';
            subTabSkema.style.zIndex = '2';
        }
        if (subTabUmr) {
            subTabUmr.className = 'payroll-subtab-btn';
            subTabUmr.style.background = 'transparent';
            subTabUmr.style.border = '1px solid transparent';
            subTabUmr.style.borderBottom = 'none';
            subTabUmr.style.color = '#475569';
            subTabUmr.style.zIndex = '1';
        }
        if (skemaCont) skemaCont.style.display = 'block';
        if (umrCont) umrCont.style.display = 'none';
        renderPayrollSchemes();
    } else if (tab === 'umr') {
        if (subTabUmr) {
            subTabUmr.className = 'payroll-subtab-btn active';
            subTabUmr.style.background = 'white';
            subTabUmr.style.border = '1px solid #e2e8f0';
            subTabUmr.style.borderBottom = '1px solid white';
            subTabUmr.style.color = '#0d6efd';
            subTabUmr.style.zIndex = '2';
        }
        if (subTabSkema) {
            subTabSkema.className = 'payroll-subtab-btn';
            subTabSkema.style.background = 'transparent';
            subTabSkema.style.border = '1px solid transparent';
            subTabSkema.style.borderBottom = 'none';
            subTabSkema.style.color = '#475569';
            subTabSkema.style.zIndex = '1';
        }
        if (skemaCont) skemaCont.style.display = 'none';
        if (umrCont) umrCont.style.display = 'block';
        renderUmrTable();
    }
}

let currentPayrollSub = 'setting'; // Default payroll sub menu

function togglePayrollSubmenu(openOnly = false) {
    const submenu = document.getElementById('submenuPayroll');
    const arrow = document.querySelector('#menuPayroll .submenu-arrow');
    if (!submenu) return;

    const isHidden = submenu.style.display === 'none';
    if (openOnly) {
        submenu.style.display = 'block';
        if (arrow) arrow.style.transform = 'rotate(180deg)';
    } else {
        if (isHidden) {
            submenu.style.display = 'block';
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            submenu.style.display = 'none';
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }
}

function switchPayrollSub(sub) {
    currentPayrollSub = sub;
    localStorage.setItem('activePayrollSub', sub);

    // Toggle active classes on submenu elements
    document.querySelectorAll('.sidebar-submenu li').forEach(el => el.classList.remove('active'));

    // Make sure parent menu is active and open
    const parentMenu = document.getElementById('menuPayroll');
    if (parentMenu) parentMenu.classList.add('active');
    togglePayrollSubmenu(true);

    const subItem = document.getElementById('submenu_' + sub);
    if (subItem) subItem.classList.add('active');

    if (sub === 'uploadUmr') {
        switchView('payroll');
        switchPayrollSubTab('umr');
    } else if (sub === 'kompensasi') {
        switchView('masterKompensasi');
    } else if (sub === 'setting') {
        switchView('payroll');
        switchPayrollSubTab('skema');
    } else if (sub === 'pajak') {
        switchView('pajak');
    } else if (sub === 'schedule') {
        switchView('schedule');
    }
}

function switchScheduleSubTab(tab) {
    localStorage.setItem('activeScheduleTab', tab);
    // Hide all sub-panels under schedule
    document.querySelectorAll('.schedule-subpanel').forEach(p => p.style.display = 'none');
    
    // Deactivate all sub-tab buttons
    document.querySelectorAll('.sub-tab-btn').forEach(btn => {
        if (btn.id.startsWith('subTabSchedule')) {
            btn.classList.remove('active');
            btn.style.color = '#64748b';
            btn.style.borderBottomColor = 'transparent';
        }
    });
    
    // Show active panel
    const tabPascal = tab.charAt(0).toUpperCase() + tab.slice(1);
    const activePanelId = 'panelSchedule' + tabPascal;
    const activePanel = document.getElementById(activePanelId);
    if (activePanel) activePanel.style.display = 'block';
    
    // Activate clicked button
    const activeBtnId = 'subTabSchedule' + tabPascal;
    const activeBtn = document.getElementById(activeBtnId);
    if (activeBtn) {
        activeBtn.classList.add('active');
        activeBtn.style.color = 'var(--primary-color)';
        activeBtn.style.borderBottomColor = 'var(--primary-color)';
    }
    
    // Auto load data for active tab
    if (tab === 'master') {
        if (typeof renderMasterSchedule === 'function') renderMasterSchedule();
    } else if (tab === 'holiday') {
        if (typeof loadHolidays === 'function') loadHolidays();
    } else if (tab === 'attendance') {
        if (typeof loadAttendanceClients === 'function') loadAttendanceClients();
    } else if (tab === 'overtime') {
        if (typeof loadOvertimeClients === 'function') loadOvertimeClients();
    } else if (tab === 'earlyArrival') {
        if (typeof loadEarlyArrivalClients === 'function') loadEarlyArrivalClients();
    }
}

function switchView(view) {
    if (view === 'holiday') {
        switchView('schedule');
        switchScheduleSubTab('holiday');
        return;
    }
    if (view === 'attendance') {
        switchView('schedule');
        switchScheduleSubTab('attendance');
        return;
    }
    if (view === 'overtime') {
        switchView('schedule');
        switchScheduleSubTab('overtime');
        return;
    }

    // RBAC: Cek permission sebelum switch view
    if (!hasPermission(view)) {
        showToast('Anda tidak memiliki akses ke halaman ini', 'error');
        return;
    }

    localStorage.setItem('activeView', view);

    // Auto-close any open modals when switching views, but keep sidebar open
    tutupSemuaModal(true);

    const clientScopedViews = ['karyawan', 'struktur', 'setup', 'pkwt', 'proses'];
    if (clientScopedViews.includes(view.toLowerCase())) {
        if (window.selectedClientId) {
            switchView('clientWorkspace');
            
            // Pilih tab yang diizinkan untuk role saat ini
            let targetTab = view.toLowerCase();
            const role = getCurrentRole();
            const allowedTabs = {
                admin: ['karyawan', 'struktur', 'kompensasi', 'pkwt', 'proses'],
                payroll: ['proses'],
                business_development: ['struktur', 'kompensasi'],
                recruiter: ['karyawan', 'pkwt'],
                client_superior: ['proses'],
                hc_ops: ['proses'],
                staff: ['proses']
            };
            const allowed = allowedTabs[role] || [];
            if (role !== 'admin' && !allowed.includes(targetTab)) {
                targetTab = allowed[0] || 'proses';
            }
            switchWorkspaceTab(targetTab);
            return;
        } else {
            switchView('klien');
            showToast('Please select a client first!', 'info');
            return;
        }
    }

    document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.sidebar-menu li').forEach(l => l.classList.remove('active'));
    document.querySelectorAll('.sidebar-submenu li').forEach(l => l.classList.remove('active'));

    if (view !== 'clientWorkspace') {
        window.selectedClientId = null;
    }

    const sectionId = 'view' + view.charAt(0).toUpperCase() + view.slice(1);
    if (document.getElementById(sectionId)) {
        document.getElementById(sectionId).classList.add('active');
    }

    // Submenu collapse/expand states
    const submenuKaryawan = document.getElementById('submenuKaryawan');
    const arrowKaryawan = document.querySelector('#menuManajemenKaryawan .submenu-arrow');
    const submenuPayroll = document.getElementById('submenuPayroll');
    const arrowPayroll = document.querySelector('#menuPayroll .submenu-arrow');

    // Default: collapse submenus
    if (submenuKaryawan) submenuKaryawan.style.display = 'none';
    if (arrowKaryawan) arrowKaryawan.style.transform = 'rotate(0deg)';
    if (submenuPayroll) submenuPayroll.style.display = 'none';
    if (arrowPayroll) arrowPayroll.style.transform = 'rotate(0deg)';

    // Highlight menu based on view
    if (view === 'dashboard') {
        const item = document.getElementById('menuDashboard');
        if (item) item.classList.add('active');
    } else if (view === 'klien') {
        const item = document.getElementById('menuKlien');
        if (item) item.classList.add('active');
    } else if (view === 'sto') {
        const item = document.getElementById('menuSto');
        if (item) item.classList.add('active');
    } else if (view === 'schedule') {
        const item = document.getElementById('menuSchedule');
        if (item) item.classList.add('active');
    } else if (view === 'userManagement') {
        const item = document.getElementById('menuUserManagement');
        if (item) item.classList.add('active');
    } else if (view === 'globalLokasiKerja' || view === 'manajemenKaryawan' || view === 'skemaShift') {
        const parent = document.getElementById('menuManajemenKaryawan');
        if (parent) parent.classList.add('active');
        if (submenuKaryawan) submenuKaryawan.style.display = 'block';
        if (arrowKaryawan) arrowKaryawan.style.transform = 'rotate(180deg)';

        let subItemId = '';
        if (view === 'globalLokasiKerja') subItemId = 'submenuLokasiKerja';
        else if (view === 'manajemenKaryawan') subItemId = 'submenuTambahKaryawan';
        else if (view === 'skemaShift') subItemId = 'submenuSkemaShift';

        const subItem = document.getElementById(subItemId);
        if (subItem) subItem.classList.add('active');
    } else if (view === 'payroll' || view === 'masterKompensasi' || view === 'pajak') {
        const parent = document.getElementById('menuPayroll');
        if (parent) parent.classList.add('active');
        if (submenuPayroll) submenuPayroll.style.display = 'block';
        if (arrowPayroll) arrowPayroll.style.transform = 'rotate(180deg)';

        let subItemId = 'submenu_setting'; // Default
        if (view === 'payroll') {
            const sub = localStorage.getItem('activePayrollSub') || 'setting';
            subItemId = 'submenu_' + sub;
            currentPayrollSub = sub; // sync global state
        } else if (view === 'masterKompensasi') {
            subItemId = 'submenu_kompensasi';
            currentPayrollSub = 'kompensasi'; // sync global state
        } else if (view === 'pajak') {
            subItemId = 'submenu_pajak';
            currentPayrollSub = 'pajak'; // sync global state
        }

        const subItem = document.getElementById(subItemId);
        if (subItem) subItem.classList.add('active');
    }

    const titles = {
        dashboard: 'Dashboard',
        klien: 'Client Management',
        sto: 'STO (Struktur Organisasi)',
        manajemenKaryawan: 'Employee Management',
        globalLokasiKerja: 'Employee Management',
        clientWorkspace: 'Client Workspace',
        payroll: 'Master Payroll Scheme',
        pajak: 'Master Payroll Scheme',
        masterKompensasi: 'Master Payroll Scheme',
        schedule: 'Schedule',
        skemaShift: 'Employee Management',
        userManagement: 'User Management'
    };
    const titleEl = document.getElementById('viewTitle');
    if (titleEl) {
        titleEl.innerText = titles[view] || 'Employee Management';
    }

    // Auto load data based on view
    if (view === 'dashboard') updateDashboardStats();
    if (view === 'klien') renderTable();
    if (view === 'sto') { if (typeof switchStoTab === 'function') switchStoTab('divisi'); }
    if (view === 'manajemenKaryawan') renderManajemenKaryawan();
    if (view === 'globalLokasiKerja') { if (typeof loadGlobalWorkLocations === 'function') loadGlobalWorkLocations(); }
    if (view === 'skemaShift') { if (typeof loadShiftSchemes === 'function') loadShiftSchemes(); }
    if (view === 'payroll') {
        const sub = localStorage.getItem('activePayrollSub') || 'setting';
        if (sub === 'uploadUmr') {
            switchPayrollSubTab('umr');
        } else {
            switchPayrollSubTab('skema');
        }
    }
    if (view === 'pajak') renderTaxSchemes();
    if (view === 'masterKompensasi') renderMasterKompensasi();
    if (view === 'userManagement') { if (typeof loadUsers === 'function') loadUsers(); }
    
    if (view === 'schedule') {
        // Initialize default filters to current month/year if not initialized
        const d = new Date();
        const currentMonth = d.getMonth() + 1;
        const currentYear = d.getFullYear();

        const otMonth = document.getElementById('overtimeMonthSelect');
        const otYear = document.getElementById('overtimeYearSelect');
        if (otMonth && !otMonth.dataset.initialized) {
            otMonth.value = currentMonth;
            otMonth.dataset.initialized = 'true';
        }
        if (otYear && !otYear.dataset.initialized) {
            otYear.value = currentYear;
            otYear.dataset.initialized = 'true';
        }

        const attMonth = document.getElementById('attendanceMonthSelect');
        const attYear = document.getElementById('attendanceYearSelect');
        if (attMonth && !attMonth.dataset.initialized) {
            attMonth.value = currentMonth;
            attMonth.dataset.initialized = 'true';
        }
        if (attYear && !attYear.dataset.initialized) {
            attYear.value = currentYear;
            attYear.dataset.initialized = 'true';
        }

        // default to holiday calendar sub tab if none is active
        const activeSubTab = document.querySelector('.sub-tab-btn.active[id^="subTabSchedule"]');
        if (!activeSubTab) {
            switchScheduleSubTab('holiday');
        } else {
            // refresh active tab
            const tabId = activeSubTab.id;
            if (tabId === 'subTabScheduleHoliday') switchScheduleSubTab('holiday');
            else if (tabId === 'subTabScheduleAttendance') switchScheduleSubTab('attendance');
            else if (tabId === 'subTabScheduleOvertime') switchScheduleSubTab('overtime');
        }
    }

    if (typeof window.updateAiAssistantContext === 'function') {
        window.updateAiAssistantContext();
    }
}


// ===== UTILS & MODAL CLOSING =====
function tutupSemuaModal(keepSidebarOpen = false) {
    const modals = ['modalClient', 'modalSkema', 'modalKomponen', 'modalOrg', 'modalPajak', 'modalSetup', 'modalPKWT', 'modalPeriode', 'modalCutOff', 'modalSlip', 'modalManualUmr', 'modalUploadUmr', 'modalSkemaKompensasi', 'modalKomponenKompensasi', 'modalKaryawan', 'modalLokasiKerja', 'modalDetailSkemaPayroll', 'modalDetailSkemaPajak', 'modalGlobalSto', 'modalBpjs', 'modalPph21', 'modalDetailBpjs', 'modalDetailPph21', 'modalPilihanSkema', 'modalSchemeTemplate', 'modalPilihSkema', 'modalSchedule', 'modalUploadAbsensi', 'attendanceModal', 'overtimeModal', 'modalUserForm', 'modalDeleteUser'];
    modals.forEach(m => { if(document.getElementById(m)) document.getElementById(m).style.display = 'none'; });
    
    // Clean up TomSelect instances from modalPilihanSkema if it was open
    if (typeof window.tutupModalPilihanSkema === 'function') {
        try {
            // Destroy TomSelect instances (excluding bpjs select which is native)
            ['modalPilihanSkemaDivisi', 'modalPilihanSkemaDepartemen', 'modalPilihanSkemaPosisi', 'modalPilihanSkemaPayroll', 'modalPilihanSkemaPajak'].forEach(id => {
                const el = document.getElementById(id);
                if (el && el.tomselect) el.tomselect.destroy();
            });
            const overrideFields = document.getElementById('modalClientBpjsOverrideFields');
            if (overrideFields) overrideFields.style.display = 'none';
            window.modalClientBpjsOriginalValues = null;
            window.editSchemaMappingId = null;
        } catch(e) { /* ignore cleanup errors */ }
    }

    // Hide additional overlays
    const extraOverlays = ['overlayPilihSkema'];
    extraOverlays.forEach(id => { if(document.getElementById(id)) document.getElementById(id).style.display = 'none'; });
    
    const sidebar = document.querySelector('.sidebar');
    const isSidebarActive = sidebar && sidebar.classList.contains('active');

    if (!keepSidebarOpen) {
        if (sidebar) {
            sidebar.classList.remove('active');
        }
        if(document.getElementById('overlay')) document.getElementById('overlay').style.display = 'none';
    } else {
        if (!isSidebarActive) {
            if(document.getElementById('overlay')) document.getElementById('overlay').style.display = 'none';
        }
    }
}

// ===== SIDEBAR TOGGLE =====
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const overlay = document.getElementById('overlay');
    
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('active');
        if (overlay) {
            overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
        }
    } else {
        sidebar.classList.toggle('collapsed');
        if (mainContent) mainContent.classList.toggle('expanded');
    }
}

function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'block';
    }
    const overlay = document.getElementById('overlay');
    if (overlay) {
        overlay.style.display = 'block';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'none';
    }
    const overlay = document.getElementById('overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Expose defined local core functions to window if needed
Object.assign(window, {
    switchView, logout, tutupSemuaModal, toggleSidebar, formatRupiah, formatRupiahInput, parseFormattedNumber, handleKomponenKompensasiNilaiInput, handleKomponenNilaiInput, switchScheduleSubTab, openModal, closeModal, formatTimeHM
});


// ===== DOMContentLoaded: Sidebar Init =====
    // Sidebar toggle button
    const sidebarToggleBtn = document.querySelector('.header-hamburger');
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', toggleSidebar);
    }

// Initialize default view - handled on DOMContentLoaded to support persistence

// MutationObserver to automatically toggle 'modal-open' class (overflow: hidden) on body when overlay is active
document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('overlay');
    if (overlay) {
        const observer = new MutationObserver(() => {
            if (overlay.style.display === 'block') {
                document.body.classList.add('modal-open');
                document.documentElement.classList.add('modal-open');
            } else {
                document.body.classList.remove('modal-open');
                document.documentElement.classList.remove('modal-open');
            }
        });
        observer.observe(overlay, { attributes: true, attributeFilter: ['style'] });
        
        // Initial check
        if (overlay.style.display === 'block') {
            document.body.classList.add('modal-open');
            document.documentElement.classList.add('modal-open');
        }
    }
});

// Quick Actions Handler
function quickAction(type) {
    if (type === 'dashboard') {
        switchView('dashboard');
        if (typeof updateDashboardStats === 'function') {
            updateDashboardStats();
        }
    } else if (type === 'tambah-klien') {
        switchView('klien');
        setTimeout(() => {
            if (typeof window.bukaModal === 'function') {
                window.bukaModal('tambah');
            }
        }, 150);
    } else if (type === 'tambah-karyawan') {
        switchView('manajemenKaryawan');
        setTimeout(() => {
            if (typeof window.bukaModalKaryawanGlobal === 'function') {
                window.bukaModalKaryawanGlobal();
            }
        }, 150);
    } else if (type === 'proses-payroll') {
        switchView('klien');
        showToast('Please select a client first to process payroll', 'info');
    } else if (type === 'pengaturan-skema') {
        switchPayrollSub('pajak');
    } else if (type === 'setting-skema') {
        switchPayrollSub('setting');
    } else if (type === 'lokasi-kerja') {
        if (typeof window.switchKaryawanSubMenu === 'function') {
            window.switchKaryawanSubMenu('lokasi_kerja');
        } else {
            switchView('globalLokasiKerja');
        }
    } else if (type === 'upload-umk-ump') {
        if (typeof window.switchPayrollSub === 'function') {
            window.switchPayrollSub('uploadUmr');
        } else {
            switchView('payroll');
            switchPayrollSubTab('umr');
        }
    } else if (type === 'struktur-gaji') {
        if (typeof window.switchPayrollSub === 'function') {
            window.switchPayrollSub('kompensasi');
        } else {
            switchView('masterKompensasi');
        }
    } else if (type === 'add-sto-global') {
        switchView('sto');
        setTimeout(() => {
            if (typeof window.bukaModalGlobalSto === 'function') {
                window.bukaModalGlobalSto('divisi', 'tambah');
            }
        }, 150);
    }
}
window.quickAction = quickAction;

// BPJS Detailed Calculations Modal
async function bukaDetailBpjsModal(type, id) {
    try {
        let kes_emp = 0, kes_co = 0;
        let jht_emp = 0, jht_co = 0;
        let jp_emp = 0, jp_co = 0;
        let jkk_co = 0, jkm_co = 0;
        let name = '';
        let period = '';

        if (type === 'pkwt') {
            const r = await fetch(`${API}/slip-details/${id}`);
            if (!r.ok) throw new Error('Failed to load slip');
            const data = await r.json();
            const info = data.info;
            name = info.employee_name;
            period = info.period_name || info.periode;
            
            kes_emp = parseFloat(info.bpjs_kes_karyawan) || 0;
            kes_co = parseFloat(info.bpjs_kes_perusahaan) || 0;
            jht_emp = parseFloat(info.bpjs_jht_karyawan) || 0;
            jht_co = parseFloat(info.bpjs_jht_perusahaan) || 0;
            jp_emp = parseFloat(info.bpjs_jp_karyawan) || 0;
            jp_co = parseFloat(info.bpjs_jp_perusahaan) || 0;
            jkk_co = parseFloat(info.bpjs_jkk_perusahaan) || 0;
            jkm_co = parseFloat(info.bpjs_jkm_perusahaan) || 0;
        } else {
            const r = await fetch(`${API}/payroll/slip/${id}`);
            if (!r.ok) throw new Error('Failed to load slip');
            const data = await r.json();
            name = data.employee.nama;
            period = `${data.payroll.bulan}/${data.payroll.tahun}`;
            
            data.details.forEach(d => {
                const comp = d.nama_komponen;
                const val = parseFloat(d.jumlah) || 0;
                if (comp.includes('BPJS Kesehatan') && comp.includes('1%')) kes_emp = val;
                if (comp.includes('BPJS Kesehatan') && (comp.includes('4%') || comp.includes('Beban Perusahaan'))) kes_co = val;
                if (comp.includes('JHT') && comp.includes('2%')) jht_emp = val;
                if (comp.includes('JHT') && (comp.includes('3.7%') || comp.includes('Beban Perusahaan'))) jht_co = val;
                if (comp.includes('JP') && comp.includes('1%')) jp_emp = val;
                if (comp.includes('JP') && (comp.includes('2%') || comp.includes('Beban Perusahaan'))) jp_co = val;
                if (comp.includes('JKK')) jkk_co = val;
                if (comp.includes('JKM')) jkm_co = val;
            });
        }

        // Calculate bases
        const kes_base = kes_emp > 0 ? (kes_emp / 0.01) : (kes_co > 0 ? (kes_co / 0.04) : 0);
        const jht_base = jht_emp > 0 ? (jht_emp / 0.02) : (jht_co > 0 ? (jht_co / 0.037) : 0);
        const jp_base = jp_emp > 0 ? (jp_emp / 0.01) : (jp_co > 0 ? (jp_co / 0.02) : 0);
        const jkk_base = jkk_co > 0 ? (jkk_co / 0.0024) : jht_base;
        const jkm_base = jkm_co > 0 ? (jkm_co / 0.003) : jht_base;

        // Render to modal
        document.getElementById('bpjsModalEmployeeName').innerText = name;
        document.getElementById('bpjsModalPeriod').innerText = period;

        // 1. BPJS Kesehatan
        document.getElementById('bpjsKesBase').innerText = formatRupiah(kes_base);
        document.getElementById('bpjsKesEmp').innerText = formatRupiah(kes_emp);
        document.getElementById('bpjsKesCo').innerText = formatRupiah(kes_co);
        document.getElementById('bpjsKesTotal').innerText = formatRupiah(kes_emp + kes_co);

        // 2. BPJS JHT
        document.getElementById('bpjsJhtBase').innerText = formatRupiah(jht_base);
        document.getElementById('bpjsJhtEmp').innerText = formatRupiah(jht_emp);
        document.getElementById('bpjsJhtCo').innerText = formatRupiah(jht_co);
        document.getElementById('bpjsJhtTotal').innerText = formatRupiah(jht_emp + jht_co);

        // 3. BPJS JP
        document.getElementById('bpjsJpBase').innerText = formatRupiah(jp_base);
        document.getElementById('bpjsJpEmp').innerText = formatRupiah(jp_emp);
        document.getElementById('bpjsJpCo').innerText = formatRupiah(jp_co);
        document.getElementById('bpjsJpTotal').innerText = formatRupiah(jp_emp + jp_co);

        // 4. BPJS JKK
        document.getElementById('bpjsJkkBase').innerText = formatRupiah(jkk_base);
        document.getElementById('bpjsJkkEmp').innerText = formatRupiah(0);
        document.getElementById('bpjsJkkCo').innerText = formatRupiah(jkk_co);
        document.getElementById('bpjsJkkTotal').innerText = formatRupiah(jkk_co);

        // 5. BPJS JKM
        document.getElementById('bpjsJkmBase').innerText = formatRupiah(jkm_base);
        document.getElementById('bpjsJkmEmp').innerText = formatRupiah(0);
        document.getElementById('bpjsJkmCo').innerText = formatRupiah(jkm_co);
        document.getElementById('bpjsJkmTotal').innerText = formatRupiah(jkm_co);

        // Grand Totals
        const grandEmp = kes_emp + jht_emp + jp_emp;
        const grandCo = kes_co + jht_co + jp_co + jkk_co + jkm_co;
        document.getElementById('bpjsGrandEmp').innerText = formatRupiah(grandEmp);
        document.getElementById('bpjsGrandCo').innerText = formatRupiah(grandCo);
        document.getElementById('bpjsGrandTotal').innerText = formatRupiah(grandEmp + grandCo);

        // Open Modal
        document.getElementById('modalDetailBpjs').style.display = 'block';
    } catch (err) {
        console.error('Error opening BPJS detail modal:', err);
        showToast('Gagal memuat rincian perhitungan BPJS', 'error');
    }
}

function tutupDetailBpjsModal() {
    document.getElementById('modalDetailBpjs').style.display = 'none';
}

window.bukaDetailBpjsModal = bukaDetailBpjsModal;
window.tutupDetailBpjsModal = tutupDetailBpjsModal;

// ===== NOTIFICATION SYSTEM JS =====
async function fetchNotifications() {
    const badge = document.getElementById('notificationsBadge');
    const countText = document.getElementById('notificationsCount');
    const list = document.getElementById('notificationsList');
    if (!list) return;

    try {
        const res = await fetch(`${API_URL}/notifications`);
        if (!res.ok) throw new Error('Failed to fetch');
        
        const json = await res.json();
        const data = json.data || [];
        const count = data.length;

        // Update Badge
        if (count > 0) {
            badge.innerText = count;
            badge.style.display = 'block';
            countText.innerText = `${count} Peringatan`;
        } else {
            badge.style.display = 'none';
            countText.innerText = '0 Peringatan';
        }

        // Render List
        if (count === 0) {
            list.innerHTML = `
                <div class="notifications-empty">
                    <i class="fas fa-bell-slash"></i>
                    Tidak ada peringatan baru
                </div>`;
            return;
        }

        list.innerHTML = data.map(item => {
            const iconClass = item.type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-exclamation-triangle';
            const iconColorClass = item.type === 'error' ? 'error' : 'warning';
            const clientId = item.client_id || 'null';
            const clientName = item.client_name ? item.client_name.replace(/'/g, "\\'") : '';
            const clientSektor = item.client_sektor ? item.client_sektor.replace(/'/g, "\\'") : '';
            const itemId = item.id ? item.id.replace(/'/g, "\\'") : '';
            return `
                <div class="notification-item" style="position: relative;" onclick="handleNotificationClick('${item.link}', ${clientId}, '${clientName}', '${clientSektor}', '${itemId}')">
                    <div class="notification-icon ${iconColorClass}">
                        <i class="${iconClass}"></i>
                    </div>
                    <div class="notification-content" style="padding-right: 28px;">
                        <div class="notification-item-title">${item.title}</div>
                        <div class="notification-item-message">${item.message}</div>
                    </div>
                    <button class="notification-dismiss-btn" onclick="dismissNotification(event, '${itemId}')" style="position: absolute; right: 12px; top: 12px; border: none; background: transparent; color: #94a3b8; cursor: pointer; padding: 4px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 14px; z-index: 10;" onmouseover="this.style.color='#ef4444'; this.style.backgroundColor='#fee2e2';" onmouseout="this.style.color='#94a3b8'; this.style.backgroundColor='transparent';" title="Hapus Notifikasi">
                        <i class="fas fa-times"></i>
                    </button>
                </div>`;
        }).join('');

    } catch (err) {
        console.error('Error fetching notifications:', err);
        list.innerHTML = `
            <div class="notifications-empty">
                <i class="fas fa-exclamation-circle"></i>
                Gagal memuat notifikasi
            </div>`;
    }
}

async function dismissNotification(event, notificationId) {
    if (event) {
        event.stopPropagation();
    }
    if (!notificationId) return;
    try {
        const res = await fetch(`${API_URL}/notifications/dismiss`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notification_id: notificationId })
        });
        if (res.ok) {
            fetchNotifications(); // Refresh notifications dropdown
        } else {
            console.error('Failed to dismiss notification');
        }
    } catch (err) {
        console.error('Error dismissing notification:', err);
    }
}

window.dismissNotification = dismissNotification;

function handleNotificationClick(link, clientId = null, clientName = '', clientSektor = '', itemId = '') {
    // Close dropdown
    const dropdown = document.getElementById('notificationsDropdown');
    if (dropdown) dropdown.classList.remove('show');

    // If it is an attendance warning / upload notification, direct to attendance and open upload modal
    if (itemId && (itemId.startsWith('attendance_') || link === 'attendance')) {
        if (clientId) {
            // Switch view first to avoid resetting window.selectedClientId
            switchView('attendance');

            window.selectedClientId = clientId;
            window.selectedClientName = clientName;
            window.selectedClientSektor = clientSektor;
            localStorage.setItem('selectedClientId', clientId);
            localStorage.setItem('selectedClientName', clientName);
            localStorage.setItem('selectedClientSektor', clientSektor);

            // Select in the main page dropdown
            const selectEl = document.getElementById('attendanceClientSelect');
            if (selectEl) {
                const optionExists = Array.from(selectEl.options).some(opt => opt.value == clientId);
                if (optionExists) {
                    selectEl.value = clientId;
                    if (typeof syncCustomClientDropdown === 'function') {
                        syncCustomClientDropdown();
                    }
                } else {
                    selectEl.innerHTML = `<option value="${clientId}" selected>Loading...</option>`;
                }
            }

            // Automatically open upload attendance modal
            setTimeout(() => {
                if (typeof window.bukaModalUploadAbsensi === 'function') {
                    window.bukaModalUploadAbsensi();
                } else if (typeof bukaModalUploadAbsensi === 'function') {
                    bukaModalUploadAbsensi();
                }
            }, 300);
        } else {
            switchView('attendance');
        }
        return;
    }

    // Switch view
    if (link === 'klien') {
        if (clientId && typeof selectClient === 'function') {
            selectClient(clientId, clientName, clientSektor);
            if (typeof switchWorkspaceTab === 'function') {
                switchWorkspaceTab('proses');
            }
            // If it is a cutoff notification, open the modal to create new period automatically
            if (itemId && itemId.startsWith('cutoff_')) {
                if (typeof window.bukaModalPeriode === 'function') {
                    window.bukaModalPeriode();
                } else if (typeof bukaModalPeriode === 'function') {
                    bukaModalPeriode();
                }
            }
        } else {
            switchView('klien');
        }
    } else if (link === 'kompensasi') {
        if (typeof switchPayrollSub === 'function') {
            switchPayrollSub('kompensasi');
        } else {
            switchView('masterKompensasi');
        }
    }
}

// Setup Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    const bell = document.getElementById('notificationsBell');
    const dropdown = document.getElementById('notificationsDropdown');

    if (bell && dropdown) {
        bell.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('show');
            if (dropdown.classList.contains('show')) {
                fetchNotifications();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    }

    // Initial load
    fetchNotifications();
    // Refresh notifications every 60 seconds
    setInterval(fetchNotifications, 60000);

    // Restore App View State on page load/refresh
    restoreAppState();
});

function restoreAppState() {
    const savedClientId = localStorage.getItem('selectedClientId');
    const savedClientName = localStorage.getItem('selectedClientName');
    const savedClientSektor = localStorage.getItem('selectedClientSektor');
    
    if (savedClientId) {
        window.selectedClientId = parseInt(savedClientId);
        window.selectedClientName = savedClientName;
        window.selectedClientSektor = savedClientSektor;
        
        const wsTitle = document.getElementById('clientWorkspaceTitle');
        const wsSektor = document.getElementById('clientWorkspaceSektor');
        if (wsTitle) wsTitle.innerText = savedClientName || '-';
        if (wsSektor) wsSektor.innerText = savedClientSektor || '-';
    }

    const savedView = localStorage.getItem('activeView') || 'dashboard';
    
    if (savedView === 'clientWorkspace' && savedClientId) {
        switchView('clientWorkspace');
        const savedWorkspaceTab = localStorage.getItem('activeWorkspaceTab') || 'karyawan';
        if (typeof switchWorkspaceTab === 'function') {
            switchWorkspaceTab(savedWorkspaceTab);
        }
    } else if (savedView === 'payroll') {
        const savedPayrollSub = localStorage.getItem('activePayrollSub') || 'setting';
        if (typeof switchPayrollSub === 'function') {
            switchPayrollSub(savedPayrollSub);
        } else {
            switchView('payroll');
        }
    } else if (savedView === 'schedule') {
        switchView('schedule');
        const savedScheduleTab = localStorage.getItem('activeScheduleTab') || 'holiday';
        if (typeof switchScheduleSubTab === 'function') {
            switchScheduleSubTab(savedScheduleTab);
        }
    } else {
        switchView(savedView);
    }
}

window.fetchNotifications = fetchNotifications;
window.handleNotificationClick = handleNotificationClick;


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

// API URL
const API_URL = BASE_URL + 'index.php/api';
window.API = API_URL;

let umrAllData = [];
let simulasiAllData = [];

// Update Header Nama
if (currentUser && document.getElementById('headerUserName')) {
    document.getElementById('headerUserName').innerText = currentUser.username;
}

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
    return parseFloat(String(val).replace(/\./g, '').replace(/,/g, '.')) || 0;
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

function showConfirm(message, title = 'Confirmation') {
    return new Promise((resolve) => {
        const overlay = document.getElementById('confirmOverlay');
        const dialog = document.getElementById('confirmDialog');
        document.getElementById('confirmTitle').innerText = title;
        document.getElementById('confirmMessage').innerText = message;
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
    }
}

function switchView(view) {
    // Auto-close any open modals when switching views, but keep sidebar open
    tutupSemuaModal(true);

    const clientScopedViews = ['karyawan', 'struktur', 'setup', 'pkwt', 'proses'];
    if (clientScopedViews.includes(view.toLowerCase())) {
        if (window.selectedClientId) {
            switchView('clientWorkspace');
            switchWorkspaceTab(view.toLowerCase());
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

    const sectionId = 'view' + view.charAt(0).toUpperCase() + view.slice(1);
    let menuId = 'menu' + view.charAt(0).toUpperCase() + view.slice(1);
    
    if (view === 'globalLokasiKerja') {
        menuId = 'menuManajemenKaryawan';
        const subItem = document.getElementById('submenuLokasiKerja');
        if (subItem) subItem.classList.add('active');
    }
    
    if(document.getElementById(sectionId)) document.getElementById(sectionId).classList.add('active');
    if(document.getElementById(menuId)) document.getElementById(menuId).classList.add('active');

    // Auto expand/collapse submenu based on active menu
    const submenu = document.getElementById('submenuKaryawan');
    const arrow = document.querySelector('#menuManajemenKaryawan .submenu-arrow');
    if (menuId === 'menuManajemenKaryawan') {
        if (submenu) submenu.style.display = 'block';
        if (arrow) arrow.style.transform = 'rotate(180deg)';
    } else {
        if (submenu) submenu.style.display = 'none';
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    }

    const titles = {
        dashboard: 'Dashboard',
        klien: 'Client Management',
        manajemenKaryawan: 'Employee Management',
        globalLokasiKerja: 'Employee Management',
        clientWorkspace: 'Client Workspace',
        payroll: 'Master Payroll Scheme',
        pajak: 'Master Payroll Scheme',
        masterKompensasi: 'Master Payroll Scheme',
        logAktivitas: 'Activity Log'
    };
    document.getElementById('viewTitle').innerText = titles[view] || 'Employee Management';

    // Highlight and expand parent menu if we are in one of the payroll submenus
    if (view === 'payroll' || view === 'masterKompensasi' || view === 'pajak') {
        const parentMenu = document.getElementById('menuPayroll');
        if (parentMenu) parentMenu.classList.add('active');
        togglePayrollSubmenu(true);
        const subItem = document.getElementById('submenu_' + currentPayrollSub);
        if (subItem) subItem.classList.add('active');
    } else {
        // Collapse submenu if switching to another section
        const submenu = document.getElementById('submenuPayroll');
        const arrow = document.querySelector('#menuPayroll .submenu-arrow');
        if (submenu) {
            submenu.style.display = 'none';
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }



    // Auto load data based on view
    if (view === 'dashboard') updateDashboardStats();
    if (view === 'klien') renderTable();
    if (view === 'manajemenKaryawan') renderManajemenKaryawan();
    if (view === 'globalLokasiKerja') { if (typeof loadGlobalWorkLocations === 'function') loadGlobalWorkLocations(); }
    if (view === 'payroll') {
        if (currentPayrollSub === 'uploadUmr') {
            switchPayrollSubTab('umr');
        } else {
            switchPayrollSubTab('skema');
        }
    }
    if (view === 'pajak') renderTaxSchemes();
    if (view === 'masterKompensasi') renderMasterKompensasi();
    if (view === 'logAktivitas') renderLogAktivitas();
}


// ===== UTILS & MODAL CLOSING =====
function tutupSemuaModal(keepSidebarOpen = false) {
    const modals = ['modalClient', 'modalSkema', 'modalKomponen', 'modalOrg', 'modalPajak', 'modalSetup', 'modalPKWT', 'modalPeriode', 'modalCutOff', 'modalSlip', 'modalManualUmr', 'modalUploadUmr', 'modalSkemaKompensasi', 'modalKomponenKompensasi', 'modalKaryawan', 'modalLokasiKerja', 'modalDetailSkemaPayroll', 'modalDetailSkemaPajak'];
    modals.forEach(m => { if(document.getElementById(m)) document.getElementById(m).style.display = 'none'; });
    
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

// Expose defined local core functions to window if needed
Object.assign(window, {
    switchView, logout, tutupSemuaModal, toggleSidebar, formatRupiah, formatRupiahInput, parseFormattedNumber, handleKomponenKompensasiNilaiInput, handleKomponenNilaiInput
});


// ===== DOMContentLoaded: Sidebar Init =====
    // Sidebar toggle button
    const sidebarToggleBtn = document.querySelector('.header-hamburger');
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', toggleSidebar);
    }

// Initialize default view
switchView('dashboard');

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
    }
}
window.quickAction = quickAction;


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


// ===== Helpers =====
function formatRupiah(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
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

function showConfirm(message, title = 'Konfirmasi') {
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

    if (diffSec < 60) return 'baru saja';
    if (diffMin < 60) return `${diffMin} menit yang lalu`;
    if (diffHr < 24) return `${diffHr} jam yang lalu`;
    return `${diffDays} hari yang lalu`;
}

async function updateDashboardStats() {
    try {
        const rc = await fetch(`${API_URL}/clients`);
        const cd = await rc.json();
        if (document.getElementById('statTotalKlien')) {
            document.getElementById('statTotalKlien').innerText = cd.length || 0;
        }

        const re = await fetch(`${API_URL}/employees`);
        const ed = await re.json();
        if (document.getElementById('statTotalKaryawan')) {
            document.getElementById('statTotalKaryawan').innerText = ed.length || 0;
        }

        const ro = await fetch(`${API_URL}/org`);
        const od = await ro.json();
        if (document.getElementById('statTotalDivisi')) {
            document.getElementById('statTotalDivisi').innerText = od.length || 0;
        }


    } catch (err) {
        console.error('Error updating dashboard stats:', err);
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

function switchView(view) {
    const clientScopedViews = ['karyawan', 'struktur', 'setup', 'pkwt', 'proses'];
    if (clientScopedViews.includes(view.toLowerCase())) {
        if (window.selectedClientId) {
            switchView('clientWorkspace');
            switchWorkspaceTab(view.toLowerCase());
            return;
        } else {
            switchView('klien');
            showToast('Pilih klien terlebih dahulu!', 'info');
            return;
        }
    }

    document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.sidebar-menu li').forEach(l => l.classList.remove('active'));

    const sectionId = 'view' + view.charAt(0).toUpperCase() + view.slice(1);
    const menuId = 'menu' + view.charAt(0).toUpperCase() + view.slice(1);
    
    if(document.getElementById(sectionId)) document.getElementById(sectionId).classList.add('active');
    if(document.getElementById(menuId)) document.getElementById(menuId).classList.add('active');

    const titles = {
        dashboard: 'Dashboard',
        klien: 'Manajemen Klien',
        manajemenKaryawan: 'Manajemen Karyawan',
        clientWorkspace: 'Workspace Klien',
        payroll: 'Master Skema Payroll',
        pajak: 'Master Skema Pajak',
        masterKompensasi: 'Master Skema Kompensasi',
        logAktivitas: 'Log Aktivitas'
    };
    document.getElementById('viewTitle').innerText = titles[view] || 'Payroll System';

    // Auto load data based on view
    if (view === 'dashboard') updateDashboardStats();
    if (view === 'klien') renderTable();
    if (view === 'manajemenKaryawan') renderManajemenKaryawan();
    if (view === 'payroll') switchPayrollSubTab('skema');
    if (view === 'pajak') renderTaxSchemes();
    if (view === 'masterKompensasi') renderMasterKompensasi();
    if (view === 'logAktivitas') renderLogAktivitas();
}

// ===== 1. KLIEN MODULE =====
async function renderTable() {
    try {
        const response = await fetch(`${API_URL}/clients`);
        clients = await response.json();
        const tbody = document.getElementById('tabelKlienBody');
        if (!tbody) return;
        tbody.innerHTML = clients.map(client => {
            const dateJoined = client.tgl_gabung ? new Date(client.tgl_gabung).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }) : '-';
            return `
                <tr style="cursor: pointer;" onclick="event.target.closest('button') ? null : selectClient(${client.id}, '${client.nama.replace(/'/g, "\\'")}', '${client.sektor.replace(/'/g, "\\'")}')">
                    <td style="font-weight: 600; color: var(--primary-color);">${client.nama}</td>
                    <td>${client.sektor}</td>
                    <td>${client.npwp || '-'}</td>
                    <td>${client.nib || '-'}</td>
                    <td>${dateJoined}</td>
                    <td>${client.alamat}</td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-icon btn-edit" onclick="bukaModal('edit', ${client.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon btn-delete" onclick="hapusKlien(${client.id})"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    } catch (err) { console.error(err); }
}

// ===== CLIENT WORKSPACE WORKFLOW =====
window.selectedClientId = null;
window.selectedClientName = null;
window.selectedClientSektor = null;

function selectClient(id, name, sektor) {
    window.selectedClientId = id;
    window.selectedClientName = name;
    window.selectedClientSektor = sektor;
    
    document.getElementById('clientWorkspaceTitle').innerText = name;
    document.getElementById('clientWorkspaceSektor').innerText = sektor;
    
    switchView('clientWorkspace');
    switchWorkspaceTab('karyawan');
}

function backToClientList() {
    window.selectedClientId = null;
    window.selectedClientName = null;
    window.selectedClientSektor = null;
    switchView('klien');
}

function switchWorkspaceTab(tab) {
    document.querySelectorAll('.ws-tab').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.querySelector(`.ws-tab[data-wtab="${tab}"]`);
    if (activeBtn) activeBtn.classList.add('active');

    document.querySelectorAll('.w-tab-panel').forEach(panel => panel.classList.remove('active'));
    const activePanel = document.getElementById('view' + tab.charAt(0).toUpperCase() + tab.slice(1));
    if (activePanel) activePanel.classList.add('active');

    if (tab === 'karyawan') {
        renderAllEmployees();
    } else if (tab === 'struktur') {
        if (typeof renderClientOrg === 'function') {
            renderClientOrg(window.selectedClientId);
        }
    } else if (tab === 'kompensasi') {
        loadPilihanSkema();
    } else if (tab === 'setup') {
        loadWorkspaceSetup();
    } else if (tab === 'pkwt') {
        renderPKWTTable();
    } else if (tab === 'proses') {
        loadActivePeriod();
    }
}

async function populateMinimumWageDropdown(tipe, selectElementId) {
    try {
        const res = await fetch(`${API_URL}/minimum-wages?tipe=${tipe}`);
        const data = await res.json();
        const select = document.getElementById(selectElementId);
        if (select) {
            select.innerHTML = '<option value="">-- Pilih Wilayah --</option>' +
                data.map(item => `<option value="${item.id}">${item.nama_daerah} (Rp ${parseFloat(item.nominal).toLocaleString('id-ID')})</option>`).join('');
        }
    } catch (err) {
        console.error('Error fetching minimum wages:', err);
    }
}

async function handlePilihanSkemaPayrollTipeChange() {
    const tipe = document.getElementById('pilihanSkemaPayrollTipe').value;
    const wilContainer = document.getElementById('pilihanSkemaPayrollWilayahContainer');
    const nomContainer = document.getElementById('pilihanSkemaPayrollNominalContainer');
    const tplContainer = document.getElementById('pilihanSkemaPayrollTemplateContainer');

    wilContainer.style.display = 'none';
    nomContainer.style.display = 'none';
    tplContainer.style.display = 'none';

    if (tipe === 'UMP' || tipe === 'UMK') {
        wilContainer.style.display = 'block';
        await populateMinimumWageDropdown(tipe, 'pilihanSkemaPayrollWilayah');
    } else if (tipe === 'Nominal') {
        nomContainer.style.display = 'block';
    } else if (tipe === 'Template') {
        tplContainer.style.display = 'block';
    }
}

async function handleSetupPayrollSchemeTipeChange() {
    const tipe = document.getElementById('setupPayrollSchemeTipe').value;
    const wilContainer = document.getElementById('setupPayrollSchemeWilayahContainer');
    const nomContainer = document.getElementById('setupPayrollSchemeNominalContainer');
    const tplContainer = document.getElementById('setupPayrollSchemeTemplateContainer');

    wilContainer.style.display = 'none';
    nomContainer.style.display = 'none';
    tplContainer.style.display = 'none';

    if (tipe === 'UMP' || tipe === 'UMK') {
        wilContainer.style.display = 'block';
        await populateMinimumWageDropdown(tipe, 'setupPayrollSchemeWilayah');
    } else if (tipe === 'Nominal') {
        nomContainer.style.display = 'block';
    } else if (tipe === 'Template') {
        tplContainer.style.display = 'block';
    }
}

async function loadWorkspaceSetup() {
    if (!window.selectedClientId) return;
    try {
        const response = await fetch(`${API_URL}/client-configs`);
        const configs = await response.json();
        const conf = configs.find(c => c.client_id == window.selectedClientId);
        
        document.getElementById('wSetupClientName').innerText = window.selectedClientName || '-';
        
        if (conf) {
            let payrollSchemeText = 'Belum Set';
            if (conf.payroll_type === 'UMP') {
                payrollSchemeText = `UMP: ${conf.minimum_wage_region || 'Belum Set'} (Rp ${conf.minimum_wage_nominal ? parseFloat(conf.minimum_wage_nominal).toLocaleString('id-ID') : '-'})`;
            } else if (conf.payroll_type === 'UMK') {
                payrollSchemeText = `UMK: ${conf.minimum_wage_region || 'Belum Set'} (Rp ${conf.minimum_wage_nominal ? parseFloat(conf.minimum_wage_nominal).toLocaleString('id-ID') : '-'})`;
            } else if (conf.payroll_type === 'Nominal') {
                payrollSchemeText = `Nominal: Rp ${conf.custom_nominal ? parseFloat(conf.custom_nominal).toLocaleString('id-ID') : '-'}`;
            } else if (conf.payroll_type === 'Template') {
                payrollSchemeText = conf.payroll_scheme_name || 'Belum Set';
            }
            document.getElementById('wSetupPayrollScheme').innerText = payrollSchemeText;
            document.getElementById('wSetupTaxScheme').innerText = conf.tax_scheme_name || 'Belum Set';
            document.getElementById('wSetupPayDate').innerText = conf.pay_date ? `Tgl ${conf.pay_date}` : 'Belum Set';
            document.getElementById('wSetupCutoff').innerText = conf.cutoff_start ? `${conf.cutoff_start} s/d ${(conf.cutoff_start - 1)}` : 'Belum Set';
        } else {
            document.getElementById('wSetupPayrollScheme').innerText = 'Belum Set';
            document.getElementById('wSetupTaxScheme').innerText = 'Belum Set';
            document.getElementById('wSetupPayDate').innerText = 'Belum Set';
            document.getElementById('wSetupCutoff').innerText = 'Belum Set';
        }
    } catch (err) {
        console.error(err);
    }
}

function bukaModal(mode, id = null) {
    const modal = document.getElementById('modalClient');
    const overlay = document.getElementById('overlay');
    modal.style.display = 'block';
    overlay.style.display = 'block';
    if (mode === 'edit' && id) {
        const client = clients.find(c => c.id == id);
        if (client) {
            document.getElementById('modalTitle').innerText = 'Edit Data Client';
            document.getElementById('clientId').value = client.id;
            document.getElementById('namaKlien').value = client.nama;
            document.getElementById('emailKlien').value = client.email;
            document.getElementById('sektorKlien').value = client.sektor;
            document.getElementById('nib').value = client.nib;
            document.getElementById('npwp').value = client.npwp;
            document.getElementById('tanggalBergabung').value = client.tgl_gabung ? client.tgl_gabung.split('T')[0] : '';
            document.getElementById('alamat').value = client.alamat;
        }
    } else {
        document.getElementById('modalTitle').innerText = 'Tambah Data Client';
        document.getElementById('formKlien').reset();
        document.getElementById('clientId').value = '';
    }
}

async function hapusKlien(id) {
    if (!await showConfirm('Hapus klien ini?')) return;
    try {
        const res = await fetch(`${API_URL}/clients/${id}`, { method: 'DELETE' });
        if (res.ok) { renderTable(); showToast('Klien berhasil dihapus', 'success'); }
    } catch (err) { console.error(err); }
}

// ===== 2. STRUKTUR ORGANISASI & KARYAWAN =====
async function renderAllEmployees() {
    try {
        const url = window.selectedClientId ? `${API_URL}/employees?client_id=${window.selectedClientId}` : `${API_URL}/employees`;
        const res = await fetch(url);
        const employees = await res.json();
        window.employees = employees; // Expose globally for app-org.js
        const tbody = document.getElementById('tabelKaryawanBody');
        if(!tbody) return;
        tbody.innerHTML = employees.map(emp => `
            <tr>
                <td style="font-weight:600;"><i class="fas fa-user" style="margin-right: 8px; opacity: 0.6;"></i>${emp.nama}</td>
                <td>${emp.nama_posisi || '-'}</td>
                <td>${emp.nama_dept || '-'}</td>
                <td>${emp.alamat || '-'}</td>
                <td>${emp.email || '-'}</td>
            </tr>
        `).join('');
    } catch (err) { console.error(err); }
}

// ===== 3. PAYROLL SCHEMES =====
async function renderPayrollSchemes() {
    try {
        const res = await fetch(`${API_URL}/payroll-schemes`);
        payrollSchemes = await res.json();
        const container = document.getElementById('payrollSchemesContainer');
        if(!container) return;
        container.innerHTML = payrollSchemes.map(scheme => `
            <div class="scheme-card">
                <div class="scheme-card-header">
                    <div class="scheme-card-info">
                        <h4><i class="fas fa-file-invoice-dollar"></i> ${scheme.nama}</h4>
                        <div class="scheme-card-desc">${scheme.deskripsi || 'Tidak ada deskripsi'}</div>
                    </div>
                    <div class="scheme-card-actions">
                        <button class="btn-icon btn-edit" onclick="bukaModalSkema('edit', ${scheme.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon btn-delete" onclick="hapusSkema(${scheme.id})"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (err) { console.error(err); }
}

// ===== 4. TAX SCHEMES (PPh 21) =====
async function renderTaxSchemes() {
    try {
        const response = await fetch(`${API_URL}/tax-schemes`);
        taxSchemes = await response.json();
        const container = document.getElementById('taxSchemesContainer');
        if (!container) return;
        container.innerHTML = taxSchemes.map(scheme => `
            <div class="scheme-card" style="border-left: 4px solid var(--danger);">
                <div class="scheme-card-header">
                    <div class="scheme-card-info">
                        <h4><i class="fas fa-percent" style="color: var(--danger);"></i> ${scheme.nama}</h4>
                        <div class="scheme-card-desc">Metode: <b>${scheme.metode}</b> | PTKP: <b>${scheme.ptkp_status}</b></div>
                    </div>
                    <div class="scheme-card-actions">
                        <button class="btn-icon btn-edit" onclick="bukaModalPajak('edit', ${scheme.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon btn-delete" onclick="hapusPajak(${scheme.id})"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (err) { console.error(err); }
}

function bukaModalPajak(mode, id = null) {
    const modal = document.getElementById('modalPajak');
    document.getElementById('modalPajak').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    if (mode === 'edit' && id) {
        const scheme = taxSchemes.find(s => s.id == id);
        if (scheme) {
            document.getElementById('pajakId').value = scheme.id;
            document.getElementById('pajakNama').value = scheme.nama;
            document.getElementById('pajakMetode').value = scheme.metode;
            document.getElementById('pajakPtkp').value = scheme.ptkp_status;
            document.getElementById('pajakDeskripsi').value = scheme.deskripsi;
        }
    } else {
        document.getElementById('formPajak').reset();
        document.getElementById('pajakId').value = '';
    }
}

// ===== 5. SETUP PAYROLL KLIEN =====
async function renderClientSetup() {
    try {
        const response = await fetch(`${API_URL}/client-configs`);
        clientConfigs = await response.json();
        const tbody = document.getElementById('tabelSetupBody');
        if (!tbody) return;
        tbody.innerHTML = clientConfigs.map(conf => {
            let payrollSchemeText = 'Belum Set';
            if (conf.payroll_type === 'UMP') {
                payrollSchemeText = `UMP: ${conf.minimum_wage_region || 'Belum Set'} (Rp ${conf.minimum_wage_nominal ? parseFloat(conf.minimum_wage_nominal).toLocaleString('id-ID') : '-'})`;
            } else if (conf.payroll_type === 'UMK') {
                payrollSchemeText = `UMK: ${conf.minimum_wage_region || 'Belum Set'} (Rp ${conf.minimum_wage_nominal ? parseFloat(conf.minimum_wage_nominal).toLocaleString('id-ID') : '-'})`;
            } else if (conf.payroll_type === 'Nominal') {
                payrollSchemeText = `Nominal: Rp ${conf.custom_nominal ? parseFloat(conf.custom_nominal).toLocaleString('id-ID') : '-'}`;
            } else if (conf.payroll_type === 'Template') {
                payrollSchemeText = conf.payroll_scheme_name || 'Belum Set';
            }
            return `
                <tr>
                    <td style="font-weight: 600;">${conf.client_name}</td>
                    <td><span class="scheme-badge bulanan">${payrollSchemeText}</span></td>
                    <td><span class="scheme-badge" style="background:#e74c3c;">${conf.tax_scheme_name || 'Belum Set'}</span></td>
                    <td>Tgl ${conf.pay_date || '-'}</td>
                    <td>${conf.cutoff_start || '-'}-${(conf.cutoff_start - 1) || '-'}</td>
                    <td>
                        <button class="btn-icon btn-edit" onclick="bukaModalSetup(${conf.client_id}, '${conf.client_name}')"><i class="fas fa-cog"></i></button>
                    </td>
                </tr>
            `;
        }).join('');
    } catch (err) { console.error(err); }
}

async function bukaModalSetup(clientId, clientName) {
    document.getElementById('modalSetup').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('setupClientId').value = clientId;
    document.getElementById('setupClientNama').value = clientName;
    
    const pRes = await fetch(`${API_URL}/payroll-schemes`);
    const pSchemes = await pRes.json();
    const tRes = await fetch(`${API_URL}/tax-schemes`);
    const tSchemes = await tRes.json();

    document.getElementById('setupPayrollScheme').innerHTML = '<option value="">-- Pilih Skema --</option>' + pSchemes.map(s => `<option value="${s.id}">${s.nama}</option>`).join('');
    document.getElementById('setupTaxScheme').innerHTML = '<option value="">-- Pilih Skema --</option>' + tSchemes.map(s => `<option value="${s.id}">${s.nama}</option>`).join('');
    
    const current = clientConfigs.find(c => c.client_id == clientId);
    if(current) {
        document.getElementById('setupPayDate').value = current.pay_date || 25;
        document.getElementById('setupCutoffStart').value = current.cutoff_start || 21;
        
        const tipeSelect = document.getElementById('setupPayrollSchemeTipe');
        if (tipeSelect) {
            tipeSelect.value = current.payroll_type || '';
            await handleSetupPayrollSchemeTipeChange();
            
            if (current.payroll_type === 'UMP' || current.payroll_type === 'UMK') {
                const wilSelect = document.getElementById('setupPayrollSchemeWilayah');
                if (wilSelect && current.minimum_wage_id) {
                    wilSelect.value = current.minimum_wage_id;
                }
            } else if (current.payroll_type === 'Nominal') {
                const nomInput = document.getElementById('setupPayrollSchemeNominal');
                if (nomInput && current.custom_nominal) {
                    nomInput.value = current.custom_nominal;
                }
            } else if (current.payroll_type === 'Template') {
                const tplSelect = document.getElementById('setupPayrollScheme');
                if (tplSelect && current.payroll_scheme_id) {
                    tplSelect.value = current.payroll_scheme_id;
                }
            }
        }
    } else {
        const tipeSelect = document.getElementById('setupPayrollSchemeTipe');
        if (tipeSelect) {
            tipeSelect.value = '';
            handleSetupPayrollSchemeTipeChange();
        }
    }
}

// ===== 6. PKWT (KONTRAK KERJA) =====
async function renderPKWTTable() {
    try {
        const url = window.selectedClientId ? `${API_URL}/pkwt?client_id=${window.selectedClientId}` : `${API_URL}/pkwt`;
        const response = await fetch(url);
        pkwtData = await response.json();
        const tbody = document.getElementById('tabelPKWTBody');
        if (!tbody) return;
        tbody.innerHTML = pkwtData.map(row => {
            const basicComp = (row.components || []).find(c => c.nama.toLowerCase().includes('gaji pokok'));
            return `
                <tr>
                    <td style="font-weight: 600; color: var(--primary-color);">${row.employee_name}</td>
                    <td>${row.client_name}</td>
                    <td>${row.position_name}</td>
                    <td>${new Date(row.start_date).toLocaleDateString()}</td>
                    <td><span class="status-badge success">${row.status}</span></td>
                    <td>${formatRupiah(basicComp ? basicComp.nilai : 0)}</td>
                    <td><button class="btn-icon btn-delete" onclick="hapusPKWT(${row.id})"><i class="fas fa-trash"></i></button></td>
                </tr>
            `;
        }).join('');
    } catch (err) { console.error(err); }
}

async function hapusPKWT(id) {
    if (!await showConfirm('Apakah Anda yakin ingin menghapus PKWT ini?')) return;
    try {
        const res = await fetch(`${API_URL}/pkwt/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderPKWTTable();
            showToast('PKWT berhasil dihapus!', 'success');
        } else {
            showToast('Gagal menghapus PKWT!', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Gagal menghapus PKWT!', 'error');
    }
}

function bukaModalPKWT() {
    document.getElementById('modalPKWT').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    fetch(`${API_URL}/clients`).then(r => r.json()).then(data => {
        const select = document.getElementById('pkwtClientId');
        select.innerHTML = '<option value="">-- Pilih Klien --</option>' + data.map(c => `<option value="${c.id}">${c.nama}</option>`).join('');
        if (window.selectedClientId) {
            select.value = window.selectedClientId;
            if (typeof window.updatePKWTSchemeInfo === 'function') {
                window.updatePKWTSchemeInfo();
            }
        }
    });
}

// ===== 7. PROSES PAYROLL BULANAN =====
async function loadActivePeriod() {
    try {
        const response = await fetch(`${API_URL}/periods`);
        const periods = await response.json();
        
        // 1. Render dropdown selector on the main page
        const select = document.getElementById('selectPeriodInput');
        if (select) {
            select.innerHTML = '<option value="">-- Pilih Periode --</option>' + periods.map(p => `
                <option value="${p.id}" ${p.id == currentPeriodId ? 'selected' : ''}>${p.nama} (${p.status})</option>
            `).join('');
        }
        
        // 2. Render history list inside the popup modal
        const list = document.getElementById('periodHistoryList');
        if (list) {
            list.innerHTML = periods.map(p => `
                <div class="period-item ${p.id == currentPeriodId ? 'active' : ''}" onclick="selectPeriod(${p.id}, '${p.nama}')" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: ${p.id == currentPeriodId ? '#f0fdf4' : 'transparent'};">
                    <span style="font-weight: 500;">${p.nama}</span>
                    <span class="status-badge success" style="font-size: 11px;">${p.status}</span>
                </div>
            `).join('');
        }
        
        if (periods.length > 0) {
            if (!currentPeriodId) {
                selectPeriod(periods[0].id, periods[0].nama);
                if (select) select.value = periods[0].id;
            } else {
                selectPeriod(currentPeriodId, periods.find(p => p.id == currentPeriodId)?.nama || '');
                if (select) select.value = currentPeriodId;
            }
        } else {
            document.getElementById('prosesActions').style.display = 'none';
            document.getElementById('prosesEmptyState').style.display = 'block';
        }
    } catch (err) { console.error(err); }
}

function selectPeriod(id, name) {
    currentPeriodId = id;
    if (!id || id === 'null' || id === '') {
        document.getElementById('prosesActions').style.display = 'none';
        document.getElementById('prosesEmptyState').style.display = 'block';
        return;
    }
    document.getElementById('activePeriodName').innerText = name;
    document.getElementById('prosesActions').style.display = 'block';
    document.getElementById('prosesEmptyState').style.display = 'none';
    renderCutOffTable();
    renderReviewGajiTable();
    tutupSemuaModal();
}

async function renderCutOffTable() {
    if(!currentPeriodId) return;
    const url = window.selectedClientId ? `${API_URL}/attendance/${currentPeriodId}?client_id=${window.selectedClientId}` : `${API_URL}/attendance/${currentPeriodId}`;
    const res = await fetch(url);
    const data = await res.json();
    document.getElementById('tabelCutOffBody').innerHTML = data.map(row => `
        <tr>
            <td>${row.employee_name}</td>
            <td>${row.hari_kerja || 0} Hari</td>
            <td>${row.jam_lembur || 0} Jam</td>
            <td>${formatRupiah(row.potongan_absensi)}</td>
            <td>${formatRupiah(row.bonus_tambahan)}</td>
            <td><button class="btn-icon btn-edit" onclick="bukaModalCutOff(${row.pkwt_id}, '${row.employee_name}')"><i class="fas fa-edit"></i></button></td>
        </tr>
    `).join('');
}

async function renderReviewGajiTable() {
    if(!currentPeriodId) return;
    const url = window.selectedClientId ? `${API_URL}/payroll-results/${currentPeriodId}?client_id=${window.selectedClientId}` : `${API_URL}/payroll-results/${currentPeriodId}`;
    const res = await fetch(url);
    const data = await res.json();
    const section = document.getElementById('resultSection');
    const tbody = document.getElementById('tabelReviewGajiBody');
    if (data.length > 0) {
        section.style.display = 'block';
        tbody.innerHTML = data.map(row => `
            <tr>
                <td>${row.employee_name}</td>
                <td style="color:var(--success);">${formatRupiah(row.total_pendapatan)}</td>
                <td style="color:var(--danger);">${formatRupiah(row.total_potongan)}</td>
                <td style="font-weight:700;">${formatRupiah(row.take_home_pay)}</td>
                <td><span class="status-badge ${row.status_approval === 'Approved' ? 'success' : 'warning'}">${row.status_approval}</span></td>
                <td>
                    ${row.status_approval === 'Pending' ? 
                        `<button class="btn-save" onclick="approveGaji(${row.id})" style="padding:5px 10px; font-size:11px;">Approve</button>` : 
                        `<button class="btn-icon" onclick="bukaSlipGaji(${row.id})" title="Lihat Slip Gaji" style="background:var(--primary-color); color:white; width:30px; height:30px;"><i class="fas fa-eye"></i></button>`
                    }
                </td>
            </tr>
        `).join('');
    } else { section.style.display = 'none'; }
}

async function generateGaji() {
    if(!currentPeriodId) return;
    showToast('Menghitung gaji...', 'info');
    const res = await fetch(`${API_URL}/generate-payroll/${currentPeriodId}`, { method: 'POST' });
    if (res.ok) { showToast('Gaji berhasil di-generate!', 'success'); renderReviewGajiTable(); }
}

async function approveGaji(id) {
    const res = await fetch(`${API_URL}/approve-payroll/${id}`, { method: 'POST' });
    if (res.ok) { showToast('Gaji disetujui!', 'success'); renderReviewGajiTable(); }
}

// ===== UTILS & MODAL CLOSING =====
function tutupSemuaModal() {
    const modals = ['modalClient', 'modalSkema', 'modalKomponen', 'modalOrg', 'modalPajak', 'modalSetup', 'modalPKWT', 'modalPeriode', 'modalCutOff', 'modalSlip', 'modalManualUmr', 'modalUploadUmr', 'modalSkemaKompensasi', 'modalKomponenKompensasi'];
    modals.forEach(m => { if(document.getElementById(m)) document.getElementById(m).style.display = 'none'; });
    if(document.getElementById('overlay')) document.getElementById('overlay').style.display = 'none';
}

// ===== SLIP GAJI =====

async function bukaSlipGaji(id) {
    try {
        const response = await fetch(`${API_URL}/slip-details/${id}`);
        const data = await response.json();

        document.getElementById('slipClientName').innerText = data.info.client_name;
        document.getElementById('slipPeriod').innerText = data.info.period_name;
        document.getElementById('slipEmployeeName').innerText = data.info.employee_name;
        document.getElementById('slipPosition').innerText = data.info.position_name;
        document.getElementById('slipTHP').innerText = formatRupiah(data.info.take_home_pay);

        const eList = document.getElementById('slipEarningsList');
        const dList = document.getElementById('slipDeductionsList');

        eList.innerHTML = data.earnings.map(e => `
            <div style="display:flex; justify-content:space-between;">
                <span>${e.nama}</span>
                <span>${formatRupiah(e.nilai)}</span>
            </div>
        `).join('');

        dList.innerHTML = data.deductions.map(d => `
            <div style="display:flex; justify-content:space-between;">
                <span>${d.nama}</span>
                <span>${formatRupiah(d.nilai)}</span>
            </div>
        `).join('');

        document.getElementById('modalSlip').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    } catch (err) {
        console.error('Error loading slip details:', err);
    }
}

function tutupModalSlip() {
    document.getElementById('modalSlip').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function cetakSlip() {
    const content = document.getElementById('slipContent').innerHTML;
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print Pay Slip</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">');
    printWindow.document.write('<style>body{font-family: "Inter", sans-serif; padding: 40px;} .primary-color{color: #2980b9;} .success{color: #27ae60;} .danger{color: #e74c3c;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    setTimeout(() => {
        printWindow.print();
    }, 500);
}

// Initialization
document.addEventListener('DOMContentLoaded', () => {
    switchView('dashboard');
    
    // Global Event Listeners
    if(document.getElementById('formKlien')) {
        document.getElementById('formKlien').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('clientId').value;
            const data = {
                nama: document.getElementById('namaKlien').value,
                email: document.getElementById('emailKlien').value,
                sektor: document.getElementById('sektorKlien').value,
                nib: document.getElementById('nib').value,
                npwp: document.getElementById('npwp').value,
                tgl_gabung: document.getElementById('tanggalBergabung').value,
                alamat: document.getElementById('alamat').value
            };
            const res = await fetch(`${API_URL}/clients${id ? '/' + id : ''}`, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (res.ok) { tutupSemuaModal(); renderTable(); showToast('Data berhasil disimpan!', 'success'); }
        });
    }

    // formSetup submit handler
    if(document.getElementById('formSetup')) {
        document.getElementById('formSetup').addEventListener('submit', async (e) => {
            e.preventDefault();
            const payrollType = document.getElementById('setupPayrollSchemeTipe').value;
            const minimumWageId = document.getElementById('setupPayrollSchemeWilayah').value;
            const customNominal = document.getElementById('setupPayrollSchemeNominal').value;
            const payrollSchemeId = document.getElementById('setupPayrollScheme').value;

            const data = {
                client_id: document.getElementById('setupClientId').value,
                payroll_type: payrollType || null,
                minimum_wage_id: (payrollType === 'UMP' || payrollType === 'UMK') ? (minimumWageId || null) : null,
                custom_nominal: (payrollType === 'Nominal') ? (customNominal || null) : null,
                payroll_scheme_id: (payrollType === 'Template') ? (payrollSchemeId || null) : null,
                tax_scheme_id: document.getElementById('setupTaxScheme').value,
                pay_date: parseInt(document.getElementById('setupPayDate').value),
                cutoff_start: parseInt(document.getElementById('setupCutoffStart').value)
            };
            const res = await fetch(`${API_URL}/client-configs`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (res.ok) {
                tutupSemuaModal();
                showToast('Setup Payroll berhasil disimpan!', 'success');
                if (window.selectedClientId) {
                    loadWorkspaceSetup();
                } else {
                    renderClientSetup();
                }
            } else {
                showToast('Gagal menyimpan Setup Payroll!', 'error');
            }
        });
    }

    // formPeriode submit handler
    if(document.getElementById('formPeriode')) {
        document.getElementById('formPeriode').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = {
                bulan: parseInt(document.getElementById('periodMonth').value),
                tahun: parseInt(document.getElementById('periodYear').value)
            };
            const res = await fetch(`${API_URL}/periods`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (res.ok) {
                tutupSemuaModal();
                showToast('Periode baru berhasil dibuka!', 'success');
                loadActivePeriod();
            } else {
                showToast('Gagal membuka periode baru!', 'error');
            }
        });
    }

    // Modal PKWT update info
    window.updatePKWTSchemeInfo = async () => {
        const clientId = document.getElementById('pkwtClientId').value;
        if(!clientId) return;
        const res = await fetch(`${API_URL}/client-configs`);
        const configs = await res.json();
        const conf = configs.find(c => c.client_id == clientId);
        const box = document.getElementById('pkwtSchemeInfo');
        box.style.display = 'block';
        document.getElementById('pkwtSchemeText').innerText = conf ? `Skema: ${conf.payroll_scheme_name}` : 'Klien belum di-setup skemanya.';
    };

    // Form Skema Payroll submit handler
    if (document.getElementById('formSkema')) {
        document.getElementById('formSkema').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('skemaId').value;
            const data = {
                nama: document.getElementById('skemaNama').value,
                deskripsi: document.getElementById('skemaDeskripsi').value,
                tipe: document.getElementById('skemaTipe').value
            };
            const url = id ? `${API_URL}/payroll-schemes/${id}` : `${API_URL}/payroll-schemes`;
            const res = await fetch(url, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (res.ok) {
                tutupSemuaModal();
                renderPayrollSchemes();
                showToast(id ? 'Skema payroll berhasil diupdate!' : 'Skema payroll berhasil ditambahkan!', 'success');
            } else {
                showToast('Gagal menyimpan skema payroll!', 'error');
            }
        });
    }

    // Form Skema Pajak submit handler
    if (document.getElementById('formPajak')) {
        document.getElementById('formPajak').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('pajakId').value;
            const data = {
                nama: document.getElementById('pajakNama').value,
                metode: document.getElementById('pajakMetode').value,
                ptkp_status: document.getElementById('pajakPtkp').value,
                deskripsi: document.getElementById('pajakDeskripsi').value
            };
            const url = id ? `${API_URL}/tax-schemes/${id}` : `${API_URL}/tax-schemes`;
            const res = await fetch(url, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (res.ok) {
                tutupSemuaModal();
                renderTaxSchemes();
                showToast(id ? 'Skema pajak berhasil diupdate!' : 'Skema pajak berhasil ditambahkan!', 'success');
            } else {
                showToast('Gagal menyimpan skema pajak!', 'error');
            }
        });
    }

    // Initialize Simulation Data
    loadSimulasiRegions();

    // Sidebar toggle button
    const sidebarToggleBtn = document.querySelector('.header-left .fa-bars');
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', toggleSidebar);
    }

    // Form Skema Kompensasi submit handler
    if (document.getElementById('formSkemaKompensasi')) {
        document.getElementById('formSkemaKompensasi').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('skemaKompensasiId').value;
            const data = {
                nama: document.getElementById('skemaKompensasiNama').value,
                deskripsi: document.getElementById('skemaKompensasiDeskripsi').value
            };
            const url = id ? `${API_URL}/compensation-schemes/${id}` : `${API_URL}/compensation-schemes`;
            const res = await fetch(url, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (res.ok) {
                tutupSemuaModal();
                renderMasterKompensasi();
                showToast(id ? 'Skema kompensasi berhasil diupdate!' : 'Skema kompensasi berhasil ditambahkan!', 'success');
            } else {
                showToast('Gagal menyimpan skema!', 'error');
            }
        });
    }

    // Form Komponen Kompensasi submit handler
    if (document.getElementById('formKomponenKompensasi')) {
        document.getElementById('formKomponenKompensasi').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('komponenKompensasiId').value;
            const schemeId = document.getElementById('komponenKompensasiSchemeId').value;
            const jenis = document.getElementById('komponenKompensasiJenis').value;
            const sifat = document.getElementById('komponenKompensasiSifat').value;
            let nama = '';
            if (jenis === 'basic_salary') {
                nama = 'Gaji Pokok';
            } else {
                nama = sifat === 'tetap' ? 'Kompensasi Tetap' : 'Kompensasi Tidak Tetap';
            }

            const data = {
                scheme_id: parseInt(schemeId),
                nama: nama,
                tipe: 'pendapatan',
                nilai: parseFloat(document.getElementById('komponenKompensasiNilai').value) || 0,
                is_persentase: parseInt(document.getElementById('komponenKompensasiIsPersentase').value) || 0,
                jenis_komponen: jenis,
                sifat_kompensasi: sifat,
                sumber_nilai: document.getElementById('komponenKompensasiSumber').value,
                periode: document.getElementById('komponenKompensasiPeriode').value
            };
            const url = id ? `${API_URL}/compensation-components/${id}` : `${API_URL}/compensation-components`;
            const res = await fetch(url, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (res.ok) {
                tutupSemuaModal();
                renderMasterKompensasi();
                showToast(id ? 'Komponen berhasil diupdate!' : 'Komponen berhasil ditambahkan!', 'success');
            } else {
                showToast('Gagal menyimpan komponen!', 'error');
            }
        });
    }

    // Form PKWT submit handler
    if (document.getElementById('formPKWT')) {
        document.getElementById('formPKWT').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = {
                employee_name: document.getElementById('pkwtEmployeeName').value,
                client_id: parseInt(document.getElementById('pkwtClientId').value),
                position_name: document.getElementById('pkwtPositionName').value,
                start_date: document.getElementById('pkwtStartDate').value,
                end_date: document.getElementById('pkwtEndDate').value,
                basic_salary: parseFloat(document.getElementById('pkwtBasicSalary').value) || 0,
                status: 'Aktif'
            };
            try {
                const res = await fetch(`${API_URL}/pkwt`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                if (res.ok) {
                    tutupModalPKWT();
                    renderPKWTTable();
                    showToast('PKWT berhasil dibuat dan gaji telah tergenerate', 'success');
                } else {
                    showToast('Gagal membuat PKWT', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Gagal membuat PKWT', 'error');
            }
        });
    }
});

// ===== PILIHAN SKEMA (Client Workspace Tab) =====
async function renderPilihanKompensasiSummary(schemeId) {
    const summaryDiv = document.getElementById('pilihanKompensasiSummary');
    if (!summaryDiv) return;
    if (!schemeId) {
        summaryDiv.innerHTML = `<p style="text-align: center; color: #94a3b8; font-size: 13px; margin: 0;">Pilih skema kompensasi di atas untuk melihat detailnya.</p>`;
        return;
    }
    try {
        const res = await fetch(`${API_URL}/compensation-schemes`);
        const schemes = await res.json();
        const scheme = schemes.find(s => s.id == schemeId);
        if (!scheme) {
            summaryDiv.innerHTML = `<p style="text-align: center; color: #94a3b8; font-size: 13px; margin: 0;">Skema tidak ditemukan.</p>`;
            return;
        }
        
        const comps = scheme.components || [];
        if (comps.length === 0) {
            summaryDiv.innerHTML = `
                <div style="text-align: center; padding: 10px;">
                    <i class="fas fa-info-circle" style="color: #94a3b8; margin-right: 6px;"></i>
                    <span style="color: #94a3b8; font-size: 13px;">Belum ada komponen kompensasi dalam skema ini.</span>
                </div>`;
            return;
        }

        const pendapatan = comps.filter(c => c.tipe === 'pendapatan');
        const potongan = comps.filter(c => c.tipe === 'potongan');
        const totalPendapatan = pendapatan.reduce((sum, c) => sum + parseFloat(c.nilai || 0), 0);
        const totalPotongan = potongan.reduce((sum, c) => sum + parseFloat(c.nilai || 0), 0);
        
        summaryDiv.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <span style="font-weight: 600; color: #1e293b; font-size: 14px;"><i class="fas fa-list" style="margin-right: 6px; color: var(--primary-color);"></i> ${comps.length} Komponen</span>
                <div style="display: flex; gap: 12px;">
                    <span style="font-size: 12px; background: #dcfce7; color: #16a34a; padding: 4px 10px; border-radius: 20px; font-weight: 600;">+ ${formatRupiah(totalPendapatan)}</span>
                    <span style="font-size: 12px; background: #fee2e2; color: #dc2626; padding: 4px 10px; border-radius: 20px; font-weight: 600;">- ${formatRupiah(totalPotongan)}</span>
                </div>
            </div>
            ${comps.map(c => `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; font-size: 13px;">
                    <span style="color: #475569;">${c.nama}</span>
                    <span style="font-weight: 600; color: ${c.tipe === 'pendapatan' ? '#16a34a' : '#dc2626'};">
                        ${c.tipe === 'pendapatan' ? '+' : '-'} ${c.is_persentase == 1 ? c.nilai + '%' : formatRupiah(c.nilai)}
                    </span>
                </div>
            `).join('')}
        `;
    } catch (err) {
        console.error(err);
    }
}

async function loadPilihanSkema() {
    if (!window.selectedClientId) return;
    try {
        // Load payroll schemes for dropdown
        const psRes = await fetch(`${API_URL}/payroll-schemes`);
        const payrollSchemes = await psRes.json();
        const psSelect = document.getElementById('pilihanSkemaPayroll');
        if (psSelect) {
            psSelect.innerHTML = '<option value="">-- Pilih Skema Payroll --</option>' +
                payrollSchemes.map(s => `<option value="${s.id}">${s.nama} (${s.tipe || 'Umum'})</option>`).join('');
        }

        // Load tax schemes for dropdown
        const tsRes = await fetch(`${API_URL}/tax-schemes`);
        const taxSchemes = await tsRes.json();
        const tsSelect = document.getElementById('pilihanSkemaPajak');
        if (tsSelect) {
            tsSelect.innerHTML = '<option value="">-- Pilih Skema Pajak --</option>' +
                taxSchemes.map(s => `<option value="${s.id}">${s.nama} (${s.metode || '-'})</option>`).join('');
        }

        // Load global compensation schemes for dropdown
        const compRes = await fetch(`${API_URL}/compensation-schemes`);
        const compensationSchemes = await compRes.json();
        const compSelect = document.getElementById('pilihanSkemaKompensasi');
        if (compSelect) {
            compSelect.innerHTML = '<option value="">-- Pilih Skema Kompensasi --</option>' +
                compensationSchemes.map(s => `<option value="${s.id}">${s.nama}</option>`).join('');
        }

        // Load current client config to pre-select values
        const cfgRes = await fetch(`${API_URL}/client-configs`);
        const configs = await cfgRes.json();
        const conf = configs.find(c => c.client_id == window.selectedClientId);
        
        const summaryDiv = document.getElementById('pilihanKompensasiSummary');
        if (summaryDiv) {
            summaryDiv.innerHTML = `<p style="text-align: center; color: #94a3b8; font-size: 13px; margin: 0;">Pilih skema kompensasi di atas untuk melihat detailnya.</p>`;
        }

        if (conf) {
            const tipeSelect = document.getElementById('pilihanSkemaPayrollTipe');
            if (tipeSelect) {
                tipeSelect.value = conf.payroll_type || '';
                await handlePilihanSkemaPayrollTipeChange();
                
                if (conf.payroll_type === 'UMP' || conf.payroll_type === 'UMK') {
                    const wilSelect = document.getElementById('pilihanSkemaPayrollWilayah');
                    if (wilSelect && conf.minimum_wage_id) {
                        wilSelect.value = conf.minimum_wage_id;
                    }
                } else if (conf.payroll_type === 'Nominal') {
                    const nomInput = document.getElementById('pilihanSkemaPayrollNominal');
                    if (nomInput && conf.custom_nominal) {
                        nomInput.value = conf.custom_nominal;
                    }
                } else if (conf.payroll_type === 'Template') {
                    if (psSelect && conf.payroll_scheme_id) {
                        psSelect.value = conf.payroll_scheme_id;
                    }
                }
            }
            if (tsSelect && conf.tax_scheme_id) tsSelect.value = conf.tax_scheme_id;
            if (compSelect && conf.compensation_scheme_id) {
                compSelect.value = conf.compensation_scheme_id;
                renderPilihanKompensasiSummary(conf.compensation_scheme_id);
            }
        } else {
            const tipeSelect = document.getElementById('pilihanSkemaPayrollTipe');
            if (tipeSelect) {
                tipeSelect.value = '';
                handlePilihanSkemaPayrollTipeChange();
            }
        }
    } catch (err) {
        console.error('Error loading pilihan skema:', err);
    }
}

async function simpanPilihanSkema() {
    if (!window.selectedClientId) {
        showToast('Pilih klien terlebih dahulu!', 'error');
        return;
    }
    const payrollType = document.getElementById('pilihanSkemaPayrollTipe').value;
    const minimumWageId = document.getElementById('pilihanSkemaPayrollWilayah').value;
    const customNominal = document.getElementById('pilihanSkemaPayrollNominal').value;
    const payrollSchemeId = document.getElementById('pilihanSkemaPayroll').value;
    const taxSchemeId = document.getElementById('pilihanSkemaPajak').value;
    const compSchemeId = document.getElementById('pilihanSkemaKompensasi').value;

    if (!payrollType && !taxSchemeId && !compSchemeId) {
        showToast('Pilih minimal satu skema!', 'error');
        return;
    }

    try {
        // Load existing config to preserve pay_date and cutoff
        const cfgRes = await fetch(`${API_URL}/client-configs`);
        const configs = await cfgRes.json();
        const existing = configs.find(c => c.client_id == window.selectedClientId);

        const data = {
            client_id: window.selectedClientId,
            payroll_type: payrollType || null,
            minimum_wage_id: (payrollType === 'UMP' || payrollType === 'UMK') ? (minimumWageId || null) : null,
            custom_nominal: (payrollType === 'Nominal') ? (customNominal || null) : null,
            payroll_scheme_id: (payrollType === 'Template') ? (payrollSchemeId || null) : null,
            tax_scheme_id: taxSchemeId || (existing ? existing.tax_scheme_id : null),
            compensation_scheme_id: compSchemeId || (existing ? existing.compensation_scheme_id : null),
            pay_date: existing ? existing.pay_date : 25,
            cutoff_start: existing ? existing.cutoff_start : 21
        };

        const res = await fetch(`${API_URL}/client-configs`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (res.ok) {
            showToast('Pilihan skema berhasil disimpan!', 'success');
            // Also update the Setup Payroll tab data
            loadWorkspaceSetup();
        } else {
            showToast('Gagal menyimpan pilihan skema!', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Gagal menyimpan pilihan skema!', 'error');
    }
}

function goToMasterKompensasi() {
    switchView('masterKompensasi');
    renderMasterKompensasi();
}

// ===== MASTER SKEMA KOMPENSASI =====
async function renderMasterKompensasi() {
    try {
        const res = await fetch(`${API_URL}/compensation-schemes`);
        window.compensationSchemes = await res.json();
        const container = document.getElementById('compensationSchemesContainer');
        if (!container) return;
        
        if (window.compensationSchemes.length === 0) {
            container.innerHTML = `
                <div class="empty-schemes" style="grid-column: span 3; text-align: center; padding: 40px; border: 2px dashed #cbd5e1; border-radius: 12px; background: white;">
                    <i class="fas fa-coins" style="font-size: 40px; color: #94a3b8; margin-bottom: 12px; display: block;"></i>
                    <p style="color: #64748b; font-weight: 600; margin-bottom: 15px;">Belum ada skema kompensasi global.</p>
                    <button class="btn-add" onclick="bukaModalSkemaKompensasi('tambah')" style="margin: 0 auto; background: var(--primary-color);">
                        <i class="fas fa-plus"></i> Tambah Skema Pertama
                    </button>
                </div>
            `;
            return;
        }

        container.innerHTML = window.compensationSchemes.map(scheme => {
            const comps = scheme.components || [];
            const earningsCount = comps.filter(c => c.tipe === 'pendapatan').length;
            const deductionsCount = comps.filter(c => c.tipe === 'potongan').length;

            return `
                <div class="scheme-card" id="comp-scheme-card-${scheme.id}" style="margin-bottom: 20px;">
                    <div class="scheme-card-header" onclick="toggleSchemeCardBody(${scheme.id})">
                        <div class="scheme-card-info">
                            <h4><i class="fas fa-coins" style="color: #10b981;"></i> ${scheme.nama}</h4>
                            <div class="scheme-card-desc" style="margin-top: 5px; color: #64748b;">${scheme.deskripsi || 'Tidak ada deskripsi'}</div>
                            <div class="scheme-card-meta" style="margin-top: 10px; display: flex; gap: 15px;">
                                <span><i class="fas fa-plus-circle" style="color: #10b981;"></i> <strong class="meta-earning">${earningsCount} Pendapatan</strong></span>
                                <span><i class="fas fa-minus-circle" style="color: #ef4444;"></i> <strong class="meta-deduction">${deductionsCount} Potongan</strong></span>
                            </div>
                        </div>
                        <div class="scheme-card-actions" onclick="event.stopPropagation()">
                            <button class="btn-icon btn-edit" onclick="bukaModalSkemaKompensasi('edit', ${scheme.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon btn-delete" onclick="hapusSkemaKompensasi(${scheme.id})"><i class="fas fa-trash"></i></button>
                            <div class="scheme-toggle" id="comp-scheme-toggle-${scheme.id}" onclick="toggleSchemeCardBody(${scheme.id}); event.stopPropagation();"><i class="fas fa-chevron-down"></i></div>
                        </div>
                    </div>
                    <div class="scheme-card-body" id="comp-scheme-body-${scheme.id}" style="display: none; padding: 20px; border-top: 1px solid #f1f5f9; background: #fafbfc;">
                        <div class="scheme-card-body-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h5 style="margin: 0; font-size: 13px; font-weight: 600; color: #475569;">KOMPONEN KOMPENSASI</h5>
                            <button class="btn-add-komponen" onclick="bukaModalKomponenKompensasi(${scheme.id}, 'tambah')" style="padding: 6px 12px; font-size: 12px;">
                                <i class="fas fa-plus"></i> Tambah Komponen
                            </button>
                        </div>
                        <div class="component-list" style="display: flex; flex-direction: column; gap: 10px;">
                            ${comps.length === 0 ? `
                                <div class="empty-component" style="text-align: center; padding: 20px; color: #94a3b8; border: 2px dashed #cbd5e1; border-radius: 8px; background: white;">
                                    <i class="fas fa-info-circle"></i> Belum ada komponen dalam skema ini.
                                </div>
                            ` : comps.map(k => `
                                <div class="component-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-radius: 8px; background: white; border: 1px solid #e2e8f0;">
                                    <div class="component-item-left" style="display: flex; align-items: center; gap: 12px;">
                                        <div class="component-kategori-icon ${k.tipe === 'pendapatan' ? 'kat-insentif' : 'kat-absensi'}" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px;">
                                            <i class="fas ${k.tipe === 'pendapatan' ? 'fa-plus' : 'fa-minus'}"></i>
                                        </div>
                                        <div class="component-info" style="display: flex; flex-direction: column;">
                                            <span class="comp-name" style="font-weight: 600; font-size: 14px; color: #1e293b;">${k.nama}</span>
                                            <span class="comp-kategori" style="color: #64748b; font-weight: 500; font-size: 11px; display: flex; gap: 6px; align-items: center; flex-wrap: wrap; margin-top: 4px;">
                                                <span style="background: ${k.tipe === 'pendapatan' ? '#d1fae5' : '#fee2e2'}; color: ${k.tipe === 'pendapatan' ? '#065f46' : '#991b1b'}; padding: 1px 6px; border-radius: 4px; font-weight: 600; font-size: 10px;">${k.tipe.toUpperCase()}</span>
                                                <span style="background: #f1f5f9; padding: 1px 6px; border-radius: 4px;">${k.jenis_komponen === 'basic_salary' ? 'Basic Salary' : (k.sifat_kompensasi === 'tidak_tetap' ? 'Kompensasi Tidak Tetap' : 'Kompensasi Tetap')}</span>
                                                <span style="background: #f1f5f9; padding: 1px 6px; border-radius: 4px;">${k.sumber_nilai === 'ump_umk' ? 'Persentase UMP/UMK' : 'Nominal Custom'}</span>
                                                <span style="background: #f1f5f9; padding: 1px 6px; border-radius: 4px;">/ ${k.periode ? k.periode.toUpperCase() : 'BULAN'}</span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="component-item-right" style="display: flex; align-items: center; gap: 15px;">
                                        <div class="component-value ${k.tipe}" style="font-weight: 700; font-size: 14px; padding: 4px 10px; border-radius: 6px; background: ${k.tipe === 'pendapatan' ? '#d1fae5' : '#fee2e2'}; color: ${k.tipe === 'pendapatan' ? '#059669' : '#dc2626'};">
                                            ${k.sumber_nilai === 'ump_umk' ? k.nilai + '% UMP/UMK' : (k.is_persentase == 1 ? k.nilai + '%' : formatRupiah(k.nilai))}
                                        </div>
                                        <div class="component-item-actions" style="display: flex; gap: 5px;">
                                            <button class="btn-icon btn-edit" onclick="bukaModalKomponenKompensasi(${scheme.id}, 'edit', ${k.id})" style="width: 28px; height: 28px; background: var(--info); border-radius: 4px; border: none; color: white; cursor: pointer;"><i class="fas fa-edit" style="font-size: 11px;"></i></button>
                                            <button class="btn-icon btn-delete" onclick="hapusKomponenKompensasi(${k.id})" style="width: 28px; height: 28px; background: var(--danger); border-radius: 4px; border: none; color: white; cursor: pointer;"><i class="fas fa-trash" style="font-size: 11px;"></i></button>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    } catch (err) {
        console.error(err);
    }
}

function toggleSchemeCardBody(schemeId) {
    const body = document.getElementById(`comp-scheme-body-${schemeId}`);
    const toggle = document.getElementById(`comp-scheme-toggle-${schemeId}`);
    if (body) {
        body.classList.toggle('expanded');
        if (body.classList.contains('expanded')) {
            body.style.display = 'block';
            if (toggle) toggle.classList.add('expanded');
        } else {
            body.style.display = 'none';
            if (toggle) toggle.classList.remove('expanded');
        }
    }
}

function bukaModalSkemaKompensasi(mode, id = null) {
    document.getElementById('modalSkemaKompensasi').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    if (mode === 'edit' && id) {
        const scheme = window.compensationSchemes.find(s => s.id == id);
        if (scheme) {
            document.getElementById('modalSkemaKompensasiTitle').innerText = 'Edit Skema Kompensasi';
            document.getElementById('skemaKompensasiId').value = scheme.id;
            document.getElementById('skemaKompensasiNama').value = scheme.nama;
            document.getElementById('skemaKompensasiDeskripsi').value = scheme.deskripsi || '';
        }
    } else {
        document.getElementById('modalSkemaKompensasiTitle').innerText = 'Tambah Skema Kompensasi';
        document.getElementById('formSkemaKompensasi').reset();
        document.getElementById('skemaKompensasiId').value = '';
    }
}

function tutupModalSkemaKompensasi() {
    tutupSemuaModal();
}

async function hapusSkemaKompensasi(id) {
    if (!await showConfirm('Apakah Anda yakin ingin menghapus skema kompensasi ini beserta seluruh komponen di dalamnya?')) return;
    try {
        const res = await fetch(`${API_URL}/compensation-schemes/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderMasterKompensasi();
            showToast('Skema kompensasi berhasil dihapus!', 'success');
        } else {
            showToast('Gagal menghapus skema!', 'error');
        }
    } catch (err) {
        console.error(err);
    }
}

function handleJenisKomponenChange() {
    const jenis = document.getElementById('komponenKompensasiJenis').value;
    const sifatContainer = document.getElementById('containerSifatKompensasi');
    if (jenis === 'kompensasi') {
        if (sifatContainer) sifatContainer.style.display = 'block';
    } else {
        if (sifatContainer) sifatContainer.style.display = 'none';
    }
}

function handleSumberNilaiChange() {
    const sumber = document.getElementById('komponenKompensasiSumber').value;
    const formatContainer = document.getElementById('containerFormatNilai');
    const labelNilai = document.getElementById('labelNilaiKompensasi');
    const inputNilai = document.getElementById('komponenKompensasiNilai');
    const selectIsPersentase = document.getElementById('komponenKompensasiIsPersentase');

    if (sumber === 'ump_umk') {
        if (formatContainer) formatContainer.style.display = 'none';
        if (selectIsPersentase) selectIsPersentase.value = '1';
        if (labelNilai) labelNilai.innerText = 'Nilai Persentase (%) dari UMP/UMK';
        if (inputNilai) inputNilai.placeholder = 'Contoh: 100';
    } else {
        if (formatContainer) formatContainer.style.display = 'none';
        if (selectIsPersentase) selectIsPersentase.value = '0';
        if (labelNilai) labelNilai.innerText = 'Nominal Custom (Rp)';
        if (inputNilai) inputNilai.placeholder = 'Contoh: 5000000';
    }
}

function bukaModalKomponenKompensasi(schemeId, mode, id = null) {
    document.getElementById('modalKomponenKompensasi').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('komponenKompensasiSchemeId').value = schemeId;
    if (mode === 'edit' && id) {
        const scheme = window.compensationSchemes.find(s => s.id == schemeId);
        const k = scheme ? (scheme.components || []).find(comp => comp.id == id) : null;
        if (k) {
            document.getElementById('modalKomponenKompensasiTitle').innerText = 'Edit Komponen Kompensasi';
            document.getElementById('komponenKompensasiId').value = k.id;
            const nameEl = document.getElementById('komponenKompensasiNama');
            if (nameEl) nameEl.value = k.nama;
            const tipeEl = document.getElementById('komponenKompensasiTipe');
            if (tipeEl) tipeEl.value = k.tipe;
            document.getElementById('komponenKompensasiNilai').value = k.nilai;
            document.getElementById('komponenKompensasiIsPersentase').value = (k.is_persentase == 1 || k.is_persentase === true || k.is_persentase === '1') ? '1' : '0';
            
            // New fields
            document.getElementById('komponenKompensasiJenis').value = k.jenis_komponen || 'kompensasi';
            document.getElementById('komponenKompensasiSifat').value = k.sifat_kompensasi || 'tetap';
            document.getElementById('komponenKompensasiSumber').value = k.sumber_nilai || 'nominal';
            document.getElementById('komponenKompensasiPeriode').value = k.periode || 'bulan';
        }
    } else {
        document.getElementById('modalKomponenKompensasiTitle').innerText = 'Tambah Komponen Kompensasi';
        document.getElementById('formKomponenKompensasi').reset();
        document.getElementById('komponenKompensasiId').value = '';
        document.getElementById('komponenKompensasiSchemeId').value = schemeId;
        
        // Defaults
        document.getElementById('komponenKompensasiJenis').value = 'kompensasi';
        document.getElementById('komponenKompensasiSifat').value = 'tetap';
        document.getElementById('komponenKompensasiSumber').value = 'nominal';
        document.getElementById('komponenKompensasiPeriode').value = 'bulan';
        document.getElementById('komponenKompensasiIsPersentase').value = '0';
    }
    
    // Trigger handlers to update visibility/labels
    handleJenisKomponenChange();
    handleSumberNilaiChange();
}

function tutupModalKomponenKompensasi() {
    tutupSemuaModal();
}

async function hapusKomponenKompensasi(id) {
    if (!await showConfirm('Apakah Anda yakin ingin menghapus komponen kompensasi ini?')) return;
    try {
        const res = await fetch(`${API_URL}/compensation-components/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderMasterKompensasi();
            showToast('Komponen kompensasi berhasil dihapus!', 'success');
        } else {
            showToast('Gagal menghapus komponen!', 'error');
        }
    } catch (err) {
        console.error(err);
    }
}

// ===== 9. KONFIGURASI ABSEN =====
async function loadAbsenConfig() {
    if (!window.selectedClientId) return;
    try {
        const res = await fetch(`${API_URL}/client-absence-config/${window.selectedClientId}`);
        const data = await res.json();
        if (data && data.id) {
            document.getElementById('cfgProrate').checked = data.prorate == 1;
            document.getElementById('cfgAbsenTidakPotong').checked = data.absen_tidak_potong == 1;
        } else {
            document.getElementById('cfgProrate').checked = false;
            document.getElementById('cfgAbsenTidakPotong').checked = false;
        }
    } catch (err) { console.error(err); }
}

async function simpanKonfigAbsen() {
    if (!window.selectedClientId) return;
    const data = {
        client_id: window.selectedClientId,
        prorate: document.getElementById('cfgProrate').checked ? 1 : 0,
        absen_tidak_potong: document.getElementById('cfgAbsenTidakPotong').checked ? 1 : 0
    };
    try {
        const res = await fetch(`${API_URL}/client-absence-config`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (res.ok) { showToast('Konfigurasi absen berhasil disimpan!', 'success'); }
    } catch (err) { console.error(err); showToast('Gagal menyimpan konfigurasi', 'error'); }
}

// ===== HAPUS SKEMA PAYROLL & PAJAK =====
async function hapusSkema(id) {
    if (!await showConfirm('Apakah Anda yakin ingin menghapus skema payroll ini?')) return;
    try {
        const res = await fetch(`${API_URL}/payroll-schemes/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderPayrollSchemes();
            showToast('Skema payroll berhasil dihapus!', 'success');
        } else {
            showToast('Gagal menghapus skema payroll!', 'error');
        }
    } catch (err) { console.error(err); }
}

async function hapusPajak(id) {
    if (!await showConfirm('Apakah Anda yakin ingin menghapus skema pajak ini?')) return;
    try {
        const res = await fetch(`${API_URL}/tax-schemes/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderTaxSchemes();
            showToast('Skema pajak berhasil dihapus!', 'success');
        } else {
            showToast('Gagal menghapus skema pajak!', 'error');
        }
    } catch (err) { console.error(err); }
}

// ===== SIDEBAR TOGGLE =====
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
}

// Expose to window
Object.assign(window, {
    switchView, logout, bukaModal, tutupModal, hapusKlien, 
    bukaModalPajak, renderTaxSchemes, renderPayrollSchemes,
    bukaModalSetup, renderClientSetup,
    bukaModalPKWT, renderPKWTTable, hapusPKWT,
    bukaModalPeriode, generateGaji, approveGaji,
    bukaSlipGaji, tutupModalSlip, cetakSlip,
    switchUmrTab, filterUmrTable, bukaModalUploadUmr, tutupModalUploadUmr,
    handleUmrSelectChange, bukaModalManualUmr, tutupModalManualUmr,
    loadSimulasiRegions, hitungSimulasiGaji,
    downloadTemplateUmr, goUmrPage,
    tutupSemuaModal, toggleSidebar,
    bukaModalSkema, bukaModalKomponen, bukaModalOrg, bukaModalCutOff,
    tutupModalSkema, tutupModalKomponen, tutupModalPajak, tutupModalSetup, 
    tutupModalPKWT, tutupModalPeriode, tutupModalCutOff,
    hapusSkema, hapusPajak,
    selectClient, backToClientList, switchWorkspaceTab, loadWorkspaceSetup,
    loadAbsenConfig, simpanKonfigAbsen,
    renderUmrTable,
    renderMasterKompensasi, bukaModalSkemaKompensasi, tutupModalSkemaKompensasi, hapusSkemaKompensasi,
    bukaModalKomponenKompensasi, tutupModalKomponenKompensasi, hapusKomponenKompensasi, toggleSchemeCardBody,
    renderPilihanKompensasiSummary, loadPilihanSkema, simpanPilihanSkema,
    handleJenisKomponenChange, handleSumberNilaiChange
});

function bukaModalSkema(mode, id = null) {
    document.getElementById('modalSkema').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    if(mode === 'edit' && id) {
        const s = payrollSchemes.find(x => x.id == id);
        if(s) {
            document.getElementById('modalSkemaTitle').innerText = 'Edit Skema Payroll';
            document.getElementById('skemaId').value = s.id;
            document.getElementById('skemaNama').value = s.nama;
            document.getElementById('skemaDeskripsi').value = s.deskripsi;
            document.getElementById('skemaTipe').value = s.tipe;
        }
    } else {
        document.getElementById('modalSkemaTitle').innerText = 'Tambah Skema Payroll';
        document.getElementById('formSkema').reset();
        document.getElementById('skemaId').value = '';
    }
}

function bukaModalKomponen(schemeId) {
    document.getElementById('modalKomponen').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('formKomponen').reset();
    document.getElementById('komponenSchemeId').value = schemeId;
}

function bukaModalOrg(type, mode, id = null) {
    document.getElementById('modalOrg').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('orgType').value = type;
}

function bukaModalPeriode() {
    document.getElementById('modalPeriode').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function bukaModalCutOff(pkwtId, empName) {
    document.getElementById('modalCutOff').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('cutoffPkwtId').value = pkwtId;
    document.getElementById('cutoffEmployeeName').value = empName;
}

// Global Closing Handlers
function tutupModal() { tutupSemuaModal(); }
function tutupModalSkema() { tutupSemuaModal(); }
function tutupModalKomponen() { tutupSemuaModal(); }
function tutupModalPajak() { tutupSemuaModal(); }
function tutupModalSetup() { tutupSemuaModal(); }
function tutupModalPKWT() { tutupSemuaModal(); }
function tutupModalPeriode() { tutupSemuaModal(); }
function tutupModalCutOff() { tutupSemuaModal(); }

// ===== UMP / UMK MODULE =====

let currentUmrType = 'UMP';
let umrFilteredData = [];
let umrCurrentPage = 1;
const UMR_PER_PAGE = 10;

function formatNominal(val) {
    return new Intl.NumberFormat('id-ID').format(val || 0);
}

async function renderUmrTable() {
    try {
        const tipe = document.getElementById('selectUmrType')?.value || currentUmrType;
        const tahun = document.getElementById('selectUmrYear')?.value || new Date().getFullYear();
        
        const response = await fetch(`${API_URL}/minimum-wages?tipe=${tipe}&tahun=${tahun}`);
        umrAllData = await response.json();
        
        currentUmrType = tipe;

        const tabUmp = document.getElementById('tabUmp');
        const tabUmk = document.getElementById('tabUmk');
        const tabNominal = document.getElementById('tabNominal');
        const tableArea = document.getElementById('umrTableArea');
        const nominalArea = document.getElementById('umrNominalArea');

        // Reset all tabs to inactive style
        const resetTabs = () => {
            [tabUmp, tabUmk, tabNominal].forEach(tab => {
                if (tab) {
                    tab.className = 'umr-tab-btn';
                    tab.style.background = 'transparent';
                    tab.style.border = '1px solid transparent';
                    tab.style.borderBottom = 'none';
                    tab.style.color = '#0d6efd';
                    tab.style.zIndex = '1';
                }
            });
        };

        const setActiveTab = (tab) => {
            if (tab) {
                tab.className = 'umr-tab-btn active';
                tab.style.background = 'white';
                tab.style.border = '1px solid #ddd';
                tab.style.borderBottom = '1px solid white';
                tab.style.zIndex = '2';
            }
        };

        resetTabs();

        if (tipe === 'NOMINAL') {
            setActiveTab(tabNominal);
            if (tableArea) tableArea.style.display = 'none';
            if (nominalArea) nominalArea.style.display = 'block';
        } else {
            if (tableArea) tableArea.style.display = 'block';
            if (nominalArea) nominalArea.style.display = 'none';
            
            if (tipe === 'UMP') {
                setActiveTab(tabUmp);
                const searchEl = document.getElementById('searchUmr');
                if (searchEl) searchEl.placeholder = 'Cari Provinsi...';
            } else {
                setActiveTab(tabUmk);
                const searchEl = document.getElementById('searchUmr');
                if (searchEl) searchEl.placeholder = 'Cari Kota/Kabupaten...';
            }

            // Update thead dynamically to match exact formats from UMP/UMK screenshots
            const thead = document.getElementById('tabelUmrBody')?.previousElementSibling;
            if (thead) {
                if (tipe === 'UMP') {
                    thead.innerHTML = `
                        <tr>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">StateId</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">StateCode</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">StateName</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">umr_amount</th>
                        </tr>
                    `;
                } else {
                    thead.innerHTML = `
                        <tr>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">RegencyId</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">RegencyCode</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">RegencyName</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">StateId</th>
                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">umr_amount</th>
                        </tr>
                    `;
                }
            }
        }
        
        // If nominal, we don't need to fetch or render the table data
        if (tipe === 'NOMINAL') return;
        
        // Dynamically populate searchUmr select options
        const searchEl = document.getElementById('searchUmr');
        if (searchEl && searchEl.tagName === 'SELECT') {
            const prevVal = searchEl.value;
            let optionsHtml = '';
            
            if (tipe === 'UMP') {
                optionsHtml += '<option value="">-- Pilih Provinsi --</option>';
                const uniqueProvinces = [...new Set(umrAllData.map(row => row.nama_daerah))].sort();
                uniqueProvinces.forEach(prov => {
                    optionsHtml += `<option value="${prov}" ${prov === prevVal ? 'selected' : ''}>${prov}</option>`;
                });
            } else {
                optionsHtml += '<option value="">-- Pilih Kota/Kabupaten --</option>';
                const uniqueRegencies = [...new Set(umrAllData.map(row => row.nama_daerah))].sort();
                uniqueRegencies.forEach(reg => {
                    optionsHtml += `<option value="${reg}" ${reg === prevVal ? 'selected' : ''}>${reg}</option>`;
                });
            }
            searchEl.innerHTML = optionsHtml;
        }

        // Apply search filter
        const q = (document.getElementById('searchUmr')?.value || '').toLowerCase();
        umrFilteredData = q 
            ? umrAllData.filter(row => row.nama_daerah.toLowerCase().includes(q) || row.kode_daerah.toLowerCase().includes(q))
            : [...umrAllData];

        renderUmrPage();
    } catch (err) { console.error(err); }
}

function renderUmrPage() {
    const tbody = document.getElementById('tabelUmrBody');
    if (!tbody) return;

    const totalData = umrFilteredData.length;
    const totalPages = Math.max(1, Math.ceil(totalData / UMR_PER_PAGE));
    
    // Clamp page
    if (umrCurrentPage > totalPages) umrCurrentPage = totalPages;
    if (umrCurrentPage < 1) umrCurrentPage = 1;

    const start = (umrCurrentPage - 1) * UMR_PER_PAGE;
    const end = Math.min(start + UMR_PER_PAGE, totalData);
    const pageData = umrFilteredData.slice(start, end);

    const stateIdMap = {
        'ID 11': 5, 'ID 12': 6, 'ID 17': 7, 'ID 15': 8, 'ID 14': 9, 'ID 13': 10, 'ID 16': 11,
        'ID 18': 12, 'ID 19': 13, 'ID 21': 14, 'ID 36': 15, 'ID 32': 16, 'ID 31': 17, 'ID 33': 18,
        'ID 35': 19, 'ID 34': 20, 'ID 51': 21, 'ID 52': 22, 'ID 53': 23, 'ID 61': 24, 'ID 63': 25,
        'ID 62': 26, 'ID 64': 27, 'ID 75': 28, 'ID 73': 29, 'ID 74': 30, 'ID 72': 31, 'ID 71': 32,
        'ID 76': 33, 'ID 81': 34, 'ID 82': 35, 'ID 91': 36, 'ID 92': 37, 'ID 65': 45
    };

    if (pageData.length > 0) {
        let index = start + 1;
        tbody.innerHTML = pageData.map(row => {
            if (currentUmrType === 'UMP') {
                const stateId = stateIdMap[row.kode_daerah] || (row.provinsi || index++);
                return `
                    <tr>
                        <td class="td-code">${stateId}</td>
                        <td class="td-code">${row.kode_daerah}</td>
                        <td class="td-name">${row.nama_daerah}</td>
                        <td class="td-nominal">${formatNominal(row.nominal)}</td>
                    </tr>
                `;
            } else {
                const regencyId = index++;
                const prefix = row.kode_daerah.split('.')[0] || '';
                const stateId = stateIdMap[prefix] || (row.provinsi || 17);
                return `
                    <tr>
                        <td class="td-code">${regencyId}</td>
                        <td class="td-code">${row.kode_daerah}</td>
                        <td class="td-name">${row.nama_daerah}</td>
                        <td class="td-code">${stateId}</td>
                        <td class="td-nominal">${formatNominal(row.nominal)}</td>
                    </tr>
                `;
            }
        }).join('');
    } else {
        const colSpan = currentUmrType === 'UMP' ? 4 : 5;
        tbody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center; padding:40px; color:#aaa;">
                <i class="fas fa-database" style="font-size:28px; margin-bottom:10px; display:block;"></i>
                Belum ada data ${currentUmrType}. Klik <b>Upload</b> untuk menambah data.
           </td></tr>`;
    }

    // Update pagination info
    const infoEl = document.getElementById('umrPaginationInfo');
    if (infoEl) {
        infoEl.innerText = totalData > 0
            ? `Menampilkan ${start + 1} - ${end} dari ${totalData} data`
            : 'Tidak ada data';
    }

    // Render pagination controls
    const controls = document.getElementById('umrPaginationControls');
    if (controls) {
        let html = '';
        // Prev button
        html += `<button ${umrCurrentPage <= 1 ? 'disabled' : ''} onclick="goUmrPage(${umrCurrentPage - 1})"><i class="fas fa-chevron-left"></i></button>`;
        
        // Page numbers
        const maxVisible = 4;
        let pageStart = Math.max(1, umrCurrentPage - Math.floor(maxVisible / 2));
        let pageEnd = Math.min(totalPages, pageStart + maxVisible - 1);
        if (pageEnd - pageStart < maxVisible - 1) pageStart = Math.max(1, pageEnd - maxVisible + 1);

        for (let i = pageStart; i <= pageEnd; i++) {
            html += `<button class="${i === umrCurrentPage ? 'active' : ''}" onclick="goUmrPage(${i})">${i}</button>`;
        }
        
        // Next button
        html += `<button ${umrCurrentPage >= totalPages ? 'disabled' : ''} onclick="goUmrPage(${umrCurrentPage + 1})"><i class="fas fa-chevron-right"></i></button>`;
        
        controls.innerHTML = html;
    }
}

function goUmrPage(page) {
    umrCurrentPage = page;
    renderUmrPage();
}

function switchUmrTab(tipe) {
    currentUmrType = tipe;
    umrCurrentPage = 1;

    const selectType = document.getElementById('selectUmrType');
    if (selectType) selectType.value = tipe;

    // Reset search
    const search = document.getElementById('searchUmr');
    if (search) search.value = '';

    renderUmrTable();
}

function filterUmrTable() {
    umrCurrentPage = 1;
    const q = (document.getElementById('searchUmr')?.value || '').toLowerCase();
    umrFilteredData = q
        ? umrAllData.filter(row => row.nama_daerah.toLowerCase().includes(q) || row.kode_daerah.toLowerCase().includes(q))
        : [...umrAllData];
    renderUmrPage();
}

function bukaModalUploadUmr() {
    document.getElementById('modalUploadUmr').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('uploadUmrTipe').value = currentUmrType;
    // Reset file input
    const fileInput = document.getElementById('fileUmr');
    if (fileInput) fileInput.value = '';
    const fileNameEl = document.getElementById('umrFileName');
    if (fileNameEl) fileNameEl.style.display = 'none';
}

function tutupModalUploadUmr() {
    document.getElementById('modalUploadUmr').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function handleUmrSelectChange(val) {
    if (val === 'MANUAL') {
        // Balikkan dropdown ke pilihan sebelumnya agar filter tidak rusak
        document.getElementById('selectUmrType').value = currentUmrType;
        bukaModalManualUmr();
    } else {
        switchUmrTab(val);
    }
}

function bukaModalManualUmr() {
    document.getElementById('modalManualUmr').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('manualUmrTipe').value = currentUmrType;
    document.getElementById('formManualUmr').reset();
    document.getElementById('manualUmrTipe').value = currentUmrType;
}

function tutupModalManualUmr() {
    document.getElementById('modalManualUmr').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

// CSV Download Template
function downloadTemplateUmr() {
    const tipe = currentUmrType;
    let csvContent = '';
    
    if (tipe === 'UMP') {
        csvContent = 'StateId,StateCode,StateName,umr_amount\n';
    } else {
        csvContent = 'RegencyId,RegencyCode,RegencyName,StateId,umr_amount\n';
    }

    const stateIdMap = {
        'ID 11': 5, 'ID 12': 6, 'ID 17': 7, 'ID 15': 8, 'ID 14': 9, 'ID 13': 10, 'ID 16': 11,
        'ID 18': 12, 'ID 19': 13, 'ID 21': 14, 'ID 36': 15, 'ID 32': 16, 'ID 31': 17, 'ID 33': 18,
        'ID 35': 19, 'ID 34': 20, 'ID 51': 21, 'ID 52': 22, 'ID 53': 23, 'ID 61': 24, 'ID 63': 25,
        'ID 62': 26, 'ID 64': 27, 'ID 75': 28, 'ID 73': 29, 'ID 74': 30, 'ID 72': 31, 'ID 71': 32,
        'ID 76': 33, 'ID 81': 34, 'ID 82': 35, 'ID 91': 36, 'ID 92': 37, 'ID 65': 45
    };

    // Jika ada data di tabel, masukkan data tersebut ke CSV
    if (umrAllData && umrAllData.length > 0) {
        let idCounter = 1;
        umrAllData.forEach(row => {
            if (tipe === 'UMP') {
                const stateId = stateIdMap[row.kode_daerah] || (row.provinsi || idCounter++);
                csvContent += `${stateId},${row.kode_daerah},${row.nama_daerah},${row.nominal}\n`;
            } else {
                const regencyId = idCounter++;
                const prefix = row.kode_daerah.split('.')[0] || '';
                const stateId = stateIdMap[prefix] || (row.provinsi || 17);
                csvContent += `${regencyId},${row.kode_daerah},${row.nama_daerah},${stateId},${row.nominal}\n`;
            }
        });
    } else {
        // Fallback ke data contoh jika tabel kosong
        if (tipe === 'UMP') {
            const defaultUmpData = [
                {code: 'ID 11', name: 'ACEH', nominal: 3000000},
                {code: 'ID 12', name: 'SUMATERA UTARA', nominal: 4000000},
                {code: 'ID 17', name: 'BENGKULU', nominal: 2000000},
                {code: 'ID 15', name: 'JAMBI', nominal: 2000000},
                {code: 'ID 14', name: 'RIAU', nominal: 2000000},
                {code: 'ID 13', name: 'SUMATERA BARAT', nominal: 2000000},
                {code: 'ID 16', name: 'SUMATERA SELATAN', nominal: 2000000},
                {code: 'ID 18', name: 'LAMPUNG', nominal: 2000000},
                {code: 'ID 19', name: 'KEP. BANGKA BELITUNG', nominal: 2000000},
                {code: 'ID 21', name: 'KEP. RIAU', nominal: 2000000},
                {code: 'ID 36', name: 'BANTEN', nominal: 2000000},
                {code: 'ID 32', name: 'JAWA BARAT', nominal: 2000000},
                {code: 'ID 31', name: 'DKI JAKARTA', nominal: 2000000},
                {code: 'ID 33', name: 'JAWA TENGAH', nominal: 2000000},
                {code: 'ID 35', name: 'JAWA TIMUR', nominal: 2000000},
                {code: 'ID 34', name: 'DI YOGYAKARTA', nominal: 2000000},
                {code: 'ID 51', name: 'BALI', nominal: 2000000},
                {code: 'ID 52', name: 'NUSA TENGGARA BARAT', nominal: 2000000},
                {code: 'ID 53', name: 'NUSA TENGGARA TIMUR', nominal: 2000000},
                {code: 'ID 61', name: 'KALIMANTAN BARAT', nominal: 2000000},
                {code: 'ID 63', name: 'KALIMANTAN SELATAN', nominal: 2000000},
                {code: 'ID 62', name: 'KALIMANTAN TENGAH', nominal: 2000000},
                {code: 'ID 64', name: 'KALIMANTAN TIMUR', nominal: 2000000},
                {code: 'ID 75', name: 'GORONTALO', nominal: 2000000},
                {code: 'ID 73', name: 'SULAWESI SELATAN', nominal: 2000000},
                {code: 'ID 74', name: 'SULAWESI TENGGARA', nominal: 2000000},
                {code: 'ID 72', name: 'SULAWESI TENGAH', nominal: 2000000},
                {code: 'ID 71', name: 'SULAWESI UTARA', nominal: 2000000},
                {code: 'ID 76', name: 'SULAWESI BARAT', nominal: 2000000},
                {code: 'ID 81', name: 'MALUKU', nominal: 2000000},
                {code: 'ID 82', name: 'MALUKU UTARA', nominal: 2000000},
                {code: 'ID 91', name: 'PAPUA', nominal: 1000000},
                {code: 'ID 92', name: 'PAPUA BARAT', nominal: 2000000},
                {code: 'ID 65', name: 'KALIMANTAN UTARA', nominal: 2000000}
            ];
            
            defaultUmpData.forEach(row => {
                const stateId = stateIdMap[row.code] || 17;
                csvContent += `${stateId},${row.code},${row.name},${row.nominal}\n`;
            });
        } else {
            const defaultUmkData = [
                {code: 'ID 11.01', name: 'KAB. ACEH BARAT', nominal: 2000000},
                {code: 'ID 11.02', name: 'KAB. ACEH BARAT DAYA', nominal: 2000000},
                {code: 'ID 11.03', name: 'KAB. ACEH BESAR', nominal: 2000000},
                {code: 'ID 31.71', name: 'KOTA JAKARTA PUSAT', nominal: 5000000},
                {code: 'ID 32.71', name: 'KOTA BOGOR', nominal: 4500000},
                {code: 'ID 32.73', name: 'KOTA BANDUNG', nominal: 4200000}
            ];
            
            let regId = 1;
            defaultUmkData.forEach(row => {
                const prefix = row.code.split('.')[0] || '';
                const stateId = stateIdMap[prefix] || 17;
                csvContent += `${regId++},${row.code},${row.name},${stateId},${row.nominal}\n`;
            });
        }
    }

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `template_${tipe.toLowerCase()}_${new Date().getFullYear()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    showToast(`Template ${tipe} berhasil diunduh!`, 'success');
}

// Drag & Drop + File Input Handling
document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.getElementById('umrDropZone');
    const fileInput = document.getElementById('fileUmr');
    
    if (fileInput) {
        // Prevent click bubble to dropZone
        fileInput.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const fileNameEl = document.getElementById('umrFileName');
                if (fileNameEl) {
                    fileNameEl.innerText = `📎 ${file.name}`;
                    fileNameEl.style.display = 'block';
                }
            }
        });
    }

    if (dropZone) {
        // Trigger file dialog on click
        dropZone.addEventListener('click', () => {
            fileInput.click();
        });

        ['dragenter', 'dragover'].forEach(evt => {
            dropZone.addEventListener(evt, (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropZone.addEventListener(evt, (e) => { e.preventDefault(); dropZone.classList.remove('drag-over'); });
        });
        dropZone.addEventListener('drop', (e) => {
            const file = e.dataTransfer.files[0];
            if (file && file.name.endsWith('.csv')) {
                fileInput.files = e.dataTransfer.files;
                const fileNameEl = document.getElementById('umrFileName');
                if (fileNameEl) {
                    fileNameEl.innerText = `📎 ${file.name}`;
                    fileNameEl.style.display = 'block';
                }
            } else {
                showToast('Hanya file CSV yang diperbolehkan!', 'error');
            }
        });
    }
});

// CSV Upload Handler
const formUploadUmr = document.getElementById('formUploadUmr');
if (formUploadUmr) {
    formUploadUmr.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const fileInput = document.getElementById('fileUmr');
        const file = fileInput.files[0];
        
        if (!file) {
            showToast('Pilih file CSV terlebih dahulu!', 'error');
            return;
        }

        showToast('Membaca dan mengupload data...', 'info');

        const reader = new FileReader();
        reader.onload = async (event) => {
            try {
                const csvText = event.target.result;
                const lines = csvText.split(/\r?\n/).filter(line => line.trim() !== '');
                
                // Skip header row
                const dataLines = lines.slice(1);
                
                if (dataLines.length === 0) {
                    showToast('File CSV kosong!', 'error');
                    return;
                }

                const tipe = document.getElementById('uploadUmrTipe').value;
                const tahun = document.getElementById('uploadUmrTahun').value;
                
                // Dynamically detect column indices based on header names for extreme robustness
                const headerLine = lines[0];
                const headers = headerLine.split(',').map(h => h.trim().replace(/^"|"$/g, ''));
                
                let codeIdx = -1;
                let nameIdx = -1;
                let nominalIdx = -1;
                let stateIdIdx = -1;

                headers.forEach((h, idx) => {
                    const cleanH = h.toLowerCase();
                    if (cleanH.includes('code') || cleanH.includes('kode')) {
                        codeIdx = idx;
                    } else if (cleanH.includes('name') || cleanH.includes('daerah') || cleanH.includes('kabupaten') || cleanH.includes('provinsi')) {
                        if (!cleanH.includes('kode')) {
                            nameIdx = idx;
                        }
                    } else if (cleanH.includes('amount') || cleanH.includes('nominal') || cleanH.includes('gaji')) {
                        nominalIdx = idx;
                    } else if (cleanH === 'stateid' || cleanH === 'provinsi_id') {
                        stateIdIdx = idx;
                    }
                });

                // Fallbacks if not auto-detected by headers
                if (codeIdx === -1) {
                    if (tipe === 'UMP') {
                        codeIdx = headers.length >= 4 ? 1 : 0;
                        nameIdx = headers.length >= 4 ? 2 : 1;
                        nominalIdx = headers.length >= 4 ? 3 : 2;
                        stateIdIdx = headers.length >= 4 ? 0 : -1;
                    } else {
                        codeIdx = headers.length >= 5 ? 1 : 0;
                        nameIdx = headers.length >= 5 ? 2 : 1;
                        stateIdIdx = headers.length >= 5 ? 3 : -1;
                        nominalIdx = headers.length >= 5 ? 4 : 2;
                    }
                }
                
                const items = dataLines.map(line => {
                    // Handle CSV with commas inside quotes
                    const cols = line.split(',').map(c => c.trim().replace(/^"|"$/g, ''));
                    let rawNominal = cols[nominalIdx] || '0';
                    rawNominal = rawNominal.trim();
                    
                    let nominalVal = 0;
                    if (rawNominal.includes('.') && rawNominal.includes(',')) {
                        if (rawNominal.lastIndexOf('.') > rawNominal.lastIndexOf(',')) {
                            nominalVal = parseFloat(rawNominal.replace(/,/g, '')) || 0;
                        } else {
                            nominalVal = parseFloat(rawNominal.replace(/\./g, '').replace(/,/g, '.')) || 0;
                        }
                    } else if (rawNominal.includes(',')) {
                        const parts = rawNominal.split(',');
                        if (parts.length === 2 && parts[1].length === 2) {
                            nominalVal = parseFloat(rawNominal.replace(/,/g, '.')) || 0;
                        } else {
                            nominalVal = parseFloat(rawNominal.replace(/,/g, '')) || 0;
                        }
                    } else if (rawNominal.includes('.')) {
                        const parts = rawNominal.split('.');
                        if (parts.length > 2 || (parts.length === 2 && parts[1].length === 3)) {
                            nominalVal = parseFloat(rawNominal.replace(/\./g, '')) || 0;
                        } else {
                            nominalVal = parseFloat(rawNominal) || 0;
                        }
                    } else {
                        nominalVal = parseFloat(rawNominal) || 0;
                    }

                    const provinceVal = stateIdIdx !== -1 ? (cols[stateIdIdx] || '') : '';
                    
                    return {
                        tipe: tipe,
                        kode_daerah: cols[codeIdx] || '',
                        nama_daerah: cols[nameIdx] || '',
                        provinsi: provinceVal,
                        nominal: nominalVal,
                        tahun: parseInt(tahun)
                    };
                }).filter(item => item.kode_daerah && item.nama_daerah);

                const res = await fetch(`${API_URL}/minimum-wages`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items: items })
                });

                if (res.ok) {
                    tutupModalUploadUmr();
                    umrCurrentPage = 1;
                    renderUmrTable();
                    showToast(`${items.length} data ${tipe} berhasil di-upload!`, 'success');
                } else {
                    showToast('Gagal mengupload data!', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Error saat memproses file CSV!', 'error');
            }
        };
        reader.readAsText(file);
    });
}

// Manual UMR Form Handler
const formManualUmr = document.getElementById('formManualUmr');
if (formManualUmr) {
    formManualUmr.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = {
            items: [{
                tipe: document.getElementById('manualUmrTipe').value,
                kode_daerah: document.getElementById('manualUmrKode').value,
                nama_daerah: document.getElementById('manualUmrNama').value,
                nominal: parseFloat(document.getElementById('manualUmrNominal').value) || 0,
                tahun: parseInt(document.getElementById('manualUmrTahun').value)
            }]
        };

        try {
            const res = await fetch(`${API_URL}/minimum-wages`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (res.ok) {
                tutupModalManualUmr();
                renderUmrTable();
                showToast('Data berhasil disimpan secara manual!', 'success');
            } else {
                showToast('Gagal menyimpan data!', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Terjadi kesalahan sistem!', 'error');
        }
    });
}

// ===== SIMULASI GAJI MODULE =====

async function loadSimulasiRegions() {
    const type = document.getElementById('simulasiType').value;
    try {
        const res = await fetch(`${API_URL}/minimum-wages?tipe=${type}`);
        let dbData = await res.json();
        
        // Data Default 38 Provinsi (Update 2026 Projection)
        const defaultUmp = [
            {id: 'ID 11', nama_daerah: 'ACEH', nominal: 3000000},
            {id: 'ID 12', nama_daerah: 'SUMATERA UTARA', nominal: 4000000},
            {id: 'ID 17', nama_daerah: 'BENGKULU', nominal: 2000000},
            {id: 'ID 15', nama_daerah: 'JAMBI', nominal: 2000000},
            {id: 'ID 14', nama_daerah: 'RIAU', nominal: 2000000},
            {id: 'ID 13', nama_daerah: 'SUMATERA BARAT', nominal: 2000000},
            {id: 'ID 16', nama_daerah: 'SUMATERA SELATAN', nominal: 2000000},
            {id: 'ID 18', nama_daerah: 'LAMPUNG', nominal: 2000000},
            {id: 'ID 19', nama_daerah: 'KEP. BANGKA BELITUNG', nominal: 2000000},
            {id: 'ID 21', nama_daerah: 'KEP. RIAU', nominal: 2000000},
            {id: 'ID 36', nama_daerah: 'BANTEN', nominal: 2000000},
            {id: 'ID 32', nama_daerah: 'JAWA BARAT', nominal: 2000000},
            {id: 'ID 31', nama_daerah: 'DKI JAKARTA', nominal: 2000000},
            {id: 'ID 33', nama_daerah: 'JAWA TENGAH', nominal: 2000000},
            {id: 'ID 35', nama_daerah: 'JAWA TIMUR', nominal: 2000000},
            {id: 'ID 34', nama_daerah: 'DI YOGYAKARTA', nominal: 2000000},
            {id: 'ID 51', nama_daerah: 'BALI', nominal: 2000000},
            {id: 'ID 52', nama_daerah: 'NUSA TENGGARA BARAT', nominal: 2000000},
            {id: 'ID 53', nama_daerah: 'NUSA TENGGARA TIMUR', nominal: 2000000},
            {id: 'ID 61', nama_daerah: 'KALIMANTAN BARAT', nominal: 2000000},
            {id: 'ID 63', nama_daerah: 'KALIMANTAN SELATAN', nominal: 2000000},
            {id: 'ID 62', nama_daerah: 'KALIMANTAN TENGAH', nominal: 2000000},
            {id: 'ID 64', nama_daerah: 'KALIMANTAN TIMUR', nominal: 2000000},
            {id: 'ID 75', nama_daerah: 'GORONTALO', nominal: 2000000},
            {id: 'ID 73', nama_daerah: 'SULAWESI SELATAN', nominal: 2000000},
            {id: 'ID 74', nama_daerah: 'SULAWESI TENGGARA', nominal: 2000000},
            {id: 'ID 72', nama_daerah: 'SULAWESI TENGAH', nominal: 2000000},
            {id: 'ID 71', nama_daerah: 'SULAWESI UTARA', nominal: 2000000},
            {id: 'ID 76', nama_daerah: 'SULAWESI BARAT', nominal: 2000000},
            {id: 'ID 81', nama_daerah: 'MALUKU', nominal: 2000000},
            {id: 'ID 82', nama_daerah: 'MALUKU UTARA', nominal: 2000000},
            {id: 'ID 91', nama_daerah: 'PAPUA', nominal: 1000000},
            {id: 'ID 92', nama_daerah: 'PAPUA BARAT', nominal: 2000000},
            {id: 'ID 65', nama_daerah: 'KALIMANTAN UTARA', nominal: 2000000}
        ];

        // Data Default Kota Besar (UMK 2024 - Full Indonesia Eksak)
        const defaultUmk = [
            {id: '1300000', nama_daerah: 'Jakarta', nominal: 7000000},
            {id: 'ID 11.16', nama_daerah: 'KAB. ACEH TAMIANG', nominal: 1000000},
            {id: 'ID 11.04', nama_daerah: 'KAB. ACEH TENGAH', nominal: 1000000},
            {id: 'ID 11.03', nama_daerah: 'KAB. ACEH TIMUR', nominal: 1000000},
            {id: 'ID 12.09', nama_daerah: 'KAB. ASAHAN', nominal: 1000000},
            {id: 'ID 51.03', nama_daerah: 'KAB. BADUNG', nominal: 5000000},
            {id: 'ID 32.04', nama_daerah: 'KAB. BANDUNG', nominal: 1000000},
            {id: 'ID 32.17', nama_daerah: 'KAB. BANDUNG BARAT', nominal: 1000000},
            {id: 'ID 72.01', nama_daerah: 'KAB. BANGGAI', nominal: 1000000},
            {id: 'ID 35.26', nama_daerah: 'KAB. BANGKALAN', nominal: 1000000},
            {id: 'ID 33.04', nama_daerah: 'KAB. BANJARNEGARA', nominal: 1000000},
            {id: 'ID 73.03', nama_daerah: 'KAB. BANTAENG', nominal: 1000000},
            {id: 'ID 34.02', nama_daerah: 'KAB. BANTUL', nominal: 1000000},
            {id: 'ID 16.07', nama_daerah: 'KAB. BANYUASIN', nominal: 1000000},
            {id: 'ID 33.02', nama_daerah: 'KAB. BANYUMAS', nominal: 1000000},
            {id: 'ID 35.10', nama_daerah: 'KAB. BANYUWANGI', nominal: 1000000},
            {id: 'ID 73.11', nama_daerah: 'KAB. BARRU', nominal: 1000000},
            {id: 'ID 33.25', nama_daerah: 'KAB. BATANG', nominal: 1000000},
            {id: 'ID 12.19', nama_daerah: 'KAB. BATU BARA', nominal: 1000000},
            {id: 'ID 32.16', nama_daerah: 'KAB. BEKASI', nominal: 1000000},
            {id: 'ID 19.02', nama_daerah: 'KAB. BELITUNG', nominal: 1000000},
            {id: 'ID 14.03', nama_daerah: 'KAB. BENGKALIS', nominal: 1000000},
            {id: 'ID 17.01', nama_daerah: 'KAB. BENGKULU SELATAN', nominal: 1000000},
            {id: 'ID 64.03', nama_daerah: 'KAB. BERAU', nominal: 1000000},
            {id: 'ID 11.11', nama_daerah: 'KAB. BIREUEN', nominal: 1000000},
            {id: 'ID 33.16', nama_daerah: 'KAB. BLORA', nominal: 1000000},
            {id: 'ID 32.01', nama_daerah: 'KAB. BOGOR', nominal: 1000000},
            {id: 'ID 35.22', nama_daerah: 'KAB. BOJONEGORO', nominal: 1000000},
            {id: 'ID 35.11', nama_daerah: 'KAB. BONDOWOSO', nominal: 1000000},
            {id: 'ID 73.08', nama_daerah: 'KAB. BONE', nominal: 1000000},
            {id: 'ID 33.09', nama_daerah: 'KAB. BOYOLALI', nominal: 1000000},
            {id: 'ID 33.29', nama_daerah: 'KAB. BREBES', nominal: 1000000},
            {id: 'ID 51.08', nama_daerah: 'KAB. BULELENG', nominal: 1000000},
            {id: 'ID 73.02', nama_daerah: 'KAB. BULUKUMBA', nominal: 1000000},
            {id: 'ID 65.01', nama_daerah: 'KAB. BULUNGAN', nominal: 1000000},
            {id: 'ID 15.08', nama_daerah: 'KAB. BUNGO', nominal: 1000000},
            {id: 'ID 32.07', nama_daerah: 'KAB. CIAMIS', nominal: 1000000},
            {id: 'ID 32.03', nama_daerah: 'KAB. CIANJUR', nominal: 1000000},
            {id: 'ID 33.01', nama_daerah: 'KAB. CILACAP', nominal: 1000000},
            {id: 'ID 32.09', nama_daerah: 'KAB. CIREBON', nominal: 1000000},
            {id: 'ID 12.07', nama_daerah: 'KAB. DELI SERDANG', nominal: 1000000},
            {id: 'ID 33.21', nama_daerah: 'KAB. DEMAK', nominal: 1000000},
            {id: 'ID 13.10', nama_daerah: 'KAB. DHARMASRAYA', nominal: 1000000},
            {id: 'ID 53.08', nama_daerah: 'KAB. ENDE', nominal: 1000000},
            {id: 'ID 73.16', nama_daerah: 'KAB. ENREKANG', nominal: 1000000},
            {id: 'ID 32.05', nama_daerah: 'KAB. GARUT', nominal: 1000000},
            {id: 'ID 51.04', nama_daerah: 'KAB. GIANYAR', nominal: 1000000},
            {id: 'ID 75.01', nama_daerah: 'KAB. GORONTALO', nominal: 1000000},
            {id: 'ID 73.06', nama_daerah: 'KAB. GOWA', nominal: 1000000},
            {id: 'ID 35.25', nama_daerah: 'KAB. GRESIK', nominal: 1000000},
            {id: 'ID 33.15', nama_daerah: 'KAB. GROBOGAN', nominal: 1000000},
            {id: 'ID 63.07', nama_daerah: 'KAB. HULU SUNGAI TENGAH', nominal: 1000000},
            {id: 'ID 63.08', nama_daerah: 'KAB. HULU SUNGAI UTARA', nominal: 1000000},
            {id: 'ID 14.04', nama_daerah: 'KAB. INDRAGIRI HILIR', nominal: 1000000},
            {id: 'ID 14.02', nama_daerah: 'KAB. INDRAGIRI HULU', nominal: 1000000},
            {id: 'ID 32.12', nama_daerah: 'KAB. INDRAMAYU', nominal: 1000000},
            {id: 'ID 91.03', nama_daerah: 'KAB. JAYAPURA', nominal: 1000000},
            {id: 'ID 35.09', nama_daerah: 'KAB. JEMBER', nominal: 1000000},
            {id: 'ID 51.01', nama_daerah: 'KAB. JEMBRANA', nominal: 1000000},
            {id: 'ID 73.04', nama_daerah: 'KAB. JENEPONTO', nominal: 1000000},
            {id: 'ID 33.20', nama_daerah: 'KAB. JEPARA', nominal: 1000000},
            {id: 'ID 35.17', nama_daerah: 'KAB. JOMBANG', nominal: 1000000},
            {id: 'ID 14.01', nama_daerah: 'KAB. KAMPAR', nominal: 1000000},
            {id: 'ID 62.03', nama_daerah: 'KAB. KAPUAS', nominal: 1000000},
            {id: 'ID 61.06', nama_daerah: 'KAB. KAPUAS HULU', nominal: 1000000},
            {id: 'ID 33.13', nama_daerah: 'KAB. KARANGANYAR', nominal: 1000000},
            {id: 'ID 32.15', nama_daerah: 'KAB. KARAWANG', nominal: 1000000},
            {id: 'ID 12.06', nama_daerah: 'KAB. KARO', nominal: 1000000},
            {id: 'ID 33.05', nama_daerah: 'KAB. KEBUMEN', nominal: 1000000},
            {id: 'ID 33.24', nama_daerah: 'KAB. KENDAL', nominal: 1000000},
            {id: 'ID 17.08', nama_daerah: 'KAB. KEPAHIANG', nominal: 1000000},
            {id: 'ID 61.04', nama_daerah: 'KAB. KETAPANG', nominal: 1000000},
            {id: 'ID 33.10', nama_daerah: 'KAB. KLATEN', nominal: 1000000},
            {id: 'ID 74.01', nama_daerah: 'KAB. KOLAKA', nominal: 1000000},
            {id: 'ID 63.02', nama_daerah: 'KAB. KOTABARU', nominal: 1000000},
            {id: 'ID 62.01', nama_daerah: 'KAB. KOTAWARINGIN BARAT', nominal: 1000000},
            {id: 'ID 62.02', nama_daerah: 'KAB. KOTAWARINGIN TIMUR', nominal: 1000000},
            {id: 'ID 61.12', nama_daerah: 'KAB. KUBU RAYA', nominal: 1000000},
            {id: 'ID 33.19', nama_daerah: 'KAB. KUDUS', nominal: 1000000},
            {id: 'ID 34.01', nama_daerah: 'KAB. KULON PROGO', nominal: 1000000},
            {id: 'ID 32.08', nama_daerah: 'KAB. KUNINGAN', nominal: 1000000},
            {id: 'ID 64.07', nama_daerah: 'KAB. KUTAI BARAT', nominal: 1000000},
            {id: 'ID 64.02', nama_daerah: 'KAB. KUTAI KARTANEGARA', nominal: 1000000},
            {id: 'ID 64.08', nama_daerah: 'KAB. KUTAI TIMUR', nominal: 1000000},
            {id: 'ID 12.10', nama_daerah: 'KAB. LABUHANBATU', nominal: 1000000},
            {id: 'ID 12.23', nama_daerah: 'KAB. LABUHANBATU UTARA', nominal: 1000000},
            {id: 'ID 16.04', nama_daerah: 'KAB. LAHAT', nominal: 1000000},
            {id: 'ID 35.24', nama_daerah: 'KAB. LAMONGAN', nominal: 1000000},
            {id: 'ID 18.04', nama_daerah: 'KAB. LAMPUNG BARAT', nominal: 1000000},
            {id: 'ID 18.01', nama_daerah: 'KAB. LAMPUNG SELATAN', nominal: 1000000},
            {id: 'ID 18.02', nama_daerah: 'KAB. LAMPUNG TENGAH', nominal: 1000000},
            {id: 'ID 18.07', nama_daerah: 'KAB. LAMPUNG TIMUR', nominal: 1000000},
            {id: 'ID 18.03', nama_daerah: 'KAB. LAMPUNG UTARA', nominal: 1000000},
            {id: 'ID 61.08', nama_daerah: 'KAB. LANDAK', nominal: 1000000},
            {id: 'ID 12.05', nama_daerah: 'KAB. LANGKAT', nominal: 1000000},
            {id: 'ID 36.02', nama_daerah: 'KAB. LEBAK', nominal: 1000000},
            {id: 'ID 52.01', nama_daerah: 'KAB. LOMBOK BARAT', nominal: 1000000},
            {id: 'ID 52.02', nama_daerah: 'KAB. LOMBOK TENGAH', nominal: 1000000},
            {id: 'ID 52.03', nama_daerah: 'KAB. LOMBOK TIMUR', nominal: 1000000},
            {id: 'ID 35.08', nama_daerah: 'KAB. LUMAJANG', nominal: 1000000},
            {id: 'ID 73.17', nama_daerah: 'KAB. LUWU', nominal: 1000000},
            {id: 'ID 73.24', nama_daerah: 'KAB. LUWU TIMUR', nominal: 1000000},
            {id: 'ID 73.22', nama_daerah: 'KAB. LUWU UTARA', nominal: 1000000},
            {id: 'ID 35.19', nama_daerah: 'KAB. MADIUN', nominal: 1000000},
            {id: 'ID 33.08', nama_daerah: 'KAB. MAGELANG', nominal: 1000000},
            {id: 'ID 35.20', nama_daerah: 'KAB. MAGETAN', nominal: 1000000},
            {id: 'ID 32.10', nama_daerah: 'KAB. MAJALENGKA', nominal: 1000000},
            {id: 'ID 76.05', nama_daerah: 'KAB. MAJENE', nominal: 1000000},
            {id: 'ID 35.07', nama_daerah: 'KAB. MALANG', nominal: 1000000},
            {id: 'ID 76.02', nama_daerah: 'KAB. MAMUJU', nominal: 1000000},
            {id: 'ID 12.13', nama_daerah: 'KAB. MANDAILING NATAL', nominal: 1000000},
            {id: 'ID 53.15', nama_daerah: 'KAB. MANGGARAI BARAT', nominal: 1000000},
            {id: 'ID 92.02', nama_daerah: 'KAB. MANOKWARI', nominal: 1000000},
            {id: 'ID 73.09', nama_daerah: 'KAB. MAROS', nominal: 1000000},
            {id: 'ID 61.10', nama_daerah: 'KAB. MELAWI', nominal: 1000000},
            {id: 'ID 15.02', nama_daerah: 'KAB. MERANGIN', nominal: 1000000},
            {id: 'ID 91.01', nama_daerah: 'KAB. MERAUKE', nominal: 1000000},
            {id: 'ID 91.09', nama_daerah: 'KAB. MIMIKA', nominal: 1000000},
            {id: 'ID 71.05', nama_daerah: 'KAB. MINAHASA SELATAN', nominal: 1000000},
            {id: 'ID 71.06', nama_daerah: 'KAB. MINAHASA UTARA', nominal: 1000000},
            {id: 'ID 35.16', nama_daerah: 'KAB. MOJOKERTO', nominal: 1000000},
            {id: 'ID 72.06', nama_daerah: 'KAB. MOROWALI', nominal: 1000000},
            {id: 'ID 16.03', nama_daerah: 'KAB. MUARA ENIM', nominal: 1000000},
            {id: 'ID 16.06', nama_daerah: 'KAB. MUSI BANYUASIN', nominal: 1000000},
            {id: 'ID 91.04', nama_daerah: 'KAB. NABIRE', nominal: 1000000},
            {id: 'ID 35.18', nama_daerah: 'KAB. NGANJUK', nominal: 1000000},
            {id: 'ID 35.21', nama_daerah: 'KAB. NGAWI', nominal: 1000000},
            {id: 'ID 16.10', nama_daerah: 'KAB. OGAN ILIR', nominal: 1000000},
            {id: 'ID 16.02', nama_daerah: 'KAB. OGAN KOMERING ILIR', nominal: 1000000},
            {id: 'ID 16.01', nama_daerah: 'KAB. OGAN KOMERING ULU', nominal: 1000000},
            {id: 'ID 16.09', nama_daerah: 'KAB. OGAN KOMERING ULU SELATAN', nominal: 1000000},
            {id: 'ID 16.08', nama_daerah: 'KAB. OGAN KOMERING ULU TIMUR', nominal: 1000000},
            {id: 'ID 35.01', nama_daerah: 'KAB. PACITAN', nominal: 1000000},
            {id: 'ID 35.28', nama_daerah: 'KAB. PAMEKASAN', nominal: 1000000},
            {id: 'ID 36.01', nama_daerah: 'KAB. PANDEGLANG', nominal: 1000000},
            {id: 'ID 32.18', nama_daerah: 'KAB. PANGANDARAN', nominal: 1000000},
            {id: 'ID 73.10', nama_daerah: 'KAB. PANGKAJENE KEPULAUAN', nominal: 1000000},
            {id: 'ID 13.12', nama_daerah: 'KAB. PASAMAN BARAT', nominal: 1000000},
            {id: 'ID 35.14', nama_daerah: 'KAB. PASURUAN', nominal: 1000000},
            {id: 'ID 33.18', nama_daerah: 'KAB. PATI', nominal: 1000000},
            {id: 'ID 33.26', nama_daerah: 'KAB. PEKALONGAN', nominal: 1000000},
            {id: 'ID 14.05', nama_daerah: 'KAB. PELALAWAN', nominal: 1000000},
            {id: 'ID 33.27', nama_daerah: 'KAB. PEMALANG', nominal: 1000000},
            {id: 'ID 64.09', nama_daerah: 'KAB. PENAJAM PASER UTARA', nominal: 1000000},
            {id: 'ID 18.09', nama_daerah: 'KAB. PESAWARAN', nominal: 1000000},
            {id: 'ID 13.01', nama_daerah: 'KAB. PESISIR SELATAN', nominal: 1000000},
            {id: 'ID 11.07', nama_daerah: 'KAB. PIDIE', nominal: 1000000},
            {id: 'ID 73.15', nama_daerah: 'KAB. PINRANG', nominal: 1000000},
            {id: 'ID 76.04', nama_daerah: 'KAB. POLEWALI MANDAR', nominal: 1000000},
            {id: 'ID 35.02', nama_daerah: 'KAB. PONOROGO', nominal: 1000000},
            {id: 'ID 18.10', nama_daerah: 'KAB. PRINGSEWU', nominal: 1000000},
            {id: 'ID 35.13', nama_daerah: 'KAB. PROBOLINGGO', nominal: 1000000},
            {id: 'ID 33.03', nama_daerah: 'KAB. PURBALINGGA', nominal: 1000000},
            {id: 'ID 32.14', nama_daerah: 'KAB. PURWAKARTA', nominal: 1000000},
            {id: 'ID 33.06', nama_daerah: 'KAB. PURWOREJO', nominal: 1000000},
            {id: 'ID 17.02', nama_daerah: 'KAB. REJANG LEBONG', nominal: 1000000},
            {id: 'ID 33.17', nama_daerah: 'KAB. REMBANG', nominal: 1000000},
            {id: 'ID 14.07', nama_daerah: 'KAB. ROKAN HILIR', nominal: 1000000},
            {id: 'ID 14.06', nama_daerah: 'KAB. ROKAN HULU', nominal: 1000000},
            {id: 'ID 61.03', nama_daerah: 'KAB. SANGGAU', nominal: 1000000},
            {id: 'ID 15.03', nama_daerah: 'KAB. SAROLANGUN', nominal: 1000000},
            {id: 'ID 33.22', nama_daerah: 'KAB. SEMARANG', nominal: 1000000},
            {id: 'ID 36.04', nama_daerah: 'KAB. SERANG', nominal: 1000000},
            {id: 'ID 12.18', nama_daerah: 'KAB. SERDANG BEDAGAI', nominal: 1000000},
            {id: 'ID 14.08', nama_daerah: 'KAB. SIAK', nominal: 1000000},
            {id: 'ID 73.14', nama_daerah: 'KAB. SIDENRENG RAPPANG', nominal: 1000000},
            {id: 'ID 35.15', nama_daerah: 'KAB. SIDOARJO', nominal: 1000000},
            {id: 'ID 72.10', nama_daerah: 'KAB. SIGI', nominal: 1000000},
            {id: 'ID 73.07', nama_daerah: 'KAB. SINJAI', nominal: 1000000},
            {id: 'ID 61.05', nama_daerah: 'KAB. SINTANG', nominal: 1000000},
            {id: 'ID 35.12', nama_daerah: 'KAB. SITUBONDO', nominal: 1000000},
            {id: 'ID 34.04', nama_daerah: 'KAB. SLEMAN', nominal: 1000000},
            {id: 'ID 73.12', nama_daerah: 'KAB. SOPPENG', nominal: 1000000},
            {id: 'ID 92.01', nama_daerah: 'KAB. SORONG', nominal: 1000000},
            {id: 'ID 33.14', nama_daerah: 'KAB. SRAGEN', nominal: 1000000},
            {id: 'ID 32.13', nama_daerah: 'KAB. SUBANG', nominal: 1000000},
            {id: 'ID 32.02', nama_daerah: 'KAB. SUKABUMI', nominal: 1000000},
            {id: 'ID 33.11', nama_daerah: 'KAB. SUKOHARJO', nominal: 1000000},
            {id: 'ID 52.04', nama_daerah: 'KAB. SUMBAWA', nominal: 1000000},
            {id: 'ID 32.11', nama_daerah: 'KAB. SUMEDANG', nominal: 1000000},
            {id: 'ID 35.29', nama_daerah: 'KAB. SUMENEP', nominal: 1000000},
            {id: 'ID 63.09', nama_daerah: 'KAB. TABALONG', nominal: 1000000},
            {id: 'ID 51.02', nama_daerah: 'KAB. TABANAN', nominal: 1000000},
            {id: 'ID 73.05', nama_daerah: 'KAB. TAKALAR', nominal: 1000000},
            {id: 'ID 73.18', nama_daerah: 'KAB. TANA TORAJA', nominal: 1000000},
            {id: 'ID 63.10', nama_daerah: 'KAB. TANAH BUMBU', nominal: 1000000},
            {id: 'ID 63.01', nama_daerah: 'KAB. TANAH LAUT', nominal: 1000000},
            {id: 'ID 36.03', nama_daerah: 'KAB. TANGERANG', nominal: 1000000},
            {id: 'ID 18.06', nama_daerah: 'KAB. TANGGAMUS', nominal: 1000000},
            {id: 'ID 12.02', nama_daerah: 'KAB. TAPANULI UTARA', nominal: 1000000},
            {id: 'ID 63.05', nama_daerah: 'KAB. TAPIN', nominal: 1000000},
            {id: 'ID 32.06', nama_daerah: 'KAB. TASIKMALAYA', nominal: 1000000},
            {id: 'ID 15.09', nama_daerah: 'KAB. TEBO', nominal: 1000000},
            {id: 'ID 33.28', nama_daerah: 'KAB. TEGAL', nominal: 1000000},
            {id: 'ID 33.23', nama_daerah: 'KAB. TEMANGGUNG', nominal: 1000000},
            {id: 'ID 12.12', nama_daerah: 'KAB. TOBA SAMOSIR', nominal: 1000000},
            {id: 'ID 73.26', nama_daerah: 'KAB. TORAJA UTARA', nominal: 1000000},
            {id: 'ID 35.23', nama_daerah: 'KAB. TUBAN', nominal: 1000000},
            {id: 'ID 18.05', nama_daerah: 'KAB. TULANG BAWANG', nominal: 1000000},
            {id: 'ID 35.04', nama_daerah: 'KAB. TULUNGAGUNG', nominal: 1000000},
            {id: 'ID 73.13', nama_daerah: 'KAB. WAJO', nominal: 1000000},
            {id: 'ID 33.12', nama_daerah: 'KAB. WONOGIRI', nominal: 1000000},
            {id: 'ID 33.07', nama_daerah: 'KAB. WONOSOBO', nominal: 1000000},
            {id: 'ID 31.73', nama_daerah: 'KOTA ADM. JAKARTA BARAT', nominal: 1000000},
            {id: 'ID 31.71', nama_daerah: 'KOTA ADM. JAKARTA PUSAT', nominal: 1000000},
            {id: 'ID 31.74', nama_daerah: 'KOTA ADM. JAKARTA SELATAN', nominal: 1000000},
            {id: 'ID 31.75', nama_daerah: 'KOTA ADM. JAKARTA TIMUR', nominal: 1000000},
            {id: 'ID 31.72', nama_daerah: 'KOTA ADM. JAKARTA UTARA', nominal: 1000000},
            {id: 'ID 81.71', nama_daerah: 'KOTA AMBON', nominal: 1000000},
            {id: 'ID 64.71', nama_daerah: 'KOTA BALIKPAPAN', nominal: 1000000},
            {id: 'ID 11.71', nama_daerah: 'KOTA BANDA ACEH', nominal: 1000000},
            {id: 'ID 18.71', nama_daerah: 'KOTA BANDAR LAMPUNG', nominal: 1000000},
            {id: 'ID 32.73', nama_daerah: 'KOTA BANDUNG', nominal: 1000000},
            {id: 'ID 32.79', nama_daerah: 'KOTA BANJAR', nominal: 1000000},
            {id: 'ID 63.72', nama_daerah: 'KOTA BANJARBARU', nominal: 1000000},
            {id: 'ID 63.71', nama_daerah: 'KOTA BANJARMASIN', nominal: 1000000},
            {id: 'ID 21.71', nama_daerah: 'KOTA BATAM', nominal: 1000000},
            {id: 'ID 35.79', nama_daerah: 'KOTA BATU', nominal: 1000000},
            {id: 'ID 74.72', nama_daerah: 'KOTA BAU BAU', nominal: 1000000},
            {id: 'ID 32.75', nama_daerah: 'KOTA BEKASI', nominal: 1000000},
            {id: 'ID 17.71', nama_daerah: 'KOTA BENGKULU', nominal: 1000000},
            {id: 'ID 12.75', nama_daerah: 'KOTA BINJAI', nominal: 1000000},
            {id: 'ID 71.72', nama_daerah: 'KOTA BITUNG', nominal: 1000000},
            {id: 'ID 35.72', nama_daerah: 'KOTA BLITAR', nominal: 1000000},
            {id: 'ID 32.71', nama_daerah: 'KOTA BOGOR', nominal: 1000000},
            {id: 'ID 64.74', nama_daerah: 'KOTA BONTANG', nominal: 1000000},
            {id: 'ID 13.75', nama_daerah: 'KOTA BUKITTINGGI', nominal: 1000000},
            {id: 'ID 36.72', nama_daerah: 'KOTA CILEGON', nominal: 1000000},
            {id: 'ID 32.77', nama_daerah: 'KOTA CIMAHI', nominal: 1000000},
            {id: 'ID 32.74', nama_daerah: 'KOTA CIREBON', nominal: 1000000},
            {id: 'ID 51.71', nama_daerah: 'KOTA DENPASAR', nominal: 1000000},
            {id: 'ID 32.76', nama_daerah: 'KOTA DEPOK', nominal: 1000000},
            {id: 'ID 14.72', nama_daerah: 'KOTA DUMAI', nominal: 1000000},
            {id: 'ID 75.71', nama_daerah: 'KOTA GORONTALO', nominal: 1000000},
            {id: 'ID 15.71', nama_daerah: 'KOTA JAMBI', nominal: 1000000},
            {id: 'ID 91.71', nama_daerah: 'KOTA JAYAPURA', nominal: 1000000},
            {id: 'ID 35.71', nama_daerah: 'KOTA KEDIRI', nominal: 1000000},
            {id: 'ID 74.71', nama_daerah: 'KOTA KENDARI', nominal: 1000000},
            {id: 'ID 71.74', nama_daerah: 'KOTA KOTAMOBAGU', nominal: 1000000},
            {id: 'ID 53.71', nama_daerah: 'KOTA KUPANG', nominal: 1000000},
            {id: 'ID 11.74', nama_daerah: 'KOTA LANGSA', nominal: 1000000},
            {id: 'ID 11.73', nama_daerah: 'KOTA LHOKSEUMAWE', nominal: 1000000},
            {id: 'ID 16.73', nama_daerah: 'KOTA LUBUK LINGGAU', nominal: 1000000},
            {id: 'ID 35.77', nama_daerah: 'KOTA MADIUN', nominal: 1000000},
            {id: 'ID 33.71', nama_daerah: 'KOTA MAGELANG', nominal: 1000000},
            {id: 'ID 73.71', nama_daerah: 'KOTA MAKASSAR', nominal: 1000000},
            {id: 'ID 35.73', nama_daerah: 'KOTA MALANG', nominal: 1000000},
            {id: 'ID 71.71', nama_daerah: 'KOTA MANADO', nominal: 1000000},
            {id: 'ID 52.71', nama_daerah: 'KOTA MATARAM', nominal: 1000000},
            {id: 'ID 12.71', nama_daerah: 'KOTA MEDAN', nominal: 1000000},
            {id: 'ID 18.72', nama_daerah: 'KOTA METRO', nominal: 1000000},
            {id: 'ID 35.76', nama_daerah: 'KOTA MOJOKERTO', nominal: 1000000},
            {id: 'ID 13.71', nama_daerah: 'KOTA PADANG', nominal: 1000000},
            {id: 'ID 13.74', nama_daerah: 'KOTA PADANG PANJANG', nominal: 1000000},
            {id: 'ID 12.77', nama_daerah: 'KOTA PADANG SIDEMPUAN', nominal: 1000000},
            {id: 'ID 16.72', nama_daerah: 'KOTA PAGAR ALAM', nominal: 1000000},
            {id: 'ID 62.71', nama_daerah: 'KOTA PALANGKARAYA', nominal: 1000000},
            {id: 'ID 16.71', nama_daerah: 'KOTA PALEMBANG', nominal: 1000000},
            {id: 'ID 73.73', nama_daerah: 'KOTA PALOPO', nominal: 1000000},
            {id: 'ID 72.71', nama_daerah: 'KOTA PALU', nominal: 1000000},
            {id: 'ID 19.71', nama_daerah: 'KOTA PANGKAL PINANG', nominal: 1000000},
            {id: 'ID 73.72', nama_daerah: 'KOTA PARE PARE', nominal: 1000000},
            {id: 'ID 13.77', nama_daerah: 'KOTA PARIAMAN', nominal: 1000000},
            {id: 'ID 35.75', nama_daerah: 'KOTA PASURUAN', nominal: 1000000},
            {id: 'ID 13.76', nama_daerah: 'KOTA PAYAKUMBUH', nominal: 1000000},
            {id: 'ID 33.75', nama_daerah: 'KOTA PEKALONGAN', nominal: 1000000},
            {id: 'ID 14.71', nama_daerah: 'KOTA PEKANBARU', nominal: 1000000},
            {id: 'ID 12.72', nama_daerah: 'KOTA PEMATANGSIANTAR', nominal: 1000000},
            {id: 'ID 61.71', nama_daerah: 'KOTA PONTIANAK', nominal: 1000000},
            {id: 'ID 16.74', nama_daerah: 'KOTA PRABUMULIH', nominal: 1000000},
            {id: 'ID 35.74', nama_daerah: 'KOTA PROBOLINGGO', nominal: 1000000},
            {id: 'ID 33.73', nama_daerah: 'KOTA SALATIGA', nominal: 1000000},
            {id: 'ID 64.72', nama_daerah: 'KOTA SAMARINDA', nominal: 1000000},
            {id: 'ID 33.74', nama_daerah: 'KOTA SEMARANG', nominal: 1000000},
            {id: 'ID 36.73', nama_daerah: 'KOTA SERANG', nominal: 1000000},
            {id: 'ID 12.73', nama_daerah: 'KOTA SIBOLGA', nominal: 1000000},
            {id: 'ID 61.72', nama_daerah: 'KOTA SINGKAWANG', nominal: 1000000},
            {id: 'ID 13.72', nama_daerah: 'KOTA SOLOK', nominal: 1000000},
            {id: 'ID 92.71', nama_daerah: 'KOTA SORONG', nominal: 1000000},
            {id: 'ID 32.72', nama_daerah: 'KOTA SUKABUMI', nominal: 1000000},
            {id: 'ID 15.72', nama_daerah: 'KOTA SUNGAI PENUH', nominal: 1000000},
            {id: 'ID 35.78', nama_daerah: 'KOTA SURABAYA', nominal: 1000000},
            {id: 'ID 33.72', nama_daerah: 'KOTA SURAKARTA', nominal: 1000000},
            {id: 'ID 36.71', nama_daerah: 'KOTA TANGERANG', nominal: 1000000},
            {id: 'ID 36.74', nama_daerah: 'KOTA TANGERANG SELATAN', nominal: 1000000},
            {id: 'ID 12.74', nama_daerah: 'KOTA TANJUNG BALAI', nominal: 1000000},
            {id: 'ID 21.72', nama_daerah: 'KOTA TANJUNG PINANG', nominal: 1000000},
            {id: 'ID 65.71', nama_daerah: 'KOTA TARAKAN', nominal: 1000000},
            {id: 'ID 32.78', nama_daerah: 'KOTA TASIKMALAYA', nominal: 1000000},
            {id: 'ID 12.76', nama_daerah: 'KOTA TEBING TINGGI', nominal: 1000000},
            {id: 'ID 33.76', nama_daerah: 'KOTA TEGAL', nominal: 1000000},
            {id: 'ID 82.71', nama_daerah: 'KOTA TERNATE', nominal: 1000000},
            {id: 'ID 71.73', nama_daerah: 'KOTA TOMOHON', nominal: 1000000},
            {id: 'ID 34.71', nama_daerah: 'KOTA YOGYAKARTA', nominal: 1000000},
            {id: 'MYS-00001', nama_daerah: 'Malaysia', nominal: 1000000},
            {id: 'SIN-01001', nama_daerah: 'Singapore', nominal: 1000000}
        ];

        // Ambil data dari DB (jika ada) atau gunakan default
        if (type === 'UMP') {
            simulasiAllData = dbData.length > 0 ? dbData : defaultUmp;
        } else {
            simulasiAllData = dbData.length > 0 ? dbData : defaultUmk;
        }

        const select = document.getElementById('simulasiRegion');
        const placeholderText = type === 'UMP' ? '-- Pilih Provinsi --' : '-- Pilih Kota/Kabupaten --';
        select.innerHTML = `<option value="">${placeholderText}</option>` + 
            simulasiAllData.map(r => `<option value="${r.id}">${r.nama_daerah}</option>`).join('');
    } catch (err) { console.error(err); }
}

function hitungSimulasiGaji() {
    const regionId = document.getElementById('simulasiRegion').value;
    const region = simulasiAllData.find(r => r.id == regionId);
    if (!region) return;
    const basic = parseFloat(region.nominal);
    const allowance = basic * 0.1; // Estimasi 10%
    const total = basic + allowance;

    document.getElementById('simBasic').innerText = formatNominal(basic);
    document.getElementById('simAllowance').innerText = formatNominal(allowance);
    document.getElementById('simTotal').innerText = formatNominal(total);

    document.getElementById('simulasiResult').style.display = 'block';
    document.getElementById('simulasiResult').scrollIntoView({ behavior: 'smooth' });
}

// Function to format Rupiah as the user types
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

// Function to handle save Nominal
function simpanNominalManual() {
    const inputVal = document.getElementById('inputUmrNominal').value;
    const nominal = parseInt(inputVal.replace(/\./g, ''));
    if (!nominal || nominal <= 0) {
        showToast('Masukkan nominal gaji yang valid!', 'error');
        return;
    }
    
    showToast('Nominal Rp ' + inputVal + ' berhasil disimpan untuk simulasi.', 'success');
    // Here you can integrate with backend API if needed to save the user's manual nominal preference
    document.getElementById('inputUmrNominal').value = '';
}

// ===== 3. GLOBAL MANAJEMEN KARYAWAN =====
let allEmployeesGlobal = [];

async function renderManajemenKaryawan(list = null) {
    try {
        if (!list) {
            const res = await fetch(`${API_URL}/employees`);
            allEmployeesGlobal = await res.json();
            list = allEmployeesGlobal;
        }
        const tbody = document.getElementById('tabelKaryawanGlobalBody');
        if (!tbody) return;
        
        tbody.innerHTML = list.map(emp => `
            <tr>
                <td style="font-weight: 600; color: #64748b;">${emp.nik || '-'}</td>
                <td style="font-weight: 600; color: var(--primary-color);">
                    <i class="fas fa-user" style="margin-right: 8px; opacity: 0.6;"></i>${emp.nama}
                </td>
                <td style="font-weight: 600; color: var(--secondary-color);">${emp.nama_klien || '-'}</td>
                <td>${emp.nama_posisi || '-'}</td>
                <td>${emp.nama_dept || '-'}</td>
                <td>${emp.alamat || '-'}</td>
                <td>${emp.email || '-'}</td>
                <td>
                    <div style="display: flex; gap: 8px;">
                        <button class="btn-icon btn-edit" onclick="bukaModalKaryawanGlobalEdit(${emp.id}, ${emp.client_id})" title="Edit Karyawan" style="color: var(--primary-color); background: rgba(41, 128, 185, 0.1); width: 30px; height: 30px; border-radius: 6px;"><i class="fas fa-user-edit"></i></button>
                        <button class="btn-icon btn-delete" onclick="hapusKaryawanGlobal(${emp.id})" title="Hapus Karyawan" style="color: var(--danger); background: rgba(231, 76, 60, 0.1); width: 30px; height: 30px; border-radius: 6px;"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        console.error(err);
    }
}

function cariKaryawanGlobalAktif() {
    const q = document.getElementById('cariKaryawanGlobal').value.toLowerCase();
    if (!q) {
        renderManajemenKaryawan(allEmployeesGlobal);
        return;
    }
    const filtered = allEmployeesGlobal.filter(emp => {
        return (emp.nama && emp.nama.toLowerCase().includes(q)) ||
               (emp.nik && emp.nik.toLowerCase().includes(q)) ||
               (emp.nama_klien && emp.nama_klien.toLowerCase().includes(q)) ||
               (emp.nama_posisi && emp.nama_posisi.toLowerCase().includes(q)) ||
               (emp.nama_dept && emp.nama_dept.toLowerCase().includes(q)) ||
               (emp.nama_divisi && emp.nama_divisi.toLowerCase().includes(q));
    });
    renderManajemenKaryawan(filtered);
}

function bukaModalKaryawanGlobal() {
    window.selectedClientId = null;
    if (typeof bukaModalKaryawan === 'function') {
        bukaModalKaryawan('tambah');
    }
}

async function bukaModalKaryawanGlobalEdit(id, clientId) {
    window.selectedClientId = clientId;
    if (typeof bukaModalKaryawan === 'function') {
        await bukaModalKaryawan('edit', id);
    }
}

async function hapusKaryawanGlobal(id) {
    if (!await showConfirm('Yakin ingin menghapus karyawan ini?')) return;
    try {
        const res = await fetch(`${API_URL}/employees/${id}`, { method: 'DELETE' });
        if (res.ok) {
            renderManajemenKaryawan();
            showToast('Karyawan berhasil dihapus', 'success');
        }
    } catch (err) {
        console.error(err);
    }
}

async function renderLogAktivitas() {
    const tableBody = document.getElementById('logAktivitasTableBody');
    if (!tableBody) return;
    
    tableBody.innerHTML = `<tr><td colspan="3" class="text-center">Memuat data...</td></tr>`;
    
    try {
        const res = await fetch(`${API_URL}/logs`);
        if (!res.ok) throw new Error('Gagal mengambil data log');
        const logs = await res.json();
        
        if (logs.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="3" class="text-center" style="font-style: italic; color: #888;">Belum ada log aktivitas.</td></tr>`;
            return;
        }
        
        tableBody.innerHTML = logs.map(log => {
            const dateStr = log.created_at ? new Date(log.created_at).toLocaleString('id-ID', {
                dateStyle: 'medium',
                timeStyle: 'short'
            }) : '-';
            
            return `
                <tr>
                    <td style="font-weight: 500; color: #1e293b;">${log.action || '-'}</td>
                    <td><span class="scheme-badge rutin" style="text-transform: none;">${log.user_action || '-'}</span></td>
                    <td style="color: #64748b;">${dateStr}</td>
                </tr>
            `;
        }).join('');
    } catch (err) {
        console.error(err);
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center" style="color: var(--danger);">Gagal memuat log aktivitas.</td></tr>`;
    }
}

window.renderManajemenKaryawan = renderManajemenKaryawan;
window.cariKaryawanGlobalAktif = cariKaryawanGlobalAktif;
window.bukaModalKaryawanGlobal = bukaModalKaryawanGlobal;
window.bukaModalKaryawanGlobalEdit = bukaModalKaryawanGlobalEdit;
window.hapusKaryawanGlobal = hapusKaryawanGlobal;
window.renderLogAktivitas = renderLogAktivitas;
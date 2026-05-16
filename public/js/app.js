// Cek Login
const currentUser = JSON.parse(localStorage.getItem('user'));
if (!currentUser) {
    window.location.href = BASE_URL + 'index.php/login';
}

// API URL
const API_URL = BASE_URL + 'index.php/api';

// Update Header Nama
if (currentUser && document.getElementById('headerUserName')) {
    document.getElementById('headerUserName').innerText = currentUser.username;
}

// ===== Global State =====
let clients = [];
let orgData = [];
let selectedClientId = null;
let payrollSchemes = [];
let taxSchemes = [];
let clientConfigs = [];
let pkwtData = [];
let currentPeriodId = null;

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

// ===== Navigation =====
function switchView(view) {
    document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.sidebar-menu li').forEach(l => l.classList.remove('active'));

    const sectionId = 'view' + view.charAt(0).toUpperCase() + view.slice(1);
    const menuId = 'menu' + view.charAt(0).toUpperCase() + view.slice(1);
    
    if(document.getElementById(sectionId)) document.getElementById(sectionId).classList.add('active');
    if(document.getElementById(menuId)) document.getElementById(menuId).classList.add('active');

    const titles = {
        dashboard: 'Dashboard',
        klien: 'Manajemen Klien',
        karyawan: 'Data Karyawan',
        struktur: 'Struktur Organisasi',
        payroll: 'Skema Payroll',
        pajak: 'Skema Pajak (PPh 21)',
        setup: 'Setup Payroll Klien',
        pkwt: 'Kontrak PKWT',
        proses: 'Proses Payroll',
        umr: 'Upload UMP/UMK'
    };
    document.getElementById('viewTitle').innerText = titles[view] || 'Payroll System';

    // Auto load data based on view
    if (view === 'dashboard') updateDashboardStats();
    if (view === 'klien') renderTable();
    if (view === 'karyawan') renderAllEmployees();
    if (view === 'payroll') renderPayrollSchemes();
    if (view === 'pajak') renderTaxSchemes();
    if (view === 'setup') renderClientSetup();
    if (view === 'pkwt') renderPKWTTable();
    if (view === 'proses') loadActivePeriod();
    if (view === 'umr') renderUmrTable();
}

// ===== 1. KLIEN MODULE =====
async function renderTable() {
    try {
        const response = await fetch(`${API_URL}/clients`);
        clients = await response.json();
        const tbody = document.getElementById('tabelKlienBody');
        if (!tbody) return;
        tbody.innerHTML = clients.map(client => `
            <tr>
                <td style="font-weight: 600; color: var(--primary-color);">${client.nama}</td>
                <td>${client.sektor}</td>
                <td>${client.alamat}</td>
                <td>
                    <div class="action-btns">
                        <button class="btn-icon btn-edit" onclick="bukaModal('edit', ${client.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon btn-delete" onclick="hapusKlien(${client.id})"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    } catch (err) { console.error(err); }
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
        const res = await fetch(`${API_URL}/employees`);
        const employees = await res.json();
        const tbody = document.getElementById('tabelKaryawanBody');
        if(!tbody) return;
        tbody.innerHTML = employees.map(emp => `
            <tr>
                <td style="font-weight:600;">${emp.employee_name}</td>
                <td>${emp.nama}</td>
                <td>${emp.department_name}</td>
                <td>${emp.division_name}</td>
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
        tbody.innerHTML = clientConfigs.map(conf => `
            <tr>
                <td style="font-weight: 600;">${conf.client_name}</td>
                <td><span class="scheme-badge bulanan">${conf.payroll_scheme_name || 'Belum Set'}</span></td>
                <td><span class="scheme-badge" style="background:#e74c3c;">${conf.tax_scheme_name || 'Belum Set'}</span></td>
                <td>Tgl ${conf.pay_date || '-'}</td>
                <td>${conf.cutoff_start || '-'}-${(conf.cutoff_start - 1) || '-'}</td>
                <td>
                    <button class="btn-icon btn-edit" onclick="bukaModalSetup(${conf.client_id}, '${conf.client_name}')"><i class="fas fa-cog"></i></button>
                </td>
            </tr>
        `).join('');
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
    }
}

// ===== 6. PKWT (KONTRAK KERJA) =====
async function renderPKWTTable() {
    try {
        const response = await fetch(`${API_URL}/pkwt`);
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

function bukaModalPKWT() {
    document.getElementById('modalPKWT').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    fetch(`${API_URL}/clients`).then(r => r.json()).then(data => {
        document.getElementById('pkwtClientId').innerHTML = '<option value="">-- Pilih Klien --</option>' + data.map(c => `<option value="${c.id}">${c.nama}</option>`).join('');
    });
}

// ===== 7. PROSES PAYROLL BULANAN =====
async function loadActivePeriod() {
    try {
        const response = await fetch(`${API_URL}/periods`);
        const periods = await response.json();
        const list = document.getElementById('periodHistoryList');
        if (list) list.innerHTML = periods.map(p => `
            <div class="period-item ${p.id == currentPeriodId ? 'active' : ''}" onclick="selectPeriod(${p.id}, '${p.nama}')">
                <span>${p.nama}</span> <span class="status-badge success">${p.status}</span>
            </div>
        `).join('');
        if (periods.length > 0 && !currentPeriodId) selectPeriod(periods[0].id, periods[0].nama);
    } catch (err) { console.error(err); }
}

function selectPeriod(id, name) {
    currentPeriodId = id;
    document.getElementById('activePeriodName').innerText = name;
    document.getElementById('prosesActions').style.display = 'block';
    renderCutOffTable();
    renderReviewGajiTable();
    tutupSemuaModal();
}

async function renderCutOffTable() {
    if(!currentPeriodId) return;
    const res = await fetch(`${API_URL}/attendance/${currentPeriodId}`);
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
    const res = await fetch(`${API_URL}/payroll-results/${currentPeriodId}`);
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
    const modals = ['modalClient', 'modalSkema', 'modalKomponen', 'modalOrg', 'modalPajak', 'modalSetup', 'modalPKWT', 'modalPeriode', 'modalCutOff', 'modalSlip', 'modalManualUmr', 'modalUploadUmr'];
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
});

// Expose to window
Object.assign(window, {
    switchView, logout, bukaModal, hapusKlien, 
    bukaModalPajak, renderTaxSchemes, 
    bukaModalSetup, renderClientSetup,
    bukaModalPKWT, renderPKWTTable,
    bukaModalPeriode, generateGaji, approveGaji,
    bukaSlipGaji, tutupModalSlip, cetakSlip,
    switchUmrTab, filterUmrTable, bukaModalUploadUmr, tutupModalUploadUmr,
    handleUmrSelectChange, bukaModalManualUmr, tutupModalManualUmr,
    downloadTemplateUmr, goUmrPage,
    tutupSemuaModal,
    bukaModalSkema, bukaModalKomponen, bukaModalOrg, bukaModalCutOff,
    tutupModalSkema, tutupModalKomponen, tutupModalPajak, tutupModalSetup, 
    tutupModalPKWT, tutupModalPeriode, tutupModalCutOff
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
function tutupModalSkema() { tutupSemuaModal(); }
function tutupModalKomponen() { tutupSemuaModal(); }
function tutupModalPajak() { tutupSemuaModal(); }
function tutupModalSetup() { tutupSemuaModal(); }
function tutupModalPKWT() { tutupSemuaModal(); }
function tutupModalPeriode() { tutupSemuaModal(); }
function tutupModalCutOff() { tutupSemuaModal(); }

// ===== UMP / UMK MODULE =====

let currentUmrType = 'UMP';
let umrAllData = [];
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
        
        // Change table headers based on type
        document.getElementById('colUmrCode').innerText = tipe === 'UMP' ? 'Kode Provinsi' : 'Kode Kota/Kab';
        document.getElementById('colUmrName').innerText = tipe === 'UMP' ? 'Provinsi' : 'Kota/Kabupaten';

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

    tbody.innerHTML = pageData.length > 0
        ? pageData.map(row => `
            <tr>
                <td class="td-code">${row.kode_daerah}</td>
                <td class="td-name">${row.nama_daerah}</td>
                <td class="td-nominal">${formatNominal(row.nominal)}</td>
            </tr>
        `).join('')
        : `<tr><td colspan="3" style="text-align:center; padding:40px; color:#aaa;">
                <i class="fas fa-database" style="font-size:28px; margin-bottom:10px; display:block;"></i>
                Belum ada data ${currentUmrType}. Klik <b>Upload</b> untuk menambah data.
           </td></tr>`;

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
        csvContent = 'Kode Provinsi,Provinsi,Nominal\n';
    } else {
        csvContent = 'Kode Kota/Kab,Kota/Kabupaten,Nominal\n';
    }

    // Jika ada data di tabel, masukkan data tersebut ke CSV
    if (umrAllData && umrAllData.length > 0) {
        umrAllData.forEach(row => {
            csvContent += `${row.kode_daerah},${row.nama_daerah},${row.nominal}\n`;
        });
    } else {
        // Fallback ke data contoh jika tabel kosong
        if (tipe === 'UMP') {
            csvContent += 'ID 11,ACEH,3460672\n';
            csvContent += 'ID 12,SUMATERA UTARA,2809915\n';
            csvContent += 'ID 13,SUMATERA BARAT,2811449\n';
            csvContent += 'ID 14,RIAU,3294625\n';
            csvContent += 'ID 15,JAMBI,3037121\n';
            csvContent += 'ID 16,SUMATERA SELATAN,3456874\n';
            csvContent += 'ID 17,BENGKULU,2507079\n';
            csvContent += 'ID 18,LAMPUNG,2716497\n';
            csvContent += 'ID 19,KEP. BANGKA BELITUNG,3640000\n';
            csvContent += 'ID 21,KEPULAUAN RIAU,3402492\n';
            csvContent += 'ID 31,DKI JAKARTA,5067381\n';
            csvContent += 'ID 32,JAWA BARAT,2057495\n';
            csvContent += 'ID 33,JAWA TENGAH,2036947\n';
            csvContent += 'ID 34,DI YOGYAKARTA,2125898\n';
            csvContent += 'ID 35,JAWA TIMUR,2165244\n';
            csvContent += 'ID 36,BANTEN,2727812\n';
            csvContent += 'ID 51,BALI,2713672\n';
            csvContent += 'ID 52,NUSA TENGGARA BARAT,2444067\n';
            csvContent += 'ID 53,NUSA TENGGARA TIMUR,2186826\n';
            csvContent += 'ID 61,KALIMANTAN BARAT,2702616\n';
            csvContent += 'ID 62,KALIMANTAN TENGAH,3226648\n';
            csvContent += 'ID 63,KALIMANTAN SELATAN,3282812\n';
            csvContent += 'ID 64,KALIMANTAN TIMUR,3360858\n';
            csvContent += 'ID 65,KALIMANTAN UTARA,3361653\n';
            csvContent += 'ID 71,SULAWESI UTARA,3545000\n';
            csvContent += 'ID 72,SULAWESI TENGAH,2736698\n';
            csvContent += 'ID 73,SULAWESI SELATAN,3434298\n';
            csvContent += 'ID 74,SULAWESI TENGGARA,2885964\n';
            csvContent += 'ID 75,GORONTALO,3025100\n';
            csvContent += 'ID 76,SULAWESI BARAT,2914958\n';
            csvContent += 'ID 81,MALUKU,2949953\n';
            csvContent += 'ID 82,MALUKU UTARA,3200000\n';
            csvContent += 'ID 91,PAPUA,4024270\n';
            csvContent += 'ID 94,PAPUA BARAT,3393000\n';
        } else {
            csvContent += 'ID 3171,KOTA JAKARTA PUSAT,5000000\n';
            csvContent += 'ID 3271,KOTA BOGOR,4500000\n';
            csvContent += 'ID 3273,KOTA BANDUNG,4200000\n';
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
                
                const items = dataLines.map(line => {
                    // Handle CSV with commas inside quotes
                    const cols = line.split(',').map(c => c.trim().replace(/^"|"$/g, ''));
                    return {
                        tipe: tipe,
                        kode_daerah: cols[0] || '',
                        nama_daerah: cols[1] || '',
                        nominal: parseFloat(cols[2]?.replace(/[^0-9.]/g, '')) || 0,
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
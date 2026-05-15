// Cek Login
const currentUser = JSON.parse(localStorage.getItem('user'));
if (!currentUser) {
    window.location.href = 'login';
}

// API URL
const API_URL = '/api';

// --- UTILITY FUNCTIONS ---
function formatRupiah(number) {
    if (number === null || number === undefined) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed; top: 20px; right: 20px; padding: 12px 24px; border-radius: 8px;
        color: white; font-weight: 600; z-index: 10000; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        transform: translateY(-20px); opacity: 0; transition: all 0.3s ease;
    `;
    toast.innerText = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
    }, 10);
    
    setTimeout(() => {
        toast.style.transform = 'translateY(-20px)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function showConfirm(message) {
    return new Promise((resolve) => {
        const modal = document.getElementById('modalConfirm');
        const overlay = document.getElementById('overlay');
        const msgEl = document.getElementById('confirmMessage');
        const btnYes = document.getElementById('btnConfirmYes');
        const btnNo = document.getElementById('btnConfirmCancel');
        
        if (!modal || !msgEl) {
            resolve(confirm(message));
            return;
        }

        msgEl.innerText = message;
        modal.style.display = 'block';
        overlay.style.display = 'block';
        
        btnYes.onclick = () => {
            modal.style.display = 'none';
            overlay.style.display = 'none';
            resolve(true);
        };
        
        btnNo.onclick = () => {
            modal.style.display = 'none';
            overlay.style.display = 'none';
            resolve(false);
        };
    });
}
window.formatRupiah = formatRupiah;
window.showToast = showToast;
window.showConfirm = showConfirm;

// Update Header Nama
if (currentUser && document.getElementById('headerUserName')) {
    document.getElementById('headerUserName').innerText = currentUser.username;
}

function logout() {
    localStorage.removeItem('user');
    window.location.href = 'login';
}

// --- UI INITIALIZATION ---

// Elemen DOM
const tabelBody = document.getElementById('tabelKlienBody');
const formKlien = document.getElementById('formKlien');
const modal = document.getElementById('modalClient') || document.getElementById('modalKlien');
const overlay = document.getElementById('overlay');
const modalTitle = document.getElementById('modalTitle');

// Toggle Sidebar
const toggleSidebarBtn = document.getElementById('toggleSidebar');
const sidebar = document.querySelector('.sidebar');
const mainContent = document.querySelector('.main-content');

if (toggleSidebarBtn) {
    toggleSidebarBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    });
}

// Data state (lokal cache)
let clients = [];
let orgData = [];
let selectedClientId = null;

// 1. Fungsi READ: Menampilkan data ke tabel
async function renderTable() {
    try {
        const response = await fetch(`${API_URL}/clients`);
        if (!response.ok) throw new Error('Gagal memuat data');
        clients = await response.json();
        
        if (!tabelBody) return;
        tabelBody.innerHTML = ''; 
        
        if (Array.isArray(clients)) {
            clients.forEach(client => {
                const row = `
                    <tr onclick="selectClient(${client.id}, '${client.nama}')" style="cursor: pointer;">
                        <td style="font-weight: 700; color: #7f8c8d;">${client.no_klien || '-'}</td>
                        <td style="font-weight: 600; color: var(--primary-color);">${client.nama}</td>
                        <td>
                            <div style="font-size: 13px; font-weight: 500;">${client.email || '-'}</div>
                            <div style="font-size: 11px; color: #888;">${client.telepon || '-'}</div>
                        </td>
                        <td>${client.sektor}</td>
                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${client.alamat}</td>
                        <td>
                            <span style="padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: ${client.status === 'Aktif' ? '#e8fdf0' : '#fde8e8'}; color: ${client.status === 'Aktif' ? '#2ecc71' : '#e74c3c'};">
                                ${client.status || 'Aktif'}
                            </span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-icon btn-edit" onclick="event.stopPropagation(); bukaModal('edit', ${client.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="event.stopPropagation(); hapusKlien(${client.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tabelBody.innerHTML += row;
            });
        }
    } catch (err) {
        console.error('Gagal mengambil data klien:', err);
    }
}
renderTable();

// 2. Fungsi Buka Modal
function bukaModal(mode, id = null) {
    if (!modal || !overlay) return;
    modal.style.display = 'block';
    overlay.style.display = 'block';
    const btnSubmit = document.getElementById('btnSubmit');

    if (mode === 'edit' && id !== null) {
        modalTitle.innerText = 'Edit Data Client';
        btnSubmit.innerText = 'Edit';
        
        const client = clients.find(c => c.id == id);
        if (client) {
            document.getElementById('clientId').value = client.id;
            document.getElementById('noKlien').value = client.no_klien || '';
            document.getElementById('namaKlien').value = client.nama || '';
            document.getElementById('emailKlien').value = client.email || '';
            document.getElementById('teleponKlien').value = client.telepon || '';
            document.getElementById('sektorKlien').value = client.sektor || '';
            document.getElementById('nib').value = client.nib || '';
            document.getElementById('npwp').value = client.npwp || '';
            document.getElementById('tanggalBergabung').value = client.tgl_gabung ? client.tgl_gabung.split('T')[0] : '';
            document.getElementById('alamat').value = client.alamat || '';
            document.getElementById('statusKlien').value = client.status || 'Aktif';
        }
    } else {
        modalTitle.innerText = 'Tambah Data Client';
        btnSubmit.innerText = 'Simpan';
        if (formKlien) formKlien.reset();
        document.getElementById('clientId').value = '';
        document.getElementById('noKlien').value = 'Otomatis';
        document.getElementById('statusKlien').value = 'Aktif';
    }
}

function tutupModal() {
    if (modal) modal.style.display = 'none';
    if (overlay) overlay.style.display = 'none';
}

// 4. Fungsi CREATE & UPDATE
if (formKlien) {
    formKlien.addEventListener('submit', async function (e) {
        e.preventDefault();
        const idInput = document.getElementById('clientId').value;
        const clientData = {
            nama: document.getElementById('namaKlien').value,
            email: document.getElementById('emailKlien').value,
            telepon: document.getElementById('teleponKlien').value,
            sektor: document.getElementById('sektorKlien').value,
            nib: document.getElementById('nib').value,
            npwp: document.getElementById('npwp').value,
            tgl_gabung: document.getElementById('tanggalBergabung').value,
            alamat: document.getElementById('alamat').value,
            status: document.getElementById('statusKlien').value,
        };

        try {
            let response;
            if (idInput) {
                response = await fetch(`${API_URL}/clients/${idInput}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(clientData)
                });
            } else {
                response = await fetch(`${API_URL}/clients`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(clientData)
                });
            }

            if (response.ok) {
                tutupModal();
                renderTable();
                showToast('Data berhasil disimpan!', 'success');
            }
        } catch (err) {
            console.error('Error saving client:', err);
            showToast('Gagal menyimpan data.', 'error');
        }
    });
}

async function hapusKlien(id) {
    const yakin = await showConfirm('Yakin ingin menghapus data klien ini?');
    if (!yakin) return;
    try {
        const response = await fetch(`${API_URL}/clients/${id}`, { method: 'DELETE' });
        if (response.ok) {
            renderTable();
            showToast('Klien berhasil dihapus', 'success');
        } else {
            const errData = await response.json();
            showToast('Gagal hapus klien: ' + (errData.error || response.statusText), 'error');
        }
    } catch (err) {
        console.error('Gagal menghapus klien:', err);
        showToast('Kesalahan koneksi ke server.', 'error');
    }
}
window.hapusKlien = hapusKlien;

// --- STRUKTUR ORGANISASI ---

function switchView(view) {
    document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.sidebar-menu li').forEach(l => l.classList.remove('active'));

    if (view === 'dashboard') {
        document.getElementById('viewDashboard').classList.add('active');
        document.getElementById('menuDashboard').classList.add('active');
        document.getElementById('viewTitle').innerText = 'Dashboard';
        updateDashboardStats();
    } else if (view === 'klien') {
        document.getElementById('viewKlien').classList.add('active');
        document.getElementById('menuKlien').classList.add('active');
        document.getElementById('viewTitle').innerText = 'Client';
        renderTable();
    } else if (view === 'karyawan') {
        document.getElementById('viewKaryawan').classList.add('active');
        document.getElementById('menuKaryawan').classList.add('active');
        document.getElementById('viewTitle').innerText = 'Data Karyawan';
        renderTableKaryawan();
    } else {
        document.getElementById('viewKlien').classList.add('active');
        document.getElementById('viewTitle').innerText = 'Manajemen Payroll';
        renderTable();
    }
}

// Stats Update Logic
async function updateDashboardStats() {
    try {
        if (currentUser && document.getElementById('welcomeName')) {
            document.getElementById('welcomeName').innerText = currentUser.username;
        }

        const resClients = await fetch(`${API_URL}/clients`);
        const clientsData = await resClients.json();
        if (document.getElementById('statTotalKlien')) {
            document.getElementById('statTotalKlien').innerText = clientsData.length || 0;
        }

        const resOrg = await fetch(`${API_URL}/org`);
        const allOrgData = await resOrg.json();
        if (document.getElementById('statTotalDivisi')) {
            document.getElementById('statTotalDivisi').innerText = allOrgData.length || 0;
        }

        let totalKaryawan = 0;
        if (Array.isArray(allOrgData)) {
            allOrgData.forEach(div => {
                (div.departments || []).forEach(dept => {
                    totalKaryawan += (dept.positions || []).length;
                });
            });
        }
        if (document.getElementById('statTotalKaryawan')) {
            document.getElementById('statTotalKaryawan').innerText = totalKaryawan;
        }

    } catch (err) {
        console.error('Error updating stats:', err);
    }
}

// Initial Load
updateDashboardStats();
renderTable();

function selectClient(id, nama) {
    selectedClientId = id;
    document.getElementById('tabelKlienContainer').style.display = 'none';
    document.getElementById('clientOrgDetail').style.display = 'block';
    document.getElementById('clientDetailTitle').innerText = `Struktur Klien: ${nama}`;
    
    // Reset Tabs
    switchClientTab('struktur');
}

function switchClientTab(tab) {
    const tabs = document.querySelectorAll('.tab-item');
    const contents = document.querySelectorAll('.client-tab-content');
    
    tabs.forEach(t => {
        t.style.color = '#888';
        t.style.borderBottom = 'none';
    });
    contents.forEach(c => c.style.display = 'none');

    if (tab === 'struktur') {
        document.getElementById('tabStruktur').style.color = 'var(--primary-color)';
        document.getElementById('tabStruktur').style.borderBottom = '3px solid var(--primary-color)';
        document.getElementById('contentStruktur').style.display = 'block';
        renderClientOrg(selectedClientId);
    } else if (tab === 'karyawan') {
        document.getElementById('tabKaryawan').style.color = 'var(--primary-color)';
        document.getElementById('tabKaryawan').style.borderBottom = '3px solid var(--primary-color)';
        document.getElementById('contentKaryawan').style.display = 'block';
        renderTableKaryawanClient();
        loadClientSchema(selectedClientId); // Load schema since it's now in the Karyawan tab
    } else {
        document.getElementById('tabPayroll').style.color = 'var(--primary-color)';
        document.getElementById('tabPayroll').style.borderBottom = '3px solid var(--primary-color)';
        document.getElementById('contentPayroll').style.display = 'block';
        filterPayrollByClient();
    }
}

async function loadClientSchema(clientId) {
    try {
        const res = await fetch(`${API_URL}/clients/schema/${clientId}`);
        if (res.ok) {
            const schema = await res.json();
            document.getElementById('schemaBpjsKes').value = schema.bpjs_kes_percent || 0;
            document.getElementById('schemaBpjsJht').value = schema.bpjs_jht_percent || 0;
            document.getElementById('schemaTaxMethod').value = schema.tax_method || 'Gross';
            document.getElementById('schemaCutOffStart').value = schema.cut_off_start || 21;
            document.getElementById('schemaCutOffEnd').value = schema.cut_off_end || 20;
        }
    } catch (err) { console.error(err); }
}

document.getElementById('formSchema').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = {
        client_id: selectedClientId,
        bpjs_kes_percent: document.getElementById('schemaBpjsKes').value,
        bpjs_jht_percent: document.getElementById('schemaBpjsJht').value,
        tax_method: document.getElementById('schemaTaxMethod').value,
        cut_off_start: document.getElementById('schemaCutOffStart').value,
        cut_off_end: document.getElementById('schemaCutOffEnd').value
    };
    const res = await fetch(`${API_URL}/clients/schema`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    if (res.ok) showToast('Skema Payroll berhasil disimpan!');
});

function backToClientList() {
    selectedClientId = null;
    document.getElementById('tabelKlienContainer').style.display = 'block';
    document.getElementById('clientOrgDetail').style.display = 'none';
}

window.selectClient = selectClient;
window.backToClientList = backToClientList;

window.toggleNode = function(element) {
    const container = element.nextElementSibling;
    const icon = element.querySelector('.toggle-icon');
    if (container.style.display === 'none') {
        container.style.display = container.dataset.displayType || 'block';
        if(icon) icon.style.transform = 'rotate(0deg)';
    } else {
        // Simpan display type asli sebelum hide
        if(!container.dataset.displayType && container.style.display) {
            container.dataset.displayType = container.style.display;
        } else if(!container.dataset.displayType) {
            const computedStyle = window.getComputedStyle(container).display;
            container.dataset.displayType = computedStyle !== 'none' ? computedStyle : 'block';
        }
        container.style.display = 'none';
        if(icon) icon.style.transform = 'rotate(-90deg)';
    }
};

async function renderClientOrg(clientId) {
    selectedClientId = clientId;
    const container = document.getElementById('clientOrgContainer');
    if (!container) return;
    container.innerHTML = '<div class="empty-state">Memuat Struktur...</div>';

    try {
        const res = await fetch(`${API_URL}/org?client_id=${clientId}`);
        orgData = await res.json(); 

        container.innerHTML = `
            <div class="org-actions" style="margin-bottom: 25px; display: flex; justify-content: flex-end;">
                <button class="btn-add" onclick="bukaModalOrg('divisi', 'tambah', null, ${clientId})" style="background: var(--primary-color);">
                    <i class="fas fa-plus"></i> Tambah Divisi
                </button>
            </div>
        `;

        if (!Array.isArray(orgData) || orgData.length === 0) {
            container.innerHTML += '<div class="empty-state" style="padding: 40px; border: 2px dashed #eee; border-radius: 12px; background: #fff;">Belum ada struktur organisasi. Klik tombol di atas untuk menambah Divisi.</div>';
            return;
        }

        orgData.forEach(div => {
            let divHtml = `
                <div class="org-level division-item" style="margin-bottom: 30px; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff;">
                    <div class="level-header" style="background: #f1f5f9; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; cursor: pointer; transition: background 0.2s;" onclick="toggleNode(this)" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                        <div class="level-title" style="font-size: 15px; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-chevron-down toggle-icon" style="color: #64748b; font-size: 12px; transition: transform 0.2s;"></i>
                            <i class="fas fa-sitemap" style="color: var(--primary-color);"></i> ${div.nama}
                        </div>
                        <div class="action-btns" onclick="event.stopPropagation()">
                            <button class="btn-nested-add" style="background: var(--info); border: none; color: white; padding: 5px 10px; border-radius: 6px; font-size: 11px; cursor: pointer;" onclick="bukaModalOrg('department', 'tambah', null, ${div.id})">
                                <i class="fas fa-plus"></i> Dept
                            </button>
                            <button class="btn-icon btn-edit" onclick="bukaModalOrg('divisi', 'edit', ${div.id}, ${clientId})"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon btn-delete" onclick="hapusOrg('divisi', ${div.id})"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="nested-container" style="padding: 15px;">
            `;

            if (div.departments && div.departments.length > 0) {
                div.departments.forEach(dept => {
                    divHtml += `
                        <div class="org-level department-item" style="margin-bottom: 20px; border-left: 3px solid var(--info); padding-left: 15px;">
                            <div class="level-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; cursor: pointer; padding: 5px; border-radius: 6px;" onclick="toggleNode(this)" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <div class="level-title" style="font-size: 14px; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-chevron-down toggle-icon" style="color: #94a3b8; font-size: 10px; transition: transform 0.2s;"></i>
                                    <i class="fas fa-building" style="color: var(--info);"></i> ${dept.nama}
                                </div>
                                <div class="action-btns" onclick="event.stopPropagation()">
                                    <button class="btn-nested-add" style="background: #10b981; border: none; color: white; padding: 4px 8px; border-radius: 6px; font-size: 10px; cursor: pointer;" onclick="bukaModalOrg('posisi', 'tambah', null, ${dept.id})">
                                        <i class="fas fa-user-plus"></i> Posisi
                                    </button>
                                    <button class="btn-icon btn-edit" onclick="bukaModalOrg('department', 'edit', ${dept.id}, ${div.id})"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon btn-delete" onclick="hapusOrg('department', ${dept.id})"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="nested-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;" data-display-type="grid">
                    `;

                    if (dept.positions && dept.positions.length > 0) {
                        dept.positions.forEach(pos => {
                            const emp = pos.employees && pos.employees.length > 0 ? pos.employees[0] : null;
                            const cardStyle = emp ? 'border-left: 4px solid var(--primary-color);' : 'border: 1px dashed #cbd5e0; background: #f8fafc;';
                            
                            divHtml += `
                                <div class="org-item unified-card" style="display: flex; align-items: center; gap: 12px; background: white; padding: 12px; border-radius: 10px; border: 1px solid #eef2f7; box-shadow: 0 2px 4px rgba(0,0,0,0.01); position: relative; ${cardStyle}">
                                    <div style="width: 36px; height: 36px; background: ${emp ? '#fff9f0' : '#f1f5f9'}; color: ${emp ? 'var(--primary-color)' : '#94a3b8'}; border-radius: 8px; display: grid; place-items: center; font-size: 14px;">
                                        <i class="fas ${emp ? 'fa-user-tie' : 'fa-user-plus'}"></i>
                                    </div>
                                    <div style="display: flex; flex-direction: column; flex-grow: 1;">
                                        <span style="font-size: 9px; font-weight: 800; text-transform: uppercase; color: #64748b;">${pos.nama}</span>
                                        <span style="font-weight: 700; color: #1e293b; font-size: 13px; line-height: 1.2;">${emp ? emp.nama : 'Posisi Kosong'}</span>
                                    </div>
                                    <div class="action-btns" style="display: flex; gap: 4px;">
                                        <button class="btn-icon btn-edit" style="padding: 4px; font-size: 10px;" onclick="bukaModalOrg('posisi', 'edit', ${pos.id}, ${dept.id})"><i class="fas fa-edit"></i></button>
                                        <button class="btn-icon btn-delete" style="padding: 4px; font-size: 10px;" onclick="hapusOrg('posisi', ${pos.id})"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        divHtml += '<div class="empty-state" style="font-size: 11px; color: #94a3b8; grid-column: 1/-1;">Belum ada posisi.</div>';
                    }

                    divHtml += `</div></div>`;
                });
            } else {
                divHtml += '<div class="empty-state" style="font-size: 12px; color: #94a3b8; text-align: center; padding: 10px;">Belum ada departemen di divisi ini.</div>';
            }

            divHtml += `</div></div>`;
            container.innerHTML += divHtml;
        });

    } catch (err) { console.error('Error rendering org tree:', err); }
}

function bukaModalOrg(type, mode, id = null, parentId = null) {
    const modalOrg = document.getElementById('modalOrg');
    if (!modalOrg || !overlay) return;
    modalOrg.style.display = 'block';
    overlay.style.display = 'block';
    
    document.getElementById('orgType').value = type;
    document.getElementById('orgId').value = id;
    document.getElementById('orgParentId').value = parentId; 
    
    const title = document.getElementById('modalOrgTitle');
    const label = document.getElementById('labelOrgName');
    const nameInput = document.getElementById('orgName');
    const employeeField = document.getElementById('posEmployeeField');
    const employeeNameInput = document.getElementById('posEmployeeName');
    const extraFields = document.getElementById('posExtraFields');
    const nikInput = document.getElementById('posNik');
    
    let typeLabel = "";
    if(type === 'divisi') typeLabel = "Divisi";
    else if(type === 'department') typeLabel = "Departemen";
    else typeLabel = "Posisi/Jabatan";

    title.innerText = (mode === 'edit' ? 'Edit ' : 'Tambah ') + typeLabel;
    label.innerText = 'Nama ' + typeLabel;
    nameInput.placeholder = 'Masukkan nama ' + typeLabel.toLowerCase();

    if (type === 'posisi') {
        employeeField.style.display = 'block';
        extraFields.style.display = 'block';
    } else {
        employeeField.style.display = 'none';
        extraFields.style.display = 'none';
    }
    
    if (mode === 'edit') {
        let currentData = { nama: "" };
        if (type === 'divisi') {
            const div = orgData.find(d => d.id == id);
            if (div) currentData.nama = div.nama;
        } else if (type === 'department') {
            orgData.forEach(div => {
                const dept = (div.departments || []).find(d => d.id == id);
                if (dept) currentData.nama = dept.nama;
            });
        } else if (type === 'posisi') {
            orgData.forEach(div => {
                (div.departments || []).forEach(dept => {
                    const pos = (dept.positions || []).find(p => p.id == id);
                    if (pos) {
                        currentData = { nama: pos.nama, employeeName: pos.employee_name || '' };
                    }
                });
            });
        }
        nameInput.value = currentData.nama || '';
        if (employeeNameInput) employeeNameInput.value = currentData.employeeName || '';
    } else {
        nameInput.value = '';
        if (employeeNameInput) employeeNameInput.value = '';
    }

    setTimeout(() => nameInput.focus(), 100);
}

const formOrg = document.getElementById('formOrg');
if (formOrg) {
    formOrg.addEventListener('submit', async function(e) {
        e.preventDefault();
        const type = document.getElementById('orgType').value;
        const id = document.getElementById('orgId').value;
        const parentId = document.getElementById('orgParentId').value;
        const name = document.getElementById('orgName').value.trim();
        const employeeName = document.getElementById('posEmployeeName') ? document.getElementById('posEmployeeName').value.trim() : '';
        const nik = document.getElementById('posNik') ? document.getElementById('posNik').value.trim() : '';
        const email = document.getElementById('posEmail') ? document.getElementById('posEmail').value.trim() : '';
        const phone = document.getElementById('posPhone') ? document.getElementById('posPhone').value.trim() : '';

        if (!name) {
            showToast('Nama harus diisi!', 'error');
            return;
        }

        try {
            let response;
            if (id && id !== "" && id !== "null") {
                response = await fetch(`${API_URL}/org/${type}/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nama: name, employee_name: employeeName, email, phone })
                });
            } else {
                let endpoint = '';
                let body = { nama: name };
                if (type === 'divisi') {
                    endpoint = '/divisions';
                    body.client_id = selectedClientId;
                } else if (type === 'department') {
                    endpoint = '/departments';
                    body.division_id = parentId;
                } else if (type === 'posisi') {
                    endpoint = '/positions';
                    body.department_id = parentId;
                    body.client_id = selectedClientId;
                    body.employee_name = employeeName;
                    body.nik = nik;
                    body.email = email;
                    body.phone = phone;
                }
                response = await fetch(`${API_URL}${endpoint}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                });
            }

            if (response.ok) {
                tutupModalOrg();
                if (selectedClientId) {
                    renderClientOrg(selectedClientId);
                    renderTableKaryawanClient(); 
                }
                showToast('Data berhasil disimpan!', 'success');
            } else {
                const errorData = await response.json();
                showToast('Gagal menyimpan: ' + (errorData.error || 'Server error'), 'error');
            }
        } catch (err) {
            console.error('Error saving org structure:', err);
            showToast('Terjadi kesalahan koneksi.', 'error');
        }
    });
}

async function hapusOrg(type, id) {
    const yakin = await showConfirm(`Yakin ingin menghapus ${type} ini?`);
    if (!yakin) return;
    try {
        const response = await fetch(`${API_URL}/org/${type}/${id}`, { method: 'DELETE' });
        if (response.ok) {
            if (selectedClientId) renderClientOrg(selectedClientId);
            showToast('Data berhasil dihapus', 'success');
        } else {
            const errorData = await response.json();
            showToast('Gagal menghapus: ' + (errorData.error || 'Server error'), 'error');
        }
    } catch (err) {
        console.error('Gagal menghapus organisasi:', err);
        showToast('Kesalahan koneksi saat menghapus.', 'error');
    }
}

function tutupSemuaModal() {
    tutupModalOrg();
    tutupModal();
}

// --- MANAJEMEN KARYAWAN ---
let employees = [];

async function renderTableKaryawan() {
    const tbody = document.getElementById('tabelKaryawanBody');
    if (!tbody) return;
    try {
        const res = await fetch(`${API_URL}/employees`);
        employees = await res.json();
        tbody.innerHTML = '';
        employees.forEach(emp => {
            tbody.innerHTML += `
                <tr>
                    <td>${emp.nik}</td>
                    <td><strong>${emp.nama}</strong></td>
                    <td>${emp.nama_posisi}</td>
                    <td>${emp.nama_klien}</td>
                    <td>${formatRupiah(emp.gaji_pokok)}</td>
                    <td><span class="badge" style="background: #e8fdf0; color: #2ecc71; padding: 4px 8px; border-radius: 4px; font-size: 11px;">${emp.status}</span></td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-icon btn-edit" onclick="bukaModalKaryawan('edit', ${emp.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon btn-delete" onclick="hapusKaryawan(${emp.id})"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        });
    } catch (err) { console.error(err); }
}

async function bukaModalKaryawan(mode, id = null) {
    const modal = document.getElementById('modalKaryawan');
    const clientSelect = document.getElementById('empClientId');
    modal.style.display = 'block';
    document.getElementById('overlay').style.display = 'block';

    // Load Client Dropdown
    clientSelect.innerHTML = '<option value="">-- Pilih Klien --</option>';
    clients.forEach(c => {
        clientSelect.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
    });

    if (selectedClientId) {
        clientSelect.value = selectedClientId;
        clientSelect.disabled = true; // Lock it!
        await loadPositions(selectedClientId);
    } else {
        clientSelect.disabled = false;
    }

    if (mode === 'edit' && id) {
        const emp = employees.find(e => e.id == id);
        document.getElementById('employeeId').value = emp.id;
        document.getElementById('empNik').value = emp.nik;
        document.getElementById('empNama').value = emp.nama;
        document.getElementById('empEmail').value = emp.email;
        document.getElementById('empRekening').value = emp.no_rekening;
        document.getElementById('empBankName').value = emp.bank_name || '';
        document.getElementById('empPtkp').value = emp.ptkp || 'TK/0';
        document.getElementById('empGaji').value = emp.gaji_pokok;
        document.getElementById('empClientId').value = emp.client_id;
        await loadPositions(emp.client_id);
        document.getElementById('empPositionId').value = emp.position_id;
        document.getElementById('empTglMasuk').value = emp.tgl_masuk;
    } else {
        document.getElementById('formKaryawan').reset();
        document.getElementById('employeeId').value = '';
    }
}

async function loadPositions(clientId) {
    if (!clientId) return;
    const posSelect = document.getElementById('empPositionId');
    if (!posSelect) return;
    posSelect.innerHTML = '<option>Loading...</option>';
    try {
        const res = await fetch(`${API_URL}/positions/client/${clientId}`);
        const positions = await res.json();
        posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
        if (Array.isArray(positions)) {
            positions.forEach(pos => {
                posSelect.innerHTML += `<option value="${pos.id}">${pos.nama}</option>`;
            });
        }
    } catch (err) { console.error(err); }
}

document.getElementById('formKaryawan').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('employeeId').value;
    const data = {
        nik: document.getElementById('empNik').value,
        nama: document.getElementById('empNama').value,
        email: document.getElementById('empEmail').value,
        no_rekening: document.getElementById('empRekening').value,
        bank_name: document.getElementById('empBankName').value,
        ptkp: document.getElementById('empPtkp').value,
        gaji_pokok: document.getElementById('empGaji').value,
        client_id: document.getElementById('empClientId').value,
        position_id: document.getElementById('empPositionId').value,
        tgl_masuk: document.getElementById('empTglMasuk').value
    };

    const method = id ? 'PUT' : 'POST';
    const url = id ? `${API_URL}/employees/${id}` : `${API_URL}/employees`;

    const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    if (res.ok) {
        tutupModalKaryawan();
        if (selectedClientId) {
            renderTableKaryawanClient();
            renderClientOrg(selectedClientId);
        }
        showToast('Data karyawan berhasil disimpan');
    }
});

function tutupModalKaryawan() {
    document.getElementById('modalKaryawan').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

// --- MANAJEMEN PAYROLL (FILTER BY CLIENT) ---
async function filterPayrollByClient() {
    if (!selectedClientId) return;
    
    const bulan = document.getElementById('payrollBulan').value;
    const tahun = document.getElementById('payrollTahun').value;
    const container = document.getElementById('tabelPayrollContainer');
    container.innerHTML = '<div style="text-align:center; padding: 20px;">Memproses data...</div>';

    try {
        const res = await fetch(`${API_URL}/employees`);
        employees = await res.json();
        
        // Load all payrolls for this period to check status
        const payRes = await fetch(`${API_URL}/payroll/status?bulan=${bulan}&tahun=${tahun}&client_id=${selectedClientId}`);
        const existingPayrolls = payRes.ok ? await payRes.json() : [];

        const clientEmployees = employees.filter(emp => emp.client_id == selectedClientId);
        
        if (clientEmployees.length === 0) {
            container.innerHTML = '<div style="text-align:center; padding: 20px;">Tidak ada karyawan di klien ini.</div>';
            return;
        }

        // Tentukan apakah dalam fase Input Cut Off atau Fase Approval
        const isCutOffPhase = existingPayrolls.length === 0;

        let html = '';

        if (isCutOffPhase) {
            // Tampilan Input Cut Off
            html = `
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
                    <div style="padding: 15px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="margin: 0; color: #1e293b;"><i class="fas fa-edit"></i> Input Data Cut-Off Kehadiran</h4>
                        <button class="btn-save" onclick="prosesPayrollBulk()" style="background: var(--primary-color);">
                            <i class="fas fa-cogs"></i> Proses & Generate Gaji
                        </button>
                    </div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f1f5f9; text-align: left; font-size: 13px; color: #64748b;">
                                <th style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">Nama Karyawan</th>
                                <th style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">Hadir (Hari)</th>
                                <th style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">Sakit/Izin (Hari)</th>
                                <th style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">Alpa (Hari)</th>
                                <th style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">Lembur (Jam)</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            clientEmployees.forEach(emp => {
                html += `
                            <tr style="border-bottom: 1px solid #e2e8f0;" class="cutoff-row" data-empid="${emp.id}">
                                <td style="padding: 12px 15px;"><strong>${emp.nama}</strong><br><span style="font-size: 11px; color: #64748b;">${emp.nama_posisi || '-'}</span></td>
                                <td style="padding: 12px 15px;"><input type="number" class="input-hadir" value="22" style="width: 60px; padding: 5px; text-align: center;"></td>
                                <td style="padding: 12px 15px;"><input type="number" class="input-sakit" value="0" style="width: 60px; padding: 5px; text-align: center;"></td>
                                <td style="padding: 12px 15px;"><input type="number" class="input-alpa" value="0" style="width: 60px; padding: 5px; text-align: center;"></td>
                                <td style="padding: 12px 15px;"><input type="number" class="input-lembur" value="0" style="width: 60px; padding: 5px; text-align: center;"></td>
                            </tr>
                `;
            });
            html += `</tbody></table></div>`;

        } else {
            // Tampilan Review / Approval / Slip
            let allApproved = existingPayrolls.every(p => p.status_pembayaran === 'Approved');
            let bulkActionHtml = allApproved ? '' : `
                <button class="btn-cancel" onclick="rejectPayrollAll()" style="color: #ef4444; border-color: #fca5a5; margin-right: 10px;">
                    <i class="fas fa-undo"></i> Batal / Revisi Cut-Off
                </button>
            `;

            html = `
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
                    <div style="padding: 15px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="margin: 0; color: #1e293b;"><i class="fas fa-file-invoice-dollar"></i> Review Gaji Bulanan</h4>
                        <div>${bulkActionHtml}</div>
                    </div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f1f5f9; text-align: left; font-size: 13px; color: #64748b;">
                                <th style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">Nama Karyawan</th>
                                <th style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">Gaji Pokok</th>
                                <th style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">Tunjangan (Lembur+)</th>
                                <th style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">Potongan (BPJS+)</th>
                                <th style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">Net Salary</th>
                                <th style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">Status</th>
                                <th style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            clientEmployees.forEach(emp => {
                const payroll = existingPayrolls.find(p => p.employee_id == emp.id);
                if (!payroll) return;

                let statusHtml = '<span class="badge" style="background: #fff9db; color: #f59f00; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Waiting Approval</span>';
                let actionHtml = `<button class="btn-save" style="font-size: 11px; padding: 5px 10px; background: #2ecc71;" onclick="approvePayroll(${payroll.id})"><i class="fas fa-check"></i> Approve</button>`;

                if (payroll.status_pembayaran === 'Approved') {
                    statusHtml = '<span class="badge" style="background: #e8fdf0; color: #2ecc71; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Approved</span>';
                    actionHtml = `<button class="btn-save" style="font-size: 11px; padding: 5px 10px; background: var(--info);" onclick="viewSlip(${payroll.id})"><i class="fas fa-file-invoice"></i> View Slip</button>`;
                }

                html += `
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 12px 15px;"><strong>${emp.nama}</strong><br><span style="font-size: 11px; color: #64748b;">${emp.nama_posisi || '-'}</span></td>
                        <td style="padding: 12px 15px;">${formatRupiah(payroll.gaji_pokok)}</td>
                        <td style="padding: 12px 15px; color: #10b981;">+ ${formatRupiah(payroll.total_tunjangan)}</td>
                        <td style="padding: 12px 15px; color: #ef4444;">- ${formatRupiah(payroll.total_potongan)}</td>
                        <td style="padding: 12px 15px;"><strong>${formatRupiah(payroll.take_home_pay)}</strong></td>
                        <td style="padding: 12px 15px;">${statusHtml}</td>
                        <td style="padding: 12px 15px;">${actionHtml}</td>
                    </tr>
                `;
            });
            html += `</tbody></table></div>`;
        }

        container.innerHTML = html;

    } catch (err) { console.error(err); }
}

async function prosesPayrollBulk() {
    const yakin = await showConfirm('Mulai proses generate gaji berdasarkan data absensi yang Anda masukkan?');
    if (!yakin) return;

    const bulan = document.getElementById('payrollBulan').value;
    const tahun = document.getElementById('payrollTahun').value;
    const rows = document.querySelectorAll('.cutoff-row');
    const data = [];

    rows.forEach(row => {
        data.push({
            employee_id: row.dataset.empid,
            hadir: parseFloat(row.querySelector('.input-hadir').value) || 0,
            sakit: parseFloat(row.querySelector('.input-sakit').value) || 0,
            alpa: parseFloat(row.querySelector('.input-alpa').value) || 0,
            lembur: parseFloat(row.querySelector('.input-lembur').value) || 0,
        });
    });

    const res = await fetch(`${API_URL}/payroll/process-bulk`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ client_id: selectedClientId, bulan, tahun, data })
    });

    if (res.ok) {
        showToast('Payroll bulk berhasil digenerate! Menunggu Approval.');
        filterPayrollByClient(); 
    } else {
        showToast('Terjadi kesalahan saat generate gaji', true);
    }
}

async function rejectPayrollAll() {
    const yakin = await showConfirm('Anda yakin ingin membatalkan semua draft gaji di periode ini dan mengulang input absensi?');
    if (!yakin) return;

    // Untuk mempermudah, kita akan memanggil reject pada setiap payroll ID di tabel
    const bulan = document.getElementById('payrollBulan').value;
    const tahun = document.getElementById('payrollTahun').value;
    
    try {
        const payRes = await fetch(`${API_URL}/payroll/status?bulan=${bulan}&tahun=${tahun}&client_id=${selectedClientId}`);
        const existingPayrolls = await payRes.json();
        
        for (const p of existingPayrolls) {
            if (p.status_pembayaran !== 'Approved') {
                await fetch(`${API_URL}/payroll/reject/${p.id}`, { method: 'DELETE' });
            }
        }
        showToast('Draft Gaji Berhasil Dibatalkan. Silakan input ulang absensi.');
        filterPayrollByClient();
    } catch(err) {
        console.error(err);
    }
}

async function approvePayroll(id) {
    const yakin = await showConfirm('Approve payroll ini? Data akan disahkan ke slip karyawan.');
    if (!yakin) return;

    const res = await fetch(`${API_URL}/payroll/approve/${id}`, { method: 'POST' });
    if (res.ok) {
        showToast('Payroll Berhasil di-Approve!');
        filterPayrollByClient();
    }
}

async function viewSlip(id) {
    const res = await fetch(`${API_URL}/payroll/slip/${id}`);
    const data = await res.json();
    const slip = data.payroll;
    const emp = data.employee;
    const details = data.details;

    const modal = document.getElementById('modalSlip');
    const content = document.getElementById('slipContent');
    
    content.innerHTML = `
        <div style="text-align: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
            <h2 style="color: var(--primary-color);">SLIP GAJI</h2>
            <p style="font-size: 13px; color: #666;">Periode: ${slip.bulan}/${slip.tahun}</p>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 14px;">
            <div>
                <p><strong>Nama:</strong> ${emp.nama}</p>
                <p><strong>NIK:</strong> ${emp.nik}</p>
            </div>
            <div style="text-align: right;">
                <p><strong>Status:</strong> ${slip.status_pembayaran}</p>
                <p><strong>Rekening:</strong> ${emp.no_rekening}</p>
            </div>
        </div>
        <table style="width: 100%; font-size: 14px;">
            <tr style="background: #f8f9fa;"><th colspan="2">Penerimaan</th></tr>
            <tr><td>Gaji Pokok</td><td style="text-align: right;">${formatRupiah(slip.gaji_pokok)}</td></tr>
            ${details.filter(d => d.tipe === 'Tunjangan').map(d => `
                <tr><td>${d.nama_komponen}</td><td style="text-align: right;">${formatRupiah(d.jumlah)}</td></tr>
            `).join('')}
            <tr style="background: #f8f9fa;"><th colspan="2">Potongan</th></tr>
            ${details.filter(d => d.tipe === 'Potongan').map(d => `
                <tr><td>${d.nama_komponen}</td><td style="text-align: right; color: #e74c3c;">- ${formatRupiah(d.jumlah)}</td></tr>
            `).join('')}
            <tr style="border-top: 2px solid #eee;">
                <th style="padding-top: 15px; font-size: 16px;">TAKE HOME PAY</th>
                <th style="padding-top: 15px; font-size: 16px; text-align: right; color: var(--success);">${formatRupiah(slip.take_home_pay)}</th>
            </tr>
        </table>
    `;

    modal.style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function tutupModalSlip() {
    document.getElementById('modalSlip').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

async function renderTableKaryawanClient() {
    if (!selectedClientId) return;
    const tbody = document.getElementById('tabelKaryawanClientBody');
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Memuat data...</td></tr>';

    try {
        const res = await fetch(`${API_URL}/employees`);
        employees = await res.json();
        
        const clientEmployees = employees.filter(emp => emp.client_id == selectedClientId);
        
        tbody.innerHTML = '';
        if (clientEmployees.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Belum ada karyawan.</td></tr>';
            return;
        }

        clientEmployees.forEach(emp => {
            const isComplete = emp.gaji_pokok > 0 && emp.no_rekening && emp.no_rekening !== '-';
            const statusBadge = isComplete 
                ? '<span class="badge" style="background: #e8fdf0; color: #2ecc71; padding: 4px 8px; border-radius: 4px; font-size: 10px;">Lengkap</span>'
                : '<span class="badge" style="background: #fff5f5; color: #ff6b6b; padding: 4px 8px; border-radius: 4px; font-size: 10px;">Belum Lengkap</span>';

            tbody.innerHTML += `
                <tr>
                    <td><strong>${emp.nama}</strong><br><small style="color: #888;">${emp.nik}</small></td>
                    <td>${emp.nama_posisi || '-'}</td>
                    <td>${emp.ptkp || 'TK/0'}</td>
                    <td>${formatRupiah(emp.gaji_pokok)}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn-icon btn-edit" onclick="bukaModalKaryawan('edit', ${emp.id})" title="Lengkapi Data"><i class="fas fa-user-edit"></i></button>
                        <button class="btn-icon btn-delete" onclick="hapusKaryawan(${emp.id})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
        });
    } catch (err) { console.error(err); }
}

async function hapusKaryawan(id) {
    const yakin = await showConfirm('Yakin ingin menghapus karyawan ini?');
    if (!yakin) return;
    try {
        const response = await fetch(`${API_URL}/employees/${id}`, { method: 'DELETE' });
        if (response.ok) {
            if (selectedClientId) {
                renderTableKaryawanClient();
                renderClientOrg(selectedClientId);
            }
            showToast('Karyawan berhasil dihapus', 'success');
        }
    } catch (err) { console.error(err); }
}

function bukaModalKaryawanSpecific() {
    bukaModalKaryawan();
    // Auto-select client and hide the field
    const clientSelect = document.getElementById('empClientId');
    if (clientSelect) {
        clientSelect.value = selectedClientId;
        clientSelect.closest('.form-group').style.display = 'none';
        loadPositions(selectedClientId);
    }
}

window.hapusKaryawan = hapusKaryawan;
window.bukaModalKaryawan = bukaModalKaryawan;
window.bukaModalKaryawanSpecific = bukaModalKaryawanSpecific;
window.renderTableKaryawanClient = renderTableKaryawanClient;
window.tutupModalKaryawan = tutupModalKaryawan;
window.loadPositions = loadPositions;
window.filterPayrollByClient = filterPayrollByClient;
window.prosesPayrollBulk = prosesPayrollBulk;
window.approvePayroll = approvePayroll;
window.rejectPayrollAll = rejectPayrollAll;
window.tutupModalSlip = tutupModalSlip;
window.viewSlip = viewSlip;
window.switchView = switchView;
window.bukaModal = bukaModal;
window.bukaModalOrg = bukaModalOrg;
window.selectClient = selectClient;
window.switchClientTab = switchClientTab;
window.logout = logout;
window.tutupModal = tutupModal;
window.renderTable = renderTable;
window.hapusKlien = hapusKlien;
window.toggleNode = toggleNode;
window.loadClientSchema = loadClientSchema;
window.renderClientOrg = renderClientOrg;
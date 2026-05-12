// Cek Login
const currentUser = JSON.parse(localStorage.getItem('user'));
if (!currentUser) {
    window.location.href = 'login';
}

// API URL
const API_URL = '/api';

// Update Header Nama
if (currentUser && document.getElementById('headerUserName')) {
    document.getElementById('headerUserName').innerText = currentUser.username;
}

function logout() {
    localStorage.removeItem('user');
    window.location.href = 'login';
}
window.logout = logout;

// Elemen DOM
const tabelBody = document.getElementById('tabelKlienBody');
const formKlien = document.getElementById('formKlien');
const modal = document.getElementById('modalClient');
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
                        <td style="font-weight: 600; color: var(--primary-color);">${client.nama}</td>
                        <td>${client.sektor}</td>
                        <td>${client.alamat}</td>
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
            document.getElementById('namaKlien').value = client.nama || '';
            document.getElementById('emailKlien').value = client.email || '';
            document.getElementById('sektorKlien').value = client.sektor || '';
            document.getElementById('nib').value = client.nib || '';
            document.getElementById('npwp').value = client.npwp || '';
            document.getElementById('tanggalBergabung').value = client.tgl_gabung ? client.tgl_gabung.split('T')[0] : '';
            document.getElementById('alamat').value = client.alamat || '';
        }
    } else {
        modalTitle.innerText = 'Tambah Data Client';
        btnSubmit.innerText = 'Simpan';
        if (formKlien) formKlien.reset();
        document.getElementById('clientId').value = '';
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
            sektor: document.getElementById('sektorKlien').value,
            nib: document.getElementById('nib').value,
            npwp: document.getElementById('npwp').value,
            tgl_gabung: document.getElementById('tanggalBergabung').value,
            alamat: document.getElementById('alamat').value,
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
                alert('Data berhasil disimpan!');
            }
        } catch (err) {
            console.error('Error saving client:', err);
            alert('Gagal menyimpan data.');
        }
    });
}

async function hapusKlien(id) {
    if (!confirm('Yakin ingin menghapus data klien ini?')) return;
    try {
        const response = await fetch(`${API_URL}/clients/${id}`, { method: 'DELETE' });
        if (response.ok) {
            renderTable();
            alert('Klien berhasil dihapus');
        } else {
            const errData = await response.json();
            alert('Gagal hapus klien: ' + (errData.error || response.statusText));
        }
    } catch (err) {
        console.error('Gagal menghapus klien:', err);
        alert('Kesalahan koneksi: Pastikan server di port 5000 sudah berjalan.');
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
    } else {
        document.getElementById('viewStruktur').classList.add('active');
        document.getElementById('menuStruktur').classList.add('active');
        document.getElementById('viewTitle').innerText = 'Struktur Organisasi';
        // Old org view is now empty as per HTML change
    }
}

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
    document.getElementById('clientDetailTitle').innerText = `Struktur Organisasi: ${nama}`;
    renderClientOrg(id);
}

function backToClientList() {
    selectedClientId = null;
    document.getElementById('tabelKlienContainer').style.display = 'block';
    document.getElementById('clientOrgDetail').style.display = 'none';
}

window.selectClient = selectClient;
window.backToClientList = backToClientList;

async function renderClientOrg(clientId) {
    try {
        const response = await fetch(`${API_URL}/org?client_id=${clientId}`);
        const data = await response.json();
        orgData = Array.isArray(data) ? data : [];

        const container = document.getElementById('clientOrgContainer');
        if (!container) return;
        container.innerHTML = '';

        if (orgData.length === 0) {
            container.innerHTML = `
                <div class="empty-state" style="text-align: center; padding: 40px; background: #fdfdfd; border-radius: 12px; border: 2px dashed #eee;">
                    <i class="fas fa-sitemap" style="font-size: 30px; color: #ddd; margin-bottom: 10px;"></i>
                    <p style="color: #aaa; font-size: 14px;">Belum ada divisi untuk klien ini.</p>
                    <button class="btn-add" onclick="bukaModalOrg('divisi', 'tambah')" style="margin: 10px auto; transform: scale(0.9);">
                        <i class="fas fa-plus"></i> Buat Divisi Pertama
                    </button>
                </div>
            `;
            return;
        }

        // Add Divisi Button at Top
        container.innerHTML += `
            <div style="margin-bottom: 20px;">
                <button class="btn-add" onclick="bukaModalOrg('divisi', 'tambah')" style="font-size: 13px;">
                    <i class="fas fa-plus"></i> Tambah Divisi
                </button>
            </div>
        `;

        orgData.forEach(divisi => {
            let divisiHtml = `
                <div class="org-level" style="border-left: 3px solid var(--primary-color);">
                    <div class="level-header">
                        <div class="level-title">
                            <button class="btn-toggle" onclick="toggleNested(this)">-</button>
                            <i class="fas fa-building" style="color: var(--primary-color);"></i>
                            <span style="font-weight: 600;">${divisi.nama}</span>
                        </div>
                        <div class="action-btns">
                            <button class="btn-icon btn-edit" onclick="bukaModalOrg('divisi', 'edit', ${divisi.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon btn-delete" onclick="hapusOrg('divisi', ${divisi.id})"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="nested-container">
                        <button class="btn-nested-add" onclick="bukaModalOrg('department', 'tambah', null, ${divisi.id})">
                            <i class="fas fa-plus-circle"></i> Tambah Department
                        </button>
                        ${(divisi.departments || []).map(dept => `
                            <div class="org-level department-item" style="border-left: 3px solid var(--info);">
                                <div class="level-header">
                                    <div class="level-title">
                                        <button class="btn-toggle" onclick="toggleNested(this)">-</button>
                                        <i class="fas fa-users-rectangle" style="color: var(--info);"></i>
                                        <span style="font-weight: 500;">${dept.nama}</span>
                                    </div>
                                    <div class="action-btns">
                                        <button class="btn-icon btn-edit" onclick="bukaModalOrg('department', 'edit', ${dept.id}, ${divisi.id})"><i class="fas fa-edit"></i></button>
                                        <button class="btn-icon btn-delete" onclick="hapusOrg('department', ${dept.id}, ${divisi.id})"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                                <div class="nested-container">
                                    <button class="btn-nested-add" style="background: #2ecc71;" onclick="bukaModalOrg('posisi', 'tambah', null, ${dept.id})">
                                        <i class="fas fa-user-plus"></i> Tambah Posisi
                                    </button>
                                    ${(dept.positions || []).map(pos => `
                                        <div class="org-level position-item" style="border-left: 3px solid var(--success);">
                                            <div class="level-header">
                                                <div class="level-title">
                                                    <div style="display: flex; align-items: center; gap: 12px;">
                                                        <div style="width: 30px; height: 30px; background: #f0f2f5; border-radius: 50%; display: grid; place-items: center;">
                                                            <i class="fas fa-user" style="color: #95a5a6; font-size: 12px;"></i>
                                                        </div>
                                                        <div style="display: flex; flex-direction: column;">
                                                            <span style="font-weight: 600; color: #333; font-size: 14px;">${pos.employee_name || '-'}</span>
                                                            <span style="font-weight: 500; font-size: 12px; color: var(--primary-color);">${pos.nama}</span>
                                                            <span style="font-size: 11px; color: #888;">${pos.email || '-'}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="action-btns">
                                                    <button class="btn-icon btn-edit" onclick="bukaModalOrg('posisi', 'edit', ${pos.id}, ${dept.id})"><i class="fas fa-edit"></i></button>
                                                    <button class="btn-icon btn-delete" onclick="hapusOrg('posisi', ${pos.id}, ${dept.id})"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
            container.innerHTML += divisiHtml;
        });
    } catch (err) {
        console.error('Gagal mengambil struktur klien:', err);
    }
}

function toggleNested(btn) {
    const container = btn.closest('.level-header').nextElementSibling;
    if (container.style.display === 'none') {
        container.style.display = 'block';
        btn.innerText = '-';
    } else {
        container.style.display = 'none';
        btn.innerText = '+';
    }
}
window.toggleNested = toggleNested;
window.renderClientOrg = renderClientOrg;

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
    const contactFields = document.getElementById('posContactFields');
    const employeeNameInput = document.getElementById('posEmployeeName');
    const emailInput = document.getElementById('posEmail');
    const phoneInput = document.getElementById('posPhone');
    
    const typeLabel = type.charAt(0).toUpperCase() + type.slice(1);
    title.innerText = (mode === 'edit' ? 'Edit ' : 'Tambah ') + typeLabel;
    
    label.innerText = type === 'posisi' ? 'Posisi' : 'Nama ' + typeLabel;
    nameInput.placeholder = type === 'posisi' ? 'Masukkan posisi' : 'Masukkan nama ' + type.toLowerCase();

    if (type === 'posisi') {
        employeeField.style.display = 'block';
        contactFields.style.display = 'block';
        employeeNameInput.required = true;
    } else {
        employeeField.style.display = 'none';
        contactFields.style.display = 'none';
        employeeNameInput.required = false;
    }
    
    if (mode === 'edit') {
        let currentData = { nama: "", employeeName: "", email: "", phone: "" };
        if (type === 'divisi') {
            const div = orgData.find(d => d.id == id);
            if (div) currentData.nama = div.nama;
        } else if (type === 'department') {
            const div = orgData.find(d => d.id == parentId);
            if (div) {
                const dept = (div.departments || []).find(dept => dept.id == id);
                if (dept) currentData.nama = dept.nama;
            }
        } else if (type === 'posisi') {
            orgData.forEach(div => {
                const dept = (div.departments || []).find(d => d.id == parentId);
                if (dept) {
                    const pos = (dept.positions || []).find(p => p.id == id);
                    if (pos) {
                        currentData = { 
                            nama: pos.nama, 
                            employeeName: pos.employee_name, 
                            email: pos.email, 
                            phone: pos.phone 
                        };
                    }
                }
            });
        }
        nameInput.value = currentData.nama || '';
        employeeNameInput.value = currentData.employeeName || '';
        emailInput.value = currentData.email || '';
        phoneInput.value = currentData.phone || '';
    } else {
        nameInput.value = '';
        employeeNameInput.value = '';
        emailInput.value = '';
        phoneInput.value = '';
    }

    setTimeout(() => nameInput.focus(), 100);
}

function tutupModalOrg() {
    const modalOrg = document.getElementById('modalOrg');
    if (modalOrg) modalOrg.style.display = 'none';
    if (overlay) overlay.style.display = 'none';
}

const formOrg = document.getElementById('formOrg');
if (formOrg) {
    formOrg.addEventListener('submit', async function(e) {
        e.preventDefault();
        const type = document.getElementById('orgType').value;
        const id = document.getElementById('orgId').value;
        const parentId = document.getElementById('orgParentId').value;
        const name = document.getElementById('orgName').value.trim();
        const employeeName = document.getElementById('posEmployeeName').value.trim();
        const email = document.getElementById('posEmail').value.trim();
        const phone = document.getElementById('posPhone').value.trim();

        if (!name) {
            alert('Nama harus diisi!');
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
                    body.employee_name = employeeName;
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
                if (selectedClientId) renderClientOrg(selectedClientId);
                alert('Data berhasil disimpan!');
            } else {
                const errorData = await response.json();
                alert('Gagal menyimpan: ' + (errorData.error || 'Server error'));
            }
        } catch (err) {
            console.error('Error saving org structure:', err);
            alert('Terjadi kesalahan koneksi.');
        }
    });
}

async function hapusOrg(type, id, parentId = null) {
    if (!confirm(`Yakin ingin menghapus ${type} ini?`)) return;
    try {
        const response = await fetch(`${API_URL}/org/${type}/${id}`, { method: 'DELETE' });
        if (response.ok) {
            if (selectedClientId) renderClientOrg(selectedClientId);
        } else {
            const errorData = await response.json();
            alert('Gagal menghapus: ' + (errorData.error || 'Server error'));
        }
    } catch (err) {
        console.error('Gagal menghapus organisasi:', err);
        alert('Kesalahan koneksi saat menghapus.');
    }
}

function tutupSemuaModal() {
    tutupModalOrg();
    tutupModal();
}

window.hapusOrg = hapusOrg;
window.tutupModal = tutupModal;
window.tutupModalOrg = tutupModalOrg;
window.tutupSemuaModal = tutupSemuaModal;
window.switchView = switchView;
window.bukaModal = bukaModal;
window.bukaModalOrg = bukaModalOrg;
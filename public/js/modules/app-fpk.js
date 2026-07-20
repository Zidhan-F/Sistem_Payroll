// =====================================================================
// app-fpk.js — Module for FPK (Form Permintaan Karyawan) Management
// =====================================================================

let fpkMasterData = [];
let fpkAssignmentData = [];
let fpkMasterSearch = '';
let fpkAssignmentSearch = '';
let editingFpkId = null;

// =====================================================================
// MASTER FPK TAB
// =====================================================================

function loadFpkMaster() {
    fetch(API_URL + '/fpk', {
        headers: { 'Authorization': 'Bearer ' + (localStorage.getItem('token') || '') }
    })
    .then(r => r.json())
    .then(data => {
        fpkMasterData = data || [];
        renderFpkMasterTable();
    })
    .catch(err => {
        console.error('Error loading FPK master:', err);
        showToast('Gagal memuat data master FPK.', 'error');
    });
}

function renderFpkMasterTable() {
    const tbody = document.getElementById('tabelFpkMasterBody');
    if (!tbody) return;

    let filtered = fpkMasterData;
    if (fpkMasterSearch) {
        const s = fpkMasterSearch.toLowerCase();
        filtered = filtered.filter(f =>
            (f.nomor_fpk || '').toLowerCase().includes(s) ||
            (f.nama_fpk || '').toLowerCase().includes(s) ||
            (f.provinsi || '').toLowerCase().includes(s) ||
            (f.city || '').toLowerCase().includes(s) ||
            (f.status || '').toLowerCase().includes(s)
        );
    }

    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px; color:#94a3b8;">Tidak ada data FPK ditemukan.</td></tr>';
        return;
    }

    tbody.innerHTML = filtered.map((f, idx) => {
        const statusBadge = f.status === 'Open'
            ? '<span style="background:#dcfce7;color:#16a34a;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">Open</span>'
            : '<span style="background:#fee2e2;color:#dc2626;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">Closed</span>';

        const canEdit = f.status === 'Open';
        const actionBtns = canEdit
            ? `<button class="btn-edit" onclick="editFpkMaster(${f.id})" style="padding:6px 12px;border-radius:6px;border:none;background:#3b82f6;color:white;cursor:pointer;font-size:12px;margin-right:4px;" title="Edit"><i class="fas fa-edit"></i></button>
               <button class="btn-delete" onclick="deleteFpkMaster(${f.id}, '${(f.nomor_fpk || '').replace(/'/g, "\\'")}')" style="padding:6px 12px;border-radius:6px;border:none;background:#ef4444;color:white;cursor:pointer;font-size:12px;" title="Hapus"><i class="fas fa-trash"></i></button>`
            : `<span style="color:#94a3b8;font-size:12px;font-style:italic;">Locked</span>`;

        return `<tr>
            <td style="padding:12px 10px;font-weight:600;color:#1e293b;">${idx + 1}</td>
            <td style="padding:12px 10px;font-weight:600;color:#1e293b;">${f.nomor_fpk || '-'}</td>
            <td style="padding:12px 10px;">${f.nama_fpk || '-'}</td>
            <td style="padding:12px 10px;">${f.provinsi || '-'}</td>
            <td style="padding:12px 10px;">${f.city || '-'}</td>
            <td style="padding:12px 10px;">${statusBadge}</td>
            <td style="padding:12px 10px;" id="tdActionFpkMaster">${actionBtns}</td>
        </tr>`;
    }).join('');
}

function openModalFpkMaster(editId = null) {
    editingFpkId = editId;
    const modal = document.getElementById('modalFpkMaster');
    const overlay = document.getElementById('overlay');
    if (!modal) return;

    document.getElementById('fpkMasterTitle').innerText = editId ? 'Edit FPK' : 'Tambah FPK Baru';
    document.getElementById('fpkNomor').value = '';
    document.getElementById('fpkNama').value = '';
    document.getElementById('fpkProvinsi').value = '';
    document.getElementById('fpkCity').value = '';

    if (editId) {
        const fpk = fpkMasterData.find(f => f.id == editId);
        if (fpk) {
            document.getElementById('fpkNomor').value = fpk.nomor_fpk || '';
            document.getElementById('fpkNama').value = fpk.nama_fpk || '';
            document.getElementById('fpkProvinsi').value = fpk.provinsi || '';
            document.getElementById('fpkCity').value = fpk.city || '';
        }
    }

    modal.style.display = 'block';
    if (overlay) overlay.style.display = 'block';
}

function tutupModalFpkMaster() {
    const modal = document.getElementById('modalFpkMaster');
    const overlay = document.getElementById('overlay');
    if (modal) modal.style.display = 'none';
    if (overlay) overlay.style.display = 'none';
    editingFpkId = null;
}

function saveFpkMaster() {
    const nomor = document.getElementById('fpkNomor').value.trim();
    const nama = document.getElementById('fpkNama').value.trim();
    const provinsi = document.getElementById('fpkProvinsi').value.trim();
    const city = document.getElementById('fpkCity').value.trim();

    if (!nomor || !nama || !provinsi || !city) {
        showToast('Semua field wajib diisi.', 'error');
        return;
    }

    const payload = { nomor_fpk: nomor, nama_fpk: nama, provinsi, city };
    const isEdit = !!editingFpkId;
    const url = isEdit ? API_URL + '/fpk/' + editingFpkId : API_URL + '/fpk';
    const method = isEdit ? 'PUT' : 'POST';

    fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + (localStorage.getItem('token') || ''),
            'X-User-Action': localStorage.getItem('username') || ''
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.id || res.message) {
            showToast(res.message || (isEdit ? 'FPK berhasil diperbarui.' : 'FPK berhasil ditambahkan.'), 'success');
            tutupModalFpkMaster();
            loadFpkMaster();
        } else {
            const errMsg = res.messages ? Object.values(res.messages).join(', ') : (res.message || 'Gagal menyimpan FPK.');
            showToast(errMsg, 'error');
        }
    })
    .catch(err => {
        console.error('Error saving FPK:', err);
        showToast('Terjadi kesalahan saat menyimpan FPK.', 'error');
    });
}

function editFpkMaster(id) {
    openModalFpkMaster(id);
}

async function deleteFpkMaster(id, nomor) {
    if (!await showConfirm(`Apakah Anda yakin ingin menghapus FPK "${nomor}"?`, 'Hapus FPK', 'Ya, Hapus', 'Batal', 'danger')) return;

    fetch(API_URL + '/fpk/' + id, {
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + (localStorage.getItem('token') || ''),
            'X-User-Action': localStorage.getItem('username') || ''
        }
    })
    .then(r => r.json())
    .then(res => {
        if (res.message) {
            showToast(res.message, 'success');
            loadFpkMaster();
        } else {
            const errMsg = res.messages ? Object.values(res.messages).join(', ') : 'Gagal menghapus FPK.';
            showToast(errMsg, 'error');
        }
    })
    .catch(err => {
        console.error('Error deleting FPK:', err);
        showToast('Terjadi kesalahan saat menghapus FPK.', 'error');
    });
}

// =====================================================================
// FPK ASSIGNMENT TAB (Penempelan Karyawan)
// =====================================================================

function loadFpkAssignments() {
    fetch(API_URL + '/fpk/assignments', {
        headers: { 'Authorization': 'Bearer ' + (localStorage.getItem('token') || '') }
    })
    .then(r => r.json())
    .then(data => {
        fpkAssignmentData = data || [];
        renderFpkAssignmentTable();
    })
    .catch(err => {
        console.error('Error loading FPK assignments:', err);
        showToast('Gagal memuat data penempatan FPK.', 'error');
    });
}

function renderFpkAssignmentTable() {
    const tbody = document.getElementById('tabelFpkAssignmentBody');
    if (!tbody) return;

    let filtered = fpkAssignmentData;
    if (fpkAssignmentSearch) {
        const s = fpkAssignmentSearch.toLowerCase();
        filtered = filtered.filter(a =>
            (a.nik || '').toLowerCase().includes(s) ||
            (a.nama_karyawan || '').toLowerCase().includes(s) ||
            (a.nomor_fpk || '').toLowerCase().includes(s) ||
            (a.nama_fpk || '').toLowerCase().includes(s) ||
            (a.provinsi || '').toLowerCase().includes(s) ||
            (a.city || '').toLowerCase().includes(s) ||
            (a.user_submit || '').toLowerCase().includes(s)
        );
    }

    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:40px; color:#94a3b8;">Belum ada karyawan yang ditempelkan ke FPK.</td></tr>';
        return;
    }

    tbody.innerHTML = filtered.map((a, idx) => {
        const tgl = a.tanggal_penempatan ? new Date(a.tanggal_penempatan).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
        return `<tr>
            <td style="padding:12px 10px;font-weight:600;color:#1e293b;">${idx + 1}</td>
            <td style="padding:12px 10px;font-weight:600;color:#1e293b;">${a.nik || '-'}</td>
            <td style="padding:12px 10px;">${a.nama_karyawan || '-'}</td>
            <td style="padding:12px 10px;font-weight:600;color:#6366f1;">${a.nomor_fpk || '-'}</td>
            <td style="padding:12px 10px;">${a.nama_fpk || '-'}</td>
            <td style="padding:12px 10px;">${a.provinsi || '-'}</td>
            <td style="padding:12px 10px;">${a.city || '-'}</td>
            <td style="padding:12px 10px;">${tgl}</td>
            <td style="padding:12px 10px;">
                <button onclick="revokeFpkAssignment(${a.id}, '${(a.nama_karyawan || '').replace(/'/g, "\\'")}', '${(a.nomor_fpk || '').replace(/'/g, "\\'")}')" style="padding:6px 12px;border-radius:6px;border:none;background:#ef4444;color:white;cursor:pointer;font-size:12px;" title="Cabut Penempatan"><i class="fas fa-unlink"></i> Cabut</button>
            </td>
        </tr>`;
    }).join('');
}

// =====================================================================
// MODAL PENEMPELAN (Assign Employee to FPK)
// =====================================================================

let fpkAssignTomSelectClient = null;
let fpkAssignTomSelectEmployee = null;
let fpkAssignTomSelectFpk = null;

function openModalFpkAssign() {
    const modal = document.getElementById('modalFpkAssign');
    const overlay = document.getElementById('overlay');
    if (!modal) return;

    // Reset form
    document.getElementById('fpkAssignProvinsi').value = '';
    document.getElementById('fpkAssignCity').value = '';

    modal.style.display = 'block';
    if (overlay) overlay.style.display = 'block';

    // Clear employee select options and initialize empty TomSelect
    const empSelect = document.getElementById('fpkAssignEmployee');
    if (empSelect) {
        empSelect.innerHTML = '<option value="">-- Pilih Karyawan (Pilih Client terlebih dahulu) --</option>';
        if (fpkAssignTomSelectEmployee) { fpkAssignTomSelectEmployee.destroy(); }
        fpkAssignTomSelectEmployee = new TomSelect('#fpkAssignEmployee', {
            placeholder: 'Pilih Client terlebih dahulu...',
            allowEmptyOption: true
        });
    }

    // Load clients
    loadClientsForFpkAssign();
    // Load open FPKs for dropdown
    loadOpenFpksForAssign();
}

function tutupModalFpkAssign() {
    const modal = document.getElementById('modalFpkAssign');
    const overlay = document.getElementById('overlay');
    if (modal) modal.style.display = 'none';
    if (overlay) overlay.style.display = 'none';

    // Destroy TomSelect instances
    if (fpkAssignTomSelectClient) { fpkAssignTomSelectClient.destroy(); fpkAssignTomSelectClient = null; }
    if (fpkAssignTomSelectEmployee) { fpkAssignTomSelectEmployee.destroy(); fpkAssignTomSelectEmployee = null; }
    if (fpkAssignTomSelectFpk) { fpkAssignTomSelectFpk.destroy(); fpkAssignTomSelectFpk = null; }
}

function loadClientsForFpkAssign() {
    const select = document.getElementById('fpkAssignClient');
    if (!select) return;

    select.innerHTML = '<option value="">-- Pilih Client --</option>';

    fetch(API_URL + '/clients', {
        headers: { 'Authorization': 'Bearer ' + (localStorage.getItem('token') || '') }
    })
    .then(r => r.json())
    .then(data => {
        (data || []).forEach(cli => {
            const opt = document.createElement('option');
            opt.value = cli.id;
            opt.textContent = cli.nama || '';
            select.appendChild(opt);
        });

        // Initialize TomSelect
        if (fpkAssignTomSelectClient) { fpkAssignTomSelectClient.destroy(); }
        fpkAssignTomSelectClient = new TomSelect('#fpkAssignClient', {
            placeholder: 'Pilih Client...',
            allowEmptyOption: true,
            onChange: function(value) {
                if (value) {
                    loadEmployeesForFpkAssign(value);
                } else {
                    const empSelect = document.getElementById('fpkAssignEmployee');
                    if (empSelect) {
                        empSelect.innerHTML = '<option value="">-- Pilih Karyawan (Pilih Client terlebih dahulu) --</option>';
                        if (fpkAssignTomSelectEmployee) { fpkAssignTomSelectEmployee.destroy(); }
                        fpkAssignTomSelectEmployee = new TomSelect('#fpkAssignEmployee', {
                            placeholder: 'Pilih Client terlebih dahulu...',
                            allowEmptyOption: true
                        });
                    }
                }
            }
        });
    })
    .catch(err => console.error('Error loading clients for FPK assign:', err));
}

function loadEmployeesForFpkAssign(clientId) {
    const select = document.getElementById('fpkAssignEmployee');
    if (!select) return;

    // Clear previous options
    select.innerHTML = '<option value="">-- Pilih Karyawan --</option>';

    if (fpkAssignTomSelectEmployee) { fpkAssignTomSelectEmployee.destroy(); fpkAssignTomSelectEmployee = null; }

    fetch(API_URL + '/employees?client_id=' + clientId, {
        headers: { 'Authorization': 'Bearer ' + (localStorage.getItem('token') || '') }
    })
    .then(r => r.json())
    .then(data => {
        (data || []).forEach(emp => {
            const opt = document.createElement('option');
            opt.value = emp.id;
            opt.textContent = `${emp.nik} - ${emp.nama} (${emp.nama_dept || '-'} / ${emp.nama_posisi || '-'})`;
            select.appendChild(opt);
        });

        // Initialize TomSelect
        fpkAssignTomSelectEmployee = new TomSelect('#fpkAssignEmployee', {
            placeholder: 'Cari Karyawan (NIK / Nama)...',
            allowEmptyOption: true,
            maxOptions: 200
        });
    })
    .catch(err => console.error('Error loading employees for FPK assign:', err));
}

function loadOpenFpksForAssign() {
    const select = document.getElementById('fpkAssignFpk');
    if (!select) return;

    select.innerHTML = '<option value="">-- Pilih FPK --</option>';

    fetch(API_URL + '/fpk', {
        headers: { 'Authorization': 'Bearer ' + (localStorage.getItem('token') || '') }
    })
    .then(r => r.json())
    .then(data => {
        const openFpks = (data || []).filter(f => f.status === 'Open');
        openFpks.forEach(f => {
            const opt = document.createElement('option');
            opt.value = f.id;
            opt.textContent = `${f.nomor_fpk} - ${f.nama_fpk} (${f.provinsi}, ${f.city})`;
            opt.dataset.provinsi = f.provinsi;
            opt.dataset.city = f.city;
            select.appendChild(opt);
        });

        // Initialize TomSelect
        if (fpkAssignTomSelectFpk) { fpkAssignTomSelectFpk.destroy(); }
        fpkAssignTomSelectFpk = new TomSelect('#fpkAssignFpk', {
            placeholder: 'Cari FPK (Nomor / Nama)...',
            allowEmptyOption: true,
            maxOptions: 200,
            onChange: function(value) {
                onFpkAssignFpkChange(value);
            }
        });
    })
    .catch(err => console.error('Error loading open FPKs for assign:', err));
}

function onFpkAssignFpkChange(fpkId) {
    const provEl = document.getElementById('fpkAssignProvinsi');
    const cityEl = document.getElementById('fpkAssignCity');

    if (!fpkId) {
        provEl.value = '';
        cityEl.value = '';
        return;
    }

    // Find FPK from loaded master data
    const fpk = fpkMasterData.find(f => f.id == fpkId);
    if (fpk) {
        provEl.value = fpk.provinsi || '';
        cityEl.value = fpk.city || '';
    }
}

function submitFpkAssignment() {
    const employeeId = document.getElementById('fpkAssignEmployee').value;
    const fpkId = document.getElementById('fpkAssignFpk').value;

    if (!employeeId || !fpkId) {
        showToast('Karyawan dan FPK wajib dipilih.', 'error');
        return;
    }

    const btnSubmit = document.getElementById('btnSubmitFpkAssign');
    if (btnSubmit) { btnSubmit.disabled = true; btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...'; }

    fetch(API_URL + '/fpk/assign', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + (localStorage.getItem('token') || ''),
            'X-User-Action': localStorage.getItem('username') || ''
        },
        body: JSON.stringify({ fpk_id: parseInt(fpkId), employee_id: parseInt(employeeId) })
    })
    .then(r => r.json())
    .then(res => {
        if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.innerHTML = '<i class="fas fa-check"></i> Submit'; }

        if (res.id || (res.message && !res.messages)) {
            showToast(res.message || 'Karyawan berhasil ditempelkan ke FPK.', 'success');
            tutupModalFpkAssign();
            loadFpkMaster();
            loadFpkAssignments();
        } else {
            const errMsg = res.messages ? Object.values(res.messages).join(', ') : (res.message || 'Gagal menempelkan karyawan ke FPK.');
            showToast(errMsg, 'error');
        }
    })
    .catch(err => {
        if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.innerHTML = '<i class="fas fa-check"></i> Submit'; }
        console.error('Error assigning FPK:', err);
        showToast('Terjadi kesalahan saat menempelkan FPK.', 'error');
    });
}

async function revokeFpkAssignment(id, nama, nomor) {
    if (!await showConfirm(`Apakah Anda yakin ingin mencabut penempatan FPK "${nomor}" dari karyawan "${nama}"?`, 'Cabut Penempatan FPK', 'Ya, Cabut', 'Batal', 'danger')) return;

    fetch(API_URL + '/fpk/assignments/' + id, {
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + (localStorage.getItem('token') || ''),
            'X-User-Action': localStorage.getItem('username') || ''
        }
    })
    .then(r => r.json())
    .then(res => {
        if (res.message) {
            showToast(res.message, 'success');
            loadFpkMaster();
            loadFpkAssignments();
        } else {
            showToast('Gagal mencabut penempatan FPK.', 'error');
        }
    })
    .catch(err => {
        console.error('Error revoking FPK assignment:', err);
        showToast('Terjadi kesalahan saat mencabut penempatan.', 'error');
    });
}

// =====================================================================
// TAB SWITCHING
// =====================================================================

function switchFpkTab(tab) {
    const role = typeof getCurrentRole === 'function' ? getCurrentRole() : 'admin';
    if (role === 'hc_ops' && tab === 'penempatan') {
        tab = 'master'; // Force back to master for HC Ops
    } else if (role === 'recruiter' && tab === 'master') {
        tab = 'penempatan'; // Force back to penempatan for Recruiter
    }

    // Update tab buttons
    document.querySelectorAll('.fpk-tab-btn').forEach(b => b.classList.remove('active'));
    const activeBtn = document.querySelector(`.fpk-tab-btn[data-tab="${tab}"]`);
    if (activeBtn) activeBtn.classList.add('active');

    // Update tab content
    document.querySelectorAll('.fpk-tab-content').forEach(c => c.style.display = 'none');
    const activeContent = document.getElementById('fpkTab_' + tab);
    if (activeContent) activeContent.style.display = 'block';

    // Load data
    if (tab === 'master') {
        loadFpkMaster();
    } else if (tab === 'penempatan') {
        loadFpkAssignments();
        loadFpkMaster(); // Also reload for latest status
    }
}

// =====================================================================
// INIT & EXPOSE
// =====================================================================

function initFpkView() {
    const role = typeof getCurrentRole === 'function' ? getCurrentRole() : 'admin';
    if (role === 'hc_ops') {
        switchFpkTab('master');
    } else if (role === 'recruiter') {
        switchFpkTab('penempatan');
    } else {
        const activeTab = document.querySelector('.fpk-tab-btn.active');
        const tabName = activeTab ? activeTab.dataset.tab : 'master';
        switchFpkTab(tabName);
    }
}

// Expose functions globally
window.loadFpkMaster = loadFpkMaster;
window.loadFpkAssignments = loadFpkAssignments;
window.openModalFpkMaster = openModalFpkMaster;
window.tutupModalFpkMaster = tutupModalFpkMaster;
window.saveFpkMaster = saveFpkMaster;
window.editFpkMaster = editFpkMaster;
window.deleteFpkMaster = deleteFpkMaster;
window.openModalFpkAssign = openModalFpkAssign;
window.tutupModalFpkAssign = tutupModalFpkAssign;
window.submitFpkAssignment = submitFpkAssignment;
window.revokeFpkAssignment = revokeFpkAssignment;
window.switchFpkTab = switchFpkTab;
window.initFpkView = initFpkView;

let workLocations = [];
let nextWorkLocationId = 1;

async function fetchNextWorkLocationId() {
    try {
        const r = await fetch('/api/work-locations');
        const data = await r.json();
        if (data && data.length > 0) {
            const maxId = Math.max(...data.map(l => parseInt(l.id) || 0));
            nextWorkLocationId = maxId + 1;
        } else {
            nextWorkLocationId = 1;
        }
    } catch (e) {
        console.error(e);
        nextWorkLocationId = 1;
    }
}

function switchClientKaryawanSubTab(tab) {
    document.querySelectorAll('.client-karyawan-subpanel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.sub-tab-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.color = '#64748b';
        btn.style.borderBottomColor = 'transparent';
    });

    if (tab === 'lokasi_kerja') {
        document.getElementById('panelLokasiKerja').style.display = 'block';
        const activeBtn = document.getElementById('subTabLokasiKerja');
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.color = 'var(--primary-color)';
            activeBtn.style.borderBottomColor = 'var(--primary-color)';
        }
        loadWorkLocationsForClient();
    } else if (tab === 'karyawan_data') {
        document.getElementById('panelKaryawanData').style.display = 'block';
        const activeBtn = document.getElementById('subTabKaryawanData');
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.color = 'var(--primary-color)';
            activeBtn.style.borderBottomColor = 'var(--primary-color)';
        }
        if (typeof renderAllEmployees === 'function') {
            renderAllEmployees();
        }
    }
}

async function loadWorkLocations() {
    try {
        const r = await fetch('/api/work-locations');
        workLocations = await r.json();
        renderWorkLocationsTable();
    } catch (e) {
        console.error(e);
    }
}

async function loadGlobalWorkLocations() {
    try {
        const r = await fetch('/api/work-locations');
        workLocations = await r.json();
        renderGlobalWorkLocationsTable();
    } catch (e) {
        console.error(e);
    }
}

function renderGlobalWorkLocationsTable() {
    const tbody = document.getElementById('tabelGlobalLokasiKerjaBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    
    if (workLocations.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding: 20px; color: var(--text-muted);">Belum ada data lokasi kerja.</td></tr>`;
        return;
    }
    
    workLocations.forEach(loc => {
        tbody.innerHTML += `
            <tr>
                <td style="font-weight:600; color: var(--primary-color);">${loc.lokasi_kerja}</td>
                <td><span style="background:#e2e8f0; color:#475569; padding:4px 8px; border-radius:6px; font-size:12px; font-weight:600;">${loc.location_code || '-'}</span></td>
                <td>${loc.nama_klien || '-'}</td>
                <td>${loc.nama_divisi || '-'}</td>
                <td>${loc.nama_dept || '-'}</td>
                <td>${loc.nama_posisi || '-'}</td>
                <td>${loc.provinsi || '-'}</td>
                <td>${loc.kota_kabupaten || '-'}</td>
                <td>
                    <div style="display:flex; gap:8px;">
                        <button onclick="editLokasiKerja(${loc.id})" class="btn-action edit" title="Edit" style="background:#3b82f6; color:white; border:none; padding:6px 10px; border-radius:6px; cursor:pointer;"><i class="fas fa-edit"></i></button>
                        <button onclick="hapusLokasiKerja(${loc.id})" class="btn-action delete" title="Hapus" style="background:#ef4444; color:white; border:none; padding:6px 10px; border-radius:6px; cursor:pointer;"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `;
    });
}

async function loadWorkLocationsForClient() {
    if (!window.selectedClientId) return;
    try {
        const r = await fetch(`/api/work-locations?client_id=${window.selectedClientId}`);
        workLocations = await r.json();
        renderWorkLocationsTable();
    } catch (e) {
        console.error(e);
    }
}

function renderWorkLocationsTable() {
    const tbody = document.getElementById('tabelLokasiKerjaBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    
    if (workLocations.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 20px; color: var(--text-muted);">Belum ada data lokasi kerja.</td></tr>`;
        return;
    }
    
    workLocations.forEach(loc => {
        tbody.innerHTML += `
            <tr>
                <td style="font-weight:600; color: var(--primary-color);">${loc.lokasi_kerja}</td>
                <td><span style="background:#e2e8f0; color:#475569; padding:4px 8px; border-radius:6px; font-size:12px; font-weight:600;">${loc.location_code || '-'}</span></td>
                <td>${loc.nama_divisi || '-'}</td>
                <td>${loc.nama_dept || '-'}</td>
                <td>${loc.nama_posisi || '-'}</td>
                <td>${loc.provinsi || '-'}</td>
                <td>${loc.kota_kabupaten || '-'}</td>
                <td>
                    <div style="display:flex; gap:8px;">
                        <button onclick="editLokasiKerja(${loc.id})" class="btn-action edit" title="Edit" style="background:#3b82f6; color:white; border:none; padding:6px 10px; border-radius:6px; cursor:pointer;"><i class="fas fa-edit"></i></button>
                        <button onclick="hapusLokasiKerja(${loc.id})" class="btn-action delete" title="Hapus" style="background:#ef4444; color:white; border:none; padding:6px 10px; border-radius:6px; cursor:pointer;"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `;
    });
}

function bukaModalLokasiKerja() {
    document.getElementById('modalLokasiKerja').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
    
    populateLocClients();
    
    document.getElementById('formLokasiKerja').reset();
    document.getElementById('workLocationId').value = '';
    document.getElementById('modalLokasiKerjaTitle').innerText = 'Tambah Lokasi Kerja';
    
    resetCascadingSelects();
    fetchNextWorkLocationId();
}

function tutupModalLokasiKerja() {
    document.getElementById('modalLokasiKerja').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

async function populateLocClients() {
    const clientSelect = document.getElementById('locClientId');
    if (!clientSelect) return;
    
    // Save current selection value
    const currentVal = clientSelect.value;
    
    clientSelect.innerHTML = '<option value="">-- Pilih Klien --</option>';
    
    try {
        const r = await fetch('/api/clients');
        const clientsData = await r.json();
        clientsData.forEach(c => {
            clientSelect.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
        });
        
        if (window.selectedClientId) {
            clientSelect.value = window.selectedClientId;
            clientSelect.dispatchEvent(new Event('change'));
            clientSelect.parentElement.style.display = 'none';
        } else {
            clientSelect.parentElement.style.display = 'block';
            if (currentVal) clientSelect.value = currentVal;
        }
    } catch (e) {
        console.error(e);
    }
}

function resetCascadingSelects() {
    document.getElementById('locDivisionId').innerHTML = '<option value="">-- Pilih Divisi --</option>';
    document.getElementById('locDepartmentId').innerHTML = '<option value="">-- Pilih Departemen --</option>';
    document.getElementById('locPositionId').innerHTML = '<option value="">-- Pilih Jabatan --</option>';
}

document.getElementById('locClientId')?.addEventListener('change', async (e) => {
    const clientId = e.target.value;
    resetCascadingSelects();
    if (!clientId) return;
    
    try {
        const r = await fetch(`/api/org?client_id=${clientId}`);
        const divs = await r.json();
        const divSelect = document.getElementById('locDivisionId');
        divSelect.innerHTML = '<option value="">-- Pilih Divisi --</option>';
        divs.forEach(d => {
            divSelect.innerHTML += `<option value="${d.id}">${d.nama}</option>`;
        });
    } catch (err) {
        console.error(err);
    }
});

document.getElementById('locDivisionId')?.addEventListener('change', async (e) => {
    const divId = e.target.value;
    document.getElementById('locDepartmentId').innerHTML = '<option value="">-- Pilih Departemen --</option>';
    document.getElementById('locPositionId').innerHTML = '<option value="">-- Pilih Jabatan --</option>';
    if (!divId) return;
    
    const clientId = document.getElementById('locClientId').value;
    try {
        const r = await fetch(`/api/org?client_id=${clientId}`);
        const divs = await r.json();
        const selectedDiv = divs.find(d => d.id == divId);
        const deptSelect = document.getElementById('locDepartmentId');
        if (selectedDiv && selectedDiv.departments) {
            selectedDiv.departments.forEach(dept => {
                deptSelect.innerHTML += `<option value="${dept.id}">${dept.nama}</option>`;
            });
        }
    } catch (err) {
        console.error(err);
    }
});

document.getElementById('locDepartmentId')?.addEventListener('change', async (e) => {
    const deptId = e.target.value;
    document.getElementById('locPositionId').innerHTML = '<option value="">-- Pilih Jabatan --</option>';
    if (!deptId) return;
    
    const clientId = document.getElementById('locClientId').value;
    const divId = document.getElementById('locDivisionId').value;
    try {
        const r = await fetch(`/api/org?client_id=${clientId}`);
        const divs = await r.json();
        const selectedDiv = divs.find(d => d.id == divId);
        if (selectedDiv && selectedDiv.departments) {
            const selectedDept = selectedDiv.departments.find(dept => dept.id == deptId);
            const posSelect = document.getElementById('locPositionId');
            if (selectedDept && selectedDept.positions) {
                selectedDept.positions.forEach(pos => {
                    posSelect.innerHTML += `<option value="${pos.id}">${pos.nama}</option>`;
                });
            }
        }
    } catch (err) {
        console.error(err);
    }
});

document.getElementById('locName')?.addEventListener('input', (e) => {
    const val = e.target.value;
    if (!val) {
        document.getElementById('locCode').value = '';
        return;
    }
    const words = val.trim().split(/\s+/);
    const initials = words
        .map(w => w.replace(/[^a-zA-Z0-9]/g, '').charAt(0).toUpperCase())
        .filter(c => c !== '')
        .join('');
    
    let id = document.getElementById('workLocationId').value;
    if (!id) {
        if (nextWorkLocationId) {
            id = nextWorkLocationId;
        } else if (workLocations && workLocations.length > 0) {
            id = Math.max(...workLocations.map(l => parseInt(l.id) || 0)) + 1;
        } else {
            id = 1;
        }
    }
    document.getElementById('locCode').value = initials + id;
});

async function editLokasiKerja(id) {
    bukaModalLokasiKerja();
    const loc = workLocations.find(l => l.id == id);
    if (!loc) return;
    
    document.getElementById('modalLokasiKerjaTitle').innerText = 'Edit Lokasi Kerja';
    document.getElementById('workLocationId').value = loc.id;
    document.getElementById('locName').value = loc.lokasi_kerja;
    document.getElementById('locCode').value = loc.location_code || '';
    document.getElementById('locProvinsi').value = loc.provinsi || '';
    document.getElementById('locKotaKabupaten').value = loc.kota_kabupaten || '';
    
    const clientSelect = document.getElementById('locClientId');
    if (clientSelect) {
        clientSelect.value = loc.client_id;
        if (window.selectedClientId) {
            clientSelect.parentElement.style.display = 'none';
        } else {
            clientSelect.parentElement.style.display = 'block';
        }
    }
    
    try {
        const r = await fetch(`/api/org?client_id=${loc.client_id}`);
        const divs = await r.json();
        const divSelect = document.getElementById('locDivisionId');
        divSelect.innerHTML = '<option value="">-- Pilih Divisi --</option>';
        divs.forEach(d => {
            divSelect.innerHTML += `<option value="${d.id}" ${d.id == loc.division_id ? 'selected' : ''}>${d.nama}</option>`;
        });
        
        if (loc.division_id) {
            const selectedDiv = divs.find(d => d.id == loc.division_id);
            const deptSelect = document.getElementById('locDepartmentId');
            deptSelect.innerHTML = '<option value="">-- Pilih Departemen --</option>';
            if (selectedDiv && selectedDiv.departments) {
                selectedDiv.departments.forEach(dept => {
                    deptSelect.innerHTML += `<option value="${dept.id}" ${dept.id == loc.department_id ? 'selected' : ''}>${dept.nama}</option>`;
                });
            }
            
            if (loc.department_id && selectedDiv && selectedDiv.departments) {
                const selectedDept = selectedDiv.departments.find(dept => dept.id == loc.department_id);
                const posSelect = document.getElementById('locPositionId');
                posSelect.innerHTML = '<option value="">-- Pilih Jabatan --</option>';
                if (selectedDept && selectedDept.positions) {
                    selectedDept.positions.forEach(pos => {
                        posSelect.innerHTML += `<option value="${pos.id}" ${pos.id == loc.position_id ? 'selected' : ''}>${pos.nama}</option>`;
                    });
                }
            }
        }
    } catch (err) {
        console.error(err);
    }
}

document.getElementById('formLokasiKerja')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('workLocationId').value;
    const data = {
        client_id: document.getElementById('locClientId').value,
        lokasi_kerja: document.getElementById('locName').value,
        location_code: document.getElementById('locCode').value,
        division_id: document.getElementById('locDivisionId').value || null,
        department_id: document.getElementById('locDepartmentId').value || null,
        position_id: document.getElementById('locPositionId').value || null,
        provinsi: document.getElementById('locProvinsi').value,
        kota_kabupaten: document.getElementById('locKotaKabupaten').value
    };
    
    try {
        const r = await fetch(id ? `/api/work-locations/${id}` : '/api/work-locations', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        if (r.ok) {
            tutupModalLokasiKerja();
            if (window.selectedClientId) {
                loadWorkLocationsForClient();
            } else {
                loadGlobalWorkLocations();
            }
            showToast('Data lokasi kerja berhasil disimpan');
        } else {
            const err = await r.json().catch(() => ({}));
            let msg = 'Gagal menyimpan data lokasi kerja';
            if (err.messages) msg = Object.values(err.messages).join(', ');
            else if (err.message) msg = err.message;
            showToast(msg, 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Terjadi kesalahan koneksi', 'error');
    }
});

async function hapusLokasiKerja(id) {
    if (!await showConfirm('Yakin ingin menghapus lokasi kerja ini?')) return;
    try {
        const r = await fetch(`/api/work-locations/${id}`, { method: 'DELETE' });
        if (r.ok) {
            if (window.selectedClientId) {
                loadWorkLocationsForClient();
            } else {
                loadGlobalWorkLocations();
            }
            showToast('Lokasi kerja berhasil dihapus');
        } else {
            showToast('Gagal menghapus lokasi kerja', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Terjadi kesalahan koneksi', 'error');
    }
}

window.loadGlobalWorkLocations = loadGlobalWorkLocations;
window.switchClientKaryawanSubTab = switchClientKaryawanSubTab;
window.bukaModalLokasiKerja = bukaModalLokasiKerja;
window.tutupModalLokasiKerja = tutupModalLokasiKerja;
window.editLokasiKerja = editLokasiKerja;
window.hapusLokasiKerja = hapusLokasiKerja;

